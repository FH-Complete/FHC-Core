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
 *          Karl Burkhart 		< burkhart@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/statistik.class.php');
require_once('../../include/benutzerberechtigung.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/statistik', null, 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite');

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Statistik</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">';

	include('../../include/meta/jquery.php');
	include('../../include/meta/jquery-tablesorter.php');

echo '
	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
	<script type="text/javascript">

		$(document).ready(function()
			{
			    $("#myTable").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra", "filter", "stickyHeaders"]
				});
			}
		);

		function confdel()
		{
			return confirm("Wollen Sie diesen Eintrag wirklich löschen?");
		}
		</script>
</head>
<body>
<h2>Statistik &Uuml;bersicht</h2>
<div style="text-align:right">
	<a href="statistik_details.php?action=new" target="detail_statistik">Neu</a>
</div>';
if(isset($_GET['action']) && $_GET['action']=='delete')
{
	if(!$rechte->isBerechtigt('basis/statistik', null, 'suid'))
		die('Sie haben keine Berechtigung fuer diese Seite');

	if(!isset($_GET['statistik_kurzbz']))
		die('Fehlender Parameter Statistik');

	$statistik = new statistik();
	if($statistik->delete($_GET['statistik_kurzbz']))
		echo '<span class="ok">Eintrag wurde erfolgreich gelöscht</span>';
	else
		echo '<span class="error">'.$statistik->errormsg.'</span>';
}

$statistik = new statistik();

if(!$statistik->getAll())
	die($statistik->errormsg);

echo '<table class="tablesorter" id="myTable">
	<thead>
		<tr>
			<th>Kurzbz</th>
			<th>Bezeichnung</th>
			<th>Gruppe</th>
			<th>Publish</th>
			<th>ContentID</th>
			<th colspan="2">Aktion</th>
		</tr>
	</thead>
	<tbody>';

foreach($statistik->result as $row)
{
	echo '<tr>';
	echo '<td><a href="statistik_details.php?action=update&statistik_kurzbz=',$row->statistik_kurzbz,' " target="detail_statistik">',$row->statistik_kurzbz,'</a></td>';
	echo '<td>',$row->bezeichnung,'</td>';
	echo '<td>',$row->gruppe,'</td>';
	echo '<td>',($row->publish?'Ja':'Nein'),'</td>';
	echo '<td>',$row->content_id,'</td>';
	echo '<td><a href="statistik_details.php?action=update&statistik_kurzbz=',$row->statistik_kurzbz,' " target="detail_statistik">bearbeiten</a></td>';
	echo '<td><a href="statistik_uebersicht.php?action=delete&statistik_kurzbz=',$row->statistik_kurzbz,' " onclick="return confdel()">entfernen</a></td>';
	echo '</tr>';
}
echo '</tbody>
</table>
</body>
</html>';
?>
