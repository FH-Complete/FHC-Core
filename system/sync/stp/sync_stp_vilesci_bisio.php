<?php
// **************************************
// Syncronisiert alle Mobilitaetsprogramme
// StPoelten -> VILESCI
// setzt vorraus: - tbl_student
//                - tbl_nation
//                - tbl_zweck
//                - tbl_mobilitaetsprogramm
// 
// Beschreibung:
// Syncronisiert alle Eintraege aus der Tabelle StudIO nach bis.tbl_bisio
// **************************************
	require_once('sync_config.inc.php');
	require_once('../../../include/bisio.class.php');
	
	//$conn=pg_connect(CONN_STRING);
	if (!$conn_ext=mssql_connect (STPDB_SERVER, STPDB_USER, STPDB_PASSWD))
		die('Fehler beim Verbindungsaufbau!');
	mssql_select_db(STPDB_DB, $conn_ext);

	if(!$conn = pg_pconnect(CONN_STRING))
		die('Fehler beim Verbindungsaufbau!');
	
	echo '
		<html>
		<head>
			<title>STP - VILESCI (BisIO)</title>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		</head>
		<body>';
	
	echo 'Starte BisIO Syncronisation '.date('H:i:s').'<br>';
	flush();

	$gesamt=0;
	$statistik='';
	$error=0;
	$anzahl_insert=0;
	$head_text="Dies ist eine automatische Mail!\n\nFolgende Fehler sind bei der Synchronisation des BisIO aufgetreten:\n\n";
	$text='';
	
	// ******** SYNC START ********** //
		
	$qry = "SELECT __id, _person, _mobiprg, _gastland, von, bis, _zweck, chkurzbez FROM studio JOIN staat on(_gastland=__staat)";
		
	if($result_ext = mssql_query($qry, $conn_ext))
	{
		while($row_ext=mssql_fetch_object($result_ext))
		{
			$gesamt++;
			
			//Eintrag suchen
			$qry = "SELECT * FROM bis.tbl_bisio WHERE ext_id='$row_ext->__id'";
			if($result = pg_query($conn, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					$bisio_id = $row->bisio_id;
					//Kein Update - Datensatz ueberpringen
					continue;
				}
			}
			else 
			{
				$text.="Fehler beim Ermitteln der ExtID: ".pg_last_error($conn)."\n";
				$error++;
				continue;
			}
			
			//Person suchen
			$qry = "SELECT student_uid FROM public.tbl_student WHERE ext_id='$row_ext->_person'";
			if($result = pg_query($conn, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					$student_uid = $row->student_uid;
				}
				else 
				{
					$text.="Person $row_ext->_person wurde nicht gefunden\n";
					$error++;
					continue;
				}
			}
			else 
			{
				$text.="Fehler beim Ermitteln der Person: ".pg_last_error($conn)."\n";
				$error++;
				continue;
			}
			
			$qry = "SELECT * FROM bis.tbl_nation WHERE nation_code='$row_ext->chkurzbez'";
			if($result = pg_query($conn, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					//Nation ist vorhanden
				}
				else 
				{
					$text.="Nation $row_ext->chkurzbez wurde nicht gefunden\n";
					$error++;
					continue;
				}
			}
			else 
			{
				$text.="Fehler beim Ermitteln der Nation: ".pg_last_error($conn)."\n";
				$error++;
				continue;
			}
			
			$bisio = new bisio($conn);
			$bisio->mobilitaetsprogramm_code = $row_ext->_mobiprg;
			$bisio->nation_code = $row_ext->chkurzbez;
			$bisio->von = ($row_ext->von!=''?date('Y-m-d', strtotime($row_ext->von)):'');
			$bisio->bis = ($row_ext->bis!=''?date('Y-m-d', strtotime($row_ext->bis)):'');
			$bisio->zweck_code = $row_ext->_zweck;
			$bisio->student_uid = $student_uid;
			$bisio->updateamum = date('Y-m-d H:i:s');
			$bisio->updatevon = 'sync';
			$bisio->insertamum = date('Y-m-d H:i:s');
			$bisio->insertvon = 'sync';
			$bisio->ext_id = $row_ext->__id;
			
			if($bisio->save(true))
			{
				$text.="Eintrag fuer $student_uid wurde angelegt\n";
				$anzahl_insert++;
			}
			else 
			{
				$text.="Fehler beim Anlegen: $bisio->errormsg\n";
				$error++;
			}
		}
	}
	else 
		$text.= "Fehler beim Laden des Kontos\n\n";
	
	$statistik .="Gesamt: $gesamt\n";
	$statistik .="Eingefuegt: $anzahl_insert\n";
	$statistik .="Fehler: $error\n";
	
	$text = $statistik."\n\n".$text;
	//$to = 'oesi@technikum-wien.at';
	$to = $adress_ext;
	
	if(mail($to, 'SYNC BisIO',$head_text.$text, "From: nsc@fhstp.ac.at"))
		echo "Mail wurde an $to versandt<br><br>";
	else 
		echo "Fehler beim Senden an $to<br><br>";
	
	echo nl2br($text);
?>
</body>
</html>