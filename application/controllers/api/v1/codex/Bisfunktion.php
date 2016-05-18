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

class Bisfunktion extends APIv1_Controller
{
	/**
	 * Bisfunktion API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model BisfunktionModel
		$this->load->model('codex/bisfunktion_model', 'BisfunktionModel');
		// Load set the uid of the model to let to check the permissions
		$this->BisfunktionModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getBisfunktion()
	{
		$studiengang_kz = $this->get('studiengang_kz');
		$bisverwendung_id = $this->get('bisverwendung_id');
		
		if(isset($studiengang_kz) && isset($bisverwendung_id))
		{
			$result = $this->BisfunktionModel->load(array($studiengang_kz, $bisverwendung_id));
			
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
	public function postBisfunktion()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['bisfunktion_id']))
			{
				$result = $this->BisfunktionModel->update($this->post()['bisfunktion_id'], $this->post());
			}
			else
			{
				$result = $this->BisfunktionModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($bisfunktion = NULL)
	{
		return true;
	}
}