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
require_once '../include/firma.class.php';
require_once '../include/organisationseinheit.class.php';
require_once '../include/wawi_konto.class.php';
require_once '../include/mitarbeiter.class.php';
$aktion ='';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>WaWi Bestellung</title>	
	<link rel="stylesheet" href="../skin/wawi.css" type="text/css"/>
	<link rel="stylesheet" href="../include/js/jquery.autocomplete.css" type="text/css"/>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<script type="text/javascript" src="../include/js/jquery.js"></script> 
	<script type="text/javascript" src="../include/js/jquery.metadata.js"></script> 
	<script type="text/javascript" src="../include/js/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="../include/js/jquery.autocomplete.min.js" ></script>
	<script type="text/javascript" src="../include/js/jquery-ui.js" ></script>

	<script type="text/javascript">
		function formatItem(row) 
		{
		    return row[0] + " <li>" + row[1] + "</li> ";
		}

		$(document).ready(function() 
		{
			  $('#firma_id').autocomplete('wawi_autocomplete.php', 
			  {
				minChars:2,
				matchSubset:1,matchContains:1,
				width:500,
				formatItem:formatItem,
				extraParams:{'work':'wawi_firma_search'	}
		  }).result(function(event, item) {
			  $('#firmenname').val(item[1]);
		  });		  		  
	 	});

		$(document).ready(function() 
		{
			  $('#mitarbeiter_name').autocomplete('wawi_autocomplete.php', 
			  {
				minChars:2,
				matchSubset:1,matchContains:1,
				width:500,
				formatItem:formatItem,
				extraParams:{'work':'wawi_mitarbeiter_search'	}
		  }).result(function(event, item) {
			  $('#mitarbeiter_uid').val(item[1]);
		  });
		  		  		  
	 	});

	</script>
</head>
<body>

<?php 

if (isset($_GET['method']))
	$aktion = $_GET['method'];


if($aktion == 'suche')
{
	$firma = new firma(); 
	$firma->getAll(); 
	$firma_all = $firma->result; 
	$oe = new organisationseinheit(); 
	$oe->getAll(); 
	$oeinheiten= $oe->result; 
	$konto = new wawi_konto();
	$konto->getAll();
	$konto_all = $konto->result;
	$mitarbeiter = new mitarbeiter();
	$mitarbeiter_all = array(); 
	$mitarbeiter_all = $mitarbeiter->getMitarbeiter();

		

	echo "Bestellung suchen "; 
	echo "<form action ='bestellung.php?method=suche method='post' name='sucheForm'>";
	echo "<table border =0>";
	echo "<tr>";
	echo "<td>Bestellnummer</td>";
	echo "<td><input type = 'text' size ='32' maxlength = '16' name = 'bestellnr'></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>Titel</td>";
	echo "<td><input type = 'text' size ='32' maxlength = '256' name = 'titel'></td>";
	echo "<tr>";
	echo "<tr>"; 
	echo "<td>Erstelldatum</td>";
	echo "<td>von <input type ='text' size ='8' name ='evon'> bis <input type ='text' size ='8' name = 'ebis'></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>Bestelldatum</td>";
	echo "<td>von <input type ='text' size ='8' name ='bvon'> bis <input type ='text' size ='8' name = 'bbis'></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td> Firma: </td>";
	echo "<td> <input id='firma_id' name='firma_id' size='32' maxlength='30' value=''  >";
	echo "</td>";
	echo "<td> <input id='firmenname' name='firmenname' size='10' maxlength='30' value=''  >";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td> Organisationseinheit: </td>";
	echo "<td><SELECT name='filter_oe_kurzbz'>"; 
	echo '<option value="">-- auswählen --</option>';
	foreach ($oeinheiten as $oei)
	{
		if($oei->aktiv)
		{
			echo '<option value="'.$oei->oe_kurzbz.'" >'.$oei->organisationseinheittyp_kurzbz.' '.$oei->bezeichnung.'</option>';
		}
		else 
		{
			echo '<option style="text-decoration:line-through;" value="'.$oei->oe_kurzbz.'">'.$oei->bezeichnung.'</option>';
		}	
	}
	echo "</td>";
	echo "</SELECT>";
	echo "</tr>";
	echo "<tr>";
	echo "<td> Konto: </td>";
	echo "<td><SELECT name='filter_konto'>"; 
	echo '<option value="">-- auswählen --</option>';
	
	foreach($konto_all as $ko)
	{
		echo '<option value='.$ko->beschreibung.' >'.$ko->kurzbz.'</option>';

	}
	echo "</td>";
	echo "</SELECT>";
	echo "</tr>";	
	echo "<tr>";
	echo "<td> Änderung durch: </td>";

	echo "<td> <input id='mitarbeiter_name' name='mitarbeiter_name' size='32' maxlength='30' value=''  >";
	echo "</td>";
	echo "<td> <input id='mitarbeiter_uid' name='mitarbeiter_uid' size='10' maxlength='30' value=''  >";
	echo "</td>";
	
	echo "</SELECT>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>Nur ohne Rechnung</td>";
	echo "<td><input type ='checkbox' name ='rechnung'></td>";
	echo "</tr>";
	echo "<tr><td>&nbsp;</td></tr>";
	echo '<tr><td><input type="submit" value="Suche"></td></tr>';
	
	echo "</table>";
	echo "</form>";
	
}