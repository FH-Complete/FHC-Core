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

class Contentgruppe extends APIv1_Controller
{
	/**
	 * Contentgruppe API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model ContentgruppeModel
		$this->load->model('content/contentgruppe_model', 'ContentgruppeModel');
		// Load set the uid of the model to let to check the permissions
		$this->ContentgruppeModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getContentgruppe()
	{
		$contentgruppeID = $this->get('contentgruppe_id');
		
		if(isset($contentgruppeID))
		{
			$result = $this->ContentgruppeModel->load($contentgruppeID);
			
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
	public function postContentgruppe()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['contentgruppe_id']))
			{
				$result = $this->ContentgruppeModel->update($this->post()['contentgruppe_id'], $this->post());
			}
			else
			{
				$result = $this->ContentgruppeModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($contentgruppe = NULL)
	{
		return true;
	}
}