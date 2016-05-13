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

class Aufwandstyp extends APIv1_Controller
{
	/**
	 * Aufwandstyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model AufwandstypModel
		$this->load->model('project/aufwandstyp_model', 'AufwandstypModel');
		// Load set the uid of the model to let to check the permissions
		$this->AufwandstypModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getAufwandstyp()
	{
		$aufwandstypID = $this->get('aufwandstyp_id');
		
		if(isset($aufwandstypID))
		{
			$result = $this->AufwandstypModel->load($aufwandstypID);
			
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
	public function postAufwandstyp()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['aufwandstyp_id']))
			{
				$result = $this->AufwandstypModel->update($this->post()['aufwandstyp_id'], $this->post());
			}
			else
			{
				$result = $this->AufwandstypModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($aufwandstyp = NULL)
	{
		return true;
	}
}