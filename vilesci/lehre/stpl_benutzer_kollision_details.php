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

require_once('../config.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/stundenplan.class.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Herstellen der Datenbankverbindung');

$student_uid = (isset($_GET['uid'])?$_GET['uid']:'');
$datum = (isset($_GET['datum'])?$_GET['datum']:'');
$stunde = (isset($_GET['stunde'])?$_GET['stunde']:'');

$user = get_uid();
#gss loadVariables($conn, $user);
loadVariables($user);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Kollision Student</title>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css" />
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
</head>
<body style="background-color:#eeeeee;">
';
if(isset($_GET['type']) && $_GET['type']=='delete')
{
	if(isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$stdplan = new stundenplan($conn, $db_stpl_table);
		if($stdplan->delete($_GET['id']))
		{
			echo 'Eintrag wurde geloescht';
		}
		else 
		{
			echo "Fehler beim Loeschen des Eintrages: $stdplan->errormsg";
		}		
	}
	else 
	{
		echo 'ID muss uebergeben werden';
	}
}

$stg_obj = new studiengang($conn);
$stg_obj->getAll('typ, kurzbz', false);
$stg_arr = array();

foreach ($stg_obj->result as $stg)
{
	$stg_arr[$stg->studiengang_kz] = $stg->kuerzel;
}
	
if($student_uid!='')
{
	echo "<h2>UNR - $db_stpl_table</h2>";
	$qry = "SELECT datum, stunde, student_uid, unr
			FROM  lehre.vw_".$db_stpl_table."_student_unr
			WHERE datum='$datum' AND stunde='$stunde' AND student_uid='$student_uid'
			ORDER BY unr LIMIT 30; 
		   ";
	
	echo '<table class="liste table-autosort:0 table-stripeclass:alternate table-autostripe">
		<thead>';
	echo '<tr class="liste">
			<th class="table-sortable:default">UNR</th>
			<th class="table-sortable:default">Datum</th>
			<th class="table-sortable:default">Stunde</th>
			<th class="table-sortable:default">Gruppen</th>			
		  </tr>
		 </thead>
		 <tbody>';
	
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			$gruppen='';
			$qry = "SELECT distinct studiengang_kz, semester, verband, gruppe, gruppe_kurzbz FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehreinheitgruppe USING(lehreinheit_id) 
			        WHERE unr='$row->unr'";
			if($result_grp = pg_query($conn, $qry))
			{
				while($row_grp = pg_fetch_object($result_grp))
				{
					if($row_grp->gruppe_kurzbz!='')
						$gruppen.="$row_grp->gruppe_kurzbz, ";
					else 
						$gruppen.=$stg_arr[$row_grp->studiengang_kz].'-'.$row_grp->semester.$row_grp->verband.$row_grp->gruppe.', ';
				}
			}
			//letzten Beistrich wieder entfernen
			$gruppen = mb_substr($gruppen, 0, mb_strlen($gruppen,'UTF-8')-2,'UTF-8');
			
			echo "<tr>";
			echo "<td class='table-sortable:default' align='center'>$row->unr</td>";
			echo "<td class='table-sortable:default' align='center'>$row->datum</td>";
			echo "<td class='table-sortable:default' align='center'>$row->stunde</td>";
			echo "<td class='table-sortable:default' align='center'>$gruppen</td>";			
			echo "</tr>";
		}
	}
	
	echo '</tbody></table>';
}
else 
{
	echo "<h2>Stundenplaneinträge - $db_stpl_table</h2>";
	
	
	$qry = "SELECT * FROM lehre.tbl_$db_stpl_table WHERE datum='$datum' AND stunde='$stunde'";
	
	echo '<table class="liste table-autosort:0 table-stripeclass:alternate table-autostripe">
		<thead>';
	echo '<tr class="liste">
			<th class="table-sortable:default">ID</th>
			<th class="table-sortable:default">LEID</th>
			<th class="table-sortable:default">UNR</th>
			<th class="table-sortable:default">STG</th>
			<th class="table-sortable:default">Gruppe</th>
			<th class="table-sortable:default">Lektor</th>
			<th class="table-sortable:default">Datum</th>
			<th class="table-sortable:default">Stunde</th>
			<th class="table-sortable:default">Ort</th>
		  </tr>
		 </thead>
		 <tbody>';
	
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			$gruppe='';
			if($row->gruppe_kurzbz!='')
				$gruppe=$row->gruppe_kurzbz;
			else 
				$gruppe=$stg_arr[$row->studiengang_kz].'-'.$row->semester.$row->verband.$row->gruppe;
				
			echo "<tr>";
			$id = ($db_stpl_table."_id");
			echo "<td class='table-sortable:default' align='center'>".$row->$id."</td>";
			echo "<td class='table-sortable:default' align='center'>$row->lehreinheit_id</td>";
			echo "<td class='table-sortable:default' align='center'>$row->unr</td>";
			echo "<td class='table-sortable:default' align='center'>".$stg_arr[$row->studiengang_kz]." - $row->semester</td>";
			echo "<td class='table-sortable:default' align='center'>$gruppe</td>";
			echo "<td class='table-sortable:default' align='center'>$row->mitarbeiter_uid</td>";
			echo "<td class='table-sortable:default' align='center'>$row->datum</td>";
			echo "<td class='table-sortable:default' align='center'>$row->stunde</td>";
			echo "<td class='table-sortable:default' align='center'>$row->ort_kurzbz</td>";
			echo "<td class='table-sortable:default' align='center'><a href='".$_SERVER['PHP_SELF']."?datum=$datum&stunde=$stunde&type=delete&id=".$row->$id."' onclick='return confirm(\"Diesen Datensatz wirklich loeschen?\")'>delete</a></td>";
			echo "</tr>";
		}
	}
	
	echo '</tbody></table>';
}

echo '</body></html>';
?>