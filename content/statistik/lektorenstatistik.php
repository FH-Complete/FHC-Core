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
	
	echo "<h2>LektorInnenstatistik (Lehrauftrag ohne Betreuungen) $ws / $ss - ".$fachbereich->bezeichnung.'</h2>';
	$qry = "SELECT distinct mitarbeiter_uid, anrede, nachname, vorname, titelpre, titelpost,
			(SELECT count(*) FROM (SELECT distinct mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id) JOIN public.tbl_fachbereich ON(lehrfach.oe_kurzbz=tbl_fachbereich.oe_kurzbz) JOIN campus.vw_mitarbeiter ON(uid=mitarbeiter_uid) WHERE studiensemester_kurzbz IN(".$db->db_add_param($ws).",".$db->db_add_param($ss).") AND fachbereich_kurzbz=".$db->db_add_param($fachbereich->fachbereich_kurzbz)." AND fixangestellt AND geschlecht='m') a) AS fix_m,
			(SELECT count(*) FROM (SELECT distinct mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id) JOIN public.tbl_fachbereich ON(lehrfach.oe_kurzbz=tbl_fachbereich.oe_kurzbz) JOIN campus.vw_mitarbeiter ON(uid=mitarbeiter_uid) WHERE studiensemester_kurzbz IN(".$db->db_add_param($ws).",".$db->db_add_param($ss).") AND fachbereich_kurzbz=".$db->db_add_param($fachbereich->fachbereich_kurzbz)." AND fixangestellt AND geschlecht='w') a) AS fix_w
			FROM lehre.tbl_lehreinheitmitarbeiter 
				JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
				JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id) 
				JOIN public.tbl_fachbereich ON(lehrfach.oe_kurzbz=tbl_fachbereich.oe_kurzbz)
				JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid)
				JOIN public.tbl_benutzer ON(uid=mitarbeiter_uid)
				JOIN public.tbl_person USING(person_id)
			WHERE studiensemester_kurzbz in(".$db->db_add_param($ws).",".$db->db_add_param($ss).") 
				AND fachbereich_kurzbz=".$db->db_add_param($fachbereich->fachbereich_kurzbz)." AND fixangestellt
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

	$qry = "SELECT distinct mitarbeiter_uid, anrede, nachname, vorname, titelpre, titelpost,
			(SELECT count(*) FROM (SELECT distinct mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id) JOIN public.tbl_fachbereich ON(tbl_fachbereich.oe_kurzbz=lehrfach.oe_kurzbz) JOIN campus.vw_mitarbeiter ON(uid=mitarbeiter_uid) WHERE studiensemester_kurzbz IN(".$db->db_add_param($ws).",".$db->db_add_param($ss).") AND fachbereich_kurzbz=".$db->db_add_param($fachbereich->fachbereich_kurzbz)." AND NOT fixangestellt AND geschlecht='m') a) AS not_fix_m,
			(SELECT count(*) FROM (SELECT distinct mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id) JOIN public.tbl_fachbereich ON(tbl_fachbereich.oe_kurzbz=lehrfach.oe_kurzbz) JOIN campus.vw_mitarbeiter ON(uid=mitarbeiter_uid) WHERE studiensemester_kurzbz IN(".$db->db_add_param($ws).",".$db->db_add_param($ss).") AND fachbereich_kurzbz=".$db->db_add_param($fachbereich->fachbereich_kurzbz)." AND NOT fixangestellt AND geschlecht='w') a) AS not_fix_w
			FROM lehre.tbl_lehreinheitmitarbeiter 
				JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
				JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id)
				JOIN public.tbl_fachbereich ON(tbl_fachbereich.oe_kurzbz=lehrfach.oe_kurzbz) 
				JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid)
				JOIN public.tbl_benutzer ON(uid=mitarbeiter_uid)
				JOIN public.tbl_person USING(person_id)
			WHERE studiensemester_kurzbz in(".$db->db_add_param($ws).",".$db->db_add_param($ss).") 
				AND fachbereich_kurzbz=".$db->db_add_param($fachbereich->fachbereich_kurzbz)." AND NOT fixangestellt
			ORDER BY nachname, vorname";
		
	if($db->db_query($qry))
	{
		$ausgabe='';
		$not_fix_m=0;
		$not_fix_w=0;
		while($row = $db->db_fetch_object())
		{
			$ausgabe.= '<tr>';
			$ausgabe.= "<td>$row->anrede</td>";
			$ausgabe.= "<td>$row->titelpre</td>";
			$ausgabe.= "<td>$row->nachname</td>";
			$ausgabe.= "<td>$row->vorname</td>";
			$ausgabe.= "<td>$row->titelpost</td>";
			$ausgabe.= "</tr>";
			$not_fix_w=$row->not_fix_w;
			$not_fix_m=$row->not_fix_m;
		}
		echo "<br /><br />".(($not_fix_m)+($not_fix_w))." Freiangestellte<br>M: ".$not_fix_m." <br>W: ".$not_fix_w."
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
	echo "<h2>LektorInnenstatistik (Lehrauftrag ohne Betreuungen) $ws / $ss";
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
							<th colspan=4>Anzahl</th>
							<th colspan=2>ALVS</th>
						</tr>
						<tr>
							<th>Institute</th>
							<th colspan=2>fix</th>
							<th colspan=2>extern</th>
							<th>$ws</th>
							<th>$ss</th>
						</tr>
						<tr>
							<th></th>
							<th>M</th>
							<th>W</th>
							<th>M</th>
							<th>W</th>
							<th colspan=2></th>
						</tr>
					</thead>
					<tbody>
					
				 ";
		
		$qry = "SELECT 
					bezeichnung, fachbereich_kurzbz,
					(SELECT count(*) FROM (SELECT distinct mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id) JOIN public.tbl_fachbereich ON(tbl_fachbereich.oe_kurzbz=lehrfach.oe_kurzbz) JOIN campus.vw_mitarbeiter ON(uid=mitarbeiter_uid) WHERE studiensemester_kurzbz IN(".$db->db_add_param($ws).",".$db->db_add_param($ss).") AND fachbereich_kurzbz=a.fachbereich_kurzbz AND fixangestellt AND geschlecht='m') a) AS fix_m,
					(SELECT count(*) FROM (SELECT distinct mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id) JOIN public.tbl_fachbereich ON(tbl_fachbereich.oe_kurzbz=lehrfach.oe_kurzbz) JOIN campus.vw_mitarbeiter ON(uid=mitarbeiter_uid) WHERE studiensemester_kurzbz IN(".$db->db_add_param($ws).",".$db->db_add_param($ss).") AND fachbereich_kurzbz=a.fachbereich_kurzbz AND fixangestellt AND geschlecht='w') a) AS fix_w,
					(SELECT count(*) FROM (SELECT distinct mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id) JOIN public.tbl_fachbereich ON(tbl_fachbereich.oe_kurzbz=lehrfach.oe_kurzbz) JOIN campus.vw_mitarbeiter ON(uid=mitarbeiter_uid) WHERE studiensemester_kurzbz IN(".$db->db_add_param($ws).",".$db->db_add_param($ss).") AND fachbereich_kurzbz=a.fachbereich_kurzbz AND NOT fixangestellt AND geschlecht='m') a) AS extern_m,
					(SELECT count(*) FROM (SELECT distinct mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id) JOIN public.tbl_fachbereich ON(tbl_fachbereich.oe_kurzbz=lehrfach.oe_kurzbz) JOIN campus.vw_mitarbeiter ON(uid=mitarbeiter_uid) WHERE studiensemester_kurzbz IN(".$db->db_add_param($ws).",".$db->db_add_param($ss).") AND fachbereich_kurzbz=a.fachbereich_kurzbz AND NOT fixangestellt AND geschlecht='w') a) AS extern_w,
					(SELECT sum(tbl_lehreinheitmitarbeiter.semesterstunden) FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id) JOIN public.tbl_fachbereich ON(lehrfach.oe_kurzbz=tbl_fachbereich.oe_kurzbz) WHERE studiensemester_kurzbz=".$db->db_add_param($ws)." AND fachbereich_kurzbz=a.fachbereich_kurzbz AND faktor>0 AND stundensatz>0) AS ws,
					(SELECT sum(tbl_lehreinheitmitarbeiter.semesterstunden) FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id) JOIN public.tbl_fachbereich ON(lehrfach.oe_kurzbz=tbl_fachbereich.oe_kurzbz) WHERE studiensemester_kurzbz=".$db->db_add_param($ss)." AND fachbereich_kurzbz=a.fachbereich_kurzbz AND faktor>0 AND stundensatz>0) AS ss
				FROM public.tbl_fachbereich a WHERE aktiv ORDER BY bezeichnung";
		
		if($db->db_query($qry))
		{
			//$gesamt_fix=0;
			//$gesamt_extern=0;
			$gesamt_ws=0;
			$gesamt_ss=0;
			while($row = $db->db_fetch_object())
			{
				if(($row->fix_m==0 && $row->fix_w==0) && ($row->extern_m==0 && $row->extern_w==0))
				{
					continue;
				}
				echo '<tr>';
				echo "<td><a href='".$_SERVER['PHP_SELF']."?details=true&fachbereich_kurzbz=$row->fachbereich_kurzbz&ss=$ss&ws=$ws'>$row->bezeichnung</a></td>";
				echo "<td align='center'>$row->fix_m</td>";
				echo "<td align='center'>$row->fix_w</td>";
				echo "<td align='center'>$row->extern_m</td>";
				echo "<td align='center'>$row->extern_w</td>";
				echo "<td align='center'>$row->ws</td>";
				echo "<td align='center'>$row->ss</td>";
				echo "</tr>";
				//$gesamt_fix+=$row->fix;
				//$gesamt_extern+=$row->extern;
				$gesamt_ws+=$row->ws;
				$gesamt_ss+=$row->ss;
			}
			echo '<tr>';
			echo '<td><b>SUMME</b></td>';
			echo "<td align='center'>&nbsp;</td>";
			echo "<td align='center'>&nbsp;</td>";
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
