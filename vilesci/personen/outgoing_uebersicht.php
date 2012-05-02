<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Karl Burkhart 			< burkhart@technikum-wien.at >
 * 
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/preoutgoing.class.php');
require_once('../../include/benutzer.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$message='';
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Outgoing</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
		<link rel="stylesheet" href="../../skin/jquery.css" type="text/css"/>
		<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<script type="text/javascript" src="../../include/js/jquery.js"></script>
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
	<h2>Outgoing Verwaltung</h2>
	';

/*if(!$rechte->isBerechtigt('inout/outgoing', null, 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite');
*/

$out = new preoutgoing();
if(!$out->getAll())
	$message = '<span class="error">'.$inc->errormsg.'</span>';
	
echo $message;
echo '
<table id="myTable" class="tablesorter">
	<thead>
		<tr>
			<th>ID</th>
			<th>Vorname</th>
			<th>Nachname</th>
			<th>Von</th>
			<th>Bis</th>
			<th></th>
		</tr>
	</thead>
	<tbody>';
foreach($out->result as $row)
{
    $user = new benutzer(); 
    $user->load($row->uid);
	echo "\n";
	echo '<tr>';
	echo '<td>'.$row->preoutgoing_id.'</td>';
	echo '<td>'.$user->vorname.'</td>';
	echo '<td>'.$user->nachname.'</td>';
	echo '<td>'.$row->dauer_von.'</td>';
	echo '<td>'.$row->dauer_bis.'</td>';
	echo '<td><a href="outgoing_detail.php?preoutgoing_id='.$row->preoutgoing_id.'" target="outgoing_detail">Details</a></td>';
	echo '</tr>';
}
echo '
	</tbody>
</table>';

echo '</body>';
echo '</html>';
?>