<?php
/* Copyright (C) 2010 Technikum-Wien
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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/**
 * Verwaltungsseite fuer Cronjobs
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/globals.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/cronjob.class.php');
require_once('../../include/datum.class.php');

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>Cronjob - Verwaltung</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../skin/styles/jquery.css" type="text/css">
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
		<script src="../../include/js/JSONeditor/JSONeditor.js" type="text/javascript"></script>
		<script src="../../include/js/jquery.js" type="text/javascript"></script>
		<script src="../../include/js/jquery-ui.js" type="text/javascript"></script>
		<script type="text/javascript">
		function initwarnung()
		{
			return confirm("Wollen Sie die Variablen wirklich initialisieren? "+
				"\nDie bestehenden Variablen werden dabei überschrieben!"+
				"\n\nACHTUNG: Wenn das Script keine Initialisierung unterstützt, wird es normal ausgeführt");
		}
		</script>
	</head>
	<body>
	<h2>Cronjob - Verwaltung</h2>
';
$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/cronjob', null, 's'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$cronjob_id = (isset($_REQUEST['cronjob_id'])?$_REQUEST['cronjob_id']:'');
$server_kurzbz = (isset($_POST['server_kurzbz'])?$_POST['server_kurzbz']:'');
$titel = (isset($_POST['titel'])?$_POST['titel']:'');
$beschreibung = (isset($_POST['beschreibung'])?$_POST['beschreibung']:'');
$file = (isset($_POST['file'])?$_POST['file']:'');
$aktiv = isset($_POST['aktiv']);
$jahr = (isset($_POST['jahr'])?$_POST['jahr']:'');
$monat = (isset($_POST['monat'])?$_POST['monat']:'');
$tag = (isset($_POST['tag'])?$_POST['tag']:'');
$wochentag = (isset($_POST['wochentag'])?$_POST['wochentag']:'');
$stunde = (isset($_POST['stunde'])?$_POST['stunde']:'');
$minute = (isset($_POST['minute'])?$_POST['minute']:'');
$standalone = isset($_POST['standalone']);
$reihenfolge = (isset($_POST['reihenfolge'])?$_POST['reihenfolge']:'');
$variablen = (isset($_POST['variablen'])?$_POST['variablen']:'');
$datum_obj = new datum();

// Loeschen eines Jobs
if(isset($_GET['cronjob_id']) && isset($_GET['type']) && $_GET['type']=='delete')
{
	if(!$rechte->isBerechtigt('basis/cronjob', null, 'suid'))
		die('Sie haben keine Berechtigung zum Loeschen der Daten');

	//Loeschen eines Cronjobs
	$cj = new cronjob();
	if(!$cj->delete($_GET['cronjob_id']))
		echo 'Fehler beim Loeschen:'.$cj->errormsg;
	else 
		echo 'Cronjob wurde gelöscht!';
}

// Speichern eines Jobs
if(isset($_GET['type']) && $_GET['type']=='save')
{
	if(!$rechte->isBerechtigt('basis/cronjob', null, 'suid'))
		die('Sie haben keine Berechtigung zum Bearbeiten der Daten');

	$cj = new cronjob();
	
	if($cronjob_id!='')
	{
		if(!$cj->load($cronjob_id))
			die('Cronjob konnte nicht geladen werden');
		$cj->new = false;
		$cj->updateamum = date('Y-m-d H:i:s');
		$cj->updatevon = $user;
	}
	else
	{
		$cj->new = true;
		$cj->insertamum = date('Y-m-d H:i:s');
		$cj->insertvon = $user;
	}
	
	$cj->server_kurzbz = $server_kurzbz;
	$cj->titel = $titel;
	$cj->beschreibung = $beschreibung;
	$cj->file = $file;
	$cj->aktiv = $aktiv;
	$cj->jahr = $jahr;
	$cj->monat = $monat;
	$cj->tag = $tag;
	$cj->wochentag = $wochentag;
	$cj->stunde = $stunde;
	$cj->minute = $minute;
	$cj->standalone = $standalone;
	$cj->reihenfolge = $reihenfolge;
	$cj->variablen = $variablen;
	
	if(!$cj->save())
		echo 'Fehler beim Speichern der Daten: '.$cj->errormsg;
	else 
		echo 'Daten erfolgreich gespeichert';
}

// Starten eines Jobs
if(isset($_GET['cronjob_id']) && isset($_GET['type']) && $_GET['type']=='init')
{
	if(!$rechte->isBerechtigt('basis/cronjob', null, 'suid'))
		die('Sie haben keine Berechtigung fuer diese Aktion');

	//Loeschen eines Cronjobs
	$cj = new cronjob();
	if($cj->load($_GET['cronjob_id']))
	{
		if($cj->init())
			echo 'Job wurde erfolgreich initialisiert';
		else 
			echo 'Fehler beim Initialisieren des Jobs:'.$cj->errormsg;
		$_GET['type']='edit';
	}
	else 
	{
		echo 'Fehler beim Laden des Jobs!';
	}
}

// Starten eines Jobs
if(isset($_GET['cronjob_id']) && isset($_GET['type']) && $_GET['type']=='execute')
{
	if(!$rechte->isBerechtigt('basis/cronjob', null, 'suid'))
		die('Sie haben keine Berechtigung fuer diese Aktion');

	//Loeschen eines Cronjobs
	$cj = new cronjob();
	if($cj->load($_GET['cronjob_id']))
	{
		if($cj->execute())
			echo 'Job wurde erfolgreich ausgefuehrt';
		else 
			echo 'Fehler beim Starten des Jobs:'.$cj->errormsg;
	}
	else 
	{
		echo 'Fehler beim Laden des Jobs!';
	}
}
$cj = new cronjob();
if(!$cj->getAll(null, 'titel'))
	die('Fehler beim Laden der Cronjobs');
	
echo '<br><a href="'.$_SERVER['PHP_SELF'].'?type=new">Neuen Cronjob anlegen</a>';

echo '<br><br>
<table class="liste table-autosort:2 table-stripeclass:alternate table-autostripe">
	<thead>
	<tr>
		<th class="table-sortable:default">ID</th>
		<th class="table-sortable:default">Server</th>
		<th class="table-sortable:default">Titel</th>
		<th class="table-sortable:default">Aktiv</th>
		<th class="table-sortable:default">letzter Start</th>
		<th class="table-sortable:default">nächster Start</th>
		<th colspan="2"></th>
	</tr>
	</thead>
	<tbody>';

foreach ($cj->result as $job)
{
	if($next = $job->getNextExecutionTime())
		$next = date('d.m.Y H:i:s',$next);
	echo "
	<tr>
		<td>".htmlspecialchars($job->cronjob_id)."</td>
		<td>".htmlspecialchars($job->server_kurzbz)."</td>
		<td>".htmlspecialchars($job->titel)."</td>
		<td>".($job->aktiv?'Ja':'Nein')."</td>
		<td>".$datum_obj->formatDatum($job->last_execute,'d.m.Y H:i:s')."</td>
		<td>".$next." (<a href=\"".$_SERVER['PHP_SELF']."?cronjob_id=$job->cronjob_id&type=execute\">jetzt starten</a>)</td>
		<td><a href=\"".$_SERVER['PHP_SELF']."?cronjob_id=$job->cronjob_id&type=edit\">details</a></td>
		<td><a href=\"".$_SERVER['PHP_SELF']."?cronjob_id=$job->cronjob_id&type=delete\">entfernen</a></td>
	</tr>";
}

echo '</tbody></table>';
	
// Neu anlegen eines Jobs
if(isset($_GET['type']) && ($_GET['type']=='edit' || $_GET['type']=='new'))
{
	if(!$rechte->isBerechtigt('basis/cronjob', null, 'suid'))
		die('Sie haben keine Berechtigung zum Bearbeiten der Daten');

	//Formular zum Editieren und neu Anlegen der Daten anzeigen
	
	$cj = new cronjob();
	echo '<br><hr><br>';
	if($_GET['type']=='edit')
	{
		if(!isset($_GET['cronjob_id']))
			die('Fehlerhafte Parameteruebergabe');
		
		if(!$cj->load($_GET['cronjob_id']))
			die('Fehler beim Laden des Eintrages');
			
		$cj->new=false;
		echo '<h3>Details zu Cronjob '.$cj->cronjob_id.'</h3>';
	}
	else 
	{
		echo '<h3>Neuer Cronjob</h3>';
		$cj->new=true;
	}
	
	echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'?type=save">';
	echo '
		<table>
			<tr>
				<td><label for="titel">Titel</label></td>
				<td><input type="text" name="titel" id="titel" maxlength="64" value="'.htmlspecialchars($cj->titel).'"></td>
				<td><label for="beschreibung">Beschreibung</label></td>
				<td colspan="8"><input type="text" name="beschreibung" id="beschreibung" size="80" value="'.htmlspecialchars($cj->beschreibung).'"></td>				
			</tr>
			<tr>
				<td><label for="server_kurzbz">Server</label></td>
				<td>
					<SELECT name="server_kurzbz" id="server_kurzbz">
						<OPTION value="">-- Alle --</OPTION>';
	
	$qry = "SELECT * FROM system.tbl_server ORDER BY server_kurzbz";
	$db = new basis_db();
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			if($row->server_kurzbz==$cj->server_kurzbz)
				$selected='selected="selected"';
			else 
				$selected='';
			
			echo '<OPTION value="'.$row->server_kurzbz.'" '.$selected.'>'.$row->server_kurzbz.'</OPTION>';
		}
	}
	
	echo '				
					</SELECT>
				</td>
				<td><label for="file">Datei</a></td>
				<td colspan="8"><input type="text" size="80" id="file" name="file" value="'.htmlspecialchars($cj->file).'"></td>
			</tr>
			<tr>
				<td><label for="jahr">Jahr</label></td>
				<td><input type="text" name="jahr" id="jahr" maxlength="6" size="6" value="'.htmlspecialchars($cj->jahr).'"></td>
				<td><label for="monat">Monat</label></td>
				<td><input type="text" name="monat" id="monat" maxlength="4" size="4" value="'.htmlspecialchars($cj->monat).'"></td>
				<td><label for="tag">Tag</label></td>
				<td><input type="text" name="tag" id="tag" maxlength="4" size="4" value="'.htmlspecialchars($cj->tag).'"></td>
				<td><label for="wochentag">Wochentag</label></td>
				<td>
					<SELECT name="wochentag" id="wochentag">
						<OPTION value="">-- keine Auswahl --</OPTION>';
	
	foreach($tagbez as $key=>$day)
	{
		if($key==$cj->wochentag && $cj->wochentag!='')
			$selected='selected="selected"';
		else 
			$selected='';
		
		echo '<OPTION value="'.$key.'" '.$selected.'>'.$day.'</OPTION>';
	}
	
	echo '						
					</SELECT>
				</td>
				<td><label for="stunde">Stunde</label></td>
				<td><input type="text" name="stunde" id="stunde" maxlength="4" size="4" value="'.htmlspecialchars($cj->stunde).'"></td>
				<td><label for="minute">Minute</label></td>
				<td><input type="text" name="minute" id="minute" maxlength="4" size="4" value="'.htmlspecialchars($cj->minute).'"></td>
			</tr>
			<tr>
				<td><label for="aktiv">Aktiv</label></td>
				<td><input type="checkbox" name="aktiv" id="aktiv" '.($cj->aktiv?'checked="checked"':'').'></td>
				<td><label for="standalone">Standalone</label></td>
				<td><input type="checkbox" name="standalone" id="standalone" '.($cj->standalone?'checked="checked"':'').'></td>
				<td><label for="reihenfolge">Reihenfolge</label></td>
				<td><input type="text" name="reihenfolge" id="reihenfolge" maxlength="4" size="4" value="'.htmlspecialchars($cj->reihenfolge).'"></td>
			</tr>
			<tr>
				<td>letzter Start&nbsp;</td>
				<td>'.$datum_obj->formatDatum($cj->last_execute,'d.m.Y H:i:s').'</td>
				<td>Running</td>
				<td>'.($cj->running?'Ja':'Nein').'</td>
			</tr>
			<tr>
				<td valign="top">
					Variablen<br/>';
	if($_GET['type']!='new')
		echo '<a href="'.$_SERVER['PHP_SELF'].'?cronjob_id='.$cj->cronjob_id.'&type=init" onclick="return initwarnung()">Initialisieren</a>';
	
	echo '	</td>
				<td colspan="8">
					<textarea id="variablen" name="variablen" cols="80" rows="3" >'.htmlspecialchars($cj->variablen).'</textarea>
				</td>
				<td colspan="3">
				<input type="button" id="opener" value="Variablen-Editor" />
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td></td>
			</tr>
			<tr>
				<td><input type="hidden" name="cronjob_id" value="'.$cj->cronjob_id.'"></td>
				<td><input type="submit" name="save" value="Speichern"></td>
			</tr>
		</table>';
	echo '</form>
	<div id="dialog" >
	<table>
	<tr>
		<td style="background-color: white; border: 1px solid black" valign="top"><div style="width:150px" id="tree"></div></td>
		<td><div style="width:400px" id="jform"></div></td>
	</tr>
	</table>
	</div>
	<script>
	var myjson=0;
	onload=function()
	{
		myjson = '.($cj->variablen!=''?$cj->variablen:'{}').';
		JSONeditor.start(\'tree\',\'jform\',myjson,false,\'../../include/js/JSONeditor/\');
		
	}
	$(function() 
	{
		$(\'#dialog\').dialog(
		{
			autoOpen: false,
			width: 600,
			modal: true,
			show: \'blind\',
			hide: \'blind\',
			close: function(event, ui) 
					{ 
						jsonwert=JSON.stringify(JSONeditor.treeBuilder.json);
						document.getElementById(\'variablen\').value = jsonwert; 
					}

		});
		
		$(\'#opener\').click(function() 
		{
			$(\'#dialog\').dialog(\'open\');
			return false;
		});
	});


	</script>
	';
}

echo '
	</body>
</html>';
?>