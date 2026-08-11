/**
 * Bulk- und Importpfade (P2). Dieselben Guards wie beim einzelnen Speichern, aber je Zeile
 * gemeldet: HTTP 200 mit der Fehlermeldung in data[uid].
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
	attemptsOfStudent,
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

			resetNotenState(ctx);
			seedBaseline(ctx, atCap.uid, { freigegeben: true });
			seedBaseline(ctx, fresh.uid, { freigegeben: true });

			// drive the first student up to the cap - the baseline already provides Antritt 1
			for (let i = 0; i < ctx.maxAntritte - 1; i += 1) {
				addPruefung(ctx, atCap, {
					note: ctx.gradeNotes[0],
					datum: attemptDate(ctx, i + 1),
				}).then((response) => {
					expectNotenSuccess(response, `bring ${atCap.uid} to the cap (attempt ${i + 2})`);
				});
			}

			const datum = attemptDate(ctx, ctx.maxAntritte + 1);

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

			readState(ctx).then((data) => {
				const created = attemptsOfStudent(data, fresh.uid);
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
			}).then((response) => {
				expectNotenSuccess(response, "seed an open attempt");
			});

			notenApi
				.createPruefungen(
					[{ uid: student.uid, lehreinheit_id: student.lehreinheit_id }],
					attemptDate(ctx, 2), // earlier than the existing attempt
					ctx.lvId,
					ctx.semKurzbz,
				)
				.then((response) => {
					const data = expectNotenSuccess(response, "createPruefungen out of order");
					expectBulkRowError(data, student.uid, "pruefungDatumBeforeExisting");
				});
		});

	});

	describe("savePruefungenBulk", () => {
		beforeEach(() => {
			requireDbReset();
		});

		it("applies the §1 rules per row", function () {
			if (ctx.cisConfig.CIS_GESAMTNOTE_PUNKTE) {
				// im Punktemodus leitet der Endpunkt die Note aus den Punkten ab; die Regelprüfung
				// deckt der punktebasierte Test unten ab
				cy.log("Skipped: CIS_GESAMTNOTE_PUNKTE ist aktiv, siehe den punktebasierten Test.");
				this.skip();
			}

			const atCap = ctx.students[0];
			const fresh = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, atCap.uid, { freigegeben: true });
			seedBaseline(ctx, fresh.uid, { freigegeben: true });

			for (let i = 0; i < ctx.maxAntritte - 1; i += 1) {
				addPruefung(ctx, atCap, { note: ctx.gradeNotes[0], datum: attemptDate(ctx, i + 1) });
			}

			const datum = attemptDate(ctx, ctx.maxAntritte + 1);

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
				if (!ctx.cisConfig.CIS_GESAMTNOTE_PUNKTE) {
					cy.log("Skipped: CIS_GESAMTNOTE_PUNKTE ist aus.");
					this.skip();
				}
			});

			it("leitet die Note aus den Punkten ab", () => {
				const student = ctx.students[1];

				resetNotenState(ctx);
				seedBaseline(ctx, student.uid, { freigegeben: true });

				// erwartete Note zur Laufzeit erfragen, damit der Notenschlüssel nicht hardcodiert ist
				notenApi.getNoteByPunkte(100, ctx.lvId, ctx.semKurzbz).then((response) => {
					const erwartet = expectNotenSuccess(response, "getNoteByPunkte(100)");
					expect(erwartet, "100 Punkte müssen eine Note ergeben").to.not.be.null;

					notenApi
						.savePruefungenBulk(ctx.lvId, ctx.semKurzbz, [
							{
								uid: student.uid,
								note: null,
								punkte: 100,
								datum: attemptDate(ctx, 1),
								lehreinheit_id: student.lehreinheit_id,
							},
						])
						.then((bulkResponse) => {
							const data = expectNotenSuccess(bulkResponse, "savePruefungenBulk mit Punkten");
							expectBulkRowAccepted(data, student.uid);
							expect(String(data[student.uid].savedPruefung[0].note)).to.eq(String(erwartet));
						});
				});
			});

			it("überspringt eine Zeile ohne Punkte und schreibt die übrigen", () => {
				const ohnePunkte = ctx.students[0];
				const mitPunkten = ctx.students[1];

				resetNotenState(ctx);
				seedBaseline(ctx, ohnePunkte.uid, { freigegeben: true });
				seedBaseline(ctx, mitPunkten.uid, { freigegeben: true });

				notenApi
					.savePruefungenBulk(ctx.lvId, ctx.semKurzbz, [
						{
							uid: ohnePunkte.uid,
							note: null,
							punkte: null,
							datum: attemptDate(ctx, 1),
							lehreinheit_id: ohnePunkte.lehreinheit_id,
						},
						{
							uid: mitPunkten.uid,
							note: null,
							punkte: 100,
							datum: attemptDate(ctx, 1),
							lehreinheit_id: mitPunkten.lehreinheit_id,
						},
					])
					.then((response) => {
						// eine kaputte Zeile darf den ganzen Import nicht abbrechen
						const data = expectNotenSuccess(response, "savePruefungenBulk mit Luecke");
						expectBulkRowError(data, ohnePunkte.uid, "c4punkteKeineNoteErmittelt");
						expectBulkRowAccepted(data, mitPunkten.uid);
					});
			});
		});
	});

	describe("saveNotenvorschlagBulk", () => {
		it("writes an LV note for every row", function () {
			if (ctx.cisConfig.CIS_GESAMTNOTE_PUNKTE) {
				// im Punktemodus kommt die Note aus dem Notenschlüssel, siehe den Punktemodus-Block
				cy.log("Skipped: CIS_GESAMTNOTE_PUNKTE ist aktiv.");
				this.skip();
			}

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

			readLvGesamtnote(ctx, a.uid).then((row) => {
				expect(row, `row for ${a.uid}`).to.not.be.null;
				expect(String(row.note)).to.eq(String(ctx.gradeNotes[0]));
			});

			readLvGesamtnote(ctx, b.uid).then((row) => {
				expect(row, `row for ${b.uid}`).to.not.be.null;
				expect(String(row.note)).to.eq(String(ctx.gradeNotes[1]));
			});
		});

		describe("Punktemodus", () => {
			beforeEach(function () {
				if (!ctx.cisConfig.CIS_GESAMTNOTE_PUNKTE) {
					cy.log("Skipped: CIS_GESAMTNOTE_PUNKTE ist aus.");
					this.skip();
				}
			});

			it("schreibt die aus den Punkten abgeleitete Note", () => {
				const student = ctx.students[0];

				resetNotenState(ctx);

				notenApi.getNoteByPunkte(100, ctx.lvId, ctx.semKurzbz).then((response) => {
					const erwartet = expectNotenSuccess(response, "getNoteByPunkte(100)");
					expect(erwartet, "100 Punkte müssen eine Note ergeben").to.not.be.null;

					notenApi
						.saveNotenvorschlagBulk(ctx.lvId, ctx.semKurzbz, [
							{ uid: student.uid, note: null, punkte: 100 },
						])
						.then((bulk) => expectBulkRowAccepted(expectNotenSuccess(bulk, "Notenimport"), student.uid))
						.then(() => readLvGesamtnote(ctx, student.uid))
						.then((row) => {
							expect(String(row.note), "Note kommt aus dem Notenschlüssel").to.eq(String(erwartet));
							expect(Number(row.punkte), "die Punkte werden mitgeschrieben").to.eq(100);
						});
				});
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

				readLvGesamtnote(ctx, ohnePunkte.uid).then((row) => {
					expect(row, "die übersprungene Zeile bleibt ungeschrieben").to.be.null;
				});
			});
		});
	});
});
