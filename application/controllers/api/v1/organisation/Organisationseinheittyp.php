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

class Organisationseinheittyp extends APIv1_Controller
{
	/**
	 * Organisationseinheittyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model OrganisationseinheittypModel
		$this->load->model('organisation/organisationseinheittyp_model', 'OrganisationseinheittypModel');
		// Load set the uid of the model to let to check the permissions
		$this->OrganisationseinheittypModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getOrganisationseinheittyp()
	{
		$organisationseinheittypID = $this->get('organisationseinheittyp_id');
		
		if(isset($organisationseinheittypID))
		{
			$result = $this->OrganisationseinheittypModel->load($organisationseinheittypID);
			
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
	public function postOrganisationseinheittyp()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['organisationseinheittyp_id']))
			{
				$result = $this->OrganisationseinheittypModel->update($this->post()['organisationseinheittyp_id'], $this->post());
			}
			else
			{
				$result = $this->OrganisationseinheittypModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($organisationseinheittyp = NULL)
	{
		return true;
	}
}