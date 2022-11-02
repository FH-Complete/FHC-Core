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

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/organisationseinheit.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$stg_obj = new studiengang();
$stg_obj->getAll('typ, kurzbz', false);

$oe_typ = new organisationseinheit();
$oe_typ->getTypen();
foreach($oe_typ->result as $row)
	$oe_typ_arr[$row->organisationseinheittyp_kurzbz]=$row->bezeichnung;
$oe_obj = new organisationseinheit();
$oe_obj->getAll();

foreach($oe_obj->result as $row)
	$oe_arr[$row->oe_kurzbz] = $oe_typ_arr[$row->organisationseinheittyp_kurzbz].' '.$row->bezeichnung;

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen(get_uid());

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Institutsliste</title>
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
		<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
		<script type="text/javascript">
			$(document).ready(function()
			{
				$("#t1").tablesorter(
				{
					sortList: [[1,0]],
					widgets: ["zebra"],
					headers: {11: {sorter: false}, 12: {sorter: false}, 13: {sorter: false}}
				});
			});
		</script>
	</head>
	<body class="Background_main">
		<h2>Liste der MitarbeiterInnen der Abteilungen</h2>';

$stsem = new studiensemester();
if(isset($_GET['ws']) && check_stsem($_GET['ws']))
	$ws = $_GET['ws'];
else
	$ws = $stsem->getNearest(1);

if(isset($_GET['ss']) && check_stsem($_GET['ss']))
	$ss = $_GET['ss'];
else
	$ss = $stsem->getNearest(2);

if($rechte->isBerechtigt('admin', 0) || $rechte->isBerechtigt('mitarbeiter', 0))
	$where = '';
else
{
	$oe = $rechte->getOEkurzbz('assistenz');
	if(count($oe)>0)
	{
		$where = " AND EXISTS (
			SELECT * FROM
				lehre.tbl_lehreinheitmitarbeiter
				JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				JOIN lehre.tbl_lehrveranstaltung lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id)
			WHERE
				tbl_lehreinheit.studiensemester_kurzbz in(".$db->db_add_param($ws).",".$db->db_add_param($ss).")
				AND mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid
				AND lehrfach.oe_kurzbz IN(";
		foreach ($oe as $oe_kurzbz)
		{
			$where.=$db->db_add_param($oe_kurzbz).",";
		}
		$where.="''))";
	}
	else
		die('Sie haben keine Berechtigung fuer diese Seite');
}

//Alle aktiven Mitarbeiter holen mit den ALVS-Stunden und der Hauptinstitutszuteilung
$qry = "SELECT
			personalnummer, vorname, nachname, fixangestellt, mitarbeiter_uid, kompetenzen,
			(
				SELECT
					sum(semesterstunden)
				FROM
					lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				WHERE
					mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid AND
					studiensemester_kurzbz=".$db->db_add_param($ws)."
			) as lvs_wintersemester,
			(
				SELECT
					sum(semesterstunden)
				FROM
					lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				WHERE
					mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid AND
					studiensemester_kurzbz=".$db->db_add_param($ss)."
			) as lvs_sommersemester,
			(
				SELECT
					tbl_organisationseinheit.oe_kurzbz
				FROM
					public.tbl_benutzerfunktion JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
				WHERE
					uid=tbl_mitarbeiter.mitarbeiter_uid AND
					funktion_kurzbz='oezuordnung' AND
					(datum_von<=now() OR datum_von is null)
					AND (datum_bis>=now() OR datum_bis is null)
				LIMIT 1
			) as hauptzuteilung
		FROM
			public.tbl_mitarbeiter JOIN public.tbl_benutzer ON(uid=mitarbeiter_uid)
			JOIN public.tbl_person USING(person_id)
		WHERE tbl_benutzer.aktiv $where";

if($result = $db->db_query($qry))
{
	$count = $db->db_num_rows($result);
	echo $count.' MitarbeiterInnen';
	echo "<br><br><table class='tablesorter' id='t1'>
				<thead>
					<tr class='liste'>
						<th>PNr</th>
						<th>Nachname</th>
						<th>Vorname</th>
						<th>Fix / Frei</th>
						<th>ALVS<br>".$db->convert_html_chars($ws)."</th>
						<th>ALVS<br>".$db->convert_html_chars($ss)."</th>
						<th>Studiengang</th>
						<th>Disziplinäre Zuordnung</th>
						<th>Zuteilungen laut Lehrauträge</th>
					</tr>
				</thead>
				<tbody>";

	while($row = $db->db_fetch_object($result))
	{
		echo '<tr>';
		echo "<td>".$db->convert_html_chars($row->personalnummer)."</td>";
		echo "<td>".$db->convert_html_chars($row->nachname)."</td>";
		echo "<td>".$db->convert_html_chars($row->vorname)."</td>";
		echo "<td>".($row->fixangestellt=='t'?'fix':'frei')."</td>";
		echo "<td>$row->lvs_wintersemester</td>";
		echo "<td>$row->lvs_sommersemester</td>";
		echo '<td>';
		$qry = "
		SELECT
			distinct studiengang_kz
		FROM
			lehre.tbl_lehreinheitmitarbeiter
			JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
			JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
		WHERE
			mitarbeiter_uid=".$db->db_add_param($row->mitarbeiter_uid)."
			AND studiensemester_kurzbz in(".$db->db_add_param($ss).", ".$db->db_add_param($ws).")";

		$text='';
		if($row->hauptzuteilung!='')
			$abteilung = $oe_arr[$row->hauptzuteilung];
		else
			$abteilung = '';

		if($result_stg = $db->db_query($qry))
			while($row_stg = $db->db_fetch_object($result_stg))
				$text.= $stg_obj->kuerzel_arr[$row_stg->studiengang_kz].', ';
		echo mb_substr($text, 0, mb_strlen($text)-2);
		echo '</td>';
		echo "<td>".$db->convert_html_chars($abteilung)."</td>";
		echo "<td>";
		$qry = "
		SELECT
			distinct lehrfach.oe_kurzbz
		FROM
			lehre.tbl_lehreinheitmitarbeiter
			JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
			JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id)
		WHERE
			mitarbeiter_uid=".$db->db_add_param($row->mitarbeiter_uid)."
			AND studiensemester_kurzbz in(".$db->db_add_param($ss).", ".$db->db_add_param($ws).")";

		$text='';
		if($result_oe = $db->db_query($qry))
			while($row_oe = $db->db_fetch_object($result_oe))
			{
				if($row_oe->oe_kurzbz!='')
					$text.= $oe_arr[$row_oe->oe_kurzbz].', ';
			}
		echo mb_substr($text, 0, mb_strlen($text)-2);
		echo "</td>";
		echo '</tr>';
	}
	echo '</tbody></table>';
}

echo '</body></html>';
?>
