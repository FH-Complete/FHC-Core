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