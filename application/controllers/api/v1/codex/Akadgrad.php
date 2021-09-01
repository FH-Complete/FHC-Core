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

class Akadgrad extends API_Controller
{
	/**
	 * Akadgrad API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Akadgrad' => 'basis/akadgrad:rw'));
		// Load model AkadgradModel
		$this->load->model('codex/akadgrad_model', 'AkadgradModel');
	}

	/**
	 * @return void
	 */
	public function getAkadgrad()
	{
		$akadgradID = $this->get('akadgrad_id');

		if (isset($akadgradID))
		{
			$result = $this->AkadgradModel->load($akadgradID);

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
	public function postAkadgrad()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['akadgrad_id']))
			{
				$result = $this->AkadgradModel->update($this->post()['akadgrad_id'], $this->post());
			}
			else
			{
				$result = $this->AkadgradModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($akadgrad = NULL)
	{
		return true;
	}
}
