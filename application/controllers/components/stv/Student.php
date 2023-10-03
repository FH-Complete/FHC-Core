<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Student extends FHC_Controller
{
	public function __construct()
	{
		// TODO(chris): access!
		parent::__construct();

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);
	}

	public function get($prestudent_id)
	{
		// TODO(chris): stdSem from Variable
		$studiensemester_kurzbz='SS2023';

		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$this->PrestudentModel->addSelect('p.*');
		$this->PrestudentModel->addSelect('s.student_uid');
		$this->PrestudentModel->addSelect('matrikelnr');
		$this->PrestudentModel->addSelect('b.aktiv');
		$this->PrestudentModel->addSelect('v.semester');
		$this->PrestudentModel->addSelect('v.verband');
		$this->PrestudentModel->addSelect('v.gruppe');
		$this->PrestudentModel->addSelect('b.alias');

		if (defined('ACTIVE_ADDONS') && strpos(ACTIVE_ADDONS, 'bewerbung') !== false) {
			$this->PrestudentModel->addSelect("(SELECT kontakt FROM public.tbl_kontakt WHERE kontakttyp='email' AND person_id=p.person_id AND zustellung ORDER BY kontakt_id LIMIT 1) AS email_privat", false);
		}

		$this->PrestudentModel->addJoin('public.tbl_student s', 'prestudent_id', 'LEFT');
		$this->PrestudentModel->addJoin('public.tbl_benutzer b', 'student_uid = uid', 'LEFT');
		$this->PrestudentModel->addJoin('public.tbl_studentlehrverband v', 'b.uid = v.student_uid AND v.studiensemester_kurzbz = ' . $this->PrestudentModel->escape($studiensemester_kurzbz), 'LEFT');
		$this->PrestudentModel->addJoin('public.tbl_person p', 'p.person_id = tbl_prestudent.person_id');

		$result = $this->PrestudentModel->loadWhere(['prestudent_id' => $prestudent_id]);
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

		$this->load->library('form_validation');

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$this->form_validation->set_rules('gebdatum', 'Geburtsdatum', 'callback_isValidDate', [
			'isValidDate' => $this->p->t('ui', 'error_invalid_date')
		]);

		$this->form_validation->set_rules('semester', 'Semester', 'integer');

		if ($this->form_validation->run() == false) {
			return $this->outputJsonError($this->form_validation->error_array());
		}

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

		$array_allowed_props_lehrverband = ['verband', 'semester', 'gruppe'];
		$update_lehrverband = array();
		foreach ($array_allowed_props_lehrverband as $prop)
		{
			$val = $this->input->post($prop);
			if ($val !== null) {
				$update_lehrverband[$prop] = $val;
			}
		}

		$array_allowed_props_person = [
			'anrede',
			'bpk',
			'titelpre',
			'titelpost',
			'nachname',
			'vorname',
			'vornamen',
			'wahlname',
			'gebdatum',
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
		$update_person = array();
		foreach ($array_allowed_props_person as $prop)
		{
			$val = $this->input->post($prop);
			if ($val !== null) {
				$update_person[$prop] = $val;
			}
		}

		$array_allowed_props_student = ['matrikelnr'];
		$update_student = array();
		foreach ($array_allowed_props_student as $prop)
		{
			$val = $this->input->post($prop);
			if ($val !== null) {
				$update_student[$prop] = $val;
			}
		}

		if (count($update_lehrverband) + count($update_student) && $uid === null) {
			$this->output->set_status_header(REST_Controller::HTTP_BAD_REQUEST);
			// TODO(chris): phrase
			return $this->outputJson("Kein/e StudentIn vorhanden!");
		}
		if (count($update_person) && $person_id === null) {
			$this->output->set_status_header(REST_Controller::HTTP_BAD_REQUEST);
			// TODO(chris): phrase
			return $this->outputJson("Keine Person vorhanden!");
		}

		if (count($update_lehrverband))
		{
			$result = $this->StudentlehrverbandModel->update([
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'student_uid' => $uid
			], $update_lehrverband);
			if (isError($result)) {
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
		}

		if (count($update_person))
		{
			$result = $this->PersonModel->update(
				$person_id,
				$update_person
			);
			if (isError($result)) {
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
		}


		if (count($update_student))
		{
			$result = $this->StudentModel->update(
				[$uid],
				$update_student
			);
			if (isError($result)) {
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
		}

		$this->outputJsonSuccess(true);
	}

	public function isValidDate($date)
	{
		try {
		    new DateTime($date);
		} catch (Exception $e) {
			return false;
		}
		return true;
	}

}
