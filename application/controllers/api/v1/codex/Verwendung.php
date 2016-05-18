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

class Verwendung extends APIv1_Controller
{
	/**
	 * Verwendung API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model VerwendungModel
		$this->load->model('codex/verwendung_model', 'VerwendungModel');
		// Load set the uid of the model to let to check the permissions
		$this->VerwendungModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getVerwendung()
	{
		$verwendungID = $this->get('verwendung_id');
		
		if(isset($verwendungID))
		{
			$result = $this->VerwendungModel->load($verwendungID);
			
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
	public function postVerwendung()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['verwendung_id']))
			{
				$result = $this->VerwendungModel->update($this->post()['verwendung_id'], $this->post());
			}
			else
			{
				$result = $this->VerwendungModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($verwendung = NULL)
	{
		return true;
	}
}