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

class Dokumentprestudent extends APIv1_Controller
{
	/**
	 * Dokumentprestudent API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model DokumentprestudentModel
		$this->load->model('crm/dokumentprestudent_model', 'DokumentprestudentModel');
		// Load set the uid of the model to let to check the permissions
		$this->DokumentprestudentModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getDokumentprestudent()
	{
		$dokumentprestudentID = $this->get('dokumentprestudent_id');
		
		if(isset($dokumentprestudentID))
		{
			$result = $this->DokumentprestudentModel->load($dokumentprestudentID);
			
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
	public function postDokumentprestudent()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['dokumentprestudent_id']))
			{
				$result = $this->DokumentprestudentModel->update($this->post()['dokumentprestudent_id'], $this->post());
			}
			else
			{
				$result = $this->DokumentprestudentModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($dokumentprestudent = NULL)
	{
		return true;
	}
}