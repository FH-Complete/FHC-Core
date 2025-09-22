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

$method=isset($_POST['action'])?$_POST['action']:'';

if($method == 'search')
{
    $datum=new datum(); 
    $out = new preoutgoing; 
    $von = $datum->formatDatum($_REQUEST['von'], 'Y-m-d');
    $bis = $datum->formatDatum($_REQUEST['bis'], 'Y-m-d');
    
    $filter_name = $_POST['filter_name'];
    $status = $_POST['select_status'];
    
    if(!$out->getOutgoingFilter($filter_name, $von, $bis, $status))
        $message = '<span class="error">'.$out->errormsg.'</span>';
}
else
{
    $out = new preoutgoing();
    if(!$out->getAll())
        $message = '<span class="error">'.$out->errormsg.'</span>';
}

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
	<h2>Outgoing Verwaltung</h2>
	';

if(!$rechte->isBerechtigt('inout/outgoing', null, 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$von = isset($_REQUEST['von'])?$_REQUEST['von']:'';
$bis = isset($_REQUEST['bis'])?$_REQUEST['bis']:'';
$filter = isset($_REQUEST['filter_name'])?$_REQUEST['filter_name']:'';
$status = isset($_REQUEST['select_status'])?$_REQUEST['select_status']:'';

echo '
<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
	<input type="hidden" name="action" value="search">
	<table>
		<tr>
			<td>Von: </td>
			<td>
				<input type="text" size="10" id="von" name="von" value="'.$von.'">
				<script type="text/javascript">
					$(document).ready(function() 
					{ 
					    $( "#von" ).datepicker($.datepicker.regional["de"]);
					});
				</script>
			</td>
			<td>Bis: </td>
			<td>
				<input type="text" size="10" name="bis" id="bis" value="'.$bis.'">
				<script type="text/javascript">
					$(document).ready(function() 
					{ 
					    $( "#bis" ).datepicker($.datepicker.regional["de"]);
					});
				</script>
			</td>
			<td>Name: </td>
			<td><input type="text" name="filter_name" value="'.$filter.'"></td>
            <td>Status: </td>';
$preoutgoing = new preoutgoing(); 
$preoutgoing->getAllStatiKurzbz();
echo '<td><SELECT name="select_status">
        <option value="">-- alle -- </option>';
foreach($preoutgoing->stati as $status_filter)
{
    $selected = '';
    if($status_filter->preoutgoing_status_kurzbz == $status)
        $selected ='selected'; 
    echo'<option value="'.$status_filter->preoutgoing_status_kurzbz.'" '.$selected.'>'.$status_filter->preoutgoing_status_kurzbz.'</option>';
}
echo'</SELECT></td>';

$aktOutgoing = new preoutgoing();
$aktOutgoing->getAktuellOutgoing(); 
$mailto_link = 'mailto:';
foreach($aktOutgoing->result as $outg)
    $mailto_link.= $outg->uid.'@'.DOMAIN.';';

echo'     <td>&nbsp;<input type="submit" value="Anzeigen"/></td></tr>
      <tr><td colspan="6"><a href="'.$mailto_link.'">Email</a> an alle zur Zeit im Ausland befindlichen Studenten senden</td>
		</tr>
	</table>
</form>';

echo $message;
echo '
<table id="myTable" class="tablesorter">
	<thead>
		<tr>
			<th>ID</th>
            <th>UID</th>
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
    echo '<td>'.$row->uid.'</td>';
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