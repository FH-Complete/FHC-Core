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
 * Authors: Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/ampel.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/datum.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
	
if(!$rechte->isBerechtigt('basis/ampel'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$datum_obj = new datum();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Ampel</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script> 
	<script type="text/javascript">
	
		$(document).ready(function() 
			{ 
			    $("#myTable").tablesorter(
				{
					sortList: [[2,0]],
					widgets: [\'zebra\']
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
<h2>Ampel &Uuml;bersicht</h2>
<div style="text-align:right">
	<a href="ampel_details.php?action=new" target="detail_ampel">Neu</a>
</div>';
if(isset($_GET['action']) && $_GET['action']=='delete')
{
	if(!$rechte->isBerechtigt('basis/ampel', null, 'suid'))
		die('Sie haben keine Berechtigung fuer diese Seite');
	
	if(!isset($_GET['ampel_id']))
		die('Fehlender Parameter Statistik');
	
	$ampel = new ampel();
	if($ampel->delete($_GET['ampel_id']))
		echo '<span class="ok">Eintrag wurde erfolgreich gelöscht</span>';
	else
		echo '<span class="error">'.$ampel->errormsg.'</span>';
}

$ampel = new ampel();

if(!$ampel->getAll())
	die($ampel->errormsg);

echo '<table class="tablesorter" id="myTable">
	<thead>
		<tr>
			<th>ID</th>
			<th>Kurzbz</th>
			<th>Deadline</th>
			<th>Vorlaufzeit</th>
			<th>Verfallszeit</th>
			<th>Verpflichtend</th>
			<th colspan="3">Aktion</th>
		</tr>
	</thead>
	<tbody>';

foreach($ampel->result as $row)
{
	echo '<tr>';
	echo '<td><a href="ampel_details.php?action=update&ampel_id=',$row->ampel_id,' " target="detail_ampel">',$row->ampel_id,'</a></td>';
	echo '<td>',$row->kurzbz,'</td>';
	echo '<td>',$datum_obj->formatDatum($row->deadline,'d.m.Y'),'</td>';
	echo '<td>',$row->vorlaufzeit,'</td>';
	echo '<td>',$row->verfallszeit,'</td>';
	echo '<td>',($row->verpflichtend=='t'?'Ja':'Nein'),'</td>';
	echo '<td><a href="ampel_details.php?action=update&ampel_id=',$row->ampel_id,' " target="detail_ampel">bearbeiten</a></td>';
	echo '<td><a href="ampel_details.php?action=copy&ampel_id=',$row->ampel_id,' " target="detail_ampel">kopieren</a></td>';
	echo '<td><a href="ampel_uebersicht.php?action=delete&ampel_id=',$row->ampel_id,' " onclick="return confdel()">entfernen</a></td>';
	echo '</tr>';
}
echo '</tbody>
</table>
</body>
</html>';
?>