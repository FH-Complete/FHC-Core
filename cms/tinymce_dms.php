<?php
/*
 * Copyright (C) 2011 FH Technikum Wien
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 * Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 * Karl Burkhart <karl.burkhart@technikum-wien.at> and
 * Manfred Kindl <manfred.kindl@technikum-wien.at>.
 */
require_once ('../config/cis.config.inc.php');
require_once ('../include/functions.inc.php');
require_once ('../include/dms.class.php');
require_once ('../include/benutzerberechtigung.class.php');
require_once ('../include/basis_db.class.php');
require_once ('../include/datum.class.php');
require_once ('../include/log.class.php');

$db = new basis_db();
$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if (! $rechte->isberechtigt('basis/dms', null, 's', null))
	die($rechte->errormsg);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//DE"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>FHComplete Document Management System</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../vendor/joeldbirch/superfish/dist/css/superfish.css" type="text/css">
	<link rel="stylesheet" href="../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../skin/style.css.php" type="text/css">
	<link rel="stylesheet" href="../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" type="text/css" href="../skin/jquery-ui-1.9.2.custom.min.css">
	<script type="text/javascript" src="../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../include/js/jquery.ui.datepicker.translation.js"></script>
	<script type="text/javascript" src="../vendor/jquery/sizzle/sizzle.js"></script>
	<script type="text/javascript" src="../vendor/joeldbirch/superfish/dist/js/superfish.min.js"></script>
	<script type="text/javascript" src="../include/tiny_mce/tiny_mce_popup.js"></script>
	<style type="text/css">
	.buttondesign
	{
		background-color: #87cefa;
		border: 1px solid black;
		border-radius: 5px;
	}
	.sf-menu
	{
		margin: 0;
		padding: 0;
		line-height: 1.0;
	}
	.sf-menu a
	{
		padding: .3em;
	}
	ul li
	{
		list-style: none;
		padding-top: top: 1px;
		padding-bottom: 1px;
		margin-left: 0;
	}
	</style>
	<script type="text/javascript">
	function conf_del()
	{
		return confirm('Möchten Sie das File wirklich löschen?');
	}

	var FileBrowserDialog=
	{
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

	$('document').ready(function() {
		$('.buttondesign').mouseenter(function() {
			$(this).animate({
				backgroundColor: "#bfefff"
			}, 300);
		});
		$('.buttondesign').mouseleave(function() {
			$(this).animate({
				backgroundColor: "#87cefa"
			}, 300);
		});
	});

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

	function updateBeschreibung(beschreibung)
	{
		var beschreibungstext = ""+beschreibung;
		beschreibungstext = beschreibungstext.replace(/4nführungsze1ch3n/g, "'");
		beschreibungstext = beschreibungstext.replace(/6Sl4sh/g, "\\");
		beschreibungstext = beschreibungstext.replace(/D4n7ührung/g, "\"");
		beschreibungstext = beschreibungstext.replace(/Ze1l3numxbr/g, "\r\n");
		document.getElementById("beschreibung-textarea").value = beschreibungstext;
	}
	function updateSchlagworte(schlagworte)
	{
		document.getElementById("schlagworte-textarea").value = schlagworte;
	}
	function updateCisSuche(cisSuche)
	{
		if (cisSuche == true)
			document.getElementById("cis_suche_checkbox").checked = true;
		else
			document.getElementById("cis_suche_checkbox").checked = false;
	}

	var __js_page_array = new Array();
	function js_toggle_container(conid)
	{
		if (document.getElementById)
		{
			var block = "block";
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

$kategorie_kurzbz = isset($_REQUEST['kategorie_kurzbz']) ? $_REQUEST['kategorie_kurzbz'] : '';
$searchstring = isset($_REQUEST['searchstring']) ? $_REQUEST['searchstring'] : '';
$importFile = isset($_REQUEST['importFile']) ? $_REQUEST['importFile'] : '';
$versionId = isset($_REQUEST['versionid']) ? $_REQUEST['versionid'] : '';
$renameId = isset($_GET['renameid']) ? $_GET['renameid'] : '';
$version = isset($_GET['version']) ? $_GET['version'] : '';
$projekt_kurzbz = isset($_REQUEST['projekt_kurzbz']) ? $_REQUEST['projekt_kurzbz'] : '';
$projektphase_id = isset($_REQUEST['projektphase_id']) ? $_REQUEST['projektphase_id'] : '';
$openupload = isset($_GET['openupload']) ? $_GET['openupload'] : false;
$newVersionID = isset($_GET['newVersionID']) ? $_GET['newVersionID'] : false;
$suche = false;
$chkatID = isset($_REQUEST['chkatID']) ? $_REQUEST['chkatID'] : '';
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$dpp = isset($_REQUEST['dpp']) ? $_REQUEST['dpp'] : 20;
$searching = isset($_REQUEST['searching']) ? $_REQUEST['searching'] : 'false';

$kategorie = new dms();
$kategorie->loadKategorie($kategorie_kurzbz);

$mimetypes = array(
	'application/pdf' => 'pdf_icon.png',
	'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'doc_icon.png',
	'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'ppt_icon.png',
	'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xls_icon.png',
	'application/vnd.oasis.opendocument.text' => 'openoffice0.jpg',
	'application/msword' => 'doc_icon.png',
	'application/vnd.ms-excel' => 'xls_icon.png',
	'application/x-zip' => 'zip_icon.png',
	'application/zip' => 'zip_icon.png',
	'application/mspowerpoint' => 'ppt_icon.png',
	'image/jpeg' => 'img_icon.png',
	'image/gif' => 'img_icon.png',
	'image/png' => 'img_icon.png'
);

// Hole Datei aus Import Verzeichnis
if ($importFile != '')
{
	if (! $rechte->isberechtigt('basis/dms', $kategorie->kategorie_oe_kurzbz, 'sui'))
		die($rechte->errormsg);

	$ext = pathinfo($importFile, PATHINFO_EXTENSION);
	$filename = uniqid();
	$filename .= ".".$ext;
	$dms_id = $_POST['dms_id_import'];

	// kopiert aus import Verzeichnis
	if (copy(IMPORT_PATH.$importFile, DMS_PATH.$filename))
	{
		$dms = new dms();
		if(!$dms->setPermission(DMS_PATH.$filename))
			echo $dms->errormsg;

		if ($dms_id != '')
		{
			if (! $dms->load($dms_id))
			{
				die($dms->errormsg);
			}
			$dms->version = $dms->version + 1;
		}
		else
		{
			$dms->version = '0';
			$dms->kategorie_kurzbz = $kategorie_kurzbz;
		}
		// Mimetype auslesen
		$finfo = finfo_open(FILEINFO_MIME_TYPE);

		$dms->insertamum = date('Y-m-d H:i:s');
		$dms->insertvon = $user;
		$dms->mimetype = finfo_file($finfo, IMPORT_PATH.$importFile); // Davor deprecated: mime_content_type(IMPORT_PATH.$importFile);
		$dms->filename = $filename;
		$dms->name = $importFile;

		if ($dms->save(true))
		{
			echo 'File wurde erfolgreich hochgeladen. <br>Filename:'.$filename.' <br>ID: <a href="id://'.$dms->dms_id.'/Auswahl" onclick="FileBrowserDialog.mySubmit('.$dms->dms_id.'); return false;" style="font-size: small">'.$dms->dms_id.'</a>';
			$dms_id = $dms->dms_id;

			if ($projekt_kurzbz != '' || $projektphase_id != '')
			{
				if (! $dms->saveProjektzuordnung($dms_id, $projekt_kurzbz, $projektphase_id))
					echo $dms->errormsg;
			}
		}
		else
			echo 'Fehler beim Speichern der Daten';

		// Lösche File aus Verzeichnis nachdem es raufgeladen wurde
		if (! unlink(IMPORT_PATH.$importFile))
			echo 'Fehler beim Löschen aufgetreten.';
	}
}
if (isset($_POST['fileupload']))
{
	if (! $rechte->isberechtigt('basis/dms', $kategorie->kategorie_oe_kurzbz, 'sui'))
		die($rechte->errormsg);

	$dms_id = $_POST['dms_id'];
	$beschreibung = $_POST['beschreibung'];
	$schlagworte = $_POST['schlagworte'];
	$mimetype = isset($_POST['mimetype']) ? $_POST['mimetype'] : '';
	$cis_suche = isset($_POST['cis_suche']) ? true : false;
	$ext = pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
	$filename = uniqid();
	$filename .= ".".$ext;
	$uploadfile = DMS_PATH.$filename;

	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile))
	{
		$dms = new dms();

		if(!$dms->setPermission($uploadfile))
			echo $dms->errormsg;

		if ($dms_id != '')
		{
			if (! $dms->load($dms_id))
			{
				die($dms->errormsg);
			}
			$dms->version = $dms->version + 1;
		}
		else
		{
			$dms->version = '0';
			$dms->kategorie_kurzbz = $kategorie_kurzbz;
		}
		// Mimetype auslesen
		$finfo = finfo_open(FILEINFO_MIME_TYPE);

		$dms->insertamum = date('Y-m-d H:i:s');
		$dms->insertvon = $user;
		if ($mimetype != '')
		{
			$dms->mimetype = $mimetype;
		}
		else
		{
			$dms->mimetype = finfo_file($finfo, $uploadfile);
		}
		$dms->filename = $filename;
		$dms->name = $_FILES['userfile']['name'];
		$dms->beschreibung = $beschreibung;
		$dms->schlagworte = $schlagworte;
		$dms->cis_suche = $cis_suche;

		if ($dms->save(true))
		{
			echo '<p class="ok">File wurde erfolgreich hochgeladen.</p>Filename intern: '.$filename.' <br>Dateiname: '.$dms->name;
			echo '<br>ID: <a href="id://'.$dms->dms_id.'/Auswahl" onclick="FileBrowserDialog.mySubmit('.$dms->dms_id.'); return false;" style="font-size: small" title="'.$dms->beschreibung.'">
				'.$dms->dms_id.'</a><br><br>';
			$dms_id = $dms->dms_id;

			if ($projekt_kurzbz != '' || $projektphase_id != '')
			{
				if (! $dms->saveProjektzuordnung($dms_id, $projekt_kurzbz, $projektphase_id))
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

if (isset($_POST['action']) && $_POST['action'] == 'rename')
{
	if (! $rechte->isberechtigt('basis/dms', $kategorie->kategorie_oe_kurzbz, 'su'))
		die($rechte->errormsg);

	$name = $_POST['dateiname'];
	$dms_id = $_POST['dms_id'];
	$version = $_POST['version'];
	$beschreibung = $_POST['beschreibung'];
	$schlagworte = $_POST['schlagworte'];
	$mimetype = isset($_POST['mimetype']) ? $_POST['mimetype'] : '';
	$cis_suche = isset($_POST['cis_suche']) ? true : false;

	$dms = new dms();
	if ($dms->load($dms_id, $version))
	{
		$dms->name = $name;
		$dms->beschreibung = $beschreibung;
		$dms->schlagworte = $schlagworte;
		$dms->cis_suche = $cis_suche;
		if ($mimetype != '')
		{
			$dms->mimetype = $mimetype;
		}
		else
		{
			$dms->mimetype = finfo_file($finfo, $uploadfile);
		}
		$dms->updateamum = date('Y-m-d H:i:s');
		$dms->updatevon = $user;

		if ($dms->save(false))
			echo '<span class="ok">Dateiname wurde erfolgreich geändert</span>';
		else
			echo '<span class="error">Fehler beim Ändern des Dateinamens:'.$dms->errormsg.'</span>';
	}
	else
		echo '<span class="error">Fehler beim Laden des Eintrages</span>';
}

if (isset($_REQUEST['delete']))
{
	if (! $rechte->isberechtigt('basis/dms', $kategorie->kategorie_oe_kurzbz, 'suid'))
		die($rechte->errormsg);

	// lösche nur die Version
	if (isset($_REQUEST['version']))
	{
		$dms_id = $_REQUEST['dms_id'];
		$version = $_REQUEST['version'];

		$dms = new dms();
		$dms->load($dms_id, $version);

		// DB Eintrag löschen
		if (! $dms->deleteVersion($dms_id, $version))
			echo '<span class="error">'.$dms->errormsg.'</span>';
		else
		{
			// Log schreiben
			$logdata_dms = (array) $dms;
			$logdata = var_export($logdata_dms, true);
			$log = new log();
			$log->executetime = date('Y-m-d H:i:s');
			$log->mitarbeiter_uid = $user;
			$log->beschreibung = "Löschen der DMS_ID ".$dms_id;
			$log->sql = 'LogData:'.$logdata;
			$log->sqlundo = '';
			$log->save(true);
		}
	}
	else
	{
		// lösche gesamten Eintrag
		$dms = new dms();
		$dms_id = $_REQUEST['dms_id'];

		// DB Einträge und Dokumente löschen
		if (! $dms->deleteDms($dms_id))
			echo '<span class="error">'.$dms->errormsg.'</span>';
		else
		{
			// Log schreiben
			$logdata_dms = (array) $dms;
			$logdata = var_export($logdata_dms, true);
			$log = new log();
			$log->executetime = date('Y-m-d H:i:s');
			$log->mitarbeiter_uid = $user;
			$log->beschreibung = "Löschen der DMS_ID ".$dms_id;
			$log->sql = 'LogData:'.$logdata;
			$log->sqlundo = '';
			$log->save(true);
		}
	}
}

if ($versionId != '')
{
	// Übersicht der Versionen
	echo '<h1>Versionsübersicht</h1>';
	if (isset($_REQUEST['searching']) && $_REQUEST['searching'] == 'true')
		echo '<p><a href="'.$_SERVER['PHP_SELF'].'?searching=true&searchstring='.$_REQUEST['searchstring'].'&page='.$page.'&dpp='.$dpp.'">zurück</a></p>';
	else
		echo '<p><a href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$_REQUEST['kategorie_kurzbz'].'&page='.$page.'&dpp='.$dpp.'">zurück</a></p>';
	drawAllVersions($versionId);
}
elseif ($renameId != '')
{
	// Datei umbenennen

	if (! $rechte->isberechtigt('basis/dms', $kategorie->kategorie_oe_kurzbz, 'su'))
		die($rechte->errormsg);

	echo '<h1>Datei umbennen</h1>';
	if (isset($_REQUEST['searching']) && $_REQUEST['searching'] == 'true')
		echo '<p><a href="'.$_SERVER['PHP_SELF'].'?searching=true&searchstring='.$_REQUEST['searchstring'].'&page='.$page.'&dpp='.$dpp.'">zurück</a></p>';
	else
		echo '<p><a href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$_REQUEST['kategorie_kurzbz'].'&page='.$page.'&dpp='.$dpp.'">zurück</a></p>';
	drawRenameForm($renameId, $version, $page, $dpp, $searching, $searchstring);
}

/*
 * if (isset($_REQUEST['searching']) && $_REQUEST['searching'] == 'true')
 * {
 * echo '<form action="'.$_SERVER['PHP_SELF'].'?searching=true&searchstring='.$searchstring.'&page='.$page.'&dpp='.$dpp.'" method="POST" enctype="multipart/form-data">';
 * }
 * else
 * {
 * echo '<form action="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$kategorie_kurzbz.'&page='.$page.'&dpp='.$dpp.'" method="POST" enctype="multipart/form-data">';
 * }
 */

elseif ($chkatID != '')
{
	// Kategorie aendern

	if (! $rechte->isberechtigt('basis/dms', $kategorie->kategorie_oe_kurzbz, 'su'))
		die($rechte->errormsg);

	if (isset($_POST['action']) && ($_POST['action'] == 'chkat'))
	{
		// neue Kategorie speichern
		$dms = new dms();
		$dms->load($chkatID);
		$dms->kategorie_kurzbz = $_POST['kategoriez'];
		$dms->save();
		if (isset($_REQUEST['searching']) && $_REQUEST['searching'] == 'true')
		{
			echo '<meta http-equiv="refresh" content="0; url='.$_SERVER['PHP_SELF'].'?searching=true&searchstring='.$_REQUEST['searchstring'].'&page='.$page.'&dpp='.$dpp.'" />';
		}
		else
		{
			echo '<meta http-equiv="refresh" content="0; url='.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$_REQUEST['kategorie_kurzbz'].'&page='.$page.'&dpp='.$dpp.'" />';
		}
	}
	else
	{
		// Kategorieauswahl
		$dms = new dms();
		$dms->load($chkatID);
		echo '<h1>Kategorie von '.$dms->name.' ändern</h1>';
		echo '<p><a href="'.$_SERVER['PHP_SELF'].'">zurück</a></p>';
		drawChangeKategorie($chkatID, $page, $dpp);
	}
}
else
{
	// Suche anzeigen
	echo '<div align="left"><h1>Dokument Auswählen</h1></div><div align="right"></div>
		<form action="'.$_SERVER['PHP_SELF'].'?searching=true&searchstring='.$searchstring.'" method="POST">
			<input type="text" name="searchstring" value="'.$searchstring.'" placeholder="#123 oder Text">
			<input type="submit" class="buttondesign" value="Suchen">
		</form>';

	echo '	<table cellspacing=0>
			<tr>
				<td valign="top" nowrap style="border-right: 1px solid lightblue; border-top: 1px solid lightblue; padding-right:5px">';

	// Link zu Admin-Oberfläche
	if ($rechte->isberechtigt('basis/dmsAdmin', null, 's'))
	{
		echo '<div style="padding: 5px; border-bottom: 1px solid lightblue"><a href="admin_dms.php" target="_blank">Administration</a></div>';
	}

	// Kategorien anzeigen
	$dms = new dms();
	$dms->getKategorie();
	echo '<ul>';
	drawKategorieMenue($dms->result);
	echo '</ul>';
	echo '<script>
	$(document).ready(function()
	{
		OpenTreeToKategorie("'.$kategorie_kurzbz.'");
	});

	//Klappt den Kategoriebaum auf, damit die ausgewaehlte Kategorie sichtbar ist
	function OpenTreeToKategorie(kategorie)
	{
		elem = document.getElementById(kategorie);
		if(elem.nodeName=="UL")
			elem.style.display="block";
		while(true)
		{
			if(!elem.parentNode)
				break;
			else
				elem = elem.parentNode;

			if(elem.nodeName=="UL" && elem.className=="tabcontent")
				elem.style.display="block";
		}
	}
	</script>';
	echo '</td>
		<td valign="top" style="border-top: 1px solid lightblue;">';
	// Dokumente der Ausgewaehlten Kategorie laden und Anzeigen
	$dms = new dms();

	if ($searchstring != '' && (isset($_GET['searching']) && $_GET['searching'] == true))
	{
		$count = new dms();
		$count->search($searchstring);
		$anzahl = count($count->result); // Falsches Ergebnis falls keine Berechtigung für eine Kategorie besteht
		$dms->search($searchstring, $dpp, $page);
		$suche = true;

		if ($page != 0)
		{
			echo '<span style="float:left">'.$anzahl.' Elemente gefunden</span><span  style="float:right">Seite '.$page.' von <a href="'.$_SERVER['PHP_SELF'].'?page=';
			if (is_int($anzahl / $dpp))
			{
				echo (int) ($anzahl / $dpp);
			}
			else
			{
				echo (int) (($anzahl / $dpp) + 1);
			}
			echo '&dpp='.$dpp.'&searching=true&searchstring='.$searchstring.'">';
			if (is_int($anzahl / $dpp))
			{
				echo (int) ($anzahl / $dpp).'&nbsp;</a></span>';
			}
			else
			{
				echo (int) (($anzahl / $dpp) + 1).'&nbsp;</a></span>';
			}
		}
		else
		{
			echo '<span align="center"><a href="'.$_SERVER['PHP_SELF'].'?page=1&dpp='.$dpp.'&searching=true&searchstring='.$searchstring.'">Seite 1</a></span>';
		}

		drawFilesList($dms->result);

		echo '<form action="'.$_SERVER['PHP_SELF'].'?page=';
		if ($page - 100 < 1)
		{
			echo '1';
		}
		else
		{
			echo ($page - 100);
		}
		if (isset($_GET['dpp']))
		{
			echo '&dpp='.$_GET['dpp'];
		}
		echo '&searching=true&searchstring='.$searchstring.'" method="POST" style="float:left"><input type="submit" class="buttondesign" name="100zurück" value="100 zurück" style="margin-left:5px;"/><input type="hidden" name="searchstring" id="searchstring" value="'.$searchstring.'" /></form>';
		echo '<form action="'.$_SERVER['PHP_SELF'].'?page=';
		if ($page - 10 < 1)
		{
			echo '1';
		}
		else
		{
			echo ($page - 10);
		}
		if (isset($_GET['dpp']))
		{
			echo '&dpp='.$_GET['dpp'];
		}
		echo '&searching=true&searchstring='.$searchstring.'" method="POST" style="float:left"><input type="submit" class="buttondesign" name="10zurück" value="10 zurück" style="margin-left:2px;"/><input type="hidden" name="searchstring" id="searchstring" value="'.$searchstring.'" /></form>';
		echo '<form action="'.$_SERVER['PHP_SELF'].'?page=';
		if ($page - 1 < 1)
		{
			echo '1';
		}
		else
		{
			echo ($page - 1);
		}
		if (isset($_GET['dpp']))
		{
			echo '&dpp='.$_GET['dpp'];
		}
		echo '&searching=true&searchstring='.$searchstring.'" method="POST" style="float:left"><input type="submit" class="buttondesign" name="zurück" value="zurück" style="margin-left:2px;"/><input type="hidden" name="searchstring" id="searchstring" value="'.$searchstring.'" /></form>';
		echo '<form action="'.$_SERVER['PHP_SELF'].'?page=0&searching=true" method="POST" style="float:left"><input type=submit class="buttondesign" name="showAll" value="Alle anzeigen" style="margin-left:2px"/><input type="hidden" name="searchstring" id="searchstring" value="'.$searchstring.'" /></form>';
		echo '<form action="'.$_SERVER['PHP_SELF'].'?page=';
		if ($page + 1 < 1)
		{
			echo '1';
		}
		else
		{
			echo ($page + 1);
		}
		if (isset($_GET['dpp']))
		{
			echo '&dpp='.$_GET['dpp'];
		}
		echo '&searching=true&searchstring='.$searchstring.'" method="POST" style="float:left"><input type="submit" class="buttondesign" name="weiter" value="weiter" style="margin-left:2px"/><input type="hidden" name="searchstring" id="searchstring" value="'.$searchstring.'" /></form>';
		echo '<form action="'.$_SERVER['PHP_SELF'].'?page=';
		if ($page + 10 < 1)
		{
			echo '1';
		}
		else
		{
			echo ($page + 10);
		}
		if (isset($_GET['dpp']))
		{
			echo '&dpp='.$_GET['dpp'];
		}
		echo '&searching=true&searchstring='.$searchstring.'" method="POST" style="float:left"><input type="submit" class="buttondesign" name="10weiter" value="10 weiter" style="margin-left:2px"/><input type="hidden" name="searchstring" id="searchstring" value="'.$searchstring.'" /></form>';
		echo '<form action="'.$_SERVER['PHP_SELF'].'?page=';
		if ($page + 100 < 1)
		{
			echo '1';
		}
		else
		{
			echo ($page + 100);
		}
		if (isset($_GET['dpp']))
		{
			echo '&dpp='.$_GET['dpp'];
		}
		echo '&searching=true&searchstring='.$searchstring.'" method="POST" style="float:left"><input type="submit" class="buttondesign" name="100weiter" value="100 weiter" style="margin-left:2px"/><input type="hidden" name="searchstring" id="searchstring" value="'.$searchstring.'" /></form>';
		echo '<form action="'.$_SERVER['PHP_SELF'].'?searching=true&searchstring='.$searchstring.'&page='.$page.' method="POST" style="float:right">
			<input type="hidden" name="page" id="page" value="';
		if ($page == 0 || $page == '')
		{
			echo '1';
		}
		else
		{
			echo $page;
		}
		echo '">
			<input type="hidden" name="searchstring" id="searchstring" value="'.$searchstring.'" />
			<input type="hidden" name="searching" id="searchstring" value="'.$searching.'" />
		<select name="dpp" onchange="this.form.submit();" style="margin-left:20px;">';
		if (isset($_GET['dpp']))
		{
			for ($i = 10; $i <= 100; $i = $i + 10)
			{
				if ($_GET['dpp'] == $i)
				{
					echo '<option selected>'.$i.'</option>';
				}
				else
				{
					echo '<option>'.$i.'</option>';
				}
			}
			switch ($_GET['dpp'])
			{
				case 150:
					echo '<option selected>150</option><option>200</option><option>500</option><option>1000</option><option>2000</option>';
					break;
				case 200:
					echo '<option>150</option><option selected>200</option><option>500</option><option>1000</option><option>2000</option>';
					break;
				case 500:
					echo '<option>150</option><option>200</option><option selected>500</option><option>1000</option><option>2000</option>';
					break;
				case 1000:
					echo '<option>150</option><option>200</option><option>500</option><option selected>1000</option><option>2000</option>';
					break;
				case 2000:
					echo '<option>150</option><option>200</option><option>500</option><option>1000</option><option selected>2000</option>';
					break;
				default:
					echo '<option>150</option><option>200</option><option>500</option><option>1000</option><option>2000</option>';
					break;
			}
		}
		else
		{
			echo '		<option>10</option><option selected>20</option><option>30</option>
						<option>40</option><option>50</option><option>60</option>
						<option>70</option><option>80</option><option>90</option>
						<option>100</option><option>150</option><option>200</option>
						<option>500</option><option>1000</option><option>2000</option>';
		}
		echo '
			</select> Elemente pro Seite&nbsp;
		</form>';
	}
	else
	{
		// Wenn eine Berechtigung auf der Kategorie liegt prüfen, ob der User die notwendigen Rechte hat
		if ($kategorie->berechtigung_kurzbz == '' || $rechte->isBerechtigt($kategorie->berechtigung_kurzbz))
		{
			if ($kategorie->kategorie_oe_kurzbz == '' || $rechte->isberechtigt('basis/dms', $kategorie->kategorie_oe_kurzbz, 's'))
			{
				$count = new dms();
				$anzahl = $count->countDocumentsKategorie($kategorie_kurzbz);
				$dms->getDocuments($kategorie_kurzbz, $dpp, $page);

				if ($page != 0)
				{
					echo '<span style="float:left">'.$anzahl.' Elemente gefunden</span><span style="float:right">Seite '.$page.' von <a href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$kategorie_kurzbz.'&page=';
					if (is_int($anzahl / $dpp))
					{
						echo (int) ($anzahl / $dpp);
					}
					else
					{
						echo (int) (($anzahl / $dpp) + 1);
					}
					echo '&dpp='.$dpp.'">';
					if (is_int($anzahl / $dpp))
					{
						echo (int) ($anzahl / $dpp).'&nbsp;</a></span>';
					}
					else
					{
						echo (int) (($anzahl / $dpp) + 1).'&nbsp;</a></span>';
					}
				}
				else
				{
					echo '<span align="center"><a href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$kategorie_kurzbz.'&page=1&dpp='.$dpp.'">Seite 1</a></span>';
				}

				drawFilesList($dms->result);
				echo '<form action="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$kategorie_kurzbz.'&page=';
				if ($page - 100 < 1)
				{
					echo '1';
				}
				else
				{
					echo ($page - 100);
				}
				if (isset($_GET['dpp']))
				{
					echo '&dpp='.$_GET['dpp'];
				}
				echo '" method="POST" style="float:left"><input type="submit" class="buttondesign" name="100zurück" value="100 zurück" style="margin-left:5px;"/></form>';
				echo '<form action="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$kategorie_kurzbz.'&page=';
				if ($page - 10 < 1)
				{
					echo '1';
				}
				else
				{
					echo ($page - 10);
				}
				if (isset($_GET['dpp']))
				{
					echo '&dpp='.$_GET['dpp'];
				}
				echo '" method="POST" style="float:left"><input type="submit" class="buttondesign" name="10zurück" value="10 zurück" style="margin-left:2px;"/></form>';
				echo '<form action="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$kategorie_kurzbz.'&page=';
				if ($page - 1 < 1)
				{
					echo '1';
				}
				else
				{
					echo ($page - 1);
				}
				if (isset($_GET['dpp']))
				{
					echo '&dpp='.$_GET['dpp'];
				}
				echo '" method="POST" style="float:left"><input type="submit" class="buttondesign" name="zurück" value="zurück" style="margin-left:2px;"/></form>';
				echo '<form action="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$kategorie_kurzbz.'&page=0" method="POST" style="float:left"><input type=submit class="buttondesign" name="showAll" value="Alle anzeigen" style="margin-left:2px"/></form>';
				echo '<form action="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$kategorie_kurzbz.'&page=';
				if ($page + 1 < 1)
				{
					echo '1';
				}
				else
				{
					echo ($page + 1);
				}
				if (isset($_GET['dpp']))
				{
					echo '&dpp='.$_GET['dpp'];
				}
				echo '" method="POST" style="float:left"><input type="submit" class="buttondesign" name="weiter" value="weiter" style="margin-left:2px"/></form>';
				echo '<form action="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$kategorie_kurzbz.'&page=';
				if ($page + 10 < 1)
				{
					echo '1';
				}
				else
				{
					echo ($page + 10);
				}
				if (isset($_GET['dpp']))
				{
					echo '&dpp='.$_GET['dpp'];
				}
				echo '" method="POST" style="float:left"><input type="submit" class="buttondesign" name="10weiter" value="10 weiter" style="margin-left:2px"/></form>';
				echo '<form action="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$kategorie_kurzbz.'&page=';
				if ($page + 100 < 1)
				{
					echo '1';
				}
				else
				{
					echo ($page + 100);
				}
				if (isset($_GET['dpp']))
				{
					echo '&dpp='.$_GET['dpp'];
				}
				echo '" method="POST" style="float:left"><input type="submit" class="buttondesign" name="100weiter" value="100 weiter" style="margin-left:2px"/></form>';
				echo '<form action="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$kategorie_kurzbz.'&page='.$page.' method="POST" style="float:right">
					<input type="hidden" name="kategorie_kurzbz" id="kategorie_kurzbz" value="'.$kategorie_kurzbz.'" /><input type="hidden" name="page" id="page" value="';
				if ($page == 0 || $page == '')
				{
					echo '1';
				}
				else
				{
					echo $page;
				}
				echo '">
					<select name="dpp" onchange="this.form.submit();" style="margin-left:20px;">';
				if (isset($_GET['dpp']))
				{
					for ($i = 10; $i <= 100; $i = $i + 10)
					{
						if ($_GET['dpp'] == $i)
						{
							echo '<option selected>'.$i.'</option>';
						}
						else
						{
							echo '<option>'.$i.'</option>';
						}
					}
					switch ($_GET['dpp'])
					{
						case 150:
							echo '<option selected>150</option><option>200</option><option>500</option><option>1000</option><option>2000</option>';
							break;
						case 200:
							echo '<option>150</option><option selected>200</option><option>500</option><option>1000</option><option>2000</option>';
							break;
						case 500:
							echo '<option>150</option><option>200</option><option selected>500</option><option>1000</option><option>2000</option>';
							break;
						case 1000:
							echo '<option>150</option><option>200</option><option>500</option><option selected>1000</option><option>2000</option>';
							break;
						case 2000:
							echo '<option>150</option><option>200</option><option>500</option><option>1000</option><option selected>2000</option>';
							break;
						default:
							echo '<option>150</option><option>200</option><option>500</option><option>1000</option><option>2000</option>';
							break;
					}
				}
				else
				{
					echo '	<option>10</option><option selected>20</option><option>30</option>
							<option>40</option><option>50</option><option>60</option>
							<option>70</option><option>80</option><option>90</option>
							<option>100</option><option>150</option><option>200</option>
							<option>500</option><option>1000</option><option>2000</option>';
				}
				echo '
					</select> Elemente pro Seite&nbsp;
				</form>';
			}
			else
				echo '<span style="float:left">Sie haben keine Berechtigung für diese Kategorie</span>';
		}
		else
			echo '<span style="float:left">Sie haben keine Berechtigung für diese Kategorie</span>';
	}
	// drawFilesThumb($dms->result);

	echo '
			</td>
		</tr>
		</table>';

	if ($rechte->isberechtigt('basis/dms', $kategorie->kategorie_oe_kurzbz, 'sui'))
	{
		echo '
		<br>
		<a href="#Upload" onclick="return upload()">Neue Datei hochladen</a>
		<br>

		<br><br>
		<div id="divupload">
			<hr>
			<span id="ueberschrift"></span>';
		if (isset($_REQUEST['searching']) && $_REQUEST['searching'] == 'true')
		{
			echo '<form action="'.$_SERVER['PHP_SELF'].'?searching=true&searchstring='.$searchstring.'&page='.$page.'&dpp='.$dpp.'" method="POST" enctype="multipart/form-data">';
		}
		else
		{
			echo '<form action="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$kategorie_kurzbz.'&page='.$page.'&dpp='.$dpp.'" method="POST" enctype="multipart/form-data">';
		}
		echo '
				<input type="hidden" name="kategorie_kurzbz" id="kategorie_kurzbz" value="'.$kategorie_kurzbz.'">
				<input type="hidden" name="dms_id" id="dms_id" value="">
				<table>
				<tr>
					<td>Beschreibung</td>
					<td><textarea name="beschreibung" id="beschreibung-textarea" rows="1" cols="80" style="font-size: small;"></textarea></td>
				</tr>
				<tr>
					<td>Schlagworte<br/>(Semikolon getrennt)</td>
					<td><textarea name="schlagworte" id="schlagworte-textarea" rows="2" cols="80" style="font-size: small;"></textarea></td>
				</tr>
				<tr>
					<td>Mimetype</td>
					<td><input type="text" name="mimetype" id="mimetype-input" size="50" maxlength="256" style="font-size: small;" /></td>
				</tr>
				<tr>
					<td>CIS-Suche</td>
					<td><input type="checkbox" id="cis_suche_checkbox" name="cis_suche"></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="file" name="userfile"></td>
				</tr>
				</table>
				<input type="hidden" name="projekt_kurzbz" value="'.$projekt_kurzbz.'">
				<input type="hidden" name="projektphase_id" value="'.$projektphase_id.'">
				<input type="submit" class="buttondesign" name="fileupload" value="Upload">
				</form>
				<br>';
		$files = scandir(IMPORT_PATH);
		$files_count = count($files) - 2; // Minus zwei wegen "." und ".."
		if ($files_count > 0 && $rechte->isberechtigt('basis/dms', $kategorie->kategorie_oe_kurzbz, 'sui'))
			drawFilesFromImport();
		echo '</div>';
	}
	if ($openupload)
	{
		echo '<script>
			$(document).ready(function()
			{
			';
		if ($newVersionID != '')
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

/**
 * ********** FUNCTIONS *******************
 */

/**
 * Zeigt alle Versionen des Dokumentes an
 *
 * @param integer $id Dokument_ID die angezeigt werden soll
 */
function drawAllVersions($id)
{
	global $rechte, $kategorie;
	$dms = new dms();
	$dms->getAllVersions($id);

	echo '<script>
			$(document).ready(function()
			{
				$("#t3").tablesorter(
				{
					sortList: [[0,1]], headers: {6:{sorter:false}},
					widgets: ["zebra"],
				});
			});
			</script>
			<table style="width:70%" class="tablesorter" id="t3">
			<thead>
			<tr align="center">
				<th>Version</th>
				<th>Name</th>
				<th>Beschreibung</th>
				<th>Schlagworte</th>
				<th>CIS-Suche</th>
				<th>Kategorie</th>
				<th>Filename intern</th>
				<th>Mimetype</th>
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
				<td style="padding: 1px; vertical-align:middle">'.$dms_help->schlagworte.'</td>
				<td style="padding: 1px; vertical-align:middle">'.($dms_help->cis_suche == 'true'?'Ja':'Nein').'</td>
				<td style="padding: 1px; vertical-align:middle" align="center">'.$dms_help->kategorie_kurzbz.'</td>
				<td style="padding: 1px; vertical-align:middle" align="center">'.$dms_help->filename.'</td>
				<td style="padding: 1px; vertical-align:middle" align="center">'.$dms_help->mimetype.'</td>
				<td style="padding: 1px; vertical-align:middle">'.$dms_help->insertamum.'</td>
				<td style="padding: 1px; vertical-align:middle;">'.$dms_help->insertvon.'</td>
				<td style="padding: 1px; vertical-align:middle;">
				<ul class="sf-menu">
						<li><a style="font-size:small">Erweitert</a>
							<ul>
								<li><a href="dms.php?id='.$dms_help->dms_id.'&version='.$dms_help->version.'" style="font-size:small" target="_blank">Herunterladen</a></li>';
		if ($rechte->isberechtigt('basis/dms', $kategorie->kategorie_oe_kurzbz, 'su'))
			echo '<li><a href="'.$_SERVER['PHP_SELF'].'?renameid='.$dms_help->dms_id.'&kategorie_kurzbz='.$dms_help->kategorie_kurzbz.'&dms_id='.$dms_help->dms_id.'&version='.$dms_help->version.'" style="font-size:small">Datei umbenennen</a></li>';
		if ($rechte->isberechtigt('basis/dms', $kategorie->kategorie_oe_kurzbz, 'suid'))
			echo '<li><a href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$dms_help->kategorie_kurzbz.'&versionid='.$dms_help->dms_id.'&dms_id='.$dms_help->dms_id.'&version='.$dms_help->version.'&delete" style="font-size:small">Löschen</a></li>';

		echo '</ul>
						</li>
					</ul>
				</td>
			</tr>';
	}
	echo '</tbody></table>';
}

/**
 * Liest die Files aus dem Importverzeichnis aus
 */
function drawFilesFromImport()
{
	global $kategorie_kurzbz, $projekt_kurzbz, $projektphase_id;

	if ($handle = opendir(IMPORT_PATH))
	{
		echo '<script>
		$(document).ready(function()
		{
			$("#t3").tablesorter(
			{
				sortList: [[0,0]], headers: {1:{sorter:false}},
				widgets: ["zebra"]
			});
		});
		</script>';
		echo '	<h3>Files im Import Ordner</h3>
				<table class="tablesorter" id="t3" style="width: auto"> <form action ="'.$_SERVER['PHP_SELF'].'" method="POST" name="import" >
    			<thead><th>File</th><th></th></thead><tbody>';

		while (false !== ($file = readdir($handle)))
		{
			if ($file != '.' && $file != '..')
			{
				echo '
				<tr>
					<td>
						<span> '.$file.'</span>
					</td>
					<td>
						<a onclick="window.location=\'#divupload\'; document.import.importFile.value=\''.$file.'\';document.import.submit();"  style="font-size:small">Upload</a>
					</td>
				</tr>';
			}
		}
		echo '
			<input type="hidden" name="dms_id_import" id="dms_id_import" value="">
			<input type="hidden" name="importFile" value="">
			<input type="hidden" name="kategorie_kurzbz" id="kategorie_kurzbz" value="'.$kategorie_kurzbz.'">
			<input type="hidden" name="projekt_kurzbz" value="'.$projekt_kurzbz.'">
			<input type="hidden" name="projektphase_id" value="'.$projektphase_id.'">
		 </form></tbody></table>';
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
	global $rechte;

	$kategorie_berechtigt = false;

	// echo '<ul>';
	foreach ($rows as $row)
	{
		// Wenn eine Berechtigung auf der Kategorie liegt prüfen, ob der User die notwendigen Rechte hat
		if ($row->berechtigung_kurzbz != '' && !$rechte->isberechtigt($row->berechtigung_kurzbz))
			continue;

		$dms = new dms();
		$dms->getKategorie($row->kategorie_kurzbz);

		$kategorie = new dms();
		$kategorie->loadKategorie($row->kategorie_kurzbz);

		// Wenn eine oe_kurzbz auf der Kategorie liegt prüfen, ob der User die Kategorie sehen darf
		if ($kategorie->kategorie_oe_kurzbz == '' || $rechte->isberechtigt('basis/dms', $kategorie->kategorie_oe_kurzbz, 's'))
		{
			// Wenn eine oe_kurzbz auf der Kategorie liegt prüfen, ob der User die Kategorie sehen darf
			if ($dms->kategorie_oe_kurzbz == '' || !$rechte->isberechtigt('basis/dms', $dms->kategorie_oe_kurzbz, 's'))
				$kategorie_berechtigt = true;

			if ($kategorie_kurzbz == '')
				$kategorie_kurzbz = $row->kategorie_kurzbz;
			if ($kategorie_kurzbz == $row->kategorie_kurzbz)
				$class = 'marked';
			else
				$class = '';

			// Suchen, ob eine Sperre fuer diese Kategorie vorhanden ist
			$groups = $dms->getLockGroups($row->kategorie_kurzbz);
			$locked = '';
			if (count($groups) > 0)
			{
				$locked = '<img src="../skin/images/login.gif" height="12px" title="Zugriff nur für Mitglieder folgender Gruppen:';
				foreach ($groups as $group)
					$locked .= " $group ";
				$locked .= '"/>';
			}
			if (count($dms->result) > 0)
			{
				echo '
				<li>
						<a href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$row->kategorie_kurzbz.'" class="MenuItem" onClick="js_toggle_container(\''.$row->kategorie_kurzbz.'\');">&nbsp;<span class="'.$class.'">'.$row->bezeichnung.'</span></a>
						'.$locked.'
						<ul class="tabcontent" id="'.$row->kategorie_kurzbz.'" style="display: none;">';
				drawKategorieMenue($dms->result);
				echo '	</ul></li>';
			}
			else
			{
				echo '
				<li><a id="'.$row->kategorie_kurzbz.'" href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$row->kategorie_kurzbz.'" class="Item">&nbsp;<span class="'.$class.'">'.$row->bezeichnung.'</span></a>'.$locked.'
	 			</li>';
			}
		}
		else
		{
			if (count($dms->result) > 0)
				drawKategorieMenue($dms->result);
		}
	}
	// echo '</table>';
	// echo '</ul>';
}
/**
 * Zeichnet die Files in Listenform
 *
 * @param $rows DMS Result Object
 */
function drawFilesList($rows)
{
	global $mimetypes, $suche, $rechte;
	$dms = new dms();

	if (count($rows) > 0)
	{
		echo '
		<script>
		$(document).ready(function()
		{
			$("#t2").tablesorter(
			{';
		if ($suche == true)
			echo 'sortList: [[4,0],[1,1]], headers: {3:{sorter:false}},';
		else
			echo 'sortList: [[0,0]], headers: {2:{sorter:false}},';

		echo '
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
	if ($suche == true)
	{
		echo '<th>Kategorie</th>';
	}
	echo '
			<th>&nbsp;</th>
			<th>ID</th>
			<th>Beschreibung</th>
			<th>Schlagworte</th>
			<th>CIS-Suche</th>
			</tr>
			</thead>
			<tbody>
		';
	$i = 0;
	foreach ($rows as $row)
	{
		// Wenn eine Berechtigung auf der Kategorie liegt prüfen, ob der User die notwendigen Rechte hat
		if ($row->berechtigung_kurzbz != '' && !$rechte->isberechtigt($row->berechtigung_kurzbz))
			continue;
		else
			$i++;

		echo '
		<tr>
			<td style="padding: 1px;">';
		if (array_key_exists($row->mimetype, $mimetypes))
			echo '<img title="'.$row->name.'" src="../skin/images/'.$mimetypes[$row->mimetype].'" style="height: 15px">';
		else
			echo '<img title="'.$row->name.'" src="../skin/images/blank.gif" style="height: 15px">';

		// wenn es noch höhere Versionen zu diesem Dokument gibt, wird dieses gekennzeichnet
		$newVersion = '';
		$newerVersionAlert = '';
		if ($dms->checkVersion($row->dms_id, $row->version))
		{
			$newVersion = '--';
			$newerVersionAlert = 'alert(\'Achtung!! Es gibt eine neuere Version dieses Dokuments. Es wird die aktuellste eingefügt.\');';
		}

		echo '
				<a href="id://'.$row->dms_id.'/Auswahl" onclick="'.$newerVersionAlert.' FileBrowserDialog.mySubmit('.$row->dms_id.'); return false;" style="font-size: small" title="'.$row->beschreibung.'">
				'.$newVersion.' '.$row->name.'</a>
			</td>';
		$datum = new datum();

		echo '<td style="padding: 1px;" title="'.$datum->formatDatum($row->insertamum, 'd.m.Y H:m').' von '.$row->insertvon.'">';
		echo $row->version;
		echo '</td>';

		$kategorie = new dms();
		$kategorie->loadKategorie($row->kategorie_kurzbz);

		// zeige bei suche auch kategorie an
		if ($suche == true)
		{
			echo '<td style="padding: 1px;">';
			echo '<a href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$row->kategorie_kurzbz.'">'.$kategorie->bezeichnung.'</a>';
			echo '</td>';
		}
		echo '<td style="padding: 1px;">';

		// Upload einer neuen Version
		echo '<ul class="sf-menu">
				<li><a href="#" style="font-size:small">Erweitert</a>
					<ul>
						<li><a href="id://'.$row->dms_id.'/Auswahl" onclick="'.$newerVersionAlert.' FileBrowserDialog.mySubmit('.$row->dms_id.');" style="font-size:small">Auswählen</a></li>
						<li><a href="dms.php?id='.$row->dms_id.'&version='.$row->version.'" style="font-size:small" target="_blank">Herunterladen</a></li>';
		if ($rechte->isberechtigt('basis/dms', $kategorie->kategorie_oe_kurzbz, 'sui'))
		{
			echo '	<li><a href="id://'.$row->dms_id.'/Upload" onclick="window.location=\'#divupload\'; updateBeschreibung(\'';
			$beschreibungstext = $row->beschreibung;
			$beschreibungstext = str_replace("'", "4nführungsze1ch3n", $beschreibungstext);
			$beschreibungstext = str_replace('"', "D4n7ührung", $beschreibungstext);
			$beschreibungstext = str_replace("\\", "6Sl4sh", $beschreibungstext);
			$beschreibungstext = str_replace("\r\n", "Ze1l3numxbr", $beschreibungstext);
			echo $beschreibungstext.'\'); 
			updateSchlagworte(\''.$row->schlagworte.'\');
			updateCisSuche(\''.$row->cis_suche.'\');
			return upload(\''.$row->dms_id.'\',\''.$row->name.'\');" style="font-size:small">Neue Version hochladen</a></li>';
		}
		if (isset($_REQUEST['searching']) && $_REQUEST['searching'] == 'true')
		{
			echo '<li><a href="'.$_SERVER['PHP_SELF'].'?versionid='.$row->dms_id.'&searching=true&';
			if (isset($_REQUEST['searchstring']))
				echo 'searchstring='.$_REQUEST['searchstring'].'&page=';
			if (isset($_REQUEST['page']))
			{
				echo $_REQUEST['page'];
			}
			else
			{
				echo '1';
			}
			echo '&dpp=';
			if (isset($_REQUEST['dpp']))
			{
				echo $_REQUEST['dpp'];
			}
			else
			{
				echo '20';
			}
			echo '" style="font-size:small" >Alle Versionen anzeigen</a></li>';
			if ($rechte->isberechtigt('basis/dms', $kategorie->kategorie_oe_kurzbz, 'su'))
			{
				echo '<li><a href="'.$_SERVER['PHP_SELF'].'?chkatID='.$row->dms_id.'&page=';
				if (isset($_REQUEST['page']))
				{
					echo $_REQUEST['page'];
				}
				else
				{
					echo '1';
				}
				echo '&dpp=';
				if (isset($_REQUEST['dpp']))
				{
					echo $_REQUEST['dpp'];
				}
				else
				{
					echo '20';
				}
				echo '&searching=true&searchstring='.$_REQUEST['searchstring'].'" style="font-size:small" >Kategorie ändern</a></li>';
				echo '<li><a href="'.$_SERVER['PHP_SELF'].'?renameid='.$row->dms_id.'&version='.$row->version.'&searching=true&';
				if (isset($_REQUEST['searchstring']))
					echo 'searchstring='.$_REQUEST['searchstring'].'&page=';
				if (isset($_REQUEST['page']))
				{
					echo $_REQUEST['page'];
				}
				else
				{
					echo '1';
				}
				echo '&dpp=';
				if (isset($_REQUEST['dpp']))
				{
					echo $_REQUEST['dpp'];
				}
				else
				{
					echo '20';
				}
				echo '" style="font-size:small" >Datei umbenennen</a></li>';
			}
			if ($rechte->isberechtigt('basis/dms', $kategorie->kategorie_oe_kurzbz, 'suid'))
			{
				echo '<li><a href="'.$_SERVER['PHP_SELF'].'?searching=true';
				if (isset($_REQUEST['searchstring']))
					echo '&searchstring='.$_REQUEST['searchstring'];

				echo '&dms_id='.$row->dms_id.'&delete" onclick="return conf_del()" style="font-size:small" >Löschen</a></li>';
			}
		}
		else
		{
			echo '<li><a href="'.$_SERVER['PHP_SELF'].'?versionid='.$row->dms_id.'&version='.$row->version.'&kategorie_kurzbz='.$row->kategorie_kurzbz.'&page=';
			if (isset($_REQUEST['page']))
			{
				echo $_REQUEST['page'];
			}
			else
			{
				echo '1';
			}
			echo '&dpp=';
			if (isset($_REQUEST['dpp']))
			{
				echo $_REQUEST['dpp'];
			}
			else
			{
				echo '20';
			}
			echo '" style="font-size:small" >Alle Versionen anzeigen</a></li>';
			if ($rechte->isberechtigt('basis/dms', $kategorie->kategorie_oe_kurzbz, 'su'))
			{
				echo '<li><a href="'.$_SERVER['PHP_SELF'].'?chkatID='.$row->dms_id.'&page=';
				if (isset($_REQUEST['page']))
				{
					echo $_REQUEST['page'];
				}
				else
				{
					echo '1';
				}
				echo '&dpp=';
				if (isset($_REQUEST['dpp']))
				{
					echo $_REQUEST['dpp'];
				}
				else
				{
					echo '20';
				}
				echo '" style="font-size:small" >Kategorie ändern</a></li>';
				echo '<li><a href="'.$_SERVER['PHP_SELF'].'?renameid='.$row->dms_id.'&version='.$row->version.'&kategorie_kurzbz='.$row->kategorie_kurzbz.'&page=';
				if (isset($_REQUEST['page']))
				{
					echo $_REQUEST['page'];
				}
				else
				{
					echo '1';
				}
				echo '&dpp=';
				if (isset($_REQUEST['dpp']))
				{
					echo $_REQUEST['dpp'];
				}
				else
				{
					echo '20';
				}
				echo '" style="font-size:small" >Datei umbenennen</a></li>';
			}
			if ($rechte->isberechtigt('basis/dms', $kategorie->kategorie_oe_kurzbz, 'suid'))
				echo '<li><a href="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$row->kategorie_kurzbz.'&dms_id='.$row->dms_id.'&delete" onclick="return conf_del()" style="font-size:small" >Löschen</a></li>';
		}
		echo '
					</ul>
				</li>
			  </ul>';
		echo '</td>';
		echo '<td style="padding: 1px;">'.$row->dms_id.'</td>';
		echo '<td style="padding: 1px;">'.$dms->convert_html_chars($row->beschreibung).'</td>';
		echo '<td style="padding: 1px;">'.$dms->convert_html_chars($row->schlagworte).'</td>';
		echo '<td style="padding: 1px; text-align: center">'.($row->cis_suche === true ? '<img title="true" src="../skin/images/check_black.png" style="height: 15px">':'&nbsp;').'</td>';
		echo '</tr>';
	}
	if ($i > 0)
		echo '</tbody></table>';
	else
		echo '<tr><td colspan="5">Für keines der gefundenen Elemente besteht eine Berechtigung</td></tr></tbody></table>';

	$suche = false;
}

/**
 * Erstellt das Formular zum Umbenennen von Dokumenten
 *
 * @param integer $dms_id ID des Dokuments
 * @param integer $version Versionsnummer des Dokuments
 */
function drawRenameForm($dms_id, $version, $page = NULL, $dpp = NULL, $searching, $searchstring)
{
	global $kategorie_kurzbz;

	$dms = new dms();
	if ($dms->load($dms_id, $version))
	{
		if ($searching == 'true')
		{
			echo '<form action="'.$_SERVER['PHP_SELF'].'?searching=true&searchstring='.$searchstring;
			if (! is_null($page))
				echo '&page='.$page;
			if (! is_null($dpp))
				echo '&dpp='.$dpp;
			echo '" method="POST">';
		}
		else
		{
			echo '<form action="'.$_SERVER['PHP_SELF'].'?kategorie_kurzbz='.$kategorie_kurzbz;
			if (! is_null($page))
				echo '&page='.$page;
			if (! is_null($dpp))
				echo '&dpp='.$dpp;
			echo '" method="POST">';
		}
		echo '
		<table>
		<tr>
			<td>Dateiname:</td>
			<td><input type="text" size="40" name="dateiname" value="'.$dms->convert_html_chars($dms->name).'"></td>
		</tr>
		<tr>
			<td>Beschreibung:</td>
			<td><textarea name="beschreibung" rows="2" cols="80" style="font-size: small;">'.$dms->convert_html_chars($dms->beschreibung).'</textarea></td>
		</tr>
		<tr>
			<td>Schlagworte<br/>(Semikolon getrennt):</td>
			<td><textarea name="schlagworte" rows="2" cols="80" style="font-size: small;">'.$dms->convert_html_chars($dms->schlagworte).'</textarea></td>
		</tr>
		<tr>
			<td>Mimetype</td>
			<td><input type="text" name="mimetype" size="80" maxlength="256" style="font-size: small;" value="'.$dms->convert_html_chars($dms->mimetype).'"/></td>
		</tr>
		<tr>
			<td>CIS-Suche:</td>
			<td><input type="checkbox" name="cis_suche" '.($dms->cis_suche == 'true'?'checked="checked"':'').'></td>
		</tr>
		</table>
		<input type="hidden" name="action" value="rename">
		<input type="hidden" name="dms_id" value="'.$dms_id.'">
		<input type="hidden" name="version" value="'.$version.'">';
		echo '<input type="submit" class="buttondesign" name="submit" value="Umbenennen">
		</form>';
	}
	else
	{
		echo '<span class="error">Fehler beim Laden des Eintrags</span>';
	}
}

/**
 * Erstellt das Formular zum Ändern der Kategorie von Dokumenten
 *
 * @param integer $dms_id ID des Dokuments
 */
function drawChangeKategorie($dms_id, $page = NULL, $dpp = NULL)
{
	global $rechte;
	$dms = new dms();
	$dms->load($dms_id);

	$allKategorien = new dms();
	$allKategorien->getAllKategories();

	if (isset($_REQUEST['searching']) && $_REQUEST['searching'] == 'true')
	{
		echo '<form action="'.$_SERVER['PHP_SELF'].'?chkatID='.$dms_id.'&searching=true&searchstring='.$_REQUEST['searchstring'];
		if (! is_null($page))
			echo '&page='.$page;
		if (! is_null($dpp))
			echo '&dpp='.$dpp;
		echo '" method="POST">';
	}
	else
	{
		echo '<form action="'.$_SERVER['PHP_SELF'].'?chkatID='.$dms_id.'&kategorie_kurzbz='.$dms->kategorie_kurzbz;
		if (! is_null($page))
			echo '&page='.$page;
		if (! is_null($dpp))
			echo '&dpp='.$dpp;
		echo '" method="POST">';
	}
	echo '
		<select name="kategoriez">
			<option value="auswahl">-- Bitte Auswählen --</option>';

	foreach ($allKategorien->result as $kategorienResult)
	{
		if ($kategorienResult->kategorie_oe_kurzbz != '' && !$rechte->isberechtigt('basis/dms', $kategorienResult->kategorie_oe_kurzbz, 'su'))
			continue;

		$selected = '';
		if ($kategorienResult->kategorie_kurzbz == $dms->kategorie_kurzbz)
			$selected = 'selected';

		echo '<option '.$selected.' value="'.$kategorienResult->kategorie_kurzbz.'">'.$kategorienResult->bezeichnung.' ['.$kategorienResult->kategorie_kurzbz.']</option>';
	}

	echo '</select>
		  <input type="hidden" name="action" value="chkat">
		  <input type="hidden" name="dms_id" value="'.$dms_id.'">';
	if (! is_null($page))
		echo '<input type="hidden" name="page" value="'.$page.'">';
	if (! is_null($dpp))
		echo '<input type="hidden" name="dpp" value="'.$dpp.'">';
	echo '<input type="submit" class="buttondesign" name="chkat_save" value="Speichern"></form>';
}

?>
</body>
</html>
