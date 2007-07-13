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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * @brief Formular zum Uploaden und Loeschen von
 *        Semesterplaenen.
 * @date 31-08-2005
 * @edit 05-01-2007 Umstellung neue DB
 */
	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
    require_once('../../../include/benutzerberechtigung.class.php');
	require_once('../../../include/lehrveranstaltung.class.php');

    //Connection Herstellen
    if(!$sql_conn = pg_pconnect(CONN_STRING))
       die("Fehler beim oeffnen der Datenbankverbindung");

	$user = get_uid();

	if(check_lektor($user,$sql_conn))
       $is_lector=true;
    else
    	die('Sie haben keine Berechtigung fuer diesen Bereich');

   	if(!isset($_GET['lvid']) || !is_numeric($_GET['lvid']))
   		die('Fehlerhafte Parameteruebergabe');
   	else
   		$lvid=$_GET['lvid'];

   	$lv_obj = new lehrveranstaltung($sql_conn);
   	if(!$lv_obj->load($lvid))
   		die('Freifach konnte nicht ermittelt werden');

    $openpath='../../../documents/freifaecher/'.$lv_obj->lehreverzeichnis.'/semesterplan/';

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
<script language="JavaScript">

/**
 * @brief Zeigt eine Sicherheitsabfrage ob die Datei
 * 		  wirklich geloescht werden soll
 */
function ConfirmFile(handle)
{
	return confirm("Wollen Sie die ausgewaehlten Dateien wirklich loeschen? Dieser Vorgang ist unwiderruflich!");
}
</script>
</head>
<title>Upload Semesterplan</title>
<body>
<table class="tabcontent" id="inhalt">
	<tr>
		<td class="tdwidth10">&nbsp;</td>
		<td class="ContentHeader"><font class="ContentHeader">Upload Semesterplan</font></td>

	</tr>
	<tr>
		<td class="tdwidth10">&nbsp;</td>
		<td class="tdwidth10">&nbsp;</td>
	</tr>

<?php
   	echo "<tr><td class='tdwidth10'>&nbsp;</td><td>";
	if(isset($inhalt))
	{
		if($inhalt!="____Ordnerinhalt____")
		{
			if(is_file($openpath . $inhalt))
			{
				exec("rm -r '$openpath$inhalt'");
				writeCISlog('DELETE',"rm -r '$openpath$inhalt'");
				echo "<center>Datei erfolgreich geloescht</center>";
			}
			else
			{
			   echo "<center>Die Datei $openpath$inhalt konnte nicht gefunden werden.</center>";
			}
		}
		else
		{
			echo "<center>Bitte zuerst eine Datei auswaehlen</center>";
		}
	}
	if(isset($userfile))
	{
		if(is_uploaded_file($userfile))
		{
			$fn = $_FILES['userfile']['name']; //Original Dateiname

			if(!stristr($fn, '.php') && !stristr($fn, '.cgi') && !stristr($fn, '.pl'))
			{
				if(copy($userfile,$openpath . $fn))
					echo "<center>Das File wurde erfolgreich hochgeladen</center>";
				else
					echo "<center>Fehler beim hochladen der Datei!</center>";
			}
			else
			{
				echo "<center>Dieser Dateityp ist nicht erlaubt <center>";
			}
		}
	}

	echo "</tr></td>";

  echo "<tr><td class='tdwidth10'>&nbsp;</td><td><form name=\"form1\"  method=\"POST\" action=\"semupload.php?lvid=$lvid\"  onSubmit=\"return ConfirmFile(this);\">";
  echo "<select name=\"inhalt\" size=5>";
  echo "<option selected>____Ordnerinhalt____</option>";
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
  echo "</select>";
  echo "<input type=\"submit\" value=\"Datei Loeschen\">";
  echo "</form></td><td>";

    //FileAuswahlfeld
  echo "<tr><td class='tdwidth10'>&nbsp;</td><td><br><form enctype=\"multipart/form-data\" method=\"POST\" action = \"semupload.php?lvid=$lvid\">";
  echo " <input type=\"file\" name = \"userfile\" size = \"30\">";
  echo " <input type=\"submit\" name=\"upload\" value=\"Upload\">";
  echo "</form></td><td>";

?>
</body>
</html>