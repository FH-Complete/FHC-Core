/**
 * Examination Regulations §1 - Grade Entry Deadline.
 *
 * Two deadlines address two issues: CIS_GESAMTNOTE_FRIST_EINGABE restricts the time of entry,
 * CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM restricts the exam date. The entry deadline applies in every write path
 * immediately after the access check; a rejected request therefore does not write anything. The message specifies the deadline
 * and uses it to verify its derivation from NOTENEINTRAGUNGSFRIST_SS/WS.
 *
 * The seeder group benotungstool_fixture_erweitert creates the summer semester in which the test user is notified after the deadline.
 */

import { notenApi } from "../../../../support/api/notenApi";
import { expectNotenError, expectNotenSuccess, messageMatchesPhrase } from "../../../../support/helpers/notenErrors";
import {
	attemptDate,
	expectedFristString,
	fristHasPassed,
	loadNotenContext,
	requireDbReset,
	teachingSemesterWithExpiredFrist,
} from "../../../../support/helpers/notenTestData";
import { addPruefung, givenBaseline } from "../../../../support/helpers/notenScenario";
import { requireConfig, requireWiederholung } from "../../../../support/helpers/notenConfig";

describe("Noten API - Noteneintragungsfrist (Prüfungsordnung §1)", () => {
	let ctx;
	// Semester, including the course, in which the user teaches and the deadline has passed
	const expired = { SS: null, WS: null };

	const fristSS = () => ctx.cisConfig.NOTENEINTRAGUNGSFRIST_SS;
	const fristWS = () => ctx.cisConfig.NOTENEINTRAGUNGSFRIST_WS;

	before(() => {
		loadNotenContext().then((context) => {
			ctx = context;
			cy.log(
				`FRIST_EINGABE=${ctx.cisConfig.CIS_GESAMTNOTE_FRIST_EINGABE} ` +
					`FRIST_PRUEFUNGSDATUM=${ctx.cisConfig.CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM} ` +
					`AUSNAHME_GILT=${ctx.cisConfig.CIS_GESAMTNOTE_FRIST_AUSNAHME_GILT}`,
			);

			["SS", "WS"].forEach((type) =>
				teachingSemesterWithExpiredFrist(type, fristSS(), fristWS()).then((pair) => {
					expired[type] = pair;
					cy.log(`${type} mit abgelaufener Frist: ${JSON.stringify(pair)}`);
				}),
			);
		});
	});

	// A missing semester is a fixture error, not a skipped test
	const pairFor = (type) => {
		expect(expired[type], `ein ${type} mit abgelaufener Frist, in dem der Testbenutzer unterrichtet`).to.not.be
			.null;
		return expired[type];
	};

	// §7 and §11: The commission review must TAKE PLACE by the deadline. That is a different question from
	// “Can it still be registered now?”
	describe("Prüfungsdatum nach der Frist", () => {
		beforeEach(() => requireDbReset());

		// The deadline for the current semester is before this date
		const afterFrist = () => `${Number(ctx.semKurzbz.slice(2, 6)) + 1}-12-31`;

		it("lehnt einen Termin ab, der nach der Frist liegt", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM", true);

			const student = ctx.students[0];

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: afterFrist() }).then((response) =>
				expectNotenError(response, "pruefungsdatumNachFrist"),
			);
		});

		it("nimmt einen Termin nach der Frist an, wenn das Prüfungsdatum an keine Frist gebunden ist", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM", false);
			requireWiederholung(this, ctx);

			const student = ctx.students[0];

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.notes.negativ, datum: afterFrist() }).then((response) =>
				expectNotenSuccess(response, "Termin nach der Frist"),
			);
		});

		it("nimmt einen Termin vor der Frist an", function () {
			requireWiederholung(this, ctx);

			const student = ctx.students[1];

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "Termin innerhalb der Frist"),
			);
		});
	});

	describe("Eingabe nach der Frist", () => {
		const s0 = () => ctx.students[0];

		// The student is not enrolled in the course from the previous semester. If the submission deadline
		// does not apply, the system rejects the student's attempt, and the request still does not write anything.
		const writePaths = {
			saveStudentPruefung: (pair) =>
				notenApi.saveStudentPruefung({
					student_uid: s0().uid,
					note: ctx.gradeNotes[0],
					punkte: null,
					datum: attemptDate(ctx, 1),
					lva_id: pair.lvId,
					lehreinheit_id: s0().lehreinheit_id,
					sem_kurzbz: pair.semKurzbz,
					pruefung_id: null,
				}),
			saveNotenvorschlag: (pair) =>
				notenApi.saveNotenvorschlag(pair.lvId, pair.semKurzbz, s0().uid, ctx.gradeNotes[0]),
			saveNotenvorschlagBulk: (pair) =>
				notenApi.saveNotenvorschlagBulk(pair.lvId, pair.semKurzbz, [
					{ uid: s0().uid, note: ctx.gradeNotes[0], punkte: null },
				]),
			createPruefungen: (pair) =>
				notenApi.createPruefungen(
					[{ uid: s0().uid, lehreinheit_id: s0().lehreinheit_id }],
					attemptDate(ctx, 1),
					pair.lvId,
					pair.semKurzbz,
				),
			savePruefungenBulk: (pair) =>
				notenApi.savePruefungenBulk(pair.lvId, pair.semKurzbz, [
					{
						uid: s0().uid,
						note: ctx.gradeNotes[0],
						punkte: null,
						datum: attemptDate(ctx, 1),
						lehreinheit_id: s0().lehreinheit_id,
					},
				]),
		};

		const reportsFrist = (response) =>
			((response.body && response.body.errors) || []).some((e) =>
				messageMatchesPhrase(e.message, "noteneintragungsfristVorbei"),
			);

		Object.entries(writePaths).forEach(([writePath, call]) => {
			["SS", "WS"].forEach((type) => {
				it(`${writePath} lehnt eine Eingabe im ${type} nach der Frist ab`, function () {
					requireConfig(this, ctx, "CIS_GESAMTNOTE_FRIST_EINGABE", true);
					requireConfig(this, ctx, "CIS_GESAMTNOTE_FRIST_AUSNAHME_GILT", false);

					const pair = pairFor(type);
					expect(
						fristHasPassed(pair.semKurzbz, fristSS(), fristWS()),
						`die Frist von ${pair.semKurzbz} ist vorbei`,
					).to.be.true;

					call(pair).then((response) => {
						expectNotenError(response, "noteneintragungsfristVorbei");

						const errorMessage = response.body.errors.map((e) => e.message).join(" | ");
						expect(errorMessage, `die Frist, die der Server für ${pair.semKurzbz} ableitet`).to.include(
							expectedFristString(pair.semKurzbz, fristSS(), fristWS()),
						);
					});
				});
			});

			// The exception covers the INPUT DATE, not the exam date.
			it(`${writePath} lässt eine Ausnahmerolle nach der Frist eintragen`, function () {
				requireConfig(this, ctx, "CIS_GESAMTNOTE_FRIST_EINGABE", true);
				requireConfig(this, ctx, "CIS_GESAMTNOTE_FRIST_AUSNAHME_GILT", true);

				call(pairFor("SS")).then((response) => {
					expect(reportsFrist(response), "die Eingabefrist greift nicht").to.be.false;
				});
			});

			it(`${writePath} nimmt ohne Eingabefrist eine späte Eingabe an`, function () {
				requireConfig(this, ctx, "CIS_GESAMTNOTE_FRIST_EINGABE", false);

				call(pairFor("SS")).then((response) => {
					expect(reportsFrist(response), "die Eingabefrist greift nicht").to.be.false;
				});
			});
		});
	});
});
