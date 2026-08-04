/**
 * Notenvorschlag - LV note write and resulting state (P1, case 11).
 *
 * State comes from two timestamps (Benotungstool.js::checkFreigabe): no freigabedatum -> offen,
 * benotungsdatum > freigabedatum -> changed, else freigegeben. The "changed" case is produced by
 * seeding an already-freigegebene baseline, which keeps this spec free of the LDAP password check
 * and the Freigabe mail.
 *
 * Caveat: getLvGesamtNoten() (behind getStudentenNoten) filters `freigabedatum < NOW()`, and
 * NULL < NOW() is NULL - so offene notes are dropped, while saveNotenvorschlag writes through the
 * unfiltered getLvGesamtNoteVorschlag(). The tests below separate write from read for that reason.
 */

import { notenApi } from "../../../../support/api/notenApi";
import { expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import {
	baselineBenotungsdatum,
	loadNotenContext,
	readLvGesamtnote,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";
import { lvNoteOf, readState } from "../../../../support/helpers/notenScenario";

describe("Noten API - Notenvorschlag", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;
		});
	});

	const readRow = (uid) => readLvGesamtnote(ctx, uid);

	describe("saving a Notenvorschlag on a student without a grade", () => {
		it("returns the stored note with a benotungsdatum and no freigabedatum", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);

			notenApi
				.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[0])
				.then((response) => {
					const data = expectNotenSuccess(response, "saveNotenvorschlag");
					expect(data, "saveNotenvorschlag returns [ lvgesamtnote ]").to.be.an("array").and.not
						.be.empty;

					const row = data[0];
					expect(String(row.note), "stored note").to.eq(String(ctx.gradeNotes[0]));
					expect(row.benotungsdatum, "benotungsdatum is stamped on save").to.exist;
					expect(row.freigabedatum, "a fresh Notenvorschlag is not freigegeben").to.be.oneOf([
						null,
						undefined,
						"",
					]);
				});
		});

		it("persists the row with freigabedatum NULL (state: offen)", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			notenApi.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[0]);

			readRow(student.uid).then((row) => {
				expect(row, "the lvgesamtnote row must exist in the database").to.not.be.null;
				expect(String(row.note)).to.eq(String(ctx.gradeNotes[0]));
				expect(row.benotungsdatum, "benotungsdatum").to.exist;
				expect(row.freigabedatum, "freigabedatum stays NULL until Freigabe").to.be.null;
			});
		});

		// The unfiltered getter behind getNotenvorschlagStudent sees the same row fine - which is the
		// getter finding 1 says getStudentenNoten should be using.
		it("reports the offen note through getNotenvorschlagStudent", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			notenApi.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[0]);

			notenApi.getNotenvorschlagStudent(ctx.lvId, ctx.semKurzbz, student.uid).then((response) => {
				const data = expectNotenSuccess(response, "getNotenvorschlagStudent");
				expect(String(data[0].note)).to.eq(String(ctx.gradeNotes[0]));
				expect(data[0].freigabedatum, "still offen").to.be.null;
			});
		});

		// EXPECTED TO FAIL while getLvGesamtNoten filters `freigabedatum < NOW()`: the row exists
		// (test above proves it) but the read model drops it until the note is freigegeben.
		it("reports the offen note back through getStudentenNoten", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			notenApi.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[0]);

			readState(ctx).then((data) => {
				const grades = lvNoteOf(data, student.uid);
				expect(grades, `grades entry for ${student.uid}`).to.exist;
				expect(
					String(grades.note_lv),
					"a saved (not yet freigegebene) Notenvorschlag must be readable again - if this is " +
						"null, getLvGesamtNoten's `freigabedatum < NOW()` filter dropped the row",
				).to.eq(String(ctx.gradeNotes[0]));
				expect(grades.freigabedatum, "still offen").to.be.oneOf([null, undefined, ""]);
			});
		});
	});

	describe("re-grading an already freigegebene note", () => {
		it("moves the state from freigegeben to changed", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student.uid, {
				note: ctx.gradeNotes[0],
				freigegeben: true,
				benotungsdatum: baselineBenotungsdatum(ctx),
			});

			// baseline: benotungsdatum == freigabedatum -> freigegeben, not changed
			readState(ctx).then((data) => {
				const grades = lvNoteOf(data, student.uid);
				expect(grades.note_lv, "seeded freigegebene note is visible").to.exist;
				expect(
					new Date(grades.benotungsdatum) > new Date(grades.freigabedatum),
					"baseline must not already look changed",
				).to.be.false;
			});

			notenApi
				.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[1])
				.then((response) => {
					expectNotenSuccess(response, "re-grade after Freigabe");
				});

			readState(ctx).then((data) => {
				const grades = lvNoteOf(data, student.uid);

				expect(String(grades.note_lv), "the new grade is stored").to.eq(
					String(ctx.gradeNotes[1]),
				);
				expect(grades.freigabedatum, "the old Freigabe timestamp is kept").to.exist;
				expect(
					new Date(grades.benotungsdatum) > new Date(grades.freigabedatum),
					"benotungsdatum must now be newer than freigabedatum (state: changed)",
				).to.be.true;
			});
		});

		it("overwrites the grade rather than adding a second row", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student.uid, { note: ctx.gradeNotes[0], freigegeben: true });

			notenApi.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[1]);

			readRow(student.uid).then((row) => {
				expect(row, "the single lvgesamtnote row").to.not.be.null;
				expect(String(row.note)).to.eq(String(ctx.gradeNotes[1]));
			});
		});
	});

});
