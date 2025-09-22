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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Karl Burkhart 			< burkhart@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/preincoming.class.php');
require_once('../../include/datum.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$datum_obj = new datum();
$message='';
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Incoming</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
		<link rel="stylesheet" href="../../skin/jquery.css" type="text/css"/>
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
					widgets: ["zebra"]
				}); 
			} 
		); 
			
	</script> 
	</head>
	<body>
	<h2>Incoming Verwaltung</h2>
	';

if(!$rechte->isBerechtigt('inout/incoming', null, 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$filter = isset($_POST['filter'])?$_POST['filter']:null;
$von = isset($_POST['von'])?$_POST['von']:null;
$bis = isset($_POST['bis'])?$_POST['bis']:null;

if(isset($_POST['aktiv']))
{
	switch($_POST['aktiv'])
	{
		case 'true': 
			$aktiv=true; 
			break;
		case 'false': 
			$aktiv=false; 
			break;
		default: 
			$aktiv=null; 
			break;
	}
}
else
	$aktiv=true;

if(isset($_POST['uebernommen']))
{
	switch($_POST['uebernommen'])
	{
		case 'true': 
			$uebernommen=true; 
			break;
		case 'false': 
			$uebernommen=false; 
			break;
		default: 
			$uebernommen=null; 
			break;
	}
}
else
	$uebernommen=false;
	
//Suchfilter
echo '
<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
	<input type="hidden" name="action" value="search">
	<table>
		<tr>
			<td>Aktiv</td>
			<td>
				<SELECT name="aktiv">
					<OPTION value="">-</OPTION>
					<OPTION value="true" '.($aktiv===true?'selected':'').'>Ja</OPTION>
					<OPTION value="false" '.($aktiv===false?'selected':'').'>Nein</OPTION>
				</SELECT>
			</td>
			<td>Übernommen</td>
			<td>
				<SELECT name="uebernommen">
					<OPTION value="">-</OPTION>
					<OPTION value="true" '.($uebernommen===true?'selected':'').'>Ja</OPTION>
					<OPTION value="false" '.($uebernommen===false?'selected':'').'>Nein</OPTION>
				</SELECT>
			</td>
			<td>Von</td>
			<td>
				<input type="text" size="10" id="von" name="von" value="'.$von.'">
				<script type="text/javascript">
					$(document).ready(function() 
					{ 
					    $( "#von" ).datepicker($.datepicker.regional["de"]);
					});
				</script>
			</td>
			<td>Bis</td>
			<td>
				<input type="text" size="10" name="bis" id="bis" value="'.$bis.'">
				<script type="text/javascript">
					$(document).ready(function() 
					{ 
					    $( "#bis" ).datepicker($.datepicker.regional["de"]);
					});
				</script>
			</td>
			<td>Name</td>
			<td><input type="text" name="filter" value="'.$filter.'"></td>
			<td><input type="submit" value="Anzeigen"/></td>
			<td width="100%" align="right"><a href="incoming_lehrveranstaltungen.php?method=lehrveranstaltungen" target="incoming_detail">Übersicht Lehrveranstaltungen</a></td>
		</tr>
	</table>
</form>
';
if($von!='')
	$von = $datum_obj->formatDatum($von);
if($bis!='')
	$bis = $datum_obj->formatDatum($bis);

$inc = new preincoming();
if(!$inc->getPreincoming($filter, $aktiv, $von, $bis, $uebernommen))
	$message = '<span class="error">'.$inc->errormsg.'</span>';
	
echo $message;
$datum = new datum();
echo '
<table id="myTable" class="tablesorter">
	<thead>
		<tr>
			<th>ID</th>
			<th>Vorname</th>
			<th>Nachname</th>
			<th>Von</th>
			<th>Bis</th>
            <th>Registriert</th>
			<th></th>
		</tr>
	</thead>
	<tbody>';
foreach($inc->result as $row)
{
	echo "\n";
	echo '<tr>';
	echo '<td>'.$row->preincoming_id.'</td>';
	echo '<td>'.$row->vorname.'</td>';
	echo '<td>'.$row->nachname.'</td>';
	echo '<td>'.$row->von.'</td>';
	echo '<td>'.$row->bis.'</td>';  
    echo '<td>'.$datum->formatDatum($row->insertamum, 'Y-m-d').'</td>';
	echo '<td><a href="incoming_detail.php?preincoming_id='.$row->preincoming_id.'" target="incoming_detail">Details</a></td>';
	echo '</tr>';
}
echo '
	</tbody>
</table>';

echo '</body>';
echo '</html>';
?>