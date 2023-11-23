<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Address extends FHC_Controller
{
	public function __construct()
	{
		// TODO(chris): access!
		parent::__construct();
	}

	public function getNations()
	{
		$this->load->model('codex/Nation_model', 'NationModel');

		$this->NationModel->addOrder('kurztext');

		$result = $this->NationModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getPlaces($plz)
	{
		$this->load->model('codex/Gemeinde_model', 'GemeindeModel');

		$this->load->library('form_validation');
		
		$this->form_validation->set_data(['address.plz' => $plz]);

		$this->form_validation->set_rules('address.plz', 'PLZ', 'numeric|less_than[10000]');

		if ($this->form_validation->run() == false) {
			$this->output->set_status_header(REST_Controller::HTTP_BAD_REQUEST);
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$result = $this->GemeindeModel->getGemeindeByPlz($plz);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}
}
