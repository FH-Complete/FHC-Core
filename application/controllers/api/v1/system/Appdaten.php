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

class Appdaten extends API_Controller
{
	/**
	 * Appdaten API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Appdaten' => 'system/appdaten:rw'));
		// Load model AppdatenModel
		$this->load->model('system/Appdaten_model', 'AppdatenModel');
	}

	/**
	 * @return void
	 */
	public function getAppdaten()
	{
		$appdatenID = $this->get('appdaten_id');

		if (isset($appdatenID))
		{
			$result = $this->AppdatenModel->load($appdatenID);

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
	public function postAppdaten()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['appdaten_id']))
			{
				$result = $this->AppdatenModel->update($this->post()['appdaten_id'], $this->post());
			}
			else
			{
				$result = $this->AppdatenModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($appdaten = NULL)
	{
		return true;
	}
}
