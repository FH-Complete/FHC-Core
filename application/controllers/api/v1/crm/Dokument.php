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

class Dokument extends APIv1_Controller
{
	/**
	 * Dokument API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model DokumentModel
		$this->load->model('crm/dokument_model', 'DokumentModel');
		// Load set the uid of the model to let to check the permissions
		$this->DokumentModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getDokument()
	{
		$dokumentID = $this->get('dokument_id');
		
		if(isset($dokumentID))
		{
			$result = $this->DokumentModel->load($dokumentID);
			
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
	public function postDokument()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['dokument_id']))
			{
				$result = $this->DokumentModel->update($this->post()['dokument_id'], $this->post());
			}
			else
			{
				$result = $this->DokumentModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($dokument = NULL)
	{
		return true;
	}
}