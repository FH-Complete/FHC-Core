<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

include('../../vilesci/config.inc.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
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
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-9">
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
if(isset($_POST['person_pk']))
{
	if(isset($_POST['familienname']) )
	{
		if(strlen(trim($qry1))>0)
		{
			$qry1.= ", familienname='".$_POST['familienname']."'";
		}
		else 
		{
			$qry1= "familienname='".$_POST['familienname']."'";
		}
	}
	if(isset($_POST['vorname']) )
	{
		if(strlen(trim($qry1))>0)
		{
			$qry1.= ", vorname='".$_POST['vorname']."'";
		}
		else 
		{
			$qry1= "vorname='".$_POST['vorname']."'";
		}
	}
	if(isset($_POST['anrede']) )
	{
		if(strlen(trim($qry1))>0)
		{
			$qry1.= ", anrede='".$_POST['anrede']."'";
		}
		else 
		{
			$qry1= "anrede='".$_POST['anrede']."'";
		}
	}
	if(isset($_POST['vornamen']) )
	{
		if(strlen(trim($qry1))>0)
		{
			$qry1.= ", vornamen='".$_POST['vornamen']."'";
		}
		else 
		{
			$qry1= "vornamen='".$_POST['vornamen']."'";
		}
	}
	if(isset($_POST['geschlecht']) )
	{
		if(strlen(trim($qry1))>0)
		{
			$qry1.= ", geschlecht='".$_POST['geschlecht']."'";
		}
		else 
		{
			$qry1= "geschlecht='".$_POST['geschlecht']."'";
		}
	}
	if(isset($_POST['gebdat']) )
	{
		if(strlen(trim($qry1))>0)
		{
			$qry1.= ", gebdat='".$_POST['gebdat']."'";
		}
		else 
		{
			$qry1= "gebdat='".$_POST['gebdat']."'";
		}
	}
	if(isset($_POST['gebort']) )
	{
		if(strlen(trim($qry1))>0)
		{
			$qry1.= ", gebort='".$_POST['gebort']."'";
		}
		else 
		{
			$qry1= "gebort='".$_POST['gebort']."'";
		}
	}
	if(isset($_POST['staatsbuergerschaft']) )
	{
		if(strlen(trim($qry1))>0)
		{
			$qry1.= ", staatsbuergerschaft='".$_POST['staatsbuergerschaft']."'";
		}
		else 
		{
			$qry1= "staatsbuergerschaft='".$_POST['staatsbuergerschaft']."'";
		}
	}
	if(isset($_POST['familienstand']) )
	{
		if(strlen(trim($qry1))>0)
		{
			$qry1.= ", familienstand='".$_POST['familienstand']."'";
		}
		else 
		{
			$qry1= "familienstand='".$_POST['familienstand']."'";
		}
	}
	if(isset($_POST['svnr']) )
	{
		if(strlen(trim($qry1))>0)
		{
			$qry1.= ", svnr='".$_POST['svnr']."'";
		}
		else 
		{
			$qry1= "svnr='".$_POST['svnr']."'";
		}
	}
	if(isset($_POST['anzahlderkinder']) )
	{
		if(strlen(trim($qry1))>0)
		{
			$qry1.= ", anzahlderkinder='".$_POST['anzahlderkinder']."'";
		}
		else 
		{
			$qry1= "anzahlderkinder='".$_POST['anzahlderkinder']."'";
		}
	}
	if(isset($_POST['ersatzkennzeichen']) )
	{
		if(strlen(trim($qry1))>0)
		{
			$qry1.= ", ersatzkennzeichen='".$_POST['ersatzkennzeichen']."'";
		}
		else 
		{
			$qry1= "ersatzkennzeichen='".$_POST['ersatzkennzeichen']."'";
		}
	}
	if(isset($_POST['titel']) )
	{
		if(strlen(trim($qry1))>0)
		{
			$qry1.= ", titel='".$_POST['titel']."'";
		}
		else 
		{
			$qry1= "titel='".$_POST['titel']."'";
		}
	}
	if(isset($_POST['gebnation']) )
	{
		if(strlen(trim($qry1))>0)
		{
			$qry1.= ", gebnation='".$_POST['gebnation']."'";
		}
		else 
		{
			$qry1= "gebnation='".$_POST['gebnation']."'";
		}
	}
	if(isset($_POST['postnomentitel']) )
	{
		if(strlen(trim($qry1))>0)
		{
			$qry1.= ", postnomentitel='".$_POST['postnomentitel']."'";
		}
		else 
		{
			$qry1= "postnomentitel='".$_POST['postnomentitel']."'";
		}
	}
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
FROM person p1, person p2 WHERE p1.person_pk<p2.person_pk AND p1.svnr=p2.svnr AND p1.svnr IS NOT NULL
AND p1.svnr<>'0005010400' AND p1.svnr<>''
AND (p1.familienname<>p2.familienname OR p1.vorname<>p2.vorname OR p1.anrede<>p2.anrede  OR p1.vornamen<>p2.vornamen 
OR p1.geschlecht<>p2.geschlecht  OR p1.gebdat<>p2.gebdat  OR p1.gebort<>p2.gebort  OR p1.staatsbuergerschaft<>p2.staatsbuergerschaft
OR p1.familienstand<>p2.familienstand  OR p1.svnr<>p2.svnr  OR p1.anzahlderkinder<>p2.anzahlderkinder
OR p1.ersatzkennzeichen<>p2.ersatzkennzeichen OR p1.titel<>p2.titel  OR p1.gebnation<>p2.gebnation 
OR p1.postnomentitel<>p2.postnomentitel) ORDER BY p1.familienname, p1.person_pk;";

if($result = pg_query($conn_fas, $qry))
{
	echo "<table class='liste'><tr><th>person_pk</th><th>familienname</th><th>vorname</th><th>anrede</th><th>vornamen</th><th>geschlecht</th><th>gebdat</th><th>gebort</th><th>gebnation</th><th>staatsbürgerschaft</th><th>familienstand</th><th>svnr</th><th>anzahlderkinder</th><th>ersatzkennzeichen</th><th>titel</th><th>postnomentitel</th></tr>";
	while($row = pg_fetch_object($result))
	{
		$i++;
		echo "<tr class='liste".($i%2)."'>";
		echo "<form action='$PHP_SELF'  method='POST'>";
		echo "<td>'".$row->personpk1."'";
		echo "<input type='hidden' name='person_pk' value='".$row->personpk1."'>";
		echo "</td>";
		echo "<td>'".$row->familienname1."'";
		if($row->familienname1<>$row->familienname2)
			echo"<input type='text' size='20' maxlength='255' name='familienname' value='".$row->familienname1."'>";
		echo "</td>";
		echo "<td>'".$row->vorname1."'";
		if($row->vorname1<>$row->vorname2)
			echo"<input type='text' size='20' maxlength='255' name='vorname' value='".$row->vorname1."'>";
		echo "</td>";
		echo "<td>'".$row->anrede1."'";
		if($row->anrede1<>$row->anrede2)
			echo"<input type='text' size='10' maxlength='20' name='anrede' value='".$row->anrede1."'>";
		echo "</td>";
		echo "<td>'".$row->vornamen1."'";
		if($row->vornamen1<>$row->vornamen2)
			echo"<input type='text' size='20' maxlength='255' name='vornamen' value='".$row->vornamen1."'>";
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
			echo"<input type='text' size='1' maxlength='1' name='geschlecht'  value='".$row->anzahlderkinder1."'>";
		echo "</td>";
		echo "<td>'".$row->ersatzkennzeichen1."'";
		if($row->ersatzkennzeichen1<>$row->ersatzkennzeichen2)
			echo"<input type='text' size='10' maxlength='10' name='ersatzkennzeichen'  value='".$row->ersatzkennzeichen1."'>";
		echo "</td>";
		echo "<td>'".$row->titel1."'";
		if($row->titel1<>$row->titel2)
			echo"<input type='text' size='20' maxlength='30' name='titel'  value='".$row->titel1."'>";
		echo "</td>";
		echo "<td>'".$row->postnomentitel1."'";
		if($row->postnomentitel1<>$row->postnomentitel2)
			echo"<input type='text' size='20' maxlength='30' name='postnomentitel'  value='".$row->postnomentitel1."'>";
		echo "</td>";
		echo "<td><input type='submit' value='Speichern'></td>";
		echo "</tr>";
		echo "</form>";
		echo "<tr class='liste".($i%2)."'>";
		echo "<form action='$PHP_SELF'  method='POST'>";
		echo "<td>'".$row->personpk2."'";
		echo "<input type='hidden' name='person_pk' value='".$row->personpk2."'>";
		echo "</td>";
		echo "<td>'".$row->familienname2."'";
		if($row->familienname1<>$row->familienname2)
			echo"<input type='text' size='20' maxlength='255' name='familienname' value='".$row->familienname2."'>";
		echo "</td>";
		echo "<td>'".$row->vorname2."'";
		if($row->vorname1<>$row->vorname2)
			echo"<input type='text' size='20' maxlength='255' name='vorname' value='".$row->vorname2."'>";
		echo "</td>";
		echo "<td>'".$row->anrede2."'";
		if($row->anrede1<>$row->anrede2)
			echo"<input type='text' size='10' maxlength='20' name='anrede' value='".$row->anrede2."'>";
		echo "</td>";
		echo "<td>'".$row->vornamen2."'";
		if($row->vornamen1<>$row->vornamen2)
			echo"<input type='text' size='20' maxlength='255' name='vornamen' value='".$row->vornamen2."'>";
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
			echo"<input type='text' size='1' maxlength='1' name='geschlecht'  value='".$row->anzahlderkinder2."'>";
		echo "</td>";
		echo "<td>'".$row->ersatzkennzeichen2."'";
		if($row->ersatzkennzeichen1<>$row->ersatzkennzeichen2)
			echo"<input type='text' size='10' maxlength='10' name='ersatzkennzeichen'  value='".$row->ersatzkennzeichen2."'>";
		echo "</td>";
		echo "<td>'".$row->titel2."'";
		if($row->titel1<>$row->titel2)
			echo"<input type='text' size='20' maxlength='30' name='titel'  value='".$row->titel2."'>";
		echo "</td>";
		echo "<td>'".$row->postnomentitel2."'";
		if($row->postnomentitel1<>$row->postnomentitel2)
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