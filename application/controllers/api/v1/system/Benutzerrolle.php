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

class Benutzerrolle extends APIv1_Controller
{
	/**
	 * Benutzerrolle API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model BenutzerrolleModel
		$this->load->model('system/benutzerrolle_model', 'BenutzerrolleModel');
		// Load set the uid of the model to let to check the permissions
		$this->BenutzerrolleModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getBenutzerrolle()
	{
		$benutzerrolleID = $this->get('benutzerrolle_id');
		
		if(isset($benutzerrolleID))
		{
			$result = $this->BenutzerrolleModel->load($benutzerrolleID);
			
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
	public function postBenutzerrolle()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['benutzerrolle_id']))
			{
				$result = $this->BenutzerrolleModel->update($this->post()['benutzerrolle_id'], $this->post());
			}
			else
			{
				$result = $this->BenutzerrolleModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($benutzerrolle = NULL)
	{
		return true;
	}
}