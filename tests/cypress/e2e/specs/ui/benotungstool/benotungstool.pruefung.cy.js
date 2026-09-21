import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import { waitForOk } from "../../../../support/helpers/network";
import {
	requireKommissionellerAntritt,
	requireConfig,
	requireNotenMode,
	requireWiederholung,
	skipIf,
} from "../../../../support/helpers/notenConfig";
import { expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import { addPruefung, givenBaseline } from "../../../../support/helpers/notenScenario";
import {
	attemptDate,
	loadNotenContext,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";

/**
 * Create and edit exams using both methods: the dialog from the table cell and the
 * batch creation from import. Both run on the server side using the same core
 * (see noten.pruefungstermin); the system verifies that the cell is correct afterward without reloading.
 *
 * The specs run in schedule mode (the “Schedules” button) because the column names are consistent there:
 * antritt_1, antritt_2, ... In date mode, the column is named after the exam date.
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
		// im Punktemodus ist das Notenfeld gesperrt, die Note kommt aus dem Notenschlüssel
		requireNotenMode(this, ctx);
	});

	it("legt aus der Zelle eine Wiederholung an und zählt sie als Antritt 2", function () {
		requireWiederholung(this, ctx);

		const student = ctx.students[0];

		resetNotenState(ctx);
		seedBaseline(ctx, student, { note: ctx.notes.negativ });

		page.visitAndWaitForTable(ctx);

		page.addPruefungInCell(student.uid, "antritt_2", {
			note: bezeichnung(ctx.gradeNotes[1]),
			datum: page.toDDMMYYYY(attemptDate(ctx, 1)),
		});

		page.expectPruefung(student.uid, "antritt_2", { note: ctx.gradeNotes[1], antritt: 2 });
		page.expectAntrittCount(student.uid, 2);

		// die LV-Note folgt dem neuesten Antritt
		page.expectLvNote(student.uid, bezeichnung(ctx.gradeNotes[1]));

		// die Zelle zeigt den Freigabestatus, den der Server geschrieben hat; die Regel prüft noten.pruefungstermin
		readLvGesamtnoteViaDb(ctx, student.uid).then((row) => {
			const changed = new Date(row.benotungsdatum) > new Date(row.freigabedatum);
			page.expectFreigabeState(student.uid, changed ? "changed" : "ok");
		});
	});

	it("nennt nach einer positiven Note den Grund statt der Schaltfläche", function () {
		requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENVERBESSERUNG", false);
		skipIf(
			this,
			!ctx.notes.positiv,
			"Übersprungen: keine positive Note, die die Kette nur ohne Notenverbesserung schliesst.",
		);

		const student = ctx.students[0];

		givenBaseline(ctx, student, { note: ctx.notes.positiv });

		page.visitAndWaitForTable(ctx);

		page.getBestandenHint(student.uid, "antritt_2").should("exist").and("have.attr", "title").and("not.be.empty");
		page.getPruefungAddButton(student.uid, "antritt_2").should("not.exist");
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

	it("korrigiert nur das Datum eines bestehenden Antritts", function () {
		requireWiederholung(this, ctx);

		const student = ctx.students[0];
		const newDate = attemptDate(ctx, 2);

		resetNotenState(ctx);
		seedBaseline(ctx, student, { note: ctx.notes.negativ });

		page.visitAndWaitForTable(ctx);

		page.addPruefungInCell(student.uid, "antritt_2", {
			note: bezeichnung(ctx.gradeNotes[1]),
			datum: page.toDDMMYYYY(attemptDate(ctx, 1)),
		});
		page.editPruefungInCell(student.uid, "antritt_2", { datum: page.toDDMMYYYY(newDate) });

		page.expectPruefung(student.uid, "antritt_2", { note: ctx.gradeNotes[1], antritt: 2 });
		page.getCell(student.uid, "antritt_2").should("contain.text", page.toDDMMYYYY(newDate));
	});

	it("sperrt die Note, sobald ein späterer Antritt existiert", function () {
		requireWiederholung(this, ctx);
		requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETEREM_TERMIN", true);

		const student = ctx.students[0];

		resetNotenState(ctx);
		seedBaseline(ctx, student, { note: ctx.notes.negativ });

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

	it("legt über die Sammelanlage für mehrere Studierende denselben Termin an", function () {
		requireWiederholung(this, ctx);

		const [a, b] = ctx.students;

		resetNotenState(ctx);
		seedBaseline(ctx, a, { note: ctx.notes.negativ });
		seedBaseline(ctx, b, { note: ctx.notes.negativ });

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

	// Der Sammeldialog prüft einen Termin am selben Tag wie der Server nach
	// CIS_GESAMTNOTE_TERMIN_GLEICHER_TAG.
	describe("Termin am selben Tag im Sammeldialog", () => {
		beforeEach(function () {
			requireWiederholung(this, ctx);
		});

		// 'entschuldigt' verbraucht keinen Antritt, deshalb bleibt ein weiterer Termin möglich
		const withTerminOn = (student, datum) => {
			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.notes.negativ });
			addPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum }).then((response) =>
				expectNotenSuccess(response, "bestehender Termin"),
			);
			page.visitAndWaitForTable(ctx);
		};

		const submitBulkTermin = (student, datum) =>
			page.submitPruefungBulk({ uids: [student.uid], datum: page.toDDMMYYYY(datum) });

		it("schickt einen Termin am selben Tag ab, wenn der Schalter an ist", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_TERMIN_GLEICHER_TAG", true);

			const student = ctx.students[0];
			const datum = attemptDate(ctx, 1);

			withTerminOn(student, datum);
			submitBulkTermin(student, datum);

			waitForOk("@createPruefungen");
			cy.get(".p-toast-message-warn").should("not.exist");
		});

		it("warnt bei einem Termin am selben Tag und schickt nichts ab", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_TERMIN_GLEICHER_TAG", false);

			const student = ctx.students[0];
			const datum = attemptDate(ctx, 1);

			withTerminOn(student, datum);
			submitBulkTermin(student, datum);

			cy.get(".p-toast-message-warn").should("contain.text", student.uid);
			page.getNewPruefungModal().should("not.be.visible");
			cy.get("@createPruefungen.all").should("have.length", 0);
		});
	});

	it("legt den letzten Antritt als kommissionelle Prüfung an", function () {
		requireKommissionellerAntritt(this, ctx);
		skipIf(
			this,
			ctx.cisConfig.CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT !== ctx.maxAntritte,
			"Übersprungen: der letzte Antritt ist nicht kommissionell.",
		);

		const student = ctx.students[1];

		resetNotenState(ctx);
		seedBaseline(ctx, student, { note: ctx.notes.negativ });

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
		const lastTermin = `antritt_${ctx.maxAntritte}`;

		page.getPruefungAddButton(student.uid, lastTermin).should("exist");

		page.addPruefungInCell(student.uid, lastTermin, {
			note: bezeichnung(ctx.notes.negativ),
			datum: page.toDDMMYYYY(attemptDate(ctx, ctx.maxAntritte)),
		});

		page.expectAntrittCount(student.uid, ctx.maxAntritte);
		// The badge displays both the antritt number and the “K” for the commission exam
		page.expectPruefung(student.uid, lastTermin, {
			note: ctx.notes.negativ,
			antritt: `${ctx.maxAntritte}-K`,
		});
		page.expectNoAntrittColumn(ctx.maxAntritte + 1);
	});
});
