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
 *          Gerald Simane-Sequens 	<gerald.simane-sequens@technikum-wien.at >.
 */
/*
 * Erstellt eine Tabelle mit den Absolventen der Studiengänge jedes Studiensemesters
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/functions.inc.php');

$db = new basis_db();

$absolventen=array(array());
$absolventen_m=array(array());
$absolventen_w=array(array());

$sumstg=array();
$sumstg_m=array();
$sumstg_w=array();

$sumsem=0;
$sumsem_m=0;
$sumsem_w=0;
$sumsum=0;
$sumsum_m=0;
$sumsum_w=0;

$trennung=false;

$stsem_obj = new studiensemester();
$stsem = $stsem_obj->getaktorNext();
$stgall = new studiengang();
$stgall->getAll("typ, kurzbz asc",true);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	<style> div.visible{display:block;}
			div.invisible{display:none;}
	</style>
	<script>
		var status="visible";
		function toggle()
		{
			var divs=document.getElementsByTagName("div");
			if (status=="visible")
			{
				status="invisible"
			}
			else
			{	
				status="visible"
			}
			for each (var item in divs)
			{
				item.className=status;
			}
		}
	</script>
	</head>
	<body>';

echo "<h2>AbsolventInnen&uuml;bersicht $stsem </h2>";
if(isset($_REQUEST["trennung"]))
{
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
	echo '<input type="submit" value="Geschlechtertrennung ausblenden"/></form><br><br>';
}
else 
{
	echo '<form action="'.$_SERVER['PHP_SELF'].'?trennung=true" method="POST">';
	echo '<input type="submit" value="Geschlechtertrennung einblenden"/></form><br><br>';
}
echo "<table class='liste table-stripeclass:alternate table-autostripe'><thead><tr><th>Semester</th>";

$stg_arr = array();
foreach ($stgall->result as $row)
{
	if($row->studiengang_kz>0 && $row->studiengang_kz<999)
	{
		echo "<th";
			if(isset($_REQUEST["trennung"]))
				{
					echo " colspan='2'";
				};
			echo ">".$row->kuerzel."</th>";
	}
}
	if(isset($_REQUEST["trennung"]))
		{
			echo "<th colspan='2'>Summe</th></tr></thead>";
			echo "<th></th>";
			foreach ($stgall->result as $row)
				{
					if($row->studiengang_kz>0 && $row->studiengang_kz<999)
					{
					echo "<th>m</th><th>w</th>";
					}
				}
			echo "<th>m</th><th>w</th>";
		}
	else echo "<th>Summe</th></tr></thead>";

echo "<tbody>";



$stsem=$stsem_obj->jump($stsem,1);
$i=0;
while($stsem_obj->jump($stsem,-1)!= $stsem)
{
	$stsem=$stsem_obj->jump($stsem,-1);
	$qry1="SELECT studiengang_kz, count(*) as anzahl FROM tbl_person 
		JOIN tbl_prestudent using(person_id) 
		JOIN tbl_prestudentstatus using(prestudent_id)
		WHERE studiensemester_kurzbz='$stsem' AND status_kurzbz='Absolvent'
			group by studiengang_kz order by studiengang_kz";
	
	$qry2="SELECT studiengang_kz, count(*) as anzahl FROM tbl_person 
		JOIN tbl_prestudent using(person_id) 
		JOIN tbl_prestudentstatus using(prestudent_id)
		WHERE studiensemester_kurzbz='$stsem' AND status_kurzbz='Absolvent' AND geschlecht='m'
			group by studiengang_kz order by studiengang_kz";
	
	$qry3="SELECT studiengang_kz, count(*) as anzahl FROM tbl_person 
		JOIN tbl_prestudent using(person_id) 
		JOIN tbl_prestudentstatus using(prestudent_id)
		WHERE studiensemester_kurzbz='$stsem' AND status_kurzbz='Absolvent' AND geschlecht='w'
			group by studiengang_kz order by studiengang_kz";
	echo "<tr><td>".$stsem."</td>";
	if($result1=$db->db_query($qry1))
	{
		while($row1 = $db->db_fetch_object($result1))
		{
			$absolventen[$stsem][$row1->studiengang_kz]=$row1->anzahl;
		}
	}
	if($result2=$db->db_query($qry2))
	{
		while($row2 = $db->db_fetch_object($result2))
		{
			$absolventen_m[$stsem][$row2->studiengang_kz]=$row2->anzahl;
		}
	}
	if($result3=$db->db_query($qry3))
	{
		while($row3 = $db->db_fetch_object($result3))
		{
			$absolventen_w[$stsem][$row3->studiengang_kz]=$row3->anzahl;
		}
	}
	//echo "<tr><td>";
	$sumsem=0;
	$sumsem_m=0;
	$sumsem_w=0;
	foreach ($stgall->result as $row)
	{
		if($row->studiengang_kz>0 && $row->studiengang_kz<999)
		{
			if(!isset($absolventen[$stsem][$row->studiengang_kz]))
			{
				$absolventen[$stsem][$row->studiengang_kz]='0';
			}
			if(!isset($absolventen_m[$stsem][$row->studiengang_kz]))
			{
				$absolventen_m[$stsem][$row->studiengang_kz]='0';
			}
			if(!isset($absolventen_w[$stsem][$row->studiengang_kz]))
			{
				$absolventen_w[$stsem][$row->studiengang_kz]='0';
			}
			echo "<td align=center>";
				if(isset($_REQUEST["trennung"]))
					{
						echo $absolventen_m[$stsem][$row->studiengang_kz]."</td><td align=center>".$absolventen_w[$stsem][$row->studiengang_kz];
					}
				else echo $absolventen[$stsem][$row->studiengang_kz];
			echo "</td>";
			if(!isset($sumstg[$row->studiengang_kz]))
			{
				$sumstg[$row->studiengang_kz]='0';
			}
			if(!isset($sumstg_m[$row->studiengang_kz]))
			{
				$sumstg_m[$row->studiengang_kz]='0';
			}
			if(!isset($sumstg_w[$row->studiengang_kz]))
			{
				$sumstg_w[$row->studiengang_kz]='0';
			}
			$sumstg[$row->studiengang_kz]+=$absolventen[$stsem][$row->studiengang_kz];
			$sumstg_m[$row->studiengang_kz]+=$absolventen_m[$stsem][$row->studiengang_kz];
			$sumstg_w[$row->studiengang_kz]+=$absolventen_w[$stsem][$row->studiengang_kz];
			$sumsem+=$absolventen[$stsem][$row->studiengang_kz];
			$sumsem_m+=$absolventen_m[$stsem][$row->studiengang_kz];
			$sumsem_w+=$absolventen_w[$stsem][$row->studiengang_kz];
		}
	}
	
		if(isset($_REQUEST["trennung"]))
		{
			echo "<td align=center style='font-weight:bold;'>".$sumsem_m."</td><td align=center style='font-weight:bold;'>".$sumsem_w."";
		}
		else echo "<td align=center style='font-weight:bold;'>".$sumsem;
	echo "</td>";
	echo "</tr>";
}
echo "<tr style='font-weight:bold;'>";
echo "<td>Summe</td>";
foreach ($stgall->result as $row)
{
	if($row->studiengang_kz>0 && $row->studiengang_kz<999)
	{
		echo "<td align=center>";
			if(isset($_REQUEST["trennung"]))
			{
				echo $sumstg_m[$row->studiengang_kz]."</td><td align=center>".$sumstg_w[$row->studiengang_kz];
			}
			else echo $sumstg[$row->studiengang_kz];
		$sumsum+=$sumstg[$row->studiengang_kz];
		$sumsum_m+=$sumstg_m[$row->studiengang_kz];
		$sumsum_w+=$sumstg_w[$row->studiengang_kz];
		echo "</td>";
	}
}
echo "<td align=center>";
	if(isset($_REQUEST["trennung"]))
	{
		echo $sumsum_m."</td><td align=center>".$sumsum_w;
	}
	else echo $sumsum;
echo "</td></tr>";
echo "</tbody></table>";

?>