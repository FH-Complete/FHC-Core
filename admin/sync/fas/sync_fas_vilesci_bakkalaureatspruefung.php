<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Diplompr�fungsdatens�tze von FAS DB in PORTAL DB
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
<title>Synchro - FAS -> Vilesci - Bachelorpr�fung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
$qry_main = "SELECT * FROM bakkalaureatspruefung;";

if($result = pg_query($conn_fas, $qry_main))
{
	echo nl2br("Bachelorpr�fung Sync\n----------------------\n");
	echo nl2br("Bachelorpr�fungsynchro Beginn: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");
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
		$typ					='b';
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
		//pr�fer1 ermitteln
		$qry="SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE ext_id='".$row->pruefer1_fk."';";
		if($resulto=pg_query($conn, $qry))
		{
			if($rowo=pg_fetch_object($resulto))
			{ 
				$pruefer1=$rowo->mitarbeiter_uid;
			}
			else 
			{
				$error=true;
				$error_log.="Pr�fer1 mit mitarbeiter_fk: $row->pruefer1_fk konnte nicht gefunden werden.\n";
			}
		}
		//pr�fer2 ermitteln
		$qry="SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE ext_id='".$row->pruefer2_fk."';";
		if($resulto=pg_query($conn, $qry))
		{
			if($rowo=pg_fetch_object($resulto))
			{ 
				$pruefer2=$rowo->mitarbeiter_uid;
			}
			else 
			{
				$error=true;
				$error_log.="Pr�fer2 mit mitarbeiter_fk: $row->pruefer2_fk konnte nicht gefunden werden.\n";
			}
		}
		//pr�fer3 ermitteln, wenn an pr�fung teilgenommen
		if($row->pruefer3_fk>'-1')
		{
			$qry="SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE ext_id='".$row->pruefer3_fk."';";
			if($resulto=pg_query($conn, $qry))
			{
				if($rowo=pg_fetch_object($resulto))
				{ 
					$pruefer3=$rowo->mitarbeiter_uid;
				}
				else 
				{
					$error=true;
					$error_log.="Pr�fer3 mit mitarbeiter_fk: $row->pruefer3_fk konnte nicht gefunden werden.\n";
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
			$qry="SELECT * FROM lehre.tbl_abschlusspruefung WHERE student_uid='".$student_uid."' AND typ='b' AND ext_id='".$row->bakkalaureatspruefung_pk."';";
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
							$ausgabe1.=", Pr�fer1: '".$pruefer1."' (statt '".$rowo->pruefer1."')";
						}
						else
						{
							$ausgabe1="Pr�fer1: '".$pruefer1."' (statt '".$rowo->pruefer1."')";
						}
					}
					if($rowo->pruefer2!=$pruefer2) 
					{
						$update=true;
						if(strlen(trim($ausgabe1))>0)
						{
							$ausgabe1.=", Pr�fer2: '".$pruefer2."' (statt '".$rowo->pruefer2."')";
						}
						else
						{
							$ausgabe1="Pr�fer2: '".$pruefer2."' (statt '".$rowo->pruefer2."')";
						}
					}
					if($rowo->pruefer3!=$pruefer3) 
					{
						$update=true;
						if(strlen(trim($ausgabe1))>0)
						{
							$ausgabe1.=", Pr�fer3: '".$pruefer3."' (statt '".$rowo->pruefer3."')";
						}
						else
						{
							$ausgabe1="Pr�fer3: '".$pruefer3."' (statt '".$rowo->pruefer3."')";
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
							$ausgabe1.=", Pr�fungsdatum: '".$datum."' (statt '".$rowo->datum."')";
						}
						else
						{
							$ausgabe1="Pr�fungsdatum: '".$datum."' (statt '".$rowo->datum."')";
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
					if($rowo->typ!=$typ) 
					{
						$update=true;
						if(strlen(trim($ausgabe1))>0)
						{
							$ausgabe1.=", Typ: '".$typ."' (statt '".$rowo->typ."')";
						}
						else
						{
							$ausgabe1="Typ: '".$typ."' (statt '".$rowo->typ."')";
						}
					}
					if($rowo->typ!=$typ) 
					{
						$update=true;
						if(strlen(trim($ausgabe1))>0)
						{
							$ausgabe1.=", Typ: '".$typ."' (statt '".$rowo->typ."')";
						}
						else
						{
							$ausgabe1="Typ: '".$typ."' (statt '".$rowo->typ."')";
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
							"typ=".myaddslashes($typ).", ".
							"anmerkung=".myaddslashes($anmerkung).", ".
							"insertvon=".myaddslashes($insertvon).", ".
							"insertamum=".myaddslashes($insertamum).", ".
							"updatevon='SYNC', ".
							"updateamum= now(), ".
							"ext_id=".myaddslashes($ext_id).
							";";
							$ausgabe.="Abschlusspr�fung von Student mit UID '".$student_uid."' ge�ndert: ".$ausgabe1."\n;";
							$anzahl_geaendert++;
					}
				}
				else 
				{
					$qry="INSERT INTO lehre.tbl_abschlusspruefung (student_uid, vorsitz, pruefer1, pruefer2, pruefer3, ".
						"abschlussbeurteilung_kurzbz, akadgrad_id, datum, sponsion, typ, anmerkung, ".
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
						myaddslashes($typ).", ".
						myaddslashes($anmerkung).", ".
						myaddslashes($insertvon).", ".
						myaddslashes($insertamum).", ".
						"'SYNC', ".
						"now(), ".
						myaddslashes($ext_id).
						");";
						$ausgabe.="Abschlusspr�fung von Student mit UID '".$student_uid."' am '".$datum."' eingetragen.\n";
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
				$error_log.='Fehler beim Speichern des Bachelorpr�fung-Datensatzes von Student:'.$student_uid." \n".$qry."\n";
				$ausgabe1='';
			}
		}
		else 
		{
			$anzahl_fehler++;
		}
	}
	$error_log="Sync Bachelorpr�fung\n-----------------------\n\n".$error_log."\n";
	echo nl2br("Bachelorpr�fungsynchro Ende: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");
	echo nl2br("Gesamt: ".$anzahl_quelle." / Eingef�gt: ".$anzahl_eingefuegt++." / Ge�ndert: ".$anzahl_geaendert." / Fehler: ".$anzahl_fehler."\n\n");
	echo nl2br($error_log. "\n------------------------------------------------------------------------\n".$ausgabe);
	
	mail($adress, 'SYNC-Fehler Bachelorpr�fung  von '.$_SERVER['HTTP_HOST'], $error_log, "From: vilesci@technikum-wien.at");
	mail($adress, 'SYNC Bachelorpr�fung von '.$_SERVER['HTTP_HOST'], "Sync Bachelorpr�fung\n-----------------------\n\nGesamt: ".$anzahl_quelle." / Eingef�gt: ".$anzahl_eingefuegt++." / Ge�ndert: ".$anzahl_geaendert." / Fehler: ".$anzahl_fehler."\n\n".$ausgabe, "From: vilesci@technikum-wien.at");
}
?>
</body>
</html>