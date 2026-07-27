<?php
class CoodleSurveyExternalParticipant_model extends DB_Model
{

	private $_ci; // Code igniter instance

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_coodle_survey_external_participants';
		$this->pk = 'id';
		
		$this->_ci =& get_instance();
	}

	public function getExternalParticipants($surveyId)
	{
		$query = "SELECT *
				FROM $this->dbTable
				WHERE survey_id = $surveyId
		";
		return $this->execQuery($query)->retval;
	}

	public function getExternalParticipant($externalParticipantId)
	{
		$query = "SELECT *
			FROM $this->dbTable
			WHERE id = $externalParticipantId
			LIMIT 1
		";
		return $this->execQuery($query)->retval[0];
	}

	public function updateExternalParticipants($surveyId, $externalParticipants, $timeslots)
	{
		$oldExternalParticipants = $this->getExternalParticipants($surveyId);
		$oldExternalParticipantIds = array_map(
			function ($oldExternalParticipant) {
				return $oldExternalParticipant->id;
			},
			$oldExternalParticipants
		);

		$persistingExternalParticipants = array_filter(
			$externalParticipants,
			function ($externalParticipant) {
				return isset($externalParticipant["id"]) && $externalParticipant["id"];
			}
		);
		$persistingExternalParticipantIds = array_map(
			function ($persistingExternalParticipant) {
				return $persistingExternalParticipant["id"];
			},
			$persistingExternalParticipants
		);

		$redundantExternalParticipantIds = array_diff(
			$oldExternalParticipantIds,
			$persistingExternalParticipantIds
		);

		$newExternalParticipants = array_filter(
			$externalParticipants,
			function ($externalParticipant) {
				return !isset($externalParticipant["id"]) || !$externalParticipant["id"];
			}
		);


		if (count($redundantExternalParticipantIds)) {
			$idsToBeDeleted = "(" . implode(", ", $redundantExternalParticipantIds) . ")";
			$deleteQuery = "DELETE FROM $this->dbTable WHERE id IN $idsToBeDeleted";
			$this->execQuery($deleteQuery);
		}

		if (count($newExternalParticipants)) {
			$insertValues = array_map(
				function ($newExternalParticipant) use ($surveyId) {
					$name = $newExternalParticipant["name"];
					$email = $newExternalParticipant["email"];
					return "($surveyId, '$name', '$email', NULL)";
				},
				$newExternalParticipants
			);
			$insertValues = implode(", ", $insertValues);
			$insertQuery = "INSERT INTO $this->dbTable (survey_id, name, email, selection) VALUES $insertValues";
			$this->execQuery($insertQuery);
		}


		$updatedExternalParticipants = $this->getExternalParticipants($surveyId);
		$timeslotIds = array_map(
			function ($timeslot) {
				return $timeslot->id;
			},
			$timeslots
		);

		foreach ($updatedExternalParticipants as $externalParticipant) {
			if (!$externalParticipant->selection)
				continue;

			$selection = json_decode($externalParticipant->selection, true);
			if (!count($selection))
				continue;

			$obsoleteSelectedTimeslotIds = array_diff($selection, $timeslotIds);
			if (!count($obsoleteSelectedTimeslotIds))
				continue;

			$updatedSelection = null;
			if (count($obsoleteSelectedTimeslotIds) !== count($selection)) {
				$updatedSelection = array_filter(
					$selection,
					function ($timeslotId) use ($obsoleteSelectedTimeslotIds) {
						return !in_array($timeslotId, $obsoleteSelectedTimeslotIds);
					}
				);
			}

			$this->updateSelection($externalParticipant->id, $updatedSelection);
		}
	}

	public function updateSelection($externalParticipantId, $selection)
	{
		$selection = $selection ? "'" . json_encode($selection) . "'" : "NULL";
		$selectionUpdateQuery = "UPDATE $this->dbTable SET selection = $selection WHERE id = $externalParticipantId";
		$this->execQuery($selectionUpdateQuery);
	}
}
