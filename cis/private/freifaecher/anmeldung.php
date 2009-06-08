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
/*
 * Ermoeglicht das Anmelden zu Freifaechern
 */
require_once('../../config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/lehrveranstaltung.class.php');

if(!$conn=pg_connect(CONN_STRING))
	die('Die Datenbankverbindung konnte nicht hergestellt werden.');

$user = get_uid();

//Aktuelles Studiensemester holen
$stsem_obj = new studiensemester($conn);
$stsem = $stsem_obj->getaktorNext();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<title>Freifaecher Anmeldung</title>
	</head>

	<body>
	<table class="tabcontent" id="inhalt">
		<tr>
	    <td class="tdwidth10">&nbsp;</td>
	    <td><table class="tabcontent">
	    	<tr>
	      	<td class="ContentHeader"><font class="ContentHeader">&nbsp;Freif&auml;cher Anmeldung</font></td>
	    	</tr>
	    	<tr>
	      	<td>&nbsp;</td>
	    	</tr>
	    	<tr>
		    	<td>
		    	Bitte markieren Sie die Freif&auml;cher f&uuml;r die Sie sich Anmelden m&ouml;chten
		    	<br />
<?php
//Wenn das Formular abgeschickt wurde
if(isset($_POST['submit']))
{
	//Wenn eine der Checkboxen angeklickt wurde
	if(isset($_POST['chkbox']))
	{
		pg_query($conn,'BEGIN');
		//Zuerst die alten Eintraege herausloeschen...
		$qry = "DELETE FROM campus.tbl_benutzerlvstudiensemester WHERE uid='$user' AND studiensemester_kurzbz='$stsem'";
		if(!pg_query($conn,$qry))
			die('Fehler beim aktualisieren der Freifaecherzuteilung! Bitte Versuchen Sie es erneut');

		//...dann die angeklickten FF hinzufuegen
		foreach ($_POST['chkbox'] as $elem)
		{
			$qry = "INSERT INTO campus.tbl_benutzerlvstudiensemester(uid, lehrveranstaltung_id, studiensemester_kurzbz) VALUES('$user','$elem','$stsem');";
			if(!pg_query($conn,$qry))
			{
				pg_query($conn,'ROLLBACK');
				die("Freifaecher konnten nicht zugeteilt werden! Bitte Versuchen Sie es erneut");
			}
		}
		pg_query($conn,'COMMIT');
		echo "<b>Ihre Daten wurden erfolgreich aktualisiert!</b><br />";
	}
	else
	{
		//Wenn keine Checkbox angeklickt wurde, alle Eintraege herausloeschen
		$qry = "DELETE FROM campus.tbl_benutzerlvstudiensemester WHERE uid='$user' AND studiensemester_kurzbz='$stsem'";
		if(!pg_query($conn,$qry))
			die("Fehler beim aktualisieren der Freifaecherzuteilung! Bitte Versuchen Sie es erneut");
		else
			echo "<b>Ihre Daten wurden erfolgreich aktualisiert!</b><br />";
	}
}

//Freifachzuteilungen holen
$qry = "SELECT * FROM campus.tbl_benutzerlvstudiensemester WHERE uid = '$user' AND studiensemester_kurzbz='$stsem'";
if($result=pg_query($conn,$qry))
{
	$ff = array();
	while($row=pg_fetch_object($result))
		$ff[] = $row->lehrveranstaltung_id;
}
else
	echo 'Fehler beim Auslesen der Zuteilunstabelle';

echo '<br />';
//Freifaecher laden
$lv_obj = new lehrveranstaltung($conn);
if($lv_obj->load_lva('0',null,null,true,null,'bezeichnung'))
{
	$anz = count($lv_obj->lehrveranstaltungen);

	echo "<form method='POST'>";
	$i=0;
	echo "<table><tr><td valign='top'>";
	foreach($lv_obj->lehrveranstaltungen as $row)
	{
		//Auftrennen in eine zweite Spalte bei der haelfte der Eintraege
		if($i==intval($anz/2))
			echo "</td><td valign='top'>";

		if(in_array($row->lehrveranstaltung_id,$ff))
			$checked = "checked='true'";
		else
			$checked = '';

		//Wenn aktiv=false dann ist fuer dieses Lehrfach keine Anmeldung mehr moeglich
		if($row->aktiv==false && $checked=='')
			$disabled = "disabled='true'";
		else
			$disabled = "";

		echo "<input type='checkbox' value='$row->lehrveranstaltung_id' name='chkbox[]' $checked $disabled >$row->bezeichnung<br />";
		$i++;
	}
	echo "</td></tr><tr><td></td><td>&nbsp;</td></tr>";
	echo "<tr><td></td><td><input type='submit' name='submit' value='Speichern'></td></tr>";
	echo "</table>";
	echo "</form>";
}
else
{
	die("Fehler bei Auslesen der Freifaecher! Bitte versuchen Sie es erneut");
}
?>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
</body>
</html>