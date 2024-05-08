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

class Semesterwochen extends API_Controller
{
	/**
	 * Semesterwochen API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Semesterwochen' => 'basis/semesterwochen:rw'));
		// Load model SemesterwochenModel
		$this->load->model('organisation/semesterwochen_model', 'SemesterwochenModel');


	}

	/**
	 * @return void
	 */
	public function getSemesterwochen()
	{
		$studiengang_kz = $this->get('studiengang_kz');
		$semester = $this->get('semester');

		if (isset($studiengang_kz) && isset($semester))
		{
			$result = $this->SemesterwochenModel->load(array($studiengang_kz, $semester));

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
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['studiengang_kz']) && isset($this->post()['semester']))
			{
				$result = $this->SemesterwochenModel->update(array($this->post()['studiengang_kz'], $this->post()['semester']), $this->post());
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
