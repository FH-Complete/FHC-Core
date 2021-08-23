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

class Rechnungsbetrag extends API_Controller
{
	/**
	 * Rechnungsbetrag API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Rechnungsbetrag' => 'basis/rechnungsbetrag:rw'));
		// Load model RechnungsbetragModel
		$this->load->model('accounting/rechnungsbetrag_model', 'RechnungsbetragModel');
	}

	/**
	 * @return void
	 */
	public function getRechnungsbetrag()
	{
		$rechnungsbetragID = $this->get('rechnungsbetrag_id');

		if (isset($rechnungsbetragID))
		{
			$result = $this->RechnungsbetragModel->load($rechnungsbetragID);

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
	public function postRechnungsbetrag()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['rechnungsbetrag_id']))
			{
				$result = $this->RechnungsbetragModel->update($this->post()['rechnungsbetrag_id'], $this->post());
			}
			else
			{
				$result = $this->RechnungsbetragModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($rechnungsbetrag = NULL)
	{
		return true;
	}
}
