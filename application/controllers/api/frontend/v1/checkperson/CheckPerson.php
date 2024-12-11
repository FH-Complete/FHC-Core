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

if (! defined('BASEPATH')) exit('No direct script access allowed');

class CheckPerson extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'updatePersonUnrulyStatus' => array('basis/mitarbeiter:rw', 'student/antragfreigabe:rw', 'student/studierendenantrag:rw'),
			'filterPerson' => array('basis/mitarbeiter:rw', 'student/antragfreigabe:rw', 'student/studierendenantrag:rw'),
			'checkUnruly' => array('basis/mitarbeiter:r', 'student/antragfreigabe:r', 'student/studierendenantrag:r', 'infocenter:r'),
			'checkDuplicate' => array('infocenter:r'),
		]);

		$this->_ci =& get_instance();
		$this->_ci->load->model('person/Person_model', 'PersonModel');
	}

	public function updatePersonUnrulyStatus()
	{
		$data = json_decode($this->input->raw_input_stream, true);

		$person_id = $data['person_id'];
		$unruly = $data['unruly'];

		$result = $this->_ci->PersonModel->updateUnruly($person_id, $unruly);

		if(isError($result)) {
			$this->terminateWithError($result);
		} else if (isSuccess($result)) {
			$this->terminateWithSuccess($result);
		}

	}

	public function checkDuplicate() {

		$person_id = $this->input->post('person_id');

		$result = $this->_ci->PersonModel->checkDuplicate($person_id);

		if (isSuccess($result))
			$this->terminateWithSuccess($result);
		else
			$this->terminateWithError('Error when searching for person');

	}

	// performs strict check over vorname, nachname, gebdatum
	public function checkUnruly() {

		$vorname = $this->input->post('vorname');
		$nachname = $this->input->post('nachname');
		$gebdatum = $this->input->post('gebdatum');

		$result = $this->_ci->PersonModel->checkUnruly($vorname, $nachname, $gebdatum);

		if (isSuccess($result))
			$this->terminateWithSuccess($result);
		else
			$this->terminateWithError('Error when searching for person');
	}

	// filters nachname on similarity and vorname/gebdatum are optional
	public function filterPerson() {
		$payload = json_decode($this->input->raw_input_stream, TRUE);

		$nachnameString = '';
		$vornameString = '';
		$filterUnruly = true;
		$birthdateString = '';

		if(array_key_exists( 'nachname', $payload) ) {
			$nachnameString = $payload['nachname'];
		}

		if(array_key_exists('vorname', $payload)) {
			$vornameString = $payload['vorname'];
		}

		if(array_key_exists('unruly', $payload)){
			$filterUnruly = $payload['unruly'];
		}

		if(array_key_exists('gebdatum', $payload)) {
			// TODO: enable if gebdatum filter for unrulys is desired
//				$birthdateString = $payload['gebdatum'];
		}

		$parametersArray = array($nachnameString);
		$where ="p.nachname~* ? ";
		if (mb_strlen($nachnameString) == 2)
		{
			$where = "p.nachname=? ";
		}

		if(isset($vornameString) && $vornameString != '')
		{
			$where.= " AND p.vorname~*?";
			$parametersArray[] = $vornameString;
		}

		if(isset($birthdateString) && $birthdateString != '')
		{
			$where.=" AND p.gebdatum=?";
			$parametersArray[] = $birthdateString;
		}

		if(isset($filterUnruly))
		{
			$where.=" AND p.unruly=?";
			$parametersArray[] = $filterUnruly;
		}

		$result = $this->_ci->PersonModel->checkUnrulyWhere($where, $parametersArray);

		if (isSuccess($result))
			$this->terminateWithSuccess($result);
		else
			$this->terminateWithError('Error when searching for person');


	}
}