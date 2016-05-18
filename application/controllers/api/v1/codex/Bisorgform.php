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

class Bisorgform extends APIv1_Controller
{
	/**
	 * Bisorgform API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model BisorgformModel
		$this->load->model('codex/bisorgform_model', 'BisorgformModel');
		// Load set the uid of the model to let to check the permissions
		$this->BisorgformModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getBisorgform()
	{
		$bisorgformID = $this->get('bisorgform_id');
		
		if(isset($bisorgformID))
		{
			$result = $this->BisorgformModel->load($bisorgformID);
			
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
	public function postBisorgform()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['bisorgform_id']))
			{
				$result = $this->BisorgformModel->update($this->post()['bisorgform_id'], $this->post());
			}
			else
			{
				$result = $this->BisorgformModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($bisorgform = NULL)
	{
		return true;
	}
}