import { notenAuth } from "../../../../support/api/notenApi";
import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import { requireConfig } from "../../../../support/helpers/notenConfig";
import {
	baselineDate,
	loadNotenContext,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";

/**
 * Approve grades via the modal dialog using a password.
 *
 * The dialog lists exactly which rows are being released (changedNoten) and prompts for the
 * LDAP password. Afterward, the status column must change from “changed” to “ok” without a reload.
 *
 * A successful release triggers the release email. The development instances deliver it to a
 * debug mailbox.
 */
const freigabePassword = () => Cypress.env("NOTEN_FREIGABE_PASSWORD") || notenAuth().password;

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
			// If no password is required, the dialog box allows access
			requireConfig(this, ctx, "CIS_GESAMTNOTE_FREIGABE_PASSWORT", true);

			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.gradeNotes[0], freigegeben: false });

			page.visitAndWaitForTable(ctx);
			page.openFreigabeModal();

			page.typeFreigabePassword("definitely-not-the-password");
			page.submitFreigabe();

			// The interface displays the rejection, but the status remains the same
			page.expectRejected("@saveStudentenNoten");
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
			requireConfig(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", true);

			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.gradeNotes[0], freigegeben: false });

			page.visitAndWaitForTable(ctx);
			page.openFreigabeModal();
			page.freigeben(freigabePassword());

			// upsertErstantritt writes the audit entry; it becomes visible the next time the page is loaded
			page.visitAndWaitForTable(ctx);
			page.expectPruefung(student.uid, "antritt_1", { note: ctx.gradeNotes[0], antritt: 1 });
			page.expectAntrittCount(student.uid, 1);

			// The start date uses the grading date of the course grade; no implicit date is used
			page.getCell(student.uid, "antritt_1").should("contain.text", page.toDDMMYYYY(baselineDate(ctx)));
		});
	});
});
