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
 * Definition and initialization of object FHC_PhraseLib
 */
var FHC_PhraseLib = {

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Returns the phrase-text in the user's language
	 * @param {String} category : phrase-category
	 * @param {String} phrase : phrase-name
	 * @param {array} params : String-parameters to be set in variables in phrasentext
	 * @returns {String} : phrase-text
	 */
	t: function (category, phrase, params = []) {

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
					if (params == null || (params != null && !$.isArray(params)))
					{
						params = [];
					}
					
					return FHC_PhraseLib._replacePhraseVariable(phraseObj.text, params); // parsing
				}
			}
		}

		return '<< PHRASE ' + phrase + ' >>';
	},

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Returns phrase with variables being replaced
	 * @param {String} phrase : phrasen-text (with one ore more variables)
	 * @param {array} replaceStringArr : String-array to be set in variables in phrasentext (order matters)
	 * @returns {String} : replaced phrasen-text
	 */
	_replacePhraseVariable: function (phrase, replaceStringArr) {
		for (var i = 0; i < replaceStringArr.length; i++)
		{
			phrase = phrase.replace(/\{(.*?)\}/, replaceStringArr[i]);
		}
		return phrase;
	}
};
