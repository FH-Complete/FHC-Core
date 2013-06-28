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
 * Klasse fuer Authentifizierung ueber Sessions und LDAP
 */

/**
 * Damit Session Authentifizierung funktioniert, muss in der php.ini die option
 * session.auto_start=1 gesetzt sein oder im Config ein session_start() hinzugefügt werden
 */
require_once(dirname(__FILE__).'/basis.class.php');

class authentication extends auth
{
	public function login($username)
	{
		// Bei einem Login wird die Session ID erneuert
		// um Session Fixation zu erschweren
		session_regenerate_id();
		$_SESSION['user'] = mb_strtolower($username);
	}

	public function getUser()
	{
		if(isset($_SESSION['user']))
			return mb_strtolower($_SESSION['user']);
		else
			return $this->RequireLogin();
	}

	public function checkpassword($username, $passwort)
	{
		if($connect=ldap_connect(LDAP_SERVER))
		{
			// bind to ldap connection
			if(($bind=ldap_bind($connect, LDAP_BIND_USER, LDAP_BIND_PASSWORD)) == false)
			{
				$this->errormsg="LDAP BIND Fehlgeschlagen";
				return false;
			}

			// search for user
			if (($res_id = ldap_search( $connect, LDAP_BASE_DN, LDAP_USER_SEARCH_FILTER."=$username")) == false)
			{
				$this->errorsmg="Suche in LDAP fehlgeschlagen";
				return false;
			}

			if (ldap_count_entries($connect, $res_id) != 1)
			{
				$this->errormsg='Username wurde nicht oder oefter gefunden';
				return false;
			}

			if (( $entry_id = ldap_first_entry($connect, $res_id))== false)
			{
				$this->errormsg='LDAP Fetch fehlgeschlagen';
				return false;
			}

			if (( $user_dn = ldap_get_dn($connect, $entry_id)) == false)
			{
				$this->errormsg='LDAP user-dn fetched fehlgeschlagen';
				return false;
			}

			/* Authentifizierung des User */
			if (($link_id = @ldap_bind($connect, $user_dn, $passwort)) == false)
			{
				return false;
			}

			ldap_close($connect);
			return true;
		}
		else
		{
			$this->errormsg='Verbindung zum LDAP Server fehlgeschlagen';
		}
		ldap_close($connect);
		return(false);
	}

	public function RequireLogin()
	{
		$_SESSION['request_uri']=$_SERVER['REQUEST_URI'];
		header('Location: '.APP_ROOT.'login.php');
		exit;
	}

	public function isUserLoggedIn()
	{
		if(isset($_SESSION['user']) && $_SESSION['user']!='')
			return true;
		else
			return false;
	}

	public function getOriginalUser()
	{
		if(isset($_SESSION['user_original']))
			return $_SESSION['user_original'];
		else
			return $_SESSION['user'];
	}

	public function loginAsUser($username)
	{
		$_SESSION['user_original']=$_SESSION['user'];
		$_SESSION['user']=$username;
		session_regenerate_id();
		return true;
	}
	
	public function logout()
	{
		unset($_SESSION['user']);
		unset($_SESSION['user_original']);
		session_destroy();
		return true;
	}
}
require_once(dirname(__FILE__).'/'.AUTH_SYSTEM.'.class.php');

?>
