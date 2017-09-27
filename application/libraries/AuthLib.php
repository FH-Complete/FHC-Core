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
	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();

		// Gets CI instance
		$this->ci =& get_instance();

		// Loads helper message to manage returning messages
		$this->ci->load->helper('Message');
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
		$this->ci->load->model('person/person_model', 'PersonModel');

		$person = $this->ci->PersonModel->loadWhere(array('zugangscode' => $code));

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
		$this->ci->load->model('person/person_model', 'PersonModel');

		$person = $this->ci->PersonModel->getPersonKontaktByZugangscode($code, $email);

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
		$this->ci->load->model('person/Benutzer_model', 'BenutzerModel');

		$benutzer = $this->ci->BenutzerModel->load($uid);

		if (hasData($benutzer))
		{
			$finalUserBasicDataByUID = $this->_getFinalUserBasicDataByPersonID($benutzer->retval[0]->person_id, $uid);
		}

		return $finalUserBasicDataByUID;
	}

	/**
	 * Returns all the keys with which is possible to obtain personal data about a final user
	 * using the given person_id. The uid is optional
	 */
	private function _getFinalUserBasicDataByPersonID($person_id, $uid = null)
	{
		$finalUserBasicDataByPersonID = new stdClass(); // returned object

		// Store the person_id and eventually all the uids and prestudent_ids related to this final user
		$finalUserBasicDataByPersonID->person_id = $person_id;
		$finalUserBasicDataByPersonID->uids = array();
		$finalUserBasicDataByPersonID->prestudent_ids = array();

		// If the UID has not been given as a parameter
		if ($uid != null)
		{
			$finalUserBasicDataByPersonID->uid[0] = $uid;
		}
		else
		{
			// Load model BenutzerModel
			$this->ci->load->model('person/Benutzer_model', 'BenutzerModel');

			// Loads all the benutzer whith that person_id
			$benutzer = $this->ci->BenutzerModel->loadWhere(array('person_id' => $person_id));

			if (hasData($benutzer)) // store all the uids
			{
				foreach ($benutzer->retval as $rownum => $rowdata)
				{
					$finalUserBasicDataByPersonID->uids[$rownum] = $rowdata->uid;
				}
			}
		}

		// Load model PrestudentModel
		$this->ci->load->model('crm/Prestudent_model', 'PrestudentModel');

		// Loads all the prestudent whith that person_id
		$prestudent = $this->ci->PrestudentModel->loadWhere(array('person_id' => $person_id));

		if (hasData($prestudent)) // store all the prestudent_ids
		{
			foreach ($prestudent->retval as $rownum => $rowdata)
			{
				$finalUserBasicDataByPersonID->prestudent_ids[$rownum] = $rowdata->prestudent_id;
			}
		}

		return success($finalUserBasicDataByPersonID);
	}
}
