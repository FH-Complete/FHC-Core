<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Favorites extends FHC_Controller
{
	public function __construct()
	{
		// TODO(chris): access!
		parent::__construct();

		// Load models
		$this->load->model('system/Variable_model', 'VariableModel');

		// Load libraries
		$this->load->library('AuthLib');

		// TODO(chris): variable table might be to small to store favorites!
	}

	public function index()
	{
		$result = $this->VariableModel->getVariables(getAuthUID(), ['stv_favorites']);

		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}

		$this->outputJson(getData($result)['stv_favorites']);
	}

	public function set()
	{
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);
		
		$this->load->library('form_validation');

		$this->form_validation->set_rules('favorites', 'Favorites', 'required');

		if ($this->form_validation->run() == false) {
			$this->output->set_status_header(REST_Controller::HTTP_BAD_REQUEST);
			return $this->outputJson($this->form_validation->error_array());
		}

		$favorites = $this->input->post('favorites');

		$result = $this->VariableModel->setVariable(getAuthUID(), 'stv_favorites', $favorites);

		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}

		$this->outputJsonSuccess(true);
	}
}
