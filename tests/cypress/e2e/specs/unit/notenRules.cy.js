/**
 * Die §1-Regeln auf Clientseite. Pure functions - kein Server, keine Fixture.
 *
 * Die Regeln liegen im Server und erreichen den Client als `student.verlauf`; hier wird nur das
 * Auswerten und der lokale Fallback (Verlauf noch nicht geladen) festgenagelt.
 */

import {
	antrittCountStudent,
	brauchtNeueLvNote,
	canAddPruefung,
	checkFreigabe,
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

describe("Benotungstool rules (client)", () => {
	describe("maxAntrittCount", () => {
	});

	describe("antrittCountStudent", () => {
		it("takes the count from the Verlauf when the server delivered one", () => {
			const s = withVerlauf({ antrittCount: 2 }, { note: 1, pruefungen: [{ note: 5 }] });
			expect(antrittCountStudent(s, ours, notenOptions), "server wins over the local fallback").to.eq(2);
		});

		describe("local fallback (Verlauf not loaded yet)", () => {
			it("does not count entschuldigt, 'noch nicht eingetragen' or 'nicht beurteilt'", () => {
				// still just the original note: none of these consumed an attempt, which is what lets a
				// second retake be entered
				const s = student({
					note: 1,
					pruefungen: [{ note: ENTSCHULDIGT }, { note: NOCH_NICHT }, { note: NICHT_BEURTEILT }],
				});
				expect(antrittCountStudent(s, ours, notenOptions)).to.eq(1);
			});

			it("counts the original LV note as the first Antritt", () => {
				expect(antrittCountStudent(student({ note: 1 }), ours, notenOptions)).to.eq(1);
			});

		});
	});

	describe("canAddPruefung", () => {
		it("follows the Verlauf", () => {
			expect(canAddPruefung(withVerlauf({ canAdd: true, antrittCount: 1 }), ours)).to.be.true;
			expect(canAddPruefung(withVerlauf({ canAdd: false, antrittCount: 2 }), ours)).to.be.false;
		});

		it("blocks an angerechnet row even though attempts are left", () => {
			// the server sets canAdd:false for an Anrechnung; the client must not second-guess it
			const s = withVerlauf({ canAdd: false, antrittCount: 0, angerechnet: true });
			expect(canAddPruefung(s, ours)).to.be.false;
		});

		it("falls back to the cap and the terminal attempt without a Verlauf", () => {
			expect(canAddPruefung(student({ note: 1 }), ours), "one attempt of two used").to.be.true;
			expect(
				canAddPruefung(student({ note: 1, pruefungen: [{ note: 5 }, { note: 4 }] }), ours),
				"cap reached",
			).to.be.false;
			expect(
				canAddPruefung(student({ note: 1, pruefungen: [{ note: 5, kommissionell: true }] }), ours),
				"terminal attempt exists",
			).to.be.false;
		});

		// Bekannte Lücke, hier festgenagelt: die LV-Note zählt nur solange gar keine Prüfung
		// existiert. Deshalb schreibt die Freigabe Antritt 1 als echte Zeile.
		it("stops counting the LV note once a Prüfung exists", () => {
			const s = student({ note: 1, pruefungen: [{ note: 5 }] });
			expect(antrittCountStudent(s, ours, notenOptions), "the LV note is no longer added").to.eq(1);
			expect(canAddPruefung(s, ours), "so another attempt still looks available").to.be.true;
		});
	});

	describe("brauchtNeueLvNote", () => {
	});

	describe("checkFreigabe", () => {
		it("derives the state from the two timestamps", () => {
			expect(checkFreigabe(null, null), "no grade at all").to.eq("offen");
			expect(checkFreigabe(null, "2026-01-10 08:00:00"), "never released").to.eq("changed");
			expect(checkFreigabe("2026-01-10 08:00:00", "2026-01-20 08:00:00"), "re-graded").to.eq("changed");
			expect(checkFreigabe("2026-01-10 08:00:00", "2026-01-10 08:00:00"), "unchanged").to.eq("ok");
		});
	});
});
