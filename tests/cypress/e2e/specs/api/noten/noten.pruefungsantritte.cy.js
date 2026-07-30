/**
 * Prüfungsordnung §1 - Prüfungsantritte (P0, cases 1-7).
 *
 * Rules fire in order A (max Antritte) -> B (chronology) -> C (occurrence limit), so each test
 * builds a state where exactly one can fire; otherwise an earlier rule masks the one under test.
 *
 * Bookkeeping: the first Termin2 add also auto-creates a Termin1 row with the original LV note, so
 * one call produces two attempts. A later Termin2 add updates the existing non-excused Termin2
 * instead of inserting. "entschuldigt" and "Noch nicht eingetragen" never count towards the cap.
 */

import { expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import {
	attemptDate,
	baselineDate,
	loadNotenContext,
	requireDbReset,
	shiftDate,
} from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	attemptsOfTyp,
	editPruefung,
	enabledRetakeTypes,
	givenBaseline,
	readState,
} from "../../../../support/helpers/notenScenario";

describe("Noten API - Prüfungsantritte (Prüfungsordnung §1)", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;

			cy.log(
				`LV ${ctx.lvId} / ${ctx.semKurzbz} | maxAntritte=${ctx.maxAntritte} | ` +
					`retakes=${enabledRetakeTypes(ctx).join(",") || "none"} | ` +
					`entschuldigt=${ctx.notes.entschuldigt}`,
			);

			expect(
				enabledRetakeTypes(ctx).length,
				"needs TERMIN2 or TERMIN3 enabled, else saveStudentPruefung only answers wrongPruefungType",
			).to.be.greaterThan(0);
		});
	});

	// one student per test, so a leaked row cannot reach the next scenario
	const studentFor = (index) => ctx.students[index % ctx.students.length];

	describe("Rule A - maximum number of Prüfungsantritte", () => {
		it("rejects an attempt once the configured maximum is reached", () => {
			const student = studentFor(0);
			const retakes = enabledRetakeTypes(ctx);

			givenBaseline(ctx, student);

			// Antritt 1 is the seeded LV note; each retake type adds one row -> count reaches maxAntritte
			retakes.forEach((typ, i) => {
				addPruefung(ctx, student, {
					note: ctx.gradeNotes[0],
					datum: attemptDate(ctx, i + 1),
					typ,
				}).then((response) => {
					expectNotenSuccess(response, `add ${typ} #${i + 1} (still below the cap)`);
				});
			});

			// one more attempt of the last enabled type -> the cap must now bite
			const lastTyp = retakes[retakes.length - 1];
			addPruefung(ctx, student, {
				note: ctx.gradeNotes[0],
				datum: attemptDate(ctx, retakes.length + 1),
				typ: lastTyp,
			}).then((response) => {
				expectNotenError(response, "maxAntritteReached");
			});

			// and nothing was written for the rejected attempt
			readState(ctx).then((data) => {
				const attempts = attemptsOfTyp(data, student.uid, lastTyp);
				expect(
					attempts.map((p) => String(p.datum).slice(0, 10)),
					`no ${lastTyp} may carry the rejected date`,
				).to.not.include(attemptDate(ctx, retakes.length + 1));
			});
		});
	});

	describe("Rule B - attempts are taken in chronological order", () => {
		// "Noch nicht eingetragen" occupies a date without counting towards the cap, so Rule A
		// cannot fire first and mask Rule B.
		const givenOpenAttemptOn = (student, datum) => {
			givenBaseline(ctx, student);
			addPruefung(ctx, student, {
				note: ctx.notes.nochNichtEingetragen,
				datum,
				typ: "Termin2",
			}).then((response) => {
				expectNotenSuccess(response, "seed an open (uncounted) Termin2");
			});
		};

		it("rejects a new attempt dated on the same day as an existing one", () => {
			const student = studentFor(1);
			const existing = attemptDate(ctx, 2);

			givenOpenAttemptOn(student, existing);

			addPruefung(ctx, student, {
				note: ctx.gradeNotes[0],
				datum: existing,
				typ: "Termin2",
			}).then((response) => {
				expectNotenError(response, "pruefungDatumBeforeExisting");
			});
		});

		it("rejects a new attempt dated before an existing one", () => {
			const student = studentFor(1);
			const existing = attemptDate(ctx, 2);

			givenOpenAttemptOn(student, existing);

			addPruefung(ctx, student, {
				note: ctx.gradeNotes[0],
				datum: attemptDate(ctx, 1),
				typ: "Termin2",
			}).then((response) => {
				expectNotenError(response, "pruefungDatumBeforeExisting");
			});
		});

		it("accepts a new attempt dated after every existing one", () => {
			const student = studentFor(1);

			givenOpenAttemptOn(student, attemptDate(ctx, 2));

			addPruefung(ctx, student, {
				note: ctx.gradeNotes[0],
				datum: attemptDate(ctx, 3),
				typ: "Termin2",
			}).then((response) => {
				expectNotenSuccess(response, "attempt strictly after the existing one");
			});
		});
	});

	describe("Rule C - 'entschuldigt' may be assigned only once", () => {
		it("rejects a second entschuldigt attempt", () => {
			const student = studentFor(2);

			givenBaseline(ctx, student);

			addPruefung(ctx, student, {
				note: ctx.notes.entschuldigt,
				datum: attemptDate(ctx, 1),
				typ: "Termin2",
			}).then((response) => {
				expectNotenSuccess(response, "first entschuldigt attempt");
			});

			addPruefung(ctx, student, {
				note: ctx.notes.entschuldigt,
				datum: attemptDate(ctx, 2),
				typ: "Termin2",
			}).then((response) => {
				expectNotenError(response, "noteOccuranceLimitReached");
			});

			readState(ctx).then((data) => {
				const excused = attemptsOfTyp(data, student.uid, "Termin2").filter(
					(p) => String(p.note) === String(ctx.notes.entschuldigt),
				);
				expect(excused, "exactly one excused Termin2 may exist").to.have.length(1);
			});
		});
	});

	describe("entschuldigt does not consume an attempt", () => {
		it("still accepts a real grade after an excused attempt", () => {
			const student = studentFor(0);

			givenBaseline(ctx, student);

			addPruefung(ctx, student, {
				note: ctx.notes.entschuldigt,
				datum: attemptDate(ctx, 1),
				typ: "Termin2",
			}).then((response) => {
				expectNotenSuccess(response, "excused attempt");
			});

			// excused must not count, so this real grade is still within the cap
			addPruefung(ctx, student, {
				note: ctx.gradeNotes[0],
				datum: attemptDate(ctx, 2),
				typ: "Termin2",
			}).then((response) => {
				expectNotenSuccess(
					response,
					"a real grade after an excused attempt (excused must not count towards the cap)",
				);
			});
		});

		it("preserves the excused entry as its own dated row instead of overwriting it", () => {
			const student = studentFor(0);

			givenBaseline(ctx, student);

			addPruefung(ctx, student, {
				note: ctx.notes.entschuldigt,
				datum: attemptDate(ctx, 1),
				typ: "Termin2",
			});
			addPruefung(ctx, student, {
				note: ctx.gradeNotes[0],
				datum: attemptDate(ctx, 2),
				typ: "Termin2",
			});

			readState(ctx).then((data) => {
				const termin2 = attemptsOfTyp(data, student.uid, "Termin2");

				expect(termin2, "the excused row and the new row must coexist").to.have.length(2);

				expect(String(termin2[0].note), "older row keeps the excused grade").to.eq(
					String(ctx.notes.entschuldigt),
				);
				expect(String(termin2[0].datum).slice(0, 10)).to.eq(attemptDate(ctx, 1));

				expect(String(termin2[1].note), "newer row carries the real grade").to.eq(
					String(ctx.gradeNotes[0]),
				);
				expect(String(termin2[1].datum).slice(0, 10)).to.eq(attemptDate(ctx, 2));
			});
		});
	});

	describe("edit guards", () => {
		/**
		 * Builds: Termin1 (baseline date) < excused Termin2 (attempt 1) < real Termin2 (attempt 2)
		 * and yields the excused row, which now has both an earlier and a later neighbour.
		 */
		const givenThreeAttempts = (student) => {
			givenBaseline(ctx, student);

			addPruefung(ctx, student, {
				note: ctx.notes.entschuldigt,
				datum: attemptDate(ctx, 1),
				typ: "Termin2",
			});
			addPruefung(ctx, student, {
				note: ctx.gradeNotes[0],
				datum: attemptDate(ctx, 2),
				typ: "Termin2",
			});

			return readState(ctx).then((data) => {
				const excused = attemptsOfTyp(data, student.uid, "Termin2").find(
					(p) => String(p.note) === String(ctx.notes.entschuldigt),
				);
				expect(excused, "excused Termin2 to edit").to.exist;
				return excused;
			});
		};

		it("rejects changing the grade once a later attempt exists", () => {
			const student = studentFor(1);

			givenThreeAttempts(student).then((excused) => {
				editPruefung(ctx, student, {
					pruefungId: excused.pruefung_id,
					note: ctx.gradeNotes[1], // different grade -> locked
					datum: attemptDate(ctx, 1),
					typ: "Termin2",
				}).then((response) => {
					expectNotenError(response, "pruefungNoteLocked");
				});
			});
		});

		it("still allows a date-only correction between the neighbouring attempts", () => {
			const student = studentFor(1);

			givenThreeAttempts(student).then((excused) => {
				// same note -> the lock does not apply; date stays strictly inside (baseline, attempt2)
				editPruefung(ctx, student, {
					pruefungId: excused.pruefung_id,
					note: excused.note,
					datum: shiftDate(attemptDate(ctx, 1), 3),
					typ: "Termin2",
				}).then((response) => {
					// NOTE: savePruefungstermin() does not receive pruefung_id - it re-derives the target
					// as "the first non-excused Termin2" (Noten.php:943-968). The validation under test
					// is what this asserts; the resulting row placement is covered separately.
					expectNotenSuccess(response, "date-only correction inside the neighbour bounds");
				});
			});
		});

		it("rejects a date on or before the previous attempt", () => {
			const student = studentFor(2);

			givenThreeAttempts(student).then((excused) => {
				editPruefung(ctx, student, {
					pruefungId: excused.pruefung_id,
					note: excused.note,
					datum: baselineDate(ctx), // == the Termin1 date -> not strictly after
					typ: "Termin2",
				}).then((response) => {
					expectNotenError(response, "pruefungDatumOutOfRange");
				});
			});
		});

		it("rejects a date on or after the following attempt", () => {
			const student = studentFor(2);

			givenThreeAttempts(student).then((excused) => {
				editPruefung(ctx, student, {
					pruefungId: excused.pruefung_id,
					note: excused.note,
					datum: attemptDate(ctx, 2), // == the later Termin2 -> not strictly before
					typ: "Termin2",
				}).then((response) => {
					expectNotenError(response, "pruefungDatumOutOfRange");
				});
			});
		});
	});
});
