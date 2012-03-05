<?php
/* Copyright (C) 2011 FH Technikum Wien
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
 * Authors:		Karl Burkhart <karl.burkhart@technikum-wien.at>
 * 
 */
require_once('../config/cis.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/dms.class.php');
require_once('../include/gruppe.class.php');
require_once('../include/benutzerberechtigung.class.php');


$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isberechtigt('basis/dms',null, 'suid', null))
	die('Sie haben keine Berechtigung diese Seite zu sehen.');
	
$kategorie_kurzbz = isset($_REQUEST['kategorie_kurzbz'])?$_REQUEST['kategorie_kurzbz']:'';
	
$method = isset($_REQUEST['method'])?$_REQUEST['method']:'';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//DE"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Admin Document Management System</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../skin/fhcomplete.css" type="text/css">
		<link rel="stylesheet" href="../skin/style.css.php" type="text/css">
		<link href="../skin/tablesort.css" rel="stylesheet" type="text/css"/>
		<link href="../skin/jquery.css" rel="stylesheet" type="text/css"/>
		<link href="../skin/fhcomplete.css" rel="stylesheet" type="text/css">
		<link href="../skin/style.css.php" rel="stylesheet" type="text/css">
		<script type="text/javascript" src="../include/tiny_mce/tiny_mce.js"></script>
		<script type="text/javascript" src="../include/js/jquery.js"></script>
		<script type="text/javascript">

		var __js_page_array = new Array();
	    function js_toggle_container(conid)
	    {
			if (document.getElementById)
			{
	        	var block = "table-row";
				if (navigator.appName.indexOf('Microsoft') > -1)
					block = 'block';
					
				// Aktueller Anzeigemode ermitteln	
	            var status = __js_page_array[conid];
	            if (status == null)
				{
			 		if (document.getElementById && document.getElementById(conid)) 
					{  
						status=document.getElementById(conid).style.display;
					} else if (document.all && document.all[conid]) {      
						status=document.all[conid].style.display;
			      	} else if (document.layers && document.layers[conid]) {                          
					 	status=document.layers[conid].style.display;
			        }							
				}	
				
				// Anzeigen oder Ausblenden
	            if (status == 'none')
	            {
			 		if (document.getElementById && document.getElementById(conid)) 
					{  
						document.getElementById(conid).style.display = 'block';
					} else if (document.all && document.all[conid]) {      
						document.all[conid].style.display='block';
			      	} else if (document.layers && document.layers[conid]) {                          
					 	document.layers[conid].style.display='block';
			        }				
	            	__js_page_array[conid] = 'block';
	            }
	            else
	            {
			 		if (document.getElementById && document.getElementById(conid)) 
					{  
						document.getElementById(conid).style.display = 'none';
					} else if (document.all && document.all[conid]) {      
						document.all[conid].style.display='none';
			      	} else if (document.layers && document.layers[conid]) {                          
					 	document.layers[conid].style.display='none';
			        }				
	            	__js_page_array[conid] = 'none';
	            }
	            return false;
	     	}
	     	else
	     		return true;
	  	}
	</script>
	</head>
<body>
<?php 

if(isset($_REQUEST['save']))
{
	if($method == 'gruppe')
	{
		// Speichert die Gruppenzugehörigkeit
		if($_REQUEST['kategorie_kurzbz'] != '')
		{
			$dms = new dms(); 
			$dms->kategorie_kurzbz = $_REQUEST['kategorie_kurzbz'];
			$dms->gruppe_kurzbz = $_POST['gruppe_kurzbz'];
			$dms->insertamum = date('Y-m-d H:i:s');
			$dms->insertvon = $user; 
			
			if(!$dms->saveGruppeKategorie())
				echo $dms->errormsg; 
			else
				echo "Gruppe erfolgreich zugeteilt";
		}
		else 
		{
			echo "Keine gültige Kategorie übergeben"; 
		}
	}
	else
	{
		$kategorieSave = new dms(); 
		if($_POST['kategorie_kurzbz'] != '')
		{
			// wenn keine auswahl getroffen wurde
			$kategorie_auswahl = (($_POST['kategorie_parent']=='auswahl')?null:$_POST['kategorie_parent']);
			
			if($kategorieSave->loadKategorie($_POST['kategorie_kurzbz']))
			{
				// Update
				$kategorieSave->bezeichnung = $_POST['kategorie_bezeichnung'];
				$kategorieSave->beschreibung =$_POST['kategorie_beschreibung'];
				$kategorieSave->parent_kategorie_kurzbz = $kategorie_auswahl;
				$kategorieSave->new=false; 
				if(!$kategorieSave->saveKategorie())
					exit($kategorieSave->errormsg);
				else 
					echo"Erfolgreich gespeichert"; 
				
			}
			else 
			{
				// Neu anlegen
				$kategorieSave->kategorie_kurzbz = $_POST['kategorie_kurzbz'];
				$kategorieSave->bezeichnung = $_POST['kategorie_bezeichnung'];
				$kategorieSave->beschreibung =$_POST['kategorie_beschreibung'];
				$kategorieSave->parent_kategorie_kurzbz = $kategorie_auswahl;
				$kategorieSave->new=true; 
				if(!$kategorieSave->saveKategorie())
					exit($kategorieSave->errormsg);
				else
					echo"Erfolgreich gespeichert"; 
			}
		}
		else
			echo "Kategorie_kurzbz darf nicht null sein."; 
	}
}

// Löscht eine Kategorie
if(isset($_REQUEST['delete']))
{
	
	if($method=='gruppe')
	{
		$dms = new dms(); 
		if(!$dms->deleteGruppe($_REQUEST['kategorie_kurzbz'], $_REQUEST['gruppe_kurzbz']))
			echo $dms->errormsg; 
		else 
			echo "Gruppe erfolgreich gelöscht"; 
		
	}
	else 
	{
		if(isset($_REQUEST['kategorie_kurzbz']))
		{
			$dms = new dms(); 
			if(!$dms->deleteKategorie($_REQUEST['kategorie_kurzbz']))
				echo $dms->errormsg; 
			else 
				echo "Erfolgreich gelöscht"; 
		}
		else 
			echo "keine Kategorie übergeben"; 
			
		$kategorie_kurzbz ='';
	}
}
	//Kategorien anzeigen
	$dms = new dms();
	$dms->getKategorie();
	
echo'	<table cellspacing=0 border="0">
			<tr> 
				<td valign="top" nowrap style="border-right: 1px solid lightblue;border-top: 1px solid lightblue;padding-right:5px">
					<h3>Kategorie:</h3>
	<table class="tabcontent" border="0">
	<tr>
		<td width="159" valign="top" class="tdwrap">
			<table class="tabcontent" border="0">
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>';
	drawKategorieMenue($dms->result);
	echo '
	<tr><td>&nbsp;</td></tr>
	<tr><td colspan="2"><a href ="'.$_SERVER['PHP_SELF'].'">Neue Kategorie anlegen</a></td></tr>

	
	</table></td></tr></table>';
	echo '<script>
	
	
	$(document).ready(function() 
	{ 
		OpenTreeToKategorie("'.$kategorie_kurzbz.'");
	});
	
	//Klappt den Kategoriebaum auf, damit die ausgewaehlte Kategorie sichtbar ist
	function OpenTreeToKategorie(kategorie)
	{
		elem = document.getElementById(kategorie);
		if(elem.nodeName=="TABLE")
			elem.style.display="block";
		while(true)
		{
			if(!elem.parentNode)
				break;
			else
				elem = elem.parentNode;
			
			if(elem.nodeName=="TABLE" && elem.className=="tabcontent")
				elem.style.display="block";
		}				
	}
	</script>';
	echo '</td>
		<td valign="top" style="border-top: 1px solid lightblue; width: 100%;">
		<a href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$kategorie_kurzbz.'">Eigenschaften</a> | <a href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$kategorie_kurzbz.'&method=gruppe">Gruppen</a><br><br>';
		
		switch($method)
		{
			case 'gruppe':
				print_rights($kategorie_kurzbz);
			break; 
				
			default: 
				drawKategorie($kategorie_kurzbz);
			break; 
		}
		
	
	echo '
			</td>
		</tr>
		</table>';
	

function drawKategorie($kategorie_kurzbz)
{ 
	$kategorie = new dms(); 
	$kategorie_beschreibung = '';
	$kategorie_bezeichnung = '';
	$disabled='';
	
	if($kategorie->loadKategorie($kategorie_kurzbz))
	{
		// Formular zum Editieren bestehender Kategorien
		$kategorie_bezeichnung = $kategorie->bezeichnung; 
		$kategorie_beschreibung = $kategorie->beschreibung;
		$disabled = 'disabled="true"';
	} 
	
	$allKategorien = new dms(); 
	$allKategorien->getAllKategories();
	//var_dump($allKategorien->result);
	echo '	<form action="'.$_SERVER['PHP_SELF'].'?save" method="POST" name="form_kategorie">	
				<table border="0">
					<tr>
						<td>Kategorie_kurzbz: </td><td><input type="text" name="kategorie_kurzbz" value="'.$kategorie_kurzbz.'" '.$disabled.' ></td>
					</tr>
					<tr>
						<td>Kategorie Bezeichnung: </td><td><input type="text" name="kategorie_bezeichnung" value="'.$kategorie_bezeichnung.'"></td>
					</tr>
					<tr>
						<td>Kategorie Beschreibung: </td><td><textarea name="kategorie_beschreibung" cols="30" rows="3">'.$kategorie_beschreibung.'</textarea></td>
					</tr>
					<tr>
						<td>Hängt unter: </td><td><select name="kategorie_parent">
						<option value="auswahl">-- Bitte Auswählen --</option>';
					foreach($allKategorien->result as $kategorienResult)
					{
						$selected ='';
						if($kategorienResult->kategorie_kurzbz == $kategorie->parent_kategorie_kurzbz )
							$selected='selected';
						if($kategorienResult->kategorie_kurzbz != $kategorie->kategorie_kurzbz)	
							echo '<option '.$selected.' value="'.$kategorienResult->kategorie_kurzbz.'">'.$kategorienResult->bezeichnung.' ['.$kategorienResult->kategorie_kurzbz.']</option>';
					}
	
	echo'				</select>
						</td>
					</tr>
					<tr><td>&nbsp;</td></tr>
					<tr></tr>
					<tr>
						<td><input type="submit" value="Speichern" onclick="document.form_kategorie.kategorie_kurzbz.disabled=false";></td>						
					</tr>
				</table></form>';
}	
	

/**
 * Erstellt den Karteireiter zum Verwalten der Zugriffsrechte auf einen Content
 * Zu einem Content können Gruppen zugeteilt werden. Diese haben dann zugriff auf den Content
 * Wenn keine Gruppen zugeordnet sind, können alle Personen auf den Content zugreifen
 */
function print_rights($kategorie_kurzbz)
{
	$dms = new dms();
	$dms->loadGruppenForKategorie($kategorie_kurzbz);
	
	if(count($dms->result)>0)
	{
		echo 'Die Mitglieder der folgenden Gruppen dürfen die Seite ansehen:<br><br>';
		echo '
		<script type="text/javascript">
			$(document).ready(function() 
			{ 
				$("#rights_table").tablesorter(
				{
					sortList: [[1,1]],
					widgets: ["zebra"]
				});
			});
		</script>';
		echo '<table id="rights_table" class="tablesorter" style="width: auto;">
			<thead>
			<tr>
				<th>Gruppe Kurzbz</th>
				<th>Bezeichnung</th>
				<th></th>
			</tr>
			</thead>
			<tbody>';
		foreach($dms->result as $row)
		{
			echo '<tr>';
			echo '<td>',$row->gruppe_kurzbz,'</td>';
			echo '<td>',$row->bezeichnung,'</td>';
			echo '<td>
					<a href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$kategorie_kurzbz.'&gruppe_kurzbz='.$row->gruppe_kurzbz.'&method=gruppe&delete" title="entfernen">
						<img src="../skin/images/delete_x.png">
					</a>
				</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
	else
		echo 'Diese Seite darf von allen angezeigt werden!<br><br>';
		
	$gruppe = new gruppe();
	$gruppe->getgruppe(null, null, null, null, true);
	
	echo '<form action="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$kategorie_kurzbz.'&method=gruppe&save" method="POST">';
	echo 'Gruppe <select name="gruppe_kurzbz">';
	foreach($gruppe->result as $row)
	{
		echo '<option value="'.$row->gruppe_kurzbz.'">'.$row->gruppe_kurzbz.'</option>';
	}
	echo '</select>';
	echo '<input type="submit" value="Hinzufügen" name="addgroup">';
	echo '</form>';
}


/**
 * Zeichnet das Kategorie Menu
 * 
 * @param $rows DMS Result Object
 */
function drawKategorieMenue($rows)
{	
	global $kategorie_kurzbz;
	
	//echo '<ul>';
	foreach($rows as $row)
	{
		if($kategorie_kurzbz==$row->kategorie_kurzbz)
			$class='marked';
		else
			$class='';
		
		$dms = new dms();
		$dms->getKategorie($row->kategorie_kurzbz);
		
		$delete = '<a href="'.$_SERVER['PHP_SELF'].'?delete&kategorie_kurzbz='.$row->kategorie_kurzbz.'"><img src="../skin/images/cross.png" height="12px" title="Kategorie löschen" /></a>';
		
		//Suchen, ob eine Sperre fuer diese Kategorie vorhanden ist
		$groups = $dms->getLockGroups($row->kategorie_kurzbz);
		$locked='';
		if(count($groups)>0)
		{
			$locked = '<img src="../skin/images/login.gif" height="12px" title="Zugriff nur für Mitglieder folgender Gruppen:';
			foreach($groups as $group)
				$locked.=" $group ";
			$locked.='"/>';
		}
		if(count($dms->result)>0)
		{
			
			echo '
			<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
	          	<td class="tdwrap">
	          		<a href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$row->kategorie_kurzbz.'" class="MenuItem" onClick="js_toggle_container(\''.$row->kategorie_kurzbz.'\');"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;<span class="'.$class.'">'.$row->bezeichnung.' </span></a>
	          		'.$locked.'
					<table class="tabcontent" id="'.$row->kategorie_kurzbz.'" style="display: none;">';
			drawKategorieMenue($dms->result);
			echo '	</table>
	          	</td>
        	</tr>';
		}
		else
		{
			echo '
			<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
	          	<td class="tdwrap"><a id="'.$row->kategorie_kurzbz.'" href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$row->kategorie_kurzbz.'" class="Item"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;<span class="'.$class.'">'.$row->bezeichnung.' </span></a>'.$delete.$locked.'</td>
        	</tr>';			
		}
		
	}
	//echo '</table>';
	//echo '</ul>';
}
?>