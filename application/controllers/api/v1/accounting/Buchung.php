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

class Buchung extends API_Controller
{
	/**
	 * Buchung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Buchung' => 'basis/buchung:r'));
		// Load model BuchungModel
		$this->load->model('accounting/buchung_model', 'BuchungModel');
	}

	/**
	 * @return void
	 */
	public function getBuchung()
	{
		$buchungID = $this->get('buchung_id');

		if (isset($buchungID))
		{
			$result = $this->BuchungModel->load($buchungID);

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
	public function postBuchung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['buchung_id']))
			{
				$result = $this->BuchungModel->update($this->post()['buchung_id'], $this->post());
			}
			else
			{
				$result = $this->BuchungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($buchung = NULL)
	{
		return true;
	}
}
