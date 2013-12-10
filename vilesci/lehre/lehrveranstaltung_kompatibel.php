<?php
/* Copyright (C) 2006 fhcomplete.org
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
 * Authors: Stefan Puraner 	<puraner@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/fachbereich.class.php');
require_once('../../include/lvinfo.class.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/organisationsform.class.php');
require_once ('../../include/organisationseinheit.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Lehrveranstaltung Verwaltung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<!--	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../skin/jquery.css" type="text/css"/>-->
	<!--<script type="text/javascript" src="../../include/js/jquery.js"></script>-->
	<script src="../../include/js/jquery1.9.min.js" type="text/javascript"></script>
	<!--<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>-->
	<script>
		$(document).ready(function loadSemester()
		{
			var studiengang_kz = $("#stgDropdown").val();
			$.ajax(
			{
				dataType: "json",
				url: "../../soap/studienplan.json.php",
				data: {
					"method": "getSemesterFromStudiengang",
					"studiengang_kz": studiengang_kz
				}
			}).success(function(data)
			{
				var html = "";
				data.result.forEach(function(option)
				{
					html+="<option value='"+ option +"'>Semester "+ option +"</option>";
				});
				$("#semDropdown").html(html);
				loadLehrveranstaltungen();
			});
		});
		
		function loadSemester()
		{
			var studiengang_kz = $("#stgDropdown").val();
			$.ajax(
			{
				dataType: "json",
				url: "../../soap/studienplan.json.php",
				data: {
					"method": "getSemesterFromStudiengang",
					"studiengang_kz": studiengang_kz
				}
			}).success(function(data)
			{
				var html = "";
				data.result.forEach(function(option)
				{
					html+="<option value='"+ option +"'>Semester "+ option +"</option>";
				});
				$("#semDropdown").html(html);
				loadLehrveranstaltungen();
			});
		}
		
		function loadLehrveranstaltungen()
		{
			var studiengang_kz = $("#stgDropdown").val();
			var semester = $("#semDropdown").val();
			var oe_kurzbz = $("#oeDropdown").val();
//			console.log(oe_kurzbz);
			if(oe_kurzbz === "null")
			{
				$.ajax(
				{
					dataType: "json",
					url: "../../soap/fhcomplete.php",
					type: "POST",
					data: {
							"typ": "json",
							"class": "lehrveranstaltung",
							"method": "load_lva",
							"parameter_0": studiengang_kz,
							"parameter_1": semester,
							"parameter_2": "null",
							"parameter_3": "null",
							"parameter_4": "true"
						}
				}).success(function(data)
				{
					var html = "";
					data.result.forEach(function(option)
					{
						html+="<option value='"+ option.metadata.lehrveranstaltung_id +"'>"+ option.data +"</option>";
					});
					$("#lvDropdown").html(html);
				});
			}
			else
			{
				$.ajax(
				{
					dataType: "json",
					url: "../../soap/fhcomplete.php",
					type: "POST",
					data: {
							"typ": "json",
							"class": "lehrveranstaltung",
							"method": "load_lva_oe",
							"parameter_0": oe_kurzbz,
							"parameter_1": true,
							"parameter_2": "null",
							"parameter_3": "bezeichnung"
						}
				}).success(function(data)
				{
//					console.log(data);
					var html = "";
					data.result.forEach(function(option)
					{
						html+="<option value='"+ option.metadata.lehrveranstaltung_id +"'>"+ option.data +"</option>";
					});
					$("#lvDropdown").html(html);
				});
			}
			
		}
		
		function loadOrganisationseinheiten()
		{
			$.ajax(
			{
				dataType: "json",
				url: "../../soap/fhcomplete.php",
				type: "POST",
				data: {
						"typ": "json",
						"class": "lehrveranstaltung",
						"method": "load_lva",
						"parameter_0": studiengang_kz,
						"parameter_1": semester,
						"parameter_2": "null",
						"parameter_3": "null",
						"parameter_4": "true"
					}
			}).success(function(data)
			{
				var html = "";
				data.result.forEach(function(option)
				{
					html+="<option value='"+ option.metadata.lehrveranstaltung_id +"'>"+ option.data +"</option>";
				});
				$("#lvDropdown").html(html);
			});
		}
		
		function saveKompatibleLv(lehrveranstaltung_id)
		{
			$.ajax(
			{
				dataType: "json",
				url: "../../soap/lehrveranstaltung.json.php",
				type: "POST",
				data: {
						"typ": "json",
						"class": "lehrveranstaltung",
						"method": "saveKompatibleLehrveranstaltung",
						"lehrveranstaltung_id":lehrveranstaltung_id,
						"lehrveranstaltung_id_kompatibel":$("#lvDropdown").val()
					}
			}).success(function(data)
			{
				if(data.error === "true")
				{
					alert(data.errormsg);
				}
				loadLVKompatibilitaet(lehrveranstaltung_id);
				var iframe = parent.document.getElementById("lv_detail");
				iframe.src = "lehrveranstaltung_kompatibel.php?lehrveranstaltung_id="+lehrveranstaltung_id;
			}).error(function(data)
			{
				alert(data.responseText);
			});
		}
		
		function deleteKompatibleLv(lehrveranstaltung_id, lehrveranstaltung_id_kompatibel)
		{
			$.ajax(
			{
				dataType: "json",
				url: "../../soap/lehrveranstaltung.json.php",
				type: "POST",
				data: {
						"typ": "json",
						"class": "lehrveranstaltung",
						"method": "deleteKompatibleLehrveranstaltung",
						"lehrveranstaltung_id":lehrveranstaltung_id,
						"lehrveranstaltung_id_kompatibel":lehrveranstaltung_id_kompatibel
					}
			}).success(function(data)
			{
				if(data.error === "true")
				{
					alert(data.errormsg);
				}
				loadLVKompatibilitaet(lehrveranstaltung_id);
				var iframe = parent.document.getElementById("lv_detail");
				iframe.src = "lehrveranstaltung_kompatibel.php?lehrveranstaltung_id="+lehrveranstaltung_id;
			}).error(function(data)
			{
				alert(data.responseText);
			});
		}
	</script>
</head>
<body>
	
<?php
$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$lehrveranstaltung_id = $_GET["lehrveranstaltung_id"];
$type = isset($_GET["type"]) ? $_GET["type"] : "";
$lv = new lehrveranstaltung();

$kompatibleLvs = $lv->loadLVkompatibel($lehrveranstaltung_id);
//var_dump($kompatibleLvs);

if(count($kompatibleLvs)>0)
{
	echo '<h3>Kompatible LVs</h3><table style="width: auto;" class="tablesorter" id="t2">
	<thead>
		<tr>
			<th class="header">ID</th>
			<th class="header">Kurzbezeichnung</th>
			<th class="header">Bezeichnung</th>
			<th class="header">ECTS</th>
			<th class="header">Studiengang</th>';
			if($type == "edit")
				echo '<th class="header">Löschen</th>';
		echo '</tr>
	</thead>
	<tbody>';
	foreach($kompatibleLvs as $lvId)
	{
		$lv->load($lvId);
		$studiengang = new studiengang();
		$studiengang->load($lv->studiengang_kz);
		echo "<tr>
				<td>".$lv->lehrveranstaltung_id."</td>
				<td>".$lv->kurzbz."</td>
				<td>".$lv->bezeichnung."</td>
				<td>".$lv->ects."</td>
				<td>".$studiengang->bezeichnung."</td>";
				if($type == "edit")
				{
					echo "<td><a href='#' onclick='javascript:deleteKompatibleLv(\"".$lehrveranstaltung_id."\",\"".$lv->lehrveranstaltung_id."\")'><img height='20' src='../../skin/images/false.png'></a></td>";
				}
				else 
				{
					echo "<td>&nbsp;</td>";
				}
			echo "</tr>";
	}
	echo "</tbody>
		</table>";
}
 else 
{
	echo "Keine kompatiblen Lehrveranstaltungen vorhanden.</br>";
}

if($type == "edit")
{
	$studiengang = new studiengang();
	$studiengang->getAll("kurzbzlang");

	//Studiengang Dropdown
	echo "<div style='padding-top: 1em;'>";
	echo "<form action='javascript:saveKompatibleLv(\"".$lehrveranstaltung_id."\")' method='POST'>
		<b>Studiengang: </b><select id='stgDropdown' style='margin-right: 1em;' onload='javascript:loadSemester();' onchange='javascript:loadSemester();'>";
	foreach($studiengang->result as $stg)
	{
		echo "<option value=".$stg->studiengang_kz.">".$stg->kuerzel." - ".$stg->kurzbzlang."</option>";
	}
	echo "</select>";

	//OE-Dropdown
	$organisationseinheit = new organisationseinheit();
	$organisationseinheit->getAll(true, true);
	echo "<b>OE: </b><select id='oeDropdown' style='margin-right: 1em;' onload='javascript:loadSemester();' onchange='javascript:loadSemester();'><option value='null'>-- Keine --</option>";
	foreach($organisationseinheit->result as $oe)
	{
		echo "<option value=".$oe->oe_kurzbz.">".$oe->organisationseinheittyp_kurzbz." ".$oe->bezeichnung."</option>";
	}
	echo "</select>";

	//Semester Dropdown
	echo "<b>Semester: </b><select id='semDropdown' style='margin-right: 1em;' onchange='javascript:loadLehrveranstaltungen()'>";
	echo "</select>";

	//Lehrveranstaltung Dropdown
	echo "<b>Lehrveranstaltungen: </b><select id='lvDropdown' onchange=''>";
	echo "</select>";

	//Submit Button
	echo "<input type='submit' value='hinzufügen'>";
	echo "</form>";
	echo "</div>";
}
echo "</body>
	</html>";

?>