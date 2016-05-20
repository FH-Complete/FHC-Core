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

class Scrumteam extends APIv1_Controller
{
	/**
	 * Scrumteam API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model ScrumteamModel
		$this->load->model('project/scrumteam_model', 'ScrumteamModel');
		// Load set the uid of the model to let to check the permissions
		$this->ScrumteamModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getScrumteam()
	{
		$scrumteam_kurzbz = $this->get('scrumteam_kurzbz');
		
		if(isset($scrumteam_kurzbz))
		{
			$result = $this->ScrumteamModel->load($scrumteam_kurzbz);
			
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
	public function postScrumteam()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['scrumteam_kurzbz']))
			{
				$result = $this->ScrumteamModel->update($this->post()['scrumteam_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->ScrumteamModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($scrumteam = NULL)
	{
		return true;
	}
}