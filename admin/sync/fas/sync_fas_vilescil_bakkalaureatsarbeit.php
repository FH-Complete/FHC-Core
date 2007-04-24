<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Bachelorarbeitsdatensaetze von FAS DB in PORTAL DB
//* ben�tigt: tbl_lehrveranstaltung, tbl_lehreinheit, tbl_fachbereich
//*

require_once('../../../vilesci/config.inc.php');
require_once('../../../include/projektarbeit.class.php');
require_once('../../../include/projektbetreuer.class.php');
require_once('../../../include/lehreinheit.class.php');


$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$error_log_fas='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_fehler=0;
$anzahl_quelle2=0;
$anzahl_eingefuegt2=0;
$anzahl_fehler2=0;
$fachbereich_kurzbz='';
$ausgabe='';
$ausgabe_all='';

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
<title>Synchro - FAS -> Portal - Bachelorarbeit</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
//nation
$qry_mail = "SELECT * FROM bakkalaureatsarbeit;";

if($result = pg_query($conn_fas, $qry_main))
{
	echo nl2br("Bachelorarbeit Sync\n------------------------\n");
	echo nl2br("Beginn: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		pg_query($conn, "BEGIN");
		$error=false;
		$error_log='';
		$projektarbeitprojekttyp_kurzbz	='Bachelor';
		$projektarbeittitel			=$row->titel;
		//$projektarbeitlehreinheit_id	='';
		//$projektarbeitstudent_uid		='';
		$projektarbeitfirma_id		='';
		$projektarbeitnote			=$row->note;
		$projektarbeitpunkte			=$row->punkte;
		$projektarbeitbeginn			='';
		$projektarbeitende			=$row->datum;
		$projektarbeitfaktor			='1.0';
		$projektarbeitfreigegeben		=$row->gesperrtbis==null?true:false;
		$projektarbeitgesperrtbis		=$row->gesperrtbis;
		$projektarbeitstundensatz		=$row->betreuerstundenhonorar;
		$projektarbeitgesamtstunden	=$row->betreuerstunden;
		$projektarbeitthemenbereich	=$row->themenbereich;
		$projektarbeitanmerkung		='';		
		//$projektarbeitupdateamum	='';
		$projektarbeitupdatevon		="SYNC";
		$projektarbeitinsertamum		=$row->creationdate;
		$projektarbeitinsertvon		=$row->creationuser;
		$projektarbeitext_id			=$row->bakkalaureatsarbeit_pk;	
		
		//$lehreinheitlehrveranstaltung_id	='';
		//$lehreinheitstudiensemester_kz	='';
		//$lehreinheitlehrfach_id			='';
		$lehreinheitlehrform_kurzbz			='BE';
		$lehreinheitstundenblockung		='1';
		$lehreinheitwochenrythmus			='1';
		$lehreinheitstart_kw				='';
		$lehreinheitraumtyp				='DIV';
		$lehreinheitraumtypalternativ		='DIV';
		$lehreinheitsprache				=$row->englisch==true?'english':'german';
		$lehreinheitlehre				=false;
		$lehreinheitanmerkung			='Bachelorarbeit';
		$lehreinheitunr				='';
		$lehreinheitlvnr				='';
		//$lehreinheitupdateamum			='';
		$lehreinheitupdatevon			="SYNC";
		$lehreinheitinsertamum			=$row->creationdate;
		$lehreinheitinsertvon			=$row->creationuser;
		$lehreinheitext_id				=$row->bakkalaureatsarbeit_pk;
			
		$studiengang_kz='';
		$semester='';
		$lva='';
		
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
				$error_log.="Student mit student_fk: $row->student_fk konnte nicht gefunden werden.\n";
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
						$error=true;
						$error_log.="Lehrfach mit Fachbereich='".$fachbereich_kurzbz."', Semester='".$semester."' und Studiengang='".$studiengang."' nicht gefunden.\n";
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
				
				$qry3="SELECT projektarbeit_id, ext_id FROM lehre.tbl_projektarbeit WHERE projekttyp_kurzbz='Bachelorarbeit' AND ext_id='".$row->bakkalaureatsarbeit_pk."';";
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
					$qry2="SELECT * FROM lehre.tbl_lehreinheit WHERE lehrform_kurzbz='BE' AND ext_id='".$row->bakkalaureatsarbeit_pk."';";
					if($result2 = pg_query($conn, $qry2))
					{
						if(pg_num_rows($result2)>0) //eintrag gefunden
						{
							if($row2=pg_fetch_object($result2))
							{ 
								// update, wenn datensatz bereits vorhanden
								$lehreinheitnew=false;
								$lehreinheitlehreinheit_id=$row2->lehreinheit_id;
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
								$updatelev=true;
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
								$updatelev=true;
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
								$updatelev=true;
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
								$updatelev=true;
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
								$updatelev=true;
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
								$updatelev=true;
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
								$updatelev=true;
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
								$updatelev=true;
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
								$updatelev=true;
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
								$updatelev=true;
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
								$updatelev=true;
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
								$updatelev=true;
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
								$updatelev=true;
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
								$updatelev=true;
								if(strlen(trim($ausgabe_le))>0)
								{
									$ausgabe_le.=", Sprache: '".$lehreinheitsprache."' (statt '".$row2->sprache."')";
								}
								else
								{
									$ausgabe_le="Sprache: '".$lehreinheitsprache."' (statt '".$row2->sprache."')";
								}
							}
							if($updatelv)
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
								       ' updateamum='.myaddslashes($lehreinheitupdateamum).','.
								       ' updatevon='.myaddslashes($lehreinheitupdatevon).','.
								       ' sprache='.myaddslashes($lehreinheitsprache).','.
								       ' ext_id='.myaddslashes($lehreinheitext_id).
								       " WHERE lehreinheit_id=".myaddslashes($lehreinheitlehreinheit_id).";";
								       $ausgabe.="Lehreinheit aktualisiert bei Lehrveranstaltung='".$lehreinheitlehrveranstaltung_id."', Studiensemester='".$lehreinheit->studiensemester_kz."' und Lehrfach='".$lehreinheit->lehrfach_id."':.$ausgabe_le.\n";
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
								$ausgabe.="Lehreinheit angelegt: Lehrveranstaltung='".$lehreinheitlehrveranstaltung_id."', Studiensemester='".$lehreinheit->studiensemester_kz."' und Lehrfach='".$lehreinheitlehrfach_id."'.\n";
							}
							else 
							{
								$anzahl_le_update++;
							}
							$anzahl_le_gesamt++;
						}
						if(!$error)
						{
							//pa anlegen
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
								     myaddslashes($projektarbeitext_id).',  now(), '.
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
								if($row3->freigegeben!=($projektarbeitfreiggegeben?'t':'f'))
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
								     	'updateamum= now(), '.
								     	'updatevon='.myaddslashes($projektarbeitupdatevon).' '.
								     	//'firmentyp='.myaddslashes($projektarbeitfirmentyp_kurzbz).' '.
									'WHERE projektarbeit_id='.myaddslashes($projektarbeitprojektarbeit_id).';';
									$ausgabe.="Projektarbeit aktualisiert: Student='".$projektarbeitstudent_uid."' und Lehreinheit='".$projektarbeitlehreinheit_id."':".$ausgabe_pa.".\n";
								}
							}
							//echo $qry;
							if(pg_query($this->conn,$qry))
							{
								$ausgabe_pa='';
								if($projektarbeitnew)
								{
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
								//projektbetreuer 2x
								
								$qry="SELECT person_portal FROM sync.tbl_syncperson WHERE person_fas='$row->betreuer_fk'"; //betreuer_fk -> person_id
								if($resultu = pg_query($conn, $qry))
								{
									if($rowu=pg_fetch_object($resultu))
									{ 
										$projektbetreuerperson_id=$rowu->person_portal;	
									}
									else
									{
										$error=true;
										$error_log.="Betreuer mit person_fk: $row->betreuer_fk konnte in syncperson nicht gefunden werden.\n";
									}
								}
								$projektbetreuerprojektarbeit_id		=$projektarbeitprojektarbeit_id;
								$projektbetreuernote			='';
								$projektbetreuerbetreuerart		='b';  //b=Bachelorarbeitsbetreuer
								$projektbetreuerfaktor			='1,0';
								$projektbetreuername			='';
								$projektbetreuerpunkte			='';
								$projektbetreuerstunden			=$row->betreuerstunden;
								$projektbetreuerstundensatz		=$row->betreuerstundenhonorar;
								//$projektbetreuerupdateamum		=$row->;
								$projektbetreuerupdatevon			="SYNC";
								//$projektbetreuerinsertamum		=$row->creationdate;
								$projektbetreuerinsertvon	 		="SYNC";
								$projektbetreuerext_id			=$row->bakkalaureatsarbeit_pk;
								
								$qry2="SELECT * FROM lehre.tbl_projektbetreuer WHERE projektarbeit_id='".$projektarbeitprojektarbeit_id."' AND person_id='".$projektbetreuerperson_id."'AND betreuerart='".$projektbetreuerbetreuerart."';";
								if($result2 = pg_query($conn, $qry2))
								{
									if(pg_num_rows($result2)>0) //wenn dieser eintrag schon vorhanden ist
									{
										if($row2=pg_fetch_object($result2))
										{
											$projektbetreuerperson_id=$row2->person_id;
											$projektbetreuernew=false;		
										}
										else $projektbetreuernew=true;
									}
									else $projektbetreuernew=true;
								}
								else
								{
									$error=true;
									$error_log.='Fehler beim Zugriff auf Tabelle tbl_projektbetreuer bei betreuer_fk: '.$row->betreuer_fk."\n";	
								}
								if($projektbetreuernew)
								{
									$qry='INSERT INTO lehre.tbl_projektbetreuer (person_id, projektarbeit_id, note, betreuerart, faktor, name,
										 punkte, stunden, stundensatz, ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
									     myaddslashes($projektbetreuerperson_id).', '.
									     myaddslashes($projektbetreuerprojektarbeit_id).', '.
									     myaddslashes($projektbetreuernote).', '.
									     myaddslashes($projektbetreuerbetreuerart).', '.
									     myaddslashes($projektbetreuerfaktor).', '.
									     myaddslashes($projektbetreuername).', '.
									     myaddslashes($projektbetreuerpunkte).', '.
									     myaddslashes($projektbetreuerstunden).', '.
									     myaddslashes($projektbetreuerstundensatz).', '.
									     myaddslashes($projektbetreuerext_id).',  now(), '.
									     myaddslashes($projektbetreuerinsertvon).', now(), '.
									     myaddslashes($projektbetreuerupdatevon).');';
									$ausgabe.="Bachelorarbeitsbetreuer eingef�gt: UID='".$projektbetreuerperson_id."' und Projektarbeit='".$projektarbeitlehreinheit_id."'.\n";
								}
								else 
								{
									$updatep=false;			
									if($row2->person_id!=$projektbetreuerperson_id) 
									{
										$updatep=true;
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
										$updatep=true;
										if(strlen(trim($ausgabe_pb))>0)
										{
											$ausgabe_pb.=", Projektarbeit: '".$projektbetreuerprojektarbeit_id."' (statt '".$row2->projektarbeit_id."')";
										}
										else
										{
											$ausgabe_pb="Projektarbeit: '".$projektbetreuerprojektarbeit_id."' (statt '".$row2->projektarbeit_id."')";
										}
									}
									if($row2->note!=$projektbetreuernote) 
									{
										$updatep=true;
										if(strlen(trim($ausgabe_pb))>0)
										{
											$ausgabe_pb.=", Note: '".$projektbetreuernote."' (statt '".$row2->note."')";
										}
										else
										{
											$ausgabe_pb="Note: '".$projektbetreuernote."' (statt '".$row2->note."')";
										}
									}
									if($row2->betreuerart!=$projektbetreuerbetreuerart) 
									{
										$updatep=true;
										if(strlen(trim($ausgabe_pb))>0)
										{
											$ausgabe_pb.=", Betreuerart: '".$projektbetreuerbetreuerart."' (statt '".$row2->betreuerart."')";
										}
										else
										{
											$ausgabe_pb="Betreuerart: '".$projektbetreuerbetreuerart."' (statt '".$row2->betreuerart."')";
										}
									}
									if($row2->faktor!=$projektbetreuerfaktor) 
									{
										$updatep=true;
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
										$updatep=true;
										if(strlen(trim($ausgabe_pb))>0)
										{
											$ausgabe_pb.=", Name: '".$projektbetreuername."' (statt '".$row2->name."')";
										}
										else
										{
											$ausgabe_pb="Name: '".$projektbetreuername."' (statt '".$row2->name."')";
										}
									}
									if($row2->punkte!=$projektbetreuerpunkte) 
									{
										$updatep=true;
										if(strlen(trim($ausgabe_pb))>0)
										{
											$ausgabe_pb.=", Punkte: '".$projektbetreuerpunkte."' (statt '".$row2->punkte."')";
										}
										else
										{
											$ausgabe_pb="Punkte: '".$projektbetreuerpunkte."' (statt '".$row2->punkte."')";
										}
									}
									if($row2->stunden!=$projektbetreuerstunden) 
									{
										$updatep=true;
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
										$updatep=true;
										if(strlen(trim($ausgabe_pb))>0)
										{
											$ausgabe_pb.=", Stundensatz: '".$projektbetreuerstundensatz."' (statt '".$row2->stundensatz."')";
										}
										else
										{
											$ausgabe_pb="Stundensatz: '".$projektbetreuerstundensatz."' (statt '".$row2->stundensatz."')";
										}
									}
									if($updatep)
									{
										$qry='UPDATE lehre.tbl_projektbetreuer SET '.
										'person_id='.myaddslashes($projektbetreuerperson_id).', '. 
										'projektarbeit_id='.myaddslashes($projektbetreuerprojektarbeit_id).', '.
										'note='.myaddslashes($projektbetreuernote).', '.
										'betreuerart='.myaddslashes($projektbetreuerbetreuerart).', '.
										'faktor='.myaddslashes($projektbetreuerfaktor).', '.
										'name='.myaddslashes($projektbetreuername).', '.
										'punkte'.myaddslashes($projektbetreuerpunkte).', '.
										'stunden='.myaddslashes($projektbetreuerstunden).', '.
										'stundensatz='.myaddslashes($projektbetreuerstundensatz).', '.
										'updateamum= now(), '.
									     	'updatevon='.myaddslashes($projektbetreuerupdatevon).' '.
										'WHERE projektarbeit_id='.myaddslashes($projektbetreuerprojektarbeit_id).';';
										$ausgabe.="Bachelorarbeitsbetreuer aktualisiert: UID='".$projektbetreuerperson_id."' und Projektarbeit='".$projektarbeitlehreinheit_id."':".$ausgabe_pb.".\n";
									}
								}
								if(pg_query($conn,$qry))
								{
									if($projektbetreuernew)
									{
										$anzahl_pbb_insert++;
									}			
									else 
									{
										if($updatep)
										{
											$anzahl_pbb_update++;
										}
									}
									$anzahl_pbb_gesamt++;
								}
								else
								{			
									$error=true;
									$error_log.='Fehler beim Speichern des Bachelorarbeitsbetreuer-Datensatzes:'.$projektbetreuerperson_id." \n".$qry."\n";
									$ausgabe_pb='';
								}
															
								$qry="SELECT person_portal FROM sync.tbl_syncperson WHERE person_fas='$row->begutachter_fk'";  //begutachter_fk -> person_id
								if($resultu = pg_query($conn, $qry))
								{
									if($rowu=pg_fetch_object($resultu))
									{ 
										$projektbetreuerperson_id=$rowu->person_portal;	
									}
									else{
										$error=true;
										$error_log.="Begutachter mit person_fk: $row->betreuer_fk konnte in syncperson nicht gefunden werden.\n";
									}
								}
								//$projektbetreuer->person_id		='';
								$projektbetreuerprojektarbeit_id		=$projektarbeitprojektarbeit_id;
								$projektbetreuernote			=$row->note;
								$projektbetreuerbetreuerart		='g';  //g=Bachelorarbeitsbegutachter
								$projektbetreuerfaktor			='1,0';
								$projektbetreuername			='';
								$projektbetreuerpunkte			=$row->punkte;
								$projektbetreuerstunden			='';
								$projektbetreuerstundensatz		='';
								//$projektbetreuerupdateamum		=$row->;
								$projektbetreuerupdatevon			="SYNC";
								//$projektbetreuerinsertamum		=$row->;
								$projektbetreuerinsertvon			="SYNC";
								$projektbetreuerext_id			=$row->bakkalaureatsarbeit_pk;
							
								$qry2="SELECT person_id FROM lehre.tbl_projektbetreuer WHERE projektarbeit_id='".$projektbetreuerprojektarbeit_id."' AND person_id='".$projektbetreuerperson_id."'AND betreuerart='".$projektbetreuerbetreuerart."';";
								if($resultu = pg_query($conn, $qry2))
								{
									if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
									{
										if($rowu=pg_fetch_object($resultu))
										{
											$projektbetreuerperson_id=$rowu->person_id;
											$projektbetreuernew=false;		
										}
										else $projektbetreuernew=true;
									}
									else $projektbetreuernew=true;
								}
								else
								{
									$error=true;
									$error_log.='Fehler beim Zugriff auf Tabelle tbl_projektbetreuer bei betreuer_fk: '.$row->betreuer_fk."\n";	
								}
								if($error)
								{
									if($projektbetreuernew)
									{
										$qry='INSERT INTO lehre.tbl_projektbetreuer (person_id, projektarbeit_id, note, betreuerart, faktor, name,
											 punkte, stunden, stundensatz, ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
										     myaddslashes($projektbetreuerperson_id).', '.
										     myaddslashes($projektbetreuerprojektarbeit_id).', '.
										     myaddslashes($projektbetreuernote).', '.
										     myaddslashes($projektbetreuerbetreuerart).', '.
										     myaddslashes($projektbetreuerfaktor).', '.
										     myaddslashes($projektbetreuername).', '.
										     myaddslashes($projektbetreuerpunkte).', '.
										     myaddslashes($projektbetreuerstunden).', '.
										     myaddslashes($projektbetreuerstundensatz).', '.
										     myaddslashes($projektbetreuerext_id).',  now(), '.
										     myaddslashes($projektbetreuerinsertvon).', now(), '.
										     myaddslashes($projektbetreuerupdatevon).');';	
										$ausgabe.="Bachelorarbeitsbegutachter eingef�gt: UID='".$projektbetreuerperson_id."' und Projektarbeit='".$projektarbeitlehreinheit_id."'.\n";
									}
									else 
									{
										
										$updatep=false;			
										if($row2->person_id!=$projektbetreuerperson_id) 
										{
											$updatep=true;
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
											$updatep=true;
											if(strlen(trim($ausgabe_pb))>0)
											{
												$ausgabe_pb.=", Projektarbeit: '".$projektbetreuerprojektarbeit_id."' (statt '".$row2->projektarbeit_id."')";
											}
											else
											{
												$ausgabe_pb="Projektarbeit: '".$projektbetreuerprojektarbeit_id."' (statt '".$row2->projektarbeit_id."')";
											}
										}
										if($row2->note!=$projektbetreuernote) 
										{
											$updatep=true;
											if(strlen(trim($ausgabe_pb))>0)
											{
												$ausgabe_pb.=", Note: '".$projektbetreuernote."' (statt '".$row2->note."')";
											}
											else
											{
												$ausgabe_pb="Note: '".$projektbetreuernote."' (statt '".$row2->note."')";
											}
										}
										if($row2->betreuerart!=$projektbetreuerbetreuerart) 
										{
											$updatep=true;
											if(strlen(trim($ausgabe_pb))>0)
											{
												$ausgabe_pb.=", Betreuerart: '".$projektbetreuerbetreuerart."' (statt '".$row2->betreuerart."')";
											}
											else
											{
												$ausgabe_pb="Betreuerart: '".$projektbetreuerbetreuerart."' (statt '".$row2->betreuerart."')";
											}
										}
										if($row2->faktor!=$projektbetreuerfaktor) 
										{
											$updatep=true;
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
											$updatep=true;
											if(strlen(trim($ausgabe_pb))>0)
											{
												$ausgabe_pb.=", Name: '".$projektbetreuername."' (statt '".$row2->name."')";
											}
											else
											{
												$ausgabe_pb="Name: '".$projektbetreuername."' (statt '".$row2->name."')";
											}
										}
										if($row2->punkte!=$projektbetreuerpunkte) 
										{
											$updatep=true;
											if(strlen(trim($ausgabe_pb))>0)
											{
												$ausgabe_pb.=", Punkte: '".$projektbetreuerpunkte."' (statt '".$row2->punkte."')";
											}
											else
											{
												$ausgabe_pb="Punkte: '".$projektbetreuerpunkte."' (statt '".$row2->punkte."')";
											}
										}
										if($row2->stunden!=$projektbetreuerstunden) 
										{
											$updatep=true;
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
											$updatep=true;
											if(strlen(trim($ausgabe_pb))>0)
											{
												$ausgabe_pb.=", Stundensatz: '".$projektbetreuerstundensatz."' (statt '".$row2->stundensatz."')";
											}
											else
											{
												$ausgabe_pb="Stundensatz: '".$projektbetreuerstundensatz."' (statt '".$row2->stundensatz."')";
											}
										}
										if($updatep)
										{
											$qry='UPDATE lehre.tbl_projektbetreuer SET '.
											'person_id='.myaddslashes($projektbetreuerperson_id).', '. 
											'projektarbeit_id='.myaddslashes($projektbetreuerprojektarbeit_id).', '.
											'note='.myaddslashes($projektbetreuernote).', '.
											'betreuerart='.myaddslashes($projektbetreuerbetreuerart).', '.
											'faktor='.myaddslashes($projektbetreuerfaktor).', '.
											'name='.myaddslashes($projektbetreuername).', '.
											'punkte'.myaddslashes($projektbetreuerpunkte).', '.
											'stunden='.myaddslashes($projektbetreuerstunden).', '.
											'stundensatz='.myaddslashes($projektbetreuerstundensatz).', '.
											'updateamum= now(), '.
										     	'updatevon='.myaddslashes($projektbetreuerupdatevon).' '.
											'WHERE projektarbeit_id='.myaddslashes($projektbetreuerprojektarbeit_id).';';
											$ausgabe.="Bachelorarbeitsbegutachter aktualisiert: UID='".$projektbetreuerperson_id."' und Projektarbeit='".$projektarbeitlehreinheit_id."':".$ausgabe_pb.".\n";
										}	
									}
									if(pg_query($conn,$qry))
									{
										if($projektbetreuernew)
										{
											$anzahl_pbg_insert++;
										}			
										else 
										{
											if($updatep)
											{
												$anzahl_pbg_update++;
											}
										}
										$anzahl_pbg_gesamt++;
									}
									else
									{			
										$error=true;
										$error_log.='Fehler beim Speichern des Bachelorarbeitsbetreuer-Datensatzes:'.$projektbetreuerperson_id." \n".$qry."\n";
										$ausgabe_pb='';
									}
									if($error)
									{
										//ROLLBACK
										$anzahl_fehler_pbg++;
										$ausgabe='';
										$text1.="\n***********".$student_uid." / ".$nachname.", ".$vorname." / ".$matrikelnr."\n";
										$text1.=$error_log;
										$text1.=" R1\n";
										$text1.="***********\n\n";
										pg_query($conn, "ROLLBACK");
									}
									else 
									{
										//COMMIT
										pg_query($conn,'COMMIT;');
										$ausgabe_all.=$ausgabe;
										$ausgabe='';
									}
								}
								else 
								{
									//ROLLBACK
									$anzahl_fehler_pbb++;
									$ausgabe='';
									$text1.="\n***********".$student_uid." / ".$nachname.", ".$vorname." / ".$matrikelnr."\n";
									$text1.=$error_log;
									$text1.=" R2\n";
									$text1.="***********\n\n";
									pg_query($conn, "ROLLBACK");
								}
							}	
							else 
							{
								//ROLLBACK
								$anzahl_fehler_pa++;
								$ausgabe='';
								$text1.="\n***********".$student_uid." / ".$nachname.", ".$vorname." / ".$matrikelnr."\n";
								$text1.=$error_log;
								$text1.=" R3\n";
								$text1.="***********\n\n";
								pg_query($conn, "ROLLBACK");
							}
						}	
						else 
						{
							//ROLLBACK
							$anzahl_fehler_le++;
							$ausgabe='';
							$text1.="\n***********".$student_uid." / ".$nachname.", ".$vorname." / ".$matrikelnr."\n";
							$text1.=$error_log;
							$text1.=" R4\n";
							$text1.="***********\n\n";
							pg_query($conn, "ROLLBACK");
						}
					}
					else 
					{
						//ROLLBACK
						$anzahl_fehler++;
						$ausgabe='';
						$text1.="\n***********".$student_uid." / ".$nachname.", ".$vorname." / ".$matrikelnr."\n";
						$text1.=$error_log;
						$text1.=" R5\n";
						$text1.="***********\n\n";
						pg_query($conn, "ROLLBACK");
					}
				}
				else 
				{
					//ROLLBACK
					$anzahl_fehler++;
					$ausgabe='';
					$text1.="\n***********".$student_uid." / ".$nachname.", ".$vorname." / ".$matrikelnr."\n";
					$text1.=$error_log;
					$text1.=" R6\n";
					$text1.="***********\n\n";
					pg_query($conn, "ROLLBACK");
				}			
			}
			else 
			{
				//ROLLBACK
				$anzahl_fehler++;
				$ausgabe='';
				$text1.="\n***********".$student_uid." / ".$nachname.", ".$vorname." / ".$matrikelnr."\n";
				$text1.=$error_log;
				$text1.=" R7\n";
				$text1.="***********\n\n";
				pg_query($conn, "ROLLBACK");
			}
		}
		else 
		{
			//ROLLBACK
			$anzahl_fehler++;
			$ausgabe='';
			$text1.="\n***********".$student_uid." / ".$nachname.", ".$vorname." / ".$matrikelnr."\n";
			$text1.=$error_log;
			$text1.=" R8\n";
			$text1.="***********\n\n";
			pg_query($conn, "ROLLBACK");
		}		
	}
//echo und mail
echo nl2br("Bachelorarbeitsynchro Ende: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");

$error_log="Sync Bachelorarbeiten\n------------\n\n".$text1;
echo nl2br("Allgemeine Fehler: ".$anzahl_fehler.".\n");
echo nl2br("Lehreinheiten:       Gesamt: ".$anzahl_le_gesamt." / Eingef�gt: ".$anzahl_le_insert." / Ge�ndert: ".$anzahl_le_update." / Fehler: ".$anzahl_fehler_le."\n");
echo nl2br("Projektarbeiten:   Gesamt: ".$anzahl_pa_gesamt." / Eingef�gt: ".$anzahl_pa_insert." / Ge�ndert: ".$anzahl_pa_update." / Fehler: ".$anzahl_fehler_pa."\n");
echo nl2br("Betreuer:       Gesamt: ".$anzahl_pbb_gesamt." / Eingef�gt: ".$anzahl_pbb_insert." / Ge�ndert: ".$anzahl_pbb_update." / Fehler: ".$anzahl_fehler_pbb."\n");
echo nl2br("Begutachter:  Gesamt: ".$anzahl_pbg_gesamt." / Eingef�gt: ".$anzahl_pbg_insert." / Ge�ndert: ".$anzahl_pbg_update." / Fehler: ".$anzahl_fehler_pbg."\n");
echo nl2br($error_log."\n--------------------------------------------------------------------------------\n");
echo nl2br($ausgabe_all);

//mail($adress, 'SYNC-Fehler Student von '.$_SERVER['HTTP_HOST'], $error_log,"From: vilesci@technikum-wien.at");

/*mail($adress, 'SYNC Bachelorarbeiten  von '.$_SERVER['HTTP_HOST'], "Allgemeine Fehler: ".$anzahl_fehler.".\n".
"Lehreinheiten:       Gesamt: ".$anzahl_le_gesamt." / Eingef�gt: ".$anzahl_le_insert." / Ge�ndert: ".$anzahl_le_update." / Fehler: ".$anzahl_fehler_le."\n".
"Projektarbeiten:   Gesamt: ".$anzahl_pa_gesamt." / Eingef�gt: ".$anzahl_pa_insert." / Ge�ndert: ".$anzahl_pa_update." / Fehler: ".$anzahl_fehler_pa."\n".
"Betreuer:       Gesamt: ".$anzahl_pbb_gesamt." / Eingef�gt: ".$anzahl_pbb_insert." / Ge�ndert: ".$anzahl_pbb_update." / Fehler: ".$anzahl_fehler_pbb."\n".
"Begutachter:  Gesamt: ".$anzahl_pbg_gesamt." / Eingef�gt: ".$anzahl_pbg_insert." / Ge�ndert: ".$anzahl_pbg_update." / Fehler: ".$anzahl_fehler_pbg."\n".
$ausgabe_all, "From: vilesci@technikum-wien.at");*/
}