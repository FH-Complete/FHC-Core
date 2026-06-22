<?php
/**
 * Copyright (C) 2024 fhcomplete.org
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

class Coodle extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'searchParticipants' => self::PERM_LOGGED,
		]);

		$this->load->library('PermissionLib');

		$this->load->model('person/Benutzer_model', 'BenutzerModel');

		// todo: remove unnecessary crap
		$this->load->model('ressource/mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('person/Person_model', 'PersonModel');


		//? put the uid and pid inside the controller for reusability
		$this->uid = getAuthUID();
		$this->pid = getAuthPersonID();

	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

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
