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

//$adress='ruhan@technikum-wien.at';
$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$ausgabe='';
$ausgabe1='';
$anzahl_eingefuegt=0;
$anzahl_geaendert=0;
$anzahl_fehler=0;
$anzahl_quelle=0;
$studiengang_kz='';
$abschlussbeurteilung_kurzbz='';

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
<title>Synchro - FAS -> Vilesci - Bachelorprüfung</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<?php
$qry_main = "SELECT * FROM bakkalaureatspruefung;";

if($result = pg_query($conn_fas, $qry_main))
{
	echo nl2br("Bachelorprüfung Sync\n----------------------\n");
	echo nl2br("Bachelorprüfungsynchro Beginn: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		//pg_query($conn, "BEGIN");
		$error=false;
		//$error_log='';
		//$abschlusspruefung_id		='';  //serial
		//$student_uid			='';
		//$vorsitz				='';
		//$pruefer1				='';
		//$pruefer2				='';
		//$pruefer3				='';
		//$abschlussbeurteilung_kurzbz	='';
		//$akadgrad_id			='';
		$datum				=$row->datum;
		$sponsion				=$row->feier;
		$pruefungstyp_kurzbz		='Bachelor';
		$anmerkung				=$row->protokoll;
		//$updateamum			='';
		$updatevon				='SYNC';
		$insertamum				=$row->creationdate;
		//$insertvon				='';
		$ext_id				=$row->bakkalaureatspruefung_pk;
				
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
		//vorsitz ermitteln
		if($row->vorsitz_fk>'-1')
		{
			$qry="SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE ext_id='".$row->vorsitz_fk."';";
			if($resulto=pg_query($conn, $qry))
			{
				if($rowo=pg_fetch_object($resulto))
				{ 
					$vorsitz=$rowo->mitarbeiter_uid;
				}
				else 
				{
					$error=true;
					$error_log.="Vorsitz mit mitarbeiter_fk: $row->vorsitz_fk konnte nicht gefunden werden.\n";
				}
			}
		}
		else 
		{
			$vorsitz=NULL;
		}	
		//prüfer1 ermitteln
		if($row->pruefer1_fk>'-1')
		{
			$qry="SELECT person_id FROM public.tbl_mitarbeiter, public.tbl_benutzer WHERE tbl_mitarbeiter.mitarbeiter_uid=tbl_benutzer.uid AND tbl_mitarbeiter.ext_id='".$row->pruefer1_fk."';";
			if($resulto=pg_query($conn, $qry))
			{
				if($rowo=pg_fetch_object($resulto))
				{ 
					$pruefer1=$rowo->person_id;
				}
				else 
				{
					$error=true;
					$error_log.="Prüfer1 mit mitarbeiter_fk: $row->pruefer1_fk konnte nicht gefunden werden.\n";
				}
			}
		}
		else 
		{
			$pruefer1=NULL;
		}
		//prüfer2 ermitteln
		if($row->pruefer2_fk>'-1')
		{
			$qry="SELECT person_id FROM public.tbl_mitarbeiter, public.tbl_benutzer WHERE tbl_mitarbeiter.mitarbeiter_uid=tbl_benutzer.uid AND tbl_mitarbeiter.ext_id='".$row->pruefer2_fk."';";
			if($resulto=pg_query($conn, $qry))
			{
				if($rowo=pg_fetch_object($resulto))
				{ 
					$pruefer2=$rowo->person_id;
				}
				else 
				{
					$error=true;
					$error_log.="Prüfer2 mit mitarbeiter_fk: $row->pruefer2_fk konnte nicht gefunden werden.\n";
				}
			}
		}
		else 
		{
			$pruefer2=NULL;
		}	
		//prüfer3 ermitteln, wenn an prüfung teilgenommen
		if($row->pruefer3_fk>'-1')
		{
			$qry="SELECT person_id FROM public.tbl_mitarbeiter, public.tbl_benutzer WHERE tbl_mitarbeiter.mitarbeiter_uid=tbl_benutzer.uid AND tbl_mitarbeiter.ext_id='".$row->pruefer3_fk."';";
			if($resulto=pg_query($conn, $qry))
			{
				if($rowo=pg_fetch_object($resulto))
				{ 
					$pruefer3=$rowo->person_id;
				}
				else 
				{
					$error=true;
					$error_log.="Prüfer3 mit mitarbeiter_fk: $row->pruefer3_fk konnte nicht gefunden werden.\n";
				}
			}
		}
		else 
		{
			$pruefer3=NULL;
		}
		//beurteilung ermitteln
		if($row->beurteilung=='0')
		{
			$abschlussbeurteilung_kurzbz=NULL;
		}
		elseif($row->beurteilung=='1')
		{
			$abschlussbeurteilung_kurzbz='ausgezeichnet';
		}
		elseif($row->beurteilung=='2')
		{
			$abschlussbeurteilung_kurzbz='gut';
		}
		elseif($row->beurteilung=='3')
		{
			$abschlussbeurteilung_kurzbz='bestanden';
		}
		elseif($row->beurteilung=='4')
		{
			$abschlussbeurteilung_kurzbz='nicht';
		}
		//akadgrad ermitteln
		$qry="SELECT akadgrad_id FROM lehre.tbl_akadgrad WHERE studiengang_kz='".$studiengang_kz."';";
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
			$qry="SELECT * FROM lehre.tbl_abschlusspruefung WHERE student_uid='".$student_uid."' AND pruefungstyp_kurzbz='Bachelor' AND ext_id='".$row->bakkalaureatspruefung_pk."';";
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
					if($rowo->pruefer3!=$pruefer3) 
					{
						$update=true;
						if(strlen(trim($ausgabe1))>0)
						{
							$ausgabe1.=", Prüfer3: '".$pruefer3."' (statt '".$rowo->pruefer3."')";
						}
						else
						{
							$ausgabe1="Prüfer3: '".$pruefer3."' (statt '".$rowo->pruefer3."')";
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
							"pruefer3=".myaddslashes($pruefer3).", ".
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
						$qry="select 1;";
					}
				}
				else 
				{
					$qry="INSERT INTO lehre.tbl_abschlusspruefung (student_uid, vorsitz, pruefer1, pruefer2, pruefer3, ".
						"abschlussbeurteilung_kurzbz, akadgrad_id, datum, sponsion, pruefungstyp_kurzbz, anmerkung, ".
						"insertvon, insertamum, updatevon, updateamum, ext_id) VALUES (".
						myaddslashes($student_uid).", ".
						myaddslashes($vorsitz).", ".
						myaddslashes($pruefer1).", ".
						myaddslashes($pruefer2).", ".
						myaddslashes($pruefer3).", ".
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
				$error_log.='Fehler beim Speichern des Bachelorprüfung-Datensatzes von Student:'.$student_uid." \n".$qry."\n";
				$ausgabe1='';
			}
		}
		else 
		{
			$anzahl_fehler++;
		}
	}
	$error_log="Sync Bachelorprüfung\n-----------------------\n\n".$error_log."\n";
	echo nl2br("Bachelorprüfungsynchro Ende: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");
	echo nl2br("Gesamt: ".$anzahl_quelle." / Eingefügt: ".$anzahl_eingefuegt++." / Geändert: ".$anzahl_geaendert." / Fehler: ".$anzahl_fehler."\n\n");
	echo nl2br($error_log. "\n------------------------------------------------------------------------\n".$ausgabe);
	
	mail($adress, 'SYNC-Fehler Bachelorprüfung  von '.$_SERVER['HTTP_HOST'], $error_log, "From: vilesci@technikum-wien.at");
	mail($adress, 'SYNC Bachelorprüfung von '.$_SERVER['HTTP_HOST'], "Sync Bachelorprüfung\n-----------------------\n\nGesamt: ".$anzahl_quelle." / Eingefügt: ".$anzahl_eingefuegt++." / Geändert: ".$anzahl_geaendert." / Fehler: ".$anzahl_fehler."\n\n".$ausgabe, "From: vilesci@technikum-wien.at");
}
?>
</body>
</html>