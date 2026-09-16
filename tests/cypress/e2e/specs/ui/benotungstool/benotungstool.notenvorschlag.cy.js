import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import { requireKonfiguration, requireNotenModus } from "../../../../support/helpers/notenConfig";
import {
	attemptDate,
	baselineDate,
	loadNotenContext,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedBaseline,
	seedPruefung,
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
		// im Punktemodus ist die Vorschlagsspalte gesperrt, die Note kommt aus den Punkten
		requireNotenModus(this, ctx);
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
	it("schreibt das im Dialog gewählte Datum in den ersten Antritt", function () {
		requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", true);

		const student = ctx.students[2];
		const datum = baselineDate(ctx);

		resetNotenState(ctx);
		page.visitAndWaitForTable(ctx);

		page.setNotenvorschlag(student.uid, bezeichnung(ctx.gradeNotes[0]));
		page.uebernehmen(student.uid, { datum: page.toDDMMYYYY(datum) });

		page.expectLvNote(student.uid, bezeichnung(ctx.gradeNotes[0]));
		page.getCell(student.uid, "antritt_1").should("contain.text", page.toDDMMYYYY(datum));

		readLvGesamtnoteViaDb(ctx, student.uid).then((rowData) => {
			expect(String(rowData.benotungsdatum).slice(0, 10), "das benotungsdatum bleibt heute").to.eq(
				new Date().toISOString().slice(0, 10),
			);
		});
	});

	// W7: Eine einzelne Wiederholung ohne Antritt 1, wie sie die Studierendenverwaltung hinterlassen kann. Der
	// Button fehlt auch ohne Vorschlag, deshalb setzt der Test einen Vorschlag und prüft eine Kontrollzeile mit.
	it("zeigt bei einer einzelnen Wiederholung keinen Übernehmen-Button", () => {
		const wiederholung = ctx.students[3];
		const kontrolle = ctx.students[4];
		// Sehr Gut zuerst: setNotenvorschlag vergleicht mit contains, "Gut" träfe "Sehr Gut"
		const vorschlag = ctx.gradeNotes[0];
		const pruefungsnote = ctx.gradeNotes.find(
			(n) => ![vorschlag, ctx.notes.negativ].map(String).includes(String(n)),
		);

		resetNotenState(ctx);
		seedBaseline(ctx, wiederholung, { erstantritt: false });
		seedBaseline(ctx, kontrolle);
		seedPruefung(ctx, wiederholung, { note: pruefungsnote, datum: attemptDate(ctx, 1), typ: "Termin2" });

		page.visitAndWaitForTable(ctx);

		page.setNotenvorschlag(kontrolle.uid, bezeichnung(vorschlag));
		page.getUebernehmenButton(kontrolle.uid).should("exist");

		page.setNotenvorschlag(wiederholung.uid, bezeichnung(vorschlag));
		page.getUebernehmenButton(wiederholung.uid).should("not.exist");
	});

	// C4: Die LV-Note ist nie 'entschuldigt', der Editor bietet die Note deshalb nicht an.
	it("bietet 'entschuldigt' nicht als Notenvorschlag an", () => {
		const student = ctx.students[1];

		resetNotenState(ctx);
		page.visitAndWaitForTable(ctx);

		page.getCell(student.uid, "note_vorschlag").click();

		// exakter Vergleich: "unentschuldigt" enthält "entschuldigt"
		cy.get(".tabulator-edit-list-item")
			.should("have.length.greaterThan", 0)
			.then((items) => {
				const labels = [...items].map((item) => item.innerText.trim());
				expect(labels, "Optionen des Editors").to.include(bezeichnung(ctx.gradeNotes[0]));
				expect(labels, "Optionen des Editors").to.not.include(bezeichnung(ctx.notes.entschuldigt));
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
