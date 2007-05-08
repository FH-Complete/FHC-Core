<?php
/* Copyright (C) 2006 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

header("Content-type: application/xhtml+xml");

// Formular zum beantworten der Fragen
require_once('../config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/frage.class.php');
require_once('../../include/vorschlag.class.php');
require_once('../../include/antwort.class.php');

$PHP_SELF=$_SERVER["PHP_SELF"];

session_start();
//testumgebung
//$_SESSION['pruefling_id']=1;

//Connection Herstellen
if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim oeffnen der Datenbankverbindung');

if(isset($_GET['gebiet_id']))
	$gebiet_id = $_GET['gebiet_id'];
else
	die('Gebiet muss uebergeben werden');

if(isset($_GET['frage_id']))
	$frage_id = $_GET['frage_id'];
else
	$frage_id = '';

$MAX_VORSCHLAEGE_PRO_ZEILE=4;
?>
<?xml version="1.0" ?>
<?xml-stylesheet type="text/xsl" href="mathml.xsl" ?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/xhtml; charset=iso-8859-1" />
	<link href="../../skin/cis.css" rel="stylesheet" type="text/css" />
	<script language="Javascript" type="text/javascript">
	//<![CDATA[
	function killChildNodes(an_element)
	{
		while (an_element.hasChildNodes())
		{
			if (!an_element.firstChild.hasChildNodes())
			{
				var k = an_element.firstChild;
				an_element.removeChild(k);
			}
			else
			{
				killChildNodes(an_element.firstChild);
			}
		}
	}

	function count_down(zeit)
	{
		if(zeit<=0)
		{
			document.location.href='gebietfertig.php';
			//alert('finish');
		}
		else
		{
			zeit = zeit-1;
			minuten = parseInt((zeit/60));
			if(minuten<10)
				minuten = '0'+minuten;
			sekunden = zeit-minuten*60;
			if(sekunden<10)
				sekunden = '0'+sekunden;
			//window.document.getElementById('counter').innerHTML = minuten+':'+sekunden;
			var div = window.document.getElementById('counter');
			killChildNodes(div);
			var parser = new DOMParser();
			var doc = parser.parseFromString('<div xmlns="http://www.w3.org/1999/xhtml">' + minuten+':'+sekunden + '<\/div>', 'application/xhtml+xml');
			var root = doc.documentElement;
			for (var i=0; i < root.childNodes.length; ++i)
				div.appendChild(document.importNode(root.childNodes[i], true))

			window.setTimeout('count_down('+zeit+')',1000);
		}
	}

	function checkantwort()
	{
		antwort = document.getElementById('antwort');
		val=antwort.getAttribut('value');
		if(val.length>1)
		{
			alert('Antwort darf nur 1 Buchstabe sein');
			return false;
		}
		if(val.length==0)
			return true;
		if(val.toUpperCase()<'A' || val.toUpperCase>'Z')
		{
			alert('Antwort darf nur ein Buchstabe von A-Z sein');
			return false;
		}
		else
		{
			return true;
		}
	}
	//]]>
	</script>
</head>

<body>
<?php
if(!isset($_SESSION['pruefling_id']))
	die('Bitte zuerst anmelden!');

//Gruppe des Prueflings holen
$qry = "SELECT * FROM testtool.tbl_pruefling WHERE pruefling_id='".addslashes($_SESSION['pruefling_id'])."'";

if($result = pg_query($conn, $qry))
{
	if($row = pg_fetch_object($result))
	{
		$gruppe = $row->gruppe_kurzbz;
	}
	else
		die('Pruefling wurde nicht gefunden');
}
else
	die('Pruefling wurde nicht gefunden');

//Start des Pruefungsvorganges
if(isset($_GET['start']))
{
	//Erste Frage holen und Begintime eintragen
	$qry = "SELECT frage_id FROM testtool.tbl_frage WHERE gebiet_id='".addslashes($gebiet_id)."' AND gruppe_kurzbz='$gruppe' AND demo=false AND nummer>0 ORDER BY nummer ASC LIMIT 1";
	$result = pg_query($conn, $qry);
	$row = pg_fetch_object($result);
	$antwort = new antwort($conn);
	$antwort->frage_id = $row->frage_id;
	$antwort->pruefling_id = $_SESSION['pruefling_id'];
	$antwort->begintime = date('Y-m-d H:i:s');
	$antwort->new = true;
	if(!$antwort->save())
		die('Fehler beim Startvorgang');
}

//Speichern einer Antwort
if(isset($_POST['submitantwort']) && isset($_GET['frage_id']))
{
	$antwort = new antwort($conn);
	if($_POST['antwort_id']!='')
	{
		if(!$antwort->load($_POST['antwort_id']))
			die('Antwort konnte nicht geladen werden');
		else
		{
			$antwort->new = false;
			$antwort->antwort_id = $_POST['antwort_id'];
		}
	}
	else
		$antwort->new = true;

	$antwort->frage_id = $_GET['frage_id'];
	$antwort->pruefling_id = $_SESSION['pruefling_id'];
	$antwort->antwort = trim(strtoupper($_POST['antwort']));
	$antwort->endtime = date('Y-m-d H:i:s');
	if(!$antwort->save())
		die('Fehler beim Speichern');
	else
	{
		$frage = new frage($conn);
		$frage_id = $frage->getNextFrage($gebiet_id, $gruppe, $frage_id);
	}
}

//Schauen ob dieses Gebiet schon gestartet wurde
$qry = "SELECT count(*) as anzahl FROM testtool.tbl_antwort JOIN testtool.tbl_frage USING(frage_id) WHERE pruefling_id='".addslashes($_SESSION['pruefling_id'])."' AND gebiet_id='".addslashes($gebiet_id)."'";

if($result = pg_query($conn, $qry))
{
	if($row = pg_fetch_object($result))
	{
		if($row->anzahl>0)
		{
			//Hat bereits Fragen beantwortet -> Frage anzeigen
			$demo=false;
		}
		else
		{
			//Demo anzeigen
			$demo=true;
		}
	}
	else
		die('error');
}
else
	die('error');

$info='';
$qry_pruefling = "SELECT vorname, nachname, stg_bez, gruppe_kurzbz FROM testtool.vw_pruefling WHERE pruefling_id='".$_SESSION['pruefling_id']."'";
if($result_pruefling = pg_query($conn, $qry_pruefling))
{
	if($row_pruefling = pg_fetch_object($result_pruefling))
	{
		$info = "$row_pruefling->vorname $row_pruefling->nachname, $row_pruefling->stg_bez, Gruppe $row_pruefling->gruppe_kurzbz";
	}
}

//Zeit des Gebietes holen
echo '<table width="100%"><tr><td>'.$info.'</td><td align="right">';

$qry = "SELECT zeit FROM testtool.tbl_gebiet WHERE gebiet_id='".addslashes($gebiet_id)."'";

$result = pg_query($conn, $qry);
if(!$row = pg_fetch_object($result))
	die('Gebiet wurde nicht gefunden');
list($stunde, $minute, $sekunde) = split(':',$row->zeit);

if($demo)
{
	//Wenn es sich um ein Demobeispiel handelt, dann wird die Maximale Gesamtzeit angezeigt
	echo $minute.':'.$sekunde.' Minuten ';
	echo "<input type=\"button\" value=\"Start\" onclick=\"document.location.href='$PHP_SELF?gebiet_id=$gebiet_id&amp;start=true'\" />";
}
else
{
	//Wenn es sich um eine Testfrage handelt, dann wird die verbleibende Zeit angezeigt
	$qry = "SELECT '$row->zeit'-(now()-min(begintime)) as time FROM testtool.tbl_antwort JOIN testtool.tbl_frage USING(frage_id) WHERE gebiet_id='".addslashes($gebiet_id)."' AND pruefling_id='".addslashes($_SESSION['pruefling_id'])."'";
	$result = pg_query($conn, $qry);
	$row = pg_fetch_object($result);
	//Zeit in Sekunden umrechnen
	list($stunde, $minute, $sekunde) = split(':',$row->time);
	$zeit = (int) ($stunde*60*60+$minute*60+$sekunde);
	//Wenn die Zeit negativ ist und die Stunde 0 ist,
	//dann muss die Zeit mit -1 multipliziert werden
	if(substr($stunde,0,1)=='-' && $stunde==0)
	{
		$zeit = $zeit*-1;
	}

	echo '<span id="counter"></span> Minuten';
	echo "<script>count_down($zeit)</script>";
}
echo '</td></tr>';
echo '</table>';

//Laden der Frage
$frage = new frage($conn);

if($frage_id!='') //Frage wurde uebergeben
{
	$frage->load($frage_id);
}
else
{
	if($demo) //Demofrage wird angezeigt
	{
		$qry = "SELECT frage_id FROM testtool.tbl_frage WHERE gebiet_id='".addslashes($gebiet_id)."' AND gruppe_kurzbz='$gruppe' AND demo=true ORDER BY nummer DESC LIMIT 1";
		$result = pg_query($conn, $qry);
		if($row = pg_fetch_object($result))
			$frage->load($row->frage_id);
	}
	else //Testfrage wird angezeigt
	{
		$qry ="SELECT frage_id FROM testtool.tbl_frage WHERE gebiet_id='".addslashes($gebiet_id)."' AND gruppe_kurzbz='$gruppe' AND nummer>0 AND demo=false ORDER BY nummer ASC LIMIT 1";
		$result = pg_query($conn, $qry);
		if($row = pg_fetch_object($result))
			$frage->load($row->frage_id);
	}
}

//Anzeigen der Frage
if($frage->frage_id!='')
{
	$frage_id = $frage->frage_id;

	if(!$demo)
	{
		//Nachschauen ob diese Frage bereits angesehen wurde
		$antwort = new antwort($conn);
		if(!$antwort->getAntwort($_SESSION['pruefling_id'],$frage_id))
		{
			//wenn diese noch nicht angesehen wurde, dann wird die begintime gesetzt
			$antwort = new antwort($conn);
			$antwort->begintime = date('Y-m-d H:i:s');
			$antwort->pruefling_id = $_SESSION['pruefling_id'];
			$antwort->frage_id = $frage_id;
			$antwort->new = true;
			if(!$antwort->save())
				echo 'Fehler beim Speichern der Erstansicht';
		}
	}
	echo '<br/><br/><center>';
	//Bild und Text der Frage anzeigen
	if($frage->bild!='')
		echo "<img class='testtoolfrage' src='bild.php?src=frage&amp;frage_id=$frage->frage_id' /><br/><br/>\n";
	echo "$frage->text<br/><br/>\n";

	//Vorschlaege laden
	$vs = new vorschlag($conn);
	$vs->getVorschlag($frage->frage_id);
	echo '<table cellspacing="30px">';
	echo '<tr>';
	$anzahl = 1;
	//Vorschlaege anzeigen
	foreach ($vs->result as $vorschlag)
	{
		echo "\n<td align='center'>";
		echo "$vorschlag->antwort<br/>";
		if($vorschlag->bild!='')
			echo "<img class='testtoolvorschlag' src='bild.php?src=vorschlag&amp;vorschlag_id=$vorschlag->vorschlag_id' /><br/>";
		if($vorschlag->text!='')
			echo $vorschlag->text.'<br/>';
		echo "</td>";
		$anzahl++;

		if($anzahl>$MAX_VORSCHLAEGE_PRO_ZEILE)
		{
			echo '</tr><tr>';
			$anzahl=1;
		}
	}
	echo '</tr></table>';
	//Antwort laden falls bereits vorhanden
	$antwort = new antwort($conn);
	$antwort->getAntwort($_SESSION['pruefling_id'],$frage->frage_id);
	if(!$demo)
	{
	echo "<form action=\"$PHP_SELF?gebiet_id=$gebiet_id&amp;frage_id=$frage->frage_id\" method=\"POST\">";
	echo "<input type=\"hidden\" name=\"antwort_id\" value=\"$antwort->antwort_id\" />";
	echo "Antwort: <input type=\"text\" size=\"1\" id=\"antwort\" name=\"antwort\" value=\"".htmlentities(addslashes($antwort->antwort))."\" />   <input type=\"submit\" name=\"submitantwort\" onclick=\"return checkantwort()\" value=\"Speichern\" />";
	echo "</form>";
	}
	echo '<br/><br/><br/>';
	//Fusszeile mit Weiter Button und Sprung direkt zu einer Frage
	if(!$demo)
	{
		$qry = "SELECT nummer, frage_id FROM testtool.tbl_frage WHERE gebiet_id='".addslashes($gebiet_id)."' AND gruppe_kurzbz='$gruppe' AND demo=false ORDER BY nummer";

		//Nummern der Fragen Anzeigen
		$result = pg_query($conn, $qry);
		while($row = pg_fetch_object($result))
		{
			if($row->frage_id==$frage_id)
				echo " <u>$row->nummer</u> -";
			else
				echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;frage_id=$row->frage_id' class='Item'>$row->nummer</a> -";
		}
	}

	//Naechste Frage holen und Weiter-Button anzeigen
	$frage = new frage($conn);
	$nextfrage = $frage->getNextFrage($gebiet_id, $gruppe, $frage_id, $demo);
	if($nextfrage)
	{
		echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;frage_id=$nextfrage' class='Item'>Weiter &gt;&gt;</a>";
	}
	else
	{
		//Wenns der letzte Eintrag ist, wieder zum ersten springen
		echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id' class='Item'>Weiter &gt;&gt;</a>";
	}

	echo '</center>';
}
else
{
	//Wenn kein Demo vorhanden ist
	echo "<br/><br/><br/><center><b>Start drücken um zu beginnen</b></center>";
}
?>

</body>
</html>
