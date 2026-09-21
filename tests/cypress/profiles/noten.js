/**
 * Configuration profiles for the Grading Tool Suite.
 *
 * A profile specifies only the deviation from the instance's current state. “config” modifies application/config/noten.php,
 * “flags” modifies the define() flags in config/global.config.inc.php. After the change, the suite checks
 * every key that getCisConfig returns under the same name. expect specifies values that a flag only
 * indirectly changes or that getCisConfig returns after resolution.
 *
 * A profile enables branches that no other profile checks. A run over all profiles lists, at the
 * end, the tests that do not run in any profile.
 */

module.exports = {
	// applies to every profile
	base: {},

	profiles: {
		default: {
			description: "die Konfiguration der Instanz",
		},

		shipped: {
			description: "die Schalter aus global.config-default.inc.php: TERMIN3 an",
			flags: { CIS_GESAMTNOTE_PRUEFUNG_TERMIN3: true },
			// 1 + TERMIN2 + TERMIN3 + KOMMPRUEF, solange CIS_GESAMTNOTE_MAX_ANTRITTE fehlt
			expect: { CIS_GESAMTNOTE_MAX_ANTRITTE: 4 },
		},

		"no-erstantritt": {
			description: "kein Pfad legt Antritt 1 an, die Freigabe verlangt kein Passwort",
			config: {
				CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME: false,
				CIS_GESAMTNOTE_FREIGABE_PASSWORT: false,
			},
		},

		final: {
			description: "eine freigegebene Note ist endgültig",
			config: { CIS_GESAMTNOTE_FREIGABE_FINAL: true },
		},

		"two-antritte": {
			description: "zwei Antritte, die kommissionelle Prüfung trägt die Studierendenverwaltung ein",
			config: {
				CIS_GESAMTNOTE_MAX_ANTRITTE: 2,
				CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF: false,
			},
		},

		"kommpruef-stv": {
			description: "drei Antritte, die kommissionelle Prüfung trägt die Studierendenverwaltung ein",
			config: { CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF: false },
		},

		"no-kommission": {
			description: "kein Antritt ist kommissionell",
			config: { CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT: 0 },
			// getCisConfig löst 0 zu null auf
			expect: { CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT: null },
		},

		rollen: {
			description: "die Assistenz darf weder freigeben noch die kommissionelle Prüfung anlegen",
			config: {
				CIS_GESAMTNOTE_ROLLENMATRIX: {
					"lehre/benotungstool": ["vorschlag", "pruefung", "kommpruef", "freigabe", "import"],
					"lehre/benotungstool_assistenz": ["vorschlag", "pruefung", "import"],
				},
			},
		},

		// The specifications call for appearances at 30-day intervals, with one excused appointment in between
		// 60 days. Both limits allow for these intervals.
		"antritt-gap": {
			description: "Wartefrist 14 Tage und Höchstfrist 400 Tage zwischen zwei Antritten",
			config: {
				CIS_GESAMTNOTE_ANTRITT_MIN_ABSTAND_TAGE: 14,
				CIS_GESAMTNOTE_ANTRITT_MAX_ABSTAND_TAGE: 400,
			},
		},

		notenverbesserung: {
			description: "Antritt nach einer positiven Note, die bessere Note bleibt LV-Note",
			config: {
				CIS_GESAMTNOTE_NOTENVERBESSERUNG: true,
				CIS_GESAMTNOTE_VERBESSERUNG_BESSERE_GEWINNT: true,
			},
		},

		// not including the start gap: on the same day, the gap is 0 days
		"same-day": {
			description: "ein neuer Termin darf am Tag eines bestehenden liegen",
			config: { CIS_GESAMTNOTE_TERMIN_GLEICHER_TAG: true },
		},

		"frist-exception": {
			description: "die Lehrenden dürfen nach der Eingabefrist eintragen",
			config: { CIS_GESAMTNOTE_FRIST_AUSNAHME: ["lehre/benotungstool"] },
			expect: { CIS_GESAMTNOTE_FRIST_AUSNAHME_GILT: true },
		},

		"frist-date-only": {
			description: "nur das Prüfungsdatum ist an die Frist gebunden, die Eingabe nicht",
			config: { CIS_GESAMTNOTE_FRIST_EINGABE: false },
		},

		open: {
			description:
				"frühere Noten änderbar, künftiges Benotungsdatum, keine Datumsfrist, ein Termin hebt die Freigabe nicht auf",
			config: {
				CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETEREM_TERMIN: false,
				CIS_GESAMTNOTE_DATUM_ZUKUNFT: true,
				CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM: false,
				CIS_GESAMTNOTE_PRUEFUNG_HEBT_FREIGABE_AUF: false,
			},
		},

		notenimport: {
			description: "der Notenimport ohne Datum, mit dem Kürzel aus tbl_note.anmerkung",
			config: {
				CIS_GESAMTNOTE_NOTENIMPORT: true,
				CIS_GESAMTNOTE_IMPORT_NOTENKUERZEL: true,
			},
		},

		punkte: {
			description: "Punktemodus",
			flags: { CIS_GESAMTNOTE_PUNKTE: true },
		},

		// “BESSERE_GEWINNT” is omitted: a lower improvement results in the final grade
		"punkte-notenverbesserung": {
			description: "Punktemodus mit Notenverbesserung und Notenimport",
			flags: { CIS_GESAMTNOTE_PUNKTE: true },
			config: {
				CIS_GESAMTNOTE_NOTENVERBESSERUNG: true,
				CIS_GESAMTNOTE_NOTENIMPORT: true,
			},
		},
	},
};
