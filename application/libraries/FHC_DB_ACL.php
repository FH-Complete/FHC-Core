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
if (! defined('FCPATH'))
	exit('No direct script access allowed');

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

class FHC_DB_ACL
{
	public $bb;
	protected $_uid;

	/**
	 * Auth Username, Password over FH-Complete
	 *
	 * @param	string	$username
	 * @param	string	$password
	 * @return	bool
	 */
	function __construct($param = null)
	{
		$this->bb = new benutzerberechtigung();
		if (is_array($param) && isset($param['uid']))
			$this->_uid = $param['uid'];
	}

	function isBerechtigt($berechtigung_kurzbz, $art=null,  $oe_kurzbz=null,  $kostenstelle_id=null)
	{
		$this->bb->getBerechtigungen($this->_uid);
		return $this->bb->isBerechtigt($berechtigung_kurzbz, $oe_kurzbz=null, $art=null, $kostenstelle_id=null);
	}

	/** ---------------------------------------------------------------
	 * Set UID
	 *
	 * @param   string  $uid
	 * @return  bool
	 */
	public function setUID($uid)
	{
		return $this->_uid = $uid;
	}
}
