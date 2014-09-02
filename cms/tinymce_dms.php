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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Karl Burkhart <karl.burkhart@technikum-wien.at> and
 *          Manfred Kindl <manfred.kindl@technikum-wien.at>.
 */
require_once('../config/cis.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/dms.class.php');
require_once('../include/benutzerberechtigung.class.php');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//DE"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>FHComplete Document Management System</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../skin/superfish.css" type="text/css">
	<link rel="stylesheet" href="../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../skin/style.css.php" type="text/css">
	<link rel="stylesheet" href="../skin/tablesort.css" type="text/css"/>
	<script type="text/javascript" src="../include/js/jquery.js"></script>
	<script type="text/javascript" src="../include/js/superfish.js"></script>
	<script type="text/javascript" src="../include/tiny_mce/tiny_mce_popup.js"></script>
	<script type="text/javascript">

    function conf_del()
	{
		return confirm('Möchten Sie das File wirklich löschen?');
	}
    
	var FileBrowserDialog={
			init: function(){
			},
			mySubmit : function (id) {
				  var URL = "dms.php?id="+id;
			        var win = tinyMCEPopup.getWindowArg("window");

			        // insert information now
			        win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;

			        // are we an image browser
			        if (typeof(win.ImageDialog) != "undefined") {
			            // we are, so update image dimensions...
			            if (win.ImageDialog.getImageData)
			                win.ImageDialog.getImageData();

			            // ... and preview if necessary
			            if (win.ImageDialog.showPreviewImage)
			                win.ImageDialog.showPreviewImage(URL);
			        }

			        // close popup window
			        tinyMCEPopup.close();
			}
	};
	
	//tinyMCEPopup.onInit.add(FileBrowserDialog.init, FileBrowserDialog);
	
	$(document).ready(function() 
	{ 
		//$('#divupload').hide();
		jQuery('ul.sf-menu').superfish({speed:'fast', delay:200});
	});
	
	function upload(id, name)
	{
		$('#divupload').show();
		
		if(typeof(id)!='undefined')
		{
			$('#dms_id').val(id);
			$('#dms_id_import').val(id); 
			$('#ueberschrift').html('Neue Version von '+name);
		}
		else
		{
			$('#dms_id').val('');
			$('#dms_id_import').val('');
			$('#ueberschrift').html('Neue Datei:');
		}
		return false;
	}

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

$kategorie_kurzbz = isset($_REQUEST['kategorie_kurzbz'])?$_REQUEST['kategorie_kurzbz']:'';
$searchstring = isset($_REQUEST['searchstring'])?$_REQUEST['searchstring']:'';
$importFile = isset($_REQUEST['importFile'])?$_REQUEST['importFile']:'';
$versionId = isset($_REQUEST['versionid'])?$_REQUEST['versionid']:'';
$renameId = isset($_GET['renameid'])?$_GET['renameid']:'';
$version = isset($_GET['version'])?$_GET['version']:'';
$projekt_kurzbz = isset($_REQUEST['projekt_kurzbz'])?$_REQUEST['projekt_kurzbz']:'';
$projektphase_id = isset($_REQUEST['projektphase_id'])?$_REQUEST['projektphase_id']:'';
$openupload = isset($_GET['openupload'])?$_GET['openupload']:false;
$newVersionID = isset($_GET['newVersionID'])?$_GET['newVersionID']:false;
$suche = false; 

$mimetypes = array(
	'application/pdf'=>'pdf.ico',
	'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'word2007.jpg',
	'application/vnd.openxmlformats-officedocument.presentationml.presentation'=>'x-office-presentation.png',
	'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'excel.gif',
	'application/vnd.oasis.opendocument.text'=>'openoffice0.jpg',
	'application/msword'=>'dotpic.gif',
	'application/vnd.ms-excel'=>'excel.gif',
	'application/x-zip'=>'zippic.gif',
	'image/jpeg'=>'imgpic.gif',
	'image/gif'=>'imgpic.gif',
	'image/png'=>'imgpic.gif',
);

// Hole Datei aus Import Verzeichnis
if($importFile != '')
{
	$ext = pathinfo($importFile, PATHINFO_EXTENSION);
	$filename=uniqid(); 
	$filename.=".".$ext; 
	$dms_id = $_POST['dms_id_import'];
	
	// kopiert aus import Verzeichnis
	if(copy(IMPORT_PATH.$importFile, DMS_PATH.$filename))
	{
		$dms = new dms; 
		
		if($dms_id!='')
    	{
    		if(!$dms->load($dms_id))
    		{
    			die($dms->errormsg);
    		}
    		$dms->version=$dms->version+1;
    	}
    	else
    	{
    		$dms->version='0';
    		$dms->kategorie_kurzbz=$kategorie_kurzbz;
    	}
		
	    $dms->insertamum=date('Y-m-d H:i:s');
    	$dms->insertvon = $user;
    	$dms->mimetype= mime_content_type(IMPORT_PATH.$importFile); 
    	$dms->filename = $filename;
    	$dms->name = $importFile;
    	    	
    	if($dms->save(true))
    	{
    		echo 'File wurde erfolgreich hochgeladen. Filename:'.$filename.' ID:'.$dms->dms_id;
    		$dms_id=$dms->dms_id;
    		
    		if($projekt_kurzbz!='' || $projektphase_id!='')
    		{
    			if(!$dms->saveProjektzuordnung($dms_id, $projekt_kurzbz, $projektphase_id))
    				echo $dms->errormsg;
    		}
    	}    	
    	else
    		echo 'Fehler beim Speichern der Daten';
    	
    	if(!chgrp(DMS_PATH.$filename,'dms'))
			echo 'CHGRP failed';
		if(!chmod(DMS_PATH.$filename, 0774))
			echo 'CHMOD failed';
		exec('sudo chown wwwrun '.$filename);	
    		
    	// Lösche File aus Verzeichnis nachdem es raufgeladen wurde
    	if(!unlink(IMPORT_PATH.$importFile))
    		echo 'Fehler beim Löschen aufgetreten.'; 
	}
}
if(isset($_POST['fileupload']))
{
	$dms_id = $_POST['dms_id'];
	$beschreibung = $_POST['beschreibung'];
	$ext = pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION); 
	$filename = uniqid();
	$filename.=".".$ext; 
	$uploadfile = DMS_PATH.$filename;
	
	
	if(move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) 
	{
		if(!chgrp($uploadfile,'dms'))
			echo 'CHGRP failed';
		if(!chmod($uploadfile, 0774))
			echo 'CHMOD failed';
		exec('sudo chown wwwrun '.$uploadfile);	
		
    	$dms = new dms();
    	
    	if($dms_id!='')
    	{
    		if(!$dms->load($dms_id))
    		{
    			die($dms->errormsg);
    		}
    		$dms->version=$dms->version+1;
    	}
    	else
    	{
    		$dms->version='0';
    		$dms->kategorie_kurzbz=$kategorie_kurzbz;
    	}
    	
    	$dms->insertamum=date('Y-m-d H:i:s');
    	$dms->insertvon = $user;
    	$dms->mimetype=$_FILES['userfile']['type'];
    	$dms->filename = $filename;
    	$dms->name = $_FILES['userfile']['name'];
    	$dms->beschreibung = $beschreibung;
    	    	
    	if($dms->save(true))
    	{
    		echo '<span class="ok">File wurde erfolgreich hochgeladen.</span> <br>Filename:'.$filename.' <br>ID:'.$dms->dms_id;
    		$dms_id=$dms->dms_id;
    		
    		if($projekt_kurzbz!='' || $projektphase_id!='')
    		{
    			if(!$dms->saveProjektzuordnung($dms_id, $projekt_kurzbz, $projektphase_id))
    				echo $dms->errormsg;
    		}
    	}    	
    	else
    	{
    		echo '<span class="error">Fehler beim Speichern der Daten</span>';
    	}
	} 
	else 
	{
	    echo '<span class="error">Fehler beim Hochladen der Datei</span>';
	}
}

if(isset($_POST['action']) && $_POST['action']=='rename')
{
	$name = $_POST['dateiname'];
	$dms_id = $_POST['dms_id'];
	$version = $_POST['version'];
	$beschreibung = $_POST['beschreibung'];
	
	$dms = new dms();
	if($dms->load($dms_id, $version))
	{
		$dms->name = $name;
		$dms->beschreibung = $beschreibung;
		
		if($dms->save(false))
			echo '<span class="ok">Dateiname wurde erfolgreich geändert</span>';
		else
			echo '<span class="error">Fehler beim Ändern des Dateinamens:'.$dms->errormsg.'</span>';
	}
	else
		echo '<span class="error">Fehler beim Laden des Eintrages</span>';
}

if(isset($_REQUEST['delete']))
{
    if(!$rechte->isberechtigt('basis/dms',null, 'sui', null))
        die('Sie haben keine Berechtigung diese Seite zu sehen.');
    
    // lösche nur die Version
    if(isset($_REQUEST['version']))
    {
        $dms_id = $_REQUEST['dms_id'];
        $version = $_REQUEST['version'];

        $dms = new dms(); 
        $dms->load($dms_id, $version);

        //  DB Eintrag löschen
        if(!$dms->deleteVersion($dms_id, $version))
            echo '<span class="error">'.$dms->errormsg.'</span>';
        else
        {   
            // File im Filesystem löschen 
            if(unlink(DMS_PATH.$dms->filename))
                echo '<span class="ok">Erfolgreich gelöscht!</span>';
            else
                echo '<span class="error">Fehler beim löschen aus dem Filesystem aufgetreten!</span>';
        }
    }else
    {
        // lösche gesamten Eintrag
        $dms_id = $_REQUEST['dms_id'];

        $dms = new dms(); 
        $error = false; 
        
        $dms->getAllVersions($dms_id);
        
        // DB Einträge löschen
        if(!$dms->deleteDms($dms_id))
            echo '<span class="error">'.$dms->errormsg.'</span>';
        else
        {
            // Alle Versionen der Datei vom Filesystem löschen
            foreach($dms->result as $obj)
            {
                if(!unlink(DMS_PATH.$obj->filename))
                    $error = true; 
            }
            if($error)
                echo '<span class="error">Fehler beim löschen aus dem Filesystem aufgetreten!</span>';
            else
                echo '<span class="ok">Erfolgreich gelöscht!</span>';
        }
    }
}

if($versionId != '')
{	
	//  Übersicht der Versionen
	echo '<h1>Versionsübersicht</h1>'; 
	echo '<span style="float:right";><a href="'.$_SERVER['PHP_SELF'].'">zurück</a></span>'; 
	drawAllVersions($versionId); 
}
elseif($renameId!='')
{
	//  Übersicht der Versionen
	echo '<h1>Versionsübersicht</h1>'; 
	echo '<span style="float:right";><a href="'.$_SERVER['PHP_SELF'].'">zurück</a></span>'; 
	drawRenameForm($renameId, $version);
}
else 
{
	echo '<div align="left"><h1>Dokument Auswählen</h1></div><div align="right"><a href="admin_dms.php" target="_blank">Administration</a></div>
		<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
			<input type="text" name="searchstring" value="'.$searchstring.'">
			<input type="submit" value="Suchen">
		</form>';
	
		// Suche anzeigen
	echo'	<table cellspacing=0>
			<tr> 
				<td valign="top" nowrap style="border-right: 1px solid lightblue;border-top: 1px solid lightblue;padding-right:5px">
					<h3>Kategorie:</h3>
					';
		//Kategorien anzeigen
	$dms = new dms();
	$dms->getKategorie();
	echo '
	<table class="tabcontent">
	<tr>
		<td width="159" valign="top" class="tdwrap">
			<table class="tabcontent">
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>';
	drawKategorieMenue($dms->result);
	echo '</table></td></tr></table>';
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
		<td valign="top" style="border-top: 1px solid lightblue;">';
	//Dokumente der Ausgewaehlten Kategorie laden und Anzeigen
	$dms = new dms();
		
	if($searchstring!='')
	{
		$dms->search($searchstring);
		$suche = true; 
	}
	else
	{
		$dms->getDocuments($kategorie_kurzbz);
	}
	
	//drawFilesThumb($dms->result);
	drawFilesList($dms->result);
	
	echo '
			</td>
		</tr>
		</table>
		<br>
		<a href="#Upload" onclick="return upload()">Neue Datei hochladen</a>
		<br><br>
		<div id="divupload">
			<hr>
			<span id="ueberschrift"></span>
			<form action="'.$_SERVER['PHP_SELF'].'" method="POST" enctype="multipart/form-data">
				<input type="hidden" name="kategorie_kurzbz" id="kategorie_kurzbz" value="'.$kategorie_kurzbz.'">
				<input type="hidden" name="dms_id" id="dms_id" value="">
				<table>
				<tr>
					<td>Beschreibung</td>
					<td><textarea name="beschreibung" rows="2" cols="80" style="font-size: small;"></textarea></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="file" name="userfile"></td>
				</tr>
				</table>
				<input type="hidden" name="projekt_kurzbz" value="'.$projekt_kurzbz.'">
				<input type="hidden" name="projektphase_id" value="'.$projektphase_id.'">
				<input type="submit" name="fileupload" value="Upload">
				</form>
				<br>';
				drawFilesFromImport(); 
	echo'			
			</div>';
	if($openupload)
	{
		echo '<script>
			$(document).ready(function() 
			{ 
			';
		if($newVersionID!='')
		{
			$dms_obj = new dms();
			$dms_obj->load($newVersionID);
			echo 'upload("'.$newVersionID.'","'.$dms_obj->name.'");';
		}
		else
			echo 'upload();';
		
		echo '
			});
			</script>';
	}
}


	
/************ FUNCTIONS ********************/

/**
 * Zeigt alle Versionen des Dokumentes an
 * 
 * @param $id DokumentID die angezeigt werden soll
 */
function drawAllVersions($id)
{
	$dms = new dms(); 
	$dms->getAllVersions($id); 
	
		echo '<script>
			$(document).ready(function() 
			{ 
				$("#t3").tablesorter(
				{
					sortList: [[0,0]], headers: {6:{sorter:false}},
					widgets: ["zebra"]
				});
			});
			</script>
			<table style="width:50%" class="tablesorter" id="t3">
			<thead>
			 <tr align="center">
			 	<th>Version</th>
			 	<th>Name</th>
			 	<th>Beschreibung</th>
			 	<th>Kategorie</th>
			 	<th>Datum</th>
			 	<th>User</th>
			 </tr>
			 </thead><tbody>';
	foreach ($dms->result as $dms_help)
	{
		echo '<tr>
			  	<td style="padding: 1px; vertical-align:middle" align="center">'.$dms_help->version.'</td>
			  	<td style="padding: 1px; vertical-align:middle">'.$dms_help->name.'</td>
			  	<td style="padding: 1px; vertical-align:middle">'.$dms_help->beschreibung.'</td>
			  	<td style="padding: 1px; vertical-align:middle" align="center">'.$dms_help->kategorie_kurzbz.'</td>
			  	<td style="padding: 1px; vertical-align:middle">'.$dms_help->insertamum.'</td>
			  	<td style="padding: 1px; vertical-align:middle;">'.$dms_help->insertvon.'</td>
		        <td style="padding: 1px; vertical-align:middle;">
		        	<ul class="sf-menu">
						<li><a style="font-size:small">Erweitert</a>
							<ul>
								<li><a href="dms.php?id='.$dms_help->dms_id.'&version='.$dms_help->version.'" style="font-size:small" target="_blank">Herunterladen</a></li>
                                <li><a href="'.$_SERVER['PHP_SELF'].'?dms_id='.$dms_help->dms_id.'&version='.$dms_help->version.'&delete" style="font-size:small">Löschen</a></li>
							</ul>
						</li>
				 	</ul>
		        </td>
			  </tr>'; 
	}
	echo '</tbody></table>'; 	 		
}

/**
 * Liest die Files aus dem Importverzeichnis aus
 * 
 */
function drawFilesFromImport()
{
	global $kategorie_kurzbz, $projekt_kurzbz, $projektphase_id;

	if ($handle = opendir(IMPORT_PATH)) 
	{
		echo '	<h3>Files im Import Ordner</h3>
				<table> <form action ="'.$_SERVER['PHP_SELF'].'" method="POST" name="import" >'; 

	    while (false !== ($file = readdir($handle))) 
	    {
	    	if($file != '.' && $file != '..')
	    	{
	    		echo'
	    		<tr>
			    	<td><img src="../skin/images/blank.png" style="height: 15px">
			       		<span> '.$file.'</span>
			       	</td>
			        <td>
			        	| <a onclick="document.import.importFile.value=\''.$file.'\';document.import.submit();" style="font-size:small">Upload</a>
			        </td>
		     	</tr>';  
	    	}
	    }
	    echo'	
	    	<input type="hidden" name="dms_id_import" id="dms_id_import" value="">
			<input type="hidden" name="importFile" value="">
			<input type="hidden" name="kategorie_kurzbz" id="kategorie_kurzbz" value="'.$kategorie_kurzbz.'">
			<input type="hidden" name="projekt_kurzbz" value="'.$projekt_kurzbz.'">
			<input type="hidden" name="projektphase_id" value="'.$projektphase_id.'">
		 </form></table>';
	    closedir($handle);
	}
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
		if($kategorie_kurzbz=='')
			$kategorie_kurzbz=$row->kategorie_kurzbz;
		if($kategorie_kurzbz==$row->kategorie_kurzbz)
			$class='marked';
		else
			$class='';
		
		$dms = new dms();
		$dms->getKategorie($row->kategorie_kurzbz);
		
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
	          		<a href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$row->kategorie_kurzbz.'" class="MenuItem" onClick="js_toggle_container(\''.$row->kategorie_kurzbz.'\');"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;<span class="'.$class.'">'.$row->bezeichnung.'</span></a>
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
	          	<td class="tdwrap"><a id="'.$row->kategorie_kurzbz.'" href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$row->kategorie_kurzbz.'" class="Item"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;<span class="'.$class.'">'.$row->bezeichnung.'</span></a>'.$locked.'</td>
        	</tr>';			
		}
		
	}
	//echo '</table>';
	//echo '</ul>';
}
/**
 * Zeichnet die Files in Listenform
 * 
 * @param $rows DMS Result Object
 */
function drawFilesList($rows)
{
	global $mimetypes, $suche;
	$dms = new dms(); 
	
	if(count($rows)>0)
	{
		echo '
		<script>
		$(document).ready(function() 
		{ 
			$("#t2").tablesorter(
			{';
				if($suche == true)
					echo 'sortList: [[4,0],[1,1]], headers: {3:{sorter:false}},';
				else
					echo 'sortList: [[0,0]], headers: {2:{sorter:false}},';
					
				echo'
				widgets: ["zebra"]
			});
		});
		</script>
		';
	}
	
	echo '
			<table class="tablesorter" id="t2">
			<thead>
			<tr>
			<th>Titel</th>
			<th title="Version">V</th>';
			if($suche == true)
			{
				echo '<th>Kategorie</th>';
			}
			echo'
			<th>&nbsp;</th>
			<th>ID</th>
			<th>Beschreibung</th>
			</tr>
			</thead>
			<tbody>
		';

	foreach($rows as $row)
	{
		echo '
		<tr>
			<td style="padding: 1px;">';
		if(array_key_exists($row->mimetype,$mimetypes))
			echo '<img title="'.$row->name.'" src="../skin/images/'.$mimetypes[$row->mimetype].'" style="height: 15px">';
		else
			echo '<img title="'.$row->name.'" src="../skin/images/blank.gif" style="height: 15px">';
			
		// wenn es noch höhere Versionen zu diesem Dokument gibt, wird dieses gekennzeichnet 
		$newVersion = '';
		$newerVersionAlert='';
		if($dms->checkVersion($row->dms_id, $row->version))
		{
			$newVersion = '--';
			$newerVersionAlert = 'alert(\'Achtung!! Es gibt eine neuere Version dieses Dokuments. Es wird die aktuellste eingefügt.\');';  	
		}
			
		echo'
				<a href="id://'.$row->dms_id.'/Auswahl" onclick="'.$newerVersionAlert.' FileBrowserDialog.mySubmit('.$row->dms_id.'); return false;" style="font-size: small" title="'.$row->beschreibung.'">
				'.$newVersion.' '.$row->name.'</a>
			</td>';
		echo '<td style="padding: 1px;">';
		echo $row->version;
		echo '</td>';

		// zeige bei suche auch kategorie an
		if($suche == true)
		{
			echo '<td style="padding: 1px;">';
			echo $row->kategorie_kurzbz;
			echo '</td>';
		}
		echo'<td style="padding: 1px;">';
		
		//Upload einer neuen Version
		echo '<ul class="sf-menu">
				<li><a href="id://'.$row->dms_id.'/Erweitert" style="font-size:small">Erweitert</a>
					<ul>
						<li><a href="id://'.$row->dms_id.'/Auswahl" onclick="'.$newerVersionAlert.' FileBrowserDialog.mySubmit('.$row->dms_id.');" style="font-size:small">Auswählen</a></li>
						<li><a href="dms.php?id='.$row->dms_id.'&version='.$row->version.'" style="font-size:small" target="_blank">Herunterladen</a></li>
						<li><a href="id://'.$row->dms_id.'/Upload" onclick="return upload(\''.$row->dms_id.'\',\''.$row->name.'\')" style="font-size:small">Neue Version hochladen</a></li>
						<li><a href="'.$_SERVER['PHP_SELF'].'?versionid='.$row->dms_id.'" style="font-size:small" >Alle Versionen anzeigen</a></li>
						<li><a href="'.$_SERVER['PHP_SELF'].'?renameid='.$row->dms_id.'&version='.$row->version.'&kategorie_kurzbz='.$row->kategorie_kurzbz.'" style="font-size:small" >Datei umbenennen</a></li>
                        <li><a href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$row->kategorie_kurzbz.'&dms_id='.$row->dms_id.'&delete" onclick="return conf_del()" style="font-size:small" >Löschen</a></li>
                    </ul>
				</li>
			  </ul>';
		echo '</td>';
		echo '<td style="padding: 1px;">'.$row->dms_id.'</td>';
		echo '<td style="padding: 1px;">'.$dms->convert_html_chars($row->beschreibung).'</td>';
		echo '</tr>';
		
	}
	echo '	
			</tbody></table>';
	$suche = false;
}

/**
 * Erstellt das Formular zum Umbenennen von Dokumenten
 * @param $dms_id ID des Dokuments
 * @param $version Versionsnummer des Dokuments
 */
function drawRenameForm($dms_id, $version)
{
	global $kategorie_kurzbz;
	
	$dms = new dms();
	if($dms->load($dms_id, $version))
	{
		echo '<form action="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$kategorie_kurzbz.'" method="POST">
		<table>
		<tr>
			<td>Dateiname:</td>
			<td><input type="text" size="40" name="dateiname" value="'.$dms->convert_html_chars($dms->name).'"></td>
		</tr>
		<tr>
			<td>Beschreibung:</td>
			<td><textarea name="beschreibung" rows="2" cols="80" style="font-size: small;">'.$dms->convert_html_chars($dms->beschreibung).'</textarea></td>
		</tr>
		</table>
		<input type="hidden" name="action" value="rename">
		<input type="hidden" name="dms_id" value="'.$dms_id.'">
		<input type="hidden" name="version" value="'.$version.'">
		<input type="submit" name="submit" value="Umbenennen">
		</form>';
	}
	else
	{
		echo '<span class="error">Fehler beim Laden des Eintrags</span>';
	}
}

?>
</body>
</html>