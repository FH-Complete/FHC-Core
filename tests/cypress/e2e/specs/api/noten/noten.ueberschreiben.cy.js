/**
 * Overwrite rules for the grade suggestion column.
 *
 * Three rules: the editor list only offers “lehre” noten, the column is locked as soon as an
 * exam exists; and a report card grade with lkt_ueberschreibbar = false also locks it.
 * All three are defined in Benotungstool.js AND in Noten::validateNotenvorschlag the direct
 * API call and the CSV import never reach the client.
 */

import { expectBulkRowError, expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import { requireConfig, requireNotenMode, requireWiederholung, skipIf } from "../../../../support/helpers/notenConfig";
import {
	attemptDate,
	loadNotenContext,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedZeugnisnote,
} from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	attemptsOfStudent,
	givenBaseline,
	readStateViaApi,
} from "../../../../support/helpers/notenScenario";
import { notenApi } from "../../../../support/api/notenApi";

describe("Noten API - Regeln zum Überschreiben des Notenvorschlags", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;
		});
	});

	const studentFor = (index) => ctx.students[index % ctx.students.length];

	it("lehnt eine Note ab, die die Auswahlliste nicht anbietet", function () {
		requireConfig(this, ctx, "CIS_GESAMTNOTE_VORSCHLAG_NUR_LEHRENOTEN", true);
		// On this instance, any active note can be a lehre note
		skipIf(this, ctx.notes.notLehre === null, "Übersprungen: keine Verwaltungsnote in tbl_note.");

		const student = studentFor(0);

		// without a first attempt: otherwise, the review rule applies and the grading rule would remain unreviewed
		givenBaseline(ctx, student, { erstantritt: false });

		notenApi
			.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.notes.notLehre)
			.then((response) => expectNotenError(response, "c4noteNichtInLehre"))
			.then(() => readLvGesamtnoteViaDb(ctx, student.uid))
			.then((row) => {
				expect(String(row.note), "a non-lehre note must not become the LV note").to.eq(
					String(ctx.notes.negativ),
				);
			});
	});

	it("lehnt eine Änderung des Notenvorschlags nach einer Prüfung ab", function () {
		requireWiederholung(this, ctx);

		const student = studentFor(1);

		givenBaseline(ctx, student);

		addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) }).then((response) => {
			expectNotenSuccess(response, "seed a Prüfung");
		});

		// the grade now belongs to the attempt history; a direct edit bypasses the Antritt rules
		notenApi
			.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[0])
			.then((response) => expectNotenError(response, "c4notenvorschlagGesperrt"))
			.then(() => readLvGesamtnoteViaDb(ctx, student.uid))
			.then((row) => {
				expect(String(row.note), "the attempt's grade must survive the call").to.eq(String(ctx.gradeNotes[1]));
			});
	});

	it("lehnt das Überschreiben einer gesperrten Zeugnisnote ab", function () {
		// auf dieser Instanz darf die Lehrperson jede aktive Note überschreiben
		skipIf(this, ctx.notes.notUeberschreibbar === null, "Übersprungen: keine gesperrte Note in tbl_note.");

		const student = studentFor(4);

		givenBaseline(ctx, student, { erstantritt: false });
		seedZeugnisnote(ctx, student.uid, ctx.notes.notUeberschreibbar);

		notenApi
			.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[1])
			.then((response) => expectNotenError(response, "c4zeugnisnoteGesperrt"))
			.then(() => readLvGesamtnoteViaDb(ctx, student.uid))
			.then((row) => {
				expect(String(row.note), "the LV note must not change").to.eq(String(ctx.notes.negativ));
			});
	});

	// Ein Termin schreibt dieselbe LV-Note wie die Übernahme und prüft dieselben Notenregeln.
	describe("Prüfungspfade", () => {
		/** Eine Verwaltungsnote, die einen Antritt verbraucht. Noten ohne Antritt bleiben erlaubt. */
		const skipWithoutVerwaltungsnote = (test) => {
			requireConfig(test, ctx, "CIS_GESAMTNOTE_VORSCHLAG_NUR_LEHRENOTEN", true);

			const withoutAntritt = (ctx.cisConfig.NOTEN_OHNE_ANTRITT || []).map(String);
			const usable = ctx.notes.notLehre !== null && !withoutAntritt.includes(String(ctx.notes.notLehre));

			skipIf(test, !usable, "Übersprungen: keine Verwaltungsnote, die einen Antritt verbraucht.");
		};

		const expectOnlyAntritt1 = (student) =>
			readStateViaApi(ctx).then((data) => {
				expect(attemptsOfStudent(data, student.uid), "nur Antritt 1").to.have.length(1);
			});

		it("lehnt einen Termin mit einer Verwaltungsnote ab", function () {
			skipWithoutVerwaltungsnote(this);
			requireWiederholung(this, ctx);

			const student = studentFor(0);

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.notes.notLehre, datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenError(response, "c4noteNichtInLehre"),
			);

			expectOnlyAntritt1(student);
			readLvGesamtnoteViaDb(ctx, student.uid).then((row) => {
				expect(String(row.note), "die LV-Note bleibt").to.eq(String(ctx.notes.negativ));
			});
		});

		it("lehnt einen Termin bei gesperrter Zeugnisnote ab", function () {
			requireWiederholung(this, ctx);

			// A anrechnungs grade reports “c4angerechnetKeinePruefung,” so the test excludes it
			const anrechnung = (ctx.cisConfig.NOTEN_ANRECHNUNG || []).map(String);
			const locked = ctx.notenOptions.find(
				(n) => n.lkt_ueberschreibbar === false && !anrechnung.includes(String(n.note)),
			);
			skipIf(this, !locked, "Übersprungen: keine gesperrte Note ausser den Anrechnungsnoten.");

			const student = studentFor(1);

			givenBaseline(ctx, student);
			seedZeugnisnote(ctx, student.uid, locked.note);

			addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenError(response, "c4zeugnisnoteGesperrt"),
			);

			expectOnlyAntritt1(student);
		});

		it("lehnt im Sammeldialog jede Zeile mit einer Verwaltungsnote ab", function () {
			skipWithoutVerwaltungsnote(this);

			const students = [studentFor(0), studentFor(1)];

			resetNotenState(ctx);

			notenApi
				.createPruefungen(
					students.map((s) => ({ uid: s.uid, lehreinheit_id: s.lehreinheit_id })),
					attemptDate(ctx, 1),
					ctx.lvId,
					ctx.semKurzbz,
					ctx.notes.notLehre,
				)
				.then((response) => {
					const data = expectNotenSuccess(response, "createPruefungen mit einer Verwaltungsnote");
					students.forEach((s) => expectBulkRowError(data, s.uid, "c4noteNichtInLehre"));
				});

			students.forEach((s) =>
				readLvGesamtnoteViaDb(ctx, s.uid).then((row) => {
					expect(row, `keine LV-Note für ${s.uid}`).to.be.null;
				}),
			);
		});
	});

	// The same process via CSV import that an assistant actually follows. The bulk endpoint
	// returns a 200 status code and reports the rejected row in data[uid].
	describe("saveNotenvorschlagBulk", () => {
		beforeEach(function () {
			// In point mode, the import calculates the grade based on the points and discards a
			// row with no points before any of these rules take effect
			requireNotenMode(this, ctx);
			requireWiederholung(this, ctx);
		});

		it("lehnt je Zeile eine Änderung des Notenvorschlags nach einer Prüfung ab", () => {
			const student = studentFor(3);

			givenBaseline(ctx, student);

			addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) }).then((response) => {
				expectNotenSuccess(response, "seed a Prüfung");
			});

			notenApi
				.saveNotenvorschlagBulk(ctx.lvId, ctx.semKurzbz, [
					{ uid: student.uid, note: ctx.gradeNotes[0], punkte: null },
				])
				.then((response) => {
					const data = expectNotenSuccess(response, "saveNotenvorschlagBulk");
					expectBulkRowError(data, student.uid, "c4notenvorschlagGesperrt");
				})
				.then(() => readLvGesamtnoteViaDb(ctx, student.uid))
				.then((row) => {
					expect(String(row.note), "the attempt's grade must survive the import").to.eq(
						String(ctx.gradeNotes[1]),
					);
				});
		});
	});
});
