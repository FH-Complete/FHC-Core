/**
 * Examination Regulations §1 - Taking Exams.
 *
 * The rules apply in the following order: A (Maximum) -> B (Chronological) -> C (Occurrence Limit).
 * Each test establishes a state in which exactly one of these can trigger; otherwise, the earlier one takes precedence.
 *
 * The count is based on the GRADE: entschuldigt, “Noch nicht eingetragen” are
 * never counted. Attempt 1 is a real exam (seeded by givenBaseline).
 */

import { notenApi } from "../../../../support/api/notenApi";
import { expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import {
	requireKommissionellerAntritt,
	requireConfig,
	requireWiederholung,
	skipIf,
} from "../../../../support/helpers/notenConfig";
import {
	attemptDate,
	baselineDate,
	loadNotenContext,
	requireDbReset,
	seedPruefung,
	seedZeugnisnote,
	shiftDate,
} from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	attemptsOfStudent,
	countingAttemptsOfStudent,
	editPruefung,
	givenBaseline,
	readStateViaApi,
	verlaufOfStudent,
} from "../../../../support/helpers/notenScenario";

describe("Noten API - Prüfungsantritte (Prüfungsordnung §1)", () => {
	let ctx;

	before(() => {
		requireDbReset();
		loadNotenContext().then((context) => {
			ctx = context;

			cy.log(
				`LV ${ctx.lvId} / ${ctx.semKurzbz} | maxAntritte=${ctx.maxAntritte} | ` +
					`entschuldigt=${ctx.notes.entschuldigt}`,
			);

			expect(ctx.maxAntritte, "needs room for at least one retake beyond the first Antritt").to.be.greaterThan(1);
		});
	});

	// one student per test, so a leaked row cannot reach the next scenario
	const studentFor = (index) => ctx.students[index % ctx.students.length];

	/** Skip if the last Antritt in the chain is not the commission link. */
	const skipUnlessLastKommissionell = (test) =>
		skipIf(
			test,
			ctx.cisConfig.CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT !== ctx.maxAntritte,
			"Übersprungen: der letzte Antritt ist nicht kommissionell.",
		);

	/** Adds counting attempts until the cap is reached. Baseline already provides Antritt 1. */
	const fillToCap = (student, firstIndex = 1) => {
		for (let i = 0; i < ctx.maxAntritte - 1; i += 1) {
			addPruefung(ctx, student, {
				note: ctx.notes.negativ,
				datum: attemptDate(ctx, firstIndex + i),
			}).then((response) => {
				expectNotenSuccess(response, `attempt ${i + 2} of ${ctx.maxAntritte}`);
			});
		}
		return attemptDate(ctx, firstIndex + ctx.maxAntritte - 1);
	};

	// The configuration refers to the special notes only by their labels. The API responds with the
	// resolved primary key; on demo data, this is set to 14 by default. If the resolution fails,
	// all rules implicitly apply to the incorrect note.
	it("löst die Sondernoten über ihre Bezeichnung aus tbl_note auf", () => {
		notenApi.getCisConfig().then((response) => {
			const config = expectNotenSuccess(response, "getCisConfig");

			expect(String(config.NOTE_ENTSCHULDIGT)).to.eq(String(ctx.notes.entschuldigt));
			expect(config.NOTEN_OHNE_ANTRITT.map(String)).to.include.members([
				String(ctx.notes.entschuldigt),
				String(ctx.notes.nochNichtEingetragen),
			]);
			expect(
				Object.keys(config.NOTEN_OCCURANCE_LIMIT_MAP).map(String),
				"the occurrence limit must be keyed on the resolved PK",
			).to.include(String(ctx.notes.entschuldigt));
		});
	});

	describe("Regel A - die Höchstzahl der Prüfungsantritte", () => {
		it("lehnt einen Antritt ab, sobald das konfigurierte Maximum erreicht ist", function () {
			// ohne Anlage der kommissionellen Prüfung endet die Kette früher, siehe den Test zu kommPruefNichtErlaubt
			requireConfig(this, ctx, "CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF", true);

			const student = studentFor(0);

			givenBaseline(ctx, student);

			const nextDate = fillToCap(student);

			addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: nextDate }).then((response) => {
				expectNotenError(response, "maxAntritteReached");
			});

			// and nothing was written for the rejected attempt
			readStateViaApi(ctx).then((data) => {
				const dates = attemptsOfStudent(data, student.uid).map((p) => String(p.datum).slice(0, 10));
				expect(dates, "no row may carry the rejected date").to.not.include(nextDate);

				expect(
					countingAttemptsOfStudent(data, student.uid),
					"exactly the possible counting rows",
				).to.have.length(ctx.maxAntritte);
			});
		});
	});

	describe("Regel B - die Antritte folgen der Zeitordnung", () => {
		beforeEach(function () {
			requireWiederholung(this, ctx);
		});

		// "Noch nicht eingetragen" belegt ein Datum ohne zu zählen, damit Regel A nicht maskiert
		const givenOpenAttemptOn = (student, datum) => {
			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.notes.nochNichtEingetragen, datum }).then((response) => {
				expectNotenSuccess(response, "seed an open (uncounted) attempt");
			});
		};

		it("lehnt einen neuen Antritt vor einem bestehenden ab", () => {
			const student = studentFor(1);

			givenOpenAttemptOn(student, attemptDate(ctx, 2));

			addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: attemptDate(ctx, 1) }).then((response) => {
				expectNotenError(response, "pruefungDatumBeforeExisting");
			});
		});

		it("nimmt einen neuen Antritt nach allen bestehenden an", () => {
			const student = studentFor(1);

			givenOpenAttemptOn(student, attemptDate(ctx, 2));

			addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: attemptDate(ctx, 3) }).then((response) => {
				expectNotenSuccess(response, "attempt strictly after the existing one");
			});
		});
	});

	describe("'entschuldigt' gilt nur einmal", () => {
		beforeEach(function () {
			requireWiederholung(this, ctx);
		});

		it("lehnt einen zweiten entschuldigten Antritt ab", () => {
			const student = studentFor(2);

			givenBaseline(ctx, student);

			addPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "first entschuldigt attempt"),
			);

			addPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum: attemptDate(ctx, 2) }).then((response) =>
				expectNotenError(response, "noteOccuranceLimitReached"),
			);

			readStateViaApi(ctx).then((data) => {
				const excused = attemptsOfStudent(data, student.uid).filter(
					(p) => String(p.note) === String(ctx.notes.entschuldigt),
				);
				expect(excused, "exactly one excused attempt may exist").to.have.length(1);
			});
		});
	});

	describe("'entschuldigt' verbraucht keinen Antritt", () => {
		beforeEach(function () {
			requireWiederholung(this, ctx);
		});

		it("nimmt nach einem entschuldigten Antritt weiter eine echte Note an", () => {
			const student = studentFor(0);

			givenBaseline(ctx, student);

			addPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "excused attempt"),
			);

			// excused must not count, so this real grade is still within the cap
			addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: attemptDate(ctx, 2) }).then((response) =>
				expectNotenSuccess(
					response,
					"a real grade after an excused attempt (excused must not count towards the cap)",
				),
			);

			readStateViaApi(ctx).then((data) => {
				expect(
					verlaufOfStudent(data, student.uid).antrittCount,
					"baseline + the real grade, the excused one not counted",
				).to.eq(2);
			});
		});
	});

	describe("die kommissionelle Prüfung ist der letzte Antritt", () => {
		// §17, Abs 1: The second retake is kommissionell. The server derives the type from the
		// position and writes it to `pruefungstyp_kurzbz` because the student administration
		// system still reads this column.
		it("schreibt den letzten Antritt als kommissionelle Prüfung", function () {
			requireKommissionellerAntritt(this, ctx);
			skipUnlessLastKommissionell(this);

			const student = studentFor(3);

			givenBaseline(ctx, student);

			// Fill up to the second-to-last step
			for (let i = 1; i < ctx.maxAntritte - 1; i += 1) {
				addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, i) }).then((response) =>
					expectNotenSuccess(response, `Antritt ${i + 1}`),
				);
			}

			addPruefung(ctx, student, {
				note: ctx.gradeNotes[0],
				datum: attemptDate(ctx, ctx.maxAntritte),
			}).then((response) => {
				const [saved] = expectNotenSuccess(response, "letzter Antritt");
				expect(saved.pruefungstyp_kurzbz, "der letzte Antritt ist kommissionell").to.eq(
					ctx.cisConfig.PRUEFUNG_TYP_KOMMISSIONELL,
				);
			});

			readStateViaApi(ctx).then((data) => {
				const verlauf = verlaufOfStudent(data, student.uid);
				expect(verlauf.antrittCount, "die kommissionelle zählt als Antritt").to.eq(ctx.maxAntritte);
				expect(verlauf.canAdd, "danach ist kein Antritt mehr möglich").to.be.false;
			});
		});
		
		it("verweigert den letzten Antritt, wenn das Tool ihn nicht anlegen darf", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF", false);
			skipUnlessLastKommissionell(this);

			const student = studentFor(4);

			givenBaseline(ctx, student);

			// Fill up to the second-to-last step
			for (let i = 1; i < ctx.maxAntritte - 1; i += 1) {
				addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, i) }).then((response) =>
					expectNotenSuccess(response, `Antritt ${i + 1}`),
				);
			}

			readStateViaApi(ctx).then((data) => {
				const verlauf = verlaufOfStudent(data, student.uid);
				expect(verlauf.kommPruefGesperrt, "der Verlauf nennt den Grund").to.be.true;
				expect(verlauf.canAdd, "und lässt keinen Antritt mehr zu").to.be.false;
			});

			addPruefung(ctx, student, {
				note: ctx.gradeNotes[0],
				datum: attemptDate(ctx, ctx.maxAntritte),
			}).then((response) => expectNotenError(response, "kommPruefNichtErlaubt"));
		});

		// Even a kommissionelle pruefung without a graded score completes the chain. Otherwise,
		// it would be possible to schedule another attempt after a committee exam that has not yet been graded.
		it("sperrt weitere Antritte auch bei einer kommPruef ohne zählende Note", () => {
			const student = studentFor(1);

			givenBaseline(ctx, student);

			seedPruefung(ctx, student, {
				note: ctx.notes.nochNichtEingetragen,
				datum: attemptDate(ctx, 1),
				type: "kommPruef",
			});

			readStateViaApi(ctx).then((data) => {
				const verlauf = verlaufOfStudent(data, student.uid);
				expect(verlauf.terminal, "die Kette ist geschlossen").to.be.true;
				expect(verlauf.canAdd, "kein weiterer Antritt möglich").to.be.false;
			});

			addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: attemptDate(ctx, 2) }).then((response) =>
				expectNotenError(response, "maxAntritteReached"),
			);
		});
	});

	describe("'Noch nicht eingetragen' verbraucht keinen Antritt", () => {
		beforeEach(function () {
			requireWiederholung(this, ctx);
		});

		it("zählt einen offenen Termin nicht als Antritt", () => {
			const student = studentFor(3);

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.notes.nochNichtEingetragen, datum: attemptDate(ctx, 1) }).then(
				(response) => expectNotenSuccess(response, "offener Termin"),
			);

			readStateViaApi(ctx).then((data) => {
				const open = attemptsOfStudent(data, student.uid).find(
					(p) => String(p.note) === String(ctx.notes.nochNichtEingetragen),
				);
				expect(open, "der offene Termin").to.exist;
				expect(open.zaehlt, "der offene Termin verbraucht keinen Antritt").to.be.false;

				const verlauf = verlaufOfStudent(data, student.uid);
				expect(verlauf.antrittCount, "nur Antritt 1").to.eq(1);
				expect(verlauf.canAdd, "ein weiterer Antritt bleibt möglich").to.be.true;
			});
		});
	});

	describe("Anrechnung - gar keine Prüfungen", () => {
		it("lehnt einen Antritt bei angerechneter Zeugnisnote ab", function () {
			const angerechnet = (ctx.cisConfig.NOTEN_ANRECHNUNG || [])[0];
			skipIf(this, angerechnet === undefined, "Übersprungen: NOTEN_ANRECHNUNG löst keine Note auf.");

			const student = studentFor(4);

			givenBaseline(ctx, student);
			seedZeugnisnote(ctx, student.uid, angerechnet);

			readStateViaApi(ctx).then((data) => {
				const verlauf = verlaufOfStudent(data, student.uid);
				expect(verlauf.angerechnet, "the Verlauf marks the Anrechnung").to.be.true;
				expect(verlauf.canAdd, "and blocks further attempts").to.be.false;
			});

			addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenError(response, "c4angerechnetKeinePruefung"),
			);
		});
	});

	describe("Schutz beim Bearbeiten", () => {
		beforeEach(function () {
			requireWiederholung(this, ctx);
		});

		/** Antritt 1 < entschuldigt < actual grade, returns the excused row (neighbors on both sides). */
		const givenThreeAttempts = (student) => {
			givenBaseline(ctx, student);

			addPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum: attemptDate(ctx, 1) });
			addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: attemptDate(ctx, 2) });

			return readStateViaApi(ctx).then((data) => {
				const excused = attemptsOfStudent(data, student.uid).find(
					(p) => String(p.note) === String(ctx.notes.entschuldigt),
				);
				expect(excused, "excused attempt to edit").to.exist;
				return excused;
			});
		};

		it("lehnt eine Notenänderung nach einem späteren Antritt ab", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETEREM_TERMIN", true);

			const student = studentFor(1);

			givenThreeAttempts(student).then((excused) => {
				editPruefung(ctx, student, {
					pruefungId: excused.pruefung_id,
					note: ctx.gradeNotes[1], // different grade -> locked
					datum: attemptDate(ctx, 1),
				}).then((response) => expectNotenError(response, "pruefungNoteLocked"));
			});
		});

		it("erlaubt weiter eine reine Datumskorrektur zwischen den Nachbarantritten", () => {
			const student = studentFor(1);

			givenThreeAttempts(student).then((excused) => {
				// same note -> the lock does not apply; date stays strictly inside (baseline, attempt2)
				editPruefung(ctx, student, {
					pruefungId: excused.pruefung_id,
					note: excused.note,
					datum: shiftDate(attemptDate(ctx, 1), 3),
				})
					.then((response) => expectNotenSuccess(response, "date-only correction inside the bounds"))
					.then(() => readStateViaApi(ctx))
					.then((data) => {
						const moved = attemptsOfStudent(data, student.uid).find(
							(p) => p.pruefung_id === excused.pruefung_id,
						);
						expect(moved, "the addressed row still exists").to.exist;
						expect(String(moved.datum).slice(0, 10), "and it is the one that moved").to.eq(
							shiftDate(attemptDate(ctx, 1), 3),
						);
					});
			});
		});

		it("lehnt ein Datum am oder vor dem vorherigen Antritt ab", () => {
			const student = studentFor(2);

			givenThreeAttempts(student).then((excused) => {
				editPruefung(ctx, student, {
					pruefungId: excused.pruefung_id,
					note: excused.note,
					datum: baselineDate(ctx), // == the Antritt-1 date -> not strictly after
				}).then((response) => expectNotenError(response, "pruefungDatumOutOfRange"));
			});
		});
	});
});
