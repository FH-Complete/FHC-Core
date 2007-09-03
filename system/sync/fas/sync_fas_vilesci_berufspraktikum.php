<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Berufspraktikumsdatensaetze von FAS DB in PORTAL DB
//* 
//*

require_once('../../../vilesci/config.inc.php');
require_once('../sync_config.inc.php');
require_once('../../../include/firma.class.php');

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
$text = '';
$anzahl_lv_fehler=0;
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
$ausgabe_fa='';
$text1='';
$text2='';
$text3='';
$text4='';
$text5='';
$text6='';
$text7='';
$text8='';


$ausgabe_pb1='';
$ausgabe_pb2='';
$projektbetreuerperson_id1="";
$projektbetreuerperson_id2="";

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
<title>Synchro - FAS -> Portal - Berufspraktikum</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
//nation
$qry_main = "SELECT *, creationdate::timestamp AS insertamum FROM berufspraktikum;";

if($result = pg_query($conn_fas, $qry_main))
{
	echo nl2br("Berufspraktikum Sync\n---------------------\n");
	echo nl2br("Berufspraktikum Beginn: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");
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
		$person='';
		if($row->lehrveranstaltung_fk<'1')
		{
			$anzahl_lv_fehler++;
			continue;
		}
		if($row->betreuer_fk<'1')
		{
			$anzahl_betreuer_fehler++;
			continue;
		}
		pg_query($conn, "BEGIN");
		$error=false;
		$error_log='';
		$projektarbeitprojekttyp_kurzbz		='Praktikum';
		$projektarbeittitel				='';
		//$projektarbeitlehreinheit_id		='';
		//$projektarbeitstudent_uid			='';
		$projektarbeitfirma_id			=$row->vilesci_firma;
		$projektarbeitnote				='';
		$projektarbeitpunkte				='0';
		$projektarbeitbeginn				=$row->von;
		$projektarbeitende				=$row->bis;
		$projektarbeitfaktor				='1.0';
		$projektarbeitfreigegeben			=true;
		$projektarbeitgesperrtbis			='';
		$projektarbeitstundensatz			=$row->stdhonorar;
		$projektarbeitgesamtstunden		=$row->gesamtstunden;
		$projektarbeitthemenbereich		='';
		$projektarbeitanmerkung			='';		
		//$projektarbeitupdateamum		='';
		$projektarbeitupdatevon			="SYNC";
		$projektarbeitinsertamum			=$row->insertamum;
		//$projektarbeitinsertvon			=$row->creationuser;
		$projektarbeitext_id				=$row->berufspraktikum_pk;	
		
		//$lehreinheitlehrveranstaltung_id		='';
		//$lehreinheitstudiensemester_kz		='';
		//$lehreinheitlehrfach_id			='';
		$lehreinheitlehrform_kurzbz			='BE';
		$lehreinheitstundenblockung		='1';
		$lehreinheitwochenrythmus			='1';
		$lehreinheitstart_kw				='';
		$lehreinheitraumtyp				='DIV';
		$lehreinheitraumtypalternativ		='DIV';
		$lehreinheitsprache				='German';
		$lehreinheitlehre				=false;
		$lehreinheitanmerkung			='Berufspraktikum';
		$lehreinheitunr				='';
		$lehreinheitlvnr				='';
		//$lehreinheitupdateamum			='';
		$lehreinheitupdatevon			="SYNC";
		$lehreinheitinsertamum			=$row->insertamum;
		//$lehreinheitinsertvon			=$row->creationuser;
		$lehreinheitext_id				=$row->berufspraktikum_pk;
		
		$farbe						="CCCCCC";
		$sprache					='German';
		$bezeichnung				='Berufspraktikum';
		$kurzbezeichnung				='BPRAX';
		
		$firmenname					=$row->firma;
		$adresse					=$row->adresse;
		$email						=$row->email;
		$telefonnummer				=$row->telefonnummer;
			
		$studiengang_kz				='';
		$semester					='';
		$lva						='';
		
		$qrycu="SELECT name FROM benutzer WHERE benutzer_pk='".$row->creationuser."';";
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
		$qry="SELECT student_uid FROM public.tbl_student WHERE ext_id='".$row->student_fk."';";
		if($resulto=pg_query($conn, $qry))
		{
			if($rowo=pg_fetch_object($resulto))
			{ 
				$projektarbeitstudent_uid=$rowo->student_uid;
			}
			else 
			{
				$error=true;
				$error_log.="Student mit student_fk: $row->student_fk konnte in tbl_student nicht gefunden werden.\n";
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
		if(!$error)
		{
			$qry="SELECT lehrveranstaltung_id, studiengang_kz, semester FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id='".$lva."';";
			if($result1 = pg_query($conn, $qry))
			{
				if($row1=pg_fetch_object($result1))
				{ 
					$lehreinheitlehrveranstaltung_id=$row1->lehrveranstaltung_id;
					$studiengang_kz=$row1->studiengang_kz;
					$semester=$row1->semester;
				}
				else 
				{
					$error=true;
					$error_log.="Lehrveranstaltung mit ext_id='".$row->lehrveranstaltung_fk."' nicht gefunden.\n";
				}
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
			if(!$error)
			{
				//echo nl2br("fachbereich_kurzbz='".$fachbereich_kurzbz."' AND semester='".$semester."' AND studiengang_kz='".$studiengang_kz."';");		
				$qry="SELECT lehrfach_id FROM lehre.tbl_lehrfach WHERE fachbereich_kurzbz='".$fachbereich_kurzbz."' AND semester='".$semester."' AND studiengang_kz='".$studiengang_kz."';";
				if($resulto = pg_query($conn, $qry))
				{
					if($rowo=pg_fetch_object($resulto))
					{ 
						$lehreinheitlehrfach_id=$rowo->lehrfach_id;
					}
					else 
					{
						//$error=true;
						//$error_log.="Lehrfach mit Fachbereich='".$fachbereich_kurzbz."', Semester='".$semester."' und Studiengang='".$studiengang_kz."' nicht gefunden.\n";
										
						$qry="INSERT INTO lehre.tbl_lehrfach (studiengang_kz, fachbereich_kurzbz, kurzbz, bezeichnung, farbe, aktiv, 
							semester, sprache, insertamum, insertvon, updateamum, updatevon, ext_id) VALUES (".
							myaddslashes($studiengang_kz).", ".
							myaddslashes($fachbereich_kurzbz).", ".
							myaddslashes($kurzbezeichnung).", ".
							myaddslashes($bezeichnung).", ".
							myaddslashes($farbe).", ".
							"false, ".
							myaddslashes($semester).", ".
							myaddslashes($sprache).", ".
							"now(), ".
							"'Sync', ".
							"now(), ".
							"'Sync', ".
							"NULL);";
						if($result2 = pg_query($conn, $qry))
						{
							$qryu = "SELECT currval('lehre.tbl_lehrfach_lehrfach_id_seq') AS id;";
							if($rowu=pg_fetch_object(pg_query($conn,$qryu)))
								$lehreinheitlehrfach_id=$rowu->id;
							else
							{					
								$error=true;
								$error_log.='Lehrfach-Sequence konnte nicht ausgelesen werden.\n';
							}
							$ausgabe.="Lehrfach '".$bezeichnung."' ('".$kurzbezeichnung."'), Fachbereich '".$fachbereich_kurzbz."', Studiengang '".$studiengang_kz."' und Semester '".$semester."' angelegt!\n";
							echo "Lehrfach '".$bezeichnung."' ('".$kurzbezeichnung."'), Fachbereich '".$fachbereich_kurzbz."', Studiengang '".$studiengang_kz."' und Semester '".$semester."' angelegt!<br>";
							
						}
						else 
						{
							$error=true;
							$error_log.='Lehrfach konnte nicht angelegt werden. '.$qry.'\n';
						}	
					}
				}
				$qry="SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE ext_id='$row->studiensemester_fk'";
				if($resulto = pg_query($conn, $qry))
				{
					if($rowo=pg_fetch_object($resulto))
					{ 
						$lehreinheitstudiensemester_kurzbz=$rowo->studiensemester_kurzbz;
					}
					else 
					{
						$error=true;
						$error_log.="Studiensemester mit ext_id='".$row->studiensemester_fk."' nicht gefunden.\n";
					}
				}
				
				$qry3="SELECT * FROM lehre.tbl_projektarbeit WHERE projekttyp_kurzbz='".$projektarbeitprojekttyp_kurzbz."' AND ext_id='".$row->berufspraktikum_pk."';";
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
					$qry2="SELECT * FROM lehre.tbl_lehreinheit WHERE lehrveranstaltung_id='".$lehreinheitlehrveranstaltung_id."' AND anmerkung='Berufspraktikum' AND ext_id='".$row->berufspraktikum_pk."';";
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
									 //$ausgabe.="Lehreinheit angelegt: Lehrveranstaltung='".$lehreinheitlehrveranstaltung_id."', Studiensemester='".$lehreinheitstudiensemester_kurzbz."' und Lehrfach='".$lehreinheitlehrfach_id."'.\n";			
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
							if($row2->insertamum!=$lehreinheitinsertamum) 
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
								$qry = 'UPDATE lehre.tbl_lehreinheit SET'.
								       ' lehrveranstaltung_id='.myaddslashes($lehreinheitlehrveranstaltung_id).','.
								       ' studiensemester_kurzbz='.myaddslashes($lehreinheitstudiensemester_kurzbz).','.
								       ' lehrfach_id='.myaddslashes($lehreinheitlehrfach_id).','.
								       ' lehrform_kurzbz='.myaddslashes($lehreinheitlehrform_kurzbz).','.
								       ' stundenblockung='.myaddslashes($lehreinheitstundenblockung).','.
								       ' wochenrythmus='.myaddslashes($lehreinheitwochenrythmus).','.
								       ' start_kw='.myaddslashes($lehreinheitstart_kw).','.
								       ' raumtyp='.myaddslashes($lehreinheitraumtyp).','.
								       ' raumtypalternativ='.myaddslashes($lehreinheitraumtypalternativ).','.
								       ' lehre='.($lehreinheitlehre?'true':'false').','.
								       ' anmerkung='.myaddslashes($lehreinheitanmerkung).','.
								       ' unr='.myaddslashes($lehreinheitunr).','.
								       ' lvnr='.myaddslashes($lehreinheitlvnr).','.
								       ' insertvon='.myaddslashes($lehreinheitinsertvon).','.
								       ' insertamum='.myaddslashes($lehreinheitinsertamum).','.
								       ' updateamum=now(),'.
								       ' updatevon='.myaddslashes($lehreinheitupdatevon).','.
								       ' sprache='.myaddslashes($lehreinheitsprache).','.
								       ' ext_id='.myaddslashes($lehreinheitext_id).
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
						
						$ausgabe_fa='';
						if(!$error)
						{
							//pa anlegen
							//if($projektarbeitnote=='0') $projektarbeitnote='9';
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
								     myaddslashes($projektarbeitext_id).', '.
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
								if($row3->insertamum!=$projektarbeitinsertamum)
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
									{
										$projektarbeitprojektarbeit_id=$rowu->id;
									}
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
								//projektbetreuer
								$qry="SELECT person_fk FROM mitarbeiter WHERE mitarbeiter_pk='".$row->betreuer_fk."';";
								if($resultu = pg_query($conn_fas, $qry))
								{
									if($rowu=pg_fetch_object($resultu))
									{ 
										$person=$rowu->person_fk;	
									}
									else
									{
										$error=true;
										$error_log.="Betreuer mit mitarbeiter_fk: ".$row->betreuer_fk."' konnte in Tabelle mitarbeiter nicht gefunden werden.\n";
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
								}
								$projektbetreuerprojektarbeit_id		=$projektarbeitprojektarbeit_id;
								$projektbetreuernote			='';
								$projektbetreuerbetreuerart		='Betreuer';  
								$projektbetreuerfaktor			=$row->faktor;
								$projektbetreuername			='';
								$projektbetreuerpunkte			='';
								$projektbetreuerstunden			=$row->stunden;
								$projektbetreuerstundensatz		=$row->stdhonorar;
								//$projektbetreuerupdateamum		=$row->;
								$projektbetreuerupdatevon			="SYNC";
								$projektbetreuerinsertamum		=$row->insertamum;
								//$projektbetreuerinsertvon	 		="SYNC";
								$projektbetreuerext_id			=$row->berufspraktikum_pk;
								
								$qry2="SELECT * FROM lehre.tbl_projektbetreuer WHERE projektarbeit_id='".$projektarbeitprojektarbeit_id."' AND person_id='".$projektbetreuerperson_id."' AND betreuerart_kurzbz='Betreuer';";
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
									if($row2->insertamum!=$projektbetreuerinsertamum) 
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
										"WHERE projektarbeit_id='".$projektbetreuerprojektarbeit_id."' AND person_id='".$projektbetreuerperson_id."'AND betreuerart_kurzbz='Betreuer';";
										
										
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
									$ausgabe_pb1=$ausgabe_pb;
									$ausgabe_pb='';
									$projektbetreuerperson_id1=$projektbetreuerperson_id;
								}
								else
								{			
									$error=true;
									$error_log.="Fehler beim Speichern des Berufspraktikumsbetreuer-Datensatzes:".$projektbetreuerperson_id." \n".$qry."\n";
									$ausgabe_pb='';
								}
								
								
								if(!$error AND $row->vilesci_firmenbetreuer!='' AND $row->vilesci_firmenbetreuer!=null)
								{
									//firmenbetreuer
									$projektbetreuerperson_id			=$row->vilesci_firmenbetreuer;
									$projektbetreuerprojektarbeit_id		=$projektarbeitprojektarbeit_id;
									$projektbetreuernote			='';
									$projektbetreuerbetreuerart		='Betreuer';  
									$projektbetreuerfaktor			='1.0';
									$projektbetreuername			='';
									$projektbetreuerpunkte			='';
									$projektbetreuerstunden			='';
									$projektbetreuerstundensatz		='';
									//$projektbetreuerupdateamum		=$row->;
									$projektbetreuerupdatevon			="SYNC";
									$projektbetreuerinsertamum		=$row->insertamum;
									//$projektbetreuerinsertvon	 		="SYNC";
									$projektbetreuerext_id			=$row->berufspraktikum_pk;
									
									$qry2="SELECT * FROM lehre.tbl_projektbetreuer WHERE projektarbeit_id='".$projektarbeitprojektarbeit_id."' AND person_id='".$projektbetreuerperson_id."' AND betreuerart_kurzbz='".$projektbetreuerbetreuerart."';";
									//echo $qry2;
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
										$error_log.="Fehler beim Zugriff auf Tabelle tbl_projektbetreuer bei betreuer_fk: ".$row->betreuer_fk."\n";	
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
										if($row2->insertamum!=$projektbetreuerinsertamum) 
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
											"WHERE projektarbeit_id='".$projektbetreuerprojektarbeit_id."' AND person_id='".$projektbetreuerperson_id."'AND betreuerart_kurzbz='".$projektbetreuerbetreuerart."';";
											
											
										}
										else 
										{
											$qry="select 1;";
										}
									}
								}
								else 
								{
									$qry="select 1;";
								}
								
								//echo nl2br ($qry."\n");
								if(pg_query($conn,$qry))
								{
									$anzahl_pbb_gesamt++;
									$ausgabe_pb2=$ausgabe_pb;
									$ausgabe_pb='';
									$projektbetreuerperson_id2=$projektbetreuerperson_id;
								}
								else
								{			
									$error=true;
									$error_log.="Fehler beim Speichern des Berufspraktikumsfirmenbetreuer-Datensatzes:".$projektbetreuerperson_id." \n".$qry."\n";
									$ausgabe_pb='';
								}
								
								if($error)
								{
									//ROLLBACK
									$anzahl_fehler_pbg++;
									$ausgabe='';
									$text1.="\n***********Berufspraktikum:".$row->berufspraktikum_pk."\n";
									$text1.=$error_log;
									$text1.=" R1\n";
									$text1.="***********\n";
									pg_query($conn, "ROLLBACK");
								}
								else 
								{
									//COMMIT
									if($projektbetreuernew1)
									{
										$anzahl_pbb_insert++;
										$ausgabe.="Berufspraktikumsbetreuer eingef�gt: UID='".$projektbetreuerperson_id1."' und Projektarbeit='".$projektarbeitlehreinheit_id."'.\n";
									}			
									else 
									{
										if($updatep1)
										{
											$anzahl_pbb_update++;
											$ausgabe.="Berufspraktikumsbetreuer aktualisiert: UID='".$projektbetreuerperson_id1."' und Projektarbeit='".$projektarbeitlehreinheit_id."':".$ausgabe_pb1.".\n";
										}
									}
									if($row->vilesci_firmenbetreuer!='' AND $row->vilesci_firmenbetreuer!='')
									{
										if($projektbetreuernew2)
										{
											$anzahl_pbb_insert++;
											$ausgabe.="Berufspraktikumsfirmenbetreuer eingef�gt: UID='".$projektbetreuerperson_id2."' und Projektarbeit='".$projektarbeitlehreinheit_id."'.\n";
										}			
										else 
										{
											if($updatep2)
											{
												$anzahl_pbb_update++;
												$ausgabe.="Berufspraktikumsfirmenbetreuer aktualisiert: UID='".$projektbetreuerperson_id3."' und Projektarbeit='".$projektarbeitlehreinheit_id."':".$ausgabe_pb2.".\n";
											}
										}
									}
									$ausgabe_pb1='';
									$ausgabe_pb2='';
									$ausgabe_pb='';
									pg_query($conn,'COMMIT;');
									$ausgabe_all.=$ausgabe;
									$ausgabe='';
								}
	
							}	
							else 
							{
								//ROLLBACK
								$anzahl_fehler_pa++;
								$ausgabe='';
								$text3.="\n***********Berufspraktikum:".$row->berufspraktikum_pk."\n";
								$text3.=$error_log;
								$text3.=" R3\n";
								$text3.="***********\n";
								pg_query($conn, "ROLLBACK");
							}
						}	
						else 
						{
							//ROLLBACK
							$anzahl_fehler_le++;
							$ausgabe='';
							$text4.="\n***********Berufspraktikum:".$row->berufspraktikum_pk."\n";
							$text4.=$error_log;
							$text4.=" R4\n";
							$text4.="***********\n";
							pg_query($conn, "ROLLBACK");
						}
					}
					else 
					{
						//ROLLBACK
						$anzahl_fehler++;
						$ausgabe='';
						$text5.="\n***********Berufspraktikumt:".$row->berufspraktikum_pk."\n";
						$text5.=$error_log;
						$text5.=" R5\n";
						$text5.="***********\n";
						pg_query($conn, "ROLLBACK");
					}
				}
				else 
				{
					//ROLLBACK
					$anzahl_fehler++;
					$ausgabe='';
					$text6.="\n***********Berufspraktikum:".$row->berufspraktikum_pk."\n";
					$text6.=$error_log;
					$text6.=" R6\n";
					$text6.="***********\n";
					pg_query($conn, "ROLLBACK");
				}			
			}
			else 
			{
				//ROLLBACK
				$anzahl_fehler++;
				$ausgabe='';
				$text7.="\n***********Berufspraktikum:".$row->berufspraktikum_pk."\n";
				$text7.=$error_log;
				$text7.=" R7\n";
				$text7.="***********\n";
				pg_query($conn, "ROLLBACK");
			}
		}
		else 
		{
			//ROLLBACK
			$anzahl_fehler++;
			$ausgabe='';
			$text8.="\n***********Berufspraktikum:".$row->berufspraktikum_pk."\n";
			$text8.=$error_log;
			$text8.=" R8\n";
			$text8.="***********\n";
			pg_query($conn, "ROLLBACK");
		}
		$error_log_fas1.=$text1;
		$error_log_fas2.=$text2;
		$error_log_fas3.=$text3;
		$error_log_fas4.=$text4;
		$error_log_fas5.=$text5;
		$error_log_fas6.=$text6;
		$error_log_fas7.=$text7;
		$error_log_fas8.=$text8;
	}
//echo und mail
echo nl2br("Berufspraktikumsynchro Ende: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");

$error_log_fas="Sync Berufspraktikum\n------------------------\n\n".$error_log_fas1."\n".$error_log_fas2."\n".$error_log_fas3."\n".$error_log_fas4."\n".$error_log_fas5."\n".$error_log_fas6."\n".$error_log_fas7."\n".$error_log_fas8;
echo nl2br("Anzahl Berufspraktika: ".$anzahl_quelle.".\n");
echo "Allgemeine Fehler: ".$anzahl_fehler.", lehrveranstaltung_fk<1: ".$anzahl_lv_fehler.", betreuer_fk<1: ".$anzahl_betreuer_fehler."<br>";
echo "Lehreinheiten:       Gesamt: ".$anzahl_le_gesamt." / Eingef�gt: ".$anzahl_le_insert." / Ge�ndert: ".$anzahl_le_update." / Fehler: ".$anzahl_fehler_le."<br>";
echo "Projektarbeiten:   Gesamt: ".$anzahl_pa_gesamt." / Eingef�gt: ".$anzahl_pa_insert." / Ge�ndert: ".$anzahl_pa_update." / Fehler: ".$anzahl_fehler_pa."<br>";
echo "Betreuer:       Gesamt: ".$anzahl_pbb_gesamt." / Eingef�gt: ".$anzahl_pbb_insert." / Ge�ndert: ".$anzahl_pbb_update." / Fehler: ".$anzahl_fehler_pbb."<br><br>";

echo nl2br($error_log_fas."\n--------------------------------------------------------------------------------\n");
echo nl2br($ausgabe_all);

mail($adress, 'SYNC Berufspraktikum von '.$_SERVER['HTTP_HOST'], 
"Allgemeine Fehler: ".$anzahl_fehler.", lehrveranstaltung_fk<1: ".$anzahl_lv_fehler.", betreuer_fk<1: ".$anzahl_betreuer_fehler.", Anzahl Berufspraktika: ".$anzahl_quelle.".\n".
"Lehreinheiten:       Gesamt: ".$anzahl_le_gesamt." / Eingef�gt: ".$anzahl_le_insert." / Ge�ndert: ".$anzahl_le_update." / Fehler: ".$anzahl_fehler_le."\n".
"Projektarbeiten:   Gesamt: ".$anzahl_pa_gesamt." / Eingef�gt: ".$anzahl_pa_insert." / Ge�ndert: ".$anzahl_pa_update." / Fehler: ".$anzahl_fehler_pa."\n".
"Betreuer:       Gesamt: ".$anzahl_pbb_gesamt." / Eingef�gt: ".$anzahl_pbb_insert." / Ge�ndert: ".$anzahl_pbb_update." / Fehler: ".$anzahl_fehler_pbb."\n\n".
$ausgabe_all,"From: vilesci@technikum-wien.at");

mail($adress, 'SYNC-Fehler Berufspraktikum  von '.$_SERVER['HTTP_HOST'], $error_log_fas, "From: vilesci@technikum-wien.at");
}
?>
</body>
</html>