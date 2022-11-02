<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */
/**
 * GUI zum verlängern der Zutrittskarte
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/betriebsmittel.class.php');
require_once('../../include/betriebsmittelperson.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/konto.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/prestudent.class.php');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../skin/jquery.css" type="text/css"/>
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
	<title>Kartenverlängerung</title>
</head>
<body>
<h2>Zutrittskarte - Verlängerung der Karte</h2>';

if(!$rechte->isBerechtigt('basis/fhausweis', 'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

$db = new basis_db();
$kartennummer = (isset($_POST['kartennummer'])?$_POST['kartennummer']:'');
$karten_user = (isset($_POST['karten_user'])?$_POST['karten_user']:'');
$action=(isset($_POST['action'])?$_POST['action']:'');

$studiensemester = new studiensemester();
$stsem = $studiensemester->getaktorNext();
$studiensemester->load($stsem);

// Wenn ende des Semesters näher als 2 Monate ist
// Wird das folgende Semester geholt, sonst das aktuelle 
$dtobj = new DateTime($studiensemester->ende);
$dtobj->sub(new DateInterval('P2M'));
$now = new DateTime("now");

if($dtobj<$now)
{
	$stsem = $studiensemester->getNextFrom($stsem);
}

echo '
<table>
	<tr>
		<td nowrap>
			<form action="'.$_SERVER['PHP_SELF'].'" METHOD="POST">
			<input type="hidden" name="action" value="sucheKarte" />
			Kartennummer: 
			<input type="text" id="kartennummer" name="kartennummer" value="'.$db->convert_html_chars($kartennummer).'" />
			<input type="submit" name="suchen" value="Suchen" />
			</form>
		</td>
		<td width="80%">
		</td>
		<td nowrap>
			<form action="'.$_SERVER['PHP_SELF'].'" METHOD="POST">
			<input type="hidden" name="action" value="sucheUser" />
			UID: 
			<input type="text" id="karten_user" name="karten_user" value="'.$db->convert_html_chars($karten_user).'" />
			<input type="submit" name="suchen" value="Suchen" />
			</form>
		</td>
	</tr>
</table>
';

if($action=='sucheKarte')
{
	$bm = new betriebsmittel();
	$bmp = new betriebsmittelperson();
	
	$kartennummer = $bm->transform_kartennummer($kartennummer);
	echo 'Suche User mit der Kartennummer '.$db->convert_html_chars($kartennummer).'<br>';	
	if(!$karten_user = getUidFromCardNumber($kartennummer))
	{
		$bmp = new betriebsmittelperson();
		if($bmp->getKartenzuordnung($kartennummer))
		{
			if($bmp->uid!='')
				$karten_user=$bmp->uid;
			else
			{
				echo '<span class="error">Diese Karte ist derzeit nicht ausgegeben</span>';
			}
		}
		else
		{
			if($karten_user=='')
				echo '<span class="error">Diese Karte ist derzeit nicht ausgegeben</span>';
		}
	}
}

if($karten_user!='')
{
	echo '<br><br>';
	$benutzer = new benutzer();
	if($benutzer->load($karten_user))
	{
		echo '
		<center>
		<table>
			<tr>
				<td>
					<img src="../../content/bild.php?src=person&person_id='.$benutzer->person_id.'" height="100px" width="75px"/>
				</td>
				<td>
					<b>Vorname:</b> '.$db->convert_html_chars($benutzer->vorname).'<br>
					<b>Nachname:</b> '.$db->convert_html_chars($benutzer->nachname).'<br>';
		
		if(check_lektor($karten_user))
		{
			//Mitarbeiter
			echo '<br><b>Mitarbeiter - keine Verlängerung nötig</b><br>';
			echo '
					</td>
				</tr>
			</table><br></center>';
		}
		else
		{
			//Student
			$student = new student();
			if($student->load($karten_user))
			{
				$stg = new studiengang();
				$stg->load($student->studiengang_kz);
				//echo '<br><b>Student</b><br>';
				echo '<b>UID:</b> '.$karten_user.'<br>';
				echo '<b>Studiengang:</b> '.$stg->kuerzel.' - '.$stg->bezeichnung.'<br>';
				echo '<b>Semester:</b> '.$student->semester.'<br>';
				
			}
			$prestudent = new prestudent();
			$prestudent->getLastStatus($student->prestudent_id);
			echo '<b>Aktueller Status:</b> '.$prestudent->status_kurzbz;
		
			echo '
					</td>
				</tr>
			</table><br>';
			
			$konto = new konto();
			if($konto->checkStudienbeitrag($karten_user, $stsem))
			{
				echo '<span class="ok" style="font-size: large">Studiengebühr für '.$stsem.' bezahlt</span>';
			}
			else
				echo '<span class="error" style="font-size: large">Studiengebühr für '.$stsem.' noch nicht bezahlt</span>';
			
			
			echo '
			</center>
		';
		}
	}
	else
	{
		echo '<span class="error">Fehler beim Laden des Users</span>';
	}
}

	echo '<br><b>Bitte ziehen Sie die Karte über den Hitag Kartenleser</b>
	<script type="text/javascript">
		$(document).ready(function() 
		{
			$("#kartennummer").val("");
			$("#kartennummer").focus();
		});
	</script>
	';

echo '</body>
</html>';
?>