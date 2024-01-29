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
 * This controller operates between (interface) the JS (GUI) and the NavigationLib (back-end)
 * Provides data to the ajax get calls about the filter
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Navigation extends FHC_Controller
{
	const NAVIGATION_PAGE_PARAM = 'navigation_page'; // Navigation page parameter name

	/**
	 * Loads the NavigationLib where the used logic lies
	 */
	public function __construct()
    {
        parent::__construct();

		$this->load->library('AuthLib');

		$this->_loadNavigationLib(); // Loads the NavigationLib with parameters
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * This function creates the left Menu for each Page
	 * @param NAVIGATION_PAGE_PARAM GET Parameter witch holds the currently called Page
	 * @return JSON object with the Menu Entries
	 */
	public function menu()
	{
		$menuArray = $this->navigationlib->getMenuArray($this->input->get(self::NAVIGATION_PAGE_PARAM));

		$this->outputJsonSuccess($menuArray);
	}

	/**
	 * This function creates the Top Menu for each Page
	 * @param NAVIGATION_PAGE_PARAM GET Parameter witch holds the currently called Page
	 * @return JSON object with the Menu Entries
	 */
	public function header()
	{
		$headerArray = $this->navigationlib->getHeaderArray($this->input->get(self::NAVIGATION_PAGE_PARAM));

		$this->outputJsonSuccess($headerArray);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Loads the NavigationLib with the NAVIGATION_PAGE_PARAM parameter
	 * If the parameter NAVIGATION_PAGE_PARAM is not given then the execution of the controller is terminated and
	 * an error message is printed
	 */
	private function _loadNavigationLib()
	{
		// If the parameter NAVIGATION_PAGE_PARAM is present in the HTTP GET or POST
		if (isset($_GET[self::NAVIGATION_PAGE_PARAM]) || isset($_POST[self::NAVIGATION_PAGE_PARAM]))
		{
			// If it is present in the HTTP GET
			if (isset($_GET[self::NAVIGATION_PAGE_PARAM]))
			{
				$navigationPage = $this->input->get(self::NAVIGATION_PAGE_PARAM); // is retrieved from the HTTP GET
			}
			elseif (isset($_POST[self::NAVIGATION_PAGE_PARAM])) // Else if it is present in the HTTP POST
			{
				$navigationPage = $this->input->post(self::NAVIGATION_PAGE_PARAM); // is retrieved from the HTTP POST
			}

			// Loads the NavigationLib that contains all the used logic
			$this->load->library('NavigationLib', array(self::NAVIGATION_PAGE_PARAM => $navigationPage));
		}
		else // Otherwise an error will be written in the output
		{
			// NOTE: Used echo to speed up the output before the exit otherwise it's not shown
			echo 'Parameter "'.self::NAVIGATION_PAGE_PARAM.'" not provided!';
			exit;
		}
	}
}
