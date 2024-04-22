<?php
/* Copyright (C) 2024 Technikum-Wien
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
 *
 * Export von Projektlisten für HR
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/zeitsperre.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/benutzerfunktion.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();
$datum = new datum();

//Rechte Pruefen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('mitarbeiter/zeitsperre'))
	die($rechte->errormsg);

//Kopfzeile
echo '<html>
	<head>
		<title>Zeitsperren (Urlaube) der MitarbeiterInnen</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../skin/jquery-ui-1.9.2.custom.min.css" type="text/css">';

		include('../../include/meta/jquery.php');
		include('../../include/meta/jquery-tablesorter.php');

echo '	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
		<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script language="Javascript">
		$(document).ready(function()
		{
			$("#ma_name").autocomplete({
			source: "../../cis/private/tools/zeitaufzeichnung_autocomplete.php?autocomplete=kunde",
			minLength:2,
			response: function(event, ui)
			{
				//Value und Label fuer die Anzeige setzen
				for(i in ui.content)
				{
					ui.content[i].value=ui.content[i].uid;
					ui.content[i].label=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
				}
			},
			select: function(event, ui)
			{
				//Ausgeaehlte Ressource zuweisen und Textfeld wieder leeren
				$("#ma_name").val(ui.item.uid);
			}
			});
			$("#t1").tablesorter(
			{
				sortList: [[3,1]],
				widgets: [\'zebra\', \'filter\'],
				headers : { 3 : { sorter: "shortDate", dateFormat: "ddmmyyyy" },4 : { sorter: "shortDate", dateFormat: "ddmmyyyy" } }
			});
			$( ".datepicker_datum" ).datepicker({
					 changeMonth: true,
					 changeYear: true,
					 dateFormat: "dd.mm.yy",
			});
		})
		</script>
	</head>
	<body class="Background_main">
	<h2>Projektexport</h2>

	Wähle einen Mitarbeiter und einen Zeitraum um die Projektliste für diesen Monat zu erstellen.<br /><br />
	';


$redirect = basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']);

echo '<form accept-charset="UTF-8" action="../../cis/private/tools/zeitaufzeichnung_projektliste.php" method="GET" taget="_blank">
<table>
	<tr>
	<td>
		User
	</td>
	<td>
		<input type="text" id="ma_name" name="uid">
	</td>
	</tr>
	<tr>
	<td>Monat</td>
	<td>
		<select name="projexpmonat" >
			<option value="1">Jänner</option>
			<option value="2">Februar</option>
			<option value="3">März</option>
			<option value="4">April</option>
			<option value="5">Mai</option>
			<option value="6">Juni</option>
			<option value="7">Juli</option>
			<option value="8">August</option>
			<option value="9">September</option>
			<option value="10">Oktober</option>
			<option value="11">November</option>
			<option value="12">Dezember</option>
		</select>
	</td>
	</tr>

	<tr>
	<td>Jahr</td>
	<td>
		<input type="text" name="projexpjahr" value="'.date('Y').'" size="4"/>
	</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	<td></td>
	<td><input type="submit" value="Export" name="export">
	</td>
	</tr>
	</table>
	</form>';

echo '</body></html>';
?>
