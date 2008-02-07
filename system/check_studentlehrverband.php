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
require_once('../include/person.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/student.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/lehrverband.class.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Hestellen der DB Verbindung');
	
$anzahl_neue_prestudent_id=0;
$anzahl_fehler_prestudent=0;
$anzahl_gruppenaenderung=0;
$anzahl_gruppenaenderung_fehler=0;
$text='';
$statistik ='';
$abunterbrecher_verschoben_error=0;
$abunterbrecher_verschoben=0;

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
// * Gruppenzuteilung von Abbrechern und Unterbrechern korrigieren.
// * Abbrecher werden in die Gruppe 0A verschoben
// * Unterbrecher in die Gruppe 0B
// *****
$text.="\n\nKorrigiere Gruppenzuteilungen von Ab-/Unterbrechern\n";

//Alle Ab-/Unterbrecher holen die nicht im 0. Semester sind
$qry = "SELECT 
			student_uid,
			tbl_student.studiengang_kz,
			tbl_prestudent.prestudent_id,
			rolle_kurzbz,
			studiensemester_kurzbz
		FROM
			public.tbl_student,
			public.tbl_prestudent,
			public.tbl_prestudentrolle
		WHERE
			tbl_student.prestudent_id=tbl_prestudent.prestudent_id AND
			tbl_prestudent.prestudent_id=tbl_prestudentrolle.prestudent_id AND
			(
				tbl_prestudentrolle.rolle_kurzbz='Unterbrecher' OR 
				tbl_prestudentrolle.rolle_kurzbz='Abbrecher'
			)
			AND
			EXISTS (SELECT 
						* 
					FROM 
						public.tbl_studentlehrverband 
					WHERE 
			        	student_uid=tbl_student.student_uid AND 
			        	studiensemester_kurzbz=tbl_prestudentrolle.studiensemester_kurzbz AND 
			        	semester<>0
			        )
		";

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		//Eintrag nur korrigieren wenn der Abbrecher/Unterbrecher Status der letzte in diesem Studiensemester ist
		$prestd = new prestudent($conn);
		$prestd->getLastStatus($row->prestudent_id, $row->studiensemester_kurzbz);
		
		if($prestd->rolle_kurzbz=='Unterbrecher' || $prestd->rolle_kurzbz=='Abbrecher')
		{
			//Studentlehrverbandeintrag aktualisieren
			$student = new student($conn);
			if($student->studentlehrverband_exists($row->student_uid, $row->studiensemester_kurzbz))
				$student->new = false;
			else 
			{
				$student->new = true;
				$student->insertamum = date('Y-m-d H:i:s');
				$student->insertvon = 'chkstudentlvb';
			}
				
			$student->uid = $row->student_uid;
			$student->studiensemester_kurzbz=$row->studiensemester_kurzbz;
			$student->studiengang_kz = $row->studiengang_kz;
			$student->semester = '0';
			$student->verband = ($prestd->rolle_kurzbz=='Unterbrecher'?'B':'A');
			$student->gruppe = ' ';
			$student->updateamum = date('Y-m-d H:i:s');
			$student->updatevon = 'chkstudentlvb';
			
			//Pruefen ob der Lehrverband exisitert, wenn nicht dann wird er angelegt
			$lehrverband = new lehrverband($conn);
			if(!$lehrverband->exists($student->studiengang_kz, $student->semester, $student->verband, $student->gruppe))
			{
				$lehrverband->studiengang_kz = $student->studiengang_kz;
				$lehrverband->semester = $student->semester;
				$lehrverband->verband = $student->verband;
				$lehrverband->gruppe = $student->gruppe;
				$lehrverband->bezeichnung = ($student->verband=='A'?'Abbrecher':'Unterbrecher');
								
				$lehrverband->save(true);		
			}
			
			if($student->save_studentlehrverband())
			{
				$text.="Student $student->uid wurde im $row->studiensemester_kurzbz in die Gruppe $student->semester$student->verband verschoben\n";
				$abunterbrecher_verschoben++;
			}
			else 
			{
				$text.="Fehler biem Speichern des Lehrverbandeintrages bei $student->student_uid:".$student->errormsg."\n";
				$abunterbrecher_verschoben_error++;
			}
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
			$text.="Fehler beim Aendern der Gruppe: ".pg_last_error($conn)."\n";
			$anzahl_gruppenaenderung_fehler++;
		}
	}
}
else 
	$text.="Fehler bei Abfrage".pg_last_error($conn);

$statistik .= "Prestudent_id wurde bei $anzahl_neue_prestudent_id Studenten korrigiert\n";
$statistik .= "$anzahl_fehler_prestudent Fehler sind bei der Korrektur der Prestudent_id aufgetreten\n";
$statistik .= "$abunterbrecher_verschoben Studenten wurden ins 0. Semester verschoben\n ";
$statistik .= "$abunterbrecher_verschoben_error Fehler sind beim Verschieben aufgetreten\n ";
$statistik .= "Bei $anzahl_gruppenaenderung Studenten wurde die Gruppenzuordnung korrigiert\n";
$statistik .= "$anzahl_gruppenaenderung_fehler Fehler sind bei der Korrektur der Gruppenzuordnung aufgetreten\n";
$statistik .= "\n\n";

if(mail(MAIL_ADMIN, 'CHECK Studentlehrverband', $statistik.$text, "From: vilesci@technikum-wien.at"))
	echo 'Mail an '.MAIL_ADMIN.' wurde versandt';
else 
	echo 'Fehler beim Versenden des Mails an '.MAIL_ADMIN;

echo nl2br("\n\n".$statistik.$text);

?>