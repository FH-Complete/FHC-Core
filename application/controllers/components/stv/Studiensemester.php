<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Studiensemester extends FHC_Controller
{
	public function __construct()
	{
		// TODO(chris): access!
		parent::__construct();
	}

	public function index()
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$this->StudiensemesterModel->addOrder('start');

		$result = $this->StudiensemesterModel->load();

		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		} elseif (!hasData($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_NOT_FOUND);
			$this->outputJson('NOT FOUND');
		} else {
			$this->outputJson(getData($result));
		}
	}

	public function set()
	{
		$this->load->library('AuthLib');
		$this->load->library('form_validation');

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$this->form_validation->set_rules('studiensemester', 'Studiensemester', 'required');

		if ($this->form_validation->run() == false) {
			$this->output->set_status_header(HTTP_BAD_REQUEST);
			return $this->outputJson($this->form_validation->error_array());
		}

		$stdsem = $this->input->post('studiensemester');

		$this->load->model('system/Variable_model', 'VariableModel');

		$result = $this->VariableModel->setVariable(getAuthUID(), 'semester_aktuell', $stdsem);

		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}

		$this->outputJsonSuccess(true);
	}
}
