<?php
/* Copyright (C) 2010 FH Technikum Wien
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
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
/**
 * Auswertung der Bestellungen und Rechnungen auf Kostenstellen und Tags
 */
require_once('../../config/wawi.config.inc.php');
require_once('../auth.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/wawi_rechnung.class.php');
require_once('../../include/wawi_bestellung.class.php');
require_once('../../include/wawi_kostenstelle.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/tags.class.php');
require_once('../../include/geschaeftsjahr.class.php');
require_once('../../include/datum.class.php');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
$datum_obj = new datum();

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
	<title>WaWi - Tags - Bericht</title>
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
<h1>Bericht - Tags</h1>
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

	$kst_tags=array();
	$tags_array=array();
	$kstIN=$db->implode4SQL($_POST['kst']);
	//Tabelle auf Basis der Bestellungen
	$qry = "SELECT
				(menge*preisprove*(100+mwst)/100) as brutto, tbl_bestellung.bestellung_id,
				tbl_bestellung.kostenstelle_id, tbl_bestelldetail.bestelldetail_id
			FROM
				wawi.tbl_bestellung
				JOIN wawi.tbl_bestelldetail USING(bestellung_id)
			WHERE
				tbl_bestellung.insertamum::date>='$gj->start' AND tbl_bestellung.insertamum::date<='$gj->ende'
				AND
				(
				bestellung_id in (SELECT bestellung_id FROM wawi.tbl_bestellungtag WHERE bestellung_id=tbl_bestellung.bestellung_id)
				OR
				bestelldetail_id in (SELECT bestelldetail_id FROM wawi.tbl_bestelldetailtag WHERE bestelldetail_id=tbl_bestelldetail.bestelldetail_id)
				)
				AND kostenstelle_id IN($kstIN)
			";

	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			//Bestelldetailtags laden
			$tags = new tags();
			$tags->GetTagsByBestelldetail($row->bestelldetail_id);

			if(count($tags->result)==0)
			{
				//Wenn kein Detailtag vorhanden ist, die Tags der Bestellung verwenden
				$tags->GetTagsByBestellung($row->bestellung_id);
			}

			foreach($tags->result as $tag)
			{
				if(!isset($tags_array[$tag->tag]))
					$tags_array[$tag->tag]=0;
				if(isset($kst_tags[$row->kostenstelle_id]) && isset($kst_tags[$row->kostenstelle_id][$tag->tag]))
					$kst_tags[$row->kostenstelle_id][$tag->tag]+=$row->brutto;
				else
					$kst_tags[$row->kostenstelle_id][$tag->tag]=$row->brutto;

			}
		}
	}
	else
		die('Fehler bei Datenbankzugriff');

	echo '<span style="font-size: small">Zeitraum: ',$datum_obj->formatDatum($gj->start,'d.m.Y'),' - ',$datum_obj->formatDatum($gj->ende,'d.m.Y').'</span>';
	echo '<H2>Bestellungen</H2>';

	draw_tag_table($tags_array, $kst_tags,'bestellung', $gj);

	//Tabelle auf Basis der Rechnungen
	$kst_tags=array();
	$tags_array=array();
	$qry = "SELECT
				(betrag*(100+mwst)/100) as brutto, tbl_bestellung.bestellung_id,
				tbl_bestellung.kostenstelle_id
			FROM
				wawi.tbl_bestellung
				JOIN wawi.tbl_rechnung USING(bestellung_id)
				JOIN wawi.tbl_rechnungsbetrag USING(rechnung_id)
			WHERE
				tbl_bestellung.insertamum::date>='$gj->start' AND tbl_bestellung.insertamum::date<='$gj->ende'
				AND bestellung_id in (SELECT bestellung_id FROM wawi.tbl_bestellungtag WHERE bestellung_id=tbl_bestellung.bestellung_id)
				AND kostenstelle_id IN ($kstIN)
			";

	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			//Bestelldetailtags laden
			$tags = new tags();
			$tags->GetTagsByBestellung($row->bestellung_id);

			foreach($tags->result as $tag)
			{
				if(!isset($tags_array[$tag->tag]))
					$tags_array[$tag->tag]=0;
				if(isset($kst_tags[$row->kostenstelle_id]) && isset($kst_tags[$row->kostenstelle_id][$tag->tag]))
					$kst_tags[$row->kostenstelle_id][$tag->tag]+=$row->brutto;
				else
					$kst_tags[$row->kostenstelle_id][$tag->tag]=$row->brutto;

			}
		}
	}
	else
		die('Fehler bei Datenbankzugriff');

	echo '<H2>Rechnungen</H2>';
	draw_tag_table($tags_array, $kst_tags,'rechnung', $gj);
}
else
{
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
<br /><br /><br /><br /><br /><br /><br /><br />
</body>
</html>
<?php
/**
 * Zeichnet eine Tabelle mit Kostenstellen und Tags
 * @param $tags_array Array mit allen vorkommenden Tags
 * @param $kst_tags 2 Dimensionales Array mit Kostenstellen, Tags und Bruttobetrag
 */
function draw_tag_table($tags_array, $kst_tags, $table_id, $gj)
{
	ksort($tags_array);
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
	foreach(array_keys($tags_array) as $tags)
	{
		echo '<th>',$tags,'</th>';
	}
	echo '
				<th>Summe</th>
			</tr>
		</thead>
	<tbody>';


	foreach($kst_tags as $kst=>$tags_value)
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

		foreach(array_keys($tags_array) as $tags)
		{
			if(isset($tags_value[$tags]))
			{
				echo '<td class="number">';
				if($table_id=='bestellung')
					echo '<a href="../bestellung.php?method=suche&submit=1&tag=',$tags,'&filter_kostenstelle=',$kst,'&evon=',$vondatum,'&ebis=',$endedatum,'">';
				echo number_format($tags_value[$tags],2,',','.');
				if($table_id=='bestellung')
					echo '</a>';
				echo '</td>';
				//Kostenstellensumme berechnen
				$kst_summe += $tags_value[$tags];

				//Tagsumme berechnen
				$tags_array[$tags]+=$tags_value[$tags];
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
	$gesamt_summe=0;
	foreach($tags_array as $tags=>$summe)
	{
		$gesamt_summe+=$summe;
		echo '<th class="number">',number_format($summe,2,',','.'),'</th>';
	}
	echo '<th class="number">',number_format($gesamt_summe,2,',','.'),'</th>';
	echo '</tr>
		</tfoot>
	</table>';
}
?>
