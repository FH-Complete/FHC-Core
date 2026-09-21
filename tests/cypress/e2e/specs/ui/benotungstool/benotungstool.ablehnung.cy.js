import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import { requireConfig, requireNotenMode, requireWiederholung } from "../../../../support/helpers/notenConfig";
import {
	attemptDate,
	loadNotenContext,
	requireDbReset,
	resetNotenState,
	seedBaseline,
	seedPruefung,
} from "../../../../support/helpers/notenTestData";

/**
 * The server rejects the save, and the interface displays this.
 *
 * Each test loads the table and then modifies the database. The table is therefore out of date, as if
 * an entry had been made in a second window. The client checks against the old state and sends the request;
 * only then does the server reject it. The test reads the message from the response and expects exactly that message in
 * a toast notification. It therefore does not hard-code any phrase and runs in every profile.
 */
context("Benotungstool UI - Ablehnung durch den Server", () => {
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
		// im Punktemodus sind die Notenfelder gesperrt, die Note kommt aus den Punkten
		requireNotenMode(this, ctx);
	});

	/** An excused termin after the date entered in the test. It does not count toward the attendance limit. */
	const laterTerminInBackground = (student) =>
		seedPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum: attemptDate(ctx, 3), type: "Termin2" });

	it("zeigt die Ablehnung eines Termins aus dem Zelldialog", function () {
		requireWiederholung(this, ctx);

		const student = ctx.students[0];

		resetNotenState(ctx);
		seedBaseline(ctx, student, { note: ctx.notes.negativ, freigegeben: true });
		page.visitAndWaitForTable(ctx);
		laterTerminInBackground(student);

		page.submitPruefungInCell(student.uid, "antritt_2", {
			note: bezeichnung(ctx.notes.negativ),
			datum: page.toDDMMYYYY(attemptDate(ctx, 1)),
		});

		page.expectRejected("@saveStudentPruefung");
		page.expectNoPruefung(student.uid, "antritt_2");
	});

	it("zeigt die Ablehnung einer Übernahme und behält die LV-Note", () => {
		const student = ctx.students[1];

		resetNotenState(ctx);
		seedBaseline(ctx, student, { note: ctx.notes.negativ, freigegeben: false, erstantritt: false });
		page.visitAndWaitForTable(ctx);

		// A duplicate entry prevents the update; the table is unaware of this
		seedPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, 1), type: "Termin2" });

		// “Sehr Gut” first: setNotenvorschlag uses the “contains” operator; ‘Gut’ would match “Sehr Gut”
		page.setNotenvorschlag(student.uid, bezeichnung(ctx.gradeNotes[0]));
		page.submitUebernahme(student.uid);

		page.expectRejected("@saveNotenvorschlag");
		page.expectLvNote(student.uid, bezeichnung(ctx.notes.negativ));
	});

	it("zeigt die abgelehnte Zeile der Sammelanlage und meldet keinen Erfolg", function () {
		requireWiederholung(this, ctx);

		const student = ctx.students[2];

		resetNotenState(ctx);
		seedBaseline(ctx, student, { note: ctx.notes.negativ, freigegeben: true });
		page.visitAndWaitForTable(ctx);
		laterTerminInBackground(student);

		page.submitPruefungBulk({
			uids: [student.uid],
			note: bezeichnung(ctx.notes.negativ),
			datum: page.toDDMMYYYY(attemptDate(ctx, 1)),
		});

		page.expectRowRejected("@createPruefungen", student.uid);
		page.expectNoSuccess();
		page.expectNoPruefung(student.uid, "antritt_2");
	});

	it("zeigt die abgelehnte Zeile des Prüfungsimports und meldet keinen Erfolg", function () {
		requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNGSIMPORT", true);
		requireWiederholung(this, ctx);

		const student = ctx.students[3];

		resetNotenState(ctx);
		seedBaseline(ctx, student, { note: ctx.notes.negativ, freigegeben: true });
		page.visitAndWaitForTable(ctx);
		laterTerminInBackground(student);

		page.submitPruefungImport([
			[
				student.uid,
				page.importDate(attemptDate(ctx, 1), ctx.cisConfig.CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT),
				ctx.notes.negativ,
			],
		]);

		page.expectRowRejected("@savePruefungenBulk", student.uid);
		page.expectNoSuccess();
		page.expectNoPruefung(student.uid, "antritt_2");
	});

	it("zeigt die abgelehnte Zeile des Notenimports und meldet keinen Erfolg", function () {
		requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENIMPORT", true);

		const student = ctx.students[4];

		resetNotenState(ctx);
		seedBaseline(ctx, student, { note: ctx.notes.negativ, freigegeben: false, erstantritt: false });
		page.visitAndWaitForTable(ctx);
		seedPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, 1), type: "Termin2" });

		page.submitNotenImport([[student.uid, ctx.gradeNotes[0]]]);

		page.expectRowRejected("@saveNotenvorschlagBulk", student.uid);
		page.expectNoSuccess();
		page.expectLvNote(student.uid, bezeichnung(ctx.notes.negativ));
	});
});
