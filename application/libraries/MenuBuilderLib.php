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
 * MenuBuilder library
 * TODO(chris): docu
 */
class MenuBuilderLib
{
	const GENERATOR_FUNC_KEY = 'generationFunc';

	protected $_ci;

	protected $config = [];

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Get code igniter instance
		$this->_ci =& get_instance();
	}

	// TODO(chris): abstract

	/**
	 * TODO(chris): comment
	 *
	 * @param string		$key
	 * @param mixed			$value
	 *
	 * @return array
	 */
	protected function mapUrlPartToVars($key, $value)
	{
		return [ $key, $value ];
	}

	protected function getPathTemplate($vars)
	{
		return implode('/', $vars['_url']) . '/%s';
	}

	protected function getLinkTemplate($vars)
	{
		return implode('/', $vars['_url']) . '/%s';
	}

	protected function buildMenu()
	{
		return $this->buildMenuRecursive($this->config, [
			'_url' => [],
			'_path' => []
		]);
	}

	private function buildMenuRecursive($config, $vars)
	{
		$result = [];

		foreach ($config as $key => $conf) {
			$res = $this->buildConfigItem($key, $conf, $vars);
			if (isset($conf['children'])) {
				foreach ($res as $k => $menuitem) {
					// convert stdClass to associative array if necessary
					if (!is_array($menuitem)) {
						$menuitem = get_object_vars($menuitem);
						$res[$k] = $menuitem;
					}
					
					$child_vars = $menuitem;
					unset($child_vars['name']);
					$child_vars['_url'] = explode('/', $child_vars['path']);
					unset($child_vars['path']);
					$child_vars = array_merge($vars, $child_vars);
					$child_vars['_path'][] = $key;
					
					$res[$k]['children'] = $this->buildMenuRecursive($conf['children'], $child_vars);
				}
			}
			$result = array_merge($result, $res);
		}

		return $result;
	}

	protected function buildSubmenu($url_segments)
	{
		$vars = $this->convertUrlPathToVars($url_segments);
		if ($vars === null)
			return null;

		$config = $this->getSubconfig($vars['_path']);
		if ($config === null)
			return null;

		$result = [];

		foreach ($config as $key => $conf) {
			$res = $this->buildConfigItem($key, $conf, $vars);
			$result = array_merge($result, $res);
		}

		return $result;
	}

	/**
	 * @param array			$path
	 *
	 * @return array|null
	 */
	private function getSubconfig($path)
	{
		$config = $this->config;
	
		while (count($path)) {
			$part = array_shift($path);
	
			if (!isset($config[$part]))
				return null;
			if (!isset($config[$part]['children']))
				return null;
	
			$config = $config[$part]['children'];
		}

		return $config;
	}

	/**
	 * @param array			$url_segments
	 * @return array|null
	 */
	private function convertUrlPathToVars($url_segments)
	{
		$config = $this->config;

		$result = [
			'_url' => $url_segments,
			'_path' => []
		];
		while (count($url_segments)) {
			if (!$config)
				return null;

			$segment = array_shift($url_segments);

			if (!isset($config[$segment]))
				return null;

			$config = $config[$segment];
			$result['_path'][] = $segment;

			if (isset($config[self::GENERATOR_FUNC_KEY])) {
				$value = array_shift($url_segments);
				list($key, $value) = $this->mapUrlPartToVars($segment, $value);
				$result[$key] = $value;
			}

			$config = isset($config['children']) ? $config['children'] : null;
		}

		return $result;
	}

	protected function buildConfigItem($key, $config, $vars)
	{
		if (isset($config[self::GENERATOR_FUNC_KEY]))
			return $this->buildItemWithMethod($key, $config, $vars);
		else
			return $this->buildGenericItem($key, $config, $vars);
	}

	protected function buildItemWithMethod($key, $config, $vars)
	{
		$vars['_url'][] = $key;
		
		$method = $config[self::GENERATOR_FUNC_KEY];
		
		return $this->$method($vars);
	}

	protected function buildGenericItem($key, $config, $vars)
	{
		$url_segments = $vars['_url'];
		$url_segments[] = $key;
		
		$result = array_merge($config, $vars);
		
		if (!isset($result['children']))
			$result['leaf'] = true;
		else
			unset($result['children']);

		$result['path'] = sprintf($this->getPathTemplate($result), $key);
		$result['link'] = sprintf($this->getLinkTemplate($result), $key);
		
		unset($result['_url']);
		unset($result['_path']);

		return [ $result ];
	}
}
