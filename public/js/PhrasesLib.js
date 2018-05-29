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
	Returns the phrase-text in the user's language
	* @param {String} category : phrase-category
	* @param {String} phrase : phrase-name
	* @param {array} params : String-parameters to be set in variables in phrasentext
	* @returns {String} : phrase-text
	*/
	t: function(category, phrase, params = []) 
	{
		var category_found = false;
		var phrase_found = false;
		
		//check category and phrase first
		if(typeof(category) == "undefined" || category === null
			|| typeof(phrase) == "undefined" || phrase === null)
		{
			console.log('Category and/or phrase not found. \n\
						1. Check params in PhrasesLib.js t-method\n\
						2. Check params in FHC-Header.php phrases-array');
			return;
		}
		
		//loop through global JS PHRASES STORAGE OBJECT and search for phrase
		for(i in FHC_JS_PHRASES_STORAGE_OBJECT)
		{
			e = FHC_JS_PHRASES_STORAGE_OBJECT[i];	
			if (e.category === category)
			{
				category_found = true;
				if (e.phrase === phrase)
				{
					phrase_found = true;
					
					//replace if params are set
					if ($.isArray(params) && typeof params !== 'undefined' && params !== null)
					{
						if (params.length !== 0)
						{
							e.text = replacePhraseVariable(e.text, params);
						}
					}
					else
					{
						console.log('Could not replace variable. \n\
									Replace-params should be an array.')
					}
									
					return e.text;				
				}
			}
		}
		
		//show error messages for missing categories/phrases
		if (!category_found)
		{
			console.log('Category not found. \n\
						1. Check params in PhrasesLib.js t-method\n\
						2. Check params in FHC-Header.php phrases-array');
		}
		
		if (category_found && !phrase_found)
		{
			console.log('Phrase not found. \n\
						1. Check params in PhrasesLib.js t-method\n\
						2. Check params in FHC-Header.php phrases-array');
		}
	}	
}


//------------------------------------------------------------------------------------------------------------------
// Helper methods

/**
	Returns phrase with variables being replaced
	* @param {String} phrase : phrasen-text (with one ore more variables)
	* @param {array} replaceStringArr : String-array to be set in variables in phrasentext (order matters)
	* @returns {String} : replaced phrasen-text
	*/
function replacePhraseVariable(phrase, replaceStringArr)
{
	for (var i = 0; i < replaceStringArr.length; i++)
	{
		phrase = phrase.replace(/\{(.*?)\}/, replaceStringArr[i]);
	}
	return phrase;
}



//...TEST
console.log(FHC_PhraseLib.t('global', 'mailAnXversandt', new Array('c@yahoo.de', 'test')));



