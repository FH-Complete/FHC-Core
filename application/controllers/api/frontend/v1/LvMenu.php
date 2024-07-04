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


use CI3_Events as Events;

/**
 * This controller operates between (interface) the JS (GUI) and the SearchBarLib (back-end)
 * Provides data to the ajax get calls about the searchbar component
 * This controller works with JSON calls on the HTTP GET and the output is always JSON
 */
class LvMenu extends FHCAPI_Controller
{
    

	/**
	 * Object initialization
	 */
	public function __construct()
	{
        
		// NOTE(chris): additional permission checks will be done in SearchBarLib
		parent::__construct([
			'getLvMenu' => self::PERM_LOGGED
		]);

		
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Gets a JSON body via HTTP POST and provides the parameters
	 */
	public function getLvMenu($lvid, $studiensemester_kurzbz)
	{
		
		if(!isset($lvid) || !isset($studiensemester_kurzbz))
			$this->terminateWithError('Missing parameters', self::ERROR_TYPE_GENERAL);

		$menu = array();

        // get the menu of the fhc core, look menu_lv.class.php
		//todo

		require_once(FHCPATH.'config/cis.config.inc.php');
		require_once(FHCPATH.'include/lehrveranstaltung.class.php');
		require_once(FHCPATH.'include/studiensemester.class.php');
		require_once(FHCPATH.'include/lehreinheit.class.php');
		require_once(FHCPATH.'include/vertrag.class.php');
		require_once(FHCPATH.'include/functions.inc.php');
		require_once(FHCPATH.'include/benutzerberechtigung.class.php');
		require_once(FHCPATH.'include/studiengang.class.php');
		require_once(FHCPATH.'include/phrasen.class.php');

        Events::trigger('lvMenuBuild', 
						// callback function for the onEvents to add newValues to the $menu
						function ($newValue) use (&$menu) {
							$menu[]= $newValue;
						},
						$lvid,
						$studiensemester_kurzbz
		);

		$this->terminateWithSuccess($menu);

	}
}

