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

class Vertragstyp extends APIv1_Controller
{
	/**
	 * Vertragstyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model VertragstypModel
		$this->load->model('accounting/vertragstyp_model', 'VertragstypModel');
		// Load set the uid of the model to let to check the permissions
		$this->VertragstypModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getVertragstyp()
	{
		$vertragstypID = $this->get('vertragstyp_id');
		
		if(isset($vertragstypID))
		{
			$result = $this->VertragstypModel->load($vertragstypID);
			
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
	public function postVertragstyp()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['vertragstyp_id']))
			{
				$result = $this->VertragstypModel->update($this->post()['vertragstyp_id'], $this->post());
			}
			else
			{
				$result = $this->VertragstypModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($vertragstyp = NULL)
	{
		return true;
	}
}