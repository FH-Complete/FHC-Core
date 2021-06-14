<?php
/* Copyright (C) 2009 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 */
/**
 * Seite zum Editieren von Testtool-Gebieten
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/gebiet.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/sprache.class.php');
require_once('../../../include/studienplan.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/organisationsform.class.php');
require_once('../../../include/ablauf.class.php');
require_once('../../../include/content.class.php');

if (!$user = get_uid())
	die('Sie sind nicht angemeldet. Es wurde keine Benutzer UID gefunden ! <a href="javascript:history.back()">Zur&uuml;ck</a>');

if (!$db = new basis_db())
{
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
}

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$sprache = new sprache();
$sprache->getAll(true, 'index');

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link href="../../../skin/tablesort.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../../../vendor/jquery/jqueryV1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
	<script type="text/javascript">
	$(document).ready(function()
	{
		$("#t1").tablesorter(
		{
			sortList: [[3,0],[1,0]],
			widgets: ["zebra"]
		});
	});
	</script>
</head>
<body>
<div id="data"></div>
';

if (isset($_GET['gebiet_id']))
	$gebiet_id = $_GET['gebiet_id'];
else
	$gebiet_id = '';

$stg_kz = (isset($_GET['stg_kz'])?$_GET['stg_kz']:'-1');

echo '<h1>Ablauf der Gebiete verwalten</h1>';

if (!$rechte->isBerechtigt('basis/testtool'))
	die($rechte->errormsg);

$studiengang = new studiengang();
$studiengang->getAll('typ, kurzbz', false);
$gebiet = new gebiet();
$gebiet->getAll();
$ablauf_vorgabe = new gebiet();
$ablauf_vorgabe->getAblaufVorgaben();

$errormsg = '';

echo '<a href="index.php?gebiet_id='.$gebiet_id.'&amp;stg_kz='.$stg_kz.'" class="Item">Zurück zur Admin Seite</a><br /><br />';
echo '<table><tr><td>';

//Studiengang Dropdown
echo '<form id="studiengang_form" action="'.$_SERVER['PHP_SELF'].'" method="GET">';
echo 'Studiengang: </td><td><SELECT name="stg_kz" onchange="document.getElementById(\'studiengang_form\').submit();"><OPTION value="-1">-- Keine Auswahl --</OPTION>';
$i = 0;
$selected = '';
$result_count = count($studiengang->result);
for ($i = 0; $i < $result_count; $i++)
{
	if ($stg_kz == $studiengang->result[$i]->studiengang_kz) $selected = 'selected';
	echo '<OPTION value="'.$studiengang->result[$i]->studiengang_kz.'" '.$selected.' >'.strtoupper($studiengang->result[$i]->typ.$studiengang->result[$i]->kurzbz).' ('.$studiengang->result[$i]->bezeichnung.')</OPTION>';
	$selected = '';
}
echo '</SELECT>';

// Studienplan Dropdown
echo "</tr><tr><td>Studienplan: </td><td>";
drawStudienplanDropdown($stg_kz, $db, "stp_id", 'studiengang_form');
echo "</form></td></tr></table>";
echo '<br /><br />';

// Gebiet speichern
if (isset($_GET['action']) && $_GET['action'] == 'save')
{
	if (isset($_POST['gebiet_id']) && $_POST['gebiet_id'] != '' &&
			isset($_POST['reihung']) && $_POST['reihung'] != '' &&
			isset($_POST['gewicht']) && $_POST['gewicht'] != '' &&
			isset($_POST['semester']) && $_POST['semester'] != '' &&
			isset($_POST['studienplan']))
	{
		// Ablauf-Vorgaben-Daten werden nur beim ersten Gebietseintrag gesendet.
		// In diesem Fall wird vorher ein neuer Ablauf-Vorgaben-Eintrag erstellt.
		$vorgaben_id = '';
		if (isset($_POST['sprache']) && $_POST['sprache'] != '')
		{
			// Prüfen, ob Content ID schon existiert
			foreach ($sprache->result as $row)
			{
				$content = new content();
				if ($content->getContent($_POST['content_id'], $row->sprache))
				{
					$content_id = $_POST['content_id'];
					break;
				}
				else
				{
					$content_id = '';
					$errormsg = '<span class="error">Content_ID '.$_POST['content_id'].' ist nicht vorhanden und wurde nicht gespeichert</span>';
				}
			}
			echo $errormsg;
			$ablauf_vorgaben = new ablauf();
			$ablauf_vorgaben->studiengang_kz = $stg_kz;
			$ablauf_vorgaben->sprache = $_POST['sprache'];
			$ablauf_vorgaben->sprachwahl = isset($_POST['sprachwahl'])?true:false;
			$ablauf_vorgaben->content_id = $content_id;
			$ablauf_vorgaben->insertamum = date('Y-m-d H:i:s');
			$ablauf_vorgaben->insertvon = $user;
			if ($ablauf_vorgaben->saveAblaufVorgabe(true))
			{
				$vorgaben_id = $ablauf_vorgaben->ablauf_vorgaben_id;
			}
			else
				echo $ablauf_vorgaben->errormsg;
		}
		elseif (isset($_POST['ablauf_vorgaben_id']) && $_POST['ablauf_vorgaben_id'] != '')
			$vorgaben_id = $_POST['ablauf_vorgaben_id'];

		$ablauf = new ablauf();
		$ablauf->studiengang_kz = $_POST['stg_kz'];
		$ablauf->gebiet_id = $_POST['gebiet_id'];
		$ablauf->reihung = $_POST['reihung'];
		$ablauf->gewicht = $_POST['gewicht'];
		$ablauf->semester = $_POST['semester'];
		$ablauf->insertvon = $user;
		$ablauf->insertamum = date('Y-m-d H:i:s');
		$ablauf->studienplan_id = $_POST['studienplan'];
		$ablauf->ablauf_vorgaben_id = $vorgaben_id;

		if (!$ablauf->save(true))
			echo $ablauf->errormsg;
	}
	else
	{
		echo '<span class="error">Bitte f&uuml;llen Sie alle Felder aus</span>';
	}
}
// Gebiet entfernen
if (isset($_GET['action']) && $_GET['action'] == 'delete')
{
	if (isset($_POST['ablauf_id']) && $_POST['ablauf_id'] != '')
	{
		$ablauf = new ablauf($_POST['ablauf_id']);
		if ($ablauf->delete($_POST['ablauf_id']))
			echo $ablauf->errormsg;

		// Wenn der Ablauf-Eintrag der letzte war, und die Ablauf-Vorgaben-ID nicht woanders verwendet wird, dann auch diesen löschen
		if (isset($_POST['ablauf_vorgaben_id']) && $_POST['ablauf_vorgaben_id'] != '')
		{
			$abl_vorgabe = new ablauf();
			$vorlage_count = $abl_vorgabe->countAblaufVorgabe($_POST['ablauf_vorgaben_id']);

			if ($vorlage_count == 0)
			{
				if (!$abl_vorgabe->deleteAblaufVorgabe($_POST['ablauf_vorgaben_id']))
					echo $abl_vorgabe->errormsg;
			}
		}
	}
}
// Gebiet bearbeiten
if (isset($_GET['action']) && $_GET['action'] == 'edit')
{
	if (isset($_POST['ablauf_id']) && $_POST['ablauf_id'] != '')
	{
		$ablauf = new ablauf($_POST['ablauf_id']);

		$gebiet = new gebiet($ablauf->result[0]->gebiet_id);
		$studiengang = new studiengang($stg_kz);

		echo '<table><form action="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&action=editsave" method="POST">
				<input type="hidden" name="ablauf_id" value="'.$_POST['ablauf_id'].'" />
				<tr><td>Studiengang_kz: </td><td><input type="text" name="stg_kz" value="'.strtoupper($studiengang->typ.$studiengang->kurzbz).' ('.$studiengang->bezeichnung.')'.'" style="width:98.5%" disabled /></td></tr>
				<tr><td>Gebiet: </td><td><input type="text" value="'.$gebiet->kurzbz.' ('.$gebiet->bezeichnung.')" style="width:98.5%" disabled /><input type="hidden" name="gebiet_id" value="'.$ablauf->result[0]->gebiet_id.'"/></td></tr>
				<tr><td>Reihung: </td><td><input type="text" name="reihung" value="'.$ablauf->result[0]->reihung.'" style="width:98.5%" /></td></tr>
				<tr><td>Gewichtung: </td><td><input type="text" name="gewicht" value="'.$ablauf->result[0]->gewicht.'" style="width:98.5%" /></td></tr>
				<tr><td>Semester: </td><td><input type="text" name="semester" value="'.$ablauf->result[0]->semester.'" style="width:98.5%" /></td></tr>
				<tr><td>Studienplan: </td><td>';
				drawStudienplanDropdown($stg_kz, $db, $name = 'studienplan_id', null, 'width:100%', $ablauf->result[0]->studienplan_id);
				echo '</td></tr>
				<tr><td>Sprache*: </td><td><select name="sprache">';
				foreach ($sprache->result as $row)
				{
					if ($ablauf->result[0]->sprache == $row->sprache)
						$selected = 'selected';
					else
						$selected = '';
					echo '<OPTION value="'.$row->sprache.'" '.$selected.'>'.$row->sprache.'</OPTION>';
				}
				echo '</SELECT></td></tr>
				<tr><td>Sprachwahl*: </td><td><input type="checkbox" name="sprachwahl" '.($ablauf->result[0]->sprachwahl?'checked':'').' /></td></tr>
				<tr><td>Content ID*: </td><td><input type="text" name="content_id" value="'.$ablauf->result[0]->content_id.'"/></td></tr>
				<tr><td colspan="2">* Wirken sich auf alle Gebiete gleich aus</td></tr>
				<tr><td></td><td><input type="submit" value="Speichern" style="width:50%"/><a href="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'"><input type="button" value="Abbrechen" style="width:50%"></a></td></tr>
			  </form></table>';
	}
	else
	{
		//echo '<span class="error">Bitte f&uuml;llen Sie alle Felder aus</span>';
	}
}
// Bearbeitetes Gebiet speichern
if (isset($_GET['action']) && $_GET['action'] == 'editsave')
{
	if (isset($_POST['reihung']) && $_POST['reihung'] != '' && isset($_POST['gewicht']) && $_POST['gewicht'] != '' && isset($_POST['semester']) && $_POST['semester'] != '')
	{
		$ablauf = new ablauf($_POST['ablauf_id']);
		$ablauf = $ablauf->result[0];
		$ablauf->reihung = $_POST['reihung'];
		$ablauf->gewicht = $_POST['gewicht'];
		$ablauf->semester = $_POST['semester'];

		if (isset($_POST['studienplan_id'])) // && $_POST['studienplan_id'] != ''
			$ablauf->studienplan_id = $_POST['studienplan_id'];

		if ($ablauf->save(false))
		{
			// Prüfen, ob Content ID schon existiert
			foreach ($sprache->result as $row)
			{
				$content = new content();
				if ($content->getContent($_POST['content_id'], $row->sprache))
				{
					$content_id = $_POST['content_id'];
					break;
				}
				else
				{
					$content_id = '';
					$errormsg = '<span class="error">Content_ID '.$_POST['content_id'].' ist nicht vorhanden und wurde nicht gespeichert</span>';
				}
			}
			echo $errormsg;
			$ablauf_vorgaben = new ablauf();
			$ablauf_vorgaben->ablauf_vorgaben_id = $ablauf->ablauf_vorgaben_id;
			$ablauf_vorgaben->studiengang_kz = $stg_kz;
			$ablauf_vorgaben->sprache = $_POST['sprache'];
			$ablauf_vorgaben->sprachwahl = isset($_POST['sprachwahl'])?true:false;
			$ablauf_vorgaben->content_id = $content_id;
			$ablauf_vorgaben->updateamum = date('Y-m-d H:i:s');
			$ablauf_vorgaben->updatevon = $user;

			if (!$ablauf_vorgaben->saveAblaufVorgabe(false))
			{
				echo $ablauf_vorgaben->errormsg;
			}

		}
		else
			echo $ablauf->errormsg;
	}
	else
	{
		echo '<span class="error">Bitte f&uuml;llen Sie alle Felder aus</span>';
	}
}

// Liste aller zugehoerigen Gebiete anzeigen
$ablauf = new ablauf();
if (isset($_GET['stp_id']) && $_GET['stp_id'] != '')
{
	$ablauf->getAblaufGebiete($stg_kz, $_GET['stp_id']);
}
else
{
	$ablauf->getAblaufGebiete($stg_kz);
}
$gebieteangehaengt = array();
$studienplan = new studienplan();
$ablauf_vorgaben_id = '';

if ($stg_kz != -1)
{
	echo '
			<table id="t1" class="tablesorter">
				<thead><tr>
					<th>Gebiet</th>
					<th>Reihung</th>
					<th>Gewichtung</th>
					<th>Semester</th>
					<th>Studienplan</th>
					<th>Sprache</th>
					<th>Sprachwahl</th>
					<th>Content ID</th>
					<th></th>
			</tr></thead><tbody>';
	foreach ($ablauf->result as $row)
	{
		$studienplan->loadStudienplan($row->studienplan_id);
		$gebiet = new gebiet($row->gebiet_id);
		array_push($gebieteangehaengt, $gebiet->gebiet_id);

		if ($ablauf_vorgaben_id == '' && $row->ablauf_vorgaben_id != '')
			$ablauf_vorgaben_id = $row->ablauf_vorgaben_id;
		echo '<tr>
				<td>'.$gebiet->bezeichnung.' ('.$gebiet->kurzbz.')</td>
				<td>'.$row->reihung.'</td>
				<td>'.$row->gewicht.'</td>
				<td>'.$row->semester.'</td>
				<td>'.$studienplan->bezeichnung.'</td>
				<td>'.$row->sprache.'</td>
				<td>'.($row->sprachwahl != ''?($row->sprachwahl?'Ja':'Nein'):'-').'</td>
				<td><a href="'.APP_ROOT.'cms/admin.php?content_id='.$row->content_id.'&action=content&filter='.$row->content_id.'" target="_blank">'.$row->content_id.'</a></td>
				<td>
					<form action="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&action=edit"   method="POST" style="float:left" id="fe'.$row->ablauf_id.'"><a onclick="document.getElementById(\'fe'.$row->ablauf_id.'\').submit();">edit</a>
						<input type="hidden" name="ablauf_id" value="'.$row->ablauf_id.'" />
					</form>
					<form action="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&action=delete" method="POST" style="float:left; margin-left:5px;" id="fd'.$row->ablauf_id.'"><a onclick="if (confirm(\'Gebietszuteilung entfernen?\')) document.getElementById(\'fd'.$row->ablauf_id.'\').submit();">delete</a>
						<input type="hidden" name="ablauf_id" value="'.$row->ablauf_id.'" />
						<input type="hidden" name="ablauf_vorgaben_id" value="'.$ablauf_vorgaben_id.'" />
					</form>
				</td>
			</tr>';
	}

	$gebiet_dropdown = new gebiet();
	$gebiet_dropdown->getAll();
	echo '</tbody><tfoot><tr>
			<form action="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&action=save" method="POST">
			<input type="hidden" name="stg_kz" value="'.$stg_kz.'" />
			<td>
				<SELECT name="gebiet_id">';
	foreach ($gebiet_dropdown->result as $row)
	{
		if ($gebiet_id == '')
			$gebiet_id = $row->gebiet_id;
		if ($gebiet_id == $row->gebiet_id)
			$selected = 'selected';
		else
			$selected = '';
		echo '<OPTION value="'.$row->gebiet_id.'" '.$selected.'>'.$row->bezeichnung.' ('.$row->kurzbz.')</OPTION>';
	}
	echo '</SELECT></td>';
	echo '<td><input type="text" name="reihung" /></td>';
	echo '<td><input type="text" name="gewicht" /></td>';
	echo '<td><input type="text" name="semester" /></td>';
	echo '<td>';
	drawStudienplanDropdown($stg_kz, $db, "studienplan");
	echo '</td>';
	// Ablauf-Vorgaben können nur beim ersten Eintrag gespeichert werden. Ansonsten werden sie über EDIT geändert.
	if (count($gebieteangehaengt) == 0)
	{
		echo '<td><select name="sprache">';
		foreach ($sprache->result as $row)
		{
			echo '<OPTION value="'.$row->sprache.'" '.$selected.'>'.$row->sprache.'</OPTION>';
		}
		echo '</SELECT></td>';
		echo '<td><input type="checkbox" name="sprachwahl" /></td>';
		echo '<td><input type="text" name="content_id" /></td>';
	}
	else
		echo '<td></td><td></td><td></td>';
	echo '<input type="hidden" name="ablauf_vorgaben_id" value="'.$ablauf_vorgaben_id.'" />';
	echo '<td><input type="submit" value="Speichern"/></td></form></tr></tfoot></table>';
}

// Ablaufzuordnung hinzufügen
if (isset($_POST['action']) && $_POST['action'] == 'saveAblauf')
{
	$ablauf_vorgaben_id = $_POST['ablauf_vorgaben_id'];
	$studiengang_kz = $_POST['studiengang_kz'];
	$reihung = $_POST['reihung'];
	$gewicht = $_POST['gewicht'];
	$semester = $_POST['semester'];

	$ablauf = new gebiet();

	$ablauf->ablauf_vorgaben_id = $ablauf_vorgaben_id;
	$ablauf->studiengang_kz = $studiengang_kz;
	$ablauf->reihung = $reihung;
	$ablauf->gewicht = $gewicht;
	$ablauf->semester = $semester;
	$ablauf->new = true;
	$ablauf->gebiet_id = $gebiet_id;

	if ($ablauf->saveAblauf())
		echo '<span class="ok">Ablauf gespeichert</span>';
	else
		echo '<span class="error">Fehler beim Speichern:'.$ablauf->errormsg.'</span>';
}

//Speichern eines neuen Eintrags
if (isset($_POST['speichern']))
{
	if (!$rechte->isBerechtigt('basis/testtool', null, 'suid'))
		die($rechte->errormsg);

	$gebiet = new gebiet();
	if ($gebiet->load($gebiet_id))
	{
		$bezeichnung_mehrsprachig = array();
		foreach ($sprache->result as $row_sprache)
		{
			if (isset($_POST['bezeichnung_mehrsprachig_'.$row_sprache->sprache]) && $_POST['bezeichnung_mehrsprachig_'.$row_sprache->sprache] != '')
				$bezeichnung_mehrsprachig[$row_sprache->sprache] = $_POST['bezeichnung_mehrsprachig_'.$row_sprache->sprache];
		}
		$gebiet->bezeichnung_mehrsprachig = $bezeichnung_mehrsprachig;

		$gebiet->kurzbz = $_POST['kurzbz'];
		$gebiet->bezeichnung = $_POST['bezeichnung_mehrsprachig_German'];
		$gebiet->beschreibung = $_POST['beschreibung'];
		$gebiet->zeit = $_POST['zeit'];
		$gebiet->multipleresponse = isset($_POST['multipleresponse']);
		$gebiet->kategorien = isset($_POST['kategorien']);
		$gebiet->zufallfrage = isset($_POST['zufallfrage']);
		$gebiet->zufallvorschlag = isset($_POST['zufallvorschlag']);
		$gebiet->levelgleichverteilung = isset($_POST['levelgleichverteilung']);
		$gebiet->maxpunkte = $_POST['maxpunkte'];
		$gebiet->maxfragen = $_POST['maxfragen'];
		$gebiet->level_start = $_POST['level_start'];
		$gebiet->level_sprung_auf = $_POST['level_sprung_auf'];
		$gebiet->level_sprung_ab = $_POST['level_sprung_ab'];
		$gebiet->updateamum = date('Y-m-d H:i:s');
		$gebiet->updatevon = $user;
		$gebiet->antwortenprozeile = $_POST['antwortenprozeile'];

		if ($gebiet->save(false))
		{
			echo 'Daten erfolgreich gespeichert';
		}
		else
		{
			echo '<span class="error">Fehler beim Speichern: '.$gebiet->errormsg.'</span>';
		}
	}
	else
	{
		echo '<span class="error">Fehler beim Laden des Gebiets</span>';
	}
}

echo '</body></html>';


/**
 * Zeichnet das Dropdown zur Auswahl des Studienplans
 * @param integer $stg_kz Studiengang.
 * @param string $db Datenbankverbindung.
 * @param string $name Name des <select name="???">.
 * @param string $autosubmitform Name der uebergeordneten Form, um bei einer Auswahl submit().
 * @param string $style Styleanweisung.
 * @param integer $studienplan Studienplan_id.
 * @return ?
 */
function drawStudienplanDropdown($stg_kz, $db, $name = '', $autosubmitform = null, $style = 'width:100%', $studienplan = null)
{
	$sprachen_obj = new sprache();
	$sprachen_obj->getAll();
	$sprachen_arr = array();
	$sprache1 = 'German';
	foreach ($sprachen_obj->result as $row)
	{
		if (isset($row->bezeichnung_arr[$sprache1]))
			$sprachen_arr[$row->sprache] = $row->bezeichnung_arr[$sprache1];
		else
			$sprachen_arr[$row->sprache] = $row->sprache;
	}
	$stsem_akt = new studiensemester();
	$stsem_akt = $stsem_akt->getaktorNext();
	$studiensemester_kurzbz = (isset($_GET['studiensemester_kurzbz']) ? $_GET['studiensemester_kurzbz'] : $stsem_akt);
	$studienplan_obj = new studienplan();
	$studienplan_obj->getStudienplaeneFromSem($stg_kz, $studiensemester_kurzbz);
	$studienordnung_arr = array();
	$studienplan_arr = array();
	$studienplaene_verwendet = array();
	$studienplan_id = '';
	$orgform_obj = new organisationsform();
	$orgform_obj->getAll();
	$orgform_arr = array();
	foreach ($orgform_obj->result as $row)
		$orgform_arr[$row->orgform_kurzbz] = $row->bezeichnung;

	foreach ($studienplan_obj->result as $row_sto)
	{
		$studienordnung_arr[$row_sto->studienordnung_id]['bezeichnung'] = $row_sto->bezeichnung_studienordnung;
		$studienplan_arr[$row_sto->studienordnung_id][$row_sto->studienplan_id]['bezeichnung'] = $row_sto->bezeichnung_studienplan;

		$studienplan_arr[$row_sto->studienordnung_id][$row_sto->studienplan_id]['orgform_kurzbz'] = $row_sto->orgform_kurzbz;
		$studienplan_arr[$row_sto->studienordnung_id][$row_sto->studienplan_id]['sprache'] = $sprachen_arr[$row_sto->sprache];
		$studienplaene_verwendet[$row_sto->studienplan_id] = $row_sto->bezeichnung_studienplan;
	}

	$selected = isset($_GET['stp_id'])?'':'selected';
	echo "<SELECT id='studienplan_dropdown' name='".$name."' ";
	if (isset($autosubmitform) && $autosubmitform != '')
		echo 'onchange="document.getElementById(\''.$autosubmitform.'\').submit();"';

	echo " style='".$style."'>";
	echo "<OPTION value='' ".$selected.">Studienplan auswaehlen</OPTION>";
	// Pruefen ob uebergebene StudienplanID in Auswahl enthalten
	// ist und ggf auf leer setzen
	if ($studienplan_id != '')
	{
		$studienplan_found = false;
		foreach ($studienplan_arr as $stoid => $row_sto)
		{
			if (array_key_exists($studienplan_id, $studienplan_arr[$stoid]))
			{
				$studienplan_found = true;
				break;
			}
		}
		if (!$studienplan_found)
		{
			$studienplan_id = '';
		}
	}
	foreach ($studienordnung_arr as $stoid => $row_sto)
	{
		$selected = '';

		echo '<option value="" disabled>Studienordnung: '.$db->convert_html_chars($row_sto['bezeichnung']).'</option>';

		foreach ($studienplan_arr[$stoid] as $stpid => $row_stp)
		{
			if (isset($_GET['stp_id']) && $_GET['stp_id'] == $stpid)
				$selected = 'selected';
			if (isset($studienplan) && $studienplan == $stpid)
				$selected = 'selected';
			echo '<option value="'.$stpid.'" '.$selected.'>'.$db->convert_html_chars($row_stp['bezeichnung']).' ('.$orgform_arr[$row_stp['orgform_kurzbz']].', '.$row_stp['sprache'].')</option>';
			$selected = '';
		}
	}
	echo '</SELECT>';
}
?>
