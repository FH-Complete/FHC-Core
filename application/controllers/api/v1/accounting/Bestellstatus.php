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

class Bestellstatus extends APIv1_Controller
{
	/**
	 * Bestellstatus API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model BestellstatusModel
		$this->load->model('accounting/bestellstatus_model', 'BestellstatusModel');
		// Load set the uid of the model to let to check the permissions
		$this->BestellstatusModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getBestellstatus()
	{
		$bestellstatusID = $this->get('bestellstatus_id');
		
		if(isset($bestellstatusID))
		{
			$result = $this->BestellstatusModel->load($bestellstatusID);
			
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
	public function postBestellstatus()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['bestellstatus_id']))
			{
				$result = $this->BestellstatusModel->update($this->post()['bestellstatus_id'], $this->post());
			}
			else
			{
				$result = $this->BestellstatusModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($bestellstatus = NULL)
	{
		return true;
	}
}