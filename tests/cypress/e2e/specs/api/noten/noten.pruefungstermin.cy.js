/**
 * savePruefungstermin - the write path shared by saveStudentPruefung, createPruefungen and
 * savePruefungenBulk. The existing specs cover the validators; this one covers the writer.
 */

import {
	expectBulkRowError,
	expectNotenError,
	expectNotenSuccess,
} from "../../../../support/helpers/notenErrors";
import {
	attemptDate,
	baselineDate,
	loadNotenContext,
	readLvGesamtnote,
	requireDbReset,
	resetNotenState,
	seedPruefung,
	shiftDate,
} from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	attemptsOfTyp,
	editPruefung,
	givenBaseline,
	readState,
} from "../../../../support/helpers/notenScenario";
import { notenApi, pruefungenOfTyp } from "../../../../support/api/notenApi";

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
		// An excused and a real Termin2 side by side - no endpoint builds this, hence the direct seed
		const student = studentFor(2);
		const excusedDate = attemptDate(ctx, 1);
		const gradedDate = attemptDate(ctx, 2);
		const movedTo = shiftDate(excusedDate, 5);

		givenBaseline(ctx, student);

		let excusedId;
		seedPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum: excusedDate, typ: "Termin2" })
			.then((seeded) => {
				console.log('ctx', ctx)
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

	it("updates the existing Termin2 instead of appending a second one", () => {
		// The cap has room (Termin3 is on here), so the second add is allowed and must land on the
		// same row - the update branch is otherwise only reached through an edit.
		const student = studentFor(0);

		givenBaseline(ctx, student);

		let firstId;
		addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: attemptDate(ctx, 1) })
			.then((response) => {
				firstId = expectNotenSuccess(response, "first Termin2")[0].pruefung_id;
				return addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 2) });
			})
			.then((response) => expectNotenSuccess(response, "second Termin2"))
			.then(() => readState(ctx))
			.then((data) => {
				const rows = attemptsOfTyp(data, student.uid, "Termin2");

				expect(rows, "the second add must not append a row").to.have.length(1);
				expect(rows[0].pruefung_id, "same row").to.eq(firstId);
				expect(String(rows[0].note)).to.eq(String(ctx.gradeNotes[1]));
				expect(dayOf(rows[0].datum)).to.eq(attemptDate(ctx, 2));
			});
	});

	it("stores an empty note as 'Noch nicht eingetragen'", () => {
		const student = studentFor(1);

		givenBaseline(ctx, student);

		addPruefung(ctx, student, { note: "", datum: attemptDate(ctx, 1) }).then((response) => {
			const [saved] = expectNotenSuccess(response, "Termin2 without a grade");
			expect(String(saved.note), "empty note is normalised").to.eq(
				String(ctx.notes.nochNichtEingetragen),
			);
		});
	});

	describe("invalid pruefungstyp", () => {
		// The only server-side typ check: which typ an attempt becomes is otherwise decided purely by
		// the frontend (getPruefungstypForStudentByAntritt).
		it("refuses a typ that is not an enabled retake", () => {
			const student = studentFor(2);

			givenBaseline(ctx, student);

			// Termin1 is the snapshot, kommPruef is entered elsewhere, the third is nonsense
			["Termin1", "kommPruef", "NichtsDergleichen"].forEach((typ) => {
				addPruefung(ctx, student, {
					note: ctx.gradeNotes[1],
					datum: attemptDate(ctx, 1),
					typ,
				}).then((response) => {
					expectNotenError(response, "wrongPruefungType");
				});
			});
		});

		
		it("leaves the LV note untouched when the typ is rejected", () => {
			const student = studentFor(3);

			givenBaseline(ctx, student);

			addPruefung(ctx, student, {
				note: ctx.gradeNotes[1],
				datum: attemptDate(ctx, 1),
				typ: "NichtsDergleichen",
			})
				.then((response) => expectNotenError(response, "wrongPruefungType"))
				.then(() => readLvGesamtnote(ctx, student.uid))
				.then((row) => {
					expect(String(row.note), "a rejected request must not re-grade the student").to.eq(
						String(ctx.gradeNotes[0]),
					);
				});
		});
	});

	it("refuses a Prüfung for a student who has no LV note yet", () => {
		// Only reachable through the bulk path: saveStudentPruefung inserts the missing LV note itself,
		// so its own guard (Noten.php:839) can never fire.
		const student = studentFor(0);

		resetNotenState(ctx);

		notenApi
			.createPruefungen(
				[{ uid: student.uid, typ: "Termin2", lehreinheit_id: student.lehreinheit_id }],
				attemptDate(ctx, 1),
				ctx.lvId,
				ctx.semKurzbz,
			)
			.then((response) => {
				const data = expectNotenSuccess(response, "createPruefungen without an LV note");
				expectBulkRowError(data, student.uid, "c4keineLvNoteEingetragen");
			});
	});
});
