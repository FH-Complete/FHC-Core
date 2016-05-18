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

class Aktivitaet extends APIv1_Controller
{
	/**
	 * Aktivitaet API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model AktivitaetModel
		$this->load->model('project/aktivitaet_model', 'AktivitaetModel');
		// Load set the uid of the model to let to check the permissions
		$this->AktivitaetModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getAktivitaet()
	{
		$aktivitaetID = $this->get('aktivitaet_id');
		
		if(isset($aktivitaetID))
		{
			$result = $this->AktivitaetModel->load($aktivitaetID);
			
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
	public function postAktivitaet()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['aktivitaet_id']))
			{
				$result = $this->AktivitaetModel->update($this->post()['aktivitaet_id'], $this->post());
			}
			else
			{
				$result = $this->AktivitaetModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($aktivitaet = NULL)
	{
		return true;
	}
}