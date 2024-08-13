<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Kontakt extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getAdressen' => ['admin:r', 'assistenz:r'],
			'addNewAddress' => ['admin:rw', 'assistenz:rw'],
			'addNewContact' => ['admin:rw', 'assistenz:rw'],
			'addNewBankverbindung' => ['mitarbeiter/bankdaten:rw', 'student/bankdaten:rw'],
			'updateAddress' => ['admin:rw', 'assistenz:rw'],
			'updateContact' => ['admin:rw', 'assistenz:rw'],
			'updateBankverbindung' => ['mitarbeiter/bankdaten:rw', 'student/bankdaten:rw'],
			'loadAddress' => ['admin:r', 'assistenz:r'],
			'loadContact' => ['admin:r', 'assistenz:r'],
			'loadBankverbindung' => ['mitarbeiter/bankdaten:r', 'student/bankdaten:r'],
			'deleteAddress' => ['admin:rw', 'assistenz:rw'],
			'deleteContact' => ['admin:rw','assistenz:rw'],
			'deleteBankverbindung' => ['mitarbeiter/bankdaten:rw','astudent/bankdaten:rw'],
			'getAdressentypen' => ['admin:r', 'assistenz:r'],
			'getKontakttypen' => ['admin:r', 'assistenz:r'],
			'getFirmen' => ['admin:r', 'assistenz:r'],
			'getStandorte' => ['admin:r', 'assistenz:r'],
			'getStandorteByFirma' => ['admin:r', 'assistenz:r'],
			'getKontakte' => ['admin:r', 'assistenz:r'],
			'getBankverbindung' => ['mitarbeiter/bankdaten:r', 'student/bankdaten:r']
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');

		// Load language phrases
		$this->loadPhrases([
			'ui',
			'person'
		]);

		// Load models
		$this->load->model('person/Adresse_model', 'AdresseModel');
		$this->load->model('organisation/standort_model', 'StandortModel');
		$this->load->model('ressource/firma_model', 'FirmaModel');
		$this->load->model('person/Kontakt_model', 'KontaktModel');

		// Extra Permissionchecks
		$permsMa = [];
		$permsStud = [];
		switch ($this->router->method) {
			case 'getBankverbindung':
			case 'loadBankverbindung':
				$permsMa = ['mitarbeiter/bankdaten:r'];
				$permsStud = ['student/bankdaten:r'];
				break;
			case 'addNewBankverbindung':
			case 'updateBankverbindung':
			case 'deleteBankverbindung':
				$permsMa = ['mitarbeiter/bankdaten:rw'];
				$permsStud = ['student/bankdaten:rw'];
				break;
			case 'getAdressen':
			case 'getKontakte':
			case 'loadAddress':
			case 'loadContact':
				$permsMa = $permsStud = ['admin:r', 'assistenz:r'];
				break;
			case 'addNewAddress':
			case 'addNewContact':
			case 'updateAddress':
			case 'updateContact':
			case 'deleteAddress':
			case 'deleteContact':
				$permsMa = $permsStud = ['admin:rw', 'assistenz:rw'];
				break;
		}
		if ($this->router->method == 'getAdressen'
			|| $this->router->method == 'getKontakte'
			|| $this->router->method == 'getBankverbindung'
			|| $this->router->method == 'addNewAddress'
			|| $this->router->method == 'addNewContact'
			|| $this->router->method == 'addNewBankverbindung'
		) {
			$person_id = current(array_slice($this->uri->rsegments, 2));
			
			$this->checkPermissionsForPerson($person_id, $permsMa, $permsStud);
		} elseif ($this->router->method == 'loadAddress'
			|| $this->router->method == 'loadContact'
			|| $this->router->method == 'loadBankverbindung'
			|| $this->router->method == 'updateAddress'
			|| $this->router->method == 'updateContact'
			|| $this->router->method == 'updateBankverbindung'
			|| $this->router->method == 'deleteAddress'
			|| $this->router->method == 'deleteContact'
			|| $this->router->method == 'deleteBankverbindung'
		) {
			$id = current(array_slice($this->uri->rsegments, 2));

			$model = 'person/Adresse_model';
			if ($this->router->method == 'loadContact'
				|| $this->router->method == 'updateContact'
				|| $this->router->method == 'deleteContact'
			) {
				$model = 'person/Kontakt_model';
			} elseif ($this->router->method == 'loadBankverbindung'
				|| $this->router->method == 'updateBankverbindung'
				|| $this->router->method == 'deleteBankverbindung'
			) {
				$model = 'person/Bankverbindung_model';
			}

			$this->load->model($model, 'TempModel');
			$result = $this->TempModel->load($id);
			$data = $this->getDataOrTerminateWithError($result);
			if (!$result)
				show_404();

			$person_id = current($data)->person_id;
			
			$this->checkPermissionsForPerson($person_id, $permsMa, $permsStud);
		}
	}
	public function getAdressen($person_id)
	{
		$this->AdresseModel->addSelect('public.tbl_adresse.*');
		$this->AdresseModel->addSelect('t.*');
		$this->AdresseModel->addSelect('f.firma_id');
		$this->AdresseModel->addSelect('f.name as firmenname');
		$this->AdresseModel->addJoin('public.tbl_adressentyp t', 'ON (t.adressentyp_kurzbz = public.tbl_adresse.typ)');
		$this->AdresseModel->addJoin('public.tbl_firma f', 'ON (f.firma_id = public.tbl_adresse.firma_id)', 'LEFT');

		$result = $this->AdresseModel->loadWhere(
			array('person_id' => $person_id)
		);
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function addNewAddress($person_id)
	{
		$this->form_validation->set_rules('plz', 'PLZ', 'required|numeric', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'PLZ']),
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'PLZ'])
		]);

		if(isset($_POST['gemeinde']) && isset($_POST['ort']))
		$this->form_validation->set_rules('plz', 'Postleitzahl', 'callback_validateLocationCombination', [
			'validateLocationCombination' => $this->p->t('ui', 'error_location_combination')
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$uid = getAuthUID();
		$co_name = isset($_POST['co_name']) ? $_POST['co_name'] : null;
		$strasse = isset($_POST['strasse']) ? $_POST['strasse'] : null;
		$ort = isset($_POST['ort']) ? $_POST['ort'] : null;
		$gemeinde = isset($_POST['gemeinde']) ? $_POST['gemeinde'] : null;
		$nation = isset($_POST['nation']) ? $_POST['nation'] : null;
		$name = isset($_POST['name']) ? $_POST['name'] : null;
		$typ = isset($_POST['typ']) ? $_POST['typ'] : null;
		$anmerkung = isset($_POST['anmerkung']) ? $_POST['anmerkung'] : null;

		if(isset($_POST['firma']))
		{
			$firma_id = $_POST['firma']['firma_id'];
		}
		else
			$firma_id = null;

		$result = $this->AdresseModel->insert(
			[
				'person_id' => $person_id,
				'strasse' =>  $strasse,
				'insertvon' => $uid,
				'insertamum' => date('c'),
				'plz' => $_POST['plz'],
				'ort' => $ort,
				'gemeinde' => $gemeinde,
				'nation' => $nation,
				'heimatadresse' => $_POST['heimatadresse'],
				'zustelladresse' => $_POST['zustelladresse'],
				'co_name' => $co_name,
				'typ' => $typ,
				'firma_id' => $firma_id,
				'name' => $name,
				'rechnungsadresse' => $_POST['rechnungsadresse'],
				'anmerkung' => $anmerkung

			]
		);
		if (isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->outputJsonSuccess(true);
	}

	public function updateAddress($address_id)
	{
		$uid = getAuthUID();
		$this->form_validation->set_rules('plz', 'PLZ', 'required|numeric', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'PLZ']),
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'PLZ'])
		]);

		if(isset($_POST['gemeinde']) && isset($_POST['ort']))
			$this->form_validation->set_rules('plz', 'Postleitzahl', 'callback_validateLocationCombination', [
				'validateLocationCombination' => $this->p->t('ui', 'error_location_combination')
			]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$this->load->model('person/Adresse_model', 'AdresseModel');

		if(!$address_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Adresse_id']), self::ERROR_TYPE_GENERAL);
		}

		if(isset($_POST['firma']))
		{
			$firma_id = $_POST['firma']['firma_id'];
		}
		elseif(isset($_POST['firma_id']))
		{
			$firma_id = $_POST['firma_id'];
		}
		else
			$firma_id = null;

		$person_id = isset($_POST['person_id']) ? $_POST['person_id'] : null;
		$co_name = isset($_POST['co_name']) ? $_POST['co_name'] : null;
		$strasse = isset($_POST['strasse']) ? $_POST['strasse'] : null;
		$ort = isset($_POST['ort']) ? $_POST['ort'] : null;
		$gemeinde = isset($_POST['gemeinde']) ? $_POST['gemeinde'] : null;
		$nation = isset($_POST['nation']) ? $_POST['nation'] : null;
		$name = isset($_POST['name']) ? $_POST['name'] : null;
		$typ = isset($_POST['typ']) ? $_POST['typ'] : null;
		$anmerkung = isset($_POST['anmerkung']) ? $_POST['anmerkung'] : null;

		$result = $this->AdresseModel->update(
			[
				'adresse_id' => $address_id
			],
			[	'person_id' => $person_id,
				'strasse' =>  $strasse,
				'updatevon' => $uid,
				'updateamum' => date('c'),
				'plz' => $_POST['plz'],
				'ort' => $ort,
				'gemeinde' => $gemeinde,
				'nation' => $nation,
				'heimatadresse' => $_POST['heimatadresse'],
				'zustelladresse' => $_POST['zustelladresse'],
				'co_name' => $co_name,
				'typ' => $typ,
				'firma_id' => $firma_id,
				'name' => $name,
				'rechnungsadresse' => $_POST['rechnungsadresse'],
				'anmerkung' => $anmerkung
			]
		);

		if (isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->outputJsonSuccess(true);
	}

	public function loadAddress($adresse_id)
	{
		$this->load->model('person/Adresse_model', 'AdresseModel');

		$this->AdresseModel->addSelect('public.tbl_adresse.*');
		$this->AdresseModel->addSelect('t.*');
		$this->AdresseModel->addSelect('f.firma_id');
		$this->AdresseModel->addSelect('f.name as firmenname');
		$this->AdresseModel->addJoin('public.tbl_adressentyp t', 'ON (t.adressentyp_kurzbz = public.tbl_adresse.typ)');
		$this->AdresseModel->addJoin('public.tbl_firma f', 'ON (f.firma_id = public.tbl_adresse.firma_id)', 'LEFT');

		$this->AdresseModel->addLimit(1);

		$result = $this->AdresseModel->loadWhere(
			array('adresse_id' => $adresse_id)
		);
		if (isError($result)) {
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		if (!hasData($result))
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Adresse_id']), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess(current(getData($result)) ? : null);
	}

	public function deleteAddress($adresse_id)
	{
		$this->load->model('person/Adresse_model', 'AdresseModel');
		$result = $this->AdresseModel->load([
			'adresse_id'=> $adresse_id,
		]);
		if(isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$result = current(getData($result));

		if($result->heimatadresse)

			$this->terminateWithError($this->p->t('person', 'error_deleteHomeAdress'), self::ERROR_TYPE_GENERAL);

		$result = $this->AdresseModel->delete(
			array('adresse_id' => $adresse_id)
		);

		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if (!hasData($result))
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Adresse_id']), self::ERROR_TYPE_GENERAL);
		}

		return $this->terminateWithSuccess(current(getData($result)) ? : null);
	}

	public function getAdressentypen()
	{
		$this->load->model('person/Adressentyp_model', 'AdressentypModel');

		$result = $this->AdressentypModel->load();
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getFirmen($searchString)
	{
		$this->load->model('ressource/firma_model', 'FirmaModel');

		$result = $this->FirmaModel->searchFirmen($searchString);
		if (isError($result)) {
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess($result ?: []);
	}

	public function getStandorte($searchString)
	{
		$this->load->model('organisation/standort_model', 'StandortModel');

		$result = $this->StandortModel->searchStandorte($searchString);
		if (isError($result)) {
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess($result ?: []);
	}

	public function getStandorteByFirma($firma_id)
	{
		$this->load->model('organisation/standort_model', 'StandortModel');

		$result = $this->StandortModel->getStandorteByFirma($firma_id);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getKontakte($person_id)
	{
		$this->KontaktModel->addSelect("public.tbl_kontakt.*,
			TO_CHAR (CASE 
					WHEN public.tbl_kontakt.updateamum >= public.tbl_kontakt.insertamum 
					THEN public.tbl_kontakt.updateamum 
					ELSE public.tbl_kontakt.insertamum 
				END::timestamp, 'DD.MM.YYYY HH24:MI:SS') AS lastUpdate, st.bezeichnung, f.name");
		$this->StandortModel->addJoin('public.tbl_standort st', 'ON (public.tbl_kontakt.standort_id = st.standort_id)', 'LEFT');
		$this->FirmaModel->addJoin('public.tbl_firma f', 'ON (f.firma_id = st.firma_id)', 'LEFT');
		$result = $this->KontaktModel->loadWhere(
			array('person_id' => $person_id)
		);

		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function getKontakttypen()
	{
		$this->load->model('person/Kontakttyp_model', 'KontakttypModel');

		$result = $this->KontakttypModel->load();
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		else
		{
			$this->terminateWithSuccess(getData($result) ?: []);
		}
	}

	public function loadContact($kontakt_id)
	{
		$this->load->model('person/Kontakt_model', 'KontaktModel');

		$this->KontaktModel->addSelect('*, public.tbl_kontakt.*');
		$this->KontaktModel->addSelect('st.kurzbz');
		$this->KontaktModel->addJoin('public.tbl_standort st', 'ON (public.tbl_kontakt.standort_id = st.standort_id)', 'LEFT');
		$this->FirmaModel->addJoin('public.tbl_firma f', 'ON (f.firma_id = st.firma_id)', 'LEFT');

		$this->KontaktModel->addLimit(1);

		$result = $this->KontaktModel->loadWhere(
			array('kontakt_id' => $kontakt_id)
		);
		if (isError($result)) {
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if (!hasData($result))
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Kontakt_id']), self::ERROR_TYPE_GENERAL);
		}
		//	$this->outputJsonSuccess(current(getData($result)));
		$this->terminateWithSuccess(current(getData($result)));
	}

	public function addNewContact($person_id)
	{
		if(($_POST['kontakttyp'] == 'email' && isset($_POST['kontakt'])))
		{
			$this->form_validation->set_rules('kontakt', 'Kontakt', 'required|valid_email', [
				'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Kontakt']),
				'valid_email' => $this->p->t('ui', 'error_fieldNoValidEmail', ['field' => 'Kontakt'])
			]);
		}
		else
		{
			$this->form_validation->set_rules('kontakt', 'Kontakt', 'required', [
				'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Kontakt'])
			]);
		}

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$this->load->model('person/Kontakt_model', 'KontaktModel');

		$uid = getAuthUID();

		$kontakttyp = $this->input->post('kontakttyp');
		$anmerkung = $this->input->post('anmerkung');
		$kontakt = $this->input->post('kontakt');
		$ext_id = $this->input->post('ext_id');
		$standort_id = $this->input->post('standort_id');

		$result = $this->KontaktModel->insert(
			[
				'person_id' => $person_id,
				'kontakttyp' =>  $kontakttyp,
				'anmerkung' => $anmerkung,
				'kontakt' => $kontakt,
				'zustellung' => $_POST['zustellung'],
				'insertvon' => $uid,
				'insertamum' => date('c'),
				'standort_id' => $standort_id,
				'ext_id' => $ext_id
			]
		);

		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		return $this->outputJsonSuccess(true);
	}

	public function updateContact($kontakt_id)
	{
		$this->load->model('person/Kontakt_model', 'KontaktModel');

		if(!$kontakt_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Kontakt_id']), self::ERROR_TYPE_GENERAL);
		}

		if(($_POST['kontakttyp'] == 'email' && isset($_POST['kontakt'])))
		{
			$this->form_validation->set_rules('kontakt', 'Kontakt', 'required|valid_email', [
				'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Kontakt']),
				'valid_email' => $this->p->t('ui', 'error_fieldNoValidEmail', ['field' => 'Kontakt'])
			]);
		}
		else
		{
			$this->form_validation->set_rules('kontakt', 'Kontakt', 'required', [
				'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Kontakt'])
			]);
		}

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

/*		if(isset($_POST['standort']))
		{
			$standort_id = $_POST['standort']['standort_id'];
		}
		else
			$standort_id = null;*/

		$uid = getAuthUID();
		$kontakttyp = $this->input->post('kontakttyp');
		$anmerkung = $this->input->post('anmerkung');
		$kontakt = $this->input->post('kontakt');
		$ext_id = $this->input->post('ext_id');
		$person_id = $this->input->post('person_id');
		$standort_id = $this->input->post('standort_id');

		//return $this->terminateWithError("in update " . $standort_id, self::ERROR_TYPE_GENERAL);

		$result = $this->KontaktModel->update(
			[
				'kontakt_id' => $kontakt_id
			],
			[
				'person_id' => $person_id,
				'kontakttyp' =>  $kontakttyp,
				'anmerkung' => $anmerkung,
				'kontakt' => $kontakt,
				'zustellung' => $_POST['zustellung'],
				'insertvon' => 	$uid,
				'insertamum' => date('c'),
				'standort_id' => $standort_id,
				'ext_id' => $ext_id
			]
		);

		if (isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->outputJsonSuccess(true);
	}

	public function deleteContact($kontakt_id)
	{
		$this->load->model('person/Kontakt_model', 'KontaktModel');

		$result = $this->KontaktModel->delete(
			array('kontakt_id' => $kontakt_id)
		);

		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		elseif (!hasData($result))
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Kontakt_id']), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(current(getData($result)) ? : null);
	}

	public function getBankverbindung($person_id)
	{
		$this->load->model('person/Bankverbindung_model', 'BankverbindungModel');

		$this->BankverbindungModel->addSelect('*');

		$result = $this->BankverbindungModel->loadWhere(
			array('person_id' => $person_id)
		);
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function addNewBankverbindung($person_id)
	{
		$this->form_validation->set_rules('iban', 'IBAN', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'IBAN'])
		]);

		$this->form_validation->set_rules('typ', 'TYP', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'TYP'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$this->load->model('person/Bankverbindung_model', 'BankverbindungModel');

		$ext_id = $this->input->post('ext_id');
		$oe_kurzbz = $this->input->post('oe_kurzbz');
		$orgform_kurzbz = $this->input->post('orgform_kurzbz');
		$name = $this->input->post('name');
		$anschrift = $this->input->post('anschrift');
		$bic = $this->input->post('bic');
		$blz  = $this->input->post('blz');
		$kontonr = $this->input->post('kontonr');

		$result = $this->BankverbindungModel->insert(
			[
				'person_id' => $person_id,
				'name' => $name,
				'anschrift' => $anschrift,
				'bic' => $bic,
				'iban' => $_POST['iban'],
				'blz' => $blz,
				'kontonr' => $kontonr,
				'insertvon' => 'uid',
				'insertamum' => date('c'),
				'typ' => $_POST['typ'],
				'verrechnung' => $_POST['verrechnung'],
				'ext_id' => $ext_id,
				'oe_kurzbz' => $oe_kurzbz,
				'orgform_kurzbz' => $orgform_kurzbz
			]
		);
		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		return $this->outputJsonSuccess(true);
	}

	public function loadBankverbindung($bankverbindung_id)
	{
		$this->load->model('person/Bankverbindung_model', 'BankverbindungModel');

		$this->BankverbindungModel->addSelect('*');

		$this->BankverbindungModel->addLimit(1);

		$result = $this->BankverbindungModel->loadWhere(
			array('bankverbindung_id' => $bankverbindung_id)
		);
		if (isError($result))
		{
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		if (!hasData($result))
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Bankverbindung_id']), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess(current(getData($result)));
	}

	public function updateBankverbindung($bankverbindung_id)
	{
		$this->form_validation->set_rules('iban', 'IBAN', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'IBAN'])
		]);

		$this->form_validation->set_rules('typ', 'TYP', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'TYP'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$this->load->model('person/Bankverbindung_model', 'BankverbindungModel');

		if(!$bankverbindung_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Bankverbindung_id']), self::ERROR_TYPE_GENERAL);
		}

		$uid = getAuthUID();

		$result = $this->BankverbindungModel->update(
			[
				'bankverbindung_id' => $bankverbindung_id
			],
			[
				'person_id' => $_POST['person_id'],
				'name' => $_POST['name'],
				'anschrift' => $_POST['anschrift'],
				'bic' => $_POST['bic'],
				'iban' => $_POST['iban'],
				'blz' => $_POST['blz'],
				'kontonr' => $_POST['kontonr'],
				'updatevon' => $uid,
				'updateamum' => date('c'),
				'typ' => $_POST['typ'],
				'verrechnung' => $_POST['verrechnung'],
				'ext_id' => $_POST['ext_id'],
				'oe_kurzbz' => $_POST['oe_kurzbz'],
				'orgform_kurzbz' => $_POST['orgform_kurzbz']
			]
		);

		if (isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->outputJsonSuccess(true);
	}

	public function deleteBankverbindung($bankverbindung_id)
	{
		$this->load->model('person/Bankverbindung_model', 'BankverbindungModel');

		$result = $this->BankverbindungModel->delete(
			array('bankverbindung_id' => $bankverbindung_id)
		);

		if (isError($result))
		{
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		if (!hasData($result))
		{
			$this->outputJson($result);
		}
		return $this->terminateWithSuccess(current(getData($result)) ? : null);
	}

	public function validateLocationCombination()
	{
		$this->load->model('codex/Gemeinde_model', 'GemeindeModel');

		return $this->GemeindeModel->checkLocation($_POST['plz'], $_POST['gemeinde'], $_POST['ort']);
	}
}
