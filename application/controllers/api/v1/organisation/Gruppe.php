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

class Gruppe extends APIv1_Controller
{
	/**
	 * Gruppe API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model GruppeModel
		$this->load->model('organisation/gruppe_model', 'GruppeModel');
		// Load set the uid of the model to let to check the permissions
		$this->GruppeModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getGruppe()
	{
		$gruppeID = $this->get('gruppe_id');
		
		if(isset($gruppeID))
		{
			$result = $this->GruppeModel->load($gruppeID);
			
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
	public function postGruppe()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['gruppe_id']))
			{
				$result = $this->GruppeModel->update($this->post()['gruppe_id'], $this->post());
			}
			else
			{
				$result = $this->GruppeModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($gruppe = NULL)
	{
		return true;
	}
}