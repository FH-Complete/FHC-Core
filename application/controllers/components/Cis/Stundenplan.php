<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 */
class Stundenplan extends Auth_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'index' => ['basis/cis'],
			'Reservierungen' => ['basis/cis'],
			'Stunden' => ['basis/cis'],
		]);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 */
	public function index()
	{
		$this->load->model('ressource/Stundenplan_model', 'StundenplanModel');

		/* $result = $this->StundenplanModel->loadForUid(getAuthUID());

		if (isError($result))
			return $this->outputJsonError(getError($result));
 		*/
		$res = $this->StundenplanModel->stundenplanGruppierung($this->StundenplanModel->getStundenplanQuery(getAuthUID())); 
		
		$res = getData($res);
		
		$this->outputJsonSuccess($res);
	}

	/**
	 */
	public function Reservierungen()
	{
		$this->load->model('ressource/Reservierung_model', 'ReservierungModel');

		$result = $this->ReservierungModel->loadForUid(getAuthUID());

		if (isError($result))
			return $this->outputJsonError(getError($result));

		$this->outputJsonSuccess(getData($result));
	}

	/**
	 */
	public function Stunden()
	{
		$this->load->model('ressource/Stunde_model', 'StundeModel');

		$result = $this->StundeModel->load();

		if (isError($result))
			return $this->outputJsonError(getError($result));

		$this->outputJsonSuccess(getData($result));
	}


}
