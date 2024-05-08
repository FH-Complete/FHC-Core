<?php
/**
 * Copyright (C) 2022 fhcomplete.org
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
 * NavigationWidget logic
 */
class NavigationLib
{
	// Session parameters names
	const SESSION_NAME = 'FHC_NAVIGATION_WIDGET'; // Navigation session name
	const SESSION_MENU_NAME = 'navigation_menu';
	const SESSION_HEADER_NAME = 'navigation_header';

	// Configuration names
	const CONFIG_MENU_NAME = 'navigation_menu';
	const CONFIG_HEADER_NAME = 'navigation_header';
	const CONFIG_NAVIGATION_FILENAME = 'navigation.php';

	const NAVIGATION_PAGE_PARAM = 'navigation_page'; // Navigation page parameter name

	const PERMISSION_NAVIGATION_METHOD = 'NavigationWidget'; // Name for fake method to be checked by the PermissionLib

	private $_ci; // Code igniter instance
	private $_navigationPage; // unique id for this navigation widget

	/**
	 * Gets the CI instance and loads message helper
	 */
	public function __construct($params = null)
	{
		$this->_ci =& get_instance(); // get code igniter instance

		// Loads navigation configs
		$this->_ci->config->load('navigation');

		// Loads library ExtensionsLib
		$this->_ci->load->library('ExtensionsLib');

		$this->_navigationPage = $this->_getNavigationPage($params); // sets the id for the related navigation widget
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Creates the left menu for each Page
	 * @param navigationPage GET Parameter witch holds the currently called Page
	 * @return array with the Menu Entries
	 */
	public function getMenuArray($navigationPage)
	{
		return $this->_getNavigationArray($navigationPage, self::CONFIG_MENU_NAME, $this->getSessionMenu());
	}

	/**
	 * Creates the header menu for each Page
	 * @param navigationPage GET Parameter witch holds the currently called Page
	 * @return array with the Menu Entries
	 */
	public function getHeaderArray($navigationPage)
	{
		return $this->_getNavigationArray($navigationPage, self::CONFIG_HEADER_NAME, $this->getSessionHeader());
	}

	/**
	 * Returns the structure for one level of the menu
	 */
	public function oneLevel(
		$description,
		$link = '#',
		$children = null,
		$icon = '',
		$expand = false,
		$subscriptDescription = null,
		$subscriptLinkClass = null,
		$subscriptLinkValue = null,
		$target = '',
		$sort = null,
		$requiredPermissions = null,
		$subscriptLinkHref = '#'
	)
	{
		return array(
			'description' => $description,
			'link' => $link,
			'target' => $target,
			'children'=> $children,
			'icon' => $icon,
			'expand' => $expand,
			'subscriptDescription' => $subscriptDescription,
			'subscriptLinkClass' => $subscriptLinkClass,
			'subscriptLinkValue' => $subscriptLinkValue,
			'sort' => $sort,
			'requiredPermissions' => $requiredPermissions,
			'subscriptLinkHref' => $subscriptLinkHref
		);
	}

	/**
	 * Wrapper method to the session helper funtions to retrieve the whole session for this navigation widget
	 */
	public function getSessionMenu()
	{
		$session = getSessionElement(self::SESSION_NAME, self::SESSION_MENU_NAME);

		if (isset($session[$this->_navigationPage]))
		{
			return $session[$this->_navigationPage];
		}

		return null;
	}

	/**
	 * Wrapper method to the session helper funtions to retrieve the whole session for this navigation widget
	 */
	public function getSessionHeader()
	{
		$session = getSessionElement(self::SESSION_NAME, self::SESSION_HEADER_NAME);

		if (isset($session[$this->_navigationPage]))
		{
			return $session[$this->_navigationPage];
		}

		return null;
	}

	/**
	 * Wrapper method to the session helper funtions to retrieve one element from the session of this navigation widget
	 */
	public function getSessionElementMenu($name)
	{
		$session = $this->getSessionMenu();

		if (isset($session[$name]))
		{
			return $session[$name];
		}

		return null;
	}

	/**
	 * Wrapper method to the session helper funtions to retrieve one element from the session of this navigation widget
	 */
	public function getSessionElementHeader($name)
	{
		$session = $this->getSessionHeader();

		if (isset($session[$name]))
		{
			return $session[$name];
		}

		return null;
	}

	/**
	 * Wrapper method to the session helper funtions to set the whole session for this navigation widget
	 */
	public function setSessionMenu($data)
	{
		setSessionElement(self::SESSION_NAME, self::SESSION_MENU_NAME, array($this->_navigationPage => $data));
	}

	/**
	 * Wrapper method to the session helper funtions to set the whole session for this navigation widget
	 */
	public function setSessionHeader($data)
	{
		setSessionElement(self::SESSION_NAME, self::SESSION_HEADER_NAME, array($this->_navigationPage => $data));
	}

	/**
	 * Wrapper method to the session helper funtions to set one element in the session for this navigation widget
	 */
	public function setSessionElementMenu($name, $value)
	{
		$session = $this->getSessionMenu();

		if ($session == null) $session = array();

		$session[$name] = $value;

		setSessionElement(self::SESSION_NAME, self::SESSION_MENU_NAME, array($this->_navigationPage => $session)); // stores the single value
	}

	/**
	 * Wrapper method to the session helper funtions to set one element in the session for this navigation widget
	 */
	public function setSessionElementHeader($name, $value)
	{
		$session = $this->getSessionHeader();

		if ($session == null) $session = array();

		$session[$name] = $value;

		setSessionElement(self::SESSION_NAME, self::SESSION_HEADER_NAME, array($this->_navigationPage => $session)); // stores the single value
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Build the array needed by the NavigationWidget to render the left menu or the header
	 * menu depending on the given parameters
	 * @param navigationPage GET Parameter witch holds the currently called Page
	 * @param configName the name of the navigation config entry
	 * @param sessionArray array present in the session that could contains other menu entries
	 * @return array with the Menu Entries
	 */
	private function _getNavigationArray($navigationPage, $configName, $sessionArray)
	{
		$navigationArray = array();

		if (isset($navigationPage)) // if the current page name is given
		{
			// Load Header Entries of Core
			$configArray = $this->_ci->config->item($configName);
			$navigationArray = $this->_wildcardsearch($configArray, $navigationPage);

			// Load Header Entries of Extensions
			$extensions = $this->_ci->extensionslib->getInstalledExtensions();
			if (hasData($extensions))
			{
				$extensionArray = array();

				foreach ($extensions->retval as $ext)
				{
					$filename = APPPATH.'config/'.ExtensionsLib::EXTENSIONS_DIR_NAME.'/'.$ext->name.'/'.self::CONFIG_NAVIGATION_FILENAME;
					if (file_exists($filename))
					{
						$config = array(); // default value

						include($filename);

						if (isset($config[$configName]) && is_array($config[$configName]))
						{
							$extensionArray = array_merge_recursive(
								$extensionArray,
								$this->_wildcardsearch($config[$configName], $navigationPage)
							);
						}
					}
				}

				$navigationArray = array_merge_recursive($navigationArray, $extensionArray);
			}

			// Load dynamic header entries from session
			if ($sessionArray != null && is_array($sessionArray))
			{
				$navigationArray = array_merge_recursive($navigationArray, $sessionArray);
			}
		}

		$this->_rmNotAllowedEntries($navigationArray); // remove not allowed menu entries

		$this->_sortNavigationArray($navigationArray); // sort menu entries

		return $navigationArray;
	}

	/**
	 * Searches a Menuentry. If there is no exact entry it searches for Wildcard Entries with a Star
	 * Example:
	 * Searching for /system/foo/index will Match the following Menuentries:
	 * 		/system/foo/index
	 *		/system/foo/*
	 *		/system/*
	 *		*
	 *
	 * @param $navigationArray Array to Search in.
	 * @param $navigationPage Navigation to search for.
	 * @return Navigation Array if found, empty array otherwise
	 */
	private function _wildcardsearch($navigationArray, $navigationPage)
	{
		// Sort Navigation to have them in correct order
		krsort($navigationArray);

		// 100% match found
		if (isset($navigationArray[$navigationPage]))
		{
			return $navigationArray[$navigationPage];
		}
		else
		{
			foreach ($navigationArray as $key => $row)
			{
				// Search for * Entries
				if (mb_strpos($key, '*') === 0 || mb_strpos($key, '*') === mb_strlen($key) - 1)
				{
					// Take * Entry if Matches
					$search = mb_substr($key, 0, -1);
					if ($search == '' || mb_strpos($navigationPage, $search) === 0)
					{
						return $row;
					}
				}
			}
		}

		return array();
	}

	/**
	 * Return an unique string that identify this navigation widget
	 * NOTE: The default value is the URI where the NavigationWidget is called
	 */
	private function _getNavigationPage($params)
	{
		if ($params != null
			&& is_array($params)
			&& isset($params[self::NAVIGATION_PAGE_PARAM])
			&& !isEmptyString($params[self::NAVIGATION_PAGE_PARAM]))
		{
			$navigationPage = $params[self::NAVIGATION_PAGE_PARAM];
		}
		else
		{
			// Gets the current page URI
			$navigationPage = $this->_ci->router->directory.$this->_ci->router->class.'/'.$this->_ci->router->method;
		}

		return $navigationPage;
	}

	/**
	 * Sorts using the sort element present in the array
	 */
	private function _sortNavigationArray(&$navigationArray)
	{
		uasort($navigationArray, function($a, $b) {

			// If the element sort is not present then the default value is 999
			$sortA = 999;
			if (isset($a['sort'])) $sortA = $a['sort'];

			// If the element sort is not present then the default value is 999
			$sortB = 999;
			if (isset($b['sort'])) $sortB = $b['sort'];

			return $sortA - $sortB; // < 0 => lt, == 0 => equal, > 0 => gt
		});

		// Sort also the children
		foreach ($navigationArray as $menuName => $singleMenu)
		{
			if (isset($singleMenu['children']) && !isEmptyArray($singleMenu['children']))
			{
				// NOTE: keep this way to give the element by reference, $singleMenu has a different reference!
				// 		otherwise the children will not be sorted
				$this->_sortNavigationArray($navigationArray[$menuName]['children']); // recursive call
			}
		}
	}

	/**
	 * Remove menu entries that the logged user is not allow to use
	 */
	private function _rmNotAllowedEntries(&$navigationArray)
	{
		$this->_ci->load->library('PermissionLib'); // Load permission library

		if (isset($navigationArray)) // to avoid error in the foreach
		{
			// Loops through the navigation array
			foreach ($navigationArray as $menuName => $singleMenu)
			{
				// If the property requiredPermissions is present is checked
				if (isset($singleMenu['requiredPermissions']))
				{
					// Checks if the logged uses has at least one of required permissions
					$isAllowed = $this->_ci->permissionlib->hasAtLeastOne(
						$singleMenu['requiredPermissions'],
						self::PERMISSION_NAVIGATION_METHOD
					);

					// If the user is not allowed then this menu entry and its children (sub menus) are removed and not displayed
					if (!$isAllowed)
					{
						unset($navigationArray[$menuName]);
					}
				}
				// Otherwise this menu entry is displayed

				// If the menu entry was NOT removed, then checks if it has children (sub menus) to check them for permissions
				// NOTE: used $navigationArray[$menuName] because could be removed by the previous unset command
				// 		therefore $singleMenu is still set
				if (isset($navigationArray[$menuName]) && isset($singleMenu['children']) && !isEmptyArray($singleMenu['children']))
				{
					// NOTE: keep this way to give the element by reference, $value has a different reference!
					// 		otherwise the children will not be checked correctly
					$this->_rmNotAllowedEntries($navigationArray[$menuName]['children']); // recursive call
				}
			}
		}
	}
}
