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

class Zeugnisnote extends API_Controller
{
	/**
	 * Zeugnisnote API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Zeugnisnote' => 'basis/zeugnisnote:rw'));
		// Load model ZeugnisnoteModel
		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');
	}

	/**
	 * @return void
	 */
	public function getZeugnisnote()
	{
		$studiensemester_kurzbz = $this->get('studiensemester_kurzbz');
		$student_uid = $this->get('student_uid');
		$lehrveranstaltung_id = $this->get('lehrveranstaltung_id');

		if (isset($studiensemester_kurzbz) && isset($student_uid) && isset($lehrveranstaltung_id))
		{
			$result = $this->ZeugnisnoteModel->load(array($studiensemester_kurzbz, $student_uid, $lehrveranstaltung_id));

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
	public function postZeugnisnote()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['studiensemester_kurzbz']) && isset($this->post()['student_uid']) && isset($this->post()['lehrveranstaltung_id']))
			{
				$pksArray = array($this->post()['studiensemester_kurzbz'],
									$this->post()['student_uid'],
									$this->post()['lehrveranstaltung_id']
								);

				$result = $this->ZeugnisnoteModel->update($pksArray, $this->post());
			}
			else
			{
				$result = $this->ZeugnisnoteModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($zeugnisnote = NULL)
	{
		return true;
	}
}
