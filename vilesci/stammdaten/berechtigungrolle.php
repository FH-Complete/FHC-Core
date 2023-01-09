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
	die('Sie habe keine Rechte um diese Seite anzuzeigen');

$rolle_kurzbz = filter_input(INPUT_GET, 'rolle_kurzbz');
$delete = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_BOOLEAN);
?>
<html>
	<head>
		<title>Berechtigungen Uebersicht</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link href="../../skin/tablesort.css" rel="stylesheet" type="text/css"/>
		<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
		<script language="Javascript">
			$(document).ready(function()
					{
						$("#t1").tablesorter(
							{
								sortList: [[0,0]],
								widgets: ["zebra"],
								headers: {3:{sorter:false}}
							});
						$("#t2").tablesorter(
								{
									sortList: [[0,0]],
									widgets: ["zebra"],
									headers: {2:{sorter:false}}
								});
					});
			function confdel()
			{
				var value=prompt('Achtung! Sie sind dabei eine Rolle zu löschen. Die Zuordnungen gehen dadurch verloren! Um diese Rolle wirklich zu Löschen tippen Sie "LÖSCHEN" in das untenstehende Feld.');

				if(value=='LÖSCHEN')
					return true;
				else
					return false;
			}
		</script>
	</head>

	<body class="background_main">
		<h2>Berechtigung - Rolle - Übersicht</h2>

	<?php
	if(isset($rolle_kurzbz))
	{
		$berechtigung_kurzbz = filter_input(INPUT_GET, 'berechtigung_kurzbz');
		$art = filter_input(INPUT_GET, 'art');
		$save = filter_input(INPUT_GET, 'save');

		if(isset($save))
		{
			if($rolle_kurzbz && $berechtigung_kurzbz && $art)
			{
				$berechtigung = new berechtigung();
				$berechtigung->rolle_kurzbz = $rolle_kurzbz;
				$berechtigung->berechtigung_kurzbz = $berechtigung_kurzbz;
				$berechtigung->art = $art;

				if($berechtigung->saveRolleBerechtigung()): ?>
					<b>Zuteilung gespeichert</b>
				<?php else: ?>
					<b>Fehler beim Speichern der Zuteilung: <?php echo $berechtigung->errormsg ?>
				<?php endif;
			}
		}

		if(isset($delete))
		{
			$berechtigung = new berechtigung();
			if(!$berechtigung->deleteRolleBerechtigung($rolle_kurzbz, $berechtigung_kurzbz)): ?>
				<b>Fehler beim Löschen: </b><?php echo $berechtigung->errormsg ?>
			<?php else: ?>
				<b>Berechtigung gelöscht!</b>
			<?php endif;
		} ?>

		<br>
		<a href="<?php echo basename(__FILE__) ?>">
			Zurück
		</a>
		<h3>RolleBerechtigung "<?php echo $rolle_kurzbz ?>":</h3>

		<?php
		$berechtigung = new berechtigung();
		$berechtigung->getBerechtigungen();
		?>
		<form action="<?php echo basename(__FILE__) ?>" method="GET">
			<input type="hidden" name="rolle_kurzbz" value="<?php echo $rolle_kurzbz ?>">
			<SELECT name="berechtigung_kurzbz">
		<?php
		$berechtigungen = new berechtigung();
		$berechtigungen->getRolleBerechtigung($rolle_kurzbz);
		$berechtigungen_arr = array();
		foreach ($berechtigungen->result as $row)
		{
			$berechtigungen_arr[] = $row->berechtigung_kurzbz;
		}
		foreach ($berechtigung->result as $row): ?>
				<OPTION value="<?php echo $row->berechtigung_kurzbz ?>"
						<?php echo array_search($row->berechtigung_kurzbz,$berechtigungen_arr)!==false ? 'disabled' : '' ?>>
					<?php echo $row->berechtigung_kurzbz ?>
				</OPTION>
		<?php endforeach; ?>
			</SELECT>
			<input type="text" value="suid" size="4" name="art">
			<input type="submit" name="save" value="Hinzufügen">
		</form>

		<table id="t1" class="tablesorter">
			<thead>
				<tr>
					<th>Kurzbz</th>
					<th>Art</th>
					<th>Beschreibung</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
		<?php

		foreach($berechtigungen->result as $rolle): ?>
				<tr>
					<td><?php echo $rolle->berechtigung_kurzbz ?></td>
					<td><?php echo $rolle->art ?></td>
					<td><?php echo $rolle->beschreibung ?></td>
					<td>
						<a href="<?php echo basename(__FILE__) ?>?delete=1&rolle_kurzbz=<?php echo $rolle->rolle_kurzbz ?>&berechtigung_kurzbz=<?php echo $rolle->berechtigung_kurzbz ?>">
							entfernen
						</a>
					</td>
				</tr>
		<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}
	else
	{
		$save = filter_input(INPUT_POST, 'save');
		$edit = filter_input(INPUT_POST, 'edit');

		if(isset($save))
		{
			$kurzbz = filter_input(INPUT_POST, 'kurzbz');
			$beschreibung = filter_input(INPUT_POST, 'beschreibung');

			if(isset($kurzbz) && isset($beschreibung))
			{
				$berechtigung = new berechtigung();
				$berechtigung->rolle_kurzbz = $kurzbz;
				$berechtigung->beschreibung = $beschreibung;
				$berechtigung->new = true;

				if($berechtigung->saveRolle())
				{
					echo 'Daten wurden gespeichert';
				}
				else
				{
					echo 'Fehler beim Speichern:'.$berechtigung->errormsg;
				}
			}
			else
			{
				echo 'Zum Speichern der Daten muss die kurzbz und die Beschreibung angegeben werden';
			}
		}

		$kurzbz = filter_input(INPUT_GET, 'kurzbz');

		if(isset($delete) && isset($kurzbz))
		{
			$berechtigung = new berechtigung();
			if($berechtigung->deleteRolle($kurzbz))
				echo 'Rolle wurde entfernt';
			else
				echo 'Fehler beim Löschen:'.$berechtigung->errormsg;
		}

		if(isset($edit))
		{
			$beschreibung = filter_input(INPUT_POST, 'beschreibung');

			$berechtigung = new berechtigung();
			$berechtigung->rolle_kurzbz = $kurzbz;
			$berechtigung->beschreibung = $beschreibung;
			$berechtigung->saveRolle(false);
		}

		//Tabelle mit Rollen anzeigen
		$berechtigung = new berechtigung();
		$berechtigung->getRollen(); ?>

		<h3>Rollen:</h3>
		<table id="t2" class="tablesorter">
			<thead>
				<tr>
					<th>Kurzbz</th>
					<th>Beschreibung</th>
					<th colspan="2">Aktion</th>
				</tr>
			</thead>
			<tbody>

			<?php
			$edit = filter_input(INPUT_GET, 'edit');
			$kurzbz = filter_input(INPUT_GET, 'kurzbz');
			foreach($berechtigung->result as $rolle):
				if($edit && $rolle->rolle_kurzbz == $kurzbz)
				{
					$rolle_edit = $rolle;
				}
				?>
				<tr>
					<td>
						<a href="<?php echo basename(__FILE__) ?>?kurzbz=<?php echo $rolle->rolle_kurzbz ?>&edit=1">
							<?php echo $rolle->rolle_kurzbz ?>
						</a>
					</td>
					<td><?php echo $rolle->beschreibung ?></td>
					<td>
						<a href="<?php echo basename(__FILE__) ?>?rolle_kurzbz=<?php echo $rolle->rolle_kurzbz ?>">
							Berechtigungen zuordnen
						</a>
					</td>
					<td>
						<a href="<?php echo basename(__FILE__) ?>?kurzbz=<?php echo $rolle->rolle_kurzbz ?>&delete=1" onclick="return confdel()">
							Rolle löschen
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<br>
			<?php
			if($edit):
				?>
				<form method="POST">
					Kurzbz: <input type="text" name="kurzbz" value="<?php echo $rolle_edit->rolle_kurzbz ?>" disabled />
					Beschreibung: <input type="text" name="beschreibung" value="<?php echo $rolle_edit->beschreibung ?>" />
					&nbsp;<input type="submit" name="edit" value="Speichern" />
				</form>
		<a href="<?php echo basename(__FILE__) ?>">Neue Rolle anlegen</a>
			<?php else: ?>
				<form method="POST">
					Kurzbz: <input type="text" name="kurzbz" value="" />
					Beschreibung: <input type="text" name="beschreibung" value="" />
					&nbsp;<input type="submit" name="save" value="Anlegen" />
				</form>
			<?php endif; ?>
	<?php } ?>

	</body>
</html>
