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

class Ort extends APIv1_Controller
{
	/**
	 * Ort API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model OrtModel
		$this->load->model('ressource/ort_model', 'OrtModel');
		// Load set the uid of the model to let to check the permissions
		$this->OrtModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getOrt()
	{
		$ort_kurzbz = $this->get('ort_kurzbz');
		
		if (isset($ort_kurzbz))
		{
			$result = $this->OrtModel->load($ort_kurzbz);
			
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
	public function postOrt()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['ort_kurzbz']))
			{
				$result = $this->OrtModel->update($this->post()['ort_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->OrtModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($ort = NULL)
	{
		return true;
	}
}