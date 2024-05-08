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

use \Studierendenantrag_model as Studierendenantrag_model;
use \DateTime as DateTime;

/**
 * This controller operates between (interface) the JS (GUI) and the AntragLib (back-end)
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Unterbrechung extends FHCAPI_Controller
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

		// Configs
		$this->load->config('studierendenantrag');

		// Libraries
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
		if (!$this->antraglib->isEntitledToCreateAntragFor($prestudent_id, false))
			$this->terminateWithError('Forbidden', self::ERROR_TYPE_AUTH, REST_Controller::HTTP_FORBIDDEN);

		$result = $this->antraglib->getPrestudentUnterbrechungsBerechtigt($prestudent_id);
		$result = $this->getDataOrTerminateWithError($result);

		if (!$result) {
			$this->terminateWithError(
				$this->p->t('studierendenantrag', 'error_no_student'),
				self::ERROR_TYPE_AUTH,
				REST_Controller::HTTP_FORBIDDEN
			);
		} elseif ($result == -1) {
			$result = $this->antraglib->getDetailsForLastAntrag($prestudent_id, Studierendenantrag_model::TYP_UNTERBRECHUNG);
			
			$data = $this->getDataOrTerminateWithError($result);

			return $this->terminateWithSuccess($data);
		} elseif ($result == -2) {
			$result = $this->antraglib->getDetailsForLastAntrag($prestudent_id);

			$data = $this->getDataOrTerminateWithError($result);

			return $this->terminateWithError($this->p->t('studierendenantrag', 'error_antrag_pending', [
                'typ' => $this->p->t('studierendenantrag', 'antrag_typ_' . $result->typ)
            ]));
		} elseif ($result == -3) {
			$this->terminateWithError(
				$this->p->t('studierendenantrag', 'error_stg_blacklist'),
				self::ERROR_TYPE_AUTH,
				REST_Controller::HTTP_FORBIDDEN
			);
		}

		$result = $this->antraglib->getDetailsForNewAntrag($prestudent_id);

		$data = $this->getDataOrTerminateWithError($result);

		$data->studiensemester = $this->antraglib->getSemesterForUnterbrechung($prestudent_id, null);

		$this->terminateWithSuccess($data);
	}

	public function getDetailsForAntrag($studierendenantrag_id)
	{
		if (!$this->antraglib->isEntitledToShowAntrag($studierendenantrag_id))
			return show_404();

		$result = $this->antraglib->getDetailsForAntrag($studierendenantrag_id);

		$data = $this->getDataOrTerminateWithError($result);

		if ($data->typ !== Studierendenantrag_model::TYP_UNTERBRECHUNG)
			return show_404();

		$this->terminateWithSuccess($data);
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

		if (!$this->form_validation->run()) {
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$grund = $this->input->post('grund');
		$studiensemester = $this->input->post('studiensemester');
		$prestudent_id = $this->input->post('prestudent_id');
		$datum_wiedereinstieg = $this->input->post('datum_wiedereinstieg');
		$dms_id = null;

		$result = $this->antraglib->getPrestudentUnterbrechungsBerechtigt($prestudent_id, $studiensemester, $datum_wiedereinstieg);

		$result = $this->getDataOrTerminateWithError($result);
		
		if (!$result)
			$this->terminateWithError($this->p->t('studierendenantrag', 'error_no_student'), self::ERROR_TYPE_GENERAL);
		elseif ($result == -3)
			$this->terminateWithError($this->p->t('studierendenantrag', 'error_stg_blacklist'), self::ERROR_TYPE_GENERAL);
		elseif ($result < 0)
			$this->terminateWithError($this->p->t('studierendenantrag', 'error_antrag_exists'), self::ERROR_TYPE_GENERAL);
		
		if (isset($_FILES['attachment']) && (!isset($_FILES['attachment']['error']) || $_FILES['attachment']['error'] != UPLOAD_ERR_NO_FILE)) {
			$this->load->library('DmsLib');

			$dms = $this->config->item('unterbrechung_dms');
			if (!count(array_filter($dms, function ($v) {
				return $v !== null;
			})))
				$dms = ['kategorie_kurzbz' => 'Akte'];
			$dms['version'] = 0;

			$allowed_filetypes = $this->config->item('unterbrechung_dms_filetypes') ?: ['*'];
			$result = $this->dmslib->upload($dms, 'attachment', $allowed_filetypes);

			$data = $this->getDataOrTerminateWithError($result);
			
			$dms_id = $data['dms_id'];
		}

		$result = $this->antraglib->createUnterbrechung($prestudent_id, $studiensemester, getAuthUID(), $grund, $datum_wiedereinstieg, $dms_id);

		$antragId = $this->getDataOrTerminateWithError($result);
		
		$result = $this->antraglib->getDetailsForAntrag($antragId);

		if (!hasData($result))
			$this->terminateWithSuccess($antragId);

		$this->terminateWithSuccess(getData($result));
	}

	public function cancelAntrag()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('antrag_id', 'Antrag ID', 'required');

		if (!$this->form_validation->run()) {
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$antrag_id = $this->input->post('antrag_id');

		$result = $this->antraglib->cancelAntrag($antrag_id, getAuthUID());

		$this->getDataOrTerminateWithError($result);

		$result = $this->antraglib->getDetailsForAntrag($antrag_id);

		if (!hasData($result))
			return $this->terminateWithSuccess($antrag_id);
	
		$this->terminateWithSuccess(getData($result));
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
