<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * NOTE: extends the FHC_Controller instead of the Auth_Controller because we want to login ;) otherwise loooooop!
 */
class Login extends FHC_Controller
{
	/**
	 *
	 */
	public function __construct()
    {
        parent::__construct();
    }

	/**
	 * Displays a login page with username and password
	 */
	public function usernamePassword()
	{
		$this->load->view('system/login/usernamePassword');
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
		if (isSuccess($login))
		{
			$this->outputJsonSuccess($this->authlib->getLandingPage()); // if login is success then retrieves the desired page
		}
		else
		{
			$this->outputJsonError(getCode($login)); // returns the error code
		}
	}

	/**
	 * Called with HTTP POST via ajax to login as another user specified by uid
	 */
	public function loginASByUid()
	{
		$uid = $this->input->get('uid');

		// With authentication -> you must be already logged to gain another identity
		$this->load->library('AuthLib');

		$loginAS = $this->authlib->loginASByUID($uid);
		$this->outputJson($loginAS); // returns the error code
	}

	/**
	 * Called with HTTP POST via ajax to login as another user specified by person id
	 */
	public function loginASByPersonId()
	{
		$person_id = $this->input->get('person_id');

		// With authentication -> you must be already logged to gain another identity
		$this->load->library('AuthLib');

		$loginAS = $this->authlib->loginASByPersonId($person_id);
		if (isSuccess($loginAS))
		{
			$this->outputJsonSuccess(true); // obtained!
		}
		else
		{
			$this->outputJsonSuccess(getCode($loginAS)); // returns the error code
		};
	}

	/**
	 * To login into the system with email and code as credentials
	 */
	public function emailCode()
	{
	}

	/**
	 * To login into the system using SSO
	 */
	public function sso()
	{
	}
}
