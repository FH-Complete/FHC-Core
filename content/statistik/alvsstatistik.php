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
require_once('../../include/benutzerberechtigung.class.php');

$user = get_uid();
loadVariables($user);

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/vilesci'))
	die('Sie haben keine Berechtigung fuer diese Seite');

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
// ALVS pro Fachbereich
$qry = "
SELECT * FROM (
	SELECT
		fachbereich_kurzbz, tbl_lehrveranstaltung.studiengang_kz, geschlecht,sum(tbl_lehreinheitmitarbeiter.semesterstunden) as semesterstunden
	FROM
		lehre.tbl_lehreinheit,
		lehre.tbl_lehrveranstaltung,
		lehre.tbl_lehrveranstaltung as lehrfach,
		public.tbl_fachbereich,
		lehre.tbl_lehreinheitmitarbeiter,
		public.tbl_benutzer,
		public.tbl_person
	WHERE
		tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id AND
		tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stsem)." AND
		tbl_lehreinheit.lehrfach_id = lehrfach.lehrveranstaltung_id AND
		tbl_lehreinheitmitarbeiter.mitarbeiter_uid = tbl_benutzer.uid AND
		tbl_benutzer.person_id = tbl_person.person_id AND
		tbl_lehreinheitmitarbeiter.semesterstunden<>0 AND
		tbl_fachbereich.oe_kurzbz=lehrfach.oe_kurzbz AND
		faktor<>0 AND
		stundensatz<>0 AND
		tbl_lehreinheitmitarbeiter.lehreinheit_id=tbl_lehreinheit.lehreinheit_id
	GROUP BY fachbereich_kurzbz, geschlecht, tbl_lehrveranstaltung.studiengang_kz
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
	$data[$row->studiengang_kz][$row->fachbereich_kurzbz][$row->geschlecht]=$row->semesterstunden;
}

sort($fachbereiche);

//Betreuerstunden
$qry = "
SELECT
	studiengang_kz, geschlecht, sum(stunden) as stunden
FROM
	lehre.tbl_projektarbeit,
	lehre.tbl_lehrveranstaltung,
	lehre.tbl_lehreinheit,
	lehre.tbl_projektbetreuer,
	public.tbl_person
WHERE
	tbl_projektarbeit.projektarbeit_id=tbl_projektbetreuer.projektarbeit_id AND
	tbl_lehreinheit.lehreinheit_id=tbl_projektarbeit.lehreinheit_id AND
	tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND
	tbl_projektbetreuer.person_id=tbl_person.person_id AND
	tbl_projektbetreuer.faktor<>0 AND
	tbl_projektbetreuer.stunden<>0 AND
	tbl_projektbetreuer.stundensatz<>0 AND
	tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stsem)."
GROUP BY studiengang_kz,geschlecht";

if(!$result = $db->db_query($qry))
	die('Fehler bei DB-Abfrage');

while($row = $db->db_fetch_object())
	$data[$row->studiengang_kz]['betreuungen'][$row->geschlecht]=$row->stunden;

if($format=='xls')
{
	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$workbook->send("ALVSStatistik_".$stsem.".xls");
	$workbook->setVersion(8);
	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("ALVSStatistik");
	$worksheet->setInputEncoding("utf-8");
	$worksheet->setRow(0,250.50);
	//Formate Definieren
	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();
	//$format_bold->setBorder(1);

	$format_border =& $workbook->addFormat();
	$format_border->setBorder(1);

	$format_rotate =& $workbook->addFormat();
	$format_rotate->setTextRotation(270);
	$format_rotate->setAlign("center");
	$format_rotate->setBold();
	
	$format_m_w =& $workbook->addFormat();
	$format_m_w->setAlign("center");
	$format_m_w->setBold();
	
	$format_data =& $workbook->addFormat();
	$format_data->setNumFormat("0.00");
	
	$format_bold_data =& $workbook->addFormat();
	$format_bold_data->setBold();
	$format_bold_data->setNumFormat("0.00");
	$format_bold_data->setVAlign("vcenter");
	
	$format_bold_center_data =& $workbook->addFormat();
	$format_bold_center_data->setBold();
	$format_bold_center_data->setNumFormat("0.00");
	$format_bold_center_data->setAlign("center");

	$spalte=0;
	$zeile=0;


	$worksheet->write($zeile,$spalte,$stsem, $format_bold);
	$maxlength[$spalte]=13;
	$summe_fb = array();
	foreach ($fachbereiche as $fb)
	{
		$zeile=0;
		$worksheet->write($zeile,++$spalte,$fb_arr[$fb], $format_rotate);
		$worksheet->mergeCells($zeile,$spalte,0,$spalte+1);
		$fachbereiche[$fb]=$spalte;
		
		$worksheet->write(++$zeile,$spalte,'m',$format_m_w);
		$worksheet->write($zeile,++$spalte,'w',$format_m_w);
		
		//$fachbereiche[$fb]=$spalte;
		$summe_fb[$fb]=array();
		//++$spalte;
		$maxlength[$spalte]=7;
		$maxlength[$spalte-1]=7;
	}
	$zeile=0;
	$worksheet->write($zeile,++$spalte,'Betreuungen', $format_rotate);
	$worksheet->mergeCells($zeile,$spalte,0,$spalte+1);
	
	$worksheet->write(++$zeile,$spalte,'m',$format_m_w);
	$worksheet->write($zeile,++$spalte,'w',$format_m_w);
	
	$maxlength[$spalte]=7;
	$maxlength[$spalte-1]=7;
	
	$fachbereiche['betreuungen']=$spalte-1;
	//$maxlength[$spalte]=3;
	$summe_fb['betreuungen']=array();
	//++$spalte;
	
	$zeile=0;
	$worksheet->write($zeile,++$spalte,'Summe', $format_rotate);
	$worksheet->mergeCells($zeile,$spalte,0,$spalte+2);
	$worksheet->write(++$zeile,$spalte,'m',$format_m_w);
	$worksheet->write($zeile,++$spalte,'w',$format_m_w);
	$worksheet->write($zeile,++$spalte,'Gesamt',$format_m_w);
	
	$maxspalten=$spalte;
	
	/*++$zeile;
	$spalte=0;
	foreach ($fachbereiche as $fb)
	{
		$worksheet->write($zeile,++$spalte,'m');
		$worksheet->write($zeile,++$spalte,'w');
		//$fachbereiche[$fb]=$spalte;
		//++$spalte;
	}*/

	if(isset($data))
	{
		foreach ($data as $key=>$val)
		{
			$zeile++;
			$spalte=0;
			$worksheet->write($zeile,$spalte,$stg_arr[$key], $format_bold);
			$summe_m=0;
			$summe_w=0;
			foreach ($data[$key] as $fb=>$stunden)
			{
				if(!isset($stunden['m']))
					$stunden['m']=0;
				$summe_m+=$stunden['m'];
				if(!isset($summe_fb[$fb]['m']))
					$summe_fb[$fb]['m']=0;
				$summe_fb[$fb]['m']+=$stunden['m'];
				$worksheet->write($zeile,$fachbereiche[$fb],$stunden['m'],$format_data);
				//if($maxlength[$fachbereiche[$fb]]<strlen($stunden['m']))
				//	$maxlength[$fachbereiche[$fb]]=strlen($stunden['m']);
				
				if(!isset($stunden['w']))
					$stunden['w']=0;
				$summe_w+=$stunden['w'];
				if(!isset($summe_fb[$fb]['w']))
					$summe_fb[$fb]['w']=0;
				$summe_fb[$fb]['w']+=$stunden['w'];
				$worksheet->write($zeile,$fachbereiche[$fb]+1,$stunden['w'],$format_data);
				//if($maxlength[$fachbereiche[$fb]]<strlen($stunden['w']))
				//	$maxlength[$fachbereiche[$fb]]=strlen($stunden['w']);
			}
			$worksheet->write($zeile,$maxspalten-2,number_format($summe_m,2,'.',''), $format_bold_data);
			$worksheet->write($zeile,$maxspalten-1,number_format($summe_w,2,'.',''), $format_bold_data);
			$gesamt = $summe_m + $summe_w;
			$worksheet->write($zeile,$maxspalten,number_format($gesamt,2,'.',''), $format_bold_data);
		}
	}


	$zeile++;
	$worksheet->write($zeile,0,'Summe', $format_bold_data);
	$worksheet->mergeCells($zeile,0,$zeile+1,0);
	//foreach ($summe_fb as $fb=>$summe)
	foreach ($summe_fb as $fb=>$summe)
	{
		if(!isset($summe['m']))
			$summe['m']=0;
		if(!isset($summe['w']))
			$summe['w']=0;
		
		if(isset($fachbereiche[$fb]))
			$worksheet->write($zeile,$fachbereiche[$fb],number_format($summe['m'],2,'.',''), $format_bold_center_data);
			$worksheet->write($zeile,$fachbereiche[$fb]+1,number_format($summe['w'],2,'.',''), $format_bold_center_data);
			$gesamt = $summe['m']+$summe['w'];
			$worksheet->write(++$zeile,$fachbereiche[$fb],number_format($gesamt,2,'.',''), $format_bold_center_data);
			$worksheet->mergeCells($zeile,$fachbereiche[$fb],$zeile,$fachbereiche[$fb]+1);
			--$zeile;
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
	th
	{
		padding: 0 10px 0 10px;
	}
	td
	{
		
	}
	</style>
	</head>
	<body class="Background_main">';



	echo "<h2>ALVS $stsem</h2>";

	echo '<table class="liste" style="border: 1px solid black" rules="all" cellspacing="0">';
	echo '<tr class="liste"><th>'.$stsem.'</th>';
	$summe_fb = array();

	foreach ($fachbereiche as $fb)
	{
		echo "<th colspan='2'>".$fb_arr[$fb]."</th>";
		$summe_fb[$fb]=array();
	}
	echo "<th colspan='2'>Betreuungen</th>";
	$summe_fb['betreuungen']=array();
	echo "<th colspan='3'>Summe</th>";
	echo "</tr>";
	// Spalten m/w
	echo '<tr class="liste"><td>&nbsp;</td>';
	foreach ($fachbereiche as $fb)
	{
		echo "<td style='text-align:center; font-weight: bold; width: 50%'>m</td><td style='text-align:center; font-weight: bold;'>w</td>";
	}
	echo "<td style='text-align:center; font-weight: bold; width: 33%'>m</td>";
	echo "<td style='text-align:center; font-weight: bold; width: 33%'>w</td>";
	//$summe_fb['betreuungen']=0;
	echo "<td style='text-align:center; font-weight: bold; width: 33%'>m</td>";
	echo "<td style='text-align:center; font-weight: bold; width: 33%'>w</td>";
	echo "<td style='text-align:center; font-weight: bold'>Gesamt</td>";
	echo "</tr>";

	// Für jeden Fachbereich eine Variable definieren
	foreach ($fachbereiche as $fb)
	{
		$summe_fb[$fb]["m"] = 0;
		$summe_fb[$fb]["w"] = 0;
	}
	$summe_fb['betreuungen']['m'] = 0;
	$summe_fb['betreuungen']['w'] = 0;
	foreach ($data as $key=>$val)
	{
		echo "<tr>";
		echo "<td>".$stg_arr[$key]."</td>";
		$summe_m =0;
		$summe_w =0;
		foreach ($fachbereiche as $fb)
		{
			if(isset($data[$key][$fb]["m"]))
			{
				$summe_m+=$data[$key][$fb]["m"];
				$summe_fb[$fb]["m"]+=$data[$key][$fb]["m"];
				echo "<td>".$data[$key][$fb]["m"]."</td>";
			}
			else
				echo "<td>&nbsp;</td>";
			if(isset($data[$key][$fb]["w"]))
			{
				$summe_w+=$data[$key][$fb]["w"];
				$summe_fb[$fb]["w"]+=$data[$key][$fb]["w"];
				echo "<td>".$data[$key][$fb]["w"]."</td>";
			}
			else
				echo "<td>&nbsp;</td>";
		}
		if(isset($data[$key]['betreuungen']['m']))
		{
			echo "<td>".number_format($data[$key]['betreuungen']['m'],2)."</td>";
			$summe_fb['betreuungen']['m']+=$data[$key]['betreuungen']['m'];
			$summe_m+=$data[$key]['betreuungen']['m'];
		}
		else
			echo "<td>&nbsp;</td>";
		if(isset($data[$key]['betreuungen']['w']))
		{
			echo "<td>".number_format($data[$key]['betreuungen']['w'],2)."</td>";
			$summe_fb['betreuungen']['w']+=$data[$key]['betreuungen']['w'];
			$summe_w+=$data[$key]['betreuungen']['w'];
		}
		else
			echo "<td>&nbsp;</td>";
		echo "</td>";
		echo "<td><b>$summe_m</b></td>";
		echo "<td><b>$summe_w</b></td>";
		echo "<td><b>".($summe_m+$summe_w)."</b></td>";
		echo "</tr>";
	}

	echo "<tr>";
	echo "<td rowspan='2'>Summe</td>";
	foreach ($fachbereiche as $fb)
	{
		if(isset($summe_fb[$fb]["m"]))
			echo "<td style='text-align:center; font-weight: bold'>".$summe_fb[$fb]["m"]."</td>";
		else
			echo "<td>&nbsp;</td>";
		if(isset($summe_fb[$fb]["w"]))
			echo "<td style='text-align:center; font-weight: bold'>".$summe_fb[$fb]["w"]."</td>";
		else
			echo "<td>&nbsp;</td>";
	}
	if(isset($summe_fb['betreuungen']['m']))
		echo "<td style='text-align:center; font-weight: bold'>".number_format($summe_fb['betreuungen']['m'],2)."</td>";
	else
		echo "<td>&nbsp;</td>";
	if(isset($summe_fb['betreuungen']['w']))
		echo "<td style='text-align:center; font-weight: bold'>".number_format($summe_fb['betreuungen']['w'],2)."</td>";
	else
		echo "<td>&nbsp;</td>";
	echo "<td colspan='3'>&nbsp;</td>";
	echo "</tr>";
	
	echo "<tr>";
	//echo "<td></td>";
	foreach ($fachbereiche as $fb)
	{
		if(isset($summe_fb[$fb]["m"]) || isset($summe_fb[$fb]["w"]))
			echo "<td colspan='2' style='text-align:center; font-weight: bold'>".($summe_fb[$fb]["m"] + $summe_fb[$fb]["w"])."</td>";
		else
			echo "<td colspan='2'>&nbsp;</td>";
	}
	if(isset($summe_fb['betreuungen']['m']) || isset($summe_fb['betreuungen']['w']))
		echo "<td colspan='2' style='text-align:center; font-weight: bold'>".number_format(($summe_fb['betreuungen']['m'] + $summe_fb['betreuungen']['w']),2)."</td>";
	else
		echo "<td colspan='2'>&nbsp;</td>";
	echo "<td colspan='3'>&nbsp;</td>";
	echo "</tr>";
	
	echo '</table>';
	echo '</body>
	</html>';
}
?>
