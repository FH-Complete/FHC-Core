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
require_once('../include/dms.class.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>FHComplete Document Management System</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../skin/style.css.php" type="text/css">
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
	</script>
</head>
<body>
<?php
$kategorie_kurzbz = isset($_GET['kategorie_kurzbz'])?$_GET['kategorie_kurzbz']:'';
echo '<h1>Dokument Auswählen</h1>
	<table>
		<tr>
			<td valign="top">
				<b>Kategorie:</b><br>';
$dms = new dms();
$dms->getKategorie();
foreach($dms->result as $row)
{
	if($kategorie_kurzbz=='')
		$kategorie_kurzbz=$row->kategorie_kurzbz;
	echo '<a href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$row->kategorie_kurzbz.'">'.$row->bezeichnung.'</a><br>';
}
echo '</td><td valign="top">';
$dms = new dms();
$dms->getDocuments($kategorie_kurzbz);
$mimetypes=array('application/pdf'=>'pdf.ico',
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'word2007.jpg',
				'application/vnd.openxmlformats-officedocument.presentationml.presentation'=>'x-office-presentation.png',
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'excel.gif',
				'application/zip'=>'zippic.jpg');
foreach($dms->result as $row)
{
	echo '<div style="border: 1px solid black; text-align: center;">';
	echo '<a href="#" onclick="FileBrowserDialog.mySubmit('.$row->dms_id.')" style="font-size: small">';
	if(array_key_exists($row->mimetype,$mimetypes))
		echo '<img src="../skin/images/'.$mimetypes[$row->mimetype].'" style="max-width: 100px">';
	else
		echo '<img src="dms.php?id='.$row->dms_id.'&notimeupdate" style="max-width: 100px">';
	echo '<br>'.$row->name.'</a>';
	echo '</div>';
}
echo '</table>';
echo '<br><a href="dms_upload.php" target="_blank">Neue Datei hochladen</a>';
?>
</body>
</html>