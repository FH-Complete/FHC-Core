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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
   
/*
 * Prueft im Stundenplan ob die Personen in den zugeteilten Räumen Platz haben
 */
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/ort.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$beginn = (isset($_GET['beginn'])?$_GET['beginn']:'');
$ende = (isset($_GET['ende'])?$_GET['ende']:'');
$stg_kz = (isset($_GET['stg_kz'])?$_GET['stg_kz']:'');
$dontloadcontent=false;

$user = get_uid();
loadVariables($user);
if (empty($db_stpl_table))
	die('Bitte die Variablen warten! db_stpl_table ist leer');

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>LV-Plan - Überbuchungen</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css" />
	<link rel="stylesheet" href="../../../skin/jquery.css" type="text/css"/>
	<link rel="stylesheet" type="text/css" href="../../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../../vendor/jquery/sizzle/sizzle.js"></script>	
	<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>
	<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
	
	<script type="text/javascript">	
		$(document).ready(function() 
			{ 
			    $("#t1").tablesorter(
				{
					 headers:
				       {  
				         0 : { sorter: "isoDate"  },
				       }, 
					sortList: [[0,0],[1,0]],
					widgets: [\'zebra\']
				}); 
			} 
		);
		</script>
</head>
<body>
<h2>LV-Plan Überbuchungen - '.$db_stpl_table.'</h2>
';
echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';

if($beginn=='' || $ende=='')
{
	$stsem_obj = new studiensemester();
	$stsem_akt = $stsem_obj->getaktorNext();
	$stsem_obj->load($stsem_akt);
	
	//$beginn = $stsem_obj->start;
	$beginn = date("Y-m-d");
	//$ende = $stsem_obj->ende;
	$ende = date("Y-m-d", strtotime('+8 days'));
	$dontloadcontent=true;
}

echo " Beginn <INPUT type='text' size='10' id='beginn' name='beginn' value='$beginn'>";
echo " Ende <INPUT type='text' size='10' id='ende' name='ende' value='$ende'>";

$stg = new studiengang();
$stg->getAll('typ, kurzbzlang', true);

echo ' Studiengang <SELECT name="stg_kz">';
echo '<option value="">-- Alle --</option>';
foreach ($stg->result as $row) 
{
	if($stg_kz==$row->studiengang_kz)
		$selected='selected';
	else 
		$selected='';
	
	echo '<option value="'.$row->studiengang_kz.'" '.$selected.'>'.$row->kuerzel.'</option>';
}
echo '</SELECT>';

echo " <INPUT type='submit' value='OK'>";
echo '</form>';

if($dontloadcontent)
	exit;

	
$ort_obj = new ort();
$ort_obj->getAll();
$ort = array();

foreach ($ort_obj->result as $row)
{
	$ort[$row->ort_kurzbz] = new stdClass(); // Prevents the warning "Creating default object from empty value"
	$ort[$row->ort_kurzbz]->max_person = $row->max_person;
}
$qry = "SELECT DISTINCT vw_".$db_stpl_table.".unr,datum, stunde, ort_kurzbz, studiensemester_kurzbz, vw_".$db_stpl_table.".studiengang_kz, vw_".$db_stpl_table.".semester, verband, gruppe, gruppe_kurzbz, UPPER(stg_typ || stg_kurzbz) as stg_kurzbz, lehrfach, lehrfach_bez 
		FROM lehre.vw_".$db_stpl_table." JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) JOIN lehre.tbl_lehrveranstaltung ON(tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id)
		WHERE datum>='".addslashes($beginn)."' AND datum<='".addslashes($ende)."'";
if($stg_kz!='')
	$qry.=" AND tbl_lehrveranstaltung.studiengang_kz='".addslashes($stg_kz)."'";

$qry.=" ORDER BY unr, datum, stunde, ort_kurzbz, studiensemester_kurzbz, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, stg_kurzbz";

echo '<table class="tablesorter" id="t1">
		<thead>
		<tr>
			<th class="table-sortable:date">Datum</th>
			
			<th>Ort</th>
			<th>Studierende aktuell (Plätze maximal)</th>
			<th>Gruppen (Studierende aktuell)</th>
			<th>Lehrfach</th>
		</tr>
		</thead>
		<tbody>';

//echo $qry;
$lastdatum=0;
$laststunde=0;
$lastort=0;
$anzahl_studenten=0;
$lehrfach='';
$lehrfach_bez='';
$arr=array();

function getAnzahl($studiengang_kz, $semester, $verband, $gruppe, $gruppe_kurzbz, $studiensemester_kurzbz)
{
	global $db;
	if($gruppe_kurzbz=='')
	{
		$qry = "SELECT count(*) as anzahl FROM public.tbl_studentlehrverband 
				WHERE studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'
				AND studiengang_kz='".addslashes($studiengang_kz)."' AND
				semester='".addslashes($semester)."'";
		if(trim($verband)!='')
			$qry.=" AND trim(verband)=trim('".addslashes($verband)."')";
		if(trim($gruppe)!='')
			$qry.=" AND trim(gruppe)=trim('".addslashes($gruppe)."')";
		
	}
	else 
	{
		$qry = "SELECT count(*) as anzahl FROM public.tbl_benutzergruppe 
				WHERE studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'
				AND gruppe_kurzbz='".addslashes($gruppe_kurzbz)."'";
	}
	if($res_anz = $db->db_query($qry))
	{
		if($row_anz = $db->db_fetch_object($res_anz))
		{
			return $row_anz->anzahl;
		}
	}
}
$gruppen='';
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if($lastdatum==$row->datum && $laststunde==$row->stunde && $lastort==$row->ort_kurzbz && $lehrfach==$row->lehrfach && $lehrfach_bez==$row->lehrfach_bez)
		{
			//Solange alles gleich ist zusammenzaehlen
			$anzahl = getAnzahl($row->studiengang_kz, $row->semester, $row->verband, $row->gruppe, $row->gruppe_kurzbz, $row->studiensemester_kurzbz);		
			$anzahl_studenten += $anzahl;
			$gruppen .=($row->gruppe_kurzbz==''?$row->stg_kurzbz.$row->semester.$row->verband.$row->gruppe:$row->gruppe_kurzbz)." ($anzahl), ";
		}
		else 
		{
			if($lastdatum!=0)
			{
				// wenn sich der Raum, Datum oder Stunde aendert, dann pruefen ob die Anzahl in den Raum passt
				// und ggf eine Meldung ausgeben
				$gruppen = mb_substr($gruppen, 0, mb_strlen($gruppen)-2);
				if($anzahl_studenten>$ort[$lastort]->max_person)
				{
					//$diff = $anzahl_studenten-$ort[$lastort]->max_person;
					$diffprozent = ($ort[$lastort]->max_person)/100*10;
					
					$style='';
					if((($ort[$lastort]->max_person+$diffprozent)-$anzahl_studenten)<0)
						$style='style="background-color: FCC850;"';
					if((($ort[$lastort]->max_person+$diffprozent)-$anzahl_studenten)<-2)
						$style='style="background-color: FF702D;"';
					if((($ort[$lastort]->max_person+$diffprozent)-$anzahl_studenten)<-4)
						$style='style="background-color: e83700;"';
					if((($ort[$lastort]->max_person+$diffprozent)-$anzahl_studenten)<-6)
						$style='style="background-color: a00404; color: d3d3d3"';

					//echo "<tr><td>$lastdatum</td><td>$laststunde</td><td>$lastort</td><td $style>$anzahl_studenten (".$ort[$lastort]->max_person.")</td><td>$gruppen</td><td>$lehrfach - $lehrfach_bez</td></tr>";
					$arr[]="<tr><td>$lastdatum</td><td>$lastort</td><td $style>$anzahl_studenten (".$ort[$lastort]->max_person.")</td><td>$gruppen</td><td>$lehrfach - $lehrfach_bez</td></tr>";
					
				}
				$anzahl_studenten=0;
				$gruppen='';
			}
			
			$anzahl = getAnzahl($row->studiengang_kz, $row->semester, $row->verband, $row->gruppe, $row->gruppe_kurzbz, $row->studiensemester_kurzbz);
			$anzahl_studenten += $anzahl;
			$gruppen .=($row->gruppe_kurzbz==''?$row->stg_kurzbz.$row->semester.$row->verband.$row->gruppe:$row->gruppe_kurzbz)." ($anzahl), ";
		}
		$lastdatum = $row->datum;
		$laststunde = $row->stunde;
		$lastort = $row->ort_kurzbz;
		$lehrfach = $row->lehrfach;
		$lehrfach_bez = $row->lehrfach_bez;
	}
}
else 
{
	echo "Fehler:".$qry;
}
$arr=array_unique($arr);
foreach ($arr AS $row)
	echo $row;
echo '</tbody></table>';
?>
</body>
</html>