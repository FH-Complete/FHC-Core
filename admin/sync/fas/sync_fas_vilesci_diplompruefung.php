<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Diplomprüfungsdatensätze von FAS DB in PORTAL DB
//*
//*

require_once('../../../vilesci/config.inc.php');


$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_fehler_lv=0;
$anzahl_lv_insert=0;
$anzahl_lv_fehler=0;
$anzahl_lv_gesamt=0;
$anzahl_lv_update=0;
$anzahl_betreuer_fehler=0;
$anzahl_quelle=0;
$anzahl_fehler=0;
$anzahl_fehler_le=0;
$anzahl_fehler_pa=0;
$anzahl_fehler_pbb=0;
$anzahl_fehler_pbg=0;
$anzahl_le_gesamt=0;
$anzahl_le_insert=0;
$anzahl_le_update=0;
$anzahl_pa_gesamt=0;
$anzahl_pa_insert=0;
$anzahl_pa_update=0;
$anzahl_pbb_gesamt=0;
$anzahl_pbb_insert=0;
$anzahl_pbb_update=0;
$anzahl_pbg_gesamt=0;
$anzahl_pbg_insert=0;
$anzahl_pbg_update=0;
$fachbereich_kurzbz='';
$ausgabe='';
$ausgabe_all='';
$ausgabe_le='';
$ausgabe_pa='';
$ausgabe_pb='';


function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>

<html>
<head>
<title>Synchro - FAS -> Portal - Diplomprüfung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
//nation
$qry = "SELECT * FROM diplomarbeit WHERE mitarbeiter_fk IS NOT NULL AND pruefungsdatum IS NOT NULL AND mitarbeiter_fk>0;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Diplomprüfung Sync\n------------------------\n");
	echo nl2br("Diplomprüfungssynchro Beginn: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		
		
		
		
		
	}
//echo und mail
echo nl2br("Diplomprüfungssynchro Ende: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");

$error_log_fas="Sync Diplomprüfung\n------------------------\n\n".$error_log_fas1."\n".$error_log_fas2."\n".$error_log_fas3."\n".$error_log_fas4."\n".$error_log_fas5."\n".$error_log_fas6."\n".$error_log_fas7."\n".$error_log_fas8;
echo nl2br("Allgemeine Fehler: ".$anzahl_fehler.", Anzahl Diplomprüfungen: ".$anzahl_quelle.".\n");


echo nl2br($error_log_fas."\n--------------------------------------------------------------------------------\n");
echo nl2br($ausgabe_all);

mail($adress, 'SYNC Diplomprüfung von '.$_SERVER['HTTP_HOST'], 
"Allgemeine Fehler: ".$anzahl_fehler.", Anzahl Diplomprüfungen: ".$anzahl_quelle.".\n".


$ausgabe_all,"From: vilesci@technikum-wien.at");

mail($adress, 'SYNC-Fehler Diplomprüfung  von '.$_SERVER['HTTP_HOST'], $error_log_fas, "From: vilesci@technikum-wien.at");
}
?>
</body>
</html>