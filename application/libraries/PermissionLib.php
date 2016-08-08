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
if (! defined('FCPATH')) exit('No direct script access allowed');

require_once(FCPATH.'include/basis_db.class.php');
require_once(FCPATH.'include/organisationseinheit.class.php');
require_once(FCPATH.'include/studiengang.class.php');
require_once(FCPATH.'include/fachbereich.class.php');
require_once(FCPATH.'include/functions.inc.php');
require_once(FCPATH.'include/wawi_kostenstelle.class.php');
require_once(FCPATH.'include/benutzerberechtigung.class.php');

/**
 * FHC-Auth Helpers
 *
 * @package		FH-Complete
 * @subpackage	Libraries
 * @category	Library
 * @author		FHC-Team
 * @link		http://fhcomplete.org/user_guide/helpers/fhcauth_helper.html
 */

// ------------------------------------------------------------------------

class PermissionLib
{
	const SELECT_RIGHT = "s";
	const UPDATE_RIGHT = "u";
	const INSERT_RIGHT = "i";
	const DELETE_RIGHT = "d";
	
	public $bb;

	/**
	 * 
	 */
	function __construct()
	{
		// Loads CI instance
		$this->ci =& get_instance();
		
		// Loads the library to manage the rights system
		$this->ci->load->library("FHC_DB_ACL");
		
		// Loads the array of resources
		$this->ci->fhc_db_acl->acl = $this->ci->config->item('fhc_acl');
	}

	/**
	 * @return bool <b>true</b> if a user has the right to access to the specified
	 *				resource with a specified permission type, <b>false</b> otherwise
	 */
	public function hasPermission($sourceName, $permissionType)
	{
		// If the resource exists
		if (isset($this->ci->fhc_db_acl->acl[$sourceName]))
		{
			// Checks permission
			return $this->ci->fhc_db_acl->isBerechtigt($this->ci->fhc_db_acl->acl[$sourceName], $permissionType);
		}
		// if the resource does not exist, do not lose useful clock cycles
		else
		{
			return false;
		}
	}
	
	function isBerechtigt($berechtigung_kurzbz, $art = null,  $oe_kurzbz = null,  $kostenstelle_id = null)
	{
		$this->bb->getBerechtigungen(getAuthUID());
		return $this->bb->isBerechtigt($berechtigung_kurzbz, $oe_kurzbz, $art, $kostenstelle_id);
	}
	
	function getPermissions($uid) {}
	
	function isEntitled($berechtigung_kurzbz, $oe_kurzbz=null, $art=null, $kostenstelle_id=null) {}
}