<?php
/**
 * Copyright (C) 2025 fhcomplete.org
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

/**
 *
 */
class Login extends FHCAPI_Controller
{
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct(array(
			'loginLDAP' => self::PERM_ANONYMOUS,
			'loginASByUid' => 'admin:rw',
			'loginASByPersonId' => 'admin:rw',
			'whoAmI' => self::PERM_ANONYMOUS,
			'searchUser' => 'admin:rw'
		));
	}

	/**
	 * Called with HTTP POST via ajax to login using the LDAP authentication
	 */
	public function loginLDAP()
	{
		$username = $this->input->post('username');
		$password = $this->input->post('password');

		$this->load->library('AuthLib', array(false)); // without authentication otherwise loooooop!

		$login = $this->authlib->loginLDAP($username, $password);

		// If login is success then retrieves the desired page
		if (isSuccess($login)) $this->terminateWithSuccess($this->authlib->getLandingPage());

		$this->terminateWithError(getError($login)); // returns the error code
	}

	/**
	 * Called with HTTP POST via ajax to login as another user specified by uid
	 */
	public function loginASByUid()
	{
		$uid = $this->input->post('uid');

		// With authentication -> you must be already logged to gain another identity
		$this->load->library('AuthLib');

		$loginAS = $this->authlib->loginASByUID($uid);

		// Got it!
		if (isSuccess($loginAS)) $this->terminateWithSuccess(true);

		// Returns the error code
		$this->terminateWithError(getError($loginAS));
	}

	/**
	 * Called with HTTP POST via ajax to login as another user specified by person id
	 */
	public function loginASByPersonId()
	{
		$person_id = $this->input->post('person_id');

		// With authentication -> you must be already logged to gain another identity
		$this->load->library('AuthLib');

		$loginAS = $this->authlib->loginASByPersonId($person_id);

		// Got it!
		if (isSuccess($loginAS)) $this->terminateWithSuccess(true);
	
		// Returns the error code
		$this->terminateWithError(getError($loginAS));
	}

	/**
	 * Called with HTTP GET via ajax to show which login cretentials are in use
	 */
	public function whoAmI()
	{
		// With authentication -> you must be already logged to gain another identity
		$this->load->library('AuthLib');

		$this->terminateWithSuccess($this->authlib->getAuthObj());
	}

	/**
	 * Search for a user in database checking the name, surname or uid
	 */
	public function searchUser()
	{
		$query = strtolower('%'.$this->input->get('query').'%');

		$this->load->model('person/Benutzer_model', 'BenutzerModel');

		$dataset = $this->BenutzerModel->execReadOnlyQuery('
			SELECT p.person_id,
				b.uid,
				p.nachname,
				p.vorname,
				b.uid,
				p.person_id || \' - \' || b.uid || \' - \' || p.nachname || \' \' || p.vorname AS label
			  FROM public.tbl_person p
		     LEFT JOIN public.tbl_benutzer b ON(b.person_id = p.person_id)
			 WHERE b.aktiv = TRUE
			   AND (p.nachname ILIKE ? OR p.vorname ILIKE ? OR b.uid ILIKE ? OR p.person_id::text LIKE ?)
		      ORDER BY p.nachname, p.vorname
		',
			array($query, $query, $query, $query)
		);

		if (isError($dataset)) $this->terminateWithError(getError($dataset));

		$this->terminateWithSuccess($dataset);
	}
}

