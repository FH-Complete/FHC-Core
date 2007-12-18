<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Personendatensaetze von FAS DB in PORTAL DB
//*
//*
//* setzt voraus: sync von sync.stp_person, sync.stp_staat
//* benoetigt: tbl_syncperson

require_once('sync_config.inc.php');

$starttime=time();
$conn=pg_connect(CONN_STRING)
	or die("Connection zur FH-Complete Datenbank fehlgeschlagen");

function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

$error_log='';
$error_log_ext='';
$error_log1='';
$ausgabe="";
$text = '';
$error = '';
$cont='';
$anzahl_quelle=0;
$eingefuegt=0;
$fehler=0;
$update=0;
$plausi='';
$start='';
$stg='';
$Kalender='';
$rolle='';
$iu='';
$log_qry_ins='';

/*************************
 * StP-PORTAL - Synchronisation
 */
?>
<html>
<head>
<title>Check - StPoelten - Prestudentrollen (Lückenfüller)</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>

<?php

//*********** Neue Daten holen *****************
$qry="SELECT __Person, chtitel, chnachname, chvorname, daEintrittDat, prestudent_id 
		FROM sync.stp_person JOIN public.tbl_prestudent ON (__Person=ext_id)
		WHERE (_cxPersonTyp='1' OR _cxPersonTyp='2');";

//alle prestudentrollen sortiert nach datum desc
//bis zu Semester von daEintrittDat fehlende Rollen eintragen - vergleich studiensemester mit getStudiensemesterFromDatum($conn, $datum, $naechstes=true) von deintrittdat 
if($resultall = pg_query($conn,$qry))
{
	$anzahl_gesamt=pg_num_rows($resultall);
	$error_log_ext.="Anzahl der Datensätze: ".$anzahl_gesamt."\n";
	echo nl2br($error_log_ext);
	while($rowall=pg_fetch_object($resultall))
	{
		$cont='';
		if($rowall->daeintrittdat==NULL || $rowall->daeintrittdat=='')
		{
			$error_log1.="\nKein Eintrittsdatum eingetragen";
			$cont=true;
			$error=true;
		}
		if($error)
		{
			$error_log.="\n*****\n".$rowall->__person." - ".trim($rowall->chtitel)." ".trim($rowall->chnachname).", ".trim($rowall->chvorname).": ".$error_log1;
			$error_log1='';
			$error=false;
			if($cont)
			{
				$fehler++;
				continue;
			}
		}
		
		
		
		
		
		
	}
}
?>
</body>
</html>