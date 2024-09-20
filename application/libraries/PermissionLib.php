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

use \benutzerberechtigung as benutzerberechtigung;

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

	// Configuration entries
	const LOGINAS_ALLOWED = 'permission_loginas_allowed';
	const LOGINAS_BLACKLIST = 'permission_loginas_blacklist';
	const LOGINAS_USERS_BLACKLIST = 'permission_loginas_users_blacklist';
	const LOGINAS_PERSONIDS_BLACKLIST = 'permission_loginas_personids_blacklist';

	private $_ci; // CI instance
	private static $bb; // benutzerberechtigung

	/**
	 * PermissionLib's constructor
	 * Here is initialized the static property bb with all the rights of the user (API caller)
	 */
	public function __construct()
	{
		// Loads CI instance
		$this->_ci =& get_instance();

		$this->_ci->config->load('permission'); // Loads permission configuration

		// If it's NOT called from command line
		if (!is_cli())
		{
			// API Caller rights initialization
			$authObj = $this->_ci->authlib->getAuthObj();
			self::$bb = new benutzerberechtigung();
			if ($authObj)
				self::$bb->getBerechtigungen($authObj->{AuthLib::AO_USERNAME});
		}
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

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
	 * - if it's called from command line than it's trusted
	 * - checks if the parameter $requiredPermissions is set, is an array and contains at least one element
	 * - checks if the given $requiredPermissions parameter contains the called method of the controller
	 * - checks if the HTTP method used to call is GET or POST
	 * - convert the required permissions to an array if needed
	 * - loops through the required permissions
	 * - checks if the permission is well formatted
	 * - retrieves permission and required access type from the $requiredPermissions array
	 * - checks if the required access type is compliant with the HTTP method (GET => r, POST => w)
	 * - if the user has one of the permissions than exit the loop
	 * - checks if the user has the same required permissiond with the same required access type
	 * - returns true if all the checks are ok, otherwise false
	 *
	 * NOTE: the displayed error messages are used to warn the developer about any issues that could occur,
	 * 		they are not intended for the final user!
	 */
	public function isEntitled($requiredPermissions, $calledMethod)
	{
		$checkPermissions = false;

		// If it's called from command line than it's trusted
		if (is_cli()) return true;

		$requestMethod = $_SERVER['REQUEST_METHOD'];

		// Checks if the parameter $requiredPermissions is set, is an array and contains at least one element
		if (isset($requiredPermissions) && !isEmptyArray($requiredPermissions))
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

					if (!isEmptyArray($permissions))
					{
						// Loops through the required permissions
						for ($pCounter = 0; $pCounter < count($permissions); $pCounter++)
						{
							// Checks if the permission is well formatted
							if (strpos($permissions[$pCounter], PermissionLib::PERMISSION_SEPARATOR) !== false)
							{
								// Retrieves permission and required access type from the $requiredPermissions array
								list($permission, $accessType) = $this->convertAccessType($permissions[$pCounter]);

								if (!isEmptyString($accessType)) // if compliant
								{
									// Checks if the user has the same required permissiond with the same required access type
									$checkPermissions = $this->isBerechtigt($permission, $accessType);

									// If the user has one of the permissionsm than exit the loop
									if ($checkPermissions === true) break;
								}
							}
							elseif ($permissions[$pCounter] == Auth_Controller::PERM_ANONYMOUS)
							{
								$checkPermissions = true;
								break;
							}
							elseif ($permissions[$pCounter] == Auth_Controller::PERM_LOGGED)
							{
								$checkPermissions = isLogged();
								break;
							}
							else
							{
								show_error('The given permission does not use the correct format');
							}
						}
					}
					else
					{
						show_error('No permissions are set for this method, an empty array is given');
					}
				}
				else
				{
					show_error('You are trying to access to this content with a not valid HTTP method');
				}
			}
			else
			{
				show_error('The given permission array does not contain the given method or is not correctly set');
			}
		}
		else
		{
			show_error('The given permissions is not a valid array or it is an empty one');
		}

		return $checkPermissions;
	}

	/**
	 * Retrieves permission and required access type from the newly formatted permission string
	 *
	 * @param string $permission
	 *
	 * @return array
	 */
	public function convertAccessType($permission)
	{
		list($permission, $reqAccessType) = explode(PermissionLib::PERMISSION_SEPARATOR, $permission);
		$accessType = '';
		if (strpos($reqAccessType, PermissionLib::READ_RIGHT) !== false)
			$accessType = PermissionLib::SELECT_RIGHT;
		if (strpos($reqAccessType, PermissionLib::WRITE_RIGHT) !== false)
			$accessType = PermissionLib::REPLACE_RIGHT.PermissionLib::DELETE_RIGHT;
		return [$permission, $accessType];
	}

	/**
	 * Checks if at least one of the permissions given as parameter (requiredPermissions) belongs to the authenticated user
	 * It checks the given permissions against a given method (controller method name) and a given permission type (R and/or W)
	 * If the $permissionType is not given then it is assumed that is already present inside requiredPermissions
	 * Wrapper method for isEntitled, it uses method to build an associative array of permissions having as key the method itself
	 */
	public function hasAtLeastOne($requiredPermissions, $method, $permissionType = null)
	{
		$isAllowed = false; // by default is NOT allowed

		// If the parameter requiredPermissions is NOT given, then no one is allow to use this FilterWidget
		if ($requiredPermissions != null)
		{
			// If requiredPermissions is NOT an array then converts it to an array
			if (!is_array($requiredPermissions))
			{
				$requiredPermissions = array($requiredPermissions);
			}

			// Checks if at least one of the permissions given as parameter belongs to the authenticated user...
			for ($p = 0; $p < count($requiredPermissions); $p++)
			{
				$pt = ''; // by default the permission is alredy present in $requiredPermissions[$p]
				if ($permissionType != null) // if is it given as parameter
				{
					$pt = self::PERMISSION_SEPARATOR.$permissionType; // then build the permission type string
				}

				$isAllowed = $this->_ci->permissionlib->isEntitled(
					array(
						$method => $requiredPermissions[$p].$pt
					),
					$method
				);

				if ($isAllowed === true) break; // ...if confirmed then is allowed to use this FilterWidget
			}
		}

		return $isAllowed;
	}

	/**
	 * Checks if it is possible for the logged user to gain the identity of the required user specified by the given uid
	 */
	public function isEntitledLoginASByUID($uid)
	{
		return !$this->_inLAUsersBlacklist($uid) && !$this->_hasLANotAllowedPermissions($uid) && $this->_hasLAPermissions();
	}

	/**
	 * Checks if it is possible for the logged user to gain the identity of the required user specified by the given person id
	 * NOTE: does NOT check permission for the given user because usually this users do NOT have any uid
	 */
	public function isEntitledLoginASByPersonId($person_id)
	{
		return !$this->_inLAPersonIdsBlacklist($person_id) && $this->_hasLAPermissions();
	}

    /**
     * Returns the study programs the person is entitled for.
     * @param null $berechtigung_kurzbz If given, only study programs are retrieved according to organisational units
     * assigned to that permission.
     * @return array|bool array of studiengang_kz the person is entitled for. False on error.
     */
    public function getSTG_isEntitledFor($berechtigung_kurzbz = null)
    {
        $studiengang_kz_arr = array();

        if (self::$bb->getStgKz($berechtigung_kurzbz))
        {
            return $studiengang_kz_arr =  self::$bb->getStgKz($berechtigung_kurzbz);
        }
        else
        {
            return false;
        }
    }

    /**
     * Returns the organisational units the person is entitled for.
     * @param null $berechtigung_kurzbz
     * @return array|bool array of oe_kurzbz the person is entitled for. False on error.
     */
    public function getOE_isEntitledFor($berechtigung_kurzbz = null)
    {
        $oe_kurzbz_arr = array();

        if (self::$bb->getOEkurzbz($berechtigung_kurzbz))
        {
            return $oe_kurzbz_arr =  self::$bb->getOEkurzbz($berechtigung_kurzbz);
        }
        else
        {
            return false;
        }
    }

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Checks if the given uid is in the users blacklist
	 */
	private function _inLAUsersBlacklist($uid)
	{
		// Given uid in user blacklist?
		return in_array($uid, $this->_ci->config->item(self::LOGINAS_USERS_BLACKLIST));
	}

	/**
	 * Checks if the given person id is in the person ids blacklist
	 */
	private function _inLAPersonIdsBlacklist($person_id)
	{
		// Given person id in person ids blacklist?
		return in_array($person_id, $this->_ci->config->item(self::LOGINAS_PERSONIDS_BLACKLIST));
	}

	/**
	 * Checks if the user whose identity is to be obtained does not have a NOT allowed permission
	 */
	private function _hasLANotAllowedPermissions($uid)
	{
		// List of permissions that cannot be gained with loginAs
		$loginASBl = $this->_ci->config->item(self::LOGINAS_BLACKLIST);

		$bb = new benutzerberechtigung();
		$bb->getBerechtigungen($uid); // gets all the permissions for the target user

		// Loops through NOT allowed permissions
		foreach ($loginASBl as $notAllowedPermission)
		{
			// Target user has a NOT allowed permission?
			if ($bb->isBerechtigt($notAllowedPermission))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the current logged user has the permission to perform a login as
	 */
	private function _hasLAPermissions()
	{
		// List of permissions that are allowed to perform loginAs
		$loginASAllowed = $this->_ci->config->item(self::LOGINAS_ALLOWED);

		// Loops through allowed permissions
		foreach ($loginASAllowed as $allowedPermission)
		{
			// The logged user has a loginAS  permission?
			if (self::$bb->isBerechtigt($allowedPermission))
			{
				return true;
			}
		}

		return false;
	}
}
