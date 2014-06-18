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
require_once('../../include/studiengang.class.php');
require_once('../../include/dokument.class.php');

$stg_kz=isset($_REQUEST['stg_kz'])?$_REQUEST['stg_kz']:'';
$dokument_kurzbz=isset($_REQUEST['dokument_kurzbz'])?$_REQUEST['dokument_kurzbz']:'';
$onlinebewerbung=isset($_REQUEST['onlinebewerbung']);

$action=isset($_GET['action'])?$_GET['action']:'';
if(isset($_POST['add']))
	$action='add';
if(isset($_POST['saveDoc']))
	$action='saveDoc';

if($action=='add')
{
	if($dokument_kurzbz != '' && $stg_kz != '')
	{
		$dokument=new dokument();
		$dokument->addDokument($dokument_kurzbz, $stg_kz, $onlinebewerbung);
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
	$output .= '<table>
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
	foreach($dokStg->result as $dok)
	{
		$checked=$dok->onlinebewerbung?' checked':'';
		$output .= '<tr>
			<td>'.$dok->bezeichnung.'</td>
			<td><input type="checkbox"'.$checked.'></td>
			<td><a href="'.$_SERVER['PHP_SELF'].'?action=delete&dokument_kurzbz='.$dok->dokument_kurzbz.'&stg_kz='.$stg_kz.'">Zuordnung löschen</a></td>
			</td>
		</tr>';
	}
	$output .= '
		<tr>
			<td>-</td>
			<td></td>
		</tr>
		<tr>
			<td><select name="dokument_kurzbz">';
	$dokAll=new dokument();
	$dokAll->getAllDokumente();
	foreach($dokAll->result as $dok)
	{
		$output .= '<option value="'.$dok->dokument_kurzbz.'">'.$dok->bezeichnung.'</option>';
	}
	$output .= '</select></td>
			<td><input type="checkbox" name="onlinebewerbung" checked></td>
		</tr>
	</tbody>
	</table>
	<input type="submit" name="add" value="Hinzufügen">
</form>
	<br/>
	<br/>
	<input type="button" onclick="showDocumentForm()" value="neues Dokument erstellen">
	<div id="documentForm" style="visibility:hidden">
	<form action="'.$_SERVER['PHP_SELF'].'" method="post">
	<input type="hidden" name="stg_kz" value="'.$stg_kz.'">
	<table>
		<tr>
			<td>Kurzbezeichnung</td>
			<td><input type="text" id="dokument_kurzbz" name="dokument_kurzbz"></td>
		</tr>
		<tr>
			<td>Bezeichnung</td>
			<td><input type="text" id="dokument_bezeichnung" name="dokument_bezeichnung"></td>
		</tr>
	</table>
	<input type="submit" name="saveDoc" value="Speichern">
	</form>
	</div>';
}
else
	$output .= '</form>';


echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css">
	<script type="text/javascript">
		$(document).ready(function() 
		{ 
			$("#t1").tablesorter(); 
		}); 
		
		function showDocumentForm(dokument_kurzbz="",bezeichnung="",neu=true)
		{
			document.getElementById("dokument_kurzbz").value=dokument_kurzbz;
			document.getElementById("dokument_bezeichnung").value=bezeichnung;
			document.getElementById("documentForm").style.visibility="visible";
			if(!neu)
			{
				document.getElementById("dokument_kurzbz").readOnly=true;
				/* document.getElementById("dokument_kurzbz").style=
					"background-color:#F2F2F2;
					color: #C6C6C6;
					border-color:#ddd"; */
			}
		}
		
	</script>
<head>
<title>Zuordnung Studiengang - Dokumente</title>
</head>
<body>
'.$output.'
</body>
</html>';

?>
