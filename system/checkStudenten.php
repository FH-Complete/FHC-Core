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
require_once('../include/benutzerberechtigung.class.php');

$db = new basis_db();
$datum = new datum();

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if (!$rechte->isBerechtigt('student/stammdaten', null, 's'))
	die($rechte->errormsg);

if (isset($_REQUEST['stg_kz']))
	$studiengang_kz = $_REQUEST['stg_kz'];
else
	$studiengang_kz = null;

$studiensemester = new studiensemester();
$aktSem = $studiensemester->getaktorNext();
$nextSem = $studiensemester->getNextFrom($aktSem);
$ausgabe = array();
$text = '';

echo '<!DOCTYPE HTML>
<html>
<head>
	<title>Check Studenten</title>
	<meta charset="utf-8"/>
	<link rel="stylesheet" href="../skin/vilesci.css" type="text/css">
</head>
<body>
<h1>Studenten Checkskript für BIS-Meldung</h1>';

/*
 *   	Studiengang muss beim Prestudenten und beim Studenten gleich sein
 */
$qry = "
SELECT
	stud.student_uid, pre.studiengang_kz, stud.studiengang_kz studiengang
FROM
	public.tbl_prestudent pre
	JOIN public.tbl_student stud using(prestudent_id)
WHERE
	stud.studiengang_kz != pre.studiengang_kz";

if ($studiengang_kz != '')
{
	$qry .= " AND
			(
				stud.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER)."
				OR pre.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER)."
			)";
}

$text .= "Suche Studiengänge die bei Prestudenten und Studenten nicht gleich sind ...<br><br>";

if ($db->db_query($qry))
{
	while ($row = $db->db_fetch_object())
	{
		$ausgabe[$row->studiengang][1][] = $row->student_uid;
		$text .= "Studenten-uid: ".$row->student_uid."<br>";
	}
}
else
	$text .= "Fehler bei der Abfrage aufgetreten. <br>";

/*
 * Abbrecher dürfen nicht mehr aktiv sein
 */
$text .= "<br>Suche alle Abbrecher die noch aktiv sind ... <br><br>";

$qry = "
SELECT
	pre_status.status_kurzbz, benutzer.aktiv, benutzer.uid, student.studiengang_kz studiengang
FROM
	public.tbl_prestudentstatus pre_status
	JOIN public.tbl_prestudent pre using(prestudent_id)
	JOIN public.tbl_student student using(prestudent_id)
	JOIN public.tbl_benutzer benutzer on(benutzer.uid=student.student_uid)
WHERE
	pre_status.status_kurzbz ='Abbrecher' and benutzer.aktiv=true";

if ($studiengang_kz != '')
	$qry .= " AND pre.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);

if ($db->db_query($qry))
{
	while ($row = $db->db_fetch_object())
	{
		$ausgabe[$row->studiengang][2][] = $row->uid;
		$text .= "Studenten-uid: ".$row->uid."<br>";
	}
}
else
	$text .= "Fehler bei der Abfrage aufgetreten. <br>";

/*
 *	Organisationsform eines Studienganges, sollte mit den Organisationsformen der Studenten übereinstimmen
 */

$text .= "<br>Suche Studenten mit ungleichen Organisationsformeinträgen
		(Studiengang <--> Prestudentstatus) ... <br><br>";

$orgArray = array();
$orgForm = new organisationsform();

$qry = "
SELECT
	studiengang.orgform_kurzbz as studorgkz, student.student_uid,
	prestudentstatus.orgform_kurzbz as studentorgkz, student.studiengang_kz studiengang
FROM
	public.tbl_studiengang studiengang
	JOIN public.tbl_student student using(studiengang_kz)
	JOIN public.tbl_prestudent prestudent using(prestudent_id)
	JOIN public.tbl_prestudentstatus prestudentstatus using(prestudent_id)
	JOIN public.tbl_benutzer benutzer on(benutzer.uid = student.student_uid)
WHERE
	benutzer.aktiv = true
	AND prestudentstatus.status_kurzbz='Student'
	AND studiengang.studiengang_kz < 10000
	AND prestudentstatus.studiensemester_kurzbz = ".$db->db_add_param($aktSem)."
	AND NOT EXISTS(
		SELECT 1 FROM lehre.tbl_studienplan JOIN lehre.tbl_studienordnung USING(studienordnung_id)
		WHERE
			tbl_studienordnung.studiengang_kz = prestudent.studiengang_kz
			AND tbl_studienplan.orgform_kurzbz = prestudentstatus.orgform_kurzbz)";

if ($studiengang_kz != '')
	$qry .= " AND prestudent.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);
$qry .= " ORDER BY student_uid";

if ($db->db_query($qry))
{
	while ($row = $db->db_fetch_object())
	{
		$studOrgform = $row->studorgkz;
		$student_uid = $row->student_uid;
		$studentOrgform = $row->studentorgkz;

		$orgArray = $orgForm->checkOrgForm($studOrgform);
		if (is_array($orgArray))
		{
			if (!in_array($studentOrgform, $orgArray))
			{
				$ausgabe[$row->studiengang][3][] = $row->student_uid;
				$text .= "Student_uid: $student_uid <br>";
			}
		}
	}
}
else
	$text .= "Fehler bei der Abfrage aufgetreten. <br>";


/*
 * Abbrecher dürfen nicht wieder einen Status bekommen
 */
$prestudentAbbrecher = new prestudent();
$prestudentLast = new prestudent();
$text .= "<br>Suche alle Abbrecher die wieder einen Status bekommen haben...<br><br>";

$qry = "
SELECT
	student.student_uid, prestudent.prestudent_id, student.studiengang_kz studiengang
FROM
	public.tbl_student student
	JOIN public.tbl_prestudent prestudent using(prestudent_id)
	JOIN public.tbl_prestudentstatus prestatus using(prestudent_id)
WHERE
	prestatus.status_kurzbz = 'Abbrecher'";

if ($studiengang_kz != '')
	$qry .= " AND prestudent.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);

if ($db->db_query($qry))
{
	while ($row = $db->db_fetch_object())
	{
		$student_uid = $row->student_uid;
		$prestudent_id = $row->prestudent_id;

		$prestudentLast->result = array();

		$prestudentLast->getLastStatus($prestudent_id);

		if ($prestudentLast->status_kurzbz != 'Abbrecher')
		{
			$ausgabe[$row->studiengang][4][] = $student_uid;
			$text .= "Studenten-uid: ".$student_uid."<br>";
		}
	}
}


/*
 * 	Aktuelles Semester beim Studenten stimmt nicht mit dem Ausbildungssemester des aktuellen Status überein
 */

$text .= "<br><br>Suche Studenten deren Semstern nicht mit dem
Ausbildungssemesters des aktuellen Status übereinstimmt ... <br><br>";

$student = new student();
$prestudent = new prestudent();
$qry = "
SELECT
	distinct(student.student_uid), prestudent.prestudent_id, status.ausbildungssemester,
	lv.semester, student.studiengang_kz studiengang
FROM
	public.tbl_student student
	JOIN public.tbl_studentlehrverband lv using(student_uid)
	JOIN public.tbl_prestudent prestudent using(prestudent_id)
	JOIN public.tbl_prestudentstatus status using(prestudent_id)
WHERE
	status.studiensemester_kurzbz = ".$db->db_add_param($aktSem)."
	AND lv.studiensemester_kurzbz = ".$db->db_add_param($aktSem)."
	AND status.status_kurzbz NOT IN ('Interessent','Bewerber','Aufgenommener','Wartender','Abgewiesener','Unterbrecher')
	AND get_rolle_prestudent (prestudent_id, ".$db->db_add_param($aktSem).")='Student'
	AND status.ausbildungssemester != lv.semester";

if ($studiengang_kz != '')
	$qry .= " AND prestudent.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);

if ($db->db_query($qry))
{
	while ($row = $db->db_fetch_object())
	{
		$student_uid = $row->student_uid;

		$ausgabe[$row->studiengang][5][] = $student_uid;
		$text .= "Studenten-uid: ".$student_uid."<br>";
	}
}


/*
 * Inaktive Studenten sollen keinen "aktiven" Status haben (Diplomant, Student, Unterbrecher, Praktikant)
 */

$text .= "<br><br>Suche alle inaktiven Studenten mit einem aktiven Status ... <br><br>";

$qry = "
SELECT
	distinct(student.student_uid), student.studiengang_kz studiengang
FROM
	public.tbl_benutzer benutzer
	JOIN public.tbl_student student on(benutzer.uid = student.student_uid)
	JOIN public.tbl_prestudent prestudent using(prestudent_id)
WHERE
	benutzer.aktiv=false
	AND get_rolle_prestudent(prestudent_id, ".$db->db_add_param($aktSem).")
		in ('Student', 'Diplomand', 'Unterbrecher', 'Praktikant')";

if ($studiengang_kz != '')
	$qry .= " AND prestudent.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);

if ($db->db_query($qry))
{
	while ($row = $db->db_fetch_object())
	{
		$ausgabe[$row->studiengang][6][] = $row->student_uid;
		$text .= "Studenten-uid: ".$row->student_uid."<br>";
	}
}

/*
 * 	Das Datum der Inskription darf nicht vor der letzten BIS-Meldung liegen
 * 	zB. Wenn Student im WS2009 studiert darf Studentenstatus nicht vor 15.4.2009 liegen
 * 	zB. Wenn Student im SS2010 studiert darf Studentenstatus nicht vor 15.11.2009 liegen
 */

$text .= "<br><br>Suche alle Studenten deren Inskription im aktuellen
Semester vor der letzten BIS-Meldung liegt ...<br><br>";

$qry = "
SELECT
	distinct(student.student_uid), prestudent.prestudent_id, student.studiengang_kz studiengang
FROM
	public.tbl_benutzer benutzer
	JOIN public.tbl_student student on(benutzer.uid = student.student_uid)
	JOIN public.tbl_prestudent prestudent using(prestudent_id)
	JOIN public.tbl_prestudentstatus prestatus using(prestudent_id)
WHERE
	benutzer.aktiv=true";

if ($studiengang_kz != '')
	$qry .= " AND prestudent.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);

if ($db->db_query($qry))
{
	while ($row = $db->db_fetch_object())
	{
		$prestudent = new prestudent();

		$prestudent->getFirstStatus($row->prestudent_id, 'Student');
		// wenn Student im aktuellen Semester zum ersten Mal den Status Student bekommt
		if ($prestudent->studiensemester_kurzbz == $aktSem)
		{
			$datumBIS = getDateForInscription($aktSem);
			$datumInscription = $datum->formatDatum($prestudent->datum, 'Y-m-d');

			// Wenn Inscriptionsdatum vor der letzten BIS Meldung liegt
			if ($datumInscription < $datumBIS)
			{
				$ausgabe[$row->studiengang][7][] = $row->student_uid;
				$text .= $row->student_uid." Inskribiert am: ".$datumInscription." BIS Meldung: ".$datumBIS."<br>";
			}
		}
	}
}


/*
 *	Datum und Studiensemester bei den Stati sind in falscher Reihenfolge
 */

$text .= "<br><br>Suche alle Studenten die Datum und Studiensemester
in deren Stati in falscher Reihenfolge haben ...<br><br>";
$prestudentFirst = new prestudent();
$prestudentSecond = new prestudent();
$i = 0;

// alle aktiven Studenten die im aktuellen Semster den Status Student haben
$qry_student = "
SELECT
	distinct(student_uid), prestudent.prestudent_id, student.studiengang_kz studiengang
FROM
	public.tbl_student student
	JOIN public.tbl_benutzer benutzer on(student.student_uid = benutzer.uid)
	JOIN public.tbl_prestudent prestudent using(prestudent_id)
	JOIN public.tbl_prestudentstatus status using(prestudent_id)
WHERE
	benutzer.aktiv=true
	AND status.status_kurzbz='Student'
	AND status.studiensemester_kurzbz=".$db->db_add_param($aktSem);

if ($studiengang_kz != '')
	$qry .= " AND prestudent.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);

if ($result = $db->db_query($qry_student))
{
	while ($student = $db->db_fetch_object($result))
	{
		$qry_orderSemester = "
		SELECT
			status.*
		FROM
			public.tbl_prestudentstatus status
			JOIN public.tbl_studiensemester semester using(studiensemester_kurzbz)
		WHERE
			prestudent_id = ".$db->db_add_param($student->prestudent_id, FHC_INTEGER)."
		ORDER BY semester.start DESC, status.datum DESC;";

		if ($result1 = $db->db_query($qry_orderSemester))
		{
			$prestudentSecond->result = array();
			$prestudentFirst->result = array();
			while ($row = $db->db_fetch_object($result1))
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
			$text .= "Fehler";

		if (!$prestudentSecond->getPrestudentRolle($student->prestudent_id, null, null, 'Datum DESC, insertamum DESC'))
			$text .= "ERROR:".$prestudentSecond->errormsg;

		$anzahl_stati = count($prestudentFirst->result);
		for ($i = 0; $i < $anzahl_stati; $i++)
		{
			if ($prestudentFirst->result[$i]->studiensemester_kurzbz
				!= $prestudentSecond->result[$i]->studiensemester_kurzbz)
			{
				$ausgabe[$student->studiengang][8][] = $student->student_uid;
				$text .= "Studenten-uid: ".$student->student_uid."<br>";
				continue 2;
			}
		}
	}
}

/*
 *	 Aktive Studenten ohne Status in aktuellen Studiensemester
 */

$prestudent = new prestudent();
$text .= "<br><br>Suche alle aktiven Studenten die keinen Status im aktuellen Studiensemester haben.<br><br>";

$qry = "
SELECT
	distinct (student_uid), prestudent.prestudent_id, student.studiengang_kz studiengang
FROM
	public.tbl_student student
	JOIN public.tbl_benutzer benutzer on (benutzer.uid = student.student_uid)
	JOIN public.tbl_prestudent prestudent using(prestudent_id)
	JOIN public.tbl_prestudentstatus status using(prestudent_id)
WHERE
	benutzer.aktiv=true";

if ($studiengang_kz != '')
	$qry .= " AND prestudent.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);

if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
	{
		if (!$prestudent->getLastStatus($row->prestudent_id, $aktSem)
		&& !$prestudent->getLastStatus($row->prestudent_id, $nextSem))
		{
			$ausgabe[$row->studiengang][9][] = $row->student_uid;
			$text .= $row->student_uid."<br>";
		}
	}
}

/*
 *	 Bewerber im aktuellen StSem die in Mischformstudiengängen keine Orgform eingetragen haben
 */
$text .= "<br><br>Suche alle Bewerber die keine Orgform eingetragen haben.<br><br>";

$qry = "
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

if ($studiengang_kz != '')
	$qry .= " AND tbl_prestudent.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);

if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
	{
		$ausgabe[$row->studiengang][10][] = $row->vorname.' '.$row->nachname.' '.$row->prestudent_id;
		$text .= $row->vorname.' '.$row->nachname.' '.$row->prestudent_id."<br>";
	}
}

/*
 *	 Studierende im aktuellen StSem die in Mischformstudiengängen keine Orgform eingetragen haben
 */
$text .= "<br><br>Suche alle Studierenden die keine Orgform eingetragen haben.<br><br>";

$qry = "
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

if ($studiengang_kz != '')
	$qry .= " AND tbl_prestudent.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);

if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
	{
		$ausgabe[$row->studiengang][11][] = $row->vorname.' '.$row->nachname.' '.$row->prestudent_id;
		$text .= $row->vorname.' '.$row->nachname.' '.$row->prestudent_id."<br>";
	}
}

/*
 * Studiengang des Prestudenten passt nicht zu Studiengang des Studienplans
 */
$text .= "<br><br>Suche alle bei denen Studiengang des Prestudenten
nicht zum Studiengang des Studienplans passt<br><br>";

$qry = "
SELECT
	distinct tbl_person.vorname, tbl_person.nachname,
	tbl_prestudent.studiengang_kz as studiengang,
	tbl_prestudent.prestudent_id
FROM
	public.tbl_prestudent
	JOIN public.tbl_prestudentstatus USING(prestudent_id)
	JOIN lehre.tbl_studienplan USING(studienplan_id)
	JOIN lehre.tbl_studienordnung USING(studienordnung_id)
	JOIN public.tbl_person USING(person_id)
WHERE
	tbl_prestudent.studiengang_kz<>tbl_studienordnung.studiengang_kz
";

if ($studiengang_kz != '')
	$qry .= " AND tbl_prestudent.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);

if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
	{
		$ausgabe[$row->studiengang][12][] = $row->vorname.' '.$row->nachname.' '.$row->prestudent_id;
		$text .= $row->vorname.' '.$row->nachname.' '.$row->prestudent_id."<br>";
	}
}

/*
 * Studienplan ist nicht gueltig
 */
$text .= "<br><br>Studienplan ist im gewaehlten Ausbildungssemester nicht gueltig<br><br>";

$qry = "
SELECT
	distinct tbl_person.vorname, tbl_person.nachname,
	tbl_prestudent.prestudent_id,
	tbl_studienplan.bezeichnung,
	tbl_prestudent.studiengang_kz as studiengang,
	tbl_prestudentstatus.status_kurzbz,
	tbl_prestudentstatus.studiensemester_kurzbz,
	tbl_prestudentstatus.ausbildungssemester
FROM
	public.tbl_prestudent
	JOIN public.tbl_prestudentstatus USING(prestudent_id)
	JOIN public.tbl_person USING(person_id)
	JOIN lehre.tbl_studienplan USING(studienplan_id)
WHERE
	status_kurzbz in('Student', 'Interessent','Bewerber','Aufgenommener')
	AND NOT EXISTS (
		SELECT
			1
		FROM
			lehre.tbl_studienplan_semester
		WHERE
			studienplan_id=tbl_prestudentstatus.studienplan_id
			AND tbl_studienplan_semester.semester = tbl_prestudentstatus.ausbildungssemester
			AND tbl_studienplan_semester.studiensemester_kurzbz = Tbl_prestudentstatus.studiensemester_kurzbz
		)
	AND tbl_prestudentstatus.studiensemester_kurzbz=".$db->db_add_param($aktSem);

if ($studiengang_kz != '')
	$qry .= " AND tbl_prestudent.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);

if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
	{
		$ausgabe[$row->studiengang][13][] = $row->vorname.' '.$row->nachname.
			' ('.$row->studiensemester_kurzbz.' '.$row->status_kurzbz.' '.$row->ausbildungssemester.'.Sem) Studienplan:'.$row->bezeichnung;
		$text .= $row->vorname.' '.$row->nachname.
			' ('.$row->studiensemester_kurzbz.' '.$row->status_kurzbz.' '.$row->ausbildungssemester.".Sem) Studienplan:'.$row->bezeichnung.'<br>";
	}
}

/*
 * Aktive Studierende ohne Matrikelnummer
 */
$text .= "<br><br>Studierender hat keine Matrikelnummer<br><br>";

$qry = "
SELECT
	distinct on (tbl_person.person_id)
	tbl_student.student_uid,
	tbl_prestudent.prestudent_id,
	tbl_prestudent.studiengang_kz as studiengang
FROM
	public.tbl_prestudent
	JOIN public.tbl_prestudentstatus USING(prestudent_id)
	JOIN public.tbl_person USING(person_id)
	JOIN public.tbl_student USING(prestudent_id)
	JOIN public.tbl_benutzer ON(tbl_student.student_uid=tbl_benutzer.uid)
WHERE
	status_kurzbz in('Student', 'Diplomand', 'Absolvent', 'Abbrecher')
	AND tbl_prestudent.bismelden
	AND tbl_benutzer.aktiv
	AND (tbl_person.matr_nr is null OR tbl_person.matr_nr = '')
	AND tbl_prestudentstatus.studiensemester_kurzbz=".$db->db_add_param($aktSem);

if ($studiengang_kz != '')
	$qry .= " AND tbl_prestudent.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);

if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
	{
		$ausgabe[$row->studiengang][14][] = $row->student_uid;
	}
}

/*
 * Bewerber die nicht zum Reihungstest angetreten gesetzt sind
 */
$text .= "<br><br>Bewerber aber kein ReihungstestAngetreten gesetzt<br><br>";
$lastSem = $studiensemester->getPreviousFrom($aktSem);
$qry="SELECT vorname, nachname, tbl_prestudent.prestudent_id, studiengang_kz FROM public.tbl_prestudent
	JOIN public.tbl_prestudentstatus ON(tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id)
	JOIN public.tbl_person USING(person_id)
	LEFT JOIN bis.tbl_orgform USING(orgform_kurzbz)
	WHERE (studiensemester_kurzbz=".$db->db_add_param($aktSem)." OR studiensemester_kurzbz=".$db->db_add_param($lastSem).")
	AND tbl_prestudent.studiengang_kz=".$db->db_add_param($studiengang_kz)."
	AND status_kurzbz='Bewerber' AND reihungstestangetreten=false
	";
if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
	{
		$ausgabe[$row->studiengang_kz][15][] = $row->vorname.' '.$row->nachname.
			' (Prestudent ID: '.$row->prestudent_id.')';
		$text .= $row->vorname.' '.$row->nachname.
			' ('.$row->prestudent_id.')';
	}
}

/*
 * Nation der Adresse ist ungleich Österreich, die Gemeinde ist aber in der Gemeinde Tabelle enthalten
 */
$text .= "<br><br>Adressnation ausserhalb Österreich mit Gemeinde in Gemeindetabelle<br><br>";
$lastSem = $studiensemester->getPreviousFrom($aktSem);
$qry="SELECT tbl_person.vorname, tbl_person.nachname, tbl_prestudent.studiengang_kz, tbl_student.student_uid
FROM
	public.tbl_adresse
	JOIN public.tbl_prestudent USING(person_id)
	JOIN public.tbl_person USING(person_id)
	JOIN public.tbl_student USING(prestudent_id)
	JOIN public.tbl_benutzer ON(uid=student_uid)
WHERE
	tbl_adresse.nation!='A'
	AND tbl_benutzer.aktiv
	AND gemeinde NOT IN ('Münster')
	AND EXISTS(SELECT 1 FROM bis.tbl_gemeinde WHERE name = tbl_adresse.gemeinde)
ORDER BY tbl_prestudent.studiengang_kz, tbl_person.nachname
";

if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
	{
		$ausgabe[$row->studiengang_kz][16][] = $row->vorname.' '.$row->nachname.
			' ('.$row->student_uid.')';
		$text .= $row->vorname.' '.$row->nachname.
			' ('.$row->student_uid.')';
	}
}

/*
 *	 Personen ohne Abschlussstatus
 */
$text .= "<br><br>Suche Personen ohne Abschlussstatus.<br><br>";

$qry = "
SELECT
	distinct tbl_prestudent.prestudent_id, tbl_person.vorname, tbl_person.nachname, tbl_prestudent.studiengang_kz as studiengang
FROM
	public.tbl_prestudent
	JOIN public.tbl_person USING(person_id)
WHERE
	NOT EXISTS(
		SELECT
			1
		FROM
			public.tbl_prestudentstatus ps
			JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
		WHERE
			prestudent_id=tbl_prestudent.prestudent_id
			AND tbl_studiensemester.ende>now()
	)
	AND '2018-01-01'<(SELECT max(datum) FROM public.tbl_prestudentstatus WHERE prestudent_id=tbl_prestudent.prestudent_id)
	AND NOT EXISTS(SELECT 1 FROM public.tbl_prestudentstatus ps
		WHERE
			prestudent_id=tbl_prestudent.prestudent_id
			AND status_kurzbz IN('Abbrecher','Abgewiesener','Absolvent','Incoming')
	)";

if ($studiengang_kz != '')
	$qry .= " AND tbl_prestudent.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);

if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
	{
		$ausgabe[$row->studiengang][17][] = $row->vorname.' '.$row->nachname.' (PreStudent '.$row->prestudent_id.')';
		$text .= $row->vorname.' '.$row->nachname.' (PreStudent '.$row->prestudent_id.")<br>";
	}
}


// Ausgabe der Studenten
foreach ($ausgabe as $stg_kz => $value)
{
	//Wenn eine Studiengangskennzahl uebergeben wird, nur diese anzeigen und die anderen ueberspringen
	if ($studiengang_kz != $stg_kz)
		continue;

	$studiengang = new studiengang();
	$studiengang->load($stg_kz);

	echo "<h2>".$studiengang->bezeichnung." (".$studiengang->kurzbzlang.")</h2>";
	echo "<table border='0'>";
	foreach ($value as $code => $uid)
	{
		switch ($code) {
			case 1:
				echo '
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="4">
							<b>Studenten deren Studiengänge (Prestudent <-> Student) nicht gleich sind</b>
						</td>
					</tr>';
				break;
			case 2:
				echo "
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='4'><b>Abbrecher die noch aktiv sind</b></td>
					</tr>";
				break;
			case 3:
				echo "
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='4'>
							<b>Studenten mit nicht identischen Organisationsformeinträgen
							(Studiengang <-> Prestudentstatus)</b>
						</td>
					</tr>";
				break;
			case 4:
				echo "
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='4'><b>Abbrecher die wieder einen Status bekommen haben</b></td>
					</tr>";
				break;
			case 5:
				echo "
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='4'>
							<b>Studenten deren Semester nicht mit dem Ausbildungssemester
							des aktuellen Status übereinstimmt</b>
						</td>
					</tr>";
				break;
			case 6:
				echo "
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='4'><b>Inaktive Studenten mit einem aktiven Status</b></td>
					</tr>";
				break;
			case 7:
				echo "
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='4'>
							<b>Studenten deren Inskription im aktuellen Semester
							vor der letzten BIS-Meldung liegt</b>
						</td>
					</tr>";
				break;
			case 8:
				echo "
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='4'>
							<b>Studenten die Datum und Studiensemester in deren Status
							in falscher Reihenfolge haben</b>
						</td>
					</tr>";
				break;
			case 9:
				echo "
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='4'>
							<b>Aktive Studenten die keinen Status im aktuellen
							oder nächsten Studiensemester haben</b>
						</td>
					</tr>";
				break;
			case 10:
				echo "
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='4'><b>Bewerberstati die keiner Organisationsform zugeordnet sind</b></td>
					</tr>";
				break;
			case 11:
				echo "
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='4'><b>Studierendenstati die keiner Organisationsform zugeordnet sind</b></td>
					</tr>";
				break;
			case 12:
				echo "
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='4'><b>Studienplan passt nicht zum Studiengang des Studierenden</b></td>
					</tr>";
				break;
			case 13:
				echo "
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='4'><b>Studienplan ist in diesem Semester nicht gültig (nicht BIS relevant)</b></td>
					</tr>";
				break;
			case 14:
				echo "
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='4'><b>Aktive Studierende ohne Matrikelnummer</b></td>
					</tr>";
				break;
			case 15:
				echo "
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='4'><b>Folgende Personen wurden zu Bewerbern gemacht, sind aber nicht zum Reihungstest angetreten.</b></td>
					</tr>";
				break;
			case 16:
				echo "
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='4'><b>Folgende Personen haben eine Adresse mit einer Nation <b>außerhalb</b> Österreichs, die Gemeinde liegt aber <b>in</b> Österreich</b></td>
					</tr>";
				break;
			case 17:
				echo "
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='4'><b>Folgende Personen haben keinen Endstatus (Absolvent, Abbrecher oder Abgewiesener) (nicht BIS relevant)</b></td>
					</tr>";
				break;
			default:
				echo "<tr><td>&nbsp;</td></tr><tr><td colspan='4'><b>Ungültiger Code</b></td></tr>";
				break;
		}

		foreach ($uid as $student_id)
		{
			echo "<tr>";
			$student = new student();
			if ($student->load($student_id))
				echo '<td>'.$student->vorname.'</td><td>'.$student->nachname.'</td><td>'.$student->uid.'</td>';
			else
				echo '<td colspan="3">'.$student_id.'</td>';
			echo "</tr>";
		}
	}
	echo "</table>";
}

/**
 * 	Gibt das Datum der BIS Meldung des übergebenen Semesters zurück
 * @param string $semester Studiensemester_kurzbz.
 * @return Datum der BIS-Meldung des uebergebeben Semesters.
 */
function getDateForInscription($semester)
{
	global $datum;

	$semesterYear = substr($semester, 2, 6);
	$semesterType = substr($semester, 0, 2);

	if ($semesterType == 'SS')
	{
		$date = "15.11.".($semesterYear - 1);
		$date = $datum->formatDatum($date, 'Y-m-d');
		return $date;
	}

	if ($semesterType == 'WS')
	{
		$date = '15.04'.$semesterYear;
		$date = $datum->formatDatum($date, 'Y-m-d');
		return $date;
	}
}
echo '
	</body>
</html>';
