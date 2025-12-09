<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class GemeinsameStudien extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getStudien' => ['admin:r', 'assistenz:r'],
			'loadStudie' => ['admin:r', 'assistenz:r'],
			'insertStudie' => ['admin:rw', 'assistenz:rw'],
			'updateStudie' => ['admin:rw', 'assistenz:rw'],
			'deleteStudie' => ['admin:rw', 'assistenz:rw'],
			'getProgramsStudien' => ['admin:r', 'assistenz:r'],
			'getTypenMobility' => ['admin:r', 'assistenz:r'],
			'getStudiensemester' => ['admin:r', 'assistenz:r'],
			'getStudienprogramme' => ['admin:r', 'assistenz:r'],
			'getPartnerfirmen' => ['admin:r', 'assistenz:r'],
			'getStatiPrestudent' => ['admin:r', 'assistenz:r'],
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');

		// Load language phrases
		$this->loadPhrases([
			'ui',
			'jointstudies'
		]);

		// Load models
		$this->load->model('codex/Mobilitaet_model', 'MobilitaetModel');

		//TODO(check if additional Permissions necessary): 'student/stammdaten'
	}

	public function getStudien($prestudent_id)
	{
		$this->MobilitaetModel->addSelect('mobilitaet_id');
		$this->MobilitaetModel->addSelect('mobilitaetstyp_kurzbz');
		$this->MobilitaetModel->addSelect('prestudent_id');
		$this->MobilitaetModel->addSelect('studiensemester_kurzbz');
		$this->MobilitaetModel->addSelect('bis.tbl_mobilitaet.mobilitaetsprogramm_code');
		$this->MobilitaetModel->addSelect('bis.tbl_mobilitaet.gsprogramm_id');
		$this->MobilitaetModel->addSelect('bis.tbl_mobilitaet.firma_id');
		$this->MobilitaetModel->addSelect('status_kurzbz');
		$this->MobilitaetModel->addSelect('ausbildungssemester');
		$this->MobilitaetModel->addSelect('bis.tbl_mobilitaet.insertvon');
		$this->MobilitaetModel->addSelect('bis.tbl_mobilitaet.insertamum');
		$this->MobilitaetModel->addSelect('bis.tbl_mobilitaet.updatevon');
		$this->MobilitaetModel->addSelect('bis.tbl_mobilitaet.updateamum');
		$this->MobilitaetModel->addSelect('mp.kurzbz');
		$this->MobilitaetModel->addSelect('gp.gsprogrammtyp_kurzbz');
		$this->MobilitaetModel->addSelect('gp.bezeichnung as studienprogramm');
		$this->MobilitaetModel->addSelect('f.name as partner');

		$this->MobilitaetModel->addJoin('bis.tbl_mobilitaetsprogramm mp', 'ON (mp.mobilitaetsprogramm_code = bis.tbl_mobilitaet.mobilitaetsprogramm_code)', 'LEFT');
		$this->MobilitaetModel->addJoin('bis.tbl_gsprogramm gp', 'ON (gp.gsprogramm_id = bis.tbl_mobilitaet.gsprogramm_id)', 'LEFT');
		$this->MobilitaetModel->addJoin('public.tbl_firma f', 'ON (f.firma_id = bis.tbl_mobilitaet.firma_id)', 'LEFT');

		$result = $this->MobilitaetModel->loadWhere([
			'prestudent_id' => $prestudent_id,
		]);

		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);
	}

	public function getTypenMobility()
	{
		$this->load->model('codex/Mobilitaetstyp_model', 'MobilitaetstypModel');

		$result = $this->MobilitaetstypModel->load();
		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);
	}

	public function getStudiensemester()
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$this->StudiensemesterModel->addOrder('start', 'DESC');
		$result = $this->StudiensemesterModel->load();
		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);
	}

	public function getStudienprogramme()
	{
		$this->load->model('codex/Gsprogramm_model', 'GsprogrammModel');

		$result = $this->GsprogrammModel->load();
		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);
	}

	public function getPartnerfirmen()
	{
		$this->load->model('ressource/Firma_model', 'FirmaModel');

		$result = $this->FirmaModel->loadWhere(
			['partner_code !=' => null]
		);
		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);
	}

	public function getStatiPrestudent()
	{
		$this->load->model('crm/Status_model', 'StatusModel');

		$result = $this->StatusModel->load();
		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);
	}

	public function loadStudie($mobilitaet_id)
	{
		$result = $this->MobilitaetModel->load($mobilitaet_id);
		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess(current($data));
	}

	public function insertStudie()
	{
		$this->load->library('form_validation');
		$authUID = getAuthUID();

		$prestudent_id = $this->input->post('prestudent_id');
		if(!$prestudent_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Prestudent ID']), self::ERROR_TYPE_GENERAL);
		}

		$formData = $this->input->post('formData');
		$ausbildungssemester = (isset($formData['ausbildungssemester']) && !empty($formData['ausbildungssemester']))
			? $formData['ausbildungssemester']
			: null;
		$mobilitaetstyp_kurzbz = (isset($formData['mobilitaetstyp_kurzbz']) && !empty($formData['mobilitaetstyp_kurzbz']))
			? $formData['mobilitaetstyp_kurzbz']
			: null;
		$studiensemester_kurzbz = (isset($formData['studiensemester_kurzbz']) && !empty($formData['studiensemester_kurzbz']))
			? $formData['studiensemester_kurzbz'] : null;

		$this->form_validation->set_data($formData);

		$this->form_validation->set_rules('mobilitaetstyp_kurzbz', 'Typ', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Typ'])
		]);

		$this->form_validation->set_rules('studiensemester_kurzbz', 'Studiensemester', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Studiensemester'])
		]);

		$this->form_validation->set_rules('ausbildungssemester', 'Ausbildungssemester', 'required|numeric', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Ausbildungssemester']),
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Ausbildungssemester']),
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$status_kurzbz = (isset($formData['status_kurzbz']) && !empty($formData['status_kurzbz']))
			? $formData['status_kurzbz']
			: null;
		$mobilitaetsprogramm_code = (isset($formData['mobilitaetsprogramm_code']) && !empty($formData['mobilitaetsprogramm_code']))
			? $formData['mobilitaetsprogramm_code']
			: null;
		$gsprogramm_id = (isset($formData['gsprogramm_id']) && !empty($formData['gsprogramm_id']))
			? $formData['gsprogramm_id']
			: null;
		$firma_id= (isset($formData['firma_id']) && !empty($formData['firma_id'])) ? $formData['firma_id'] : null;

		$result = $this->MobilitaetModel->insert([
			'prestudent_id' => $prestudent_id,
			'mobilitaetstyp_kurzbz' =>$mobilitaetstyp_kurzbz,
			'status_kurzbz' => $status_kurzbz,
			'studiensemester_kurzbz' =>$studiensemester_kurzbz,
			'mobilitaetsprogramm_code' => $mobilitaetsprogramm_code,
			'gsprogramm_id' => $gsprogramm_id,
			'firma_id' => $firma_id,
			'ausbildungssemester' =>$ausbildungssemester,
			'insertvon' => $authUID,
			'insertamum' => date('c'),
		]);

		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);
	}

	public function updateStudie()
	{
		$this->load->library('form_validation');
		$authUID = getAuthUID();

		$prestudent_id = $this->input->post('prestudent_id');
		if(!$prestudent_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Prestudent ID']), self::ERROR_TYPE_GENERAL);
		}

		$formData = $this->input->post('formData');

		$mobilitaet_id = (isset($formData['mobilitaet_id']) && !empty($formData['mobilitaet_id']))
			? $formData['mobilitaet_id'] :
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Mobilitaet ID']), self::ERROR_TYPE_GENERAL);
		$ausbildungssemester = (isset($formData['ausbildungssemester']) && !empty($formData['ausbildungssemester']))
			? $formData['ausbildungssemester']
			: null;
		$mobilitaetstyp_kurzbz = (isset($formData['mobilitaetstyp_kurzbz']) && !empty($formData['mobilitaetstyp_kurzbz']))
			? $formData['mobilitaetstyp_kurzbz']
			: null;
		$studiensemester_kurzbz = (isset($formData['studiensemester_kurzbz']) && !empty($formData['studiensemester_kurzbz']))
			? $formData['studiensemester_kurzbz']
			: null;

		$this->form_validation->set_data($formData);

		$this->form_validation->set_rules('mobilitaetstyp_kurzbz', 'Typ', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Typ'])
		]);

		$this->form_validation->set_rules('studiensemester_kurzbz', 'Studiensemester', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Studiensemester'])
		]);

		$this->form_validation->set_rules('ausbildungssemester', 'Ausbildungssemester', 'required|numeric', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Ausbildungssemester']),
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Ausbildungssemester']),
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$status_kurzbz = (isset($formData['status_kurzbz']) && !empty($formData['status_kurzbz'])) ? $formData['status_kurzbz'] : null;
		$mobilitaetsprogramm_code = (isset($formData['mobilitaetsprogramm_code']) && !empty($formData['mobilitaetsprogramm_code']))
			? $formData['mobilitaetsprogramm_code']
			: null;
		$gsprogramm_id = (isset($formData['gsprogramm_id']) && !empty($formData['gsprogramm_id']))
			? $formData['gsprogramm_id']
			: null;
		$firma_id= (isset($formData['firma_id']) && !empty($formData['firma_id'])) ? $formData['firma_id'] : null;

		$result = $this->MobilitaetModel->update(
			[
				'mobilitaet_id' => $mobilitaet_id,
			],
			[
				'prestudent_id' => $prestudent_id,
				'mobilitaetstyp_kurzbz' => $mobilitaetstyp_kurzbz,
				'status_kurzbz' => $status_kurzbz,
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'mobilitaetsprogramm_code' => $mobilitaetsprogramm_code,
				'gsprogramm_id' => $gsprogramm_id,
				'firma_id' => $firma_id,
				'ausbildungssemester' => $ausbildungssemester,
				'updatevon' => $authUID,
				'updateamum' => date('c'),
			]
		);

		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);
	}

	public function deleteStudie($mobilitaet_id)
	{
		if(!$mobilitaet_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Mobilität ID']), self::ERROR_TYPE_GENERAL);
		}
		$result = $this->MobilitaetModel->delete(
			array('mobilitaet_id' => $mobilitaet_id)
		);

		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);
	}
}
