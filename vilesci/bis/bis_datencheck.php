<?php
/* Copyright (C) 2007
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */


require('../config.inc.php');
require('../../include/studiensemester.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

//$adress="ruhan@technikum-wien.at";
$error_log='';
$error_log1='';
$error_log_all="";
$stgart='';
$fehler='';
$v='';
$studiensemester=new studiensemester($conn);
$ssem=$studiensemester->getaktorNext();

if(isset($_GET['stg_kz']))
{
	$stg_kz=$_GET['stg_kz'];
}
else 
{
	$stg_kz=0;
}
if(isset($_GET['email']))
{
	if($_GET['email']==true)
	{
		$email=true;
	}
	else 
	{
		$email=false;	
	}
}
else 
{
	$email=false;
}
function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

?>

<html>
<head>
<title>Datenüberprüfung für BIS-Meldung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
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
$qry="SELECT * FROM public.tbl_studiengang WHERE studiengang_kz='".$stg_kz."'";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$stgart=$row->typ;
		$stgemail=$row->email;
	}
}

$qry="SELECT DISTINCT ON(student_uid, nachname, vorname) *, public.tbl_person.person_id AS pers_id, tbl_abschlusspruefung.datum AS abdatum FROM public.tbl_student 
	JOIN public.tbl_benutzer ON(student_uid=uid) 
	JOIN public.tbl_person USING (person_id) 
	JOIN public.tbl_prestudent USING (prestudent_id)
	JOIN public.tbl_prestudentrolle ON(tbl_prestudent.prestudent_id=tbl_prestudentrolle.prestudent_id) 
	JOIN public.tbl_adresse ON(tbl_person.person_id=tbl_adresse.person_id)
	LEFT JOIN lehre.tbl_abschlusspruefung USING(student_uid) 
	WHERE heimatadresse IS TRUE 
	AND (studiensemester_kurzbz='".$ssem."') AND tbl_student.studiengang_kz='".$stg_kz."'
	AND (rolle_kurzbz='Student' OR rolle_kurzbz='Incoming' OR rolle_kurzbz='Outgiong'
		OR rolle_kurzbz='Praktikant' OR rolle_kurzbz='Diplomand' OR rolle_kurzbz='Absolvent') 
	ORDER BY student_uid, nachname, vorname
	";

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$qryadr="SELECT * from public.tbl_adresse WHERE person_id='".$row->pers_id."';";
		if(pg_num_rows(pg_query($conn,$qryadr))!=1)
		{
			$error_log1="Es sind ".pg_num_rows(pg_query($conn,$qryadr))." Heimatadressen eingetragen\n";
		}
		if($row->gebdatum<'1920-01-01' OR $row->gebdatum==null OR $row->gebdatum='')
		{
			if($error_log!='')
			{
				$error_log=", Geburtsdatum ('".$row->gebdatum."')";
			}
			else 
			{
				$error_log.="Geburtsdatum ('".$row->gebdatum."')";
			}
		}
		if($row->geschlecht!='m' && $row->geschlecht!='w')
		{
			if($error_log!='')
			{
				$error_log.=", Geschlecht ('".$row->geschlecht."')";
			}
			else 
			{
				$error_log.="Geschlecht ('".$row->geschlecht."')";
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
		if($stgart=='m')
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
		if($error_log!='' OR $error_log1!='')
		{
			$v.="<u>Bei Student (UID, Vorname, Nachname) '".$row->student_uid."', '".$row->nachname."', '".$row->vorname."' ($row->rolle_kurzbz): </u>\n";
			if($error_log!='')
			{
				$v.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Es fehlt: ".$error_log."\n";
			}
			if($error_log1!='')
			{
				$v.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$error_log1;
			}
			$v.="\n";
		}
		$error_log='';
		$error_log1='';
	}
}
echo "<H1>BIS - Studentendaten werden überprüft. Studiengang: ".$stg_kz."</H1>\n";
echo "<H2>Fehlende BIS-Daten (für Meldung ".$ssem."): </H2><br>";
//echo "(Doppelte Zeilen deuten auf mehrere Heimatadressen hin - bitte Kontakte überprüfen)<br><br>";

echo nl2br($v."\n");

if($email)
{
	mail(trim($stgemail), 'BIS-Daten / Studiengang: '.$stg_kz,"Fehlende Daten für die BIS-Meldung:(von ".$_SERVER['HTTP_HOST'].")\n(Doppelte Zeilen deuten auf mehrere Heimatadressen hin - bitte Kontakte überprüfen)\n\nStudiengang: ".$stg_kz."(".$stgemail.")\n".$v."\n","From: vilesci@technikum-wien.at");
	//mail($adress, 'BIS-Daten / Studiengang: '.$f,"\nFehlende Daten für die BIS-Meldung: (von ".$_SERVER['HTTP_HOST'].")\n(Doppelte Zeilen deuten auf mehrere Heimatadressen hin - bitte Kontakte überprüfen)\n\nStudiengang: ".$f."(".$email[$f].")\n".$v."\n","From: vilesci@technikum-wien.at");	
}	



?>