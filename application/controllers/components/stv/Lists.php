<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Lists extends FHC_Controller
{
	public function __construct()
	{
		// TODO(chris): access!
		parent::__construct();
	}

	public function getSprachen()
	{
		$this->load->model('system/Sprache_model', 'SpracheModel');

		$this->SpracheModel->addOrder('sprache');

		$result = $this->SpracheModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getGeschlechter()
	{
		$this->load->model('person/Geschlecht_model', 'GeschlechtModel');

		$this->GeschlechtModel->addOrder('sort');
		$this->GeschlechtModel->addOrder('geschlecht');

		$this->GeschlechtModel->addSelect('*');
		$this->GeschlechtModel->addSelect("bezeichnung_mehrsprachig[(SELECT index FROM public.tbl_sprache WHERE sprache=" . $this->GeschlechtModel->escape(DEFAULT_LANGUAGE) . " LIMIT 1)] AS bezeichnung");

		$result = $this->GeschlechtModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getAusbildungen()
	{
		$this->load->model('codex/Ausbildung_model', 'AusbildungModel');

		$this->AusbildungModel->addOrder('ausbildungcode');

		$result = $this->AusbildungModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getStgs()
	{
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$this->StudiengangModel->addSelect('*');
		$this->StudiengangModel->addSelect('UPPER(typ || kurzbz) AS kuerzel');

		$this->StudiengangModel->addOrder('typ');
		$this->StudiengangModel->addOrder('kurzbz');

		$result = $this->StudiengangModel->loadWhere(['aktiv' => true]);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getOrgforms()
	{
		$this->load->model('codex/Orgform_model', 'OrgformModel');

		$this->OrgformModel->addOrder('bezeichnung');

		$result = $this->OrgformModel->loadWhere(['rolle' => true]);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}
}
