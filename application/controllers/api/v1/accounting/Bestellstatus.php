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

class Bestellstatus extends API_Controller
{
	/**
	 * Bestellstatus API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Bestellstatus' => 'basis/bestellstatus:rw'));
		// Load model BestellstatusModel
		$this->load->model('accounting/bestellstatus_model', 'BestellstatusModel');
	}

	/**
	 * @return void
	 */
	public function getBestellstatus()
	{
		$bestellstatus_kurzbz = $this->get('bestellstatus_kurzbz');

		if (isset($bestellstatus_kurzbz))
		{
			$result = $this->BestellstatusModel->load($bestellstatus_kurzbz);

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
	public function postBestellstatus()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['bestellstatus_kurzbz']))
			{
				$result = $this->BestellstatusModel->update($this->post()['bestellstatus_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->BestellstatusModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($bestellstatus = NULL)
	{
		return true;
	}
}
