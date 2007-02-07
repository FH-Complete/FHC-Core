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
require_once('../../../include/projektarbeit.class.php');
require_once('../../../include/projektbetreuer.class.php');


$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_fehler=0;
$anzahl_quelle2=0;
$anzahl_eingefuegt2=0;
$anzahl_fehler2=0;
$fachbereich_kurzbz='';
$person_id1='';
$person_id2='';
$person_idb='';

function validate($row)
{
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
$qry = "SELECT * FROM diplomarbeit;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Bachelorarbeit Sync\n------------------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$projektarbeit				=new projektarbeit($conn);
		$projektarbeit->projekttyp_kurzbz	='Diplomarbeit';
		$projektarbeit->titel			=$row->diplomarbeitsthema;
		//$projektarbeit->lehreinheit_id	='';
		//$projektarbeit->student_uid	='';
		$projektarbeit->firma_id		='';
		$projektarbeit->note			=$row->diplomarbeitgesamtnote;
		$projektarbeit->punkte		='';
		$projektarbeit->beginn		='';
		$projektarbeit->ende		=$row->diplomarbeitsdatum;
		$projektarbeit->faktor		='1.0';
		$projektarbeit->freigegeben	=$row->freigegeben;
		$projektarbeit->gesperrtbis		=$row->gesperrtbis;
		$projektarbeit->stundensatz	=$row->kosten;
		$projektarbeit->gesamtstunden	=$row->betreuungsstunden;
		$projektarbeit->themenbereich	='';
		$projektarbeit->anmerkung		='';		
		//$projektarbeit->updateamum	=$row->;
		$projektarbeit->updatevon		="SYNC";
		//$projektarbeit->insertamum	=$row->;
		$projektarbeit->insertvon		="SYNC";
		$projektarbeit->ext_id		=$row->diplomarbeit_pk;
		
		//student_id ermitteln
		$qry="SELECT student_uid FROM public.student WHERE ext_id='$row->student_fk';";
		if($resulto = pg_query($conn, $qry))
		{
			if($rowo=pg_fetch_object($resulto))
			{ 
				$projektarbeit->student_uid=$rowo->student_uid;
			}
			else {
				$error=true;
				$error_log.="Student mit student_fk: $row->student_fk konnte nicht gefunden werden.\n";
			}
		}
		
		//betreuer
		
		$qry="Select nachname, vorname, person_id from tbl_person join tbl_benutzer using (person_id) join tbl_mitarbeiter on tbl_benutzer.uid=mitarbeiter_uid;";
		if($resultb1 = pg_query($conn_fas, $qry))
		{
			while ($rowb1=pg_fetch_object($resultb1))
			{ 
				if (strstr($rowb1->nachname, $row->erstbegutachter.' '))
				{
					$person_id1=$rowb1->person_id;	
				}
				if (strstr($rowb1->nachname, $row->zweitbegutachter.' '))
				{
					$person_id2=$rowb1->person_id;	
				}
				if (strstr($rowb1->nachname, $row->betreuer.' '))
				{
					$person_idb=$rowb1->person_id;	
				}
			}
		}
		if ($person_id1!='')
		{
			$projektbetreuer				=new projektbetreuer($conn);
			$projektbetreuer->person_id		=$person_id1;
			$projektbetreuer->projektarbeit_id		=$projektarbeit->projektarbeit_id;
			$projektbetreuer->note			=$row->noteerstbegutachter;
			$projektbetreuer->betreuerart		='g';  //g=Diplomarbeitsbegutachter
			$projektbetreuer->faktor			='1,0';
			$projektbetreuer->name			='';
			$projektbetreuer->punkte			=$row->punkteerstbegutachter;
			$projektbetreuer->stunden			='';
			$projektbetreuer->stundensatz		='';
			//$projektbetreuer->updateamum		=$row->;
			$projektbetreuer->updatevon		="SYNC";
			//$projektbetreuer->insertamum		=$row->;
			$projektbetreuer->insertvon		="SYNC";
			$projektbetreuer->ext_id			=$row->diplomarbeit_pk;
			$qry="SELECT uid FROM student WHERE student_pk=".$row->student_fk.";";
			if($resultu = pg_query($conn_fas, $qry))
			{
				if($rowu=pg_fetch_object($resultu))
				{ 
					$projektarbeit->student_uid=$rowu->uid;
					$qry2="SELECT projektarbeit_id, ext_id FROM lehre.tbl_projektarbeit WHERE projekttyp_kurzbz='Diplomarbeit' AND ext_id='".$row->diplomarbeit_pk."';";
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
				}
			}
							
			$qry2="SELECT person_id FROM lehre.projektbetreuer WHERE projektarbeit_id='".$projektarbeit->projektarbeit_id."' AND person_id='".$projektbetreuer->person_id."';";
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
				$error_log.='Fehler beim Zugriff auf Tabelle tbl_projektbetreuer bei betreuer_fk: '.$projektbetreuer->person_id;	
			}
			if(!$error)
			{
				if(!$projektbetreuer->save())
				{
					$error_log.=$projektbetreuer->errormsg."\n";
					$anzahl_fehler++;
				}
				
			}
		}
		if ($person_id2!='')
		{
			$projektbetreuer				=new projektbetreuer($conn);
			$projektbetreuer->person_id		=$person_id2;
			$projektbetreuer->projektarbeit_id		=$projektarbeit->projektarbeit_id;
			$projektbetreuer->note			=$row->notezweitbegutachter;
			$projektbetreuer->betreuerart		='g';  //d=Diplomarbeitsbegutachter
			$projektbetreuer->faktor			='1,0';
			$projektbetreuer->name			='';
			$projektbetreuer->punkte			=$row->punktezweitbegutachter;
			$projektbetreuer->stunden			='';
			$projektbetreuer->stundensatz		='';
			//$projektbetreuer->updateamum		=$row->;
			$projektbetreuer->updatevon		="SYNC";
			//$projektbetreuer->insertamum		=$row->;
			$projektbetreuer->insertvon		="SYNC";
			$projektbetreuer->ext_id			=$row->diplomarbeit_pk;
			$qry="SELECT uid FROM student WHERE student_pk=".$row->student_fk.";";
			if($resultu = pg_query($conn_fas, $qry))
			{
				if($rowu=pg_fetch_object($resultu))
				{ 
					$projektarbeit->student_uid=$rowu->uid;
					$qry2="SELECT projektarbeit_id, ext_id FROM lehre.tbl_projektarbeit WHERE projekttyp_kurzbz	='Diplomarbeit' AND ext_id='".$row->diplomarbeit_pk."';";
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
				}
			}
							
			$qry2="SELECT person_id FROM lehre.projektbetreuer WHERE projektarbeit_id='".$projektarbeit->projektarbeit_id."' AND person_id='".$projektbetreuer->person_id."';";
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
				$error_log.='Fehler beim Zugriff auf Tabelle tbl_projektbetreuer bei betreuer_fk: '.$projektbetreuer->person_id;	
			}
			if(!$error)
			{
				if(!$projektbetreuer->save())
				{
					$error_log.=$projektbetreuer->errormsg."\n";
					$anzahl_fehler++;
				}
				
			}
		}		
		if ($person_idb!='')
		{
			$projektbetreuer				=new projektbetreuer($conn);
			$projektbetreuer->person_id		=$person_idb;
			$projektbetreuer->projektarbeit_id		=$projektarbeit->projektarbeit_id;
			$projektbetreuer->note			='';
			$projektbetreuer->betreuerart		='d';  //d=Diplomarbeitsbetreuer
			$projektbetreuer->faktor			='1,0';
			$projektbetreuer->name			='';
			$projektbetreuer->punkte			='';
			$projektbetreuer->stunden			=$row->betreuungsstunden;
			$projektbetreuer->stundensatz		=$row->kosten;
			//$projektbetreuer->updateamum		=$row->;
			$projektbetreuer->updatevon		="SYNC";
			//$projektbetreuer->insertamum		=$row->;
			$projektbetreuer->insertvon		="SYNC";
			$projektbetreuer->ext_id			=$row->diplomarbeit_pk;
			$qry="SELECT uid FROM student WHERE student_pk=".$row->student_fk.";";
			if($resultu = pg_query($conn_fas, $qry))
			{
				if($rowu=pg_fetch_object($resultu))
				{ 
					$projektarbeit->student_uid=$rowu->uid;
					$qry2="SELECT projektarbeit_id, ext_id FROM lehre.tbl_projektarbeit WHERE projekttyp_kurzbz='Diplomarbeit' AND ext_id='".$row->diplomarbeit_pk."';";
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
				}
			}
							
			$qry2="SELECT person_id FROM lehre.projektbetreuer WHERE projektarbeit_id='".$projektarbeit->projektarbeit_id."' AND person_id='".$projektbetreuer->person_id."';";
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
				$error_log.='Fehler beim Zugriff auf Tabelle tbl_projektbetreuer bei betreuer_fk: '.$projektbetreuer->person_id;	
			}
			if(!$error)
			{
				if(!$projektbetreuer->save())
				{
					$error_log.=$projektbetreuer->errormsg."\n";
					$anzahl_fehler++;
				}
				
			}
		}
		flush();	
	}	
}


//echo nl2br($text);
echo nl2br($error_log);
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");

?>
</body>
</html>