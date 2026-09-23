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

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 */
class Admin extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Set required permissions
		parent::__construct(
			array(
				'index' => 'dashboard/admin:rw',
				'preview' => 'dashboard/admin:r',
			)
		);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods
	public function index()
	{
		$this->load->view('dashboard/admin.php', []);
	}

	public function preview($dashboard_kurzbz = 'CIS')
	{
		$this->load->view('dashboard/preview.php', [
			'dashboard_kurzbz' => $dashboard_kurzbz
		]);
	}
}
