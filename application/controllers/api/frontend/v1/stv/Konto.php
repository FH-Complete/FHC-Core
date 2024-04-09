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
 * Provides data to the ajax get calls about a Konto
 * Listens to ajax post calls to change the Konto data
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Konto extends FHCAPI_Controller
{
	/**
	 * Calls the parent's constructor and prepares libraries and phrases
	 */
	public function __construct()
	{
		// TODO(chris): permissions
		parent::__construct([
			'get' => 'student/stammdaten:r'
		]);

		// Load models
		$this->load->model('crm/Konto_model', 'KontoModel');

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Get details for a prestudent
	 *
	 * @param string			$type
	 * @param string			(optional) $studiengang_kz
	 * @return void
	 */
	public function get($type, $studiengang_kz = '')
	{
		// TODO(chris): validation

		$person_id = $this->input->post('person_id');

		if ($this->input->post('only_open')) {
			$result = $this->KontoModel->getOffeneBuchungen($person_id, $studiengang_kz);
		} else {
			$result = $this->KontoModel->getAlleBuchungen($person_id, $studiengang_kz);
		}

		#$result = $this->getDataOrTerminateWithError($result);
		if (isError($result))
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		$result = $result->retval;

		// sort into tree
		$childs = [];
		$data = [];
		foreach ($result as $entry) {
			if ($entry->buchungsnr_verweis) {
				if (isset($data[$entry->buchungsnr_verweis])) {
					if (!isset($data[$entry->buchungsnr_verweis]->_children))
						$data[$entry->buchungsnr_verweis]->_children = [];
					$data[$entry->buchungsnr_verweis]->_children[] = $entry;
				} else {
					if (!isset($childs[$entry->buchungsnr_verweis]))
						$childs[$entry->buchungsnr_verweis] = [];
					$childs[$entry->buchungsnr_verweis][] = $entry;
				}
			} else {
				$data[$entry->buchungsnr] = $entry;
				if (isset($childs[$entry->buchungsnr]))
					$entry->_children = $childs[$entry->buchungsnr];
			}
		}

		$this->terminateWithSuccess(array_values($data));
	}
}
