/**
 * savePruefungstermin - the write path shared by saveStudentPruefung, createPruefungen and
 * savePruefungenBulk. The existing specs cover the validators; this one covers the writer.
 */

import { expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import {
	attemptDate,
	baselineDate,
	loadNotenContext,
	requireDbReset,
	seedPruefung,
	shiftDate,
} from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	editPruefung,
	givenBaseline,
	readState,
} from "../../../../support/helpers/notenScenario";
import { pruefungenOfTyp } from "../../../../support/api/notenApi";

const dayOf = (value) => String(value).slice(0, 10);

describe("Noten API - savePruefungstermin (write path)", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;
			expect(
				context.cisConfig.CIS_GESAMTNOTE_PRUEFUNG_TERMIN2,
				"needs TERMIN2 enabled - every test here writes a Termin2",
			).to.be.ok;
		});
	});

	const studentFor = (index) => ctx.students[index % ctx.students.length];

	it("auto-creates Termin1 carrying the original LV note and its benotungsdatum", () => {
		const student = studentFor(0);

		givenBaseline(ctx, student);

		addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) }).then((response) => {
			const [saved, , extra] = expectNotenSuccess(response, "first Termin2");

			expect(extra, "Termin1 snapshot in extraPruefung").to.exist;
			expect(extra.pruefungstyp_kurzbz).to.eq("Termin1");
			expect(String(extra.note), "snapshot keeps the pre-retake LV note").to.eq(String(ctx.gradeNotes[0]));
			expect(dayOf(extra.datum), "snapshot keeps the original benotungsdatum").to.eq(baselineDate(ctx));

			expect(saved.pruefungstyp_kurzbz).to.eq("Termin2");
			expect(String(saved.note)).to.eq(String(ctx.gradeNotes[1]));
		});
	});

	it("returns extraPruefung null once Termin1 exists", () => {
		// On PHP 8 the null lands in count() (Noten.php:822) and turns this into a 500.
		const student = studentFor(1);

		givenBaseline(ctx, student);

		addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) })
			.then((response) => expectNotenSuccess(response, "first Termin2")[0])
			.then((created) =>
				editPruefung(ctx, student, {
					pruefungId: created.pruefung_id,
					note: ctx.gradeNotes[0],
					datum: attemptDate(ctx, 1),
				}),
			)
			.then((response) => {
				const [saved, lvgesamtnote, extra] = expectNotenSuccess(response, "edit of the Termin2");

				expect(saved, "savedPruefung").to.exist;
				expect(extra, "no second Termin1 is created").to.be.null;
				expect(lvgesamtnote, "lvgesamtnote").to.exist;
			});
	});

	it("edits the pruefung identified by pruefung_id", () => {
		// An excused and a real Termin2 side by side - no endpoint builds this, hence the direct seed.
		const student = studentFor(2);
		const excusedDate = attemptDate(ctx, 1);
		const gradedDate = attemptDate(ctx, 2);
		const movedTo = shiftDate(excusedDate, 5);

		givenBaseline(ctx, student);

		let excusedId;
		seedPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum: excusedDate, typ: "Termin2" })
			.then((seeded) => {
				excusedId = seeded.pruefungId;
				return seedPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: gradedDate, typ: "Termin2" });
			})
			// note stays entschuldigt: a later pruefung exists, so only the datum may move
			.then(() =>
				editPruefung(ctx, student, {
					pruefungId: excusedId,
					note: ctx.notes.entschuldigt,
					datum: movedTo,
				}),
			)
			.then((response) => expectNotenSuccess(response, "moving the excused Termin2"))
			.then(() => readState(ctx))
			.then((data) => {
				const rows = pruefungenOfTyp(data, student.uid, "Termin2");
				const excused = rows.find((p) => p.pruefung_id === excusedId);
				const graded = rows.find((p) => p.pruefung_id !== excusedId);

				// the untargeted row first: silently rewriting a real grade is the worse outcome
				expect(graded, "the other Termin2 still exists").to.exist;
				expect(String(graded.note), "the untargeted Termin2 keeps its grade").to.eq(
					String(ctx.gradeNotes[0]),
				);
				expect(dayOf(graded.datum), "the untargeted Termin2 keeps its date").to.eq(gradedDate);

				expect(excused, "the excused Termin2 still exists").to.exist;
				expect(dayOf(excused.datum), "the row named by pruefung_id moved").to.eq(movedTo);
			});
	});

	it("adds an attempt while the LV note is still offen", () => {
		// freigegeben:false -> getLvGesamtNoten hides the row, so the writer takes the INSERT branch
		// against an existing primary key.
		const student = studentFor(3);

		givenBaseline(ctx, student, { freigegeben: false });

		addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) }).then((response) => {
			expectNotenSuccess(response, "Termin2 on an un-freigegebene LV note");
		});
	});
});
