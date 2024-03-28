<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \Studierendenantrag_model as Studierendenantrag_model;
use \DateTime as DateTime;

/**
 *
 */
class Unterbrechung extends FHC_Controller
{

	/**
	 * Calls the parent's constructor and loads the FilterCmptLib
	 */
	public function __construct()
	{
		parent::__construct();

		// Configs
		$this->load->config('studierendenantrag');

		// Libraries
		$this->load->library('AuthLib');
		$this->load->library('AntragLib');

		// Load language phrases
		$this->loadPhrases([
			'studierendenantrag',
			'ui'
		]);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getDetailsForNewAntrag($prestudent_id)
	{
		if (!$this->antraglib->isEntitledToCreateAntragFor($prestudent_id, false)) {
			$this->output->set_status_header(403);
			return $this->outputJsonError('Forbidden');
		}
		$result = $this->antraglib->getPrestudentUnterbrechungsBerechtigt($prestudent_id);
		if (isError($result)) {
			$this->output->set_status_header(500);
			return $this->outputJsonError(getError($result));
		}
		$result = $result->retval;
		if (!$result) {
			$this->output->set_status_header(403);
			return $this->outputJsonError($this->p->t('studierendenantrag', 'error_no_student'));
		}
		elseif ($result == -1)
		{
			$result = $this->antraglib->getDetailsForLastAntrag($prestudent_id, Studierendenantrag_model::TYP_UNTERBRECHUNG);
			if (isError($result)) {
				return $this->outputJsonError(getError($result));
			}

			return $this->outputJsonSuccess(getData($result));
		}
		elseif ($result == -2)
		{
			$result = $this->antraglib->getDetailsForLastAntrag($prestudent_id);
			if (isError($result)) {
				return $this->outputJsonError(getError($result));
			}

			$result = getData($result);
			$this->output->set_status_header(400);
			return $this->outputJsonError($this->p->t('studierendenantrag', 'error_antrag_pending', [
                'typ' => $this->p->t('studierendenantrag', 'antrag_typ_' . $result->typ)
            ]));
		}
		elseif ($result == -3)
		{
			$this->output->set_status_header(403);
			return $this->outputJsonError($this->p->t('studierendenantrag', 'error_stg_blacklist'));
		}
		$result = $this->antraglib->getDetailsForNewAntrag($prestudent_id);
		if (isError($result)) {
			return $this->outputJsonError(getError($result));
		}

		$data = getData($result);

		$data->studiensemester = $this->antraglib->getSemesterForUnterbrechung($prestudent_id, null);

		$this->outputJsonSuccess($data);
	}

	public function getDetailsForAntrag($studierendenantrag_id)
	{
		if (!$this->antraglib->isEntitledToShowAntrag($studierendenantrag_id))	return show_404();

		$result = $this->antraglib->getDetailsForAntrag($studierendenantrag_id);
		if (isError($result)) {
			return $this->outputJsonError(getError($result));
		}

		$data = getData($result);

		if ($data->typ !== Studierendenantrag_model::TYP_UNTERBRECHUNG)
			return show_404();

		$this->outputJsonSuccess($data);
	}

	public function createAntrag()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('studiensemester', 'Studiensemester', 'required');
		$this->form_validation->set_rules('prestudent_id', 'Prestudent ID', 'required');
		$this->form_validation->set_rules('grund', 'Grund', 'required');
		$this->form_validation->set_rules(
			'datum_wiedereinstieg',
			'Datum Wiedereinstieg',
			'required|callback_isValidDate|callback_isDateInFuture',
			[
				'isValidDate' => $this->p->t('ui', 'error_invalid_date'),
				'isDateInFuture' => $this->p->t('ui', 'error_invalid_date')
			]
		);

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$grund = $this->input->post('grund');
		$studiensemester = $this->input->post('studiensemester');
		$prestudent_id = $this->input->post('prestudent_id');
		$datum_wiedereinstieg = $this->input->post('datum_wiedereinstieg');
		$dms_id = null;

		$result = $this->antraglib->getPrestudentUnterbrechungsBerechtigt($prestudent_id, $studiensemester, $datum_wiedereinstieg);
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

		if(isset($_FILES['attachment']) && (!isset($_FILES['attachment']['error']) || $_FILES['attachment']['error'] != UPLOAD_ERR_NO_FILE))
		{
			$this->load->library('DmsLib');

			$dms = $this->config->item('unterbrechung_dms');
			if (!count(array_filter($dms, function ($v) {
				return $v !== null;
			})))
				$dms = ['kategorie_kurzbz' => 'Akte'];
			$dms['version'] = 0;

			$allowed_filetypes = $this->config->item('unterbrechung_dms_filetypes') ?: ['*'];
			$result = $this->dmslib->upload($dms, 'attachment', $allowed_filetypes);
			if(isError($result))
			{
				return $this->outputJsonError(['db' => getError($result)]);
			}
			$dms_id = getData($result)['dms_id'];
		}

		$result = $this->antraglib->createUnterbrechung($prestudent_id, $studiensemester, getAuthUID(), $grund, $datum_wiedereinstieg, $dms_id);
		if(isError($result))
		{
			return $this->outputJsonError(['db' => getError($result)]);
		}

		$antragId = getData($result);
		$result = $this->antraglib->getDetailsForAntrag($antragId);

		if(!hasData($result))
			return $this->outputJsonSuccess($antragId);
		$this->outputJsonSuccess(getData($result));
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

		$result = $this->antraglib->cancelAntrag($antrag_id, getAuthUID());
		if (isError($result))
		{
			return $this->outputJsonError(['db' => getError($result)]);
		}

		$result = $this->antraglib->getDetailsForAntrag($antrag_id);

		if (!hasData($result))
			return $this->outputJsonSuccess($antrag_id);
		$this->outputJsonSuccess(getData($result));
	}

	public function isValidDate($date)
	{
		try {
		    new DateTime($date);
		} catch (Exception $e) {
			return false;
		}
		return true;
	}

	public function isDateInFuture($date)
	{
		return new DateTime() < new DateTime($date);
	}
}
