import { loginAsLektor } from "../../../../support/api/notenApi";
import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import { requireConfig, requireNotenMode, requireRepeat, skipIf } from "../../../../support/helpers/notenConfig";
import {
	antrittDate,
	loadNotenContext,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";

/**
 * The two imports.
 *
 * Noten import: "uid<TAB>Note" per line; writes only the LV-Note.
 * Pruefungen import: "uid<TAB>dd.MM.yyyy<TAB>Note" per line; also creates the Pruefung.
 *
 * CIS_GESAMTNOTE_NOTENIMPORT / CIS_GESAMTNOTE_PRUEFUNGSIMPORT turn them on. Without the flag the
 * button is missing, and the block skips.
 */
describe("Benotungstool UI - Import", () => {
	let ctx;
	let bezeichnung;

	before(() => {
		requireDbReset();
		loadNotenContext().then((loaded) => {
			ctx = loaded;
			bezeichnung = (note) => page.bezeichnungOf(ctx, note);
		});
	});

	beforeEach(() => loginAsLektor());

	beforeEach(function () {
		// in the Punkte mode both imports expect Punkte instead of a Note
		requireNotenMode(this, ctx);
	});

	/**
	 * A Note whose Kuerzel from tbl_note.anmerkung works in the import: unique, not empty, and not the
	 * Note itself. If the configuration does not fit, it returns null, and the test skips.
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

			page.importLvNoten([
				[a.uid, ctx.notenScale[0]],
				[b.uid, ctx.notenScale[1]],
			]);

			page.expectLvNote(a.uid, bezeichnung(ctx.notenScale[0]));
			page.expectLvNote(b.uid, bezeichnung(ctx.notenScale[1]));

			// entered, but not freigegeben
			page.expectFreigabeState(a.uid, "changed");
			page.expectFreigabeState(b.uid, "changed");
		});

		it("legt dabei den ersten Antritt an", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", true);

			const student = ctx.students[0];

			resetNotenState(ctx);
			page.visitAndWaitForTable(ctx);

			page.importLvNoten([[student.uid, ctx.notenScale[0]]]);

			// the LV-Note IS Antritt 1; the import writes it as its own row
			page.expectAntrittCount(student.uid, 1);
			page.expectPruefung(student.uid, "antritt_1", { note: ctx.notenScale[0], antritt: 1 });
		});

		it("nimmt das Kürzel aus der Notenliste, sobald die Option das erlaubt", function () {
			const kuerzel = kuerzelNote();
			skipIf(this, !kuerzel, "Übersprungen: keine Note mit brauchbarem Kürzel in tbl_note.anmerkung.");

			const student = ctx.students[1];

			resetNotenState(ctx);
			page.visitAndWaitForTable(ctx);

			page.importLvNoten([[student.uid, kuerzel.anmerkung]]);

			page.expectLvNote(student.uid, kuerzel.bezeichnung);
		});
	});

	describe("Prüfungsimport", () => {
		beforeEach(function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNGSIMPORT", true);
		});

		it("legt je Zeile einen datierten Antritt an", function () {
			requireRepeat(this, ctx);

			const [a, b] = ctx.students;
			// the configuration sets the format, not the test
			const datum = page.importDate(antrittDate(ctx, 1), ctx.cisConfig.CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT);

			resetNotenState(ctx);
			seedBaseline(ctx, a, { note: ctx.noten.negativ });
			seedBaseline(ctx, b, { note: ctx.noten.negativ });

			page.visitAndWaitForTable(ctx);

			page.importPruefungen([
				[a.uid, datum, ctx.notenScale[1]],
				[b.uid, datum, ctx.notenScale[1]],
			]);

			[a, b].forEach((student) => {
				page.expectPruefung(student.uid, "antritt_2", { note: ctx.notenScale[1], antritt: 2 });
				page.expectAntrittCount(student.uid, 2);
				page.expectLvNote(student.uid, bezeichnung(ctx.notenScale[1]));
			});
		});

		it("legt für einen Studenten ohne LV-Note beides an", () => {
			const student = ctx.students[2];

			resetNotenState(ctx);
			page.visitAndWaitForTable(ctx);

			page.importPruefungen([
				[
					student.uid,
					page.importDate(antrittDate(ctx, 1), ctx.cisConfig.CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT),
					ctx.notenScale[0],
				],
			]);

			page.expectPruefung(student.uid, "antritt_1", { note: ctx.notenScale[0], antritt: 1 });
			page.expectLvNote(student.uid, bezeichnung(ctx.notenScale[0]));
		});
	});

	// The client checks each line before it sends. It shows a warning for each invalid line and sends
	// only the other lines. It does not look for duplicate lines, on purpose.
	describe("fehlerhafte Zeilen", () => {
		it("warnt im Prüfungsimport je fehlerhafter Zeile und schickt nur die gültigen", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNGSIMPORT", true);
			requireRepeat(this, ctx);

			const [perUid, perMatrikelnr, wrongDate, wrongNote, wrongColumns] = ctx.students;
			const format = ctx.cisConfig.CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT;
			const datum = page.importDate(antrittDate(ctx, 1), format);
			const year = antrittDate(ctx, 1).slice(0, 4);
			const impossible = format === "yyyy-MM-dd" ? `${year}-02-31` : `31.02.${year}`;
			const note = ctx.noten.negativ;

			expect(perMatrikelnr.matrikelnr, "Matrikelnummer des Studierenden").to.be.a("string").and.not.be.empty;

			resetNotenState(ctx);
			seedBaseline(ctx, perUid, { note: ctx.noten.negativ });
			seedBaseline(ctx, perMatrikelnr, { note: ctx.noten.negativ });
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
			// a line with the wrong number of columns names only its line number
			page.expectWarnings(4);

			page.sentUids("@importPruefungen", "pruefungen").then((uids) => {
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

			page.importLvNoten([
				[valid.uid, ctx.notenScale[0]],
				["zz_unbekannt", ctx.notenScale[0]],
				[wrongNote.uid, "keine-note"],
				[wrongColumns.uid, ctx.notenScale[0], "zu viel"],
			]);

			page.expectWarning("zz_unbekannt");
			page.expectWarning(wrongNote.uid);
			page.expectWarnings(3);

			page.sentUids("@importLvNoten", "lv_noten").then((uids) => {
				expect(uids, "nur die gültige Zeile").to.deep.eq([valid.uid]);
			});
		});
	});
});
