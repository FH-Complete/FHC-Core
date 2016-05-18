<?php
/* Copyright (C) 2010 Technikum-Wien
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
 * Authors: Christian Paminger 	<christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl 		<rudolf.hangl@technikum-wien.at> and
 *          Andreas Moik 	<moik@technikum-wien.at>.
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/studiengang.class.php');

$db = new basis_db();

$stsem = new studiensemester();
$stsem->getNextStudiensemester();

$stg = new studiengang();
$stg->getAll();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link href="../../../include/js/tablesort/table.css" rel="stylesheet" type="text/css">
	<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
	<title>Lehrveranstaltungen - &Uuml;bersicht</title>	
</head>
<body>
<?php

	echo '
	<table class="tabcontent" id="inhalt">
		<tr>
			<td class="tdwidth10">&nbsp;</td>
	    	<td>
				<table class="tabcontent">
				<tr>
					<td class="ContentHeader">
						<font class="ContentHeader">&nbsp;Lehrveranstaltungen - &Uuml;bersicht ('.$stsem->studiensemester_kurzbz.')</font>
					</td>
	      		</tr>
	      		<tr>
	        		<td>&nbsp;</td>
	      		</tr>
		  		<tr>
		  			<td>
	';

	$qry = "SELECT 
				tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehrveranstaltung.studiengang_kz, 
				tbl_lehrveranstaltung.bezeichnung, tbl_lehrveranstaltung.semester, 
				tbl_lehrveranstaltung.bezeichnung_english, tbl_lehrveranstaltung.incoming,
				tbl_lehrveranstaltung.sprache,
				(
				SELECT
					count(*)
				FROM 
					campus.vw_student_lehrveranstaltung 
					JOIN public.tbl_prestudent USING(prestudent_id)
					JOIN public.tbl_prestudentstatus USING(prestudent_id)
				WHERE
					lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND
					lehreinheit_id in (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit 
						WHERE lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
						AND tbl_lehreinheit.studiensemester_kurzbz='$stsem->studiensemester_kurzbz')
					AND tbl_prestudentstatus.status_kurzbz='Incoming'
					AND tbl_prestudentstatus.status_kurzbz='$stsem->studiensemester_kurzbz'
				GROUP BY uid
				) as anzahlincoming
			FROM 
				lehre.tbl_lehrveranstaltung JOIN public.tbl_studiengang USING(studiengang_kz)
			WHERE 
				tbl_lehrveranstaltung.incoming>0 AND 
				tbl_lehrveranstaltung.aktiv AND 
				tbl_lehrveranstaltung.lehre
				AND tbl_lehrveranstaltung.studiengang_kz>0 AND tbl_lehrveranstaltung.studiengang_kz<10000
				AND tbl_studiengang.aktiv
				";

	echo '<table width="100%" class="table-autosort:3 table-stripeclass:alternate table-autostripe">
			<thead>
			<tr class="liste">
				<th class="table-sortable:numeric">ID</th>
				<th class="table-sortable:default">Studiengang</th>
				<th class="table-sortable:numeric">Semester</th>
				<th class="table-sortable:numeric">Sprache</th>
				<th class="table-sortable:default">Lehrveranstaltung</th>
				<th class="table-sortable:default">Lehrveranstaltung Englisch</th>
				<th>LV-Info</th>
				<th class="table-sortable:numeric">Pl&auml;tze gesamt</th>
				<th class="table-sortable:numeric">Freie Pl&auml;tze</th>
			</tr>
			</thead>
			<tbody>';
	if($result = $db->db_query($qry))
	{
		$i=0;
		while($row = $db->db_fetch_object($result))
		{
			$freieplaetze = $row->incoming - $row->anzahlincoming;
			if($freieplaetze<0)
				$freieplaetze=0;
			
			$i++;
			echo '<tr>';
			echo '<td>',$row->lehrveranstaltung_id,'</td>';
			echo '<td>',$stg->kuerzel_arr[$row->studiengang_kz],'</td>';
			echo '<td>',$row->semester,'</td>';
			echo '<td>',$row->sprache,'</td>';
			echo '<td>',$row->bezeichnung,'</td>';
			echo '<td>',$row->bezeichnung_english,'</td>';
			echo '<td>
					<a href="#Deutsch" class="Item" onclick="javascript:window.open(\'ects/preview.php?lv='.$row->lehrveranstaltung_id.'&amp;language=de\',\'Lehrveranstaltungsinformation\',\'width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes\');return false;">Deutsch&nbsp;</a>
					<a href="#Englisch" class="Item" onclick="javascript:window.open(\'ects/preview.php?lv='.$row->lehrveranstaltung_id.'&amp;language=en\',\'Lehrveranstaltungsinformation\',\'width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes\');return false;">Englisch</a>
				  </td>';
			echo '<td>',$row->incoming,'</td>';
			echo '<td>',$freieplaetze,'</td>';
			echo '</tr>';
		}
	}
	echo '</tbody></table>';
?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>

