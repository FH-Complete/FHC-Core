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
require_once('../config/wawi.config.inc.php');
require_once('auth.php');
require_once('../include/firma.class.php');
require_once('../include/organisationseinheit.class.php');
require_once('../include/datum.class.php');
require_once('../include/wawi_konto.class.php');
require_once('../include/wawi_rechnung.class.php');
require_once('../include/wawi_kostenstelle.class.php');
require_once('../include/benutzerberechtigung.class.php');

$aktion ='';
if (isset($_GET['method']))
	$aktion = $_GET['method'];
else 
	$aktion = 'suche';

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>WaWi Rechnung</title>	
	<link rel="stylesheet" href="../skin/wawi.css" type="text/css"/>
	<link rel="stylesheet" href="../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" href="../skin/jquery-ui.css" type="text/css"/>
	<link rel="stylesheet" href="../include/js/jquery.css" type="text/css"/>	
	<link rel="stylesheet" href="../include/js/jquery.autocomplete.css" type="text/css"/>
	
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<script type="text/javascript" src="../include/js/jquery.js"></script> 
	<script type="text/javascript" src="../include/js/jquery.metadata.js"></script> 
	<script type="text/javascript" src="../include/js/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="../include/js/jquery.autocomplete.min.js" ></script>
	<script type="text/javascript" src="../include/js/jquery-ui.js" ></script>
	<script type="text/javascript" src="..//include/js/jquery.ui.datepicker-de.js"></script> 
	
	
	<script type="text/javascript">
	function loadFirma(id)
	{
		$.post("bestellung.php", {id: id, getFirma: 'true'},
		function(data){
			$('#firma').html(data);
		});
	}

	function formatItem(row) 
	{
	    return row[0] + " <br/>" + row[1];
	}

	
		$(document).ready(function() 
		{
			<?php
			if($aktion=='suche' && !isset($_POST['submit']))
			{
				echo "
				  $('#firmenname').autocomplete('wawi_autocomplete.php', 
				  {
					minChars:2,
					matchSubset:1,matchContains:1,
					width:500,
					formatItem:formatItem,
					extraParams:{'work':'wawi_firma_search'	}
				  }).result(function(event, item) {
					  $('#firma_id').val(item[1]);
				  });
				  
				  $( \"#rechnungsdatum_von\" ).datepicker($.datepicker.regional['de']);	  		  
				  $( \"#rechnungsdatum_bis\" ).datepicker($.datepicker.regional['de']);
				  $( \"#buchungsdatum_von\" ).datepicker($.datepicker.regional['de']);
				  $( \"#buchungsdatum_bis\" ).datepicker($.datepicker.regional['de']);
				  $( \"#erstelldatum_bis\" ).datepicker($.datepicker.regional['de']);
				  $( \"#erstelldatum_von\" ).datepicker($.datepicker.regional['de']);
				  $( \"#bestelldatum_von\" ).datepicker($.datepicker.regional['de']);
				  $( \"#bestelldatum_bis\" ).datepicker($.datepicker.regional['de']);
				  
				  ";
			}
			?> 
			$("#myTable").tablesorter(
			{
				sortList: [[4,1]],
				widgets: ['zebra']
			});			
	 	});
	 	
			
	</script>
</head>
<body>

<?php 
$date = new datum(); 
$user=get_uid();

$berechtigung_kurzbz='wawi/rechnung'; 
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$kst=new wawi_kostenstelle(); 
$kst->loadArray($rechte->getKostenstelle($berechtigung_kurzbz)); 
	
if($aktion == 'suche')
{	 
	if(!isset($_POST['submit']))
	{
		// Suchmaske anzeigen
		$oe = new organisationseinheit(); 
		$oe->getAll(); 

		$konto = new wawi_konto();
		$konto->getAll();
		
		$kostenstelle = new wawi_kostenstelle();
		$kostenstelle->getAll();

		echo "<h2>Rechnung suchen</h2>\n"; 
		echo "<form action ='rechnung.php?method=suche' method='post' name='sucheForm'>\n";
		echo "<table border =0>\n";
		echo "<tr>\n";
		echo "<td><b>Rechnungsdaten</b></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td>Rechnungsnummer</td>\n";
		echo "<td><input type = 'text' size ='32' maxlength = '16' name = 'rechnungsnr'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n"; 
		echo "<td>Rechnungsdatum</td>\n";
		echo "<td>von <input type='text' id='rechnungsdatum_von' size='12' name='rechnungsdatum_von'> bis <input type ='text' id='rechnungsdatum_bis' size='12' name='rechnungsdatum_bis'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td>Buchungsdatum</td>\n";
		echo "<td>von <input type='text' id='buchungsdatum_von' size='12' name='buchungsdatum_von'> bis <input type='text' id='buchungsdatum_bis' size='12' name='buchungsdatum_bis'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td><b>Bestelldaten</b></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td>Bestellnummer</td>\n";
		echo "<td><input type='text' size='32' maxlength='16' name='bestellnummer'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n"; 
		echo "<td>Erstelldatum</td>\n";
		echo "<td>von <input type='text' id='erstelldatum_von' size='12' name='erstelldatum_von'> bis <input type ='text' id='erstelldatum_bis' size='12' name='erstelldatum_bis'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td>Bestelldatum</td>\n";
		echo "<td>von <input type='text' id='bestelldatum_von' size='12' name='bestelldatum_von'> bis <input type='text' id='bestelldatum_bis' size='12' name='bestelldatum_bis'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td> Organisationseinheit: </td>\n";
		echo "<td><SELECT name='filter_oe_kurzbz' onchange='loadFirma(this.value)'>\n"; 
		echo "<option value=''>-- auswählen --</option>\n";
		foreach ($oe->result as $oei)
		{
			if($oei->aktiv)
			{
				echo '<option value="'.$oei->oe_kurzbz.'" >'.$oei->organisationseinheittyp_kurzbz.' '.$oei->bezeichnung."</option>\n";
			}
			else 
			{
				echo '<option style="text-decoration:line-through;" value="'.$oei->oe_kurzbz.'">'.$oei->bezeichnung."</option>\n";
			}	
		}
		echo "</td>\n";
		echo "</SELECT>\n";
		echo "</tr>\n";		
		echo "<tr>\n";
		echo "<td> Firma: </td>\n";
		echo "<td> <input id='firmenname' name='firmenname' size='32' maxlength='30' value=''>\n";
		echo "</td>\n";
		echo "<td> <input type ='hidden' id='firma_id' name='firma_id' size='10' maxlength='30' value=''  >\n";
		echo "</td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td> Kostenstelle: </td>\n";
		echo "<td><SELECT name='filter_kostenstelle'>\n"; 
		echo "<option value=''>-- auswählen --</option>\n";	
		foreach($kostenstelle->result as $kst)
		{
			echo '<option value='.$kst->kostenstelle_id.' >'.$kst->bezeichnung."</option>\n";
	
		}
		echo "</td>\n";
		echo "</SELECT>\n";
		echo "</tr>\n";	
		echo "<tr>\n";
		echo "<td> Konto: </td>\n";
		echo "<td><SELECT name='filter_konto' id='searchKonto' style='width: 230px;'>\n"; 
		echo "<option value=''>-- auswählen --</option>\n";	
		foreach($konto->result as $ko)
		{
			echo '<option value='.$ko->konto_id.' >'.$ko->kurzbz."</option>\n";
	
		}
		echo "</td>\n";
		echo "</SELECT>\n";
		echo "</tr>\n";	
		echo "<tr>\n";
		echo "<tr><td>&nbsp;</td></tr>\n";
		echo "<tr><td><input type='submit' name ='submit' value='Suche'></td></tr>\n";
		echo "</table>\n";
		echo "</form>\n";
	}
	else
	{		
		// Suchergebnisse anzeigen
		$rechnungsnr = $_POST['rechnungsnr'];
		$bestellnummer = $_POST['bestellnummer'];
		$rechnungsdatum_von = $_POST['rechnungsdatum_von'];
		$rechnungsdatum_bis = $_POST['rechnungsdatum_bis'];
		$buchungsdatum_von = $_POST['buchungsdatum_von'];
		$buchungsdatum_bis = $_POST['buchungsdatum_bis'];
		$erstelldatum_von = $_POST['erstelldatum_von'];
		$erstelldatum_bis = $_POST['erstelldatum_bis'];
		$bestelldatum_von = $_POST['bestelldatum_von'];
		$bestelldatum_bis = $_POST['bestelldatum_bis'];
		$firma_id = $_POST['firma_id'];
		$oe_kurzbz = $_POST['filter_oe_kurzbz'];
		$filter_konto = $_POST['filter_konto'];
		$filter_kostenstelle = $_POST['filter_kostenstelle'];
		
		$rechnung = new wawi_rechnung();
		
		if($rechnungsdatum_von != '') 
			$rechnungsdatum_von = $date->formatDatum($rechnungsdatum_von);
		if($rechnungsdatum_bis != '') 
			$rechnungsdatum_bis = $date->formatDatum($rechnungsdatum_bis);
		if($buchungsdatum_von != '') 
			$buchungsdatum_von = $date->formatDatum($buchungsdatum_von);
		if($buchungsdatum_bis != '') 
			$buchungsdatum_bis = $date->formatDatum($buchungsdatum_bis);
		if($erstelldatum_von != '') 
			$erstelldatum_von = $date->formatDatum($erstelldatum_von);
		if($erstelldatum_bis != '') 
			$erstelldatum_bis = $date->formatDatum($erstelldatum_bis);
		if($bestelldatum_von != '') 
			$bestelldatum_von = $date->formatDatum($bestelldatum_von);
		if($bestelldatum_bis != '') 
			$bestelldatum_bis = $date->formatDatum($bestelldatum_bis);
					
		if($rechnungsdatum_von!==false && $rechnungsdatum_bis!==false 
		&& $buchungsdatum_von!==false && $buchungsdatum_bis!==false
		&& $erstelldatum_von!==false && $erstelldatum_bis!==false
		&& $bestelldatum_von!==false && $bestelldatum_bis!==false
		)
		{
			if($rechnung->getAllSearch($rechnungsnr, $rechnungsdatum_von, $rechnungsdatum_bis, $buchungsdatum_von, $buchungsdatum_bis, $erstelldatum_von, $erstelldatum_bis, $bestelldatum_von, $bestelldatum_bis, $bestellnummer, $firma_id, $oe_kurzbz, $filter_konto, $filter_kostenstelle))
			{
				$date = new datum(); 
				
				echo "<table id='myTable' class='tablesorter' width ='100%'> <thead>\n";		
				echo "<tr>
						<th></th>
						<th>Rechnungsnr.</th>
						<th>Bestell_ID</th>
						<th>Typ</th>
						<th>Rechnungsdatum</th>
						<th>Buchungstext</th>
						<th>Freigegeben</th>
						<th>Letzte Änderung</th>
					  </tr></thead><tbody>\n";
			
				foreach($rechnung->result as $row)
				{	
					//Zeilen der Tabelle ausgeben
					echo "<tr>\n";
					echo "<td nowrap> <a href= \"rechnung.php?method=update&id=$row->rechnung_id\" title=\"Bearbeiten\"> <img src=\"../skin/images/edit.gif\"> </a><a href=\"rechnung.php?method=delete&id=$row->rechnung_id\" onclick='return conf_del()' title='Löschen'> <img src=\"../skin/images/delete.gif\"></a>";
					echo '<td>'.$row->rechnungsnr."</td>\n";
					echo '<td>'.$row->bestellung_id."</td>\n";
					echo '<td>'.$row->rechnungstyp_kurzbz."</td>\n";
					echo '<td>'.$date->formatDatum($row->rechnungsdatum, 'd.m.Y')."</td>\n";
					echo '<td>'.$row->buchungstext."</td>\n";
					echo '<td>'.$freigegeben=($row->freigegeben=='t')?'ja':'nein'."</td>\n"; 
					echo '<td>'.$date->formatDatum($row->updateamum,'d.m.Y H:i:s').' '.$row->updatevon ."</td>\n"; 
					echo "</tr>\n";
				}
				echo "</tbody></table>\n";	
			}
			else 
			echo "Fehler bei der Abfrage!";
		}
		else
		echo "ungültiges Datumsformat";
	}
} 	
elseif($aktion == 'new')
{
	echo '<h1>Noch nicht implementiert</h1>';
}
elseif($aktion == 'save')
{
	echo '<h1>Noch nicht implementiert</h1>';
} 
elseif($aktion=='delete')
{
	echo '<h1>Noch nicht implementiert</h1>';
}
elseif($aktion=='update')
{
	echo '<h1>Noch nicht implementiert</h1>';
}
?>
</body>
</html>