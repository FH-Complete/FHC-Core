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

// The maximum number of attempts that count, the first attempt and the kommissionelle attempt
// included. null derives the number from the old flags:
//   1 (the original assessment) + TERMIN2 + TERMIN3 + KOMMPRUEF
// The examination rules permit three attempts. With TERMIN2 and KOMMPRUEF the formula gives 3.
// An installation with TERMIN3 gets 4, which is one more attempt in the same chain.
$config['CIS_GESAMTNOTE_MAX_ANTRITTE'] = null;

// The exam types that take place before a commission.
$config['PRUEFUNG_KOMMISSIONELL_TYPEN'] = ['kommPruef', 'zusKommPruef'];

// The type that the tool writes for the last attempt. The last attempt is always kommissionell.
$config['PRUEFUNG_TYP_KOMMISSIONELL'] = 'kommPruef';

// Exam types that never use an attempt. A zusKommPruef repeats a kommissionelle Prüfung that had
// a procedural fault. It stands outside the attempt chain, and the student administration enters
// it. The examination rules do not describe it.
$config['PRUEFUNG_TYPEN_OHNE_ANTRITT'] = ['zusKommPruef'];

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