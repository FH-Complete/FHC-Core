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

class Zgvgruppe extends APIv1_Controller
{
	/**
	 * Zgvgruppe API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model ZgvgruppeModel
		$this->load->model('codex/zgvgruppe_model', 'ZgvgruppeModel');
		// Load set the uid of the model to let to check the permissions
		$this->ZgvgruppeModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getZgvgruppe()
	{
		$zgvgruppeID = $this->get('zgvgruppe_id');
		
		if(isset($zgvgruppeID))
		{
			$result = $this->ZgvgruppeModel->load($zgvgruppeID);
			
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
	public function postZgvgruppe()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['zgvgruppe_id']))
			{
				$result = $this->ZgvgruppeModel->update($this->post()['zgvgruppe_id'], $this->post());
			}
			else
			{
				$result = $this->ZgvgruppeModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($zgvgruppe = NULL)
	{
		return true;
	}
}