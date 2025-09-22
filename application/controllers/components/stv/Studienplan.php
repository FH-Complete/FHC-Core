<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Studienplan extends Auth_Controller
{
	public function __construct()
	{
		// TODO(chris): access!
		parent::__construct([
			'get' => self::PERM_LOGGED
		]);
	}

	public function get()
	{
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');

		$_POST = json_decode($this->input->raw_input_stream, true);
		
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('studiengang_kz', 'StudiengangKz', 'required|numeric');
		$this->form_validation->set_rules('studiensemester_kurzbz', 'StudiensemesterKurbz', 'required');
		$this->form_validation->set_rules('ausbildungssemester', 'Ausbildungssemester', 'numeric');

		if ($this->form_validation->run() == false) {
			$this->output->set_status_header(REST_Controller::HTTP_BAD_REQUEST);
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$studiengang_kz = $this->input->post('studiengang_kz');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		$ausbildungssemester = $this->input->post('ausbildungssemester') ?: null;
		$orgform_kurzbz = $this->input->post('orgform_kurzbz') ?: null;

		$result = $this->StudienplanModel->getStudienplaeneBySemester($studiengang_kz, $studiensemester_kurzbz, $ausbildungssemester, $orgform_kurzbz);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}
}
