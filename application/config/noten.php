<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

// 'entschuldigt' & 'noch nicht eingetragen' -> wirken sich nicht auf Antritte aus
$config['NOTEN_OHNE_ANTRITT'] = [9, 17]; // tbl_note pk

$config['NOTEN_OCCURANCE_LIMIT_MAP'] = [17 => 1]; // across the 4 fixed antritte only one can be entschuldigt

// tbl_note pk of the 'entschuldigt' note. An entschuldigt Termin is preserved as its own dated
// entry when a new pruefung of the same type is created (instead of being overwritten).
$config['NOTE_ENTSCHULDIGT'] = 17;

// availability of the two Benotungstool import flows. When both are true they are shown as
// separate buttons/dialogs.
$config['CIS_GESAMTNOTE_PRUEFUNGSIMPORT'] = true;  // dated import that creates a pruefung per row
$config['CIS_GESAMTNOTE_NOTENIMPORT'] = false;     // classic note-only import (uid + note, no date)

$config['SHOW_BENOTUNGSDATUM_ON_NOTENVORSCHLAG_UEBERNAHME'] = true;

// Noteneintragungsfrist (Prüfungsordnung §1): grade/pruefung entry is only permitted up to this
// deadline. The month/day below is applied to the studiensemester's year:
//   Sommersemester (SSyyyy) -> deadline in the SAME calendar year   (default 15th November)
//   Wintersemester (WSyyyy) -> deadline in the FOLLOWING calendar year (default 15th May)
$config['CIS_GESAMTNOTE_NOTENEINTRAGUNGSFRIST'] = false; // switch to use the window enforcement
$config['NOTENEINTRAGUNGSFRIST_SS'] = ['month' => 11, 'day' => 15]; // Sommersemester deadline (same year)
$config['NOTENEINTRAGUNGSFRIST_WS'] = ['month' => 5,  'day' => 15];  // Wintersemester deadline (following year)