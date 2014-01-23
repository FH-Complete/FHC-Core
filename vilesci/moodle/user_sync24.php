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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 */
/**
 * Script zum manuellen synchronisieren der User in die Moodle Kurse 
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/moodle.class.php');
require_once('../../include/moodle24_user.class.php');
require_once('../../include/benutzerberechtigung.class.php');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/moodle'))
	die('Sie haben keine Berechtigung fuer diese Seite');


echo '<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Moodle 2.4 User Sync</title>
	<link rel="stylesheet" href="../../skin/fhcomplete.css" />
	<link rel="stylesheet" href="../../skin/vilesci.css" />
</head>
<body>
	<h1>Moodle 2.4 User Sync</h1>
	Auf dieser Seite können die Teilnehmer eines Moodle 2.4 Kurses aktualisiert werden.
	Geben Sie dazu die ID des Moodle Kurses ein.<br><br>
	<form method="POST" action="'.$_SERVER['PHP_SELF'].'">
	<table>
		<tr>
			<td>Moodle Kurs ID:</td>
			<td><input type="text" name="mdl_course_id" size="5" value="" /></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="User Zuteilung aktualisieren" name="sync" /></td>
		</tr>
	</table>
</form>
';
if(isset($_POST['sync']))
{
	if(isset($_POST['mdl_course_id']) && $_POST['mdl_course_id']!='' && is_numeric($_POST['mdl_course_id']))
	{
		$mdl_course_id = $_POST['mdl_course_id'];
		
		$moodle = new moodle24_user();
		echo '<br><h2>Übertrage LektorInnen</h2><br>';
		if($moodle->sync_lektoren($mdl_course_id))
		{
			echo $moodle->log;
		}
		else
			echo 'Fehler bei der Zuteilung:'.$moodle->errormsg;
		

		$moodle = new moodle24_user();
		echo '<br><h2>Übertrage Studierende</h2><br>';
		if($moodle->sync_studenten($mdl_course_id))
		{
			echo $moodle->log;
		}
		else
			echo 'Fehler bei der Zuteilung:'.$moodle->errormsg;
	}
	else
	{
		echo 'Fehler: Bitte füllen Sie alle Felder aus';
	}
}
echo '</body>
</html>';
?>
