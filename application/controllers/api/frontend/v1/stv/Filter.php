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

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller operates between (interface) the JS (GUI) and the back-end
 * Provides data to the ajax get calls about the Studiengang filter
 * Listens to ajax post calls to change the Studiengang filter data
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Filter extends FHCAPI_Controller
{
	/**
	 * Calls the parent's constructor and prepares libraries and phrases
	 */
	public function __construct()
	{
		parent::__construct([
			'getStg' => self::PERM_LOGGED,
			'setStg' => self::PERM_LOGGED
		]);

		// Load models
		$this->load->model('system/Variable_model', 'VariableModel');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Get current setting
	 *
	 * @return void
	 */
	public function getStg()
	{
		$result = $this->VariableModel->getVariables(getAuthUID(), ['kontofilterstg']);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data['kontofilterstg'] == 'true');
	}

	/**
	 * Set current setting
	 *
	 * @return void
	 */
	public function setStg()
	{
		$this->load->library('form_validation');

		$studiengang_kz = $this->input->post('studiengang_kz');

		if ($studiengang_kz === null) {
			$this->form_validation->set_rules('studiengang_kz', 'Studiengang', 'required');

			if (!$this->form_validation->run())
				$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->VariableModel->setVariable(getAuthUID(), 'kontofilterstg', $studiengang_kz ? 'true' : 'false');

		$this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(true);
	}
}
