/**
 * Grade key - Points per grade. Read-only.
 *
 * Instead of recreating the mapping, the range is scanned and checked for the properties of a
 * valid scale: continuous, monotonic, and exact at every boundary.
 */

import { notenApi } from "../../../../support/api/notenApi";
import { expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import { requirePunkteMode, skipIf } from "../../../../support/helpers/notenConfig";
import {
	attemptDate,
	loadNotenContext,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";
import { attemptsOfStudent, givenBaseline, readStateViaApi } from "../../../../support/helpers/notenScenario";

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

	it("bildet jeden Punktewert auf eine monotone und exakte Notenskala ab", () => {
		sweepScale().then((observed) => {
			const graded = observed.filter((entry) => entry.note !== null && entry.note !== undefined);

			expect(
				graded.length,
				`LV ${lvId} in ${ctx.semKurzbz} has no Notenschlüssel - getNoteByPunkte returned null ` +
					`for every value in 0..${MAX_PUNKTE}. Pin NOTEN_LV_ID to an LV that has one.`,
			).to.be.greaterThan(0);

			// Below the lowest threshold, the model returns zero, this must be a continuous
			// start. a zero AFTER a note would create a gap in the scale
			const firstGradedIndex = observed.findIndex((e) => e.note !== null && e.note !== undefined);
			observed.slice(firstGradedIndex).forEach((entry) => {
				expect(
					entry.note,
					`points ${entry.punkte} fall inside the graded range and must map to a grade`,
				).to.not.be.oneOf([null, undefined]);
			});

			// Monotonic: The PKs range from 1 (best) to 5, so the number must never increase
			for (let i = 1; i < graded.length; i += 1) {
				expect(
					Number(graded[i].note),
					`grade at ${graded[i].punkte} points must not be worse than at ${graded[i - 1].punkte}`,
				).to.be.at.most(Number(graded[i - 1].note));
			}

			// Report each level individually so that a shifted threshold is visible in the output
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

			cy.log(`Notenschlüssel of LV ${lvId}: ${boundaries.map((b) => `>=${b.punkte} -> ${b.note}`).join(", ")}`);

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

	// Nodes without a derivable grade reject all paths equally.
	describe("Punkte ohne ableitbare Note", () => {
		let thresholdBelowZero = false;

		before(() => {
			notenApi.getNoteByPunkte(-1, lvId, ctx.semKurzbz).then((response) => {
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
					expect(attemptsOfStudent(data, s.uid), `Prüfungen von ${s.uid}`).to.have.length(1);
				});
			});

		it("saveStudentPruefung lehnt negative Punkte ab", () => {
			const student = ctx.students[0];

			givenBaseline(ctx, student);

			notenApi
				.saveStudentPruefung({
					student_uid: student.uid,
					note: ctx.gradeNotes[0],
					punkte: -1,
					datum: attemptDate(ctx, 1),
					lva_id: lvId,
					lehreinheit_id: student.lehreinheit_id,
					sem_kurzbz: ctx.semKurzbz,
					pruefung_id: null,
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
					students.map((s) => ({ uid: s.uid, lehreinheit_id: s.lehreinheit_id })),
					attemptDate(ctx, 1),
					lvId,
					ctx.semKurzbz,
					null,
					-1,
				)
				.then((response) => expectNotenError(response, "c4punkteKeineNoteErmittelt"));

			expectOnlyAntritt1(students);
		});
	});
});
