import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import { requireConfig, requireNotenMode, requireWiederholung, skipIf } from "../../../../support/helpers/notenConfig";
import {
	attemptDate,
	loadNotenContext,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";

/**
 * The two import options.
 *
 * Grade import: “UID<TAB>Grade” per line; writes only the course grade.
 * Exam import: “UID<TAB>dd.MM.yyyy<TAB>Grade” per line; also creates the exam date.
 *
 * Both can be configured via CIS_GESAMTNOTE_NOTENIMPORT / CIS_GESAMTNOTE_PRUEFUNGSIMPORT
 * if the import is disabled, the button does not appear and the block is skipped.
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
		// im Punktemodus erwarten beide Importe Punkte statt einer Note
		requireNotenMode(this, ctx);
	});

	/**
	 * A grade whose abbreviation from tbl_note.anmerkung can be used in the import: unique, not empty,
	 * and not the grade itself. If the configuration does not match the parameter, null is returned and
	 * the test skips this line.
	 */
	const kuerzelNote = () => {
		if (!ctx.cisConfig.CIS_GESAMTNOTE_IMPORT_NOTENKUERZEL) return null;

		const kuerzelOf = (n) =>
			String(n.anmerkung ?? "")
				.trim()
				.toLowerCase();

		return (ctx.notenOptions ?? []).find((n) => {
			const k = kuerzelOf(n);
			if (!n.lehre || k === "" || k === String(n.note).trim()) return false;

			const duplicate = ctx.notenOptions.filter((o) => kuerzelOf(o) === k).length > 1;
			const isNoteValue = ctx.notenOptions.some((o) => String(o.note).trim() === k);

			return !duplicate && !isNoteValue;
		});
	};

	describe("Notenimport", () => {
		beforeEach(function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENIMPORT", true);
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
			requireConfig(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", true);

			const student = ctx.students[0];

			resetNotenState(ctx);
			page.visitAndWaitForTable(ctx);

			page.importNoten([[student.uid, ctx.gradeNotes[0]]]);

			// The course grade IS “Attendance 1”; the import writes it as a separate row
			page.expectAntrittCount(student.uid, 1);
			page.expectPruefung(student.uid, "antritt_1", { note: ctx.gradeNotes[0], antritt: 1 });
		});

		it("nimmt das Kürzel aus der Notenliste, sobald die Option das erlaubt", function () {
			const kuerzel = kuerzelNote();
			skipIf(this, !kuerzel, "Übersprungen: keine Note mit brauchbarem Kürzel in tbl_note.anmerkung.");

			const student = ctx.students[1];

			resetNotenState(ctx);
			page.visitAndWaitForTable(ctx);

			page.importNoten([[student.uid, kuerzel.anmerkung]]);

			page.expectLvNote(student.uid, kuerzel.bezeichnung);
		});
	});

	describe("Prüfungsimport", () => {
		beforeEach(function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNGSIMPORT", true);
		});

		it("legt je Zeile einen datierten Antritt an", function () {
			requireWiederholung(this, ctx);

			const [a, b] = ctx.students;
			// das Format nennt die Konfiguration, nicht der Test
			const datum = page.importDate(attemptDate(ctx, 1), ctx.cisConfig.CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT);

			resetNotenState(ctx);
			seedBaseline(ctx, a, { note: ctx.notes.negativ });
			seedBaseline(ctx, b, { note: ctx.notes.negativ });

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

			page.importPruefungen([
				[
					student.uid,
					page.importDate(attemptDate(ctx, 1), ctx.cisConfig.CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT),
					ctx.gradeNotes[0],
				],
			]);

			page.expectPruefung(student.uid, "antritt_1", { note: ctx.gradeNotes[0], antritt: 1 });
			page.expectLvNote(student.uid, bezeichnung(ctx.gradeNotes[0]));
		});
	});

	// The client checks each line before sending it. It reports any invalid lines as a warning and
	// sends only the remaining ones. It deliberately does not detect duplicate lines.
	describe("fehlerhafte Zeilen", () => {
		it("warnt im Prüfungsimport je fehlerhafter Zeile und schickt nur die gültigen", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNGSIMPORT", true);
			requireWiederholung(this, ctx);

			const [perUid, perMatrikelnr, wrongDate, wrongNote, wrongColumns] = ctx.students;
			const format = ctx.cisConfig.CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT;
			const datum = page.importDate(attemptDate(ctx, 1), format);
			const year = attemptDate(ctx, 1).slice(0, 4);
			const impossible = format === "yyyy-MM-dd" ? `${year}-02-31` : `31.02.${year}`;
			const note = ctx.notes.negativ;

			expect(perMatrikelnr.matrikelnr, "Matrikelnummer des Studierenden").to.be.a("string").and.not.be.empty;

			resetNotenState(ctx);
			seedBaseline(ctx, perUid, { note: ctx.notes.negativ });
			seedBaseline(ctx, perMatrikelnr, { note: ctx.notes.negativ });
			page.visitAndWaitForTable(ctx);

			page.importPruefungen([
				[perUid.uid, datum, note],
				[perMatrikelnr.matrikelnr, datum, note],
				["zz_unbekannt", datum, note],
				[wrongDate.uid, impossible, note],
				[wrongNote.uid, datum, "keine-note"],
				[wrongColumns.uid, datum],
			]);

			page.expectWarning("zz_unbekannt");
			page.expectWarning(wrongDate.uid);
			page.expectWarning(wrongNote.uid);
			// die Zeile mit falscher Spaltenzahl nennt nur ihre Zeilennummer
			page.expectWarnings(4);

			page.sentUids("@savePruefungenBulk", "pruefungen").then((uids) => {
				expect(uids, "nur die gültigen Zeilen, die Matrikelnummer aufgelöst").to.deep.eq([
					perUid.uid,
					perMatrikelnr.uid,
				]);
			});
		});

		it("warnt im Notenimport je fehlerhafter Zeile und schickt nur die gültigen", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENIMPORT", true);

			const [valid, wrongNote, wrongColumns] = ctx.students;

			resetNotenState(ctx);
			page.visitAndWaitForTable(ctx);

			page.importNoten([
				[valid.uid, ctx.gradeNotes[0]],
				["zz_unbekannt", ctx.gradeNotes[0]],
				[wrongNote.uid, "keine-note"],
				[wrongColumns.uid, ctx.gradeNotes[0], "zu viel"],
			]);

			page.expectWarning("zz_unbekannt");
			page.expectWarning(wrongNote.uid);
			page.expectWarnings(3);

			page.sentUids("@saveNotenvorschlagBulk", "noten").then((uids) => {
				expect(uids, "nur die gültige Zeile").to.deep.eq([valid.uid]);
			});
		});
	});
});
