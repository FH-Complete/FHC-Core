<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

// The configuration of the Benotungstool. The tool reads two sources:
//   this file             $this->config->item('KEY')
//   global.config.inc.php define() flags of the old tool: CIS_GESAMTNOTE_PUNKTE, CIS_GESAMTNOTE_GEWICHTUNG,
//                         CIS_GESAMTNOTE_FREIGABEMAIL_NOTE, CIS_GESAMTNOTE_PRUEFUNG_MOODLE_LE_NOTE and
//                         CIS_GESAMTNOTE_PRUEFUNG_TERMIN2/TERMIN3/KOMMPRUEF (see CIS_GESAMTNOTE_MAX_ANTRITTE)
//
// The prefixes in this file:
//   NOTEN_, NOTE_     name a Note by its Bezeichnung. The server finds its key in lehre.tbl_note.
//   PRUEFUNG_         name a Pruefungstyp from lehre.tbl_pruefungstyp.
//   CIS_GESAMTNOTE_   the switches and values of the tool.
//
// Every key must be present. The code has no default values.

// --- Noten ---------------------------------------------------------------------------------------

// Noten that use no Antritt.
$config['NOTEN_OHNE_ANTRITT_BEZEICHNUNGEN'] = ['Noch nicht eingetragen', 'entschuldigt'];

// Anrechnung Noten: an earlier result counts for this LV. As a Zeugnisnote they block every Pruefung.
$config['NOTEN_ANRECHNUNG_BEZEICHNUNGEN'] = ['angerechnet', 'intern angerechnet'];

// How often a Note may occur in the Pruefungen of one student.
$config['NOTEN_OCCURRENCE_LIMIT_MAP'] = ['entschuldigt' => 1];

// The Note 'entschuldigt'. An addon can report a Pruefung date as entschuldigt.
$config['NOTE_ENTSCHULDIGT_BEZEICHNUNG'] = 'entschuldigt';

// The Note of a Pruefung without a result.
$config['NOTE_NICHT_EINGETRAGEN_BEZEICHNUNG'] = 'Noch nicht eingetragen';

// Noten with no better one. They always close the Antritt chain.
$config['NOTEN_ABSCHLIESSEND_BEZEICHNUNGEN'] = ['Sehr Gut', 'Bestanden', 'Approbiert', 'Erfolgreich absolviert'];

// The order of the Noten, best first. tbl_note.notenwert is NULL everywhere, so the order cannot come
// from the database. A Note outside this list is not comparable.
$config['NOTEN_RANGFOLGE_BEZEICHNUNGEN'] = ['Sehr Gut', 'Gut', 'Befriedigend', 'Genügend', 'Nicht Genügend'];

// The order of the Noten in the lists: 'skala' = 1-5 first, then alphabetical; 'bezeichnung' = alphabetical.
$config['NOTEN_SORTIERUNG'] = 'skala';

// --- Antritte -------------------------------------------------------------------------------------

// The maximum number of Antritte, Antritt 1 and the kommissionelle Pruefung included.
// null = 1 + CIS_GESAMTNOTE_PRUEFUNG_TERMIN2 + CIS_GESAMTNOTE_PRUEFUNG_TERMIN3 + CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF.
// The Pruefungsordnung permits three Antritte: TERMIN2 and KOMMPRUEF give 3, TERMIN3 adds one more.
$config['CIS_GESAMTNOTE_MAX_ANTRITTE'] = null;

// The Antritt from which the Pruefung is kommissionell:
//   'letzter'  the last Antritt (CIS_GESAMTNOTE_MAX_ANTRITTE)
//   0          never
//   3          from Antritt 3 on
// A chain of one Antritt is never kommissionell.
$config['CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT'] = 'letzter';

// The Benotungstool may create the kommissionelle Pruefung. Some installations enter it in the StV.
// false: the tool shows an existing kommissionelle Pruefung, but it creates none. The chain then ends
// one Antritt earlier for this tool.
$config['CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF'] = true;

// Allow another Antritt after a positive Note. It uses an Antritt like any repeat.
$config['CIS_GESAMTNOTE_NOTENVERBESSERUNG'] = false;

// A worse repeat keeps the better LV-Note. The Pruefung keeps its own Note.
$config['CIS_GESAMTNOTE_VERBESSERUNG_BESSERE_GEWINNT'] = false;

// Create Antritt 1 as its own Pruefung when a Note becomes the LV-Note: the proposal, the import, the
// Freigabe, and the Notenuebernahme in the StV.
$config['CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME'] = true;

// --- Pruefungstypen ---------------------------------------------------------------------------------
// The tool derives the position of a Pruefung from its date. The type marks a kommissionelle Pruefung,
// sorts old Pruefungen without a date, separates Antritt 1 from a repeat (verlauf.hasRepeat), and feeds
// the old reports of the StV.

// The Pruefungstypen of a kommissionelle Pruefung.
$config['PRUEFUNG_KOMMISSIONELL_TYPEN'] = ['kommPruef', 'zusKommPruef'];

// The Pruefungstyp that the tool writes for a kommissionelle Pruefung.
$config['PRUEFUNG_TYP_KOMMISSIONELL'] = 'kommPruef';

// Pruefungstypen that never use an Antritt. A zusKommPruef repeats a kommissionelle Pruefung with a
// procedural fault. The StV enters it.
$config['PRUEFUNG_TYPEN_OHNE_ANTRITT'] = ['zusKommPruef'];

// The Pruefungstyp that the tool writes for each Antritt. A type that tbl_pruefungstyp does not have
// is skipped (Termin3 is absent by default).
$config['PRUEFUNG_TYP_JE_ANTRITT'] = [1 => 'Termin1', 2 => 'Termin2', 3 => 'Termin3'];

// --- Pruefung dates -------------------------------------------------------------------------------

// Allow a new Pruefung on the day of an existing one.
$config['CIS_GESAMTNOTE_PRUEFUNG_GLEICHER_TAG'] = false;

// A later Pruefung locks the Note of an earlier one. Its date stays editable.
$config['CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETERER_PRUEFUNG'] = true;

// Allow a Benotungsdatum in the future: the day of Antritt 1 when the LV-Note is written. A Pruefung
// may always lie in the future: without a Note it waits for its result.
$config['CIS_GESAMTNOTE_DATUM_ZUKUNFT'] = false;

// The minimum number of days between two Antritte. A Pruefung without an Antritt does not count.
// null = off
$config['CIS_GESAMTNOTE_ANTRITT_MIN_ABSTAND_TAGE'] = null;

// The maximum number of days between two Antritte. null = off
$config['CIS_GESAMTNOTE_ANTRITT_MAX_ABSTAND_TAGE'] = null;

// --- Frist ----------------------------------------------------------------------------------------
// The Frist (Pruefungsordnung §1) is a day in the year of the Studiensemester:
//   Sommersemester SSyyyy -> the same year
//   Wintersemester WSyyyy -> the next year

$config['NOTENEINTRAGUNGSFRIST_SS'] = ['month' => 11, 'day' => 15];
$config['NOTENEINTRAGUNGSFRIST_WS'] = ['month' => 5, 'day' => 15];

// No entry after the Frist.
$config['CIS_GESAMTNOTE_FRIST_EINGABE'] = true;

// No Pruefung date after the Frist.
// Example: a Pruefung must take place before the Frist, but the Note may come later -> EINGABE false.
$config['CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM'] = true;

// Permissions that may enter after the Frist. The Pruefung date has no exception.
// Example: ['admin', 'lehre/benotungstool_assistenz']
$config['CIS_GESAMTNOTE_FRIST_AUSNAHME'] = [];

// --- Roles and Freigabe ---------------------------------------------------------------------------

// The actions of each permission. The keys are also the permissions that open the tool.
//   lvnote     write the LV-Note (the proposal)
//   pruefung   create or edit a Pruefung
//   kommpruef  create the kommissionelle Pruefung (also needs CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF)
//   freigabe   the Freigabe
//   import     the two imports
// A user with several permissions gets all their actions.
$config['CIS_GESAMTNOTE_ROLLENMATRIX'] = [
	'lehre/benotungstool' => ['lvnote', 'pruefung', 'kommpruef', 'freigabe', 'import'],
	'lehre/benotungstool_assistenz' => ['lvnote', 'pruefung', 'kommpruef', 'freigabe', 'import']
];

// A Lektor sees only the LVs that the Lektor teaches.
$config['CIS_GESAMTNOTE_LEKTOR_NUR_EIGENE_LV'] = true;

// The Freigabe asks for the password of the user.
$config['CIS_GESAMTNOTE_FREIGABE_PASSWORT'] = true;

// The Freigabe sends a mail.
$config['CIS_GESAMTNOTE_FREIGABEMAIL'] = true;

// The recipients of the mail: 'studiengang' = the addresses of the Studiengang, 'aufrufer' = the user.
// An entry with an '@' is a fixed address.
$config['CIS_GESAMTNOTE_FREIGABEMAIL_EMPFAENGER'] = ['studiengang', 'aufrufer'];

// The Sancho template of the mail. The text is in the database.
$config['CIS_GESAMTNOTE_FREIGABEMAIL_VORLAGE'] = 'Notenfreigabe';

// A freigegeben LV-Note is final. No path can change it.
$config['CIS_GESAMTNOTE_FREIGABE_FINAL'] = false;

// A new or changed Pruefung cancels the Freigabe of the LV-Note: it sets the benotungsdatum.
$config['CIS_GESAMTNOTE_PRUEFUNG_HEBT_FREIGABE_AUF'] = true;

// --- LV-Note and proposal -------------------------------------------------------------------------

// An LV-Note must be a Note of the Lehre (tbl_note.lehre).
$config['CIS_GESAMTNOTE_LVNOTE_NUR_LEHRENOTEN'] = true;

// The proposal stays open after a repeat. false: after a repeat the Lektor changes the LV-Note
// through the Pruefung.
$config['CIS_GESAMTNOTE_VORSCHLAG_NACH_WIEDERHOLUNG'] = false;

// The rounding of the Teilnoten average. The smaller number is the better Note:
//   'kaufmaennisch'  2.5 -> 3
//   'besser'         2.5 -> 2
//   'schlechter'     2.1 -> 3
$config['CIS_GESAMTNOTE_VORSCHLAG_RUNDUNG'] = 'kaufmaennisch';

// The decimals of the Punkte average before the Notenschluessel applies.
$config['CIS_GESAMTNOTE_VORSCHLAG_PUNKTE_STELLEN'] = 2;

// --- Import and display ---------------------------------------------------------------------------

// The two imports. With both on, the tool shows two buttons.
$config['CIS_GESAMTNOTE_PRUEFUNGSIMPORT'] = true;   // one Pruefung per row: Kennung, Datum, Note
$config['CIS_GESAMTNOTE_NOTENIMPORT'] = false;      // one LV-Note per row: Kennung, Note

// The Note column of an import accepts the key of lehre.tbl_note. true also accepts the short form
// from lehre.tbl_note.anmerkung ('nb', 'ea', 'en') of the Excel Notenliste.
$config['CIS_GESAMTNOTE_IMPORT_NOTENKUERZEL'] = false;

// The column order of an import. Allowed: 'kennung', 'datum', 'note'.
$config['CIS_GESAMTNOTE_IMPORT_SPALTEN_NOTEN'] = ['kennung', 'note'];
$config['CIS_GESAMTNOTE_IMPORT_SPALTEN_PRUEFUNG'] = ['kennung', 'datum', 'note'];

// The date format of an import: 'dd.MM.yyyy' or 'yyyy-MM-dd'.
$config['CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT'] = 'dd.MM.yyyy';

// An import stops at the first rejected row. false: it reports the row and writes the others.
$config['CIS_GESAMTNOTE_IMPORT_ABBRUCH'] = false;

// The Pruefung columns of the table. The user can switch the layout; the browser keeps the choice.
//   'antritt'  one column per Antritt, the date is in the cell
//   'datum'    one column per Pruefung date
$config['CIS_GESAMTNOTE_PRUEFUNGSSPALTEN'] = 'antritt';

// The badge in a Pruefung cell; {n} is the Antritt number.
$config['CIS_GESAMTNOTE_ANTRITT_ZEICHEN'] = [
	'kommissionell' => '{n}-K',
	'kommissionell_ohne_antritt' => 'K',
	'antritt' => '{n}',
	'ohne_antritt' => '–'
];
