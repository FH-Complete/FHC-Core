<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

class CheckUserAuth extends REST_Controller
{
	/**
	 * Course API constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		// Loads helper message to manage returning messages
		// NOTE: loaded here because it does not extend the APIv1_Controller
		$this->load->helper('hlp_message');
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
			$result = $this->authlib->checkUserAuthByUsernamePassword($username, $password);

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
			$result = $this->authlib->checkUserAuthByUsernamePassword($username, $password, true);
		}
		elseif (isset($code) || isset($email))
		{
			// If code and email are given then check authentication using them
			if (isset($code) && isset($email))
			{
				$result = $this->authlib->checkUserAuthByCodeEmail($code, $email);
			}
			else // otherwise check authentication using only code
			{
				$result = $this->authlib->checkUserAuthByCode($code);
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
