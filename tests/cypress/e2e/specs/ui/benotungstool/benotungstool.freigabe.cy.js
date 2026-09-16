import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import { requireKonfiguration } from "../../../../support/helpers/notenConfig";
import {
	baselineDate,
	loadNotenContext,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";

/**
 * Notenfreigabe über den Modal-Dialog mit Passwort.
 *
 * Der Dialog listet genau die Zeilen, die freigegeben werden (changedNoten), und verlangt das
 * LDAP-Passwort. Danach muss die Statusspalte ohne Reload von changed auf ok springen.
 *
 * Eine erfolgreiche Freigabe verschickt die Freigabemail. Die Entwicklungsinstanzen stellen sie in ein
 * Debug-Postfach zu.
 */
const freigabePassword = () => Cypress.env("NOTEN_FREIGABE_PASSWORD") || Cypress.env("adminpassword");

context("Benotungstool UI - Notenfreigabe", () => {
	let ctx;
	let bezeichnung;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;
			bezeichnung = (note) => page.bezeichnungOf(ctx, note);
		});
	});

	describe("Dialog", () => {
		it("listet die noch nicht freigegebene Note mit ihrer Zielnote", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.gradeNotes[0], freigegeben: false });

			page.visitAndWaitForTable(ctx);
			page.expectFreigabeState(student.uid, "changed");

			page.openFreigabeModal();
			page.expectFreigabeSummaryRow(student.uid, bezeichnung(ctx.gradeNotes[0]));
		});

		it("lehnt ein falsches Passwort ab und lässt den Status unverändert", function () {
			// ohne Passwortpflicht gibt der Dialog frei
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_FREIGABE_PASSWORT", true);

			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.gradeNotes[0], freigegeben: false });

			page.visitAndWaitForTable(ctx);
			page.openFreigabeModal();

			page.typeFreigabePasswort("definitely-not-the-password");
			page.submitFreigabe();

			// die Oberfläche zeigt die Ablehnung, der Status bleibt
			page.expectAbgelehnt("@saveStudentenNoten");
			page.expectFreigabeState(student.uid, "changed");

			readLvGesamtnoteViaDb(ctx, student.uid).then((rowData) => {
				expect(rowData.freigabedatum, "eine abgelehnte Freigabe stempelt nichts").to.be.null;
			});
		});
	});

	describe("Freigabe", () => {
		it("gibt frei und schaltet die Statusspalte auf ok", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.gradeNotes[0], freigegeben: false });

			page.visitAndWaitForTable(ctx);
			page.expectFreigabeState(student.uid, "changed");

			page.openFreigabeModal();
			page.freigeben(freigabePassword());

			page.expectFreigabeState(student.uid, "ok");
			page.expectLvNote(student.uid, bezeichnung(ctx.gradeNotes[0]));
		});

		it("legt mit der Freigabe den ersten Antritt an", function () {
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", true);

			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.gradeNotes[0], freigegeben: false });

			page.visitAndWaitForTable(ctx);
			page.openFreigabeModal();
			page.freigeben(freigabePassword());

			// upsertErstantritt schreibt die Prüfungszeile; sichtbar wird sie beim nächsten Laden
			page.visitAndWaitForTable(ctx);
			page.expectPruefung(student.uid, "antritt_1", { note: ctx.gradeNotes[0], antritt: 1 });
			page.expectAntrittCount(student.uid, 1);

			// der Antritt übernimmt das Benotungsdatum der LV-Note, kein implizites Datum
			page.getCell(student.uid, "antritt_1").should("contain.text", page.toDDMMYYYY(baselineDate(ctx)));
		});
	});
});
