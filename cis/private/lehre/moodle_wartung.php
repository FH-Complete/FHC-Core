<?php
/* Copyright (C) 2008 Technikum-Wien
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
require_once('../../config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/moodle_course.class.php');
require_once('../../../include/moodle_user.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/lehreinheit.class.php');
require_once('../../../include/lehreinheitgruppe.class.php');
require_once('../../../include/lehreinheitmitarbeiter.class.php');
require_once('../../../include/studiengang.class.php');

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

$art = (isset($_POST['art'])?$_POST['art']:'le');

$berechtigt = false;

//Pruefen ob Rechte fuer diese LV vorhanden sind
$qry = "SELECT distinct vorname, nachname, tbl_benutzer.uid as uid FROM lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, public.tbl_benutzer, public.tbl_person WHERE tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid=tbl_benutzer.uid AND tbl_person.person_id=tbl_benutzer.person_id AND lehrveranstaltung_id='$lvid' AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid NOT like '_Dummy%' AND tbl_person.aktiv=true AND studiensemester_kurzbz='$stsem' ORDER BY nachname, vorname";

if($result = pg_query($conn, $qry))
{
	while($row_lector = pg_fetch_object($result))
	{
		if($user==$row_lector->uid)
			$berechtigt=true;
	}
}

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

if($rechte->isBerechtigt('admin'))
	$berechtigt=true;

$lv = new lehrveranstaltung($conn);
$lv->load($lvid);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
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
<table class="tabcontent" height="100%" id="inhalt">
	<tr>
		<td class="tdwidth10">&nbsp;</td>
		<td class="ContentHeader"><font class="ContentHeader">'.$lv->bezeichnung.'&nbsp;('.$stsem.')</font></td>
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
if(isset($_POST['neu']))
{
	if($_POST['bezeichnung']=='')
	{
		echo '<span class="error">Bezeichnung muss angegeben werden</span><br>';
	}
	else 
	{
		$lehrveranstaltung = new lehrveranstaltung($conn);
		$lehrveranstaltung->load($lvid);
		$studiengang = new studiengang($conn);
		$studiengang->load($lehrveranstaltung->studiengang_kz);
		
		//Kurzbezeichnung generieren Format: STSEM-STG-SEM-LV/LEID/LEID/LEID...
		$shortname = $stsem.'-'.$studiengang->kuerzel.'-'.$lehrveranstaltung->semester.'-'.$lehrveranstaltung->kurzbz;
		foreach ($_POST as $key=>$value)
		{
			if(strstr($key, 'lehreinheit_'))
			{
				$shortname.='/'.$value;
			}
		}
		//Gesamte LV zu einem Moodle Kurs zusammenlegen
		if($art=='lv')
		{
			$mdl_course = new moodle_course($conn, $conn_moodle);
			
			$mdl_course->lehrveranstaltung_id = $lvid;
			$mdl_course->studiensemester_kurzbz = $stsem;
			$mdl_course->mdl_fullname = $_POST['bezeichnung'];
			$mdl_course->mdl_shortname = $shortname;
			$mdl_course->insertamum = date('Y-m-d H:i:s');
			$mdl_course->insertvon = $user;
			
			//Moodlekurs anlegen
			if($mdl_course->create_moodle())
			{
				//Eintrag in der Vilesci DB
				$mdl_course->create_vilesci();
	
				$mdl_user = new moodle_user($conn, $conn_moodle);
				//Lektoren Synchronisieren
				if(!$mdl_user->sync_lektoren($mdl_course->mdl_course_id))
					echo $mdl_user->errormsg;
					
				$mdl_user = new moodle_user($conn, $conn_moodle);
				//Studenten Synchronisieren
				if(!$mdl_user->sync_studenten($mdl_course->mdl_course_id))
					echo $mdl_user->errormsg;
			}
		}
		elseif($art=='le') //Getrennte Kurse fuer die Lehreinheiten
		{
			$lehreinheiten=array();
			
			foreach ($_POST as $key=>$value)
			{
				if(strstr($key, 'lehreinheit_'))
				{
					$lehreinheiten[]=$value;
				}
			}
			
			if(count($lehreinheiten)>0)
			{
				$mdl_course = new moodle_course($conn, $conn_moodle);
				
				$mdl_course->mdl_fullname = $_POST['bezeichnung'];
				$mdl_course->mdl_shortname = $shortname;
				$mdl_course->studiensemester_kurzbz = $stsem;
				$mdl_course->insertamum = date('Y-m-d H:i:s');
				$mdl_course->insertvon = $user;
				$mdl_course->lehreinheit_id=$lehreinheiten[0];
	
				//Kurs im Moodle anlegen
				if($mdl_course->create_moodle())
				{
					//fuer jede Lehreinheit einen Eintrag in VilesciDB anlegen
					foreach ($lehreinheiten as $value)
					{
						$mdl_course->lehreinheit_id = $value;
						if(!$mdl_course->create_vilesci())
							echo '<br>Fehler beim Anlegen:'.$mdl_course->errormsg;
					}
					
					$mdl_user = new moodle_user($conn, $conn_moodle);
					//Lektoren Synchronisieren
					if(!$mdl_user->sync_lektoren($mdl_course->mdl_course_id))
						echo $mdl_user->errormsg;
					
					$mdl_user = new moodle_user($conn, $conn_moodle);	
					//Studenten Synchronisieren
					if(!$mdl_user->sync_studenten($mdl_course->mdl_course_id))
						echo $mdl_user->errormsg;
				}
			}
			else 
			{
				echo '<span class="error">Es muss mindestens eine Lehreinheit markiert sein</span><br>';
			}
		}
		else 
			die('art ist unbekannt');
	}
}

$mdl_course = new moodle_course($conn, $conn_moodle);
if($mdl_course->course_exists_for_lv($lvid, $stsem) || $mdl_course->course_exists_for_allLE($lvid, $stsem))
{
	echo 'Es ist bereits ein Moodle Kurs für die Gesamt LV vorhanden';
}
else 
{
	//wenn bereits ein Moodle Kurs fuer eine Lehreinheit angelegt wurde, dann dass
	//anlegen fuer die Lehrveranstaltung verhindern
	$qry = "SELECT 1 FROM lehre.tbl_moodle 
			WHERE lehreinheit_id in(SELECT lehreinheit_id FROM lehre.tbl_lehreinheit 
									WHERE lehrveranstaltung_id='".addslashes($lvid)."' 
									AND studiensemester_kurzbz='".addslashes($stsem)."')";
	$disable_lv='';
	if($result = pg_query($conn, $qry))
		if(pg_num_rows($result)>0)
			$disable_lv='disabled="true"';
			
	echo 'Moodle Kurs anlegen: <br><br>
			<form action="'.$_SERVER['PHP_SELF'].'?lvid='.$lvid.'&stsem='.$stsem.'" method="POST">
			<input type="radio" '.$disable_lv.' name="art" value="lv" onclick="togglediv()" '.($art=='lv'?'checked':'').'>einen Moodle Kurs f&uuml;r die gesamte LV anlegen<br>
			<input type="radio" id="radiole" name="art" value="le" onclick="togglediv()" '.($art=='le'?'checked':'').'>einen Moodle Kurs für einzelne Lehreinheiten anlegen
		  ';
	
	$le = new lehreinheit($conn);
	$le->load_lehreinheiten($lv->lehrveranstaltung_id, $stsem);
	echo '<div id="lehreinheitencheckboxen" style="display:none">';
	foreach ($le->lehreinheiten as $row)
	{
		//Gruppen laden
		$gruppen = '';
		
		$lehreinheitgruppe = new lehreinheitgruppe($conn);
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
		$lehreinheitmitarbeiter = new lehreinheitmitarbeiter($conn);
		$lehreinheitmitarbeiter->getLehreinheitmitarbeiter($row->lehreinheit_id);
		
		foreach ($lehreinheitmitarbeiter->lehreinheitmitarbeiter as $ma)
		{
			$lektoren.= ' '.$ma->mitarbeiter_uid;
		}
		
		if($mdl_course->course_exists_for_le($row->lehreinheit_id))
			$disabled='disabled';
		else 
			$disabled='';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="checkbox" name="lehreinheit_'.$row->lehreinheit_id.'" value="'.$row->lehreinheit_id.'" '.$disabled.'>'.$row->lehrform_kurzbz.' '.$gruppen.' '.$lektoren;
		echo '<br>';
	}
	echo '</div>';
	
	echo '<br>Kursbezeichnung: <input type="text" name="bezeichnung" maxlength="254" size="40" value="'.$lv->bezeichnung.'"><br><br>';
	echo '<input type="submit" name="neu" value="Kurs anlegen">
			</form>';
}
echo '</td>';

echo '<td valign="top">';
echo '<b>Vorhandene Moodle Kurse für diese LV</b>';
if(!$mdl_course->getAll($lvid, $stsem))
	echo $mdl_course->errormsg;
echo '<br>';
foreach ($mdl_course->result as $course)
{
	echo '<br><a href="'.MOODLE_PATH.'course/view.php?id='.$course->mdl_course_id.'" class="Item" target="_blank">'.$course->mdl_fullname.'</a>';
}
echo '</td></tr></table>';



echo '</td>
	</tr>
</table>
</body>
</html>';
?>