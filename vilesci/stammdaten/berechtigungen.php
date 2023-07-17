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

if(!$rechte->isBerechtigt('basis/berechtigung'))
	die($rechte->errormsg);

$berechtigung_kurzbz = filter_input(INPUT_POST, 'berechtigung_kurzbz');
$beschreibung = filter_input(INPUT_POST, 'beschreibung');
$kurzbz = filter_input(INPUT_GET, 'kurzbz');
$kurzbzPost = filter_input(INPUT_POST, 'kurzbz_post');
$edit = filter_input(INPUT_GET, 'edit');
$save = filter_input(INPUT_POST, 'save');
$delete = filter_input(INPUT_GET, 'delete');
$saveNew = filter_input(INPUT_POST, 'saveNew');

//$delete = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_BOOLEAN);
?>
<html>
	<head>
		<title>Rechte Übersicht</title>
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
					headers: {2:{sorter:false, filter:false}},
					widgetOptions : {filter_saveFilters : true}
				});
				$('.resetsaved').click(function()
				{
					$("#t1").trigger("filterReset");
					window.location("<?php echo $_SERVER['PHP_SELF'] ?>");
					return false;
				});
				$("textarea").keyup(function()
				{
					$(this).siblings("span").text((256 - $(this).val().length));
				});
			});
			function confdel(berechtigung)
			{
				return confirm("Achtung! Sie sollten nur Rechte löschen, die unabhängig von Core-Funktionalitäten angelegt wurden.\n\n"
								+"Sie können ein Recht erst dann löschen, wenn alle Zuordnungen zu Personen, Rollen und Funktionen ebenfalls entfernt wurden.\n\n"
								+"Wollen Sie das Recht '" + berechtigung + "' wirklich löschen?");
			}
		</script>
	</head>

	<body class="background_main">
		<h2>Rechte Übersicht</h2>

	<?php
		if(isset($saveNew))
		{
			if(isset($kurzbzPost) && isset($beschreibung))
			{
				$berechtigung = new berechtigung();
				$berechtigung->berechtigung_kurzbz = $kurzbzPost;
				$berechtigung->beschreibung = $beschreibung;
				$berechtigung->new = true;

				if($berechtigung->save())
				{
					echo 'Recht '.$kurzbzPost.' wurde angelegt<br><br>';
				}
				else
				{
					echo 'Fehler beim Speichern:'.$berechtigung->errormsg.'<br><br>';
				}
			}
			else
			{
				echo 'Zum Speichern der Daten muss die Kurzbz und die Beschreibung angegeben werden<br><br>';
			}
		}

		if(isset($delete) && isset($kurzbz))
		{
			$berechtigung = new berechtigung();
			if($berechtigung->delete($kurzbz))
				echo 'Das Recht "'.$kurzbz.'" wurde erfolgreich gelöscht<br><br>';
			else
				echo 'Fehler beim Löschen des Rechts:'.$berechtigung->errormsg.'<br><br>';
		}

		if(isset($save))
		{
			$berechtigung = new berechtigung();
			$berechtigung->load($berechtigung_kurzbz);
			$berechtigung->beschreibung = $beschreibung;
			if (!$berechtigung->save(false))
				echo 'Fehler beim Speichern:'.$berechtigung->errormsg.'<br><br>';
		}

		//Tabelle mit Rollen anzeigen
		$berechtigung = new berechtigung();
		$berechtigung->getBerechtigungen();

		echo '<button type="button" class="resetsaved" title="Reset Filter">Reset Filter</button>';
		echo '
		<table id="t1" class="tablesorter">
			<thead>
				<tr>
					<th>Kurzbz</th>
					<th>Beschreibung</th>
					<th colspan="2">Aktion</th>
				</tr>
			</thead>
			<tbody>';

			foreach($berechtigung->result as $recht)
			{
				if($edit && $recht->berechtigung_kurzbz == $kurzbz)
				{
					echo '
						<tr>
							<td>
								'.$recht->berechtigung_kurzbz.'
							</td>
							<td colspan="3"><form method="POST" action="'.$_SERVER['PHP_SELF'].'?kurzbz='.$kurzbz.'">
									<textarea style="vertical-align: top; font-family: inherit; font-size: small;" 
											cols="50" 
											rows="3" 
											type="text" 
											maxlength="256" 
											size="200" 
											name="beschreibung" />'.$recht->beschreibung.'</textarea>
									<span style="color: grey; display: inline-block; width: 30px;">'.(256 - strlen($recht->beschreibung)).'</span>
									<input type="hidden" name="berechtigung_kurzbz" value="'.$recht->berechtigung_kurzbz.'" />
									<input type="submit" name="save" value="Speichern" />
								</form>
							</td>
						</tr>';
				}
				else
				{
					echo '
						<tr>
							<td>'.$recht->berechtigung_kurzbz.'</td>
							<td>'.$recht->beschreibung.'</td>
							<td>
								<a href="'.basename(__FILE__).'?kurzbz='.$recht->berechtigung_kurzbz.'&edit=1">
									Bearbeiten
								</a>
							</td>
							<td>
								<a href="'.basename(__FILE__).'?kurzbz='.$recht->berechtigung_kurzbz.'&delete=1" 
									onclick="return confdel(\''.$recht->berechtigung_kurzbz.'\')">
									Recht löschen
								</a>
							</td>
						</tr>';
				}
			}
			echo '
			</tbody>
		</table>

		<br><div style="vertical-align: top">';

		echo '	<form method="POST">
					<h2>Neues Recht einfügen</h2>
					Kurzbz: <input type="text" maxlength="32" size="35" name="kurzbz_post" value="" />
					Beschreibung: <textarea style="vertical-align: top; font-family: inherit; font-size: small;" 
											cols="50" 
											rows="3" 
											type="text" 
											maxlength="256" 
											size="200" 
											name="beschreibung" 
											value="" /></textarea>
					<span style="color: grey; display: inline-block; width: 30px;" id="countdown_textarea_new">256</span>
					&nbsp;<input type="submit" name="saveNew" value="Anlegen" />
				</form>
			</div>
	</body>
</html>';
	?>
