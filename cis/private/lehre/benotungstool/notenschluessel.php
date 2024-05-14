<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 */
require_once('../../../../config/cis.config.inc.php');
require_once('../../../../config/global.config.inc.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/notenschluessel.class.php');
require_once('../../../../include/phrasen.class.php');

$uid = get_uid();

$sprache = getSprache();
$p = new phrasen($sprache);

if(isset($_GET['lehrveranstaltung_id']))
	$lehrveranstaltung_id = $_GET['lehrveranstaltung_id'];
else
	die('Fehlerhafte Parameteruebergabe');

if(isset($_GET['stsem']))
	$studiensemester_kurzbz = $_GET['stsem'];
else
	die('Fehlerhafte Parameteruebergabe');


echo '<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../../../skin/style.css.php" rel="stylesheet" type="text/css">
    <link href="../../../../skin/jquery.css" rel="stylesheet"  type="text/css"/>
    <link href="../../../../skin/tablesort.css" rel="stylesheet"  type="text/css"/>
    <link href="../../../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../../../include/js/jquery.ui.datepicker.translation.js"></script>
	<title>Grade</title>
</head>
<body>';
$notenschluessel =  new notenschluessel();
if($kurzbz = $notenschluessel->getNotenschluessel($lehrveranstaltung_id, $studiensemester_kurzbz))
{
	if($notenschluessel->loadAufteilung($kurzbz))
	{
		echo '<table id="t1" class="tablesorter">
				<thead>
				<tr>
					<th>'.$p->t('benotungstool/note').'</th>
					<th>'.$p->t('benotungstool/punkte').'</th>
				</tr>
				</thead>
				<tbody>';
		foreach($notenschluessel->result as $row)
		{
			echo '<tr>';
			echo '<td>'.$row->notenbezeichnung.'</td>';
			echo '<td>'.$row->punkte.'</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
}
else
{
	echo $p->t('gesamtnote/keinNotenschluesselvorhanden');
}
echo '</body></html>';
?>
