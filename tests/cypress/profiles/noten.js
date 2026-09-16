/**
 * Konfigurationsprofile der Benotungstool-Suite.
 *
 * Ein Profil nennt nur die Abweichung vom Stand der Instanz. config ändert application/config/noten.php,
 * flags ändert die define()-Schalter in config/global.config.inc.php. Nach dem Wechsel prüft die Suite
 * jeden Schlüssel, den getCisConfig unter demselben Namen liefert. pruefe nennt Werte, die ein Flag nur
 * mittelbar ändert oder die getCisConfig aufgelöst liefert.
 *
 * Ein Profil schaltet Zweige ein, die kein anderes Profil prüft. `npm run cy:noten:profile` listet am
 * Ende die Tests, die in keinem Profil laufen.
 */

module.exports = {
	// gilt in jedem Profil. Die Freigabemail bleibt an: die Entwicklungsinstanzen stellen jede Mail in ein
	// Debug-Postfach zu.
	basis: {},

	profile: {
		standard: {
			beschreibung: "die Konfiguration der Instanz",
		},

		auslieferung: {
			beschreibung: "die Schalter aus global.config-default.inc.php: TERMIN3 an",
			flags: { CIS_GESAMTNOTE_PRUEFUNG_TERMIN3: true },
			// 1 + TERMIN2 + TERMIN3 + KOMMPRUEF, solange CIS_GESAMTNOTE_MAX_ANTRITTE fehlt
			pruefe: { CIS_GESAMTNOTE_MAX_ANTRITTE: 4 },
		},

		"ohne-erstantritt": {
			beschreibung: "kein Pfad legt Antritt 1 an, die Freigabe verlangt kein Passwort",
			config: {
				CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME: false,
				CIS_GESAMTNOTE_FREIGABE_PASSWORT: false,
			},
		},

		final: {
			beschreibung: "eine freigegebene Note ist endgültig",
			config: { CIS_GESAMTNOTE_FREIGABE_FINAL: true },
		},

		"zwei-antritte": {
			beschreibung: "zwei Antritte, die kommissionelle Prüfung trägt die Studierendenverwaltung ein",
			config: {
				CIS_GESAMTNOTE_MAX_ANTRITTE: 2,
				CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF: false,
			},
		},

		"kommpruef-stv": {
			beschreibung: "drei Antritte, die kommissionelle Prüfung trägt die Studierendenverwaltung ein",
			config: { CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF: false },
		},

		"ohne-kommission": {
			beschreibung: "kein Antritt ist kommissionell",
			config: { CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT: 0 },
			// getCisConfig löst 0 zu null auf
			pruefe: { CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT: null },
		},

		rollen: {
			beschreibung: "die Assistenz darf weder freigeben noch die kommissionelle Prüfung anlegen",
			config: {
				CIS_GESAMTNOTE_ROLLENMATRIX: {
					"lehre/benotungstool": ["vorschlag", "pruefung", "kommpruef", "freigabe", "import"],
					"lehre/benotungstool_assistenz": ["vorschlag", "pruefung", "import"],
				},
			},
		},

		// Die Specs legen Antritte im Abstand von 30 Tagen an, mit einem entschuldigten Termin dazwischen
		// 60 Tage. Beide Grenzen lassen diese Abstände zu.
		abstand: {
			beschreibung: "Wartefrist 14 Tage und Höchstfrist 400 Tage zwischen zwei Antritten",
			config: {
				CIS_GESAMTNOTE_ANTRITT_MIN_ABSTAND_TAGE: 14,
				CIS_GESAMTNOTE_ANTRITT_MAX_ABSTAND_TAGE: 400,
			},
		},

		verbesserung: {
			beschreibung: "Antritt nach einer positiven Note, die bessere Note bleibt LV-Note",
			config: {
				CIS_GESAMTNOTE_NOTENVERBESSERUNG: true,
				CIS_GESAMTNOTE_VERBESSERUNG_BESSERE_GEWINNT: true,
			},
		},

		// nicht mit abstand: am selben Tag beträgt der Abstand 0 Tage
		"gleicher-tag": {
			beschreibung: "ein neuer Termin darf am Tag eines bestehenden liegen",
			config: { CIS_GESAMTNOTE_TERMIN_GLEICHER_TAG: true },
		},

		"frist-ausnahme": {
			beschreibung: "die Lehrenden dürfen nach der Eingabefrist eintragen",
			config: { CIS_GESAMTNOTE_FRIST_AUSNAHME: ["lehre/benotungstool"] },
			pruefe: { CIS_GESAMTNOTE_FRIST_AUSNAHME_GILT: true },
		},

		"frist-nur-datum": {
			beschreibung: "nur das Prüfungsdatum ist an die Frist gebunden, die Eingabe nicht",
			config: { CIS_GESAMTNOTE_FRIST_EINGABE: false },
		},

		offen: {
			beschreibung:
				"frühere Noten änderbar, künftiges Benotungsdatum, keine Datumsfrist, ein Termin hebt die Freigabe nicht auf",
			config: {
				CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETEREM_TERMIN: false,
				CIS_GESAMTNOTE_DATUM_ZUKUNFT: true,
				CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM: false,
				CIS_GESAMTNOTE_PRUEFUNG_HEBT_FREIGABE_AUF: false,
			},
		},

		notenimport: {
			beschreibung: "der Notenimport ohne Datum, mit dem Kürzel aus tbl_note.anmerkung",
			config: {
				CIS_GESAMTNOTE_NOTENIMPORT: true,
				CIS_GESAMTNOTE_IMPORT_NOTENKUERZEL: true,
			},
		},

		punkte: {
			beschreibung: "Punktemodus",
			flags: { CIS_GESAMTNOTE_PUNKTE: true },
		},

		// BESSERE_GEWINNT bleibt aus: eine schlechtere Verbesserung schreibt die letzte Note
		"punkte-verbesserung": {
			beschreibung: "Punktemodus mit Notenverbesserung und Notenimport",
			flags: { CIS_GESAMTNOTE_PUNKTE: true },
			config: {
				CIS_GESAMTNOTE_NOTENVERBESSERUNG: true,
				CIS_GESAMTNOTE_NOTENIMPORT: true,
			},
		},
	},
};
