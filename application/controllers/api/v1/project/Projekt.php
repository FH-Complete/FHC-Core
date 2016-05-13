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

class Projekt extends APIv1_Controller
{
	/**
	 * Projekt API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model ProjektModel
		$this->load->model('project/projekt_model', 'ProjektModel');
		// Load set the uid of the model to let to check the permissions
		$this->ProjektModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getProjekt()
	{
		$projektID = $this->get('projekt_id');
		
		if(isset($projektID))
		{
			$result = $this->ProjektModel->load($projektID);
			
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
	public function postProjekt()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['projekt_id']))
			{
				$result = $this->ProjektModel->update($this->post()['projekt_id'], $this->post());
			}
			else
			{
				$result = $this->ProjektModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($projekt = NULL)
	{
		return true;
	}
}