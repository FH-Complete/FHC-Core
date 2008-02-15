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

/*if (!$conn_ext=mssql_connect (STPDB_SERVER, STPDB_USER, STPDB_PASSWD))
	die('Fehler beim Verbindungsaufbau!');
mssql_select_db(STPDB_DB, $conn_ext);*/

$conn=pg_connect(CONN_STRING)
	or die("Connection zur FH-Complete Datenbank fehlgeschlagen");

// Sync-Tabelle fuer Zugang checken
if (!@pg_query($conn,'SELECT * FROM sync.stp_zugang LIMIT 1;'))
{
	$sql='	CREATE TABLE sync.stp_zugang
			(
    			cxzugang integer NOT NULL,
    			zgv_code integer NOT NULL
			);
			REVOKE ALL ON TABLE stp_zugang FROM PUBLIC;
			GRANT INSERT,SELECT,UPDATE ON TABLE stp_zugang TO admin;
		';
	if (!@pg_query($conn,$sql))
		echo '<strong>sync.stp_zugang: '.pg_last_error($conn).' </strong><BR>';
	else
	{
		echo 'sync.stp_zugang wurde angelegt!<BR>';
	}
}


function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

$error_log='';
$error_log1='';
$error_log2='';
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
$update=0;
$eingefuegt1=0;
$fehler1=0;
$update1=0;
$plausi='';
$start='';
$stg='';
$aufmerksam=array();
$zgv=array();
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
<title>Synchro - StPoelten -> Vilesci - Prestudent</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>

<?php

if (!@pg_query($conn,'SELECT * FROM sync.tbl_syncperson LIMIT 1;'))
{
	die("<strong>sync.tbl_syncperson: ".pg_last_error($conn)." </strong><BR>");
}

$qry="SELECT * FROM public.tbl_aufmerksamdurch";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$aufmerksam[$row->ext_id]=$row->aufmerksamdurch_kurzbz;
	}
}
else
{
	echo "<br>".$qry."<br><strong>".pg_last_error($conn)." </strong><br>";
}
$qry="SELECT * FROM sync.stp_zugang";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$zgv[$row->cxzugang]=$row->zgv_code;
	}
}
else
{
	echo "<br>".$qry."<br><strong>".pg_last_error($conn)." </strong><br>";
}
$zgv[2]=99;
$zgv['']=99;

$ststat=array(2=>'Bewerber', 3=>'Student', 4=>'Ausserordentlicher', 5=>'Unterbrecher', 6=>'Absolvent', 7=>'Abbrecher', 8=>'Abgewiesener',
	10=>'Diplomand', 11=>'Diplomand', 12=>'Incoming');

//einlesen von studiendauer der stg in array
$qry="SELECT * FROM public.tbl_studiengang;";
if($result = pg_query($conn,$qry))
{
	while($row=pg_fetch_object($result))
	{
		$maxsemester[$row->studiengang_kz]=$row->max_semester;	
	}
}
	
	
//*********** Neue Daten holen *****************
$qry="SELECT __Person, datenquelle, inAusmassBesch, HoechsteAusbildung, _cxZugang, daMaturaDat,
	_cxZugangFHMag, daZugangFHMagDat, chtitel, chnachname, chvorname, studiengang_kz, typ,
	chKalenderSemStatAend, inStudiensemester, _cxstudstatus, _StgOrgForm
		FROM sync.stp_person JOIN sync.stp_stgvertiefung ON (_stgvertiefung=__stgvertiefung)
		JOIN public.tbl_studiengang ON (_studiengang=ext_id)
		WHERE __Person IN (SELECT __person FROM sync.tbl_syncperson) AND
		(_cxPersonTyp='1' OR _cxPersonTyp='2');";

//WHERE __Person IN (SELECT ext_id FROM tbl_person WHERE ext_id IS NOT NULL) AND
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
		$iu='';
		//plausi
		if($row->datenquelle=='' || $row->datenquelle==NULL)
		{
			$datenquelle=0;
		}
		else
		{
			$datenquelle=$row->datenquelle;
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
		//$row->chkalendersemstataend='W07';
		if($row->chkalendersemstataend=='' || $row->chkalendersemstataend==NULL)
		{
			$error_log1.="\nchkalendersemstataend nicht eingetragen !";//Fehlerausgabe, wenn kein wert eingetragen, da vor ws1999 wert noch nicht in db//15.02.2008
			$cont=true;
			$error=true;
		}
		if($row->_cxstudstatus=='3' || $row->_cxstudstatus=='4' || $row->_cxstudstatus=='9' || $row->_cxstudstatus=='10' || $row->_cxstudstatus=='11')
		{
			$row->chkalendersemstataend='W07';// Standardwert WS2007; von FH-StP gewünscht; 11.12.07
		}
		if($error)
		{
			$error=false;
			if($cont)
			{
				$error_log.="\n*****\n".$row->__person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname).": ".$error_log1;
				$error_log.="\n==>nicht übertragen!";
				$fehler++;
				$error_log1='';
				continue;
			}
			else
			{
				$error_log2.="\n*****\n".$row->__person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname).": ".$error_log1;
				$error_log2.="\n==>übertragen!";
				$error_log1='';
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
		$iu='';
		$qry_ins='';
		$rolle=$ststat[$row->_cxstudstatus];
		$Kalender=ucwords(substr($row->chkalendersemstataend,0,1)).'S'.((integer)substr($row->chkalendersemstataend,1,2)<11?'20':'19').substr($row->chkalendersemstataend,1,2);
		//echo substr($row->chkalendersemstataend,2,2)."/".$row->chkalendersemstataend."--->".$Kalender;
		pg_query($conn, "BEGIN");
		$qry_synk="SELECT * FROM sync.tbl_syncperson where __person=".$row->__person.";";
		$row_synk=pg_fetch_object(pg_query($conn, $qry_synk));
		$qry_chk="SELECT * FROM public.tbl_prestudent WHERE person_id=".myaddslashes($row_synk->person_id)." AND studiengang_kz=".myaddslashes($row->studiengang_kz).";";
		if($result_chk = pg_query($conn, $qry_chk))
		{
			if(pg_num_rows($result_chk)==0)
			{
				$iu='i';
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
			}
			else
			{
				$iu='u';
				if($row_chk=pg_fetch_object($result_chk))
				{
					$qry_ins='';
					$log_qry_ins='';
					if ($row_chk->aufmerksamdurch_kurzbz!=$aufmerksam[$datenquelle])
					{
						$qry_ins.=" aufmerksamdurch_kurzbz=".myaddslashes($aufmerksam[$datenquelle]).",";
						$log_qry_ins=$row_chk->aufmerksamdurch_kurzbz."/".myaddslashes($aufmerksam[$datenquelle]);
					}
					if ($row_chk->ausbildungcode!=$row->hoechsteausbildung)
					{
						$qry_ins.=" ausbildungcode=".myaddslashes($row->hoechsteausbildung).", ";
						$log_qry_ins.=", ".$row_chk->ausbildungcode."/".myaddslashes($row->hoechsteausbildung);
					}
					if ($row_chk->zgv_code!=$zgv[$row->_cxzugang])
					{
						$qry_ins.=" zgv_code=".myaddslashes($zgv[$row->_cxzugang]).", ";
						$log_qry_ins.=", ".$row_chk->zgv_code."/".myaddslashes($zgv[$row->_cxzugang]);
					}
					if ($row_chk->zgvdatum!=$row->damaturadat)
					{
						$qry_ins.=" zgvdatum=".myaddslashes($row->damaturadat).", ";
						$log_qry_ins.=", ".$row_chk->zgvdatum."/".myaddslashes($row->damaturadat);
					}
					if ($row_chk->zgvmas_code!=$row->_cxzugangfhmag)
					{
						$qry_ins.=" zgvmas_code=".myaddslashes($row->_cxzugangfhmag).", ";
						$log_qry_ins.=", ".$row_chk->zgvmas_code."/".myaddslashes($row->_cxzugangfhmag);
					}
					if ($row_chk->zgvmadatum!=$row->dazugangfhmagdat)
					{
						$qry_ins.=" zgvmadatum=".myaddslashes($row->dazugangfhmagdat).", ";
						$log_qry_ins.=", ".$row_chk->zgvmadatum."/".myaddslashes($row->dazugangfhmagdat);
					}
					if($qry_ins!='')
					{
						$qry_ins="UPDATE public.tbl_prestudent SET".$qry_ins." updateamum=now(), updatevon='SYNC' WHERE person_id=".myaddslashes($row_synk->person_id)." AND studiengang_kz=".myaddslashes($row->studiengang_kz).";";
					}
				}
			}
			if($qry_ins!='')
			{
				if(!$result_neu = pg_query($conn, $qry_ins))
				{
					$error_log.= $qry_ins."\n<strong>".pg_last_error($conn)." </strong>\n";
					$fehler++;
					pg_query($conn, "ROLLBACK");
				}
				else
				{
					if($iu=='i')
					{
						$ausgabe.="\n------------------\nÜbertragen: ".$row->__person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname).", Stg: ".$row->studiengang_kz;
						$eingefuegt++;
						//Prestudent_id ermitteln
						$qry_seq = "SELECT currval('public.tbl_prestudent_prestudent_id_seq') AS id;";
						if($row_seq=pg_fetch_object(pg_query($conn,$qry_seq)))
						{
							$prestudent_id=$row_seq->id;
						}
						else
						{
							$error=true;
							pg_query($conn, "ROLLBACK");
							$error_log.='Prestudent-Sequence konnte nicht ausgelesen werden\n';
						}
					}
					elseif($iu=='u')
					{
						$ausgabe.="\n------------------\nGeändert: ".$row->__person." - ".trim($row->chtitel)." ".trim($row->chnachname).", ".trim($row->chvorname).", Stg: ".$row->studiengang_kz."\n---".$qry_ins."\n---".$log_qry_ins;
						$prestudent_id=$row_chk->prestudent_id;
						$update++;
					}
					$iu='';

					if(!$error)
					{
						if($row->instudiensemester>$maxsemester[$row->studiengang_kz])
						{
							$row->instudiensemester=$maxsemester[$row->studiengang_kz];
						}
						$qry_ins='';
						$qry_status="SELECT * FROM public.tbl_prestudentrolle WHERE prestudent_id='".$prestudent_id."' AND rolle_kurzbz='".$rolle."' AND studiensemester_kurzbz='".$Kalender."' AND ausbildungssemester='".$row->instudiensemester."';";
						$result_status=pg_query($conn,$qry_status);
						if(pg_num_rows($result_status)==0)
						{
							$iu='i';
							$qry_ins="INSERT INTO public.tbl_prestudentrolle (prestudent_id, rolle_kurzbz,
								studiensemester_kurzbz, ausbildungssemester,datum, orgform_kurzbz,
								insertamum, insertvon, updateamum, updatevon, ext_id)
								VALUES (".
								myaddslashes($prestudent_id).", ".
								myaddslashes($rolle).", ".
								myaddslashes($Kalender).", ";
								//max.studiendauer wenn ausbildungssemester größer (v.a. für 50er und 60er)
								if($row->instudiensemester>$maxsemester[$row->studiengang_kz])
								{
									$qry_ins.=myaddslashes($maxsemester[$row->studiengang_kz]);
								}
								else 
								{
									$qry_ins.=myaddslashes($row->instudiensemester);
								}
								$qry_ins.=", now(), ".
								myaddslashes($orgform).",
								now(),
								'SYNC',
								NULL,
								NULL, ".
								myaddslashes($row->__person).")";
						}
						else
						{
							$iu='u';
							if($row_status=pg_fetch_object($result_status))
							{
								$qry_ins='';
								if ($row_status->orgform_kurzbz!=$orgform)
									$qry_ins.="orgform_kurzbz=".myaddslashes($orgform).", ";
									
								if($row->instudiensemester>$maxsemester[$row->studiengang_kz])
									$row->instudiensemester=$maxsemester[$row->studiengang_kz];
								if ($row_status->ausbildungssemester!=$row->instudiensemester)
									$qry_ins.="ausbildungssemester=".myaddslashes($row->instudiensemester).", ";
								if($qry_ins!='')
								{
									$qry_ins.="UPDATE public.tbl_prestudentrolle SET ".$qry_ins."updateamum=now(), updatevon='SYNC' WHERE prestudent_id='".$prestudent_id."' AND rolle_kurzbz='".$rolle."' AND studiensemester_kurzbz='".$Kalender."';";
								}
							}
						}
						if($qry_ins!='')
						{
							if(!$result_neu = pg_query($conn, $qry_ins))
							{
								$error_log.= $qry_ins."\n<strong>".pg_last_error($conn)." </strong>\n";
								$fehler1++;
								pg_query($conn, "ROLLBACK");
							}
							else
							{
								pg_query($conn, "COMMIT");
								if($iu=='i')
								{
									$ausgabe.="\n---Rolle eingefügt: ".$rolle." im Studiensemester ".$Kalender." und Ausbildungssemeser ".$row->instudiensemester." (OrgForm ".$orgform.");";
									$eingefuegt1++;
								}
								elseif($iu=='u')
								{
									$ausgabe.="\n---Rolle geändert: ".$rolle." im Studiensemester ".$Kalender." und Ausbildungssemeser ".$row->instudiensemester." (OrgForm ".$orgform.");";
									$update1++;
								}
							}
						}
					}
				}
			}
		}
		else
		{
			echo "<br>".$qry_chk."<br><strong>".pg_last_error($conn)." </strong><br>";
		}
	}
}
else
{
	echo "<br>".$qry."<br><strong>".pg_last_error($conn)." </strong><br>";
}
echo "<br><b>Prestudent:</b>";
echo "<br>Eingefügt:  ".$eingefuegt;
echo "<br>Geändert:     ".$update;
echo "<br>Fehler:       ".$fehler;
echo "<br><b>Prestudent-Rolle:</b>";
echo "<br>Eingefügt:  ".$eingefuegt1;
echo "<br>Geändert:     ".$update1;
echo "<br>Fehler:       ".$fehler1;
echo "<br><br>";
echo nl2br($error_log."\n----------------------------------------------------------------------------------------------------\n".$error_log2);
echo nl2br($ausgabe);

echo "<br><br>".date("d.m.Y H:i:s")."<br>";

mail($adress, 'SYNC-Fehler StP-Prestudent von '.$_SERVER['HTTP_HOST'], $error_log."\n---------------------------------------------------------\n"
.$error_log2,"From: nsc@fhstp.ac.at");

mail($adress, 'SYNC StP-Prestudent  von '.$_SERVER['HTTP_HOST'], "Sync Student\n------------\n\n"
."Prestudenten:      Gesamt: ".$anzahl_person_gesamt." / Eingefügt: ".$eingefuegt." / Fehler: ".$fehler." / Geändert: ".$update
."\nPrestudentrollen:  Gesamt: ".$anzahl_person_gesamt." / Eingefügt: ".$eingefuegt1." / Fehler: ".$fehler1." / Geändert: ".$update1
."\n\nBeginn: ".$start."\nEnde:   ".date("d.m.Y H:i:s")."\n\n".$ausgabe, "From: nsc@fhstp.ac.at");


?>
</body>
</html>