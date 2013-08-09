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

class authentication extends auth
{

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
				return mb_strtolower($_SESSION['user']);
			else
				return $this->RequireLogin();
		}
	}

	// derzeit checkldapuser in functions.inc.php bzw per htaccess
	public function checkpassword($username, $passwort)
	{
		if($connect=ldap_connect(LDAP_SERVER))
		{
			ldap_set_option($connect, LDAP_OPT_REFERRALS,0);
			ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION,3);

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
				$this->errormsg='LDAP Bind fehlgeschlagen: '.ldap_error($connect);
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

	// derzeit manual_basic_auth in functions.inc.php eventuell 
	// direkt von getUser aus aufrufen wenn nicht authentifiziert
	public function RequireLogin()
	{
		if(!(isset($_SERVER['PHP_AUTH_USER']) && $this->checkpassword($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])))
		{
			header('WWW-Authenticate: Basic realm="'.AUTH_NAME.'"');
			header('HTTP/1.0 401 Unauthorized');
			echo "Ihre Zugangsdaten sind ungueltig!";
			exit;
		}
		else
		{
			return mb_strtolower($_SERVER['PHP_AUTH_USER']);
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
