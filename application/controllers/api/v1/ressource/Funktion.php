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

class Funktion extends APIv1_Controller
{
	/**
	 * Funktion API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model FunktionModel
		$this->load->model('ressource/funktion_model', 'FunktionModel');
		// Load set the uid of the model to let to check the permissions
		$this->FunktionModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getFunktion()
	{
		$funktionID = $this->get('funktion_id');
		
		if(isset($funktionID))
		{
			$result = $this->FunktionModel->load($funktionID);
			
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
	public function postFunktion()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['funktion_id']))
			{
				$result = $this->FunktionModel->update($this->post()['funktion_id'], $this->post());
			}
			else
			{
				$result = $this->FunktionModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($funktion = NULL)
	{
		return true;
	}
}