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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/*
 * Verlinkt zur Wartungsseite der verwendeten Moodle Version
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/moodle.class.php');
require_once('../../../include/phrasen.class.php');

$sprache = getSprache(); 
$p = new phrasen($sprache); 

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

if (!$user=get_uid())
	die($p->t('moodle/sieSindNichtAngemeldet').' !');
		
if(isset($_GET['lvid']) && is_numeric($_GET['lvid']))
	$lvid=$_GET['lvid'];
else 
	die($p->t('moodle/lvidMussUebergebenWerden'));
	
if(isset($_GET['stsem']) && check_stsem($_GET['stsem']))
	$stsem = $_GET['stsem'];
else 
	die($p->t('moodle/esWurdeKeinStudiensemesterUebergeben'));

$art = (isset($_POST['art'])?$_POST['art']:'lv');

$berechtigt = false;

//Pruefen ob Rechte fuer diese LV vorhanden sind
$qry = "SELECT distinct vorname, nachname, tbl_benutzer.uid as uid FROM lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, public.tbl_benutzer, public.tbl_person WHERE tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid=tbl_benutzer.uid AND tbl_person.person_id=tbl_benutzer.person_id AND lehrveranstaltung_id='$lvid' AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid NOT like '_Dummy%' AND tbl_person.aktiv=true AND studiensemester_kurzbz='$stsem' ORDER BY nachname, vorname";
if($result = $db->db_query($qry))
{
	while($row_lector = $db->db_fetch_object($result))
	{
		if($user==$row_lector->uid)
			$berechtigt=true;
	}
}

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if($rechte->isBerechtigt('admin'))
	$berechtigt=true;

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>
<body>
<h1>Moodle Versionswahl</h1>';
$moodle = new moodle();
if(!$moodle->getAll($lvid, $stsem))
	echo $moodle->errormsg;


if(isset($moodle->result[0]))
{
	// Wenn bereits ein Moodle Kurs vorhanden ist, wird auf die 
	// Wartungsseite der entsprechenden Version verlinkt.
	$moodle_version = $moodle->result[0]->moodle_version;

	if($moodle_version=='1.9')
		$link = 'moodle_wartung.php?lvid='.$db->convert_html_chars($lvid).'&stsem='.$db->convert_html_chars($stsem);
	elseif($moodle_version=='2.4')
		$link = 'moodle2_4_wartung.php?lvid='.$db->convert_html_chars($lvid).'&stsem='.$db->convert_html_chars($stsem);
	else
		die('Unbekannte Moodle Version gefunden');

	echo '<script language="javascript">window.location.href=\''.$link.'\';</script>';
	echo $p->t('moodle/weiterleitung', array($link));
}
else
{
	$link19 = 'moodle_wartung.php?lvid='.$db->convert_html_chars($lvid).'&stsem='.$db->convert_html_chars($stsem);
	$link24 = 'moodle2_4_wartung.php?lvid='.$db->convert_html_chars($lvid).'&stsem='.$db->convert_html_chars($stsem);
	echo $p->t('moodle/wartungschoice', array($link19, $link24));
}
echo '</body></html>';
?>
