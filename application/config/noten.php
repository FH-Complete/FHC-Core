<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

// Noten die keinen Prüfungsantritt verbrauchen. Fallback-PKs; massgeblich sind die per Bezeichnung
// aufgelösten Noten (NOTEN_OHNE_ANTRITT_BEZEICHNUNGEN), da die PKs je Installation abweichen.
$config['NOTEN_OHNE_ANTRITT'] = [9, 17]; // tbl_note pk

// 'Nicht beurteilt' ist eine Altlast: formal eine Note, inhaltlich keine Leistungsfeststellung.
// Wird weiterhin häufig vergeben und darf daher keinen Antritt kosten.
$config['NOTEN_OHNE_ANTRITT_BEZEICHNUNGEN'] = ['Noch nicht eingetragen', 'entschuldigt', 'Nicht beurteilt'];

// Anrechnungsnoten: die Leistung wurde vorab anerkannt, der/die Studierende ist zwar Teil der
// Lehrveranstaltung, tritt aber zu keiner Prüfung an. Solange die ZEUGNISNOTE eine davon ist, sind
// für diese Lehrveranstaltung keine Prüfungen möglich. Auflösung wie oben über die Bezeichnung,
// die PKs sind nur Fallback.
$config['NOTEN_ANRECHNUNG_BEZEICHNUNGEN'] = ['angerechnet', 'intern angerechnet'];
$config['NOTEN_ANRECHNUNG'] = [6, 16]; // tbl_note pk

$config['NOTEN_OCCURANCE_LIMIT_MAP'] = [17 => 1]; // über alle Antritte hinweg nur ein entschuldigt

// tbl_note pk of the 'entschuldigt' note. An entschuldigt Termin is preserved as its own dated
// entry when a new pruefung of the same type is created (instead of being overwritten).
$config['NOTE_ENTSCHULDIGT'] = 17;

// Maximale Anzahl zählender Prüfungsantritte, die IN DIESEM TOOL eingetragen werden können
// (inkl. erstem Antritt). null = aus den Alt-Flags CIS_GESAMTNOTE_PRUEFUNG_TERMIN2/TERMIN3
// ableiten (1 + je aktiviertem Termin), damit bestehende Installationen unverändert laufen.
// Der abschliessende Antritt (kommPruef) wird woanders eingetragen und zählt hier nicht mit.
$config['CIS_GESAMTNOTE_MAX_ANTRITTE'] = null;

// Prüfungstypen die den Verlauf abschliessen: danach ist kein weiterer Antritt möglich. Das ist
// die einzige Stelle, an der der Typ eines Termins noch eine Bedeutung trägt.
$config['PRUEFUNG_TERMINAL_TYPEN'] = ['kommPruef', 'zusKommPruef'];

// Spaltenaufteilung der Prüfungen in der Notentabelle:
//   'antritt' - eine Spalte je Antrittsnummer, Datum steht in der Zelle (robust bei Einzelterminen)
//   'datum'   - eine Spalte je Prüfungsdatum (kompakt, wenn ganze Jahrgänge am selben Tag antreten)
// Nur die Vorgabe; im Tool umschaltbar und pro Benutzer gespeichert.
$config['CIS_GESAMTNOTE_PRUEFUNGSSPALTEN'] = 'antritt';

// Bei der Notenübernahme durch die Assistenz den ersten Antritt als eigene Prüfung anlegen.
$config['CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME'] = true;

// availability of the two Benotungstool import flows. When both are true they are shown as
// separate buttons/dialogs.
$config['CIS_GESAMTNOTE_PRUEFUNGSIMPORT'] = true;  // dated import that creates a pruefung per row
$config['CIS_GESAMTNOTE_NOTENIMPORT'] = false;     // classic note-only import (uid + note, no date)

// Noteneintragungsfrist (Prüfungsordnung §1): grade/pruefung entry is only permitted up to this
// deadline. The month/day below is applied to the studiensemester's year:
//   Sommersemester (SSyyyy) -> deadline in the SAME calendar year   (default 15th November)
//   Wintersemester (WSyyyy) -> deadline in the FOLLOWING calendar year (default 15th May)
$config['CIS_GESAMTNOTE_NOTENEINTRAGUNGSFRIST'] = false; // switch to use the window enforcement
$config['NOTENEINTRAGUNGSFRIST_SS'] = ['month' => 11, 'day' => 15]; // Sommersemester deadline (same year)
$config['NOTENEINTRAGUNGSFRIST_WS'] = ['month' => 5,  'day' => 15];  // Wintersemester deadline (following year)