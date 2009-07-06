<?php
/* Copyright (C) 2008 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

		require_once('../../../config/vilesci.config.inc.php');
#		require_once('../../../include/basis_db.class.php');
#		if (!$db = new basis_db())
#				die('Es konnte keine Verbindung zum Server aufgebaut werden.');
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");


$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_fehler=0;
$i=0;
$qry1='';
$qry2='';

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Studentendatenkorrektur</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
</head>
<body>
<style>
TR.liste
{
	background-color: #D3DCE3;
}
TR.liste0
{
	background-color: #EEEEEE;
}
TR.liste1
{
	background-color: #DDDDDD;
}
</style>
<?php
$qry1 ='';
if(isset($_POST['person_pk']))
{
		$qry1.=(isset($_POST['familienname'])?(!empty($qry1)?',':'')."familienname='".addslashes(trim($_POST['familienname']))."'" :''); 
		$qry1.=(isset($_POST['vorname'])?(!empty($qry1)?',':'')."vorname='".addslashes(trim($_POST['vorname']))."'" :''); 
		$qry1.=(isset($_POST['anrede'])?(!empty($qry1)?',':'')."anrede='".addslashes(trim($_POST['anrede']))."'" :''); 
		$qry1.=(isset($_POST['vornamen'])?(!empty($qry1)?',':'')."vornamen='".addslashes(trim($_POST['vornamen']))."'" :''); 
		$qry1.=(isset($_POST['geschlecht'])?(!empty($qry1)?',':'')."geschlecht='".addslashes(trim($_POST['geschlecht']))."'" :''); 
		$qry1.=(isset($_POST['gebdat'])?(!empty($qry1)?',':'')."gebdat='".addslashes(trim($_POST['gebdat']))."'" :''); 
		$qry1.=(isset($_POST['gebort'])?(!empty($qry1)?',':'')."gebort='".addslashes(trim($_POST['gebort']))."'" :''); 
		$qry1.=(isset($_POST['staatsbuergerschaft'])?(!empty($qry1)?',':'')."staatsbuergerschaft='".addslashes(trim($_POST['staatsbuergerschaft']))."'" :''); 
		$qry1.=(isset($_POST['familienstand'])?(!empty($qry1)?',':'')."familienstand='".addslashes(trim($_POST['familienstand']))."'" :''); 
		$qry1.=(isset($_POST['svn'])?(!empty($qry1)?',':'')."svn='".addslashes(trim($_POST['svn']))."'" :''); 
		$qry1.=(isset($_POST['anzahlderkinder'])?(!empty($qry1)?',':'')."anzahlderkinder='".addslashes(trim($_POST['anzahlderkinder']))."'" :''); 
		$qry1.=(isset($_POST['ersatzkennzeichen'])?(!empty($qry1)?',':'')."ersatzkennzeichen='".addslashes(trim($_POST['ersatzkennzeichen']))."'" :''); 
		$qry1.=(isset($_POST['titel'])?(!empty($qry1)?',':'')."titel='".addslashes(trim($_POST['titel']))."'" :''); 
		$qry1.=(isset($_POST['gebnation'])?(!empty($qry1)?',':'')."gebnation='".addslashes(trim($_POST['gebnation']))."'" :''); 
		$qry1.=(isset($_POST['postnomentitel'])?(!empty($qry1)?',':'')."postnomentitel='".addslashes(trim($_POST['postnomentitel']))."'" :''); 
	if(strlen(trim($qry1))>0)
	{
		$qry = "UPDATE person SET ".$qry1. " WHERE person_pk=".$_POST['person_pk'];
		if(pg_query($conn_fas, $qry))
		{
			echo "Erfolgreich gespeichert: ".$qry;
		}
		else
		{
			echo "<span style='font-color: Red;'>Fehler beim Speichern Person</span>";
		}
	}
}

$qry2='';
if(isset($_POST['student']))
{
		$qry2.=(isset($_POST['zgv'])?(!empty($qry2)?',':'')."zgv='".addslashes(trim($_POST['zgv']))."'" :''); 
		$qry2.=(isset($_POST['zgvdatum'])?(!empty($qry2)?',':'')."zgvdatum='".addslashes(trim($_POST['zgvdatum']))."'" :''); 
		$qry2.=(isset($_POST['zgvort'])?(!empty($qry2)?',':'')."zgvort='".addslashes(trim($_POST['zgvort']))."'" :''); 
		$qry2.=(isset($_POST['zgvmagister'])?(!empty($qry2)?',':'')."zgvmagister='".addslashes(trim($_POST['zgvmagister']))."'" :''); 
		$qry2.=(isset($_POST['zgvmagisterdatum'])?(!empty($qry2)?',':'')."zgvmagisterdatum='".addslashes(trim($_POST['zgvmagisterdatum']))."'" :''); 
		$qry2.=(isset($_POST['zgvmagisterort'])?(!empty($qry2)?',':'')."zgvmagisterort='".addslashes(trim($_POST['zgvmagisterort']))."'" :''); 
		$qry2.=(isset($_POST['punkte'])?(!empty($qry2)?',':'')."punkte='".addslashes(trim($_POST['punkte']))."'" :''); 
		$qry2.=(isset($_POST['perskz'])?(!empty($qry2)?',':'')."perskz='".addslashes(trim($_POST['perskz']))."'" :''); 
		$qry2.=(isset($_POST['aufgenommenam'])?(!empty($qry2)?',':'')."aufgenommenam='".addslashes(trim($_POST['aufgenommenam']))."'" :''); 
		$qry2.=(isset($_POST['beendigungsdatum'])?(!empty($qry2)?',':'')."beendigungsdatum='".addslashes(trim($_POST['beendigungsdatum']))."'" :''); 
		$qry2.=(isset($_POST['aufmerksamdurch'])?(!empty($qry2)?',':'')."aufmerksamdurch='".addslashes(trim($_POST['aufmerksamdurch']))."'" :''); 
		$qry2.=(isset($_POST['aufnahmeschluessel'])?(!empty($qry2)?',':'')."aufnahmeschluessel='".addslashes(trim($_POST['aufnahmeschluessel']))."'" :''); 
		$qry2.=(isset($_POST['aufnahmeschluesselfk'])?(!empty($qry2)?',':'')."aufnahmeschluesselfk='".addslashes(trim($_POST['aufnahmeschluesselfk']))."'" :''); 

		$qry2.=(isset($_POST['berufstaetigkeit'])?(!empty($qry2)?',':'')."berufstaetigkeit='".addslashes(trim($_POST['berufstaetigkeit']))."'" :''); 
		$qry2.=(isset($_POST['angetreten'])?(!empty($qry2)?',':'')."angetreten='".addslashes(trim($_POST['angetreten']))."'" :''); 
	if(strlen(trim($qry2))>0)
	{
		$qry = "UPDATE student SET ".$qry2. " WHERE student_pk=".$_POST['student'];
		if(pg_query($conn_fas, $qry))
		{
			echo nl2br("\nErfolgreich gespeichert: ".$qry);
		}
		else
		{
			echo nl2br("<span style='font-color: Red;'>\nFehler beim Speichern Student</span>");
		}
	}
}

$qry1='';

// OR (p1.ersatzkennzeichen=p2.ersatzkennzeichen AND p1.ersatzkennzeichen IS NOT NULL AND p1.ersatzkennzeichen<>'')
$qry="
SELECT
p1.person_pk AS personpk1, p1.familienname AS familienname1, p1.vorname AS vorname1, p1.vornamen AS vornamen1, p1.geschlecht AS geschlecht1,
p1.gebdat AS gebdat1, p1.gebort AS gebort1, p1.staatsbuergerschaft AS staatsbuergerschaft1, p1.familienstand AS familienstand1,
p1.svnr AS svnr1, p1. ersatzkennzeichen  AS ersatzkennzeichen1, p1.anrede AS anrede1, p1.anzahlderkinder AS anzahlderkinder1,
p1.titel AS titel1,  p1.gebnation AS gebnation1, p1.postnomentitel AS postnomentitel1, p1.uid as uid1,
p1.student_pk AS student1, p1.zgv AS zgv1, p1.studiengang_fk AS studiengang1, p1.zgvdatum AS zgvdatum1, p1.zgvort AS zgvort1,
p1.zgvmagister AS zgvmagister1, p1.zgvmagisterort AS zgvmagisterort1, p1.zgvmagisterdatum AS zgvmagisterdatum1, p1.punkte AS punkte1,
p1.perskz AS perskz1, p1.aufgenommenam AS aufgenommenam1, p1.aufmerksamdurch AS aufmerksamdurch1, p1.berufstaetigkeit AS berufstaetigkeit1,
p1.beendigungsdatum AS beendigungsdatum1, p1.berufstaetigkeit AS berufstaetigkeit1, p1.aufmerksamdurch_fk AS aufmerksamdurchfk1,
p1.aufnahmeschluessel AS aufnahmeschluessel1, p1.aufnahmeschluessel_fk AS aufnahmeschluesselfk1, p1.angetreten AS angetreten1,
p2.person_pk AS personpk2, p2.familienname AS familienname2, p2.vorname AS vorname2, p2.vornamen AS vornamen2, p2.geschlecht AS geschlecht2,
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
	OR ((p1.svnr<>p2.svnr OR p1.svnr IS NOT NULL OR p1.svnr<>'') AND p1.familienname=p2.familienname AND p1.familienname IS NOT NULL AND p1.familienname!=''
	AND p1.gebdat=p2.gebdat AND p1.gebdat IS NOT NULL AND p1.gebdat>'1935-01-01' AND p1.gebdat<'2000-01-01'))
AND (p1.person_pk < p2.person_pk) AND (p1.studiengang_fk=p2.studiengang_fk)
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
	ORDER BY p1.familienname, p1.person_pk LIMIT 10;";

//ORDER BY p1.familienname, p1.person_pk;";

if($result = pg_query($conn_fas, $qry))
{
	echo "<table class='liste'><tr><th></th><th>person_pk</th><th>familienname</th><th>vorname</th><th>vornamen</th><th>anrede</th><th>geschlecht</th><th>gebdat</th><th>gebort</th><th>gebnation</th><th>staatsbürgerschaft</th><th>familienstand</th><th>svnr</th><th>anzahlderkinder</th><th>ersatzkennzeichen</th><th>titel</th><th>postnomentitel</th>
		<th>student_pk</th><th>studiengang</th><th>zgv</th><th>zgvdatum</th><th>zgvort</th><th>zgvmagister</th><th>zgvmagisterdatum</th><th>zgvmagisterort</th><th>punkte</th><th>perskz</th><th>aufgenommenam</th><th>beendigungsdatum</th><th>aufmerksamdurch</th><th>aufnahmeschluessel</th><th>aufnahmeschluessel_fk</th><th>berufstaetigkeit</th><th>angetreten</th><th></th></tr>";
	while($row = pg_fetch_object($result))
	{
		$i++;
		echo "<tr class='liste".($i%2)."'>";
		echo "<form action='$PHP_SELF'  method='POST'>";
		echo "<td><input type='submit' value='Speichern'></td>";
		echo "<td>'".$row->personpk1."'";
		echo "<input type='hidden' name='person_pk' value='".$row->personpk1."'>";
		echo "</td>";
		echo "<td>'".$row->familienname1."'";
		if($row->familienname1<>$row->familienname2)
			echo"<input type='text' size='20' maxlength='255' name='familienname' value='".$row->familienname1."'>";
		echo "</td>";
		echo "<td>'".$row->vorname1."'";
		if($row->vorname1<>$row->vorname2 || $row->vornamen1<>$row->vornamen2)
			echo"<input type='text' size='20' maxlength='255' name='vorname' value='".$row->vorname1."'>";
		echo "</td>";
		echo "<td>'".$row->vornamen1."'";
		if($row->vorname1<>$row->vorname2 || $row->vornamen1<>$row->vornamen2)
			echo"<input type='text' size='20' maxlength='255' name='vornamen' value='".$row->vornamen1."'>";
		echo "</td>";
		echo "<td>'".$row->anrede1."'";
		if($row->anrede1<>$row->anrede2)
			echo"<input type='text' size='10' maxlength='20' name='anrede' value='".$row->anrede1."'>";
		echo "</td>";
		echo "<td>'".$row->geschlecht1."'";
		if($row->geschlecht1<>$row->geschlecht2)
			echo"<input type='text' size='1' maxlength='1' name='geschlecht'  value='".$row->geschlecht1."'>";
		echo "</td>";
		echo "<td>'".$row->gebdat1."'";
		if($row->gebdat1<>$row->gebdat2)
			echo"<input type='text' size='10' maxlength='10' name='gebdat'  value='".$row->gebdat1."'>";
		echo "</td>";
		echo "<td>'".$row->gebort1."'";
		if($row->gebort1<>$row->gebort2)
			echo"<input type='text' size='20' maxlength='255' name='gebort'  value='".$row->gebort1."'>";
		echo "</td>";
		echo "<td>'".$row->gebnation1."'";
		if($row->gebnation1<>$row->gebnation2)
			echo"<input type='text' size='3' maxlength='3' name='gebnation'  value='".$row->gebnation1."'>";
		echo "</td>";
		echo "<td>'".$row->staatsbuergerschaft1."'";
		if($row->staatsbuergerschaft1<>$row->staatsbuergerschaft2)
			echo"<input type='text' size='3' maxlength='3' name='staatsbuergerschaft'  value='".$row->staatsbuergerschaft1."'>";
		echo "</td>";
		echo "<td>'".$row->familienstand1."'";
		if($row->familienstand1<>$row->familienstand2)
			echo"<input type='text' size='1' maxlength='1' name='familienstand'  value='".$row->familienstand1."'>";
		echo "</td>";
		echo "<td>'".$row->svnr1."'";
		if($row->svnr1<>$row->svnr2)
			echo"<input type='text' size='10' maxlength='10' name='svnr'  value='".$row->svnr1."'>";
		echo "</td>";
		echo "<td>'".$row->anzahlderkinder1."'";
		if($row->anzahlderkinder1<>$row->anzahlderkinder2)
			echo"<input type='text' size='1' maxlength='1' name='anzahlderkinder'  value='".$row->anzahlderkinder1."'>";
		echo "</td>";
		echo "<td>'".$row->ersatzkennzeichen1."'";
		if($row->ersatzkennzeichen1<>$row->ersatzkennzeichen2)
			echo"<input type='text' size='10' maxlength='10' name='ersatzkennzeichen'  value='".$row->ersatzkennzeichen1."'>";
		echo "</td>";
		echo "<td>'".$row->titel1."'";
		if($row->titel1<>$row->titel2 || $row->postnomentitel1<>$row->postnomentitel2)
			echo"<input type='text' size='20' maxlength='30' name='titel'  value='".$row->titel1."'>";
		echo "</td>";
		echo "<td>'".$row->postnomentitel1."'";
		if($row->titel1<>$row->titel2 || $row->postnomentitel1<>$row->postnomentitel2)
		echo"<input type='text' size='20' maxlength='30' name='postnomentitel'  value='".$row->postnomentitel1."'>";
		echo "</td>";
		echo "<td>'".$row->student1."'";
		echo "<input type='hidden' name='student' value='".$row->student1."'>";
		echo "</td>";
		echo "<td>'".$row->studiengang1."'";
		//if($row->studiengang1<>$row->studiengang2)
		//	echo"<input type='text' size='2' maxlength='2' name='studiengang'  value='".$row->studiengang1."'>";
		echo "</td>";
		echo "<td>'".$row->zgv1."'";
		if($row->zgv1<>$row->zgv2)
			echo"<input type='text' size='2' maxlength='2' name='zgv'  value='".$row->zgv1."'>";
		echo "</td>";
		echo "<td>'".$row->zgvdatum1."'";
		if($row->zgvdatum1<>$row->zgvdatum2)
			echo"<input type='text' size='10' maxlength='10' name='zgvdatum'  value='".$row->zgvdatum1."'>";
		echo "</td>";
		echo "<td>'".$row->zgvort1."'";
		if($row->zgvort1<>$row->zgvort2)
			echo"<input type='text' size='20' maxlength='255' name='zgvort'  value='".$row->zgvort1."'>";
		echo "</td>";
		echo "<td>'".$row->zgvmagister1."'";
		if($row->zgvmagister1<>$row->zgvmagister2)
			echo"<input type='text' size='2' maxlength='2' name='zgvmagister'  value='".$row->zgvmagister1."'>";
		echo "</td>";
		echo "<td>'".$row->zgvmagisterdatum1."'";
		if($row->zgvmagisterdatum1<>$row->zgvmagisterdatum2)
			echo"<input type='text' size='10' maxlength='10' name='zgvmagisterdatum'  value='".$row->zgvmagisterdatum1."'>";
		echo "</td>";
		echo "<td>'".$row->zgvmagisterort1."'";
		if($row->zgvmagisterort1<>$row->zgvmagisterort2)
			echo"<input type='text' size='20' maxlength='255' name='zgvmagisterort'  value='".$row->zgvmagisterort1."'>";
		echo "</td>";
		echo "<td>'".$row->punkte1."'";
		if($row->punkte1<>$row->punkte2)
			echo"<input type='text' size='4' maxlength='4' name='punkte'  value='".$row->punkte1."'>";
		echo "</td>";
		echo "<td>'".$row->perskz1."'";
		if($row->perskz1<>$row->perskz2)
			echo"<input type='text' size='10' maxlength='12' name='perskz'  value='".$row->perskz1."'>";
		echo "</td>";
		echo "<td>'".$row->aufgenommenam1."'";
		if($row->aufgenommenam1<>$row->aufgenommenam2)
			echo"<input type='text' size='10' maxlength='10' name='aufgenommenam'  value='".$row->aufgenommenam1."'>";
		echo "</td>";
		echo "<td>'".$row->beendigungsdatum1."'";
		if($row->beendigungsdatum1<>$row->beendigungsdatum2)
			echo"<input type='text' size='10' maxlength='10' name='beendigungsdatum'  value='".$row->beendigungsdatum1."'>";
		echo "</td>";
		echo "<td>'".$row->aufmerksamdurch1."'";
		if($row->aufmerksamdurch1<>$row->aufmerksamdurch2)
			echo"<input type='text' size='2' maxlength='2' name='aufmerksamdurch'  value='".$row->aufmerksamdurch1."'>";
		echo "</td>";
		echo "<td>'".$row->aufnahmeschluessel1."'";
		if($row->aufnahmeschluessel1<>$row->aufnahmeschluessel2)
			echo"<input type='text' size='20' maxlength='255' name='aufnahmeschluessel'  value='".$row->aufnahmeschluessel1."'>";
		echo "</td>";
		echo "<td>'".$row->aufnahmeschluesselfk1."'";
		if($row->aufnahmeschluesselfk1<>$row->aufnahmeschluesselfk2)
			echo"<input type='text' size='2' maxlength='2' name='aufnahmeschluesselfk'  value='".$row->aufnahmeschluesselfk1."'>";
		echo "</td>";
		echo "<td>'".$row->berufstaetigkeit1."'";
		if($row->berufstaetigkeit1<>$row->berufstaetigkeit2)
			echo"<input type='text' size='2' maxlength='2' name='berufstaetigkeit'  value='".$row->berufstaetigkeit1."'>";
		echo "</td>";
		echo "<td>'".$row->angetreten1."'";
		if($row->angetreten1<>$row->angetreten2)
			echo"<input type='text' size='2' maxlength='2' name='angetreten'  value='".$row->angetreten1."'>";
		echo "</td>";
		echo "<td><input type='submit' value='Speichern'></td>";
		echo "</tr>";
		echo "</form>";
		echo "<tr class='liste".($i%2)."'>";
		echo "<form action='$PHP_SELF'  method='POST'>";
		echo "<td><input type='submit' value='Speichern'></td>";
		echo "<td>'".$row->personpk2."'";
		echo "<input type='hidden' name='person_pk' value='".$row->personpk2."'>";
		echo "</td>";
		echo "<td>'".$row->familienname2."'";
		if($row->familienname1<>$row->familienname2)
			echo"<input type='text' size='20' maxlength='255' name='familienname' value='".$row->familienname2."'>";
		echo "</td>";
		echo "<td>'".$row->vorname2."'";
		if($row->vorname1<>$row->vorname2 || $row->vornamen1<>$row->vornamen2)
			echo"<input type='text' size='20' maxlength='255' name='vorname' value='".$row->vorname2."'>";
		echo "</td>";
		echo "<td>'".$row->vornamen2."'";
		if($row->vorname1<>$row->vorname2 || $row->vornamen1<>$row->vornamen2)
			echo"<input type='text' size='20' maxlength='255' name='vornamen' value='".$row->vornamen2."'>";
		echo "</td>";
		echo "<td>'".$row->anrede2."'";
		if($row->anrede1<>$row->anrede2)
			echo"<input type='text' size='10' maxlength='20' name='anrede' value='".$row->anrede2."'>";
		echo "</td>";
		echo "<td>'".$row->geschlecht2."'";
		if($row->geschlecht1<>$row->geschlecht2)
			echo"<input type='text' size='1' maxlength='1' name='geschlecht'  value='".$row->geschlecht2."'>";
		echo "</td>";
		echo "<td>'".$row->gebdat2."'";
		if($row->gebdat1<>$row->gebdat2)
			echo"<input type='text' size='10' maxlength='10' name='gebdat'  value='".$row->gebdat2."'>";
		echo "</td>";
		echo "<td>'".$row->gebort2."'";
		if($row->gebort1<>$row->gebort2)
			echo"<input type='text' size='20' maxlength='255' name='gebort'  value='".$row->gebort2."'>";
		echo "</td>";
		echo "<td>'".$row->gebnation2."'";
		if($row->gebnation1<>$row->gebnation2)
			echo"<input type='text' size='3' maxlength='3' name='gebnation'  value='".$row->gebnation2."'>";
		echo "</td>";
		echo "<td>'".$row->staatsbuergerschaft2."'";
		if($row->staatsbuergerschaft1<>$row->staatsbuergerschaft2)
			echo"<input type='text' size='3' maxlength='3' name='staatsbuergerschaft'  value='".$row->staatsbuergerschaft2."'>";
		echo "</td>";
		echo "<td>'".$row->familienstand2."'";
		if($row->familienstand1<>$row->familienstand2)
			echo"<input type='text' size='1' maxlength='1' name='familienstand'  value='".$row->familienstand2."'>";
		echo "</td>";
		echo "<td>'".$row->svnr2."'";
		if($row->svnr1<>$row->svnr2)
			echo"<input type='text' size='10' maxlength='10' name='svnr'  value='".$row->svnr2."'>";
		echo "</td>";
		echo "<td>'".$row->anzahlderkinder2."'";
		if($row->anzahlderkinder1<>$row->anzahlderkinder2)
			echo"<input type='text' size='1' maxlength='1' name='anzahlderkinder'  value='".$row->anzahlderkinder2."'>";
		echo "</td>";
		echo "<td>'".$row->ersatzkennzeichen2."'";
		if($row->ersatzkennzeichen1<>$row->ersatzkennzeichen2)
			echo"<input type='text' size='10' maxlength='10' name='ersatzkennzeichen'  value='".$row->ersatzkennzeichen2."'>";
		echo "</td>";
		echo "<td>'".$row->titel2."'";
		if($row->titel1<>$row->titel2 || $row->postnomentitel1<>$row->postnomentitel2)
			echo"<input type='text' size='20' maxlength='30' name='titel'  value='".$row->titel2."'>";
		echo "</td>";
		echo "<td>'".$row->postnomentitel2."'";
		if($row->titel1<>$row->titel2 || $row->postnomentitel1<>$row->postnomentitel2)
			echo"<input type='text' size='20' maxlength='30' name='postnomentitel'  value='".$row->postnomentitel2."'>";
		echo "</td>";
		echo "<td>'".$row->student2."'";
		echo "<input type='hidden' name='student' value='".$row->student2."'>";
		echo "</td>";
		echo "<td>'".$row->studiengang2."'";
		//if($row->studiengang1<>$row->studiengang2)
		//	echo"<input type='text' size='2' maxlength='2' name='studiengang'  value='".$row->studiengang2."'>";
		echo "</td>";
		echo "<td>'".$row->zgv2."'";
		if($row->zgv1<>$row->zgv2)
			echo"<input type='text' size='2' maxlength='2' name='zgv'  value='".$row->zgv2."'>";
		echo "</td>";
		echo "<td>'".$row->zgvdatum2."'";
		if($row->zgvdatum1<>$row->zgvdatum2)
			echo"<input type='text' size='10' maxlength='10' name='zgvdatum'  value='".$row->zgvdatum2."'>";
		echo "</td>";
		echo "<td>'".$row->zgvort2."'";
		if($row->zgvort1<>$row->zgvort2)
			echo"<input type='text' size='20' maxlength='255' name='zgvort'  value='".$row->zgvort2."'>";
		echo "</td>";
		echo "<td>'".$row->zgvmagister2."'";
		if($row->zgvmagister1<>$row->zgvmagister2)
			echo"<input type='text' size='2' maxlength='2' name='zgvmagister'  value='".$row->zgvmagister2."'>";
		echo "</td>";
		echo "<td>'".$row->zgvmagisterdatum2."'";
		if($row->zgvmagisterdatum1<>$row->zgvmagisterdatum2)
			echo"<input type='text' size='10' maxlength='10' name='zgvmagisterdatum'  value='".$row->zgvmagisterdatum2."'>";
		echo "</td>";
		echo "<td>'".$row->zgvmagisterort2."'";
		if($row->zgvmagisterort1<>$row->zgvmagisterort2)
			echo"<input type='text' size='20' maxlength='255' name='zgvmagisterort'  value='".$row->zgvmagisterort2."'>";
		echo "</td>";
		echo "<td>'".$row->punkte2."'";
		if($row->punkte1<>$row->punkte2)
			echo"<input type='text' size='4' maxlength='4' name='punkte'  value='".$row->punkte2."'>";
		echo "</td>";
		echo "<td>'".$row->perskz2."'";
		if($row->perskz1<>$row->perskz2)
			echo"<input type='text' size='10' maxlength='12' name='perskz'  value='".$row->perskz2."'>";
		echo "</td>";
		echo "<td>'".$row->aufgenommenam2."'";
		if($row->aufgenommenam1<>$row->aufgenommenam2)
			echo"<input type='text' size='10' maxlength='10' name='aufgenommenam'  value='".$row->aufgenommenam2."'>";
		echo "</td>";
		echo "<td>'".$row->beendigungsdatum2."'";
		if($row->beendigungsdatum1<>$row->beendigungsdatum2)
			echo"<input type='text' size='10' maxlength='10' name='beendigungsdatum'  value='".$row->beendigungsdatum2."'>";
		echo "</td>";
		echo "<td>'".$row->aufmerksamdurch2."'";
		if($row->aufmerksamdurch1<>$row->aufmerksamdurch2)
			echo"<input type='text' size='2' maxlength='2' name='aufmerksamdurch'  value='".$row->aufmerksamdurch2."'>";
		echo "</td>";
		echo "<td>'".$row->aufnahmeschluessel2."'";
		if($row->aufnahmeschluessel1<>$row->aufnahmeschluessel2)
			echo"<input type='text' size='20' maxlength='255' name='aufnahmeschluessel'  value='".$row->aufnahmeschluessel2."'>";
		echo "</td>";
		echo "<td>'".$row->aufnahmeschluesselfk2."'";
		if($row->aufnahmeschluesselfk1<>$row->aufnahmeschluesselfk2)
			echo"<input type='text' size='2' maxlength='2' name='aufnahmeschluesselfk'  value='".$row->aufnahmeschluesselfk2."'>";
		echo "</td>";
		echo "<td>'".$row->berufstaetigkeit2."'";
		if($row->berufstaetigkeit1<>$row->berufstaetigkeit2)
			echo"<input type='text' size='2' maxlength='2' name='berufstaetigkeit'  value='".$row->berufstaetigkeit2."'>";
		echo "</td>";
		echo "<td>'".$row->angetreten2."'";
		if($row->angetreten1<>$row->angetreten2)
			echo"<input type='text' size='2' maxlength='2' name='angetreten'  value='".$row->angetreten2."'>";
		echo "</td>";
		echo "<td><input type='submit' value='Speichern'></td>";
		echo "</tr>";
		echo "</form>";
	}
}
?>
</body>
</html>