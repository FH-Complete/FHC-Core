import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
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
			cy.log("Skipped: CIS_GESAMTNOTE_PUNKTE ist aktiv.");
			this.skip();
		}
	});

	describe("Notenimport", () => {
		beforeEach(function () {
			if (!ctx.cisConfig.CIS_GESAMTNOTE_NOTENIMPORT) {
				cy.log("Skipped: CIS_GESAMTNOTE_NOTENIMPORT ist aus, der Button existiert nicht.");
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

		it("legt dabei keine Prüfung an", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			page.visitAndWaitForTable(ctx);

			page.importNoten([[student.uid, ctx.gradeNotes[0]]]);

			// die LV-Note zählt als impliziter erster Antritt, ohne dass eine Zeile entsteht
			page.expectAntrittCount(student.uid, 1);
			page.expectKeinePruefung(student.uid, "antritt_1");
		});
	});

	describe("Prüfungsimport", () => {
		beforeEach(function () {
			if (!ctx.cisConfig.CIS_GESAMTNOTE_PRUEFUNGSIMPORT) {
				cy.log("Skipped: CIS_GESAMTNOTE_PRUEFUNGSIMPORT ist aus, der Button existiert nicht.");
				this.skip();
			}
		});

		it("legt je Zeile einen datierten Antritt an", () => {
			const [a, b] = ctx.students;
			const datum = page.toDDMMYYYY(attemptDate(ctx, 1));

			resetNotenState(ctx);
			seedBaseline(ctx, a.uid, { note: ctx.gradeNotes[0], freigegeben: true });
			seedBaseline(ctx, b.uid, { note: ctx.gradeNotes[0], freigegeben: true });

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

			page.importPruefungen([[student.uid, page.toDDMMYYYY(attemptDate(ctx, 1)), ctx.gradeNotes[0]]]);

			page.expectPruefung(student.uid, "antritt_1", { note: ctx.gradeNotes[0], antritt: 1 });
			page.expectLvNote(student.uid, bezeichnung(ctx.gradeNotes[0]));
		});
	});
});
