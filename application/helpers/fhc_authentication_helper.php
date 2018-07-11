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
if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once FHCPATH.'include/authentication.class.php';

/**
 * FHC-Auth Helpers
 *
 * @package		FH-Complete
 * @subpackage	Helpers
 * @category	Helpers
 * @author		FHC-Team
 * @link		http://fhcomplete.org/user_guide/helpers/fhcauth_helper.html
 */

// ------------------------------------------------------------------------

if ( ! function_exists('auth'))
{
	/**
	 * Auth Username, Password over FH-Complete
	 *
	 * @param	string	$username
	 * @param	string	$password
	 * @return	bool
	 */
	function auth($username, $password)
	{
		$auth = new authentication();
		if ($auth->checkpassword($username, $password))
		{
			echo 'Auth-Method-False';
			return true;
		}
		else
		{
			echo 'Auth-Method-False';
			return false;
		}
	}
}

/**
 * Look if User is logged in and return uid
 * it tries to work always with CI session
 * Otherwise return false
 *
 * @return	string or (bool)false
*/
function getAuthUID()
{
	$ci =& get_instance(); // get CI instance
	$ci->load->library('AuthLib'); // load authentication library

	return $ci->authlib->getUser();
}
