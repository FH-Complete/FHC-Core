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

class Benutzergruppe extends APIv1_Controller
{
	/**
	 * Benutzergruppe API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model BenutzergruppeModel
		$this->load->model('person/benutzergruppe_model', 'BenutzergruppeModel');
		// Load set the uid of the model to let to check the permissions
		$this->BenutzergruppeModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getBenutzergruppe()
	{
		$benutzergruppeID = $this->get('benutzergruppe_id');
		
		if(isset($benutzergruppeID))
		{
			$result = $this->BenutzergruppeModel->load($benutzergruppeID);
			
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
	public function postBenutzergruppe()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['benutzergruppe_id']))
			{
				$result = $this->BenutzergruppeModel->update($this->post()['benutzergruppe_id'], $this->post());
			}
			else
			{
				$result = $this->BenutzergruppeModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($benutzergruppe = NULL)
	{
		return true;
	}
}