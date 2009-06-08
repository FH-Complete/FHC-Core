<?php
// ************************************
// * Script befuellt/aktualisiert Aufgrund der Eintraege in der
// * Tabelle prestudentrolle die Tabelle studentlehrverband
// * Bei 50ern und 60ern wird das Semester fortlaufend vergeben
// * und nicht anhand der rolle
// **********************************
require_once('../../../vilesci/config.inc.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/student.class.php');
require_once('../../../include/prestudent.class.php');
require_once('../../../include/lehrverband.class.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Hestellen der DB Verbindung');
$text='';

//Alle Studenten mit Studiensemester der Rolle holen
$qry = "SELECT 
			distinct student_uid, tbl_prestudent.prestudent_id, tbl_prestudentstatus.studiensemester_kurzbz, tbl_student.studiengang_kz
		FROM 
			public.tbl_prestudent JOIN public.tbl_student USING(prestudent_id) 
			JOIN public.tbl_prestudentstatus USING(prestudent_id) 
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
		if($prestd->status_kurzbz=='Abbrecher')
		{
			$semester='0';
			$verband='A';
		}
		elseif($prestd->status_kurzbz=='Unterbrecher')
		{
			$semester='0';
			$verband='B';
		}
		else
		{ 
			$semester = $prestd->ausbildungssemester;
			$verband=' ';
		}
		
		//Keinen Eintrag erstellen fuer Semester in denen er noch kein Student war und
		//keinen Eintrag fuer Diplomanden und Absolventen (werden weiter unten gesondert behandelt)
		if(in_array($prestd->status_kurzbz, array('Interessent','Bewerber','Abgewiesener','Aufgenommener','Wartender', 'Diplomand','Absolvent')))
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
			$student->insertvon = 'chkstdlvbrolle';
			$student->verband = $verband;
			$student->gruppe = ' ';
		}
			
		$student->uid = $row->student_uid;
		$student->studiensemester_kurzbz = $row->studiensemester_kurzbz;
		$student->studiengang_kz = $row->studiengang_kz;
		$student->semester = $semester;
		$student->updateamum = date('Y-m-d H:i:s');
		$student->updatevon = 'chkstdlvbrolle';
		
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

$text.="\n\nAbsolventen, Diplomanden, 50er und 60er abgleichen\n\n";
//Absolventen, Diplomanden, 50er und 60er

// Bei 50ern und 60ern wird das Semester immer weitergezaehlt. In der Prestudentrolle
// steht aber weiterhin das wirkliche Semester. Daher wird zum Semester dass in der Prestudentrolle
// steht die Anzahl der Semester hinzugezaehlt in denen er schon Diplomand ist.

//Alle Studenten holen die einen Diplomanden oder Absolventeneintrag haben
$qry = "SELECT distinct student_uid, prestudent_id, studiengang_kz, verband FROM public.tbl_prestudentstatus JOIN public.tbl_student USING(prestudent_id) WHERE status_kurzbz='Diplomand' OR status_kurzbz='Absolvent'";

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		//Alle Diplomandeneintraege des Studenten holen
		$qry_diplomand = "SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id='$row->prestudent_id' AND status_kurzbz='Diplomand' ORDER BY datum";
		
		if($result_diplomand = pg_query($conn, $qry_diplomand))
		{
			$i=0;
			while($row_diplomand = pg_fetch_object($result_diplomand))
			{
				$student = new student($conn);
				$prestd = new prestudent($conn);
				
				//Aktuellen Status in diesem Studiensemester holen
				if(!$prestd->getLastStatus($row->prestudent_id, $row_diplomand->studiensemester_kurzbz))
				{
					$text.="Fehler beim Laden des Status von $row->prestudent_id\n";
					continue;
				}
				
				//Wenn Diplomand nicht der letzte Status in diesem Semester ist, dann Weiterspringen
				if($prestd->status_kurzbz!='Diplomand')
				{
					$i++;
					continue;
				}
				
				//50er und 60er rutschen immer ein Semester weiter
				$semester = $row_diplomand->ausbildungssemester+$i;
				
				//Studentlehrverband Eintrag laden
				if($student->load_studentlehrverband($row->student_uid, $row_diplomand->studiensemester_kurzbz))
				{
					$student->new = false;
					
					//Wenn der gleiche Eintrag schon vorhanden ist dann ueberspringen
					if($student->semester==$semester)
					{
						$i++;
						continue;
					}
				}
				else 
				{
					$student->new = true;
					$student->insertamum = date('Y-m-d H:i:s');
					$student->insertvon = 'chkstdlvbrolle';
					$student->verband = ' ';
					$student->gruppe = ' ';
				}
					
				$student->uid = $row->student_uid;
				$student->studiensemester_kurzbz = $row_diplomand->studiensemester_kurzbz;
				$student->studiengang_kz = $row->studiengang_kz;
				$student->semester = $semester;
				$student->updateamum = date('Y-m-d H:i:s');
				$student->updatevon = 'chkstdlvbrolle';
				
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
						$text.="Erstelle Studentlehrverbandeintrag für $row->student_uid im $row_diplomand->studiensemester_kurzbz in Semester $student->semester (Diplomand)\n";
					else 
						$text.="Aktualisiere Studentlehrverbandeintrag für $row->student_uid im $row_diplomand->studiensemester_kurzbz in Semester $student->semester (Diplomand)\n";
				}
				else 
				{
					$text.="Fehler bei $student->uid im $student->studiensemester_kurzbz Semester $student->semester Studiengang $student->studiengang_kz Verband '$student->verband' Gruppe '$student->gruppe'\n";
				}
				//Semester um eins weiterzaehlen
				$i++;
			}
			
			//Absolventen Eintrag in der Tabelle Studentlehrverband anlegen
			
			//Absolventeneintrag holen
			$qry_absolvent = "SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id='$row->prestudent_id' AND status_kurzbz='Absolvent' ORDER BY datum";
			if($result_absolvent = pg_query($conn, $qry_absolvent))
			{
				while($row_absolvent = pg_fetch_object($result_absolvent))
				{
					$student = new student($conn);

					//Der Absolventeneintrag befindet sich im gleichen Semester wie der letzte Diplomandeneintrag		
					$semester = $row_absolvent->ausbildungssemester+($i!=0?$i-1:0);
					
					//Studentlehrverband Eintrag laden
					if($student->load_studentlehrverband($row->student_uid, $row_absolvent->studiensemester_kurzbz))
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
						$student->insertvon = 'chkstdlvbrolle';
						$student->verband = $row->verband;
						$student->gruppe = ' ';
					}
						
					$student->uid = $row->student_uid;
					$student->studiensemester_kurzbz = $row_absolvent->studiensemester_kurzbz;
					$student->studiengang_kz = $row->studiengang_kz;
					$student->semester = $semester;
					$student->updateamum = date('Y-m-d H:i:s');
					$student->updatevon = 'chkstdlvbrolle';
					
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
							$text.="Erstelle Studentlehrverbandeintrag für $student->uid im $student->studiensemester_kurzbz in Semester $student->semester (Absolvent)\n";
						else 
							$text.="Aktualisiere Studentlehrverbandeintrag für $student->uid im $student->studiensemester_kurzbz in Semester $student->semester (Absolvent)\n";
					}
					else 
					{
						$text.="Fehler bei $student->uid im $student->studiensemester_kurzbz Semester $student->semester Studiengang $student->studiengang_kz Verband '$student->verband' Gruppe '$student->gruppe'\n";
					}
				}
			}
			else 
			{
				$text.="\nFehler beim Ermitteln des Absolventeneintrages:".$qry_absolvent;
			}
		}
		else 
		{
			$text.="\nFehler beim Ermitteln des Diplomandeneintrages:".$qry_diplomand;
		}
	}
}
else 
{
	$text.="\nFehler beim Ermitteln der Absolventen und Diplomanden".$qry;
}

echo nl2br($text);
?>