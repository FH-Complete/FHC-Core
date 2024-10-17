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

class Student extends API_Controller
{
	/**
	 * Student API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Student' => 'basis/student:rw'));
		// Load model StudentModel
		$this->load->model('crm/student_model', 'StudentModel');


	}

	/**
	 * @return void
	 */
	public function getStudent()
	{
		$studentID = $this->get('student_id');

		if (isset($studentID))
		{
			$result = $this->StudentModel->load($studentID);

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
	public function postStudent()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['student_id']))
			{
				$result = $this->StudentModel->update($this->post()['student_id'], $this->post());
			}
			else
			{
				$result = $this->StudentModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($student = NULL)
	{
		return true;
	}
}
