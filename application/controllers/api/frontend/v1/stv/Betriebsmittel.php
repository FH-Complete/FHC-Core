<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Betriebsmittel extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getAllBetriebsmittel' => ['admin:r', 'assistenz:r'],
			'addNewBetriebsmittel' => ['admin:r', 'assistenz:r'],
			'updateBetriebsmittel' => ['admin:r', 'assistenz:r'],
			'loadBetriebsmittel' => ['admin:r', 'assistenz:r'],
			'getTypenBetriebsmittel' => ['admin:r', 'assistenz:r']
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);
	}

	public function getAllBetriebsmittel($uid, $person_id)
	{
		$this->load->model('ressource/Betriebsmittelperson_model', 'BetriebsmittelpersonModel');

		//uid
		//$result = $this->BetriebsmittelpersonModel->getBetriebsmittelByUid($uid);

		//person_id
		$result = $this->BetriebsmittelpersonModel->getBetriebsmittel($person_id);

		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function loadBetriebsmittel()
	{
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$betriebsmittel_id = $this->input->post('betriebsmittel_id');

		$this->load->model('ressource/Betriebsmittel_model', 'BetriebsmittelModel');


		$result = $this->BetriebsmittelModel->loadWhere(
			array(
				'betriebsmittel_id' => $betriebsmittel_id
			)
		);
		if (isError($result))
		{
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		elseif (!hasData($result))
		{
			$this->terminateWithError("no Betriebsmittel with ID found: " . $betriebsmittel_id, self::ERROR_TYPE_GENERAL);
		}

	//	var_dump($result);

		$this->terminateWithSuccess(getData($result) ? : []);

	}

	public function getTypenBetriebsmittel()
	{
		$this->load->model('ressource/Betriebsmitteltyp_model', 'BetriebsmitteltypModel');

		$result = $this->BetriebsmitteltypModel->load();
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

}


