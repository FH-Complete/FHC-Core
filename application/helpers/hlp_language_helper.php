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
// Functions needed to manage multi lingual contents
// ------------------------------------------------------------------------

/**
 * Function to retrieve the language of the logged user
 * If is not possible to retrieve it, then the default system language is returnd
 * If as parameter is given a valid language the it's returned useful to avoid
 * to write the same control structures for the language
 */
function getUserLanguage($language = null)
{
	if (!isEmptyString($language)) return $language;

	$ci =& get_instance(); // get CI instance

	// Use the default system language, if it's possible retrieves the language for the logged user
	$language = DEFAULT_LANGUAGE;
	// Checks if the user is authenticated to retrieve the users's language
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

/**
 * Function to retrieve a phrase from an array of phrases (same phrase in more languages) using the given language as parameter
 * The given $phraseLanguagesArray parameter contains more translations of the same phrase
 * $language parameter contains the language used to get the phrase
 * The first time this function is called an array, that has the language as its key and the language index as its value,
 * is retrived from database and then stored in the user session.
 * All subsequent calls retrieves this array from the session itself.
 */
function getPhraseByLanguage($phraseLanguagesArray, $language)
{
	$phrase = null;
	$ci =& get_instance(); // get CI instance

	// Try to get the language session
	$langArray = getSessionElement(LANG_SESSION_NAME, LANG_SESSION_INDEXES);
	if ($langArray == null) // If not already loaded in session
	{
		// Loads the Sprache_model to retrieve the language settings from the DB
		// NOTE: Stores the loaded model with the alias SpracheModelLanguage to avoid to overwrite
		// 		an already loaded SpracheModel used somewhere else
		$ci->load->model('system/Sprache_model', 'SpracheModelLanguage');

		// Add order clause by index and select only the sprache column
		$ci->SpracheModelLanguage->addOrder('index');
		$ci->SpracheModelLanguage->addSelect('sprache');

		// Retrieves from public.tbl_sprache
		$dbLanguages = $ci->SpracheModelLanguage->load();
		if (hasData($dbLanguages)) // If everything is ok and contains data
		{
			$index = 0; // Incremental integer
			$languageIndexes = array(); // Array that will contains languages and their indexes

			// Loops through database results
			foreach (getData($dbLanguages) as $dbLanguage)
			{
				$languageIndexes[$dbLanguage->sprache] = $index++; // set $languageIndexes array elements
			}
		}

		$langArray = $languageIndexes; // copy $languageIndexes to $langArray
		// Set session element $_SESSION['LANG']['LANG_INDEXES'] with $languageIndexes
		setSessionElement(LANG_SESSION_NAME, LANG_SESSION_INDEXES, $langArray);
	}

	// Checks to avoid ugly php error messages
	if (!isEmptyArray($phraseLanguagesArray) && !isEmptyArray($langArray)
		&& isset($langArray[$language]) && isset($phraseLanguagesArray[$langArray[$language]]))
	{
		// If everything is ok then set phrase
		$phrase = $phraseLanguagesArray[$langArray[$language]];
	}

	return $phrase;
}
