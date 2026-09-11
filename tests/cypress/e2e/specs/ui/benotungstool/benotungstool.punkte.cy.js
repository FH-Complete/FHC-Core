import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import { requirePunkteModus } from "../../../../support/helpers/notenConfig";
import {
	attemptDate,
	loadNotenContext,
	readLvGesamtnote,
	requireDbReset,
	resetNotenState,
	seedBaseline,
	seedPruefung,
} from "../../../../support/helpers/notenTestData";
import { notenApi } from "../../../../support/api/notenApi";

/**
 * Punktemodus (CIS_GESAMTNOTE_PUNKTE) in der Oberfläche.
 *
 * Die Punktespalte und die Punktefelder der beiden Dialoge existieren nur mit diesem Flag, die Note
 * wird dann aus dem Notenschlüssel abgeleitet statt gewählt. Genau diese Felder deckt sonst nichts
 * ab, deshalb steht der ganze Modus hier in einer eigenen Datei.
 *
 * Der Notenschlüssel wird nie hartcodiert: die erwartete Note kommt zur Laufzeit aus
 * getNoteByPunkte, damit die Specs an jeder Instanz mit eigenem Schlüssel laufen.
 */
context("Benotungstool UI - Punktemodus", () => {
	let ctx;
	// Punktewerte, deren Noten zur Laufzeit ermittelt werden
	const OBEN = 100;
	const MITTE = 70;
	let noteOben;
	let noteMitte;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;
			if (!ctx.cisConfig.CIS_GESAMTNOTE_PUNKTE) return;

			notenApi.getNoteByPunkte(OBEN, ctx.lvId, ctx.semKurzbz).then((r) => {
				noteOben = r.body.data;
				expect(noteOben, `${OBEN} Punkte müssen eine Note ergeben`).to.not.be.null;
			});
			notenApi.getNoteByPunkte(MITTE, ctx.lvId, ctx.semKurzbz).then((r) => {
				noteMitte = r.body.data;
				expect(noteMitte, `${MITTE} Punkte müssen eine Note ergeben`).to.not.be.null;
				expect(String(noteMitte), "die beiden Punktewerte müssen verschiedene Noten liefern").to.not.eq(
					String(noteOben),
				);
			});
		});
	});

	beforeEach(function () {
		requirePunkteModus(this, ctx);
	});

	describe("Punktespalte in der Tabelle", () => {
		it("zeigt die Punktespalte und sperrt dafür die Notenauswahl", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			page.visitAndWaitForTable(ctx);

			page.getPunkteCell(student.uid).should("exist");
			// die Note kommt aus den Punkten, sie darf nicht direkt gewählt werden
			page.expectNotenvorschlagGesperrt(student.uid);
		});

		it("schreibt Note und Punkte, wenn der Vorschlag übernommen wird", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			page.visitAndWaitForTable(ctx);

			page.setPunkteInCell(student.uid, MITTE);
			page.uebernehmen(student.uid);

			page.expectLvNote(student.uid, page.bezeichnungOf(ctx, noteMitte));
			page.expectFreigabeState(student.uid, "changed");

			readLvGesamtnote(ctx, student.uid).then((row) => {
				expect(String(row.note), "abgeleitete Note").to.eq(String(noteMitte));
				expect(Number(row.punkte), "die Punkte werden mitgeschrieben").to.eq(MITTE);
			});
		});

		// Antritt 1 und die LV-Note sind dieselbe Leistung, daher bleibt die Spalte dafür offen.
		// Erst die Wiederholung nimmt der LV-Note die Hoheit über Note und Punkte.
		it("sperrt die Punktespalte ab der ersten Wiederholung", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student.uid, { note: ctx.gradeNotes[0], freigegeben: true });
			seedPruefung(ctx, student, {
				note: ctx.gradeNotes[0],
				datum: attemptDate(ctx, 1),
				typ: "Termin2",
			});

			page.visitAndWaitForTable(ctx);

			page.expectPunkteZelleGesperrt(student.uid);
		});
	});

	describe("Prüfungsdialog", () => {
		it("bietet ein Punktefeld statt der Notenauswahl", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student.uid, { note: ctx.gradeNotes[0], freigegeben: true });

			page.visitAndWaitForTable(ctx);
			page.getPruefungAddButton(student.uid, "antritt_2").click();
			page.getPruefungModal().should("be.visible");

			cy.get("[data-cy='pruefung-punkte']").should("be.visible");
			page.expectNoteFeldGesperrt("pruefung-note");
		});

		it("legt den Termin mit der aus den Punkten abgeleiteten Note an", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student.uid, { note: ctx.gradeNotes[0], freigegeben: true });

			page.visitAndWaitForTable(ctx);
			page.getPruefungAddButton(student.uid, "antritt_2").click();
			page.getPruefungModal().should("be.visible");

			page.setDatum("pruefung-datum", page.toDDMMYYYY(attemptDate(ctx, 1)));
			page.setPruefungPunkte(OBEN);

			cy.get("[data-cy='pruefung-submit']").click();
			cy.wait("@saveStudentPruefung").its("response.statusCode").should("eq", 200);
			page.getPruefungModal().should("not.be.visible");

			page.expectPruefung(student.uid, "antritt_2", { note: noteOben, antritt: 2 });
			page.expectLvNote(student.uid, page.bezeichnungOf(ctx, noteOben));
		});
	});

	describe("Sammelanlage", () => {
	});
});
