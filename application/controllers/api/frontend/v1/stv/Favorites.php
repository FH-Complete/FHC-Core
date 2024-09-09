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

/**
 * This controller operates between (interface) the JS (GUI) and the back-end
 * Provides data to the ajax get calls about favorite verbände
 * Listens to ajax post calls to change the favorite verbände data
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Favorites extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'index' => self::PERM_LOGGED,
			'set' => self::PERM_LOGGED
		]);

		// Load models
		$this->load->model('system/Variable_model', 'VariableModel');

		// TODO(chris): variable table might be to small to store favorites!
	}

	public function index()
	{
		$result = $this->VariableModel->getVariables(getAuthUID(), ['stv_favorites']);

		$data = $this->getDataOrTerminateWithError($result);

		if (!$data)
			$this->terminateWithSuccess(null);
		else
			$this->terminateWithSuccess($data['stv_favorites']);
	}

	public function set()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('favorites', 'Favorites', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$favorites = $this->input->post('favorites');

		$result = $this->VariableModel->setVariable(getAuthUID(), 'stv_favorites', $favorites);

		$this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess(true);
	}
}
