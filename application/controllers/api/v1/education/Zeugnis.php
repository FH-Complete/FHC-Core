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

class Zeugnis extends APIv1_Controller
{
	/**
	 * Zeugnis API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model ZeugnisModel
		$this->load->model('education/zeugnis', 'ZeugnisModel');
		// Load set the uid of the model to let to check the permissions
		$this->ZeugnisModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getZeugnis()
	{
		$zeugnis_id = $this->get('zeugnis_id');
		
		if(isset($zeugnis_id))
		{
			$result = $this->ZeugnisModel->load($zeugnis_id);
			
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
	public function postZeugnis()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['zeugnis_id']))
			{
				$result = $this->ZeugnisModel->update($this->post()['zeugnis_id'], $this->post());
			}
			else
			{
				$result = $this->ZeugnisModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($zeugnis = NULL)
	{
		return true;
	}
}