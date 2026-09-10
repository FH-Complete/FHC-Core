<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

// Noten, die keinen Prüfungsantritt verbrauchen.
$config['NOTEN_OHNE_ANTRITT_BEZEICHNUNGEN'] = ['Noch nicht eingetragen', 'entschuldigt'];

// Anrechnungsnoten: die Leistung wurde vorab anerkannt.
$config['NOTEN_ANRECHNUNG_BEZEICHNUNGEN'] = ['angerechnet', 'intern angerechnet'];

// Wie oft eine Note über alle Antritte hinweg vorkommen darf. Schlüssel ist die Bezeichnung.
$config['NOTEN_OCCURANCE_LIMIT_MAP'] = ['entschuldigt' => 1];

// Die Note 'entschuldigt'. Ein entschuldigter Termin bleibt als eigene datierte Zeile erhalten,
// wenn eine neue Prüfung desselben Typs entsteht.
$config['NOTE_ENTSCHULDIGT_BEZEICHNUNG'] = 'entschuldigt';

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

// The Benotungstool may create the kommissionelle Prüfung itself. Some installations enter it in
// another tool. The tool SHOWS an existing kommissionelle Prüfung either way; false blocks the
// creation only: the cell offers no button, and the dialog, the bulk entry and the import refuse
// the row. The chain then stops one attempt earlier for this tool.
$config['CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF'] = true;

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

// The grade column of an imported row. false accepts the note itself (the primary key of
// lehre.tbl_note) only. true also accepts the shorthand from lehre.tbl_note.anmerkung, which the
// Excel grade list uses for the special grades ('nb', 'ea', 'en').
$config['CIS_GESAMTNOTE_IMPORT_NOTENKUERZEL'] = false;

// Noteneintragungsfrist (Prüfungsordnung §1): grade/pruefung entry is only permitted up to this
// deadline. The month/day below is applied to the studiensemester's year:
//   Sommersemester (SSyyyy) -> deadline in the SAME calendar year   (default 15th November)
//   Wintersemester (WSyyyy) -> deadline in the FOLLOWING calendar year (default 15th May)
$config['CIS_GESAMTNOTE_NOTENEINTRAGUNGSFRIST'] = false; // switch to use the window enforcement
$config['NOTENEINTRAGUNGSFRIST_SS'] = ['month' => 11, 'day' => 15]; // Sommersemester deadline (same year)
$config['NOTENEINTRAGUNGSFRIST_WS'] = ['month' => 5,  'day' => 15];  // Wintersemester deadline (following year)