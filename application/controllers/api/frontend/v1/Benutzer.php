<?php
/**
 * Copyright (C) 2026 fhcomplete.org
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

class Benutzer extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getUserData' => self::PERM_LOGGED,
		]);

		$this->load->model('person/Benutzer_model', 'BenutzerModel');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getUserData()
	{
		$uid = $this->input->get("uid") ?? getAuthUID();
		$this->BenutzerModel->addSelect("uid, person_id, insertamum");
		$userResult = $this->BenutzerModel->load(["uid" => $uid]);
		$userData = $this->getDataOrTerminateWithError($userResult);
		$this->terminateWithSuccess($userData);
	}
}

