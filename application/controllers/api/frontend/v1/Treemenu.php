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

use \ReflectionMethod as ReflectionMethod;

/**
 * This controller operates between (interface) the JS (GUI) and the back-end
 * Provides data to the ajax get calls about menues
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Treemenu extends FHCAPI_Controller
{
	public function __construct()
	{
		// Load Router
		$router = load_class('Router');

		// Load Config
		$config = load_class('Config');
		$config->load('treemenu');

		// Get Treemenu name & config
		$name = $router->method;
		$conf = $config->item($name);
		
		// Get Permissions from config
		$permissions = $conf['permissions'] ?? self::PERM_ANONYMOUS;

		// Call constructor
		parent::__construct([
			$name => $permissions
		]);

		// Load Library
		$libraryName = $conf['library'] ?? null;
		$this->load->library($libraryName, null, 'treemenulib');
	}
	
	/**
	 * @param string		$method
	 * @param array			$params				(optional)
	 *
	 * @return void
	 */
	public function _remap($method, $params = [])
	{
		if (!$this->treemenulib)
			show_404();

		$this->addMeta('ci_method', $method);
		$this->addMeta('ci_params', $params);
		
		$params = $this->uri->uri_to_assoc(6); // 6 = start of $params
		$config = $this->treemenulib->config;

		foreach (array_keys($params) as $key) {
			if (!isset($config[$key]) || !$config[$key])
				show_404();

			$config = $config[$key];
		}

		// Prepare aliases
		$argumentaliases = [];
		if (property_exists($this->treemenulib, 'path_to_argument'))
			$argumentaliases = array_flip($this->treemenulib->path_to_argument);
		$methodaliases = [];
		if (property_exists($this->treemenulib, 'redirect_method'))
			$methodaliases = $this->treemenulib->redirect_method;
		
		$items = [];
		foreach ($config as $uripart => $children) {
			if (!is_array($children)) {
				$uripart = $children;
				$children = [];
			}

			$methodName = $uripart;
			if (isset($methodaliases[$methodName])) {
				$methodName = $methodaliases[$methodName];
			} elseif (strpbrk($methodName, ' _-') !== false) {
				$methodName = lcfirst(str_replace([' ', '_', '-'], '', ucwords($methodName, " \t\r\n\f\v_-")));
			}

			// Prepare arguments
			$reflection = new ReflectionMethod($this->treemenulib, $methodName);
			$arguments = [];
			foreach ($reflection->getParameters() as $arg) {
				if ($arg->name == 'path_template') {
					$path = $params;
					$path[$uripart] = '%s';
					$arguments[] = $this->uri->assoc_to_uri($path);
				} elseif ($arg->name == 'has_children') {
					$arguments[] = $children;
				} elseif ($arg->name == 'original_method') {
					$arguments[] = $uripart;
				} elseif (isset($argumentaliases[$arg->name]) && isset($params[$argumentaliases[$arg->name]])) {
					$arguments[] = $params[$argumentaliases[$arg->name]];
				} elseif (isset($params[$arg->name])) {
					$arguments[] = $params[$arg->name];
				} else {
					$arguments[] = null;
				}
			}

			// Call function from lib
			$items = array_merge($items, call_user_func_array([$this->treemenulib, $methodName], $arguments));
		}

		$this->terminateWithSuccess($items);
	}
}
