<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Bearbeiten und Eintragen von Coodle Umfragen
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/coodle.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/benutzerberechtigung.class.php');

$user = get_uid();
$sprache = getSprache();
$p = new phrasen($sprache);
$datum_obj = new datum();
$db = new basis_db();
$message='';

if(!check_lektor($user))
	die($p->t('global/keineBerechtigung'));

// Administratoren duerfen die UID als Parameter uebergeben um die Umfragen von anderen Personen anzuzeigen
if(isset($_GET['uid']))
{
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
	if($rechte->isBerechtigt('admin'))
	{
		$user = $_GET['uid'];
		$getParam = '&uid='.$user;
	}
	else
		$getParam = '';
}
else
	$getParam = '';

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
		"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet"  href="../../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	<link rel="stylesheet" href="../../../skin/styles/jquery.css" type="text/css">
	<link rel="stylesheet" href="../../../skin/jquery-ui-1.9.2.custom.min.css" type="text/css">
	<link rel="stylesheet" href="../../../vendor/fgelinas/timepicker/jquery.ui.timepicker.css" type="text/css"/>
	<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
	<script src="../../../include/tiny_mce/tiny_mce.js" type="text/javascript" ></script>
	<script src="../../../vendor/fgelinas/timepicker/jquery.ui.timepicker.js" type="text/javascript" ></script>
	<script type="text/javascript">
	$(document).ready(function()
	{
		$.datepicker.setDefaults( $.datepicker.regional[ "de" ] );
		$("#datepicker_datum").datepicker(
		{
			changeMonth: true,
			defaultDate: "+7",
			minDate: "0",
		});
		$(".timepicker").timepicker(
		{
			showPeriodLabels: false,
			showHours: false,
			minuteText: "",
			minutes: {starts: 30, ends: 150, interval: 30},
			rows: 5,
		});

	});
	</script>
	<script type="text/javascript">
	tinyMCE.init
	(
		{
		mode : "textareas",
		theme : "advanced",
		language : "de",
		file_browser_callback: "FHCFileBrowser",

		plugins : "spellchecker,pagebreak,style,layer,table,advhr,advimage,advlink,inlinepopups,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",

		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink,|,bullist,pastetext",
		theme_advanced_buttons2 : "",
		theme_advanced_buttons3 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "center",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		force_br_newlines : true,
		force_p_newlines : false,
		forced_root_block : "",
		//Formatierungen beim Einfuegen entfernen
		paste_auto_cleanup_on_paste : true,
		paste_remove_styles: true,
		paste_remove_styles_if_webkit: true,
		paste_strip_class_attributes: true,
		paste_retain_style_properties: "",
		paste_text_sticky: true,
		setup : function(ed)
		{
			ed.onInit.add(function(ed)
			{	ed.pasteAsPlainText = true;
				ed.controlManager.setActive("pastetext", true);
			});
		   }

		}
	);

	function checkrequired()
	{
		var error = false;
		if(document.getElementById("titel").value == "")
		{
			document.getElementById("titel").style.border = "solid red 2px";
			error = true;
		}
		else
		{
			document.getElementById("titel").style.border = "";
		}
		if(document.getElementById("dauer").value == "")
		{
			document.getElementById("dauer").style.border = "solid red 2px";
			error = true;
		}
		else
		{
			document.getElementById("dauer").style.border = "";
		}
		if(document.getElementById("datepicker_datum").value == "")
		{
			document.getElementById("datepicker_datum").style.border = "solid red 2px";
			error = true;
		}
		else
		{
			document.getElementById("datepicker_datum").style.border = "";
		}

		var datum = document.getElementById("datepicker_datum").value;
		var Tag = datum.substring(0,2);
		var Monat = datum.substring(3,5);
		Monat = Monat-1;
		var Jahr = datum.substring(6,10);
		//Neues Datumsobjekt erzeugen
		var Enddatum = new Date(Jahr, Monat, Tag,23,59,59);
		var Heute = new Date();
		if (Enddatum < Heute)
		{
			alert("Das Umfrageende darf nicht in der Vergangenheit liegen");
			document.getElementById("datepicker_datum").style.border = "solid red 2px";
			error = true;
		}
		else
		{
			document.getElementById("datepicker_datum").style.border = "";
		}

		if (error)
			return false;
		else
			return true;
	}
	</script>
	<style>
	#wrapper
	{
		width: 80%;
		padding: 0 10px 15px 10px;
		border: 1px solid #ccc;
		background: #eee;
		text-align: left;
	}
	#wrapper h4
	{
		font-size: 17px;
		margin-top: 0;
		padding-top: 10px;
		padding-bottom: 10px;
		text-decoration: none;
	}
	#weiter
	{
		width: 80%;
		text-align: center;
		margin-top: 10px;
		padding: 10px;

		padding: 10px 10px 10px 10px;
		border: 1px solid #D6E9C6;
		background: #DFF0D8;
	}
	#weiter:hover
	{
		width: 80%;
		text-align: center;
		margin-top: 10px;
		padding: 10px;

		padding: 10px 10px 10px 10px;
		border: 1px solid #ccc;
		background: #ddd;
	}
	#laufend
	{
		width: 80%;
		text-align: center;
		margin-top: 10px;
		padding: 10px;

		padding: 10px 10px 10px 10px;
		border: 1px solid #ccc;
		background: #EDCECE;
	}
	a:hover
	{
		text-decoration: none;
	}
	.ui-timepicker-table td a
	{
		padding:0.2em 0.3em 0.2em 0.3em;
		width: 1.8em;
	}
	</style>
	<title>'.$p->t('coodle/coodle').'</title>
</head>
<body>';
echo '<h1>'.$p->t('coodle/coodle').'</h1>';

if(isset($_POST['save']))
{
	//Speichern
	$titel = $_POST['titel'];
	$beschreibung = ($_POST['beschreibung']==''?null:$_POST['beschreibung']);
	$dauer = $_POST['dauer'];
	$endedatum = $_POST['endedatum'];
	$coodle_id = $_POST['coodle_id'];
	if (isset($_POST['mailversand']))
			$mailversand = true;
		else
			$mailversand = false;

	if (isset($_POST['teilnehmer_anonym']))
			$teilnehmer_anonym = true;
		else
			$teilnehmer_anonym = false;

	if (isset($_POST['termin_anonym']))
			$termin_anonym = true;
		else
			$termin_anonym = false;

	$coodle = new coodle();

	if($coodle_id!='')
	{
		if(!$coodle->load($coodle_id))
			die($coodle->errormsg);

		if($coodle->ersteller_uid!=$user)
		{
			die($p->t('basis/keineBerechtigung'));
		}
		$coodle->new = false;
	}
	else
	{
		$coodle->new = true;
		$coodle->ersteller_uid = $user;
		$coodle->insertamum = date('Y-m-d H:i:s');
		$coodle->insertvon = $user;
		$coodle->coodle_status_kurzbz = 'neu';
	}

	$coodle->titel = $titel;
	$coodle->beschreibung = $beschreibung;
	$coodle->dauer = $dauer;
	$coodle->endedatum = $datum_obj->formatDatum($endedatum, 'Y-m-d');
	$coodle->updateamum = date('Y-m-d H:i:s');
	$coodle->updatevon = $user;
	$coodle->mailversand = $mailversand;
	$coodle->teilnehmer_anonym = $teilnehmer_anonym;
	$coodle->termine_anonym = $termin_anonym;

	if($coodle->save())
	{
		$message.= '<span class="ok">'.$p->t('global/erfolgreichgespeichert').'</span>';
		//Fuer alle neuen Umfragen wird ein Termine am 01.01.1900 00:00:01 als Option fuer "Keine Auswahl" angelegt
		if ($coodle->new == true)
		{
			$coodletermin = new coodle();

			$coodletermin->datum = '1900-01-01';
			$coodletermin->uhrzeit = '00:00:01';
			$coodletermin->coodle_id = $coodle->coodle_id;

			if (!$coodletermin->saveTermin(true))
				$message.= '<span class="error">'.$coodletermin->errormsg.'</span>';
		}
		// Einer neuen Umfrage wird der Ersteller automatisch als TeilnehmerIn hinzugefügt
		if ($coodle->new == true)
		{
			$coodleRessource = new coodle();
			
			if(!$coodleRessource->RessourceExists($coodle->coodle_id, $user))
			{
				$coodleRessource->coodle_id = $coodle->coodle_id;
				$coodleRessource->uid = $user;
				$coodleRessource->email = $user.'@'.DOMAIN;
				$coodleRessource->insertamum = date('Y-m-d H:i:s');
				$coodleRessource->insertvon = $user;
				$coodleRessource->updateamum = date('Y-m-d H:i:s');
				$coodleRessource->updatevon = $user;
				
				if(!$coodleRessource->saveRessource(true))
					$message.= '<span class="error">'.$coodleRessource->errormsg.'</span>';
			}
		}
	}
	else
	{
		$message.= '<span class="error">'.$coodle->errormsg.'</span>';
	}
}
elseif(isset($_GET['coodle_id']))
{
	// Bearbeiten
	$coodle = new coodle();
	if($coodle->load($_GET['coodle_id']))
	{
		if($coodle->ersteller_uid!=$user)
			die($p->t('global/keineBerechtigungFuerDieseSeite'));

		if(($coodle->coodle_status_kurzbz!='neu') && ($coodle->coodle_status_kurzbz!='laufend'))
		{
			// Wenn bereits abgeschlosse oder storniert,
			// kann nicht mehr bearbeitet werden
			die($p->t('coodle/umfrageNichtGueltig'));
		}
	}
	else
	{
		die('Error:'.$coodle->errormsg);
	}
}
else
{
	// Neu
	$coodle = new coodle();
	$coodle->endedatum=date('d.m.Y',strtotime("+7 day"));
	$coodle->dauer=60;
	$coodle->mailversand=true;
}
echo '
<a href="uebersicht.php">&lt;&lt;&nbsp;'.$p->t('coodle/zurueckZurUebersicht').'</a><br>
<br>
<form method="POST">';
echo '<div id="wrapper">
<h4>';
if($coodle->coodle_id=='')
	echo $p->t('coodle/neuerEintrag');
elseif($coodle->coodle_status_kurzbz=='laufend')
	echo $p->t('coodle/laufendeUmfrageBearbeiten');
else
	echo $p->t('coodle/bearbeiten');
echo '</h4>';

echo '
<input type="hidden" name="coodle_id" value="'.$db->convert_html_chars($coodle->coodle_id).'" />
<table>
	<tr>
		<td valign="top">'.$p->t('coodle/titel').'</td>
		<td valign="top">
			<input type="hidden" name="titel" value="'.$db->convert_html_chars($coodle->titel).'" />
			<input id="titel" placeholder="'.$p->t('coodle/titelEingeben').'" title="'.$p->t('coodle/titelInfotext').'" type="text" name="titel" value="'.$db->convert_html_chars($coodle->titel).'" maxlength="64" size="50" '.($coodle->coodle_status_kurzbz=='laufend'?'disabled':'').'/></td>
		<td valign="top" style="color:grey">'.$p->t('coodle/titelInfotext').'</td>
	</tr>
	<tr>
		<td valign="top">'.$p->t('coodle/beschreibung').'</td>
		<td><textarea name="beschreibung" rows="6" cols="50" >'.$db->convert_html_chars($coodle->beschreibung).'</textarea></td>
		<td valign="top" style="color:grey"><br><br>'.$p->t('coodle/beschreibungInfotext').'</td>
	</tr>
	<tr>
		<td valign="top">'.$p->t('coodle/dauer').'</td>
		<td valign="top">
			<input type="hidden" name="dauer" value="'.$db->convert_html_chars($coodle->dauer).'" />
			<input id="dauer" class="timepicker" type="text" name="dauer" value="'.$db->convert_html_chars($coodle->dauer).'" maxlength="5" size="2" '.($coodle->coodle_status_kurzbz=='laufend'?'disabled':'').'/>
			'.$p->t('coodle/dauerminuten').'
		</td>
		<td valign="top" style="color:grey">'.$p->t('coodle/dauerInfotext').'</td>
	</tr>
	<tr>
		<td valign="top">'.$p->t('coodle/endedatum').'</td>
		<td valign="top"><input id="datepicker_datum" type="text" name="endedatum" value="'.$db->convert_html_chars($datum_obj->formatDatum($coodle->endedatum,'d.m.Y')).'" maxlength="10" size="10"/></td>
		<td valign="top" style="color:grey">'.$p->t('coodle/endeInfotext').'</td>
	</tr>
	<tr>
		<td valign="top">'.$p->t('coodle/mailversand').'</td>
		<td valign="top"><input id="mailversand" type="checkbox" name="mailversand" '.($coodle->mailversand=='t'?'checked':'').'/></td>
		<td valign="top" style="color:grey">'.$p->t('coodle/infotextMailversand').'</td>
	</tr>
	<tr>
		<td valign="top">'.$p->t('coodle/teilnehmerAnonym').'</td>
		<td valign="top"><input id="teilnehmer_anonym" type="checkbox" name="teilnehmer_anonym" '.($coodle->teilnehmer_anonym=='t'?'checked':'').'/></td>
		<td valign="top" style="color:grey">'.$p->t('coodle/infotextTeilnehmerAnonym').'</td>
	</tr>
	<tr>
		<td valign="top">'.$p->t('coodle/terminAnonym').'</td>
		<td valign="top"><input id="termin_anonym" type="checkbox" name="termin_anonym" '.($coodle->termine_anonym=='t'?'checked':'').'/></td>
		<td valign="top" style="color:grey">'.$p->t('coodle/infotextTerminAnonym').'</td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" name="save" value="'.$p->t('global/speichern').'" onClick="return checkrequired();"/> '.$message.'</td>
	</tr>
</table>';

//echo '</fieldset>';
echo '</div>';
echo '
</form>';

if($coodle->coodle_id)
{
	echo '<a href="termin.php?coodle_id='.$db->convert_html_chars($coodle->coodle_id).'"><div id="weiter">'.$p->t('coodle/weiterZurTerminauswahl').'</div></a>';
}
/*elseif ($coodle->coodle_status_kurzbz=='laufend')
	echo '<div id="laufend">'.$p->t('coodle/umfrageLaeuftBereits').'</div>';*/

echo '</body>
</html>';
?>
