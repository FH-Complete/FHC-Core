<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \Netcarver\Textile\Parser as NTParser;

class PhrasesLib
{
	// Directory name where all the category files are
	const CORE_PHRASES_DIRECTORY = 'phrases/';

	// Who adds phrases into the database
	const INSERT_BY = 'PhrasesManager';

	// Array elements names
	const APP = 'app';
	const CATEGORY = 'category';
	const PHRASE = 'phrase';
	const SPRACHE = 'sprache';
	const TEXT = 'text';
	const DESCRIPTION = 'description';

	private $_ci; // Code igniter instance
	private $_phrases; // Contains the retrieved phrases

	/**
	 * Loads parser library
	 */
	public function __construct()
	{
		$this->_ci =& get_instance();

		$this->_phrases = null; // set the property _phrases as null by default

		// CI parser
		$this->_ci->load->library('parser');
		// Loads EPrintfLib
		$this->_ci->load->library('EPrintfLib');

		// Loads the PhraseModel
		$this->_ci->load->model('system/Phrase_model', 'PhraseModel');
		// Loads the PhrasentextModel
		$this->_ci->load->model('system/Phrasentext_model', 'PhrasentextModel');

		// Workaround to use more parameters in the construct since PHP doesn't support many constructors
		$this->_extendConstruct(func_get_args());
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Return the phrases in JSON format
	 */
	public function toJSON()
	{
		return json_encode($this->_phrases);
	}

	/**
	 * getPhrase() - loads a specific Phrase
	 */
	public function getPhrase($phrase_id)
	{
		if (isEmptyString($phrase_id)) return error(MSG_ERR_INVALID_MSG_ID);

		return $this->_ci->PhraseModel->load($phrase_id);
	}

	/**
	 * getPhrases() - Retrieves phrases from the DB
	 * The given parameter are the same needed to read from the table system.tb_phrase
	 */
	public function getPhrases($app, $sprache, $phrase = null, $orgeinheit_kurzbz = null, $orgform_kurzbz = null, $blockTags = null)
	{
		if (isset($app) && isset($sprache))
		{
			$result = $this->_ci->PhraseModel->getPhrases($app, $sprache, $phrase, $orgeinheit_kurzbz, $orgform_kurzbz);

			if (hasData($result))
			{
				// Textile parser
				$textileParser = new NTParser();

				for ($i = 0; $i < count($result->retval); $i++)
				{
					// If no <p> tags required
					if ($blockTags == 'no')
					{
						$tmpText = $textileParser->parse($result->retval[$i]->text); // Parse

						// Removes tags <p> and </p> from the beginning and from the end of the string if they are present
						// NOTE: Those tags are usually, but not always, added by the textile parser
						if (strlen($tmpText) >= 7)
						{
							if (substr($tmpText, 0, 3) == '<p>')
							{
								$tmpText = substr($tmpText, 3, strlen($tmpText));
							}
							if (substr($tmpText, -4, strlen($tmpText)) == '</p>')
							{
								$tmpText = substr($tmpText, 0, strlen($tmpText) - 4);
							}
						}

						$result->retval[$i]->text = $tmpText;
					}
					else
					{
						$result->retval[$i]->text = $textileParser->parse($result->retval[$i]->text);
					}
				}
			}
		}
		else
		{
			$result = error('app and sprache parameters are required');
		}

		return $result;
	}

	/**
	 * Retrieves a phrases from the the property _phrases with the given parameters
	 * It also replace parameters inside the phrase if they are provided
	 * @param string $category Category name which is used to categorize the phrase.
	 * @param string $phrase Phrase name.
	 * @param array $parameters Array of String var(s) to be set into phrases' placeholder values (order matters).
	 * @return string Phrase text
	 */
	public function t($category, $phrase, $parameters = array(), $orgeinheit_kurzbz = null, $orgform_kurzbz = null)
	{
		// If the property _phrases is populated
		if (!isEmptyArray($this->_phrases))
		{
			// Loops through the _phrases property
			for ($i = 0; $i < count($this->_phrases); $i++)
			{
				$_phrase = $this->_phrases[$i]; // single phrase

				// If the single phrase match the given parameters and is not an empty string
				if ($_phrase->category == $category
					&& $_phrase->phrase == $phrase
					&& $_phrase->orgeinheit_kurzbz == $orgeinheit_kurzbz
					&& $_phrase->orgform_kurzbz == $orgform_kurzbz
					&& !isEmptyString($_phrase->text))
				{
					if (!is_array($parameters)) $parameters = array(); // if params is not an array

					return parseText($_phrase->text, $parameters); // parsing
				}
			}
		}

		// If a valid phrase is not found
		return '<< PHRASE '.$phrase.' >>';
	}

	/**
	 * Install phrases from the core
	 */
	public function installFromCore()
	{
		$this->_installPhrases(APPPATH.self::CORE_PHRASES_DIRECTORY);
	}

	/**
	 * Install phrases from the given path
	 */
	public function installFrom($phrasesDirectory)
	{
		$this->_installPhrases($phrasesDirectory);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Extends the functionalities of the constructor of this class
	 * This is a workaround to use more parameters in the construct since PHP doesn't support many constructors
	 * @param (array) $params Array of categories and (optional) language.
	 * categories:
	 *		- could be a string or an array of strings. These are the categories used to load phrases
	 *		- could be an array of categories, and for each category there is an array of phrases
	 * language: optional parameter must be a string. It's used to load phrases
	 */
	private function _extendConstruct($params)
	{
		// Checks if the $params is an array with at least one element
		if (!isEmptyArray($params))
		{
			$parameters = $params[0];	// temporary variable

			// If there are parameters
			if (!isEmptyArray($parameters))
			{
				$categories = $parameters[0]; // categories is always the first parameter
				// If it is not an array, then convert into one
				if (!is_array($categories)) $categories = array($categories);

				// Retrieves the language of the logged user
				$language = getUserLanguage(count($parameters) == 2 ? $parameters[1] : null);

				// If only categories is not an empty array then loads phrases
				if (count($categories) > 0) $this->_setPhrases($categories, $language);
			}
		}
	}

	/**
	 * Retrieves phrases in the users language.
	 * If a phrase is not set in the users language it will be retrieved in the default language.
	 * Stores phrases-array in property $_phrases.
	 * @param array $categories Could be an:
	 *		- indexed array: string or an array of strings. These are the categories used to load phrases.
	 *		- associative array: of categories, and for each category there is an array of phrases.
	 * @param string User's language or default language.
	 */
	private function _setPhrases($categories, $language)
	{
		$phrases = null;
		// Checks if categories is associative or indexed array
		if (ctype_digit(implode('', array_keys($categories))))
		{
			// is indexed array -> Loads phrases
			$isIndexArray = true;
			$phrases = $this->_ci->PhraseModel->getPhrasesByCategoryAndLanguage($categories, $language);
		}
		else
		{
			// is assoc array -> Loads specific phrasentexte by category and phrases
			$isIndexArray = false;
			$phrases = $this->_ci->PhraseModel->getPhrasesByCategoryAndPhrasesAndLanguage($categories, $language);
		}

		// If language is not default language and phrasentext is null -> fallback to default language
		if ($language != DEFAULT_LANGUAGE)
		{
			// Get array with phrasentexte in the default language
			$defaultPhrases = null;
			if ($isIndexArray)
			{
				$defaultPhrases = $this->_ci->PhraseModel->getPhrasesByCategoryAndLanguage($categories, DEFAULT_LANGUAGE);
			}
			else
			{
				$defaultPhrases = $this->_ci->PhraseModel->getPhrasesByCategoryAndPhrasesAndLanguage($categories, DEFAULT_LANGUAGE);
			}

			// Combine array with phrasentexte in users language and in default language
			// (default used if phrasentext in users language is null or not set)
			if (hasData($phrases) && hasData($defaultPhrases))
			{
				// Loop through phrases in default language
				foreach ($defaultPhrases->retval as $defaultPhrase)
				{
					$found = false;	// flag for found phrase

					// Loop through phrases in users language
					foreach ($phrases->retval as $phrase)
					{
						// If same phrase and category found and text is not null
						// use phrase in users language
						if ($phrase->phrase == $defaultPhrase->phrase
							&& $phrase->category == $defaultPhrase->category
							&& !is_null($phrase->text))
						{
							$found = true;
							break;
						}
					}

					// Otherwise use phrase in default language
					if (!$found) array_push($phrases->retval, $defaultPhrase);
				}
			}
			// Otherwise if only defaultPhrases have data
			elseif (hasData($defaultPhrases))
			{
				$phrases = $defaultPhrases;
			}
		}

		// If there are phrases loaded then store them in the property _phrases
		if (hasData($phrases)) $this->_phrases = $phrases->retval;
	}

	/**
	 * Install phrases from the given directory
	 */
	private function _installPhrases($phrasesDirectory)
	{
		$this->_ci->eprintflib->printInfo('------------------------------------------------------------------------------------------');
		$this->_ci->eprintflib->printInfo('Phrases installation started from: '.$phrasesDirectory);

		// If the given directory name does not exist
		if (!is_dir($phrasesDirectory))
		{
			$this->_ci->eprintflib->printError('The directory '.$phrasesDirectory.' does not exist');
		}
		else // otherwise install the phrases from the given directory
		{
			// Get the list of category files from the given directory
			$phrasesCategoryFiles = scandir($phrasesDirectory);

			if ($phrasesCategoryFiles == false)
			{
				$this->_ci->eprintflib->printError('An error occurred while trying to access to the given directory: '.$phrasesDirectory);
			}
			else
			{
				// If no files are inside the given directory
				if (count($phrasesCategoryFiles) == 2)
				{
					$this->_ci->eprintflib->printInfo('No phrases files are inside the given directory: '.$phrasesDirectory);
				}

				// For each file in this directory that represents a phrases category
				foreach ($phrasesCategoryFiles as $phrasesCategoryFile)
				{
					// Gets the infos about the file
					$pathInfo = pathinfo($phrasesDirectory.$phrasesCategoryFile);

					// Skip the upper directory, the same directory and files that are not a php file
					if ($phrasesCategoryFile != '.'
						&& $phrasesCategoryFile != '..'
						&& $pathInfo['extension'] == 'php')
					{
						$phrases = null; // define the variable

						// Include the php file that contains phrases for that category
						require_once($phrasesDirectory.$phrasesCategoryFile);

						// If this file contains an array called phrases
						if (isset($phrases) && is_array($phrases))
						{
							$addPhrases = $this->_addPhrases($phrases); // add them to the database

							// If a blocking error occurred then print an error and stop the execution
							if (isError($addPhrases))
							{
								$this->_ci->eprintflib->printError(getError($addPhrases));
								break;
							}
						}
						else // otherwise print an error and continue with the next file
						{
							$this->_ci->eprintflib->printInfo(
								'The file '.$phrasesDirectory.$phrasesCategoryFile.' does not contain an array called "phrases"'
							);
						}

						// Clean for the next file
						unset($phrases);
					}
				}
			}
		}

		$this->_ci->eprintflib->printInfo('Phrases installation ended');
		$this->_ci->eprintflib->printInfo('------------------------------------------------------------------------------------------');
	}

	/**
	 * Add new phrases to the database
	 */
	private function _addPhrases($phrases)
	{
		// For eache given phrase
		foreach ($phrases as $phrase)
		{
			$phrase_id = null; // The id of the new/existing phrase

			// Checks the mandatory fields, if one of them is not valid continue with the next phrase
			if (!$this->_isValidElement($phrase, self::APP)) continue;
			if (!$this->_isValidElement($phrase, self::CATEGORY)) continue;
			if (!$this->_isValidElement($phrase, self::PHRASE)) continue;

			// Checks if the phrase already exists in the database
			$phraseResult = $this->_ci->PhraseModel->loadWhere(
				array(
					'app' => $phrase[self::APP],
					'category' => $phrase[self::CATEGORY],
					'phrase' => $phrase[self::PHRASE]
				)
			);

			// If an error occurred then return the error itself
			if (isError($phraseResult)) return $phraseResult;

			// If no phrase has been found
			if (!hasData($phraseResult))
			{
				// Then add the phrase to the database
				$phraseInsertResult = $this->_ci->PhraseModel->insert(
					array(
						'app' => $phrase[self::APP],
						'category' => $phrase[self::CATEGORY],
						'phrase' => $phrase[self::PHRASE],
						'insertamum' => 'NOW()',
						'insertvon' => self::INSERT_BY
					)
				);

				// If an error occurred then return the error itself
				if (isError($phraseInsertResult)) return $phraseInsertResult;

				$phrase_id = getData($phraseInsertResult); // the phrase_id of the new added phrase

				// Prints info about the new added phrase
				$this->_ci->eprintflib->printMessage(
					sprintf(
						'A new phrase has been added into the database: '.
						'phrase_id => %s | app => %s | category => %s | phrase => %s',
						$phrase_id,
						$phrase[self::APP],
						$phrase[self::CATEGORY],
						$phrase[self::PHRASE]
					)
				);
			}
			else // otherwise if the phrase already exists in the database
			{
				$phrase_id = getData($phraseResult)[0]->phrase_id; // gets the phrase_id
			}

			// If not a valid phrase_id
			if ($phrase_id == null) return error('Not a valid phrase id');

			// For each phrase text, one text for each language
			foreach ($phrase['phrases'] as $phraseText)
			{
				// Checks the mandatory fields, if one of them is not valid continue with the next phrase text
				if (!$this->_isValidElement($phraseText, self::SPRACHE)) continue;
				if (!$this->_isValidElement($phraseText, self::TEXT)) continue;

				// Set the not optional fields if they have not been set
				if (!isset($phraseText[self::DESCRIPTION])) $phraseText[self::DESCRIPTION] = null;

				// Checks if the phrase already exists in the database
				$phraseTextResult = $this->_ci->PhrasentextModel->loadWhere(
					array(
						'phrase_id' => $phrase_id,
						'sprache' => $phraseText[self::SPRACHE]
					)
				);

				// If an error occurred then return the error itself
				if (isError($phraseTextResult)) return $phraseTextResult;

				// If no text for the phrase was found
				if (!hasData($phraseTextResult))
				{
					// Then add the text phrase to the database
					$phraseTextInsertResult = $this->_ci->PhrasentextModel->insert(
						array(
							'phrase_id' => $phrase_id,
							'sprache' => $phraseText[self::SPRACHE],
							'text' => $phraseText[self::TEXT],
							'description' => $phraseText[self::DESCRIPTION],
							'insertvon' => self::INSERT_BY,
							'insertamum' => 'NOW()',
							'orgeinheit_kurzbz' => null,
							'orgform_kurzbz' => null
						)
					);

					// If an error occurred then return the error itself
					if (isError($phraseTextInsertResult)) return $phraseTextInsertResult;

					// Prints info about the new added text phrase
					$this->_ci->eprintflib->printMessage(
						sprintf(
							'A new text has been added into the database: '.
							'phrase_id => %s | sprache => %s | text => %s | description => %s',
							$phrase_id,
							$phraseText[self::SPRACHE],
							substr($phraseText[self::TEXT], 0, 42).'...',
							substr($phraseText[self::DESCRIPTION], 0, 42).'...'
						)
					);
				}
			}
		}

		// If here then no blocking errors occurred
		return success();
	}

	/**
	 * Checks if the given array element exists in the given array and if it is a valid string and then returns true
	 * Otherwise prints an info and then returns false
	 */
	private function _isValidElement($array, $elementName)
	{
		// If a not valid text is set
		if ((isset($array[$elementName]) && isEmptyString($array[$elementName])) || !isset($array[$elementName]))
		{
			$this->_ci->eprintflib->printInfo('Not a valid element "'.$elementName.'":');
			var_dump($array); // KEEP IT!!!
			$this->_ci->eprintflib->printEOL();
			return false;
		}

		return true;
	}
}

