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

class Rechnung extends API_Controller
{
	/**
	 * Rechnung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Rechnung' => 'basis/rechnung:rw'));
		// Load model RechnungModel
		$this->load->model('accounting/rechnung_model', 'RechnungModel');
	}

	/**
	 * @return void
	 */
	public function getRechnung()
	{
		$rechnungID = $this->get('rechnung_id');

		if (isset($rechnungID))
		{
			$result = $this->RechnungModel->load($rechnungID);

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
	public function postRechnung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['rechnung_id']))
			{
				$result = $this->RechnungModel->update($this->post()['rechnung_id'], $this->post());
			}
			else
			{
				$result = $this->RechnungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($rechnung = NULL)
	{
		return true;
	}
}
