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

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$permissions = array('lehre/benotungstool:rw', 'lehre/benotungstool_assistenz:rw');
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
			'getLvForStudiengang' => $permissions
		]);

		$this->load->library('AuthLib', null, 'AuthLib');
		$this->load->library('PhrasesLib');

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

	public function getCisConfig() {
		// resolved from tbl_note (Bezeichnung) with config fallback -> single source of truth
		$special = $this->resolveSpecialNotes();
		$NOTEN_OHNE_ANTRITT = $special['ohneAntritt'];
		$NOTEN_OCCURANCE_LIMIT_MAP = $special['limitMap'];
		$NOTE_ENTSCHULDIGT = $special['entschuldigt'];
		
		$this->terminateWithSuccess(
			array(
				// Punkte bei der Noteneingabe anzeigen
				'CIS_GESAMTNOTE_PUNKTE' => CIS_GESAMTNOTE_PUNKTE,
				
				// basically on/of toggle for the points/grade col and the arrow button
				'CIS_GESAMTNOTE_UEBERSCHREIBEN' => CIS_GESAMTNOTE_UEBERSCHREIBEN,
				
				// only relevant in punkte calculation in backend
				// 'CIS_GESAMTNOTE_GEWICHTUNG' => CIS_GESAMTNOTE_GEWICHTUNG,
				
				// enables the first in-tool retake (Termin2).
				// Used in the maxAntritte calculation.
				'CIS_GESAMTNOTE_PRUEFUNG_TERMIN2' => CIS_GESAMTNOTE_PRUEFUNG_TERMIN2,

				// legacy: enables a SECOND in-tool retake (Termin3) for other installations whose ruleset
				// uses three explicit Nachprüfungs-Termine. Each enabled retake type raises the maxAntritte cap.
				'CIS_GESAMTNOTE_PRUEFUNG_TERMIN3' => CIS_GESAMTNOTE_PRUEFUNG_TERMIN3,
				
				// used to toggle availability of kommPruef type pruefungen
				'CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF' => CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF,
				
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
				
				// send a mail when approving grades
				'CIS_GESAMTNOTE_FREIGABEMAIL_NOTE' => CIS_GESAMTNOTE_FREIGABEMAIL_NOTE,
				
				'NOTEN_OHNE_ANTRITT' => $NOTEN_OHNE_ANTRITT,

				'NOTEN_OCCURANCE_LIMIT_MAP' => $NOTEN_OCCURANCE_LIMIT_MAP,

				// pk of the 'entschuldigt' note; used to preserve excused Termine on new pruefung creation
				'NOTE_ENTSCHULDIGT' => $NOTE_ENTSCHULDIGT,

				// show the Benotungsdatum of the first Antritt in the "Ursprüngliche Zeugnisnote" column
				'SHOW_BENOTUNGSDATUM_ON_NOTENVORSCHLAG_UEBERNAHME' => $this->config->item('SHOW_BENOTUNGSDATUM_ON_NOTENVORSCHLAG_UEBERNAHME'),

				// Noteneintragungsfrist window (enforced server-side; also surfaced so the UI can hint at it)
				'CIS_GESAMTNOTE_NOTENEINTRAGUNGSFRIST' => $this->config->item('CIS_GESAMTNOTE_NOTENEINTRAGUNGSFRIST')
			)
		);
	}

	/**
	 * GET METHOD
	 * expects (optional) 'sem_kurzbz'
	 * Single role-determining entry point for the tool: the server decides which flow the current user
	 * gets and returns exactly the initial dropdown data for it.
	 * A teacher (holds lehre/benotungstool) gets their assigned
	 * Lehrveranstaltungen directly. Assistenz (only lehre/benotungstool_assistenz) gets the
	 * Studiengänge they are entitled for and picks one before an LV is loaded.
	 */
	public function getBenotungstoolContext() {
		$sem_kurzbz = $this->input->get("sem_kurzbz", TRUE);
		$lv_id = $this->input->get("lv_id", TRUE); // optional: deep-link target, used to preselect

		$this->load->library('PermissionLib');

		// teachers keep the classic assigned-LV flow; the Studiengang flow is only for Assistenz.
		// Role determination mirrors assertLvAccess, which scopes each role's actual data
		// access (teachers to their own LVs, Assistenzen to their entitled Studiengänge).
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

	/**
	 * GET METHOD
	 * expects 'studiengang_kz', 'sem_kurzbz'
	 * Returns the Lehrveranstaltungen of a Studiengang in a Studiensemester for the Assistenz flow.
	 * The caller may only load Studiengänge they are entitled for via lehre/benotungstool_assistenz.
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

	/**
	 * Scopes data access to the Lehrveranstaltungen a user may act on, so a shared/guessed URL cannot be
	 * used to read or edit foreign grades. A teacher (lehre/benotungstool) may
	 * only touch LVs they are assigned to teach in the given Studiensemester. Assistenz may
	 * touch LVs of a Studiengang they are entitled for.
	 */
	private function assertLvAccess($lv_id, $sem_kurzbz = null)
	{
		$this->load->library('PermissionLib');

		// admins keep full access
		if ($this->permissionlib->isBerechtigt('admin')) return;

		// teachers: only their own LVs (assigned as lehreinheitmitarbeiter in this semester)
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
	 * GET METHOD
	 * expects 'lv_id', 'sem_kurzbz'
	 * returns List of all Students of given lehrveranstaltung and semester and fetches their grades.
	 * Loads LvGesamtnote aswell as Teilnoten from externalSources via getExternalGrades Event.
	 * Calculates the Notenvorschlag for every student based on averaging their Teilnoten.
	 * Finally also fetches all Prüfungen for every student which are linked to lva and semester.
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
		
		foreach($student_uids as $uid) {
			$grades[$uid]['grades'] = [];

			$result = $this->LvgesamtnoteModel->getLvGesamtNoten($lv_id, $uid, $sem_kurzbz);
//			$this->addMeta($uid.'getLvGesamtNoten', $result);
			if(!isError($result) && hasData($result)) {
				$lvgesamtnote = getData($result)[0];
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
		
		// calculate notenvorschläge from teilnoten
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
					if (is_numeric($teilnote['grade']) || (is_null($teilnote['grade']) && is_numeric($teilnote['points'])))
					{
						$notensumme += $teilnote['grade'];
						$punktesumme += $teilnote['points'];
						$notensumme_gewichtet += $teilnote['grade'] * $teilnote['weight'];
						$punktesumme_gewichtet += $teilnote['points'] * $teilnote['weight'];
						$gewichtsumme += $teilnote['weight'];
						$anzahlnoten += 1;
					}
				}
				
				if (CIS_GESAMTNOTE_PUNKTE) {
					if (defined('CIS_GESAMTNOTE_GEWICHTUNG') && CIS_GESAMTNOTE_GEWICHTUNG) {
						// Lehreinheitsgewichtung
						$punkte_vorschlag = round($punktesumme_gewichtet / $gewichtsumme, 2);
						$note_vorschlag_result = $this->NotenschluesselaufteilungModel->getNote($punkte_vorschlag, $lv_id, $sem_kurzbz);
						$note_vorschlag = $this->getDataOrTerminateWithError($note_vorschlag_result);
					} else {
						$punkte_vorschlag = round($punktesumme / $anzahlnoten, 2);
						$note_vorschlag_result = $this->NotenschluesselaufteilungModel->getNote($punkte_vorschlag, $lv_id, $sem_kurzbz);
						$note_vorschlag = $this->getDataOrTerminateWithError($note_vorschlag_result);
					}
				} else {
					if (defined('CIS_GESAMTNOTE_GEWICHTUNG') && CIS_GESAMTNOTE_GEWICHTUNG) {
						$note_vorschlag = round($notensumme_gewichtet / $gewichtsumme);
					} else {
						$note_vorschlag = round($notensumme / $anzahlnoten);
					}
				}
				
				$student->note_vorschlag = $note_vorschlag;
			}
		}
		
		// get all prüfungen with noten held in that semester in that lva
		$pruefungen = $this->LePruefungModel->getPruefungenByLvStudiensemester($lv_id, $sem_kurzbz);
		$pruefungenData = getData($pruefungen);

		$this->terminateWithSuccess(array($studentenData, $pruefungenData, DOMAIN, $grades, $anwresult));
	}

	/**
	 * GET METHOD
	 * returns List of all available & active NotenOptions 
	 */
	public function getNoten() {
		$this->load->model('education/Note_model', 'NoteModel');

		$result = $this->NoteModel->getAllActive();
		$noten = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($noten);
	}

	/**
	 * POST METHOD
	 * expects 'lv_id', 'sem_kurzbz', 'password', 'noten'
	 * Notenfreigabe method which checks the users password as a security measure.
	 * Tries to load Lehrveranstaltung, Studiengang and Person via Model in order to validate the coherency of input parameters
	 * lv_id & sem_kurzbz in relation to the noten array delivered.
	 * Updates the LvGesamtnote note, aswell as freigabedatum, which is key in the logic of the freigegeben/offen/changed notenStatus
	 * Along this process builds a html table to be placed in a confirmation email (uid only and full variant depending on config)
	 * which is being sent to the Lektor, aswell as the assigned Assistenz.
	 */
	public function saveStudentenNoten() {
		$result = $this->getPostJSON();

		if(!property_exists($result, 'sem_kurzbz') || !property_exists($result, 'lv_id') || 
			!property_exists($result, 'password') || !property_exists($result, 'noten')) {
			$this->terminateWithError($this->p->t('global', 'missingParameters'), 'general');
		}
		
		if(!$this->AuthLib->checkUserAuthByUsernamePassword(getAuthUID(), $result->password)->retval) {
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
							$ret[] = array('uid' => $note->uid, 'freigabedatum' => $lvgesamtnote->freigabedatum, 'benotungsdatum' => $lvgesamtnote->benotungsdatum);
						}
					}
					 
					if (defined('CIS_GESAMTNOTE_FREIGABEMAIL_NOTE') && CIS_GESAMTNOTE_FREIGABEMAIL_NOTE)
					{
						$studlist .= "<tr><td>" . trim($note->matrikelnr) . "</td>";
						$studlist .= "<td>" . trim($note->kuerzel) . "</td>";
						$studlist .= "<td>" . trim($note->nachname) . "</td>";
						$studlist .= "<td>" . trim($note->vorname) . "</td>";

						if(defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE) {
							$studlist .= "<td>" . trim($lvgesamtnote->punkte) . "</td>";
						}
						$studlist .= "<td>" .$note->noteBezeichnung. "</td>";

						$studlist .= "<td>" . $lvgesamtnote->mitarbeiter_uid;
						if ($lvgesamtnote->updatevon != '')
							$studlist .= " (" . $lvgesamtnote->updatevon . ")";
						$studlist .= "</td></tr>";
					} else {
						$studlist .= "<tr><td>" . trim($note->uid) . "</td></tr>\n";
					}
				}
			}
		}
		$studlist .= "</table>";

		$this->logLib->logInfoDB(array('saveStudentenNoten', array(
			'updatevon' => getAuthUID(),
			'updateamum' => date('Y-m-d H:i:s')
		), getAuthUID(), getAuthPersonId(), array($result->noten, $lv_id, $sem_kurzbz)));
		
		// always send the mail, config toggles data contents
		$this->sendFreigabeEmail($lektorFullName, $lvaFullName, count($result->noten), $emails, $studlist, $betreff);
		
		$this->terminateWithSuccess($ret);
	}

	
	private function sendFreigabeEmail($lektorFullName, $lvaFullName, $notenCount, $emailAdressen, $studlist, $betreff)
	{
		$emailAdressen[] = getAuthUID() . "@" . DOMAIN; // also send mail to lektors own adress
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
			sendSanchoMail(
				'Notenfreigabe',
				$body_fields,
				$email,
				$betreff
			);
		}

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
	 * POST METHOD
	 * expects 'datum', 'lva_id', 'student_uid', 'note'
	 * Inserts or updates a pruefung for lva & student_uid at given datum (YYYY-MM-DD). When creating a new
	 * Pruefung, sets the provided (Prüfungs-) Note.
	 * Updates the LvGesamtnote of student.
	 * Can return 1 or 2 Prüfungen, since the original grade before the first prüfung is being saved as "Termin1" when
	 * a "Termin2" is being created.
	 */
	public function saveStudentPruefung() { // einzelne pruefung speichern
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
		$typ = $result->typ;

		$this->assertLvAccess($lva_id, $stsem);

		// Prüfungsordnung §1: no entry/change past the Noteneintragungsfrist
		$this->enforceNoteneintragungsfrist($stsem);

		$jetzt = date("Y-m-d H:i:s");

		if(CIS_GESAMTNOTE_PUNKTE && isset($punkte) && $punkte >= 0) {
			// Bei Punkteeingabe wird die Note nochmals geprueft und ggf korrigiert
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
			$this->load->model('education/Note_model', 'NoteModel');
			$result = $this->NoteModel->getNochNichtEingetragenNote();
			$note = getData($result)[0]->note;
		}

		// validate the edit before any write: the date must stay between the neighbouring exams and,
		// once a later/higher pruefung exists, the grade may no longer be changed (only the date).
		// only applies when editing an existing record ($pruefung_id set)
		$editError = $this->validatePruefungEdit($student_uid, $lva_id, $stsem, $note, $datum, $pruefung_id);
		if($editError !== null) {
			$this->terminateWithError($editError, 'general');
		}

		// for a NEW attempt (no pruefung_id) enforce the add rules server-side
		// maxAntritte calc, chronological order, occurrence limit for entschuldigt
		if($pruefung_id === null || $pruefung_id === '') {
			$addError = $this->validatePruefungAdd($student_uid, $lva_id, $stsem, $note, $datum);
			if($addError !== null) {
				$this->terminateWithError($addError, 'general');
			}
		}


		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$res = $this->LehrveranstaltungModel->load($lva_id);
		if(isError($res) || !hasData($res)) {
			$this->terminateWithError($this->p->t('benotungstool', 'noValidLvFoundForId', [$lva_id]));
		}

		$studiengang_kz = getData($res)[0]->studiengang_kz;
		$res = $this->StudiengangModel->load($studiengang_kz);
		if(isError($res) || !hasData($res)) {
			$this->terminateWithError($this->p->t('benotungstool', 'noValidStudiengangFoundForId', [$studiengang_kz]));
		}
		

		$result = $this->LvgesamtnoteModel->getLvGesamtNoten($lva_id, $student_uid, $stsem);

		$origLvNote = null;
		$origLvPunkte = null;
		$origBenotungsdatum = null;
		
//		// TODO: check if savePruefungstermin wont throw an error before updating/inserting lv note
		// todo: also check if typ is wrong but another would be available?
//		if(!$this->ableToSavePruefungstermin($typ))
		
		if(!isError($result) && !hasData($result)) {
			
			$id = $this->LvgesamtnoteModel->insert(
				array(
					'student_uid' => $student_uid,
					'lehrveranstaltung_id' => $lva_id,
					'studiensemester_kurzbz' => $stsem,
					'note' => $note,
					'punkte' => $punkte,
					'mitarbeiter_uid' => getAuthUID(),
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
			if($id) {
				$res = $this->LvgesamtnoteModel->load($id->retval);
				if(hasData($res)) $lvgesamtnote = getData($res)[0];
			}

			$this->logLib->logInfoDB(array('saveStudentPruefung insert lvnote', $res, array(
				'insertvon' => getAuthUID(),
				'insertamum' => date('Y-m-d H:i:s')
			), getAuthUID(), getAuthPersonId(), $student_uid, $lva_id, $stsem, $note,$punkte, $jetzt));

		}
		else if(!isError($result) && hasData($result))
		{
			$lvgesamtnote = getData($result)[0];

			$orig = getData($result)[0];
			$origLvNote = $lvgesamtnote->note;
			$origLvPunkte = $lvgesamtnote->punkte;
			$origBenotungsdatum = $lvgesamtnote->benotungsdatum;
			
			$id = $this->LvgesamtnoteModel->update(
				[$lvgesamtnote->student_uid, $lvgesamtnote->studiensemester_kurzbz, $lvgesamtnote->lehrveranstaltung_id],
				array(
					'note' => $note,
					'punkte' => $punkte,
					'benotungsdatum' => $jetzt,
					'updateamum' => $jetzt,
					'updatevon' => getAuthUID()
				)
			);

			if($id) {
				$res = $this->LvgesamtnoteModel->load($id->retval);
				if(hasData($res)) $lvgesamtnote = getData($res)[0];
			}
			
			$this->logLib->logInfoDB(array('saveStudentPruefung update lvnote', $res, array(
				'updatevon' => getAuthUID(),
				'updateamum' => date('Y-m-d H:i:s')
			), getAuthUID(), getAuthPersonId(), $student_uid, $lva_id, $stsem, $note,$punkte, $jetzt));
		}
		
		// save pruefung after updating lvnote, since pruefungspunkte get loaded by lv punkte 
		$pruefungenChanged = $this->savePruefungstermin($typ, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum, $origLvNote, $origLvPunkte, $origBenotungsdatum);

		$savedPruefung = $pruefungenChanged['savedPruefung'] ?? null;
		$extraPruefung = $pruefungenChanged['extraPruefung'] ?? null;

		$savedPruefungData = count($savedPruefung) > 0 ? $savedPruefung[0] : null;
		$extraPruefungData = count($extraPruefung) > 0 ? $extraPruefung[0] : null;
		
		$this->terminateWithSuccess(array($savedPruefungData, $lvgesamtnote, $extraPruefungData));
	}

	/**
	 * private helper method to update/insert pruefungstermine 
	 */
	private function savePruefungstermin($typ, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum, $origLvNote = null, $origLvPunkte = null, $origBenotungsdatum = null)
	{

		// extra check if the student has lvnote and a zeugnisnote in the relevant lva
		$resultLV = $this->LvgesamtnoteModel->getLvGesamtNoten($lva_id, $student_uid, $stsem);
		$lvgesamtnoteData = getData($resultLV);
//		$this->addMeta('lvgesamtnoteData', $lvgesamtnoteData);
		
		// allocating pruefungen before lv note is forbidden
		if($lvgesamtnoteData == null) return $this->p->t('benotungstool', 'c4keineLvNoteEingetragen');

		$status = [];
		
		// TODO: config here?
		// send $grades reference to moodle addon
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
		
		if(count($status) > 0 && $status[0] == true) {
			$entschuldigtNote = $this->resolveSpecialNotes()['entschuldigt'];

			// the occurrence limit was validated on the PRE-override note, so re-check it here: only
			// apply the auto-excuse if it would not exceed the allowed number of excused Termine
			if($entschuldigtNote !== null) {
				$allRes = $this->LePruefungModel->getPruefungenByUidTypLvStudiensemester($student_uid, null, $lva_id, $stsem);
				$existing = (!isError($allRes) && hasData($allRes)) ? getData($allRes) : [];
				if(!$this->exceedsNoteOccuranceLimit($existing, $entschuldigtNote, null)) {
					$note = $entschuldigtNote;
				}
			}
		}
		
		$jetzt = date("Y-m-d H:i:s");
		
		$pruefungenChanged = [];
		
		if($typ == "Termin2" && defined('CIS_GESAMTNOTE_PRUEFUNG_TERMIN2') && CIS_GESAMTNOTE_PRUEFUNG_TERMIN2) 
		{
			
			// Wenn eine Nachprüfung angelegt wird, wird zuerst eine Pruefung mit 1. Termin angelegt welche für die ursprüngliche Note
			// vor den Prüfungsantritten zählt
			
			$result1 = $this->LePruefungModel->getPruefungenByUidTypLvStudiensemester($student_uid, "Termin1", $lva_id, $stsem);
			
			// if there is a termin 1 entry already do nothing
			if(!isError($result1) && hasData($result1)) {

			} else if(!isError($result1) && !hasData($result1)) {
				// new entry termin1

				$resultLV = $this->LvgesamtnoteModel->getLvGesamtNoteVorschlag($lva_id, $student_uid, $stsem);
				
				// update Termin1 note
				if ($origLvNote !== null) {
					$pr_note = $origLvNote;
					$pr_punkte = $origLvPunkte;
					$benotungsdatum = $origBenotungsdatum ?? $jetzt;
				} 
				else if (hasData($resultLV))
				{
					$lvgesamtnote = getData($resultLV)[0];
					$pr_note = $lvgesamtnote->note;
					$pr_punkte = $lvgesamtnote->punkte;
					$benotungsdatum = $lvgesamtnote->benotungsdatum;
				}
				else if(!hasData($resultLV))// set Termin1 note to "noch nicht eingetragen"
				{
					$this->load->model('education/Note_model', 'NoteModel');
					$result = $this->NoteModel->getNochNichtEingetragenNote();
					$pr_note = getData($result)[0]->note;
					$pr_punkte = '';
					$benotungsdatum = $jetzt;
				}
				
				$id = $this->LePruefungModel->insert(
					array(
						'lehreinheit_id' => $lehreinheit_id,
						'student_uid' => $student_uid,
						'mitarbeiter_uid' => getAuthUID(),
						'note' => $pr_note,
						'punkte' => $pr_punkte,
						'pruefungstyp_kurzbz' => "Termin1",
						'datum' => $benotungsdatum,
						'anmerkung' => "",
						'insertamum' => $jetzt,
						'insertvon' => getAuthUID(),
						'updateamum' => null,
						'updatevon' => null,
						'ext_id' => null
					)
				);
				if($id) {
					$res = $this->LePruefungModel->load($id->retval);
					if(hasData($res)) $pruefungenChanged['extraPruefung'] = getData($res);
				}

				$this->logLib->logInfoDB(array('termin1 created',$res, getAuthUID(), getAuthPersonId()));
				
			}

			


			// Die Pruefung wird als Termin2 eingetragen.
			// Ein bestehender "entschuldigt"-Termin2 bleibt erhalten: in diesem Fall wird die neue
			// Pruefung als eigener Datensatz angelegt statt den entschuldigten zu ueberschreiben.
			$result2 = $this->LePruefungModel->getPruefungenByUidTypLvStudiensemester($student_uid, "Termin2", $lva_id, $stsem);

			$termin2 = null;
			if(!isError($result2) && hasData($result2)) {
				foreach(getData($result2) as $t2) {
					if(!$this->isEntschuldigtNote($t2->note)) { $termin2 = $t2; break; }
				}
			}

			if($termin2 !== null) {
				// update existing (non-excused) Termin2
				$id = $this->LePruefungModel->update(
					$termin2->pruefung_id,
					array(
						'updateamum' => $jetzt,
						'updatevon' => getAuthUID(),
						'note' => $note,
						'punkte' => $punkte,
						'datum' => $datum,
						'anmerkung' => ""
					)
				);
				if($id) {
					$res = $this->LePruefungModel->load($id->retval);
					if(hasData($res)) $pruefungenChanged['savedPruefung'] = getData($res);
				}

				$this->logLib->logInfoDB(array('termin2 updated',$res, getAuthUID(), getAuthPersonId()));


			} else if(!isError($result2)) {
				// no editable Termin2 (none yet, or only an entschuldigt one) -> insert new, excused stays

				$id = $this->LePruefungModel->insert(
					array(
						'lehreinheit_id' => $lehreinheit_id,
						'student_uid' => $student_uid,
						'mitarbeiter_uid' => getAuthUID(),
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
				if($id) {
					$res = $this->LePruefungModel->load($id->retval);
					if(hasData($res)) $pruefungenChanged['savedPruefung'] = getData($res);
				}

				$this->logLib->logInfoDB(array('termin2 inserted',$res, getAuthUID(), getAuthPersonId()));

			}

		} 
		else if($typ == "Termin3" && defined('CIS_GESAMTNOTE_PRUEFUNG_TERMIN3') && CIS_GESAMTNOTE_PRUEFUNG_TERMIN3)
		{
			// same entschuldigt-preservation handling as Termin2
			$result3 = $this->LePruefungModel->getPruefungenByUidTypLvStudiensemester($student_uid, "Termin3", $lva_id, $stsem);

			$termin3 = null;
			if(!isError($result3) && hasData($result3)) {
				foreach(getData($result3) as $t3) {
					if(!$this->isEntschuldigtNote($t3->note)) { $termin3 = $t3; break; }
				}
			}

			if($termin3 !== null) {
				// update existing (non-excused) Termin3
				$id = $this->LePruefungModel->update(
					$termin3->pruefung_id,
					array(
						'updateamum' => $jetzt,
						'updatevon' => getAuthUID(),
						'note' => $note,
						'punkte' => $punkte,
						'datum' => $datum,
						'anmerkung' => ""
					)
				);
				if($id) {
					$res = $this->LePruefungModel->load($id->retval);
					if(hasData($res)) $pruefungenChanged['savedPruefung'] = getData($res);
				}

				$this->logLib->logInfoDB(array('termin3 updated',$res, getAuthUID(), getAuthPersonId()));

			} else if(!isError($result3)) {
				// no editable Termin3 (none yet, or only an entschuldigt one) -> insert new, excused stays

				$id = $this->LePruefungModel->insert(
					array(
						'lehreinheit_id' => $lehreinheit_id,
						'student_uid' => $student_uid,
						'mitarbeiter_uid' => getAuthUID(),
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
				if($id) {
					$res = $this->LePruefungModel->load($id->retval);
					if(hasData($res)) $pruefungenChanged['savedPruefung'] = getData($res);
				}
				
				$this->logLib->logInfoDB(array('termin3 inserted',$res, getAuthUID(), getAuthPersonId()));

			}
		}
		else {
			$this->terminateWithError($this->p->t('benotungstool', 'wrongPruefungType', [$student_uid, $typ]), 'general');
		}
		
		return $pruefungenChanged;
	}

	/**
	 * ranking of the pruefung attempt types, used to detect whether a "höhere"
	 * (later attempt) pruefung exists for a student
	 */
	private function pruefungAttemptRank($typ)
	{
		switch($typ) {
			case 'Termin1':   return 1;
			case 'Termin2':   return 2;
			case 'Termin3':   return 3;
			case 'kommPruef': return 4;
			default:          return 0;
		}
	}

	/**
	 * whether a note is the configured 'entschuldigt' note. Such Termine are preserved as their
	 * own dated entry instead of being overwritten when a new pruefung of the same type is created.
	 */
	private function isEntschuldigtNote($note)
	{
		$entschuldigt = $this->resolveSpecialNotes()['entschuldigt'];
		return $entschuldigt !== null && $note == $entschuldigt;
	}

	/**
	 * Validates an edit to an existing Termin2/Termin3 pruefung. Returns a localized error
	 * string if the edit is not allowed, or null if it is. Mirrors the frontend guards so a
	 * disallowed edit cannot be forced through the API.
	 *
	 * Rules:
	 *  - The new $datum must stay strictly between the dates of the chronologically adjacent
	 *    pruefungen so the attempt order is preserved.
	 *  - Once a later-dated or higher-attempt pruefung already exists the grade may no longer be
	 *    changed (only the datum may be corrected within the bounds above).
	 *
	 * Only guards EDITS: $pruefung_id identifies the record being edited; when it is null (a new
	 * attempt is being added) nothing is restricted here (adds are validated client-side).
	 *
	 * @param string $student_uid
	 * @param int    $lva_id
	 * @param string $stsem
	 * @param int    $newNote      the (already resolved) note being saved
	 * @param string $newDatum     the datum being saved (Y-m-d)
	 * @param int    $pruefung_id  pk of the record being edited, or null for an add
	 * @return string|null
	 */
	private function validatePruefungEdit($student_uid, $lva_id, $stsem, $newNote, $newDatum, $pruefung_id)
	{
		if($pruefung_id === null || $pruefung_id === '') return null; // add, not an edit

		$result = $this->LePruefungModel->getPruefungenByUidTypLvStudiensemester($student_uid, null, $lva_id, $stsem);
		if(isError($result) || !hasData($result)) return null;

		$pruefungen = getData($result);

		// the record being edited
		$current = null;
		foreach($pruefungen as $p) {
			if($p->pruefung_id == $pruefung_id) { $current = $p; break; }
		}
		if($current === null) return null;

		// only the changeable resit grades are guarded
		if($current->pruefungstyp_kurzbz !== 'Termin2' && $current->pruefungstyp_kurzbz !== 'Termin3') return null;

		$currentRank = $this->pruefungAttemptRank($current->pruefungstyp_kurzbz);
		$currentDate = substr((string)$current->datum, 0, 10);
		$new         = substr((string)$newDatum, 0, 10);

		// chronological bounds from the immediate date-neighbours + detect later/higher pruefung
		$lower = null; $upper = null; $hasLaterOrHigher = false;
		foreach($pruefungen as $p) {
			if($p->pruefung_id == $current->pruefung_id) continue;

			$d = substr((string)$p->datum, 0, 10);
			if($d !== '') {
				if($d < $currentDate) { if($lower === null || $d > $lower) $lower = $d; }
				elseif($d > $currentDate) { if($upper === null || $d < $upper) $upper = $d; }
			}

			if($d > $currentDate || $this->pruefungAttemptRank($p->pruefungstyp_kurzbz) > $currentRank) {
				$hasLaterOrHigher = true;
			}
		}

		// grade is locked once a later/higher pruefung exists
		if($hasLaterOrHigher && $newNote != $current->note) {
			return $this->p->t('benotungstool', 'pruefungNoteLocked', [$student_uid]);
		}

		// datum must stay strictly between the neighbouring exam dates
		if(($lower !== null && $new <= $lower) || ($upper !== null && $new >= $upper)) {
			return $this->p->t('benotungstool', 'pruefungDatumOutOfRange', [$student_uid]);
		}

		// an edit may also change the note (e.g. to 'entschuldigt') -> keep the occurrence limit
		if($this->exceedsNoteOccuranceLimit($pruefungen, $newNote, $current->pruefung_id)) {
			return $this->p->t('benotungstool', 'noteOccuranceLimitReached', [$student_uid]);
		}

		return null;
	}

	/**
	 * Validates ADDING a new pruefung (attempt). Returns an error string if the add is not
	 * allowed, or null if it is..
	 *
	 * Rules (Prüfungsordnung §1):
	 *  - Rule A: the number of counting Prüfungsantritte may not exceed the configured maximum
	 *  - Rule B: attempts are taken in chronological order
	 *  - Rule C: a note carrying an occurrence limit (entschuldigt) may not exceed it.
	 *
	 * @param string $student_uid
	 * @param int    $lva_id
	 * @param string $stsem
	 * @param int    $note   the (already resolved) note being saved
	 * @param string $datum  the datum being saved (Y-m-d)
	 * @return string|null
	 */
	private function validatePruefungAdd($student_uid, $lva_id, $stsem, $note, $datum)
	{
		$result = $this->LePruefungModel->getPruefungenByUidTypLvStudiensemester($student_uid, null, $lva_id, $stsem);
		$pruefungen = (!isError($result) && hasData($result)) ? getData($result) : [];

		// current LV note (the implicit first Antritt), read before it gets mutated by the caller
		$lvNote = null;
		$resLv = $this->LvgesamtnoteModel->getLvGesamtNoten($lva_id, $student_uid, $stsem);
		if(!isError($resLv) && hasData($resLv)) $lvNote = getData($resLv)[0]->note;

		// Rule A: max Antritte
		if($this->computeAntrittCount($pruefungen, $lvNote) >= $this->computeMaxAntritte()) {
			return $this->p->t('benotungstool', 'maxAntritteReached', [$student_uid, $this->computeMaxAntritte()]);
		}

		// Rule B: no new attempt on/before an existing dated attempt
		$newDate = substr((string)$datum, 0, 10);
		foreach($pruefungen as $p) {
			$d = substr((string)$p->datum, 0, 10);
			if($d !== '' && $d >= $newDate) {
				return $this->p->t('benotungstool', 'pruefungDatumBeforeExisting', [$student_uid]);
			}
		}

		// Rule C: occurrence limit (e.g. only one 'entschuldigt' across the fixed Antritte)
		if($this->exceedsNoteOccuranceLimit($pruefungen, $note, null)) {
			return $this->p->t('benotungstool', 'noteOccuranceLimitReached', [$student_uid]);
		}

		return null;
	}

	/**
	 * Number of counting Prüfungsantritte for a student, mirroring the frontend getAntrittCountStudent:
	 * a kommPruef caps the count at 4, notes in NOTEN_OHNE_ANTRITT (entschuldigt / noch nicht
	 * eingetragen) do not count, and when no pruefung counts yet the original LV note counts as the
	 * first Antritt (unless it is a non-lehre note such as 'angerechnet').
	 */
	private function computeAntrittCount($pruefungen, $lvNote)
	{
		$notenOhneAntritt = $this->resolveSpecialNotes()['ohneAntritt'];

		foreach($pruefungen as $p) {
			if($p->pruefungstyp_kurzbz == 'kommPruef') return 4;
		}

		$count = 0;
		foreach($pruefungen as $p) {
			if(!in_array($p->note, $notenOhneAntritt)) $count++;
		}

		if($count === 0 && $lvNote !== null && $this->isLehreNote($lvNote)) return 1;

		return $count;
	}

	/**
	 * Maximum number of counting Prüfungsantritte enterable IN THIS TOOL, mirroring the frontend
	 * maxAntrittCount: the original note (always 1) plus each enabled in-tool retake type (Termin2 and
	 * the legacy Termin3). kommPruef is the terminal attempt, entered elsewhere, and is intentionally
	 * not part of this cap. For our installation (Termin2 on, Termin3 off) this is 2; excused Termine do
	 * not count (see computeAntrittCount), which is what lets Termin2 occur twice within the cap.
	 */
	private function computeMaxAntritte()
	{
		$max = 1;
		if(defined('CIS_GESAMTNOTE_PRUEFUNG_TERMIN2') && CIS_GESAMTNOTE_PRUEFUNG_TERMIN2) $max++;
		if(defined('CIS_GESAMTNOTE_PRUEFUNG_TERMIN3') && CIS_GESAMTNOTE_PRUEFUNG_TERMIN3) $max++;
		return $max;
	}

	/**
	 * Whether the given note is flagged as a 'lehre' note in tbl_note.
	 */
	private function isLehreNote($note)
	{
		$res = $this->NoteModel->load($note);
		if(isError($res) || !hasData($res)) return false;
		return (bool) getData($res)[0]->lehre;
	}

	/**
	 * Single source of truth for the "special" note PKs (entschuldigt & noch nicht eingetragen).
	 * tbl_note is resolved by Bezeichnung (install-independent) and takes precedence; the hardcoded
	 * config PKs (NOTE_ENTSCHULDIGT / NOTEN_OHNE_ANTRITT) are only a fallback. This removes the second
	 * source of truth so the entschuldigt-preservation and Antritt-counting logic can't disagree with
	 * the Bezeichnung lookups on installs whose PKs differ. Cached per request.
	 *
	 * @return array{entschuldigt: mixed, ohneAntritt: array, limitMap: array}
	 */
	private function resolveSpecialNotes()
	{
		static $cache = null;
		if($cache !== null) return $cache;

		$cfgEntschuldigt = $this->config->item('NOTE_ENTSCHULDIGT');
		$cfgOhneAntritt  = $this->config->item('NOTEN_OHNE_ANTRITT');
		$cfgLimitMap     = $this->config->item('NOTEN_OCCURANCE_LIMIT_MAP');
		if(!is_array($cfgOhneAntritt)) $cfgOhneAntritt = [];
		if(!is_array($cfgLimitMap))    $cfgLimitMap = [];

		// resolve by Bezeichnung, fall back to the config PKs
		$resEnt = $this->NoteModel->getEntschuldigtNote();
		$entschuldigt = (!isError($resEnt) && hasData($resEnt)) ? getData($resEnt)[0]->note : $cfgEntschuldigt;

		$resNn = $this->NoteModel->getNochNichtEingetragenNote();
		$nochNicht = (!isError($resNn) && hasData($resNn)) ? getData($resNn)[0]->note : ($cfgOhneAntritt[0] ?? null);

		$ohneAntritt = array_values(array_filter([$nochNicht, $entschuldigt], function($v){ return $v !== null; }));

		// re-key the configured entschuldigt limit onto the resolved PK (keep any other entries as-is)
		$limitMap = [];
		foreach($cfgLimitMap as $k => $v) {
			$limitMap[($k == $cfgEntschuldigt) ? $entschuldigt : $k] = $v;
		}

		$cache = [
			'entschuldigt' => $entschuldigt,
			'ohneAntritt'  => $ohneAntritt,
			'limitMap'     => $limitMap
		];
		return $cache;
	}

	/**
	 * Check for configured occurrence limit (NOTEN_OCCURANCE_LIMIT_MAP) among the given pruefungen.
	 * $excludePruefungId skips the record currently being edited
	 * Noten without a configured limit never exceed.
	 */
	private function exceedsNoteOccuranceLimit($pruefungen, $note, $excludePruefungId = null)
	{
		$limitMap = $this->resolveSpecialNotes()['limitMap'];
		if(!is_array($limitMap)) return false;

		$limit = null;
		foreach($limitMap as $limitNote => $limitVal) {
			if($limitNote == $note) { $limit = $limitVal; break; }
		}
		if($limit === null) return false;

		$count = 0;
		foreach($pruefungen as $p) {
			if($excludePruefungId !== null && $p->pruefung_id == $excludePruefungId) continue;
			if($p->note == $note) $count++;
		}

		return ($count + 1) > $limit;
	}

	/**
	 * Enforces the Noteneintragungsfrist (Prüfungsordnung §1) for grade/pruefung entry. Terminates the
	 * request with an error when the deadline for the given studiensemester has passed. Does nothing
	 * when the window enforcement is disabled or the deadline cannot be determined.
	 */
	private function enforceNoteneintragungsfrist($sem_kurzbz)
	{
		if(!$this->config->item('CIS_GESAMTNOTE_NOTENEINTRAGUNGSFRIST')) return;

		$deadline = $this->computeNoteneintragungsfrist($sem_kurzbz);
		if($deadline === null) return;

		if(new DateTime() > $deadline) {
			$this->terminateWithError(
				$this->p->t('benotungstool', 'noteneintragungsfristVorbei', [$deadline->format('d.m.Y')]),
				'general'
			);
		}
	}

	/**
	 * Computes the Noteneintragungsfrist deadline (end of day) for a studiensemester_kurzbz of the
	 * form '{SS|WS}yyyy'. SS -> deadline in the same calendar year, WS -> in the following year. The
	 * month/day come from the noten config. Returns a DateTime, or null when it can't be determined.
	 */
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

	/**
	 * POST METHOD
	 * expects 'sem_kurzbz', 'lv_id', 'student_uid', 'note'
	 * Method that sets lv_note of student in lva and semester from provided Points/Grade Selection.
	 * Updates the note & benotungsdatum, which is key in the noten state offen/freigegeben/changed
	 */
	public function saveNotenvorschlag() {
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

		$this->assertLvAccess($lv_id, $sem_kurzbz);

		// Prüfungsordnung §1: no entry/change past the Noteneintragungsfrist
		$this->enforceNoteneintragungsfrist($sem_kurzbz);

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
					'mitarbeiter_uid' => getAuthUID(),
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
			if($id) {
				$res = $this->LvgesamtnoteModel->load($id->retval);
				if(hasData($res)) $lvgesamtnote = getData($res)[0];
			}

			$this->logLib->logInfoDB(array('saveNotenvorschlag insert lv gesamtnote',$res, getAuthUID(), getAuthPersonId()));
		}
		
		$this->terminateWithSuccess(array($lvgesamtnote));
	}

	/**
	 * POST METHOD
	 * expects 'sem_kurzbz', 'lv_id', 'noten'
	 * Bulk variant of saveNotenvorschlag, used when importing grades from csv.
	 */
	public function saveNotenvorschlagBulk() {
		$result = $this->getPostJSON();

		if(!property_exists($result, 'lv_id') || !property_exists($result, 'sem_kurzbz') ||
			!property_exists($result, 'noten')) {
			$this->terminateWithError($this->p->t('global', 'missingParameters'), 'general');
		}
		
		$lv_id = $result->lv_id;
		$sem_kurzbz = $result->sem_kurzbz;
		$noten = $result->noten;

		$this->assertLvAccess($lv_id, $sem_kurzbz);

		// Prüfungsordnung §1: no entry/change past the Noteneintragungsfrist
		$this->enforceNoteneintragungsfrist($sem_kurzbz);

		$retLvNoten = [];
		
		foreach($noten as $note)
		{

			$result = $this->LvgesamtnoteModel->getLvGesamtNoteVorschlag($lv_id, $note->uid, $sem_kurzbz);
//			$this->addMeta($note->uid.'$result', $result);
			
			if(CIS_GESAMTNOTE_PUNKTE) {
				$resultNote = $this->NotenschluesselaufteilungModel->getNote($note->punkte, $lv_id, $sem_kurzbz);
				$note->note = $this->getDataOrTerminateWithError($resultNote);
//				$this->addMeta($note->uid.'note', $note);
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
						'mitarbeiter_uid' => getAuthUID(),
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
				if($id) {
					$res = $this->LvgesamtnoteModel->load($id->retval);
					if(hasData($res)) $lvgesamtnote = getData($res)[0];
				}

				$this->logLib->logInfoDB(array('saveNotenvorschlagBulk insert lv gesamtnote',$res, getAuthUID(), getAuthPersonId()));
			}

			$retLvNoten[] = $lvgesamtnote;
		}

		$this->terminateWithSuccess($retLvNoten);
	}

	/**
	 * POST METHOD
	 * expects 'uids', 'datum'
	 * Bulk variant of saveStudentPruefung, used when creating a new Prüfung for several students. Always sets note to
	 * "noch nicht eingetragen" for the created Prüfung.
	 */
	public function createPruefungen() {
		$result = $this->getPostJSON();

		if(!property_exists($result, 'uids') || !property_exists($result, 'datum')) {
			$this->terminateWithError($this->p->t('global', 'missingParameters'), 'general');
		}

		$uids = $result->uids;
		$datum = $result->datum;
		$lva_id = $result->lva_id;

		$stsem = $result->sem_kurzbz;

		$this->assertLvAccess($lva_id, $stsem);

		// Prüfungsordnung §1: no entry past the Noteneintragungsfrist
		$this->enforceNoteneintragungsfrist($stsem);

		$ret = [];

		$this->load->model('education/Note_model', 'NoteModel');
		$result = $this->NoteModel->getNochNichtEingetragenNote();
		$note = getData($result)[0]->note;

		foreach ($uids as $student) {
			$student_uid = $student->uid;
			$typ = $student->typ;
			$punkte = null; // new pruefungen never have punkte,

			$lehreinheit_id = $student->lehreinheit_id;

			// server-side add guards (max Antritte, chronological order, occurrence limit)
			$addError = $this->validatePruefungAdd($student_uid, $lva_id, $stsem, $note, $datum);
			if($addError !== null) {
				$ret[$student->uid] = $addError; // string result is surfaced per-student in the UI
				continue;
			}

			$ret[$student->uid] = $this->savePruefungstermin($typ, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum);
		}

		$this->logLib->logInfoDB(array('createPruefungen',$ret, getAuthUID(), getAuthPersonId()));

		$this->terminateWithSuccess($ret);
	}

	/**
	 * POST METHOD
	 * expects 'lv_id', 'sem_kurzbz', 'pruefungen'
	 * Bulk variant of saveStudentPruefung, used when importing pruefungsdata from csv with available noten.
	 */
	public function savePruefungenBulk() {
		$result = $this->getPostJSON();

		if(!property_exists($result, 'lv_id') || !property_exists($result, 'sem_kurzbz') ||
			!property_exists($result, 'pruefungen')) {
			$this->terminateWithError($this->p->t('global', 'missingParameters'), 'general');
		}

		$lv_id = $result->lv_id;
		$sem_kurzbz = $result->sem_kurzbz;
		$pruefungen = $result->pruefungen;

		$this->assertLvAccess($lv_id, $sem_kurzbz);

		// Prüfungsordnung §1: no entry past the Noteneintragungsfrist
		$this->enforceNoteneintragungsfrist($sem_kurzbz);

		$ret = [];

		foreach ($pruefungen as $pruefung) {
			
			if(CIS_GESAMTNOTE_PUNKTE) {
				$result = $this->NotenschluesselaufteilungModel->getNote($pruefung->punkte, $lv_id, $sem_kurzbz);
//				$this->addMeta($pruefung->uid."result", $result);
				$pruefung->note = $this->getDataOrTerminateWithError($result);
//				$this->addMeta($pruefung->uid."note", $pruefung->note);
			}
			
			$student_uid = $pruefung->uid;
			$typ = $pruefung->typ;
			$note = $pruefung->note; // TODO: parameterize for import maybe
			$datum = $pruefung->datum;
			$punkte = $pruefung->punkte;

			$lehreinheit_id = $pruefung->lehreinheit_id;

			// server-side add guards (max Antritte, chronological order, occurrence limit)
			$addError = $this->validatePruefungAdd($student_uid, $lv_id, $sem_kurzbz, $note, $datum);
			if($addError !== null) {
				$ret[$student_uid] = $addError; // string result is surfaced per-student in the UI
				continue;
			}

			$ret[$student_uid] = $this->savePruefungstermin($typ, $student_uid, $lv_id, $sem_kurzbz, $lehreinheit_id, $note, $punkte, $datum);
		}

		$this->logLib->logInfoDB(array('savePruefungenBulk',$ret, getAuthUID(), getAuthPersonId()));
		
		$this->terminateWithSuccess($ret);
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
		
		$result = $this->NotenschluesselaufteilungModel->getNote($punkte, $lv_id, $sem_kurzbz);
		$data = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($data);
		
	}

}

