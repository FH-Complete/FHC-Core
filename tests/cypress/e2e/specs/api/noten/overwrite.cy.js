/**
 * The overwrite rules of the proposal column.
 *
 * Three rules: the editor list offers only Noten of the Lehre; the column locks after a repeat; and a
 * Zeugnisnote with lkt_ueberschreibbar = false also locks it. Benotungstool.js AND Noten::validateLvNote
 * both have all three, because the direct API call and the CSV import never reach the client.
 */

import { expectBulkRowError, expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import { requireConfig, requireNotenMode, requireRepeat, skipIf } from "../../../../support/helpers/notenConfig";
import {
	antrittDate,
	loadNotenContext,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedZeugnisnote,
} from "../../../../support/helpers/notenTestData";
import { addPruefung, pruefungenOf, givenBaseline, readStateViaApi } from "../../../../support/helpers/notenScenario";
import { notenApi, loginAsLektor } from "../../../../support/api/notenApi";

describe("Noten API - Regeln zum Überschreiben der LV-Note", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((loaded) => {
			ctx = loaded;
		});
	});

	beforeEach(() => loginAsLektor());

	it("lehnt eine Note ab, die die Auswahlliste nicht anbietet", function () {
		requireConfig(this, ctx, "CIS_GESAMTNOTE_LVNOTE_NUR_LEHRENOTEN", true);
		// on this instance every active Note can be a lehre Note
		skipIf(this, ctx.noten.notLehre === null, "Übersprungen: keine Verwaltungsnote in tbl_note.");

		const student = ctx.students[0];

		// without Antritt 1: otherwise the Pruefung rule answers first, and the Note rule stays untested
		givenBaseline(ctx, student, { erstantritt: false });

		notenApi
			.saveLvNote(ctx.lvId, ctx.semKurzbz, student.uid, ctx.noten.notLehre)
			.then((response) => expectNotenError(response, "c4noteNichtInLehre"))
			.then(() => readLvGesamtnoteViaDb(ctx, student))
			.then((row) => {
				expect(String(row.note), "eine Verwaltungsnote wird nicht zur LV-Note").to.eq(
					String(ctx.noten.negativ),
				);
			});
	});

	it("lehnt eine Änderung des Notenvorschlags nach einer Prüfung ab", function () {
		requireRepeat(this, ctx);
		requireConfig(this, ctx, "CIS_GESAMTNOTE_VORSCHLAG_NACH_WIEDERHOLUNG", false);

		const student = ctx.students[1];

		givenBaseline(ctx, student);

		addPruefung(ctx, student, { note: ctx.notenScale[1], datum: antrittDate(ctx, 1) }).then((response) => {
			expectNotenSuccess(response, "eine Prüfung anlegen");
		});

		// the Note now belongs to the Antritt chain; a direct edit would bypass the Antritt rules
		notenApi
			.saveLvNote(ctx.lvId, ctx.semKurzbz, student.uid, ctx.notenScale[0])
			.then((response) => expectNotenError(response, "c4notenvorschlagGesperrt"))
			.then(() => readLvGesamtnoteViaDb(ctx, student))
			.then((row) => {
				expect(String(row.note), "die Note des Antritts bleibt erhalten").to.eq(String(ctx.notenScale[1]));
			});
	});

	it("lehnt das Überschreiben einer gesperrten Zeugnisnote ab", function () {
		// on this instance the Lektor may overwrite every active Note
		skipIf(this, ctx.noten.notUeberschreibbar === null, "Übersprungen: keine gesperrte Note in tbl_note.");

		const student = ctx.students[4];

		givenBaseline(ctx, student, { erstantritt: false });
		seedZeugnisnote(ctx, student, ctx.noten.notUeberschreibbar);

		notenApi
			.saveLvNote(ctx.lvId, ctx.semKurzbz, student.uid, ctx.notenScale[1])
			.then((response) => expectNotenError(response, "c4zeugnisnoteGesperrt"))
			.then(() => readLvGesamtnoteViaDb(ctx, student))
			.then((row) => {
				expect(String(row.note), "die LV-Note ändert sich nicht").to.eq(String(ctx.noten.negativ));
			});
	});

	// A Pruefung writes the same LV-Note as the proposal and checks the same Note rules.
	describe("Prüfungspfade", () => {
		/** An administrative Note that uses an Antritt. Noten without an Antritt stay allowed. */
		const skipWithoutVerwaltungsnote = (test) => {
			requireConfig(test, ctx, "CIS_GESAMTNOTE_LVNOTE_NUR_LEHRENOTEN", true);

			const withoutAntritt = (ctx.cisConfig.NOTEN_OHNE_ANTRITT || []).map(String);
			const usable = ctx.noten.notLehre !== null && !withoutAntritt.includes(String(ctx.noten.notLehre));

			skipIf(test, !usable, "Übersprungen: keine Verwaltungsnote, die einen Antritt verbraucht.");
		};

		const expectOnlyAntritt1 = (student) =>
			readStateViaApi(ctx).then((data) => {
				expect(pruefungenOf(data, student.uid), "nur Antritt 1").to.have.length(1);
			});

		it("lehnt einen Termin mit einer Verwaltungsnote ab", function () {
			skipWithoutVerwaltungsnote(this);
			requireRepeat(this, ctx);

			const student = ctx.students[0];

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.noten.notLehre, datum: antrittDate(ctx, 1) }).then((response) =>
				expectNotenError(response, "c4noteNichtInLehre"),
			);

			expectOnlyAntritt1(student);
			readLvGesamtnoteViaDb(ctx, student).then((row) => {
				expect(String(row.note), "die LV-Note bleibt").to.eq(String(ctx.noten.negativ));
			});
		});

		it("lehnt einen Termin bei gesperrter Zeugnisnote ab", function () {
			requireRepeat(this, ctx);

			// an Anrechnung Note answers "c4angerechnetKeinePruefung", so the test leaves it out
			const anrechnung = (ctx.cisConfig.NOTEN_ANRECHNUNG || []).map(String);
			const locked = ctx.notenOptions.find(
				(n) => n.lkt_ueberschreibbar === false && !anrechnung.includes(String(n.note)),
			);
			skipIf(this, !locked, "Übersprungen: keine gesperrte Note ausser den Anrechnungsnoten.");

			const student = ctx.students[1];

			givenBaseline(ctx, student);
			seedZeugnisnote(ctx, student, locked.note);

			addPruefung(ctx, student, { note: ctx.noten.negativ, datum: antrittDate(ctx, 1) }).then((response) =>
				expectNotenError(response, "c4zeugnisnoteGesperrt"),
			);

			expectOnlyAntritt1(student);
		});

		it("lehnt im Sammeldialog jede Zeile mit einer Verwaltungsnote ab", function () {
			skipWithoutVerwaltungsnote(this);

			const students = [ctx.students[0], ctx.students[1]];

			resetNotenState(ctx);

			notenApi
				.createPruefungen(
					ctx.lvId,
					ctx.semKurzbz,
					students.map((s) => ({ uid: s.uid, lehreinheit_id: s.lehreinheit_id })),
					{ datum: antrittDate(ctx, 1), note: ctx.noten.notLehre },
				)
				.then((response) => {
					const data = expectNotenSuccess(response, "createPruefungen mit einer Verwaltungsnote");
					students.forEach((s) => expectBulkRowError(data, s.uid, "c4noteNichtInLehre"));
				});

			students.forEach((s) =>
				readLvGesamtnoteViaDb(ctx, s).then((row) => {
					expect(row, `keine LV-Note für ${s.uid}`).to.be.null;
				}),
			);
		});
	});

	// The same flow through the CSV import, the way an Assistenz works. The bulk endpoint returns
	// HTTP 200 and reports the rejected row in data[uid].error.
	describe("importLvNoten", () => {
		beforeEach(function () {
			// in the Punkte mode the import derives the Note from the Punkte and drops a row without
			// Punkte before these rules apply
			requireNotenMode(this, ctx);
			requireRepeat(this, ctx);
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENIMPORT", true);
		});

		it("lehnt je Zeile eine Änderung des Notenvorschlags nach einer Prüfung ab", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_VORSCHLAG_NACH_WIEDERHOLUNG", false);

			const student = ctx.students[3];

			givenBaseline(ctx, student);

			addPruefung(ctx, student, { note: ctx.notenScale[1], datum: antrittDate(ctx, 1) }).then((response) => {
				expectNotenSuccess(response, "eine Prüfung anlegen");
			});

			notenApi
				.importLvNoten(ctx.lvId, ctx.semKurzbz, [{ uid: student.uid, note: ctx.notenScale[0], punkte: null }])
				.then((response) => {
					const data = expectNotenSuccess(response, "importLvNoten");
					expectBulkRowError(data, student.uid, "c4notenvorschlagGesperrt");
				})
				.then(() => readLvGesamtnoteViaDb(ctx, student))
				.then((row) => {
					expect(String(row.note), "die Note des Antritts überlebt den Import").to.eq(
						String(ctx.notenScale[1]),
					);
				});
		});
	});
});
