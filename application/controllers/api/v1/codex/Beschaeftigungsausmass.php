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

class Beschaeftigungsausmass extends API_Controller
{
	/**
	 * Beschaeftigungsausmass API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Beschaeftigungsausmass' => 'basis/beschaeftigungsausmass:rw'));
		// Load model BeschaeftigungsausmassModel
		$this->load->model('codex/beschaeftigungsausmass_model', 'BeschaeftigungsausmassModel');
	}

	/**
	 * @return void
	 */
	public function getBeschaeftigungsausmass()
	{
		$beschausmasscode = $this->get('beschausmasscode');

		if (isset($beschausmasscode))
		{
			$result = $this->BeschaeftigungsausmassModel->load($beschausmasscode);

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
	public function postBeschaeftigungsausmass()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['beschausmasscode']))
			{
				$result = $this->BeschaeftigungsausmassModel->update($this->post()['beschausmasscode'], $this->post());
			}
			else
			{
				$result = $this->BeschaeftigungsausmassModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($beschaeftigungsausmass = NULL)
	{
		return true;
	}
}
