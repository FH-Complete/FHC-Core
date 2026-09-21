/**
 * Scope of write paths: An exam belongs to the student and the course associated with the request.
 *
 * An edit affects only one exam for this student in this course. The server selects the
 * course, and a non-participant does not receive a grade. Both rejections report
 * c4pruefungNichtGespeichert. A genuine write error reports the same message, which is why every
 * test also checks the database.
 *
 * The seeder group `benotungstool_fixture_erweitert` creates a second course with two instructors.
 */

import { notenApi, notenAuth, pruefungenOf } from "../../../../support/api/notenApi";
import {
	expectBulkRowAccepted,
	expectBulkRowError,
	expectNotenError,
	expectNotenSuccess,
} from "../../../../support/helpers/notenErrors";
import { requireNotenMode, requireWiederholung, skipIf } from "../../../../support/helpers/notenConfig";
import { assistenzAuth, requireAssistenz } from "../../../../support/helpers/notenAssistenz";
import {
	attemptDate,
	baselineDate,
	loadNotenContext,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedBaseline,
	seedPruefung,
	shiftDate,
} from "../../../../support/helpers/notenTestData";
import { attemptsOfStudent, editPruefung, readStateViaApi } from "../../../../support/helpers/notenScenario";

const NOTEN_API = "/index.ci.php/api/frontend/v1/Noten";

const dayOf = (value) => String(value).slice(0, 10);

describe("Noten API - Scope der Schreibpfade", () => {
	let ctx;
	let a;
	let b;

	// null: die Instanz kann den Fall nicht bauen, der Test wird übersprungen
	let outsider = null;
	let foreign = null;
	let otherLe = null;
	let typeWithoutAntritt = null;

	const lvScope = () => ({ lvId: ctx.lvId, semKurzbz: ctx.semKurzbz });

	/** A student with exactly one course unit and another course unit from the same course. */
	const findOtherLe = (lehreinheiten, i = 0) => {
		if (lehreinheiten.length < 2 || i >= ctx.students.length) return cy.wrap(null, { log: false });

		const student = ctx.students[i];
		return cy.task("noten:db:lehreinheitenOfStudent", { ...lvScope(), studentUid: student.uid }).then((own) => {
			if (own.length !== 1) return findOtherLe(lehreinheiten, i + 1);
			const other = lehreinheiten.find((le) => String(le) !== String(own[0]));
			return { student, ownLe: own[0], otherLe: other };
		});
	};

	/** The first type from PRUEFUNG_TYPEN_OHNE_ANTRITT that tbl_pruefungstyp contains. */
	const findTypeWithoutAntritt = (types, i = 0) => {
		if (i >= types.length) return cy.wrap(null, { log: false });

		return cy
			.task("noten:db:pruefungstypExists", { type: types[i] })
			.then((found) => (found ? types[i] : findTypeWithoutAntritt(types, i + 1)));
	};

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;
			[a, b] = ctx.students;

			cy.task("noten:db:nonParticipant", lvScope()).then((uid) => {
				outsider = uid;
			});
			cy.task("noten:db:foreignLehreinheit", lvScope()).then((row) => {
				foreign = row;
			});
			findTypeWithoutAntritt(ctx.cisConfig.PRUEFUNG_TYPEN_OHNE_ANTRITT || []).then((type) => {
				typeWithoutAntritt = type;
			});
			cy.task("noten:db:lehreinheitenOfLv", lvScope())
				.then((lehreinheiten) => findOtherLe(lehreinheiten))
				.then((found) => {
					otherLe = found;
				});
		});
	});

	describe("ein Edit trifft nur eine eigene Prüfung", () => {
		/** Die Prüfung von B, die der Request für A bearbeiten will. */
		const seedForeignRow = () =>
			seedPruefung(ctx, b, { note: ctx.notes.negativ, datum: baselineDate(ctx), type: "Termin1" }).then(
				(seeded) => seeded.pruefungId,
			);

		const expectForeignRowUnchanged = (foreignId) =>
			readStateViaApi(ctx).then((data) => {
				const storedPruefung = pruefungenOf(data, b.uid).find(
					(p) => String(p.pruefung_id) === String(foreignId),
				);
				expect(storedPruefung, "die Prüfung von B existiert weiter").to.exist;
				expect(String(storedPruefung.note), "die Prüfung von B behält ihre Note").to.eq(
					String(ctx.notes.negativ),
				);
				expect(dayOf(storedPruefung.datum), "die Prüfung von B behält ihr Datum").to.eq(baselineDate(ctx));
			});

		it("lehnt die Prüfung eines anderen Studierenden ab", () => {
			const otherNote = ctx.gradeNotes.find((n) => String(n) !== String(ctx.notes.negativ));
			let foreignId;
			let lvNoteBefore;

			resetNotenState(ctx);
			seedBaseline(ctx, a);
			seedBaseline(ctx, b, { erstantritt: false });
			seedForeignRow().then((id) => {
				foreignId = id;
			});
			readLvGesamtnoteViaDb(ctx, a.uid).then((row) => {
				lvNoteBefore = row;
			});

			cy.then(() =>
				editPruefung(ctx, a, {
					pruefungId: foreignId,
					note: otherNote,
					datum: shiftDate(baselineDate(ctx), 3),
				}),
			).then((response) => expectNotenError(response, "c4pruefungNichtGespeichert"));

			cy.then(() => expectForeignRowUnchanged(foreignId));
			readLvGesamtnoteViaDb(ctx, a.uid).then((row) => {
				expect(String(row.note), "die LV-Note von A bleibt").to.eq(String(lvNoteBefore.note));
				expect(String(row.benotungsdatum), "das Benotungsdatum von A bleibt").to.eq(
					String(lvNoteBefore.benotungsdatum),
				);
			});
		});

		it("lehnt eine fremde Prüfung für einen Studierenden ohne Prüfung ab", () => {
			let foreignId;

			resetNotenState(ctx);
			seedForeignRow().then((id) => {
				foreignId = id;
			});

			cy.then(() =>
				editPruefung(ctx, a, {
					pruefungId: foreignId,
					note: ctx.gradeNotes[0],
					datum: shiftDate(baselineDate(ctx), 3),
				}),
			).then((response) => expectNotenError(response, "c4pruefungNichtGespeichert"));

			readLvGesamtnoteViaDb(ctx, a.uid).then((row) => {
				expect(row, "A bekommt keine LV-Note").to.be.null;
			});
			cy.then(() => expectForeignRowUnchanged(foreignId));
		});

		// Der Server sperrt jeden Typ aus PRUEFUNG_TYPEN_OHNE_ANTRITT für Edits.
		// Aktuell: zusKommPruef, den die Studierendenverwaltung einträgt.
		it("lehnt eine Prüfung der Studierendenverwaltung ab", function () {
			skipIf(
				this,
				!typeWithoutAntritt,
				"Übersprungen: kein Typ aus PRUEFUNG_TYPEN_OHNE_ANTRITT in tbl_pruefungstyp.",
			);

			const datum = attemptDate(ctx, 1);
			let pruefungId;

			resetNotenState(ctx);
			seedBaseline(ctx, a);
			seedPruefung(ctx, a, { note: ctx.notes.negativ, datum, type: typeWithoutAntritt }).then((seeded) => {
				pruefungId = seeded.pruefungId;
			});

			cy.then(() =>
				editPruefung(ctx, a, { pruefungId, note: ctx.notes.negativ, datum: shiftDate(datum, 2) }),
			).then((response) => expectNotenError(response, "c4pruefungNichtGespeichert"));

			readStateViaApi(ctx).then((data) => {
				const storedPruefung = pruefungenOf(data, a.uid).find(
					(p) => String(p.pruefung_id) === String(pruefungId),
				);
				expect(storedPruefung, `die Prüfung vom Typ ${typeWithoutAntritt} existiert weiter`).to.exist;
				expect(dayOf(storedPruefung.datum), "sie behält ihr Datum").to.eq(datum);
			});
		});
	});

	describe("die Lehreinheit einer Prüfung", () => {
		const terminFor = (uid, lehreinheitId) =>
			notenApi.saveStudentPruefung({
				student_uid: uid,
				note: ctx.notes.negativ,
				punkte: null,
				datum: attemptDate(ctx, 1),
				lva_id: ctx.lvId,
				lehreinheit_id: lehreinheitId,
				sem_kurzbz: ctx.semKurzbz,
				pruefung_id: null,
			});

		// The server replaces the teaching unit without displaying an error message.
		it("ersetzt eine andere Lehreinheit derselben LV", function () {
			skipIf(this, !otherLe, "Übersprungen: die LV hat keine zweite Lehreinheit.");
			requireWiederholung(this, ctx);

			const { student, ownLe } = otherLe;

			resetNotenState(ctx);
			seedBaseline(ctx, student);

			terminFor(student.uid, otherLe.otherLe).then((response) => {
				const [saved] = expectNotenSuccess(response, "Termin mit einer anderen Lehreinheit");
				expect(String(saved.lehreinheit_id), "die Lehreinheit des Studierenden").to.eq(String(ownLe));
			});
		});

		it("ersetzt die Lehreinheit einer fremden LV", function () {
			skipIf(this, !foreign, "Übersprungen: das Semester hat keine Lehreinheit einer anderen LV.");
			requireWiederholung(this, ctx);

			resetNotenState(ctx);
			seedBaseline(ctx, a);

			terminFor(a.uid, foreign.lehreinheit_id)
				.then((response) => {
					// resetNotenState does not reach a line in the external LV
					const saved = response.body?.data?.[0];
					if (saved && String(saved.lehreinheit_id) === String(foreign.lehreinheit_id)) {
						cy.task("noten:db:deletePruefung", { pruefungId: saved.pruefung_id, studentUids: [a.uid] });
					}
					return cy.wrap(response, { log: false });
				})
				.then((response) => {
					const [saved] = expectNotenSuccess(response, "Termin mit einer fremden Lehreinheit");
					return cy
						.task("noten:db:lehreinheitenOfStudent", { ...lvScope(), studentUid: a.uid })
						.then((own) => {
							expect(own.map(String), "eine Lehreinheit des Studierenden").to.include(
								String(saved.lehreinheit_id),
							);
						});
				});

			readStateViaApi(ctx).then((data) => {
				expect(attemptsOfStudent(data, a.uid), "Antritt 1 und der neue Termin").to.have.length(2);
			});
		});

		describe("Nicht-Teilnehmer", () => {
			beforeEach(function () {
				skipIf(this, !outsider, "Übersprungen: kein aktiver Studierender ausserhalb der LV.");
				resetNotenState(ctx, [outsider]);
			});

			// The reset is the check: It clears what the request wrote and counts it.
			const expectNothingWritten = () =>
				resetNotenState(ctx, [outsider]).then((deleted) => {
					expect(deleted.deletedLvGesamtnoten, `LV-Noten von ${outsider}`).to.eq(0);
					expect(deleted.deletedPruefungen, `Prüfungen von ${outsider}`).to.eq(0);
				});

			it("saveStudentPruefung legt nichts an", () => {
				terminFor(outsider, a.lehreinheit_id).then((response) =>
					expectNotenError(response, "c4pruefungNichtGespeichert"),
				);
				expectNothingWritten();
			});

			it("createPruefungen lehnt nur die Zeile des Nicht-Teilnehmers ab", () => {
				resetNotenState(ctx);

				notenApi
					.createPruefungen(
						[
							{ uid: a.uid, lehreinheit_id: a.lehreinheit_id },
							{ uid: outsider, lehreinheit_id: a.lehreinheit_id },
						],
						attemptDate(ctx, 1),
						ctx.lvId,
						ctx.semKurzbz,
					)
					.then((response) => {
						const data = expectNotenSuccess(response, "createPruefungen");
						expectBulkRowError(data, outsider, "c4pruefungNichtGespeichert");
						expect(data[a.uid].savedPruefung, `Prüfung für ${a.uid}`).to.exist;
					});
				expectNothingWritten();
			});

			it("savePruefungenBulk lehnt nur die Zeile des Nicht-Teilnehmers ab", function () {
				requireNotenMode(this, ctx);

				const bulkRow = (uid) => ({
					uid,
					note: ctx.notes.negativ,
					punkte: null,
					datum: attemptDate(ctx, 1),
					lehreinheit_id: a.lehreinheit_id,
				});

				resetNotenState(ctx);

				notenApi
					.savePruefungenBulk(ctx.lvId, ctx.semKurzbz, [bulkRow(a.uid), bulkRow(outsider)])
					.then((response) => {
						const data = expectNotenSuccess(response, "savePruefungenBulk");
						expectBulkRowError(data, outsider, "c4pruefungNichtGespeichert");
						expectBulkRowAccepted(data, a.uid);
					});
				expectNothingWritten();
			});

			it("saveNotenvorschlag schreibt keine LV-Note", () => {
				notenApi
					.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, outsider, ctx.notes.negativ)
					.then((response) => expectNotenError(response, "c4pruefungNichtGespeichert"));
				expectNothingWritten();
			});

			it("saveNotenvorschlagBulk lehnt nur die Zeile des Nicht-Teilnehmers ab", function () {
				requireNotenMode(this, ctx);

				resetNotenState(ctx);

				notenApi
					.saveNotenvorschlagBulk(ctx.lvId, ctx.semKurzbz, [
						{ uid: a.uid, note: ctx.gradeNotes[0], punkte: null },
						{ uid: outsider, note: ctx.gradeNotes[0], punkte: null },
					])
					.then((response) => {
						const data = expectNotenSuccess(response, "saveNotenvorschlagBulk");
						expectBulkRowError(data, outsider, "c4pruefungNichtGespeichert");
						expectBulkRowAccepted(data, a.uid);
					});
				expectNothingWritten();
			});
		});

		describe("getLehrendeFuerLehreinheit", () => {
			it("nennt die Lehrenden einer Lehreinheit der LV", () => {
				notenApi.getLehrendeFuerLehreinheit(a.lehreinheit_id, ctx.lvId, ctx.semKurzbz).then((response) => {
					expect(expectNotenSuccess(response, "eigene Lehreinheit"), "Lehrende").to.be.an("array");
				});
			});

			it("lehnt die Lehreinheit einer fremden LV ab", function () {
				skipIf(this, !foreign, "Übersprungen: das Semester hat keine Lehreinheit einer anderen LV.");

				notenApi
					.getLehrendeFuerLehreinheit(foreign.lehreinheit_id, ctx.lvId, ctx.semKurzbz)
					.then((response) => expectNotenError(response, "wrongParameters"));
			});

			it("lehnt eine nicht numerische Lehreinheit ab", () => {
				notenApi
					.getLehrendeFuerLehreinheit("abc", ctx.lvId, ctx.semKurzbz)
					.then((response) => expectNotenError(response, "missingParameters"));
			});
		});
	});

	// The person listed as the grader for the exam and course grade: a valid selection, otherwise, the caller if they
	// teach the lehreinheit, otherwise, the first instructor of the lehreinheit based on the UID.
	describe("die benotende Person", () => {
		// ein Studierender, dessen Lehreinheit mehrere Lehrende hat; der Suite-Benutzer steht nicht an erster Stelle
		let target = null;
		let suiteUid = null;

		before(() => {
			const search = (i) =>
				i >= ctx.students.length
					? cy.wrap(null, { log: false })
					: cy
							.task("noten:db:lehrendeOfLehreinheit", { lehreinheitId: ctx.students[i].lehreinheit_id })
							.then((lehrende) =>
								lehrende.length > 1 && lehrende.includes(suiteUid) && lehrende[0] !== suiteUid
									? { student: ctx.students[i], lehrende }
									: search(i + 1),
							);

			cy.request({
				method: "GET",
				url: "/index.ci.php/api/frontend/v1/AuthInfo/getAuthUID",
				auth: notenAuth(),
			})
				.then((response) => {
					suiteUid = response.body.data.uid;
					return search(0);
				})
				.then((found) => {
					target = found;
				});
		});

		beforeEach(() => {
			expect(
				target,
				"eine Lehreinheit mit mehreren Lehrenden, darunter der Suite-Benutzer (Seeder-Gruppe benotungstool_fixture_erweitert)",
			).to.not.be.null;
			requireDbReset();
			resetNotenState(ctx);
		});

		const terminBody = (mitarbeiter_uid) => ({
			student_uid: target.student.uid,
			note: ctx.notes.negativ,
			punkte: null,
			datum: attemptDate(ctx, 1),
			lva_id: ctx.lvId,
			lehreinheit_id: target.student.lehreinheit_id,
			sem_kurzbz: ctx.semKurzbz,
			pruefung_id: null,
			mitarbeiter_uid,
		});

		const grader = (response) => {
			const [saved] = expectNotenSuccess(response, "Termin");
			return cy.task("noten:db:pruefungMitarbeiter", {
				pruefungId: saved.pruefung_id,
				studentUid: target.student.uid,
			});
		};

		it("trägt den Aufrufer ein, wenn er die Lehreinheit unterrichtet", () => {
			notenApi
				.saveStudentPruefung(terminBody())
				.then(grader)
				.then((uid) => expect(uid, "benotende Person der Prüfung").to.eq(suiteUid));

			readLvGesamtnoteViaDb(ctx, target.student.uid).then((row) => {
				expect(row.mitarbeiter_uid, "benotende Person der LV-Note").to.eq(suiteUid);
			});
		});

		it("trägt die gewählte Lehrperson ein", () => {
			const chosen = target.lehrende.find((uid) => uid !== suiteUid);

			notenApi
				.saveStudentPruefung(terminBody(chosen))
				.then(grader)
				.then((uid) => expect(uid, "benotende Person der Prüfung").to.eq(chosen));
		});

		it("übergeht eine Auswahl ausserhalb der Lehreinheit", () => {
			notenApi
				.saveStudentPruefung(terminBody("keine-lehrperson"))
				.then(grader)
				.then((uid) => expect(uid, "benotende Person der Prüfung").to.eq(suiteUid));
		});

		it("trägt ohne eigene Lehre die erste Lehrperson der Lehreinheit ein", function () {
			requireAssistenz(this);

			// otherwise, the request will be processed using the Suite user's cookie
			cy.clearAllCookies();

			cy.request({
				method: "POST",
				url: `${NOTEN_API}/saveStudentPruefung`,
				body: terminBody(),
				auth: assistenzAuth(),
				failOnStatusCode: false,
			})
				.then(grader)
				.then((uid) => expect(uid, "benotende Person der Prüfung").to.eq(target.lehrende[0]));
		});
	});
});
