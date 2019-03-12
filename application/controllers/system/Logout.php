<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * NOTE: extends the FHC_Controller instead of the Auth_Controller because it is NOT neeaded to checks permissions to logout
 */
class Logout extends FHC_Controller
{
	/**
	 *
	 */
	public function __construct()
    {
        parent::__construct();

		// Loads AuthLib to check if the user is authenticated and to use the lib logic
		$this->load->library('AuthLib');
    }

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Logout the current logged user
	 */
	public function index()
	{
		$this->authlib->logout();
	}
}
