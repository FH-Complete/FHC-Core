<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 */ 
/**
 * Script um mehrere User auf einmal im Moodle anzulegen
 * Die UID der User die angelegt werden sollen, werden in einem Textfeld uebergeben
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/moodle24_user.class.php');
require_once('../../include/benutzerberechtigung.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/moodle'))
	die('Sie haben keine Berechtigung für diese Seite');

if (!$db = new basis_db())
	die('Fehler bei der Datenbankverbindung');

$userliste = (isset($_POST['userliste'])?trim($_POST['userliste']):'');
$messages='';

if($userliste!='')
{
	$moodle = new moodle24_user();	

	$uids = explode("\n",$userliste);
	foreach($uids as $uid)
	{
		$uid=trim($uid);
		// Check ob User nicht bereits angelegt ist
		if (!$moodle->loaduser($uid))
		{
			//  User ist noch nicht in Moodle angelegt => Neuanlage
			if (!$moodle->createUser($uid))
					$messages.=$moodle->errormsg.'X'.$uid.'X';
			else
				$messages.='<br>User '.$uid.' angelegt';
		}
		else
			$messages.='<br>User '.$uid.' bereits vorhanden';
	}
}
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Moodle 2.4 - Accountverwaltung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body>
<h2>Moodle - User anlegen</h2>
	<form name="createuser" method="POST" action="'.$_SERVER["PHP_SELF"].'" target="_self">
  		Bitte geben sie die UIDs der Personen die im Moodle angelegt werden sollen ein (ein User pro Zeile):<br>
  		<textarea name="userliste" cols="32" rows="20"></textarea>
  		<input type="submit" value="Anlegen">
  	</form>	';
echo $messages;
echo '
</body>
	</html>';
?>
