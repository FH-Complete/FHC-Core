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
require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/Excel/excel.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Connecten zur Datenbank');

$user = get_uid();
loadVariables($conn, $user);

$stsem = $semester_aktuell;

$typ = (isset($_GET['typ'])?$_GET['typ']:'');

$studiengang = new studiengang($conn);
$studiengang->getAll('typ, kurzbz', false);

$stg_arr = array();
foreach ($studiengang->result as $row)
	$stg_arr[$row->studiengang_kz] = $row->kuerzel.' ('.$row->kurzbzlang.')';

$qry = "
	SELECT 
		stdlvb.studiengang_kz, 
		count(*) AS all,
		(SELECT count(*) FROM tbl_studentlehrverband WHERE studiensemester_kurzbz='$stsem' AND semester=1 AND studiengang_kz=stdlvb.studiengang_kz ) AS s1, 
		(SELECT count(*) FROM tbl_studentlehrverband WHERE studiensemester_kurzbz='$stsem' AND semester=2 AND studiengang_kz=stdlvb.studiengang_kz ) AS s2, 
		(SELECT count(*) FROM tbl_studentlehrverband WHERE studiensemester_kurzbz='$stsem' AND semester=3 AND studiengang_kz=stdlvb.studiengang_kz ) AS s3,
		(SELECT count(*) FROM tbl_studentlehrverband WHERE studiensemester_kurzbz='$stsem' AND semester=4 AND studiengang_kz=stdlvb.studiengang_kz ) AS s4,
		(SELECT count(*) FROM tbl_studentlehrverband WHERE studiensemester_kurzbz='$stsem' AND semester=5 AND studiengang_kz=stdlvb.studiengang_kz ) AS s5,
		(SELECT count(*) FROM tbl_studentlehrverband WHERE studiensemester_kurzbz='$stsem' AND semester=6 AND studiengang_kz=stdlvb.studiengang_kz ) AS s6,
		(SELECT count(*) FROM tbl_studentlehrverband WHERE studiensemester_kurzbz='$stsem' AND semester=7 AND studiengang_kz=stdlvb.studiengang_kz ) AS s7,
		(SELECT count(*) FROM tbl_studentlehrverband WHERE studiensemester_kurzbz='$stsem' AND semester=8 AND studiengang_kz=stdlvb.studiengang_kz ) AS s8
	FROM 
		tbl_studentlehrverband stdlvb JOIN tbl_studiengang USING(studiengang_kz)
	WHERE 
		studiensemester_kurzbz='$stsem' AND semester>0 AND semester<9
	GROUP BY typ, kurzbz, studiengang_kz 
";
if(!$result = pg_query($conn, $qry))
	die('Fehler bei Datenbankabfrage');

if($typ=='xls')
{
	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();
	
	// sending HTTP headers
	$workbook->send("StudentenSemester_".$stsem.".xls");
	
	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("StudentenSemester");
	
	//Formate Definieren
	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();
	$format_bold->setBorder(1);
	
	$format_border =& $workbook->addFormat();
	$format_border->setBorder(1);
		
	$spalte=0;
	$zeile=0;
	
	$worksheet->write($zeile,$spalte,$stsem, $format_bold);
	$worksheet->write($zeile,++$spalte,'Gesamt', $format_bold);
	$worksheet->write($zeile,++$spalte,'1', $format_bold);
	$worksheet->write($zeile,++$spalte,'2', $format_bold);
	$worksheet->write($zeile,++$spalte,'3', $format_bold);
	$worksheet->write($zeile,++$spalte,'4', $format_bold);
	$worksheet->write($zeile,++$spalte,'5', $format_bold);
	$worksheet->write($zeile,++$spalte,'6', $format_bold);
	$worksheet->write($zeile,++$spalte,'7', $format_bold);
	$worksheet->write($zeile,++$spalte,'8', $format_bold);
	
	while($row = pg_fetch_object($result))
	{
		$zeile++;
		$spalte=0;
		$worksheet->write($zeile,$spalte,$stg_arr[$row->studiengang_kz], $format_bold);
		$worksheet->write($zeile,++$spalte,$row->all, $format_border);
		$worksheet->write($zeile,++$spalte,($row->s1!=0?$row->s1:''), $format_border);
		$worksheet->write($zeile,++$spalte,($row->s2!=0?$row->s2:''), $format_border);
		$worksheet->write($zeile,++$spalte,($row->s3!=0?$row->s3:''), $format_border);
		$worksheet->write($zeile,++$spalte,($row->s4!=0?$row->s4:''), $format_border);
		$worksheet->write($zeile,++$spalte,($row->s5!=0?$row->s5:''), $format_border);
		$worksheet->write($zeile,++$spalte,($row->s6!=0?$row->s6:''), $format_border);
		$worksheet->write($zeile,++$spalte,($row->s7!=0?$row->s7:''), $format_border);
		$worksheet->write($zeile,++$spalte,($row->s8!=0?$row->s8:''), $format_border);
	}
		
	$workbook->close();
}
else 
{
	echo '
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
	<title>Studenten/Semester</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<style type="text/css">
	td, th
	{
		border: 1px solid black;
	}
	</style>
	</head>
	<body class="Background_main">';
	
	
	
	echo "<h2>Studenten / Semester</h2>";
	
	echo '<table class="liste" style="border: 1px solid black" cellspacing="0"><tr class="liste"><th>'.$stsem.'</th><th>Gesamt</th><th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th></tr>';

	while($row = pg_fetch_object($result))
	{
		echo "<tr>";
		echo "<td align='left'>".$stg_arr[$row->studiengang_kz]."</td>";
		echo "<td align='center'>".$row->all."</td>";
		echo "<td align='center'>".($row->s1!=0?$row->s1:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s2!=0?$row->s2:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s3!=0?$row->s3:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s4!=0?$row->s4:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s5!=0?$row->s5:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s6!=0?$row->s6:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s7!=0?$row->s7:'&nbsp;')."</td>";
		echo "<td align='center'>".($row->s8!=0?$row->s8:'&nbsp;')."</td>";
		echo "</tr>";
	}
		
	echo '</table>';
	echo '</body>
	</html>';
}
?>
