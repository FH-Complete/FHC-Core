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

class Studentbeispiel extends API_Controller
{
	/**
	 * Studentbeispiel API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Studentbeispiel' => 'basis/studentbeispiel:rw'));
		// Load model StudentbeispielModel
		$this->load->model('education/Studentbeispiel_model', 'StudentbeispielModel');
	}

	/**
	 * @return void
	 */
	public function getStudentbeispiel()
	{
		$beispiel_id = $this->get('beispiel_id');
		$student_uid = $this->get('student_uid');

		if (isset($beispiel_id) && isset($student_uid))
		{
			$result = $this->StudentbeispielModel->load(array($beispiel_id, $student_uid));

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
	public function postStudentbeispiel()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['beispiel_id']) && isset($this->post()['student_uid']))
			{
				$result = $this->StudentbeispielModel->update(array($this->post()['beispiel_id'], $this->post()['student_uid']), $this->post());
			}
			else
			{
				$result = $this->StudentbeispielModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($studentbeispiel = NULL)
	{
		return true;
	}
}
