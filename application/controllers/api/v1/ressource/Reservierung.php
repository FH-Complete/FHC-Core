<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Reservierung extends API_Controller
{
	/**
	 * Reservierung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Reservierung' => 'basis/reservierung:rw'));
		// Load model ReservierungModel
		$this->load->model('ressource/reservierung_model', 'ReservierungModel');


	}

	/**
	 * @return void
	 */
	public function getReservierung()
	{
		$reservierungID = $this->get('reservierung_id');

		if (isset($reservierungID))
		{
			$result = $this->ReservierungModel->load($reservierungID);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * @return void
	 */
	public function postReservierung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['reservierung_id']))
			{
				$result = $this->ReservierungModel->update($this->post()['reservierung_id'], $this->post());
			}
			else
			{
				$result = $this->ReservierungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($reservierung = NULL)
	{
		return true;
	}
}
