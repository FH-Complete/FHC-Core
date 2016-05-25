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

class Orgform extends APIv1_Controller
{
	/**
	 * Orgform API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model OrgformModel
		$this->load->model('codex/orgform_model', 'OrgformModel');
		// Load set the uid of the model to let to check the permissions
		$this->OrgformModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getOrgform()
	{
		$orgform_kurzbz = $this->get('orgform_kurzbz');
		
		if (isset($orgform_kurzbz))
		{
			$result = $this->OrgformModel->load($orgform_kurzbz);
			
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
	public function postOrgform()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['orgform_kurzbz']))
			{
				$result = $this->OrgformModel->update($this->post()['orgform_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->OrgformModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
        
	public function getAll()
	{
		$result = $this->OrgformModel->loadWhole();
		
		$this->response($result, REST_Controller::HTTP_OK);
	}
	
	private function _validate($orgform = NULL)
	{
		return true;
	}
}