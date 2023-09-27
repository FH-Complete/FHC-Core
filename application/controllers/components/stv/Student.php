<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Student extends FHC_Controller
{
	public function __construct()
	{
		// TODO(chris): access!
		parent::__construct();
	}

	public function getPerson($person_id)
	{
		$this->load->model('person/Person_model', 'PersonModel');

		$result = $this->PersonModel->load($person_id);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		} elseif (!hasData($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_NOT_FOUND);
			$this->outputJson('NOT FOUND');
		} else {
			$this->outputJson(current(getData($result)));
		}
	}

	public function getStudent($student_uid)
	{
		// TODO(chris): this is wrong
		$this->load->model('crm/Student_model', 'StudentModel');

		$result = $this->StudentModel->load([$student_uid]);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		} elseif (!hasData($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_NOT_FOUND);
			$this->outputJson('NOT FOUND');
		} else {
			$this->outputJson(current(getData($result)));
		}
	}

	public function getNations()
	{
		$this->load->model('codex/Nation_model', 'NationModel');

		$this->NationModel->addOrder('kurztext');

		$result = $this->NationModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		} else {
			$this->outputJson(getData($result) ?: []);
		}
	}

	public function getSprachen()
	{
		$this->load->model('system/Sprache_model', 'SpracheModel');

		$this->SpracheModel->addOrder('sprache');

		$result = $this->SpracheModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		} else {
			$this->outputJson(getData($result) ?: []);
		}
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
			$this->outputJson(getError($result));
		} else {
			$this->outputJson(getData($result) ?: []);
		}
	}
}
