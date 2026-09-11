/**
 * Notenvorschlag - LV-Note schreiben und der resultierende Status (P1, case 11).
 *
 * Status aus zwei Zeitstempeln (notenRules.js::checkFreigabe). Der "changed nach Freigabe"-Fall
 * wird über eine bereits freigegebene Baseline erzeugt, damit dieser Spec ohne LDAP-Passwort und
 * ohne Freigabemail auskommt.
 */

import { notenApi } from "../../../../support/api/notenApi";
import { expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import {
	attemptDate,
	baselineBenotungsdatum,
	baselineDate,
	loadNotenContext,
	readLvGesamtnote,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	attemptsOfStudent,
	lvNoteOf,
	readState,
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

	const readRow = (uid) => readLvGesamtnote(ctx, uid);

	describe("saving a Notenvorschlag on a student without a grade", () => {
		it("returns the stored note with a benotungsdatum and no freigabedatum", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);

			notenApi
				.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[0])
				.then((response) => {
					const data = expectNotenSuccess(response, "saveNotenvorschlag");
					expect(data, "saveNotenvorschlag returns [ lvgesamtnote ]").to.be.an("array").and.not
						.be.empty;

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
		it("reports the offen note back through getStudentenNoten", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			notenApi.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[0]);

			readState(ctx).then((data) => {
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
		it("writes attempt 1 with the chosen day", () => {
			const student = ctx.students[1];
			const datum = baselineDate(ctx);

			resetNotenState(ctx);

			notenApi
				.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[0], null, datum)
				.then((response) => {
					const row = expectNotenSuccess(response, "saveNotenvorschlag")[0];
					expect(row.verlauf, "die Antwort trägt den Verlauf").to.exist;
					expect(row.verlauf.antrittCount, "die LV-Note ist Antritt 1").to.eq(1);
				})
				.then(() => readState(ctx))
				.then((data) => {
					const attempts = attemptsOfStudent(data, student.uid);
					expect(attempts, "genau ein Antritt").to.have.length(1);
					expect(String(attempts[0].datum).slice(0, 10), "mit dem gewählten Datum").to.eq(datum);
					expect(attempts[0].antritt_nr, "als Antritt 1").to.eq(1);
				});
		});

		it("makes the next exam attempt 2, not attempt 1 again", () => {
			const student = ctx.students[2];

			resetNotenState(ctx);

			notenApi
				.saveNotenvorschlag(
					ctx.lvId, ctx.semKurzbz, student.uid, ctx.notes.negativ, null, baselineDate(ctx),
				)
				.then((response) => expectNotenSuccess(response, "übernehmen"));

			addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) })
				.then((response) => {
					const [saved] = expectNotenSuccess(response, "Wiederholung");
					expect(saved.pruefungstyp_kurzbz, "die Wiederholung ist nicht Antritt 1").to.not.eq(
						"Termin1",
					);
				})
				.then(() => readState(ctx))
				.then((data) => {
					const verlauf = verlaufOfStudent(data, student.uid);
					expect(verlauf.antrittCount, "die Übernahme und die Prüfung sind zwei Antritte").to.eq(2);
				});
		});

	});

	describe("re-grading an already freigegebene note", () => {
		it("moves the state from freigegeben to changed", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student.uid, {
				note: ctx.gradeNotes[0],
				freigegeben: true,
				erstantritt: false,
				benotungsdatum: baselineBenotungsdatum(ctx),
			});

			// baseline: benotungsdatum == freigabedatum -> freigegeben, not changed
			readState(ctx).then((data) => {
				const grades = lvNoteOf(data, student.uid);
				expect(grades.note_lv, "seeded freigegebene note is visible").to.exist;
				expect(
					new Date(grades.benotungsdatum) > new Date(grades.freigabedatum),
					"baseline must not already look changed",
				).to.be.false;
			});

			notenApi
				.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[1])
				.then((response) => {
					expectNotenSuccess(response, "re-grade after Freigabe");
				});

			readState(ctx).then((data) => {
				const grades = lvNoteOf(data, student.uid);

				expect(String(grades.note_lv), "the new grade is stored").to.eq(
					String(ctx.gradeNotes[1]),
				);
				expect(grades.freigabedatum, "the old Freigabe timestamp is kept").to.exist;
				expect(
					new Date(grades.benotungsdatum) > new Date(grades.freigabedatum),
					"benotungsdatum must now be newer than freigabedatum (state: changed)",
				).to.be.true;
			});
		});

		it("overwrites the grade rather than adding a second row", () => {
			const student = ctx.students[1];

			resetNotenState(ctx);
			seedBaseline(ctx, student.uid, {
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
