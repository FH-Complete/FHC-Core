<?php
class CoodleSurvey_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_coodle_surveys';
		$this->pk = 'id';
	}

	public function getSurvey($surveyId)
	{
		$query = "
			SELECT *
			FROM $this->dbTable
			WHERE id = $surveyId
			LIMIT 1
		";
		return $this->execQuery($query)->retval[0];
	}

	public function createSurvey($surveyData, $creatorUid)
	{
		$surveyCreationResult = $this->insert([
			"creator_uid" => $creatorUid,
			"title" => $surveyData["title"],
			"description" => $surveyData["description"],
			"timeslot_duration" => $surveyData["timeslotDuration"],
			"are_selections_anonymized" => $surveyData["areSelectionsAnonymized"],
			"are_participants_anonymized" => $surveyData["areParticipantsAnonymized"],
			"max_selections" => $surveyData["maxSelections"],
			"selected_timeslot_id" => null,
			"ends_at" => $surveyData["endsAt"],
			"completed_at" => null,
			"canceled_at" => null,
			"created_at" => "NOW()",
			"updated_at" => "NOW()",
		]);

		if (isError($surveyCreationResult))
		{
			return error('Something went wrong during survey creation!');
		}
		
		return success($surveyCreationResult->retval);
	}

	public function updateSurvey($surveyId, $surveyData)
	{
		$surveyUpdateResult = $this->update($surveyId, [
			"title" => $surveyData["title"],
			"description" => $surveyData["description"],
			"timeslot_duration" => $surveyData["timeslotDuration"],
			"are_selections_anonymized" => $surveyData["areSelectionsAnyonymized"],
			"are_participants_anonymized" => $surveyData["areParticipantsAnyonymized"],
			"max_selections" => $surveyData["maxSelections"],
			"ends_at" => $surveyData["endsAt"],
			"updated_at" => "NOW()",
		]);

		if (isError($surveyUpdateResult))
		{
			return error('Something went wrong during survey update!');
		}
		
		return success($surveyUpdateResult->retval);
	}
}
