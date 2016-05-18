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

class Berechtigung extends APIv1_Controller
{
	/**
	 * Berechtigung API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model BerechtigungModel
		$this->load->model('system/berechtigung_model', 'BerechtigungModel');
		// Load set the uid of the model to let to check the permissions
		$this->BerechtigungModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getBerechtigung()
	{
		$berechtigungID = $this->get('berechtigung_id');
		
		if(isset($berechtigungID))
		{
			$result = $this->BerechtigungModel->load($berechtigungID);
			
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
	public function postBerechtigung()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['berechtigung_id']))
			{
				$result = $this->BerechtigungModel->update($this->post()['berechtigung_id'], $this->post());
			}
			else
			{
				$result = $this->BerechtigungModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($berechtigung = NULL)
	{
		return true;
	}
}