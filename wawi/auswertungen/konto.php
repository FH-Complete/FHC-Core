<?php
/* Copyright (C) 2011 Technikum-Wien
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
 /**
 * Auswertung der Bestellungen und Rechnungen auf Kostenstellen und Konten
 */
require_once('../../config/wawi.config.inc.php');
require_once('../auth.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/wawi_rechnung.class.php');
require_once('../../include/wawi_bestellung.class.php');
require_once('../../include/wawi_kostenstelle.class.php');
require_once('../../include/wawi_konto.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/tags.class.php');
require_once('../../include/geschaeftsjahr.class.php');
require_once('../../include/datum.class.php');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
$datum_obj = new datum();
$bestellung = new wawi_bestellung();

// Kostenstellen auf Grund der Rechte holen
$kst_array = $rechte->getKostenstelle('wawi/bestellung');
$kst_array = array_merge($kst_array, $rechte->getKostenstelle('wawi/rechnung'));
$kst_array = array_merge($kst_array, $rechte->getKostenstelle('wawi/kostenstelle'));
$kst_array = array_merge($kst_array, $rechte->getKostenstelle('wawi/freigabe'));

$kst_array = array_unique($kst_array);

if(count($kst_array)==0)
	die('Sie benoetigen eine Kostenstellenberechtigung um diese Seite anzuzeigen');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>WaWi - Konto - Bericht</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/wawi.css" type="text/css">
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css">

	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
	<script type="text/javascript" src="../../vendor/jquery-archive/jquery-metadata/jquery.metadata.js"></script>
	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript">
 	function alleMarkieren(checked)
 	{
 	 	checkbox = $(':checkbox').attr('checked',checked);
 	}
	</script>
</head>
<body>
<h1>Bericht - Konto</h1>
<?php

if(isset($_POST['show']))
{
	if(!isset($_POST['kst']))
		die('Sie müssen mindestens eine Kostenstelle auswählen!<br> <a href="#Zurueck" onclick="javascript:history.back()">Zurück</a>');

	$db = new basis_db();

	//Vom Studiensemester
	$geschaeftsjahr = $_POST['geschaeftsjahr'];
	$gj= new geschaeftsjahr();
	$gj->load($geschaeftsjahr);

	$kst_konto=array();
	$konto_array=array();
	$kstIN=$db->implode4SQL($_POST['kst']);
	//Tabelle auf Basis der Bestellungen
	$qry = "SELECT
				tbl_bestellung.bestellung_id, sum (menge*preisprove*(100+COALESCE(mwst,0))/100) as brutto,
				tbl_bestellung.kostenstelle_id, tbl_konto.beschreibung[1], tbl_konto.konto_id
			FROM
				wawi.tbl_bestellung
				JOIN wawi.tbl_bestelldetail USING(bestellung_id)
				JOIN wawi.tbl_konto USING (konto_id)
			WHERE
				tbl_bestellung.insertamum>='$gj->start' AND tbl_bestellung.insertamum<='$gj->ende'
				AND kostenstelle_id IN($kstIN)
			group by tbl_bestellung.bestellung_id, tbl_bestellung.kostenstelle_id, tbl_konto.beschreibung, tbl_konto.konto_id
			order by beschreibung
			";

	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			if(!isset($konto_array[$row->konto_id]))
				$konto_array[$row->konto_id]=0;

			if(isset($kst_konto[$row->kostenstelle_id]) && isset($kst_konto[$row->kostenstelle_id][$row->konto_id]))
			{
				$kst_konto[$row->kostenstelle_id][$row->konto_id]+=$row->brutto;
			}
			else
			{
				 $kst_konto[$row->kostenstelle_id][$row->konto_id]=$row->brutto+0;
			}
		}
	}
	else
		die('Fehler bei Datenbankzugriff');

	echo '<span style="font-size: small">Zeitraum: ',$datum_obj->formatDatum($gj->start,'d.m.Y'),' - ',$datum_obj->formatDatum($gj->ende,'d.m.Y').'</span>';
	echo '<H2>Bestellungen</H2>';

	draw_konto_table($konto_array, $kst_konto,'bestellung', $gj);

	//Tabelle auf Basis der Rechnungen
	$kst_konto=array();
	$konto_array=array();
	$qry = "SELECT
				(betrag*(100+COALESCE(mwst,0))/100) as brutto, tbl_bestellung.bestellung_id, tbl_bestellung.konto_id,
				tbl_bestellung.kostenstelle_id, tbl_konto.beschreibung[1]
			FROM
				wawi.tbl_bestellung
				JOIN wawi.tbl_konto USING(konto_id)
				JOIN wawi.tbl_rechnung USING(bestellung_id)
				JOIN wawi.tbl_rechnungsbetrag USING(rechnung_id)
			WHERE
				tbl_bestellung.insertamum::date>='$gj->start' AND tbl_bestellung.insertamum::date<='$gj->ende'
				AND kostenstelle_id IN ($kstIN)
			order by beschreibung
			";

	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			//Bestelldetailtags laden
			if(!isset($konto_array[$row->konto_id]))
					$konto_array[$row->konto_id]=0;

			if(isset($kst_konto[$row->kostenstelle_id]) && isset($kst_konto[$row->kostenstelle_id][$row->konto_id]))
			{
				$kst_konto[$row->kostenstelle_id][$row->konto_id]+=$row->brutto;

			}
			else
			{
				$kst_konto[$row->kostenstelle_id][$row->konto_id]=$row->brutto;

			}
		}
	}
	else
		die('Fehler bei Datenbankzugriff');

	echo '<H2>Rechnungen</H2>';

	draw_konto_table($konto_array, $kst_konto,'rechnung', $gj);
}else {

	$kostenstelle = new wawi_kostenstelle();

	$kostenstelle->loadArray($kst_array, 'bezeichnung',false);
	echo 'Bitte markieren sie die Kostenstellen die auf der Auswertung aufscheinen sollen:<br /><br />
	<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
		<table>

		<tbody>';
	$anzahl=0;
	$gesamt = count($kst_array);
	echo '<tr><td valign="top"><table>';

	foreach($kostenstelle->result as $kst)
	{
		if($anzahl%(($gesamt/3)+1)==0 && $anzahl!=0)
		{
			echo '</table></td><td valign="top"><table>';
		}
		if($kst->aktiv)
			$class='';
		else
			$class='class="inaktiv"';
		echo '<tr>
				<td><input type="checkbox" name="kst[]" value="'.$kst->kostenstelle_id.'"></td>
				<td nowrap '.$class.'>'.$kst->bezeichnung.' </td>
			</tr>';
		$anzahl++;
	}
	echo '</table></td></tr>';

	echo '</tbody>
	</table>
	<br />
	<table>
	<tr>
		<td><input type="checkbox" name="allemarkieren" onclick="alleMarkieren(this.checked)"></td>
		<td>Alle markieren</td>
	</tr>
	</table>
	<br />
	Geschäftsjahr
	<SELECT name="geschaeftsjahr" >';
	$gj = new geschaeftsjahr();
	$geschaeftsjahr = $gj->getakt();

	$gj->getAll();

	foreach ($gj->result as $gjahr)
	{
		if($gjahr->geschaeftsjahr_kurzbz==$geschaeftsjahr)
			$selected='selected';
		else
			$selected='';
		echo '<option value="'.$gjahr->geschaeftsjahr_kurzbz.'" '.$selected.'>'.$gjahr->geschaeftsjahr_kurzbz.'</option>';
	}
	echo '
	</SELECT>
	<br />
	<br />
	<input type="submit" value="Anzeigen" name="show">
	</form>';
}
?>

</body>
</html>


<?php

/**
 * Zeichnet eine Tabelle mit Kostenstellen und Konten
 * @param $konto_array Array mit allen vorkommenden Konten
 * @param $kst_konto 2 Dimensionales Array mit Kostenstellen, Konten und Bruttobetrag
 */
function draw_konto_table($konto_array, $kst_konto, $table_id, $gj)
{
	ksort($kst_konto);

	$vondatum = $gj->start;
	$endedatum = $gj->ende;

	echo '
	<script type="text/javascript">
	$(document).ready(function()
	{
		$("#'.$table_id.'").tablesorter(
		{
			sortList: [[0,0]],
			widgets: [\'zebra\']
		});
 	});
 	</script>
	<table class="tablesorter" id="'.$table_id.'">
		<thead>
			<tr>
				<th>Kostenstelle</th>';
	foreach(array_keys($konto_array) as $konten)
	{
		$konto = new wawi_konto();
		$konto->load($konten);
		echo '<th>',$konto->kurzbz,'</th>';
	}
	echo '
				<th>Summe</th>
			</tr>
		</thead>
	<tbody>';


	foreach($kst_konto as $kst=>$konten_value)
	{
		$kst_summe=0;

		$kostenstelle = new wawi_kostenstelle();
		$kostenstelle->load($kst);
		echo '<tr>';
		if($kostenstelle->aktiv)
			$class='';
		else
			$class='class="inaktiv"';
		echo '<td '.$class.'>'.$kostenstelle->bezeichnung.'</td>';

		foreach(array_keys($konto_array) as $konten)
		{
			if(isset($konten_value[$konten]))
			{
				echo '<td class="number">';
				if($table_id=='bestellung')
					echo '<a href="../bestellung.php?method=suche&submit=1&filter_konto=',$konten,'&filter_kostenstelle=',$kst,'&evon=',$vondatum,'&ebis=',$endedatum,'">';
				echo number_format($konten_value[$konten],2,',','.');
				if($table_id=='bestellung')
					echo '</a>';
				echo '</td>';
				//Kostenstellensumme berechnen
				$kst_summe += $konten_value[$konten];

				//Kontensumme berechnen
				settype($konto_array[$konten],'float');
				settype($konten_value[$konten], 'float');
				$konto_array[$konten] +=$konten_value[$konten];

			}
			else
				echo '<td>&nbsp;</td>';
		}
		echo '<td class="number">',number_format($kst_summe,2,',','.'),'</td>';
		echo '</tr>';
	}
	echo '</tbody>
		 <tfoot>
		 	<tr>
		 		<th>Summe</th>';
	settype($gesamt_summe, 'float');
	$gesamt_summe = 0;
	foreach($konto_array as $konten=>$summe)
	{
		settype($summe, 'float');
		$gesamt_summe+=$summe;
		echo '<th class="number">',number_format($summe,2,',','.'),'</th>';
	}
	echo '<th class="number">',number_format($gesamt_summe,2,',','.'),'</th>';
	echo '</tr>
		</tfoot>
	</table>';
}

?>
