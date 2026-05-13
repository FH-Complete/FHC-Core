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
 * MenuBuilder library
 * TODO(chris): docu
 */
class MenuBuilderLib
{
	protected $_ci;

	protected $children = [];

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Get code igniter instance
		$this->_ci =& get_instance();
	}

	/*private function registerTraits()
	{
		$class = $this;
		$traits = [];

		do {
			$traits = array_merge(class_uses($class), $traits);
		} while ($class = get_parent_class($class));

		$config = $this->registerTraitsRecursive($traits);
		$this->_ci->addMeta('test', $config);
	}

	private function registerTraitsRecursive($traits)
	{
		// TODO(chris): implement
		$children = [];
		foreach ($traits as $name => $trait) {
			$traitId = ucfirst(str_replace("Trait", "", $name));
			$initMethod = "init" . $traitId;
			$this->_ci->addMeta('traits', $initMethod);
			$child = $this->$initMethod();
			if (!isset($child['alias']))
				$child['alias'] = strtolower($traitId);
			$children[$child['alias']] = $child;
			$childTraits = class_uses($trait);
			$children[$child['alias']]['children'] = $this->registerTraits($childTraits);
		}
		return $children;
	}*/

	// TODO(chris): abstract

	final protected function getPathTemplate($path)
	{
		return implode('/', $path) . '/%s';
	}

	protected function getLinkTemplate($path, $vars)
	{
		return implode('/', $path) . '/%s';
	}

	protected function buildMenu()
	{
		return $this->buildMenuRecursive($this->children, [], []);
	}

	private function buildMenuRecursive($children, $identifiers, $path)
	{
		$result = [];

		foreach ($children as $key => $segment) {
			$node_config = $this->getNodeConfig($key, $segment);

			$nodes = $this->buildNode($node_config, $segment, $identifiers, $path);

			foreach ($nodes as $k => $node) {
				// Convert stdClass to array
				if (!is_array($node))
					$node = get_object_vars($node);

				// Render children
				if (isset($node_config['children'])) {
					$node_path = explode('/', $node['path']);
					
					$node_identifiers = $identifiers;
					if (isset($node_config['identifiers'])) {
						if (is_string($node_config['identifiers'])) {
							$reflection = new ReflectionMethod($this, $node_config['identifiers']);
							$num_segments = $reflection->getNumberOfParameters();
							$parameters = array_slice($node_path, $num_segments * -1);
							$node_identifiers = call_user_func_array([$this, $node_config['identifiers']], $parameters);
							$node_identifiers = array_merge($identifiers, $node_identifiers);
						} else {
							if (count($node_path) < count($node_config['identifiers']))
								return null; // NOTE(chris): wrong number of url segments

							foreach ($node_config['identifiers'] as $index => $id_name) {
								$pos = count($node_path) - count($node_config['identifiers']) + $index;
								$node_identifiers[$id_name] = $node_path[$pos];
							}
						}
					}

					$node['children'] = $this->buildMenuRecursive($node_config['children'], $node_identifiers, $node_path);
				} else {
					$node['leaf'] = true;
				}
				$nodes[$k] = $node;
			}

			$result = array_merge($result, $nodes);
		}

		return $result;
	}

	final protected function getNodeConfig($key, $segment)
	{
		$traitname = is_int($key) ? $segment : $key;
		$initFunc = 'init' . ucfirst($traitname);
		
		// TODO(chris): check identifiers string: single or function??

		return $this->$initFunc();
	}

	private function buildNode($config, $segment, $identifiers, $path)
	{// TODO(chris): why slash at beginning: "/inout"
		if (isset($config['build'])) {
			$buildFunc = $config['build'];
			$path[] = $segment;
			$pathTemplate = $this->getPathTemplate($path);
			$linkTemplate = $this->getLinkTemplate($path, $identifiers);
			return $this->$buildFunc($identifiers, $pathTemplate, $linkTemplate);
		} else {
			return $this->buildGenericItem($segment, $config, $identifiers, $path);
		}
	}

	/**
	 * @param array			$url_segments
	 * @return array|null
	 */
	protected function buildSubmenu($url_segments)
	{
		$children = $this->children;
		$original_path = $url_segments;

		$segment = '';
		$identifiers = [];
		$config = [];

		while(count($url_segments)) {
			$segment = array_shift($url_segments);
			$key = array_search($segment, $children);
			if ($key === false)
				return []; // NOTE(chris): node not found

			$config = $this->getNodeConfig($key, $segment);

			if (!isset($config['build']) && !isset($config['name']))
				return null; // TODO(chris): invalid config
			
			if (isset($config['identifiers'])) {
				if (is_string($config['identifiers'])) {
					$reflection = new ReflectionMethod($this, $config['identifiers']);
					$num_segments = $reflection->getNumberOfParameters();
					$parameters = array_slice($url_segments, 0, $num_segments);
					$url_segments = array_slice($url_segments, $num_segments);
					$new_identifiers = call_user_func_array([$this, $config['identifiers']], $parameters);
					$identifiers = array_merge($identifiers, $new_identifiers);
				} else {
					if (count($url_segments) < count($config['identifiers']))
						return null; // NOTE(chris): wrong number of url segments

					foreach ($config['identifiers'] as $id_name) {
						$identifiers[$id_name] = array_shift($url_segments);
					}
				}
			}

			if (isset($config['children'])) {
				$children = $config['children'];
			} elseif (count($url_segments)) {
				return null; // NOTE(chris): wrong number of url segments
			} else {
				return [];
			}
		}

		$result = [];

		foreach ($children as $key => $segment) {
			$node_config = $this->getNodeConfig($key, $segment);
			$nodes = $this->buildNode($node_config, $segment, $identifiers, $original_path);

			$result = array_merge($result, $nodes);
		}

		return $result;
	}

	protected function buildGenericItem($segment, $config, $identifiers, $path)
	{
		$vars = isset($config['vars']) ? $config['vars'] : [];
		
		$vars = array_merge($identifiers, $vars);

		$vars['name'] = $config['name'];

		if (!isset($config['children']))
			$vars['leaf'] = true;

		$vars['path'] = sprintf($this->getPathTemplate($path), $segment);
		$vars['link'] = sprintf($this->getLinkTemplate($path, $vars), $segment);
		
		return [ $vars ];
	}
}
