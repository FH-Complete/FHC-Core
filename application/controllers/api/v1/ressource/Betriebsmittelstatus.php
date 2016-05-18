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

class Betriebsmittelstatus extends APIv1_Controller
{
	/**
	 * Betriebsmittelstatus API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model BetriebsmittelstatusModel
		$this->load->model('ressource/betriebsmittelstatus_model', 'BetriebsmittelstatusModel');
		// Load set the uid of the model to let to check the permissions
		$this->BetriebsmittelstatusModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getBetriebsmittelstatus()
	{
		$betriebsmittelstatusID = $this->get('betriebsmittelstatus_id');
		
		if(isset($betriebsmittelstatusID))
		{
			$result = $this->BetriebsmittelstatusModel->load($betriebsmittelstatusID);
			
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
	public function postBetriebsmittelstatus()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['betriebsmittelstatus_id']))
			{
				$result = $this->BetriebsmittelstatusModel->update($this->post()['betriebsmittelstatus_id'], $this->post());
			}
			else
			{
				$result = $this->BetriebsmittelstatusModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($betriebsmittelstatus = NULL)
	{
		return true;
	}
}