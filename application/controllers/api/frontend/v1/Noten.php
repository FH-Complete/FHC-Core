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

	public function getCisConfig() {
		// resolved from tbl_note (Bezeichnung) with config fallback -> single source of truth
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

				// the default column layout ('antritt' or 'datum'); the user can change it in the tool
				'CIS_GESAMTNOTE_PRUEFUNGSSPALTEN' => $this->config->item('CIS_GESAMTNOTE_PRUEFUNGSSPALTEN'),

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
	 * degree programmes they are entitled for, and picks one before a course is loaded.
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

	/**
	 * GET METHOD
	 * expects 'studiengang_kz', 'sem_kurzbz'
	 * Returns the Lehrveranstaltungen of a Studiengang in a Studiensemester for the Assistenz flow.
	 * The caller may load only the degree programmes of lehre/benotungstool_assistenz.
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
	 * At the end it also fetches all exams of every student for that course and semester.
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

			// Read without the filter. getLvGesamtNoten() gives released grades only. With that getter
			// the course grade and the release state are empty after a reload.
			$lvgesamtnote = $this->getLvGesamtnoteRow($lv_id, $uid, $sem_kurzbz);
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
				
				// Without a partial grade that counts there is no average. Without this guard the
				// division uses zero. PHP 7 gives INF, and INF gives the best grade. PHP 8 stops with
				// a fatal error and the full grade table stays empty.
				$gewichtet = defined('CIS_GESAMTNOTE_GEWICHTUNG') && CIS_GESAMTNOTE_GEWICHTUNG;
				$divisor = $gewichtet ? $gewichtsumme : $anzahlnoten;

				if ($divisor > 0) {
					if (CIS_GESAMTNOTE_PUNKTE) {
						$punkte_vorschlag = round(($gewichtet ? $punktesumme_gewichtet : $punktesumme) / $divisor, 2);
						$note_vorschlag_result = $this->NotenschluesselaufteilungModel->getNote($punkte_vorschlag, $lv_id, $sem_kurzbz);
						$note_vorschlag = $this->getDataOrTerminateWithError($note_vorschlag_result);
					} else {
						$note_vorschlag = round(($gewichtet ? $notensumme_gewichtet : $notensumme) / $divisor);
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
				$grades[$uid]['verlauf'] = $this->verlaufSummary(
					$verlauf, false, $this->getLvGesamtnoteRow($lv_id, $uid, $sem_kurzbz) !== null
				);
			}
		}

		$this->terminateWithSuccess(array($studentenData, $pruefungenAbgeleitet, DOMAIN, $grades, $anwresult));
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
								'verlauf' => $this->buildVerlaufSummary($note->uid, $lv_id, $sem_kurzbz)
							);
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
	 * exam, and sets the grade of that exam.
	 * Updates the course grade of the student. It never writes the transcript grade, the student
	 * administration does that.
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
			$this->load->model('education/Note_model', 'NoteModel');
			$result = $this->NoteModel->getNochNichtEingetragenNote();
			$note = getData($result)[0]->note;
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

	/**
	 * The core of one exam entry for ONE student: validate, write the course grade, write the exam.
	 * The dialog and the bulk endpoints both use this method. The transcript grade stays unchanged.
	 *
	 * @return array|string ['savedPruefung', 'lvgesamtnote', 'verlauf'] or an error message
	 */
	private function savePruefungFuerStudent($pruefung_id, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum, $mitarbeiter_uid = null)
	{
		// §7 and §11: every exam must take place before the grade entry deadline. The kommissionelle
		// Prüfung is the important case, because it decides if the student loses a semester.
		$fristError = $this->pruefungsdatumNachFrist($stsem, $datum, $student_uid);
		if($fristError !== null) return $fristError;

		// validate before any write
		$editError = $this->validatePruefungEdit($student_uid, $lva_id, $stsem, $note, $datum, $pruefung_id);
		if($editError !== null) return $editError;

		if($pruefung_id === null || $pruefung_id === '') {
			$addError = $this->validatePruefungAdd($student_uid, $lva_id, $stsem, $note, $datum);
			if($addError !== null) return $addError;
		}

		// the client does not always send it; without it the insert fails on the NOT NULL column
		if(!$lehreinheit_id) {
			$resLe = $this->LehrveranstaltungModel->getLeByStudent($student_uid, $stsem, $lva_id);
			if(!isError($resLe) && hasData($resLe)) $lehreinheit_id = current(getData($resLe))->lehreinheit_id;
		}

		$jetzt = date("Y-m-d H:i:s");

		// Read without the filter. A grade that is not released still exists. You must update it.
		// A new insert breaks the primary key.
		$bestehendeLvNote = $this->getLvGesamtnoteRow($lva_id, $student_uid, $stsem);

		// this decides if the course grade is the implicit first attempt; read it before the update
		$origLvNote = $bestehendeLvNote ? $bestehendeLvNote->note : null;
		$lvgesamtnote = $bestehendeLvNote;

		if($bestehendeLvNote === null) {
			$lvgesamtnote = $this->createLvGesamtnote($lva_id, $student_uid, $stsem, $note, $punkte, $lehreinheit_id, $mitarbeiter_uid);

			$this->logLib->logInfoDB(array('pruefung: lvnote angelegt', $student_uid, $lva_id, $stsem,
				$note, $punkte, getAuthUID(), getAuthPersonId()));
		} else {
			$id = $this->LvgesamtnoteModel->update(
				[$bestehendeLvNote->student_uid, $bestehendeLvNote->studiensemester_kurzbz, $bestehendeLvNote->lehrveranstaltung_id],
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

			$this->logLib->logInfoDB(array('pruefung: lvnote aktualisiert', $student_uid, $lva_id, $stsem,
				$note, $punkte, getAuthUID(), getAuthPersonId()));
		}

		// save pruefung after updating lvnote, since pruefungspunkte get loaded by lv punkte
		$pruefungenChanged = $this->savePruefungstermin($pruefung_id, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum, $origLvNote, $mitarbeiter_uid);
		if(is_string($pruefungenChanged)) return $pruefungenChanged;

		$pruefungenChanged['lvgesamtnote'] = $lvgesamtnote;

		return $pruefungenChanged;
	}

	/**
	 * Creates an exam or updates an exam. The exam type has no meaning here: the position comes
	 * from the history. The code writes pruefungstyp_kurzbz, but it never reads it.
	 *
	 * @param int    $pruefung_id  gesetzt = diesen Datensatz bearbeiten, null = neuer Antritt
	 * @return array|string        the changed exams or a translated error message
	 */
	private function savePruefungstermin($pruefung_id, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum, $origLvNote = null, $mitarbeiter_uid = null)
	{
		// no exam without a course grade (a grade that is not released also counts)
		if($this->getLvGesamtnoteRow($lva_id, $student_uid, $stsem) === null) {
			return $this->p->t('benotungstool', 'c4keineLvNoteEingetragen');
		}

		$pruefungen = $this->VerlaufLib->getPruefungen($student_uid, $lva_id, $stsem);

		$status = [];

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
			$entschuldigtNote = $this->VerlaufLib->getSpecialNotes()['entschuldigt'];

			// the limit was checked on the grade BEFORE the override, therefore check it again
			if($entschuldigtNote !== null
				&& !$this->VerlaufLib->ueberschreitetNotenLimit($pruefungen, $entschuldigtNote, $pruefung_id)) {
				$note = $entschuldigtNote;
			}
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
			if($id) {
				$res = $this->LePruefungModel->load($id->retval);
				if(hasData($res)) $pruefungenChanged['savedPruefung'] = getData($res);
			}

			$this->logLib->logInfoDB(array('pruefung updated', $res, getAuthUID(), getAuthPersonId()));

			if(!isset($pruefungenChanged['savedPruefung'])) {
				return $this->p->t('benotungstool', 'c4pruefungNichtGespeichert', [$student_uid]);
			}

			$pruefungenChanged['verlauf'] = $this->buildVerlaufSummary($student_uid, $lva_id, $stsem);
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

		$pruefungenChanged['verlauf'] = $this->buildVerlaufSummary($student_uid, $lva_id, $stsem);
		return $pruefungenChanged;
	}

	/** The history for the client. Each write answer contains it, so the client calculates nothing. */
	private function buildVerlaufSummary($student_uid, $lva_id, $stsem)
	{
		$lvNote = $this->getLvGesamtnoteRow($lva_id, $student_uid, $stsem);
		$verlauf = $this->VerlaufLib->getVerlauf(
			$student_uid, $lva_id, $stsem,
			$lvNote ? $lvNote->note : null,
			$this->getZeugnisnote($lva_id, $student_uid, $stsem)
		);

		return $this->verlaufSummary($verlauf, true, $lvNote !== null);
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
			'erstantrittMoeglich' => $verlauf->erstantrittMoeglich,
			'naechsteRolle' => $verlauf->naechsteRolle,
			// credited: the row is visible, but you cannot select it and it gets no exams
			'angerechnet' => $verlauf->angerechnet,
			'hatLvNote' => $hatLvNote // ungefiltert, also inklusive noch nicht freigegebener
		);

		if($withPruefungen) $summary['pruefungen'] = $verlauf->pruefungen;

		return $summary;
	}

	/**
	 * Guards an EDIT ($pruefung_id is set; if not, validatePruefungAdd applies). The date must stay
	 * between the two adjacent exams. An exam with a later date locks the grade.
	 *
	 * @return string|null lokalisierte Fehlermeldung oder null
	 */
	private function validatePruefungEdit($student_uid, $lva_id, $stsem, $newNote, $newDatum, $pruefung_id)
	{
		if($pruefung_id === null || $pruefung_id === '') return null; // add, not an edit

		$zeugnisNote = $this->getZeugnisnote($lva_id, $student_uid, $stsem);
		if($this->VerlaufLib->istAnrechnungsnote($zeugnisNote)) {
			return $this->p->t('benotungstool', 'c4angerechnetKeinePruefung', [$student_uid]);
		}

		$pruefungen = $this->VerlaufLib->getPruefungen($student_uid, $lva_id, $stsem);
		if(count($pruefungen) === 0) return null;

		$verlauf = $this->VerlaufLib->buildVerlauf($pruefungen, null, $zeugnisNote);

		// the record being edited
		$current = null;
		foreach($verlauf->pruefungen as $p) {
			if($p->pruefung_id == $pruefung_id) { $current = $p; break; }
		}
		if($current === null) return null;

		// The student administration owns the exams that use no attempt (zusKommPruef), so this tool
		// does not validate them. The kommissionelle Prüfung itself is a normal attempt now.
		if(in_array($current->pruefungstyp_kurzbz, $this->VerlaufLib->getTypenOhneAntritt())) return null;

		$currentDate = substr((string)$current->datum, 0, 10);
		$new         = substr((string)$newDatum, 0, 10);

		// the limits come from the two exams before and after this one
		$lower = null; $upper = null;
		foreach($verlauf->pruefungen as $p) {
			if($p->pruefung_id == $current->pruefung_id) continue;

			$d = substr((string)$p->datum, 0, 10);
			if($d === '') continue;

			if($d < $currentDate) { if($lower === null || $d > $lower) $lower = $d; }
			elseif($d > $currentDate) { if($upper === null || $d < $upper) $upper = $d; }
		}

		// grade is locked once a later attempt exists
		if($this->VerlaufLib->hatSpaeterenTermin($verlauf, $current->pruefung_id) && $newNote != $current->note) {
			return $this->p->t('benotungstool', 'pruefungNoteLocked', [$student_uid]);
		}

		// datum must stay strictly between the neighbouring exam dates
		if(($lower !== null && $new <= $lower) || ($upper !== null && $new >= $upper)) {
			return $this->p->t('benotungstool', 'pruefungDatumOutOfRange', [$student_uid]);
		}

		// an edit may also change the note (e.g. to 'entschuldigt') -> keep the occurrence limit
		if($this->VerlaufLib->ueberschreitetNotenLimit($verlauf->pruefungen, $newNote, $current->pruefung_id)) {
			return $this->p->t('benotungstool', 'noteOccuranceLimitReached', [$student_uid]);
		}

		return null;
	}

	/**
	 * Guards a NEW attempt. Rule A is the attempt limit, and nothing follows a final exam. Rule B
	 * is the chronological order. Rule C is the occurrence limit of a grade.
	 *
	 * @return string|null lokalisierte Fehlermeldung oder null
	 */
	private function validatePruefungAdd($student_uid, $lva_id, $stsem, $note, $datum)
	{
		$zeugnisNote = $this->getZeugnisnote($lva_id, $student_uid, $stsem);
		if($this->VerlaufLib->istAnrechnungsnote($zeugnisNote)) {
			return $this->p->t('benotungstool', 'c4angerechnetKeinePruefung', [$student_uid]);
		}

		$pruefungen = $this->VerlaufLib->getPruefungen($student_uid, $lva_id, $stsem);

		// the implicit first attempt; read it before the caller changes it
		$lvRow = $this->getLvGesamtnoteRow($lva_id, $student_uid, $stsem);
		$lvNote = $lvRow ? $lvRow->note : null;

		$verlauf = $this->VerlaufLib->buildVerlauf($pruefungen, $lvNote, $zeugnisNote);

		// A: the first attempt only materialises the course grade, therefore it adds no attempt
		if($verlauf->naechsteRolle !== PruefungsverlaufLib::ROLLE_ERSTANTRITT && !$verlauf->canAdd) {
			return $this->p->t('benotungstool', 'maxAntritteReached', [$student_uid, $verlauf->maxAntritte]);
		}

		// B: no attempt on the same day as an existing exam, and no attempt before it
		$newDate = substr((string)$datum, 0, 10);
		foreach($pruefungen as $p) {
			$d = substr((string)$p->datum, 0, 10);
			if($d !== '' && $d >= $newDate) {
				return $this->p->t('benotungstool', 'pruefungDatumBeforeExisting', [$student_uid]);
			}
		}

		// C: the occurrence limit, for example one 'entschuldigt' only
		if($this->VerlaufLib->ueberschreitetNotenLimit($pruefungen, $note, null)) {
			return $this->p->t('benotungstool', 'noteOccuranceLimitReached', [$student_uid]);
		}

		return null;
	}

	/**
	 * The active grades, keyed by their PK. getNoten() sends the client the same set, so both sides
	 * judge a grade by the same flags.
	 *
	 * @return array note (string) => zeile aus lehre.tbl_note
	 */
	private function aktiveNoten()
	{
		if($this->aktiveNotenCache !== null) return $this->aktiveNotenCache;

		$this->aktiveNotenCache = array();

		$result = $this->NoteModel->getAllActive();
		if(!isError($result) && hasData($result)) {
			foreach(getData($result) as $n) $this->aktiveNotenCache[(string)$n->note] = $n;
		}

		return $this->aktiveNotenCache;
	}

	/**
	 * Guards a DIRECT api write of the course grade (saveNotenvorschlag and its bulk variant). All
	 * three rules are also in the cell editor, but the API and the CSV import reach the server
	 * without it. The order follows the editor: grade, exam, transcript.
	 *
	 * @return string|null lokalisierte Fehlermeldung oder null
	 */
	private function validateNotenvorschlag($lva_id, $student_uid, $stsem, $note)
	{
		$noten = $this->aktiveNoten();
		$wert  = trim((string)$note);

		// the editor offers the lehre grades only; an administrative grade belongs to the transcript
		if(!isset($noten[$wert]) || !$noten[$wert]->lehre) {
			return $this->p->t('benotungstool', 'c4noteNichtInLehre', [$student_uid]);
		}

		// as soon as an exam exists the grade belongs to the attempt history and follows its rules
		if(count($this->VerlaufLib->getPruefungen($student_uid, $lva_id, $stsem)) > 0) {
			return $this->p->t('benotungstool', 'c4notenvorschlagGesperrt', [$student_uid]);
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
	 * Checks the DATE of the exam against the deadline. enforceNoteneintragungsfrist compares the
	 * current time, which is a different question: a user can enter an exam today and give it a
	 * date after the deadline.
	 *
	 * @return string|null a translated error message, or null if the date is permitted
	 */
	private function pruefungsdatumNachFrist($sem_kurzbz, $datum, $student_uid)
	{
		if(!$this->config->item('CIS_GESAMTNOTE_NOTENEINTRAGUNGSFRIST')) return null;

		$deadline = $this->computeNoteneintragungsfrist($sem_kurzbz);
		if($deadline === null) return null;

		$tag = substr((string) $datum, 0, 10);
		if($tag === '' || $tag <= $deadline->format('Y-m-d')) return null;

		return $this->p->t('benotungstool', 'pruefungsdatumNachFrist', [$student_uid, $deadline->format('d.m.Y')]);
	}

	/** Stops the request if the grade entry deadline of the semester has passed. */
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
	 * The answer uses the uid as the key. Each row holds the course grade or an error message.
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

		// examination rules: no entry and no change after the grade entry deadline
		$this->enforceNoteneintragungsfrist($sem_kurzbz);

		$retLvNoten = [];
		
		foreach($noten as $note)
		{

			$result = $this->LvgesamtnoteModel->getLvGesamtNoteVorschlag($lv_id, $note->uid, $sem_kurzbz);
//			$this->addMeta($note->uid.'$result', $result);
			
			if(CIS_GESAMTNOTE_PUNKTE) {
				$abgeleitet = $this->noteAusPunkten($note->punkte, $lv_id, $sem_kurzbz, $note->uid);
				// no grade can be derived: skip the row, but do not stop the full request
				if(is_string($abgeleitet)) { $retLvNoten[$note->uid] = $abgeleitet; continue; }
				$note->note = $abgeleitet;
			}

			// one bad row must not stop the import, so the message goes into this row
			$fehler = $this->validateNotenvorschlag($lv_id, $note->uid, $sem_kurzbz, $note->note);
			if($fehler !== null) { $retLvNoten[$note->uid] = $fehler; continue; }

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
				if($id) {
					$res = $this->LvgesamtnoteModel->load($id->retval);
					if(hasData($res)) $lvgesamtnote = getData($res)[0];
				}

				$this->logLib->logInfoDB(array('saveNotenvorschlagBulk insert lv gesamtnote',$res, getAuthUID(), getAuthPersonId()));
			}

			$retLvNoten[$note->uid] = $lvgesamtnote;
		}

		$this->terminateWithSuccess($retLvNoten);
	}

	/**
	 * POST METHOD
	 * expects 'uids', 'datum', optional 'note'/'punkte'
	 * The bulk variant of saveStudentPruefung. It creates one exam for several students.
	 * Without a selected grade it writes "noch nicht eingetragen".
	 */
	public function createPruefungen() {
		$payload = $this->getPostJSON();

		if(!property_exists($payload, 'uids') || !property_exists($payload, 'datum')) {
			$this->terminateWithError($this->p->t('global', 'missingParameters'), 'general');
		}

		$uids = $payload->uids;
		$datum = $payload->datum;
		$lva_id = $payload->lva_id;

		$stsem = $payload->sem_kurzbz;

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
			$resNote = $this->NoteModel->getNochNichtEingetragenNote();
			$note = getData($resNote)[0]->note;
			$punkte = null;
		}

		// the dialog sends the teacher when all selected students share one Lehreinheit
		$mitarbeiter_uid = property_exists($payload, 'mitarbeiter_uid') ? $payload->mitarbeiter_uid : null;

		// the same core as the dialog; each row gets its own error message
		foreach ($uids as $student) {
			$ret[$student->uid] = $this->savePruefungFuerStudent(
				null, $student->uid, $lva_id, $stsem, $student->lehreinheit_id, $note, $punkte, $datum,
				$mitarbeiter_uid
			);
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

		// examination rules: no entry after the grade entry deadline
		$this->enforceNoteneintragungsfrist($sem_kurzbz);

		$ret = [];

		foreach ($pruefungen as $pruefung) {

			if(CIS_GESAMTNOTE_PUNKTE) {
				$note = $this->noteAusPunkten($pruefung->punkte, $lv_id, $sem_kurzbz, $pruefung->uid);
				// no grade can be derived: skip the row, but do not stop the full request
				if(is_string($note)) { $ret[$pruefung->uid] = $note; continue; }
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
	 * Creates or updates the first attempt. The release calls it.
	 *
	 * The date is the benotungsdatum of the course grade, which is the moment the teacher recorded
	 * the assessment. The examination rules ask for the date of the last performance (§8), but the
	 * system does not hold that date, because many courses have no exam date at all. The old tool
	 * used the benotungsdatum for the same row, so the value stays comparable with the old data.
	 *
	 * It applies only while one exam or no exam exists. After that the course grade holds the last
	 * grade, and it must not overwrite attempt 1. A credited grade changes nothing.
	 */
	/**
	 * The grade from the points for ONE bulk row. It gives the grade, or a translated error message
	 * if no grade can be derived. The caller then skips the row and continues with the other rows.
	 *
	 * @return mixed|string
	 */
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

	private function upsertErstantritt($lva_id, $student_uid, $stsem, $note, $punkte, $datum)
	{
		if($this->VerlaufLib->istAnrechnungsnote($this->getZeugnisnote($lva_id, $student_uid, $stsem))) return;

		$pruefungen = $this->VerlaufLib->getPruefungen($student_uid, $lva_id, $stsem);
		if(count($pruefungen) > 1) return;

		$jetzt = date("Y-m-d H:i:s");

		if(count($pruefungen) === 1) {
			$this->LePruefungModel->update(
				$pruefungen[0]->pruefung_id,
				array(
					'note' => $note,
					'punkte' => $punkte,
					'datum' => $datum,
					'updateamum' => $jetzt,
					'updatevon' => getAuthUID()
				)
			);

			$this->logLib->logInfoDB(array('erstantritt aktualisiert (freigabe)', $student_uid, getAuthUID(), getAuthPersonId()));
			return;
		}

		// the server finds the lehreinheit_id; it does not use the value from the client
		$resLe = $this->LehrveranstaltungModel->getLeByStudent($student_uid, $stsem, $lva_id);
		if(isError($resLe) || !hasData($resLe)) return;
		$le = current(getData($resLe));

		$this->LePruefungModel->insert(
			array(
				'lehreinheit_id' => $le->lehreinheit_id,
				'student_uid' => $student_uid,
				'mitarbeiter_uid' => $this->benotenderMitarbeiter($le->lehreinheit_id),
				'note' => $note,
				'punkte' => $punkte,
				'pruefungstyp_kurzbz' => $this->VerlaufLib->legacyTypFuerAntritt(1),
				'datum' => $datum,
				'anmerkung' => "",
				'insertamum' => $jetzt,
				'insertvon' => getAuthUID(),
				'updateamum' => null,
				'updatevon' => null,
				'ext_id' => null
			)
		);

		$this->logLib->logInfoDB(array('erstantritt angelegt (freigabe)', $student_uid, getAuthUID(), getAuthPersonId()));
	}

	/**
	 * The teachers of one Lehreinheit, from lehre.tbl_lehreinheitmitarbeiter.
	 *
	 * @return array
	 */
	private function lehrendeDerLehreinheit($lehreinheit_id)
	{
		if(!$lehreinheit_id) return array();

		$this->load->model('education/Lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');
		$result = $this->LehreinheitmitarbeiterModel->getLektorenByLe($lehreinheit_id);
		if(isError($result) || !hasData($result)) return array();

		$lehrende = getData($result);
		usort($lehrende, function($a, $b) {
			return strcmp($a->mitarbeiter_uid, $b->mitarbeiter_uid);
		});

		return $lehrende;
	}

	/**
	 * The uid of the person who gave the grade. It goes into lehre.tbl_pruefung and into
	 * campus.tbl_lvgesamtnote.
	 *
	 * Historically only teachers used this tool, so the caller was always right. An assistant may
	 * do the data entry too, but the grade still comes from the teacher. The row must therefore
	 * carry the teacher of the Lehreinheit, not the person who types. insertvon and updatevon keep
	 * the caller, because they answer a different question.
	 *
	 * Order: a valid selection wins, then the caller if the caller teaches this Lehreinheit, then
	 * the only teacher. With several teachers and no selection the first one wins, because an
	 * assistant is never the correct answer. Without any teacher the caller stays.
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

	/**
	 * The same answer for a course grade, which has no Lehreinheit of its own. The Lehreinheit of
	 * the student decides.
	 *
	 * @return string
	 */
	private function benotenderMitarbeiterFuerStudent($lva_id, $student_uid, $stsem, $gewaehlt = null)
	{
		$lehreinheit_id = null;

		$resLe = $this->LehrveranstaltungModel->getLeByStudent($student_uid, $stsem, $lva_id);
		if(!isError($resLe) && hasData($resLe)) $lehreinheit_id = current(getData($resLe))->lehreinheit_id;

		return $this->benotenderMitarbeiter($lehreinheit_id, $gewaehlt);
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

	/**
	 * Reads the course grade WITHOUT the filter. getLvGesamtNoten() uses 'freigabedatum < NOW()'
	 * and therefore hides a new grade. In this tool this wrapper is always the correct one.
	 */
	private function getLvGesamtnoteRow($lva_id, $student_uid, $stsem)
	{
		$res = $this->LvgesamtnoteModel->getLvGesamtNoteVorschlag($lva_id, $student_uid, $stsem);
		return (!isError($res) && hasData($res)) ? getData($res)[0] : null;
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
	 * GET METHOD
	 * expects 'lehreinheit_id', 'lv_id', 'sem_kurzbz'
	 * The teachers of one Lehreinheit. The dialog needs them when the Lehreinheit has more than one,
	 * because the exam must carry the teacher and not the person who types.
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

