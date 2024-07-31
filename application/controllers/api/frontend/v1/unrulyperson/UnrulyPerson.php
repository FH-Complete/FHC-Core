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

class UnrulyPerson extends FHCAPI_Controller
{

	// TODO: BERECHTIGUNGEN
	public function __construct()
	{
		parent::__construct([
			'updatePersonUnrulyStatus' => 'basis/mitarbeiter:rw'
		]);

		$this->_ci =& get_instance();
		$this->_ci->load->model('person/Person_model', 'PersonModel');
	}

	public function updatePersonUnrulyStatus()
	{
		$data = json_decode($this->input->raw_input_stream, true);

		$person_id = $data['person_id'];
		$unruly = $data['unruly'];

		$result = $this->_ci->PersonModel->updateUnruly($person_id, $unruly);

		if(isError($result)) {
			$this->terminateWithError($result);
		} else if (isSuccess($result)) {
			$this->terminateWithSuccess($result);
		}

	}
}