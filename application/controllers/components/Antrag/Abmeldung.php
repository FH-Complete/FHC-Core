<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \Studierendenantrag_model as Studierendenantrag_model;

/**
 *
 */
class Abmeldung extends FHC_Controller
{

	/**
	 * Calls the parent's constructor and loads the FilterCmptLib
	 */
	public function __construct()
	{
		parent::__construct();

		// Libraries
		$this->load->library('AuthLib');
		$this->load->library('AntragLib');

		// Load language phrases
		$this->loadPhrases([
			'studierendenantrag'
		]);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Retrieves data of the current studiengang for the current user
	 */

	public function getDetailsForNewAntrag($prestudent_id)
	{
		if (!$this->antraglib->isEntitledToCreateAntragFor($prestudent_id, true)) {
			$this->output->set_status_header(403);
			return $this->outputJsonError('Forbidden');
		}
		$result = $this->antraglib->getPrestudentAbmeldeBerechtigt($prestudent_id);
		if (isError($result)) {
			$this->output->set_status_header(500);
			return $this->outputJsonError(getError($result));
		}
		$result = $result->retval;
		if (!$result) {
			$this->output->set_status_header(403);
			return $this->outputJsonError($this->p->t('studierendenantrag', 'error_no_student'));
		}
		elseif ($result == -3)
		{
			$this->output->set_status_header(403);
			return $this->outputJsonError($this->p->t('studierendenantrag', 'error_stg_blacklist'));
		}
		elseif ($result == -1)
		{
			$result = $this->antraglib->getDetailsForLastAntrag($prestudent_id, Studierendenantrag_model::TYP_ABMELDUNG);
			if (isError($result)) {
				return $this->outputJsonError(getError($result));
			}

			$data = getData($result);
			$data->canCancel = (boolean)$this->antraglib->isEntitledToCancelAntrag($data->studierendenantrag_id);

			return $this->outputJsonSuccess($data);
		}

		$result = $this->antraglib->getDetailsForNewAntrag($prestudent_id);
		if (isError($result)) {
			return $this->outputJsonError(getError($result));
		}

		$this->outputJsonSuccess(getData($result));
	}

	public function createAntrag()
	{
		$this->load->library('form_validation');

		$_POST = json_decode($this->input->raw_input_stream, true);

		$this->form_validation->set_rules('studiensemester', 'Studiensemester', 'required');
		$this->form_validation->set_rules('prestudent_id', 'Prestudent ID', 'required');
		$this->form_validation->set_rules('grund', 'Grund', 'required');

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$grund = $this->input->post('grund');
		$studiensemester = $this->input->post('studiensemester');
		$prestudent_id = $this->input->post('prestudent_id');

		$result = $this->antraglib->getPrestudentAbmeldeBerechtigt($prestudent_id);
		if (isError($result)) {
			return $this->outputJsonError(['db' => getError($result)]);
		}
		$result = $result->retval;
		if (!$result)
		{
			return $this->outputJsonError(['db' => $this->p->t('studierendenantrag', 'error_no_student')]);
		}
		elseif ($result == -3)
		{
			return $this->outputJsonError(['db' => $this->p->t('studierendenantrag', 'error_stg_blacklist')]);
		}
		elseif ($result < 0)
		{
			return $this->outputJsonError(['db' => $this->p->t('studierendenantrag', 'error_antrag_exists')]);
		}

		$result = $this->antraglib->createAbmeldung($prestudent_id, $studiensemester, getAuthUID(), $grund);
		if (isError($result))
		{
			return $this->outputJsonError(['db' => getError($result)]);
		}

		$result = $this->antraglib->getDetailsForAntrag(getData($result));
		if (!hasData($result))
		return $this->outputJsonSuccess(true);

		$data = getData($result);
		$data->canCancel = (boolean)$this->antraglib->isEntitledToCancelAntrag($data->studierendenantrag_id);

		$this->outputJsonSuccess($data);
	}

	public function cancelAntrag()
	{
		$this->load->library('form_validation');

		$_POST = json_decode($this->input->raw_input_stream, true);

		$this->form_validation->set_rules('antrag_id', 'Antrag ID', 'required');

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$antrag_id = $this->input->post('antrag_id');
		if(!$this->antraglib->isEntitledToCancelAntrag($antrag_id))
		{
			$this->output->set_status_header(403);

			return $this->outputJsonError('Forbidden');
		}

		$result = $this->antraglib->cancelAntrag($antrag_id, getAuthUID());
		if(isError($result))
		{
			return $this->outputJsonError(['db' => getError($result)]);
		}

		$result = $this->antraglib->getDetailsForAntrag($antrag_id);

		if (!hasData($result))
			return $this->outputJsonSuccess($antrag_id);
		$this->outputJsonSuccess(getData($result));
	}

	public function getStudiengaengeAssistenz()
	{
		$this->load->library('PermissionLib');

		$studiengaenge = $this->permissionlib->getSTG_isEntitledFor('student/studierendenantrag');

		$result = $this->antraglib->getAbmeldeBerechtigtForStg($studiengaenge);
		if (isError($result)) {
			return $this->outputJsonError(getError($result));
		}
		$result = getData($result);
		if (!$result) {
			return $this->outputJsonSuccess([]);
		}

		$sortedStudents = [];
		foreach ($result as $item) {
			if (!isset($sortedStudents[$item->studiengang_kz]))
				$sortedStudents[$item->studiengang_kz] = [
					'bezeichnung' => $item->bezeichnung,
					'orgform' => $item->orgform,
					'studenten' => []
				];
			$sortedStudents[$item->studiengang_kz]['studenten'][] = [
				'semester' => $item->semester,
				'prestudent_id' => $item->prestudent_id,
				'name' => trim($item->vorname . ' ' . $item->nachname),
				'vorname' => $item->vorname,
				'nachname' => $item->nachname,
				'studiensemester_kurzbz' => $item->studiensemester_kurzbz
			];
		}

		$this->outputJsonSuccess($sortedStudents);
	}
}
