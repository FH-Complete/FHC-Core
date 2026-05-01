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


class CisMenu extends FHCAPI_Controller
{
	
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getMenu' => self::PERM_LOGGED,
		]);

		

	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

    /**
	 * fetches the menu for CIS from the database based on the userLanguage
	 */
    public function getMenu()
	{
		$this->load->model('content/Content_model', 'ContentModel');
		$this->load->config('cis');
		$cis4_content_id =$this->config->item('cis_menu_root_content_id');
		$result = $this->ContentModel->getMenu($cis4_content_id, getAuthUID(),getUserLanguage());
		$result = $this->getDataOrTerminateWithError($result);
		$menu = $result->childs ?? [];
		$this->terminateWithSuccess($menu);
    }

	
	
}

