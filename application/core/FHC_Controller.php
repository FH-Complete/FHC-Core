<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class FHC_Controller extends CI_Controller
{
	/**
	 * Standard construct for all the controllers, loads the authentication system
	 * Checks the caller permissions
	 */
    public function __construct($requiredPermissions)
	{
        parent::__construct();

		// Loads authentication helper
		$this->load->helper('fhcauth');

		// Loads permission lib
		$this->load->library('PermissionLib');

		$this->_isAllowed($requiredPermissions);
	}

	/**
	 * Checks if the caller is allowed to access to this content with the given permissions
	 * If it is not allowed will set the HTTP header with code 401
	 * Wrapper for _checkPermissions
	 */
	private function _isAllowed($requiredPermissions)
	{
		if (!$this->permissionlib->isEntitled($requiredPermissions, $this->router->method))
		{
			header('HTTP/1.0 401 Unauthorized');
			echo 'You are not allowed to access to this content';
			exit;
		}
	}

	/**
	 * Wrapper to load phrases using the PhrasesLib
	 * NOTE: The library is loaded with the alias 'p', so must me used with this alias in the rest of the code.
	 *		EX: $this->p->t(<category>, <phrase name>)
	 */
	public function loadPhrases($categories, $language = null)
	{
		$this->load->library('PhrasesLib', array($categories, $language), 'p');
	}
}
