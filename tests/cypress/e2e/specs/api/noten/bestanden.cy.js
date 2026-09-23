/**
 * When a Note closes the Antritt chain ("bestanden").
 *
 *   negative              -> a repeat is allowed
 *   positive, not final   -> a repeat only with CIS_GESAMTNOTE_NOTENVERBESSERUNG
 *   final                 -> the chain is closed, always
 *
 * tbl_note.positiv alone is not the rule: 'entschuldigt' and 'Teilgenommen' have it too. Only the
 * Note of a Pruefung that uses an Antritt decides.
 */

import { notenApi, loginAsLektor } from "../../../../support/api/notenApi";
import {
	expectBulkRowAccepted,
	expectBulkRowError,
	expectNotenError,
	expectNotenSuccess,
} from "../../../../support/helpers/notenErrors";
import {
	antrittDate,
	loadNotenContext,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	pruefungenOf,
	editPruefung,
	givenBaseline,
	readStateViaApi,
	verlaufOf,
} from "../../../../support/helpers/notenScenario";
import { requireConfig, requireRepeat, skipIf } from "../../../../support/helpers/notenConfig";

describe("Noten API - wann eine Note die Kette schliesst", () => {
	let ctx;

	before(() => {
		loadNotenContext().then((loaded) => {
			ctx = loaded;
			cy.log(`NOTEN_ABSCHLIESSEND = ${JSON.stringify(ctx.cisConfig.NOTEN_ABSCHLIESSEND)}`);
			cy.log(`NOTENVERBESSERUNG = ${ctx.cisConfig.CIS_GESAMTNOTE_NOTENVERBESSERUNG}`);
			cy.log(`VERBESSERUNG_BESSERE_GEWINNT = ${ctx.cisConfig.CIS_GESAMTNOTE_VERBESSERUNG_BESSERE_GEWINNT}`);
			cy.log(`noten.negativ=${ctx.noten.negativ} positiv=${ctx.noten.positiv} bestnote=${ctx.noten.bestnote}`);
		});
	});

	beforeEach(() => loginAsLektor());

	beforeEach(() => requireDbReset());

	it("lässt die Kette nach einer negativen Note offen", function () {
		requireRepeat(this, ctx);

		const student = ctx.students[0];

		givenBaseline(ctx, student, { note: ctx.noten.negativ });

		addPruefung(ctx, student, { note: ctx.noten.negativ, datum: antrittDate(ctx, 1) })
			.then((response) => expectNotenSuccess(response, "Wiederholung nach negativer Note"))
			.then(() => readStateViaApi(ctx))
			.then((data) => {
				const verlauf = verlaufOf(data, student.uid);
				expect(verlauf.bestanden, "eine negative Note ist kein Abschluss").to.be.false;
			});
	});

	it("schliesst die Kette nach einer abschliessenden Note", function () {
		skipIf(this, ctx.noten.bestnote === null, "Übersprungen: keine Note aus NOTEN_ABSCHLIESSEND in der 1-5-Skala.");

		const student = ctx.students[1];

		givenBaseline(ctx, student, { note: ctx.noten.bestnote });

		readStateViaApi(ctx)
			.then((data) => {
				const verlauf = verlaufOf(data, student.uid);
				expect(verlauf.bestanden, "die Bestnote schliesst ab").to.be.true;
				expect(verlauf.canAdd, "kein weiterer Termin nach der Bestnote").to.be.false;
			})
			.then(() => addPruefung(ctx, student, { note: ctx.noten.negativ, datum: antrittDate(ctx, 1) }))
			.then((response) => expectNotenError(response, "pruefungNachBestandenerNote"));
	});

	// The bestnote ALWAYS closes the chain, also with CIS_GESAMTNOTE_NOTENVERBESSERUNG.
	it("schliesst die Kette nach der Bestnote auch bei erlaubter Verbesserung", function () {
		requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENVERBESSERUNG", true);
		skipIf(this, ctx.noten.bestnote === null, "Übersprungen: keine Note aus NOTEN_ABSCHLIESSEND in der 1-5-Skala.");

		const student = ctx.students[2];

		givenBaseline(ctx, student, { note: ctx.noten.bestnote });

		addPruefung(ctx, student, { note: ctx.noten.negativ, datum: antrittDate(ctx, 1) }).then((response) =>
			expectNotenError(response, "pruefungNachBestandenerNote"),
		);
	});

	describe("eine positive Note ausserhalb NOTEN_ABSCHLIESSEND", () => {
		beforeEach(function () {
			skipIf(
				this,
				ctx.noten.positiv === null,
				"Übersprungen: keine positive Note ausserhalb NOTEN_ABSCHLIESSEND.",
			);
		});

		it("schliesst die Kette ohne Notenverbesserung", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENVERBESSERUNG", false);

			const student = ctx.students[3];

			givenBaseline(ctx, student, { note: ctx.noten.positiv });

			addPruefung(ctx, student, { note: ctx.noten.positiv, datum: antrittDate(ctx, 1) }).then((response) =>
				expectNotenError(response, "pruefungNachBestandenerNote"),
			);
		});

		it("lässt mit Notenverbesserung einen weiteren Antritt zu", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENVERBESSERUNG", true);

			const student = ctx.students[3];

			givenBaseline(ctx, student, { note: ctx.noten.positiv });

			addPruefung(ctx, student, { note: ctx.noten.positiv, datum: antrittDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "Verbesserung nach positiver Note"),
			);
		});

		// With VERBESSERUNG_BESSERE_GEWINNT a worse repeat does not lower the LV-Note.
		it("behält bei einer schlechteren Verbesserung die bessere LV-Note", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENVERBESSERUNG", true);
			requireConfig(this, ctx, "CIS_GESAMTNOTE_VERBESSERUNG_BESSERE_GEWINNT", true);

			const student = ctx.students[4];

			givenBaseline(ctx, student, { note: ctx.noten.positiv });

			// the repeat is worse than Antritt 1
			addPruefung(ctx, student, { note: ctx.noten.negativ, datum: antrittDate(ctx, 1) })
				.then((response) => expectNotenSuccess(response, "schlechtere Verbesserung"))
				.then(() => readLvGesamtnoteViaDb(ctx, student))
				.then((row) => {
					expect(String(row.note), "die LV-Note behält die bessere Note").to.eq(String(ctx.noten.positiv));
				});

			// the Pruefung itself keeps the Note that the student got
			readStateViaApi(ctx).then((data) => {
				const pruefungen = pruefungenOf(data, student.uid);
				const latest = pruefungen[pruefungen.length - 1];
				expect(String(latest.note), "der Termin trägt die echte Note").to.eq(String(ctx.noten.negativ));
			});
		});

		it("schreibt bei einer schlechteren Verbesserung die letzte Note", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENVERBESSERUNG", true);
			requireConfig(this, ctx, "CIS_GESAMTNOTE_VERBESSERUNG_BESSERE_GEWINNT", false);

			const student = ctx.students[4];

			givenBaseline(ctx, student, { note: ctx.noten.positiv });

			addPruefung(ctx, student, { note: ctx.noten.negativ, datum: antrittDate(ctx, 1) })
				.then((response) => expectNotenSuccess(response, "schlechtere Verbesserung"))
				.then(() => readLvGesamtnoteViaDb(ctx, student))
				.then((row) => {
					expect(String(row.note), "die LV-Note folgt dem letzten Antritt").to.eq(String(ctx.noten.negativ));
				});
		});
	});

	// Only one Pruefung without a Note per LV. A Pruefung with a Note after it stays allowed.
	describe("nur ein Termin ohne Note", () => {
		beforeEach(function () {
			requireRepeat(this, ctx);
		});

		const open = () => ctx.noten.nochNichtEingetragen;

		const openPruefungen = (data, student) =>
			pruefungenOf(data, student.uid).filter((p) => String(p.note) === String(open()));

		it("lehnt einen zweiten Termin ohne Note ab", () => {
			const student = ctx.students[0];

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: open(), datum: antrittDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "erster offener Termin"),
			);
			addPruefung(ctx, student, { note: open(), datum: antrittDate(ctx, 2) }).then((response) =>
				expectNotenError(response, "pruefungOhneErgebnis"),
			);

			readStateViaApi(ctx).then((data) => {
				expect(openPruefungen(data, student), "offene Termine").to.have.length(1);
			});
		});

		it("lehnt im Sammeldialog je Zeile einen zweiten offenen Termin ab", () => {
			const students = [ctx.students[0], ctx.students[1]];
			const bulkRows = students.map((s) => ({ uid: s.uid, lehreinheit_id: s.lehreinheit_id }));

			resetNotenState(ctx);
			students.forEach((s) => seedBaseline(ctx, s));

			notenApi
				.createPruefungen(ctx.lvId, ctx.semKurzbz, bulkRows, { datum: antrittDate(ctx, 1) })
				.then((response) => {
					const data = expectNotenSuccess(response, "erster Sammeltermin ohne Note");
					students.forEach((s) => expectBulkRowAccepted(data, s.uid));
				});

			notenApi
				.createPruefungen(ctx.lvId, ctx.semKurzbz, bulkRows, { datum: antrittDate(ctx, 2) })
				.then((response) => {
					const data = expectNotenSuccess(response, "zweiter Sammeltermin ohne Note");
					students.forEach((s) => expectBulkRowError(data, s.uid, "pruefungOhneErgebnis"));
				});
		});

		it("lehnt eine Änderung auf 'Noch nicht eingetragen' neben einem offenen Termin ab", () => {
			const student = ctx.students[2];
			let latest;

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: open(), datum: antrittDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "offener Termin"),
			);

			// decision of the product owner: a Pruefung with a Note after the open one stays allowed
			addPruefung(ctx, student, { note: ctx.noten.negativ, datum: antrittDate(ctx, 2) }).then((response) => {
				latest = expectNotenSuccess(response, "Termin mit Note nach dem offenen Termin")[student.uid].pruefung;
			});

			cy.then(() =>
				editPruefung(ctx, student, {
					pruefungId: latest.pruefung_id,
					note: open(),
					datum: antrittDate(ctx, 2),
				}),
			).then((response) => expectNotenError(response, "pruefungOhneErgebnis"));
		});
	});
});
