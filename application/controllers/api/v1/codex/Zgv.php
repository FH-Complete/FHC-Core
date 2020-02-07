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

class Zgv extends API_Controller
{
	/**
	 * Zgv API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Zgv' => 'basis/zgv:rw'));
		// Load model ZgvModel
		$this->load->model('codex/Zgv_model', 'ZgvModel');
	}

	/**
	 * @return void
	 */
	public function getZgv()
	{
		$zgv_code = $this->get('zgv_code');

		if (isset($zgv_code))
		{
			$result = $this->ZgvModel->load($zgv_code);

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
	public function postZgv()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['zgv_code']))
			{
				$result = $this->ZgvModel->update($this->post()['zgv_code'], $this->post());
			}
			else
			{
				$result = $this->ZgvModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($zgv = NULL)
	{
		return true;
	}
}
