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

describe("Benotungstool-Regeln (Client)", () => {
	describe("maxAntrittCount", () => {
		it("liest die Zahl aus der Konfiguration", () => {
			expect(maxAntrittCount(configWith(3))).to.eq(3);
			expect(maxAntrittCount(configWith(1))).to.eq(1);
		});

		it("nimmt 1, solange die Konfiguration keine Zahl liefert", () => {
			// getCisConfig liefert den Schlüssel erst nach dem Laden; bis dahin gilt ein Antritt
			expect(maxAntrittCount({}), "Schlüssel fehlt").to.eq(1);
			expect(maxAntrittCount({ CIS_GESAMTNOTE_MAX_ANTRITTE: null }), "Schlüssel ist null").to.eq(1);
			expect(maxAntrittCount(undefined), "keine Konfiguration").to.eq(1);
			expect(maxAntrittCount(null), "Konfiguration ist null").to.eq(1);
		});

		// Der Rückfall nutzt ??, nicht ||. Mit || würde eine konfigurierte 0 zu 1 werden und das
		// Werkzeug liesse einen Antritt zu, den die Konfiguration verbietet.
		it("behält eine konfigurierte 0", () => {
			expect(maxAntrittCount(configWith(0))).to.eq(0);
		});

		// getMaxAntritte auf dem Server liefert ein int. Die Umwandlung hält den Wert auch dann eine
		// Zahl, wenn er je als Text ankommt: die Aufrufer vergleichen ihn, die Tests streng.
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

			// dieselbe Falle wie in brauchtNeueLvNote: die Note 0 ist eine Note, kein fehlender Wert
			it("zählt auch die Note 0 als ersten Antritt", () => {
				const mitNullNote = [...notenOptions, { note: 0, lehre: true }];
				expect(antrittCountStudent(student({ note: 0 }), ours, mitNullNote)).to.eq(1);
			});
		});
	});

	describe("canAddPruefung", () => {
		it("folgt dem Verlauf", () => {
			expect(canAddPruefung(withVerlauf({ canAdd: true, antrittCount: 1 }), ours)).to.be.true;
			expect(canAddPruefung(withVerlauf({ canAdd: false, antrittCount: 2 }), ours)).to.be.false;
		});

		it("sperrt eine angerechnete Zeile trotz freier Antritte", () => {
			// the server sets canAdd:false for an Anrechnung; the client must not second-guess it
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

		// Bekannte Lücke, hier festgenagelt: die LV-Note zählt nur solange gar keine Prüfung
		// existiert. Deshalb schreibt die Freigabe Antritt 1 als echte Zeile.
		it("zählt die LV-Note nicht mehr mit, sobald eine Prüfung existiert", () => {
			const s = student({ note: 1, pruefungen: [{ note: 5 }] });
			expect(antrittCountStudent(s, ours, notenOptions), "the LV note is no longer added").to.eq(1);
			expect(canAddPruefung(s, ours), "so another attempt still looks available").to.be.true;
		});
	});

	describe("brauchtNeueLvNote", () => {
		/**
		 * Der Verlauf schlägt lv_note, und zwar genau deshalb: lv_note trägt nur FREIGEGEBENE Noten
		 * (Benotungstool.js setzt es aus getLvGesamtNoten), hatLvNote dagegen zählt auch die
		 * eingetragene, noch nicht freigegebene Note mit. Noten.php nennt das Feld
		 * "ungefiltert, also inklusive noch nicht freigegebener".
		 */
		it("meldet keinen Bedarf, wenn die LV-Note nur noch nicht freigegeben ist", () => {
			const s = { verlauf: { hatLvNote: true }, lv_note: null };
			expect(brauchtNeueLvNote(s), "die Note existiert, sie ist nur nicht freigegeben").to.be.false;
		});

		it("meldet Bedarf, wenn der Verlauf keine LV-Note kennt", () => {
			// auch wenn lv_note gesetzt ist: der Verlauf entscheidet
			expect(brauchtNeueLvNote({ verlauf: { hatLvNote: false }, lv_note: 1 })).to.be.true;
			expect(brauchtNeueLvNote({ verlauf: { hatLvNote: false }, lv_note: null })).to.be.true;
		});

		// verlaufSummary hat für hatLvNote den Vorgabewert null. Ein Aufrufer, der ihn nicht
		// übergibt, erzeugt damit den Hinweis "noch keine LV-Note".
		it("meldet Bedarf, wenn der Verlauf das Feld nicht trägt", () => {
			expect(brauchtNeueLvNote({ verlauf: {} })).to.be.true;
		});

		describe("lokaler Rückfall, solange der Verlauf fehlt", () => {
			it("liest lv_note", () => {
				expect(brauchtNeueLvNote({ lv_note: 1 }), "eine Note ist da").to.be.false;
				expect(brauchtNeueLvNote({ lv_note: null }), "keine Note").to.be.true;
				expect(brauchtNeueLvNote({}), "Feld fehlt").to.be.true;
			});

			// Die Note 0 ("Teilnote") ist eine Note. Ein truthy-Test hielte sie für "keine Note" und
			// das Werkzeug verspräche eine neue LV-Note, obwohl eine existiert.
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
