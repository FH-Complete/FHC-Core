/**
 * Bulk / import paths (P2).
 *
 * Same validatePruefungAdd guards as the single save, but reported differently: HTTP 200 with the
 * error string in data[uid] per row (Noten.php:1600/:1656). createPruefungen is the main vehicle
 * because it always writes "Noch nicht eingetragen" and never touches punkte.
 */

import { notenApi } from "../../../../support/api/notenApi";
import {
	expectBulkRowAccepted,
	expectBulkRowError,
	expectNotenSuccess,
} from "../../../../support/helpers/notenErrors";
import {
	attemptDate,
	loadNotenContext,
	readLvGesamtnote,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	attemptsOfTyp,
	enabledRetakeTypes,
	readState,
} from "../../../../support/helpers/notenScenario";

describe("Noten API - bulk paths", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;
		});
	});

	describe("createPruefungen", () => {
		it("rejects only the row that breaks a §1 rule and accepts the rest", () => {
			const atCap = ctx.students[0];
			const fresh = ctx.students[1];
			const retakes = enabledRetakeTypes(ctx);

			resetNotenState(ctx);
			seedBaseline(ctx, atCap.uid, { freigegeben: true });
			seedBaseline(ctx, fresh.uid, { freigegeben: true });

			// drive the first student up to the cap
			retakes.forEach((typ, i) => {
				addPruefung(ctx, atCap, {
					note: ctx.gradeNotes[0],
					datum: attemptDate(ctx, i + 1),
					typ,
				}).then((response) => {
					expectNotenSuccess(response, `bring ${atCap.uid} to the cap via ${typ}`);
				});
			});

			const datum = attemptDate(ctx, retakes.length + 2);

			notenApi
				.createPruefungen(
					[
						{ uid: atCap.uid, typ: retakes[retakes.length - 1], lehreinheit_id: atCap.lehreinheit_id },
						{ uid: fresh.uid, typ: "Termin2", lehreinheit_id: fresh.lehreinheit_id },
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

			readState(ctx).then((data) => {
				const created = attemptsOfTyp(data, fresh.uid, "Termin2");
				expect(
					created.map((p) => String(p.datum).slice(0, 10)),
					"the accepted row was really created",
				).to.include(datum);
			});
		});

		it("applies the chronological rule per row", () => {
			const student = ctx.students[2];

			resetNotenState(ctx);
			seedBaseline(ctx, student.uid, { freigegeben: true });

			// an open (uncounted) attempt occupies a date without consuming an Antritt
			addPruefung(ctx, student, {
				note: ctx.notes.nochNichtEingetragen,
				datum: attemptDate(ctx, 3),
				typ: "Termin2",
			}).then((response) => {
				expectNotenSuccess(response, "seed an open Termin2");
			});

			notenApi
				.createPruefungen(
					[{ uid: student.uid, typ: "Termin2", lehreinheit_id: student.lehreinheit_id }],
					attemptDate(ctx, 2), // earlier than the existing attempt
					ctx.lvId,
					ctx.semKurzbz,
				)
				.then((response) => {
					const data = expectNotenSuccess(response, "createPruefungen out of order");
					expectBulkRowError(data, student.uid, "pruefungDatumBeforeExisting");
				});
		});

		it("rejects a call without the required parameters", () => {
			cy.request({
				method: "POST",
				url: "/index.ci.php/api/frontend/v1/Noten/createPruefungen",
				body: { lva_id: ctx.lvId, sem_kurzbz: ctx.semKurzbz },
				auth: {
					username: Cypress.env("adminusername"),
					password: Cypress.env("adminpassword"),
				},
				failOnStatusCode: false,
			}).then((response) => {
				expect(response.status, "missing uids/datum").to.eq(500);
				expect(response.body).to.have.nested.property("meta.status", "error");
			});
		});
	});

	describe("savePruefungenBulk", () => {
		beforeEach(function () {
			if (ctx.cisConfig.CIS_GESAMTNOTE_PUNKTE) {
				// With punkte mode on, this endpoint unconditionally re-derives every row's note from
				// the Notenschlüssel (Noten.php:1638-1643), so a row would need valid punkte rather than
				// a grade. Covering that needs a points-based fixture; skip rather than assert nonsense.
				cy.log(
					"Skipped: CIS_GESAMTNOTE_PUNKTE is enabled, so savePruefungenBulk grades by points " +
						"and this grade-based scenario does not apply.",
				);
				this.skip();
			}
			requireDbReset();
		});

		it("applies the §1 rules per row", () => {
			const atCap = ctx.students[0];
			const fresh = ctx.students[1];
			const retakes = enabledRetakeTypes(ctx);

			resetNotenState(ctx);
			seedBaseline(ctx, atCap.uid, { freigegeben: true });
			seedBaseline(ctx, fresh.uid, { freigegeben: true });

			retakes.forEach((typ, i) => {
				addPruefung(ctx, atCap, {
					note: ctx.gradeNotes[0],
					datum: attemptDate(ctx, i + 1),
					typ,
				});
			});

			const datum = attemptDate(ctx, retakes.length + 2);

			notenApi
				.savePruefungenBulk(ctx.lvId, ctx.semKurzbz, [
					{
						uid: atCap.uid,
						typ: retakes[retakes.length - 1],
						note: ctx.gradeNotes[0],
						punkte: null,
						datum,
						lehreinheit_id: atCap.lehreinheit_id,
					},
					{
						uid: fresh.uid,
						typ: "Termin2",
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
	});

	describe("saveNotenvorschlagBulk", () => {
		it("writes an LV note for every row", () => {
			const [a, b] = ctx.students;

			resetNotenState(ctx);

			notenApi
				.saveNotenvorschlagBulk(ctx.lvId, ctx.semKurzbz, [
					{ uid: a.uid, note: ctx.gradeNotes[0], punkte: null },
					{ uid: b.uid, note: ctx.gradeNotes[1], punkte: null },
				])
				.then((response) => {
					const data = expectNotenSuccess(response, "saveNotenvorschlagBulk");
					expect(data, "one lvgesamtnote row per input row").to.be.an("array").and.have.length(2);
				});

			readLvGesamtnote(ctx, a.uid).then((row) => {
				expect(row, `row for ${a.uid}`).to.not.be.null;
				expect(String(row.note)).to.eq(String(ctx.gradeNotes[0]));
			});

			readLvGesamtnote(ctx, b.uid).then((row) => {
				expect(row, `row for ${b.uid}`).to.not.be.null;
				expect(String(row.note)).to.eq(String(ctx.gradeNotes[1]));
			});
		});
	});
});
