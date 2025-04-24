<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Aufnahmetermine extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getAufnahmetermine' => ['admin:r', 'assistenz:r'],
			'loadAufnahmetermin' => ['admin:r', 'assistenz:r'],
			'insertAufnahmetermin' => ['admin:rw', 'assistenz:rw'],
			'updateAufnahmetermin' => ['admin:rw', 'assistenz:rw'],
			'deleteAufnahmetermin' => ['admin:rw', 'assistenz:rw'],
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
		$this->load->model('crm/Reihungstest_model', 'ReihungstestModel');
	}

	public function getAufnahmetermine($person_id)
	{
		$result = $this->ReihungstestModel->getReihungstestPerson($person_id);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function insertAufnahmetermin()
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

		$aufnahmetermin_id = $this->getDataOrTerminateWithError($result);

		//check if localData (purposes)
		if(count($localPurposes) > 0){
			foreach ($localPurposes as $zweck){
				$zweck = (int)$zweck;
				$this->addAufnahmeterminPurpose($aufnahmetermin_id, $zweck);
			}
		}

		//check if localData (supports)
		if(count($localSupports) > 0){
			foreach ($localSupports as $support){
				$this->addAufnahmeterminSupport($aufnahmetermin_id, $support);
			}
		}

		$this->terminateWithSuccess($aufnahmetermin_id);
	}

	public function loadAufnahmetermin($aufnahmetermin_id)
	{
		$this->BisioModel->addSelect("*");
		$this->BisioModel->addJoin('bis.tbl_mobilitaetsprogramm mp', 'ON (mp.mobilitaetsprogramm_code = bis.tbl_bisio.mobilitaetsprogramm_code)', 'LEFT');
		$this->BisioModel->addJoin('lehre.tbl_lehreinheit le', 'ON (le.lehreinheit_id = bis.tbl_bisio.lehreinheit_id)','LEFT');
		$result = $this->BisioModel->loadWhere(
			array('aufnahmetermin_id' => $aufnahmetermin_id)
		);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($data));
	}

	public function updateAufnahmetermin()
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
				'aufnahmetermin_id' =>  $formData['aufnahmetermin_id']
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

	public function deleteAufnahmetermin($aufnahmetermin_id)
	{
		//check if extension table exists
		$result =  $this->BisioModel->tableExists('extension', 'tbl_mo_bisioidzuordnung');
		$data = $this->getDataOrTerminateWithError($result);

		//if table exists check if existing entry
		if(!empty($data))
		{
			$this->BisioModel->addSelect("count(*)");
			$this->BisioModel->addJoin('extension.tbl_mo_bisioidzuordnung mo', 'ON (mo.aufnahmetermin_id = bis.tbl_bisio.aufnahmetermin_id)', 'LEFT');

			$resultCheckMo = $this->BisioModel->loadWhere(
				array('mo.aufnahmetermin_id' => $aufnahmetermin_id)
			);

			$resultCheckMo = $this->getDataOrTerminateWithError($resultCheckMo);
			$count = current($resultCheckMo)->count;

			$existsInExtension = $count > 0 ? true : false;

			if($existsInExtension)
				$this->terminateWithError($this->p->t('mobility', 'error_existingEntryInExtension'), self::ERROR_TYPE_GENERAL);
		}

		$result = $this->BisioModel->delete(
			array('aufnahmetermin_id' => $aufnahmetermin_id)
		);

		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);
	}

}
