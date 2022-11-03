<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/infoscreen.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/datum.class.php');

$user = get_uid();
$basis = new basis();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/infoscreen'))
	die($rechte->errormsg);

$datum_obj = new datum();

$action = isset($_GET['action'])?$_GET['action']:'show';
$infoscreen_id = isset($_GET['infoscreen_id'])?$_GET['infoscreen_id']:'';

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Infoscreen</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
	<script type="text/javascript">

		$(document).ready(function()
			{
			    $("#myTable").tablesorter(
				{
					sortList: [[2,0]],
					widgets: [\'zebra\']
				});
			}
		);
		</script>
</head>
<body>
<h2>Infoscreen &Uuml;bersicht</h2>
<div style="text-align:right">
	<a href="infoscreen_preview.php" target="main">Übersicht anzeigen</a>&nbsp;|&nbsp;
	<a href="infoscreen_uebersicht.php?action=new" target="uebersicht_infoscreen">Neuen Infoscreen hinzufügen</a>
</div>';

if($action=='save')
{
	$infoscreen_id = $_POST['infoscreen_id'];
	$bezeichnung = $_POST['bezeichnung'];
	$beschreibung = $_POST['beschreibung'];
	$ipadresse = $_POST['ipadresse'];

	$infoscreen = new infoscreen();
	if($infoscreen_id!='')
	{
		$infoscreen->load($infoscreen_id);
		$infoscreen->new = false;
	}
	else
		$infoscreen->new = true;

	$infoscreen->bezeichnung = $bezeichnung;
	$infoscreen->beschreibung = $beschreibung;
	$infoscreen->ipadresse = $ipadresse;

	if(!$infoscreen->save())
		echo '<span class="error">',$basis->convert_html_chars($infoscreen->errormsg),'</span>';
	else
		echo '<span class="ok">Daten erfolgreich gespeichert</span>';
}

if($action=='new' || $action=='update')
{
	$infoscreen = new infoscreen();
	if($action=='new')
	{
		echo '<h3>Neu</h3>';
	}
	else
	{
		echo '<h3>Bearbeiten von ID ',$basis->convert_html_chars($infoscreen_id),'</h3>';
		if(!$infoscreen->load($infoscreen_id))
			die('Fehler: '.$infoscreen->errormsg);
	}
	echo '
	<form action="',$_SERVER['PHP_SELF'],'?action=save" method="POST">
	<input type="hidden" name="infoscreen_id" value="',$basis->convert_html_chars($infoscreen->infoscreen_id),'">
	<table>
	<tr>
		<td>Bezeichnung</td>
		<td><input type="text" name="bezeichnung" size="60" maxlength="64" value="',$basis->convert_html_chars($infoscreen->bezeichnung),'" /></td>
	</tr>
	<tr>
		<td>Beschreibung</td>
		<td><input type="text" name="beschreibung" size="60" value="',$basis->convert_html_chars($infoscreen->beschreibung),'" /></td>
	</tr>
	<tr>
		<td>IP Adresse</td>
		<td><input type="text" name="ipadresse" size="50" maxlength="50" value="',$basis->convert_html_chars($infoscreen->ipadresse),'" /></td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" value="Speichern" /></td>
	</tr>
	</table>
	</form>';
}
if($action=='reboot')
{
	//if(!$rechte->isBerechtigt('admin'))
		//die($rechte->errormsg);

	require_once("../../vendor/autoload.php");

	if(isset($_GET["ip"]) && $_GET["ip"])
	{
		$ssh = new \phpseclib\Net\SSH2($_GET["ip"]);
		if (!$ssh->login(INFOSCREEN_USER, INFOSCREEN_PASSWORD))
		{
			exit('Login Failed');
		}
		echo $ssh->exec('reboot') . "<br>";
	}
	echo '<script>window.location.href = "infoscreen_uebersicht.php";</script>';
}

$infoscreen = new infoscreen();

if(!$infoscreen->getAll())
	die($infoscreen->errormsg);

echo '<table class="tablesorter" id="myTable">
	<thead>
		<tr>
			<th>ID</th>
			<th>Bezeichnung</th>
			<th>Beschreibung</th>
			<th>IP</th>
			<th colspan="2">Aktion</th>
		</tr>
	</thead>
	<tbody>';

foreach($infoscreen->result as $row)
{
	echo '<tr>';
	echo '<td>',$basis->convert_html_chars($row->infoscreen_id),'</td>';
	echo '<td>',$basis->convert_html_chars($row->bezeichnung),'</td>';
	echo '<td>',$basis->convert_html_chars($row->beschreibung),'</td>';
	echo '<td>',$basis->convert_html_chars($row->ipadresse),'</td>';
	//if($rechte->isBerechtigt('admin'))
		echo '<td><a href="infoscreen_uebersicht.php?action=reboot&ip='.$row->ipadresse.' " target="uebersicht_infoscreen">Reboot</a></td>';
	echo '<td><a href="infoscreen_details.php?action=show&infoscreen_id=',$basis->convert_html_chars($row->infoscreen_id),' " target="detail_infoscreen">details</a></td>';
	echo '<td><a href="infoscreen_uebersicht.php?action=update&infoscreen_id=',$basis->convert_html_chars($row->infoscreen_id),' " target="uebersicht_infoscreen">bearbeiten</a></td>';
	echo '</tr>';
}
echo '</tbody>
</table>
</body>
</html>';
?>
