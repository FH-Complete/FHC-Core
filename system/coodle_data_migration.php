<?php
/* Copyright (C) 2026 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *
 * Beschreibung:
 * Dieses Skript prueft die gesamte Systemumgebung und sollte nach jedem Update gestartet werden.
 * Geprueft wird: die Datenbank per "dbupdate_VERSION.php" auf aktualitaet, dabei werden fehlende Attribute angelegt.
 */
require_once('../config/system.config.inc.php');
require_once('../include/basis_db.class.php');

$db = new basis_db();

// check if new coodle table is already populated
$newSurveysCountQuery = "SELECT COUNT (*) FROM campus.tbl_coodle_surveys";
$newSurveysCountResult = $db->db_query($newSurveysCountQuery);
$newSurveysCount = $db->db_fetch_object($newSurveysCountResult)->count;
if ($newSurveysCount > 0) {
	exit;
}

// make sure id autoincrement is set to 1
// $autoIncrementResetQuery = "ALTER TABLE campus.tbl_coodle_surveys AUTO_INCREMENT = 1";
// $autoIncrementResetQuery = "ALTER SEQUENCE campus.seq_tbl_coodle_surveys_id RESTART WITH 1";
// $db->db_query($autoIncrementResetQuery);

// todo: include all surveys once code tested
// getting old surveys...
$surveysQuery = "SELECT * FROM campus.tbl_coodle WHERE coodle_id < 101";
$surveysResult = $db->db_query($surveysQuery);
while ($survey = $db->db_fetch_object($surveysResult)) {
	// getting old timeslots before importing survey, as timeslot count affects max_selections value
	$timeslotsData = [];
	$selectedOldTimeslotId = null;
	$timeslotsQuery = "SELECT * FROM campus.tbl_coodle_termin WHERE coodle_id = $survey->coodle_id";
	$timeslotsResult = $db->db_query($timeslotsQuery);
	while ($timeslot = $db->db_fetch_object($timeslotsResult)) {
		$timeslotsData[] = [
			"oldTimeslotId" => $timeslot->coodle_termin_id,
			"startTime" => $timeslot->datum . " " . $timeslot->uhrzeit,
		];
		if ($timeslot->auswahl) {
			$selectedOldTimeslotId = $timeslot->coodle_termin_id;
		}
	}

	// importing survey...
	$formattedDescription = formatDescription($survey->beschreibung ?? "");
	$timeslotDuration = $survey->dauer ? $survey->dauer : 15;
	if ($timeslotDuration % 5 !== 0) {
		$timeslotDuration = $timeslotDuration - ($timeslotDuration % 5);
	}
	$areParticipantsAnonymized = $survey->teilnehmer_anonym ? "true" : "false";
	$areSelectionsAnonymized = $survey->termine_anonym ? "true" : "false";
	$maxSelections = count($timeslotsData) ? count($timeslotsData) : 1;
	$completedAt = $survey->coodle_status_kurzbz === "abgeschlossen" ? "'$survey->updateamum'" : "NULL";
	$canceledAt = $survey->coodle_status_kurzbz === "storniert" ? "'$survey->updateamum'" : "NULL";
	$surveyInsertValues = [
		"id" => $survey->coodle_id,
		"creator_uid" => "'$survey->ersteller_uid'",
		"title" => "'$survey->titel'",
		"description" => "'$formattedDescription'",
		"timeslot_duration" => $timeslotDuration,
		"are_selections_anonymized" => $areSelectionsAnonymized,
		"are_participants_anonymized" => $areParticipantsAnonymized,
		"max_selections" => $maxSelections,
		"selected_timeslot_id" => "NULL",
		"ends_at" => "'$survey->endedatum'",
		"completed_at" => $completedAt,
		"canceled_at" => $canceledAt,
		"created_at" => "'$survey->insertamum'",
		"updated_at" => "'$survey->updateamum'",
	];
	$columnsString = "(" . implode(", ", array_keys($surveyInsertValues)) . ")";
	$valuesString = "(" . implode(", ", array_values($surveyInsertValues)) . ")";
	$surveyInsertQuery = "INSERT INTO campus.tbl_coodle_surveys $columnsString
		VALUES $valuesString";
	$db->db_query($surveyInsertQuery);

	// importing timeslots...
	$timeslotIds = [];
	if (count($timeslotsData)) {
		$timeslotInsertValues = array_map(
			function ($timeslot) use ($survey) {
				return "(" . $survey->coodle_id . ", '" . $timeslot["startTime"] . "')";
			},
			$timeslotsData
		);
		$timeslotInsertValues = implode(", ", $timeslotInsertValues);
		$timeslotsInsertQuery = "INSERT INTO campus.tbl_coodle_survey_timeslots (survey_id, starts_at) VALUES $timeslotInsertValues";
		$db->db_query($timeslotsInsertQuery);

		$newTimeslotsQuery = "SELECT * FROM campus.tbl_coodle_survey_timeslots WHERE survey_id = $survey->coodle_id";
		$newTimeslotsResult = $db->db_query($newTimeslotsQuery);
		while ($newTimeslot = $db->db_fetch_object($newTimeslotsResult)) {
			$correspondingOldTimeslotArray = array_values(array_filter(
				$timeslotsData,
				function ($oldTimeslot) use ($newTimeslot) {
					return $oldTimeslot["startTime"] === $newTimeslot->starts_at;
				}
			));
			$correspondingOldTimeslot = count($correspondingOldTimeslotArray) ? $correspondingOldTimeslotArray[0] : null;
			$timeslotIds[] = [
				"oldTimeslotId" => isset($correspondingOldTimeslot["oldTimeslotId"]) ? $correspondingOldTimeslot["oldTimeslotId"] : null,
				"newTimeslotId" => $newTimeslot->id,
			];
		}
	}

	// if there is a selected timeslot, getting its new id and updating survey
	if ($selectedOldTimeslotId && count($timeslotIds)) {
		$selectedTimeslotIdArray = array_filter(
			$timeslotIds,
			function ($timeslot) use ($selectedOldTimeslotId) {
				return $timeslot["oldTimeslotId"] === $selectedOldTimeslotId;
			}
		);
		$selectedTimeslotId = count($selectedTimeslotIdArray) ? $selectedTimeslotIdArray[0] : null;
		$selectedTimeslotNewId = isset($selectedTimeslotId["newTimeslotId"]) ? $selectedTimeslotId["newTimeslotId"] : null;
		if ($selectedTimeslotNewId) {
			$surveyUpdateQuery = "UPDATE campus.tbl_coodle_surveys
				SET selected_timeslot_id = $selectedTimeslotNewId
				WHERE id = $survey->coodle_id";
			$db->db_query($surveyUpdateQuery);
		}
	}

	// getting old timeslot votes...
	$votes = [];
	$oldTimeslotIds = array_map(
		function ($timeslotId) {
			return $timeslotId["oldTimeslotId"];
		},
		$timeslotIds
	);
	$oldTimeslotIds = array_filter(
		$oldTimeslotIds,
		function ($oldTimeslotId) {
			return !!$oldTimeslotId;
		}
	);
	if (count($oldTimeslotIds)) {
		$oldTimeslotIdsString = "(" . implode(", ", $oldTimeslotIds) . ")";
		$resourceTimeslotQuery = "SELECT * FROM campus.tbl_coodle_ressource_termin WHERE coodle_termin_id IN $oldTimeslotIdsString";
		$resourceTimeslotResult = $db->db_query($resourceTimeslotQuery);
		while ($resourceTimeslot = $db->db_fetch_object($resourceTimeslotResult)) {
			$timeslotIdArray = array_values(array_filter($timeslotIds, function ($timeslotId) use ($resourceTimeslot) {
				return $timeslotId["oldTimeslotId"] === $resourceTimeslot->coodle_termin_id;
			}));
			$timeslotId = count($timeslotIdArray) ? $timeslotIdArray[0] : null;
			if ($timeslotId) {
				$votes[] = [
					"oldResourceId" => $resourceTimeslot->coodle_ressource_id,
					"newTimeslotId" => $timeslotId["newTimeslotId"],
				];
			}
		}
	}

	// getting participant resources...
	$participantsData = [];
	$externalParticipantsData = [];
	$resourcesQuery = "SELECT * FROM campus.tbl_coodle_ressource WHERE coodle_id = $survey->coodle_id";
	$resourcesResult = $db->db_query($resourcesQuery);
	while ($resource = $db->db_fetch_object($resourcesResult)) {
		if ($resource->uid) {
			$participantData = [
				"uid" => $resource->uid,
				"selection" => null,
			];

			$participantVotes = array_filter(
				$votes,
				function ($vote) use ($resource) {
					return $vote["oldResourceId"] === $resource->coodle_ressource_id;
				}
			);
			if (count($participantVotes)) {
				$participantData["selection"] = array_map(
					function ($vote) {
						return $vote["newTimeslotId"];
					},
					$participantVotes
				);
			}

			$participantsData[] = $participantData;
		} else if ($resource->name && $resource->email) {
			$externalParticipantData = [
				"name" => $resource->name,
				"email" => $resource->email,
				"accessKey" => $resource->zugangscode,
				"selection" => null,
			];

			$externalParticipantVotes = array_filter(
				$votes,
				function ($vote) use ($resource) {
					return $vote["oldResourceId"] === $resource->coodle_ressource_id;
				}
			);
			if (count($externalParticipantVotes)) {
				$externalParticipantData["selection"] = array_map(
					function ($vote) {
						return $vote["newTimeslotId"];
					},
					$externalParticipantVotes
				);
			}

			$externalParticipantsData[] = $externalParticipantData;
		}
	}

	if (count($participantsData)) {
		$participantInsertValues = array_map(
			function ($participantData) use ($survey) {
				$formattedSelectionString = $participantData["selection"] !== null ? ("'" . json_encode($participantData["selection"]) . "'") : "NULL";
				$participantUid = $participantData["uid"];
				return "($survey->coodle_id, '$participantUid', $formattedSelectionString)";
			},
			$participantsData
		);
		$participantInsertValues = implode(", ", $participantInsertValues);
		$participantInsertQuery = "INSERT INTO campus.tbl_coodle_survey_participants (survey_id, participant_uid, selection) VALUES $participantInsertValues";
		$db->db_query($participantInsertQuery);
	}

	if (count($externalParticipantsData)) {
		$externalParticipantInsertValues = array_map(
			function ($externalParticipantData) use ($survey) {
				$formattedSelectionString = $externalParticipantData["selection"] !== null ? ("'" . json_encode($externalParticipantData["selection"]) . "'") : "NULL";
				$externalParticipantName = $externalParticipantData["name"];
				$externalParticipantEmail = $externalParticipantData["email"];
				$externalParticipantAccessKey = $externalParticipantData["accessKey"];
				return "($survey->coodle_id, '$externalParticipantName', '$externalParticipantEmail', '$externalParticipantAccessKey', $formattedSelectionString)";
			},
			$externalParticipantsData
		);
		$externalParticipantInsertValues = implode(", ", $participantInsertValues);
		$externalParticipantInsertQuery = "INSERT INTO campus.tbl_coodle_survey_external_participants (survey_id, name, email, access_key, selection) VALUES $externalParticipantInsertValues";
		$db->db_query($externalParticipantInsertQuery);
	}

	echo (json_encode($timeslotsData));
	echo ("<br>");
	echo (json_encode($participantsData));
	echo ("<br>");
	echo (json_encode($externalParticipantsData));
	echo ("<br>");
	echo ("-----");
	echo ("<br>");
}

function formatDescription($oldDescription)
{
	$descriptionWithNewlines = preg_replace("/<br ?\/?>/i", "\n", $oldDescription);
	$descriptionWithoutHtmlTags = preg_replace("/<.*>/i", "", $descriptionWithNewlines);
	return trim($descriptionWithoutHtmlTags);
}

?>