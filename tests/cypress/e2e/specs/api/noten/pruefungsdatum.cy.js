/**
 * The configurable rules for the date of a Pruefung.
 *
 * No rule value is hardcoded. Each switch has one test per branch. A test skips itself when the other
 * branch is configured, and a profile in tests/cypress/profiles/noten.js runs it.
 */

import { notenApi, loginAsLektor } from "../../../../support/api/notenApi";
import {
	expectBulkRowAccepted,
	expectBulkRowError,
	expectNotenError,
	expectNotenSuccess,
} from "../../../../support/helpers/notenErrors";
import { requireConfig, requireNotenMode, requireRepeat, skipIf } from "../../../../support/helpers/notenConfig";
import {
	antrittDate,
	baselineDate,
	loadNotenContext,
	requireDbReset,
	resetNotenState,
	seedBaseline,
	shiftDate,
} from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	pruefungenOf,
	editPruefung,
	givenBaseline,
	readStateViaApi,
} from "../../../../support/helpers/notenScenario";

describe("Noten API - konfigurierbare Terminregeln", () => {
	let ctx;

	before(() => {
		loadNotenContext().then((loaded) => {
			ctx = loaded;
			cy.log(`TERMIN_GLEICHER_TAG = ${ctx.cisConfig.CIS_GESAMTNOTE_PRUEFUNG_GLEICHER_TAG}`);
			cy.log(
				`NOTE_SPERRE_BEI_SPAETEREM_TERMIN = ${ctx.cisConfig.CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETERER_PRUEFUNG}`,
			);
			cy.log(`DATUM_ZUKUNFT = ${ctx.cisConfig.CIS_GESAMTNOTE_DATUM_ZUKUNFT}`);
			cy.log(`ANTRITT_MIN_ABSTAND_TAGE = ${ctx.cisConfig.CIS_GESAMTNOTE_ANTRITT_MIN_ABSTAND_TAGE}`);
			cy.log(`ANTRITT_MAX_ABSTAND_TAGE = ${ctx.cisConfig.CIS_GESAMTNOTE_ANTRITT_MAX_ABSTAND_TAGE}`);
		});
	});

	beforeEach(() => loginAsLektor());

	beforeEach(() => requireDbReset());

	// The rules compare Y-m-d strings. A different format sorts the Pruefungen wrong.
	describe("Prüfungsdatum", () => {
		const impossibleDay = () => `${baselineDate(ctx).slice(0, 4)}-02-31`;

		[
			["einen Kalendertag, den es nicht gibt", impossibleDay],
			["das Format d.m.Y", () => antrittDate(ctx, 1).split("-").reverse().join(".")],
			["ein leeres Datum", () => ""],
		].forEach(([label, datum]) => {
			it(`lehnt ${label} ab`, () => {
				const student = ctx.students[0];

				givenBaseline(ctx, student);
				addPruefung(ctx, student, { note: ctx.noten.negativ, datum: datum() }).then((response) =>
					expectNotenError(response, "pruefungsdatumUngueltig"),
				);

				readStateViaApi(ctx).then((data) => {
					expect(pruefungenOf(data, student.uid), "nur Antritt 1").to.have.length(1);
				});
			});
		});

		it("speichert ein Datum mit Uhrzeit als Kalendertag", function () {
			requireRepeat(this, ctx);

			const student = ctx.students[1];
			const datum = antrittDate(ctx, 1);

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.noten.negativ, datum: `${datum} 10:00:00` }).then((response) =>
				expectNotenSuccess(response, "Datum mit Uhrzeit"),
			);

			readStateViaApi(ctx).then((data) => {
				const newPruefung = pruefungenOf(data, student.uid)[1];
				expect(String(newPruefung.datum).slice(0, 10), "der Kalendertag").to.eq(datum);
			});
		});

		it("lehnt im Import nur die Zeile mit dem ungültigen Datum ab", function () {
			requireNotenMode(this, ctx);
			requireRepeat(this, ctx);
			requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNGSIMPORT", true);

			const valid = ctx.students[0];
			const invalid = ctx.students[1];
			const bulkRow = (student, datum) => ({
				uid: student.uid,
				note: ctx.noten.negativ,
				punkte: null,
				datum,
				lehreinheit_id: student.lehreinheit_id,
			});

			resetNotenState(ctx);
			seedBaseline(ctx, valid);
			seedBaseline(ctx, invalid);

			notenApi
				.importPruefungen(ctx.lvId, ctx.semKurzbz, [
					bulkRow(valid, antrittDate(ctx, 1)),
					bulkRow(invalid, impossibleDay()),
				])
				.then((response) => {
					const data = expectNotenSuccess(response, "importPruefungen");
					expectBulkRowAccepted(data, valid.uid);
					expectBulkRowError(data, invalid.uid, "pruefungsdatumUngueltig");
				});

			readStateViaApi(ctx).then((data) => {
				expect(pruefungenOf(data, invalid.uid), "nur Antritt 1").to.have.length(1);
			});
		});
	});

	// A second Pruefung on the same day: too early, or allowed by the configuration.
	describe("CIS_GESAMTNOTE_PRUEFUNG_GLEICHER_TAG", () => {
		beforeEach(function () {
			requireRepeat(this, ctx);
		});

		// Antritt 1 is on baselineDate, and the new Pruefung is on exactly that day
		const pruefungOnBaselineDay = (student) => {
			givenBaseline(ctx, student);
			return addPruefung(ctx, student, { note: ctx.notenScale[1], datum: baselineDate(ctx) });
		};

		it("lehnt einen Termin am selben Tag ab", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNG_GLEICHER_TAG", false);

			pruefungOnBaselineDay(ctx.students[0]).then((response) =>
				expectNotenError(response, "pruefungDatumBeforeExisting"),
			);
		});

		it("nimmt einen Termin am selben Tag an, wenn der Schalter an ist", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNG_GLEICHER_TAG", true);

			pruefungOnBaselineDay(ctx.students[0]).then((response) =>
				expectNotenSuccess(response, "Termin am selben Tag"),
			);
		});

		it("lehnt einen Termin vor einem bestehenden immer ab", () => {
			const student = ctx.students[1];

			givenBaseline(ctx, student);

			// one day earlier is too early in both configurations
			addPruefung(ctx, student, {
				note: ctx.notenScale[1],
				datum: shiftDate(baselineDate(ctx), -1),
			}).then((response) => expectNotenError(response, "pruefungDatumBeforeExisting"));
		});
	});

	// Does a later Pruefung lock the Note of the earlier one?
	describe("CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETERER_PRUEFUNG", () => {
		beforeEach(function () {
			requireRepeat(this, ctx);
		});

		/** Antritt 1 and a later Pruefung. Then the request changes only the Note of Antritt 1. */
		const changeNoteOfAntritt1 = (student) => {
			givenBaseline(ctx, student);

			return addPruefung(ctx, student, { note: ctx.notenScale[1], datum: antrittDate(ctx, 1) })
				.then((response) => expectNotenSuccess(response, "späterer Termin"))
				.then(() => readStateViaApi(ctx))
				.then((data) => {
					const firstAntritt = pruefungenOf(data, student.uid)[0];
					expect(firstAntritt, "Antritt 1").to.exist;

					return editPruefung(ctx, student, {
						pruefungId: firstAntritt.pruefung_id,
						note: ctx.notenScale[1],
						datum: firstAntritt.datum,
					});
				});
		};

		it("sperrt die Note eines früheren Termins", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETERER_PRUEFUNG", true);

			changeNoteOfAntritt1(ctx.students[2]).then((response) => expectNotenError(response, "pruefungNoteLocked"));
		});

		it("lässt die Note eines früheren Termins ändern, wenn der Schalter aus ist", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETERER_PRUEFUNG", false);

			changeNoteOfAntritt1(ctx.students[2]).then((response) =>
				expectNotenSuccess(response, "Note trotz späterem Termin"),
			);
		});
	});

	// A Benotungsdatum in the future.
	describe("CIS_GESAMTNOTE_DATUM_ZUKUNFT", () => {
		const proposalWithTomorrow = (student) => {
			const tomorrow = shiftDate(new Date().toISOString().slice(0, 10), 1);

			givenBaseline(ctx, student, { erstantritt: false });
			return notenApi.saveLvNote(ctx.lvId, ctx.semKurzbz, student.uid, ctx.notenScale[0], null, tomorrow);
		};

		it("lehnt ein künftiges Benotungsdatum ab", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_DATUM_ZUKUNFT", false);

			proposalWithTomorrow(ctx.students[3]).then((response) =>
				expectNotenError(response, "benotungsdatumInZukunft"),
			);
		});

		it("nimmt ein künftiges Benotungsdatum an, wenn der Schalter an ist", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_DATUM_ZUKUNFT", true);

			proposalWithTomorrow(ctx.students[3]).then((response) =>
				expectNotenSuccess(response, "künftiges Benotungsdatum"),
			);
		});
	});

	// The minimum gap between two Antritte. Without a value the rule is off.
	describe("CIS_GESAMTNOTE_ANTRITT_MIN_ABSTAND_TAGE", () => {
		let min;

		beforeEach(function () {
			min = Number(ctx.cisConfig.CIS_GESAMTNOTE_ANTRITT_MIN_ABSTAND_TAGE);
			skipIf(
				this,
				!Number.isFinite(min) || min <= 0,
				"Übersprungen: CIS_GESAMTNOTE_ANTRITT_MIN_ABSTAND_TAGE ist nicht gesetzt.",
			);
			requireRepeat(this, ctx);
		});

		it("lehnt einen Antritt vor Ablauf der Wartefrist ab", () => {
			const student = ctx.students[4];

			givenBaseline(ctx, student);

			addPruefung(ctx, student, {
				note: ctx.notenScale[1],
				datum: shiftDate(baselineDate(ctx), min - 1),
			}).then((response) => expectNotenError(response, "pruefungAbstandZuKurz"));
		});

		it("nimmt einen Antritt genau am Ende der Wartefrist an", () => {
			const student = ctx.students[5];

			givenBaseline(ctx, student);

			addPruefung(ctx, student, {
				note: ctx.notenScale[1],
				datum: shiftDate(baselineDate(ctx), min),
			}).then((response) => expectNotenSuccess(response, "Antritt am Ende der Wartefrist"));
		});

		it("lehnt es ab, einen Antritt in die Wartefrist zu verschieben", () => {
			const student = ctx.students[5];

			givenBaseline(ctx, student);

			addPruefung(ctx, student, { note: ctx.noten.negativ, datum: shiftDate(baselineDate(ctx), min) }).then(
				(response) => {
					const data = expectNotenSuccess(response, "Antritt am Ende der Wartefrist");

					editPruefung(ctx, student, {
						pruefungId: data[student.uid].pruefung.pruefung_id,
						note: ctx.noten.negativ,
						datum: shiftDate(baselineDate(ctx), min - 1),
					}).then((edit) => expectNotenError(edit, "pruefungAbstandZuKurz"));
				},
			);
		});
	});

	// The maximum gap to the next Antritt.
	describe("CIS_GESAMTNOTE_ANTRITT_MAX_ABSTAND_TAGE", () => {
		let max;

		beforeEach(function () {
			max = Number(ctx.cisConfig.CIS_GESAMTNOTE_ANTRITT_MAX_ABSTAND_TAGE);
			skipIf(
				this,
				!Number.isFinite(max) || max <= 0,
				"Übersprungen: CIS_GESAMTNOTE_ANTRITT_MAX_ABSTAND_TAGE ist nicht gesetzt.",
			);
			requireRepeat(this, ctx);
		});

		it("lehnt einen Antritt nach Ablauf der Höchstfrist ab", () => {
			const student = ctx.students[6];

			givenBaseline(ctx, student);

			addPruefung(ctx, student, {
				note: ctx.notenScale[1],
				datum: shiftDate(baselineDate(ctx), max + 1),
			}).then((response) => expectNotenError(response, "pruefungAbstandZuLang"));
		});

		it("lehnt es ab, einen Antritt hinter die Höchstfrist zu verschieben", () => {
			const student = ctx.students[6];

			givenBaseline(ctx, student);

			addPruefung(ctx, student, { note: ctx.noten.negativ, datum: shiftDate(baselineDate(ctx), max) }).then(
				(response) => {
					const data = expectNotenSuccess(response, "Antritt am Ende der Höchstfrist");

					editPruefung(ctx, student, {
						pruefungId: data[student.uid].pruefung.pruefung_id,
						note: ctx.noten.negativ,
						datum: shiftDate(baselineDate(ctx), max + 1),
					}).then((edit) => expectNotenError(edit, "pruefungAbstandZuLang"));
				},
			);
		});
	});
});
