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

use \REST_Controller as REST_Controller;
use \Studierendenantragstatus_model as Studierendenantragstatus_model;

/**
 * This controller operates between (interface) the JS (GUI) and the AntragLib (back-end)
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Wiederholung extends FHCAPI_Controller
{

	/**
	 * Calls the parent's constructor and loads the FilterCmptLib
	 */
	public function __construct()
	{
		parent::__construct([
			'getDetailsForNewAntrag' => self::PERM_LOGGED,
			'createAntrag' => self::PERM_LOGGED,
			'cancelAntrag' => self::PERM_LOGGED,
			'getLvs' => self::PERM_LOGGED,
			'saveLvs' => ['student/studierendenantrag:w']
		]);

		// Libraries
		$this->load->library('AntragLib');

		// Load language phrases
		$this->loadPhrases([
			'global',
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
		if (!$this->antraglib->isEntitledToCreateAntragFor($prestudent_id, false))
			$this->terminateWithError('Forbidden', self::ERROR_TYPE_AUTH, REST_Controller::HTTP_FORBIDDEN);

		$result = $this->antraglib->getPrestudentWiederholungsBerechtigt($prestudent_id);
		$result = $this->getDataOrTerminateWithError($result);

		if (!$result) {
			$this->terminateWithError(
				$this->p->t('studierendenantrag', 'error_no_student_no_failed_exam'),
				self::ERROR_TYPE_AUTH,
				REST_Controller::HTTP_FORBIDDEN
			);
		} elseif ($result == -1) {
			$result = $this->antraglib->getDetailsForLastAntrag($prestudent_id, Studierendenantrag_model::TYP_WIEDERHOLUNG);
			$data = $this->getDataOrTerminateWithError($result);

			$result = $this->antraglib->getFailedExamForPrestudent($prestudent_id, $data->datum, $data->studiensemester_kurzbz);
			// NOTE(chris): error handling for this function should already happenden in antraglib->getPrestudentWiederholungsBerechtigt()
			$pruefungsdata = current(getData($result));

			$data->studiensemester_kurzbz = $pruefungsdata->studiensemester_kurzbz;
			$data->lvbezeichnung = $pruefungsdata->lvbezeichnung;
			$data->pruefungsdatum = $pruefungsdata->datum;

			$this->terminateWithSuccess($data);
		} elseif ($result == -2) {
			$result = $this->antraglib->getDetailsForLastAntrag($prestudent_id);
			$result = $this->getDataOrTerminateWithError($result);

			$this->terminateWithError(
				$this->p->t('studierendenantrag', 'error_antrag_pending', [
					'typ' => $this->p->t('studierendenantrag', 'antrag_typ_' . $result->typ)
				]),
				self::ERROR_TYPE_GENERAL,
				REST_Controller::HTTP_BAD_REQUEST
			);
		} elseif ($result == -3) {
			$this->terminateWithError(
				$this->p->t('studierendenantrag', 'error_stg_blacklist'),
				self::ERROR_TYPE_GENERAL,
				REST_Controller::HTTP_BAD_REQUEST
			);
		}

		$result = $this->antraglib->getDetailsForNewAntrag($prestudent_id);
		$data = $this->getDataOrTerminateWithError($result);

		$result = $this->antraglib->getFailedExamForPrestudent($prestudent_id);
		// NOTE(chris): error handling for this function should already happenden in antraglib->getPrestudentWiederholungsBerechtigt()
		$pruefungsdata = current(getData($result));

		$data->studiensemester_kurzbz = $pruefungsdata->studiensemester_kurzbz;
		$data->lvbezeichnung = $pruefungsdata->lvbezeichnung;
		$data->pruefungsdatum = $pruefungsdata->datum;

		$this->terminateWithSuccess($data);
	}

	public function createAntrag()
	{
		$this->createAntragWithStatus(true);
	}

	public function cancelAntrag()
	{
		$this->createAntragWithStatus(false);
	}

	protected function createAntragWithStatus($repeat)
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('prestudent_id', 'Prestudent ID', 'required');
		$this->form_validation->set_rules('studiensemester', 'Studiensemester', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$prestudent_id = $this->input->post('prestudent_id');
		$studiensemester = $this->input->post('studiensemester');

		$result = $this->antraglib->getPrestudentWiederholungsBerechtigt($prestudent_id);
		$result = $this->getDataOrTerminateWithError($result);

		if (!$result) {
			$this->terminateWithError($this->p->t('studierendenantrag', 'error_no_student'), self::ERROR_TYPE_GENERAL);
		} elseif ($result == -1) {
			$result = $this->PrestudentstatusModel->getLastStatus($prestudent_id);
			$result = $this->getDataOrTerminateWithError($result);
			if (!$result)
				$this->terminateWithError($this->p->t('studierendenantrag', 'error_no_prestudentstatus', [
					'prestudent_id' => $prestudent_id
				]), self::ERROR_TYPE_GENERAL);
			if (!in_array(current($result)->status_kurzbz, $this->config->item('antrag_prestudentstatus_whitelist')))
				$this->terminateWithError($this->p->t('studierendenantrag', 'error_no_student'), self::ERROR_TYPE_GENERAL);
		} elseif ($result == -2) {
			$this->terminateWithError($this->p->t('studierendenantrag', 'error_antrag_exists'), self::ERROR_TYPE_GENERAL);
		} elseif ($result == -3) {
			$this->terminateWithError($this->p->t('studierendenantrag', 'error_stg_blacklist'), self::ERROR_TYPE_GENERAL);
		}

		$result = $this->antraglib->createWiederholung($prestudent_id, $studiensemester, getAuthUID(), $repeat);
		$antragId = $this->getDataOrTerminateWithError($result);

		$result = $this->antraglib->getDetailsForAntrag($antragId);

		if (!hasData($result))
			$this->terminateWithSuccess(true);

		$data = getData($result);

		$result = $this->antraglib->getFailedExamForPrestudent($prestudent_id);
		// NOTE(chris): error handling for this function should already happenden in antraglib->getPrestudentWiederholungsBerechtigt()
		$pruefungsdata = current(getData($result));

		$data->studiensemester_kurzbz = $pruefungsdata->studiensemester_kurzbz;
		$data->lvbezeichnung = $pruefungsdata->lvbezeichnung;
		$data->pruefungsdatum = $pruefungsdata->datum;

		$this->terminateWithSuccess($data);
	}


	public function getLvs($antrag_id)
	{
		$result = $this->antraglib->getLvsForAntrag($antrag_id);
		if (isError($result)) {
			$error = getError($result);
			if ($error == 'Forbidden')
				$this->terminateWithError(
					$error,
					self::ERROR_TYPE_AUTH,
					REST_Controller::HTTP_FORBIDDEN
				);
			$this->terminateWithError(
				$error,
				self::ERROR_TYPE_GENERAL
			);
		}
		$lvs = getData($result);

		$this->terminateWithSuccess($lvs);
	}

	public function saveLvs()
	{
		$forbiddenLvs = $this->input->post('forbiddenLvs');
		$mandatoryLvs = $this->input->post('mandatoryLvs');
		$antragsLvs = array_merge($forbiddenLvs, $mandatoryLvs);

		if (!$antragsLvs)
			$this->terminateWithError($this->p->t('studierendenantrag', 'error_no_lv'), self::ERROR_TYPE_GENERAL);

		$insert = array_map(function ($lv) {
			return [
				'studierendenantrag_id' => $lv['studierendenantrag_id'],
				'lehrveranstaltung_id' => $lv['lehrveranstaltung_id'],
				'note' => $lv['zugelassen']
                    ? ($lv['zugelassen'] == 1 ? 0 : $this->config->item('wiederholung_note_angerechnet'))
                    : $this->config->item('wiederholung_note_nicht_zugelassen'),
				'anmerkung' => $lv['anmerkung'],
				'insertvon' => getAuthUID(),
				'studiensemester_kurzbz' => $lv['studiensemester_kurzbz']
			];
		}, $antragsLvs);

		$antrag_ids = array_unique(array_map(function ($lv) {
			return $lv['studierendenantrag_id'];
		}, $insert));

		foreach ($antrag_ids as $antrag_id) {
			$result = $this->StudierendenantragModel->loadIdAndStatusWhere([
				'studierendenantrag_id' => $antrag_id
			]);
			$antrag = $this->getDataOrTerminateWithError($result);
			if (!$antrag)
				$this->terminateWithError(
					$this->p->t('studierendenantrag', 'error_no_antrag_found', ['id' => $antrag_id]),
					self::ERROR_TYPE_GENERAL
				);
			$antrag = current($antrag);

			if ($antrag->status != Studierendenantragstatus_model::STATUS_CREATED
				&& $antrag->status != Studierendenantragstatus_model::STATUS_LVSASSIGNED)
				$this->terminateWithError(
					$this->p->t('studierendenantrag', 'error_antrag_locked'),
					self::ERROR_TYPE_GENERAL
				);
		}

		$result = $this->antraglib->saveLvs($insert);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}
}
