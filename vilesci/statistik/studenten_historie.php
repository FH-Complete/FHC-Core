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
 *         
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/fachbereich.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/statistik.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$stg_obj = new studiengang();
$stg_obj->getAll('typ, kurzbz', false);

$fb_obj = new fachbereich();
$fb_obj->getAll();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen(get_uid());

echo '
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
		<html>
		<head>
		<title>Institutsliste</title>
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
		</head>
		<body class="Background_main">
		<h2>Liste der MitarbeiterInnen der Institute an der Fachhochschule Technikum Wien</h2>';

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
		echo 'Sie haben keine Berechtigung fuer diese Seite'; //die
}

$statistik = new statistik();
$result=$statistik->get_prestudenten(335,'WS2008',1);

if($result)
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
						<th class='table-sortable:numeric'>$ws</th>
						<th class='table-sortable:numeric'>$ss</th>
						<th class='table-sortable:default'>Studiengang</th>
						<th class='table-sortable:default'>Hauptzuteilung</th>
						<th class='table-sortable:default'>Sonstige</th>
					</tr>
				</thead>
				<tbody>";

	//while($row = $db->db_fetch_object($result))
	//{
		echo '<tr>';
		echo '<td>'.$statistik->statistik_obj[0].'</td>';
		echo "<td>$row->vorname</td>";
		echo "<td>".($row->fixangestellt=='t'?'fix':'frei')."</td>";
		echo "<td>$row->kompetenzen</td>";
		echo "<td>$row->lvs_wintersemester</td>";
		echo "<td>$row->lvs_sommersemester</td>";
		echo '<td>';
		
		echo '</td>';
		echo "<td>";
		echo "</td>";
		echo '</tr>';
	//}
	echo '</tbody></table>';
}

echo '</body></html>';
?>