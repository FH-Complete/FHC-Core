<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (!defined('BASEPATH')) exit('No direct script access allowed');

class CheckUserAuth extends APIv1_Controller
{
	/**
	 * Course API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Checks if username and password of a final user are valid
	 */
	public function getCheckByUsernamePassword()
    {
		$username = $this->get('username');
		$password = $this->get('password');

		if (isset($username) && isset($password))
		{
			$result = $this->authlib->CheckUserAuthByUsernamePassword($username, $password);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
    }

	/**
	 * Checks if username and password of a final user are valid
	 * Returns all the keys with which is possible to obtain all the personal data about a final user
	 */
	public function getCheckUserAuth()
	{
		$code = $this->get('code');
		$email = $this->get('email');
		$username = $this->get('username');
		$password = $this->get('password');

		$result = null;
		$httpCode = null;

		// If username and password are given then check authentication using them
		if (isset($username) && isset($password))
		{
			$result = $this->authlib->CheckUserAuthByUsernamePassword($username, $password, true);
		}
		elseif (isset($code) || isset($email))
		{
			// If code and email are given then check authentication using them
			if (isset($code) && isset($email))
			{
				$result = $this->authlib->CheckUserAuthByCodeEmail($code, $email);
			}
			else // otherwise check authentication using only code
			{
				$result = $this->authlib->CheckUserAuthByCode($code);
			}
		}

		// If is a success and contains data
		if (hasData($result))
		{
			$httpCode = REST_Controller::HTTP_OK;
		}

		$this->response($result, $httpCode);
	}
}
