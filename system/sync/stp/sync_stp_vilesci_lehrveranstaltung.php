<?php
// **************************************
// Syncronisiert alle Lehrveranstaltungen
// StPoelten -> VILESCI
// setzt vorraus: - tbl_sprache
//                - tbl_studiengang
// 
// Beschreibung:
// Das Semester der Lehrveranstaltungen wird ueber die Tabelle
// Studienplaneintrag ermittelt. Bei Lehrveranstaltungen die nicht in der Tabelle
// Studienplaneintrag vorkommen wird das Semester ueber die Tabelle SemesterplanEintrag
// ermittelt. 
// LVs die weder in der Tabelle Studienplaneintrag noch in der Tabelle Semesterplaneintrag
// vorhanden sind werden im 0ten Semester angelegt.
// **************************************
	require_once('sync_config.inc.php');
	require_once('../../../include/lehrveranstaltung.class.php');
	require_once('../../../include/studiengang.class.php');
	require_once('../../../include/lehrfach.class.php');
	
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
			<title>STP - VILESCI (Lehrveranstaltungen)</title>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		</head>
		<body>';
	
	echo 'Starte Lehrveranstaltungs Syncronisation '.date('H:i:s').'<br>';
	flush();

	$plausi_error=0;
	$update_error=0;
	$insert_error=0;
	$lvs_gesamt=0;
	$anz_update=0;
	$anz_insert=0;
	$update_lf_error=0;
	$insert_lf_error=0;
	$anz_lf_update=0;
	$anz_lf_insert=0;
	$statistik='';
	$head_text="Dies ist eine automatische Mail!\n\nFolgende Fehler sind bei der Synchronisation der Lehrveranstaltungen aufgetreten:\n\n";
	$text='';
	
	$stg_arr = array();
	$stg_obj = new studiengang($conn);
	$stg_obj->getAll(null, false);
	
	foreach ($stg_obj->result as $stg) 
		$stg_arr[$stg->ext_id] = $stg->studiengang_kz;

	// ******** FUNKTIONEN ********** //
	function cleankurzbz($kurzbz)
	{
		$kurzbz = str_replace('/', '', $kurzbz);
		$kurzbz = str_replace('Ü', 'U', $kurzbz);
		$kurzbz = str_replace('Ö', 'O', $kurzbz);
		$kurzbz = str_replace('Ä', 'A', $kurzbz);
		
		return $kurzbz;
	}
	
	function synctabentry_lv($semester, $lv, $lehrveranstaltung_id)
	{
		global $conn;
		
		$qry = "INSERT INTO sync.tbl_synclehrveranstaltung(lv, insemester, lehrveranstaltung_id) VALUES($lv, $semester, $lehrveranstaltung_id);";
		pg_query($conn, $qry);
	}
	
	function synctabentry_lf($semester, $lv, $lehrfach_id)
	{
		global $conn;
		
		$qry = "INSERT INTO sync.tbl_synclehrfach(lv, insemester, lehrfach_id) VALUES($lv, $semester, $lehrfach_id);";
		pg_query($conn, $qry);
	}
	
	if (!@pg_query($conn,'SELECT * FROM sync.tbl_synclehrveranstaltung LIMIT 1;'))
	{
		$sql='CREATE TABLE sync.tbl_synclehrveranstaltung (
				lv	integer,
				insemester	integer,
				lehrveranstaltung_id integer,
				constraint "pk_tbl_sync_stp_lehrveranstaltung" primary key ("lv","insemester","lehrveranstaltung_id"));
			Grant select on sync.tbl_synclehrveranstaltung to group "admin";
			Grant update on sync.tbl_synclehrveranstaltung to group "admin";
			Grant delete on sync.tbl_synclehrveranstaltung to group "admin";
			Grant insert on sync.tbl_synclehrveranstaltung to group "admin";';
		if (!@pg_query($conn,$sql))
			$text.= "sync.tbl_synclehrveranstaltung: ".pg_last_error($conn)."\n";
		else
			$text.= "sync.tbl_synclehrveranstaltung wurde angelegt!\n";
	}
	
	if (!@pg_query($conn,'SELECT * FROM sync.tbl_synclehrfach LIMIT 1;'))
	{
		$sql='CREATE TABLE sync.tbl_synclehrfach (
				lv	integer,
				insemester	integer,
				lehrfach_id integer,
				constraint "pk_tbl_sync_stp_lehrfach" primary key ("lv","insemester","lehrfach_id"));
			Grant select on sync.tbl_synclehrfach to group "admin";
			Grant update on sync.tbl_synclehrfach to group "admin";
			Grant delete on sync.tbl_synclehrfach to group "admin";
			Grant insert on sync.tbl_synclehrfach to group "admin";';
		if (!@pg_query($conn,$sql))
			$text.= "sync.tbl_synclehrfach: ".pg_last_error($conn)."\n";
		else
			$text.= "sync.tbl_synclehrfach wurde angelegt!\n";
	}
	
	// ******** SYNC START ********** //
		
	$qry = "SELECT 
				_LV, SUBSTRING(chLVNr_new, 0, 200) as chLVNr, SUBSTRING(chBezeichnung, 0, 200) as chBezeichnung, _Studiengang, SUBSTRING(meKommentar, 0, 200) as meKommentar, inSemester, inSWS, ECTS
			FROM 
				lv JOIN studienplaneintrag ON(__LV=_LV)
			UNION
			SELECT
				_LV, SUBSTRING(chLVNr_new, 0, 200) as chLVNr, SUBSTRING(chBezeichnung, 0, 200) as chBezeichnung, _Studiengang, SUBSTRING(meKommentar, 0, 200) as meKommentar, inSemester, inSWS, ECTS
			FROM 
				lv JOIN semesterplaneintrag on(__LV=_LV)
			WHERE
				semesterplaneintrag._lv not in(SELECT _lv FROM studienplaneintrag)
			UNION
			SELECT
				__LV as _LV, SUBSTRING(chLVNr_new, 0, 200) as chLVNr, SUBSTRING(chBezeichnung, 0, 200) as chBezeichnung, _Studiengang, SUBSTRING(meKommentar, 0, 200) as meKommentar, 0 as inSemester, 0 as inSWS, 0 as ECTS
			FROM 
				lv
			WHERE
				__LV not in(SELECT _lv FROM studienplaneintrag) AND
				__LV not in(SELECT _lv FROM semesterplaneintrag)				
			";
		
	if($result_ext = mssql_query($qry, $conn_ext))
	{
		while($row_ext=mssql_fetch_object($result_ext))
		{
			$lvs_gesamt++;
			//Lehrveranstaltung
			
			//Schauen ob dieser Eintrag schon vorhanden ist
			$qry = "SELECT lehrveranstaltung_id FROM sync.tbl_synclehrveranstaltung WHERE lv='$row_ext->_LV' AND insemester='$row_ext->inSemester'";
			
			if($result = pg_query($conn, $qry))
			{
				if(pg_num_rows($result)>0)
				{
					if($row = pg_fetch_object($result))
						$lehrveranstaltung_id=$row->lehrveranstaltung_id;
					else 
					{
						$text.="Fehler beim Auslesen der Lehrveranstaltung_id fuer lv $row_ext->_LV insemester $row_ext->inSemester\n";
						continue;
					}
				}
				else 
				{
					$lehrveranstaltung_id='';
				}
			}
			else 
			{
				$text.='Fehler beim Ermitteln der Lehrveranstaltung:'.pg_last_error($conn)."\n";
				continue;
			}
			
			$lv_obj = new lehrveranstaltung($conn);
			
			if($lehrveranstaltung_id=='')
			{
				$lv_obj->new = true;
				$lv_obj->insertamum = date('Y-m-d H:i:s');
				$lv_obj->insertvon = 'sync';
			}
			else 
			{
				if($lv_obj->load($lehrveranstaltung_id))
				{
					$lv_obj->new = false;
					$lv_obj->updateamum = date('Y-m-d H:i:s');
					$lv_obj->updatevon = 'sync';
				}
				else 
				{
					$text.="Fehler beim Laden einer gefundenen Lehrveranstaltung?!? Lehrveranstaltung_id:$lehrveranstaltung_id\n";
				}
			}
			
			$updtext = '';
			if(!$lv_obj->new)
			{
				//Aenderungen suchen
				if($lv_obj->kurzbz!=cleankurzbz($row_ext->chLVNr))
					$updtext.="	Kurzbz wurde von $lv_obj->kurzbz auf ".cleankurzbz($row_ext->chLVNr)." geaendert\n";
				if($lv_obj->bezeichnung!=$row_ext->chBezeichnung)
					$updtext.="	Bezeichnung wurde von $lv_obj->bezeichnung auf $row_ext->chBezeichnung geaendert\n";
				if($lv_obj->studiengang_kz!=$stg_arr[$row_ext->_Studiengang])
					$updtext.="	Studiengang wurde von $lv_obj->studiengang_kz auf ".$stg_arr[$row_ext->_Studiengang]." geaendert\n";
				if($lv_obj->semester!=$row_ext->inSemester)
					$updtext.="	Semester wurde von $lv_obj->semester auf $row_ext->inSemester geaendert\n";
				if($lv_obj->sprache!='German')
					$updtext.="	Sprache wurde von $lv_obj->sprache auf German geaendert\n";
				if($lv_obj->ects!=round($row_ext->ECTS,2))
					$updtext.="	ECTS wurde von $lv_obj->ects auf ".round($row_ext->ECTS,2)." geaendert\n";
				if($lv_obj->semesterstunden!=((int)$row_ext->inSWS*ANZAHL_SEMESTERWOCHEN))
					$updtext.="	Semesterstunden wurde von $lv_obj->semesterstunden auf ".($row_ext->inSWS*ANZAHL_SEMESTERWOCHEN)." geaendert\n";
				if($lv_obj->anmerkung!=$row_ext->meKommentar)
					$updtext.="	Anmerkung wurde von $lv_obj->anmerkung auf $row_ext->meKommentar geaendert\n";
				if($lv_obj->lehre != true)
					$updtext.="	lehre wurde von $lv_obj->lehre auf true geaendert\n";
				if($lv_obj->lehreverzeichnis != strtolower(cleankurzbz($row_ext->chLVNr)))
					$updtext.="	Lehreverzeichnis wurde von $lv_obj->lehreverzeichnis auf ".strtolower(cleankurzbz($row_ext->chLVNr))." geaendert\n";
				if($lv_obj->aktiv != true)
					$updtext.="	aktiv wurde von $lv_obj->aktiv auf true geaendert\n";
				if($lv_obj->planfaktor != '')
					$updtext.="	planfaktor wurde von $lv_obj->planfaktor auf '' geaendert\n";
				if($lv_obj->planlektoren != '')
					$updtext.="	planlektoren wurde von $lv_obj->planlektoren auf '' geaendert\n";
				if($lv_obj->planpersonalkosten != '')
					$updtext.="	lehre wurde von $lv_obj->planpersonalkosten auf '' geaendert\n";
				//if($lv_obj->ext_id != $row_ext->__StudienplanEintrag)
				//	$updtext.=" ext_id wurde von $lv_obj->ext_id auf $row_ext->__StudienplanEintrag geaendert\n";
				if($lv_obj->sort != '')
					$updtext.="	sort wurde von $sort auf '' geaendert\n";
				if($lv_obj->zeugnis != true)
					$updtext.="	zeugnis wurde von $lv_obj->zeugnis auf true geaendert\n";
				if($lv_obj->koordinator != '')
					$updtext.="	koordinator wurde von $lv_obj->koordinator auf '' geaendert\n";
				if($lv_obj->projektarbeit != false)
					$updtext.="	projektarbeit wurde von $lv_obj->projektarbeit auf false geaendert\n";
			}
			$lv_obj->kurzbz = cleankurzbz($row_ext->chLVNr);
			$lv_obj->bezeichnung = $row_ext->chBezeichnung;
			$lv_obj->studiengang_kz = $stg_arr[$row_ext->_Studiengang];
			$lv_obj->semester = $row_ext->inSemester;
			$lv_obj->sprache = 'German';
			$lv_obj->ects = $row_ext->ECTS;
			$lv_obj->semesterstunden = (int) $row_ext->inSWS*ANZAHL_SEMESTERWOCHEN;
			$lv_obj->anmerkung = $row_ext->meKommentar;
			$lv_obj->lehre = true;
			$lv_obj->lehreverzeichnis = strtolower(cleankurzbz($row_ext->chLVNr));
			$lv_obj->aktiv = true;
			$lv_obj->planfaktor = '';
			$lv_obj->planlektoren = '';
			$lv_obj->planpersonalkosten = '';
			$lv_obj->ext_id = '';
			$lv_obj->sort = '';
			$lv_obj->zeugnis = true;
			$lv_obj->koordinator = '';
			$lv_obj->projektarbeit = false;
			
			if($updtext!='' || $lv_obj->new)
			{
				if($lv_obj->save())
				{
					if($lv_obj->new)
					{
						$text.= "Lehrveranstaltung $lv_obj->bezeichnung/$lv_obj->semester wurde neu angelegt\n";
						synctabentry_lv($lv_obj->semester, $row_ext->_LV, $lv_obj->lehrveranstaltung_id);
						$anz_insert++;
					}
					else 
					{
						$text.= "Lehrveranstaltung $lv_obj->bezeichnung/$lv_obj->semester wurde aktualisiert\n".$updtext;
						$anz_update++;
					}
				}
				else 
				{
					$text.= "Fehler beim Speichern von $lv_obj->bezeichnung/$lv_obj->semester/$lv_obj->lehrveranstaltung_id:".$lv_obj->errormsg.' '.pg_last_error($conn);
					if($lv_obj->new)
						$insert_error++;
					else 
						$update_error++;
				}
			}
			
			
			// *********** Lehrfach **************
			//Schauen ob dieser Eintrag schon vorhanden ist
			$qry = "SELECT lehrfach_id FROM sync.tbl_synclehrfach WHERE lv='$row_ext->_LV' AND insemester='$row_ext->inSemester'";
			
			if($result = pg_query($conn, $qry))
			{
				if(pg_num_rows($result)>0)
				{
					if($row = pg_fetch_object($result))
						$lehrfach_id=$row->lehrfach_id;
					else 
					{
						$text.="Fehler beim Auslesen der Lehrfach_id bei lv $row_ext->_LV semester $row_ext->inSemester\n";
						continue;
					}
				}
				else 
				{
					$lehrfach_id='';
				}
			}
			else 
			{
				$text.='Fehler beim Ermitteln der Lehrveranstaltung:'.pg_last_error($conn)."\n";
				continue;
			}
			
			$lf_obj = new lehrfach($conn);
			
			if($lehrfach_id=='')
			{
				$lf_obj->new = true;
				$lf_obj->insertamum = date('Y-m-d H:i:s');
				$lf_obj->insertvon = 'sync';
			}
			else 
			{
				if($lf_obj->load($lehrfach_id))
				{
					$lf_obj->new = false;
					$lf_obj->updateamum = date('Y-m-d H:i:s');
					$lf_obj->updatevon = 'sync';
				}
				else 
				{
					$text.="Fehler beim Laden eines gefundenen Lehrfaches?!? Lehrfach_id:$lehrfach_id\n";
				}
			}
			
			$updtext = '';
			if(!$lf_obj->new)
			{
				//Aenderungen suchen
				if($lf_obj->studiengang_kz!=$stg_arr[$row_ext->_Studiengang])
					$updtext.="	Studiengang wurde von $lf_obj->studiengang_kz auf ".$stg_arr[$row_ext->_Studiengang]." geaendert\n";
				if($lf_obj->semester!=$row_ext->inSemester)
					$updtext.="	Semester wurde von $lv_obj->semester auf $row_ext->inSemester geaendert\n";
				if($lf_obj->sprache!='German')
					$updtext.="	Sprache wurde von $lf_obj->sprache auf German geaendert\n";
				if($lf_obj->fachbereich_kurzbz!='Dummy')
					$updtext.="	Fachbereich_kurzbz wurde von $lf_obj->fachbereich_kurzbz auf 'Dummy' geaendert\n";
				if($lf_obj->kurzbz != cleankurzbz($row_ext->chLVNr))
					$updtext.="	Kurzbz wurde von $lf_obj->kurzbz auf ".cleankurzbz($row_ext->chLVNr)." geaendert\n";
				if($lf_obj->bezeichnung!=$row_ext->chBezeichnung)
					$updtext.="	Bezeichnung wurde von $lf_obj->bezeichnung auf $row_ext->chBezeichnung geaendert\n";
				if($lf_obj->farbe != '')
					$updtext.="	farbe wurde von $lf_obj->farbe auf '' geaendert\n";				
				if($lf_obj->aktiv != true)
					$updtext.="	aktiv wurde von $lf_obj->aktiv auf true geaendert\n";
				//if($lf_obj->ext_id != $row_ext->__StudienplanEintrag)
				//	$updtext.=" ext_id wurde von $lf_obj->ext_id auf $row_ext->__StudienplanEintrag geaendert\n";
			}
			
			$lf_obj->kurzbz = cleankurzbz($row_ext->chLVNr);
			$lf_obj->bezeichnung = $row_ext->chBezeichnung;
			$lf_obj->studiengang_kz = $stg_arr[$row_ext->_Studiengang];
			$lf_obj->semester = $row_ext->inSemester;
			$lf_obj->sprache = 'German';
			$lf_obj->aktiv = true;
			$lf_obj->fachbereich_kurzbz = 'Dummy';
			$lf_obj->farbe = '';
			$lf_obj->ext_id = '';
			
			if($updtext!='' || $lf_obj->new)
			{
				if($lf_obj->save())
				{
					if($lf_obj->new)
					{
						$text.= "Lehrfach $lf_obj->bezeichnung/$lf_obj->semester wurde neu angelegt\n";
						synctabentry_lf($lf_obj->semester, $row_ext->_LV, $lf_obj->lehrfach_id);
						$anz_lf_insert++;
					}
					else 
					{
						$text.= "Lehrfach $lf_obj->bezeichnung/$lf_obj->semester wurde aktualisiert\n".$updtext;
						$anz_lf_update++;
					}
				}
				else
				{
					$text.= "Fehler beim Speichern von $lf_obj->bezeichnung/$lf_obj->semester/$lf_obj->lehrfach_id:".$lf_obj->errormsg.' '.pg_last_error($conn);
					if($lf_obj->new)
						$insert_lf_error++;
					else 
						$update_lf_error++;
				}
			}
		}
	}
	else 
		$text.= "Fehler beim Laden der Lehrveranstaltungen\n\n";
	
	$statistik .="LVs Import: $lvs_gesamt\n";
	$statistik .="Neue LVs: $anz_insert\n";
	$statistik .="Aktualisierte LVs: $anz_update\n";
	$statistik .="Fehler beim Anlegen von LVs: $insert_error\n";
	$statistik .="Fehler beim Aktualisieren von LVs: $update_error\n\n";
	$statistik .="Neue LF: $anz_lf_insert\n";
	$statistik .="Aktualisierte LF: $anz_lf_update\n";
	$statistik .="Fehler beim Anlegen von LF: $insert_lf_error\n";
	$statistik .="Fehler beim Aktualisieren von LF: $update_lf_error\n\n";
	
	$text = $statistik."\n\n".$text;
	//$to = 'oesi@technikum-wien.at';
	$to = $adress_ext;
	
	if(mail($to, 'SYNC Lehrveranstaltung',$head_text.$text, "From: vilesci@technikum-wien.at"))
		echo "Mail wurde an $to versandt<br><br>";
	else 
		echo "Fehler beim Senden an $to<br><br>";
	
	echo nl2br($text);
?>
</body>
</html>