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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/berechtigung.class.php');
require_once('../../include/benutzerberechtigung.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if (!$rechte->isBerechtigt('basis/berechtigung'))
	die($rechte->errormsg);

$rolle_kurzbz = filter_input(INPUT_GET, 'rolle_kurzbz');
$delete = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_BOOLEAN);
$save = filter_input(INPUT_GET, 'save');
?>
<html>
	<head>
		<title>Rollen Rollen Uebersicht</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link href="../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
		<?php
		include('../../include/meta/jquery.php');
		include('../../include/meta/jquery-tablesorter.php');
		?>
		<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
		<script language="Javascript">
			$(document).ready(function()
			{
				$("#t1").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra", "filter", "stickyHeaders"],
					headers: {3:{filter:false, sorter:false}},
					widgetOptions : {filter_saveFilters : true}
				});

				$('.resetsaved').click(function()
				{
					$(".tablesorter").trigger("filterReset");
					return false;
				});
				$("textarea").keyup(function()
				{
					$(this).siblings("span").text((256 - $(this).val().length));
				});

				// Breite des Autocompletes korrigieren um das Springen zu verhindern
				$.extend($.ui.autocomplete.prototype.options, {
					open: function(event, ui) {
						$(this).autocomplete("widget").css({
							"width": ($(".ui-menu-item").width()+ 20 + "px"),
							"padding-left": "5px"
						});
					}
				});

				$(".rolle_autocomplete").autocomplete({
					source: "benutzerberechtigung_autocomplete.php?autocomplete=rollen&rolle=<?php echo $rolle_kurzbz; ?>",
					minLength:2,
					response: function(event, ui)
					{
						//Value und Label fuer die Anzeige setzen
						for(i in ui.content)
						{
							ui.content[i].value=ui.content[i].rolle_kurzbz;
							ui.content[i].label=ui.content[i].rolle_kurzbz+" - "+ui.content[i].beschreibung;
						}
					},
					select: function(event, ui)
					{
						//Ausgewaehlte Ressource zuweisen und Textfeld wieder leeren
						$(this).val(ui.item.rolle_kurzbz);
					}
				});
			});

			function validateNewData()
			{
				if($('#rolle_neu_autocomplete').val() == '')
				{
					alert('Rolle darf nicht leer sein')
					return false;
				}
				else
					return true;
			}
		</script>
		<style>
			button
			{
				line-height: 9pt;
			}
			table.tablesorter tr:hover td
			{
				/*pointer-events: none !important;*/
			}
			table.tablesorter tr.odd:hover td
			{
				background-color: #d3d3d3 !important;
			}
			table.tablesorter tr.even:hover td
			{
				background-color: #efefef !important;
			}
			table.tablesorter tr.odd:hover td.difference
			{
				background-color: #b2b2b2 !important;
				/*border-top: 1px solid #aaa;*/
				/*border-bottom: 1px solid #aaa;*/
			}
			table.tablesorter tr.even:hover td.difference
			{
				background-color: #b2b2b2 !important;
				/*border-top: 1px solid #aaa;*/
				/*border-bottom: 1px solid #aaa;*/
			}
			.difference
			{
				background-color: #b2b2b2 !important;
				border-top: 1px solid #aaa;
				border-bottom: 1px solid #aaa;
			}
			/*.difference:hover*/
			/*{*/
			/*	background-color: #b2b2b2 !important;*/
			/*	border-top: 1px solid #aaa;*/
			/*	border-bottom: 1px solid #aaa;*/
			/*}*/
			.tablesorter
			{
				border-collapse: collapse !important;
			}
		</style>
	</head>

	<body class="background_main">

<?php
	if (isset($rolle_kurzbz))
	{
		echo '<h2>Rollen der Rolle "'.$rolle_kurzbz.'"</h2>';

		if (isset($save))
		{
			$add_rolle_kurzbz = filter_input(INPUT_GET, 'add_rolle_kurzbz');
			if (isset($rolle_kurzbz) && isset($add_rolle_kurzbz))
			{
				$berechtigung = new berechtigung();

				if ($berechtigung->saveRolleRolle($rolle_kurzbz, $add_rolle_kurzbz, $user))
				{
					echo '<b>Zuteilung gespeichert</b>';
				}
				else
				{
					echo '<b>Fehler beim Speichern der Zuteilung: </b>'.$berechtigung->errormsg;
				}
			}
		}

		if (isset($delete))
		{
			$delete_rolle_kurzbz = filter_input(INPUT_GET, 'delete_rolle_kurzbz');
			if (isset($rolle_kurzbz) && isset($delete_rolle_kurzbz))
			{
				$berechtigung = new berechtigung();

				if ($berechtigung->deleteRolleRolle($rolle_kurzbz, $delete_rolle_kurzbz))
				{
					echo '<b>Rolle '.$delete_rolle_kurzbz.' gelöscht!</b>';
				}
				else
				{
					echo '<b>Fehler beim Löschen: </b>'.$berechtigung->errormsg;
				}
			}
		}
?>
		<a href="<?php echo APP_ROOT; ?>/vilesci/stammdaten/berechtigungrolle.php">
			Zurück zur Rollen Übersicht
		</a>
		<br><br>
<?php
		$berechtigung = new berechtigung();
		$berechtigung->getBerechtigungen();
?>
		<form action="<?php echo basename(__FILE__) ?>" method="GET">
			<div style="vertical-align: top">
			<input type="text" 
					value=""
					placeholder="Neue Rolle" 
					id="rolle_neu_autocomplete" 
					class="rolle_autocomplete" 
					name="add_rolle_kurzbz" 
					style="width: 300px">
			<input type="hidden" name="rolle_kurzbz" value="<?php echo $rolle_kurzbz ?>">
			<input type="submit" name="save" value="Hinzufügen" onclick="return validateNewData()">
			</div>
		</form>
		<button type="button" class="resetsaved" title="Reset Filter">Reset Filter</button>
		<table id="t1" class="tablesorter">
			<thead>
				<tr>
					<th>Kurzbz</th>
					<th>Beschreibung</th>
					<th>Lange Beschreibung</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
		<?php
		$berechtigungen = new berechtigung();
		$berechtigungen->getRollenRollen($rolle_kurzbz);

		foreach($berechtigungen->result as $rolle): ?>
				<tr>
					<td><?php echo $rolle->rolle_kurzbz ?></td>
					<td><?php echo $rolle->beschreibung ?></td>
					<td><?php echo $rolle->lange_beschreibung ?></td>
					<td>
						<a href="<?php echo basename(__FILE__) ?>?delete=1&rolle_kurzbz=<?php echo $rolle_kurzbz ?>&delete_rolle_kurzbz=<?php echo $rolle->rolle_kurzbz ?>">
							Rolle entfernen
						</a>
					</td>
				</tr>
		<?php endforeach; ?>
			</tbody>
		</table>
<?php
	}
?>
			</div>
	</body>
</html>
