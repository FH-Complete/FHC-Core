/**
 * Bulk- und Importpfade (P2). Dieselben Guards wie beim einzelnen Speichern, aber je Zeile
 * gemeldet: HTTP 200 mit der Fehlermeldung in data[uid].
 */

import { notenApi } from "../../../../support/api/notenApi";
import { expectBulkRowAccepted, expectBulkRowError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import {
	requireKonfiguration,
	requireNotenModus,
	requirePunkteModus,
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

	/** Führt den Studierenden bis an die Grenze. Die Baseline liefert Antritt 1. */
	const bisZurGrenze = (student) => {
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
			// ohne Anlage der kommissionellen Prüfung meldet die Grenze kommPruefNichtErlaubt
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF", true);
		});

		it("lehnt nur die Zeile gegen eine §1-Regel ab und nimmt die übrigen an", () => {
			const atCap = ctx.students[0];
			const fresh = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, atCap);
			seedBaseline(ctx, fresh);

			const datum = bisZurGrenze(atCap);

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
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF", true);
		});

		it("wendet die §1-Regeln je Zeile an", function () {
			// im Punktemodus leitet der Endpunkt die Note aus den Punkten ab; die Regelprüfung
			// deckt der punktebasierte Test unten ab
			requireNotenModus(this, ctx);

			const atCap = ctx.students[0];
			const fresh = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, atCap);
			seedBaseline(ctx, fresh);

			const datum = bisZurGrenze(atCap);

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

		// Punktemodus: die Note kommt aus dem Notenschlüssel, die Zeile liefert nur Punkte.
		describe("Punktemodus", () => {
			beforeEach(function () {
				requirePunkteModus(this, ctx);
			});

			it("wendet die Regeln je Zeile auf die abgeleitete Note an", () => {
				const atCap = ctx.students[0];
				const fresh = ctx.students[1];
				const punkte = 70;

				resetNotenState(ctx);
				seedBaseline(ctx, atCap);
				seedBaseline(ctx, fresh);

				const datum = bisZurGrenze(atCap);
				const zeile = (student) => ({
					uid: student.uid,
					note: null,
					punkte,
					datum,
					lehreinheit_id: student.lehreinheit_id,
				});

				notenApi.savePruefungenBulk(ctx.lvId, ctx.semKurzbz, [zeile(atCap), zeile(fresh)]).then((response) => {
					const data = expectNotenSuccess(response, "savePruefungenBulk mit Punkten");
					expectBulkRowError(data, atCap.uid, "maxAntritteReached");
					expectBulkRowAccepted(data, fresh.uid);
				});

				notenApi.getNoteByPunkte(punkte, ctx.lvId, ctx.semKurzbz).then((antwort) => {
					const erwartet = antwort.body.data;

					readStateViaApi(ctx).then((data) => {
						const neu = attemptsOfStudent(data, fresh.uid).find(
							(p) => String(p.datum).slice(0, 10) === datum,
						);
						expect(neu, "der neue Termin").to.exist;
						expect(String(neu.note), "die aus den Punkten abgeleitete Note").to.eq(String(erwartet));
					});
				});
			});
		});
	});

	describe("saveNotenvorschlagBulk", () => {
		it("schreibt für jede Zeile eine LV-Note", function () {
			// im Punktemodus kommt die Note aus dem Notenschlüssel, siehe den Punktemodus-Block
			requireNotenModus(this, ctx);

			const [a, b] = ctx.students;

			resetNotenState(ctx);

			notenApi
				.saveNotenvorschlagBulk(ctx.lvId, ctx.semKurzbz, [
					{ uid: a.uid, note: ctx.gradeNotes[0], punkte: null },
					{ uid: b.uid, note: ctx.gradeNotes[1], punkte: null },
				])
				.then((response) => {
					// nach uid geschlüsselt: je Zeile die LV-Note oder eine Fehlermeldung
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
				requirePunkteModus(this, ctx);
			});

			it("überspringt eine Zeile ohne Punkte und schreibt die übrigen", () => {
				const ohnePunkte = ctx.students[0];
				const mitPunkten = ctx.students[1];

				resetNotenState(ctx);

				notenApi
					.saveNotenvorschlagBulk(ctx.lvId, ctx.semKurzbz, [
						{ uid: ohnePunkte.uid, note: null, punkte: null },
						{ uid: mitPunkten.uid, note: null, punkte: 100 },
					])
					.then((response) => {
						// eine kaputte Zeile darf den ganzen Import nicht abbrechen
						const data = expectNotenSuccess(response, "Notenimport mit Luecke");
						expectBulkRowError(data, ohnePunkte.uid, "c4punkteKeineNoteErmittelt");
						expectBulkRowAccepted(data, mitPunkten.uid);
					});

				readLvGesamtnoteViaDb(ctx, ohnePunkte.uid).then((row) => {
					expect(row, "die übersprungene Zeile bleibt ungeschrieben").to.be.null;
				});
			});
		});
	});
});
