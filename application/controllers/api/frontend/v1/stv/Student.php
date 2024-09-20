<?php
/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

/**
 * This controller operates between (interface) the JS (GUI) and the back-end
 * Provides data to the ajax get calls about a Student
 * Listens to ajax post calls to change the Student data
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Student extends FHCAPI_Controller
{
	/**
	 * Calls the parent's constructor and prepares libraries and phrases
	 */
	public function __construct()
	{
		parent::__construct([
			'get' => ['admin:r', 'assistenz:r'],
			'save' => ['admin:rw', 'assistenz:rw'],
			'check' => ['admin:rw', 'assistenz:rw'],
			'add' => ['admin:rw', 'assistenz:rw'] // TODO(chris): extra permissions
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		if ($this->router->method == 'get'
			|| $this->router->method == 'save'
		) {
			$prestudent_id = current(array_slice($this->uri->rsegments, 2));
			if ($this->router->method == 'get')
				$this->checkPermissionsForPrestudent($prestudent_id, ['admin:r', 'assistenz:r']);
			else
				$this->checkPermissionsForPrestudent($prestudent_id, ['admin:rw', 'assistenz:rw']);
		}

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Get details for a prestudent
	 *
	 * @param string			$prestudent_id
	 * @return void
	 */
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
			$this->PrestudentModel->addSelect(
				"(
					SELECT kontakt 
					FROM public.tbl_kontakt 
					WHERE kontakttyp='email' 
					AND person_id=p.person_id 
					AND zustellung 
					ORDER BY kontakt_id 
					LIMIT 1
				) AS email_privat",
				false
			);
		}

		$this->PrestudentModel->addJoin('public.tbl_student s', 'prestudent_id', 'LEFT');
		$this->PrestudentModel->addJoin('public.tbl_benutzer b', 'student_uid = uid', 'LEFT');
		$this->PrestudentModel->addJoin(
			'public.tbl_studentlehrverband v',
			'b.uid = v.student_uid AND v.studiensemester_kurzbz = ' . $this->PrestudentModel->escape($studiensemester_kurzbz),
			'LEFT'
		);
		$this->PrestudentModel->addJoin('public.tbl_person p', 'p.person_id = tbl_prestudent.person_id');

		$result = $this->PrestudentModel->loadWhere(['prestudent_id' => $prestudent_id]);
		
		$student = $this->getDataOrTerminateWithError($result);
		
		if (!$student)
			return show_404();

		$this->terminateWithSuccess(current($student));
	}

	/**
	 * Saves data to a prestudent
	 *
	 * @param string			$prestudent_id
	 * @return void
	 */
	public function save($prestudent_id)
	{
		$studiensemester_kurzbz = $this->variablelib->getVar('semester_aktuell');

		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');

		$this->load->library('form_validation');

		$this->form_validation->set_rules('gebdatum', 'Geburtsdatum', 'is_valid_date');

		$this->form_validation->set_rules('semester', 'Semester', 'integer');

		$this->load->library('UDFLib');
		
		$result = $this->udflib->getCiValidations($this->PersonModel, $this->input->post());

		//TODO(Manu) check with Chris: input number not allowed
		$udf_field_validations = $this->getDataOrTerminateWithError($result);

		$this->form_validation->set_rules($udf_field_validations);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$result = $this->StudentModel->loadWhere(['prestudent_id' => $prestudent_id]);
		
		$student = $this->getDataOrTerminateWithError($result);

		$uid = $student ? current($student)->student_uid : null;

		$result = $this->PrestudentModel->loadWhere(['prestudent_id' => $prestudent_id]);

		$person = $this->getDataOrTerminateWithError($result);

		$person_id = $person ? current($person)->person_id : null;

		
		$array_allowed_props_lehrverband = ['verband', 'semester', 'gruppe'];
		$update_lehrverband = array();
		foreach ($array_allowed_props_lehrverband as $prop) {
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
		
		// add UDFs
		$result = $this->udflib->getDefinitionForModel($this->PersonModel);

		$definitions = $this->getDataOrTerminateWithError($result);

		foreach ($definitions as $def)
			$array_allowed_props_person[] = $def['name'];

		$update_person = array();
		foreach ($array_allowed_props_person as $prop) {
			$val = $this->input->post($prop);
			if ($val !== null) {
				$update_person[$prop] = $val;
			}
		}

		$array_allowed_props_student = ['matrikelnr'];
		$update_student = array();
		foreach ($array_allowed_props_student as $prop) {
			$val = $this->input->post($prop);
			if ($val !== null) {
				$update_student[$prop] = $val;
			}
		}

		// Check PKs
		if (count($update_lehrverband) + count($update_student) && $uid === null) {
			// TODO(chris): phrase
			$this->terminateWithValidationErrors(['' => "Kein/e StudentIn vorhanden!"]);
		}
		if (count($update_person) && $person_id === null) {
			// TODO(chris): phrase
			$this->terminateWithValidationErrors(['' => "Keine Person vorhanden!"]);
		}

		// Do Updates
		if (count($update_lehrverband)) {
			$result = $this->StudentlehrverbandModel->update([
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'student_uid' => $uid
			], $update_lehrverband);
			$this->getDataOrTerminateWithError($result);
		}

		if (count($update_person)) {
			$result = $this->PersonModel->update(
				$person_id,
				$update_person
			);
			$this->getDataOrTerminateWithError($result);
		}


		if (count($update_student)) {
			$result = $this->StudentModel->update(
				[$uid],
				$update_student
			);
			$this->getDataOrTerminateWithError($result);
		}

		$this->terminateWithSuccess(array_fill_keys(array_merge(
			array_keys($update_lehrverband),
			array_keys($update_person),
			array_keys($update_student)
		), ''));
	}

	public function check()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('gebdatum', 'Geburtsdatum', 'is_valid_date');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$vorname = $this->input->post('vorname');
		$nachname = $this->input->post('nachname');
		$gebdatum = $this->input->post('gebdatum');

		if (!$vorname && !$nachname && !$gebdatum)
			$this->terminateWithValidationErrors(['At least one of vorname, nachname or gebdatum must be set']);

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

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function add()
	{
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

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		// TODO(chris): This should be in a library
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$this->db->trans_start();

		$result = $this->addInteressent();

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
			$this->terminateWithError('TODO(chris): TEXT', self::ERROR_TYPE_GENERAL);

		$this->terminateWithSuccess($result);
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
			$person_id = $this->getDataOrTerminateWithError($result);
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
				$address = $this->getDataOrTerminateWithError($result);
				if ($address) {
					$address = current($address);

					$data['updateamum'] = date('c');
					$data['updatevon'] = getAuthUID();

					$result = $this->AdresseModel->update($address->adresse_id, $data);
					$this->getDataOrTerminateWithError($result);
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
				$this->getDataOrTerminateWithError($result);
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
				$this->getDataOrTerminateWithError($result);
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
		$prestudent = $this->getDataOrTerminateWithError($result);
		if ($prestudent) {
			$prestudent = current($prestudent);
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
		$prestudent_id = $this->getDataOrTerminateWithError($result);

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
		$this->getDataOrTerminateWithError($result);

		if ($incoming) {
			// TODO(chris): IMPLEMENT!
			//Matrikelnummer und UID generieren
			//Benutzerdatensatz anlegen
			//Studentendatensatz anlegen
			//StudentLehrverband anlegen
		}

		// TODO(chris): DEBUG
		/*$result = $this->PrestudentModel->loadWhere([
			'pestudent_id' => 1
		]);
		if (isError($result)) {
			return $result;
		}*/

		$this->terminateWithSuccess(true);
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
}
