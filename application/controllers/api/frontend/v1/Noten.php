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
			'getStudentenNoten' => self::PERM_LOGGED, // todo: berechtigung
			'getNoten' => self::PERM_LOGGED,
			'saveStudentenNoten' => self::PERM_LOGGED, // todo: berechtigungen!
			'getNotenvorschlagStudent' => self::PERM_LOGGED,
			'saveNotenvorschlag' => self::PERM_LOGGED,
			'saveStudentPruefung' => self::PERM_LOGGED,
			'createPruefungen' => self::PERM_LOGGED,
			'saveNotenvorschlagBulk' => self::PERM_LOGGED,
			'savePruefungenBulk' => self::PERM_LOGGED
		]);

		$this->load->library('AuthLib', null, 'AuthLib');
		
		// Loads phrases system
		$this->loadPhrases([
			'global'
		]);
		require_once(FHCPATH . 'include/mobilitaet.class.php');
		require_once(FHCPATH . 'include/student.class.php');
		require_once(FHCPATH . 'include/lvgesamtnote.class.php');
		require_once(FHCPATH . 'include/lehrveranstaltung.class.php');
		require_once(FHCPATH . 'include/lehreinheit.class.php');
		require_once(FHCPATH . 'include/studiengang.class.php');
		require_once(FHCPATH . 'include/pruefung.class.php');
		
	}
	
	public function getStudentenNoten() {
		$lv_id = $this->input->get("lv_id",TRUE);
		$sem_kurzbz = $this->input->get("sem_kurzbz",TRUE);

		if (!isset($lv_id) || isEmptyString($lv_id)
			|| !isset($sem_kurzbz) || isEmptyString($sem_kurzbz))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');

		// todo: check various other berechtigungen if its mitarbeiter/lektor/zugeteilterLektor?

		$this->load->model('education/Pruefung_model', 'PruefungModel');
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');

		// get studenten for lva & sem with zeugnisnote if available
		$studenten = $this->LehrveranstaltungModel->getStudentsByLv($sem_kurzbz, $lv_id);
		$studentenData = $this->getDataOrTerminateWithError($studenten);
//		$studentenData = getData($studenten);
		$this->addMeta('$studentenData', $studentenData);
		
		$func = function ($value) {
			return $value->uid;
		};
		
		$grades = array();
		$student_uids = array_map($func, $studentenData);
		foreach($student_uids as $uid) {
			$grades[$uid]['grades'] = [];

			$student = new student();
			$student->load($uid);
			$student->result[]= $student;
			$prestudent_id = $student->prestudent_id;
			
			$mobility = new mobilitaet();
			$mobility->loadPrestudent($prestudent_id);
			$output = $mobility->result;
			$eintrag = '';
			foreach ($output as $k)
			{
				if(($k->mobilitaetstyp_kurzbz == 'GS') && ($k->studiensemester_kurzbz == $sem_kurzbz))
					$eintrag = ' (d.d.)';
			}
			$grades[$uid]['mobility'] = $eintrag;

			if ($lvgesamtnote = new lvgesamtnote($lv_id, $uid, $sem_kurzbz))
			{
				$grades[$uid]['note_lv'] = $lvgesamtnote->note;
				$grades[$uid]['freigabedatum'] = $lvgesamtnote->freigabedatum;
				$grades[$uid]['benotungsdatum'] = $lvgesamtnote->benotungsdatum;
				$grades[$uid]['punkte_lv'] = $lvgesamtnote->punkte;
			}
		}

		// send $grades reference to moodle addon
		
		// TODO: event getExterneNoten
		Events::trigger(
			'moodleGrades',
			function & () use (&$grades)
			{
				return $grades;
			},
			[
				'lvid' => $lv_id,
				'stsem' => $sem_kurzbz
			]
		);
		$this->addMeta('$grades', $grades);
		
		// calculate notenvorschläge from teilnoten, TODO: seperate function + own endpoint
		foreach($studentenData as $student) {
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

				// calculate grades points from notenschlüssel
				if (CIS_GESAMTNOTE_PUNKTE)
				{
					if (defined('CIS_GESAMTNOTE_GEWICHTUNG') && CIS_GESAMTNOTE_GEWICHTUNG)
					{
						// Lehreinheitsgewichtung
						$punkte_vorschlag = round($punktesumme_gewichtet / $gewichtsumme, 2);
						$notenschluessel = new notenschluessel();
						$note_vorschlag = $notenschluessel->getNote($punkte_vorschlag, $lv_id, $sem_kurzbz);
					}
					else
					{
						$punkte_vorschlag = round($punktesumme / $anzahlnoten, 2);
						$notenschluessel = new notenschluessel();
						$note_vorschlag = $notenschluessel->getNote($punkte_vorschlag, $lv_id, $sem_kurzbz);
					}
				}
				else
				{
					if (defined('CIS_GESAMTNOTE_GEWICHTUNG') && CIS_GESAMTNOTE_GEWICHTUNG)
					{
						$note_vorschlag = round($notensumme_gewichtet / $gewichtsumme);
					}
					else
					{
						$note_vorschlag = round($notensumme / $anzahlnoten);
					}
				}

				$student->note_vorschlag = $note_vorschlag;
			}
		}
		
		// get all prüfungen with noten held in that semester in that lva
		$pruefungen = $this->PruefungModel->getPruefungenByLvStudiensemester($lv_id, $sem_kurzbz);
		$pruefungenData = getData($pruefungen);
//		$pruefungenData = $this->getDataOrTerminateWithError($pruefungen);
		$this->addMeta('$pruefungenData', $pruefungenData);
		$this->terminateWithSuccess(array($studentenData, $pruefungenData, DOMAIN, $grades));
	}

	public function getNoten() {
		$this->load->model('education/Note_model', 'NoteModel');

		$result = $this->NoteModel->getAllActive();
		$noten = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($noten);
	}
	
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
		
		// TODO: also do something similar when updating/creating a pruefung!
		
		foreach($result->noten as $note) {
			$lvgesamtnote = new lvgesamtnote();
			if ($lvgesamtnote->load($lv_id, $note->uid, $sem_kurzbz))
			{
				if ($lvgesamtnote->benotungsdatum > $lvgesamtnote->freigabedatum)
				{
					$lvgesamtnote->freigabedatum = date("Y-m-d H:i:s");
					$lvgesamtnote->freigabevon_uid = getAuthUID();
					if($lvgesamtnote->save()) {
						$ret[] = array('uid' => $note->uid, 'freigabedatum' => $lvgesamtnote->freigabedatum, 'benotungsdatum' => $lvgesamtnote->benotungsdatum);
					}

					if (defined('CIS_GESAMTNOTE_FREIGABEMAIL_NOTE') && CIS_GESAMTNOTE_FREIGABEMAIL_NOTE)
					{
						// TODO: infomail an studiengangsassistenz
						// Enthalten sind MatrikelNr., Vorname, Nachname und Note der neuen oder geänderten Einträge.
					}

				}
			}

		}
		
		
		$this->terminateWithSuccess($ret);
	}

	public function getNotenvorschlagStudent() {
		// TODO: Notenvorschlag laden allgemeiner Endpunkt, der im Backend mit Logik (z.B. Moodle) angepasst werden kann.
		
		$this->terminateWithSuccess();
	}
	
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

		$punkte = str_replace(',', '.', $punkte);

		if($punkte!='')
		{
			// Bei Punkteeingabe wird die Note nochmals geprueft und ggf korrigiert
			$notenschluessel = new notenschluessel();
			$note_pruef = $notenschluessel->getNote($punkte, $lva_id, $stsem);
			if($note_pruef!=$note)
			{
				$note = $note_pruef;
				$note_dirty=true;
			}
		}

		if($note=='')
			$note = 9;

		$old_note = $note;
		
		// TODO: notwendiger check?
//		//Laden der Lehrveranstaltung
//		$lv_obj = new lehrveranstaltung();
//		if(!$lv_obj->load($lva_id))
//			die($lv_obj->errormsg);
//
//		//Studiengang laden
//		$stg_obj = new studiengang($lv_obj->studiengang_kz);
//		
		
		$pruefungenChanged = $this->savePruefungstermin($typ, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum);
		
		//Gesamtnote updaten
		$lvgesamtnote = new lvgesamtnote();
		if (!$lvgesamtnote->load($lva_id, $student_uid, $stsem))
		{
			$lvgesamtnote->student_uid = $student_uid;
			$lvgesamtnote->lehrveranstaltung_id = $lva_id;
			$lvgesamtnote->studiensemester_kurzbz = $stsem;
			$lvgesamtnote->note = $note;
			$lvgesamtnote->punkte = $punkte;
			$lvgesamtnote->mitarbeiter_uid = getAuthUID();
			$lvgesamtnote->benotungsdatum = $jetzt;
			$lvgesamtnote->freigabedatum = null;
			$lvgesamtnote->freigabevon_uid = null;
			$lvgesamtnote->bemerkung = null;
			$lvgesamtnote->updateamum = null;
			$lvgesamtnote->updatevon = null;
			$lvgesamtnote->insertamum = $jetzt;
			$lvgesamtnote->insertvon = getAuthUID();
			$new = true;
//			$response = "neu";
		}
		else
		{
			$lvgesamtnote->note = $note;
			$lvgesamtnote->punkte = $punkte;
			$lvgesamtnote->benotungsdatum = $jetzt;
			$lvgesamtnote->updateamum = $jetzt;
			$lvgesamtnote->updatevon = getAuthUID();
			$new = false;
//			if ($lvgesamtnote->freigabedatum)
//				$response = "update_f";
//			else
//				$response = "update";
		}

		$lvgesamtnote->save($new);

		$savedPruefung = $pruefungenChanged['savedPruefung'] ?? null;
		$extraPruefung = $pruefungenChanged['extraPruefung'] ?? null; // TODO: test

		$this->terminateWithSuccess(array($savedPruefung, $lvgesamtnote, $extraPruefung));
	}
	
	private function savePruefungstermin($typ, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum) 
	{
		$jetzt = date("Y-m-d H:i:s");
		
		$pruefungenChanged = [];
		
		if($typ == "Termin2") {

			$pr = new Pruefung();

			// Wenn eine Pruefung angelegt wird, wird zuerst eine Pruefung mit 1. Termin angelegt
			// und dort die Zeugnisnote gespeichert
			if($pr->getPruefungen($student_uid, "Termin1", $lva_id, $stsem))
			{
				if ($pr->result)
				{
					// TODO: is this filler if branch really necessary?
					$termin1 = 1;
				}
				else
				{
					$lvnote = new lvgesamtnote();
					// update Termin1 note
					if ($lvnote->load($lva_id, $student_uid, $stsem))
					{
						$pr_note = $lvnote->note;
						$pr_punkte = $lvnote->punkte;
						$benotungsdatum = $lvnote->benotungsdatum;
					}
					else // set Termin1 note to "noch nicht eingetragen"
					{
						$pr_note = 9;
						$pr_punkte = '';
						$benotungsdatum = $jetzt;
					}

					$pr_1 = new Pruefung();
					$pr_1->lehreinheit_id = $lehreinheit_id;
					$pr_1->student_uid = $student_uid;
					$pr_1->mitarbeiter_uid = getAuthUID();
					$pr_1->note = $pr_note;
					$pr_1->punkte = $pr_punkte;
					$pr_1->pruefungstyp_kurzbz = "Termin1";
					$pr_1->datum = $benotungsdatum;
					$pr_1->anmerkung = "";
					$pr_1->insertamum = $jetzt;
					$pr_1->insertvon = getAuthUID();
					$pr_1->updateamum = null;
					$pr_1->updatevon = null;
					$pr_1->ext_id = null;
					$pr_1->new = true;
					$pr_1->save();
					$pruefungenChanged['extraPruefung'] = $pr_1; //"neu T1";
				}

				$prTermin2 = new Pruefung();
				$pr_2 = new Pruefung();

				// Die Pruefung wird als Termin2 eingetragen
				if ($prTermin2->getPruefungen($student_uid, 'Termin2', $lva_id, $stsem))
				{
					if	($prTermin2->result)
					{
						$pr_2->load($prTermin2->result[0]->pruefung_id);
						$pr_2->new = null;
						$pr_2->updateamum = $jetzt;
						$pr_2->updatevon = getAuthUID();
						$old_note = $pr_2->note;
						$pr_2->note = $note;
						$pr_2->punkte = $punkte;
						$pr_2->datum = $datum;
						$pr_2->anmerkung = "";
						$pruefungenChanged['savedPruefung'] = $pr_2;
//						$savedPruefung = $pr_2;//"update T2";
					}
					else
					{
						$pr_2->lehreinheit_id = $lehreinheit_id;
						$pr_2->student_uid = $student_uid;
						$pr_2->mitarbeiter_uid = getAuthUID();
						$pr_2->note = $note;
						$pr_2->punkte = $punkte;
						$pr_2->pruefungstyp_kurzbz = $typ;
						$pr_2->datum = $datum;
						$pr_2->anmerkung = "";
						$pr_2->insertamum = $jetzt;
						$pr_2->insertvon = getAuthUID();
						$pr_2->updateamum = null;
						$pr_2->updatevon = null;
						$pr_2->ext_id = null;
						$pr_2->new = true;
						$old_note = -1;
						$pruefungenChanged['savedPruefung'] = $pr_2;
//						$savedPruefung = $pr_2;//"new T2";
					}
					$pr_2->save();
				}
			}

		} else if($typ == "Termin3") {

			$prTermin3 = new Pruefung();
			$pr_3 = new Pruefung();

			if ($prTermin3->getPruefungen($student_uid, 'Termin3', $lva_id, $stsem))
			{
				if	($prTermin3->result)
				{
					$pr_3->load($prTermin3->result[0]->pruefung_id);
					$pr_3->new = null;
					$pr_3->updateamum = $jetzt;
					$pr_3->updatevon = getAuthUID();
					$old_note = $pr_3->note;
					$pr_3->note = $note;
					$pr_3->punkte = $punkte;
					$pr_3->datum = $datum;
					$pr_3->anmerkung = "";
					$pruefungenChanged['savedPruefung'] = $pr_3;
//					$savedPruefung = $pr_3; //"update T3";
				}
				else
				{
					$pr_3->lehreinheit_id = $lehreinheit_id;
					$pr_3->student_uid = $student_uid;
					$pr_3->mitarbeiter_uid = getAuthUID();
					$pr_3->note = $note;
					$pr_3->punkte = $punkte;
					$pr_3->pruefungstyp_kurzbz = $typ;
					$pr_3->datum = $datum;
					$pr_3->anmerkung = "";
					$pr_3->insertamum = $jetzt;
					$pr_3->insertvon = getAuthUID();
					$pr_3->updateamum = null;
					$pr_3->updatevon = null;
					$pr_3->ext_id = null;
					$pr_3->new = true;
					$old_note = -1;
					$pruefungenChanged['savedPruefung'] = $pr_3;
//					$savedPruefung = $pr_3; //"new T3";
				}
				$pr_3->save();
			}
		} else {
			// TODO: proper error phrase that explains better why we terminated with error
			$this->terminateWithError("Typ is not termin2 or termin3.", 'general');
		}
		
		return $pruefungenChanged;
	}

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
		
		$lvgesamtnote = new lvgesamtnote();
		if (!$lvgesamtnote->load($lv_id, $student_uid, $sem_kurzbz))
		{
			$lvgesamtnote->student_uid = $student_uid;
			$lvgesamtnote->lehrveranstaltung_id = $lv_id;
			$lvgesamtnote->studiensemester_kurzbz = $sem_kurzbz;
			$lvgesamtnote->note = trim($note);
			$lvgesamtnote->mitarbeiter_uid = getAuthUID();
			$lvgesamtnote->benotungsdatum = date("Y-m-d H:i:s");
			$lvgesamtnote->freigabedatum = null;
			$lvgesamtnote->freigabevon_uid = null;
			$lvgesamtnote->bemerkung = null;
			$lvgesamtnote->updateamum = null;
			$lvgesamtnote->updatevon = null;
			$lvgesamtnote->insertamum = date("Y-m-d H:i:s");
			$lvgesamtnote->insertvon = getAuthUID();
			$lvgesamtnote->punkte =// TODO: deprecated?
			$new = true;
			$response = "neu";
		}
		else
		{
			$lvgesamtnote->note = trim($note);
			$lvgesamtnote->punkte = null; // TODO: deprecated?
			$lvgesamtnote->benotungsdatum = date("Y-m-d H:i:s");
			$lvgesamtnote->updateamum = date("Y-m-d H:i:s");
			$lvgesamtnote->updatevon = getAuthUID();
			$new = false;
			if ($lvgesamtnote->freigabedatum)
				$response = "update_f";
			else
				$response = "update";
		}
		if (!$lvgesamtnote->save($new))
			$ret = $lvgesamtnote->errormsg;
		else
			$ret = $response;

		$lvgesamtnote->load($lv_id, $student_uid, $sem_kurzbz);
		
		$this->terminateWithSuccess(array($ret, $lvgesamtnote));
	}

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
		$responseMsgs = [];
		
		foreach($noten as $note)
		{
			$lvgesamtnote = new lvgesamtnote();
			if (!$lvgesamtnote->load($lv_id, $note->uid, $sem_kurzbz))
			{
				$lvgesamtnote->student_uid = $note->uid;
				$lvgesamtnote->lehrveranstaltung_id = $lv_id;
				$lvgesamtnote->studiensemester_kurzbz = $sem_kurzbz;
				$lvgesamtnote->note = trim($note->note);
				$lvgesamtnote->mitarbeiter_uid = getAuthUID();
				$lvgesamtnote->benotungsdatum = date("Y-m-d H:i:s");
				$lvgesamtnote->freigabedatum = null;
				$lvgesamtnote->freigabevon_uid = null;
				$lvgesamtnote->bemerkung = null;
				$lvgesamtnote->updateamum = null;
				$lvgesamtnote->updatevon = null;
				$lvgesamtnote->insertamum = date("Y-m-d H:i:s");
				$lvgesamtnote->insertvon = getAuthUID();
				$lvgesamtnote->punkte =// TODO: deprecated?
				$new = true;
				$response = "neu";
			}
			else
			{
				$lvgesamtnote->note = trim($note->note);
				$lvgesamtnote->punkte = null; // TODO: deprecated?
				$lvgesamtnote->benotungsdatum = date("Y-m-d H:i:s");
				$lvgesamtnote->updateamum = date("Y-m-d H:i:s");
				$lvgesamtnote->updatevon = getAuthUID();
				$new = false;
				if ($lvgesamtnote->freigabedatum)
					$response = "update_f";
				else
					$response = "update";
			}

			if (!$lvgesamtnote->save($new))
				$responseMsgs[] = $lvgesamtnote->errormsg;
			else
				$responseMsgs[] = $response;
			
			
			$lvgesamtnote->load($lv_id, $note->uid, $sem_kurzbz);

			$retLvNoten[] = $lvgesamtnote;
		}

		$this->terminateWithSuccess(array($retLvNoten, $responseMsgs));

	}
	
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
			$punkte = ''; // TODO: check punkte feature

			$lehreinheit_id = $student->lehreinheit_id;
			$ret[$student->uid] = $this->savePruefungstermin($typ, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum);
		}

		$this->terminateWithSuccess($ret);
	}

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
			$punkte = ''; // TODO: check punkte feature

			$lehreinheit_id = $pruefung->lehreinheit_id;
			$ret[$student_uid] = $this->savePruefungstermin($typ, $student_uid, $lv_id, $sem_kurzbz, $lehreinheit_id, $note, $punkte, $datum);
		}
		
		
		$this->terminateWithSuccess($ret);
	}

}

