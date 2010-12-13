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
require_once('../include/wawi_bestellung.class.php');
require_once('../include/benutzerberechtigung.class.php');

$aktion ='';
if (isset($_GET['method']))
	$aktion = $_GET['method'];
else 
	$aktion = 'suche';

if(isset($_POST['getBetragRow']) && isset($_POST['id']))
{
	if(is_numeric($_POST['id']))
	{
		echo getBetragRow($_POST['id']);
		exit;
	}
	else
	{
		die('ID ungueltig');
	}
}
	
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
	<script type="text/javascript" src="../include/js/jquery.ui.datepicker-de.js"></script> 
		
	<script type="text/javascript">
	function loadFirma(id)
	{
		$.post("bestellung.php", {id: id, getFirma: 'true'},
		function(data){
			$('#firma').html(data);
		});
	}

	function conf_del()
	{
		return confirm('Wollen Sie diese Rechnung wirklich löschen?');
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
	if(!$rechte->isBerechtigt('wawi/rechnung',null,'s'))
		die('Sie haben keine Berechtigung fuer diese Seite');
	
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
						<th>Bestell_Nr</th>
						<th>Rechnungsdatum</th>
						<th>Buchungstext</th>
						<th>Brutto</th>
						<th>Freigegeben</th>
						<th>Letzte Änderung</th>
					  </tr></thead><tbody>\n";
				$brutto_gesamt=0;
				foreach($rechnung->result as $row)
				{	
					$obj = new wawi_rechnung();
					$brutto = $obj->getBrutto($row->rechnung_id);
					$brutto = round($brutto,2);
					$brutto_gesamt +=$brutto;
					//Zeilen der Tabelle ausgeben
					echo "<tr>\n";
					echo "<td nowrap> 
							<a href= \"rechnung.php?method=update&id=$row->rechnung_id\" title=\"Bearbeiten\"> <img src=\"../skin/images/edit.gif\"> </a>
							<a href=\"rechnung.php?method=delete&id=$row->rechnung_id\" onclick='return conf_del()' title='Löschen'> <img src=\"../skin/images/delete.gif\"></a>";
					echo '<td>'.$row->rechnungsnr."</td>\n";
					echo '<td>'.$row->bestell_nr."</td>\n";
					echo '<td>'.$date->formatDatum($row->rechnungsdatum, 'd.m.Y')."</td>\n";
					echo '<td>'.$row->buchungstext."</td>\n";
					echo '<td class="number">'.number_format($brutto,2,".","")."</td>\n";
					echo '<td>'.$freigegeben=($row->freigegeben=='t')?'ja':'nein'."</td>\n"; 
					echo '<td>'.$date->formatDatum($row->updateamum,'d.m.Y H:i:s').' '.$row->updatevon ."</td>\n"; 
					echo "</tr>\n";
				}
				echo '</tbody>
					<tfoot>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th>Summe</th>
						<th class="number">'.number_format($brutto_gesamt,2).'</th>
						<th></th>
						<th></th>
					</table>';	
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
	if(!$rechte->isBerechtigt('wawi/rechnung',null,'sui'))
		die('Sie haben keine Berechtigung zum Anlegen von Rechnungen');

	echo '<h1>Rechnung Neu</h1>';
	echo '<form action="rechnung.php" method="GET">';
	echo '<input type="hidden" name="method" value="update"/>';
	echo '<SELECT name="kostenstelle_id">';
	$kostenstelle = new wawi_kostenstelle();
	$kostenstelle->loadArray($rechte->getKostenstelle('wawi/rechnung'));
	
	foreach($kostenstelle->result as $row)
	{
		echo '<option value="'.$row->kostenstelle_id.'">'.$row->bezeichnung.'</option>';
	}
	echo '</SELECT>';
	echo '<input type="submit" name="submit" value="Weiter"/>';
	echo '</form>';
	
}
elseif($aktion == 'save')
{
	if(!$rechte->isBerechtigt('wawi/rechnung',null,'su'))
		die('Sie haben keine Berechtigung zum Speichern der Rechnungen');
	
	if(isset($_POST['rechnung_id']) 
	&& isset($_POST['rechnungsnummer'])
	&& isset($_POST['buchungstext'])
	&& isset($_POST['rechnungsdatum'])
	&& isset($_POST['bestellung_id'])
	&& isset($_POST['rechnungstyp_kurzbz'])
	&& isset($_POST['buchungsdatum']))
	{
		$rechnung_id = $_POST['rechnung_id'];
		$rechnungsnummer = $_POST['rechnungsnummer'];
		$buchungstext = $_POST['buchungstext'];
		$rechnungsdatum = $_POST['rechnungsdatum'];
		$bestellung_id = $_POST['bestellung_id'];
		$buchungsdatum = $_POST['buchungsdatum'];
		$rechnungstyp_kurzbz = $_POST['rechnungstyp_kurzbz'];
		
		foreach($_POST as $key=>$value)
		{
			if(mb_strstr($key, 'rechnungsbetrag_id_'))
			{
				$id = mb_substr($key, mb_strlen('rechnungsbetrag_id_'));
				$betraege[$id]['id']=$_POST['rechnungsbetrag_id_'.$id];
				$betraege[$id]['bezeichnung']=$_POST['bezeichnung_'.$id];
				$betraege[$id]['betrag']=$_POST['betrag_'.$id];
				$betraege[$id]['mwst']=$_POST['mwst_'.$id];
			}
		}
		
		$rechnung = new wawi_rechnung();
		if($rechnung_id!='')
		{
			//Update
			if(!$rechnung->load($rechnung_id))
				die('Rechnung wurde nicht gefunden');
		}
		else
		{
			//Neue Rechnung
			$rechnung->new = true;
			$rechnung->insertamum = date('Y-m-d');
			$rechnung->insertvon = $user;
			$rechnung->freigegeben = false;
		}	
		$rechnung->rechnungsnr = $rechnungsnummer;
		$rechnung->buchungstext = $buchungstext;
		$rechnung->rechnungsdatum = $date->formatDatum($rechnungsdatum);
		$rechnung->buchungsdatum = $date->formatDatum($buchungsdatum);
		$rechnung->bestellung_id = $bestellung_id;
		$rechnung->updateamum = date('Y-m-d H:i:s');
		$rechnung->updatevon = $user;
		$rechnung->rechnungstyp_kurzbz = $rechnungstyp_kurzbz;
		
		if(isset($_POST['transfer_datum']) && $rechte->isBerechtigt('wawi/rechnung_transfer', null, 'suid'))
			$rechnung->transfer_datum = $date->formatDatum($_POST['transfer_datum']);
		
		if($rechnung->save())
		{
			foreach($betraege as $row)
			{
				if($row['id']=='' && $row['betrag']=='' && $row['mwst']=='' && $row['bezeichnung']=='')
					continue;
									
				$rb = new wawi_rechnung();
				
				//Leere Zeilen werden geloescht
				if($row['betrag']=='' && $row['mwst']=='' && $row['bezeichnung']=='')
				{
					$rb->delete_betrag($row['id']);
				}
				else
				{
					//Speichern der Zeile
					$rb->rechnungsbetrag_id=$row['id'];
					$rb->rechnung_id = $rechnung->rechnung_id;
					$rb->betrag = $row['betrag'];
					$rb->bezeichnung = $row['bezeichnung'];
					$rb->mwst = $row['mwst'];
					if($row['id']=='')
						$rb->new=true;
					else
						$rb->new=false;
					
					$rb->save_betrag();
				}
			}
			
			echo 'Daten wurden gespeichert!';
			$_GET['id']=$rechnung->rechnung_id;
			$aktion = 'update';
		}
		else
		{
			echo 'Fehler: '.$rechnung->errormsg;
		}
	}
	else
		die('Falsche Parameter uebergeben');
} 
elseif($aktion=='delete')
{
	if(!$rechte->isBerechtigt('wawi/rechnung',null,'suid'))
		die('Sie haben keine Berechtigung zum Loeschen von Rechnungen');
	
	if(isset($_GET['id']))
	{
		echo '<h1>Rechnung Löschen</h1>';
		
		$rechnung = new wawi_rechnung();
		if($rechnung->delete($_GET['id']))
		{
			echo 'Rechnung wurde erfolgreich geloescht';
		}
		else
		{
			echo '<span class="error">Fehler: '.$rechnung->errormsg.'</span>';
		}
		echo '<br /><br /><a href="javascript:history.back()">Zurück</a>';
	}
}

if($aktion=='update')
{
	if(!$rechte->isBerechtigt('wawi/rechnung',null,'su'))
		die('Sie haben keine Berechtigung zum Bearbeiten der Rechnungen');
	
	$rechnung = new wawi_rechnung();
	$bestellung = new wawi_bestellung();
	$kostenstelle = new wawi_kostenstelle();
	$konto = new wawi_konto();
	$firma = new firma();
	$oe_kurzbz='';
	
	if(isset($_GET['id']))
	{
		echo '<h1>Rechnung Bearbeiten</h1>';
		$rechnung_id = $_GET['id'];
		if(!is_numeric($rechnung_id))
			die('RechnungID ist ungueltig');
				
		if(!$rechnung->load($rechnung_id))
			die('Rechnung wurde nicht gefunden');
			
		if(!$bestellung->load($rechnung->bestellung_id))
			die('Diese Rechnung ist keiner gueltigen Bestellung zugeordnet');
		$bestellung_id=$bestellung->bestellung_id;
		
		if(!$kostenstelle->load($bestellung->kostenstelle_id))
			die('Die Rechnung bzw Bestellung ist keiner gueltigen Kostenstelle zugeordnet');
		
		if(!$konto->load($bestellung->konto_id))
			die('Die Rechnung bzw Bestellung ist keim gueltigen Konto zugeordnet');
			
		if(!$firma->load($bestellung->firma_id))
			die('Die Rechnung bzw Bestellung ist keiner gueltigen Firma zugeordnet');
		$kostenstelle_id=$bestellung->kostenstelle_id;
		
		echo '<table>
			<tr>
				<td><b>Kostenstelle:</b></td>
				<td>'.$kostenstelle->bezeichnung.'</td>
			</tr>
			<tr>
				<td><b>Konto:</b></td>
				<td>'.$konto->beschreibung[1].'</td>
			</tr>
			<tr>
				<td><b>Firma:</b></td>
				<td>'.$firma->name.'</td>
			</tr>
			</table>';
	}
	elseif(isset($_GET['kostenstelle_id']))
	{
		echo '<h1>Rechnung Neu</h1>';
		$rechnung_id='';
		$bestellung_id='';
		$kostenstelle_id = $_GET['kostenstelle_id'];
	}
	elseif(isset($_GET['bestellung_id']))
	{
		echo '<h1>Rechnung Neu</h1>';
		$bestellung_id=$_GET['bestellung_id'];
		$rechnung_id='';
		if(!$bestellung->load($bestellung_id))
			die('Bestellung existiert nicht');
		$kostenstelle_id=$bestellung->kostenstelle_id;
	}
	else
	{
		die('ungueltige parameter');
	}
	echo '
	<br />
	<form action="'.$_SERVER['PHP_SELF'].'?method=save" method="POST">
	<input type="hidden" name="rechnung_id" value="'.$rechnung->rechnung_id.'">
	<table>
	<tr>
		<td>Rechnungsnummer</td>
		<td>Rechnungsdatum (tt.mm.JJJJ)&nbsp;&nbsp;</td>
		<td>Bestellung</td>
		<td>Typ</td>
	</tr>
	<tr>
		<td><input type="text" name="rechnungsnummer" value="'.$rechnung->rechnungsnr.'"></td>
		<td>
			<input type="text" name="rechnungsdatum" size="10" id="rechnungsdatum" value="'.$date->formatDatum($rechnung->rechnungsdatum,'d.m.Y').'">
			<script type="text/javascript">
				$("#rechnungsdatum" ).datepicker($.datepicker.regional["de"]);
			</script>	
		</td>
		<td>
			<SELECT name="bestellung_id">
			';
	$bestellung = new wawi_bestellung();
	$vondatum = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')-2));
	
	$bestellung->getAllSearch(null, null, null, null, $vondatum, null, null, null, null, null, null, null, $kostenstelle_id);
	
	$vorhanden=false;
	foreach($bestellung->result as $row)
	{
		if($bestellung_id==$row->bestellung_id)
		{
			$selected='selected';
			$vorhanden=true;
		}
		else
			$selected='';
			
		$anzahl=0;
		if(!$row->freigegeben)
			$class='rechnung_nichtfreigegeben';
		else
		{
			$anzahl = $rechnung->count($row->bestellung_id);
			if($anzahl>0)
				$class='rechnung_freigegebenvorhanden';
			else
				$class='rechnung_freigegeben';
		}
		
		echo '<option value="'.$row->bestellung_id.'" '.$selected.' class="'.$class.'">'.$row->bestell_nr.' ('.$anzahl.')</option>';
	}
	if($bestellung_id!='' && !$vorhanden)
	{
		$bestell_obj = new wawi_bestellung();
		$bestell_obj->load($bestellung_id);
		$anzahl = $rechnung->count($bestellung_id);
		echo '<option value="'.$bestell_obj->bestellung_id.'" selected>'.$bestell_obj->bestell_nr.' ('.$anzahl.')</option>';
	}
	echo '</SELECT>
		</td>
		<td>
			<SELECT name="rechnungstyp_kurzbz">';
	$rtyp = new wawi_rechnung();
	$rtyp->getRechnungstyp();
	
	foreach($rtyp->result as $row)
	{
		if($row->rechnungstyp_kurzbz==$rechnung->rechnungstyp_kurzbz)
			$selected='selected';
		else
			$selected='';
		
		echo '<option value="'.$row->rechnungstyp_kurzbz.'" '.$selected.'>'.$row->beschreibung.'</option>';
	}	
	
	$disabled='';
	if(!$rechte->isBerechtigt('wawi/rechnungen_freigeben',null, 'suid'))
		$disabled='disabled="disabled"';
	
	echo '</SELECT>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td valign="top">
			Buchungstext<br />
			<textarea name="buchungstext" rows="4" cols="35">'.$rechnung->buchungstext.'</textarea>
		</td>
		<td valign="top">
			Buchungsdatum (tt.mm.JJJJ)<br />
			<input type="text" name="buchungsdatum" size="10" id="buchungsdatum" value="'.$date->formatDatum($rechnung->buchungsdatum,'d.m.Y').'">
			<script type="text/javascript">
				$("#buchungsdatum" ).datepicker($.datepicker.regional["de"]);
			</script>
			<br /> <br />
			Transferdatum (tt.mm.JJJJ)<br />';
	if(!$rechte->isBerechtigt('wawi/rechnung_transfer',null, 'suid'))
	{
		echo $date->formatDatum($rechnung->transfer_datum,'d.m.Y'); 
	}
	else
	{
		echo '
			<input type="text" name="transfer_datum" size="10" id="transfer_datum" value="'.$date->formatDatum($rechnung->transfer_datum,'d.m.Y').'">
			<script type="text/javascript">
				$("#transfer_datum" ).datepicker($.datepicker.regional["de"]);
			</script>';
	}
	echo '
		</td>
		<td valign="top" colspan="2">
			<table>
			<thead>
				<tr>
					<td>Bezeichnung</td>
					<td>Betrag Netto</td>
					<td>MwSt</td>
					<td>Brutto</td>
				</tr>
			</thead>
			<tbody id="betrag_table">';
	
	
	//Vorhandenen Betraege anzeigen
	$betraege = new wawi_rechnung();
	$betraege->loadBetraege($rechnung->rechnung_id);
	
	$i=0;
	foreach($betraege->result as $row)
	{
		echo getBetragRow($i, $row->rechnungsbetrag_id, $row->bezeichnung, $row->betrag, $row->mwst);
		$i++;
	}
	
	//Unten eine Leere Zeile hinzufuegen
	echo getBetragRow($i);
	
	echo '
			</tbody>
			<tfoot>
			<tr>
				<td>Summe Netto:</td>
				<td class="number" ><span id="netto"></span> &euro;</td>
			</tr>
			<tr>
				<td>Summe Brutto:</td>
				<td class="number" ><span id="brutto"></span> &euro;</td>
			</tr>
			</tfoot>
			</table>
			<script type="text/javascript">
			var anzahlRows='.$i.';
			
			/**
			 * Fuegt eine neue Zeile fuer den Betrag hinzu wenn die 
			 * uebergebene id, die der letzte Zeile ist
			 * und der Betrag eingetragen wurde
			 */
			function checkNewRow(id)
			{
				var betrag="";
				betrag = $("#betrag_"+id).val();
				
				// Wenn der betrag nicht leer ist,
				// und die letzte reihe ist, 
				// dann eine neue Zeile hinzufuegen
				if(betrag.length>0 && anzahlRows==id)
				{
					$.post("rechnung.php", {id: id+1, getBetragRow: "true"},
							function(data){
								$("#betrag_table").append(data);
								anzahlRows=anzahlRows+1;
							});
				}
			}
			
			/**
			 * Brutto und Netto Summen berechnen
			 */
			function summe()
			{
				var i=0;
				var netto=0;
				var brutto=0;
				while(i<=anzahlRows)
				{
					var betrag = $("#betrag_"+i).val();
					var mwst = $("#mwst_"+i).val();
					var brutto_row = $("#brutto_"+i).val();
					betrag = betrag.replace(",",".");
					mwst = mwst.replace(",",".");
					brutto_row = brutto_row.replace(",",".");
					
					if(betrag!="" && mwst!="")
					{
						betrag = parseFloat(betrag);
						mwst = parseFloat(mwst);
						brutto_row = parseFloat(brutto_row);
						netto = netto + betrag;
						
						brutto = brutto + brutto_row;
					}
					i=i+1;
				}
				
				//auf 2 nachkommastellen runden
				netto = Math.round(netto*100)/100;
				brutto = Math.round(brutto*100)/100;
				
				$("#netto").html(netto);
				$("#brutto").html(brutto);
			}
			
			/**
			 * Berechnet den Nettopreis
			 */
			function netto(id)
			{
				var brutto = $("#brutto_"+id).val();
				var mwst = $("#mwst_"+id).val();
				brutto = brutto.replace(",",".");
				mwst = mwst.replace(",",".");
				brutto = parseFloat(brutto);
				mwst = parseFloat(mwst);
				
				if(!isNaN(brutto) && !isNaN(mwst))
				{
					// Nettopreis berechnen
					var netto = brutto/(100+mwst)*100;
					
					//auf 2 Nachkommastellen runden
					netto = Math.round(netto*100)/100;
					
					$("#betrag_"+id).val(netto);
				}
				else
					$("#betrag_"+id).val(0);
			}

			/**
			 * Berechnet den Bruttopreis
			 */
			function brutto(id)
			{
				var netto = $("#betrag_"+id).val();
				var mwst = $("#mwst_"+id).val();
				netto = netto.replace(",",".");
				mwst = mwst.replace(",",".");
				netto = parseFloat(netto);
				mwst = parseFloat(mwst);
				
				if(!isNaN(netto) && !isNaN(mwst))
				{
					// Nettopreis berechnen
					var brutto = netto*(100+mwst)/100;
					
					//auf 2 Nachkommastellen runden
					brutto = Math.round(brutto*100)/100;
					
					$("#brutto_"+id).val(brutto);
				}
				else
					$("#brutto_"+id).val(0);
			}
			
			$(document).ready(function() 
			{
				summe();
			});
			
			function bruttonetto(id)
			{
				var inetto = $("#betrag_"+id).val();
				var ibrutto = $("#brutto_"+id).val();
				
				if(inetto=="" || inetto==0)
				{
					netto(id);
				}
				else
				{
					brutto(id);
				}
			}
			</script>
		</td>
	</tr>
	<tr>
		<td><input type="submit" value="Speichern"/></td>
	</tr>
	</table>	
	';

}

/**
 * 
 * Liefert eine Zeile zum Eintragen des Betrages
 *
 * @param $i Nummer der Zeile
 * @param $rechnungsbetrag_id ID des rechnungsbetrages (optional)
 * @param $bezeichnung Bezeichnung des rechnungsbetrages (optional)
 * @param $betrag Betrag des rechnungsbetrages (optional)
 * @param $mwst MwSt des rechnungsbetrages (optional)
 */
function getBetragRow($i, $rechnungsbetrag_id='', $bezeichnung='', $betrag='', $mwst='')
{
	return '<tr id="row_'.$i.'">
				<td>
					<input type="hidden" name="rechnungsbetrag_id_'.$i.'" value="'.$rechnungsbetrag_id.'">
					<input type="text" name="bezeichnung_'.$i.'" value="'.$bezeichnung.'">
				</td>
				<td>
					<input class="number" type="text" size="12" maxlength="12" id="betrag_'.$i.'" name="betrag_'.$i.'" value="'.$betrag.'"  onblur="checkNewRow('.$i.')" onchange="brutto('.$i.'); summe()"> &euro; 
				</td>
				<td>
					<input class="number" type="text" size="5" maxlength="5" id="mwst_'.$i.'" name="mwst_'.$i.'" value="'.$mwst.'" onchange="bruttonetto('.$i.'); summe(); "> %
				</td>
				<td>
					<input class="number" type="text" size="12" maxlenght="15" id="brutto_'.$i.'" name="brutto_'.$i.'" value="'.($betrag*(100+$mwst)/100).'" onchange="netto('.$i.'); summe();"> &euro;
				</td>
			</tr>';
}
?>
</body>
</html>