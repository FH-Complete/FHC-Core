<?php
// ************************************
// * Script befuellt/aktualisiert Aufgrund der Eintraege in der
// * Tabelle prestudentrolle die Tabelle studentlehrverband
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
$text='';

//Alle Studenten mit Studiensemester der Rolle holen
$qry = "SELECT 
			distinct student_uid, tbl_prestudent.prestudent_id, tbl_prestudentrolle.studiensemester_kurzbz, tbl_student.studiengang_kz
		FROM 
			public.tbl_prestudent JOIN public.tbl_student USING(prestudent_id) 
			JOIN public.tbl_prestudentrolle USING(prestudent_id) 
		ORDER BY 
			student_uid";

$text.="Studentlehrverbandeintraege mit Prestudentrollen abgleichen\n\n";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$student = new student($conn);
		$prestd = new prestudent($conn);
		//Aktuellen Status in diesem Studiensemester holen
		if(!$prestd->getLastStatus($row->prestudent_id, $row->studiensemester_kurzbz))
		{
			$text.="Fehler beim Laden des Status von $row->prestudent_id\n";
			continue;
		}
		
		//Abbrecher und Unterbrecher ins 0. Semester verschieben
		if($prestd->rolle_kurzbz=='Abbrecher')
		{
			$semester='0';
			$verband='A';
		}
		elseif($prestd->rolle_kurzbz=='Unterbrecher')
		{
			$semester='0';
			$verband='B';
		}
		else
		{ 
			$semester = $prestd->ausbildungssemester;
			$verband=' ';
		}
		
		//Keinen Eintrag erstellen fuer Semester in denen er noch kein Student war
		if(in_array($prestd->rolle_kurzbz, array('Interessent','Bewerber','Abgewiesener','Aufgenommener','Wartender')))
			continue;
			
		if($student->load_studentlehrverband($row->student_uid, $row->studiensemester_kurzbz))
		{
			$student->new = false;
			
			//Wenn der gleiche Eintrag schon vorhanden ist dann ueberspringen
			if($student->semester==$semester)
				continue;
		}
		else 
		{
			$student->new = true;
			$student->insertamum = date('Y-m-d H:i:s');
			$student->insertvon = 'auto';
			$student->verband = $verband;
			$student->gruppe = ' ';
		}
			
		$student->uid = $row->student_uid;
		$student->studiensemester_kurzbz = $row->studiensemester_kurzbz;
		$student->studiengang_kz = $row->studiengang_kz;
		$student->semester = $semester;
		$student->updateamum = date('Y-m-d H:i:s');
		$student->updatevon = 'auto';
		
		//Lehrverband anlegen falls dieser nicht existiert
		$lvb = new lehrverband($conn);
		if(!$lvb->exists($student->studiengang_kz, $student->semester, $student->verband, $student->gruppe))
		{
			$lvb->studiengang_kz = $student->studiengang_kz;
			$lvb->semester = $student->semester;
			$lvb->verband = $student->verband;
			$lvb->gruppe = $student->gruppe;
			$lvb->aktiv = true;
			
			if(!$lvb->save(true))
			{
				$text.="Fehler beim Anlegen des Lehrverbandes: ".$lvb->errormsg;
			}
		}
		
		//Zuteilung Speichern
		if($student->save_studentlehrverband())
		{
			if($student->new)
				$text.="Erstelle Studentlehrverbandeintrag für $row->student_uid im $row->studiensemester_kurzbz in Semester $student->semester\n";
			else 
				$text.="Aktualisiere Studentlehrverbandeintrag für $row->student_uid im $row->studiensemester_kurzbz in Semester $student->semester\n";
		}
		else 
		{
			$text.="Fehler bei $student->uid im $student->studiensemester_kurzbz Semester $student->semester Studiengang $student->studiengang_kz Verband '$student->verband' Gruppe '$student->gruppe'\n";
		}
	}
}
echo nl2br($text);
?>