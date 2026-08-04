/**
 * Overwrite rules for the Notenvorschlag column - all of them client-side only.
 *
 * CIS_GESAMTNOTE_UEBERSCHREIBEN is exposed by getCisConfig but read by nobody in CIS4; only the

 * What the CIS4 tool enforces sits in Benotungstool.js and never reaches the server:
 *  - the editor list offers lehre notes only (:1925), so 'nicht zugelassen' can never be picked
 *  - the column is non-editable once the student has any Prüfung (:1017)
 *
 * saveNotenvorschlag applies neither, so both tests are expected RED: a direct API call bypasses
 * the rules. Assertions read the row back through the DB, since getLvGesamtNoten hides
 * un-freigegebene notes.
 *
 * A third rule - lkt_ueberschreibbar on the student's Zeugnisnote (:1019) - is not covered here:
 * it needs a seeded lehre.tbl_zeugnisnote row and the test DB role has no rights on that table.
 */

import { expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import {
	attemptDate,
	loadNotenContext,
	readLvGesamtnote,
	requireDbReset,
} from "../../../../support/helpers/notenTestData";
import { addPruefung, givenBaseline } from "../../../../support/helpers/notenScenario";
import { notenApi } from "../../../../support/api/notenApi";

describe("Noten API - Notenvorschlag overwrite rules", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;
		});
	});

	const studentFor = (index) => ctx.students[index % ctx.students.length];

	it("refuses a note the editor list never offers", function () {
		if (ctx.notes.nichtLehre === null) {
			this.skip(); // every active note is a lehre note on this instance
		}

		const student = studentFor(0);

		givenBaseline(ctx, student);

		notenApi
			.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.notes.nichtLehre)
			.then(() => readLvGesamtnote(ctx, student.uid))
			.then((row) => {
				expect(String(row.note), "a non-lehre note must not become the LV note").to.eq(
					String(ctx.gradeNotes[0]),
				);
			});
	});

	it("refuses to change the Notenvorschlag once a Prüfung exists", () => {
		const student = studentFor(1);

		givenBaseline(ctx, student);

		addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) }).then((response) => {
			expectNotenSuccess(response, "seed a Prüfung");
		});

		// the grade now belongs to the attempt history; a direct edit bypasses the Antritt rules
		notenApi
			.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[0])
			.then(() => readLvGesamtnote(ctx, student.uid))
			.then((row) => {
				expect(String(row.note), "the attempt's grade must survive the call").to.eq(
					String(ctx.gradeNotes[1]),
				);
			});
	});

	// Same hole through the CSV import, which is the path an Assistenz actually drives.
	// saveNotenvorschlagBulk validates nothing at all, so these are RED for the same reason.
	describe("saveNotenvorschlagBulk", () => {
		it("refuses a note the editor list never offers", function () {
			if (ctx.notes.nichtLehre === null) this.skip();

			const student = studentFor(2);

			givenBaseline(ctx, student);

			notenApi
				.saveNotenvorschlagBulk(ctx.lvId, ctx.semKurzbz, [
					{ uid: student.uid, note: ctx.notes.nichtLehre, punkte: null },
				])
				.then(() => readLvGesamtnote(ctx, student.uid))
				.then((row) => {
					expect(String(row.note), "a non-lehre note must not become the LV note").to.eq(
						String(ctx.gradeNotes[0]),
					);
				});
		});

		it("refuses to change the Notenvorschlag once a Prüfung exists", () => {
			const student = studentFor(3);

			givenBaseline(ctx, student);

			addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) }).then(
				(response) => {
					expectNotenSuccess(response, "seed a Prüfung");
				},
			);

			notenApi
				.saveNotenvorschlagBulk(ctx.lvId, ctx.semKurzbz, [
					{ uid: student.uid, note: ctx.gradeNotes[0], punkte: null },
				])
				.then(() => readLvGesamtnote(ctx, student.uid))
				.then((row) => {
					expect(String(row.note), "the attempt's grade must survive the import").to.eq(
						String(ctx.gradeNotes[1]),
					);
				});
		});
	});
});
