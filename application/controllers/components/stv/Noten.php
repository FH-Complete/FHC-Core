<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Noten extends Auth_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getZeugnis' => 'student/noten:r'
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
	}

	public function getZeugnis($prestudent_id)
	{
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');

		$result = $this->StudentModel->loadWhere([
			'prestudent_id' => $prestudent_id
		]);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}
		if (!hasData($result))
			return $this->outputJsonSuccess(null);
		
		$student_uid = current(getData($result))->student_uid;

		$studiensemester_kurzbz = $this->variablelib->getVar('semester_aktuell');

		$result = $this->ZeugnisnoteModel->getZeugnisnoten($student_uid, $studiensemester_kurzbz);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		
		return $this->outputJson($result);
	}
}
