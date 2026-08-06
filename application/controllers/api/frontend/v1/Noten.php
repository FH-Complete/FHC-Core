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
				// Punkte bei der Noteneingabe anzeigen
				'CIS_GESAMTNOTE_PUNKTE' => CIS_GESAMTNOTE_PUNKTE,
				
				// basically on/of toggle for the points/grade col and the arrow button
				'CIS_GESAMTNOTE_UEBERSCHREIBEN' => CIS_GESAMTNOTE_UEBERSCHREIBEN,
				
				// only relevant in punkte calculation in backend
				// 'CIS_GESAMTNOTE_GEWICHTUNG' => CIS_GESAMTNOTE_GEWICHTUNG,
				
				// Maximale Anzahl zählender Antritte in diesem Tool. Serverseitig abgeleitet (explizite
				// Konfiguration oder Alt-Flags TERMIN2/TERMIN3); der Client rechnet das nicht mehr nach.
				'CIS_GESAMTNOTE_MAX_ANTRITTE' => $this->VerlaufLib->getMaxAntritte(),

				// Vorgabe der Spaltenaufteilung ('antritt' | 'datum'), im Tool umschaltbar
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

		// Prüfungsverlauf je Student serverseitig ableiten: Position, Antrittsnummer, ob ein Termin
		// einen Antritt verbraucht und ob noch einer möglich ist. Der Client wertet das nur noch aus,
		// damit die Regeln nicht in zwei Implementierungen auseinanderlaufen.
		$proStudent = [];
		foreach($pruefungenData ?: [] as $p) {
			$proStudent[$p->student_uid][] = $p;
		}

		// Zeugnisnoten kommen bereits mit der Studentenliste (tbl_zeugnisnote.note) - keine
		// Extra-Abfrage je Zeile nötig
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
							$ret[] = array('uid' => $note->uid, 'freigabedatum' => $lvgesamtnote->freigabedatum, 'benotungsdatum' => $lvgesamtnote->benotungsdatum);

							// Mit der Freigabe wird die Note verbindlich - genau hier entsteht der
							// erste Prüfungstermin. Das Anlegen einer Prüfung legt nie zusätzlich
							// einen zweiten Termin an.
							$this->upsertErstantritt(
								$lv_id, $lvgesamtnote->student_uid, $sem_kurzbz,
								$lvgesamtnote->note, $lvgesamtnote->punkte, $lvgesamtnote->freigabedatum
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
	 * Pruefung, sets the provided (Prüfungs-) Note.
	 * Updates the LvGesamtnote of student (niemals die Zeugnisnote - die Übernahme passiert in der STV).
	 * Can return 1 or 2 Prüfungen: fehlt der erste Antritt als eigener Datensatz (Altdaten), wird er
	 * beim Anlegen einer Wiederholung aus der ursprünglichen LV-Note nachgetragen.
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

		$result = $this->savePruefungFuerStudent($pruefung_id, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum);

		// Validierungs- und Schreibfehler kommen als lokalisierte Meldung zurück
		if(is_string($result)) $this->terminateWithError($result, 'general');

		$savedPruefung = $result['savedPruefung'] ?? [];
		$savedPruefungData = count($savedPruefung) > 0 ? $savedPruefung[0] : null;
		$lvgesamtnote = $result['lvgesamtnote'] ?? null;

		// eine Aktion = ein Termin, daher kein zusätzlicher Datensatz mehr in der Antwort
		$this->terminateWithSuccess(array($savedPruefungData, $lvgesamtnote, $result['verlauf'] ?? null));
	}

	/**
	 * Kern einer Prüfungseingabe für EINEN Studenten: prüfen, LV-Note schreiben, dann den Termin.
	 *
	 * Einzige Stelle, an der das passiert - der Dialog aus der Tabelle (saveStudentPruefung) und die
	 * Bulk-Endpunkte verwenden sie gleichermassen. Beide Wege tun damit exakt dasselbe, der Bulk-Weg
	 * nur für mehrere Studenten auf einmal. Die Zeugnisnote bleibt unberührt, deren Übernahme
	 * passiert in der Studierendenverwaltung.
	 *
	 * @return array|string ['savedPruefung' => [...], 'lvgesamtnote' => stdClass, 'verlauf' => [...]]
	 *                      oder eine lokalisierte Fehlermeldung
	 */
	private function savePruefungFuerStudent($pruefung_id, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum)
	{
		// validate the edit before any write: the date must stay between the neighbouring exams and,
		// once a later pruefung exists, the grade may no longer be changed (only the date).
		// only applies when editing an existing record ($pruefung_id set)
		$editError = $this->validatePruefungEdit($student_uid, $lva_id, $stsem, $note, $datum, $pruefung_id);
		if($editError !== null) return $editError;

		// for a NEW attempt (no pruefung_id) enforce the add rules server-side
		// maxAntritte calc, chronological order, occurrence limit for entschuldigt
		if($pruefung_id === null || $pruefung_id === '') {
			$addError = $this->validatePruefungAdd($student_uid, $lva_id, $stsem, $note, $datum);
			if($addError !== null) return $addError;
		}

		// fehlt die lehreinheit_id (der Client liefert sie nicht immer), serverseitig auflösen -
		// sonst scheitert das Insert stillschweigend am NOT NULL
		if(!$lehreinheit_id) {
			$resLe = $this->LehrveranstaltungModel->getLeByStudent($student_uid, $stsem, $lva_id);
			if(!isError($resLe) && hasData($resLe)) $lehreinheit_id = current(getData($resLe))->lehreinheit_id;
		}

		$jetzt = date("Y-m-d H:i:s");

		// ungefiltert: eine noch nicht freigegebene LV-Note existiert bereits und muss aktualisiert
		// statt neu angelegt werden (sonst Verstoss gegen den Primärschlüssel)
		$bestehendeLvNote = $this->getLvGesamtnoteRow($lva_id, $student_uid, $stsem);

		// ursprüngliche Note festhalten, bevor sie überschrieben wird: sie entscheidet, ob die
		// LV-Note als impliziter erster Antritt zählt
		$origLvNote = $bestehendeLvNote ? $bestehendeLvNote->note : null;
		$lvgesamtnote = $bestehendeLvNote;

		if($bestehendeLvNote === null) {
			$lvgesamtnote = $this->createLvGesamtnote($lva_id, $student_uid, $stsem, $note, $punkte);

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
		$pruefungenChanged = $this->savePruefungstermin($pruefung_id, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum, $origLvNote);
		if(is_string($pruefungenChanged)) return $pruefungenChanged;

		$pruefungenChanged['lvgesamtnote'] = $lvgesamtnote;

		return $pruefungenChanged;
	}

	/**
	 * Legt einen Prüfungstermin an oder aktualisiert einen bestehenden.
	 *
	 * Termintyp-agnostisch: die Position eines Antritts ergibt sich aus dem Verlauf (Datum bzw.
	 * Reihenfolge), nicht aus pruefungstyp_kurzbz. Der Alt-Typ wird beim Schreiben nur noch als
	 * abgeleitete Rückwärtskompatibilitäts-Projektion mitgeschrieben (siehe PruefungsverlaufLib),
	 * damit bestehende Auswertungen weiterlaufen; zurückgelesen wird er nie.
	 *
	 * @param int    $pruefung_id  gesetzt = genau diesen Datensatz bearbeiten, null = neuer Antritt
	 * @return array|string        geänderte Prüfungen oder eine lokalisierte Fehlermeldung
	 */
	private function savePruefungstermin($pruefung_id, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum, $origLvNote = null)
	{
		// allocating pruefungen before lv note is forbidden (ungefiltert: auch eine noch nicht
		// freigegebene LV-Note zählt als vorhanden, siehe getLvGesamtnoteRow)
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

			// the occurrence limit was validated on the PRE-override note, so re-check it here: only
			// apply the auto-excuse if it would not exceed the allowed number of excused Termine
			if($entschuldigtNote !== null
				&& !$this->VerlaufLib->ueberschreitetNotenLimit($pruefungen, $entschuldigtNote, $pruefung_id)) {
				$note = $entschuldigtNote;
			}
		}

		$jetzt = date("Y-m-d H:i:s");

		$pruefungenChanged = [];

		// Bearbeitung: der adressierte Datensatz wird geändert, seine Position im Verlauf ergibt
		// sich danach wieder aus dem Datum. Kein Typwechsel, keine Neuanlage.
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

		// Eine Aktion legt genau EINEN Termin an. Der erste Antritt entsteht nicht beiläufig hier,
		// sondern bei der Notenfreigabe (upsertErstantritt) - dort wird die Note verbindlich.

		$typ = ($rolle === PruefungsverlaufLib::ROLLE_ERSTANTRITT)
			? $this->VerlaufLib->legacyTypFuerAntritt(1)
			: $this->VerlaufLib->legacyTypFuerWiederholung($verlauf);

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

		$this->logLib->logInfoDB(array('pruefung inserted ('.$rolle.')', $res, getAuthUID(), getAuthPersonId()));

		// Schlägt das Insert fehl (zB fehlende lehreinheit_id), darf hier KEIN Erfolg zurückkommen:
		// der Client wertet die Antwort sonst als angelegt und zeigt einen leeren Verlauf an.
		if(!isset($pruefungenChanged['savedPruefung'])) {
			return $this->p->t('benotungstool', 'c4pruefungNichtGespeichert', [$student_uid]);
		}

		$pruefungenChanged['verlauf'] = $this->buildVerlaufSummary($student_uid, $lva_id, $stsem);
		return $pruefungenChanged;
	}

	/**
	 * Verlaufskennzahlen und die abgeleiteten Termine eines Studenten für den Client. Wird nach
	 * jeder Änderung mitgeliefert, damit die Oberfläche die Regeln nicht selbst nachrechnet und
	 * ihre Zeilen einfach aus dem Serverstand neu aufbauen kann.
	 */
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
	 * @param bool $withPruefungen Termine mitliefern (Schreibpfade) oder nur die Kennzahlen
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
			// Zeugnisnote ist eine Anrechnung -> Zeile wird angezeigt, ist aber nicht auswählbar und
			// bekommt keine Prüfungen
			'angerechnet' => $verlauf->angerechnet,
			// ob überhaupt eine LV-Note existiert - ungefiltert, also inklusive noch nicht
			// freigegebener. Die Oberfläche kennt über lv_note nur die freigegebene.
			'hatLvNote' => $hatLvNote
		);

		if($withPruefungen) $summary['pruefungen'] = $verlauf->pruefungen;

		return $summary;
	}

	/**
	 * Validates an edit to an existing pruefung. Returns a localized error string if the edit is
	 * not allowed, or null if it is. Mirrors the frontend guards so a disallowed edit cannot be
	 * forced through the API.
	 *
	 * Rules:
	 *  - The new $datum must stay strictly between the dates of the chronologically adjacent
	 *    pruefungen so the attempt order is preserved.
	 *  - Once a later attempt exists the grade may no longer be changed (only the datum may be
	 *    corrected within the bounds above).
	 *
	 * Only guards EDITS: $pruefung_id identifies the record being edited; when it is null a new
	 * attempt is being added and validatePruefungAdd applies instead.
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

		// Anrechnung: die Leistung wurde vorab anerkannt, an bestehenden Terminen wird nichts geändert
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

		// abschliessende Termine (kommPruef) werden in einem anderen Tool gepflegt
		if($current->terminal) return null;

		$currentDate = substr((string)$current->datum, 0, 10);
		$new         = substr((string)$newDatum, 0, 10);

		// chronologische Grenzen aus den unmittelbaren Datums-Nachbarn
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
	 * Validates ADDING a new pruefung (attempt). Returns an error string if the add is not
	 * allowed, or null if it is.
	 *
	 * Rules (Prüfungsordnung §1):
	 *  - Rule A: die Zahl der zählenden Prüfungsantritte darf das Maximum nicht überschreiten, und
	 *            nach einem abschliessenden Termin ist kein weiterer Antritt mehr möglich
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
		// Anrechnung: wer die Lehrveranstaltung angerechnet bekommen hat, tritt zu keiner Prüfung an
		$zeugnisNote = $this->getZeugnisnote($lva_id, $student_uid, $stsem);
		if($this->VerlaufLib->istAnrechnungsnote($zeugnisNote)) {
			return $this->p->t('benotungstool', 'c4angerechnetKeinePruefung', [$student_uid]);
		}

		$pruefungen = $this->VerlaufLib->getPruefungen($student_uid, $lva_id, $stsem);

		// current LV note (the implicit first Antritt), read before it gets mutated by the caller
		$lvRow = $this->getLvGesamtnoteRow($lva_id, $student_uid, $stsem);
		$lvNote = $lvRow ? $lvRow->note : null;

		$verlauf = $this->VerlaufLib->buildVerlauf($pruefungen, $lvNote, $zeugnisNote);

		// Rule A: max Antritte. Der erste Antritt materialisiert nur die ohnehin als Antritt gezählte
		// LV-Note und erhöht die Zahl der Antritte daher nicht.
		if($verlauf->naechsteRolle !== PruefungsverlaufLib::ROLLE_ERSTANTRITT && !$verlauf->canAdd) {
			return $this->p->t('benotungstool', 'maxAntritteReached', [$student_uid, $verlauf->maxAntritte]);
		}

		// Rule B: no new attempt on/before an existing dated attempt
		$newDate = substr((string)$datum, 0, 10);
		foreach($pruefungen as $p) {
			$d = substr((string)$p->datum, 0, 10);
			if($d !== '' && $d >= $newDate) {
				return $this->p->t('benotungstool', 'pruefungDatumBeforeExisting', [$student_uid]);
			}
		}

		// Rule C: occurrence limit (e.g. only one 'entschuldigt' across the Antritte)
		if($this->VerlaufLib->ueberschreitetNotenLimit($pruefungen, $note, null)) {
			return $this->p->t('benotungstool', 'noteOccuranceLimitReached', [$student_uid]);
		}

		return null;
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
	 * expects 'uids', 'datum', optional 'note'/'punkte'
	 * Bulk variant of saveStudentPruefung, used when creating a new Prüfung for several students.
	 * Ohne ausgewählte Note wird "noch nicht eingetragen" gesetzt.
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

		// Prüfungsordnung §1: no entry past the Noteneintragungsfrist
		$this->enforceNoteneintragungsfrist($stsem);

		$ret = [];

		$note = property_exists($payload, 'note') ? $payload->note : null;
		$punkte = property_exists($payload, 'punkte') ? $payload->punkte : null;

		// Punkteeingabe hat Vorrang: die Note kommt dann aus dem Notenschlüssel
		if(CIS_GESAMTNOTE_PUNKTE && $punkte !== null && $punkte !== '' && $punkte >= 0) {
			$resNote = $this->NotenschluesselaufteilungModel->getNote($punkte, $lva_id, $stsem);
			$note = $this->getDataOrTerminateWithError($resNote);
		}

		// ohne Auswahl bleibt der Termin unbenotet
		if($note === null || $note === '') {
			$resNote = $this->NoteModel->getNochNichtEingetragenNote();
			$note = getData($resNote)[0]->note;
			$punkte = null;
		}

		// identisch zum Dialog aus der Tabelle, nur für mehrere Studenten: derselbe Kern schreibt
		// LV-Note und Termin. Fehler kommen je Zeile als lokalisierte Meldung zurück.
		foreach ($uids as $student) {
			$ret[$student->uid] = $this->savePruefungFuerStudent(
				null, $student->uid, $lva_id, $stsem, $student->lehreinheit_id, $note, $punkte, $datum
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
			
			// identisch zum Dialog aus der Tabelle, nur je Importzeile
			$ret[$pruefung->uid] = $this->savePruefungFuerStudent(
				null, $pruefung->uid, $lv_id, $sem_kurzbz, $pruefung->lehreinheit_id,
				$pruefung->note, $pruefung->punkte, $pruefung->datum
			);
		}

		$this->logLib->logInfoDB(array('savePruefungenBulk',$ret, getAuthUID(), getAuthPersonId()));
		
		$this->terminateWithSuccess($ret);
	}
	
	/**
	 * Aktuelle LV-Gesamtnote eines Studenten oder null. In den Bulk-Pfaden ist sie zugleich die
	 * ursprüngliche Note, weil dort - anders als in saveStudentPruefung - vorher nichts
	 * überschrieben wird.
	 */
	/**
	 * Legt den ersten Prüfungstermin an oder aktualisiert ihn (Upsert).
	 *
	 * Aufgerufen bei der Notenfreigabe: erst dort wird die Note verbindlich, deshalb entsteht genau
	 * dann der erste Antritt - und nicht beiläufig beim Anlegen einer Prüfung. Datiert auf das
	 * Freigabedatum.
	 *
	 * Greift nur, solange höchstens ein Termin existiert: sobald Wiederholungen erfasst sind, führt
	 * die LV-Note die zuletzt erreichte Note und dürfte den ersten Antritt nicht mehr überschreiben.
	 * Bei einer Anrechnung passiert nichts.
	 *
	 * @return void
	 */
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

		// lehreinheit_id serverseitig auflösen statt sie vom Client zu übernehmen
		$resLe = $this->LehrveranstaltungModel->getLeByStudent($student_uid, $stsem, $lva_id);
		if(isError($resLe) || !hasData($resLe)) return;
		$le = current(getData($resLe));

		$this->LePruefungModel->insert(
			array(
				'lehreinheit_id' => $le->lehreinheit_id,
				'student_uid' => $student_uid,
				'mitarbeiter_uid' => getAuthUID(),
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
	 * Zeugnisnote eines Studenten in einer LV, oder null. Anrechnungen werden dort geführt (nicht in
	 * der LV-Note) und entscheiden darüber, ob überhaupt Prüfungen möglich sind.
	 *
	 * @return mixed|null
	 */
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

	private function getLvGesamtnoteRow($lva_id, $student_uid, $stsem)
	{
		// ACHTUNG: getLvGesamtNoten filtert auf 'freigabedatum < NOW()' und liefert daher NUR
		// freigegebene Noten. Für die Frage "existiert eine LV-Note?" ist das falsch - eine gerade
		// erst angelegte Note hat noch kein Freigabedatum und wäre unsichtbar (Endlos-Fehler
		// "keine LV-Note eingetragen" bzw. doppelter Insert). Deshalb hier der ungefilterte Zugriff.
		$res = $this->LvgesamtnoteModel->getLvGesamtNoteVorschlag($lva_id, $student_uid, $stsem);
		return (!isError($res) && hasData($res)) ? getData($res)[0] : null;
	}

	/**
	 * Legt eine LV-Gesamtnote an. Die Zeugnisnote bleibt unberührt - dieses Tool liest sie nur,
	 * die Übernahme in das Zeugnis passiert in der Studierendenverwaltung.
	 *
	 * @return stdClass|null
	 */
	private function createLvGesamtnote($lva_id, $student_uid, $stsem, $note, $punkte)
	{
		$jetzt = date("Y-m-d H:i:s");

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

