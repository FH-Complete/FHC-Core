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

class Zweck extends APIv1_Controller
{
	/**
	 * Zweck API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model ZweckModel
		$this->load->model('codex/zweck_model', 'ZweckModel');
		// Load set the uid of the model to let to check the permissions
		$this->ZweckModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getZweck()
	{
		$zweckID = $this->get('zweck_id');
		
		if(isset($zweckID))
		{
			$result = $this->ZweckModel->load($zweckID);
			
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
	public function postZweck()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['zweck_id']))
			{
				$result = $this->ZweckModel->update($this->post()['zweck_id'], $this->post());
			}
			else
			{
				$result = $this->ZweckModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($zweck = NULL)
	{
		return true;
	}
}