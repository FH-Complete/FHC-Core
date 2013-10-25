<?php
/* Copyright (C) 2013 fhcomplete.org
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
 * Script um eine Person gleichzeitig zu mehreren Moodle Kursen zuzuteilen
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
	<title>Moodle 2.4 Rollenzuteilung</title>
	<link rel="stylesheet" href="../../skin/fhcomplete.css" />
	<link rel="stylesheet" href="../../skin/vilesci.css" />
</head>
<body>
	<h1>Moodle Rollenzuteilung</h1>
	<form method="POST" action="'.$_SERVER['PHP_SELF'].'">
	<table>
		<tr>
			<td>Moodle Kurs IDs getrennt mit \',\':</td>
			<td><input type="text" name="mdl_course_ids" value="" /></td>
		</tr>
		<tr>
			<td>Rolle</td>
			<td>
				<SELECT name="role">
					<OPTION value="3">Lektor/in</OPTION>
					<OPTION value="5">Student/in</OPTION>
					<OPTION value="4">Tutor/in</OPTION>
					<OPTION value="">----</OPTION>
					<OPTION value="1">Manager</OPTION>
					<OPTION value="2">Course Creator</OPTION>
					<OPTION value="6">Guest</OPTION>
					<OPTION value="7">User</OPTION>
					<OPTION value="8">frontpage</OPTION>
				</SELECT>
			</td>
		</tr>
		<tr>
			<td>UID</td>
			<td><input type="text" name="uid" id="uid"/></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="Zuteilen" name="add" /></td>
		</tr>
	</table>
</form>
';
if(isset($_POST['add']))
{
	if(isset($_POST['uid']) && $_POST['uid']!='' &&
		isset($_POST['role']) && $_POST['role']!='' &&
		isset($_POST['mdl_course_ids']) && $_POST['mdl_course_ids']!='')
	{
		$mdl_course_id_array = explode(',',$_POST['mdl_course_ids']);
		$uid = $_POST['uid'];
		$role_id=$_POST['role'];

		$moodle = new moodle24_user();
		if($moodle->MassEnroll($uid, $mdl_course_id_array, $role_id))
		{
			echo 'Zuteilung erfolgreich';
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
