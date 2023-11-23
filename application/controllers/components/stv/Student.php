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

	public function add()
	{
		$_POST = json_decode($this->input->raw_input_stream, true);

		if (!$this->input->post('person_id')) {
			if (!isset($_POST['address']) || !is_array($_POST['address']))
				$_POST['address'] = [];
			$_POST['address']['func'] = 1;
		}
		if ($this->input->post('incoming')) {
			$_POST['ausbildungssemester'] = 0;
		}

		$this->load->library('form_validation');

		$this->form_validation->set_rules('nachname', 'Nachname', 'callback_requiredIfNotPersonId', [
			'requiredIfNotPersonId' => $this->p->t('ui', 'error_required')
		]);
		$this->form_validation->set_rules('geschlecht', 'Geschlecht', 'callback_requiredIfNotPersonId', [
			'requiredIfNotPersonId' => $this->p->t('ui', 'error_required')
		]);
		$this->form_validation->set_rules('gebdatum', 'Geburtsdatum', 'callback_isValidDate', [
			'isValidDate' => $this->p->t('ui', 'error_invalid_date')
		]);
		$this->form_validation->set_rules('address[func]', 'Address', 'required|integer|less_than[2]|greater_than[-2]');
		$this->form_validation->set_rules('address[plz]', 'PLZ', 'callback_requiredIfAddressFunc', [
			'requiredIfAddressFunc' => $this->p->t('ui', 'error_required')
		]);
		$this->form_validation->set_rules('address[gemeinde]', 'Gemeinde', 'callback_requiredIfAddressFunc', [
			'requiredIfAddressFunc' => $this->p->t('ui', 'error_required')
		]);
		$this->form_validation->set_rules('address[ort]', 'Ort', 'callback_requiredIfAddressFunc', [
			'requiredIfAddressFunc' => $this->p->t('ui', 'error_required')
		]);
		$this->form_validation->set_rules('address[address]', 'Adresse', 'callback_requiredIfAddressFunc', [
			'requiredIfAddressFunc' => $this->p->t('ui', 'error_required')
		]);
		$this->form_validation->set_rules('email', 'E-Mail', 'valid_email');
		$this->form_validation->set_rules('studiengang_kz', 'Studiengang', 'required');
		$this->form_validation->set_rules('studiensemester_kurzbz', 'Studiensemester', 'required');
		$this->form_validation->set_rules('ausbildungssemester', 'Ausbildungssemester', 'required|integer|less_than[9]|greater_than[-1]');
		// TODO(chris): validate studienplan with studiengang, semester and orgform?
		// TODO(chris): validate person_id, studiengang_kz, studiensemester_kurzbz, orgform_kurzbz, nation, gemeinde, ort, geschlecht?

		if ($this->form_validation->run() == false) {
			$this->output->set_status_header(REST_Controller::HTTP_BAD_REQUEST);
			return $this->outputJsonError($this->form_validation->error_array());
		}

		// TODO(chris): This should be in a library
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$this->db->trans_start();

		$result = $this->addInteressent();

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return $this->outputJsonSuccess(true); // TODO(chris): DEBUG! REMOVE!
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->outputJson($result);
	}

	protected function addInteressent()
	{
		// Person anlegen wenn nötig
		$person_id = $this->input->post('person_id');
		if (!$person_id) {
			$this->load->model('person/Person_model', 'PersonModel');
			
			$data = [
				'nachname' => $this->input->post('nachname'),
				'insertamum' => date('c'),
				'insertvon' => getAuthUID(),
				'zugangscode' => uniqid(),
				'aktiv' => true
			];
			if ($this->input->post('anrede'))
				$data['anrede'] = $this->input->post('anrede');
			if ($this->input->post('titelpre'))
				$data['titelpre'] = $this->input->post('titelpre');
			if ($this->input->post('titelpost'))
				$data['titelpost'] = $this->input->post('titelpost');
			if ($this->input->post('vorname'))
				$data['vorname'] = $this->input->post('vorname');
			if ($this->input->post('vornamen'))
				$data['vornamen'] = $this->input->post('vornamen');
			if ($this->input->post('wahlname'))
				$data['wahlname'] = $this->input->post('wahlname');
			if ($this->input->post('geschlecht'))
				$data['geschlecht'] = $this->input->post('geschlecht');
			if ($this->input->post('gebdatum'))
				$data['gebdatum'] = (new DateTime($this->input->post('datum_obj')))->format('Y-m-d');
			if ($this->input->post('geburtsnation'))
				$data['geburtsnation'] = $this->input->post('geburtsnation');
			if ($this->input->post('staatsbuergerschaft'))
				$data['staatsbuergerschaft'] = $this->input->post('staatsbuergerschaft');

			$result = $this->PersonModel->insert($data);
			if (isError($result))
				return $result;
			$person_id = getData($result);
		}

		// Addresse anlegen
		$anlegen = $this->input->post('address[func]');
		if ($anlegen) {
			$this->load->model('person/Adresse_model', 'AdresseModel');

			$data = [
				'nation' => $this->input->post('address[nation]'),
				'strasse' => $this->input->post('address[address]'),
				'plz' => $this->input->post('address[plz]'),
				'ort' => $this->input->post('address[ort]'),
				'gemeinde' => $this->input->post('address[gemeinde]'),
				'typ' => 'h',
				'zustelladresse' => true,
			];
			if ($anlegen < 0) { // Überschreiben
				$this->AdresseModel->addOrder('zustelladresse', 'DESC');
				$this->AdresseModel->addOrder('sort');
				$result = $this->AdresseModel->loadWhere([
					'person_id' => $person_id
				]);
				if (isError($result))
					return $result;
				if (hasData($result)) {
					$address = current(getData($result));

					$data['updateamum'] = date('c');
					$data['updatevon'] = getAuthUID();

					$result = $this->AdresseModel->update($address->adresse_id, $data);
					if (isError($result))
						return $result;
				} else {
					//Wenn keine Adrese vorhanden ist dann eine neue Anlegen
					$anlegen = 1;
					$data['heimatadresse'] = true;
				}
			}
			if ($anlegen > 0) {
				$data['person_id'] = $person_id;
				$data['insertamum'] = date('c');
				$data['insertvon'] = getAuthUID();
				if (!isset($data['heimatadresse']))
					$data['heimatadresse'] = !$this->input->post('person_id');
				
				$result = $this->AdresseModel->insert($data);
				if (isError($result))
					return $result;
			}
		}
		
		// Kontaktdaten
		$kontaktdaten = [];
		foreach (['email', 'telefon', 'mobil'] as $k) {
			$v = $this->input->post($k);
			if ($v)
				$kontaktdaten[$k] = $v;
		}
		if (count($kontaktdaten)) {
			$this->load->model('person/Kontakt_model', 'KontaktModel');

			foreach ($kontaktdaten as $typ => $kontakt) {
				$data = [
					'person_id' => $person_id,
					'kontakttyp' => $typ,
					'kontakt' => $kontakt,
					'zustellung' => true,
					'insertamum' => date('c'),
					'insertvon' => getAuthUID()
				];
				$result = $this->KontaktModel->insert($data);
				if (isError($result))
					return $result;
			}
		}

		// Prestudent anlegen
		$data = [
			'aufmerksamdurch_kurzbz' => 'k.A.',
			'person_id' => $person_id,
			'studiengang_kz' => $this->input->post('studiengang_kz'),
			'ausbildungcode' => $this->input->post('letzteausbildung'),
			'anmerkung' => $this->input->post('anmerkungen'),
			'reihungstestangetreten' => false,
			'bismelden' => true
		];
		$ausbildungsart = $this->input->post('ausbildungsart');
		if ($ausbildungsart)
			$data['anmerkung'] .= ' Ausbildungsart:' . $ausbildungsart;
		// Incomings und ausserordentliche sind bei Meldung nicht förderrelevant
		$incoming = $this->input->post('incoming');
		if ($incoming || substr($data['studiengang_kz'], 0, 1) == '9')
			$data['foerderrelevant'] = false;
		// Wenn die Person schon im System erfasst ist, dann die ZGV des Datensatzes uebernehmen
		$this->PrestudentModel->addOrder('zgvmas_code');
		$this->PrestudentModel->addOrder('zgv_code', 'DESC');
		$this->PrestudentModel->addLimit(1);
		$result = $this->PrestudentModel->loadWhere([
			'person_id' => $person_id
		]);
		if (isError($result))
			return $result;
		if (hasData($result)) {
			$prestudent = current(getData($result));
			if ($prestudent->zgv_code) {
				$data['zgv_code'] = $prestudent->zgv_code;
				$data['zgvort'] = $prestudent->zgvort;
				$data['zgvdatum'] = $prestudent->zgvdatum;

				$data['zgvmas_code'] = $prestudent->zgvmas_code;
				$data['zgvmaort'] = $prestudent->zgvmaort;
				$data['zgvmadatum'] = $prestudent->zgvmadatum;
			}
		}
		// Prestudent speichern
		$result = $this->PrestudentModel->insert($data);
		if (isError($result))
			return $result;
		$prestudent_id = getData($result);

		// Prestudent Rolle Anlegen
		$data = [
			'prestudent_id' => $prestudent_id,
			'status_kurzbz' => $incoming ? 'Incoming' : 'Interessent',
			'studiensemester_kurzbz' => $this->input->post('studiensemester_kurzbz'),
			'ausbildungssemester' => $this->input->post('ausbildungssemester') ?: 0,
			'orgform_kurzbz' => $this->input->post('orgform_kurzbz') ?: null,
			'studienplan_id' => $this->input->post('studienplan_id') ?: null,
			'datum' => date('Y-m-d'),
			'insertamum' => date('c'),
			'insertvon' => getAuthUID()
		];
		$result = $this->PrestudentstatusModel->insert($data);
		if (isError($result))
			return $result;

		if ($incoming) {
			// TODO(chris): IMPLEMENT!
			//Matrikelnummer und UID generieren
			//Benutzerdatensatz anlegen
			//Studentendatensatz anlegen
			//StudentLehrverband anlegen
		}

		// TODO(chris): DEBUG
		$result = $this->PrestudentModel->loadWhere([
			'pestudent_id' => 1
		]);
		if (isError($result)) {
			return $result;
		}

		return success(true);
	}

	public function requiredIfNotPersonId($value)
	{
		if (isset($_POST['person_id']))
			return true;
		return !!$value;
	}
	
	public function requiredIfAddressFunc($value)
	{
		if (!$_POST['address']['func'])
			return true;
		return !!$value;
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
