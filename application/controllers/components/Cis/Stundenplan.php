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
			'index' => ['student/anrechnung_beantragen:r','user:r'], // TODO(chris): permissions?
			'Reservierungen' => ['student/anrechnung_beantragen:r','user:r'], // TODO(chris): permissions?
			'Stunden' => ['student/anrechnung_beantragen:r','user:r'] // TODO(chris): permissions?
		]);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 */
	public function index()
	{
		$this->load->model('ressource/Stundenplan_model', 'StundenplanModel');

		$result = $this->StundenplanModel->loadForUid(get_uid());

		if (isError($result))
			return $this->outputJsonError(getError($result));

		$this->outputJsonSuccess(getData($result));
	}

	/**
	 */
	public function Reservierungen()
	{
		$this->load->model('ressource/Reservierung_model', 'ReservierungModel');

		$result = $this->ReservierungModel->loadForUid(get_uid());

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
