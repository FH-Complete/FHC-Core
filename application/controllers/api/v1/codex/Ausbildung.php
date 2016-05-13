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

class Ausbildung extends APIv1_Controller
{
	/**
	 * Ausbildung API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model AusbildungModel
		$this->load->model('codex/ausbildung_model', 'AusbildungModel');
		// Load set the uid of the model to let to check the permissions
		$this->AusbildungModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getAusbildung()
	{
		$ausbildungID = $this->get('ausbildung_id');
		
		if(isset($ausbildungID))
		{
			$result = $this->AusbildungModel->load($ausbildungID);
			
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
	public function postAusbildung()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['ausbildung_id']))
			{
				$result = $this->AusbildungModel->update($this->post()['ausbildung_id'], $this->post());
			}
			else
			{
				$result = $this->AusbildungModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($ausbildung = NULL)
	{
		return true;
	}
}