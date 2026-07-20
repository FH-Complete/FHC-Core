<?php
class CoodleSurveyTimeslot_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_coodle_survey_timeslots';
		$this->pk = 'id';
	}

	public function getTimeslots($surveyId)
	{
		$query = "SELECT id, starts_at
			FROM $this->dbTable
			WHERE survey_id = $surveyId
		";
		return $this->execQuery($query)->retval;
	}

	public function getTimeslotsForMultipleSurveys($surveyIds)
	{
		if (!count($surveyIds)) return [];

		$surveyIds = "(" . implode(", ", $surveyIds) . ")";
		$query = "SELECT *
			FROM $this->dbTable
			WHERE survey_id IN $surveyIds
		";
		return $this->execQuery($query)->retval;
	}

	public function getTimeslot($timeslotId)
	{
		$query = "SELECT *
			FROM $this->dbTable
			WHERE id = $timeslotId
			LIMIT 1
		";
		return $this->execQuery($query)->retval[0];
	}

	public function updateTimeslots($surveyId, $timeslots)
	{
		$newTimeslots = array_filter(
			$timeslots,
			function ($timeslot) {
				return !$timeslot["id"];
			}
		);
		$persistedTimeslots = array_filter(
			$timeslots,
			function ($timeslot) {
				return $timeslot["id"];
			}
		);
		$persistedTimeslotIds = array_map(
			function ($persistedTimeslot) {
				return $persistedTimeslot["id"];
			},
			$persistedTimeslots
		);

		$existingTimeslotsQuery = "SELECT id, starts_at
			FROM $this->dbTable
			WHERE survey_id = $surveyId
		";
		$existingTimeslots = $this->execQuery($existingTimeslotsQuery)->retval;
		$existingTimeslotIds = array_map(
			function ($existingTimeslot) {
				return $existingTimeslot->id;
			},
			$existingTimeslots
		);

		$obsoleteTimeslotIds = array_diff($existingTimeslotIds, $persistedTimeslotIds);
		if (count($obsoleteTimeslotIds)) {
			$deleteQuery = "DELETE FROM $this->dbTable WHERE id IN ('" . implode("', '", $obsoleteTimeslotIds) . "');";
			$this->execQuery($deleteQuery);
		}

		if (count($newTimeslots)) {
			$insertQuery = "INSERT INTO $this->dbTable (survey_id, starts_at) VALUES ";
			$newTimeslotInsertValues = array_map(
				function ($newTimeslot) use ($surveyId) {
					return "('$surveyId', '" . $newTimeslot["startsAt"] . "')";
				},
				$newTimeslots
			);
			$newTimeslotInsertValues = implode(", ", $newTimeslotInsertValues);
			$insertQuery .= $newTimeslotInsertValues;
			$insertQuery .= ";";

			$this->execQuery($insertQuery);
		}
	}
}
