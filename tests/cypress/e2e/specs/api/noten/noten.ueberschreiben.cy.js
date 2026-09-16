/**
 * Overwrite-Regeln der Notenvorschlag-Spalte.
 *
 * Drei Regeln: die Editorliste bietet nur lehre-Noten an, die Spalte ist gesperrt, sobald eine
 * Prüfung existiert, und eine Zeugnisnote mit lkt_ueberschreibbar = false sperrt sie ebenfalls.
 * Alle drei stehen in Benotungstool.js UND in Noten::validateNotenvorschlag - der direkte
 * API-Aufruf und der CSV-Import erreichen den Client nie.
 */

import { expectBulkRowError, expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import {
	requireKonfiguration,
	requireNotenModus,
	requireWiederholung,
	skipWenn,
} from "../../../../support/helpers/notenConfig";
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
		requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_VORSCHLAG_NUR_LEHRENOTEN", true);
		// auf dieser Instanz kann jede aktive Note eine Lehrenote sein
		skipWenn(this, ctx.notes.nichtLehre === null, "Übersprungen: keine Verwaltungsnote in tbl_note.");

		const student = studentFor(0);

		// ohne Erstantritt: sonst greift die Prüfungsregel und die Notenregel bliebe ungeprüft
		givenBaseline(ctx, student, { erstantritt: false });

		notenApi
			.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.notes.nichtLehre)
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
		skipWenn(this, ctx.notes.nichtUeberschreibbar === null, "Übersprungen: keine gesperrte Note in tbl_note.");

		const student = studentFor(4);

		givenBaseline(ctx, student, { erstantritt: false });
		seedZeugnisnote(ctx, student.uid, ctx.notes.nichtUeberschreibbar);

		notenApi
			.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[1])
			.then((response) => expectNotenError(response, "c4zeugnisnoteGesperrt"))
			.then(() => readLvGesamtnoteViaDb(ctx, student.uid))
			.then((row) => {
				expect(String(row.note), "the LV note must not change").to.eq(String(ctx.notes.negativ));
			});
	});

	// W1: Ein Termin schreibt dieselbe LV-Note wie die Übernahme und prüft dieselben Notenregeln.
	describe("Prüfungspfade", () => {
		/** Eine Verwaltungsnote, die einen Antritt verbraucht. Noten ohne Antritt bleiben erlaubt. */
		const skipOhneVerwaltungsnote = (test) => {
			requireKonfiguration(test, ctx, "CIS_GESAMTNOTE_VORSCHLAG_NUR_LEHRENOTEN", true);

			const ohneAntritt = (ctx.cisConfig.NOTEN_OHNE_ANTRITT || []).map(String);
			const brauchbar = ctx.notes.nichtLehre !== null && !ohneAntritt.includes(String(ctx.notes.nichtLehre));

			skipWenn(test, !brauchbar, "Übersprungen: keine Verwaltungsnote, die einen Antritt verbraucht.");
		};

		const expectNurAntritt1 = (student) =>
			readStateViaApi(ctx).then((data) => {
				expect(attemptsOfStudent(data, student.uid), "nur Antritt 1").to.have.length(1);
			});

		it("lehnt einen Termin mit einer Verwaltungsnote ab", function () {
			skipOhneVerwaltungsnote(this);
			requireWiederholung(this, ctx);

			const student = studentFor(0);

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.notes.nichtLehre, datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenError(response, "c4noteNichtInLehre"),
			);

			expectNurAntritt1(student);
			readLvGesamtnoteViaDb(ctx, student.uid).then((row) => {
				expect(String(row.note), "die LV-Note bleibt").to.eq(String(ctx.notes.negativ));
			});
		});

		it("lehnt einen Termin bei gesperrter Zeugnisnote ab", function () {
			requireWiederholung(this, ctx);

			// eine Anrechnungsnote meldet c4angerechnetKeinePruefung, deshalb schliesst der Test sie aus
			const anrechnung = (ctx.cisConfig.NOTEN_ANRECHNUNG || []).map(String);
			const gesperrt = ctx.notenOptions.find(
				(n) => n.lkt_ueberschreibbar === false && !anrechnung.includes(String(n.note)),
			);
			skipWenn(this, !gesperrt, "Übersprungen: keine gesperrte Note ausser den Anrechnungsnoten.");

			const student = studentFor(1);

			givenBaseline(ctx, student);
			seedZeugnisnote(ctx, student.uid, gesperrt.note);

			addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenError(response, "c4zeugnisnoteGesperrt"),
			);

			expectNurAntritt1(student);
		});

		it("lehnt im Sammeldialog jede Zeile mit einer Verwaltungsnote ab", function () {
			skipOhneVerwaltungsnote(this);

			const students = [studentFor(0), studentFor(1)];

			resetNotenState(ctx);

			notenApi
				.createPruefungen(
					students.map((s) => ({ uid: s.uid, lehreinheit_id: s.lehreinheit_id })),
					attemptDate(ctx, 1),
					ctx.lvId,
					ctx.semKurzbz,
					ctx.notes.nichtLehre,
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

	// Derselbe Weg über den CSV-Import, den eine Assistenz tatsächlich fährt. Der Bulk-Endpunkt
	// antwortet 200 und meldet die abgelehnte Zeile in data[uid].
	describe("saveNotenvorschlagBulk", () => {
		beforeEach(function () {
			// im Punktemodus leitet der Import die Note aus den Punkten ab und verwirft eine
			// Zeile ohne Punkte, bevor eine dieser Regeln greift
			requireNotenModus(this, ctx);
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
