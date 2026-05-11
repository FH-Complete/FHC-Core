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
 * This controller operates between (interface) the JS (GUI) and the back-end
 * Provides data to the ajax get calls about menues
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Menu extends FHCAPI_Controller
{
	public function __construct()
	{
		$permissions = [];
		$router = load_class('Router');
		// TODO(chris): permission
		$permissions[$router->method] = ['admin:r', 'assistenz:r'];
		parent::__construct($permissions);

		// Load Config
		$this->config->load('menubuilder');
	}
	
	/**
	 * @param string		$method
	 * @param array			$params				(optional)
	 *
	 * @return void
	 */
	public function _remap($method, $params = [])
	{
		$this->load->library($this->config->item($method), null, 'menulib');
		
		if (!$this->menulib)
			show_404();
		
		$submenu = $this->menulib->build($params);

		$this->terminateWithSuccess($submenu);
	}
}
