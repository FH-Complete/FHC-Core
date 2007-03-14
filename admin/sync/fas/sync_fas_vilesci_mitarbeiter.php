<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Synchronisiert Mitarbeiterdatensaetze von FAS DB in PORTAL DB
 * setzt voraus: tbl_nation, tbl_sprache, tbl_ort
 * benötigt: tbl_syncperson
 */
require_once('../../../vilesci/config.inc.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

//$adress='ruhan@technikum-wien.at';
//$adress_plausi='ruhan@technikum-wien.at';
$adress='fas_sync@technikum-wien.at';
$adress_plausi='fas_sync@technikum-wien.at';

$error_log='';
$error_log_fas='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt_person=0;
$anzahl_geaendert_person=0;
$anzahl_eingefuegt_mitarbeiter=0;
$anzahl_geaendert_mitarbeiter=0;
$anzahl_eingefuegt_benutzer=0;
$anzahl_geaendert_benutzer=0;
$plausi='';
$plausisvnr='';
$anzahl_fehler_person=0;
$anzahl_fehler_benutzer=0;
$anzahl_fehler_mitarbeiter=0;
$ausgabe_person='';
$ausgabe_mitarbeiter='';
$ausgabe_benutzer='';
$ausgabe='';

function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

/************************************
 * FAS-PORTAL - Synchronisation
 */
?>
<html>
<head>
<title>Synchro - FAS -> Vilesci - Mitarbeiter</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>

<?php
$plausisvnr="Überprüfung Mitarbeiterdaten im FAS:\n\n";
echo nl2br($error_log_fas);

$qry="SELECT * FROM Person join Mitarbeiter ON person_pk=mitarbeiter.person_fk WHERE svnr='0005010400';";
if($resultp = pg_query($conn_fas, $qry))
{
	$plausisvnr="SVNr 0005010400 findet sich bei folgenden ".pg_numrows($resultp)." Personen:\n";
	while($rowp=pg_fetch_object($resultp))
	{
		$plausisvnr.="Person ".$rowp->uid." / PNr.: ".$rowp->persnr." / ".$rowp->familienname."\n";
		$error=true;
	}
}
$qry="SELECT * FROM Person join Mitarbeiter ON person_pk=mitarbeiter.person_fk WHERE uid IS NULL OR uid='';";
if($resultp = pg_query($conn_fas, $qry))
{
	$plausisvnr.="\n\nKeine UID bei ".pg_numrows($resultp)." Personen:\n";
	while($rowp=pg_fetch_object($resultp))
	{
		$plausisvnr.="Person mit PNr.: ".$rowp->persnr." / ".$rowp->familienname."\n";
		$error=true;
	}
}
echo nl2br($plausisvnr."\n");
$qry="
SELECT 
p1.person_pk AS person1, p1.familienname AS familienname1, p1.vorname AS vorname1, p1.vornamen AS vornamen1, p1.geschlecht AS geschlecht1, 
p1.gebdat AS gebdat1, p1.gebort AS gebort1, p1.staatsbuergerschaft AS staatsbuergerschaft1, p1.familienstand AS familienstand1, 
p1.svnr AS svnr1, p1. ersatzkennzeichen  AS ersatzkennzeichen1, p1.anrede AS anrede1, p1.anzahlderkinder AS anzahlderkinder1, 
p1.bismelden AS bismelden1, p1.titel AS titel1,  p1.uid AS uid1, p1.gebnation AS gebnation1, p1.postnomentitel AS postnomentitel1,
p1.beginndatum AS beginndatum1, p1.akadgrad AS akadgrad1, p1.habilitation AS habilitation1, p1.mitgliedentwicklungsteam as mitgliedentwicklungsteam1, 
p1.qualifikation as qualifikation1, p1.hauptberuflich as hauptberuflich1, p1.hauptberuf AS hauptberuf1, p1.semesterwochenstunden AS semesterwochenstunden1, 
p1.persnr as persnr1, p1.beendigungsdatum AS beendigungsdatum1, p1.ausgeschieden AS ausgeschieden1, p1.kurzbez AS kurzbez1, 
p1.stundensatz AS stundensatz1, p1.ausbildung AS ausbildung1, p1.aktiv AS aktiv1,
p2.person_pk AS person2, p2.familienname AS familienname2, p2.vorname AS vorname2, p2.vornamen AS vornamen2, p2.geschlecht AS geschlecht2, 
p2.gebdat AS gebdat2, p2.gebort AS gebort2, p2.staatsbuergerschaft AS staatsbuergerschaft2, p2.familienstand AS familienstand2, 
p2.svnr AS svnr2, p2. ersatzkennzeichen  AS ersatzkennzeichen2, p2.anrede AS anrede2, p2.anzahlderkinder AS anzahlderkinder2, 
p2.bismelden AS bismelden2, p2.titel AS titel2,  p2.uid AS uid2, p2.gebnation AS gebnation2, p2.postnomentitel AS postnomentitel2,
p2.beginndatum AS beginndatum2, p2.akadgrad AS akadgrad2, p2.habilitation AS habilitation2, p2.mitgliedentwicklungsteam AS mitgliedentwicklungsteam2, 
p2.qualifikation as qualifikation2, p2.hauptberuflich as hauptberuflich2, p2.hauptberuf AS hauptberuf2, p2.semesterwochenstunden AS semesterwochenstunden2, 
p2.persnr as persnr2, p2.beendigungsdatum AS beendigungsdatum2, p2.ausgeschieden AS ausgeschieden2, p2.kurzbez AS kurzbez2, 
p2.stundensatz AS stundensatz2, p2.ausbildung AS ausbildung2, p2.aktiv AS aktiv2
FROM (person JOIN mitarbeiter ON person_pk=mitarbeiter.person_fk ) AS p1
CROSS JOIN (person JOIN mitarbeiter ON person_pk=mitarbeiter.person_fk) AS p2 WHERE 
((p1.gebdat=p2.gebdat AND p1.familienname=p2.familienname AND p1.vorname=p2.vorname) 
OR ((p1.ersatzkennzeichen=p2.ersatzkennzeichen AND p1.ersatzkennzeichen<>'') OR (p1.svnr=p2.svnr AND p1.svnr<>'')))
AND (p1.person_pk < p2.person_pk)
AND (p1.svnr<>'0005010400' AND p2.svnr<>'0005010400')
AND (p1.familienname<>p2.familienname OR p1.vorname<>p2.vorname OR p1.vornamen<>p2.vornamen OR p1.geschlecht<>p2.geschlecht OR p1.gebdat<>p2.gebdat OR p1.gebort<>p2.gebort OR p1.staatsbuergerschaft<> p2.staatsbuergerschaft OR p1.familienstand<>p2.familienstand OR p1.svnr<>p2.svnr OR p1.ersatzkennzeichen<>p2.ersatzkennzeichen OR p1.anrede<>p2.anrede OR p1.anzahlderkinder<>p2.anzahlderkinder OR p1.titel<>p2.titel OR p1.gebnation<>p2.gebnation OR p1.postnomentitel<> p2.postnomentitel 
	OR p1.beginndatum<>p2.beginndatum OR p1.akadgrad<>p2.akadgrad OR p1.habilitation<>p2.habilitation OR p1.mitgliedentwicklungsteam<>p2.mitgliedentwicklungsteam OR p1.qualifikation<>p2.qualifikation OR p1.hauptberuflich<>p2.hauptberuflich OR p1.hauptberuf<>p2.hauptberuf OR p1.semesterwochenstunden<>p2.semesterwochenstunden OR p1.persnr<>p2.persnr OR p1.beendigungsdatum<>p2.beendigungsdatum OR p1.ausgeschieden<>p2.ausgeschieden OR p1.kurzbez<>p2.kurzbez OR p1.stundensatz<>p2.stundensatz OR p1.ausbildung<>p2.ausbildung OR p1.aktiv<>p2.aktiv) 
order by p1.familienname;
";
//AND (p1.svnr<>'0005010400' AND p2.svnr<>'0005010400')

if($resultp = pg_query($conn_fas, $qry))
{
	while($rowp=pg_fetch_object($resultp))
	{
		$plausi='';
		if ($rowp->geschlecht1<>$rowp->geschlecht2)
		{
			$plausi.="Geschlecht der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->geschlecht1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->geschlecht2."'.\n";
			$error=true;
		}
		if ($rowp->familienname1<>$rowp->familienname2)
		{
			$plausi.="Familienname der Person ".$rowp->uid1." (person_pk=".$rowp->person1.") ist '".$rowp->familienname1."' bei ".$rowp->uid2." (person_pk=".$rowp->person2.")  aber '".$rowp->familienname2."'.\n";
			$error=true;
		}
		if ($rowp->vorname1<>$rowp->vorname2)
		{
			$plausi.="Vorname der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->vorname1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->vorname2."'.\n";
			$error=true;
		}
		if ($rowp->vornamen1<>$rowp->vornamen2)
		{
			$plausi.="Vornamen der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->vornamen1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->vornamen2."'.\n";
			$error=true;
		}
		if ($rowp->gebdat1<>$rowp->gebdat2)
		{
			$plausi.="Geburtsdatum der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->gebdat1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->gebdat2."'.\n";
			$error=true;
		}
		if ($rowp->gebort1<>$rowp->gebort2)
		{
			$plausi.="Geburtsort der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->gebort1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->gebort2."'.\n";
			$error=true;
		}
		if ($rowp->staatsbuergerschaft1<>$rowp->staatsbuergerschaft2)
		{
			$plausi.="Staatsbürgerschaft der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->staatsbuergerschaft1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->staatsbuergerschaft2."'.\n";
			$error=true;
		}
		if ($rowp->familienstand1<>$rowp->familienstand2)
		{
			$plausi.="Familienstand der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->familienstand1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->familienstand2."'.\n";
			$error=true;
		}
		if ($rowp->svnr1<>$rowp->svnr2)
		{
			$plausi.="Sozialversicherung der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->svnr1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->svnr2."'.\n";
			$error=true;
		}
		if ($rowp->ersatzkennzeichen1<>$rowp->ersatzkennzeichen2)
		{
			$plausi.="Ersatzkennzeichen der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->ersatzkennzeichen1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->ersatzkennzeichen2."'.\n";
			$error=true;
		}
		if ($rowp->anrede1<>$rowp->anrede2)
		{
			$plausi.="Anrede der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->anrede1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->anrede2."'.\n";
			$error=true;
		}
		if ($rowp->anzahlderkinder1<>$rowp->anzahlderkinder2)
		{
			$plausi.="Anzahl der Kinder der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->anzahlderkinder1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->anzahlderkinder2."'.\n";
			$error=true;
		}
		if ($rowp->titel1<>$rowp->titel2)
		{
			$plausi.="Titel der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->titel1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->titel2."'.\n";
			$error=true;
		}
		if ($rowp->gebnation1<>$rowp->gebnation2)
		{
			$plausi.="Geburtsnation der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->gebnation1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->gebnation2."'.\n";
			$error=true;
		}
		if ($rowp->postnomentitel1<>$rowp->postnomentitel2)
		{
			$plausi.="Postnomentitel der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->postnomentitel1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->postnomentitel2."'.\n";
			$error=true;
		}
		if ($rowp->beginndatum1<>$rowp->beginndatum2)
		{
			$plausi.="Beginndatum der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->beginndatum1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->beginndatum2."'.\n";
			$error=true;
		}
		if ($rowp->akadgrad1<>$rowp->akadgrad2)
		{
			$plausi.="Akademischer Grad der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->akadgrad1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->akadgrad2."'.\n";
			$error=true;
		}
		if ($rowp->habilitation1<>$rowp->habilitation2)
		{
			$plausi.="Habilitation der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->habilitation1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->habilitation2."'.\n";
			$error=true;
		}
		if ($rowp->mitgliedentwicklungsteam1<>$rowp->mitgliedentwicklungsteam2)
		{
			$plausi.="Mitgliedentwicklungsteam der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->mitgliedentwicklungsteam1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->mitgliedentwickluingsteam2."'.\n";
			$error=true;
		}
		if ($rowp->qualifikation1<>$rowp->qualifikation2)
		{
			$plausi.="Qualifikation der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->qualifikation1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->qualifikation2."'.\n";
			$error=true;
		}
		if ($rowp->hauptberuflich1<>$rowp->hauptberuflich2)
		{
			$plausi.="Hauptberuflich der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->hauptberuflich1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->hauptberuflich2."'.\n";
			$error=true;
		}
		if ($rowp->hauptberuf1<>$rowp->hauptberuf2)
		{
			$plausi.="Hauptberuf der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->hauptberuf1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->hauptberuf2."'.\n";
			$error=true;
		}
		if ($rowp->semesterwochenstunden1<>$rowp->semesterwochenstunden2)
		{
			$plausi.="Semesterwochenstunden der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->semesterwochenstunden1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->semesterwochenstunden2."'.\n";
			$error=true;
		}
		if ($rowp->persnr1<>$rowp->persnr2)
		{
			$plausi.="Personalnummer der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->persnr1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->persnr2."'.\n";
			$error=true;
		}
		if ($rowp->beendigungsdatum1<>$rowp->beendigungsdatum2)
		{
			$plausi.="Beendigungsdatum der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->beendigungsdatum1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->beendigungsdatum2."'.\n";
			$error=true;
		}
		if ($rowp->ausgeschieden1<>$rowp->ausgeschieden2)
		{
			$plausi.="Ausgeschieden der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->ausgeschieden1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->ausgeschieden2."'.\n";
			$error=true;
		}
		if ($rowp->kurzbez1<>$rowp->kurzbez2)
		{
			$plausi.="Kurzbezeichnung der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->kurzbez1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->kurzbez2."'.\n";
			$error=true;
		}
		if ($rowp->stundensatz1<>$rowp->stundensatz2)
		{
			$plausi.="Stundensatz der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->stundensatz1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->stundensatz2."'.\n";
			$error=true;
		}
		if ($rowp->ausbildung1<>$rowp->ausbildung2)
		{
			$plausi.="Ausbildung der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->ausbildung1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->ausbildung2."'.\n";
			$error=true;
		}
		if ($rowp->aktiv1<>$rowp->aktiv2)
		{
			$plausi.="Aktiv der Person ".$rowp->familienname1." (".$rowp->uid1.", person_pk=".$rowp->person1.") ist '".$rowp->aktiv1."' bei ".$rowp->familienname2." (".$rowp->uid2.", person_pk=".$rowp->person2.") aber '".$rowp->aktiv2."'.\n";
			$error=true;
		}
		if ($error)
		{
			$plausi="*****\n".$plausi."*****\n";
			echo nl2br ($plausi);
			$error_log_fas.=$plausi;
			//ob_flush();
			//flush();
			$error=false;
		}
	}
}
$error_log_fas=$plausisvnr."\n".$error_log_fas;
mail($adress_plausi, 'Plausicheck von Mitarbeiter', $error_log_fas,"From: vilesci@technikum-wien.at");
$error_log_fas='';





$qryall = "SELECT * FROM person JOIN mitarbeiter ON person_pk=mitarbeiter.person_fk WHERE (person.uid IS NOT NULL  AND person.uid<>'')
AND person_pk NOT IN (
SELECT p1.person_pk
FROM (person JOIN mitarbeiter ON person_pk=mitarbeiter.person_fk ) AS p1
CROSS JOIN (person JOIN mitarbeiter ON person_pk=mitarbeiter.person_fk) AS p2 WHERE 
((p1.gebdat=p2.gebdat AND p1.familienname=p2.familienname AND p1.svnr='' AND p1.ersatzkennzeichen='') 
OR ((p1.ersatzkennzeichen=p2.ersatzkennzeichen AND p1.ersatzkennzeichen<>'') OR (p1.svnr=p2.svnr AND p1.svnr<>'')))
AND (p1.person_pk <> p2.person_pk)
AND (p1.familienname<>p2.familienname OR p1.vorname<>p2.vorname OR p1.vornamen<>p2.vornamen OR p1.geschlecht<>p2.geschlecht OR p1.gebdat<>p2.gebdat OR p1.staatsbuergerschaft<> p2.staatsbuergerschaft OR p1.familienstand<>p2.familienstAND OR p1.svnr<>p2.svnr OR p1.ersatzkennzeichen<>p2.ersatzkennzeichen OR p1.anrede<>p2.anrede OR p1.anzahlderkinder<>p2.anzahlderkinder OR p1.bismelden<>p2.bismelden OR p1.titel<>p2.titel OR p1.gebnation<>p2.gebnation OR p1.postnomentitel<> p2.postnomentitel 
	OR p1.beginndatum<>p2.beginndatum OR p1.akadgrad<>p2.akadgrad OR p1.habilitation<>p2.habilitation OR p1.mitgliedentwicklungsteam<>p2.mitgliedentwicklungsteam OR p1.qualifikation<>p2.qualifikation OR p1.hauptberuflich<>p2.hauptberuflich OR p1.hauptberuf<>p2.hauptberuf OR p1.semesterwochenstunden<>p2.semesterwochenstunden OR p1.persnr<>p2.persnr OR p1.beendigungsdatum<>p2.beendigungsdatum OR p1.ausgeschieden<>p2.ausgeschieden OR p1.kurzbez<>p2.kurzbez OR p1.stundensatz<>p2.stundensatz OR p1.ausbildung<>p2.ausbildung OR p1.aktiv<>p2.aktiv) 
);";

if($resultall = pg_query($conn_fas, $qryall))
{
	echo nl2br("Mitarbeiter Sync\n-----------------\n");
	//ob_flush();
	//flush();
	$anzahl_quelle=pg_num_rows($resultall);
	while($rowall = pg_fetch_object($resultall))
	{
		$ausgabe_person='';
		$ausgabe_benutzer='';
		$ausgabe_mitarbeiter='';
		//echo "- ";
		//ob_flush();
		//flush();
		//PERSON
		$personsprache='';
		$persongebzeit='';
		$personfoto='';
		$personhomepage='';
		$persongeburtsnation=$rowall->gebnation;
		$personanrede=trim($rowall->anrede);
		$persontitelpost=trim($rowall->postnomentitel);
		$persontitelpre=trim($rowall->titel);
		$personnachname=trim($rowall->familienname);
		$personvorname=trim($rowall->vorname);
		$personvornamen=trim($rowall->vornamen);
		$persongebdatum=$rowall->gebdat;
		$persongebort=$rowall->gebort;
		$personanmerkungen='';
		$personsvnr=trim($rowall->svnr);
		$personersatzkennzeichen=trim($rowall->ersatzkennzeichen);
		$personfamilienstand=$rowall->familienstand;
		$personanzahlkinder=$rowall->anzahlderkinder;
		$personstaatsbuergerschaft=$rowall->staatsbuergerschaft;
		$persongeschlecht=strtolower($rowall->geschlecht);
		$personext_id=$rowall->person_pk;
		$personaktiv=true;
		$personupdatevon='SYNC';
		$personinsertvon='SYNC';
		if($rowall->familienstand==1)
		{
			$personfamilienstand='l';
		}
		elseif($rowall->familienstand==2)
		{
			$personfamilienstand='v';
		}
		elseif($rowall->familienstand==3)
		{
			$personfamilienstand='g';
		}
		elseif($rowall->familienstand==4)
		{
			$personfamilienstand='w';
		}
		else
		{
			$personfamilienstand=null;
		}
		if ($persongeschlecht=='')
		{
			$persongeschlecht='m';
		}
		
		//MITARBEITER
		//if($rowall->personalnummer!='')
		$mitarbeiteruid=$rowall->uid;
		$mitarbeiterpersonalnummer=$rowall->persnr;
		$mitarbeitertelefonklappe='';
		$mitarbeiterkurzbz=$rowall->kurzbez;
		$mitarbeiterlektor=true;
		$mitarbeiterfixangestellt=false;
		$mitarbeiterstundensatz=0;
		if($rowall->ausbildung>0)
		{
			$mitarbeiterausbildungcode=$rowall->ausbildung;
		}
		else 
		{
			$mitarbeiterausbildungcode=null;
		}
		$mitarbeiterort_kurzbz=null;
		$mitarbeiteranmerkung=$rowall->bemerkung;
		$mitarbeiterinsertvon='SYNC';
		$mitarbeiterinsertamum='';
		$mitarbeiterupdateamum='';
		$mitarbeiterupdatevon='SYNC';
		$mitarbeiterext_id=$rowall->mitarbeiter_pk;
		
				
		
		//BENUTZER
		$benutzeruid=$rowall->uid;
		$benutzerperson_id='';
		$benutzeraktiv=($rowall->aktiv=='t'?true:false);
		$benutzeralias='';
		$benutzerinsertvon='SYNC';
		$benutzerinsertamum='';
		$benutzerupdateamum='';
		$benutzerupdatevon='SYNC';
		$benutzerext_id=$rowall->person_pk;
		
		
		$error=false;
			
		pg_query($conn, "BEGIN");
		
		$qry="SELECT person_id FROM public.tbl_benutzer WHERE uid='$rowall->uid'";
		if($resultu = pg_query($conn, $qry))
		{
			if(pg_num_rows($resultu)>0 && $rowall->uid!='') //wenn dieser eintrag schon vorhanden ist
			{
				if($rowu=pg_fetch_object($resultu))
				{
					//update
					$personperson_id=$rowu->person_id;
					$personnew=false;
				}
				else
				{
					$error=true;
					$error_log.="benutzer von $rowall->uid konnte nicht ermittelt werden\n";
				}
			}
			else
			{
				$qry="SELECT person_fas, person_portal FROM sync.tbl_syncperson WHERE person_fas='$rowall->person_pk'";
				if($result1 = pg_query($conn, $qry))
				{
					if(pg_num_rows($result1)>0) //wenn dieser eintrag schon vorhanden ist
					{
						if($row1=pg_fetch_object($result1))
						{
							//update
							$personperson_id=$row1->person_portal;
							$personnew=false;								
						}
						else
						{
							$error=true;
							$error_log.="person von $rowall->person_pk konnte nicht ermittelt werden\n";
						}
					}
					else
					{
						//vergleich svnr und ersatzkennzeichen
						$qry="SELECT * FROM public.tbl_person 
							WHERE ('$rowall->svnr' is not null AND '$rowall->svnr' <> '' AND svnr = '$rowall->svnr') 
							OR ('$rowall->ersatzkennzeichen' is not null AND '$rowall->ersatzkennzeichen' <> '' AND ersatzkennzeichen = '$rowall->ersatzkennzeichen')";
						if($resultz = pg_query($conn, $qry))
						{
							if(pg_num_rows($resultz)>0) //wenn dieser eintrag schon vorhanden ist
							{
								if($rowz=pg_fetch_object($resultz))
								{
									$personnew=false;
									$personperson_id=$rowz->person_id;

								}
								else
								{
									$error=true;
									$error_log.="person mit svnr: $rowall->svnr bzw. ersatzkennzeichen: $rowall->ersatzkennzeichen konnte nicht ermittelt werden (".pg_num_rows($resultz).")\n";
								}
							}
							else
							{
								//insert
								$personnew=true;
								//echo nl2br("insert von ".$rowall->uid.", ".$rowall->familienname."\n");
							}
						}
					}
				}
			}
			$persondone=false;
			if(!$error)
			{
				if($personnew) 
				{
					$qryi = "INSERT INTO public.tbl_person (sprache, anrede, titelpost, titelpre, nachname, vorname, vornamen, 
					                    gebdatum, gebort, gebzeit, foto, anmerkungen, homepage, svnr, ersatzkennzeichen, 
					                    familienstand, anzahlkinder, aktiv, insertamum, insertvon, updateamum, updatevon,
					                    geschlecht, geburtsnation, staatsbuergerschaft, ext_id)
					       	 VALUES(".myaddslashes($personsprache).", ".
							myaddslashes($personanrede).", ".
							myaddslashes($persontitelpost).", ".
						        myaddslashes($persontitelpre).", ".
						        myaddslashes($personnachname).", ".
						        myaddslashes($personvorname).", ".
						        myaddslashes($personvornamen).", ".
						        myaddslashes($persongebdatum).", ".
						        myaddslashes($persongebort).", ".
						        myaddslashes($persongebzeit).", ".
						        myaddslashes($personfoto).", ".
						        myaddslashes($personanmerkungen).", ".
						        myaddslashes($personhomepage).", ".
						        myaddslashes($personsvnr).", ".
						        myaddslashes($personersatzkennzeichen).", ".
						        myaddslashes($personfamilienstand).", ".
						        myaddslashes($personanzahlkinder).", ".
						        myaddslashes($personaktiv?'true':'false').", 
						        now(), ".
						        myaddslashes($personinsertvon).", 
						        now(), ".
						        myaddslashes($personupdatevon).", ".
						        myaddslashes($persongeschlecht).", ".
						        myaddslashes($persongeburtsnation).", ".
						        myaddslashes($personstaatsbuergerschaft).", ".
						        myaddslashes($personext_id).";";
						        $persondone=true;
						        $ausgabe_person="Person ".$personnachname." ".$personvorname." eingefügt.";
				}
				else
				{
					//person_id auf gueltigkeit pruefen
					if(!is_numeric($personperson_id))
					{				
						$error_log.= "person_id muss eine gueltige Zahl sein\n";
						$error=true;
					}
					
					//update nur wenn änderungen gemacht
					$qryu="SELECT * FROM public.tbl_person WHERE person_id='$personperson_id';";
					if($resultu = pg_query($conn, $qryu))
					{
						if($rowu = pg_fetch_object($resultu))
						{
							$updatep=false;			
							if($rowu->sprache!=$personsprache) 
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Sprache: '".$personsprache."'";
								}
								else
								{
									$ausgabe_person="Sprache: '".$personsprache."'";
								}
							}
							if($rowu->anrede!=$personanrede)
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Anrede: '".$personanrede."'";
								}
								else
								{
									$ausgabe_person="Anrede: '".$personanrede."'";
								}
							}
							if($rowu->titelpost!=$persontitelpost)
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Postnomentitel: '".$persontitelpost."'";
								}
								else
								{
									$ausgabe_person="Postnomentitel: '".$persontitelpost."'";
								}
							}
							if($rowu->titelpre!=$persontitelpre)
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Prenomentitel: '".$persontitelpre."'";
								}
								else
								{
									$ausgabe_person="Prenomentitel: '".$persontitelpre."'";
								}
							}
							if($rowu->nachname!=$personnachname)
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Nachname: '".$personnachname."'";
								}
								else
								{
									$ausgabe_person=" Nachname: '".$personnachname."'";
								}
							}
							if($rowu->vorname!=$personvorname)
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Vorname: '".$personvorname."'";
								}
								else
								{
									$ausgabe_person="Vorname: '".$personvorname."'";
								}
							}
							if($rowu->vornamen!=$personvornamen)
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Vornamen: '".$personvornamen."'";
								}
								else
								{
									$ausgabe_person="Vornamen: '".$personvornamen."'";
								}
							}
							if($rowu->gebdatum!=$persongebdatum)
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Geburtsdatum: '".$persongebdatum."'";
								}
								else
								{
									$ausgabe_person="Geburtsdatum: '".$persongebdatum."'";
								}
							}
							if($rowu->gebort!=$persongebort)
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Geburtsort: '".$persongebort."'";
								}
								else
								{
									$ausgabe_person="Geburtsort: '".$persongebort."'";
								}
							}
							if($rowu->gebzeit!=$persongebzeit)
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Geburtszeit: '".$persongebzeit."'";
								}
								else
								{
									$ausgabe_person="Geburtszeit: '".$persongebzeit."'";
								}
							}
							if($rowu->foto!=$personfoto)
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Foto: '".$personfoto."'";
								}
								else
								{
									$ausgabe_person="Foto: '".$personfoto."'";
								}
							}
							if($rowu->anmerkungen!=$personanmerkungen)
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Anmerkungen: '".$personanmerkungen."'";
								}
								else
								{
									$ausgabe_person="Anmerkungen: '".$personanmerkungen."'";
								}
							}
							if($rowu->homepage!=$personhomepage){
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Homepage: '".$personhomepage."'";
								}
								else
								{
									$ausgabe_person="Homepage: '".$personhomepage."'";
								}
							}
							if($rowu->svnr!=$personsvnr)
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", SVNr: '".$personsvnr."'";
								}
								else
								{
									$ausgabe_person="SVNr: '".$personsvnr."'";
								}
							}
							if($rowu->ersatzkennzeichen!=$personersatzkennzeichen)
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Ersatzkennzeichen: '".$personersatzkennzeichen."'";
								}
								else
								{
									$ausgabe_person="Ersatzkennzeichen: '".$personersatzkennzeichen."'";
								}
							}
							if($rowu->familienstand!=$personfamilienstand)
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Familienstand: '".$personfamilienstand."'";
								}
								else
								{
									$ausgabe_person="Familienstand: '".$personfamilienstand."'";
								}
							}
							if($rowu->anzahlkinder!=$personanzahlkinder){
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Anzahl der Kinder: '".$personanzahlkinder."'";
								}
								else
								{
									$ausgabe_person="Anzahl der Kinder: '".$personanzahlkinder."'";
								}
							}
							if($rowu->aktiv!=$personaktiv)
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Aktiv: '".$personaktiv."'";
								}
								else
								{
									$ausgabe_person="Aktiv: '".$personaktiv."'";
								}
							}
							if($rowu->geburtsnation!=$persongeburtsnation)
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Geburtsnation: '".$persongeburtsnation."'";
								}
								else
								{
									$ausgabe_person="Geburtsnation: '".$persongeburtsnation."'";
								}
							}
							if($rowu->geschlecht!=$persongeschlecht)
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Geschlecht: '".$persongeschlecht."'";
								}
								else
								{
									$ausgabe_person="Sprache: '".$persongeschlecht."'";
								}
							}
							if($rowu->staatsbuergerschaft!=$personstaatsbuergerschaft)
							{
								$updatep=true;
								if(strlen(trim($ausgabe_person))>0)
								{
									$ausgabe_person.=", Staatsbürgerschaft: '".$personstaatsbuergerschaft."'";
								}
								else
								{
									$ausgabe_person="Staatsbürgerschaft: '".$personstaatsbuergerschaft."'";
								}
							}
							
							
							if($updatep)
							{
								$qry = "UPDATE public.tbl_person SET
								       sprache=".myaddslashes($personsprache).", 
								       anrede=".myaddslashes($personanrede).", 
								       titelpost=".myaddslashes($persontitelpost).", 
								       titelpre=".myaddslashes($persontitelpre).", 
								       nachname=".myaddslashes($personnachname).", 
								       vorname=".myaddslashes($personvorname).", 
								       vornamen=".myaddslashes($personvornamen).", 
								       gebdatum=".myaddslashes($persongebdatum).", 
								       gebort=".myaddslashes($persongebort).", 
								       gebzeit=".myaddslashes($persongebzeit).", 
								       foto=".myaddslashes($personfoto).", 
								       anmerkungen=".myaddslashes($personanmerkungen).", 
								       homepage=".myaddslashes($personhomepage).", 
								       svnr=".myaddslashes($personsvnr).", 
								       ersatzkennzeichen=".myaddslashes($personersatzkennzeichen).", 
								       familienstand=".myaddslashes($personfamilienstand).", 
								       anzahlkinder=".myaddslashes($personanzahlkinder).", 
								       aktiv=".myaddslashes($personaktiv?'true':'false').", 
								       insertamum=now(),
								       insertvon=".myaddslashes($personinsertvon).", 
								       updateamum=now(),
								       updatevon=".myaddslashes($personupdatevon).", 
								       geschlecht=".myaddslashes($persongeschlecht).", 
								       geburtsnation=".myaddslashes($persongeburtsnation).", 
								       staatsbuergerschaft=".myaddslashes($personstaatsbuergerschaft).", 
								       ext_id=".myaddslashes($personext_id)." 
								       WHERE person_id=".myaddslashes($personperson_id).";";
								$persondone=true;
								$ausgabe_person="Änderungen bei Person ".$personnachname." ".$personvorname.": ".$ausgabe_person."\n";
							}
						}
					}
				}
				if ($persondone)
				{
					if(pg_query($conn,$qry))
					{
						if($personnew)
						{
							$qryseq = "SELECT currval('tbl_person_person_id_seq') AS id;";
							if($rowseq=pg_fetch_object(pg_query($conn,$qryseq)))
								$personperson_id=$rowseq->id;
							else
							{					
								$error_log.= "Sequence konnte nicht ausgelesen werden\n";
								$error=true;
							}
						}				
					}
					else
					{			
						$error_log.= "*****\nFehler beim Speichern des Person-Datensatzes: ".$personnachname."\n".$qry."\n".pg_errormessage($conn)."\n*****\n";
						$error=true;
						$ausgabe_person="";
					}
				}
				$mitarbeiterdone=false;
				if(!$error)
				{
					//Benutzer schon vorhanden?
					$qry="SELECT uid, person_id FROM public.tbl_benutzer WHERE person_id='$personperson_id'";
					if($resultu = pg_query($conn, $qry))
					{
						if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
						{
							if($rowu=pg_fetch_object($resultu))
							{
								$benutzernew=false;	
								$benutzeruid=$rowu->uid;	
							}
							else $benutzernew=true;
						}
						else $benutzernew=true;
					}
					else
					{
						$error=true;
						$error_log.='Fehler beim Zugriff auf Tabelle tbl_benutzer bei person_id: '.$personperson_id;	
					}
					//echo nl2br("\n".$benutzeruid." / ".$personnachname."\n");
					if($benutzernew)
					{
						$qry = "INSERT INTO public.tbl_benutzer (uid, aktiv, alias, person_id, insertamum, insertvon, updateamum, updatevon) VALUES(".
							myaddslashes($benutzeruid).", ".
							myaddslashes($benutzeraktiv?'true':'false').", ".
							myaddslashes($benutzeralias).", ".
							myaddslashes($personperson_id).", 
							now(), ".
							myaddslashes($benutzerinsertvon).", 
							now(), ".
							myaddslashes($benutzerupdatevon).");";
							$ausgabe_benutzer="Benutzer ".$benutzeruid." ".$benutzeralias." eingefügt.";
					}
					else
					{	
						$qryu="SELECT * FROM public.tbl_benutzer WHERE uid='$benutzeruid';";
						$updateb=false;
						if($resultu = pg_query($conn, $qryu))
						{
							if($rowu = pg_fetch_object($resultu))
							{
								if($rowu->aktiv!=($benutzeraktiv?'t':'f'))
								{
									$updateb=true;
									if(strlen(trim($ausgabe_benutzer))>0)
									{
										$ausgabe_benutzer.=", Aktiv: '".($benutzeraktiv?'true':'false')."'";
									}
									else
									{
										$ausgabe_benutzer="Aktiv: '".($benutzeraktiv?'true':'false')."'";
									}
								}		
								if($rowu->alias!=$benutzeralias)
								{
									$updateb=true;
									if(strlen(trim($ausgabe_benutzer))>0)
									{
										$ausgabe_benutzer.=", Alias: ,".$benutzeralias."'";
									}
									else
									{
										$ausgabe_benutzer="Alias: '".$benutzeralias."'";
									}
								}
								if($rowu->person_id!=$personperson_id)
								{
									$updateb=true;
									if(strlen(trim($ausgabe_benutzer))>0)
									{
										$ausgabe_benutzer.=", PersonID: '".$personperson_id."'";
									}
									else
									{
										$ausgabe_benutzer="PersonID: '".$personperson_id."'";
									}
								}
							}
						}
						if($updateb)
						{
							$qry = "UPDATE public.tbl_benutzer SET
							       aktiv=".myaddslashes($benutzeraktiv?'true':'false').", 
							       alias=".myaddslashes($benutzeralias).", 
							       person_id=".myaddslashes($personperson_id).", 
							       updateamum=now(), 
							       updatevon=".myaddslashes($benutzerupdatevon).",
							       insertamum=now(), 
							       insertvon=".myaddslashes($benutzerinsertvon)." 
							       WHERE uid='$benutzeruid';";
							$ausgabe_benutzer="Änderungen bei Benutzer ".$benutzeruid." ".$benutzeralias.": ".$ausgabe_benutzer."\n";
						}
					}
					if(!pg_query($conn,$qry))
					{		
						$error_log.= "*****\nFehler beim Speichern des Benutzer-Datensatzes: ".$personnachname."\n".$qry."\n".pg_errormessage($conn)."\n*****\n";
						$error=true;
						$ausgabe_benutzer='';
					}
					if(!$error)
					{								
						//überprüfen, ob eintrag in syncperson schon vorhanden
						$qryz="SELECT person_fas FROM sync.tbl_syncperson WHERE person_fas='$personext_id' AND person_portal='$personperson_id'";
						if($resultz = pg_query($conn, $qryz))
						{
							if(pg_num_rows($resultz)==0) //wenn dieser eintrag noch nicht vorhanden ist => hinzufügen
							{
								$qry='INSERT INTO sync.tbl_syncperson (person_fas, person_portal)'.
									'VALUES ('.$personext_id.', '.$personperson_id.');';
								pg_query($conn, $qry);
							}
						}
						//mitarbeiter
						$qry2="SELECT * FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid='$mitarbeiteruid'";
						if($result2 = pg_query($conn, $qry2))
						{		
							if(pg_num_rows($result2)>0) //wenn dieser eintrag schon vorhanden ist
							{
								if($row2=pg_fetch_object($result2))
								{
									//Mitarbeiterdaten updaten
									$mitarbeiternew=false;
									$mitarbeiterperson_id=$personperson_id;
									$mitarbeiterort_kurzbz=$row2->ort_kurzbz;
									$mitarbeiterlektor=($row2->lektor=='t'?true:false);
									$mitarbeiterfixangestellt=($row2->fixangestellt=='t'?true:false);
									$mitarbeitertelefonklappe=$row2->telefonklappe;
								}
							}
							else 
							{
								//Mitarbeiter neu anlegen
								$mitarbeiternew=true;
							}
						}
						else 
						{
							$error_log.="Mitarbeiter von $rowall->uid konnte nicht gefunden werden\n";
							$error=true;	
						}
						if(!$error)
						{
							if($mitarbeiternew)
							{
					
								//Neuen Datensatz anlegen							
								$qry = "INSERT INTO public.tbl_mitarbeiter(mitarbeiter_uid, ausbildungcode, personalnummer, kurzbz, lektor, ort_kurzbz, fixangestellt, telefonklappe, anmerkung, updateamum, updatevon, insertamum, insertvon, ext_id) VALUES(".
								myaddslashes($mitarbeiteruid).", ".
								myaddslashes($mitarbeiterausbildungcode).", ".
								myaddslashes($mitarbeiterpersonalnummer)." , ".
								myaddslashes($mitarbeiterkurzbz)." , ".
								myaddslashes($mitarbeiterlektor?'true':'false').", ".
								myaddslashes($mitarbeiterort_kurzbz).", ".
								myaddslashes($mitarbeiterfixangestellt?'true':'false').", ".
								myaddslashes($mitarbeitertelefonklappe)." , ".
								myaddslashes($mitarbeiteranmerkung).", 
								now(), ".
								myaddslashes($mitarbeiterupdatevon)." , 
								now(), ".
								myaddslashes($mitarbeiterinsertvon)." , ".
								myaddslashes($mitarbeiterext_id).");";
								$ausgabe_mitarbeiter="Mitarbeiter ".$mitarbeiterpersonalnummer." ".$mitarbeiterkurzbz." eingefügt.";
							}
							else
							{
								//Bestehenden Datensatz updaten
								$qry="SELECT * FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid='$mitarbeiteruid'";
								$updatem=false;
								if($resultu = pg_query($conn, $qry))
								{
									if($rowu = pg_fetch_object($resultu))
									{
										if($rowu->ausbildungcode!=$mitarbeiterausbildungcode)
										{
											$updatem=true;
											if(strlen(trim($ausgabe_mitarbeiter))>0)
											{
												$ausgabe_mitarbeiter.=", Ausbildungscode: '".$mitarbeiterausbildungcode."'";
											}
											else
											{
												$ausgabe_mitarbeiter="Ausbildungscode: '".$mitarbeiterausbildungcode."'";
											}
										}
										if($rowu->personalnummer!=$mitarbeiterpersonalnummer)
										{
											$updatem=true;
											if(strlen(trim($ausgabe_mitarbeiter))>0)
											{
												$ausgabe_mitarbeiter.=", Personalnummer: '".$mitarbeiterpersonalnummer."'";
											}
											else
											{
												$ausgabe_mitarbeiter="Personalnummer: '".$mitarbeiterpersonalnummer."'";
											}
										}
										if($rowu->kurzbz!=$mitarbeiterkurzbz)
										{
											$updatem=true;
											if(strlen(trim($ausgabe_mitarbeiter))>0)
											{
												$ausgabe_mitarbeiter.=", Kurzbezeichnung: '".$mitarbeiterkurzbz."'";
											}
											else
											{
												$ausgabe_mitarbeiter="Kurzbezeichnung: '".$mitarbeiterkurzbz."'";
											}
										}
										/*if($rowu->lektor!=($mitarbeiterlektor?'t':'f'))
										{
											$updatem=true;
											if(strlen(trim($ausgabe_mitarbeiter))>0)
											{
												$ausgabe_mitarbeiter.=", Lektor: '".($mitarbeiterlektor?'true':'false')."'";
											}
											else
											{
												$ausgabe_mitarbeiter="Lektor: '".($mitarbeiterlektor?'true':'false')."'";
											}
										}*/
										/*if($rowu->fixangestellt!=($mitarbeiterfixangestellt?'t':'f'))
										{
											$updatem=true;
											if(strlen(trim($ausgabe_mitarbeiter))>0)
											{
												$ausgabe_mitarbeiter.=", Fixangestellt: '".($mitarbeiterfixangestellt?'true':'false')."'";
											}
											else
											{
												$ausgabe_mitarbeiter="Fixangestellt: '".($mitarbeiterfixangestellt?'true':'false')."'";
											}
										}*/
										if($rowu->telefonklappe!=$mitarbeitertelefonklappe)
										{
											$updatem=true;
											if(strlen(trim($ausgabe_mitarbeiter))>0)
											{
												$ausgabe_mitarbeiter.=", Telefonklappe: '".$mitarbeitertelefonklappe."'";
											}
											else
											{
												$ausgabe_mitarbeiter="Telefonklappe: '".$mitarbeitertelefonklappe."'";
											}
										}
										if($rowu->ort_kurzbz!=$mitarbeiterort_kurzbz)
										{
											$updatem=true;
											if(strlen(trim($ausgabe_mitarbeiter))>0)
											{
												$ausgabe_mitarbeiter.=", Ortkurzbezeichnung: '".$mitarbeiterort_kurzbz."'";
											}
											else
											{
												$ausgabe_mitarbeiter="Ortkurzbezeichnung: '".$mitarbeiterort_kurzbz."'";
											}
										}
										if($row->anmerkung!=$mitarbeiteranmerkung)
										{
											$updatem=true;
											if(strlen(trim($ausgabe_mitarbeiter))>0)
											{
												$ausgabe_person.=", Anmerkung: '".$mitarbeiteranmerkung."'";
											}
											else
											{
												$ausgabe_person="Anmerkung: '".$mitarbeiteranmerkung."'";
											}
										}
									}
								}
								if($updatem)
								{
									$qry ="UPDATE public.tbl_mitarbeiter SET 
									ausbildungcode=".myaddslashes($mitarbeiterausbildungcode).", 
									personalnummer=".myaddslashes($mitarbeiterpersonalnummer).", 
									kurzbz=".myaddslashes($mitarbeiterkurzbz).", 
									lektor=".myaddslashes($rowu->lektor).", 
									fixangestellt=".myaddslashes($rowu->fixangestellt).", 
									telefonklappe=".myaddslashes($mitarbeitertelefonklappe).", 
									ort_kurzbz=".myaddslashes($mitarbeiterort_kurzbz).", 
									anmerkung=".myaddslashes($mitarbeiteranmerkung).", 
									updateamum=now(), 
									updatevon=".myaddslashes($mitarbeiterupdatevon).", 
									insertamum=now(), 
									insertvon=".myaddslashes($mitarbeiterinsertvon).", 
									ext_id=".myaddslashes($mitarbeiterext_id)." 
									WHERE mitarbeiter_uid='$mitarbeiteruid';";
									$ausgabe_mitarbeiter="Änderungen bei Mitarbeiter ".$mitarbeiteruid.": ".$ausgabe_mitarbeiter."\n";
								}
							}
							if(!pg_query($conn,$qry))
							{		
								$error_log.= "*****\nFehler beim Speichern des Mitarbeiter-Datensatzes: ".$personnachname."\n".$qry."\n".pg_errormessage($conn)."\n*****\n";
								$error=true;
								$ausgabe_mitarbeiter='';
							}
							//Benutzer anlegen
							if(!$error)
							{
								
								if ($personnew)
								{
									$anzahl_eingefuegt_person++;	
								}
								else 
								{
									if($updatep) 
									{
										$anzahl_geaendert_person++;
									}
								}
								
								if ($mitarbeiternew)
								{
									$anzahl_eingefuegt_mitarbeiter++;	
								}
								else 
								{
									if($updatem) 
									{
										$anzahl_geaendert_mitarbeiter++;
									}
								}
								if ($benutzernew)
								{
									$anzahl_eingefuegt_benutzer++;	
								}
								else 
								{
									if($updateb) 
									{
										$anzahl_geaendert_benutzer++;
									}
								}
								$ausgabe.=$ausgabe_person;
								$ausgabe.=$ausgabe_benutzer;
								$ausgabe.=$ausgabe_mitarbeiter;
								pg_query($conn, "COMMIT");								
							}
							else
							{
								$anzahl_fehler_mitarbeiter++;
								pg_query($conn, "ROLLBACK");
							}
						}
						else
						{
							$anzahl_fehler_mitarbeiter++;
							pg_query($conn, "ROLLBACK");
						}
					}
					else 
					{
						$anzahl_fehler_benutzer++;
						pg_query($conn, "ROLLBACK");
					}
							
				}
				else
				{
					$anzahl_fehler_person++;
					pg_query($conn, "ROLLBACK");
				}
			}
			else
			{
				$anzahl_fehler_person++;
				pg_query($conn, "ROLLBACK");
			}
		}
	}
	echo nl2br("\nabgeschlossen\n\n");
}
else
{
	$error_log.= '\nPersonendatensaetze konnten nicht geladen werden\n';
}



//echo nl2br($text);
echo nl2br("\n\nGesamt FAS: $anzahl_quelle\nPerson:     Eingefügt: $anzahl_eingefuegt_person / geaendert: $anzahl_geaendert_person / Fehler: $anzahl_fehler_person");
echo nl2br("\nBenutzer:     Eingefügt: $anzahl_eingefuegt_benutzer / geaendert: $anzahl_geaendert_benutzer / Fehler: $anzahl_fehler_benutzer");
echo nl2br("\nMitarbeiter:  Eingefügt: $anzahl_eingefuegt_mitarbeiter / geaendert: $anzahl_geaendert_mitarbeiter / Fehler: $anzahl_fehler_mitarbeiter");
echo nl2br("\nLog:\n".$error_log);
echo nl2br("\n=====\n".$ausgabe);
$ausgabe="Person Sync\n-------------\n\nGesamt FAS: $anzahl_quelle\nPerson:        Eingefügt: $anzahl_eingefuegt_person / geaendert: $anzahl_geaendert_person / Fehler: $anzahl_fehler_person"
."\nBenutzer:     Eingefügt: $anzahl_eingefuegt_benutzer / geaendert: $anzahl_geaendert_benutzer / Fehler: $anzahl_fehler_benutzer"
."\nMitarbeiter: Eingefügt: $anzahl_eingefuegt_mitarbeiter / geaendert: $anzahl_geaendert_mitarbeiter / Fehler: $anzahl_fehler_mitarbeiter.".$ausgabe;
$error_log="\n\n\nFehler:\n$error_log";
if(strlen(trim($error_log))>0)
{
	mail($adress, 'SYNC-Fehler Mitarbeiter', $error_log,"From: vilesci@technikum-wien.at");
}
mail($adress, 'SYNC Mitarbeiter', $ausgabe,"From: vilesci@technikum-wien.at");
?>
</body>
</html>