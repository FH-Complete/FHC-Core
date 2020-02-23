<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------------------------------
// Functions needed to manage the user authentication
// NOTE: the following functions do NOT prompt a login page if the user is NOT logged in
// -----------------------------------------------------------------------------------------------------

/**
 * If the user is NOT logged then a null value is returned.
 * If the user is alredy logged, then it is possible to access to the authentication object
 * that contains the person_id of the logged user
 * NOTE: if a user is logged then a person_id is always present!
 */
function getAuthPersonId()
{
	$ci =& get_instance(); // get CI instance

	return isLogged() ? ($ci->authlib->getAuthObj())->{AuthLib::AO_PERSON_ID} : null;
}

/**
 * If the user is NOT logged then a null value is returned.
 * If the user is alredy logged, then it is possible to access to the authentication object
 * that contains the username of the logged user
 * NOTE: if the user is logged with a "foreign" method (ex. Bewerbungstool),
 *		then it is possible that the username is null!
 */
function getAuthUID()
{
	$ci =& get_instance(); // get CI instance

	return isLogged() ? ($ci->authlib->getAuthObj())->{AuthLib::AO_USERNAME} : null;
}

/**
 * If the user is NOT logged then a null value is returned.
 * If the user is alredy logged, then it is possible to access to the authentication object
 * that contains the firstname of the logged user
 * NOTE: if the user is logged with a "foreign" method (ex. Bewerbungstool),
 *		then it is possible that the firstname is null!
 */
function getAuthFirstname()
{
	$ci =& get_instance(); // get CI instance

	return isLogged() ? ($ci->authlib->getAuthObj())->{AuthLib::AO_NAME} : null;
}

/**
 * If the user is NOT logged then a null value is returned.
 * If the user is alredy logged, then it is possible to access to the authentication object
 * that contains the surname of the logged user
 * NOTE: if the user is logged with a "foreign" method (ex. Bewerbungstool),
 *		then it is possible that the surname is null!
 */
function getAuthSurname()
{
	$ci =& get_instance(); // get CI instance

	return isLogged() ? ($ci->authlib->getAuthObj())->{AuthLib::AO_SURNAME} : null;
}
