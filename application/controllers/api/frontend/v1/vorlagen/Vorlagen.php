<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Vorlagen extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getVorlagen' => ['admin:r', 'assistenz:r'],
			'getVorlagenByLoggedInUser' => ['admin:r', 'assistenz:r'],
		]);

		//Load Models
		$this->load->model('system/Vorlage_model', 'VorlageModel');

		// Additional Permission Checks
		//TODO(manu) check permissions

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');
		$this->load->library('VorlageLib');

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);
	}

	public function getVorlagen()
	{
		$this->load->model('system/Vorlage_model', 'VorlageModel');

		$this->VorlageModel->addOrder('vorlage_kurzbz', 'ASC');

		$result = $this->VorlageModel->loadWhere(
			array(
				'mimetype' => 'text/html'
			));

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getVorlagenByLoggedInUser()
	{
		//get oe of user
		$uid = getAuthUID();
		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
		$result = $this->BenutzerfunktionModel->getBenutzerfunktionByUid($uid, 'oezuordnung');

		$data = $this->getDataOrTerminateWithError($result);
		$oe_kurzbz = current($data);

		$result = $this->VorlageModel->getAllVorlagenByOe($oe_kurzbz->oe_kurzbz);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

}