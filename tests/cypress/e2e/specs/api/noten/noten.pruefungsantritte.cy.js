/**
 * Prüfungsordnung §1 - Prüfungsantritte.
 *
 * Die Regeln greifen in der Reihenfolge A (Maximum) -> B (Chronologie) -> C (Vorkommenslimit).
 * Jeder Test baut einen Zustand, in dem genau eine davon feuern kann, sonst maskiert die frühere.
 *
 * Gezählt wird über die NOTE: entschuldigt, "Noch nicht eingetragen" und "Nicht beurteilt" zählen
 * nie mit. Antritt 1 ist eine echte Prüfung (von givenBaseline geseedet).
 */

import { notenApi } from "../../../../support/api/notenApi";
import { expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import {
	attemptDate,
	baselineDate,
	loadNotenContext,
	requireDbReset,
	seedPruefung,
	shiftDate,
} from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	attemptsOfStudent,
	countingAttemptsOfStudent,
	editPruefung,
	givenBaseline,
	readState,
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

			expect(
				ctx.maxAntritte,
				"needs room for at least one retake beyond the first Antritt",
			).to.be.greaterThan(1);
		});
	});

	// one student per test, so a leaked row cannot reach the next scenario
	const studentFor = (index) => ctx.students[index % ctx.students.length];

	/**
	 * Der letzte Antritt ist kommissionell. Darf das Tool ihn nicht anlegen, endet die Kette einen
	 * Antritt früher, und die Grenze meldet eine andere Phrase. Beides kommt aus der Konfiguration.
	 */
	const kommPruefAnlegbar = () => ctx.cisConfig.CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF !== false;
	const anlegbareAntritte = () => (kommPruefAnlegbar() ? ctx.maxAntritte : ctx.maxAntritte - 1);
	const grenzPhrase = () => (kommPruefAnlegbar() ? "maxAntritteReached" : "kommPruefNichtErlaubt");
	// Ob der letzte Antritt kommissionell ist, steht in der Konfiguration, nicht im Test.
	const letzteRolle = () => {
		const ab = ctx.cisConfig.CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT ?? null;
		return ab !== null && ctx.maxAntritte >= ab ? "kommissionell" : "pruefung";
	};

	/** Adds counting attempts until the cap is reached. Baseline already provides Antritt 1. */
	const fillToCap = (student, firstIndex = 1) => {
		for (let i = 0; i < anlegbareAntritte() - 1; i += 1) {
			addPruefung(ctx, student, {
				note: ctx.notes.negativ,
				datum: attemptDate(ctx, firstIndex + i),
			}).then((response) => {
				expectNotenSuccess(response, `attempt ${i + 2} of ${anlegbareAntritte()}`);
			});
		}
		return attemptDate(ctx, firstIndex + anlegbareAntritte() - 1);
	};

	// Die Konfiguration nennt die Sondernoten nur mit ihrer Bezeichnung. Die API antwortet mit dem
	// aufgelösten Primärschlüssel, auf Demodaten ist entschuldigt die 14. Scheitert die Auflösung,
	// greifen alle Regeln stillschweigend auf der falschen Note.
	it("resolves the special notes from tbl_note by their Bezeichnung", () => {
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

	describe("Rule A - maximum number of Prüfungsantritte", () => {
		it("rejects an attempt once the configured maximum is reached", () => {
			const student = studentFor(0);

			givenBaseline(ctx, student);

			const nextDate = fillToCap(student);

			addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: nextDate }).then((response) => {
				expectNotenError(response, grenzPhrase());
			});

			// and nothing was written for the rejected attempt
			readState(ctx).then((data) => {
				const dates = attemptsOfStudent(data, student.uid).map((p) => String(p.datum).slice(0, 10));
				expect(dates, "no row may carry the rejected date").to.not.include(nextDate);

				expect(countingAttemptsOfStudent(data, student.uid), "exactly the possible counting rows")
					.to.have.length(anlegbareAntritte());
			});
		});
	});

	describe("Rule B - attempts are taken in chronological order", () => {
		// "Noch nicht eingetragen" belegt ein Datum ohne zu zählen, damit Regel A nicht maskiert
		const givenOpenAttemptOn = (student, datum) => {
			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.notes.nochNichtEingetragen, datum }).then((response) => {
				expectNotenSuccess(response, "seed an open (uncounted) attempt");
			});
		};

		it("rejects a new attempt dated before an existing one", () => {
			const student = studentFor(1);

			givenOpenAttemptOn(student, attemptDate(ctx, 2));

			addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: attemptDate(ctx, 1) }).then(
				(response) => {
					expectNotenError(response, "pruefungDatumBeforeExisting");
				},
			);
		});

		it("accepts a new attempt dated after every existing one", () => {
			const student = studentFor(1);

			givenOpenAttemptOn(student, attemptDate(ctx, 2));

			addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: attemptDate(ctx, 3) }).then(
				(response) => {
					expectNotenSuccess(response, "attempt strictly after the existing one");
				},
			);
		});
	});

	describe("Rule C - 'entschuldigt' may be assigned only once", () => {
		it("rejects a second entschuldigt attempt", () => {
			const student = studentFor(2);

			givenBaseline(ctx, student);

			addPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum: attemptDate(ctx, 1) }).then(
				(response) => expectNotenSuccess(response, "first entschuldigt attempt"),
			);

			addPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum: attemptDate(ctx, 2) }).then(
				(response) => expectNotenError(response, "noteOccuranceLimitReached"),
			);

			readState(ctx).then((data) => {
				const excused = attemptsOfStudent(data, student.uid).filter(
					(p) => String(p.note) === String(ctx.notes.entschuldigt),
				);
				expect(excused, "exactly one excused attempt may exist").to.have.length(1);
			});
		});
	});

	describe("entschuldigt does not consume an attempt", () => {
		it("still accepts a real grade after an excused attempt", () => {
			const student = studentFor(0);

			givenBaseline(ctx, student);

			addPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum: attemptDate(ctx, 1) }).then(
				(response) => expectNotenSuccess(response, "excused attempt"),
			);

			// excused must not count, so this real grade is still within the cap
			addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: attemptDate(ctx, 2) }).then(
				(response) =>
					expectNotenSuccess(
						response,
						"a real grade after an excused attempt (excused must not count towards the cap)",
					),
			);

			readState(ctx).then((data) => {
				expect(
					verlaufOfStudent(data, student.uid).antrittCount,
					"baseline + the real grade, the excused one not counted",
				).to.eq(2);
			});
		});
	});

	describe("die kommissionelle Prüfung ist der letzte Antritt", () => {
		// §17 Abs 1: die zweite Wiederholung ist kommissionell. Der Server leitet den Typ aus der
		// Position ab und schreibt ihn in pruefungstyp_kurzbz, weil die Studierendenverwaltung
		// diese Spalte weiterhin liest.
		it("schreibt den letzten Antritt nach seiner konfigurierten Rolle", function () {
			if (!kommPruefAnlegbar()) {
				Cypress.log({ name: "skip", message: "Skipped: das Tool darf keine kommissionelle Prüfung anlegen." });
				this.skip();
			}
			if (letzteRolle() !== "kommissionell") {
				Cypress.log({ name: "skip", message: `Übersprungen: die Kette endet mit "${letzteRolle()}".` });
				this.skip();
			}

			const student = studentFor(3);

			givenBaseline(ctx, student);

			// bis zum vorletzten Antritt auffüllen
			for (let i = 1; i < ctx.maxAntritte - 1; i += 1) {
				addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, i) }).then(
					(response) => expectNotenSuccess(response, `Antritt ${i + 1}`),
				);
			}

			addPruefung(ctx, student, {
				note: ctx.gradeNotes[0],
				datum: attemptDate(ctx, ctx.maxAntritte),
			}).then((response) => {
				const [saved] = expectNotenSuccess(response, "letzter Antritt");
				const kommTyp = ctx.cisConfig.PRUEFUNG_TYP_KOMMISSIONELL;

				if (letzteRolle() === "kommissionell") {
					expect(saved.pruefungstyp_kurzbz, "der letzte Antritt ist kommissionell").to.eq(kommTyp);
					return;
				}

				expect(
					saved.pruefungstyp_kurzbz,
					`die Kette endet mit der Rolle "${letzteRolle()}", nicht kommissionell`,
				).to.not.eq(kommTyp);
			});

			readState(ctx).then((data) => {
				const verlauf = verlaufOfStudent(data, student.uid);
				expect(verlauf.antrittCount, "die kommissionelle zählt als Antritt").to.eq(ctx.maxAntritte);
				expect(verlauf.canAdd, "danach ist kein Antritt mehr möglich").to.be.false;
			});
		});

		// Die Anlage kann abgeschaltet sein, wenn eine Installation die kommissionelle Prüfung in
		// einem anderen Werkzeug einträgt. Angezeigt wird sie trotzdem, nur angelegt nicht.
		it("verweigert den letzten Antritt, wenn das Tool ihn nicht anlegen darf", function () {
			if (kommPruefAnlegbar()) {
				Cypress.log({ name: "skip", message: "Skipped: das Tool darf die kommissionelle Prüfung anlegen." });
				this.skip();
			}

			const student = studentFor(4);

			givenBaseline(ctx, student);

			// bis zum vorletzten Antritt auffüllen; danach wäre der kommissionelle fällig
			for (let i = 1; i < ctx.maxAntritte - 1; i += 1) {
				addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, i) }).then(
					(response) => expectNotenSuccess(response, `Antritt ${i + 1}`),
				);
			}

			readState(ctx).then((data) => {
				const verlauf = verlaufOfStudent(data, student.uid);
				expect(verlauf.kommPruefGesperrt, "der Verlauf nennt den Grund").to.be.true;
				expect(verlauf.canAdd, "und lässt keinen Antritt mehr zu").to.be.false;
			});

			addPruefung(ctx, student, {
				note: ctx.gradeNotes[0],
				datum: attemptDate(ctx, ctx.maxAntritte),
			}).then((response) => expectNotenError(response, "kommPruefNichtErlaubt"));
		});

		// Auch eine kommissionelle Prüfung ohne zählende Note schliesst die Kette. Sonst liesse sich
		// nach einer noch unbenoteten kommissionellen ein weiterer Antritt anlegen.
		it("sperrt weitere Antritte auch bei einer kommPruef ohne zählende Note", () => {
			const student = studentFor(1);

			givenBaseline(ctx, student);

			seedPruefung(ctx, student, {
				note: ctx.notes.nochNichtEingetragen,
				datum: attemptDate(ctx, 1),
				typ: "kommPruef",
			});

			readState(ctx).then((data) => {
				const verlauf = verlaufOfStudent(data, student.uid);
				expect(verlauf.terminal, "die Kette ist geschlossen").to.be.true;
				expect(verlauf.canAdd, "kein weiterer Antritt möglich").to.be.false;
			});

			addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: attemptDate(ctx, 2) }).then(
				(response) => expectNotenError(response, "maxAntritteReached"),
			);
		});

	});

	describe("'Noch nicht eingetragen' does not consume an attempt", () => {
	});

	describe("Anrechnung - no Prüfungen at all", () => {
		it("refuses an attempt while the Zeugnisnote is angerechnet", () => {
			const angerechnet = ctx.notes.angerechnet ?? ctx.notes.internAngerechnet;
			if (!angerechnet) {
				cy.log("no Anrechnungsnote in tbl_note - skipped");
				return;
			}

			// braucht einen Studenten, dessen Fixture-Zeugnisnote eine Anrechnung ist
			readState(ctx).then((data) => {
				const target = (data[0] || []).find((s) => String(s.note) === String(angerechnet));
				if (!target) {
					cy.log(`no student with Zeugnisnote ${angerechnet} in LV ${ctx.lvId} - skipped`);
					return;
				}

				const verlauf = verlaufOfStudent(data, target.uid);
				expect(verlauf.angerechnet, "the Verlauf marks the Anrechnung").to.be.true;
				expect(verlauf.canAdd, "and blocks further attempts").to.be.false;

				addPruefung(ctx, target, { note: ctx.gradeNotes[0], datum: attemptDate(ctx, 1) }).then(
					(response) => expectNotenError(response, "c4angerechnetKeinePruefung"),
				);
			});
		});
	});

	describe("edit guards", () => {
		/** Antritt 1 < entschuldigt < echte Note; liefert die entschuldigte Zeile (Nachbarn beidseits). */
		const givenThreeAttempts = (student) => {
			givenBaseline(ctx, student);

			addPruefung(ctx, student, { note: ctx.notes.entschuldigt, datum: attemptDate(ctx, 1) });
			addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: attemptDate(ctx, 2) });

			return readState(ctx).then((data) => {
				const excused = attemptsOfStudent(data, student.uid).find(
					(p) => String(p.note) === String(ctx.notes.entschuldigt),
				);
				expect(excused, "excused attempt to edit").to.exist;
				return excused;
			});
		};

		it("rejects changing the grade once a later attempt exists", () => {
			const student = studentFor(1);

			givenThreeAttempts(student).then((excused) => {
				editPruefung(ctx, student, {
					pruefungId: excused.pruefung_id,
					note: ctx.gradeNotes[1], // different grade -> locked
					datum: attemptDate(ctx, 1),
				}).then((response) => expectNotenError(response, "pruefungNoteLocked"));
			});
		});

		it("still allows a date-only correction between the neighbouring attempts", () => {
			const student = studentFor(1);

			givenThreeAttempts(student).then((excused) => {
				// same note -> the lock does not apply; date stays strictly inside (baseline, attempt2)
				editPruefung(ctx, student, {
					pruefungId: excused.pruefung_id,
					note: excused.note,
					datum: shiftDate(attemptDate(ctx, 1), 3),
				})
					.then((response) => expectNotenSuccess(response, "date-only correction inside the bounds"))
					.then(() => readState(ctx))
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

		it("rejects a date on or before the previous attempt", () => {
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
