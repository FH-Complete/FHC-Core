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

class Statistik extends APIv1_Controller
{
	/**
	 * Statistik API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model StatistikModel
		$this->load->model('organisation/statistik_model', 'StatistikModel');
		// Load set the uid of the model to let to check the permissions
		$this->StatistikModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getStatistik()
	{
		$statistikID = $this->get('statistik_id');
		
		if(isset($statistikID))
		{
			$result = $this->StatistikModel->load($statistikID);
			
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
	public function postStatistik()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['statistik_id']))
			{
				$result = $this->StatistikModel->update($this->post()['statistik_id'], $this->post());
			}
			else
			{
				$result = $this->StatistikModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($statistik = NULL)
	{
		return true;
	}
}