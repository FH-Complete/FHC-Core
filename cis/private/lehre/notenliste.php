<?php
/* Copyright (C) 2008 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/*
 * Erstellt eine Liste mit den Noten des eingeloggten Studenten
 * das betreffende Studiensemester kann ausgewaehlt werden
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/note.class.php');

if (!$db = new basis_db())
	die('Fehler beim Herstellen der Datenbankverbindung');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<title>Leistungsbeurteilung</title>

	<script language="JavaScript" type="text/javascript">
	function MM_jumpMenu(targ, selObj, restore)
	{
	  eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");

	  if(restore)
	  {
	  	selObj.selectedIndex = 0;
	  }
	}
	</script>
</head>

<body>
	<table class="tabcontent" id="inhalt">
		<tr>
	    <td class="tdwidth10">&nbsp;</td>
	    <td><table class="tabcontent">
	    	<tr>
	      		<td class="ContentHeader"><font class="ContentHeader">&nbsp;Leistungsbeurteilung</font></td>
	    	</tr>
	    	<tr>
	      		<td>&nbsp;</td>
	    	</tr>
	    	<tr>
	    		<td>
<?php

if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
	$stsem = '';
	
$user = get_uid();
$datum_obj = new datum();

$error = '';


if(!check_student($user))
{
	$error .= 'Sie m&uuml;ssen als Student eingeloggt sein um ihre Noten abzufragen!';
	echo $error;
}
else
{
	$qry = "SELECT vw_student.vorname, vw_student.nachname, tbl_studiengang.bezeichnung 
		FROM public.tbl_studiengang JOIN campus.vw_student USING (studiengang_kz) 
		WHERE campus.vw_student.uid = '".addslashes($user)."'";
	
	if (!$result=$db->db_query($qry))
		die("Kein Studentendatensatz!");
	else
	{
		$row=$db->db_fetch_object($result);
		
		$vorname= $row->vorname;
		$nachname = $row->nachname;
		$stg_name = $row->bezeichnung;
	}
	
	//Aktuelles Studiensemester ermitteln
	
	$stsem_obj = new studiensemester();
	if($stsem=='')
		$stsem = $stsem_obj->getaktorNext();
	
	$stsem_obj->getAll();

	
	echo "<br />";
	echo "<b>Name:</b> $vorname $nachname<br />";
	echo "<b>Studiengang:</b>  $stg_name<br />";
	echo "<b>Studiensemester:</b> <SELECT name='stsem' onChange=\"MM_jumpMenu('self',this,0)\">";
	foreach ($stsem_obj->studiensemester as $semrow)
	{
		if($stsem == $semrow->studiensemester_kurzbz)
			echo "<OPTION value='notenliste.php?stsem=$semrow->studiensemester_kurzbz' selected>$semrow->studiensemester_kurzbz</OPTION>";
		else
			echo "<OPTION value='notenliste.php?stsem=$semrow->studiensemester_kurzbz'>$semrow->studiensemester_kurzbz</OPTION>";
	}
	echo "</SELECT><br />";

	//echo "Datum: ".date('d.m.Y')."<br />";
	echo "<br />";

	//Lehrveranstaltungen und Noten holen
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_zeugnisnote.note, tbl_lvgesamtnote.note as lvnote, tbl_zeugnisnote.benotungsdatum, tbl_lvgesamtnote.freigabedatum, tbl_lvgesamtnote.benotungsdatum as lvbenotungsdatum
			FROM
				lehre.tbl_lehrveranstaltung, lehre.tbl_zeugnisnote
			LEFT OUTER JOIN
				campus.tbl_lvgesamtnote
			USING (lehrveranstaltung_id, student_uid)
			WHERE
				tbl_zeugnisnote.student_uid = '".addslashes($user)."'
			AND
				tbl_zeugnisnote.studiensemester_kurzbz = '".addslashes($stsem)."'
			AND
				tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_zeugnisnote.lehrveranstaltung_id
			ORDER BY bezeichnung";

	if($result=$db->db_query($qry))
	{
		//Tabelle anzeigen
		$tbl= "<table><tr class='liste'><th>Lehrveranstaltung</th><th>LV-Note</th><th>Zeugnisnote</th><th>Benotungsdatum der Zeugnisnote</th></tr>";
		$i=0;
		while($row=$db->db_fetch_object($result))
		{
			$i++;
			$tbl.= "<tr class='liste".($i%2)."'><td>$row->bezeichnung</td>";
			$tbl.= "<td>";
			
			//Nur freigegebene Noten anzeigen
			if($row->freigabedatum>=$row->lvbenotungsdatum)
			{
				$note = new note();
				if($note->load($row->lvnote))
					$tbl.=$note->bezeichnung;
				else
					$tbl.=$row->lvnote;
			}
			$tbl.= "</td>";
			if ($row->note != $row->lvnote && $row->lvnote != NULL)
				$markier = " style='border: 1px solid red;'";
			else
				$markier = "";
			$tbl .= "<td".$markier.">";
			
			$note = new note();
			if($note->load($row->note))
				$tbl.=$note->bezeichnung;
			else
				$tbl.=$row->note;
		
			
			$tbl .= "</td>";
			$tbl .= '<td>'.$datum_obj->formatDatum($row->benotungsdatum,'d.m.Y').'</td>';
			$tbl .= "</tr>";
		}
		

		$tbl.= "</table>";
		if($i==0)
			echo "Es wurden noch keine Beurteilungen eingetragen";
		else
			echo $tbl;
	}
	else
	{
		$error .= "Fehler beim Auslesen der Noten";
	}
}
echo $error;
?>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
</body>
</html>