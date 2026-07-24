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
			'getCoodleFreeBusy' => self::PERM_ANONYMOUS,
		]);

		$this->load->model('person/Freebusy_model', 'FreeBusyModel');
		$this->load->model('person/Freebusytyp_model', 'FreeBusyTypeModel');

		$this->load->library('FreeBusyLib');
		$this->load->library('form_validation');

		$this->load->model("ressource/CoodleSurvey_model", "CoodleSurveyModel");
		$this->load->model("ressource/CoodleSurveyTimeslot_model", "CoodleSurveyTimeslotModel");
		$this->load->model("ressource/CoodleSurveyParticipant_model", "CoodleSurveyParticipantModel");

		$this->loadPhrases([
			'coodle'
		]);
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
			$this->terminateWithError($this->p->t("coodle", "url_not_working"));
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
		$this->form_validation->set_rules("url", "URL", "required|max_length[255]");
		$this->form_validation->set_rules("description", "Description", "max_length[255]");
		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		if (!$this->isUrlValid($url)) {
			$this->terminateWithError($this->p->t("coodle", "url_not_working"));
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
		$coodleSurveyIdToExclude = $this->input->post("coodleSurveyIdToExclude");
		$freeBusyEntries = $this->FreeBusyModel->loadWhere(["uid" => $uid, "aktiv" => true]);
		$freeBusyEntries = $this->getDataOrTerminateWithError($freeBusyEntries);

		$cumulativeFreeBusyEvents = $this->freebusylib->getDefaultInternalFreeBusy($uid);

		foreach ($freeBusyEntries as $freeBusyEntry) {
			// todo: following workaround is to use new coodle freebusy endpoint, remove once actual redirect is set up
			// keep query param with excluded survey id, only remove url replacement
			$freeBusyUrl = $freeBusyEntry->url;
			if ($freeBusyEntry->freebusytyp_kurzbz === "Coodle") {
				$freeBusyUrl = APP_ROOT . "cis.php/CoodleFreeBusy/" . $uid;
				if ($coodleSurveyIdToExclude) {
					$freeBusyUrl .= "?excludeSurvey=" . $coodleSurveyIdToExclude;
				}
			}

			$freeBusyEvents = $this->freebusylib->getFreeBusy($freeBusyUrl);
			$cumulativeFreeBusyEvents = array_merge(
				$cumulativeFreeBusyEvents,
				$freeBusyEvents
			);
		}

		$this->terminateWithSuccess($cumulativeFreeBusyEvents);
	}

	public function getCoodleFreeBusy($uid)
	{
		$participantEntries = $this->CoodleSurveyParticipantModel->getParticipantEntriesByUid($uid);

		$surveys = [];
		if (count($participantEntries)) {
			$surveyIds = array_map(
				function ($participantEntry) {
					return $participantEntry->survey_id;
				},
				$participantEntries
			);
			$surveys = $this->CoodleSurveyModel->getSurveys($surveyIds);
		}

		$surveyIdToBeExcluded = $this->input->get("excludeSurvey");
		if ($surveyIdToBeExcluded) {
			$surveyIdToBeExcluded = intval($surveyIdToBeExcluded);
			$surveys = array_filter(
				$surveys,
				function ($survey) use ($surveyIdToBeExcluded) {
					return $survey->id !== $surveyIdToBeExcluded;
				}
			);
		}


		$activeSurveys = array_filter(
			$surveys,
			function ($survey) {
				return !$survey->completed_at && !$survey->canceled_at;
			}
		);

		$timeslots = [];
		if (count($activeSurveys)) {
			$activeSurveyIds = array_map(
				function ($activeSurvey) {
					return $activeSurvey->id;
				},
				$activeSurveys
			);
			$timeslots = $this->CoodleSurveyTimeslotModel->getTimeslotsForMultipleSurveys($activeSurveyIds);
		}

		$formattedTimeslots = [];
		if (count($timeslots)) {
			$localTimezone = new DateTimeZone("Europe/Vienna");
			$utcTimezone = new DateTimeZone("UTC");

			foreach ($activeSurveys as $activeSurvey) {
				$correspondingTimeslots = array_filter(
					$timeslots,
					function ($timeslot) use ($activeSurvey) {
						return $timeslot->survey_id === $activeSurvey->id;
					}
				);
				$formattedCorrespondingTimeslots = array_map(
					function ($timeslot) use ($activeSurvey, $utcTimezone, $localTimezone) {
						$startTime = new DateTime($timeslot->starts_at, $localTimezone);
						$endTime = new DateTime($timeslot->starts_at, $localTimezone);
						$endTime->modify("+$activeSurvey->timeslot_duration minutes");

						$startTime->setTimezone($utcTimezone);
						$endTime->setTimezone($utcTimezone);

						$formattedStartTime = $startTime->format("Ymd\THis\Z");
						$formattedEndTime = $endTime->format("Ymd\THis\Z");
						return [
							"start" => $formattedStartTime,
							"end" => $formattedEndTime,
						];
					},
					$correspondingTimeslots
				);
				$formattedTimeslots = array_merge($formattedTimeslots, $formattedCorrespondingTimeslots);
			}
		}

		$coodleFreeBusyIcsFilePath = tempnam(sys_get_temp_dir(), "coodle_freebusy_");

		$coodleFreeBusyIcsFile = fopen($coodleFreeBusyIcsFilePath, "w");
		fwrite($coodleFreeBusyIcsFile, "BEGIN:VCALENDAR" . PHP_EOL);
		fwrite($coodleFreeBusyIcsFile, "VERSION:2.0" . PHP_EOL);
		fwrite($coodleFreeBusyIcsFile, "PRDODID:" . CAMPUS_NAME . PHP_EOL);
		fwrite($coodleFreeBusyIcsFile, "ATTENDEE:mailto:" . $uid . "@" . DOMAIN . PHP_EOL);
		fwrite($coodleFreeBusyIcsFile, "BEGIN:VFREEBUSY" . PHP_EOL);

		foreach ($formattedTimeslots as $timeslot) {
			fwrite($coodleFreeBusyIcsFile, "FREEBUSY;FBTYPE=BUSY:" . $timeslot["start"] . "/" . $timeslot["end"] . PHP_EOL);
		}

		fwrite($coodleFreeBusyIcsFile, "END:VFREEBUSY" . PHP_EOL);
		fwrite($coodleFreeBusyIcsFile, "END:VCALENDAR");

		$this->terminateWithFileOutput("text/calendar", file_get_contents($coodleFreeBusyIcsFilePath), "coodle_freebusy_$uid.ics");
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

}
