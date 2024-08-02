<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Language Class
 */
class FHC_Lang extends CI_Lang
{
	private $_phrases = [];

	/**
	 * Load a language file
	 *
	 * @param	mixed	$langfile	Language file name
	 * @param	string	$idiom		Language name (english, etc.)
	 * @param	bool	$return		Whether to return the loaded array of translations
	 * @param 	bool	$add_suffix	Whether to add suffix to $langfile
	 * @param 	string	$alt_path	Alternative path to look for the language file
	 *
	 * @return	void|string[]	Array containing translations, if $return is set to TRUE
	 */
	public function load($langfile, $idiom = '', $return = false, $add_suffix = true, $alt_path = '')
	{
		$language = getUserLanguage($idiom ?: null);
		$language = 'english';

		if (!isset($this->_phrases[$language]))
			$this->_phrases[$language] = [];

		$categories = [];

		if (is_array($langfile)) {
			foreach ($langfile as $cat)
				if (!isset($this->_phrases[$language][$cat]))
					$categories[] = 'ci_' . $cat;
		} else {
			$categories[] = 'ci_' . $langfile;
		}

		if ($categories) {
			$ci =& get_instance();
			$ci->load->model('system/Phrase_model', 'PhraseModel');

			$phrases = $ci->PhraseModel->getPhrasesByCategoryAndLanguage($categories, $language);

			$loadedPhrases = array_fill_keys($categories, []);

			foreach ($phrases->retval as $phrase)
				if (!is_null($phrase->text) && in_array($phrase->category, $categories))
					$loadedPhrases[$phrase->category][$phrase->phrase] = $phrase->text;

			if ($language != DEFAULT_LANGUAGE) {
				$defaultPhrases = $ci->PhraseModel->getPhrasesByCategoryAndLanguage($categories, DEFAULT_LANGUAGE);
				if (hasData($phrases) && hasData($defaultPhrases)) {
					foreach ($defaultPhrases->retval as $phrase)
						if (!isset($loadedPhrases[$phrase->category][$phrase->phrase]))
							$loadedPhrases[$phrase->category][$phrase->phrase] = $phrase->text;
				} elseif (hasData($defaultPhrases)) {
					foreach ($defaultPhrases->retval as $phrase)
						if (!is_null($phrase->text) && in_array($phrase->category, $categories))
							$loadedPhrases[$phrase->category][$phrase->phrase] = $phrase->text;
				}
			}
			foreach ($loadedPhrases as $cat => $phrases)
				$this->_phrases[$language][$cat] = $phrases;
		}

		$result = parent::load($langfile, strtolower($language), $return, $add_suffix, $alt_path);

		if ($return) {
			if (is_array($langfile))
				foreach ($langfile as $cat)
					$result = array_merge($result, $this->_phrases[$language]['ci_' . $cat]);
			else
				$result = array_merge($result, $this->_phrases[$language]['ci_' . $langfile]);

		} else {
			if (is_array($langfile))
				foreach ($langfile as $cat)
					$this->language = array_merge($this->language, $this->_phrases[$language]['ci_' . $cat]);
			else
				$this->language = array_merge($this->language, $this->_phrases[$language]['ci_' . $langfile]);
		}
		return $result;
	}
}
