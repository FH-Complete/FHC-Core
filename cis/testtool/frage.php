<?php
/* Copyright (C) 2009 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 */
/**
 * Formular zum Beantworten der Fragen
 */

header("Content-type: application/xhtml+xml");

require_once('../../config/cis.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/frage.class.php');
require_once('../../include/vorschlag.class.php');
require_once('../../include/antwort.class.php');
require_once('../../include/gebiet.class.php');

if (!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

$PHP_SELF=$_SERVER["PHP_SELF"];

session_start();

if(isset($_GET['gebiet_id']))
	$gebiet_id = $_GET['gebiet_id'];
else
	die('Gebiet muss uebergeben werden');

if(isset($_GET['frage_id']))
	$frage_id = $_GET['frage_id'];
else
	$frage_id = '';

//$MAX_VORSCHLAEGE_PRO_ZEILE=4;
?>
<?xml version="1.0" ?>
<?xml-stylesheet type="text/xsl" href="mathml.xsl" ?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/xhtml; charset=UTF-8" />
	<link href="../../skin/style.css.php" rel="stylesheet" type="text/css" />
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
			parent.menu.location.reload();
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

$gebiet = new gebiet($gebiet_id);

if($gebiet->level_start!='')
	$levelgebiet=true;
else 
	$levelgebiet=false;

list($stunde, $minute, $sekunde) = split(':',$gebiet->zeit);

//Start des Pruefungsvorganges
if(isset($_GET['start']))
{
	//Fragenpool generieren
	$frage = new frage();
	if(!$frage->generateFragenpool($_SESSION['pruefling_id'], $gebiet_id))
		die('Fehler beim Generieren des Fragenpools:'.$frage->errormsg);
	
	//Erste Frage des Pools holen
	if(!$frage_id = $frage->getNextFrage($gebiet_id, $_SESSION['pruefling_id']))
		die('Es wurde keine Frage gefunden');
	
	//Beginnzeit Speichern
	$prueflingfrage = new frage();
	if(!$prueflingfrage->getPrueflingfrage($_SESSION['pruefling_id'], $frage_id))
		die('Fehler:'.$prueflingfrage->errormsg);
	
	$prueflingfrage->begintime = date('Y-m-d H:i:s');
	if(!$prueflingfrage->save_prueflingfrage(false))
		die('Fehler beim Startvorgang');
		
	echo '<script language="Javascript">parent.menu.location.reload();</script>';
}

//Speichern einer Antwort
if(isset($_POST['submitantwort']) && isset($_GET['frage_id']))
{
	// vor dem Speichern der Antworten, alle Antworten zu der Frage loeschen
	// und die Antworten neu anlegen
	// Unterscheidung ob mehrere oder nur eine Antwort uebergeben wird
	$error=false;
	
	$db->db_query('BEGIN;');

	// alle vorhandenen Antworten zu dieser Frage loeschen
	$qry = "DELETE FROM testtool.tbl_antwort WHERE antwort_id in(
				SELECT antwort_id FROM testtool.tbl_antwort JOIN testtool.tbl_vorschlag USING(vorschlag_id)
				WHERE frage_id='".addslashes($_GET['frage_id'])."' AND pruefling_id='".addslashes($_SESSION['pruefling_id'])."')";

	$db->db_query($qry);
	
	// Antwort nur Speichern wenn eine Antwort gewaehlt wurde	
	if(isset($_POST['vorschlag_id']) && $_POST['vorschlag_id']!='')
	{
		$vorschlaege = array();
		//Falls nur eine einzelne Antwort kommt, diese auch in ein Array packen
		if(!is_array($_POST['vorschlag_id']))
			$vorschlaege[0]=$_POST['vorschlag_id'];
		else 
			$vorschlaege = $_POST['vorschlag_id'];
		
		//alle Antworten Speichern
		foreach ($vorschlaege as $vorschlag_id) 
		{
			if($vorschlag_id!='')
			{
				$antwort = new antwort();
				
				$antwort->new = true;
				$antwort->vorschlag_id = $vorschlag_id;
				$antwort->pruefling_id = $_SESSION['pruefling_id'];
				
				if(!$antwort->save())
				{
					$errormsg = $antwort->errormsg;
					$error=true;
				}
			}
		}
			
		if(!$error)
		{
			//Endzeit der Frage eintragen
			$prueflingfrage = new frage();
			if(!$prueflingfrage->getPrueflingfrage($_SESSION['pruefling_id'], $frage_id))
			{
				$errormsg = $antwort->errormsg;
				$error = true;
			}
			$prueflingfrage->endtime = date('Y-m-d H:i:s');
			
			if(!$prueflingfrage->save_prueflingfrage(false))
			{
				$errormsg = $prueflingfrage->errormsg;
				$error = true;
			}
		}
	}
	
	if($error)
	{
		$db->db_query('ROLLBACK;');
		die('Fehler:'.$errormsg);
	}
	else 
	{
		$db->db_query('COMMIT;');
	}
	
	$frage = new frage();
	
	if($levelgebiet)
	{
		//bei gelevelten Fragen die naechste Frage holen
		$frage->generateFragenpool($_SESSION['pruefling_id'], $gebiet_id);
	}
	
	$frage_id = $frage->getNextFrage($gebiet_id, $_SESSION['pruefling_id'], $frage_id);
}

//Schauen ob dieses Gebiet schon gestartet wurde
$qry = "SELECT begintime
		FROM 
			testtool.tbl_pruefling_frage JOIN testtool.tbl_frage USING(frage_id)
		WHERE pruefling_id='".addslashes($_SESSION['pruefling_id'])."' AND gebiet_id='".addslashes($gebiet_id)."'
		ORDER BY begintime ASC LIMIT 1";

if($result = $db->db_query($qry))
{
	if($row = $db->db_fetch_object($result))
	{
		if($row->begintime!='')
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
		$demo=true;
}
else
	die('error');

$info='';

//Name und Studiengang anzeigen
$qry_pruefling = "SELECT vorname, nachname, stg_bez FROM testtool.vw_pruefling 
					WHERE pruefling_id='".addslashes($_SESSION['pruefling_id'])."'";

if($result_pruefling = $db->db_query($qry_pruefling))
{
	if($row_pruefling = $db->db_fetch_object($result_pruefling))
	{
		$info = "$row_pruefling->vorname $row_pruefling->nachname, $row_pruefling->stg_bez";
	}
}

//Zeit des Gebietes holen
echo '<table width="100%"><tr><td>'.$info.'</td><td align="right">';

if($demo)
{
	//Wenn es sich um ein Demobeispiel handelt, dann wird die Maximale Gesamtzeit angezeigt
	echo $minute.':'.$sekunde.' Minuten ';
	echo "<input type=\"button\" value=\"Start\" onclick=\"document.location.href='$PHP_SELF?gebiet_id=$gebiet_id&amp;start=true'\" />";
}
else
{
	//Wenn es sich um eine Testfrage handelt, dann wird die verbleibende Zeit angezeigt
	$qry = "SELECT '$gebiet->zeit'-(now()-min(begintime)) as time 
			FROM testtool.tbl_pruefling_frage JOIN testtool.tbl_frage USING(frage_id) 
			WHERE gebiet_id='".addslashes($gebiet_id)."' AND pruefling_id='".addslashes($_SESSION['pruefling_id'])."'";
	$result = $db->db_query($qry);
	$row = $db->db_fetch_object($result);
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
$frage = new frage();

if($frage_id!='') //Frage wurde uebergeben
{
	$frage->load($frage_id);
}
else
{
	if($levelgebiet)
	{
		// wenn keine Frage uebergeben wurde und die maximale Fragenanzahl erreicht wurde
		// dann ist das Gebiet fertig
		$qry = "SELECT count(*) as anzahl FROM testtool.tbl_pruefling_frage JOIN testtool.tbl_frage USING(frage_id) 
				WHERE gebiet_id='".addslashes($gebiet_id)."' AND pruefling_id='".addslashes($_SESSION['pruefling_id'])."'";
		$result = $db->db_query($qry);
		$row = $db->db_fetch_object($result);
		
		if($row->anzahl>=$gebiet->maxfragen)
		{
			die("<script>document.location.href='gebietfertig.php';</script>");
		}
	}
	
	$frage_id = $frage->getNextFrage($gebiet_id, $_SESSION['pruefling_id'], null, $demo);
	$frage->load($frage_id);
}

//Anzeigen der Frage
if($frage->frage_id!='')
{
	$frage_id = $frage->frage_id;
	$frage->getFrageSprache($frage_id, $_SESSION['sprache']);

	if(!$demo)
	{
		//Nachschauen ob diese Frage bereits angesehen wurde
		$antwort = new antwort();
		$antwort->getAntwort($_SESSION['pruefling_id'],$frage_id);
		if(count($antwort->result)==0)
		{
			//wenn diese noch nicht angesehen wurde, dann wird die begintime gesetzt
			$prueflingfrage = new frage();
			if(!$prueflingfrage->getPrueflingfrage($_SESSION['pruefling_id'], $frage_id))
				die('Diese Frage ist nicht fuer Sie bestimmt');
				
			if($prueflingfrage->begintime=='')
			{
				$prueflingfrage->begintime = date('Y-m-d H:i:s');
				$prueflingfrage->new = false;
				if(!$prueflingfrage->save_prueflingfrage())
					echo 'Fehler beim Speichern der Erstansicht';
			}
		}
	}
	echo '<br/><br/><center>';
	//Bild und Text der Frage anzeigen
	if($frage->bild!='')
		echo "<img class='testtoolfrage' src='bild.php?src=frage&amp;frage_id=$frage->frage_id&amp;sprache=".$_SESSION['sprache']."' /><br/><br/>\n";
		
	//Sound einbinden
	if($frage->audio!='')
		echo '<embed autostart="false" src="sound.php?src=frage&amp;frage_id='.$frage->frage_id.'&amp;sprache='.$_SESSION['sprache'].'" height="20" width="250"/><br />';
	echo "$frage->text<br/><br/>\n";

	//Vorschlaege laden
	$vs = new vorschlag();
	$vs->getVorschlag($frage->frage_id, $_SESSION['sprache'], $gebiet->zufallvorschlag);
	echo "<form action=\"$PHP_SELF?gebiet_id=$gebiet_id&amp;frage_id=$frage->frage_id\" method=\"POST\">";
	echo '<table cellspacing="30px">';
	echo '<tr>';
	$anzahl = 1;
	
	//Antworten laden falls bereits vorhanden
	$antwort = new antwort();
	$antwort->getAntwort($_SESSION['pruefling_id'],$frage->frage_id);
		
	//Vorschlaege anzeigen
	foreach ($vs->result as $vorschlag)
	{
		echo "\n<td align='center' valign='top'>";
		
		//Bei multipleresponse checkboxen anzeigen ansonsten radiobuttons
		if($gebiet->multipleresponse)
			$type='checkbox';
		else 
			$type='radio';
			
		//Antworten markieren wenn die Frage bereits beantwortet wurde
		$checked=false;
		reset($antwort->result);
		foreach ($antwort->result as $answer)
		{
			if($vorschlag->vorschlag_id==$answer->vorschlag_id)
			{
				$checked='checked="checked"';
			}
		}
		
		echo '<input type="'.$type.'" name="vorschlag_id[]" value="'.$vorschlag->vorschlag_id.'" '.$checked.'/>';
		
		echo '<br/>';
		if($vorschlag->bild!='')
			echo "<img class='testtoolvorschlag' src='bild.php?src=vorschlag&amp;vorschlag_id=$vorschlag->vorschlag_id&amp;sprache=".$_SESSION['sprache']."' /><br/>";
		if($vorschlag->audio!='')
			echo '<embed autostart="false" src="sound.php?src=vorschlag&amp;vorschlag_id='.$vorschlag->vorschlag_id.'&amp;sprache='.$_SESSION['sprache'].'" height="20" width="100"/><br />';
		if($vorschlag->text!='')
			echo $vorschlag->text.'<br/>';
		echo "</td>";
		$anzahl++;

		if($anzahl>$gebiet->antwortenprozeile)
		{
			echo '</tr><tr>';
			$anzahl=1;
		}
	}
	
	//wenn singleresponse und keine Levels und vorschlaege vorhanden sind, dann gibt es auch die 
	//moeglichkeit fuer keine Antwort
	if(!$gebiet->multipleresponse && !$levelgebiet && count($vs->result)>0)
	{
		echo "<td align='center' valign='top'>";
		echo '<input type="radio" name="vorschlag_id[]" value="" /><br /><font color="#acacac">CLEAR</font></td>';
	}
	echo '</tr></table>';
	
	if(!$demo)
	{
		echo "<input type=\"submit\" name=\"submitantwort\" value=\"Speichern\" />";
	}
	echo "</form>";
	echo '<br/><br/><br/>';
	//Fusszeile mit Weiter Button und Sprung direkt zu einer Frage
	if(!$demo && !$levelgebiet)
	{
		$qry = "SELECT tbl_pruefling_frage.nummer, tbl_pruefling_frage.frage_id 
				FROM testtool.tbl_pruefling_frage JOIN testtool.tbl_frage USING(frage_id) 
				WHERE gebiet_id='".addslashes($gebiet_id)."' AND pruefling_id='".addslashes($_SESSION['pruefling_id'])."' AND demo=false ORDER BY nummer";

		//Nummern der Fragen Anzeigen
		$result = $db->db_query($qry);
		while($row = $db->db_fetch_object($result))
		{
			if($row->frage_id==$frage_id)
				echo " <u>$row->nummer</u> -";
			else
				echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;frage_id=$row->frage_id' class='Item'>$row->nummer</a> -";
		}
	}

	//Weiter Button nur bei nicht gelevelten Gebieten anzeigen
	if(!$levelgebiet)
	{
		//Naechste Frage holen und Weiter-Button anzeigen
		$frage = new frage();
		$nextfrage = $frage->getNextFrage($gebiet_id, $_SESSION['pruefling_id'], $frage_id, $demo);
		if($nextfrage)
		{
			echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;frage_id=$nextfrage' class='Item'>Weiter &gt;&gt;</a>";
		}
		else
		{
			if($demo)
			{
				$qry = "SELECT count(*) as anzahl FROM testtool.tbl_frage 
						WHERE tbl_frage.gebiet_id='".addslashes($gebiet_id)."' 
						AND demo ";
				if($row = $db->db_fetch_object($db->db_query($qry)))
				{
					if($row->anzahl>1)
					{
						//Bei Demos den Weiter-Button nur anzeigen, wenn ausser der Startseite noch andere Demoseiten vorhanden sind
						echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id' class='Item'>Weiter &gt;&gt;</a>";
					}
				}
			}
			else
			{
				//Wenns der letzte Eintrag ist, wieder zum ersten springen
				echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id' class='Item'>Weiter &gt;&gt;</a>";
			}
		}
	}

	echo '</center>';
}
else
{
	//Wenn kein Demo vorhanden ist
	echo "<br/><br/><br/><center><b>Start druecken um zu beginnen</b></center>";
}
?>

</body>
</html>
