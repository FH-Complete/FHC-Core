<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Kontodatensaetze von FAS DB in PORTAL DB
//*
//*

require_once('../../../vilesci/config.inc.php');
require_once('../../../include/konto.class.php');
require_once('../../../include/functions.inc.php');
require_once('../sync_config.inc.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

//$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_vorhanden=0;
$anzahl_fehler=0;
$ausgabe='';
$ausgabe_adresse='';
$update=false;


/*************************
 * FAS-PORTAL - Synchronisation
 */
?>

<html>
<head>
<title>Synchro - FAS -> Vilesci - Konto</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
//konto

//studiensemester
$qry="SELECT studiensemester_kurzbz, ext_id FROM public.tbl_studiensemester;";
if($result = pg_query($conn, $qry))
{
	while($row=pg_fetch_object($result))
	{ 
		$studiensemester[$row->ext_id]=$row->studiensemester_kurzbz;
	}
}
$qry="SELECT studiengang_kz, ext_id FROM public.tbl_studiengang WHERE ext_id IS NOT NULL;";
if($result = pg_query($conn, $qry))
{
	while($row=pg_fetch_object($result))
	{ 
		$studiengang[$row->ext_id]=$row->studiengang_kz;
	}
}


$qry="SELECT *, student_zahlung.creationuser as cuser, student_zahlung.creationdate as cdate 
	FROM student_zahlung, zahlung WHERE student_zahlung.zahlung_fk=zahlung.zahlung_pk ;";
if($result = pg_query($conn_fas, $qry))
{
	echo "Konto Sync<br>--------------<br>";
	echo "Beginn: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."<br><br>";
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$konto					=new konto($conn);
		//$konto->person_id			='';
		//$konto->studiengang_kz		='';
		$konto->studiensemester_kurzbz	=getStudiensemesterFromDatum($conn, $row->bezahltam, true);
		//$konto->buchungsnr_verweis	='';
		$betrag				=$row->betrag;
		$konto->buchungsdatum		=$row->bezahltam;
		$konto->buchungstext		=$row->name;
		$konto->buchungstyp_kurzbz	='Sonstiges';
		$konto->mahnspanne		='30';
		$konto->updateamum		=date("Y-m-d H:i:s");
		$konto->updatevon			="SYNC";
		$konto->insertamum			=$row->cdate;
		$konto->insertvon			=$row->cuser;
		$konto->ext_id			='';
		
		$konto->new=false;
		
		//ext_id "basteln": soll aus student_fk und dreistelliger zahlung_fk bestehen
		if(strlen(trim($row->zahlung_fk))==1)
		{
			$konto->ext_id=trim($row->student_fk)."00".trim($row->zahlung_fk);
		}
		elseif(strlen(trim($row->zahlung_fk))==2)
		{
			$konto->ext_id=trim($row->student_fk)."0".trim($row->zahlung_fk);
		}
		elseif(strlen(trim($row->zahlung_fk))==3)
		{
			$konto->ext_id=trim($row->student_fk).trim($row->zahlung_fk);
		}
		$qry="SELECT * FROM public.tbl_konto WHERE ext_id='".$konto->ext_id."';";
		if($result3 = pg_query($conn, $qry))
		{
			if(pg_num_rows($result3)>0) //kontobewegung bereits eingetragen
			{
				//eintrag bereits vorhanden
				$konto->new=false;
				//$ausgabe.="Eintrag bereits vorhanden! ('".$row->name."' - '".$row->student_fk."')\n";
				//echo "Eintrag bereits vorhanden! ('".$row->name."' - '".$row->student_fk."')<br>";
				$anzahl_vorhanden++;
				continue;
			}
		}
		
		
		//insertvon ermitteln
		$qrycu="SELECT name FROM benutzer WHERE benutzer_pk='".$row->creationuser."';";
		if($resultcu = pg_query($conn_fas, $qrycu))
		{
			if($rowcu=pg_fetch_object($resultcu))
			{
				$konto->insertvon=$rowcu->name;
			}
		}
		//person_id herausfinden
		$qry="SELECT person_fk, studiengang_fk FROM student WHERE student_pk='".$row->student_fk."';";
		if($result2 = pg_query($conn_fas, $qry))
		{
			if(pg_num_rows($result2)>0) //eintrag gefunden
			{
				if($row2=pg_fetch_object($result2))
				{
					$konto->studiengang_kz=$studiengang[$row2->studiengang_fk];
				}
			}
			else 
			{
				//person nicht gefunden
				$anzahl_fehler++;
				$error_log.="Person mit student_fk '".$row->student_fk."' nicht in Tabelle 'student' gefunden.\n";
				echo "Person mit student_fk '".$row->student_fk."' nicht in Tabelle 'student' gefunden.<br>";
				continue;
			}
		}
		if(in_array($konto->studiengang_kz, $dont_sync_php))
		{
			//bestimmte Stg auslassen!
			continue;
		}
		$qry1="SELECT person_portal FROM sync.tbl_syncperson WHERE person_fas=".$row2->person_fk.";";
		if($result1 = pg_query($conn, $qry1))
		{
			if(pg_num_rows($result1)>0) //person gefunden
			{
				if($row1=pg_fetch_object($result1))
				{ 
					$konto->person_id=$row1->person_portal;
					$konto->new=true; //vorhandene Einträge wurden bereits übersprungen
				}
				else 
				{
					$error_log.="Person mit person_pk '".$row2->person_fk."' in tbl_syncperson nicht gefunden.\n";
					echo "Person mit person_pk '".$row2->person_fk."' in tbl_syncperson nicht gefunden.<br>";
					$anzahl_fehler++;
					$error=true;
					continue;
				}
			}
			else 
			{
				$error_log.="Person mit person_pk '".$row2->person_fk."' in tbl_syncperson nicht gefunden!\n";
				echo "Person mit person_pk '".$row2->person_fk."' in tbl_syncperson nicht gefunden!<br>";
				$anzahl_fehler++;
				$error=true;
				continue;
			}
		}	
		else 
		{
			$error=true;
			$error_log.="Fehler beim Zugriff auf tbl_syncperson bei Person mit person_pk '".$row2->person_fk."'!\n";
			echo "Fehler beim Zugriff auf tbl_syncperson bei Person mit person_pk '".$row2->person_fk."'!<br>";
			$anzahl_fehler++;
		}	
		if(!$error)
		{
			if($konto->new)
			{
				if($row->art==1)
				{
					//Buchung1 - Belastung
					$konto->buchungsnr_verweis=null;
					$konto->betrag='-'.$betrag;
					if(!$konto->save())
					{
						$error_log.=$konto->errormsg."\n";
						echo $konto->errormsg."<br>";
						$anzahl_fehler++;
					}
					else 
					{
						//Buchung2 - Zahlung
						$konto->buchungsnr_verweis=$konto->buchungsnr;
						$konto->buchungsnr='';
						$konto->betrag=$betrag;
						if(!$konto->save())
						{
							$error_log.=$konto->errormsg."\n";
							echo $konto->errormsg."<br>";
							$anzahl_fehler++;
						}
						else
						{
							$ausgabe.="Buchung (e) von Person '".$konto->person_id."' mit Zahlung '".$row->zahlung_fk."', Text '".$konto->buchungstext."' und Betrag €".$konto->betrag." eingefügt.\n";
							echo "Buchung (e) von Person '".$konto->person_id."' mit Zahlung '".$row->zahlung_fk."', Text '".$konto->buchungstext."' und Betrag €".$konto->betrag." eingefügt.<br>";
							$anzahl_eingefuegt++;
						}
					}
				}	
				elseif($row->art==2)
				{
					//Buchung1 - Gutschrift
					$konto->betrag=$betrag;
					$konto->buchungsnr_verweis=null;
					if(!$konto->save())
					{
						$error_log.=$konto->errormsg."\n";
						echo $konto->errormsg."<br>";
						$anzahl_fehler++;
					}
					else 
					{
						//Buchung2 - Zahlung
						$konto->buchungsnr_verweis=$konto->buchungsnr;
						$konto->buchungsnr='';
						$konto->betrag='-'.$betrag;
						if(!$konto->save())
						{
							$error_log.=$konto->errormsg."\n";
							echo $konto->errormsg."<br>";
							$anzahl_fehler++;
						}
						else
						{
							$ausgabe.="Buchung (a) von Person '".$konto->person_id."' mit Zahlung '".$row->zahlung_fk."', Text '".$konto->buchungstext."' und Betrag €".$betrag." eingefügt.\n";
							echo "Buchung (a) von Person '".$konto->person_id."' mit Zahlung '".$row->zahlung_fk."', Text '".$konto->buchungstext."' und Betrag €".$betrag." eingefügt.<br>";
							$anzahl_eingefuegt++;
						}
					}
				}
			}
		}	
	}	
}
echo "-------------------------------------------------------------------------------------------------------------------------------<br><br>";
echo "Ende: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."<br><br>";
//echo nl2br($error_log);
echo nl2br("\nZahlungen\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / bereits vorhanden: $anzahl_vorhanden / Fehler: $anzahl_fehler");
$ausgabe="\nZahlungen\nGesamt: ".$anzahl_quelle." / Eingefügt: ".$anzahl_eingefuegt." / bereits vorhanden: ".$anzahl_vorhanden." / Fehler: ". $anzahl_fehler."\n\n".$ausgabe;
if(strlen(trim($error_log))>0)
{
	mail($adress, 'SYNC-Fehler Konto von '.$_SERVER['HTTP_HOST'], $error_log,"From: vilesci@technikum-wien.at");
}
mail($adress, 'SYNC Konto von '.$_SERVER['HTTP_HOST'], $ausgabe,"From: vilesci@technikum-wien.at");
?>
</body>
</html>