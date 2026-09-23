/**
 * saveLvNote: the proposal writes the LV-Note, and the Freigabe state follows.
 *
 * Two timestamps decide the state: benotungsdatum after freigabedatum = changed. The case "changed
 * after the Freigabe" starts from a baseline that is already freigegeben, so this spec needs no LDAP
 * password and sends no Freigabe mail.
 */

import { notenApi, loginAsLektor } from "../../../../support/api/notenApi";
import { expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import { requireConfig, requireRepeat } from "../../../../support/helpers/notenConfig";
import {
	antrittDate,
	baselineBenotungsdatum,
	baselineDate,
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
	studentOf,
	readStateViaApi,
	verlaufOf,
} from "../../../../support/helpers/notenScenario";

describe("Noten API - LV-Note", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((loaded) => {
			ctx = loaded;
		});
	});

	beforeEach(() => loginAsLektor());

	describe("ein Notenvorschlag für einen Studierenden ohne Note", () => {
		it("gibt die gespeicherte Note mit benotungsdatum und ohne freigabedatum zurück", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);

			notenApi.saveLvNote(ctx.lvId, ctx.semKurzbz, student.uid, ctx.notenScale[0]).then((response) => {
				const row = expectNotenSuccess(response, "saveLvNote")[student.uid].lvgesamtnote;
				expect(String(row.note), "gespeicherte Note").to.eq(String(ctx.notenScale[0]));
				expect(row.benotungsdatum, "benotungsdatum wird beim Speichern gesetzt").to.exist;
				expect(row.freigabedatum, "eine neue LV-Note ist nicht freigegeben").to.be.oneOf([null, undefined, ""]);
			});
		});

		// getStudentenNoten uses the unfiltered getter, so it also reads Noten that are not freigegeben.
		// Otherwise the LV-Note and the Freigabe state would be empty after a reload.
		it("meldet die offene Note über getStudentenNoten zurück", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			notenApi.saveLvNote(ctx.lvId, ctx.semKurzbz, student.uid, ctx.notenScale[0]);

			readStateViaApi(ctx).then((data) => {
				const row = studentOf(data, student.uid);
				expect(row, `Zeile von ${student.uid}`).to.exist;
				expect(
					String(row.lv_note),
					"a saved LV-Note that is not freigegeben must be readable again - if this is " +
						"null, getLvGesamtNoten's `freigabedatum < NOW()` filter dropped the row",
				).to.eq(String(ctx.notenScale[0]));
				expect(row.freigabedatum, "noch nicht freigegeben").to.be.oneOf([null, undefined, ""]);
			});
		});
	});

	// `erstantritt: false` is the old data form: a freigegeben LV-Note without a Pruefung row. Only then may
	// the proposal change the LV-Note directly; with a Pruefung the server rejects it (validateLvNote,
	// see overwrite.cy.js). The date comes from the dialog of the proposal. The server uses it as the
	// date of Antritt 1, so the same rules apply as for a Pruefung date.
	describe("das gewählte Benotungsdatum", () => {
		// The LV-Note IS Antritt 1. Without this row the next Pruefung would get "Termin2", and the
		// legacy Pruefungstyp of the whole chain would move by one.
		it("schreibt Antritt 1 mit dem gewählten Tag", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", true);

			const student = ctx.students[1];
			const datum = baselineDate(ctx);

			resetNotenState(ctx);

			notenApi
				.saveLvNote(ctx.lvId, ctx.semKurzbz, student.uid, ctx.notenScale[0], null, datum)
				.then((response) => {
					const { verlauf } = expectNotenSuccess(response, "saveLvNote")[student.uid];
					expect(verlauf, "die Antwort trägt den Verlauf").to.exist;
					expect(verlauf.antrittCount, "die LV-Note ist Antritt 1").to.eq(1);
					expect(verlauf.hasRepeat, "Antritt 1 ist keine Wiederholung").to.be.false;
				})
				.then(() => readStateViaApi(ctx))
				.then((data) => {
					const pruefungen = pruefungenOf(data, student.uid);
					expect(pruefungen, "genau ein Antritt").to.have.length(1);
					expect(String(pruefungen[0].datum).slice(0, 10), "mit dem gewählten Datum").to.eq(datum);
					expect(pruefungen[0].antritt_nr, "als Antritt 1").to.eq(1);
				});
		});

		it("macht die nächste Prüfung zu Antritt 2 statt wieder zu Antritt 1", function () {
			requireRepeat(this, ctx);

			const student = ctx.students[2];

			resetNotenState(ctx);

			notenApi
				.saveLvNote(ctx.lvId, ctx.semKurzbz, student.uid, ctx.noten.negativ, null, baselineDate(ctx))
				.then((response) => expectNotenSuccess(response, "übernehmen"));

			addPruefung(ctx, student, { note: ctx.notenScale[1], datum: antrittDate(ctx, 1) })
				.then((response) => {
					const { pruefung } = expectNotenSuccess(response, "Wiederholung")[student.uid];
					expect(pruefung.pruefungstyp_kurzbz, "die Wiederholung ist nicht Antritt 1").to.not.eq("Termin1");
				})
				.then(() => readStateViaApi(ctx))
				.then((data) => {
					const verlauf = verlaufOf(data, student.uid);
					expect(verlauf.antrittCount, "die Übernahme und die Prüfung sind zwei Antritte").to.eq(2);
				});
		});

		it("schreibt ohne CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME nur die LV-Note", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", false);

			const student = ctx.students[1];

			resetNotenState(ctx);

			notenApi
				.saveLvNote(ctx.lvId, ctx.semKurzbz, student.uid, ctx.noten.negativ, null, baselineDate(ctx))
				.then((response) => {
					const { lvgesamtnote, verlauf } = expectNotenSuccess(response, "saveLvNote")[student.uid];
					expect(String(lvgesamtnote.note), "die LV-Note").to.eq(String(ctx.noten.negativ));
					expect(verlauf.antrittCount, "die LV-Note zählt als Antritt 1").to.eq(1);
				});

			readStateViaApi(ctx).then((data) => {
				expect(pruefungenOf(data, student.uid), "kein Termin").to.have.length(0);
			});
		});

		// Without ERSTANTRITT_BEI_UEBERNAHME the LV-Note stays the implicit Antritt 1. The first Pruefung
		// writes that row and becomes Antritt 2 itself.
		it("legt ohne CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME Antritt 1 mit dem ersten Termin an", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", false);
			requireRepeat(this, ctx);

			const student = ctx.students[2];

			resetNotenState(ctx);
			notenApi
				.saveLvNote(ctx.lvId, ctx.semKurzbz, student.uid, ctx.noten.negativ)
				.then((response) => expectNotenSuccess(response, "übernehmen ohne Erstantritt"));

			addPruefung(ctx, student, { note: ctx.notenScale[1], datum: antrittDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "erster Termin"),
			);

			readStateViaApi(ctx).then((data) => {
				const pruefungen = pruefungenOf(data, student.uid);
				expect(pruefungen, "Antritt 1 und der neue Termin").to.have.length(2);
				expect(String(pruefungen[0].note), "Antritt 1 trägt die LV-Note").to.eq(String(ctx.noten.negativ));
				expect(
					pruefungen.map((p) => p.antritt_nr),
					"Antrittsnummern",
				).to.deep.eq([1, 2]);
				expect(verlaufOf(data, student.uid).antrittCount, "zwei Antritte").to.eq(2);
			});
		});
	});

	// One query reads all LV-Noten of the LV. The result for each student stays the same.
	describe("Lesedaten", () => {
		it("liefert LV-Note und Zeitstempel je Studierendem", () => {
			const [freigegeben, open, withoutNote] = ctx.students;

			resetNotenState(ctx);
			seedBaseline(ctx, freigegeben, { freigegeben: true });
			seedBaseline(ctx, open, { freigegeben: false, erstantritt: false });

			readStateViaApi(ctx).then((data) => {
				const f = studentOf(data, freigegeben.uid);
				expect(f.lv_note, "LV-Note der freigegebenen Zeile").to.exist;
				expect(f.freigabedatum, "freigabedatum der freigegebenen Zeile").to.exist;
				expect(f.benotungsdatum, "benotungsdatum der freigegebenen Zeile").to.exist;

				const o = studentOf(data, open.uid);
				expect(o.lv_note, "LV-Note der offenen Zeile").to.exist;
				expect(o.benotungsdatum, "benotungsdatum der offenen Zeile").to.exist;
				expect(o.freigabedatum, "die offene Zeile ist nicht freigegeben").to.be.oneOf([null, undefined, ""]);

				expect((studentOf(data, withoutNote.uid) || {}).lv_note, "keine LV-Note").to.be.oneOf([
					null,
					undefined,
				]);
			});
		});
	});

	// A single repeat without Antritt 1, as the StV can leave it. The Benotungstool no longer creates
	// this state: its first Pruefung writes Antritt 1.
	describe("eine einzelne Wiederholung", () => {
		const student = () => ctx.students[3];
		// g2: the Note of the repeat, g3: the new LV-Note
		const g2 = () =>
			ctx.noten.positiv ??
			ctx.noten.bestnote ??
			ctx.notenScale.find((n) => String(n) !== String(ctx.noten.negativ));
		const g3 = () => ctx.notenScale.find((n) => String(n) !== String(g2()));

		/** The LV-Note without Antritt 1, and one repeat. Yields the id of the repeat. */
		const givenWiederholung = () => {
			resetNotenState(ctx);
			seedBaseline(ctx, student(), { erstantritt: false });

			return seedPruefung(ctx, student(), { note: g2(), datum: antrittDate(ctx, 1), type: "Termin2" }).then(
				(seeded) => seeded.pruefungId,
			);
		};

		it("sperrt die Übernahme", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_VORSCHLAG_NACH_WIEDERHOLUNG", false);

			let wiederholungId;
			givenWiederholung().then((id) => {
				wiederholungId = id;
			});

			readStateViaApi(ctx).then((data) => {
				expect(verlaufOf(data, student().uid).hasRepeat, "hasRepeat").to.be.true;
				expect(verlaufOf(data, student().uid).lvNoteLocked, "lvNoteLocked").to.be.true;
			});

			notenApi
				.saveLvNote(ctx.lvId, ctx.semKurzbz, student().uid, g3(), null, baselineDate(ctx))
				.then((response) => expectNotenError(response, "c4notenvorschlagGesperrt"));

			readStateViaApi(ctx).then((data) => {
				const pruefung = pruefungenOf(data, student().uid).find(
					(p) => String(p.pruefung_id) === String(wiederholungId),
				);
				expect(String(pruefung.note), "die Wiederholung behält ihre Note").to.eq(String(g2()));
				expect(String(pruefung.datum).slice(0, 10), "die Wiederholung behält ihr Datum").to.eq(
					antrittDate(ctx, 1),
				);
			});
		});

		it("übernimmt die Note trotz Wiederholung, wenn der Schalter an ist", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_VORSCHLAG_NACH_WIEDERHOLUNG", true);

			givenWiederholung();

			readStateViaApi(ctx).then((data) => {
				expect(verlaufOf(data, student().uid).lvNoteLocked, "lvNoteLocked").to.be.false;
			});

			notenApi
				.saveLvNote(ctx.lvId, ctx.semKurzbz, student().uid, g3(), null, baselineDate(ctx))
				.then((response) => expectNotenSuccess(response, "saveLvNote nach einer Wiederholung"));

			readLvGesamtnoteViaDb(ctx, student()).then((row) => {
				expect(String(row.note), "die neue LV-Note").to.eq(String(g3()));
			});
		});
	});

	describe("eine bereits freigegebene Note neu benoten", () => {
		// a final Freigabe forbids exactly this, see freigabe.cy.js
		beforeEach(function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_FREIGABE_FINAL", false);
		});

		it("wechselt den Status von freigegeben auf geändert", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student, {
				note: ctx.notenScale[0],
				freigegeben: true,
				erstantritt: false,
				benotungsdatum: baselineBenotungsdatum(ctx),
			});

			// baseline: benotungsdatum == freigabedatum -> freigegeben, not changed
			readStateViaApi(ctx).then((data) => {
				const lvNote = studentOf(data, student.uid);
				expect(lvNote.lv_note, "die freigegebene Note ist sichtbar").to.exist;
				expect(
					new Date(lvNote.benotungsdatum) > new Date(lvNote.freigabedatum),
					"baseline must not already look changed",
				).to.be.false;
			});

			notenApi.saveLvNote(ctx.lvId, ctx.semKurzbz, student.uid, ctx.notenScale[1]).then((response) => {
				expectNotenSuccess(response, "eine neue LV-Note nach der Freigabe");
			});

			readStateViaApi(ctx).then((data) => {
				const lvNote = studentOf(data, student.uid);

				expect(String(lvNote.lv_note), "die neue LV-Note ist gespeichert").to.eq(String(ctx.notenScale[1]));
				expect(lvNote.freigabedatum, "das alte freigabedatum bleibt").to.exist;
				expect(
					new Date(lvNote.benotungsdatum) > new Date(lvNote.freigabedatum),
					"benotungsdatum must now be newer than freigabedatum (state: changed)",
				).to.be.true;
			});
		});

		it("überschreibt die Note, statt eine zweite Zeile anzulegen", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student, {
				note: ctx.notenScale[0],
				freigegeben: true,
				erstantritt: false,
			});

			notenApi.saveLvNote(ctx.lvId, ctx.semKurzbz, student.uid, ctx.notenScale[1]);

			readLvGesamtnoteViaDb(ctx, student).then((row) => {
				expect(row, "die einzige lvgesamtnote-Zeile").to.not.be.null;
				expect(String(row.note)).to.eq(String(ctx.notenScale[1]));
			});
		});
	});
});
