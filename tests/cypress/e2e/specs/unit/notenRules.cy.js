/**
 * The §1 rules on the client side as pure functions.
 *
 * The rules are stored on the server and are sent to the client as `student.history`; here, only the
 * evaluation and the local fallback (history not yet loaded) are hard-coded.
 */

import {
	antrittCountStudent,
	brauchtNeueLvNote,
	canAddPruefung,
	checkFreigabe,
	isBestanden,
	maxAntrittCount,
} from "../../../../../public/js/components/Cis/Benotungstool/notenRules.js";

const ENTSCHULDIGT = 14;
const NOCH_NICHT = 9;
const NICHT_BEURTEILT = 7;

/** Mirrors getCisConfig: maxAntritte is derived server-side, the client only reads the number. */
const configWith = (maxAntritte) => ({
	CIS_GESAMTNOTE_MAX_ANTRITTE: maxAntritte,
	NOTEN_OHNE_ANTRITT: [NOCH_NICHT, ENTSCHULDIGT, NICHT_BEURTEILT],
});

const ours = configWith(2);

const notenOptions = [
	{ note: 1, lehre: true },
	{ note: 5, lehre: true },
	{ note: ENTSCHULDIGT, lehre: true },
	{ note: NOCH_NICHT, lehre: true },
	{ note: NICHT_BEURTEILT, lehre: true }, // "Nicht beurteilt" - a grade on paper only
	{ note: 20, lehre: false }, // "intern angerechnet" - no participation
];

const student = (props) => ({ pruefungen: [], note: null, hoechsterAntritt: 0, ...props });

/** A row whose Verlauf the server has already delivered. */
const withVerlauf = (verlauf, props = {}) => student({ verlauf: { maxAntritte: 2, ...verlauf }, ...props });

describe("Benotungstool-Regeln (Client)", () => {
	describe("maxAntrittCount", () => {
		it("liest die Zahl aus der Konfiguration", () => {
			expect(maxAntrittCount(configWith(3))).to.eq(3);
			expect(maxAntrittCount(configWith(1))).to.eq(1);
		});

		it("nimmt 1, solange die Konfiguration keine Zahl liefert", () => {
			// getCisConfig returns the key only after loading; until then, a default value applies
			expect(maxAntrittCount({}), "Schlüssel fehlt").to.eq(1);
			expect(maxAntrittCount({ CIS_GESAMTNOTE_MAX_ANTRITTE: null }), "Schlüssel ist null").to.eq(1);
			expect(maxAntrittCount(undefined), "keine Konfiguration").to.eq(1);
			expect(maxAntrittCount(null), "Konfiguration ist null").to.eq(1);
		});

		// The fallback uses ??, not ||. If || were used, a configured 0 would become 1, and the
		// tool would allow a start that the configuration prohibits.
		it("behält eine konfigurierte 0", () => {
			expect(maxAntrittCount(configWith(0))).to.eq(0);
		});

		// getMaxAntritte on the server returns an int. The conversion ensures that the value remains a
		// number even if it is received as text: the callers compare it, and the tests are strict.
		it("gibt immer eine Zahl zurück", () => {
			expect(maxAntrittCount(configWith("4")), "Text mit Zahl").to.eq(4);
			expect(maxAntrittCount(configWith(3)), "bleibt eine Zahl").to.eq(3);
		});

		it("nimmt 1, wenn der Wert keine Zahl ist", () => {
			expect(maxAntrittCount(configWith("abc")), "unbrauchbarer Text").to.eq(1);
			expect(maxAntrittCount(configWith("")), "leerer Text").to.eq(1);
		});
	});

	describe("antrittCountStudent", () => {
		it("nimmt die Anzahl aus dem Verlauf, wenn der Server einen liefert", () => {
			const s = withVerlauf({ antrittCount: 2 }, { note: 1, pruefungen: [{ note: 5 }] });
			expect(antrittCountStudent(s, ours, notenOptions), "server wins over the local fallback").to.eq(2);
		});

		describe("lokaler Rückfall, solange der Verlauf fehlt", () => {
			it("zählt entschuldigt, 'noch nicht eingetragen' und 'nicht beurteilt' nicht mit", () => {
				// still just the original note: none of these consumed an attempt, which is what lets a
				// second retake be entered
				const s = student({
					note: 1,
					pruefungen: [{ note: ENTSCHULDIGT }, { note: NOCH_NICHT }, { note: NICHT_BEURTEILT }],
				});
				expect(antrittCountStudent(s, ours, notenOptions)).to.eq(1);
			});

			it("zählt die ursprüngliche LV-Note als ersten Antritt", () => {
				expect(antrittCountStudent(student({ note: 1 }), ours, notenOptions)).to.eq(1);
			});

			// Same pitfall as in “needsNewLvNote”: a score of 0 is a score, not a missing value
			it("zählt auch die Note 0 als ersten Antritt", () => {
				const withZeroNote = [...notenOptions, { note: 0, lehre: true }];
				expect(antrittCountStudent(student({ note: 0 }), ours, withZeroNote)).to.eq(1);
			});
		});
	});

	describe("canAddPruefung", () => {
		it("folgt dem Verlauf", () => {
			expect(canAddPruefung(withVerlauf({ canAdd: true, antrittCount: 1 }), ours)).to.be.true;
			expect(canAddPruefung(withVerlauf({ canAdd: false, antrittCount: 2 }), ours)).to.be.false;
		});

		it("sperrt eine angerechnete Zeile trotz freier Antritte", () => {
			// the server sets canAdd: false for an Anrechnung, the client must not second-guess it
			const s = withVerlauf({ canAdd: false, antrittCount: 0, angerechnet: true });
			expect(canAddPruefung(s, ours)).to.be.false;
		});

		it("fällt ohne Verlauf auf das Maximum und den letzten Antritt zurück", () => {
			expect(canAddPruefung(student({ note: 1 }), ours), "one attempt of two used").to.be.true;
			expect(canAddPruefung(student({ note: 1, pruefungen: [{ note: 5 }, { note: 4 }] }), ours), "cap reached").to
				.be.false;
			expect(
				canAddPruefung(student({ note: 1, pruefungen: [{ note: 5, kommissionell: true }] }), ours),
				"terminal attempt exists",
			).to.be.false;
		});

		// Known issue, addressed here: the course grade only counts if there is no exam
		// at all. That is why the approval writes “Attempt 1” as a separate row.
		it("zählt die LV-Note nicht mehr mit, sobald eine Prüfung existiert", () => {
			const s = student({ note: 1, pruefungen: [{ note: 5 }] });
			expect(antrittCountStudent(s, ours, notenOptions), "the LV note is no longer added").to.eq(1);
			expect(canAddPruefung(s, ours), "so another attempt still looks available").to.be.true;
		});
	});

	describe("isBestanden", () => {
		it("folgt dem Verlauf und bleibt ohne Verlauf false", () => {
			expect(isBestanden(withVerlauf({ canAdd: false, bestanden: true }))).to.be.true;
			expect(isBestanden(withVerlauf({ canAdd: false, bestanden: false }))).to.be.false;
			// the client cannot tell a pass without the server rules
			expect(isBestanden(student({ note: 1 }))).to.be.false;
		});
	});

	describe("brauchtNeueLvNote", () => {
		/**
		 * The history uses `lv_note`: `lv_note` only contains APPROVED grades
		 * (`Benotungstool.js` calculates it from `getLvGesamtNoten`), whereas `hatLvNote` also counts the
		 * entered grades that have not yet been approved. Noten.php refers to this field as
		 * “unfiltered, i.e., including those not yet approved.”
		 */
		it("meldet keinen Bedarf, wenn die LV-Note nur noch nicht freigegeben ist", () => {
			const s = { verlauf: { hatLvNote: true }, lv_note: null };
			expect(brauchtNeueLvNote(s), "die Note existiert, sie ist nur nicht freigegeben").to.be.false;
		});

		it("meldet Bedarf, wenn der Verlauf keine LV-Note kennt", () => {
			// even if lv_note is set, the history takes precedence
			expect(brauchtNeueLvNote({ verlauf: { hatLvNote: false }, lv_note: 1 })).to.be.true;
			expect(brauchtNeueLvNote({ verlauf: { hatLvNote: false }, lv_note: null })).to.be.true;
		});

		// verlaufSummary has a default value of zero for hatLvNote. A caller who does not
		// pass it will thus trigger the message “No LV note yet.”
		it("meldet Bedarf, wenn der Verlauf das Feld nicht trägt", () => {
			expect(brauchtNeueLvNote({ verlauf: {} })).to.be.true;
		});

		describe("lokaler Rückfall, solange der Verlauf fehlt", () => {
			it("liest lv_note", () => {
				expect(brauchtNeueLvNote({ lv_note: 1 }), "eine Note ist da").to.be.false;
				expect(brauchtNeueLvNote({ lv_note: null }), "keine Note").to.be.true;
				expect(brauchtNeueLvNote({}), "Feld fehlt").to.be.true;
			});

			// A grade of 0 (“partial grade”) is a grade. A truthy test would treat it as “no grade,” and
			// the tool would promise a new course grade even though one already exists.
			it("erkennt die Note 0 als Note", () => {
				expect(brauchtNeueLvNote({ lv_note: 0 })).to.be.false;
			});

			it("hält einen leeren Text für keine Note", () => {
				expect(brauchtNeueLvNote({ lv_note: "" })).to.be.true;
			});
		});
	});

	describe("checkFreigabe", () => {
		it("leitet den Status aus den beiden Zeitstempeln ab", () => {
			expect(checkFreigabe(null, null), "no grade at all").to.eq("offen");
			expect(checkFreigabe(null, "2026-01-10 08:00:00"), "never released").to.eq("changed");
			expect(checkFreigabe("2026-01-10 08:00:00", "2026-01-20 08:00:00"), "re-graded").to.eq("changed");
			expect(checkFreigabe("2026-01-10 08:00:00", "2026-01-10 08:00:00"), "unchanged").to.eq("ok");
		});
	});
});
