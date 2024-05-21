<?php
/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \stdClass as stdClass;
use \Studierendenantrag_model as Studierendenantrag_model;

/**
 * This controller operates between (interface) the JS (GUI) and the AntragLib (back-end)
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Leitung extends FHCAPI_Controller
{

	/**
	 * Calls the parent's constructor and loads the AntragLib
	 */
	public function __construct()
	{
		parent::__construct([
			'getActiveStgs' => ['student/antragfreigabe:r', 'student/studierendenantrag:r'],
			'getAntraege' => ['student/antragfreigabe:r', 'student/studierendenantrag:r'],
			'getHistory' => ['student/antragfreigabe:r', 'student/studierendenantrag:r'],
			'getPrestudents' => 'student/studierendenantrag:w',
			'approveAntrag' => 'student/antragfreigabe:w',
			'rejectAntrag' => 'student/antragfreigabe:w',
			'reopenAntrag' => 'student/studierendenantrag:w',
			'pauseAntrag' => ['student/antragfreigabe:w', 'student/studierendenantrag:w'],
			'unpauseAntrag' => ['student/antragfreigabe:w', 'student/studierendenantrag:w'],
			'objectAntrag' => ['student/antragfreigabe:w', 'student/studierendenantrag:w'],
			'approveObjection' => ['student/antragfreigabe:w', 'student/studierendenantrag:w'],
			'denyObjection' => ['student/antragfreigabe:w', 'student/studierendenantrag:w']
		]);

		// Libraries
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
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getAntraege($studiengang = null, $extra = null)
	{
		if ($studiengang && $studiengang == 'todo') {
			$studiengang = $extra;
			$extra = true;
		} else {
			$extra = false;
		}

		$studiengaenge = $this->permissionlib->getSTG_isEntitledFor('student/antragfreigabe');
		if(!is_array($studiengaenge))
			$studiengaenge = [];


		$stgsNeuanlage = $this->permissionlib->getSTG_isEntitledFor('student/studierendenantrag');
		if(!is_array($stgsNeuanlage))
			$stgsNeuanlage = [];

		$studiengaenge = array_unique(array_merge($studiengaenge, $stgsNeuanlage));

		if ($studiengang) {
			if (!in_array($studiengang, $studiengaenge))
				$this->terminateWithError(
					'Forbidden',
					self::ERROR_TYPE_AUTH,
					REST_Controller::HTTP_FORBIDDEN
				);
			$studiengaenge = [$studiengang];
		}

		$antraege = [];
		if ($studiengaenge) {
			$result = $extra
				? $this->StudierendenantragModel->loadActiveForStudiengaenge($studiengaenge)
				: $this->StudierendenantragModel->loadForStudiengaenge($studiengaenge);

			$antraege = $this->getDataOrTerminateWithError($result);
		}

		$this->terminateWithSuccess($antraege ?: []);
	}

	public function getHistory($studierendenantrag_id)
	{
		if (!$this->antraglib->isEntitledToSeeHistoryForAntrag($studierendenantrag_id))
			$this->terminateWithError(
				'Forbidden',
				self::ERROR_TYPE_AUTH,
				REST_Controller::HTTP_FORBIDDEN
			);

		$result = $this->antraglib->getAntragHistory($studierendenantrag_id);
		$data = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($data ?: []);
	}

	public function getPrestudents()
	{
		$query = $this->input->post('query');

		$studiengaenge = $this->permissionlib->getSTG_isEntitledFor('student/studierendenantrag');

		$result = $this->antraglib->getAktivePrestudentenInStgs($studiengaenge, $query);
		$result = $this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess($result ?: []);
	}

	public function approveAntrag()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules(
			'studierendenantrag_id',
			'Studierenden Antrag',
			[
				'required',
				['isEntitledToApproveAntrag', [$this->antraglib, 'isEntitledToApproveAntrag']],
			],
			[
				'isEntitledToApproveAntrag' => $this->p->t('studierendenantrag', 'error_no_right')
			]
		);
		$this->form_validation->set_rules(
			'typ',
			'Typ',
			'required|in_list[' . implode(',', [
				Studierendenantrag_model::TYP_ABMELDUNG,
				Studierendenantrag_model::TYP_ABMELDUNG_STGL,
				Studierendenantrag_model::TYP_UNTERBRECHUNG,
				Studierendenantrag_model::TYP_WIEDERHOLUNG
			]) . ']'
		);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$studierendenantrag_id = $this->input->post('studierendenantrag_id');
		switch ($this->input->post('typ')) {
			case Studierendenantrag_model::TYP_ABMELDUNG:
			case Studierendenantrag_model::TYP_ABMELDUNG_STGL:
				$result = $this->antraglib->approveAbmeldung([$studierendenantrag_id], getAuthUID());
				break;
			case Studierendenantrag_model::TYP_UNTERBRECHUNG:
				$result = $this->antraglib->approveUnterbrechung([$studierendenantrag_id], getAuthUID());
				break;
			case Studierendenantrag_model::TYP_WIEDERHOLUNG:
				$result = $this->antraglib->approveWiederholung($studierendenantrag_id, getAuthUID());
				break;
		}
		$this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess($studierendenantrag_id);
	}

	public function rejectAntrag()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules(
			'studierendenantrag_id',
			'Studierenden Antrag',
			[
				'required',
				['isEntitledToRejectAntrag', [$this->antraglib, 'isEntitledToRejectAntrag']],
			],
			[
				'isEntitledToRejectAntrag' => $this->p->t('studierendenantrag', 'error_no_right')
			]
		);
		$this->form_validation->set_rules('grund', 'Grund', 'required');
		$this->form_validation->set_rules(
			'typ',
			'Typ',
			'required|in_list[' . implode(',', [
				Studierendenantrag_model::TYP_UNTERBRECHUNG
			]) . ']'
		);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$studierendenantrag_id = $this->input->post('studierendenantrag_id');
		$grund = $this->input->post('grund');

		$result = $this->antraglib->rejectUnterbrechung([$studierendenantrag_id], getAuthUID(), $grund);
		$this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess($studierendenantrag_id);
	}

	public function reopenAntrag()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules(
			'studierendenantrag_id',
			'Studierenden Antrag',
			[
				'required',
				['isEntitledToReopenAntrag', [$this->antraglib, 'isEntitledToReopenAntrag']],
			],
			[
				'isEntitledToReopenAntrag' => $this->p->t('studierendenantrag', 'error_no_right')
			]
		);
		$this->form_validation->set_rules(
			'typ',
			'Typ',
			'required|in_list[' . implode(',', [
				Studierendenantrag_model::TYP_WIEDERHOLUNG
			]) . ']'
		);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$studierendenantrag_id = $this->input->post('studierendenantrag_id');

		$result = $this->antraglib->reopenWiederholung($studierendenantrag_id, getAuthUID());
		$this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess($studierendenantrag_id);
	}

	public function pauseAntrag()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules(
			'studierendenantrag_id',
			'Studierenden Antrag',
			[
				'required',
				['isEntitledToPauseAntrag', [$this->antraglib, 'isEntitledToPauseAntrag']],
				['antragCanBeManualPaused', [$this->antraglib, 'antragCanBeManualPaused']]
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

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$studierendenantrag_id = $this->input->post('studierendenantrag_id');

		$result = $this->antraglib->pauseAntrag($studierendenantrag_id, getAuthUID());
		$this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess($studierendenantrag_id);
	}

	public function unpauseAntrag()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules(
			'studierendenantrag_id',
			'Studierenden Antrag',
			[
				'required',
				['isEntitledToUnpauseAntrag', [$this->antraglib, 'isEntitledToUnpauseAntrag']],
				['antragCanBeManualUnpaused', [$this->antraglib, 'antragCanBeManualUnpaused']]
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

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$studierendenantrag_id = $this->input->post('studierendenantrag_id');

		$result = $this->antraglib->unpauseAntrag($studierendenantrag_id, getAuthUID());
		$this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess($studierendenantrag_id);
	}

	public function objectAntrag()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules(
			'studierendenantrag_id',
			'Studierenden Antrag',
			[
				'required',
				['isEntitledToObjectAntrag', [$this->antraglib, 'isEntitledToObjectAntrag']],
				['canBeObjected', function ($a) {
					return $this->antraglib->hasType($a, Studierendenantrag_model::TYP_ABMELDUNG_STGL);
				}]
			],
			[
				'isEntitledToObjectAntrag' => $this->p->t('studierendenantrag', 'error_no_right'),
				'canBeObjected' => $this->p->t(
					'studierendenantrag',
					'error_no_objection'
				)
			]
		);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$studierendenantrag_id = $this->input->post('studierendenantrag_id');

		$result = $this->antraglib->objectAbmeldung($studierendenantrag_id, getAuthUID());
		$this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess($studierendenantrag_id);
	}

	public function approveObjection()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules(
			'studierendenantrag_id',
			'Studierenden Antrag',
			[
				'required',
				['isEntitledToObjectAntrag', [$this->antraglib, 'isEntitledToObjectAntrag']],
				['isObjected', function ($a) {
					return $this->antraglib->hasStatus($a, Studierendenantragstatus_model::STATUS_OBJECTED);
				}]
			],
			[
				'isEntitledToObjectAntrag' => $this->p->t('studierendenantrag', 'error_no_right'),
				'isObjected' => $this->p->t(
					'studierendenantrag',
					'error_not_objected'
				)
			]
		);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$studierendenantrag_id = $this->input->post('studierendenantrag_id');
		
		$result = $this->antraglib->cancelAntrag($studierendenantrag_id, getAuthUID());
		$this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess($studierendenantrag_id);
	}

	public function denyObjection()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules(
			'studierendenantrag_id',
			'Studierenden Antrag',
			[
				'required',
				['isEntitledToObjectAntrag', [$this->antraglib, 'isEntitledToObjectAntrag']],
				['isObjected', function ($a) {
					return $this->antraglib->hasStatus($a, Studierendenantragstatus_model::STATUS_OBJECTED);
				}]
			],
			[
				'isEntitledToObjectAntrag' => $this->p->t('studierendenantrag', 'error_no_right'),
				'isObjected' => $this->p->t(
					'studierendenantrag',
					'error_not_objected'
				)
			]
		);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$studierendenantrag_id = $this->input->post('studierendenantrag_id');
		$grund = $this->input->post('grund');

		$result = $this->antraglib->denyObjectionAbmeldung($studierendenantrag_id, getAuthUID(), $grund);
		$this->getDataOrTerminateWithError($result);

		return $this->terminateWithSuccess($studierendenantrag_id);
	}
}
