import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import { requireConfig, requirePunkteMode, requireWiederholung } from "../../../../support/helpers/notenConfig";
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
 * Point Mode (CIS_GESAMTNOTE_PUNKTE) in the user interface.
 *
 * The points column and the points fields in both dialogs exist only when this flag is set; the grade
 * is then derived from the grading key instead of being selected. Nothing else covers these specific fields,
 * which is why this entire mode is contained in its own file here.
 *
 * The grading key is never hard-coded: the expected grade is retrieved at runtime from
 * getNoteByPunkte, so that the specs run with their own key for each instance.
 */
context("Benotungstool UI - Punktemodus", () => {
	let ctx;
	// Punktewerte, deren Noten zur Laufzeit ermittelt werden
	const TOP = 100;
	const MIDDLE = 70;
	let noteTop;
	let noteMiddle;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;
			if (!ctx.cisConfig.CIS_GESAMTNOTE_PUNKTE) return;

			notenApi.getNoteByPunkte(TOP, ctx.lvId, ctx.semKurzbz).then((r) => {
				noteTop = r.body.data;
				expect(noteTop, `${TOP} Punkte müssen eine Note ergeben`).to.not.be.null;
			});
			notenApi.getNoteByPunkte(MIDDLE, ctx.lvId, ctx.semKurzbz).then((r) => {
				noteMiddle = r.body.data;
				expect(noteMiddle, `${MIDDLE} Punkte müssen eine Note ergeben`).to.not.be.null;
				expect(String(noteMiddle), "die beiden Punktewerte müssen verschiedene Noten liefern").to.not.eq(
					String(noteTop),
				);
			});
		});
	});

	beforeEach(function () {
		requirePunkteMode(this, ctx);
	});

	describe("Punktespalte in der Tabelle", () => {
		it("zeigt die Punktespalte und sperrt dafür die Notenauswahl", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			page.visitAndWaitForTable(ctx);

			page.getPunkteCell(student.uid).should("exist");
			// die Note kommt aus den Punkten, sie darf nicht direkt gewählt werden
			page.expectNotenvorschlagLocked(student.uid);
		});

		it("schreibt Note und Punkte, wenn der Vorschlag übernommen wird", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			page.visitAndWaitForTable(ctx);

			page.setPunkteInCell(student.uid, MIDDLE);
			page.uebernehmen(student.uid);

			page.expectLvNote(student.uid, page.bezeichnungOf(ctx, noteMiddle));
			page.expectFreigabeState(student.uid, "changed");

			readLvGesamtnoteViaDb(ctx, student.uid).then((row) => {
				expect(String(row.note), "abgeleitete Note").to.eq(String(noteMiddle));
				expect(Number(row.punkte), "die Punkte werden mitgeschrieben").to.eq(MIDDLE);
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
				type: "Termin2",
			});

			page.visitAndWaitForTable(ctx);

			page.expectPunkteCellLocked(student.uid);
		});
	});

	describe("Prüfungsdialog", () => {
		it("bietet ein Punktefeld statt der Notenauswahl", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.notes.negativ, freigegeben: true });

			page.visitAndWaitForTable(ctx);
			page.getPruefungAddButton(student.uid, "antritt_2").click();
			page.getPruefungModal().should("be.visible");

			cy.get("[data-cy='pruefung-punkte']").should("be.visible");
			page.expectNoteFieldLocked("pruefung-note");
		});

		it("legt den Termin mit der aus den Punkten abgeleiteten Note an", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.notes.negativ, freigegeben: true });

			page.visitAndWaitForTable(ctx);
			page.getPruefungAddButton(student.uid, "antritt_2").click();
			page.getPruefungModal().should("be.visible");

			page.setDate("pruefung-datum", page.toDDMMYYYY(attemptDate(ctx, 1)));
			page.setPruefungPunkte(TOP);

			cy.get("[data-cy='pruefung-submit']").click();
			cy.wait("@saveStudentPruefung").its("response.statusCode").should("eq", 200);
			page.getPruefungModal().should("not.be.visible");

			page.expectPruefung(student.uid, "antritt_2", { note: noteTop, antritt: 2 });
			page.expectLvNote(student.uid, page.bezeichnungOf(ctx, noteTop));
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
			page.addPruefungBulk({ uids: [a.uid, b.uid], punkte: MIDDLE, datum: page.toDDMMYYYY(attemptDate(ctx, 1)) });

			[a, b].forEach((student) =>
				page.expectPruefung(student.uid, "antritt_2", { note: noteMiddle, antritt: 2 }),
			);
		});
	});

	// Die Excel-Liste schreibt Dezimalstellen mit Komma oder mit Punkt. Beide Schreibweisen ergeben dieselbe Zahl.
	describe("Import mit Punkten", () => {
		const PUNKTE = 89.5;

		[
			["Dezimalkomma", "89,5"],
			["Dezimalpunkt", "89.5"],
		].forEach(([notation, input]) => {
			it(`liest Punkte mit ${notation} im Prüfungsimport`, function () {
				requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNGSIMPORT", true);
				requireWiederholung(this, ctx);

				const student = ctx.students[4];
				const datum = page.importDate(attemptDate(ctx, 1), ctx.cisConfig.CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT);

				resetNotenState(ctx);
				seedBaseline(ctx, student, { note: ctx.notes.negativ, freigegeben: true });
				page.visitAndWaitForTable(ctx);

				page.importPruefungen([[student.uid, datum, input]]);

				cy.get("@savePruefungenBulk").its("request.body.pruefungen.0.punkte").should("eq", PUNKTE);

				readStateViaApi(ctx).then((data) => {
					const newTermin = attemptsOfStudent(data, student.uid)[1];
					expect(newTermin, "der importierte Termin").to.exist;
					expect(Number(newTermin.punkte), "die Punkte des Termins").to.eq(PUNKTE);
				});
			});

			it(`liest Punkte mit ${notation} im Notenimport`, function () {
				requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENIMPORT", true);

				const student = ctx.students[5];

				resetNotenState(ctx);
				page.visitAndWaitForTable(ctx);

				page.importNoten([[student.uid, input]]);

				cy.get("@saveNotenvorschlagBulk").its("request.body.noten.0.punkte").should("eq", PUNKTE);

				readLvGesamtnoteViaDb(ctx, student.uid).then((row) => {
					expect(row, "die importierte LV-Note").to.not.be.null;
					expect(Number(row.punkte), "die Punkte der LV-Note").to.eq(PUNKTE);
				});
			});
		});
	});
});
