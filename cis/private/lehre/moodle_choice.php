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
require_once('../../../include/lehreinheit.class.php');

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

echo '<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>
<body>
<h1>'.$p->t('moodle/kursUebersicht').'</h1>
';

$moodle = new moodle();
$moodle->getAll($lvid, $stsem);

$meinekurse = '';
$allgemeinekurse = '';

foreach ($moodle->result as $row)
{
	$kurs = '';
	switch($row->moodle_version)
	{
		case '1.9':
			$mdlcourse19=new moodle19_course();
			$mdlcourse19->load($row->mdl_course_id);
			$bezeichnung = $mdlcourse19->mdl_fullname;
			if($bezeichnung=='')
				$bezeichnung = 'Course '.$row->mdl_course_id;
			$kurs = "<a href='".$moodle->getPfad($row->moodle_version)."course/view.php?id=".$row->mdl_course_id."' class='Item'>$bezeichnung</a><br>";
			break;

		case '2.4':
			$mdlcourse24=new moodle24_course();
			$mdlcourse24->load($row->mdl_course_id);
			$bezeichnung = $mdlcourse24->mdl_fullname;
			if($bezeichnung=='')
				$bezeichnung = 'Course '.$row->mdl_course_id;
			$kurs = "<a href='".$moodle->getPfad($row->moodle_version)."course/view.php?id=".$row->mdl_course_id."' class='Item'>$bezeichnung</a><br>";
			break;

		default:
			$kurs = $p->t('moodle/ungueltigeVersion',array($row->moodle_version)).'<br>';
			break;
	}
	if($row->lehreinheit_id!='')
	{
		$le = new lehreinheit();
		$stud = $le->getStudenten($row->lehreinheit_id);
		$zugeordnet = false;

		foreach($stud as $row_stud)
		{
			if($row_stud->uid == $user)
			{
				$zugeordnet = true;
				break;
			}
		}

		if($zugeordnet)
		{
			$meinekurse .= $kurs;
		}
	}
	$allgemeinekurse .= $kurs;
}

if($meinekurse!='')
{
	echo '<h2>'.$p->t('moodle/meineKurse').'</h2>';
	echo $meinekurse;
}

echo '<br><br><h2>'.$p->t('moodle/vorhandeneKurse').'</h2>';

echo $allgemeinekurse;

echo '</body>
</html>';
?>
