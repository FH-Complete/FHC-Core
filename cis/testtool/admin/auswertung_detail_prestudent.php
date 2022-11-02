<?php
/* Copyright (C) 2009 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>.
 */
/**
 * Detailergebnisse eines Prestudenten beim Reihungstest
 */
require_once('../../../config/vilesci.config.inc.php');			
require_once('../../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//DE" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<title>Reihungstest Detailergebnis</title>
		<link rel="stylesheet" href="../../../skin/jquery.css" type="text/css"/>
		<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../../include/js/tablesort/table.css" type="text/css">
		<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" href="../../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../../vendor/jquery/sizzle/sizzle.js"></script> 
	</head>
	<body class="Background_main">
	<h2>Auswertung Reihungstest Detailergebnis PrestudentIn '.$db->convert_html_chars($_GET['prestudent_id']).'</h2>';

if(!$rechte->isBerechtigt('lehre/reihungstest', null, 's'))
		die('Sie haben keine Berechtigung fuer diese Seite');

// Testergebnisse anzeigen
//echo '<hr><br><form action="'.$_SERVER['PHP_SELF'].'" method="POST">Testergebnisse der Person mit der Prestudent_id <input type="text" name="prestudent_id"><input type="submit" value="anzeigen" name="testergebnisanzeigen"></form>';
if(isset($_GET['prestudent_id']))
{
	if(is_numeric($_GET['prestudent_id']) && $_GET['prestudent_id']!='')
	{
		$qry="SELECT nachname,vorname,person_id,prestudent_id,tbl_pruefling.pruefling_id,tbl_pruefling_frage.begintime,bezeichnung,kurzbz,tbl_frage.nummer,level, tbl_vorschlag.nummer as antwortnummer, tbl_vorschlag.punkte
				FROM testtool.tbl_antwort
				JOIN testtool.tbl_vorschlag USING(vorschlag_id)
				JOIN testtool.tbl_frage USING (frage_id)
				JOIN testtool.tbl_gebiet USING (gebiet_id)
				JOIN testtool.tbl_pruefling USING (pruefling_id)
				JOIN testtool.tbl_pruefling_frage ON (tbl_pruefling.pruefling_id=tbl_pruefling_frage.pruefling_id AND tbl_frage.frage_id =tbl_pruefling_frage.frage_id)
				JOIN public.tbl_prestudent USING (prestudent_id)
				JOIN public.tbl_person USING (person_id)
				WHERE prestudent_id=".$db->db_add_param($_GET['prestudent_id'], FHC_INTEGER)."
				ORDER BY kurzbz,tbl_pruefling_frage.begintime,nummer";
		if($result = $db->db_query($qry))
		{
			echo '<table class="liste table-stripeclass:alternate table-autostripe">
					<thead>
					<tr>
						<th>Nachname</th>
						<th>Vorname</th>
						<th>PersonID</th>
						<th>PrestudentID</th>
						<th>PrueflingID</th>
						<th>Beginnzeit</th>
						<th>Gebiet</th>
						<th>Frage #</th>
						<th>Level</th>
						<th>Antwort #</th>
						<th>Punkte</th>
					</tr>
					</thead>
					<tbody>';
			while($row = $db->db_fetch_object($result))
			{
				echo '<tr>';
				echo "<td>$row->nachname</td>";
				echo "<td>$row->vorname</td>";
				echo "<td>$row->person_id</td>";
				echo "<td>$row->prestudent_id</td>";
				echo "<td>$row->pruefling_id</td>";
				echo "<td>$row->begintime</td>";
				echo "<td>$row->bezeichnung ($row->kurzbz)</td>";
				echo "<td>$row->nummer</td>";
				echo "<td>$row->level</td>";
				echo "<td>$row->antwortnummer</td>";
				echo "<td>$row->punkte</td>";
				echo '</tr>';
			}
			echo '</tbody></table>';
		}
	}
}
	
echo '</body>
</html>';
?>
