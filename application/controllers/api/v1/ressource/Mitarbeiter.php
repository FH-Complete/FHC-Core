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

class Mitarbeiter extends APIv1_Controller
{
	/**
	 * Mitarbeiter API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model MitarbeiterModel
		$this->load->model('ressource/mitarbeiter_model', 'MitarbeiterModel');
		// Load set the uid of the model to let to check the permissions
		$this->MitarbeiterModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getMitarbeiter()
	{
		$mitarbeiterID = $this->get('mitarbeiter_id');
		
		if(isset($mitarbeiterID))
		{
			$result = $this->MitarbeiterModel->load($mitarbeiterID);
			
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
	public function postMitarbeiter()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['mitarbeiter_id']))
			{
				$result = $this->MitarbeiterModel->update($this->post()['mitarbeiter_id'], $this->post());
			}
			else
			{
				$result = $this->MitarbeiterModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($mitarbeiter = NULL)
	{
		return true;
	}
}