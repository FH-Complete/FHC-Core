/**
 * The write path (savePruefungForStudent) that savePruefung, createPruefungen and
 * importPruefungen share. The other specs cover the rules.
 *
 * Invariant: one action writes exactly one Pruefung. Exception: next to an LV-Note without a Pruefung
 * row, the first new Pruefung first writes Antritt 1.
 */

import { expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import { requireConfig, requireRepeat } from "../../../../support/helpers/notenConfig";
import {
	antrittDate,
	baselineDate,
	loadNotenContext,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedPruefung,
	shiftDate,
} from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	pruefungenOf,
	editPruefung,
	givenBaseline,
	readStateViaApi,
	verlaufOf,
} from "../../../../support/helpers/notenScenario";
import { notenApi, loginAsLektor } from "../../../../support/api/notenApi";

const dayOf = (value) => String(value).slice(0, 10);

describe("Noten API - Prüfungstermin (Schreibpfad)", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((loaded) => {
			ctx = loaded;
			expect(ctx.maxAntritte, "Platz für mindestens eine Wiederholung").to.be.greaterThan(1);
		});
	});

	beforeEach(() => loginAsLektor());

	it("schreibt je Anlage genau eine Prüfung, ohne Kopie daneben", function () {
		requireRepeat(this, ctx);

		const student = ctx.students[0];

		givenBaseline(ctx, student);

		addPruefung(ctx, student, { note: ctx.notenScale[1], datum: antrittDate(ctx, 1) })
			.then((response) => {
				const { pruefung, lvgesamtnote, verlauf } = expectNotenSuccess(response, "Prüfung anlegen")[
					student.uid
				];

				expect(pruefung, "die gespeicherte Prüfung").to.exist;
				expect(String(pruefung.note)).to.eq(String(ctx.notenScale[1]));
				expect(lvgesamtnote, "die Antwort trägt die LV-Note für die Zeile").to.exist;
				expect(verlauf, "die Antwort trägt den Verlauf").to.exist;
				expect(verlauf.pruefungen, "der Verlauf trägt die Antritte").to.be.an("array");
			})
			.then(() => readStateViaApi(ctx))
			.then((data) => {
				const pruefungen = pruefungenOf(data, student.uid);
				expect(pruefungen, "Antritt 1 der Vorlage und genau eine neue Zeile").to.have.length(2);
			});
	});

	it("leitet Position und Antrittsnummer auf dem Server ab", function () {
		requireRepeat(this, ctx);

		const student = ctx.students[1];

		givenBaseline(ctx, student);

		addPruefung(ctx, student, { note: ctx.noten.negativ, datum: antrittDate(ctx, 1) })
			.then(() => readStateViaApi(ctx))
			.then((data) => {
				const pruefungen = pruefungenOf(data, student.uid);

				expect(
					pruefungen.map((p) => p.position),
					"positions are 1..n in date order",
				).to.deep.eq([1, 2]);
				expect(
					pruefungen.map((p) => p.antritt_nr),
					"beide sind Antritte",
				).to.deep.eq([1, 2]);
				pruefungen.forEach((p) => expect(p.is_antritt, `Antritt an Position ${p.position}`).to.be.true);

				const verlauf = verlaufOf(data, student.uid);
				expect(verlauf.antrittCount, "beide Antritte gezählt").to.eq(2);
				expect(verlauf.maxAntritte, "die Grenze kommt vom Server").to.eq(ctx.maxAntritte);
				// the last Antritt is kommissionell; if the Benotungstool cannot create it, the chain ends here
				const creatable =
					ctx.cisConfig.CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF !== false
						? ctx.maxAntritte
						: ctx.maxAntritte - 1;
				expect(verlauf.canAdd, `canAdd bei 2 von ${creatable} möglichen`).to.eq(2 < creatable);
			});
	});

	it("hängt je Anlage eine neue Zeile an, statt die vorherige zu überschreiben", function () {
		requireRepeat(this, ctx);

		// only an explicit pruefung_id updates; an add always inserts a row
		const student = ctx.students[0];

		givenBaseline(ctx, student);

		let firstId;
		addPruefung(ctx, student, { note: ctx.noten.entschuldigt, datum: antrittDate(ctx, 1) })
			.then((response) => {
				firstId = expectNotenSuccess(response, "erste Anlage")[student.uid].pruefung.pruefung_id;
				return addPruefung(ctx, student, { note: ctx.notenScale[1], datum: antrittDate(ctx, 2) });
			})
			.then((response) => expectNotenSuccess(response, "zweite Anlage"))
			.then(() => readStateViaApi(ctx))
			.then((data) => {
				const pruefungen = pruefungenOf(data, student.uid);

				expect(pruefungen, "Vorlage und zwei neue Zeilen").to.have.length(3);
				expect(
					pruefungen.map((p) => p.pruefung_id),
					"the first add still exists",
				).to.include(firstId);
			});
	});

	it("bearbeitet die Prüfung mit der übergebenen pruefung_id", () => {
		// an entschuldigt and a real Pruefung side by side: no endpoint builds this, so the test seeds it
		const student = ctx.students[2];
		const entschuldigtDate = antrittDate(ctx, 1);
		const withNoteDate = antrittDate(ctx, 2);
		const movedTo = shiftDate(entschuldigtDate, 5);

		givenBaseline(ctx, student);

		let entschuldigtId;
		seedPruefung(ctx, student, { note: ctx.noten.entschuldigt, datum: entschuldigtDate, type: "Termin2" })
			.then((seeded) => {
				entschuldigtId = seeded.pruefungId;
				return seedPruefung(ctx, student, { note: ctx.notenScale[0], datum: withNoteDate, type: "Termin2" });
			})
			// note stays entschuldigt: a later pruefung exists, so only the datum may move
			.then(() =>
				editPruefung(ctx, student, {
					pruefungId: entschuldigtId,
					note: ctx.noten.entschuldigt,
					datum: movedTo,
				}),
			)
			.then((response) => expectNotenSuccess(response, "die entschuldigte Prüfung verschieben"))
			.then(() => readStateViaApi(ctx))
			.then((data) => {
				const rows = pruefungenOf(data, student.uid);
				const entschuldigt = rows.find((p) => p.pruefung_id === entschuldigtId);
				const withNote = rows.find((p) => dayOf(p.datum) === withNoteDate);

				// the other row first: a silent change of a real Note is the worse error
				expect(withNote, "der andere Antritt bleibt").to.exist;
				expect(String(withNote.note), "die andere Prüfung behält ihre Note").to.eq(String(ctx.notenScale[0]));

				expect(entschuldigt, "die entschuldigte Prüfung bleibt").to.exist;
				expect(dayOf(entschuldigt.datum), "die Zeile mit dieser pruefung_id ist verschoben").to.eq(movedTo);
			});
	});

	it("schreibt die LV-Note aus der Note des Antritts", function () {
		requireRepeat(this, ctx);

		const student = ctx.students[3];

		givenBaseline(ctx, student);

		addPruefung(ctx, student, { note: ctx.notenScale[1], datum: antrittDate(ctx, 1) })
			.then((response) => expectNotenSuccess(response, "eine Prüfung mit Note"))
			.then(() => readLvGesamtnoteViaDb(ctx, student))
			.then((row) => {
				expect(String(row.note), "die LV-Note folgt dem letzten Antritt").to.eq(String(ctx.notenScale[1]));
			});
	});

	// An LV-Note without a Pruefung row is Antritt 1: from a proposal without ERSTANTRITT_BEI_UEBERNAHME,
	// or from the time before the Benotungstool. Without that row the count misses it, and the student
	// gets one Antritt too many.
	it("schreibt Antritt 1 aus einer LV-Note ohne Prüfungszeile nach", function () {
		requireRepeat(this, ctx);

		const student = ctx.students[4];

		givenBaseline(ctx, student, { erstantritt: false });
		addPruefung(ctx, student, { note: ctx.noten.negativ, datum: antrittDate(ctx, 1) }).then((response) =>
			expectNotenSuccess(response, "erster Termin neben der LV-Note"),
		);

		readStateViaApi(ctx).then((data) => {
			const pruefungen = pruefungenOf(data, student.uid);
			expect(pruefungen, "Antritt 1 und der neue Termin").to.have.length(2);
			expect(String(pruefungen[0].note), "Antritt 1 trägt die LV-Note").to.eq(String(ctx.noten.negativ));
			expect(dayOf(pruefungen[0].datum) < antrittDate(ctx, 1), "Antritt 1 liegt vor dem neuen Termin").to.be.true;
			expect(
				pruefungen.map((p) => p.antritt_nr),
				"Antrittsnummern",
			).to.deep.eq([1, 2]);
			expect(verlaufOf(data, student.uid).antrittCount, "zwei Antritte").to.eq(2);
		});
	});

	// Rule: the LV-Note is the Note of the last Pruefung that counts as an Antritt. Without such a
	// Pruefung the implicit Antritt 1 stays, otherwise the LV-Note is "Noch nicht eingetragen".
	// The LV-Note is never "entschuldigt".
	describe("LV-Note nach einem Termin", () => {
		const g2 = () =>
			ctx.noten.positiv ??
			ctx.noten.bestnote ??
			ctx.notenScale.find((n) => String(n) !== String(ctx.noten.negativ));

		const expectLvNote = (student, note, message) =>
			readLvGesamtnoteViaDb(ctx, student).then((row) => {
				expect(String(row.note), message).to.eq(String(note));
				return row;
			});

		const expectAntritte = (student, count) =>
			readStateViaApi(ctx).then((data) => {
				expect(verlaufOf(data, student.uid).antrittCount, "antrittCount").to.eq(count);
			});

		it("behält die LV-Note bei einer Datumskorrektur an Termin 1", function () {
			requireRepeat(this, ctx);

			const student = ctx.students[0];

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: g2(), datum: antrittDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "Termin 2"),
			);

			readStateViaApi(ctx)
				.then((data) => {
					const t1 = pruefungenOf(data, student.uid)[0];
					// the Note stays the same, otherwise validateEdit answers pruefungNoteLocked
					return editPruefung(ctx, student, {
						pruefungId: t1.pruefung_id,
						note: t1.note,
						datum: shiftDate(baselineDate(ctx), 1),
					});
				})
				.then((response) => expectNotenSuccess(response, "Datumskorrektur an Termin 1"));

			expectLvNote(student, g2(), "die LV-Note bleibt beim Ergebnis von Termin 2");
		});

		it("behält die LV-Note bei einem neuen entschuldigten Termin", function () {
			requireRepeat(this, ctx);

			const student = ctx.students[1];

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.noten.entschuldigt, datum: antrittDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "entschuldigter Termin"),
			);

			expectLvNote(student, ctx.noten.negativ, "die LV-Note bleibt beim Ergebnis von Termin 1");
			expectAntritte(student, 1);
		});

		// no requireRepeat: the test needs only Antritt 1. With two Antritte and without
		// ALLOW_CREATE_KOMMPRUEF it would fail
		it("setzt 'Noch nicht eingetragen', wenn der erste Termin entschuldigt ist", () => {
			const student = ctx.students[2];

			resetNotenState(ctx);
			addPruefung(ctx, student, { note: ctx.noten.entschuldigt, datum: antrittDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "entschuldigter erster Termin"),
			);

			expectLvNote(student, ctx.noten.nochNichtEingetragen, "die LV-Note ist nie entschuldigt").then((row) => {
				expect(row.punkte, "ohne Punkte").to.be.null;
			});

			readStateViaApi(ctx).then((data) => {
				const verlauf = verlaufOf(data, student.uid);
				expect(verlauf.antrittCount, "der entschuldigte Termin verbraucht keinen Antritt").to.eq(0);
				expect(verlauf.canAdd, "ein weiterer Termin ist möglich").to.be.true;
			});

			addPruefung(ctx, student, { note: ctx.noten.negativ, datum: antrittDate(ctx, 2) }).then((response) =>
				expectNotenSuccess(response, "erster zählender Termin"),
			);

			readStateViaApi(ctx).then((data) => {
				const counting = pruefungenOf(data, student.uid).find(
					(p) => String(p.note) === String(ctx.noten.negativ),
				);
				expect(counting.antritt_nr, "der Studierende hat keinen Antritt verloren").to.eq(1);
			});
		});

		it("setzt 'Noch nicht eingetragen', wenn der einzige Termin nachträglich entschuldigt wird", () => {
			const student = ctx.students[3];

			givenBaseline(ctx, student);

			readStateViaApi(ctx)
				.then((data) => {
					const t1 = pruefungenOf(data, student.uid)[0];
					return editPruefung(ctx, student, {
						pruefungId: t1.pruefung_id,
						note: ctx.noten.entschuldigt,
						datum: dayOf(t1.datum),
					});
				})
				.then((response) => expectNotenSuccess(response, "Termin 1 wird entschuldigt"));

			expectLvNote(student, ctx.noten.nochNichtEingetragen, "die LV-Note ist nie entschuldigt");
			// the old LV-Note does not count as the implicit Antritt 1
			expectAntritte(student, 0);
		});

		it("fällt auf Termin 1 zurück, wenn die Wiederholung nachträglich entschuldigt wird", function () {
			requireRepeat(this, ctx);

			const student = ctx.students[0];

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: g2(), datum: antrittDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "Termin 2"),
			);

			readStateViaApi(ctx)
				.then((data) => {
					const t2 = pruefungenOf(data, student.uid)[1];
					return editPruefung(ctx, student, {
						pruefungId: t2.pruefung_id,
						note: ctx.noten.entschuldigt,
						datum: dayOf(t2.datum),
					});
				})
				.then((response) => expectNotenSuccess(response, "Termin 2 wird entschuldigt"));

			expectLvNote(student, ctx.noten.negativ, "die LV-Note ist wieder das Ergebnis von Termin 1");
			expectAntritte(student, 1);
		});

		it("behält eine LV-Note aus Altdaten als Ergebnis von Antritt 1", function () {
			requireRepeat(this, ctx);

			const student = ctx.students[1];

			givenBaseline(ctx, student, { erstantritt: false });
			addPruefung(ctx, student, { note: ctx.noten.entschuldigt, datum: antrittDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "entschuldigter Termin neben der Altdaten-LV-Note"),
			);

			expectLvNote(student, ctx.noten.negativ, "die Altdaten-LV-Note bleibt");
			expectAntritte(student, 1);
		});

		it("behält das letzte Ergebnis bei einem offenen Termin", function () {
			requireRepeat(this, ctx);

			const student = ctx.students[2];

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.noten.nochNichtEingetragen, datum: antrittDate(ctx, 1) }).then(
				(response) => expectNotenSuccess(response, "offener Termin"),
			);

			expectLvNote(student, ctx.noten.negativ, "die LV-Note bleibt beim Ergebnis von Termin 1");
		});

		it("lehnt 'entschuldigt' als übernommene LV-Note ab", () => {
			const student = ctx.students[3];

			givenBaseline(ctx, student, { erstantritt: false });
			notenApi
				.saveLvNote(ctx.lvId, ctx.semKurzbz, student.uid, ctx.noten.entschuldigt)
				.then((response) => expectNotenError(response, "c4noteNichtInLehre"));

			expectLvNote(student, ctx.noten.negativ, "die LV-Note bleibt");
		});
	});

	// CIS_GESAMTNOTE_PRUEFUNG_HEBT_FREIGABE_AUF: the Freigabe state depends on benotungsdatum > freigabedatum.
	describe("Freigabe nach einem neuen Termin", () => {
		beforeEach(function () {
			// a final Freigabe forbids the new Pruefung
			requireConfig(this, ctx, "CIS_GESAMTNOTE_FREIGABE_FINAL", false);
			requireRepeat(this, ctx);
		});

		const newPruefungAfterFreigabe = (student) => {
			givenBaseline(ctx, student, { freigegeben: true });
			addPruefung(ctx, student, { note: ctx.noten.negativ, datum: antrittDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "Termin nach der Freigabe"),
			);
			return readLvGesamtnoteViaDb(ctx, student);
		};

		it("hebt die Freigabe mit einem neuen Termin auf", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNG_HEBT_FREIGABE_AUF", true);

			newPruefungAfterFreigabe(ctx.students[5]).then((row) => {
				expect(new Date(row.benotungsdatum) > new Date(row.freigabedatum), "benotungsdatum nach freigabedatum")
					.to.be.true;
			});
		});

		it("behält die Freigabe bei einem neuen Termin", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNG_HEBT_FREIGABE_AUF", false);

			newPruefungAfterFreigabe(ctx.students[5]).then((row) => {
				expect(new Date(row.benotungsdatum) > new Date(row.freigabedatum), "benotungsdatum nach freigabedatum")
					.to.be.false;
			});
		});
	});

	it("legt die LV-Note an, wenn ein Studierender noch keine hat", () => {
		// the bulk endpoint runs through the same core and writes the LV-Note before the Pruefung
		const student = ctx.students[0];

		resetNotenState(ctx);

		notenApi
			.createPruefungen(ctx.lvId, ctx.semKurzbz, [{ uid: student.uid, lehreinheit_id: student.lehreinheit_id }], {
				datum: antrittDate(ctx, 1),
			})
			.then((response) => {
				const data = expectNotenSuccess(response, "createPruefungen ohne LV-Note");
				expect(data[student.uid], `Ergebniszeile von ${student.uid}`).to.be.an("object");
				expect(data[student.uid].pruefung, "die Prüfung ist geschrieben").to.exist;
			})
			.then(() => readLvGesamtnoteViaDb(ctx, student))
			.then((row) => {
				expect(row, "die LV-Note entsteht mit").to.not.be.null;
				expect(String(row.note), "trägt die Note der Prüfung").to.eq(String(ctx.noten.nochNichtEingetragen));
			});
	});
});
