<?php
/**
 * FH-Complete
 *
 * @package        FHC-API
 * @author        FHC-Team
 * @copyright    Copyright (c) 2016, fhcomplete.org
 * @license        GPLv3
 * @link        http://fhcomplete.org
 * @since        Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Studienplan extends FHCAPI_Controller
{
	public function __construct()
	{
		// TODO(chris): access!
		parent::__construct([
			'getAllStudyPlans' => self::PERM_LOGGED,
			'getStudyPlansByOrganizationalUnitAndSemesterDates' => self::PERM_LOGGED,
			'getBySemester' => self::PERM_LOGGED,
			'getStudyPlan' => self::PERM_LOGGED,
		]);
	}

	public function getAllStudyPlans()
	{
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');
		$result = $this->StudienplanModel->load();
		$studien_plan_result = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($studien_plan_result);
	}

	public function getStudyPlansByOrganizationalUnitAndSemesterDates($organizationalUnitShortCode)
	{
		$this->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->load->model('organisation/Studienordnung_model', 'StudienordnungModel');
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');

		$startDate = date('Y-m-d', strtotime($this->input->get('filter[startDate]')));
		$endDate = date('Y-m-d', strtotime($this->input->get('filter[endDate]')));
		if (!$startDate || !$endDate) {
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Start- oder Enddatum']), self::ERROR_TYPE_GENERAL);
		}

		$studyPlansResponse = $this->StudienplanModel->getStudyPlansForOrganizationalUnitAndDatesQueryResponse($organizationalUnitShortCode, $startDate, $endDate);
		if (isError($studyPlansResponse)) $this->terminateWithError(getError($studyPlansResponse), self::ERROR_TYPE_DB);
		if (!hasData($studyPlansResponse)) return $this->terminateWithSuccess(null);

		return $this->terminateWithSuccess($this->getDataOrTerminateWithError($studyPlansResponse));
	}

	public function getBySemester()
	{
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');

		$studiengang_kz = $this->input->get('studiengang_kz');
		$studiensemester_kurzbz = $this->input->get('studiensemester_kurzbz');
		$ausbildungssemester = $this->input->get('ausbildungssemester') ?: null;
		$orgform_kurzbz = $this->input->get('orgform_kurzbz') ?: null;

		if (!$studiengang_kz || !is_numeric($studiengang_kz))
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Studiengangskennzahl']), self::ERROR_TYPE_GENERAL);

		if (!$studiensemester_kurzbz)
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Studiensemester']), self::ERROR_TYPE_GENERAL);

		if (isset($ausbildungssemester) && !is_numeric($ausbildungssemester))
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Ausbildungssemester']), self::ERROR_TYPE_GENERAL);


		//~ $this->load->library('form_validation');
		
		//~ $this->form_validation->set_rules('studiengang_kz', 'StudiengangKz', 'required|numeric');
		//~ $this->form_validation->set_rules('studiensemester_kurzbz', 'StudiensemesterKurbz', 'required');
		//~ $this->form_validation->set_rules('ausbildungssemester', 'Ausbildungssemester', 'numeric');

		//~ if (!$this->form_validation->run())
		//~ {
			//~ $this->addMeta('fail2', 'fail2');
			//~ return $this->terminateWithValidationErrors($this->form_validation->error_array());
		//~ }


		$this->addMeta('stg_kz', $studiengang_kz);
		$this->addMeta('sem', $studiensemester_kurzbz);
		$this->addMeta('sem2', $ausbildungssemester);
		$this->addMeta('org', $orgform_kurzbz);

		$result = $this->StudienplanModel->getStudienplaeneBySemester($studiengang_kz, $studiensemester_kurzbz, $ausbildungssemester, $orgform_kurzbz);
		if (isError($result)) $this->terminateWithError(getError($result), self::ERROR_TYPE_DB);

		$this->terminateWithSuccess(hasData($result) ? getData($result) : []);
	}

	public function getStudyPlan($id)
	{
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');

		$result = $this->StudienplanModel->loadWhere(['studienplan_id' => $id]);
		if (isError($result)) $this->terminateWithError(getError($result), self::ERROR_TYPE_DB);
		if (!hasData($result)) return $this->terminateWithSuccess(null);

		$this->terminateWithSuccess(getData($result)[0]);
	}
}
