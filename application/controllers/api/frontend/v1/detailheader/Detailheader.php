<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Detailheader extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getHeader' => self::PERM_LOGGED,
			'getPersonAbteilung' => self::PERM_LOGGED,
			'getLeitungOrg' => self::PERM_LOGGED,
			'getSemesterStati' => self::PERM_LOGGED,
		]);
	}

	public function getHeader($person_id)
	{
		$this->load->model('ressource/Mitarbeiter_model', 'Mitarbeitermodel');

		$result = $this->Mitarbeitermodel->getHeader($person_id);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($data));
	}

	public function getPersonAbteilung($mitarbeiter_uid)
	{
		$this->load->model('ressource/Mitarbeiter_model', 'Mitarbeitermodel');

		$result = $this->Mitarbeitermodel->getPersonAbteilung($mitarbeiter_uid);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($data));
	}

	public function getLeitungOrg($oekurzbz)
	{
		$this->load->model('ressource/Mitarbeiter_model', 'Mitarbeitermodel');

		$result = $this->Mitarbeitermodel->getLeitungOrg($oekurzbz);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($data));
	}

	public function getSemesterStati($prestudent_id)
	{
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$result = $this->PrestudentstatusModel->getAllPrestudentstatiWithStudiensemester($prestudent_id);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

}


