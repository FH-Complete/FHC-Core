<?php

/**
 * FH-Complete
 *
 * @package             FHC-Helper
 * @author              FHC-Team
 * @copyright           Copyright (c) 2023 fhcomplete.net
 * @license             GPLv3
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

class PrestudentLib
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$this->_ci =& get_instance();

		// // Configs
		// $this->_ci->load->config('studierendenantrag');

		// // Models
		$this->_ci->load->model('crm/Prestudent_model', 'PrestudentModel');
		$this->_ci->load->model('crm/Student_model', 'StudentModel');
		$this->_ci->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
		$this->_ci->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');
		$this->_ci->load->model('organisation/Lehrverband_model', 'LehrverbandModel');
		$this->_ci->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
		$this->_ci->load->model('person/Benutzer_model', 'BenutzerModel');
		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
	}

	public function setAbbrecher(
		$prestudent_id,
		$studiensemester_kurzbz,
		$insertvon = null,
		$statusgrund_id = null,
		$datum = null,
		$bestaetigtam = null,
		$bestaetigtvon = null
	) {
		if (!$insertvon)
			$insertvon = getAuthUID();
		if (!$bestaetigtvon)
			$bestaetigtvon = $insertvon;

		$result = $this->_ci->PrestudentstatusModel->getLastStatus($prestudent_id, $studiensemester_kurzbz);
		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result)
			return error($this->_ci->p->t('studierendenantrag', 'error_no_prestudent_in_sem', [
				'prestudent_id' => $prestudent_id,
				'studiensemester_kurzbz' => $studiensemester_kurzbz
			]));

		$prestudent_status = current($result);

		$result = $this->_ci->StudentModel->loadWhere(['prestudent_id' => $prestudent_id]);

		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result)
			return error($this->_ci->p->t('studierendenantrag', 'error_no_student_for_prestudent', ['prestudent_id' => $prestudent_id]));

		$student = current($result);

		if(!$datum)
			$datum = date('c');

		if(!$bestaetigtam)
			$bestaetigtam = date('c');

		// Status und Statusgrund updaten
		$result = $this->_ci->PrestudentstatusModel->insert([
			'prestudent_id' => $prestudent_id,
			'status_kurzbz' => Prestudentstatus_model::STATUS_ABBRECHER,
			'studiensemester_kurzbz' => $prestudent_status->studiensemester_kurzbz,
			'ausbildungssemester' => $prestudent_status->ausbildungssemester,
			'datum' => $datum,
			'insertvon' => $insertvon,
			'insertamum' => date('c'),
			'orgform_kurzbz'=> $prestudent_status->orgform_kurzbz,
			'studienplan_id'=> $prestudent_status->studienplan_id,
			'bestaetigtvon' => $bestaetigtvon,
			'bestaetigtam' => $bestaetigtam,
			'statusgrund_id' => $statusgrund_id
		]);

		if (isError($result))
			return $result;

		//Verband anlegen
		$result = $this->_ci->LehrverbandModel->load([
			'studiengang_kz' => $student->studiengang_kz,
			'semester' => 0,
			'verband' => 'A',
			'gruppe' => ''
		]);

		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result)
		{
			$result = $this->_ci->LehrverbandModel->load([
				'studiengang_kz' => $student->studiengang_kz,
				'semester' => 0,
				'verband' => '',
				'gruppe' => ''
			]);
			if (isError($result))
				return $result;
			$result = getData($result);

			if(!$result)
			{
				$this->_ci->LehrverbandModel->insert([
					'studiengang_kz' => $student->studiengang_kz,
					'semester' => 0,
					'verband' => '',
					'gruppe' => '',
					'bezeichnung' => 'Ab-Unterbrecher',
					'aktiv' => true,
				]);
			}

			$this->_ci->LehrverbandModel->insert([
				'studiengang_kz' => $student->studiengang_kz,
				'semester' => 0,
				'verband' => 'A',
				'gruppe' => '',
				'bezeichnung' => 'Abbrecher',
				'aktiv' => true
			]);
		}

		// noch nicht eingetragene Zeugnisnoten auf 9 setzen
		$result = $this->_ci->ZeugnisnoteModel->getZeugnisnoten($student->student_uid, $prestudent_status->studiensemester_kurzbz);
		if (isError($result))
			return $result;
		$result = getData($result) ?: [];

		foreach ($result as $lv)
		{
			if (!$lv->note)
			{
				$result = $this->_ci->ZeugnisnoteModel->insert([
					'note' => 9,
					'studiensemester_kurzbz' => $lv->studiensemester_kurzbz,
					'student_uid' => $lv->uid,
					'lehrveranstaltung_id' => $lv->lehrveranstaltung_id
				]);
				if (isError($result)) {
					$result = $this->_ci->ZeugnisnoteModel->update([
						'studiensemester_kurzbz' => $lv->studiensemester_kurzbz,
						'student_uid' => $lv->uid,
						'lehrveranstaltung_id' => $lv->lehrveranstaltung_id
					], [
						'note' => 9
					]);

					if (isError($result))
						return $result;
				}
			}
		}


		// Update Aktionen

		// StudentModel updaten
		$this->_ci->StudentModel->update([
			'student_uid' => $student->student_uid
		], [
			'verband' => 'A',
			'gruppe' => '',
			'semester' => 0,
			'updatevon' => $insertvon,
			'updateamum' => date('c')
		]);

		//Studentlehrverband setzen
		$this->_ci->StudentlehrverbandModel->update([
			'studiensemester_kurzbz' => $prestudent_status->studiensemester_kurzbz,
			'student_uid' => $student->student_uid
		], [
			'studiengang_kz' => $student->studiengang_kz,
			'semester' => 0,
			'verband' => 'A',
			'gruppe' => '',
			'updateamum' => date('c'),
			'updatevon' => $insertvon
		]);

		// Benutzer inaktiv setzen
		$this->_ci->BenutzerModel->update([
			'uid' =>  $student->student_uid
		], [
			'aktiv' => false,
			'updateaktivvon' => $insertvon,
			'updateaktivam' => date('c'),
			'updatevon' => $insertvon,
			'updateamum' => date('c')
		]);

		return success();
	}

	public function setUnterbrecher(
		$prestudent_id,
		$studiensemester_kurzbz,
		$studierendenantrag_id = null,
		$insertvon = null,
		$ausbildungssemester = null,
		$statusgrund_id = null
	) {
		$ausbildungssemester_plus = 0;
		
		if (!$insertvon)
			$insertvon = getAuthUID();


		$result = $this->_ci->PrestudentstatusModel->getLastStatus($prestudent_id, $studiensemester_kurzbz);
		
		if (isError($result))
			return $result;
		
		$result = getData($result);
		
		if (!$result) { // NOTE(chris): no status in target stdsem
			//NOTE(manu): only valid if nextSemester focus max

			$result = $this->_ci->PrestudentstatusModel->getLastStatus($prestudent_id);
			if (isError($result))
				return $result;
			$result = getData($result);

			// check if ausbildungssemester is last
			$this->_ci->StudiengangModel->addJoin('public.tbl_prestudent p', 'studiengang_kz');
			$res = $this->_ci->StudiengangModel->loadWhere(['p.prestudent_id' => $prestudent_id]);
			if(isError($res))
				return $res;
			if(!hasData($res))
				return error($this->_ci->p->t('studierendenantrag', 'error_no_stg_for_prestudent', [
					'prestudent_id' => $prestudent_id
				]));

			$studiengang = current(getData($res));
			$prestudent_status = current($result);
			if($prestudent_status->ausbildungssemester + 1 < $studiengang->max_semester)
				$ausbildungssemester_plus = 1;

			if(!$result)
			{
				return error($this->_ci->p->t('studierendenantrag', 'error_no_prestudent_in_sem', [
					'prestudent_id' => $prestudent_id,
					'studiensemester_kurzbz' => $studiensemester_kurzbz
				]));
			}
		}

		$prestudent_status = current($result);


		$result = $this->_ci->StudentModel->loadWhere(['prestudent_id' => $prestudent_id]);

		if (isError($result))
			return $result;

		$result = getData($result);
		
		if (!$result)
			return error($this->_ci->p->t('studierendenantrag', 'error_no_student_for_prestudent', ['prestudent_id' => $prestudent_id]));

		$student = current($result);


		if ($studierendenantrag_id)
		{
			$resultAntrag = $this->_ci->StudierendenantragModel->load($studierendenantrag_id);
			if (isError($resultAntrag))
				return $resultAntrag;
			$resultAntrag = getData($resultAntrag);
			if (!$resultAntrag)
				return error($this->_ci->p->t('studierendenantrag', 'error_no_antrag_found', ['id' => $studierendenantrag_id]));

			$antrag = current($resultAntrag);
			$anmerkung = 'Wiedereinstieg ' . $antrag->datum_wiedereinstieg;
		}
		else
			$anmerkung = '';

		if ($ausbildungssemester)
			$semester = $ausbildungssemester;
		else
			$semester = $prestudent_status->ausbildungssemester + $ausbildungssemester_plus;

		// Status updaten
		$result = $this->_ci->PrestudentstatusModel->insert([
			'prestudent_id' => $prestudent_id,
			'status_kurzbz' => Prestudentstatus_model::STATUS_UNTERBRECHER,
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'ausbildungssemester' => $semester,
			'datum' => date('c'),
			'insertvon' => $insertvon,
			'insertamum' => date('c'),
			'orgform_kurzbz'=> $prestudent_status->orgform_kurzbz,
			'studienplan_id'=> $prestudent_status->studienplan_id,
			'bestaetigtvon' => $insertvon,
			'bestaetigtam' => date('c'),
			'anmerkung'=> $anmerkung,
			'statusgrund_id' => $statusgrund_id
		]);

		if (isError($result))
			return $result;

		//Verband anlegen
		$result = $this->_ci->LehrverbandModel->load([
			'studiengang_kz' => $student->studiengang_kz,
			'semester' => 0,
			'verband' => 'B',
			'gruppe' => ''
		]);

		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result)
		{
			$result = $this->_ci->LehrverbandModel->load([
				'studiengang_kz' => $student->studiengang_kz,
				'semester' => 0,
				'verband' => '',
				'gruppe' => ''
			]);
			if (isError($result))
				return $result;
			$result = getData($result);

			if(!$result)
			{
				$this->_ci->LehrverbandModel->insert([
					'studiengang_kz' => $student->studiengang_kz,
					'semester' => 0,
					'verband' => '',
					'gruppe' => '',
					'bezeichnung' => 'Ab-Unterbrecher',
					'aktiv' => true,
				]);
			}

			$this->_ci->LehrverbandModel->insert([
				'studiengang_kz' => $student->studiengang_kz,
				'semester' => 0,
				'verband' => 'B',
				'gruppe' => '',
				'bezeichnung' => 'Unterbrecher',
				'aktiv' => true
			]);
		}

		// noch nicht eingetragene Zeugnisnoten auf 9 setzen
		$result = $this->_ci->ZeugnisnoteModel->getZeugnisnoten($student->student_uid, $studiensemester_kurzbz);
		if (isError($result))
			return $result;
		$result = getData($result) ?: [];

		foreach ($result as $lv)
		{
			if (!$lv->note)
			{
				$result = $this->_ci->ZeugnisnoteModel->insert([
					'note' => 9,
					'studiensemester_kurzbz' => $lv->studiensemester_kurzbz,
					'student_uid' => $lv->uid,
					'lehrveranstaltung_id' => $lv->lehrveranstaltung_id
				]);
				if (isError($result)) {
					$result = $this->_ci->ZeugnisnoteModel->update([
						'studiensemester_kurzbz' => $lv->studiensemester_kurzbz,
						'student_uid' => $lv->uid,
						'lehrveranstaltung_id' => $lv->lehrveranstaltung_id
					], [
						'note' => 9
					]);

					if (isError($result))
						return $result;
				}
			}
		}

		// Update Aktionen

		// StudentModel updaten
		$this->_ci->StudentModel->update([
			'student_uid' => $student->student_uid
		], [
			'verband' => 'B',
			'gruppe' => '',
			'semester' => 0,
			'updatevon' => $insertvon,
			'updateamum' => date('c')
		]);

		//Studentlehrverband setzen
		$result = $this->_ci->StudentlehrverbandModel->loadWhere([
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'student_uid' => $student->student_uid
		]);
		if (hasData($result)) {
			$this->_ci->StudentlehrverbandModel->update([
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'student_uid' => $student->student_uid
			], [
				'studiengang_kz' => $student->studiengang_kz,
				'semester' => 0,
				'verband' => 'B',
				'gruppe' => '',
				'updateamum' => date('c'),
				'updatevon' => $insertvon
			]);
		} else {
			$this->_ci->StudentlehrverbandModel->insert([
				'student_uid' => $student->student_uid,
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'studiengang_kz' => $student->studiengang_kz,
				'semester' => 0,
				'verband' => 'B',
				'gruppe' => '',
				'insertamum' => date('c'),
				'insertvon' => $insertvon
			]);
		}

		return success();
	}

	public function setStudent($prestudent_id, $studiensemester_kurzbz, $ausbildungssemester, $statusgrund_id)
	{
		$authUID = getAuthUID();
		$now = date('c');


		$result = $this->_ci->PrestudentstatusModel->getLastStatus($prestudent_id);

		if (isError($result))
			return $result;
		if (!hasData($result))
			return error($this->_ci->p->t('studierendenantrag', 'error_no_prestudentstatus', [
				'prestudent_id' => $prestudent_id
			]));

		$prestudent_status = current(getData($result));


		$result = $this->_ci->StudentModel->loadWhere(['prestudent_id' => $prestudent_id]);

		if (isError($result))
			return $result;
		if (!hasData($result))
			return error($this->_ci->p->t('studierendenantrag', 'error_no_student_for_prestudent', ['prestudent_id' => $prestudent_id]));

		$student = current(getData($result));


		$this->_ci->load->library('VariableLib', ['uid' => $authUID]);
		$semester_aktuell = $this->_ci->variablelib->getVar('semester_aktuell');


		// Update Aktionen

		// Status updaten
		$result = $this->_ci->PrestudentstatusModel->insert([
			'prestudent_id' => $prestudent_id,
			'status_kurzbz' => Prestudentstatus_model::STATUS_STUDENT,
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'statusgrund_id' => $statusgrund_id,
			'ausbildungssemester' => $ausbildungssemester,
			'datum' => $now,
			'insertvon' => $authUID,
			'insertamum' => $now,
			'orgform_kurzbz'=> $prestudent_status->orgform_kurzbz,
			'studienplan_id'=> $prestudent_status->studienplan_id,
			'bestaetigtvon' => $authUID,
			'bestaetigtam' => $now
		]);

		if (isError($result))
			return $result;


		// Student updaten
		$result = $this->_ci->StudentModel->update([
			'student_uid' => $student->student_uid
		], [
			'semester' => $ausbildungssemester,
			'verband' => '',
			'gruppe' => '',
			'updatevon' => $authUID,
			'updateamum' => $now
		]);

		if (isError($result))
			return $result;


		// Studentlehrverband updaten
		$result = $this->_ci->StudentlehrverbandModel->update([
			'student_uid' => $student->student_uid,
			'studiensemester_kurzbz' => $semester_aktuell
		], [
			'semester' => $ausbildungssemester,
			'verband' => '',
			'gruppe' => '',
			'updatevon' => $authUID,
			'updateamum' => $now
		]);

		if (isError($result))
			return $result;


		// Benutzer updaten
		$result = $this->_ci->BenutzerModel->load([$student->student_uid]);

		if (isError($result))
			return $result;
		if (!hasData($result))
			return error($this->_ci->p->t('person', 'error_noBenutzer'));

		$benutzer = current(getData($result));
		$updateData = [
			'aktiv' => true,
			'updateamum' => $now,
			'updatevon' => $authUID
		];
		if (!$benutzer->aktiv) {
			$updateData['updateaktivam'] = $now;
			$updateData['updateaktivvon'] = $authUID;
		}


		$this->_ci->BenutzerModel->update([$student->student_uid], $updateData);

		return success();
	}

	public function setFirstStudent(
		$prestudent_id,
		$studiensemester_kurzbz,
		$ausbildungssemester,
		$orgform_kurzbz,
		$studienplan_id,
		$statusgrund_id
	) {
		$this->_ci->PrestudentModel->addJoin('public.tbl_person p', 'person_id');
		$this->_ci->PrestudentModel->addJoin('public.tbl_studiengang stg', 'studiengang_kz');
		$result = $this->_ci->PrestudentModel->load($prestudent_id);

		if (isError($result))
			return $result;

		if (!hasData($result))
			return error('No prestudent');

		$student_data = current(getData($result));


		$authUID = getAuthUID();
		$now = date('c');
		$today = date('Y-m-d');

		$jahr = mb_substr($studiensemester_kurzbz, 4, 2);


		// Genererate Personenkennzeichen
		$personenkennzeichen = $this->_ci->StudentModel->generateMatrikelnummer2(
			$student_data->studiengang_kz,
			$studiensemester_kurzbz,
			$student_data->typ
		);
		if (isError($personenkennzeichen))
			return $personenkennzeichen;
		$personenkennzeichen = getData($personenkennzeichen);
		
		
		// Generate UID
		$uid = $this->_ci->StudentModel->generateUID(
			$student_data->kurzbz,
			$jahr,
			$student_data->typ,
			$personenkennzeichen,
			$student_data->vorname,
			$student_data->nachname
		);
		if (isError($uid))
			return $uid;
		$uid = getData($uid);


		// Generate Matrikelnummer
		$matrikelnummer = $this->_ci->BenutzerModel->generateMatrikelnummer(
			$student_data->oe_kurzbz
		);
		if (isError($matrikelnummer))
			return $matrikelnummer;
		$matrikelnummer = getData($matrikelnummer);


		// Generate Alias
		$alias = '';
		if (!defined('GENERATE_ALIAS_STUDENT')
			|| GENERATE_ALIAS_STUDENT === true
		) {
			$result = $this->_ci->BenutzerModel->generateAliasFromName($student_data->vorname, $student_data->nachname);
			if (isError($result))
				return $result;
			$alias = getData($result);
		}

		// Generate Activation Key
		$activationkey = $this->_ci->BenutzerModel->generateActivationkey();


		// Overwrite stuff
		if (defined('SET_UID_AS_MATRIKELNUMMER')
			&& SET_UID_AS_MATRIKELNUMMER)
			$matrikelnummer = $uid;
		if (defined('SET_UID_AS_PERSONENKENNZEICHEN')
			&& SET_UID_AS_PERSONENKENNZEICHEN)
			$personenkennzeichen = $uid;


		// Update Person
		$this->_ci->load->model('person/Person_model', 'PersonModel');
		$result = $this->_ci->PersonModel->update([
			'person_id' => $student_data->person_id,
			'matr_nr' => null
		], [
			'matr_nr' => $matrikelnummer
		]);

		if (isError($result))
			return $result;


		// Add Benutzer
		$result = $this->_ci->BenutzerModel->insert([
			'uid' => $uid,
			'person_id' => $student_data->person_id,
			'aktiv' => true,
			'aktivierungscode' => $activationkey,
			'alias' => $alias,
			'insertvon' => $authUID,
			'insertamum' => $now,
		]);

		if (isError($result))
			return $result;
		

		// Add Student
		$result = $this->_ci->StudentModel->insert([
			'student_uid' => $uid,
			'matrikelnr' => $personenkennzeichen,
			'prestudent_id' => $prestudent_id,
			'studiengang_kz' => $student_data->studiengang_kz,
			'semester' => $ausbildungssemester,
			'verband' => ' ',
			'gruppe' => ' ',
			'insertvon' => $authUID,
			'insertamum' => $now
		]);

		if (isError($result))
			return $result;
		

		// Add Lehrverband if it does not exist
		$result = $this->_ci->LehrverbandModel->load([' ', ' ', $ausbildungssemester, $student_data->studiengang_kz]);
		
		if (isError($result))
			return $result;
		
		if (!hasData($result)) {
			$result = $this->_ci->LehrverbandModel->insert([
				'studiengang_kz' => $student_data->studiengang_kz,
				'semester' => $ausbildungssemester,
				'verband' => ' ',
				'gruppe' => ' ',
				'aktiv' => true
			]);

			if (isError($result))
				return $result;
		}


		// Add Rolle
		$result = $this->_ci->PrestudentstatusModel->insert([
			'prestudent_id' => $prestudent_id,
			'status_kurzbz' => Prestudentstatus_model::STATUS_STUDENT,
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'ausbildungssemester' => $ausbildungssemester,
			'orgform_kurzbz'=> $orgform_kurzbz,
			'studienplan_id'=> $studienplan_id,
			'datum' => $today,
			'insertamum' => $now,
			'insertvon' => $authUID,
			'bestaetigtam' => $today,
			'bestaetigtvon' => $authUID,
			'statusgrund_id' => $statusgrund_id
		]);

		if (isError($result))
			return $result;


		// Add Studentlehrverband
		$result = $this->_ci->StudentlehrverbandModel->insert([
			'student_uid' => $uid,
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'studiengang_kz' => $student_data->studiengang_kz,
			'semester' => $ausbildungssemester,
			'verband' => ' ',
			'gruppe' => ' ',
			'insertamum' => $now,
			'insertvon' => $authUID
		]);

		if (isError($result))
			return $result;

		return success();
	}

	public function setDiplomand($prestudent_id, $studiensemester_kurzbz, $ausbildungssemester, $statusgrund_id)
	{
		return $this->setBasic(
			getAuthUID(),
			date('c'),
			Prestudentstatus_model::STATUS_DIPLOMAND,
			$prestudent_id,
			$studiensemester_kurzbz,
			$ausbildungssemester,
			$statusgrund_id
		);
	}

	public function setAbsolvent($prestudent_id, $studiensemester_kurzbz, $ausbildungssemester, $statusgrund_id)
	{
		$authUID = getAuthUID();
		$now = date('c');


		$result = $this->setBasic(
			$authUID,
			$now,
			Prestudentstatus_model::STATUS_ABSOLVENT,
			$prestudent_id,
			$studiensemester_kurzbz,
			$ausbildungssemester,
			$statusgrund_id
		);

		if (isError($result))
			return $result;

		
		// Load Student
		$result = $this->_ci->StudentModel->loadWhere(['prestudent_id' => $prestudent_id]);

		if (isError($result))
			return $result;
		if (!hasData($result))
			return error($this->_ci->p->t('studierendenantrag', 'error_no_student_for_prestudent', ['prestudent_id' => $prestudent_id]));

		$student = current(getData($result));


		// Benutzer inaktiv setzen
		$this->_ci->BenutzerModel->update([
			'uid' =>  $student->student_uid
		], [
			'aktiv' => false,
			'updateaktivvon' => $authUID,
			'updateaktivam' => $now,
			'updatevon' => $authUID,
			'updateamum' => $now
		]);

		if (isError($result))
			return $result;

		return success();
	}

	public function setBewerber($prestudent_id, $studiensemester_kurzbz, $ausbildungssemester, $statusgrund_id)
	{
		$result = $this->setBasic(
			getAuthUID(),
			date('c'),
			Prestudentstatus_model::STATUS_BEWERBER,
			$prestudent_id,
			$studiensemester_kurzbz,
			$ausbildungssemester,
			$statusgrund_id
		);

		if (isError($result))
			return $result;

		if (SEND_BEWERBER_INFOMAIL) {
			// TODO(chris): IMPLEMENT!
		}

		return success();
	}

	public function setAufgenommener($prestudent_id, $studiensemester_kurzbz, $ausbildungssemester, $statusgrund_id)
	{
		return $this->setBasic(
			getAuthUID(),
			date('c'),
			Prestudentstatus_model::STATUS_AUFGENOMMENER,
			$prestudent_id,
			$studiensemester_kurzbz,
			$ausbildungssemester,
			$statusgrund_id
		);
	}

	public function setAbgewiesener($prestudent_id, $studiensemester_kurzbz, $ausbildungssemester, $statusgrund_id)
	{
		return $this->setBasic(
			getAuthUID(),
			date('c'),
			Prestudentstatus_model::STATUS_ABGEWIESENER,
			$prestudent_id,
			$studiensemester_kurzbz,
			$ausbildungssemester,
			$statusgrund_id
		);
	}

	public function setWartender($prestudent_id, $studiensemester_kurzbz, $ausbildungssemester, $statusgrund_id)
	{
		return $this->setBasic(
			getAuthUID(),
			date('c'),
			Prestudentstatus_model::STATUS_WARTENDER,
			$prestudent_id,
			$studiensemester_kurzbz,
			$ausbildungssemester,
			$statusgrund_id
		);
	}

	protected function setBasic($authUID, $now, $status_kurzbz, $prestudent_id, $studiensemester_kurzbz, $ausbildungssemester, $statusgrund_id = null)
	{
		$result = $this->_ci->PrestudentstatusModel->getLastStatus($prestudent_id);

		if (isError($result))
			return $result;
		if (!hasData($result))
			return error($this->_ci->p->t('studierendenantrag', 'error_no_prestudentstatus', [
				'prestudent_id' => $prestudent_id
			]));

		$prestudent_status = current(getData($result));


		// Update Aktionen

		// Status updaten
		$result = $this->_ci->PrestudentstatusModel->insert([
			'prestudent_id' => $prestudent_id,
			'status_kurzbz' => $status_kurzbz,
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'ausbildungssemester' => $ausbildungssemester,
			'datum' => $now,
			'insertvon' => $authUID,
			'insertamum' => $now,
			'orgform_kurzbz'=> $prestudent_status->orgform_kurzbz,
			'studienplan_id'=> $prestudent_status->studienplan_id,
			'bestaetigtvon' => $authUID,
			'bestaetigtam' => $now,
			'statusgrund_id' => $statusgrund_id
		]);

		if (isError($result))
			return $result;

		return success();
	}
}
