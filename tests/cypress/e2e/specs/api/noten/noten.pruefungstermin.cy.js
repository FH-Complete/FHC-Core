/**
 * Der Schreibpfad (savePruefungFuerStudent), den saveStudentPruefung, createPruefungen und
 * savePruefungenBulk teilen. Die Validatoren decken die anderen Specs ab.
 *
 * Invariante: eine Aktion schreibt genau eine Prüfung. Ausnahme: neben einer LV-Note ohne Prüfungszeile
 * schreibt der erste neue Termin zuerst Antritt 1.
 */

import { expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import { requireKonfiguration, requireWiederholung } from "../../../../support/helpers/notenConfig";
import {
	attemptDate,
	baselineDate,
	loadNotenContext,
	readLvGesamtnote,
	requireDbReset,
	resetNotenState,
	seedPruefung,
	shiftDate,
} from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	attemptsOfStudent,
	editPruefung,
	givenBaseline,
	readState,
	verlaufOfStudent,
} from "../../../../support/helpers/notenScenario";
import { notenApi, pruefungenOf } from "../../../../support/api/notenApi";

const dayOf = (value) => String(value).slice(0, 10);

describe("Noten API - Prüfungstermin (write path)", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;
			expect(context.maxAntritte, "needs room for at least one retake").to.be.greaterThan(1);
		});
	});

	const studentFor = (index) => ctx.students[index % ctx.students.length];

	it("writes exactly one Prüfung per add - no snapshot alongside it", function () {
		requireWiederholung(this, ctx);

		const student = studentFor(0);

		givenBaseline(ctx, student);

		addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) })
			.then((response) => {
				const [saved, lvgesamtnote, verlauf] = expectNotenSuccess(response, "add an attempt");

				expect(saved, "savedPruefung").to.exist;
				expect(String(saved.note)).to.eq(String(ctx.gradeNotes[1]));
				expect(lvgesamtnote, "lvgesamtnote is returned so the client can refresh the row").to.exist;
				expect(verlauf, "verlauf is returned").to.exist;
				expect(verlauf.pruefungen, "verlauf carries the attempts").to.be.an("array");
			})
			.then(() => readState(ctx))
			.then((data) => {
				const attempts = attemptsOfStudent(data, student.uid);
				expect(attempts, "baseline Antritt 1 plus exactly one added row").to.have.length(2);
			});
	});

	it("derives position and Antrittsnummer server-side", function () {
		requireWiederholung(this, ctx);

		const student = studentFor(1);

		givenBaseline(ctx, student);

		addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, 1) })
			.then(() => readState(ctx))
			.then((data) => {
				const attempts = attemptsOfStudent(data, student.uid);

				expect(attempts.map((p) => p.position), "positions are 1..n in date order").to.deep.eq([1, 2]);
				expect(attempts.map((p) => p.antritt_nr), "both count as attempts").to.deep.eq([1, 2]);
				attempts.forEach((p) => expect(p.zaehlt, `zaehlt of position ${p.position}`).to.be.true);

				const verlauf = verlaufOfStudent(data, student.uid);
				expect(verlauf.antrittCount, "both attempts counted").to.eq(2);
				expect(verlauf.maxAntritte, "the cap comes from the server").to.eq(ctx.maxAntritte);
				// der letzte Antritt ist kommissionell; darf das Tool ihn nicht anlegen, ist hier Schluss
				const anlegbar = ctx.cisConfig.CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF !== false
					? ctx.maxAntritte
					: ctx.maxAntritte - 1;
				expect(verlauf.canAdd, `canAdd with 2 of ${anlegbar} possible`).to.eq(2 < anlegbar);
			});
	});

	it("appends a new row per add instead of overwriting the previous one", function () {
		requireWiederholung(this, ctx);

		// nur eine explizite pruefung_id aktualisiert, ein Add fügt immer ein
		const student = studentFor(0);

		givenBaseline(ctx, student);

		let firstId;
		addPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum: attemptDate(ctx, 1) })
			.then((response) => {
				firstId = expectNotenSuccess(response, "first add")[0].pruefung_id;
				return addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 2) });
			})
			.then((response) => expectNotenSuccess(response, "second add"))
			.then(() => readState(ctx))
			.then((data) => {
				const attempts = attemptsOfStudent(data, student.uid);

				expect(attempts, "baseline + two added rows").to.have.length(3);
				expect(attempts.map((p) => p.pruefung_id), "the first add still exists").to.include(firstId);
			});
	});

	it("edits the pruefung identified by pruefung_id", () => {
		// An excused and a real attempt side by side - no endpoint builds this, hence the direct seed
		const student = studentFor(2);
		const excusedDate = attemptDate(ctx, 1);
		const gradedDate = attemptDate(ctx, 2);
		const movedTo = shiftDate(excusedDate, 5);

		givenBaseline(ctx, student);

		let excusedId;
		seedPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum: excusedDate, typ: "Termin2" })
			.then((seeded) => {
				excusedId = seeded.pruefungId;
				return seedPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: gradedDate, typ: "Termin2" });
			})
			// note stays entschuldigt: a later pruefung exists, so only the datum may move
			.then(() =>
				editPruefung(ctx, student, {
					pruefungId: excusedId,
					note: ctx.notes.entschuldigt,
					datum: movedTo,
				}),
			)
			.then((response) => expectNotenSuccess(response, "moving the excused attempt"))
			.then(() => readState(ctx))
			.then((data) => {
				const rows = pruefungenOf(data, student.uid);
				const excused = rows.find((p) => p.pruefung_id === excusedId);
				const graded = rows.find((p) => dayOf(p.datum) === gradedDate);

				// the untargeted row first: silently rewriting a real grade is the worse outcome
				expect(graded, "the other attempt still exists").to.exist;
				expect(String(graded.note), "the untargeted attempt keeps its grade").to.eq(
					String(ctx.gradeNotes[0]),
				);

				expect(excused, "the excused attempt still exists").to.exist;
				expect(dayOf(excused.datum), "the row named by pruefung_id moved").to.eq(movedTo);
			});
	});

	it("writes the LV note from the attempt's grade", function () {
		requireWiederholung(this, ctx);

		const student = studentFor(3);

		givenBaseline(ctx, student);

		addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) })
			.then((response) => expectNotenSuccess(response, "attempt with a grade"))
			.then(() => readLvGesamtnote(ctx, student.uid))
			.then((row) => {
				expect(String(row.note), "the LV note follows the newest attempt").to.eq(
					String(ctx.gradeNotes[1]),
				);
			});
	});

	// Eine LV-Note ohne Prüfungszeile ist Antritt 1: aus einer Übernahme ohne Erstantritt oder aus der Zeit
	// vor dem Werkzeug. Ohne die Zeile fiele sie aus der Zählung, und der Studierende bekäme einen Antritt zu viel.
	it("schreibt Antritt 1 aus einer LV-Note ohne Prüfungszeile nach", function () {
		requireWiederholung(this, ctx);

		const student = studentFor(4);

		givenBaseline(ctx, student, { erstantritt: false });
		addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, 1) }).then((response) =>
			expectNotenSuccess(response, "erster Termin neben der LV-Note"),
		);

		readState(ctx).then((data) => {
			const attempts = attemptsOfStudent(data, student.uid);
			expect(attempts, "Antritt 1 und der neue Termin").to.have.length(2);
			expect(String(attempts[0].note), "Antritt 1 trägt die LV-Note").to.eq(String(ctx.notes.negativ));
			expect(dayOf(attempts[0].datum) < attemptDate(ctx, 1), "Antritt 1 liegt vor dem neuen Termin").to.be.true;
			expect(attempts.map((p) => p.antritt_nr), "Antrittsnummern").to.deep.eq([1, 2]);
			expect(verlaufOfStudent(data, student.uid).antrittCount, "zwei Antritte").to.eq(2);
		});
	});

	// Regel: Die LV-Note ist die Note des letzten Termins, der einen Antritt verbraucht. Ohne einen
	// solchen Termin bleibt ein impliziter Erstantritt, sonst gilt "Noch nicht eingetragen".
	// Die LV-Note ist nie 'entschuldigt'.
	describe("LV-Note nach einem Termin", () => {
		const g2 = () =>
			ctx.notes.positiv ?? ctx.notes.bestnote ?? ctx.gradeNotes.find((n) => String(n) !== String(ctx.notes.negativ));

		const expectLvNote = (student, note, message) =>
			readLvGesamtnote(ctx, student.uid).then((row) => {
				expect(String(row.note), message).to.eq(String(note));
				return row;
			});

		const expectAntritte = (student, anzahl) =>
			readState(ctx).then((data) => {
				expect(verlaufOfStudent(data, student.uid).antrittCount, "antrittCount").to.eq(anzahl);
			});

		it("behält die LV-Note bei einer Datumskorrektur an Termin 1", function () {
			requireWiederholung(this, ctx);

			const student = studentFor(0);

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: g2(), datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "Termin 2"),
			);

			readState(ctx)
				.then((data) => {
					const t1 = attemptsOfStudent(data, student.uid)[0];
					// die Note bleibt gleich, sonst meldet validateEdit pruefungNoteLocked
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
			requireWiederholung(this, ctx);

			const student = studentFor(1);

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "entschuldigter Termin"),
			);

			expectLvNote(student, ctx.notes.negativ, "die LV-Note bleibt beim Ergebnis von Termin 1");
			expectAntritte(student, 1);
		});

		// Kein requireWiederholung: der Test braucht nur Antritt 1. Mit zwei Antritten ohne
		// kommissionelle Anlage scheitert er, siehe docs/benotungstool-status.md, Abschnitt 9.
		it("setzt 'Noch nicht eingetragen', wenn der erste Termin entschuldigt ist", () => {
			const student = studentFor(2);

			resetNotenState(ctx);
			addPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "entschuldigter erster Termin"),
			);

			expectLvNote(student, ctx.notes.nochNichtEingetragen, "die LV-Note ist nie entschuldigt").then((row) => {
				expect(row.punkte, "ohne Punkte").to.be.null;
			});

			readState(ctx).then((data) => {
				const verlauf = verlaufOfStudent(data, student.uid);
				expect(verlauf.antrittCount, "der entschuldigte Termin verbraucht keinen Antritt").to.eq(0);
				expect(verlauf.canAdd, "ein weiterer Termin ist möglich").to.be.true;
			});

			addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, 2) }).then((response) =>
				expectNotenSuccess(response, "erster zählender Termin"),
			);

			readState(ctx).then((data) => {
				const zaehlend = attemptsOfStudent(data, student.uid).find(
					(p) => String(p.note) === String(ctx.notes.negativ),
				);
				expect(zaehlend.antritt_nr, "der Studierende hat keinen Antritt verloren").to.eq(1);
			});
		});

		it("setzt 'Noch nicht eingetragen', wenn der einzige Termin nachträglich entschuldigt wird", () => {
			const student = studentFor(3);

			givenBaseline(ctx, student);

			readState(ctx)
				.then((data) => {
					const t1 = attemptsOfStudent(data, student.uid)[0];
					return editPruefung(ctx, student, {
						pruefungId: t1.pruefung_id,
						note: ctx.notes.entschuldigt,
						datum: dayOf(t1.datum),
					});
				})
				.then((response) => expectNotenSuccess(response, "Termin 1 wird entschuldigt"));

			expectLvNote(student, ctx.notes.nochNichtEingetragen, "die LV-Note ist nie entschuldigt");
			// die alte LV-Note zählt nicht als impliziter Erstantritt
			expectAntritte(student, 0);
		});

		it("fällt auf Termin 1 zurück, wenn die Wiederholung nachträglich entschuldigt wird", function () {
			requireWiederholung(this, ctx);

			const student = studentFor(0);

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: g2(), datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "Termin 2"),
			);

			readState(ctx)
				.then((data) => {
					const t2 = attemptsOfStudent(data, student.uid)[1];
					return editPruefung(ctx, student, {
						pruefungId: t2.pruefung_id,
						note: ctx.notes.entschuldigt,
						datum: dayOf(t2.datum),
					});
				})
				.then((response) => expectNotenSuccess(response, "Termin 2 wird entschuldigt"));

			expectLvNote(student, ctx.notes.negativ, "die LV-Note ist wieder das Ergebnis von Termin 1");
			expectAntritte(student, 1);
		});

		it("behält eine LV-Note aus Altdaten als Ergebnis von Antritt 1", function () {
			requireWiederholung(this, ctx);

			const student = studentFor(1);

			givenBaseline(ctx, student, { erstantritt: false });
			addPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "entschuldigter Termin neben der Altdaten-LV-Note"),
			);

			expectLvNote(student, ctx.notes.negativ, "die Altdaten-LV-Note bleibt");
			expectAntritte(student, 1);
		});

		it("behält das letzte Ergebnis bei einem offenen Termin", function () {
			requireWiederholung(this, ctx);

			const student = studentFor(2);

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.notes.nochNichtEingetragen, datum: attemptDate(ctx, 1) }).then(
				(response) => expectNotenSuccess(response, "offener Termin"),
			);

			expectLvNote(student, ctx.notes.negativ, "die LV-Note bleibt beim Ergebnis von Termin 1");
		});

		it("lehnt 'entschuldigt' als übernommene LV-Note ab", () => {
			const student = studentFor(3);

			givenBaseline(ctx, student, { erstantritt: false });
			notenApi
				.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.notes.entschuldigt)
				.then((response) => expectNotenError(response, "c4noteNichtInLehre"));

			expectLvNote(student, ctx.notes.negativ, "die LV-Note bleibt");
		});
	});

	// CIS_GESAMTNOTE_PRUEFUNG_HEBT_FREIGABE_AUF: Der Freigabestatus hängt an benotungsdatum > freigabedatum.
	describe("Freigabe nach einem neuen Termin", () => {
		beforeEach(function () {
			// eine endgültige Freigabe verbietet den neuen Termin
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_FREIGABE_FINAL", false);
			requireWiederholung(this, ctx);
		});

		const neuerTerminNachFreigabe = (student) => {
			givenBaseline(ctx, student, { freigegeben: true });
			addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "Termin nach der Freigabe"),
			);
			return readLvGesamtnote(ctx, student.uid);
		};

		it("hebt die Freigabe mit einem neuen Termin auf", function () {
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_PRUEFUNG_HEBT_FREIGABE_AUF", true);

			neuerTerminNachFreigabe(studentFor(5)).then((row) => {
				expect(new Date(row.benotungsdatum) > new Date(row.freigabedatum), "benotungsdatum nach freigabedatum")
					.to.be.true;
			});
		});

		it("behält die Freigabe bei einem neuen Termin", function () {
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_PRUEFUNG_HEBT_FREIGABE_AUF", false);

			neuerTerminNachFreigabe(studentFor(5)).then((row) => {
				expect(new Date(row.benotungsdatum) > new Date(row.freigabedatum), "benotungsdatum nach freigabedatum")
					.to.be.false;
			});
		});
	});

	it("creates the LV note when a student has none yet", () => {
		// der Bulk-Pfad läuft durch denselben Kern und schreibt die LV-Note vor dem Termin
		const student = studentFor(0);

		resetNotenState(ctx);

		notenApi
			.createPruefungen(
				[{ uid: student.uid, lehreinheit_id: student.lehreinheit_id }],
				attemptDate(ctx, 1),
				ctx.lvId,
				ctx.semKurzbz,
			)
			.then((response) => {
				const data = expectNotenSuccess(response, "createPruefungen without an LV note");
				expect(data[student.uid], `row result for ${student.uid}`).to.be.an("object");
				expect(data[student.uid].savedPruefung, "the Prüfung was written").to.exist;
			})
			.then(() => readLvGesamtnote(ctx, student.uid))
			.then((row) => {
				expect(row, "the LV note was created alongside").to.not.be.null;
				expect(String(row.note), "carries the Prüfung's note").to.eq(
					String(ctx.notes.nochNichtEingetragen),
				);
			});
	});

});
