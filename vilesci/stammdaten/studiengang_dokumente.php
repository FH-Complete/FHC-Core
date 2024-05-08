<?php
/*
 * Copyright 2014 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 *
 * Authors: Martin Tatzber <tatzberm@technikum-wien.at>
 * 			Manfred Kindl <manfred.kindl@technikum-wien.at>
 *
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/dokument.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/sprache.class.php');

$stg_kz = isset($_REQUEST['stg_kz']) ? $_REQUEST['stg_kz'] : '';
$dokument_kurzbz = isset($_REQUEST['dokument_kurzbz']) ? $_REQUEST['dokument_kurzbz'] : '';
$onlinebewerbung = isset($_REQUEST['onlinebewerbung']);
$pflicht = isset($_POST['pflicht']);
$nachreichbar = isset($_POST['nachreichbar']);
$stufe = isset($_REQUEST['stufe']) ? $_REQUEST['stufe'] : '';

$sprache = new sprache();
$sprache->getAll(true, 'index');

$action = isset($_GET['action'])?$_GET['action']:'';
if(isset($_POST['add']))
	$action='add';
if(isset($_POST['saveDoc']))
	$action='saveDoc';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
$db = new basis_db();
if(!$rechte->isBerechtigt('assistenz', null, 's'))
	die($rechte->errormsg);

if($action == 'add')
{
	if(!$rechte->isBerechtigt('assistenz', $stg_kz, 'sui'))
		die($rechte->errormsg);

	if($dokument_kurzbz != '' && $stg_kz != '')
	{
		$dokument = new dokument();
		$dokument->dokument_kurzbz = $dokument_kurzbz;
		$dokument->studiengang_kz = $stg_kz;
		$dokument->onlinebewerbung = $onlinebewerbung;
        $dokument->pflicht = $pflicht;
        $dokument->nachreichbar = $nachreichbar;
		$dokument->stufe = $stufe;

		$beschreibung_mehrsprachig = array();
		foreach($sprache->result as $row_sprache)
		{
			if(isset($_POST['beschreibung_mehrsprachig_'.$row_sprache->sprache]))
				$beschreibung_mehrsprachig[$row_sprache->sprache] = $_POST['beschreibung_mehrsprachig_'.$row_sprache->sprache];
		}
		$dokument->beschreibung_mehrsprachig = $beschreibung_mehrsprachig;

		$dokument->saveDokumentStudiengang();
	}
}

if($action == 'delete')
{
	if(!$rechte->isBerechtigt('assistenz', $stg_kz, 'suid'))
		die($rechte->errormsg);
	
	if($dokument_kurzbz != '' && $stg_kz != '')
	{
		$dokument = new dokument();
		if(!$dokument->deleteDokumentStg($dokument_kurzbz, $stg_kz))
			echo 'Fehler beim Löschen: '.$dokument->errormsg;
	}
}

if($action =='toggleonline')
{
	if(!$rechte->isBerechtigt('assistenz', $stg_kz, 'su'))
		die($rechte->errormsg);

	if($dokument_kurzbz != '' && $stg_kz != '')
	{
		$dokument = new dokument();
		if($dokument->loadDokumentStudiengang($dokument_kurzbz, $stg_kz))
		{
			$dokument->onlinebewerbung = !$dokument->onlinebewerbung;
			if(!$dokument->saveDokumentStudiengang())
				echo $dokument->errormsg;
		}
		else
			echo 'Zuordnung ist nicht vorhanden';
	}
}

if($action === 'togglepflicht') 
{
	if(!$rechte->isBerechtigt('assistenz', $stg_kz, 'su'))
		die($rechte->errormsg);

	if($dokument_kurzbz != '' && $stg_kz != '')
	{
		$dokument = new dokument();
		if($dokument->loadDokumentStudiengang($dokument_kurzbz, $stg_kz))
		{
			$dokument->pflicht = !$dokument->pflicht;
			if(!$dokument->saveDokumentStudiengang())
				echo $dokument->errormsg;
		}
		else
			echo 'Zuordnung ist nicht vorhanden';
	}
}

// Ändern der Stufe per Ajax
$changeStufe = filter_input(INPUT_POST, 'changeStufe', FILTER_VALIDATE_BOOLEAN);
if ($changeStufe && isset($_POST['stufe']) && isset($_POST['studiengang_kz']))
{
	// Check if stufe = 0
	if (filter_input(INPUT_POST, 'stufe', FILTER_VALIDATE_INT) === 0
		|| filter_input(INPUT_POST, 'stufe', FILTER_VALIDATE_INT)
		|| filter_input(INPUT_POST, 'stufe') == '')
	{
		$stufe = filter_input(INPUT_POST, 'stufe');
	}
	else
	{
		echo json_encode(array(
			'status' => 'fehler',
			'msg' => '"'.$_POST['stufe'].'" ist kein gueltiger Wert fuer die Stufe'
		));
		exit();
	}

	$studiengang_kz = filter_input(INPUT_POST, 'studiengang_kz', FILTER_VALIDATE_INT);
	$dokument_kurzbz = filter_input(INPUT_POST, 'dokument_kurzbz');

	$dokument = new dokument();
	$dokument->loadDokumentStudiengang($dokument_kurzbz, $studiengang_kz);
	$dokument->stufe = $stufe;

	if (!$dokument->saveDokumentStudiengang())
	{
		echo json_encode(array(
			'status' => 'fehler',
			'msg' => $p->t('global/fehlerBeiDerParameteruebergabe')
		));
		exit();
	}
	else
	{
		echo json_encode(array(
			'status' => 'ok',
			'msg' => 'Status erfolgreich aktualisiert'
		));
		exit();
	}
}

if($action === 'togglenachreichbar') 
{
	if(!$rechte->isBerechtigt('assistenz', $stg_kz, 'su'))
		die($rechte->errormsg);
	
	if($dokument_kurzbz != '' && $stg_kz != '')
	{
		$dokument = new dokument();
		if($dokument->loadDokumentStudiengang($dokument_kurzbz, $stg_kz))
		{
			$dokument->nachreichbar = !$dokument->nachreichbar;
			if(!$dokument->saveDokumentStudiengang())
				echo $dokument->errormsg;
		}
		else
			echo 'Zuordnung ist nicht vorhanden';
	}
}

if($action == 'saveDoc')
{
	if(!$rechte->isBerechtigt('assistenz', $stg_kz, 'sui'))
		die($rechte->errormsg);
	
	$dokBezeichnung = isset($_POST['dokument_bezeichnung'])?$_POST['dokument_bezeichnung']:'';
	$dokKurzbz = isset($_POST['dokument_kurzbz'])?$_POST['dokument_kurzbz']:'';

	if($dokBezeichnung != '')
	{
		$dokument = new dokument();
		$dokument->dokument_kurzbz = $dokKurzbz;
		$dokument->bezeichnung = $dokBezeichnung;

		if($dokument->saveDokument(true))
		{
			echo 'Dokument hinzugefügt';
		}
		else
		{
			echo $dokument->errormsg;
		}
	}
}

echo '<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
	<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
	<script type="text/javascript" src="../../include/tiny_mce/tiny_mce.js"></script>';

	include('../../include/meta/jquery.php');
	include('../../include/meta/jquery-tablesorter.php');

	echo '
	<script type="text/javascript">
		$(document).ready(function()
		{
			$.tablesorter.addParser({
			// set a unique id
			id: "stufe",
			is: function(s) {
				// return false so this parser is not auto detected
				return false;
			},
			format: function(s, table, cell) 
			{
				return $("input", cell).val();
			},
			// set type, either numeric or text
			type: "numeric"
			});
			
			$("#t1").tablesorter(
			{
				sortList: [[0,0]],
				widgets: ["saveSort","zebra","filter"],
				headers: {7:{sorter: "stufe"}},
				widgetOptions : {	filter_useParsedData : true,
									filter_functions : {
									// Add select menu to this column
									4 : {
									"Ja" : function(e, n, f, i, $r, c, data) { return /t/.test(e); },
									"Nein" : function(e, n, f, i, $r, c, data) { return /f/.test(e); }
									},
									5 : {
									"Ja" : function(e, n, f, i, $r, c, data) { return /t/.test(e); },
									"Nein" : function(e, n, f, i, $r, c, data) { return /f/.test(e); }
									},
									6 : {
									"Ja" : function(e, n, f, i, $r, c, data) { return /t/.test(e); },
									"Nein" : function(e, n, f, i, $r, c, data) { return /f/.test(e); }
									}
				}}
			});
			$("#t2").tablesorter(
			{
				sortList: [[2,0]],
				widgets: ["zebra"],
				headers: {0:{sorter:false}}
			});
		});

		function showDocumentForm(dokument_kurzbz="",bezeichnung="",neu=true)
		{
			document.getElementById("dokument_kurzbz").value=dokument_kurzbz;
			document.getElementById("dokument_bezeichnung").value=bezeichnung;
			document.getElementById("documentForm").style.visibility="visible";
			if(!neu)
			{
				document.getElementById("dokument_kurzbz").readOnly=true;
			}
		}

		function confdel()
		{
			return confirm("Wollen Sie diesen Eintrag wirklich löschen?");
		}

		tinyMCE.init({
			mode: "specific_textareas",
			editor_selector: "mceEditor",
			theme: "advanced",
			language: "de",
			file_browser_callback: "FHCFileBrowser",
			plugins: "spellchecker,pagebreak,style,layer,table,advhr,advimage,advlink,inlinepopups,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking",
			// Theme options
			theme_advanced_buttons1: "code, bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,cut,copy,paste,pastetext,pasteword,|,bullist,numlist,|,outdent,indent", //,|,formatselect,fontsizeselect",
			theme_advanced_buttons2: "undo,redo,|,link,unlink,|,forecolor,backcolor,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,fullscreen",
			theme_advanced_buttons3: "",
			theme_advanced_toolbar_location: "top",
			theme_advanced_toolbar_align: "center",
			theme_advanced_statusbar_location: "bottom",
			theme_advanced_resizing: true,
			force_br_newlines: true,
			force_p_newlines: false,
			forced_root_block: "",
			editor_deselector: "mceNoEditor"
		});
		
		function changeStufe(dokument_kurzbz)
		{
			var stufe = $("#stufe_"+dokument_kurzbz).val();
			var studiengang_kz = $("#studiengangSelect").val();
			
			data = {
				stufe: stufe,
				studiengang_kz: studiengang_kz,
				dokument_kurzbz: dokument_kurzbz,
				changeStufe: true
			};
		
			$.ajax({
				url: "studiengang_dokumente.php",
				data: data,
				type: "POST",
				dataType: "json",
				success: function(data)
				{
					if(data.status!="ok")
					{
						$("#feedbackSpanFalse_"+dokument_kurzbz).toggle();
						$("#feedbackSpanFalse_"+dokument_kurzbz).attr("title", data["msg"]);
						
					}
					else
					{
						$("#feedbackSpanFalse_"+dokument_kurzbz).hide();
						$("#feedbackSpanTrue_"+dokument_kurzbz).toggle();
						$("#feedbackSpanTrue_"+dokument_kurzbz).delay(1000).fadeOut();
					}
				},
				error: function(data)
				{
					alert(data["msg"]);
				}
			});
		}
	</script>
	<title>Zuordnung Studiengang - Dokumente</title>
</head>
<body>';

// Verwaltung der Dokumenttypen
if(isset($_GET['action']) && $_GET['action'] == 'dokumenttypen')
{
	if(!$rechte->isBerechtigt('admin', null, 'suid'))
		die($rechte->errormsg);
	
	echo '<h1>Dokumenttypen</h1>';
	echo '<a href="studiengang_dokumente.php">Zur&uuml;ck zu Dokumenten Zuteilung</a><br><br>';

	if(isset($_GET['type']))
	{
		if($_GET['type'] == 'delete')
		{
			$dokument = new dokument();
			if(!$dokument->deleteDokumenttyp($_GET['dokument_kurzbz']))
				echo $dokument->errormsg;

		}
	}
	if(isset($_POST['saveDokumenttyp']))
	{
		$dokument = new dokument();
		$dokument->dokument_kurzbz = $_POST['dokument_kurzbz'];
		$dokument->bezeichnung = $_POST['dokument_bezeichnung'];
		$dokument->ausstellungsdetails = isset($_POST['ausstellungsdetails'])?true:false;
		if(isset($_POST['neu']) && $_POST['neu'] == 'true')
			$neu = true;
		else
			$neu = false;

		$bezeichnung_mehrsprachig = array();
		foreach($sprache->result as $row_sprache)
		{
			if(isset($_POST['bezeichnung_mehrsprachig_'.$row_sprache->sprache]))
				$bezeichnung_mehrsprachig[$row_sprache->sprache] = $_POST['bezeichnung_mehrsprachig_'.$row_sprache->sprache];
		}
		$dokument->bezeichnung_mehrsprachig = $bezeichnung_mehrsprachig;

		$dokumentbeschreibung_mehrsprachig = array();
		foreach($sprache->result as $row_sprache)
		{
			if(isset($_POST['dokumentbeschreibung_mehrsprachig_'.$row_sprache->sprache]))
				$dokumentbeschreibung_mehrsprachig[$row_sprache->sprache] = $_POST['dokumentbeschreibung_mehrsprachig_'.$row_sprache->sprache];
		}
		$dokument->dokumentbeschreibung_mehrsprachig = $dokumentbeschreibung_mehrsprachig;

		if(!$dokument->saveDokument($neu))
			echo $dokument->errormsg;
	}

	$dokument = new dokument();	
	if(isset($_GET['type']) && $_GET['type'] == 'edit')
	{
		$dokument->loadDokumenttyp($_GET['dokument_kurzbz']);
	}
	
	echo'<form action="'.$_SERVER['PHP_SELF'].'?action=dokumenttypen" method="post">
	<table>
		<tr>
			<td>Kurzbezeichnung</td>
			<td>
				<input typ="text" id="dokument_kurzbz" name="dokument_kurzbz" maxlength="8" size="8" '.($dokument->dokument_kurzbz != ''?'disabled':'').' value="'.$dokument->dokument_kurzbz.'"/>';
				if ($dokument->dokument_kurzbz != '')
					echo '<input type="hidden" id="dokument_kurzbz_hidden" name="dokument_kurzbz" value="'.$dokument->dokument_kurzbz.'" />';
				echo '<input type="hidden" id="neu" name="neu" value="'.($dokument->dokument_kurzbz == ''?'true':'false').'" />
			</td>
		</tr>
		<tr>
			<td>Bezeichnung Intern</td>
			<td>
				<input type="text" id="dokument_bezeichnung" name="dokument_bezeichnung" size="50" maxlength="128" value="'.$dokument->bezeichnung.'">
			</td>
		</tr>
		<tr>
			<td>Ausstellungsdetails</td>
			<td>
				<input type="checkbox" id="ausstellungsdetails" name="ausstellungsdetails" '.($dokument->ausstellungsdetails == true?'checked':'').'>
				&nbsp;Sollen beim Dokument weitere Felder (zB Ausstellungsnation) angezeigt werden?
			</td>
		</tr>';
	foreach($sprache->result as $s)
	{
		echo '<tr><td colspan="2" style="border-top: 1px solid grey"></td></tr>';
		echo '<tr><td>Bezeichnung '.$s->bezeichnung_arr[$s->sprache].'</td><td>';
		echo '<input type="text" maxlength="128" size="50" name="bezeichnung_mehrsprachig_'.$s->sprache.'"  value="'.(isset($dokument->bezeichnung_mehrsprachig[$s->sprache])?$db->convert_html_chars($dokument->bezeichnung_mehrsprachig[$s->sprache]):'').'" />';
		echo '<tr><td style="vertical-align: top">Beschreibung '.$s->bezeichnung_arr[$s->sprache].'</td><td>';
		echo '<textarea id="beschreibung'.$s->sprache.'" class="mceEditor" cols="100" name="dokumentbeschreibung_mehrsprachig_'.$s->sprache.'" >'.(isset($dokument->dokumentbeschreibung_mehrsprachig[$s->sprache])?$db->convert_html_chars($dokument->dokumentbeschreibung_mehrsprachig[$s->sprache]):'').'</textarea></td></tr>';
	}
	echo '
		</tr>
		<tr>
			<td></td>
			<td><br><input type="submit" name="saveDokumenttyp" value="Speichern"></td>
		</tr>
	</table>
	</form><br>';

	$dokument = new dokument();
	$dokument->getAllDokumente();
	
	echo '

	<table id="t2" class="tablesorter" style="width:auto">
	<thead>
		<th></th>
		<th>Kurzbz</th>
		<th>Bezeichnung Intern</th>
		<th>Ausstellungsdetails</th>';
	foreach ($sprache->result as $sprache_row)
	{
		echo '<th>Bezeichnung '.$sprache_row->bezeichnung_arr[$sprache_row->sprache].'</th>';
		echo '<th>Beschreibung '.$sprache_row->bezeichnung_arr[$sprache_row->sprache].'</th>';
	}
	echo'</thead>
	<tbody>
		';
	foreach($dokument->result as $row)
	{
		echo '<tr>
				<td>
					<a href="'.$_SERVER['PHP_SELF'].'?action=dokumenttypen&type=edit&dokument_kurzbz='.$row->dokument_kurzbz.'"><img src="../../skin/images/edit.png" title="Bearbeiten" /></a>
					';
		// Lichtbil und Zeugnis duerfen nicht geloescht werden da diese fuer Bildupload und
		// Zeugnisarchivierung verwendet werden
		if(!in_array($row->dokument_kurzbz,array('Lichtbil','Zeugnis')))
			echo '<a href="'.$_SERVER['PHP_SELF'].'?action=dokumenttypen&type=delete&dokument_kurzbz='.$row->dokument_kurzbz.'"><img src="../../skin/images/cross.png" title="Löschen" /></a>';

		echo '
				</td>
				<td>'.$row->dokument_kurzbz.'</td>
				<td>'.cutString($row->bezeichnung, 50, ' [...]').'</td>
				<td style="text-align: center">'.($row->ausstellungsdetails == true?'<img width="15px" src="../../skin/images/check_black.png" alt="true"/>':'').'</td>';
		foreach ($sprache->result as $sprache_row)
		{
			echo '<td>'.cutString($row->bezeichnung_mehrsprachig[$sprache_row->sprache], 50, ' [...]').'</td>';
			echo '<td>'.cutString($db->convert_html_chars($row->dokumentbeschreibung_mehrsprachig[$sprache_row->sprache]), 20, ' [...]').'</td>';
		}
		echo'</tr>';
	}
	echo '</tbody></table>';
	
	echo'<br><br><br><br><br><br><br>';
}
else
{
	$studiengang = new studiengang();
	$studiengang->getAll('typ, kurzbz');
	$types = new studiengang();
	$types->getAllTypes();
	$typ = '';
	$kuerzel = '';

	echo '<h1>Zuteilung Studiengang - Dokumente</h1>
	<table width="100%">
	<tr>
	<td>
	<form action='.$_SERVER['PHP_SELF'].' method="post" name="dokumente_zuteilung">
		<select id="studiengangSelect" name="stg_kz" onchange="document.dokumente_zuteilung.submit()">';
	echo '<option value="">-- Studiengang auswählen --</option>';
	foreach ($studiengang->result as $stg)
	{
		if(!$rechte->isBerechtigt('assistenz', $stg->studiengang_kz, 's'))
			continue;
		
		if ($typ != $stg->typ || $typ == '')
		{
			if ($typ != '')
				echo '</optgroup>';
				echo '<optgroup label="'.($types->studiengang_typ_arr[$stg->typ] != ''?$types->studiengang_typ_arr[$stg->typ]:$stg->typ).'">';
		}
		if($stg_kz == $stg->studiengang_kz)
		{
			$selected = ' selected';
			$kuerzel = $stg->kuerzel;
		}
		else
			$selected='';
		
		echo '<option value="'.$stg->studiengang_kz.'"'.$selected.'>'.$stg->kuerzel.' - '.$stg->bezeichnung.'</option>';
		$typ = $stg->typ;
	}
	echo '</select>
	<input type="submit" value="Anzeigen">
	</td>';
	if($rechte->isBerechtigt('admin', null, 'suid'))
	{
		echo '<td align="right">
				<a href="'.$_SERVER['PHP_SELF'].'?action=dokumenttypen">Dokumenttypen verwalten</a>
				</td>';
	}
	echo'</tr></table>

	<br/>';

	if($stg_kz != '')
	{
		echo '<table id="t1" class="tablesorter">
		<thead>
		<tr>';
		$spaltenzaehler = 0;
		foreach ($sprache->result as $sprache_row)
		{
			$spaltenzaehler = $spaltenzaehler+2;
			echo '<th>Bezeichnung '.$sprache_row->bezeichnung_arr[$sprache_row->sprache].'</th>';
			echo '<th>Beschreibung '.$sprache_row->bezeichnung_arr[$sprache_row->sprache].'</th>';
		}
		if($rechte->isBerechtigt('assistenz', $stg_kz, 'su'))
		{
			echo'	<th style="text-align: center">Online-Bewerbung</th>
					<th style="text-align: center">Pflicht</th>
					<th style="text-align: center">Nachreichbar</th>
					<th style="text-align: center">Stufe*</th>
					<th class="sorter-false"></th>';
		}
		echo'</tr>
		</thead>
		<tbody>';

		$dokStg = new dokument();
		$dokStg->getDokumente($stg_kz);
		$dok_stg = new dokument();
		
		$zugewieseneDokumente = array();
		$beschreibung = '';
		
		foreach($dokStg->result as $dok)
		{
			$dok_stg->loadDokumentStudiengang($dok->dokument_kurzbz, $stg_kz);
			
			$zugewieseneDokumente[] = $dok->dokument_kurzbz;
			$checked_onlinebewerbung = $dok->onlinebewerbung ? 'true' : 'false';
			$checked_pflicht = $dok->pflicht ? 'true' : 'false';
			$checked_nachreichbar = $dok->nachreichbar ? 'true' : 'false';
			echo '<tr>';
			foreach ($sprache->result as $sprache_row)
			{
				$beschreibung = '';
				echo '<td>'.$dok->bezeichnung_mehrsprachig[$sprache_row->sprache].'</td>';
				/*if ($dok->dokumentbeschreibung_mehrsprachig[$sprache_row->sprache] != '')
					$beschreibung = '<b>Allgemein</b>: '.cutString($dok->dokumentbeschreibung_mehrsprachig[$sprache_row->sprache], 50, ' [...]').'<br/>';
				if ($dok_stg->beschreibung_mehrsprachig[$sprache_row->sprache] != '')
					$beschreibung .= '<span style="color: green"><b>'.$kuerzel.'</b></span>: '.cutString($dok_stg->beschreibung_mehrsprachig[$sprache_row->sprache], 50, ' [...]');*/

				if ($dok->dokumentbeschreibung_mehrsprachig[$sprache_row->sprache] != '' || $dok_stg->beschreibung_mehrsprachig[$sprache_row->sprache] != '')
					$beschreibung = 'Vorhanden';

				echo '<td>'.$beschreibung.'</td>';
			}
			if($rechte->isBerechtigt('assistenz', $stg_kz, 'su'))
			{
				echo'	<td style="text-align: center">
							<div style="display: none">'.$checked_onlinebewerbung.'</div>
							<a href="'.$_SERVER['PHP_SELF'].'?action=toggleonline&dokument_kurzbz='.$dok->dokument_kurzbz.'&stg_kz='.$stg_kz.'">
								<img src="../../skin/images/'.$checked_onlinebewerbung.'.png" />
							</a>
						</td>
						<td style="text-align: center">
							<div style="display: none">'.$checked_pflicht.'</div>
							<a href="'.$_SERVER['PHP_SELF'].'?action=togglepflicht&dokument_kurzbz='.$dok->dokument_kurzbz.'&stg_kz='.$stg_kz.'">
								<img src="../../skin/images/'.$checked_pflicht.'.png" />
							</a>
						</td>
						<td style="text-align: center">
							<div style="display: none">'.$checked_nachreichbar.'</div>
							<a href="'.$_SERVER['PHP_SELF'].'?action=togglenachreichbar&dokument_kurzbz='.$dok->dokument_kurzbz.'&stg_kz='.$stg_kz.'">
								<img src="../../skin/images/'.$checked_nachreichbar.'.png" />
							</a>
						</td>
						<td style="text-align: left; width: 60px">
							<input style="width: 30px" type="text" id="stufe_'.$dok->dokument_kurzbz.'" value="'.$dok->stufe.'" tabindex="1" onchange="changeStufe(\''.$dok->dokument_kurzbz.'\')">
							<span id="feedbackSpanTrue_'.$dok->dokument_kurzbz.'" style="display: none"><img style="width: 16px" src="../../skin/images/true.png" /></span>
							<span id="feedbackSpanFalse_'.$dok->dokument_kurzbz.'" style="display: none" title=""><img style="width: 16px" src="../../skin/images/false.png" /></span>
						</td>
						<td style="text-align: center">';
						if($rechte->isBerechtigt('assistenz', $stg_kz, 'su'))
							echo '<a href="'.$_SERVER['PHP_SELF'].'?action=edit&dokument_kurzbz='.$dok->dokument_kurzbz.'&stg_kz='.$stg_kz.'"><img src="../../skin/images/edit.png" title="Zuordnung bearbeiten" size="17px" /></a>';
						if($rechte->isBerechtigt('assistenz', $stg_kz, 'suid'))
							echo '<a href="'.$_SERVER['PHP_SELF'].'?action=delete&dokument_kurzbz='.$dok->dokument_kurzbz.'&stg_kz='.$stg_kz.'" onclick="return confdel()"><img src="../../skin/images/delete.png" title="Zuordnung löschen" height="17px"/></a>
						</td>';
			}
			echo'</tr>';
		}

		$dok_stg = new dokument();
		$dokument = new dokument();
		if($action == 'edit')
		{
			if(!$dok_stg->loadDokumentStudiengang($dokument_kurzbz, $stg_kz))
				die('Failed to load:'.$dok_stg->errormsg);
			
			if(!$dokument->loadDokumenttyp($dokument_kurzbz))
				die('Failed to load:'.$dokument->errormsg);
		}

		echo '</tbody>';
		
		if($rechte->isBerechtigt('assistenz', $stg_kz, 'su'))
		{
			echo '<tfoot>
			<tr>
				<td colspan="'.$spaltenzaehler.'" class="normal"><select name="dokument_kurzbz">';
			$dokAll = new dokument();
			$dokAll->getAllDokumente();
			foreach($dokAll->result as $dok_row)
			{
				if($dok_stg->dokument_kurzbz == $dok_row->dokument_kurzbz)
					echo '<option value="'.$dok_row->dokument_kurzbz.'" selected="selected">'.$dok_row->bezeichnung.'</option>';
				elseif(!in_array($dok_row->dokument_kurzbz,$zugewieseneDokumente))
					echo '<option value="'.$dok_row->dokument_kurzbz.'">'.$dok_row->bezeichnung.'</option>';
			}
			echo '</select>';
			echo '<table>';
			foreach($sprache->result as $s)
			{
				echo '<tr><td class="normal">Studiengangsspezifische Beschreibung '.$s->bezeichnung_arr[$s->sprache].'</td><td>';
					echo '<textarea cols="80" class="mceEditor" name="beschreibung_mehrsprachig_'.$s->sprache.'" >'.(isset($dok_stg->beschreibung_mehrsprachig[$s->sprache])?$db->convert_html_chars($dok_stg->beschreibung_mehrsprachig[$s->sprache]):'').'</textarea></td>';
					echo '<td class="normal" style="vertical-align: top"><h2>Allgemeine Beschreibung '.$s->bezeichnung_arr[$s->sprache].'</h2>'.$dokument->dokumentbeschreibung_mehrsprachig[$s->sprache].'</td>';
			}
			echo '</tr></table>';
			echo '</td>
					<td class="normal" style="text-align: center" valign="top">
						<input type="checkbox" name="onlinebewerbung" '.($dok_stg->onlinebewerbung?'checked="checked"':'').'></td>
					<td class="normal" style="text-align: center" valign="top">
						<input type="checkbox" name="pflicht" '.($dok_stg->pflicht?'checked="checked"':'').'>
					</td>
					<td  class="normal" style="text-align: center" valign="top">
						<input type="checkbox" name="nachreichbar" '.($dok_stg->nachreichbar?'checked="checked"':'').'>
					</td>
					<td class="normal" style="text-align: center" valign="top">
						<input type="text" style="width: 30px" name="stufe" value="'.$dok_stg->stufe.'">
					</td>
					<td  class="normal" valign="top"><input type="submit" name="add" value="Speichern"></td>
				</tr>
			</tfoot>';
		}
		echo' </table></form>';
	}
	else
		echo '</form>';
}
echo '<div style="font-size: small">*) Statusstufen:<br>
<ul>
<li>0 oder leer -> immer sichtbar</li>
<li>10 -> Interessent</li>
<li>15 -> Interessent Status bestätigt</li>
<li>20 -> Bewerber</li>
<li>30 -> Wartender</li>
<li>40 -> Aufgenommener</li>
<li>50 -> Student</li>
</ul></div>';
echo '
</body>
</html>';

?>
