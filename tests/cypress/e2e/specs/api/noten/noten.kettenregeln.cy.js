/**
 * When a grade closes the attempt chain.
 *
 *   negative              -> repeat allowed
 *   positive, not final   -> only with CIS_GESAMTNOTE_NOTENVERBESSERUNG
 *   final                 -> chain closed, always
 *
 * tbl_note.positiv alone is not the rule: 'entschuldigt' and 'Teilgenommen' carry it too. Only the
 * grade of a Termin that uses an attempt decides.
 */

import { notenApi } from "../../../../support/api/notenApi";
import {
	expectBulkRowAccepted,
	expectBulkRowError,
	expectNotenError,
	expectNotenSuccess,
} from "../../../../support/helpers/notenErrors";
import {
	attemptDate,
	loadNotenContext,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	attemptsOfStudent,
	editPruefung,
	givenBaseline,
	readStateViaApi,
	verlaufOfStudent,
} from "../../../../support/helpers/notenScenario";
import { requireConfig, requireWiederholung, skipIf } from "../../../../support/helpers/notenConfig";

describe("Noten API - wann eine Note die Kette schliesst", () => {
	let ctx;

	before(() => {
		loadNotenContext().then((context) => {
			ctx = context;
			cy.log(`NOTEN_ABSCHLIESSEND = ${JSON.stringify(ctx.cisConfig.NOTEN_ABSCHLIESSEND)}`);
			cy.log(`NOTENVERBESSERUNG = ${ctx.cisConfig.CIS_GESAMTNOTE_NOTENVERBESSERUNG}`);
			cy.log(`VERBESSERUNG_BESSERE_GEWINNT = ${ctx.cisConfig.CIS_GESAMTNOTE_VERBESSERUNG_BESSERE_GEWINNT}`);
			cy.log(`notes.negativ=${ctx.notes.negativ} positiv=${ctx.notes.positiv} bestnote=${ctx.notes.bestnote}`);
		});
	});

	const studentFor = (index) => {
		const student = ctx.students[index];
		expect(student, `student at index ${index}`).to.exist;
		return student;
	};

	beforeEach(() => requireDbReset());

	it("lässt die Kette nach einer negativen Note offen", function () {
		requireWiederholung(this, ctx);

		const student = studentFor(0);

		givenBaseline(ctx, student, { note: ctx.notes.negativ });

		addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, 1) })
			.then((response) => expectNotenSuccess(response, "Wiederholung nach negativer Note"))
			.then(() => readStateViaApi(ctx))
			.then((data) => {
				const verlauf = verlaufOfStudent(data, student.uid);
				expect(verlauf.bestanden, "eine negative Note ist kein Abschluss").to.be.false;
			});
	});

	it("schliesst die Kette nach einer abschliessenden Note", function () {
		skipIf(this, ctx.notes.bestnote === null, "Übersprungen: keine Note aus NOTEN_ABSCHLIESSEND in der 1-5-Skala.");

		const student = studentFor(1);

		givenBaseline(ctx, student, { note: ctx.notes.bestnote });

		readStateViaApi(ctx)
			.then((data) => {
				const verlauf = verlaufOfStudent(data, student.uid);
				expect(verlauf.bestanden, "die Bestnote schliesst ab").to.be.true;
				expect(verlauf.canAdd, "kein weiterer Termin nach der Bestnote").to.be.false;
			})
			.then(() => addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, 1) }))
			.then((response) => expectNotenError(response, "pruefungNachBestandenerNote"));
	});

	// The top grade ALWAYS ends the chain, even when the improvement feature is enabled.
	it("schliesst die Kette nach der Bestnote auch bei erlaubter Verbesserung", function () {
		requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENVERBESSERUNG", true);
		skipIf(this, ctx.notes.bestnote === null, "Übersprungen: keine Note aus NOTEN_ABSCHLIESSEND in der 1-5-Skala.");

		const student = studentFor(2);

		givenBaseline(ctx, student, { note: ctx.notes.bestnote });

		addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, 1) }).then((response) =>
			expectNotenError(response, "pruefungNachBestandenerNote"),
		);
	});

	describe("eine positive Note ausserhalb NOTEN_ABSCHLIESSEND", () => {
		beforeEach(function () {
			skipIf(
				this,
				ctx.notes.positiv === null,
				"Übersprungen: keine positive Note ausserhalb NOTEN_ABSCHLIESSEND.",
			);
		});

		it("schliesst die Kette ohne Notenverbesserung", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENVERBESSERUNG", false);

			const student = studentFor(3);

			givenBaseline(ctx, student, { note: ctx.notes.positiv });

			addPruefung(ctx, student, { note: ctx.notes.positiv, datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenError(response, "pruefungNachBestandenerNote"),
			);
		});

		it("lässt mit Notenverbesserung einen weiteren Antritt zu", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENVERBESSERUNG", true);

			const student = studentFor(3);

			givenBaseline(ctx, student, { note: ctx.notes.positiv });

			addPruefung(ctx, student, { note: ctx.notes.positiv, datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "Verbesserung nach positiver Note"),
			);
		});

		// An attempt to improve a grade must not lower the course grade if configured to do so.
		it("behält bei einer schlechteren Verbesserung die bessere LV-Note", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENVERBESSERUNG", true);
			requireConfig(this, ctx, "CIS_GESAMTNOTE_VERBESSERUNG_BESSERE_GEWINNT", true);

			const student = studentFor(4);

			givenBaseline(ctx, student, { note: ctx.notes.positiv });

			// The improvement is less significant than the first attempt
			addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, 1) })
				.then((response) => expectNotenSuccess(response, "schlechtere Verbesserung"))
				.then(() => readLvGesamtnoteViaDb(ctx, student.uid))
				.then((row) => {
					expect(String(row.note), "die LV-Note behält die bessere Note").to.eq(String(ctx.notes.positiv));
				});

			// The exam itself reflects the grade the student actually received
			readStateViaApi(ctx).then((data) => {
				const attempts = attemptsOfStudent(data, student.uid);
				const latest = attempts[attempts.length - 1];
				expect(String(latest.note), "der Termin trägt die echte Note").to.eq(String(ctx.notes.negativ));
			});
		});

		it("schreibt bei einer schlechteren Verbesserung die letzte Note", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENVERBESSERUNG", true);
			requireConfig(this, ctx, "CIS_GESAMTNOTE_VERBESSERUNG_BESSERE_GEWINNT", false);

			const student = studentFor(4);

			givenBaseline(ctx, student, { note: ctx.notes.positiv });

			addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, 1) })
				.then((response) => expectNotenSuccess(response, "schlechtere Verbesserung"))
				.then(() => readLvGesamtnoteViaDb(ctx, student.uid))
				.then((row) => {
					expect(String(row.note), "die LV-Note folgt dem letzten Antritt").to.eq(String(ctx.notes.negativ));
				});
		});
	});

	// Only one ungraded appointment per course. One graded appointment following the open appointment is still permitted.
	describe("nur ein Termin ohne Note", () => {
		beforeEach(function () {
			requireWiederholung(this, ctx);
		});

		const open = () => ctx.notes.nochNichtEingetragen;

		const openTermine = (data, student) =>
			attemptsOfStudent(data, student.uid).filter((p) => String(p.note) === String(open()));

		it("lehnt einen zweiten Termin ohne Note ab", () => {
			const student = studentFor(0);

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: open(), datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "erster offener Termin"),
			);
			addPruefung(ctx, student, { note: open(), datum: attemptDate(ctx, 2) }).then((response) =>
				expectNotenError(response, "pruefungOhneErgebnis"),
			);

			readStateViaApi(ctx).then((data) => {
				expect(openTermine(data, student), "offene Termine").to.have.length(1);
			});
		});

		it("lehnt im Sammeldialog je Zeile einen zweiten offenen Termin ab", () => {
			const students = [studentFor(0), studentFor(1)];
			const bulkRows = students.map((s) => ({ uid: s.uid, lehreinheit_id: s.lehreinheit_id }));

			resetNotenState(ctx);
			students.forEach((s) => seedBaseline(ctx, s));

			notenApi.createPruefungen(bulkRows, attemptDate(ctx, 1), ctx.lvId, ctx.semKurzbz).then((response) => {
				const data = expectNotenSuccess(response, "erster Sammeltermin ohne Note");
				students.forEach((s) => expectBulkRowAccepted(data, s.uid));
			});

			notenApi.createPruefungen(bulkRows, attemptDate(ctx, 2), ctx.lvId, ctx.semKurzbz).then((response) => {
				const data = expectNotenSuccess(response, "zweiter Sammeltermin ohne Note");
				students.forEach((s) => expectBulkRowError(data, s.uid, "pruefungOhneErgebnis"));
			});
		});

		it("lehnt eine Änderung auf 'Noch nicht eingetragen' neben einem offenen Termin ab", () => {
			const student = studentFor(2);
			let latest;

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: open(), datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "offener Termin"),
			);

			// User's decision: An appointment with a grade following the open appointment is still allowed.
			addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, 2) }).then((response) => {
				[latest] = expectNotenSuccess(response, "Termin mit Note nach dem offenen Termin");
			});

			cy.then(() =>
				editPruefung(ctx, student, {
					pruefungId: latest.pruefung_id,
					note: open(),
					datum: attemptDate(ctx, 2),
				}),
			).then((response) => expectNotenError(response, "pruefungOhneErgebnis"));
		});
	});
});
