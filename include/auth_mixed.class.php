<?php
/* Copyright (C) 2013 FH Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> 
 *
 */
/**
 * Klasse fuer Authentifizierung
 */

require_once(dirname(__FILE__).'/basis.class.php');
require_once(dirname(__FILE__).'/../addons/ldap/vilesci/ldap.class.php');

class authentication extends auth
{
	public $ldap_config;

	public function __construct()
	{
		$this->ldap_config[]=array('LDAP_SERVER'=>LDAP_SERVER,
			'LDAP_PORT'=>LDAP_PORT,
			'LDAP_STARTTLS'=>LDAP_STARTTLS,
			'LDAP_BASE_DN'=>LDAP_BASE_DN,
			'LDAP_BIND_USER'=>LDAP_BIND_USER,
			'LDAP_BIND_PASSWORD'=>LDAP_BIND_PASSWORD,
			'LDAP_USER_SEARCH_FILTER'=>LDAP_USER_SEARCH_FILTER);

		// Wenn ein zweiter LDAP Server angegeben wurde, diesen mitaufnehmen
		if(defined('LDAP2_SERVER'))
		{
			$this->ldap_config[]=array('LDAP_SERVER'=>LDAP2_SERVER,
			'LDAP_PORT'=>LDAP2_PORT,
			'LDAP_STARTTLS'=>LDAP2_STARTTLS,
			'LDAP_BASE_DN'=>LDAP2_BASE_DN,
			'LDAP_BIND_USER'=>LDAP2_BIND_USER,
			'LDAP_BIND_PASSWORD'=>LDAP2_BIND_PASSWORD,
			'LDAP_USER_SEARCH_FILTER'=>LDAP2_USER_SEARCH_FILTER);
		}
	}
	
	public function login($username)
	{
		// Nicht noetig da dies ueber htaccess gesteuert wird
	}

	public function getUser()
	{
		 // derzeit get_uid in functions.inc.php
		if(isset($_SERVER['REMOTE_USER']))
		{
			return mb_strtolower(trim($_SERVER['REMOTE_USER']));
		}
		else
		{
			if(isset($_SESSION['user']))
				return mb_strtolower(trim($_SESSION['user']));
			else
				return $this->RequireLogin();
		}
	}

	/**
	 * Prueft ob Username und Passwort stimmen
	 * @param $username UID des Users
	 * @param $passwort Passwort des Users
	 * @return boolean true wenn Passwort ok, false wenn falsch
	 */
	public function checkpassword($username, $passwort)
	{
		// Alle vorhandenen LDAP Server nacheinander durchlaufen
		// bis einer passt.
		foreach($this->ldap_config as $ldap)
		{
			$ldap_obj = new ldap();
			// Verbindung zum Server
			if($ldap_obj->connect($ldap['LDAP_SERVER'],$ldap['LDAP_PORT'],$ldap['LDAP_BIND_USER'],$ldap['LDAP_BIND_PASSWORD'],$ldap['LDAP_STARTTLS']))
			{
				// DN des Users holen
				if($userdn = $ldap_obj->GetUserDN($username, $ldap['LDAP_BASE_DN'],$ldap['LDAP_USER_SEARCH_FILTER']))
				{
					// Verbindung trennen
					$ldap_obj->unbind();

					// Verbindung mit DN des Users und dessen Passwort herstellen
					if($ldap_obj->connect($ldap['LDAP_SERVER'],$ldap['LDAP_PORT'],$userdn,$passwort,$ldap['LDAP_STARTTLS']))
					{
						// Passwort und User OK
						$ldap_obj->unbind();
						return true;
					}
				}
			}
		}
		// Kein Eintrag gefunden
		return false;
	}

	/**
	 * Prueft ob der User im LDAP angelegt ist
	 * @param $username UID des Users
	 * @return boolean true wenn vorhanden, sonst false
	 */
	public function UserExternalExists($username)
	{
		// Alle vorhandenen LDAP Server nacheinander durchlaufen
		// bis einer passt.
		foreach($this->ldap_config as $ldap)
		{
			$ldap_obj = new ldap();
			// Verbindung zum Server
			if($ldap_obj->connect($ldap['LDAP_SERVER'],$ldap['LDAP_PORT'],$ldap['LDAP_BIND_USER'],$ldap['LDAP_BIND_PASSWORD'],$ldap['LDAP_STARTTLS']))
			{
				// User suchen
				if($userdn = $ldap_obj->GetUserDN($username, $ldap['LDAP_BASE_DN'],$ldap['LDAP_USER_SEARCH_FILTER']))
				{
					$ldap_obj->unbind();
					return true;
				}
			}
			$ldap_obj->unbind();
		}
		return false;
	}

	// derzeit manual_basic_auth in functions.inc.php eventuell 
	// direkt von getUser aus aufrufen wenn nicht authentifiziert
	public function RequireLogin()
	{
		if(!(isset($_SERVER['PHP_AUTH_USER']) && $this->checkpassword($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])))
		{
			header('WWW-Authenticate: Basic realm="'.AUTH_NAME.'"');
			header('HTTP/1.0 401 Unauthorized');
			echo "Invalid Credentials";
			exit;
		}
		else
		{
			return mb_strtolower(trim($_SERVER['PHP_AUTH_USER']));
		}
	}

	public function isUserLoggedIn()
	{
		if(isset($_SERVER['PHP_AUTH_USER']) && $this->checkpassword($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']))
			return true;
		else
			return false;
	}

	public function getOriginalUser()
	{
		if(isset($_SERVER['REMOTE_USER']))
			return mb_strtolower(trim($_SERVER['REMOTE_USER']));
		else
		{
			if(isset($_SESSION['user_original']))
				return $_SESSION['user_original'];
		}
	}

	public function loginAsUser($username)
	{
		$_SESSION['user']=$username;
		return true;
	}
	
	public function logout()
	{
		echo "LOGOUT BEI MIXED AUTH NICHT MÖGLICH";
	}
}
?>
