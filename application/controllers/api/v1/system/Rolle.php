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

if(!defined('BASEPATH')) exit('No direct script access allowed');

class Rolle extends APIv1_Controller
{
	/**
	 * Rolle API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model RolleModel
		$this->load->model('system/rolle_model', 'RolleModel');
		// Load set the uid of the model to let to check the permissions
		$this->RolleModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getRolle()
	{
		$rolleID = $this->get('rolle_id');
		
		if(isset($rolleID))
		{
			$result = $this->RolleModel->load($rolleID);
			
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
	public function postRolle()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['rolle_id']))
			{
				$result = $this->RolleModel->update($this->post()['rolle_id'], $this->post());
			}
			else
			{
				$result = $this->RolleModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($rolle = NULL)
	{
		return true;
	}
}