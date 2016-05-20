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

class Bewerbungstermine extends APIv1_Controller
{
	/**
	 * Bewerbungstermine API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model BewerbungstermineModel
		$this->load->model('crm/bewerbungstermine_model', 'BewerbungstermineModel');
		// Load set the uid of the model to let to check the permissions
		$this->BewerbungstermineModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getBewerbungstermine()
	{
		$bewerbungstermineID = $this->get('bewerbungstermine_id');
		
		if (isset($bewerbungstermineID))
		{
			$result = $this->BewerbungstermineModel->load($bewerbungstermineID);
			
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
	public function postBewerbungstermine()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['bewerbungstermine_id']))
			{
				$result = $this->BewerbungstermineModel->update($this->post()['bewerbungstermine_id'], $this->post());
			}
			else
			{
				$result = $this->BewerbungstermineModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($bewerbungstermine = NULL)
	{
		return true;
	}
}