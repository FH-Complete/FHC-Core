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
	
	echo "<h2>MitarbeiterInnenstatistik (Hauptzuordnung) - ".$fachbereich->bezeichnung.'</h2>';
	$qry = "SELECT distinct uid, anrede, nachname, vorname, titelpre, titelpost,
			(SELECT count(*) FROM (SELECT distinct uid FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) WHERE oe_kurzbz='".addslashes($fachbereich->oe_kurzbz)."' AND fixangestellt AND funktion_kurzbz='oezuordnung' AND aktiv AND (datum_bis >= now() OR datum_bis IS NULL) AND (datum_von <= now() OR datum_von IS NULL) AND geschlecht='m') a) as fix_m,
			(SELECT count(*) FROM (SELECT distinct uid FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) WHERE oe_kurzbz='".addslashes($fachbereich->oe_kurzbz)."' AND fixangestellt AND funktion_kurzbz='oezuordnung' AND aktiv AND (datum_bis >= now() OR datum_bis IS NULL) AND (datum_von <= now() OR datum_von IS NULL) AND geschlecht='w') a) as fix_w 
			FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) 
			WHERE oe_kurzbz='".addslashes($fachbereich->oe_kurzbz)."' 
			AND fixangestellt AND funktion_kurzbz='oezuordnung' AND aktiv 
			AND (datum_bis >= now() OR datum_bis IS NULL)
			AND (datum_von <= now() OR datum_von IS NULL)
			ORDER BY nachname, vorname";
	
	if($db->db_query($qry))
	{
		$ausgabe='';
		$fix_m=0;
		$fix_w=0;
		while($row = $db->db_fetch_object())
		{
			$ausgabe.= '<tr>';
			$ausgabe.= "<td>$row->anrede</td>";
			$ausgabe.= "<td>$row->titelpre</td>";
			$ausgabe.= "<td>$row->nachname</td>";
			$ausgabe.= "<td>$row->vorname</td>";
			$ausgabe.= "<td>$row->titelpost</td>";
			$ausgabe.= "</tr>";
			$fix_w=$row->fix_w;
			$fix_m=$row->fix_m;
		}
		echo (($fix_m)+($fix_w))." Fixangestellte<br>M: ".$fix_m." <br>W: ".$fix_w."
			<table class='liste table-stripeclass:alternate table-autostripe'>
					<thead>
						<tr>
							<th>Anrede</th>
							<th>TitelPre</th>
							<th>Nachname</th>
							<th>Vorname</th>
							<th>Titelpost</th>
						</tr>
					</thead>
					<tbody>
				 ";
		echo $ausgabe;
	}
	echo '</tbody></table>';
	
	$qry = "SELECT distinct uid, anrede, nachname, vorname, titelpre, titelpost, 
			(SELECT count(*) FROM (SELECT distinct uid FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) WHERE oe_kurzbz='".addslashes($fachbereich->oe_kurzbz)."' AND NOT fixangestellt AND funktion_kurzbz='oezuordnung' AND aktiv AND (datum_bis >= now() OR datum_bis IS NULL) AND (datum_von <= now() OR datum_von IS NULL) AND geschlecht='m') a) as extern_m,
			(SELECT count(*) FROM (SELECT distinct uid FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) WHERE oe_kurzbz='".addslashes($fachbereich->oe_kurzbz)."' AND NOT fixangestellt AND funktion_kurzbz='oezuordnung' AND aktiv AND (datum_bis >= now() OR datum_bis IS NULL) AND (datum_von <= now() OR datum_von IS NULL) AND geschlecht='w') a) as extern_w
			FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) 
			WHERE oe_kurzbz='".addslashes($fachbereich->oe_kurzbz)."' 
			AND NOT fixangestellt AND funktion_kurzbz='oezuordnung' AND aktiv
			AND (datum_bis >= now() OR datum_bis IS NULL)
			AND (datum_von <= now() OR datum_von IS NULL)
			ORDER BY nachname, vorname";
	
if($db->db_query($qry))
	{
		$ausgabe='';
		$extern_m=0;
		$extern_w=0;
		while($row = $db->db_fetch_object())
		{
			$ausgabe.= '<tr>';
			$ausgabe.= "<td>$row->anrede</td>";
			$ausgabe.= "<td>$row->titelpre</td>";
			$ausgabe.= "<td>$row->nachname</td>";
			$ausgabe.= "<td>$row->vorname</td>";
			$ausgabe.= "<td>$row->titelpost</td>";
			$ausgabe.= "</tr>";
			$extern_w=$row->extern_w;
			$extern_m=$row->extern_m;
		}
		echo "<br /><br />".(($extern_m)+($extern_w))." Freiangestellte<br>M: ".$extern_m." <br>W: ".$extern_w."
			<table class='liste table-stripeclass:alternate table-autostripe'>
					<thead>
						<tr>
							<th>Anrede</th>
							<th>TitelPre</th>
							<th>Nachname</th>
							<th>Vorname</th>
							<th>Titelpost</th>
						</tr>
					</thead>
					<tbody>
				 ";
		echo $ausgabe;
	}
	echo '</tbody></table>';
}
else 
{
	echo "<h2>MitarbeiterInnenstatistik (Hauptzuordnung)";
	echo '<span style="position:absolute; right:15px;">'.date('d.m.Y').'</span></h2><br>';
	echo '</h2>';
	
	echo '<br><br>';
	
	echo "<table class='liste table-stripeclass:alternate table-autostripe'>
				<thead>
					<tr>
						<th></th>
						<th colspan=4>Anzahl</th>
					</tr>
					<tr>
						<th>Institute</th>
						<th colspan=2>fix</th>
						<th colspan=2>extern</th>
					</tr>
					<tr>
							<th></th>
							<th>M</th>
							<th>W</th>
							<th>M</th>
							<th>W</th>
						</tr>
				</thead>
				<tbody>
				
			 ";
	
	$qry = "SELECT 
				bezeichnung, fachbereich_kurzbz,
				(SELECT count(*) FROM (SELECT distinct uid FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) WHERE oe_kurzbz=a.oe_kurzbz AND fixangestellt AND funktion_kurzbz='oezuordnung' AND aktiv AND (datum_bis >= now() OR datum_bis IS NULL) AND (datum_von <= now() OR datum_von IS NULL) AND geschlecht='m') a) as fix_m,
				(SELECT count(*) FROM (SELECT distinct uid FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) WHERE oe_kurzbz=a.oe_kurzbz AND fixangestellt AND funktion_kurzbz='oezuordnung' AND aktiv AND (datum_bis >= now() OR datum_bis IS NULL) AND (datum_von <= now() OR datum_von IS NULL) AND geschlecht='w') a) as fix_w,
				(SELECT count(*) FROM (SELECT distinct uid FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) WHERE oe_kurzbz=a.oe_kurzbz AND NOT fixangestellt AND funktion_kurzbz='oezuordnung' AND aktiv AND (datum_bis >= now() OR datum_bis IS NULL) AND (datum_von <= now() OR datum_von IS NULL) AND geschlecht='m') a) as extern_m,
				(SELECT count(*) FROM (SELECT distinct uid FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) WHERE oe_kurzbz=a.oe_kurzbz AND NOT fixangestellt AND funktion_kurzbz='oezuordnung' AND aktiv AND (datum_bis >= now() OR datum_bis IS NULL) AND (datum_von <= now() OR datum_von IS NULL) AND geschlecht='w') a) as extern_w
			FROM public.tbl_fachbereich a WHERE aktiv ORDER BY bezeichnung";
	
	if($result = $db->db_query($qry))
	{
		$gesamt_fix=0;
		$gesamt_fix_m=0;
		$gesamt_fix_w=0;
		$gesamt_extern=0;
		$gesamt_extern_m=0;
		$gesamt_extern_w=0;
		$gesamt_fix_m_nz=0;
		$gesamt_fix_w_nz=0;
		$gesamt_extern_m_nz=0;
		$gesamt_extern_w_nz=0;

		while($row = $db->db_fetch_object($result))
		{
			if(($row->fix_m==0 && $row->fix_w==0) && ($row->extern_m==0 && $row->extern_w==0))
			{
				continue;
			}
			echo '<tr>';
			echo "<td><a href='".$_SERVER['PHP_SELF']."?details=true&fachbereich_kurzbz=".$row->fachbereich_kurzbz."'>$row->bezeichnung</a></td>";
			echo "<td align='center'>$row->fix_m</td>";
			echo "<td align='center'>$row->fix_w</td>";
			echo "<td align='center'>$row->extern_m</td>";
			echo "<td align='center'>$row->extern_w</td>";
			echo "</tr>";
			$gesamt_fix_m+=$row->fix_m;			
			$gesamt_fix_w+=$row->fix_w;			
			$gesamt_fix+=(($row->fix_m)+($row->fix_w));
			$gesamt_extern_m+=$row->extern_m;
			$gesamt_extern_w+=$row->extern_w;
			$gesamt_extern+=(($row->extern_m)+($row->extern_w));
		}
		
		$qry = "SELECT 
					(SELECT count(*) FROM campus.vw_mitarbeiter WHERE uid NOT in(SELECT uid FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='oezuordnung' AND (datum_bis >= now() OR datum_bis IS NULL) AND (datum_von <= now() OR datum_von IS NULL)) AND aktiv AND fixangestellt AND geschlecht='m') as fix_m,
					(SELECT count(*) FROM campus.vw_mitarbeiter WHERE uid NOT in(SELECT uid FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='oezuordnung' AND (datum_bis >= now() OR datum_bis IS NULL) AND (datum_von <= now() OR datum_von IS NULL)) AND aktiv AND fixangestellt AND geschlecht='w') as fix_w,
					(SELECT count(*) FROM campus.vw_mitarbeiter WHERE uid NOT in(SELECT uid FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='oezuordnung' AND (datum_bis >= now() OR datum_bis IS NULL) AND (datum_von <= now() OR datum_von IS NULL)) AND aktiv AND NOT fixangestellt AND geschlecht='m') as extern_m,
					(SELECT count(*) FROM campus.vw_mitarbeiter WHERE uid NOT in(SELECT uid FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='oezuordnung' AND (datum_bis >= now() OR datum_bis IS NULL) AND (datum_von <= now() OR datum_von IS NULL)) AND aktiv AND NOT fixangestellt AND geschlecht='w') as extern_w
				";
		if($result = $db->db_query($qry))
		{
			if($row = $db->db_fetch_object($result))
			{
				echo '<tr>';
				echo "<td>Nicht zugeordnet</td>";
				echo "<td align='center'>$row->fix_m</td>";
				echo "<td align='center'>$row->fix_w</td>";
				echo "<td align='center'>$row->extern_m</td>";
				echo "<td align='center'>$row->extern_w</td>";
				echo "</tr>";
				$gesamt_fix_m_nz += $row->fix_m;
				$gesamt_fix_w_nz += $row->fix_w;
				$gesamt_extern_m_nz += $row->extern_m;
				$gesamt_extern_w_nz += $row->extern_w;
			}
		}
		echo '<tr>';
		echo '<td rowspan="2"><b>SUMME</b></td>';
		echo "<td align='center'><b>".(($gesamt_fix_m)+($gesamt_fix_m_nz))."</b></td>";
		echo "<td align='center'><b>".(($gesamt_fix_w)+($gesamt_fix_w_nz))."</b></td>";
		echo "<td align='center'><b>".(($gesamt_extern_m)+($gesamt_extern_m_nz))."</b></td>";
		echo "<td align='center'><b>".(($gesamt_extern_w)+($gesamt_extern_w_nz))."</b></td>";
		echo "</tr>";
		echo '<tr>';
		echo "<td align='center' colspan='2'><b>".(($gesamt_fix_m)+($gesamt_fix_m_nz)+($gesamt_fix_w)+($gesamt_fix_w_nz))."</b></td>";
		echo "<td align='center' colspan='2'><b>".(($gesamt_extern_m)+($gesamt_extern_m_nz)+($gesamt_extern_w)+($gesamt_extern_w_nz))."</b></td>";
		echo "</tr>";
		
	}
	echo '</tbody></table>';
}
?>
</body>
</html>