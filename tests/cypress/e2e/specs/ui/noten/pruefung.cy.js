import { loginAsLektor } from "../../../../support/api/notenApi";
import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import { waitForOk } from "../../../../support/helpers/network";
import {
	requireKommissionellerAntritt,
	requireConfig,
	requireNotenMode,
	requireRepeat,
	skipIf,
} from "../../../../support/helpers/notenConfig";
import { expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import { addPruefung, givenBaseline } from "../../../../support/helpers/notenScenario";
import {
	antrittDate,
	loadNotenContext,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";

/**
 * Add and edit Pruefungen in both ways: the dialog of a table cell and the "Neue Prüfung" dialog.
 * Both use the same core on the server (see api/noten/pruefung.cy.js). The spec checks that the cell
 * is correct without a reload.
 *
 * The specs use the column layout "antritt" (the "Termine" button), because its column names are fixed:
 * antritt_1, antritt_2, ... In the date mode a column is named after the date of the Pruefung.
 */
describe("Benotungstool UI - Prüfungen", () => {
	let ctx;
	let bezeichnung;

	before(() => {
		requireDbReset();
		loadNotenContext().then((loaded) => {
			ctx = loaded;
			bezeichnung = (note) => page.bezeichnungOf(ctx, note);

			expect(ctx.maxAntritte, "braucht Platz für mindestens eine Wiederholung").to.be.greaterThan(1);
		});
	});

	beforeEach(() => loginAsLektor());

	beforeEach(function () {
		// in the Punkte mode the Note field is locked: the Note comes from the Notenschluessel
		requireNotenMode(this, ctx);
	});

	it("legt aus der Zelle eine Wiederholung an und zählt sie als Antritt 2", function () {
		requireRepeat(this, ctx);

		const student = ctx.students[0];

		resetNotenState(ctx);
		seedBaseline(ctx, student, { note: ctx.noten.negativ });

		page.visitAndWaitForTable(ctx);

		page.addPruefungInCell(student.uid, "antritt_2", {
			note: bezeichnung(ctx.notenScale[1]),
			datum: page.toDDMMYYYY(antrittDate(ctx, 1)),
		});

		page.expectPruefung(student.uid, "antritt_2", { note: ctx.notenScale[1], antritt: 2 });
		page.expectAntrittCount(student.uid, 2);

		// the LV-Note follows the newest Antritt
		page.expectLvNote(student.uid, bezeichnung(ctx.notenScale[1]));

		// the cell shows the Freigabe state that the server wrote; api/noten/pruefung.cy.js checks the rule
		readLvGesamtnoteViaDb(ctx, student).then((row) => {
			const changed = new Date(row.benotungsdatum) > new Date(row.freigabedatum);
			page.expectFreigabeState(student.uid, changed ? "changed" : "freigegeben");
		});
	});

	it("nennt nach einer positiven Note den Grund statt der Schaltfläche", function () {
		requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENVERBESSERUNG", false);
		skipIf(
			this,
			!ctx.noten.positiv,
			"Übersprungen: keine positive Note, die die Kette nur ohne Notenverbesserung schliesst.",
		);

		const student = ctx.students[0];

		givenBaseline(ctx, student, { note: ctx.noten.positiv });

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
		cy.get("[data-cy='pruefung-without-lvnote']").should("be.visible");
		cy.get("[data-cy='pruefung-submit']").click();
		page.getPruefungModal().should("not.be.visible");

		// without a selected Note the server writes "Noch nicht eingetragen", which uses no Antritt
		page.expectPruefung(student.uid, "antritt_1", { note: ctx.noten.nochNichtEingetragen });
		page.expectLvNote(student.uid, bezeichnung(ctx.noten.nochNichtEingetragen));
		page.expectAntrittCount(student.uid, 0);
	});

	it("korrigiert nur das Datum eines bestehenden Antritts", function () {
		requireRepeat(this, ctx);

		const student = ctx.students[0];
		const newDate = antrittDate(ctx, 2);

		resetNotenState(ctx);
		seedBaseline(ctx, student, { note: ctx.noten.negativ });

		page.visitAndWaitForTable(ctx);

		page.addPruefungInCell(student.uid, "antritt_2", {
			note: bezeichnung(ctx.notenScale[1]),
			datum: page.toDDMMYYYY(antrittDate(ctx, 1)),
		});
		page.editPruefungInCell(student.uid, "antritt_2", { datum: page.toDDMMYYYY(newDate) });

		page.expectPruefung(student.uid, "antritt_2", { note: ctx.notenScale[1], antritt: 2 });
		page.getCell(student.uid, "antritt_2").should("contain.text", page.toDDMMYYYY(newDate));
	});

	it("sperrt die Note, sobald ein späterer Antritt existiert", function () {
		requireRepeat(this, ctx);
		requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETERER_PRUEFUNG", true);

		const student = ctx.students[0];

		resetNotenState(ctx);
		seedBaseline(ctx, student, { note: ctx.noten.negativ });

		page.visitAndWaitForTable(ctx);

		// entschuldigt first: it uses no Antritt, so the third column stays reachable also with
		// maxAntritte = 2
		page.addPruefungInCell(student.uid, "antritt_2", {
			note: bezeichnung(ctx.noten.entschuldigt),
			datum: page.toDDMMYYYY(antrittDate(ctx, 1)),
		});
		page.addPruefungInCell(student.uid, "antritt_3", {
			note: bezeichnung(ctx.notenScale[1]),
			datum: page.toDDMMYYYY(antrittDate(ctx, 2)),
		});

		page.openPruefungModalForEdit(student.uid, "antritt_2");

		cy.get("[data-cy='pruefung-note-locked']").should("be.visible");
		cy.get("[data-cy='pruefung-note']").should("have.class", "p-disabled");
	});

	it("legt über die Sammelanlage für mehrere Studierende denselben Termin an", function () {
		requireRepeat(this, ctx);

		const [a, b] = ctx.students;

		resetNotenState(ctx);
		seedBaseline(ctx, a, { note: ctx.noten.negativ });
		seedBaseline(ctx, b, { note: ctx.noten.negativ });

		page.visitAndWaitForTable(ctx);

		page.createPruefungen({
			uids: [a.uid, b.uid],
			note: bezeichnung(ctx.notenScale[1]),
			datum: page.toDDMMYYYY(antrittDate(ctx, 1)),
		});

		[a, b].forEach((student) => {
			page.expectPruefung(student.uid, "antritt_2", { note: ctx.notenScale[1], antritt: 2 });
			page.expectAntrittCount(student.uid, 2);
			page.expectLvNote(student.uid, bezeichnung(ctx.notenScale[1]));
		});
	});

	// The table holds the selection; the multiselect of the dialog changes it through the table.
	it("gleicht die Auswahl zwischen Tabelle und Sammeldialog ab", function () {
		requireRepeat(this, ctx);

		const [a, b] = ctx.students;

		resetNotenState(ctx);
		seedBaseline(ctx, a, { note: ctx.noten.negativ });
		seedBaseline(ctx, b, { note: ctx.noten.negativ });

		page.visitAndWaitForTable(ctx);

		page.getRowCheckbox(a.uid).click();
		page.openCreatePruefungen();
		page.expectStudentsInDialog([a.uid]);

		// b in, a out
		page.toggleStudentsInDialog([b.uid, a.uid]);

		page.expectStudentsInDialog([b.uid]);
		page.expectRowSelected(b.uid, true);
		page.expectRowSelected(a.uid, false);
	});

	// The "Neue Prüfung" dialog checks a Pruefung on the same day like the server does,
	// by CIS_GESAMTNOTE_PRUEFUNG_GLEICHER_TAG.
	describe("Termin am selben Tag im Sammeldialog", () => {
		beforeEach(function () {
			requireRepeat(this, ctx);
		});

		// 'entschuldigt' uses no Antritt, so one more Pruefung stays possible
		const withPruefungOn = (student, datum) => {
			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.noten.negativ });
			addPruefung(ctx, student, { note: ctx.noten.entschuldigt, datum }).then((response) =>
				expectNotenSuccess(response, "bestehender Termin"),
			);
			page.visitAndWaitForTable(ctx);
		};

		const submitNewPruefung = (student, datum) =>
			page.submitCreatePruefungen({ uids: [student.uid], datum: page.toDDMMYYYY(datum) });

		it("schickt einen Termin am selben Tag ab, wenn der Schalter an ist", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNG_GLEICHER_TAG", true);

			const student = ctx.students[0];
			const datum = antrittDate(ctx, 1);

			withPruefungOn(student, datum);
			submitNewPruefung(student, datum);

			waitForOk("@createPruefungen");
			cy.get(".p-toast-message-warn").should("not.exist");
		});

		it("warnt bei einem Termin am selben Tag und schickt nichts ab", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNG_GLEICHER_TAG", false);

			const student = ctx.students[0];
			const datum = antrittDate(ctx, 1);

			withPruefungOn(student, datum);
			submitNewPruefung(student, datum);

			cy.get(".p-toast-message-warn").should("contain.text", student.uid);
			page.getPruefungModal().should("not.be.visible");
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
		seedBaseline(ctx, student, { note: ctx.noten.negativ });

		page.visitAndWaitForTable(ctx);

		// the baseline gives Antritt 1; the normal columns go up to the second-to-last Antritt
		for (let i = 1; i < ctx.maxAntritte - 1; i += 1) {
			page.addPruefungInCell(student.uid, `antritt_${i + 1}`, {
				note: bezeichnung(ctx.noten.negativ),
				datum: page.toDDMMYYYY(antrittDate(ctx, i)),
			});
		}

		// By the Pruefungsordnung the last Antritt is kommissionell. It gets no column of its own,
		// but the next Pruefung column. The K in the cell marks it.
		const lastAntrittColumn = `antritt_${ctx.maxAntritte}`;

		page.getPruefungAddButton(student.uid, lastAntrittColumn).should("exist");

		page.addPruefungInCell(student.uid, lastAntrittColumn, {
			note: bezeichnung(ctx.noten.negativ),
			datum: page.toDDMMYYYY(antrittDate(ctx, ctx.maxAntritte)),
		});

		page.expectAntrittCount(student.uid, ctx.maxAntritte);
		// the badge shows the Antritt number and the "K" of the kommissionelle Pruefung
		page.expectPruefung(student.uid, lastAntrittColumn, {
			note: ctx.noten.negativ,
			antritt: `${ctx.maxAntritte}-K`,
		});
		page.expectNoAntrittColumn(ctx.maxAntritte + 1);
	});
});
