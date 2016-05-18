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

class Lehrform extends APIv1_Controller
{
	/**
	 * Lehrform API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model LehrformModel
		$this->load->model('codex/lehrform_model', 'LehrformModel');
		// Load set the uid of the model to let to check the permissions
		$this->LehrformModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getLehrform()
	{
		$lehrformID = $this->get('lehrform_id');
		
		if(isset($lehrformID))
		{
			$result = $this->LehrformModel->load($lehrformID);
			
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
	public function postLehrform()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['lehrform_id']))
			{
				$result = $this->LehrformModel->update($this->post()['lehrform_id'], $this->post());
			}
			else
			{
				$result = $this->LehrformModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($lehrform = NULL)
	{
		return true;
	}
}