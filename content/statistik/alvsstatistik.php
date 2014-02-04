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
require_once('../../include/fachbereich.class.php');
require_once('../../include/Excel/excel.php');

$user = get_uid();
loadVariables($user);

$stsem = (isset($_GET['stsem'])?$_GET['stsem']:$semester_aktuell);
$format = (isset($_GET['format'])?$_GET['format']:'');

$studiengang = new studiengang();
$studiengang->getAll('typ, kurzbz', false);

$stg_arr = array();
foreach ($studiengang->result as $row)
	$stg_arr[$row->studiengang_kz] = $row->kuerzel.' ('.$row->kurzbzlang.')';

$fachbereich = new fachbereich();
$fachbereich->getAll();

$fb_arr = array();
foreach ($fachbereich->result as $row)
	$fb_arr[$row->fachbereich_kurzbz]=$row->bezeichnung;

$db = new basis_db();

$qry = "
SELECT * FROM (
	SELECT
		fachbereich_kurzbz, tbl_lehrveranstaltung.studiengang_kz, sum(tbl_lehreinheitmitarbeiter.semesterstunden) as semesterstunden
	FROM
		lehre.tbl_lehreinheit,
		lehre.tbl_lehrveranstaltung,
		lehre.tbl_lehrveranstaltung as lehrfach,
		public.tbl_fachbereich,
		lehre.tbl_lehreinheitmitarbeiter
	WHERE
		tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id AND
		tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stsem)." AND
		tbl_lehreinheit.lehrfach_id = lehrfach.lehrveranstaltung_id AND
		tbl_lehreinheitmitarbeiter.semesterstunden<>0 AND
		tbl_fachbereich.oe_kurzbz=lehrfach.oe_kurzbz AND
		faktor<>0 AND
		stundensatz<>0 AND
		tbl_lehreinheitmitarbeiter.lehreinheit_id=tbl_lehreinheit.lehreinheit_id
	GROUP BY fachbereich_kurzbz, tbl_lehrveranstaltung.studiengang_kz
	) as a JOIN public.tbl_studiengang USING(studiengang_kz)
ORDER BY typ, tbl_studiengang.kurzbz, fachbereich_kurzbz
";

if(!$db->db_query($qry))
	die('Fehler bei Datenbankabfrage');

$fachbereiche = array();

while($row = $db->db_fetch_object())
{
	if(!in_array($row->fachbereich_kurzbz, $fachbereiche))
		$fachbereiche[] = $row->fachbereich_kurzbz;
	$data[$row->studiengang_kz][$row->fachbereich_kurzbz]=$row->semesterstunden;
}

sort($fachbereiche);

$qry = "
SELECT
	studiengang_kz, sum(stunden) as stunden
FROM
	lehre.tbl_projektarbeit,
	lehre.tbl_lehrveranstaltung,
	lehre.tbl_lehreinheit,
	lehre.tbl_projektbetreuer
WHERE
	tbl_projektarbeit.projektarbeit_id=tbl_projektbetreuer.projektarbeit_id AND
	tbl_lehreinheit.lehreinheit_id=tbl_projektarbeit.lehreinheit_id AND
	tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND
	tbl_projektbetreuer.faktor<>0 AND
	tbl_projektbetreuer.stunden<>0 AND
	tbl_projektbetreuer.stundensatz<>0 AND
	tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stsem)."
GROUP BY studiengang_kz";

if(!$result = $db->db_query($qry))
	die('Fehler bei DB-Abfrage');

while($row = $db->db_fetch_object())
	$data[$row->studiengang_kz]['betreuungen']=$row->stunden;

if($format=='xls')
{
	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$workbook->send("ALVSStatistik_".$stsem.".xls");
	$workbook->setVersion(8);
	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("ALVSStatistik");
	$worksheet->setInputEncoding('utf-8');
	//Formate Definieren
	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();
	//$format_bold->setBorder(1);

	$format_border =& $workbook->addFormat();
	$format_border->setBorder(1);

	$format_rotate =& $workbook->addFormat();
	$format_rotate->setTextRotation(270);
	$format_rotate->setAlign('center');
	$format_rotate->setBold();

	$spalte=0;
	$zeile=0;


	$worksheet->write($zeile,$spalte,$stsem, $format_bold);
	$maxlength[$spalte]=13;
	$summe_fb = array();
	foreach ($fachbereiche as $fb)
	{
		$worksheet->write($zeile,++$spalte,$fb_arr[$fb], $format_rotate);
		$fachbereiche[$fb]=$spalte;
		$maxlength[$spalte]=3;
		$summe_fb[$fb]=0;
	}
	$worksheet->write($zeile,++$spalte,'Betreuerstunden', $format_rotate);
	$fachbereiche['betreuungen']=$spalte;
	$maxlength[$spalte]=3;
	$summe_fb['betreuungen']=0;

	$worksheet->write($zeile,++$spalte,'Summe', $format_rotate);
	$maxspalten=$spalte;


	if(isset($data))
	{
		foreach ($data as $key=>$val)
		{
			$zeile++;
			$spalte=0;
			$worksheet->write($zeile,$spalte,$stg_arr[$key], $format_bold);
			$summe=0;
			foreach ($data[$key] as $fb=>$stunden)
			{
				$summe+=$stunden;
				$summe_fb[$fb]+=$stunden;
				$worksheet->write($zeile,$fachbereiche[$fb],$stunden);
				if($maxlength[$fachbereiche[$fb]]<strlen($stunden))
					$maxlength[$fachbereiche[$fb]]=strlen($stunden);
			}
			$worksheet->write($zeile,$maxspalten,$summe, $format_bold);
		}
	}


	$zeile++;
	$worksheet->write($zeile,0,'Summe', $format_bold);
	foreach ($summe_fb as $fb=>$summe)
	{
		if(isset($fachbereiche[$fb]))
			$worksheet->write($zeile,$fachbereiche[$fb],$summe, $format_bold);
	}

	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet->setColumn($i, $i, $breite);

	$workbook->close();
}
else
{
	echo '
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
	<title>Studenten/Semester</title>
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



	echo "<h2>ALVS $stsem</h2>";

	echo '<table class="liste" style="border: 1px solid black" cellspacing="0"><tr class="liste"><th>'.$stsem.'</th>';
	$summe_fb = array();

	foreach ($fachbereiche as $fb)
	{
		echo "<th>".$fb_arr[$fb]."</th>";
		$summe_fb[$fb]=0;
	}
	echo "<th>Betreuungen</th>";
	$summe_fb['betreuungen']=0;
	echo "<th>Summe</th>";
	echo "</tr>";

	foreach ($data as $key=>$val)
	{
		echo "<tr>";
		echo "<td>".$stg_arr[$key]."</td>";
		$summe =0;
		foreach ($fachbereiche as $fb)
		{
			echo "<td>";
			if(isset($data[$key][$fb]))
			{
				$summe+=$data[$key][$fb];
				$summe_fb[$fb]+=$data[$key][$fb];
				echo $data[$key][$fb];
			}
			else
				echo "&nbsp;";
			echo "</td>";
		}

		echo "<td>";
		if(isset($data[$key]['betreuungen']))
		{
			echo number_format($data[$key]['betreuungen'],2);
			$summe_fb['betreuungen']+=$data[$key]['betreuungen'];
		}
		else
			echo "&nbsp;";
		echo "</td>";
		echo "<td><b>$summe</b></td>";
		echo "</tr>";
	}

	echo "<tr>";
	echo "<td>Summe</td>";
	foreach ($fachbereiche as $fb)
	{
		echo "<td><b>";
		if(isset($summe_fb[$fb]))
			echo $summe_fb[$fb];
		else
			echo "&nbsp;";
		echo "</b></td>";
	}
	echo "<td><b>";
		if(isset($summe_fb['betreuungen']))
			echo number_format($summe_fb['betreuungen'],2);
		else
			echo "&nbsp;";
		echo "</b></td>";
	echo "</tr>";
	echo '</table>';
	echo '</body>
	</html>';
}
?>
