/**
 * A successful Freigabe sends the Freigabe mail, unless CIS_GESAMTNOTE_FREIGABEMAIL is false.
 * The dev instances send every mail to a debug mailbox (MAIL_DEBUG), so the tests run with the mail on.
 * The suite cannot see the mail; it checks the templates and the response.
 */

import { freigabePassword, notenApi, loginAsLektor } from "../../../../support/api/notenApi";
import {
	expectBulkRowAccepted,
	expectBulkRowError,
	expectNotenError,
	expectNotenSuccess,
} from "../../../../support/helpers/notenErrors";
import { requireConfig, requireNotenMode } from "../../../../support/helpers/notenConfig";
import {
	antrittDate,
	baselineBenotungsdatum,
	loadNotenContext,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedBaseline,
	seedPruefung,
} from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	pruefungenOf,
	givenBaseline,
	studentOf,
	readStateViaApi,
	verlaufOf,
} from "../../../../support/helpers/notenScenario";

describe("Noten API - Notenfreigabe", () => {
	let ctx;

	before(() => {
		loadNotenContext().then((loaded) => {
			ctx = loaded;
		});
	});

	beforeEach(() => loginAsLektor());

	describe("Passwortschutz", () => {
		it("lehnt eine Freigabe mit falschem Passwort ab und ändert nichts", function () {
			// without CIS_GESAMTNOTE_FREIGABE_PASSWORT every password passes
			requireConfig(this, ctx, "CIS_GESAMTNOTE_FREIGABE_PASSWORT", true);
			requireDbReset();

			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student, {
				note: ctx.notenScale[0],
				freigegeben: false,
				erstantritt: false,
				benotungsdatum: baselineBenotungsdatum(ctx),
			});

			notenApi
				.saveFreigabe(ctx.lvId, ctx.semKurzbz, "definitely-not-the-password", [student.uid])
				.then((response) => {
					expectNotenError(response, "wrongPassword");
				});

			readLvGesamtnoteViaDb(ctx, student).then((row) => {
				expect(row, "die Zeile bleibt").to.not.be.null;
				expect(row.freigabedatum, "eine abgelehnte Freigabe setzt kein freigabedatum").to.be.null;
			});
		});
	});

	describe("der normale Ablauf", () => {
		beforeEach(() => {
			requireDbReset();
		});

		it("setzt das freigabedatum auf einer geänderten Note", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student, {
				note: ctx.notenScale[0],
				freigegeben: false,
				erstantritt: false,
				benotungsdatum: baselineBenotungsdatum(ctx),
			});

			notenApi.saveFreigabe(ctx.lvId, ctx.semKurzbz, freigabePassword(), [student.uid]).then((response) => {
				const entry = expectNotenSuccess(response, "saveFreigabe")[student.uid];
				expect(entry, `${student.uid} wird als freigegeben gemeldet`).to.exist;
				expect(entry.lvgesamtnote.freigabedatum, "freigabedatum ist gesetzt").to.exist;
				expect(entry.verlauf, "der Verlauf kommt mit der Antwort zurück").to.exist;
				expect(entry.verlauf.pruefungen, "inklusive der Termine").to.be.an("array");
			});

			readStateViaApi(ctx).then((data) => {
				const lvNote = studentOf(data, student.uid);
				expect(lvNote.freigabedatum, "die Note ist jetzt freigegeben").to.exist;
			});

			// the Freigabe used to stop before it wrote
			readLvGesamtnoteViaDb(ctx, student).then((row) => {
				expect(row.freigabedatum, "C1: die Freigabe schreibt freigabedatum").to.not.be.null;
			});
		});

		it("legt mit der Freigabe den ersten Antritt an", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", true);

			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.noten.negativ, freigegeben: false, erstantritt: false });

			notenApi.saveFreigabe(ctx.lvId, ctx.semKurzbz, freigabePassword(), [student.uid]).then((response) => {
				const entry = expectNotenSuccess(response, "saveFreigabe")[student.uid];
				// without the verlauf in the response, the table shows the new Pruefung only after a reload
				expect(entry.verlauf.pruefungen, "der erste Antritt kommt mit der Antwort zurück").to.have.length(1);
			});

			readStateViaApi(ctx).then((data) => {
				const pruefungen = pruefungenOf(data, student.uid);
				expect(pruefungen, "genau ein Termin").to.have.length(1);
				expect(pruefungen[0].antritt_nr, "als Antritt 1").to.eq(1);
			});
		});

		it("legt ohne CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME keinen Termin an", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", false);

			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.noten.negativ, freigegeben: false, erstantritt: false });

			notenApi.saveFreigabe(ctx.lvId, ctx.semKurzbz, freigabePassword(), [student.uid]).then((response) => {
				const entry = expectNotenSuccess(response, "saveFreigabe")[student.uid];
				expect(entry.lvgesamtnote.freigabedatum, "die Freigabe gilt trotzdem").to.exist;
			});

			readStateViaApi(ctx).then((data) => {
				expect(pruefungenOf(data, student.uid), "kein Termin").to.have.length(0);
				expect(verlaufOf(data, student.uid).antrittCount, "die LV-Note zählt als Antritt 1").to.eq(1);
			});
		});

		it("gibt ohne Passwort frei, wenn die Konfiguration keines verlangt", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_FREIGABE_PASSWORT", false);

			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.noten.negativ, freigegeben: false, erstantritt: false });

			notenApi.saveFreigabe(ctx.lvId, ctx.semKurzbz, "", [student.uid]).then((response) => {
				const data = expectNotenSuccess(response, "Freigabe ohne Passwort");
				expect(Object.keys(data), "freigegebene Zeilen").to.include(student.uid);
			});

			readLvGesamtnoteViaDb(ctx, student).then((row) => {
				expect(row.freigabedatum, "freigabedatum").to.not.be.null;
			});
		});

		// A single repeat is not Antritt 1. The Freigabe does not overwrite its Note.
		it("behält die Note einer einzelnen Wiederholung", () => {
			const student = ctx.students[1];
			const g2 = ctx.notenScale.find((n) => String(n) !== String(ctx.noten.negativ));

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.noten.negativ, freigegeben: false, erstantritt: false });
			seedPruefung(ctx, student, { note: g2, datum: antrittDate(ctx, 1), type: "Termin2" });

			notenApi
				.saveFreigabe(ctx.lvId, ctx.semKurzbz, freigabePassword(), [student.uid])
				.then((response) => expectNotenSuccess(response, "saveFreigabe"));

			readStateViaApi(ctx).then((data) => {
				const pruefungen = pruefungenOf(data, student.uid);
				expect(pruefungen, "die Freigabe legt keine Prüfung an").to.have.length(1);
				expect(String(pruefungen[0].note), "die Wiederholung behält ihre Note").to.eq(String(g2));
			});
		});

		// The person who enters a Pruefung owns its date. The Freigabe makes the Note binding and
		// keeps the date.
		it("behält das Datum einer bestehenden Prüfung", () => {
			const student = ctx.students[2];
			const pruefungsdatum = antrittDate(ctx, 2);

			resetNotenState(ctx);
			seedBaseline(ctx, student, {
				note: ctx.noten.negativ,
				freigegeben: false,
				erstantritt: false,
				benotungsdatum: baselineBenotungsdatum(ctx),
			});
			// Antritt 1 with its own date; without it, the Freigabe would create Antritt 1 on the benotungsdatum
			seedPruefung(ctx, student, { note: ctx.noten.negativ, datum: pruefungsdatum, type: "Termin1" });

			notenApi
				.saveFreigabe(ctx.lvId, ctx.semKurzbz, freigabePassword(), [student.uid])
				.then((response) => expectNotenSuccess(response, "saveFreigabe"));

			readStateViaApi(ctx).then((data) => {
				const pruefungen = pruefungenOf(data, student.uid);
				expect(pruefungen, "der Termin bleibt der einzige").to.have.length(1);
				expect(String(pruefungen[0].datum).slice(0, 10), "mit seinem eigenen Datum").to.eq(pruefungsdatum);
			});
		});

		it("ändert nur Zeilen mit einem benotungsdatum nach dem freigabedatum", () => {
			const changed = ctx.students[0];
			const alreadyFreigegeben = ctx.students[1];

			resetNotenState(ctx);

			// changed: benotungsdatum after freigabedatum -> must be re-freigegeben
			seedBaseline(ctx, changed, {
				note: ctx.notenScale[0],
				freigegeben: true,
				freigabedatum: `${ctx.semKurzbz.slice(2, 6)}-01-05 08:00:00`,
				benotungsdatum: `${ctx.semKurzbz.slice(2, 6)}-01-10 08:00:00`,
			});

			// already freigegeben and untouched since -> must be left alone
			seedBaseline(ctx, alreadyFreigegeben, {
				note: ctx.notenScale[0],
				freigegeben: true,
				freigabedatum: `${ctx.semKurzbz.slice(2, 6)}-01-10 08:00:00`,
				benotungsdatum: `${ctx.semKurzbz.slice(2, 6)}-01-10 08:00:00`,
			});

			notenApi
				.saveFreigabe(ctx.lvId, ctx.semKurzbz, freigabePassword(), [changed.uid, alreadyFreigegeben.uid])
				.then((response) => {
					const data = expectNotenSuccess(response, "selektive Freigabe");
					const uids = Object.keys(data);

					expect(uids, "die geänderte Note ist freigegeben").to.include(changed.uid);
					expect(
						uids,
						"an unchanged, already freigegebene note must not be freigegeben again",
					).to.not.include(alreadyFreigegeben.uid);
				});

			readLvGesamtnoteViaDb(ctx, alreadyFreigegeben).then((row) => {
				expect(
					new Date(row.freigabedatum).toISOString().slice(0, 10),
					"the untouched row keeps its original freigabedatum",
				).to.eq(`${ctx.semKurzbz.slice(2, 6)}-01-10`);
			});
		});
	});

	// The mail goes to a debug mailbox, and the suite does not read it. So it checks what the mail needs:
	// both templates have a text, and a Freigabe with the mail on returns a full response.
	describe("Freigabemail", () => {
		beforeEach(function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_FREIGABEMAIL", true);
			requireDbReset();
		});

		it("findet die Vorlage der Freigabemail und das Sancho-Layout", () => {
			const vorlage = ctx.cisConfig.CIS_GESAMTNOTE_FREIGABEMAIL_VORLAGE;

			cy.task("noten:db:readVorlagen", { kurzbz: [vorlage, "Sancho_Mail_Template"] }).then((rows) => {
				const text = (kurzbz) => (rows.find((r) => r.vorlage_kurzbz === kurzbz) || {}).text || "";

				// without a text the mail is empty, and nobody notices
				expect(text(vorlage), `Text der Vorlage ${vorlage}`).to.not.be.empty;
				expect(text(vorlage), "die Vorlage setzt die Notenliste ein").to.include("{studlist}");
				expect(text("Sancho_Mail_Template"), "das Layout setzt den Inhalt ein").to.include("{content}");
			});
		});

		it("gibt mit eingeschalteter Mail frei", () => {
			const student = ctx.students[3];

			resetNotenState(ctx);
			seedBaseline(ctx, student, { note: ctx.noten.negativ, freigegeben: false, erstantritt: false });

			notenApi.saveFreigabe(ctx.lvId, ctx.semKurzbz, freigabePassword(), [student.uid]).then((response) => {
				const entry = expectNotenSuccess(response, "Freigabe mit Mail")[student.uid];
				expect(entry, `${student.uid} ist freigegeben`).to.exist;
			});

			readLvGesamtnoteViaDb(ctx, student).then((row) => {
				expect(row.freigabedatum, "freigabedatum").to.not.be.null;
			});
		});
	});

	// CIS_GESAMTNOTE_FREIGABE_FINAL: no endpoint changes a freigegeben Note. The tests seed the
	// Freigabe, so they need no password.
	describe("endgültige Freigabe", () => {
		beforeEach(function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_FREIGABE_FINAL", true);
			requireDbReset();
		});

		const otherNote = () => ctx.notenScale.find((n) => String(n) !== String(ctx.noten.negativ));

		it("lehnt eine Übernahme nach der Freigabe ab", () => {
			const student = ctx.students[0];

			// freigegeben on purpose: with FINAL the baseline seeds an open Note by default
			givenBaseline(ctx, student, { freigegeben: true });

			notenApi
				.saveLvNote(ctx.lvId, ctx.semKurzbz, student.uid, otherNote())
				.then((response) => expectNotenError(response, "freigabeEndgueltig"));

			readLvGesamtnoteViaDb(ctx, student).then((row) => {
				expect(String(row.note), "die LV-Note bleibt").to.eq(String(ctx.noten.negativ));
			});
		});

		it("lehnt einen neuen Termin nach der Freigabe ab", () => {
			const student = ctx.students[1];

			givenBaseline(ctx, student, { freigegeben: true });

			addPruefung(ctx, student, { note: ctx.noten.negativ, datum: antrittDate(ctx, 1) }).then((response) =>
				expectNotenError(response, "freigabeEndgueltig"),
			);

			readStateViaApi(ctx).then((data) => {
				expect(pruefungenOf(data, student.uid), "nur Antritt 1").to.have.length(1);
			});
		});

		it("lehnt im Import nur die freigegebene Zeile ab", function () {
			// Punkte mode: the Note comes from the Notenschluessel; the row sends only Punkte
			requireNotenMode(this, ctx);
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENIMPORT", true);

			const [freigegeben, open] = ctx.students;

			resetNotenState(ctx);
			seedBaseline(ctx, freigegeben, { freigegeben: true });
			seedBaseline(ctx, open, { freigegeben: false, erstantritt: false });

			notenApi
				.importLvNoten(ctx.lvId, ctx.semKurzbz, [
					{ uid: freigegeben.uid, note: otherNote(), punkte: null },
					{ uid: open.uid, note: otherNote(), punkte: null },
				])
				.then((response) => {
					const data = expectNotenSuccess(response, "importLvNoten");
					expectBulkRowError(data, freigegeben.uid, "freigabeEndgueltig");
					expectBulkRowAccepted(data, open.uid);
				});
		});
	});
});
