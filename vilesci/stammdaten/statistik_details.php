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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Karl Burkhart		< burkhart@technikum-wien.at >
 */
/**
 * Seite zur Wartung der Statistiken
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/statistik.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/berechtigung.class.php');

if(!$db = new basis_db())
{
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
}

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/statistik'))
	die('Sie haben keine Berechtigung fuer diese Seite');
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Statistik - Details</title>
		<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	</head>
	<body>

		<?php
		$statistik_kurzbz = filter_input(INPUT_GET, 'statistik_kurzbz');
		$statistik = new statistik();

		if($statistik_kurzbz)
		{
			$exists = $statistik->load($statistik_kurzbz);
		}
		else
		{
			$statistik_kurzbz = filter_input(INPUT_POST, 'statistik_kurzbz');
			$exists = false;
		}

		if(isset($_POST['save']))
		{
			$statistik_kurzbz_orig = (isset($_POST['statistik_kurzbz_orig']) ? $_POST['statistik_kurzbz_orig'] : die('Statistik_kurzbz_orig fehlt'));
			$bezeichnung = (isset($_POST['bezeichnung']) ? $_POST['bezeichnung'] : die('Bezeichnung fehlt'));
			$url = (isset($_POST['url']) ? $_POST['url'] : die('URL fehlt'));
			$sql = (isset($_POST['sql']) ? $_POST['sql'] : die('SQL fehlt'));
			$gruppe = (isset($_POST['gruppe']) ? $_POST['gruppe'] : die('Gruppe fehlt'));
			$content_id = (isset($_POST['content_id']) ? $_POST['content_id'] : die('ContentID fehlt'));
			$php = (isset($_POST['php']) ? $_POST['php'] : die('PHP fehlt'));
			$r = (isset($_POST['r']) ? $_POST['r'] : die('R fehlt'));
			$publish = (isset($_POST['publish']) ? true : false);
			$berechtigung_kurzbz = (isset($_POST['berechtigung_kurzbz']) ? $_POST['berechtigung_kurzbz'] : die('Berechtigungkurzbz fehlt'));
			$preferences = (isset($_POST['preferences']) ? $_POST['preferences'] : die('preferences fehlt'));

			if(!$exists)
			{
				$statistik->insertamum = date('Y-m-d H:i:s');
				$statistik->insertvon = $user;
				$statistik->new = true;
			}

			$statistik->statistik_kurzbz = $statistik_kurzbz;
			$statistik->statistik_kurzbz_orig = $statistik_kurzbz_orig;
			$statistik->bezeichnung = $bezeichnung;
			$statistik->url = $url;
			$statistik->sql = $sql;
			$statistik->gruppe = $gruppe;
			$statistik->content_id = $content_id;
			$statistik->php = $php;
			$statistik->r = $r;
			$statistik->publish = $publish;
			$statistik->updateamum = date('Y-m-d H:i:s');
			$statistik->updatevon = $user;
			$statistik->berechtigung_kurzbz = $berechtigung_kurzbz;
			$statistik->preferences = $preferences;

			$success = $statistik->save();

			if($success):
				?>
				<span class="ok">Daten erfolgreich gespeichert</span>
				<script type='text/javascript'>
					parent.uebersicht_statistik.location.href = 'statistik_uebersicht.php';
				</script>
			<?php else: ?>
				<span class="error"><?php echo $statistik->errormsg ?></span>
			<?php
			endif;
		}

		$preferences = trim($statistik->preferences);

		if(empty($preferences))
		{
			$statistik->preferences = <<<EOT
// Folgendes Objekt wird als "options"-Parameter an den Pivottable übergeben:
{
	rows: [],
	cols: [],
	rendererName: "Table",
	aggregatorName: "Count",
	vals: []
}
EOT;
		}
		?>
		<form method="POST">
			<fieldset>
				<?php if($statistik->new === false): ?>
					<legend>Bearbeiten - <?php echo $statistik_kurzbz ?></legend>
				<?php else: ?>
					<legend>Neu</legend>
				<?php endif; ?>
				<input type="hidden" name="statistik_kurzbz_orig" value="<?php echo $statistik->statistik_kurzbz ?>">
				<table>
					<tr>
						<td>Kurzbz</td>
						<td><input type="text" name="statistik_kurzbz" size="50" maxlength="64" value="<?php echo $statistik->statistik_kurzbz ?>"></td>
						<td></td>
						<td>Gruppe</td>
						<td><input type="text" name="gruppe" value="<?php echo $statistik->gruppe ?>"></td>
					</tr>
					<tr>
						<td>Bezeichnung</td>
						<td><input type="text" name="bezeichnung" size="80" maxlength="256" value="<?php echo $statistik->bezeichnung ?>"></td>
						<td></td>
						<td>ContentID</td>
						<td><input type="text" name="content_id" value="<?php echo $statistik->content_id ?>"></td>
					</tr>
					<tr>
						<td>URL</td>
						<td><input type="text" name="url" size="80" maxlength="512" value="<?php echo $statistik->url ?>"></td>
						<td></td>
						<td>Berechtigung</td>
						<td>
							<?php
							$berechtigung = new berechtigung();
							$berechtigung->getBerechtigungen();
							?>
							<select name="berechtigung_kurzbz">
								<option value="">-- keine Auswahl --</option>
								<?php foreach($berechtigung->result as $row): ?>
									<option value="<?php echo $row->berechtigung_kurzbz ?>"
											<?php echo ($row->berechtigung_kurzbz == $statistik->berechtigung_kurzbz ? 'selected' : '') ?>>
												<?php echo $row->berechtigung_kurzbz ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<td rowspan="3">SQL</td>
						<td rowspan="3"><textarea name="sql" cols="60" rows="5"><?php echo $statistik->sql ?></textarea></td>
						<td></td>
						<td>R</td>
						<td><input type="text" name="r" value="<?php echo $statistik->r ?>"></td>
					</tr>
					<tr valign="top">
						<td></td>
						<td>PHP</td>
						<td><input type="text" name="php" value="<?php echo $statistik->php ?>"></td>
					</tr>
					<tr valign="top">
						<td></td>
						<td>Publish</td>
						<td><input type="checkbox" name="publish" <?php echo $statistik->publish ? 'checked="checked"' : '' ?>></td>
					</tr>

					<tr valign="top">
						<td>Preferences</td>
						<td><textarea name="preferences" cols="60" rows="5"><?php echo $statistik->preferences ?></textarea></td>
						<td></td>
						<td></td>
						<td><input type="submit" value="Speichern" name="save"></td>
					</tr>
				</table>
			</fieldset>
		</form>
	</body>
</html>
