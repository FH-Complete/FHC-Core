<?php
// **************************************
// Syncronisiert alle Mitarbeiter
// StPoelten -> VILESCI
// setzt vorraus: - tbl_benutzer
//                - tbl_ausbildung
// **************************************
	require_once('sync_config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/person.class.php');
	require_once('../../../include/benutzer.class.php');
	require_once('../../../include/mitarbeiter.class.php');
	
	//$adress='pam@technikum-wien.at';
	//$adress='oesi@technikum-wien.at';
	//$adress='ruhan@technikum-wien.at';

	if(!$conn = pg_pconnect(CONN_STRING))
		die('Fehler beim Verbindungsaufbau!');
	
	echo '
		<html>
		<head>
			<title>STP - VILESCI (Mitarbeiter)</title>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		</head>
		<body>';
	
	echo 'Starte Mitarbeiter Syncronisation '.date('H:i:s').'<br>';
	flush();

	$head_text="Dies ist eine automatische Mail!\n\nFolgende Fehler sind bei der Synchronisation der Mitarbeiter aufgetreten:\n\n";
	$text='';
	$user_gesamt=0;
	$anzahl_fehler=0;
	$anzahl_update=0;
	$anzahl_insert=0;
	$anzahl_ohne_personalnummer=0;
	$statistik='';
		
	// ******** SYNC START ********** //
		
	$qry = "SELECT * FROM sync.stp_person WHERE chusername<>'' AND chusername is not null AND _cxPersonTyp in(2,3,4)";
		
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			$user_gesamt++;
			
			//Schauen ob dieser Eintrag schon vorhanden ist
			$qry_ext = "SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE ext_id='$row->__person'";
			
			if($result_ext = pg_query($conn, $qry_ext))
			{
				if(pg_num_rows($result_ext)>0)
				{
					if($row_ext = pg_fetch_object($result_ext))
					{
						$uid = $row_ext->mitarbeiter_uid;
						
						if($uid!=$row->chusername)
						{
							//Username wurde geaendert
							$qry = "UPDATE public.tbl_benutzer SET uid='$row->chusername' WHERE uid='$uid'";
							pg_query($conn, $qry);
							
							$text.="UID von $uid auf $row->chusername geaendert\n";
							$uid = $row->chusername;
							$anzahl_update++;
						}
					}
					else 
					{
						$text.="Fehler beim Auslesen der UID fuer ext_id $row->__person\n";
						$anzahl_fehler++;
						continue;
					}
				}
				else 
				{
					$uid='';
				}
			}
			else 
			{
				$text.='Fehler beim Ermitteln der UID:'.pg_last_error($conn)."\n";
				$anzahl_fehler++;
				continue;
			}
			
			//Wenn der Eintrag anhand der ext_id nicht gefunden wurde, dann wird nach der UID gesucht
			//Wenn diese vorhanden ist, dann kommt eine Fehlermeldung
			if($uid=='')
			{
				$qry_ext = "SELECT * FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid='$row->chusername'";
				if($result_ext = pg_query($conn, $qry_ext))
				{
					if(pg_num_rows($result_ext)>0)
					{
						$text.="Der Username $row->chusername ist doppelt vergeben\n";
						$anzahl_fehler++;
						continue;
					}
				}
			}
			
			//Wenn keine Personalnummer angegeben ist -> Fehler
			if($row->personalnr=='')
			{
				$anzahl_ohne_personalnummer++;
				continue;
			}
			
			//Pruefen ob der Benutzer angelegt ist
			if($uid=='')
			{
				$qry_ext = "SELECT * FROM public.tbl_benutzer WHERE uid='$row->chusername'";
				if($result_ext = pg_query($conn, $qry_ext))
				{
					if(pg_num_rows($result_ext)==0)
					{
						$text.="Der Benutzer $row->chusername ist nicht vorhanden\n";
						$anzahl_fehler++;
						continue;
					}
				}
			}
			
			//Wenn schon ein Eintrag mit der gleichen Personalnummer vorhanden ist, dann ueberspringen
			if($uid=='')
			{
				$qry_ext = "SELECT * FROM public.tbl_mitarbeiter WHERE personalnummer='$row->personalnr'";
				if($result_ext = pg_query($conn, $qry_ext))
				{
					if(pg_num_rows($result_ext)>0)
					{
						$text.="Die Personalnummer $row->personalnr von $row->chusername ist doppelt vergeben\n";
						$anzahl_fehler++;
						continue;
					}
				}
			}
			
			$mitarbeiter = new mitarbeiter($conn);
			
			if($uid!='')
			{
				if($mitarbeiter->load($uid))
				{
					$mitarbeiter->new = false;
				}
				else 
				{
					$text.="Fehler beim Laden von $uid\n";
					$anzahl_fehler++;
					continue;
				}
			}
			else 
			{
				$mitarbeiter->new = true;
			}
			$updtext='';
			if(!$mitarbeiter->new)
			{
				if($mitarbeiter->uid != $row->chusername)
					$updtext.="	UID wird von $mitarbeiter->uid auf $row->chusername geaendert\n";
				if($mitarbeiter->ext_id_mitarbeiter != $row->__person)
					$updtext.="	ext_id wird von $mitarbeiter->ext_id_mitarbeiter auf $row->__person geaendert\n";
				if($mitarbeiter->personalnummer != $row->personalnr)
					$updtext.="	Personalnummer wird von $mitarbeiter->personalnummer auf $row->personalnr geaendert\n";
				if($mitarbeiter->telefonklappe!=$row->chklappe)
					$updtext.="	Telefonklappe wird von $mitarbeiter->telefonklappe auf $row->chklappe geaendert\n";
				if($mitarbeiter->kurzbz != (substr($row->chnachname, 0, 6).substr($row->chvorname, 0, 2)))
					$updtext.="	Kurzbz wird von $mitarbeiter->kurzbz auf ".substr($row->chnachname, 0, 6).substr($row->chvorname, 0, 2)." geaendert\n";
				if(!$mitarbeiter->lektor)
					$updtext.="	Lektor wird von false auf true gesetzt\n";
				if(!$mitarbeiter->fixangestellt)
					$updtext.="	Fixangestellt wird von false auf true gesetzt\n";
				if(!$mitarbeiter->bismelden)
					$updtext.="	Bismelden wird von false auf true gesetzt\n";
				if($mitarbeiter->stundensatz!=80)
					$updtext.="	Stundensatz wird von $mitarbeiter->stundensatz auf 80 geaendert\n";
				if($mitarbeiter->ausbildungcode!=$row->hoechsteausbildung)
					$updtext.="	Ausbildungcode wird von $mitarbeiter->ausbildungcode auf $row->hochsteausbildung geaendert\n";
				if($mitarbeiter->ort_kurzbz!='')
					$updtext.="	Ort_kurzbz wird von $mitarbeiter->ort_kurzbz auf '' geaendert\n";
				if($mitarbeiter->standort_kurzbz!='')
					$updtext.="	Standort_kurzbz wird von $mitarbeiter->standort_kurzbz auf '' geaendert\n";
				if($mitarbeiter->anmerkung !='')
					$updtext.="	Anmerkung wird von $mitarbeiter->anmerkung auf '' geaendert\n";
			}
			
			if($updtext!='' || $mitarbeiter->new)
			{
				$mitarbeiter->insertamum = date('Y-m-d H:i:s');
				$mitarbeiter->insertvon = 'sync';
				$mitarbeiter->updateamum = date('Y-m-d H:i:s');
				$mitarbeiter->updatevon = 'sync';
				$mitarbeiter->uid = $row->chusername;
				$mitarbeiter->ext_id_mitarbeiter = $row->__person;
				$mitarbeiter->personalnummer = $row->personalnr;
				$mitarbeiter->telefonklappe = $row->chklappe;
				$mitarbeiter->kurzbz = substr($row->chnachname, 0, 6).substr($row->chvorname, 0, 2);
				$mitarbeiter->lektor = true;
				$mitarbeiter->fixangestellt = true;
				$mitarbeiter->bismelden = true;
				$mitarbeiter->stundensatz = 80;
				$mitarbeiter->ausbildungcode = $row->hoechsteausbildung;
				$mitarbeiter->ort_kurzbz = '';
				$mitarbeiter->standort_kurzbz = '';
				$mitarbeiter->anmerkung = '';
				
				if($mitarbeiter->save(null, false))
				{
					if($mitarbeiter->new)
					{
						$text.="Mitarbeiter $mitarbeiter->uid wurde neu angelegt\n";
						$anzahl_insert++;
					}
					else 
					{
						$text.="Mitarbeiter $mitarbeiter->uid wurde aktualisiert\n".$updtext;
						$anzahl_update++;
					}
				}
				else 
				{
					$text.="Fehler beim Speichern von $mitarbeiter->uid: $mitarbeiter->errormsg\n";
					$anzahl_fehler;
				}
			}
		}
	}
	else 
		$text.= "Fehler beim Laden der Personen\n\n";
	
	$qry = "SELECT count(*) as anzahl FROM sync.stp_person WHERE _cxpersontyp in(2,3,4) AND chusername=''";
	$anzahl_ohne_username=0;
	if($result = pg_query($conn, $qry))
		if($row = pg_fetch_object($result))
			$anzahl_ohne_username=$row->anzahl;			

	$statistik .="Anzahl der Mitarbeiter: ".($user_gesamt+$anzahl_ohne_username)."\n";
	$statistik .="Anzahl der Fehler: ".($anzahl_fehler+$anzahl_ohne_personalnummer+$anzahl_ohne_username)."\n";	
	$statistik .="Anzahl Insert: $anzahl_insert\n";
	$statistik .="Anzahl Update: $anzahl_update\n";
	$statistik .="Mitarbeiter mit Username aber ohne Personalnummer: $anzahl_ohne_personalnummer\n";
	$statistik .="Mitarbeiter ohne Username: $anzahl_ohne_username\n";
		
	$text = $statistik."\n\n".$text;
	
	if(mail($adress, 'SYNC Mitarbeiter',$head_text.$text, "From: nsc@fhstp.ac.at"))
		echo "Mail wurde an $adress versandt<br><br>";
	else 
		echo "Fehler beim Senden an $adress<br><br>";
	
	echo nl2br($text);
?>
</body>
</html>