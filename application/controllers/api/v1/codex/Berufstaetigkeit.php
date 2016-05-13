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

class Berufstaetigkeit extends APIv1_Controller
{
	/**
	 * Berufstaetigkeit API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model BerufstaetigkeitModel
		$this->load->model('codex/berufstaetigkeit_model', 'BerufstaetigkeitModel');
		// Load set the uid of the model to let to check the permissions
		$this->BerufstaetigkeitModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getBerufstaetigkeit()
	{
		$berufstaetigkeitID = $this->get('berufstaetigkeit_id');
		
		if(isset($berufstaetigkeitID))
		{
			$result = $this->BerufstaetigkeitModel->load($berufstaetigkeitID);
			
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
	public function postBerufstaetigkeit()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['berufstaetigkeit_id']))
			{
				$result = $this->BerufstaetigkeitModel->update($this->post()['berufstaetigkeit_id'], $this->post());
			}
			else
			{
				$result = $this->BerufstaetigkeitModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($berufstaetigkeit = NULL)
	{
		return true;
	}
}