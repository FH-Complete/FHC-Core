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
 * This controller redirects urls to another location. This is mainly used
 * with the $routes config
 */
class Redirect extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Main function to handle all redirects.
	 *
	 * @param string				$method - the redirect code (30x)
	 * @param array					$params - the url to redirect to
	 *
	 * @return void
	 */
	public function _remap($method, $params)
	{
		redirect($params, 'location', $method);
	}
}
