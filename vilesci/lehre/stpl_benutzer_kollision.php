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
/*
 * Fuehrt eine Kollisionspruefung im Stundenplan auf Studentenebene durch
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/basis_db.class.php');			
require_once('../../include/studiensemester.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$beginn = (isset($_GET['beginn'])?$_GET['beginn']:'');
$ende = (isset($_GET['ende'])?$_GET['ende']:'');
$stg_kz = (isset($_GET['stg_kz'])?$_GET['stg_kz']:'');
$dontloadcontent=false;

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('lehre/lvplan'))
	die('Sie haben keine Berechtigung für diese Seite');

loadVariables($user);
if (empty($db_stpl_table))
	die("Bitte die Variablen warten! db_stpl_table ist leer");

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Kollision Student</title>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css" />
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
<script language="Javascript">
function changeStudiensemester(dropdown)
{
	document.getElementById("beginn").value = dropdown.options[dropdown.selectedIndex].getAttribute("beginn");
	document.getElementById("ende").value = dropdown.options[dropdown.selectedIndex].getAttribute("ende");
}
</script>
</head>
<body style="background-color:#eeeeee;">
<h2>Kollision Student - '.$db_stpl_table.'</h2>
';
echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';

if($beginn=='' || $ende=='')
{
	$stsem_obj = new studiensemester();
	$stsem_akt = $stsem_obj->getaktorNext();
	$stsem_obj->load($stsem_akt);
	
	$beginn = $stsem_obj->start;
	$ende = $stsem_obj->ende;
	$dontloadcontent=true;
}

echo 'Studiensemester <SELECT name="studiensemester_kurzbz" onchange="changeStudiensemester(this)">';
echo "<option value='' beginn='' ende=''>-- Auswahl --</option>";
$stsem_obj = new studiensemester();
$stsem_obj->getAll();

foreach($stsem_obj->studiensemester as $stsem)
{
	if(isset($stsem_akt) && $stsem_akt!='' && $stsem_akt==$stsem->studiensemester_kurzbz)
		$selected='selected';
	else 
		$selected='';
	
	echo "<option value='$stsem->studiensemester_kurzbz' beginn='$stsem->start' ende='$stsem->ende' $selected>$stsem->studiensemester_kurzbz</option>";
}

echo '</SELECT>';

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

if($stg_kz=='')
{
	$qry = "SELECT datum, stunde, student_uid, count(student_uid) AS anzahl
			FROM lehre.vw_".$db_stpl_table."_student_unr
			WHERE datum>=".$db->db_add_param($beginn)." AND datum<=".$db->db_add_param($ende)."
			GROUP BY datum, stunde, student_uid
			HAVING count(student_uid)>1
			ORDER BY datum, stunde, student_uid LIMIT 30; 
		   ";
}
else 
{
	$qry = "SELECT datum, stunde, student_uid, count(student_uid) AS anzahl
			FROM lehre.vw_".$db_stpl_table."_student_unr JOIN public.tbl_student USING(student_uid)
			WHERE datum>=".$db->db_add_param($beginn)." AND datum<=".$db->db_add_param($ende)." AND studiengang_kz=".$db->db_add_param($stg_kz)."
			GROUP BY datum, stunde, student_uid
			HAVING count(student_uid)>1
			ORDER BY datum, stunde, student_uid LIMIT 30; 
		   ";
}
//echo $qry;
echo '<table class="liste table-autosort:0 table-stripeclass:alternate table-autostripe">
	<thead>';
echo '<tr class="liste">
		<th class="table-sortable:default">Datum</th>
		<th class="table-sortable:default">Stunde</th>
		<th class="table-sortable:default">UID</th>
		<th class="table-sortable:default">Anzahl</th>
		<th class="table-sortable:default">&nbsp;</th>
		<th class="table-sortable:default">&nbsp;</th>
	  </tr>
	 </thead>
	 <tbody>';
	 
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		echo "<tr>";
		echo "<td class='table-sortable:default' align='center'>$row->datum</td>";
		echo "<td class='table-sortable:default' align='center'>$row->stunde</td>";
		echo "<td class='table-sortable:default' align='center'>$row->student_uid</td>";
		echo "<td class='table-sortable:default' align='center'>$row->anzahl</td>";
		echo "<td class='table-sortable:default' align='center'><a href='stpl_benutzer_kollision_details.php?datum=$row->datum&stunde=$row->stunde' target='kollision_detail'>Stundenplan</a></td>";
		echo "<td class='table-sortable:default' align='center'><a href='stpl_benutzer_kollision_details.php?datum=$row->datum&stunde=$row->stunde&uid=$row->student_uid' target='kollision_detail'>UNR</a></td>";
		echo "</tr>";
	}
}

echo '</tbody></table>';
if($result && $db->db_num_rows($result)>=30)
	echo 'Info: Es werden nur die ersten 30 Eintr&auml;ge angezeigt!';
echo '</body></html';
?>
