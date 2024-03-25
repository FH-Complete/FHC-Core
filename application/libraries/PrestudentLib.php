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

	public function setAbbrecher($prestudent_id, $studiensemester_kurzbz, $insertvon = null, $statusgrund_kurzbz = null, $datum = null, $bestaetigtam = null, $bestaetigtvon = null)
	{
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

		//Status und Statusgrund updaten
		$result = $this->_ci->PrestudentstatusModel->withGrund($statusgrund_kurzbz)->insert([
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
			'bestaetigtam' => $bestaetigtam
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

		//noch nicht eingetragene Zeugnisnoten auf 9 setzen
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


		//Update Aktionen

		//StudentModel updaten
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

		//Benutzer inaktiv setzen
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

	public function setUnterbrecher($prestudent_id, $studiensemester_kurzbz, $studierendenantrag_id, $insertvon = null)
	{
		$ausbildungssemester_plus = 0;
		if (!$insertvon)
			$insertvon = getAuthUID();

		$result = $this->_ci->PrestudentstatusModel->getLastStatus($prestudent_id, $studiensemester_kurzbz);
		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result) {
			//NOTE(manu): only valid if nextSemester focus max

			$result = $this->_ci->PrestudentstatusModel->getLastStatus($prestudent_id);
			if (isError($result))
				return $result;
			$result = getData($result);

			//check if ausbildungssemester is last
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

		$resultAntrag = $this->_ci->StudierendenantragModel->load($studierendenantrag_id);
		if (isError($resultAntrag))
			return $resultAntrag;
		$resultAntrag = getData($resultAntrag);
		if (!$resultAntrag)
			return error($this->_ci->p->t('studierendenantrag', 'error_no_antrag_found', ['id' => $studierendenantrag_id]));

		$antrag = current($resultAntrag);

		//Status updaten
		$result = $this->_ci->PrestudentstatusModel->insert([
			'prestudent_id' => $prestudent_id,
			'status_kurzbz' => Prestudentstatus_model::STATUS_UNTERBRECHER,
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'ausbildungssemester' => $prestudent_status->ausbildungssemester + $ausbildungssemester_plus,
			'datum' => date('c'),
			'insertvon' => $insertvon,
			'insertamum' => date('c'),
			'orgform_kurzbz'=> $prestudent_status->orgform_kurzbz,
			'studienplan_id'=> $prestudent_status->studienplan_id,
			'bestaetigtvon' => $insertvon,
			'bestaetigtam' => date('c'),
			'anmerkung'=> 'Wiedereinstieg ' . $antrag->datum_wiedereinstieg
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

		//noch nicht eingetragene Zeugnisnoten auf 9 setzen
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


		//Update Aktionen

		//StudentModel updaten
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
}
