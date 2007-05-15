<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Lehreinheitendatensätze von FAS DB in PORTAL DB
//*
//*

require_once('../../../vilesci/config.inc.php');


$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

//$adress='ruhan@technikum-wien.at';
$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$ausgabe='';
$ausgabe1='';
$anzahl_eingefuegt=0;
$anzahl_geaendert=0;
$anzahl_fehler=0;
$anzahl_quelle=0;

function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

/*************************
 * FAS-VILESCI - Synchronisation
 */
?>

<html>
<head>
<title>Synchro - FAS -> Vilesci - Lehreinheiten</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
$qry_main = "SELECT * FROM lehreinheit;";

if($result = pg_query($conn_fas, $qry_main))
{
	echo nl2br("Lehreinheiten Sync\n----------------------\n");
	echo nl2br("Lehreinheitensynchro Beginn: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		//pg_query($conn, "BEGIN");
		$error=false;
		//$lehrveranstaltung_id	='';
		//$studiensemester_kurzbz	='';
		//$lehrfach_id			='';
		//$lehrform_kurzbz		='';
		$stundenblockung		=$row->ivar3;
		$wochenrythmus		=$row->ivar1;
		$start_kw			=$row->ivar2;
		//$raumtyp			='';
		//$raumtypalternativ		='';
		$sprache			='German';
		$lehre				=true;
		$anmerkung			=$row->bemerkungen;
		$unr				='';
		$lvnr				=$row->nummer;
		$updateamum		='';
		$updatevon			='SYNC';
		$insertamum			=$row->creationdate;
		//$insertvon			='';
		$ext_id			=$row->lehreinheit_pk;
		
		//insertvon ermitteln
		$qrycu="SELECT name FROM public.benutzer WHERE benutzer_pk='".$row->creationuser."';";
		if($resultcu = pg_query($conn_fas, $qrycu))
		{
			if($rowcu=pg_fetch_object($resultcu))
			{
				$insertvon=$rowcu->name;
			}
		}
		//lehrveranstaltung ermitteln
		$qry="SELECT lva_vilesci FROM sync.tbl_synclehrveranstaltung WHERE lva_fas='".$row->lehrveranstaltung_fk."';";
		if($results = pg_query($conn, $qry))
		{
			if($rows=pg_fetch_object($results))
			{ 
				$lva=$rows->lva_vilesci;	
			}
			else 
			{
				$error=true;
				$error_log.="LVA_FAS=".$row->lehrveranstaltung_fk." in Tabelle tbl_synclehrveranstaltung nicht gefunden:\n";
			}
		}
		if($error)
		{
			$anzahl_fehler++;
			continue;
		}
		//studiengang ermitteln
		$qry="SELECT lehrveranstaltung_id, studiengang_kz, semester FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id='".$lva."';";
		if($result1 = pg_query($conn, $qry))
		{
			if($row1=pg_fetch_object($result1))
			{ 
				$lehrveranstaltung_id=$row1->lehrveranstaltung_id;
				$studiengang_kz=$row1->studiengang_kz;
				$semester=$row1->semester;
			}
			else 
			{
				$error=true;
				$error_log.="Lehrveranstaltung mit ext_id='".$row->lehrveranstaltung_fk."' nicht gefunden.\n";
			}
		}
		if($error)
		{
			$anzahl_fehler++;
			continue;
		}
		//studiensemester ermitteln
		$qry="SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE ext_id='$row->studiensemester_fk'";
		if($resulto = pg_query($conn, $qry))
		{
			if($rowo=pg_fetch_object($resulto))
			{ 
				$studiensemester_kurzbz=$rowo->studiensemester_kurzbz;
			}
			else 
			{
				$error=true;
				$error_log.="Studiensemester mit ext_id='".$row->studiensemester_fk."' nicht gefunden.\n";
			}
		}
		if($error)
		{
			$anzahl_fehler++;
			continue;
		}
		$qry="SELECT fachbereich_kurzbz FROM public.tbl_fachbereich WHERE ext_id='$row->fachbereich_fk'";
		if($result2 = pg_query($conn, $qry))
		{
			if($row2=pg_fetch_object($result2))
			{ 
				$fachbereich_kurzbz=$row2->fachbereich_kurzbz;
			}
			else 
			{
				$error=true;
				$error_log.="Fachbereich mit ext_id='".$row->fachbereich_fk."' nicht gefunden.\n";
			}
		}
		if($error)
		{
			$anzahl_fehler++;
			continue;
		}
		//lehrfach ermitteln
		$qry="SELECT lehrfach_id FROM lehre.tbl_lehrfach WHERE fachbereich_kurzbz='".$row->fachbereich_kurzbz."' AND semester='".$row->semester."' AND studiengang_kz='".$studiengang_kz."';";
		if($resulto = pg_query($conn, $qry))
		{
			if($rowo=pg_fetch_object($resulto))
			{ 
				$lehrfach_id=$rowo->lehrfach_id;
			}
			else 
			{
				$error=true;
				$error_log.="Lehrfach mit Fachbereich='".$fachbereich_kurzbz."', Semester='".$semester."' und Studiengang='".$studiengang_kz."' nicht gefunden.\n";
			}
		}
		if($error)
		{
			$anzahl_fehler++;
			continue;
		}
		//lehrform ermitteln
		$qry="SELECT kurzbezeichnung FROM lehrform WHERE lehrform_pk='".$row->lehrform_fk."';";
		if($resulto = pg_query($conn_fas, $qry))
		{
			if($rowo=pg_fetch_object($resulto))
			{ 
				$lehrform_kurzbz=trim($rowo->kurzbezeichnung);
			}
			else 
			{
				$error=true;
				$error_log.="Lehrform von lehrform_fk'".$lehrform_fk."' in der Tabelle lehrform nicht gefunden.\n";
			}
		}
		if($error)
		{
			$anzahl_fehler++;
			continue;
		}
		//raumtypen ermitteln
		$qry="SELECT kurzbezeichnung FROM raumtyp WHERE raumtyp_pk='".$row->raumtyp_fk."';";
		if($resulto = pg_query($conn_fas, $qry))
		{
			if($rowo=pg_fetch_object($resulto))
			{ 
				$raumtyp=trim($rowo->kurzbezeichnung);
			}
			else 
			{
				$error=true;
				$error_log.="Raumtyp von raumtyp_fk'".$raumtyp_fk."' in der Tabelle raumtyp nicht gefunden.\n";
			}
		}
		if($error)
		{
			$anzahl_fehler++;
			continue;
		}
		$qry="SELECT kurzbezeichnung FROM raumtyp WHERE raumtyp_pk='".$row->alternativraumtyp_fk."';";
		if($resulto = pg_query($conn_fas, $qry))
		{
			if($rowo=pg_fetch_object($resulto))
			{ 
				$raumtypalternativ=trim($rowo->kurzbezeichnung);
			}
			else 
			{
				$error=true;
				$error_log.="Alternativer Raumtyp von alternativraumtyp_fk'".$alternativraumtyp_fk."' in der Tabelle raumtyp nicht gefunden.\n";
			}
		}
		if($error)
		{
			$anzahl_fehler++;
			continue;
		}
		//insert oder update?
		$qry="SELECT * FROM lehre.tbl_lehreinheit WHERE ";
		
	}
	$error_log="Sync Lehreinheiten\n-----------------------\n\n".$error_log."\n";
	echo nl2br("Lehreinheitensynchro Ende: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");
	echo nl2br("Gesamt: ".$anzahl_quelle." / Eingefügt: ".$anzahl_eingefuegt++." / Geändert: ".$anzahl_geaendert." / Fehler: ".$anzahl_fehler."\n\n");
	echo nl2br($error_log. "\n------------------------------------------------------------------------\n".$ausgabe);
	
	mail($adress, 'SYNC-Fehler Lehreinheiten  von '.$_SERVER['HTTP_HOST'], $error_log, "From: vilesci@technikum-wien.at");
	mail($adress, 'SYNC Lehreinheiten von '.$_SERVER['HTTP_HOST'], "Sync Lehreinheiten\n-----------------------\n\nGesamt: ".$anzahl_quelle." / Eingefügt: ".$anzahl_eingefuegt++." / Geändert: ".$anzahl_geaendert." / Fehler: ".$anzahl_fehler."\n\n".$ausgabe, "From: vilesci@technikum-wien.at");
}
?>
</body>
</html>