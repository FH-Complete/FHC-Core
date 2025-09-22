<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/variable.class.php');
require_once('../../include/studiensemester.class.php');

$user = get_uid();
$db = new basis_db();
$var = new variable();
$var->loadVariables($user);

$stg = new studiengang();
$stg->getAll('typ, kurzbz');

if(isset($_REQUEST['stsem']))
	$studiensemester_kurzbz = $_REQUEST['stsem'];
else
	$studiensemester_kurzbz = $var->variable->semester_aktuell;

$stsem = new studiensemester();
$stsem->getAll();

$studiengang_kz='';
if(isset($_REQUEST['stg_kz']))
	if(is_numeric($_REQUEST['stg_kz']))
		$studiengang_kz = $_REQUEST['stg_kz'];
	
echo '<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" href="../../skin/jquery.css" type="text/css"/>
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css"/>
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css" />
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
	<title>DropOut ZGV Statistik</title>
	<script type="text/javascript">
	$(document).ready(function() 
	{ 
	    $("#myTable").tablesorter(
		{
			sortList: [[0,0]],
			widgets: ["zebra"]
		});
	}); 
	</script>
</head>
<body>';

$stgkuerzel='';
if($studiengang_kz!='')
{
	$stg_obj = new studiengang();
	$stg_obj->load($studiengang_kz);
	$stgkuerzel=$stg_obj->kuerzel;
}
echo '
<h2>DropOut ZGV - Studiensemester '.$db->convert_html_chars($studiensemester_kurzbz).' Studiengang '.$db->convert_html_chars($stgkuerzel).'</h2>
';
echo '<form action="'.$_SERVER['PHP_SELF'].'" METHOD="POST">
Studiensemester: <SELECT name="stsem">';
foreach($stsem->studiensemester as $row)
{
	if($studiensemester_kurzbz==$row->studiensemester_kurzbz)
		$selected='selected';
	else
		$selected='';
		
	echo '<OPTION value="'.$row->studiensemester_kurzbz.'" '.$selected.'>'.$row->studiensemester_kurzbz.'</OPTION>';
}
echo '</SELECT>';

echo ' Studiengang: <SELECT name="stg_kz">';
foreach($stg->result as $row)
{
	if($studiengang_kz=='')
		$studiengang_kz=$row->studiengang_kz;
		
	if($studiengang_kz==$row->studiengang_kz)
		$selected='selected';
	else
		$selected='';
		
	echo '<OPTION value="'.$row->studiengang_kz.'" '.$selected.'>'.$row->kuerzel.'</OPTION>';
}
echo '</SELECT>';

echo '
<input type="submit" value="Anzeigen" /></form>';
echo '
<table id="myTable" class="tablesorter">
	<thead>
		<tr>
			<th nowrap>ZGV</th>
			<th nowrap>Abbrecher M</th>
			<th nowrap>Abbrecher W</th>
			<th nowrap>Abbrecher Gesamt</th>
		</tr>
	</thead>
	<tbody>	
';

$summe_abbrecher_m=0;
$summe_abbrecher_w=0;
$summe_abbrecher_gesamt=0;
$qry="SELECT * FROM bis.tbl_zgv";
if($result_zgv = $db->db_query($qry))
{
	while($row_zgv = $db->db_fetch_object($result_zgv))
	{
		echo "<tr>\n";
		echo '<td>'.$db->convert_html_chars($row_zgv->zgv_kurzbz).'</td>';
		
		//Studienabbrecher
		//Alle die im Studiensemester $studiensemester_kurzbz zu studieren
		//begonnen haben und bisher abgebrochen haben
		$qry = "
		SELECT 
			count(*) anzahl, geschlecht
		FROM 
			public.tbl_prestudent a
			JOIN public.tbl_prestudentstatus status USING(prestudent_id)
			JOIN public.tbl_person USING(person_id)		
		WHERE 
			bismelden=true
			AND status_kurzbz='Abbrecher' 
			AND a.studiengang_kz=".$db->db_add_param($studiengang_kz,FHC_INTEGER)."
			AND a.zgv_code=".$db->db_add_param($row_zgv->zgv_code)."
			AND EXISTS 
				(
					SELECT
						1 
					FROM 
						public.tbl_prestudentstatus as status
					WHERE 
						prestudent_id=a.prestudent_id
						AND (status_kurzbz='Student' OR status_kurzbz='Unterbrecher')
						AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." 
						AND NOT EXISTS (SELECT 1 FROM public.tbl_prestudentstatus 
										WHERE prestudent_id=status.prestudent_id AND (status_kurzbz='Student' OR status_kurzbz='Unterbrecher')
										AND datum<status.datum)
				)		
		GROUP BY geschlecht";
		$abbrecher=array();
		$abbrecher['m']=0;
		$abbrecher['w']=0;
		if($result = $db->db_query($qry))
			while($row = $db->db_fetch_object($result))
				$abbrecher[$row->geschlecht]=$row->anzahl;
				
		echo '<td align="right">'.$db->convert_html_chars($abbrecher['m']).'</td>';
		echo '<td align="right">'.$db->convert_html_chars($abbrecher['w']).'</td>';
		$abbrecher_gesamt = array_sum($abbrecher);
		echo '<td align="right">'.$db->convert_html_chars($abbrecher_gesamt).'</td>';
		
		$summe_abbrecher_m+=$abbrecher['m'];
		$summe_abbrecher_w+=$abbrecher['w'];
		$summe_abbrecher_gesamt+=$abbrecher_gesamt;
		
		/*
		if($abbrecher_gesamt==0 || $anfaenger_gesamt==0)
			$dropout=0;
		else
			$dropout = 100/$anfaenger_gesamt*$abbrecher_gesamt;
		echo '<td align="right">'.$db->convert_html_chars(number_format($dropout,2)).' %</td>';*/
		echo "\n</tr>\n";
	}
}
/*
if($summe_abbrecher_gesamt==0 || $summe_anfaenger_gesamt==0)
	$dropout_gesamt=0;
else
	$dropout_gesamt = 100/$summe_anfaenger_gesamt*$summe_abbrecher_gesamt;
	*/
echo '</tbody>
<tfooter>
	<tr>
		<th></th>
		<th align="right">'.$summe_abbrecher_m.'</th>
		<th align="right">'.$summe_abbrecher_w.'</th>
		<th align="right">'.$summe_abbrecher_gesamt.'</th>
	</tr>
</tfooter>		
</table>';

echo '</body>
</html>';
?>