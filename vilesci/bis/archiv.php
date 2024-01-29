<?php
/* Copyright (C) 2015 Technikum-Wien
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
 * Authors: Nikolaus Krondraf <nikolaus.krondraf@technikum-wien.at>
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/bisarchiv.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/datum.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('student/stammdaten',null,'suid') && !$rechte->isBerechtigt('assistenz',null,'suid') &&
   !$rechte->isBerechtigt('admin',null,'suid') && !$rechte->isBerechtigt('mitarbeiter/stammdaten',null,'suid'))
{
	die('Sie haben keine Berechtigung für diese Seite');
}

// XML-Datei oder HTML-Übersicht ausgeben
isset($_GET['action']) ? $action = $_GET['action'] : $action = null;
isset($_GET['id']) ? $id = $_GET['id'] : $id = null;
if($action == 'xml' || $action == 'html')
{
	$bisarchiv = new bisarchiv();
	if(!$bisarchiv->load($id))
	{
		echo $bisarchiv->errormsg;
		exit;
	}
	
	if($action == 'xml')
	{
		header("Content-type: text/xml");
		echo $bisarchiv->meldung;
		exit;
	}
	else if($action == 'html')
	{
		echo $bisarchiv->html;
		exit;
	}
}

$datum = new datum();
$bisarchiv = new bisarchiv();
$studiengang = new studiengang();
$studiengang->getAll('bezeichnung');
$studiensemester = new studiensemester();
$studiensemester->getAll();
$aktsem = $studiensemester->getakt();
isset($_GET['anzeige']) ? $anzeige = $_GET['anzeige'] : $anzeige = $aktsem;
$prevsem = $studiensemester->getPreviousFrom($anzeige);
$nextsem = $studiensemester->getNextFrom($anzeige);

// Archiv-Navigation erstellen
$prevsem != null ? $prevnav = '<a href="archiv.php?anzeige=' . $prevsem . '">&Lt;</a>' : $prevnav = null;
$nextsem != null ? $nextnav = '<a href="archiv.php?anzeige=' . $nextsem . '">&Gt;</a>' : $nextnav = null;

// Daten archivieren
if($action == null && isset($_POST['action']))
	$action = $_POST['action'];

if($action != null)
{
	if($action == "archivieren")
	{
		isset($_GET['meldung']) ? $meldung = $_GET['meldung'] : $meldung = null;
		isset($_GET['html']) ? $html = $_GET['html'] : $html = null;
		isset($_GET['stg']) ? $stg = $_GET['stg'] : $stg = null;
		isset($_GET['sem']) ? $sem = $_GET['sem'] : $sem = null;
		isset($_GET['typ']) ? $typ = $_GET['typ'] : $typ = null;
	}
	if($action == "upload")
	{
		$html = null;
		$meldung = null;
		$stg = null;
		isset($_POST['typ']) ? $typ = $_POST['typ'] : $typ = null;
		isset($_POST['sem']) ? $sem = $_POST['sem'] : $sem = null;
		if($typ != "mitarbeiter")
			isset($_POST['stg']) ? $stg = $_POST['stg'] : $stg = null;
				
		if(!empty($_FILES))
		{
			if($_FILES['meldung']['type'] == 'text/xml')
			{
				move_uploaded_file($_FILES['meldung']['tmp_name'], "bisdaten/" . basename($_FILES['meldung']['name']));
				$meldung = "bisdaten/" . basename($_FILES['meldung']['name']);
			}
		}
	}
	
	if($html != null)
	{
		$bisarchiv->readFile($html, 'html');
	}
	$bisarchiv->readFile($meldung, 'xml');
	$bisarchiv->studiengang_kz = $stg;
	$bisarchiv->studiensemster_kurzbz = $sem;
	$bisarchiv->insertvon = $uid;
	$bisarchiv->typ = $typ;
	$result = $bisarchiv->save();
}

// Daten des aktuellen Semesters ermitteln
$bisarchiv->getBisData($anzeige);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>BIS - Archiv</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
		<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
		<style type="text/css">
			form { width: 700px; }
			label { float: left; width: 130px; }
			.msg { font-weight: bold; }
			.error { color: red; }
			#t1 { width: 75%; }
		</style>
		<script language="JavaScript" type="text/javascript">
		$(document).ready(function() 
		{ 
			$("#t1").tablesorter(
			{
				widgets: ["zebra"],
				headers: {
					4: {
						sorter: false
					},
					5: {
						sorter: false
					}
				},
				sortList: [[3,1]]
			});
			
			$("#typ").change(function(){
				if($("#typ").val() == 'studenten')
					$("#select-stg").show();
				else
					$("#select-stg").hide();
			})
		});
		</script>
	</head>
	<body>
		<h1>BIS - Meldung archivieren</h1>
		<?php 
		if($action != null)
		{
			if($result)
				echo '<p class="msg">Die Meldung wurde erfolgreich archiviert.</p>';
			else
				echo '<p class="msg error">Fehler: ' . $bisarchiv->errormsg . '</p>';
		}
		?>
		
		<form enctype="multipart/form-data" action="archiv.php" method="post">
			<fieldset>
				<legend>BIS-Meldung manuell archivieren</legend>
				<p>
					<label for="sem">Studiensemester</label>
					<select id="sem" name="sem">
						<?php
						foreach($studiensemester->studiensemester as $obj) 
						{
							if($obj->studiensemester_kurzbz == $aktsem)
								$property = ' selected';
							else
								$property = null;
							
							echo '<option value="' . $obj->studiensemester_kurzbz . '"' . $property . '>' . $obj->studiensemester_kurzbz . '</option>';
						}
						?>
					</select>
				</p>
				<p>
					<label for="typ">Typ</label>
					<select id="typ" name="typ">
						<option value="studenten">Studenten</option>
						<option value="mitarbeiter">Mitarbeiter</option>
					</select>
				</p>
				<p id="select-stg">
					<label for="stg">Studiengang</label>
					<select id="stg" name="stg">
						<?php
						foreach($studiengang->result as $obj) 
						{
							echo '<option value="' . $obj->studiengang_kz . '">' . $obj->bezeichnung . ' - ' . $obj->kurzbzlang . ' (' . $obj->kuerzel . ')</option>';
						}
						?>
					</select>
				</p>
				<p>
					<label for="meldung">XML-Datei</label>
					<input type="file" id="meldung" name="meldung" value="" />
				</p>
				
				<input type="submit" value="Datei archivieren" /> <input type="reset" value="zurücksetzen" />
				<input type="hidden" name="action" value="upload" />
			</fieldset>
		</form>
		
		<h2><?php echo $prevnav; ?> BIS-Archiv für <?php echo $anzeige . " "  . $nextnav; ?></h2>
		<?php 
		if($bisarchiv->result == null)
		{
			echo '<p>Für dieses Semester sind keine archivierten Daten vorhanden.</p>';
		}
		else
		{
			echo '<table class="tablesorter" id="t1">
				 <thead>
					<tr>
					   <th>Typ</th>
					   <th>Studiengang</th>
					   <th>Stg. Kennzahl</th>
					   <th>Datum</th>
					   <th>Meldung</th>
					   <th>Meldungsübersicht</th>
					</tr>
				 </thead>
				 <tbody>';

			foreach($bisarchiv->result as $data)
			{
				$stgbez = null;
				if($data->studiengang_kz != '')
				{
					$studiengang->load($data->studiengang_kz);
					$stgbez = $studiengang->bezeichnung . ' - ' . $studiengang->kurzbzlang . ' (' . $studiengang->kuerzel . ')';
				}
				
				echo '<tr>';
				echo '<td>' . ucfirst($data->typ) . '</td>';
				echo '<td>' . $stgbez . '</td>';
				echo '<td>' . $data->studiengang_kz . '</td>';
				echo '<td>' . $datum->convertISODate($data->insertamum) . '</td>';
				echo '<td><a href="archiv.php?id=' . $data->archiv_id . '&action=xml">XML-Datei downloaden</a></td>';
				if($data->html != '')
					echo '<td><a href="archiv.php?id=' . $data->archiv_id . '&action=html" target="_blank">ansehen</a></td>';
				else
					echo '<td></td>';
				echo '</tr>';
			}
			
			echo '</tbody></table>';
		}
		?>
	</body>
</html>