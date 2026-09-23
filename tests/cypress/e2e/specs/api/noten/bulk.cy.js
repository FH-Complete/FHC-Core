/**
 * The bulk and import endpoints. The same rules as for a single save, but each row reports its own
 * result: HTTP 200, and an error message in data[uid].
 */

import { notenApi, loginAsLektor } from "../../../../support/api/notenApi";
import {
	expectBulkRowAccepted,
	expectBulkRowError,
	expectNotenError,
	expectNotenSuccess,
} from "../../../../support/helpers/notenErrors";
import {
	requireConfig,
	requireNotenMode,
	requirePunkteMode,
	requireRepeat,
} from "../../../../support/helpers/notenConfig";
import {
	antrittDate,
	loadNotenContext,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";
import { addPruefung, pruefungenOf, readStateViaApi } from "../../../../support/helpers/notenScenario";

describe("Noten API - Sammelpfade", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((loaded) => {
			ctx = loaded;
		});
	});

	beforeEach(() => loginAsLektor());

	/** Adds Antritte up to maxAntritte. The baseline has Antritt 1. */
	const bringToCap = (student) => {
		for (let i = 0; i < ctx.maxAntritte - 1; i += 1) {
			addPruefung(ctx, student, {
				note: ctx.noten.negativ,
				datum: antrittDate(ctx, i + 1),
			}).then((response) => {
				expectNotenSuccess(response, `${student.uid} bis zur Grenze füllen (Antritt ${i + 2})`);
			});
		}
		return antrittDate(ctx, ctx.maxAntritte + 1);
	};

	describe("createPruefungen", () => {
		beforeEach(function () {
			requireRepeat(this, ctx);
			// without CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF the limit answers "kommPruefNichtErlaubt"
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
					ctx.lvId,
					ctx.semKurzbz,
					[
						{ uid: atCap.uid, lehreinheit_id: atCap.lehreinheit_id },
						{ uid: fresh.uid, lehreinheit_id: fresh.lehreinheit_id },
					],
					{ datum },
				)
				.then((response) => {
					// the request as a whole succeeds - errors are per row
					const data = expectNotenSuccess(response, "createPruefungen");

					expectBulkRowError(data, atCap.uid, "maxAntritteReached");
					expectBulkRowAccepted(data, fresh.uid);
				});

			readStateViaApi(ctx).then((data) => {
				const created = pruefungenOf(data, fresh.uid);
				expect(
					created.map((p) => String(p.datum).slice(0, 10)),
					"the accepted row was really created",
				).to.include(datum);
			});
		});
	});

	describe("importPruefungen", () => {
		beforeEach(function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNGSIMPORT", true);
			requireDbReset();
			requireRepeat(this, ctx);
			requireConfig(this, ctx, "CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF", true);
		});

		it("wendet die §1-Regeln je Zeile an", function () {
			// in the Punkte mode the endpoint derives the Note from the Punkte; the Punkte test below
			// covers the rule check
			requireNotenMode(this, ctx);

			const atCap = ctx.students[0];
			const fresh = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, atCap);
			seedBaseline(ctx, fresh);

			const datum = bringToCap(atCap);

			notenApi
				.importPruefungen(ctx.lvId, ctx.semKurzbz, [
					{
						uid: atCap.uid,
						note: ctx.notenScale[0],
						punkte: null,
						datum,
						lehreinheit_id: atCap.lehreinheit_id,
					},
					{
						uid: fresh.uid,
						note: ctx.notenScale[0],
						punkte: null,
						datum,
						lehreinheit_id: fresh.lehreinheit_id,
					},
				])
				.then((response) => {
					const data = expectNotenSuccess(response, "importPruefungen");
					expectBulkRowError(data, atCap.uid, "maxAntritteReached");
					expectBulkRowAccepted(data, fresh.uid);
				});
		});

		// Punkte mode: the Note comes from the Notenschluessel; the row sends only Punkte
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
					.importPruefungen(ctx.lvId, ctx.semKurzbz, [bulkRow(atCap), bulkRow(fresh)])
					.then((response) => {
						const data = expectNotenSuccess(response, "importPruefungen mit Punkten");
						expectBulkRowError(data, atCap.uid, "maxAntritteReached");
						expectBulkRowAccepted(data, fresh.uid);
					});

				notenApi.getNoteByPunkte(ctx.lvId, ctx.semKurzbz, punkte).then((punkteResponse) => {
					const expectedNote = punkteResponse.body.data;

					readStateViaApi(ctx).then((data) => {
						const newPruefung = pruefungenOf(data, fresh.uid).find(
							(p) => String(p.datum).slice(0, 10) === datum,
						);
						expect(newPruefung, "der neue Termin").to.exist;
						expect(String(newPruefung.note), "die aus den Punkten abgeleitete Note").to.eq(
							String(expectedNote),
						);
					});
				});
			});
		});
	});

	describe("importLvNoten", () => {
		beforeEach(function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENIMPORT", true);
		});

		it("schreibt für jede Zeile eine LV-Note", function () {
			// in the Punkte mode the Note comes from the Notenschluessel, see the Punktemodus block
			requireNotenMode(this, ctx);

			const [a, b] = ctx.students;

			resetNotenState(ctx);

			notenApi
				.importLvNoten(ctx.lvId, ctx.semKurzbz, [
					{ uid: a.uid, note: ctx.notenScale[0], punkte: null },
					{ uid: b.uid, note: ctx.notenScale[1], punkte: null },
				])
				.then((response) => {
					// by uid: each row has the LV-Note or an error
					const data = expectNotenSuccess(response, "importLvNoten");
					expectBulkRowAccepted(data, a.uid);
					expectBulkRowAccepted(data, b.uid);
				});

			readLvGesamtnoteViaDb(ctx, a).then((row) => {
				expect(row, `Zeile von ${a.uid}`).to.not.be.null;
				expect(String(row.note)).to.eq(String(ctx.notenScale[0]));
			});

			readLvGesamtnoteViaDb(ctx, b).then((row) => {
				expect(row, `Zeile von ${b.uid}`).to.not.be.null;
				expect(String(row.note)).to.eq(String(ctx.notenScale[1]));
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
					.importLvNoten(ctx.lvId, ctx.semKurzbz, [
						{ uid: withoutPunkte.uid, note: null, punkte: null },
						{ uid: withPunkte.uid, note: null, punkte: 100 },
					])
					.then((response) => {
						// a broken row must not stop the whole import
						const data = expectNotenSuccess(response, "Notenimport mit Luecke");
						expectBulkRowError(data, withoutPunkte.uid, "c4punkteKeineNoteErmittelt");
						expectBulkRowAccepted(data, withPunkte.uid);
					});

				readLvGesamtnoteViaDb(ctx, withoutPunkte).then((row) => {
					expect(row, "die übersprungene Zeile bleibt ungeschrieben").to.be.null;
				});
			});
		});
	});

	// the switch hides the button; the server rejects a direct call too
	describe("Importschalter", () => {
		it("lehnt den Notenimport ab, wenn der Schalter aus ist", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENIMPORT", false);

			const student = ctx.students[0];

			resetNotenState(ctx);

			notenApi
				.importLvNoten(ctx.lvId, ctx.semKurzbz, [{ uid: student.uid, note: ctx.notenScale[0], punkte: null }])
				.then((response) => expectNotenError(response, "importAusgeschaltet"));

			readLvGesamtnoteViaDb(ctx, student).then((row) => {
				expect(row, "der abgelehnte Import schreibt keine LV-Note").to.be.null;
			});
		});

		it("lehnt den Prüfungsimport ab, wenn der Schalter aus ist", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNGSIMPORT", false);

			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student);

			notenApi
				.importPruefungen(ctx.lvId, ctx.semKurzbz, [
					{
						uid: student.uid,
						lehreinheit_id: student.lehreinheit_id,
						datum: antrittDate(ctx, 1),
						note: ctx.noten.negativ,
						punkte: null,
					},
				])
				.then((response) => expectNotenError(response, "importAusgeschaltet"));

			readStateViaApi(ctx).then((data) => {
				expect(pruefungenOf(data, student.uid), "nur Antritt 1 der Baseline").to.have.length(1);
			});
		});
	});
});
