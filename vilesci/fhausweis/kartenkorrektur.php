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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Seite zur Korrektur der Mifare Nummer
 * Karten bei denen die Mifare Nummer nicht korrekt gespeichert wurde, kann über diese Seite korrigiert werden
 * Dabei wird die Karte zuerst über den Hitag Kartenleser gezogen, der User wird angezeigt
 * Danach wird die Karte über den Mifare Leser gezogen und die neue Mifare Nummer gespeichert
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
	<title>Kartenkorrektur</title>
</head>
<body>
<h2>Zutrittskarte - Zuordnungskorrektur</h2>';

if(!$rechte->isBerechtigt('basis/fhausweis', 'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

$db = new basis_db();
$kartennummer_hitag = (isset($_POST['kartennummer_hitag'])?$_POST['kartennummer_hitag']:'');
$karten_user = (isset($_POST['karten_user'])?$_POST['karten_user']:'');
$kartennummer_mifare = (isset($_POST['kartennummer_mifare'])?$_POST['kartennummer_mifare']:'');
$action=(isset($_POST['action'])?$_POST['action']:'');

if($action=='kartentausch')
{
	echo '<br>Korrigiere Karte von User: '.$db->convert_html_chars($karten_user);
	echo ' '.$db->convert_html_chars($kartennummer_hitag).' -> '.$db->convert_html_chars($kartennummer_mifare);
	echo '<br>';
	
	$benutzer = new benutzer();
	if(!$benutzer->load($karten_user))
	{
		echo '<span class="error">Fehler beim Laden des Benutzers</span>';
	}
	else
	{
		$error=false;
		//Neue Karte aktivieren
		$bmp = new betriebsmittelperson();
		if($bmp->getKartenzuordnungPerson($benutzer->person_id, $kartennummer_hitag))
		{
			$bm = new betriebsmittel();
			if($bm->load($bmp->betriebsmittel_id))
			{
				$bm->updateamum = date('Y-m-d H:i:s');
				$bm->updatevon = $uid;
				$bm->nummer2=$kartennummer_mifare;

				if(!$bm->save(false))
				{
					echo '<span class="error">Fehler beim Speichern: '.$bm->errormsg.'</span>';
					$error=true;
				}
				else
					echo '<span class="ok">Karte erfolgreich korrigiert</span>';
			}
		}
		else
		{
			echo '<span class="error">Fehler Kartenzuordnung wurde nicht gefunden</span>';
			$error = true;
		}		
	}
	$kartennummer_mifare='';
	$karten_user='';
	$kartennummer_hitag='';
	
	echo '<br><hr><br>';
}

echo '
<table>
	<tr>
		<td nowrap>
			<form action="'.$_SERVER['PHP_SELF'].'" METHOD="POST">
			<input type="hidden" name="action" value="sucheKarte" />
			Hitag Kartennummer: 
			<input type="text" id="kartennummer_hitag" name="kartennummer_hitag" value="'.$db->convert_html_chars($kartennummer_hitag).'" />
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
	
	$kartennummer_hitag = $bm->transform_kartennummer($kartennummer_hitag);
	echo 'Suche User mit der Kartennummer '.$db->convert_html_chars($kartennummer_hitag).'<br>';	
	if(!$karten_user = getUidFromCardNumber($kartennummer_hitag))
	{
		$bmp = new betriebsmittelperson();
		if($bmp->getKartenzuordnung($kartennummer_hitag))
		{
			if($bmp->uid!='')
				$karten_user=$bmp->uid;
			else
			{
				echo '<span class="error">Diese Karte ist derzeit nicht ausgegeben - Bitte an den Support wenden</span>';
			}
		}
		else
		{
			if($karten_user=='')
				echo '<span class="error">Diese Karte ist derzeit nicht ausgegeben - Bitte an den Support wenden</span>';
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
			echo '<br><b>Mitarbeiter</b><br>';
		}
		else
		{
			//Student
			$student = new student();
			if($student->load($karten_user))
			{
				$stg = new studiengang();
				$stg->load($student->studiengang_kz);
				echo '<br><b>Student</b><br>';
				echo '<b>Studiengang:</b> '.$stg->kuerzel.' - '.$stg->bezeichnung.'<br>';
				echo '<b>Semester:</b> '.$student->semester.'<br>';
			}
		}
			
		echo '
				</td>
			</tr>
		</table>
		';
		echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" onsubmit="return checkValues()">
		<input type="hidden" name="action" value="kartentausch" />
		<input type="hidden" name="karten_user" value="'.$db->convert_html_chars($benutzer->uid).'" />
		<input type="hidden" name="kartennummer_hitag" value="'.$db->convert_html_chars($kartennummer_hitag).'" />
		<table>
		<tr>
			<td>Kartennummer Mifare</td>
			<td>
				<input type="text" value="" name="kartennummer_mifare" id="kartennummer_mifare"/>
				<script type="text/javascript">
				$(document).ready(function() 
				{
					$("#kartennummer_mifare").focus();
				});
				</script>
			</td>
			<td><div id="mifare_description"></div></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="Korrigieren" /></td>
		</tr>
		</table>
		
		</form>
		<script type="text/javascript">
		function checkValues()
		{
			var hitag = document.getElementById("kartennummer_hitag");
			
			if($("#kartennummer_hitag").val()=="")
			{
				$("#mifare_description").text("Ziehen Sie die Karten über den Mifare Leser");
				$("#kartennummer_mifare").focus();
				return false;
			}
			
			return true;				
		}
		
		</script>'; 
	}
	else
	{
		echo '<span class="error">Fehler beim Laden des Users</span>';
	}
}
else
{
	echo '<br><b>Bitte ziehen Sie die Karte über den Hitag Kartenleser</b>
	<script type="text/javascript">
		$(document).ready(function() 
		{
			$("#kartennummer_hitag").val("");
			$("#kartennummer_hitag").focus();
		});
	</script>
	';
}
echo '</body>
</html>';
?>