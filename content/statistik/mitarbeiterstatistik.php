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
 * Bei einem klick auf das Institut wird die Detailansicht angezeigt, in der die einzelnen
 * Lektoren Namentlich aufscheinen.
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/fachbereich.class.php');

$db = new basis_db();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	</head>
	<body>';

if(isset($_GET['details']) && isset($_GET['fachbereich_kurzbz']))
{
	$fachbereich = new fachbereich();
	if(!$fachbereich->load($_GET['fachbereich_kurzbz']))
		die('Institut existiert nicht');
	
	echo "<h2>Mitarbeiterstatistik (Hauptzuordnung) - ".$fachbereich->bezeichnung.'</h2>';
	$qry = "SELECT distinct uid, nachname, vorname, titelpre, titelpost 
			FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) 
			WHERE oe_kurzbz='".addslashes($fachbereich->oe_kurzbz)."' 
			AND fixangestellt AND funktion_kurzbz='oezuordnung' AND aktiv 
			ORDER BY nachname, vorname";
	
	if($result = $db->db_query($qry))
	{
		echo "Fixangestellt - Anzahl: ".$db->db_num_rows($result)."
			<table class='liste table-stripeclass:alternate table-autostripe'>
					<thead>
						<tr>
							<th>TitelPre</th>
							<th>Nachname</th>
							<th>Vorname</th>
							<th>Titelpost</th>
						</tr>
					</thead>
					<tbody>
				 ";
		while($row = $db->db_fetch_object($result))
		{
			echo '<tr>';
			echo "<td>$row->titelpre</td>";
			echo "<td>$row->nachname</td>";
			echo "<td>$row->vorname</td>";
			echo "<td>$row->titelpost</td>";
			echo "</tr>";
		}		
	}
	echo '</tbody></table>';
	
	$qry = "SELECT distinct uid, nachname, vorname, titelpre, titelpost 
			FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) 
			WHERE oe_kurzbz='".addslashes($fachbereich->oe_kurzbz)."' 
			AND NOT fixangestellt AND funktion_kurzbz='oezuordnung' AND aktiv
			ORDER BY nachname, vorname";
	
	if($result = $db->db_query($qry))
	{
		echo "<br /><br />Freiangestellt - Anzahl: ".$db->db_num_rows($result)."
			<table class='liste table-stripeclass:alternate table-autostripe'>
					<thead>
						<tr>
							<th>TitelPre</th>
							<th>Nachname</th>
							<th>Vorname</th>
							<th>Titelpost</th>
						</tr>
					</thead>
					<tbody>
				 ";
		while($row = $db->db_fetch_object($result))
		{
			echo '<tr>';
			echo "<td>$row->titelpre</td>";
			echo "<td>$row->nachname</td>";
			echo "<td>$row->vorname</td>";
			echo "<td>$row->titelpost</td>";
			echo "</tr>";
		}		
	}
	echo '</tbody></table>';
}
else 
{
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
	
	$qry = "SELECT 
				bezeichnung, fachbereich_kurzbz,
				(SELECT count(*) FROM (SELECT distinct uid FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) WHERE oe_kurzbz=a.oe_kurzbz AND fixangestellt AND funktion_kurzbz='oezuordnung' AND aktiv) a) as fix,
				(SELECT count(*) FROM (SELECT distinct uid FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) WHERE oe_kurzbz=a.oe_kurzbz AND NOT fixangestellt AND funktion_kurzbz='oezuordnung' AND aktiv) a) as extern
			FROM public.tbl_fachbereich a WHERE aktiv ORDER BY bezeichnung";
	
	if($result = $db->db_query($qry))
	{
		$gesamt_fix=0;
		$gesamt_extern=0;

		while($row = $db->db_fetch_object($result))
		{
			if($row->fix==0 && $row->extern==0)
			{
				continue;
			}
			echo '<tr>';
			echo "<td><a href='".$_SERVER['PHP_SELF']."?details=true&fachbereich_kurzbz=".$row->fachbereich_kurzbz."'>$row->bezeichnung</a></td>";
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
		if($result = $db->db_query($qry))
		{
			if($row = $db->db_fetch_object($result))
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
}
?>
</body>
</html>