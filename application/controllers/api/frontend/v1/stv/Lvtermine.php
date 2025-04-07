<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Lvtermine extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getStundenplan' => ['admin:r', 'assistenz:r'],
			'getLvsStudent' => ['admin:r', 'assistenz:r'],
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');

		// Load language phrases
		$this->loadPhrases([
			'ui',
		]);

		// Load models
		$this->load->model('ressource/Stundenplan_model', 'StundenplanModel');
	}

	public function getStundenplan($uid)
	{
		//get stundenplan for uid
		$result = $this->StundenplanModel->loadForUid($uid);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);

	}

	public function getLvsStudent($uid)
	{
		$authUID = getAuthUID();
		//$this->terminateWithError('uid' . $uid, self::ERROR_TYPE_GENERAL);
		//$this->terminateWithError('uid' . $authUID, self::ERROR_TYPE_GENERAL);

		$this->load->model('system/Variable_model', 'VariableModel');
		$result = $this->VariableModel->getVariables($authUID);
		$data = $this->getDataOrTerminateWithError($result);

		//$this->terminateWithSuccess(current($data)); // WS2025

		$this->load->model('ressource/Stunde_model', 'StundeModel');
		$this->StundeModel->addOrder('stunde', 'ASC');
		$result = $this->StundeModel->load();
		$dataStunden = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($dataStunden);
	//	$this->terminateWithSuccess(current($data));

		//TODO(manu) check existing apis for liste stundenplan und lvs
		// version for student
		// version for lehrfaecherverteilung




	}
}