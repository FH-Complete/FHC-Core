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
 * GUI zum Tauschen der Zutrittskarte
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
	<title>Kartentausch</title>
</head>
<body>
<h2>Zutrittskarte - Tauschen der Karte</h2>';

if(!$rechte->isBerechtigt('basis/fhausweis', 'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

$db = new basis_db();
$kartennummer_alt = (isset($_POST['kartennummer_alt'])?$_POST['kartennummer_alt']:'');
$karten_user = (isset($_POST['karten_user'])?$_POST['karten_user']:'');
$kartennummer_hitag = (isset($_POST['kartennummer_hitag'])?$_POST['kartennummer_hitag']:'');
$action=(isset($_POST['action'])?$_POST['action']:'');

if($action=='kartentausch')
{
	echo '<br>Tausche Karte von User: '.$db->convert_html_chars($karten_user);
	echo ' '.$db->convert_html_chars($kartennummer_alt);
	echo ' -> '.$db->convert_html_chars($kartennummer_hitag);
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
			if($bmp->nummer2=='' && $kartennummer_alt!=$kartennummer_hitag)
			{
				// Alte Karte und Neue Karte vermutlich vertauscht -> Abbruch
				echo '<span class="error">Fehler beim Tauschen - Eventuell wurden alte und neue Karte vertauscht!? Bitte tauschen Sie die Karte erneut.</span>';
				$error = true;	
			}
			else
			{
				$bmp->ausgegebenam=date('Y-m-d');
				$bmp->updateamum = date('Y-m-d H:i:s');
				$bmp->updatevon = $uid;
				
				if(!$bmp->save(false))
				{
					echo '<span class="error">Fehler beim Tauschen: '.$bmp->errormsg.'</span>';
					$error=true;
				}
			}
		}
		else
		{
			echo '<span class="error">Fehler beim Tauschen: Die neue Karte wurde dieser Person noch nicht zugeordnet</span>';
			$error = true;
		}
		if($kartennummer_alt!=$kartennummer_hitag)
		{
			if(!$error)
			{
				if($kartennummer_alt!='')
				{
					//Alte Karte deaktivieren wenn vorhanden
					$bmp = new betriebsmittelperson();
					if($bmp->getKartenzuordnung($kartennummer_alt))
					{
						if($bmp->person_id==$benutzer->person_id)
						{
							$bmp->retouram = date('Y-m-d');
							if(!$bmp->save(false))
							{
								echo '<span class="error">Fehler beim Eintragen des Retourdatums</span>';
								$error=true;
							}
						}
						else
						{
							echo '<span class="error">Karte passt nicht zur Person</span>';
							$error=true;
						}
					}
					else
					{
						echo '<span class="error">Kartenzuordnung der alten Karte nicht gefunden</span>';
						$error=true;
					}
				}
			}
			if(!$error)
			{
				echo '<span class="ok">Karte erfolgreich getauscht</span>';
			}	
		}
		else
		{
			echo '<span class="ok">Karte wurde aktiviert</span>';
		}
				
	}
	$kartennummer_alt='';
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
			Alte Kartennummer: 
			<input type="text" id="kartennummer_alt" name="kartennummer_alt" value="'.$db->convert_html_chars($kartennummer_alt).'" />
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
	
	$kartennummer_alt = $bm->transform_kartennummer($kartennummer_alt);
	echo 'Suche User mit der Kartennummer '.$db->convert_html_chars($kartennummer_alt).'<br>';	
	if(!$karten_user = getUidFromCardNumber($kartennummer_alt))
	{
		$bmp = new betriebsmittelperson();
		if($bmp->getKartenzuordnung($kartennummer_alt))
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
if($action=='sucheUser')
{
	echo '<span class="ok">Bei direkten Zugriff auf die Person muss die alte Karte manuell entfernt werden!</span>';
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
		<input type="hidden" name="kartennummer_alt" value="'.$db->convert_html_chars($kartennummer_alt).'" />
		<table>
		<tr>
			<td>Kartennummer Neu</td>
			<td>
				<input type="text" value="" name="kartennummer_hitag" id="kartennummer_hitag"/>
				<script type="text/javascript">
				$(document).ready(function() 
				{
					$("#kartennummer_hitag").focus();
				});
				</script>
			</td>
			<td><div id="hitag_description"></div></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="Zuteilen" /></td>
		</tr>
		</table>
		
		</form>
		<script type="text/javascript">
		function checkValues()
		{
			var hitag = document.getElementById("kartennummer_hitag");
			
			if($("#kartennummer_hitag").val()=="")
			{
				$("#hitag_description").text("Ziehen Sie die neue Karten über Lesegerät 1");
				$("#kartennummer_hitag").focus();
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
	echo '<br><b>Bitte ziehen Sie die alte Karte über den Kartenleser</b>
	<script type="text/javascript">
		$(document).ready(function() 
		{
			$("#kartennummer_alt").val("");
			$("#kartennummer_alt").focus();
		});
	</script>
	';
}
echo '</body>
</html>';
?>