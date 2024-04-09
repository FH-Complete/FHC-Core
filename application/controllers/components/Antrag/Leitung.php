<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \stdClass as stdClass;

/**
 *
 */
class Leitung extends FHC_Controller
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

	public function getActiveStgs()
	{
		$studiengaenge = $this->permissionlib->getSTG_isEntitledFor('student/antragfreigabe') ?: [];
		$studiengaenge = array_merge($studiengaenge, $this->permissionlib->getSTG_isEntitledFor('student/studierendenantrag') ?: []);
		
		$result = $this->StudierendenantragModel->loadStgsWithAntraege($studiengaenge);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getAntraege($studiengang = null, $extra = null)
	{
		if ($studiengang && $studiengang == 'todo') {
			$studiengang = $extra;
			$extra = true;
		} else {
			$extra = false;
		}

		if ($studiengang) {
			$studiengaenge = [$studiengang];
		} else {
			$studiengaenge =$this->permissionlib->getSTG_isEntitledFor('student/antragfreigabe');
			if(!is_array($studiengaenge))
				$studiengaenge = [];


			$stgsNeuanlage = $this->permissionlib->getSTG_isEntitledFor('student/studierendenantrag');
			if(!is_array($stgsNeuanlage))
				$stgsNeuanlage = [];

			$studiengaenge = array_unique(array_merge($studiengaenge, $stgsNeuanlage));
		}


		$antraege = [];
		if ($studiengaenge) {
			$result = $extra
				? $this->StudierendenantragModel->loadActiveForStudiengaenge($studiengaenge)
				: $this->StudierendenantragModel->loadForStudiengaenge($studiengaenge);
			if (isError($result)) {
				$this->output->set_status_header(500);
				return $this->outputJson('Internal Server Error');
			}
			if(hasData($result))
			{
			    $antraege = getData($result);
			}
		}

		$this->outputJson($antraege);
	}

	public function reopenAntrag()
	{
		$this->load->library('form_validation');

		$_POST = json_decode($this->input->raw_input_stream, true);

		$this->form_validation->set_rules(
			'studierendenantrag_id',
			'Studierenden Antrag',
			'required|callback_isEntitledToReopenAntrag',
			[
				'isEntitledToReopenAntrag' => $this->p->t('studierendenantrag', 'error_no_right')
			]
		);

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$studierendenantrag_id = $this->input->post('studierendenantrag_id');

		$result = $this->antraglib->reopenWiederholung($studierendenantrag_id, getAuthUID());

		if (isError($result))
			return $this->outputJsonError(['studierendenantrag_id' => getError($result)]);

		$this->outputJsonSuccess($studierendenantrag_id);
	}

	public function pauseAntrag()
	{
		$this->load->library('form_validation');

		$_POST = json_decode($this->input->raw_input_stream, true);

		$this->form_validation->set_rules(
			'studierendenantrag_id',
			'Studierenden Antrag',
			[
				'required',
				[
					'isEntitledToPauseAntrag',
					[$this->antraglib, 'isEntitledToPauseAntrag']
				],
				[
					'antragCanBeManualPaused',
					[$this->antraglib, 'antragCanBeManualPaused']
				]
			],
			[
				'isEntitledToPauseAntrag' => $this->p->t('studierendenantrag', 'error_no_right'),
				'antragCanBeManualPaused' => $this->p->t(
					'studierendenantrag',
					'error_not_pauseable',
					['id' => $this->input->post('studierendenantrag_id')]
				)
			]
		);

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$studierendenantrag_id = $this->input->post('studierendenantrag_id');

		$result = $this->antraglib->pauseAntrag($studierendenantrag_id, getAuthUID());

		if (isError($result))
			return $this->outputJsonError(['studierendenantrag_id' => getError($result)]);

		$this->outputJsonSuccess($studierendenantrag_id);
	}

	public function unpauseAntrag()
	{
		$this->load->library('form_validation');

		$_POST = json_decode($this->input->raw_input_stream, true);

		$this->form_validation->set_rules(
			'studierendenantrag_id',
			'Studierenden Antrag',
			[
				'required',
				[
					'isEntitledToUnpauseAntrag',
					[$this->antraglib, 'isEntitledToUnpauseAntrag']
				],
				[
					'antragCanBeManualUnpaused',
					[$this->antraglib, 'antragCanBeManualUnpaused']
				]
			],
			[
				'isEntitledToUnpauseAntrag' => $this->p->t('studierendenantrag', 'error_no_right'),
				'antragCanBeManualUnpaused' => $this->p->t(
					'studierendenantrag',
					'error_not_paused',
					['id' => $this->input->post('studierendenantrag_id')]
				)
			]
		);

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$studierendenantrag_id = $this->input->post('studierendenantrag_id');

		$result = $this->antraglib->unpauseAntrag($studierendenantrag_id, getAuthUID());

		if (isError($result))
			return $this->outputJsonError(['studierendenantrag_id' => getError($result)]);

		$this->outputJsonSuccess($studierendenantrag_id);
	}

	public function objectAntrag()
	{
		$this->load->library('form_validation');

		$_POST = json_decode($this->input->raw_input_stream, true);

		$this->form_validation->set_rules(
			'studierendenantrag_id',
			'Studierenden Antrag',
			'required|callback_isEntitledToObjectAntrag|callback_canBeObjected',
			[
				'isEntitledToObjectAntrag' => $this->p->t('studierendenantrag', 'error_no_right'),
				'canBeObjected' => $this->p->t('studierendenantrag', 'error_no_objection')
			]
		);

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$studierendenantrag_id = $this->input->post('studierendenantrag_id');

		$result = $this->antraglib->objectAbmeldung($studierendenantrag_id, getAuthUID());

		if (isError($result))
			return $this->outputJsonError(['studierendenantrag_id' => getError($result)]);

		$this->outputJsonSuccess($studierendenantrag_id);
	}

	public function objectionDeny()
	{
		$this->load->library('form_validation');

		$_POST = json_decode($this->input->raw_input_stream, true);

		$this->form_validation->set_rules(
			'studierendenantrag_id',
			'Studierenden Antrag',
			'required|callback_isEntitledToObjectAntrag|callback_isObjected',
			[
				'isEntitledToObjectAntrag' => $this->p->t('studierendenantrag', 'error_no_right'),
				'isObjected' => $this->p->t('studierendenantrag', 'error_not_objected')
			]
		);

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$studierendenantrag_id = $this->input->post('studierendenantrag_id');
		$grund = $this->input->post('grund');

		$result = $this->antraglib->denyObjectionAbmeldung($studierendenantrag_id, getAuthUID(), $grund);

		if (isError($result))
			return $this->outputJsonError(['studierendenantrag_id' => getError($result)]);

		$this->outputJsonSuccess($studierendenantrag_id);
	}

	public function objectionApprove()
	{
		$this->load->library('form_validation');

		$_POST = json_decode($this->input->raw_input_stream, true);

		$this->form_validation->set_rules(
			'studierendenantrag_id',
			'Studierenden Antrag',
			'required|callback_isEntitledToObjectAntrag|callback_isObjected',
			[
				'isEntitledToObjectAntrag' => $this->p->t('studierendenantrag', 'error_no_right'),
				'isObjected' => $this->p->t('studierendenantrag', 'error_not_objected')
			]
		);

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$studierendenantrag_id = $this->input->post('studierendenantrag_id');

		$result = $this->antraglib->cancelAntrag($studierendenantrag_id, getAuthUID());

		if (isError($result))
			return $this->outputJsonError(['studierendenantrag_id' => getError($result)]);

		$this->outputJsonSuccess($studierendenantrag_id);
	}

	public function isEntitledToReopenAntrag($studierendenantrag_id)
	{
		return $this->antraglib->isEntitledToReopenAntrag($studierendenantrag_id);
	}

	public function isEntitledToObjectAntrag($studierendenantrag_id)
	{
		return $this->antraglib->isEntitledToObjectAntrag($studierendenantrag_id);
	}

	public function isEntitledToRejectAntrag($studierendenantrag_id)
	{
		return $this->antraglib->isEntitledToRejectAntrag($studierendenantrag_id);
	}

	public function canBeObjected($studierendenantrag_id)
	{
		return $this->antraglib->hasType($studierendenantrag_id, Studierendenantrag_model::TYP_ABMELDUNG_STGL);
	}

	public function isObjected($studierendenantrag_id)
	{
		return $this->antraglib->hasStatus($studierendenantrag_id, Studierendenantragstatus_model::STATUS_OBJECTED);
	}


	public function approveAbmeldung()
	{
		$this->load->library('form_validation');

		$_POST = json_decode($this->input->raw_input_stream, true);

		$this->form_validation->set_rules(
			'studierendenantrag_id',
			'Studierenden Antrag',
			'required|callback_isEntitledToApproveAntrag',
			[
				'isEntitledToApproveAntrag' => $this->p->t('studierendenantrag', 'error_no_right')
			]
		);

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$studierendenantrag_id = $this->input->post('studierendenantrag_id');

		$result = $this->antraglib->approveAbmeldung([$studierendenantrag_id], getAuthUID());
		if (isError($result))
		{
			return $this->outputJsonError(['db' => getError($result)]);
		}

		return $this->outputJsonSuccess($studierendenantrag_id);
	}

	public function approveAbmeldungStgl()
	{
		return $this->approveAbmeldung();
	}

	public function approveUnterbrechung()
	{
		$this->load->library('form_validation');

		$_POST = json_decode($this->input->raw_input_stream, true);

		$this->form_validation->set_rules(
			'studierendenantrag_id',
			'Studierenden Antrag',
			'required|callback_isEntitledToApproveAntrag',
			[
				'isEntitledToApproveAntrag' => $this->p->t('studierendenantrag', 'error_no_right')
			]
		);

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$studierendenantrag_id = $this->input->post('studierendenantrag_id');

		$result = $this->antraglib->approveUnterbrechung([$studierendenantrag_id], getAuthUID());
		if (isError($result))
		{
			return $this->outputJsonError(['db' => getError($result)]);
		}

		return $this->outputJsonSuccess($studierendenantrag_id);
	}

	public function rejectUnterbrechung()
	{
		$this->load->library('form_validation');

		$_POST = json_decode($this->input->raw_input_stream, true);

		$this->form_validation->set_rules(
			'studierendenantrag_id',
			'Studierenden Antrag',
			'required|callback_isEntitledToRejectAntrag',
			[
				'isEntitledToRejectAntrag' => $this->p->t('studierendenantrag', 'error_no_right')
			]
		);
		$this->form_validation->set_rules('grund', 'Grund', 'required');

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$studierendenantrag_id = $this->input->post('studierendenantrag_id');
		$grund = $this->input->post('grund');

		$result = $this->antraglib->rejectUnterbrechung([$studierendenantrag_id], getAuthUID(), $grund);
		if (isError($result))
		{
			return $this->outputJsonError(['db' => getError($result)]);
		}

		return $this->outputJsonSuccess($studierendenantrag_id);
	}

	public function approveWiederholung()
	{
		$this->load->library('form_validation');

		$_POST = json_decode($this->input->raw_input_stream, true);

		$this->form_validation->set_rules(
			'studierendenantrag_id',
			'Studierenden Antrag',
			'required|callback_isEntitledToApproveAntrag',
			[
				'isEntitledToApproveAntrag' => $this->p->t('studierendenantrag', 'error_no_right')
			]
		);

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$studierendenantrag_id = $this->input->post('studierendenantrag_id');

		$result = $this->antraglib->approveWiederholung($studierendenantrag_id, getAuthUID());
		if (isError($result))
		{
			return $this->outputJsonError(['db' => getError($result)]);
		}

		return $this->outputJsonSuccess($studierendenantrag_id);
	}

	public function isEntitledToApproveAntrag($studierendenantrag_id)
	{
		return $this->antraglib->isEntitledToApproveAntrag($studierendenantrag_id);
	}

	public function getHistory($studierendenantrag_id)
	{
		if (!$this->antraglib->isEntitledToSeeHistoryForAntrag($studierendenantrag_id)) {
			$this->output->set_status_header(403);
			return $this->outputJson('Forbidden');
		}

		$result = $this->antraglib->getAntragHistory($studierendenantrag_id);
		if (isError($result)) {
			return $this->outputJsonError(getError($result));
		}

		$this->outputJsonSuccess(getData($result) ?: []);
	}
}
