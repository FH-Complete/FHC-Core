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

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller operates between (interface) the JS (GUI) and the back-end
 * Provides data to the ajax get calls about verbände
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class TreeMenu extends FHCAPI_Controller
{
	protected $treemenuconfig;

	public function __construct()
	{
		parent::__construct([
			'fullMenu' => ['admin:r', 'assistenz:r'],
			'partMenu' => ['admin:r', 'assistenz:r'],
		]);
	}

	public function fullMenu($treemenu=null)
	{
		$includeSubMenu = true;
		$this->getMenu($treemenu, $includeSubMenu);
	}

	public function partMenu($treemenu=null)
	{
		$includeSubMenu = false;
		$this->getMenu($treemenu, $includeSubMenu);
	}

	protected function getMenu($treemenu=null, $includeSubMenu=false)
	{
		if(is_null($treemenu))
		{
			$this->terminateWithError('missing parameter treemenu.');
		}

		$this->loadMenuConfig($treemenu);

		$uri = $this->uri->uri_to_assoc(7);
		$startconfig = $this->findStartLib($this->treemenuconfig, array_keys($uri));

		$libpath = $startconfig['library'];
		$children = isset($startconfig['children']) ? $startconfig['children'] : array();
		$libname = basename($startconfig['library']);

		$this->load->library(
			$libpath,
			$children,
			$libname
		);

		$bhdebug = (object) array(
			'treemenu' => $treemenu,
			'treemenuconfig' => $this->treemenuconfig,
			'uri' => $uri,
			'libpath' => $libpath,
			'libname' => $libname,
			'children' => $children,
			'startconfig' => $startconfig
		);
		$this->addMeta('bhdebug', $bhdebug);
		//$this->addMeta('bhci', $this);

		$this->$libname->init($uri, $includeSubMenu);
		$data = $this->$libname->getSubMenu();
		$this->terminateWithSuccess($data);
	}

	protected function findStartLib($config, $uri)
	{
		$level = array_shift($uri);
		if(is_null($level)) {
			return $config;
		}
		return $this->findStartLib($config['children'][$level], $uri);
	}

	protected function loadMenuConfig($treemenu)
	{
		$this->config->load('treemenu/' . $treemenu . '.php');
		$this->treemenuconfig = $this->config->item($treemenu);
	}
}