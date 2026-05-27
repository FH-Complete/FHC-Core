<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Aufnahmetermine extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getAufnahmetermine' => ['admin:r', 'assistenz:r'],
			'loadAufnahmetermin' => ['admin:r', 'assistenz:r'],
			'insertAufnahmetermin' => ['admin:rw', 'assistenz:rw'],
			'updateAufnahmetermin' => ['admin:rw', 'assistenz:rw'],
			'deleteAufnahmetermin' => ['admin:rw', 'assistenz:rw'],
			'getListPlacementTests' => ['admin:r', 'assistenz:r'],
			'getListStudyPlans' => ['admin:r', 'assistenz:r'],
			'loadDataRtPrestudent' => ['admin:r', 'assistenz:r'],
			'insertOrUpdateDataRtPrestudent' => ['admin:r', 'assistenz:r'],
			'loadAufnahmegruppen' => ['admin:r', 'assistenz:r'],
			'getResultReihungstest' => ['admin:r', 'assistenz:r'],
			'getZukuenftigeReihungstestStg' => ['admin:r', 'assistenz:r'],
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');

		// Load language phrases
		$this->loadPhrases([
			'ui',
			'admission'
		]);

		// Load models
		$this->load->model('crm/Reihungstest_model', 'ReihungstestModel');
		$this->load->model('crm/RtPerson_model', 'RtPersonModel');
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');
		$this->load->model('organisation/Studienordnung_model', 'StudienordnungModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
	}

	public function getAufnahmetermine($person_id)
	{
		$result = $this->ReihungstestModel->getReihungstestPerson($person_id);
		$arrayRt = $this->getDataOrTerminateWithError($result);

		foreach ($arrayRt as $item) {
			//Studienplan
			$result = $this->StudienplanModel->loadWhere([
				'studienplan_id' => $item->studienplan_id
			]);
			$data = $this->getDataOrTerminateWithError($result);
			$studienordnung_id_ber = current($data)->studienordnung_id;

			//Studienordnung
			$result = $this->StudienordnungModel->loadWhere([
				'studienordnung_id' => $studienordnung_id_ber
			]);
			$data = $this->getDataOrTerminateWithError($result);
			$studiengang_kz_ber = current($data)->studiengang_kz;

			//Studiengang von studiengang_kz_ber
			$result = $this->StudiengangModel->load($studiengang_kz_ber);
			$data = $this->getDataOrTerminateWithError($result);

			$studiengangkurzbzlang_ber = current($data)->kurzbzlang;
			$typ_ber = current($data)->typ;

			//add to Array
			$item->studiengang_kz_ber = $studiengang_kz_ber;
			$item->studiengangkurzbzlang_ber = $studiengangkurzbzlang_ber;
			$item->studiengangtyp_ber = $typ_ber;
		}
		$this->terminateWithSuccess($arrayRt);
	}

	public function insertAufnahmetermin()
	{
		$this->load->library('form_validation');
		$authUID = getAuthUID();

		$formData = $this->input->post('formData');
		$person_id = $this->input->post('person_id');

		if(!$person_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Person ID']), self::ERROR_TYPE_GENERAL);
		}

		$rt_id = (isset($formData['rt_id']) && !empty($formData['rt_id'])) ? $formData['rt_id'] : null;
		$anmeldedatum =	(isset($formData['anmeldedatum']) && !empty($formData['anmeldedatum'])) ? $formData['anmeldedatum'] : null;
		$teilgenommen =	(isset($formData['teilgenommen']) && !empty($formData['teilgenommen'])) ? $formData['teilgenommen'] : false;
		$studienplan_id = (isset($formData['studienplan_id']) && !empty($formData['studienplan_id'])) ? $formData['studienplan_id'] : null;
		$punkte = (isset($formData['punkte']) && !empty($formData['punkte'])) ? $formData['punkte'] : null;

		//validation if there is already an RT with chosen data existing
		$result = $this->RtPersonModel->loadWhere(
			array(
				'rt_id' => $rt_id,
				'person_id' => $person_id,
				'studienplan_id' => $studienplan_id,
				)
		);
		$data = getData($result);
		if($data)
			return $this->terminateWithError("Error", self::ERROR_TYPE_GENERAL);

		$this->form_validation->set_data($formData);

		$this->form_validation->set_rules('punkte', 'Punkte', 'numeric', [
			'required' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Punkte'])
		]);
		$this->form_validation->set_rules('studienplan_id', 'studienplan_id', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Studienplan'])
		]);
		$this->form_validation->set_rules('rt_id', 'Reihungstest_id', 'required', [
			'is_valid_date' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Reihungstest'])
		]);
		$this->form_validation->set_rules('anmeldedatum', 'AnmeldeDatum', 'is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'Anmeldedatum'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->RtPersonModel->insert([
			'person_id' => $person_id,
			'rt_id' => $rt_id,
			'anmeldedatum' => $anmeldedatum,
			'teilgenommen' => $teilgenommen,
			'studienplan_id' => $studienplan_id,
			'punkte' => $punkte,
			'insertamum' => date('c'),
			'insertvon' => $authUID,
		]);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function loadAufnahmetermin($rt_person_id)
	{
		$result = $this->RtPersonModel->loadWhere(
			array('rt_person_id' => $rt_person_id)
		);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($data));
	}

	public function updateAufnahmetermin()
	{
		$this->load->library('form_validation');
		$authUID = getAuthUID();

		$formData = $this->input->post('formData');
		$rt_person_id = $this->input->post('rt_person_id');
		$person_id = (isset($formData['person_id']) && !empty($formData['person_id'])) ? $formData['person_id'] : null;


		if(!$person_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Person ID']), self::ERROR_TYPE_GENERAL);
		}
		if(!$rt_person_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'RT_Person ID']), self::ERROR_TYPE_GENERAL);
		}

		$rt_id =	(isset($formData['rt_id']) && !empty($formData['rt_id'])) ? $formData['rt_id'] : null;
		$anmeldedatum =	(isset($formData['anmeldedatum']) && !empty($formData['anmeldedatum'])) ? $formData['anmeldedatum'] : null;
		$teilgenommen =	(isset($formData['teilgenommen']) && !empty($formData['teilgenommen'])) ? $formData['teilgenommen'] : false;
		$studienplan_id = (isset($formData['studienplan_id']) && !empty($formData['studienplan_id'])) ? $formData['studienplan_id'] : null;
		$punkte = (isset($formData['punkte']) && !empty($formData['punkte'])) ? $formData['punkte'] : null;

		$this->form_validation->set_data($formData);

		$this->form_validation->set_rules('punkte', 'Punkte', 'numeric', [
			'required' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Punkte'])
		]);
		$this->form_validation->set_rules('studienplan_id', 'studienplan_id', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Studienplan'])
		]);
		$this->form_validation->set_rules('rt_id', 'Reihungstest_id', 'required', [
			'is_valid_date' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Reihungstest'])
		]);

		$this->form_validation->set_rules('anmeldedatum', 'AnmeldeDatum', 'is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'Anmeldedatum'])
		]);


		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->RtPersonModel->update(
			[
				'rt_person_id' => $rt_person_id,
			],
			[
				'rt_id' => $rt_id,
				'anmeldedatum' => $anmeldedatum,
				'teilgenommen' => $teilgenommen,
				'studienplan_id' => $studienplan_id,
				'punkte' => $punkte,
				'insertamum' => date('c'),
				'insertvon' => $authUID,
			]
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function deleteAufnahmetermin($rt_person_id)
	{
		$result = $this->RtPersonModel->delete(
			array('rt_person_id' => $rt_person_id)
		);

		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);
	}

	public function getListPlacementTests($prestudent_id)
	{
		if(!$prestudent_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Prestudent ID']), self::ERROR_TYPE_GENERAL);
		}

		//get studienplan array
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$this->PrestudentstatusModel->addSelect('*');
		$this->PrestudentstatusModel->addSelect('sp.studienplan_id');

		$this->PrestudentstatusModel->addJoin('lehre.tbl_studienplan sp', 'studienplan_id', 'LEFT');

		$result = $this->PrestudentstatusModel->loadWhere(
			array(
				'prestudent_id' => $prestudent_id,
				'status_kurzbz' => 'Interessent'
			)
		);

		//check if existing placementtest
		if(!hasData($result))
			$this->terminateWithSuccess([]);
		else
			$data = getData($result);

		$studienplan_arr = [];
		$include_ids = [];
		foreach ($data as $item)
		{
			if($item->studienplan_id != null)
				$studienplan_arr[] = $item->studienplan_id;
		}
		if(!hasData($studienplan_arr))
			$this->terminateWithSuccess([]);

		//get Placementtests Person
		$person_id = $this->_getPersonId($prestudent_id);
		$resultRt = $this->ReihungstestModel->getReihungstestPerson($person_id);

		//check if existing placementtest
		if(!hasData($result))
			$this->terminateWithSuccess([]);
		else
			$dataRt = getData($resultRt);

		foreach ($dataRt as $item)
		{
			if(!in_array($item->studienplan_id, $studienplan_arr))
				$studienplan_arr[] = $item->studienplan_id;
			if(!in_array($item->rt_id, $include_ids) && ($item->rt_id != null))
				$include_ids[] = $item->rt_id;
		}

		$result = $this->ReihungstestModel->getReihungstestByStudyPlanAndIds($studienplan_arr, $include_ids);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getListStudyPlans($person_id)
	{
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');

		$result = $this->StudienplanModel->getStudienplaeneForPerson($person_id);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function loadDataRtPrestudent($prestudent_id)
	{
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$this->PrestudentModel->addSelect(["reihungstestangetreten"]);
		$this->PrestudentModel->addSelect(["rt_gesamtpunkte"]);
		$this->PrestudentModel->addSelect(["aufnahmegruppe_kurzbz"]);
		$result = $this->PrestudentModel->loadWhere(
			array('prestudent_id' => $prestudent_id)
		);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($data));
	}

	public function insertOrUpdateDataRtPrestudent()
	{
		$this->load->library('form_validation');
		$authUID = getAuthUID();

		$formData = $this->input->post('formData');
		$prestudent_id = $this->input->post('prestudent_id');

		if(!$prestudent_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Prestudent ID']), self::ERROR_TYPE_GENERAL);
		}
		$rt_gesamtpunkte =
			(isset($formData['rt_gesamtpunkte']) && !empty($formData['rt_gesamtpunkte']))
				? $formData['rt_gesamtpunkte']
				: null;
		$reihungstestangetreten =
			(isset($formData['reihungstestangetreten']) && !empty($formData['reihungstestangetreten']))
				? $formData['reihungstestangetreten']
				: false;
		$aufnahmegruppe_kurzbz =
			(isset($formData['aufnahmegruppe_kurzbz']) && !empty($formData['aufnahmegruppe_kurzbz']))
				? $formData['aufnahmegruppe_kurzbz']
				: null;

		$this->form_validation->set_data($formData);

		$this->form_validation->set_rules('rt_gesamtpunkte', 'Rt_gesamtpunkte', 'numeric', [
			'required' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Rt_gesamtpunkte'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$result = $this->PrestudentModel->update(
			[
				'prestudent_id' => $prestudent_id,
			],
			[
				'reihungstestangetreten' => $reihungstestangetreten,
				'rt_gesamtpunkte' => $rt_gesamtpunkte,
				'aufnahmegruppe_kurzbz' => $aufnahmegruppe_kurzbz,
				'updateamum' => date('c'),
				'updatevon' => $authUID,
			]
		);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function loadAufnahmegruppen()
	{
		$uid = $this->input->get('uid');
		$studiensemester_kurzbz = $this->input->get('studiensemester_kurzbz');

		$this->load->model('person/Benutzergruppe_model', 'BenutzergruppeModel');

		$result = $this->BenutzergruppeModel->loadAufnahmegruppen($uid, $studiensemester_kurzbz);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(($data));
	}

	public function getResultReihungstest()
	{
		$person_id = $this->input->get('person_id');
		$punkte = $this->input->get('punkte');
		$reihungstest_id = $this->input->get('reihungstest_id');
		$has_excluded_gebiete = $this->input->get('hasExcludedAreas');

		if(!$reihungstest_id)
		{
			$this->terminateWithSuccess(null);
		}

		//for gewichtung
		$studiengang_kz = $this->input->get('studiengang_kz');

		$this->load->model('testtool/Ablauf_model', 'AblaufModel');
		$result = $this->AblaufModel->getAblaufGebieteAndGewichte($studiengang_kz, 1);
		$data = $this->getDataOrTerminateWithError($result);

		$weightedArray = [];
		$basis_gebiet_id_arr = [];
		$basis_gebiet_id_toString = '';
		foreach ($data as $abl)
		{
			$weightedArray[$abl->gebiet_id] = $abl->gewicht;
			$basis_gebiet_id_arr[]= $abl->gebiet_id;
		}
		$basis_gebiet_id_toString = implode(', ', $basis_gebiet_id_arr);

		$result = $this->ReihungstestModel->getReihungstestErgebnisPerson(
			$person_id,
			$punkte,
			$reihungstest_id,
			$weightedArray,
			$has_excluded_gebiete,
			$basis_gebiet_id_toString
		);
		$this->terminateWithSuccess($result);
	}

	public function getZukuenftigeReihungstestStg()
	{
		$studiengang_kz = $this->input->get('studiengang_kz');
		if(!$studiengang_kz)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Studiengang_kz']), self::ERROR_TYPE_GENERAL);
		}

		$result = $this->ReihungstestModel->getZukuenftigeReihungstestStg($studiengang_kz);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	private function _getPersonId($prestudent_id)
	{
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$result = $this->PrestudentModel->loadWhere(
			['prestudent_id' => $prestudent_id]
		);

		$data = $this->getDataOrTerminateWithError($result);
		$person = current($data);

		return $person->person_id;
	}
}
