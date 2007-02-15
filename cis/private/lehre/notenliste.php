<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="../../../skin/cis.css" rel="stylesheet" type="text/css">
	<title>Leistungsbeurteilung</title>
	
	<script language="JavaScript">
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
	<table width="100%"  border="0" cellspacing="0" cellpadding="0">
		<tr>
	    <td width="10">&nbsp;</td>
	    <td><table width="100%"  border="0" cellspacing="0" cellpadding="0">
	    	<tr> 
	      	<td class="ContentHeader"><font class="ContentHeader">&nbsp;Leistungsbeurteilung</font></td>
	    	</tr>
	    	<tr> 
	      	<td>&nbsp;</td>
	    	</tr>
	    	<tr>
	    	<td>
<?php
/* 
 * Erstellt eine Notenliste des aktuellen Studiensemesters
 * zur Information fuer Studenten
 */
require('../../../include/functions.inc.php');
require('../../config.inc.php');
if(!$conn=pg_connect(CONN_STRING_FAS))
	die("Die Datenbankverbindung konnte nicht hergestellt werden.");

if(isset($stsem) && (!is_numeric($stsem) || strlen($stsem)>2))
	die('Fehler');
$user = get_uid();

$error = '';
$student_id = '';

//Student ID ermitteln
$qry = "SELECT 
			student.student_pk as student_id,
			vorname, 
			familienname,
			studiengang.name as stg
		FROM 
			person, 
			student,
			studiengang 
		WHERE 
			person.uid='$user' AND 
			person.person_pk=student.person_fk AND
			studiengang.studiengang_pk=student.studiengang_fk 
		ORDER BY aufgenommenam DESC LIMIT 1
			";

if(!$row=pg_fetch_object(pg_query($conn,$qry)))
{
	$error .= 'Sie m&uuml;ssen als Student eingeloggt sein um ihre Noten abzufragen!';
}
else 
{	
	$vorname=$row->vorname;
	$nachname=$row->familienname;
	$stg_name=$row->stg;
	$student_id = $row->student_id;
	//Aktuelles Studiensemester ermitteln
	$qry = "SELECT 
				CASE studiensemester.art 
					WHEN 1 THEN 'WS' || studiensemester.jahr
					WHEN 2 THEN 'SS' || studiensemester.jahr 					
				END as stsem_name,
				studiensemester_pk, aktuell
			FROM studiensemester order by jahr, art DESC";
	
	if(!$result = pg_query($conn, $qry))
		die("Fehler beim lesen aus der Datenbank");
		
	/*if($row = pg_fetch_object($result))
	{
		$stsem = $row->studiensemester_pk;
		$stsem_name = $row->stsem_name;
	}
	else 
		die("Derzeit kann keine Notenliste erstellt werden");
		*/
	echo "<br />";
	echo "<b>Name:</b> $vorname $nachname<br />";
	echo "<b>Studiengang:</b>  $stg_name<br />";
	echo "<b>Studiensemester:</b> <SELECT name='stsem' onChange=\"MM_jumpMenu('self',this,0)\">";
	while($row = pg_fetch_object($result))
	{
		if(!isset($stsem) && $row->aktuell=='J')
			$stsem=$row->studiensemester_pk;
		if($stsem==$row->studiensemester_pk)
			echo "<OPTION value='notenliste.php?stsem=$row->studiensemester_pk' selected>$row->stsem_name</OPTION>";
		else
			echo "<OPTION value='notenliste.php?stsem=$row->studiensemester_pk'>$row->stsem_name</OPTION>";
	}
	echo "</SELECT><br />";
	
	//echo "Datum: ".date('d.m.Y')."<br />";
	echo "<br />";
	
	//Lehrveranstaltungen und Noten holen
	$qry = "SELECT 
				lehrveranstaltung.name as lvname, 
				note.note as note, 				
				status
			FROM 
				note, 
				lehrveranstaltung
			WHERE 
				note.lehrveranstaltung_fk=lehrveranstaltung.lehrveranstaltung_pk AND 
				lehrveranstaltung.studiensemester_fk='$stsem' AND 
				note.student_fk='$student_id'
			ORDER BY lvname";
	
	if($result=pg_query($conn,$qry))
	{
		//Tabelle anzeigen
		$tbl= "<table><tr class='liste'><th>Lehrveranstaltung</th><th>Note</th><th></th></tr>";
		$i=0;
		while($row=pg_fetch_object($result))
		{
			$i++;
			$tbl.= "<tr class='liste".($i%2)."'><td>$row->lvname</td>";
			$tbl.= "<td>";
			//Note ausgeben
			switch($row->note)
			{
				case  1: $tbl.= "Sehr gut"; 						break;
				case  2: $tbl.= "Gut";							break;
				case  3: $tbl.= "Befriedigend";					break;
				case  4: $tbl.= "Gen&uuml;gend";					break;
				case  5: $tbl.= "Nicht Gen&uuml;gend";			break;
				case  6: $tbl.= "Angerechnet";					break;
				case  7: $tbl.= "Nicht Beurteilt";				break;
				case  8: $tbl.= "Teilgenommen";					break;
				case  9: $tbl.= "Noch nicht eingetragen";			break;
				case 10: $tbl.= "Bestanden";						break;
				case 11: $tbl.= "Approbiert";						break;
				case 12: $tbl.= "erfolgreich Absolviert";			break;
				case 13: $tbl.= "nicht erfolgreich Absolviert";	break;
			}
			$tbl.= "</td><td>";
			//Status ausgeben
			switch($row->status)
			{
				case  1: $tbl.= "1. Prüfung"; 			break;
				case  2: $tbl.= "2. Prüfung"; 			break;
				case 11: $tbl.= "Kommissionelle Prüfung"; break;
				default: $tbl.= "&nbsp;"; 				break;
			}
			$tbl.= "</td></tr>";
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