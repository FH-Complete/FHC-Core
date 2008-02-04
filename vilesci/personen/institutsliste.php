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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/fachbereich.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Connecten zur DB');

$stg_obj = new studiengang($conn);
$stg_obj->getAll('typ, kurzbz', false);

$fb_obj = new fachbereich($conn);
$fb_obj->getAll();

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen(get_uid());

echo '
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
		<html>
		<head>
		<title>Institutsliste</title>
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<meta http-equiv="content-type" content="text/html; charset=ISO-8859-9" />
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
		</head>
		<body class="Background_main">
		<h2>Liste der MitarbeiterInnen der Institute an der Fachhochschule Technikum Wien</h2>';

$stsem = new studiensemester($conn);
$ws = $stsem->getNearest(1);
$ss = $stsem->getNearest(2);

if($rechte->isBerechtigt('admin', 0))
	$where = '';
else 
{
	$fb = $rechte->getFbKz();
	if(count($fb)>0)
	{
		$where = " AND EXISTS (SELECT * FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrfach USING(lehrfach_id) WHERE 
								tbl_lehreinheit.studiensemester_kurzbz in('$ws','$ss') AND mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid AND
								fachbereich_kurzbz IN(";
		foreach ($fb as $fachbereich_kurzbz)
		{
			$where.="'$fachbereich_kurzbz',";
		}
		$where.="''))";
	}
	else 
		die('Sie haben keine Berechtigung fuer diese Seite');
}

//Alle aktiven Mitarbeiter holen mit den ALVS-Stunden und der Hauptinstitutszuteilung
$qry = "SELECT 
			vorname, nachname, fixangestellt, mitarbeiter_uid, kompetenzen, 
			(
				SELECT 
					sum(semesterstunden) 
				FROM 
					lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				WHERE 
					mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid AND 
					studiensemester_kurzbz='$ws'
			) as lvs_wintersemester,
			(
				SELECT 
					sum(semesterstunden) 
				FROM 
					lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				WHERE 
					mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid AND 
					studiensemester_kurzbz='$ss'
			) as lvs_sommersemester,
			(
				SELECT 
					fachbereich_kurzbz
				FROM 
					public.tbl_benutzerfunktion
				WHERE
					uid=tbl_mitarbeiter.mitarbeiter_uid AND
					funktion_kurzbz='Institut'
				LIMIT 1
			) as hauptzuteilung
		FROM 
			public.tbl_mitarbeiter JOIN public.tbl_benutzer ON(uid=mitarbeiter_uid) 
			JOIN public.tbl_person USING(person_id) 
		WHERE tbl_benutzer.aktiv $where";

if($result = pg_query($conn, $qry))
{
	echo "<br><br><table class='liste table-autosort:0 table-stripeclass:alternate table-autostripe'>
				<thead>
					<tr>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th colspan='2'>ALVS</th>
						<th></th>
						<th colspan='2'>Institute</th>
					</tr>
					<tr class='liste'>
						<th class='table-sortable:default'>Nachname</th>
						<th class='table-sortable:default'>Vorname</th>
						<th class='table-sortable:default'>Fix / Frei</th>
						<th class='table-sortable:default'>Kompetenzen</th>
						<th class='table-sortable:default'>$ws</th>
						<th class='table-sortable:default'>$ss</th>
						<th class='table-sortable:default'>Studiengang</th>
						<th class='table-sortable:default'>Hauptzuteilung</th>
						<th class='table-sortable:default'>Sonstige</th>
					</tr>
				</thead>
				<tbody>";

	while($row = pg_fetch_object($result))
	{
		echo '<tr>';
		echo "<td>$row->nachname</td>";
		echo "<td>$row->vorname</td>";
		echo "<td>".($row->fixangestellt=='t'?'fix':'frei')."</td>";
		echo "<td>$row->kompetenzen</td>";
		echo "<td>$row->lvs_wintersemester</td>";
		echo "<td>$row->lvs_sommersemester</td>";
		echo '<td>';
		$qry = "SELECT distinct studiengang_kz FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) WHERE mitarbeiter_uid='$row->mitarbeiter_uid' AND studiensemester_kurzbz in('$ss', '$ws')";
		$text='';
		if($result_stg = pg_query($conn, $qry))
			while($row_stg = pg_fetch_object($result_stg))
				$text.= $stg_obj->kuerzel_arr[$row_stg->studiengang_kz].', ';
		echo substr($text, 0, strlen($text)-2);
		echo '</td>';
		echo "<td>".(isset($fb_obj->bezeichnung_arr[$row->hauptzuteilung])?$fb_obj->bezeichnung_arr[$row->hauptzuteilung]:'')."</td>";
		echo "<td>";
		$qry = "SELECT distinct fachbereich_kurzbz FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) JOIN lehre.tbl_lehrfach USING(lehrfach_id) WHERE mitarbeiter_uid='$row->mitarbeiter_uid' AND studiensemester_kurzbz in('$ss', '$ws')";
		$text='';
		if($result_fb = pg_query($conn, $qry))
			while($row_fb = pg_fetch_object($result_fb))
				$text.= $fb_obj->bezeichnung_arr[$row_fb->fachbereich_kurzbz].', ';
		echo substr($text, 0, strlen($text)-2);
		echo "</td>";
		echo '</tr>';
	}
	echo '</tbody></table>';
}

echo '</body></html>';
?>