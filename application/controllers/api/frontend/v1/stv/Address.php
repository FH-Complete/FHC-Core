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
 * Provides data to the ajax get calls about addresses
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Address extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getNations' => self::PERM_LOGGED,
			'getPlaces' => self::PERM_LOGGED
		]);
	}

	public function getNations()
	{
		$this->load->model('codex/Nation_model', 'NationModel');

		$this->NationModel->addOrder('kurztext');

		$result = $this->NationModel->load();
		$data = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($data);
	}

	public function getPlaces($plz)
	{
		$this->load->model('codex/Gemeinde_model', 'GemeindeModel');

		$this->load->library('form_validation');
		
		$this->form_validation->set_data(['address.plz' => $plz]);

		$this->form_validation->set_rules('address.plz', 'PLZ', 'numeric|less_than[10000]');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$result = $this->GemeindeModel->getGemeindeByPlz($plz);
		$data = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($data);
	}
}
