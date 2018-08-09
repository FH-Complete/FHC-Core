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

if (! defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------
// Functions needed to manage the user language preference
// ------------------------------------------------------------------------

/**
 * Function to retrive the language of the logged user
 * If is not possible to retrive it, then the default system language is returnd
 * If as parameter is given a valid language the it's returned useful to avoid
 * to write the same control structures for the language
 */
function getUserLanguage($language = null)
{
	if (!isEmptyString($language)) return $language;

	$ci =& get_instance(); // get CI instance

	// Use the default system language, if it's possible retrives the language for the logged user
	$language = DEFAULT_LANGUAGE;
	// Checks if the user is authenticated to retrive the users's language
	// NOTE: this helper could be called when the user is not logged in the system
	// 		so this is why is checked if the function getAuthUID exists
	if (function_exists('getAuthUID'))
	{
		// NOTE: Stores the loaded model with the alias PersonModelLanguage to avoid to overwrite
		// 		an already loaded PersonModel used somewhere else
		$ci->load->model('person/Person_model', 'PersonModelLanguage');

		$language = $ci->PersonModelLanguage->getLanguage(getAuthUID());
	}

	return $language;
}
