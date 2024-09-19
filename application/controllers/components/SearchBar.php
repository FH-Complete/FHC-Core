<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class SearchBar extends FHC_Controller
{
	const SEARCHSTR_PARAM = 'searchstr';
	const TYPES_PARAM = 'types';

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct();

		// Load the library SearchBarLib
		$this->load->library('SearchBarLib');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Gets a JSON body via HTTP POST and provides the parameters
	 */
	public function search()
	{
		$json = json_decode($this->input->raw_input_stream);

		// Checks if the searchstr and the types parameters are in the POSTed JSON
		if (isset($json->{self::SEARCHSTR_PARAM}) && isset($json->{self::TYPES_PARAM}))
		{
			// Convert to json the result from searchbarlib->search
			$this->outputJson(
				$this->searchbarlib->search(
					$json->{self::SEARCHSTR_PARAM},
					$json->{self::TYPES_PARAM}
				)
			);
		}
		else // otherwise return an error in JSON format
		{
			$this->outputJsonError(SearchBarLib::ERROR_WRONG_JSON);
		}
	}
}

