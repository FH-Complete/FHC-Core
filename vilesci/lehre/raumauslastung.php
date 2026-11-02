<?php
/* Copyright (C) 2006 Technikum-Wien
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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/**
 *	Raumauslastung
 *
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/raumtyp.class.php');
require_once('../../include/studiensemester.class.php');
require('../../include/globals.inc.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('basis/stundenplan',null,'s') && !$rechte->isBerechtigt('lehre/lvplan',null,'s'))
	die($rechte->errormsg);

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$raum = array();
$werteRaumtyp = array();

// Daten des aktuellen Studiensemesters laden
$studiensemester = new studiensemester();
$studiensemester_akt = $studiensemester->getaktorNearest();
$studiensemester_daten = new studiensemester($studiensemester_akt);

if (isset($_POST['studiensemester']))
	$studiensemester_akt = $_POST['studiensemester'];

if (isset($_POST['datum_beginn']))
	$datum_beginn = $_POST['datum_beginn'];
else
	$datum_beginn = $studiensemester_daten->start;

if (isset($_POST['datum_ende']))
	$datum_ende = $_POST['datum_ende'];
else
	$datum_ende = $studiensemester_daten->ende;

if (isset($_POST['stunde_beginn']))
	$stunde_beginn = $_POST['stunde_beginn'];
else
	$stunde_beginn = 12;

if (isset($_POST['stunde_ende']))
	$stunde_ende = $_POST['stunde_ende'];
else
	$stunde_ende = 16;

if (isset($_POST['stunde_samstag_beginn']))
	$stunde_samstag_beginn = $_POST['stunde_samstag_beginn'];
else
	$stunde_samstag_beginn = 12;

if (isset($_POST['stunde_samstag_ende']))
	$stunde_samstag_ende = $_POST['stunde_samstag_ende'];
else
	$stunde_samstag_ende = 16;

if (isset($_POST['stundenplantable']))
{
	if($_POST['stundenplantable'] == 'stundenplan')
		$stundenplantable = 'tbl_stundenplan';
	elseif($_POST['stundenplantable'] == 'stundenplandev')
		$stundenplantable = 'tbl_stundenplandev';
	else
		die('Invalid table');
}
else
	$stundenplantable = 'tbl_stundenplan';

if (isset($_POST['reservierungen']))
{
	if($_POST['reservierungen'] == 'reservierungen_mit')
		$reservierungen = 'reservierungen_mit';
	elseif($_POST['reservierungen'] == 'reservierungen_ohne')
		$reservierungen = 'reservierungen_ohne';
	else
		die('Fehlerhafte Parameterübergabe');
}
else
	$reservierungen = 'reservierungen_mit';

$raumtypen_arr = array();
if (isset($_POST['raumtypen']))
{
	foreach ($_POST['raumtypen'] AS $key => $value)
	{
		if ($value != '')
			$raumtypen_arr[] = $value;
	}
}

$ts_beginn = mktime(0,0,0,substr($datum_beginn,5,2),substr($datum_beginn,8,2),substr($datum_beginn,0,4));
$ts_ende = mktime(0,0,0,substr($datum_ende,5,2),substr($datum_ende,8,2),substr($datum_ende,0,4));

$wochen = round(($ts_ende-$ts_beginn)/(60*60*24*7));
if ($wochen == 0)
	$wochen = 1;

if (isset($_POST['loadTable']))
{
	//Stundenplandaten holen
	$sql_query = "WITH ferienzeiten AS (
		SELECT DISTINCT
				datum::date,
				EXTRACT(DOW FROM datum) AS dow
			FROM 
				(
					SELECT 
						vondatum AS datum
					FROM 
						lehre.tbl_ferien
					WHERE 
						vondatum BETWEEN ".$db->db_add_param($datum_beginn)." AND ".$db->db_add_param($datum_ende)."
					UNION
					SELECT 
						bisdatum AS datum
					FROM 
						lehre.tbl_ferien
					WHERE 
						bisdatum BETWEEN ".$db->db_add_param($datum_beginn)." AND ".$db->db_add_param($datum_ende)."
					UNION
					SELECT 
						generate_series(
							GREATEST(vondatum, ".$db->db_add_param($datum_beginn)."::DATE), 
							LEAST(bisdatum, ".$db->db_add_param($datum_ende)."::DATE), 
							'1 day'::interval
						) AS datum
					FROM 
						lehre.tbl_ferien
					WHERE 
						vondatum BETWEEN ".$db->db_add_param($datum_beginn)." AND ".$db->db_add_param($datum_ende)."
						OR bisdatum BETWEEN ".$db->db_add_param($datum_beginn)." AND ".$db->db_add_param($datum_ende)."
						OR (vondatum <= ".$db->db_add_param($datum_beginn)." AND bisdatum >= ".$db->db_add_param($datum_ende).")
				) AS dates
	)
	SELECT DISTINCT datum,
					stunde,
					ort_kurzbz, 
					EXTRACT(DOW FROM datum) AS tag, 
					substring(EXTRACT(YEAR FROM datum)::text FROM 3 FOR 2) ||'_'||EXTRACT('week' FROM datum) AS kw, 
					max_person
				FROM lehre.".$stundenplantable." FULL JOIN public.tbl_ort USING (ort_kurzbz)
				WHERE
					datum>=".$db->db_add_param($datum_beginn)."
					AND datum<=".$db->db_add_param($datum_ende)."
					AND CASE WHEN EXTRACT(DOW FROM datum) = 6
						THEN stunde>=".$db->db_add_param($stunde_samstag_beginn)."
							AND stunde<=".$db->db_add_param($stunde_samstag_ende)."
						ELSE stunde>=".$db->db_add_param($stunde_beginn)."
							AND stunde<=".$db->db_add_param($stunde_ende)."
						END
					AND datum NOT IN (SELECT datum FROM ferienzeiten)";
	if (count($raumtypen_arr) > 0)
	{
		$sql_query .= " AND ort_kurzbz IN (SELECT ort_kurzbz FROM public.tbl_ortraumtyp WHERE raumtyp_kurzbz IN ('".implode($raumtypen_arr, '\',\'')."'))";
		// Auch leere Räume ohne verplante Stunden laden
		$sql_query .= " UNION
						SELECT NULL, NULL, ort_kurzbz, NULL, NULL, max_person 
						FROM public.tbl_ortraumtyp 
						JOIN public.tbl_ort USING (ort_kurzbz)
						WHERE raumtyp_kurzbz IN ('".implode($raumtypen_arr, '\',\'')."')";
	}

	if ($reservierungen == 'reservierungen_mit')
	{
		$sql_query .= " 
		UNION
		
		SELECT DISTINCT datum,
						stunde,
						ort_kurzbz, 
						EXTRACT(DOW FROM datum) AS tag, 
						substring(EXTRACT(YEAR FROM datum)::text FROM 3 FOR 2) ||'_'||EXTRACT('week' FROM datum) AS kw,
						max_person
					FROM campus.tbl_reservierung 
					FULL JOIN public.tbl_ort USING (ort_kurzbz)
					WHERE
						datum>=".$db->db_add_param($datum_beginn)."
						AND datum<=".$db->db_add_param($datum_ende)."
						AND CASE WHEN EXTRACT(DOW FROM datum) = 6
							THEN stunde>=".$db->db_add_param($stunde_samstag_beginn)."
								AND stunde<=".$db->db_add_param($stunde_samstag_ende)."
							ELSE stunde>=".$db->db_add_param($stunde_beginn)."
								AND stunde<=".$db->db_add_param($stunde_ende)."
							END
						AND datum NOT IN (SELECT datum FROM ferienzeiten)";
		if (count($raumtypen_arr) > 0)
		{
			$sql_query .= " AND ort_kurzbz IN (SELECT ort_kurzbz FROM public.tbl_ortraumtyp WHERE raumtyp_kurzbz IN ('".implode($raumtypen_arr, '\',\'')."'))";
		}
	}
	$sql_query .= " ORDER BY ort_kurzbz, kw LIMIT 100";
	//echo '<pre>', var_dump($sql_query), '</pre>';
	if (!$result = $db->db_query($sql_query))
	{
		die($db->db_last_error());
	}

	// Anzahl der Wochentage zwischen den Datumswerten ermitteln

	$qry_wochentage = "WITH date_range AS (
		SELECT generate_series(".$db->db_add_param($datum_beginn)."::date, ".$db->db_add_param($datum_ende)."::date, '1 day'::interval) AS day
	),
	weekdays AS (
		SELECT EXTRACT(DOW FROM day) AS dow
		FROM date_range
	)
	SELECT 
		dow,
		COUNT(*) AS count
	FROM weekdays
	GROUP BY dow
	ORDER BY dow;";

	//Aufbereitung Wochentage
	$wochentageArray = array();
	if ($resultWochentage = $db->db_query($qry_wochentage))
	{
		while ($row = $db->db_fetch_object($resultWochentage))
		{
			$wochentageArray[$row->dow] = $row->count;
		}
	}
	else
	{
		die($db->db_last_error());
	}

	//Ferien und Feiertage ermitteln
	$qry_ferien = "SELECT DISTINCT
				datum::date,
				EXTRACT(DOW FROM datum) AS dow
			FROM 
				(
					SELECT 
						vondatum AS datum
					FROM 
						lehre.tbl_ferien
					WHERE 
						vondatum BETWEEN ".$db->db_add_param($datum_beginn)." AND ".$db->db_add_param($datum_ende)."
					UNION
					SELECT 
						bisdatum AS datum
					FROM 
						lehre.tbl_ferien
					WHERE 
						bisdatum BETWEEN ".$db->db_add_param($datum_beginn)." AND ".$db->db_add_param($datum_ende)."
					UNION
					SELECT 
						generate_series(
							GREATEST(vondatum, ".$db->db_add_param($datum_beginn)."::DATE), 
							LEAST(bisdatum, ".$db->db_add_param($datum_ende)."::DATE), 
							'1 day'::interval
						) AS datum
					FROM 
						lehre.tbl_ferien
					WHERE 
						vondatum BETWEEN ".$db->db_add_param($datum_beginn)." AND ".$db->db_add_param($datum_ende)."
						OR bisdatum BETWEEN ".$db->db_add_param($datum_beginn)." AND ".$db->db_add_param($datum_ende)."
						OR (vondatum <= ".$db->db_add_param($datum_beginn)." AND bisdatum >= ".$db->db_add_param($datum_ende).")
				) AS dates";

	//Aufbereitung Ferienzeiten
	if ($resultFerien = $db->db_query($qry_ferien))
	{
		while ($row = $db->db_fetch_object($resultFerien))
		{
			//Wochentage abziehen, wenn es ein Feiertag ist
			$wochentageArray[$row->dow]--;
		}
	}
	else
	{
		die($db->db_last_error());
	}

	//Aufbereitung Variante 1
	while ($row = $db->db_fetch_object($result))
	{
		if (!isset($raum[$row->ort_kurzbz]))
		{
			$raum[$row->ort_kurzbz] = new stdClass();
		}
		$raum[$row->ort_kurzbz]->ort = $row->ort_kurzbz;
		$raum[$row->ort_kurzbz]->personen = $row->max_person;
		if (!isset($raum[$row->ort_kurzbz]->last[$row->tag][$row->stunde]->anzahl))
		{
			$raum[$row->ort_kurzbz]->last[$row->tag][$row->stunde] = new stdClass();
			$raum[$row->ort_kurzbz]->last[$row->tag][$row->stunde]->anzahl = 1;
		}
		else
		{
			$raum[$row->ort_kurzbz]->last[$row->tag][$row->stunde]->anzahl++;
		}

		//Aufbereitung Variante 2
		if (!isset($werteRaumtyp[$row->ort_kurzbz]))
		{
			$werteRaumtyp[$row->ort_kurzbz] = new stdClass();
		}
		$werteRaumtyp[$row->ort_kurzbz]->ort = $row->ort_kurzbz;
		$werteRaumtyp[$row->ort_kurzbz]->personen = $row->max_person;
		if (!isset($werteRaumtyp[$row->ort_kurzbz]->last[$row->kw][$row->stunde]->anzahl))
		{
			$werteRaumtyp[$row->ort_kurzbz]->last[$row->kw][$row->stunde] = new stdClass();
			$werteRaumtyp[$row->ort_kurzbz]->last[$row->kw][$row->stunde]->anzahl = 1;
		}
		else
		{
			$werteRaumtyp[$row->ort_kurzbz]->last[$row->kw][$row->stunde]->anzahl++;
		}
	}
}
?>
<html>
<head>
<title>Raumauslastung</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<!--<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">-->
<link href="../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
	<?php
			include('../../include/meta/jquery.php');
			include('../../include/meta/jquery-tablesorter.php');
	?>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<style>
	/* Hier werden Regeln aus dem vilesci.css übernommen außer das nervige hover-highlighting beim tablesorter*/
	body
	{
		background-color: #F5F5F5;
		font-family: Tahoma, Arial, Helvetica, Verdana, Geneva, sans-serif;
		font-size: small;
		/*IE hack*/
		voice-family: "\"}\"";
		voice-family: inherit;
		font-size: medium;
		/*IE hack end*/
	}
	h2
	{
		color: black;
		padding-top: 2px;
		padding-bottom: 1px;
		white-space:nowrap;
		padding-left: 10px;
		font-size: 120%;
		border-bottom:solid 1px #0086CC;
		font-weight:normal;
	}
	/*td*/
	/*{*/
	/*	margin:	4px;*/
	/*	padding: 0px;*/
	/*	font-size: smaller; !*0.8em;*!*/
	/*}*/
	pre
	{
		font-size: 110%;
	}
	input, textarea, select
	{
		border-width:1px;
		border-color:#999999;
		border-style:solid;
		padding:1px;
	}
	table.tablesorter
	{
		font-size: 12px;
	}
	table.tablesorter tbody td
	{
		padding-right: 2px;
		text-align: right;
	}
	table.tablesorter thead tr th
	{
		min-width: 15px;
		text-align: right;
	}
	.table_feiertage
	{
		border: 1px solid black;
		padding: 2px;
	}
</style>
<script language="JavaScript" type="text/javascript">
	$(document).ready(function()
	{
		// Define the tablesorter widget
			$.tablesorter.addWidget({
				id: 'heatmap',
				format: function(table) {
					var $table = $(table);

					// Apply colors based on fixed range values
					$table.find('tbody td').each(function() {
						var value = parseFloat($(this).text()) || 0;
						var color = getColor(value);
						$(this).css('background-color', color);
					});
				}
			});

		// Apply colors based on fixed range values
		$("#table_farblegende").find('tbody td').each(function() {
			var value = parseFloat($(this).text()) || 0;
			var color = getColor(value);
			$(this).css('background-color', color);
		});

		function getColor(value) {
			var r, g, b, a = 0.7;
			var percent = value / 100; // Convert value to a percentage of the fixed range 0-100

			if (percent < 0.33) {
				// Transition from White (255, 255, 255) to Green (0, 255, 0)
				r = Math.floor(255 - 255 * (percent / 0.33));
				g = 255;
				b = Math.floor(255 - 255 * (percent / 0.33));
			} else if (percent < 0.66) {
				// Transition from Green (0, 255, 0) to Yellow (255, 255, 0)
				r = Math.floor(255 * ((percent - 0.33) / 0.33));
				g = 255;
				b = 0;
			} else {
				// Transition from Yellow (255, 255, 0) to Red (255, 0, 0)
				r = 255;
				g = Math.floor(255 - 255 * ((percent - 0.66) / 0.34));
				b = 0;
			}
			return 'rgba(' + r + ',' + g + ',' + b + ',' + a +')';
		}

		$("#t1").tablesorter(
		{
			sortList: [[0,0],[1,0]],
			widgets: ["filter", "stickyHeaders", "heatmap"]
		});

		$( ".datepicker_datum" ).datepicker({
			 changeMonth: true,
			 changeYear: true,
			 dateFormat: 'yy-mm-dd',
			 showOn: "focus"
			 });

		$("#studiensemester_select").change(function()
		{
			//var index = $(this).prop("selectedIndex");
			//index = index-1;
			var vonDatum = $(this).find(':selected').data('von')
			var bisDatum = $(this).find(':selected').data('bis')
			$("#datum_beginn").val(vonDatum);
			$("#datum_ende").val(bisDatum);
		});
	});
	</script>

</head>
<?php

$raumtypen = new raumtyp();
$raumtypen->getAll();
function sortRaumtypen($a, $b)
{
	$c = strcmp(strtolower($a->beschreibung), strtolower($b->beschreibung));
	return $c;
}
usort($raumtypen->result, "sortRaumtypen");

?>
<form method="POST">
	<p>
	Studienemester: <select id="studiensemester_select" name="studiensemester">
	<?php

		$studiensemesterSelect = new studiensemester();
		$studiensemesterSelect->getPlusMinus(2,5);
			foreach ($studiensemesterSelect->studiensemester AS $row)
			{
				echo '<option value="'.$row->studiensemester_kurzbz.'" data-von="'.$row->start.'" data-bis="'.$row->ende.'"'.($row->studiensemester_kurzbz == $studiensemester_akt ? 'selected="selected"' : '').'>'.$row->studiensemester_kurzbz.'</option>';
			}
	?>
				</select>
	Beginn:<input class="datepicker_datum" id="datum_beginn" name="datum_beginn" value="<?php echo $datum_beginn; ?>" size="9" />
	Ende:<input class="datepicker_datum" id="datum_ende" name="datum_ende" value="<?php echo $datum_ende; ?>" size="9" />
	</p>
	<p>
	Unterrichtseinheit von:<input name="stunde_beginn" value="<?php echo $stunde_beginn; ?>" size="2" />
	bis:<input name="stunde_ende" value="<?php echo $stunde_ende; ?>" size="2" />
		(Optional Samstag abweichend von:<input name="stunde_samstag_beginn" value="<?php echo $stunde_samstag_beginn; ?>" size="2" />
			bis:<input name="stunde_samstag_ende" value="<?php echo $stunde_samstag_ende; ?>" size="2" />)
	</p>
		<p>
	Tabelle:<select name="stundenplantable">
		<option value="stundenplan" <?php echo ($stundenplantable=='tbl_stundenplan'?'selected':'');?>>Stundenplan</option>
		<option value="stundenplandev" <?php echo ($stundenplantable=='tbl_stundenplandev'?'selected':'');?>>StundenplanDEV</option>
	</select>
	Reservierungen:<select name="reservierungen">
			<option value="reservierungen_mit" <?php echo ($reservierungen=='reservierungen_mit'?'selected':'');?>>Mit Reservierungen</option>
			<option value="reservierungen_ohne" <?php echo ($reservierungen=='reservierungen_ohne'?'selected':'');?>>Ohne Reservierungen</option>
		</select>
		</p>
			<p style="display: flex;">
	Raumtypen:<select name="raumtypen[]" multiple="multiple" style="height: 50px;" onmouseover="this.style.height = '200px';" onmouseleave="this.style.height = '50px';">
					<option value="" <?php echo count($raumtypen_arr) === 0 ? 'selected="selected"' : '' ?>>Alle belegten Räume</option>
		<?php
			foreach ($raumtypen->result AS $row)
			{
				echo '<option value="'.$row->raumtyp_kurzbz.'" '.(in_array($row->raumtyp_kurzbz, $raumtypen_arr) ? 'selected="selected"' : '').'>'.$row->beschreibung.'</option>';
			}
		?>
			</select>
			</p>
	<input type="hidden" name="loadTable">
	<input type="submit">
</form>

<?php

if (isset($_POST['loadTable']))
{
	echo '<h2> Raumauslastung vom '.$datum_beginn.' - '.$datum_ende.' ('.$wochen.' Wochen)</h2>';


	echo '<table style="width: 100%"><tbody><tr><td>';
	//Übersichtatabelle über die abgezogenen Feiertage
	echo '<h4>Anzahl abgezogener Feiertage/Ferien</h4>';
	echo '<table class="table_feiertage">
			<thead>
				<tr>
					<th style="text-align: center;">Montage</th>
					<th style="text-align: center;">Dienstage</th>
					<th style="text-align: center;">Mittwoche</th>
					<th style="text-align: center;">Donnerstage</th>
					<th style="text-align: center;">Freitage</th>
					<th style="text-align: center;">Samstage</th>
				</tr>
			</thead>
			<tbody>
			<tr>';
			for ($t=1;$t<7;$t++)
			{
				echo '<td>'.$wochentageArray[$t].'</td>';
			}
	echo '</tr></tbody></table>';
	echo '</td><td>';
	// Farblegende
	echo '<h4>Farblegende</h4>';
	echo '<table id="table_farblegende">
			<tbody>
			<tr>';
			for ($i=1;$i<=100;$i++)
			{
				if ($i == 50)
				{
					echo '</tr><tr>';
				}
				echo '<td>'.$i.'</td>';
			}
	echo '</tr></tbody></table>';
	echo '</td></tr></tbody></table>';

	$variante = 2;
	if ($variante == 1)
	{
		echo '<table id="t1">
			<thead>
			<tr>';
		$span = $stunde_ende - $stunde_beginn + 1;
		$spanSamstag = $stunde_samstag_ende - $stunde_samstag_beginn + 1;

		echo '<th rowspan="2">Ort (Personen)</th>
				<th colspan='."$span".' data-sorter="false" style="text-align: center;">Montag</th>
				<th colspan='."$span".' data-sorter="false" style="text-align: center;">Dienstag</th>
				<th colspan='."$span".' data-sorter="false" style="text-align: center;">Mittwoch</th>
				<th colspan='."$span".' data-sorter="false" style="text-align: center;">Donnerstag</th>
				<th colspan='."$span".' data-sorter="false" style="text-align: center;">Freitag</th>
				<th colspan='."$spanSamstag".' data-sorter="false" style="text-align: center;">Samstag</th>';

		echo '</tr>
					<tr>';

		for ($t = 1; $t < 7; $t++)
		{
			if ($t != 6)
			{
				for ($s = $stunde_beginn; $s <= $stunde_ende; $s++)
				{
					echo '<th data-sorter="false" data-filter="false">'.$s.'</th>';
				}
			}
			else
			{
				for ($s = $stunde_samstag_beginn; $s <= $stunde_samstag_ende; $s++)
				{
					echo '<th data-sorter="false" data-filter="false">'.$s.'</th>';
				}
			}
		}
		echo '	</tr>
			</thead>
			<tbody>';

		foreach ($raum as $ort)
		{
			echo '<tr><td style="text-align: left; white-space: nowrap">'.$ort->ort.' ('.$ort->personen.')</td>';
			for ($t = 1; $t < 7; $t++)
			{
				if ($t != 6)
				{
					for ($s = $stunde_beginn; $s <= $stunde_ende; $s++)
					{
						if (!isset($ort->last[$t][$s]->anzahl))
						{
							$ort->last[$t][$s] = new stdClass();
							$ort->last[$t][$s]->anzahl = 0;
						}
						echo '<td>';
						//echo $ort->last[$t][$s]->anzahl;
						//echo round($ort->last[$t][$s]->anzahl / $wochentageArray[$t] * 100, 0).'%';
						if ($ort->last[$t][$s]->anzahl > 0)
						{
							//echo round(($wochentageArray[$t] / $ort->last[$t][$s]->anzahl) * 100, 0).'%';
							echo round(($ort->last[$t][$s]->anzahl / $wochentageArray[$t]) * 100, 0);
						}
						else
						{
							echo '-';
						}
						echo '</td>';
					}
				}
				else
				{
					for ($s = $stunde_samstag_beginn; $s <= $stunde_samstag_ende; $s++)
					{
						if (!isset($ort->last[$t][$s]->anzahl))
						{
							$ort->last[$t][$s] = new stdClass();
							$ort->last[$t][$s]->anzahl = 0;
						}
						echo '<td>';
						//echo $ort->last[$t][$s]->anzahl;
						//echo round($ort->last[$t][$s]->anzahl / $wochentageArray[$t] * 100, 0).'%';
						if ($ort->last[$t][$s]->anzahl > 0)
						{
							//echo round(($wochentageArray[$t] / $ort->last[$t][$s]->anzahl) * 100, 0).'%';
							echo round(($ort->last[$t][$s]->anzahl / $wochentageArray[$t]) * 100, 0);
						}
						else
						{
							echo '-';
						}
						echo '</td>';
					}
				}
			}
			echo '</tr>';
		}
		echo '</tbody>
				</table>';
	}
	elseif ($variante == 2)
	{
		echo '<table id="t1">
			<thead>
			<tr>';
		$span = $stunde_ende - $stunde_beginn + 1;
		$spanSamstag = $stunde_samstag_ende - $stunde_samstag_beginn + 1;
		//echo '<pre>', var_dump($werteRaumtyp), '</pre>';
		echo '<th>Raumtyp</th>';
		echo '<th>Stunde</th>';
		foreach ($werteRaumtyp as $wert)
		{
			foreach ($wert->last AS $woche => $value)
			{
				echo '<th data-sorter="false" style="text-align: center;">'.$woche.'</th>';
			}
		}

		echo '</tr>
			</thead>
			<tbody>';

		foreach ($werteRaumtyp as $wert)
		{
			//echo '<td rowspan="16" style="text-align: left; white-space: nowrap">SEM</td>';
			echo '<tr>';

				echo '<td rowspan="16" style="text-align: left; white-space: nowrap">SEM</td>';
			foreach ($wert->last as $woche)
			{
				//echo '<pre>', var_dump($woche), '</pre>';
				for ($s = 1; $s <= 16; $s++)
				{
					if (isset($woche[$s]->anzahl))
										echo '<td>'.$woche[$s]->anzahl.'</td>';
									else
										echo '<td>-</td>';
				}

			}
			echo '</tr>';


			/*for ($t = 1; $t < 7; $t++)
			{
				for ($s = 1; $s <= 16; $s++)
				{
					if (!isset($ort->last[$t][$s]->anzahl))
					{
						$ort->last[$t][$s] = new stdClass();
						$ort->last[$t][$s]->anzahl = 0;
					}
					echo '<td>';
					//echo $ort->last[$t][$s]->anzahl;
					//echo round($ort->last[$t][$s]->anzahl / $wochentageArray[$t] * 100, 0).'%';
					if ($ort->last[$t][$s]->anzahl > 0)
					{
						//echo round(($wochentageArray[$t] / $ort->last[$t][$s]->anzahl) * 100, 0).'%';
						echo round(($ort->last[$t][$s]->anzahl / $wochentageArray[$t]) * 100, 0);
					}
					else
					{
						echo '-';
					}
					echo '</td>';
				}
			}
			echo '</tr>';*/
		}
		echo '</tbody>
				</table>';
	}
}
?>
</body>
</html>
