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

class Dokumentstudiengang extends APIv1_Controller
{
	/**
	 * Dokumentstudiengang API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model DokumentstudiengangModel
		$this->load->model('crm/dokumentstudiengang_model', 'DokumentstudiengangModel');
		// Load set the uid of the model to let to check the permissions
		$this->DokumentstudiengangModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getDokumentstudiengang()
	{
		$dokumentstudiengangID = $this->get('dokumentstudiengang_id');
		
		if(isset($dokumentstudiengangID))
		{
			$result = $this->DokumentstudiengangModel->load($dokumentstudiengangID);
			
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
	public function postDokumentstudiengang()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['dokumentstudiengang_id']))
			{
				$result = $this->DokumentstudiengangModel->update($this->post()['dokumentstudiengang_id'], $this->post());
			}
			else
			{
				$result = $this->DokumentstudiengangModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($dokumentstudiengang = NULL)
	{
		return true;
	}
}