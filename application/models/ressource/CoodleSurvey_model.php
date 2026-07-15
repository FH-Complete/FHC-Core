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

		$this->load->model('ressource/CoodleSurveyParticipant_model', 'CoodleSurveyParticipantModel');
	}

	public function getSurvey($surveyId)
	{
		$query = "SELECT *
			FROM $this->dbTable
			WHERE id = $surveyId
			LIMIT 1
		";
		return $this->execQuery($query)->retval[0];
	}

	public function getActiveSurveys($uid)
	{
		$userParticipantEntries = $this->CoodleSurveyParticipantModel->getParticipantEntriesByUid($uid);
		$surveyIdsWhereUserIsParticipant = array_map(
			function ($userParticipantEntry) {
				return $userParticipantEntry->survey_id;
			},
			$userParticipantEntries
		);
		$surveyIdsWhereUserIsParticipantValue = implode(", ", $surveyIdsWhereUserIsParticipant);
		$query = "SELECT
			survey.id as id,
			survey.creator_uid as creator_uid,
			survey.title as title,
			survey.ends_at as ends_at,
			survey.created_at as created_at,
			person.vorname || ' ' || person.nachname AS creator_name
		 	FROM $this->dbTable as survey
			JOIN public.tbl_benutzer benutzer ON(benutzer.uid = survey.creator_uid)
			JOIN public.tbl_person person ON(person.person_id = benutzer.person_id)
			WHERE (id IN ($surveyIdsWhereUserIsParticipantValue) OR creator_uid = '$uid')
			AND completed_at IS NULL
			AND canceled_at IS NULL";
		return $this->execQuery($query)->retval;
	}

	public function getInactiveSurveys($uid)
	{
		$userParticipantEntries = $this->CoodleSurveyParticipantModel->getParticipantEntriesByUid($uid);
		$surveyIdsWhereUserIsParticipant = array_map(
			function ($userParticipantEntry) {
				return $userParticipantEntry->survey_id;
			},
			$userParticipantEntries
		);
		$surveyIdsWhereUserIsParticipantValue = implode(", ", $surveyIdsWhereUserIsParticipant);
		$query = "SELECT 
			survey.id as id,
			survey.creator_uid as creator_uid,
			survey.title as title,
			survey.created_at as created_at,
			survey.completed_at as completed_at,
			survey.canceled_at as canceled_at,
			person.vorname || ' ' || person.nachname AS creator_name
		 	FROM $this->dbTable as survey
			JOIN public.tbl_benutzer benutzer ON(benutzer.uid = survey.creator_uid)
			JOIN public.tbl_person person ON(person.person_id = benutzer.person_id)
			WHERE (id IN ($surveyIdsWhereUserIsParticipantValue) OR creator_uid = '$uid')
			AND (completed_at IS NOT NULL OR canceled_at IS NOT NULL)";
		return $this->execQuery($query)->retval;
	}

	public function createSurvey($surveyData, $creatorUid)
	{
		$surveyCreationResult = $this->insert([
			"creator_uid" => $creatorUid,
			"title" => $surveyData["title"],
			"description" => $surveyData["description"] ?? "",
			"timeslot_duration" => $surveyData["timeslotDuration"],
			"are_selections_anonymized" => !!$surveyData["areSelectionsAnonymized"],
			"are_participants_anonymized" => !!$surveyData["areParticipantsAnonymized"],
			"max_selections" => $surveyData["maxSelections"] ?? 1,
			"selected_timeslot_id" => null,
			"ends_at" => $surveyData["endsAt"],
			"completed_at" => null,
			"canceled_at" => null,
			"created_at" => "NOW()",
			"updated_at" => "NOW()",
		]);

		if (isError($surveyCreationResult)) {
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
			"are_selections_anonymized" => $surveyData["areSelectionsAnonymized"],
			"are_participants_anonymized" => $surveyData["areParticipantsAnonymized"],
			"max_selections" => $surveyData["maxSelections"],
			"ends_at" => $surveyData["endsAt"],
			"updated_at" => "NOW()",
		]);

		if (isError($surveyUpdateResult)) {
			return error('Something went wrong during survey update!');
		}

		return success($surveyUpdateResult->retval);
	}

	public function cancelSurvey($surveyId)
	{
		$this->update($surveyId, [
			"canceled_at" => "NOW()",
			"updated_at" => "NOW()",
		]);
	}

	public function completeSurvey($surveyId, $timeslotId)
	{
		$this->update($surveyId, [
			"selected_timeslot_id" => $timeslotId,
			"completed_at" => "NOW()",
			"updated_at" => "NOW()",
		]);
	}
}
