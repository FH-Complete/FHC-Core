<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

class CheckUserAuth extends RESTFul_Controller
{
	/**
	 * Course API constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		// Loads helper message to manage returning messages
		// NOTE: loaded here because it does not extend the API_Controller
		$this->load->helper('hlp_return_object');
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
}
