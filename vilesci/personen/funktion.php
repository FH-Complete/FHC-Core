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
require_once('../../include/basis_db.class.php');
if (!$db = new basis_db())
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$sql_query="SELECT funktion_kurzbz, beschreibung FROM public.tbl_funktion ORDER BY funktion_kurzbz";
$result_funktion=$db->db_query($sql_query);
if(!$result_funktion)
	die("funktion not found!" .$db->db_last_error());
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Funktion</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
<body>
<H2>Funktionen</H2>
<h3>&Uuml;bersicht</h3>
<table class="liste table-autosort:0 table-stripeclass:alternate table-autostripe">

<?php
if ($result_funktion!=0)
{
	$num_rows=$db->db_num_rows($result_funktion);
	$num_fields=$db->db_num_fields($result_funktion);

	echo '<thead>
			<tr>';
	echo '<th></th>';
	for ($i=0;$i<$num_fields; $i++)
	    echo "<th class='table-sortable:default'>".$db->db_field_name($result_funktion,$i)."</th>";
	echo '</tr></thead><tbody>';
	for ($j=0; $j<$num_rows;$j++)
	{
		$row=$db->db_fetch_row($result_funktion,$j);
		
		echo "<tr>";
		echo "<td><a href=\"funktion_det.php?kurzbz=$row[0]\">Details</a></td>";
	    for ($i=0; $i<$num_fields; $i++)
			echo "<td>$row[$i]</td>";
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