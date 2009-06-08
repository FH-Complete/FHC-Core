<?php
/* Copyright (C) 2008 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Prestudentrollendatensaetze von FAS DB in PORTAL DB
//*
//*
//* setzt voraus: sync von sync.stp_person, sync.stp_staat
//* benoetigt: tbl_syncperson, tbl_studiensemester

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
$studiensemester=array();
$maxsemester=array();
$semdiff=0;
$instg='';
$status_kurzbz='';

$i=0;
/*************************
 * StP-PORTAL - Synchronisation
 */
?>
<html>
<head>
<title>Check - StPoelten - Prestudentrollen (Lückenfüller)</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>

<?php


$start=date("d.m.Y H:i:s");
//einlesen von studiensemesterdaten in arrays
$qry="SELECT * FROM public.tbl_studiensemester ORDER BY start;";
if($result = pg_query($conn,$qry))
{
	while($row=pg_fetch_object($result))
	{
		$studiensemester[$i]=$row->studiensemester_kurzbz;
		$semstart[$row->studiensemester_kurzbz]=$row->start;
		$semende[$row->studiensemester_kurzbz]=$row->ende;
		$i++;	
	}
}
//einlesen von studiendauer der stg in array
$qry="SELECT * FROM public.tbl_studiengang;";
if($result = pg_query($conn,$qry))
{
	while($row=pg_fetch_object($result))
	{
		$maxsemester[$row->studiengang_kz]=$row->max_semester;	
	}
}

//$adress="ruhan@technikum-wien.at";

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
		//echo nl2br("\n".trim($rowall->chtitel)." ".trim($rowall->chnachname).", ".trim($rowall->chvorname));
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
		$qry_pre="SELECT * FROM public.tbl_prestudent WHERE prestudent_id=".myaddslashes($rowall->prestudent_id).";";
		if($resultpre = pg_query($conn,$qry_pre))
		{
			if($rowpre=pg_fetch_object($resultpre))
			{
				//Studiengang des aktuellen prestudenten
				$instg=$rowpre->studiengang_kz;
			}
		}
		else 
		{
			$fehler++;
			$error_log.= "\n".$qry_rl."\n".pg_last_error($conn)."\n";
		}
		$qry_rl="SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id=".myaddslashes($rowall->prestudent_id)." ORDER BY datum desc LIMIT 1";
		if($resultrl = pg_query($conn,$qry_rl))
		{
			if($rowrl=pg_fetch_object($resultrl))
			{
				$beginnsem=getStudiensemesterFromDatum($conn, $rowall->daeintrittdat, true);
				if($rowrl->ausbildungssemester==NULL || $rowrl->ausbildungssemester=='')
				{
					$error_log.="\n*****\n".$rowall->__person." - ".trim($rowall->chtitel)." ".trim($rowall->chnachname).", ".trim($rowall->chvorname)." (Prestudent ".$rowall->prestudent_id.")";
					$error_log.="\nAusbildungssemester = ".$rowrl->ausbildungssemester." !";
					$fehler++;	
				}
				else 
				{
					//wieviele semester seit studienbeginn
					$semdiff=array_search($rowrl->studiensemester_kurzbz, $studiensemester)-array_search($beginnsem, $studiensemester)+1;	
					if($semdiff<1)
					{
						$semdiff=1;
					}
					$ausgabe.="\n*****\n".$rowall->__person." - ".trim($rowall->chtitel)." ".trim($rowall->chnachname).", ".trim($rowall->chvorname).": ".$rowall->daeintrittdat."/".$beginnsem." (Prestudent ".$rowall->prestudent_id.")";
					$ausgabe.="\nAusbildungssemester = ".$rowrl->ausbildungssemester." / semdiff= ".$semdiff.".";
					while (array_search($rowrl->studiensemester_kurzbz, $studiensemester)>=array_search($beginnsem, $studiensemester))
					{
						if($semdiff>=$maxsemester[$instg])
						{
							$status_kurzbz="Diplomand";
						}
						else
						{
							$status_kurzbz="Student";
						}
						if($semdiff>$maxsemester[$instg])
						{
							$ausbsem=$maxsemester[$instg];
						}
						else 
						{
							$ausbsem=$semdiff;
						}
						$qry_chk="SELECT * FROM public.tbl_prestudentstatus 
						WHERE prestudent_id=".myaddslashes($rowall->prestudent_id)." 
						AND studiensemester_kurzbz=".myaddslashes($rowrl->studiensemester_kurzbz)." 
						AND status_kurzbz=".myaddslashes($status_kurzbz)." 
						AND ausbildungssemester=".myaddslashes($ausbsem).";";
						
						if($resultchk = pg_query($conn,$qry_chk))
						{
							if(pg_num_rows($resultchk)==0)
							{
								//INSERT Prestudentrolle
								if($rowrl->studiensemester_kurzbz==$beginnsem)
								{
									$rowrl->datum=$rowall->daeintrittdat;	
								}
								$qry_ins="INSERT INTO public.tbl_prestudentstatus (prestudent_id, status_kurzbz,
									studiensemester_kurzbz, ausbildungssemester,datum, orgform_kurzbz,
									insertamum, insertvon, updateamum, updatevon, ext_id)
									VALUES (".
									myaddslashes($rowall->prestudent_id).", ".
									myaddslashes($status_kurzbz).", ".
									myaddslashes($rowrl->studiensemester_kurzbz).", ";
									//wenn semdiff>maxsemester dann maxsemester sonst semdiff
									if($semdiff>$maxsemester[$instg])
									{
										$qry_ins.=myaddslashes($maxsemester[$instg]).", ";
									}
									else 
									{
										$qry_ins.=myaddslashes($semdiff).", ";
									}
									$qry_ins.=myaddslashes($rowrl->datum).", ".
									myaddslashes($rowrl->orgform_kurzbz).",
									now(), 	'SYNC', NULL, NULL, NULL)";
								if(!$resultins = pg_query($conn,$qry_ins))
								{
									$fehler++;
									$error_log.="\n*****\n".$rowall->__person." - ".trim($rowall->chtitel)." ".trim($rowall->chnachname).", ".trim($rowall->chvorname).": ".$rowall->daeintrittdat;
									$error_log.= "\n".$qry_ins."\n".pg_last_error($conn)."\n";	
								}
								else 
								{
									$eingefuegt++;
									//$ausgabe.="\n---".$qry_ins;
									$ausgabe.="\n---".$rowrl->studiensemester_kurzbz.": ".($semdiff>=$maxsemester[$instg]?'Diplomand':'Student').", ".($semdiff>$maxsemester[$instg]?$maxsemester[$instg]:$semdiff).".Semester, Datum: ".$rowrl->datum.", Orgform: ".$rowrl->orgform_kurzbz.";";
								}
							}
							else 
							{
								$qry_chk='';
								if($row_chk=pg_fetch_object($resultchk))
								{
									if($semdiff>=$maxsemester[$instg])
									{
										if($row_chk->status_kurzbz!='Diplomand')
										{
											$qry_chk="status_kurzbz='Diplomand', ";
										}
									}
									else
									{
										if($row_chk->status_kurzbz!='Student')
										{
											$qry_chk="status_kurzbz='Student', ";
										}
									}
									if($semdiff>$maxsemester[$instg])
									{
										if($row_chk->ausbildungssemester!=$maxsemester[$instg])
										{
											$qry_chk.="ausbildungssemester=".myaddslashes($maxsemester[$instg]).", ";
										}
									}
									else 
									{
										if($row_chk->ausbildungssemester!=$semdiff)
										{
											$qry_chk.="ausbildungssemester=".myaddslashes($semdiff).", ";
										}
									}
									if($row_chk->datum!=$rowrl->datum)
									{
										$qry_chk.="datum=".myaddslashes($rowrl->datum).", ";
									}
									if($row_chk->orgform_kurzbz!=$rowrl->orgform_kurzbz)
									{
										$qry_chk.="orgform_kurzbz=".myaddslashes($rowrl->orgform_kurzbz).", ";
									}
									if($qry_chk!='')
									{
										$qry_upd="UPDATE public.tbl_prestudentstatus SET ".$qry_chk."updateamum=now(), updatevon='sync' 
										WHERE  prestudent_id=".myaddslashes($rowall->prestudent_id)." 
										AND studiensemester_kurzbz=".myaddslashes($rowrl->studiensemester_kurzbz)." 
										AND status_kurzbz=".myaddslashes($status_kurzbz)." 
										AND ausbildungssemester=".$ausbsem.";";
										if(!$resultins = pg_query($conn,$qry_upd))
										{
											$fehler++;
											$error_log.="\n*****\n".$rowall->__person." - ".trim($rowall->chtitel)." ".trim($rowall->chnachname).", ".trim($rowall->chvorname).": ".$rowall->daeintrittdat;
											$error_log.= "\n".$qry_upd."\n".pg_last_error($conn)."\n";	
										}
										else 
										{
											$eingefuegt++;
											//$ausgabe.="\n---".$qry_ins;
											$ausgabe.="\n---".$qry_chk.";";
										}
									}
									else 
									{
										$ausgabe.="\nEintrag in Semester ".$rowrl->studiensemester_kurzbz." bereits vorhanden!";
									}
								}
							}
						}
						//studiensemester, ausbildungssemester, datum
						if(array_search($rowrl->studiensemester_kurzbz, $studiensemester)-1>=array_search($beginnsem,$studiensemester))
						{
							$rowrl->studiensemester_kurzbz=$studiensemester[array_search($rowrl->studiensemester_kurzbz, $studiensemester)-1];
							//ausbildungssemester nicht kleiner als 1
							if($semdiff>1)
							{
								//$rowrl->ausbildungssemester=$rowrl->ausbildungssemester-1;
								$semdiff=$semdiff-1;
							}
							else 
							{
								//$rowrl->ausbildungssemester=1;
								$semdiff=1;
							}
							$rowrl->datum=$semstart[$rowrl->studiensemester_kurzbz];
						}
						else 
						{
							break;
						}
					}
					//Interessent (6Mon.) und Bewerber (4Mon.) eintragen
					if($rowrl->studiensemester_kurzbz==$beginnsem)
					{
						$rowrl->datum=$rowall->daeintrittdat;	
					}
					$qry_chk="SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id=".myaddslashes($rowall->prestudent_id)." AND status_kurzbz='Bewerber';";
					if($resultchk = pg_query($conn,$qry_chk))
					{
						if(pg_num_rows($resultchk)==0)
						{
							$qry_ins="INSERT INTO public.tbl_prestudentstatus (prestudent_id, status_kurzbz,
								studiensemester_kurzbz, ausbildungssemester,datum, orgform_kurzbz,
								insertamum, insertvon, updateamum, updatevon, ext_id)
								VALUES (".
								myaddslashes($rowall->prestudent_id).", 
								'Bewerber', ".
								myaddslashes($rowrl->studiensemester_kurzbz).", ".
								myaddslashes($semdiff).", ".
								myaddslashes(date('Y-m-d', strtotime($rowrl->datum.' -4 months'))).", ".
								myaddslashes($rowrl->orgform_kurzbz).",
								now(), 	'SYNC', NULL, NULL, NULL)";
							if(!$resultins = pg_query($conn,$qry_ins))
							{
								$fehler++;
								$error_log.="\n*****\n".$rowall->__person." - ".trim($rowall->chtitel)." ".trim($rowall->chnachname).", ".trim($rowall->chvorname).": ".$rowall->daeintrittdat;
								$error_log.= "\n".$qry_ins."\n<strong>".pg_last_error($conn)." </strong>\n";	
							}
							else 
							{
								$eingefuegt++;
								$ausgabe.="\n---".$rowrl->studiensemester_kurzbz.": Bewerber, ".$semdiff.".Semester , Datum: ".date('Y-m-d', strtotime($rowrl->datum.' -4 months')).", Orgform: ".$rowrl->orgform_kurzbz.";";
								//$ausgabe.="\n".$qry_ins;
							}
						}
					}
					$qry_chk="SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id=".myaddslashes($rowall->prestudent_id)." AND status_kurzbz='Interessent';";
					if($resultchk = pg_query($conn,$qry_chk))
					{
						if(pg_num_rows($resultchk)==0)
						{
							$qry_ins="INSERT INTO public.tbl_prestudentstatus (prestudent_id, status_kurzbz,
									studiensemester_kurzbz, ausbildungssemester,datum, orgform_kurzbz,
									insertamum, insertvon, updateamum, updatevon, ext_id)
									VALUES (".
									myaddslashes($rowall->prestudent_id).", 
									'Interessent', ".
									myaddslashes($rowrl->studiensemester_kurzbz).", ".
									myaddslashes($semdiff).", ".
									myaddslashes(date('Y-m-d', strtotime($rowrl->datum.' -6 months'))).", ".
									myaddslashes($rowrl->orgform_kurzbz).",
									now(), 	'SYNC', NULL, NULL, NULL)";
							if(!$resultins = pg_query($conn,$qry_ins))
							{
								$fehler++;
								$error_log.="\n*****\n".$rowall->__person." - ".trim($rowall->chtitel)." ".trim($rowall->chnachname).", ".trim($rowall->chvorname).": ".$rowall->daeintrittdat;
								$error_log.= "\n".$qry_ins."\n<strong>".pg_last_error($conn)." </strong>\n";	
							}
							else 
							{
								$update++;
								$ausgabe.="\n---".$rowrl->studiensemester_kurzbz.": Interessent, ".$semdiff.".Semester , Datum: ".$new_date = date('Y-m-d', strtotime($rowrl->datum.' -6 months')).", Orgform: ".$rowrl->orgform_kurzbz.";";

							}
						}
					}
				}
			}
		}
		else 
		{
			$fehler++;
			$error_log.= "\n".$qry_rl."\n".pg_last_error($conn)."\n";
		}
	}
}
echo nl2br($ausgabe);

mail($adress, 'SYNC-Fehler StP-Prestudentrollen von '.$_SERVER['HTTP_HOST'], $error_log,"From: nsc@fhstp.ac.at");

mail($adress, 'SYNC StP-Prestudentrollen  von '.$_SERVER['HTTP_HOST'], "Sync Person\n------------\n\n"
."Personen: Gesamt: ".$anzahl_gesamt." / Fehler: ".$fehler." / Eingefügte Rollen: ".$eingefuegt." Geändert: ".$update
."\n\nBeginn:  ".$start."\nEnde:    ".date("d.m.Y H:i:s")."\n\n".$ausgabe, "From: nsc@fhstp.ac.at");
?>
</body>
</html>