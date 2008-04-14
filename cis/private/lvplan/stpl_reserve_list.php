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
	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/benutzerberechtigung.class.php');

	$uid=get_uid();
	
	if (isset($_GET['id']))
		$id=$_GET['id'];

	// Datenbankverbindung
	if (!$conn = pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');
		
	$rechte = new benutzerberechtigung($conn);
	$rechte->getBerechtigungen($uid);
	
	// Datums Format und search_path
	if(!$erg_std=pg_query($conn, "SET datestyle TO ISO; SET search_path TO campus;"))
		die(pg_last_error($conn));

	if (isset($id))
	{
		$sql_query="DELETE FROM tbl_reservierung WHERE reservierung_id=$id";
		$erg=pg_exec($conn, $sql_query);
	}

	
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Reservierungsliste</title>
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
</head>
<body id="inhalt">
	<H2><table class="tabcontent">
		<tr>
		<td>&nbsp;<a class="Item" href="index.php">Lehrveranstaltungsplan</a> &gt;&gt; Reservierungen</td>
		<td align="right"><A href="help/index.html" class="hilfe" target="_blank">HELP&nbsp;</A></td>
		</tr>
		</table>
	</H2>
	<?php
	//Aktuelle Reservierungen abfragen.
	$datum=mktime();
	$datum=date("Y-m-d",$datum);
	
	//EIGENE
	$sql_query="SELECT * FROM vw_reservierung WHERE datum>='$datum' AND uid='$uid'";
	$sql_query.=" ORDER BY  datum, titel, ort_kurzbz, stunde";
	$erg_res=pg_query($conn, $sql_query);
	$num_rows_res=pg_numrows($erg_res);
	
	if ($num_rows_res>0)
	{
		echo 'Eigene Reservierungen:<br>';
		echo '<table border="0">';
		echo '<tr class="liste"><th>Datum</th><th>Titel</th><th>Stunde</th><th>Ort</th><th>Person</th><th>Beschreibung</th></tr>';
		for ($i=0; $i<$num_rows_res; $i++)
		{
			$zeile=$i % 2;
			$id=pg_result($erg_res,$i,"reservierung_id");
			$datum=pg_result($erg_res,$i,"datum");
			$titel=pg_result($erg_res,$i,"titel");
			$stunde=pg_result($erg_res,$i,"stunde");
			$ort_kurzbz=pg_result($erg_res,$i,"ort_kurzbz");
			$pers_uid=pg_result($erg_res,$i,"uid");
			//$lektor_kurzbz=pg_result($erg_res,$i,"lektor_kurzbz");
			$beschreibung=pg_result($erg_res,$i,"beschreibung");
			echo '<tr class="liste'.$zeile.'">';
			echo '<td>'.$datum.'</td>';
			echo '<td>'.$titel.'</td>';
			echo '<td>'.$stunde.'</td>';
			echo '<td>'.$ort_kurzbz.'</td>';
			echo '<td>'.$pers_uid.'</td>';
			echo '<td>'.$beschreibung.'<a  name="liste'.$i.'">&nbsp;</a></td>';
			$z=$i-1;
			if (($pers_uid==$uid)|| $rechte->isBerechtigt('admin', 0, 'suid'))
				echo '<td><A class="Item" href="stpl_reserve_list.php?id='.$id.'#liste'.$z.'">Delete</A></td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	
	echo '<br><br>';
	//ALLE
	$sql_query="SELECT * FROM vw_reservierung WHERE datum>='$datum' ";
	$sql_query.=" ORDER BY  datum, titel, ort_kurzbz, stunde";
	$erg_res=pg_query($conn, $sql_query);
	$num_rows_res=pg_numrows($erg_res);
	
	if ($num_rows_res>0)
	{
		echo 'Alle Reservierungen:<br>';
		echo '<table border="0">';
		echo '<tr class="liste"><th>Datum</th><th>Titel</th><th>Stunde</th><th>Ort</th><th>Person</th><th>Beschreibung</th></tr>';
		for ($i=0; $i<$num_rows_res; $i++)
		{
			$zeile=$i % 2;
			$id=pg_result($erg_res,$i,"reservierung_id");
			$datum=pg_result($erg_res,$i,"datum");
			$titel=pg_result($erg_res,$i,"titel");
			$stunde=pg_result($erg_res,$i,"stunde");
			$ort_kurzbz=pg_result($erg_res,$i,"ort_kurzbz");
			$pers_uid=pg_result($erg_res,$i,"uid");
			//$lektor_kurzbz=pg_result($erg_res,$i,"lektor_kurzbz");
			$beschreibung=pg_result($erg_res,$i,"beschreibung");
			echo '<tr class="liste'.$zeile.'">';
			echo '<td>'.$datum.'</td>';
			echo '<td>'.$titel.'</td>';
			echo '<td>'.$stunde.'</td>';
			echo '<td>'.$ort_kurzbz.'</td>';
			echo '<td>'.$pers_uid.'</td>';
			echo '<td>'.$beschreibung.'<a  name="liste'.$i.'">&nbsp;</a></td>';
			$z=$i-1;
			if (($pers_uid==$uid) || $rechte->isBerechtigt('admin', 0, 'suid'))
				echo '<td><A class="Item" href="stpl_reserve_list.php?id='.$id.'#liste'.$z.'">Delete</A></td>';
			echo '</tr>';
		}
		echo '</table>';
	}
?>
</body>
</html>