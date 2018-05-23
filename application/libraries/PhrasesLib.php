<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class PhrasesLib
{
	private $_ci; // Code igniter instance
	private $_phrases; // Contains the retrived phrases

	/**
	 * Loads parser library
	 */
    public function __construct()
    {
		$this->_ci =& get_instance();

		// CI parser
		$this->_ci->load->library('parser');

		$this->_ci->load->model('system/Phrase_model', 'PhraseModel');
		$this->_ci->load->model('system/Phrasentext_model', 'PhrasentextModel');

		// Loads helper message to manage returning messages
		$this->_ci->load->helper('message');

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
        if (empty($phrase_id)) return error(MSG_ERR_INVALID_MSG_ID);

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
        if (empty($phrase_id)) return error(MSG_ERR_INVALID_MSG_ID);

        return $this->_ci->PhrasentextModel->loadWhere(array('phrase_id' => $phrase_id));
    }

    /**
     * delPhrasentext
     */
    public function delPhrasentext($phrasentext_id)
    {
        if (empty($phrasentext_id)) return error(MSG_ERR_INVALID_MSG_ID);

        return $this->_ci->PhrasentextModel->delete(array('phrasentext_id' => $phrasentext_id));
    }

	/**
     * savePhrase() - will save a spezific Phrase.
     */
    public function savePhrase($phrase_id, $data)
    {
        if (empty($data)) return error(MSG_ERR_INVALID_MSG_ID);

        return $this->_ci->PhraseModel->update($phrase_id, $data);
    }


	/**
     * getVorlagetextByVorlage() - will load tbl_vorlagestudiengang for a spezific Template.
     */
    public function getPhrasentextById($phrasentext_id)
	{
        if (empty($phrasentext_id))
        	return error($this->_ci->lang->line('fhc_'.FHC_INVALIDID, false));

        return $this->_ci->PhrasentextModel->load($phrasentext_id);
    }

	/**
     * getPhrases() - Retrives phrases from the DB
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
						$tmpText = $textileParser->textileThis($result->retval[$i]->text); // Parse

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
						$result->retval[$i]->text = $textileParser->textileThis($result->retval[$i]->text);
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
     * parseVorlagetext() - will parse a Vorlagetext.
     */
    public function parseVorlagetext($text, $data = array())
	{
        if (empty($text))
        	return error($this->_ci->lang->line('fhc_'.FHC_INVALIDID, false));

		return $this->_ci->parser->parse_string($text, $data, true);
    }

	/**
	 *
	 */
	public function t($category, $phrase, $parameters = array(), $orgeinheit_kurzbz = null, $orgform_kurzbz = null)
	{
		if (isset($this->_phrases) && is_array($this->_phrases))
		{
			for ($i = 0; $i < count($this->_phrases); $i++)
			{
				
				$_phrase = $this->_phrases[$i];
									
				if ($_phrase->category == $category
					&& $_phrase->phrase == $phrase
					&& $_phrase->orgeinheit_kurzbz == $orgeinheit_kurzbz
					&& $_phrase->orgform_kurzbz== $orgform_kurzbz
					&& (!empty($_phrase->text)))
					{
						if ($parameters == null) 
							$parameters = array();
						
						return $this->_ci->parser->parse_string($_phrase->text, $parameters, true);	
					}			
			}
			
			//fallback 1: if phrase not found in phrases-array, try with default language
			$default_language = DEFAULT_LANGUAGE;
			$categories = $this->_ci->PhraseModel->getCategories();
			
			if (hasData($categories))
			{
				$categories = $categories->retval;
				foreach($categories as $cat)
					$all_categories[] = $cat->category;
			}
			
			$phrases = $this->_ci->PhraseModel->getPhrasesByCategoryAndLanguage($all_categories, $default_language);
			
			if (hasData($phrases))
			{
				$default_phrases = $phrases->retval;
			}

			if (isset($default_phrases) && is_array($default_phrases))
			{
				for ($i = 0; $i < count($default_phrases); $i++)
				{
					$_phrase = $default_phrases[$i];
//									var_dump($_phrase);
									
//									echo $phrase . "<br>";
//									echo $_phrase->phrase . "<br><br>";

					if ($_phrase->category == $category
						&& $_phrase->phrase == $phrase
						&& $_phrase->orgeinheit_kurzbz == $orgeinheit_kurzbz
						&& $_phrase->orgform_kurzbz== $orgform_kurzbz)
					{
						if ($parameters == null) 
							$parameters = array();						
						return $this->_ci->parser->parse_string($_phrase->text, $parameters, true);	
					}			
				}
			}
			
			//fallback 2: if phrase not found at all, return phrasename
			$phrase = '<< PHRASE ' . $phrase . ' >>';
			return $this->_ci->parser->parse_string($phrase, $parameters, true);
		}		
	}
	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Extends the functionalities of the constructor of this class
	 * This is a workaround to use more parameters in the construct since PHP doesn't support many constructors
	 * The new accepted parameters are:
	 * - categories: could be a string or an array of strings. These are the categories used to load phrases
	 * - language: optional parameter must be a string. It's used to load phrases
	 */
	private function _extend_construct($params)
	{
		// Checks if the $params is an array with at least one element
		if (is_array($params) && count($params) > 0)
		{
			$parameters = $params[0]; // temporary variable

			// If there are parameters
			if (is_array($parameters) && count($parameters) > 0)
			{
				$categories = $parameters[0]; // categories is always the first parameter
				if (!is_array($categories)) // if it is not an array, then convert into one
				{
					$categories = array($categories);
				}

				// Use the given language if present, otherwise retrives the language for the logged user
				$language = DEFAULT_LANGUAGE;
				if (count($parameters) == 2 && !empty($parameters[1]) && is_string($parameters[1]))
				{
					$language = $parameters[1];
				}
				else
				{
					$this->_ci->load->model('person/Person_model', 'PersonModel');

					$language = $this->_ci->PersonModel->getLanguage(getAuthUID());
				}

				// Loads phrases
				$phrases = $this->_ci->PhraseModel->getPhrasesByCategoryAndLanguage($categories, $language);

				// If there are phrases loaded then store them in the property _phrases
				if (hasData($phrases))
				{
					$this->_phrases = $phrases->retval;
				}
			}
		}
	}
}
