<?php
// **************************************
// Syncronisiert alle Studenten
// legt zusaetzlich Lehrverband und 
// Studentlehrverbandeintrag an
// StPoelten -> VILESCI
// setzt vorraus: - tbl_benutzer
//                - sync.stp_person
//                - sync.stp_stgvertiefung
//                - public.tbl_studiensemester	
// **************************************
	require_once('sync_config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/person.class.php');
	require_once('../../../include/benutzer.class.php');
	require_once('../../../include/student.class.php');
	require_once('../../../include/lehrverband.class.php');
	
	//$adress='pam@technikum-wien.at';
	//$adress='oesi@technikum-wien.at';
	//$adress='ruhan@technikum-wien.at';

	if(!$conn = pg_pconnect(CONN_STRING))
		die('Fehler beim Verbindungsaufbau!');
	
	echo '
		<html>
		<head>
			<title>STP - VILESCI (Student)</title>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		</head>
		<body>';
	
	echo 'Starte Studenten Syncronisation '.date('H:i:s').'<br>';
	flush();

	$head_text="Dies ist eine automatische Mail!\n\nFolgende Fehler sind bei der Synchronisation der Studenten aufgetreten:\n\n";
	$text='';
	$user_gesamt=0;
	$anzahl_fehler=0;
	$anzahl_update=0;
	$anzahl_insert=0;
	$anzahl_fehlender_benutzer=0;
	$anzahl_doppelte_uid=0;
	$statistik='';
		
	// ******** SYNC START ********** //
		
	$qry = "SELECT __person, studiengang_kz, instudiensemester, chusername, chmatrikelnr, chkalendersemstataend FROM sync.stp_person JOIN sync.stp_stgvertiefung ON(_stgvertiefung=__stgvertiefung) JOIN public.tbl_studiengang ON(_studiengang=ext_id) WHERE chusername<>'' AND chusername is not null AND _cxPersonTyp in(1, 2) AND chmatrikelnr!='' AND chmatrikelnr is not null";
		
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			$user_gesamt++;

			$qry_ext = "SELECT student_uid FROM public.tbl_student WHERE ext_id='$row->__person'";
			
			if($result_ext = pg_query($conn, $qry_ext))
			{
				if($row_ext = pg_fetch_object($result_ext))
				{
					$student_uid = $row_ext->student_uid;
					if($student_uid!=$row->chusername)
					{
						//Username wurde geaendert
						$qry = "UPDATE public.tbl_benutzer SET uid='$row->chusername' WHERE uid='$student_uid'";
						pg_query($conn, $qry);
						
						$text.="UID von $student_uid auf $row->chusername geaendert\n";
						$student_uid = $row->chusername;
						$anzahl_update++;
					}
				}
				else 
				{
					$student_uid='';
				}
			}
			else 
			{
				$text.="Fehler beim Ermitteln der ext_id: ".pg_last_error($conn)."\n";
				$anzahl_fehler++;
				continue;				
			}
			
			$student = new student($conn);
			
			if($student_uid!='')
			{
				if($student->load($student_uid))
				{
					$student->new = false;
				}
				else 
				{
					$text.="Fehler beim Laden eines Studenten $student_uid: ".$student->errormsg."\n";
					$anzahl_fehler++;
					continue;
				}
			}
			else 
			{
				$student->new = true;
			}
			
			//Pruefen ob der Lehrverband existiert und ggf anlegen
			$lehrverband = new lehrverband($conn);
			if(!$lehrverband->exists($row->studiengang_kz, $row->instudiensemester, '', ''))
			{
				$lehrverband->studiengang_kz = $row->studiengang_kz;
				$lehrverband->semester = $row->instudiensemester;
				$lehrverband->verband = '';
				$lehrverband->gruppe = '';
				$lehrverband->aktiv = true;
				
				if(!$lehrverband->save(true))
				{
					$text.="Fehler beim Anlegen des Lehrverbandes $row->studiengang_kz/$row->instudiensemester\n";
					$anzahl_fehler++;
					continue;
				}
			}
			
			//Pruefen ob Benutzer vorhanden ist
			$qry_ext = "SELECT * FROM public.tbl_benutzer WHERE uid='".addslashes($row->chusername)."'";
			if($result_ext = pg_query($conn, $qry_ext))
			{
				if(pg_num_rows($result_ext)==0)
				{
					$text.="Benutzer wurde nicht gefunden: $row->chusername\n";
					$anzahl_fehlender_benutzer++;
					$anzahl_fehler++;
					continue;
				}
			}
			else 
			{
				$text.="Fehler beim ermitteln des Benutzers\n";
				continue;
			}
			
			if($student_uid=='')
			{
				//Pruefen ob bereits ein Student mit dieser UID vorhanden ist
				$qry_ext = "SELECT * FROM public.tbl_student WHERE student_uid='".addslashes($row->chusername)."'";
				if($result_ext = pg_query($conn, $qry_ext))
				{
					if(pg_num_rows($result_ext)>0)
					{
						$text.="Student mit dieser uid bereits vorhanden: $row->chusername\n";
						$anzahl_doppelte_uid++;
						$anzahl_fehler++;
						continue;
					}
				}
				else 
				{
					$text.="Fehler beim Suchen nach doppelten Eintraegen\n";
					continue;
				}
			}
			
			$updtext='';
			
			if($student_uid!='')
			{
				if(trim($student->matrikelnr)!=$row->chmatrikelnr)
					$updtext.="	Matrikelnr wurde von $student->matrikelnr auf $row->chmatrikelnr geaendert\n";
				if($student->studiengang_kz!=$row->studiengang_kz)
					$updtext.="	Studiengang wurde von $student->studiengang_kz auf $row->studiengang_kz geaendert\n";
				if($student->semester != $row->instudiensemester)
					$updtext.="	Semester wurde von $student->semester auf $row->instudiensemester geaendert\n";
				if(trim($student->verband)!='')
					$updtext.=" Verband wurde von $student->verband auf '' geaendert\n";
				if(trim($student->gruppe)!='')
					$updtext.=" Gruppe wurde von $student->gruppe auf '' geaendert\n";
			}
			
			$student->uid = $row->chusername;
			$student->matrikelnr = $row->chmatrikelnr;
			$student->prestudent_id = '';
			$student->studiengang_kz = $row->studiengang_kz;
			$student->semester = $row->instudiensemester;
			$student->verband = '';
			$student->gruppe = '';
			$student->updateamum = date('Y-m-d H:i:s');
			$student->updatevon = 'sync';
			$student->insertamum = date('Y-m-d H:i:s');
			$student->insertvon = 'sync';
			$student->ext_id_student = $row->__person;
				
			if($updtext!='' || $student_uid=='')
			{	
				if($student->save(null, false))
				{
					if($student->new)
					{
						$text.="Student $student->uid wurde neu angelegt\n";
						$anzahl_insert++;
					}
					else 
					{
						$text.="Student $student->uid wurde aktualisiert\n".$updtext;
						$anzahl_update++;
					}
				}
				else 
				{
					$text.="Fehler beim Speichern: ".$student->errormsg."\n";
					$anzahl_fehler++;
					continue;
				}
			}
			if($row->chkalendersemstataend=='')
				$row->chkalendersemstataend='W07';
			
			$studiensemester=ucwords(substr($row->chkalendersemstataend,0,1)).'S'.((integer)substr($row->chkalendersemstataend,1,2)<11?'20':'19').substr($row->chkalendersemstataend,1,2);

			$student->studiensemester_kurzbz = $studiensemester;
			if($student->studentlehrverband_exists($student->uid, $student->studiensemester_kurzbz))
				$student->new = false;
			else 
				$student->new = true;
			
			if(!$student->save_studentlehrverband())
			{
				$text.="Fehler beim Speichern des Studentlehrverbandeintrages:".$student->errormsg."\n";
				$anzahl_fehler++;
			}
			else 
			{
				//$text.="Saved: $student->uid - $student->studiensemester_kurzbz\n";
			}
		}
	}
	else 
	{
		$text.= "Fehler beim Laden der Studenten\n\n";
		$anzahl_fehler++;
	}
	
	$statistik .="Anzahl Studenten: ".($user_gesamt)."\n";
	$statistik .="Anzahl der Fehler: ".($anzahl_fehler)."\n";	
	$statistik .="Anzahl Insert: $anzahl_insert\n";
	$statistik .="Anzahl Update: $anzahl_update\n";
	$statistik .="Fehlender Benutzereintrag: $anzahl_fehlender_benutzer\n";
	$statistik .="Mehrfach vorhandene UIDs: $anzahl_doppelte_uid\n";
	$statistik .="\n";
	
	$qry = "SELECT count(*) as anzahl FROM sync.stp_person WHERE chmatrikelnr='' AND _cxpersontyp in(1,2) AND chusername<>''";
	if($result = pg_query($conn, $qry))
		if($row = pg_fetch_object($result))
			$statistik .="Studenten mit Username ohne Matrikelnummer: $row->anzahl\n";
			
	$qry = "SELECT count(*) as anzahl FROM sync.stp_person WHERE chmatrikelnr<>'' AND _cxpersontyp in(1,2) AND chusername=''";
	if($result = pg_query($conn, $qry))
		if($row = pg_fetch_object($result))
			$statistik .="Studenten mit Matrikelnummer ohne Username: $row->anzahl\n";
			
	$text = $statistik."\n\n".$text;
	
	if(mail($adress, 'SYNC Studenten',$head_text.$text, "From: nsc@fhstp.ac.at"))
		echo "Mail wurde an $adress versandt<br><br>";
	else 
		echo "Fehler beim Senden an $adress<br><br>";
	
	echo nl2br($text);
?>
</body>
</html>