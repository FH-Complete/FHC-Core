<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Prestudentdatensaetze von StP DB in PORTAL DB
//*
//*
//* setzt voraus: sync von sync.stp_person
//* benoetigt: tbl_syncperson

require_once('sync_config.inc.php');

$starttime=time();
	
if (!$conn_ext=mssql_connect (STPDB_SERVER, STPDB_USER, STPDB_PASSWD))
	die('Fehler beim Verbindungsaufbau!');
mssql_select_db(STPDB_DB, $conn_ext);

$conn=pg_connect(CONN_STRING)
	or die("Connection zur FH-Complete Datenbank fehlgeschlagen");
	
function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

$error_log='';
$error_log1='';
$error_log_ext='';
$ausgabe="";
$text = '';
$error = '';
$cont='';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_update=0;
$anzahl_fehler=0;
$eingefuegt=0;
$fehler=0;
$dublette=0;
$plausi='';
$start='';
$stg='';
$staat=array();

/*************************
 * StP-PORTAL - Synchronisation
 */
?>
<html>
<head>
<title>Synchro - StPoelten -> Vilesci - Prestudent</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>

<?php


//*********** Neue Daten holen *****************
$qry="SELECT __Person, datenquelle, inAusmassBesch, HoechsteAusbildung, _cxZugang, daMaturaDat, 
	_cxZugangFHMag, daZugangFHMagDat, chtitel, chnachname, chvorname  
		FROM sync.stp_person  
		WHERE __Person IN (SELECT ext_id FROM tbl_person WHERE ext_id IS NOT NULL) AND 
		(_cxPersonTyp='1' OR _cxPersonTyp='2');";

$error_log_ext="Überprüfung Prestudentdaten in EXT-DB:\n\n";
$start=date("d.m.Y H:i:s");
echo $start."<br>";
if($result = pg_query($conn, $qry))
{
	$anzahl_person_gesamt=pg_num_rows($result);
	$error_log_ext.="Anzahl der Datensätze: ".$anzahl_person_gesamt."\n";
	echo nl2br($error_log_ext);
	while($row=pg_fetch_object($result))
	{
		$cont='';
		//plausi
		if($error)
		{
			$error_log.="\n*****\n".$row->__person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname).": ".$error_log1;
			$error_log1='';
			$error=false;
			if($cont)
			{
				$fehler++;
				continue;
			}
		}
//Studiengang ermitteln 
		$qry_stg = "SELECT *  				
				FROM cxWebPage JOIN PersonGrp ON(_cxWebPage=__cxWebPage)
				JOIN _Person_PersonGrp ON(_PersonGrp=__PersonGrp)
				WHERE _Person=".myaddslashes($row->__person).";";
		if($result_stg = mssql_query($qry_stg, $conn_ext))
		{
			if($row_stg=mssql_fetch_object($result_stg))
			{
				$stg=$row_stg->_Studiengang;	
			}
		}
		else 
		{
			echo "<br>nix gfundn!";
		}
	 	echo "<br>*****<br>".$row->__person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname).", Studiengang ".$stg;
	}
}
else
{
	echo $qry;
}

echo "<br>Eingefügt:  ".$eingefuegt;
echo "<br>Doppelt:     ".$dublette;
echo "<br>Fehler:       ".$fehler;
echo "<br><br>";
echo nl2br($error_log);
echo nl2br($ausgabe);
/*
mail($adress, 'SYNC-Fehler StP-Prestudent von '.$_SERVER['HTTP_HOST'], $error_log,"From: vilesci@technikum-wien.at");

mail($adress, 'SYNC StP-Prestudent  von '.$_SERVER['HTTP_HOST'], "Sync Student\n------------\n\n"
."Personen: Gesamt: ".$anzahl_person_gesamt." / Eingefügt: ".$eingefuegt." / Fehler: ".$fehler." / Doppelt: ".$dublette
."\n\n".$dateiausgabe."Beginn: ".$start."\nEnde:    ".date("d.m.Y H:i:s")."\n\n".$ausgabe, "From: vilesci@technikum-wien.at");
*/

?>
</body>
</html>