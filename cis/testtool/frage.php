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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>,
 *			Manfred Kindl <manfred.kindl@technikum-wien.at>
 */
/**
 * Formular zum Beantworten der Fragen
 */

header("Content-type: application/xhtml+xml");

require_once('../../config/cis.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/frage.class.php');
require_once('../../include/vorschlag.class.php');
require_once('../../include/antwort.class.php');
require_once('../../include/gebiet.class.php');
require_once('../../include/sprache.class.php');
require_once '../../include/phrasen.class.php';

if (!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

$PHP_SELF=$_SERVER["PHP_SELF"];

function getSpracheUser()
{
	if(isset($_SESSION['sprache_user']))
	{
		$sprache_user=$_SESSION['sprache_user'];
	}
	else
	{
		if(isset($_COOKIE['sprache_user']))
		{
			$sprache_user=$_COOKIE['sprache_user'];
		}
		else
		{
			$sprache_user=DEFAULT_LANGUAGE;
		}
		setSpracheUser($sprache_user);
	}
	return $sprache_user;
}

function setSpracheUser($sprache)
{
	$_SESSION['sprache_user']=$sprache;
	setcookie('sprache_user',$sprache,time()+60*60*24*30,'/');
}

if(isset($_GET['sprache_user']))
{
	$sprache_user = new sprache();
	if($sprache_user->load($_GET['sprache_user']))
	{
		setSpracheUser($_GET['sprache_user']);
	}
	else
		setSpracheUser(DEFAULT_LANGUAGE);
}

$sprache_user = getSpracheUser(); 
$p = new phrasen($sprache_user);

$sprache = getSprache(); 

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
<!--[IF !IE]>
<?xml version="1.0" ?>
<?xml-stylesheet type="text/xsl" href="mathml.xsl" ?>
 -->


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

	function GebietStarten(bezeichnung,stunde,minute,sekunde,gebiet_id)
	{
		var check = confirm(<?php echo "'".$p->t('testtool/okKlickenUmZuStarten')."'"?>+' '+stunde+'h '+minute+'m '+sekunde+'s');
        if (check == true) {
        	document.location.href = 'frage.php?gebiet_id='+gebiet_id+'&start=true';
        }
        else {
            return false;
        }
	}

	function letzteFrage()
	{
		alert(<?php echo "'".$p->t("testtool/alleFragenBeantwortet")."'"?>);
        return true;
	}

	//]]>
	</script>
</head>

<body>
<?php
if(!isset($_SESSION['pruefling_id']))
	die($p->t('testtool/bitteZuerstAnmelden'));

$gebiet = new gebiet($gebiet_id);

if($gebiet->level_start!='')
	$levelgebiet=true;
else 
	$levelgebiet=false;

list($stunde, $minute, $sekunde) = explode(':',$gebiet->zeit);

//Start des Pruefungsvorganges
if(isset($_GET['start']))
{
	//Fragenpool generieren
	$frage = new frage();
	if(!$frage->generateFragenpool($_SESSION['pruefling_id'], $gebiet_id))
		die($p->t('testtool/fehlerBeimGenerierenDesFragenpools').':'.$frage->errormsg);
	
	//Erste Frage des Pools holen
	if(!$frage_id = $frage->getNextFrage($gebiet_id, $_SESSION['pruefling_id']))
		die($p->t('testtool/esWurdeKeineFrageGefunden'));
	
	//Beginnzeit Speichern
	$prueflingfrage = new frage();
	if(!$prueflingfrage->getPrueflingfrage($_SESSION['pruefling_id'], $frage_id))
		die($p->t('testtool/fehler').':'.$prueflingfrage->errormsg);
	
	$prueflingfrage->begintime = date('Y-m-d H:i:s');
	if(!$prueflingfrage->save_prueflingfrage(false))
		die($p->t('testtool/fehlerBeimStartvorgang'));
		
	echo '<script language="Javascript">parent.menu.location.reload();</script>';
}

//Speichern einer Antwort
if(isset($_POST['submitantwort']) && isset($_GET['frage_id']))
{
	// vor dem Speichern der Antworten, alle Antworten zu der Frage loeschen
	// und die Antworten neu anlegen
	// Unterscheidung ob mehrere oder nur eine Antwort uebergeben wird
	
	if($levelgebiet && !isset($_POST['vorschlag_id']))
	{
		echo '<span class="error">'.$p->t('testtool/beiDiesemGebietMuessenSieJedeFrageBeantworten').'</span>';
	}
	else
	{
		
		$error=false;
		
		$db->db_query('BEGIN;');
	
		// alle vorhandenen Antworten zu dieser Frage loeschen
		$qry = "DELETE FROM testtool.tbl_antwort WHERE antwort_id in(
					SELECT antwort_id FROM testtool.tbl_antwort JOIN testtool.tbl_vorschlag USING(vorschlag_id)
					WHERE frage_id=".$db->db_add_param($_GET['frage_id'])." AND pruefling_id=".$db->db_add_param($_SESSION['pruefling_id']).")";
	
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
			die($p->t('testtool/fehler').':'.$errormsg);
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
}

//Schauen ob dieses Gebiet schon gestartet wurde
$qry = "SELECT begintime
		FROM 
			testtool.tbl_pruefling_frage JOIN testtool.tbl_frage USING(frage_id)
		WHERE pruefling_id=".$db->db_add_param($_SESSION['pruefling_id'], FHC_INTEGER)." AND gebiet_id=".$db->db_add_param($gebiet_id, FHC_INTEGER)."
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
$qry_pruefling = "SELECT vorname, nachname, stg_bez, tbl_studiengangstyp.bezeichnung FROM testtool.vw_pruefling 
					JOIN public.tbl_studiengang USING (studiengang_kz)
					JOIN public.tbl_studiengangstyp USING (typ)
					WHERE pruefling_id=".$db->db_add_param($_SESSION['pruefling_id']);

if($result_pruefling = $db->db_query($qry_pruefling))
{
	if($row_pruefling = $db->db_fetch_object($result_pruefling))
	{
		$info = "$row_pruefling->vorname $row_pruefling->nachname, $row_pruefling->bezeichnung $row_pruefling->stg_bez";
	}
}

$fortschrittsbalken='';
if($levelgebiet)
{
	$max = $gebiet->maxfragen;
	$aktuell=0;
	$qry = "SELECT count(*) as anzahl FROM testtool.tbl_pruefling_frage JOIN testtool.tbl_frage USING(frage_id)
			WHERE pruefling_id=".$db->db_add_param($_SESSION['pruefling_id'], FHC_INTEGER)."
			AND gebiet_id=".$db->db_add_param($gebiet_id, FHC_INTEGER);
	
	if($result_aktuell = $db->db_query($qry))
	{
		if($row_aktuell = $db->db_fetch_object($result_aktuell))
		{
			$aktuell = $row_aktuell->anzahl;
		}
	}
	$psolved = $aktuell/$max*100;
	//$fortschrittsbalken .= "$aktuell / $max";
	$fortschrittsbalken .= '
	<table width="300" style="border: 1px solid black;" cellpadding="0" cellspacing="0" background="../../skin/images/bg_.gif">
		<tr>
  			<td valign="top" style="height:1px;font-size:2px;">
  				<table cellpadding="0" cellspacing="0" style="border:0px;height:1px;font-size:2px;">
      			<tr>
	      			<td nowrap="nowrap" style="height:1px;font-size:2px;">
						<font size="2" face="Arial, Helvetica, sans-serif">
						<img src="../../skin/images/entry.gif" width="'.($psolved*3).'" height="10" alt="" border="1" />
						</font>
					</td>
				</tr>
				</table>
			</td>
		</tr>
	</table>';
	$fortschrittsbalken .= '<span class="smallb"><b> '.$aktuell.' / '.$max.'</b> ['.number_format($psolved,1,'.','').'%]</span>';
	
}
//Zeit des Gebietes holen
echo '<table width="100%"><tr><td valign="top" width="50%">'.$info.'</td><td align="center">'.$fortschrittsbalken.'</td><td width="50%"></td></tr><tr><td colspan="3">';

if($demo)
{
	//Wenn es sich um ein Demobeispiel handelt, dann wird die Maximale Gesamtzeit angezeigt
	echo "<input type=\"button\" value=\"".$p->t("testtool/gebietStarten")."\" onclick=\"GebietStarten('".$db->convert_html_chars($gebiet->bezeichnung)."','".$stunde."','".$minute."','".$sekunde."','".$gebiet_id."')\" /> ";
	echo '<center>'.$p->t('testtool/bearbeitungszeit').': '.$stunde.'h '.$minute.'m '.$sekunde.'s</center>';
}
else
{
	//Wenn es sich um eine Testfrage handelt, dann wird die verbleibende Zeit angezeigt
	$qry = "SELECT '$gebiet->zeit'-(now()-min(begintime)) as time 
			FROM testtool.tbl_pruefling_frage JOIN testtool.tbl_frage USING(frage_id) 
			WHERE gebiet_id=".$db->db_add_param($gebiet_id, FHC_INTEGER)." AND pruefling_id=".$db->db_add_param($_SESSION['pruefling_id'], FHC_INTEGER);
	$result = $db->db_query($qry);
	$row = $db->db_fetch_object($result);
	//Zeit in Sekunden umrechnen
	list($stunde, $minute, $sekunde) = explode(':',$row->time);
	$zeit = (int) ($stunde*60*60+$minute*60+$sekunde);
	//Wenn die Zeit negativ ist und die Stunde 0 ist,
	//dann muss die Zeit mit -1 multipliziert werden
	if(substr($stunde,0,1)=='-' && $stunde==0)
	{
		$zeit = $zeit*-1;
	}

	echo $p->t('testtool/bearbeitungszeit').': <span id="counter"></span>';
	echo "<script>count_down($zeit)</script>";
	
	if($zeit<0)
		die('</td></tr></table><center><b>'.$p->t('testtool/dieZeitIstAbgelaufen').'</b></center></body></html>');
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
				WHERE gebiet_id=".$db->db_add_param($gebiet_id, FHC_INTEGER)." AND pruefling_id=".$db->db_add_param($_SESSION['pruefling_id'], FHC_INTEGER)." AND tbl_pruefling_frage.endtime is not null";
		$result = $db->db_query($qry);
		$row = $db->db_fetch_object($result);
		
		if($row->anzahl>=$gebiet->maxfragen)
		{
			die("<script>document.location.href='gebietfertig.php';</script>");
		}
	}
	
	$frage_id = $frage->getNextFrage($gebiet_id, $_SESSION['pruefling_id'], null, $demo, $levelgebiet);
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
				die($p->t('testtool/dieseFrageIstNichtFuerSieBestimmt'));
				
			if($prueflingfrage->begintime=='')
			{
				$prueflingfrage->begintime = date('Y-m-d H:i:s');
				$prueflingfrage->new = false;
				if(!$prueflingfrage->save_prueflingfrage())
					echo $p->t('testtool/fehlerBeimSpeichernDerErstansicht');
			}
		}
	}
	echo '<center>';
	//Kopfzeile mit Weiter Button und Sprung direkt zu einer Frage
	if(!$demo && !$levelgebiet)
	{
		$qry = "SELECT tbl_pruefling_frage.nummer, tbl_pruefling_frage.frage_id 
				FROM testtool.tbl_pruefling_frage JOIN testtool.tbl_frage USING(frage_id) 
				WHERE gebiet_id=".$db->db_add_param($gebiet_id, FHC_INTEGER)." AND pruefling_id=".$db->db_add_param($_SESSION['pruefling_id'], FHC_INTEGER)." AND demo=false ORDER BY nummer";

		echo " <table><tr>";
		//Nummern der Fragen Anzeigen
		$result = $db->db_query($qry);
		while($row = $db->db_fetch_object($result))
		{
			$antwort = new antwort();
			$antwort->getAntwort($_SESSION['pruefling_id'],$row->frage_id);
			if($row->frage_id==$frage_id)
				echo " <a href='#' target='_self'><td style='width:12px; text-align:center; padding:2px; box-shadow: 0px 0px 3px 3px #888888;".(count($antwort->result)!=0?"background-color:lightgreen;":"")."'>".($row->nummer<10?" ":"")."$row->nummer</td></a>";
			else
				echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;frage_id=$row->frage_id'><td style='width:12px; text-align:center; padding:2px; box-shadow: 0px 0px 3px 0px #888888;".(count($antwort->result)!=0?"background-color:lightgreen;":"")."'>$row->nummer</td></a>";
		}
		//echo " </tr></table>";
	}
	//Weiter Button nur bei nicht gelevelten Gebieten anzeigen
	if(!$levelgebiet)
	{
		//Naechste Frage holen und Weiter-Button anzeigen
		$frage2 = new frage();
		$nextfrage = $frage2->getNextFrage($gebiet_id, $_SESSION['pruefling_id'], $frage_id, $demo);
		if($nextfrage)
		{
			if($demo)
				$value=$p->t('testtool/demo');
			else 
				$value=$p->t('testtool/blaettern');
			
			echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;frage_id=$nextfrage' class='Item'>$value &gt;&gt;</a>";
		}
		else
		{
			if($demo)
			{
				$qry = "SELECT count(*) as anzahl FROM testtool.tbl_frage 
						WHERE tbl_frage.gebiet_id=".$db->db_add_param($gebiet_id, FHC_INTEGER)." 
						AND demo ";
				if($row = $db->db_fetch_object($db->db_query($qry)))
				{
					if($row->anzahl>1)
					{
						//Bei Demos den Weiter-Button nur anzeigen, wenn ausser der Startseite noch andere Demoseiten vorhanden sind
						echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id' class='Item'>".$p->t("testtool/startseite")." &gt;&gt;</a>";
					}
				}
			}
			else
			{
				//Wenns der letzte Eintrag ist, wieder zum ersten springen
				echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id' class='Item'>".$p->t('testtool/blaettern')." &gt;&gt;</a>";
			}
		}
	}
	else 
	{
		//Naechste Frage holen und Weiter-Button anzeigen
		if($demo)
		{
			
			$frage2 = new frage();
			$nextfrage = $frage2->getNextFrage($gebiet_id, $_SESSION['pruefling_id'], $frage_id, $demo);

			if($nextfrage)
			{
				$value="Demo";
				echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;frage_id=$nextfrage' class='Item'>$value &gt;&gt;</a>";
			}
			else
			{
				//Naechste Frage holen und Weiter-Button anzeigen
				//$frage = new frage();
				//$nextfrage = $frage->getNextFrage($gebiet_id, $_SESSION['pruefling_id'], $frage_id, $demo);
				
				$qry = "SELECT count(*) as anzahl FROM testtool.tbl_frage 
						WHERE tbl_frage.gebiet_id=".$db->db_add_param($gebiet_id, FHC_INTEGER)."
						AND demo ";
				if($row = $db->db_fetch_object($db->db_query($qry)))
				{
					if($row->anzahl>1)
					{
						//Bei Demos den Weiter-Button nur anzeigen, wenn ausser der Startseite noch andere Demoseiten vorhanden sind
						echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id' class='Item'>".$p->t("testtool/startseite")." &gt;&gt;</a>";
					}
				}
			}
		}
	}
	if(!$demo && !$levelgebiet)
		echo " </tr></table>";
	
	echo '<br/><br/><br/><br/>';
	//Bild und Text der Frage anzeigen
	if($frage->bild!='')
		echo "<img class='testtoolfrage' src='bild.php?src=frage&amp;frage_id=$frage->frage_id&amp;sprache=".$_SESSION['sprache']."' /><br/><br/>\n";
		
	//Sound einbinden
	if($frage->audio!='')
	{
		//echo '<embed autostart="false" src="sound.php?src=frage&amp;frage_id='.$frage->frage_id.'&amp;sprache='.$_SESSION['sprache'].'" height="20" width="250"/><br />';
		echo '
			<script language="JavaScript" src="audio-player/audio-player.js"></script>
			<object type="application/x-shockwave-flash" data="audio-player/player.swf" id="audioplayer1" height="24" width="290">
			<param name="movie" value="audio_player/player.swf" />
			<param name="FlashVars" value="playerID=audioplayer1&amp;soundFile=sound.php%3Fsrc%3Dfrage%26frage_id%3D'.$frage->frage_id.'%26sprache%3D'.$_SESSION['sprache'].'" />
			<param name="quality" value="high" />
			<param name="menu" value="false" />
			<param name="wmode" value="transparent" />
			</object>';
	}
	echo "$frage->text<br/><br/>\n";

	//Vorschlaege laden
	$vs = new vorschlag();
	$vs->getVorschlag($frage->frage_id, $_SESSION['sprache'], $gebiet->zufallvorschlag);
	$letzte = $frage->getNextFrage($gebiet_id, $_SESSION['pruefling_id'], $frage_id, $demo);
	echo "<form action=\"$PHP_SELF?gebiet_id=$gebiet_id&amp;frage_id=$frage->frage_id\" method=\"POST\" ".(!$letzte && !$levelgebiet?"onsubmit=\"letzteFrage()\"":"").">";
	echo '<table cellspacing="30px">';
	echo '<tr>';
	$anzahl = 1;
	$beantwortet = false;
	
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
				$beantwortet = true;
			}
		}
		
		echo '<input type="'.$type.'" class="button_style" name="vorschlag_id[]" value="'.$vorschlag->vorschlag_id.'" '.$checked.'/>';
		
		echo '<br/>';
		if($vorschlag->bild!='')
			echo "<img class='testtoolvorschlag' src='bild.php?src=vorschlag&amp;vorschlag_id=$vorschlag->vorschlag_id&amp;sprache=".$_SESSION['sprache']."' /><br/>";
		if($vorschlag->audio!='')
		{
			//echo '<embed autostart="false" src="sound.php?src=vorschlag&amp;vorschlag_id='.$vorschlag->vorschlag_id.'&amp;sprache='.$_SESSION['sprache'].'" height="20" width="100"/><br />';
			echo '
				<script language="JavaScript" src="audio-player/audio-player.js"></script>
				<object type="application/x-shockwave-flash" data="audio-player/player.swf" id="audioplayer1" height="24" width="290">
				<param name="movie" value="audio_player/player.swf" />
				<param name="FlashVars" value="playerID=audioplayer1&amp;soundFile=sound.php%3Fsrc%3Dvorschlag%26vorschlag_id%3D'.$vs->vorschlag_id.'%26sprache%3D'.$_SESSION['sprache'].'" />
				<param name="quality" value="high" />
				<param name="menu" value="false" />
				<param name="wmode" value="transparent" />
				</object>';
		}
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
		echo '<input type="radio" class="button_style" name="vorschlag_id[]" value="" '.($beantwortet==false?'checked="checked"':'').'/><br /><font color="#acacac">'.$p->t('testtool/keineAntwort').'</font></td>';
	}
	echo '</tr></table>';
	
	if(!$demo)
	{
		echo '<input style="width:180px; white-space:normal" type="submit" name="submitantwort" value="'.$p->t('testtool/speichernUndWeiter').'" />';
	}
	echo "</form>";
	echo '<br/><br/><br/>';
	echo '</center>';
}
else
{
	//Wenn kein Demo vorhanden ist
	echo "<br/><br/><br/><center><b>".$p->t("testtool/startDrueckenUmZuBeginnen")."</b></center>";
}




?>

</body>
</html>
