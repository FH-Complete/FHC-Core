<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class Kalender extends FHCAPI_Controller
{

	private $_ci;
	const ALLOWED_PLAN_FILTER = ['ort', 'uid', 'stg'];
	const ALLOWED_ROOM_FILTER = ['lehreinheit_id', 'kalender_id'];

	const ALLOWED_TO_UPDATE = ['start_time', 'end_time', 'ort_kurzbz'];
	/**
	 * Object initialization
	 */
	public function __construct()
	{

		parent::__construct([
			'getStunden' => self::PERM_LOGGED,
			'getPlan' => self::PERM_LOGGED,
			'getPlanNew' => self::PERM_LOGGED,
			'getPlanByOrt' => self::PERM_LOGGED,
			'getRaumvorschlag' => self::PERM_LOGGED,
			'getZeitwuensche' => self::PERM_LOGGED,
			'getZeitsperren' => self::PERM_LOGGED,
			'updateKalenderEvent' => 'lehre/lvplan:rw',
			'addKalenderEvent' => 'lehre/lvplan:rw'
		]);

		$this->_ci =& get_instance();

		$this->_ci->load->library('LogLib');
		$this->_ci->load->library('form_validation');
		$this->_ci->load->library('KalenderLib');
		$this->loadPhrases([
			'ui'
		]);


		$this->_ci->loglib->setConfigs(array(
			'classIndex' => 5,
			'functionIndex' => 5,
			'lineIndex' => 4,
			'dbLogType' => 'API', // required
			'dbExecuteUser' => 'RESTful API'
		));
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods
	/**
	 * fetches Stunden layout from database
	 * @access public
	 *
	 */
	public function getStunden()
	{
		$this->load->model('ressource/Stunde_model', 'StundeModel');

		$this->_ci->StundeModel->addOrder('stunde', 'ASC');
		$stunden = $this->_ci->StundeModel->load();

		$stunden = $this->getDataOrTerminateWithError($stunden);

		$this->terminateWithSuccess($stunden);
	}
	public function getPlan()
	{
		$this->_ci->form_validation->set_data($_GET);
		$this->_ci->form_validation->set_rules('start_date',"start_date","required");
		$this->_ci->form_validation->set_rules('end_date',"end_date","required");

		if($this->_ci->form_validation->run() === FALSE)
			$this->terminateWithValidationErrors($this->_ci->form_validation->error_array());

		$start_date = $this->_ci->input->get('start_date', TRUE);
		$end_date = $this->_ci->input->get('end_date', TRUE);

		$filter = $this->_checkFilter(self::ALLOWED_PLAN_FILTER);

		$stundenplan_data = $this->_ci->kalenderlib->getPlan(
			$start_date,
			$end_date,
			isset($filter->ort) ? $filter->ort : null,
			isset($filter->uid) ? $filter->uid : null,
			isset($filter->stg) ? $filter->stg : null
		);

		$this->terminateWithSuccess($stundenplan_data);
	}

	public function getPlanNew()
	{
		$this->_ci->form_validation->set_data($_GET);
		$this->_ci->form_validation->set_rules('start_date',"start_date","required");
		$this->_ci->form_validation->set_rules('end_date',"end_date","required");

		if($this->_ci->form_validation->run() === FALSE)
			$this->terminateWithValidationErrors($this->_ci->form_validation->error_array());

		$start_date = $this->_ci->input->get('start_date', TRUE);
		$end_date = $this->_ci->input->get('end_date', TRUE);

		$filter = $this->_checkFilter(self::ALLOWED_PLAN_FILTER);

		$stundenplan_data = $this->_ci->kalenderlib->getPlanNew(
			$start_date,
			$end_date,
			isset($filter->ort) ? $filter->ort : null,
			isset($filter->uid) ? $filter->uid : null,
			isset($filter->stg) ? $filter->stg : null
		);

		$this->terminateWithSuccess($stundenplan_data);
	}

	public function getPlanByOrt($start_date = null, $end_date = null, $ort = null)
	{
		if (!isset($start_date) || !isset($end_date) || !isset($ort))
		{
			$this->_ci->form_validation->set_data($_GET);
			$this->_ci->form_validation->set_rules('start_date',"start_date","required");
			$this->_ci->form_validation->set_rules('end_date',"end_date","required");
			$this->_ci->form_validation->set_rules('ort',"ort","required");
			if($this->_ci->form_validation->run() === FALSE)
				$this->terminateWithValidationErrors($this->_ci->form_validation->error_array());

			$start_date = $this->_ci->input->get('start_date', TRUE);
			$end_date = $this->_ci->input->get('end_date', TRUE);
			$ort = $this->_ci->input->get('ort', TRUE);
		}

		$this->terminateWithSuccess($this->_ci->kalenderlib->getPlanByOrt($start_date, $end_date, $ort));
	}

	public function getZeitsperren()
	{
		$this->_ci->form_validation->set_data($_GET);
		$this->_ci->form_validation->set_rules('start_date',"start_date","required");
		$this->_ci->form_validation->set_rules('end_date',"end_date","required");
		$this->_ci->form_validation->set_rules('emp',"emp","required");
		if($this->_ci->form_validation->run() === FALSE)
			$this->terminateWithValidationErrors($this->_ci->form_validation->error_array());

		$start_date = $this->_ci->input->get('start_date', TRUE);
		$end_date = $this->_ci->input->get('end_date', TRUE);
		$emp = $this->_ci->input->get('emp', TRUE);

		$stundenplan_data = $this->_ci->kalenderlib->getZeitsperren($start_date, $end_date, $emp);

		$this->terminateWithSuccess($stundenplan_data);
	}

	public function getZeitwuensche()
	{
		$this->_ci->form_validation->set_data($_GET);
		$this->_ci->form_validation->set_rules('start_date',"start_date","required");
		$this->_ci->form_validation->set_rules('end_date',"end_date","required");
		$this->_ci->form_validation->set_rules('emp',"emp","required");

		if($this->_ci->form_validation->run() === FALSE)
			$this->terminateWithValidationErrors($this->_ci->form_validation->error_array());

		$start_date = $this->_ci->input->get('start_date', TRUE);
		$end_date = $this->_ci->input->get('end_date', TRUE);
		$emp = $this->_ci->input->get('emp', TRUE);

		$stundenplan_data = $this->_ci->kalenderlib->getZeitwuensche($start_date, $end_date, $emp);

		$this->terminateWithSuccess($stundenplan_data);
	}

	public function updateKalenderEvent()
	{
		$this->_ci->form_validation->set_data($_POST);
		$this->_ci->form_validation->set_rules('kalender_id',"kalender_id","required");

		if($this->_ci->form_validation->run() === FALSE)
			$this->terminateWithValidationErrors($this->_ci->form_validation->error_array());

		$updateFields = $this->_checkUpdate($this->_ci->input->post('updatedInfos', TRUE));
		$kalender_id = $this->_ci->input->post('kalender_id', TRUE);

		if (isset($updateFields->ort_kurzbz) && !isEmptyString($updateFields->ort_kurzbz))
		{
			$result = $this->_ci->kalenderlib->updateOrt($kalender_id, $updateFields->ort_kurzbz);

			if (isError($result))
				$this->terminateWithError(getError($result));
		}

		if (isset($updateFields->start_time) || isset($updateFields->end_time))
		{
			$result = $this->_ci->kalenderlib->updateZeit($kalender_id, $updateFields->start_time, $updateFields->end_time);

			if (isError($result))
				$this->terminateWithError(getError($result));
		}

		$this->terminateWithSuccess('Erfolgreich');
	}

	public function getRaumvorschlag()
	{
		$this->_ci->form_validation->set_data($_GET);
		$this->_ci->form_validation->set_rules('start_date',"start_date","required");
		$this->_ci->form_validation->set_rules('end_date',"end_date","required");

		if($this->_ci->form_validation->run() === FALSE)
			$this->terminateWithValidationErrors($this->_ci->form_validation->error_array());

		$start_date = $this->_ci->input->get('start_date', TRUE);
		$end_date = $this->_ci->input->get('end_date', TRUE);

		$filter = $this->_checkFilter(self::ALLOWED_ROOM_FILTER);

		if (isset($filter->lehreinheit_id))
		{
			$result = $this->_ci->kalenderlib->getRaumvorschlagByLehreinheitID(
				$start_date,
				$end_date,
				$filter->lehreinheit_id
			);
		}

		if (isset($filter->kalender_id))
		{
			$result = $this->_ci->kalenderlib->getRaumvorschlagByKalenderID(
				$start_date,
				$end_date,
				$filter->kalender_id
			);
		}

		if (isError($result))
			$this->terminateWithError(getError($result));

		$this->terminateWithSuccess(getData($result));
	}

	public function addKalenderEvent()
	{
		$this->_ci->form_validation->set_data($_POST);
		$this->_ci->form_validation->set_rules('lehreinheit_id',"lehreinheit_id","required");
		$this->_ci->form_validation->set_rules('start_date',"start_date","required");
		$this->_ci->form_validation->set_rules('end_date',"end_date","required");

		if($this->_ci->form_validation->run() === FALSE)
			$this->terminateWithValidationErrors($this->_ci->form_validation->error_array());

		$lehreinheit_id = $this->_ci->input->post('lehreinheit_id', TRUE);
		$ort_kurzbz = $this->_ci->input->post('ort_kurzbz', TRUE);
		$start_date = $this->_ci->input->post('start_date', TRUE);
		$end_date = $this->_ci->input->post('end_date', TRUE);


		$result = $this->_ci->kalenderlib->addKalenderEvent($start_date, $end_date, $lehreinheit_id, $ort_kurzbz);

		if (isError($result))
			$this->terminateWithError(getError($result));

		$this->terminateWithSuccess('Erfolgreich');
	}

	private function _checkFilter($filters)
	{
		$filter_valid = true;
		$filter_object = new stdClass();
		foreach ($filters as $filter)
		{
			if ($this->_ci->input->get($filter))
			{
				$filter_valid = true;
				$filter_object->$filter = $this->_ci->input->get($filter);
			}
		}

		if (!$filter_valid)
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		return $filter_object;
	}

	private function _checkUpdate($updateInfos)
	{
		$update_valid = false;
		$update_object = new stdClass();
		foreach (self::ALLOWED_TO_UPDATE as $filter)
		{
			if (isset($updateInfos[$filter]))
			{
				$update_valid = true;
				$update_object->$filter = $updateInfos[$filter];
			}
		}

		if (!$update_valid)
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);


		return $update_object;
	}
}
