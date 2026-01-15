<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Zeitsperren extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getZeitsperrenUser' => self::PERM_LOGGED,
			'getTypenZeitsperren' => self::PERM_LOGGED,
			'getTypenErreichbarkeit' => self::PERM_LOGGED,
			'getStunden' => self::PERM_LOGGED,
			'loadZeitsperre' => self::PERM_LOGGED,
			'add' => self::PERM_LOGGED,
			'update' => self::PERM_LOGGED,
			'delete' => self::PERM_LOGGED,
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
		$this->load->model('ressource/Zeitsperre_model', 'ZeitsperreModel');
		$this->load->model('ressource/Zeitsperretyp_model', 'ZeitsperretypModel');
		$this->load->model('ressource/Erreichbarkeit_model', 'ErreichbarkeitModel');
		$this->load->model('ressource/Stunde_model', 'StundeModel');
	}

	public function getZeitsperrenUser($uid)
	{
		$result = $this->ZeitsperreModel->getZeitsperrenUser($uid);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function getTypenZeitsperren()
	{
		$this->ZeitsperretypModel->addOrder('beschreibung', 'ASC');
		$result = $this->ZeitsperretypModel->load();
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function getTypenErreichbarkeit()
	{
		$result = $this->ErreichbarkeitModel->load();
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function getStunden()
	{
		$this->StundeModel->addOrder('stunde', 'ASC');
		$result = $this->StundeModel->load();
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function loadZeitsperre($zeitsperre_id)
	{
		$result = $this->ZeitsperreModel->addSelect(
			'campus.tbl_zeitsperre.*, typ.*,
			ma.person_id AS ma_person_id, ma.vorname AS ma_vorname, ma.nachname AS ma_nachname,
			ma.titelpre AS ma_titelpre, ma.titelpost AS ma_titelpost'
		);
		$this->ZeitsperreModel->addJoin('campus.tbl_zeitsperretyp typ', 'ON (typ.zeitsperretyp_kurzbz = campus.tbl_zeitsperre.zeitsperretyp_kurzbz)');
		$this->ZeitsperreModel->addJoin('public.tbl_benutzer ben', 'ON (ben.uid = campus.tbl_zeitsperre.vertretung_uid)', 'LEFT');
		$this->ZeitsperreModel->addJoin('public.tbl_person ma', 'ON (ma.person_id = ben.person_id)', 'LEFT');
		$result = $this->ZeitsperreModel->loadWhere(
			array('zeitsperre_id' => $zeitsperre_id)
		);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess((current(getData($result)) ?: []));
	}

	public function add($mitarbeiter_uid)
	{
		$this->form_validation->set_rules('zeitsperretyp_kurzbz', 'Grund Zeitsperre', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Grund Zeitsperre'])
		]);

		$this->form_validation->set_rules('vondatum', 'VON-DATUM', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'VON-DATUM'])
		]);

		$this->form_validation->set_rules('bisdatum', 'BIS-DATUM', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'BIS-DATUM'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$bezeichnung = $this->input->post('bezeichnung');
		$vondatum = $this->input->post('vondatum');
		$vonstunde = $this->input->post('vonstunde');
		$bisdatum = $this->input->post('bisdatum');
		$bisstunde = $this->input->post('bisstunde');
		//$vonIso = $this->input->post('vonISO'); //Timestamp für Stunde
		//$bisIso = $this->input->post('bisISO'); //Timestamp für Stunde
		$erreichbarkeit_kurzbz = $this->input->post('erreichbarkeit_kurzbz');
		$vertretung_uid = $this->input->post('vertretung_uid');
		$zeitsperretyp_kurzbz = $this->input->post('zeitsperretyp_kurzbz');

	//	$this->terminateWithError("for later: Stunden Timestamps: VON " . $vonIso . " BIS " . $bisIso, self::ERROR_TYPE_GENERAL);


		$uid = getAuthUID();

		$result = $this->ZeitsperreModel->insert(
			[
				'mitarbeiter_uid' => $mitarbeiter_uid,
				'bezeichnung' => $bezeichnung,
				'vondatum' => $vondatum,
				'vonstunde' => $vonstunde,
				'bisdatum' => $bisdatum,
				'bisstunde' => $bisstunde,
				'erreichbarkeit_kurzbz' => $erreichbarkeit_kurzbz,
				'zeitsperretyp_kurzbz' => $zeitsperretyp_kurzbz,
				'vertretung_uid' => $vertretung_uid,
				'insertvon' =>  $uid,
				'insertamum' => date('c'),
			]
		);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function update()
	{

	}

	public function delete($zeitsperre_id)
	{
		if (!is_numeric($zeitsperre_id) || (int)$zeitsperre_id <= 0)
		{
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'Zeitsperre_id']), self::ERROR_TYPE_GENERAL);
		}
		$result = $this->ZeitsperreModel->delete(
			array('zeitsperre_id' => $zeitsperre_id)
		);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess((getData($result) ?: []));
	}

}
