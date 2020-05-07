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
 * If is not possible to retrieve it, then the default system language is returned
 * NOTE: If the given parameter is a valid language then it is returned
 *			It is useful to avoid to write a lot of "if else" structures
 */
function getUserLanguage($language = null)
{
	// If the given parameter is a valid language then return it
	if (!isEmptyString($language)) return $language;

	// Use the default system language as fallback
	$language = DEFAULT_LANGUAGE;

	// If the language is present in the session and it is valid
	if (isset($_SESSION[LANG_SESSION_CURRENT_LANGUAGE]) && !isEmptyString($_SESSION[LANG_SESSION_CURRENT_LANGUAGE]))
	{
		$language = $_SESSION[LANG_SESSION_CURRENT_LANGUAGE]; // then use it
	}
	// Else if the language is present in the cookie and it is valid
	elseif (isset($_COOKIE[LANG_SESSION_CURRENT_LANGUAGE]) && !isEmptyString($_COOKIE[LANG_SESSION_CURRENT_LANGUAGE]))
	{
		$language = $_COOKIE[LANG_SESSION_CURRENT_LANGUAGE];
	}
	
	// Otherwise checks if the user is authenticated to retrieve the users's language
	// NOTE: this helper could be called when the user is NOT logged in the system
	// 		therefore is checked if the user is logged
	elseif (isLogged())
	{
		$ci =& get_instance(); // get CI instance

		// NOTE: Stores the loaded model with the alias PersonModelLanguage to avoid to overwrite
		// 		an already loaded PersonModel used somewhere else
		$ci->load->model('person/Person_model', 'PersonModelLanguage');

		// Retrieves language/s for the logged user
		$languagesDB = $ci->PersonModelLanguage->getLanguage(getAuthUID());
		if (hasData($languagesDB))
		{
			// Looks for the first valid language
			foreach (getData($languagesDB) as $languageDB)
			{
				if (!isEmptyString($languageDB->sprache))
				{
					$language = $languageDB->sprache;
					break;
				}
			}
		}
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
		// Retrieves active languages
		$dbLanguages = getDBActiveLanguages();
		if (hasData($dbLanguages)) // If everything is ok and contains data
		{
			$index = 0; // Incremental integer
			$langArray = array(); // Array that will contains languages and their indexes

			// Loops through database results
			foreach (getData($dbLanguages) as $dbLanguage)
			{
				$langArray[$dbLanguage->sprache] = $index++; // set $languageIndexes array elements
			}
		}

		// Set session element $_SESSION['LANG']['LANG_INDEXES'] with $langArray
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

/**
 * Tries to load active languages from session, if not present then loads them from database and stores them in session
 */
function getActiveLanguages()
{
	$languagesArray = getSessionElement(LANG_SESSION_NAME, LANG_SESSION_ACTIVE_LANGUAGES);
	if ($languagesArray == null)
	{
		$languagesArray = array();

		// Retrieves from public.tbl_sprache
		$dbLanguages = getDBActiveLanguages();
		if (hasData($dbLanguages))
		{
			// Loops through database results
			foreach (getData($dbLanguages) as $dbLanguage)
			{
				$languagesArray[$dbLanguage->sprache] = $dbLanguage->bezeichnung; // set $languageIndexes array elements
			}
		}

		// Set session element $_SESSION['LANG']['LANG_SESSION_ACTIVE_LANGUAGES'] with $languagesArray
		setSessionElement(LANG_SESSION_NAME, LANG_SESSION_ACTIVE_LANGUAGES, $languagesArray);
	}

	return $languagesArray;
}

/**
 * Loads active languages from database
 */
function getDBActiveLanguages()
{
	$ci =& get_instance(); // get CI instance

	// Loads the Sprache_model to retrieve the language settings from the DB
	// NOTE: Stores the loaded model with the alias SpracheModelLanguage to avoid to overwrite
	// 		an already loaded SpracheModel used somewhere else
	$ci->load->model('system/Sprache_model', 'SpracheModelLanguage');

	// Add order clause by index and select only the sprache column
	$ci->SpracheModelLanguage->addOrder('index');
	$ci->SpracheModelLanguage->addSelect('sprache, bezeichnung');

	// Retrieves from public.tbl_sprache
	return $ci->SpracheModelLanguage->loadWhere(array('content' => true));
}

/**
 * Sets the current language to render the GUI in session
 */
function setUserLanguage($language)
{
	$languageValid = false;

	// Checks if the given language is valid (present between active languages)
	foreach (getActiveLanguages() as $languageName => $languageTranslation)
	{
		if ($language == $languageName)
		{
			$languageValid = true;
			break;
		}
	}

	if ($languageValid) // if the provided language is valid
	{
		$_SESSION[LANG_SESSION_CURRENT_LANGUAGE] = $language; // stores it in session

		return success('Language successfully changed'); // return success!!
	}

	return error('The given language is not valid'); // return an error
}
