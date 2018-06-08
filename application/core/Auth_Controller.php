<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Auth_Controller extends FHC_Controller
{
	/**
	 * Extends this controller if authentication is required
	 */
    public function __construct($requiredPermissions)
	{
        parent::__construct();

		// Loads authentication helper
		$this->load->helper('fhcauth');

		// Checks if the caller is allowed to access to this content
		$this->_isAllowed($requiredPermissions);
	}

	/**
	 * Checks if the caller is allowed to access to this content with the given permissions
	 * If it is not allowed will set the HTTP header with code 401
	 * Wrapper for _checkPermissions
	 */
	private function _isAllowed($requiredPermissions)
	{
		// Loads permission lib
		$this->load->library('PermissionLib');

		if (!$this->permissionlib->isEntitled($requiredPermissions, $this->router->method))
		{
			header('HTTP/1.0 401 Unauthorized');
			echo 'You are not allowed to access to this content';
			exit;
		}
	}
}
