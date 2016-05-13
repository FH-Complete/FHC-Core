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

class Aufmerksamdurch extends APIv1_Controller
{
	/**
	 * Aufmerksamdurch API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model AufmerksamdurchModel
		$this->load->model('codex/aufmerksamdurch_model', 'AufmerksamdurchModel');
		// Load set the uid of the model to let to check the permissions
		$this->AufmerksamdurchModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getAufmerksamdurch()
	{
		$aufmerksamdurchID = $this->get('aufmerksamdurch_id');
		
		if(isset($aufmerksamdurchID))
		{
			$result = $this->AufmerksamdurchModel->load($aufmerksamdurchID);
			
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
	public function postAufmerksamdurch()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['aufmerksamdurch_id']))
			{
				$result = $this->AufmerksamdurchModel->update($this->post()['aufmerksamdurch_id'], $this->post());
			}
			else
			{
				$result = $this->AufmerksamdurchModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($aufmerksamdurch = NULL)
	{
		return true;
	}
}