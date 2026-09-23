/**
 * Two requests at the same time for the same student in the same LV.
 *
 * Noten::beginStudentTransaction takes a transaction-wide advisory lock. The second request waits and reads
 * the state after the first commit. One run proves little for a race condition, so each test repeats it.
 */

import { loginAsLektor } from "../../../../support/api/notenApi";
import { expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import { requireConfig, requireRepeat } from "../../../../support/helpers/notenConfig";
import {
	antrittDate,
	baselineDate,
	loadNotenContext,
	requireDbReset,
	resetNotenState,
} from "../../../../support/helpers/notenTestData";
import { pruefungenOf, givenBaseline, readStateViaApi } from "../../../../support/helpers/notenScenario";

const ROUNDS = 5;

const concurrently = (path, bodies) => cy.task("noten:http:postParallel", { path, bodies });

describe("Noten API - gleichzeitige Schreibvorgänge", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((loaded) => {
			ctx = loaded;
		});
	});

	beforeEach(() => loginAsLektor());

	it("legt bei zwei gleichzeitigen Terminen am selben Tag nur einen an", function () {
		requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNG_GLEICHER_TAG", false);
		requireRepeat(this, ctx);

		const student = ctx.students[0];
		const datum = antrittDate(ctx, 1);

		// 'entschuldigt' uses no Antritt. So after the first commit only the date rule rejects the
		// second Pruefung, whatever maxAntritte and the kommPruef rule say.
		const body = {
			student_uid: student.uid,
			note: ctx.noten.entschuldigt,
			punkte: null,
			datum,
			lv_id: ctx.lvId,
			lehreinheit_id: student.lehreinheit_id,
			sem_kurzbz: ctx.semKurzbz,
			pruefung_id: null,
		};

		Cypress._.times(ROUNDS, (i) => {
			givenBaseline(ctx, student);

			concurrently("savePruefung", [body, body]).then((responses) => {
				const [accepted, rejected] = [...responses].sort((x, y) => x.status - y.status);
				expectNotenSuccess(accepted, `Durchlauf ${i + 1}: erster Request`);
				expectNotenError(rejected, "pruefungDatumBeforeExisting");
			});

			readStateViaApi(ctx).then((data) => {
				const onDay = pruefungenOf(data, student.uid).filter((p) => String(p.datum).slice(0, 10) === datum);
				expect(onDay, `Durchlauf ${i + 1}: Termine am ${datum}`).to.have.length(1);
			});
		});
	});

	it("schreibt bei zwei gleichzeitigen Übernahmen nur einen Antritt 1", function () {
		// without ERSTANTRITT_BEI_UEBERNAHME the proposal writes no Antritt 1, so there is no race
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

			concurrently("saveLvNote", [body(ctx.notenScale[0]), body(ctx.notenScale[1])]).then((responses) => {
				responses.forEach((response, n) =>
					expectNotenSuccess(response, `Durchlauf ${i + 1}, Request ${n + 1}`),
				);
			});

			readStateViaApi(ctx).then((data) => {
				expect(pruefungenOf(data, student.uid), `Durchlauf ${i + 1}: Antritte`).to.have.length(1);
			});
		});
	});
});
