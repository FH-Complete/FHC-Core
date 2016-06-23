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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
 */
/**
 * GUI zum verlängern der Zutrittskarte
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
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
	<script type="text/javascript" src="../../include/js/jquery.js"></script>
	<link href="../../skin/tablesort.css" rel="stylesheet" type="text/css"/>
	<script type="text/javascript">
		$(document).ready(function()
		{
			$("#t1").tablesorter(
			{
				sortList: [[0,0]],
				widgets: ["zebra"]
			});
		});
	</script>
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
			$prestudent = new prestudent();
			$prestudent->getPrestudentsFromUid($karten_user);


			if(count($prestudent->result) > 0)
			{
				//echo '<br><b>Student</b><br>';
				echo '<b>UID:</b> '.$karten_user.'<br>';

				echo '<table id="t1" class="tablesorter">';
				echo '<thead>';
				echo '<tr>';
				echo '<th>Studiengang</th>';
				echo '<th>Semester</th>';
				echo '<th>Aktueller Status</th>';
				echo '<th>Studiengebühr</th>';
				echo '</tr>';
				echo '</thead>';
				echo '<tbody>';

				foreach($prestudent->result as $ps)
				{
					echo '<tr>';

					$stg = new studiengang();
					$stg->load($ps->studiengang_kz);
					echo '<td>'.$stg->kuerzel.' - '.$stg->bezeichnung.'</td>';

					$stsem = new studiensemester();
					$akt = $stsem->getaktorNext();
					$ps->load_studentlehrverband($akt);
					echo '<td>'.$ps->semester.'</td>';

					$ps->getLastStatus($ps->prestudent_id);
					echo '<td>'.$ps->status_kurzbz.'</td>';

					$konto = new konto();

					if($konto->checkStudienbeitrag($karten_user, $akt))
						echo '<td class="ok">'.$akt.' bezahlt</td>';

					else
						echo '<td class="error">'.$akt.' noch nicht bezahlt</td>';

					echo '</tr>';
				}
				echo '</tbody>';
				echo '</table>';
			}


			echo '
					</td>
				</tr>
			</table><br>';
			
			
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
