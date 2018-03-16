<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class FHC_Controller extends CI_Controller
{
	// Conversion from HTTP method to access type method
	const READ_HTTP_METHOD = 'GET';
	const WRITE_HTTP_METHOD = 'POST';

	/**
	 * Standard construct for all the controllers, loads the authentication system
	 * Checks the caller permissions
	 */
    public function __construct($requiredPermissions)
	{
        parent::__construct();

		$this->load->helper('fhcauth');

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
		if (!$this->_checkPermissions($requiredPermissions))
		{
			header('HTTP/1.0 401 Unauthorized');
			echo 'You are not allowed to access to this content';
			exit;
		}
	}

	/**
	 * Checks if the caller is allowed to access to this content with the given permissions
	 * - checks if the parameter $requiredPermissions is set, is an array and contains at least one element
	 * - checks if the given $requiredPermissions parameter contains the called method of the controller
	 * - checks if the HTTP method used to call is GET or POST
	 * - convert the required permissions to an array if needed
	 * - loops through the required permissions
	 * - checks if the permission is well formatted
	 * - retrives permission and required access type from the $requiredPermissions array
	 * - checks if the required access type is compliant with the HTTP method (GET => r, POST => w)
	 * - if the user has one of the permissions than exit the loop
	 * - checks if the user has the same required permissiond with the same required access type
	 * - returns true if all the checks are ok, otherwise false
	 */
	private function _checkPermissions($requiredPermissions)
	{
		$checkPermissions = false;
		$method = $this->router->method;
		$requestMethod = $_SERVER['REQUEST_METHOD'];

		// Checks if the parameter $requiredPermissions is set, is an array and contains at least one element
		if (isset($requiredPermissions) && is_array($requiredPermissions) && count($requiredPermissions) > 0)
		{
			// Checks if the given $requiredPermissions parameter contains the called method of the controller
			if (isset($requiredPermissions[$method]))
			{
				// Checks if the HTTP method used to call is GET or POST
				if ($requestMethod == self::READ_HTTP_METHOD || $requestMethod == self::WRITE_HTTP_METHOD)
				{
					$permissions = $requiredPermissions[$method];
					// Convert the required permissions to an array if needed
					if (!is_array($permissions))
					{
						$permissions = array($requiredPermissions[$method]);
					}

					// Loops through the required permissions
					for ($pCounter = 0; $pCounter < count($permissions); $pCounter++)
					{
						// Checks if the permission is well formatted
						if (strpos($permissions[$pCounter], PermissionLib::PERMISSION_SEPARATOR) !== false)
						{
							// Retrives permission and required access type from the $requiredPermissions array
							list($permission, $requiredAccessType) = explode(PermissionLib::PERMISSION_SEPARATOR, $permissions[$pCounter]);

							$accessType = null;

							// Checks if the required access type is compliant with the HTTP method (GET => r, POST => w)
							if ($requestMethod == self::READ_HTTP_METHOD
								&& strpos($requiredAccessType, PermissionLib::READ_RIGHT) !== false)
							{
								$accessType = PermissionLib::SELECT_RIGHT; // S
							}
							elseif ($requestMethod == self::WRITE_HTTP_METHOD
								&& strpos($requiredAccessType, PermissionLib::WRITE_RIGHT) !== false)
							{
								$accessType = PermissionLib::REPLACE_RIGHT.PermissionLib::DELETE_RIGHT; // UID
							}

							if ($accessType != null) // if compliant
							{
								// Checks if the user has the same required permissiond with the same required access type
								$checkPermissions = $this->permissionlib->isBerechtigt($permission, $accessType);

								// If the user has one of the permissionsm than exit the loop
								if ($checkPermissions === true) break;
							}
						}
						else
						{
							show_error('The given permission does not use the correct format');
						}
					}
				}
				else
				{
					show_error('Your are trying to access to this content with a not valid HTTP method');
				}
			}
			else
			{
				show_error('The given permission array does not contain the called method');
			}
		}
		else
		{
			show_error('You must give the permissions array as parameter to the constructor of the controller');
		}

		return $checkPermissions;
	}
}
