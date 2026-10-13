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

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Permission extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getPermissions' => self::PERM_LOGGED,
		]);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * retrieves permissions for auth user
	 * @access public
	 */
	public function getPermissions()
	{
		$permissionsParam = $this->input->post("permissions");
		if (!$permissionsParam || !is_array($permissionsParam)) {
			$this->terminateWithError("Invalid parameter!");
		}

		$permissions = [];
		foreach ($permissionsParam as $permission) {
			$permissions[$permission] = $this->permissionlib->isBerechtigt($permission);
		}

		$this->terminateWithSuccess($permissions);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

}

