import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import { requireKonfiguration, requirePunkteModus, requireWiederholung } from "../../../../support/helpers/notenConfig";
import { attemptsOfStudent, readStateViaApi } from "../../../../support/helpers/notenScenario";
import {
	attemptDate,
	loadNotenContext,
	readLvGesamtnoteViaDb,
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

			readLvGesamtnoteViaDb(ctx, student.uid).then((row) => {
				expect(String(row.note), "abgeleitete Note").to.eq(String(noteMitte));
				expect(Number(row.punkte), "die Punkte werden mitgeschrieben").to.eq(MITTE);
			});
		});

		// Antritt 1 und die LV-Note sind dieselbe Leistung, daher bleibt die Spalte dafür offen.
		// Erst die Wiederholung nimmt der LV-Note die Hoheit über Note und Punkte.
		it("sperrt die Punktespalte ab der ersten Wiederholung", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.gradeNotes[0], freigegeben: true });
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
		// Beide Tests brauchen einen freien Antritt: eine positive Basisnote schliesst die Kette.
		it("bietet ein Punktefeld statt der Notenauswahl", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.notes.negativ, freigegeben: true });

			page.visitAndWaitForTable(ctx);
			page.getPruefungAddButton(student.uid, "antritt_2").click();
			page.getPruefungModal().should("be.visible");

			cy.get("[data-cy='pruefung-punkte']").should("be.visible");
			page.expectNoteFeldGesperrt("pruefung-note");
		});

		it("legt den Termin mit der aus den Punkten abgeleiteten Note an", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.notes.negativ, freigegeben: true });

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
		// die Sammelanlage schickt nur Punkte; erst der Server leitet die Note ab
		it("legt den Termin mit der aus den Punkten abgeleiteten Note an", function () {
			requireWiederholung(this, ctx);

			const [a, b] = [ctx.students[2], ctx.students[3]];

			resetNotenState(ctx);
			seedBaseline(ctx, a, { note: ctx.notes.negativ, freigegeben: true });
			seedBaseline(ctx, b, { note: ctx.notes.negativ, freigegeben: true });

			page.visitAndWaitForTable(ctx);
			page.addPruefungBulk({ uids: [a.uid, b.uid], punkte: MITTE, datum: page.toDDMMYYYY(attemptDate(ctx, 1)) });

			[a, b].forEach((student) => page.expectPruefung(student.uid, "antritt_2", { note: noteMitte, antritt: 2 }));
		});
	});

	// Die Excel-Liste schreibt Dezimalstellen mit Komma oder mit Punkt. Beide Schreibweisen ergeben dieselbe Zahl.
	describe("Import mit Punkten", () => {
		const PUNKTE = 89.5;

		[
			["Dezimalkomma", "89,5"],
			["Dezimalpunkt", "89.5"],
		].forEach(([schreibweise, eingabe]) => {
			it(`liest Punkte mit ${schreibweise} im Prüfungsimport`, function () {
				requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_PRUEFUNGSIMPORT", true);
				requireWiederholung(this, ctx);

				const student = ctx.students[4];
				const datum = page.importDatum(attemptDate(ctx, 1), ctx.cisConfig.CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT);

				resetNotenState(ctx);
				seedBaseline(ctx, student, { note: ctx.notes.negativ, freigegeben: true });
				page.visitAndWaitForTable(ctx);

				page.importPruefungen([[student.uid, datum, eingabe]]);

				cy.get("@savePruefungenBulk").its("request.body.pruefungen.0.punkte").should("eq", PUNKTE);

				readStateViaApi(ctx).then((data) => {
					const neu = attemptsOfStudent(data, student.uid)[1];
					expect(neu, "der importierte Termin").to.exist;
					expect(Number(neu.punkte), "die Punkte des Termins").to.eq(PUNKTE);
				});
			});

			it(`liest Punkte mit ${schreibweise} im Notenimport`, function () {
				requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_NOTENIMPORT", true);

				const student = ctx.students[5];

				resetNotenState(ctx);
				page.visitAndWaitForTable(ctx);

				page.importNoten([[student.uid, eingabe]]);

				cy.get("@saveNotenvorschlagBulk").its("request.body.noten.0.punkte").should("eq", PUNKTE);

				readLvGesamtnoteViaDb(ctx, student.uid).then((row) => {
					expect(row, "die importierte LV-Note").to.not.be.null;
					expect(Number(row.punkte), "die Punkte der LV-Note").to.eq(PUNKTE);
				});
			});
		});
	});
});
