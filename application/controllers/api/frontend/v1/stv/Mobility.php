<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

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
	}

	public function getMobilitaeten($student_uid)
	{
		$this->BisioModel->addSelect("*");
		$this->BisioModel->addJoin('bis.tbl_mobilitaetsprogramm mp', 'ON (mp.mobilitaetsprogramm_code = bis.tbl_bisio.mobilitaetsprogramm_code)', 'LEFT');
		$this->BisioModel->addJoin('lehre.tbl_lehreinheit le', 'ON (le.lehreinheit_id = bis.tbl_bisio.lehreinheit_id)','LEFT');
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

		$_POST['von'] =	(isset($formData['von']) && !empty($formData['von'])) ? $formData['von'] : null;
		$_POST['bis'] =	(isset($formData['bis']) && !empty($formData['bis'])) ? $formData['bis'] : null;
		$_POST['nation_code'] =	(isset($formData['nation_code']) && !empty($formData['nation_code'])) ? $formData['nation_code'] : 'A';
		$_POST['mobilitaetsprogramm_code'] = (isset($formData['mobilitaetsprogramm_code']) && !empty($formData['mobilitaetsprogramm_code'])) ? $formData['mobilitaetsprogramm_code'] : null;
		$_POST['herkunftsland_code'] = (isset($formData['herkunftsland_code']) && !empty($formData['herkunftsland_code'])) ? $formData['herkunftsland_code'] : 'A';
		$_POST['ects_erworben'] = (isset($formData['ects_erworben']) && !empty($formData['ects_erworben'])) ? $formData['ects_erworben'] : null;
		$_POST['ects_angerechnet'] = (isset($formData['ects_angerechnet']) && !empty($formData['ects_angerechnet'])) ? $formData['ects_angerechnet'] : null;
		$_POST['lehreinheit_id'] = (isset($formData['lehreinheit_id']) && !empty($formData['lehreinheit_id'])) ? $formData['lehreinheit_id'] : null;

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

		$ort =	(isset($formData['ort']) && !empty($formData['ort'])) ? $formData['ort'] : null;
		$universitaet =	(isset($formData['universitaet']) && !empty($formData['universitaet'])) ? $formData['universitaet'] : null;
		$localPurposes = (isset($formData['localPurposes']) && !empty($formData['localPurposes'])) ? $formData['localPurposes'] : null;
		$localSupports = (isset($formData['localSupports']) && !empty($formData['localSupports'])) ? $formData['localSupports'] : null;

		$result = $this->BisioModel->insert([
			'student_uid' => $student_uid,
			'von' => $_POST['von'],
			'bis' => $_POST['bis'],
			'mobilitaetsprogramm_code' => $_POST['mobilitaetsprogramm_code'],
			'nation_code' => $_POST['nation_code'],
			'herkunftsland_code' => $_POST['herkunftsland_code'],
			'lehreinheit_id' => $_POST['lehreinheit_id'],
			'ort' => $ort,
			'universitaet' => $universitaet,
			'ects_erworben' => $_POST['ects_erworben'] ,
			'ects_angerechnet' => $_POST['ects_angerechnet'],
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
		$this->BisioModel->addJoin('lehre.tbl_lehreinheit le', 'ON (le.lehreinheit_id = bis.tbl_bisio.lehreinheit_id)','LEFT');
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

		$_POST['von'] =	(isset($formData['von']) && !empty($formData['von'])) ? $formData['von'] : null;
		$_POST['bis'] =	(isset($formData['bis']) && !empty($formData['bis'])) ? $formData['bis'] : null;
		$_POST['nation_code'] =	(isset($formData['nation_code']) && !empty($formData['nation_code'])) ? $formData['nation_code'] : 'A';
		$_POST['mobilitaetsprogramm_code'] = (isset($formData['mobilitaetsprogramm_code']) && !empty($formData['mobilitaetsprogramm_code'])) ? $formData['mobilitaetsprogramm_code'] : null;
		$_POST['herkunftsland_code'] = (isset($formData['herkunftsland_code']) && !empty($formData['herkunftsland_code'])) ? $formData['herkunftsland_code'] : 'A';
		$_POST['ects_erworben']  = (isset($formData['ects_erworben']) && !empty($formData['ects_erworben'])) ? $formData['ects_erworben'] : null;
		$_POST['ects_angerechnet'] = (isset($formData['ects_angerechnet']) && !empty($formData['ects_angerechnet'])) ? $formData['ects_angerechnet'] : null;
		$_POST['lehreinheit_id'] = (isset($formData['lehreinheit_id']) && !empty($formData['lehreinheit_id'])) ? $formData['lehreinheit_id'] : null;

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
				'von' => $_POST['von'],
				'bis' => $_POST['bis'],
				'mobilitaetsprogramm_code' => $_POST['mobilitaetsprogramm_code'],
				'nation_code' => $_POST['nation_code'],
				'herkunftsland_code' => $_POST['herkunftsland_code'],
				'lehreinheit_id' => $_POST['lehreinheit_id'],
				'ort' => $formData['ort'],
				'universitaet' => $formData['universitaet'],
				'ects_erworben' => $_POST['ects_erworben'] ,
				'ects_angerechnet' => $_POST['ects_angerechnet'],
				'updateamum' => date('c'),
				'updatevon' => $authUID,
			]
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($data));
	}

	public function deleteMobility($bisio_id)
	{
		//check if extension table exists
		$result =  $this->BisioModel->tableExists('extension', 'tbl_mo_bisioidzuordnung');
		$data = $this->getDataOrTerminateWithError($result);

		//if table exists check if existing entry
		if(!empty($data))
		{
			$this->BisioModel->addSelect("count(*)");
			$this->BisioModel->addJoin('extension.tbl_mo_bisioidzuordnung mo', 'ON (mo.bisio_id = bis.tbl_bisio.bisio_id)', 'LEFT');

			$resultCheckMo = $this->BisioModel->loadWhere(
				array('mo.bisio_id' => $bisio_id)
			);

			$resultCheckMo = $this->getDataOrTerminateWithError($resultCheckMo);
			$count = current($resultCheckMo)->count;

			$existsInExtension = $count > 0 ? true : false;

			if($existsInExtension)
				$this->terminateWithError($this->p->t('mobility', 'error_existingEntryInExtension'), self::ERROR_TYPE_GENERAL);
		}

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
