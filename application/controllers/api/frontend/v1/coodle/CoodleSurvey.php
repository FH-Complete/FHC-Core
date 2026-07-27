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
	protected $coodlePageExternalUrl;
	protected $coodleIcalUrl;
	protected $coodleIcalUrlWithEncryptedUid;

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getSurvey' => self::PERM_LOGGED,
			'getSurveyForExternalParticipant' => self::PERM_ANONYMOUS,
			'getActiveSurveys' => self::PERM_LOGGED,
			'getInactiveSurveys' => self::PERM_LOGGED,
			'createSurvey' => self::PERM_LOGGED,
			'updateSurvey' => self::PERM_LOGGED,
			'searchParticipants' => self::PERM_LOGGED,
			'submitParticipantSelection' => self::PERM_LOGGED,
			'submitExternalParticipantSelection' => self::PERM_ANONYMOUS,
			'cancelSurvey' => self::PERM_LOGGED,
			'completeSurvey' => self::PERM_LOGGED,
			'sendVotingReminders' => self::PERM_LOGGED,
			'getCoodleIcalUrl' => self::PERM_LOGGED,
			'getCoodleIcal' => self::PERM_ANONYMOUS,
			'getCoodleIcalEncrypted' => self::PERM_ANONYMOUS,
		]);

		$this->load->library('form_validation');
		$this->load->library('CryptLib');
		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model('ressource/CoodleSurvey_model', 'CoodleSurveyModel');
		$this->load->model('ressource/CoodleSurveyTimeslot_model', 'CoodleSurveyTimeslotModel');
		$this->load->model('ressource/CoodleSurveyParticipant_model', 'CoodleSurveyParticipantModel');
		$this->load->model('ressource/CoodleSurveyExternalParticipant_model', 'CoodleSurveyExternalParticipantModel');
		$this->load->helper('hlp_sancho_helper');

		$this->coodlePageUrl = APP_ROOT . "cis.php/Cis/Coodle";
		$this->coodlePageExternalUrl = APP_ROOT . "cis.php/Cis/CoodleExternal/{key}";
		$this->coodleIcalUrl = APP_ROOT . "cis.php/CoodleIcal/{uid}";
		$this->coodleIcalUrlWithEncryptedUid = APP_ROOT . "cis.php/CoodleIcal/encrypted/{encryptedUid}";

		$this->loadPhrases([
			'coodle'
		]);
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
		$participants = $this->parseParticipantSelections($participants);

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

		$externalParticipants = $this->CoodleSurveyExternalParticipantModel->getExternalParticipants($surveyId);
		$externalParticipants = $this->parseParticipantSelections($externalParticipants);

		$survey->participants = $participants;
		$survey->external_participants = $externalParticipants;
		$survey->vote_tallies = $this->getSurveyVoteTallies($timeslots, $participants, $externalParticipants);

		$survey = $this->anonymizeSurvey($survey, $authUserUID, null);

		$survey->creator = [
			"uid" => $survey->creator_uid,
			"name" => getData($this->PersonModel->getFullName($survey->creator_uid)),
		];
		unset($survey->creator_uid);

		$this->terminateWithSuccess($survey);
	}

	public function getSurveyForExternalParticipant()
	{
		$key = $this->input->post("key");
		$validatedKey = $this->validateEncryptedExternalParticipantKey($this->safeDecode($key));
		if (!$validatedKey)
			$this->terminateWithError($this->p->t("coodle", "invalid_access_key_error"));

		$survey = $validatedKey["survey"];
		$externalParticipant = $validatedKey["externalParticipant"];

		$timeslots = $this->CoodleSurveyTimeslotModel->getTimeslots($survey->id);
		$survey->timeslots = $timeslots;

		$participants = $this->CoodleSurveyParticipantModel->getParticipants($survey->id);
		$participants = $this->parseParticipantSelections($participants);
		$survey->participants = $participants;

		$externalParticipants = $this->CoodleSurveyExternalParticipantModel->getExternalParticipants($survey->id);
		$externalParticipants = $this->parseParticipantSelections($externalParticipants);
		$survey->external_participants = $externalParticipants;

		$survey->vote_tallies = $this->getSurveyVoteTallies($timeslots, $participants, $externalParticipants);

		$survey = $this->anonymizeSurvey($survey, null, $externalParticipant->id);

		$survey->creator = [
			"uid" => null,
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
		$this->CoodleSurveyExternalParticipantModel->updateExternalParticipants($surveyId, $surveyData["externalParticipants"], $timeslots);

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

			$externalParticipants = $this->CoodleSurveyExternalParticipantModel->getExternalParticipants($surveyId);
			foreach ($externalParticipants as $externalParticipant) {
				$encryptedAccessKey = $this->safeEncode($this->getEncryptedExternalParticipantKey($externalParticipant));

				sendSanchoMail(
					"Sancho_Mail_Coodle_Created_Ext",
					[
						"surveyParticipantName" => $externalParticipant->name,
						"surveyCreatorName" => $authUserFullName,
						"org" => CAMPUS_NAME,
						"surveyTitle" => $survey->title,
						"surveyHref" => str_replace("{key}", $encryptedAccessKey, $this->coodlePageExternalUrl),
					],
					$externalParticipant->email,
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
		$this->form_validation->set_rules("title", "Title", "required|max_length[255]");
		$this->form_validation->set_rules("description", "Description", "max_length[1000]");
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
		$this->CoodleSurveyExternalParticipantModel->updateExternalParticipants($surveyId, $surveyData["externalParticipants"], $timeslots);

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

			$externalParticipants = $this->CoodleSurveyExternalParticipantModel->getExternalParticipants($surveyId);
			foreach ($externalParticipants as $externalParticipant) {
				$encryptedAccessKey = $this->safeEncode($this->getEncryptedExternalParticipantKey($externalParticipant));

				sendSanchoMail(
					"Sancho_Mail_Coodle_Updated_Ext",
					[
						"surveyParticipantName" => $externalParticipant->name,
						"surveyCreatorName" => $authUserFullName,
						"org" => CAMPUS_NAME,
						"surveyTitle" => $survey->title,
						"surveyHref" => str_replace("{key}", $encryptedAccessKey, $this->coodlePageExternalUrl),
					],
					$externalParticipant->email,
					"Coodle Umfrage modifiziert / Coodle survey modified"
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

		$this->CoodleSurveyParticipantModel->updateSelection($surveyId, getAuthUID(), $selection);
		$this->terminateWithSuccess($surveyId);
	}

	public function submitExternalParticipantSelection()
	{
		$key = $this->input->post("key");
		$selection = $this->input->post("selection");

		$key = $this->input->post("key");
		$validatedKey = $this->validateEncryptedExternalParticipantKey($this->safeDecode($key));
		if (!$validatedKey)
			$this->terminateWithError($this->p->t("coodle", "invalid_access_key_error"));

		$survey = $validatedKey["survey"];
		$externalParticipant = $validatedKey["externalParticipant"];

		if ($survey->completed_at || $survey->canceled_at) {
			$this->terminateWithError("You can no longer vote in this survey!");
		}

		if ($selection && count($selection)) {
			$timeslots = $this->CoodleSurveyTimeslotModel->getTimeslots($survey->id);
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

		$this->CoodleSurveyExternalParticipantModel->updateSelection($externalParticipant->id, $selection);
		$this->terminateWithSuccess();
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
			$externalParticipants = $this->CoodleSurveyExternalParticipantModel->getExternalParticipants($surveyId);
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

			foreach ($externalParticipants as $externalParticipant) {
				$encryptedAccessKey = $this->safeEncode($this->getEncryptedExternalParticipantKey($externalParticipant));

				sendSanchoMail(
					"Sancho_Mail_Coodle_Canceled_Ext",
					[
						"surveyParticipantName" => $externalParticipant->name,
						"surveyCreatorName" => $authUserFullName,
						"org" => CAMPUS_NAME,
						"surveyTitle" => $survey->title,
						"surveyHref" => str_replace("{key}", $encryptedAccessKey, $this->coodlePageExternalUrl),
					],
					$externalParticipant->email,
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

			$localTimezone = new DateTimeZone('Europe/Vienna');
			$reservationStart = new DateTime($selectedTimeslot->starts_at, $localTimezone);
			$reservationEnd = new DateTime($selectedTimeslot->starts_at, $localTimezone);
			$reservationEnd = $reservationEnd->modify("+$survey->timeslot_duration minutes");
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
			$externalParticipants = $this->CoodleSurveyExternalParticipantModel->getExternalParticipants($surveyId);
			$authUserFullName = getData($this->PersonModel->getFullName(getAuthUID()));

			if ($selectedTimeslot) {
				$localTimezone = new DateTimeZone('Europe/Vienna');
				$timeslotStart = new DateTime($selectedTimeslot->starts_at, $localTimezone);
				$timeslotEnd = new DateTime($selectedTimeslot->starts_at, $localTimezone);
				$timeslotEnd = $timeslotEnd->modify("+$survey->timeslot_duration minutes");
				$formattedTimeslot = $timeslotStart->format("d.m.Y H:i") . "-" . $timeslotEnd->format("H:i");

				$utcTimezone = new DateTimeZone("UTC");
				$timeslotStart->setTimezone($utcTimezone);
				$timeslotEnd->setTimezone($utcTimezone);

				$calendarFilePath = tempnam(sys_get_temp_dir(), "coodle_");
				$this->writeToCoodleIcsFile(
					$calendarFilePath,
					$survey->id,
					$survey->title,
					$timeslotStart,
					$timeslotEnd,
					$selectedRoomId,
					$authUserFullName,
					getAuthUID() . "@" . DOMAIN
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

				foreach ($externalParticipants as $externalParticipant) {
					$encryptedAccessKey = $this->safeEncode($this->getEncryptedExternalParticipantKey($externalParticipant));

					sendSanchoMail(
						"Sancho_Mail_Coodle_Completed_Ext",
						[
							"surveyCreatorName" => $authUserFullName,
							"surveyParticipantName" => $externalParticipant->name,
							"org" => CAMPUS_NAME,
							"surveyTitle" => $survey->title,
							"selectedSurveyTimeslot" => $formattedTimeslot,
							"surveyHref" => str_replace("{key}", $encryptedAccessKey, $this->coodlePageExternalUrl),
						],
						$externalParticipant->email,
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

				foreach ($externalParticipants as $externalParticipant) {
					$encryptedAccessKey = $this->safeEncode($this->getEncryptedExternalParticipantKey($externalParticipant));

					sendSanchoMail(
						"Sancho_Mail_Coodle_Cmpltd_No_Ext",
						[
							"surveyCreatorName" => $authUserFullName,
							"surveyParticipantName" => $externalParticipant->name,
							"org" => CAMPUS_NAME,
							"surveyTitle" => $survey->title,
							"surveyHref" => str_replace("{key}", $encryptedAccessKey, $this->coodlePageExternalUrl),
						],
						$externalParticipant->email,
						"Coodle Umfrage vollendet / Coodle survey completed"
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
			$this->terminateWithError("You are not authorized to send reminders for this survey!");
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

		$externalParticipants = $this->CoodleSurveyExternalParticipantModel->getExternalParticipants($surveyId);
		$externalParticipantsWithoutVote = array_filter(
			$externalParticipants,
			function ($externalParticipant) {
				return $externalParticipant->selection === null;
			}
		);

		if (!count($participantsWithoutVote) && !count($externalParticipantsWithoutVote)) {
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

		foreach ($externalParticipantsWithoutVote as $externalParticipant) {
			$encryptedAccessKey = $this->safeEncode($this->getEncryptedExternalParticipantKey($externalParticipant));

			sendSanchoMail(
				"Sancho_Mail_Coodle_Reminder_Ext",
				[
					"surveyCreatorName" => $authUserFullName,
					"surveyParticipantName" => $externalParticipant->name,
					"org" => CAMPUS_NAME,
					"surveyTitle" => $survey->title,
					"surveyHref" => str_replace("{key}", $encryptedAccessKey, $this->coodlePageExternalUrl),
				],
				$externalParticipant->email,
				"Coodle Umfrage Erinnerung / Coodle survey reminder"
			);
		}

		$this->terminateWithSuccess();
	}

	public function getCoodleIcalUrl()
	{
		$uid = getAuthUID();

		$encryptedUid = $this->cryptlib->RIJNDAEL_256_ECB(str_pad($uid, 32, chr(0)), LVPLAN_CYPHER_KEY, true);
		$encodedEncryptedUid = $this->safeEncode($encryptedUid);

		$unencryptedUrl = str_replace("{uid}", $uid, $this->coodleIcalUrl);
		$encryptedUrl = str_replace("{encryptedUid}", $encodedEncryptedUid, $this->coodleIcalUrlWithEncryptedUid);

		$this->terminateWithSuccess([
			"unencryptedUrl" => $unencryptedUrl,
			"encryptedUrl" => $encryptedUrl,
		]);
	}

	public function getCoodleIcal($uid)
	{
		$coodleIcalFilePath = $this->generateCoodleIcal($uid, false);
		$this->terminateWithFileOutput("text/calendar", file_get_contents($coodleIcalFilePath), "coodle_ical.ics");
	}

	public function getCoodleIcalEncrypted($encryptedUid)
	{
		$decodedEncryptedUid = $this->safeDecode($encryptedUid);

		$uid = trim($this->cryptlib->RIJNDAEL_256_ECB_DECRYPT($decodedEncryptedUid, LVPLAN_CYPHER_KEY, true));

		$coodleIcalFilePath = $this->generateCoodleIcal($uid, true);
		$this->terminateWithFileOutput("text/calendar", file_get_contents($coodleIcalFilePath), "coodle_ical.ics");
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

	private function getSurveyVoteTallies($timeslots, $participants, $externalParticipants)
	{
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
		foreach ($externalParticipants as $externalParticipant) {
			if ($externalParticipant->selection === null)
				continue;

			if (!count($externalParticipant->selection)) {
				$individualVotes[] = "none";
				continue;
			}

			$individualVotes = array_merge($individualVotes, $externalParticipant->selection);
		}
		foreach ($individualVotes as $vote) {
			$voteTallies[$vote]++;
		}

		return $voteTallies;
	}

	private function anonymizeSurvey($survey, $authUserUID, $authExternalParticipantId)
	{
		$isAuthUserSurveyCreator = $authUserUID && $survey->creator_uid === $authUserUID;

		if ($isAuthUserSurveyCreator) {
			return $survey;
		}

		$areParticipantsAnonymized = $survey->are_participants_anonymized;
		$areSelectionsAnonymized = $survey->are_selections_anonymized;

		$authUserFilteredParticipants = $authUserUID ? array_values(array_filter(
			$survey->participants,
			function ($participant) use ($authUserUID) {
				return $participant->uid === $authUserUID;
			}
		)) : [];
		$authFilteredExternalParticipants = $authExternalParticipantId ? array_values(array_filter(
			$survey->external_participants,
			function ($externalParticipant) use ($authExternalParticipantId) {
				return $externalParticipant->id === $authExternalParticipantId;
			}
		)) : [];


		if ($areSelectionsAnonymized) {
			$survey->vote_tallies = null;
		}

		if ($areParticipantsAnonymized && $areSelectionsAnonymized) {
			$survey->participants = $authUserFilteredParticipants;
			$survey->external_participants = $authFilteredExternalParticipants;
		} else if ($areParticipantsAnonymized) {
			$survey->participants = array_map(
				function ($participant) use ($authUserUID) {
					if ($participant->uid !== $authUserUID) {
						$participant->uid = null;
						$participant->name = null;
					}
					return $participant;
				},
				$survey->participants
			);

			$survey->external_participants = array_map(
				function ($externalParticipant) use ($authExternalParticipantId) {
					if ($externalParticipant->id !== $authExternalParticipantId) {
						$externalParticipant->id = null;
						$externalParticipant->name = null;
						$externalParticipant->email = null;
					}
					return $externalParticipant;
				},
				$survey->external_participants
			);
		} else if ($areSelectionsAnonymized) {
			$survey->participants = array_map(
				function ($participant) use ($authUserUID) {
					if ($participant->uid !== $authUserUID) {
						$participant->selection = null;
					}
					return $participant;
				},
				$survey->participants
			);

			$survey->external_participants = array_map(
				function ($externalParticipant) use ($authExternalParticipantId) {
					if ($externalParticipant->id !== $authExternalParticipantId) {
						$externalParticipant->selection = null;
					}
					return $externalParticipant;
				},
				$survey->external_participants
			);
		}

		if ($authExternalParticipantId) {
			$survey->participants = array_map(
				function ($participant) {
					$participant->uid = null;
					return $participant;
				},
				$survey->participants
			);

			$survey->external_participants = array_map(
				function ($externalParticipant) use ($authExternalParticipantId) {
					if ($externalParticipant->id !== $authExternalParticipantId) {
						$externalParticipant->id = null;
					}
					return $externalParticipant;
				},
				$survey->external_participants
			);
		}

		return $survey;
	}

	private function parseParticipantSelections($participants)
	{
		$result = array_map(function ($participant) {
			if ($participant->selection) {
				$participant->selection = array_values(json_decode($participant->selection, true));
			}
			return $participant;
		}, $participants);
		return $result;
	}

	private function searchUsersAsParticipants($searchString)
	{
		$searchStrings = explode(" ", strtolower($searchString));

		$firstSearchString = $searchStrings[0];
		$remainingSearchStrings = array_slice($searchStrings, 1);

		$query = "SELECT
				'user' AS type,
				benutzer.uid AS uid,
				person.vorname || ' ' || person.nachname AS name
				FROM public.tbl_person person
				JOIN public.tbl_benutzer benutzer ON(benutzer.person_id = person.person_id)
				WHERE (LOWER(person.vorname || ' ' || person.nachname) LIKE '%" . $firstSearchString . "%'
				OR benutzer.uid LIKE '%" . $firstSearchString . "%')
		";

		if (count($remainingSearchStrings)) {
			foreach ($remainingSearchStrings as $remainingSearchString) {
				$query .= " AND (LOWER(person.vorname || ' ' || person.nachname) LIKE '%" . $remainingSearchString . "%'
				OR benutzer.uid LIKE '%" . $remainingSearchString . "%')";
			}
		}

		$query .= "ORDER BY benutzer.uid
			LIMIT 25";

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


		$userGroupsQuery = "SELECT
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

	private function writeToCoodleIcsFile($calendarFilePath, $id, $title, $startUTC, $endUTC, $location, $organizerName, $organizerEmail)
	{
		$formattedStart = $startUTC->format("Ymd\THis\Z");
		$formattedEnd = $endUTC->format("Ymd\THis\Z");

		$calendarFile = fopen($calendarFilePath, "w");
		fwrite($calendarFile, "BEGIN:VCALENDAR" . PHP_EOL);
		fwrite($calendarFile, "VERSION:2.0" . PHP_EOL);
		fwrite($calendarFile, "CALSCALE:GREGORIAN" . PHP_EOL);
		fwrite($calendarFile, "ORGANIZER;CN=" . $organizerName . ":mailto:" . $organizerEmail . PHP_EOL);
		fwrite($calendarFile, "PRDODID:" . CAMPUS_NAME . PHP_EOL);
		fwrite($calendarFile, "BEGIN:VEVENT" . PHP_EOL);
		fwrite($calendarFile, "UID:" . "coodle_" . $id . "_" . $formattedStart . "_" . $formattedEnd . PHP_EOL);
		fwrite($calendarFile, "DTSTART:" . $formattedStart . PHP_EOL);
		fwrite($calendarFile, "DTEND:" . $formattedEnd . PHP_EOL);
		fwrite($calendarFile, "SUMMARY:" . $title . PHP_EOL);
		if ($location) {
			fwrite($calendarFile, "LOCATION:" . $location . PHP_EOL);
		}
		fwrite($calendarFile, "TRANSP:OPAQUE" . PHP_EOL);
		fwrite($calendarFile, "END:VEVENT" . PHP_EOL);
		fwrite($calendarFile, "END:VCALENDAR");
	}

	private function generateCoodleIcal($uid, $shouldIncludeIdentifyingInformation)
	{
		$activeParticipantEntries = $this->CoodleSurveyParticipantModel->getActiveParticipantEntriesByUid($uid);
		$selectedTimeslotIds = [];
		foreach ($activeParticipantEntries as $participantEntry) {
			if ($participantEntry->selection === null)
				continue;

			$selectedTimeslotIds = array_merge($selectedTimeslotIds, json_decode($participantEntry->selection));
		}

		$events = [];
		$localTimezone = new DateTimeZone("Europe/Vienna");
		$utcTimezone = new DateTimeZone("UTC");
		foreach ($selectedTimeslotIds as $timeslotId) {
			$timeslot = $this->CoodleSurveyTimeslotModel->getTimeslot($timeslotId);
			if (!$timeslot)
				continue;

			$survey = $this->CoodleSurveyModel->getSurvey($timeslot->survey_id);

			$startTime = new DateTime($timeslot->starts_at, $localTimezone);
			$endTime = new DateTime($timeslot->starts_at, $localTimezone);
			$endTime->modify("+$survey->timeslot_duration minutes");

			$startTime->setTimezone($utcTimezone);
			$endTime->setTimezone($utcTimezone);

			$surveyCreatorFullName = getData($this->PersonModel->getFullName($survey->creator_uid));

			$formattedStartTime = $startTime->format("Ymd\THis\Z");
			$formattedEndTime = $endTime->format("Ymd\THis\Z");
			$events[] = [
				"id" => "coodle_termin_option_" . $survey->id . "_" . $formattedStartTime . "_" . $formattedEndTime,
				"summary" => "Coodle Terminoption" . ($shouldIncludeIdentifyingInformation ? " $survey->title" : ""),
				"description" => $shouldIncludeIdentifyingInformation ? "Erstellt von " . $surveyCreatorFullName : "",
				"start" => $formattedStartTime,
				"end" => $formattedEndTime,
			];
		}

		$coodleIcalFilePath = tempnam(sys_get_temp_dir(), "coodle_ical_");

		$coodleIcalFile = fopen($coodleIcalFilePath, "w");
		fwrite($coodleIcalFile, "BEGIN:VCALENDAR" . PHP_EOL);
		fwrite($coodleIcalFile, "VERSION:2.0" . PHP_EOL);
		fwrite($coodleIcalFile, "PRDODID:" . CAMPUS_NAME . PHP_EOL);

		foreach ($events as $event) {
			fwrite($coodleIcalFile, "BEGIN:VEVENT" . PHP_EOL);
			fwrite($coodleIcalFile, "UID:" . $event["id"] . PHP_EOL);
			fwrite($coodleIcalFile, "SUMMARY:" . $event["summary"] . PHP_EOL);
			fwrite($coodleIcalFile, "DESCRIPTION:" . $event["description"] . PHP_EOL);
			fwrite($coodleIcalFile, "DTSTART:" . $event["start"] . PHP_EOL);
			fwrite($coodleIcalFile, "DTNED:" . $event["end"] . PHP_EOL);
			fwrite($coodleIcalFile, "TRANS:OPAQUE" . PHP_EOL);
			fwrite($coodleIcalFile, "END:VEVENT" . PHP_EOL);
		}

		fwrite($coodleIcalFile, "END:VCALENDAR");

		return $coodleIcalFilePath;
	}

	private function safeEncode($value)
	{
		$encodedValue = base64_encode($value);
		$encodedValue = str_replace(array('+', '/', '='), array('-', '_', ''), $encodedValue);
		$encodedValue = trim($encodedValue);
		return $encodedValue;
	}

	private function safeDecode($value)
	{
		$value = str_replace(array('-', '_'), array('+', '/'), $value);
		$mod4 = strlen($value) % 4;
		if ($mod4) {
			$value .= substr('====', $mod4);
		}
		$decodedValue = base64_decode($value);
		return $decodedValue;
	}

	private function getEncryptedExternalParticipantKey($externalParticipant)
	{
		$value = "coodleExt_" . $externalParticipant->survey_id . "_" . $externalParticipant->id;
		return $this->cryptlib->RIJNDAEL_256_ECB(str_pad($value, 32, chr(0)), LVPLAN_CYPHER_KEY, true);
	}

	private function validateEncryptedExternalParticipantKey($encryptedKey)
	{
		$decryptedKey = trim($this->cryptlib->RIJNDAEL_256_ECB_DECRYPT($encryptedKey, LVPLAN_CYPHER_KEY, true));
		$values = explode("_", $decryptedKey);
		if (count($values) !== 3 || $values[0] !== "coodleExt")
			return null;

		$surveyId = $values[1];
		$externalParticipantId = $values[2];

		$survey = $this->CoodleSurveyModel->getSurvey($surveyId);
		$externalParticipant = $this->CoodleSurveyExternalParticipantModel->getExternalParticipant($externalParticipantId);

		if (!$survey || !$externalParticipant || $survey->id !== $externalParticipant->survey_id)
			return null;

		return [
			"survey" => $survey,
			"externalParticipant" => $externalParticipant,
		];
	}
}
