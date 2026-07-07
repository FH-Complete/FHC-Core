<?php
class CoodleSurveyParticipant_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_coodle_survey_participants';
	}

	public function getParticipants($surveyId)
	{
		$query = "
			SELECT
				surveyParticipant.participant_uid as uid,
				surveyParticipant.selection as selection,
				person.vorname || ' ' || person.nachname AS name
				FROM $this->dbTable surveyParticipant
				JOIN public.tbl_benutzer benutzer ON(benutzer.uid = surveyParticipant.participant_uid)
				JOIN public.tbl_person person ON(person.person_id = benutzer.person_id)
				WHERE survey_id = $surveyId
		";
		return $this->execQuery($query)->retval;
	}

	public function updateParticipants($surveyId, $participants, $timeslots)
	{
		$participantUids = array_map(function ($participant) {
			return $participant["uid"];
		}, $participants);

		$existingParticipantsQuery = "SELECT * FROM " . $this->dbTable . " WHERE survey_id = " . $surveyId;
		$existingParticipants = $this->execQuery($existingParticipantsQuery)->retval;
		$existingParticipantUids = array_map(
			function ($existingParticipant) {
				return $existingParticipant->participant_uid;
			},
			$existingParticipants
		);

		$newParticipantUids = array_filter(
			$participantUids,
			function ($participantUid) use ($existingParticipantUids) {
				return !in_array($participantUid, $existingParticipantUids);
			}
		);
		$redundantParticipantUids = array_filter(
			$existingParticipantUids,
			function ($existingParticipantUid) use ($participantUids) {
				return !in_array($existingParticipantUid, $participantUids);
			}
		);

		if (count($newParticipantUids)) {
			$insertQuery = "INSERT INTO $this->dbTable VALUES ";
			$newParticipantInsertValues = array_map(
				function ($newParticipantUid) use ($surveyId) {
					return "('$surveyId', '$newParticipantUid', NULL)";
				},
				$newParticipantUids
			);
			$newParticipantInsertValues = implode(", ", $newParticipantInsertValues);
			$insertQuery .= $newParticipantInsertValues;
			$insertQuery .= ";";

			$this->execQuery($insertQuery);
		}

		if (count($redundantParticipantUids)) {
			$deleteQuery = "DELETE FROM $this->dbTable WHERE survey_id = $surveyId AND participant_uid IN ('" . implode("', '", $redundantParticipantUids) . "');";
			$this->execQuery($deleteQuery);
		}

		$timeslotIds = array_map(
			function ($timeslot) {
				return $timeslot->id;
			},
			$timeslots
		);

		$updatedExistingParticipants = $this->execQuery($existingParticipantsQuery)->retval;
		foreach ($updatedExistingParticipants as $participant) {
			if (!$participant->selection)
				continue;

			$selection = json_decode($participant->selection, true);
			$obsoleteSelectedTimeslotIds = array_diff($selection, $timeslotIds);
			if (!count($obsoleteSelectedTimeslotIds))
				continue;

			$updatedSelectionValue = "NULL";
			if (count($obsoleteSelectedTimeslotIds) !== count($selection)) {
				$updatedSelection = array_filter(
					$selection,
					function ($timeslotId) use ($obsoleteSelectedTimeslotIds) {
						return !in_array($timeslotId, $obsoleteSelectedTimeslotIds);
					}
				);
				$updatedSelectionValue = "'" . json_encode($updatedSelection) . "'";
			}

			$updateQuery = "UPDATE $this->dbTable SET selection = $updatedSelectionValue WHERE survey_id = $surveyId AND participant_uid = $participant->uid";
			$this->execQuery($updateQuery);
		}
	}
}
