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
			'getFreeBusySchedule' => self::PERM_LOGGED,
		]);

		$this->load->model('person/Freebusy_model', 'FreeBusyModel');
		$this->load->model('person/Freebusytyp_model', 'FreeBusyTypeModel');

		$this->load->library('FreeBusyLib');
		$this->load->library('form_validation');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getFreeBusyTypes()
	{
		$freeBusyTypesResult = $this->FreeBusyTypeModel->getActiveFreeBusyTypes();
		$freeBusyTypes = $this->getDataOrTerminateWithError($freeBusyTypesResult);
		$freeBusyTypes = $this->formatDefaultUrlsInFreeBusyTypes($freeBusyTypes);
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
		$type = $this->input->post("type") ?? "Sonstiges";
		$url = $this->input->post("url");
		$isActive = $this->input->post("isActive");

		$this->form_validation->set_data([
			"url" => $url,
			"description" => $description,
		]);
		$this->form_validation->set_rules("url", "URL", "required|max_length[255]");
		$this->form_validation->set_rules("description", "Description", "max_length[255]");
		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		if (!$this->isUrlValid($url)) {
			$this->terminateWithError("Could not open the provided URL!");
		}

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
		$type = $this->input->post("type") ?? "Sonstiges";
		$url = $this->input->post("url");
		$isActive = $this->input->post("isActive");

		$this->form_validation->set_data([
			"url" => $url,
			"description" => $description,
		]);
		$this->form_validation->set_rules("url", "URL", "required|string|max_length[255]");
		$this->form_validation->set_rules("description", "Description", "string|max_length[255]");
		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		if (!$this->isUrlValid($url)) {
			$this->terminateWithError("Could not open the provided URL!");
		}

		if (!$this->isUrlValid($url)) {
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
			$this->terminateWithError("You are not authorized to delete this FreeBusy entry!");
		}

		$this->FreeBusyModel->delete($id);

		$this->terminateWithSuccess();
	}

	public function getFreeBusySchedule()
	{
		$uid = $this->input->post("uid");
		$freeBusyEntries = $this->FreeBusyModel->loadWhere(["uid" => $uid, "aktiv" => true]);
		$freeBusyEntries = $this->getDataOrTerminateWithError($freeBusyEntries);
		foreach ($freeBusyEntries as $freeBusyEntry) {
			// 
		}
		$this->addMeta("freebusy", $freeBusyEntries);
		// todo

		$this->terminateWithSuccess($freeBusyEntries);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

	private function isUrlValid($url)
	{
		return !!@fopen($url, "r");
	}

	private function formatDefaultUrlsInFreeBusyTypes($freeBusyTypes)
	{
		$uid = getAuthUID();
		return array_map(function ($freeBusyType) use ($uid) {
			if (!$freeBusyType->url_vorlage || !strlen($freeBusyType->url_vorlage)) {
				$freeBusyType->url_default = null;
				unset($freeBusyType->url_vorlage);
				return $freeBusyType;
			}

			$freeBusyType->url_default = str_replace('$uid', $uid, $freeBusyType->url_vorlage);
			unset($freeBusyType->url_vorlage);
			return $freeBusyType;
		}, $freeBusyTypes);
	}

	// todo: remove
	private function _getFreeBusySchedule($uid)
	{
		$fp = fopen(APP_ROOT . 'cis/public/freebusy.php/' . $uid, 'r');
		if (!$fp) {
			//Load Failed
		} else {
			$doc = '';
			while (!feof($fp)) {
				$line = fgets($fp);
				$doc .= $line;
			}
			fclose($fp);

			//FreeBusy Parsen
			$ical = new ical();
			$ical->parseFreeBusy($doc);

			$events = [];
			foreach ($ical->dtresult as $row) {
				$item['id'] = $uid . $row['dtstart'] . $row['dtend'];
				$item['title'] = $uid;
				$item['start'] = fixDate($row['dtstart']);
				$item['end'] = fixDate($row['dtend']);
				$item['allDay'] = false;
				$item['editable'] = false;
				$events[] = $item;
			}
			return $events;
		}
	}
}
