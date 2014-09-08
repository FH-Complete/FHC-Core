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
require_once('../../include/benutzerberechtigung.class.php');

$user = get_uid();
loadVariables($user);
$db = new basis_db();
$stsem = $semester_aktuell;

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/vilesci'))
	die('Sie haben keine Berechtigung fuer diese Seite');

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
	$format_bold->setVAlign("vcenter");

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
	$worksheet->mergeCells(0,17,0,19);
	$worksheet->write(0,19,'', $format_bold);
	
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
	$worksheet->write($zeile+1,++$spalte,'m', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'w', $format_bold);
	$worksheet->write($zeile+1,++$spalte,'', $format_bold);

	//Tabellendaten
	$summe_m_[] = array();
	$summe_m_[0] = 0;
	$summe_w_[] = array();
	$summe_w_[0] = 0;
	while($row = $db->db_fetch_object($result))
	{
		$zeile++;
		$spalte=0;
		$summe_m = 0;
		$summe_w = 0;
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
		$summe_m+= $row->s1_m;
		$summe_m+= $row->s2_m;
		$summe_m+= $row->s3_m;
		$summe_m+= $row->s4_m;
		$summe_m+= $row->s5_m;
		$summe_m+= $row->s6_m;
		$summe_m+= $row->s7_m;
		$summe_m+= $row->s8_m;
		$summe_w+= $row->s1_w;
		$summe_w+= $row->s2_w;
		$summe_w+= $row->s3_w;
		$summe_w+= $row->s4_w;
		$summe_w+= $row->s5_w;
		$summe_w+= $row->s6_w;
		$summe_w+= $row->s7_w;
		$summe_w+= $row->s8_w;
		$worksheet->write($zeile+1,++$spalte,$summe_m, $format_border);
		$worksheet->write($zeile+1,++$spalte,$summe_w, $format_border);
		$worksheet->write($zeile+1,++$spalte,$row->all, $format_border);
		//Pro Semester und Geschlecht eine Variable mit den Summen befüllen
		for ($i=1;$i<9;$i++)
		{
			$var_m = 's'.$i.'_m';
			$var_w = 's'.$i.'_w';
			if(!isset($summe_m_[$i]))
				$summe_m_[$i]=0;
			if(!isset($summe_w_[$i]))
				$summe_w_[$i]=0;
			
			$summe_m_[$i]+= $row->$var_m;
			$summe_w_[$i]+= $row->$var_w;
		}
		if(!isset($gesamtsumme))
			$gesamtsumme=0;
		if(!isset($gesamtsumme_m))
			$gesamtsumme_m=0;
		if(!isset($gesamtsumme_w))
			$gesamtsumme_w=0;
		
		$gesamtsumme+= $row->all;
		$gesamtsumme_m+= $summe_m;
		$gesamtsumme_w+= $summe_w;
	}
	$spalte=0;
	$zeile++;
	$zeile++;
	//Summenzeile
	$worksheet->write($zeile,$spalte,'Summe', $format_bold);
	$worksheet->mergeCells($zeile,$spalte,$zeile+1,$spalte);
	$worksheet->write($zeile+1,$spalte,'', $format_bold);
	$spalte++;
	//Für jede Semestersummenvariable eine Spalte ausgeben
	for ($i=1;$i<9;$i++)
	{
		$worksheet->write($zeile,$spalte++,$summe_m_[$i], $format_bold);
		$worksheet->write($zeile,$spalte++,$summe_w_[$i], $format_bold);
	}
	$worksheet->write($zeile,$spalte++,$gesamtsumme_m, $format_bold);
	$worksheet->write($zeile,$spalte++,$gesamtsumme_w, $format_bold);
	$worksheet->write($zeile,$spalte++,$gesamtsumme, $format_bold);
	//echo "<td align='center'><b>".$gesamtsumme_m."</b></td>";
	//echo "<td align='center'><b>".$gesamtsumme_w."</b></td>";
	//echo "<td rowspan='2' valign='middle' align='center'><b>".$gesamtsumme."</b></td>";
	$zeile++;
	$spalte=0;
	for ($i=1;$i<9;$i++)
	{
		$worksheet->write($zeile,++$spalte,($summe_m_[$i]+$summe_w_[$i]), $format_bold);
		$worksheet->mergeCells($zeile,$spalte,$zeile,$spalte+1);
		$worksheet->write($zeile,$spalte+1,'', $format_bold);
		$spalte++;
	}
	$zeile++;
	$spalte=0;
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
	echo '<table class="liste" style="border: 1px solid black" rules="all" cellspacing="0">
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
				<th colspan="3">Gesamt*</th>
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
				<th>m</th>
				<th>w</th>
				<th>&nbsp;</th>';

	//Für die Berechnung der Spaltensummen
	$summe_m_[] = array();
	$summe_w_[] = array();
		//Pro Semester und Geschlecht eine Variable mit den Summen definieren
		for ($i=1;$i<9;$i++)
		{
			$summe_m_[$i]= 0;
			$summe_w_[$i]= 0;
		}
	$gesamtsumme = 0;
	$gesamtsumme_m = 0;
	$gesamtsumme_w = 0;
	while($row = $db->db_fetch_object($result))
	{
		$summe_m = 0;
		$summe_w = 0;
		echo "<tr>";
		echo "<td align='left'>".$stg_arr[$row->studiengang_kz]."</td>";
		echo "<td align='center'>".($row->s1_m!=0?$row->s1_m:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s1_w!=0?$row->s1_w:'&nbsp;')."</td>";
		$summe_m+= $row->s1_m;
		$summe_w+= $row->s1_w;
		echo "<td align='center'>".($row->s2_m!=0?$row->s2_m:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s2_w!=0?$row->s2_w:'&nbsp;')."</td>";
		$summe_m+= $row->s2_m;
		$summe_w+= $row->s2_w;
		echo "<td align='center'>".($row->s3_m!=0?$row->s3_m:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s3_w!=0?$row->s3_w:'&nbsp;')."</td>";
		$summe_m+= $row->s3_m;
		$summe_w+= $row->s3_w;
		echo "<td align='center'>".($row->s4_m!=0?$row->s4_m:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s4_w!=0?$row->s4_w:'&nbsp;')."</td>";
		$summe_m+= $row->s4_m;
		$summe_w+= $row->s4_w;
		echo "<td align='center'>".($row->s5_m!=0?$row->s5_m:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s5_w!=0?$row->s5_w:'&nbsp;')."</td>";
		$summe_m+= $row->s5_m;
		$summe_w+= $row->s5_w;
		echo "<td align='center'>".($row->s6_m!=0?$row->s6_m:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s6_w!=0?$row->s6_w:'&nbsp;')."</td>";
		$summe_m+= $row->s6_m;
		$summe_w+= $row->s6_w;
		echo "<td align='center'>".($row->s7_m!=0?$row->s7_m:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s7_w!=0?$row->s7_w:'&nbsp;')."</td>";
		$summe_m+= $row->s7_m;
		$summe_w+= $row->s7_w;
		echo "<td align='center'>".($row->s8_m!=0?$row->s8_m:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s8_w!=0?$row->s8_w:'&nbsp;')."</td>";
		$summe_m+= $row->s8_m;
		$summe_w+= $row->s8_w;
		echo "<td align='center'>".$summe_m."</td>";
		echo "<td align='center'>".$summe_w."</td>";
		echo "<td align='center'>".$row->all."</td>";
		echo "</tr>";
		//Pro Semester und Geschlecht eine Variable mit den Summen befüllen
		for ($i=1;$i<9;$i++)
		{
			$var_m = 's'.$i.'_m';
			$var_w = 's'.$i.'_w';
			$summe_m_[$i]+= $row->$var_m;
			$summe_w_[$i]+= $row->$var_w;
		}
		$gesamtsumme+= $row->all;
		$gesamtsumme_m+= $summe_m;
		$gesamtsumme_w+= $summe_w;
	}
	echo "<tr>";
	echo "<td rowspan='2'>Summen</td>";
	//Für jede Semestersummenvariable eine Spalte ausgeben
	for ($i=1;$i<9;$i++)
	{
		echo "<td align='center'>".$summe_m_[$i]."</td>";
		echo "<td align='center'>".$summe_w_[$i]."</td>";
	}
	echo "<td align='center'><b>".$gesamtsumme_m."</b></td>";
	echo "<td align='center'><b>".$gesamtsumme_w."</b></td>";
	echo "<td rowspan='2' valign='middle' align='center'><b>".$gesamtsumme."</b></td>";
	echo "</tr>";
	echo "<tr>";
	for ($i=1;$i<9;$i++)
	{
		echo "<td colspan='2' align='center'>".($summe_m_[$i]+$summe_w_[$i])."</td>";
	}
	echo "<td align='center'></td>";
	echo "<td align='center'></td>";
	echo "</tr>";

	echo '</table>';
	echo '<br/>* Die Summe addiert nur die Semester 1-8. Falls sich aktiv Studierende im 9. oder 10. Semester befinden, weicht die Summe von den tatsächlichen Werten ab.';
	echo '</body>
	</html>';
}
?>
