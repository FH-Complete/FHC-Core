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
			'person',
			'zeitsperren'
		]);

		// Load models
		$this->load->model('ressource/Zeitsperre_model', 'ZeitsperreModel');
		$this->load->model('ressource/Zeitsperretyp_model', 'ZeitsperretypModel');
		$this->load->model('ressource/Erreichbarkeit_model', 'ErreichbarkeitModel');
		$this->load->model('ressource/Stunde_model', 'StundeModel');
		$this->load->model('ressource/Zeitaufzeichnung_model', 'ZeitaufzeichnungModel');
	}

	public function getZeitsperrenUser($uid)
	{
		//check if $uid is passedUser
		$loggedInUser = getAuthUID();
		if($loggedInUser != $uid) {
			$this->load->library('PermissionLib');
			$isAdmin = $this->permissionlib->isBerechtigt('admin');
			if(!$isAdmin) {
				$this->terminateWithError($this->p->t('ui', 'noAdmin'), self::ERROR_TYPE_GENERAL);
			}
		}

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
		$this->ZeitsperreModel->addSelect(
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
		$loggedInUser = getAuthUID();

		if($mitarbeiter_uid != $loggedInUser)
			$this->terminateWithError($this->p->t('ui', 'noPermission'), self::ERROR_TYPE_GENERAL);

		$this->form_validation->set_rules('zeitsperretyp_kurzbz', 'Grund Zeitsperre', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Grund Zeitsperre'])
		]);

		$this->form_validation->set_rules('vondatum', 'VonDatum', 'required|is_valid_date', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'VonDatum']),
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'VonDatum'])
		]);

		$this->form_validation->set_rules('bisdatum', 'BisDatum', 'required|is_valid_date|callback_check_von_bis_datum|callback_check_diff_intval', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'BisDatum']),
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'BisDatum']),
			'check_von_bis_datum' => $this->p->t('zeitsperre', 'error_VonDatumGroesserAlsBisDatum'),
			'check_diff_intval' => $this->p->t('zeitsperre', 'error_zeitraumAuffallendHoch')
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

		//check if existing zeitsperre
		$result = $this->ZeitsperreModel->getSperreByDate($mitarbeiter_uid, $vondatum, $vonstunde, true);
		$data = $this->getDataOrTerminateWithError($result);

		if(hasData($result))
		{
			$this->terminateWithError($this->p->t('zeitsperren', 'error_existingZeitsperre', ['typ'=> current($data)->zeitsperretyp_kurzbz]), self::ERROR_TYPE_GENERAL);
		}

		//check if existing zeitaufzeichnung
		if(in_array($zeitsperretyp_kurzbz, Zeitsperre_model::BLOCKIERENDE_ZEITSPERREN))
		{
			$result = $this->ZeitsperreModel->existsZeitaufzeichnung($mitarbeiter_uid, $vondatum, $bisdatum);

			if(hasData($result))
				$this->terminateWithError($this->p->t('zeitsperren', 'error_existingZeitaufzeichnung'), self::ERROR_TYPE_GENERAL);
		}

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
				'insertvon' =>  $loggedInUser,
				'insertamum' => date('c'),
			]
		);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function update($zeitsperre_id)
	{
		//check if loggedin User is owner of the zeitsperre
		$loggedInUser = getAuthUID();
		$result = $this->ZeitsperreModel->load($zeitsperre_id);
		$data = $this->getDataOrTerminateWithError($result);
		$uid = current($data)->mitarbeiter_uid;

		if($uid != $loggedInUser)
			$this->terminateWithError($this->p->t('ui', 'noPermission'), self::ERROR_TYPE_GENERAL);

		if(!$zeitsperre_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Zeitsperre_id']), self::ERROR_TYPE_GENERAL);
		}
		//get current params
		$array_update = [
			'bezeichnung',
			'vondatum',
			'vonstunde',
			'bisdatum',
			'bisstunde',
			//	'vonISO', //Timestamp für Stunde
			//	'bisISO', //Timestamp für Stunde
			'erreichbarkeit_kurzbz',
			'vertretung_uid',
			'zeitsperretyp_kurzbz',
			'mitarbeiter_uid',
		];
		$post = $this->input->post();
		$update = [];

		foreach ($array_update as $prop)
		{
			if (array_key_exists($prop, $post))
			{
				$update[$prop] = $post[$prop];
			}
		}

		// Validation
		$rulesDefined = false;  //necessary, otherwise CI validation will always be triggered, even without rules
		foreach ($update as $key => $val) {
			switch ($key) {
				case 'zeitsperretyp_kurzbz':
					$this->form_validation->set_rules(
						$key,
						'Grund Zeitsperre',
						'required',
						['required' => $this->p->t('ui', 'error_fieldRequired', ['field'=>'Grund Zeitsperre'])]
					);
					$rulesDefined = true;
					break;
				case 'vondatum':
					$this->form_validation->set_rules(
						$key,
						'VonDatum',
						'required|is_valid_date',
						[
							'required' => $this->p->t('ui', 'error_fieldRequired', ['field'=>'VonDatum']),
							'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field'=>'VonDatum'])
						]
					);
					$rulesDefined = true;
					break;
				case 'bisdatum':
					$rules = 'required|is_valid_date';
					if (array_key_exists('vondatum', $update)) {
						$rules .= '|callback_check_von_bis_datum|callback_check_diff_intval';
					}
					$this->form_validation->set_rules(
						$key,
						'BisDatum',
						$rules,
						[
							'required' => $this->p->t('ui', 'error_fieldRequired', ['field'=>'BisDatum']),
							'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field'=>'BisDatum']),
							'check_von_bis_datum' => $this->p->t('zeitsperre', 'error_VonDatumGroesserAlsBisDatum'),
							'check_diff_intval' => $this->p->t('zeitsperre', 'error_zeitraumAuffallendHoch')
						]
					);
					$rulesDefined = true;
					break;
			}
		}

		if ($rulesDefined && $this->form_validation->run() == false) {
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		if(array_key_exists('vondatum', $post) || array_key_exists('bisdatum', $post))
		{
			$result = $this->ZeitsperreModel->load($zeitsperre_id);
			$data = $this->getDataOrTerminateWithError($result);
			$data = current($data);

			$mitarbeiter_uid = array_key_exists('mitarbeiter_uid', $post) ? $update['mitarbeiter_uid'] : $data->mitarbeiter_uid;
			$vondatum = array_key_exists('vondatum', $post) ? $update['vondatum'] : $data->vondatum;
			$bisdatum = array_key_exists('bisdatum', $post) ? $update['bisdatum'] : $data->bisdatum;
			$vonstunde = array_key_exists('vonstunde', $post) ? $update['vonstunde'] : $data->vonstunde;
			$zeitsperretyp_kurzbz = array_key_exists('zeitsperretyp_kurzbz', $post) ? $update['zeitsperretyp_kurzbz'] : $data->zeitsperretyp_kurzbz;

			$result = $this->ZeitsperreModel->getSperreByDate($mitarbeiter_uid, $vondatum, $vonstunde, true);
			$data = $this->getDataOrTerminateWithError($result);

			if(hasData($result))
			{
				$this->terminateWithError($this->p->t('zeitsperren', 'error_existingZeitsperre', ['typ'=> current($data)->zeitsperretyp_kurzbz]), self::ERROR_TYPE_GENERAL);
			}

			//check if existing zeitaufzeichnung
			if(in_array($zeitsperretyp_kurzbz, Zeitsperre_model::BLOCKIERENDE_ZEITSPERREN))
			{
				$result = $this->ZeitsperreModel->existsZeitaufzeichnung($mitarbeiter_uid, $vondatum, $bisdatum);

				if(hasData($result))
					$this->terminateWithError($this->p->t('zeitsperren', 'error_existingZeitaufzeichnung'), self::ERROR_TYPE_GENERAL);
			}
		}

		if (!empty($update)) {
			$update['updatevon'] = $loggedInUser;
			$update['updateamum'] = date('c');
			$result = $this->ZeitsperreModel->update($zeitsperre_id, $update);

			$data = $this->getDataOrTerminateWithError($result);

			$this->terminateWithSuccess($data);
		}
		else
			$this->terminateWithSuccess("no update");
	}

	public function delete($zeitsperre_id)
	{

		if (!is_numeric($zeitsperre_id) || (int)$zeitsperre_id <= 0)
		{
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'Zeitsperre_id']), self::ERROR_TYPE_GENERAL);
		}

		//check if loggedin User is owner of the zeitsperre
		$loggedInUser = getAuthUID();
		$result = $this->ZeitsperreModel->load($zeitsperre_id);
		$data = $this->getDataOrTerminateWithError($result);
		$uid = current($data)->mitarbeiter_uid;

		if($uid != $loggedInUser)
			$this->terminateWithError($this->p->t('ui', 'noPermission'), self::ERROR_TYPE_GENERAL);

		$result = $this->ZeitsperreModel->delete(
			array('zeitsperre_id' => $zeitsperre_id)
		);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function check_von_bis_datum($bisdatum)
	{
		$vondatum = $this->input->post('vondatum');

		return $vondatum <= $bisdatum;
	}

	public function check_diff_intval($bisdatum)
	{
		$vondatum = $this->input->post('vondatum');

		// Intervall in days
		$vonTs = strtotime($vondatum);
		$bisTs = strtotime($bisdatum);

		$tage = ($bisTs - $vonTs) / 86400;

		// if intervall > 14
		return $tage <= 14;
	}


}
