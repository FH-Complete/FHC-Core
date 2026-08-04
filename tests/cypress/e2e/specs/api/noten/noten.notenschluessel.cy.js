/**
 * Notenschlüssel - points to grade (P0, case 10). Read-only.
 *
 * getNote() picks the aufteilung row with the highest punkte <= input, and returns null when the LV
 * has no Notenschlüssel. Rather than re-implementing that lookup, this sweeps the range and asserts
 * the properties a correct scale must have: total, monotonic, exact at each boundary.
 */

import { notenApi } from "../../../../support/api/notenApi";
import { expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import { loadNotenContext } from "../../../../support/helpers/notenTestData";

const MAX_PUNKTE = 100;

describe("Noten API - Notenschlüssel (getNoteByPunkte)", () => {
	let ctx;
	let lvId;

	before(() => {
		loadNotenContext().then((context) => {
			ctx = context;
			lvId = context.lvId;
		});
	});

	/** Sweeps 0..MAX_PUNKTE and returns [{ punkte, note }] as the server reports it. */
	const sweepScale = () => {
		const observed = [];

		for (let punkte = 0; punkte <= MAX_PUNKTE; punkte += 1) {
			notenApi.getNoteByPunkte(punkte, lvId, ctx.semKurzbz).then((response) => {
				const note = expectNotenSuccess(response, `getNoteByPunkte(${punkte})`);
				observed.push({ punkte, note });
			});
		}

		return cy.then(() => observed);
	};

	it("maps every point value through a monotonic, exact grade scale", () => {
		sweepScale().then((observed) => {
			const graded = observed.filter((entry) => entry.note !== null && entry.note !== undefined);

			expect(
				graded.length,
				`LV ${lvId} in ${ctx.semKurzbz} has no Notenschlüssel - getNoteByPunkte returned null ` +
					`for every value in 0..${MAX_PUNKTE}. Pin NOTEN_LV_ID to an LV that has one.`,
			).to.be.greaterThan(0);

			// (4) below the lowest threshold the model answers null, and that region must be a
			// contiguous prefix - a null appearing *after* a graded value would mean a gap in the scale
			const firstGradedIndex = observed.findIndex((e) => e.note !== null && e.note !== undefined);
			observed.slice(firstGradedIndex).forEach((entry) => {
				expect(
					entry.note,
					`points ${entry.punkte} fall inside the graded range and must map to a grade`,
				).to.not.be.oneOf([null, undefined]);
			});

			// (2) monotonic: grade PKs run 1 (best) .. 5 (worst), so the number must never increase
			for (let i = 1; i < graded.length; i += 1) {
				expect(
					Number(graded[i].note),
					`grade at ${graded[i].punkte} points must not be worse than at ${graded[i - 1].punkte}`,
				).to.be.at.most(Number(graded[i - 1].note));
			}

			// (3) exact boundaries: report each step so a shifted threshold is visible in the output
			const boundaries = [];
			for (let i = 1; i < graded.length; i += 1) {
				if (Number(graded[i].note) !== Number(graded[i - 1].note)) {
					boundaries.push({
						punkte: graded[i].punkte,
						note: graded[i].note,
						previousNote: graded[i - 1].note,
					});
				}
			}

			cy.log(
				`Notenschlüssel of LV ${lvId}: ${boundaries
					.map((b) => `>=${b.punkte} -> ${b.note}`)
					.join(", ")}`,
			);

			expect(
				boundaries.length,
				"a usable Notenschlüssel must have at least one grade boundary in the swept range",
			).to.be.greaterThan(0);

			// re-assert each boundary individually so a failure names the exact threshold
			boundaries.forEach((boundary) => {
				notenApi.getNoteByPunkte(boundary.punkte, lvId, ctx.semKurzbz).then((response) => {
					const note = expectNotenSuccess(response, `boundary ${boundary.punkte}`);
					expect(Number(note), `grade exactly at the ${boundary.punkte}-point boundary`).to.eq(
						Number(boundary.note),
					);
				});

				notenApi.getNoteByPunkte(boundary.punkte - 1, lvId, ctx.semKurzbz).then((response) => {
					const note = expectNotenSuccess(response, `just below ${boundary.punkte}`);
					expect(
						Number(note),
						`one point below the ${boundary.punkte}-point boundary must still be the worse grade`,
					).to.eq(Number(boundary.previousNote));
				});
			});
		});
	});

	it("reports no grade for an LV without a Notenschlüssel", () => {
		// A non-existent LV id can have no Notenschlüssel assigned, so the model's success(null)
		// contract is observable without depending on a particular fixture.
		notenApi.getNoteByPunkte(50, 0, ctx.semKurzbz).then((response) => {
			const note = expectNotenSuccess(response, "getNoteByPunkte for an LV without a schluessel");
			expect(note, "no Notenschlüssel -> no grade").to.be.oneOf([null, undefined]);
		});
	});

});
