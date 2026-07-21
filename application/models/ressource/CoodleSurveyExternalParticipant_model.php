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

	public function getExternalParticipants($surveyId, $shouldIncludeAccessKey = false)
	{
		$columns = "id, name, email, selection";
		if ($shouldIncludeAccessKey) {
			$columns .= ", access_key";
		}

		$query = "SELECT $columns
				FROM $this->dbTable
				WHERE survey_id = $surveyId
		";
		return $this->execQuery($query)->retval;
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
		$this->_ci->addMeta("oldExternalParticipants", $oldExternalParticipants);
		$this->_ci->addMeta("oldExternalParticipantIds", $oldExternalParticipantIds);

		$persistingExternalParticipants = array_filter(
			$externalParticipants,
			function ($externalParticipant) {
				return isset($externalParticipant->id) && $externalParticipant->id;
			}
		);
		$persistingExternalParticipantIds = array_map(
			function ($persistingExternalParticipant) {
				return $persistingExternalParticipant->id;
			},
			$persistingExternalParticipants
		);
		$this->_ci->addMeta("persistingExternalParticipants", $persistingExternalParticipants);
		$this->_ci->addMeta("persistingExternalParticipantIds", $persistingExternalParticipantIds);

		$redundantExternalParticipantIds = array_diff(
			$oldExternalParticipantIds,
			$persistingExternalParticipantIds
		);
		$this->_ci->addMeta("redundantExternalParticipantIds", $redundantExternalParticipantIds);

		$newExternalParticipants = array_filter(
			$externalParticipants,
			function ($externalParticipant) {
				return !isset($externalParticipant->id) || !$externalParticipant->id;
			}
		);
		$this->_ci->addMeta("newExternalParticipants", $newExternalParticipants);


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
					$accessKey = $surveyId . "_" . $email;
					// todo: encrypt access key
					return "($surveyId, '$name', '$email', '$accessKey', NULL)";
				},
				$newExternalParticipants
			);
			$insertValues = implode(", ", $insertValues);
			$insertQuery = "INSERT INTO $this->dbTable (survey_id, name, email, access_key, selection) VALUES $insertValues";
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
