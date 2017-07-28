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
 * Verwaltungsseite zum Anlegen von Moodle Kursen
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/lehreinheit.class.php');
require_once('../../../include/lehreinheitgruppe.class.php');
require_once('../../../include/lehreinheitmitarbeiter.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/moodle.class.php');
require_once('../../../include/moodle19_course.class.php');
require_once('../../../include/moodle19_user.class.php');
require_once('../../../include/moodle24_course.class.php');
require_once('../../../include/moodle24_user.class.php');

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

if(!$berechtigt)
	die('Sie haben keine Berechtigung für diese Seite');

$lv = new lehrveranstaltung();
$lv->load($lvid);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
<script language="Javascript">
<!--
function togglediv()
{
	var block = "table-row";
	if (navigator.appName.indexOf("Microsoft") > -1)
		block = "block";

	if(document.getElementById("radiole").checked)
		document.getElementById("lehreinheitencheckboxen").style.display = block;
	else
		document.getElementById("lehreinheitencheckboxen").style.display = "none";
}
-->
</script>
</head>
<body onload="togglediv()">
<h1>'.$lv->bezeichnung.'&nbsp;('.$stsem.')</h1>
<table width="100%" >
	<tr>
		<td valign="top">';

if(isset($_POST['neu']))
{
	if($_POST['bezeichnung']=='')
	{
		echo '<span class="error">'.$p->t('benotungstool/bezeichnungMussEingegebenWerden').'</span><br>';
	}
	else
	{
		$lehrveranstaltung = new lehrveranstaltung();
		$lehrveranstaltung->load($lvid);
		$studiengang = new studiengang();
		$studiengang->load($lehrveranstaltung->studiengang_kz);

		$orgform = ($lehrveranstaltung->orgform_kurzbz!=''?$lehrveranstaltung->orgform_kurzbz:$studiengang->orgform_kurzbz);

		//Kurzbezeichnung generieren Format: STSEM-STG-SEM-LV/LEID/LEID/LEID...
		//$shortname = $stsem.'-'.$studiengang->kuerzel.'-'.$lehrveranstaltung->semester.'-'.$lehrveranstaltung->kurzbz;
		$shortname = $studiengang->kuerzel.'-'.$orgform.'-'.$lehrveranstaltung->semester.'-'.$stsem.'-'.$lehrveranstaltung->kurzbz;

		//Gesamte LV zu einem Moodle Kurs zusammenlegen
		if($art=='lv')
		{
			$mdl_course = new moodle19_course();

			$mdl_course->lehrveranstaltung_id = $lvid;
			$mdl_course->studiensemester_kurzbz = $stsem;
			$mdl_course->mdl_fullname = $_POST['bezeichnung'];
			$mdl_course->mdl_shortname = $shortname;
			$mdl_course->insertamum = date('Y-m-d H:i:s');
			$mdl_course->insertvon = $user;
			$mdl_course->gruppen = isset($_POST['gruppen']);

			//Moodlekurs anlegen
			if($mdl_course->create_moodle())
			{
				//Eintrag in der Vilesci DB
				$mdl_course->create_vilesci();

				$mdl_user = new moodle19_user();
				//Lektoren Synchronisieren
				if(!$mdl_user->sync_lektoren($mdl_course->mdl_course_id))
					echo $mdl_user->errormsg;

				$mdl_user = new moodle19_user();
				//Studenten Synchronisieren
				if(!$mdl_user->sync_studenten($mdl_course->mdl_course_id))
					echo $mdl_user->errormsg;
			}
			else
			{
				echo $mdl_course->errormsg;
			}
		}
		elseif($art=='le') //Getrennte Kurse fuer die Lehreinheiten
		{
			$lehreinheiten=array();

			foreach ($_POST as $key=>$value)
			{
				if(mb_strstr($key, 'lehreinheit_'))
				{
					$shortname.='/'.$value;
					$lehreinheiten[]=$value;
				}
			}

			if(count($lehreinheiten)>0)
			{
				$mdl_course = new moodle19_course();

				$mdl_course->mdl_fullname = $_POST['bezeichnung'];
				$mdl_course->mdl_shortname = $shortname;
				$mdl_course->studiensemester_kurzbz = $stsem;
				$mdl_course->insertamum = date('Y-m-d H:i:s');
				$mdl_course->insertvon = $user;
				$mdl_course->lehreinheit_id=$lehreinheiten[0];
				$mdl_course->gruppen = isset($_POST['gruppen']);

				//Kurs im Moodle anlegen
				if($mdl_course->create_moodle())
				{
					//fuer jede Lehreinheit einen Eintrag in VilesciDB anlegen
					foreach ($lehreinheiten as $value)
					{
						$mdl_course->lehreinheit_id = $value;
						if(!$mdl_course->create_vilesci())
							echo '<br>'.$p->t('moodle/fehlerBeimAnlegenAufgetreten').':'.$mdl_course->errormsg;
					}

					$mdl_user = new moodle19_user();
					//Lektoren Synchronisieren
					if(!$mdl_user->sync_lektoren($mdl_course->mdl_course_id))
						echo $mdl_user->errormsg;

					$mdl_user = new moodle19_user();
					//Studenten Synchronisieren
					if(!$mdl_user->sync_studenten($mdl_course->mdl_course_id))
						echo $mdl_user->errormsg;
				}
			}
			else
			{
				echo '<span class="error">'.$p->t('moodle/esMussMindestensEineLehreinheitMarkiertSein').'</span><br>';
			}
		}
		else
			die($p->t('moodle/artIstUnbekannt'));
	}
}
//Gruppen Syncro ein/aus schalten
if(isset($_POST['changegruppe']))
{
	if(isset($_POST['moodle_id']) && is_numeric($_POST['moodle_id']))
	{
		$mcourse = new moodle19_course();
		if($mcourse->updateGruppenSync($_POST['moodle_id'], isset($_POST['gruppen'])))
			echo '<b>'.$p->t('moodle/datenWurdenAktualisiert').'</b><br>';
		else
			echo '<span class="error">'.$p->t('global/fehlerBeimAktualisierenDerDaten').'</span>';
	}
	else
	{
		echo '<span class="error">'.$p->t('moodle/esWurdeKeineGueltigeIdUebergeben').'</span>';
	}
}

//Anlegen eines Testkurses
if(isset($_GET['action']) && $_GET['action']=='createtestkurs')
{
	$mdl_course = new moodle19_course();
	if(!$mdl_course->loadTestkurs($lvid, $stsem))
	{
		$lehrveranstaltung = new lehrveranstaltung();
		$lehrveranstaltung->load($lvid);
		$studiengang = new studiengang();
		$studiengang->load($lehrveranstaltung->studiengang_kz);

		//$orgform = ($lehrveranstaltung->orgform_kurzbz!=''?$lehrveranstaltung->orgform_kurzbz:$studiengang->orgform_kurzbz);

		//Kurzbezeichnung generieren Format: STSEM-STG-SEM-LV/LEID/LEID/LEID...
		$shortname = 'TK-'.$stsem.'-'.$studiengang->kuerzel.'-'.$lehrveranstaltung->semester.'-'.$lehrveranstaltung->kurzbz;

		$mdl_course->lehrveranstaltung_id = $lvid;
		$mdl_course->studiensemester_kurzbz = $stsem;
		$mdl_course->mdl_fullname = 'Testkurs - '.$lehrveranstaltung->bezeichnung;
		$mdl_course->mdl_shortname = $shortname;

		//TestKurs erstellen
		if($mdl_course->createTestkurs($lvid, $stsem))
		{
			$id=$mdl_course->mdl_course_id;
			$errormsg='';

			$mdl_user = new moodle19_user();
			//Lektoren zuweisen
			if(!$mdl_user->sync_lektoren($id, $lvid, $stsem))
				$errormsg.=$p->t('moodle/fehlerBeiDerLektorenZuordnung').':'.$mdl_user->errormsg.'<br>';
			//Teststudenten zuweisen
			if(!$mdl_user->createTestStudentenZuordnung($id))
				$errormsg.=$p->t('moodle/fehlerBeiDerStudentenZuordnung').':'.$mdl_user->errormsg.'<br>';

			if($errormsg!='')
				echo $errormsg;
			else
				echo '<b>'.$p->t('moodle/testkursWurdeErfolgreichAngelegt').'</b><br>';
		}
	}
	else
	{
		echo '<span class="error">'.$p->t('moodle/esExistiertBereitsEinTestkurs').'</span><br>';
	}
}

//Anlegen eines Testkurses fuer Moodle 24
if(isset($_GET['action']) && $_GET['action']=='createtestkurs24')
{
	$mdl_course24 = new moodle24_course();
	if(!$mdl_course24->loadTestkurs($lvid, $stsem))
	{
		$lehrveranstaltung = new lehrveranstaltung();
		$lehrveranstaltung->load($lvid);
		$studiengang = new studiengang();
		$studiengang->load($lehrveranstaltung->studiengang_kz);

		//$orgform = ($lehrveranstaltung->orgform_kurzbz!=''?$lehrveranstaltung->orgform_kurzbz:$studiengang->orgform_kurzbz);

		//Kurzbezeichnung generieren Format: STSEM-STG-SEM-LV/LEID/LEID/LEID...
		$shortname = 'TK-'.$stsem.'-'.$studiengang->kuerzel.'-'.$lehrveranstaltung->semester.'-'.$lehrveranstaltung->kurzbz;

		$mdl_course24->lehrveranstaltung_id = $lvid;
		$mdl_course24->studiensemester_kurzbz = $stsem;
		$mdl_course24->mdl_fullname = 'Testkurs - '.$lehrveranstaltung->bezeichnung;
		$mdl_course24->mdl_shortname = $shortname;

		//TestKurs erstellen
		if($mdl_course24->createTestkurs($lvid, $stsem))
		{
			$id=$mdl_course24->mdl_course_id;
			$errormsg='';

			$mdl_user24 = new moodle24_user();
			//Lektoren zuweisen
			if(!$mdl_user24->sync_lektoren($id, $lvid, $stsem))
				$errormsg.=$p->t('moodle/fehlerBeiDerLektorenZuordnung').':'.$mdl_user24->errormsg.'<br>';
			//Teststudenten zuweisen
			if(!$mdl_user24->createTestStudentenZuordnung($id))
				$errormsg.=$p->t('moodle/fehlerBeiDerStudentenZuordnung').':'.$mdl_user24->errormsg.'<br>';

			if($errormsg!='')
				echo $errormsg;
			else
				echo '<b>'.$p->t('moodle/testkursWurdeErfolgreichAngelegt').'</b><br>';
		}
	}
	else
	{
		echo '<span class="error">'.$p->t('moodle/esExistiertBereitsEinTestkurs').'</span><br>';
	}
}

$moodle = new moodle();

// Pruefen ob bereits fuer alle Lehreinheiten oder fuer die gesamte LV ein Moodle Kurs angelegt ist
if($moodle->course_exists_for_lv($lvid, $stsem) || $moodle->course_exists_for_allLE($lvid, $stsem))
{
	echo $p->t('moodle/esIstBereitsEinMoodleKursVorhanden');
}
else
{
	//wenn bereits ein Moodle Kurs fuer eine Lehreinheit angelegt wurde, dann dass
	//anlegen fuer die Lehrveranstaltung verhindern
	$qry = "SELECT 1 FROM lehre.tbl_moodle
			WHERE lehreinheit_id in(SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
									WHERE lehrveranstaltung_id=".$db->db_add_param($lvid)."
									AND studiensemester_kurzbz=".$db->db_add_param($stsem).")";
	$disable_lv='';
	if($result = $db->db_query($qry))
		if($db->db_num_rows($result)>0)
		{
			$disable_lv='disabled="true"';
			//wenn schon ein Moodle Kurs zu einer Lehreinheit angelegt wurde,
			//dann ist standardmaessig die Lehreinheit markiert
			if($art=='lv')
				$art='le';
		}

	echo '<b>'.$p->t('moodle/moodleKursAnlegen').': </b><br><br>
			<form action="'.$_SERVER['PHP_SELF'].'?lvid='.$lvid.'&stsem='.$stsem.'" method="POST">
			<input type="radio" '.$disable_lv.' name="art" value="lv" onclick="togglediv()" '.($art=='lv'?'checked':'').'>einen Moodle Kurs f&uuml;r die gesamte LV anlegen<br>
			<input type="radio" id="radiole" name="art" value="le" onclick="togglediv()" '.($art=='le'?'checked':'').'>einen Moodle Kurs für einzelne Lehreinheiten anlegen
		  ';

	$le = new lehreinheit();
	$le->load_lehreinheiten($lv->lehrveranstaltung_id, $stsem);
	echo '<div id="lehreinheitencheckboxen" style="display:none">';
	foreach ($le->lehreinheiten as $row)
	{
		//Gruppen laden
		$gruppen = '';

		$lehreinheitgruppe = new lehreinheitgruppe();
		$lehreinheitgruppe->getLehreinheitgruppe($row->lehreinheit_id);
		foreach ($lehreinheitgruppe->lehreinheitgruppe as $grp)
		{
			if($grp->gruppe_kurzbz=='')
				$gruppen.=' '.$grp->semester.$grp->verband.$grp->gruppe;
			else
				$gruppen.=' '.$grp->gruppe_kurzbz;
		}

		//Lektoren laden
		$lektoren = '';
		$lehreinheitmitarbeiter = new lehreinheitmitarbeiter();
		$lehreinheitmitarbeiter->getLehreinheitmitarbeiter($row->lehreinheit_id);

		foreach ($lehreinheitmitarbeiter->lehreinheitmitarbeiter as $ma)
		{
			$lektoren.= ' '.$ma->mitarbeiter_uid;
		}

		if($moodle->course_exists_for_le($row->lehreinheit_id))
			$disabled='disabled';
		else
			$disabled='';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="checkbox" name="lehreinheit_'.$row->lehreinheit_id.'" value="'.$row->lehreinheit_id.'" '.$disabled.'>'.$row->lehrform_kurzbz.' '.$gruppen.' '.$lektoren;
		echo '<br>';
	}
	echo '</div>';

	$studiengang = new studiengang();
	$studiengang->load($lv->studiengang_kz);
	$orgform = ($lv->orgform_kurzbz!=''?$lv->orgform_kurzbz:$studiengang->orgform_kurzbz);
	$longbezeichnung = $studiengang->kuerzel.'-'.$orgform.'-'.$lv->semester.'-'.$stsem.' - '.$lv->bezeichnung;

	echo '<br>'.$p->t('moodle/kursbezeichnung').': <input type="text" name="bezeichnung" maxlength="254" size="40" value="'.$longbezeichnung.'">';
	echo '<br>'.$p->t('moodle/gruppenUebernehmen').': <input type="checkbox" name="gruppen" checked>';
	echo '<br><br><input type="submit" name="neu" value="'.$p->t('moodle/kursAnlegen').'">
			</form>';
}
echo '</td>';

echo '<td valign="top">';
echo '<b>'.$p->t('moodle/vorhandeneMoodleKurse').'</b>';
if(!$moodle->getAll($lvid, $stsem))
	echo $moodle->errormsg;
echo '<table>';
foreach ($moodle->result as $course)
{
	switch($course->moodle_version)
	{
		case '1.9':
			$moodlecourse = new moodle19_course();
			$moodlecourse->load($course->mdl_course_id);
			echo '<tr>';
			echo '<td><a href="'.$moodle->getPfad($course->moodle_version).'course/view.php?id='.$course->mdl_course_id.'" class="Item" target="_blank">'.$moodlecourse->mdl_fullname.'</a></td>';
//			echo "<td nowrap><form action='".$_SERVER['PHP_SELF']."?lvid=$lvid&stsem=$stsem' method='POST' style='margin:0px'><input type='hidden' name='moodle_id' value='$course->moodle_id'><input type='checkbox' name='gruppen' ".($course->gruppen?'checked':'').">Gruppen übernehmen <input type='submit' value='".$p->t('global/ok')."' name='changegruppe'></form></td>";
			echo '</tr>';
			break;

		case '2.4':
			$moodlecourse = new moodle24_course();
			$moodlecourse->load($course->mdl_course_id);
			echo '<tr>';
			echo '<td><a href="'.$moodle->getPfad($course->moodle_version).'course/view.php?id='.$course->mdl_course_id.'" class="Item" target="_blank">'.$moodlecourse->mdl_fullname.'</a> (v2.4)</td>';
			echo '</tr>';
			break;

		default:
			// andere Moodle Version
			echo '<tr><td>Unknown Moodle Version - ID '.$course->mdl_course_id.'</td></tr>';
	}
}
echo '</table>';
echo '</td></tr></table>';

echo '<br><br><br>';
echo '<b>'.$p->t('moodle/testkurse').'</b><br><br>';
$mdlcourse = new moodle19_course();
if($mdlcourse->loadTestkurs($lvid, $stsem))
{
	echo '<a href="'.$moodle->getPfad('1.9').'course/view.php?id='.$mdlcourse->mdl_course_id.'" class="Item" target="_blank">'.$mdlcourse->mdl_fullname.'</a>';
}
else
{
	echo "<a href='".$_SERVER['PHP_SELF']."?lvid=$lvid&stsem=$stsem&action=createtestkurs' class='Item'>".$p->t('moodle/klickenSieHierUmTestkursErstellen')."</a>";
}

echo '<br><br><hr><b>'.$p->t('moodle/testkurse24').'</b><br><br>';
$mdlcourse24 = new moodle24_course();
if($mdlcourse24->loadTestkurs($lvid, $stsem))
{
	echo '<a href="'.$moodle->getPfad('2.4').'course/view.php?id='.$mdlcourse24->mdl_course_id.'" class="Item" target="_blank">'.$mdlcourse24->mdl_fullname.'</a>';
}
else
{
	echo "<a href='".$_SERVER['PHP_SELF']."?lvid=$lvid&stsem=$stsem&action=createtestkurs24' class='Item'>".$p->t('moodle/klickenSieHierUmTestkursErstellen24')."</a>";
}
echo '
</body>
</html>';
?>
