/**
 * Pruefungsordnung §1: the Antritte.
 *
 * The server checks the rules in this order: A (maximum) -> B (date order) -> C (occurrence limit).
 * Each test builds a state in which only one rule can apply; otherwise an earlier rule answers first.
 *
 * The NOTE decides what counts: "entschuldigt" and "Noch nicht eingetragen" never count.
 * Antritt 1 is a real Pruefung row (givenBaseline seeds it).
 */

import { notenApi, loginAsLektor } from "../../../../support/api/notenApi";
import { expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import {
	requireKommissionellerAntritt,
	requireConfig,
	requireRepeat,
	skipIf,
} from "../../../../support/helpers/notenConfig";
import {
	antrittDate,
	baselineDate,
	loadNotenContext,
	requireDbReset,
	seedPruefung,
	seedZeugnisnote,
	shiftDate,
} from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	pruefungenOf,
	antritteOf,
	editPruefung,
	givenBaseline,
	readStateViaApi,
	verlaufOf,
} from "../../../../support/helpers/notenScenario";

describe("Noten API - Prüfungsantritte (Prüfungsordnung §1)", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((loaded) => {
			ctx = loaded;

			cy.log(
				`LV ${ctx.lvId} / ${ctx.semKurzbz} | maxAntritte=${ctx.maxAntritte} | ` +
					`entschuldigt=${ctx.noten.entschuldigt}`,
			);

			expect(ctx.maxAntritte, "Platz für mindestens eine Wiederholung nach Antritt 1").to.be.greaterThan(1);
		});
	});

	beforeEach(() => loginAsLektor());

	/** Skips if the last Antritt is not the kommissionelle Pruefung. */
	const skipUnlessLastKommissionell = (test) =>
		skipIf(
			test,
			ctx.cisConfig.CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT !== ctx.maxAntritte,
			"Übersprungen: der letzte Antritt ist nicht kommissionell.",
		);

	/** Adds counting Antritte up to maxAntritte. The baseline already has Antritt 1. */
	const fillToCap = (student, firstIndex = 1) => {
		for (let i = 0; i < ctx.maxAntritte - 1; i += 1) {
			addPruefung(ctx, student, {
				note: ctx.noten.negativ,
				datum: antrittDate(ctx, firstIndex + i),
			}).then((response) => {
				expectNotenSuccess(response, `Antritt ${i + 2} von ${ctx.maxAntritte}`);
			});
		}
		return antrittDate(ctx, firstIndex + ctx.maxAntritte - 1);
	};

	// The configuration names the special Noten by Bezeichnung. The API returns the resolved id
	// (14 on the demo data). If the lookup fails, every rule uses the wrong Note without an error.
	it("löst die Sondernoten über ihre Bezeichnung aus tbl_note auf", () => {
		notenApi.getCisConfig().then((response) => {
			const config = expectNotenSuccess(response, "getCisConfig");

			expect(String(config.NOTE_ENTSCHULDIGT)).to.eq(String(ctx.noten.entschuldigt));
			expect(config.NOTEN_OHNE_ANTRITT.map(String)).to.include.members([
				String(ctx.noten.entschuldigt),
				String(ctx.noten.nochNichtEingetragen),
			]);
			expect(
				Object.keys(config.NOTEN_OCCURRENCE_LIMIT_MAP).map(String),
				"the occurrence limit must be keyed on the resolved PK",
			).to.include(String(ctx.noten.entschuldigt));
		});
	});

	describe("Regel A - die Höchstzahl der Prüfungsantritte", () => {
		it("lehnt einen Antritt ab, sobald das konfigurierte Maximum erreicht ist", function () {
			// without CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF the chain ends earlier, see the kommPruefNichtErlaubt test
			requireConfig(this, ctx, "CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF", true);

			const student = ctx.students[0];

			givenBaseline(ctx, student);

			const nextDate = fillToCap(student);

			addPruefung(ctx, student, { note: ctx.notenScale[0], datum: nextDate }).then((response) => {
				expectNotenError(response, "maxAntritteReached");
			});

			// and the rejected Antritt wrote nothing
			readStateViaApi(ctx).then((data) => {
				const dates = pruefungenOf(data, student.uid).map((p) => String(p.datum).slice(0, 10));
				expect(dates, "keine Zeile trägt das abgelehnte Datum").to.not.include(nextDate);

				expect(antritteOf(data, student.uid), "genau die möglichen zählenden Zeilen").to.have.length(
					ctx.maxAntritte,
				);
			});
		});
	});

	describe("Regel B - die Antritte folgen der Zeitordnung", () => {
		beforeEach(function () {
			requireRepeat(this, ctx);
		});

		// "Noch nicht eingetragen" takes a date without counting, so rule A does not answer first
		const givenOpenPruefungOn = (student, datum) => {
			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.noten.nochNichtEingetragen, datum }).then((response) => {
				expectNotenSuccess(response, "eine offene Prüfung ohne Antritt");
			});
		};

		it("lehnt einen neuen Antritt vor einem bestehenden ab", () => {
			const student = ctx.students[1];

			givenOpenPruefungOn(student, antrittDate(ctx, 2));

			addPruefung(ctx, student, { note: ctx.notenScale[0], datum: antrittDate(ctx, 1) }).then((response) => {
				expectNotenError(response, "pruefungDatumBeforeExisting");
			});
		});

		it("nimmt einen neuen Antritt nach allen bestehenden an", () => {
			const student = ctx.students[1];

			givenOpenPruefungOn(student, antrittDate(ctx, 2));

			addPruefung(ctx, student, { note: ctx.notenScale[0], datum: antrittDate(ctx, 3) }).then((response) => {
				expectNotenSuccess(response, "eine Prüfung streng nach der vorhandenen");
			});
		});
	});

	describe("'entschuldigt' gilt nur einmal", () => {
		beforeEach(function () {
			requireRepeat(this, ctx);
		});

		it("lehnt einen zweiten entschuldigten Antritt ab", () => {
			const student = ctx.students[2];

			givenBaseline(ctx, student);

			addPruefung(ctx, student, { note: ctx.noten.entschuldigt, datum: antrittDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "erste entschuldigte Prüfung"),
			);

			addPruefung(ctx, student, { note: ctx.noten.entschuldigt, datum: antrittDate(ctx, 2) }).then((response) =>
				expectNotenError(response, "noteOccuranceLimitReached"),
			);

			readStateViaApi(ctx).then((data) => {
				const entschuldigt = pruefungenOf(data, student.uid).filter(
					(p) => String(p.note) === String(ctx.noten.entschuldigt),
				);
				expect(entschuldigt, "nur eine entschuldigte Prüfung ist erlaubt").to.have.length(1);
			});
		});
	});

	describe("'entschuldigt' verbraucht keinen Antritt", () => {
		beforeEach(function () {
			requireRepeat(this, ctx);
		});

		it("nimmt nach einem entschuldigten Antritt weiter eine echte Note an", () => {
			const student = ctx.students[0];

			givenBaseline(ctx, student);

			addPruefung(ctx, student, { note: ctx.noten.entschuldigt, datum: antrittDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "entschuldigte Prüfung"),
			);

			// entschuldigt uses no Antritt, so this Note is still within the limit
			addPruefung(ctx, student, { note: ctx.notenScale[0], datum: antrittDate(ctx, 2) }).then((response) =>
				expectNotenSuccess(
					response,
					"eine Note nach einer entschuldigten Prüfung (entschuldigt verbraucht keinen Antritt)",
				),
			);

			readStateViaApi(ctx).then((data) => {
				expect(
					verlaufOf(data, student.uid).antrittCount,
					"Antritt 1 and the new Note; entschuldigt uses no Antritt",
				).to.eq(2);
			});
		});
	});

	describe("die kommissionelle Prüfung ist der letzte Antritt", () => {
		// §17 Abs 1: the second repeat is kommissionell. The server derives the Pruefungstyp from the
		// position and writes it to `pruefungstyp_kurzbz`, because the StV still reads this column.
		it("schreibt den letzten Antritt als kommissionelle Prüfung", function () {
			requireKommissionellerAntritt(this, ctx);
			skipUnlessLastKommissionell(this);

			const student = ctx.students[3];

			givenBaseline(ctx, student);

			// fill up to the second-to-last Antritt
			for (let i = 1; i < ctx.maxAntritte - 1; i += 1) {
				addPruefung(ctx, student, { note: ctx.noten.negativ, datum: antrittDate(ctx, i) }).then((response) =>
					expectNotenSuccess(response, `Antritt ${i + 1}`),
				);
			}

			addPruefung(ctx, student, {
				note: ctx.notenScale[0],
				datum: antrittDate(ctx, ctx.maxAntritte),
			}).then((response) => {
				const { pruefung } = expectNotenSuccess(response, "letzter Antritt")[student.uid];
				expect(pruefung.pruefungstyp_kurzbz, "der letzte Antritt ist kommissionell").to.eq(
					ctx.cisConfig.PRUEFUNG_TYP_KOMMISSIONELL,
				);
			});

			readStateViaApi(ctx).then((data) => {
				const verlauf = verlaufOf(data, student.uid);
				expect(verlauf.antrittCount, "die kommissionelle zählt als Antritt").to.eq(ctx.maxAntritte);
				expect(verlauf.canAdd, "danach ist kein Antritt mehr möglich").to.be.false;
			});
		});

		it("verweigert den letzten Antritt, wenn das Tool ihn nicht anlegen darf", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF", false);
			skipUnlessLastKommissionell(this);

			const student = ctx.students[4];

			givenBaseline(ctx, student);

			// fill up to the second-to-last Antritt
			for (let i = 1; i < ctx.maxAntritte - 1; i += 1) {
				addPruefung(ctx, student, { note: ctx.noten.negativ, datum: antrittDate(ctx, i) }).then((response) =>
					expectNotenSuccess(response, `Antritt ${i + 1}`),
				);
			}

			readStateViaApi(ctx).then((data) => {
				const verlauf = verlaufOf(data, student.uid);
				expect(verlauf.kommPruefLocked, "der Verlauf nennt den Grund").to.be.true;
				expect(verlauf.canAdd, "und lässt keinen Antritt mehr zu").to.be.false;
			});

			addPruefung(ctx, student, {
				note: ctx.notenScale[0],
				datum: antrittDate(ctx, ctx.maxAntritte),
			}).then((response) => expectNotenError(response, "kommPruefNichtErlaubt"));
		});

		// A kommissionelle Pruefung closes the chain also without a counting Note. Otherwise a new
		// Antritt would be possible after an ungraded kommissionelle Pruefung.
		it("sperrt weitere Antritte auch bei einer kommPruef ohne zählende Note", () => {
			const student = ctx.students[1];

			givenBaseline(ctx, student);

			seedPruefung(ctx, student, {
				note: ctx.noten.nochNichtEingetragen,
				datum: antrittDate(ctx, 1),
				type: "kommPruef",
			});

			readStateViaApi(ctx).then((data) => {
				const verlauf = verlaufOf(data, student.uid);
				expect(verlauf.terminal, "die Kette ist geschlossen").to.be.true;
				expect(verlauf.canAdd, "kein weiterer Antritt möglich").to.be.false;
			});

			addPruefung(ctx, student, { note: ctx.notenScale[0], datum: antrittDate(ctx, 2) }).then((response) =>
				expectNotenError(response, "maxAntritteReached"),
			);
		});
	});

	describe("'Noch nicht eingetragen' verbraucht keinen Antritt", () => {
		beforeEach(function () {
			requireRepeat(this, ctx);
		});

		it("zählt einen offenen Termin nicht als Antritt", () => {
			const student = ctx.students[3];

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.noten.nochNichtEingetragen, datum: antrittDate(ctx, 1) }).then(
				(response) => expectNotenSuccess(response, "offener Termin"),
			);

			readStateViaApi(ctx).then((data) => {
				const open = pruefungenOf(data, student.uid).find(
					(p) => String(p.note) === String(ctx.noten.nochNichtEingetragen),
				);
				expect(open, "der offene Termin").to.exist;
				expect(open.is_antritt, "der offene Termin verbraucht keinen Antritt").to.be.false;

				const verlauf = verlaufOf(data, student.uid);
				expect(verlauf.antrittCount, "nur Antritt 1").to.eq(1);
				expect(verlauf.canAdd, "ein weiterer Antritt bleibt möglich").to.be.true;
			});
		});
	});

	describe("Anrechnung - gar keine Prüfungen", () => {
		it("lehnt einen Antritt bei angerechneter Zeugnisnote ab", function () {
			const angerechnet = (ctx.cisConfig.NOTEN_ANRECHNUNG || [])[0];
			skipIf(this, angerechnet === undefined, "Übersprungen: NOTEN_ANRECHNUNG löst keine Note auf.");

			const student = ctx.students[4];

			givenBaseline(ctx, student);
			seedZeugnisnote(ctx, student, angerechnet);

			readStateViaApi(ctx).then((data) => {
				const verlauf = verlaufOf(data, student.uid);
				expect(verlauf.angerechnet, "der Verlauf nennt die Anrechnung").to.be.true;
				expect(verlauf.canAdd, "und blockiert weitere Antritte").to.be.false;
			});

			addPruefung(ctx, student, { note: ctx.noten.negativ, datum: antrittDate(ctx, 1) }).then((response) =>
				expectNotenError(response, "c4angerechnetKeinePruefung"),
			);
		});
	});

	describe("Schutz beim Bearbeiten", () => {
		beforeEach(function () {
			requireRepeat(this, ctx);
		});

		/** Antritt 1 < entschuldigt < a real Note. Returns the entschuldigt row, which has a neighbor on both sides. */
		const givenThreePruefungen = (student) => {
			givenBaseline(ctx, student);

			addPruefung(ctx, student, { note: ctx.noten.entschuldigt, datum: antrittDate(ctx, 1) });
			addPruefung(ctx, student, { note: ctx.notenScale[0], datum: antrittDate(ctx, 2) });

			return readStateViaApi(ctx).then((data) => {
				const entschuldigt = pruefungenOf(data, student.uid).find(
					(p) => String(p.note) === String(ctx.noten.entschuldigt),
				);
				expect(entschuldigt, "entschuldigte Prüfung zum Ändern").to.exist;
				return entschuldigt;
			});
		};

		it("lehnt eine Notenänderung nach einem späteren Antritt ab", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETERER_PRUEFUNG", true);

			const student = ctx.students[1];

			givenThreePruefungen(student).then((entschuldigt) => {
				editPruefung(ctx, student, {
					pruefungId: entschuldigt.pruefung_id,
					note: ctx.notenScale[1], // another Note -> locked
					datum: antrittDate(ctx, 1),
				}).then((response) => expectNotenError(response, "pruefungNoteLocked"));
			});
		});

		it("erlaubt weiter eine reine Datumskorrektur zwischen den Nachbarantritten", () => {
			const student = ctx.students[1];

			givenThreePruefungen(student).then((entschuldigt) => {
				// same Note -> the lock does not apply; the date stays strictly between baseline and Antritt 2
				editPruefung(ctx, student, {
					pruefungId: entschuldigt.pruefung_id,
					note: entschuldigt.note,
					datum: shiftDate(antrittDate(ctx, 1), 3),
				})
					.then((response) => expectNotenSuccess(response, "reine Datumskorrektur innerhalb der Grenzen"))
					.then(() => readStateViaApi(ctx))
					.then((data) => {
						const moved = pruefungenOf(data, student.uid).find(
							(p) => p.pruefung_id === entschuldigt.pruefung_id,
						);
						expect(moved, "die angesprochene Zeile bleibt").to.exist;
						expect(String(moved.datum).slice(0, 10), "und genau sie ist verschoben").to.eq(
							shiftDate(antrittDate(ctx, 1), 3),
						);
					});
			});
		});

		it("lehnt ein Datum am oder vor dem vorherigen Antritt ab", () => {
			const student = ctx.students[2];

			givenThreePruefungen(student).then((entschuldigt) => {
				editPruefung(ctx, student, {
					pruefungId: entschuldigt.pruefung_id,
					note: entschuldigt.note,
					datum: baselineDate(ctx), // == the Antritt-1 date -> not strictly after
				}).then((response) => expectNotenError(response, "pruefungDatumOutOfRange"));
			});
		});
	});
});
