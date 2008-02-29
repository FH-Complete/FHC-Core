<?php
// **************************************
// Syncronisiert alle Studiengebuehren
// StPoelten -> VILESCI
// setzt vorraus: - tbl_person
//                - tbl_studiengang
//                - tbl_studiensemester
//                - tbl_buchungstyp
//
// Beschreibung:
// Syncronisert die Studiengebuehren aus der Tabelle Studiengebuehren in
// die Tabelle public.tbl_konto. Dabei wird zuerst die Belastung mit der
// negativen Studiengebuehr angelegt und danach die dazupassende Gegenbuchung.
// **************************************
	require_once('sync_config.inc.php');
	require_once('../../../include/studiengang.class.php');
	require_once('../../../include/konto.class.php');
	require_once('../../../include/functions.inc.php');

	//$conn=pg_connect(CONN_STRING);
	if (!$conn_ext=mssql_connect (STPDB_SERVER, STPDB_USER, STPDB_PASSWD))
		die('Fehler beim Verbindungsaufbau!');
	mssql_select_db(STPDB_DB, $conn_ext);

	if(!$conn = pg_pconnect(CONN_STRING))
		die('Fehler beim Verbindungsaufbau!');

	echo '
		<html>
		<head>
			<title>STP - VILESCI (Konto)</title>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		</head>
		<body>';

	echo 'Starte Konto Syncronisation '.date('H:i:s').'<br>';
	flush();

	$gesamt=0;
	$statistik='';
	$error=0;
	$head_text="Dies ist eine automatische Mail!\n\nFolgende Fehler sind bei der Synchronisation des Kontos aufgetreten:\n\n";
	$text='';


	$stg_arr = array();
	$stg_obj = new studiengang($conn);
	$stg_obj->getAll(null, false);

	foreach ($stg_obj->result as $stg)
		$stg_arr[$stg->studiengang_kz] = $stg->kuerzel;

	// ******** SYNC START ********** //

	$qry = "SELECT
				_person, sem1, sem2, sem3, sem4, sem5, sem6, sem7, sem8, sem9, sem10
			FROM
				studiengebuehren
			";

	if($result_ext = mssql_query($qry, $conn_ext))
	{
		while($row_ext=mssql_fetch_object($result_ext))
		{
			$gesamt++;

			//Person suchen
			$qry = "SELECT person_id, studiengang_kz FROM public.tbl_prestudent WHERE ext_id='$row_ext->_person'";
			if($result = pg_query($conn, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					$person_id = $row->person_id;
					$studiengang_kz = $row->studiengang_kz;
				}
				else
				{
					$text.="Person zu $row_ext->_person wurde nicht gefunden\n";
					$error++;
					continue;
				}
			}
			else
			{
				$text.="Fehler beim ermitteln der Person: ".pg_last_error($conn)."\n";
				$error++;
				continue;
			}

			//Semester durchlaufen
			for($i=1;$i<=10;$i++)
			{
				$semester = 'sem'.$i;
				if($row_ext->$semester=='')
					continue;

				if (strtotime($row_ext->$semester)>0)
					$datum = date('Y-m-d', strtotime($row_ext->$semester));
				else
					continue;

				$studiensemester_kurzbz = getStudiensemesterFromDatum($conn, $datum, true);
				if(!$studiensemester_kurzbz)
				{
					$text.="Es konnte kein passendes Studiensemester zu $datum gefunden werden\n";
					$error++;
				}

				$konto = new konto($conn);

				$qry = "SELECT * FROM public.tbl_konto WHERE
							person_id='$person_id' AND
							studiengang_kz='$studiengang_kz' AND
							studiensemester_kurzbz='$studiensemester_kurzbz' AND
							buchungstyp_kurzbz='Studiengebuehr'";
				if($result = pg_query($conn, $qry))
				{
					if(pg_num_rows($result)>0)
					{
						//Wenn der Eintrag schon vorhanden ist dann dieses Semester ueberspringen
						continue;
					}
				}
				else
				{
					$text.="Fehler bei Select:".pg_last_error($conn);
					continue;
				}

				//Belastung Buchen
				$konto->person_id = $person_id;
				$konto->studiengang_kz = $studiengang_kz;
				$konto->studiensemester_kurzbz = $studiensemester_kurzbz;
				$konto->buchungstyp_kurzbz = 'Studiengebuehr';
				$konto->buchungsnr_verweis = '';
				$konto->betrag = -363.36;
				$konto->buchungsdatum = $datum;
				$konto->buchungstext = "Studiengebuehr $stg_arr[$studiengang_kz] $i. Semester";
				$konto->mahnspanne = 0;
				$konto->updateamum = date('Y-m-d H:i:s');
				$konto->updatevon = 'sync';
				$konto->insertamum = date('Y-m-d H:i:s');
				$konto->insertvon = 'sync';

				if($konto->save(true))
				{
					//Gegenbuchung
					$konto->buchungsnr_verweis = $konto->buchungsnr;
					$konto->betrag = 363.36;
					if($konto->save(true))
					{
						$text.="Studiengebuehr fuer Person $person_id Studiengang $stg_arr[$studiengang_kz] Semester $i wurde hinzugefuegt\n";
					}
					else
					{
						$text.="Fehler beim Speichern der Gegenbuchung: $konto->errormsg\n";
						$error++;
					}
				}
				else
				{
					$text.="Fehler beim Speichern: $konto->errormsg\n";
					$error++;
				}
			}
		}
	}
	else
		$text.= "Fehler beim Laden des Kontos\n\n";

	$statistik .="Gesamt: $gesamt\n";
	$statistik .="Fehler: $error\n";

	$text = $statistik."\n\n".$text;
	//$to = 'oesi@technikum-wien.at';
	$to = $adress_ext;

	if(mail($to, 'SYNC Konto',$head_text.$text, "From: nsc@fhstp.ac.at"))
		echo "Mail wurde an $to versandt<br><br>";
	else
		echo "Fehler beim Senden an $to<br><br>";

	echo nl2br($text);
?>
</body>
</html>