<?php
/* Copyright (C) 2016 fhcomplete.org
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
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
 /**
  * Script zur Vorrückung von LV-Informationen in das Folgesemester
  */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/organisationseinheit.class.php');
require_once('../../include/studienplan.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('lehre/studienplan', null, 's'))
{
	die($rechte->errormsg);
}

$berechtigteStgKz = $rechte->getStgKz('lehre/studienplan');

echo '<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css">

	<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script  type="text/javascript" src="../../vendor/rmariuzzo/jquery-checkboxes/dist/jquery.checkboxes-1.0.7.min.js"></script>
	<script type="text/javascript">
	$(document).ready(function()
	{
		$("#t1").tablesorter({
			sortList: [[1,0],[2,0]],
			widgets: ["zebra"],
			headers: {0:{sorter:false}}
			});

		$("#toggle_t1").on("click", function(e) {
			$("#t1").checkboxes("toggle");
			e.preventDefault();
		});

		$("#uncheck_t1").on("click", function(e) {
			$("#t1").checkboxes("uncheck");
			e.preventDefault();
		});

		$("#t1").checkboxes("range", true);
		
		$("#select_studiensemester_kurzbz_from").change(function()
		{
			var index = $(this).prop("selectedIndex");
			index = index+3;
			$("#select_studiensemester_kurzbz_to :nth-child("+index+")").prop("selected", true);
		});
	});
	</script>
	<title>Studienplan Semester Vorrückung</title>
</head>
<body>
<h1>Studienplan Semester Vorrückung</h1>
<p>Lädt alle Studienpläne, die für das Zielsemester noch keinen Eintrag im 1. Ausbildungssemester haben.<br>
Das Vorrücken kopiert die Semesterzuordnungen des Quellsemesters (aller Ausbildungssemester) ins Zielsemester</p>
';

$db = new basis_db();
$studiensemester_kurzbz_from = (isset($_POST['studiensemester_kurzbz_from'])?$_POST['studiensemester_kurzbz_from']:'');
$studiensemester_kurzbz_to = (isset($_POST['studiensemester_kurzbz_to'])?$_POST['studiensemester_kurzbz_to']:'');
$studienplaene = filter_input(INPUT_POST, 'studienplaene', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);
$anzahl_kopiert = 0;

if($studiensemester_kurzbz_from == '')
{
	$stsem = new studiensemester();
	$studiensemester_kurzbz_from = $stsem->getaktorNext();
}
if($studiensemester_kurzbz_to == '')
{
	$stsem = new studiensemester();
	$studiensemester_kurzbz_to = $stsem->jump($studiensemester_kurzbz_from, 2);
}

echo '<form action="studienplan_vorrueckung.php" method="POST">';

echo ' Quelle: <select id="select_studiensemester_kurzbz_from" name="studiensemester_kurzbz_from" />';

$stsem = new studiensemester();
$stsem->getPlusMinus(null,10,'ende ASC');

foreach($stsem->studiensemester as $row)
{
	if($row->studiensemester_kurzbz == $studiensemester_kurzbz_from)
		$selected = 'selected';
	else
		$selected = '';
	echo '<option value="'.$db->convert_html_chars($row->studiensemester_kurzbz).'" '.$selected.'>'.
			$db->convert_html_chars($row->studiensemester_kurzbz).
		'</option>';
}
echo '</select>';

echo ' Ziel:<select id="select_studiensemester_kurzbz_to" name="studiensemester_kurzbz_to" />';

foreach($stsem->studiensemester as $row)
{
	if($row->studiensemester_kurzbz == $studiensemester_kurzbz_to)
		$selected = 'selected';
	else
		$selected = '';
	echo '<option value="'.$db->convert_html_chars($row->studiensemester_kurzbz).'" '.$selected.'>'.
			$db->convert_html_chars($row->studiensemester_kurzbz).
		'</option>';
}
echo '</select>';
echo '<input type="submit" value="Anzeigen" name="show" />';
echo '</form>';

if(isset($_POST['vorruecken']) && !empty($studienplaene) && $studiensemester_kurzbz_to != '')
{
	if(!$rechte->isBerechtigt('lehre/studienplan', null, 'sui'))
	{
		die($rechte->errormsg);
	}
	$studienplan = new studienplan();
	foreach ($studienplaene AS $studienplan_id)
	{
		$ausbildungssemester = $studienplan->loadAusbildungsemesterFromStudiensemester($studienplan_id, $studiensemester_kurzbz_from);
		foreach ($ausbildungssemester AS $semester)
		{
			if ($studienplan->saveSemesterZuordnung(array(array(	"studienplan_id" => $studienplan_id,
				"studiensemester_kurzbz" => $studiensemester_kurzbz_to,
				"ausbildungssemester" => $semester))))
			{
				echo '<br><span style="color: green">Eintrag für Studienplan '.$studienplan_id.', '.$studiensemester_kurzbz_to.', '.$semester.'. Semester erstellt</span>';
			}
			else
			{
				echo '<br><span class="error">Fehler beim Speichern des Eintrags für Studienplan '.$studienplan_id.', '.$studiensemester_kurzbz_to.', '.$semester.'. Semester</span>';
				$studienplan->errormsg;
			}
		}
	}
}

if(isset($_POST['show']) && $studiensemester_kurzbz_from != '' && $studiensemester_kurzbz_to != '')
{
	$qry = "	SELECT UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) AS studiengang,
					studienplan.studienplan_id,
					studienplan.orgform_kurzbz,
					studienplan.bezeichnung,
					studienplan.sprache
				FROM lehre.tbl_studienplan studienplan
				JOIN lehre.tbl_studienplan_semester USING (studienplan_id)
				JOIN lehre.tbl_studienordnung sto USING (studienordnung_id)
				JOIN PUBLIC.tbl_studiengang USING (studiengang_kz)
				WHERE tbl_studienplan_semester.studiensemester_kurzbz = ".$db->db_add_param($studiensemester_kurzbz_from, FHC_STRING)."
					AND NOT EXISTS (
						SELECT 1
						FROM lehre.tbl_studienplan_semester
						JOIN lehre.tbl_studienplan USING (studienplan_id)
						WHERE studienplan_id = studienplan.studienplan_id
							AND orgform_kurzbz = studienplan.orgform_kurzbz
							AND studiensemester_kurzbz = ".$db->db_add_param($studiensemester_kurzbz_to, FHC_STRING)."
						)
					AND NOT EXISTS (
						SELECT 1
						FROM lehre.tbl_studienplan_semester
						JOIN lehre.tbl_studienplan USING (studienplan_id)
						JOIN lehre.tbl_studienordnung USING (studienordnung_id)
						WHERE orgform_kurzbz = studienplan.orgform_kurzbz
							AND studiensemester_kurzbz = ".$db->db_add_param($studiensemester_kurzbz_to, FHC_STRING)."
							AND studiengang_kz = sto.studiengang_kz
						)
					AND tbl_studiengang.typ IN ('b', 'm', 'l')
					AND tbl_studiengang.studiengang_kz IN (".implode(',',$berechtigteStgKz).")
					AND studienplan.onlinebewerbung_studienplan = true";
	if (substr($studiensemester_kurzbz_from,0,2) == 'SS')
	{
		$qry .= "   AND tbl_studienplan_semester.semester = 2";
	}
	else
	{
		$qry .= "   AND tbl_studienplan_semester.semester = 1";
	}
	$qry .= "   ORDER BY studiengang";
	
	if($result = $db->db_query($qry))
	{
		echo '<br>Anzahl: '.$db->db_num_rows($result);

		if ($db->db_num_rows($result) > 0)
		{
			echo '<form action="studienplan_vorrueckung.php" method="POST">';
			echo '<input type="hidden" name="show">';
			echo '<input type="hidden" value="'.$studiensemester_kurzbz_from.'" name="studiensemester_kurzbz_from">';
			echo '<input type="hidden" value="'.$studiensemester_kurzbz_to.'" name="studiensemester_kurzbz_to">';
			echo '<table id="t1" class="tablesorter" style="width: unset">
						<thead>
						<tr>
							<th style="text-align: center">
							<nobr>
								<a href="#" id="toggle_t1" data-toggle="checkboxes" data-action="toggle" ><img src="../../skin/images/checkbox_toggle.png" name="toggle"></a>
								<a href="#" id="uncheck_t1" data-toggle="checkboxes" data-action="uncheck" ><img src="../../skin/images/checkbox_uncheck.png" name="toggle"></a>
							</nobr>
							</th>
							<th>Studiengang</th>
							<th>Organisationsform</th>
							<th>Bezeichnung</th>
							<th>Sprache</th>
							<th>Studienplan ID</th>
						</tr>
						</thead>
						<tbody>';
			while ($row = $db->db_fetch_object($result))
			{
				echo '
						<tr>
							<td><input type="checkbox" class="chkbox" name="studienplaene[]" value="'.$row->studienplan_id.'" checked="checked"></td>
							<td>'.$row->studiengang.'</td>
							<td>'.$row->orgform_kurzbz.'</td>
							<td>'.$row->bezeichnung.'</td>
							<td>'.$row->sprache.'</td>
							<td>'.$row->studienplan_id.'</td>
							</tr>';
			}
			echo "</tbody></table>";
			if ($rechte->isBerechtigt('lehre/studienplan', null, 'sui'))
			{
				echo '<button type="submit" name="vorruecken">Ausgewählte Studienpläne vorrücken</button>';
			}
			else
			{
				echo '<button name="vorruecken" disabled>Ausgewählte Studienpläne vorrücken</button> Keine Berechtigung zum Vorrücken von Studienplänen';
			}

			echo '</form>';
		}
	}
}

echo '</body>
</html>';
