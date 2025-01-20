<?php
/* Copyright (C) 2013 fhcomplete.org
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
 * Authors: Martin Tatzber < tatzberm@technikum-wien.at >,
 * 			Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/lvangebot.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/gruppe.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();
$reloadstr = '';  // neuladen der liste im oberen frame
$errorstr='';
$htmlstr='';
$datum_obj = new datum();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
$write_admin=false;

$lvangebot_id = (isset($_REQUEST['lvangebot_id'])?$_REQUEST['lvangebot_id']:'');
$lv_id = (isset($_REQUEST['lehrveranstaltung_id'])?$_REQUEST['lehrveranstaltung_id']:'');

if (isset($_GET['action']))
	$action=$_GET['action'];
else if(isset($_POST['neu']))
	$action='neu';
else
	$action='';

//wenn eine lvangebot_id mitgegeben wurde, wird der entsprechende Eintrag geladen
$lvangebot = new lvangebot();
if($lvangebot_id!='')
{
	if (!$lvangebot->load($lvangebot_id))
		die('LV-Angebot konnte nicht geladen werden!');
	else
	{
		$new=false;
		$lv_id=$lvangebot->lehrveranstaltung_id;
	}
}
else
	$new=true;

$lv_obj = new lehrveranstaltung();
$lv_obj->load($lv_id);
$stg_obj = new studiengang();
$stg_obj->load($lv_obj->studiengang_kz);
$oe_studiengang = $stg_obj->oe_kurzbz;

if(!$rechte->isBerechtigt('lehre/lehrveranstaltung', $oe_studiengang, 's'))
	die('Sie haben keine Berechtigung fuer diese Seite');

if($rechte->isBerechtigt('lehre/lehrveranstaltung', $oe_studiengang, 'suid'))
	$write_admin=true;

if($action=='delete')
{
	if($write_admin)
	{
		if(!$lvangebot->delete($lvangebot_id))
			$errorstr=$this->errormsg;
		else
			//reset, damit Daten nicht noch einmal ins Formular übernommen werden
			$lvangebot=new lvangebot();
	}
	else
		$errorstr='keine Berechtigung zum Löschen aus LV-Angebot';
}

if(isset($_POST["schick"]))
{
	if($write_admin)
	{
		if($new)
		{
			$lvangebot->new=true;
			$lvangebot->insertamum=date('Y-m-d H:i:s');
			$lvangebot->insertvon=$user;
		}
		else
		{
			$lvangebot->new=false;
			$lvangebot->updatenamum=date('Y-m-d H:i:s');
			$lvangebot->updatevon=$user;
		}

		if(isset($_POST['neue_gruppe_anlegen']))
		{
			$lehrveranstaltung_obj = new lehrveranstaltung();
			if(!$lehrveranstaltung_obj->load($_POST['lehrveranstaltung_id']))
				die('Fehler beim Laden der Lehrveranstaltung');

			$studiengang = new studiengang();
			if(!$studiengang->load($lehrveranstaltung_obj->studiengang_kz))
				die('Fehler beim Laden des Studienganges');

			$gruppe = new gruppe();
			$gruppe_kurzbz = mb_strtoupper(substr($studiengang->kuerzel.$lehrveranstaltung_obj->semester.'-'.$_POST['studiensemester_kurzbz'].'-'.$lehrveranstaltung_obj->kurzbz,0,32));
			$gruppe_kurzbz = $gruppe->getNummerierteGruppenbez($gruppe_kurzbz);
			$gruppe->gruppe_kurzbz=$gruppe_kurzbz;
			$gruppe->studiengang_kz=$studiengang->studiengang_kz;
			$gruppe->bezeichnung=mb_substr($lehrveranstaltung_obj->bezeichnung,0,30);
			$gruppe->semester=$lehrveranstaltung_obj->semester;
			$gruppe->sort='';
			$gruppe->mailgrp=false;
			$gruppe->beschreibung=$lehrveranstaltung_obj->bezeichnung;
			$gruppe->sichtbar=true;
			$gruppe->generiert=false;
			$gruppe->aktiv=true;
			$gruppe->lehre=true;
			$gruppe->content_visible=false;
			$gruppe->orgform_kurzbz=$lehrveranstaltung_obj->orgform_kurzbz;
			$gruppe->gesperrt=false;
			$gruppe->zutrittssystem=false;
			$gruppe->insertamum=date('Y-m-d H:i:s');
			$gruppe->insertvon=$user;

			if(!$gruppe->save(true))
			{
				die('Fehler beim Erstellen der Gruppe'.$gruppe->errormsg);
			}
		}
		else
			$gruppe_kurzbz=$_POST['gruppe_kurzbz'];

		$lvangebot->lehrveranstaltung_id=$_POST['lehrveranstaltung_id'];
		$lvangebot->studiensemester_kurzbz=$_POST['studiensemester_kurzbz'];
		$lvangebot->gruppe_kurzbz=$gruppe_kurzbz;
		$lvangebot->incomingplaetze=$_POST['incomingplaetze'];
		$lvangebot->gesamtplaetze=$_POST['gesamtplaetze'];
		$lvangebot->anmeldefenster_start=$datum_obj->formatDatum($_POST['anmeldefenster_start'], 'Y-m-d');
		$lvangebot->anmeldefenster_ende=$datum_obj->formatDatum($_POST['anmeldefenster_ende'],'Y-m-d');

		if(!$lvangebot->save())
			$errorstr = $lvangebot->errormsg;
		else
			$lvangebot = new lvangebot();
	}
	else
		$errorstr = 'keine Berechtigung zum Speichern in LV-Angebot';
}

?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Lehrveranstaltung - Details</title>
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<script type="text/javascript" src="../../include/js/datecheck.js"></script>
	<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css"/>
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<script type="text/javascript">
		$(function() {
			$("#anmeldefenster_start,#anmeldefenster_ende").datepicker();
		});

		$(document).ready(function()
		{
			$("#t1").tablesorter(
			{
				widgets: ["zebra"]
			});

			$('#gruppe_kurzbz').autocomplete({
				source: "lvangebot_autocomplete.php",
				minLength:1,
				response: function(event, ui)
				{
					//Value und Label fuer die Anzeige setzen
					for(i in ui.content)
					{
						ui.content[i].value=ui.content[i].gruppe_kurzbz;
						ui.content[i].label=ui.content[i].gruppe_kurzbz;
					}
				},
				select: function(event, ui)
				{
					ui.item.value=ui.item.gruppe_kurzbz;
				}
			});
		});

		function submitable()
		{
			document.getElementById("submsg").style.visibility="visible";
		}

		function confdel()
		{
			return confirm("Wollen Sie diesen Eintrag wirklich löschen?");
		}

		function NeueGruppeClick()
		{
			if($('#cboxNeueGruppe').prop('checked'))
			{
				$('#gruppe_kurzbz').attr("disabled", "disabled");
			}
			else
			{
				$('#gruppe_kurzbz').removeAttr("disabled");
			}
		}
	</script>
</head>
<body>
<?php
	echo '<h3>LV-Angebot - '.$lv_obj->bezeichnung.' ('.$lv_id.')</h3>
		<form action="lehrveranstaltung_lvangebot.php" method="POST">';

	if($action!='neu')
		echo '<input type="hidden" name="lvangebot_id" value="'.$lvangebot->lvangebot_id.'">';
	echo '<input type="hidden" name="lehrveranstaltung_id" value="'.$lv_id.'">
		<table class="tablesorter" id="t1">
			<thead>
				<tr>
				<th>Studiensemester</th>
				<th>Gruppe</th>
				<th title="Incomingplätze">Inc</th>
				<th title="Gesamtplätze">Ges</th>
				<th>Anmeldefenster Start</th>
				<th>Anmeldefenster Ende</th>
				<th></th>
				<th></th>
			</tr>
		</thead>
		<tbody>';

	// Vorhandene Eintraege anzeigen
	$lvangebotliste = new lvangebot();
	$lvangebotliste->getAllFromLvId($lv_id);

	foreach($lvangebotliste->result as $lvang)
	{
		echo '<tr>
			<td>'.$lvang->studiensemester_kurzbz.'</td>
			<td>'.$lvang->gruppe_kurzbz.'</td>
			<td>'.$lvang->incomingplaetze.'</td>
			<td>'.$lvang->gesamtplaetze.'</td>
			<td>'.$datum_obj->formatDatum($lvang->anmeldefenster_start,'d.m.Y').'</td>
			<td>'.$datum_obj->formatDatum($lvang->anmeldefenster_ende,'d.m.Y').'</td>
			<td><a href='.$_SERVER["PHP_SELF"].'?action=edit&lvangebot_id='.$lvang->lvangebot_id.'>Edit</a></td>
			<td><a href='.$_SERVER["PHP_SELF"].'?action=delete&lvangebot_id='.$lvang->lvangebot_id.' onclick="return confdel()">Delete</a></td>
			</tr>';
	}

	echo '</tbody><tfoot>';
	// Neu / Editieren Zeile
	if($action == 'edit')
		$disableDropdown=true;
	else
		$disableDropdown=false;
	echo '<tr>
			<th valign="top"><select name="studiensemester_kurzbz" '.($disableDropdown?'disabled="disabled':'onchange="submitable()"').'>';

	if($action!='edit')
	{
		$lvangebot = new lvangebot();
		// Bei neuen Eintraegen das aktuelle Studiensemester vorauswaehlen
		$stsem=new studiensemester();
		$lvangebot->studiensemester_kurzbz=$stsem->getaktorNext();
	}
	$stsem = new studiensemester();
	$stsem->getAll();

	foreach($stsem->studiensemester as $stsem)
	{
		if($lvangebot->studiensemester_kurzbz==$stsem->studiensemester_kurzbz)
			$selected='selected';
		else
			$selected='';
		echo "\n".'<option value="'.$stsem->studiensemester_kurzbz.'" '.$selected.'>'.$stsem->studiensemester_kurzbz.'</option>';
	}
	echo '</select>';
	if($disableDropdown)
		echo '<input type="hidden" name="studiensemester_kurzbz" value="'.$lvangebot->studiensemester_kurzbz.'" />';
	echo '</th>
		<th valign="top">
			<input type="text" name="gruppe_kurzbz" id="gruppe_kurzbz" onchange="submitable()" value="'.$lvangebot->gruppe_kurzbz.'"/>
			<br>neue Gruppe anlegen<input type="checkbox" id="cboxNeueGruppe" name="neue_gruppe_anlegen" onclick="NeueGruppeClick()"/>
		</th>
		<th valign="top">
			<input type="text" name="incomingplaetze" size="2" onchange="submitable()" value="'.$lvangebot->incomingplaetze.'"/>
		</th>
		<th valign="top">
			<input type="text" name="gesamtplaetze" size="2" onchange="submitable()" value="'.$lvangebot->gesamtplaetze.'"/>
		</th>
		<th valign="top">
			<input id="anmeldefenster_start" size="10" type="text" name="anmeldefenster_start" onchange="submitable()" value="'.$datum_obj->formatDatum($lvangebot->anmeldefenster_start,'d.m.Y').'"/>
		</th>
		<th valign="top">
			<input id="anmeldefenster_ende" size="10" type="text" name="anmeldefenster_ende" onchange="submitable()" value="'.$datum_obj->formatDatum($lvangebot->anmeldefenster_ende,'d.m.Y').'"/>
		</th>
		<th  valign="top" colspan="2">
			<input type="submit" name="schick" value="'.($lvangebot->lvangebot_id==''?'Anlegen':'Ändern').'" />
		</th>
	</tr>
	</tfoot>
	</table>
	</form>';
	echo '<span id="submsg" style="color:red; visibility:hidden;">Datensatz ge&auml;ndert!&nbsp;&nbsp;</span>';
	echo "<div class='inserterror'>".$errorstr."</div>\n";
	echo '<a href="lehrveranstaltung_lvangebot.php?lehrveranstaltung_id='.$lv_id.'">Neuen Eintrag anlegen</a>';
?>
</body>
</html>
