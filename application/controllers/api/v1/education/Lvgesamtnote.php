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

class Lvgesamtnote extends API_Controller
{
	/**
	 * Lvgesamtnote API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Lvgesamtnote' => 'basis/lvgesamtnote:rw'));
		// Load model LvgesamtnoteModel
		$this->load->model('education/Lvgesamtnote_model', 'LvgesamtnoteModel');
	}

	/**
	 * @return void
	 */
	public function getLvgesamtnote()
	{
		$student_uid = $this->get('student_uid');
		$studiensemester_kurzbz = $this->get('studiensemester_kurzbz');
		$lehrveranstaltung_id = $this->get('lehrveranstaltung_id');

		if (isset($student_uid) && isset($studiensemester_kurzbz) && isset($lehrveranstaltung_id))
		{
			$result = $this->LvgesamtnoteModel->load(array($student_uid, $studiensemester_kurzbz, $lehrveranstaltung_id));

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
	public function postLvgesamtnote()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['student_uid']) && isset($this->post()['studiensemester_kurzbz']) && isset($this->post()['lehrveranstaltung_id']))
			{
				$pksArray = array($this->post()['student_uid'],
									$this->post()['studiensemester_kurzbz'],
									$this->post()['lehrveranstaltung_id']
								);

				$result = $this->LvgesamtnoteModel->update($pksArray, $this->post());
			}
			else
			{
				$result = $this->LvgesamtnoteModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($lvgesamtnote = NULL)
	{
		return true;
	}
}
