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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/moodle.class.php');
require_once('../../../include/moodle19_course.class.php');
require_once('../../../include/moodle24_course.class.php');
require_once('../../../include/phrasen.class.php');

if (!$db = new basis_db())
	die('Fehler beim Herstellen der Datenbankverbindung');

$user = get_uid();

$p = new phrasen(getSprache());

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
<h1>'.$p->t('moodle/kursUebersicht').'</h1>

<table width="100%">
	<tr>
		<td>';

$moodle = new moodle();
$moodle->getAll($lvid, $stsem);

foreach ($moodle->result as $row)
{
	switch($row->moodle_version)
	{
		case '1.9':
			$mdlcourse19=new moodle19_course();
			$mdlcourse19->load($row->mdl_course_id);
			echo "<a href='".$moodle->getPfad($row->moodle_version)."course/view.php?id=".$row->mdl_course_id."' class='Item'>$mdlcourse19->mdl_fullname</a><br>";
			break;

		case '2.4':
			$mdlcourse24=new moodle24_course();
			$mdlcourse24->load($row->mdl_course_id);
			echo "<a href='".$moodle->getPfad($row->moodle_version)."course/view.php?id=".$row->mdl_course_id."' class='Item'>$mdlcourse24->mdl_fullname</a><br>";
			break;

		default:
			echo $p->t('moodle/ungueltigeVersion',array($row->moodle_version)).'<br>';
			break;
	}
}

echo '	</td>
	</tr>
</table>
</body>
</html>';
?>
