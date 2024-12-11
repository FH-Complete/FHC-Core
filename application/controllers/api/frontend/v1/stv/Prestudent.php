<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Prestudent extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'get' => ['admin:r', 'assistenz:r'],
			'updatePrestudent' =>  ['admin:rw', 'assistenz:rw'],
			'getHistoryPrestudents' => ['admin:r', 'assistenz:r'],
			'getBezeichnungZGV' => ['admin:r', 'assistenz:r'],
			'getBezeichnungDZgv' => ['admin:r', 'assistenz:r'],
			'getBezeichnungMZgv' => ['admin:r', 'assistenz:r'],
			'getAusbildung' => ['admin:r', 'assistenz:r'],
			'getAufmerksamdurch' => ['admin:r', 'assistenz:r'],
			'getBerufstaetigkeit' => ['admin:r', 'assistenz:r'],
			'getTypenStg' => ['admin:r', 'assistenz:r'],
			'getStudienplaene' => ['admin:r', 'assistenz:r'],
			'getStudiengang' => ['admin:r', 'assistenz:r']
		]);

		if ($this->router->method == 'updatePrestudent') {
			$prestudent_id = current(array_slice($this->uri->rsegments, 2));
			$this->checkPermissionsForPrestudent($prestudent_id, ['admin:rw', 'assistenz:rw']);
		} elseif ($this->router->method == 'get'
			|| $this->router->method == 'getStudienplaene'
			|| $this->router->method == 'getStudiengang'
		) {
			$prestudent_id = current(array_slice($this->uri->rsegments, 2));
			$this->checkPermissionsForPrestudent($prestudent_id, ['admin:r', 'assistenz:r']);
		} elseif ($this->router->method == 'getHistoryPrestudents') {
			$person_id = current(array_slice($this->uri->rsegments, 2));
			$this->checkPermissionsForPerson($person_id, ['admin:r', 'assistenz:r'], ['admin:r', 'assistenz:r']);
		}

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
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		// UDF
		$this->load->library('UDFLib');
		
		$result = $this->udflib->getCiValidations($this->PrestudentModel, $this->input->post());
		$udf_field_validations = $this->getDataOrTerminateWithError($result);

		//Form validation
		$this->load->library('form_validation');

		$this->form_validation->set_rules($udf_field_validations);

		$this->form_validation->set_rules('priorisierung', 'Priorisierung', 'numeric', [
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Priorisierung'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

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

		// add UDFs
		$result = $this->udflib->getDefinitionForModel($this->PrestudentModel);

		$definitions = $this->getDataOrTerminateWithError($result);

		foreach ($definitions as $def)
			$array_allowed_props_prestudent[] = $def['name'];

		$update_prestudent = array();
		foreach ($array_allowed_props_prestudent as $prop)
		{
			$val = $this->input->post($prop);
			if ($val !== null || $prop == 'foerderrelevant') {
				$update_prestudent[$prop] = $val;
			}
		}

		$update_prestudent['updateamum'] = date('c');
		$update_prestudent['updatevon'] = $uid;

		if (count($update_prestudent))
		{
			$result = $this->PrestudentModel->update(
				$prestudent_id,
				$update_prestudent
			);
			$this->getDataOrTerminateWithError($result);

			return $this->terminateWithSuccess(true);
		}
		return $this->terminateWithSuccess(false);
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
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');
		$result = $this->StudienplanModel->getStudienplaeneByPrestudents($prestudent_id);

		$data = $this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess($data);
	}

	/**
	 * Gets details for the Studiengang of the Prestudent
	 *
	 * @param integer					$prestudent_id
	 *
	 * @return stdClass
	 */
	public function getStudiengang($prestudent_id)
	{
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$this->PrestudentModel->addSelect('stg.*');

		$this->PrestudentModel->addJoin('public.tbl_studiengang stg', 'studiengang_kz');

		$result = $this->PrestudentModel->load($prestudent_id);

		$stg = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($stg));
	}
}
