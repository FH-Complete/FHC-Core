<?php

/**
 * FH-Complete
 *
 * @package		FHC-Helper
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016 fhcomplete.org
 * @license		GPLv3
 * @since		Version 1.0.0
 */

/**
 * Message Helper
 *
 * @subpackage	Helpers
 * @category	Helpers
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

// -------------------------------------------------------------------------------------------------------
// Collection of functions to handle comfortably the php session.
// It works keeping a different session name for each functionality (ex. FilterWidget and NavigationWidget)
// -------------------------------------------------------------------------------------------------------

/**
 * Returns the whole session by its name given as parameter
 * If it's not present the a null value is returned
 */
function getSession($sessionName)
{
	$session = null;

	// If it is present a session for this filter
	if (isset($_SESSION[$sessionName]))
	{
		$session = $_SESSION[$sessionName];
	}

	return $session;
}

/**
 * Returns one element specified by the paraemter name, from the session specified by the parameters sessionName
 * If it's not present the a null value is returned
 */
function getElementSession($sessionName, $name)
{
	$session = getSession($sessionName); // get the whole session for this filter

	if (isset($session[$name]))
	{
		return $session[$name];
	}

	return null;
}

/**
 * Sets the whole session specified by the parameters sessionName
 */
function setSession($sessionName, $data)
{
	// If is NOT already present into the session
	if (!isset($_SESSION[$sessionName])
		|| (isset($_SESSION[$sessionName]) && !is_array($_SESSION[$sessionName])))
	{
		$_SESSION[$sessionName] = array(); // then create it
	}

	$_SESSION[$sessionName] = $data; // stores data
}

/**
 * Sets one element of the session specified by the parameters sessionName
 */
function setElementSession($sessionName, $name, $value)
{
	// If is NOT already present into the session
	if (!isset($_SESSION[$sessionName])
		|| (isset($_SESSION[$sessionName]) && !is_array($_SESSION[$sessionName])))
	{
		$_SESSION[$sessionName] = array(); // then create it
	}

	$_SESSION[$sessionName][$name] = $value; // stores the single value
}
