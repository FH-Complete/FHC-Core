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

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Prestudent extends APIv1_Controller
{
	/**
	 * Prestudent API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PrestudentModel
		$this->load->model('crm/prestudent_model', 'PrestudentModel');
		// Load set the uid of the model to let to check the permissions
		$this->PrestudentModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getPrestudent()
	{
		$prestudentID = $this->get('prestudent_id');
		
		if (isset($prestudentID))
		{
			$result = $this->PrestudentModel->load($prestudentID);
			
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
	public function postPrestudent()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['prestudent_id']))
			{
				$result = $this->PrestudentModel->update($this->post()['prestudent_id'], $this->post());
			}
			else
			{
				$result = $this->PrestudentModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($prestudent = NULL)
	{
		return true;
	}
}