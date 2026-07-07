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

class CoodleSurvey extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getSurvey' => self::PERM_LOGGED,
			'createSurvey' => self::PERM_LOGGED,
			'updateSurvey' => self::PERM_LOGGED,
			'searchParticipants' => self::PERM_LOGGED,
		]);

		$this->load->library('PermissionLib');

		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model('ressource/CoodleSurvey_model', 'CoodleSurveyModel');
		$this->load->model('ressource/CoodleSurveyTimeslot_model', 'CoodleSurveyTimeslotModel');
		$this->load->model('ressource/CoodleSurveyParticipant_model', 'CoodleSurveyParticipantModel');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods


	public function getSurvey()
	{
		$surveyId = $this->input->post("surveyId");
		$survey = $this->CoodleSurveyModel->getSurvey($surveyId);

		$timeslots = $this->CoodleSurveyTimeslotModel->getTimeslots($surveyId);
		$survey->timeslots = $timeslots;

		$participants = $this->CoodleSurveyParticipantModel->getParticipants($surveyId);
		$participants = array_map(function ($participant) {
			if ($participant->selection) {
				$participant->selection = json_decode($participant->selection);
			}
			return $participant;
		}, $participants);

		$timeslotIds = array_map(function ($timeslot) {
			return $timeslot->id;
		}, $timeslots);
		$voteTallies = ["none" => 0];
		foreach ($timeslotIds as $timeslotId) {
			$voteTallies[$timeslotId] = 0;
		}

		$individualVotes = [];
		foreach ($participants as $participant) {
			if ($participant->selection === null)
				continue;

			if (!count($participant->selection)) {
				$individualVotes[] = "none";
				continue;
			}

			$individualVotes = array_merge($individualVotes, $participant->selection);
		}
		foreach ($individualVotes as $vote) {
			$voteTallies[$vote]++;
		}

		$authUserUID = getAuthUID();
		$authUserFilteredParticipants = array_values(array_filter(
			$participants,
			function ($participant) use ($authUserUID) {
				return $participant->uid === $authUserUID;
			}
		));
		$authUserParticipant = count($authUserFilteredParticipants) ? $authUserFilteredParticipants[0] : null;

		$isAuthUserSurveyCreator = $survey->creator_uid === $authUserUID;

		if (!$authUserParticipant && !$isAuthUserSurveyCreator) {
			$this->terminateWithError("You are not authorized to view this survey!");
		}

		$areParticipantsAnonymized = $survey->are_participants_anonymized;
		$areSelectionsAnonymized = $survey->are_selections_anonymized;

		if ($isAuthUserSurveyCreator || !$areSelectionsAnonymized) {
			$survey->vote_tallies = $voteTallies;
		} else {
			$survey->vote_tallies = null;
		}

		if (!$isAuthUserSurveyCreator) {
			if ($areParticipantsAnonymized && $areSelectionsAnonymized) {
				$participants = $authUserFilteredParticipants;
			} else if ($areParticipantsAnonymized) {
				$participants = array_map(
					function ($participant) use ($authUserUID) {
						if ($participant->uid !== $authUserUID) {
							$participant->uid = null;
							$participant->name = null;
						}
						return $participant;
					},
					$participants
				);
			} else if ($areSelectionsAnonymized) {
				$participants = array_map(
					function ($participant) use ($authUserUID) {
						if ($participant->uid !== $authUserUID) {
							$participant->selection = null;
						}
						return $participant;
					},
					$participants
				);
			}
		}

		$survey->participants = $participants;

		$survey->creator = [
			"uid" => $survey->creator_uid,
			"name" => $this->PersonModel->getFullName($survey->creator_uid)->retval,
		];
		unset($survey->creator_uid);

		$this->terminateWithSuccess($survey);
	}
	public function createSurvey()
	{
		$surveyData = $this->input->post("surveyData");
		// todo: form validation

		$surveyId = $this->CoodleSurveyModel->createSurvey($surveyData, getAuthUID())->retval;
		$this->CoodleSurveyTimeslotModel->updateTimeslots($surveyId, $surveyData["timeslots"]);
		$timeslots = $this->CoodleSurveyTimeslotModel->getTimeslots($surveyId);
		$this->CoodleSurveyParticipantModel->updateParticipants($surveyId, $surveyData["participants"], $timeslots);

		$this->terminateWithSuccess($surveyId);
	}

	public function updateSurvey()
	{
		// todo: update survey
	}

	public function searchParticipants()
	{
		$searchString = $this->input->post("searchString");

		if (!$searchString || !strlen($searchString))
			$this->terminateWithError("Empty search input!");

		$users = $this->searchUsersAsParticipants($searchString);
		$groups = $this->searchGroupsAsParticipants($searchString);

		$potentialParticipants = array_merge($users, $groups);

		$this->terminateWithSuccess($potentialParticipants);
	}


	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

	private function searchUsersAsParticipants($searchString)
	{
		$searchStrings = explode(" ", strtolower($searchString));

		$firstSearchString = $searchStrings[0];
		$remainingSearchStrings = array_slice($searchStrings, 1);

		$query = "
			SELECT
				'user' AS type,
				benutzer.uid AS uid,
				person.vorname || ' ' || person.nachname AS name
				FROM public.tbl_person person
				JOIN public.tbl_benutzer benutzer ON(benutzer.person_id = person.person_id)
				WHERE LOWER(person.vorname || ' ' || person.nachname) LIKE '%" . $firstSearchString . "%'
		";

		if (count($remainingSearchStrings)) {
			foreach ($remainingSearchStrings as $remainingSearchString) {
				$query .= " AND LOWER(person.vorname || ' ' || person.nachname) LIKE '%" . $remainingSearchString . "%'";
			}
		}

		$query .= " LIMIT 25";

		$dbModel = new DB_Model();
		$usersQueryResult = $dbModel->execReadOnlyQuery($query);

		if (hasData($usersQueryResult)) {
			return getData($usersQueryResult);
		} else {
			return [];
		}

	}

	private function searchGroupsAsParticipants($searchString)
	{
		$searchStrings = explode(" ", strtolower($searchString));

		$firstSearchString = $searchStrings[0];
		$remainingSearchStrings = array_slice($searchStrings, 1);

		$groupsQuery = "
			SELECT
				'group' AS type,
				gruppe_kurzbz AS name
				FROM public.tbl_gruppe
				WHERE LOWER(gruppe_kurzbz) LIKE '%" . $firstSearchString . "%'
		";

		if (count($remainingSearchStrings)) {
			foreach ($remainingSearchStrings as $remainingSearchString) {
				$groupsQuery .= " AND LOWER(gruppe_kurzbz) LIKE '%" . $remainingSearchString . "%'";
			}
		}

		$groupsQuery .= " LIMIT 25";

		$dbModel = new DB_Model();
		$groupsQueryResult = $dbModel->execReadOnlyQuery($groupsQuery);
		// return getData($groupsQueryResult);

		$groups = hasData($groupsQueryResult) ? getData($groupsQueryResult) : [];
		if (!count($groups))
			return $groups;

		$groupNames = array_map(function ($group) {
			return $group->name;
		}, $groups);


		$userGroupsQuery = "
			SELECT
				benutzerGruppe.uid as uid,
				benutzerGruppe.gruppe_kurzbz as group,
				person.vorname || ' ' || person.nachname AS name
				FROM public.tbl_benutzergruppe benutzerGruppe
				JOIN public.tbl_benutzer benutzer ON(benutzer.uid = benutzerGruppe.uid)
				JOIN public.tbl_person person ON(person.person_id = benutzer.person_id)
				WHERE gruppe_kurzbz IN ('" . implode("', '", $groupNames) . "')
		";
		$userGroupsQueryResult = $dbModel->execReadOnlyQuery($userGroupsQuery);

		$users = hasData($userGroupsQueryResult) ? getData($userGroupsQueryResult) : [];
		if (!count($users))
			return [];

		$groupedUsers = [];

		foreach ($users as $user) {
			$userData = [
				"uid" => $user->uid,
				"name" => $user->name,
			];

			$group = $user->group;

			if (isset($groupedUsers[$group])) {
				$groupedUsers[$group][] = $userData;
			} else {
				$groupedUsers[$group] = [$userData];
			}
		}

		$groups = array_map(function ($group) use ($groupedUsers) {
			$group->users = isset($groupedUsers[$group->name]) ? $groupedUsers[$group->name] : [];
			return $group;
		}, $groups);
		$groups = array_filter($groups, function ($group) {
			return count($group->users);
		});
		return $groups;
	}

}
