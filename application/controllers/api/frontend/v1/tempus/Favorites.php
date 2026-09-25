<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Favorites extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'index' => 'lehre/lvplan:rw',
			'set' => 'lehre/lvplan:rw'
		]);

		// Load models
		$this->load->model('system/Variable_model', 'VariableModel');
	}

	public function index()
	{
		$result = $this->VariableModel->getVariables(getAuthUID(), ['tempus_favorites']);

		$data = $this->getDataOrTerminateWithError($result);

		if (!$data)
			$this->terminateWithSuccess(null);
		else
			$this->terminateWithSuccess(isset($data['tempus_favorites']) ? $data['tempus_favorites'] : null);
	}

	public function set()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('favorites', 'Favorites', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$favorites = $this->input->post('favorites');

		$result = $this->VariableModel->setVariable(getAuthUID(), 'tempus_favorites', $favorites);

		$this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess(true);
	}
}
