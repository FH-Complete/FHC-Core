import { loginAsLektor } from "../../../../support/api/notenApi";
import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import { requireConfig, requireNotenMode } from "../../../../support/helpers/notenConfig";
import {
	antrittDate,
	baselineDate,
	loadNotenContext,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedBaseline,
	seedPruefung,
} from "../../../../support/helpers/notenTestData";

/**
 * Enter a proposal and apply it as the LV-Note.
 *
 * api/noten/lvnote.cy.js checks the rules. This spec checks that the table shows the server state
 * without a reload: the LV-Note, the Freigabe state, and that the apply button goes away.
 */
describe("Benotungstool UI - Notenvorschlag", () => {
	let ctx;
	let bezeichnung;

	before(() => {
		requireDbReset();
		loadNotenContext().then((loaded) => {
			ctx = loaded;
			bezeichnung = (note) => page.bezeichnungOf(ctx, note);
		});
	});

	beforeEach(() => loginAsLektor());

	beforeEach(function () {
		// in the Punkte mode the proposal column is locked: the Note comes from the Punkte
		requireNotenMode(this, ctx);
	});

	it("trägt einen Vorschlag über den Zelleneditor ein", () => {
		const student = ctx.students[0];

		resetNotenState(ctx);
		page.visitAndWaitForTable(ctx);

		// without a Note the row is empty: no benotungsdatum -> open
		page.expectFreigabeState(student.uid, "open");

		page.setProposal(student.uid, bezeichnung(ctx.notenScale[0]));

		page.expectProposal(student.uid, bezeichnung(ctx.notenScale[0]));
		// the proposal alone writes no LV-Note
		page.expectFreigabeState(student.uid, "open");
	});

	it("übernimmt den Vorschlag als LV-Note und zeigt den Status changed", () => {
		const student = ctx.students[0];

		resetNotenState(ctx);
		page.visitAndWaitForTable(ctx);

		page.setProposal(student.uid, bezeichnung(ctx.notenScale[0]));
		page.applyProposal(student.uid);

		page.expectLvNote(student.uid, bezeichnung(ctx.notenScale[0]));
		page.expectFreigabeState(student.uid, "changed");
	});

	// The selected date is the date of Antritt 1, not the benotungsdatum. The benotungsdatum stays the
	// time of the entry, otherwise a changed Note would look freigegeben.
	it("schreibt das im Dialog gewählte Datum in den ersten Antritt", function () {
		requireConfig(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", true);

		const student = ctx.students[2];
		const datum = baselineDate(ctx);

		resetNotenState(ctx);
		page.visitAndWaitForTable(ctx);

		page.setProposal(student.uid, bezeichnung(ctx.notenScale[0]));
		page.applyProposal(student.uid, { datum: page.toDDMMYYYY(datum) });

		page.expectLvNote(student.uid, bezeichnung(ctx.notenScale[0]));
		page.getCell(student.uid, "antritt_1").should("contain.text", page.toDDMMYYYY(datum));

		readLvGesamtnoteViaDb(ctx, student).then((rowData) => {
			expect(String(rowData.benotungsdatum).slice(0, 10), "das benotungsdatum bleibt heute").to.eq(
				new Date().toISOString().slice(0, 10),
			);
		});
	});

	// A single repeat without Antritt 1, as the StV can leave it. After a repeat the Note belongs to the
	// Pruefung: the proposal column is locked, so the row has neither an editor nor a button. The control
	// row shows that the button appears without a repeat.
	it("zeigt bei einer einzelnen Wiederholung keinen Übernehmen-Button", function () {
		requireConfig(this, ctx, "CIS_GESAMTNOTE_VORSCHLAG_NACH_WIEDERHOLUNG", false);

		const wiederholung = ctx.students[3];
		const control = ctx.students[4];
		// "Sehr Gut" first: setProposal uses contains, and "Gut" would match "Sehr Gut"
		const vorschlag = ctx.notenScale[0];
		const pruefungsnote = ctx.notenScale.find(
			(n) => ![vorschlag, ctx.noten.negativ].map(String).includes(String(n)),
		);

		resetNotenState(ctx);
		seedBaseline(ctx, wiederholung, { erstantritt: false });
		seedBaseline(ctx, control);
		seedPruefung(ctx, wiederholung, { note: pruefungsnote, datum: antrittDate(ctx, 1), type: "Termin2" });

		page.visitAndWaitForTable(ctx);

		page.setProposal(control.uid, bezeichnung(vorschlag));
		page.getApplyProposalButton(control.uid).should("exist");

		page.expectProposalLocked(wiederholung.uid);
		page.getApplyProposalButton(wiederholung.uid).should("not.exist");
	});

	// With the switch on the server leaves the LV-Note open after a repeat, and the page follows it.
	it("bietet nach einer Wiederholung den Übernehmen-Button an, wenn der Schalter an ist", function () {
		requireConfig(this, ctx, "CIS_GESAMTNOTE_VORSCHLAG_NACH_WIEDERHOLUNG", true);

		const student = ctx.students[3];
		const vorschlag = ctx.notenScale[0];
		const pruefungsnote = ctx.notenScale.find(
			(n) => ![vorschlag, ctx.noten.negativ].map(String).includes(String(n)),
		);

		resetNotenState(ctx);
		seedBaseline(ctx, student, { erstantritt: false });
		seedPruefung(ctx, student, { note: pruefungsnote, datum: antrittDate(ctx, 1), type: "Termin2" });

		page.visitAndWaitForTable(ctx);

		page.setProposal(student.uid, bezeichnung(vorschlag));
		page.getApplyProposalButton(student.uid).should("exist");
	});

	// The LV-Note is never 'entschuldigt', so the editor does not offer that Note.
	it("bietet 'entschuldigt' nicht als Notenvorschlag an", () => {
		const student = ctx.students[1];

		resetNotenState(ctx);
		page.visitAndWaitForTable(ctx);

		page.getCell(student.uid, "proposed_note").click();

		// exact match: "unentschuldigt" contains "entschuldigt"
		cy.get(".tabulator-edit-list-item")
			.should("have.length.greaterThan", 0)
			.then((items) => {
				const labels = [...items].map((item) => item.innerText.trim());
				expect(labels, "Optionen des Editors").to.include(bezeichnung(ctx.notenScale[0]));
				expect(labels, "Optionen des Editors").to.not.include(bezeichnung(ctx.noten.entschuldigt));
			});
	});

	it("blendet den Übernehmen-Button aus, sobald Vorschlag und LV-Note übereinstimmen", () => {
		const student = ctx.students[0];

		resetNotenState(ctx);
		page.visitAndWaitForTable(ctx);

		page.setProposal(student.uid, bezeichnung(ctx.notenScale[0]));
		page.applyProposal(student.uid);

		page.getApplyProposalButton(student.uid).should("not.exist");
	});
});
