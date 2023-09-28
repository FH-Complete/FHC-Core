<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Student extends FHC_Controller
{
	public function __construct()
	{
		// TODO(chris): access!
		parent::__construct();
	}

	public function get($prestudent_id)
	{
		// TODO(chris): stdSem from Variable
		$studiensemester_kurzbz='SS2023';

		$this->load->model('crm/Student_model', 'StudentModel');

		$this->StudentModel->addSelect('p.*');
		$this->StudentModel->addSelect('tbl_student.student_uid');
		$this->StudentModel->addSelect('matrikelnr');
		$this->StudentModel->addSelect('b.aktiv');
		$this->StudentModel->addSelect('v.semester');
		$this->StudentModel->addSelect('v.verband');
		$this->StudentModel->addSelect('v.gruppe');
		$this->StudentModel->addSelect('b.alias');

		$this->StudentModel->addJoin('public.tbl_benutzer b', 'student_uid = uid');
		$this->StudentModel->addJoin('public.tbl_studentlehrverband v', 'b.uid = v.student_uid AND v.studiensemester_kurzbz = ' . $this->StudentModel->escape($studiensemester_kurzbz), 'LEFT');
		$this->StudentModel->addJoin('public.tbl_person p', 'person_id');

		$result = $this->StudentModel->loadWhere(['prestudent_id' => $prestudent_id]);
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
	public function save($prestudent_id)
	{
		// TODO(chris): stdSem from Variable
		$studiensemester_kurzbz='SS2023';

		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');

		$data = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$result = $this->StudentModel->loadWhere(['prestudent_id' =>$prestudent_id]);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		} elseif (!hasData($result)) {
			$uid = null;
		} else {
			$student = current(getData($result));
			$uid = $student->student_uid;
		}

		$result = $this->PrestudentModel->loadWhere(['prestudent_id' =>$prestudent_id]);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		} elseif (!hasData($result)) {
			$person_id= null;
		} else {
			$person = current(getData($result));
			$person_id = $person->person_id;
		}

		$array_allowed_props = ['verband','semester','gruppe'];
		$update = array();
		foreach ($array_allowed_props as $prop)
		{
			if(isset($data[$prop])){
				$update[$prop] = $data[$prop];
			}
		}
		//TODO(chris): form validation verband
		if (count($update))
		{
			if($uid === null)
			{
				$this->output->set_status_header(REST_Controller::HTTP_BAD_REQUEST);
				return $this->outputJson("Kein/e StudentIn vorhanden!");
			}
			$this->StudentlehrverbandModel->update([
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'student_uid' => $uid],
				$update
			);
		}

		$array_allowed_props = [
			'anrede',
			'bpk',
			'titelpre',
			'titelpost',
			'nachname',
			'vorname',
			'vornamen',
			'wahlname',
			'geburtsdatum',
			'gebort',
			'geburtsnation',
			'svnr',
			'ersatzkennzeichen',
			'staatsbuergerschaft',
			'matr_nr',
			'sprache',
			'geschlecht',
			'familienstand',
			'foto',
			'anmerkung',
			'homepage'
		];
		$update = array();
		foreach ($array_allowed_props as $prop)
		{
			if(isset($data[$prop])){
				$update[$prop] = $data[$prop];
			}
		}

		if (count($update))
		{
			if($person_id === null)
			{
				$this->output->set_status_header(REST_Controller::HTTP_BAD_REQUEST);
				return $this->outputJson("Keine Person vorhanden!");
			}
			$this->PersonModel->update(
				$person_id,
				$update
			);
		}


		$array_allowed_props = ['matrikelnr'];
		$update = array();
		foreach ($array_allowed_props as $prop)
		{
			if(isset($data[$prop])){
				$update[$prop] = $data[$prop];
			}
		}
		if (count($update))
		{
			if ($uid === null) {
				$this->output->set_status_header(REST_Controller::HTTP_BAD_REQUEST);
				return $this->outputJson("Kein/e StudentIn vorhanden!");
			}
			$this->StudentModel->update(
				[$uid],
				$update
			);
		}






	}
}
