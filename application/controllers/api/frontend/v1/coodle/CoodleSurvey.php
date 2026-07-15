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

	protected $coodlePageUrl;

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getSurvey' => self::PERM_LOGGED,
			'getActiveSurveys' => self::PERM_LOGGED,
			'getInactiveSurveys' => self::PERM_LOGGED,
			'createSurvey' => self::PERM_LOGGED,
			'updateSurvey' => self::PERM_LOGGED,
			'searchParticipants' => self::PERM_LOGGED,
			'submitParticipantSelection' => self::PERM_LOGGED,
			'cancelSurvey' => self::PERM_LOGGED,
			'completeSurvey' => self::PERM_LOGGED,
			'sendVotingReminders' => self::PERM_LOGGED,
		]);

		$this->load->library('PermissionLib');
		$this->load->library('form_validation');
		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model('ressource/CoodleSurvey_model', 'CoodleSurveyModel');
		$this->load->model('ressource/CoodleSurveyTimeslot_model', 'CoodleSurveyTimeslotModel');
		$this->load->model('ressource/CoodleSurveyParticipant_model', 'CoodleSurveyParticipantModel');
		$this->load->helper('hlp_sancho_helper');

		$this->coodlePageUrl = APP_ROOT . "cis.php/Cis/Coodle";
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
			"name" => getData($this->PersonModel->getFullName($survey->creator_uid)),
		];
		unset($survey->creator_uid);

		$this->terminateWithSuccess($survey);
	}

	public function getActiveSurveys()
	{
		$uid = getAuthUID();
		$activeSurveys = $this->CoodleSurveyModel->getActiveSurveys($uid);
		$activeSurveys = array_map(
			function ($survey) {
				$survey->creator = [
					"uid" => $survey->creator_uid,
					"name" => $survey->creator_name,
				];
				unset($survey->creator_uid);
				unset($survey->creator_name);
				return $survey;
			},
			$activeSurveys
		);
		$this->terminateWithSuccess($activeSurveys);
	}

	public function getInactiveSurveys()
	{
		$uid = getAuthUID();
		$inactiveSurveys = $this->CoodleSurveyModel->getInactiveSurveys($uid);
		$inactiveSurveys = array_map(
			function ($survey) {
				$survey->creator = [
					"uid" => $survey->creator_uid,
					"name" => $survey->creator_name,
				];
				unset($survey->creator_uid);
				unset($survey->creator_name);
				return $survey;
			},
			$inactiveSurveys
		);
		$this->terminateWithSuccess($inactiveSurveys);
	}

	public function createSurvey()
	{
		$surveyData = $this->input->post("surveyData");
		$shouldInformParticipants = $this->input->post("shouldInformParticipants");

		$this->form_validation->set_data([
			"title" => $surveyData["title"],
			"description" => $surveyData["description"],
			"timeslotDuration" => $surveyData["timeslotDuration"],
			"maxSelections" => $surveyData["maxSelections"],
			"endsAt" => $surveyData["endsAt"],
		]);
		$this->form_validation->set_rules("title", "Title", "required|max_length[255]");
		$this->form_validation->set_rules("description", "Description", "max_length[1000]");
		$this->form_validation->set_rules("timeslotDuration", "Appointment duration", "required|integer|min[5]|max[300]");
		$this->form_validation->set_rules("maxSelections", "Maximum number of selections", "required|integer|min[1]");
		$this->form_validation->set_rules("endsAt", "Planned end date", "required");
		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$surveyId = getData($this->CoodleSurveyModel->createSurvey($surveyData, getAuthUID()));
		$this->CoodleSurveyTimeslotModel->updateTimeslots($surveyId, $surveyData["timeslots"]);
		$timeslots = $this->CoodleSurveyTimeslotModel->getTimeslots($surveyId);
		$this->CoodleSurveyParticipantModel->updateParticipants($surveyId, $surveyData["participants"], $timeslots);

		if ($shouldInformParticipants) {
			$survey = $this->CoodleSurveyModel->getSurvey($surveyId);
			$participants = $this->CoodleSurveyParticipantModel->getParticipants($surveyId);
			$authUserFullName = getData($this->PersonModel->getFullName(getAuthUID()));

			foreach ($participants as $participant) {
				sendSanchoMail(
					"Sancho_Mail_Coodle_Created",
					[
						"surveyParticipantName" => $participant->name,
						"surveyCreatorName" => $authUserFullName,
						"surveyTitle" => $survey->title,
						"surveyHref" => $this->coodlePageUrl . "?id=" . $surveyId,
					],
					$participant->uid . "@" . DOMAIN,
					"Coodle Umfrage erstellt / Coodle survey created"
				);
			}
		}

		$this->terminateWithSuccess($surveyId);
	}

	public function updateSurvey()
	{
		$surveyId = $this->input->post("surveyId");
		$surveyData = $this->input->post("surveyData");
		$shouldInformParticipants = $this->input->post("shouldInformParticipants");
		
		$this->form_validation->set_data([
			"title" => $surveyData["title"],
			"description" => $surveyData["description"],
			"timeslotDuration" => $surveyData["timeslotDuration"],
			"maxSelections" => $surveyData["maxSelections"],
			"endsAt" => $surveyData["endsAt"],
		]);
		$this->form_validation->set_rules("title", "Title", "required|string|max_length[255]");
		$this->form_validation->set_rules("description", "Description", "string|max_length[1000]");
		$this->form_validation->set_rules("timeslotDuration", "Appointment duration", "required|integer|min[5]|max[300]");
		$this->form_validation->set_rules("maxSelections", "Maximum number of selections", "required|integer|min[1]");
		$this->form_validation->set_rules("endsAt", "Planned end date", "required");
		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		if (!$surveyId) {
			$this->terminateWithError("Missing survey id!");
		}

		$survey = $this->CoodleSurveyModel->getSurvey($surveyId);

		if (!$survey)
			$this->terminateWithError("Survey not found!");
		if ($survey->creator_uid !== getAuthUID())
			$this->terminateWithError("You are not authorized to modify this survey!");

		$this->CoodleSurveyModel->updateSurvey($surveyId, $surveyData);
		$this->CoodleSurveyTimeslotModel->updateTimeslots($surveyId, $surveyData["timeslots"]);
		$timeslots = $this->CoodleSurveyTimeslotModel->getTimeslots($surveyId);
		$this->CoodleSurveyParticipantModel->updateParticipants($surveyId, $surveyData["participants"], $timeslots);

		if ($shouldInformParticipants) {
			$survey = $this->CoodleSurveyModel->getSurvey($surveyId);
			$participants = $this->CoodleSurveyParticipantModel->getParticipants($surveyId);
			$authUserFullName = getData($this->PersonModel->getFullName(getAuthUID()));

			foreach ($participants as $participant) {
				sendSanchoMail(
					'Sancho_Mail_Coodle_Updated',
					[
						"surveyParticipantName" => $participant->name,
						"surveyCreatorName" => $authUserFullName,
						"surveyTitle" => $survey->title,
						"surveyHref" => $this->coodlePageUrl . "?id=" . $surveyId,
					],
					$participant->uid . "@" . DOMAIN,
					'Coodle Umfrage modifiziert / Coodle survey modified'
				);
			}
		}

		$this->terminateWithSuccess($surveyId);
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

	public function submitParticipantSelection()
	{
		$surveyId = $this->input->post("surveyId");
		$selection = $this->input->post("selection");

		$survey = $this->CoodleSurveyModel->getSurvey($surveyId);
		if (!$survey) {
			$this->terminateWithError("Survey not found!");
		}

		if ($survey->completed_at || $survey->canceled_at) {
			$this->terminateWithError("You can no longer vote in this survey!");
		}

		$participants = $this->CoodleSurveyParticipantModel->getParticipants($surveyId);
		$participantUids = array_map(
			function ($participant) {
				return $participant->uid;
			},
			$participants
		);
		if (!in_array(getAuthUID(), $participantUids)) {
			$this->terminateWithError("You are not authorized to participate in this survey!");
		}

		if ($selection && count($selection)) {
			$timeslots = $this->CoodleSurveyTimeslotModel->getTimeslots($surveyId);
			$timeslotIds = array_map(
				function ($timeslot) {
					return $timeslot->id;
				},
				$timeslots
			);
			$selection = array_filter(
				$selection,
				function ($selectedTimeslotId) use ($timeslotIds) {
					return in_array($selectedTimeslotId, $timeslotIds);
				}
			);

			$selection = array_slice($selection, 0, $survey->max_selections);
		}

		$this->CoodleSurveyParticipantModel->updateSelection($surveyId, getAuthUID(), json_encode($selection));
		$this->terminateWithSuccess($surveyId);
	}

	public function cancelSurvey()
	{
		$shouldInformParticipants = $this->input->post("shouldInformParticipants");
		$surveyId = $this->input->post("surveyId");
		$survey = $this->CoodleSurveyModel->getSurvey($surveyId);

		if (!$survey) {
			$this->terminateWithError("Survey not found!");
		} else if ($survey->creator_uid !== getAuthUID()) {
			$this->terminateWithError("You are not authorized to modify this survey!");
		} else if ($survey->canceled_at) {
			$this->terminateWithError("This survey has already been canceled!");
		} else if ($survey->completed_at) {
			$this->terminateWithError("This survey has already been completed!");
		}

		$this->CoodleSurveyModel->cancelSurvey($surveyId);

		if ($shouldInformParticipants) {
			$survey = $this->CoodleSurveyModel->getSurvey($surveyId);
			$participants = $this->CoodleSurveyParticipantModel->getParticipants($surveyId);
			$authUserFullName = getData($this->PersonModel->getFullName(getAuthUID()));

			foreach ($participants as $participant) {
				sendSanchoMail(
					'Sancho_Mail_Coodle_Canceled',
					[
						"surveyParticipantName" => $participant->name,
						"surveyCreatorName" => $authUserFullName,
						"surveyTitle" => $survey->title,
						"surveyHref" => $this->coodlePageUrl . "?id=" . $surveyId,
					],
					$participant->uid . "@" . DOMAIN,
					'Coodle Umfrage storniert / Coodle survey canceled'
				);
			}
		}
	}

	public function completeSurvey()
	{
		$shouldInformParticipants = $this->input->post("shouldInformParticipants");
		$surveyId = $this->input->post("surveyId");
		$selectedTimeslotId = $this->input->post("selectedTimeslotId");
		$selectedRoomId = $this->input->post("selectedRoomId");

		$survey = $this->CoodleSurveyModel->getSurvey($surveyId);
		if (!$survey) {
			$this->terminateWithError("Survey not found!");
		} else if ($survey->creator_uid !== getAuthUID()) {
			$this->terminateWithError("You are not authorized to modify this survey!");
		} else if ($survey->canceled_at) {
			$this->terminateWithError("This survey has already been canceled!");
		} else if ($survey->completed_at) {
			$this->terminateWithError("This survey has already been completed!");
		}

		$selectedTimeslot = null;
		if ($selectedTimeslotId) {
			$selectedTimeslot = $this->CoodleSurveyTimeslotModel->getTimeslot($selectedTimeslotId);
			if (!$selectedTimeslot) {
				$this->terminateWithError("Selected timeslot not found!");
			} else if ($selectedTimeslot->survey_id !== $surveyId) {
				$this->terminateWithError("Invalid timeslot!");
			}
		}

		if ($selectedTimeslot && $selectedRoomId) {
			$this->load->library('StundenplanLib');
			$tz = new DateTimeZone('Europe/Berlin');
			$reservationStart = DateTimeImmutable::createFromFormat(
				'Y-m-d H:i:s',
				$selectedTimeslot->starts_at,
				$tz
			);
			$reservationEnd = $reservationStart->modify("+$survey->timeslot_duration minutes");
			$this->stundenplanlib->addReservation(
				$reservationStart->format("c"),
				$reservationEnd->format("c"),
				"Coodle",
				null,
				$selectedRoomId
			);
		}

		$this->CoodleSurveyModel->completeSurvey($surveyId, $selectedTimeslotId);

		if ($shouldInformParticipants) {
			$survey = $this->CoodleSurveyModel->getSurvey($surveyId);
			$participants = $this->CoodleSurveyParticipantModel->getParticipants($surveyId);
			$authUserFullName = getData($this->PersonModel->getFullName(getAuthUID()));

			if ($selectedTimeslot) {
				$tz = new DateTimeZone('Europe/Berlin');
				$timeslotStart = DateTimeImmutable::createFromFormat(
					'Y-m-d H:i:s',
					$selectedTimeslot->starts_at,
					$tz
				);
				$timeslotEnd = $timeslotStart->modify("+$survey->timeslot_duration minutes");
				$formattedTimeslot = $timeslotStart->format("d.m.Y H:i") . "-" . $timeslotEnd->format("H:i");

				$calendarFilePath = tempnam(sys_get_temp_dir(), "coodle_");
				$this->writeToIcsFile(
					$calendarFilePath,
					$survey->title,
					$timeslotStart,
					$timeslotEnd,
					$selectedRoomId
				);

				foreach ($participants as $participant) {
					sendSanchoMail(
						'Sancho_Mail_Coodle_Completed',
						[
							"surveyParticipantName" => $participant->name,
							"surveyCreatorName" => $authUserFullName,
							"surveyTitle" => $survey->title,
							"selectedSurveyTimeslot" => $formattedTimeslot,
							"surveyHref" => $this->coodlePageUrl . "?id=" . $surveyId,
						],
						$participant->uid . "@" . DOMAIN,
						"Coodle Umfrage vollendet / Coodle survey completed",
						"",
						"",
						null,
						null,
						null,
						[
							[
								"filePath" => $calendarFilePath,
								"altName" => "coodle.ics",
							]
						]
					);
				}
				unlink($calendarFilePath);
			} else {
				foreach ($participants as $participant) {
					sendSanchoMail(
						'Sancho_Mail_Coodle_Completed_No',
						[
							"surveyParticipantName" => $participant->name,
							"surveyCreatorName" => $authUserFullName,
							"surveyTitle" => $survey->title,
							"surveyHref" => $this->coodlePageUrl . "?id=" . $surveyId,
						],
						$participant->uid . "@" . DOMAIN,
						'Coodle Umfrage vollendet / Coodle survey completed'
					);
				}
			}
		}
		$this->terminateWithSuccess();
	}

	public function sendVotingReminders()
	{
		$surveyId = $this->input->post("surveyId");
		$survey = $this->CoodleSurveyModel->getSurvey($surveyId);
		if (!$survey) {
			$this->terminateWithError("Survey not found!");
		} else if ($survey->creator_uid !== getAuthUID()) {
			$this->terminateWithError("You do not own this survey!");
		} else if ($survey->canceled_at || $survey->completed_at) {
			$this->terminateWithError("This survey is no longer active!");
		}

		$participants = $this->CoodleSurveyParticipantModel->getParticipants($surveyId);
		$participantsWithoutVote = array_filter(
			$participants,
			function ($participant) {
				return $participant->selection === null;
			}
		);

		if (!count($participantsWithoutVote)) {
			$this->terminateWithError("All participants have already voted!");
		}

		$authUserFullName = getData($this->PersonModel->getFullName(getAuthUID()));

		foreach ($participantsWithoutVote as $participant) {
			sendSanchoMail(
				'Sancho_Mail_Coodle_Reminder',
				[
					"surveyParticipantName" => $participant->name,
					"surveyCreatorName" => $authUserFullName,
					"surveyTitle" => $survey->title,
					"surveyHref" => $this->coodlePageUrl . "?id=" . $surveyId,
				],
				$participant->uid . "@" . DOMAIN,
				'Coodle Umfrage Erinnerung / Coodle survey reminder'
			);
		}

		$this->terminateWithSuccess();
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

	private function writeToIcsFile($calendarFilePath, $title, $start, $end, $location)
	{
		$calendarFile = fopen($calendarFilePath, "w");
		fwrite($calendarFile, "BEGIN:VCALENDAR" . PHP_EOL);
		fwrite($calendarFile, "VERSION:2.0" . PHP_EOL);
		fwrite($calendarFile, "CALSCALE:GREGORIAN" . PHP_EOL);
		fwrite($calendarFile, "PRDODID:" . CAMPUS_NAME . PHP_EOL);
		fwrite($calendarFile, "X-WR-TIMEZONE:Europe/Vienna" . PHP_EOL);
		fwrite($calendarFile, "BEGIN:VEVENT" . PHP_EOL);
		fwrite($calendarFile, "DTSTART:" . $start->format("Ymd") . "T" . $start->format("His") . PHP_EOL);
		fwrite($calendarFile, "DTEND:" . $end->format("Ymd") . "T" . $end->format("His") . PHP_EOL);
		fwrite($calendarFile, "SUMMARY:" . $title . PHP_EOL);
		if ($location) {
			fwrite($calendarFile, "LOCATION:" . $location . PHP_EOL);
		}
		fwrite($calendarFile, "TRANSP:OPAQUE" . PHP_EOL);
		fwrite($calendarFile, "END:VEVENT" . PHP_EOL);
		fwrite($calendarFile, "END:VCALENDAR");
	}
}
