/**
 * Access control (P2) - das Scoping von assertLvAccess.
 *
 * Diese API kennt keine Impersonation, der Lektoren-Scope braucht daher echte Zugangsdaten:
 * NOTEN_TEACHER_USER / NOTEN_TEACHER_PASSWORD / NOTEN_FOREIGN_LV_ID.
 */

import { notenApi } from "../../../../support/api/notenApi";
import {
	expectAuthError,
	expectNotenError,
	expectNotenSuccess,
} from "../../../../support/helpers/notenErrors";
import {
	attemptDate,
	loadNotenContext,
	readLvGesamtnote,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";
import {
	assistenzAuth,
	assistenzConfigured,
	assistenzKontext,
	assistenzLvMitLehreinheiten,
} from "../../../../support/helpers/notenAssistenz";

const NOTEN_API = "/index.ci.php/api/frontend/v1/Noten";

const teacherConfigured = () =>
	Boolean(Cypress.env("NOTEN_TEACHER_USER") && Cypress.env("NOTEN_FOREIGN_LV_ID"));

const getAs = (auth, path, qs) =>
	cy.request({ method: "GET", url: `${NOTEN_API}/${path}`, qs, auth, failOnStatusCode: false });

const skipOhneAssistenz = (test) => {
	if (assistenzConfigured()) return;
	Cypress.log({ name: "skip", message: "Übersprungen: NOTEN_ASSISTENZ_USER / NOTEN_ASSISTENZ_PASSWORD fehlen." });
	test.skip();
};

/** A getStudentenNoten call as an explicitly chosen user (or with no credentials at all). */
const getStudentenNotenAs = (auth, lvId, semKurzbz) =>
	cy.request({
		method: "GET",
		url: `${NOTEN_API}/getStudentenNoten`,
		qs: { lv_id: lvId, sem_kurzbz: semKurzbz },
		auth,
		failOnStatusCode: false,
	});

describe("Noten API - access control", () => {
	let ctx;

	before(() => {
		loadNotenContext().then((context) => {
			ctx = context;
		});
	});

	it("rejects requests that are not authenticated", () => {
		cy.clearAllCookies(); // otherwise the previous login is still active

		cy.request({ method: "GET", url: `${NOTEN_API}/getCisConfig`, failOnStatusCode: false }).then(
			(response) => expectAuthError(response),
		);

		cy.request({
			method: "GET",
			url: `${NOTEN_API}/getCisConfig`,
			auth: { username: "no-such-user", password: "no-such-password" },
			failOnStatusCode: false,
		}).then((response) => expectAuthError(response));
	});

	describe("the configured API user", () => {
		it("can read the students of the test LV", () => {
			notenApi.getStudentenNoten(ctx.lvId, ctx.semKurzbz).then((response) => {
				const data = expectNotenSuccess(response, "getStudentenNoten on the own/admin LV");
				expect(data[0], "student list").to.be.an("array").and.not.be.empty;
			});
		});
	});

	describe("teacher scoping", () => {
		beforeEach(function () {
			if (!teacherConfigured()) {
				Cypress.log({
					name: "skip",
					message:
					"Skipped: needs NOTEN_TEACHER_USER / NOTEN_TEACHER_PASSWORD and NOTEN_FOREIGN_LV_ID " +
						"(an LV that teacher does NOT teach). This API has no impersonation, so a second " +
						"real login is required.",
				});
				this.skip();
			}
			// cy.request reuses the session cookie of the previous (admin) call and the server prefers
			// it over the Basic header - without this every test here silently runs as the admin user.
			cy.clearAllCookies();
		});

		const teacherAuth = () => ({
			username: Cypress.env("NOTEN_TEACHER_USER"),
			password: Cypress.env("NOTEN_TEACHER_PASSWORD"),
		});

		// assertLvAccess denies through terminateWithError -> 500 + phrase, not a 401.
		it("denies a teacher access to an LV they do not teach", () => {
			getStudentenNotenAs(
				teacherAuth(),
				Cypress.env("NOTEN_FOREIGN_LV_ID"),
				ctx.semKurzbz,
			).then((response) => {
				expectNotenError(response, "keineBerechtigungNoten");
			});
		});

		it("denies a teacher writing a grade in an LV they do not teach", () => {
			cy.request({
				method: "POST",
				url: `${NOTEN_API}/saveNotenvorschlag`,
				body: {
					lv_id: Cypress.env("NOTEN_FOREIGN_LV_ID"),
					sem_kurzbz: ctx.semKurzbz,
					student_uid: ctx.students[0].uid,
					note: ctx.gradeNotes[0],
					punkte: null,
				},
				auth: teacherAuth(),
				failOnStatusCode: false,
			}).then((response) => {
				expectNotenError(response, "keineBerechtigungNoten");
			});
		});

		it("denies a teacher the Lehreinheiten of an LV they do not teach", () => {
			getAs(teacherAuth(), "getLehreinheitenFuerLv", {
				lv_id: Cypress.env("NOTEN_FOREIGN_LV_ID"),
				sem_kurzbz: ctx.semKurzbz,
			}).then((response) => expectNotenError(response, "keineBerechtigungNoten"));
		});
	});

	// getLvForStudiengang ist NICHT abgedeckt: als Admin greift isBerechtigt('admin') und jeder
	// Aufruf gelingt. Braucht denselben Nicht-Admin-Login wie die Lektorentests oben.

	describe("getBenotungstoolContext als Assistenz", () => {
		beforeEach(function () {
			skipOhneAssistenz(this);
			cy.clearAllCookies();
		});

		// C1: Der Assistenz-Zweig lud früher als einziger das StudiengangModel. Der Test schützt ihn.
		it("liefert die berechtigten Studiengänge", () => {
			assistenzKontext(ctx.semKurzbz).then((kontext) => {
				expect(kontext, "ein Semester mit Studiengängen der Assistenz").to.not.be.null;
				expect(kontext.data.isAssistenz, "isAssistenz").to.be.true;
				expect(kontext.data.studiengaenge, "studiengaenge").to.be.an("array").and.not.be.empty;
			});
		});

		// W9: Die alte Route las das Semester als lv_id.
		it("ignoriert eine lv_id, die nicht nur aus Ziffern besteht", () => {
			getAs(assistenzAuth(), "getBenotungstoolContext", {
				sem_kurzbz: ctx.semKurzbz,
				lv_id: ctx.semKurzbz,
			}).then((response) => {
				const data = expectNotenSuccess(response, "getBenotungstoolContext mit dem Semester als lv_id");
				expect(data.preselectStudiengang_kz, "keine Vorauswahl").to.be.null;
			});
		});
	});

	// C5: Eine leere oder nicht numerische lv_id weitete die Zugriffsprüfung auf alle LVs aus.
	describe("Parameter der Zugriffsprüfung", () => {
		const s0 = () => ctx.students[0];

		// saveStudentenNoten fehlt: Der Endpunkt prüft zuerst das Passwort und verschickt eine Mail.
		const endpunkte = {
			getStudentenNoten: (lv, sem) => notenApi.getStudentenNoten(lv, sem),
			getLehreinheitenFuerLv: (lv, sem) => notenApi.getLehreinheitenFuerLv(lv, sem),
			getNoteByPunkte: (lv, sem) => notenApi.getNoteByPunkte(50, lv, sem),
			saveNotenvorschlag: (lv, sem) => notenApi.saveNotenvorschlag(lv, sem, s0().uid, ctx.notes.negativ),
			saveNotenvorschlagBulk: (lv, sem) => notenApi.saveNotenvorschlagBulk(lv, sem, []),
			// alle Felder mitschicken, sonst druckt PHP auf der Dev-Instanz eine Notice vor das JSON
			saveStudentPruefung: (lv, sem) =>
				notenApi.saveStudentPruefung({
					student_uid: s0().uid,
					note: ctx.notes.negativ,
					datum: attemptDate(ctx, 1),
					lva_id: lv,
					lehreinheit_id: s0().lehreinheit_id,
					sem_kurzbz: sem,
				}),
			// createPruefungen prüft uids vor der Zugriffsprüfung
			createPruefungen: (lv, sem) => notenApi.createPruefungen([{ uid: s0().uid }], attemptDate(ctx, 1), lv, sem),
			savePruefungenBulk: (lv, sem) => notenApi.savePruefungenBulk(lv, sem, []),
		};

		Object.entries(endpunkte).forEach(([name, aufruf]) => {
			[null, 0, "", "abc", "1abc", -1].forEach((lv) => {
				it(`${name} lehnt lv_id=${JSON.stringify(lv)} ab`, () => {
					aufruf(lv, ctx.semKurzbz).then((response) => expectNotenError(response, "wrongParameters"));
				});
			});

			[null, "", "   "].forEach((sem) => {
				it(`${name} lehnt sem_kurzbz=${JSON.stringify(sem)} ab`, () => {
					aufruf(ctx.lvId, sem).then((response) => expectNotenError(response, "wrongParameters"));
				});
			});
		});

		describe("ohne LV-Filter", () => {
			let student = null;

			before(() => {
				requireDbReset();

				// Der alte Code überschrieb eine LV-Note einer anderen LV. Diese Zeile setzt kein Reset zurück.
				const suche = (i) =>
					i >= ctx.students.length
						? cy.wrap(null, { log: false })
						: cy
							.task("noten:db:andereLvNoten", {
								lvId: ctx.lvId,
								semKurzbz: ctx.semKurzbz,
								studentUid: ctx.students[i].uid,
							})
							.then((anzahl) => (anzahl === 0 ? ctx.students[i] : suche(i + 1)));

				suche(0).then((found) => {
					student = found;
				});
			});

			it("ändert bei lv_id=null keine LV-Note", function () {
				if (!student) {
					Cypress.log({ name: "skip", message: "Übersprungen: jeder Studierende hat LV-Noten in anderen LVs." });
					this.skip();
				}

				const g2 = ctx.gradeNotes.find((n) => String(n) !== String(ctx.notes.negativ));

				resetNotenState(ctx);
				seedBaseline(ctx, student.uid);

				notenApi
					.saveNotenvorschlag(null, ctx.semKurzbz, student.uid, g2)
					.then((response) => expectNotenError(response, "wrongParameters"));

				readLvGesamtnote(ctx, student.uid).then((row) => {
					expect(String(row.note), "die LV-Note bleibt").to.eq(String(ctx.notes.negativ));
				});
			});
		});
	});

	// W10: Der Endpunkt liefert alle Lehreinheiten der LV, für jede Rolle mit Zugriff auf die LV.
	describe("getLehreinheitenFuerLv", () => {
		const lehreinheitenDerLv = (lvId) =>
			cy.task("noten:db:lehreinheitenDerLv", { lvId, semKurzbz: ctx.semKurzbz }).then((ids) => ids.map(String).sort());

		// die Abfrage liefert eine Zeile je Gruppe, eine Lehreinheit kann also mehrfach vorkommen
		const idsOf = (rows) => [...new Set(rows.map((r) => String(r.lehreinheit_id)))].sort();

		before(() => requireDbReset());

		it("liefert dieselben Lehreinheiten wie die Datenbank", () => {
			notenApi.getLehreinheitenFuerLv(ctx.lvId, ctx.semKurzbz).then((response) => {
				const rows = expectNotenSuccess(response, "getLehreinheitenFuerLv");
				expect(rows, "Lehreinheiten").to.be.an("array").and.not.be.empty;
				expect(rows[0], "Felder einer Lehreinheit").to.include.all.keys(
					"lehreinheit_id", "lehrveranstaltung_id", "lehrform_kurzbz", "direktinskription", "semester",
					"verband", "gruppe", "gruppe_kurzbz", "kurzbz", "kurzbzlang", "termincount", "studentcount",
				);

				lehreinheitenDerLv(ctx.lvId).then((ids) => {
					expect(idsOf(rows), "lehreinheit_id").to.deep.eq(ids);
				});
			});
		});

		it("liefert einer Assistenz die Lehreinheiten einer LV ihres Studiengangs", function () {
			skipOhneAssistenz(this);
			cy.clearAllCookies();

			assistenzLvMitLehreinheiten(ctx.semKurzbz).then((ziel) => {
				expect(ziel, "eine LV der Assistenz mit Lehreinheiten").to.not.be.null;

				getAs(assistenzAuth(), "getLehreinheitenFuerLv", { lv_id: ziel.lvId, sem_kurzbz: ziel.sem }).then(
					(response) => {
						const rows = expectNotenSuccess(response, "getLehreinheitenFuerLv als Assistenz");
						expect(idsOf(rows), "lehreinheit_id").to.deep.eq(ziel.lehreinheiten);
					},
				);
			});
		});

		it("ersetzt den alten Endpunkt Lehre/getLeForLv", () => {
			cy.request({
				method: "GET",
				url: "/index.ci.php/api/frontend/v1/Lehre/getLeForLv",
				auth: { username: Cypress.env("adminusername"), password: Cypress.env("adminpassword") },
				failOnStatusCode: false,
			}).then((response) => {
				expect(response.status, "Status von Lehre/getLeForLv").to.not.eq(200);
			});
		});
	});
});
