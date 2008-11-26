<?php
/* Copyright (C) 2007 Technikum-Wien
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

require_once('../../vilesci/config.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/functions.inc.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Connecten zur DB');

if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
{
	$stsem_obj = new studiensemester($conn);
	$stsem = $stsem_obj->getaktorNext();
}
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	</head>
	<body>';


	echo "<h2>Lektorenstatistik $stsem";
	echo '<span style="position:absolute; right:15px;">'.date('d.m.Y').'</span></h2><br>';
	echo '</h2>';
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">Studiensemester: <SELECT name="stsem">';
	$studsem = new studiensemester($conn);
	$studsem->getAll();

	foreach ($studsem->studiensemester as $stsemester)
	{
		if($stsemester->studiensemester_kurzbz==$stsem)
			$selected='selected';
		else 
			$selected='';
		
		echo '<option value="'.$stsemester->studiensemester_kurzbz.'" '.$selected.'>'.$stsemester->studiensemester_kurzbz.'</option>';
	}
	echo '</SELECT>
		<input type="submit" value="Anzeigen" /></form><br><br>';

if($stsem!='')
{
	if(substr($stsem, 0, 2)=='WS')
	{
		$stsem_obj = new studiensemester($conn);
		$ss = $stsem_obj->getNextFrom($stsem);
		$ws = $stsem;
	}
	else 
	{
		$stsem_obj = new studiensemester($conn);
		$ws = $stsem_obj->getPreviousFrom($stsem);
		$ss = $stsem;
	}
	echo "<table class='liste table-stripeclass:alternate table-autostripe'>
				<thead>
					<tr>
						<th></th>
						<th colspan=2>Anzahl</th>
						<th colspan=2>ALVS</th>
					</tr>
					<tr>
						<th>Institute</th>
						<th>fix</th>
						<th>extern</th>
						<th>$ws</th>
						<th>$ss</th>
					</tr>
				</thead>
				<tbody>
				
			 ";
	//Bachelor
	$qry = "SELECT 
				bezeichnung, 
				(SELECT count(*) FROM (SELECT distinct mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrfach USING(lehrfach_id) JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid) WHERE studiensemester_kurzbz in('$ws','$ss') AND fachbereich_kurzbz=a.fachbereich_kurzbz AND fixangestellt) a) as fix,
				(SELECT count(*) FROM (SELECT distinct mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrfach USING(lehrfach_id) JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid) WHERE studiensemester_kurzbz in('$ws','$ss') AND fachbereich_kurzbz=a.fachbereich_kurzbz AND NOT fixangestellt) a) as extern,
				(SELECT sum(semesterstunden) FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrfach USING(lehrfach_id) WHERE studiensemester_kurzbz='$ws' AND fachbereich_kurzbz=a.fachbereich_kurzbz) as ws,
				(SELECT sum(semesterstunden) FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrfach USING(lehrfach_id) WHERE studiensemester_kurzbz='$ss' AND fachbereich_kurzbz=a.fachbereich_kurzbz) as ss
			FROM public.tbl_fachbereich a WHERE aktiv ORDER BY bezeichnung";
	/*
	Mitarbeiter laut institutszuordnung
	(SELECT count(*) FROM public.tbl_benutzerfunktion JOIN public.tbl_mitarbeiter on (uid=mitarbeiter_uid) WHERE fachbereich_kurzbz=a.fachbereich_kurzbz AND funktion_kurzbz='Institut' AND fixangestellt AND aktiv) as fix,
	(SELECT count(*) FROM public.tbl_benutzerfunktion JOIN public.tbl_mitarbeiter on (uid=mitarbeiter_uid) WHERE fachbereich_kurzbz=a.fachbereich_kurzbz AND funktion_kurzbz='Institut' AND NOT fixangestellt AND aktiv) as extern,
	*/
	//echo '<pre>'.$qry.'</pre><br><br>';
	if($result = pg_query($conn, $qry))
	{
		
		$gesamt_fix=0;
		$gesamt_extern=0;
		$gesamt_ws=0;
		$gesamt_ss=0;
		while($row = pg_fetch_object($result))
		{
			if($row->fix==0 && $row->extern==0)
			{
				continue;
			}
			echo '<tr>';
			echo "<td>$row->bezeichnung</td>";
			echo "<td align='center'>$row->fix</td>";
			echo "<td align='center'>$row->extern</td>";
			echo "<td align='center'>$row->ws</td>";
			echo "<td align='center'>$row->ss</td>";
			echo "</tr>";
			$gesamt_fix+=$row->fix;
			$gesamt_extern+=$row->extern;
			$gesamt_ws+=$row->ws;
			$gesamt_ss+=$row->ss;
		}
		echo '<tr>';
		echo '<td><b>SUMME</b></td>';
		echo "<td align='center'><b>$gesamt_fix</b></td>";
		echo "<td align='center'><b>$gesamt_extern</b></td>";
		echo "<td align='center'><b>$gesamt_ws</b></td>";
		echo "<td align='center'><b>$gesamt_ss</b></td>";
		echo "</tr>";
		
	}
	echo '</tbody></table>';
}
?>
</body>
</html>