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

class Semesterwochen extends APIv1_Controller
{
	/**
	 * Semesterwochen API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model SemesterwochenModel
		$this->load->model('organisation/semesterwochen_model', 'SemesterwochenModel');
		// Load set the uid of the model to let to check the permissions
		$this->SemesterwochenModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getSemesterwochen()
	{
		$semesterwochenID = $this->get('semesterwochen_id');
		
		if(isset($semesterwochenID))
		{
			$result = $this->SemesterwochenModel->load($semesterwochenID);
			
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
	public function postSemesterwochen()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['semesterwochen_id']))
			{
				$result = $this->SemesterwochenModel->update($this->post()['semesterwochen_id'], $this->post());
			}
			else
			{
				$result = $this->SemesterwochenModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($semesterwochen = NULL)
	{
		return true;
	}
}