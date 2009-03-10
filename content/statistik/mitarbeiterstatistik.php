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
/*
 * Generiert eine Liste mit den Institutszuordnungen der Mitarbeiter
 * und einer aufschluesselung ob diese Fixangestellt sind
 */
require_once('../../vilesci/config.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/functions.inc.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Connecten zur DB');

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	</head>
	<body>';


	echo "<h2>Mitarbeiterstatistik (Hauptzuordnung)";
	echo '<span style="position:absolute; right:15px;">'.date('d.m.Y').'</span></h2><br>';
	echo '</h2>';
	
	echo '<br><br>';
	
	echo "<table class='liste table-stripeclass:alternate table-autostripe'>
				<thead>
					<tr>
						<th></th>
						<th colspan=2>Anzahl</th>
					</tr>
					<tr>
						<th>Institute</th>
						<th>fix</th>
						<th>extern</th>
					</tr>
				</thead>
				<tbody>
				
			 ";
	//Bachelor
	$qry = "SELECT 
				bezeichnung, 
				(SELECT count(*) FROM (SELECT distinct uid FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) WHERE fachbereich_kurzbz=a.fachbereich_kurzbz AND fixangestellt AND funktion_kurzbz='oezuordnung' AND aktiv) a) as fix,
				(SELECT count(*) FROM (SELECT distinct uid FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) WHERE fachbereich_kurzbz=a.fachbereich_kurzbz AND NOT fixangestellt AND funktion_kurzbz='oezuordnung' AND aktiv) a) as extern
			FROM public.tbl_fachbereich a WHERE aktiv ORDER BY bezeichnung";
	
	if($result = pg_query($conn, $qry))
	{
		
		$gesamt_fix=0;
		$gesamt_extern=0;

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
			echo "</tr>";
			$gesamt_fix+=$row->fix;
			$gesamt_extern+=$row->extern;
		}
		
		$qry = "SELECT 
					(SELECT count(*) FROM campus.vw_mitarbeiter WHERE uid NOT in(SELECT uid FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='oezuordnung') AND aktiv AND fixangestellt) as fix,
					(SELECT count(*) FROM campus.vw_mitarbeiter WHERE uid NOT in(SELECT uid FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='oezuordnung') AND aktiv AND NOT fixangestellt) as extern
				";
		if($result = pg_query($conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				echo '<tr>';
				echo "<td>Nicht zugeordnet</td>";
				echo "<td align='center'>$row->fix</td>";
				echo "<td align='center'>$row->extern</td>";
				echo "</tr>";
				$gesamt_fix += $row->fix;
				$gesamt_extern += $row->extern;
			}
		}
		echo '<tr>';
		echo '<td><b>SUMME</b></td>';
		echo "<td align='center'><b>$gesamt_fix</b></td>";
		echo "<td align='center'><b>$gesamt_extern</b></td>";
		echo "</tr>";
		
	}
	echo '</tbody></table>';
?>
</body>
</html>