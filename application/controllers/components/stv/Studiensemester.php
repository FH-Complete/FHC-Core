<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Studiensemester extends Auth_Controller
{
	public function __construct()
	{
		// TODO(chris): access!
		parent::__construct([
			'index' => self::PERM_LOGGED,
			'now' => self::PERM_LOGGED,
			'set' => self::PERM_LOGGED
		]);
	}

	public function index()
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$this->StudiensemesterModel->addOrder('start');

		$result = $this->StudiensemesterModel->load();

		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function now()
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$result = $this->StudiensemesterModel->getNearest();

		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		}
		$result = getData($result) ?: [];

		if (count($result) != 1) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJsonError(count($result) ? 'Mehrere Studiensemester aktiv' : 'Kein Studiensemester aktiv');
		} else {
			$this->outputJsonSuccess(current($result)->studiensemester_kurzbz);
		}
	}

	public function set()
	{
		$this->load->library('AuthLib');
		$this->load->library('form_validation');

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$this->form_validation->set_rules('studiensemester', 'Studiensemester', 'required');

		if ($this->form_validation->run() == false) {
			$this->output->set_status_header(REST_Controller::HTTP_BAD_REQUEST);
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$stdsem = $this->input->post('studiensemester');

		$this->load->model('system/Variable_model', 'VariableModel');

		$result = $this->VariableModel->setVariable(getAuthUID(), 'semester_aktuell', $stdsem);

		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}

		$this->outputJsonSuccess(true);
	}
}
