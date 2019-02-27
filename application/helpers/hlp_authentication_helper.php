<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------
// Functions needed to manage the user authentication
// ------------------------------------------------------------------------

/**
 * It calls the AuthLib, if the user is NOT logged then the login page is shown
 * If the user is alredy logged, then it is possible to access to the authentication object
 * that contains the username of the logged user
 *
 * @return string or null
 */
function getAuthUID()
{
	$ci =& get_instance(); // get CI instance
	$ci->load->library('AuthLib'); // load authentication library

	return ($ci->authlib->getAuthObj())->{AuthLib::AO_USERNAME};
}
