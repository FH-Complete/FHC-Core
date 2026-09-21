/**
 * Configurable exam date guards.
 *
 * No rule value is hardcoded. Each switch has one test per branch. A test skips itself when the other
 * branch is configured, and a profile in tests/cypress/profiles/noten.js runs it.
 */

import { notenApi } from "../../../../support/api/notenApi";
import {
	expectBulkRowAccepted,
	expectBulkRowError,
	expectNotenError,
	expectNotenSuccess,
} from "../../../../support/helpers/notenErrors";
import { requireConfig, requireNotenMode, requireWiederholung, skipIf } from "../../../../support/helpers/notenConfig";
import {
	attemptDate,
	baselineDate,
	loadNotenContext,
	requireDbReset,
	resetNotenState,
	seedBaseline,
	shiftDate,
} from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	attemptsOfStudent,
	editPruefung,
	givenBaseline,
	readStateViaApi,
} from "../../../../support/helpers/notenScenario";

describe("Noten API - konfigurierbare Terminregeln", () => {
	let ctx;

	before(() => {
		loadNotenContext().then((context) => {
			ctx = context;
			cy.log(`TERMIN_GLEICHER_TAG = ${ctx.cisConfig.CIS_GESAMTNOTE_TERMIN_GLEICHER_TAG}`);
			cy.log(
				`NOTE_SPERRE_BEI_SPAETEREM_TERMIN = ${ctx.cisConfig.CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETEREM_TERMIN}`,
			);
			cy.log(`DATUM_ZUKUNFT = ${ctx.cisConfig.CIS_GESAMTNOTE_DATUM_ZUKUNFT}`);
			cy.log(`ANTRITT_MIN_ABSTAND_TAGE = ${ctx.cisConfig.CIS_GESAMTNOTE_ANTRITT_MIN_ABSTAND_TAGE}`);
			cy.log(`ANTRITT_MAX_ABSTAND_TAGE = ${ctx.cisConfig.CIS_GESAMTNOTE_ANTRITT_MAX_ABSTAND_TAGE}`);
		});
	});

	const studentFor = (index) => {
		const student = ctx.students[index];
		expect(student, `student at index ${index}`).to.exist;
		return student;
	};

	beforeEach(() => requireDbReset());

	// The rules compare Y-m-d strings. A different format sorts the occurrences incorrectly.
	describe("Prüfungsdatum", () => {
		const impossibleDay = () => `${baselineDate(ctx).slice(0, 4)}-02-31`;

		[
			["einen Kalendertag, den es nicht gibt", impossibleDay],
			["das Format d.m.Y", () => attemptDate(ctx, 1).split("-").reverse().join(".")],
			["ein leeres Datum", () => ""],
		].forEach(([label, datum]) => {
			it(`lehnt ${label} ab`, () => {
				const student = studentFor(0);

				givenBaseline(ctx, student);
				addPruefung(ctx, student, { note: ctx.notes.negativ, datum: datum() }).then((response) =>
					expectNotenError(response, "pruefungsdatumUngueltig"),
				);

				readStateViaApi(ctx).then((data) => {
					expect(attemptsOfStudent(data, student.uid), "nur Antritt 1").to.have.length(1);
				});
			});
		});

		it("speichert ein Datum mit Uhrzeit als Kalendertag", function () {
			requireWiederholung(this, ctx);

			const student = studentFor(1);
			const datum = attemptDate(ctx, 1);

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.notes.negativ, datum: `${datum} 10:00:00` }).then((response) =>
				expectNotenSuccess(response, "Datum mit Uhrzeit"),
			);

			readStateViaApi(ctx).then((data) => {
				const newTermin = attemptsOfStudent(data, student.uid)[1];
				expect(String(newTermin.datum).slice(0, 10), "der Kalendertag").to.eq(datum);
			});
		});

		it("lehnt im Import nur die Zeile mit dem ungültigen Datum ab", function () {
			requireNotenMode(this, ctx);
			requireWiederholung(this, ctx);

			const valid = studentFor(0);
			const invalid = studentFor(1);
			const bulkRow = (student, datum) => ({
				uid: student.uid,
				note: ctx.notes.negativ,
				punkte: null,
				datum,
				lehreinheit_id: student.lehreinheit_id,
			});

			resetNotenState(ctx);
			seedBaseline(ctx, valid);
			seedBaseline(ctx, invalid);

			notenApi
				.savePruefungenBulk(ctx.lvId, ctx.semKurzbz, [
					bulkRow(valid, attemptDate(ctx, 1)),
					bulkRow(invalid, impossibleDay()),
				])
				.then((response) => {
					const data = expectNotenSuccess(response, "savePruefungenBulk");
					expectBulkRowAccepted(data, valid.uid);
					expectBulkRowError(data, invalid.uid, "pruefungsdatumUngueltig");
				});

			readStateViaApi(ctx).then((data) => {
				expect(attemptsOfStudent(data, invalid.uid), "nur Antritt 1").to.have.length(1);
			});
		});
	});

	// A second appointment on the same day: either too early or allowed as per config.
	describe("CIS_GESAMTNOTE_TERMIN_GLEICHER_TAG", () => {
		beforeEach(function () {
			requireWiederholung(this, ctx);
		});

		// The initial start date is baselineDate, the new date falls exactly on that day
		const terminOnBaselineDay = (student) => {
			givenBaseline(ctx, student);
			return addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: baselineDate(ctx) });
		};

		it("lehnt einen Termin am selben Tag ab", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_TERMIN_GLEICHER_TAG", false);

			terminOnBaselineDay(studentFor(0)).then((response) =>
				expectNotenError(response, "pruefungDatumBeforeExisting"),
			);
		});

		it("nimmt einen Termin am selben Tag an, wenn der Schalter an ist", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_TERMIN_GLEICHER_TAG", true);

			terminOnBaselineDay(studentFor(0)).then((response) => expectNotenSuccess(response, "Termin am selben Tag"));
		});

		it("lehnt einen Termin vor einem bestehenden immer ab", () => {
			const student = studentFor(1);

			givenBaseline(ctx, student);

			// One day earlier is too early in both configurations
			addPruefung(ctx, student, {
				note: ctx.gradeNotes[1],
				datum: shiftDate(baselineDate(ctx), -1),
			}).then((response) => expectNotenError(response, "pruefungDatumBeforeExisting"));
		});
	});

	// Does a later appointment block the earlier one?
	describe("CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETEREM_TERMIN", () => {
		beforeEach(function () {
			requireWiederholung(this, ctx);
		});

		/** First performance and a later date. After that, the request only changes the score for the first performance. */
		const changeNoteOfAntritt1 = (student) => {
			givenBaseline(ctx, student);

			return addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) })
				.then((response) => expectNotenSuccess(response, "späterer Termin"))
				.then(() => readStateViaApi(ctx))
				.then((data) => {
					const firstAntritt = attemptsOfStudent(data, student.uid)[0];
					expect(firstAntritt, "Antritt 1").to.exist;

					return editPruefung(ctx, student, {
						pruefungId: firstAntritt.pruefung_id,
						note: ctx.gradeNotes[1],
						datum: firstAntritt.datum,
					});
				});
		};

		it("sperrt die Note eines früheren Termins", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETEREM_TERMIN", true);

			changeNoteOfAntritt1(studentFor(2)).then((response) => expectNotenError(response, "pruefungNoteLocked"));
		});

		it("lässt die Note eines früheren Termins ändern, wenn der Schalter aus ist", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETEREM_TERMIN", false);

			changeNoteOfAntritt1(studentFor(2)).then((response) =>
				expectNotenSuccess(response, "Note trotz späterem Termin"),
			);
		});
	});

	// The grading date is in the future.
	describe("CIS_GESAMTNOTE_DATUM_ZUKUNFT", () => {
		const uebernahmeWithTomorrow = (student) => {
			const tomorrow = shiftDate(new Date().toISOString().slice(0, 10), 1);

			givenBaseline(ctx, student, { erstantritt: false });
			return notenApi.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[0], null, tomorrow);
		};

		it("lehnt ein künftiges Benotungsdatum ab", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_DATUM_ZUKUNFT", false);

			uebernahmeWithTomorrow(studentFor(3)).then((response) =>
				expectNotenError(response, "benotungsdatumInZukunft"),
			);
		});

		it("nimmt ein künftiges Benotungsdatum an, wenn der Schalter an ist", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_DATUM_ZUKUNFT", true);

			uebernahmeWithTomorrow(studentFor(3)).then((response) =>
				expectNotenSuccess(response, "künftiges Benotungsdatum"),
			);
		});
	});

	// Die Wartefrist zwischen zwei Antritten. Ohne Wert ist die Regel aus.
	describe("CIS_GESAMTNOTE_ANTRITT_MIN_ABSTAND_TAGE", () => {
		let min;

		beforeEach(function () {
			min = Number(ctx.cisConfig.CIS_GESAMTNOTE_ANTRITT_MIN_ABSTAND_TAGE);
			skipIf(
				this,
				!Number.isFinite(min) || min <= 0,
				"Übersprungen: CIS_GESAMTNOTE_ANTRITT_MIN_ABSTAND_TAGE ist nicht gesetzt.",
			);
			requireWiederholung(this, ctx);
		});

		it("lehnt einen Antritt vor Ablauf der Wartefrist ab", () => {
			const student = studentFor(4);

			givenBaseline(ctx, student);

			addPruefung(ctx, student, {
				note: ctx.gradeNotes[1],
				datum: shiftDate(baselineDate(ctx), min - 1),
			}).then((response) => expectNotenError(response, "pruefungAbstandZuKurz"));
		});

		it("nimmt einen Antritt genau am Ende der Wartefrist an", () => {
			const student = studentFor(5);

			givenBaseline(ctx, student);

			addPruefung(ctx, student, {
				note: ctx.gradeNotes[1],
				datum: shiftDate(baselineDate(ctx), min),
			}).then((response) => expectNotenSuccess(response, "Antritt am Ende der Wartefrist"));
		});
	});

	// Die Höchstfrist bis zum nächsten Antritt.
	describe("CIS_GESAMTNOTE_ANTRITT_MAX_ABSTAND_TAGE", () => {
		let max;

		beforeEach(function () {
			max = Number(ctx.cisConfig.CIS_GESAMTNOTE_ANTRITT_MAX_ABSTAND_TAGE);
			skipIf(
				this,
				!Number.isFinite(max) || max <= 0,
				"Übersprungen: CIS_GESAMTNOTE_ANTRITT_MAX_ABSTAND_TAGE ist nicht gesetzt.",
			);
			requireWiederholung(this, ctx);
		});

		it("lehnt einen Antritt nach Ablauf der Höchstfrist ab", () => {
			const student = studentFor(6);

			givenBaseline(ctx, student);

			addPruefung(ctx, student, {
				note: ctx.gradeNotes[1],
				datum: shiftDate(baselineDate(ctx), max + 1),
			}).then((response) => expectNotenError(response, "pruefungAbstandZuLang"));
		});
	});
});
