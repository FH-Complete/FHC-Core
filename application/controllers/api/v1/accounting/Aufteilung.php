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

class Aufteilung extends APIv1_Controller
{
	/**
	 * Aufteilung API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model AufteilungModel
		$this->load->model('accounting/aufteilung_model', 'AufteilungModel');
		// Load set the uid of the model to let to check the permissions
		$this->AufteilungModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getAufteilung()
	{
		$aufteilungID = $this->get('aufteilung_id');
		
		if(isset($aufteilungID))
		{
			$result = $this->AufteilungModel->load($aufteilungID);
			
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
	public function postAufteilung()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['aufteilung_id']))
			{
				$result = $this->AufteilungModel->update($this->post()['aufteilung_id'], $this->post());
			}
			else
			{
				$result = $this->AufteilungModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($aufteilung = NULL)
	{
		return true;
	}
}