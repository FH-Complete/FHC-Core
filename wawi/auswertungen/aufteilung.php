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
 * Auswertung der Bestellungen und Rechnungen auf Kostenstellen
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

$kst_array = $rechte->getKostenstelle();

if(count($kst_array)==0)
	die('Sie benoetigen eine Kostenstellenberechtigung um diese Seite anzuzeigen');

$datum_obj = new datum();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>WaWi - Kostenstelle - Auswertung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	
	<link rel="stylesheet" href="<?php echo APP_ROOT; ?>../skin/jquery.css" type="text/css">
	<link rel="stylesheet" href="<?php echo APP_ROOT; ?>../include/meta/tablesort.php" type="text/css">
	<link rel="stylesheet" href="<?php echo APP_ROOT; ?>../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="<?php echo APP_ROOT; ?>../skin/wawi.css" type="text/css">
	
			
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script> 
	<script type="text/javascript">
 	function alleMarkieren(checked)
 	{
 	 	inputs = document.getElementsByTagName('input');

 	 	for each(i in inputs)
 	 	{
 	 	 	if(i.type=='checkbox')
 	 	 	{
 	 	 	 	i.checked=checked;
 	 	 	}
 	 	}
 	}
	</script>
</head>
<body>
<h1>Bericht - Aufteilung</h1>
<?php

	$db = new basis_db();
	
	//Vom Studiensemester
	
	echo '
	<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
	Geschäftsjahr
	<SELECT name="geschaeftsjahr" >';
	$gj = new geschaeftsjahr();
	
	$geschaeftsjahr = isset($_POST['geschaeftsjahr'])?$_POST['geschaeftsjahr']:$gj->getakt();	
	
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
	<input type="submit" value="Anzeigen" name="show">
	</form>';

	$gj= new geschaeftsjahr();
	$gj->load($geschaeftsjahr);

	$kstIN=$db->implode4SQL($kst_array);
	
	echo '<span style="font-size: small">Zeitraum: ',$datum_obj->formatDatum($gj->start,'d.m.Y'),' - ',$datum_obj->formatDatum($gj->ende,'d.m.Y').'</span>';
	echo '
	<script type="text/javascript">
	$(document).ready(function() 
		{
			$("#myTable").tablesorter(
			{
				sortList: [[1,0]],
				widgets: ["zebra"]
			});			
	 	});
	 </script>';
	echo '<table id="myTable" class="tablesorter" style="width: auto;">
			<thead>
				<tr>
					<th>OE</th>
					<th>Bezeichnung</th>
					<th>Brutto</th>
				</tr>
			</thead>
			<tbody>';
	
	//Tabelle auf Basis der Bestellungen
	$qry = "
			SELECT 
				oe_kurzbz, tbl_organisationseinheit.bezeichnung, anteil_brutto, tbl_organisationseinheit.organisationseinheittyp_kurzbz
			FROM
				(
				SELECT 
					oe_kurzbz, sum(brutto/100*anteil) as anteil_brutto
				FROM 
					(
					SELECT 
						tbl_bestellung.bestellung_id, 
						sum(tbl_bestelldetail.menge*tbl_bestelldetail.preisprove/100*(100+tbl_bestelldetail.mwst)) as brutto
					FROM
						wawi.tbl_bestellung 
						JOIN wawi.tbl_bestelldetail USING(bestellung_id)
					WHERE
						tbl_bestellung.insertamum>='$gj->start' AND tbl_bestellung.insertamum<'$gj->ende'
						AND kostenstelle_id IN($kstIN)
					GROUP BY bestellung_id
					) bestellung
					JOIN wawi.tbl_aufteilung USING(bestellung_id)
				GROUP BY oe_kurzbz
				) a
				JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
			";

	$summe = 0;
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			echo '<tr>';
			echo '<td>',$row->oe_kurzbz,'</td>';
			echo '<td>',$row->organisationseinheittyp_kurzbz,' ',$row->bezeichnung,'</td>';
			echo '<td class="number">',number_format($row->anteil_brutto,2,',','.'),'</td>';
			echo '</tr>';
			echo "\n";
			$summe += $row->anteil_brutto;
		}
	}
	else
		die('Fehler bei Datenbankzugriff');

	echo '</tbody>';
	echo '<tfoot>';
	echo '<tr>';
	echo '<th></th>';
	echo '<th></th>';
	echo '<th>',number_format($summe,2,',','.'),'</th>';
	echo '</tfoot>';
	echo '</table>';
?>
<br /><br /><br /><br /><br /><br /><br /><br />
</body>
</html>
