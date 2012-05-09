<?php
/* Copyright (C) 2007 Technikum-Wien
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
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/Excel/excel.php');

$user = get_uid();
loadVariables($user);
$db = new basis_db();
$stsem = $semester_aktuell;

$format = (isset($_GET['format'])?$_GET['format']:'');

$studiengang = new studiengang();
$studiengang->getAll('typ, kurzbz', false);

$stg_arr = array();
foreach ($studiengang->result as $row)
	$stg_arr[$row->studiengang_kz] = $row->kuerzel.' ('.$row->kurzbzlang.')';

$qry = "
	SELECT stdlvb.studiengang_kz,
		count(*) AS all,
		(SELECT count(*) FROM public.tbl_studentlehrverband JOIN campus.vw_student ON (student_uid=uid) WHERE tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."' AND tbl_studentlehrverband.semester=1 AND tbl_studentlehrverband.studiengang_kz=stdlvb.studiengang_kz AND geschlecht='m') AS s1_m,
		(SELECT count(*) FROM public.tbl_studentlehrverband JOIN campus.vw_student ON (student_uid=uid) WHERE tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."' AND tbl_studentlehrverband.semester=1 AND tbl_studentlehrverband.studiengang_kz=stdlvb.studiengang_kz AND geschlecht='w') AS s1_w,
		(SELECT count(*) FROM public.tbl_studentlehrverband JOIN campus.vw_student ON (student_uid=uid) WHERE tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."' AND tbl_studentlehrverband.semester=2 AND tbl_studentlehrverband.studiengang_kz=stdlvb.studiengang_kz AND geschlecht='m') AS s2_m,
		(SELECT count(*) FROM public.tbl_studentlehrverband JOIN campus.vw_student ON (student_uid=uid) WHERE tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."' AND tbl_studentlehrverband.semester=2 AND tbl_studentlehrverband.studiengang_kz=stdlvb.studiengang_kz AND geschlecht='w') AS s2_w,
		(SELECT count(*) FROM public.tbl_studentlehrverband JOIN campus.vw_student ON (student_uid=uid) WHERE tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."' AND tbl_studentlehrverband.semester=3 AND tbl_studentlehrverband.studiengang_kz=stdlvb.studiengang_kz AND geschlecht='m') AS s3_m,
		(SELECT count(*) FROM public.tbl_studentlehrverband JOIN campus.vw_student ON (student_uid=uid) WHERE tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."' AND tbl_studentlehrverband.semester=3 AND tbl_studentlehrverband.studiengang_kz=stdlvb.studiengang_kz AND geschlecht='w') AS s3_w,
		(SELECT count(*) FROM public.tbl_studentlehrverband JOIN campus.vw_student ON (student_uid=uid) WHERE tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."' AND tbl_studentlehrverband.semester=4 AND tbl_studentlehrverband.studiengang_kz=stdlvb.studiengang_kz AND geschlecht='m') AS s4_m,
		(SELECT count(*) FROM public.tbl_studentlehrverband JOIN campus.vw_student ON (student_uid=uid) WHERE tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."' AND tbl_studentlehrverband.semester=4 AND tbl_studentlehrverband.studiengang_kz=stdlvb.studiengang_kz AND geschlecht='w') AS s4_w,
		(SELECT count(*) FROM public.tbl_studentlehrverband JOIN campus.vw_student ON (student_uid=uid) WHERE tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."' AND tbl_studentlehrverband.semester=5 AND tbl_studentlehrverband.studiengang_kz=stdlvb.studiengang_kz AND geschlecht='m') AS s5_m,
		(SELECT count(*) FROM public.tbl_studentlehrverband JOIN campus.vw_student ON (student_uid=uid) WHERE tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."' AND tbl_studentlehrverband.semester=5 AND tbl_studentlehrverband.studiengang_kz=stdlvb.studiengang_kz AND geschlecht='w') AS s5_w,
		(SELECT count(*) FROM public.tbl_studentlehrverband JOIN campus.vw_student ON (student_uid=uid) WHERE tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."' AND tbl_studentlehrverband.semester=6 AND tbl_studentlehrverband.studiengang_kz=stdlvb.studiengang_kz AND geschlecht='m') AS s6_m,
		(SELECT count(*) FROM public.tbl_studentlehrverband JOIN campus.vw_student ON (student_uid=uid) WHERE tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."' AND tbl_studentlehrverband.semester=6 AND tbl_studentlehrverband.studiengang_kz=stdlvb.studiengang_kz AND geschlecht='w') AS s6_w,
		(SELECT count(*) FROM public.tbl_studentlehrverband JOIN campus.vw_student ON (student_uid=uid) WHERE tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."' AND tbl_studentlehrverband.semester=7 AND tbl_studentlehrverband.studiengang_kz=stdlvb.studiengang_kz AND geschlecht='m') AS s7_m,
		(SELECT count(*) FROM public.tbl_studentlehrverband JOIN campus.vw_student ON (student_uid=uid) WHERE tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."' AND tbl_studentlehrverband.semester=7 AND tbl_studentlehrverband.studiengang_kz=stdlvb.studiengang_kz AND geschlecht='w') AS s7_w,
		(SELECT count(*) FROM public.tbl_studentlehrverband JOIN campus.vw_student ON (student_uid=uid) WHERE tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."' AND tbl_studentlehrverband.semester=8 AND tbl_studentlehrverband.studiengang_kz=stdlvb.studiengang_kz AND geschlecht='m') AS s8_m,
		(SELECT count(*) FROM public.tbl_studentlehrverband JOIN campus.vw_student ON (student_uid=uid) WHERE tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."' AND tbl_studentlehrverband.semester=8 AND tbl_studentlehrverband.studiengang_kz=stdlvb.studiengang_kz AND geschlecht='w') AS s8_w
	FROM
		tbl_studentlehrverband stdlvb JOIN tbl_studiengang USING(studiengang_kz) 
	WHERE
		studiensemester_kurzbz='".addslashes($stsem)."' AND semester>0 AND semester<9 AND aktiv 
		
	GROUP BY typ, kurzbz, studiengang_kz
	ORDER BY typ, kurzbz, studiengang_kz
";

if(!$result = $db->db_query($qry))
	die('Fehler bei Datenbankabfrage');

if($format=='xls')
{
	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$workbook->send("StudierendeSemester_".$stsem.".xls");

	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("StudierendeSemester");
	$worksheet->setInputEncoding('utf-8');

	//Formate Definieren
	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();
	$format_bold->setBorder(1);
	$format_bold->setAlign("center");

	$format_border =& $workbook->addFormat();
	$format_border->setBorder(1);
	$format_border->setAlign("center");
	
	$format_left =& $workbook->addFormat();
	$format_left->setBorder(1);
	$format_left->setAlign("left");

	//Überschriften 1. Zeile
	$worksheet->write(0,0,$stsem, $format_bold);
	$worksheet->write(0,1,'1', $format_bold);
	$worksheet->mergeCells(0,1,0,2);
	$worksheet->write(0,3,'2', $format_bold);
	$worksheet->mergeCells(0,3,0,4);
	$worksheet->write(0,5,'3', $format_bold);
	$worksheet->mergeCells(0,5,0,6);
	$worksheet->write(0,7,'4', $format_bold);
	$worksheet->mergeCells(0,7,0,8);
	$worksheet->write(0,9,'5', $format_bold);
	$worksheet->mergeCells(0,9,0,10);
	$worksheet->write(0,11,'6', $format_bold);
	$worksheet->mergeCells(0,11,0,12);
	$worksheet->write(0,13,'7', $format_bold);
	$worksheet->mergeCells(0,13,0,14);
	$worksheet->write(0,15,'8', $format_bold);
	$worksheet->mergeCells(0,15,0,16);
	$worksheet->write(0,17,'Gesamt*', $format_bold);
	
	//Überschriften 2. Zeile
	$spalte=0;
	$zeile=0;
	$worksheet->write($zeile+1,$spalte,'', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'m', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'w', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'m', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'w', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'m', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'w', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'m', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'w', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'m', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'w', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'m', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'w', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'m', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'w', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'m', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'w', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'', $format_bold);

	//Tabellendaten
	while($row = $db->db_fetch_object($result))
	{
		$zeile++;
		$spalte=0;
		$worksheet->setColumn($spalte,$spalte,15);
		$worksheet->write($zeile+1,$spalte,$stg_arr[$row->studiengang_kz], $format_left);
		$worksheet->write($zeile+1,++$spalte,($row->s1_m!=0?$row->s1_m:''), $format_border);
		$worksheet->write($zeile+1,++$spalte,($row->s1_w!=0?$row->s1_w:''), $format_border);
		$worksheet->write($zeile+1,++$spalte,($row->s2_m!=0?$row->s2_m:''), $format_border);
		$worksheet->write($zeile+1,++$spalte,($row->s2_w!=0?$row->s2_w:''), $format_border);
		$worksheet->write($zeile+1,++$spalte,($row->s3_m!=0?$row->s3_m:''), $format_border);
		$worksheet->write($zeile+1,++$spalte,($row->s3_w!=0?$row->s3_w:''), $format_border);
		$worksheet->write($zeile+1,++$spalte,($row->s4_m!=0?$row->s4_m:''), $format_border);
		$worksheet->write($zeile+1,++$spalte,($row->s4_w!=0?$row->s4_w:''), $format_border);
		$worksheet->write($zeile+1,++$spalte,($row->s5_m!=0?$row->s5_m:''), $format_border);
		$worksheet->write($zeile+1,++$spalte,($row->s5_w!=0?$row->s5_w:''), $format_border);
		$worksheet->write($zeile+1,++$spalte,($row->s6_m!=0?$row->s6_m:''), $format_border);
		$worksheet->write($zeile+1,++$spalte,($row->s6_w!=0?$row->s6_w:''), $format_border);
		$worksheet->write($zeile+1,++$spalte,($row->s7_m!=0?$row->s7_m:''), $format_border);
		$worksheet->write($zeile+1,++$spalte,($row->s7_w!=0?$row->s7_w:''), $format_border);
		$worksheet->write($zeile+1,++$spalte,($row->s8_m!=0?$row->s8_m:''), $format_border);
		$worksheet->write($zeile+1,++$spalte,($row->s8_w!=0?$row->s8_w:''), $format_border);
		$worksheet->write($zeile+1,++$spalte,$row->all, $format_border);
	}
	$spalte=0;
	$zeile++;
	$worksheet->write($zeile+1,$spalte,'* Die Summe addiert nur die Semester 1-8. Falls sich aktiv Studierende im 9. oder 10. Semester befinden, weicht die Summe von den tatsaechlichen Werten ab');
	$workbook->close();
}
else
{
	echo '
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
	<title>Studierende/Semester</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<style type="text/css">
	td, th
	{
		border: 1px solid black;
	}
	</style>
	</head>
	<body class="Background_main">';
	echo "<h2>Studierende / Semester</h2>";
	echo '<table class="liste" style="border: 1px solid black" cellspacing="0">
			<tr class="liste">
				<th>'.$stsem.'</th>
				<th colspan="2">1</th>
				<th colspan="2">2</th>
				<th colspan="2">3</th>
				<th colspan="2">4</th>
				<th colspan="2">5</th>
				<th colspan="2">6</th>
				<th colspan="2">7</th>
				<th colspan="2">8</th>
				<th>Gesamt*</th>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<th>m</th><th>w</th>
				<th>m</th><th>w</th>
				<th>m</th><th>w</th>
				<th>m</th><th>w</th>
				<th>m</th><th>w</th>
				<th>m</th><th>w</th>
				<th>m</th><th>w</th>
				<th>m</th><th>w</th>
				<th>&nbsp;</th>';

	while($row = $db->db_fetch_object($result))
	{
		echo "<tr>";
		echo "<td align='left'>".$stg_arr[$row->studiengang_kz]."</td>";
		echo "<td align='center'>".($row->s1_m!=0?$row->s1_m:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s1_w!=0?$row->s1_w:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s2_m!=0?$row->s2_m:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s2_w!=0?$row->s2_w:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s3_m!=0?$row->s3_m:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s3_w!=0?$row->s3_w:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s4_m!=0?$row->s4_m:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s4_w!=0?$row->s4_w:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s5_m!=0?$row->s5_m:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s5_w!=0?$row->s5_w:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s6_m!=0?$row->s6_m:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s6_w!=0?$row->s6_w:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s7_m!=0?$row->s7_m:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s7_w!=0?$row->s7_w:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s8_m!=0?$row->s8_m:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s8_w!=0?$row->s8_w:'&nbsp;')."</td>";
		echo "<td align='center'>".$row->all."</td>";
		echo "</tr>";
	}

	echo '</table>';
	echo '<br/>* Die Summe addiert nur die Semester 1-8. Falls sich aktiv Studierende im 9. oder 10. Semester befinden, weicht die Summe von den tatsächlichen Werten ab.';
	echo '</body>
	</html>';
}
?>
