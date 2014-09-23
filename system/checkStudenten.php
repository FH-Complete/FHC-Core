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
 *          Karl Burkhart <burkhart@technikum-wien.at>
 */

require_once('../config/vilesci.config.inc.php');
require_once('../include/basis_db.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/organisationsform.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/student.class.php'); 
require_once('../include/studiengang.class.php');
require_once('../include/functions.inc.php');
require_once('../include/datum.class.php');

$db = new basis_db();
$datum = new datum();
$studiensemester = new studiensemester(); 
$aktSem = $studiensemester->getaktorNext();
$nextSem = $studiensemester->getNextFrom($aktSem);  
$ausgabe = array(); 

$text ="";
?>
<html>
	<head>
	<title>Check Studenten</title>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	
	<link rel="stylesheet" href="../skin/vilesci.css" type="text/css">
	</head>

	<body class="background_main">
		<h2>Studenten Checkskript für BIS-Meldung</h2>
<?php 

/*
 *   	Studiengang muss beim Prestudenten und beim Studenten gleich sein
 */

$qry="select stud.student_uid, pre.studiengang_kz, stud.studiengang_kz studiengang 
from public.tbl_prestudent pre 
join public.tbl_student stud using(prestudent_id) 
where stud.studiengang_kz != pre.studiengang_kz;"; 

$text.="Suche Studiengänge die bei Prestudenten und Studenten nicht gleich sind ...<br><br>";

if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{
		$ausgabe[$row->studiengang][1][]= $row->student_uid; 
		$text.="Studenten-uid: ".$row->student_uid."<br>"; 
	}
}
else
	$text.="Fehler bei der Abfrage aufgetreten. <br>"; 

/*	
 * Abbrecher dürfen nicht mehr aktiv sein 
 */
	
$text.= "<br>Suche alle Abbrecher die noch aktiv sind ... <br><br>";

$qry ="select pre_status.status_kurzbz, benutzer.aktiv, benutzer.uid, student.studiengang_kz studiengang
from public.tbl_prestudentstatus pre_status 
join public.tbl_prestudent pre using(prestudent_id)
join public.tbl_student student using(prestudent_id)
join public.tbl_benutzer benutzer on(benutzer.uid=student.student_uid)
where pre_status.status_kurzbz ='Abbrecher' and benutzer.aktiv = 'true';";

if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{
		$ausgabe[$row->studiengang][2][]= $row->uid; 
		$text .="Studenten-uid: ".$row->uid."<br>"; 
	}
}
else
	$text.= "Fehler bei der Abfrage aufgetreten. <br>"; 



/*
 *	Organisationsform eines Studienganges, sollte mit den Organisationsformen der Studenten übereinstimmen
 */

$text.= "<br>Suche Studenten mit ungleichen Organisationsformeinträgen (Studiengang <--> Prestudentstatus) ... <br><br>"; 

$orgArray = array(); 
$orgForm = new organisationsform(); 

$qry ="select studiengang.orgform_kurzbz as studorgkz, student.student_uid, prestudentstatus.orgform_kurzbz as studentorgkz, student.studiengang_kz studiengang
from public.tbl_studiengang studiengang
join public.tbl_student student using(studiengang_kz)
join public.tbl_prestudent prestudent using(prestudent_id)
join public.tbl_prestudentstatus prestudentstatus using(prestudent_id)
join public.tbl_benutzer benutzer on(benutzer.uid = student.student_uid)
where benutzer.aktiv = 'true' and prestudentstatus.status_kurzbz ='Student'
and studiengang.studiengang_kz < 10000
and prestudentstatus.studiensemester_kurzbz = '$aktSem' 
order by student_uid; "; 


if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{
		$studOrgform = $row->studorgkz; 
		$student_uid = $row->student_uid; 
		$studentOrgform = $row->studentorgkz; 

		$orgArray = $orgForm->checkOrgForm($studOrgform); 
		if(is_array($orgArray))
		{
			if(!in_array($studentOrgform, $orgArray))
			{
				$ausgabe[$row->studiengang][3][]= $row->student_uid; 
				$text.= "Student_uid: $student_uid <br>";
			}
		}
	}
}
else
	$text.="Fehler bei der Abfrage aufgetreten. <br>"; 
	

/*
 * Abbrecher dürfen nicht wieder einen Status bekommen
 */

$prestudentAbbrecher = new prestudent(); 
$prestudentLast = new prestudent(); 
$text.= "<br>Suche alle Abbrecher die wieder einen Status bekommen haben...<br><br>"; 

$qry ="select student.student_uid, prestudent.prestudent_id, student.studiengang_kz studiengang
from public.tbl_student student
join public.tbl_prestudent prestudent using(prestudent_id)
join public.tbl_prestudentstatus prestatus using(prestudent_id) 
where prestatus.status_kurzbz = 'Abbrecher'; "; 

if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{
		$student_uid = $row->student_uid; 
		$prestudent_id = $row->prestudent_id; 
		
		$prestudentLast->result = array(); 
		
		$prestudentLast->getLastStatus($prestudent_id); 
		
		if($prestudentLast->status_kurzbz != 'Abbrecher')
		{
			$ausgabe[$row->studiengang][4][]= $student_uid; 
			$text.= "Studenten-uid: ".$student_uid."<br>";   
		}
	}
}


/*
 * 	Aktuelles Semester beim Studenten stimmt nicht mit dem Ausbildungssemester des aktuellen Status überein
 */ 

$text .="<br><br>Suche Studenten deren Semstern nicht mit dem Ausbildungssemesters des aktuellen Status übereinstimmt ... <br><br>"; 
	
$student = new student(); 
$prestudent = new prestudent(); 
$qry = "select distinct(student.student_uid), prestudent.prestudent_id, status.ausbildungssemester, lv.semester, student.studiengang_kz studiengang
from public.tbl_student student
join public.tbl_studentlehrverband lv using(student_uid)
join public.tbl_prestudent prestudent using(prestudent_id)
join public.tbl_prestudentstatus status using(prestudent_id) 
WHERE status.studiensemester_kurzbz = '$aktSem'  
and lv.studiensemester_kurzbz = '$aktSem' AND status.status_kurzbz NOT IN ('Interessent','Bewerber')
and get_rolle_prestudent (prestudent_id, '$aktSem')='Student';"; 

if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{
		$student_uid = $row->student_uid;
		
		if($row->ausbildungssemester != $row->semester)
		{
			$ausgabe[$row->studiengang][5][]= $student_uid; 
			$text.="Studenten-uid: ".$student_uid."<br>";
		} 
	}
}


/*
 * Inaktive Studenten sollen keinen "aktiven" Status haben (Diplomant, Student, Unterbrecher, Praktikant)
 */	 

$text.="<br><br>Suche alle inaktiven Studenten mit einem aktiven Status ... <br><br>"; 

$qry = "Select distinct(student.student_uid), student.studiengang_kz studiengang 
from public.tbl_benutzer benutzer 
join public.tbl_student student on(benutzer.uid = student.student_uid)
join public.tbl_prestudent prestudent using(prestudent_id)
where benutzer.aktiv = 'false' 
and get_rolle_prestudent (prestudent_id, '$aktSem') in ('Student', 'Diplomand', 'Unterbrecher', 'Praktikant')";

if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{
		$ausgabe[$row->studiengang][6][]= $row->student_uid; 
		$text.="Studenten-uid: ".$row->student_uid."<br>"; 
	}
}

/*
 * 	Das Datum der Inskription darf nicht vor der letzten BIS-Meldung liegen
 * 	zB. Wenn Student im WS2009 studiert darf Studentenstatus nicht vor 15.4.2009 liegen
 * 	zB. Wenn Student im SS2010 studiert darf Studentenstatus nicht vor 15.11.2009 liegen
 */

$text.="<br><br>Suche alle Studenten deren Inskription im aktuellen Semester vor der letzten BIS-Meldung liegt ...<br><br>";

$qry ="Select distinct(student.student_uid), prestudent.prestudent_id, student.studiengang_kz studiengang 
from public.tbl_benutzer benutzer 
join public.tbl_student student on(benutzer.uid = student.student_uid)
join public.tbl_prestudent prestudent using(prestudent_id)
join public.tbl_prestudentstatus prestatus using(prestudent_id) 
where benutzer.aktiv = 'true'"; 

if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{	
		$prestudent = new prestudent();

		$prestudent->getFirstStatus($row->prestudent_id, 'Student');
		// wenn Student im aktuellen Semester zum ersten Mal den Status Student bekommt
		if($prestudent->studiensemester_kurzbz == $aktSem)
		{
			$datumBIS = getDateForInscription($aktSem); 
			$datumInscription = $datum->formatDatum($prestudent->datum, 'Y-m-d');
			
			// Wenn Inscriptionsdatum vor der letzten BIS Meldung liegt
			if($datumInscription < $datumBIS)
			{
				$ausgabe[$row->studiengang][7][]= $row->student_uid; 
				$text.= $row->student_uid ." Inskribiert am: ".$datumInscription." BIS Meldung: ".$datumBIS."<br>"; 
			}
		}

	}
}


/*
 *	Datum und Studiensemester bei den Stati sind in falscher Reihenfolge 
 */

$text.="<br><br>Suche alle Studenten die Datum und Studiensemester in deren Stati in falscher Reihenfolge haben ...<br><br>"; 
$prestudentFirst = new prestudent();
$prestudentSecond = new prestudent();
$i = 0; 

// alle aktiven Studenten die im aktuellen Semster den Status Student haben
$qry_student ="Select distinct(student_uid), prestudent.prestudent_id, student.studiengang_kz studiengang 
from public.tbl_student student 
join public.tbl_benutzer benutzer on(student.student_uid = benutzer.uid)
join public.tbl_prestudent prestudent using(prestudent_id)
join public.tbl_prestudentstatus status using(prestudent_id)
where benutzer.aktiv = 'true' 
and status.status_kurzbz ='Student' 
and status.studiensemester_kurzbz = '$aktSem';";

if($result = $db->db_query($qry_student))
{
	while($student = $db->db_fetch_object($result))
	{	
		$qry_orderSemester ="SELECT * FROM public.tbl_prestudentstatus status 
		join public.tbl_studiensemester semester using(studiensemester_kurzbz)
		where prestudent_id = '$student->prestudent_id' 
		order by start DESC, datum DESC;"; 
		
		if($result1 = $db->db_query($qry_orderSemester))
		{
			$prestudentSecond->result = array();
			$prestudentFirst->result = array(); 
			while($row = $db->db_fetch_object($result1))
			{
				$prestudentStatus = new prestudent(); 
				
				$prestudentStatus->prestudent_id = $row->prestudent_id; 
				$prestudentStatus->status_kurzbz = $row->status_kurzbz; 
				$prestudentStatus->studiensemester_kurzbz = $row->studiensemester_kurzbz; 
				$prestudentStatus->ausbildungssemester = $row->ausbildungssemester; 
				$prestudentStatus->datum = $row->datum; 
				$prestudentStatus->insertamum = $row->insertamum; 
				$prestudentStatus->insertvon = $row->insertvon;
				$prestudentStatus->updateamum = $row->updateamum; 
				$prestudentStatus->updatevon = $row->updatevon; 
				$prestudentStatus->ext_id = $row->ext_id; 
				$prestudentStatus->orgform_kurzbz = $row->orgform_kurzbz; 
				
				$prestudentFirst->result[] = $prestudentStatus; 
			}
		}
		else 
			$text.= "Fehler";

		if(!$prestudentSecond->getPrestudentRolle($student->prestudent_id,null,null,'Datum DESC, insertamum DESC'))
			$text.= "ERROR:".$prestudentSecond->errormsg; 
	
		for($i=0; $i<count($prestudentFirst->result); $i++)
		{
			if($prestudentFirst->result[$i]->studiensemester_kurzbz != $prestudentSecond->result[$i]->studiensemester_kurzbz)
			{
				$ausgabe[$student->studiengang][8][]= $student->student_uid; 
				$text.= "Studenten-uid: ".$student->student_uid."<br>"; 
				continue 2; 
			}
		}
	}
}

/*
 *	 Aktive Studenten ohne Status in aktuellen Studiensemester
 */

$prestudent = new prestudent(); 
$text.="<br><br>Suche alle aktiven Studenten die keinen Status im aktuellen Studiensemester haben.<br><br>"; 

$qry ="Select distinct (student_uid), prestudent.prestudent_id, student.studiengang_kz studiengang
from public.tbl_student student 
join public.tbl_benutzer benutzer on (benutzer.uid = student.student_uid)
join public.tbl_prestudent prestudent using(prestudent_id)
join public.tbl_prestudentstatus status using(prestudent_id)
where benutzer.aktiv = 'true'"; 

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if(!$prestudent->getLastStatus($row->prestudent_id, $aktSem) 
		&& !$prestudent->getLastStatus($row->prestudent_id, $nextSem))
		{
			$ausgabe[$row->studiengang][9][]= $row->student_uid; 
			$text.= $row->student_uid."<br>";
		} 
	}
}

/*
 *	 Bewerber im aktuellen StSem die in Mischformstudiengängen keine Orgform eingetragen haben
 */
$text.="<br><br>Suche alle Bewerber die keine Orgform eingetragen haben.<br><br>"; 

$qry ="
SELECT
	tbl_prestudent.prestudent_id, tbl_person.vorname, tbl_person.nachname, tbl_prestudent.studiengang_kz as studiengang
FROM
	public.tbl_prestudent
	JOIN public.tbl_person USING(person_id)
	JOIN public.tbl_prestudentstatus USING(prestudent_id)
	JOIN public.tbl_studiengang USING(studiengang_kz)
WHERE
	tbl_prestudentstatus.status_kurzbz='Bewerber'
	AND tbl_studiengang.mischform
	AND (tbl_prestudentstatus.orgform_kurzbz='' OR tbl_prestudentstatus.orgform_kurzbz is null)
	AND tbl_prestudentstatus.studiensemester_kurzbz=".$db->db_add_param($aktSem); 

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$ausgabe[$row->studiengang][10][]= $row->vorname.' '.$row->nachname.' '.$row->prestudent_id; 
		$text.= $row->vorname.' '.$row->nachname.' '.$row->prestudent_id."<br>"; 
	}
}

/*
 *	 Studierende im aktuellen StSem die in Mischformstudiengängen keine Orgform eingetragen haben
 */
$text.="<br><br>Suche alle Bewerber die keine Orgform eingetragen haben.<br><br>"; 

$qry ="
SELECT
	tbl_prestudent.prestudent_id, tbl_person.vorname, tbl_person.nachname, tbl_prestudent.studiengang_kz as studiengang
FROM
	public.tbl_prestudent
	JOIN public.tbl_person USING(person_id)
	JOIN public.tbl_prestudentstatus USING(prestudent_id)
	JOIN public.tbl_studiengang USING(studiengang_kz)
WHERE
	tbl_prestudentstatus.status_kurzbz='Student'
	AND tbl_studiengang.mischform
	AND (tbl_prestudentstatus.orgform_kurzbz='' OR tbl_prestudentstatus.orgform_kurzbz is null)
	AND tbl_prestudentstatus.studiensemester_kurzbz=".$db->db_add_param($aktSem); 

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$ausgabe[$row->studiengang][11][]= $row->vorname.' '.$row->nachname.' '.$row->prestudent_id; 
		$text.= $row->vorname.' '.$row->nachname.' '.$row->prestudent_id."<br>"; 
	}
}
// Ausgabe der Studenten
foreach($ausgabe as $stg_kz=>$value)
{
	//Wenn eine Studiengangskennzahl uebergeben wird, nur diese anzeigen und die anderen ueberspringen
	if(isset($_REQUEST['stg_kz']) && $_REQUEST['stg_kz']!=$stg_kz)
		continue;
	
	$studiengang = new studiengang(); 
	$studiengang->load($stg_kz); 
	
	echo "<br><br><h2>".$studiengang->bezeichnung ." (".$studiengang->kurzbzlang.")</h2>"; 
	echo "<table border='0'>"; 
	foreach($value as $code=>$uid)
	{
		switch ($code) {
			case 1:
					echo '<tr><td>&nbsp;</td></tr><tr><td colspan="4"><b>Studenten deren Studiengänge (Prestudent <-> Student) nicht gleich sind</b></td></tr>';
					break;
			case 2:
					echo "<tr><td>&nbsp;</td></tr><tr><td colspan='4'><b>Abrecher die noch aktiv sind</b></td></td>";
					break;
			case 3:
					echo "<tr><td>&nbsp;</td></tr><tr><td colspan='4'><b>Studenten mit nicht identischen Organisationsformeinträgen (Studiengang <-> Prestudentstatus)</b></td></tr>";
					break;
			case 4:
					echo "<tr><td>&nbsp;</td></tr><tr><td colspan='4'><b>Abbrecher die wieder einen Status bekommen haben</b></td></td>"; 
					break;
			case 5:
					echo "<tr><td>&nbsp;</td></tr><tr><td colspan='4'><b>Studenten deren Semester nicht mit dem Ausbildungssemester des aktuellen Status übereinstimmt</b></td></tr>";
					break;
			case 6:
					echo "<tr><td>&nbsp;</td></tr><tr><td colspan='4'><b>Inaktive Studenten mit einem aktiven Status</b></td></tr>";
					break;
			case 7:
					echo "<tr><td>&nbsp;</td></tr><tr><td colspan='4'><b>Studenten deren Inskription im aktuellen Semester vor der letzten BIS-Meldung liegt</b></td></tr>";
					break;
			case 8:
					echo "<tr><td>&nbsp;</td></tr><tr><td colspan='4'><b>Studenten die Datum und Studiensemestern in deren Stati in falscher Reihenfolge haben</b></td></tr>";
					break;
			case 9:
					echo "<tr><td>&nbsp;</td></tr><tr><td colspan='4'><b>Aktive Studenten die keinen Status im aktuellen oder nächsten Studiensemester haben</b></td></tr>";
					break;
			case 10:
					echo "<tr><td>&nbsp;</td></tr><tr><td colspan='4'><b>Bewerberstati die keiner Organisationsform zugeordnet sind</b></td></tr>";
					break;
			case 11:
					echo "<tr><td>&nbsp;</td></tr><tr><td colspan='4'><b>Studierendenstati die keiner Organisationsform zugeordnet sind</b></td></tr>";
					break;
					
			default:
					echo "<tr><td>&nbsp;</td></tr><tr><td colspan='4'><b>Ungültiger Code</b></td></tr>";
					break;
		}

		foreach ($uid as $student_id)
		{	
			echo "<tr>"; 
			$student = new student(); 
			if($student->load($student_id))
				echo '<td>'.$student->vorname.'</td><td>'.$student->nachname.'</td><td>'.$student->uid.'</td>';
			else
				echo '<td colspan="3">'.$student_id,'</td>'; 
			echo "</tr>"; 
		}
		
	}
	echo "</table>"; 
}

//echo $text; 

/*
 * 	Gibt das Datum der BIS Meldung des übergebenen Semesters zurück
 */
function getDateForInscription ($semester)
{
	global $datum;
	
	$semesterYear = substr($semester,2,6);
	$semesterType = substr($semester,0,2); 
	
	if($semesterType == 'SS')
	{
		$date = "15.11.".($semesterYear-1); 
		$date = $datum->formatDatum($date, 'Y-m-d');
		return $date; 
	}
	
	if($semesterType == 'WS')
	{
		$date = '15.04'.$semesterYear; 
		$date = $datum->formatDatum($date, 'Y-m-d'); 
		return $date; 
	}
}
?>	
	</body>
	</html>