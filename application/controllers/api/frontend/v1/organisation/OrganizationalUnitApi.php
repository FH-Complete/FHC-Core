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
class OrganizationalUnitApi extends FHCAPI_Controller
{
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getAllOrganizationalUnits'=> array('basis/organisationseinheit:r'),
		]);

		$this->load->library('form_validation');

		$this->load->model('education/ClassTimeSlotValidityPeriod_model', "ClassTimeSlotValidityPeriodModel");

		// Loads phrases system
		$this->loadPhrases([
			'global'
		]);

	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getAllOrganizationalUnits()
	{
		$this->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
		$result = $this->OrganisationseinheitModel->load();
		$organization_units_result = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($organization_units_result);
	}
}
