import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import {
	loadNotenContext,
	requireDbReset,
	resetNotenState,
	seedBaseline,
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
			cy.log("Skipped: CIS_GESAMTNOTE_PUNKTE ist aktiv.");
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

	it("blendet den Übernehmen-Button aus, sobald Vorschlag und LV-Note übereinstimmen", () => {
		const student = ctx.students[0];

		resetNotenState(ctx);
		page.visitAndWaitForTable(ctx);

		page.setNotenvorschlag(student.uid, bezeichnung(ctx.gradeNotes[0]));
		page.uebernehmen(student.uid);

		page.getUebernehmenButton(student.uid).should("not.exist");
	});

	it("zeigt eine bereits freigegebene Note nach dem Laden als ok", () => {
		const student = ctx.students[1];

		resetNotenState(ctx);
		seedBaseline(ctx, student.uid, { note: ctx.gradeNotes[0], freigegeben: true });

		page.visitAndWaitForTable(ctx);

		page.expectLvNote(student.uid, bezeichnung(ctx.gradeNotes[0]));
		page.expectFreigabeState(student.uid, "ok");
	});

	it("zeigt eine erfasste, nicht freigegebene Note nach dem Laden als changed", () => {
		const student = ctx.students[1];

		resetNotenState(ctx);
		seedBaseline(ctx, student.uid, { note: ctx.gradeNotes[0], freigegeben: false });

		page.visitAndWaitForTable(ctx);

		// getStudentenNoten liest ungefiltert - wäre das nicht so, bliebe die Zeile hier leer
		page.expectLvNote(student.uid, bezeichnung(ctx.gradeNotes[0]));
		page.expectFreigabeState(student.uid, "changed");
	});
});
