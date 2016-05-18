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

class Resturlaub extends APIv1_Controller
{
	/**
	 * Resturlaub API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model ResturlaubModel
		$this->load->model('ressource/resturlaub_model', 'ResturlaubModel');
		// Load set the uid of the model to let to check the permissions
		$this->ResturlaubModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getResturlaub()
	{
		$resturlaubID = $this->get('resturlaub_id');
		
		if(isset($resturlaubID))
		{
			$result = $this->ResturlaubModel->load($resturlaubID);
			
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
	public function postResturlaub()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['resturlaub_id']))
			{
				$result = $this->ResturlaubModel->update($this->post()['resturlaub_id'], $this->post());
			}
			else
			{
				$result = $this->ResturlaubModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($resturlaub = NULL)
	{
		return true;
	}
}