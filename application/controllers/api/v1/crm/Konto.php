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

class Konto extends API_Controller
{
	/**
	 * Konto API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Konto' => 'basis/konto:rw'));
		// Load model KontoModel
		$this->load->model('crm/konto_model', 'KontoModel');


	}

	/**
	 * @return void
	 */
	public function getKonto()
	{
		$buchungsnr = $this->get('buchungsnr');

		if (isset($buchungsnr))
		{
			$result = $this->KontoModel->load($buchungsnr);

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
	public function postKonto()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['buchungsnr']))
			{
				$result = $this->KontoModel->update($this->post()['buchungsnr'], $this->post());
			}
			else
			{
				$result = $this->KontoModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($konto = NULL)
	{
		return true;
	}
}
