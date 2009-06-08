<?php
// **************************************
// Syncronisiert alle Noten
// StPoelten -> VILESCI
// setzt vorraus: - tbl_lehrveranstaltung
//                - tbl_student
//				  - tbl_studiensemester
//                - tbl_note
//                - tbl_synclehrveranstaltung
// 
// Beschreibung:
// Kopiert alle Noten von Tabelle Note/SemesterplanEintrag anhand von _LV und insemester
// in die Tabelle lehre.tbl_zeugnisnote
// **************************************
	require_once('sync_config.inc.php');
	require_once('../../../include/lehrveranstaltung.class.php');
	require_once('../../../include/studiengang.class.php');
	require_once('../../../include/zeugnisnote.class.php');
	require_once('../../../include/studiensemester.class.php');
		
	//$adress='pam@technikum-wien.at';
	//$adress='oesi@technikum-wien.at';
	//$adress='ruhan@technikum-wien.at';

	//$conn=pg_connect(CONN_STRING);
	if (!$conn_ext=mssql_connect (STPDB_SERVER, STPDB_USER, STPDB_PASSWD))
		die('Fehler beim Verbindungsaufbau!');
	mssql_select_db(STPDB_DB, $conn_ext);

	if(!$conn = pg_pconnect(CONN_STRING))
		die('Fehler beim Verbindungsaufbau!');
	
	echo '
		<html>
		<head>
			<title>STP - VILESCI (Noten)</title>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		</head>
		<body>';
	
	echo 'Starte Noten Syncronisation '.date('H:i:s').'<br>';
	$beginn = date('H:i:s');
	flush();

	$update_error=0;
	$insert_error=0;
	$noten_gesamt=0;
	$error=0;
	$student_not_found=0;
	$stsem_not_found_count=0;
	$lv_not_found=0;
	$anz_update=0;
	$anz_insert=0;
	$statistik='';
	$head_text="Dies ist eine automatische Mail!\n\nFolgende Fehler sind bei der Synchronisation der Noten aufgetreten:\n\n";
	$text='';
	$stsem_not_found = array();
	
	$stsem_obj = new studiensemester($conn);
	$stsem_obj->getAll();
	$stsem_arr = array();
	
	foreach ($stsem_obj->studiensemester as $row)
		$stsem_arr[]=$row->studiensemester_kurzbz;
	
	$noten_arr = array( "1"=>"1",
						"2"=>"2",
						"3"=>"3",
						"4"=>"4",
						"5"=>"5",
						"6"=>"10",  //bestanden
						"7"=>"13",  //nicht bestanden->nicht erfolgreich absolviert
						"8"=>"6",   //befreit->angerechnet
						"9"=>"7",   //Nicht beurteilt
						"10"=>"8",  // Teilgenommen
						"18"=>"12", //mit Erfolg Teilgenommen->erfolgreich absolviert
						"11"=>"15", //mit ausgezeichnetem erfolg bestanden
						"14"=>"6",  //anerkannt->angerechnet
						"16"=>"16", //Ausland
						"17"=>"17"  // komm. Pruefung
					   ); 
	/*Fehlende Notenzuordnungen
		12	?
		13	?
	 */

	
	$stg_arr = array();
	$stg_obj = new studiengang($conn);
	$stg_obj->getAll(null, false);
	
	foreach ($stg_obj->result as $stg) 
		$stg_arr[$stg->ext_id] = $stg->studiengang_kz;

	
	// ******** SYNC START ********** //
		
	$qry = "SELECT 
				_lv, insemester, _person, _cxbeurteilungsstufe, chKalenderSem, __Note
			FROM 
				note JOIN semesterplaneintrag ON(_semesterplaneintrag=__semesterplaneintrag)
			ORDER BY _person
			";
		
	$lastperson='';
	if($result_ext = mssql_query($qry, $conn_ext))
	{
		while($row_ext=mssql_fetch_object($result_ext))
		{
			$noten_gesamt++;
			//Alle 100 Noten einen Punkt ausgeben und alle 10.000 einen Zeilenumbruch
		    if($noten_gesamt%100==0)
		    {
		    	echo '.';
		    	flush();
		    }
		    
		    if($noten_gesamt%10000==0)
		    {
		    	echo '<br>';
		    	flush();
		    }
		    
		    //Student_uid nur ermitteln wenn die person unterschiedlich zur vorigen ist
			if($lastperson!=$row_ext->_person)
			{
				$lastperson=$row_ext->_person;
				// student_uid ermitteln
				$qry_std = "SELECT student_uid FROM public.tbl_student WHERE ext_id='".addslashes($row_ext->_person)."'";
				if($result_std = pg_query($conn, $qry_std))
				{
					if(pg_num_rows($result_std)==0)
					{
						//$text.="Es wurde kein Studenteneintrag zu Person_id $row_ext->_person gefunden\n";
						$student_not_found++;
						$error++;
						$student_uid='';
						continue;
					}
					elseif(pg_num_rows($result_std)==1)
					{
						$row_std = pg_fetch_object($result_std);
						$student_uid = $row_std->student_uid;
					}
					else 
					{
						$text.="Es wurde mehr als ein passender Studenteneintrag zu $row_ext->_person gefunden\n";
						$student_uid='';
						$error++;
						continue;
					}
				}
				else 
				{
					$text.="Fehler beim Ermitteln des Studenten:".pg_last_error($conn)."\n";
					$student_uid='';
					$error++;
					continue;
				}
			}
			
			//wenn keine UID zu dieser Person gefunden wurde, dann Weiterspringen
			if($student_uid=='')
			{
				$student_not_found++;
				$error++;
				continue;
			}
						
			
			//Nicht zuordenbare Noten entfernen
			if(!array_key_exists($row_ext->_cxbeurteilungsstufe, $noten_arr))
			{
				$text.="Die Note $row_ext->_cxbeurteilungsstufe von Person $row_ext->_person kann nicht zugeordnet werden\n";
				$error++;
				continue;
			}
			
			// LV Ermitteln
			$qry_lv = "SELECT lehrveranstaltung_id FROM sync.tbl_synclehrveranstaltung WHERE lv='".addslashes($row_ext->_lv)."' AND insemester='".addslashes($row_ext->insemester)."'";
			
			if($result_lv = pg_query($conn, $qry_lv))
			{
				if(pg_num_rows($result_lv)==0)
				{
					$text.="Lehrveranstaltung zu $row_ext->_lv/$row_ext->insemester wurde nicht gefunden\n";
					$lv_not_found++;
					$error++;
					continue;
				}
				elseif(pg_num_rows($result_lv)==1)
				{
					$row_lv = pg_fetch_object($result_lv);
					
					$lehrveranstaltung_id = $row_lv->lehrveranstaltung_id;
				}
				else 
				{
					$text.="Es wurde mehr als eine passende Lehrveranstaltung zu $row_ext->lv/$row_ext->insemester gefunden\n";
					$error++;
					continue;
				}
			}
			else 
			{
				$text.="Fehler beim Ermitteln der LV:".pg_last_error($conn)."\n";
				$error++;
				continue;
			}
			
			// Studiensemester Ermitteln
			$stsem=strtoupper((substr($row_ext->chKalenderSem,0,1)).'S'.((integer)substr($row_ext->chKalenderSem,1,2)<11?'20':'19').substr($row_ext->chKalenderSem,1,2));
			if(!in_array($stsem, $stsem_arr))
			{
				if(!in_array($stsem, $stsem_not_found))
					$stsem_not_found[] = $stsem;
				$error++;
				$stsem_not_found_count++;
				continue;
			}
			
			// Nachschauen ob Note im Vilesci vorhanden ist
			$zeugnisnote = new zeugnisnote($conn);
			
			$updtext='';
			
			if($zeugnisnote->load($lehrveranstaltung_id, $student_uid, $stsem))
			{
				$zeugnisnote->new = false;
				
				if($zeugnisnote->note != $noten_arr[$row_ext->_cxbeurteilungsstufe])
				{
					$updtext.="	Note wurde bei $lehrveranstaltung_id/$student_uid/$stsem von $zeugnisnote->note auf ".$noten_arr[$row_ext->_cxbeurteilungsstufe]." geaendert\n";
				}
			}
			else 
			{
				$zeugnisnote->new = true;
				$zeugnisnote->insertamum = date('Y-m-d H:i:s');
				$zeugnisnote->insertvon = 'sync';
			}

			if($updtext!='' || $zeugnisnote->new)
			{
				//Daten Speichern
				$zeugnisnote->lehrveranstaltung_id = $lehrveranstaltung_id;
				$zeugnisnote->studiensemester_kurzbz = $stsem;
				$zeugnisnote->student_uid = $student_uid;
				$zeugnisnote->note = $noten_arr[$row_ext->_cxbeurteilungsstufe];
				$zeugnisnote->updateamum = date('Y-m-d H:i:s');
				$zeugnisnote->updatevon = 'sync';
				$zeugnisnote->ext_id = $row_ext->__Note;
	
				if($zeugnisnote->save())
				{
					if($zeugnisnote->new)
					{
						$text.="Es wurde eine neue Note: ".$noten_arr[$row_ext->_cxbeurteilungsstufe]." für Lehrveranstaltung: $lehrveranstaltung_id Student: $student_uid Studiensemester: $stsem  angelegt\n";
						$anz_insert++;
					}
					else 
					{
						$text.="Eintrag von Lehrveranstaltung: $lehrveranstaltung_id Student: $student_uid Studiensemester: $stsem wurde aktualisiert\n".$updtext;
						$anz_update++;
					}
				}
				else 
				{
					if($zeugnisnote->new)
					{
						$text.="Fehler beim Anlegen einer Neuen Note Lehrveranstaltung: $lehrveranstaltung_id Student: $student_uid Studiensemester: $stsem ".$zeugnisnote->errormsg."\n";
						$insert_error++;
						$error++;
					}
					else 
					{
						$text.="Fehler beim Aktualisieren von Lehrveranstaltung: $lehrveranstaltung_id Student: $student_uid Studiensemester: $stsem ".$zeugnisnote->errormsg."\n";
						$update_error++;
						$error++;
					}
				}
			}
		}
	}
	else 
		$text.= "Fehler beim Laden der Noten\n\n";
	
	$ende = date('H:i:s');

	$statistik .="Start: $beginn Ende: $ende\n\n";
	$statistik .="Noten Import: $noten_gesamt\n";
	$statistik .="Neue Noten angelegt: $anz_insert\n";
	$statistik .="Aktualisierte Noten: $anz_update\n";
	$statistik .="Fehler beim Anlegen von Noten: $insert_error\n";
	$statistik .="Fehler beim Aktualisieren von Noten: $update_error\n";
	$statistik .="Nicht gefundene Studenten: $student_not_found\n";
	$statistik .="Nicht gefundene LVs: $lv_not_found\n";
	$statistik .="Fehler Gesamt: $error\n";
	if(count($stsem_not_found)>0)
	{
		$statistik .="Folgende Studiensemester sind nicht in der Tabelle tbl_studiensemester vorhanden: ";
		foreach ($stsem_not_found as $row)
			$statistik.=$row.', ';
		$statistik .="\nDadurch aufgetretene Fehler: $stsem_not_found_count\n";
	}
		
	$text = $statistik."\n\n".$text;
		
	if(mail($adress, 'SYNC Noten',$head_text.$text, "From: nsc@fhstp.ac.at"))
		echo "<br>Mail wurde an $adress versandt<br><br>";
	else 
		echo "<br>Fehler beim Senden an $adress<br><br>";
	
	echo nl2br($text);
?>
</body>
</html>