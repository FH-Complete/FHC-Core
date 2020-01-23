<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class PhrasesLib
{
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

		$this->_ci->load->model('system/Phrase_model', 'PhraseModel');
		$this->_ci->load->model('system/Phrasentext_model', 'PhrasentextModel');

		// Workaround to use more parameters in the construct since PHP doesn't support many constructors
		$this->_extend_construct(func_get_args());
    }

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

   	/**
     * getPhrase() - loads a specific Phrase
     */
    public function getPhrase($phrase_id)
    {
        if (isEmptyString($phrase_id)) return error(MSG_ERR_INVALID_MSG_ID);

        return $this->_ci->PhraseModel->load($phrase_id);
    }

    /**
     * getSubMessages() - will return all Messages subordinated from a specified message.
     */
    public function getPhraseByApp($app = null)
    {
	    return $this->_ci->PhraseModel->loadWhere(array('app' => $app));
    }

    /**
     * getPhraseInhalt
     */
	public function getPhraseInhalt($phrase_id)
    {
        if (isEmptyString($phrase_id)) return error(MSG_ERR_INVALID_MSG_ID);

        return $this->_ci->PhrasentextModel->loadWhere(array('phrase_id' => $phrase_id));
    }

    /**
     * delPhrasentext
     */
    public function delPhrasentext($phrasentext_id)
    {
        if (isEmptyString($phrasentext_id)) return error(MSG_ERR_INVALID_MSG_ID);

        return $this->_ci->PhrasentextModel->delete(array('phrasentext_id' => $phrasentext_id));
    }

	/**
     * savePhrase() - will save a spezific Phrase.
     */
    public function savePhrase($phrase_id, $data)
    {
        if (isEmptyString($data)) return error(MSG_ERR_INVALID_MSG_ID);

        return $this->_ci->PhraseModel->update($phrase_id, $data);
    }

	/**
     * getVorlagetextByVorlage() - will load tbl_vorlagestudiengang for a spezific Template.
     */
    public function getPhrasentextById($phrasentext_id)
	{
        if (isEmptyString($phrasentext_id)) return error('Not a valid phrasentext_id');

        return $this->_ci->PhrasentextModel->load($phrasentext_id);
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
				$textileParser = new \Netcarver\Textile\Parser();

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
     * insertPhraseinhalt() - will load tbl_vorlagestudiengang for a spezific Template.
     */
    public function insertPhraseinhalt($data)
	{
        return $this->_ci->PhrasentextModel->insert($data);
    }

	/**
     * getVorlagetextById() - will load tbl_vorlagestudiengang for a spezific Template.
     */
    public function getVorlagetextById($vorlagestudiengang_id)
	{
        return $this->_ci->VorlageStudiengangModel->load($vorlagestudiengang_id);
    }

	/**
     * saveVorlagetext() - will load tbl_vorlagestudiengang for a spezific Template.
     */
    public function updatePhraseInhalt($phrasentext_id, $data)
	{
        return $this->_ci->PhrasentextModel->update($phrasentext_id, $data);
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
		if (is_array($this->_phrases))
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
	private function _extend_construct($params)
	{
		// Checks if the $params is an array with at least one element
		if (is_array($params) && count($params) > 0)
		{
			$parameters = $params[0];	// temporary variable
			$isIndexArray = false;		//flag for indexed array

			// If there are parameters
			if (is_array($parameters) && count($parameters) > 0)
			{
				$categories = $parameters[0]; // categories is always the first parameter
				if (!is_array($categories)) // if it is not an array, then convert into one
				{
					$categories = array($categories);
				}

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
			// get array with phrasentexte in the default language
			$defaultPhrases = null;
			if ($isIndexArray)
			{
				$defaultPhrases = $this->_ci->PhraseModel->getPhrasesByCategoryAndLanguage($categories, DEFAULT_LANGUAGE);
			}
			else
			{
				$defaultPhrases = $this->_ci->PhraseModel->getPhrasesByCategoryAndPhrasesAndLanguage($categories, DEFAULT_LANGUAGE);
			}

			// combine array with phrasentexte in users language and in default language
			// (default used if phrasentext in users language is null or not set)
			if (hasData($phrases) && hasData($defaultPhrases))
			{
				// loop through phrases in default language
				foreach ($defaultPhrases->retval as $defaultPhrase)
				{
					$found = false;	// flag for found phrase

					// loop through phrases in users language
					foreach ($phrases->retval as $phrase)
					{
						// if same phrase and category found and text is not null
						// use phrase in users language
						if ($phrase->phrase == $defaultPhrase->phrase
							&& $phrase->category == $defaultPhrase->category
							&& !is_null($phrase->text))
						{
							$found = true;
							break;
						}
					}

					// otherwise use phrase in default language
					if (!$found)
					{
						array_push($phrases->retval, $defaultPhrase);
					}
				}
			}
			elseif (hasData($defaultPhrases))
			{
				$phrases = $defaultPhrases;
			}
		}

		// If there are phrases loaded then store them in the property _phrases
		if (hasData($phrases))
		{
			$this->_phrases = $phrases->retval;
		}
	}

	/**
	 * Returns the property _phrases JSON encoded
	 * @return json encoded property _phrases
	 */
	public function getJSON()
	{
		return json_encode($this->_phrases);
	}
}
