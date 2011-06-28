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
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
require_once('../config/cis.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/dms.class.php');


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
	<script type="text/javascript" src="../include/js/jquery.js"></script>
	<script type="text/javascript" src="../include/js/superfish.js"></script>
	<script type="text/javascript" src="../include/tiny_mce/tiny_mce_popup.js"></script>
	<script type="text/javascript">
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
		$('#divupload').hide();
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
	</script>
</head>
<body>
<?php

$user = get_uid();
$kategorie_kurzbz = isset($_REQUEST['kategorie_kurzbz'])?$_REQUEST['kategorie_kurzbz']:'';
$searchstring = isset($_REQUEST['searchstring'])?$_REQUEST['searchstring']:'';
$importFile = isset($_REQUEST['importFile'])?$_REQUEST['importFile']:'';
$versionId = isset($_REQUEST['versionid'])?$_REQUEST['versionid']:'';
$suche = false; 

$mimetypes = array(
	'application/pdf'=>'pdf.ico',
	'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'word2007.jpg',
	'application/vnd.openxmlformats-officedocument.presentationml.presentation'=>'x-office-presentation.png',
	'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'excel.gif',
	'application/vnd.oasis.opendocument.text'=>'openoffice0.jpg',
	'application/msword'=>'dotpic.gif',
	'application/zip'=>'zippic.jpg',

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
    	}
		
	    $dms->insertamum=date('Y-m-d H:i:s');
    	$dms->insertvon = $user;
    	$dms->mimetype= mime_content_type(IMPORT_PATH.$importFile); 
    	$dms->filename = $filename;
    	$dms->name = $importFile;
    	$dms->kategorie_kurzbz=$kategorie_kurzbz;
    	
    	if($dms->save(true))
    	{
    		echo 'File wurde erfolgreich hochgeladen. Filename:'.$filename.' ID:'.$dms->dms_id;
    		$dms_id=$dms->dms_id;
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
    	}
    	
    	$dms->insertamum=date('Y-m-d H:i:s');
    	$dms->insertvon = $user;
    	$dms->mimetype=$_FILES['userfile']['type'];
    	$dms->filename = $filename;
    	$dms->name = $_FILES['userfile']['name'];
    	$dms->kategorie_kurzbz=$kategorie_kurzbz;
    	
    	if($dms->save(true))
    	{
    		echo 'File wurde erfolgreich hochgeladen. Filename:'.$filename.' ID:'.$dms->dms_id;
    		$dms_id=$dms->dms_id;
    	}    	
    	else
    	{
    		echo 'Fehler beim Speichern der Daten';
    	}
	} 
	else 
	{
	    echo 'Fehler beim Hochladen der Datei';
	}
}

if($versionId != '')
{	
	//  Übersicht der Versionen
	echo '<h1>Versionsübersicht</h1>'; 
	echo '<span style="float:right";><a href="'.$_SERVER['PHP_SELF'].'">zurück</a></span>'; 
	drawAllVersions($versionId); 
}
else 
{
	echo '<h1>Dokument Auswählen</h1>
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
	drawKategorieMenue($dms->result);
	
	echo '</td>
		<td valign="top" style="border-top: 1px solid lightblue; width: 100%;">';
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
				<input type="file" name="userfile">
				<input type="submit" name="fileupload" value="Upload">
				</form>
				<h3>Files im Import Ordner</h3>';
				drawFilesFromImport(); 
	echo'			
			</div>';
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
	
	echo '<table >
		 <tr align="center">
		 	<td>Version</td>
		 	<td>Name</td>
		 	<td>Kategorie</td>
		 	<td>Datum</td>
		 	<td>User</td>
		 </tr>';
	foreach ($dms->result as $dms_help)
	{
		echo '<tr>
			  	<td align="center">'.$dms_help->version.'</td>
			  	<td>'.$dms_help->name.'</td>
			  	<td align="center">'.$dms_help->kategorie_kurzbz.'</td>
			  	<td>'.$dms_help->insertamum.'</td>
			  	<td>'.$dms_help->insertvon.'</td>
		        <td>
		        	<ul class="sf-menu">
						<li><a style="font-size:small">Erweitert</a>
							<ul>
								<li><a href="dms.php?id='.$dms_help->dms_id.'&version='.$dms_help->version.'" style="font-size:small" target="_blank">Herunterladen</a></li>
							</ul>
						</li>
				 	</ul>
		        </td>
			  </tr>'; 
	}
	echo '</table>'; 	 		
}

/**
 * Liest die Files aus dem Importverzeichnis aus
 * 
 */
function drawFilesFromImport()
{
	global $kategorie_kurzbz; 
	if ($handle = opendir(IMPORT_PATH)) 
	{
		echo '<table> <form action ="'.$_SERVER['PHP_SELF'].'" method="POST" name="import" >'; 

	    while (false !== ($file = readdir($handle))) 
	    {
	    	if($file != '.' && $file != '..')
	    	{
	    		echo'
	    		<tr>
			    	<td><img src="../skin/images/blank.png" style="height: 15px">
			       		<a> '.$file.'</a>
			       	</td>
			        <td>
			        	<ul class="sf-menu">
							<li><a style="font-size:small">Erweitert</a>
								<ul>
									<li><a onclick="document.import.importFile.value=\''.$file.'\';document.import.submit();" style="font-size:small">Upload</a></li>
								</ul>
							</li>
					 	</ul>
			        </td>
		     	</tr>';  
	    	}
	    }
	    echo'	
	    	<input type="hidden" name="dms_id_import" id="dms_id_import" value="">
			<input type="hidden" name="importFile" value="">
			<input type="hidden" name="kategorie_kurzbz" id="kategorie_kurzbz" value="'.$kategorie_kurzbz.'">
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
	echo '<ul>';
	foreach($rows as $row)
	{
		if($kategorie_kurzbz=='')
			$kategorie_kurzbz=$row->kategorie_kurzbz;
		if($kategorie_kurzbz==$row->kategorie_kurzbz)
			$class='class="marked"';
		else
			$class='';
		
		echo '<li>
			<a href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$row->kategorie_kurzbz.'" '.$class.'>';
		echo $row->bezeichnung.'</a>';
		$dms = new dms();
		$dms->getKategorie($row->kategorie_kurzbz);
		if(count($dms->result)>0)
			drawKategorieMenue($dms->result);
		echo '</li>';
	}
	echo '
				</ul>';
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
	echo '
			<table>
		';

	foreach($rows as $row)
	{
		echo '
		<tr>
			<td>';
		if(array_key_exists($row->mimetype,$mimetypes))
			echo '<img src="../skin/images/'.$mimetypes[$row->mimetype].'" style="height: 15px">';
		else
			echo '<img src="../skin/images/blank.png" style="height: 15px">';
			
		// wenn es noch höhere Versionen zu diesem Dokument gibt, wird dieses gekennzeichnet 
		$newVersion = '';
		$newerVersionAlert='';
		if($dms->checkVersion($row->dms_id, $row->version))
		{
			$newVersion = '*';
			$newerVersionAlert = 'alert(\'Achtung!! Es gibt eine neuere Version dieses Dokuments. Es wird die aktuellste eingefügt.\');';  	
		}
			
		echo'
				<a href="id://'.$row->dms_id.'/Auswahl" onclick="'.$newerVersionAlert.' FileBrowserDialog.mySubmit('.$row->dms_id.'); return false;" style="font-size: small" title="'.$row->beschreibung.'">
				'.$row->name.' '.$newVersion.'</a>
			</td>';
		
		echo '<td>'; 
		// zeige bei suche auch kategorie an
		if($suche == true)
		{
			echo $row->kategorie_kurzbz;
		}
		echo'</td><td>';
		
		//Upload einer neuen Version
		echo '<ul class="sf-menu">
				<li><a href="id://'.$row->dms_id.'/Erweitert" style="font-size:small">Erweitert</a>
					<ul>
						<li><a href="id://'.$row->dms_id.'/Auswahl" onclick="'.$newerVersionAlert.' FileBrowserDialog.mySubmit('.$row->dms_id.');" style="font-size:small">Auswählen</a></li>
						<li><a href="dms.php?id='.$row->dms_id.'&version='.$row->version.'" style="font-size:small" target="_blank">Herunterladen</a></li>
						<li><a href="id://'.$row->dms_id.'/Upload" onclick="return upload(\''.$row->dms_id.'\',\''.$row->name.'\')" style="font-size:small">Neue Version hochladen</a></li>
						<li><a href="'.$_SERVER['PHP_SELF'].'?versionid='.$row->dms_id.'" style="font-size:small" >Alle Versionen anzeigen</a></li>
					</ul>
				</li>
			  </ul>';
		echo '</td>
		</tr>';
		
	}
	echo '	
			</table>';
	$suche = false;
}
/**
 * Zeichnet die Files mit Vorschau
 * 
 * @param $rows DMS Result Object
 */
function drawFilesThumb($rows)
{
	global $mimetypes;
	echo '
			<table>
				<tr>';
	$anzahl=0;
	foreach($rows as $row)
	{
		if($anzahl>2)
		{
			echo "
				</tr>
				<tr>";
			$anzahl=0;
		}
		echo '
					<td>';
		echo '<center>';
		echo '<a href="id://'.$row->dms_id.'/Auswahl" onclick="FileBrowserDialog.mySubmit('.$row->dms_id.'); return false;" style="font-size: small" title="'.$row->beschreibung.'">';
		
		if(array_key_exists($row->mimetype,$mimetypes))
			echo '<img src="../skin/images/'.$mimetypes[$row->mimetype].'" style="height: 15px">';
		else
			echo '<img src="dms.php?id='.$row->dms_id.'&amp;notimeupdate" style="max-width: 100px">';
		echo '</a><br>';
		//echo '<br>'.$row->name.'</a>';
		
		//Upload einer neuen Version
		echo '<ul class="sf-menu">
				<li><a href="id://'.$row->dms_id.'/Auswahl" onclick="FileBrowserDialog.mySubmit('.$row->dms_id.');" style="font-size:small">'.$row->name.'</a>
					<ul>
						<li><a href="id://'.$row->dms_id.'/Auswahl" onclick="FileBrowserDialog.mySubmit('.$row->dms_id.');" style="font-size:small">Auswählen</a></li>
						<li><a href="dms.php?id='.$row->dms_id.'&version='.$row->version.'" style="font-size:small" target="_blank">Herunterladen</a></li>
						<li><a href="id://'.$row->dms_id.'/Upload" onclick="return upload(\''.$row->dms_id.'\',\''.$row->name.'\')" style="font-size:small">Neue Version hochladen</a></li>
						<li><a href="id://'.$row->dms_id.'/ShowAll" onclick="return upload(\''.$row->dms_id.'\',\''.$row->name.'\')" style="font-size:small" >Alle Versionen anzeigen</a></li>
					</ul>
				</li>
			  </ul>';
		echo '</center></td>';
		$anzahl++;
	}
	echo '	
				</tr>
			</table>';
}
?>
</body>
</html>