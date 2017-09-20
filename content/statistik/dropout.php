<?php
/* Copyright (C) 2012 fhcomplete.org
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


$html='';
$csv='';
$json='{';
$user = get_uid();
$db = new basis_db();
$var = new variable();
$var->loadVariables($user);

$stg = new studiengang();
$stg->getAll('typ, kurzbz');

if(isset($_REQUEST['outputformat']))
	$outputformat = $_REQUEST['outputformat'];
else
	$outputformat = 'html';
	
if(isset($_REQUEST['stsem']))
	$studiensemester_kurzbz = $_REQUEST['stsem'];
else
	$studiensemester_kurzbz = $var->variable->semester_aktuell;

$stsem = new studiensemester();
$stsem->getAll();

$html.= '<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" href="../../skin/jquery.css" type="text/css"/>
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css"/>
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css" />
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jqueryV1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
	<title>DropOut Statistik</title>
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
<body>
<h2>DropOut Statistik - Studiensemester '.$db->convert_html_chars($studiensemester_kurzbz).'</h2>
';
$html.= '<form action="'.$_SERVER['PHP_SELF'].'" METHOD="POST">
Studiensemester: <SELECT name="stsem">';
foreach($stsem->studiensemester as $row)
{
	if($studiensemester_kurzbz==$row->studiensemester_kurzbz)
		$selected='selected';
	else
		$selected='';
		
	$html.= '<OPTION value="'.$row->studiensemester_kurzbz.'" '.$selected.'>'.$row->studiensemester_kurzbz.'</OPTION>';
}
$html.= '</SELECT>
<input type="submit" value="Anzeigen" /></form>';
$html.= '
<table id="myTable" class="tablesorter">
	<thead>
		<tr>
			<th nowrap>Studiengang</th>
			<th nowrap>Anfänger M</th>
			<th nowrap>Anfänger W</th>
			<th nowrap>Anfänger Gesamt</th>
			<th nowrap>Abbrecher M</th>
			<th nowrap>Abbrecher W</th>
			<th nowrap>Abbrecher Gesamt</th>
			<th nowrap>DropOut in %</th>
		</tr>
	</thead>
	<tbody>	
';
$csv.='"Studiengang",	"Anfaenger M",	"Anfaenger W",	"Anfaenger Gesamt",	"Abbrecher M",	"Abbrecher W",	"Abbrecher Gesamt",	"DropOut in %"'."\r\n";

$summe_anfaenger_m=0;
$summe_anfaenger_w=0;
$summe_anfaenger_gesamt=0;
$summe_abbrecher_m=0;
$summe_abbrecher_w=0;
$summe_abbrecher_gesamt=0;

foreach($stg->result as $row_stg)
{
	if($row_stg->typ!='b' && $row_stg->typ!='m')
		continue;
	$html.= "<tr>\n";
	$html.= '<td>'.$db->convert_html_chars($row_stg->kuerzel).'</td>';
	$csv.='"'.$row_stg->kuerzel.'",	';
	$json.='"'.$row_stg->kuerzel.'":{';
	
	//Studienanfaenger
	$qry = "
	SELECT 
		count(*) anzahl, geschlecht
	FROM 
		public.tbl_prestudent 
		JOIN public.tbl_prestudentstatus status USING(prestudent_id)
		JOIN public.tbl_person USING(person_id)		
	WHERE 
		bismelden=true
		AND	(status_kurzbz='Student' OR status_kurzbz='Unterbrecher')
		AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." 
		AND NOT EXISTS (SELECT 1 FROM public.tbl_prestudentstatus WHERE prestudent_id=status.prestudent_id AND (status_kurzbz='Student' OR status_kurzbz='Unterbrecher') AND datum<status.datum)
		AND tbl_prestudent.studiengang_kz=".$db->db_add_param($row_stg->studiengang_kz,FHC_INTEGER).
	"GROUP BY geschlecht";
	$anfaenger=array();
	$anfaenger['m']=0;
	$anfaenger['w']=0;
	if($result = $db->db_query($qry))
		while($row = $db->db_fetch_object($result))
			$anfaenger[$row->geschlecht]=$row->anzahl;
			
	$html.= '<td align="right">'.$db->convert_html_chars($anfaenger['m']).'</td>';
	$csv.='"'.$anfaenger['m'].'",	';
	$json.='"Anfaenger M": "'.$anfaenger['m'].'", '; 
	
	$html.= '<td align="right">'.$db->convert_html_chars($anfaenger['w']).'</td>';
	$csv.='"'.$anfaenger['w'].'",	';
	$json.='"Anfaenger W": "'.$anfaenger['w'].'", '; 
	
	$anfaenger_gesamt = array_sum($anfaenger);
	$html.= '<td align="right">'.$db->convert_html_chars($anfaenger_gesamt).'</td>';
	$csv.='"'.$anfaenger_gesamt.'",	';
	$json.='"Anfaenger Gesamt": "'.$anfaenger_gesamt.'", ';
	
	$summe_anfaenger_m+=$anfaenger['m'];
	$summe_anfaenger_w+=$anfaenger['w'];
	$summe_anfaenger_gesamt+=$anfaenger_gesamt;
	
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
		AND a.studiengang_kz=".$db->db_add_param($row_stg->studiengang_kz,FHC_INTEGER)."
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
			
	$html.= '<td align="right">'.$db->convert_html_chars($abbrecher['m']).'</td>';
	$csv.='"'.$abbrecher['m'].'",	';
	$json.='"Abbrecher M": "'.$abbrecher['m'].'", ';
	
	$html.= '<td align="right">'.$db->convert_html_chars($abbrecher['w']).'</td>';
	$csv.='"'.$abbrecher['w'].'",	';
	$json.='"Abbrecher W": "'.$abbrecher['w'].'", ';	
	
	$abbrecher_gesamt = array_sum($abbrecher);
	$html.= '<td align="right">'.$db->convert_html_chars($abbrecher_gesamt).'</td>';
	$csv.='"'.$abbrecher_gesamt.'",	';
	$json.='"Abbrecher Gesamt": "'.$abbrecher_gesamt.'", ';	
	
	$summe_abbrecher_m+=$abbrecher['m'];
	$summe_abbrecher_w+=$abbrecher['w'];
	$summe_abbrecher_gesamt+=$abbrecher_gesamt;
	if($abbrecher_gesamt==0 || $anfaenger_gesamt==0)
		$dropout=0;
	else
		$dropout = 100/$anfaenger_gesamt*$abbrecher_gesamt;
	$html.= '<td align="right">'.$db->convert_html_chars(number_format($dropout,2)).' %</td>';
	$csv.='"'.number_format($dropout,2).'"'."\n";
	$json.='"DropOut in %": "'.number_format($dropout,2).'"}, ';	
	
	$html.= "\n</tr>\n";
}
if($summe_abbrecher_gesamt==0 || $summe_anfaenger_gesamt==0)
	$dropout_gesamt=0;
else
	$dropout_gesamt = 100/$summe_anfaenger_gesamt*$summe_abbrecher_gesamt;
$html.= '</tbody>
<tfooter>
	<tr>
		<th></th>
		<th align="right">'.$summe_anfaenger_m.'</th>
		<th align="right">'.$summe_anfaenger_w.'</th>
		<th align="right">'.$summe_anfaenger_gesamt.'</th>
		<th align="right">'.$summe_abbrecher_m.'</th>
		<th align="right">'.$summe_abbrecher_w.'</th>
		<th align="right">'.$summe_abbrecher_gesamt.'</th>
		<th align="right">'.number_format($dropout_gesamt,2).' %</th>
	</tr>
</tfooter>		
</table>';

$html.= '</body>
</html>';
// JSON-Ende: letzes Komma loeschen und beenden
$json=substr($json,0,-2).'}';
switch ($outputformat)
{
	case 'csv':
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=dropout.csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo $csv;
		break;
	case 'json':
		header("Content-type: application/json");
		header("Content-Disposition: attachment; filename=dropout.json");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo $json;
		//echo '{"one": "Singular sensation","two": "Beady little eyes","three": "Little birds pitch by my doorstep"}';
		break;
	default:
		echo $html;
}
?>
