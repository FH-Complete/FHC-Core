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

class Erreichbarkeit extends APIv1_Controller
{
	/**
	 * Erreichbarkeit API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model ErreichbarkeitModel
		$this->load->model('ressource/erreichbarkeit_model', 'ErreichbarkeitModel');
		// Load set the uid of the model to let to check the permissions
		$this->ErreichbarkeitModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getErreichbarkeit()
	{
		$erreichbarkeitID = $this->get('erreichbarkeit_id');
		
		if(isset($erreichbarkeitID))
		{
			$result = $this->ErreichbarkeitModel->load($erreichbarkeitID);
			
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
	public function postErreichbarkeit()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['erreichbarkeit_id']))
			{
				$result = $this->ErreichbarkeitModel->update($this->post()['erreichbarkeit_id'], $this->post());
			}
			else
			{
				$result = $this->ErreichbarkeitModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($erreichbarkeit = NULL)
	{
		return true;
	}
}