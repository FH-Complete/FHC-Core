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
 * Erstellt eine Tablle mit der Anzahl der Lektoren die im angegebenen
 * StudienJAHR einen Lehrauftrag im jeweiligen Institut haben, getrennt nach Fixangestellten und Freien
 * und der Anzahl der Stunden die in diesem Institut gehalten wurden
 * Bei einem klick auf das Institut wird die Detailansicht angezeigt, in der die einzelnen
 * Lektoren Namentlich aufscheinen.
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/fachbereich.class.php');

$ws='';
$ss='';
$db = new basis_db();
if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
{
	$stsem_obj = new studiensemester();
	$stsem = $stsem_obj->getaktorNext();
}
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
	$ss = (isset($_GET['ss'])?$_GET['ss']:'');
	$ws = (isset($_GET['ws'])?$_GET['ws']:'');
	$fachbereich = new fachbereich();
	if(!$fachbereich->load($_GET['fachbereich_kurzbz']))
		die('Institut existiert nicht');
	
	echo "<h2>Lektorenstatistik (Lehrauftrag ohne Betreuungen) $ws / $ss - ".$fachbereich->bezeichnung.'</h2>';
	$qry = "SELECT distinct mitarbeiter_uid, nachname, vorname, titelpre, titelpost
			FROM lehre.tbl_lehreinheitmitarbeiter 
				JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
				JOIN lehre.tbl_lehrfach USING(lehrfach_id) 
				JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid)
				JOIN public.tbl_benutzer ON(uid=mitarbeiter_uid)
				JOIN public.tbl_person USING(person_id)
			WHERE studiensemester_kurzbz in('".addslashes($ws)."','".addslashes($ss)."') 
				AND fachbereich_kurzbz='".addslashes($fachbereich->fachbereich_kurzbz)."' AND fixangestellt
			ORDER BY nachname, vorname";
	
	
	if($db->db_query($qry))
	{
		echo "Fixangestellt - Anzahl: ".$db->db_num_rows()."
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
		while($row = $db->db_fetch_object())
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
	
	$qry = "SELECT distinct mitarbeiter_uid, nachname, vorname, titelpre, titelpost
			FROM lehre.tbl_lehreinheitmitarbeiter 
				JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
				JOIN lehre.tbl_lehrfach USING(lehrfach_id) 
				JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid)
				JOIN public.tbl_benutzer ON(uid=mitarbeiter_uid)
				JOIN public.tbl_person USING(person_id)
			WHERE studiensemester_kurzbz in('".addslashes($ws)."','".addslashes($ss)."') 
				AND fachbereich_kurzbz='".addslashes($fachbereich->fachbereich_kurzbz)."' AND NOT fixangestellt
			ORDER BY nachname, vorname";
		
	if($db->db_query($qry))
	{
		echo "<br /><br />Freiangestellt - Anzahl: ".$db->db_num_rows()."
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
		while($row = $db->db_fetch_object())
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
	if(substr($stsem, 0, 2)=='WS')
	{
		$stsem_obj = new studiensemester();
		$ss = $stsem_obj->getNextFrom($stsem);
		$ws = $stsem;
	}
	else 
	{
		$stsem_obj = new studiensemester();
		$ws = $stsem_obj->getPreviousFrom($stsem);
		$ss = $stsem;
	}
	echo "<h2>Lektorenstatistik (Lehrauftrag ohne Betreuungen) $ws / $ss";
	echo '<span style="position:absolute; right:15px;">'.date('d.m.Y').'</span></h2><br>';
	echo '</h2>';
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">Studiensemester: <SELECT name="stsem">';
	$studsem = new studiensemester();
	$studsem->getAll();

	foreach ($studsem->studiensemester as $stsemester)
	{
		if($stsemester->studiensemester_kurzbz==$ws)
			$selected='selected';
		else 
			$selected='';
		if(substr($stsemester->studiensemester_kurzbz, 0, 2)=='WS')
		{
			$stsem_obj = new studiensemester();
			$ss1 = $stsem_obj->getNextFrom($stsemester->studiensemester_kurzbz);
			$ws1 = $stsemester->studiensemester_kurzbz;
			echo '<option value="'.$stsemester->studiensemester_kurzbz.'" '.$selected.'>'.$ws1.'/'.$ss1.'</option>';			
		}
		
	}
	echo '</SELECT>
		<input type="submit" value="Anzeigen" /></form><br><br>';

	if($stsem!='')
	{
		
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
		
		$qry = "SELECT 
					bezeichnung, fachbereich_kurzbz,
					(SELECT count(*) FROM (SELECT distinct mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrfach USING(lehrfach_id) JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid) WHERE studiensemester_kurzbz in('".addslashes($ws)."','".addslashes($ss)."') AND fachbereich_kurzbz=a.fachbereich_kurzbz AND fixangestellt) a) as fix,
					(SELECT count(*) FROM (SELECT distinct mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrfach USING(lehrfach_id) JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid) WHERE studiensemester_kurzbz in('".addslashes($ws)."','".addslashes($ss)."') AND fachbereich_kurzbz=a.fachbereich_kurzbz AND NOT fixangestellt) a) as extern,
					(SELECT sum(semesterstunden) FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrfach USING(lehrfach_id) WHERE studiensemester_kurzbz='".addslashes($ws)."' AND fachbereich_kurzbz=a.fachbereich_kurzbz AND faktor>0 AND stundensatz>0) as ws,
					(SELECT sum(semesterstunden) FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrfach USING(lehrfach_id) WHERE studiensemester_kurzbz='".addslashes($ss)."' AND fachbereich_kurzbz=a.fachbereich_kurzbz AND faktor>0 AND stundensatz>0) as ss
				FROM public.tbl_fachbereich a WHERE aktiv ORDER BY bezeichnung";
		/*
		Mitarbeiter laut institutszuordnung
		(SELECT count(*) FROM public.tbl_benutzerfunktion JOIN public.tbl_mitarbeiter on (uid=mitarbeiter_uid) WHERE fachbereich_kurzbz=a.fachbereich_kurzbz AND funktion_kurzbz='oezuordnung' AND fixangestellt AND aktiv) as fix,
		(SELECT count(*) FROM public.tbl_benutzerfunktion JOIN public.tbl_mitarbeiter on (uid=mitarbeiter_uid) WHERE fachbereich_kurzbz=a.fachbereich_kurzbz AND funktion_kurzbz='oezuordnung' AND NOT fixangestellt AND aktiv) as extern,
		*/
		//echo '<pre>'.$qry.'</pre><br><br>';
		if($db->db_query($qry))
		{
			$gesamt_fix=0;
			$gesamt_extern=0;
			$gesamt_ws=0;
			$gesamt_ss=0;
			while($row = $db->db_fetch_object())
			{
				if($row->fix==0 && $row->extern==0)
				{
					continue;
				}
				echo '<tr>';
				echo "<td><a href='".$_SERVER['PHP_SELF']."?details=true&fachbereich_kurzbz=$row->fachbereich_kurzbz&ss=$ss&ws=$ws'>$row->bezeichnung</a></td>";
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
			echo "<td align='center'>&nbsp;</td>";
			echo "<td align='center'>&nbsp;</td>";
			echo "<td align='center'><b>$gesamt_ws</b></td>";
			echo "<td align='center'><b>$gesamt_ss</b></td>";
			echo "</tr>";
			
		}
		echo '</tbody></table>';
	}
}
?>
</body>
</html>