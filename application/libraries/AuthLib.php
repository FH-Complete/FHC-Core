<?php
/**
 * FH-Complete
 *
 * @package	FHC-Helper
 * @author	FHC-Team
 * @copyright	Copyright (c) 2016 fhcomplete.org
 * @license	GPLv3
 * @link	https://fhcomplete.org
 * @since	Version 1.0.0
 * @filesource
 */

/**
 * FHC-Auth Helpers
 *
 * @package		FH-Complete
 * @subpackage	Helpers
 * @category	Helpers
 * @author		FHC-Team
 * @link		http://fhcomplete.org/user_guide/helpers/fhcauth_helper.html
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once FHCPATH.'include/authentication.class.php';

class AuthLib extends authentication
{
	private $_ci; // CI instance

	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();

		// Gets CI instance
		$this->_ci =& get_instance();
	}

	/**
	 * Checks the authentication of an addon
	 * returns TRUE if valid, otherwise FALSE
	 */
	public function basicAuthentication($username, $password)
	{
		return $this->checkpassword($username, $password);
	}

	/**
	 * Checks if the given username and password of a final user are valid
	 */
	public function CheckUserAuthByUsernamePassword($username, $password, $keys = false)
	{
		$result = error(false);

		if (isset($username) && isset($password))
		{
			if ($this->checkpassword($username, $password) === true)
			{
				if ($keys === true)
				{
					$result = $this->_getFinalUserBasicDataByUID($username);
				}
				else
				{
					$result = success(true);
				}
			}
		}

		return $result;
	}

	/**
	 * Checks if the given code of a final user is valid
	 */
	public function CheckUserAuthByCode($code)
	{
		$result = error(false);

		// Load model PersonModel
		$this->_ci->load->model('person/person_model', 'PersonModel');

		$person = $this->_ci->PersonModel->loadWhere(array('zugangscode' => $code));

		if (hasData($person))
		{
			$result = $this->_getFinalUserBasicDataByPersonID($person->retval[0]->person_id);
		}

		return $result;
	}

	/**
	 * Checks if the given code and email of a final user are valid
	 */
	public function CheckUserAuthByCodeEmail($code, $email)
	{
		$result = error(false);

		// Load model PersonModel
		$this->_ci->load->model('person/person_model', 'PersonModel');

		$person = $this->_ci->PersonModel->getPersonKontaktByZugangscode($code, $email);

		if (hasData($person))
		{
			$result = $this->_getFinalUserBasicDataByPersonID($person->retval[0]->person_id);
		}

		return $result;
	}

	/**
	 * Returns all the keys with which is possible to obtain personal data about a final user
	 * using the given username (uid)
	 */
	private function _getFinalUserBasicDataByUID($uid)
	{
		$finalUserBasicDataByUID = null;

		// Load model BenutzerModel
		$this->_ci->load->model('person/Benutzer_model', 'BenutzerModel');

		$benutzer = $this->_ci->BenutzerModel->load($uid);

		if (hasData($benutzer))
		{
			$finalUserBasicDataByUID = $this->_getFinalUserBasicDataByPersonID($benutzer->retval[0]->person_id);
		}

		return $finalUserBasicDataByUID;
	}

	/**
	 * Returns all the keys with which is possible to obtain personal data about a final user
	 * using the given person_id
	 */
	private function _getFinalUserBasicDataByPersonID($person_id)
	{
		$finalUserBasicDataByPersonID = new stdClass(); // returned object

		// Store the person_id and eventually all the uid and prestudent_id related to this final user
		$finalUserBasicDataByPersonID->person_id = $person_id;

		return success($finalUserBasicDataByPersonID);
	}
}
