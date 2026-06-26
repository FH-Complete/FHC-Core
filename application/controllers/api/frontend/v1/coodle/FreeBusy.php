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

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class FreeBusy extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getFreeBusyTypes' => self::PERM_LOGGED,
			'getFreeBusyEntries' => self::PERM_LOGGED,
			'createFreeBusyEntry' => self::PERM_LOGGED,
			'updateFreeBusyEntry' => self::PERM_LOGGED,
			'deleteFreeBusyEntry' => self::PERM_LOGGED,
		]);

		$this->load->model('person/Freebusy_model', 'FreeBusyModel');
		$this->load->model('person/Freebusytyp_model', 'FreeBusyTypeModel');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getFreeBusyTypes()
	{
		$freeBusyTypesResult = $this->FreeBusyTypeModel->getAllFreeBusyTypes();
		$freeBusyTypes = $this->getDataOrTerminateWithError($freeBusyTypesResult);
		// todo: are we using all types? are we changing base urls? are we changing tables?
		// tentatively filtering out most types, leaving only sogo and other
		$freeBusyTypes = array_values(array_filter($freeBusyTypes, function ($freeBusyType) {
			return in_array($freeBusyType->freebusytyp_kurzbz, ["Sonstiges", "SoGo"]);
		}));
		$this->terminateWithSuccess($freeBusyTypes);

	}

	public function getFreeBusyEntries()
	{
		$this->FreeBusyModel->addOrder("freebusy_id");
		$freeBusyEntries = $this->FreeBusyModel->loadWhere(["uid" => getAuthUID()]);
		$freeBusyEntries = $this->getDataOrTerminateWithError($freeBusyEntries);
		$this->terminateWithSuccess($freeBusyEntries);
	}

	public function createFreeBusyEntry()
	{
		$description = $this->input->post("description");
		// todo: check if defaulting to other is fine
		$type = $this->input->post("type") ?? "Sonstiges";
		$url = $this->input->post("url");
		$isActive = $this->input->post("isActive");

		if (!$this->isValid($url)) {
			$this->terminateWithError("Could not open the provided URL!");
		}
		$this->addMeta("isValidUrl", $this->isValid($url));

		$this->FreeBusyModel->insert(
			[
				"uid" => getAuthUID(),
				"freebusytyp_kurzbz" => $type,
				"url" => $url,
				"aktiv" => $isActive,
				"bezeichnung" => $description,
			]
		);

		$this->terminateWithSuccess();
	}

	public function updateFreeBusyEntry()
	{
		$id = $this->input->post("id");
		$description = $this->input->post("description");
		// todo: check if defaulting to other is fine
		$type = $this->input->post("type") ?? "Sonstiges";
		$url = $this->input->post("url");
		$isActive = $this->input->post("isActive");

		if (!$this->isValid($url)) {
			$this->terminateWithError("Could not open the provided URL!");
		}

		$existingFreeBusyEntry = $this->FreeBusyModel->load($id)->retval[0];
		if (!$existingFreeBusyEntry) {
			$this->terminateWithError("FreeBusy entry not found!");
		} else if ($existingFreeBusyEntry->uid !== getAuthUID()) {
			$this->terminateWithError("You are not authorized to modify this FreeBusy entry!");
		}

		$this->FreeBusyModel->update(
			$id,
			[
				"uid" => getAuthUID(),
				"freebusytyp_kurzbz" => $type,
				"url" => $url,
				"aktiv" => $isActive,
				"bezeichnung" => $description,
			]
		);

		$this->terminateWithSuccess();
	}

	public function deleteFreeBusyEntry()
	{
		$id = $this->input->post("id");

		$existingFreeBusyEntry = $this->FreeBusyModel->load($id)->retval[0];
		if (!$existingFreeBusyEntry) {
			$this->terminateWithError("FreeBusy entry not found!");
		} else if ($existingFreeBusyEntry->uid !== getAuthUID()) {
			$this->terminateWithError("You are not authorized to modify this FreeBusy entry!");
		}

		$this->FreeBusyModel->delete($id);

		$this->terminateWithSuccess();
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

	private function isValid($url)
	{
		return !!@fopen($url, "r");
	}
}
