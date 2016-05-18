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

class Mobilitaetsprogramm extends APIv1_Controller
{
	/**
	 * Mobilitaetsprogramm API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model MobilitaetsprogrammModel
		$this->load->model('codex/mobilitaetsprogramm_model', 'MobilitaetsprogrammModel');
		// Load set the uid of the model to let to check the permissions
		$this->MobilitaetsprogrammModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getMobilitaetsprogramm()
	{
		$mobilitaetsprogrammID = $this->get('mobilitaetsprogramm_id');
		
		if(isset($mobilitaetsprogrammID))
		{
			$result = $this->MobilitaetsprogrammModel->load($mobilitaetsprogrammID);
			
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
	public function postMobilitaetsprogramm()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['mobilitaetsprogramm_id']))
			{
				$result = $this->MobilitaetsprogrammModel->update($this->post()['mobilitaetsprogramm_id'], $this->post());
			}
			else
			{
				$result = $this->MobilitaetsprogrammModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($mobilitaetsprogramm = NULL)
	{
		return true;
	}
}