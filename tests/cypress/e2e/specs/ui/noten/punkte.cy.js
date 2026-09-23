import { benotungstoolPage as page } from "../../../../support/pages/benotungstool.po";
import { requireConfig, requirePunkteMode, requireRepeat } from "../../../../support/helpers/notenConfig";
import { pruefungenOf, readStateViaApi } from "../../../../support/helpers/notenScenario";
import {
	antrittDate,
	loadNotenContext,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedBaseline,
	seedPruefung,
} from "../../../../support/helpers/notenTestData";
import { notenApi, loginAsLektor } from "../../../../support/api/notenApi";

/**
 * The Punkte mode (CIS_GESAMTNOTE_PUNKTE) on the page.
 *
 * The Punkte column and the Punkte fields of both dialogs exist only with this flag. The Note then
 * comes from the Notenschluessel; nobody selects it. No other spec covers these fields, so the whole
 * mode has its own file.
 *
 * The spec never hardcodes the Notenschluessel: it gets the expected Note from getNoteByPunkte at
 * runtime, so it works with the Notenschluessel of each instance.
 */
describe("Benotungstool UI - Punktemodus", () => {
	let ctx;
	// Punkte values; the test gets their Noten at runtime
	const TOP = 100;
	const MIDDLE = 70;
	let noteTop;
	let noteMiddle;

	before(() => {
		requireDbReset();
		loadNotenContext().then((loaded) => {
			ctx = loaded;
			if (!ctx.cisConfig.CIS_GESAMTNOTE_PUNKTE) return;

			notenApi.getNoteByPunkte(ctx.lvId, ctx.semKurzbz, TOP).then((r) => {
				noteTop = r.body.data;
				expect(noteTop, `${TOP} Punkte müssen eine Note ergeben`).to.not.be.null;
			});
			notenApi.getNoteByPunkte(ctx.lvId, ctx.semKurzbz, MIDDLE).then((r) => {
				noteMiddle = r.body.data;
				expect(noteMiddle, `${MIDDLE} Punkte müssen eine Note ergeben`).to.not.be.null;
				expect(String(noteMiddle), "die beiden Punktewerte müssen verschiedene Noten liefern").to.not.eq(
					String(noteTop),
				);
			});
		});
	});

	beforeEach(() => loginAsLektor());

	beforeEach(function () {
		requirePunkteMode(this, ctx);
	});

	describe("Punktespalte in der Tabelle", () => {
		it("zeigt die Punktespalte und sperrt dafür die Notenauswahl", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			page.visitAndWaitForTable(ctx);

			page.getPunkteCell(student.uid).should("exist");
			// the Note comes from the Punkte; nobody may select it directly
			page.expectProposalLocked(student.uid);
		});

		it("schreibt Note und Punkte, wenn der Vorschlag übernommen wird", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			page.visitAndWaitForTable(ctx);

			page.setPunkteInCell(student.uid, MIDDLE);
			page.applyProposal(student.uid);

			page.expectLvNote(student.uid, page.bezeichnungOf(ctx, noteMiddle));
			page.expectFreigabeState(student.uid, "changed");

			readLvGesamtnoteViaDb(ctx, student).then((row) => {
				expect(String(row.note), "abgeleitete Note").to.eq(String(noteMiddle));
				expect(Number(row.punkte), "die Punkte werden mitgeschrieben").to.eq(MIDDLE);
			});
		});

		// Antritt 1 and the LV-Note are the same result, so the column stays open for it.
		// Only a repeat takes Note and Punkte away from the LV-Note.
		it("sperrt die Punktespalte ab der ersten Wiederholung", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_VORSCHLAG_NACH_WIEDERHOLUNG", false);

			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.notenScale[0], freigegeben: true });
			seedPruefung(ctx, student, {
				note: ctx.notenScale[0],
				datum: antrittDate(ctx, 1),
				type: "Termin2",
			});

			page.visitAndWaitForTable(ctx);

			page.expectPunkteCellLocked(student.uid);
		});
	});

	describe("Prüfungsdialog", () => {
		it("bietet ein Punktefeld statt der Notenauswahl", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.noten.negativ, freigegeben: true });

			page.visitAndWaitForTable(ctx);
			page.getPruefungAddButton(student.uid, "antritt_2").click();
			page.getPruefungModal().should("be.visible");

			cy.get("[data-cy='pruefung-punkte']").should("be.visible");
			page.expectNoteFieldLocked("pruefung-note");
		});

		it("legt den Termin mit der aus den Punkten abgeleiteten Note an", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.noten.negativ, freigegeben: true });

			page.visitAndWaitForTable(ctx);
			page.getPruefungAddButton(student.uid, "antritt_2").click();
			page.getPruefungModal().should("be.visible");

			page.setDate("pruefung-datum", page.toDDMMYYYY(antrittDate(ctx, 1)));
			page.setPruefungPunkte(TOP);

			cy.get("[data-cy='pruefung-submit']").click();
			cy.wait("@savePruefung").its("response.statusCode").should("eq", 200);
			page.getPruefungModal().should("not.be.visible");

			page.expectPruefung(student.uid, "antritt_2", { note: noteTop, antritt: 2 });
			page.expectLvNote(student.uid, page.bezeichnungOf(ctx, noteTop));
		});
	});

	describe("Sammelanlage", () => {
		// the "Neue Prüfung" dialog sends the Punkte; the server derives the Note from them
		it("legt den Termin mit der aus den Punkten abgeleiteten Note an", function () {
			requireRepeat(this, ctx);

			const [a, b] = [ctx.students[2], ctx.students[3]];

			resetNotenState(ctx);
			seedBaseline(ctx, a, { note: ctx.noten.negativ, freigegeben: true });
			seedBaseline(ctx, b, { note: ctx.noten.negativ, freigegeben: true });

			page.visitAndWaitForTable(ctx);
			page.createPruefungen({
				uids: [a.uid, b.uid],
				punkte: MIDDLE,
				datum: page.toDDMMYYYY(antrittDate(ctx, 1)),
			});

			[a, b].forEach((student) =>
				page.expectPruefung(student.uid, "antritt_2", { note: noteMiddle, antritt: 2 }),
			);
		});
	});

	// The Excel list writes decimals with a comma or with a dot. Both give the same number.
	describe("Import mit Punkten", () => {
		const PUNKTE = 89.5;

		[
			["Dezimalkomma", "89,5"],
			["Dezimalpunkt", "89.5"],
		].forEach(([notation, input]) => {
			it(`liest Punkte mit ${notation} im Prüfungsimport`, function () {
				requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNGSIMPORT", true);
				requireRepeat(this, ctx);

				const student = ctx.students[4];
				const datum = page.importDate(antrittDate(ctx, 1), ctx.cisConfig.CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT);

				resetNotenState(ctx);
				seedBaseline(ctx, student, { note: ctx.noten.negativ, freigegeben: true });
				page.visitAndWaitForTable(ctx);

				page.importPruefungen([[student.uid, datum, input]]);

				cy.get("@importPruefungen").its("request.body.pruefungen.0.punkte").should("eq", PUNKTE);

				readStateViaApi(ctx).then((data) => {
					const newPruefung = pruefungenOf(data, student.uid)[1];
					expect(newPruefung, "der importierte Termin").to.exist;
					expect(Number(newPruefung.punkte), "die Punkte des Termins").to.eq(PUNKTE);
				});
			});

			it(`liest Punkte mit ${notation} im Notenimport`, function () {
				requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENIMPORT", true);

				const student = ctx.students[5];

				resetNotenState(ctx);
				page.visitAndWaitForTable(ctx);

				page.importLvNoten([[student.uid, input]]);

				cy.get("@importLvNoten").its("request.body.lv_noten.0.punkte").should("eq", PUNKTE);

				readLvGesamtnoteViaDb(ctx, student).then((row) => {
					expect(row, "die importierte LV-Note").to.not.be.null;
					expect(Number(row.punkte), "die Punkte der LV-Note").to.eq(PUNKTE);
				});
			});
		});
	});
});
