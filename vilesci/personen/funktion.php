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
/**
 * Changes:	23.10.2004: Anpassung an neues DB-Schema (WM)
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/basis_db.class.php');

$uid = get_uid();

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('mitarbeiter',null,'suid'))
	die($rechte->errormsg);

$sql_query="SELECT beschreibung,funktion_kurzbz FROM public.tbl_funktion ORDER BY funktion_kurzbz";
$result_funktion=$db->db_query($sql_query);
if(!$result_funktion)
	die("funktion not found!" .$db->db_last_error());
?>
<!DOCTYPE HTML>
<html>
<head>
	<title>Funktion</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link href="../../skin/tablesort.css" rel="stylesheet" type="text/css"/>
	<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
	<script language="Javascript">
	$(document).ready(function()
			{
				$("#t1").tablesorter(
					{
						sortList: [[0,0]],
						widgets: ["zebra"],
						headers: {2:{sorter:false},3:{sorter:false}}
					});
			});
</script>
<body>
<H2>Funktionen</H2>
<h3>&Uuml;bersicht</h3>
<table id="t1" class="tablesorter">

<?php
if ($result_funktion!=0)
{
	$num_rows=$db->db_num_rows($result_funktion);
	$num_fields=$db->db_num_fields($result_funktion);

	echo '<thead>
			<tr>';
	for ($i=0;$i<$num_fields; $i++)
		echo "<th class='table-sortable:default'>".$db->db_field_name($result_funktion,$i)."</th>";
	echo '<th></th>';
	echo '<th></th>';
	echo '</tr></thead><tbody>';
	for ($j=0; $j<$num_rows;$j++)
	{
		$row=$db->db_fetch_row($result_funktion,$j);

		echo "<tr>";
	    for ($i=0; $i<$num_fields; $i++)
			echo "<td>$row[$i]</td>";

		echo "<td><a href=\"funktion_det.php?kurzbz=$row[1]\">Details</a></td>";
		echo "<td><a href=\"../stammdaten/benutzerberechtigung_details.php?funktion_kurzbz=$row[1]\">Berechtigungen</a></td>";
	    echo "</tr>\n";
	}
}
else
	echo "Kein Eintrag gefunden!";
?>
</tbody>
</table>
</body>
</html>
