<?php
/* Copyright (C) 2007 
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          AndreAS Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Synchronisiert Studentendatensaetze von FAS DB in PORTAL DB
 * benötigt: tbl_nation, tbl_sprache, tbl_studiengang
 * benötigt: tbl_syncperson, tbl_studiensemester, tbl_reihungstest
*/
require_once('../../../vilesci/config.inc.php');
require_once('../../../include/datum.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

$error_log='';
$text = '';
$text1 = '';
$text2 = '';
$text3 = '';
$text4 = '';
$text5 = '';
$plausiueb='';
$dateiausgabe='';
//$dateiausgabe=fopen('sync_fas_vilesci_student_ausgabe.txt','w');
//fwrite($dateiausgabe,"Ausgabe der Studentensynchro vom ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");
$anzahl_eingefuegt=0;
$anzahl_fehler=0;
$ausgabe='';
$new_person=false;
$new_prestudent=false;
$new_student=false;
$new_benutzer=false;
$new_rolle=false;
$i=0;
$notest=0;
$anzahl_person_gesamt=0;
$anzahl_student_gesamt=0;
$anzahl_pre_gesamt=0;
$anzahl_benutzer_gesamt=0;
$anzahl_person_insert=0;
$anzahl_person_update=0;
$anzahl_fehler_person=0;
$anzahl_student_insert=0;
$anzahl_student_update=0;
$anzahl_fehler_student=0;
$anzahl_pre_insert=0;
$anzahl_pre_update=0;
$anzahl_fehler_pre=0;
$anzahl_benutzer_insert=0;
$anzahl_benutzer_update=0;
$anzahl_fehler_benutzer=0;
$anzahl_nichtstudenten=0;
$updateb=false;
$updatem=false;
$updatep=false;
$updater=false;
$updates=false;
$rolle_kurzbz=array(1=>"Interessent", 2=>"Bewerber", 3=>"Student", 4=>"Ausserordentlicher", 5=>"Abgewiesener", 6=>"Aufgenommener", 7=>"Wartender", 8=>"Abbrecher", 9=>"Unterbrecher", 10=>"Outgoing", 11=>"Incoming", 12=>"Praktikant", 13=>"Diplomant", 14=>"Absolvent");
$studiensemester_kurzbz=array(2=>"WS2002",3=>"SS2003",4=>"WS2003",5=>"SS2004",6=>"WS2004",7=>"SS2005",8=>"WS2005",9=>"SS2006",10=>"WS2006",11=>"SS2007",12=>"WS2007",13=>"SS2008",14=>"WS2008",15=>"SS2009",16=>"WS2009");
$studiengangfk=array(2=>11,3=>91,4=>94,5=>145,6=>227,7=>182,8=>222,9=>203,10=>204,11=>92,12=>258,13=>308,14=>254,15=>256,16=>257,17=>255,18=>302,19=>336,20=>330,21=>333, 22=>327,23=>335,24=>228,25=>303,26=>299,27=>298,28=>300,29=>297,30=>329,31=>301,32=>332,33=>331,34=>328,35=>476,36=>1,37=>334);
//Kennzahlen für MEUE im Array studiengangfk NACHTRAGEN

$error_log_fas=array();
foreach ($studiengangfk AS $stg)
{
	$error_log_fas[$stg]='';
}

set_time_limit(60);
//$adress='ruhan@technikum-wien.at';
$adress='fas_sync@technikum-wien.at';

function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

// ***********************************
// * VILESCI->PORTAL - Synchronisation
// ***********************************
?>

<html>
<head>
<title>Synchro - FAS -> Vilesci - Student</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
echo nl2br("Studentensynchro Beginn ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");

$plausiueb="Überprüfung Studentendaten im FAS:\n\n";

$qry="SELECT * FROM person JOIN student ON person_pk=student.person_fk WHERE svnr='0005010400';";
if($resultp = pg_query($conn_fas, $qry))
{
	if(pg_num_rows($resultp)>0)
	{
		$plausisvnr.="SVNr 0005010400 findet sich bei folgenden ".pg_numrows($resultp)." Studenten:\n";
		while($rowp=pg_fetch_object($resultp))
		{
			$plausisvnr.="Student ".$rowp->uid." / ".$rowp->familienname."\n";
			$error=true;
		}
	}
}
if(pg_numrows($resultp)==0)
{
	$plausisvnr='';
}
echo nl2br($plausisvnr."\n");
$qry="
SELECT 
p1.person_pk AS person1, p1.familienname AS familienname1, p1.vorname AS vorname1, p1.vornamen AS vornamen1, p1.geschlecht AS geschlecht1, 
p1.gebdat AS gebdat1, p1.gebort AS gebort1, p1.staatsbuergerschaft AS staatsbuergerschaft1, p1.familienstand AS familienstand1, 
p1.svnr AS svnr1, p1. ersatzkennzeichen  AS ersatzkennzeichen1, p1.anrede AS anrede1, p1.anzahlderkinder AS anzahlderkinder1, 
p1.titel AS titel1,  p1.gebnation AS gebnation1, p1.postnomentitel AS postnomentitel1, p1.uid as uid1,
p1.student_pk AS student1, p1.zgv AS zgv1, p1.studiengang_fk AS studiengang1, p1.zgvdatum AS zgvdatum1, p1.zgvort AS zgvort1,
p1.zgvmagister AS zgvmagister1, p1.zgvmagisterort AS zgvmagisterort1, p1.zgvmagisterdatum AS zgvmagisterdatum1, p1.punkte AS punkte1,
p1.perskz AS perskz1, p1.aufgenommenam AS aufgenommenam1, p1.aufmerksamdurch AS aufmerksamdurch1, p1.berufstaetigkeit AS berufstaetigkeit1,
p1.beendigungsdatum AS beendigungsdatum1, p1.berufstaetigkeit AS berufstaetigkeit1, p1.aufmerksamdurch_fk AS aufmerksamdurchfk1,
p1.aufnahmeschluessel AS aufnahmeschluessel1, p1.aufnahmeschluessel_fk AS aufnahmeschluesselfk1, p1.angetreten AS angetreten1,
p2.person_pk AS person2, p2.familienname AS familienname2, p2.vorname AS vorname2, p2.vornamen AS vornamen2, p2.geschlecht AS geschlecht2, 
p2.gebdat AS gebdat2, p2.gebort AS gebort2, p2.staatsbuergerschaft AS staatsbuergerschaft2, p2.familienstand AS familienstand2, 
p2.svnr AS svnr2, p2. ersatzkennzeichen  AS ersatzkennzeichen2, p2.anrede AS anrede2, p2.anzahlderkinder AS anzahlderkinder2, 
p2.titel AS titel2,  p2.gebnation AS gebnation2, p2.postnomentitel AS postnomentitel2, p2.uid as uid2, 
p2.student_pk AS student2, p2.zgv AS zgv2, p2.studiengang_fk AS studiengang2, p2.zgvdatum AS zgvdatum2, p2.zgvort AS zgvort2,
p2.zgvmagister AS zgvmagister2, p2.zgvmagisterort AS zgvmagisterort2, p2.zgvmagisterdatum AS zgvmagisterdatum2, p2.punkte AS punkte2,
p2.perskz AS perskz2, p2.aufgenommenam AS aufgenommenam2, p2.aufmerksamdurch AS aufmerksamdurch2, p2.berufstaetigkeit AS berufstaetigkeit2,
p2.beendigungsdatum AS beendigungsdatum2, p2.berufstaetigkeit AS berufstaetigkeit2, p2.aufmerksamdurch_fk AS aufmerksamdurchfk2,
p2.aufnahmeschluessel AS aufnahmeschluessel2, p2.aufnahmeschluessel_fk AS aufnahmeschluesselfk2, p2.angetreten AS angetreten2
FROM (person JOIN student ON person_pk=student.person_fk ) AS p1 
CROSS JOIN (person JOIN student ON person_pk=student.person_fk) AS p2 WHERE 
((p1.svnr=p2.svnr AND p1.svnr IS NOT NULL AND p1.svnr<>'') 
OR ((p1.svnr<>p2.svnr AND (p1.svnr IS NULL OR p1.svnr='' OR p2.svnr IS NULL OR p2.svnr='')) 
	AND p1.familienname=p2.familienname AND p1.familienname IS NOT NULL AND p1.familienname!='' 
	AND p1.gebdat=p2.gebdat AND p1.gebdat IS NOT NULL AND p1.gebdat>'1935-01-01' AND p1.gebdat<'2000-01-01'))
AND (p1.person_pk < p2.person_pk) 
AND (p1.svnr<>'0005010400' AND p2.svnr<>'0005010400') 
AND (trim(p1.familienname)<>trim(p2.familienname) OR trim(p1.vorname)<>trim(p2.vorname) OR trim(p1.vornamen)<>trim(p2.vornamen)
	OR p1.geschlecht<>p2.geschlecht OR p1.gebort<>p2.gebort 
	OR p1.gebdat<>p2.gebdat OR p1.staatsbuergerschaft<> p2.staatsbuergerschaft OR p1.familienstand<>p2.familienstand 
	OR p1.svnr<>p2.svnr OR p1.ersatzkennzeichen<>p2.ersatzkennzeichen OR p1.anrede<>p2.anrede OR p1.titel<>p2.titel 
	OR p1.anzahlderkinder<>p2.anzahlderkinder OR p1.gebnation<>p2.gebnation OR p1.postnomentitel<> p2.postnomentitel 

	OR ((p1.zgv<>p2.zgv OR p1.zgvdatum<>p2.zgvdatum OR p1.zgvort<>p2.zgvort 
	OR p1.zgvmagister<>p2.zgvmagister OR p1.zgvmagisterort<>p2.zgvmagisterort OR p1.zgvmagisterdatum<>p2.zgvmagisterdatum 
	OR p1.punkte<>p2.punkte OR p1.perskz<>p2.perskz OR p1.aufgenommenam<>p2.aufgenommenam  
	OR p1.beendigungsdatum<>p2.beendigungsdatum OR p1.aufmerksamdurch<>p2.aufmerksamdurch 
	OR p1.aufnahmeschluessel<>p2.aufnahmeschluessel OR p1.aufnahmeschluessel_fk<>p2.aufnahmeschluessel_fk 
	OR p1.berufstaetigkeit<>p2.berufstaetigkeit OR p1.aufmerksamdurch_fk<>p2.aufmerksamdurch_fk 
	OR p1.angetreten<>p2.angetreten)AND p1.studiengang_fk=p2.studiengang_fk))
order by p1.familienname;
";
//
if($resultp = pg_query($conn_fas, $qry))
{
	while($rowp=pg_fetch_object($resultp))
	{
		$studstg1='';
		$studstg2='';
		//ermittle Stg-Kürzel
		$qrystg="SELECT typ, kurzbz FROM public.tbl_studiengang WHERE studiengang_kz='".$studiengangfk[$rowp->studiengang1]."';";
		if($resultstg = pg_query($conn, $qrystg))
		{
			if(pg_num_rows($resultstg)>0)
			{
				if($rowstg = pg_fetch_object($resultstg))
				{
					$studstg1=strtoupper(trim($rowstg->typ)).strtoupper(trim($rowstg->kurzbz));
				}
			}
			else 
			{
				echo nl2br("Studiengang ".$studiengangfk[$rowp->studiengang1]." nicht gefunden.");
				
			}
		}
		else 
		{
			echo nl2br("Kein Zugriff auf tbl_studiengang => Studiengang ".$studiengangfk[$rowp->studiengang1]." nicht gefunden.");
		}
		
		//ermittle Stg-Kürzel
		$qrystg="SELECT typ, kurzbz FROM tbl_studiengang WHERE studiengang_kz='".$studiengangfk[$rowp->studiengang2]."';";
		if($resultstg = pg_query($conn, $qrystg))
		{
			if(pg_num_rows($resultstg)>0)
			{
				if($rowstg = pg_fetch_object($resultstg))
				{
					$studstg2=strtoupper(trim($rowstg->typ)).strtoupper(trim($rowstg->kurzbz));
				}
			}
			else 
			{
				echo nl2br("Studiengang ".$studiengangfk[$rowp->studiengang2]." nicht gefunden.");
			}
		}
		else 
		{
			echo nl2br("Kein Zugriff auf tbl_studiengang => Studiengang ".$studiengangfk[$rowp->studiengang2]." nicht gefunden.");
		}
		$plausi='';
		//if ($rowp->studiengang1<>$rowp->studiengang2)
		//{
		//	$plausi.="Studiengang der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->studiengang1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->studiengang2."'.\n";
		//	$error=true;
		//}
		if ($rowp->geschlecht1<>$rowp->geschlecht2)
		{
			$plausi.="Geschlecht der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->geschlecht1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->geschlecht2."'.\n";
			$error=true;
		}
		if (trim($rowp->familienname1)<>trim($rowp->familienname2))
		{
			$plausi.="Familienname der Person ".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->familienname1."' bei ".$rowp->uid2." (stg=(".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.")  aber '".$rowp->familienname2."'.\n";
			$error=true;
		}
		if (trim($rowp->vorname1)<>trim($rowp->vorname2))
		{
			$plausi.="Vorname der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->vorname1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->vorname2."'.\n";
			$error=true;
		}
		if (trim($rowp->vornamen1)<>trim($rowp->vornamen2))
		{
			$plausi.="Vornamen der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->vornamen1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->vornamen2."'.\n";
			$error=true;
		}
		if ($rowp->gebdat1<>$rowp->gebdat2)
		{
			$plausi.="Geburtsdatum der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->gebdat1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->gebdat2."'.\n";
			$error=true;
		}
		if ($rowp->gebort1<>$rowp->gebort2)
		{
			$plausi.="Geburtsort der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->gebort1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->gebort2."'.\n";
			$error=true;
		}
		if ($rowp->staatsbuergerschaft1<>$rowp->staatsbuergerschaft2)
		{
			$plausi.="Staatsbürgerschaft der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->staatsbuergerschaft1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->staatsbuergerschaft2."'.\n";
			$error=true;
		}
		if ($rowp->familienstand1<>$rowp->familienstand2)
		{
			$plausi.="Familienstand der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->familienstand1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->familienstand2."'.\n";
			$error=true;
		}
		if ($rowp->svnr1<>$rowp->svnr2)
		{
			$plausi.="Sozialversicherung der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->svnr1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->svnr2."'.\n";
			$error=true;
		}
		if ($rowp->ersatzkennzeichen1<>$rowp->ersatzkennzeichen2)
		{
			$plausi.="Ersatzkennzeichen der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->ersatzkennzeichen1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->ersatzkennzeichen2."'.\n";
			$error=true;
		}
		if ($rowp->anrede1<>$rowp->anrede2)
		{
			$plausi.="Anrede der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->anrede1."' bei ".$rowp->familienname2." (".$rowp->uid2.", ".", stg=".$studstg1.", (".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->anrede2."'.\n";
			$error=true;
		}
		if ($rowp->anzahlderkinder1<>$rowp->anzahlderkinder2)
		{
			$plausi.="Anzahl der Kinder der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->anzahlderkinder1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->anzahlderkinder2."'.\n";
			$error=true;
		}
		if ($rowp->gebnation1<>$rowp->gebnation2)
		{
			$plausi.="Geburtsnation der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->gebnation1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->gebnation2."'.\n";
			$error=true;
		}
		if ($rowp->titel1<>$rowp->titel2)
		{
			$plausi.="Titel der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->titel1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->titel2."'.\n";
			$error=true;
		}
		if ($rowp->postnomentitel1<>$rowp->postnomentitel2)
		{
			$plausi.="Postnomentitel der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->postnomentitel1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->postnomentitel2."'.\n";
			$error=true;
		}
		if($studstg1==$studstg2)
		{
			if ($rowp->perskz1<>$rowp->perskz2)
			{
				$plausi.="Personenkennzeichen der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->perskz1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->perskz2."'.\n";
				$error=true;
			}
			if ($rowp->zgv1<>$rowp->zgv2)
			{
				$plausi.="Zugangsvoraussetzung der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->zgv1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->zgv2."'.\n";
				$error=true;
			}
			if ($rowp->zgvdatum1<>$rowp->zgvdatum2)
			{
				$plausi.="Zugangsvoraussetzungsdatum der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->zgvdatum1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->zgvdatum2."'.\n";
				$error=true;
			}
			if ($rowp->zgvort1<>$rowp->zgvort2)
			{
				$plausi.="Zugangsvoraussetzungsort der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->zgvort1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->zgvort2."'.\n";
				$error=true;
			}
			if ($rowp->zgvmagister1<>$rowp->zgvmagister2)
			{
				$plausi.="Magister-Zugangsvoraussetzung der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->zgvmagister1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->zgvmagister2."'.\n";
				$error=true;
			}
			if ($rowp->zgvmagisterdatum1<>$rowp->zgvmagisterdatum2)
			{
				$plausi.="Magister-Zugangsvoraussetzungsdatum der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->zgvmagisterdatum1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->zgvmagisterdatum2."'.\n";
				$error=true;
			}
			if ($rowp->zgvmagisterort1<>$rowp->zgvmagisterort2)
			{
				$plausi.="Magister-Zugangsvoraussetzungort der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->zgvmagisterort1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->zgvmagisterort2."'.\n";
				$error=true;
			}
			if ($rowp->punkte1<>$rowp->punkte2)
			{
				$plausi.="Reihungstestpunkte der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->punkte1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->punkte2."'.\n";
				$error=true;
			}
			if ($rowp->aufgenommenam1<>$rowp->aufgenommenam2)
			{
				$plausi.="Aufnahmedatum der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->aufgenommenam1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->aufgenommenam2."'.\n";
				$error=true;
			}
			if ($rowp->beendigungsdatum1<>$rowp->beendigungsdatum2)
			{
				$plausi.="Beendigungsdatum der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->beendigungsdatum1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->beendigungsdatum2."'.\n";
				$error=true;
			}
			if ($rowp->aufmerksamdurch1<>$rowp->aufmerksamdurch2)
			{
				$plausi.="Aufmerksamdurch der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->aufmerksamdurch1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->aufmerksamdurch2."'.\n";
				$error=true;
			}
			if ($rowp->berufstaetigkeit1<>$rowp->berufstaetigkeit2)
			{
				$plausi.="Berufstätigkeit der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->berufstaetigkeit1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->berufstaetigkeit2."'.\n";
				$error=true;
			}
			if ($rowp->aufmerksamdurchfk1<>$rowp->aufmerksamdurchfk2)
			{
				$plausi.="Aufmerksamdurch(fk) der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->aufmerksamdurchfk1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->aufmerksamdurchfk2."'.\n";
				$error=true;
			}
			if ($rowp->aufnahmeschluessel1<>$rowp->aufnahmeschluessel2)
			{
				$plausi.="Aufnahmeschluessel der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->aufnahmeschluessel1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->aufnahmeschluessel2."'.\n";
				$error=true;
			}
			if ($rowp->aufnahmeschluesselfk1<>$rowp->aufnahmeschluesselfk2)
			{
				$plausi.="Aufnahmeschluessel(fk) der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->aufnahmeschluesselfk1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->aufnahmeschluesselfk2."'.\n";
				$error=true;
			}
			if ($rowp->angetreten1<>$rowp->angetreten2)
			{
				$plausi.="Angetreten der Person ".$rowp->familienname1." (".$rowp->uid1.", stg=".$studstg1."(".$studiengangfk[$rowp->studiengang1]."), person_pk=".$rowp->person1.") ist '".$rowp->angetreten1."' bei ".$rowp->familienname2." (".$rowp->uid2.", stg=".$studstg2."(".$studiengangfk[$rowp->studiengang2]."), person_pk=".$rowp->person2.") aber '".$rowp->angetreten2."'.\n";
				$error=true;
			}
		}
		
		if ($error)
		{
			
			$error_log_fas[$studiengangfk[$rowp->studiengang1]].="*****\n".$plausi."*****\n";
			echo nl2br ("*****\n".$plausi."*****\n");
			//ob_flush();
			//flush();
			$error=false;
		}
	}
}
foreach ($studiengangfk AS $stg)
{
	$error_log_fas[$stg]=$plausisvnr."\n".$error_log_fas[$stg];
	$qryass="SELECT email FROM tbl_studiengang WHERE studiengang_kz='$stg';";
	if($resultass = pg_query($conn, $qryass))
	{
		if(pg_num_rows($resultass)>0)
		{
			if($rowass = pg_fetch_object($resultass))
			{
				if(strlen(trim($error_log_fas[$stg]))>0)
				{
					$errro_log_fas[$stg]=$plausiueb.$error_log_fas[$stg];
					//mail(trim($rowass->email), 'Plausicheck von Studenten / Studiengang: '.$stg, $error_log_fas[$stg],"From: vilesci@technikum-wien.at");
					
					if (!mail($adress, 'Plausicheck von Studenten / Studiengang: '.$stg.", von ".$_SERVER['HTTP_HOST'], $error_log_fas[$stg],"From: vilesci@technikum-wien.at"))
					{
						echo nl2br("Plausicheck-Mail('".$stg."') an '".$adress."' konnte nicht verschickt werden!\n");
					}
				}
			}
		}
		else 
		{
			echo nl2br("Studiengang ".$stg." nicht gefunden. E-Mail mit folgenden Inhalt wird nicht verschickt:\n".$error_log_fas[$stg]);
		}
	}
	else 
	{
		echo nl2br("Kein Zugriff auf tbl_studiengang => Studiengang ".$stg." nicht gefunden. E-Mail mit folgendem Inhalt wird nicht verschickt:\n".$error_log_fas[$stg]);
	}

}



$qry="
SELECT * FROM person JOIN student ON person_fk=person_pk WHERE uid NOT LIKE '\_dummy%' 
AND person_pk NOT IN(
	SELECT p1.person_pk
	FROM (person JOIN student ON person_pk=student.person_fk ) AS p1 
	CROSS JOIN (person JOIN student ON person_pk=student.person_fk) AS p2 WHERE 
	(
		(p1.svnr=p2.svnr AND p1.svnr IS NOT NULL AND p1.svnr<>'') 
		OR (
			(p1.svnr<>p2.svnr AND 
				(p1.svnr IS NULL OR p1.svnr='' OR p2.svnr IS NULL OR p2.svnr='')
			) 
			AND p1.familienname=p2.familienname AND p1.familienname IS NOT NULL AND p1.familienname!='' 
			AND p1.gebdat=p2.gebdat AND p1.gebdat IS NOT NULL AND p1.gebdat>'1935-01-01' AND p1.gebdat<'2000-01-01'
		)
	)
	AND (p1.person_pk < p2.person_pk) 
	AND (p1.svnr<>'0005010400' AND p2.svnr<>'0005010400') 
	AND (trim(p1.familienname)<>trim(p2.familienname) OR trim(p1.vorname)<>trim(p2.vorname) OR trim(p1.vornamen)<>trim(p2.vornamen)
		OR p1.geschlecht<>p2.geschlecht OR p1.gebort<>p2.gebort 
		OR p1.gebdat<>p2.gebdat OR p1.staatsbuergerschaft<> p2.staatsbuergerschaft OR p1.familienstand<>p2.familienstand 
		OR p1.svnr<>p2.svnr OR p1.ersatzkennzeichen<>p2.ersatzkennzeichen OR p1.anrede<>p2.anrede OR p1.titel<>p2.titel 
		OR p1.anzahlderkinder<>p2.anzahlderkinder OR p1.gebnation<>p2.gebnation OR p1.postnomentitel<> p2.postnomentitel 
	
		OR (
			(
				p1.zgv<>p2.zgv OR p1.zgvdatum<>p2.zgvdatum OR p1.zgvort<>p2.zgvort 
				OR p1.zgvmagister<>p2.zgvmagister OR p1.zgvmagisterort<>p2.zgvmagisterort 
				OR p1.zgvmagisterdatum<>p2.zgvmagisterdatum OR p1.punkte<>p2.punkte OR p1.perskz<>p2.perskz 
				OR p1.aufgenommenam<>p2.aufgenommenam  	OR p1.beendigungsdatum<>p2.beendigungsdatum 
				OR p1.aufmerksamdurch<>p2.aufmerksamdurch OR p1.aufnahmeschluessel<>p2.aufnahmeschluessel 
				OR p1.aufnahmeschluessel_fk<>p2.aufnahmeschluessel_fk OR p1.berufstaetigkeit<>p2.berufstaetigkeit 
				OR p1.aufmerksamdurch_fk<>p2.aufmerksamdurch_fk OR p1.angetreten<>p2.angetreten
			)
			AND p1.studiengang_fk=p2.studiengang_fk
		)
	)
)
;
";
//order by familienname
//$qry="SELECT * FROM person JOIN student ON person_fk=person_pk WHERE uid='iw05d203';";
$datum_obj=new datum();
if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("\n Sync Student\n--------------\n\n");
	while($row = pg_fetch_object($result))
	{
		//echo "- ";
		//ob_flush();
		//flush();
		$ausgabe_person='';
		$ausgabe_benutzer='';
		$ausgabe_pre='';
		$ausgabe_prerolle='';
		$ausgabe_student='';
		$error_log='';
		//$text='';
		$error=false;
		
		//Attribute Person
		$staatsbuergerschaft=$row->staatsbuergerschaft;
		$geburtsnation=$row->gebnation;
		$sprache='German';
		$anrede=$row->anrede;
		$titelpost=trim($row->postnomentitel);
		$titelpre=trim($row->titel);
		$nachname=trim($row->familienname);
		$vorname=trim($row->vorname);
		$vornamen=trim($row->vornamen);
		$gebdatum=$row->gebdat;
		$gebort=$row->gebort;
		$gebzeit=''; //bei update auslassen
		$foto=''; //bei update auslassen
		$anmerkungen='';
		$homepage='';
		$svnr=$row->svnr;
		$ersatzkennzeichen=$row->ersatzkennzeichen;
		if ($row->familienstand<='0')
		{
			$familienstand=null;
		}
		if ($row->familienstand=='1')
		{
			$familienstand='l';
		}
		if ($row->familienstand=='2')
		{
			$familienstand='v';
		}
		if ($row->familienstand=='3')
		{
			$familienstand='g';
		}
		if ($row->familienstand=='4')
		{
			$familienstand='w';
		}
		$geschlecht=strtolower($row->geschlecht);
		$anzahlkinder=$row->anzahlderkinder;
		//$aktiv=($row->aktiv=='t'?true:false);
		$insertvon=$row->creationuser;
		$insertamum=$row->creationdate;
		$updateamum=$row->creationdate;
		$updatevon='SYNC';
		$ext_id_person=$row->person_pk;
				
		//Attribute Benutzer
		$uid='';
		$person_id='';
		$alias='';
		$ext_id_benutzer=$row->student_pk;
		
		//Attribute Prestudent
		$aufmerksamdurch_kurzbz='';
		$person_id='';
		$studiengang_kz='';
		$berufstaetigkeit_code=$row->berufstaetigkeit;
		if($berufstaetigkeit_code<0)
		{
			$berufstaetigkeit_code=null;
		}
		$ausbildungcode='';
		$zgv_code=$row->zgv;
		$zgvort=trim($row->zgvort);
		$zgvdatum=$row->zgvdatum;
		$zgvmas_code=$row->zgvmagister;
		$zgvmaort=trim($row->zgvmagisterort);
		$zgvmadatum=$row->zgvmagisterdatum;
		$facheinschlberuf=($row->berufstaetigkeit=='J'?true:false);
		$reihungstest_id='';
		$punkte=$row->punkte;
		$ext_id_pre=$row->student_pk;
		$anmeldungreihungstest='';
		$reihungstestangetreten=($row->angetreten=='J'?true:false);
		$anmerkung=$row->bemerkung;
		//bismelden		

		//Attribute Student
		$student_uid=trim($row->uid);
		$matrikelnr=trim($row->perskz);		
		$prestudent_id='';
		//studiengang_kz bei prestudent
		$semester='';
		$verband='';
		$gruppe='';
		$ext_id_student=$row->student_pk;
			
		if($zgv_code<=0 or $zgv_code=='')
		{
			$zgv_code=null;
		}
		if($zgvmas_code<=0 or $zgvmas_code=='')
		{
			$zgvmas_code=null;
		}
		
		//Attribute überprüfen!!!	
		if(strlen($sprache)>16)
		{
			$error_log = "Sprache darf nicht laenger als 16 Zeichen sein: ".$nachname.", ".$vorname."\n";
		}
		if(strlen($anrede)>16)
		{
			$error_log = "Anrede darf nicht laenger als 16 Zeichen sein: ".$nachname.", ".$vorname."\n";
		}
		if(strlen($titelpost)>32)
		{
			$error_log = "Titelpost darf nicht laenger als 32 Zeichen sein: ".$nachname.", ".$vorname."\n";
		} 
		if(strlen($titelpre)>64)
		{
			$error_log = "Titelpre darf nicht laenger als 64 Zeichen sein: ".$nachname.", ".$vorname."\n";
		}
		if(strlen($nachname)>64)
		{
			$error_log = "Nachname darf nicht laenger als 64 Zeichen sein: ".$nachname.", ".$vorname."\n";
		}
		if($nachname=='' || is_null($nachname))
		{
			$error_log = "Nachname muss eingegeben werden: ".$ext_id_person.", ".$nachname.", ".$vorname."\n";
		}		
		if(strlen($vorname)>32)
		{
			$error_log = "Vorname darf nicht laenger als 32 Zeichen sein: ".$nachname.", ".$vorname."\n";
		}
		if(strlen($vornamen)>128)
		{
			$error_log = "Vornamen darf nicht laenger als 128 Zeichen sein: ".$nachname.", ".$vorname."\n";
		}
		if(strlen($gebort)>128)
		{
			$error_log = "Geburtsort darf nicht laenger als 128 Zeichen sein: ".$nachname.", ".$vorname."\n";
		}
		if($foto!='' && !is_numeric($foto))
		{
			$error_log = "FotoOID ist ungueltig.\n";
		}
		if(strlen($homepage)>256)
		{
			$error_log = "Homepage darf nicht laenger als 256 Zeichen sein: ".$nachname.", ".$vorname."\n";
		}
		if(strlen($svnr)>10)
		{
			$error_log = "SVNR darf nicht laenger als 10 Zeichen sein: ".$nachname.", ".$vorname."\n";
		}
		if(strlen($ersatzkennzeichen)>10)
		{
			$error_log = "Ersatzkennzeichen darf nicht laenger als 10 Zeichen sein: ".$nachname.", ".$vorname."\n";
		}
		if(strlen($familienstand)>1)
		{
			$error_log = "Familienstand ist ungueltig: ".$nachname.", ".$vorname."\n";
		}
		if($anzahlkinder!='' && !is_numeric($anzahlkinder))
		{
			$error_log = "Anzahl der Kinder ist ungueltig: ".$nachname.", ".$vorname."\n";
		}
		if($ext_id_person!='' && !is_numeric($ext_id_person))
		{
			$error_log = "Ext_ID_Person ist keine gueltige Zahl: ".$nachname.", ".$vorname."\n";
		}
		if(strlen($geschlecht)>1)
		{
			$error_log = "Geschlechtsangabe ungültig: ".$nachname.", ".$vorname."\n";
		}
		if(strlen($geburtsnation)>3)
		{
			$error_log = "Geburtsnation darf nicht laenger als 3 Zeichen sein: ".$nachname.", ".$vorname."\n";
		}
		if(strlen($staatsbuergerschaft)>3)
		{
			$error_log = "Staatsbuergerschaft darf nicht laenger als 3 Zeichen sein: ".$nachname.", ".$vorname."\n";
		}
		if($geschlecht!='m' && $geschlecht!='w')
		{
			$error_log = "Geschlecht muß entweder w oder m sein!: ".$nachname.", ".$vorname."\n";
		}		
		if(strlen($student_uid)>16)
		{
			$error_log = 'UID darf nicht laenger als 16 Zeichen sein. : '.$nachname.", ".$vorname."\n";
		}
		/*if($student_uid == '')
		{
			$error_log = 'UID muss eingegeben werden '.$nachname.", ".$vorname."\n";
		}*/
		if(strlen($alias)>256)
		{
			$error_log = 'Alias darf nicht laenger als 256 Zeichen sein '.$nachname.", ".$vorname."\n";
		}
		/*if(!is_numeric($person_id))
		{
			$error_log = 'person_id muss eine gueltige Zahl sein '.$nachname.", ".$vorname."\n";
		}*/
		if(strlen($matrikelnr)>15)
		{
			$error_log = 'Matrikelnummer darf nicht laenger als 15 Zeichen sein '.$nachname.", ".$vorname."\n";
		}		
		if(!is_numeric($berufstaetigkeit_code))
		{
			$error_log = 'berufstaetigkeit_code muss eine gueltige Zahl sein '.$nachname.", ".$vorname."\n";
		}
		if(!is_numeric($zgv_code) && $zgv_code!='')
		{
			$error_log = 'zgv_code muss eine gueltige Zahl sein '.$nachname.", ".$vorname."\n";
		}
		if(strlen($zgvort)>64)
		{
			$error_log = 'zgvort darf nicht laenger als 64 Zeichen sein:'.$zgvort." bei ".$nachname.", ".$vorname."\n";
		}
		if(!is_numeric($zgvmas_code) && $zgvmas_code!='')
		{
			$error_log='zgvmas_code muss eine gueltige Zahl sein. '.$nachname.", ".$vorname."\n";
		}
		if(strlen($zgvmaort)>64)
		{
			$error_log = 'zgvmaort darf nicht laenger als 64 Zeichen sein:'.$zgvmaort." bei ".$nachname.", ".$vorname."\n";
		}
		if(!is_numeric($punkte))
		{
			$error_log='Punkte muss eine gueltige Zahl sein. '.$nachname.", ".$vorname."\n";
		}
//echo nl2br("\n\nFAS:".$nachname.", ".$vorname.": UID=".$student_uid.", Matrikelnr=".$matrikelnr."\n");
		//Ermittlung der Daten des Reihungstests
		$qry="SELECT student_fk, reihungstest_fk, anmeldedatum FROM student_reihungstest WHERE student_fk='".$row->student_pk."';";
		if($result_rt1 = pg_query($conn_fas, $qry))
		{		
			if($row_rt1=pg_fetch_object($result_rt1))
			{
				$qry="SELECT reihungstest_id FROM public.tbl_reihungstest WHERE ext_id='".$row_rt1->reihungstest_fk."';";
				if($result_rt2 = pg_query($conn, $qry))
				{		
					if($row_rt2=pg_fetch_object($result_rt2))
					{
						$reihungstest_id=$row_rt2->reihungstest_id;
						$anmeldungreihungstest=$row_rt1->anmeldedatum;
					}
					else 
					{
						$error_log.="Reihungstest_id von $row_rt1->reihungstest_fk konnte nicht gefunden werden.\n";	
					}	
				}
				else 
				{
					$error_log.="Reihungstest von $row_rt1->reihungstest_fk wurde nicht gefunden.\n";	
				}
			}
			else 
			{
				$error_log.="Kein Reihungstest von Student $row->familienname, $row->vorname gefunden!\n";	
				$reihungstest_id='';
				$anmeldungreihungstest='';
				$notest++;
			}
		}
		
		//Student aktiv?
		$qry="SELECT * FROM (SELECT status, creationdate, ausbildungssemester_fk FROM student_ausbildungssemester WHERE student_fk= '".$ext_id_student."'  AND
		studiensemester_fk=(SELECT studiensemester_pk FROM studiensemester WHERE aktuell='J') ORDER BY creationdate DESC LIMIT 1) AS abc
		WHERE status IN ('3', '10', '11', '12', '13');";
		$aktiv=false;
		$semester=' ';
		if($resultu = pg_query($conn_fas, $qry))
		{
			if(pg_num_rows($resultu)>0)
			{
				if($rowu=pg_fetch_object($resultu))
				{	
					$aktiv=true;
					$qry="SELECT * FROM ausbildungssemester WHERE ausbildungssemester_pk='".$rowu->ausbildungssemester_fk."';";
					if($resultx = pg_query($conn_fas, $qry))
					{
						if($rowx=pg_fetch_object($resultx))
						{
							$semester=$rowx->semester;	
						}
					}
				}
			}
			else 
			{
				$aktiv=false;
			}
		}
		else
		{
			$error=true;
			$error_log.='Fehler beim Abfragen des aktuellen Status bei student_pk: '.$row->student_pk;
			echo nl2br('Fehler beim Abfragen des aktuellen Status bei student_pk: '.$row->student_pk);	
		}
		//Start der Transaktion
		pg_query($conn,'BEGIN;');
		
		//Reihenfolge: person - prestudent - benutzer - student 
		
		//insert oder update bei person?
		$qry="SELECT person_id FROM public.tbl_benutzer WHERE uid='$row->uid'";

		if($resultu = pg_query($conn, $qry))
		{
			if(pg_num_rows($resultu)>0 && $row->uid!='') //wenn dieser eintrag schon vorhanden ist
			{
				if($rowu=pg_fetch_object($resultu))
				{
					//update
					$person_id=$rowu->person_id;
					$new_person=false;
				}
				else 
				{
					$error=true;
					$error_log.="benutzer von $row->uid konnte nicht ermittelt werden\n";
				}
			}	
			else 
			{
				$qry="SELECT person_fas, person_portal FROM sync.tbl_syncperson WHERE person_fas='$row->person_pk'";
				if($result_sync1 = pg_query($conn, $qry))
				{
					if(pg_num_rows($result_sync1)>0) //wenn dieser eintrag schon vorhanden ist
					{
						if($row_sync1=pg_fetch_object($result_sync1))
						{ 
							//update
							$person_id=$row_sync1->person_portal;
							$new_person=false;
						}
						else 
						{
							$error=true;
							$error_log.="Person von $row->person_pk konnte nicht ermittelt werden\n";
						}
					}
					else
					{
						//vergleich svnr und ersatzkennzeichen
						$qry="SELECT person_id FROM public.tbl_person 
							WHERE ('$row->svnr' is not null AND '$row->svnr'<> '' AND svnr = '$row->svnr') 
								OR ('$row->ersatzkennzeichen' is not null AND '$row->ersatzkennzeichen' <> '' AND ersatzkennzeichen = '$row->ersatzkennzeichen')";
						if($resultz = pg_query($conn, $qry))
						{
							if(pg_num_rows($resultz)>0) //wenn dieser eintrag schon vorhanden ist
							{
								if($rowz=pg_fetch_object($resultz))
								{
									$new_person=false;
									$person_id=$rowz->person_id;
								}
								else 
								{
									$error=true;
									$error_log.="Person mit SVNR: $row->svnr bzw. Ersatzkennzeichen: $row->ersatzkennzeichen konnte nicht ermittelt werden (".pg_num_rows($resultz).")\n";
								}
							}
							else 
							{
								//insert
								$new_person=true;
							}
						}		
					}
				}					
			}	
		}
		if($new_person)
		{
			//insert person
			$qry = 'INSERT INTO public.tbl_person (sprache, anrede, titelpost, titelpre, nachname, vorname, vornamen, gebdatum, gebort, gebzeit, foto, anmerkungen, homepage, svnr, ersatzkennzeichen, familienstand, anzahlkinder, aktiv, insertamum, insertvon, updateamum, updatevon, geschlecht, geburtsnation, staatsbuergerschaft, ext_id) VALUES('
					.myaddslashes($sprache).','.
					myaddslashes($anrede).','.
					myaddslashes($titelpost).','.
				        myaddslashes($titelpre).','.
				        myaddslashes($nachname).','.
				        myaddslashes($vorname).','.
				        myaddslashes($vornamen).','.
				        myaddslashes($gebdatum).','.
				        myaddslashes($gebort).','.
				        myaddslashes($gebzeit).','.
				        myaddslashes($foto).','.
				        myaddslashes($anmerkungen).','.
				        myaddslashes($homepage).','.
				        myaddslashes($svnr).','.
				        myaddslashes($ersatzkennzeichen).','.
				        myaddslashes($familienstand).','.
				        myaddslashes($anzahlkinder).','.
				        'true, '.
				        myaddslashes($insertamum).','.
				        myaddslashes($insertvon).','.
				        myaddslashes($updateamum).','.
				        myaddslashes($updatevon).','.
				        myaddslashes($geschlecht).','.
				        myaddslashes($geburtsnation).','.
				        myaddslashes($staatsbuergerschaft).','.
				        myaddslashes($ext_id_person).');';
				        $ausgabe_person="Person ".$nachname." ".$vorname." eingefügt.\n";
		}
		else 
		{
			//update person
			//person_id auf gueltigkeit pruefen
			if(!is_numeric($person_id))
			{				
				$error=true;
				$error_log.= 'person_id muss eine gueltige Zahl sein: '.$nachname;
			}
			
			//update nur wenn änderungen gemacht
			$qry="SELECT * FROM public.tbl_person WHERE person_id='$person_id';";
			if($result1 = pg_query($conn, $qry))
			{
				while($row1 = pg_fetch_object($result1))
				{
					$updatep=false;			
					if($row1->sprache!=$sprache) 
					{
						$updatep=true;
						if(strlen(trim($ausgabe_person))>0)
						{
							$ausgabe_person.=", Sprache: '".$sprache."' (statt '".$row1->sprache."')";
						}
						else
						{
							$ausgabe_person="Sprache: '".$sprache."' (statt '".$row1->sprache."')";
						}
					}
					if($row1->anrede!=$anrede)
					{
						$updatep=true;
						if(strlen(trim($ausgabe_person))>0)
						{
							$ausgabe_person.=", Anrede: '".$anrede."' (statt '".$row1->anrede."')";
						}
						else
						{
							$ausgabe_person="Anrede: '".$anrede."' (statt '".$row1->anrede."')";
						}
					}
					if($row1->titelpost!=$titelpost)
					{
						$updatep=true;
						if(strlen(trim($ausgabe_person))>0)
						{
							$ausgabe_person.=", Postnomentitel: '".$titelpost."' (statt '".$row1->titelpost."')";
						}
						else
						{
							$ausgabe_person="Postnomentitel: '".$titelpost."' (statt '".$row1->titelpost."')";
						}
					}
					if($row1->titelpre!=$titelpre)
					{
						$updatep=true;
						if(strlen(trim($ausgabe_person))>0)
						{
							$ausgabe_person.=", Prenomentitel: '".$titelpre."' (statt '".$row1->titelpre."')";
						}
						else
						{
							$ausgabe_person="Prenomentitel: '".$titelpre."' (statt '".$row1->titelpre."')";
						}
					}
					if($row1->nachname!=$nachname)
					{
						$updatep=true;
						if(strlen(trim($ausgabe_person))>0)
						{
							$ausgabe_person.=", Nachname: '".$nachname."' (statt '".$row1->nachname."')";
						}
						else
						{
							$ausgabe_person=" Nachname: '".$nachname."' (statt '".$row1->nachname."')";
						}
					}
					if($row1->vorname!=$vorname)
					{
						$updatep=true;
						if(strlen(trim($ausgabe_person))>0)
						{
							$ausgabe_person.=", Vorname: '".$vorname."' (statt '".$row1->vorname."')";
						}
						else
						{
							$ausgabe_person="Vorname: '".$vorname."' (statt '".$row1->vorname."')";
						}
					}
					if($row1->vornamen!=$vornamen)
					{
						$updatep=true;
						if(strlen(trim($ausgabe_person))>0)
						{
							$ausgabe_person.=", Vornamen: '".$vornamen."' (statt '".$row1->vornamen."')";
						}
						else
						{
							$ausgabe_person="Vornamen: '".$vornamen."' (statt '".$row1->vornamen."')";
						}
					}
					if($row1->gebdatum!=$gebdatum)
					{
						$updatep=true;
						if(strlen(trim($ausgabe_person))>0)
						{
							$ausgabe_person.=", Geburtsdatum: '".$gebdatum."' (statt '".$row1->gebdatum."')";
						}
						else
						{
							$ausgabe_person="Geburtsdatum: '".$gebdatum."' (statt '".$row1->gebdatum."')";
						}
					}
					if($row1->gebort!=$gebort)
					{
						$updatep=true;
						if(strlen(trim($ausgabe_person))>0)
						{
							$ausgabe_person.=", Geburtsort: '".$gebort."' (statt '".$row1->gebort."')";
						}
						else
						{
							$ausgabe_person="Geburtsort: '".$gebort."' (statt '".$row1->gebort."')";
						}
					}
					if($row1->svnr!=$svnr)
					{
						$updatep=true;
						if(strlen(trim($ausgabe_person))>0)
						{
							$ausgabe_person.=", Sozialversicherungsnummer: '".$svnr."' (statt '".$row1->svnr."')";
						}
						else
						{
							$ausgabe_person="Sozialversicherungsnummer: '".$svnr."' (statt '".$row1->svnr."')";
						}
					}
					if($row1->ersatzkennzeichen!=$ersatzkennzeichen)
					{
						$updatep=true;
						if(strlen(trim($ausgabe_person))>0)
						{
							$ausgabe_person.=", Ersatzkennzeichen: '".$ersatzkennzeichen."' (statt '".$row1->ersatzkennzeichen."')";
						}
						else
						{
							$ausgabe_person="Ersatzkennzeichen: '".$ersatzkennzeichen."' (statt '".$row1->ersatzkennzeichen."')";
						}
					}
					if($row1->familienstand!=$familienstand)
					{
						$updatep=true;
						if(strlen(trim($ausgabe_person))>0)
						{
							$ausgabe_person.=", Familienstand: '".$familienstand."' (statt '".$row1->familienstand."')";
						}
						else
						{
							$ausgabe_person="Familienstand: '".$familienstand."' (statt '".$row1->familienstand."')";
						}
					}
					if($row1->anzahlkinder!=$anzahlkinder)
					{
						$updatep=true;
						if(strlen(trim($ausgabe_person))>0)
						{
							$ausgabe_person.=", Anzahl der Kinder: '".$anzahlkinder."' (statt '".$row1->anzahlkinder."')";
						}
						else
						{
							$ausgabe_person="Anzahl der Kinder: '".$anzahlkinder."' (statt '".$row1->anzahlkinder."')";
						}
					}
					if($row1->geburtsnation!=$geburtsnation)
					{
						$updatep=true;
						if(strlen(trim($ausgabe_person))>0)
						{
							$ausgabe_person.=", Geburtsnation: '".$geburtsnation."' (statt '".$row1->geburtsnation."')";
						}
						else
						{
							$ausgabe_person="Geburtsnation: '".$geburtsnation."' (statt '".$row1->geburtsnation."')";
						}
					}
					if($row1->geschlecht!=$geschlecht)
					{
						$updatep=true;
						if(strlen(trim($ausgabe_person))>0)
						{
							$ausgabe_person.=", Geschlecht: '".$geschlecht."' (statt '".$row1->geschlecht."')";
						}
						else
						{
							$ausgabe_person="Geschlecht: '".$geschlecht."' (statt '".$row1->geschlecht."')";
						}
					}
					if($row1->staatsbuergerschaft!=$staatsbuergerschaft)
					{
						$updatep=true;
						if(strlen(trim($ausgabe_person))>0)
						{
							$ausgabe_person.=", Staatsbürgerschaft: '".$staatsbuergerschaft."' (statt '".$row1->staatsbuergerschaft."')";
						}
						else
						{
							$ausgabe_person="Staatsbürgerschaft: '".$staatsbuergerschaft."' (statt '".$row1->staatsbuergerschaft."')";
						}
					}
					if($updatep)
					{
						$qry = 'UPDATE public.tbl_person SET'.
						       ' sprache='.myaddslashes($sprache).','.
						       ' anrede='.myaddslashes($anrede).','.
						       ' titelpost='.myaddslashes($titelpost).','.
						       ' titelpre='.myaddslashes($titelpre).','.
						       ' nachname='.myaddslashes($nachname).','.
						       ' vorname='.myaddslashes($vorname).','.
						       ' vornamen='.myaddslashes($vornamen).','.
						       ' gebdatum='.myaddslashes($gebdatum).','.
						       ' gebort='.myaddslashes($gebort).','.
						       //' gebzeit='.myaddslashes($gebzeit).','.
						       //' foto='.myaddslashes($foto).','.
						       //' homepage='.myaddslashes($homepage).','.
						       ' svnr='.myaddslashes($svnr).','.
						       ' ersatzkennzeichen='.myaddslashes($ersatzkennzeichen).','.
						       ' familienstand='.myaddslashes($familienstand).','.
						       ' anzahlkinder='.myaddslashes($anzahlkinder).','.
						       ' aktiv=true, '.
						       ' geschlecht='.myaddslashes($geschlecht).','.
						       ' geburtsnation='.myaddslashes($geburtsnation).','.
						       ' staatsbuergerschaft='.myaddslashes($staatsbuergerschaft).','.
				        		       " updateamum=now()".','.
				        		       " updatevon=".myaddslashes($updatevon).','.
						       ' ext_id='.myaddslashes($ext_id_person).
						       ' WHERE person_id='.myaddslashes($person_id).';';
						       $ausgabe_person="Änderungen bei Person ".$nachname." ".$vorname.": ".$ausgabe_person."\n";
					}
				}
			}
		}
		if(@pg_query($conn,$qry))
		{
			if($new_person)
			{
				$qry = "SELECT currval('public.tbl_person_person_id_seq') AS id;";
				if($rowu=pg_fetch_object(pg_query($conn,$qry)))
					$person_id=$rowu->id;
				else
				{					
					$error=true;
					$error_log.='Person-Sequence konnte nicht ausgelesen werden';
				}
				$anzahl_person_insert++;
			}			
			else 
			{
				if($updatep)
				{
					$anzahl_person_update++;
				}
			}
			//Eintrag Synctabelle
			$qryz="SELECT person_fas FROM sync.tbl_syncperson WHERE person_fas='$row->person_pk' AND person_portal='$person_id'";
			if($resultz = pg_query($conn, $qryz))
			{
				if(pg_num_rows($resultz)==0) //wenn dieser eintrag noch nicht vorhanden ist
				{
					$qry='INSERT INTO sync.tbl_syncperson (person_fas, person_portal)'.
						'VALUES ('.$row->person_pk.', '.$person_id.');';
					$resulti = pg_query($conn, $qry);
				}
			}
			$anzahl_person_gesamt++;
		}
		else
		{			
			$error=true;
			$error_log.='Fehler beim Speichern des Person-Datensatzes:'.$nachname.' '.$qry."\n".pg_errormessage($conn)."\n";
			$ausgabe_person='';
		}
		
		if(!$error)
		{
			//Weitere Reihenfolge: prestudent - benutzer - student
			
			//Prestudent schon vorhanden?
			$qry="SELECT prestudent_id FROM public.tbl_prestudent WHERE ext_id='".$row->student_pk."';";
			if($resultu = pg_query($conn, $qry))
			{
				if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
				{
					if($rowu=pg_fetch_object($resultu))
					{
						$prestudent_id=$rowu->prestudent_id;
						$new_prestudent=false;		
					}
					else 
					{
						$new_prestudent=true;
					}
				}
				else 
				{
					$new_prestudent=true;
				}
			}
			else
			{
				$error=true;
				$error_log.='Fehler beim Zugriff auf Tabelle tbl_prestudent bei student_pk: '.$row->student_pk.pg_errormessage($conn)."\n";	
			}
			
			//Studiengang ermitteln
			$qry="SELECT studiengang_kz FROM public.tbl_studiengang WHERE ext_id='".$row->studiengang_fk."';";
			if($resultu = pg_query($conn, $qry))
			{
				if(pg_num_rows($resultu)>0) 
				{
					if($rowu=pg_fetch_object($resultu))
					{
						$studiengang_kz=$rowu->studiengang_kz;
					}
				}
				else 
				{
					echo nl2br("\n".$qry."\nSTUDIENGANG NICHT GEFUNDEN!!! \n");
					$error_log.="\n".$qry."\nSTUDIENGANG NICHT GEFUNDEN!!! \n";
					$error=true;
				}
			}
			else 
			{
				echo nl2br("\n".$qry."\nFehler beim Zugriff auf tbl_studiengang\n".pg_errormessage($conn)."\n");
				$error_log.="\n".$qry."\nFehler beim Zugriff auf tbl_studiengang\n".pg_errormessage($conn)."\n";
				$error=true;
			}
			if($row->aufmerksamdurch=='1')		$aufmerksamdurch_kurzbz='k.A.';
			else if($row->aufmerksamdurch=='2')	$aufmerksamdurch_kurzbz='Internet';
			else if($row->aufmerksamdurch=='3')	$aufmerksamdurch_kurzbz='Zeitungen';
			else if($row->aufmerksamdurch=='4')	$aufmerksamdurch_kurzbz='Werbung';
			else if($row->aufmerksamdurch=='5')	$aufmerksamdurch_kurzbz='Mundpropaganda';
			else if($row->aufmerksamdurch=='6')	$aufmerksamdurch_kurzbz='FH-Führer';
			else if($row->aufmerksamdurch=='7')	$aufmerksamdurch_kurzbz='BEST Messe';
			else if($row->aufmerksamdurch=='8')	$aufmerksamdurch_kurzbz='Partnerfirma';
			else if($row->aufmerksamdurch=='9')	$aufmerksamdurch_kurzbz='Schule';
			else if($row->aufmerksamdurch=='10')	$aufmerksamdurch_kurzbz='Bildungstelefon';
			else if($row->aufmerksamdurch=='11')	$aufmerksamdurch_kurzbz='TGM';
			else if($row->aufmerksamdurch=='12')	$aufmerksamdurch_kurzbz='Abgeworben';
			else if($row->aufmerksamdurch=='13')	$aufmerksamdurch_kurzbz='Technikum Wien';
			else if($row->aufmerksamdurch=='14')	$aufmerksamdurch_kurzbz='Aussendungen';
			else if($row->aufmerksamdurch=='15')	$aufmerksamdurch_kurzbz='offene Tür';
			else $aufmerksamdurch_kurzbz='k.A.';
			
			
			if($new_prestudent)
			{
				//insert prestudent
				
				$qry = 'INSERT INTO public.tbl_prestudent (aufmerksamdurch_kurzbz, person_id, studiengang_kz, berufstaetigkeit_code, zgv_code, zgvort, zgvdatum, zgvmas_code, zgvmaort, zgvmadatum, facheinschlberuf, reihungstest_id, punkte, anmeldungreihungstest, reihungstestangetreten, anmerkung, insertamum, insertvon, updateamum, updatevon, ext_id) VALUES('.
					myaddslashes($aufmerksamdurch_kurzbz).', '.
					myaddslashes($person_id).', '.
					myaddslashes($studiengang_kz).', '.
					myaddslashes($berufstaetigkeit_code).', '.
					myaddslashes($zgv_code).', '.
					myaddslashes($zgvort).', '.
					myaddslashes($zgvdatum).', '.
					myaddslashes($zgvmas_code).', '.
					myaddslashes($zgvmaort).', '.
					myaddslashes($zgvmadatum).', '.
					($facheinschlberuf?'true':'false').', '.
					myaddslashes($reihungstest_id).', '.
					myaddslashes($punkte).', '.
					myaddslashes($anmeldungreihungstest).', '.
					($reihungstestangetreten?'true':'false').', '.
					myaddslashes($anmerkung).', '.
					myaddslashes($insertamum).','.
					"'SYNC', ".
					myaddslashes($updateamum).','.
					"'SYNC', ".
					myaddslashes($ext_id_pre).');';
					$ausgabe_pre="Prestudent:  ID:".$person_id.", ".$nachname." eingefügt.\n";
			}
			else 
			{
				//update prestudent
				
				//prestudent_id auf gueltigkeit pruefen
				if(!is_numeric($prestudent_id))
				{				
					$error=true;
					$error_log.= 'prestudent_id muss eine gueltige Zahl sein';
				}
				
				//update nur wenn änderungen gemacht
				$qry="SELECT * FROM public.tbl_prestudent WHERE prestudent_id='$prestudent_id';";
				if($results = pg_query($conn, $qry))
				{
					while($rows = pg_fetch_object($results))
					{
						$updater=false;			
						if($rows->aufmerksamdurch_kurzbz!=$aufmerksamdurch_kurzbz)
						{
							$updater=true;
							if(strlen(trim($ausgabe_pre))>0)
							{
								$ausgabe_pre.=", Aufmerksamdurch: '".$aufmerksamdurch_kurzbz."'";
							}
							else
							{
								$ausgabe_pre="Aufmerksamdurch: '".$aufmerksamdurch_kurzbz."'";
							}
						}
						if($rows->person_id!=$person_id)
						{
							$updater=true;
							if(strlen(trim($ausgabe_pre))>0)
							{
								$ausgabe_pre.=", PersonID: '".$person_id."'";
							}
							else
							{
								$ausgabe_pre="PersonID: '".$person_id."'";
							}
						}
						if($rows->studiengang_kz!=$studiengang_kz)
						{
							$updater=true;
							if(strlen(trim($ausgabe_pre))>0)
							{
								$ausgabe_pre.=", Studiengang: '".$studiengang_kz."'";
							}
							else
							{
								$ausgabe_pre="Studiengang: '".$studiengang_kz."'";
							}
						}
						if($rows->berufstaetigkeit_code!=$berufstaetigkeit_code)
						{
							$updater=true;
							if(strlen(trim($ausgabe_pre))>0)
							{
								$ausgabe_pre.=", Berufstätigkeitscode: '".$berufstaetigkeit_code."'";
							}
							else
							{
								$ausgabe_pre="Berufstätigkeitscode: '".$berufstaetigkeit_code."'";
							}
						}
						if($rows->zgv_code!=$zgv_code)
						{
							$updater=true;
							if(strlen(trim($ausgabe_pre))>0)
							{
								$ausgabe_pre.=", ZGV-Code: '".$zgv_code."'";
							}
							else
							{
								$ausgabe_pre="ZGV-Code: '".$zgv_code."'";
							}
						}
						if($rows->zgvort!=$zgvort)
						{
							$updater=true;
							if(strlen(trim($ausgabe_pre))>0)
							{
								$ausgabe_pre.=", ZGV-Ort: '".$zgvort."'";
							}
							else
							{
								$ausgabe_pre="ZGV-Ort: '".$zgvort."'";
							}
						}
						if($rows->zgvdatum!=$zgvdatum)
						{
							$updater=true;
							if(strlen(trim($ausgabe_pre))>0)
							{
								$ausgabe_pre.=", ZGV-Datum: '".$zgvdatum."'";
							}
							else
							{
								$ausgabe_pre="ZGV-Datum: '".$zgvdatum."'";
							}
						}
						if($rows->zgvmas_code!=$zgvmas_code)
						{
							$updater=true;
							if(strlen(trim($ausgabe_pre))>0)
							{
								$ausgabe_pre.=", ZGV-Mastercode: '".$zgvmas_code."'";
							}
							else
							{
								$ausgabe_pre="ZGV-Mastercode: '".$zgvmas_code."'";
							}
						}
						if($rows->zgvmaort!=$zgvmaort)
						{
							$updater=true;
							if(strlen(trim($ausgabe_pre))>0)
							{
								$ausgabe_pre.=", ZGV-Masterort: '".$zgvmaort."'";
							}
							else
							{
								$ausgabe_pre="ZGV-Masterort: '".$zgvmaort."'";
							}
						}
						if($rows->zgvmadatum!=$zgvmadatum)
						{
							$updater=true;
							if(strlen(trim($ausgabe_pre))>0)
							{
								$ausgabe_pre.=", ZGV-Masterdatum: '".$zgvmadatum."'";
							}
							else
							{
								$ausgabe_pre="ZGV-Masterdatum: '".$zgvmadatum."'";
							}
						}
						if($rows->facheinschlberuf!=($facheinschlberuf?'t':'f'))
						{
							$updater=true;
							if(strlen(trim($ausgabe_pre))>0)
							{
								$ausgabe_pre.=", Facheinschl.Berufstätig: '".($facheinschlberuf?'true':'false')."'";
							}
							else
							{
								$ausgabe_pre="Facheinschl.Berufstätig: '".($facheinschlberuf?'true':'false')."'";
							}
						}
						if($rows->reihungstest_id!=$reihungstest_id)
						{
							$updater=true;
							if(strlen(trim($ausgabe_pre))>0)
							{
								$ausgabe_pre.=", ReihungstestID: '".$reihungstest_id."'";
							}
							else
							{
								$ausgabe_pre="ReihungstestID: '".$reihungstest_id."'";
							}
						}
						if($rows->punkte!=$punkte)
						{
							$updater=true;
							if(strlen(trim($ausgabe_pre))>0)
							{
								$ausgabe_pre.=", Punkte Reihungstest: '".$punkte."'";
							}
							else
							{
								$ausgabe_pre="Punkte Reihungstest: '".$punkte."'";
							}
						}
						if($rows->anmeldungreihungstest!=$anmeldungreihungstest)
						{
							$updater=true;
							if(strlen(trim($ausgabe_pre))>0)
							{
								$ausgabe_pre.=", Anmeldung Reihungstest: '".$anmeldungreihungstest."'";
							}
							else
							{
								$ausgabe_pre="Anmeldung Reihungstest: '".$anmeldungreihungstest."'";
							}
						}						
						if($rows->reihungstestangetreten!=($reihungstestangetreten?'t':'f'))
						{
							$updater=true;
							if(strlen(trim($ausgabe_pre))>0)
							{
								$ausgabe_pre.=", Angetreten zum Reihungstest: '".($reihungstestangetreten?'true':'false')."'";
							}
							else
							{
								$ausgabe_pre="Angetreten zum Reihungstest: '".($reihungstestangetreten?'true':'false')."'";
							}
						}
						if($rows->anmerkung!=$anmerkung)
						{
							$updater=true;
							if(strlen(trim($ausgabe_pre))>0)
							{
								$ausgabe_pre.=", Anmerkung: '".$anmerkung."'";
							}
							else
							{
								$ausgabe_pre="Anmerkung: '".$anmerkung."'";
							}
						}
						if($rows->ext_id!=$ext_id_pre)
						{
							$updater=true;
							if(strlen(trim($ausgabe_pre))>0)
							{
								$ausgabe_pre.=", EXT_ID: '".$ext_id."'";
							}
							else
							{
								$ausgabe_pre="EXT_ID: '".$ext_id."'";
							}
						}
						if($updater)
						{
							$qry = 'UPDATE public.tbl_prestudent SET'.
							       ' aufmerksamdurch_kurzbz='.myaddslashes($aufmerksamdurch_kurzbz).','.
							       ' person_id='.myaddslashes($person_id).','.
							       ' studiengang_kz='.myaddslashes($studiengang_kz).','.
							       ' berufstaetigkeit_code='.myaddslashes($berufstaetigkeit_code).','.
							       ' zgv_code='.myaddslashes($zgv_code).','.
							       ' zgvort='.myaddslashes($zgvort).','.
							       ' zgvdatum='.myaddslashes($zgvdatum).','.
							       ' zgvmas_code='.myaddslashes($zgvmas_code).','.
							       ' zgvmaort='.myaddslashes($zgvmaort).','.
							       ' zgvmadatum='.myaddslashes($zgvmadatum).','.
							       ' facheinschlberuf='.($facheinschlberuf?'true':'false').','.
							       ' reihungstest_id='.myaddslashes($reihungstest_id).','.
							       ' punkte='.myaddslashes($punkte).','.
							       ' anmeldungreihungstest='.myaddslashes($anmeldungreihungstest).','.
							       ' reihungstestangetreten='.($reihungstestangetreten?'true':'false').','.
							       ' anmerkung='.myaddslashes($anmerkung).','.
					        		       " updateamum=now()".','.
					        		       " updatevon=".myaddslashes($updatevon).','.
							       ' ext_id='.myaddslashes($ext_id_pre).
							       ' WHERE prestudent_id='.myaddslashes($prestudent_id).';';
							       $ausgabe_pre="Änderungen bei Prestudent mit person_id '".$person_id."', ('".$prestudent_id."'), ".$nachname.": ".$ausgabe_pre."\n";
						}
					}
				}
			}
			
			if(pg_query($conn,$qry))
			{
//echo nl2br("person_id=".$person_id."\nPrestudent: ".$qry."\n");
				if($new_prestudent)
				{
					$qry = "SELECT currval('public.tbl_prestudent_prestudent_id_seq') AS id;";
					if($rowu=pg_fetch_object(pg_query($conn,$qry)))
					{
						$prestudent_id=$rowu->id;
					}
					else
					{					
						$error=true;
						$error_log.='Prestudent-Sequence konnte nicht ausgelesen werden';
					}
					$anzahl_pre_insert++;
				}			
				else 
				{
					if($updater)
					{
						$anzahl_pre_update++;
					}
				}
				$anzahl_pre_gesamt++;
			}
			else
			{			
				$error=true;
				$error_log.='Fehler beim Speichern des Prestudent-Datensatzes:'.$nachname." \n".$qry."\n".pg_errormessage($conn)."\n";
				$ausgabe_pre='';
			}
									
			if(!$error)
			{
				//Weitere Reihenfolge: benutzer, student
				
				

				//Gruppenverband ermitteln
				$qry="SELECT fas_function_find_verband_from_student(".$ext_id_student.") AS verband,
					fas_function_find_jahrgang_from_student(".$ext_id_student.") AS jahrgang,
					fas_function_find_gruppe_from_student(".$ext_id_student.") AS gruppe;";
				if($resultu = pg_query($conn_fas, $qry))
				{
					if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
					{
						if($rowu=pg_fetch_object($resultu))
						{
							if($semester==' ')
							{
								$semester=$rowu->jahrgang;
							}
							if ($rowu->verband==null)
							{
								$verband=' ';
							}
							else 
							{
								$verband=$rowu->verband;
							}
							if($rowu->gruppe==null)
							{
								$gruppe=' ';
							}
							else 
							{
								$gruppe=$rowu->gruppe;
							}
							if(!$aktiv && $student_uid<>'' && $student_uid!=null && isset($student_uid) && $matrikelnr<>'' && $matrikelnr!=null && isset($matrikelnr))
							{
								$semester='10';
							}
							if($semester!=null AND $verband!=null AND $gruppe!=null)
							{
								$qry="SELECT * from public.tbl_lehrverband WHERE studiengang_kz=".myaddslashes($studiengang_kz)." AND semester=".myaddslashes($semester)." AND verband=".myaddslashes($verband)." AND gruppe=".myaddslashes($gruppe).";";
								if($resultg = pg_query($conn, $qry))
								{
									if(pg_num_rows($resultg)<1)
									{
										$qry="INSERT INTO public.tbl_lehrverband (studiengang_kz, semester, verband, gruppe, aktiv, bezeichnung, ext_id) 
										VALUES(".myaddslashes($studiengang_kz).", ".
										myaddslashes($semester).", ".
										myaddslashes($verband).", ".
										myaddslashes($gruppe).", ".
										"true, null , null );";
										pg_query($conn, $qry);
										
									}
								}
								$qry="SELECT * from public.tbl_lehrverband WHERE studiengang_kz=".myaddslashes($studiengang_kz)." AND semester=".myaddslashes($semester)." AND verband=".myaddslashes($verband)." AND gruppe=' ';";
								if($resultg = pg_query($conn, $qry))
								{
									if(pg_num_rows($resultg)<1)
									{
										$qry="INSERT INTO public.tbl_lehrverband (studiengang_kz, semester, verband, gruppe, aktiv, bezeichnung, ext_id) 
										VALUES(".myaddslashes($studiengang_kz).", ".
										myaddslashes($semester).", ".
										myaddslashes($verband).", ".
										"' ', true, null, null);";
										pg_query($conn, $qry);
									}
								}
								$qry="SELECT * from public.tbl_lehrverband WHERE studiengang_kz=".myaddslashes($studiengang_kz)." AND semester=".myaddslashes($semester)." AND  verband=' ' AND gruppe=' ';";
								if($resultg = pg_query($conn, $qry))
								{
									if(pg_num_rows($resultg)<1)
									{
										$qry="INSERT INTO public.tbl_lehrverband (studiengang_kz, semester, verband, gruppe, aktiv, bezeichnung, ext_id) 
										VALUES(".myaddslashes($studiengang_kz).", ".
										myaddslashes($semester).", ".
										"' ', ' ', true, null, null);";
										pg_query($conn, $qry);
									}
								}
							}
						}
					}
				}
				//prestudentrolle
//echo nl2br("Aktiv=".($aktiv?'true':'false').", Semester=".$semester.", Verband=".$verband.", Gruppe=".$gruppe."\n");				
				$qry="SELECT * FROM student_ausbildungssemester where student_fk='$ext_id_student';";
				if($resultru = pg_query($conn_fas, $qry))
				{
					while($rowru=pg_fetch_object($resultru))
					{
						$qry="SELECT semester FROM ausbildungssemester WHERE ausbildungssemester_pk='$rowru->ausbildungssemester_fk'";
						if($resultr = pg_query($conn_fas, $qry))
						{
							while($rowr=pg_fetch_object($resultr))
							{
								$ausbildungssemester=$rowr->semester;
								$date = date('Y-m-d', $datum_obj->mktime_fromtimestamp($rowru->creationdate));
								$status=$rowru->status;
								$stm=$rowru->studiensemester_fk;
								$qry="SELECT * FROM public.tbl_prestudentrolle WHERE prestudent_id='$prestudent_id' AND rolle_kurzbz='$rolle_kurzbz[$status]' AND studiensemester_kurzbz='$studiensemester_kurzbz[$stm]' AND ausbildungssemester='$ausbildungssemester';";
								if($resultu = pg_query($conn, $qry))
								{
									if(!pg_num_rows($resultu)>0) //wenn dieser eintrag noch nicht vorhanden ist
									{
										$qry="INSERT INTO public.tbl_prestudentrolle (prestudent_id, rolle_kurzbz, studiensemester_kurzbz, ausbildungssemester, datum, insertamum, insertvon, updateamum, updatevon, ext_id) VALUES (".
										"'$prestudent_id', '$rolle_kurzbz[$status]', '$studiensemester_kurzbz[$stm]', '$ausbildungssemester', '$date',now(),'SYNC',now(),'SYNC', '$rowru->student_ausbildungssemester_pk')";
										if(!pg_query($conn, $qry))
										{
											$error_log.="FEHLER bei Eintrag in tbl_prestudentrolle: '$prestudent_id', '$rolle_kurzbz[$status]', '$studiensemester_kurzbz[$stm]', '$ausbildungssemester'.\n".pg_errormessage($conn)."\n";
											$error=true;
										}
										else 
										{
											$ausgabe_prerolle.="    Prestudentrolle für ID ".$person_id." ('".$prestudent_id."'), ".$nachname." angelegt: '".$rolle_kurzbz[$status]."' in Studiensemester '".$studiensemester_kurzbz[$stm]."' mit Ausbildungssemester '".$ausbildungssemester."'.\n";
										}
									}
								}
							}
						}
					}
				}
				if(!$error)
				{
					$ausgabe.=$ausgabe_person;
					$ausgabe_pre.=$ausgabe_prerolle;
					$ausgabe.=$ausgabe_pre;
				}
				if ($semester!=null and $semester!='' and is_numeric($semester) 
				    and $verband!=null and $gruppe!=null)
				{
					//Benutzer schon vorhanden?
					$qry="SELECT uid,person_id,ext_id FROM public.tbl_benutzer WHERE ext_id=".$ext_id_student.";";
					if($resultx = pg_query($conn, $qry))
					{
						if(pg_num_rows($resultx)>0) 
						{	
							if($rowx=pg_fetch_object($resultx))
							{
								$new_benutzer=false;
								$student_uid=$rowx->uid;
								//echo nl2br("new1\n");
							}
						}
						else 
						{
							$qry="SELECT uid, person_id, ext_id FROM public.tbl_benutzer WHERE person_id='".$person_id."' AND uid='".$matrikelnr."';";
							if($resultu = pg_query($conn, $qry))
							{
								if(pg_num_rows($resultu)>0) 
								{
									$student_uid=$matrikelnr;
									$new_benutzer=false;
									//echo nl2br("new2\n");
								}
						
								else 
								{
									$qry="SELECT uid, person_id, ext_id FROM public.tbl_benutzer WHERE person_id='".$person_id."' AND uid='".$student_uid."';";
									if($resultu = pg_query($conn, $qry))
									{
										if(pg_num_rows($resultu)>0) 
										{	
											if($rowu=pg_fetch_object($resultu))
											{
												$qryfas="SELECT * FROM person, student WHERE person_pk=person_fk AND uid='".$student_uid."' AND perskz<>'';";
												if($resultfas = pg_query($conn_fas, $qryfas)) 
												{
													if(pg_num_rows($resultfas)>1) //person mit mehreren studentendatensätzen?
													{
														$qry="SELECT * FROM (SELECT status, creationdate FROM student_ausbildungssemester WHERE student_fk= '".$ext_id_student."'  AND
														studiensemester_fk=(SELECT studiensemester_pk FROM studiensemester WHERE aktuell='J') ORDER BY creationdate DESC LIMIT 1) AS abc
														WHERE status='9';";
														$aktiv9=false;
														if($resulta = pg_query($conn_fas, $qry))
														{
															if(pg_num_rows($resulta)>0)
															{
																$aktiv9=true;
															}
															else 
															{
																$aktiv9=false;
															}
														}
														else
														{
															$error=true;
															$error_log.='Fehler beim Abfragen des aktuellen Status auf Unterbrecher bei student_pk: '.$row->student_pk;
															echo nl2br('Fehler beim Abfragen des aktuellen Status auf Unterbrecher bei student_pk: '.$row->student_pk);	
														}
														
														if(!$aktiv && !$aktiv9) 
														{
															if($rowu->uid==$matrikelnr)
															{
																$new_benutzer=false;
																//echo nl2br("new3\n");
															}
															else 
															{
																$qrychk="SELECT * FROM public.tbl_benutzer WHERE person_id=person_id AND uid='".$matrikelnr."';";
																if($resultchk = pg_query($conn, $qrychk)) 
																{
																	if(pg_num_rows($resultchk)>0)
																	{
																		$new_benutzer=false;
																		//echo nl2br("new4\n");
																	}
																	else 
																	{
																		$new_benutzer=true;
																		$student_uid=$matrikelnr;
																		//echo nl2br("new5\n");
																	}
																}
																else
																{
																	$error=true;
																	$error_log.='Fehler beim Zugriff auf Tabelle tbl_benutzer - person_id: '.$person_id."\n".pg_errormessage($conn)."\n";	
																}
															}
														}
														else 
														{
															$new_benutzer=false;
															//echo nl2br("new6\n");
														}
													}
													else 
													{
														$new_benutzer=false;
														//echo nl2br("new7\n");
													}
												}
											}
											else 
											{
												$new_benutzer=true;
												//echo nl2br("new8\n");
											}
										}
										else 
										{
											$new_benutzer=true;
											//echo nl2br("new9\n");
										}
									}
									else
									{
										$error=true;
										$error_log.='Fehler beim Zugriff auf Tabelle tbl_benutzer bei person_id: '.$person_id."\n".pg_errormessage($conn)."\n";	
									}
								}
							}	
						}
					}
					else
					{
						$error=true;
						$error_log.='Fehler beim Zugriff auf Tabelle tbl_benutzer mit person_id: '.$person_id."\n".pg_errormessage($conn)."\n";	
					}
					
					
					
//echo nl2br("Neu=".$new_benutzer.", UID=".$student_uid."\n");
					//Student schon vorhanden?
					$qry="SELECT student_uid FROM public.tbl_student WHERE ext_id='".$ext_id_student."';";
					if($resultu = pg_query($conn, $qry))
					{
						if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
						{
							if($rowu=pg_fetch_object($resultu))
							{
								$new_student=false;	
								$student_where=" WHERE ext_id=".myaddslashes($ext_id_student).";";	
							}
						}
						else 
						{
							$qry="SELECT student_uid FROM public.tbl_student WHERE matrikelnr='".$matrikelnr."';";
							if($resultu = pg_query($conn, $qry))
							{
								if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
								{
									if($rowu=pg_fetch_object($resultu))
									{	
										$new_student=false;	
										$student_where=" WHERE matrikelnr=".myaddslashes($matrikelnr).";";
									}
								}
								else 
								{
									$new_student=true;
								}
							}
						}
					}
					else
					{
						$error=true;
						$error_log.='Fehler beim Zugriff auf Tabelle tbl_student bei student_pk: '.$ext_id_student."\n".pg_errormessage($conn)."\n";	
					}
//echo nl2br($student_where);
					
					if($new_benutzer)
					{
						//insert benutzer
						$qry = 'INSERT INTO public.tbl_benutzer (uid, person_id, aktiv, alias, insertamum, insertvon, updateamum, updatevon, ext_id) VALUES('.
						myaddslashes($student_uid).', '.
						myaddslashes($person_id).', '.
						($aktiv?'true':'false').', '.
						myaddslashes($alias).', '.
						myaddslashes($insertamum).','.
						"'SYNC'".', '.
						myaddslashes($updateamum).','.
						"'SYNC'".', '.
						myaddslashes($ext_id_benutzer).'); ';
						$ausgabe_benutzer="Benutzer ".$student_uid." ".$alias." eingefügt.\n";	
					}
					else 
					{
						//update benutzer
						//person_id auf gueltigkeit pruefen
						
						if(!is_numeric($person_id))
						{				
							$error=true;
							$text.='person_id muss eine gueltige Zahl sein\n';
							$error_log.= 'person_id muss eine gueltige Zahl sein\n';
						}
						
						
						//update nur wenn änderungen gemacht
						$qry="SELECT * FROM public.tbl_benutzer WHERE person_id='$person_id' AND uid='$student_uid'";
						if($results = pg_query($conn, $qry))
						{
							while($rows = pg_fetch_object($results))
							{
								$updateb=false;
								if($rows->uid!=$student_uid)
								{
									$updateb=true;
									if(strlen(trim($ausgabe_benutzer))>0)
									{
										$ausgabe_benutzer.=", UID: '".$student_uid."' statt('".$rows->uid."')";
									}
									else
									{
										$ausgabe_benutzer="UID: '".$student_uid."' statt('".$rows->uid."')";
									}
								}	
								if($rows->person_id!=$person_id)
								{
									$updateb=true;
									if(strlen(trim($ausgabe_benutzer))>0)
									{
										$ausgabe_benutzer.=", Person_ID: '".$person_id."' statt('".$rows->person_id."')";
									}
									else
									{
										$ausgabe_benutzer="Person_ID: '".$person_id."' statt('".$rows->person_id."')";
									}
								}			
								if($rows->aktiv!=($aktiv?'t':'f'))
								{
									$updateb=true;
									if(strlen(trim($ausgabe_benutzer))>0)
									{
										$ausgabe_benutzer.=", Aktiv: '".($aktiv?'true':'false')."' statt('".($rows->aktiv?'true':'false')."')";
									}
									else
									{
										$ausgabe_benutzer="Aktiv: '".($aktiv?'true':'false')."' statt('".($rows->aktiv?'true':'false')."')";
									}
								}		
								if($rows->ext_id!=$ext_id_benutzer)
								{
									$updateb=true;
									if(strlen(trim($ausgabe_benutzer))>0)
									{
										$ausgabe_benutzer.=", Ext_ID: '".$ext_id_benutzer."' statt('".$rows->ext_id."')";
									}
									else
									{
										$ausgabe_benutzer="Ext_ID: '".$ext_id_benutzer."' statt('".$rows->ext_id."')";
									}
								}
								if($updateb)
								{
									$qry = 'UPDATE public.tbl_benutzer SET'.
									       ' uid='.myaddslashes($student_uid).','.
									       ' person_id='.myaddslashes($person_id).','.
									       ' aktiv='.($aktiv?'true':'false').','.
							        		       " updateamum=now()".','.
							        		       " updatevon=".myaddslashes($updatevon).', '.
							        		       " ext_id=".myaddslashes($ext_id_benutzer).
									       " WHERE person_id='$person_id' AND uid='$student_uid'";
									       $ausgabe_benutzer="Änderungen bei Benutzer ".$student_uid." ".$alias.": ".$ausgabe_benutzer.".\n";
								}
							}
						}
					}
					if(!pg_query($conn,$qry))
					{			
						$error=true;
						$error_log.='Fehler beim Speichern des Benutzer-Datensatzes:'.$nachname.' '.$qry."\n".pg_errormessage($conn)."\n";
						$ausgabe_benutzer='';
					}
					else 
					{
						if($new_benutzer)
						{
							$anzahl_benutzer_insert++;
						}
						else 
						{
							if($updateb)
							{
								$anzahl_benutzer_update++;
							}
						}
						$anzahl_benutzer_gesamt++;
					}
					if(!$error)
					{
						
						if($new_student)
						{
							//insert student
							
							$qry = 'INSERT INTO public.tbl_student (student_uid, matrikelnr, prestudent_id, studiengang_kz, semester, verband, gruppe, insertamum, insertvon, updateamum, updatevon, ext_id) VALUES('.
								myaddslashes($student_uid).', '.
								myaddslashes($matrikelnr).', '.
								myaddslashes($prestudent_id).', '.
								myaddslashes($studiengang_kz).', '.
								myaddslashes($semester).', '.
								myaddslashes($verband).', '.
								myaddslashes($gruppe).', '.
								myaddslashes($insertamum).','.
								"'SYNC'".', '.
								myaddslashes($updateamum).','.
								"'SYNC'".', '.
								myaddslashes($ext_id_student).'); ';
								$ausgabe_student="Student mit ID ".$student_uid.", ".$nachname.", Matrikelnummer:'".$matrikelnr."' eingefügt.\n";
						}
						else 
						{
							//update student
																									
							//update nur wenn änderungen gemacht
							$qry="SELECT * FROM public.tbl_student ".$student_where;
							
							if($results = pg_query($conn, $qry))
							{
								while($rows = pg_fetch_object($results))
								{
									$updates=false;	
									if(trim($rows->student_uid)!=trim($student_uid))
									{
										$updates=true;
										if(strlen(trim($ausgabe_student))>0)
										{
											$ausgabe_student.=", Student_UID: '".$student_uid."' (statt '".trim($rows->student_uid)."')";
										}
										else
										{
											$ausgabe_student="Student_UID: '".$student_uid."' (statt '".trim($rows->student_uid)."')";
										}
									}		
									if(trim($rows->matrikelnr)!=trim($matrikelnr))
									{
										$updates=true;
										if(strlen(trim($ausgabe_student))>0)
										{
											$ausgabe_student.=", Matrikelnummer: '".$matrikelnr."' (statt '".trim($rows->matrikelnr)."')";
										}
										else
										{
											$ausgabe_student="Matrikelnummer: '".$matrikelnr."' (statt '".trim($rows->matrikelnr)."')";
										}
									}
									if($rows->prestudent_id!=$prestudent_id)
									{
										$updates=true;
										if(strlen(trim($ausgabe_student))>0)
										{
											$ausgabe_student.=", PrestudentID: '".$prestudent_id."' (statt '".$rows->prestudent_id."')";
										}
										else
										{
											$ausgabe_student="PrestudentID: '".$prestudent_id."' (statt '".$rows->prestudent_id."')";
										}
									}
									if($rows->studiengang_kz!=$studiengang_kz)
									{
										$updates=true;
										if(strlen(trim($ausgabe_student))>0)
										{
											$ausgabe_student.=", Studiengang: '".$studiengang_kz."' (statt '".$rows->studiengang_kz."')";
										}
										else
										{
											$ausgabe_student="Studiengang: '".$studiengang_kz."' (statt '".$rows->studiengang_kz."')";
										}
									}
									if($rows->semester!=$semester)
									{
										$updates=true;
										if(strlen(trim($ausgabe_student))>0)
										{
											$ausgabe_student.=", Semester: '".$semester."' (statt '".$rows->semester."')";
										}
										else
										{
											$ausgabe_student="Semester: '".$semester."' (statt '".$rows->semester."')";
										}
									}
									if($rows->verband!=$verband)
									{
										$updates=true;
										if(strlen(trim($ausgabe_student))>0)
										{
											$ausgabe_student.=", Verband: '".$verband."' (statt '".$rows->verband."')";
										}
										else
										{
											$ausgabe_student="Verband: '".$verband."' (statt '".$rows->verband."')";
										}
									}
									if($rows->gruppe!=$gruppe)
									{
										$updates=true;
										if(strlen(trim($ausgabe_student))>0)
										{
											$ausgabe_student.=", Gruppe: '".$gruppe."' (statt '".$rows->gruppe."')";
										}
										else
										{
											$ausgabe_student="Gruppe: '".$gruppe."' (statt '".$rows->gruppe."')";
										}
									}						
									if($rows->ext_id!=$ext_id_student)
									{
										$updates=true;
										if(strlen(trim($ausgabe_student))>0)
										{
											$ausgabe_student.=", EXT_ID: '".$ext_id_student."' (statt '".$rows->ext_id."')";
										}
										else
										{
											$ausgabe_student="EXT_ID: '".$ext_id_student."' (statt '".$rows->ext_id."')";
										}
									}	
									if($updates)
									{
										$qry = 'UPDATE public.tbl_student SET'.
										       ' student_uid='.myaddslashes($student_uid).','.
										       ' matrikelnr='.myaddslashes($matrikelnr).','.
										       ' prestudent_id='.myaddslashes($prestudent_id).','.
										       ' studiengang_kz='.myaddslashes($studiengang_kz).','.
										       //' semester='.myaddslashes($semester).','.
										       //' verband='.myaddslashes($verband).','.
										       //' gruppe='.myaddslashes($gruppe).','.
								        		       " updateamum=now()".','.
								        		       " updatevon=".myaddslashes($updatevon).','.
										       ' ext_id='.myaddslashes($ext_id_student).
										       $student_where;
										       $ausgabe_student="Änderungen bei Student ".$student_uid.", ".$nachname.", (".$matrikelnr."): ".$ausgabe_student.".\n";
									}
								}
							}
						}
						if(!@pg_query($conn,$qry))
						{			
							$error=true;
							$error_log.='Fehler beim Speichern des Student-Datensatzes:'.$nachname.' / '.$qry."\n".pg_errormessage($conn)."\n";
							$ausgabe_student='';
						}
						else 
						{
							if($new_student)
							{
								$anzahl_student_insert++;
							}
							else 
							{
								if($updates)
								{
									$anzahl_student_update++;
								}
							}
							$anzahl_student_gesamt++;
						}
						if(!$error)
						{
							$ausgabe.=$ausgabe_benutzer;
							$ausgabe.=$ausgabe_student;
							//fwrite($dateiausgabe,$ausgabe);
							//$ausgabe='';
							pg_query($conn,'COMMIT;');
						}
						else
						{
							$anzahl_fehler_student++;
							$text1.="\n***********".$student_uid." / ".$nachname.", ".$vorname." / ".$matrikelnr."\n";
							$text1.=$error_log;
							$text1.="\n".$qry." R1\n";
							$text1.="***********\n\n";
							pg_query($conn,'ROLLBACK;');
						}									
					}
					else 
					{
						$anzahl_fehler_benutzer++;
						$text2.="\n***********".$student_uid." / ".$nachname.", ".$vorname." / ".$matrikelnr."\n";
						$text2.=$error_log;
						$text2.="\n".$qry." R2\n";
						$text2.="***********\n\n";
						pg_query($conn,'ROLLBACK;');
					}
				}
				else 
				{
					$anzahl_nichtstudenten++;
					if($error)
					{
						$anzahl_fehler_pre++;
						$text5.="\n***********".$student_uid." / ".$nachname.", ".$vorname." / ".$matrikelnr."\n";
						$text5.="Semester: ".$semester."/ Verband: ".$verband." / Gruppe: ".$gruppe." / Stg:".$studiengang_kz."\n";
						$text5.=$error_log;
						$text5.="\n".$qry." C1\n";
						$text5.="***********\n\n";
					}
					$ausgabe.=$ausgabe_person;
					//fwrite($dateiausgabe,$ausgabe);
					//$ausgabe='';
					pg_query($conn,'COMMIT;'); //Commit, wenn kein Gruppeneintrag gefunden (Interessent, Bewerber) => nur Person und Prestudent werden angelegt	
				}
				
			}
			else
			{
				$anzahl_fehler_pre++;
				$text3.="\n***********".$student_uid." / ".$nachname.", ".$vorname." / ".$matrikelnr."\n";
				$text3.=$error_log;
				$text3.="\n".$qry." R3\n";
				$text3.="***********\n\n";
				pg_query($conn,'ROLLBACK;');
			}						
		}
		else
		{
			$anzahl_fehler_person++;
			$text4.="\n***********".$student_uid." / ".$nachname.", ".$vorname." / ".$matrikelnr."\n";
			$text4.=$error_log;
			$text4.="\n".$qry." R4\n";
			$text4.="***********\n\n";
			pg_query($conn,'ROLLBACK;');
		}

	}
}		
echo nl2br("Studentensynchro Ende ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");

echo nl2br("\n\nPersonen ohne Reihungstest: ".$notest." \n");
echo nl2br("Personen:       Gesamt: ".$anzahl_person_gesamt." / Eingefügt: ".$anzahl_person_insert." / Geändert: ".$anzahl_person_update." / Fehler: ".$anzahl_fehler_person."\n");
echo nl2br("Prestudenten:   Gesamt: ".$anzahl_pre_gesamt." / Eingefügt: ".$anzahl_pre_insert." / Geändert: ".$anzahl_pre_update." / Fehler: ".$anzahl_fehler_pre."\n");
echo nl2br("Benutzer:       Gesamt: ".$anzahl_benutzer_gesamt." / Eingefügt: ".$anzahl_benutzer_insert." / Geändert: ".$anzahl_benutzer_update." / Fehler: ".$anzahl_fehler_benutzer."\n");
echo nl2br("Nicht-Studenten: ".$anzahl_nichtstudenten."\n");
echo nl2br("Studenten:      Gesamt: ".$anzahl_student_gesamt." / Eingefügt: ".$anzahl_student_insert." / Geändert: ".$anzahl_student_update." / Fehler: ".$anzahl_fehler_student."\n");

$error_log="Sync Student\n------------\n\n".$text4."\n".$text3."\n".$text5."\n".$text2."\n".$text1;
echo nl2br($error_log);

mail($adress, 'SYNC-Fehler Student von '.$_SERVER['HTTP_HOST'], $error_log,"From: vilesci@technikum-wien.at");

mail($adress, 'SYNC Student  von '.$_SERVER['HTTP_HOST'], "Sync Student\n------------\n\nPersonen ohne Reihungstest: ".$notest." \n\n"
."Personen:       Gesamt: ".$anzahl_person_gesamt." / Eingefügt: ".$anzahl_person_insert." / Geändert: ".$anzahl_person_update." / Fehler: ".$anzahl_fehler_person."\n"
."Prestudenten:   Gesamt: ".$anzahl_pre_gesamt." / Eingefügt: ".$anzahl_pre_insert." / Geändert: ".$anzahl_pre_update." / Fehler: ".$anzahl_fehler_pre."\n"
."Benutzer:       Gesamt: ".$anzahl_benutzer_gesamt." / Eingefügt: ".$anzahl_benutzer_insert." / Geändert: ".$anzahl_benutzer_update." / Fehler: ".$anzahl_fehler_benutzer."\n"
."Nicht-Studenten: ".$anzahl_nichtstudenten."\n"
."Studenten:      Gesamt: ".$anzahl_student_gesamt." / Eingefügt: ".$anzahl_student_insert." / Geändert: ".$anzahl_student_update." / Fehler: ".$anzahl_fehler_student."\n\n".$dateiausgabe."Fertig: ".date("d.m.Y H:i:s")."\n\n".$ausgabe, "From: vilesci@technikum-wien.at");

?>
</body>
</html>