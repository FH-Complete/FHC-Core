<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * NOTE: extends the FHC_Controller instead of the Auth_Controller because we want to login ;)
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
	 * To login into the system with username and password as credentials
	 */
	public function usernamePassword()
	{
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
