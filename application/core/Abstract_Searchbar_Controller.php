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
 * This controller operates between (interface) the JS (GUI) and the SearchBarLib (back-end)
 * Provides data to the ajax get calls about the searchbar component
 * This controller works with JSON calls on the HTTP GET and the output is always JSON
 */
class Searchbar extends FHCAPI_Controller
{
	const SEARCHSTR_PARAM = 'searchstr';
	const TYPES_PARAM = 'types';

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		// NOTE(chris): additional permission checks will be done in SearchBarLib
		parent::__construct([
			'search' => self::PERM_LOGGED
		]);

		// Load the library SearchBarLib
		$this->load->library('SearchBarLib');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Gets a JSON body via HTTP POST and provides the parameters
	 */
	public function search()
	{
		$this->load->library('form_validation');

		// Checks if the searchstr and the types parameters are in the POSTed JSON
		$this->form_validation->set_rules(self::SEARCHSTR_PARAM, null, 'required');
		$this->form_validation->set_rules(self::TYPES_PARAM . '[]', null, 'required');

		if (!$this->form_validation->run())
			$this->terminateWithError(SearchBarLib::ERROR_WRONG_JSON, self::ERROR_TYPE_GENERAL);

		// Convert to json the result from searchbarlib->search
		$result = $this->searchbarlib->search($this->input->post(self::SEARCHSTR_PARAM), $this->input->post(self::TYPES_PARAM));
		if (property_exists($result, 'error'))
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		$this->terminateWithSuccess($result);
	}
}

