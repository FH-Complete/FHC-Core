import { freigabePassword, loginAsLektor } from "../../../../support/api/notenApi";
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
 * The Freigabe through the dialog, with the password.
 *
 * The dialog lists exactly the rows that it releases (changedLvNoten) and asks for the LDAP password.
 * After that the state column must change from "changed" to "freigegeben" without a reload.
 *
 * A successful Freigabe sends the Freigabe mail. The dev instances send it to a debug mailbox.
 */

describe("Benotungstool UI - Notenfreigabe", () => {
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

	describe("Dialog", () => {
		it("listet die noch nicht freigegebene Note mit ihrer Zielnote", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.notenScale[0], freigegeben: false, erstantritt: false });

			page.visitAndWaitForTable(ctx);
			page.expectFreigabeState(student.uid, "changed");

			page.openFreigabeModal();
			page.expectFreigabeSummaryRow(student.uid, bezeichnung(ctx.notenScale[0]));
		});

		it("sperrt die Freigabe, wenn keine LV-Note geändert ist", () => {
			resetNotenState(ctx);

			page.visitAndWaitForTable(ctx);
			page.openFreigabeModal();
			page.expectFreigabeEmpty();
		});

		it("lehnt ein falsches Passwort ab und lässt den Status unverändert", function () {
			// without CIS_GESAMTNOTE_FREIGABE_PASSWORT every password passes
			requireConfig(this, ctx, "CIS_GESAMTNOTE_FREIGABE_PASSWORT", true);

			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.notenScale[0], freigegeben: false, erstantritt: false });

			page.visitAndWaitForTable(ctx);
			page.openFreigabeModal();

			page.typeFreigabePassword("definitely-not-the-password");
			page.submitFreigabe();

			// the page shows the rejection, and the state stays the same
			page.expectRejected("@saveFreigabe");
			page.expectFreigabeState(student.uid, "changed");

			readLvGesamtnoteViaDb(ctx, student).then((rowData) => {
				expect(rowData.freigabedatum, "eine abgelehnte Freigabe stempelt nichts").to.be.null;
			});
		});
	});

	describe("Freigabe", () => {
		it("gibt frei und schaltet die Statusspalte auf freigegeben", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.notenScale[0], freigegeben: false, erstantritt: false });

			page.visitAndWaitForTable(ctx);
			page.expectFreigabeState(student.uid, "changed");

			page.openFreigabeModal();
			page.saveFreigabe(freigabePassword());

			page.expectFreigabeState(student.uid, "freigegeben");
			page.expectLvNote(student.uid, bezeichnung(ctx.notenScale[0]));
		});

		it("legt mit der Freigabe den ersten Antritt an", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", true);

			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.notenScale[0], freigegeben: false, erstantritt: false });

			page.visitAndWaitForTable(ctx);
			page.openFreigabeModal();
			page.saveFreigabe(freigabePassword());

			// the Freigabe writes Antritt 1; a new load proves that the server stored it
			page.visitAndWaitForTable(ctx);
			page.expectPruefung(student.uid, "antritt_1", { note: ctx.notenScale[0], antritt: 1 });
			page.expectAntrittCount(student.uid, 1);

			// Antritt 1 gets the benotungsdatum of the LV-Note, not an implicit date
			page.getCell(student.uid, "antritt_1").should("contain.text", page.toDDMMYYYY(baselineDate(ctx)));
		});
	});
});
