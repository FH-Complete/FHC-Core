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

require_once(APPPATH . 'libraries/MenuBuilderLib.php');
require_once(APPPATH . 'traits/menu/StgTrait.php');
require_once(APPPATH . 'traits/menu/InoutTrait.php');

use \ReflectionMethod as ReflectionMethod;

/**
 * StudVw Menu library
 */
class StvMenuLib extends MenuBuilderLib
{
	use StgTrait, InoutTrait;

	protected $children = [
		'stg',
		'inout'
	];

	public function build($url_segments = [])
	{
		$result = $this->buildSubmenu($url_segments);

		if ($result === null)
			show_404();

		return $result;
	}
	public function buildAll()
	{
		return $this->buildMenu();
	}

	protected function getLinkTemplate($path, $vars)
	{
		$result = '';

		$children = $this->children;
		while (count($path)) {
			$segment = array_shift($path);
			$key = array_search($segment, $children);
			$config = $this->getNodeConfig($key, $segment);
			if (isset($config['identifiers'])) {
				if (is_array($config['identifiers'])) {
					$count = count($config['identifiers']);
				} else {
					$reflection = new ReflectionMethod($this, $config['identifiers']);
					$count = $reflection->getNumberOfParameters();
				}
				while ($count--) {
					if (count($path))
						$result .= array_shift($path) . '/';
				}
			} else {
				$result .= $segment . '/';
			}

			if (isset($config['children']))
				$children = $config['children'];
			else
				return [];
		}

		if (!strpos($result, '/prestudent') && !isset($vars['no_sem_reload']))
			$result = 'CURRENT_SEMESTER/' . $result;

		return $result . '%s';
	}
}
