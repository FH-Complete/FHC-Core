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
$config['CIS_GESAMTNOTE_NOTENEINTRAGUNGSFRIST'] = true; // switch to use the window enforcement
$config['NOTENEINTRAGUNGSFRIST_SS'] = ['month' => 11, 'day' => 15]; // Sommersemester deadline (same year)
$config['NOTENEINTRAGUNGSFRIST_WS'] = ['month' => 5,  'day' => 15];  // Wintersemester deadline (following year)

// --- exam date guards --------------------------------------------------------------------------

// allow a new exam on the same day as an existing one
$config['CIS_GESAMTNOTE_TERMIN_GLEICHER_TAG'] = false;

// a later exam locks the earlier grade; only its date stays editable
$config['CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETEREM_TERMIN'] = true;

// allow a benotungsdatum in the future
$config['CIS_GESAMTNOTE_DATUM_ZUKUNFT'] = false;

// waiting period between two ATTEMPTS in days; a Termin without an attempt does not start it.
// null = off
$config['CIS_GESAMTNOTE_ANTRITT_MIN_ABSTAND_TAGE'] = null;

// deadline for the next attempt in days. null = off
$config['CIS_GESAMTNOTE_ANTRITT_MAX_ABSTAND_TAGE'] = null;

// --- when a grade closes the attempt chain -------------------------------------------------------

// grades with no better one; they always close the chain. Named, not keyed: tbl_note resolves them
$config['NOTEN_ABSCHLIESSEND_BEZEICHNUNGEN'] = ['Sehr Gut', 'Bestanden', 'Approbiert', 'Erfolgreich absolviert'];

// allow another attempt after a positive grade (points mode: same grade, more points).
// it uses up an attempt like any repeat
$config['CIS_GESAMTNOTE_NOTENVERBESSERUNG'] = false;

// a worse repeat keeps the better LV-Note; the exam row keeps its real grade
$config['CIS_GESAMTNOTE_VERBESSERUNG_BESSERE_GEWINNT'] = false;

// grade order, best first. tbl_note.notenwert is NULL everywhere, so it cannot be derived.
// a grade outside this list is not comparable -> last grade wins
$config['NOTEN_RANGFOLGE_BEZEICHNUNGEN'] = ['Sehr Gut', 'Gut', 'Befriedigend', 'Genügend', 'Nicht Genügend'];

// --- deadlines ------------------------------------------------------------------------------------
// Two separate questions. Omit a key to inherit CIS_GESAMTNOTE_NOTENEINTRAGUNGSFRIST.
// Example: exam must happen by 15 Nov, but may be entered later -> PRUEFUNGSDATUM true, EINGABE false

// blocks the time of ENTRY
$config['CIS_GESAMTNOTE_FRIST_EINGABE'] = true;

// blocks the DATE of the exam
$config['CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM'] = true;

// permissions exempt from the ENTRY deadline. The date deadline has no exception: an exam does not
// happen retroactively. Empty = no exception. Suggested: ['admin', 'lehre/benotungstool_assistenz']
$config['CIS_GESAMTNOTE_FRIST_AUSNAHME'] = [];

// --- attempt roles and legacy types ---------------------------------------------------------------

// from which attempt the exam is held before a commission.
//   'letzter'  the last attempt, whatever the count
//   0          never
//   3          from attempt 3 on
// attempt count comes from CIS_GESAMTNOTE_MAX_ANTRITTE. A chain of one is never kommissionell
$config['CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT'] = 'letzter';

// legacy pruefungstyp per attempt; Stv still reads that column. A type missing from
// tbl_pruefungstyp is skipped (Termin3 is absent by default).
// The kommissionell role overrides this with PRUEFUNG_TYP_KOMMISSIONELL
$config['PRUEFUNG_TYP_JE_ANTRITT'] = [1 => 'Termin1', 2 => 'Termin2', 3 => 'Termin3'];

// --- roles and release ----------------------------------------------------------------------------

// which permission may do which action. The keys are also the permissions that open the tool.
//   vorschlag  write the LV-Note via the takeover path
//   pruefung   create or edit an exam
//   kommpruef  create the kommissionell attempt (on top of ALLOW_CREATE_KOMMPRUEF)
//   freigabe   release grades
//   import     the bulk paths
// several roles -> union of their actions. Empty matrix = no restriction
$config['CIS_GESAMTNOTE_ROLLENMATRIX'] = [
	'lehre/benotungstool' => ['vorschlag', 'pruefung', 'kommpruef', 'freigabe', 'import'],
	'lehre/benotungstool_assistenz' => ['vorschlag', 'pruefung', 'kommpruef', 'freigabe', 'import']
];

// a teacher sees only the courses they teach
$config['CIS_GESAMTNOTE_LEKTOR_NUR_EIGENE_LV'] = true;

// the release asks for the caller's password
$config['CIS_GESAMTNOTE_FREIGABE_PASSWORT'] = true;

// the release sends a mail
$config['CIS_GESAMTNOTE_FREIGABEMAIL'] = true;

// mail recipients. 'studiengang' = the degree programme addresses, 'aufrufer' = the releasing user.
// an entry containing '@' is a fixed address
$config['CIS_GESAMTNOTE_FREIGABEMAIL_EMPFAENGER'] = ['studiengang', 'aufrufer'];

// Sancho template of the release mail; the body lives in the DB
$config['CIS_GESAMTNOTE_FREIGABEMAIL_VORLAGE'] = 'Notenfreigabe';

// a released grade is final and refuses any later change
$config['CIS_GESAMTNOTE_FREIGABE_FINAL'] = false;

// a new or edited exam revokes the release (it resets benotungsdatum, which the state compares)
$config['CIS_GESAMTNOTE_PRUEFUNG_HEBT_FREIGABE_AUF'] = true;

// --- proposal, import, display --------------------------------------------------------------------

// rounding of the partial-grade average. The SMALLER number is the better grade, so the values are
// named after the grade:
//   'kaufmaennisch'  2.5 -> 3
//   'besser'         2.5 -> 2
//   'schlechter'     2.1 -> 3
$config['CIS_GESAMTNOTE_VORSCHLAG_RUNDUNG'] = 'kaufmaennisch';

// decimals of the points average before the grading scale applies
$config['CIS_GESAMTNOTE_VORSCHLAG_PUNKTE_STELLEN'] = 2;

// the takeover path accepts only grades valid in teaching (tbl_note.lehre)
$config['CIS_GESAMTNOTE_VORSCHLAG_NUR_LEHRENOTEN'] = true;

// the takeover path stays open once a repeat exists. false: from then on use the exam dialog
$config['CIS_GESAMTNOTE_VORSCHLAG_NACH_WIEDERHOLUNG'] = false;

// grade an empty entry falls back to
$config['NOTE_NICHT_EINGETRAGEN_BEZEICHNUNG'] = 'Noch nicht eingetragen';

// import column order. Allowed: 'kennung', 'datum', 'note'
$config['CIS_GESAMTNOTE_IMPORT_SPALTEN_NOTEN'] = ['kennung', 'note'];
$config['CIS_GESAMTNOTE_IMPORT_SPALTEN_PRUEFUNG'] = ['kennung', 'datum', 'note'];

// 'dd.MM.yyyy' or 'yyyy-MM-dd'
$config['CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT'] = 'dd.MM.yyyy';

// stop the bulk path at the first rejected row. false: report it and keep writing the rest
$config['CIS_GESAMTNOTE_IMPORT_ABBRUCH'] = false;

// badge in the exam cell; {n} is the attempt number
$config['CIS_GESAMTNOTE_ANTRITT_ZEICHEN'] = [
	'kommissionell' => '{n}-K',
	'kommissionell_ohne_antritt' => 'K',
	'antritt' => '{n}',
	'ohne_antritt' => '–'
];

// grade list order: 'skala' = 1-5 first then alphabetical, 'bezeichnung' = alphabetical only
$config['NOTEN_SORTIERUNG'] = 'skala';
