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

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/frage.class.php');
require_once('../../../include/vorschlag.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/studiengang.class.php');

if (!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

$PHP_SELF = $_SERVER['PHP_SELF'];
session_cache_limiter('none');
session_start();

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if (!$rechte->isBerechtigt('basis/testtool', null, 's'))
	die($rechte->errormsg);

$studiengang = new studiengang();
$studiengang->getAll('typ, kurzbz', false);
$stg_kz = (isset($_GET['stg_kz'])?$_GET['stg_kz']:'-1');

if (isset($_GET['gebiet_id']))
{
	$gebiet_id = $_GET['gebiet_id'];
}
else
{
	$gebiet_id = '';
}

if (isset($_GET['nummer']))
{
	$nummer = $_GET['nummer'];
}
else
{
	$nummer = '';
}

if (isset($_GET['frage_id']))
{
	$frage_id = $_GET['frage_id'];
}
else
{
	$frage_id = '';
}

if (isset($_GET['vorschlag_id']))
{
	$vorschlag_id = $_GET['vorschlag_id'];
}
else
{
	$vorschlag_id = '';
}
$filter = isset($_GET['filter'])?$_GET['filter']:'';

$save_vorschlag_error = false;
/*<?xml-stylesheet type="text/xsl" href="../mathml.xsl"?>*/

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<!DOCTYPE html
  PUBLIC "-//W3C//DTD XHTML 1.1 plus MathML 2.0//EN"
         "http://www.w3.org/Math/DTD/mathml2/xhtml-math11-f.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
<head>
<meta http-equiv="Content-Type" content="text/xhtml; charset=utf-8" />
<title>Testtool-Administration</title>
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css" />
<script language="Javascript">
//Vorschau anzeigen
function preview()
{
	document.getElementById('vorschau').innerHTML = document.getElementById('text').value;
}
function previewvorschlag()
{
	document.getElementById('vorschauvorschlag').innerHTML = document.getElementById('text_vorschlag').value;
}
function insertfrage(aTag, eTag)
{
	var input = document.forms['formular_frage'].elements['text'];
	input.focus();
	/* Einfügen des Formatierungscodes */
	var start = input.selectionStart;
	var end = input.selectionEnd;
	var insText = input.value.substring(start, end);
	input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);
	/* Anpassen der Cursorposition */
	var pos;
	if (insText.length == 0) {
		pos = start + aTag.length;
	} else {
		pos = start + aTag.length + insText.length + eTag.length;
	}
	input.selectionStart = pos;
	input.selectionEnd = pos;
}
function insertvorschlag(aTag, eTag)
{
	var input = document.forms['formular_vorschlag'].elements['text_vorschlag'];
	input.focus();
	/* Einfügen des Formatierungscodes */
	var start = input.selectionStart;
	var end = input.selectionEnd;
	var insText = input.value.substring(start, end);
	input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);
	/* Anpassen der Cursorposition */
	var pos;
	if (insText.length == 0) {
		pos = start + aTag.length;
	} else {
		pos = start + aTag.length + insText.length + eTag.length;
	}
	input.selectionStart = pos;
	input.selectionEnd = pos;
}

function confirmDeleteFrage()
{
	return confirm('Wollen Sie diese Frage wirklich löschen?');
}
</script>
<style type="text/css">

textarea {
font-size: 10pt;
}

.nummern
{
	border: 1px solid lightgrey;
	border-collapse: collapse;
	border-spacing: 0;
	padding: 2px;
}
td.nummern
{
	width: 15px;
	text-align: center;
}
</style>
</head>

<body>

<h1>
	<div style="float:left">Testtool - Administrationsseite</div>
	<div style="text-align:right; padding-right: 5px;">
		<a href="gebieteAnheangen.php?stg_kz=<?php echo $stg_kz?>">Ablauf</a> |
		<a href="uebersichtGebiete.php" class="Item" target="blank">Gebietübersicht</a> |
		<a href="uebersichtFragen.php" class="Item" target="blank">Fragenübersicht</a> |
		<a href="auswertung.php" class="Item">Auswertung</a> |
		<a href="Testtool.pdf" class="Item" target="_blank">Hilfe</a>
	</div>
</h1>
<?php

// aendern der Sprache
if (isset($_GET['type']) && $_GET['type'] == 'changesprache')
{
	$_SESSION['sprache'] = $_GET['sprache'];
}

if (!isset($_SESSION['sprache']))
	$_SESSION['sprache'] = DEFAULT_LANGUAGE;

$sprache = $_SESSION['sprache'];

//Bei Upload des Bildes
if (isset($_POST['submitbild']))
{
	if (!$rechte->isBerechtigt('basis/testtool', null, 'suid'))
		die($rechte->errormsg);

	if (isset($_FILES['bild']['tmp_name']))
	{
		//Extension herausfiltern
		$ext = explode('.', $_FILES['bild']['name']);
		$ext = mb_strtolower($ext[count($ext) - 1]);

		//--check that it's a jpeg or gif or png
		if ($ext == 'gif' || $ext == 'png' || $ext == 'jpg' || $ext == 'jpeg')
		{
			$filename = $_FILES['bild']['tmp_name'];
			//File oeffnen
			$fp = fopen($filename, 'r');
			//auslesen
			$content = fread($fp, filesize($filename));
			fclose($fp);
			//base64 codieren
			$content = base64_encode($content);

			$frage = new frage();
			if ($frage->getFrageSprache($_GET['frage_id'], $sprache))
			{
				//HEX Wert in die Datenbank speichern
				$frage->bild = $content;
				$frage->new = false;
				if ($frage->save_fragesprache())
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
if (isset($_POST['submitaudio']))
{
	if (!$rechte->isBerechtigt('basis/testtool', null, 'suid'))
		die($rechte->errormsg);

	if (isset($_FILES['audio']['tmp_name']))
	{
		//Extension herausfiltern
		$ext = explode('.', $_FILES['audio']['name']);
		$ext = mb_strtolower($ext[count($ext) - 1]);

		//--check that it's a mp3
		if ($ext == 'mp3' || $ext == 'ogg')
		{
			$filename = $_FILES['audio']['tmp_name'];
			//File oeffnen
			$content = file_get_contents($filename);
			/*$fp = fopen($filename,'r');
			//auslesen
			$content = fread($fp, filesize($filename));
			fclose($fp);*/
			//die('<br><br>'.$content);
			//base64 codieren
			$content = base64_encode($content);
			$frage = new frage();
			if ($frage->getFrageSprache($_GET['frage_id'], $sprache))
			{
				//HEX Wert in die Datenbank speichern
				$frage->audio = $content;
				$frage->new = false;
				if ($frage->save_fragesprache())
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
//Loeschen eines Bildes
if (isset($_POST['deletePicture']) || isset($_POST['deleteAudio']))
{
	if (!$rechte->isBerechtigt('basis/testtool', null, 'suid'))
		die($rechte->errormsg);

	$frage = new frage();
	if ($frage->load($_GET['frage_id']))
	{
		$frage->new = false;

		if ($frage->getFrageSprache($frage->frage_id, $sprache))
		{
			$frage->new = false;
		}
		else
			$frage->new = true;

		if (isset($_POST['deletePicture']))
			$frage->bild = '';
		elseif (isset($_POST['deleteAudio']))
			$frage->audio = '';

		if ($frage->save_fragesprache())
		{
			echo "<b>File erfolgreich gelöscht</b><br />";
			$nummer = $frage->nummer;
		}
		else
			echo '<b>Fehler:'.$frage->errormsg.'</b><br />';

	}
	else
		echo '<b>'.$frage->errormsg.'</b><br />';
}

//Speichern der Frage-Daten
if (isset($_POST['submitdata']))
{
	if (!$rechte->isBerechtigt('basis/testtool', null, 'suid'))
		die($rechte->errormsg);

	$frage = new frage();
	if ($frage->load($_GET['frage_id']))
	{
		$frage->demo = isset($_POST['demo']);
		$frage->nummer = $_POST['nummer'];
		$frage->level = $_POST['level'];
		$frage->aktiv = isset($_POST['aktiv'])?true:false;
		$frage->new = false;

		if ($frage->save())
		{
			if ($frage->getFrageSprache($frage->frage_id, $sprache))
			{
				$frage->new = false;
			}
			else
				$frage->new = true;

			if (isset($_POST['text']))
				$frage->text = $_POST['text'];
			$frage->sprache = $sprache;

			$xml = '<?xml version="1.0" encoding="utf-8"?><root>'.$frage->text.'</root>';
			libxml_use_internal_errors(true);
			if (simplexml_load_string($xml) || $_POST['text'] == '')
			{
				if ($frage->save_fragesprache())
				{
					echo "<b>Daten gespeichert</b><br />";
					$nummer = $frage->nummer;
				}
				else
					echo '<b>Fehler:'.$frage->errormsg.'</b><br />';
			}
			else
			{
				$frage_error_text = $frage->text;
				echo '<b>Fehler: Text ist kein gueltiges XML:<span class="error"><br />';
				foreach (libxml_get_errors() as $error)
				{
					echo $error->message.'<br />';
				}
				echo '</span></b><br />';
			}
		}
		else
			echo '<b>'.$frage->errormsg.'</b><br />';
	}
	else
		echo '<b>'.$frage->errormsg.'</b><br />';
}

//Speichern eines Vorschlages
if (isset($_POST['submitvorschlag']))
{
	if (!$rechte->isBerechtigt('basis/testtool', null, 'suid'))
		die($rechte->errormsg);

	$bildcontent = '';
	if (isset($_FILES['bild']['tmp_name']) && is_uploaded_file($_FILES['bild']['tmp_name']))
	{
		//Extension herausfiltern
		$ext = explode('.', $_FILES['bild']['name']);
		$ext = mb_strtolower($ext[count($ext) - 1]);

		//--check that it's a jpeg or gif or png
		if ($ext == 'gif' || $ext == 'png' || $ext == 'jpg' || $ext == 'jpeg')
		{
			$filename = $_FILES['bild']['tmp_name'];
			//File oeffnen
			$fp = fopen($filename, 'r');
			//auslesen
			$bildcontent = fread($fp, filesize($filename));
			fclose($fp);
			//base64 codieren
			$bildcontent = base64_encode($bildcontent);
		}
		else
			echo "<b>Datei ist kein Bild!</b><br />";
	}

	$audiocontent = '';
	if (isset($_FILES['audio']['tmp_name']) && is_uploaded_file($_FILES['audio']['tmp_name']))
	{
		//Extension herausfiltern
		$ext = explode('.', $_FILES['audio']['name']);
		$ext = mb_strtolower($ext[count($ext) - 1]);

		//--check that it's a jpeg or gif or png
		if ($ext == 'mp3')
		{
			$filename = $_FILES['audio']['tmp_name'];
			//File oeffnen
			$fp = fopen($filename, 'r');
			//auslesen
			$audiocontent = fread($fp, filesize($filename));
			fclose($fp);
			//base64 codieren
			$audiocontent = base64_encode($audiocontent);
		}
		else
			echo "<b>Datei ist kein Bild!</b><br />";
	}
	$vorschlag = new vorschlag();
	$error = false;

	if ($_POST['vorschlag_id'] != '')
	{
		if ($vorschlag->load($_POST['vorschlag_id']))
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
	if ($_POST['nummer'] == '' || !is_numeric($_POST['nummer']))
	{
		$error = true;
		echo '<b>Nummer ist ungueltig</b><br />';
	}

	if (!$error)
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
		$vorschlag->aktiv = isset($_POST['aktiv'])?true:false;

		$xml = '<?xml version="1.0" encoding="utf-8"?><root>'.$vorschlag->text.'</root>';
		libxml_use_internal_errors(true);
		if (simplexml_load_string($xml) || $_POST['text'] == '')
		{
			if ($vorschlag->save())
			{
				if ($vorschlag->save_vorschlagsprache())
				{
					echo "<b>Vorschlag gespeichert</b><br />";
				}
				else
				{
					$save_vorschlag_error = true;
					echo "Fehler beim Speichern von Vorschlagsprache: $vorschlag->errormsg<br />";
				}
			}
			else
			{
				$save_vorschlag_error = true;
				echo '<b>'.$vorschlag->errormsg.'</b><br />';
			}
		}
		else
		{
			$vorschlag_error_text = $vorschlag->text;
			echo '<b>Fehler: Text ist kein gueltiges XML:<span class="error"><br />';
			foreach (libxml_get_errors() as $error)
			{
				echo $error->message.'<br />';
			}
			echo '</span></b><br />';
		}
	}
	else
		$save_vorschlag_error = true;
}
//Vorschlag loeschen
if (isset($_GET['type']) && $_GET['type'] == 'delete' && isset($_GET['vorschlag_id']))
{
	if (!$rechte->isBerechtigt('basis/testtool', null, 'suid'))
		die($rechte->errormsg);

	$vs = new vorschlag();
	if (!$vs->delete($_GET['vorschlag_id']))
		echo '<b>'.$vs->errormsg.'</b><br />';
	$vorschlag_id = '';
}

if (isset($_POST['type']) && $_POST['type'] == 'deleteFrage')
{
	if (!$rechte->isBerechtigt('basis/testtool', null, 'suid'))
		die($rechte->errormsg);

	$frage = new frage();
	if (!$frage->delete($_GET['frage_id']))
	{
		echo "Löschen fehlgeschlagen:".$frage->errormsg;
	}
	$nummer = '';
}

// anlegen einer neuen Frage
if (isset($_GET['type']) && $_GET['type'] == 'neuefrage')
{
	if (!$rechte->isBerechtigt('basis/testtool', null, 'suid'))
		die($rechte->errormsg);

	$frage_obj = new frage();
	$nummer = $frage_obj->getMaxNummer($gebiet_id) + 1;

	$frage_obj->gebiet_id = $_GET['gebiet_id'];
	$frage_obj->nummer = $nummer;
	$frage_obj->demo = false;
	$frage_obj->insertamum = date('Y-m-d H:i:s');
	$frage_obj->insertvon = $user;
	$frage_obj->sprache = $sprache;
	$frage_obj->aktiv = true;
	$frage_obj->new = true;
	if ($frage_obj->save())
	{
		if ($frage_obj->save_fragesprache())
		{
			echo 'Frage wurde erfolgreich angelegt';
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
if (isset($_GET['type']) && $_GET['type'] == 'gebietpruefen' && isset($_GET['gebiet_id']))
{
	$gebiet = new gebiet($gebiet_id);

	if ($gebiet->check_gebiet($gebiet_id) && $gebiet->warningmsg == '')
	{
		echo "<b>Das Gebiet $gebiet->bezeichnung wurde erfolgreich ueberprueft</b>";
	}
	else
	{
		if ($gebiet->errormsg != '')
		{
			echo "<b>Bei der Ueberpruefung des Gebiets '$gebiet->bezeichnung' sind folgende Fehler aufgetreten:<br /></b>";
			echo "<span class='error'>".nl2br($gebiet->errormsg)."</span>";
			echo '<br /><br />';
		}
		if ($gebiet->warningmsg != '')
		{
			echo "<b>Folgende Warnungen sind aufgetreten:<br /></b>";
			echo nl2br($gebiet->warningmsg);
			echo '<br /><br />';
		}
	}

	$maxpunkte = $gebiet->berechneMaximalpunkte($gebiet_id);
	if ($gebiet->maxpunkte != $maxpunkte)
	{
		echo '<br /><span class="error">die empfohlene Punkteanzahl betraegt '.$maxpunkte.' Punkte anstatt '.$gebiet->maxpunkte.' Punkte</span>';
	}
}

//Vorschlaege aktiv und inaktiv setzen
if (isset($_GET['type']) && $_GET['type'] == 'vorschlaegeaktiv')
{
	if (isset($_POST['allevorschlaege']))
	{
		$vs = new vorschlag();
		$vs->new = false;
		if (isset($_POST['vorschlagaktiv']))
		{
			$vorschlaegeAktiv = array();
			$vorschlaegeInaktiv = array();
			$checkedVorschlaege = $_POST['vorschlagaktiv'];
			$allevorschlaege = array();
			$allevorschlaege = explode(",", rtrim($_POST['allevorschlaege'], ","));

			foreach ($allevorschlaege as $vorschlag)
			{
				$vs->load($vorschlag, $sprache);
				if (in_array($vorschlag, $checkedVorschlaege))
				{
					$vs->aktiv = true;
					$vs->save();
				}
				else
				{
					$vs->aktiv = false;
					$vs->save();
				}
			}
		}
		else
		{
			$allevorschlaege = array();
			$allevorschlaege = explode(",", rtrim($_POST['allevorschlaege'], ","));

			foreach ($allevorschlaege as $vorschlag)
			{
				$vs->load($vorschlag, $sprache);
				$vs->aktiv = false;
				$vs->save();
			}
		}
	}
}

echo '<table width="100%"><tr><td>';

//Liste der Studiengänge
echo 'Studiengang: <select onchange="window.location.href=this.value">';
		echo '<option value="'.$PHP_SELF.'?" >Alle Studiengänge</option>';
foreach ($studiengang->result as $row)
{
	$stg_arr[$row->studiengang_kz] = $row->kuerzel;
	if ($stg_kz == '')
	$stg_kz = $row->studiengang_kz;
	if ($row->studiengang_kz == $stg_kz)
	$selected = 'selected="selected"';
	else
	$selected = '';

	echo '<option value="'.$PHP_SELF.'?stg_kz='.$row->studiengang_kz.'" '.$selected.'>'.$db->convert_html_chars($row->kuerzel).' - '.$db->convert_html_chars($row->bezeichnung).'</option>'."\n";
}
		echo '</select>';

//Liste der Gebiete
	$qry = "SELECT * FROM testtool.tbl_ablauf WHERE studiengang_kz=".$db->db_add_param($stg_kz);
	$anzahl = $db->db_num_rows($db->db_query($qry));

	if ($stg_kz !== "-1" && $anzahl !== 0)
	{
		$qry = "SELECT * FROM testtool.tbl_gebiet LEFT JOIN testtool.tbl_ablauf USING (gebiet_id)
		WHERE studiengang_kz=".$db->db_add_param($stg_kz)." ORDER BY semester,reihung";
	}
	else
		$qry = "SELECT * FROM testtool.tbl_gebiet ORDER BY bezeichnung";

if (($anzahl !== 0) || ($stg_kz == '-1') && ($stg_kz !== ''))
{
	if ($result = $db->db_query($qry))
	{
		echo ' Gebiet:<select onchange="window.location.href=\''.$PHP_SELF.'?stg_kz='.$stg_kz.'&amp;gebiet_id=\'+this.value;">';
		//echo 'Gebiet: <select onchange="window.location.href=this.value">';

		while ($row = $db->db_fetch_object($result))
		{
			if ($gebiet_id == '')
				$gebiet_id = $row->gebiet_id;

			if ($gebiet_id == $row->gebiet_id)
				$selected = 'selected="selected"';
			else
				$selected = '';

			if ($stg_kz == "-1")
				echo '<option value="'.$row->gebiet_id.'" '.$selected.'>'.$row->bezeichnung.' - '.$row->kurzbz.' - '.$row->zeit.'</option>'."\n";
			else
				echo '<option value="'.$row->gebiet_id.'" '.$selected.'>('.$row->semester.') - '.$row->bezeichnung.' - '.$row->kurzbz.' - '.$row->zeit.'</option>'."\n";
		}
		echo '</select>';
	}

	echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;stg_kz=$stg_kz&amp;nummer=$nummer&amp;type=gebietpruefen' class='Item'>Prüfen</a> | ";
	echo " <a href='edit_gebiet.php?gebiet_id=$gebiet_id&amp;stg_kz=$stg_kz' class='Item'>Bearbeiten</a> |";
	echo " <a href='add_gebiet.php?stg_kz=".$stg_kz."'>Neues Gebiet</a> | ";
	echo " <a href='copy_gebiet.php?stg_kz=".$stg_kz."&amp;gebietToCopy=".$gebiet_id."'>Gebiet kopieren</a>";
	//echo " <a href='export.php?gebiet_id=$gebiet_id&amp;stg_kz=$stg_kz' target='_blank'>PDF exportieren</a>";
	//echo " <br/>Gebiet_id=".$gebiet_id."";
	echo '</td><td align="right">';

	//Liste der Sprachen

	$qry = "SELECT sprache FROM public.tbl_sprache WHERE content ORDER BY sprache DESC";

	if ($result = $db->db_query($qry))
	{
		while ($row = $db->db_fetch_object($result))
		{
			if ($sprache == '')
				$sprache = $row->sprache;
			if ($sprache == $row->sprache)
				$selected = 'style="border:1px solid black;"';
			else
				$selected = '';
			echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;nummer=$nummer&amp;stg_kz=$stg_kz&amp;type=changesprache&amp;sprache=$row->sprache' class='Item' $selected><img src='../bild.php?src=flag&amp;sprache=$row->sprache' alt='$row->sprache' title='$row->sprache'/></a>";
		}
	}
	echo '</td></tr></table>';
	echo '<br />';

	// Liste der Fragen
	if ($filter == 'aktiv')
	{
		$qry = "SELECT distinct nummer, aktiv, level, demo FROM testtool.tbl_frage WHERE gebiet_id=".$db->db_add_param($gebiet_id)." AND aktiv ORDER BY nummer";
	}
	elseif ($filter == 'inaktiv')
	{
		$qry = "SELECT distinct nummer, aktiv, level, demo FROM testtool.tbl_frage WHERE gebiet_id=".$db->db_add_param($gebiet_id)." AND NOT aktiv ORDER BY nummer";
	}
	else
	{
		$qry = "SELECT distinct nummer, aktiv, level, demo FROM testtool.tbl_frage WHERE gebiet_id=".$db->db_add_param($gebiet_id)." ORDER BY nummer";
	}

	if ($result = $db->db_query($qry))
	{
		$resultArray = array();
		$i = 0;
		while ($row = $db->db_fetch_object($result))
		{
			$resultArray[$i]['nummer'] = $row->nummer;
			$resultArray[$i]['aktiv'] = $row->aktiv;
			$resultArray[$i]['level'] = $row->level;
			$resultArray[$i]['demo'] = $row->demo;
			$i++;
		}
		// Aktiv / Inaktiv Checkboxes
		$aktivchecked = ($filter == 'aktiv' || $filter == '')?'checked="checked"':'';
		$inaktivchecked = ($filter == 'inaktiv' || $filter == '')?'checked="checked"':'';
		if ($filter == 'aktiv')
		{
			$link = "";
			echo '<a href="'.$PHP_SELF.'?gebiet_id='.$gebiet_id.'&amp;stg_kz='.$stg_kz.'&amp;nummer='.$nummer.'&amp;filter=inaktiv">
					<input type="checkbox" name="aktiv" '.$aktivchecked.' onclick="window.location.assign(\''.$PHP_SELF.'?gebiet_id='.$gebiet_id.'&amp;stg_kz='.$stg_kz.'&amp;nummer='.$nummer.'&amp;filter=inaktiv\');"/>aktiv</a>
					<a href="'.$PHP_SELF.'?gebiet_id='.$gebiet_id.'&amp;stg_kz='.$stg_kz.'&amp;nummer='.$nummer.'&amp;filter=">
					<input type="checkbox" name="inaktiv" '.$inaktivchecked.' onclick="window.location.assign(\''.$PHP_SELF.'?gebiet_id='.$gebiet_id.'&amp;stg_kz='.$stg_kz.'&amp;nummer='.$nummer.'&amp;filter=\');"/>inaktiv</a>';
		}
		elseif ($filter == 'inaktiv')
		{
			echo '<a href="'.$PHP_SELF.'?gebiet_id='.$gebiet_id.'&amp;stg_kz='.$stg_kz.'&amp;nummer='.$nummer.'&amp;filter=">
					<input type="checkbox" name="aktiv" '.$aktivchecked.' onclick="window.location.assign(\''.$PHP_SELF.'?gebiet_id='.$gebiet_id.'&amp;stg_kz='.$stg_kz.'&amp;nummer='.$nummer.'&amp;filter=\');"/>aktiv</a>
					<a href="'.$PHP_SELF.'?gebiet_id='.$gebiet_id.'&amp;stg_kz='.$stg_kz.'&amp;nummer='.$nummer.'&amp;filter=aktiv">
					<input type="checkbox" name="inaktiv" '.$inaktivchecked.' onclick="window.location.assign(\''.$PHP_SELF.'?gebiet_id='.$gebiet_id.'&amp;stg_kz='.$stg_kz.'&amp;nummer='.$nummer.'&amp;filter=aktiv\');"/>inaktiv</a>';
		}
		elseif ($filter == '')
		{
			echo '<a href="'.$PHP_SELF.'?gebiet_id='.$gebiet_id.'&amp;stg_kz='.$stg_kz.'&amp;nummer='.$nummer.'&amp;filter=inaktiv">
					<input type="checkbox" name="aktiv" '.$aktivchecked.' onclick="window.location.assign(\''.$PHP_SELF.'?gebiet_id='.$gebiet_id.'&amp;stg_kz='.$stg_kz.'&amp;nummer='.$nummer.'&amp;filter=inaktiv\');"/>aktiv</a>
					<a href="'.$PHP_SELF.'?gebiet_id='.$gebiet_id.'&amp;stg_kz='.$stg_kz.'&amp;nummer='.$nummer.'&amp;filter=aktiv">
					<input type="checkbox" name="inaktiv" '.$inaktivchecked.' onclick="window.location.assign(\''.$PHP_SELF.'?gebiet_id='.$gebiet_id.'&amp;stg_kz='.$stg_kz.'&amp;nummer='.$nummer.'&amp;filter=aktiv\');"/>inaktiv</a>';
		}
		echo '<br/><table class="nummern" style="display: inline-table;"><tbody><tr>
				<td>Nummer:</td>';
		foreach ($resultArray AS $key=>$value)
		{
			if ($nummer == '')
				$nummer = $value['nummer'];

			$style = '';
			if ($db->db_parse_bool($value['aktiv']) == false)
				$style = 'style="color: lightgrey"';

			$styleSelected = '';
			if ($nummer == $value['nummer'])
			{
				$styleSelected = 'style="background-color: lightblue"';
			}

			echo '<td class="nummern" '.$styleSelected.'><a href="'.$PHP_SELF.'?gebiet_id='.$gebiet_id.'&amp;stg_kz='.$stg_kz.'&amp;nummer='.$value['nummer'].'&amp;filter='.$filter.'" '.$style.'>'.$value['nummer'].'</a></td>';
		}
		echo '</tr><tr>
				<td>Level:</td>';
		$leveltext = '';
		foreach ($resultArray AS $key=>$value)
		{
			if ($value['level'] == '')
			{
				$leveltext = '-';
			}
			else
			{
				$leveltext = $value['level'];
				if ($value['demo'] == 't')
				{
					$leveltext .= '*';
				}
			}
			echo '<td class="nummern" style="color: grey">'.$leveltext.'</td>';
		}
		echo '</tr></tbody></table>';
		echo " <a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;stg_kz=$stg_kz&amp;type=neuefrage&amp;filter=$filter' class='Item'>neue Frage hinzufuegen</a>";
		$frage_obj = new frage();
		if ($filter == 'aktiv')
		{
			$nextNummer = $frage_obj->getNextFrageNummer($nummer, $gebiet_id, true);
		}
		elseif ($filter == 'inaktiv')
		{
			$nextNummer = $frage_obj->getNextFrageNummer($nummer, $gebiet_id, false);
		}
		else
		{
			$nextNummer = $frage_obj->getNextFrageNummer($nummer, $gebiet_id);
		}
		//if ($nummer<$db->db_num_rows($result)-1)
		if ($nextNummer != '')
			echo " - <a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;stg_kz=$stg_kz&amp;nummer=".($nextNummer)."&amp;filter=".$filter."' class='Item'>Weiter &gt;&gt;</a>";
	}

	echo "\n\n<br />";

	//Fragen holen
	$frage = new frage();
	$frage->getFragen($gebiet_id, $nummer);

	if (count($frage->result) == 1)
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
			if ($frage_id == '')
				$frage_id = $row->frage_id;

			if ($frage_id == $row->frage_id)
				echo "<a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;stg_kz=$stg_kz&amp;nummer=$row->nummer&amp;frage_id=$row->frage_id' class='Item'><u>$row->frage_id</u></a> -";
			else
				echo "<a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;stg_kz=$stg_kz&amp;nummer=$row->nummer&amp;frage_id=$row->frage_id' class='Item'>$row->frage_id</a> -";
		}
	}
}
else
{
	echo ' <strong>Keine Gebiete in diesem Studiengang</strong> ';
	echo '</td></tr></table>';
}

if ($frage_id != '')
{
	$frage->load($frage_id);
	$frage->getFrageSprache($frage_id, $sprache);


	echo '<table><tr><td valign="top" align="right">';
	echo '<form action="'.$PHP_SELF.'?gebiet_id='.$gebiet_id.'&amp;stg_kz='.$stg_kz.'&amp;nummer='.$nummer.'&amp;frage_id='.$frage->frage_id.'&amp;filter='.$filter.'" method="POST" onsubmit="return confirmDeleteFrage()">
	<input type="hidden" name="type" value="deleteFrage" />
	<input type="submit" value="Frage löschen" />
	</form>';
	//Fragen
	echo "<table>";
	echo "<tr>";
	//Upload Feld fuer Bild
	echo "<td valign='bottom'>
			<form method='POST' enctype='multipart/form-data' action='$PHP_SELF?gebiet_id=$gebiet_id&amp;stg_kz=$stg_kz&amp;nummer=$nummer&amp;frage_id=$frage->frage_id&amp;filter=$filter'>
			Bild: <input type='file' name='bild' />
			<input type='submit' name='submitbild' value='Upload' />
			</form>
		</td>
		<td>
		<form method='POST' enctype='multipart/form-data' action='$PHP_SELF?gebiet_id=$gebiet_id&amp;stg_kz=$stg_kz&amp;nummer=$nummer&amp;frage_id=$frage->frage_id&amp;filter=$filter'>
			Audio: <input type='file' name='audio' />
			<input type='submit' name='submitaudio' value='Upload' />
			</form>
		</td>
		</tr>";
	//Wenn ein Bild vorhanden ist, dann anzeigen
	if ($frage->bild != '')
	{
		echo "\n<tr><td width='400' height='300'><img src='../bild.php?src=frage&amp;frage_id=$frage->frage_id&amp;sprache=$sprache' width='400' />";
		echo "\n<form method='POST' enctype='multipart/form-data' action='$PHP_SELF?gebiet_id=$gebiet_id&amp;stg_kz=$stg_kz&amp;nummer=$nummer&amp;frage_id=$frage->frage_id&amp;filter=$filter'>";
		echo "\n<center><input type='submit' name='deletePicture' value='Delete Picture' onclick='return confirm(\"Bild löschen?\")'/></center>";
		echo "\n</form>";
	}
	else
	{
		echo "\n<tr><td align='center' width='400' style='background: #DDDDDD;'>\n";
		if ($frage->audio == '')
			echo "Kein Bild vorhanden\n";
	}
	$timestamp = time();
	if ($frage->audio != '')
	{
		echo '	<audio src="../sound.php?src=frage&amp;frage_id='.$frage->frage_id.'&amp;sprache='.$sprache.'&amp;'.$timestamp.'" controls="controls">
					<div>
						<p>Ihr Browser unterstützt dieses Audioelement leider nicht.</p>
					</div>
				</audio>';
		echo "\n<form method='POST' enctype='multipart/form-data' action='$PHP_SELF?gebiet_id=$gebiet_id&amp;stg_kz=$stg_kz&amp;nummer=$nummer&amp;frage_id=$frage->frage_id&amp;filter=$filter'>";
		echo "\n<center><input type='submit' name='deleteAudio' value='Delete Audio' onclick='return confirm(\"Audiodatei löschen?\")'/></center>";
		echo "\n</form>";
	}
	echo '</td>';
	//Zusaetzliche EingabeFelder anzeigen
	echo "<td>";
	echo "<form name='formular_frage' method='POST' action='$PHP_SELF?gebiet_id=$gebiet_id&amp;stg_kz=$stg_kz&amp;nummer=$nummer&amp;frage_id=$frage_id&amp;filter=$filter'>";
	echo "<table>";
	//Bei Aenderungen im Textfeld werden diese sofort in der Vorschau angezeigt
	//Wenn beim Speichern der Text kein Gueltiges XML ist, wird der vorige Text erneut angezeigt

	echo "<tr valign='top'><td colspan='2'>\n<textarea name='text' id='text' cols='50' rows='27' oninput='preview()' ".($frage->aktiv == false?'disabled="disabled"':'')."><![CDATA[".(isset($frage_error_text)?$frage_error_text:$frage->text)."]]></textarea>\n</td>";
	echo "<table><tr><td><input type='button' value='br' onclick='insertfrage(\"&lt;br/&gt;\", \"\")' />";
	echo "<input type='button' value='F' style='font-weight:bold' onclick='insertfrage(\"&lt;strong&gt;\", \"&lt;/strong&gt;\")' />";
	echo "<input type='button' value='K' style='font-style:italic' onclick='insertfrage(\"&lt;i&gt;\", \"&lt;/i&gt;\")' /><br/><br/>";
	echo "<input type='button' value='MathML' onclick='insertfrage(\"&lt;math xmlns=\&quot;http://www.w3.org/1998/Math/MathML\&quot;&gt;\", \"&lt;/math&gt;\")' title='Deklaration' /><br/>";
	echo "<input type='button' value='mrow' onclick='insertfrage(\"&lt;mrow&gt;\", \"&lt;/mrow&gt;\")' title='Zusammenhängende Zeile' /><br/>";
	echo "<input type='button' value='mo' onclick='insertfrage(\"&lt;mo&gt;\", \"&lt;/mo&gt;\")' title='Operator (+,-,=,...)' /><br/>";
	echo "<input type='button' value='mn' onclick='insertfrage(\"&lt;mn&gt;\", \"&lt;/mn&gt;\")' title='Number (1,2,3,...)' /><br/>";
	echo "<input type='button' value='mi' onclick='insertfrage(\"&lt;mi&gt;\", \"&lt;/mi&gt;\")' title='Identifier (Variablen x,y,...)' /><br/>";
	echo "<input type='button' value='mfrac' onclick='insertfrage(\"&lt;mfrac&gt;\", \"&lt;/mfrac&gt;\")' title='Bruch' /><br/>";
	echo "<input type='button' value='msup' onclick='insertfrage(\"&lt;msup&gt;\", \"&lt;/msup&gt;\")' title='Hochgestellt' /><br/>";
	echo "<input type='button' value='msub' onclick='insertfrage(\"&lt;msub&gt;\", \"&lt;/msub&gt;\")' title='Tiefgestellt' /><br/>";
	echo "<input type='button' value='mspace' onclick='insertfrage(\"&lt;mspace width=\&quot;3px\&quot;/&gt;\", \"\")' title='Leerraum (einstellbar)' /><br/>";
	echo "<input type='button' value='mfenced' onclick='insertfrage(\"&lt;mfenced&gt;\", \"&lt;/mfenced&gt;\")' title='Große Klammern' /><br/>";
	echo "<input type='button' value='msqrt' onclick='insertfrage(\"&lt;msqrt&gt;\", \"&lt;/msqrt&gt;\")' title='Wurzel' /><br/>";
	echo "<input type='button' value='munderover' onclick='insertfrage(\"&lt;munderover&gt;&lt;mo movablelimits=\&quot;false\&quot;&gt;Das steht mittig&lt;/mo&gt;&lt;mo&gt;Das steht unten&lt;/mo&gt;&lt;mo&gt;Das steht oben&lt;/mo&gt;&lt;/munderover&gt;\", \"\")' title='Oben und unten' /><br/>";
	echo "<input type='button' value='mtext' onclick='insertfrage(\"&lt;mtext&gt;\", \"&lt;/mtext&gt;\")' title='Text' /><br/>";
	echo "Operatoren:<br/>π<br/>·<br/>∑<br/>∫<br/><a href='http://de.selfhtml.org/html/referenz/zeichen.htm#benannte_iso8859_1' target='blank'>Weitere</a>";
	echo "</td>";
	echo "</tr></table></tr>";
	echo "<tr><td>Demo <input type='checkbox' name='demo' ".($frage->demo?'checked="true"':'')." />
			Level <input type='text' name='level' value='$frage->level' size='1' />
			Nummer <input type='text' name='nummer' value='$frage->nummer' size='1' />
			Aktiv <input type='checkbox' name='aktiv' ".($frage->aktiv == true?'checked="true"':'')." /></td>
		 <td align='right'><input type='submit' value='Speichern' name='submitdata' /></td>";
	echo "</tr></table>";
	echo "</form>";
	echo "</td></tr>";
	//Vorschau fuer das Text-Feld
	echo "<tr><td colspan='2'>Vorschau:<br /><div id='vorschau' style='border: 1px solid black' align='center'>$frage->text</div></td></tr>";
	echo "</table>";
	echo '</td><td style="border-left: 1px solid black" valign="top">';

	$vorschlag = new vorschlag();

	if ($vorschlag_id != '')
		if (!$vorschlag->load($vorschlag_id, $sprache))
			die($vorschlag->errormsg);

	if ($save_vorschlag_error)
	{
		$vorschlag->vorschlag_id = (isset($_POST['vorschlag_id'])?$_POST['vorschlag_id']:'');
		$vorschlag->frage_id = $_GET['frage_id'];
		$vorschlag->nummer = $_POST['nummer'];
		$vorschlag->punkte = $_POST['punkte'];
		$vorschlag->text = $_POST['text'];
		$vorschlag->bild = '';
	}
	//Vorschlag
	echo '<b>Vorschlag'.($vorschlag_id != ''?' Edit':'').'</b><br /><br />';
	echo "<form name='formular_vorschlag' method='POST' enctype='multipart/form-data' action='$PHP_SELF?gebiet_id=$gebiet_id&amp;stg_kz=$stg_kz&amp;nummer=$nummer&amp;frage_id=$frage_id&amp;filter=$filter'>";
	echo "<input type='hidden' name='vorschlag_id' value='$vorschlag->vorschlag_id' />";
	echo '<table>';
	if (!isset($vorschlag_id) || $vorschlag_id != '')
	{
		echo "<tr><td>Nummer:</td><td><input type='text' name='nummer' size='3' id='nummer' value='$vorschlag->nummer' />";
	}
	else
	{
		if (isset($_GET['gebiet_id']) && isset($_GET['nummer']))
		{
			$fragef1 = new frage();
			$fragef1->getFragen($_GET['gebiet_id'], $_GET['nummer']);
			if(isset($fragef1->result[0]))
			{
				$vorschlag->getVorschlag($fragef1->result[0]->frage_id, $sprache, false, false);
				echo "<tr><td>Nummer:</td><td><input type='text' name='nummer' size='3' id='nummer' value='".(count($vorschlag->result) + 1)."' />"; //@Todo: Count ($vorschlag->result) liefert die naechste Nummer nur dann richtig, falls keine Zahl dazwischen fehlt (1,3,4,..). Hier sollte die letzte Nummer ermittelt werden.
			}
			else
				echo "<tr><td>Nummer:</td><td><input type='text' name='nummer' size='3' id='nummer' />";
		}
		else
		{
			echo "<tr><td>Nummer:</td><td><input type='text' name='nummer' size='3' id='nummer' />";
		}
	}
	echo "<input type='button' value='1' onclick='document.getElementById(\"nummer\").value=\"1\";' />";
	echo "<input type='button' value='2' onclick='document.getElementById(\"nummer\").value=\"2\";' />";
	echo "<input type='button' value='3' onclick='document.getElementById(\"nummer\").value=\"3\";' />";
	echo "<input type='button' value='4' onclick='document.getElementById(\"nummer\").value=\"4\";' /></td></tr>";
	echo '<tr>';
	echo "<td>Punkte:</td><td><input type='text' size='8' id='punkte' name='punkte' value='$vorschlag->punkte' onchange='this.value = this.value.replace(/,/g, \".\")'/>";
	echo "<input type='button' style='background-color:#FFBFBF' value='-1/2' onclick='document.getElementById(\"punkte\").value=\"-0.5\";' />";
	echo "<input type='button' style='background-color:#FFBFBF' value='-1/3' onclick='document.getElementById(\"punkte\").value=\"-0.3333\";' />";
	echo "<input type='button' value='-1' style='background-color:#FFBFBF' onclick='document.getElementById(\"punkte\").value=\"-1\";' />";
	echo "<input type='button' style='background-color:#CCCCCC' value='0' onclick='document.getElementById(\"punkte\").value=\"0\";' />";
	echo "<input type='button' value='+1' style='background-color:#C5FFBF' onclick='document.getElementById(\"punkte\").value=\"1\";' />";
	echo "<input type='button' value='+1/3' style='background-color:#C5FFBF' onclick='document.getElementById(\"punkte\").value=\"0.3333\";' />";
	echo "<input type='button' value='+1/2' style='background-color:#C5FFBF' onclick='document.getElementById(\"punkte\").value=\"0.5\";' /></td>";
	echo '</tr>';
	echo '<tr valign="top">';
	echo '<td>Text:</td><td><textarea name="text" id="text_vorschlag" rows="25" cols="45" oninput="previewvorschlag()" '.($vorschlag->aktiv == true || is_null($vorschlag->aktiv)?'':'disabled="disabled"').'><![CDATA['.$vorschlag->text."]]></textarea>\n</td>";
	echo "<td><input type='button' value='br' onclick='insertvorschlag(\"&lt;br/&gt;\", \"\")' />";
	echo "<input type='button' value='F' style='font-weight:bold' onclick='insertvorschlag(\"&lt;strong&gt;\", \"&lt;/strong&gt;\")' />";
	echo "<input type='button' value='K' style='font-style:italic' onclick='insertvorschlag(\"&lt;i&gt;\", \"&lt;/i&gt;\")' /><br/><br/>";
	echo "<input type='button' value='MathML' onclick='insertvorschlag(\"&lt;math xmlns=\&quot;http://www.w3.org/1998/Math/MathML\&quot;&gt;\", \"&lt;/math&gt;\")' title='Deklaration' /><br/>";
	echo "<input type='button' value='mrow' onclick='insertvorschlag(\"&lt;mrow&gt;\", \"&lt;/mrow&gt;\")' title='Zusammenhängende Zeile' /><br/>";
	echo "<input type='button' value='mo' onclick='insertvorschlag(\"&lt;mo&gt;\", \"&lt;/mo&gt;\")' title='Operator (+,-,=,...)' /><br/>";
	echo "<input type='button' value='mn' onclick='insertvorschlag(\"&lt;mn&gt;\", \"&lt;/mn&gt;\")' title='Number (1,2,3,...)' /><br/>";
	echo "<input type='button' value='mi' onclick='insertvorschlag(\"&lt;mi&gt;\", \"&lt;/mi&gt;\")' title='Identifier (Variablen x,y,...)' /><br/>";
	echo "<input type='button' value='mfrac' onclick='insertvorschlag(\"&lt;mfrac&gt;\", \"&lt;/mfrac&gt;\")' title='Bruch' /><br/>";
	echo "<input type='button' value='msup' onclick='insertvorschlag(\"&lt;msup&gt;\", \"&lt;/msup&gt;\")' title='Hochgestellt' /><br/>";
	echo "<input type='button' value='msub' onclick='insertvorschlag(\"&lt;msub&gt;\", \"&lt;/msub&gt;\")' title='Tiefgestellt' /><br/>";
	echo "<input type='button' value='mspace' onclick='insertvorschlag(\"&lt;mspace width=\&quot;3px\&quot;/&gt;\", \"\")' title='Leerraum (einstellbar)' /><br/>";
	echo "<input type='button' value='mfenced' onclick='insertvorschlag(\"&lt;mfenced&gt;\", \"&lt;/mfenced&gt;\")' title='Große Klammern' /><br/>";
	echo "<input type='button' value='msqrt' onclick='insertvorschlag(\"&lt;msqrt&gt;\", \"&lt;/msqrt&gt;\")' title='Wurzel' /><br/>";
	echo "<input type='button' value='munderover' onclick='insertvorschlag(\"&lt;munderover&gt;&lt;mo movablelimits=\&quot;false\&quot;&gt;Das steht mittig&lt;/mo&gt;&lt;mo&gt;Das steht unten&lt;/mo&gt;&lt;mo&gt;Das steht oben&lt;/mo&gt;&lt;/munderover&gt;\", \"\")' title='Oben und unten' /><br/>";
	echo "<input type='button' value='mtext' onclick='insertvorschlag(\"&lt;mtext&gt;\", \"&lt;/mtext&gt;\")' title='Text' /><br/>";
	echo "Operatoren:<br/>π<br/>·<br/>∑<br/>∫<br/><a href='http://de.selfhtml.org/html/referenz/zeichen.htm#benannte_iso8859_1' target='blank'>Weitere</a>";
	echo "</td>";
	echo '</tr><tr valign="top">';
	//Upload Feld fuer Bild
	echo "<td>Bild:</td><td><input type='file' name='bild' /></td>";
	echo '</tr>';
	echo '<tr>';
	//Upload Feld fuer Audio
	echo "<td>Audio:</td><td><input type='file' name='audio' /></td></tr>";

	//Aktiv Checkbox
	echo "<tr><td>Aktiv</td><td><input type='checkbox' name='aktiv' ";
	if ($vorschlag->aktiv == true || is_null($vorschlag->aktiv))
	{
		echo "checked='checked'";
	}
	elseif ($vorschlag->aktiv == false)
	{
		echo "";
	}
	echo "/></td></tr>";
	echo "<tr><td colspan='2' align='right'><input type='submit' name='submitvorschlag' value='Speichern' />".($vorschlag_id != ''?"<input type='button' value='Abbrechen' onclick=\"document.location.href='$PHP_SELF?gebiet_id=$gebiet_id&amp;stg_kz=$stg_kz&amp;nummer=$nummer&amp;frage_id=$frage->frage_id'\" />":'')."</td></tr>";
	//Vorschau fuer das Text-Feld
	echo "<tr><td colspan='2'>Vorschau:<br /><div id='vorschauvorschlag' style='border: 1px solid black' align='center'>$vorschlag->text</div></td></tr>";
	echo "</table>";
	echo "</form>";

	echo '</td></tr></table>';

	$vorschlag = new vorschlag();
	$vorschlag->getVorschlag($frage_id, $sprache, false, false);
	$i = 0;
	$allevorschlaege = '';
	if (count($vorschlag->result) > 0)
	{
		echo '<form action="'.$_SERVER['PHP_SELF'].'?gebiet_id='.$gebiet_id.'&amp;stg_kz='.$stg_kz.'&amp;nummer='.$nummer.'&amp;frage_id='.$frage->frage_id.'&amp;type=vorschlaegeaktiv&amp;filter='.$filter.'" method="POST">';
		echo '<table><tr class="liste"><th>Nummer</th><th>Punkte</th><th>Text</th><th>Bild</th><th>Audio</th><th></th><th></th><th>Aktiv</th></tr>';

		$a = array();
		foreach ($vorschlag->result as $vs)
		{
			$i++;
			echo "<tr class='liste".($i % 2)."'><td>$vs->nummer</td>";
			if ($vs->punkte >= 0)
			{
				echo "<td align='right'>$vs->punkte";
			}
			else
				echo "<td align='right' style='color:#FF8204'>$vs->punkte";
			echo "</td><td>$vs->text</td>
					<td>".($vs->bild != ''?"<img src='../bild.php?src=vorschlag&amp;vorschlag_id=$vs->vorschlag_id&amp;sprache=$sprache' height='24' onmouseover='height=200' onmouseout='height=24'/>":"")."</td>
					<td>";
			$a[] = $vs->punkte;
			if ($vs->audio != '')
			{
				echo '	<audio src="../sound.php?src=vorschlag&amp;vorschlag_id='.$vs->vorschlag_id.'&amp;sprache='.$sprache.'&amp;'.$timestamp.'" controls="controls">
							<div>
								<p>Ihr Browser unterstützt dieses Audioelement leider nicht.</p>
							</div>
						</audio>';
			}
			echo "	</td>
					<td><a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;stg_kz=$stg_kz&amp;nummer=$nummer&amp;frage_id=$frage->frage_id&amp;vorschlag_id=$vs->vorschlag_id'>edit</a></td>
					<td><a href='$PHP_SELF?gebiet_id=$gebiet_id&amp;stg_kz=$stg_kz&amp;nummer=$nummer&amp;frage_id=$frage->frage_id&amp;vorschlag_id=$vs->vorschlag_id&amp;type=delete' onclick=\"return confirm('Wollen Sie diesen Eintrag wirklich loeschen?')\">delete</a></td>
					<td align='center'><input type='checkbox' name='vorschlagaktiv[]' value='".$vs->vorschlag_id."' ";
			$vss = new vorschlag();
			$vss->load($vs->vorschlag_id);
			if ($vss->aktiv == true) echo "checked='checked'";

			echo "/></td></tr>";
			$allevorschlaege .= $vs->vorschlag_id.",";
		}

		echo '<tr><td><input type="hidden" name="allevorschlaege" value="'.$allevorschlaege.'" />Summe:</td><td align="left">'.number_format(array_sum($a), 2, ".", "").'&nbsp;&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td><input type="submit" value="Speichern"/></td></tr>';
		echo '</table></form><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>';
	}
}


?>
</body>
</html>
