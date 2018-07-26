<?php
/* Copyright (C) 2015 Technikum-Wien
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
 * Authors: Nikolaus Krondraf <nikolaus.krondraf@technikum-wien.at>
 */

// Oberflaeche zum Upload von Dokumenten zu Notizen aus dem FAS
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/dms.class.php');
require_once('../include/notiz.class.php');

header("Content-Type: text/html; charset=utf-8");

$PHP_SELF = $_SERVER['PHP_SELF'];
echo "<html><body>";

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('assistenz') && !$rechte->isBerechtigt('mitarbeiter'))
	die('Keine Berechtigung');

$kategorie_kurzbz = 'notiz';
$dokument_kurzbz = isset($_REQUEST['dokument_kurzbz'])?$_REQUEST['dokument_kurzbz']:'';

if(isset($_POST['fileupload']))
{
	$error = false;

	// dms Eintrag anlegen
	if(isset($_GET['notiz_id']))
	{
		$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		$filename = uniqid();
		$filename.=".".$ext;
		$uploadfile = DMS_PATH.$filename;

		if(move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile))
		{
			$dms = new dms();
			if(!$dms->setPermission($uploadfile))
				echo $dms->errormsg;

			$dms->version='0';
			$dms->kategorie_kurzbz=$kategorie_kurzbz;
			$dms->insertamum=date('Y-m-d H:i:s');
			$dms->insertvon = $user;
			$dms->mimetype=$_FILES['file']['type'];
			$dms->filename = $filename;
			$dms->name = $_FILES['file']['name'];
			$dms->beschreibung = $_POST['anmerkung_intern'];

			if($dms->save(true))
			{
				$dms_id=$dms->dms_id;

				$notiz = new notiz($_GET['notiz_id']);
				if(!$notiz->saveDokument($dms_id))
				{
					echo 'Fehler beim Speichern des Dokuments';
					$error = true;
				}
				else
				{
					echo '<script>window.opener.NotizDokumentUploadScope.RefreshNotizBlocking();window.opener.NotizDokumentUploadScope.selectItem();window.close();</script>';
				}

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
	else
	{
		echo 'Es muss eine Notiz ausgewaehlt werden';
		$error = true;
	}
}

if(isset($_GET['notiz_id']))
{
	echo "	<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
			<html>
			<head>
				<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
				<link href='../skin/style.css.php' rel='stylesheet' type='text/css'>
				<link rel='stylesheet' href='../skin/jquery.css' type='text/css'/>
			</head>
			<body style='padding:10px;'>
			<h1>Upload Dokumente</h1>
			<form method='POST' enctype='multipart/form-data' action='$PHP_SELF?notiz_id=".$_GET['notiz_id']."'>
			<table>
				<tr>
					<td align='right'>Dokument:</td>
					<td><input type='file' name='file' /></td>
				</tr>";

	echo "	<tr>
				<td align='right'>Anmerkung:</td><td><textarea name='anmerkung_intern' cols='45' id='anmerkung_intern'></textarea></td>
			</tr>
			<tr>
				<td><input type='hidden' name='fileupload' id='fileupload'></td>
				<td><input type='submit' name='submitdok' value='Upload'></td>

			</tr></table></form></body></html>";
}
else
{
	echo "Es wurde keine notiz_id angegeben";
}
?>

</body>
</html>
