<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 */
/**
 * Anwesenheit
 * 
 * Erfasst die Anwesenheiten der Studierenden
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/lehreinheit.class.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/anwesenheit.class.php');

if (!$uid = get_uid())
	die('Keine UID gefunden!');

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$db = new basis_db();
$datum_obj = new datum();

if(!$rechte->isBerechtigt('basis/person', null, 'suid'))
	die('Sie haben keine Berechtigung für diese Seite');


if(isset($_REQUEST['work']))
	$work = $_REQUEST['work'];
else
	$work='';

echo '<!DOCTYPE HTML>
<html>
	<head>
		<title>Anwesenheit</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css">
		<script type="text/javascript" src="../../include/js/jquery1.9.min.js"></script>
		<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css"/>

		<script type="text/javascript">
		$(document).ready(function() {
			if(document.getElementById("usercode"))
				document.getElementById("usercode").focus();
			else
				document.getElementById("lvcode").focus();
		})

		$(document).ready(function() 
		{ 
			$("#t1").tablesorter(
			{
				sortList: [[4,1]],
				widgets: ["zebra"]
			});
		});
		
		function inputUsercode()
		{
			var usercode = $("#usercode").val();
			if(usercode.length==13)
			{
				var person_id = parseInt(usercode, 10);

				$("#img_"+person_id).attr("src","../../skin/images/false.png");
				var uid = $("#uid_"+person_id).val();
				$("#anwesenheit_"+uid).val("false");
				$("#usercode").val("");
			}
		}
		function toggleAnwesenheit(person_id)
		{
			var uid = $("#uid_"+person_id).val();
			var wert = $("#anwesenheit_"+uid).val();

			if(wert=="true")
			{
				$("#img_"+person_id).attr("src","../../skin/images/false.png");
				$("#anwesenheit_"+uid).val("false");
			}
			else
			{
				$("#img_"+person_id).attr("src","../../skin/images/true.png");
				$("#anwesenheit_"+uid).val("true");
			}
			return false;
		}
		</script>
	</head>
	<body>
';

if($work=='save')
{
	foreach($_POST as $key=>$value)
	{
		if(strstr($key, 'anwesenheit_'))
		{
			$user = mb_substr($key, mb_strlen('anwesenheit_'));

			$anwesenheit_id=$_POST['anwesenheitid_'.$user];

			$anwesenheit = new anwesenheit();			

			if($anwesenheit_id!='')
			{
				$anwesenheit->load($anwesenheit_id);
			}

			$anwesenheit->uid = $user;
			$anwesenheit->einheiten = $_POST['einheiten'];
			$anwesenheit->lehreinheit_id = $_POST['lehreinheit_id'];
			$anwesenheit->datum = $_POST['datum'];
			$anwesenheit->anwesend=($value=='true'?true:false);
			$anwesenheit->anmerkung = $_POST['anmerkung_'.$user];
			$anwesenheit->save();
		}
	}
	echo 'Daten wurden gespeichert';
	$work='';
}

if($work=='loadAnwesenheit')
{
	if(!isset($_POST['lvcode']))
		die('Parameter ungueltig');

	$lvcode = $_POST['lvcode'];

	//echo 'LVCode:'.$lvcode;


	$datum = mb_substr($lvcode,0,6);
	$lehreinheit_id = mb_substr($lvcode,6,6);

	$datum = '20'.substr($datum,0,2).'-'.substr($datum,2,2).'-'.substr($datum,4,2);
	//echo '<br>LehreinheitID:'.ltrim($lehreinheit_id,0);
	//echo '<br>Datum:'.$datum;

	// Bereits eingetragene Anwesenheiten fuer diese Lehreinheit/Datum
	$anwesenheit = new anwesenheit();
	$anwesenheit->getAnwesenheitLehreinheit($lehreinheit_id, $datum);

	$aw_arr=array();
	foreach($anwesenheit->result as $row)
	{
		$aw_arr[$row->uid]=$row;
	}

	$lehreinheit = new lehreinheit();
	if($lehreinheit->load($lehreinheit_id))
	{
		$lehrveranstaltung = new lehrveranstaltung();
		if($lehrveranstaltung->load($lehreinheit->lehrveranstaltung_id))
		{
			// Anzahl der Einheiten ermitteln
			$qry = "SELECT distinct stunde 
					FROM 
						lehre.tbl_stundenplan 
					WHERE 
						lehreinheit_id=".$db->db_add_param($lehreinheit_id)." 
						AND datum=".$db->db_add_param($datum).";";
			if($result = $db->db_query($qry))
			{
				$einheiten = $db->db_num_rows($result);
			}

			echo '<h2>Lehrveranstaltung: '.$lehrveranstaltung->bezeichnung.' ('.$datum_obj->formatDatum($datum,'d.m.Y').' - '.$einheiten.' Einheiten)</h2>';

			if(count($aw_arr)>0)
			{
				echo '<span class="error">Achtung - diese Liste wurde bereits erfasst!</span>';
			}
			echo '<form action="'.$_SERVER['PHP_SELF'].'?work=save" method="POST" >';
			echo 'Bitte scannen Sie alle Barcodes der Studierenden die NICHT anwesend waren und speichern sie danach die Daten<br>';
			echo '<input type="text" id="usercode" name="usercode" value="" oninput="inputUsercode()">';
			echo '<input type="hidden" name="einheiten" value="'.$einheiten.'"/>';
			echo '<input type="hidden" name="lehreinheit_id" value="'.$lehreinheit_id.'" />';
			echo '<input type="hidden" name="datum" value="'.$datum.'" />';

			echo '<br><br><input type="submit" name="text" value="Speichern" />';

			// Teilnehmer ermitteln 
			$qry = "SELECT distinct uid, vorname, nachname, person_id
					FROM 
						campus.vw_student_lehrveranstaltung
						JOIN public.tbl_benutzer USING(uid)
						JOIN public.tbl_person USING(person_id) WHERE lehreinheit_id=".$db->db_add_param($lehreinheit_id);

			if($result = $db->db_query($qry))
			{
				$anzahl = $db->db_num_rows($result);
				echo '<h3>'.$anzahl.' Teilnehmer:</h3>';
				echo '<table id="t1" class="tablesorter" style="width:auto">
						<thead>
						<tr>
							<th>Anwesend</th>
							<th>PersonID</th>
							<th>UID</th>
							<th>Vorname</th>
							<th>Nachname</th>
							<th>Anmerkung</th>
						</tr>
						</thead>
						<tbody>';

				while($row = $db->db_fetch_object($result))
				{
					if(isset($aw_arr[$row->uid]))
					{
						$anwesenheit_id=$aw_arr[$row->uid]->anwesenheit_id;
						$anwesend = $aw_arr[$row->uid]->anwesend;
						$anmerkung = $aw_arr[$row->uid]->anmerkung;
					}
					else
					{
						$anwesenheit_id='';
						$anwesend=true;
						$anmerkung='';
					}
					echo '<tr>';
					echo '<td align="center">
							<a href="#Toggle" onclick="toggleAnwesenheit(\''.$row->person_id.'\')">
								<img id="img_'.$row->person_id.'" src="../../skin/images/'.($anwesend?'true':'false').'.png">
							</a>
							<input type="hidden" name="anwesenheitid_'.$row->uid.'" value="'.$anwesenheit_id.'" />
							<input type="hidden" name="anwesenheit_'.$row->uid.'" id="anwesenheit_'.$row->uid.'" value="'.($anwesend?'true':'false').'" />
							<input type="hidden" name="uid_'.$row->person_id.'" id="uid_'.$row->person_id.'" value="'.$row->uid.'" />
						</td>';
					echo '<td>'.$row->person_id.'</td>';
					echo '<td>'.$row->uid.'</td><td>'.$row->vorname.'</td><td>'.$row->nachname.'</td>';
					echo '<td><input type="text" name="anmerkung_'.$row->uid.'" value="'.$db->convert_html_chars($anmerkung).'" /></td>';
					echo '</tr>';
				}

				echo '</tbody></table>';
				echo '</form>';
			}
			else
				echo 'Fehler beim Laden der Teilnehmer';
		}
		else
			echo 'Fehler beim Laden der Lehrveranstaltung';
	}
	else
		echo 'Fehler beim Laden der Lehreinheit';
}

if($work=='')
{
	echo '<h1>Anwesenheit</h1>';

	echo '<form name="sendform" action="'.$_SERVER["PHP_SELF"].'" method="post">
	Bitte scannen Sie den Lehreinheiten Barcode<br>
	<input type="text" id="lvcode" name="lvcode" value="" size="13"/>
	<input type="hidden" name="work" value="loadAnwesenheit" />
	</form>';
}
echo '
</body>
</html>';
?>
