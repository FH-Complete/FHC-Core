<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller is used to set the current language for a user
 * It is part of the Language Switcher Widget
 */
class Language extends FHC_Controller
{
	/**
	 * Calls the parent's constructor
	 */
	public function __construct()
    {
        parent::__construct();
    }

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 *
	 */
	public function setSessionLanguage()
	{
		$language = $this->input->post('language');

		$this->outputJson(setUserLanguage($language));
	}
}
