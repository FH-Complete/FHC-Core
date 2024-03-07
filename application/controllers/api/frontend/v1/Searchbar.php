<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Searchbar extends FHCAPI_Controller
{
	const SEARCHSTR_PARAM = 'searchstr';
	const TYPES_PARAM = 'types';

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		// NOTE(chris): additional permission checks will be done in SearchBarLib
		parent::__construct([
			'search' => self::PERM_LOGGED
		]);

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
		$this->load->library('form_validation');

		// Checks if the searchstr and the types parameters are in the POSTed JSON
		$this->form_validation->set_rules(self::SEARCHSTR_PARAM, null, 'required');
		$this->form_validation->set_rules(self::TYPES_PARAM . '[]', null, 'required');

		if (!$this->form_validation->run())
			$this->terminateWithError(SearchBarLib::ERROR_WRONG_JSON, self::ERROR_TYPE_GENERAL);

		// Convert to json the result from searchbarlib->search
		$result = $this->searchbarlib->search($this->input->post(self::SEARCHSTR_PARAM), $this->input->post(self::TYPES_PARAM));
		if (property_exists($result, 'error'))
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		$this->terminateWithSuccess($result);
	}
}

