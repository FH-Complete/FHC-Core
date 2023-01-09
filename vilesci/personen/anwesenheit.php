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
require_once('../../include/studiensemester.class.php');
require_once('../../include/lehreinheitmitarbeiter.class.php');

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

if($work=='getTermine')
{
	$stg = $_POST['stg'];
	$sem = $_POST['sem'];
	$stsem = $_POST['stsem'];
	$lv = $_POST['lv'];

	// Daten der Lehreinheiten ermitteln
	$qry = "SELECT
				le.lehreinheit_id, sp.ort_kurzbz, datum
			FROM
				lehre.tbl_lehreinheit le
				JOIN lehre.tbl_lehrveranstaltung lv ON lv.lehrveranstaltung_id = le.lehrveranstaltung_id
				JOIN lehre.tbl_stundenplan sp ON (sp.lehreinheit_id=le.lehreinheit_id)
			WHERE lv.studiengang_kz = " . $db->db_add_param($stg)."
			AND lv.lehrveranstaltung_id = " . $db->db_add_param($lv)."
			AND lv.semester = " . $db->db_add_param($sem)."
			AND le.studiensemester_kurzbz=".$db->db_add_param($stsem)." ORDER BY datum, stunde";

	$data = array();
	$lektoren=array();
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$paddedLehreinheitId = str_pad($row->lehreinheit_id, 6, "0", STR_PAD_LEFT);
			$id = date('ymd', strtotime($row->datum)) . $paddedLehreinheitId;

			if(!isset($lektoren[$row->lehreinheit_id]))
			{
				$le_obj = new lehreinheitmitarbeiter();
				$le_obj->getLehreinheitmitarbeiter($row->lehreinheit_id);
				$lektoren[$row->lehreinheit_id]='';
				foreach($le_obj->lehreinheitmitarbeiter as $row_lem)
				{
					$lektoren[$row->lehreinheit_id].=$row_lem->mitarbeiter_uid.' ';
				}
			}

			$data[$id]=$datum_obj->formatDatum($row->datum,'d.m.Y').' '.$lektoren[$row->lehreinheit_id];
		}
	}
	echo json_encode($data);
	exit;
}
if($work=='getLVs')
{
	$stg = $_POST['stg'];
	$sem = $_POST['sem'];
	$stsem = $_POST['stsem'];

	$lv = new lehrveranstaltung();
	$lv->load_lva_le($stg, $stsem, $sem);

	$data = array();
	foreach($lv->lehrveranstaltungen as $row)
	{
		$data[$row->lehrveranstaltung_id]=$row->bezeichnung;
	}
	echo json_encode($data);
	exit;
}

echo '<!DOCTYPE HTML>
<html>
	<head>
		<title>Anwesenheit</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css">
		<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
		<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css"/>

		<script type="text/javascript">
		$(document).ready(function() {
			if(document.getElementById("usercode"))
				document.getElementById("usercode").focus();
			else
				document.getElementById("lvcode").focus();

            // Tablesorter
            $("#t1").tablesorter(
			{
				sortList: [[4,0]],
				widgets: ["zebra"]
			});

            // Enter-Taste beim Scannen abfangen
            $("#usercode").keydown(function(event) {
                if (event.which == 13)
                    event.preventDefault();
            });
		});

		function inputUsercode()
		{
			var usercode = $("#usercode").val();
			if(usercode.length==12)
			{
				var person_id = parseInt(usercode, 10);
                person_id = person_id.toString();
                person_id = person_id.substring(0, person_id.length - 1);

				$("#img_"+person_id).attr("src","../../skin/images/false.png");
				var uid = $("#uid_"+person_id).val();
				$("#anwesenheit_"+person_id).val("false");
				$("#usercode").val("");
			}
		}
		function toggleAnwesenheit(person_id)
		{
			var uid = $("#uid_"+person_id).val();
			var wert = $("#anwesenheit_"+person_id).val();

			if(wert=="true")
			{
				$("#img_"+person_id).attr("src","../../skin/images/false.png");
				$("#anwesenheit_"+person_id).val("false");
			}
			else
			{
				$("#img_"+person_id).attr("src","../../skin/images/true.png");
				$("#anwesenheit_"+person_id).val("true");
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
		if(strstr($key, 'uid_'))
		{
			$person_id = mb_substr($key, mb_strlen('uid_'));
			$user = $_POST['uid_'.$person_id];
			$anwesend = $_POST['anwesenheit_'.$person_id];
			$anwesenheit_id=$_POST['anwesenheitid_'.$person_id];
			$anwesenheit = new anwesenheit();

			if($anwesenheit_id!='')
			{
				if(!$anwesenheit->load($anwesenheit_id))
					die('Es ist ein Fehler beim Laden der Daten aufgetreten: '.$anwesenheit->errormsg.' Bitte versuchen Sie es erneut');
			}
			else
			{
				// Wenn der Eintrag bereits exisitiert aber kein Update durchgefuehrt wird, dann wird der Eintrag uebersprungen
				// da der Eintrag sonst doppelt vorhanden ist.
				// zB bei Reload der Seite oder schliessen und erneuten oeffnen des Browsers und Absenden der POST Daten
				if($anwesenheit->AnwesenheitEntryExists($_POST['lehreinheit_id'], $_POST['datum'], $user))
				{
					echo $anwesenheit->convert_html_chars($user)." wird übersprungen da der Eintrag bereits erfasst wurde<br>";
					continue;
				}
			}

			$anwesenheit->uid = $user;
			$anwesenheit->einheiten = $_POST['einheiten'];
			$anwesenheit->lehreinheit_id = $_POST['lehreinheit_id'];
			$anwesenheit->datum = $_POST['datum'];
			$anwesenheit->anwesend=($anwesend=='true'?true:false);
			$anwesenheit->anmerkung = $_POST['anmerkung_'.$person_id];
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
							<input type="hidden" name="anwesenheitid_'.$row->person_id.'" value="'.$anwesenheit_id.'" />
							<input type="hidden" name="anwesenheit_'.$row->person_id.'" id="anwesenheit_'.$row->person_id.'" value="'.($anwesend?'true':'false').'" />
							<input type="hidden" name="uid_'.$row->person_id.'" id="uid_'.$row->person_id.'" value="'.$row->uid.'" />
						</td>';
					echo '<td>'.$row->person_id.'</td>';
					echo '<td>'.$row->uid.'</td><td>'.$row->vorname.'</td><td>'.$row->nachname.'</td>';
					echo '<td><input type="text" name="anmerkung_'.$row->person_id.'" value="'.$db->convert_html_chars($anmerkung).'" /></td>';
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
	</form>
	';


	$studiengang_kz='';
	$semester='';
	$studiensemester_kurzbz='';
	$lv_id='';

	echo '<br><hr><br>
	<form name="sendform" action="'.$_SERVER["PHP_SELF"].'" method="post">
	<input type="hidden" name="work" value="loadAnwesenheit" />';
	$studiengang = new studiengang();
	$studiengang->getAll('typ,kurzbz');
	echo 'Studiengang <select name="studiengang" id="studiengang" style="width:250px"  onchange="loadListe()">';
	foreach($studiengang->result as $row)
	{
		if($studiengang_kz=='')
			$studiengang_kz=$row->studiengang_kz;

		echo '<option value="'.$row->studiengang_kz.'">'.$row->kuerzel.' -'.$row->bezeichnung.'</option>';
	}
	echo '</select>';

	echo 'Semester <select name="semester" id="semester"  onchange="loadListe()">';
	for($i=1;$i<=10;$i++)
	{
		if($semester=='')
			$semester = $i;
		echo '<option value="'.$i.'">'.$i.'</option>';
	}
	echo '</select>';

	$stsem = new studiensemester();
	$akt = $stsem->getAktOrNext();
	$stsem->getAll();
	echo 'Studiensemester <select name="stsem" id="stsem" onchange="loadListe()">';
	foreach($stsem->studiensemester as $row)
	{
		if($studiensemester_kurzbz=='')
			$studiensemester_kurzbz=$row->studiensemester_kurzbz;

		if($row->studiensemester_kurzbz==$akt)
			$selected='selected';
		else
			$selected='';
		echo '<option value="'.$row->studiensemester_kurzbz.'" '.$selected.'>'.$row->studiensemester_kurzbz.'</option>';
	}
	echo '</select>';

	$lv = new lehrveranstaltung();
	$lv->load_lva_le($studiengang_kz, $studiensemester_kurzbz, $semester);
	echo 'LV <select name="lv" id="lv" onchange="loadListe(\'lv\')">
		<option value="">--Auswahl--</option>';
	foreach($lv->lehrveranstaltungen as $row)
	{
		if($lv_id=='')
			$lv_id=$row->lehrveranstaltung_id;
		echo '<option value="'.$row->lehrveranstaltung_id.'">'.$row->bezeichnung.'</option>';
	}
	echo '</select>';

	echo 'Termin <select name="lvcode" id="termine" >';

	echo '</select>
	<input type="submit" />';

	echo '<script>
	function loadListe(action)
	{
		var stg = $("#studiengang").val();
		var sem = $("#semester").val();
		var stsem = $("#stsem").val();
		var lv = $("#lv").val();

		if(action=="lv" && lv!="")
		{
			// Termine holen
			data = {
				stg: stg,
				sem: sem,
				stsem: stsem,
				lv: lv,
				work: "getTermine"
			};

			$.ajax({
				url: "anwesenheit.php",
				data: data,
				type: "POST",
				dataType: "json",
				success: function(data)
				{
					$("#termine").empty();
					$("#termine").append(\'<option value="">-- Auswahl --</option>\');
					$.each(data, function(i, entry){
						$("#termine").append(\'<option value="\'+i+\'">\'+entry+\'</option>\');
					});
				},
				error: function(data)
				{
					alert("Fehler beim Laden der Daten");
				}
			});
		}
		else
		{
			// LV holen
			data = {
				stg: stg,
				sem: sem,
				stsem: stsem,
				work: "getLVs"
			};

			$.ajax({
				url: "anwesenheit.php",
				data: data,
				type: "POST",
				dataType: "json",
				success: function(data)
				{
					$("#lv").empty();
					$("#lv").append(\'<option value="">-- Auswahl --</option>\');
					$.each(data, function(i, entry){
						$("#lv").append(\'<option value="\'+i+\'">\'+entry+\'</option>\');
					});
				},
				error: function(data)
				{
					alert("Fehler beim Laden der Daten");
				}
			});
		}
	}
	</script>';
}
echo '
</body>
</html>';
?>
