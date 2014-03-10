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

abstract class auth extends basis
{
	abstract function getUser();
	abstract function checkpassword($username, $passwort);
	abstract function UserExternalExists($username);
	abstract function RequireLogin(); 
	abstract function isUserLoggedIn();
	abstract function getOriginalUser();
	abstract function login($username);
	abstract function loginAsUser($username);
	abstract function logout();
}
require_once(dirname(__FILE__).'/'.AUTH_SYSTEM.'.class.php');

?>
