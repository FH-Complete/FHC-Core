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

class Webservicelog extends API_Controller
{
	/**
	 * Webservicelog API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Webservicelog' => 'basis/webservicelog:rw'));
		// Load model WebservicelogModel
		$this->load->model('system/webservicelog_model', 'WebservicelogModel');


	}

	/**
	 * @return void
	 */
	public function getWebservicelog()
	{
		$webservicelogID = $this->get('webservicelog_id');

		if (isset($webservicelogID))
		{
			$result = $this->WebservicelogModel->load($webservicelogID);

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
	public function postWebservicelog()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['webservicelog_id']))
			{
				$result = $this->WebservicelogModel->update($this->post()['webservicelog_id'], $this->post());
			}
			else
			{
				$result = $this->WebservicelogModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($webservicelog = NULL)
	{
		return true;
	}
}
