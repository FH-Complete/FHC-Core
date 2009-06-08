<?php
/* Copyright (C) 2006 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/*
 *
 */
require_once('../../config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/moodle_course.class.php');
require_once('../../../include/moodle_user.class.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Connecten zur DB');

//$conn_moodle='';
if(!$conn_moodle = pg_pconnect(CONN_STRING_MOODLE))
	die('Fehler beim Connecten zur DB');

$user = get_uid();

if(isset($_GET['lvid']))
	$lvid=$_GET['lvid'];
else 
	die('lvid muss uebergeben werden');
	
if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else 
	die('Es wurde kein Studiensemester uebergeben');
	
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>
<body>
<table class="tabcontent" height="100%" id="inhalt">
	<tr>
		<td class="tdwidth10">&nbsp;</td>
		<td class="ContentHeader"><font class="ContentHeader">MOODLE Kurse</font></td>
    </tr>
    <tr>
    	<td class="tdvertical">&nbsp;</td>
        <td></td>
	</tr>
	<tr>
		<td class="tdvertical">&nbsp;</td>
		<td class="tdvertical">
		
		<table width="100%">
			<tr>
				<td>';

$mdlcourse = new moodle_course($conn, $conn_moodle);
$mdlcourse->getAll($lvid, $stsem);

foreach ($mdlcourse->result as $row)
{
	echo "<a href='".MOODLE_PATH."course/view.php?id=".$row->mdl_course_id."' class='Item'>$row->mdl_fullname</a><br>";
}

echo '			</td>
			</tr>
		</table>
		</td>
	</tr>
</table>
</body>
</html>';
?>