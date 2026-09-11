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

use CI3_Events as Events;

class Noten extends FHCAPI_Controller
{
	/** tbl_note by PK, read once per request. @see aktiveNoten() */
	private $aktiveNotenCache = null;

	/** The teachers of one Lehreinheit. They do not change during one request. */
	private $lehrendeCache = array();

	/** The Lehreinheit of one student in one course. A bulk path asks for it several times. */
	private $lehreinheitCache = array();

	public function __construct()
	{
		$permissions = self::berechtigungenAusMatrix();
		parent::__construct([
			'getStudentenNoten' => $permissions,
			'getNoten' => $permissions,
			'saveStudentenNoten' => $permissions,
			'getNotenvorschlagStudent' => $permissions,
			'saveNotenvorschlag' => $permissions,
			'saveStudentPruefung' => $permissions,
			'createPruefungen' => $permissions,
			'saveNotenvorschlagBulk' => $permissions,
			'savePruefungenBulk' => $permissions,
			'getCisConfig' => $permissions,
			'getNoteByPunkte' => $permissions,
			'getBenotungstoolContext' => $permissions,
			'getLehrendeFuerLehreinheit' => $permissions,
			'getLvForStudiengang' => $permissions
		]);

		$this->load->library('AuthLib', null, 'AuthLib');
		$this->load->library('PhrasesLib');
		$this->load->library('PruefungsverlaufLib', null, 'VerlaufLib');

		// Loads LogLib with different debug trace levels to get data of the job that extends this class
		// It also specify parameters to set database fields
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
		
		// Loads phrases system
		$this->loadPhrases([
			'global',
			'person',
			'benotungstool',
			'lehre',
			'ui',
			'password'
		]);
		
		$this->load->model('education/LePruefung_model', 'LePruefungModel');
		$this->load->model('education/Lvgesamtnote_model', 'LvgesamtnoteModel');
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->load->model('education/Notenschluesselaufteilung_model', 'NotenschluesselaufteilungModel');
		$this->load->model('education/Note_model', 'NoteModel');
		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('codex/Mobilitaet_model', 'MobilitaetModel');
		$this->load->model('organisation/Erhalter_model', 'ErhalterModel');

		$this->load->config('noten');
		$this->load->helper('hlp_sancho_helper');

	}

	/**
	 * POST 'uids', 'datum', optional 'note'/'punkte'. One exam for several students; without a
	 * grade it writes "Noch nicht eingetragen".
	 */
	public function createPruefungen() {
		// role first: a caller without the action fails on the right, not on missing parameters
		$this->assertAktion('pruefung');

		$payload = $this->getPostJSON();

		if(!property_exists($payload, 'uids') || !property_exists($payload, 'datum')
			|| !property_exists($payload, 'lva_id') || !property_exists($payload, 'sem_kurzbz')) {
			$this->terminateWithError($this->p->t('global', 'missingParameters'), 'general');
		}

		$uids = $payload->uids;
		$datum = $payload->datum;
		$lva_id = $payload->lva_id;

		$stsem = $payload->sem_kurzbz;

		// every entry must name a student; the Lehreinheit is optional, the server looks it up
		if(!is_array($uids) || count($uids) === 0) {
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		}

		foreach($uids as $student) {
			if(!is_object($student) || !property_exists($student, 'uid') || isEmptyString($student->uid)) {
				$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
			}
		}

		$this->assertLvAccess($lva_id, $stsem);

		// examination rules: no entry after the grade entry deadline
		$this->enforceNoteneintragungsfrist($stsem);

		$ret = [];

		$note = property_exists($payload, 'note') ? $payload->note : null;
		$punkte = property_exists($payload, 'punkte') ? $payload->punkte : null;

		// the points win: the grade then comes from the grading scale
		if(CIS_GESAMTNOTE_PUNKTE && $punkte !== null && $punkte !== '' && $punkte >= 0) {
			$resNote = $this->NotenschluesselaufteilungModel->getNote($punkte, $lva_id, $stsem);
			$note = $this->getDataOrTerminateWithError($resNote);
		}

		// without a selection the exam has no grade
		if($note === null || $note === '') {
			// config names the grade, the lib resolves it in tbl_note
			$note = $this->VerlaufLib->getNoteNichtEingetragen();
			$punkte = null;
		}

		// the dialog sends the teacher when all selected students share one Lehreinheit
		$mitarbeiter_uid = property_exists($payload, 'mitarbeiter_uid') ? $payload->mitarbeiter_uid : null;

		// the same core as the dialog; each row gets its own error message
		foreach ($uids as $student) {
			$lehreinheit_id = property_exists($student, 'lehreinheit_id') ? $student->lehreinheit_id : null;

			$ret[$student->uid] = $this->savePruefungFuerStudent(
				null, $student->uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum,
				$mitarbeiter_uid
			);
		}

		$this->logLib->logInfoDB(array('createPruefungen',$ret, getAuthUID(), getAuthPersonId()));

		$this->terminateWithSuccess($ret);
	}

	/**
	 * GET, optional 'sem_kurzbz'. Role-determining entry point: a teacher gets their own courses,
	 * an Assistenz gets the degree programmes they may pick from.
	 */
	public function getBenotungstoolContext() {
		$sem_kurzbz = $this->input->get("sem_kurzbz", TRUE);
		$lv_id = $this->input->get("lv_id", TRUE); // optional: deep-link target, used to preselect

		$this->load->library('PermissionLib');

		// teachers keep the classic assigned-LV flow; the Studiengang flow is only for Assistenz.
		// Role determination mirrors assertLvAccess, which scopes each role's actual data
		// access. A teacher sees the own courses, an assistant sees the entitled degree programmes.
		$isLektor = $this->permissionlib->isBerechtigt('lehre/benotungstool');
		$entitledStgs = $this->permissionlib->getSTG_isEntitledFor('lehre/benotungstool_assistenz');
		$isAssistenz = !$isLektor && is_array($entitledStgs) && count($entitledStgs) > 0;

		$studiengaenge = array();
		$lehrveranstaltungen = array();
		$preselectStudiengang_kz = null;

		if (isset($sem_kurzbz) && !isEmptyString($sem_kurzbz)) {
			if ($isAssistenz) {
				$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
				$result = $this->StudiengangModel->getByStgs($entitledStgs, $sem_kurzbz);
				if (!isError($result)) $studiengaenge = getData($result) ?? array();

				// deep-link: resolve the Studiengang of the requested LV so the frontend can preselect
				// the Studiengang dropdown (and then its LV) - only if the Assistenz is entitled for it
				if (isset($lv_id) && !isEmptyString($lv_id)) {
					$res = $this->LehrveranstaltungModel->load($lv_id);
					if (!isError($res) && hasData($res)) {
						$stg = getData($res)[0]->studiengang_kz;
						if (in_array($stg, $entitledStgs)) $preselectStudiengang_kz = $stg;
					}
				}
			} else {
				$result = $this->LehrveranstaltungModel->getLvForLektorInSemester($sem_kurzbz, getAuthUID());
				if (!isError($result)) $lehrveranstaltungen = getData($result) ?? array();
			}
		}

		$this->terminateWithSuccess(array(
			'isAssistenz' => $isAssistenz,
			'studiengaenge' => $studiengaenge,
			'lehrveranstaltungen' => $lehrveranstaltungen,
			'preselectStudiengang_kz' => $preselectStudiengang_kz
		));
	}

	public function getCisConfig() {
		// The configuration names the special grades, tbl_note gives their keys. The client compares
		// keys, therefore the answer carries the resolved keys, never a Bezeichnung.
		$special = $this->VerlaufLib->getSpecialNotes();
		$NOTEN_OHNE_ANTRITT = $special['ohneAntritt'];
		$NOTEN_OCCURANCE_LIMIT_MAP = $special['limitMap'];
		$NOTE_ENTSCHULDIGT = $special['entschuldigt'];
		
		$this->terminateWithSuccess(
			array(
				// show the points during the grade entry
				'CIS_GESAMTNOTE_PUNKTE' => CIS_GESAMTNOTE_PUNKTE,
				
				// basically on/of toggle for the points/grade col and the arrow button
				'CIS_GESAMTNOTE_UEBERSCHREIBEN' => CIS_GESAMTNOTE_UEBERSCHREIBEN,
				
				// only relevant in punkte calculation in backend
				// 'CIS_GESAMTNOTE_GEWICHTUNG' => CIS_GESAMTNOTE_GEWICHTUNG,
				
				// The maximum number of attempts that count in this tool. The server derives it from the
				// configuration or from the old TERMIN2/TERMIN3 flags. The client does not calculate it.
				'CIS_GESAMTNOTE_MAX_ANTRITTE' => $this->VerlaufLib->getMaxAntritte(),

				// attempt from which the exam is kommissionell, or null; 'letzter' already resolved
				'CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT' => $this->VerlaufLib->getKommissionellAbAntritt(),

				// legacy type the kommissionell role writes; Stv reads that column
				'PRUEFUNG_TYP_KOMMISSIONELL' => $this->config->item('PRUEFUNG_TYP_KOMMISSIONELL'),

				// the default column layout ('antritt' or 'datum'); the user can change it in the tool
				'CIS_GESAMTNOTE_PRUEFUNGSSPALTEN' => $this->config->item('CIS_GESAMTNOTE_PRUEFUNGSSPALTEN'),

				// may this tool CREATE the kommissionelle Prüfung (application/config/noten.php)?
				// It always shows one that exists.
				'CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF' => $this->VerlaufLib->darfKommPruefAnlegen(),
				
				//technically exists but is never used, could be LE pendant to next flag
				// 'CIS_GESAMTNOTE_PRUEFUNG_MOODLE_NOTE' => CIS_GESAMTNOTE_PRUEFUNG_MOODLE_NOTE,
			
				// basically a toggle for "use teilnoten" and the source is always moodle
				// setting this to false breaks legacy tool and if that was fixed it wouldnt render any table at all
				// anyway so not sure why this even is a config at all. placebo at best
				
				// toggles availability of the teilnoten column... existas but do we really need this?
				'CIS_GESAMTNOTE_PRUEFUNG_MOODLE_LE_NOTE' => CIS_GESAMTNOTE_PRUEFUNG_MOODLE_LE_NOTE,

				// availability of the two import flows (application/config/noten.php); when both are
				// true they are shown separately
				'CIS_GESAMTNOTE_PRUEFUNGSIMPORT' => $this->config->item('CIS_GESAMTNOTE_PRUEFUNGSIMPORT'),
				'CIS_GESAMTNOTE_NOTENIMPORT' => $this->config->item('CIS_GESAMTNOTE_NOTENIMPORT'),

				// does an imported row accept the shorthand from tbl_note.anmerkung as the grade?
				'CIS_GESAMTNOTE_IMPORT_NOTENKUERZEL' => (bool) $this->config->item('CIS_GESAMTNOTE_IMPORT_NOTENKUERZEL'),

				// shape of an imported row and the badge in the exam cell
				'CIS_GESAMTNOTE_IMPORT_SPALTEN_NOTEN' => $this->config->item('CIS_GESAMTNOTE_IMPORT_SPALTEN_NOTEN'),
				'CIS_GESAMTNOTE_IMPORT_SPALTEN_PRUEFUNG' => $this->config->item('CIS_GESAMTNOTE_IMPORT_SPALTEN_PRUEFUNG'),
				'CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT' => $this->config->item('CIS_GESAMTNOTE_IMPORT_DATUMSFORMAT'),
				'CIS_GESAMTNOTE_ANTRITT_ZEICHEN' => $this->config->item('CIS_GESAMTNOTE_ANTRITT_ZEICHEN'),

				// weighting of the partial grades; server applies, client only shows
				'CIS_GESAMTNOTE_GEWICHTUNG' => defined('CIS_GESAMTNOTE_GEWICHTUNG') && CIS_GESAMTNOTE_GEWICHTUNG,
				
				// send a mail when approving grades
				'CIS_GESAMTNOTE_FREIGABEMAIL_NOTE' => CIS_GESAMTNOTE_FREIGABEMAIL_NOTE,

				// actions this caller may perform; the client only hides buttons, the server decides
				'CIS_GESAMTNOTE_AKTIONEN' => $this->erlaubteAktionen(),

				// release: password required, and final?
				'CIS_GESAMTNOTE_FREIGABE_PASSWORT' => $this->config->item('CIS_GESAMTNOTE_FREIGABE_PASSWORT') !== false,
				'CIS_GESAMTNOTE_FREIGABE_FINAL' => (bool) $this->config->item('CIS_GESAMTNOTE_FREIGABE_FINAL'),
				
				'NOTEN_OHNE_ANTRITT' => $NOTEN_OHNE_ANTRITT,

				'NOTEN_OCCURANCE_LIMIT_MAP' => $NOTEN_OCCURANCE_LIMIT_MAP,

				// pk of the 'entschuldigt' note; used to preserve excused Termine on new pruefung creation
				'NOTE_ENTSCHULDIGT' => $NOTE_ENTSCHULDIGT,

				// Noteneintragungsfrist window (enforced server-side; also surfaced so the UI can hint at it)
				'CIS_GESAMTNOTE_NOTENEINTRAGUNGSFRIST' => $this->config->item('CIS_GESAMTNOTE_NOTENEINTRAGUNGSFRIST'),

				// the two deadlines, fallback already resolved
				'CIS_GESAMTNOTE_FRIST_EINGABE' => $this->fristAktiv('CIS_GESAMTNOTE_FRIST_EINGABE'),
				'CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM' => $this->fristAktiv('CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM'),

				// does THIS user carry an entry-deadline exception?
				'CIS_GESAMTNOTE_FRIST_AUSNAHME_GILT' => $this->darfFristUeberschreiten(),

				// exam date guards; server enforces, client only hides buttons
				'CIS_GESAMTNOTE_TERMIN_GLEICHER_TAG' => (bool) $this->config->item('CIS_GESAMTNOTE_TERMIN_GLEICHER_TAG'),
				'CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETEREM_TERMIN' =>
					$this->config->item('CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETEREM_TERMIN') === null
						? true
						: (bool) $this->config->item('CIS_GESAMTNOTE_NOTE_SPERRE_BEI_SPAETEREM_TERMIN'),
				'CIS_GESAMTNOTE_DATUM_ZUKUNFT' => (bool) $this->config->item('CIS_GESAMTNOTE_DATUM_ZUKUNFT'),
				'CIS_GESAMTNOTE_ANTRITT_MIN_ABSTAND_TAGE' => $this->config->item('CIS_GESAMTNOTE_ANTRITT_MIN_ABSTAND_TAGE'),
				'CIS_GESAMTNOTE_ANTRITT_MAX_ABSTAND_TAGE' => $this->config->item('CIS_GESAMTNOTE_ANTRITT_MAX_ABSTAND_TAGE'),

				// when a grade closes the chain; resolved keys, never a Bezeichnung
				'NOTEN_ABSCHLIESSEND' => $special['abschliessend'],
				'CIS_GESAMTNOTE_NOTENVERBESSERUNG' => $this->VerlaufLib->darfVerbessern(),
				'CIS_GESAMTNOTE_VERBESSERUNG_BESSERE_GEWINNT' =>
					(bool) $this->config->item('CIS_GESAMTNOTE_VERBESSERUNG_BESSERE_GEWINNT')
			)
		);
	}

	/**
	 * GET 'lehreinheit_id', 'lv_id', 'sem_kurzbz'. Teachers of one Lehreinheit; the dialog offers a
	 * choice when there is more than one.
	 */
	public function getLehrendeFuerLehreinheit() {
		$lehreinheit_id = $this->input->get('lehreinheit_id');
		$lv_id = $this->input->get('lv_id');
		$sem_kurzbz = $this->input->get('sem_kurzbz');

		if(!$lehreinheit_id || !$lv_id || !$sem_kurzbz) {
			$this->terminateWithError($this->p->t('global', 'missingParameters'), 'general');
		}

		$this->assertLvAccess($lv_id, $sem_kurzbz);

		$lehrende = array();
		foreach($this->lehrendeDerLehreinheit($lehreinheit_id) as $l) {
			$lehrende[] = array(
				'mitarbeiter_uid' => $l->mitarbeiter_uid,
				'vorname' => $l->vorname,
				'nachname' => $l->nachname
			);
		}

		$this->terminateWithSuccess($lehrende);
	}

	/**
	 * GET 'studiengang_kz', 'sem_kurzbz'. Courses of one Studiengang for the Assistenz flow; only
	 * programmes the caller is entitled for.
	 */
	public function getLvForStudiengang() {
		$studiengang_kz = $this->input->get("studiengang_kz", TRUE);
		$sem_kurzbz = $this->input->get("sem_kurzbz", TRUE);

		if (!isset($studiengang_kz) || isEmptyString($studiengang_kz)
			|| !isset($sem_kurzbz) || isEmptyString($sem_kurzbz)) {
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		}

		$this->load->library('PermissionLib');
		$entitledStgs = $this->permissionlib->getSTG_isEntitledFor('lehre/benotungstool_assistenz');
		$isAdmin = $this->permissionlib->isBerechtigt('admin');

		if (!$isAdmin && (!is_array($entitledStgs) || !in_array($studiengang_kz, $entitledStgs))) {
			$this->terminateWithError($this->p->t('ui', 'keineBerechtigung'), 'general');
		}

		$result = $this->LehrveranstaltungModel->getLvForStudiengangInSemester($sem_kurzbz, $studiengang_kz);
		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);
	}

	public function getNoteByPunkte() {
		$result = $this->getPostJSON();
		
		if(!property_exists($result, 'punkte') 
			|| !property_exists($result, 'lv_id')
			|| !property_exists($result, 'sem_kurzbz')) {
			$this->terminateWithError($this->p->t('global', 'missingParameters'), 'general');
		}

		$punkte = $result->punkte;
		$lv_id = $result->lv_id;
		$sem_kurzbz = $result->sem_kurzbz;

		// the grading scale belongs to the course, therefore the same scope as every other endpoint
		$this->assertLvAccess($lv_id, $sem_kurzbz);

		$result = $this->NotenschluesselaufteilungModel->getNote($punkte, $lv_id, $sem_kurzbz);
		$data = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($data);
		
	}

	/**
	 * GET METHOD
	 * returns List of all available & active NotenOptions 
	 */
	public function getNoten() {
		$this->load->model('education/Note_model', 'NoteModel');

		// the controller holds the configuration; the model only builds the order
		$result = $this->NoteModel->getAllActive($this->config->item('NOTEN_SORTIERUNG'));
		$noten = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($noten);
	}

	/**
	 * GET METHOD
	 * should return Notenvorschlag for single Students, not used anywhere but required as per
	 * https://openproject.technikum-wien.at/projects/fh-complete/work_packages/60873/activity
	 */
	public function getNotenvorschlagStudent() {
		$uid = $this->input->get("uid",TRUE);

		// if uid is missing or empty, fall back to getAuthUID()
		if ($uid === NULL || trim((string)$uid) === '') {
			$uid = getAuthUID();
		}

		$sem_kurzbz = $this->input->get("sem_kurzbz",TRUE);
		$lv_id = $this->input->get("lv_id",TRUE);

		if ($uid === NULL || trim((string)$uid) === ''
			|| $sem_kurzbz === NULL || trim((string)$sem_kurzbz) === ''
			|| $lv_id === NULL || trim((string)$lv_id) === '') {
			$this->terminateWithError($this->p->t('global', 'missingParameters'), 'general');
		}

		$this->assertLvAccess($lv_id, $sem_kurzbz);
		

		$result = $this->LvgesamtnoteModel->getLvGesamtNoteVorschlag($lv_id, $uid, $sem_kurzbz);
		$data = $this->getDataOrTerminateWithError($result);
		
		// TODO: moodle teilnote but it seems they only work for a whole course?
		
		// get anw% of student by prestudent_id
//		$anwresult = $this->getAnwesenheiten($prestudent_ids, $lv_id, $sem_kurzbz);



		$this->terminateWithSuccess($data);
	}

	/**
	 * GET 'lv_id', 'sem_kurzbz'. Students of the course with their grades, the Teilnoten from
	 * getExternalGrades, the averaged Notenvorschlag and every exam of the semester.
	 */
	public function getStudentenNoten() {
		$lv_id = $this->input->get("lv_id",TRUE);
		$sem_kurzbz = $this->input->get("sem_kurzbz",TRUE);

		if (!isset($lv_id) || isEmptyString($lv_id)
			|| !isset($sem_kurzbz) || isEmptyString($sem_kurzbz))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');

		$this->assertLvAccess($lv_id, $sem_kurzbz);

		// get studenten for lva & sem with zeugnisnote if available
		$studenten = $this->LehrveranstaltungModel->getStudentsByLv($sem_kurzbz, $lv_id);
		$studentenData = $this->getDataOrTerminateWithError($studenten);
		
		if(count($studentenData) == 0) {
			$this->terminateWithError($this->p->t('benotungstool', 'c4keineStudentenGefunden'));
		}
		
		$func = function ($value) {
			return $value->uid;
		};
		
		$grades = array();
		$student_uids = array_map($func, $studentenData);

		$funcpre = function ($value) {
			return $value->prestudent_id;
		};
		
		$prestudent_ids = array_map($funcpre, $studentenData);
		
		if(count($student_uids) > 0) {
			$mobres = $this->MobilitaetModel->getMobilityZusatzForUids($student_uids);
			$mobData = $this->getDataOrTerminateWithError($mobres);

			$result = $this->ErhalterModel->load();
			$erhalter = getData($result)[0];
			
			$erhalter_kz = '9' . sprintf("%03s", $erhalter->erhalter_kz);
			foreach($mobData as $mob) {
				$grades[$mob->uid]['mobility_zusatz'] = $this->MobilitaetModel->formatZusatz($mob, $erhalter_kz);
			}
		}
		
		// the course grade of each student, read once. The history below needs the same rows.
		$lvNotenRows = array();

		foreach($student_uids as $uid) {
			$grades[$uid]['grades'] = [];

			// Read without the filter. getLvGesamtNoten() gives released grades only. With that getter
			// the course grade and the release state are empty after a reload.
			$lvgesamtnote = $this->getLvGesamtnoteRow($lv_id, $uid, $sem_kurzbz);
			$lvNotenRows[$uid] = $lvgesamtnote;

			if($lvgesamtnote !== null) {
				$grades[$uid]['note_lv'] = $lvgesamtnote->note;
				$grades[$uid]['freigabedatum'] = $lvgesamtnote->freigabedatum;
				$grades[$uid]['benotungsdatum'] = $lvgesamtnote->benotungsdatum;
				$grades[$uid]['punkte_lv'] = $lvgesamtnote->punkte;
			} else {
				$grades[$uid]['note_lv'] = null;
				$grades[$uid]['freigabedatum'] = null;
				$grades[$uid]['benotungsdatum'] = null;
				$grades[$uid]['punkte_lv'] = null;
			}
		}

		// send $grades reference to moodle addon
		try {
			Events::trigger(
				'getExternalGrades',
				function & () use (&$grades)
				{
					return $grades;
				},
				[
					'lvid' => $lv_id,
					'stsem' => $sem_kurzbz
				]
			);
		} catch (Throwable $t) {
//			$this->addMeta('throwable', $t->getTrace());
			$this->addMeta('getExternalGradesError', $t->getMessage());
		}
		
		// assign the anw% to the students in the studentData loop
		$anwresult = $this->getAnwesenheiten($prestudent_ids, $lv_id, $sem_kurzbz);
		
		// calculate the grade proposals from the partial grades
		foreach($studentenData as $student) {
			
			// null when the Anwesenheiten addon is absent - the column stays empty in the UI
			$student->anwquote = $anwresult[$student->prestudent_id] ?? null;
			
			$g = $grades[$student->uid]['grades'];
			$note_lv = $grades[$student->uid]['note_lv'];
			
			// overwrite any calculation with lv note once available
			if(!is_null($note_lv)) {
				$student->note_vorschlag = $note_lv;
			} else if(count($g) > 0) {
				
				$notensumme = 0;
				$notensumme_gewichtet = 0;
				$gewichtsumme = 0;
				$punktesumme = 0;
				$punktesumme_gewichtet = 0;
				$anzahlnoten = 0;
				foreach($g as $teilnote) {
					$note = $teilnote['grade'] ?? null;
					$punkte = $teilnote['points'] ?? null;
					$gewicht = is_numeric($teilnote['weight'] ?? null) ? $teilnote['weight'] : 0;

					$hatNote = is_numeric($note);
					$hatPunkte = is_numeric($punkte);

					// A partial entry counts only for the value that the mode uses. A row without a
					// grade adds zero in the grade mode and still raises the divisor, which makes the
					// average better than the performance.
					if(!(CIS_GESAMTNOTE_PUNKTE ? $hatPunkte : $hatNote)) continue;

					if($hatNote) {
						$notensumme += $note;
						$notensumme_gewichtet += $note * $gewicht;
					}

					if($hatPunkte) {
						$punktesumme += $punkte;
						$punktesumme_gewichtet += $punkte * $gewicht;
					}

					$gewichtsumme += $gewicht;
					$anzahlnoten += 1;
				}
				
				// Without a partial grade that counts there is no average. Without this guard the
				// division uses zero. PHP 7 gives INF, and INF gives the best grade. PHP 8 stops with
				// a fatal error and the full grade table stays empty.
				$gewichtet = defined('CIS_GESAMTNOTE_GEWICHTUNG') && CIS_GESAMTNOTE_GEWICHTUNG;
				$divisor = $gewichtet ? $gewichtsumme : $anzahlnoten;

				if ($divisor > 0) {
					if (CIS_GESAMTNOTE_PUNKTE) {
						$punkte_vorschlag = round(($gewichtet ? $punktesumme_gewichtet : $punktesumme) / $divisor,
							$this->punkteNachkommastellen());
						$note_vorschlag_result = $this->NotenschluesselaufteilungModel->getNote($punkte_vorschlag, $lv_id, $sem_kurzbz);
						$note_vorschlag = $this->getDataOrTerminateWithError($note_vorschlag_result);
					} else {
						$note_vorschlag = $this->rundeNote(($gewichtet ? $notensumme_gewichtet : $notensumme) / $divisor);
					}

					$student->note_vorschlag = $note_vorschlag;
				}
			}
		}
		
		// get all exams with grades of that semester and that course
		$pruefungen = $this->LePruefungModel->getPruefungenByLvStudiensemester($lv_id, $sem_kurzbz);
		$pruefungenData = getData($pruefungen);

		// the server derives the history for each student; the client only reads it
		$proStudent = [];
		foreach($pruefungenData ?: [] as $p) {
			$proStudent[$p->student_uid][] = $p;
		}

		// the transcript grades come with the student list (tbl_zeugnisnote.note)
		$zeugnisnoten = [];
		foreach($studentenData as $s) $zeugnisnoten[$s->uid] = $s->note;

		$pruefungenAbgeleitet = [];
		foreach(array_unique(array_merge($student_uids, array_keys($proStudent))) as $uid) {
			$lvNote = isset($grades[$uid]) ? ($grades[$uid]['note_lv'] ?? null) : null;
			$verlauf = $this->VerlaufLib->buildVerlauf($proStudent[$uid] ?? [], $lvNote, $zeugnisnoten[$uid] ?? null);

			foreach($verlauf->pruefungen as $p) $pruefungenAbgeleitet[] = $p;

			if(isset($grades[$uid])) {
				// the row comes from the loop above; a second read gives the same answer
				$grades[$uid]['verlauf'] = $this->verlaufSummary(
					$verlauf, false, isset($lvNotenRows[$uid]) && $lvNotenRows[$uid] !== null
				);
			}
		}

		$this->terminateWithSuccess(array($studentenData, $pruefungenAbgeleitet, DOMAIN, $grades, $anwresult));
	}

	/**
	 * POST 'sem_kurzbz', 'lv_id', 'student_uid', 'note'. Writes the LV-Note and the benotungsdatum,
	 * which drives the offen/changed/freigegeben state.
	 */
	public function saveNotenvorschlag() {
		// role first: a caller without the action fails on the right, not on missing parameters
		$this->assertAktion('vorschlag');

		$result = $this->getPostJSON();

		if(!property_exists($result, 'lv_id') || !property_exists($result, 'sem_kurzbz') ||
			!property_exists($result, 'student_uid') || !property_exists($result, 'note')) {
			$this->terminateWithError($this->p->t('global', 'missingParameters'), 'general');
		}

		$lv_id = $result->lv_id;
		$student_uid = $result->student_uid;
		$sem_kurzbz = $result->sem_kurzbz;
		$note = $result->note;
		$punkte = $result->punkte;

		// the day the assessment took place. The dialog sends it, older callers do not.
		$datum = property_exists($result, 'datum') ? $result->datum : null;

		$this->assertLvAccess($lv_id, $sem_kurzbz);

		// examination rules: no entry and no change after the grade entry deadline
		$this->enforceNoteneintragungsfrist($sem_kurzbz);

		// In the points mode the grading scale decides, not the grade from the client. If not, you
		// get a course grade that contradicts its own points. Without points there is nothing to
		// derive (for example a proposal from Moodle partial grades), then the given grade applies.
		if(CIS_GESAMTNOTE_PUNKTE && $punkte !== null && $punkte !== '') {
			$abgeleitet = $this->noteAusPunkten($punkte, $lv_id, $sem_kurzbz, $student_uid);
			if(is_string($abgeleitet)) $this->terminateWithError($abgeleitet, 'general');
			$note = $abgeleitet;
		}

		$fehler = $this->validateNotenvorschlag($lv_id, $student_uid, $sem_kurzbz, $note);
		if($fehler !== null) $this->terminateWithError($fehler, 'general');

		$fehler = $this->validateBenotungsdatum($datum, $student_uid, $sem_kurzbz);
		if($fehler !== null) $this->terminateWithError($fehler, 'general');

		// Der gewählte Tag ist das Datum von Antritt 1, nicht das benotungsdatum. Das benotungsdatum
		// bleibt der Zeitpunkt der Eingabe, weil die Freigabe es mit dem freigabedatum vergleicht:
		// ein Tag in der Vergangenheit liesse die geänderte Note als freigegeben erscheinen.
		$erstantrittDatum = $datum === null || $datum === '' ? date("Y-m-d") : substr((string) $datum, 0, 10);
		$lvgesamtnote = null;

		$result = $this->LvgesamtnoteModel->getLvGesamtNoteVorschlag($lv_id, $student_uid, $sem_kurzbz);

//		$this->addMeta('LvgesamtnoteModelresult', $result);
		
		if(!isError($result) && hasData($result)) {
			$lvgesamtnote = getData($result)[0];
			
			$id = $this->LvgesamtnoteModel->update(
				[$lvgesamtnote->student_uid, $lvgesamtnote->studiensemester_kurzbz, $lvgesamtnote->lehrveranstaltung_id],
				array(
					'note' => $note,
					'punkte' => $punkte,
					'benotungsdatum' => date("Y-m-d H:i:s"),
					'updateamum' => date("Y-m-d H:i:s"),
					'updatevon' => getAuthUID()
				)
			);

			$res = null;
			if($id) {
				$res = $this->LvgesamtnoteModel->load($id->retval);
				if(hasData($res)) $lvgesamtnote = getData($res)[0];
			}

			$this->logLib->logInfoDB(array('saveNotenvorschlag update lv gesamtnote',$res, getAuthUID(), getAuthPersonId()));

		} else if(!isError($result) && !hasData($result)) {
			$id = $this->LvgesamtnoteModel->insert(
				array(
					'student_uid' => $student_uid,
					'lehrveranstaltung_id' => $lv_id,
					'studiensemester_kurzbz' => $sem_kurzbz,
					'note' => $note,
					'punkte' => $punkte,
					'mitarbeiter_uid' => $this->benotenderMitarbeiterFuerStudent($lv_id, $student_uid, $sem_kurzbz),
					'benotungsdatum' => date("Y-m-d H:i:s"),
					'freigabedatum' => null,
					'freigabevon_uid' => null,
					'bemerkung' => null,
					'updateamum' => null,
					'updatevon' => null,
					'insertamum' => date("Y-m-d H:i:s"),
					'insertvon' => getAuthUID()
				)
			);
			$res = null;
			if($id) {
				$res = $this->LvgesamtnoteModel->load($id->retval);
				if(hasData($res)) $lvgesamtnote = getData($res)[0];
			}

			$this->logLib->logInfoDB(array('saveNotenvorschlag insert lv gesamtnote',$res, getAuthUID(), getAuthPersonId()));
		}

		// Ohne geschriebene LV-Note entsteht kein Antritt: eine Prüfung ohne Note ist ein Zustand,
		// den jeder andere Pfad ablehnt (c4keineLvNoteEingetragen).
		if($lvgesamtnote === null) {
			$this->terminateWithError($this->p->t('benotungstool', 'c4pruefungNichtGespeichert', [$student_uid]), 'general');
		}

		// The course grade IS the first attempt. Write it as its own exam now, or the next exam
		// becomes attempt 2 and the legacy type of the whole chain moves one place.
		$this->erstantrittBeiUebernahme($lv_id, $student_uid, $sem_kurzbz, $note, $punkte, $erstantrittDatum);

		// the client shows the new attempt at once, without a reload. The row is the one just written.
		$lvgesamtnote->verlauf = $this->buildVerlaufSummary($student_uid, $lv_id, $sem_kurzbz, $lvgesamtnote);

		$this->terminateWithSuccess(array($lvgesamtnote));
	}

	/**
	 * POST 'sem_kurzbz', 'lv_id', 'noten'. Bulk saveNotenvorschlag for the CSV import; the answer
	 * is keyed by uid and holds the course grade or an error per row.
	 */
	public function saveNotenvorschlagBulk() {
		// role first: a caller without the action fails on the right, not on missing parameters
		$this->assertAktion('import');

		$result = $this->getPostJSON();

		if(!property_exists($result, 'lv_id') || !property_exists($result, 'sem_kurzbz') ||
			!property_exists($result, 'noten')) {
			$this->terminateWithError($this->p->t('global', 'missingParameters'), 'general');
		}
		
		$lv_id = $result->lv_id;
		$sem_kurzbz = $result->sem_kurzbz;
		$noten = $result->noten;

		$this->assertLvAccess($lv_id, $sem_kurzbz);

		// examination rules: no entry and no change after the grade entry deadline
		$this->enforceNoteneintragungsfrist($sem_kurzbz);

		$retLvNoten = [];
		
		foreach($noten as $note)
		{
			// je Zeile neu: sonst trägt die Variable die Zeile davor, und eine gescheiterte Zeile
			// meldet die Note der vorherigen Person zurück
			$lvgesamtnote = null;

			$result = $this->LvgesamtnoteModel->getLvGesamtNoteVorschlag($lv_id, $note->uid, $sem_kurzbz);
//			$this->addMeta($note->uid.'$result', $result);
			
			if(CIS_GESAMTNOTE_PUNKTE) {
				$abgeleitet = $this->noteAusPunkten($note->punkte, $lv_id, $sem_kurzbz, $note->uid);
				// no grade can be derived: skip the row, but do not stop the full request
				if(is_string($abgeleitet)) {
					$retLvNoten[$note->uid] = $abgeleitet;
					if($this->importBrichtAb()) break;
					continue;
				}
				$note->note = $abgeleitet;
			}

			// one bad row must not stop the import, so the message goes into this row
			$fehler = $this->validateNotenvorschlag($lv_id, $note->uid, $sem_kurzbz, $note->note);
			if($fehler !== null) {
				$retLvNoten[$note->uid] = $fehler;
				// stop here, or keep writing the remaining rows
				if($this->importBrichtAb()) break;
				continue;
			}

			if(!isError($result) && hasData($result)) {
				$lvgesamtnote = getData($result)[0];

				$id = $this->LvgesamtnoteModel->update(
					[$lvgesamtnote->student_uid, $lvgesamtnote->studiensemester_kurzbz, $lvgesamtnote->lehrveranstaltung_id],
					array(
						'note' => trim($note->note),
						'punkte' => $note->punkte,
						'benotungsdatum' => date("Y-m-d H:i:s"),
						'updateamum' => date("Y-m-d H:i:s"),
						'updatevon' => getAuthUID()
					)
				);

				$res = null;
				if($id) {
					$res = $this->LvgesamtnoteModel->load($id->retval);
					if(hasData($res)) $lvgesamtnote = getData($res)[0];
				}

				$this->logLib->logInfoDB(array('saveNotenvorschlagBulk update lv gesamtnote',$res, getAuthUID(), getAuthPersonId()));

			} else if(!isError($result) && !hasData($result)) {
				$id = $this->LvgesamtnoteModel->insert(
					array(
						'student_uid' => $note->uid,
						'lehrveranstaltung_id' => $lv_id,
						'studiensemester_kurzbz' => $sem_kurzbz,
						'note' => trim($note->note),
						'punkte' => $note->punkte,
						'mitarbeiter_uid' => $this->benotenderMitarbeiterFuerStudent($lv_id, $note->uid, $sem_kurzbz),
						'benotungsdatum' => date("Y-m-d H:i:s"),
						'freigabedatum' => null,
						'freigabevon_uid' => null,
						'bemerkung' => null,
						'updateamum' => null,
						'updatevon' => null,
						'insertamum' => date("Y-m-d H:i:s"),
						'insertvon' => getAuthUID()
					)
				);
				$res = null;
				if($id) {
					$res = $this->LvgesamtnoteModel->load($id->retval);
					if(hasData($res)) $lvgesamtnote = getData($res)[0];
				}

				$this->logLib->logInfoDB(array('saveNotenvorschlagBulk insert lv gesamtnote',$res, getAuthUID(), getAuthPersonId()));
			}

			// Ohne geschriebene LV-Note entsteht kein Antritt, und die Zeile meldet den Fehler
			if($lvgesamtnote === null) {
				$retLvNoten[$note->uid] = $this->p->t('benotungstool', 'c4pruefungNichtGespeichert', [$note->uid]);
				continue;
			}

			// the same rule as the single dialog: the course grade is attempt 1
			$this->erstantrittBeiUebernahme($lv_id, $note->uid, $sem_kurzbz, trim($note->note), $note->punkte, date("Y-m-d"));

			$lvgesamtnote->verlauf = $this->buildVerlaufSummary($note->uid, $lv_id, $sem_kurzbz, $lvgesamtnote);

			$retLvNoten[$note->uid] = $lvgesamtnote;
		}

		$this->terminateWithSuccess($retLvNoten);
	}

	/**
	 * POST METHOD
	 * expects 'lv_id', 'sem_kurzbz', 'pruefungen'
	 * Bulk variant of saveStudentPruefung, used when importing pruefungsdata from csv with available noten.
	 */
	public function savePruefungenBulk() {
		// role first: a caller without the action fails on the right, not on missing parameters
		$this->assertAktion('import');

		$result = $this->getPostJSON();

		if(!property_exists($result, 'lv_id') || !property_exists($result, 'sem_kurzbz') ||
			!property_exists($result, 'pruefungen')) {
			$this->terminateWithError($this->p->t('global', 'missingParameters'), 'general');
		}

		$lv_id = $result->lv_id;
		$sem_kurzbz = $result->sem_kurzbz;
		$pruefungen = $result->pruefungen;

		$this->assertLvAccess($lv_id, $sem_kurzbz);

		// examination rules: no entry after the grade entry deadline
		$this->enforceNoteneintragungsfrist($sem_kurzbz);

		$ret = [];

		foreach ($pruefungen as $pruefung) {

			if(CIS_GESAMTNOTE_PUNKTE) {
				$note = $this->noteAusPunkten($pruefung->punkte, $lv_id, $sem_kurzbz, $pruefung->uid);
				// no grade can be derived: skip the row, but do not stop the full request
				if(is_string($note)) {
					$ret[$pruefung->uid] = $note;
					if($this->importBrichtAb()) break;
					continue;
				}
				$pruefung->note = $note;
			}

			// the same as the dialog in the table, but for each import row
			$ret[$pruefung->uid] = $this->savePruefungFuerStudent(
				null, $pruefung->uid, $lv_id, $sem_kurzbz, $pruefung->lehreinheit_id,
				$pruefung->note, $pruefung->punkte, $pruefung->datum,
				property_exists($pruefung, 'mitarbeiter_uid') ? $pruefung->mitarbeiter_uid : null
			);
		}

		$this->logLib->logInfoDB(array('savePruefungenBulk',$ret, getAuthUID(), getAuthPersonId()));
		
		$this->terminateWithSuccess($ret);
	}

	/**
	 * POST 'lv_id', 'sem_kurzbz', 'password', 'noten'. Releases the grades: sets freigabedatum,
	 * which drives the offen/changed/freigegeben state, and mails a confirmation table.
	 */
	public function saveStudentenNoten() {
		// role first: a caller without the action fails on the right, not on missing parameters
		$this->assertAktion('freigabe');

		$result = $this->getPostJSON();

		if(!property_exists($result, 'sem_kurzbz') || !property_exists($result, 'lv_id') || 
			!property_exists($result, 'password') || !property_exists($result, 'noten')) {
			$this->terminateWithError($this->p->t('global', 'missingParameters'), 'general');
		}
		
		// second factor for a binding grade; an installation may drop it
		if($this->config->item('CIS_GESAMTNOTE_FREIGABE_PASSWORT') !== false
			&& !$this->AuthLib->checkUserAuthByUsernamePassword(getAuthUID(), $result->password)->retval) {
			$this->terminateWithError($this->p->t('password', 'wrongPassword'), 'general');
		}
		
		$lv_id = $result->lv_id;
		$sem_kurzbz = $result->sem_kurzbz;

		$this->assertLvAccess($lv_id, $sem_kurzbz);

		$ret = [];

		$res = $this->LehrveranstaltungModel->load($lv_id);
		if(isError($res) || !hasData($res)) {
			$this->terminateWithError($this->p->t('benotungstool', 'noValidLvFoundForId', [$lv_id]));
		}

		$lv = getData($res)[0];

		$studiengang_kz = $lv->studiengang_kz;
		$res = $this->StudiengangModel->load($studiengang_kz);
		if(isError($res) || !hasData($res)) {
			$this->terminateWithError($this->p->t('benotungstool', 'noValidStudiengangFoundForId', [$studiengang_kz]));
		}
		$sg = getData($res)[0];
		$lvaFullName = $sg->kurzbzlang . ' ' . $lv->semester . '.Semester
					' . $lv->bezeichnung . " - " .$lv->lehrform_kurzbz. " " . $lv->orgform_kurzbz . " - " . $sem_kurzbz;
		
		$emails = explode(', ', $sg->email);
		

		$res = $this->PersonModel->load(getAuthPersonId());
		if(isError($res) || !hasData($res)) {
			$this->terminateWithError($this->p->t('benotungstool', 'noValidPersonFoundForId', [getAuthPersonId()]));
		}
		$pers = getData($res)[0];
		$lektorFullName = $pers->anrede.' '.$pers->vorname.' '.$pers->nachname; //.' ('.$pers->kurzbz.')';

		
		$res = $this->StudienplanModel->getStudienplanByLvaSemKurzbz($lv_id, $sem_kurzbz);
		$data = getData($res);
		$studienplan_bezeichnung = '';
		foreach ($data as $row) {
			$studienplan_bezeichnung .= $row->bezeichnung . ' ';
		}
		$betreff = $this->p->t('benotungstool','notenfreigabe').' ' . $lv->bezeichnung . ' ' . $lv->orgform_kurzbz . ' - ' . $studienplan_bezeichnung;
		
		// The mail names the person and the released grade. Both come from the database: a value from
		// the request could carry markup, and it could name a grade that was never released.
		$studenten = array();
		$resStud = $this->LehrveranstaltungModel->getStudentsByLv($sem_kurzbz, $lv_id);
		if(!isError($resStud) && hasData($resStud)) {
			foreach(getData($resStud) as $s) $studenten[$s->uid] = $s;
		}
		$notenBezeichnungen = $this->aktiveNoten();

		$studlist = "<table border='1'><tr>";

		if (defined('CIS_GESAMTNOTE_FREIGABEMAIL_NOTE') && CIS_GESAMTNOTE_FREIGABEMAIL_NOTE) {
			$studlist .= "<td><b>" . $this->p->t('person','personenkennzeichen') . "</b></td>\n
			<td><b>" . $this->p->t('lehre','studiengang') . "</b></td>\n
			<td><b>" . $this->p->t('benotungstool','c4nachname') . "</b></td>\n
			<td><b>" . $this->p->t('benotungstool','c4vorname') . "</b></td>\n";
			if(defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE) {
				$studlist .= "<td><b>" . $this->p->t('benotungstool','c4punkte') . "</b></td>\n";
			}
			$studlist .= "<td><b>" . $this->p->t('benotungstool','c4grade') . "</b></td>\n";
			$studlist .= "<td><b>" . $this->p->t('ui','bearbeitetVon') . "</b></td></tr>\n";
		} else {
			$studlist .= "<td><b>" . $this->p->t('person','uid') . "</b></td></tr>\n";
		}
		
		foreach($result->noten as $note) {

			$resultLVGes = $this->LvgesamtnoteModel->getLvGesamtNoteVorschlag($lv_id, $note->uid, $sem_kurzbz);
			if (!isError($resultLVGes) && hasData($resultLVGes))
			{
				$lvgesamtnote = getData($resultLVGes)[0];

				// only what changed since the last release; same as the old tool
				if ($lvgesamtnote->benotungsdatum > $lvgesamtnote->freigabedatum)
				{

					$id = $this->LvgesamtnoteModel->update(
						[$lvgesamtnote->student_uid, $lvgesamtnote->studiensemester_kurzbz, $lvgesamtnote->lehrveranstaltung_id],
						array(
							'note' => $lvgesamtnote->note,
							'freigabevon_uid' => getAuthUID(),
							'freigabedatum' => date("Y-m-d H:i:s"),
							'updateamum' => date("Y-m-d H:i:s"),
							'updatevon' => getAuthUID()
						)
					);

					if($id) {
						$res = $this->LvgesamtnoteModel->load($id->retval);
						if(hasData($res)) {
							$lvgesamtnote = getData($res)[0];

							// The release makes the grade binding, therefore the first exam starts
							// here. A new exam never creates a second exam in addition.
							$this->upsertErstantritt(
								$lv_id, $lvgesamtnote->student_uid, $sem_kurzbz,
								$lvgesamtnote->note, $lvgesamtnote->punkte, $lvgesamtnote->benotungsdatum
							);

							// The verlauf goes back with the answer, so the table shows the new exam
							// at once. Without it the row updates only after a reload.
							$ret[] = array(
								'uid' => $note->uid,
								'freigabedatum' => $lvgesamtnote->freigabedatum,
								'benotungsdatum' => $lvgesamtnote->benotungsdatum,
								// the row is the one the release just wrote
								'verlauf' => $this->buildVerlaufSummary($note->uid, $lv_id, $sem_kurzbz, $lvgesamtnote)
							);
						}
					}
					 
					if (defined('CIS_GESAMTNOTE_FREIGABEMAIL_NOTE') && CIS_GESAMTNOTE_FREIGABEMAIL_NOTE)
					{
						$stud = isset($studenten[$note->uid]) ? $studenten[$note->uid] : null;

						$noteKey = (string) $lvgesamtnote->note;
						$noteBez = isset($notenBezeichnungen[$noteKey])
							? $notenBezeichnungen[$noteKey]->bezeichnung
							: $noteKey;

						$studlist .= "<tr><td>" . $this->mailZelle($stud ? $stud->matrikelnr : $note->uid) . "</td>";
						$studlist .= "<td>" . $this->mailZelle($stud ? $stud->kuerzel : '') . "</td>";
						$studlist .= "<td>" . $this->mailZelle($stud ? $stud->nachname : '') . "</td>";
						$studlist .= "<td>" . $this->mailZelle($stud ? $stud->vorname : '') . "</td>";

						if(defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE) {
							$studlist .= "<td>" . $this->mailZelle($lvgesamtnote->punkte) . "</td>";
						}
						$studlist .= "<td>" . $this->mailZelle($noteBez) . "</td>";

						$studlist .= "<td>" . $this->mailZelle($lvgesamtnote->mitarbeiter_uid);
						if ($lvgesamtnote->updatevon != '')
							$studlist .= " (" . $this->mailZelle($lvgesamtnote->updatevon) . ")";
						$studlist .= "</td></tr>";
					} else {
						$studlist .= "<tr><td>" . $this->mailZelle($note->uid) . "</td></tr>\n";
					}
				}
			}
		}
		$studlist .= "</table>";

		$this->logLib->logInfoDB(array('saveStudentenNoten', array(
			'updatevon' => getAuthUID(),
			'updateamum' => date('Y-m-d H:i:s')
		), getAuthUID(), getAuthPersonId(), array($result->noten, $lv_id, $sem_kurzbz)));
		
		// config toggles the mail itself; FREIGABEMAIL_NOTE toggles how much it carries
		if($this->config->item('CIS_GESAMTNOTE_FREIGABEMAIL') !== false) {
			$this->sendFreigabeEmail($lektorFullName, $lvaFullName, count($result->noten), $emails, $studlist, $betreff);
		}
		
		$this->terminateWithSuccess($ret);
	}

	/**
	 * POST 'datum' (YYYY-MM-DD), 'lva_id', 'student_uid', 'note'. Inserts or updates one exam and
	 * the course grade. Never writes the Zeugnisnote - Stv does that.
	 */
	public function saveStudentPruefung() { // einzelne pruefung speichern
		// role first: a caller without the action fails on the right, not on missing parameters
		$this->assertAktion('pruefung');

		$result = $this->getPostJSON();

		if(!property_exists($result, 'datum') || !property_exists($result, 'lva_id') ||
			!property_exists($result, 'student_uid') || !property_exists($result, 'note')) {
			$this->terminateWithError($this->p->t('global', 'missingParameters'), 'general');
		}

		$student_uid = $result->student_uid;
		$note = $result->note;
		$punkte = $result->punkte;
		$datum = $result->datum;
		$lva_id = $result->lva_id;
		$lehreinheit_id = $result->lehreinheit_id;
		// pruefung_id identifies the record being edited; null when a new pruefung is added
		$pruefung_id = property_exists($result, 'pruefung_id') ? $result->pruefung_id : null;

		$stsem = $result->sem_kurzbz;

		$this->assertLvAccess($lva_id, $stsem);

		// examination rules: no entry and no change after the grade entry deadline
		$this->enforceNoteneintragungsfrist($stsem);

		$jetzt = date("Y-m-d H:i:s");

		if(CIS_GESAMTNOTE_PUNKTE && isset($punkte) && $punkte >= 0) {
			// with a points entry the server checks the grade again and corrects it
			$resultNote = $this->NotenschluesselaufteilungModel->getNote($punkte, $lva_id, $stsem);
			if(isError($resultNote)) {
				$this->terminateWithError(getError($resultNote));
			} else {
				$data = getData($resultNote);
				if($data != $note)
				{
					$note = $data;
				}
			}
			
		}

		// TODO: more sophisticated empty check
		if($note=='') {
			// config names the grade, the lib resolves it in tbl_note
			$note = $this->VerlaufLib->getNoteNichtEingetragen();
		}

		// the dialog sends the teacher when the Lehreinheit has more than one
		$mitarbeiter_uid = property_exists($result, 'mitarbeiter_uid') ? $result->mitarbeiter_uid : null;

		$result = $this->savePruefungFuerStudent($pruefung_id, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum, $mitarbeiter_uid);

		// validation errors and write errors come back as a translated message
		if(is_string($result)) $this->terminateWithError($result, 'general');

		$savedPruefung = $result['savedPruefung'] ?? [];
		$savedPruefungData = count($savedPruefung) > 0 ? $savedPruefung[0] : null;
		$lvgesamtnote = $result['lvgesamtnote'] ?? null;

		$this->terminateWithSuccess(array($savedPruefungData, $lvgesamtnote, $result['verlauf'] ?? null));
	}

	/** Active grades by PK; getNoten() sends the client the same set. @return array note => tbl_note row */
	private function aktiveNoten()
	{
		if($this->aktiveNotenCache !== null) return $this->aktiveNotenCache;

		$this->aktiveNotenCache = array();

		$result = $this->NoteModel->getAllActive($this->config->item('NOTEN_SORTIERUNG'));
		if(!isError($result) && hasData($result)) {
			foreach(getData($result) as $n) $this->aktiveNotenCache[(string)$n->note] = $n;
		}

		return $this->aktiveNotenCache;
	}

	/** Stops the request when no role of the caller carries this action. */
	private function assertAktion($aktion)
	{
		if ($this->darfAktion($aktion)) return;

		$this->terminateWithError($this->p->t('benotungstool', 'aktionNichtErlaubt', [$aktion]), 'general');
	}

	/**
	 * Scopes access so a guessed URL cannot reach foreign grades: a teacher only their own courses
	 * in that Studiensemester, an Assistenz the courses of a Studiengang they are entitled for.
	 *
	 * The Studiensemester is mandatory. Without it a teacher who taught the course in ANY semester
	 * passes the check for every semester.
	 */
	private function assertLvAccess($lv_id, $sem_kurzbz)
	{
		$this->load->library('PermissionLib');

		// admins keep full access
		if ($this->permissionlib->isBerechtigt('admin')) return;

		// teachers: only their own LVs (assigned as lehreinheitmitarbeiter in this semester)
		if ($this->config->item('CIS_GESAMTNOTE_LEKTOR_NUR_EIGENE_LV') === false
			&& $this->permissionlib->isBerechtigt('lehre/benotungstool')) {
			return; // this installation lets a teacher grade every course
		}

		if ($this->permissionlib->isBerechtigt('lehre/benotungstool')) {
			$res = $this->LehrveranstaltungModel->getLektorIsTeachingLva($lv_id, getAuthUID(), $sem_kurzbz);
			$rows = getData($res);
			if (!isError($res) && !empty($rows) && $rows[0]->teaches > 0) return;
			// not a teacher of this LV -> fall through (a both-role user may still be entitled as Assistenz)
		}

		// (pure or additional) Assistenz: only LVs of an entitled Studiengang
		$entitledStgs = $this->permissionlib->getSTG_isEntitledFor('lehre/benotungstool_assistenz');
		$lv = null;
		if (is_array($entitledStgs) && count($entitledStgs) > 0) {
			$res = $this->LehrveranstaltungModel->load($lv_id);
			if (!isError($res) && hasData($res)) {
				$lv = getData($res)[0];
				if (in_array($lv->studiengang_kz, $entitledStgs)) return;
			}
		}

		if ($lv === null) {
			$res = $this->LehrveranstaltungModel->load($lv_id);
			if (!isError($res) && hasData($res)) $lv = getData($res)[0];
		}
		$bezeichnung = $lv !== null ? $lv->bezeichnung : $lv_id;

		$this->terminateWithError(
			$this->p->t('benotungstool', 'keineBerechtigungNoten', [$bezeichnung, $sem_kurzbz]),
			'general'
		);
	}

	/**
	 * uid of the grading person for tbl_pruefung and tbl_lvgesamtnote. An assistant may type it in,
	 * but the grade comes from the teacher; insertvon/updatevon keep the caller.
	 *
	 * Order: valid selection, caller if they teach it, the only teacher, the first teacher, caller.
	 *
	 * @return string
	 */
	private function benotenderMitarbeiter($lehreinheit_id, $gewaehlt = null)
	{
		$uids = array();
		foreach($this->lehrendeDerLehreinheit($lehreinheit_id) as $lehrend) $uids[] = $lehrend->mitarbeiter_uid;

		if(count($uids) === 0) return getAuthUID();
		if($gewaehlt !== null && $gewaehlt !== '' && in_array($gewaehlt, $uids)) return $gewaehlt;
		if(in_array(getAuthUID(), $uids)) return getAuthUID();

		return $uids[0];
	}

	/** Same, for a course grade: the student's Lehreinheit decides. @return string */
	private function benotenderMitarbeiterFuerStudent($lva_id, $student_uid, $stsem, $gewaehlt = null)
	{
		$lehreinheit_id = null;

		$resLe = $this->LehrveranstaltungModel->getLeByStudent($student_uid, $stsem, $lva_id);
		if(!isError($resLe) && hasData($resLe)) $lehreinheit_id = current(getData($resLe))->lehreinheit_id;

		return $this->benotenderMitarbeiter($lehreinheit_id, $gewaehlt);
	}

	/**
	 * Object initialization
	 */
	/**
	 * Permissions that open this tool = keys of CIS_GESAMTNOTE_ROLLENMATRIX.
	 * Reads the file directly: the constructor runs before CI can load a config.
	 *
	 * @return array
	 */
	private static function berechtigungenAusMatrix()
	{
		$config = array();
		$datei = APPPATH . 'config/noten.php';
		if (is_file($datei)) include $datei;

		$matrix = isset($config['CIS_GESAMTNOTE_ROLLENMATRIX']) ? $config['CIS_GESAMTNOTE_ROLLENMATRIX'] : null;
		if (!is_array($matrix) || count($matrix) === 0) {
			return array('lehre/benotungstool:rw', 'lehre/benotungstool_assistenz:rw');
		}

		$berechtigungen = array();
		foreach (array_keys($matrix) as $rolle) $berechtigungen[] = $rolle . ':rw';

		return $berechtigungen;
	}

	/**
	 * The history for the client. Each write answer contains it, so the client calculates nothing.
	 *
	 * The caller hands over what it already read. Only the exams are read again, because the write
	 * just changed them. false = not given, null = read and absent.
	 *
	 * @param stdClass|null|false $lvRow
	 * @param mixed|null|false    $zeugnisNote
	 */
	private function buildVerlaufSummary($student_uid, $lva_id, $stsem, $lvRow = false, $zeugnisNote = false)
	{
		if($lvRow === false) $lvRow = $this->getLvGesamtnoteRow($lva_id, $student_uid, $stsem);
		if($zeugnisNote === false) $zeugnisNote = $this->getZeugnisnote($lva_id, $student_uid, $stsem);

		$verlauf = $this->VerlaufLib->getVerlauf(
			$student_uid, $lva_id, $stsem,
			$lvRow ? $lvRow->note : null,
			$zeugnisNote
		);

		return $this->verlaufSummary($verlauf, true, $lvRow !== null);
	}

	/** The deadline from '{SS|WS}yyyy': SS in the same year, WS in the next year. @return DateTime|null */
	private function computeNoteneintragungsfrist($sem_kurzbz)
	{
		if(!is_string($sem_kurzbz) || strlen($sem_kurzbz) < 6) return null;

		$type = strtoupper(substr($sem_kurzbz, 0, 2));
		$year = (int) substr($sem_kurzbz, 2, 4);
		if($year <= 0) return null;

		if($type === 'SS') {
			$cfg = $this->config->item('NOTENEINTRAGUNGSFRIST_SS');
			$deadlineYear = $year;
		} elseif($type === 'WS') {
			$cfg = $this->config->item('NOTENEINTRAGUNGSFRIST_WS');
			$deadlineYear = $year + 1;
		} else {
			return null;
		}

		$month = (is_array($cfg) && isset($cfg['month'])) ? (int)$cfg['month'] : ($type === 'SS' ? 11 : 5);
		$day   = (is_array($cfg) && isset($cfg['day']))   ? (int)$cfg['day']   : 15;

		$deadline = new DateTime();
		$deadline->setDate($deadlineYear, $month, $day);
		$deadline->setTime(23, 59, 59);
		return $deadline;
	}

	/** Creates a course grade. The student administration writes it to the transcript. @return stdClass|null */
	private function createLvGesamtnote($lva_id, $student_uid, $stsem, $note, $punkte, $lehreinheit_id = null, $mitarbeiter_uid = null)
	{
		$jetzt = date("Y-m-d H:i:s");

		// the caller knows the Lehreinheit here, so no second lookup is needed
		$benotender = $lehreinheit_id
			? $this->benotenderMitarbeiter($lehreinheit_id, $mitarbeiter_uid)
			: $this->benotenderMitarbeiterFuerStudent($lva_id, $student_uid, $stsem, $mitarbeiter_uid);

		$id = $this->LvgesamtnoteModel->insert(
			array(
				'student_uid' => $student_uid,
				'lehrveranstaltung_id' => $lva_id,
				'studiensemester_kurzbz' => $stsem,
				'note' => $note,
				'punkte' => $punkte,
				'mitarbeiter_uid' => $benotender,
				'benotungsdatum' => $jetzt,
				'freigabedatum' => null,
				'freigabevon_uid' => null,
				'bemerkung' => null,
				'updateamum' => null,
				'updatevon' => null,
				'insertamum' => $jetzt,
				'insertvon' => getAuthUID()
			)
		);
		if(!$id) return null;

		$res = $this->LvgesamtnoteModel->load($id->retval);
		return hasData($res) ? getData($res)[0] : null;
	}

	/**
	 * May the caller perform this action? Several roles -> union. Empty matrix = no restriction.
	 *
	 * @return bool
	 */
	private function darfAktion($aktion)
	{
		$matrix = $this->config->item('CIS_GESAMTNOTE_ROLLENMATRIX');
		if (!is_array($matrix) || count($matrix) === 0) return true;

		$this->load->library('PermissionLib');

		foreach ($matrix as $rolle => $aktionen) {
			if (!is_array($aktionen) || !in_array($aktion, $aktionen)) continue;
			if ($this->permissionlib->isBerechtigt($rolle)) return true;
		}

		return false;
	}

	/** May the caller enter late? ENTRY only - an exam does not happen retroactively. @return bool */
	private function darfFristUeberschreiten()
	{
		$rollen = $this->config->item('CIS_GESAMTNOTE_FRIST_AUSNAHME');
		if(!is_array($rollen) || count($rollen) === 0) return false;

		$this->load->library('PermissionLib');

		foreach($rollen as $rolle) {
			if($this->permissionlib->isBerechtigt($rolle)) return true;
		}

		return false;
	}

	/** Stops the request if the grade entry deadline of the semester has passed. */
	private function enforceNoteneintragungsfrist($sem_kurzbz)
	{
		if(!$this->fristAktiv('CIS_GESAMTNOTE_FRIST_EINGABE')) return;

		// a named role may enter late; the exam date stays bound either way
		if($this->darfFristUeberschreiten()) return;

		$deadline = $this->computeNoteneintragungsfrist($sem_kurzbz);
		if($deadline === null) return;

		if(new DateTime() > $deadline) {
			$this->terminateWithError(
				$this->p->t('benotungstool', 'noteneintragungsfristVorbei', [$deadline->format('d.m.Y')]),
				'general'
			);
		}
	}

	/** All actions of the caller, for the client. @return array */
	private function erlaubteAktionen()
	{
		$matrix = $this->config->item('CIS_GESAMTNOTE_ROLLENMATRIX');
		if (!is_array($matrix)) return array();

		$aktionen = array();
		foreach ($matrix as $aktionenDerRolle) {
			if (!is_array($aktionenDerRolle)) continue;
			foreach ($aktionenDerRolle as $aktion) {
				if (!in_array($aktion, $aktionen) && $this->darfAktion($aktion)) $aktionen[] = $aktion;
			}
		}

		return $aktionen;
	}

	/**
	 * Writes the first attempt when somebody takes the course grade over, the same way the
	 * Studierendenverwaltung does it. CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME switches it off.
	 * A credited transcript grade forbids every exam.
	 */
	private function erstantrittBeiUebernahme($lv_id, $student_uid, $sem_kurzbz, $note, $punkte, $datum)
	{
		if(!$this->config->item('CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME')) return;
		if($this->VerlaufLib->istAnrechnungsnote($this->getZeugnisnote($lv_id, $student_uid, $sem_kurzbz))) return;

		// the person who enters the grade picks this date, therefore it also updates attempt 1
		$lehreinheit_id = $this->lehreinheitFuerStudent($lv_id, $student_uid, $sem_kurzbz);

		$geschrieben = $this->VerlaufLib->upsertErstantritt(
			$student_uid, $lv_id, $sem_kurzbz, $note, $punkte, $datum,
			$this->benotenderMitarbeiter($lehreinheit_id), true, $lehreinheit_id
		);

		if($geschrieben !== null) {
			$this->logLib->logInfoDB(array('erstantritt (uebernahme)', $student_uid, getAuthUID(), getAuthPersonId()));
		}
	}

	/**
	 * Recipients of the release mail: 'studiengang', 'aufrufer', or any entry containing '@'.
	 *
	 * @return array
	 */
	private function freigabeEmpfaenger($studiengangAdressen)
	{
		$konfiguriert = $this->config->item('CIS_GESAMTNOTE_FREIGABEMAIL_EMPFAENGER');
		if(!is_array($konfiguriert)) $konfiguriert = array('studiengang', 'aufrufer');

		$adressen = array();
		foreach($konfiguriert as $eintrag) {
			if($eintrag === 'studiengang') {
				foreach($studiengangAdressen as $adresse) {
					if(trim($adresse) !== '') $adressen[] = trim($adresse);
				}
				continue;
			}

			if($eintrag === 'aufrufer') { $adressen[] = getAuthUID() . '@' . DOMAIN; continue; }

			if(strpos((string) $eintrag, '@') !== false) $adressen[] = trim($eintrag);
		}

		return array_values(array_unique($adressen));
	}

	/**
	 * The grade of an excused date, or null. An addon knows the excuses, therefore the answer comes
	 * from an event. A failure of that addon must not stop the grade entry.
	 *
	 * Runs BEFORE the writes: the course grade and the exam must tell the same story.
	 *
	 * @param array $pruefungen   the exams of the student, read before the write
	 * @param mixed $pruefung_id  the edited row; it does not count against the occurrence limit
	 * @return mixed|null
	 */
	private function entschuldigungsNote($student_uid, $datum, $pruefungen, $pruefung_id)
	{
		$status = [];

		try {
			Events::trigger(
				'getEntschuldigungsStatusForStudentOnDate',
				function & () use (&$status)
				{
					return $status;
				},
				[
					'student_uid' => $student_uid,
					'datum' => $datum
				]
			);
		} catch (Throwable $t) {
			$this->addMeta('getEntschuldigungsStatusError', $t->getMessage());
			return null;
		}

		if(count($status) === 0 || $status[0] != true) return null;

		$entschuldigtNote = $this->VerlaufLib->getSpecialNotes()['entschuldigt'];
		if($entschuldigtNote === null) return null;

		// the rules were checked on the grade BEFORE the override, therefore check the limit again
		if($this->VerlaufLib->ueberschreitetNotenLimit($pruefungen, $entschuldigtNote, $pruefung_id)) {
			return null;
		}

		return $entschuldigtNote;
	}

	/** Does one of the two deadline checks apply? Falls back to the old combined key. @return bool */
	private function fristAktiv($key)
	{
		$wert = $this->config->item($key);
		if($wert !== null) return (bool) $wert;

		return (bool) $this->config->item('CIS_GESAMTNOTE_NOTENEINTRAGUNGSFRIST');
	}

	private function getAnwesenheiten($prestudent_ids, $lv_id, $sem_kurzbz) {

		$anwesenheiten = [];
		try {
			$downloadFunc = function ($anwesenheitenResult) use (&$anwesenheiten) {
				// map result rows by prestudent_uid to retrieve them by that key later on
				foreach ($anwesenheitenResult as $anw) {
					$anwesenheiten[$anw->prestudent_id] = $anw->sum;
				}
			};
			
			Events::trigger(
				'getAnwesenheitenForLvAndSemester',
				$prestudent_ids,
				$lv_id,
				$sem_kurzbz,
				$downloadFunc
			);
		} catch (Throwable $t) {
			$this->addMeta('getAnwesenheitenForLvAndSemester', $t->getMessage());
		}
		
		return $anwesenheiten;
		
	}

	/**
	 * Reads the course grade WITHOUT the filter. getLvGesamtNoten() uses 'freigabedatum < NOW()'
	 * and therefore hides a new grade. In this tool this wrapper is always the correct one.
	 */
	private function getLvGesamtnoteRow($lva_id, $student_uid, $stsem)
	{
		$res = $this->LvgesamtnoteModel->getLvGesamtNoteVorschlag($lva_id, $student_uid, $stsem);
		return (!isError($res) && hasData($res)) ? getData($res)[0] : null;
	}

	/** The transcript grade or null. Credited grades are there, not in the course grade. @return mixed|null */
	private function getZeugnisnote($lva_id, $student_uid, $stsem)
	{
		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');

		$res = $this->ZeugnisnoteModel->load([
			'studiensemester_kurzbz' => $stsem,
			'student_uid' => $student_uid,
			'lehrveranstaltung_id' => $lva_id
		]);

		return (!isError($res) && hasData($res)) ? getData($res)[0]->note : null;
	}

	/** Does a bulk path stop at the first rejected row? @return bool */
	private function importBrichtAb()
	{
		return (bool) $this->config->item('CIS_GESAMTNOTE_IMPORT_ABBRUCH');
	}

	/** Is this course grade row released? @return bool */
	private function istFreigegeben($lvRow)
	{
		return $lvRow !== null && $lvRow->freigabedatum !== null && $lvRow->freigabedatum !== '';
	}

	/** Lehreinheit of one student; the grading person and the exam row both need it. @return mixed|null */
	private function lehreinheitFuerStudent($lva_id, $student_uid, $stsem)
	{
		$key = $lva_id . '|' . $student_uid . '|' . $stsem;
		if(array_key_exists($key, $this->lehreinheitCache)) return $this->lehreinheitCache[$key];

		$this->lehreinheitCache[$key] = null;

		$resLe = $this->LehrveranstaltungModel->getLeByStudent($student_uid, $stsem, $lva_id);
		if(isError($resLe) || !hasData($resLe)) return null;

		$this->lehreinheitCache[$key] = current(getData($resLe))->lehreinheit_id;

		return $this->lehreinheitCache[$key];
	}

	/**
	 * The teachers of one Lehreinheit, from lehre.tbl_lehreinheitmitarbeiter.
	 *
	 * @return array
	 */
	private function lehrendeDerLehreinheit($lehreinheit_id)
	{
		if(!$lehreinheit_id) return array();

		$key = (string) $lehreinheit_id;
		if(isset($this->lehrendeCache[$key])) return $this->lehrendeCache[$key];

		$this->lehrendeCache[$key] = array();

		$this->load->model('education/Lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');
		$result = $this->LehreinheitmitarbeiterModel->getLektorenByLe($lehreinheit_id);
		if(isError($result) || !hasData($result)) return array();

		$lehrende = getData($result);
		usort($lehrende, function($a, $b) {
			return strcmp($a->mitarbeiter_uid, $b->mitarbeiter_uid);
		});

		$this->lehrendeCache[$key] = $lehrende;

		return $lehrende;
	}

	/** One cell of the release mail. The mail is HTML, therefore no value may carry markup. @return string */
	private function mailZelle($wert)
	{
		return htmlspecialchars(trim((string) $wert), ENT_QUOTES, 'UTF-8');
	}

	/** Grade from points for ONE bulk row, or a translated error so the caller skips it. @return mixed|string */
	private function noteAusPunkten($punkte, $lv_id, $sem_kurzbz, $uid)
	{
		$result = $this->NotenschluesselaufteilungModel->getNote($punkte, $lv_id, $sem_kurzbz);
		if(isError($result)) return getError($result);

		$note = getData($result);
		if($note === null || $note === '') {
			return $this->p->t('benotungstool', 'c4punkteKeineNoteErmittelt', [$uid]);
		}

		return $note;
	}

	/**
	 * Exam DATE against the deadline. enforceNoteneintragungsfrist asks the other question: the
	 * time of entry.
	 *
	 * @return string|null translated error or null
	 */
	private function pruefungsdatumNachFrist($sem_kurzbz, $datum, $student_uid)
	{
		if(!$this->fristAktiv('CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM')) return null;

		$deadline = $this->computeNoteneintragungsfrist($sem_kurzbz);
		if($deadline === null) return null;

		$tag = substr((string) $datum, 0, 10);
		if($tag === '' || $tag <= $deadline->format('Y-m-d')) return null;

		return $this->p->t('benotungstool', 'pruefungsdatumNachFrist', [$student_uid, $deadline->format('d.m.Y')]);
	}

	/** Decimals of the points average before the scale applies. @return int */
	private function punkteNachkommastellen()
	{
		$stellen = $this->config->item('CIS_GESAMTNOTE_VORSCHLAG_PUNKTE_STELLEN');

		return is_numeric($stellen) && (int) $stellen >= 0 ? (int) $stellen : 2;
	}

	/**
	 * Turns a rule answer into a text. Every message names the student first.
	 *
	 * @param array|null $fehler [phraseKey, extraParams]
	 * @return string|null
	 */
	private function regelFehlertext($fehler, $student_uid)
	{
		if($fehler === null) return null;

		return $this->p->t('benotungstool', $fehler[0], array_merge([$student_uid], $fehler[1]));
	}

	/**
	 * Rounds the partial-grade average. Smaller number = better grade, so 'besser' floors.
	 *
	 * @return int
	 */
	private function rundeNote($wert)
	{
		$modus = $this->config->item('CIS_GESAMTNOTE_VORSCHLAG_RUNDUNG');

		if($modus === 'besser') return (int) floor($wert);
		if($modus === 'schlechter') return (int) ceil($wert);

		return (int) round($wert);
	}

	/**
	 * One exam entry for ONE student: validate, write course grade, write exam. Zeugnisnote is
	 * never touched.
	 *
	 * Both writes run in ONE transaction. A course grade without its exam is a state that no rule
	 * describes, and the client would show an attempt that does not exist.
	 *
	 * @return array|string ['savedPruefung', 'lvgesamtnote', 'verlauf'] or an error message
	 */
	private function savePruefungFuerStudent($pruefung_id, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum, $mitarbeiter_uid = null)
	{
		// §7 and §11: every exam must take place before the grade entry deadline. The kommissionelle
		// Prüfung is the important case, because it decides if the student loses a semester.
		$fristError = $this->pruefungsdatumNachFrist($stsem, $datum, $student_uid);
		if($fristError !== null) return $fristError;

		// The context of the rules, read once. It travels with the call, so no method below reads the
		// same row again.
		// The course grade comes without the filter: a grade that is not released still exists. You
		// must update it, because a new insert breaks the primary key.
		$zeugnisNote = $this->getZeugnisnote($lva_id, $student_uid, $stsem);
		$bestehendeLvNote = $this->getLvGesamtnoteRow($lva_id, $student_uid, $stsem);
		$pruefungen = $this->VerlaufLib->getPruefungen($student_uid, $lva_id, $stsem);

		// validate before any write
		$regelError = $this->validatePruefung($student_uid, $note, $datum, $pruefung_id, $pruefungen, $bestehendeLvNote, $zeugnisNote);
		if($regelError !== null) return $regelError;

		// An excused date decides the grade before anything is written. If it decided later, the
		// course grade would carry the grade of the dialog and the exam would carry 'entschuldigt'.
		$entschuldigt = $this->entschuldigungsNote($student_uid, $datum, $pruefungen, $pruefung_id);
		if($entschuldigt !== null) $note = $entschuldigt;

		// the client does not always send it; without it the insert fails on the NOT NULL column
		if(!$lehreinheit_id) $lehreinheit_id = $this->lehreinheitFuerStudent($lva_id, $student_uid, $stsem);

		$jetzt = date("Y-m-d H:i:s");

		// this decides if the course grade is the implicit first attempt; read it before the update
		$origLvNote = $bestehendeLvNote ? $bestehendeLvNote->note : null;
		$lvgesamtnote = $bestehendeLvNote;

		$this->db->trans_begin();

		if($bestehendeLvNote === null) {
			$lvgesamtnote = $this->createLvGesamtnote($lva_id, $student_uid, $stsem, $note, $punkte, $lehreinheit_id, $mitarbeiter_uid);

			$this->logLib->logInfoDB(array('pruefung: lvnote angelegt', $student_uid, $lva_id, $stsem,
				$note, $punkte, getAuthUID(), getAuthPersonId()));
		} else {
			// An excused date is no performance. It documents the date and keeps the course grade, so
			// an earlier result survives.
			$lvNoteNeu = $entschuldigt !== null ? $origLvNote : $note;
			$lvPunkteNeu = $entschuldigt !== null ? $bestehendeLvNote->punkte : $punkte;

			// a repeat must not worsen the LV-Note; the exam row keeps the real grade
			if($entschuldigt === null
				&& $this->config->item('CIS_GESAMTNOTE_VERBESSERUNG_BESSERE_GEWINNT')
				&& $this->VerlaufLib->istSchlechter($note, $origLvNote)) {
				$lvNoteNeu = $origLvNote;
			}

			// state hangs on benotungsdatum > freigabedatum, so touching the date revokes the release
			$hebtAuf = $this->config->item('CIS_GESAMTNOTE_PRUEFUNG_HEBT_FREIGABE_AUF') !== false;

			$id = $this->LvgesamtnoteModel->update(
				[$bestehendeLvNote->student_uid, $bestehendeLvNote->studiensemester_kurzbz, $bestehendeLvNote->lehrveranstaltung_id],
				array(
					'note' => $lvNoteNeu,
					'punkte' => $lvPunkteNeu,
					'benotungsdatum' => $hebtAuf ? $jetzt : $bestehendeLvNote->benotungsdatum,
					'updateamum' => $jetzt,
					'updatevon' => getAuthUID()
				)
			);

			if($id) {
				$res = $this->LvgesamtnoteModel->load($id->retval);
				if(hasData($res)) $lvgesamtnote = getData($res)[0];
			}

			$this->logLib->logInfoDB(array('pruefung: lvnote aktualisiert', $student_uid, $lva_id, $stsem,
				$note, $punkte, getAuthUID(), getAuthPersonId()));
		}

		// save pruefung after updating lvnote, since pruefungspunkte get loaded by lv punkte
		$pruefungenChanged = $this->savePruefungstermin(
			$pruefung_id, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum,
			$origLvNote, $mitarbeiter_uid, $lvgesamtnote, $pruefungen, $zeugnisNote
		);

		if(is_string($pruefungenChanged)) {
			// the course grade alone is not a valid state, therefore it goes back as well
			$this->db->trans_rollback();
			return $pruefungenChanged;
		}

		$this->db->trans_commit();

		$pruefungenChanged['lvgesamtnote'] = $lvgesamtnote;

		return $pruefungenChanged;
	}

	/**
	 * Creates or updates an exam. The type carries no meaning here - position comes from the
	 * history; pruefungstyp_kurzbz is written but never read back.
	 *
	 * @param int                 $pruefung_id set = edit that row, null = new attempt
	 * @param stdClass|null       $lvgesamtnote the course grade the caller wrote
	 * @param array               $pruefungen   the exams before this write
	 * @param mixed|null          $zeugnisNote  the transcript grade the caller read
	 * @return array|string the changed exams or a translated error message
	 */
	private function savePruefungstermin($pruefung_id, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum, $origLvNote = null, $mitarbeiter_uid = null, $lvgesamtnote = null, $pruefungen = array(), $zeugnisNote = null)
	{
		// no exam without a course grade (a grade that is not released also counts)
		if($lvgesamtnote === null) {
			return $this->p->t('benotungstool', 'c4keineLvNoteEingetragen');
		}

		$jetzt = date("Y-m-d H:i:s");

		$pruefungenChanged = [];

		// edit: only the addressed record, no type change and no new record
		if($pruefung_id !== null && $pruefung_id !== '') {
			$id = $this->LePruefungModel->update(
				$pruefung_id,
				array(
					'updateamum' => $jetzt,
					'updatevon' => getAuthUID(),
					'note' => $note,
					'punkte' => $punkte,
					'datum' => $datum,
					'anmerkung' => ""
				)
			);
			$res = null;
			if($id) {
				$res = $this->LePruefungModel->load($id->retval);
				if(hasData($res)) $pruefungenChanged['savedPruefung'] = getData($res);
			}

			$this->logLib->logInfoDB(array('pruefung updated', $res, getAuthUID(), getAuthPersonId()));

			if(!isset($pruefungenChanged['savedPruefung'])) {
				return $this->p->t('benotungstool', 'c4pruefungNichtGespeichert', [$student_uid]);
			}

			$pruefungenChanged['verlauf'] = $this->buildVerlaufSummary($student_uid, $lva_id, $stsem, $lvgesamtnote, $zeugnisNote);
			return $pruefungenChanged;
		}

		$verlauf = $this->VerlaufLib->buildVerlauf($pruefungen, $origLvNote);
		$rolle = $verlauf->naechsteRolle;

		// one action makes one exam; the release creates the first attempt (upsertErstantritt)
		$typ = ($rolle === PruefungsverlaufLib::ROLLE_ERSTANTRITT)
			? $this->VerlaufLib->legacyTypFuerAntritt(1)
			: $this->VerlaufLib->legacyTypFuerWiederholung($verlauf);

		$id = $this->LePruefungModel->insert(
			array(
				'lehreinheit_id' => $lehreinheit_id,
				'student_uid' => $student_uid,
				'mitarbeiter_uid' => $this->benotenderMitarbeiter($lehreinheit_id, $mitarbeiter_uid),
				'note' => $note,
				'punkte' => $punkte,
				'pruefungstyp_kurzbz' => $typ,
				'datum' => $datum,
				'anmerkung' => "",
				'insertamum' => $jetzt,
				'insertvon' => getAuthUID(),
				'updateamum' => null,
				'updatevon' => null,
				'ext_id' => null
			)
		);
		$res = null;
		if($id) {
			$res = $this->LePruefungModel->load($id->retval);
			if(hasData($res)) $pruefungenChanged['savedPruefung'] = getData($res);
		}

		$this->logLib->logInfoDB(array('pruefung inserted ('.$rolle.')', $res, getAuthUID(), getAuthPersonId()));

		// savedPruefung is the proof of success. Without this guard a failed insert (for example
		// a missing lehreinheit_id) tells the client that the write was successful.
		if(!isset($pruefungenChanged['savedPruefung'])) {
			return $this->p->t('benotungstool', 'c4pruefungNichtGespeichert', [$student_uid]);
		}

		$pruefungenChanged['verlauf'] = $this->buildVerlaufSummary($student_uid, $lva_id, $stsem, $lvgesamtnote, $zeugnisNote);
		return $pruefungenChanged;
	}

	private function sendFreigabeEmail($lektorFullName, $lvaFullName, $notenCount, $emailAdressen, $studlist, $betreff)
	{
		$emailAdressen = $this->freigabeEmpfaenger($emailAdressen);
		$adressen = implode(";", $emailAdressen);
		
		foreach ($emailAdressen as $email)
		{
			// Prepare mail content
			$body_fields = array(
				'lektor' => $lektorFullName,
				'lvaname' => $lvaFullName,
				'studlist' => $studlist,
				'neuenotencount' => $notenCount,
				'adressen' => $adressen
			);

			// Send mail
			$vorlage = $this->config->item('CIS_GESAMTNOTE_FREIGABEMAIL_VORLAGE');
			if(!is_string($vorlage) || $vorlage === '') $vorlage = 'Notenfreigabe';

			sendSanchoMail(
				$vorlage,
				$body_fields,
				$email,
				$betreff
			);
		}

	}

	/**
	 * Creates or updates attempt 1 on release, dated on the benotungsdatum. §8 asks for the date of
	 * the last performance, which the system does not hold - the old tool used the same column.
	 *
	 * Applies only while at most one exam exists. A credited grade changes nothing.
	 */
	private function upsertErstantritt($lva_id, $student_uid, $stsem, $note, $punkte, $datum)
	{
		// same switch as the Übernahme path
		if(!$this->config->item('CIS_GESAMTNOTE_ERSTANTRITT_BEI_UEBERNAHME')) return;

		if($this->VerlaufLib->istAnrechnungsnote($this->getZeugnisnote($lva_id, $student_uid, $stsem))) return;

		// The release makes the grade binding. It moves no date: an exam keeps the date it carries,
		// and attempt 1 keeps the date that the person picked while entering the grade. Only a
		// missing attempt 1 is created, and it is dated on the benotungsdatum.
		$lehreinheit_id = $this->lehreinheitFuerStudent($lva_id, $student_uid, $stsem);

		$geschrieben = $this->VerlaufLib->upsertErstantritt(
			$student_uid, $lva_id, $stsem, $note, $punkte, $datum,
			$this->benotenderMitarbeiter($lehreinheit_id), false, $lehreinheit_id
		);

		if($geschrieben !== null) {
			$this->logLib->logInfoDB(array('erstantritt (freigabe)', $student_uid, getAuthUID(), getAuthPersonId()));
		}
	}

	/**
	 * Guards the benotungsdatum. It becomes the date of attempt 1, so the exam date rules apply.
	 * No date is allowed - the current moment then applies.
	 *
	 * @return string|null translated error or null
	 */
	private function validateBenotungsdatum($datum, $student_uid, $sem_kurzbz)
	{
		if($datum === null || $datum === '') return null;

		$tag = substr((string) $datum, 0, 10);
		$geprueft = DateTime::createFromFormat('Y-m-d', $tag);

		if($geprueft === false || $geprueft->format('Y-m-d') !== $tag) {
			return $this->p->t('benotungstool', 'benotungsdatumUngueltig', [$student_uid]);
		}

		// an assessment that did not happen yet has no date
		if(!$this->config->item('CIS_GESAMTNOTE_DATUM_ZUKUNFT') && $tag > date('Y-m-d')) {
			return $this->p->t('benotungstool', 'benotungsdatumInZukunft', [$student_uid]);
		}

		return $this->pruefungsdatumNachFrist($sem_kurzbz, $tag, $student_uid);
	}

	/**
	 * Guards a direct API write of the course grade. The cell editor carries the same rules, but
	 * API and CSV import bypass it. Order: grade, exam, transcript.
	 *
	 * @return string|null translated error or null
	 */
	private function validateNotenvorschlag($lva_id, $student_uid, $stsem, $note)
	{
		$noten = $this->aktiveNoten();
		$wert  = trim((string)$note);

		// the editor offers the lehre grades only; an administrative grade belongs to the transcript
		if($this->config->item('CIS_GESAMTNOTE_VORSCHLAG_NUR_LEHRENOTEN') !== false
			&& (!isset($noten[$wert]) || !$noten[$wert]->lehre)) {
			return $this->p->t('benotungstool', 'c4noteNichtInLehre', [$student_uid]);
		}

		// Antritt 1 und die LV-Note sind dieselbe Leistung, deshalb schreibt dieser Weg sie weiter.
		// Ab der ersten Wiederholung gehört die Note zum Antritt: dann über den Prüfungsdialog.
		if($this->config->item('CIS_GESAMTNOTE_VORSCHLAG_NACH_WIEDERHOLUNG') !== true
			&& $this->VerlaufLib->hatWiederholung($this->VerlaufLib->getPruefungen($student_uid, $lva_id, $stsem))) {
			return $this->p->t('benotungstool', 'c4notenvorschlagGesperrt', [$student_uid]);
		}

		// a released grade can be final; then no path may change it any more
		if($this->config->item('CIS_GESAMTNOTE_FREIGABE_FINAL')
			&& $this->istFreigegeben($this->getLvGesamtnoteRow($lva_id, $student_uid, $stsem))) {
			return $this->p->t('benotungstool', 'freigabeEndgueltig', [$student_uid]);
		}

		// a transcript grade can forbid the teacher to overwrite it, for example 'intern angerechnet'.
		// An unknown or inactive grade locks nothing, which is what the editor does as well.
		$zeugnisnote = trim((string)$this->getZeugnisnote($lva_id, $student_uid, $stsem));
		if(isset($noten[$zeugnisnote]) && !$noten[$zeugnisnote]->lkt_ueberschreibbar) {
			return $this->p->t('benotungstool', 'c4zeugnisnoteGesperrt', [$student_uid]);
		}

		return null;
	}

	/**
	 * Runs the examination rules for one write. The lib holds the rules; this only translates the
	 * answer. The caller reads the context, because the write path needs the same three values.
	 *
	 * Empty $pruefung_id = new attempt, set = edit.
	 *
	 * @param array         $pruefungen  the exams of the student in this course
	 * @param stdClass|null $lvRow       the course grade, read without the release filter
	 * @param mixed|null    $zeugnisNote the transcript grade
	 * @return string|null translated error or null
	 */
	private function validatePruefung($student_uid, $note, $datum, $pruefung_id, $pruefungen, $lvRow, $zeugnisNote)
	{
		$lvNote = $lvRow ? $lvRow->note : null;

		$neu = ($pruefung_id === null || $pruefung_id === '');

		$fehler = $neu
			? $this->VerlaufLib->validateAdd($pruefungen, $note, $datum, $lvNote, $zeugnisNote)
			: $this->VerlaufLib->validateEdit($pruefungen, $pruefung_id, $note, $datum, $lvNote, $zeugnisNote);

		if($fehler !== null) return $this->regelFehlertext($fehler, $student_uid);

		// a released grade can be final; the exam would change it
		if($this->config->item('CIS_GESAMTNOTE_FREIGABE_FINAL') && $this->istFreigegeben($lvRow)) {
			return $this->p->t('benotungstool', 'freigabeEndgueltig', [$student_uid]);
		}

		// the matrix can reserve the kommPruef for one role, on top of the global switch
		if($neu && !$this->darfAktion('kommpruef')) {
			$verlauf = $this->VerlaufLib->buildVerlauf($pruefungen, $lvNote, $zeugnisNote);
			if($verlauf->naechsteRolle === PruefungsverlaufLib::ROLLE_KOMMISSIONELL) {
				return $this->p->t('benotungstool', 'kommPruefNichtErlaubt', [$student_uid]);
			}
		}

		return null;
	}

	/**
	 * @param bool $withPruefungen add the exams (for the write paths) or send the counters only
	 * @return array
	 */
	private function verlaufSummary($verlauf, $withPruefungen = false, $hatLvNote = null)
	{
		$summary = array(
			'antrittCount' => $verlauf->antrittCount,
			'maxAntritte' => $verlauf->maxAntritte,
			'canAdd' => $verlauf->canAdd,
			'terminal' => $verlauf->terminal,
			// closed by a pass, not by the attempt limit
			'bestanden' => $verlauf->bestanden,
			'erstantrittMoeglich' => $verlauf->erstantrittMoeglich,
			'naechsteRolle' => $verlauf->naechsteRolle,
			// the next attempt is kommissionell and this tool may not create it
			'kommPruefGesperrt' => $verlauf->kommPruefGesperrt,
			// credited: the row is visible, but you cannot select it and it gets no exams
			'angerechnet' => $verlauf->angerechnet,
			'hatLvNote' => $hatLvNote // ungefiltert, also inklusive noch nicht freigegebener
		);

		if($withPruefungen) $summary['pruefungen'] = $verlauf->pruefungen;

		return $summary;
	}
}

