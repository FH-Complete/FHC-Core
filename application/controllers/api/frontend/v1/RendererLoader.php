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

class RendererLoader extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{

		parent::__construct([
			'GetRenderers' => self::PERM_LOGGED,
           
		]);

        $this->load->library('LogLib');
        $this->loglib->setConfigs(array(
			'classIndex' => 5,
			'functionIndex' => 5,
			'lineIndex' => 4,
			'dbLogType' => 'API', // required
			'dbExecuteUser' => 'RESTful API'
		));


	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	 /**
     * fetches Stundenplan and Moodle events together
     * @access public
     *
     */
	public function GetRenderers(){
		$renderer_paths = [];
		Events::trigger(
				'loadRenderers',
				function & () use (&$renderer_paths)
				{
					return $renderer_paths;
				}
			);
		$this->terminateWithSuccess($renderer_paths);
	}

	
	

}
