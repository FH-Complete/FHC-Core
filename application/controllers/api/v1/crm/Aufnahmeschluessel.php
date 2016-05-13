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

class Aufnahmeschluessel extends APIv1_Controller
{
	/**
	 * Aufnahmeschluessel API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model AufnahmeschluesselModel
		$this->load->model('crm/aufnahmeschluessel_model', 'AufnahmeschluesselModel');
		// Load set the uid of the model to let to check the permissions
		$this->AufnahmeschluesselModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getAufnahmeschluessel()
	{
		$aufnahmeschluesselID = $this->get('aufnahmeschluessel_id');
		
		if(isset($aufnahmeschluesselID))
		{
			$result = $this->AufnahmeschluesselModel->load($aufnahmeschluesselID);
			
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
	public function postAufnahmeschluessel()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['aufnahmeschluessel_id']))
			{
				$result = $this->AufnahmeschluesselModel->update($this->post()['aufnahmeschluessel_id'], $this->post());
			}
			else
			{
				$result = $this->AufnahmeschluesselModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($aufnahmeschluessel = NULL)
	{
		return true;
	}
}