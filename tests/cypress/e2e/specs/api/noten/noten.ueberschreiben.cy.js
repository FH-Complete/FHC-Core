/**
 * Overwrite-Regeln der Notenvorschlag-Spalte.
 *
 * Drei Regeln: die Editorliste bietet nur lehre-Noten an, die Spalte ist gesperrt, sobald eine
 * Prüfung existiert, und eine Zeugnisnote mit lkt_ueberschreibbar = false sperrt sie ebenfalls.
 * Alle drei stehen in Benotungstool.js UND in Noten::validateNotenvorschlag - der direkte
 * API-Aufruf und der CSV-Import erreichen den Client nie.
 */

import { expectBulkRowError, expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import {
	attemptDate,
	loadNotenContext,
	readLvGesamtnote,
	requireDbReset,
	seedZeugnisnote,
} from "../../../../support/helpers/notenTestData";
import { addPruefung, givenBaseline } from "../../../../support/helpers/notenScenario";
import { notenApi } from "../../../../support/api/notenApi";

describe("Noten API - Notenvorschlag overwrite rules", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;
		});
	});

	const studentFor = (index) => ctx.students[index % ctx.students.length];

	it("refuses a note the editor list never offers", function () {
		if (ctx.notes.nichtLehre === null) {
			this.skip(); // every active note is a lehre note on this instance
		}

		const student = studentFor(0);

		// ohne Erstantritt: sonst greift die Prüfungsregel und die Notenregel bliebe ungeprüft
		givenBaseline(ctx, student, { erstantritt: false });

		notenApi
			.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.notes.nichtLehre)
			.then((response) => expectNotenError(response, "c4noteNichtInLehre"))
			.then(() => readLvGesamtnote(ctx, student.uid))
			.then((row) => {
				expect(String(row.note), "a non-lehre note must not become the LV note").to.eq(
					String(ctx.gradeNotes[0]),
				);
			});
	});

	it("refuses to change the Notenvorschlag once a Prüfung exists", () => {
		const student = studentFor(1);

		givenBaseline(ctx, student);

		addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) }).then((response) => {
			expectNotenSuccess(response, "seed a Prüfung");
		});

		// the grade now belongs to the attempt history; a direct edit bypasses the Antritt rules
		notenApi
			.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[0])
			.then((response) => expectNotenError(response, "c4notenvorschlagGesperrt"))
			.then(() => readLvGesamtnote(ctx, student.uid))
			.then((row) => {
				expect(String(row.note), "the attempt's grade must survive the call").to.eq(
					String(ctx.gradeNotes[1]),
				);
			});
	});

	it("refuses to overwrite a locked Zeugnisnote", function () {
		if (ctx.notes.nichtUeberschreibbar === null) {
			this.skip(); // every active note allows the teacher to overwrite it on this instance
		}

		const student = studentFor(4);

		givenBaseline(ctx, student, { erstantritt: false });
		seedZeugnisnote(ctx, student.uid, ctx.notes.nichtUeberschreibbar);

		notenApi
			.saveNotenvorschlag(ctx.lvId, ctx.semKurzbz, student.uid, ctx.gradeNotes[1])
			.then((response) => expectNotenError(response, "c4zeugnisnoteGesperrt"))
			.then(() => readLvGesamtnote(ctx, student.uid))
			.then((row) => {
				expect(String(row.note), "the LV note must not change").to.eq(String(ctx.gradeNotes[0]));
			});
	});

	// Derselbe Weg über den CSV-Import, den eine Assistenz tatsächlich fährt. Der Bulk-Endpunkt
	// antwortet 200 und meldet die abgelehnte Zeile in data[uid].
	describe("saveNotenvorschlagBulk", () => {
		beforeEach(function () {
			if (ctx.cisConfig.CIS_GESAMTNOTE_PUNKTE) {
				// im Punktemodus leitet der Import die Note aus den Punkten ab und verwirft eine
				// Zeile ohne Punkte, bevor eine dieser Regeln greift
				cy.log("Skipped: CIS_GESAMTNOTE_PUNKTE ist aktiv.");
				this.skip();
			}
		});

		it("refuses a note the editor list never offers", function () {
			if (ctx.notes.nichtLehre === null) this.skip();

			const student = studentFor(2);

			givenBaseline(ctx, student, { erstantritt: false });

			notenApi
				.saveNotenvorschlagBulk(ctx.lvId, ctx.semKurzbz, [
					{ uid: student.uid, note: ctx.notes.nichtLehre, punkte: null },
				])
				.then((response) => {
					const data = expectNotenSuccess(response, "saveNotenvorschlagBulk");
					expectBulkRowError(data, student.uid, "c4noteNichtInLehre");
				})
				.then(() => readLvGesamtnote(ctx, student.uid))
				.then((row) => {
					expect(String(row.note), "a non-lehre note must not become the LV note").to.eq(
						String(ctx.gradeNotes[0]),
					);
				});
		});

		it("refuses to overwrite a locked Zeugnisnote", function () {
			if (ctx.notes.nichtUeberschreibbar === null) this.skip();

			const student = studentFor(5);

			givenBaseline(ctx, student, { erstantritt: false });
			seedZeugnisnote(ctx, student.uid, ctx.notes.nichtUeberschreibbar);

			notenApi
				.saveNotenvorschlagBulk(ctx.lvId, ctx.semKurzbz, [
					{ uid: student.uid, note: ctx.gradeNotes[1], punkte: null },
				])
				.then((response) => {
					const data = expectNotenSuccess(response, "saveNotenvorschlagBulk");
					expectBulkRowError(data, student.uid, "c4zeugnisnoteGesperrt");
				})
				.then(() => readLvGesamtnote(ctx, student.uid))
				.then((row) => {
					expect(String(row.note), "the LV note must not change").to.eq(
						String(ctx.gradeNotes[0]),
					);
				});
		});

		it("refuses to change the Notenvorschlag once a Prüfung exists", () => {
			const student = studentFor(3);

			givenBaseline(ctx, student);

			addPruefung(ctx, student, { note: ctx.gradeNotes[1], datum: attemptDate(ctx, 1) }).then(
				(response) => {
					expectNotenSuccess(response, "seed a Prüfung");
				},
			);

			notenApi
				.saveNotenvorschlagBulk(ctx.lvId, ctx.semKurzbz, [
					{ uid: student.uid, note: ctx.gradeNotes[0], punkte: null },
				])
				.then((response) => {
					const data = expectNotenSuccess(response, "saveNotenvorschlagBulk");
					expectBulkRowError(data, student.uid, "c4notenvorschlagGesperrt");
				})
				.then(() => readLvGesamtnote(ctx, student.uid))
				.then((row) => {
					expect(String(row.note), "the attempt's grade must survive the import").to.eq(
						String(ctx.gradeNotes[1]),
					);
				});
		});
	});
});
