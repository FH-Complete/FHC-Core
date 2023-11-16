<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Kontakt extends FHC_Controller
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

	public function getAdressen($person_id)
	{
		$this->load->model('person/Adresse_model', 'AdresseModel');
		//TODO(manu) check select: für Anzeige alle Tabellen nötig
		$this->AdresseModel->addSelect('*');
		$this->AdresseModel->addJoin('public.tbl_adressentyp t', 'ON (t.adressentyp_kurzbz = public.tbl_adresse.typ)');
		$this->AdresseModel->addJoin('public.tbl_firma f', 'ON (f.firma_id = public.tbl_adresse.firma_id)', 'LEFT');

		//	$this->AdresseModel->addJoin('public.tbl_firmentyp ft', 'ON (f.firma_id = ft.firmentyp.firma_id)', 'LEFT');

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

		$this->load->model('person/Adresse_model', 'AdresseModel');

		// Load Libraries
		$this->load->library('AuthLib');
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		$firma_id = '';
		if (isset($_POST['firma_id']))
			$firma_id = $_POST('firma_id');

		$co_name = '';
		if (isset($_POST['co_name']))
			$co_name = $_POST('co_name');

		$result = $this->AdresseModel->insert(
			[
				'person_id' => $person_id,
				'strasse' =>  $_POST['strasse'],
				'insertvon' => 'uid',
				'insertamum' => date('c'),
				'plz' => $_POST['plz'],
				'ort' => $_POST['ort'],
				'gemeinde' => $_POST['gemeinde'],
				'nation' => $_POST['nation'],
				'heimatadresse' => $_POST['heimatadresse'],
				'zustelladresse' => $_POST['zustelladresse'],
				'co_name' => $co_name,
				'typ' => $_POST['typ'],
				'firma_id' => $firma_id,
				'name' => $_POST['name'],
				'rechnungsadresse' => $_POST['rechnungsadresse'],
				'anmerkung' => $_POST['anmerkung']


			]
		);

		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}
		return $this->outputJsonSuccess(true);
	}

	public function updateAddress($address_id)
		//Todo(manu) update Firma
	{
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$this->load->model('person/Adresse_model', 'AdresseModel');
		// Load Libraries
		$this->load->library('AuthLib');
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		if(!$address_id)
		{
			return $this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

		$result = $this->AdresseModel->update([
			'adresse_id' => $address_id
		],
			[	'person_id' => $_POST['person_id'],
				'strasse' =>  $_POST['strasse'],
				'updatevon' => 'uid',
				'updateamum' => date('c'),
				'plz' => $_POST['plz'],
				'ort' => $_POST['ort'],
				'gemeinde' => $_POST['gemeinde'],
				'nation' => $_POST['nation'],
				'heimatadresse' => $_POST['heimatadresse'],
				'zustelladresse' => $_POST['zustelladresse'],
				'co_name' => $_POST['co_name'],
				'typ' => $_POST['typ']
			]);

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

		$this->AdresseModel->addSelect('*');
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
			//	$this->outputJson(getData($result) ?: []);
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

	//TODO(Manu) Liste zu lang - besser nachladen
	public function getFirmen($searchString)
	{
		$this->load->model('ressource/firma_model', 'FirmaModel');

		$result = $this->FirmaModel->searchFirmen($searchString);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		} else {
			$this->outputJson(getData($result) ?: []);
		}
	}

	//TODO(Manu) Autocomplete?
	public function getStandorte($searchString)
	{
		$this->load->model('organisation/Standort_model', 'StandortModel');

		$result = $this->StandortModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		} else {
			$this->outputJson(getData($result) ?: []);
		}
	}

	public function getFirmenliste($searchString)
	{
		$this->load->model('ressource/firma_model', 'FirmaModel');

		$result = $this->FirmaModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		} else {
			$this->outputJson(getData($result) ?: []);
		}
	}

	/**
	 * Get Array of Names of Ortschaften having plz
	 * @param string $plz Postleitzahl
	 * @return array $result[]
	 */
	public function getOrtschaften($plz, $gemeinde=null)
	{
		$this->load->model('person/Adresse_model', 'AdresseModel');

		//$ort = isset($ortschaft) ? $ortschaft : null;
		if(isset($gemeinde)) {
			$gemeinde = urldecode($gemeinde);
		}
		else
			$gemeinde = null;

		$result = $this->AdresseModel->getOrtschaften($plz, $gemeinde);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}
		elseif (!hasData($result)) {
			$this->outputJson($result);
		}
		else
		{
			$this->outputJsonSuccess(getData($result));
		}
	}

	/**
	 * Get Array of Names of Gemeinden having plz
	 * @param string $plz Postleitzahl
	 * @return array $result[]
	 */
	function getGemeinden($plz)
	{
		$this->load->model('person/Adresse_model', 'AdresseModel');

		$result = $this->AdresseModel->getGemeinden($plz);
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
			$this->outputJsonSuccess(getData($result));
		}
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

	public function loadContact($kontact_id)
	{
		$this->load->model('person/Kontakt_model', 'KontaktModel');

		$this->KontaktModel->addSelect('*');
		$this->KontaktModel->addJoin('public.tbl_standort st', 'ON (public.tbl_kontakt.standort_id = st.standort_id)', 'LEFT');
		$this->KontaktModel->addJoin('public.tbl_firma f', 'ON (f.firma_id = st.firma_id)', 'LEFT');

		$this->KontaktModel->addLimit(1);

		$result = $this->KontaktModel->loadWhere(
			array('kontakt_id' => $kontact_id)
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
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$this->load->model('person/Kontakt_model', 'KontaktModel');

		$firma_id = '';
		if (isset($_POST['firma_id']))
			$firma_id = $_POST('firma_id');

		$result = $this->KontaktModel->insert(
			[
				'person_id' => $person_id,
				'kontakttyp' =>  $_POST['kontakttyp'],
				'anmerkung' => $_POST['anmerkung'],
				'kontakt' => $_POST['kontakt'],
				'zustellung' => $_POST['zustellung'],
				'insertvon' => 'uid',
				'insertamum' => date('c'),
				'standort_id' => $_POST['standort_id'],
				'ext_id' => $_POST['ext_id']
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

		$result = $this->KontaktModel->update([
			'kontakt_id' => $kontakt_id
		],
			[
				'person_id' => $_POST['person_id'],
				'kontakttyp' =>  $_POST['kontakttyp'],
				'anmerkung' => $_POST['anmerkung'],
				'kontakt' => $_POST['kontakt'],
				'zustellung' => $_POST['zustellung'],
				'updatevon' => 'uid',
				'updateamum' => date('c'),
				'standort_id' => $_POST['standort_id'],
				'ext_id' => $_POST['ext_id']
			]);

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
			$this->outputJson($result); //success mit Wert null
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

		$result = $this->BankverbindungModel->update([
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
				'updatevon' => 'uid',
				'updateamum' => date('c'),
				'typ' => $_POST['typ'],
				'verrechnung' => $_POST['verrechnung'],
				'ext_id' => $_POST['ext_id'],
				'oe_kurzbz' => $_POST['oe_kurzbz'],
				'orgform_kurzbz' => $_POST['orgform_kurzbz']
			]);

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
			$this->outputJson($result); //success mit Wert null
		}
		return $this->outputJsonSuccess(current(getData($result)));
	}



}
