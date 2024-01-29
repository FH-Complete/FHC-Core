/**
 * FH-Complete
 *
 * @package
 * @author
 * @copyright   Copyright (c) 2016 fhcomplete.org
 * @license GPLv3
 * @link    https://fhcomplete.org
 * @since	Version 1.0.0
 */

/**
 * Definition and initialization of object FHC_PhrasesLib
 */
var FHC_PhrasesLib = {

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Returns the phrase-text in the user's language
	 * NOTE: the parameter params is an object since associative arrays are NOT supported in JS
	 * @param {String} category : phrase-category
	 * @param {String} phrase : phrase-name
	 * @param {Object} params : parameters to be replaced instead of {<parameter name>} in phraseObj.text
	 * @returns {String} : phrase-text
	 */
	t: function(category, phrase, params) {

		if (typeof(params)=='undefined')
			 params = {};

		// Checks if FHC_JS_PHRASES_STORAGE_OBJECT is an array
		if ($.isArray(FHC_JS_PHRASES_STORAGE_OBJECT))
		{
			// loop through global JS PHRASES STORAGE OBJECT and search for phrase
			for (i in FHC_JS_PHRASES_STORAGE_OBJECT)
			{
				var phraseObj = FHC_JS_PHRASES_STORAGE_OBJECT[i]; // Single phrase object

				// If the single phrase match the given parameters and is not an empty string
				if (phraseObj.category == category
					&& phraseObj.phrase == phrase
					&& phraseObj.text != null
					&& phraseObj.text.trim() != '')
				{
					// If params is null or not an array
					if (params == null)
					{
						params = {};
					}

					return FHC_PhrasesLib._replacePhraseVariable(phraseObj.text, params); // parsing
				}
			}
		}

		return '<< PHRASE ' + phrase + ' >>';
	},

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Returns phrase with variables being replaced
	 * NOTE: params is an object but here is treat as an associative array, not that much orthodox but it works fine ;)
	 * @param {String} phrase : phrasen-text (with one ore more variables)
	 * @param {Object} params : parameters to be replaced instead of {<parameter name>} in phrase
	 * @returns {String} : replaced phrasen-text
	 */
	_replacePhraseVariable: function(phrase, params) {

		// Loops
		for (var paramName in params)
		{
			var paramValue = params[paramName];

			phrase = phrase.replace('{' + paramName + '}', paramValue);
		}

		return phrase;
	}
};
