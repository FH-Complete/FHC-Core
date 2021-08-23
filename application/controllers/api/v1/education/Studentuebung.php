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

class Studentuebung extends API_Controller
{
	/**
	 * Studentuebung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Studentuebung' => 'basis/studentuebung:rw'));
		// Load model StudentuebungModel
		$this->load->model('education/Studentuebung_model', 'StudentuebungModel');
	}

	/**
	 * @return void
	 */
	public function getStudentuebung()
	{
		$uebung_id = $this->get('uebung_id');
		$student_uid = $this->get('student_uid');

		if (isset($uebung_id) && isset($student_uid))
		{
			$result = $this->StudentuebungModel->load(array($uebung_id, $student_uid));

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
	public function postStudentuebung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['uebung_id']) && isset($this->post()['student_uid']))
			{
				$result = $this->StudentuebungModel->update(array($this->post()['uebung_id'], $this->post()['student_uid']), $this->post());
			}
			else
			{
				$result = $this->StudentuebungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($studentuebung = NULL)
	{
		return true;
	}
}
