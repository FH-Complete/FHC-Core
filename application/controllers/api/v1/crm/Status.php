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

class Status extends APIv1_Controller
{
	/**
	 * Status API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model StatusModel
		$this->load->model('crm/status_model', 'StatusModel');
		// Load set the uid of the model to let to check the permissions
		$this->StatusModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getStatus()
	{
		$status_kurzbz = $this->get('status_kurzbz');
		
		if (isset($status_kurzbz))
		{
			$result = $this->StatusModel->load($status_kurzbz);
			
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
	public function postStatus()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['status_kurzbz']))
			{
				$result = $this->StatusModel->update($this->post()['status_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->StatusModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($status = NULL)
	{
		return true;
	}
}