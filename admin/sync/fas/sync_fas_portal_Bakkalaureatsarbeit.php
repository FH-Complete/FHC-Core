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
$qry = "SELECT * FROM bakkalaureatsarbeit;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Bachelorarbeit Sync\n------------------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		pg_query($conn_fas, "BEGIN");
		$error=false;
		$error_log='';
		$projektarbeit				=new projektarbeit($conn);
		$projektarbeit->projekttyp_kurzbz	='Bachelor';
		$projektarbeit->titel			=$row->titel;
		//$projektarbeit->lehreinheit_id	='';
		//$projektarbeit->student_uid	='';
		$projektarbeit->firma_id		='';
		$projektarbeit->note			=$row->note;
		$projektarbeit->punkte		=$row->punkte;
		$projektarbeit->beginn		='';
		$projektarbeit->ende		=$row->datum;
		$projektarbeit->faktor		='1.0';
		$projektarbeit->freigegeben	=$row->gesperrtbis==null?true:false;
		$projektarbeit->gesperrtbis		=$row->gesperrtbis;
		$projektarbeit->stundensatz	=$row->betreuerstundenhonorar;
		$projektarbeit->gesamtstunden	=$row->betreuerstunden;
		$projektarbeit->themenbereich	=$row->themenbereich;
		$projektarbeit->anmerkung		='';		
		//$projektarbeit->updateamum	=$row->;
		$projektarbeit->updatevon		="SYNC";
		//$projektarbeit->insertamum	=$row->;
		$projektarbeit->insertvon		="SYNC";
		$projektarbeit->ext_id		=$row->bakkalaureatsarbeit_pk;
		
		
		$studiengang_kz='';
		$semester='';
		$lva='';
		//student_id ermitteln
		$qry="SELECT student_uid FROM public.tbl_student WHERE ext_id='".$row->student_fk."';";
		if($resulto=pg_query($conn, $qry))
		{
			if($rowo=pg_fetch_object($resulto))
			{ 
				$projektarbeit->student_uid=$rowo->student_uid;
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
					$lehreinheit->lehrveranstaltung_id=$row1->lehrveranstaltung_id;
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
						$lehreinheit->lehrfach_id=$rowo->lehrfach_id;
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
						$lehreinheit->studiensemester_kurzbz=$rowo->studiensemester_kurzbz;
					}
					else 
					{
						$error=true;
						$error_log.="Studiensemester mit ext_id='".$row->studiensemester_fk."' nicht gefunden.\n";
					}
				}
				
				$lehreinheit					=new lehreinheit($conn);
				//$lehreinheit->lehrveranstaltung_id	='';
				//$lehreinheit->studiensemester_kz	='';
				//$lehreinheit->lehrfach_id			='';
				$lehreinheit->lehrform_kurzbz		='BE';
				$lehreinheit->stundenblockung		='1';
				$lehreinheit->wochenrythmus		='1';
				$lehreinheit->start_kw			='';
				$lehreinheit->raumtyp			='DIV';
				$lehreinheit->raumtypalternativ		='DIV';
				$lehreinheit->sprache			=$row->englisch==true?'english':'german';
				$lehreinheit->lehre				=false;
				$lehreinheit->anmerkung			='Bachelorarbeit';
				$lehreinheit->unr				='';
				$lehreinheit->lvnr				='';
				//$lehreinheit->updateamum		=$row->;
				$lehreinheit->updatevon			="SYNC";
				//$lehreinheit->insertamum			=$row->;
				$lehreinheit->insertvon			="SYNC";
				$lehreinheit->ext_id				=$row->bakkalaureatsarbeit_pk;
				
				
				
				
				
				
				
				
				
				
				
				//betreuer
				$qry="SELECT person_portal FROM sync.tbl_syncperson WHERE person_fas='$row->betreuer_fk'"; //betreuer_fk -> person_id
				if($resultu = pg_query($conn, $qry))
				{
					if($rowu=pg_fetch_object($resultu))
					{ 
						$projektbetreuer->person_id=$rowu->person_portal;	
					}
					else
					{
						$error=true;
						$error_log.="Betreuer mit person_fk: $row->betreuer_fk konnte in syncperson nicht gefunden werden.\n";
					}
				}
				$projektbetreuer				=new projektbetreuer($conn);
				//$projektbetreuer->person_id		='';
				//$projektbetreuer->projektarbeit_id	='';
				$projektbetreuer->note			='';
				$projektbetreuer->betreuerart		='b';  //b=Bachelorarbeitsbetreuer
				$projektbetreuer->faktor			='1,0';
				$projektbetreuer->name			='';
				$projektbetreuer->punkte			='';
				$projektbetreuer->stunden			=$row->betreuerstunden;
				$projektbetreuer->stundensatz		=$row->betreuerstundenhonorar;
				//$projektbetreuer->updateamum		=$row->;
				$projektbetreuer->updatevon		="SYNC";
				//$projektbetreuer->insertamum		=$row->creationdate;
				$projektbetreuer->insertvon		="SYNC";
				$projektbetreuer->ext_id			=$row->bakkalaureatsarbeit_pk;
				/*$qry="SELECT student_uid FROM public.tbl_student WHERE ext_id='".$row->student_fk."';";
				if($resultu = pg_query($conn, $qry))
				{
					if($rowu=pg_fetch_object($resultu))
					{ 
						$projektarbeit->student_uid=$rowu->student_uid;*/
				$qry2="SELECT projektarbeit_id, ext_id FROM lehre.tbl_projektarbeit WHERE projekttyp_kurzbz='Bachelorarbeit' AND ext_id='".$row->bakkalaureatsarbeit_pk."';";
				if($result2 = pg_query($conn, $qry2))
				{
					if(pg_num_rows($result2)>0) //eintrag gefunden
					{
						if($row2=pg_fetch_object($result2))
						{ 
							// update, wenn datensatz bereits vorhanden
							$projektarbeit->new=false;
							$projektarbeit->projektarbeit_id=$row2->projektarbeit_id;
						}
					}
					else 
					{
						// insert, wenn datensatz noch nicht vorhanden
						$projektarbeit->new=true;	
					}
				}
					/*}
				}*/
						
				//le anlegen
				$qry2="SELECT lehreinheit_id FROM lehre.tbl_lehreinheit WHERE lehrform_kurzbz	='BE' AND ext_id='".$row->bakkalaureatsarbeit_pk."';";
				if($result2 = pg_query($conn, $qry2))
				{
					if(pg_num_rows($result2)>0) //eintrag gefunden
					{
						if($row2=pg_fetch_object($result2))
						{ 
							// update, wenn datensatz bereits vorhanden
							$lehreinheit->new=false;
							$lehreinheit->lehreinheit_id=$row2->lehreinheit_id;
						}
					}
					else 
					{
						// insert, wenn datensatz noch nicht vorhanden
						$lehreinheit->new=true;	
					}
					
				}
				if(!$error)
				{
					if(!$lehreinheit->save())
					{
						$error_log.=$lehreinheit->errormsg."\n";
						$anzahl_fehler++;
					}
					else 
					{
						if($lehreinheit->new=true)
						{
							$qry = "SELECT currval('lehre.tbl_lehreinheit_lehreinheit_id_seq') AS id;";
							if($rowu=pg_fetch_object(pg_query($conn,$qry)))
								$projektarbeit->lehreinheit_id=$rowu->id;
							else
							{					
								$error=true;
								$error_log.="Lehreinheit-Sequence konnte nicht ausgelesen werden.\n";
							}
							$ausgabe.="Lehreinheit angelegt: Lehrveranstaltung='".$lehreinheit->lehrveranstaltung_id."', Studiensemester='".$lehreinheit->studiensemester_kz."' und Lehrfach='".$lehreinheit->lehrfach_id."'.\n";
						}
						else 
						{
							$ausgabe.="Lehreinheit aktualisiert: Lehrveranstaltung='".$lehreinheit->lehrveranstaltung_id."', Studiensemester='".$lehreinheit->studiensemester_kz."' und Lehrfach='".$lehreinheit->lehrfach_id."'.\n";
						}
					}
					
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
					
								
					//betreuer und begutachter
					echo nl2br("projektarbeit_id='".$projektarbeit->projektarbeit_id."' AND person_id='".$projektbetreuer->person_id."';");
					$qry2="SELECT person_id FROM lehre.tbl_projektbetreuer WHERE projektarbeit_id='".$projektarbeit->projektarbeit_id."' AND person_id='".$projektbetreuer->person_id."';";
					if($resultu = pg_query($conn, $qry2))
					{
						if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
						{
							if($rowu=pg_fetch_object($resultu))
							{
								$projektbetreuer->person_id=$rowu->person_id;
								$projektbetreuer->new=false;		
							}
							else $projektbetreuer->new=true;
						}
						else $projektbetreuer->new=true;
					}
					else
					{
						$error=true;
						$error_log.='Fehler beim Zugriff auf Tabelle tbl_projektbetreuer bei betreuer_fk: '.$row->betreuer_fk."\n";	
					}
					if(!$error)
					{
						if(!$projektbetreuer->save())
						{
							$error_log.=$projektbetreuer->errormsg."\n";
							$anzahl_fehler++;
						}
						else 
						{
							if($projektbetreuer->new=true)
							{
								$ausgabe.="Projektbetreuer angelegt: Person-ID='".$projektbetreuer->person_id."' und Projektarbeit='".$projektbetreuer->projektarbeit_id."'.\n";
							}
							else 
							{
								$ausgabe.="Projektbetreuer aktualisiert: Person-ID='".$projektbetreuer->person_id."' und Projektarbeit='".$projektbetreuer->projektarbeit_id."'.\n";
							}
						}
						
					
						//begutachter
						$qry="SELECT person_portal FROM sync.tbl_syncperson WHERE person_fas='$row->begutachter_fk'";  //begutachter_fk -> person_id
						if($resultu = pg_query($conn, $qry))
						{
							if($rowu=pg_fetch_object($resultu))
							{ 
								$projektbetreuer->person_id=$rowu->person_portal;	
							}
							else{
								$error=true;
								$error_log.="Begutachter mit person_fk: $row->betreuer_fk konnte in syncperson nicht gefunden werden.\n";
							}
						}
						
						$projektbetreuer				=new projektbetreuer($conn);
						//$projektbetreuer->person_id		='';
						$projektbetreuer->projektarbeit_id		=$projektarbeit->projektarbeit_id;
						$projektbetreuer->note			=$row->note;
						$projektbetreuer->betreuerart		='g';  //g=Bachelorarbeitsbegutachter
						$projektbetreuer->faktor			='1,0';
						$projektbetreuer->name			='';
						$projektbetreuer->punkte			=$row->punkte;
						$projektbetreuer->stunden			='';
						$projektbetreuer->stundensatz		='';
						//$projektbetreuer->updateamum		=$row->;
						$projektbetreuer->updatevon		="SYNC";
						//$projektbetreuer->insertamum		=$row->;
						$projektbetreuer->insertvon		="SYNC";
						$projektbetreuer->ext_id			=$row->bakkalaureatsarbeit_pk;
						
						$qry2="SELECT person_id FROM lehre.tbl_projektbetreuer WHERE projektarbeit_id='".$projektarbeit->projektarbeit_id."' AND person_id='".$projektbetreuer->person_id."';";
						if($resultu = pg_query($conn, $qry2))
						{
							if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
							{
								if($rowu=pg_fetch_object($resultu))
								{
									$projektbetreuer->person_id=$rowu->person_id;
									$projektbetreuer->new=false;		
								}
								else $projektbetreuer->new=true;
							}
							else $projektbetreuer->new=true;
						}
						else
						{
							$error=true;
							$error_log.='Fehler beim Zugriff auf Tabelle tbl_projektbetreuer bei begutachter_fk: '.$row->begutachter_fk."\n";	
						}
						if(!$error)
						{
							if(!$projektbetreuer->save())
							{
								$error_log.=$projektbetreuer->errormsg."\n";
								$anzahl_fehler++;
							}
							else 
							{
								if($projektbetreuer->new=true)
								{
									$ausgabe.="Projektbetreuer angelegt: Person-ID='".$projektbetreuer->person_id."' und Projektarbeit='".$projektbetreuer->projektarbeit_id."' (Begutachter).\n";
								}
								else 
								{
									$ausgabe.="Projektbetreuer aktualisiert: Person-ID='".$projektbetreuer->person_id."' und Projektarbeit='".$projektbetreuer->projektarbeit_id."' (Begutachter).\n";
								}
							}
							
						}	
						
						
						if(!$error)
						{
							
						}
						else 
						{
							$anzahl_fehler++;
							pg_query($conn_fas, "ROLLBACK");
						}
					}
					else 
					{
						$anzahl_fehler++;
						pg_query($conn_fas, "ROLLBACK");
					}
				}
				else 
				{
					$anzahl_fehler++;
					pg_query($conn_fas, "ROLLBACK");
				}
			}
			else 
			{
				$anzahl_fehler++;
				pg_query($conn_fas, "ROLLBACK");
			}
		}
		else 
		{
			$anzahl_fehler++;
			pg_query($conn_fas, "ROLLBACK");
		}
		flush();
		$error_log_fas.=$error_log;	
		$error_log='';
		$ausgabe_all.=$ausgabe;
		$ausgabe='';
	}	
}


//echo nl2br($text);
echo nl2br("\nGesamt: $anzahl_quelle / Eingef�gt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");
echo nl2br("\n\n-------------------\n".$ausgabe_all);
echo nl2br("\n\n-------------------\n".$error_log_fas);
?>
</body>
</html>