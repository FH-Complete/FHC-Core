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
//* benötigt: tbl_syncperson, tbl_zgv, tbl_zgvmaster, tbl_studiensemester
//* 

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
$aufmerksam=array();
$zgv=array();
$Kalender='';
$rolle='';

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

$qry="SELECT * FROM public.tbl_aufmerksamdurch";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$aufmerksam[$row->ext_id]=$row->aufmerksamdurch_kurzbz;
	}
}
$qry="SELECT * FROM sync.stp_zugang";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$zgv[$row->cxzugang]=$row->zgv_code;
	}
}
$zgv[2]=99;
$zgv['']=99;

$ststat=array(2=>'Bewerber', 3=>'Student', 4=>'Ausserordentlicher', 5=>'Unterbrecher', 6=>'Absolvent', 7=>'Abbrecher', 8=>'Abgewiesener',
	10=>'Diplomand', 11=>'Diplomand', 12=>'Incoming');

//*********** Neue Daten holen *****************
$qry="SELECT __Person, datenquelle, inAusmassBesch, HoechsteAusbildung, _cxZugang, daMaturaDat, 
	_cxZugangFHMag, daZugangFHMagDat, chtitel, chnachname, chvorname, studiengang_kz, typ, 
	chKalenderSemStatAend, inStudiensemester, _cxStudStatus, _StgOrgForm  
		FROM sync.stp_person JOIN sync.stp_stgvertiefung ON (_stgvertiefung=__stgvertiefung) 
		JOIN public.tbl_studiengang ON (_studiengang=ext_id)   
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
		if($row->datenquelle=='' || $row->datenquelle==NULL)
		{
			$datenquelle=0;
		}
		else 
		{
			$datenquelle==$row->datenquelle;
		}
		if($row->studiengang_kz=='' || $row->studiengang_kz==NULL)
		{
			$error_log1.="\nKein zugeordneter Studiengang gefunden";
			$cont=true;
			$error=true;
		}
		if($row->_cxzugang=='' || $row->_cxzugang==NULL)
		{
			$error_log1.="\nZugangsvoraussetzung nicht eingetragen";
			$error=true;
		}
		if($row->damaturadat=='' || $row->damaturadat==NULL)
		{
			$error_log1.="\nDatum der Zugangsvoraussetzung nicht eingetragen";
			$error=true;
		}
		if($row->typ=="m")
		{
			if($row->_cxzugangfhmag=='' || $row->_cxzugangfhmag==NULL)
			{
				$error_log1.="\nZugangsvoraussetzung Mag. nicht eingetragen";
				$error=true;
			}
			if($row->dazugangfhmagdat=='' || $row->dazugangfhmagdat==NULL)
			{
				$error_log1.="\nDatum der Zugangsvoraussetzung Mag. nicht eingetragen";
				$error=true;
			}
		}
		/*if($row->_stgorgform=='' || $row->_stgorgform==NULL)
		{
			$error_log1.="\nOrganisationsform nicht eingetragen";
			$error=true;
		}*/
		if($row->_cxstudstatus=='' || $row->_cxstudstatus==NULL)
		{
			$error_log1.="\nStudentenstatus nicht eingetragen";
			$cont=true;
			$error=true;
		}
		if($row->chkalendersemstataend=='' || $row->chkalendersemstataend==NULL)
		{
			$error_log1.="\nKalenderSemStatAend (Studiensemester) nicht eingetragen";
			$cont=true;
			$error=true;
		}
		if($error)
		{
			$error_log.="\n*****\n".$row->__person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname).": ".$error_log1;
			$error_log1='';
			$error=false;
			if($cont)
			{
				$error_log.="\n==>nicht übertragen!";
				$fehler++;
				continue;
			}
			else 
			{
				$error_log.="\n==>übertragen!";	
			}
		}
		
		if($row->_stgorgform==1)
		{
			$orgform="VZ";
		}
		elseif($row->_stgorgform==2)
		{
			$orgform="BB";
		}
		elseif($row->_stgorgform==4)
		{
			$orgform="ZGS";
		}
		else 
		{
			$orgform="VZ";
		}
		$rolle=$ststat[$row->_cxstudstatus];
		$Kalender=ucwords(substr($row->chkalendersemstataend,0,1)).'S'.((integer)substr($row->chkalendersemstataend,1,2)<11?'20':'19').substr($row->chkalendersemstataend,1,2);
		//echo substr($row->chkalendersemstataend,2,2)."/".$row->chkalendersemstataend."--->".$Kalender;
		
		$qry_synk="SELECT * FROM sync.tbl_syncperson where __person=".$row->__person.";";
		$row_synk=pg_fetch_object(pg_query($conn, $qry_synk));
		$qry_chk="SELECT * FROM public.tbl_prestudent WHERE person_id=".myaddslashes($row_synk->person_id)." AND studiengang_kz=".myaddslashes($row->studiengang_kz).";";
		if($result_chk = pg_query($conn, $qry_chk))
		{
			if(pg_num_rows($result_chk)==0)	
			{
				pg_query($conn, "BEGIN");
				$qry_ins="INSERT INTO public.tbl_prestudent (aufmerksamdurch_kurzbz, person_id, studiengang_kz,
					berufstaetigkeit_code, ausbildungcode, zgv_code, zgvort, zgvdatum, zgvmas_code, zgvmaort, 
					zgvmadatum, 	aufnahmeschluessel, facheinschlberuf, reihungstest_id, anmeldungreihungstest, 
					reihungstestangetreten, punkte, bismelden, anmerkung, insertamum, insertvon, updateamum, 
					updatevon, ext_id) 
					VALUES (".
					myaddslashes($aufmerksam[$datenquelle]).", ".
					myaddslashes($row_synk->person_id).", ".
					myaddslashes($row->studiengang_kz).", 
					NULL, ".
					myaddslashes($row->hoechsteausbildung).", ".
					myaddslashes($zgv[$row->_cxzugang]).", 
					NULL, ".
					myaddslashes($row->damaturadat).", ".
					myaddslashes($row->_cxzugangfhmag).", 
					NULL, ".
					myaddslashes($row->dazugangfhmagdat).", 
					NULL, 
					FALSE, 
					NULL, 
					NULL, 
					TRUE, ".
					myaddslashes(0).", 
					TRUE, 
					'', 
					now(), 
					'SYNC', 
					NULL, 
					NULL, ".
					myaddslashes($row->__person).");";
				
					if(!$result_neu = pg_query($conn, $qry_ins))
					{
						$error_log.= $qry_ins."\n<strong>".pg_last_error($conn)." </strong>\n";
						$fehler++;
						pg_query($conn, "ROLLBACK");
					}
					else 
					{
						//Prestudent_id ermitteln
						$qry_seq = "SELECT currval('public.tbl_prestudent_prestudent_id_seq') AS id;";
						if($row_seq=pg_fetch_object(pg_query($conn,$qry_seq)))
						{
							$prestudent_id=$row_seq->id;
						}
						else
						{
							$error=true;
							$error_log.='Prestudent-Sequence konnte nicht ausgelesen werden\n';
						}
						if(!$error)
						{
							$ausgabe.="\n------------------\nÜbertragen: ".$row->__person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname).", Stg: ".$row->studiengang_kz;	
							$qry_ins="INSERT INTO public.tbl_prestudentrolle (prestudent_id, rolle_kurzbz, 
								studiensemester_kurzbz, ausbildungssemester,datum, orgform_kurzbz, 
								insertamum, insertvon, updateamum, updatevon, ext_id) 
								VALUES (".
								myaddslashes($prestudent_id).", ".
								myaddslashes($rolle).", ".
								myaddslashes($Kalender).", ".
								myaddslashes($row->instudiensemester).", 
								now(), ".
								myaddslashes($orgform).", 
								now(), 
								'SYNC', 
								NULL, 
								NULL, ".
								myaddslashes($row->__person).")";
							if(!$result_neu = pg_query($conn, $qry_ins))
							{
								$error_log.= $qry_ins."\n<strong>".pg_last_error($conn)." </strong>\n";
								$fehler++;
								pg_query($conn, "ROLLBACK");
							}
							else 
							{
								pg_query($conn, "COMMIT");
								$eingefuegt++;
							}
						}
						else 
						{
							pg_query($conn, "ROLLBACK");
						}
					}		
			}
			else
			{
				$dublette++;
			}
		}
		
	 	//echo "<br>*****<br>".$row->__person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname).", Studiengang ".$row->studiengang_kz;	
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

echo "<br><br>".date("d.m.Y H:i:s")."<br>";
/*
mail($adress, 'SYNC-Fehler StP-Prestudent von '.$_SERVER['HTTP_HOST'], $error_log,"From: vilesci@technikum-wien.at");

mail($adress, 'SYNC StP-Prestudent  von '.$_SERVER['HTTP_HOST'], "Sync Student\n------------\n\n"
."Personen: Gesamt: ".$anzahl_person_gesamt." / Eingefügt: ".$eingefuegt." / Fehler: ".$fehler." / Doppelt: ".$dublette
."\n\n".$dateiausgabe."Beginn: ".$start."\nEnde:   ".date("d.m.Y H:i:s")."\n\n".$ausgabe, "From: vilesci@technikum-wien.at");
*/

?>
</body>
</html>