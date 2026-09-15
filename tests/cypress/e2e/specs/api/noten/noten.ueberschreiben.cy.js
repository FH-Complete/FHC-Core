/**
 * Overwrite-Regeln der Notenvorschlag-Spalte.
 *
 * Drei Regeln: die Editorliste bietet nur lehre-Noten an, die Spalte ist gesperrt, sobald eine
 * Prüfung existiert, und eine Zeugnisnote mit lkt_ueberschreibbar = false sperrt sie ebenfalls.
 * Alle drei stehen in Benotungstool.js UND in Noten::validateNotenvorschlag - der direkte
 * API-Aufruf und der CSV-Import erreichen den Client nie.
 */

import { expectBulkRowError, expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import { requireKonfiguration, requireWiederholung } from "../../../../support/helpers/notenConfig";
import {
	attemptDate,
	loadNotenContext,
	readLvGesamtnote,
	requireDbReset,
	resetNotenState,
	seedZeugnisnote,
} from "../../../../support/helpers/notenTestData";
import { addPruefung, attemptsOfStudent, givenBaseline, readState } from "../../../../support/helpers/notenScenario";
import { notenApi } from "../../../../support/api/notenApi";

describe("Noten API - Notenvorschlag overwrite rules", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;
		});
	});

	const studentFor = (index) => ctx.students[index % ctx.students.length];

	it("refuses a note the editor list never offers", function () {
		requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_VORSCHLAG_NUR_LEHRENOTEN", true);
		if (ctx.notes.nichtLehre === null) {
			this.skip(); // every active note is a lehre note on this instance
		}

		const student = studentFor(0);

		// ohne Erstantritt: sonst greift die Prüfungsregel und die Notenregel bliebe ungeprüft
		givenBaseline(ctx, student, { erstantritt: false });

		notenApi
			.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.notes.nichtLehre)
			.then((response) => expectNotenError(response, "c4noteNichtInLehre"))
			.then(() => readLvGesamtnote(ctx, student.uid))
			.then((row) => {
				expect(String(row.note), "a non-lehre note must not become the LV note").to.eq(
					String(ctx.notes.negativ),
				);
			});
	});

	it("refuses to change the Notenvorschlag once a Prüfung exists", function () {
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
			.then(() => readLvGesamtnote(ctx, student.uid))
			.then((row) => {
				expect(String(row.note), "the attempt's grade must survive the call").to.eq(
					String(ctx.gradeNotes[1]),
				);
			});
	});

	it("refuses to overwrite a locked Zeugnisnote", function () {
		if (ctx.notes.nichtUeberschreibbar === null) {
			this.skip(); // every active note allows the teacher to overwrite it on this instance
		}

		const student = studentFor(4);

		givenBaseline(ctx, student, { erstantritt: false });
		seedZeugnisnote(ctx, student.uid, ctx.notes.nichtUeberschreibbar);

		notenApi
			.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[1])
			.then((response) => expectNotenError(response, "c4zeugnisnoteGesperrt"))
			.then(() => readLvGesamtnote(ctx, student.uid))
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
			if (ctx.notes.nichtLehre !== null && !ohneAntritt.includes(String(ctx.notes.nichtLehre))) return;

			Cypress.log({ name: "skip", message: "Übersprungen: keine Verwaltungsnote, die einen Antritt verbraucht." });
			test.skip();
		};

		const expectNurAntritt1 = (student) =>
			readState(ctx).then((data) => {
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
			readLvGesamtnote(ctx, student.uid).then((row) => {
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
			if (!gesperrt) {
				Cypress.log({ name: "skip", message: "Übersprungen: keine gesperrte Note ausser den Anrechnungsnoten." });
				this.skip();
			}

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
				readLvGesamtnote(ctx, s.uid).then((row) => {
					expect(row, `keine LV-Note für ${s.uid}`).to.be.null;
				}),
			);
		});
	});

	// Derselbe Weg über den CSV-Import, den eine Assistenz tatsächlich fährt. Der Bulk-Endpunkt
	// antwortet 200 und meldet die abgelehnte Zeile in data[uid].
	describe("saveNotenvorschlagBulk", () => {
		beforeEach(function () {
			if (ctx.cisConfig.CIS_GESAMTNOTE_PUNKTE) {
				// im Punktemodus leitet der Import die Note aus den Punkten ab und verwirft eine
				// Zeile ohne Punkte, bevor eine dieser Regeln greift
				Cypress.log({ name: "skip", message: "Skipped: CIS_GESAMTNOTE_PUNKTE ist aktiv." });
				this.skip();
			}
			requireWiederholung(this, ctx);
		});

		it("refuses to change the Notenvorschlag once a Prüfung exists", () => {
			const student = studentFor(3);

			givenBaseline(ctx, student);

			addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) }).then(
				(response) => {
					expectNotenSuccess(response, "seed a Prüfung");
				},
			);

			notenApi
				.saveNotenvorschlagBulk(ctx.lvId, ctx.semKurzbz, [
					{ uid: student.uid, note: ctx.gradeNotes[0], punkte: null },
				])
				.then((response) => {
					const data = expectNotenSuccess(response, "saveNotenvorschlagBulk");
					expectBulkRowError(data, student.uid, "c4notenvorschlagGesperrt");
				})
				.then(() => readLvGesamtnote(ctx, student.uid))
				.then((row) => {
					expect(String(row.note), "the attempt's grade must survive the import").to.eq(
						String(ctx.gradeNotes[1]),
					);
				});
		});
	});
});
