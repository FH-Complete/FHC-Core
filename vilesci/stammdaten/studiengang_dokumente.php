<?php
/*
 * Copyright 2014 fhcomplete.org
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 *
 * Authors: Martin Tatzber <tatzberm@technikum-wien.at>
 *
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/dokument.class.php');
require_once('../../include/benutzerberechtigung.class.php');

$stg_kz=isset($_REQUEST['stg_kz'])?$_REQUEST['stg_kz']:'';
$dokument_kurzbz=isset($_REQUEST['dokument_kurzbz'])?$_REQUEST['dokument_kurzbz']:'';
$onlinebewerbung=isset($_REQUEST['onlinebewerbung']);

$action=isset($_GET['action'])?$_GET['action']:'';
if(isset($_POST['add']))
	$action='add';
if(isset($_POST['saveDoc']))
	$action='saveDoc';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('basis/studiengang', $stg_kz, 'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

if($action=='add')
{
	if($dokument_kurzbz != '' && $stg_kz != '')
	{
		$dokument=new dokument();
		$dokument->dokument_kurzbz = $dokument_kurzbz;
		$dokument->studiengang_kz = $stg_kz;
		$dokument->onlinebewerbung = $onlinebewerbung;
		$dokument->saveDokumentStudiengang();
	}
}

if($action=='delete')
{
	if($dokument_kurzbz != '' && $stg_kz != '')
	{
		$dokument=new dokument();
		if(!$dokument->deleteDokumentStg($dokument_kurzbz, $stg_kz))
			echo 'Fehler beim Löschen: '.$dokument->errormsg;
	}
}

if($action =='toggleonline')
{
	if($dokument_kurzbz != '' && $stg_kz != '')
	{
		$dokument=new dokument();
		if($dokument->loadDokumentStudiengang($dokument_kurzbz, $stg_kz))
		{
			$dokument->onlinebewerbung = !$dokument->onlinebewerbung;
			if(!$dokument->saveDokumentStudiengang())
				echo $dokument->errormsg;
		}
		else
			echo 'Zuordnung ist nicht vorhanden';
	}
}

if($action=='saveDoc')
{
	$dokBezeichnung=isset($_POST['dokument_bezeichnung'])?$_POST['dokument_bezeichnung']:'';
	$dokKurzbz=isset($_POST['dokument_kurzbz'])?$_POST['dokument_kurzbz']:'';
	
	if($dokBezeichnung!='')
	{
		$dokument=new dokument();
		$dokument->dokument_kurzbz=$dokKurzbz;
		$dokument->bezeichnung=$dokBezeichnung;
		
		if($dokument->saveDokument(true))
		{
			echo 'Dokument hinzugefügt';
		}
		else
		{
			echo $dokument->errormsg;
		}
	}
}

$studiengang=new studiengang();
$studiengang->getAll('typ, kurzbz');

$output='<h1>Zuteilung Studiengang - Dokumente</h1>
<form action='.$_SERVER['PHP_SELF'].' method="post">
	<select name="stg_kz">';
foreach ($studiengang->result as $stg)
{
	if($stg_kz==$stg->studiengang_kz)
		$selected=' selected';
	else
		$selected='';
	$output .= '<option value="'.$stg->studiengang_kz.'"'.$selected.'>'.$stg->kurzbzlang.' '.$stg->bezeichnung.'</option>';
}
$output .= '</select>
<input type="submit" value="Anzeigen">
<br/>
<br/>';


if($stg_kz!='')
{
	$output .= '<table id="t1" class="tablesorter">
	<thead>
	<tr>
		<th>Dokumentname</th>
		<th>Online-Bewerbung</th>
		<th></th>
	</tr>
	</thead>
	<tbody>';
	$dokStg=new dokument();
	$dokStg->getDokumente($stg_kz);
	$zugewieseneDokumente=array();
	foreach($dokStg->result as $dok)
	{
		$zugewieseneDokumente[]=$dok->dokument_kurzbz;
		$checked=$dok->onlinebewerbung?'true':'false';
		$output .= '<tr>
			<td>'.$dok->bezeichnung.'</td>
			<td><a href="'.$_SERVER['PHP_SELF'].'?action=toggleonline&dokument_kurzbz='.$dok->dokument_kurzbz.'&stg_kz='.$stg_kz.'"><img src="../../skin/images/'.$checked.'.png" /></a></td>
			<td><a href="'.$_SERVER['PHP_SELF'].'?action=delete&dokument_kurzbz='.$dok->dokument_kurzbz.'&stg_kz='.$stg_kz.'">Zuordnung löschen</a></td>
			</td>
		</tr>';
	}
	$output .= '
	</tbody>
	<tfoot>
		<tr>
			<td><select name="dokument_kurzbz">';
	$dokAll=new dokument();
	$dokAll->getAllDokumente();
	foreach($dokAll->result as $dok)
	{
		if(!in_array($dok->dokument_kurzbz,$zugewieseneDokumente))
			$output .= '<option value="'.$dok->dokument_kurzbz.'">'.$dok->bezeichnung.'</option>';
	}
	$output .= '</select></td>
			<td><input type="checkbox" name="onlinebewerbung" checked></td>
			<td><input type="submit" name="add" value="Hinzufügen"></td>
		</tr>
	</tfoot>
	</table>

</form>
	<br/>
	<br/>
	<input type="button" onclick="showDocumentForm()" value="neuen Dokumenttyp erstellen">
	<div id="documentForm" style="visibility:hidden">
	<form action="'.$_SERVER['PHP_SELF'].'" method="post">
	<input type="hidden" name="stg_kz" value="'.$stg_kz.'">
	<table>
		<tr>
			<td>Kurzbezeichnung</td>
			<td><input type="text" id="dokument_kurzbz" name="dokument_kurzbz" maxlength="8" size="8"></td>
		</tr>
		<tr>
			<td>Bezeichnung</td>
			<td><input type="text" id="dokument_bezeichnung" name="dokument_bezeichnung" maxlength="128"></td>
		</tr>
	</table>
	<input type="submit" name="saveDoc" value="Speichern">
	</form>
	</div>';
}
else
	$output .= '</form>';


echo '<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css">
	<script type="text/javascript" src="../../include/js/jquery.js"></script>

	<script type="text/javascript">
		$(document).ready(function() 
		{ 
			$("#t1").tablesorter(
			{
				sortList: [[0,0]],
				widgets: ["zebra"],
				headers: {2:{sorter:false}}
			}); 
		}); 
		
		function showDocumentForm(dokument_kurzbz="",bezeichnung="",neu=true)
		{
			document.getElementById("dokument_kurzbz").value=dokument_kurzbz;
			document.getElementById("dokument_bezeichnung").value=bezeichnung;
			document.getElementById("documentForm").style.visibility="visible";
			if(!neu)
			{
				document.getElementById("dokument_kurzbz").readOnly=true;
			}
		}
		
	</script>
	<title>Zuordnung Studiengang - Dokumente</title>
</head>
<body>
'.$output.'
</body>
</html>';

?>
