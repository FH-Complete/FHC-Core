import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import {
	baselineDate,
	loadNotenContext,
	readLvGesamtnote,
	requireDbReset,
	resetNotenState,
} from "../../../../support/helpers/notenTestData";

/**
 * Notenvorschlag eintragen und übernehmen.
 *
 * Die Regeln dahinter prüft noten.notenvorschlag; hier geht es darum, dass die Tabelle den
 * Serverzustand ohne Reload korrekt nachführt: LV-Note, Freigabestatus und das Verschwinden des
 * Übernehmen-Buttons.
 */
context("Benotungstool UI - Notenvorschlag", () => {
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
			// im Punktemodus ist die Vorschlagsspalte gesperrt, die Note kommt aus den Punkten
			Cypress.log({ name: "skip", message: "Skipped: CIS_GESAMTNOTE_PUNKTE ist aktiv." });
			this.skip();
		}
	});

	it("trägt einen Vorschlag über den Zelleneditor ein", () => {
		const student = ctx.students[0];

		resetNotenState(ctx);
		page.visitAndWaitForTable(ctx);

		// ohne Note ist die Zeile leer: kein Benotungsdatum -> offen
		page.expectFreigabeState(student.uid, "offen");

		page.setNotenvorschlag(student.uid, bezeichnung(ctx.gradeNotes[0]));

		page.expectNotenvorschlag(student.uid, bezeichnung(ctx.gradeNotes[0]));
		// der Vorschlag allein schreibt noch keine LV-Note
		page.expectFreigabeState(student.uid, "offen");
	});

	it("übernimmt den Vorschlag als LV-Note und zeigt den Status changed", () => {
		const student = ctx.students[0];

		resetNotenState(ctx);
		page.visitAndWaitForTable(ctx);

		page.setNotenvorschlag(student.uid, bezeichnung(ctx.gradeNotes[0]));
		page.uebernehmen(student.uid);

		page.expectLvNote(student.uid, bezeichnung(ctx.gradeNotes[0]));
		page.expectFreigabeState(student.uid, "changed");
	});

	// Das gewählte Datum ist das Datum von Antritt 1, nicht das benotungsdatum. Das benotungsdatum
	// bleibt der Zeitpunkt der Eingabe, sonst erschiene eine geänderte Note als freigegeben.
	it("schreibt das im Dialog gewählte Datum in den ersten Antritt", () => {
		const student = ctx.students[2];
		const datum = baselineDate(ctx);

		resetNotenState(ctx);
		page.visitAndWaitForTable(ctx);

		page.setNotenvorschlag(student.uid, bezeichnung(ctx.gradeNotes[0]));
		page.uebernehmen(student.uid, { datum: page.toDDMMYYYY(datum) });

		page.expectLvNote(student.uid, bezeichnung(ctx.gradeNotes[0]));
		page.getCell(student.uid, "antritt_1").should("contain.text", page.toDDMMYYYY(datum));

		readLvGesamtnote(ctx, student.uid).then((rowData) => {
			expect(String(rowData.benotungsdatum).slice(0, 10), "das benotungsdatum bleibt heute")
				.to.eq(new Date().toISOString().slice(0, 10));
		});
	});

	it("blendet den Übernehmen-Button aus, sobald Vorschlag und LV-Note übereinstimmen", () => {
		const student = ctx.students[0];

		resetNotenState(ctx);
		page.visitAndWaitForTable(ctx);

		page.setNotenvorschlag(student.uid, bezeichnung(ctx.gradeNotes[0]));
		page.uebernehmen(student.uid);

		page.getUebernehmenButton(student.uid).should("not.exist");
	});

});
