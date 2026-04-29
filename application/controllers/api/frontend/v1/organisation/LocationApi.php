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
class LocationApi extends FHCAPI_Controller
{
	
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getLocationsByCompanyType'=> self::PERM_LOGGED,
		]);

		$this->load->library('form_validation');

		$this->load->model('organisation/standort_model', 'StandortModel');

		$this->loadPhrases([
			'global',
			'ui',
		]);

	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods
    public function getLocationsByCompanyType() {
		$companyType = $this->input->get('companyType');
		if (!isset($companyType)) {
			$this->terminateWithError('companyType parameter is required', REST_Controller::HTTP_BAD_REQUEST);
			return;
		}

		$result = $this->StandortModel->getByCompanyType($companyType);
		
		return $this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}
}