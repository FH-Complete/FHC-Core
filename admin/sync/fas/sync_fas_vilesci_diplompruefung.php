<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Diplomprüfungsdatensätze von FAS DB in PORTAL DB
//*
//*

require_once('../../../vilesci/config.inc.php');


$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_fehler=0;
$anzahl_eingefuegt=0;
$anzahl_geaendert=0;

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
<title>Synchro - FAS -> Portal - Diplomprüfung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
//nation
$qry = "SELECT * FROM diplomarbeit WHERE mitarbeiter_fk IS NOT NULL AND pruefungsdatum IS NOT NULL AND mitarbeiter_fk>0;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Diplomprüfung Sync\n------------------------\n");
	echo nl2br("Diplomprüfungssynchro Beginn: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		//pg_query($conn, "BEGIN");
		$error=false;
		//$error_log='';
		//$abschlusspruefung_id		='';  //serial
		//$student_uid			='';
		$vorsitz				=$row->vilesci_vorsitzender;
		$pruefer1				=$row->vilesci_pruefer;
		$pruefer2				=$row->vilesci_pruefer1;
		//$pruefer3				='';//kein dritter Prüfer bei Diplomarbeiten
		//$abschlussbeurteilung_kurzbz	='';
		//$akadgrad_id			='';
		$datum				=$row->pruefungsdatum;
		$sponsion				=$row->diplomarbeitsdatum;
		$pruefungstyp_kurzbz		='Diplom';
		$anmerkung				=$row->pruefungsprotokoll;
		//$updateamum			='';
		$updatevon				='SYNC';
		$insertamum				=$row->creationdate;
		//$insertvon				='';
		$ext_id				=$row->diplomarbeit_pk;
				
		//insertvon ermitteln
		$qrycu="SELECT name FROM public.benutzer WHERE benutzer_pk='".$row->creationuser."';";
		if($resultcu = pg_query($conn_fas, $qrycu))
		{
			if($rowcu=pg_fetch_object($resultcu))
			{
				$insertvon=$rowcu->name;
			}
		}
		//student_id ermitteln
		$qry="SELECT student_uid, studiengang_kz FROM public.tbl_student WHERE ext_id='".$row->student_fk."';";
		if($resulto=pg_query($conn, $qry))
		{
			if($rowo=pg_fetch_object($resulto))
			{ 
				$student_uid=$rowo->student_uid;
				$studiengang_kz=$rowo->studiengang_kz;
			}
			else 
			{
				$error=true;
				$error_log.="Student mit student_fk: $row->student_fk konnte nicht gefunden werden.\n";
			}
		}
		//beurteilung ermitteln
		If($row->gesamtnote=='10')
		{
			$abschlussbeurteilung_kurzbz='ausgezeichnet';
		}
		elseif($row->gesamtnote=='20')
		{
			$abschlussbeurteilung_kurzbz='gut';
		}
		elseif($row->gesamtnote=='30')
		{
			$abschlussbeurteilung_kurzbz='bestanden';
		}
		else
		{
			$abschlussbeurteilung_kurzbz=NULL;
		}
		//geschlecht ermitteln
		$qry="SELECT geschlecht from person,student WHERE student_pk='".$row->student_fk."' AND student.person_fk=person.person_pk;";
		if($resulto=pg_query($conn_fas, $qry))
		{
			if($rowo=pg_fetch_object($resulto))
			{ 
				$geschlecht=strtolower($rowo->geschlecht);
			}
			else 
			{
				$error=true;
				$error_log.="Person mit student_fk: $row->student_fk konnte nicht gefunden werden.\n";
			}
		}
		//akadgrad ermitteln
		if($studiengang_kz<=222)
		{
			$qry="SELECT * FROM lehre.tbl_akadgrad WHERE studiengang_kz='".$studiengang_kz."' AND geschlecht='".$geschlecht."';";
		}
		else 
		{
			$qry="SELECT * FROM lehre.tbl_akadgrad WHERE studiengang_kz='".$studiengang_kz."';";
		}	
		if($resulto=pg_query($conn, $qry))
		{
			if($rowo=pg_fetch_object($resulto))
			{ 
				$akadgrad_id=$rowo->akadgrad_id;
			}
		}
			
		//insert oder update?
		if(!$error)
		{
			$qry="SELECT * FROM lehre.tbl_abschlusspruefung WHERE student_uid='".$student_uid."' AND pruefungstyp_kurzbz='Diplom' AND ext_id='".$row->diplomarbeit_pk."';";
			if($resulto=pg_query($conn, $qry))
			{
				if($rowo=pg_fetch_object($resulto))
				{
					$update=false;			
					if($rowo->vorsitz!=$vorsitz) 
					{
						$update=true;
						if(strlen(trim($ausgabe1))>0)
						{
							$ausgabe1.=", Vorsitz: '".$vorsitz."' (statt '".$rowo->vorsitz."')";
						}
						else
						{
							$ausgabe1="Vorsitz: '".$vorsitz."' (statt '".$rowo->vorsitz."')";
						}
					}
					if($rowo->pruefer1!=$pruefer1) 
					{
						$update=true;
						if(strlen(trim($ausgabe1))>0)
						{
							$ausgabe1.=", Prüfer1: '".$pruefer1."' (statt '".$rowo->pruefer1."')";
						}
						else
						{
							$ausgabe1="Prüfer1: '".$pruefer1."' (statt '".$rowo->pruefer1."')";
						}
					}
					if($rowo->pruefer2!=$pruefer2) 
					{
						$update=true;
						if(strlen(trim($ausgabe1))>0)
						{
							$ausgabe1.=", Prüfer2: '".$pruefer2."' (statt '".$rowo->pruefer2."')";
						}
						else
						{
							$ausgabe1="Prüfer2: '".$pruefer2."' (statt '".$rowo->pruefer2."')";
						}
					}
					if($rowo->abschlussbeurteilung_kurzbz!=$abschlussbeurteilung_kurzbz) 
					{
						$update=true;
						if(strlen(trim($ausgabe1))>0)
						{
							$ausgabe1.=", Abschlussbeurteilung: '".$abschlussbeurteilung_kurzbz."' (statt '".$rowo->abschlussbeurteilung_kurzbz."')";
						}
						else
						{
							$ausgabe1="Abschlussbeurteilung: '".$abschlussbeurteilung_kurzbz."' (statt '".$rowo->abschlussbeurteilung_kurzbz."')";
						}
					}
					if($rowo->akadgrad_id!=$akadgrad_id) 
					{
						$update=true;
						if(strlen(trim($ausgabe1))>0)
						{
							$ausgabe1.=", akad.Grad: '".$akadgrad_id."' (statt '".$rowo->akadgrad_id."')";
						}
						else
						{
							$ausgabe1="Akad.Grad: '".$akadgrad_id."' (statt '".$rowo->akadgrad_id."')";
						}
					}
					if($rowo->datum!=$datum) 
					{
						$update=true;
						if(strlen(trim($ausgabe1))>0)
						{
							$ausgabe1.=", Prüfungsdatum: '".$datum."' (statt '".$rowo->datum."')";
						}
						else
						{
							$ausgabe1="Prüfungsdatum: '".$datum."' (statt '".$rowo->datum."')";
						}
					}
					if($rowo->sponsion!=$sponsion) 
					{
						$update=true;
						if(strlen(trim($ausgabe1))>0)
						{
							$ausgabe1.=", Sponsionsdatum: '".$sponsion."' (statt '".$rowo->sponsion."')";
						}
						else
						{
							$ausgabe1="Sponsionsdatum: '".$sponsion."' (statt '".$rowo->sponsion."')";
						}
					}
					if($rowo->pruefungstyp_kurzbz!=$pruefungstyp_kurzbz) 
					{
						$update=true;
						if(strlen(trim($ausgabe1))>0)
						{
							$ausgabe1.=", Prüfungstyp: '".$pruefungstyp_kurzbz."' (statt '".$rowo->pruefungstyp_kurzbz."')";
						}
						else
						{
							$ausgabe1="Prüfungstyp: '".$pruefungstyp_kurzbz."' (statt '".$rowo->pruefungstyp_kurzbz."')";
						}
					}
					if($rowo->anmerkung!=$anmerkung) 
					{
						$update=true;
						if(strlen(trim($ausgabe1))>0)
						{
							$ausgabe1.=", Anmerkung: '".$anmerkung."' (statt '".$rowo->anmerkung."')";
						}
						else
						{
							$ausgabe1="Anmerkung: '".$anmerkung."' (statt '".$rowo->anmerkung."')";
						}
					}
					if($rowo->insertvon!=$insertvon) 
					{
						$update=true;
						if(strlen(trim($ausgabe1))>0)
						{
							$ausgabe1.=", Insertvon: '".$insertvon."' (statt '".$rowo->insertvon."')";
						}
						else
						{
							$ausgabe1="Insertvon: '".$insertvon."' (statt '".$rowo->insertvon."')";
						}
					}
					if(date("d.m.Y", $rowo->insertamum)!=date("d.m.Y", $insertamum)) 
					{
						$update=true;
						if(strlen(trim($ausgabe1))>0)
						{
							$ausgabe1.=", Insertamum: '".$insertamum."' (statt '".$rowo->insertamum."')";
						}
						else
						{
							$ausgabe1="Insertamum: '".$insertamum."' (statt '".$rowo->insertamum."')";
						}
					}
					if($update)
					{
						$qry="UPDATE lehre.tbl_abschlusspruefung SET ".
							"abschlusspruefung_id=".myaddslashes($rowo->abschlusspruefung_id).", ".
							"student_uid=".myaddslashes($student_uid).", ".
							"vorsitz=".myaddslashes($vorsitz).", ".
							"pruefer1=".myaddslashes($pruefer1).", ".
							"pruefer2=".myaddslashes($pruefer2).", ".
							"abschlussbeurteilung_kurzbz=".myaddslashes($abschlussbeurteilung_kurzbz).", ".
							"akadgrad_id=".myaddslashes($akadgrad_id).", ".
							"datum=".myaddslashes($datum).", ".
							"sponsion=".myaddslashes($sponsion).", ".
							"pruefungstyp_kurzbz=".myaddslashes($pruefungstyp_kurzbz).", ".
							"anmerkung=".myaddslashes($anmerkung).", ".
							"insertvon=".myaddslashes($insertvon).", ".
							"insertamum=".myaddslashes($insertamum).", ".
							"updatevon='SYNC', ".
							"updateamum= now(), ".
							"ext_id=".myaddslashes($ext_id).
							";";
							$ausgabe.="Abschlussprüfung von Student mit UID '".$student_uid."' geändert: ".$ausgabe1."\n;";
							$anzahl_geaendert++;
					}
					else 
					{
						$qry="SELECT * FROM lehre.tbl_abschlusspruefung;";
					}
				}
				else 
				{
					$qry="INSERT INTO lehre.tbl_abschlusspruefung (student_uid, vorsitz, pruefer1, pruefer2, ".
						"abschlussbeurteilung_kurzbz, akadgrad_id, datum, sponsion, pruefungstyp_kurzbz, anmerkung, ".
						"insertvon, insertamum, updatevon, updateamum, ext_id) VALUES (".
						myaddslashes($student_uid).", ".
						myaddslashes($vorsitz).", ".
						myaddslashes($pruefer1).", ".
						myaddslashes($pruefer2).", ".
						myaddslashes($abschlussbeurteilung_kurzbz).", ".
						myaddslashes($akadgrad_id).", ".
						myaddslashes($datum).", ".
						myaddslashes($sponsion).", ".
						myaddslashes($pruefungstyp_kurzbz).", ".
						myaddslashes($anmerkung).", ".
						myaddslashes($insertvon).", ".
						myaddslashes($insertamum).", ".
						"'SYNC', ".
						"now(), ".
						myaddslashes($ext_id).
						");";
						$ausgabe.="Abschlussprüfung von Student mit UID '".$student_uid."' am '".$datum."' eingetragen.\n";
						$anzahl_eingefuegt++;
				}
			}
			else 
			{
				$error_log.= "*****\nFehler beim Zugriff auf tbl_abschlusspruefung!\n";
				$anzahl_fehler++;
			}
			if(!pg_query($conn,$qry))
			{	
				$anzahl_fehler++;		
				$error_log.='Fehler beim Speichern des Diplomprüfung-Datensatzes von Student:'.$student_uid." \n".$qry."\n";
				$ausgabe1='';
			}
		}
		else 
		{
			$anzahl_fehler++;
		}
		
		
		
		
	}
//echo und mail
echo nl2br("Diplomprüfungssynchro Ende: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");

$error_log_fas="Sync Diplomprüfung\n------------------------\n\n".$error_log;
echo nl2br("Allgemeine Fehler: ".$anzahl_fehler."\nAnzahl Diplomprüfungen: ".$anzahl_quelle." / Eingefügt: ".$anzahl_eingefügt." / Geändert: ".$anzahl_geaendert.".\n\n");


echo nl2br($error_log_fas."\n--------------------------------------------------------------------------------------------------------------------------------\n");
echo nl2br($ausgabe_all);

mail($adress, 'SYNC Diplomprüfung von '.$_SERVER['HTTP_HOST'], 
"Allgemeine Fehler: ".$anzahl_fehler.", Anzahl Diplomprüfungen: ".$anzahl_quelle.".\n".
$ausgabe_all,"From: vilesci@technikum-wien.at");

mail($adress, 'SYNC-Fehler Diplomprüfung  von '.$_SERVER['HTTP_HOST'], $error_log_fas, "From: vilesci@technikum-wien.at");
}
?>
</body>
</html>