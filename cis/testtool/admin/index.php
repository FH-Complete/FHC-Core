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
/**
 * Administrationsseite fuer das Testtool
 */

header('Content-type: application/xhtml+xml');

require_once('../../config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/frage.class.php');
require_once('../../../include/vorschlag.class.php');
require_once('../../../include/benutzerberechtigung.class.php');

$PHP_SELF=$_SERVER['PHP_SELF'];

session_start();

//wandelt einen String in HEX-Werte um
function strhex($string)
{
    $hex='';
    for ($i=0;$i<strlen($string);$i++)
        $hex.=(strlen(dechex(ord($string[$i])))<2)? "0".dechex(ord($string[$i])): dechex(ord($string[$i]));
    return $hex;
}

//Connection Herstellen
if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Oeffnen der Datenbankverbindung');

$user=get_uid();
$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin'))
	die('Sie haben keine Berechtigung fuer diese Seite');

if(isset($_GET['gebiet_id']))
	$gebiet_id = $_GET['gebiet_id'];
else
	$gebiet_id = '';

if(isset($_GET['nummer']))
	$nummer = $_GET['nummer'];
else
	$nummer = '';

if(isset($_GET['frage_id']))
	$frage_id = $_GET['frage_id'];
else
	$frage_id = '';

if(isset($_GET['vorschlag_id']))
	$vorschlag_id = $_GET['vorschlag_id'];
else
	$vorschlag_id = '';

$save_vorschlag_error=false;
?>
<?xml version="1.0" ?>

<?xml-stylesheet type="text/xsl" href="http://www.w3.org/Math/XSL/mathml.xsl"?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/xhtml; charset=utf-8" />
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css" />
<script language="Javascript">
//Vorschau anzeigen
function preview()
{
	document.getElementById('vorschau').innerHTML = document.getElementById('text').value;
}
</script>
<style type="text/css">

textarea {
font-size: 10pt;
}

</style>
</head>

<body>
<h1>
	<div style="float:left">Testtool - Administrationsseite</div>
	<div style="text-align:right"><a href="auswertung.php" class="Item">Auswertung</a></div>
</h1>
<?php

// aendern der Sprache
if(isset($_GET['type']) && $_GET['type']=='changesprache')
{
	$_SESSION['sprache']=$_GET['sprache'];
}

if(!isset($_SESSION['sprache']))
	$_SESSION['sprache']='German';
	
$sprache = $_SESSION['sprache'];

//Bei Upload des Bildes
if(isset($_POST['submitbild']))
{
	if(isset($_FILES['bild']['tmp_name']))
	{
		//Extension herausfiltern
    	$ext = explode('.',$_FILES['bild']['name']);
        $ext = strtolower($ext[count($ext)-1]);

        //--check that it's a jpeg or gif or png
        if ($ext=='gif' || $ext=='png' || $ext=='jpg' || $ext=='jpeg')
        {
			$filename = $_FILES['bild']['tmp_name'];
			//File oeffnen
			$fp = fopen($filename,'r');
			//auslesen
			$content = fread($fp, filesize($filename));
			fclose($fp);
			//in HEX-Werte umrechnen
			$content = strhex($content);

			$frage = new frage($conn);
			if($frage->getFrageSprache($_GET['frage_id'], $sprache))
			{
				//HEX Wert in die Datenbank speichern
				$frage->bild = $content;
				$frage->new = false;
				if($frage->save_fragesprache())
					echo "<b>Bild gespeichert</b><br />";
				else
					echo '<b>'.$frage->errormsg.'</b><br />';
			}
			else
				echo '<b>'.$frage->errormsg.'</b><br />';
		}
		else
			echo "<b>File ist kein gueltiges Bild</b><br />";
	}
}
//Bei Upload eines Audiofiles
if(isset($_POST['submitaudio']))
{
	if(isset($_FILES['audio']['tmp_name']))
	{
		//Extension herausfiltern
    	$ext = explode('.',$_FILES['audio']['name']);
        $ext = strtolower($ext[count($ext)-1]);

        //--check that it's a mp3
        if ($ext=='mp3' || $ext=='ogg')
        {
			$filename = $_FILES['audio']['tmp_name'];
			//File oeffnen
			$fp = fopen($filename,'r');
			//auslesen
			$content = fread($fp, filesize($filename));
			fclose($fp);
			//in HEX-Werte umrechnen
			$content = strhex($content);

			$frage = new frage($conn);
			if($frage->getFrageSprache($_GET['frage_id'], $sprache))
			{
				//HEX Wert in die Datenbank speichern
				$frage->audio = $content;
				$frage->new = false;
				if($frage->save_fragesprache())
					echo "<b>Audio gespeichert</b><br />";
				else
					echo '<b>'.$frage->errormsg.'</b><br />';
			}
			else
				echo '<b>'.$frage->errormsg.'</b><br />';
		}
		else
			echo "<b>Es duerfen nur mp3 Dateien hochgeladen werden</b><br />";
	}
}

//Speichern der Frage-Daten
if(isset($_POST['submitdata']))
{
	$frage = new frage($conn);
	if($frage->load($_GET['frage_id']))
	{
		$frage->demo = isset($_POST['demo']);
		$frage->nummer = $_POST['nummer'];
		$frage->level = $_POST['level'];
		$frage->new = false;
		
		if($frage->save())
		{
			if(!$frage->getFrageSprache($frage->frage_id, $sprache))
			{
				$frage->new=true;
			}
			
			$frage->text = $_POST['text'];
			$frage->sprache = $sprache;

			if($frage->save_fragesprache())
			{
				echo "<b>Daten gespeichert</b><br />";
				$nummer = $frage->nummer;
			}
			else 
				echo '<b>Fehler:'.$frage->errormsg.'</b><br />';
		}
		else
			echo '<b>'.$frage->errormsg.'</b><br />';
	}
	else
		echo '<b>'.$frage->errormsg.'</b><br />';
}

//Speichern eines Vorschlages
if(isset($_POST['submitvorschlag']))
{
	$bildcontent='';
	if(isset($_FILES['bild']['tmp_name']) && is_uploaded_file($_FILES['bild']['tmp_name']))
	{
		//Extension herausfiltern
    	$ext = explode('.',$_FILES['bild']['name']);
        $ext = strtolower($ext[count($ext)-1]);

        //--check that it's a jpeg or gif or png
        if ($ext=='gif' || $ext=='png' || $ext=='jpg' || $ext=='jpeg')
        {
			$filename = $_FILES['bild']['tmp_name'];
			//File oeffnen
			$fp = fopen($filename,'r');
			//auslesen
			$bildcontent = fread($fp, filesize($filename));
			fclose($fp);
			//in HEX-Werte umrechnen
			$bildcontent = strhex($bildcontent);
		}
		else
			echo "<b>Datei ist kein Bild!</b><br />";
	}
	
	$audiocontent='';
	if(isset($_FILES['audio']['tmp_name']) && is_uploaded_file($_FILES['audio']['tmp_name']))
	{
		//Extension herausfiltern
    	$ext = explode('.',$_FILES['audio']['name']);
        $ext = strtolower($ext[count($ext)-1]);

        //--check that it's a jpeg or gif or png
        if ($ext=='mp3')
        {
			$filename = $_FILES['audio']['tmp_name'];
			//File oeffnen
			$fp = fopen($filename,'r');
			//auslesen
			$audiocontent = fread($fp, filesize($filename));
			fclose($fp);
			//in HEX-Werte umrechnen
			$audiocontent = strhex($audiocontent);
		}
		else
			echo "<b>Datei ist kein Bild!</b><br />";
	}
	$vorschlag = new vorschlag($conn);
	$error=false;

	if($_POST['vorschlag_id']!='')
	{
		if($vorschlag->load($_POST['vorschlag_id']))
		{
			$vorschlag->new = false;
			$vorschlag->vorschlag_id = $_POST['vorschlag_id'];
		}
		else
		{
			echo '<b>Fehler beim Laden des Datensatzes</b><br />';
			$error = true;
		}
	}
	else
	{
		$vorschlag->new = true;
		$vorschlag->insertamum = date('Y-m-d H:i:s');
		$vorschlag->insertvon = $user;
	}
	if($_POST['nummer']=='' || !is_numeric($_POST['nummer']))
	{
		$error = true;
		echo '<b>Nummer ist ungueltig</b><br />';
	}

	if(!$error)
	{
		$vorschlag->bild = $bildcontent;
		$vorschlag->audio = $audiocontent;
		$vorschlag->frage_id = $_GET['frage_id'];
		$vorschlag->nummer = $_POST['nummer'];
		$vorschlag->punkte = $_POST['punkte'];
		$vorschlag->text = $_POST['text'];
		$vorschlag->sprache = $sprache;
		$vorschlag->updateamum = date('Y-m-d H:i:s');
		$vorschlag->updatevon = $user;
		
		if($vorschlag->save())
		{
			if($vorschlag->save_vorschlagsprache())
			{
				echo "<b>Vorschlag gespeichert</b><br />";
			}
			else 
			{
				$save_vorschlag_error=true;
				echo "Fehler beim Speichern von Vorschlagsprache: $vorschlag->errormsg<br />";
			}
		}
		else
		{
			$save_vorschlag_error=true;
			echo '<b>'.$vorschlag->errormsg.'</b><br />';
		}
	}
	else
		$save_vorschlag_error=true;
}
//Vorschlag loeschen
if(isset($_GET['type']) && $_GET['type']=='delete' && isset($_GET['vorschlag_id']))
{
	$vs = new vorschlag($conn);
	if(!$vs->delete($_GET['vorschlag_id']))
		echo '<b>'.$vs->errormsg.'</b><br />';
	$vorschlag_id='';
}

// anlegen einer neuen Frage
if(isset($_GET['type']) && $_GET['type']=='neuefrage')
{
	$frage_obj = new frage($conn);
	
	$frage_obj->gebiet_id = $_GET['gebiet_id'];
	$frage_obj->nummer=999;
	$frage_obj->demo=false;
	$frage_obj->insertamum = date('Y-m-d H:i:s');
	$frage_obj->insertvon = $user;
	$frage_obj->sprache = $sprache;
	$frage_obj->new = true;
	if($frage_obj->save())
	{
		if($frage_obj->save_fragesprache())
		{
			echo 'Frage wurde erfolgreich angelegt';
			$nummer=999;
		}
		else 
		{
			echo '<span class="error">Fehler beim Speichern der FrageSprache: '.$frage_obj->errormsg.'</span>';
		}
	}
	else 
	{
		echo '<span class="error">Fehler beim Speichern der Frage: '.$frage_obj->errormsg.'</span>';
	}
}

//Gebiet pruefen
if(isset($_GET['type']) && $_GET['type']=='gebietpruefen' && isset($_GET['gebiet_id']))
{
	$gebiet = new gebiet($conn, $gebiet_id);
	
	if($gebiet->check_gebiet($gebiet_id))
	{
		echo "<b>Das Gebiet $gebiet->bezeichnung wurde erfolgreich ueberprueft</b>";
	}
	else 
	{
		echo "<b>Bei der Ueberpruefung des Gebiets '$gebiet->bezeichnung' sind folgende Fehler aufgetreten:<br /></b>";
		echo nl2br($gebiet->errormsg);
		echo '<br /><br />';
	}
	
	$maxpunkte = $gebiet->berechneMaximalpunkte($gebiet_id);
	if($gebiet->maxpunkte!=$maxpunkte)
	{
		echo '<br /><span class="error">die empfohlene Punkteanzahl betraegt '.$maxpunkte.' Punkte anstatt '.$gebiet->maxpunkte.' Punkte</span>';
	}
}

echo '<table width="100%"><tr><td>';

//Liste der Gebiete
$qry  = "SELECT * FROM testtool.tbl_gebiet ORDER BY bezeichnung";
if($result = pg_query($conn, $qry))
{
	echo 'Gebiet: <select onchange="window.location.href=\''.$PHP_SELF.'?gebiet_id=\'+this.value;">';
	while($row = pg_fetch_object($result))
	{
		if($gebiet_id=='')
			$gebiet_id = $row->gebiet_id;
		
		if($gebiet_id==$row->gebiet_id)
			$selected='selected="selected"';
		else 
			$selected='';
		
		echo '<option value="'.$row->gebiet_id.'" '.$selected.'>'.$row->bezeichnung.'</option>'."\n";
	}
	echo '</select>';
}

echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;nummer=$nummer&amp;type=gebietpruefen' class='Item'>Pruefen</a> | ";
echo " <a href='edit_gebiet.php?gebiet_id=$gebiet_id' class='Item'>Bearbeiten</a>";
echo '</td><td align="right">';

//Liste der Sprachen

$qry = "SELECT sprache FROM public.tbl_sprache ORDER BY sprache DESC";

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		if($sprache=='')
			$sprache = $row->sprache;
		if($sprache==$row->sprache)
			$selected='style="border:1px solid black;"';
		else 
			$selected='';
		echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;nummer=$nummer&amp;type=changesprache&amp;sprache=$row->sprache' class='Item' $selected><img src='../bild.php?src=flag&amp;sprache=$row->sprache' alt='$row->sprache' title='$row->sprache'/></a>";
	}
}
echo '</td></tr></table>';
echo '<br />';

// Liste der Fragen
$qry = "SELECT distinct nummer FROM testtool.tbl_frage WHERE gebiet_id='".addslashes($gebiet_id)."' ORDER BY nummer";

if($result = pg_query($conn, $qry))
{
	echo 'Nummer: ';
	while($row = pg_fetch_object($result))
	{
		if($nummer=='')
			$nummer = $row->nummer;

		if($nummer==$row->nummer)
			echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;nummer=$row->nummer' class='Item'><u>$row->nummer</u></a> -";
		else
			echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;nummer=$row->nummer' class='Item'>$row->nummer</a> -";
	}
	echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;type=neuefrage' class='Item'>neue Frage hinzufuegen</a>";
}

echo "\n\n<br />";

//Fragen holen
$frage = new frage($conn);
$frage->getFragen($gebiet_id, $nummer);

if(count($frage->result)==1)
{
	$frage_id = $frage->result[0]->frage_id;
}
else 
{
	//Wenn fuer diese Nummer mehrere Fragen vorhanden sind,
	//koennen diese extra ausgewaehlt werden
	echo 'FrageID: ';
	foreach ($frage->result as $row) 
	{
		if($frage_id=='')
			$frage_id=$row->frage_id;
		
		if($frage_id==$row->frage_id)
			echo "<a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;nummer=$row->nummer&amp;frage_id=$row->frage_id' class='Item'><u>$row->frage_id</u></a> -";
		else 
			echo "<a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;nummer=$row->nummer&amp;frage_id=$row->frage_id' class='Item'>$row->frage_id</a> -";
	}
}

if($frage_id!='')
{
	$frage->load($frage_id);
	$frage->getFrageSprache($frage_id, $sprache);
	
	echo "<table><tr><td>";
	//Fragen
	echo "<table>";
	echo "<tr>";
	//Upload Feld fuer Bild
	echo "<td valign='bottom'>
			<form method='POST' enctype='multipart/form-data' action='$PHP_SELF?gebiet_id=$gebiet_id&amp;nummer=$nummer&amp;frage_id=$frage->frage_id'>
			Bild: <input type='file' name='bild' />
			<input type='submit' name='submitbild' value='Upload' />
			</form>
		</td>
		<td>
		<form method='POST' enctype='multipart/form-data' action='$PHP_SELF?gebiet_id=$gebiet_id&amp;nummer=$nummer&amp;frage_id=$frage->frage_id'>
			Audio: <input type='file' name='audio' />
			<input type='submit' name='submitaudio' value='Upload' />
			</form>
		</td>
		</tr>";
	//Wenn ein Bild vorhanden ist, dann anzeigen
	if($frage->bild!='')
	{
		echo "\n<tr><td width='400' height='300'><img src='../bild.php?src=frage&amp;frage_id=$frage->frage_id&amp;sprache=$sprache' width='400' height='300' />";
	}
	else
	{
		//echo "\n<tr><td align='center' width='400' height='300' style='background: #DDDDDD;'>Kein Bild vorhanden\n";
		echo "\n<tr><td align='center' width='400' height='300' style='background: #DDDDDD;'>\n";
		if($frage->audio=='')
			echo "Kein Bild vorhanden\n";
	}
	if($frage->audio!='')
	{
		echo '<br /><embed autostart="false" src="../sound.php?src=frage&amp;frage_id='.$frage->frage_id.'&amp;sprache='.$sprache.'" height="20" width="250"/>';
	}
	echo '</td>';
	//Zusaetzliche EingabeFelder anzeigen
	echo "<td>";
	echo "<form method='POST' action='$PHP_SELF?gebiet_id=$gebiet_id&amp;nummer=$nummer&amp;frage_id=$frage_id'>";
	echo "<table>";
	//Bei Aenderungen im Textfeld werden diese sofort in der Vorschau angezeigt
	echo "<tr><td colspan='2'>\n<textarea name='text' id='text' cols='40' rows='20' oninput='preview()'><![CDATA[$frage->text]]></textarea>\n</td></tr>";
	echo "<tr><td>Demo <input type='checkbox' name='demo' ".($frage->demo?'checked="true"':'')." />
			Level <input type='text' name='level' value='$frage->level' size='1' />
			Nummer <input type='text' name='nummer' value='$frage->nummer' size='1' /></td>
		 <td align='right'><input type='submit' value='Speichern' name='submitdata' /></td>";
	echo "</tr></table>";
	echo "</form>";
	echo "</td></tr>";
	//Vorschau fuer das Text-Feld
	echo "<tr><td colspan='2'>Vorschau:<br /><div id='vorschau' style='border: 1px solid black'>$frage->text</div></td></tr>";
	echo "</table>";
	echo '</td><td style="border-left: 1px solid black" valign="top">';

	$vorschlag = new vorschlag($conn);

	if($vorschlag_id!='')
		if(!$vorschlag->load($vorschlag_id, $sprache))
			die($vorschlag->errormsg);
	if($save_vorschlag_error)
	{
		$vorschlag->vorschlag_id = (isset($_POST['vorschlag_id'])?$_POST['vorschlag_id']:'');
		$vorschlag->frage_id = $_GET['frage_id'];
		$vorschlag->nummer = $_POST['nummer'];
		$vorschlag->punkte = $_POST['punkte'];
		$vorschlag->text = $_POST['text'];
		$vorschlag->bild = '';
	}
	//Vorschlag
	echo '<b>Vorschlag'.($vorschlag_id!=''?' Edit':'').'</b><br /><br />';
	echo "<form method='POST' enctype='multipart/form-data' action='$PHP_SELF?gebiet_id=$gebiet_id&amp;nummer=$nummer&amp;frage_id=$frage_id'>";
	echo "<input type='hidden' name='vorschlag_id' value='$vorschlag->vorschlag_id' />";
	echo '<table>';
	echo '<tr>';
	echo "<td>Punkte</td><td><input type='text' size='8' name='punkte' value='$vorschlag->punkte' /></td>";
	echo '</tr>';
	echo '<tr>';
	echo '<td>Text:</td><td><textarea name="text" id="text" rows="20" cols="40" oninput="preview()"><![CDATA['.$vorschlag->text."]]></textarea>\n</td>";
	echo '</tr><tr valign="top">';
	//Upload Feld fuer Bild
	echo "<td>Bild:</td><td><input type='file' name='bild' /></td>";
	echo '</tr>';
	echo '<tr>';
	//Upload Feld fuer Audio
	echo "<td>Audio:</td><td><input type='file' name='audio' /></td></tr>";
	echo "<tr><td>Nummer:</td><td><input type='text' name='nummer' size='3' value='$vorschlag->nummer' /></td></tr>";
	echo "<tr><td colspan='2' align='right'><input type='submit' name='submitvorschlag' value='Speichern' />".($vorschlag_id!=''?"<input type='button' value='Abbrechen' onclick=\"document.location.href='$PHP_SELF?gebiet_id=$gebiet_id&amp;nummer=$nummer&amp;frage_id=$frage->frage_id'\" />":'')."</td></tr>";
	echo "</table>";
	echo "</form>";

	echo '</td></tr></table>';

	$vorschlag = new vorschlag($conn);
	$vorschlag->getVorschlag($frage_id, $sprache, false);
	$i=0;
	if(count($vorschlag->result)>0)
	{
		echo '<table><tr class="liste"><th>Nummer</th><th>Punkte</th><th>Text</th><th>Bild</th><th>Audio</th><th></th><th></th></tr>';

		foreach ($vorschlag->result as $vs)
		{
			$i++;
			echo "<tr class='liste".($i%2)."'><td>$vs->nummer</td>
					  <td align='right'>$vs->punkte</td>
					  <td>$vs->text</td>
					  <td><img src='../bild.php?src=vorschlag&amp;vorschlag_id=$vs->vorschlag_id&amp;sprache=$sprache' /></td>			
					  <td>";
			if($vs->audio!='')
				echo "<embed autostart='false' src='../sound.php?src=vorschlag&amp;vorschlag_id=".$vs->vorschlag_id."&amp;sprache=".$sprache."' height='20' width='150'/>";
			echo "	  </td>
					  <td><a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;nummer=$nummer&amp;frage_id=$frage->frage_id&amp;vorschlag_id=$vs->vorschlag_id'>edit</a></td>
					  <td><a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;nummer=$nummer&amp;frage_id=$frage->frage_id&amp;vorschlag_id=$vs->vorschlag_id&amp;type=delete' onclick=\"return confirm('Wollen Sie diesen Eintrag wirklich loeschen?')\">delete</a></td>
				  </tr>";
		}
		echo '</table>';
	}
}


?>
</body>
</html>
