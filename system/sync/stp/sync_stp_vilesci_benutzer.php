<?php
// **************************************
// Syncronisiert alle Benutzer
// StPoelten -> VILESCI
// setzt vorraus: - tbl_person
//                - sync.stp_person
// **************************************
	require_once('sync_config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/person.class.php');
	require_once('../../../include/benutzer.class.php');
	
	//$adress='pam@technikum-wien.at';
	//$adress='oesi@technikum-wien.at';
	//$adress='ruhan@technikum-wien.at';

	if(!$conn = pg_pconnect(CONN_STRING))
		die('Fehler beim Verbindungsaufbau!');
	
	echo '
		<html>
		<head>
			<title>STP - VILESCI (Benutzer)</title>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		</head>
		<body>';
	
	echo 'Starte Benutzer Syncronisation '.date('H:i:s').'<br>';
	flush();

	$head_text="Dies ist eine automatische Mail!\n\nFolgende Fehler sind bei der Synchronisation der Benutzer aufgetreten:\n\n";
	$text='';
	$user_gesamt=0;
	$anzahl_fehler=0;
	$anzahl_update=0;
	$anzahl_insert=0;
	$statistik='';
		
	// ******** SYNC START ********** //
		
	$qry = "SELECT * FROM sync.stp_person WHERE chusername<>'' AND chusername is not null";
		
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			$user_gesamt++;
			
			//Schauen ob dieser Eintrag schon vorhanden ist
			$qry_ext = "SELECT * FROM public.tbl_benutzer WHERE ext_id='$row->__person'";
			
			if($result_ext = pg_query($conn, $qry_ext))
			{
				if(pg_num_rows($result_ext)>0)
				{
					if($row_ext = pg_fetch_object($result_ext))
					{
						$uid = $row_ext->uid;
						
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
				continue;
			}
			
			//Wenn der Eintrag anhand der ext_id nicht gefunden wurde, dann wird nach der UID gesucht
			//Wenn diese vorhanden ist, dann kommt eine Fehlermeldung
			if($uid=='')
			{
				$qry_ext = "SELECT * FROM public.tbl_benutzer WHERE uid='$row->chusername'";
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
			
			$benutzer = new benutzer($conn);
			
			if($uid!='')
			{
				if($benutzer->load($uid))
				{
					$benutzer->new = false;
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
				$benutzer->new = true;
				
				$qry_ext = "SELECT * FROM sync.tbl_syncperson WHERE __person='$row->__person'";
				if($result_ext = pg_query($conn, $qry_ext))
				{
					if($row_ext = pg_fetch_object($result_ext))
					{
						$benutzer->person_id = $row_ext->person_id;
					}
					else 
					{
						$text .= "Person wurde nicht gefunden: $row->__person\n";
						$anzahl_fehler++;
						continue;
					}
				}
				else 
				{
					$text .= "Fehler beim ermitteln der Person:".pg_last_error($conn)."\n";
					$anzahl_fehler++;
					continue;
				}
			}
			
			$updtext='';
			
			if($uid!='')
			{
				if($benutzer->uid!=$row->chusername)
					$updtext.="	UID wurde von $benutzer->uid auf $row->chusername geaendert\n";
				if(!$benutzer->bnaktiv)
					$updtext.="	Aktiv wurde von false auf true gesetzt\n";
			}
			
			if($benutzer->new || $updtext!='')
			{
				$benutzer->insertamum = date('Y-m-d H:i:s');
				$benutzer->insertvon = 'sync';
				$benutzer->updateamum = date('Y-m-d H:i:s');
				$benutzer->updatevon = 'sync';
				$benutzer->uid = $row->chusername;
				$benutzer->bn_ext_id = $row->__person;
				$benutzer->bnaktiv = true;
				//$benutzer->alias = '';
				
				if($benutzer->save(null, false))
				{
					if($benutzer->new)
					{
						$text.="Benutzer $benutzer->uid wurde neu angelegt\n";
						$anzahl_insert++;
					}
					else 
					{
						$text.="Benutzer $benutzer->uid wurde aktualisiert\n".$updtext;
						$anzahl_update++;
					}
				}
				else 
				{
					$text.="Fehler beim Speichern von $benutzer->uid: $benutzer->errormsg\n";
					$anzahl_fehler;
				}
			}
		}
	}
	else 
		$text.= "Fehler beim Laden der Personen\n\n";
	
	$statistik .="Anzahl der Benutzer: $user_gesamt\n";
	$statistik .="Anzahl der Fehler: $anzahl_fehler\n";	
	$statistik .="Anzahl Insert: $anzahl_insert\n";
	$statistik .="Anzahl Update: $anzahl_update\n";
		
	$text = $statistik."\n\n".$text;
	
	if(mail($adress, 'SYNC Benutzer',$head_text.$text, "From: nsc@fhstp.ac.at"))
		echo "Mail wurde an $adress versandt<br><br>";
	else 
		echo "Fehler beim Senden an $adress<br><br>";
	
	echo nl2br($text);
?>
</body>
</html>