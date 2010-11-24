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

require_once('auth.php');
require_once '../config/wawi.config.inc.php';
require_once '../include/firma.class.php';
require_once '../include/organisationseinheit.class.php';
require_once '../include/mitarbeiter.class.php';
require_once '../include/datum.class.php';
require_once '../include/benutzerberechtigung.class.php';
require_once '../include/wawi_konto.class.php';
require_once '../include/wawi_bestellung.class.php';
require_once '../include/wawi_kostenstelle.class.php';
require_once '../include/wawi_bestelldetails.class.php';

$aktion ='';

if(isset($_POST['getKonto']))
{
	$id = $_POST['id']; 
	if(is_numeric($id))
	{
		$konto = new wawi_konto(); 
		$konto->getKontoFromKostenstelle($id);
		if(count($konto->result)>0)
		{
			foreach($konto->result as $ko)
			{
				echo '<option value='.$ko->konto_id.' >'.$ko->kurzbz."</option>\n";
			}
		}
		else 
			echo "<option value =''>Keine Konten zu dieser Kst</option>";
	}
	else
		echo "<option value =''>Keine Konten zu dieser Kst</option>";
	exit; 
}

if(isset($_POST['getFirma']))
{
	$id = $_POST['id']; 
	if(isset($_POST['id']))
	{
		if($_POST['id'] == 'opt_auswahl')
		{
			// anzeige aller Firmen
			$firmaAll = new firma(); 
			$firmaAll->getAll(); 
			
			echo "<option value=''>-- auswählen --</option>\n";
			foreach ($firmaAll->result as $fi)
			{
				echo "<option value=".$fi->firma_id." >".$fi->name."</option>\n";
			}
			
		}
		else
		{
			// anzeige der Firmen die oe zugeordnet sind
			$firma = new firma(); 
			$firma->get_firmaorganisationseinheit(null,$id);
			if(count($firma->result)>0)
			{
				echo "<option value=''>-- auswählen --</option>\n";
				foreach($firma->result as $fi)
				{
					echo '<option value='.$fi->firma_id.' >'.$fi->name."</option>\n";
				}
			}
			else 
				echo "<option value =''>Keine Firmen zu dieser OE</option>";
		}
	}
	else
		echo "<option value =''>Keine Firmen zu dieser OE</option>";
	exit; 
}

if(isset($_POST['getSearchKonto']))
{
	$id = $_POST['id']; 
	if(isset($_POST['id']))
	{
		if($_POST['id'] == 'opt_auswahl')
		{
			$konto = new wawi_konto();
			$konto->getAll();
			// anzeige aller Konten
			echo "<option value=''>-- auswählen --</option>\n";
			foreach($konto->result as $ko)
			{
				echo '<option value='.$ko->konto_id.' >'.$ko->kurzbz."</option>\n";
		
			}
		}
		else 
		{
			// anzeige aller Konten die der Kostenstelle zugeordnet sind
			$konto = new wawi_konto();
			$konto->getKontoFromOE($id);
			if(count($konto->result)>0)
			{
				echo "<option value=''>-- auswählen --</option>\n";
				foreach($konto->result as $ko)
				{
					echo '<option value='.$ko->konto_id.' >'.$ko->beschreibung[1]."</option>\n";
				}
			}
			else 
				echo "<option value =''>Kein Konto zu dieser OE</option>";
		}
	}
	else
		echo "<option value =''>Kein Konto zu dieser OE</option>";
	exit; 
}
	
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>WaWi Bestellung</title>	
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
	function conf_del()
	{
		return confirm('Diese Bestellung wirklich löschen?');
	}
	
	function loadKonto(id)
	{
		$.post("bestellung.php", {id: id, getKonto: 'true'},
		function(data){
			$('#konto').html(data);
		});
	}

	function loadFirma(id)
	{
		$.post("bestellung.php", {id: id, getFirma: 'true'},
		function(data){
			$('#firma').html(data);
		});
		
		$.post("bestellung.php", {id: id, getSearchKonto: 'true'},
				function(data){
					$('#searchKonto').html(data);
		});
	}

	function formatItem(row) 
	{
	    return row[0] + " <li>" + row[1] + "</li> ";
	}	  		  
 	
	function conf_del()
	{
		return confirm('Diese Bestellung wirklich löschen?');
	}

	$(function() {
		$( "#datepicker_evon" ).datepicker($.datepicker.regional['de']);
	});
	$(function() {
		$( "#datepicker_ebis" ).datepicker($.datepicker.regional['de']);
	});
	$(function() {
		$( "#datepicker_bvon" ).datepicker($.datepicker.regional['de']);
	});
	$(function() {
		$( "#datepicker_bbis" ).datepicker($.datepicker.regional['de']);
	});
	$(function() {
		$( "#datepicker_liefertermin" ).datepicker($.datepicker.regional['de']);
	});

	$(document).ready(function() 
	{ 
	    $("#myTable").tablesorter(
		{
			sortList: [[1,0]],
			widgets: ['zebra']
		}); 
		
	    $('#aufteilung_link').click(function() {
	          $('#aufteilung').toggle();
	          return false;
	        });
        
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
	  	  		  		  
	}); 
	function calcLine(id)
   	{
     var zahl = 
     (eval(document.getElementById("preisprove_"+id).value)  + ((document.getElementById("preisprove_"+id).value) * (document.getElementById("mwst_"+id).value) / 100)) 
     * eval(document.getElementById("menge_"+id).value);
     document.getElementById("brutto_"+id).value = zahl.toFixed(2); 
   	}

	function getMwst()
	{

	}
			
	</script>
</head>
<body>

<?php 
$date = new datum(); 
$user=get_uid();

$berechtigung_kurzbz='wawi/bestellung'; 
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$kst=new wawi_kostenstelle(); 
$kst->loadArray($rechte->getKostenstelle($berechtigung_kurzbz)); 


if (isset($_GET['method']))
	$aktion = $_GET['method'];
	
if($aktion == 'suche')
{	 
	if(!isset($_POST['submit']))
	{
		// Suchmaske anzeigen
		$oe = new organisationseinheit(); 
		$oe->getAll(); 
		$oeinheiten= $oe->result; 
		$konto = new wawi_konto();
		$konto->getAll();
		$konto_all = $konto->result;
		$datum = new datum(); 
		$datum=getdate(); 
		$firmaAll = new firma(); 
		$firmaAll->getAll(); 
		if ($datum['mon']<=9)
		{
			$suchdatum="01.09.".($datum['year']-1);
		}
		else
		{
			$suchdatum="01.09.".$datum['year'];
		}

		echo "<h2>Bestellung suchen</h2>\n"; 
		echo "<form action ='bestellung.php?method=suche' method='post' name='sucheForm'>\n";
		echo "<table border =0>\n";
		echo "<tr>\n";
		echo "<td>Bestellnummer</td>\n";
		echo "<td><input type = 'text' size ='32' maxlength = '16' name = 'bestellnr'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td>Titel</td>\n";
		echo "<td><input type = 'text' size ='32' maxlength = '256' name = 'titel'></td>\n";
		echo "<tr>\n";
		echo "<tr>\n"; 
		echo "<td>Erstelldatum</td>\n";
		echo "<td>von <input type ='text' id='datepicker_evon' size ='12' name ='evon' value=$suchdatum> bis <input type ='text' id='datepicker_ebis' size ='12' name = 'ebis'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td>Bestelldatum</td>\n";
		echo "<td>von <input type ='text' id='datepicker_bvon' size ='12' name ='bvon'> bis <input type ='text' id='datepicker_bbis' size ='12' name = 'bbis'></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td> Organisationseinheit: </td>\n";
		echo "<td><SELECT name='filter_oe_kurzbz' onchange='loadFirma(this.value)'>\n"; 
		echo "<option value='opt_auswahl'>-- auswählen --</option>\n";
		foreach ($oeinheiten as $oei)
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
		echo "<td> <input id='firmenname' name='firmenname' size='32' maxlength='30' value=''  >\n";
		
		echo "<SELECT name='filter_firma' id='firma' style='width: 256px;'>\n"; 
		echo "<option value=''>-- auswählen --</option>\n";
		foreach ($firmaAll->result as $fi)
		{
			echo "<option value=".$fi->firma_id." >".$fi->name."</option>\n";
		}
		echo "</td>\n";
		echo "</SELECT>\n";
		echo "</td>\n";
		echo "<td> <input type ='hidden' id='firma_id' name='firma_id' size='10' maxlength='30' value=''  >\n";
		echo "</td>\n";
		echo "</tr>\n";	
		echo "<tr>\n";
		echo "<td> Konto: </td>\n";
		echo "<td><SELECT name='filter_konto' id='searchKonto' style='width: 230px;'>\n"; 
		echo "<option value=''>-- auswählen --</option>\n";	
		foreach($konto_all as $ko)
		{
			echo '<option value='.$ko->konto_id.' >'.$ko->kurzbz."</option>\n";
	
		}
		echo "</td>\n";
		echo "</SELECT>\n";
		echo "</tr>\n";	
		echo "<tr>\n";
		echo "<td> Änderung durch: </td>\n";
		echo "<td> <input id='mitarbeiter_name' name='mitarbeiter_name' size='32' maxlength='30' value=''  >\n";
		echo "</td>\n";
		echo "<td> <input type ='hidden' id='mitarbeiter_uid' name='mitarbeiter_uid' size='10' maxlength='30' value=''  >\n";
		echo "</td>\n";		
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td>Nur ohne Rechnung</td>\n";
		echo "<td><input type ='checkbox' name ='rechnung'></td>\n";
		echo "</tr>\n";
		echo "<tr><td>&nbsp;</td></tr>\n";
		echo "<tr><td><input type='submit' name ='submit' value='Suche'></td></tr>\n";
		echo "</table>\n";
		echo "</form>\n";
	}
	else
	{		
		// Suchergebnisse anzeigen
		//var_dump($_POST);
		$bestellnummer = $_POST['bestellnr'];
		$titel = $_POST['titel'];
		$evon = $_POST['evon'];
		$ebis = $_POST['ebis'];
		$bvon = $_POST['bvon'];
		$bbis = $_POST['bbis'];
		$firma_id = $_POST['firma_id'];
		$oe_kurzbz = $_POST['filter_oe_kurzbz'];
		$filter_konto = $_POST['filter_konto'];
		$mitarbeiter_uid =  $_POST['mitarbeiter_uid'];
		$filter_firma = $_POST['filter_firma'];
		if (isset ($_POST['rechnung']))
			$rechnung = true; 
		else
			$rechnung = false; 
		
		$bestellung = new wawi_bestellung();
		
		if($evon != '') 
			$evon = $date->formatDatum($evon);
		if($ebis != '') 
			$ebis = $date->formatDatum($ebis);
		if($bvon != '') 
			$bvon = $date->formatDatum($bvon);
		if($bbis != '') 
			$bbis = $date->formatDatum($bbis);
			
		if(($evon || $evon === '') && ($ebis || $ebis === '' ) && ($bvon || $bvon === '') && ($bbis || $bbis === ''))
		{
			// Filter firma oder firma id werden angezeigt
			if($bestellung->getAllSearch($bestellnummer, $titel, $evon, $ebis, $bvon, $bbis, $firma_id, $oe_kurzbz, $filter_konto, $mitarbeiter_uid, $rechnung, $filter_firma))
			{
				$firma = new firma();
				$date = new datum(); 
				
				echo "<table id='myTable' class='tablesorter' width ='100%'> <thead>\n";		
				echo "<tr>
						<th></th>
						<th>Bestellnr.</th>
						<th>Bestell_ID</th>
						<th>Firma</th>
						<th>Erstellung</th>
						<th>Freigegeben</th>
						<th>Brutto</th>
						<th>Titel</th>
						<th>Letzte Änderung</th>
					  </tr></thead><tbody>\n";
			
				foreach($bestellung->result as $row)
				{	
					$brutto = $bestellung->getBrutto($row->bestellung_id);
					$firmenname = '';
					if(is_numeric($row->firma_id))
					{
						$firma->load($row->firma_id);	
						$firmenname = $firma->name; 
					}

					//Zeilen der Tabelle ausgeben
					echo "<tr>\n";
					echo "<td nowrap> <a href= \"bestellung.php?method=update&id=$row->bestellung_id\" title=\"Bearbeiten\"> <img src=\"../skin/images/edit.gif\"> </a><a href=\"bestellung.php?method=delete&id=$row->bestellung_id\" onclick='return conf_del()' title='Löschen'> <img src=\"../skin/images/delete.gif\"></a>";
					echo '<td>'.$row->bestell_nr."</td>\n";
					echo '<td>'.$row->bestellung_id."</td>\n";
					echo '<td>'.$firmenname."</td>\n";
					echo '<td>'.$date->formatDatum($row->insertamum, 'd.m.Y')."</td>\n";
					echo '<td>'.$freigegeben=($row->freigegeben=='t')?'ja':'nein'."</td>\n"; 
					echo '<td>'.number_format($brutto,2)."</td>\n"; 
					echo '<td>'.$row->titel."</td>\n";
					echo '<td>'.$row->updateamum.' '.$row->updatevon ."</td>\n"; 
		
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
} 	else if($aktion == 'new')
	{
		// Maske für neue Bestellung anzeigen
		echo "<h2>Neue Bestellung</h2>";
		echo "<form action ='bestellung.php?method=save' method='post' name='newForm'>\n";
		echo "<table border = 0>\n";
		echo "<tr>\n";
		echo "<td>Titel:</td>\n";
		echo "<td><input type='text' size ='32' maxlength='256' id ='titel' name ='titel'>";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td>Kostenstelle:</td><td><SELECT name='filter_kst' onchange='loadKonto(this.value)'>\n";
		echo "<option value=''>-- Kostenstelle auswählen --</option>\n";
		foreach ($kst->result as $ks)
		{
			echo "<option value=".$ks->kostenstelle_id.">".$ks->bezeichnung."(".mb_strtoupper($ks->kurzbz).") - ".mb_strtoupper($ks->oe_kurzbz)."</option>\n";
		}				
		echo "</SELECT></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td>Firma:</td>\n";
		echo "<td> <input id='firmenname' name='firmenname' size='32' maxlength='30' value=''  ></td>\n";
		echo "<td> <input type ='hidden' id='firma_id' name='firma_id' size='10' maxlength='30' value=''  ></td>\n";
		echo "</tr>\n";
		echo "<tr>\n"; 
		echo "<td>Konto: </td>\n"; 
		echo "<td>\n";
		echo "<select name='konto' id='konto' style='width: 230px;'>\n";
		echo "<option value='' >Kostenstelle auswaehlen</option>\n";
		echo "</select>\n";
		echo "</td>\n"; 
		echo "<tr>\n";
		echo "<td>&nbsp;</td>\n";
		echo "<tr><td><input type='submit' name='submit' value='Anlegen'></td></tr>\n";
		echo "</table>\n";
	}
	else if($aktion == 'save')
	{
		if(isset($_POST))
		{
			// Die Bestellung wird gespeichert und die neue id zurückgegeben
			$newBestellung = new wawi_bestellung(); 
			$newBestellung->titel = $_POST['titel'];
			$newBestellung->kostenstelle_id = $_POST['filter_kst'];
			$newBestellung->firma_id = $_POST['firma_id'];
			$newBestellung->konto_id = $_POST['konto'];
			
			$newBestellung->insertamum = date('Y-m-d H:i:s');
			$newBestellung->insertvon = $user; 
			$newBestellung->updateamum = date('Y-m-d H:i:s');
			$newBestellung->updatevon = $user; 
			
			$newBestellung->new = true; 
			$newBestellung->freigegeben = false; 
			
			if (!$bestell_id = $newBestellung->save())
			echo $newBestellung->errormsg; 
			echo "Bestellung mit der ID ".$bestell_id." erfolgreich angelegt. ";
			echo "<a href = bestellung.php?method=update&id=".$bestell_id."> Link drücken";  
		}
	
	} 
	else if($_GET['method']=='delete')
	{
		$id = (isset($_GET['id'])?$_GET['id']:null);
		
		$bestellung = new wawi_bestellung(); 
		
		if($bestellung->delete($id))
		{
			echo 'Bestellung erfolgreich gelöscht. <br>';
		}
		else
		{
			echo $bestellung->errormsg; 
		}
	}
	else if($_GET['method']=='update')
	{
		$id = (isset($_GET['id'])?$_GET['id']:null);
		
		$bestellung = new wawi_bestellung(); 
		$bestellung->load($id); 
		$detail = new wawi_bestelldetail(); 
		$detail->getAllDetailsFromBestellung($id);
		$anz_detail =  count($detail->result); 
		$konto = new wawi_konto(); 
		$konto->getKontoFromKostenstelle($bestellung->kostenstelle_id);
		$kostenstelle = new wawi_kostenstelle(); 
		$kostenstelle->load($bestellung->kostenstelle_id);
		
		
		$i= 0; 
		$summe= 0; 
		
		echo "<h2>Bearbeiten</h2>";
		echo "<form action ='bestellung.php?method=update' method='post' name='editForm'>\n";
		echo "<h4>Bestellnummer: ".$bestellung->bestell_nr."</h4>";
		
		//tabelle Bestelldetails
		echo "<table border = 0 width= '100%' class='dark'>\n";
		echo "<tr>\n"; 	
		echo "<td>Titel: </td>\n";
		echo "<td><input name= 'titel' type='text' size='60' maxlength='256' value ='".$bestellung->titel."'></td>\n";
		echo "<td>Erstellt am:</td>\n"; 
		echo "<td>".$date->formatDatum($bestellung->insertamum, 'd.m.Y')."</td>\n";
		echo "</tr>\n"; 
		echo "<tr>\n"; 	
		echo "<td>Firma: </td>\n";
		echo "<td><input type='text' name='firma' size='60' maxlength='256' value ='".$bestellung->firma_id."'></input></td>\n";
		echo "<td>Liefertermin:</td>\n"; 
		echo "<td><input type='text' name ='liefertermin'  size='60' maxlength='10' id ='datepicker_liefertermin'></input></td>\n";
		echo "</tr>\n"; 
		echo "<tr>\n"; 	
		echo "<td>Kostenstelle: </td>\n";
		echo "<td>$kostenstelle->bezeichnung</td>\n";
		echo "<td>Lieferadresse:</td>\n"; 
		echo "<td><input type='text' name='lieferadresse' size='60' maxlength='256' ></input></td>\n";
		echo "</tr>\n"; 
		echo "<tr>\n"; 	
		echo "<td>Konto: </td>\n";
		echo "<td><SELECT name='filter_konto' id='searchKonto' style='width: 230px;'>\n"; 
		echo "<option value=''>-- auswählen --</option>\n";	
		foreach($konto->result as $ko)
		{ 
			$selected ='';
			if($ko->konto_id == $bestellung->konto_id)
				$selected = 'selected';	
					
			echo '<option value='.$ko->konto_id.' '.$selected.'>'.$ko->kurzbz."</option>\n";
	
		}
		echo "<td>Rechnungsadresse:</td>\n"; 
		echo "<td><input type='text' name='rechnungsadresse' size='60' maxlength='256' ></input></td>\n";
		echo "</tr>\n"; 
		echo "<tr>\n"; 	
		echo "<td>Bemerkungen: </td>\n";
		echo "<td><input type='text' name='bemerkung' size='60' maxlength='256' value =''></input></td>\n";
		echo "<td></td>\n"; 
		echo "<td></td>\n";
		echo "</tr>\n"; 
		echo "</table>\n";
		
		echo "<br>";
		
		//tabelle Positonen
		echo "<table border =1 width='70%'>\n";
		echo "<tr>\n";
		echo "<th>Löschen</th>\n";
		echo "<th>Pos</th>\n";
		echo "<th>Menge</th>\n";
		echo "<th>VE</th>\n";
		echo "<th>Bezeichnung</th>\n";
		echo "<th>Artikelnr.</th>\n";
		echo "<th>Preis/VE</th>\n";
		echo "<th>USt</th>\n";
		echo "<th>Brutto</th>\n";
		echo "</tr>\n";
		foreach($detail->result as $det)
		{
			$i++; 
			$brutto=($det->menge * ($det->preisprove +($det->preisprove * ($det->mwst/100))));
			echo "<tr>\n";
			echo "<td><a>delete</a></td>\n";
			echo "<td><input type='text' size='2' name='pos' id='pos' maxlength='2' value='$det->position' ></input></td>\n";
			echo "<td><input type='text' size='5' name='menge' id='menge_$i' maxlength='7' value='$det->menge' onChange='calcLine($i);'></input></td>\n";
			echo "<td><input type='text' size='5' name='ve' id='ve' maxlength='7' value='$det->verpackungseinheit'></input></td>\n";
			echo "<td><input type='text' size='100' name='beschreibung' id='beschreibung' value='$det->beschreibung'</input></td>\n";
			echo "<td><input type='text' size='25' name='artikelnr' id='artikelnr' maxlength='32' value='$det->artikelnummer'></input></td>\n";
			echo "<td><input type='text' size='25' name='preisprove' id='preisprove_$i' maxlength='15' value='$det->preisprove' onChange='calcLine($i);'></input></td>\n";
			echo "<td><input type='text' size='5' name='mwst' id='mwst_$i' maxlength='5' value='$det->mwst' onChange='calcLine($i);'></input></td>\n";
			echo "<td><input type='text' size='10' id='brutto_$i' value='".number_format($brutto,2)."' disabled></input></td>\n";
			echo "</tr>\n";
			$summe+=$brutto; 
		}
		echo "</table>\n";
		echo "Gesamtpreis/Brutto: ".$summe; 
		echo "<br><br>\n"; 
		// div Aufteilung --> kann ein und ausgeblendet werden
		echo "<a id='aufteilung_link'>Aufteilung</a>\n"; 
		echo "<div id='aufteilung'>\n";
		echo "test"; 
		echo "</div>"; 
	}