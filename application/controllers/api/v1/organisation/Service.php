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

class Service extends API_Controller
{
	/**
	 * Service API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Service' => 'basis/service:rw'));
		// Load model ServiceModel
		$this->load->model('organisation/service_model', 'ServiceModel');


	}

	/**
	 * @return void
	 */
	public function getService()
	{
		$serviceID = $this->get('service_id');

		if (isset($serviceID))
		{
			$result = $this->ServiceModel->load($serviceID);

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
	public function postService()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['service_id']))
			{
				$result = $this->ServiceModel->update($this->post()['service_id'], $this->post());
			}
			else
			{
				$result = $this->ServiceModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($service = NULL)
	{
		return true;
	}
}
