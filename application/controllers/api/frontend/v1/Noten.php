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
		
		$this->load->model('education/LePruefung_model', 'LePruefungModel');
		$this->load->model('education/Lvgesamtnote_model', 'LvgesamtnoteModel');
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->load->model('crm/Student_model', 'StudentModel');
	}
	
	public function getStudentenNoten() {
		$lv_id = $this->input->get("lv_id",TRUE);
		$sem_kurzbz = $this->input->get("sem_kurzbz",TRUE);

		if (!isset($lv_id) || isEmptyString($lv_id)
			|| !isset($sem_kurzbz) || isEmptyString($sem_kurzbz))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');

		// todo: check various other berechtigungen if its mitarbeiter/lektor/zugeteilterLektor?

		

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

		$res = $this->StudentModel->load(['be21b081']);
		$this->addMeta('be21b081', $res);
		
		foreach($student_uids as $uid) {
			$grades[$uid]['grades'] = [];

//			$student = new student();
//			$student->load($uid);
//			$student->result[]= $student;
			
			$res = $this->StudentModel->load([$uid]);
			$this->addMeta($uid, $res);
			if(!isError($res) && hasData($res)) $student = getData($res)[0];

			$prestudent_id = $student->prestudent_id;
			
			
			// TODO: last class to get rid of but this one is complicated
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
			
			$result = $this->LvgesamtnoteModel->getLvGesamtNoten($lv_id, $uid, $sem_kurzbz);
			$this->addMeta('getLvGesamtNoten', $result);
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

				
				// TODO: develop the punkte feature with models
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
		$pruefungen = $this->LePruefungModel->getPruefungenByLvStudiensemester($lv_id, $sem_kurzbz);
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

			$result = $this->LvgesamtnoteModel->getLvGesamtNoten($lv_id, $note->uid, $sem_kurzbz);
			
			if (!isError($result) && hasData($result))
			{
				$lvgesamtnote = getData($result)[0];
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
		$punkte = null;
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
		
		$punkte = null;

//		if($punkte!='')
//		{
//			// Bei Punkteeingabe wird die Note nochmals geprueft und ggf korrigiert
//			$notenschluessel = new notenschluessel();
//			$note_pruef = $notenschluessel->getNote($punkte, $lva_id, $stsem);
//			if($note_pruef!=$note)
//			{
//				$note = $note_pruef;
//				$note_dirty=true;
//			}
//		}

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
		
		$pruefungenChanged = $this->savePruefungstermin($typ, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum);
		
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

		$this->addMeta('$pruefungenChanged', $pruefungenChanged);
		$this->addMeta('$lvgesamtnote', $lvgesamtnote);
		$savedPruefung = $pruefungenChanged['savedPruefung'] ?? null;
		$extraPruefung = $pruefungenChanged['extraPruefung'] ?? null; // TODO: test

		$savedPruefungData = count($savedPruefung) > 0 ? $savedPruefung[0] : null;
		$extraPruefungData = count($extraPruefung) > 0 ? $extraPruefung[0] : null;
		
		$this->terminateWithSuccess(array($savedPruefungData, $lvgesamtnote, $extraPruefungData));
	}
	
	private function savePruefungstermin($typ, $student_uid, $lva_id, $stsem, $lehreinheit_id, $note, $punkte, $datum) 
	{
		$jetzt = date("Y-m-d H:i:s");
		
		$pruefungenChanged = [];
		
		$this->load->model('education/Lvgesamtnote_model', 'LvgesamtnoteModel');
		

		if($typ == "Termin2") {
			
			// Wenn eine Nachprüfung angelegt wird, wird zuerst eine Pruefung mit 1. Termin angelegt welche für die ursprüngliche Note
			// vor den Prüfungsantritten zählt
			
			$result1 = $this->LePruefungModel->getPruefungenByUidTypLvStudiensemester($student_uid, "Termin1", $lva_id, $stsem);
			
			// if there is a termin 1 entry already do nothing
			if(!isError($result1) && hasData($result1)) {

			} else if(!isError($result1) && !hasData($result1)) {
				// new entry termin1

				$resultLV = $this->LvgesamtnoteModel->getLvGesamtNoten($lva_id, $student_uid, $stsem);
				
				$this->addMeta('$resultLV', $resultLV);
				
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
					$pr_note = 9;
					$pr_punkte = null;
					$benotungsdatum = $jetzt;
				}

				$this->addMeta('$pr_punkte', $pr_punkte);
				
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
					$this->addMeta('idTermin1', $id);
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
						'punkte' => null,//$punkte,
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

		} else if($typ == "Termin3") {

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
						'punkte' => null,//$punkte,
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

		$result = $this->LvgesamtnoteModel->getLvGesamtNoten($lv_id, $student_uid, $sem_kurzbz);
		$this->addMeta('getLvGesamtNoten', $result);
		if(!isError($result) && hasData($result)) {
			$lvgesamtnote = getData($result)[0];
			
			$id = $this->LvgesamtnoteModel->update(
				[$lvgesamtnote->student_uid, $lvgesamtnote->studiensemester_kurzbz, $lvgesamtnote->lehrveranstaltung_id],
				array(
					'note' => $note,
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
					'student_uid' => $student_uid,
					'lehrveranstaltung_id' => $lv_id,
					'studiensemester_kurzbz' => $sem_kurzbz,
					'note' => $note,
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
		
		$this->terminateWithSuccess(array($lvgesamtnote));
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

			$result = $this->LvgesamtnoteModel->getLvGesamtNoten($lv_id, $note->uid, $sem_kurzbz);
			$this->addMeta('getLvGesamtNoten', $result);
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

