<?php
/* Copyright (C) 2007
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */


require_once('../../../vilesci/config.inc.php');
require_once('../sync_config.inc.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

//$adress="ruhan@technikum-wien.at";
$error_log='';
$error_log_all="";
$stgart=array();
$fehler=array();

function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

?>

<html>
<head>
<title>Datenüberprüfung für BIS-Meldung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

$qry="SELECT * FROM public.tbl_studiensemester";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$beginn[$row->studiensemester_kurzbz]=$row->start;
		$ende[$row->studiensemester_kurzbz]=$row->ende;
	}
}
$qry="SELECT * FROM public.tbl_studiengang";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$stgart[$row->studiengang_kz]=$row->typ;
		$email[$row->studiengang_kz]=$row->email;
	}
}

$qry="SELECT *, tbl_abschlusspruefung.datum AS abdatum FROM public.tbl_student 
	JOIN public.tbl_benutzer ON(student_uid=uid) 
	JOIN public.tbl_person USING (person_id) 
	JOIN public.tbl_prestudent USING (prestudent_id)
	JOIN public.tbl_prestudentrolle ON(tbl_prestudent.prestudent_id=tbl_prestudentrolle.prestudent_id) 
	JOIN public.tbl_adresse ON(tbl_person.person_id=tbl_adresse.person_id)
	LEFT JOIN lehre.tbl_abschlusspruefung USING(student_uid) 
	WHERE heimatadresse IS TRUE 
	AND (studiensemester_kurzbz='WS2007')
	AND (rolle_kurzbz='Student' OR rolle_kurzbz='Incoming' OR rolle_kurzbz='Outgiong'
		OR rolle_kurzbz='Praktikant' OR rolle_kurzbz='Diplomand' OR rolle_kurzbz='Absolvent') 
	ORDER BY tbl_student.studiengang_kz, student_uid, nachname, vorname
	";
//

//uid='tw01e036' AND

//$qry="SELECT DISTINCT ON(mitarbeiter_uid) mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter ;";

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		if($row->gebdatum<'1920-01-01')
		{
			$error_log.="Geburtsdatum ('".$row->gebdatum."')";
		}
		if($row->geschlecht!='m' && $row->geschlecht!='w')
		{
			if($error_log!='')
			{
				$error_log.=", Geschlecht ('".$row->gebdatum."')";
			}
			else 
			{
				$error_log.="Geschlecht ('".$row->gebdatum."')";
			}
		}
		if($row->vorname=='' || $row->vorname==null)
		{
			if($error_log!='')
			{
				$error_log.=", Vorname ('".$row->vorname."')";
			}
			else 
			{
				$error_log.="Vorname ('".$row->vorname."')";
			}
		}
		if($row->nachname=='' || $row->nachname==null)
		{
			if($error_log!='')
			{
				$error_log.=", Nachname ('".$row->nachname."')";
			}
			else 
			{
				$error_log.="Nachname ('".$row->nachname."')";
			}
		}
		if(($row->svnr=='' || $row->svnr==null)&&($row->ersatzkennzeichen=='' || $row->ersatzkennzeichen==null))
		{
			if($error_log!='')
			{
				$error_log.=", SVNR ('".$row->svnr."') bzw. Erskz ('".$row->ersatzkennzeichen."')";
			}
			else 
			{
				$error_log.="SVNR ('".$row->svnr."') bzw. Erskz ('".$row->ersatzkennzeichen."')";
			}
		}
		if($row->staatsbuergerschaft=='' || $row->staatsbuergerschaft==null)
		{
			if($error_log!='')
			{
				$error_log.=", Staatsbürgerschaft ('".$row->staatsbuergerschaft."')";
			}
			else 
			{
				$error_log.="Staatsbürgerschaft ('".$row->staatsbuergerschaft."')";
			}
		}
		if($row->plz=='' || $row->plz==null)
		{
			if($error_log!='')
			{
				$error_log.=", Heimat-PLZ ('".$row->plz."')";
			}
			else 
			{
				$error_log.="Heimat-PLZ ('".$row->plz."')";
			}
		}
		if($row->gemeinde=='' || $row->gemeinde==null)
		{
			if($error_log!='')
			{
				$error_log.=", Heimat-Gemeinde ('".$row->gemeinde."')";
			}
			else 
			{
				$error_log.="Heimat-Gemeinde ('".$row->gemeinde."')";
			}
		}
		if($row->strasse=='' || $row->strasse==null)
		{
			if($error_log!='')
			{
				$error_log.=", Heimat-Strasse ('".$row->strasse."')";
			}
			else 
			{
				$error_log.="Heimat-Strasse ('".$row->strasse."')";
			}
		}
		if($row->nation=='' || $row->nation==null)
		{
			if($error_log!='')
			{
				$error_log.=", Heimat-Nation ('".$row->nation."')";
			}
			else 
			{
				$error_log.="Heimat-Nation ('".$row->nation."')";
			}
		}
		if($row->zgv_code=='' || $row->zgv_code==null)
		{
			if($error_log!='')
			{
				$error_log.=", ZugangCode ('".$row->zgv_code."')";
			}
			else 
			{
				$error_log.="ZugangCode ('".$row->zgv_code."')";
			}
		}
		if($row->zgvdatum=='' || $row->zgvdatum==null)
		{
			if($error_log!='')
			{
				$error_log.=", ZugangDatum ('".$row->zgvdatum."')";
			}
			else 
			{
				$error_log.="ZugangDatum ('".$row->zgvdatum."')";
			}
		}
		if($stgart[$row->studiengang_kz]=='m')
		{
			if($row->zgvmas_code=='' || $row->zgvmas_code==null)
			{
				if($error_log!='')
				{
					$error_log.=", ZugangMagStgCode ('".$row->zgvmas_code."')";
				}
				else 
				{
					$error_log.="ZugangMagStgCode ('".$row->zgvmas_code."')";
				}
			}
			if($row->zgvmadatum=='' || $row->zgvmadatum==null)
			{
				if($error_log!='')
				{
					$error_log.=", ZugangMagStgDatum ('".$row->zgvmadatum."')";
				}
				else 
				{
					$error_log.="ZugangMagStgDatum ('".$row->zgvmadatum."')";
				}
			}
		}
		
		//bei Absolventen das Beendigungsdatum (Sponsion oder Abschlussprüfung) überprüfen
		if($row->rolle_kurzbz=='Absolvent')
		{
			if($row->abdatum=='' || $row->abdatum==null)
			{
				if($error_log!='')
				{
					$error_log.=", Datum der Abschlussprüfung ('".$row->abdatum."')";
				}
				else 
				{
					$error_log.="Datum der Abschlussprüfung ('".$row->abdatum."')";
				}
			}
			if($row->sponsion=='' || $row->sponsion==null)
			{
				if($error_log!='')
				{
					$error_log.=", Datum der Sponsion ('".$row->sponsion."')";
				}
				else 
				{
					$error_log.="Datum der Sponsion ('".$row->sponsion."')";
				}
			}
		}
		if($row->zgvdatum=='' || $row->zgvdatum==null)
		{
			if($error_log!='')
			{
				$error_log.=", Berufstätigkeitscode ('".$row->zgvdatum."')";
			}
			else 
			{
				$error_log.="Berufstätigkeitscode ('".$row->zgvdatum."')";
			}
		}
		if($error_log!='')
		{
			$error_log="Bei Student (UID, Vorname, Nachname) '".$row->student_uid."', '".$row->nachname."', '".$row->vorname."' ($row->rolle_kurzbz) fehlt: ".$error_log."\n";
			if(!isset($fehler[$row->studiengang_kz]))
			{
				$fehler[$row->studiengang_kz]=$error_log;
			}
			else 
			{
				$fehler[$row->studiengang_kz].=$error_log;
			}
			$error_log="";
		}
	}
}
echo "Fehlende BIS-Daten (für Meldung 11.2007): <br>";
echo "(Doppelte Zeilen deuten auf mehrere Heimatadressen hin - bitte Kontakte überprüfen)<br><br>";
foreach ($fehler as $f => $v)
{
	echo nl2br("Studiengang: ".$f."(".$email[$f].")\n".$v."\n");
	//mail(trim($email[$f]), 'BIS-Daten / Studiengang: '.$f,"Fehlende Daten für die BIS-Meldung:\n(Doppelte Zeilen deuten auf mehrere Heimatadressen hin - bitte Kontakte überprüfen)\n\nStudiengang: ".$f."(".$email[$f].")\n".$v."\n","From: vilesci@technikum-wien.at");
	mail($adress, 'BIS-Daten / Studiengang: '.$f,"Fehlende Daten für die BIS-Meldung:\n(Doppelte Zeilen deuten auf mehrere Heimatadressen hin - bitte Kontakte überprüfen)\n\nStudiengang: ".$f."(".$email[$f].")\n".$v."\n","From: vilesci@technikum-wien.at");	
}


?>