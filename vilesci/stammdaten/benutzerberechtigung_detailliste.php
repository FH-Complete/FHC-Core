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
require_once('../../include/funktion.class.php');
require_once('../../include/organisationseinheit.class.php');
require_once('../../include/wawi_kostenstelle.class.php');
require_once('../../include/benutzer.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$uid = isset($_GET['uid']) && $_GET['uid']!='' ? $_GET['uid'] : die('UID muss übergeben werden');
$benutzer = new benutzer();
$benutzer->load($uid);

if(!$rechte->isBerechtigt('basis/berechtigung'))
	die('Sie habe keine Rechte um diese Seite anzuzeigen');

?>
<html>
	<head>
		<title>Detaillierte Berechtigungsliste</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link href="../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
		<?php
		include('../../include/meta/jquery.php');
		include('../../include/meta/jquery-tablesorter.php');
		?>
		<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
		<script>
			$(document).ready(function() {
				$("#t1").tablesorter(
					{
						sortList: [[0, 0], [1, 0], [2, 0]],
						widgets: ["zebra", "filter", "stickyHeaders"],
						widgetOptions: {
							filter_functions: {
								// Add select menu to this column
								8: {
									"Aktive/Wartende": function (e, n, f, i, $r, c, data) {
										return e == 'Aktiv' || e == 'Wartend';
									},
									"Aktive": function (e, n, f, i, $r, c, data) {
										return /Aktiv/.test(e);
									},
									"Wartende": function (e, n, f, i, $r, c, data) {
										return /Wartend/.test(e);
									},
									"Inaktive": function (e, n, f, i, $r, c, data) {
										return /Inaktiv/.test(e);
									}
								}
							}
						}
					});
			});
		</script>
	</head>

	<body class="background_main">
		<h2>Detaillierte Berechtigungsliste von <?php echo $benutzer->vorname.' '.$benutzer->nachname ?></h2>

	<?php
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);

	$funktionsArray = array();
	$funktionen = new funktion();
	$funktionen->getAll();

	foreach ($funktionen->result as $item)
	{
		$funktionsArray[$item->funktion_kurzbz] = $item->beschreibung;
	}

	$kostenstelleArray = array();
	$kostenstellen = new wawi_kostenstelle();
	$kostenstellen->getAll();

	foreach ($kostenstellen->result as $item)
	{
		$kostenstelleArray[$item->kostenstelle_id] = $item->bezeichnung.' ('.$item->kostenstelle_id.')';
	}

	$oeArray = array();
	$oes = new organisationseinheit();
	$oes->getAll();

	foreach ($oes->result as $item)
	{
		$oeArray[$item->oe_kurzbz] = $item->organisationseinheittyp_kurzbz.' '.$item->bezeichnung;
	}
	$heute = strtotime(date('Y-m-d'));

	echo '<table id="t1" class="tablesorter">
		<thead><tr>
		<th>Funktion</th>
		<th>Rolle</th>
		<th>Recht</th>
		<th>Art</th>
		<th>Organisationseinheit</th>
		<th>Kostenstelle</th>
		<th>Gültig ab</th>
		<th>Gültig bis</th>
		<th data-value="Aktive/Wartende">Status</th>
		</tr></thead><tbody>';
	foreach ($rechte->berechtigungen AS $key)
	{

		if ($key->ende!='' && strtotime($key->ende) < $heute)
		{
			$titel="Inaktiv";
		}
		elseif ($key->start!='' && strtotime($key->start) > $heute)
		{
			$titel="Wartend";
		}
		else
		{
			$titel="Aktiv";
		}
		echo '<tr>';
		echo '<td>'.($key->funktion_kurzbz != '' ? $funktionsArray[$key->funktion_kurzbz] : '').'</td>';
		echo '<td>'.($key->rolle_kurzbz != '' ? $key->rolle_kurzbz : '').'</td>';
		echo '<td>'.($key->berechtigung_kurzbz != '' ? $key->berechtigung_kurzbz : '').'</td>';
		echo '<td>'.($key->art != '' ? $key->art : '').'</td>';
		echo '<td>'.($key->oe_kurzbz != '' ? $oeArray[$key->oe_kurzbz]   : '').'</td>';
		echo '<td>'.($key->kostenstelle_id != '' ? $kostenstelleArray[$key->kostenstelle_id] : '').'</td>';
		echo '<td>'.($key->start != '' ? $key->start : '').'</td>';
		echo '<td>'.($key->ende != '' ? $key->ende : '').'</td>';
		echo '<td>'.$titel.'</td>';
		echo '</tr>';
	}
	echo '</tbody></table>';

	?>

	</body>
</html>
