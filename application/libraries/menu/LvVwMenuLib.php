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

use \ReflectionMethod as ReflectionMethod;

/**
 * LvVw Menu library
 */
class LvVwMenuLib extends MenuBuilderLib
{
	use StgTrait;

	protected $children = [
		'ModifiedStg' => 'stg'
	];

	public function build($url_segments = [])
	{
		$result = $this->buildSubmenu($url_segments);

		if ($result === null)
			show_404();

		return $result;
	}

	protected function initModifiedStg()
	{
		$config = $this->initStg();
		$config['children'] = [
			'ModifiedSemester' => 'semester',
			'ModifiedOrgform' => 'orgform'
		];
		return $config;
	}

	protected function initModifiedSemester()
	{
		$config = $this->initSemester();
		unset($config['children']);
		$config['build'] = 'getModifiedSemester';
		return $config;
	}

	protected function getModifiedSemester($vars, $pathTemplate, $linkTemplate)
	{
		$result = $this->getSemester($vars, $pathTemplate, $linkTemplate);

		foreach (array_keys($result) as $key)
			$result[$key]->leaf = true;

		return $result;
	}

	protected function initModifiedOrgform()
	{
		$config = $this->initOrgform();
		unset($config['children']);
		$config['build'] = 'getModifiedOrgform';
		return $config;
	}

	protected function getModifiedOrgform($vars, $pathTemplate, $linkTemplate)
	{
		$result = $this->getOrgform($vars, $pathTemplate, $linkTemplate);

		foreach (array_keys($result) as $key)
			$result[$key]->leaf = true;

		return $result;
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
				return '%s';
		}

		return $result . '%s';
	}
}
