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

class Erhalter extends APIv1_Controller
{
	/**
	 * Erhalter API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model ErhalterModel
		$this->load->model('organisation/erhalter_model', 'ErhalterModel');
		// Load set the uid of the model to let to check the permissions
		$this->ErhalterModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getErhalter()
	{
		$erhalterID = $this->get('erhalter_id');
		
		if(isset($erhalterID))
		{
			$result = $this->ErhalterModel->load($erhalterID);
			
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
	public function postErhalter()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['erhalter_id']))
			{
				$result = $this->ErhalterModel->update($this->post()['erhalter_id'], $this->post());
			}
			else
			{
				$result = $this->ErhalterModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($erhalter = NULL)
	{
		return true;
	}
}