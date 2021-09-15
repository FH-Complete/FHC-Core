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

class Studentlehrverband extends API_Controller
{
	/**
	 * Studentlehrverband API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Studentlehrverband' => 'basis/studentlehrverband:rw'));
		// Load model StudentlehrverbandModel
		$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
	}

	/**
	 * @return void
	 */
	public function getStudentlehrverband()
	{
		$studiensemester_kurzbz = $this->get('studiensemester_kurzbz');
		$student_uid = $this->get('student_uid');

		if (isset($studiensemester_kurzbz) && isset($student_uid))
		{
			$result = $this->StudentlehrverbandModel->load(array($studiensemester_kurzbz, $student_uid));

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
	public function postStudentlehrverband()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['studiensemester_kurzbz']) && isset($this->post()['student_uid']))
			{
				$result = $this->StudentlehrverbandModel->update(array($this->post()['studiensemester_kurzbz'], $this->post()['student_uid']), $this->post());
			}
			else
			{
				$result = $this->StudentlehrverbandModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($studentlehrverband = NULL)
	{
		return true;
	}
}
