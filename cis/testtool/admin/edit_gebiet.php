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

if (!$user=get_uid())
	die('Sie sind nicht angemeldet. Es wurde keine Benutzer UID gefunden ! <a href="javascript:history.back()">Zur&uuml;ck</a>');

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link href="../../../skin/tablesort.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../../../include/js/jquery1.9.min.js" ></script>
	<script type="text/javascript">
    $(document).ready(function()
    {
        $("#t1").tablesorter(
        {
            sortList: [[0,0]],
            widgets: ["zebra"]
        });
    });

	function deleteZuordnung(ablauf_id)
	{
		if(confirm("Wollen Sie dieses Zuordnung wirklich entfernen?"))
        {
            $("#data").html(\'<form action="edit_gebiet.php" name="sendform" id="sendform" method="POST"><input type="hidden" name="action" value="deleteZuordnung" /><input type="hidden" name="ablauf_id" value="\'+ablauf_id+\'" /></form>\');
			document.sendform.submit();
        }
        return false;
	}

    </script>
</head>
<body>
<div id="data"></div>
';

if(isset($_GET['gebiet_id']))
	$gebiet_id=$_GET['gebiet_id'];
else
	$gebiet_id='';

$stg_kz = (isset($_GET['stg_kz'])?$_GET['stg_kz']:'-1');

echo '<h1>&nbsp;Gebiet bearbeiten</h1>';

if(!$rechte->isBerechtigt('basis/testtool'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$gebiet = new gebiet();
$gebiet->getAll();

echo '<a href="index.php?gebiet_id='.$gebiet_id.'&amp;stg_kz='.$stg_kz.'" class="Item">Zurück zur Admin Seite</a><br /><br />';

//Liste der Gebiete anzeigen
echo '<form id="gebiet_form" action="'.$_SERVER['PHP_SELF'].'" method="GET">';
echo 'Gebiet: <SELECT name="gebiet_id" onchange="document.getElementById(\'gebiet_form\').submit();">';

foreach ($gebiet->result as $row)
{
	if($gebiet_id=='')
		$gebiet_id=$row->gebiet_id;

	if($gebiet_id==$row->gebiet_id)
		$selected='selected';
	else
		$selected='';

	echo '<OPTION value="'.$row->gebiet_id.'" '.$selected.'>'.$row->bezeichnung.' - '.$row->kurzbz.' - '.$row->zeit.'</OPTION>';
}
echo '</SELECT>
	<!--<input type="submit" value="Bearbeiten">-->
	</form>';

echo '<br /><br />';

// Ablaufzuordnung entfernen
if(isset($_POST['action']) && $_POST['action']=='deleteZuordnung')
{
	if(!isset($_POST['ablauf_id']) || !is_numeric($_POST['ablauf_id']))
		die('ungueltige Parameteruebergabe');

	$ablauf_id = $_POST['ablauf_id'];

	$ablauf = new gebiet();
	if($ablauf->deleteAblaufZuordnung($ablauf_id))
		echo '<span class="ok">Ablauf wurde entfernt</span>';
	else
		echo '<span class="error">Fehler beim Entfernen:'.$ablauf->errormsg.'</span>';

}
// Ablaufzuordnung hinzufügen
if(isset($_POST['action']) && $_POST['action']=='saveAblauf')
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

	if($ablauf->saveAblauf())
		echo '<span class="ok">Ablauf gespeichert</span>';
	else
		echo '<span class="error">Fehler beim Speichern:'.$ablauf->errormsg.'</span>';

}

//Speichern der Daten
if(isset($_POST['speichern']))
{
	if(!$rechte->isBerechtigt('basis/testtool', null, 'suid'))
		die('Sie haben keine Berechtigung fuer diese Aktion');

	$gebiet = new gebiet();
	if($gebiet->load($gebiet_id))
	{
		$gebiet->kurzbz = $_POST['kurzbz'];
		$gebiet->bezeichnung = $_POST['bezeichnung'];
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

		if($gebiet->save(false))
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

if($gebiet_id!='')
{
	$gebiet = new gebiet($gebiet_id);

	echo "<hr />";
	echo '<form accept-charset="UTF-8" action="'.$_SERVER['PHP_SELF'].'?gebiet_id='.$gebiet_id.'&amp;stg_kz='.$stg_kz.'" method="POST">';
	echo '<table>';

	echo '<tr>';
	//ID
	echo '<td>ID</td><td>'.$gebiet_id.'</td>';
	echo '</tr><tr>';
	//Kurzbz
	echo '<td>Kurzbz</td><td><input type="text" maxlength="10" size="10" name="kurzbz" value="'.$gebiet->kurzbz.'"></td>';
	echo '</tr><tr>';
	//Bezeichnung
	echo '<td>Bezeichnung</td><td><input type="text" maxlength="50" name="bezeichnung" value="'.$gebiet->bezeichnung.'"></td>';
	echo '</tr><tr>';
	//Beschreibung
	echo '<td>Beschreibung</td><td><textarea name="beschreibung">'.$gebiet->beschreibung.'</textarea></td>';
	echo '</tr><tr>';
	//Zeit
	echo '<td>Zeit</td><td><input type="text" name="zeit" size="8" maxlength="8" value="'.$gebiet->zeit.'"> hh:mm:ss</td>';
	echo '</tr><tr>';
	echo '<td>Multiple Response</td><td><input type="checkbox" name="multipleresponse" '.($gebiet->multipleresponse?'checked="checked"':'').'></td>';
	echo '</tr><tr>';
	echo '<td>Kategorien</td><td><input type="checkbox" name="kategorien" '.($gebiet->kategorien?'checked="checked"':'').'></td>';
	echo '</tr><tr>';
	echo '<td>Zufällige Fragereihenfolge</td><td><input type="checkbox" name="zufallfrage" '.($gebiet->zufallfrage?'checked="checked"':'').'></td>';
	echo '</tr><tr>';
	echo '<td>Zufällige Vorschlagreihenfolge</td><td><input type="checkbox" name="zufallvorschlag" '.($gebiet->zufallvorschlag?'checked="checked"':'').'></td>';
	echo '</tr><tr>';
	echo '<td>Levelgleichverteilung</td><td><input type="checkbox" name="levelgleichverteilung" '.($gebiet->levelgleichverteilung?'checked="checked"':'').'></td>';
	echo '</tr><tr>';
	// empfohlene maximalpunkte berechnen und anzeigen
	$maximalpunkte = $gebiet->berechneMaximalpunkte($gebiet_id);
	if($gebiet->maxpunkte!=$maximalpunkte)
		$hinweis = '<span class="error">empfohlene Maximalpunkteanzahl: '.$maximalpunkte.'</span>';
	else
		$hinweis ='';
	echo '<td>Maximale Punkteanzahl</td><td><input type="text" size="5" maxlength="5" name="maxpunkte" value="'.$gebiet->maxpunkte.'">'.$hinweis.'</td>';
	echo '</tr><tr>';
	echo '<td>Maximale Fragenanzahl</td><td><input type="text" size="5" maxlength="5" name="maxfragen" value="'.$gebiet->maxfragen.'"></td>';
	echo '</tr><tr>';
	echo '<td>Antworten pro Zeile</td><td><input type="text" size="5" maxlength="5" name="antwortenprozeile" value="'.$gebiet->antwortenprozeile.'"></td>';
	echo '</tr><tr>';
	echo '<td>Start Level</td><td><input type="text" size="5" maxlength="5" name="level_start" value="'.$gebiet->level_start.'"></td>';
	echo '</tr><tr>';
	echo '<td>Richtige Fragen bis Levelaufstieg</td><td><input type="text" size="5" maxlength="5" name="level_sprung_auf" value="'.$gebiet->level_sprung_auf.'"></td>';
	echo '</tr><tr>';
	echo '<td>Falsche Fragen bis Levelabstieg</td><td><input type="text" size="5" maxlength="5" name="level_sprung_ab" value="'.$gebiet->level_sprung_ab.'"></td>';
	echo '</tr><tr>';
	echo '<td></td><td><input type="submit" name="speichern" value="Speichern"></td>';
	echo '</tr></table>';

	echo '</form>';

	echo '<hr />
	<h2>Zuordnung</h2>';

	$gebiet = new gebiet();
	$gebiet->loadAblaufGebiet($gebiet_id);

	$studiengang = new studiengang();
	$studiengang->getAll('typ, kurzbz',false);

	echo '<form action="edit_gebiet.php" method="POST">';
	echo '<table id="t1" class="tablesorter">
	<thead>
	<tr>
		<th>Studiengang</th>
		<th>Reihung</th>
		<th>Gewicht</th>
		<th>Semester</th>
		<th>Vorgaben</th>
		<th>Aktion</th>
	</tr>
	</thead>
	<tbody>';
	foreach($gebiet->result as $row)
	{
		echo '<tr>
		<td>'.$studiengang->kuerzel_arr[$row->studiengang_kz].'</td>
		<td>'.$row->reihung.'</td>
		<td>'.$row->gewicht.'</td>
		<td>'.$row->semester.'</td>
		<td>'.$row->ablauf_vorgaben_id.'</td>
		<td><a href="#loeschen" onclick="return deleteZuordnung(\''.$row->ablauf_id.'\');" ><img src="../../../skin/images/delete.png" height="15px" /></a></td>
		</tr>';
	}
	echo '</tbody>';
	echo '<tfoot>
	<tr>
	<td><select name="studiengang_kz">';
	foreach($studiengang->kuerzel_arr as $stg_kz=>$row_stg)
	{
		echo '<option value="'.$stg_kz.'">'.$row_stg.'</option>';
	}
	echo '</select>
	</td>
	<td><input type="text" name="reihung" value="1" size="2" /></td>
	<td><input type="text" name="gewicht" value="1" size="2"/></td>
	<td><input type="text" name="semester" value="1" size="2"/></td>
	<td>
	<select name="ablauf_vorgaben_id">';

	$ablauf_vorgabe = new gebiet();
	$ablauf_vorgabe->getAblaufVorgaben();

	foreach($ablauf_vorgabe->result as $vorgabe)
	{
		echo '<option value="'.$vorgabe->ablauf_vorgaben_id.'">'.$studiengang->kuerzel_arr[$vorgabe->studiengang_kz].' - Sprache: '.$vorgabe->sprache.' Sprachwahl: '.($vorgabe->sprachwahl?'Ja':'Nein').' Content:'.$vorgabe->content_id.'</option>';
	}

	echo '</select></td>
	<td>
		<input type="hidden" name="action" value="saveAblauf" />
		<input type="submit" value="speichern" /></td>
	</tr>';
	echo '</tfoot></table>';
	echo '</form>';
}

echo '</body></html>';
?>
