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

class Stunde extends APIv1_Controller
{
	/**
	 * Stunde API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model StundeModel
		$this->load->model('ressource/stunde_model', 'StundeModel');
		// Load set the uid of the model to let to check the permissions
		$this->StundeModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getStunde()
	{
		$stundeID = $this->get('stunde_id');
		
		if(isset($stundeID))
		{
			$result = $this->StundeModel->load($stundeID);
			
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
	public function postStunde()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['stunde_id']))
			{
				$result = $this->StundeModel->update($this->post()['stunde_id'], $this->post());
			}
			else
			{
				$result = $this->StundeModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($stunde = NULL)
	{
		return true;
	}
}