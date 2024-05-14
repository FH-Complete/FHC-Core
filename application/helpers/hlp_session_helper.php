<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

// -------------------------------------------------------------------------------------------------------
// Collection of functions to handle comfortably the php session.
// It works keeping a different session name for each functionality (ex. FilterWidget and NavigationWidget)
// It also starts the PHP session
// -------------------------------------------------------------------------------------------------------

session_start(); // starts the session (old good PHP session!)

/**
 * Returns the whole session by its name given as parameter
 * If it's not present the a null value is returned
 */
function getSession($sessionName)
{
	$session = null;

	// If it is present a session with the given name
	if (isset($_SESSION[$sessionName]))
	{
		$session = $_SESSION[$sessionName];
	}

	return $session;
}

/**
 * Returns one element specified by the paraemter $elementName, from the session specified by the parameters sessionName
 * If it's not present the a null value is returned
 */
function getSessionElement($sessionName, $elementName)
{
	$session = getSession($sessionName); // get the whole session with the given name

	if (isset($session[$elementName]))
	{
		return $session[$elementName];
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
 * Sets one element ($elementName) of the session specified by the parameters sessionName
 */
function setSessionElement($sessionName, $elementName, $value)
{
	// If is NOT already present into the session
	if (!isset($_SESSION[$sessionName])
		|| (isset($_SESSION[$sessionName]) && !is_array($_SESSION[$sessionName])))
	{
		$_SESSION[$sessionName] = array(); // then create it
	}

	$_SESSION[$sessionName][$elementName] = $value; // stores the single value
}

/**
 * Clean the whole session specified by the parameters sessionName
 */
function cleanSession($sessionName)
{
	// If it is present a session with the given name
	if (isset($_SESSION[$sessionName]))
	{
		unset($_SESSION[$sessionName]);
	}
}

/**
 * Clean one element ($elementName) of the session specified by the parameters sessionName
 */
function cleanSessionElement($sessionName, $elementName)
{
	if (isset($_SESSION[$sessionName][$elementName]))
	{
		unset($_SESSION[$sessionName][$elementName]);
	}
}
