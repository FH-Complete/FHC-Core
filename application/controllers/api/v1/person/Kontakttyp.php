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

class Kontakttyp extends APIv1_Controller
{
	/**
	 * Kontakttyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model KontakttypModel
		$this->load->model('person/kontakttyp_model', 'KontakttypModel');
		// Load set the uid of the model to let to check the permissions
		$this->KontakttypModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getKontakttyp()
	{
		$kontakttypID = $this->get('kontakttyp_id');
		
		if(isset($kontakttypID))
		{
			$result = $this->KontakttypModel->load($kontakttypID);
			
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
	public function postKontakttyp()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['kontakttyp_id']))
			{
				$result = $this->KontakttypModel->update($this->post()['kontakttyp_id'], $this->post());
			}
			else
			{
				$result = $this->KontakttypModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($kontakttyp = NULL)
	{
		return true;
	}
}