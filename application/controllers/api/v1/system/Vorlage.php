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

class Vorlage extends APIv1_Controller
{
	/**
	 * Vorlage API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model VorlageModel
		$this->load->model('system/vorlage_model', 'VorlageModel');
		// Load set the uid of the model to let to check the permissions
		$this->VorlageModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getVorlage()
	{
		$vorlageID = $this->get('vorlage_id');
		
		if(isset($vorlageID))
		{
			$result = $this->VorlageModel->load($vorlageID);
			
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
	public function postVorlage()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['vorlage_id']))
			{
				$result = $this->VorlageModel->update($this->post()['vorlage_id'], $this->post());
			}
			else
			{
				$result = $this->VorlageModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($vorlage = NULL)
	{
		return true;
	}
}