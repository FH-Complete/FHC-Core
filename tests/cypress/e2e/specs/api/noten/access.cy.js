/**
 * Access control: the scope of `assertLvAccess`.
 *
 * The Lektor tests use an LV of the semester that NOTEN_LEKTOR_USER does not teach (readForeignLv).
 */

import { apiGet, notenApi, lektorAuth, loginAsLektor } from "../../../../support/api/notenApi";
import { expectAuthError, expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import {
	antrittDate,
	loadNotenContext,
	readAuthUid,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";
import { requireConfig, skipIf } from "../../../../support/helpers/notenConfig";
import {
	assistenzAuth,
	assistenzContext,
	assistenzLvWithLehreinheiten,
	requireAssistenz,
} from "../../../../support/helpers/notenAssistenz";

describe("Noten API - Zugriffsschutz", () => {
	let ctx;

	before(() => {
		loadNotenContext().then((loaded) => {
			ctx = loaded;
		});
	});

	beforeEach(() => loginAsLektor());

	it("lehnt eine Anfrage ohne Anmeldung ab", () => {
		cy.clearAllCookies(); // otherwise the previous login is still active

		apiGet("getCisConfig", undefined, null).then((response) => expectAuthError(response));

		apiGet("getCisConfig", undefined, { username: "no-such-user", password: "no-such-password" }).then((response) =>
			expectAuthError(response),
		);
	});

	describe("der konfigurierte API-Benutzer", () => {
		let lvWithoutStudents = null;

		before(() => {
			readAuthUid()
				.then((uid) => cy.task("noten:db:readLvWithoutStudents", { uid }))
				.then((found) => {
					lvWithoutStudents = found;
				});
		});

		it("liest die Studierenden der Test-LV", () => {
			notenApi.getStudentenNoten(ctx.lvId, ctx.semKurzbz).then((response) => {
				const data = expectNotenSuccess(response, "getStudentenNoten auf der eigenen LV");
				expect(data.students, "Liste der Studierenden").to.be.an("array").and.not.be.empty;
			});
		});

		it("liefert für eine LV ohne Studierende eine leere Liste", function () {
			skipIf(this, !lvWithoutStudents, "Übersprungen: NOTEN_LEKTOR_USER unterrichtet keine LV ohne Studierende.");

			notenApi.getStudentenNoten(lvWithoutStudents.lvId, lvWithoutStudents.semKurzbz).then((response) => {
				const data = expectNotenSuccess(response, "getStudentenNoten für eine LV ohne Studierende");
				expect(data.students, "Liste der Studierenden").to.be.an("array").that.is.empty;
			});
		});
	});

	describe("Scope der Lehrperson", () => {
		let foreignLvId = null;

		before(() => {
			readAuthUid()
				.then((uid) => cy.task("noten:db:readForeignLv", { semKurzbz: ctx.semKurzbz, uid }))
				.then((lvId) => {
					foreignLvId = lvId;
				});
		});

		beforeEach(function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_LEKTOR_NUR_EIGENE_LV", true);
			skipIf(
				this,
				!foreignLvId,
				"Übersprungen: das Semester hat keine LV, die NOTEN_LEKTOR_USER nicht unterrichtet.",
			);
		});

		// assertLvAccess denies through terminateWithError -> 500 + phrase, not a 401.
		it("verweigert einer Lehrperson eine fremde LV", () => {
			notenApi
				.getStudentenNoten(foreignLvId, ctx.semKurzbz)
				.then((response) => expectNotenError(response, "keineBerechtigungNoten"));
		});

		it("verweigert einer Lehrperson die Note in einer fremden LV", () => {
			notenApi
				.saveLvNote(foreignLvId, ctx.semKurzbz, ctx.students[0].uid, ctx.notenScale[0])
				.then((response) => expectNotenError(response, "keineBerechtigungNoten"));
		});

		it("verweigert einer Lehrperson die Lehreinheiten einer fremden LV", () => {
			notenApi
				.getLehreinheitenForLv(foreignLvId, ctx.semKurzbz)
				.then((response) => expectNotenError(response, "keineBerechtigungNoten"));
		});
	});

	// getLvForStudiengang is NOT tested here: for an admin isBerechtigt('admin') applies, and every
	// call succeeds. It needs a non-admin login.
	describe("getBenotungstoolContext als Assistenz", () => {
		beforeEach(function () {
			requireAssistenz(this);
			cy.clearAllCookies();
		});

		it("liefert die berechtigten Studiengänge", () => {
			assistenzContext(ctx.semKurzbz).then((result) => {
				expect(result, "ein Semester mit Studiengängen der Assistenz").to.not.be.null;
				expect(result.data.isAssistenz, "isAssistenz").to.be.true;
				expect(result.data.studiengaenge, "studiengaenge").to.be.an("array").and.not.be.empty;
			});
		});

		it("ignoriert eine lv_id, die nicht nur aus Ziffern besteht", () => {
			apiGet(
				"getBenotungstoolContext",
				{ sem_kurzbz: ctx.semKurzbz, lv_id: ctx.semKurzbz },
				assistenzAuth(),
			).then((response) => {
				const data = expectNotenSuccess(response, "getBenotungstoolContext mit dem Semester als lv_id");
				expect(data.preselectStudiengang_kz, "keine Vorauswahl").to.be.null;
			});
		});
	});

	// An empty or non-numeric lv_id would extend the access check to all LVs.
	describe("Parameter der Zugriffsprüfung", () => {
		const s0 = () => ctx.students[0];

		// saveFreigabe is left out: it checks the password first and sends a mail.
		const endpoints = {
			getStudentenNoten: (lvId, semKurzbz) => notenApi.getStudentenNoten(lvId, semKurzbz),
			getLehreinheitenForLv: (lvId, semKurzbz) => notenApi.getLehreinheitenForLv(lvId, semKurzbz),
			getNoteByPunkte: (lvId, semKurzbz) => notenApi.getNoteByPunkte(lvId, semKurzbz, 50),
			saveLvNote: (lvId, semKurzbz) => notenApi.saveLvNote(lvId, semKurzbz, s0().uid, ctx.noten.negativ),
			importLvNoten: (lvId, semKurzbz) => notenApi.importLvNoten(lvId, semKurzbz, []),
			savePruefung: (lvId, semKurzbz) =>
				notenApi.savePruefung(lvId, semKurzbz, s0().uid, {
					note: ctx.noten.negativ,
					datum: antrittDate(ctx, 1),
				}),
			// createPruefungen checks the students before the access check
			createPruefungen: (lvId, semKurzbz) =>
				notenApi.createPruefungen(lvId, semKurzbz, [{ uid: s0().uid }], { datum: antrittDate(ctx, 1) }),
			importPruefungen: (lvId, semKurzbz) => notenApi.importPruefungen(lvId, semKurzbz, []),
		};

		Object.entries(endpoints).forEach(([name, call]) => {
			[null, 0, "", "abc", "1abc", -1].forEach((lvId) => {
				it(`${name} lehnt lv_id=${JSON.stringify(lvId)} ab`, () => {
					call(lvId, ctx.semKurzbz).then((response) => expectNotenError(response, "wrongParameters"));
				});
			});

			[null, "", "   "].forEach((semKurzbz) => {
				it(`${name} lehnt sem_kurzbz=${JSON.stringify(semKurzbz)} ab`, () => {
					call(ctx.lvId, semKurzbz).then((response) => expectNotenError(response, "wrongParameters"));
				});
			});
		});

		describe("ohne LV-Filter", () => {
			let student = null;

			before(() => {
				requireDbReset();

				const search = (i) =>
					i >= ctx.students.length
						? cy.wrap(null, { log: false })
						: cy
								.task("noten:db:countOtherLvNoten", {
									lvId: ctx.lvId,
									semKurzbz: ctx.semKurzbz,
									studentUid: ctx.students[i].uid,
								})
								.then((count) => (count === 0 ? ctx.students[i] : search(i + 1)));

				search(0).then((found) => {
					student = found;
				});
			});

			it("ändert bei lv_id=null keine LV-Note", function () {
				skipIf(this, !student, "Übersprungen: jeder Studierende hat LV-Noten in anderen LVs.");

				const g2 = ctx.notenScale.find((n) => String(n) !== String(ctx.noten.negativ));

				resetNotenState(ctx);
				seedBaseline(ctx, student);

				notenApi
					.saveLvNote(null, ctx.semKurzbz, student.uid, g2)
					.then((response) => expectNotenError(response, "wrongParameters"));

				readLvGesamtnoteViaDb(ctx, student).then((row) => {
					expect(String(row.note), "die LV-Note bleibt").to.eq(String(ctx.noten.negativ));
				});
			});
		});
	});

	// The endpoint returns all Lehreinheiten of the LV, for every role with access to the LV.
	describe("getLehreinheitenForLv", () => {
		const lehreinheitenOfLv = (lvId) =>
			cy
				.task("noten:db:readLehreinheitenOfLv", { lvId, semKurzbz: ctx.semKurzbz })
				.then((ids) => ids.map(String).sort());

		// The query returns one row per group, so a Lehreinheit can appear more than once
		const idsOf = (rows) => [...new Set(rows.map((r) => String(r.lehreinheit_id)))].sort();

		before(() => requireDbReset());

		it("liefert dieselben Lehreinheiten wie die Datenbank", () => {
			notenApi.getLehreinheitenForLv(ctx.lvId, ctx.semKurzbz).then((response) => {
				const rows = expectNotenSuccess(response, "getLehreinheitenForLv");
				expect(rows, "Lehreinheiten").to.be.an("array").and.not.be.empty;
				expect(rows[0], "Felder einer Lehreinheit").to.include.all.keys(
					"lehreinheit_id",
					"lehrveranstaltung_id",
					"lehrform_kurzbz",
					"direktinskription",
					"semester",
					"verband",
					"gruppe",
					"gruppe_kurzbz",
					"kurzbz",
					"kurzbzlang",
					"termincount",
					"studentcount",
				);

				lehreinheitenOfLv(ctx.lvId).then((ids) => {
					expect(idsOf(rows), "lehreinheit_id").to.deep.eq(ids);
				});
			});
		});

		it("liefert einer Assistenz die Lehreinheiten einer LV ihres Studiengangs", function () {
			requireAssistenz(this);
			cy.clearAllCookies();

			assistenzLvWithLehreinheiten(ctx.semKurzbz).then((target) => {
				expect(target, "eine LV der Assistenz mit Lehreinheiten").to.not.be.null;

				apiGet(
					"getLehreinheitenForLv",
					{ lv_id: target.lvId, sem_kurzbz: target.semKurzbz },
					assistenzAuth(),
				).then((response) => {
					const rows = expectNotenSuccess(response, "getLehreinheitenForLv als Assistenz");
					expect(idsOf(rows), "lehreinheit_id").to.deep.eq(target.lehreinheiten);
				});
			});
		});

		it("ersetzt den alten Endpunkt Lehre/getLeForLv", () => {
			cy.request({
				method: "GET",
				url: "/index.ci.php/api/frontend/v1/Lehre/getLeForLv",
				auth: lektorAuth(),
				failOnStatusCode: false,
			}).then((response) => {
				expect(response.status, "Status von Lehre/getLeForLv").to.not.eq(200);
			});
		});
	});
});
