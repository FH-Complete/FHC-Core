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

class AuthInfo extends FHCAPI_Controller
{
	protected $uid;
	protected $pid;
	protected $isMitarbeiter;
	protected $isStudent;

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getAuthUID' => self::PERM_LOGGED,
			'getAuthInfo' => self::PERM_LOGGED,
		]);

		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');

		$this->uid = getAuthUID();
		$this->pid = getAuthPersonID();
		$this->isMitarbeiter = getData($this->MitarbeiterModel->isMitarbeiter($this->uid)) ?? false;
		$this->isStudent = getData($this->StudentModel->isStudent($this->uid)) ?? false;
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * returns the uid of the currently logged in user
	 * @access public
	 *
	 */
	public function getAuthUID()
	{
		$this->terminateWithSuccess(['uid'=>$this->uid]);
	}
	
	public function getAuthInfo()
	{
		$data = (object) array(
				'uid' => $this->uid,
				'isMitarbeiter' => $this->isMitarbeiter,
				'isStudent' => $this->isStudent
		);
		$this->terminateWithSuccess($data);
	}
}

