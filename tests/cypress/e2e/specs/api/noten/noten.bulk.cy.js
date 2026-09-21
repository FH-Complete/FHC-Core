/**
 * Bulk and import paths. Same guards as for individual saves, but reported per row
 * : HTTP 200 with the error message in data[uid].
 */

import { notenApi } from "../../../../support/api/notenApi";
import { expectBulkRowAccepted, expectBulkRowError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import {
	requireConfig,
	requireNotenMode,
	requirePunkteMode,
	requireWiederholung,
} from "../../../../support/helpers/notenConfig";
import {
	attemptDate,
	loadNotenContext,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";
import { addPruefung, attemptsOfStudent, readStateViaApi } from "../../../../support/helpers/notenScenario";

describe("Noten API - Sammelpfade", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;
		});
	});

	/** Takes students to the limit. The baseline provides the starting point. */
	const bringToCap = (student) => {
		for (let i = 0; i < ctx.maxAntritte - 1; i += 1) {
			addPruefung(ctx, student, {
				note: ctx.notes.negativ,
				datum: attemptDate(ctx, i + 1),
			}).then((response) => {
				expectNotenSuccess(response, `bring ${student.uid} to the cap (attempt ${i + 2})`);
			});
		}
		return attemptDate(ctx, ctx.maxAntritte + 1);
	};

	describe("createPruefungen", () => {
		beforeEach(function () {
			requireWiederholung(this, ctx);
			// If the commission audit is not attached, the boundary returns “commAuditNotAllowed”
			requireConfig(this, ctx, "CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF", true);
		});

		it("lehnt nur die Zeile gegen eine §1-Regel ab und nimmt die übrigen an", () => {
			const atCap = ctx.students[0];
			const fresh = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, atCap);
			seedBaseline(ctx, fresh);

			const datum = bringToCap(atCap);

			notenApi
				.createPruefungen(
					[
						{ uid: atCap.uid, lehreinheit_id: atCap.lehreinheit_id },
						{ uid: fresh.uid, lehreinheit_id: fresh.lehreinheit_id },
					],
					datum,
					ctx.lvId,
					ctx.semKurzbz,
				)
				.then((response) => {
					// the request as a whole succeeds - errors are per row
					const data = expectNotenSuccess(response, "createPruefungen");

					expectBulkRowError(data, atCap.uid, "maxAntritteReached");
					expectBulkRowAccepted(data, fresh.uid);
				});

			readStateViaApi(ctx).then((data) => {
				const created = attemptsOfStudent(data, fresh.uid);
				expect(
					created.map((p) => String(p.datum).slice(0, 10)),
					"the accepted row was really created",
				).to.include(datum);
			});
		});
	});

	describe("savePruefungenBulk", () => {
		beforeEach(function () {
			requireDbReset();
			requireWiederholung(this, ctx);
			requireConfig(this, ctx, "CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF", true);
		});

		it("wendet die §1-Regeln je Zeile an", function () {
			// In Punkte mode, the endpoint derives the grade from the points; the rule check
			// is covered by the points-based test below
			requireNotenMode(this, ctx);

			const atCap = ctx.students[0];
			const fresh = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, atCap);
			seedBaseline(ctx, fresh);

			const datum = bringToCap(atCap);

			notenApi
				.savePruefungenBulk(ctx.lvId, ctx.semKurzbz, [
					{
						uid: atCap.uid,
						note: ctx.gradeNotes[0],
						punkte: null,
						datum,
						lehreinheit_id: atCap.lehreinheit_id,
					},
					{
						uid: fresh.uid,
						note: ctx.gradeNotes[0],
						punkte: null,
						datum,
						lehreinheit_id: fresh.lehreinheit_id,
					},
				])
				.then((response) => {
					const data = expectNotenSuccess(response, "savePruefungenBulk");
					expectBulkRowError(data, atCap.uid, "maxAntritteReached");
					expectBulkRowAccepted(data, fresh.uid);
				});
		});

		// Point mode: The grade comes from the grading key; the row only provides points.
		describe("Punktemodus", () => {
			beforeEach(function () {
				requirePunkteMode(this, ctx);
			});

			it("wendet die Regeln je Zeile auf die abgeleitete Note an", () => {
				const atCap = ctx.students[0];
				const fresh = ctx.students[1];
				const punkte = 70;

				resetNotenState(ctx);
				seedBaseline(ctx, atCap);
				seedBaseline(ctx, fresh);

				const datum = bringToCap(atCap);
				const bulkRow = (student) => ({
					uid: student.uid,
					note: null,
					punkte,
					datum,
					lehreinheit_id: student.lehreinheit_id,
				});

				notenApi
					.savePruefungenBulk(ctx.lvId, ctx.semKurzbz, [bulkRow(atCap), bulkRow(fresh)])
					.then((response) => {
						const data = expectNotenSuccess(response, "savePruefungenBulk mit Punkten");
						expectBulkRowError(data, atCap.uid, "maxAntritteReached");
						expectBulkRowAccepted(data, fresh.uid);
					});

				notenApi.getNoteByPunkte(punkte, ctx.lvId, ctx.semKurzbz).then((punkteResponse) => {
					const expectedNote = punkteResponse.body.data;

					readStateViaApi(ctx).then((data) => {
						const newTermin = attemptsOfStudent(data, fresh.uid).find(
							(p) => String(p.datum).slice(0, 10) === datum,
						);
						expect(newTermin, "der neue Termin").to.exist;
						expect(String(newTermin.note), "die aus den Punkten abgeleitete Note").to.eq(
							String(expectedNote),
						);
					});
				});
			});
		});
	});

	describe("saveNotenvorschlagBulk", () => {
		it("schreibt für jede Zeile eine LV-Note", function () {
			// In points mode, the grade comes from the grading key, see the score mode block
			requireNotenMode(this, ctx);

			const [a, b] = ctx.students;

			resetNotenState(ctx);

			notenApi
				.saveNotenvorschlagBulk(ctx.lvId, ctx.semKurzbz, [
					{ uid: a.uid, note: ctx.gradeNotes[0], punkte: null },
					{ uid: b.uid, note: ctx.gradeNotes[1], punkte: null },
				])
				.then((response) => {
					// Sorted by uid: each line contains either the course grade or an error message
					const data = expectNotenSuccess(response, "saveNotenvorschlagBulk");
					expectBulkRowAccepted(data, a.uid);
					expectBulkRowAccepted(data, b.uid);
				});

			readLvGesamtnoteViaDb(ctx, a.uid).then((row) => {
				expect(row, `row for ${a.uid}`).to.not.be.null;
				expect(String(row.note)).to.eq(String(ctx.gradeNotes[0]));
			});

			readLvGesamtnoteViaDb(ctx, b.uid).then((row) => {
				expect(row, `row for ${b.uid}`).to.not.be.null;
				expect(String(row.note)).to.eq(String(ctx.gradeNotes[1]));
			});
		});

		describe("Punktemodus", () => {
			beforeEach(function () {
				requirePunkteMode(this, ctx);
			});

			it("überspringt eine Zeile ohne Punkte und schreibt die übrigen", () => {
				const withoutPunkte = ctx.students[0];
				const withPunkte = ctx.students[1];

				resetNotenState(ctx);

				notenApi
					.saveNotenvorschlagBulk(ctx.lvId, ctx.semKurzbz, [
						{ uid: withoutPunkte.uid, note: null, punkte: null },
						{ uid: withPunkte.uid, note: null, punkte: 100 },
					])
					.then((response) => {
						// A broken line should not cause the entire import to fail
						const data = expectNotenSuccess(response, "Notenimport mit Luecke");
						expectBulkRowError(data, withoutPunkte.uid, "c4punkteKeineNoteErmittelt");
						expectBulkRowAccepted(data, withPunkte.uid);
					});

				readLvGesamtnoteViaDb(ctx, withoutPunkte.uid).then((row) => {
					expect(row, "die übersprungene Zeile bleibt ungeschrieben").to.be.null;
				});
			});
		});
	});
});
