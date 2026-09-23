/**
 * The Notenschluessel: Punkte to Note. Read only.
 *
 * The test does not rebuild the mapping. It scans the Punkte range and checks the properties of a
 * valid scale: no gap, monotonic, and exact at every limit.
 */

import { notenApi, loginAsLektor } from "../../../../support/api/notenApi";
import { expectBulkRowError, expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import { requirePunkteMode, skipIf } from "../../../../support/helpers/notenConfig";
import {
	antrittDate,
	loadNotenContext,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";
import { pruefungenOf, givenBaseline, readStateViaApi } from "../../../../support/helpers/notenScenario";

const MAX_PUNKTE = 100;

describe("Noten API - Notenschlüssel (getNoteByPunkte)", () => {
	let ctx;
	let lvId;

	before(() => {
		loadNotenContext().then((loaded) => {
			ctx = loaded;
			lvId = ctx.lvId;
		});
	});

	beforeEach(() => loginAsLektor());

	/** Sweeps 0..MAX_PUNKTE and returns [{ punkte, note }] as the server reports it. */
	const sweepScale = () => {
		const observed = [];

		for (let punkte = 0; punkte <= MAX_PUNKTE; punkte += 1) {
			notenApi.getNoteByPunkte(lvId, ctx.semKurzbz, punkte).then((response) => {
				const note = expectNotenSuccess(response, `getNoteByPunkte(${punkte})`);
				observed.push({ punkte, note });
			});
		}

		return cy.then(() => observed);
	};

	it("bildet jeden Punktewert auf eine monotone und exakte Notenskala ab", () => {
		sweepScale().then((observed) => {
			const withNote = observed.filter((entry) => entry.note !== null && entry.note !== undefined);

			expect(
				withNote.length,
				`LV ${lvId} in ${ctx.semKurzbz} has no Notenschlüssel - getNoteByPunkte returned null ` +
					`for every value in 0..${MAX_PUNKTE}. Pin NOTEN_LV_ID to an LV that has one.`,
			).to.be.greaterThan(0);

			// Below the lowest limit the server returns no Note. That block must be at the start:
			// no Note AFTER a Note is a gap in the scale
			const firstWithNoteIndex = observed.findIndex((e) => e.note !== null && e.note !== undefined);
			observed.slice(firstWithNoteIndex).forEach((entry) => {
				expect(
					entry.note,
					`${entry.punkte} Punkte are inside the range with a Note and must give a Note`,
				).to.not.be.oneOf([null, undefined]);
			});

			// monotonic: the ids go from 1 (best) to 5, so the number must never go up
			for (let i = 1; i < withNote.length; i += 1) {
				expect(
					Number(withNote[i].note),
					`the Note at ${withNote[i].punkte} Punkte must not be worse than at ${withNote[i - 1].punkte}`,
				).to.be.at.most(Number(withNote[i - 1].note));
			}

			// report each step on its own, so a moved limit shows in the output
			const boundaries = [];
			for (let i = 1; i < withNote.length; i += 1) {
				if (Number(withNote[i].note) !== Number(withNote[i - 1].note)) {
					boundaries.push({
						punkte: withNote[i].punkte,
						note: withNote[i].note,
						previousNote: withNote[i - 1].note,
					});
				}
			}

			cy.log(`Notenschlüssel of LV ${lvId}: ${boundaries.map((b) => `>=${b.punkte} -> ${b.note}`).join(", ")}`);

			expect(
				boundaries.length,
				"a usable Notenschlüssel has at least one Note limit in the scanned range",
			).to.be.greaterThan(0);

			// re-assert each boundary individually so a failure names the exact threshold
			boundaries.forEach((boundary) => {
				notenApi.getNoteByPunkte(lvId, ctx.semKurzbz, boundary.punkte).then((response) => {
					const note = expectNotenSuccess(response, `Schwelle ${boundary.punkte}`);
					expect(Number(note), `die Note genau an der Schwelle von ${boundary.punkte} Punkten`).to.eq(
						Number(boundary.note),
					);
				});

				notenApi.getNoteByPunkte(lvId, ctx.semKurzbz, boundary.punkte - 1).then((response) => {
					const note = expectNotenSuccess(response, `just below ${boundary.punkte}`);
					expect(
						Number(note),
						`one Punkt below the limit of ${boundary.punkte} Punkte must still be the worse Note`,
					).to.eq(Number(boundary.previousNote));
				});
			});
		});
	});

	// Punkte without a derivable Note: every endpoint rejects them the same way.
	describe("Punkte ohne ableitbare Note", () => {
		let thresholdBelowZero = false;

		before(() => {
			notenApi.getNoteByPunkte(lvId, ctx.semKurzbz, -1).then((response) => {
				const note = expectNotenSuccess(response, "getNoteByPunkte(-1)");
				thresholdBelowZero = note !== null && note !== undefined;
			});
		});

		beforeEach(function () {
			requirePunkteMode(this, ctx);
			skipIf(this, thresholdBelowZero, "Übersprungen: der Notenschlüssel hat eine Schwelle unter 0 Punkten.");
			requireDbReset();
		});

		const expectOnlyAntritt1 = (students) =>
			readStateViaApi(ctx).then((data) => {
				students.forEach((s) => {
					expect(pruefungenOf(data, s.uid), `Prüfungen von ${s.uid}`).to.have.length(1);
				});
			});

		it("savePruefung lehnt negative Punkte ab", () => {
			const student = ctx.students[0];

			givenBaseline(ctx, student);

			notenApi
				.savePruefung(lvId, ctx.semKurzbz, student.uid, {
					pruefung_id: null,
					lehreinheit_id: student.lehreinheit_id,
					datum: antrittDate(ctx, 1),
					note: ctx.notenScale[0],
					punkte: -1,
				})
				.then((response) => expectNotenError(response, "c4punkteKeineNoteErmittelt"));

			expectOnlyAntritt1([student]);
		});

		it("createPruefungen lehnt negative Punkte für alle gewählten Studierenden ab", () => {
			const students = ctx.students.slice(0, 2);

			resetNotenState(ctx);
			students.forEach((s) => seedBaseline(ctx, s));

			notenApi
				.createPruefungen(
					lvId,
					ctx.semKurzbz,
					students.map((s) => ({ uid: s.uid, lehreinheit_id: s.lehreinheit_id })),
					{ datum: antrittDate(ctx, 1), note: null, punkte: -1 },
				)
				.then((response) => {
					const data = expectNotenSuccess(response, "createPruefungen mit negativen Punkten");
					students.forEach((s) => expectBulkRowError(data, s.uid, "c4punkteKeineNoteErmittelt"));
				});

			expectOnlyAntritt1(students);
		});
	});
});
