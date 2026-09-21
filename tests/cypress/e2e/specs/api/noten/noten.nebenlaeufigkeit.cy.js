/**
 * Two concurrent requests for the same student in the same course.
 *
 * Grades::lockStudent places a transaction-wide advisory lock. The second request waits and
 * then reads the state after the first commit. A single run proves little in the event of a race condition,
 * so each test repeats the process.
 */

import { expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import { requireConfig, requireWiederholung } from "../../../../support/helpers/notenConfig";
import {
	attemptDate,
	baselineDate,
	loadNotenContext,
	requireDbReset,
	resetNotenState,
} from "../../../../support/helpers/notenTestData";
import { attemptsOfStudent, givenBaseline, readStateViaApi } from "../../../../support/helpers/notenScenario";

const ROUNDS = 5;

const concurrently = (path, bodies) => cy.task("noten:http:parallel", { path, bodies });

describe("Noten API - gleichzeitige Schreibvorgänge", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;
		});
	});

	it("legt bei zwei gleichzeitigen Terminen am selben Tag nur einen an", function () {
		requireConfig(this, ctx, "CIS_GESAMTNOTE_TERMIN_GLEICHER_TAG", false);
		requireWiederholung(this, ctx);

		const student = ctx.students[0];
		const datum = attemptDate(ctx, 1);

		// ‘entschuldigt’ does not count as an antritt. Therefore, after the first commit, only the
		// date rule rejects the second date, regardless of maxAppearances and the committee rule.
		const body = {
			student_uid: student.uid,
			note: ctx.notes.entschuldigt,
			punkte: null,
			datum,
			lva_id: ctx.lvId,
			lehreinheit_id: student.lehreinheit_id,
			sem_kurzbz: ctx.semKurzbz,
			pruefung_id: null,
		};

		Cypress._.times(ROUNDS, (i) => {
			givenBaseline(ctx, student);

			concurrently("saveStudentPruefung", [body, body]).then((responses) => {
				const [accepted, rejected] = [...responses].sort((x, y) => x.status - y.status);
				expectNotenSuccess(accepted, `Durchlauf ${i + 1}: erster Request`);
				expectNotenError(rejected, "pruefungDatumBeforeExisting");
			});

			readStateViaApi(ctx).then((data) => {
				const onDay = attemptsOfStudent(data, student.uid).filter(
					(p) => String(p.datum).slice(0, 10) === datum,
				);
				expect(onDay, `Durchlauf ${i + 1}: Termine am ${datum}`).to.have.length(1);
			});
		});
	});

	it("schreibt bei zwei gleichzeitigen Übernahmen nur einen Antritt 1", function () {
		// Without an initial call, the transfer does not set a time that could cause a race condition
		requireConfig(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", true);

		const student = ctx.students[1];
		const body = (note) => ({
			lv_id: ctx.lvId,
			sem_kurzbz: ctx.semKurzbz,
			student_uid: student.uid,
			note,
			punkte: null,
			datum: baselineDate(ctx),
		});

		Cypress._.times(ROUNDS, (i) => {
			resetNotenState(ctx);

			concurrently("saveNotenvorschlag", [body(ctx.gradeNotes[0]), body(ctx.gradeNotes[1])]).then((responses) => {
				responses.forEach((response, n) =>
					expectNotenSuccess(response, `Durchlauf ${i + 1}, Request ${n + 1}`),
				);
			});

			readStateViaApi(ctx).then((data) => {
				expect(attemptsOfStudent(data, student.uid), `Durchlauf ${i + 1}: Antritte`).to.have.length(1);
			});
		});
	});
});
