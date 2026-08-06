/**
 * savePruefungFuerStudent / savePruefungstermin - the write path shared by saveStudentPruefung,
 * createPruefungen and savePruefungenBulk. The other specs cover the validators; this one covers
 * the writer.
 *
 * Core invariant since the Prüfungsverlauf refactor: one action writes exactly one Prüfung. The
 * former Termin1 snapshot is gone - Antritt 1 is created by the password-gated Freigabe.
 */

import { expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import {
	attemptDate,
	loadNotenContext,
	readLvGesamtnote,
	requireDbReset,
	resetNotenState,
	seedPruefung,
	shiftDate,
} from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	attemptsOfStudent,
	editPruefung,
	givenBaseline,
	readState,
	verlaufOfStudent,
} from "../../../../support/helpers/notenScenario";
import { notenApi, pruefungenOf } from "../../../../support/api/notenApi";

const dayOf = (value) => String(value).slice(0, 10);

describe("Noten API - Prüfungstermin (write path)", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;
			expect(context.maxAntritte, "needs room for at least one retake").to.be.greaterThan(1);
		});
	});

	const studentFor = (index) => ctx.students[index % ctx.students.length];

	it("writes exactly one Prüfung per add - no snapshot alongside it", () => {
		const student = studentFor(0);

		givenBaseline(ctx, student);

		addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) })
			.then((response) => {
				const [saved, lvgesamtnote, verlauf] = expectNotenSuccess(response, "add an attempt");

				expect(saved, "savedPruefung").to.exist;
				expect(String(saved.note)).to.eq(String(ctx.gradeNotes[1]));
				expect(lvgesamtnote, "lvgesamtnote is returned so the client can refresh the row").to.exist;
				expect(verlauf, "verlauf is returned").to.exist;
				expect(verlauf.pruefungen, "verlauf carries the attempts").to.be.an("array");
			})
			.then(() => readState(ctx))
			.then((data) => {
				const attempts = attemptsOfStudent(data, student.uid);
				expect(attempts, "baseline Antritt 1 plus exactly one added row").to.have.length(2);
			});
	});

	it("derives position and Antrittsnummer server-side", () => {
		const student = studentFor(1);

		givenBaseline(ctx, student);

		addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) })
			.then(() => readState(ctx))
			.then((data) => {
				const attempts = attemptsOfStudent(data, student.uid);

				expect(attempts.map((p) => p.position), "positions are 1..n in date order").to.deep.eq([1, 2]);
				expect(attempts.map((p) => p.antritt_nr), "both count as attempts").to.deep.eq([1, 2]);
				attempts.forEach((p) => expect(p.zaehlt, `zaehlt of position ${p.position}`).to.be.true);

				const verlauf = verlaufOfStudent(data, student.uid);
				expect(verlauf.antrittCount, "both attempts counted").to.eq(2);
				expect(verlauf.maxAntritte, "the cap comes from the server").to.eq(ctx.maxAntritte);
				expect(verlauf.canAdd, `canAdd with 2 of ${ctx.maxAntritte} used`).to.eq(2 < ctx.maxAntritte);
			});
	});

	it("does not count an excused attempt, and keeps it as its own row", () => {
		const student = studentFor(2);

		givenBaseline(ctx, student);

		addPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum: attemptDate(ctx, 1) })
			.then((response) => expectNotenSuccess(response, "excused attempt"))
			.then(() => readState(ctx))
			.then((data) => {
				const attempts = attemptsOfStudent(data, student.uid);

				expect(attempts, "the excused row is kept, not merged").to.have.length(2);

				const excused = attempts[1];
				expect(excused.zaehlt, "excused consumes no attempt").to.be.false;
				expect(excused.antritt_nr, "and carries no Antrittsnummer").to.be.null;

				expect(verlaufOfStudent(data, student.uid).antrittCount, "still one attempt used").to.eq(1);
			});
	});

	it("appends a new row per add instead of overwriting the previous one", () => {
		// The old writer re-derived its target as "the first non-excused Termin2" and updated it.
		// Now only an explicit pruefung_id updates; an add always inserts.
		const student = studentFor(0);

		givenBaseline(ctx, student);

		let firstId;
		addPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum: attemptDate(ctx, 1) })
			.then((response) => {
				firstId = expectNotenSuccess(response, "first add")[0].pruefung_id;
				return addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 2) });
			})
			.then((response) => expectNotenSuccess(response, "second add"))
			.then(() => readState(ctx))
			.then((data) => {
				const attempts = attemptsOfStudent(data, student.uid);

				expect(attempts, "baseline + two added rows").to.have.length(3);
				expect(attempts.map((p) => p.pruefung_id), "the first add still exists").to.include(firstId);
			});
	});

	it("edits the pruefung identified by pruefung_id", () => {
		// An excused and a real attempt side by side - no endpoint builds this, hence the direct seed
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
			.then((response) => expectNotenSuccess(response, "moving the excused attempt"))
			.then(() => readState(ctx))
			.then((data) => {
				const rows = pruefungenOf(data, student.uid);
				const excused = rows.find((p) => p.pruefung_id === excusedId);
				const graded = rows.find((p) => dayOf(p.datum) === gradedDate);

				// the untargeted row first: silently rewriting a real grade is the worse outcome
				expect(graded, "the other attempt still exists").to.exist;
				expect(String(graded.note), "the untargeted attempt keeps its grade").to.eq(
					String(ctx.gradeNotes[0]),
				);

				expect(excused, "the excused attempt still exists").to.exist;
				expect(dayOf(excused.datum), "the row named by pruefung_id moved").to.eq(movedTo);
			});
	});

	it("adds an attempt while the LV note is still offen", () => {
		// getLvGesamtNoten filters `freigabedatum < NOW()`. The writer must read the LV note through
		// the UNFILTERED getter, otherwise it takes the INSERT branch against an existing primary key
		// and answers "keine LV-Note eingetragen" forever.
		const student = studentFor(3);

		givenBaseline(ctx, student, { freigegeben: false });

		addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) }).then((response) => {
			expectNotenSuccess(response, "attempt on an un-freigegebene LV note");
		});
	});

	it("stores an empty note as 'Noch nicht eingetragen'", () => {
		const student = studentFor(1);

		givenBaseline(ctx, student);

		addPruefung(ctx, student, { note: "", datum: attemptDate(ctx, 1) }).then((response) => {
			const [saved] = expectNotenSuccess(response, "attempt without a grade");
			expect(String(saved.note), "empty note is normalised").to.eq(
				String(ctx.notes.nochNichtEingetragen),
			);
		});
	});

	it("writes the LV note from the attempt's grade", () => {
		const student = studentFor(3);

		givenBaseline(ctx, student);

		addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) })
			.then((response) => expectNotenSuccess(response, "attempt with a grade"))
			.then(() => readLvGesamtnote(ctx, student.uid))
			.then((row) => {
				expect(String(row.note), "the LV note follows the newest attempt").to.eq(
					String(ctx.gradeNotes[1]),
				);
			});
	});

	it("creates the LV note when a student has none yet", () => {
		// Previously this answered c4keineLvNoteEingetragen. The bulk path now runs the same core as
		// the single dialog, which writes the LV note before the Prüfung.
		const student = studentFor(0);

		resetNotenState(ctx);

		notenApi
			.createPruefungen(
				[{ uid: student.uid, lehreinheit_id: student.lehreinheit_id }],
				attemptDate(ctx, 1),
				ctx.lvId,
				ctx.semKurzbz,
			)
			.then((response) => {
				const data = expectNotenSuccess(response, "createPruefungen without an LV note");
				expect(data[student.uid], `row result for ${student.uid}`).to.be.an("object");
				expect(data[student.uid].savedPruefung, "the Prüfung was written").to.exist;
			})
			.then(() => readLvGesamtnote(ctx, student.uid))
			.then((row) => {
				expect(row, "the LV note was created alongside").to.not.be.null;
				expect(String(row.note), "carries the Prüfung's note").to.eq(
					String(ctx.notes.nochNichtEingetragen),
				);
			});
	});

	it("applies a chosen note to both the Prüfung and the LV note in the bulk path", () => {
		const student = studentFor(1);

		resetNotenState(ctx);

		notenApi
			.createPruefungen(
				[{ uid: student.uid, lehreinheit_id: student.lehreinheit_id }],
				attemptDate(ctx, 1),
				ctx.lvId,
				ctx.semKurzbz,
				ctx.gradeNotes[1],
			)
			.then((response) => {
				const data = expectNotenSuccess(response, "createPruefungen with a note");
				expect(String(data[student.uid].savedPruefung[0].note)).to.eq(String(ctx.gradeNotes[1]));
			})
			.then(() => readLvGesamtnote(ctx, student.uid))
			.then((row) => expect(String(row.note)).to.eq(String(ctx.gradeNotes[1])));
	});
});
