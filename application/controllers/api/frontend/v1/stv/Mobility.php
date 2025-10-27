<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;
use CI3_Events as Events;

class Mobility extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getMobilitaeten' => ['admin:r', 'assistenz:r'],
			'loadMobility' => ['admin:r', 'assistenz:r'],
			'insertMobility' => ['admin:rw', 'assistenz:rw'],
			'updateMobility' => ['admin:rw', 'assistenz:rw'],
			'deleteMobility' => ['admin:rw', 'assistenz:rw'],
			'getProgramsMobility' => ['admin:r', 'assistenz:r'],
			'getLVList' => ['admin:r', 'assistenz:r'],
			'getAllLehreinheiten' => ['admin:r', 'assistenz:r'],
			'getLvsandLesByStudent' => ['admin:r', 'assistenz:r'],
			'getPurposes' => ['admin:r', 'assistenz:r'],
			'getSupports' => ['admin:r', 'assistenz:r'],
			'getListPurposes' => ['admin:r', 'assistenz:r'],
			'getListSupports' => ['admin:r', 'assistenz:r'],
			'deleteMobilityPurpose' => ['admin:r', 'assistenz:r'],
			'addMobilityPurpose' => ['admin:r', 'assistenz:r'],
			'deleteMobilitySupport' => ['admin:r', 'assistenz:r'],
			'addMobilitySupport' => ['admin:r', 'assistenz:r'],
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');

		// Load language phrases
		$this->loadPhrases([
			'ui',
			'mobility'
		]);

		// Load models
		$this->load->model('codex/Bisio_model', 'BisioModel');

		//Permission checks for Studiengangsarray
		$allowedStgs = $this->permissionlib->getSTG_isEntitledFor('assistenz') ?: [];

		if ($this->router->method == 'insertMobility' || $this->router->method == 'updateMobility')
		{
			$student_uid = $this->input->post('uid');
			if(!$student_uid)
			{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Student UID']), self::ERROR_TYPE_GENERAL);
			}
			$this->_checkAllowedStgsFromUid($student_uid, $allowedStgs);
		}

		if ($this->router->method == 'deleteMobility') {
			$bisio_id = $this->input->post('bisio_id');
			if(!$bisio_id)
			{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Bisio ID']), self::ERROR_TYPE_GENERAL);
			}
			$result = $this->BisioModel->load(
				array('bisio_id' => $bisio_id)
			);
			$data = $this->getDataOrTerminateWithError($result);
			$student_uid = current($data)->student_uid;

			$this->_checkAllowedStgsFromUid($student_uid, $allowedStgs);
		}
	}

	private function _checkAllowedStgsFromUid($student_uid, $allowedStgs)
	{
		$this->load->model('crm/Student_model', 'StudentModel');
		$result = $this->StudentModel->loadWhere(['student_uid' => $student_uid]);
		$data = $this->getDataOrTerminateWithError($result);
		$studiengang_kz = current($data)->studiengang_kz;

		if (!in_array($studiengang_kz, $allowedStgs))
		{
			return $this->terminateWithError($this->p->t('ui', 'error_keineBerechtigungStg'), self::ERROR_TYPE_GENERAL);
		}
	}

	public function getMobilitaeten($student_uid)
	{
		$this->BisioModel->addSelect("*");
		$this->BisioModel->addJoin('bis.tbl_mobilitaetsprogramm mp', 'ON (mp.mobilitaetsprogramm_code = bis.tbl_bisio.mobilitaetsprogramm_code)', 'LEFT');
		$this->BisioModel->addJoin('lehre.tbl_lehreinheit le', 'ON (le.lehreinheit_id = bis.tbl_bisio.lehreinheit_id)', 'LEFT');
		$this->BisioModel->addOrder('von', 'DESC');
		$this->BisioModel->addOrder('bis', 'DESC');
		$this->BisioModel->addOrder('bisio_id', 'DESC');
		$result = $this->BisioModel->loadWhere(
			array('student_uid' => $student_uid)
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getProgramsMobility()
	{
		$this->load->model('codex/Mobilitaetsprogramm_model', 'MobilitaetsprogrammModel');

		$result = $this->MobilitaetsprogrammModel->load();
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function insertMobility()
	{
		$this->load->library('form_validation');
		$authUID = getAuthUID();

		$student_uid = $this->input->post('uid');

		if(!$student_uid)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Student UID']), self::ERROR_TYPE_GENERAL);
		}

		$formData = $this->input->post('formData');

		$von = $formData['von'] ?? null;
		$bis = $formData['bis'] ?? null;
		$nation_code = $formData['nation_code'] ?? null;
		$mobilitaetsprogramm_code = $formData['mobilitaetsprogramm_code'] ?? null;
		$herkunftsland_code = $formData['herkunftsland_code'] ?? null;
		$ects_erworben = $formData['ects_erworben'] ?? null;
		$ects_angerechnet = $formData['ects_angerechnet'] ?? null;
		$lehreinheit_id = $formData['lehreinheit_id'] ?? null;
		$ort = $formData['ort'] ?? null;
		$universitaet = $formData['universitaet'] ?? null;
		$localPurposes = $formData['localPurposes'] ?? null;
		$localSupports = $formData['localSupports'] ?? null;

		$this->form_validation->set_data($formData);

		$this->form_validation->set_rules('nation_code', 'Nation_code', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Nation_code'])
		]);
		$this->form_validation->set_rules('herkunftsland_code', 'Herkunftsland_code', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Herkunftsland_code'])
		]);
		$this->form_validation->set_rules('mobilitaetsprogramm_code', 'Mobilitaetsprogramm_code', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Mobilitaetsprogramm_code'])
		]);
		$this->form_validation->set_rules('von', 'VonDatum', 'is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'VonDatum'])
		]);

		$this->form_validation->set_rules('bis', 'VBisDatum', 'is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'VBisDatum'])
		]);

		$this->form_validation->set_rules('ects_erworben', 'Ects_erworben', 'numeric', [
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Ects_erworben'])
		]);

		$this->form_validation->set_rules('ects_angerechnet', 'Ects_angerechnet', 'numeric', [
		'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Ects_angerechnet'])
		]);

		$this->form_validation->set_rules('lehreinheit_id', 'Lehreinheit', 'numeric', [
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Lehreinheit'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->BisioModel->insert([
			'student_uid' => $student_uid,
			'von' => $von,
			'bis' => $bis,
			'mobilitaetsprogramm_code' => $mobilitaetsprogramm_code,
			'nation_code' => $nation_code,
			'herkunftsland_code' => $herkunftsland_code,
			'lehreinheit_id' => $lehreinheit_id,
			'ort' => $ort,
			'universitaet' => $universitaet,
			'ects_erworben' => $ects_erworben ,
			'ects_angerechnet' => $ects_angerechnet,
			'insertamum' => date('c'),
			'insertvon' => $authUID,
		]);

		$bisio_id = $this->getDataOrTerminateWithError($result);

		//check if localData (purposes)
		if(count($localPurposes) > 0){
			foreach ($localPurposes as $zweck){
				$zweck = (int)$zweck;
				$this->addMobilityPurpose($bisio_id, $zweck);
			}
		}

		//check if localData (supports)
		if(count($localSupports) > 0){
			foreach ($localSupports as $support){
				$this->addMobilitySupport($bisio_id, $support);
			}
		}

		$this->terminateWithSuccess($bisio_id);
	}

	public function loadMobility($bisio_id)
	{
		$this->BisioModel->addSelect("*");
		$this->BisioModel->addJoin('bis.tbl_mobilitaetsprogramm mp', 'ON (mp.mobilitaetsprogramm_code = bis.tbl_bisio.mobilitaetsprogramm_code)', 'LEFT');
		$this->BisioModel->addJoin('lehre.tbl_lehreinheit le', 'ON (le.lehreinheit_id = bis.tbl_bisio.lehreinheit_id)', 'LEFT');
		$result = $this->BisioModel->loadWhere(
			array('bisio_id' => $bisio_id)
		);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($data));
	}

	public function updateMobility()
	{

		$this->load->library('form_validation');
		$authUID = getAuthUID();

		$student_uid = $this->input->post('uid');

		if(!$student_uid)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Student UID']), self::ERROR_TYPE_GENERAL);
		}
		$formData = $this->input->post('formData');

		$von = $formData['von'] ?? null;
		$bis = $formData['bis'] ?? null;
		$nation_code = $formData['nation_code'] ?? null;
		$mobilitaetsprogramm_code = $formData['mobilitaetsprogramm_code'] ?? null;
		$herkunftsland_code = $formData['herkunftsland_code'] ?? null;
		$ects_erworben = $formData['ects_erworben'] ?? null;
		$ects_angerechnet = $formData['ects_angerechnet'] ?? null;
		$lehreinheit_id = $formData['lehreinheit_id'] ?? null;
		$ort = $formData['ort'] ?? null;
		$universitaet = $formData['universitaet'] ?? null;

		$this->form_validation->set_data($formData);

		$this->form_validation->set_rules('nation_code', 'Nation_code', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Nation_code'])
		]);
		$this->form_validation->set_rules('herkunftsland_code', 'Herkunftsland_code', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Herkunftsland_code'])
		]);

		$this->form_validation->set_rules('mobilitaetsprogramm_code', 'Mobilitaetsprogramm_code', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Mobilitaetsprogramm_code'])
		]);
		$this->form_validation->set_rules('von', 'VonDatum', 'is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'VonDatum'])
		]);

		$this->form_validation->set_rules('bis', 'VBisDatum', 'is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'VBisDatum'])
		]);

		$this->form_validation->set_rules('ects_erworben', 'Ects_erworben', 'numeric', [
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Ects_erworben'])
		]);

		$this->form_validation->set_rules('ects_angerechnet', 'Ects_angerechnet', 'numeric', [
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Ects_angerechnet'])
		]);

		$this->form_validation->set_rules('lehreinheit_id', 'Lehreinheit', 'numeric', [
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Lehreinheit'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->BisioModel->update(
			[
				'bisio_id' =>  $formData['bisio_id']
			],
			[
				'student_uid' => $student_uid,

				'von' => $von,
				'bis' => $bis,
				'mobilitaetsprogramm_code' => $mobilitaetsprogramm_code,
				'nation_code' => $nation_code,
				'herkunftsland_code' => $herkunftsland_code,
				'lehreinheit_id' => $lehreinheit_id,
				'ort' => $ort,
				'universitaet' => $universitaet,
				'ects_erworben' => $ects_erworben ,
				'ects_angerechnet' => $ects_angerechnet,
				'updateamum' => date('c'),
				'updatevon' => $authUID,
			]
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($data));
	}

	public function deleteMobility()
	{
		$bisio_id = $this->input->post('bisio_id');

		//check if entry in MobilityOnline extension exists
		Events::trigger('mobility_delete', $bisio_id);

		$result = $this->BisioModel->delete(
			array('bisio_id' => $bisio_id)
		);

		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);

	}

	public function getLVList($studiengang_kz)
	{
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');

		$result = $this->LehrveranstaltungModel->getLvsByStudiengangkz($studiengang_kz);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getAllLehreinheiten()
	{
		$lv_id = $this->input->post('lv_id');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');

		$this->load->model('education/Lehreinheit_model', 'LehreinheitModel');

		$result = $this->LehreinheitModel->getLesFromLvIds($lv_id, $studiensemester_kurzbz);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getLvsandLesByStudent($student_uid)
	{
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');

		$result = $this->LehrveranstaltungModel->getLvsByStudent($student_uid);

		$data = $this->getDataOrTerminateWithError($result);

		$lv_ids = array();
		$allData = array();

		foreach ($data as $lehrveranstaltung) {
			$lv_ids[] = $lehrveranstaltung->lehrveranstaltung_id;
		}

		$this->load->model('education/Lehreinheit_model', 'LehreinheitModel');

		foreach ($lv_ids as $id)
		{
			$result = $this->LehreinheitModel->getLesFromLvIds($id);
			$data = $this->getDataOrTerminateWithError($result);

			if (is_array($data)) {
				$allData = array_merge($allData, $data);
			}
		}

		return $this->terminateWithSuccess($allData);
	}

	public function getPurposes($bisio_id)
	{
		$bisio_id = (int)$bisio_id;

		$this->load->model('codex/Bisiozweck_model', 'BisiozweckModel');

		$this->BisiozweckModel->addSelect("*");
		$this->BisiozweckModel->addJoin('bis.tbl_zweck zw', 'ON (zw.zweck_code = bis.tbl_bisio_zweck.zweck_code)');

		$result = $this->BisiozweckModel->loadWhere(
			array('bisio_id' => $bisio_id)
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getSupports($bisio_id)
	{
		$bisio_id = (int)$bisio_id;

		$this->load->model('codex/Bisioaufenthaltfoerderung_model', 'BisioaufenthaltfoerderungModel');

		$this->BisioaufenthaltfoerderungModel->addSelect("*");
		$this->BisioaufenthaltfoerderungModel->addJoin('bis.tbl_aufenthaltfoerderung af', 'ON (af.aufenthaltfoerderung_code = bis.tbl_bisio_aufenthaltfoerderung.aufenthaltfoerderung_code)');

		$result = $this->BisioaufenthaltfoerderungModel->loadWhere(
			array('bisio_id' => $bisio_id)
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getListPurposes()
	{
		$this->load->model('codex/Zweck_model', 'ZweckModel');

		$result = $this->ZweckModel->load();

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getListSupports()
	{
		$this->load->model('codex/Aufenthaltfoerderung_model', 'AufenthaltfoerderungModel');

		$result = $this->AufenthaltfoerderungModel->load();

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function addMobilityPurpose($bisio_id, $local_purpose = null)
	{
		$zweck_code = $this->input->post('zweck_code');

		if($local_purpose){
			$zweck_code = $local_purpose;
		}

		$this->load->model('codex/Bisiozweck_model', 'BisiozweckModel');
		if(!$local_purpose)
		{
			$check = $this->BisiozweckModel->loadWhere(
				[
					'bisio_id' => $bisio_id,
					'zweck_code' => $zweck_code,
				]
			);
			if (hasData($check))
			{
				$this->terminateWithError($this->p->t('ui', 'error_entryExisting'), self::ERROR_TYPE_GENERAL);
			}
		}

		$result = $this->BisiozweckModel->insert(
			array(
				'bisio_id' => $bisio_id,
				'zweck_code' => $zweck_code
			)
		);

		$data = $this->getDataOrTerminateWithError($result);

		if($local_purpose)
		{
			return $data;
		}

		return $this->terminateWithSuccess(current($data));
	}

	public function deleteMobilityPurpose($bisio_id)
	{
		$zweck_code = $this->input->post('zweck_code');

		$this->load->model('codex/Bisiozweck_model', 'BisiozweckModel');


		$result = $this->BisiozweckModel->delete(
			array(
				'bisio_id' => $bisio_id,
				'zweck_code' => $zweck_code
			)
		);

		$data = $this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess(current($data));
	}

	public function addMobilitySupport($bisio_id, $local_support = null)
	{
		$aufenthaltfoerderung_code = $this->input->post('aufenthaltfoerderung_code');

		if($local_support){
			$aufenthaltfoerderung_code = $local_support;
		}
		$this->load->model('codex/Bisioaufenthaltfoerderung_model', 'BisioaufenthaltfoerderungModel');

		if(!$local_support)
		{
			$check = $this->BisioaufenthaltfoerderungModel->loadWhere(
				[
					'bisio_id' => $bisio_id,
					'aufenthaltfoerderung_code' => $aufenthaltfoerderung_code,
				]
			);
			if (hasData($check))
			{
				$this->terminateWithError($this->p->t('ui', 'error_entryExisting'), self::ERROR_TYPE_GENERAL);
			}
		}

		$result = $this->BisioaufenthaltfoerderungModel->insert(
			array(
				'bisio_id' => $bisio_id,
				'aufenthaltfoerderung_code' => $aufenthaltfoerderung_code
			)
		);

		$data = $this->getDataOrTerminateWithError($result);

		if($local_support)
		{
			return $data;
		}

		return $this->terminateWithSuccess(current($data));
	}

	public function deleteMobilitySupport($bisio_id)
	{
		$aufenthaltfoerderung_code = $this->input->post('aufenthaltfoerderung_code');

		$this->load->model('codex/Bisioaufenthaltfoerderung_model', 'BisioaufenthaltfoerderungModel');

		$result = $this->BisioaufenthaltfoerderungModel->delete(
			array(
				'bisio_id' => $bisio_id,
				'aufenthaltfoerderung_code' => $aufenthaltfoerderung_code
			)
		);
		$data = $this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess(current($data));
	}
}
