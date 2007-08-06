<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Diplomarbeitsdatensaetze von FAS DB in PORTAL DB
//*
//*

require_once('../../../vilesci/config.inc.php');
require_once('../sync_config.inc.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

//$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$error_log_fas1='';
$error_log_fas2='';
$error_log_fas3='';
$error_log_fas4='';
$error_log_fas5='';
$error_log_fas6='';
$error_log_fas7='';
$error_log_fas8='';
$error_log_fas9='';
$error_log_fas10='';
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
$anzahl_fehler_pbb2=0;
$anzahl_fehler_pbg=0;
$anzahl_fehler_pbg2=0;
$anzahl_le_gesamt=0;
$anzahl_le_insert=0;
$anzahl_le_update=0;
$anzahl_pa_gesamt=0;
$anzahl_pa_insert=0;
$anzahl_pa_update=0;
$anzahl_pbb_update=0;
$anzahl_pbb_gesamt=0;
$anzahl_pbb_insert=0;
$anzahl_pbb2_gesamt=0;
$anzahl_pbb2_insert=0;
$anzahl_pbb2_update=0;
$anzahl_pbg_gesamt=0;
$anzahl_pbg_insert=0;
$anzahl_pbg_update=0;
$anzahl_pbg2_gesamt=0;
$anzahl_pbg2_insert=0;
$anzahl_pbg2_update=0;
$fachbereich_kurzbz='';
$ausgabe='';
$ausgabe_all='';
$ausgabe_le='';
$ausgabe_pa='';
$ausgabe_pb='';
$text1='';
$text2='';
$text3='';
$text4='';
$text5='';
$text6='';
$text7='';
$text8='';
$text9='';
$text10='';
$projektbetreuernew1=false;
$updatep1='';
$projektbetreuernew2=false;
$updatep2='';
$projektbetreuernew3=false;
$updatep3='';
$projektbetreuernew3=false;
$updatep3='';
$projektbetreuernew4=false;
$updatep4='';


$noz=0;
$noe=0;
$no1=0;
$no2=0;


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
<title>Synchro - FAS -> Portal - Diplomarbeit</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
//nation
$qry="SELECT count(*) FROM lehre.tbl_lehrveranstaltung WHERE bezeichnung='Diplomarbeit';";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$anzahl_lv_gesamt=$row->count;
	}
}
$qry = "SELECT * FROM diplomarbeit WHERE diplomarbeitsdatum IS NOT NULL;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Diplomarbeit Sync\n------------------------\n");
	echo nl2br("Diplomarbeitsynchro Beginn: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$text1='';
		$text2='';
		$text3='';
		$text4='';
		$text5='';
		$text6='';
		$text7='';
		$text8='';
		$error_log='';
		$error=false;
		$projektarbeitprojekttyp_kurzbz	='Diplom';
		$projektarbeittitel			=$row->diplomarbeitsthema;
		//$projektarbeitlehreinheit_id	='';
		//$projektarbeitstudent_uid	='';
		$projektarbeitfirma_id		='';
		$projektarbeitnote			='';
		$projektarbeitpunkte	 		=number_format((float)$row->punkteerstbegutachter + (float)$row->punktezweitbegutachter, 2, '.', '');
		$projektarbeitbeginn			='';
		$projektarbeitende			=$row->diplomarbeitsdatum;
		$projektarbeitfaktor			='1.0';
		$projektarbeitfreigegeben		=$row->freigegeben;
		$projektarbeitgesperrtbis		=$row->gesperrtbis;
		$projektarbeitstundensatz		=$row->kosten;
		$projektarbeitgesamtstunden	=number_format($row->betreuungsstunden, 0, '.', '');
		$projektarbeitthemenbereich	='';
		$projektarbeitanmerkung		='';		
		//$projektarbeitupdateamum	=$row->;
		$projektarbeitupdatevon		="SYNC";
		$projektarbeitinsertamum		=$row->creationdate;
		//$projektarbeitinsertvon		=$row->;
		$projektarbeitext_id			=$row->diplomarbeit_pk;
		
		if(trim(strtoupper($row->diplomarbeitgesamtnote))=='SEHR GUT' || trim($row->diplomarbeitgesamtnote)=='1')
		{
			$projektarbeitnote=1;	
		}
		elseif(trim(strtoupper($row->diplomarbeitgesamtnote))=='GUT' || trim($row->diplomarbeitgesamtnote)=='2')
		{
			$projektarbeitnote=2;
		}
		elseif(trim(strtoupper($row->diplomarbeitgesamtnote))=='BEFRIEDIGEND' || trim($row->diplomarbeitgesamtnote)=='3')
		{
			$projektarbeitnote=3;
		}
		elseif(trim(strtoupper($row->diplomarbeitgesamtnote))=='GENÜGEND' || trim($row->diplomarbeitgesamtnote)=='4')
		{
			$projektarbeitnote=4;
		}
		elseif(trim(strtoupper($row->diplomarbeitgesamtnote))=='NICHT GENÜGEND' || trim($row->diplomarbeitgesamtnote)=='5')
		{
			$projektarbeitnote=5;
		}
		elseif($row->diplomarbeitgesamtnote==NULL || trim($row->diplomarbeitgesamtnote)=='')
		{
			$projektarbeitnote=null;
		}
		elseif(trim($row->diplomarbeitgesamtnote)=='1,5' || trim($row->diplomarbeitgesamtnote)=='2,5' || trim($row->diplomarbeitgesamtnote)=='3,5')
		{
			$projektarbeitnote=round(0+trim($row->diplomarbeitgesamtnote));
			$projektarbeitanmerkung="Diplomarbeitsnote ursprünglich ".trim($row->diplomarbeitgesamtnote)."!";
		}
		//$lehreinheitlehrveranstaltung_id	='';
		//$lehreinheitstudiensemester_kz	='';
		//$lehreinheitlehrfach_id			='';
		$lehreinheitlehrform_kurzbz			='BE';
		$lehreinheitstundenblockung		='1';
		$lehreinheitwochenrythmus			='1';
		$lehreinheitstart_kw				='';
		$lehreinheitraumtyp				='DIV';
		$lehreinheitraumtypalternativ		='DIV';
		$lehreinheitsprache				='German';
		$lehreinheitlehre				=false;
		$lehreinheitanmerkung			='Diplomarbeit';
		$lehreinheitunr				='';
		$lehreinheitlvnr				='';
		//$lehreinheitupdateamum			='';
		$lehreinheitupdatevon			="SYNC";
		$lehreinheitinsertamum			=$row->creationdate;
		//$lehreinheitinsertvon			=$row->creationuser;
		$lehreinheitext_id				=$row->diplomarbeit_pk;
			
		$studiengang_kz='';
		$semester='';
		$lehrveranstaltung_id='';
		
		
		
		$qrycu="SELECT name FROM public.benutzer WHERE benutzer_pk='".$row->creationuser."';";
		if($resultcu = pg_query($conn_fas, $qrycu))
		{
			if($rowcu=pg_fetch_object($resultcu))
			{
				$lehreinheitinsertvon=$rowcu->name;
				$projektarbeitinsertvon=$rowcu->name;
				$projektbetreuerinsertvon=$rowcu->name;
			}
		}
		
		//student_id ermitteln
		$qry="SELECT student_uid FROM public.tbl_student WHERE ext_id='$row->student_fk';";
		if($resulto = pg_query($conn, $qry))
		{
			if($rowo=pg_fetch_object($resulto))
			{ 
				$projektarbeitstudent_uid=$rowo->student_uid;
			}
			else {
				$error=true;
				$error_log.="Student mit student_fk: $row->student_fk konnte nicht gefunden werden.\n";
			}
		}
		
		//lehrveranstaltung
		//studiengang_kz über student ermitteln
		$qry="SELECT studiengang_fk FROM public.student WHERE student_pk='".$row->student_fk."';";
		if($results = pg_query($conn_fas, $qry))
		{
			if($rows=pg_fetch_object($results))
			{ 
				$qry="SELECT studiengang_kz, max_semester FROM public.tbl_studiengang WHERE ext_id='".$rows->studiengang_fk."';";
				if($resulte = pg_query($conn, $qry))
				{
					if($rowe=pg_fetch_object($resulte))
					{ 
						$lehrveranstaltungstudiengang_kz=$rowe->studiengang_kz;
						$lehrveranstaltungmax_semester=$rowe->max_semester;
					}
					else 
					{
						$error=true;
						$error_log="Studiengang_kz von Studiengang mit studiengang_fk '".$rows->studiengang_fk."' nicht gefunden!";
					}
				}
			}
			else 
			{
				$error=true;
				$error_log="Studiengang von Student '".$row->student_fk."' nicht gefunden!";
			}
		}
		
		$qry="SELECT * FROM lehre.tbl_lehrveranstaltung WHERE bezeichnung='Diplomarbeit' 
			AND studiengang_kz='".$lehrveranstaltungstudiengang_kz."' 
			AND semester='".$lehrveranstaltungmax_semester."';";
		if($results = pg_query($conn, $qry))
		{
			if($rows=pg_fetch_object($results))
			{ 
				$lehreinheitlehrveranstaltung_id=$rows->lehrveranstaltung_id;	
			}
			else 
			{
				$qry="INSERT INTO lehre.tbl_lehrveranstaltung (kurzbz, bezeichnung, studiengang_kz, semester, sprache, ects, semesterstunden,".
					"anmerkung, lehre, lehreverzeichnis, aktiv, planfaktor, planlektoren, planpersonalkosten, plankostenprolektor,".
					"updateamum, updatevon, insertamum, insertvon, ext_id) VALUES (".
					"'DIPL', 'Diplomarbeit', ".
					$lehrveranstaltungstudiengang_kz.", ".
					$lehrveranstaltungmax_semester.", ".
					"'German', ".
					"'0', ".
					"'0', ".
					"'', ".
					"false, ".
					"NULL,".
					"true, ".
					"'1.0', ".
					"'0', ".
					"'0', ".
					"'0', ".
					"now(), 'SYNC', now(), 'SYNC', NULL);";
				if(!pg_query($conn,$qry))
				{
					$error=true;
					$error_log.= "*****\nFehler beim Anlegen einer Lehrveranstaltung\n   ".$qry."\n";
				}
				else 
				{
					$anzahl_lv_insert++;
					$anzahl_lv_gesamt++;
					$qry = "SELECT currval('lehre.tbl_lehrveranstaltung_lehrveranstaltung_id_seq') AS id;";
					if($rowu=pg_fetch_object(pg_query($conn,$qry)))
						$lehreinheitlehrveranstaltung_id=$rowu->id;
					else
					{					
						$error=true;
						$error_log.="Lehrveranstaltung-Sequence konnte nicht ausgelesen werden.\n";
					}
					$ausgabe.="Lehrveranstaltung angelegt: Studiengang '".$lehrveranstaltungstudiengang_kz."' und Semester '".$lehrveranstaltungmax_semester."'\n";
				}
			}
			$studiengang_kz=$lehrveranstaltungstudiengang_kz;
			$semester=$lehrveranstaltungmax_semester;
		}
		else 
		{
			$error=true;
			$error_log.= "*****\nFehler beim Zugriff auf Tabelle lehre.tbl_lehrveranstaltung.\n   ".$qry."\n";
		}
		if(!$error)
		{
			/*$qry="SELECT fachbereich_kurzbz FROM public.tbl_fachbereich WHERE ext_id='$row->fachbereich_fk'";
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
			}*/
			if(!$error)
			{
				$qry="SELECT lehrfach_id FROM lehre.tbl_lehrfach WHERE fachbereich_kurzbz='Praxissemester u' AND semester='".$semester."' AND studiengang_kz='".$studiengang_kz."';";
				if($resulto = pg_query($conn, $qry))
				{
					if($rowo=pg_fetch_object($resulto))
					{ 
						$lehreinheitlehrfach_id=$rowo->lehrfach_id;
					}
					else 
					{
						$qry="INSERT INTO lehre.tbl_lehrfach (studiengang_kz, fachbereich_kurzbz, kurzbz, bezeichnung, farbe, aktiv, ".
						"semester, sprache, updateamum, updatevon, insertamum, insertvon, ext_id) VALUES (".
						myaddslashes($studiengang_kz).", ".
						"'Praxissemester u', ".
						"'DIPS', ".
						"'Betreuung von Diplom- , Bachelor- und Projektarbeiten', ".
						"'DED8FE', ".
						"true ,".
						myaddslashes($semester).", ".
						"'German', ".
						"now(), ".
						"'SYNC', ".
						"now(), ".
						"'SYNC', ".
						"NULL);";
						//echo nl2br($qry."\n");
						if($resulto = pg_query($conn, $qry))
						{
							$ausgabe.="Lehrfach angelegt mit Fachbereich='Praxissemester u', Semester='".$semester."' und Studiengang='".$studiengang_kz."'.\n";
							//sequenz auslesen für $lehreinheitlehrfach_id
							$qry= "SELECT currval('lehre.tbl_lehrfach_lehrfach_id_seq') AS id;";
							if($rowseq=pg_fetch_object(pg_query($conn,$qry)))
								$lehreinheitlehrfach_id=$rowseq->id;
							else
							{					
								$error_log.= "Sequence von ".$semester.", ".$studiengang_kz." konnte nicht ausgelesen werden\n".$qry."\n";
								$error=true;
							}
						}
						else 
						{
							$error=true;
							$error_log.="Fehler beim Einfügen des Lehrfachs mit Fachbereich='Praxissemester u', Semester='".$semester."' und Studiengang='".$studiengang_kz."'.\n";
						}
					}
				}
				//Datum der DA in welchem Sem?
				$qry="SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE '".$row->diplomarbeitsdatum."'>start ORDER BY ext_id desc;";
				if($resulto = pg_query($conn, $qry))
				{
					if($rowo=pg_fetch_object($resulto))
					{ 
						$lehreinheitstudiensemester_kurzbz=$rowo->studiensemester_kurzbz;
					}
					else 
					{
						$error=true;
						$error_log.="Studiensemester für Diplomarbeitsdatum '".$row->diplomarbeitsdatum."' nicht gefunden!\n";
					}
				}
				
				$qry3="SELECT * FROM lehre.tbl_projektarbeit WHERE projekttyp_kurzbz='Diplom' AND ext_id='".$row->diplomarbeit_pk."';";
				if($result3 = pg_query($conn, $qry3))
				{
					if(pg_num_rows($result3)>0) //eintrag gefunden
					{
						if($row3=pg_fetch_object($result3))
						{ 
							// update, wenn datensatz bereits vorhanden
							$projektarbeitnew=false;
							$projektarbeitprojektarbeit_id=$row3->projektarbeit_id;
						}
					}
					else 
					{
						// insert, wenn datensatz noch nicht vorhanden
						$projektarbeitnew=true;	
					}
				}
				if(!$error)
				{
					$qry2="SELECT * FROM lehre.tbl_lehreinheit WHERE lehrveranstaltung_id='".$lehreinheitlehrveranstaltung_id."' AND anmerkung='Diplomarbeit'; AND ext_id='".$row->diplomarbeit_pk."';";
					if($result2 = pg_query($conn, $qry2))
					{
						if(pg_num_rows($result2)>0) //eintrag gefunden
						{
							if($row2=pg_fetch_object($result2))
							{ 
								// update, wenn datensatz bereits vorhanden
								$lehreinheitnew=false;
								$lehreinheitlehreinheit_id=$row2->lehreinheit_id;
								$projektarbeitlehreinheit_id=$row2->lehreinheit_id;
							}
						}
						else 
						{
							// insert, wenn datensatz noch nicht vorhanden
							$lehreinheitnew=true;	
						}
						
					}
					if(!$error)
					{
						if($lehreinheitnew)
						{
							$qry = 'INSERT INTO lehre.tbl_lehreinheit (lehrveranstaltung_id, studiensemester_kurzbz,
							                                     lehrfach_id, lehrform_kurzbz, stundenblockung, wochenrythmus,
							                                     start_kw, raumtyp, raumtypalternativ, lehre, anmerkung, unr, lvnr, 
							                                     insertamum, insertvon, updateamum, updatevon,  ext_id, sprache)
							        VALUES('.myaddslashes($lehreinheitlehrveranstaltung_id).', '.
									myaddslashes($lehreinheitstudiensemester_kurzbz).', '.
									myaddslashes($lehreinheitlehrfach_id).', '.
									myaddslashes($lehreinheitlehrform_kurzbz).', '.
									myaddslashes($lehreinheitstundenblockung).', '.
									myaddslashes($lehreinheitwochenrythmus).', '.
									myaddslashes($lehreinheitstart_kw).', '.
									myaddslashes($lehreinheitraumtyp).', '.
									myaddslashes($lehreinheitraumtypalternativ).', '.
									($lehreinheitlehre?'true':'false').', '.
									myaddslashes($lehreinheitanmerkung).', '.
									myaddslashes($lehreinheitunr).', '.
									myaddslashes($lehreinheitlvnr).', '.
									myaddslashes($lehreinheitinsertamum).', '.
									myaddslashes($lehreinheitinsertvon).', 
									now(), '.
									myaddslashes($lehreinheitupdatevon).', '.
									myaddslashes($lehreinheitext_id).', '.
									myaddslashes($lehreinheitsprache).');';
						}
						else
						{
							$updatele=false;			
							if($row2->lehrveranstaltung_id!=$lehreinheitlehrveranstaltung_id) 
							{
								$updatele=true;
								if(strlen(trim($ausgabe_le))>0)
								{
									$ausgabe_le.=", Lehrveranstaltung ID: '".$lehreinheitlehrveranstaltung_id."' (statt '".$row2->lehrveranstaltung_id."')";
								}
								else
								{
									$ausgabe_le="Lehrveranstaltung ID: '".$lehreinheitlehrveranstaltung_id."' (statt '".$row2->lehrveranstaltung_id."')";
								}
							}
							if($row2->studiensemester_kurzbz!=$lehreinheitstudiensemester_kurzbz) 
							{
								$updatele=true;
								if(strlen(trim($ausgabe_le))>0)
								{
									$ausgabe_le.=", Studiensemester: '".$lehreinheitstudiensemester_kurzbz."' (statt '".$row2->studiensemester_kurzbz."')";
								}
								else
								{
									$ausgabe_le="Studiensemester: '".$lehreinheitstudiensemester_kurzbz."' (statt '".$row2->studiensemester_kurzbz."')";
								}
							}
							if($row2->lehrfach_id!=$lehreinheitlehrfach_id) 
							{
								$updatele=true;
								if(strlen(trim($ausgabe_le))>0)
								{
									$ausgabe_le.=", Lehrfach ID: '".$lehreinheitlehrfach_id."' (statt '".$row2->lehrfach_id."')";
								}
								else
								{
									$ausgabe_le="Lehrfach ID: '".$lehreinheitlehrfach_id."' (statt '".$row2->lehrfach_id."')";
								}
							}
							if($row2->lehrform_kurzbz!=$lehreinheitlehrform_kurzbz) 
							{
								$updatele=true;
								if(strlen(trim($ausgabe_le))>0)
								{
									$ausgabe_le.=", Lehrform: '".$lehreinheitlehrform_kurzbz."' (statt '".$row2->lehrform_kurzbz."')";
								}
								else
								{
									$ausgabe_le="Lehrform: '".$lehreinheitlehrform_kurzbz."' (statt '".$row2->lehrform_kurzbz."')";
								}
							}
							if($row2->stundenblockung!=$lehreinheitstundenblockung) 
							{
								$updatele=true;
								if(strlen(trim($ausgabe_le))>0)
								{
									$ausgabe_le.=", Stundenblockung: '".$lehreinheitstundenblockung."' (statt '".$row2->stundenblockung."')";
								}
								else
								{
									$ausgabe_le="Stundenblockung: '".$lehreinheitstundenblockung."' (statt '".$row2->stundenblockung."')";
								}
							}
							if($row2->wochenrythmus!=$lehreinheitwochenrythmus) 
							{
								$updatele=true;
								if(strlen(trim($ausgabe_le))>0)
								{
									$ausgabe_le.=", Wochenrythmus: '".$lehreinheitwochenrythmus."' (statt '".$row2->wochenrythmus."')";
								}
								else
								{
									$ausgabe_le="Wochenrythmus: '".$lehreinheitwochenrythmus."' (statt '".$row2->wochenrythmus."')";
								}
							}
							if($row2->start_kw!=$lehreinheitstart_kw) 
							{
								$updatele=true;
								if(strlen(trim($ausgabe_le))>0)
								{
									$ausgabe_le.=", Wochenrythmus: '".$lehreinheitstart_kw."' (statt '".$row2->start_kw."')";
								}
								else
								{
									$ausgabe_le="Wochenrythmus: '".$lehreinheitstart_kw."' (statt '".$row2->start_kw."')";
								}
							}
							if($row2->raumtyp!=$lehreinheitraumtyp) 
							{
								$updatele=true;
								if(strlen(trim($ausgabe_le))>0)
								{
									$ausgabe_le.=", Raumtyp: '".$lehreinheitraumtyp."' (statt '".$row2->raumtyp."')";
								}
								else
								{
									$ausgabe_le="Raumtyp: '".$lehreinheitraumtyp."' (statt '".$row2->raumtyp."')";
								}
							}
							if($row2->raumtypalternativ!=$lehreinheitraumtypalternativ) 
							{
								$updatele=true;
								if(strlen(trim($ausgabe_le))>0)
								{
									$ausgabe_le.=", Raumtyp alternativ: '".$lehreinheitraumtypalternativ."' (statt '".$row2->raumtypalternativ."')";
								}
								else
								{
									$ausgabe_le="Raumtyp alternativ: '".$lehreinheitraumtypalternativ."' (statt '".$row2->raumtypalternativ."')";
								}
							}
							if($row2->lehre!=($lehreinheitlehre?'t':'f') && $lehreinheitlehre!='') 
							{
								$updatele=true;
								if(strlen(trim($ausgabe_le))>0)
								{
									$ausgabe_le.=", Lehre: '".($lehreinheitlehre?'true':'false')."' (statt '".$row2->lehre."')";
								}
								else
								{
									$ausgabe_le="Lehre: '".($lehreinheitlehre?'true':'false')."' (statt '".$row2->lehre."')";
								}
							}
							if($row2->anmerkung!=$lehreinheitanmerkung) 
							{
								$updatele=true;
								if(strlen(trim($ausgabe_le))>0)
								{
									$ausgabe_le.=", Anmerkung: '".$lehreinheitanmerkung."' (statt '".$row2->anmerkung."')";
								}
								else
								{
									$ausgabe_le="Anmerkung: '".$lehreinheitanmerkung."' (statt '".$row2->anmerkung."')";
								}
							}
							if($row2->unr!=$lehreinheitunr) 
							{
								$updatele=true;
								if(strlen(trim($ausgabe_le))>0)
								{
									$ausgabe_le.=", UNr: '".$lehreinheitunr."' (statt '".$row2->unr."')";
								}
								else
								{
									$ausgabe_le="UNr: '".$lehreinheitunr."' (statt '".$row2->unr."')";
								}
							}
							if($row2->lvnr!=$lehreinheitlvnr) 
							{
								$updatele=true;
								if(strlen(trim($ausgabe_le))>0)
								{
									$ausgabe_le.=", LvNr: '".$lehreinheitlvnr."' (statt '".$row2->lvnr."')";
								}
								else
								{
									$ausgabe_le="LvNr: '".$lehreinheitlvnr."' (statt '".$row2->lvnr."')";
								}
							}
							if($row2->sprache!=$lehreinheitsprache) 
							{
								$updatele=true;
								if(strlen(trim($ausgabe_le))>0)
								{
									$ausgabe_le.=", Sprache: '".$lehreinheitsprache."' (statt '".$row2->sprache."')";
								}
								else
								{
									$ausgabe_le="Sprache: '".$lehreinheitsprache."' (statt '".$row2->sprache."')";
								}
							}
							if($row2->insertvon!=$lehreinheitinsertvon) 
							{
								$updatele=true;
								if(strlen(trim($ausgabe_le))>0)
								{
									$ausgabe_le.=", Insertvon: '".$lehreinheitinsertvon."' (statt '".$row2->insertvon."')";
								}
								else
								{
									$ausgabe_le="Insertvon: '".$lehreinheitinsertvon."' (statt '".$row2->insertvon."')";
								}
							}
							if(date("d.m.Y", $row2->insertamum)!=date("d.m.Y", $lehreinheitinsertamum))
							{
								$updatele=true;
								if(strlen(trim($ausgabe_le))>0)
								{
									$ausgabe_le.=", Insertamum: '".$lehreinheitinsertamum."' (statt '".$row2->insertamum."')";
								}
								else
								{
									$ausgabe_le="Insertamum: '".$lehreinheitinsertamum."' (statt '".$row2->insertamum."')";
								}
							}
							if($updatele)
							{
								$qry = 'UPDATE lehre.tbl_lehreinheit SET
									lehrveranstaltung_id='.myaddslashes($lehreinheitlehrveranstaltung_id).', 
									studiensemester_kurzbz='.myaddslashes($lehreinheitstudiensemester_kurzbz).', 
									lehrfach_id='.myaddslashes($lehreinheitlehrfach_id).', 
									lehrform_kurzbz='.myaddslashes($lehreinheitlehrform_kurzbz).', 
									stundenblockung='.myaddslashes($lehreinheitstundenblockung).', 
									wochenrythmus='.myaddslashes($lehreinheitwochenrythmus).', 
									start_kw='.myaddslashes($lehreinheitstart_kw).', 
									raumtyp='.myaddslashes($lehreinheitraumtyp).', 
									raumtypalternativ='.myaddslashes($lehreinheitraumtypalternativ).', 
									lehre='.($lehreinheitlehre?'true':'false').', 
									anmerkung='.myaddslashes($lehreinheitanmerkung).', 
									unr='.myaddslashes($lehreinheitunr).', 
									lvnr='.myaddslashes($lehreinheitlvnr).', 
									insertvon='.myaddslashes($lehreinheitinsertvon).', 
									insertamum='.myaddslashes($lehreinheitinsertamum).', 
									updateamum=now(), 
									updatevon='.myaddslashes($lehreinheitupdatevon).', 
									sprache='.myaddslashes($lehreinheitsprache).', 
									ext_id='.myaddslashes($lehreinheitext_id).
									" WHERE lehreinheit_id=".myaddslashes($lehreinheitlehreinheit_id).";";
								$ausgabe.="Lehreinheit aktualisiert bei Lehrveranstaltung='".$lehreinheitlehrveranstaltung_id."', Studiensemester='".$lehreinheitstudiensemester_kurzbz."' und Lehrfach='".$lehreinheitlehrfach_id."':.$ausgabe_le.\n";
							}
							else 
							{
								$qry="select 1;";
							}
						}
						
						if(!pg_query($conn,$qry))
						{
							$error_log.= "*****\nFehler beim Speichern des Lehreinheits-Datensatzes: ".$lehreinheitlehreinheit_id."\n   ".$qry."\n";
							$anzahl_fehler++;
						}
						else 
						{
							if($lehreinheitnew)
							{
								$anzahl_le_insert++;
								$qry = "SELECT currval('lehre.tbl_lehreinheit_lehreinheit_id_seq') AS id;";
								if($rowu=pg_fetch_object(pg_query($conn,$qry)))
									$projektarbeitlehreinheit_id=$rowu->id;
								else
								{					
									$error=true;
									$error_log.="Lehreinheit-Sequence konnte nicht ausgelesen werden.\n";
								}
								$ausgabe.="Lehreinheit angelegt: Lehrveranstaltung='".$lehreinheitlehrveranstaltung_id."', Studiensemester='".$lehreinheitstudiensemester_kurzbz."' und Lehrfach='".$lehreinheitlehrfach_id."'.\n";
							}
							else 
							{
								if($updatele)
								{
									$anzahl_le_update++;
								}
							}
							$anzahl_le_gesamt++;
						}
						if(!$error)
						{
							//pa anlegen
							if($projektarbeitnote=='0') $projektarbeitnote='9';
							if($projektarbeitnew)
							{
								//Neuen Datensatz einfuegen
													
								$qry='INSERT INTO lehre.tbl_projektarbeit (projekttyp_kurzbz, titel, lehreinheit_id, student_uid, firma_id, note, punkte, 
									beginn, ende, faktor, freigegeben, gesperrtbis, stundensatz, gesamtstunden, themenbereich, anmerkung, 
									ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
								     myaddslashes($projektarbeitprojekttyp_kurzbz).', '.
								     myaddslashes($projektarbeittitel).', '.
								     myaddslashes($projektarbeitlehreinheit_id).', '.
								     myaddslashes($projektarbeitstudent_uid).', '.
								     myaddslashes($projektarbeitfirma_id).', '.
								     myaddslashes($projektarbeitnote).', '.
								     myaddslashes($projektarbeitpunkte).', '.
								     myaddslashes($projektarbeitbeginn).', '.
								     myaddslashes($projektarbeitende).', '.
								     myaddslashes($projektarbeitfaktor).', '.
								     ($projektarbeitfreigegeben?'true':'false').', '.
								     myaddslashes($projektarbeitgesperrtbis).', '.
								     myaddslashes($projektarbeitstundensatz).', '.
								     myaddslashes($projektarbeitgesamtstunden).', '.
								     myaddslashes($projektarbeitthemenbereich).', '.
								     myaddslashes($projektarbeitanmerkung).', '.
								     myaddslashes($projektarbeitext_id).',  '.
								     myaddslashes($projektarbeitinsertamum).', '.
								     myaddslashes($projektarbeitinsertvon).', now(), '.
								     myaddslashes($projektarbeitupdatevon).');';
								     $ausgabe.="Projektarbeit angelegt: Student='".$projektarbeitstudent_uid."' und Lehreinheit='".$projektarbeitlehreinheit_id."'.\n";			
							}
							else
							{
								//Updaten des bestehenden Datensatzes
										
								$updatep=false;			
								if($row3->projekttyp_kurzbz!=$projektarbeitprojekttyp_kurzbz) 
								{
									$updatep=true;
									if(strlen(trim($ausgabe_pa))>0)
									{
										$ausgabe_pa.=", Projekttyp: '".$projektarbeitprojekttyp_kurzbz."' (statt '".$row3->projekttyp_kurzbz."')";
									}
									else
									{
										$ausgabe_pa="Projekttyp: '".$projektarbeitprojekttyp_kurzbz."' (statt '".$row3->projekttyp_kurzbz."')";
									}
								}
								if($row3->titel!=$projektarbeittitel) 
								{
									$updatep=true;
									if(strlen(trim($ausgabe_pa))>0)
									{
										$ausgabe_pa.=", Titel: '".$projektarbeittitel."' (statt '".$row3->titel."')";
									}
									else
									{
										$ausgabe_pa="Titel: '".$projektarbeittitel."' (statt '".$row3->titel."')";
									}
								}
								if($row3->lehreinheit_id!=$projektarbeitlehreinheit_id) 
								{
									$updatep=true;
									if(strlen(trim($ausgabe_pa))>0)
									{
										$ausgabe_pa.=", Lehreinheit: '".$projektarbeitlehreinheit_id."' (statt '".$row3->lehreinheit_id."')";
									}
									else
									{
										$ausgabe_pa="Lehreinheit: '".$projektarbeitlehreinheit_id."' (statt '".$row3->lehreinheit_id."')";
									}
								}
								if($row3->student_uid!=$projektarbeitstudent_uid) 
								{
									$updatep=true;
									if(strlen(trim($ausgabe_pa))>0)
									{
										$ausgabe_pa.=", Student: '".$projektarbeitstudent_uid."' (statt '".$row3->student_uid."')";
									}
									else
									{
										$ausgabe_pa="Student: '".$projektarbeitstudent_uid."' (statt '".$row3->student_uid."')";
									}
								}
								if($row3->firma_id!=$projektarbeitfirma_id) 
								{
									$updatep=true;
									if(strlen(trim($ausgabe_pa))>0)
									{
										$ausgabe_pa.=", Firma: '".$projektarbeitfirma_id."' (statt '".$row3->firma_id."')";
									}
									else
									{
										$ausgabe_pa="Firma: '".$projektarbeitfirma_id."' (statt '".$row3->firma_id."')";
									}
								}
								if($row3->note!=$projektarbeitnote) 
								{
									$updatep=true;
									if(strlen(trim($ausgabe_pa))>0)
									{
										$ausgabe_pa.=", Note: '".$projektarbeitnote."' (statt '".$row3->note."')";
									}
									else
									{
										$ausgabe_pa="Note: '".$projektarbeitnote."' (statt '".$row3->note."')";
									}
								}
								if($row3->punkte!=$projektarbeitpunkte) 
								{
									$updatep=true;
									if(strlen(trim($ausgabe_pa))>0)
									{
										$ausgabe_pa.=", Punkte: '".$projektarbeitpunkte."' (statt '".$row3->punkte."')";
									}
									else
									{
										$ausgabe_pa="Punkte: '".$projektarbeitpunkte."' (statt '".$row3->punkte."')";
									}
								}
								if($row3->beginn!=$projektarbeitbeginn) 
								{
									$updatep=true;
									if(strlen(trim($ausgabe_pa))>0)
									{
										$ausgabe_pa.=", Beginn: '".$projektarbeitbeginn."' (statt '".$row3->beginn."')";
									}
									else
									{
										$ausgabe_pa="Beginn: '".$projektarbeitbeginn."' (statt '".$row3->beginn."')";
									}
								}
								if($row3->ende!=$projektarbeitende) 
								{
									$updatep=true;
									if(strlen(trim($ausgabe_pa))>0)
									{
										$ausgabe_pa.=", Ende: '".$projektarbeitende."' (statt '".$row3->ende."')";
									}
									else
									{
										$ausgabe_pa="Ende: '".$projektarbeitende."' (statt '".$row3->ende."')";
									}
								}
								if($row3->faktor!=$projektarbeitfaktor) 
								{
									$updatep=true;
									if(strlen(trim($ausgabe_pa))>0)
									{
										$ausgabe_pa.=", Faktor: '".$projektarbeitfaktor."' (statt '".$row3->faktor."')";
									}
									else
									{
										$ausgabe_pa="Faktor: '".$projektarbeitfaktor."' (statt '".$row3->faktor."')";
									}
								}
								if($row3->freigegeben!=($projektarbeitfreigegeben?'t':'f'))
								{
									$updatep=true;
									if(strlen(trim($ausgabe_pa))>0)
									{
										$ausgabe_pa.=", freigegeben: '".($projektarbeitfreigegeben?'true':'false')."' (statt '".$row3->freigegeben."')";
									}
									else
									{
										$ausgabe_pa="freigegeben: '".($projektarbeitfreigegeben?'true':'false')."' (statt '".$row3->freigegeben."')";
									}
								}								
								if($row3->gesperrtbis!=$projektarbeitgesperrtbis) 
								{
									$updatep=true;
									if(strlen(trim($ausgabe_pa))>0)
									{
										$ausgabe_pa.=", gesperrt bis: '".$projektarbeitgesperrtbis."' (statt '".$row3->gesperrtbis."')";
									}
									else
									{
										$ausgabe_pa="gesperrt bis: '".$projektarbeitgesperrtbis."' (statt '".$row3->gesperrtbis."')";
									}
								}
								if($row3->stundensatz!=$projektarbeitstundensatz) 
								{
									$updatep=true;
									if(strlen(trim($ausgabe_pa))>0)
									{
										$ausgabe_pa.=", Stundensatz: '".$projektarbeitstundensatz."' (statt '".$row3->stundensatz."')";
									}
									else
									{
										$ausgabe_pa="Stundensatz: '".$projektarbeitstundensatz."' (statt '".$row3->stundensatz."')";
									}
								}
								if($row3->gesamtstunden!=$projektarbeitgesamtstunden) 
								{
									$updatep=true;
									if(strlen(trim($ausgabe_pa))>0)
									{
										$ausgabe_pa.=", Gesamtstunden: '".$projektarbeitgesamtstunden."' (statt '".$row3->gesamtstunden."')";
									}
									else
									{
										$ausgabe_pa="Gesamtstunden: '".$projektarbeitgesamtstunden."' (statt '".$row3->gesamtstunden."')";
									}
								}
								if($row3->themenbereich!=$projektarbeitthemenbereich) 
								{
									$updatep=true;
									if(strlen(trim($ausgabe_pa))>0)
									{
										$ausgabe_pa.=", Themenbereich: '".$projektarbeitthemenbereich."' (statt '".$row3->themenbereich."')";
									}
									else
									{
										$ausgabe_pa="Themenbereich: '".$projektarbeitthemenbereich."' (statt '".$row3->themenbereich."')";
									}
								}
								if($row3->anmerkung!=$projektarbeitanmerkung) 
								{
									$updatep=true;
									if(strlen(trim($ausgabe_pa))>0)
									{
										$ausgabe_pa.=", Anmerkung: '".$projektarbeitanmerkung."' (statt '".$row3->anmerkung."')";
									}
									else
									{
										$ausgabe_pa="Anmerkung: '".$projektarbeitanmerkung."' (statt '".$row3->anmerkung."')";
									}
								}
								if(date("d.m.Y", $row3->insertamum)!=date("d.m.Y", $projektarbeitinsertamum))
								{
									$updatep=true;
									if(strlen(trim($ausgabe_pa))>0)
									{
										$ausgabe_pa.=", Insertamum: '".$projektarbeitinsertamum."' (statt '".$row3->insertamum."')";
									}
									else
									{
										$ausgabe_pa="Insertamum: '".$projektarbeitinsertamum."' (statt '".$row3->insertamum."')";
									}
								}
								if($row3->insertvon!=$projektarbeitinsertvon) 
								{
									$updatep=true;
									if(strlen(trim($ausgabe_pa))>0)
									{
										$ausgabe_pa.=", Insertvon: '".$projektarbeitinsertvon."' (statt '".$row3->insertvon."')";
									}
									else
									{
										$ausgabe_pa="Insertvon: '".$projektarbeitinsertvon."' (statt '".$row3->insertvon."')";
									}
								}
								if($updatep)
								{
								$qry='UPDATE lehre.tbl_projektarbeit SET '.
									'projekttyp_kurzbz='.myaddslashes($projektarbeitprojekttyp_kurzbz).', '. 
									'titel='.myaddslashes($projektarbeittitel).', '.
									'lehreinheit_id='.myaddslashes($projektarbeitlehreinheit_id).', '.
									'student_uid='.myaddslashes($projektarbeitstudent_uid).', '.
									'firma_id='.myaddslashes($projektarbeitfirma_id).', '.
									'note='.myaddslashes($projektarbeitnote).', '.
									'punkte='.myaddslashes($projektarbeitpunkte).', '.
									'beginn='.myaddslashes($projektarbeitbeginn).', '.
									'ende='.myaddslashes($projektarbeitende).', '.
									'faktor='.myaddslashes($projektarbeitfaktor).', '.
									'freigegeben='.($projektarbeitfreigegeben?'true':'false').', '.
									'gesperrtbis='.myaddslashes($projektarbeitgesperrtbis).', '.
									'stundensatz='.myaddslashes($projektarbeitstundensatz).', '.
									'gesamtstunden='.myaddslashes($projektarbeitgesamtstunden).', '.
									'themenbereich='.myaddslashes($projektarbeitthemenbereich).', '.
									'anmerkung='.myaddslashes($projektarbeitanmerkung).', '.  
									'insertamum='.myaddslashes($projektarbeitinsertamum).', '.  
									'insertvon='.myaddslashes($projektarbeitinsertvon).', '.  
								     	'updateamum= now(), '.
								     	'updatevon='.myaddslashes($projektarbeitupdatevon).' '.
								     	//'firmentyp='.myaddslashes($projektarbeitfirmentyp_kurzbz).' '.
									'WHERE projektarbeit_id='.myaddslashes($projektarbeitprojektarbeit_id).';';
									$ausgabe.="Projektarbeit aktualisiert: Student='".$projektarbeitstudent_uid."' und Lehreinheit='".$projektarbeitlehreinheit_id."':".$ausgabe_pa.".\n";
								}
								else 
								{
									$qry="select 1;";
								}
							}
							//echo $qry;
							if(pg_query($conn,$qry))
							{
								$ausgabe_pa='';
								if($projektarbeitnew)
								{
									$qry = "SELECT currval('lehre.tbl_projektarbeit_projektarbeit_id_seq') AS id;";
									if($rowu=pg_fetch_object(pg_query($conn,$qry)))
										$projektarbeitprojektarbeit_id=$rowu->id;
									else
									{					
										$error=true;
										$error_log.="Projektarbeit-Sequence konnte nicht ausgelesen werden.\n";
									}
									$anzahl_pa_insert++;
								}			
								else 
								{
									if($updatep)
									{
										$anzahl_pa_update++;
									}
								}
								$anzahl_pa_gesamt++;
							}
							else 
							{
								$ausgabe_pa='';
								$error=true;
								$error_log.= "*****\nFehler beim Speichern des Projektarbeits-Datensatzes: ".$projektarbeitlehreinheit_id."\n   ".$qry."\n";
								$anzahl_fehler++;
							}
							if(!$error)
							{
								/*$qry="SELECT person_fk FROM mitarbeiter WHERE mitarbeiter_pk='".$row->mitarbeiter_fk."';";
								if($resultu = pg_query($conn_fas, $qry))
								{
									if($rowu=pg_fetch_object($resultu))
									{ 
										$person=$rowu->person_fk;	
									}
									else
									{
										$error=true;
										$error_log.="Betreuer mit mitarbeiter_fk: ".$row->mitarbeiter_fk."' konnte in Tabelle mitarbeiter nicht gefunden werden.\n";
									}
								}
								$qry="SELECT person_portal FROM sync.tbl_syncperson WHERE person_fas='$person'"; //betreuer_fk -> person_id
								if($resultu = pg_query($conn, $qry))
								{
									if($rowu=pg_fetch_object($resultu))
									{ 
										$projektbetreuerperson_id=$rowu->person_portal;	
									}
									else
									{
										$error=true;
										$error_log.="Betreuer mit person_fk: ".$person." konnte in syncperson nicht gefunden werden.\n";
									}
								}*/
								//ERSTBEGUTACHTER
								if($row->vilesci_erstbegutachter!=null)
								{
									$projektbetreuerperson_id			=$row->vilesci_erstbegutachter;
									$projektbetreuerprojektarbeit_id		=$projektarbeitprojektarbeit_id;
									//$projektbetreuernote			=$row->noteerstbegutachter;
									$projektbetreuerbetreuerart		='Erstbegutachter';  
									$projektbetreuerfaktor			=$row->faktor;
									$projektbetreuername			='';
									$projektbetreuerpunkte			=number_format($row->punkteerstbegutachter, 2, '.', '');
									$projektbetreuerstunden			="";
									$projektbetreuerstundensatz		="";
									//$projektbetreuerupdateamum		=$row->;
									$projektbetreuerupdatevon			="SYNC";
									$projektbetreuerinsertamum		=$row->creationdate;
									//$projektbetreuerinsertvon	 		="SYNC";
									$projektbetreuerext_id			=$row->diplomarbeit_pk;
									
									if(trim(strtoupper($row->noteerstbegutachter))=='SEHR GUT')
									{
										$projektbetreuernote='1';	
									}
									elseif(trim(strtoupper($row->noteerstbegutachter))=='GUT')
									{
										$projektbetreuernote='2';
									}
									elseif(trim(strtoupper($row->noteerstbegutachter))=='BEFRIEDIGEND')
									{
										$projektbetreuernote='3';
									}
									elseif(trim(strtoupper($row->noteerstbegutachter))=='GENÜGEND')
									{
										$projektbetreuernote='4';
									}
									elseif(trim(strtoupper($row->noteerstbegutachter))=='NICHT GENÜGEND')
									{
										$projektbetreuernote='5';
									}
									
									
									$qry2="SELECT * FROM lehre.tbl_projektbetreuer WHERE projektarbeit_id='".$projektarbeitprojektarbeit_id."' AND person_id='".$projektbetreuerperson_id."' AND betreuerart_kurzbz='Erstbegutachter';";
									if($result2 = pg_query($conn, $qry2))
									{
										if(pg_num_rows($result2)>0) //wenn dieser eintrag schon vorhanden ist
										{
											if($row2=pg_fetch_object($result2))
											{
												$projektbetreuerperson_id=$row2->person_id;
												$projektbetreuernew1=false;		
											}
											else $projektbetreuernew1=true;
										}
										else $projektbetreuernew1=true;
									}
									else
									{
										$error=true;
										$error_log.="Fehler beim Zugriff auf Tabelle tbl_projektbetreuer bei betreuer_fk: ".$row->betreuer_fk."\n";	
									}
									if($projektbetreuernew1)
									{
										$qry='INSERT INTO lehre.tbl_projektbetreuer (person_id, projektarbeit_id, betreuerart_kurzbz, faktor, name,
											  stunden, stundensatz, ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
										     myaddslashes($projektbetreuerperson_id).', '.
										     myaddslashes($projektbetreuerprojektarbeit_id).', '.
										     myaddslashes($projektbetreuerbetreuerart).', '.
										     myaddslashes($projektbetreuerfaktor).', '.
										     myaddslashes($projektbetreuername).', '.
										     myaddslashes($projektbetreuerstunden).', '.
										     myaddslashes($projektbetreuerstundensatz).', '.
										     myaddslashes($projektbetreuerext_id).', '.
										     myaddslashes($projektbetreuerinsertamum).', '.
										     myaddslashes($projektbetreuerinsertvon).', now(), '.
										     myaddslashes($projektbetreuerupdatevon).');';
										
									}
									else 
									{
										$updatep1=false;			
										if($row2->person_id!=$projektbetreuerperson_id) 
										{
											$updatep1=true;
											if(strlen(trim($ausgabe_pb))>0)
											{
												$ausgabe_pb.=", Betreuer: '".$projektbetreuerperson_id."' (statt '".$row2->person_id."')";
											}
											else
											{
												$ausgabe_pb="Betreuer: '".$projektbetreuerperson_id."' (statt '".$row2->person_id."')";
											}
										}
										if($row2->projektarbeit_id!=$projektbetreuerprojektarbeit_id) 
										{
											$updatep1=true;
											if(strlen(trim($ausgabe_pb))>0)
											{
												$ausgabe_pb.=", Projektarbeit: '".$projektbetreuerprojektarbeit_id."' (statt '".$row2->projektarbeit_id."')";
											}
											else
											{
												$ausgabe_pb="Projektarbeit: '".$projektbetreuerprojektarbeit_id."' (statt '".$row2->projektarbeit_id."')";
											}
										}
										if($row2->betreuerart_kurzbz!=$projektbetreuerbetreuerart) 
										{
											$updatep1=true;
											if(strlen(trim($ausgabe_pb))>0)
											{
												$ausgabe_pb.=", Betreuerart: '".$projektbetreuerbetreuerart."' (statt '".$row2->betreuerart_kurzbz."')";
											}
											else
											{
												$ausgabe_pb="Betreuerart: '".$projektbetreuerbetreuerart."' (statt '".$row2->betreuerart_kurzbz."')";
											}
										}
										if($row2->faktor!=$projektbetreuerfaktor) 
										{
											$updatep1=true;
											if(strlen(trim($ausgabe_pb))>0)
											{
												$ausgabe_pb.=", Faktor: '".$projektbetreuerfaktor."' (statt '".$row2->faktor."')";
											}
											else
											{
												$ausgabe_pb="Faktor: '".$projektbetreuerfaktor."' (statt '".$row2->faktor."')";
											}
										}
										if($row2->name!=$projektbetreuername) 
										{
											$updatep1=true;
											if(strlen(trim($ausgabe_pb))>0)
											{
												$ausgabe_pb.=", Name: '".$projektbetreuername."' (statt '".$row2->name."')";
											}
											else
											{
												$ausgabe_pb="Name: '".$projektbetreuername."' (statt '".$row2->name."')";
											}
										}
										if($row2->stunden!=$projektbetreuerstunden) 
										{
											$updatep1=true;
											if(strlen(trim($ausgabe_pb))>0)
											{
												$ausgabe_pb.=", Betreuerstunden: '".$projektbetreuerstunden."' (statt '".$row2->stunden."')";
											}
											else
											{
												$ausgabe_pb="Betreuerstunden: '".$projektbetreuerstunden."' (statt '".$row2->stunden."')";
											}
										}
										if($row2->stundensatz!=$projektbetreuerstundensatz) 
										{
											$updatep1=true;
											if(strlen(trim($ausgabe_pb))>0)
											{
												$ausgabe_pb.=", Stundensatz: '".$projektbetreuerstundensatz."' (statt '".$row2->stundensatz."')";
											}
											else
											{
												$ausgabe_pb="Stundensatz: '".$projektbetreuerstundensatz."' (statt '".$row2->stundensatz."')";
											}
										}
										if(date("d.m.Y", $row2->insertamum)!=date("d.m.Y", $projektbetreuerinsertamum)) 
										{
											$updatep1=true;
											if(strlen(trim($ausgabe_pb))>0)
											{
												$ausgabe_pb.=", Insertamum: '".$projektbetreuerinsertamum."' (statt '".$row2->insertamum."')";
											}
											else
											{
												$ausgabe_pb="Insertamum: '".$projektbetreuerinsertamum."' (statt '".$row2->insertamum."')";
											}
										}
										if($row2->insertvon!=$projektbetreuerinsertvon) 
										{
											$updatep1=true;
											if(strlen(trim($ausgabe_pb))>0)
											{
												$ausgabe_pb.=", Insertvon: '".$projektbetreuerinsertvon."' (statt '".$row2->insertvon."')";
											}
											else
											{
												$ausgabe_pb="Insertvon: '".$projektbetreuerinsertvon."' (statt '".$row2->insertvon."')";
											}
										}
										if($updatep1)
										{
											$qry='UPDATE lehre.tbl_projektbetreuer SET '.
											'person_id='.myaddslashes($projektbetreuerperson_id).', '. 
											'projektarbeit_id='.myaddslashes($projektbetreuerprojektarbeit_id).', '.
											'betreuerart_kurzbz='.myaddslashes($projektbetreuerbetreuerart).', '.
											'faktor='.myaddslashes($projektbetreuerfaktor).', '.
											'name='.myaddslashes($projektbetreuername).', '.
											'stunden='.myaddslashes($projektbetreuerstunden).', '.
											'stundensatz='.myaddslashes($projektbetreuerstundensatz).', '.
											'insertamum='.myaddslashes($projektbetreuerinsertamum).', '.
											'insertvon='.myaddslashes($projektbetreuerinsertvon).', '.
											'updateamum= now(), '.
										     	'updatevon='.myaddslashes($projektbetreuerupdatevon).' '.
											"WHERE projektarbeit_id='".$projektbetreuerprojektarbeit_id."' AND person_id='".$projektbetreuerperson_id."'AND betreuerart='Erstbegutachter';";
											
											
										}
										else 
										{
											$qry="select 1;";
										}
									}
									//echo nl2br ($qry."\n");
									if(pg_query($conn,$qry))
									{
										$anzahl_pbg_gesamt++;
										$ausgabe_pb1=$ausgabe_pb;
										$ausgabe_pb='';
										$projektbetreuerperson_id1=$projektbetreuerperson_id;
									}
									else
									{			
										$error=true;
										$error_log.="Fehler beim Speichern des Diplomarbeitserstbetreuer-Datensatzes:".$projektbetreuerperson_id." \n".$qry."\n";
										$ausgabe_pb='';
									}
								}
								else 
								{
									$noe++;
								}	
								
								if(!$error)
								{
									if($row->vilesci_zweitbegutachter!=null)
									{
										if(trim($row->vilesci_zweitbegutachter)!='')
										{
											//ZWEITBEGUTACHTER
											$projektbetreuerperson_id			=$row->vilesci_zweitbegutachter;
											$projektbetreuerprojektarbeit_id		=$projektarbeitprojektarbeit_id;
											//$projektbetreuernote			=$row->notezweitbegutachter;
											$projektbetreuerbetreuerart		='Zweitbegutachter';  
											$projektbetreuerfaktor			=$row->faktor;
											$projektbetreuername			='';
											$projektbetreuerpunkte			=number_format($row->punktezweitbegutachter, 2, '.', '');
											$projektbetreuerstunden			="";
											$projektbetreuerstundensatz		="";
											//$projektbetreuerupdateamum		=$row->;
											$projektbetreuerupdatevon			="SYNC";
											$projektbetreuerinsertamum		=$row->creationdate;
											//$projektbetreuerinsertvon	 		="SYNC";
											$projektbetreuerext_id			=$row->diplomarbeit_pk;
											
											if(trim(strtoupper($row->notezweitbegutachter))=='SEHR GUT')
											{
												$projektbetreuernote='1';	
											}
											elseif(trim(strtoupper($row->notezweitbegutachter))=='GUT')
											{
												$projektbetreuernote='2';
											}
											elseif(trim(strtoupper($row->notezweitbegutachter))=='BEFRIEDIGEND')
											{
												$projektbetreuernote='3';
											}
											elseif(trim(strtoupper($row->notezweitbegutachter))=='GENÜGEND')
											{
												$projektbetreuernote='4';
											}
											elseif(trim(strtoupper($row->notezweitbegutachter))=='NICHT GENÜGEND')
											{
												$projektbetreuernote='5';
											}
											
											
											$qry2="SELECT * FROM lehre.tbl_projektbetreuer WHERE projektarbeit_id='".$projektarbeitprojektarbeit_id."' AND person_id='".$projektbetreuerperson_id."' AND betreuerart_kurzbz='Zweitbegutachter';";
											if($result2 = pg_query($conn, $qry2))
											{
												if(pg_num_rows($result2)>0) //wenn dieser eintrag schon vorhanden ist
												{
													if($row2=pg_fetch_object($result2))
													{
														$projektbetreuerperson_id=$row2->person_id;
														$projektbetreuernew2=false;		
													}
													else $projektbetreuernew2=true;
												}
												else $projektbetreuernew2=true;
											}
											else
											{
												$error=true;
												$error_log.="Fehler beim Zugriff auf Tabelle tbl_projektbetreuer bei '".$qry2."'\n;";
											}
											if($projektbetreuernew2)
											{
												$qry='INSERT INTO lehre.tbl_projektbetreuer (person_id, projektarbeit_id, betreuerart_kurzbz, faktor, name,
													  stunden, stundensatz, ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
												     myaddslashes($projektbetreuerperson_id).', '.
												     myaddslashes($projektbetreuerprojektarbeit_id).', '.
												     myaddslashes($projektbetreuerbetreuerart).', '.
												     myaddslashes($projektbetreuerfaktor).', '.
												     myaddslashes($projektbetreuername).', '.
												     myaddslashes($projektbetreuerstunden).', '.
												     myaddslashes($projektbetreuerstundensatz).', '.
												     myaddslashes($projektbetreuerext_id).', '.
												     myaddslashes($projektbetreuerinsertamum).', '.
												     myaddslashes($projektbetreuerinsertvon).', now(), '.
												     myaddslashes($projektbetreuerupdatevon).');';
												
											}
											else 
											{
												$updatep2=false;			
												if($row2->person_id!=$projektbetreuerperson_id) 
												{
													$updatep2=true;
													if(strlen(trim($ausgabe_pb))>0)
													{
														$ausgabe_pb.=", Betreuer: '".$projektbetreuerperson_id."' (statt '".$row2->person_id."')";
													}
													else
													{
														$ausgabe_pb="Betreuer: '".$projektbetreuerperson_id."' (statt '".$row2->person_id."')";
													}
												}
												if($row2->projektarbeit_id!=$projektbetreuerprojektarbeit_id) 
												{
													$updatep2=true;
													if(strlen(trim($ausgabe_pb))>0)
													{
														$ausgabe_pb.=", Projektarbeit: '".$projektbetreuerprojektarbeit_id."' (statt '".$row2->projektarbeit_id."')";
													}
													else
													{
														$ausgabe_pb="Projektarbeit: '".$projektbetreuerprojektarbeit_id."' (statt '".$row2->projektarbeit_id."')";
													}
												}
												if($row2->betreuerart_kurzbz!=$projektbetreuerbetreuerart) 
												{
													$updatep2=true;
													if(strlen(trim($ausgabe_pb))>0)
													{
														$ausgabe_pb.=", Betreuerart: '".$projektbetreuerbetreuerart."' (statt '".$row2->betreuerart_kurzbz."')";
													}
													else
													{
														$ausgabe_pb="Betreuerart: '".$projektbetreuerbetreuerart."' (statt '".$row2->betreuerart_kurzbz."')";
													}
												}
												if($row2->faktor!=$projektbetreuerfaktor) 
												{
													$updatep2=true;
													if(strlen(trim($ausgabe_pb))>0)
													{
														$ausgabe_pb.=", Faktor: '".$projektbetreuerfaktor."' (statt '".$row2->faktor."')";
													}
													else
													{
														$ausgabe_pb="Faktor: '".$projektbetreuerfaktor."' (statt '".$row2->faktor."')";
													}
												}
												if($row2->name!=$projektbetreuername) 
												{
													$updatep2=true;
													if(strlen(trim($ausgabe_pb))>0)
													{
														$ausgabe_pb.=", Name: '".$projektbetreuername."' (statt '".$row2->name."')";
													}
													else
													{
														$ausgabe_pb="Name: '".$projektbetreuername."' (statt '".$row2->name."')";
													}
												}
												if($row2->stunden!=$projektbetreuerstunden) 
												{
													$updatep2=true;
													if(strlen(trim($ausgabe_pb))>0)
													{
														$ausgabe_pb.=", Betreuerstunden: '".$projektbetreuerstunden."' (statt '".$row2->stunden."')";
													}
													else
													{
														$ausgabe_pb="Betreuerstunden: '".$projektbetreuerstunden."' (statt '".$row2->stunden."')";
													}
												}
												if($row2->stundensatz!=$projektbetreuerstundensatz) 
												{
													$updatep2=true;
													if(strlen(trim($ausgabe_pb))>0)
													{
														$ausgabe_pb.=", Stundensatz: '".$projektbetreuerstundensatz."' (statt '".$row2->stundensatz."')";
													}
													else
													{
														$ausgabe_pb="Stundensatz: '".$projektbetreuerstundensatz."' (statt '".$row2->stundensatz."')";
													}
												}
												if(date("d.m.Y", $row2->insertamum)!=date("d.m.Y", $projektbetreuerinsertamum)) 
												{
													$updatep2=true;
													if(strlen(trim($ausgabe_pb))>0)
													{
														$ausgabe_pb.=", Insertamum: '".$projektbetreuerinsertamum."' (statt '".$row2->insertamum."')";
													}
													else
													{
														$ausgabe_pb="Insertamum: '".$projektbetreuerinsertamum."' (statt '".$row2->insertamum."')";
													}
												}
												if($row2->insertvon!=$projektbetreuerinsertvon) 
												{
													$updatep2=true;
													if(strlen(trim($ausgabe_pb))>0)
													{
														$ausgabe_pb.=", Insertvon: '".$projektbetreuerinsertvon."' (statt '".$row2->insertvon."')";
													}
													else
													{
														$ausgabe_pb="Insertvon: '".$projektbetreuerinsertvon."' (statt '".$row2->insertvon."')";
													}
												}
												if($updatep2)
												{
													$qry='UPDATE lehre.tbl_projektbetreuer SET '.
													'person_id='.myaddslashes($projektbetreuerperson_id).', '. 
													'projektarbeit_id='.myaddslashes($projektbetreuerprojektarbeit_id).', '.
													'betreuerart_kurzbz='.myaddslashes($projektbetreuerbetreuerart).', '.
													'faktor='.myaddslashes($projektbetreuerfaktor).', '.
													'name='.myaddslashes($projektbetreuername).', '.
													'stunden='.myaddslashes($projektbetreuerstunden).', '.
													'stundensatz='.myaddslashes($projektbetreuerstundensatz).', '.
													'insertamum='.myaddslashes($projektbetreuerinsertamum).', '.
													'insertvon='.myaddslashes($projektbetreuerinsertvon).', '.
													'updateamum= now(), '.
												     	'updatevon='.myaddslashes($projektbetreuerupdatevon).' '.
													"WHERE projektarbeit_id='".$projektbetreuerprojektarbeit_id."' AND person_id='".$projektbetreuerperson_id."'AND betreuerart='Zweitbegutachter';";
												}
												else 
												{
													$qry="select 1;";
												}
											}
											//echo nl2br ($qry."\n");
											if(pg_query($conn,$qry))
											{
												$anzahl_pbg2_gesamt++;
												$ausgabe_pb2=$ausgabe_pb;
												$ausgabe_pb='';
												$projektbetreuerperson_id2=$projektbetreuerperson_id;
											}
											else
											{			
												$error=true;
												$error_log.="Fehler beim Speichern des Diplomarbeitszweitbegutachter-Datensatzes:".$projektbetreuerperson_id." \n".$qry."\n";
												$ausgabe_pb='';
											}
										}
										else 
										{
											$noz++;
										}
									}
									else 
									{
										$noz++;
									}
									if($row->vilesci_betreuer!=null)
									{
										//ERSTBETREUER
										$projektbetreuerperson_id			=$row->vilesci_betreuer;
										$projektbetreuerprojektarbeit_id		=$projektarbeitprojektarbeit_id;
										$projektbetreuernote			='';
										$projektbetreuerbetreuerart		='Erstbetreuer';  
										$projektbetreuerfaktor			=$row->faktor;
										$projektbetreuername			='';
										$projektbetreuerpunkte			='';
										$projektbetreuerstunden			=number_format($row->betreuungsstunden, 4, '.', '');
										$projektbetreuerstundensatz		=$row->kosten;
										//$projektbetreuerupdateamum		=$row->;
										$projektbetreuerupdatevon			="SYNC";
										$projektbetreuerinsertamum		=$row->creationdate;
										//$projektbetreuerinsertvon	 		="SYNC";
										$projektbetreuerext_id			=$row->diplomarbeit_pk;
																			
										
										$qry2="SELECT * FROM lehre.tbl_projektbetreuer WHERE projektarbeit_id='".$projektarbeitprojektarbeit_id."' AND person_id='".$projektbetreuerperson_id."' AND betreuerart_kurzbz='Erstbetreuer';";
										if($result2 = pg_query($conn, $qry2))
										{
											if(pg_num_rows($result2)>0) //wenn dieser eintrag schon vorhanden ist
											{
												if($row2=pg_fetch_object($result2))
												{
													$projektbetreuerperson_id=$row2->person_id;
													$projektbetreuernew3=false;		
												}
												else $projektbetreuernew3=true;
											}
											else $projektbetreuernew3=true;
										}
										else
										{
											$error=true;
											$error_log.="Fehler beim Zugriff auf Tabelle tbl_projektbetreuer bei person_id: ".$row->vilesci_betreuer."\n";	
										}
										if($projektbetreuernew3)
										{
											$qry='INSERT INTO lehre.tbl_projektbetreuer (person_id, projektarbeit_id, betreuerart_kurzbz, faktor, name,
												  stunden, stundensatz, ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
											     myaddslashes($projektbetreuerperson_id).', '.
											     myaddslashes($projektbetreuerprojektarbeit_id).', '.
											     myaddslashes($projektbetreuerbetreuerart).', '.
											     myaddslashes($projektbetreuerfaktor).', '.
											     myaddslashes($projektbetreuername).', '.
											     myaddslashes($projektbetreuerstunden).', '.
											     myaddslashes($projektbetreuerstundensatz).', '.
											     myaddslashes($projektbetreuerext_id).', '.
											     myaddslashes($projektbetreuerinsertamum).', '.
											     myaddslashes($projektbetreuerinsertvon).', now(), '.
											     myaddslashes($projektbetreuerupdatevon).');';
											
										}
										else 
										{
											$updatep3=false;			
											if($row2->person_id!=$projektbetreuerperson_id) 
											{
												$updatep3=true;
												if(strlen(trim($ausgabe_pb))>0)
												{
													$ausgabe_pb.=", Betreuer: '".$projektbetreuerperson_id."' (statt '".$row2->person_id."')";
												}
												else
												{
													$ausgabe_pb="Betreuer: '".$projektbetreuerperson_id."' (statt '".$row2->person_id."')";
												}
											}
											if($row2->projektarbeit_id!=$projektbetreuerprojektarbeit_id) 
											{
												$updatep3=true;
												if(strlen(trim($ausgabe_pb))>0)
												{
													$ausgabe_pb.=", Projektarbeit: '".$projektbetreuerprojektarbeit_id."' (statt '".$row2->projektarbeit_id."')";
												}
												else
												{
													$ausgabe_pb="Projektarbeit: '".$projektbetreuerprojektarbeit_id."' (statt '".$row2->projektarbeit_id."')";
												}
											}
											if($row2->betreuerart_kurzbz!=$projektbetreuerbetreuerart) 
											{
												$updatep3=true;
												if(strlen(trim($ausgabe_pb))>0)
												{
													$ausgabe_pb.=", Betreuerart: '".$projektbetreuerbetreuerart."' (statt '".$row2->betreuerart_kurzbz."')";
												}
												else
												{
													$ausgabe_pb="Betreuerart: '".$projektbetreuerbetreuerart."' (statt '".$row2->betreuerart_kurzbz."')";
												}
											}
											if($row2->faktor!=$projektbetreuerfaktor) 
											{
												$updatep3=true;
												if(strlen(trim($ausgabe_pb))>0)
												{
													$ausgabe_pb.=", Faktor: '".$projektbetreuerfaktor."' (statt '".$row2->faktor."')";
												}
												else
												{
													$ausgabe_pb="Faktor: '".$projektbetreuerfaktor."' (statt '".$row2->faktor."')";
												}
											}
											if($row2->name!=$projektbetreuername) 
											{
												$updatep3=true;
												if(strlen(trim($ausgabe_pb))>0)
												{
													$ausgabe_pb.=", Name: '".$projektbetreuername."' (statt '".$row2->name."')";
												}
												else
												{
													$ausgabe_pb="Name: '".$projektbetreuername."' (statt '".$row2->name."')";
												}
											}
											if($row2->stunden!=$projektbetreuerstunden) 
											{
												$updatep3=true;
												if(strlen(trim($ausgabe_pb))>0)
												{
													$ausgabe_pb.=", Betreuerstunden: '".$projektbetreuerstunden."' (statt '".$row2->stunden."')";
												}
												else
												{
													$ausgabe_pb="Betreuerstunden: '".$projektbetreuerstunden."' (statt '".$row2->stunden."')";
												}
											}
											if($row2->stundensatz!=$projektbetreuerstundensatz) 
											{
												$updatep3=true;
												if(strlen(trim($ausgabe_pb))>0)
												{
													$ausgabe_pb.=", Stundensatz: '".$projektbetreuerstundensatz."' (statt '".$row2->stundensatz."')";
												}
												else
												{
													$ausgabe_pb="Stundensatz: '".$projektbetreuerstundensatz."' (statt '".$row2->stundensatz."')";
												}
											}
											if(date("d.m.Y", $row2->insertamum)!=date("d.m.Y", $projektbetreuerinsertamum)) 
											{
												$updatep3=true;
												if(strlen(trim($ausgabe_pb))>0)
												{
													$ausgabe_pb.=", Insertamum: '".$projektbetreuerinsertamum."' (statt '".$row2->insertamum."')";
												}
												else
												{
													$ausgabe_pb="Insertamum: '".$projektbetreuerinsertamum."' (statt '".$row2->insertamum."')";
												}
											}
											if($row2->insertvon!=$projektbetreuerinsertvon) 
											{
												$updatep3=true;
												if(strlen(trim($ausgabe_pb))>0)
												{
													$ausgabe_pb.=", Insertvon: '".$projektbetreuerinsertvon."' (statt '".$row2->insertvon."')";
												}
												else
												{
													$ausgabe_pb="Insertvon: '".$projektbetreuerinsertvon."' (statt '".$row2->insertvon."')";
												}
											}
											if($updatep3)
											{
												$qry='UPDATE lehre.tbl_projektbetreuer SET '.
												'person_id='.myaddslashes($projektbetreuerperson_id).', '. 
												'projektarbeit_id='.myaddslashes($projektbetreuerprojektarbeit_id).', '.
												'betreuerart_kurzbz='.myaddslashes($projektbetreuerbetreuerart).', '.
												'faktor='.myaddslashes($projektbetreuerfaktor).', '.
												'name='.myaddslashes($projektbetreuername).', '.
												'stunden='.myaddslashes($projektbetreuerstunden).', '.
												'stundensatz='.myaddslashes($projektbetreuerstundensatz).', '.
												'insertamum='.myaddslashes($projektbetreuerinsertamum).', '.
												'insertvon='.myaddslashes($projektbetreuerinsertvon).', '.
												'updateamum= now(), '.
											     	'updatevon='.myaddslashes($projektbetreuerupdatevon).' '.
												"WHERE projektarbeit_id='".$projektbetreuerprojektarbeit_id."' AND person_id='".$projektbetreuerperson_id."'AND betreuerart_kurzbz='Erstbetreuer';";
											}
											else 
											{
												$qry="select 1;";
											}
										}
										//echo nl2br ($qry."\n");
										if(pg_query($conn,$qry))
										{
											$anzahl_pbb_gesamt++;
											$ausgabe_pb3=$ausgabe_pb;
											$ausgabe_pb='';
											$projektbetreuerperson_id3=$projektbetreuerperson_id;
										}
										else
										{			
											$error=true;
											$error_log.="Fehler beim Speichern des Diplomarbeitserstbetreuer-Datensatzes:".$projektbetreuerperson_id." \n".$qry."\n";
											$ausgabe_pb='';
										}
									}
									else 
									{
										$no1++;
									}
									if($row->vilesci_firmenbetreuer!=null)
									{
										//ZWEITBETREUER
										$projektbetreuerperson_id			=$row->vilesci_firmenbetreuer;
										$projektbetreuerprojektarbeit_id		=$projektarbeitprojektarbeit_id;
										$projektbetreuernote			='';
										$projektbetreuerbetreuerart		='Zweitbetreuer';  
										$projektbetreuerfaktor			=$row->faktor;
										$projektbetreuername			='';
										$projektbetreuerpunkte			='';
										$projektbetreuerstunden			=number_format($row->betreuungsstunden, 4, '.', '');
										$projektbetreuerstundensatz		=$row->kosten;
										//$projektbetreuerupdateamum		=$row->;
										$projektbetreuerupdatevon			="SYNC";
										$projektbetreuerinsertamum		=$row->creationdate;
										//$projektbetreuerinsertvon	 		="SYNC";
										$projektbetreuerext_id			=$row->diplomarbeit_pk;
																			
										
										$qry2="SELECT * FROM lehre.tbl_projektbetreuer WHERE projektarbeit_id='".$projektarbeitprojektarbeit_id."' AND person_id='".$projektbetreuerperson_id."' AND betreuerart_kurzbz='Zweitbetreuer';";
										if($result2 = pg_query($conn, $qry2))
										{
											if(pg_num_rows($result2)>0) //wenn dieser eintrag schon vorhanden ist
											{
												if($row2=pg_fetch_object($result2))
												{
													$projektbetreuerperson_id=$row2->person_id;
													$projektbetreuernew4=false;		
												}
												else $projektbetreuernew4=true;
											}
											else $projektbetreuernew4=true;
										}
										else
										{
											$error=true;
											$error_log.="Fehler beim Zugriff auf Tabelle tbl_projektbetreuer bei person_id: ".$row->vilesci_firmenbetreuer."\n";	
										}
										if($projektbetreuernew4)
										{
											$qry='INSERT INTO lehre.tbl_projektbetreuer (person_id, projektarbeit_id, betreuerart_kurzbz, faktor, name,
												  stunden, stundensatz, ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
											     myaddslashes($projektbetreuerperson_id).', '.
											     myaddslashes($projektbetreuerprojektarbeit_id).', '.
											     myaddslashes($projektbetreuerbetreuerart).', '.
											     myaddslashes($projektbetreuerfaktor).', '.
											     myaddslashes($projektbetreuername).', '.
											     myaddslashes($projektbetreuerstunden).', '.
											     myaddslashes($projektbetreuerstundensatz).', '.
											     myaddslashes($projektbetreuerext_id).', '.
											     myaddslashes($projektbetreuerinsertamum).', '.
											     myaddslashes($projektbetreuerinsertvon).', now(), '.
											     myaddslashes($projektbetreuerupdatevon).');';
											
										}
										else 
										{
											$updatep4=false;			
											if($row2->person_id!=$projektbetreuerperson_id) 
											{
												$updatep4=true;
												if(strlen(trim($ausgabe_pb))>0)
												{
													$ausgabe_pb.=", Betreuer: '".$projektbetreuerperson_id."' (statt '".$row2->person_id."')";
												}
												else
												{
													$ausgabe_pb="Betreuer: '".$projektbetreuerperson_id."' (statt '".$row2->person_id."')";
												}
											}
											if($row2->projektarbeit_id!=$projektbetreuerprojektarbeit_id) 
											{
												$updatep4=true;
												if(strlen(trim($ausgabe_pb))>0)
												{
													$ausgabe_pb.=", Projektarbeit: '".$projektbetreuerprojektarbeit_id."' (statt '".$row2->projektarbeit_id."')";
												}
												else
												{
													$ausgabe_pb="Projektarbeit: '".$projektbetreuerprojektarbeit_id."' (statt '".$row2->projektarbeit_id."')";
												}
											}
											if($row2->betreuerart_kurzbz!=$projektbetreuerbetreuerart) 
											{
												$updatep4=true;
												if(strlen(trim($ausgabe_pb))>0)
												{
													$ausgabe_pb.=", Betreuerart: '".$projektbetreuerbetreuerart."' (statt '".$row2->betreuerart_kurzbz."')";
												}
												else
												{
													$ausgabe_pb="Betreuerart: '".$projektbetreuerbetreuerart."' (statt '".$row2->betreuerart_kurzbz."')";
												}
											}
											if($row2->faktor!=$projektbetreuerfaktor) 
											{
												$updatep4=true;
												if(strlen(trim($ausgabe_pb))>0)
												{
													$ausgabe_pb.=", Faktor: '".$projektbetreuerfaktor."' (statt '".$row2->faktor."')";
												}
												else
												{
													$ausgabe_pb="Faktor: '".$projektbetreuerfaktor."' (statt '".$row2->faktor."')";
												}
											}
											if($row2->name!=$projektbetreuername) 
											{
												$updatep4=true;
												if(strlen(trim($ausgabe_pb))>0)
												{
													$ausgabe_pb.=", Name: '".$projektbetreuername."' (statt '".$row2->name."')";
												}
												else
												{
													$ausgabe_pb="Name: '".$projektbetreuername."' (statt '".$row2->name."')";
												}
											}
											if($row2->stunden!=$projektbetreuerstunden) 
											{
												$updatep4=true;
												if(strlen(trim($ausgabe_pb))>0)
												{
													$ausgabe_pb.=", Betreuerstunden: '".$projektbetreuerstunden."' (statt '".$row2->stunden."')";
												}
												else
												{
													$ausgabe_pb="Betreuerstunden: '".$projektbetreuerstunden."' (statt '".$row2->stunden."')";
												}
											}
											if($row2->stundensatz!=$projektbetreuerstundensatz) 
											{
												$updatep4=true;
												if(strlen(trim($ausgabe_pb))>0)
												{
													$ausgabe_pb.=", Stundensatz: '".$projektbetreuerstundensatz."' (statt '".$row2->stundensatz."')";
												}
												else
												{
													$ausgabe_pb="Stundensatz: '".$projektbetreuerstundensatz."' (statt '".$row2->stundensatz."')";
												}
											}
											if(date("d.m.Y", $row2->insertamum)!=date("d.m.Y", $projektbetreuerinsertamum)) 
											{
												$updatep4=true;
												if(strlen(trim($ausgabe_pb))>0)
												{
													$ausgabe_pb.=", Insertamum: '".$projektbetreuerinsertamum."' (statt '".$row2->insertamum."')";
												}
												else
												{
													$ausgabe_pb="Insertamum: '".$projektbetreuerinsertamum."' (statt '".$row2->insertamum."')";
												}
											}
											if($row2->insertvon!=$projektbetreuerinsertvon) 
											{
												$updatep4=true;
												if(strlen(trim($ausgabe_pb))>0)
												{
													$ausgabe_pb.=", Insertvon: '".$projektbetreuerinsertvon."' (statt '".$row2->insertvon."')";
												}
												else
												{
													$ausgabe_pb="Insertvon: '".$projektbetreuerinsertvon."' (statt '".$row2->insertvon."')";
												}
											}
											if($updatep4)
											{
												$qry='UPDATE lehre.tbl_projektbetreuer SET '.
												'person_id='.myaddslashes($projektbetreuerperson_id).', '. 
												'projektarbeit_id='.myaddslashes($projektbetreuerprojektarbeit_id).', '.
												'betreuerart_kurzbz='.myaddslashes($projektbetreuerbetreuerart).', '.
												'faktor='.myaddslashes($projektbetreuerfaktor).', '.
												'name='.myaddslashes($projektbetreuername).', '.
												'stunden='.myaddslashes($projektbetreuerstunden).', '.
												'stundensatz='.myaddslashes($projektbetreuerstundensatz).', '.
												'insertamum='.myaddslashes($projektbetreuerinsertamum).', '.
												'insertvon='.myaddslashes($projektbetreuerinsertvon).', '.
												'updateamum= now(), '.
											     	'updatevon='.myaddslashes($projektbetreuerupdatevon).' '.
												"WHERE projektarbeit_id='".$projektbetreuerprojektarbeit_id."' AND person_id='".$projektbetreuerperson_id."'AND betreuerart_kurzbz='Zweitbetreuer';";
											}
											else 
											{
												$qry="select 1;";
											}
										}
										//echo nl2br ($qry."\n");
										if(pg_query($conn,$qry))
										{
											$anzahl_pbb2_gesamt++;
											$ausgabe_pb4=$ausgabe_pb;
											$ausgabe_pb='';
											$projektbetreuerperson_id4=$projektbetreuerperson_id;
										}
										else
										{			
											$error=true;
											$error_log.="Fehler beim Speichern des Diplomarbeitszweitbetreuer-Datensatzes:".$projektbetreuerperson_id." \n".$qry."\n";
											$ausgabe_pb='';
										}
									}
									else 
									{
										$no2++;
									}
									if($error)
									{
										//ROLLBACK
										$anzahl_fehler_pbb++;
										$ausgabe='';
										$text9.="\n***********Diplomarbeit:".$row->diplomarbeit_pk."\n";
										$text9.=$error_log;
										$text9.=" R9\n";
										$text9.="***********\n";
										pg_query($conn, "ROLLBACK");
									}
									else 
									{
										//COMMIT
										if($projektbetreuernew1)
										{
											$anzahl_pbg_insert++;
											$ausgabe.="Diplomarbeitsbetreuer eingefügt: UID='".$projektbetreuerperson_id1."' und Projektarbeit='".$projektarbeitlehreinheit_id."'.\n";
										}			
										else 
										{
											if($updatep1)
											{
												$anzahl_pbg_update++;
												$ausgabe.="Diplomarbeitsbetreuer(1) aktualisiert: UID='".$projektbetreuerperson_id1."' und Projektarbeit='".$projektarbeitlehreinheit_id."':".$ausgabe_pb1.".\n";
											}
										}
										if($projektbetreuernew2)
										{
											$anzahl_pbg2_insert++;
											$ausgabe.="Diplomarbeitsbetreuer eingefügt: UID='".$projektbetreuerperson_id2."' und Projektarbeit='".$projektarbeitlehreinheit_id."'.\n";
										}			
										else 
										{
											if($updatep2)
											{
												$anzahl_pbg2_update++;
												$ausgabe.="Diplomarbeitsbetreuer(2) aktualisiert: UID='".$projektbetreuerperson_id2."' und Projektarbeit='".$projektarbeitlehreinheit_id."':".$ausgabe_pb2.".\n";
											}
										}
										if($projektbetreuernew3)
										{
											$anzahl_pbb_insert++;
											$ausgabe.="Diplomarbeitsbetreuer eingefügt: UID='".$projektbetreuerperson_id3."' und Projektarbeit='".$projektarbeitlehreinheit_id."'.\n";
										}			
										else 
										{
											if($updatep3)
											{
												$anzahl_pbb_update++;
												$ausgabe.="Diplomarbeitsbetreuer(3) aktualisiert: UID='".$projektbetreuerperson_id3."' und Projektarbeit='".$projektarbeitlehreinheit_id."':".$ausgabe_pb3.".\n";
											}
										}
										if($projektbetreuernew4)
										{
											$anzahl_pbb2_insert++;
											$ausgabe.="Diplomarbeitsbetreuer eingefügt: UID='".$projektbetreuerperson_id4."' und Projektarbeit='".$projektarbeitlehreinheit_id."'.\n";
										}			
										else 
										{
											if($updatep4)
											{
												$anzahl_pbb2_update++;
												$ausgabe.="Diplomarbeitsbetreuer(4) aktualisiert: UID='".$projektbetreuerperson_id4."' und Projektarbeit='".$projektarbeitlehreinheit_id."':".$ausgabe_pb4.".\n";
											}
										}
										$ausgabe_pb1='';
										$ausgabe_pb2='';
										$ausgabe_pb3='';
										$ausgabe_pb4='';
										pg_query($conn,'COMMIT;');
										$ausgabe_all.=$ausgabe;
										$ausgabe='';
									}
								}	
								else 
								{
									//ROLLBACK
									$anzahl_fehler_pbg++;
									$ausgabe='';
									$text2.="\n***********Diplomarbeit:".$row->diplomarbeit_pk."\n";
									$text2.=$error_log;
									$text2.=" R2\n";
									$text2.="***********\n";
									pg_query($conn, "ROLLBACK");	
								}
								
							}
							else 
							{
								//ROLLBACK
								$anzahl_fehler_pa++;
								$ausgabe='';
								$text2.="\n***********Diplomarbeit:".$row->diplomarbeit_pk."\n";
								$text2.=$error_log;
								$text2.=" R3\n";
								$text2.="***********\n";
								pg_query($conn, "ROLLBACK");
							}
						}
						else 
						{
							//ROLLBACK
							$anzahl_fehler_le++;
							$ausgabe='';
							$text3.="\n***********Diplomarbeit:".$row->diplomarbeit_pk."\n";
							$text3.=$error_log;
							$text3.=" R4\n";
							$text3.="***********\n";
							pg_query($conn, "ROLLBACK");
						}
					}
					else 
					{
						//ROLLBACK
						$anzahl_fehler++;
						$ausgabe='';
						$text4.="\n***********Diplomarbeit:".$row->diplomarbeit_pk."\n";
						$text4.=$error_log;
						$text4.=" R5\n";
						$text4.="***********\n";
						pg_query($conn, "ROLLBACK");
					}
				}
				else 
				{
					//ROLLBACK
					$anzahl_fehler++;
					$ausgabe='';
					$text5.="\n***********Diplomarbeit:".$row->diplomarbeit_pk."\n";
					$text5.=$error_log;
					$text5.=" R6\n";
					$text5.="***********\n";
					pg_query($conn, "ROLLBACK");
				}
			}
			else 
			{
				//ROLLBACK
				$anzahl_fehler++;
				$ausgabe='';
				$text6.="\n***********Diplomarbeit:".$row->diplomarbeit_pk."\n";
				$text6.=$error_log;
				$text6.=" R7\n";
				$text6.="***********\n";
				pg_query($conn, "ROLLBACK");
			}
		}
		else 
		{
			//ROLLBACK
			$anzahl_fehler++;
			$ausgabe='';
			$text7.="\n***********Diplomarbeit:".$row->diplomarbeit_pk."\n";
			$text7.=$error_log;
			$text7.=" R8\n";
			$text7.="***********\n";
			pg_query($conn, "ROLLBACK");
		}
		$error_log_fas1.=$text1;
		$error_log_fas2.=$text2;
		$error_log_fas3.=$text3;
		$error_log_fas4.=$text4;
		$error_log_fas5.=$text5;
		$error_log_fas6.=$text6;
		$error_log_fas7.=$text7;
		$error_log_fas8.='';
		$error_log_fas9.=$text9;
		$error_log_fas10.=$text10;
	}
//echo und mail
echo nl2br("Diplomarbeitsynchro Ende: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");

$error_log_fas="Sync Diplomarbeit\n------------------------\n\n".$error_log_fas1."\n".$error_log_fas2."\n".$error_log_fas3."\n".$error_log_fas4."\n".$error_log_fas5."\n".$error_log_fas6."\n".$error_log_fas7."\n".$error_log_fas8."\n".$error_log_fas9;
echo nl2br("Allgemeine Fehler: ".$anzahl_fehler.", Anzahl Diplomarbeiten: ".$anzahl_quelle.".\n");
echo nl2br("Lehrveranstaltungen:       Gesamt: ".$anzahl_lv_gesamt." / Eingefügt: ".$anzahl_lv_insert." / Geändert: ".$anzahl_lv_update." / Fehler: ".$anzahl_fehler_lv."\n");
echo nl2br("Lehreinheiten:       	Gesamt: ".$anzahl_le_gesamt." / Eingefügt: ".$anzahl_le_insert." / Geändert: ".$anzahl_le_update." / Fehler: ".$anzahl_fehler_le."\n");
echo nl2br("Projektarbeiten:   	Gesamt: ".$anzahl_pa_gesamt." / Eingefügt: ".$anzahl_pa_insert." / Geändert: ".$anzahl_pa_update." / Fehler: ".$anzahl_fehler_pa."\n");
echo nl2br("Begutachter1:  		Gesamt: ".$anzahl_pbg_gesamt." / Eingefügt: ".$anzahl_pbg_insert." / Geändert: ".$anzahl_pbg_update." / Fehler: ".$anzahl_fehler_pbg." / kein Erstbegutachter: ".$noe."\n");
echo nl2br("Begutachter2:  		Gesamt: ".$anzahl_pbg2_gesamt." / Eingefügt: ".$anzahl_pbg2_insert." / Geändert: ".$anzahl_pbg2_update." / Fehler: ".$anzahl_fehler_pbg2." / kein Zweitbegutachter: ".$noz."\n");
echo nl2br("Betreuer1:  		Gesamt: ".$anzahl_pbb_gesamt." / Eingefügt: ".$anzahl_pbb_insert." / Geändert: ".$anzahl_pbb_update." / Fehler: ".$anzahl_fehler_pbb." / kein Erstbetreuer: ".$no1."\n");
echo nl2br("Betreuer2:  		Gesamt: ".$anzahl_pbb2_gesamt." / Eingefügt: ".$anzahl_pbb2_insert." / Geändert: ".$anzahl_pbb2_update." / Fehler: ".$anzahl_fehler_pbb2." / kein Zweitbetreuer: ".$no2."\n\n");
echo nl2br($error_log_fas."\n--------------------------------------------------------------------------------\n");
echo nl2br($ausgabe_all);

mail($adress, 'SYNC Diplomarbeit von '.$_SERVER['HTTP_HOST'], 
"Allgemeine Fehler: ".$anzahl_fehler.", Anzahl Diplomarbeiten: ".$anzahl_quelle.".\n".
"Lehrveranstaltungen:    Gesamt: ".$anzahl_lv_gesamt." / Eingefügt: ".$anzahl_lv_insert." / Geändert: ".$anzahl_lv_update." / Fehler: ".$anzahl_fehler_lv."\n".
"Lehreinheiten:       	Gesamt: ".$anzahl_le_gesamt." / Eingefügt: ".$anzahl_le_insert." / Geändert: ".$anzahl_le_update." / Fehler: ".$anzahl_fehler_le."\n".
"Projektarbeiten:    	Gesamt: ".$anzahl_pa_gesamt." / Eingefügt: ".$anzahl_pa_insert." / Geändert: ".$anzahl_pa_update." / Fehler: ".$anzahl_fehler_pa."\n".
"Begutachter1:  		Gesamt: ".$anzahl_pbg_gesamt." / Eingefügt: ".$anzahl_pbg_insert." / Geändert: ".$anzahl_pbg_update." / Fehler: ".$anzahl_fehler_pbg." / kein Erstbegutachter: ".$noe."\n".
"Begutachter2:  		Gesamt: ".$anzahl_pbg2_gesamt." / Eingefügt: ".$anzahl_pbg2_insert." / Geändert: ".$anzahl_pbg2_update." / Fehler: ".$anzahl_fehler_pbg2." / kein Zweitbegutachter: ".$noz."\n".
"Betreuer1:  		Gesamt: ".$anzahl_pbb_gesamt." / Eingefügt: ".$anzahl_pbb_insert." / Geändert: ".$anzahl_pbb_update." / Fehler: ".$anzahl_fehler_pbb." / kein Erstbetreuer: ".$no1."\n".
"Betreuer2:  		Gesamt: ".$anzahl_pbb2_gesamt." / Eingefügt: ".$anzahl_pbb2_insert." / Geändert: ".$anzahl_pbb2_update." / Fehler: ".$anzahl_fehler_pbb2." / kein Zweitbetreuer: ".$no2."\n\n".
$ausgabe_all,"From: vilesci@technikum-wien.at");

mail($adress, 'SYNC-Fehler Diplomarbeiten  von '.$_SERVER['HTTP_HOST'], $error_log_fas, "From: vilesci@technikum-wien.at");
}
?>
</body>
</html>