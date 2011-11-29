<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 */
/**
 * Seite zur Wartung der Ampeln
 */
require_once('../../config/vilesci.config.inc.php');		
require_once('../../include/ampel.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/datum.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
		
$user = get_uid();
	
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
	
if(!$rechte->isBerechtigt('basis/ampel'))
	die('Sie haben keine Berechtigung fuer diese Seite');
	
$datum_obj = new datum();
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Ampel - Details</title>
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body>

<?php
	$action = (isset($_GET['action'])?$_GET['action']:'new');
	$ampel_id = (isset($_REQUEST['ampel_id'])?$_REQUEST['ampel_id']:'');
	$ampel = new ampel();
	
	if($action=='save')
	{
		$kurzbz = (isset($_POST['kurzbz'])?$_POST['kurzbz']:die('Kurzbz fehlt'));
		
		foreach ($_POST as $key=>$value)
		{
			if(mb_strstr($key,'beschreibung'))
			{
				$idx = mb_substr($key, mb_strlen('beschreibung'));
				$beschreibung[$idx] = $value;
			}
		}
		$benutzer_select = (isset($_POST['benutzer_select'])?$_POST['benutzer_select']:die('Benutzer_select fehlt'));
		$deadline = (isset($_POST['deadline'])?$_POST['deadline']:die('Deadline fehlt'));
		$vorlaufzeit = (isset($_POST['vorlaufzeit'])?$_POST['vorlaufzeit']:die('Vorlaufzeit fehlt'));
		$verfallszeit = (isset($_POST['verfallszeit'])?$_POST['verfallszeit']:die('verfallszeit fehlt'));
		$new = (isset($_POST['new'])?$_POST['new']:'true');
		if($new=='true')
		{
			$ampel->insertamum=date('Y-m-d H:i:s');
			$ampel->insertvon = $user;
			$ampel->new = true;
		}
		else
		{
			if(!$ampel->load($ampel_id))
				die($ampel->errormsg);
				
			$ampel->new=false;
		}
		
		$ampel->kurzbz=$kurzbz;
		$ampel->beschreibung = $beschreibung;
		$ampel->benutzer_select = $benutzer_select;
		$ampel->deadline = $datum_obj->formatDatum($deadline,'Y-m-d');
		$ampel->vorlaufzeit = $vorlaufzeit;
		$ampel->verfallszeit = $verfallszeit;
		$ampel->updateamum = date('Y-m-d H:i:s');
		$ampel->updatevon = $user;
		
		if($ampel->save())
		{		
			echo '<span class="ok">Daten erfolgreich gespeichert</span>';
			echo "<script type='text/javascript'>\n";
			echo "	parent.uebersicht_ampel.location.href='ampel_uebersicht.php';";
			echo "</script>\n";
			$action='update';
			$ampel_id = $ampel->ampel_id;
		}
		else
		{
			$action='new';
			echo '<span class="error">'.$ampel->errormsg.'</span>';
		}
	}

	echo '<fieldset>';
	switch($action)
	{
		case 'new':
			echo '<legend>Neu</legend>';
			$new = 'true';
			break;
		case 'update':
			if(!$ampel->load($ampel_id))
				die($ampel->errormsg);
			echo "<legend>Bearbeiten von ID $ampel_id</legend>";
			$new = 'false';
			break;
		case 'copy':
			if(!$ampel->load($ampel_id))
				die($ampel->errormsg);
			echo "<legend>Kopieren von ID $ampel_id</legend>";
			$new = 'true';
			$ampel->ampel_id='';
			break;
		default:
			die('Invalid Action');
			break;
	}
	
	echo '<form action="'.$_SERVER['PHP_SELF'].'?action=save" method="POST">';
	echo '<input type="hidden" name="new" value="'.htmlspecialchars($new).'">';
	echo '<input type="hidden" name="ampel_id" value="'.htmlspecialchars($ampel->ampel_id).'">';
	echo '<table>';
	echo '<tr>';
	echo '   <td>Kurzbz</td>';	
	echo '   <td><input type="text" name="kurzbz" size="30" maxlength="64" value="'.htmlspecialchars($ampel->kurzbz).'"></td>';
	echo '	 <td></td>';
	echo '   <td>Deadline</td>';
	echo '   <td><input type="text" name="deadline" size="10" maxlength="10" value="'.htmlspecialchars($datum_obj->formatDatum($ampel->deadline,'d.m.Y')).'"></td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '   <td rowspan="2">Benutzer Select</td>';
	echo '   <td rowspan="2"><textarea name="benutzer_select" cols="60" rows="5">'.htmlspecialchars($ampel->benutzer_select).'</textarea></td>';
	echo '   <td></td>';
	echo '   <td valign="middle">Vorlaufzeit (in Tagen)</td>';
	echo '   <td valign="middle"><input type="text" name="vorlaufzeit" size="4" maxlength="4" value="'.htmlspecialchars($ampel->vorlaufzeit).'"></td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '   <td></td>';
	echo '   <td>Verfallszeit (in Tagen)</td>';
	echo '   <td><input type="text" name="verfallszeit" size="4" maxlength="4" value="'.htmlspecialchars($ampel->verfallszeit).'"></td>';
	echo '</tr>';
	
	$sprache = new sprache();
	$sprache->getAll();
	foreach($sprache->result as $lang)
	{
		echo '<tr valign="top">';
		echo '   <td>Beschreibung '.$lang->sprache.'</td>';
		echo '   <td><textarea name="beschreibung'.$lang->sprache.'" cols="60" rows="5">'.htmlspecialchars((isset($ampel->beschreibung[$lang->sprache])?$ampel->beschreibung[$lang->sprache]:'')).'</textarea></td>';
		echo '   <td></td>';
		echo '</tr>';
	}
	echo '<tr valign="bottom">';
	echo '   <td></td>';
	echo '   <td></td>';
	echo '   <td><input type="submit" value="Speichern" name="save"></td>';
	echo '</table>';
	echo '</form>';
	
	echo '</fieldset>';
?>
</body>
</html>