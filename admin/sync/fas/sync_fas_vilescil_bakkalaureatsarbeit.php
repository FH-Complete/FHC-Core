<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Bachelorarbeitsdatensaetze von FAS DB in PORTAL DB
//* benötigt: tbl_lehrveranstaltung, tbl_lehreinheit, tbl_fachbereich
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
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		pg_query($conn_fas, "BEGIN");
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
				
				$qry2="SELECT projektarbeit_id, ext_id FROM lehre.tbl_projektarbeit WHERE projekttyp_kurzbz='Bachelorarbeit' AND ext_id='".$row->bakkalaureatsarbeit_pk."';";
				if($result2 = pg_query($conn, $qry2))
				{
					if(pg_num_rows($result2)>0) //eintrag gefunden
					{
						if($row2=pg_fetch_object($result2))
						{ 
							// update, wenn datensatz bereits vorhanden
							$projektarbeitnew=false;
							$projektarbeitprojektarbeit_id=$row2->projektarbeit_id;
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
								$lehreinheitinsert++;
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
								$lehreinheitupdate++;
							}
						}
						if(!$error)
						{
							//pa anlegen
						
							if(!$projektarbeit->save())
							{
								$error_log.=$projektarbeit->errormsg."\n";
								$anzahl_fehler++;
							}
							else 
							{
								if($projektarbeit->new=true)
								{
									$ausgabe.="Projektarbeit angelegt: Student='".$projektarbeit->student_uid."' und Lehreinheit='".$projektarbeit->lehreinheit_id."'.\n";
								}
								else 
								{
									$ausgabe="Projektarbeit aktualisiert: Student='".$projektarbeit->student_uid."' und Lehreinheit='".$projektarbeit->lehreinheit_id."'.\n";
								}
								$anzahl_eingefuegt++;
							}
							
						}	
						else 
						{
							//ROLLBACK
						}
					}
					else 
					{
						//ROLLBACK
					}
				}
				else 
				{
					//ROLLBACK
				}			
			}
			else 
			{
				//ROLLBACK
			}
		}
		else 
		{
			//ROLLBACK
		}		
	}
}