<?php
// ************************************
// * Script zur Pruefung und Korrektur
// * moeglicher Inkonsistenzen
// *
// * - Studenten ohne Prestudent_id werden korrigiert
// * - Inkonsistenzen der Tabellen tbl_studentlehrverband, tbl_student werden korrigiert
// **********************************
require_once('../vilesci/config.inc.php');
require_once('../include/studiensemester.class.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Hestellen der DB Verbindung');
	
$anzahl_neue_prestudent_id=0;
$anzahl_fehler_prestudent=0;
$anzahl_gruppenaenderung=0;
$anzahl_gruppenaenderung_fehler=0;
$text='';
$statistik ='';

// ****
// * Bei Studenten mit fehlener Prestudent_id wird die passende id ermittelt und Eingetragen
// ****
$qry = "SELECT student_uid, studiengang_kz FROM public.tbl_student WHERE prestudent_id is null";
if($result = pg_query($conn, $qry))
{
	$text.="Suche Studenten mit fehlender Prestudent_id ...\n\n";
	
	while($row = pg_fetch_object($result))
	{
		$qry_id = "SELECT tbl_prestudent.prestudent_id FROM campus.vw_student JOIN public.tbl_prestudent USING(person_id) WHERE uid='$row->student_uid' AND tbl_prestudent.studiengang_kz='$row->studiengang_kz'";
		if($result_id = pg_query($conn, $qry_id))
		{
			if(pg_num_rows($result_id)==1)
			{
				if($row_id = pg_fetch_object($result_id))
				{
					$qry_upd = "UPDATE public.tbl_student SET prestudent_id='$row_id->prestudent_id' WHERE student_uid='$row->student_uid'";
					if(pg_query($conn, $qry_upd))
					{
						$text .= "Prestudent_id von $row->student_uid wurde auf $row_id->prestudent_id gesetzt\n";
						$anzahl_neue_prestudent_id++;
					}
				}
				else 
				{
					$text .= "unbekannter Fehler\n";
					$anzahl_fehler_prestudent++;
				}
			}
			elseif(pg_num_rows($result_id)>1)
			{
				$text .= "Student $row->student_uid hat keine Prestudent_id und MEHRERE passende Prestudenteintraege\n";
				$anzahl_fehler_prestudent++;
			}
			elseif(pg_num_rows($result_id)==0)
			{
				$text .= "Student $row->student_uid hat keine Prestudent_id und KEINE passenden Prestudenteintraege\n";
				$anzahl_fehler_prestudent++;
			}
		}
		else 
		{
			$text.="Fehler bei Abfrage:".pg_last_error($conn)."\n";
			$anzahl_fehler_prestudent++;
		}
	}
}

// *****
// * Unterschiedliche Gruppenzuteilungen in tbl_studentlehrverband - tbl_student korrigieren
// *****

$stsem = new studiensemester($conn);

$stsem = $stsem->getNearest();

$text.="\n\nKorrigiere Inkonsitenzen in den Tabellen tbl_studentlehrverband, tbl_student (Verwendetes Studiensemester: $stsem)\n\n";

$qry = "SELECT 
			tbl_student.studiengang_kz as studiengang_kz_old,
			tbl_student.semester as semester_old,
			tbl_student.verband as verband_old,
			tbl_student.gruppe as gruppe_old, 
			tbl_studentlehrverband.student_uid, 
			tbl_studentlehrverband.studiengang_kz,
			tbl_studentlehrverband.semester,
			tbl_studentlehrverband.verband,
			tbl_studentlehrverband.gruppe 
		FROM 
			public.tbl_student JOIN public.tbl_studentlehrverband USING(student_uid)
		WHERE 
			tbl_studentlehrverband.studiensemester_kurzbz='$stsem' AND
			(
				tbl_student.studiengang_kz<>tbl_studentlehrverband.studiengang_kz OR
				tbl_student.semester<>tbl_studentlehrverband.semester OR
				tbl_student.verband<>tbl_studentlehrverband.verband OR
				tbl_student.gruppe<>tbl_studentlehrverband.gruppe
			)";

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$qry = "UPDATE public.tbl_student SET studiengang_kz='$row->studiengang_kz', semester='$row->semester', verband='$row->verband', gruppe='$row->gruppe' WHERE student_uid='$row->student_uid'";
		if(pg_query($conn, $qry))
		{
			$text .= "Bei Student $row->student_uid wurde die Gruppenzuordnung von $row->studiengang_kz_old/$row->semester_old/$row->verband_old/$row->gruppe_old auf $row->studiengang_kz/$row->semester/$row->verband/$row->gruppe geaendert\n";
			$anzahl_gruppenaenderung++;
		}
		else 
		{
			$text.="Fehler beim aendern der Gruppe: ".pg_last_error($conn)."\n";
			$anzahl_gruppenaenderung_fehler++;
		}
	}
}
else 
	$text.="Fehler bei Abfrage".pg_last_error($conn);

$statistik .= "Prestudent_id wurde bei $anzahl_neue_prestudent_id Studenten korrigiert\n";
$statistik .= "$anzahl_fehler_prestudent Fehler sind bei der Korrektur der Prestudent_id aufgetreten\n";
$statistik .= "Bei $anzahl_gruppenaenderung Studenten wurde die Gruppenzuordnung korrigiert\n";
$statistik .= "$anzahl_gruppenaenderung_fehler Fehler sind bei der Korrektur der Gruppenzuordnung aufgetreten\n";
$statistik .= "\n\n";


//TODO Mailversand
echo nl2br($statistik.$text);

?>