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

require_once '../include/firma.class.php';
require_once '../include/organisationseinheit.class.php';
require_once '../include/mitarbeiter.class.php';
require_once '../include/datum.class.php';
require_once '../include/benutzerberechtigung.class.php';
require_once '../include/standort.class.php';
require_once '../include/adresse.class.php';
require_once '../include/studiengang.class.php';
require_once '../include/mail.class.php';
require_once '../include/wawi_konto.class.php';
require_once '../include/wawi_bestellung.class.php';
require_once '../include/wawi_kostenstelle.class.php';
require_once '../include/wawi_bestelldetails.class.php';
require_once '../include/wawi_aufteilung.class.php'; 
require_once '../include/wawi_bestellstatus.class.php';
require_once '../include/tags.class.php';

$aktion ='';
$test = 0;			// Bestelldetail Anzahl

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

if(isset($_POST['getDetailRow']) && isset($_POST['id']))
{
	if(is_numeric($_POST['id']))
	{
		echo getDetailRow($_POST['id']);
		$test++; 
		exit;
	}
	else
	{
		die('ID ungueltig');
	}
}

if(isset($_POST['deleteDetail']) && isset($_POST['id']))
{
	if(is_numeric($_POST['id']))
	{
		$detail = new wawi_bestelldetail(); 
		$detail->delete($_POST['id']); 
		exit;
	}
	else
	{
		die('ID ungueltig');
	}
}

if(isset($_POST['saveDetail']))
{
		$detail = new wawi_bestelldetail(); 
		$detail->bestellung_id = $_POST['bestellung']; 
		$detail->position = $_POST['pos'];
		$detail->menge = $_POST['menge']; 
		$detail->verpackungseinheit = $_POST['ve']; 
		$detail->beschreibung = $_POST['beschreibung']; 
		$detail->artikelnummer = $_POST['artikelnr']; 
		$detail->preisprove = $_POST['preis']; 
		$detail->mwst = $_POST['mwst']; 
		$detail->insertamum = date('Y-m-d H:i:s'); 
		$detail->updateamum = date('Y-m-d H:i:s'); 
		$detail->new = true; 
		if(!$detail->save())
			echo $detail->errormsg;
		echo $detail->bestelldetail_id;  
		exit;
}



if(isset($_POST['deleteBtnBestellt']) && isset($_POST['id']))
{
	$date = new datum(); 
	
	$bestellstatus = new wawi_bestellstatus(); 
	$bestellstatus->bestellung_id = $_POST['id'];
	$bestellstatus->bestellstatus_kurzbz = 'Bestellung';
	$bestellstatus->uid = $_POST['user_id'];
	$bestellstatus->oe_kurzbz = '';
	$bestellstatus->datum = date('Y-m-d H:i:s');
	$bestellstatus->insertvon = $_POST['user_id'];
	$bestellstatus->insertamum = date('Y-m-d H:i:s');
	$bestellstatus->updatevon = $_POST['user_id'];
	$bestellstatus->updateamum = date('Y-m-d H:i:s');
	
	if($bestellstatus->save())
	echo $date->formatDatum($bestellstatus->datum, 'd.m.Y');  
	else 
	echo $bestellstatus->errormsg; 
		exit; 
}

if(isset($_POST['deleteBtnStorno']) && isset($_POST['id']))
{
	$date = new datum(); 
	
	$bestellstatus = new wawi_bestellstatus(); 
	$bestellstatus->bestellung_id = $_POST['id'];
	$bestellstatus->bestellstatus_kurzbz = 'Storno';
	$bestellstatus->uid = $_POST['user_id'];
	$bestellstatus->oe_kurzbz = '';
	$bestellstatus->datum = date('Y-m-d H:i:s');
	$bestellstatus->insertvon = $_POST['user_id'];
	$bestellstatus->insertamum = date('Y-m-d H:i:s');
	$bestellstatus->updatevon = $_POST['user_id'];
	$bestellstatus->updateamum = date('Y-m-d H:i:s');
	
	if($bestellstatus->save())
	echo $date->formatDatum($bestellstatus->datum, 'd.m.Y');  
	else 
	echo $bestellstatus->errormsg; 
		exit; 
}	
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>WaWi Bestellung</title>	
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../skin/wawi.css" type="text/css"/>
	<link rel="stylesheet" href="../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" href="../skin/jquery-ui.css" type="text/css"/>
	<link rel="stylesheet" href="../include/js/jquery.css" type="text/css"/>	
	<link rel="stylesheet" href="../include/js/jquery.autocomplete.css" type="text/css"/>

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
	    return row[0] + " <br>" + row[1] + "<br> ";
	}	
	function formatItemTag(row) 
	{
	    return row[0];
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


	    $('#aufteilung').toggle();
	    
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
		if(!$rechte->isberechtigt('wawi/bestellung',null, 's'))
			die('Sie haben keine Berechtigung zum Suchen von Bestellungen');
		
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
		if(!$rechte->isberechtigt('wawi/bestellung',null, 's'))
			die('Sie haben keine Berechtigung zum Suchen von Bestellungen');
			
		$bestellnummer = $_POST['bestellnr'];
		$titel = $_POST['titel'];
		$evon = $_POST['evon'];
		$ebis = $_POST['ebis'];
		$bvon = $_POST['bvon'];
		$bbis = $_POST['bbis'];
		$firma_id = $_POST['firma_id'];
		if($_POST['filter_oe_kurzbz'] == 'opt_auswahl')
			$oe_kurzbz = '';
		else 
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
				$brutto = 0; 
				$gesamtpreis =0; 
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
					$gesamtpreis +=$brutto; 
					
					$firmenname = '';
					if(is_numeric($row->firma_id))
					{
						$firma->load($row->firma_id);	
						$firmenname = $firma->name; 
					}
					//Zeilen der Tabelle ausgeben
					echo "<tr>\n";
					echo "<td nowrap> <a href= \"bestellung.php?method=update&id=$row->bestellung_id\" title=\"Bestellung bearbeiten\"> <img src=\"../skin/images/edit_wawi.gif\"> </a><a href=\"bestellung.php?method=delete&id=$row->bestellung_id\" onclick='return conf_del()' title='Bestellung löschen'> <img src=\"../skin/images/delete_x.png\"></a><a href= \"rechnung.php?method=update&bestellung_id=$row->bestellung_id\" title=\"Neue Rechnung anlegen\"> <img src=\"../skin/images/Calculator.png\"> </a><a href= \"bestellung.php?method=copy&id=$row->bestellung_id\" title=\"Bestellung kopieren\"> <img src=\"../skin/images/copy.png\"> </a></td>";
					echo '<td>'.$row->bestell_nr."</td>\n";
					echo '<td>'.$row->bestellung_id."</td>\n";
					echo '<td>'.$firmenname."</td>\n";
					echo '<td>'.$date->formatDatum($row->insertamum, 'd.m.Y')."</td>\n";
					echo '<td>'.$freigegeben=($row->freigegeben=='t')?'ja':'nein'."</td>\n"; 
					echo '<td>'.number_format($brutto, 2, ",",".")."</td>\n"; 
					echo '<td>'.$row->titel."</td>\n";
					echo '<td>'.$row->updateamum.' '.$row->updatevon ."</td>\n"; 
		
					echo "</tr>\n";	
				}
				echo "</tbody>\n";
				echo "<tfooter><tr><td></td><td></td><td></td><td></td><td></td><td>Summe:</td><td colspan='2'>".number_format($gesamtpreis,2, ",",".")." €</td></tr></tfooter></table>\n";	
			}
			else 
			echo $bestellung->errormsg;
		}
		else
		echo "ungültiges Datumsformat";
	}
} 	else if($aktion == 'new')
	{
		// Maske für neue Bestellung anzeigen
		if(!$rechte->isberechtigt('wawi/bestellung',null, 'sui'))
			die('Sie haben keine Berechtigung zum Anlegen von Bestellungen');
		
		echo "<h2>Neue Bestellung</h2>";
		echo "<form action ='bestellung.php?method=save&new=true' method='post' name='newForm'>\n";
		echo "<table border = 0>\n";
		echo "<tr>\n";
		echo "<td>Titel:</td>\n";
		echo "<td><input type='text' size ='32' maxlength='256' id ='titel' name ='titel'>";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td>Kostenstelle:</td><td><SELECT name='filter_kst' onchange='loadKonto(this.value)'>\n";
		echo "<option value ='opt_kostenstelle'>-- Kostenstelle auswählen --</option>\n";
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
		echo "<option value='' >Konto auswaehlen</option>\n";
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
			if(!$rechte->isberechtigt('wawi/bestellung',null, 'sui'))
				die('Sie haben keine Berechtigung zum Suchen von Bestellungen');
			
			$newBestellung = new wawi_bestellung(); 
			$newBestellung->titel = $_POST['titel'];
			
			if($_POST['filter_kst']=='opt_kostenstelle')
				$newBestellung->kostenstelle_id = null; 
			else 
				$newBestellung->kostenstelle_id = $_POST['filter_kst'];
			
			$newBestellung->firma_id = $_POST['firma_id'];
			
			if($_POST['konto']=='')		
				$newBestellung->konto_id = null; 
			else
				$newBestellung->konto_id = $_POST['konto'];
			
			$newBestellung->insertamum = date('Y-m-d H:i:s');
			$newBestellung->insertvon = $user; 
			$newBestellung->updateamum = date('Y-m-d H:i:s');
			$newBestellung->updatevon = $user; 
			$newBestellung->new = true; 
			$newBestellung->freigegeben = false; 
			
			if (!$bestell_id = $newBestellung->save())
			{
				echo $newBestellung->errormsg; 
			}
			else 
			{
				echo "Bestellung erfolgreich angelegt. "; 
				$_GET['method']= 'update';
				$_GET['id'] = $bestell_id; 
			}
		}
	} 
	else if($_GET['method']=='delete')
	{
		if(!$rechte->isberechtigt('wawi/bestellung',null, 'suid'))
			die('Sie haben keine Berechtigung zum Löschen von Bestellungen');
		
		// Bestellung löschen
		$id = (isset($_GET['id'])?$_GET['id']:null);
		$bestellung = new wawi_bestellung(); 
		if($bestellung->RechnungVorhanden($id))
		{
			echo 'Kann nicht gelöscht werden. Der Bestellung ist noch eine Rechnung zugeordnet.'; 
		}
		else 
		{
			if($bestellung->delete($id))
				echo 'Bestellung erfolgreich gelöscht. <br>';
			else
				echo $bestellung->errormsg; 
		}
	}
	else if($_GET['method']=='deletedetail')
	{
		if(!$rechte->isberechtigt('wawi/bestellung',null, 'suid'))
			die('Sie haben keine Berechtigung zum Löschen von Bestellungen');
		
		// Bestellung löschen
		$id = (isset($_GET['id'])?$_GET['id']:null);
		$detail = new wawi_bestelldetail(); 
		$detail->delete($id); 
		
	}
	else if($_GET['method']=='copy')
	{ 
		$bestellung_id = $_GET['id'];
		$bestellung = new wawi_bestellung(); 
		if ($bestellung_neu = $bestellung->copyBestellung($bestellung_id, $user))
		{
			$_GET['method']='update';
			$_GET['id']=$bestellung_neu;
		}

	}	
	if($_GET['method']=='update')
	{ 
		
	echo '	<script type="text/javascript">
			function FensterOeffnen (adresse) 
			{
				MeinFenster = window.open(adresse, "Info", "width=400,height=500,left=100,top=200");
		  		MeinFenster.focus();
			}
			</script>'; 
		
		
		// Bestellung Editieren	
		if(!isset($_GET['bestellung']))
		{
			if(!$rechte->isberechtigt('wawi/bestellung',null, 'su'))
				die('Sie haben keine Berechtigung zum Bearbeiten von Bestellungen');
			
			$id = (isset($_GET['id'])?$_GET['id']:null);
	
			$bestellung = new wawi_bestellung(); 
			$bestellung->load($id); 
			$detail = new wawi_bestelldetail(); 
			$detail->getAllDetailsFromBestellung($id);
			$anz_detail =  count($detail->result); 
			$konto = new wawi_konto(); 
			$konto->getKontoFromKostenstelle($bestellung->kostenstelle_id);
			$konto_bestellung = new wawi_konto(); 
			$konto_bestellung->load($bestellung->konto_id);
			$kostenstelle = new wawi_kostenstelle(); 
			$kostenstelle->load($bestellung->kostenstelle_id);
			$aufteilung = new wawi_aufteilung(); 
			
			// Bei neuer Bestellung Default Aufteilung holen ansonsten von bestehender bestellung
			if(isset($_GET['new']))
			{
				$aufteilung->getAufteilungFromKostenstelle($bestellung->kostenstelle_id);
			}
			else
			{
				$aufteilung->getAufteilungFromBestellung($bestellung->bestellung_id);
			}
			
			$firma = new firma(); 
			$firma->load($bestellung->firma_id);  
			$liefertermin = $date->formatDatum($bestellung->liefertermin, 'd.m.Y'); 
			$allStandorte = new standort(); 
			$allStandorte->getStandorteWithTyp('Intern');
			$status= new wawi_bestellstatus();
			$bestell_tag = new tags(); 
			$studiengang = new studiengang(); 
			$studiengang->getAll('typ, kurzbz', null); 

			$summe= 0; 
			$konto_vorhanden = false; 
			
			echo "<h2>Bearbeiten</h2>";
			echo "<form action ='bestellung.php?method=update&bestellung=$bestellung->bestellung_id' method='post' name='editForm' id='editForm'>\n";
			echo "<h4>Bestellnummer: ".$bestellung->bestell_nr."</h4>";
			echo '<a href= "bestellung.php?method=copy&id='.$bestellung->bestellung_id.'">Bestellung kopieren</a>'; 
			//tabelle Bestelldetails
			echo "<table border = 0 width= '100%' class='dark'>\n";
			echo "<tr>\n"; 	
			echo "<td>Titel: </td>\n";
			echo "<td><input name= 'titel' type='text' size='60' maxlength='256' value ='".$bestellung->titel."'></td>\n";
			echo "<td>Erstellt am:</td>\n"; 
			echo "<td colspan ='2'><span name='erstellt' title ='".$bestellung->insertvon."' >".$date->formatDatum($bestellung->insertamum, 'd.m.Y')."</span></td>\n";
			echo "<td></td>"; 
			echo "</tr>\n"; 
			echo "<tr>\n"; 	
			echo "<td>Firma: </td>\n";
			echo "<td><input type='text' name='firmenname' id='firmenname' size='60' maxlength='256' value ='".$firma->name."'></input>\n";
			echo "<input type='text' name='firma_id' id='firma_id' size='5' maxlength='7' value ='".$bestellung->firma_id."'></td>\n";
			echo "<td>Liefertermin:</td>\n"; 
			echo "<td colspan ='2'><input type='text' name ='liefertermin'  size='11' maxlength='10' id ='datepicker_liefertermin' value='".$liefertermin."'></input></td>\n";
			echo "<td></td>"; 
			echo "</tr>\n"; 
			echo "<tr>\n"; 	
			echo "<td>Kostenstelle: </td>\n";
			echo "<td><input type='text' name='kostenstelle_id' id='kostenstelle_id' value='$kostenstelle->bezeichnung' disabled size ='60'></input></td>\n";
			echo "<td>Lieferadresse:</td>\n"; 
			echo "<td colspan ='2'><Select name='filter_lieferadresse' id='filter_lieferadresse' style='width: 400px;'>\n";
			
			foreach($allStandorte->result as $standorte)
			{
				$selected ='';
				$standort_lieferadresse = new adresse(); 
				$standort_lieferadresse->load($standorte->adresse_id); 
				
				if($standort_lieferadresse->adresse_id == $bestellung->lieferadresse)
				{	
					$selected ='selected';	
				}
				echo "<option value='".$standort_lieferadresse->adresse_id."' ". $selected.">".$standorte->kurzbz.' - '.$standort_lieferadresse->strasse.', '.$standort_lieferadresse->plz.' '.$standort_lieferadresse->ort."</option>\n";
			}		
			
			echo "</td><td></td></tr>\n"; 
			echo "<tr>\n"; 	
			echo "<td>Konto: </td>\n";
			echo "<td><SELECT name='filter_konto' id='searchKonto' style='width: 230px;'>\n"; 
			foreach($konto->result as $ko)
			{ 
				$selected ='';
				if($ko->konto_id == $bestellung->konto_id)
				{
					$selected = 'selected';	
					$konto_vorhanden = true; 
				}		
				echo '<option value='.$ko->konto_id.' '.$selected.'>'.$ko->kurzbz."</option>\n";
		
			}
			//wenn die konto_id von der bestellung nicht in den Konten die der Kostenstelle zugeordnet sind befidet --> selbst hinschreiben
			if(!$konto_vorhanden)
			{
				echo '<option value='.$bestellung->konto_id.' selected>'.$konto_bestellung->kurzbz."</option>\n";
			}
			echo "</td><td>Rechnungsadresse:</td>\n"; 
			echo "<td colspan ='2'><Select name='filter_rechnungsadresse' id='filter_rechnungsadresse' style='width: 400px;'>\n";
			foreach($allStandorte->result as $standorte)
			{
				$selected ='';
				$standort_rechnungsadresse = new adresse(); 
				$standort_rechnungsadresse->load($standorte->adresse_id); 
				
				if($standort_rechnungsadresse->adresse_id == $bestellung->rechnungsadresse)
					$selected ='selected';
					
				echo "<option value='".$standort_rechnungsadresse->adresse_id."' ". $selected.">".$standorte->kurzbz.' - '.$standort_rechnungsadresse->strasse.', '.$standort_rechnungsadresse->plz.' '.$standort_rechnungsadresse->ort."</option>\n";
			}		
			echo "</td><td></td></tr>\n"; 
			echo "<tr>\n"; 	
			echo "<td>Bemerkungen: </td>\n";
			echo "<td><input type='text' name='bemerkung' size='60' maxlength='256' value =''></input></td>\n";
			echo "<td>Status:</td>\n"; 
			echo "<td width ='200px'>\n";
	
			if(!$status->isStatiVorhanden($bestellung->bestellung_id, 'Bestellung'))
			{
				echo "<span id='btn_bestellt'>";	
				echo "<input type='button' value ='Bestellt' onclick='deleteBtnBestellt($bestellung->bestellung_id)'></input>";
				echo "</span>";
			}
			else
			{
				$status_help = new wawi_bestellstatus(); 
				$status_help->getStatiFromBestellung('Bestellung', $bestellung->bestellung_id); 
				echo '<span title ="'.$status_help->insertvon.'">Bestellt am: '.$date->formatDatum($status->datum,'d.m.Y').'</span>'; 
			}
			echo "</td><td>\n";		
			$disabled='';
			if(!$status->isStatiVorhanden($bestellung->bestellung_id, 'Storno') )
			{
				if(!$status->isStatiVorhanden($bestellung->bestellung_id, 'Bestellung'))
				$disabled = 'disabled';
				echo "<span id='btn_storniert'>";
				echo "<input type='button' value='Storniert' id='storniert' name='storniert' $disabled onclick='deleteBtnStorno($bestellung->bestellung_id)' ></input>";
				echo "</span>";
			}
			else 
			{
				echo "<span>Storniert am: ".$date->formatDatum($status->datum, 'd.m.Y')."</span>";
			}
			echo"</td></tr>\n"; 
			echo "<tr>\n";
			echo"<td>Tags:</td>\n"; 
			$bestell_tag->GetTagsByBestellung($bestellung->bestellung_id);
			$tag_help = $bestell_tag->GetStringTags();
			echo "<td><input type='text' id='tags' name='tags' size='32' value='".$tag_help."'>\n";		
		
			echo '	<script type="text/javascript">
						$("#tags").autocomplete("wawi_autocomplete.php", 
						{
							minChars:1,
							matchSubset:1,matchContains:1,
							width:500,
							multiple: true,
							multipleSeparator: "; ",
							formatItem:formatItemTag,
							extraParams:{"work":"tags", "bestell_id":"'.$bestellung->bestellung_id.'"}
						});
					</script>';
		
			echo "</td>\n"; 
			echo "<td>Freigabe:</td>\n";
			echo "<td colspan =2>";
			
			if($status->isStatiVorhanden($bestellung->bestellung_id, 'Freigabe'))
			{	
				echo "<span title='$status->insertvon'>KST:".$date->formatDatum($status->datum,'d.m.Y')." </span>"; 
			}
			else 
			{
				$rechte->getBerechtigungen($user); 
				$disabled = '';
				if($rechte->isberechtigt('wawi/freigabe',null, 'su', $bestellung->kostenstelle_id))
				{	
					if(!$status->isStatiVorhanden($bestellung->bestellung_id, 'Abgeschickt'))
						$disabled = 'disabled';
					echo "<input type='submit' value='KST Freigabe' name ='btn_freigabe' $disabled>"; 
				}
			}
			
			// Welche OEs müssen noch freigeben wenn KST schon freigegeben hat
			if($status->getFreigabeFromBestellung($bestellung->bestellung_id)) 
			{	
				$oes = array(); 
				$oes = $bestellung->FreigabeOe($bestellung->bestellung_id); 
				$freigabe = false; 
				foreach($oes as $o)
				{
					if(!$status->isStatiVorhanden($bestellung->bestellung_id, 'Freigabe', $o))
					{
						echo "<input type='submit' value='$o' name ='btn_freigabe'>"; 
						echo "<input type='hidden' value='$o' name ='freigabe_oe' id ='freigabe_id'>";   
						$freigabe = true; 
						break; 
					}
					else 
					{
						echo "<span title='$status->insertvon'>".$o.":".$date->formatDatum($status->datum,'d.m.Y')." </span>"; 
					}
				}
				if($freigabe == false)
				{
					if(!$bestellung->isFreigegeben($bestellung->bestellung_id))
					{
						$bestellung->SetFreigegeben($bestellung->bestellung_id); 
					}
					else
						echo "alle freigegeben";  
				}
			}

			echo "</td></tr>";
			echo "</table>\n";
			echo "<br>";
			
			//tabelle Details
			echo "<table border =0 width='70%'>\n";
			echo "<tr>\n";
			echo "<th></th>\n";
			echo "<th>Pos</th>\n";
			echo "<th>Menge</th>\n";
			echo "<th>VE</th>\n";
			echo "<th>Bezeichnung</th>\n";
			echo "<th>Artikelnr.</th>\n";
			echo "<th>Preis/VE</th>\n";
			echo "<th>USt <a href = 'mwst.html' onclick='FensterOeffnen(this.href); return false'> <img src='../skin/images/question.png'> </a></th>\n";
			echo "<th>Brutto</th>\n";
			echo "<th><div id='tags_headline' style='display:none'>Tags</div><a id='tags_link'><img src='../skin/images/plus.png'> </a></th>";
			echo "</tr>\n";
			echo "<tbody id='detailTable'>";
			$i= 1; 
			foreach($detail->result as $det)
			{
				$brutto=($det->menge * ($det->preisprove +($det->preisprove * ($det->mwst/100))));
				getDetailRow($i, $det->bestelldetail_id, $det->menge, $det->verpackungseinheit, $det->beschreibung, $det->artikelnummer, $det->preisprove, $det->mwst, sprintf("%01.2f",$brutto));
				$summe+=$brutto; 
				$i++; 
			}
			getDetailRow($i);
			
			$test = $i; 
			echo "</tbody>";
			echo "<tfoot><tr>"; 
			echo "<td></td>"; 
			echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td colspan ='2'>Gesamtpreis/Brutto: </td>";
			echo "<td id = 'brutto'></td>";
			echo "<td><input type='hidden' name='detail_anz' id='detail_anz' value='$test'></input></td>"; 
			echo "</tr>";
			echo "</tfoot>";
			echo "</table>\n";
			echo "<br><br>\n"; 
			echo '
			<script type="text/javascript">
			
			var anzahlRows='.$i.';
			var bestellung_id ='.$bestellung->bestellung_id.';
			var uid = "'.$user.'";
	
			 $("#tags_link").click(function() {
			 i=1; 
			 while(i<=anzahlRows)
			 {
				 $("#detail_tag_"+i).toggle();		 
 				i=i+1;        		
			 }
			  $("#tags_headline").toggle();
			  $("#tags_link").toggle();
			 return false;
	        });
			
			function deleteBtnBestellt(bestellung_id)
			{
				$("#btn_bestellt").html(); 
				
				$.post("bestellung.php", {id: bestellung_id, user_id: uid,  deleteBtnBestellt: "true"},
							function(data){
						
	
								$("#btn_bestellt").html("Bestellt am: " +data); 
								document.editForm.storniert.disabled=false; 
							});	
				 
			}
			
			function deleteBtnStorno(bestellung_id)
			{
				$("#btn_storniert").html(); 
				
				$.post("bestellung.php", {id: bestellung_id, user_id: uid,  deleteBtnStorno: "true"},
							function(data){
							$("#btn_storniert").html("Storniert am: " +data); 
							document.editForm.btn_submit.disabled=true; 
							document.editForm.btn_abschicken.disabled=true;
							});
			}
			
			/*
			Berechnet die Brutto Summe für eine Zeile
			*/
			function calcLine(id)
		   	{
		    	var brutto=0;
	
		    	var menge = $("#menge_"+id).val();
		    	var betrag = $("#preisprove_"+id).val();
		    	var mwst = $("#mwst_"+id).val();
		    	
		    	if(betrag!="" && mwst!="" && menge!="")
		    	{
		    		menge = parseFloat(menge);
					betrag = parseFloat(betrag);
					mwst = parseFloat(mwst);
					
					brutto = menge * (brutto + (betrag+(betrag*mwst/100)));
		    	}
		    	brutto = Math.round(brutto*100)/100;
			   	document.getElementById("brutto_"+id).value = brutto.toFixed(2);
			    summe();
		   	}
	
			/*
			Berechnet die gesamte Brutto Summe für eine Bestellung
			*/
			function summe()
			{
				var i=1;
				var netto=0;
				var brutto=0;
				while(i<=anzahlRows)
				{
				
					var menge =$("#menge_"+i).val();
					var betrag = $("#preisprove_"+i).val();
					var mwst = $("#mwst_"+i).val();
						
					if(betrag!="" && mwst!="" && menge!="")
					{
						menge = parseFloat(menge);
						betrag = parseFloat(betrag);
						mwst = parseFloat(mwst);
						
						netto = netto + betrag;
						
						brutto = brutto + (menge * (betrag+(betrag*mwst/100)));
					}
					i=i+1;
				}
				netto = Math.round(netto*100)/100;
				brutto = Math.round(brutto*100)/100;
				brutto = brutto.toFixed(2);
				$("#netto").html(netto);
				$("#brutto").html(brutto);
			}
			
			$(document).ready(function() 
			{
				summe();
			});
			
			/**
			 * Fuegt eine neue Zeile fuer den Betrag hinzu wenn die 
			 * uebergebene id, die der letzte Zeile ist
			 * und der Betrag eingetragen wurde
			 */
			function checkNewRow(id)
			{
				var betrag="";
				betrag = $("#preisprove_"+id).val();
				
				// Wenn der betrag nicht leer ist,
				// und die letzte reihe ist, 
				// dann eine neue Zeile hinzufuegen
				if(betrag.length>0 && anzahlRows==id)
				{
					$.post("bestellung.php", {id: id+1, getDetailRow: "true"},
							function(data){
								$("#detailTable").append(data);
								//saveDetail(anzahlRows);
								anzahlRows=anzahlRows+1;
								var test = 0; 
								test = document.getElementById("detail_anz").value;
								document.getElementById("detail_anz").value = parseFloat(test) +1;
							});
				}
	
			}
			
			function saveDetail(i )
			{
			var pos = $("#pos_"+i).val(); 
			var menge =  $("#menge_"+i).val();
			var ve =  $("#ve_"+i).val(); 
			var beschreibung =  $("#beschreibung_"+i).val(); 
			var artikelnr =  $("#artikelnr_"+i).val(); 
			var preis =  $("#preisprove_"+i).val(); 
			var mwst =  $("#mwst_"+i).val(); 
			var brutto =  $("#brutto_"+i).val(); 
			$.post("bestellung.php", {pos: pos, menge: menge, ve: ve, beschreibung: beschreibung, artikelnr: artikelnr, preis: preis, mwst: mwst, brutto: brutto, bestellung: bestellung_id, saveDetail: "true"},
				function(data){
					alert(data);
				});  
			}
			
			// löscht einen Bestelldetaileintrag
			function removeDetail(i, bestelldetail_id)
			{
				$("#row_"+i).remove();
				$.post("bestellung.php", {id: bestelldetail_id, deleteDetail: "true"},
				function(data){
				}); 
			}
		
			</script>';
			
			$disabled ='';
			if($status->isStatiVorhanden($bestellung->bestellung_id, 'Storno') || $status->isStatiVorhanden($bestellung->bestellung_id, 'Abgeschickt') )
				$disabled ='disabled';
			
				
			echo "<input type='submit' value='Speichern' id='btn_submit' name='btn_submit' $disabled></input>\n"; 
			echo "<input type='submit' value='Abschicken' id='btn_abschicken' name='btn_abschicken' $disabled></input>\n"; 
			echo "<br><br>"; 

			if($status->isStatiVorhanden($bestellung->bestellung_id, 'Abgeschickt'))
			{
				echo "Bestellung wurde am ".$date->formatDatum($status->datum,'d.m.Y')." abgeschickt."; 
			}

			// div Aufteilung --> kann ein und ausgeblendet werden
			echo "<br>";
			echo "<a id='aufteilung_link'>Aufteilung</a>\n"; 
			echo "<br>"; 
			echo "<div id='aufteilung'>\n";
			echo "<table border=0 width='65%' class='dark'>"; 
			echo "<tr>\n"; 
			$help = 0; 
			$anteil = 0;
			$summe = 0;
			// alle studiengänge, auch inaktive
			foreach($studiengang->result as $stud)
			{
				$vorhanden = false;
				if($stud->studiengang_kz < 10000)
				{
					if($help%6 == 0)
					{
						echo"</tr><tr>"; 
					}
					foreach($aufteilung->result as $auf)
					{
						// wenn in aufteilung vorhanden
						if(mb_strtoupper($auf->oe_kurzbz) == mb_strtoupper($stud->oe_kurzbz))
						{
							$anteil = $auf->anteil;
							$vorhanden = true;
						}					
					}
					if($stud->aktiv || $vorhanden)
					{
						$summe += $anteil; 
						echo "<td style='text-align:right;'>".mb_strtoupper($stud->oe_kurzbz).":</td> <td><input type='text' size='6' name='aufteilung_$help' onChange='summe_aufteilung()' id='aufteilung_$help' value='".number_format($anteil, 2, ",",".")."'> % </td><input type='hidden' name='oe_kurzbz_$help' value='$stud->oe_kurzbz'>\n";
						$help++;
						$anteil = 0;
					} 
				}
			}
			echo "</tr>"; 
			echo "<tfoot>\n";
			echo '<tr>
					<td><input type="hidden" name="anz_aufteilung" value="'.$help.'"></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td colspan="2" style="text-align:right;">Summe:</td>
					';
			echo "<td><input type='text' size ='6' id='aufteilung_summe' name='aufteilung_summe' value ='".number_format($summe, 2, ",",".")."'>%</td>";
			echo "</tr></tfoot>"; 
			echo "</table>";
			echo "</div>"; 
			echo "<br><br>";
			
						echo '
			<script type="text/javascript">
			
			var anz='.$help.';

			/*
			Berechnet die Prozentuelle Aufteilung
			*/
			function summe_aufteilung()
			{
				var i=0;
				var aufteilung=0;
				var summe = 0; 

				while(i<anz)
				{
					
					aufteilung =$("#aufteilung_"+i).val();
					aufteilung=parseFloat(aufteilung); 
					summe = parseFloat(summe);	
					summe = summe + aufteilung; 

					i=i+1;
				}
				 document.getElementById("aufteilung_summe").value = parseFloat(summe).toFixed(2);
			}
			
			</script>';	
		}
		else 
		{
			// Update auf Bestellung
			$date = new datum(); 	
		//	var_dump($_POST); 
			$save = false; 
			
			$bestellung_id = $_GET['bestellung'];
			$bestellung_new = new wawi_bestellung(); 
			$bestellung_new->load($bestellung_id);
			$bestellung_new_brutto = $bestellung_new->getBrutto($bestellung_id);
			$status = new wawi_bestellstatus(); 
				
			// speichern 
			if(isset($_POST['btn_abschicken']) || isset($_POST['btn_submit']))
			{
				// wenn es status Storno oder Abgeschickt schon gibt, darf nicht gespeichert werden
				if($status->isStatiVorhanden($bestellung_new->bestellung_id, 'Storno') || $status->isStatiVorhanden($bestellung_new->bestellung_id, 'Abgeschickt'))
				{
					echo "Kein Speichern mehr möglich.<br>"; 
					echo "<a href = bestellung.php?method=update&id=".$bestellung_id."> Zurück zur Bestellung </a>";
				}
				else
				{
					$aufteilung_anzahl = $_POST['anz_aufteilung'];
					$bestellung_detail_anz = $_POST['detail_anz'];
	
					$bestellung_new->new = false; 
					$bestellung_new->besteller_uid=$user; 
					if(is_numeric($_POST['filter_konto']))
						$bestellung_new->konto_id = $_POST['filter_konto'];
					else 
						$bestellung_new->konto_id = '';
					$bestellung_new->firma_id = $_POST['firma_id'];
					$bestellung_new->lieferadresse = $_POST['filter_lieferadresse'];
					$bestellung_new->rechnungsadresse = $_POST['filter_rechnungsadresse'];
					$bestellung_new->titel = $_POST['titel'];
					$bestellung_new->bemerkung = $_POST['bemerkung'];
					$bestellung_new->liefertermin = $date->formatDatum($_POST['liefertermin'], 'Y-m-d'); 
					$bestellung_new->updateamum = date('Y-m-d H:i:s');
					$bestellung_new->updatevon = $user; 
					$tags = explode(";", $_POST['tags']);
					$help_tags = new tags(); 
					$help_tags->bestellung_id = $bestellung_id; 
					$help_tags->deleteBestellungTag($tags);
					
					foreach ($tags as $bestelltags)
					{
						$tag_bestellung = new tags(); 
						$tag_bestellung->tag = trim($bestelltags); 
						$tag_bestellung->bestellung_id = $bestellung_id; 
						$tag_bestellung->insertvon = $user; 
						$tag_besetllung->insertamum = date('Y-m-d H:i:s');
						
						if(!$tag_bestellung->TagExists())
						{
							$tag_bestellung->saveTag(); 
							$tag_bestellung->saveBestellungTag();
						}
						else
						{
							if(!$tag_bestellung->BestellungTagExists())
								$tag_bestellung->saveBestellungTag();
						}
					} 
					// letzte leere zeile nicht speichern
					for($i = 1; $i < $bestellung_detail_anz; $i++)
					{
						$detail_id = $_POST["bestelldetailid_$i"]; 
						$bestell_detail = new wawi_bestelldetail(); 		
						
						// gibt es ein bestelldetail schon
						if($detail_id != '')
						{
							// Update
							$bestell_detail->load($detail_id);
							
							$tags_detail = explode(";", $_POST["detail_tag_$i"]);
		
							$help_detailtags = new tags(); 
							$help_detailtags->bestelldetail_id = $detail_id; 
							$help_detailtags->deleteBestelldetailTag($tags_detail);
							
							foreach ($tags_detail as $det)
							{
								$detail_tag = new tags(); 
								$detail_tag->tag = trim($det); 
								$detail_tag->bestelldetail_id = $detail_id; 
								$detail_tag->insertvon = $user; 
								$detail_tag->insertamum = date('Y-m-d H:i:s');
								
								if(!$detail_tag->TagExists())
								{
									$detail_tag->saveTag();
									$detail_tag->saveBestelldetailTag();
								}
								else
								{
									if(!$detail_tag->BestelldetailTagExists())
										$detail_tag->saveBestelldetailTag();
								}
							} 
							
							$bestell_detail->position = $_POST["pos_$i"];
							$bestell_detail->menge = $_POST["menge_$i"];
							$bestell_detail->verpackungseinheit = $_POST["ve_$i"];
							$bestell_detail->beschreibung = $_POST["beschreibung_$i"];
							$bestell_detail->artikelnummer = $_POST["artikelnr_$i"];
							$bestell_detail->preisprove = $_POST["preisprove_$i"];
							$bestell_detail->mwst = $_POST["mwst_$i"];
							$bestell_detail->updateamum = date('Y-m-d H:i:s');
							$bestell_detail->updatevon = $user;
							$bestell_detail->new = false; 
						}
						else 
						{
							// Insert
							$bestell_detail->bestellung_id = $_GET['bestellung'];
							$bestell_detail->position = $_POST["pos_$i"];
							$bestell_detail->menge = $_POST["menge_$i"];
							$bestell_detail->verpackungseinheit = $_POST["ve_$i"];
							$bestell_detail->beschreibung = $_POST["beschreibung_$i"];
							$bestell_detail->artikelnummer = $_POST["artikelnr_$i"];
							$bestell_detail->preisprove = $_POST["preisprove_$i"];
							$bestell_detail->mwst = $_POST["mwst_$i"];
							$bestell_detail->sort = $_POST["pos_$i"];
							$bestell_detail->insertamum = date('Y-m-d H:i:s');
							$bestell_detail->insertvon = $user;
							$bestell_detail->updateamum = date('Y-m-d H:i:s');
							$bestell_detail->updatevon = $user;
							$bestell_detail->new = true; 
						}
						
						if(!$bestell_detail->save())
						{
							echo $bestell_detail->errormsg; 
						}
					}
		
					for($i=0; $i<$aufteilung_anzahl; $i++)
					{
						$aufteilung = new wawi_aufteilung(); 
						$aufteilung->bestellung_id = $bestellung_id;
						$aufteilung->oe_kurzbz = $_POST['oe_kurzbz_'.$i];
						$aufteilung->anteil = $_POST['aufteilung_'.$i]; 
						
						if($aufteilung->AufteilungExists())
						{
							// Update
							$aufteilung->updateamum = date('Y-m-d H:i:s');
							$aufteilung->updatevon = $user; 
							$aufteilung->new = false; 
						}
						else
						{
							// Insert
							$aufteilung->updateamum = date('Y-m-d H:i:s');
							$aufteilung->updatevon = $user; 
							$aufteilung->insertamum = date('Y-m-d H:i:s');
							$aufteilung->insertvon = $user; 
							$aufteilung->new = true; 
						}
						$aufteilung->saveAufteilung(); 
					}
					
					if($bestellung_new->save())
					{
						echo "erfolgreich gespeichert. <br><br>";
						$save = true; 
						$_GET['method']= 'update';
						$_GET['id']= $bestellung_id; 
					}
					echo "<a href = bestellung.php?method=update&id=".$bestellung_id."> Zurück zur Bestellung </a>";		
				}

			}
			// Bestellung freigeben wird in gang gesetzt --> durch Abschick Button
			if(isset($_POST['btn_abschicken']) )
			{	
				// wenn status Storno vorhanden ist kann nicht mehr freigegeben werden
				if($status->isStatiVorhanden($bestellung_new->bestellung_id, 'Storno'))
				{
					echo "Keine Freigabe mehr möglich, da Storniert wurde.<br>"; 
					echo "<a href = bestellung.php?method=update&id=".$bestellung_id."> Zurück zur Bestellung </a>";
				}
				else
				{
						$status_abgeschickt = new wawi_bestellstatus(); 
						if(!$status_abgeschickt->isStatiVorhanden($bestellung_id, 'Abgeschickt'))
						{
							$bestellung_new->load($bestellung_id); 
												
							$status_abgeschickt->bestellung_id = $bestellung_id; ; 
							$status_abgeschickt->bestellstatus_kurzbz ='Abgeschickt'; 
							$status_abgeschickt->uid = $user; 
							$status_abgeschickt->oe_kurzbz = ''; 
							$status_abgeschickt->datum = date('Y-m-d H:i:s'); 
							$status_abgeschickt->insertvon = $user; 
							$status_abgeschickt->insertamum = date('Y-m-d H:i:s'); 
							$status_abgeschickt->updatevon = $user;
							$status_abgeschickt->updateamum = date('Y-m-d H:i:s'); 
		
							if(!$status_abgeschickt->save())
							{
								echo "Fehler beim Setzen auf Status Abgeschickt.";
							}
							// wer ist freigabeberechtigt auf kostenstelle
							$rechte = new benutzerberechtigung();
							$uids = $rechte->getFreigabeBenutzer($bestellung_new->kostenstelle_id, null); 
							foreach($uids as $uid)
							{
								echo $uid; 
								// E-Mail an Kostenstellenverantwortliche senden
								$msg ="$bestellung_new->bestellung_id freigeben. <a href=https://calva.technikum-wien.at/burkhart/fhcomplete/trunk/wawi/index.php?content=bestellung.php&method=update&id=$bestellung_new->bestellung_id> drücken </a>"; 
								$mail = new mail($uid.'@'.DOMAIN, 'no-reply', 'Freigabe Bestellung', $msg);
								$mail->setHTMLContent($msg); 
								if(!$mail->send())
									echo 'Fehler beim Senden des Mails';
								else
									echo '<br> Mail verschickt!';
							}
						}
					}
				}
			
			// kostenstelle gibt frei
			if(isset($_POST['btn_freigabe']) )
			{
				if(!isset($_POST['freigabe_oe']))
				{
					// Kostenstelle gibt frei
					// wenn status Storno vorhanden, soll nicht mehr freigegeben werden. 
					if($status->isStatiVorhanden($bestellung_new->bestellung_id, 'Storno'))
					{
						echo "Keine Freigabe mehr möglich, da Storniert wurde.<br>"; 
						echo "<a href = bestellung.php?method=update&id=".$bestellung_id."> Zurück zur Bestellung </a>";
					}
					else
					{ 
						// Freigabestatus für Kostenstelle
						$bestellung_new->load($bestellung_id); 
						$status = new wawi_bestellstatus(); 
						$status->bestellung_id = $bestellung_new->bestellung_id; 
						$status->bestellstatus_kurzbz = 'Freigabe';
						$status->uid = $user; 
						$status->oe_kurzbz = '';
						$status->datum = date('Y-m-d H:i:s');
						$status->insertvon = $user; 
						$status->insertamum = date('Y-m-d H:i:s');
						$status->updateamum = date('Y-m-d H:i:s'); 
						$status->updatevon = $user; 
						
						if(!$status->save())
						{
							echo "Fehler beim Setzen auf Status Freigabe.<br>"; 
							echo "<a href = bestellung.php?method=update&id=".$bestellung_id."> Zurück zur Bestellung </a>";	
						}
						else 
						{
							echo "<a href = bestellung.php?method=update&id=".$bestellung_id."> Zurück zur Bestellung </a><br>";	
							echo "FREIGABE KOSTENSTELLE erfolgreich";
							
							// wer ist freigabeberechtigt auf nächsthöhere Organisationseinheit
							$oes = array(); 
							$oes = $bestellung_new->FreigabeOe($bestellung_id); 
							$freigabe= false; 
							foreach($oes as $o)
							{
								if(!$status->isStatiVorhanden($bestellung_new->bestellung_id, 'Freigabe', $o))
								{
									$rechte = new benutzerberechtigung();
									$uids = $rechte->getFreigabeBenutzer(null, $o); 
									$freigabe = true; 
									break; 
								}
							}
							if(!$freigabe == false)
							{
								// es wurde noch nicht alles Freigegeben
								foreach($uids as $uid)
								{
									echo $uid; 
									// E-Mail an Kostenstellenverantwortliche senden
									$msg ="$bestellung_new->bestellung_id freigeben. <a href=https://calva.technikum-wien.at/burkhart/fhcomplete/trunk/wawi/index.php?content=bestellung.php&method=update&id=$bestellung_new->bestellung_id> drücken </a>"; 
									$mail = new mail($uid.'@'.DOMAIN, 'no-reply', 'Freigabe Bestellung', $msg);
									$mail->setHTMLContent($msg); 
									if(!$mail->send())
										echo 'Fehler beim Senden des Mails';
									else
										echo '<br> Mail verschickt!';
								}
							}
						}
					}
				}
				else
				{
					// OE gibt frei
					// wenn status Storno vorhanden, soll nicht mehr freigegeben werden. 
					if($status->isStatiVorhanden($bestellung_new->bestellung_id, 'Storno'))
					{
						echo "Keine Freigabe mehr möglich, da Storniert wurde.<br>"; 
						echo "<a href = bestellung.php?method=update&id=".$bestellung_id."> Zurück zur Bestellung </a>";
					}
					else
					{

						// Freigabestatus für Kostenstelle
						$bestellung_new->load($bestellung_id); 
						$status = new wawi_bestellstatus(); 
						$status->bestellung_id = $bestellung_new->bestellung_id; 
						$status->bestellstatus_kurzbz = 'Freigabe';
						$status->uid = $user; 
						$status->oe_kurzbz = $_POST['freigabe_oe'];
						$status->datum = date('Y-m-d H:i:s');
						$status->insertvon = $user; 
						$status->insertamum = date('Y-m-d H:i:s');
						$status->updateamum = date('Y-m-d H:i:s'); 
						$status->updatevon = $user; 
						
						if(!$status->save())
						{
							echo "Fehler beim Setzen auf Status Freigabe.<br>"; 
							echo "<a href = bestellung.php?method=update&id=".$bestellung_id."> Zurück zur Bestellung </a>";	
						}
						else 
						{
							echo "<a href = bestellung.php?method=update&id=".$bestellung_id."> Zurück zur Bestellung </a><br>";	
							echo "FREIGABE OE erfolgreich";
							
							// wer ist freigabeberechtigt auf nächsthöhere Organisationseinheit
							$oes = array(); 
							$oes = $bestellung_new->FreigabeOe($bestellung_id); 
							$freigabe = false; 
							foreach($oes as $o)
							{
								if(!$status->isStatiVorhanden($bestellung_new->bestellung_id, 'Freigabe', $o))
								{
									$rechte = new benutzerberechtigung();
									$uids = $rechte->getFreigabeBenutzer(null, $o); 
									$freigabe = true; 
									break; 
								}
							}
							if(!$freigabe == false)
							{
								// es wurde noch nicht alles Freigegeben
								foreach($uids as $uid)
								{
									// E-Mail an Kostenstellenverantwortliche senden
									$msg ="$bestellung_new->bestellung_id freigeben. <a href=https://calva.technikum-wien.at/burkhart/fhcomplete/trunk/wawi/index.php?content=bestellung.php&method=update&id=$bestellung_new->bestellung_id> drücken </a>"; 
									$mail = new mail($uid.'@'.DOMAIN, 'no-reply', 'Freigabe Bestellung', $msg);
									$mail->setHTMLContent($msg); 
									if(!$mail->send())
										echo 'Fehler beim Senden des Mails';
									else
										echo '<br> Mail verschickt!';
								}
							}
						}
					}
				}
			}
		}
	}

	function getDetailRow($i, $bestelldetail_id='', $menge='', $ve='', $beschreibung='', $artikelnr='', $preisprove='', $mwst='', $brutto='')
	{
		echo "<tr id ='row_$i'>\n";
		echo "<td><a onClick='removeDetail($i, $bestelldetail_id)' title='Bestelldetail löschen'> <img src=\"../skin/images/delete_x.png\"> </a></td>\n";
		echo "<td><input type='text' size='2' name='pos_$i' id='pos_$i' maxlength='2' value='$i'></input></td>\n";
		echo "<td><input type='text' size='5' class='number' name='menge_$i' id='menge_$i' maxlength='7' value='$menge', onChange='calcLine($i);'></input></td>\n";
		echo "<td><input type='text' size='5' name='ve_$i' id='ve_$i' maxlength='7' value='$ve'></input></td>\n";
		echo "<td><input type='text' size='80' name='beschreibung_$i' id='beschreibung_$i' value='$beschreibung'></input></td>\n";
		echo "<td><input type='text' size='15' name='artikelnr_$i' id='artikelnr_$i' maxlength='32' value='$artikelnr'></input></td>\n";
		echo "<td><input type='text' size='15' class='number' name='preisprove_$i' id='preisprove_$i' maxlength='15' value='$preisprove' onblur='checkNewRow($i)' onChange='calcLine($i);'></input></td>\n";
		echo "<td><input type='text' size='8' class='number' name='mwst_$i' id='mwst_$i' maxlength='5' value='$mwst' onChange='calcLine($i);'></input></td>\n";
		echo "<td><input type='text' size='10' class='number' name ='brutto_$i' id='brutto_$i' value='$brutto' disabled></input></td>\n";
		$detail_tag = new tags(); 
		$detail_tag->GetTagsByBestelldetail($bestelldetail_id);
		$help = $detail_tag->GetStringTags(); 
		echo "<td><input type='text' size='10' name='detail_tag_$i' id='detail_tag_$i' style='display:none' value='$help' ></input></td>"; 
		
		echo "	<script type='text/javascript'>
						$('#detail_tag_'+$i).autocomplete('wawi_autocomplete.php', 
						{
							minChars:1,
							matchSubset:1,matchContains:1,
							width:500,
							multiple: true,
							multipleSeparator: '; ',
							extraParams:{'work':'detail_tags', 'detail_id':'.$bestelldetail_id.'}
						});
					</script>";
		
		echo "<td><input type='hidden' size='20' name='bestelldetailid_$i' id='bestelldetailid_$i' value='$bestelldetail_id'></input></td>";
		echo "</tr>\n";
	}
	