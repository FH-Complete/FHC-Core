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

class Gemeinde extends APIv1_Controller
{
	/**
	 * Gemeinde API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model GemeindeModel
		$this->load->model('codex/gemeinde_model', 'GemeindeModel');
		// Load set the uid of the model to let to check the permissions
		$this->GemeindeModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getGemeinde()
	{
		$gemeindeID = $this->get('gemeinde_id');
		
		if(isset($gemeindeID))
		{
			$result = $this->GemeindeModel->load($gemeindeID);
			
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
	public function postGemeinde()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['gemeinde_id']))
			{
				$result = $this->GemeindeModel->update($this->post()['gemeinde_id'], $this->post());
			}
			else
			{
				$result = $this->GemeindeModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($gemeinde = NULL)
	{
		return true;
	}
}