<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 *          Karl Burkhart <burkhart@technikum-wien.at>
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/studiensemester.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Incoming</title>
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">	
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script> 
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
</head>
<body>
<h2>Incoming</h2>';

$studiengang_kz = isset($_REQUEST['studiengang_kz'])?$_REQUEST['studiengang_kz']:'';

echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
$stg_obj = new studiengang();
$stg_obj->getAll('typ, kurzbz');
echo "\n",'Studiengang <SELECT name="studiengang_kz">
<OPTION value="">-- Alle --</OPTION>';
foreach($stg_obj->result as $row)
{
	if($row->studiengang_kz==$studiengang_kz)
		$selected='selected';
	else
		$selected='';
	echo '<OPTION value="'.$row->studiengang_kz.'" '.$selected.'>'.$row->kuerzel.' ('.$row->kurzbzlang.')</OPTION>';
}
echo '</SELECT>';
echo '&nbsp;&nbsp;<input type="submit" name="show" value="OK"></form>';

$stsem = new studiensemester();
$stsem->getFinished();
foreach($stsem->studiensemester as $row)
{
	$qry="SELECT 
			distinct vorname, nachname, studiengang_kz, UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) as stg
		  FROM 
		  	public.tbl_prestudent 
		  	JOIN public.tbl_prestudentstatus USING(prestudent_id)
		  	JOIN public.tbl_person USING(person_id)
		  	JOIN public.tbl_studiengang USING(studiengang_kz)
		  WHERE
		  	tbl_prestudentstatus.status_kurzbz='Incoming'
		  	AND tbl_prestudentstatus.studiensemester_kurzbz='".$row->studiensemester_kurzbz."'";
	if($studiengang_kz!='')
		$qry.=" AND tbl_prestudent.studiengang_kz='".addslashes($studiengang_kz)."'";
	$qry.=" ORDER BY stg";
		
	if($result = $db->db_query($qry))
	{
		$anzahl = $db->db_num_rows($result);
		echo '<h3>'.$row->studiensemester_kurzbz.' Anzahl: '.$anzahl.'</h3>';
		
		if($anzahl>0)
		{
			echo '
			<script type="text/javascript">
			$(document).ready(function() 
				{ 
				    $("#'.$row->studiensemester_kurzbz.'").tablesorter(
					{
						sortList: [[2,0]],
						widgets: ["zebra"]
					}); 
				} 
			); 
			</script>
			<table id="'.$row->studiensemester_kurzbz.'" class="tablesorter" style="width:auto">
			<thead>
			<tr>
				<th>Nachname</th>
				<th>Vorname</th>
				<th>Studiengang</th>
			</tr>
			</thead>
			<tbody>';
			
			while($row = $db->db_fetch_object($result))
			{
				echo '<tr>';
				echo '<td>'.$row->nachname.'</td>';
				echo '<td>'.$row->vorname.'</td>';
				echo '<td>'.$row->stg.'</td>';
				echo '</tr>';
			}
			echo '</tbody></table>';
		}
	}
}

echo '</body></html>';
?>