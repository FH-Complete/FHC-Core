<?php
/* Copyright (C) 2011 FH Technikum Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>
 *          Robert Hofer <robert.hofer@technikum-wien.at>
 */
/**
 * Statistik Uebersichtsseite
 * - zeigt die Beschreibung einer Statistik ein
 * - Link zum Starten der Statistik
 * - Eventuelle Parametereingabe für die Statistik
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/statistik.class.php');
require_once('../../include/filter.class.php');
require_once('../../include/functions.inc.php');

$statistik_kurzbz = filter_input(INPUT_GET, 'statistik_kurzbz');

$statistik = new statistik();

if(!$statistik->load($statistik_kurzbz))
{
	die($statistik->errormsg);
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Statistik</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css"/>
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css"/>
		<link rel="stylesheet" href="../../vendor/components/jqueryui/themes/base/jquery-ui.min.css" />
		<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script>
			$(function() {
				$.datepicker.setDefaults({dateFormat: "yy-mm-dd"});
			});
		</script>
	</head>
	<body>

		<h2>Report - <?php echo $statistik->bezeichnung ?></h2>
		<?php
		//Beschreibung zu der Statistik anzeigen
		if($statistik->content_id): ?>
			<a href="#" onclick="window.open('../../cms/content.php?content_id=<?php echo $statistik->content_id ?>', 'Beschreibung', 'width=600,height=600, scrollbars=yes');">
				Beschreibung anzeigen
			</a><br><br>
		<?php endif;
$variablenstring='';
$action='';
if($statistik->url)
{
	$action = $statistik->url;
	$variablenstring = $statistik->url;
}
elseif($statistik->sql!='')
{
	$action = 'statistik_sql.php?statistik_kurzbz='.$statistik_kurzbz;
	$variablenstring = $statistik->sql;
}

$vars = $statistik->parseVars($variablenstring); ?>
		<script type="text/javascript">
			function doit()
			{
				<?php if($statistik->url): ?>
					var action='<?php echo $action ?>';

					<?php foreach ($vars as $var): ?>
						action = action.replace('$<?php echo $var ?>', document.getElementById('<?php echo $var ?>').value);
					<?php endforeach; ?>
					parent.detail_statistik.location.href=action;
					return false;
				<?php else: ?>
					return true;
				<?php endif; ?>
			}
		</script>
		<form action="<?php echo $action ?>" method="POST" target="detail_statistik" onsubmit="return doit();">
			<table>

			<?php
			// Filter parsen
			$fltr=new filter();
			$fltr->loadAll(); ?>

				<tr>
					<?php foreach($vars as $var):
						if ($fltr->isFilter($var)): ?>
							<td><?php echo $var ?></td><td><?php echo $fltr->getHtmlWidget($var) ?></td>
						<?php else: ?>
							<td><?php echo $var ?></td><td><input type="text" id="<?php echo $var ?>" name="<?php echo $var ?>" value=""></td>
						<?php endif;
					endforeach; ?>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" value="Anzeigen"></td>
				</tr>
			</table>
		</form>
	</body>
</html>
