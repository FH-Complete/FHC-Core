<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Prestudent extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'get' => ['admin:r', 'assistenz:r'],
			'updatePrestudent' =>  ['admin:w', 'assistenz:w'],
			'getHistoryPrestudents' => ['admin:r', 'assistenz:r'],
			'getBezeichnungZGV' => self::PERM_LOGGED,
			'getBezeichnungDZgv' => self::PERM_LOGGED,
			'getBezeichnungMZgv' => self::PERM_LOGGED,
			'getAusbildung' => self::PERM_LOGGED,
			'getAufmerksamdurch' => self::PERM_LOGGED,
			'getBerufstaetigkeit' => self::PERM_LOGGED,
			'getTypenStg' => self::PERM_LOGGED,
			'getStudienplaene' => self::PERM_LOGGED
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		// Load language phrases
		$this->loadPhrases([
			'ui', 'studierendenantrag', 'lehre'
		]);
	}

	public function get($prestudent_id)
	{
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$this->PrestudentModel->addSelect('*');
		$result = $this->PrestudentModel->loadWhere(['prestudent_id' => $prestudent_id]);

		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		if(!hasData($result))
		{
			return show_404();
		}
		$this->terminateWithSuccess(current(getData($result)));
	}

	public function updatePrestudent($prestudent_id)
	{
		$this->load->library('form_validation');
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		//get Studiengang von prestudent_id
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$result = $this->PrestudentModel->load([
			'prestudent_id'=> $prestudent_id,
		]);
		if(isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$result = current(getData($result));

		$stg = $result->studiengang_kz;

		if(!$this->permissionlib->isBerechtigt('admin', 'suid', $stg) && !$this->permissionlib->isBerechtigt('assistenz', 'suid', $stg))
		{
			return $this->terminateWithError($this->p->t('lehre','error_keineSchreibrechte'), self::ERROR_TYPE_GENERAL);
		}

		//Form validation
		$this->form_validation->set_rules('priorisierung', 'Priorisierung', 'numeric', [
			'numeric' => $this->p->t('ui','error_fieldNotNumeric',['field' => 'Priorisierung'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$deltaData = $_POST;

		$uid = getAuthUID();

		$array_allowed_props_prestudent = [
			'aufmerksamdurch_kurzbz',
			'studiengang_kz',
			'gsstudientyp_kurzbz',
			'person_id',
			'berufstaetigkeit_code',
			'ausbildungcode',
			'zgv_code',
			'zgvort',
			'zgvdatum',
			'zgvnation',
			'zgvmas_code',
			'zgvmaort',
			'zgvmadatum',
			'zgvmanation',
			'facheinschlberuf',
			'bismelden',
			'anmerkung',
			'dual',
			'zgvdoktor_code',
			'zgvdoktorort',
			'zgvdoktordatum',
			'zgvdoktornation',
			'aufnahmegruppe_kurzbz',
			'priorisierung',
			'foerderrelevant',
			'zgv_erfuellt',
			'zgvmas_erfuellt',
			'zgvdoktor_erfuellt',
			'mentor',
			'aufnahmeschluessel',
			'standort_code'
		];

		$update_prestudent = array();
		foreach ($array_allowed_props_prestudent as $prop)
		{
			$val = isset($deltaData[$prop]) ? $deltaData[$prop] : null;
			if ($val !== null || $prop == 'foerderrelevant') {
				$update_prestudent[$prop] = $val;
			}
		}

		$update_prestudent['updateamum'] = date('c');
		$update_prestudent['updatevon'] = $uid;

		//utf8-decode for special chars (eg tag der offenen Tür, FH-Führer)
		function utf8_decode_if_string($value)
		{
			if (is_string($value)) {
				return utf8_decode($value);
			} else {
				return $value;
			}
		}
		$update_prestudent_encoded = array_map('utf8_decode_if_string', $update_prestudent);

		if (count($update_prestudent))
		{
			$result = $this->PrestudentModel->update(
				$prestudent_id,
				$update_prestudent_encoded
			);
			if (isError($result)) {
				$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
			return $this->terminateWithSuccess(true);
		}
	}

	public function getHistoryPrestudents($person_id)
	{
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$result = $this->PrestudentModel->getHistoryPrestudents($person_id);
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getBezeichnungZGV()
	{
		$this->load->model('codex/Zgv_model', 'ZgvModel');

		$this->ZgvModel->addOrder('zgv_code');

		$result = $this->ZgvModel->load();
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getBezeichnungDZgv()
	{
		$this->load->model('codex/Zgvdoktor_model', 'ZgvdoktorModel');

		$this->ZgvdoktorModel->addOrder('zgvdoktor_code');

		$result = $this->ZgvdoktorModel->load();
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getBezeichnungMZgv()
	{
		$this->load->model('codex/Zgvmaster_model', 'ZgvmasterModel');

		$this->ZgvmasterModel->addOrder('zgvmas_code');

		$result = $this->ZgvmasterModel->load();
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getAusbildung()
	{
		$this->load->model('codex/Ausbildung_model', 'AusbildungModel');

		$this->AusbildungModel->addOrder('ausbildungcode');

		$result = $this->AusbildungModel->load();
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getAufmerksamdurch()
	{
		$this->load->model('codex/Aufmerksamdurch_model', 'AufmerksamdurchModel');

		$this->AufmerksamdurchModel->addOrder('aufmerksamdurch_kurzbz');

		$result = $this->AufmerksamdurchModel->load();
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getBerufstaetigkeit()
	{
		$this->load->model('codex/Berufstaetigkeit_model', 'BerufstaetigkeitModel');

		$this->BerufstaetigkeitModel->addOrder('berufstaetigkeit_code');

		$result = $this->BerufstaetigkeitModel->load();
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getTypenStg()
	{
		$this->load->model('education/Gsstudientyp_model', 'GsstudientypModel');

		$this->GsstudientypModel->addOrder('gsstudientyp_kurzbz');

		$result = $this->GsstudientypModel->load();
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getStudienplaene($prestudent_id)
	{
		if (!is_int($prestudent_id))
			show_404();
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');
		$result = $this->StudienplanModel->getStudienplaeneByPrestudents($prestudent_id);

		$data = $this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess($data);
	}
}
