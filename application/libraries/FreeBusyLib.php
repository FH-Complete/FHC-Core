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

// require_once('../../include/ical.class.php');

class FreeBusyLib
{
	private $_ci; // Code igniter instance

	public function __construct()
	{
		$this->_ci =& get_instance();
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getFreeBusy($url)
	{
		$fileStream = @fopen($url, "r");
		if (!$fileStream)
			return [];

		$busyTimeslots = [];
		$fileContent = file_get_contents($url);
		if (mb_strpos($fileContent, "BEGIN:VFREEBUSY") !== false) {
			$busyTimeslots = $this->parseFreeBusyFormat($fileContent);
		} else {
			$busyTimeslots = $this->parseConventionalFormat($fileContent);
		}

		return $busyTimeslots;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	private function parseConventionalFormat($fileContent)
	{
		$fileContentRows = explode("\n", $fileContent);
		$fileContentRows = array_map(function ($row) {
			return trim($row);
		}, $fileContentRows);

		$busyTimeslots = [];
		$isParsingEvent = false;
		$parsedEvent = ["start" => null, "end" => null];
		$currentDateTime = new DateTime();
		$currentDateTime = $currentDateTime->format("Y-m-d H:i:s");

		foreach ($fileContentRows as $row) {
			if (mb_strpos($row, "BEGIN:VEVENT") !== false) {
				$isParsingEvent = true;
			} else if (mb_strpos($row, "END:VEVENT") !== false) {
				if (
					$isParsingEvent &&
					$parsedEvent["start"] &&
					$parsedEvent["end"] &&
					$parsedEvent["end"] > $currentDateTime
				) {
					$busyTimeslots[] = $parsedEvent;
				}

				$isParsingEvent = false;
				$parsedEvent["start"] = null;
				$parsedEvent["end"] = null;
			} else if (mb_strpos($row, "DTSTART;VALUE=DATE") !== false) {
				$startDateString = explode(":", $row)[1];
				$start = substr($startDateString, 0, 4) . "-" . substr($startDateString, 4, 2) . "-" . substr($startDateString, 6, 2) . " 00:00:00";
				$parsedEvent["start"] = $start;
			} else if (mb_strpos($row, "DTSTART") !== false) {
				$timestamp = explode(":", $row)[1];

				if (mb_substr($timestamp, -1) === "Z") {
					$timestamp = $this->convertTimestampToLocalTimezone($timestamp);
				} else if (mb_strpos($row, "TZID=") !== false) {
					$remainingString = explode("TZID=", $row)[1];
					$remainingString = explode(":", $remainingString)[0];
					$timezoneIdentifier = explode(";", $remainingString)[0];
					$timestamp = $this->convertTimestampToLocalTimezone($timestamp, $timezoneIdentifier);
				}

				$startDate = substr($timestamp, 0, 4) . "-" . substr($timestamp, 4, 2) . "-" . substr($timestamp, 6, 2);
				$startTime = substr($timestamp, 9, 2) . ":" . substr($timestamp, 11, 2) . ":" . substr($timestamp, 13, 2);
				$parsedEvent["start"] = $startDate . " " . $startTime;
			} else if (mb_strpos($row, "DTEND;VALUE=DATE") !== false) {
				$endDateString = explode(":", $row)[1];
				$end = substr($endDateString, 0, 4) . "-" . substr($endDateString, 4, 2) . "-" . substr($endDateString, 6, 2) . " 00:00:00";
				$parsedEvent["end"] = $end;
			} else if (mb_strpos($row, "DTEND") !== false) {
				$timestamp = explode(":", $row)[1];
				if (mb_substr($timestamp, -1) === "Z") {
					$timestamp = $this->convertTimestampToLocalTimezone($timestamp);
				} else if (mb_strpos($row, "TZID=") !== false) {
					$remainingString = explode("TZID=", $row)[1];
					$remainingString = explode(":", $remainingString)[0];
					$timezoneIdentifier = explode(";", $remainingString)[0];
					$timestamp = $this->convertTimestampToLocalTimezone($timestamp, $timezoneIdentifier);
				}

				$endDate = substr($timestamp, 0, 4) . "-" . substr($timestamp, 4, 2) . "-" . substr($timestamp, 6, 2);
				$endTime = substr($timestamp, 9, 2) . ":" . substr($timestamp, 11, 2) . ":" . substr($timestamp, 13, 2);
				$parsedEvent["end"] = $endDate . " " . $endTime;
			}
		}

		return $busyTimeslots;
	}

	private function parseFreeBusyFormat($fileContent)
	{
		$fileContentRows = explode("\n", $fileContent);
		$fileContentRows = array_map(function ($row) {
			return trim($row);
		}, $fileContentRows);

		$busyTimeslots = [];
		$currentDateTime = new DateTime();
		$currentDateTime = $currentDateTime->format("Y-m-d H:i:s");

		foreach ($fileContentRows as $row) {
			if (
				mb_strpos($row, "VFREEBUSY") !== false ||
				mb_strpos($row, "FREEBUSY") === false ||
				mb_strpos($row, "FBTYPE=FREE") !== false
			)
				continue;

			$timestamps = explode(":", $row)[1];
			$timestamps = explode(",", $timestamps);
			foreach ($timestamps as $timestamp) {
				$startEndTimestamps = explode("/", $timestamp);

				$startTimestamp = $startEndTimestamps[0];
				$endTimestamp = $startEndTimestamps[1];

				if (mb_substr($startTimestamp, -1) === "Z") {
					$startTimestamp = $this->convertTimestampToLocalTimezone($startTimestamp);
				}
				if (mb_substr($endTimestamp, -1) === "Z") {
					$endTimestamp = $this->convertTimestampToLocalTimezone($endTimestamp);
				}

				$startDate = substr($startTimestamp, 0, 4) . "-" . substr($startTimestamp, 4, 2) . "-" . substr($startTimestamp, 6, 2);
				$startTime = substr($startTimestamp, 9, 2) . ":" . substr($startTimestamp, 11, 2) . ":" . substr($startTimestamp, 13, 2);
				$start = $startDate . " " . $startTime;

				$endDate = substr($endTimestamp, 0, 4) . "-" . substr($endTimestamp, 4, 2) . "-" . substr($endTimestamp, 6, 2);
				$endTime = substr($endTimestamp, 9, 2) . ":" . substr($endTimestamp, 11, 2) . ":" . substr($endTimestamp, 13, 2);
				$end = $endDate . " " . $endTime;

				if ($end < $currentDateTime)
					continue;

				$busyTimeslots[] = ["start" => $start, "end" => $end];
			}
		}

		return $busyTimeslots;
	}

	private function convertTimestampToLocalTimezone($timestamp, $originalTimezoneIdentifier = "UTC")
	{
		$originalTimezone = new DateTimeZone($originalTimezoneIdentifier);
		$localTimezone = new DateTimeZone('Europe/Vienna');

		$date = new DateTime($timestamp, $originalTimezone);
		$date->setTimezone($localTimezone);
		return $date->format('Ymd\THis');
	}
}
