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

class Moodle extends APIv1_Controller
{
	/**
	 * Moodle API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model MoodleModel
		$this->load->model('education/moodle', 'MoodleModel');
		// Load set the uid of the model to let to check the permissions
		$this->MoodleModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getMoodle()
	{
		$moodle_id = $this->get('moodle_id');
		
		if(isset($moodle_id))
		{
			$result = $this->MoodleModel->load($moodle_id);
			
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
	public function postMoodle()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['moodle_id']))
			{
				$result = $this->MoodleModel->update($this->post()['moodle_id'], $this->post());
			}
			else
			{
				$result = $this->MoodleModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($moodle = NULL)
	{
		return true;
	}
}