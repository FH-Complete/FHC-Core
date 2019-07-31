<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class APIv1_Controller extends RESTFul_Controller
{
	private $_requiredPermissions;

	/**
	 * Standard constructor for all the RESTful resources
	 */
    public function __construct($requiredPermissions)
    {
        parent::__construct();

		$this->_requiredPermissions = $requiredPermissions;

		log_message('debug', 'Called API: '.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
    }

	/**
	 * This method is automatically called by CodeIgniter after the execution of the constructor is completed
	 * - Cheks if the Authlib was loaded, if not it means that the authentication failed
	 * - Loads the permsission lib and calls permissionlib->isEntitled
	 * - Checks if the caller is allowed to access to this content with the given permissions
	 *	 if it is not allowed will set the HTTP header with code 401
	 * - Calls the parent (REST_Controller) _remap method to performs other checks
	 * NOTE: this methods override the parent method!!!
	 */
	public function _remap($object_called, $arguments = [])
	{
		if (isset($this->authlib)) // if set then the authentication is ok
		{
			// Loads permission lib
			$this->load->library('PermissionLib');

			// Cheks if the user has the permission to call a method
			if (!$this->permissionlib->isEntitled($this->_requiredPermissions, $this->router->method))
			{
				// If not...
				$this->response(error('You are not allowed to access to this content'), REST_Controller::HTTP_UNAUTHORIZED);
			}
		}

		// Finally calls the parent _remap to perform other checks
		parent::_remap($object_called, $arguments);
	}
}
