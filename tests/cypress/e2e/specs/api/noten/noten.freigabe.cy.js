/**
 * Notenfreigabe (P1, cases 12-14).
 *
 * Eine erfolgreiche Freigabe verschickt die Freigabemail, solange CIS_GESAMTNOTE_FREIGABEMAIL nicht false
 * ist. Die Entwicklungsinstanzen stellen jede Mail in ein Debug-Postfach zu (MAIL_DEBUG), deshalb laufen
 * die Tests mit der Mail. Die Suite sieht die Mail selbst nicht: sie prüft die Vorlagen und die Antwort.
 */

import { notenApi } from "../../../../support/api/notenApi";
import {
	expectBulkRowAccepted,
	expectBulkRowError,
	expectNotenError,
	expectNotenSuccess,
} from "../../../../support/helpers/notenErrors";
import { requireKonfiguration, requireNotenModus } from "../../../../support/helpers/notenConfig";
import {
	attemptDate,
	baselineBenotungsdatum,
	loadNotenContext,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedBaseline,
	seedPruefung,
} from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	attemptsOfStudent,
	givenBaseline,
	lvNoteOf,
	readStateViaApi,
	verlaufOfStudent,
} from "../../../../support/helpers/notenScenario";

const freigabePassword = () => Cypress.env("NOTEN_FREIGABE_PASSWORD") || Cypress.env("adminpassword");

/** The payload shape saveStudentenNoten expects per student (see the studlist builder). */
const notenPayload = (student, noteBezeichnung) => ({
	uid: student.uid,
	matrikelnr: student.matrikelnr || student.matr_nr || "",
	kuerzel: student.kuerzel || "",
	nachname: student.nachname || "",
	vorname: student.vorname || "",
	noteBezeichnung: noteBezeichnung || "",
});

describe("Noten API - Notenfreigabe", () => {
	let ctx;

	before(() => {
		loadNotenContext().then((context) => {
			ctx = context;
		});
	});

	describe("Passwortschutz", () => {
		it("lehnt eine Freigabe mit falschem Passwort ab und ändert nichts", function () {
			// ohne Passwortpflicht gibt der Aufruf frei
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_FREIGABE_PASSWORT", true);
			requireDbReset();

			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student, {
				note: ctx.gradeNotes[0],
				freigegeben: false,
				benotungsdatum: baselineBenotungsdatum(ctx),
			});

			notenApi
				.saveStudentenNoten("definitely-not-the-password", [notenPayload(student)], ctx.lvId, ctx.semKurzbz)
				.then((response) => {
					expectNotenError(response, "wrongPassword");
				});

			readLvGesamtnoteViaDb(ctx, student.uid).then((row) => {
				expect(row, "the row still exists").to.not.be.null;
				expect(row.freigabedatum, "a rejected Freigabe must not stamp freigabedatum").to.be.null;
			});
		});
	});

	describe("der normale Ablauf", () => {
		beforeEach(() => {
			requireDbReset();
		});

		it("setzt das freigabedatum auf einer geänderten Note", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student, {
				note: ctx.gradeNotes[0],
				freigegeben: false,
				benotungsdatum: baselineBenotungsdatum(ctx),
			});

			notenApi
				.saveStudentenNoten(freigabePassword(), [notenPayload(student)], ctx.lvId, ctx.semKurzbz)
				.then((response) => {
					const data = expectNotenSuccess(response, "saveStudentenNoten");
					expect(data, "freigegebene rows are reported back").to.be.an("array");

					const entry = data.find((r) => r.uid === student.uid);
					expect(entry, `${student.uid} must be reported as freigegeben`).to.exist;
					expect(entry.freigabedatum, "freigabedatum is stamped").to.exist;
					expect(entry.verlauf, "der Verlauf kommt mit der Antwort zurück").to.exist;
					expect(entry.verlauf.pruefungen, "inklusive der Termine").to.be.an("array");
				});

			readStateViaApi(ctx).then((data) => {
				const grades = lvNoteOf(data, student.uid);
				expect(grades.freigabedatum, "the note is now freigegeben").to.exist;
			});

			// C1: Die Freigabe brach früher vor dem Schreiben ab.
			readLvGesamtnoteViaDb(ctx, student.uid).then((row) => {
				expect(row.freigabedatum, "C1: die Freigabe schreibt freigabedatum").to.not.be.null;
			});
		});

		it("legt mit der Freigabe den ersten Antritt an", function () {
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", true);

			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.notes.negativ, freigegeben: false });

			notenApi
				.saveStudentenNoten(freigabePassword(), [notenPayload(student)], ctx.lvId, ctx.semKurzbz)
				.then((response) => {
					const entry = expectNotenSuccess(response, "saveStudentenNoten").find((r) => r.uid === student.uid);
					// ohne den Verlauf in der Antwort zeigt die Tabelle den neuen Termin erst nach einem Reload
					expect(entry.verlauf.pruefungen, "der erste Antritt kommt mit der Antwort zurück").to.have.length(
						1,
					);
				});

			readStateViaApi(ctx).then((data) => {
				const attempts = attemptsOfStudent(data, student.uid);
				expect(attempts, "genau ein Termin").to.have.length(1);
				expect(attempts[0].antritt_nr, "als Antritt 1").to.eq(1);
			});
		});

		it("legt ohne CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME keinen Termin an", function () {
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", false);

			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.notes.negativ, freigegeben: false });

			notenApi
				.saveStudentenNoten(freigabePassword(), [notenPayload(student)], ctx.lvId, ctx.semKurzbz)
				.then((response) => {
					const entry = expectNotenSuccess(response, "saveStudentenNoten").find((r) => r.uid === student.uid);
					expect(entry.freigabedatum, "die Freigabe gilt trotzdem").to.exist;
				});

			readStateViaApi(ctx).then((data) => {
				expect(attemptsOfStudent(data, student.uid), "kein Termin").to.have.length(0);
				expect(verlaufOfStudent(data, student.uid).antrittCount, "die LV-Note zählt als Antritt 1").to.eq(1);
			});
		});

		it("gibt ohne Passwort frei, wenn die Konfiguration keines verlangt", function () {
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_FREIGABE_PASSWORT", false);

			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.notes.negativ, freigegeben: false });

			notenApi.saveStudentenNoten("", [notenPayload(student)], ctx.lvId, ctx.semKurzbz).then((response) => {
				const data = expectNotenSuccess(response, "Freigabe ohne Passwort");
				expect(
					data.map((r) => r.uid),
					"freigegebene Zeilen",
				).to.include(student.uid);
			});

			readLvGesamtnoteViaDb(ctx, student.uid).then((row) => {
				expect(row.freigabedatum, "freigabedatum").to.not.be.null;
			});
		});

		// W7: Eine einzelne Wiederholung ist nicht Antritt 1. Die Freigabe überschreibt ihre Note nicht.
		it("behält die Note einer einzelnen Wiederholung", () => {
			const student = ctx.students[1];
			const g2 = ctx.gradeNotes.find((n) => String(n) !== String(ctx.notes.negativ));

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.notes.negativ, freigegeben: false, erstantritt: false });
			seedPruefung(ctx, student, { note: g2, datum: attemptDate(ctx, 1), typ: "Termin2" });

			notenApi
				.saveStudentenNoten(freigabePassword(), [notenPayload(student)], ctx.lvId, ctx.semKurzbz)
				.then((response) => expectNotenSuccess(response, "saveStudentenNoten"));

			readStateViaApi(ctx).then((data) => {
				const attempts = attemptsOfStudent(data, student.uid);
				expect(attempts, "the Freigabe adds no exam").to.have.length(1);
				expect(String(attempts[0].note), "the repeat keeps its grade").to.eq(String(g2));
			});
		});

		// Das Datum eines Termins gehört der Person, die ihn eingetragen hat. Die Freigabe macht die
		// Note verbindlich und verschiebt kein Datum.
		it("behält das Datum einer bestehenden Prüfung", () => {
			const student = ctx.students[2];
			const pruefungsdatum = attemptDate(ctx, 2);

			resetNotenState(ctx);
			seedBaseline(ctx, student, {
				note: ctx.notes.negativ,
				freigegeben: false,
				erstantritt: false,
				benotungsdatum: baselineBenotungsdatum(ctx),
			});
			// Antritt 1 mit einem eigenen Datum; ohne Termin datiert die Freigabe auf das Benotungsdatum
			seedPruefung(ctx, student, { note: ctx.notes.negativ, datum: pruefungsdatum, typ: "Termin1" });

			notenApi
				.saveStudentenNoten(freigabePassword(), [notenPayload(student)], ctx.lvId, ctx.semKurzbz)
				.then((response) => expectNotenSuccess(response, "saveStudentenNoten"));

			readStateViaApi(ctx).then((data) => {
				const attempts = attemptsOfStudent(data, student.uid);
				expect(attempts, "der Termin bleibt der einzige").to.have.length(1);
				expect(String(attempts[0].datum).slice(0, 10), "mit seinem eigenen Datum").to.eq(pruefungsdatum);
			});
		});

		it("ändert nur Zeilen mit einem benotungsdatum nach dem freigabedatum", () => {
			const changed = ctx.students[0];
			const alreadyReleased = ctx.students[1];

			resetNotenState(ctx);

			// changed: benotungsdatum after freigabedatum -> must be re-freigegeben
			seedBaseline(ctx, changed, {
				note: ctx.gradeNotes[0],
				freigegeben: true,
				freigabedatum: `${ctx.semKurzbz.slice(2, 6)}-01-05 08:00:00`,
				benotungsdatum: `${ctx.semKurzbz.slice(2, 6)}-01-10 08:00:00`,
			});

			// already freigegeben and untouched since -> must be left alone
			seedBaseline(ctx, alreadyReleased, {
				note: ctx.gradeNotes[0],
				freigegeben: true,
				freigabedatum: `${ctx.semKurzbz.slice(2, 6)}-01-10 08:00:00`,
				benotungsdatum: `${ctx.semKurzbz.slice(2, 6)}-01-10 08:00:00`,
			});

			notenApi
				.saveStudentenNoten(
					freigabePassword(),
					[notenPayload(changed), notenPayload(alreadyReleased)],
					ctx.lvId,
					ctx.semKurzbz,
				)
				.then((response) => {
					const data = expectNotenSuccess(response, "selective Freigabe");
					const uids = data.map((r) => r.uid);

					expect(uids, "the changed note is freigegeben").to.include(changed.uid);
					expect(
						uids,
						"an unchanged, already freigegebene note must not be freigegeben again",
					).to.not.include(alreadyReleased.uid);
				});

			readLvGesamtnoteViaDb(ctx, alreadyReleased.uid).then((row) => {
				expect(
					new Date(row.freigabedatum).toISOString().slice(0, 10),
					"the untouched row keeps its original freigabedatum",
				).to.eq(`${ctx.semKurzbz.slice(2, 6)}-01-10`);
			});
		});
	});

	// Die Mail geht an ein Debug-Postfach, die Suite liest sie nicht. Sie prüft deshalb, was die Mail braucht:
	// beide Vorlagen mit Text und eine Freigabe, die mit eingeschalteter Mail vollständig antwortet.
	describe("Freigabemail", () => {
		beforeEach(function () {
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_FREIGABEMAIL", true);
			requireDbReset();
		});

		it("findet die Vorlage der Freigabemail und das Sancho-Layout", () => {
			const vorlage = ctx.cisConfig.CIS_GESAMTNOTE_FREIGABEMAIL_VORLAGE;

			cy.task("noten:db:vorlagen", { kurzbz: [vorlage, "Sancho_Mail_Template"] }).then((rows) => {
				const text = (kurzbz) => (rows.find((r) => r.vorlage_kurzbz === kurzbz) || {}).text || "";

				// ohne Text ist der Mailinhalt leer, und niemand merkt es
				expect(text(vorlage), `Text der Vorlage ${vorlage}`).to.not.be.empty;
				expect(text(vorlage), "die Vorlage setzt die Notenliste ein").to.include("{studlist}");
				expect(text("Sancho_Mail_Template"), "das Layout setzt den Inhalt ein").to.include("{content}");
			});
		});

		it("gibt mit eingeschalteter Mail frei", () => {
			const student = ctx.students[3];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.notes.negativ, freigegeben: false });

			notenApi
				.saveStudentenNoten(freigabePassword(), [notenPayload(student)], ctx.lvId, ctx.semKurzbz)
				.then((response) => {
					const entry = expectNotenSuccess(response, "Freigabe mit Mail").find((r) => r.uid === student.uid);
					expect(entry, `${student.uid} ist freigegeben`).to.exist;
				});

			readLvGesamtnoteViaDb(ctx, student.uid).then((row) => {
				expect(row.freigabedatum, "freigabedatum").to.not.be.null;
			});
		});
	});

	// CIS_GESAMTNOTE_FREIGABE_FINAL: Kein Pfad ändert eine freigegebene Note. Die Tests säen die
	// Freigabe, deshalb brauchen sie kein Passwort.
	describe("endgültige Freigabe", () => {
		beforeEach(function () {
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_FREIGABE_FINAL", true);
			requireDbReset();
		});

		const andereNote = () => ctx.gradeNotes.find((n) => String(n) !== String(ctx.notes.negativ));

		it("lehnt eine Übernahme nach der Freigabe ab", () => {
			const student = ctx.students[0];

			// ausdrücklich freigegeben: unter FINAL sät die Baseline sonst eine offene Note
			givenBaseline(ctx, student, { freigegeben: true });

			notenApi
				.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, andereNote())
				.then((response) => expectNotenError(response, "freigabeEndgueltig"));

			readLvGesamtnoteViaDb(ctx, student.uid).then((row) => {
				expect(String(row.note), "die LV-Note bleibt").to.eq(String(ctx.notes.negativ));
			});
		});

		it("lehnt einen neuen Termin nach der Freigabe ab", () => {
			const student = ctx.students[1];

			givenBaseline(ctx, student, { freigegeben: true });

			addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenError(response, "freigabeEndgueltig"),
			);

			readStateViaApi(ctx).then((data) => {
				expect(attemptsOfStudent(data, student.uid), "nur Antritt 1").to.have.length(1);
			});
		});

		it("lehnt im Import nur die freigegebene Zeile ab", function () {
			// im Punktemodus leitet der Import die Note aus den Punkten ab
			requireNotenModus(this, ctx);

			const [freigegeben, offen] = ctx.students;

			resetNotenState(ctx);
			seedBaseline(ctx, freigegeben, { freigegeben: true });
			seedBaseline(ctx, offen, { freigegeben: false });

			notenApi
				.saveNotenvorschlagBulk(ctx.lvId, ctx.semKurzbz, [
					{ uid: freigegeben.uid, note: andereNote(), punkte: null },
					{ uid: offen.uid, note: andereNote(), punkte: null },
				])
				.then((response) => {
					const data = expectNotenSuccess(response, "saveNotenvorschlagBulk");
					expectBulkRowError(data, freigegeben.uid, "freigabeEndgueltig");
					expectBulkRowAccepted(data, offen.uid);
				});
		});
	});
});
