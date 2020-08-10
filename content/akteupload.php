<?php
/* Copyright (C) 2006 Technikum-Wien
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
 *		  Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *		  Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

// Oberflaeche zum Upload von Dokumenten aus dem FAS
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/person.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/akte.class.php');
require_once('../include/dokument.class.php');
require_once('../include/dms.class.php');
require_once('../include/phrasen.class.php');

header("Content-Type: text/html; charset=utf-8");

$PHP_SELF = $_SERVER['PHP_SELF'];
echo "<html><body>";

$user = get_uid();
$p = new phrasen();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('assistenz') && !$rechte->isBerechtigt('mitarbeiter'))
	die('Keine Berechtigung');

$kategorie_kurzbz = isset($_REQUEST['kategorie_kurzbz'])?$_REQUEST['kategorie_kurzbz']:'';
$dokument_kurzbz = isset($_REQUEST['dokument_kurzbz'])?$_REQUEST['dokument_kurzbz']:'';

if(isset($_POST['submitbild']))
{
	$error = false;

	// dms Eintrag anlegen
	if(isset($_POST['fileupload']))
	{
		$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		$filename = uniqid();
		$filename.=".".$ext;
		$uploadfile = DMS_PATH.$filename;


		if(move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile))
		{
			$dms = new dms();
			$dms->setPermission($uploadfile);

			$dms->version='0';
			$dms->kategorie_kurzbz=$kategorie_kurzbz;

			$dms->insertamum=date('Y-m-d H:i:s');
			//$dms->insertvon = $user;
			$dms->mimetype=$_FILES['file']['type'];
			$dms->filename = $filename;
			$dms->name = $_FILES['file']['name'];

			if($dms->save(true))
			{
				$dms_id=$dms->dms_id;

			}
			else
			{
				echo 'Fehler beim Speichern der Daten';
				$error = true;
			}
		}
		else
		{
			echo 'Fehler beim Hochladen der Datei';
			$error = true;
		}
	}

	if(isset($_FILES['file']['tmp_name']) && !$error)
	{
		//Extension herausfiltern
		$ext = explode('.',$_FILES['file']['name']);
		$ext = mb_strtolower($ext[count($ext)-1]);

		$filename = $_FILES['file']['tmp_name'];

		//$fp = fopen($filename,'r');
		//auslesen
		//$content = fread($fp, filesize($filename));
		//fclose($fp);

		$akte = new akte();

		if($akte->getAkten($_GET['person_id'], 'Lichtbil'))
		{
			if(count($akte->result)>0)
			{
				$akte = $akte->result[0];
				$akte->new = false;
			}
			else
				$akte->new = true;
		}
		else
		{
			$akte->new = true;
		}

		$dokument = new dokument();
		$dokument->loadDokumenttyp($_REQUEST['dokumenttyp']);

		$akte->dokument_kurzbz = $_REQUEST['dokumenttyp'];
		$akte->person_id = $_GET['person_id'];
		//$akte->inhalt = base64_encode($content);
		$akte->mimetype = $_FILES['file']['type'];
		$akte->erstelltam = date('Y-m-d H:i:s');
		$akte->gedruckt = false;
		$akte->titel = $akte->titel = cutString($_FILES['file']['name'], 32, '~', true); // Filename gekuerzt auf 32 Zeichen;
		$akte->bezeichnung = cutString($dokument->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE], 32);
		$akte->updateamum = date('Y-m-d H:i:s');
		$akte->updatevon = $user;
		$akte->insertamum = date('Y-m-d H:i:s');
		$akte->nachgereicht = false;
		$akte->anmerkung = '';
		$akte->insertvon = $user;
		$akte->uid = '';
		$akte->dms_id = $dms_id;
		$akte->new = true;
		$akte->titel_intern = $_REQUEST['titel_intern'];
		$akte->anmerkung_intern = $_REQUEST['anmerkung_intern'];

		if(!$akte->save())
		{
			echo "<b>Fehler: $akte->errormsg</b>";
		}
		else
		{
			// Bei erfolgreichem Upload wird die Ansicht im FAS refresht
			echo "<b>Erfolgreich gespeichert</b>
			<script>
				window.opener.InteressentDokumentTreeNichtabgegebenDatasourceRefresh();
				window.opener.InteressentDokumentTreeAbgegebenDatasourceRefresh();
				window.close();
			</script>";
		}
	}
}




if(isset($_GET['person_id']))
{
	$dokument = new dokument();
	$dokument->getAllDokumente('Zeugnis,DiplSupp,Bescheid');

	echo "	<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
			<html>
			<head>
				<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
				<link href='../skin/style.css.php' rel='stylesheet' type='text/css'>
				<link rel='stylesheet' href='../skin/jquery.css' type='text/css'/>
			</head>
			<body style='padding:10px;'>
			<h1>Upload Dokumente</h1>
			<form method='POST' enctype='multipart/form-data' action='$PHP_SELF?person_id=".$_GET['person_id']."'>
			<table>
				<tr>
					<td align='right'>Dokument:</td>
					<td><input type='file' name='file' /></td>
				</tr>
				<tr>
					<td align='right'>Typ:</td>
					<td><SELECT style='width:300px' name='dokumenttyp'>";

				foreach ($dokument->result as $dok)
				{
					$onclick="document.getElementById('titel_intern').value='".$dok->dokument_kurzbz."';";

					if(isset($_GET['dokument_kurzbz']) && $_GET['dokument_kurzbz']==$dok->dokument_kurzbz)
						$selected='selected';
					else
						$selected='';
					echo '<option value="'.$dok->dokument_kurzbz.'" onclick="'.$onclick.'" '.$selected.'>'.$dok->bezeichnung."</option>\n";
				}

	echo "	<tr>
				<td align='right'>Titel:</td><td><input size='45' maxlength='32' type='text' name='titel_intern' id='titel_intern' length='35' ></td>
			</tr>
			<tr>
				<td align='right'>Anmerkung:</td><td><textarea name='anmerkung_intern' cols='45' id='anmerkung_intern'></textarea></td>
			</tr>
			<tr>
				<td><input type='hidden' name='kategorie_kurzbz' id='kategorie_kurzbz' value='Akte'>
				<input type='hidden' name='fileupload' id='fileupload'></td>
				<td><input type='submit' name='submitbild' value='Upload'></td>

			</tr></table></form></body></html>";
}
else
{
	echo "Es wurde keine Person_id angegeben";
}
?>

</body>
</html>
