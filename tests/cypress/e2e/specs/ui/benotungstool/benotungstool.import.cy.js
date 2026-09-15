import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import { requireKonfiguration, requireWiederholung } from "../../../../support/helpers/notenConfig";
import {
	attemptDate,
	loadNotenContext,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";

/**
 * Die beiden Importe aus der Toolbar.
 *
 * Notenimport: "UID<TAB>Note" je Zeile, schreibt nur die LV-Note.
 * Prüfungsimport: "UID<TAB>dd.MM.yyyy<TAB>Note" je Zeile, legt zusätzlich den Termin an.
 *
 * Beide sind über CIS_GESAMTNOTE_NOTENIMPORT / CIS_GESAMTNOTE_PRUEFUNGSIMPORT konfigurierbar - ist
 * der Import aus, existiert der Button nicht und der Block wird übersprungen.
 */
context("Benotungstool UI - Import", () => {
	let ctx;
	let bezeichnung;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;
			bezeichnung = (note) => page.bezeichnungOf(ctx, note);
		});
	});

	beforeEach(function () {
		if (ctx.cisConfig.CIS_GESAMTNOTE_PUNKTE) {
			// im Punktemodus erwarten beide Importe Punkte statt einer Note
			Cypress.log({ name: "skip", message: "Skipped: CIS_GESAMTNOTE_PUNKTE ist aktiv." });
			this.skip();
		}
	});

	/**
	 * Eine Note, deren Kürzel aus tbl_note.anmerkung im Import brauchbar ist: eindeutig, nicht leer
	 * und nicht die Note selbst. Passt die Konfiguration nicht zum Parameter, kommt null zurück und
	 * der Test überspringt sich. Nichts davon steht fest im Test, die Spalte ist Freitext.
	 */
	const kuerzelNote = () => {
		if (!ctx.cisConfig.CIS_GESAMTNOTE_IMPORT_NOTENKUERZEL) return null;

		const kuerzelVon = (n) => String(n.anmerkung ?? "").trim().toLowerCase();

		return (ctx.notenOptions ?? []).find((n) => {
			const k = kuerzelVon(n);
			if (!n.lehre || k === "" || k === String(n.note).trim()) return false;

			const doppelt = ctx.notenOptions.filter((o) => kuerzelVon(o) === k).length > 1;
			const alsNote = ctx.notenOptions.some((o) => String(o.note).trim() === k);

			return !doppelt && !alsNote;
		});
	};

	describe("Notenimport", () => {
		beforeEach(function () {
			if (!ctx.cisConfig.CIS_GESAMTNOTE_NOTENIMPORT) {
				Cypress.log({ name: "skip", message: "Skipped: CIS_GESAMTNOTE_NOTENIMPORT ist aus, der Button existiert nicht." });
				this.skip();
			}
		});

		it("schreibt die LV-Note für jede Zeile", () => {
			const [a, b] = ctx.students;

			resetNotenState(ctx);
			page.visitAndWaitForTable(ctx);

			page.importNoten([
				[a.uid, ctx.gradeNotes[0]],
				[b.uid, ctx.gradeNotes[1]],
			]);

			page.expectLvNote(a.uid, bezeichnung(ctx.gradeNotes[0]));
			page.expectLvNote(b.uid, bezeichnung(ctx.gradeNotes[1]));

			// erfasst, aber nicht freigegeben
			page.expectFreigabeState(a.uid, "changed");
			page.expectFreigabeState(b.uid, "changed");
		});

		it("legt dabei den ersten Antritt an", function () {
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", true);

			const student = ctx.students[0];

			resetNotenState(ctx);
			page.visitAndWaitForTable(ctx);

			page.importNoten([[student.uid, ctx.gradeNotes[0]]]);

			// die LV-Note IST Antritt 1, der Import schreibt ihn als eigene Zeile
			page.expectAntrittCount(student.uid, 1);
			page.expectPruefung(student.uid, "antritt_1", { note: ctx.gradeNotes[0], antritt: 1 });
		});

		it("nimmt das Kürzel aus der Notenliste, sobald die Option das erlaubt", function () {
			const kuerzel = kuerzelNote();
			if (!kuerzel) {
				Cypress.log({ name: "skip", message: "Skipped: keine Note mit brauchbarem Kürzel in tbl_note.anmerkung." });
				this.skip();
			}

			const student = ctx.students[1];

			resetNotenState(ctx);
			page.visitAndWaitForTable(ctx);

			page.importNoten([[student.uid, kuerzel.anmerkung]]);

			page.expectLvNote(student.uid, kuerzel.bezeichnung);
		});
	});

	describe("Prüfungsimport", () => {
		beforeEach(function () {
			if (!ctx.cisConfig.CIS_GESAMTNOTE_PRUEFUNGSIMPORT) {
				Cypress.log({ name: "skip", message: "Skipped: CIS_GESAMTNOTE_PRUEFUNGSIMPORT ist aus, der Button existiert nicht." });
				this.skip();
			}
		});

		it("legt je Zeile einen datierten Antritt an", function () {
			requireWiederholung(this, ctx);

			const [a, b] = ctx.students;
			// das Format nennt die Konfiguration, nicht der Test
			const datum = page.importDatum(attemptDate(ctx, 1), ctx.cisConfig.CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT);

			resetNotenState(ctx);
			seedBaseline(ctx, a.uid, { note: ctx.notes.negativ });
			seedBaseline(ctx, b.uid, { note: ctx.notes.negativ });

			page.visitAndWaitForTable(ctx);

			page.importPruefungen([
				[a.uid, datum, ctx.gradeNotes[1]],
				[b.uid, datum, ctx.gradeNotes[1]],
			]);

			[a, b].forEach((student) => {
				page.expectPruefung(student.uid, "antritt_2", { note: ctx.gradeNotes[1], antritt: 2 });
				page.expectAntrittCount(student.uid, 2);
				page.expectLvNote(student.uid, bezeichnung(ctx.gradeNotes[1]));
			});
		});

		it("legt für einen Studenten ohne LV-Note beides an", () => {
			const student = ctx.students[2];

			resetNotenState(ctx);
			page.visitAndWaitForTable(ctx);

			page.importPruefungen([[
				student.uid,
				page.importDatum(attemptDate(ctx, 1), ctx.cisConfig.CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT),
				ctx.gradeNotes[0],
			]]);

			page.expectPruefung(student.uid, "antritt_1", { note: ctx.gradeNotes[0], antritt: 1 });
			page.expectLvNote(student.uid, bezeichnung(ctx.gradeNotes[0]));
		});
	});

	// Der Client prüft jede Zeile, bevor er sie schickt. Eine fehlerhafte Zeile meldet er als Warnung und
	// schickt nur die übrigen. Doppelte Zeilen erkennt er bewusst nicht.
	describe("fehlerhafte Zeilen", () => {
		it("warnt im Prüfungsimport je fehlerhafter Zeile und schickt nur die gültigen", function () {
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_PRUEFUNGSIMPORT", true);
			requireWiederholung(this, ctx);

			const [perUid, perMatrikelnr, falschesDatum, falscheNote, falscheSpalten] = ctx.students;
			const format = ctx.cisConfig.CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT;
			const datum = page.importDatum(attemptDate(ctx, 1), format);
			const jahr = attemptDate(ctx, 1).slice(0, 4);
			const unmoeglich = format === "yyyy-MM-dd" ? `${jahr}-02-31` : `31.02.${jahr}`;
			const note = ctx.notes.negativ;

			expect(perMatrikelnr.matrikelnr, "Matrikelnummer des Studierenden").to.be.a("string").and.not.be.empty;

			resetNotenState(ctx);
			seedBaseline(ctx, perUid.uid, { note: ctx.notes.negativ });
			seedBaseline(ctx, perMatrikelnr.uid, { note: ctx.notes.negativ });
			page.visitAndWaitForTable(ctx);

			page.importPruefungen([
				[perUid.uid, datum, note],
				[perMatrikelnr.matrikelnr, datum, note],
				["zz_unbekannt", datum, note],
				[falschesDatum.uid, unmoeglich, note],
				[falscheNote.uid, datum, "keine-note"],
				[falscheSpalten.uid, datum],
			]);

			page.expectWarnung("zz_unbekannt");
			page.expectWarnung(falschesDatum.uid);
			page.expectWarnung(falscheNote.uid);
			// die Zeile mit falscher Spaltenzahl nennt nur ihre Zeilennummer
			page.expectWarnungen(4);

			page.gesendeteUids("@savePruefungenBulk", "pruefungen").then((uids) => {
				expect(uids, "nur die gültigen Zeilen, die Matrikelnummer aufgelöst").to.deep.eq([perUid.uid, perMatrikelnr.uid]);
			});
		});

		it("warnt im Notenimport je fehlerhafter Zeile und schickt nur die gültigen", function () {
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_NOTENIMPORT", true);

			const [gueltig, falscheNote, falscheSpalten] = ctx.students;

			resetNotenState(ctx);
			page.visitAndWaitForTable(ctx);

			page.importNoten([
				[gueltig.uid, ctx.gradeNotes[0]],
				["zz_unbekannt", ctx.gradeNotes[0]],
				[falscheNote.uid, "keine-note"],
				[falscheSpalten.uid, ctx.gradeNotes[0], "zu viel"],
			]);

			page.expectWarnung("zz_unbekannt");
			page.expectWarnung(falscheNote.uid);
			page.expectWarnungen(3);

			page.gesendeteUids("@saveNotenvorschlagBulk", "noten").then((uids) => {
				expect(uids, "nur die gültige Zeile").to.deep.eq([gueltig.uid]);
			});
		});
	});
});
