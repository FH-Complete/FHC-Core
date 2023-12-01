<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Kontakt extends FHC_Controller
{
	public function __construct()
	{
		parent::__construct();

		// Load Libraries
		$this->load->library('AuthLib');
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);
	}

	public function getAdressen($person_id)
	{
		$this->load->model('person/Adresse_model', 'AdresseModel');

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
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		} else {
			$this->outputJson(getData($result) ?: []);
		}
	}

	//old version
	public function addNewAddress($person_id)
	{
		$this->load->library('form_validation');
		$_POST = json_decode($this->input->raw_input_stream, true);

		$this->form_validation->set_rules('plz', 'PLZ', 'required|numeric');

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$this->load->model('person/Adresse_model', 'AdresseModel');

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
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}
		return $this->outputJsonSuccess(true);
	}

	//Version mit input->post() TODO(Manu) check mit Chris, not working
/*	public function addNewAddress($person_id)
	{
		$this->load->library('form_validation');
		//$_POST = json_decode($this->input->raw_input_stream, true);


		$this->load->model('person/Adresse_model', 'AdresseModel');

		$uid = getAuthUID();

		$data = [
			'insertvon' => $uid,
			'insertamum' => date('c'),
			'plz' => $this->input->post('plz'),
			'heimatadresse' => $this->input->post('heimatadresse'),
			'zustelladresse' => $this->input->post('zustelladresse'),
			'rechnungsadresse' => $this->input->post('rechnungsadresse')
		];


		$this->form_validation->set_rules('plz', 'PLZ', 'required|numeric');

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}


		if ($this->input->post('co_name'))
			$data['co_name'] = $this->input->post('co_name');
		if ($this->input->post('strasse'))
			$data['strasse'] = $this->input->post('strasse');
		if ($this->input->post('ort'))
			$data['ort'] = $this->input->post('ort');
		if ($this->input->post('gemeinde'))
			$data['gemeinde'] = $this->input->post('gemeinde');
		if ($this->input->post('nation'))
			$data['nation'] = $this->input->post('nation');
		if ($this->input->post('name'))
			$data['name'] = $this->input->post('name');
		if ($this->input->post('typ'))
			$data['typ'] = $this->input->post('typ');
		if ($this->input->post('anmerkung'))
			$data['anmerkung'] = $this->input->post('anmerkung');
		if ($this->input->post('firma'))
			$data['firma_id'] = $this->input->post('firma_id');

		$result = $this->AdresseModel->insert($data);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}
		return $this->outputJsonSuccess(true);
	}*/

	public function updateAddress($address_id)
	{
		$uid = getAuthUID();
		$this->load->library('form_validation');
		$_POST = json_decode($this->input->raw_input_stream, true);

		$this->form_validation->set_rules('plz', 'PLZ', 'required');

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$this->load->model('person/Adresse_model', 'AdresseModel');

		if(!$address_id)
		{
			return $this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(isset($_POST['firma']))
		{
			$firma_id = $_POST['firma']['firma_id'];
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
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
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
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}

		elseif (!hasData($result)) {
			$this->outputJson($result); //success mit Wert null
		}
		else
		{
			$this->outputJsonSuccess(current(getData($result)));
		}
	}

	public function deleteAddress($adresse_id)
	{
		$this->load->model('person/Adresse_model', 'AdresseModel');

		$result = $this->AdresseModel->delete(
			array('adresse_id' => $adresse_id)
		);

		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}
		elseif (!hasData($result)) {
			$this->outputJson($result); //success mit Wert null
		}
		return $this->outputJsonSuccess(current(getData($result)));
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

	public function getFirmen($searchString)
	{
		$this->load->model('ressource/firma_model', 'FirmaModel');

		$result = $this->FirmaModel->searchFirmen($searchString);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getStandorte($searchString)
	{
		$this->load->model('organisation/standort_model', 'StandortModel');

		$result = $this->StandortModel->searchStandorte($searchString);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getKontakte($person_id)
	{
		$this->load->model('person/Kontakt_model', 'KontaktModel');
		$this->load->model('organisation/standort_model', 'StandortModel');

		$this->KontaktModel->addSelect('*');
		$this->StandortModel->addJoin('public.tbl_standort st', 'ON (public.tbl_kontakt.standort_id = st.standort_id)', 'LEFT');

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

	public function getKontakttypen()
	{
		$this->load->model('person/Kontakttyp_model', 'KontakttypModel');

		$result = $this->KontakttypModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		} else {
			$this->outputJson(getData($result) ?: []);
		}
	}

	public function loadContact($kontakt_id)
	{
		$this->load->model('person/Kontakt_model', 'KontaktModel');

		$this->KontaktModel->addSelect('public.tbl_kontakt.*');
		$this->KontaktModel->addSelect('st.kurzbz');
		$this->KontaktModel->addJoin('public.tbl_standort st', 'ON (public.tbl_kontakt.standort_id = st.standort_id)', 'LEFT');

		$this->KontaktModel->addLimit(1);

		$result = $this->KontaktModel->loadWhere(
			array('kontakt_id' => $kontakt_id)
		);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}

		elseif (!hasData($result)) {
			$this->outputJson($result); //success mit Wert null
			//	$this->outputJson(getData($result) ?: []);
		}
		else
		{
			$this->outputJsonSuccess(current(getData($result)));
		}
	}

	public function addNewContact($person_id)
	{
		$this->load->library('form_validation');

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		if(($_POST['kontakttyp'] == 'email' && isset($_POST['kontakt'])))
			$this->form_validation->set_rules('kontakt', 'Kontakt', 'valid_email');
		else
			$this->form_validation->set_rules('kontakt', 'Kontakt', 'required');

/*		if(($_POST['kontakttyp'] == 'email' && isset($_POST['kontakt'])))
		{
			$this->form_validation->set_data(['email' => $_POST['kontakt']]);
			$this->form_validation->set_rules('email', 'email', 'valid_email|required]');
		}*/


		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}


		$this->load->model('person/Kontakt_model', 'KontaktModel');

		if(isset($_POST['standort']))
		{
			$standort_id = $_POST['standort']['standort_id'];
		}
		else
			$standort_id = null;

		$uid = getAuthUID();
		$kontakttyp = isset($_POST['kontakttyp']) ? $_POST['kontakttyp'] : null;
		$anmerkung = isset($_POST['anmerkung']) ? $_POST['anmerkung'] : null;
		$kontakt = isset($_POST['kontakt']) ? $_POST['kontakt'] : null;
		$ext_id = isset($_POST['ext_id']) ? $_POST['ext_id'] : null;

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
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}
		return $this->outputJsonSuccess(true);
	}

	public function updateContact($kontakt_id)
	{
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$this->load->model('person/Kontakt_model', 'KontaktModel');

		if(!$kontakt_id)
		{
			return $this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(isset($_POST['standort']))
		{
			$standort_id = $_POST['standort']['standort_id'];
		}
		else
			$standort_id = null;

		$uid = getAuthUID();
		$kontakttyp = isset($_POST['kontakttyp']) ? $_POST['kontakttyp'] : null;
		$anmerkung = isset($_POST['anmerkung']) ? $_POST['anmerkung'] : null;
		$kontakt = isset($_POST['kontakt']) ? $_POST['kontakt'] : null;
		$ext_id = isset($_POST['ext_id']) ? $_POST['ext_id'] : null;
		$person_id = isset($_POST['person_id']) ? $_POST['person_id'] : null;

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
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
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
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}
		elseif (!hasData($result)) {
			$this->outputJson($result);
		}
		return $this->outputJsonSuccess(current(getData($result)));
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

	public function addNewBankverbindung($person_id)
	{
		$this->load->library('form_validation');
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$this->form_validation->set_rules('iban', 'IBAN', 'required');
		$this->form_validation->set_rules('typ', 'TYP', 'required');

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$this->load->model('person/Bankverbindung_model', 'BankverbindungModel');

		$ext_id = isset($_POST['ext_id']) ? $_POST['ext_id'] : null;
		$oe_kurzbz = isset($_POST['oe_kurzbz']) ? $_POST['oe_kurzbz'] : null;
		$orgform_kurzbz = isset($_POST['orgform_kurzbz']) ? $_POST['orgform_kurzbz'] : null;
		$name = isset($_POST['name']) ? $_POST['name'] : null;
		$anschrift = isset($_POST['anschrift']) ? $_POST['anschrift'] : null;
		$bic = isset($_POST['bic']) ? $_POST['bic'] : null;
		$blz = isset($_POST['blz']) ? $_POST['blz'] : null;
		$kontonr = isset($_POST['kontonr']) ? $_POST['kontonr'] : null;

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
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
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
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}

		elseif (!hasData($result)) {
			$this->outputJson($result); //success mit Wert null
			//	$this->outputJson(getData($result) ?: []);
		}
		else
		{
			$this->outputJsonSuccess(current(getData($result)));
		}
	}

	public function updateBankverbindung($bankverbindung_id)
	{
		$this->load->library('form_validation');
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$this->form_validation->set_rules('iban', 'IBAN', 'required');
		$this->form_validation->set_rules('typ', 'TYP', 'required');

		$this->load->model('person/Bankverbindung_model', 'BankverbindungModel');

		if(!$bankverbindung_id)
		{
			return $this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
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
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
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
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}
		elseif (!hasData($result)) {
			$this->outputJson($result);
		}
		return $this->outputJsonSuccess(current(getData($result)));
	}
}
