/**
 * The configurations that the noten suite must pass.
 *
 * A profile lists only the differences to the instance. `config` changes application/config/noten.php,
 * `flags` changes the define() flags in config/global.config.inc.php. After a switch, each key that
 * getCisConfig returns under the same name must have the profile value. `expect` lists values that a
 * flag changes only indirectly, or that getCisConfig returns in a resolved form.
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
			// 1 + TERMIN2 + TERMIN3 + KOMMPRUEF, while CIS_GESAMTNOTE_MAX_ANTRITTE is null
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
			// getCisConfig resolves 0 to null
			expect: { CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT: null },
		},

		rollen: {
			description: "die Assistenz darf weder freigeben noch die kommissionelle Prüfung anlegen",
			config: {
				CIS_GESAMTNOTE_ROLLENMATRIX: {
					"lehre/benotungstool": ["lvnote", "pruefung", "kommpruef", "freigabe", "import"],
					"lehre/benotungstool_assistenz": ["lvnote", "pruefung", "import"],
				},
			},
		},

		// The specs put Antritte 30 days apart, or 60 days with an entschuldigt Pruefung in between.
		// Both limits allow these gaps.
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

		// without CIS_GESAMTNOTE_ANTRITT_MIN_ABSTAND_TAGE: on the same day the gap is 0 days
		"same-day": {
			description: "ein neuer Termin darf am Tag eines bestehenden liegen",
			config: { CIS_GESAMTNOTE_PRUEFUNG_GLEICHER_TAG: true },
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
				"frühere Noten änderbar, künftiges Benotungsdatum, keine Datumsfrist, ein Termin hebt die Freigabe nicht auf, LV-Note nach einer Wiederholung änderbar",
			config: {
				CIS_GESAMTNOTE_VORSCHLAG_NACH_WIEDERHOLUNG: true,
				CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETERER_PRUEFUNG: false,
				CIS_GESAMTNOTE_DATUM_ZUKUNFT: true,
				CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM: false,
				CIS_GESAMTNOTE_PRUEFUNG_HEBT_FREIGABE_AUF: false,
			},
		},

		notenimport: {
			description:
				"nur der Notenimport: ohne Datum, mit dem Kürzel aus tbl_note.anmerkung, der Prüfungsimport ist aus",
			config: {
				CIS_GESAMTNOTE_NOTENIMPORT: true,
				CIS_GESAMTNOTE_IMPORT_NOTENKUERZEL: true,
				CIS_GESAMTNOTE_PRUEFUNGSIMPORT: false,
			},
		},

		punkte: {
			description: "Punktemodus",
			flags: { CIS_GESAMTNOTE_PUNKTE: true },
		},

		// without VERBESSERUNG_BESSERE_GEWINNT: a worse Note of the repeat becomes the LV-Note
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
