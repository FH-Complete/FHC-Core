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

class Webservicerecht extends API_Controller
{
	/**
	 * Webservicerecht API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Webservicerecht' => 'basis/webservicerecht:rw'));
		// Load model WebservicerechtModel
		$this->load->model('system/webservicerecht_model', 'WebservicerechtModel');


	}

	/**
	 * @return void
	 */
	public function getWebservicerecht()
	{
		$webservicerechtID = $this->get('webservicerecht_id');

		if (isset($webservicerechtID))
		{
			$result = $this->WebservicerechtModel->load($webservicerechtID);

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
	public function postWebservicerecht()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['webservicerecht_id']))
			{
				$result = $this->WebservicerechtModel->update($this->post()['webservicerecht_id'], $this->post());
			}
			else
			{
				$result = $this->WebservicerechtModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($webservicerecht = NULL)
	{
		return true;
	}
}
