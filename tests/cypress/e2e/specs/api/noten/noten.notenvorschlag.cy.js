/**
 * Notenvorschlag - LV-Note schreiben und der resultierende Status (P1, case 11).
 *
 * Status aus zwei Zeitstempeln (notenRules.js::checkFreigabe). Der "changed nach Freigabe"-Fall
 * wird über eine bereits freigegebene Baseline erzeugt, damit dieser Spec ohne LDAP-Passwort und
 * ohne Freigabemail auskommt.
 */

import { notenApi } from "../../../../support/api/notenApi";
import { expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import { requireKonfiguration, requireWiederholung } from "../../../../support/helpers/notenConfig";
import {
	attemptDate,
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
	attemptsOfStudent,
	lvNoteOf,
	readStateViaApi,
	verlaufOfStudent,
} from "../../../../support/helpers/notenScenario";

describe("Noten API - Notenvorschlag", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;
		});
	});

	const readRow = (uid) => readLvGesamtnoteViaDb(ctx, uid);

	describe("ein Notenvorschlag für einen Studierenden ohne Note", () => {
		it("gibt die gespeicherte Note mit benotungsdatum und ohne freigabedatum zurück", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);

			notenApi.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[0]).then((response) => {
				const data = expectNotenSuccess(response, "saveNotenvorschlag");
				expect(data, "saveNotenvorschlag returns [ lvgesamtnote ]").to.be.an("array").and.not.be.empty;

				const row = data[0];
				expect(String(row.note), "stored note").to.eq(String(ctx.gradeNotes[0]));
				expect(row.benotungsdatum, "benotungsdatum is stamped on save").to.exist;
				expect(row.freigabedatum, "a fresh Notenvorschlag is not freigegeben").to.be.oneOf([
					null,
					undefined,
					"",
				]);
			});
		});

		// getStudentenNoten liest seit dem Wechsel auf den ungefilterten Getter auch noch nicht
		// freigegebene Noten - sonst sind LV-Note und Freigabestatus nach einem Reload leer.
		it("meldet die offene Note über getStudentenNoten zurück", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			notenApi.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[0]);

			readStateViaApi(ctx).then((data) => {
				const grades = lvNoteOf(data, student.uid);
				expect(grades, `grades entry for ${student.uid}`).to.exist;
				expect(
					String(grades.note_lv),
					"a saved (not yet freigegebene) Notenvorschlag must be readable again - if this is " +
						"null, getLvGesamtNoten's `freigabedatum < NOW()` filter dropped the row",
				).to.eq(String(ctx.gradeNotes[0]));
				expect(grades.freigabedatum, "still offen").to.be.oneOf([null, undefined, ""]);
			});
		});
	});

	// `erstantritt: false` ist die Altdatenform: freigegebene LV-Note ohne Prüfungszeile. Nur dort
	// ist ein direktes Umbenoten erlaubt - existiert eine Prüfung, lehnt der Server ab
	// (validateNotenvorschlag, siehe noten.ueberschreiben).
	// Das Datum kommt aus dem Übernehmen-Dialog. Die Freigabe macht daraus das Datum von Antritt 1,
	// daher gelten dieselben Grenzen wie für einen Prüfungstermin.
	describe("das gewählte Benotungsdatum", () => {
		// Die LV-Note IST Antritt 1. Ohne diese Zeile bekäme die nächste Prüfung Termin2, und der
		// Legacy-Typ der ganzen Kette verschiebt sich um eine Stelle.
		it("schreibt Antritt 1 mit dem gewählten Tag", function () {
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", true);

			const student = ctx.students[1];
			const datum = baselineDate(ctx);

			resetNotenState(ctx);

			notenApi
				.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[0], null, datum)
				.then((response) => {
					const row = expectNotenSuccess(response, "saveNotenvorschlag")[0];
					expect(row.verlauf, "die Antwort trägt den Verlauf").to.exist;
					expect(row.verlauf.antrittCount, "die LV-Note ist Antritt 1").to.eq(1);
					expect(row.verlauf.hatWiederholung, "Antritt 1 ist keine Wiederholung").to.be.false;
				})
				.then(() => readStateViaApi(ctx))
				.then((data) => {
					const attempts = attemptsOfStudent(data, student.uid);
					expect(attempts, "genau ein Antritt").to.have.length(1);
					expect(String(attempts[0].datum).slice(0, 10), "mit dem gewählten Datum").to.eq(datum);
					expect(attempts[0].antritt_nr, "als Antritt 1").to.eq(1);
				});
		});

		it("macht die nächste Prüfung zu Antritt 2 statt wieder zu Antritt 1", function () {
			requireWiederholung(this, ctx);

			const student = ctx.students[2];

			resetNotenState(ctx);

			notenApi
				.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.notes.negativ, null, baselineDate(ctx))
				.then((response) => expectNotenSuccess(response, "übernehmen"));

			addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) })
				.then((response) => {
					const [saved] = expectNotenSuccess(response, "Wiederholung");
					expect(saved.pruefungstyp_kurzbz, "die Wiederholung ist nicht Antritt 1").to.not.eq("Termin1");
				})
				.then(() => readStateViaApi(ctx))
				.then((data) => {
					const verlauf = verlaufOfStudent(data, student.uid);
					expect(verlauf.antrittCount, "die Übernahme und die Prüfung sind zwei Antritte").to.eq(2);
				});
		});

		it("schreibt ohne CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME nur die LV-Note", function () {
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", false);

			const student = ctx.students[1];

			resetNotenState(ctx);

			notenApi
				.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.notes.negativ, null, baselineDate(ctx))
				.then((response) => {
					const row = expectNotenSuccess(response, "saveNotenvorschlag")[0];
					expect(String(row.note), "die LV-Note").to.eq(String(ctx.notes.negativ));
					expect(row.verlauf.antrittCount, "die LV-Note zählt als Antritt 1").to.eq(1);
				});

			readStateViaApi(ctx).then((data) => {
				expect(attemptsOfStudent(data, student.uid), "kein Termin").to.have.length(0);
			});
		});

		// Ohne Erstantritt bei der Übernahme bleibt die LV-Note Antritt 1. Der erste Termin schreibt ihn nach
		// und wird selbst Antritt 2.
		it("legt ohne CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME Antritt 1 mit dem ersten Termin an", function () {
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME", false);
			requireWiederholung(this, ctx);

			const student = ctx.students[2];

			resetNotenState(ctx);
			notenApi
				.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.notes.negativ)
				.then((response) => expectNotenSuccess(response, "übernehmen ohne Erstantritt"));

			addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "erster Termin"),
			);

			readStateViaApi(ctx).then((data) => {
				const attempts = attemptsOfStudent(data, student.uid);
				expect(attempts, "Antritt 1 und der neue Termin").to.have.length(2);
				expect(String(attempts[0].note), "Antritt 1 trägt die LV-Note").to.eq(String(ctx.notes.negativ));
				expect(
					attempts.map((p) => p.antritt_nr),
					"Antrittsnummern",
				).to.deep.eq([1, 2]);
				expect(verlaufOfStudent(data, student.uid).antrittCount, "zwei Antritte").to.eq(2);
			});
		});
	});

	// W5: Eine Abfrage liest alle LV-Noten der LV. Das Ergebnis je Studierendem bleibt gleich.
	describe("Lesedaten", () => {
		it("liefert LV-Note und Zeitstempel je Studierendem", () => {
			const [freigegeben, offen, ohneNote] = ctx.students;

			resetNotenState(ctx);
			seedBaseline(ctx, freigegeben, { freigegeben: true });
			seedBaseline(ctx, offen, { freigegeben: false });

			readStateViaApi(ctx).then((data) => {
				const f = lvNoteOf(data, freigegeben.uid);
				expect(f.note_lv, "LV-Note der freigegebenen Zeile").to.exist;
				expect(f.freigabedatum, "freigabedatum der freigegebenen Zeile").to.exist;
				expect(f.benotungsdatum, "benotungsdatum der freigegebenen Zeile").to.exist;

				const o = lvNoteOf(data, offen.uid);
				expect(o.note_lv, "LV-Note der offenen Zeile").to.exist;
				expect(o.benotungsdatum, "benotungsdatum der offenen Zeile").to.exist;
				expect(o.freigabedatum, "die offene Zeile ist nicht freigegeben").to.be.oneOf([null, undefined, ""]);

				expect((lvNoteOf(data, ohneNote.uid) || {}).note_lv, "keine LV-Note").to.be.oneOf([null, undefined]);
			});
		});
	});

	// W7: Eine einzelne Wiederholung ohne Antritt 1, wie sie die Studierendenverwaltung hinterlassen kann. Dieses
	// Werkzeug erzeugt den Zustand nicht mehr: sein erster Termin schreibt Antritt 1 nach.
	describe("eine einzelne Wiederholung", () => {
		it("sperrt die Übernahme", () => {
			const student = ctx.students[3];
			const g2 =
				ctx.notes.positiv ??
				ctx.notes.bestnote ??
				ctx.gradeNotes.find((n) => String(n) !== String(ctx.notes.negativ));
			const g3 = ctx.gradeNotes.find((n) => String(n) !== String(g2));

			resetNotenState(ctx);
			seedBaseline(ctx, student, { erstantritt: false });

			let wiederholungId;
			seedPruefung(ctx, student, { note: g2, datum: attemptDate(ctx, 1), typ: "Termin2" }).then((seeded) => {
				wiederholungId = seeded.pruefungId;
			});

			readStateViaApi(ctx).then((data) => {
				expect(verlaufOfStudent(data, student.uid).hatWiederholung, "hatWiederholung").to.be.true;
			});

			notenApi
				.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, g3, null, baselineDate(ctx))
				.then((response) => expectNotenError(response, "c4notenvorschlagGesperrt"));

			readStateViaApi(ctx).then((data) => {
				const pruefung = attemptsOfStudent(data, student.uid).find(
					(p) => String(p.pruefung_id) === String(wiederholungId),
				);
				expect(String(pruefung.note), "die Wiederholung behält ihre Note").to.eq(String(g2));
				expect(String(pruefung.datum).slice(0, 10), "die Wiederholung behält ihr Datum").to.eq(
					attemptDate(ctx, 1),
				);
			});
		});
	});

	describe("eine bereits freigegebene Note neu benoten", () => {
		// eine endgültige Freigabe verbietet genau das, siehe noten.freigabe
		beforeEach(function () {
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_FREIGABE_FINAL", false);
		});

		it("wechselt den Status von freigegeben auf geändert", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student, {
				note: ctx.gradeNotes[0],
				freigegeben: true,
				erstantritt: false,
				benotungsdatum: baselineBenotungsdatum(ctx),
			});

			// baseline: benotungsdatum == freigabedatum -> freigegeben, not changed
			readStateViaApi(ctx).then((data) => {
				const grades = lvNoteOf(data, student.uid);
				expect(grades.note_lv, "seeded freigegebene note is visible").to.exist;
				expect(
					new Date(grades.benotungsdatum) > new Date(grades.freigabedatum),
					"baseline must not already look changed",
				).to.be.false;
			});

			notenApi.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[1]).then((response) => {
				expectNotenSuccess(response, "re-grade after Freigabe");
			});

			readStateViaApi(ctx).then((data) => {
				const grades = lvNoteOf(data, student.uid);

				expect(String(grades.note_lv), "the new grade is stored").to.eq(String(ctx.gradeNotes[1]));
				expect(grades.freigabedatum, "the old Freigabe timestamp is kept").to.exist;
				expect(
					new Date(grades.benotungsdatum) > new Date(grades.freigabedatum),
					"benotungsdatum must now be newer than freigabedatum (state: changed)",
				).to.be.true;
			});
		});

		it("überschreibt die Note, statt eine zweite Zeile anzulegen", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student, {
				note: ctx.gradeNotes[0],
				freigegeben: true,
				erstantritt: false,
			});

			notenApi.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[1]);

			readRow(student.uid).then((row) => {
				expect(row, "the single lvgesamtnote row").to.not.be.null;
				expect(String(row.note)).to.eq(String(ctx.gradeNotes[1]));
			});
		});
	});
});
