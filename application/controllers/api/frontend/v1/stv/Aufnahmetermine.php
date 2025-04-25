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
			'getListPlacementTests' => ['admin:rw', 'assistenz:rw'],
			'getListStudyPlans' => ['admin:rw', 'assistenz:rw'],

		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');

		// Load language phrases
		$this->loadPhrases([
			'ui',
			'admission'
		]);

		// Load models
		$this->load->model('crm/Reihungstest_model', 'ReihungstestModel');
		$this->load->model('crm/RtPerson_model', 'RtPersonModel');
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

		$formData = $this->input->post('formData');
		$person_id = $this->input->post('person_id');

		if(!$person_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Person ID']), self::ERROR_TYPE_GENERAL);
		}



		$_POST['rt_id'] =	(isset($formData['rt_id']) && !empty($formData['rt_id'])) ? $formData['rt_id'] : null;
		$_POST['anmeldedatum'] =	(isset($formData['anmeldedatum']) && !empty($formData['anmeldedatum'])) ? $formData['anmeldedatum'] : null;
		$_POST['teilgenommen'] =	(isset($formData['teilgenommen']) && !empty($formData['teilgenommen'])) ? $formData['teilgenommen'] : false;
		$_POST['studienplan_id'] = (isset($formData['studienplan_id']) && !empty($formData['studienplan_id'])) ? $formData['studienplan_id'] : null;
		$_POST['punkte'] = (isset($formData['punkte']) && !empty($formData['punkte'])) ? $formData['punkte'] : null;

		$this->form_validation->set_rules('punkte', 'Punkte', 'numeric', [
			'required' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Punkte'])
		]);
		$this->form_validation->set_rules('studienplan_id', 'studienplan_id', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Studienplan'])
		]);
		$this->form_validation->set_rules('rt_id', 'Reihungstest_id', 'required', [
			'is_valid_date' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Reihungstest'])
		]);

		$this->form_validation->set_rules('anmeldedatum', 'AnmeldeDatum', 'is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'Anmeldedatum'])
		]);


		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->RtPersonModel->insert([
			'person_id' => $person_id,
			'rt_id' => $_POST['rt_id'],
			'anmeldedatum' => $_POST['anmeldedatum'],
			'teilgenommen' => $_POST['teilgenommen'],
			'studienplan_id' => $_POST['studienplan_id'],
			'punkte' => $_POST['punkte'],
			'insertamum' => date('c'),
			'insertvon' => $authUID,
		]);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function loadAufnahmetermin($rt_person_id)
	{
		$result = $this->RtPersonModel->loadWhere(
			array('rt_person_id' => $rt_person_id)
		);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($data));
	}

	public function updateAufnahmetermin()
	{
		$this->load->library('form_validation');
		$authUID = getAuthUID();

		$formData = $this->input->post('formData');
		$rt_person_id = $this->input->post('rt_person_id');
		$person_id = (isset($formData['person_id']) && !empty($formData['person_id'])) ? $formData['person_id'] : null;


		if(!$person_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Person ID']), self::ERROR_TYPE_GENERAL);
		}
		if(!$rt_person_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'RT_Person ID']), self::ERROR_TYPE_GENERAL);
		}


		$_POST['rt_id'] =	(isset($formData['rt_id']) && !empty($formData['rt_id'])) ? $formData['rt_id'] : null;
		$_POST['anmeldedatum'] =	(isset($formData['anmeldedatum']) && !empty($formData['anmeldedatum'])) ? $formData['anmeldedatum'] : null;
		$_POST['teilgenommen'] =	(isset($formData['teilgenommen']) && !empty($formData['teilgenommen'])) ? $formData['teilgenommen'] : false;
		$_POST['studienplan_id'] = (isset($formData['studienplan_id']) && !empty($formData['studienplan_id'])) ? $formData['studienplan_id'] : null;
		$_POST['punkte'] = (isset($formData['punkte']) && !empty($formData['punkte'])) ? $formData['punkte'] : null;

		$this->form_validation->set_rules('punkte', 'Punkte', 'numeric', [
			'required' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Punkte'])
		]);
		$this->form_validation->set_rules('studienplan_id', 'studienplan_id', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Studienplan'])
		]);
		$this->form_validation->set_rules('rt_id', 'Reihungstest_id', 'required', [
			'is_valid_date' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Reihungstest'])
		]);

		$this->form_validation->set_rules('anmeldedatum', 'AnmeldeDatum', 'is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'Anmeldedatum'])
		]);


		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->RtPersonModel->update(
			[
				'rt_person_id' => $rt_person_id,
				'person_id' => $person_id,
			],
			[
				'rt_id' => $_POST['rt_id'],
				'anmeldedatum' => $_POST['anmeldedatum'],
				'teilgenommen' => $_POST['teilgenommen'],
				'studienplan_id' => $_POST['studienplan_id'],
				'punkte' => $_POST['punkte'],
				'insertamum' => date('c'),
				'insertvon' => $authUID,
		]);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function deleteAufnahmetermin($rt_person_id)
	{
		$result = $this->RtPersonModel->delete(
			array('rt_person_id' => $rt_person_id)
		);

		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);
	}

	public function getListPlacementTests()
	{
		$result = $this->ReihungstestModel->getAllReihungstests();
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getListStudyPlans($person_id)
	{
		$this->load->model('organisation/Studienplan_model','StudienplanModel');

		$result = $this->StudienplanModel->getStudienplaeneForPerson($person_id);;
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

}
