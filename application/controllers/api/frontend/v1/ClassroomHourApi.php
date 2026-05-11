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
class ClassroomHourApi extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getAllClassroomHours' => self::PERM_LOGGED,
		]);

		$this->load->model('ressource/Stunde_model', "ClassroomHourModel");

	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getAllClassroomHours()
	{
		$this->ClassroomHourModel->addOrder('stunde', 'ASC');
		$result = $this->ClassroomHourModel->load();

		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}
}

