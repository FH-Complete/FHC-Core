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

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Personendatenkorrektur</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/vilesci_old.css" rel="stylesheet" type="text/css">
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
$qry1='';
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
			echo "<span style='font-color: Red;'>Fehler beim Speichern</span>";
		}
	}
}
$qry1='';

// OR (p1.ersatzkennzeichen=p2.ersatzkennzeichen AND p1.ersatzkennzeichen IS NOT NULL AND p1.ersatzkennzeichen<>'')

$qry = "SET CLIENT_ENCODING TO 'LATIN9';
SELECT p1.person_pk as personpk1, p1.familienname as familienname1, p1.vorname as vorname1, p1. anrede as anrede1,
p1.vornamen as vornamen1, p1.geschlecht as geschlecht1, p1.gebdat as gebdat1, p1.gebort as gebort1,
p1.staatsbuergerschaft as staatsbuergerschaft1, p1.familienstand as familienstand1, p1.svnr as svnr1,
p1.anzahlderkinder as anzahlderkinder1, p1.ersatzkennzeichen as ersatzkennzeichen1, p1.bemerkung as bemerkung1, p1.titel as titel1,
p1.uid as uid1, p1.gebnation as gebnation1, p1.postnomentitel as postnomentitel1, p1.bismelden as bismelden1,
p2.person_pk as personpk2, p2.familienname as familienname2, p2.vorname as vorname2, p2. anrede as anrede2,
p2.vornamen as vornamen2, p2.geschlecht as geschlecht2, p2.gebdat as gebdat2, p2.gebort as gebort2,
p2.staatsbuergerschaft as staatsbuergerschaft2, p2.familienstand as familienstand2, p2.svnr as svnr2,
p2.anzahlderkinder as anzahlderkinder2, p2.ersatzkennzeichen as ersatzkennzeichen2, p2.bemerkung as bemerkung2, p2.titel as titel2,
p2.uid as uid2, p2.gebnation as gebnation2, p2.postnomentitel as postnomentitel2, p2.bismelden as bismelden2
FROM person p1, person p2
WHERE p1.person_pk<p2.person_pk
AND (	(p1.svnr=p2.svnr AND p1.svnr IS NOT NULL AND p1.svnr<>'')
		)
AND (p1.familienname<>p2.familienname OR p1.vorname<>p2.vorname OR p1.anrede<>p2.anrede  OR p1.vornamen<>p2.vornamen

OR p1.geschlecht<>p2.geschlecht  OR p1.gebdat<>p2.gebdat  OR p1.gebort<>p2.gebort  OR p1.staatsbuergerschaft<>p2.staatsbuergerschaft
OR p1.familienstand<>p2.familienstand  OR p1.svnr<>p2.svnr  OR p1.anzahlderkinder<>p2.anzahlderkinder
OR p1.ersatzkennzeichen<>p2.ersatzkennzeichen OR p1.titel<>p2.titel  OR p1.gebnation<>p2.gebnation
OR p1.postnomentitel<>p2.postnomentitel) LIMIT 20;";
//ORDER BY p1.familienname, p1.person_pk;";

if($result = pg_query($conn_fas, $qry))
{
	echo "<table class='liste'><tr><th></th><th>person_pk</th><th>familienname</th><th>vorname</th><th>vornamen</th><th>anrede</th><th>geschlecht</th><th>gebdat</th><th>gebort</th><th>gebnation</th><th>staatsbürgerschaft</th><th>familienstand</th><th>svnr</th><th>anzahlderkinder</th><th>ersatzkennzeichen</th><th>titel</th><th>postnomentitel</th><th></th></tr>";
	while($row = pg_fetch_object($result))
	{
		$i++;
		echo "<tr class='liste".($i%2)."'>";
		echo "<form action=''  method='POST'>";
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
		echo "<td><input type='submit' value='Speichern'></td>";
		echo "</tr>";
		echo "</form>";
		echo "<tr class='liste".($i%2)."'>";
		echo "<form action=''  method='POST'>";
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
		echo "<td><input type='submit' value='Speichern'></td>";
		echo "</tr>";
		echo "</form>";
	}
}
?>
</body>
</html>