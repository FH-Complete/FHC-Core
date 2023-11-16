<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Student extends FHC_Controller
{
	public function __construct()
	{
		// TODO(chris): access!
		parent::__construct();

		// Load Libraries
		$this->load->library('AuthLib');
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);
	}

	public function get($prestudent_id)
	{
		$studiensemester_kurzbz = $this->variablelib->getVar('semester_aktuell');

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
			$this->outputJson($result);
		} else {
			$this->outputJsonSuccess(getData($result) ?: []);
		}
	}

	public function save($prestudent_id)
	{
		$studiensemester_kurzbz = $this->variablelib->getVar('semester_aktuell');

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

	public function check()
	{
		$_POST = json_decode($this->input->raw_input_stream, true);
		
		$this->load->library('form_validation');

		$this->form_validation->set_rules('gebdatum', 'Geburtsdatum', 'callback_isValidDate', [
			'isValidDate' => $this->p->t('ui', 'error_invalid_date')
		]);

		if ($this->form_validation->run() == false) {
			$this->output->set_status_header(REST_Controller::HTTP_BAD_REQUEST);
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$vorname = $this->input->post('vorname');
		$nachname = $this->input->post('nachname');
		$gebdatum = $this->input->post('gebdatum');

		if (!$vorname && !$nachname && !$gebdatum) {
			return $this->outputJsonError(['#' => 'At least one of vorname, nachname or gebdatum must be set']);
		}

		$this->load->model('person/Person_model', 'PersonModel');

		if ($gebdatum)
			$this->PersonModel->db->where('gebdatum', (new DateTime($gebdatum))->format('Y-m-d'));
		if ($vorname && $nachname) {
			$this->PersonModel->db->or_group_start();
			$this->PersonModel->db->where('LOWER(nachname)', 'LOWER(' . $this->PersonModel->db->escape($nachname) . ')', false);
			$this->PersonModel->db->where('LOWER(vorname)', 'LOWER(' . $this->PersonModel->db->escape($vorname) . ')', false);
			$this->PersonModel->db->group_end();
		} elseif ($nachname) {
			$this->PersonModel->db->or_where('LOWER(nachname)', 'LOWER(' . $this->PersonModel->escape($nachname) . ')', false);
		}

		$result = $this->PersonModel->load();

		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->outputJson($result);
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
	
	public function getAdressen($person_id)
	{
		$this->load->model('person/Adresse_model', 'AdresseModel');
		$this->load->model('person/Adressentyp_model', 'AdressentypModel');
		$this->load->model('ressource/firma_model', 'FirmaModel');

		$this->AdresseModel->addSelect('*');
		$this->AdressentypModel->addJoin('public.tbl_adressentyp t', 'ON (t.adressentyp_kurzbz = public.tbl_adresse.typ)');
		$this->FirmaModel->addJoin('public.tbl_firma f', 'ON (f.firma_id = public.tbl_adresse.firma_id)', 'LEFT');

		$result = $this->AdresseModel->loadWhere(
			array('person_id' => $person_id)
		);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		} else {
			$this->outputJson(getData($result) ?: []);
		}
	}

	public function addNewAddress($person_id)
	{
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		//var_dump($_POST);
		//echo ($_POST['ort']);
		$this->load->model('person/Adresse_model', 'AdresseModel');
		// Load Libraries
		$this->load->library('AuthLib');
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		$result = $this->AdresseModel->insert(
			[
				'person_id' => $person_id,
				'strasse' =>  $_POST['strasse'],
				'insertvon' => 'FHC4',
				'insertamum' => date('c'),
				'plz' => $_POST['plz'],
				'ort' => $_POST['ort'],
				'gemeinde' => $_POST['gemeinde'],
				'nation' => $_POST['nation'],
				'heimatadresse' => $_POST['heimatadresse'],
				'zustelladresse' => $_POST['zustelladresse'],
				'co_name' => $_POST['co_name'],
				'typ' => $_POST['typ']
			]
		);

		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}
		return $this->outputJsonSuccess(true);
	}

	public function getAdressentypen()
	{
		$this->load->model('person/Adressentyp_model', 'AdressentypModel');

		$result = $this->AdressentypModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		} else {
			$this->outputJson(getData($result) ?: []);
		}
	}

	public function getGmeinden($plz)
	{
		//TODO(manu) finish
		$result = "";
		return $result;
	}

	public function getKontakte($person_id)
	{
		$this->load->model('person/Kontakt_model', 'KontaktModel');
		$this->load->model('organisation/standort_model', 'StandortModel');
		$this->load->model('ressource/firma_model', 'FirmaModel');

		$this->KontaktModel->addSelect('*');
		$this->StandortModel->addJoin('public.tbl_standort st', 'ON (public.tbl_kontakt.standort_id = st.standort_id)', 'LEFT');
		$this->StandortModel->addJoin('public.tbl_firma f', 'ON (f.firma_id = st.firma_id)', 'LEFT');

		$result = $this->KontaktModel->loadWhere(
			array('person_id' => $person_id)
		);

		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		} else {
			$this->outputJson(getData($result) ?: []);
		}
	}

	public function getBankverbindung($person_id)
	{
		$this->load->model('person/Bankverbindung_model', 'BankverbindungModel');

		$this->BankverbindungModel->addSelect('*');

		$result = $this->BankverbindungModel->loadWhere(
			array('person_id' => $person_id)
		);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		} else {
			$this->outputJson(getData($result) ?: []);
		}
	}
}
