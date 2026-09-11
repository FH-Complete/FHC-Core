import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import {
	attemptDate,
	loadNotenContext,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";

/**
 * Prüfungen anlegen und bearbeiten, über beide Wege: den Dialog aus der Tabellenzelle und die
 * Sammelanlage aus der Toolbar. Beide laufen serverseitig durch denselben Kern
 * (siehe noten.pruefungstermin); geprüft wird, dass die Zelle danach ohne Reload stimmt.
 *
 * Die Specs laufen im Terminmodus (Schaltfläche "Termine"), weil die Spaltennamen dort stabil sind:
 * antritt_1, antritt_2, ... Im Datumsmodus heisst die Spalte wie das Prüfungsdatum.
 */
context("Benotungstool UI - Prüfungen", () => {
	let ctx;
	let bezeichnung;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;
			bezeichnung = (note) => page.bezeichnungOf(ctx, note);

			expect(ctx.maxAntritte, "braucht Platz für mindestens eine Wiederholung").to.be.greaterThan(1);
		});
	});

	beforeEach(function () {
		if (ctx.cisConfig.CIS_GESAMTNOTE_PUNKTE) {
			// im Punktemodus ist das Notenfeld gesperrt, die Note kommt aus dem Notenschlüssel
			Cypress.log({ name: "skip", message: "Skipped: CIS_GESAMTNOTE_PUNKTE ist aktiv." });
			this.skip();
		}
	});

	it("legt aus der Zelle eine Wiederholung an und zählt sie als Antritt 2", () => {
		const student = ctx.students[0];

		resetNotenState(ctx);
		seedBaseline(ctx, student.uid, { note: ctx.notes.negativ, freigegeben: true });

		page.visitAndWaitForTable(ctx);

		page.addPruefungInCell(student.uid, "antritt_2", {
			note: bezeichnung(ctx.gradeNotes[1]),
			datum: page.toDDMMYYYY(attemptDate(ctx, 1)),
		});

		page.expectPruefung(student.uid, "antritt_2", { note: ctx.gradeNotes[1], antritt: 2 });
		page.expectAntrittCount(student.uid, 2);

		// die LV-Note folgt dem neuesten Antritt und ist damit wieder unfreigegeben
		page.expectLvNote(student.uid, bezeichnung(ctx.gradeNotes[1]));
		page.expectFreigabeState(student.uid, "changed");
	});

	it("legt einen Antritt für einen Studenten ohne LV-Note an und weist darauf hin", () => {
		const student = ctx.students[2];

		resetNotenState(ctx);
		page.visitAndWaitForTable(ctx);

		page.getPruefungAddButton(student.uid, "antritt_1").click();
		page.getPruefungModal().should("be.visible");
		cy.get("[data-cy='pruefung-ohne-lvnote']").should("be.visible");
		cy.get("[data-cy='pruefung-submit']").click();
		page.getPruefungModal().should("not.be.visible");

		// ohne gewählte Note entsteht "Noch nicht eingetragen" - das verbraucht keinen Antritt
		page.expectPruefung(student.uid, "antritt_1", { note: ctx.notes.nochNichtEingetragen });
		page.expectLvNote(student.uid, bezeichnung(ctx.notes.nochNichtEingetragen));
		page.expectAntrittCount(student.uid, 0);
	});

	it("korrigiert nur das Datum eines bestehenden Antritts", () => {
		const student = ctx.students[0];
		const neuesDatum = attemptDate(ctx, 2);

		resetNotenState(ctx);
		seedBaseline(ctx, student.uid, { note: ctx.notes.negativ, freigegeben: true });

		page.visitAndWaitForTable(ctx);

		page.addPruefungInCell(student.uid, "antritt_2", {
			note: bezeichnung(ctx.gradeNotes[1]),
			datum: page.toDDMMYYYY(attemptDate(ctx, 1)),
		});
		page.editPruefungInCell(student.uid, "antritt_2", { datum: page.toDDMMYYYY(neuesDatum) });

		page.expectPruefung(student.uid, "antritt_2", { note: ctx.gradeNotes[1], antritt: 2 });
		page.getCell(student.uid, "antritt_2").should("contain.text", page.toDDMMYYYY(neuesDatum));
	});

	it("sperrt die Note, sobald ein späterer Antritt existiert", () => {
		const student = ctx.students[0];

		resetNotenState(ctx);
		seedBaseline(ctx, student.uid, { note: ctx.notes.negativ, freigegeben: true });

		page.visitAndWaitForTable(ctx);

		// entschuldigt zuerst: verbraucht keinen Antritt, daher bleibt die dritte Spalte auch bei
		// maxAntritte = 2 erreichbar
		page.addPruefungInCell(student.uid, "antritt_2", {
			note: bezeichnung(ctx.notes.entschuldigt),
			datum: page.toDDMMYYYY(attemptDate(ctx, 1)),
		});
		page.addPruefungInCell(student.uid, "antritt_3", {
			note: bezeichnung(ctx.gradeNotes[1]),
			datum: page.toDDMMYYYY(attemptDate(ctx, 2)),
		});

		page.openPruefungModalForEdit(student.uid, "antritt_2");

		cy.get("[data-cy='pruefung-note-locked']").should("be.visible");
		cy.get("[data-cy='pruefung-note']").should("have.class", "p-disabled");
	});

	it("legt über die Sammelanlage für mehrere Studierende denselben Termin an", () => {
		const [a, b] = ctx.students;

		resetNotenState(ctx);
		seedBaseline(ctx, a.uid, { note: ctx.notes.negativ, freigegeben: true });
		seedBaseline(ctx, b.uid, { note: ctx.notes.negativ, freigegeben: true });

		page.visitAndWaitForTable(ctx);

		page.addPruefungBulk({
			uids: [a.uid, b.uid],
			note: bezeichnung(ctx.gradeNotes[1]),
			datum: page.toDDMMYYYY(attemptDate(ctx, 1)),
		});

		[a, b].forEach((student) => {
			page.expectPruefung(student.uid, "antritt_2", { note: ctx.gradeNotes[1], antritt: 2 });
			page.expectAntrittCount(student.uid, 2);
			page.expectLvNote(student.uid, bezeichnung(ctx.gradeNotes[1]));
		});
	});

	it("legt den letzten Antritt als kommissionelle Prüfung an", function () {
		if (ctx.cisConfig.CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF === false) {
			Cypress.log({ name: "skip", message: "Skipped: das Tool darf keine kommissionelle Prüfung anlegen." });
			this.skip();
		}

		const student = ctx.students[1];

		resetNotenState(ctx);
		seedBaseline(ctx, student.uid, { note: ctx.notes.negativ, freigegeben: true });

		page.visitAndWaitForTable(ctx);

		// Baseline liefert Antritt 1. Bis zum vorletzten Antritt laufen die normalen Spalten.
		for (let i = 1; i < ctx.maxAntritte - 1; i += 1) {
			page.addPruefungInCell(student.uid, `antritt_${i + 1}`, {
				note: bezeichnung(ctx.notes.negativ),
				datum: page.toDDMMYYYY(attemptDate(ctx, i)),
			});
		}

		// Der letzte Antritt ist laut Prüfungsordnung kommissionell. Er bekommt keine eigene Spalte,
		// sondern den nächsten Prüfungstermin. Das K in der Zelle kennzeichnet ihn.
		const letzterTermin = `antritt_${ctx.maxAntritte}`;

		page.getPruefungAddButton(student.uid, letzterTermin).should("exist");

		page.addPruefungInCell(student.uid, letzterTermin, {
			note: bezeichnung(ctx.notes.negativ),
			datum: page.toDDMMYYYY(attemptDate(ctx, ctx.maxAntritte)),
		});

		page.expectAntrittCount(student.uid, ctx.maxAntritte);
		// das Badge trägt beides: die Antrittsnummer und das K der kommissionellen Prüfung
		page.expectPruefung(student.uid, letzterTermin, {
			note: ctx.notes.negativ,
			antritt: `${ctx.maxAntritte}-K`,
		});
		page.expectKeineAntrittsspalte(ctx.maxAntritte + 1);
	});
});
