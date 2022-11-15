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
 * Authors: Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 */
/**
 * Seite zur Wartung der Services
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/service.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/organisationseinheit.class.php');
require_once('../../include/benutzer.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/service'))
	die($rechte->errormsg);

$datum_obj = new datum();
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Service - Details</title>
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link href="../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>


	<?php //echo include('../../include/meta/jquery.php'); ?>

	<script type="text/javascript">
	$(document).ready(function()
	{
		$(".benutzer_uid").autocomplete({
			source: "reihungstestverwaltung_autocomplete.php?autocomplete=kunde",
			minLength:2,
			response: function(event, ui)
			{
				//Value und Label fuer die Anzeige setzen
				for(i in ui.content)
				{
					//ui.content[i].value=ui.content[i].uid;
					ui.content[i].value=ui.content[i].uid;
					ui.content[i].label=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
				}
			},
			select: function(event, ui)
			{
				//Ausgeaehlte Ressource zuweisen und Textfeld wieder leeren
				$(this.id).val(ui.item.uid);
			}
		});
	});
	</script>
</head>
<body>

<?php
	$action = (isset($_GET['action'])?$_GET['action']:'new');
	$service_id = (isset($_REQUEST['service_id'])?$_REQUEST['service_id']:'');
	$service = new service();

	if($action=='save')
	{
		$error = false;
		$bezeichnung = (isset($_POST['bezeichnung'])?$_POST['bezeichnung']:die('Bezeichnung fehlt'));
		$beschreibung = (isset($_POST['beschreibung'])?$_POST['beschreibung']:die('Beschreibung fehlt'));
		$oe_kurzbz = (isset($_POST['oe_kurzbz'])?$_POST['oe_kurzbz']:die('Organisationseinheit fehlt'));
		$content_id = (isset($_POST['content_id'])?$_POST['content_id']:die('Content_id fehlt'));
		$design_uid = (isset($_POST['design_uid'])?$_POST['design_uid']:'');
		$betrieb_uid = (isset($_POST['betrieb_uid'])?$_POST['betrieb_uid']:'');
		$operativ_uid = (isset($_POST['operativ_uid'])?$_POST['operativ_uid']:'');
		$ext_id = (isset($_POST['ext_id'])?$_POST['ext_id']:die('ext_id fehlt'));
		$servicekategorie_kurzbz = (isset($_POST['servicekategorie_kurzbz'])?$_POST['servicekategorie_kurzbz']:'');
		$new = (isset($_POST['new'])?$_POST['new']:'true');
		if($new=='true')
		{
			$service->new = true;
		}
		else
		{
			if(!$service->load($service_id))
				die($service->errormsg);

			$service->new = false;
		}

		$service->bezeichnung = $bezeichnung;
		$service->beschreibung = $beschreibung;
		$service->ext_id = $ext_id;
		$service->oe_kurzbz = $oe_kurzbz;
		$service->content_id = $content_id;
		$service->servicekategorie_kurzbz = $servicekategorie_kurzbz;

		if ($design_uid != '' || $betrieb_uid != '' || $operativ_uid != '')
		{
			$benutzer = new benutzer();
			if ($design_uid != '' && !$benutzer->load($design_uid))
			{
				echo '<span class="error">Benutzer '.$design_uid.' ist nicht vorhanden</span>';
				$error = true;
			}
			if ($betrieb_uid != '' && !$benutzer->load($betrieb_uid))
			{
				echo '<span class="error">Benutzer '.$betrieb_uid.' ist nicht vorhanden</span>';
				$error = true;
			}
			if ($operativ_uid != '' && !$benutzer->load($operativ_uid))
			{
				echo '<span class="error">Benutzer '.$operativ_uid.' ist nicht vorhanden</span>';
				$error = true;
			}
		}

		$service->design_uid = $design_uid;
		$service->betrieb_uid = $betrieb_uid;
		$service->operativ_uid = $operativ_uid;

		if ($error == false)
		{
			if($service->save())
			{
				echo '<span class="ok">Daten erfolgreich gespeichert</span>';
				echo "<script type='text/javascript'>\n";
				//echo "	parent.uebersicht_service.location.href='service_uebersicht.php?oe_kurzbz=$oe_kurzbz';";
				echo "</script>\n";
				$action='update';
				$service_id = $service->service_id;
			}
			else
			{
				$action='new';
				echo '<span class="error">'.$service->errormsg.'</span>';
			}
		}
		else
		{
			$action='update';
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
			if(!$service->load($service_id))
				die($service->errormsg);
			echo "<legend>Bearbeiten von ID $service_id</legend>";
			$new = 'false';
			break;
		default:
			die('Invalid Action');
			break;
	}

	$servicekategorie_arr = $service->getKategorieArray();

	echo '<form action="'.$_SERVER['PHP_SELF'].'?action=save" method="POST">';
	echo '<input type="hidden" name="new" value="'.htmlspecialchars($new).'">';
	echo '<input type="hidden" name="service_id" value="'.htmlspecialchars($service->service_id).'">';
	echo '<table>';
	echo '<tr>';
	echo '   <td>Organisationseinheit&nbsp;</td>';
	echo '   <td>';
	echo '<SELECT name="oe_kurzbz">';
	$oe = new organisationseinheit();
	$oe->getAll();
	foreach($oe->result as $row)
	{
		if($row->oe_kurzbz==$service->oe_kurzbz)
			$selected='selected';
		else
			$selected='';

		echo '<OPTION value="'.$row->oe_kurzbz.'" '.$selected.'>'.$row->organisationseinheittyp_kurzbz.' '.$row->bezeichnung.'</OPTION>';
	}

	echo '</SELECT>';
	echo ' </td>';
	echo '</tr>';
	echo '<tr>';
	echo '   <td>Bezeichnung</td>';
	echo '   <td><input type="text" name="bezeichnung" size="30" maxlength="64" value="'.htmlspecialchars($service->bezeichnung).'"></td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '   <td>Beschreibung</td>';
	echo '   <td><textarea name="beschreibung" cols="60" rows="5">'.htmlspecialchars($service->beschreibung).'</textarea></td>';
	echo '</tr>';
	echo '<tr>';
	echo '   <td>Kategorie&nbsp;</td>';
	echo '   <td>';
	echo '<SELECT name="servicekategorie_kurzbz">';
	echo '<OPTION value="">-- keine Auswahl --</OPTION>';
	foreach($servicekategorie_arr as $key=>$value)
	{
		if($key==$service->servicekategorie_kurzbz)
			$selected='selected';
		else
			$selected='';

		echo '<OPTION value="'.$key.'" '.$selected.'>'.$value.'</OPTION>';
	}

	echo '</SELECT>';
	echo ' </td>';
	echo '<tr valign="top">';
	echo '   <td>Design</td>';
	echo '   <td><input type="text" id="design_uid" name="design_uid" class="benutzer_uid" size="32" maxlength="32" value="'.htmlspecialchars($service->design_uid).'"></td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '   <td>Betrieb</td>';
	echo '   <td><input type="text" id="betrieb_uid" name="betrieb_uid" class="benutzer_uid" size="32" maxlength="32" value="'.htmlspecialchars($service->betrieb_uid).'"></td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '   <td>Operativ</td>';
	echo '   <td><input type="text" id="operativ_uid" name="operativ_uid" class="benutzer_uid" size="32" maxlength="32" value="'.htmlspecialchars($service->operativ_uid).'"></td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '   <td>Content_ID</td>';
	echo '   <td><input type="text" name="content_id" size="8" maxlength="10" value="'.htmlspecialchars($service->content_id).'"></td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '   <td>Externe ID</td>';
	echo '   <td><input type="text" name="ext_id" size="4" maxlength="10" value="'.htmlspecialchars($service->ext_id).'"></td>';
	echo '</tr>';
	echo '<tr><td></td><td>&nbsp;</td></tr>';
	echo '<tr valign="bottom">';
	echo '   <td></td>';
	echo '   <td><input type="submit" value="Speichern" name="save"></td>';
	echo '</table>';
	echo '</form>';

	echo '</fieldset>';
?>
</body>
</html>
