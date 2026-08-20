import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import {
	loadNotenContext,
	readLvGesamtnote,
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
 * Ein erfolgreiches Speichern verschickt IMMER die Freigabemail - dieselbe Sperre wie in
 * noten.freigabe: opt-in über NOTEN_FREIGABE_ENABLED. Dialogansicht und Passwortablehnung
 * schreiben nichts und laufen daher immer.
 */
const freigabeEnabled = () => String(Cypress.env("NOTEN_FREIGABE_ENABLED")).toLowerCase() === "true";
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
			seedBaseline(ctx, student.uid, { note: ctx.gradeNotes[0], freigegeben: false });

			page.visitAndWaitForTable(ctx);
			page.expectFreigabeState(student.uid, "changed");

			page.openFreigabeModal();
			page.expectFreigabeSummaryRow(student.uid, bezeichnung(ctx.gradeNotes[0]));
		});

		it("meldet eine leere Auswahl, wenn nichts freizugeben ist", () => {
			resetNotenState(ctx);

			page.visitAndWaitForTable(ctx);
			page.openFreigabeModal();

			cy.get("[data-cy='freigabe-summary-empty']").should("be.visible");
		});

		it("lehnt ein falsches Passwort ab und lässt den Status unverändert", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student.uid, { note: ctx.gradeNotes[0], freigegeben: false });

			page.visitAndWaitForTable(ctx);
			page.openFreigabeModal();

			page.typeFreigabePasswort("definitely-not-the-password");
			page.submitFreigabe();
			cy.wait("@saveStudentenNoten");

			readLvGesamtnote(ctx, student.uid).then((rowData) => {
				expect(rowData.freigabedatum, "eine abgelehnte Freigabe stempelt nichts").to.be.null;
			});
		});
	});

	describe("Freigabe (verschickt Mail - opt in über NOTEN_FREIGABE_ENABLED)", () => {
		beforeEach(function () {
			if (!freigabeEnabled()) {
				cy.log(
					"Skipped: die Freigabe verschickt die Notenfreigabe-Mail. NOTEN_FREIGABE_ENABLED=true " +
						"nur auf einer Umgebung setzen, auf der das harmlos ist.",
				);
				this.skip();
			}
		});

		it("gibt frei und schaltet die Statusspalte auf ok", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student.uid, { note: ctx.gradeNotes[0], freigegeben: false });

			page.visitAndWaitForTable(ctx);
			page.expectFreigabeState(student.uid, "changed");

			page.openFreigabeModal();
			page.freigeben(freigabePassword());

			page.expectFreigabeState(student.uid, "ok");
			page.expectLvNote(student.uid, bezeichnung(ctx.gradeNotes[0]));
		});

		it("legt mit der Freigabe den ersten Antritt an", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student.uid, { note: ctx.gradeNotes[0], freigegeben: false });

			page.visitAndWaitForTable(ctx);
			page.openFreigabeModal();
			page.freigeben(freigabePassword());

			// upsertErstantritt schreibt die Prüfungszeile; sichtbar wird sie beim nächsten Laden
			page.visitAndWaitForTable(ctx);
			page.expectPruefung(student.uid, "antritt_1", { note: ctx.gradeNotes[0], antritt: 1 });
			page.expectAntrittCount(student.uid, 1);
		});
	});
});
