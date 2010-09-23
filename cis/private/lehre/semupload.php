<?php
/* Copyright (C) 2010 FH Technikum Wien
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
/**
 * Formular zum Uploaden und Loeschen von
 * Semesterplaenen.
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/lehrveranstaltung.class.php');

$user = get_uid();

if(check_lektor($user))
	$is_lector=true;

if(!isset($_GET['lvid']) || !is_numeric($_GET['lvid']))
{
	die("Fehler bei der Parameteruebergabe");
}
else
	$lvid = $_GET['lvid'];

$lv_obj = new lehrveranstaltung();
if(!$lv_obj->load($lvid))
	die('Fehler beim Laden der Lehrveranstaltung');
$stg_obj = new studiengang();

if(!$stg_obj->load($lv_obj->studiengang_kz))
	die('Fehler beim Laden des Studienganges');

$openpath = DOC_ROOT.'/documents/'.strtolower($stg_obj->kuerzel).'/'.$lv_obj->semester.'/'.strtolower($lv_obj->lehreverzeichnis).'/semesterplan/';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Upload Semesterplan</title>
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<script language="JavaScript" type="text/javascript">
	
	/**
	 * Zeigt eine Sicherheitsabfrage ob die Datei
	 * wirklich gelöscht werden soll
	 */
	function ConfirmFile(handle)
	{
		return confirm('Wollen Sie die ausgewählten Dateien wirklich löschen? Dieser Vorgang ist unwiderruflich!');
	}
	
	</script>
</head>
<body id="inhalt">
	<table class="tabcontent" >
	<tr>
		<td class="tdwidth10">&nbsp;</td>
		<td class="ContentHeader"><font class="ContentHeader">Upload Semesterplan</font></td>

	</tr>
	<tr>
		<td class="tdwidth10">&nbsp;</td>
		<td class="tdwidth10">&nbsp;</td>
	</tr>

<?php


	if(!$is_lector)
		die('<tr><td class="tdwidth10">&nbsp;</td><td>Sie haben keine Berechtigung für diesen Bereich</td></tr>');

	echo '<tr><td class="tdwidth10">&nbsp;</td><td>';
	if(isset($_POST['inhalt']))
	{
		$inhalt = $_POST['inhalt'];
		if($inhalt!="____Ordnerinhalt____")
		{
			if(!mb_strstr($inhalt,'..'))
			{
				if(is_file($openpath . $inhalt))
				{
					writeCISlog('DELETE', "rm -r '$openpath$inhalt'");
					exec("rm -r '$openpath$inhalt'");
					echo '<center>Datei erfolgreich gelöscht</center>';
				}
				else
				{
				   echo "<center>Die Datei $openpath$inhalt konnte nicht gefunden werden.</center>";
				}
			}
			else
			{
				writeCISlog('REPORT', 'versuchter Loeschvorgang von '.$openpath.$inhalt);
				echo '<center>Fehlerhafte Parameter</center>';
			}
		}
		else
		{
			echo '<center>Bitte zuerst eine Datei auswählen</center>';
		}
	}

	if(isset($_POST['upload']))
	{
		if(is_uploaded_file($_FILES['userfile']['tmp_name']))
		{
			$fn = $_FILES['userfile']['name']; //Original Dateiname

			if(check_filename($fn))
			{
				if(!stristr($fn, '.php') && !stristr($fn, '.php3') &&
				   !stristr($fn,'.php4') && !stristr($fn, '.php5') &&
				   !stristr($fn, '.cgi') && !stristr($fn, '.pl'))
				{
					if(move_uploaded_file($_FILES['userfile']['tmp_name'],$openpath . $fn))
					{
						exec('sudo chown www-data:teacher "'.$openpath.$fn.'"');
						echo '<center>Das File wurde erfolgreich hochgeladen</center>';
					}
					else
						echo '<center>Fehler beim Upload! Bitte Versuchen Sie es erneut</center>';
				}
				else
				{
					echo '<center>Dieser Dateityp ist nicht erlaubt <center>';
				}
			}
			else
				echo '<center>Der Dateiname darf nur Buchstaben und Zahlen enthalten</center>';
		}
		else
			echo '<center>Fehler beim Upload! Bitte Versuchen Sie es erneut</center>';
	}

	echo '</tr></td>';

	echo '<tr><td class="tdwidth10">&nbsp;</td><td><form accept-charset="UTF-8" name="form1"  method="POST" action="semupload.php?lvid='.$lvid.'"  onSubmit="return ConfirmFile(this);">';
	echo '<select name="inhalt" size=5>';
	echo '<option selected>____Ordnerinhalt____</option>';
	//Inhalt des Semesterplan Ordners Auslesen
	if(is_dir($openpath))
	{
  		$dest_dir = dir($openpath);
		while($entry = $dest_dir->read())
		{
			if(!is_dir($entry))
				echo "<option>$entry</option>";
		}
	}
	echo '</select>';
	echo '<input type="submit" value="Datei Löschen">';
	echo '</form></td><td>';

    //FileAuswahlfeld
	echo '<tr><td class="tdwidth10">&nbsp;</td><td><br><form enctype="multipart/form-data" method="POST" action="semupload.php?lvid='.$lvid.'">';
	echo ' <input type="file" name="userfile" size="30">';
	echo ' <input type="submit" name="upload" value="Upload">';
	echo '</form></td><td>';
?>
	</table>
</body>
</html>