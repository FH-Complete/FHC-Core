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

isset($_GET['von']) ? $von = $_GET['von'] : $von = $studiensemester->begin->start;
isset($_GET['bis']) ? $bis = $_GET['bis'] : $bis = $studiensemester->ende->ende;

$buchung->getBuchungPerson($benutzer->person_id);

// Ausgabe
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title><?php echo $p->t('lvaliste/titel'); ?></title>
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	<link rel="stylesheet" href="../../../skin/jquery.css" type="text/css"/>
	<script type="text/javascript" src="../../../include/js/jquery.js"></script>
	<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>
	<script language="Javascript">
	<!--
	$(document).ready(function() 
	{ 
		$("#t1").tablesorter(
		{
			widgets: ["zebra"]
		});
	});
	-->
	</script>
</head>
<body id="inhalt">
<H1><?php echo $p->t('buchungen/titel'); ?></H1>

<table id="t1" class="tablesorter">
	<thead>
		<tr>
			<th><?php echo $p->t('buchungen/buchungsdatum'); ?></th>
			<th><?php echo $p->t('buchungen/buchungstext'); ?></th>
			<th><?php echo $p->t('buchungen/betrag'); ?></th>
			<th><?php echo $p->t('buchungen/buchgstyp'); ?></th>
		</tr>
	</thead>
	<?php
	foreach($buchung->result as $row)
	{
		echo '<tr>';
		echo '<td>' . $datum->formatDatum($row->buchungsdatum, 'd.m.Y') . '</td>';
		echo '<td>' . $row->buchungstext . '</td>';
		echo '<td>' . $row->betrag . '</td>';
		echo '<td>' . $row->buchungstyp_kurzbz . '</td>';
		echo '</tr>';
		
		$summe += $row->betrag;
	}
	?>
</table>
</body>
</html>