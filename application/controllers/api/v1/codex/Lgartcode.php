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

class Lgartcode extends APIv1_Controller
{
	/**
	 * Lgartcode API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model LgartcodeModel
		$this->load->model('codex/lgartcode_model', 'LgartcodeModel');
		// Load set the uid of the model to let to check the permissions
		$this->LgartcodeModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getLgartcode()
	{
		$lgartcodeID = $this->get('lgartcode_id');
		
		if(isset($lgartcodeID))
		{
			$result = $this->LgartcodeModel->load($lgartcodeID);
			
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
	public function postLgartcode()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['lgartcode_id']))
			{
				$result = $this->LgartcodeModel->update($this->post()['lgartcode_id'], $this->post());
			}
			else
			{
				$result = $this->LgartcodeModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($lgartcode = NULL)
	{
		return true;
	}
}