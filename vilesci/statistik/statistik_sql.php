<?php
/* Copyright (C) 2011 FH Technikum Wien
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
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/statistik.class.php');

if(!isset($_GET['statistik_kurzbz']))
	die('Statistik_kurzbz Parameter fehlt');

$statistik_kurzbz = $_GET['statistik_kurzbz'];

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Statistik</title>	
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css"/>
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css"/>
	<script type="text/javascript" src="../../include/js/jquery.js"></script> 
	<script type="text/javascript">
		$(document).ready(function() 
		{ 
		    $("#myTable").tablesorter(
			{
				widgets: [\'zebra\']
			}); 
		}); 
	
	</script>
</head>
<body>';

$statistik = new statistik();
if(!$statistik->load($statistik_kurzbz))
	die($statistik->errormsg);

echo '<h2>Statistik - '.$statistik->bezeichnung.'</h2>';

if($statistik->sql!='')
{
	$sql = $statistik->sql;
	foreach($_POST as $name=>$value)
	{
		$sql = str_replace('$'.$name,addslashes($value),$sql);
	}
	
	$db = new basis_db();
	if($result = $db->db_query($sql))
	{
		echo '<table class="tablesorter" id="myTable">';
		echo '<thead><tr>';
		$anzahl_spalten = $db->db_num_fields($result);
		for($spalte=0;$spalte<$anzahl_spalten;$spalte++)
		{
			echo '<th>'.$db->db_field_name($result,$spalte).'</th>';
		}
		echo '</tr></thead><tbody>';
		while($row = $db->db_fetch_object($result))
		{
			echo '<tr>';
			$anzahl_spalten = $db->db_num_fields($result);
			for($spalte=0;$spalte<$anzahl_spalten;$spalte++)
			{
				$name = $db->db_field_name($result,$spalte);
				echo '<td>'.$row->$name.'</td>';
			}	
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
}
else
{
	echo 'Zu dieser Statistik gibt es keine SQL Abfrage';
}
?>