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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>FHComplete Document Management System</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../skin/style.css.php" type="text/css">
	<script type="text/javascript" src="../include/js/jquery.js"></script>
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
	
	tinyMCEPopup.onInit.add(FileBrowserDialog.init, FileBrowserDialog);
	
	$(document).ready(function() 
	{ 
		$('#divupload').hide();
	});
	
	function upload(id, name)
	{
		$('#divupload').show();
		
		if(typeof(id)!='undefined')
		{
			$('#dms_id').val(id);
			$('#ueberschrift').html('Neue Version von '+name);
		}
		else
		{
			$('#dms_id').val('');
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
$mimetypes = array(
	'application/pdf'=>'pdf.ico',
	'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'word2007.jpg',
	'application/vnd.openxmlformats-officedocument.presentationml.presentation'=>'x-office-presentation.png',
	'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'excel.gif',
	'application/vnd.oasis.opendocument.text'=>'openoffice0.jpg',
	'application/msword'=>'dotpic.gif',
	'application/zip'=>'zippic.jpg',

);


if(isset($_POST['fileupload']))
{
	$dms_id = $_POST['dms_id'];
	
	$filename = uniqid();
	$uploadfile = DMS_PATH.$filename;
	
	if(move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) 
	{
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


echo '<h1>Dokument Auswählen</h1>
	<table>
		<tr>
			<td valign="top" nowrap>
				<b>Kategorie:</b><br>';
//Kategorien anzeigen

$dms = new dms();
$dms->getKategorie();
foreach($dms->result as $row)
{
	if($kategorie_kurzbz=='')
		$kategorie_kurzbz=$row->kategorie_kurzbz;
	if($kategorie_kurzbz==$row->kategorie_kurzbz)
		$class='class="marked"';
	else
		$class='';
	
	echo '
		<a href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$row->kategorie_kurzbz.'" '.$class.'>';
	echo $row->bezeichnung.'</a><br>';
}
echo '
			</td>
			<td valign="top">';

//Dokumente der Ausgewaehlten Kategorie laden und Anzeigen
$dms = new dms();
$dms->getDocuments($kategorie_kurzbz);

echo '
		<table>
			<tr>';
$anzahl=0;
foreach($dms->result as $row)
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
	echo '<a href="id://'.$row->dms_id.'/Auswahl" onclick="FileBrowserDialog.mySubmit('.$row->dms_id.'); return false;" style="font-size: small" title="'.$row->beschreibung.'">';
	echo '<center>';
	if(array_key_exists($row->mimetype,$mimetypes))
		echo '<img src="../skin/images/'.$mimetypes[$row->mimetype].'" style="height: 15px">';
	else
		echo '<img src="dms.php?id='.$row->dms_id.'&amp;notimeupdate" style="max-width: 100px">';
	
	echo '<br>'.$row->name.'</a><br>';
	
	//Upload einer neuen Version
	echo '<a href="id://'.$row->dms_id.'/Upload" onclick="return upload(\''.$row->dms_id.'\',\''.$row->name.'\')" style="font-size:small">Upload</a> ';
	echo '</center></td>';
	$anzahl++;
}
echo '	
			</tr>
		</table>
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
	</div>';
?>
</body>
</html>