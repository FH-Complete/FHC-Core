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
use \Studierendenantrag_model as Studierendenantrag_model;

/**
 * This controller operates between (interface) the JS (GUI) and the AntragLib (back-end)
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Abmeldung extends FHCAPI_Controller
{

	/**
	 * Calls the parent's constructor and loads the AntragLib
	 */
	public function __construct()
	{
		parent::__construct([
			'getDetailsForNewAntrag' => self::PERM_LOGGED,
			'getDetailsForAntrag' => self::PERM_LOGGED,
			'createAntrag' => self::PERM_LOGGED,
			'cancelAntrag' => self::PERM_LOGGED
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

	/**
	 * Retrieves data of the current studiengang for the current user
	 */

	public function getDetailsForNewAntrag($prestudent_id)
	{
		if (!$this->antraglib->isEntitledToCreateAntragFor($prestudent_id, true))
			$this->terminateWithError('Forbidden', self::ERROR_TYPE_AUTH, REST_Controller::HTTP_FORBIDDEN);

		$result = $this->antraglib->getPrestudentAbmeldeBerechtigt($prestudent_id);
		$result = $this->getDataOrTerminateWithError($result);
		
		if (!$result) {
			$this->terminateWithError(
				$this->p->t('studierendenantrag', 'error_no_student'),
				self::ERROR_TYPE_AUTH,
				REST_Controller::HTTP_FORBIDDEN
			);
		} elseif ($result == -3) {
			$this->terminateWithError(
				$this->p->t('studierendenantrag', 'error_stg_blacklist'),
				self::ERROR_TYPE_AUTH,
				REST_Controller::HTTP_FORBIDDEN
			);
		} elseif ($result == -1) {
			$result = $this->antraglib->getDetailsForLastAntrag(
                $prestudent_id,
                [
                    Studierendenantrag_model::TYP_ABMELDUNG,
                    Studierendenantrag_model::TYP_ABMELDUNG_STGL
                ]
            );

			$data = $this->getDataOrTerminateWithError($result);
			
			$data->canCancel = (
				$data->status == Studierendenantragstatus_model::STATUS_CREATED &&
				$this->antraglib->isEntitledToCancelAntrag($data->studierendenantrag_id)
			);

			$this->terminateWithSuccess($data);
		}

		$result = $this->antraglib->getDetailsForNewAntrag($prestudent_id);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getDetailsForAntrag($studierendenantrag_id)
	{
		if (!$this->antraglib->isEntitledToShowAntrag($studierendenantrag_id))
			return show_404();

		$result = $this->antraglib->getDetailsForAntrag($studierendenantrag_id);
		
		$data = $this->getDataOrTerminateWithError($result);

		if ($data->typ !== Studierendenantrag_model::TYP_ABMELDUNG_STGL && $data->typ !== Studierendenantrag_model::TYP_ABMELDUNG)
			return show_404();

		$data->canCancel = (
			$data->status == Studierendenantragstatus_model::STATUS_CREATED &&
			$this->antraglib->isEntitledToCancelAntrag($data->studierendenantrag_id)
		);

		$this->terminateWithSuccess($data);
	}

	public function createAntrag()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('studiensemester', 'Studiensemester', 'required');
		$this->form_validation->set_rules('prestudent_id', 'Prestudent ID', 'required');
		$this->form_validation->set_rules('grund', 'Grund', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$grund = $this->input->post('grund');
		$studiensemester = $this->input->post('studiensemester');
		$prestudent_id = $this->input->post('prestudent_id');
		$unruly = $this->input->post('unruly');

		$result = $this->antraglib->getPrestudentAbmeldeBerechtigt($prestudent_id);
		$result = $this->getDataOrTerminateWithError($result);
		if (!$result)
			$this->terminateWithError($this->p->t('studierendenantrag', 'error_no_student'), self::ERROR_TYPE_GENERAL);
		elseif ($result == -3)
			$this->terminateWithError($this->p->t('studierendenantrag', 'error_stg_blacklist'), self::ERROR_TYPE_GENERAL);
		elseif ($result < 0)
			$this->terminateWithError($this->p->t('studierendenantrag', 'error_antrag_exists'), self::ERROR_TYPE_GENERAL);

		$result = $this->antraglib->createAbmeldung($prestudent_id, $studiensemester, getAuthUID(), $grund, $unruly);
		$data = $this->getDataOrTerminateWithError($result);

		$result = $this->antraglib->getDetailsForAntrag($data);
		if (!hasData($result))
			return $this->terminateWithSuccess(true);

		$data = getData($result);
		$data->canCancel = (boolean)$this->antraglib->isEntitledToCancelAntrag($data->studierendenantrag_id);

		$this->terminateWithSuccess($data);
	}

	public function cancelAntrag()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('antrag_id', 'Antrag ID', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$antrag_id = $this->input->post('antrag_id');

		if (!$this->antraglib->isEntitledToCancelAntrag($antrag_id))
			$this->terminateWithError('Forbidden', self::ERROR_TYPE_AUTH, REST_Controller::HTTP_FORBIDDEN);

		$result = $this->antraglib->cancelAntrag($antrag_id, getAuthUID());
		$this->getDataOrTerminateWithError($result);

		$result = $this->antraglib->getDetailsForAntrag($antrag_id);
		if (!hasData($result))
			$this->terminateWithSuccess($antrag_id);
		
		$data = getData($result);
		
		$this->terminateWithSuccess($data);
	}
}