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

$stg_kz = isset($_REQUEST['stg_kz']) ? $_REQUEST['stg_kz'] : '0';
$dokument_kurzbz = isset($_REQUEST['dokument_kurzbz']) ? $_REQUEST['dokument_kurzbz'] : '';
$onlinebewerbung = isset($_REQUEST['onlinebewerbung']);

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
        $dokument->pflicht = filter_input(INPUT_POST, 'pflicht', FILTER_VALIDATE_BOOLEAN);
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

if($action === 'togglepflicht') {
    if($dokument_kurzbz != '' && $stg_kz != '')
    {
        $dokument=new dokument();
        if($dokument->loadDokumentStudiengang($dokument_kurzbz, $stg_kz))
        {
            $dokument->pflicht = !$dokument->pflicht;
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
<body>';


if(isset($_GET['action']) && $_GET['action']=='dokumenttypen')
{
	echo '<h1>Dokumenttypen</h1>';

	if(isset($_GET['type']))
	{
		if($_GET['type']=='delete')
		{
			$dokument = new dokument();
			if(!$dokument->deleteDokumenttyp($_GET['dokument_kurzbz']))
				echo $dokument->errormsg;
			
		}
	}
	if(isset($_POST['saveDokumenttyp']))
	{
		$dokument = new dokument();
		$dokument->dokument_kurzbz=$_POST['dokument_kurzbz'];
		$dokument->bezeichnung = $_POST['dokument_bezeichnung'];
		if(isset($_POST['neu']) && $_POST['neu']=='true')
			$neu=true;
		else
			$neu=false;

		if(!$dokument->saveDokument($neu))
			echo $dokument->errormsg;
	}

	$dokument = new dokument();
	$dokument->getAllDokumente();

	echo '
	<form action="'.$_SERVER['PHP_SELF'].'?action=dokumenttypen" method="post">
	<table id="t1" class="tablesorter" style="width:auto">
	<thead>
		<th></th>
		<th>Kurzbz</th>
		<th>Bezeichnung</th>
	</thead>
	<tbody>
		';
	foreach($dokument->result as $row)
	{
		echo '<tr>
				<td>
					<a href="'.$_SERVER['PHP_SELF'].'?action=dokumenttypen&type=edit&dokument_kurzbz='.$row->dokument_kurzbz.'"><img src="../../skin/images/edit.png" title="Bearbeiten" /></a>
					';
		// Lichtbil und Zeugnis duerfen nicht geloescht werden da diese fuer Bildupload und 
		// Zeugnisarchivierung verwendet werden
		if(!in_array($row->dokument_kurzbz,array('Lichtbil','Zeugnis')))
			echo '<a href="'.$_SERVER['PHP_SELF'].'?action=dokumenttypen&type=delete&dokument_kurzbz='.$row->dokument_kurzbz.'"><img src="../../skin/images/cross.png" title="Löschen" /></a>';

		echo '
				</td>
				<td>'.$row->dokument_kurzbz.'</td>
				<td>'.$row->bezeichnung.'</td>				
			</tr>';
	}

	$dokument_kurzbz='';
	$dokument_bezeichnung='';

	if(isset($_GET['type']) && $_GET['type']=='edit')
	{
		$dokument = new dokument();
		if($dokument->loadDokumenttyp($_GET['dokument_kurzbz']))
		{
			$dokument_kurzbz = $dokument->dokument_kurzbz;
			$dokument_bezeichnung = $dokument->bezeichnung;
		}
	}

	echo '
	</tbody>
	<tfoot>
		<tr>
			<td></td>
			<td>
				<input typ="text" id="dokument_kurzbz" name="dokument_kurzbz" maxlength="8" size="8" '.($dokument_kurzbz!=''?'readonly':'').' value="'.$dokument_kurzbz.'"/>
				<input type="hidden" id="neu" name="neu" value="'.($dokument_kurzbz==''?'true':'false').'" />
			</td>
			<td><input type="text" id="dokument_bezeichnung" name="dokument_bezeichnung" maxlength="128" value="'.$dokument_bezeichnung.'">
			<input type="submit" name="saveDokumenttyp" value="Speichern"></td>
		</tr>
	</tfoot>
	</table>
	</form>';
}
else
{
	$studiengang=new studiengang();
	$studiengang->getAll('typ, kurzbz');

	echo '<h1>Zuteilung Studiengang - Dokumente</h1>
	<table width="100%">
	<tr>
	<td>
	<form action='.$_SERVER['PHP_SELF'].' method="post">
		<select name="stg_kz">';
	foreach ($studiengang->result as $stg)
	{
		if($stg_kz==$stg->studiengang_kz)
			$selected=' selected';
		else
			$selected='';
		echo '<option value="'.$stg->studiengang_kz.'"'.$selected.'>'.$stg->kurzbzlang.' '.$stg->bezeichnung.'</option>';
	}
	echo '</select>
	<input type="submit" value="Anzeigen">
	</td><td align="right">
	<a href="'.$_SERVER['PHP_SELF'].'?action=dokumenttypen">Dokumenttypen verwalten</a>
	</td></tr></table>

	<br/>';

	if($stg_kz!='')
	{
		echo '<table id="t1" class="tablesorter">
		<thead>
		<tr>
			<th>Dokumentname</th>
			<th>Online-Bewerbung</th>
			<th>Pflicht</th>
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
            $checked_onlinebewerbung = $dok->onlinebewerbung ? 'true' : 'false';
            $checked_pflicht = $dok->pflicht ? 'true' : 'false';
			echo '<tr>
				<td>'.$dok->bezeichnung.'</td>
				<td><a href="'.$_SERVER['PHP_SELF'].'?action=toggleonline&dokument_kurzbz='.$dok->dokument_kurzbz.'&stg_kz='.$stg_kz.'"><img src="../../skin/images/'.$checked_onlinebewerbung.'.png" /></a></td>
				<td><a href="'.$_SERVER['PHP_SELF'].'?action=togglepflicht&dokument_kurzbz='.$dok->dokument_kurzbz.'&stg_kz='.$stg_kz.'"><img src="../../skin/images/'.$checked_pflicht.'.png" /></a></td>
				<td><a href="'.$_SERVER['PHP_SELF'].'?action=delete&dokument_kurzbz='.$dok->dokument_kurzbz.'&stg_kz='.$stg_kz.'">Zuordnung löschen</a></td>
				</td>
			</tr>';
		}
		echo '
		</tbody>
		<tfoot>
			<tr>
				<td><select name="dokument_kurzbz">';
		$dokAll=new dokument();
		$dokAll->getAllDokumente();
		foreach($dokAll->result as $dok)
		{
			if(!in_array($dok->dokument_kurzbz,$zugewieseneDokumente))
				echo '<option value="'.$dok->dokument_kurzbz.'">'.$dok->bezeichnung.'</option>';
		}
		echo '</select></td>
				<td><input type="checkbox" name="onlinebewerbung" checked></td>
				<td>
				    <input type="hidden" name="pflicht" value="0">
				    <input type="checkbox" name="pflicht" value="1" checked>
				</td>
				<td><input type="submit" name="add" value="Hinzufügen"></td>
			</tr>
		</tfoot>
		</table>

	</form>';
	}
	else
		echo '</form>';
}
echo '
</body>
</html>';

?>
