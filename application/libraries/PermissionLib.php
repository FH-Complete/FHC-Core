<?php
/**
 * FH-Complete
 *
 * @package	FHC-Helper
 * @author	FHC-Team
 * @copyright	Copyright (c) 2016 fhcomplete.org
 * @license	GPLv3
 * @link	https://fhcomplete.org
 * @since	Version 1.0.0
 * @filesource
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once(FHCPATH.'include/basis_db.class.php');
require_once(FHCPATH.'include/organisationseinheit.class.php');
require_once(FHCPATH.'include/studiengang.class.php');
require_once(FHCPATH.'include/fachbereich.class.php');
require_once(FHCPATH.'include/functions.inc.php');
require_once(FHCPATH.'include/wawi_kostenstelle.class.php');
require_once(FHCPATH.'include/benutzerberechtigung.class.php');

class PermissionLib
{
	// Available rights in the DB
	const SELECT_RIGHT = 's';
	const UPDATE_RIGHT = 'u';
	const INSERT_RIGHT = 'i';
	const DELETE_RIGHT = 'd';
	const REPLACE_RIGHT = 'ui';

	// Available rights to access a controller
	const READ_RIGHT = 'r';
	const WRITE_RIGHT = 'w';
	const READ_WRITE_RIGHT = 'rw';

	const PERMISSION_SEPARATOR = ':'; // used as separator berween permission and right

	// Conversion from HTTP method to access type method
	const READ_HTTP_METHOD = 'GET';
	const WRITE_HTTP_METHOD = 'POST';

	private $acl; // conversion array from a source to a permission
	private static $bb; // benutzerberechtigung

	/**
	 * PermissionLib's constructor
	 * Here is initialized the static property bb with all the rights of the user (API caller)
	 */
	public function __construct()
	{
		// Loads CI instance
		$this->ci =& get_instance();

		// Loads the auth helper
		$this->ci->load->helper('fhcauth');

		// Loads the array of resources
		$this->acl = $this->ci->config->item('fhc_acl');

		if (!is_cli())
		{
			// API Caller rights initialization
			self::$bb = new benutzerberechtigung();
			self::$bb->getBerechtigungen(getAuthUID());
		}
	}

	/**
	 * Check if the user is entitled to get access to a source with the given access type
	 *
	 * @return bool <b>true</b> if a user has the right to access to the specified
	 *				resource with a specified permission type, <b>false</b> otherwise
	 */
	public function isEntitled($sourceName, $permissionType)
	{
		$isEntitled = false;

		// If it's called from command line than it's trusted
		if (!is_cli())
		{
			// If the resource exists
			if (isset($this->acl[$sourceName]))
			{
				// Checks permission
				$isEntitled = $this->isBerechtigt($this->acl[$sourceName], $permissionType);
			}
		}
		else
		{
			$isEntitled	= true;
		}

		return $isEntitled;
	}

	/**
	 * Get a permission by a given source
	 */
	public function getBerechtigungKurzbz($sourceName)
	{
		$returnValue = null;

		if (isset($this->acl[$sourceName]))
		{
			$returnValue = $this->acl[$sourceName];
		}

		return $returnValue;
	}

	/**
	 * Checks user's (API caller) rights
	 */
	public function isBerechtigt($berechtigung_kurzbz, $art = null, $oe_kurzbz = null, $kostenstelle_id = null)
	{
		$isBerechtigt = false;

		if (!is_null($berechtigung_kurzbz))
		{
			if (self::$bb->isBerechtigt($berechtigung_kurzbz, $oe_kurzbz, $art, $kostenstelle_id))
			{
				$isBerechtigt = true;
			}
		}

		return $isBerechtigt;
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
	 *
	 * NOTE: the displayed error messages are used to warn the developer about any issues that could occur,
	 * 		they are not intended for the final user!
	 */
	public function checkPermissions($requiredPermissions, $calledMethod)
	{
		$checkPermissions = false;
		$requestMethod = $_SERVER['REQUEST_METHOD'];

		// Checks if the parameter $requiredPermissions is set, is an array and contains at least one element
		if (isset($requiredPermissions) && is_array($requiredPermissions) && count($requiredPermissions) > 0)
		{
			// Checks if the given $requiredPermissions parameter contains the called method of the controller
			if (isset($requiredPermissions[$calledMethod]))
			{
				// Checks if the HTTP method used to call is GET or POST
				if ($requestMethod == self::READ_HTTP_METHOD || $requestMethod == self::WRITE_HTTP_METHOD)
				{
					$permissions = $requiredPermissions[$calledMethod];
					// Convert the required permissions to an array if needed
					if (!is_array($permissions))
					{
						$permissions = array($requiredPermissions[$calledMethod]);
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
								$checkPermissions = $this->isBerechtigt($permission, $accessType);

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
