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

class Ort extends APIv1_Controller
{
	/**
	 * Ort API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model OrtModel
		$this->load->model('ressource/ort_model', 'OrtModel');
		// Load set the uid of the model to let to check the permissions
		$this->OrtModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getOrt()
	{
		$ortID = $this->get('ort_id');
		
		if(isset($ortID))
		{
			$result = $this->OrtModel->load($ortID);
			
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
	public function postOrt()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['ort_id']))
			{
				$result = $this->OrtModel->update($this->post()['ort_id'], $this->post());
			}
			else
			{
				$result = $this->OrtModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($ort = NULL)
	{
		return true;
	}
}