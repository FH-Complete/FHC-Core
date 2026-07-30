/**
 * Prüfungsordnung §1 - Noteneintragungsfrist (P0, cases 8-9).
 *
 * enforceNoteneintragungsfrist runs after assertLvAccess but before any write, so a request naming
 * a past-deadline semester is rejected without touching a row. The message carries the derived
 * deadline, which doubles as a check of computeNoteneintragungsfrist
 * (SS -> 15.11.yyyy, WS -> 15.05.yyyy+1).
 */

import { notenApi } from "../../../../support/api/notenApi";
import { expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import {
	attemptDate,
	expectedFristString,
	fristHasPassed,
	loadNotenContext,
	requireDbReset,
	semesterWithPastFrist,
} from "../../../../support/helpers/notenTestData";
import { addPruefung, givenBaseline } from "../../../../support/helpers/notenScenario";

// Deadlines computeNoteneintragungsfrist derives: SS -> 15.11.yyyy, WS -> 15.05.yyyy+1.
const fristConfig = () => ({ ss: { month: 11, day: 15 }, ws: { month: 5, day: 15 } });

describe("Noten API - Noteneintragungsfrist (Prüfungsordnung §1)", () => {
	let ctx;
	let enforced;

	before(() => {
		loadNotenContext().then((context) => {
			ctx = context;
			enforced = Boolean(ctx.cisConfig.CIS_GESAMTNOTE_NOTENEINTRAGUNGSFRIST);
			cy.log(`CIS_GESAMTNOTE_NOTENEINTRAGUNGSFRIST = ${enforced}`);
		});
	});

	describe("deadline has passed", () => {
		// A rejected request writes nothing, so these need no DB reset.
		[
			{ type: "SS", label: "Sommersemester (deadline in the same year)" },
			{ type: "WS", label: "Wintersemester (deadline in the following year)" },
		].forEach(({ type, label }) => {
			it(`rejects grade entry for a ${label}`, function () {
				// no flag, no deadline - skip rather than pretend it passed
				if (!enforced) this.skip();

				const sem = semesterWithPastFrist(type);
				const cfg = fristConfig();
				const expectedDeadline = expectedFristString(sem, cfg.ss, cfg.ws);

				expect(fristHasPassed(sem), `${sem} deadline is in the past`).to.be.true;

				notenApi
					.saveStudentPruefung({
						student_uid: ctx.students[0].uid,
						note: ctx.gradeNotes[0],
						punkte: null,
						datum: attemptDate(ctx, 1),
						lva_id: ctx.lvId,
						lehreinheit_id: ctx.students[0].lehreinheit_id,
						sem_kurzbz: sem,
						typ: "Termin2",
						pruefung_id: null,
					})
					.then((response) => {
						expectNotenError(response, "noteneintragungsfristVorbei");

						const message = response.body.errors.map((e) => e.message).join(" | ");
						expect(
							message,
							`the ${type} deadline the server derived for ${sem}`,
						).to.include(expectedDeadline);
					});
			});
		});

		it("rejects saveNotenvorschlag past the deadline as well", function () {
			if (!enforced) this.skip();

			const sem = semesterWithPastFrist("SS");

			notenApi
				.saveNotenvorschlag(ctx.lvId, sem, ctx.students[0].uid, ctx.gradeNotes[0])
				.then((response) => {
					expectNotenError(response, "noteneintragungsfristVorbei");
				});
		});
	});

	describe("deadline is still ahead", () => {
		before(() => {
			requireDbReset();
		});

		it("accepts grade entry in a semester whose deadline has not passed", function () {
			if (enforced && fristHasPassed(ctx.semKurzbz)) {
				// writing is impossible here; the whole mutating suite would be blocked too
				throw new Error(
					`Noteneintragungsfrist enforcement is ON and the deadline for the test semester ` +
						`${ctx.semKurzbz} (${expectedFristString(ctx.semKurzbz)}) has already passed, so no ` +
						`grade can be entered. Point NOTEN_SEM at a semester whose deadline is still ahead.`,
				);
			}

			const student = ctx.students[0];

			givenBaseline(ctx, student);

			addPruefung(ctx, student, {
				note: ctx.gradeNotes[0],
				datum: attemptDate(ctx, 1),
				typ: "Termin2",
			}).then((response) => {
				expectNotenSuccess(response, `grade entry within the deadline for ${ctx.semKurzbz}`);
			});
		});
	});

	describe("deadline computation", () => {
		it("derives the documented SS and WS deadlines", () => {
			const cfg = fristConfig();

			// pins the mapping, independent of the clock
			expect(expectedFristString("SS2025", cfg.ss, cfg.ws)).to.eq("15.11.2025");
			expect(expectedFristString("WS2025", cfg.ss, cfg.ws)).to.eq("15.05.2026");
		});
	});
});
