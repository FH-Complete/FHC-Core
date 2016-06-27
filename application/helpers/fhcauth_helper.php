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
require_once FCPATH.'include/authentication.class.php';

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
	$uid = false;
	$ci =& get_instance(); // get CI instance
	$ci->load->library('session'); // load session library

	// If uid hasn't never been set and is present in CI session
	if ($uid === false && isset($ci->session->uid))
	{
		$uid = $ci->session->uid;
	}
	else
	{
		// Try to check if uid is stored elsewhere
		if (isset($_SERVER['PHP_AUTH_USER']))
		{
			$uid = $_SERVER['PHP_AUTH_USER'];
		}
		else if (isset($_SESSION['uid']))
		{
			$uid = $_SESSION['uid'];
		}
		// Workaround for a strange behavior
		// Sometimes $_SERVER['PHP_AUTH_USER'] is not set here, but is set when
		// used by authentication object
		else
		{
			$auth = new authentication();
			$uid = $auth->getUser();
		}
	}

	// If uid is set and uid in CI session is not set
	if ($uid !== false && !isset($ci->session->uid))
	{
		$ci->session->uid = $uid;
	}
	
	return $uid;
}