<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Zeitsperren extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getZeitsperrenUser' => self::PERM_LOGGED,
			'getTypenZeitsperren' => self::PERM_LOGGED,
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');

		// Load language phrases
		$this->loadPhrases([
			'ui',
			'person'
		]);

		// Load models
		$this->load->model('ressource/Zeitsperre_model', 'ZeitsperreModel');
		$this->load->model('ressource/Zeitsperretyp_model', 'ZeitsperretypModel');
	}

	public function getZeitsperrenUser($uid)
	{
		$result = $this->ZeitsperreModel->getZeitsperrenUser($uid);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function getTypenZeitsperren()
	{
		$this->ZeitsperretypModel->addOrder('beschreibung', 'ASC');
		$result = $this->ZeitsperretypModel->load();
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess((getData($result) ?: []));
	}

}
