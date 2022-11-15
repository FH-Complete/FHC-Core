<?php
/* Copyright (C) 2010 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
require_once '../config/wawi.config.inc.php';
require_once('auth.php');

require_once '../include/wawi_konto.class.php';
require_once '../include/wawi_bestellung.class.php';
require_once '../include/wawi_kostenstelle.class.php';
require_once '../include/wawi_bestelldetail.class.php';
require_once '../include/wawi_aufteilung.class.php'; 
require_once '../include/wawi_bestellstatus.class.php';
require_once '../include/wawi_zahlungstyp.class.php';
require_once '../include/datum.class.php';
require_once '../include/firma.class.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Checksrikpt für Bestellungen</title>	
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" href="../skin/jquery.css" type="text/css"/>
	<link rel="stylesheet" href="../skin/fhcomplete.css" type="text/css"/>
	<link rel="stylesheet" href="../skin/wawi.css" type="text/css"/>
	<link rel="stylesheet" type="text/css" href="../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../vendor/jquery/sizzle/sizzle.js"></script> 
	<script type="text/javascript">
	function checkKst()
	{
		if(isNaN(document.checkForm.min.value) || isNaN(document.checkForm.max.value))
		{
			alert("Bitte geben Sie eine Nummer ein.");
			return false;  
		}
		return true; 
	}
	</script>
	
</head>
<body>
<h1>Check Bestellungen</h1>
<?php 
$min = (isset($_POST['min'])?$_REQUEST['min']:'1');
$max = (isset($_POST['max'])?$_REQUEST['max']:'42');
$type = (isset($_GET['type'])?$_GET['type']:'');

echo '
<table>
	<tr>
	<td>
		<form action ="check_bestellung.php" method="post" name="checkForm">
		<table>
			<tr><td>min (Wochen): </td><td><input type="text" name="min" id="min" value="'.$min.'"></td></tr>
			<tr><td>max (Wochen): </td><td><input type="text" name="max" id="max" value="'.$max.'"></td></tr>
			<tr><td>&nbsp;</td><td><input type="submit" name="submit" value="anzeigen" onclick="return checkKst();"></td></tr>
		</table>
		</form>
	</td>
	<td width="100px">&nbsp;</td>
	<td valign="top">
		<form action ="check_bestellung.php?type=nichtgeliefert" method="post" name="checkForm">
			<input type="submit" name="submit" value="Nicht gelieferte Bestellungen anzeigen"></td></tr>
		</form>
	</td>
	</tr>
</table>';

echo '
	<script type="text/javascript">
	$(document).ready(function() 
	{ 
	    $("#checkTable").tablesorter(
		{
			sortList: [[4,1]],
			widgets: ["zebra"]
		}); 
	}); 
	</script>';

	$date = new datum(); 
	$firma = new firma();
	
	$bestellung = new wawi_bestellung();
	if($type=='nichtgeliefert')
		$bestellung->loadBestellungNichtGeliefert();
	else if(is_numeric($min) && is_numeric($max))
	{
		$bestellung->loadBestellungForCheck($min, $max);
	}
	else
		die('Fehlerhafte Parameter');	
		
	echo '	<table id="checkTable" class="tablesorter" width ="100%">
			<thead>
			<tr>
				<th></th>
				<th>Bestellnr.</th>
				<th>Bestell_ID</th>
				<th>Firma</th>
				<th>Erstellung</th>
				<th>Freigegeben</th>
				<th>Geliefert</th>
				<th>Bestellt</th>
				<th>Brutto</th>
				<th>Titel</th>
				<th>Letze Änderung</th>
			</tr>
			</thead>
			<tbody>';		
	foreach($bestellung->result as $row)
	{
		$firmenname = '';
		$geliefert ='nein';
		$bestellt ='nein';
		$status = new wawi_bestellstatus(); 
		if(is_numeric($row->firma_id))
		{
			$firma->load($row->firma_id);	
			$firmenname = $firma->name; 
		}
		if($row->freigegeben)
			$freigegeben = 'ja';
		else
			$freigegeben = 'nein';
		
		if($status->isStatiVorhanden($row->bestellung_id, 'Lieferung'))
					$geliefert = 'ja';
		
		if($status->isStatiVorhanden($row->bestellung_id, 'Bestellung'))
					$bestellt = 'ja';
			
		$brutto = $bestellung->getBrutto($row->bestellung_id);
		echo '	<tr>
					<td nowrap><a href="bestellung.php?method=update&id='.$row->bestellung_id.'" title="Bestellung bearbeiten"> <img src="../skin/images/edit_wawi.gif"></a><a href="bestellung.php?method=delete&id='.$row->bestellung_id.'" onclick="return conf_del()" title="Bestellung löschen"> <img src="../skin/images/delete_x.png"></a></td>
					<td>'.$row->bestell_nr.'</td>
					<td>'.$row->bestellung_id.'</td>
					<td>'.$firmenname.'</td>
					<td>'.$date->formatDatum($row->insertamum, "d.m.Y").'</td>
					<td>'.$freigegeben.'</td>
					<td>'.$geliefert.'</td>
					<td>'.$bestellt.'</td>
					<td align="right">'.number_format($brutto, 2, ",",".").'</td>
					<td>'.$row->titel.'</td>
					<td nowrap>'.$date->formatDatum($row->updateamum, "d.m.Y").' '.$row->updatevon.'</td>
				</tr>';
	}
	echo '	</tbody>
			</table>';

?>