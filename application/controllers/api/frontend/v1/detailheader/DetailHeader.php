<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class DetailHeader extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getSemesterStati' => ['admin:r', 'assistenz:r'],
		]);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getSemesterStati($prestudent_id)
	{
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$result = $this->PrestudentstatusModel->getAllPrestudentstatiWithStudiensemester($prestudent_id);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

}
