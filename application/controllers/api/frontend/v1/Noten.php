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
		parent::__construct([
			'getStudentenNoten' => array('lehre/benotungstool:rw'),
			'getNoten' => array('lehre/benotungstool:rw'),
			'saveStudentenNoten' => array('lehre/benotungstool:rw'),
			'getNotenvorschlagStudent' => array('lehre/benotungstool:rw'),
			'saveNotenvorschlag' => array('lehre/benotungstool:rw'),
			'saveStudentPruefung' => array('lehre/benotungstool:rw'),
			'createPruefungen' => array('lehre/benotungstool:rw'),
			'saveNotenvorschlagBulk' => array('lehre/benotungstool:rw'),
			'savePruefungenBulk' => array('lehre/benotungstool:rw'),
			'getCisConfig' => array('lehre/benotungstool:rw'),
			'getNoteByPunkte' => array('lehre/benotungstool:rw')
		]);

		$this->load->library('AuthLib', null, 'AuthLib');
		$this->load->library('PhrasesLib');
		
		// Loads phrases system
		$this->loadPhrases([
			'global',
			'person',
			'benotungstool',
			'lehre',
			'ui'
		]);
		
		$this->load->model('education/LePruefung_model', 'LePruefungModel');
		$this->load->model('education/Lvgesamtnote_model', 'LvgesamtnoteModel');
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->load->model('education/Notenschluesselaufteilung_model', 'NotenschluesselaufteilungModel');
		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('codex/Mobilitaet_model', 'MobilitaetModel');
		$this->load->model('organisation/Erhalter_model', 'ErhalterModel');

		$this->load->helper('hlp_sancho_helper');

	}

	public function getCisConfig() {
		$this->terminateWithSuccess(
			array(
				// Punkte bei der Noteneingabe anzeigen
				'CIS_GESAMTNOTE_PUNKTE' => CIS_GESAMTNOTE_PUNKTE,
				
				// basically on/of toggle for the points/grade col and the arrow button
				'CIS_GESAMTNOTE_UEBERSCHREIBEN' => CIS_GESAMTNOTE_UEBERSCHREIBEN,
				
				// only relevant in punkte calculation in backend
//				'CIS_GESAMTNOTE_GEWICHTUNG' => CIS_GESAMTNOTE_GEWICHTUNG,
				
				// this one should always be set true since fh prüfungsordnung requires at least 3 antritte (t1+t2+kP)
//				'CIS_GESAMTNOTE_PRUEFUNG_TERMIN2' => CIS_GESAMTNOTE_PRUEFUNG_TERMIN2,
			
			
				// TODO
				// should in 99% of cases be kept true to enable 4 antritte in total, but if a certain
				// fh still works with 3 antritte per note this can limit the max number of pruefungen accordingly
				'CIS_GESAMTNOTE_PRUEFUNG_TERMIN3' => CIS_GESAMTNOTE_PRUEFUNG_TERMIN3,
				
				// used to toggle availability of kommPruef type pruefungen
				'CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF' => CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF,
				
				//technically exists but is never used, could be LE pendant to next flag
//				'CIS_GESAMTNOTE_PRUEFUNG_MOODLE_NOTE' => CIS_GESAMTNOTE_PRUEFUNG_MOODLE_NOTE,
			
				// basically a toggle for "use teilnoten" and the source is always moodle
				// setting this to false breaks legacy tool and if that was fixed it wouldnt render any table at all
				// anyway so not sure why this even is a config at all. placebo at best
				
				// TODO: do we really need this?
				'CIS_GESAMTNOTE_PRUEFUNG_MOODLE_LE_NOTE' => CIS_GESAMTNOTE_PRUEFUNG_MOODLE_LE_NOTE,
				
				// send a mail when approving grades
				'CIS_GESAMTNOTE_FREIGABEMAIL_NOTE' => CIS_GESAMTNOTE_FREIGABEMAIL_NOTE
			)
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
		
		// get studenten for lva & sem with zeugnisnote if available
		$studenten = $this->LehrveranstaltungModel->getStudentsByLv($sem_kurzbz, $lv_id);
		$studentenData = $this->getDataOrTerminateWithError($studenten);
		
		if(count($studentenData) == 0) {
			$this->terminateWithError('No students found for lva and semester');
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
			
//			$res = $this->StudentModel->load([$uid]);
//			if(!isError($res) && hasData($res)) $student = getData($res)[0];
			
			$result = $this->LvgesamtnoteModel->getLvGesamtNoten($lv_id, $uid, $sem_kurzbz);

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
			$this->addMeta('getExternalGradesError', $t->getMessage());
		}
		
		// assign the anw% to the students in the studentData loop
		$anwresult = $this->getAnwesenheiten($prestudent_ids, $lv_id, $sem_kurzbz);
		
		// calculate notenvorschläge from teilnoten
		foreach($studentenData as $student) {
			
			$student->anwquote = $anwresult[$student->prestudent_id];
			
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
						$note_vorschlag = $this->NotenschluesselaufteilungModel->getNote($punkte_vorschlag, $lv_id, $sem_kurzbz);
					} else {
						$punkte_vorschlag = round($punktesumme / $anzahlnoten, 2);
						$note_vorschlag = $this->NotenschluesselaufteilungModel->getNote($punkte_vorschlag, $lv_id, $sem_kurzbz);
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
			$this->terminateWithError($this->p->t('global', 'wrongPassword'), 'general');
		}
		
		$lv_id = $result->lv_id;
		$sem_kurzbz = $result->sem_kurzbz;
		
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
			if(defined(CIS_GESAMTNOTE_PUNKTE) && CIS_GESAMTNOTE_PUNKTE) {
				$studlist .= "<td><b>" . $this->p->t('benotungstool','c4punkte') . "</b></td>\n";
			}
			$studlist .= "<td><b>" . $this->p->t('benotungstool','c4grade') . "</b></td>\n";
			$studlist .= "<td><b>" . $this->p->t('ui','bearbeitetVon') . "</b></td></tr>\n";
		} else {
			$studlist .= "<td><b>" . $this->p->t('person','uid') . "</b></td></tr>\n";
		}
		
		foreach($result->noten as $note) {

			$resultLVGes = $this->LvgesamtnoteModel->getLvGesamtNoten($lv_id, $note->uid, $sem_kurzbz);

			if (!isError($resultLVGes) && hasData($resultLVGes))
			{
				$lvgesamtnote = getData($resultLVGes)[0];

				if ($lvgesamtnote->benotungsdatum > $lvgesamtnote->freigabedatum)
				{

					$id = $this->LvgesamtnoteModel->update(
						[$lvgesamtnote->student_uid, $lvgesamtnote->studiensemester_kurzbz, $lvgesamtnote->lehrveranstaltung_id],
						array(
							'note' => $note->note,
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

						if(defined(CIS_GESAMTNOTE_PUNKTE) && CIS_GESAMTNOTE_PUNKTE) {
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
		
		// TODO: we need a zuordnungscheck here? any lektor can get any grades?
		// what about assistenz with different rights doing lectors job once again?
		// students checking their own grades?
		
		
		$result = $this->LvgesamtnoteModel->getLvGesamtNoten($lv_id, $uid, $sem_kurzbz);
		$data = $this->getDataOrTerminateWithError($result);
		
		// TODO: moodle teilnote but it seems they only work for a whole course?
		
		// get anw% of student by prestudent_id
		$anwresult = $this->getAnwesenheiten($prestudent_ids, $lv_id, $sem_kurzbz);



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
		
		$stsem = $result->sem_kurzbz;
		$typ = $result->typ;
		
		$jetzt = date("Y-m-d H:i:s");
		
		// nachpruefungeintragen.php script calls query on campus.student_lehrveranstaltung to find a
		// lehreinheit_id for lva_id -> lehreinheit should be determined prior to that in new benotungstool
		// by retrieving it from students row in campus.vw_student_lehrveranstaltung earlier on
		
//		$lehreinheit_id = getLehreinheit($db, $lvid, $student_uid, $stsem);
//		$lehreinheit_id = $result->lehreinheit_id;
		
//		$punkte = null;

		if(isset($punkte) && $punkte >= 0) {
			// Bei Punkteeingabe wird die Note nochmals geprueft und ggf korrigiert
			$result = $this->NotenschluesselaufteilungModel->getNote($punkte, $lva_id, $stsem);
			if(isError($result)) {
				$this->terminateWithError('notenspiegel hats zrissen');
			} else {
				$data = getData($result);
				if($data != $note)
				{
					$note = $data;
				}
			}
			
		}

		// TODO: more sophisticated empty check
		if($note=='')
			$note = 9;
		

		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$res = $this->LehrveranstaltungModel->load($lva_id);
		if(isError($res) || !hasData($res)) {
			$this->terminateWithError('Keine gültige Lehrveranstaltung gefunden für ID: '.$lva_id);
		}
		
		$studiengang_kz = getData($res)[0]->studiengang_kz;
		$res = $this->StudiengangModel->load($studiengang_kz);
		if(isError($res) || !hasData($res)) {
			$this->terminateWithError('Kein gültiger Studiengang gefunden für ID: '.$studiengang_kz);
		}
		
		
		//Gesamtnote updaten
		$result = $this->LvgesamtnoteModel->getLvGesamtNoten($lva_id, $student_uid, $stsem);
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
		}
		else if(!isError($result) && hasData($result))
		{
			$lvgesamtnote = getData($result)[0];

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
			
		}
		
		// save pruefung after updating lvnote, since pruefungspunkte get loaded by lv punkte 
		$pruefungenChanged = $this->savePruefungstermin($typ, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum);

		$savedPruefung = $pruefungenChanged['savedPruefung'] ?? null;
		$extraPruefung = $pruefungenChanged['extraPruefung'] ?? null;

		$savedPruefungData = count($savedPruefung) > 0 ? $savedPruefung[0] : null;
		$extraPruefungData = count($extraPruefung) > 0 ? $extraPruefung[0] : null;
		
		$this->terminateWithSuccess(array($savedPruefungData, $lvgesamtnote, $extraPruefungData));
	}

	/**
	 * private helper method to update/insert pruefungstermine 
	 */
	private function savePruefungstermin($typ, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte = '', $datum) 
	{

		// extra check if the student has lvnote and a zeugnisnote in the relevant lva
		$resultLV = $this->LvgesamtnoteModel->getLvGesamtNoten($lva_id, $student_uid, $stsem);
		
		$lvgesamtnoteData = getData($resultLV);
		$this->addMeta('lvgesamtnoteData', $lvgesamtnoteData);
		
		// allocating pruefungen before lv note is forbidden
		if($lvgesamtnoteData == null) return $this->p->t('benotungstool', 'c4keineLvNoteEingetragen');
		
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
			$note = 17; //entschuldigt
		}
		
		$jetzt = date("Y-m-d H:i:s");
		
		$pruefungenChanged = [];
		
		$this->load->model('education/Lvgesamtnote_model', 'LvgesamtnoteModel');
		
		if($typ == "Termin2" && defined('CIS_GESAMTNOTE_PRUEFUNG_TERMIN2') && CIS_GESAMTNOTE_PRUEFUNG_TERMIN2) 
		{
			
			// Wenn eine Nachprüfung angelegt wird, wird zuerst eine Pruefung mit 1. Termin angelegt welche für die ursprüngliche Note
			// vor den Prüfungsantritten zählt
			
			$result1 = $this->LePruefungModel->getPruefungenByUidTypLvStudiensemester($student_uid, "Termin1", $lva_id, $stsem);
			
			// if there is a termin 1 entry already do nothing
			if(!isError($result1) && hasData($result1)) {

			} else if(!isError($result1) && !hasData($result1)) {
				// new entry termin1

				$resultLV = $this->LvgesamtnoteModel->getLvGesamtNoten($lva_id, $student_uid, $stsem);
				
				// update Termin1 note
				if (hasData($resultLV))
				{
					$lvgesamtnote = getData($resultLV)[0];
					$pr_note = $lvgesamtnote->note;
					$pr_punkte = $lvgesamtnote->punkte;
					$benotungsdatum = $lvgesamtnote->benotungsdatum;
				}
				else if(!hasData($resultLV))// set Termin1 note to "noch nicht eingetragen"
				{
					// TODO: avoid hardcoded noten primary keys!
					$pr_note = 9; 
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
			}
			
			
			// Die Pruefung wird als Termin2 eingetragen
			$result2 = $this->LePruefungModel->getPruefungenByUidTypLvStudiensemester($student_uid, "Termin2", $lva_id, $stsem);
			// if there is a termin 2 entry already update it
			if(!isError($result2) && hasData($result2)) {
				// update
				$termin2 = getData($result2)[0];
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

			} else if(!isError($result2) && !hasData($result2)) {
				// new entry termin 2

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
			}

		} else if($typ == "Termin3" && defined('CIS_GESAMTNOTE_PRUEFUNG_TERMIN3') && CIS_GESAMTNOTE_PRUEFUNG_TERMIN3) 
		{

			$result3 = $this->LePruefungModel->getPruefungenByUidTypLvStudiensemester($student_uid, "Termin3", $lva_id, $stsem);

			if(!isError($result3) && hasData($result3)) {
				// update
				$termin3 = getData($result3)[0];

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

			} else if(!isError($result3) && !hasData($result3)) {
				// insert new termin3

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
				
			}
		} else {
			$this->terminateWithError($this->p->t('benotungstool', 'wrongPruefungType', [$student_uid, $typ]), 'general');
		}
		
		return $pruefungenChanged;
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

		

		$result = $this->LvgesamtnoteModel->getLvGesamtNoten($lv_id, $student_uid, $sem_kurzbz);

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
		
		$retLvNoten = [];
		
		foreach($noten as $note)
		{

			$result = $this->LvgesamtnoteModel->getLvGesamtNoten($lv_id, $note->uid, $sem_kurzbz);

			if(!isError($result) && hasData($result)) {
				$lvgesamtnote = getData($result)[0];

				$id = $this->LvgesamtnoteModel->update(
					[$lvgesamtnote->student_uid, $lvgesamtnote->studiensemester_kurzbz, $lvgesamtnote->lehrveranstaltung_id],
					array(
						'note' => trim($note->note),
						'punkte' => null,
						'benotungsdatum' => date("Y-m-d H:i:s"),
						'updateamum' => date("Y-m-d H:i:s"),
						'updatevon' => getAuthUID()
					)
				);

				if($id) {
					$res = $this->LvgesamtnoteModel->load($id->retval);
					if(hasData($res)) $lvgesamtnote = getData($res)[0];
				}
			} else if(!isError($result) && !hasData($result)) {
				$id = $this->LvgesamtnoteModel->insert(
					array(
						'student_uid' => $note->uid,
						'lehrveranstaltung_id' => $lv_id,
						'studiensemester_kurzbz' => $sem_kurzbz,
						'note' => trim($note->note),
						'punkte' => null,
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
		
		$ret = [];
		
		foreach ($uids as $student) {
			$student_uid = $student->uid;
			$typ = $student->typ;
			$note = 9; //$result->note; // TODO: parameterize for import maybe
			$punkte = null; // new pruefungen never have punkte,

			$lehreinheit_id = $student->lehreinheit_id;
			$ret[$student->uid] = $this->savePruefungstermin($typ, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum);
		}

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
		
		$ret = [];

		foreach ($pruefungen as $pruefung) {
			$student_uid = $pruefung->uid;
			$typ = $pruefung->typ;
			$note = $pruefung->note; // TODO: parameterize for import maybe
			$datum = $pruefung->datum;
			$punkte = null;

			$lehreinheit_id = $pruefung->lehreinheit_id;
			$ret[$student_uid] = $this->savePruefungstermin($typ, $student_uid, $lv_id, $sem_kurzbz, $lehreinheit_id, $note, $punkte, $datum);
		}
		
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

		//  TODO validate post properly
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

