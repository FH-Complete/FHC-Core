<?php
/* Copyright (C) 2014 Technikum-Wien
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
 * Authors: Nikolaus Krondraf <nikolaus.krondraf@technikum-wien.at>
 */
/*
 * Erstellt eine Liste mit den Buchungen eines Mitarbeiters
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../config/global.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/buchung.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/datum.class.php');

if (!$db = new basis_db())
  die('Fehler beim Oeffnen der Datenbankverbindung');

$summe = 0;
$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('buchung/mitarbeiter'))
	die('Sie haben keine Berechtigung für diese Seite');

$benutzer = new benutzer($user);
$studiensemester = new studiensemester();
$buchung = new buchung();
$datum = new datum();
$p = new phrasen(getSprache());

// Beginn und Ende des aktuellen Semesters ermitteln
$studiensemester->getTimestamp($studiensemester->getakt());

!empty($_GET['von']) ? $von = $_GET['von'] : $von = date('d.m.Y', $studiensemester->begin->start);
!empty($_GET['bis']) ? $bis = $_GET['bis'] : $bis = date('d.m.Y', $studiensemester->ende->ende);

$options['von'] = $datum->formatDatum($von);
$options['bis'] = $datum->formatDatum($bis);
$buchung->getBuchungPerson($benutzer->person_id, $options);

// Ausgabe
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title><?php echo $p->t('buchungen/titel'); ?></title>
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	<link rel="stylesheet" href="../../../skin/jquery.css" type="text/css"/>
	<link rel="stylesheet" type="text/css" href="../../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../../vendor/jquery/sizzle/sizzle.js"></script>
	<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>
	<script language="Javascript">
	<!--
	$(document).ready(function() 
	{ 
		$("#t1").tablesorter(
		{
			sortList: [[0,0]],
			widgets: ["zebra"]
		});
		
		$("#von, #bis").datepicker($.datepicker.regional["de"]);
	});
	-->
	</script>
</head>
<body id="inhalt">
<H1><?php echo $p->t('buchungen/titel'); ?></H1>

<form method="get" action="mitarbeiter_buchung.php">
	von <input type="text" id="von" name="von" value="<?php echo $von; ?>" />
	bis <input type="text" id="bis" name="bis" value="<?php echo $bis; ?>" />
	<input type="submit" value="filtern" />
</form>

<table id="t1" class="tablesorter">
	<thead>
		<tr>
			<th><?php echo $p->t('buchungen/buchungsdatum'); ?></th>
			<th><?php echo $p->t('buchungen/buchungstext'); ?></th>
			<th><?php echo $p->t('buchungen/betrag'); ?></th>
			<th><?php echo $p->t('buchungen/buchgstyp'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach($buchung->result as $row)
	{
		echo '<tr>';
		echo '<td>' . $datum->formatDatum($row->buchungsdatum, 'd.m.Y') . '</td>';
		echo '<td>' . $row->buchungstext . '</td>';
		echo '<td>' . str_replace('.', ',', $row->betrag) . '</td>';
		echo '<td>' . $row->buchungstyp_kurzbz . '</td>';
		echo '</tr>';
		
		$summe += $row->betrag;
	}
	?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="2" align="right"><b>Summe</b></td>
			<th class="header"><?php echo number_format($summe, 2, ',', '') ?></th>
		</tr>
	</tfoot>
</table>
</body>
</html>