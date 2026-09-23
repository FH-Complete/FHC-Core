<?php
/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

// the constructor needs the permissions before CodeIgniter can load a helper
require_once APPPATH . 'helpers/hlp_benotungstool_helper.php';

use CI3_Events as Events;

/**
 * The Noten API of the Benotungstool.
 *
 * Read:  getCisConfig, getNoten, getBenotungstoolContext, getLvForStudiengang, getLehreinheitenForLv,
 *        getLektorenForLehreinheit, getStudentenNoten, getNoteByPunkte
 * Write: saveLvNote, importLvNoten, savePruefung, createPruefungen, importPruefungen, saveFreigabe
 *
 * Each write answers { <uid>: row }. A row is { lvgesamtnote, verlauf } plus 'pruefung' for a Pruefung.
 * A rejected row of a bulk write is { error: { code, message } }. An error that stops the request has
 * the same code. The code is the phrase key of the message.
 *
 * PruefungsverlaufLib holds the rules of the Antritt chain. This class holds access, Frist and Freigabe.
 * The domain model is in tests/cypress/suites/readme_noten.txt, section 2.
 */
class Noten extends FHCAPI_Controller
{
	/** lehre.tbl_note by key, read once per request. */
	private $activeNotenCache = null;

	/** The Lektoren of one Lehreinheit. */
	private $lektorenCache = array();

	/** The Lehreinheiten of one student in one LV. An import asks for them several times. */
	private $lehreinheitenCache = array();

	/** The Zeugnisnote of one student in one LV. This tool never writes it. */
	private $zeugnisnoteCache = array();

	public function __construct()
	{
		$permissions = benotungstoolPermissions();
		parent::__construct([
			'getCisConfig' => $permissions,
			'getNoten' => $permissions,
			'getBenotungstoolContext' => $permissions,
			'getLvForStudiengang' => $permissions,
			'getLehreinheitenForLv' => $permissions,
			'getLektorenForLehreinheit' => $permissions,
			'getStudentenNoten' => $permissions,
			'getNoteByPunkte' => $permissions,
			'saveLvNote' => $permissions,
			'importLvNoten' => $permissions,
			'savePruefung' => $permissions,
			'createPruefungen' => $permissions,
			'importPruefungen' => $permissions,
			'saveFreigabe' => $permissions
		]);

		$this->load->library('AuthLib', null, 'AuthLib');
		$this->load->library('PermissionLib');
		$this->load->library('PhrasesLib');
		$this->load->library('PruefungsverlaufLib', null, 'VerlaufLib');

		// the log entries name this class, its function and its line
		$this->load->library('LogLib', array(
			'classIndex' => 5,
			'functionIndex' => 5,
			'lineIndex' => 4,
			'dbLogType' => 'API', // required
			'dbExecuteUser' => 'RESTful API',
			'requestId' => 'API',
			'requestDataFormatter' => function ($data) {
				return json_encode($data);
			}
		), 'logLib');

		$this->loadPhrases([
			'global',
			'person',
			'benotungstool',
			'lehre',
			'ui',
			'password'
		]);

		$this->load->model('education/LePruefung_model', 'LePruefungModel');
		$this->load->model('education/Lehreinheit_model', 'LehreinheitModel');
		$this->load->model('education/Lvgesamtnote_model', 'LvgesamtnoteModel');
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->load->model('education/Notenschluesselaufteilung_model', 'NotenschluesselaufteilungModel');
		$this->load->model('education/Note_model', 'NoteModel');
		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');
		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');
		$this->load->model('codex/Mobilitaet_model', 'MobilitaetModel');
		$this->load->model('organisation/Erhalter_model', 'ErhalterModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$this->load->config('noten');
		$this->load->helper('hlp_sancho_helper');
	}

	// === Read ===================================================================================

	/** GET. The configuration that the client and the tests read. A Note is a key, never a Bezeichnung. */
	public function getCisConfig()
	{
		$special = $this->VerlaufLib->getSpecialNotes();

		$config = array(
			// define() flags of the old tool
			'CIS_GESAMTNOTE_PUNKTE' => CIS_GESAMTNOTE_PUNKTE,
			'CIS_GESAMTNOTE_PRUEFUNG_MOODLE_LE_NOTE' => CIS_GESAMTNOTE_PRUEFUNG_MOODLE_LE_NOTE,

			// values that the server derives
			'CIS_GESAMTNOTE_MAX_ANTRITTE' => $this->VerlaufLib->getMaxAntritte(),
			'CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT' => $this->VerlaufLib->getKommissionellFromAntritt(),
			'CIS_GESAMTNOTE_AKTIONEN' => $this->allowedActions(),
			'CIS_GESAMTNOTE_FRIST_AUSNAHME_GILT' => $this->hasFristException(),

			// the special Noten as keys of lehre.tbl_note
			'NOTE_ENTSCHULDIGT' => $special['entschuldigt'],
			'NOTE_NICHT_EINGETRAGEN' => $special['nichtEingetragen'],
			'NOTEN_OHNE_ANTRITT' => $special['ohneAntritt'],
			'NOTEN_ANRECHNUNG' => $special['anrechnung'],
			'NOTEN_ABSCHLIESSEND' => $special['abschliessend'],
			'NOTEN_OCCURRENCE_LIMIT_MAP' => $special['limitMap']
		);

		// the values of config/noten.php as they are
		$keys = array(
			'CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF',
			'CIS_GESAMTNOTE_ANTRITT_MAX_ABSTAND_TAGE',
			'CIS_GESAMTNOTE_ANTRITT_MIN_ABSTAND_TAGE',
			'CIS_GESAMTNOTE_ANTRITT_ZEICHEN',
			'CIS_GESAMTNOTE_DATUM_ZUKUNFT',
			'CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME',
			'CIS_GESAMTNOTE_FREIGABE_FINAL',
			'CIS_GESAMTNOTE_FREIGABE_PASSWORT',
			'CIS_GESAMTNOTE_FREIGABEMAIL',
			'CIS_GESAMTNOTE_FREIGABEMAIL_VORLAGE',
			'CIS_GESAMTNOTE_FRIST_AUSNAHME',
			'CIS_GESAMTNOTE_FRIST_EINGABE',
			'CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM',
			'CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT',
			'CIS_GESAMTNOTE_IMPORT_NOTENKUERZEL',
			'CIS_GESAMTNOTE_IMPORT_SPALTEN_NOTEN',
			'CIS_GESAMTNOTE_IMPORT_SPALTEN_PRUEFUNG',
			'CIS_GESAMTNOTE_LEKTOR_NUR_EIGENE_LV',
			'CIS_GESAMTNOTE_LVNOTE_NUR_LEHRENOTEN',
			'CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETERER_PRUEFUNG',
			'CIS_GESAMTNOTE_NOTENIMPORT',
			'CIS_GESAMTNOTE_NOTENVERBESSERUNG',
			'CIS_GESAMTNOTE_PRUEFUNG_GLEICHER_TAG',
			'CIS_GESAMTNOTE_PRUEFUNG_HEBT_FREIGABE_AUF',
			'CIS_GESAMTNOTE_PRUEFUNGSIMPORT',
			'CIS_GESAMTNOTE_PRUEFUNGSSPALTEN',
			'CIS_GESAMTNOTE_ROLLENMATRIX',
			'CIS_GESAMTNOTE_VERBESSERUNG_BESSERE_GEWINNT',
			'CIS_GESAMTNOTE_VORSCHLAG_NACH_WIEDERHOLUNG',
			'NOTENEINTRAGUNGSFRIST_SS',
			'NOTENEINTRAGUNGSFRIST_WS',
			'PRUEFUNG_TYP_KOMMISSIONELL',
			'PRUEFUNG_TYPEN_OHNE_ANTRITT'
		);
		foreach ($keys as $key) $config[$key] = $this->config->item($key);

		$this->terminateWithSuccess($config);
	}

	/** GET. All active Noten, in the order of NOTEN_SORTIERUNG. */
	public function getNoten()
	{
		$result = $this->NoteModel->getAllActive($this->config->item('NOTEN_SORTIERUNG'));
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	/**
	 * GET 'sem_kurzbz', optional 'lv_id' (a deep link). A Lektor gets the own LVs. An Assistenz gets
	 * the Studiengaenge of the permission, and the Studiengang of the deep link.
	 */
	public function getBenotungstoolContext()
	{
		$sem_kurzbz = $this->input->get('sem_kurzbz', TRUE);
		$lv_id = $this->input->get('lv_id', TRUE);

		// the same roles as assertLvAccess
		$isLektor = $this->permissionlib->isBerechtigt('lehre/benotungstool');
		$studiengaenge = $this->permissionlib->getSTG_isEntitledFor('lehre/benotungstool_assistenz');
		$isAssistenz = !$isLektor && is_array($studiengaenge) && count($studiengaenge) > 0;

		$context = array(
			'isAssistenz' => $isAssistenz,
			'studiengaenge' => array(),
			'lehrveranstaltungen' => array(),
			'preselectStudiengang_kz' => null
		);

		if (isEmptyString((string) $sem_kurzbz)) $this->terminateWithSuccess($context);

		if (!$isAssistenz) {
			$result = $this->LehrveranstaltungModel->getLvForLektorInSemester($sem_kurzbz, getAuthUID());
			if (!isError($result)) $context['lehrveranstaltungen'] = getData($result) ?: array();

			$this->terminateWithSuccess($context);
		}

		$result = $this->StudiengangModel->getByStgs($studiengaenge, $sem_kurzbz);
		if (!isError($result)) $context['studiengaenge'] = getData($result) ?: array();

		// the Studiengang of the deep link, if the Assistenz may see it
		if (ctype_digit((string) $lv_id)) {
			$result = $this->LehrveranstaltungModel->load($lv_id);
			if (hasData($result) && in_array(getData($result)[0]->studiengang_kz, $studiengaenge)) {
				$context['preselectStudiengang_kz'] = getData($result)[0]->studiengang_kz;
			}
		}

		$this->terminateWithSuccess($context);
	}

	/** GET 'studiengang_kz', 'sem_kurzbz'. The LVs of one Studiengang, for the Assistenz. */
	public function getLvForStudiengang()
	{
		$studiengang_kz = $this->input->get('studiengang_kz', TRUE);
		$sem_kurzbz = $this->input->get('sem_kurzbz', TRUE);

		if (isEmptyString((string) $studiengang_kz) || isEmptyString((string) $sem_kurzbz)) {
			$this->terminateWithPhrase('global', 'wrongParameters');
		}

		$studiengaenge = $this->permissionlib->getSTG_isEntitledFor('lehre/benotungstool_assistenz');
		if (!$this->permissionlib->isBerechtigt('admin')
			&& (!is_array($studiengaenge) || !in_array($studiengang_kz, $studiengaenge))) {
			$this->terminateWithPhrase('ui', 'keineBerechtigung');
		}

		$result = $this->LehrveranstaltungModel->getLvForStudiengangInSemester($sem_kurzbz, $studiengang_kz);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	/** GET 'lv_id', 'sem_kurzbz'. All Lehreinheiten of the LV, for the filter and the Notenlisten. */
	public function getLehreinheitenForLv()
	{
		$lv_id = $this->input->get('lv_id', TRUE);
		$sem_kurzbz = $this->input->get('sem_kurzbz', TRUE);

		$this->assertLvAccess($lv_id, $sem_kurzbz);

		$result = $this->LehreinheitModel->getLehreinheitenForLv($lv_id, $sem_kurzbz);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	/** GET 'lehreinheit_id', 'lv_id', 'sem_kurzbz'. The Lektoren of one Lehreinheit; with several, the dialog offers a choice. */
	public function getLektorenForLehreinheit()
	{
		$lehreinheit_id = $this->input->get('lehreinheit_id', TRUE);
		$lv_id = $this->input->get('lv_id', TRUE);
		$sem_kurzbz = $this->input->get('sem_kurzbz', TRUE);

		if (!ctype_digit((string) $lehreinheit_id)) $this->terminateWithPhrase('global', 'missingParameters');

		$this->assertLvAccess($lv_id, $sem_kurzbz);

		// the Lehreinheit must belong to this LV
		$result = $this->LehreinheitModel->loadWhere(array(
			'lehreinheit_id' => $lehreinheit_id,
			'lehrveranstaltung_id' => $lv_id,
			'studiensemester_kurzbz' => $sem_kurzbz
		));
		if (!hasData($result)) $this->terminateWithPhrase('global', 'wrongParameters');

		$lektoren = array();
		foreach ($this->lektorenOfLehreinheit($lehreinheit_id) as $lektor) {
			$lektoren[] = array(
				'mitarbeiter_uid' => $lektor->mitarbeiter_uid,
				'vorname' => $lektor->vorname,
				'nachname' => $lektor->nachname
			);
		}

		$this->terminateWithSuccess($lektoren);
	}

	/**
	 * GET 'lv_id', 'sem_kurzbz'. One row per student: the Zeugnisnote, the LV-Note, the Teilnoten, the
	 * proposal and the Verlauf with all Pruefungen. -> { students, domain }
	 */
	public function getStudentenNoten()
	{
		$lv_id = $this->input->get('lv_id', TRUE);
		$sem_kurzbz = $this->input->get('sem_kurzbz', TRUE);

		$this->assertLvAccess($lv_id, $sem_kurzbz);

		$students = $this->getDataOrTerminateWithError($this->LehrveranstaltungModel->getStudentsByLv($sem_kurzbz, $lv_id));

		// an LV without students is no error; the addons get no empty list
		if (empty($students)) $this->terminateWithSuccess(array('students' => array(), 'domain' => DOMAIN));

		$uids = array_column($students, 'uid');

		$lvNoten = $this->lvGesamtnotenByUid($lv_id, $sem_kurzbz);
		$pruefungen = $this->pruefungenByUid($lv_id, $sem_kurzbz);
		$teilnoten = $this->teilnotenByUid($uids, $lv_id, $sem_kurzbz);
		$mobility = $this->mobilityByUid($uids);
		$anwesenheiten = $this->getAnwesenheiten(array_column($students, 'prestudent_id'), $lv_id, $sem_kurzbz);
		$notenForPunkte = array();

		foreach ($students as $student) {
			$uid = $student->uid;
			$lvgesamtnote = isset($lvNoten[$uid]) ? $lvNoten[$uid] : null;

			// getStudentsByLv names the Zeugnisnote 'note'
			$student->zeugnisnote = $student->note;
			unset($student->note);

			$student->lv_note = $lvgesamtnote ? $lvgesamtnote->note : null;
			$student->lv_punkte = $lvgesamtnote ? $lvgesamtnote->punkte : null;
			$student->freigabedatum = $lvgesamtnote ? $lvgesamtnote->freigabedatum : null;
			$student->benotungsdatum = $lvgesamtnote ? $lvgesamtnote->benotungsdatum : null;
			$student->teilnoten = $teilnoten[$uid];
			$student->mobility_zusatz = isset($mobility[$uid]) ? $mobility[$uid] : null;
			// null without the Anwesenheiten addon
			$student->anwquote = isset($anwesenheiten[$student->prestudent_id]) ? $anwesenheiten[$student->prestudent_id] : null;

			// an LV-Note replaces the proposal
			$student->proposed_note = $student->lv_note !== null
				? $student->lv_note
				: $this->proposeNote($student->teilnoten, $lv_id, $sem_kurzbz, $notenForPunkte);

			$verlauf = $this->VerlaufLib->buildVerlauf(
				isset($pruefungen[$uid]) ? $pruefungen[$uid] : array(),
				$student->lv_note,
				$student->zeugnisnote
			);
			$student->verlauf = $this->verlaufSummary($verlauf, $lvgesamtnote, $student->zeugnisnote);
		}

		$this->terminateWithSuccess(array('students' => $students, 'domain' => DOMAIN));
	}

	/** POST 'punkte', 'lv_id', 'sem_kurzbz'. The Note for the Punkte, from the Notenschluessel of the LV. */
	public function getNoteByPunkte()
	{
		$payload = $this->getPostWith(array('punkte', 'lv_id', 'sem_kurzbz'));

		$this->assertLvAccess($payload->lv_id, $payload->sem_kurzbz);

		$result = $this->NotenschluesselaufteilungModel->getNote($payload->punkte, $payload->lv_id, $payload->sem_kurzbz);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	// === Write ==================================================================================

	/**
	 * POST 'lv_id', 'sem_kurzbz', 'student_uid', 'note', optional 'punkte' and 'datum'. Writes the
	 * LV-Note, and Antritt 1 on 'datum'. -> { uid: { lvgesamtnote, verlauf } }
	 */
	public function saveLvNote()
	{
		// the role first: a user without the action gets that answer, not a parameter error
		$this->assertAction('lvnote');

		$payload = $this->getPostWith(array('lv_id', 'sem_kurzbz', 'student_uid', 'note'));

		$this->assertLvAccess($payload->lv_id, $payload->sem_kurzbz);
		$this->assertFrist($payload->sem_kurzbz);

		$row = $this->saveLvNoteForStudent(
			$payload->lv_id, $payload->sem_kurzbz, $payload->student_uid,
			$payload->note, $this->field($payload, 'punkte'), $this->field($payload, 'datum')
		);
		if (isset($row['error'])) $this->terminateWithError($row['error'], 'general');

		$this->terminateWithSuccess(array($payload->student_uid => $row));
	}

	/**
	 * POST 'lv_id', 'sem_kurzbz', 'lv_noten' [{ uid, note, punkte }]. The Noten import.
	 * -> { uid: { lvgesamtnote, verlauf } or { error } }
	 */
	public function importLvNoten()
	{
		$this->assertAction('import');

		$payload = $this->getPostWith(array('lv_id', 'sem_kurzbz', 'lv_noten'));
		$this->assertRows($payload->lv_noten);

		$this->assertLvAccess($payload->lv_id, $payload->sem_kurzbz);
		$this->assertFrist($payload->sem_kurzbz);

		// the switch also hides the button; a direct call must not pass it
		if (!$this->config->item('CIS_GESAMTNOTE_NOTENIMPORT')) $this->terminateWithPhrase('benotungstool', 'importAusgeschaltet');

		$rows = array();
		foreach ($payload->lv_noten as $lvNote) {
			$row = $this->missingPunkteError($lvNote);
			if ($row === null) {
				$row = $this->saveLvNoteForStudent(
					$payload->lv_id, $payload->sem_kurzbz, $lvNote->uid,
					$this->field($lvNote, 'note'), $this->field($lvNote, 'punkte'), null
				);
			}

			$rows[$lvNote->uid] = $row;
			if (isset($row['error']) && $this->config->item('CIS_GESAMTNOTE_IMPORT_ABBRUCH')) break;
		}

		$this->terminateWithSuccess($rows);
	}

	/**
	 * POST 'lv_id', 'sem_kurzbz', 'student_uid', 'datum' (Y-m-d), 'note', optional 'punkte',
	 * 'pruefung_id' (set = change this Pruefung), 'lehreinheit_id', 'mitarbeiter_uid'.
	 * -> { uid: { pruefung, lvgesamtnote, verlauf } }
	 */
	public function savePruefung()
	{
		$this->assertAction('pruefung');

		$payload = $this->getPostWith(array('lv_id', 'sem_kurzbz', 'student_uid', 'datum', 'note'));

		$this->assertLvAccess($payload->lv_id, $payload->sem_kurzbz);
		$this->assertFrist($payload->sem_kurzbz);

		$row = $this->savePruefungForStudent(
			$payload->lv_id, $payload->sem_kurzbz, $payload->student_uid, $this->field($payload, 'pruefung_id'), $payload
		);
		if (isset($row['error'])) $this->terminateWithError($row['error'], 'general');

		$this->terminateWithSuccess(array($payload->student_uid => $row));
	}

	/**
	 * POST 'lv_id', 'sem_kurzbz', 'students' [{ uid, lehreinheit_id }], 'datum', optional 'note',
	 * 'punkte', 'mitarbeiter_uid'. One new Pruefung for each student; without a Note it waits for its
	 * result. -> { uid: { pruefung, lvgesamtnote, verlauf } or { error } }
	 */
	public function createPruefungen()
	{
		$this->assertAction('pruefung');

		$payload = $this->getPostWith(array('lv_id', 'sem_kurzbz', 'students', 'datum'));

		// each entry names a student; the server finds a missing Lehreinheit
		$this->assertRows($payload->students);
		if (count($payload->students) === 0) $this->terminateWithPhrase('global', 'wrongParameters');

		$this->assertLvAccess($payload->lv_id, $payload->sem_kurzbz);
		$this->assertFrist($payload->sem_kurzbz);

		$rows = array();
		foreach ($payload->students as $student) {
			$input = (object) array(
				'datum' => $payload->datum,
				'note' => $this->field($payload, 'note'),
				'punkte' => $this->field($payload, 'punkte'),
				'lehreinheit_id' => $this->field($student, 'lehreinheit_id'),
				// the dialog sends a Lektor only if all students share one Lehreinheit
				'mitarbeiter_uid' => $this->field($payload, 'mitarbeiter_uid')
			);
			$rows[$student->uid] = $this->savePruefungForStudent($payload->lv_id, $payload->sem_kurzbz, $student->uid, null, $input);
		}

		$this->terminateWithSuccess($rows);
	}

	/**
	 * POST 'lv_id', 'sem_kurzbz', 'pruefungen' [{ uid, lehreinheit_id, datum, note, punkte }].
	 * The Pruefung import: one new Pruefung per row. -> { uid: { pruefung, lvgesamtnote, verlauf } or { error } }
	 */
	public function importPruefungen()
	{
		$this->assertAction('import');

		$payload = $this->getPostWith(array('lv_id', 'sem_kurzbz', 'pruefungen'));
		$this->assertRows($payload->pruefungen);

		$this->assertLvAccess($payload->lv_id, $payload->sem_kurzbz);
		$this->assertFrist($payload->sem_kurzbz);

		// the switch also hides the button; a direct call must not pass it
		if (!$this->config->item('CIS_GESAMTNOTE_PRUEFUNGSIMPORT')) $this->terminateWithPhrase('benotungstool', 'importAusgeschaltet');

		$rows = array();
		foreach ($payload->pruefungen as $pruefung) {
			$row = $this->missingPunkteError($pruefung);
			if ($row === null) {
				$row = $this->savePruefungForStudent($payload->lv_id, $payload->sem_kurzbz, $pruefung->uid, null, $pruefung);
			}

			$rows[$pruefung->uid] = $row;
			if (isset($row['error']) && $this->config->item('CIS_GESAMTNOTE_IMPORT_ABBRUCH')) break;
		}

		$this->terminateWithSuccess($rows);
	}

	/**
	 * POST 'lv_id', 'sem_kurzbz', 'password', 'uids'. The Freigabe of each LV-Note that changed since
	 * the last Freigabe: sets the freigabedatum, writes Antritt 1 and sends the mail.
	 * -> { uid: { lvgesamtnote, verlauf } } for each freigegeben LV-Note
	 */
	public function saveFreigabe()
	{
		$this->assertAction('freigabe');

		$payload = $this->getPostWith(array('lv_id', 'sem_kurzbz', 'password', 'uids'));
		if (!is_array($payload->uids)) $this->terminateWithPhrase('global', 'wrongParameters');

		// a second factor for a binding Note
		if ($this->config->item('CIS_GESAMTNOTE_FREIGABE_PASSWORT')
			&& !$this->AuthLib->checkUserAuthByUsernamePassword(getAuthUID(), $payload->password)->retval) {
			$this->terminateWithPhrase('password', 'wrongPassword');
		}

		$this->assertLvAccess($payload->lv_id, $payload->sem_kurzbz);
		$this->assertFrist($payload->sem_kurzbz);

		// the mail data first: an unknown LV stops the request before any write
		$mail = $this->prepareFreigabeMail($payload->lv_id, $payload->sem_kurzbz);

		$rows = array();
		foreach ($payload->uids as $uid) {
			$row = $this->saveFreigabeForStudent($payload->lv_id, $payload->sem_kurzbz, $uid);
			if ($row !== null) $rows[$uid] = $row;
		}

		$this->logLib->logInfoDB(array('saveFreigabe', array_keys($rows), $payload->lv_id, $payload->sem_kurzbz, getAuthUID(), getAuthPersonId()));

		// no mail without a freigegeben LV-Note
		if ($this->config->item('CIS_GESAMTNOTE_FREIGABEMAIL') && count($rows) > 0) $this->sendFreigabeMail($mail, $rows);

		$this->terminateWithSuccess($rows);
	}

	// === Write: one student =====================================================================

	/**
	 * One LV-Note for one student, and Antritt 1 on $datum. saveLvNote and importLvNoten use it.
	 *
	 * @param string|null $datum the day of Antritt 1; null = today
	 * @return array { lvgesamtnote, verlauf } or { error }
	 */
	private function saveLvNoteForStudent($lv_id, $sem_kurzbz, $student_uid, $note, $punkte, $datum)
	{
		// only a participant of the LV gets a Note
		if (!$this->lehreinheitenOfStudent($lv_id, $student_uid, $sem_kurzbz)) {
			return $this->phraseError('benotungstool', 'studentNichtInLv', array($student_uid));
		}

		// In the Punkte mode the Notenschluessel decides, else the LV-Note contradicts its own Punkte.
		// Without Punkte (a proposal from Moodle Teilnoten) the given Note applies.
		if (CIS_GESAMTNOTE_PUNKTE && $punkte !== null && $punkte !== '') {
			$note = $this->noteFromPunkte($punkte, $lv_id, $sem_kurzbz);
			if ($note === null) return $this->phraseError('benotungstool', 'c4punkteKeineNoteErmittelt', array($student_uid));
		}

		// The day is the date of Antritt 1, not the benotungsdatum: the Freigabe compares the
		// benotungsdatum with the freigabedatum, and a day in the past would look freigegeben.
		$erstantrittDay = isEmptyString((string) $datum) ? date('Y-m-d') : substr((string) $datum, 0, 10);

		// the checks, the LV-Note and Antritt 1 are one change
		$this->beginStudentTransaction($student_uid, $lv_id, $sem_kurzbz);

		// the context of the rules, read once inside the lock
		$zeugnisnote = $this->getZeugnisnote($lv_id, $student_uid, $sem_kurzbz);
		$lvgesamtnote = $this->getLvGesamtnote($lv_id, $student_uid, $sem_kurzbz);
		$verlauf = $this->VerlaufLib->getVerlauf($student_uid, $lv_id, $sem_kurzbz, $lvgesamtnote ? $lvgesamtnote->note : null, $zeugnisnote);

		$error = $this->validateLvNote($student_uid, $note, $verlauf, $lvgesamtnote, $zeugnisnote);
		if ($error === null) $error = $this->validateBenotungsdatum($datum, $student_uid, $sem_kurzbz);
		if ($error !== null) {
			$this->db->trans_rollback();
			return $error;
		}

		$lvgesamtnote = $lvgesamtnote === null
			? $this->createLvGesamtnote($lv_id, $student_uid, $sem_kurzbz, $note, $punkte)
			: $this->updateLvGesamtnote($lvgesamtnote, $note, $punkte, date('Y-m-d H:i:s'));

		if ($lvgesamtnote === null) {
			$this->db->trans_rollback();
			return $this->phraseError('benotungstool', 'lvNoteNichtGespeichert', array($student_uid));
		}

		$this->writeErstantritt($lv_id, $student_uid, $sem_kurzbz, $note, $punkte, $erstantrittDay, PruefungsverlaufLib::SET_DATUM);

		if (!$this->commitStudentTransaction()) return $this->phraseError('benotungstool', 'lvNoteNichtGespeichert', array($student_uid));

		return array(
			'lvgesamtnote' => $lvgesamtnote,
			'verlauf' => $this->readVerlaufSummary($student_uid, $lv_id, $sem_kurzbz, $lvgesamtnote)
		);
	}

	/**
	 * One Pruefung for one student: check it, write the LV-Note, write the Pruefung. Never the
	 * Zeugnisnote. Both writes run in one transaction: an LV-Note without its Pruefung is a state that
	 * no rule describes.
	 *
	 * @param mixed    $pruefung_id set = change this Pruefung, null = a new Pruefung
	 * @param stdClass $input       datum, note, punkte, lehreinheit_id, mitarbeiter_uid
	 * @return array { pruefung, lvgesamtnote, verlauf } or { error }
	 */
	private function savePruefungForStudent($lv_id, $sem_kurzbz, $student_uid, $pruefung_id, $input)
	{
		$isNew = isEmptyString((string) $pruefung_id);
		$note = $this->field($input, 'note');
		$punkte = $this->field($input, 'punkte');
		$mitarbeiter_uid = $this->field($input, 'mitarbeiter_uid');

		// in the Punkte mode the Notenschluessel gives the Note
		if (CIS_GESAMTNOTE_PUNKTE && $punkte !== null && $punkte !== '') {
			$note = $this->noteFromPunkte($punkte, $lv_id, $sem_kurzbz);
			if ($note === null) return $this->phraseError('benotungstool', 'c4punkteKeineNoteErmittelt', array($student_uid));
		}

		// without a Note the Pruefung waits for its result
		if ($note === null || $note === '') {
			$note = $this->VerlaufLib->getNoteNichtEingetragen();
			$punkte = null;
		}

		// the rules compare Y-m-d strings; another format breaks the order of the Antritte
		$datum = substr((string) $this->field($input, 'datum'), 0, 10);
		$parsed = DateTime::createFromFormat('Y-m-d', $datum);
		if (!$parsed || $parsed->format('Y-m-d') !== $datum) {
			return $this->phraseError('benotungstool', 'pruefungsdatumUngueltig', array($student_uid));
		}

		// §7 and §11: each Pruefung takes place before the Frist
		$error = $this->pruefungDatumFristError($sem_kurzbz, $datum, $student_uid);
		if ($error !== null) return $error;

		// the Lehreinheit decides the LV of the Pruefung: only one of this student in this LV
		$lehreinheiten = $this->lehreinheitenOfStudent($lv_id, $student_uid, $sem_kurzbz);
		if (!$lehreinheiten) return $this->phraseError('benotungstool', 'studentNichtInLv', array($student_uid));
		$index = array_search($this->field($input, 'lehreinheit_id'), $lehreinheiten);
		$lehreinheit_id = $lehreinheiten[$index === false ? 0 : $index];

		// the addon runs before the transaction: a failed addon query would abort it
		$entschuldigt = $this->entschuldigtNoteFromAddon($student_uid, $datum);

		$this->beginStudentTransaction($student_uid, $lv_id, $sem_kurzbz);

		// the context of the rules, read once inside the lock
		$zeugnisnote = $this->getZeugnisnote($lv_id, $student_uid, $sem_kurzbz);
		$lvgesamtnote = $this->getLvGesamtnote($lv_id, $student_uid, $sem_kurzbz);
		$lvNote = $lvgesamtnote ? $lvgesamtnote->note : null;
		$verlauf = $this->VerlaufLib->getVerlauf($student_uid, $lv_id, $sem_kurzbz, $lvNote, $zeugnisnote);

		$error = $this->validatePruefung($student_uid, $pruefung_id, $note, $datum, $verlauf, $lvgesamtnote, $zeugnisnote);
		if ($error !== null) {
			$this->db->trans_rollback();
			return $error;
		}

		// An LV-Note without a Pruefung is Antritt 1: the proposal ran without
		// CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME, or the Note is older than this tool. Write it now.
		if ($isNew && count($verlauf->pruefungen) === 0 && $verlauf->implicitErstantritt) {
			// Antritt 1 stays before the new Pruefung, which can be older than the benotungsdatum
			$lvNoteDay = substr((string) $lvgesamtnote->benotungsdatum, 0, 10);
			$erstantrittDay = ($lvNoteDay !== '' && $lvNoteDay < $datum) ? $lvNoteDay : date('Y-m-d', strtotime($datum . ' -1 day'));

			$this->VerlaufLib->upsertErstantritt(
				$student_uid, $lv_id, $sem_kurzbz, $lvNote, $lvgesamtnote->punkte, $erstantrittDay,
				PruefungsverlaufLib::KEEP_DATUM, $this->gradingLektor($lehreinheit_id), $lehreinheit_id
			);
			$verlauf = $this->VerlaufLib->getVerlauf($student_uid, $lv_id, $sem_kurzbz, $lvNote, $zeugnisnote);
		}

		// an entschuldigt date replaces the Note of the dialog, within the occurrence limit
		if ($entschuldigt !== null && !$this->VerlaufLib->exceedsNoteLimit($verlauf->pruefungen, $entschuldigt, $pruefung_id)) {
			$note = $entschuldigt;
		}

		list($newLvNote, $newLvPunkte) = $this->VerlaufLib->deriveLvNote(
			$verlauf, $pruefung_id, $note, $punkte, $lvNote, $lvgesamtnote ? $lvgesamtnote->punkte : null
		);

		if ($lvgesamtnote === null) {
			$lvgesamtnote = $this->createLvGesamtnote($lv_id, $student_uid, $sem_kurzbz, $newLvNote, $newLvPunkte, $lehreinheit_id, $mitarbeiter_uid);
		} else {
			// the Freigabe state compares benotungsdatum and freigabedatum, so a new benotungsdatum cancels the Freigabe
			$benotungsdatum = $this->config->item('CIS_GESAMTNOTE_PRUEFUNG_HEBT_FREIGABE_AUF') ? date('Y-m-d H:i:s') : $lvgesamtnote->benotungsdatum;
			$lvgesamtnote = $this->updateLvGesamtnote($lvgesamtnote, $newLvNote, $newLvPunkte, $benotungsdatum);
		}

		// no Pruefung without an LV-Note (also one that is not freigegeben)
		if ($lvgesamtnote === null) {
			$this->db->trans_rollback();
			return $this->phraseError('benotungstool', 'c4keineLvNoteEingetragen');
		}

		if ($isNew) {
			// one action writes one Pruefung; the Freigabe writes Antritt 1 (writeErstantritt)
			$type = $verlauf->nextRole === PruefungsverlaufLib::ROLE_ERSTANTRITT
				? $this->VerlaufLib->legacyTypFuerAntritt(1)
				: $this->VerlaufLib->legacyTypeForRepeat($verlauf);

			$pruefung = $this->insertPruefung($student_uid, $lehreinheit_id, $note, $punkte, $datum, $mitarbeiter_uid, $type);
		} else {
			$pruefung = $this->updatePruefung($pruefung_id, $note, $punkte, $datum);
		}

		// the saved Pruefung proves the success: a failed insert still leaves a Verlauf
		if ($pruefung === null) {
			$this->db->trans_rollback();
			return $this->phraseError('benotungstool', 'c4pruefungNichtGespeichert', array($student_uid));
		}

		if (!$this->commitStudentTransaction()) return $this->phraseError('benotungstool', 'c4pruefungNichtGespeichert', array($student_uid));

		return array(
			'pruefung' => $pruefung,
			'lvgesamtnote' => $lvgesamtnote,
			'verlauf' => $this->readVerlaufSummary($student_uid, $lv_id, $sem_kurzbz, $lvgesamtnote)
		);
	}

	/**
	 * The Freigabe of one LV-Note, if it changed since the last Freigabe. It makes the Note binding,
	 * so Antritt 1 starts here, on the benotungsdatum.
	 *
	 * @return array|null { lvgesamtnote, verlauf }, or null if nothing changed
	 */
	private function saveFreigabeForStudent($lv_id, $sem_kurzbz, $student_uid)
	{
		// the read, the Freigabe and Antritt 1 are one change
		$this->beginStudentTransaction($student_uid, $lv_id, $sem_kurzbz);

		$lvgesamtnote = $this->getLvGesamtnote($lv_id, $student_uid, $sem_kurzbz);

		// only a change since the last Freigabe, like the old tool
		if ($lvgesamtnote === null || $lvgesamtnote->benotungsdatum <= $lvgesamtnote->freigabedatum) {
			$this->commitStudentTransaction();
			return null;
		}

		$now = date('Y-m-d H:i:s');
		$this->LvgesamtnoteModel->update($this->lvGesamtnoteKey($lvgesamtnote), array(
			'freigabevon_uid' => getAuthUID(),
			'freigabedatum' => $now,
			'updateamum' => $now,
			'updatevon' => getAuthUID()
		));
		$lvgesamtnote = $this->getLvGesamtnote($lv_id, $student_uid, $sem_kurzbz);
		if ($lvgesamtnote === null) {
			$this->db->trans_rollback();
			return null;
		}

		// the Freigabe moves no date: an existing Antritt 1 keeps the day that the Lektor picked
		$this->writeErstantritt(
			$lv_id, $student_uid, $sem_kurzbz, $lvgesamtnote->note, $lvgesamtnote->punkte,
			$lvgesamtnote->benotungsdatum, PruefungsverlaufLib::KEEP_DATUM
		);

		if (!$this->commitStudentTransaction()) return null;

		return array(
			'lvgesamtnote' => $lvgesamtnote,
			'verlauf' => $this->readVerlaufSummary($student_uid, $lv_id, $sem_kurzbz, $lvgesamtnote)
		);
	}

	/**
	 * Writes Antritt 1 when a Note becomes binding: saveLvNote, the imports and the Freigabe.
	 * CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME switches it off. An Anrechnung forbids each Pruefung.
	 *
	 * @param bool $datumMode PruefungsverlaufLib::SET_DATUM (the Lektor picked the day) or KEEP_DATUM
	 */
	private function writeErstantritt($lv_id, $student_uid, $sem_kurzbz, $note, $punkte, $datum, $datumMode)
	{
		if (!$this->config->item('CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME')) return;
		if ($this->VerlaufLib->isAnrechnungNote($this->getZeugnisnote($lv_id, $student_uid, $sem_kurzbz))) return;

		$lehreinheit_id = $this->lehreinheitOfStudent($lv_id, $student_uid, $sem_kurzbz);

		$pruefung = $this->VerlaufLib->upsertErstantritt(
			$student_uid, $lv_id, $sem_kurzbz, $note, $punkte, $datum,
			$datumMode, $this->gradingLektor($lehreinheit_id), $lehreinheit_id
		);

		if ($pruefung !== null) $this->logLib->logInfoDB(array('erstantritt', $student_uid, getAuthUID(), getAuthPersonId()));
	}

	/** In the Punkte mode an import row needs Punkte. @return array|null { error } */
	private function missingPunkteError($row)
	{
		if (!CIS_GESAMTNOTE_PUNKTE || is_numeric($this->field($row, 'punkte'))) return null;

		return $this->phraseError('benotungstool', 'c4punkteKeineNoteErmittelt', array($row->uid));
	}

	// === Checks =================================================================================

	/**
	 * Checks a write of the LV-Note. The cell editor reads the same answer (verlauf.lvNoteLocked), but
	 * the API and the import bypass it.
	 *
	 * @param stdClass $verlauf from buildVerlauf, read inside the lock
	 * @return array|null { error }
	 */
	private function validateLvNote($student_uid, $note, $verlauf, $lvgesamtnote, $zeugnisnote)
	{
		$value = trim((string) $note);

		// the LV-Note is never 'entschuldigt': an entschuldigt date uses no Antritt
		if ($value !== '' && $value == $this->VerlaufLib->getSpecialNotes()['entschuldigt']) {
			return $this->phraseError('benotungstool', 'c4noteNichtInLehre', array($student_uid));
		}

		$error = $this->lehreNoteError($note, $student_uid);
		if ($error !== null) return $error;

		$reason = $this->lvNoteLockReason($verlauf, $lvgesamtnote, $zeugnisnote);
		return $reason === null ? null : $this->phraseError('benotungstool', $reason, array($student_uid));
	}

	/**
	 * Why nobody may write the LV-Note of this row directly, or null. validateLvNote and the client
	 * (verlauf.lvNoteLocked) read this one answer.
	 *
	 * @return string|null the phrase key of the reason
	 */
	private function lvNoteLockReason($verlauf, $lvgesamtnote, $zeugnisnote)
	{
		// Antritt 1 and the LV-Note are the same result. After a repeat the Note belongs to the Pruefung.
		if ($verlauf->hasRepeat && !$this->config->item('CIS_GESAMTNOTE_VORSCHLAG_NACH_WIEDERHOLUNG')) return 'c4notenvorschlagGesperrt';

		if ($this->config->item('CIS_GESAMTNOTE_FREIGABE_FINAL') && $this->isFreigegeben($lvgesamtnote)) return 'freigabeEndgueltig';

		if ($this->isZeugnisnoteLocked($zeugnisnote)) return 'c4zeugnisnoteGesperrt';

		return null;
	}

	/**
	 * Checks a Pruefung write. The lib checks the Antritt chain; this adds the Freigabe, the Lehre
	 * Noten, the Zeugnisnote and the kommpruef action.
	 *
	 * @param stdClass $verlauf from buildVerlauf, read inside the lock
	 * @return array|null { error }
	 */
	private function validatePruefung($student_uid, $pruefung_id, $note, $datum, $verlauf, $lvgesamtnote, $zeugnisnote)
	{
		$isNew = isEmptyString((string) $pruefung_id);

		$ruleError = $isNew
			? $this->VerlaufLib->validateAdd($verlauf, $note, $datum)
			: $this->VerlaufLib->validateEdit($verlauf, $pruefung_id, $note, $datum);
		if ($ruleError !== null) {
			return $this->phraseError('benotungstool', $ruleError[0], array_merge(array($student_uid), $ruleError[1]));
		}

		if ($this->config->item('CIS_GESAMTNOTE_FREIGABE_FINAL') && $this->isFreigegeben($lvgesamtnote)) {
			return $this->phraseError('benotungstool', 'freigabeEndgueltig', array($student_uid));
		}

		// the Pruefung also writes the LV-Note: a Note of the Lehre, or one without an Antritt
		if (!in_array(trim((string) $note), $this->VerlaufLib->getSpecialNotes()['ohneAntritt'])) {
			$error = $this->lehreNoteError($note, $student_uid);
			if ($error !== null) return $error;
		}

		if ($this->isZeugnisnoteLocked($zeugnisnote)) {
			return $this->phraseError('benotungstool', 'c4zeugnisnoteGesperrt', array($student_uid));
		}

		// the Rollenmatrix can reserve the kommissionelle Pruefung for one role
		if ($isNew && !$this->canDoAction('kommpruef') && $verlauf->nextRole === PruefungsverlaufLib::ROLE_KOMMISSIONELL) {
			return $this->phraseError('benotungstool', 'kommPruefNichtErlaubt', array($student_uid));
		}

		return null;
	}

	/**
	 * The benotungsdatum becomes the date of Antritt 1, so the rules of a Pruefung date apply.
	 * Without a date the entry takes today.
	 *
	 * @return array|null { error }
	 */
	private function validateBenotungsdatum($datum, $student_uid, $sem_kurzbz)
	{
		if (isEmptyString((string) $datum)) return null;

		$day = substr((string) $datum, 0, 10);
		$parsed = DateTime::createFromFormat('Y-m-d', $day);
		if ($parsed === false || $parsed->format('Y-m-d') !== $day) {
			return $this->phraseError('benotungstool', 'benotungsdatumUngueltig', array($student_uid));
		}

		// an assessment that did not happen yet has no date
		if (!$this->config->item('CIS_GESAMTNOTE_DATUM_ZUKUNFT') && $day > date('Y-m-d')) {
			return $this->phraseError('benotungstool', 'benotungsdatumInZukunft', array($student_uid));
		}

		return $this->pruefungDatumFristError($sem_kurzbz, $day, $student_uid);
	}

	/** With CIS_GESAMTNOTE_LVNOTE_NUR_LEHRENOTEN the LV-Note must be a Note of the Lehre. @return array|null { error } */
	private function lehreNoteError($note, $student_uid)
	{
		$noten = $this->activeNoten();
		$value = trim((string) $note);

		if (!$this->config->item('CIS_GESAMTNOTE_LVNOTE_NUR_LEHRENOTEN') || (isset($noten[$value]) && $noten[$value]->lehre)) return null;

		return $this->phraseError('benotungstool', 'c4noteNichtInLehre', array($student_uid));
	}

	/** A Zeugnisnote with lkt_ueberschreibbar = false locks the row. An unknown Note locks nothing. @return bool */
	private function isZeugnisnoteLocked($zeugnisnote)
	{
		$noten = $this->activeNoten();
		$value = trim((string) $zeugnisnote);

		return isset($noten[$value]) && !$noten[$value]->lkt_ueberschreibbar;
	}

	// === Access and roles =======================================================================

	/** Stops the request if no permission of the user has this action. */
	private function assertAction($action)
	{
		if (!$this->canDoAction($action)) $this->terminateWithPhrase('benotungstool', 'aktionNichtErlaubt', array($action));
	}

	/** Does a permission of the user have this action in CIS_GESAMTNOTE_ROLLENMATRIX? @return bool */
	private function canDoAction($action)
	{
		foreach ($this->config->item('CIS_GESAMTNOTE_ROLLENMATRIX') as $permission => $actions) {
			if (in_array($action, $actions) && $this->permissionlib->isBerechtigt($permission)) return true;
		}

		return false;
	}

	/** All actions of the user, for the client. The client only hides buttons; the server decides. @return array */
	private function allowedActions()
	{
		$actions = array();
		foreach ($this->config->item('CIS_GESAMTNOTE_ROLLENMATRIX') as $actionsOfPermission) {
			foreach ($actionsOfPermission as $action) {
				if (!in_array($action, $actions) && $this->canDoAction($action)) $actions[] = $action;
			}
		}

		return $actions;
	}

	/**
	 * Stops a request for a foreign LV. A Lektor reaches the own LVs in this Studiensemester, an
	 * Assistenz the LVs of a Studiengang of the permission. An admin reaches every LV.
	 *
	 * The Studiensemester is required: without it a Lektor of ANY semester passes the check.
	 */
	private function assertLvAccess($lv_id, $sem_kurzbz)
	{
		// an empty id makes load() read all LVs
		if (!ctype_digit((string) $lv_id) || (int) $lv_id < 1 || isEmptyString((string) $sem_kurzbz)) {
			$this->terminateWithPhrase('global', 'wrongParameters');
		}

		if ($this->permissionlib->isBerechtigt('admin')) return;

		if ($this->permissionlib->isBerechtigt('lehre/benotungstool')) {
			if (!$this->config->item('CIS_GESAMTNOTE_LEKTOR_NUR_EIGENE_LV')) return;

			$result = $this->LehrveranstaltungModel->getLektorIsTeachingLva($lv_id, getAuthUID(), $sem_kurzbz);
			if (hasData($result) && getData($result)[0]->teaches > 0) return;
			// not a Lektor of this LV: a user with both permissions can still pass as Assistenz
		}

		$result = $this->LehrveranstaltungModel->load($lv_id);
		$lv = hasData($result) ? getData($result)[0] : null;

		$studiengaenge = $this->permissionlib->getSTG_isEntitledFor('lehre/benotungstool_assistenz');
		if ($lv !== null && is_array($studiengaenge) && in_array($lv->studiengang_kz, $studiengaenge)) return;

		$this->terminateWithPhrase('benotungstool', 'keineBerechtigungNoten', array($lv !== null ? $lv->bezeichnung : $lv_id, $sem_kurzbz));
	}

	// === Frist ==================================================================================

	/** Stops the request after the Frist of the Studiensemester (CIS_GESAMTNOTE_FRIST_EINGABE). */
	private function assertFrist($sem_kurzbz)
	{
		if (!$this->config->item('CIS_GESAMTNOTE_FRIST_EINGABE') || $this->hasFristException()) return;

		$frist = $this->computeFrist($sem_kurzbz);
		if ($frist !== null && new DateTime() > $frist) {
			$this->terminateWithPhrase('benotungstool', 'noteneintragungsfristVorbei', array($frist->format('d.m.Y')));
		}
	}

	/** A Pruefung date after the Frist (CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM). It has no exception. @return array|null { error } */
	private function pruefungDatumFristError($sem_kurzbz, $datum, $student_uid)
	{
		if (!$this->config->item('CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM')) return null;

		$frist = $this->computeFrist($sem_kurzbz);
		$day = substr((string) $datum, 0, 10);
		if ($frist === null || $day === '' || $day <= $frist->format('Y-m-d')) return null;

		return $this->phraseError('benotungstool', 'pruefungsdatumNachFrist', array($student_uid, $frist->format('d.m.Y')));
	}

	/** May the user enter after the Frist (CIS_GESAMTNOTE_FRIST_AUSNAHME)? @return bool */
	private function hasFristException()
	{
		foreach ($this->config->item('CIS_GESAMTNOTE_FRIST_AUSNAHME') as $permission) {
			if ($this->permissionlib->isBerechtigt($permission)) return true;
		}

		return false;
	}

	/** The Frist of 'SSyyyy' (the same year) or 'WSyyyy' (the next year), at 23:59:59. @return DateTime|null */
	private function computeFrist($sem_kurzbz)
	{
		$type = strtoupper(substr((string) $sem_kurzbz, 0, 2));
		$year = (int) substr((string) $sem_kurzbz, 2, 4);
		if ($year <= 0 || ($type !== 'SS' && $type !== 'WS')) return null;

		$monthDay = $this->config->item('NOTENEINTRAGUNGSFRIST_' . $type);

		$frist = new DateTime();
		$frist->setDate($type === 'SS' ? $year : $year + 1, $monthDay['month'], $monthDay['day']);
		$frist->setTime(23, 59, 59);
		return $frist;
	}

	// === Proposal ===============================================================================

	/**
	 * The proposal from the Teilnoten: their average, rounded; in the Punkte mode the Note of the Punkte
	 * average. null if no Teilnote counts.
	 *
	 * @param array $notenForPunkte a cache: equal Punkte give an equal Note
	 * @return mixed|null
	 */
	private function proposeNote($teilnoten, $lv_id, $sem_kurzbz, &$notenForPunkte)
	{
		$weighted = (bool) CIS_GESAMTNOTE_GEWICHTUNG;
		$sum = 0;
		$divisor = 0;

		foreach ($teilnoten as $teilnote) {
			// the mode decides which value counts; a row without it would only raise the divisor
			$value = CIS_GESAMTNOTE_PUNKTE ? ($teilnote['points'] ?? null) : ($teilnote['grade'] ?? null);
			if (!is_numeric($value)) continue;

			$weight = is_numeric($teilnote['weight'] ?? null) ? $teilnote['weight'] : 0;
			$sum += $weighted ? $value * $weight : $value;
			$divisor += $weighted ? $weight : 1;
		}

		// PHP 7 divides by zero to INF, which gives the best Note
		if ($divisor <= 0) return null;

		if (!CIS_GESAMTNOTE_PUNKTE) return $this->roundNote($sum / $divisor);

		$punkte = round($sum / $divisor, (int) $this->config->item('CIS_GESAMTNOTE_VORSCHLAG_PUNKTE_STELLEN'));
		if (!array_key_exists((string) $punkte, $notenForPunkte)) {
			$notenForPunkte[(string) $punkte] = $this->getDataOrTerminateWithError(
				$this->NotenschluesselaufteilungModel->getNote($punkte, $lv_id, $sem_kurzbz)
			);
		}

		return $notenForPunkte[(string) $punkte];
	}

	/** Rounds the Teilnoten average (CIS_GESAMTNOTE_VORSCHLAG_RUNDUNG). The smaller number is the better Note. @return int */
	private function roundNote($average)
	{
		switch ($this->config->item('CIS_GESAMTNOTE_VORSCHLAG_RUNDUNG')) {
			case 'besser': return (int) floor($average);
			case 'schlechter': return (int) ceil($average);
			default: return (int) round($average);
		}
	}

	/** The Note for $punkte from the Notenschluessel of the LV. @return mixed|null null: the Notenschluessel has none */
	private function noteFromPunkte($punkte, $lv_id, $sem_kurzbz)
	{
		$note = $this->getDataOrTerminateWithError($this->NotenschluesselaufteilungModel->getNote($punkte, $lv_id, $sem_kurzbz));

		return ($note === null || $note === '') ? null : $note;
	}

	// === Database ===============================================================================

	/** Opens a transaction. A second request for the same student in the same LV waits for it. */
	private function beginStudentTransaction($student_uid, $lv_id, $sem_kurzbz)
	{
		$this->db->trans_begin();
		$this->db->query('SELECT pg_advisory_xact_lock(hashtext(?))', array($student_uid . '|' . $lv_id . '|' . $sem_kurzbz));
	}

	/** Commits the write of one student, or rolls it back after a failed statement. @return bool */
	private function commitStudentTransaction()
	{
		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return false;
		}

		return $this->db->trans_commit();
	}

	/**
	 * The LV-Note row WITHOUT the Freigabe filter. Lvgesamtnote_model::getLvGesamtNoten() reads
	 * 'freigabedatum < NOW()' and hides a new LV-Note. This tool always uses this method.
	 *
	 * @return stdClass|null
	 */
	private function getLvGesamtnote($lv_id, $student_uid, $sem_kurzbz)
	{
		$result = $this->LvgesamtnoteModel->getByStudent($lv_id, $student_uid, $sem_kurzbz);
		return hasData($result) ? getData($result)[0] : null;
	}

	/** @return stdClass|null the new LV-Note. The StV writes it into the Zeugnis. */
	private function createLvGesamtnote($lv_id, $student_uid, $sem_kurzbz, $note, $punkte, $lehreinheit_id = null, $mitarbeiter_uid = null)
	{
		if ($lehreinheit_id === null) $lehreinheit_id = $this->lehreinheitOfStudent($lv_id, $student_uid, $sem_kurzbz);
		$now = date('Y-m-d H:i:s');

		$result = $this->LvgesamtnoteModel->insert(array(
			'student_uid' => $student_uid,
			'lehrveranstaltung_id' => $lv_id,
			'studiensemester_kurzbz' => $sem_kurzbz,
			'note' => $note,
			'punkte' => $punkte,
			'mitarbeiter_uid' => $this->gradingLektor($lehreinheit_id, $mitarbeiter_uid),
			'benotungsdatum' => $now,
			'freigabedatum' => null,
			'freigabevon_uid' => null,
			'bemerkung' => null,
			'updateamum' => null,
			'updatevon' => null,
			'insertamum' => $now,
			'insertvon' => getAuthUID()
		));

		$this->logLib->logInfoDB(array('lvgesamtnote inserted', $student_uid, $lv_id, $sem_kurzbz, $note, getAuthUID(), getAuthPersonId()));

		return isError($result) ? null : $this->getLvGesamtnote($lv_id, $student_uid, $sem_kurzbz);
	}

	/** @return stdClass the changed LV-Note, or the old one if the update failed */
	private function updateLvGesamtnote($lvgesamtnote, $note, $punkte, $benotungsdatum)
	{
		$this->LvgesamtnoteModel->update($this->lvGesamtnoteKey($lvgesamtnote), array(
			'note' => $note,
			'punkte' => $punkte,
			'benotungsdatum' => $benotungsdatum,
			'updateamum' => date('Y-m-d H:i:s'),
			'updatevon' => getAuthUID()
		));

		$this->logLib->logInfoDB(array('lvgesamtnote updated', $lvgesamtnote->student_uid, $lvgesamtnote->lehrveranstaltung_id,
			$lvgesamtnote->studiensemester_kurzbz, $note, getAuthUID(), getAuthPersonId()));

		$changed = $this->getLvGesamtnote($lvgesamtnote->lehrveranstaltung_id, $lvgesamtnote->student_uid, $lvgesamtnote->studiensemester_kurzbz);
		return $changed !== null ? $changed : $lvgesamtnote;
	}

	/** The primary key of campus.tbl_lvgesamtnote. @return array */
	private function lvGesamtnoteKey($lvgesamtnote)
	{
		return array($lvgesamtnote->student_uid, $lvgesamtnote->studiensemester_kurzbz, $lvgesamtnote->lehrveranstaltung_id);
	}

	/**
	 * A new Pruefung. The type has no meaning for the rules; the StV and old reports read it.
	 *
	 * @return stdClass|null
	 */
	private function insertPruefung($student_uid, $lehreinheit_id, $note, $punkte, $datum, $mitarbeiter_uid, $type)
	{
		$result = $this->LePruefungModel->insert(array(
			'lehreinheit_id' => $lehreinheit_id,
			'student_uid' => $student_uid,
			'mitarbeiter_uid' => $this->gradingLektor($lehreinheit_id, $mitarbeiter_uid),
			'note' => $note,
			'punkte' => $punkte,
			'pruefungstyp_kurzbz' => $type,
			'datum' => $datum,
			'anmerkung' => '',
			'insertamum' => date('Y-m-d H:i:s'),
			'insertvon' => getAuthUID(),
			'updateamum' => null,
			'updatevon' => null,
			'ext_id' => null
		));

		$this->logLib->logInfoDB(array('pruefung inserted', $student_uid, $type, getAuthUID(), getAuthPersonId()));

		return isError($result) ? null : $this->loadPruefung($result->retval);
	}

	/** Changes Note, Punkte and date of one Pruefung, never its type. @return stdClass|null */
	private function updatePruefung($pruefung_id, $note, $punkte, $datum)
	{
		$result = $this->LePruefungModel->update($pruefung_id, array(
			'note' => $note,
			'punkte' => $punkte,
			'datum' => $datum,
			'anmerkung' => '',
			'updateamum' => date('Y-m-d H:i:s'),
			'updatevon' => getAuthUID()
		));

		$this->logLib->logInfoDB(array('pruefung updated', $pruefung_id, getAuthUID(), getAuthPersonId()));

		return isError($result) ? null : $this->loadPruefung($pruefung_id);
	}

	/** @return stdClass|null */
	private function loadPruefung($pruefung_id)
	{
		$result = $this->LePruefungModel->load($pruefung_id);
		return hasData($result) ? getData($result)[0] : null;
	}

	/** Is this LV-Note freigegeben? @return bool */
	private function isFreigegeben($lvgesamtnote)
	{
		return $lvgesamtnote !== null && !isEmptyString((string) $lvgesamtnote->freigabedatum);
	}

	/** The Zeugnisnote, or null. An Anrechnung is there, not in the LV-Note. @return mixed|null */
	private function getZeugnisnote($lv_id, $student_uid, $sem_kurzbz)
	{
		$key = $lv_id . '|' . $student_uid . '|' . $sem_kurzbz;
		if (!array_key_exists($key, $this->zeugnisnoteCache)) {
			$result = $this->ZeugnisnoteModel->load(array(
				'studiensemester_kurzbz' => $sem_kurzbz,
				'student_uid' => $student_uid,
				'lehrveranstaltung_id' => $lv_id
			));
			$this->zeugnisnoteCache[$key] = hasData($result) ? getData($result)[0]->note : null;
		}

		return $this->zeugnisnoteCache[$key];
	}

	/** The active Noten by key; getNoten() sends the client the same set. @return array */
	private function activeNoten()
	{
		if ($this->activeNotenCache === null) {
			$this->activeNotenCache = array();

			$result = $this->NoteModel->getAllActive($this->config->item('NOTEN_SORTIERUNG'));
			foreach ((hasData($result) ? getData($result) : array()) as $note) $this->activeNotenCache[(string) $note->note] = $note;
		}

		return $this->activeNotenCache;
	}

	/** The Lehreinheiten of one student in the LV, lowest id first. Empty = no participant. @return array */
	private function lehreinheitenOfStudent($lv_id, $student_uid, $sem_kurzbz)
	{
		$key = $lv_id . '|' . $student_uid . '|' . $sem_kurzbz;
		if (!array_key_exists($key, $this->lehreinheitenCache)) {
			$result = $this->LehrveranstaltungModel->getLeIdsByStudent($student_uid, $sem_kurzbz, $lv_id);
			$this->lehreinheitenCache[$key] = hasData($result) ? array_column(getData($result), 'lehreinheit_id') : array();
		}

		return $this->lehreinheitenCache[$key];
	}

	/** The first Lehreinheit of one student in the LV, or null. @return mixed|null */
	private function lehreinheitOfStudent($lv_id, $student_uid, $sem_kurzbz)
	{
		$lehreinheiten = $this->lehreinheitenOfStudent($lv_id, $student_uid, $sem_kurzbz);

		return $lehreinheiten ? $lehreinheiten[0] : null;
	}

	/** The Lektoren of one Lehreinheit (lehre.tbl_lehreinheitmitarbeiter), sorted by uid. @return array */
	private function lektorenOfLehreinheit($lehreinheit_id)
	{
		if (!$lehreinheit_id) return array();

		$key = (string) $lehreinheit_id;
		if (!isset($this->lektorenCache[$key])) {
			$this->load->model('education/Lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');
			$result = $this->LehreinheitmitarbeiterModel->getLektorenByLe($lehreinheit_id);

			$lektoren = hasData($result) ? getData($result) : array();
			usort($lektoren, function ($a, $b) {
				return strcmp($a->mitarbeiter_uid, $b->mitarbeiter_uid);
			});
			$this->lektorenCache[$key] = $lektoren;
		}

		return $this->lektorenCache[$key];
	}

	/**
	 * The uid of the grading Lektor for tbl_pruefung and tbl_lvgesamtnote. An Assistenz can select
	 * one; insertvon and updatevon keep the user.
	 *
	 * Order: a valid selection, the user if the user teaches, the first Lektor, the user.
	 *
	 * @return string
	 */
	private function gradingLektor($lehreinheit_id, $selected = null)
	{
		$uids = array_column($this->lektorenOfLehreinheit($lehreinheit_id), 'mitarbeiter_uid');

		if (count($uids) === 0) return getAuthUID();
		if (!isEmptyString((string) $selected) && in_array($selected, $uids)) return $selected;
		if (in_array(getAuthUID(), $uids)) return getAuthUID();

		return $uids[0];
	}

	/** All LV-Noten of the LV by uid, without the Freigabe filter. @return array */
	private function lvGesamtnotenByUid($lv_id, $sem_kurzbz)
	{
		$lvNoten = array();
		$result = $this->LvgesamtnoteModel->getByLvStudiensemester($lv_id, $sem_kurzbz);
		foreach ((hasData($result) ? getData($result) : array()) as $row) $lvNoten[$row->student_uid] = $row;

		return $lvNoten;
	}

	/** All Pruefungen of the LV by uid. @return array */
	private function pruefungenByUid($lv_id, $sem_kurzbz)
	{
		$pruefungen = array();
		$result = $this->LePruefungModel->getPruefungenByLvStudiensemester($lv_id, $sem_kurzbz);
		foreach ((hasData($result) ? getData($result) : array()) as $row) $pruefungen[$row->student_uid][] = $row;

		return $pruefungen;
	}

	/** The mobility text of each student (Mobility/Legende.js explains it). @return array */
	private function mobilityByUid($uids)
	{
		if (count($uids) === 0) return array();

		$erhalter = getData($this->ErhalterModel->load())[0];
		$erhalter_kz = '9' . sprintf('%03s', $erhalter->erhalter_kz);

		$mobility = array();
		foreach ($this->getDataOrTerminateWithError($this->MobilitaetModel->getMobilityZusatzForUids($uids)) as $row) {
			$mobility[$row->uid] = $this->MobilitaetModel->formatZusatz($row, $erhalter_kz);
		}

		return $mobility;
	}

	// === Addons =================================================================================

	/** The Teilnoten of each student from the Moodle addon. An addon error goes into the meta data. @return array */
	private function teilnotenByUid($uids, $lv_id, $sem_kurzbz)
	{
		// the addon appends to $grades[uid]['grades']
		$grades = array();
		foreach ($uids as $uid) $grades[$uid] = array('grades' => array());

		try {
			Events::trigger(
				'getExternalGrades',
				function & () use (&$grades) {
					return $grades;
				},
				array('lvid' => $lv_id, 'stsem' => $sem_kurzbz)
			);
		} catch (Throwable $t) {
			$this->addMeta('getExternalGradesError', $t->getMessage());
		}

		$teilnoten = array();
		foreach ($uids as $uid) $teilnoten[$uid] = $grades[$uid]['grades'];

		return $teilnoten;
	}

	/** The Anwesenheit in percent by prestudent_id, from the Anwesenheiten addon. @return array */
	private function getAnwesenheiten($prestudent_ids, $lv_id, $sem_kurzbz)
	{
		$anwesenheiten = array();

		try {
			Events::trigger(
				'getAnwesenheitenForLvAndSemester',
				$prestudent_ids,
				$lv_id,
				$sem_kurzbz,
				function ($rows) use (&$anwesenheiten) {
					foreach ($rows as $row) $anwesenheiten[$row->prestudent_id] = $row->sum;
				}
			);
		} catch (Throwable $t) {
			$this->addMeta('getAnwesenheitenForLvAndSemester', $t->getMessage());
		}

		return $anwesenheiten;
	}

	/**
	 * The Note 'entschuldigt' if an addon reports the date as entschuldigt, else null. A failure of the
	 * addon must not stop the entry. Call it BEFORE the transaction: a failed addon query aborts it.
	 *
	 * @return mixed|null
	 */
	private function entschuldigtNoteFromAddon($student_uid, $datum)
	{
		$status = array();

		try {
			Events::trigger(
				'getEntschuldigungsStatusForStudentOnDate',
				function & () use (&$status) {
					return $status;
				},
				array('student_uid' => $student_uid, 'datum' => $datum)
			);
		} catch (Throwable $t) {
			$this->addMeta('getEntschuldigungsStatusError', $t->getMessage());
			return null;
		}

		if (count($status) === 0 || $status[0] != true) return null;

		return $this->VerlaufLib->getSpecialNotes()['entschuldigt'];
	}

	// === Freigabe mail ==========================================================================

	/** The parts of the mail that do not depend on the rows. Stops the request for an unknown LV. @return stdClass */
	private function prepareFreigabeMail($lv_id, $sem_kurzbz)
	{
		$result = $this->LehrveranstaltungModel->load($lv_id);
		if (!hasData($result)) $this->terminateWithPhrase('benotungstool', 'noValidLvFoundForId', array($lv_id));
		$lv = getData($result)[0];

		$result = $this->StudiengangModel->load($lv->studiengang_kz);
		if (!hasData($result)) $this->terminateWithPhrase('benotungstool', 'noValidStudiengangFoundForId', array($lv->studiengang_kz));
		$studiengang = getData($result)[0];

		$result = $this->PersonModel->load(getAuthPersonId());
		if (!hasData($result)) $this->terminateWithPhrase('benotungstool', 'noValidPersonFoundForId', array(getAuthPersonId()));
		$person = getData($result)[0];

		$studienplaene = array();
		$result = $this->StudienplanModel->getStudienplanByLvaSemKurzbz($lv_id, $sem_kurzbz);
		foreach ((hasData($result) ? getData($result) : array()) as $studienplan) $studienplaene[] = $studienplan->bezeichnung;

		// the mail names the person and the Note from the database, never a value from the request
		$students = array();
		$result = $this->LehrveranstaltungModel->getStudentsByLv($sem_kurzbz, $lv_id);
		foreach ((hasData($result) ? getData($result) : array()) as $student) $students[$student->uid] = $student;

		$mail = new stdClass();
		$mail->students = $students;
		$mail->studiengangAddresses = explode(', ', $studiengang->email);
		$mail->lektor = $person->anrede . ' ' . $person->vorname . ' ' . $person->nachname;
		$mail->lv = $studiengang->kurzbzlang . ' ' . $lv->semester . '.Semester ' . $lv->bezeichnung
			. ' - ' . $lv->lehrform_kurzbz . ' ' . $lv->orgform_kurzbz . ' - ' . $sem_kurzbz;
		$mail->subject = $this->p->t('benotungstool', 'notenfreigabe') . ' ' . $lv->bezeichnung . ' ' . $lv->orgform_kurzbz
			. ' - ' . implode(' ', $studienplaene);

		return $mail;
	}

	/** Sends the Freigabe mail with one table row per freigegeben LV-Note. */
	private function sendFreigabeMail($mail, $rows)
	{
		// CIS_GESAMTNOTE_FREIGABEMAIL_NOTE: the full table, else the uids only
		$details = (bool) CIS_GESAMTNOTE_FREIGABEMAIL_NOTE;
		$noten = $this->activeNoten();

		$header = $details
			? array($this->p->t('person', 'personenkennzeichen'), $this->p->t('lehre', 'studiengang'),
				$this->p->t('benotungstool', 'c4nachname'), $this->p->t('benotungstool', 'c4vorname'))
			: array($this->p->t('person', 'uid'));
		if ($details && CIS_GESAMTNOTE_PUNKTE) $header[] = $this->p->t('benotungstool', 'c4punkte');
		if ($details) {
			$header[] = $this->p->t('benotungstool', 'c4grade');
			$header[] = $this->p->t('ui', 'bearbeitetVon');
		}

		$table = "<table border='1'><tr><td><b>" . implode("</b></td>\n<td><b>", $header) . "</b></td></tr>\n";

		foreach ($rows as $uid => $row) {
			$lvgesamtnote = $row['lvgesamtnote'];
			$student = isset($mail->students[$uid]) ? $mail->students[$uid] : null;

			$cells = $details
				? array($student ? $student->matrikelnr : $uid, $student ? $student->kuerzel : '',
					$student ? $student->nachname : '', $student ? $student->vorname : '')
				: array($uid);
			if ($details && CIS_GESAMTNOTE_PUNKTE) $cells[] = $lvgesamtnote->punkte;
			if ($details) {
				$note = (string) $lvgesamtnote->note;
				$cells[] = isset($noten[$note]) ? $noten[$note]->bezeichnung : $note;
				$cells[] = $lvgesamtnote->mitarbeiter_uid . ($lvgesamtnote->updatevon != '' ? ' (' . $lvgesamtnote->updatevon . ')' : '');
			}

			$table .= '<tr><td>' . implode('</td><td>', array_map(array($this, 'mailCell'), $cells)) . "</td></tr>\n";
		}
		$table .= '</table>';

		$recipients = $this->freigabeRecipients($mail->studiengangAddresses);
		foreach ($recipients as $recipient) {
			sendSanchoMail(
				$this->config->item('CIS_GESAMTNOTE_FREIGABEMAIL_VORLAGE'),
				array(
					'lektor' => $mail->lektor,
					'lvaname' => $mail->lv,
					'studlist' => $table,
					'neuenotencount' => count($rows),
					'adressen' => implode(';', $recipients)
				),
				$recipient,
				$mail->subject
			);
		}
	}

	/** The recipients from CIS_GESAMTNOTE_FREIGABEMAIL_EMPFAENGER. @return array */
	private function freigabeRecipients($studiengangAddresses)
	{
		$addresses = array();
		foreach ($this->config->item('CIS_GESAMTNOTE_FREIGABEMAIL_EMPFAENGER') as $entry) {
			if ($entry === 'studiengang') {
				foreach ($studiengangAddresses as $address) {
					if (trim($address) !== '') $addresses[] = trim($address);
				}
			} elseif ($entry === 'aufrufer') {
				$addresses[] = getAuthUID() . '@' . DOMAIN;
			} elseif (strpos((string) $entry, '@') !== false) {
				$addresses[] = trim($entry);
			}
		}

		return array_values(array_unique($addresses));
	}

	/** One cell of the mail. The mail is HTML, so no value may carry markup. @return string */
	private function mailCell($value)
	{
		return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
	}

	// === Answers and errors =====================================================================

	/** The Verlauf for the answer of a write. The client builds the row again from it. @return array */
	private function readVerlaufSummary($student_uid, $lv_id, $sem_kurzbz, $lvgesamtnote)
	{
		$zeugnisnote = $this->getZeugnisnote($lv_id, $student_uid, $sem_kurzbz);
		$verlauf = $this->VerlaufLib->getVerlauf(
			$student_uid, $lv_id, $sem_kurzbz, $lvgesamtnote ? $lvgesamtnote->note : null, $zeugnisnote
		);

		return $this->verlaufSummary($verlauf, $lvgesamtnote, $zeugnisnote);
	}

	/**
	 * The fields of the Verlauf that the client reads. The server decides each rule; the client only
	 * reads these answers, so no rule exists twice.
	 *
	 * @return array
	 */
	private function verlaufSummary($verlauf, $lvgesamtnote, $zeugnisnote)
	{
		return array(
			// each Pruefung also carries note_locked: a later Pruefung locks its Note
			'pruefungen' => $verlauf->pruefungen,
			'antrittCount' => $verlauf->antrittCount,
			'maxAntritte' => $verlauf->maxAntritte,
			'canAdd' => $verlauf->canAdd,
			'terminal' => $verlauf->terminal,
			// closed by a pass, not by the limit
			'bestanden' => $verlauf->bestanden,
			// a second Pruefung exists
			'hasRepeat' => $verlauf->hasRepeat,
			// nobody may write the LV-Note directly: the proposal editor and the apply button are locked
			'lvNoteLocked' => $this->lvNoteLockReason($verlauf, $lvgesamtnote, $zeugnisnote) !== null,
			// the Lektor may not overwrite the Zeugnisnote: the row gets no Pruefung
			'zeugnisnoteLocked' => $this->isZeugnisnoteLocked($zeugnisnote),
			// a new Pruefung must not lie before this day (Y-m-d, or null)
			'earliestNewDay' => $verlauf->earliestNewDay,
			// Note => limit: the Noten that NOTEN_OCCURRENCE_LIMIT_MAP allows no more time
			'notenAtLimit' => $verlauf->notenAtLimit,
			// the next Antritt is kommissionell, and this tool may not create it
			'kommPruefLocked' => $verlauf->kommPruefLocked,
			// the row is visible, but nobody can select it
			'angerechnet' => $verlauf->angerechnet,
			// also an LV-Note that is not freigegeben
			'hasLvNote' => $lvgesamtnote !== null
		);
	}

	/** A translated error with its phrase key as code. @return array { error: { code, message } } */
	private function phraseError($category, $key, $params = array())
	{
		return array('error' => array('code' => $key, 'message' => $this->p->t($category, $key, $params)));
	}

	/** Stops the request with a translated error; the phrase key is the code. */
	private function terminateWithPhrase($category, $key, $params = array())
	{
		$error = $this->phraseError($category, $key, $params);
		$this->terminateWithError($error['error'], 'general');
	}

	/** The POST body. Stops the request if a key is missing. @return stdClass */
	private function getPostWith($keys)
	{
		$payload = $this->getPostJSON();

		foreach ($keys as $key) {
			if (!is_object($payload) || !property_exists($payload, $key)) $this->terminateWithPhrase('global', 'missingParameters');
		}

		return $payload;
	}

	/** Stops the request unless $rows is a list of objects with a uid. */
	private function assertRows($rows)
	{
		if (!is_array($rows)) $this->terminateWithPhrase('global', 'wrongParameters');

		foreach ($rows as $row) {
			if (isEmptyString((string) $this->field($row, 'uid'))) $this->terminateWithPhrase('global', 'wrongParameters');
		}
	}

	/** An optional field of a request object. @return mixed|null */
	private function field($object, $key)
	{
		return (is_object($object) && property_exists($object, $key)) ? $object->$key : null;
	}
}
