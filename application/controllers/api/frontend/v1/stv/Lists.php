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
 * Provides data to the ajax get calls about generally used lists
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Lists extends FHCAPI_Controller
{
	public function __construct()
	{
		// TODO(chris): permissions
		parent::__construct([
			'getStudiensemester' => ['admin:r', 'assistenz:r', 'student/stammdaten:r'], // alle?
			'getStgs' => ['admin:r', 'assistenz:r', 'student/stammdaten:r'] // alle?
		]);
	}

	public function getStudiensemester()
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$this->StudiensemesterModel->addOrder('ende');

		$result = $this->StudiensemesterModel->load();

		#$data = $this->getDataOrTerminateWithError($result);
		if (isError($result))
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		$data = $result->retval;

		$this->terminateWithSuccess($data);
	}

	public function getStgs()
	{
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$this->StudiengangModel->addSelect('*');
		$this->StudiengangModel->addSelect('UPPER(typ || kurzbz) AS kuerzel');

		$this->StudiengangModel->addOrder('typ');
		$this->StudiengangModel->addOrder('kurzbz');

		$result = $this->StudiengangModel->load();

		#$data = $this->getDataOrTerminateWithError($result);
		if (isError($result))
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		$data = $result->retval;

		$this->terminateWithSuccess($data);
	}
}
