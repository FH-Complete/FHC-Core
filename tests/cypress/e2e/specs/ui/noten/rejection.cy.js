import { loginAsLektor } from "../../../../support/api/notenApi";
import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import { requireConfig, requireNotenMode, requireRepeat } from "../../../../support/helpers/notenConfig";
import {
	antrittDate,
	loadNotenContext,
	requireDbReset,
	resetNotenState,
	seedBaseline,
	seedPruefung,
} from "../../../../support/helpers/notenTestData";

/**
 * The server rejects a save, and the page shows it.
 *
 * Each test loads the table and then changes the database. So the table is out of date, as if
 * someone entered data in a second window. The client checks the old state and sends the request;
 * only the server rejects it. The test reads the message from the response and expects exactly this
 * message in a toast. So it needs no phrase and runs in every profile.
 */
describe("Benotungstool UI - Ablehnung durch den Server", () => {
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
		// in the Punkte mode the Note fields are locked: the Note comes from the Punkte
		requireNotenMode(this, ctx);
	});

	/** An entschuldigt Pruefung after the date that the test enters. It uses no Antritt. */
	const laterPruefungInBackground = (student) =>
		seedPruefung(ctx, student, { note: ctx.noten.entschuldigt, datum: antrittDate(ctx, 3), type: "Termin2" });

	it("zeigt die Ablehnung eines Termins aus dem Zelldialog", function () {
		requireRepeat(this, ctx);

		const student = ctx.students[0];

		resetNotenState(ctx);
		seedBaseline(ctx, student, { note: ctx.noten.negativ, freigegeben: true });
		page.visitAndWaitForTable(ctx);
		laterPruefungInBackground(student);

		page.submitPruefungInCell(student.uid, "antritt_2", {
			note: bezeichnung(ctx.noten.negativ),
			datum: page.toDDMMYYYY(antrittDate(ctx, 1)),
		});

		page.expectRejected("@savePruefung");
		page.expectNoPruefung(student.uid, "antritt_2");
	});

	it("zeigt die Ablehnung einer Übernahme und behält die LV-Note", function () {
		// the repeat in the background locks the LV-Note only while this switch is off
		requireConfig(this, ctx, "CIS_GESAMTNOTE_VORSCHLAG_NACH_WIEDERHOLUNG", false);

		const student = ctx.students[1];

		resetNotenState(ctx);
		seedBaseline(ctx, student, { note: ctx.noten.negativ, freigegeben: false, erstantritt: false });
		page.visitAndWaitForTable(ctx);

		// a Pruefung written in the background blocks the change; the table does not know it
		seedPruefung(ctx, student, { note: ctx.noten.negativ, datum: antrittDate(ctx, 1), type: "Termin2" });

		// "Sehr Gut" first: setProposal uses contains, and "Gut" would match "Sehr Gut"
		page.setProposal(student.uid, bezeichnung(ctx.notenScale[0]));
		page.submitApplyProposal(student.uid);

		page.expectRejected("@saveLvNote");
		page.expectLvNote(student.uid, bezeichnung(ctx.noten.negativ));
	});

	it("zeigt die abgelehnte Zeile der Sammelanlage und meldet keinen Erfolg", function () {
		requireRepeat(this, ctx);

		const student = ctx.students[2];

		resetNotenState(ctx);
		seedBaseline(ctx, student, { note: ctx.noten.negativ, freigegeben: true });
		page.visitAndWaitForTable(ctx);
		laterPruefungInBackground(student);

		page.submitCreatePruefungen({
			uids: [student.uid],
			note: bezeichnung(ctx.noten.negativ),
			datum: page.toDDMMYYYY(antrittDate(ctx, 1)),
		});

		page.expectRowRejected("@createPruefungen", student.uid);
		page.expectNoSuccess();
		page.expectNoPruefung(student.uid, "antritt_2");
	});

	it("zeigt die abgelehnte Zeile des Prüfungsimports und meldet keinen Erfolg", function () {
		requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNGSIMPORT", true);
		requireRepeat(this, ctx);

		const student = ctx.students[3];

		resetNotenState(ctx);
		seedBaseline(ctx, student, { note: ctx.noten.negativ, freigegeben: true });
		page.visitAndWaitForTable(ctx);
		laterPruefungInBackground(student);

		page.submitPruefungImport([
			[
				student.uid,
				page.importDate(antrittDate(ctx, 1), ctx.cisConfig.CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT),
				ctx.noten.negativ,
			],
		]);

		page.expectRowRejected("@importPruefungen", student.uid);
		page.expectNoSuccess();
		page.expectNoPruefung(student.uid, "antritt_2");
	});

	it("zeigt die abgelehnte Zeile des Notenimports und meldet keinen Erfolg", function () {
		requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENIMPORT", true);

		const student = ctx.students[4];

		resetNotenState(ctx);
		seedBaseline(ctx, student, { note: ctx.noten.negativ, freigegeben: false, erstantritt: false });
		page.visitAndWaitForTable(ctx);
		seedPruefung(ctx, student, { note: ctx.noten.negativ, datum: antrittDate(ctx, 1), type: "Termin2" });

		page.submitLvNotenImport([[student.uid, ctx.notenScale[0]]]);

		page.expectRowRejected("@importLvNoten", student.uid);
		page.expectNoSuccess();
		page.expectLvNote(student.uid, bezeichnung(ctx.noten.negativ));
	});
});
