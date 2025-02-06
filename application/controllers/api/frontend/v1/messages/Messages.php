<?php


if (!defined('BASEPATH')) exit('No direct script access allowed');

class Messages extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getMessages' => ['admin:r', 'assistenz:r'],
		]);

		//Load Models
		$this->load->model('system/Message_model', 'MessageModel');

		// Additional Permission Checks
		//TODO(manu) check permissions

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);
	}

	public function getMessages($id, $type_id)
	{
		//$this->terminateWithError("in backend " . $type_id . ": " . $id, self::ERROR_TYPE_GENERAL);

		if ($type_id != "person_id")
		{
			$this->terminateWithError("logic for type_id " . $type_id . " not defined yet", self::ERROR_TYPE_GENERAL);
		}

		$result = $this->MessageModel->getMessagesOfPerson($id);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}
}