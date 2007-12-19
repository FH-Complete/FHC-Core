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
require_once('../../../include/functions.inc.php');

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
$beginnsem='';
$semstart=array();
$semende=array();

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

$qry="SELECT * FROM public.tbl_studiensemester ORDER BY start;";
if($result = pg_query($conn,$qry))
{
	while($row=pg_fetch_object($result))
	{
		$semstart[$row->studiensemester_kurzbz]=$row->start;
		$semende[$row->studiensemester_kurzbz]=$row->ende;	
	}
}
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
		
		echo nl2br("\n*****\n".$rowall->__person." - ".trim($rowall->chtitel)." ".trim($rowall->chnachname).", ".trim($rowall->chvorname).": ".$rowall->daeintrittdat);
		
		$qry_rl="SELECT * FROM public.tbl_prestudentrolle WHERE prestudent_id=".myaddslashes($rowall->prestudent_id)." ORDER BY datum desc LIMIT 1";
		if($resultrl = pg_query($conn,$qry_rl))
		{
			if($rowrl=pg_fetch_object($resultrl))
			{
				$beginnsem=getStudiensemesterFromDatum($conn, $rowall->daeintrittdat, true);
				while ($rowrl->studiensemester_kurzbz>$beginnsem) 
				{
					$qry_chk="SELECT * FROM public.tbl_prestudentrolle WHERE prestudent_id=".myaddslashes($rowall->prestudent_id)." AND studiensemester_kurzbz=".myaddslashes($rowrl->studiensemester_kurzbz).";";
					if($resultchk = pg_query($conn,$qry_chk))
					{
						if(pg_num_rows($resultchk)==0)
						{
							//INSERT Prestudentrolle
							$qry_ins="INSERT INTO public.tbl_prestudentrolle (prestudent_id, rolle_kurzbz,
								studiensemester_kurzbz, ausbildungssemester,datum, orgform_kurzbz,
								insertamum, insertvon, updateamum, updatevon, ext_id)
								VALUES (".
								myaddslashes($prestudent_id).", 
								'Student', ".
								myaddslashes($rowrl->studiensemester_kurzbz).", ".
								myaddslashes($rowrl->ausbildungssemester).",
								now(), ".
								myaddslashes($rowrl->orgform).",
								now(),
								'SYNC',
								NULL,
								NULL, 
								NULL)";
							if(!$resultins = pg_query($conn,$qry_ins))
							{
								$fehler++;
								$error_log.= $sql."\n<strong>".pg_last_error($conn)." </strong>\n";	
							}
						}
					}
				}
				//Interessent (6Mon.) und Bewerber (4Mon.) eintragen
				
			}
		}
		else 
		{
			$fehler++;
			$error_log.= "\n".$sql."\n<strong>".pg_last_error($conn)." </strong>\n";
		}
		
		
	}
}
?>
</body>
</html>