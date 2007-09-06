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
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/note.class.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/Excel/excel.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Connecten zur Datenbank');

$user = get_uid();
loadVariables($conn, $user);

if(!isset($_GET['studiengang_kz']))
	die('Falsche Parameteruebergabe');
else 
	$studiengang_kz = $_GET['studiengang_kz'];

$semester = isset($_GET['semester'])?$_GET['semester']:'';
$typ = isset($_GET['typ'])?$_GET['typ']:'';

$stg = new studiengang($conn);
$stg->load($studiengang_kz);

$student = new student($conn);
$result_student = $student->getStudents($studiengang_kz,$semester,null,null,null, $semester_aktuell);

$lehrveranstaltung = new lehrveranstaltung($conn);
$lehrveranstaltung->load_lva($studiengang_kz, $semester, null, null, true);

$noten = new note($conn);
$noten->getAll();
$noten_arr = array();
$noten_farben = array();

foreach ($noten->result as $row)
{
	$noten_arr[$row->note]=$row->anmerkung;
	$noten_farben[$row->note]=$row->farbe;
}

if($typ=='xls')
{
	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();
	
	// sending HTTP headers
	$workbook->send("Notenliste_".$semester_aktuell."_".$stg->kuerzel.($semester!=''?'_'.$semester:'').".xls");
	
	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("Notenliste");
	
	//Formate Definieren
	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();
	$format_bold->setBorder(1);
	
	$format_rotate =& $workbook->addFormat();
	$format_rotate->setTextRotation(270);
	$format_rotate->setAlign('center');
	
	$format_number =& $workbook->addFormat();
	$format_number->setNumFormat('0.00');
	$format_number->setBorder(1);

	//Farben ueberschreiben
	foreach ($noten_farben as $note=>$farbe) 
	{
		
		if($farbe!='')
		{
			$workbook->setCustomColor($note+10, 
				hexdec(substr($farbe,0,2)),
				hexdec(substr($farbe,2,2)),
				hexdec(substr($farbe,4,2)));
		}
		else 
		{
			$workbook->setCustomColor($note+10, 255, 255, 255);
		}
	}
	
	$spalte=0;
	$zeile=0;
	
	$worksheet->write($zeile,$spalte,'Nachname', $format_bold);
	$maxlength[$spalte]=10;
	$worksheet->write($zeile,++$spalte,'Vorname', $format_bold);
	$maxlength[$spalte]=10;
	$worksheet->write($zeile,++$spalte,'Personenkennzeichen', $format_bold);
	$maxlength[$spalte]=20;
	
	foreach ($lehrveranstaltung->lehrveranstaltungen as $row_lva)
	{
		$worksheet->write($zeile,++$spalte,$row_lva->bezeichnung, $format_rotate);
		$maxlength[$spalte]=3;
	}
	$worksheet->write($zeile,++$spalte,'Notendurchschnitt', $format_bold);
	$maxlength[$spalte]=15;
	
	$anzahl_lv=array();
	$summe_lv=array();
	
	foreach ($result_student as $row_student)
	{
		$zeile++;
		$spalte=0;
		
		$worksheet->write($zeile,$spalte,$row_student->nachname, $format_bold);
		if($maxlength[$spalte]<strlen($row_student->nachname))
			$maxlength[$spalte]=strlen($row_student->nachname);
		$worksheet->write($zeile,++$spalte,$row_student->vorname, $format_bold);
		if($maxlength[$spalte]<strlen($row_student->vorname))
			$maxlength[$spalte]=strlen($row_student->vorname);
		$worksheet->write($zeile,++$spalte,$row_student->matrikelnr, $format_bold);
				
		$noten = array();
		$qry = "SELECT * FROM lehre.tbl_zeugnisnote WHERE student_uid='$row_student->uid' AND studiensemester_kurzbz='$semester_aktuell'";
		if($result = pg_query($conn, $qry))
			while($row = pg_fetch_object($result))
				$noten[$row->lehrveranstaltung_id] = $row->note;
		
		$anzahl=0;
		$summe=0;
		foreach ($lehrveranstaltung->lehrveranstaltungen as $row_lva)
		{
			if(isset($noten[$row_lva->lehrveranstaltung_id]))
			{								
				unset($format_colored);
				$format_colored =& $workbook->addFormat();
				$format_colored->setFgColor($noten[$row_lva->lehrveranstaltung_id]+10);
				$format_colored->setBorder(1);
				$format_colored->setAlign('center');
				
				if(isset($format_colored))
					$worksheet->write($zeile,++$spalte,$noten_arr[$noten[$row_lva->lehrveranstaltung_id]],$format_colored);
				else 
					$worksheet->write($zeile,++$spalte,$noten_arr[$noten[$row_lva->lehrveranstaltung_id]]);
				
				if(is_numeric($noten_arr[$noten[$row_lva->lehrveranstaltung_id]]))
				{
					if(!isset($summe_lv[$row_lva->lehrveranstaltung_id]))
					{
						$summe_lv[$row_lva->lehrveranstaltung_id]=0;
						$anzahl_lv[$row_lva->lehrveranstaltung_id]=0;
					}
					$summe_lv[$row_lva->lehrveranstaltung_id] += $noten[$row_lva->lehrveranstaltung_id];
					$anzahl_lv[$row_lva->lehrveranstaltung_id]++;
					$summe+=$noten[$row_lva->lehrveranstaltung_id];
					$anzahl++;
				}
			}
			else 
			{
				unset($format_colored);
				$format_colored =& $workbook->addFormat();
				$format_colored->setFgColor(19);
				$format_colored->setBorder(1);
				$format_colored->setAlign('center');

				$worksheet->write($zeile,++$spalte,'',$format_colored);
				unset($format_colored);
			}
		}
		if($anzahl!=0)
			$schnitt = $summe/$anzahl;
		else
			$schnitt=0;
			
		$worksheet->write($zeile,++$spalte,$schnitt, $format_number);
	}
	
	$zeile++;
	$spalte=2;
	$worksheet->write($zeile,$spalte,'Notendurchschnitt', $format_bold);
	
	$summe_schnitt=0;
	$anzahl_schnitt=0;
	foreach ($lehrveranstaltung->lehrveranstaltungen as $row_lva)
	{
		if(isset($summe_lv[$row_lva->lehrveranstaltung_id]))
		{
			if($anzahl_lv[$row_lva->lehrveranstaltung_id]!=0)
				$schnitt = $summe_lv[$row_lva->lehrveranstaltung_id]/$anzahl_lv[$row_lva->lehrveranstaltung_id];
			else 
				$schnitt = 0;
		}
		else
			$schnitt=0;
		if($schnitt!=0)
		{
			$summe_schnitt +=$schnitt;
			$anzahl_schnitt++;
		}
		$worksheet->write($zeile,++$spalte,$schnitt, $format_number);
	}
	
	if($anzahl_schnitt!=0)
		$schnitt = $summe_schnitt/$anzahl_schnitt;
	else 
		$schnitt=0;
	$worksheet->write($zeile,++$spalte,$schnitt, $format_number);
	
	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet->setColumn($i, $i, $breite+2);
		
	$workbook->close();
}
else 
{
	echo '
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
	<title>Lehreinheit</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<style type="text/css">
	td, th
	{
		border: 1px solid black;
		text-align: center;
	}
	</style>
	</head>
	<body class="Background_main">';
	
	
	
	echo "<h2>Notenspiegel $stg->kuerzel $semester</h2>";
	
	echo '<table class="liste" style="border: 1px solid black" cellspacing="0"><tr class="liste"><th>Nr</th><th>Name</th><th>Personenkennzeichen</th>';
	foreach ($lehrveranstaltung->lehrveranstaltungen as $row_lva)
	{
		echo "<th>$row_lva->bezeichnung</th>";
	}
	echo '<th>Notendurchschnitt</td>';
	echo '</tr>';
	$i=0;
	$anzahl_lv=array();
	$summe_lv=array();
	
	foreach ($result_student as $row_student)
	{
		$i++;
		echo "<tr><td>$i</td><td>$row_student->nachname $row_student->vorname</td><td>$row_student->matrikelnr</td>";
		
		$noten = array();
		$qry = "SELECT * FROM lehre.tbl_zeugnisnote WHERE student_uid='$row_student->uid' AND studiensemester_kurzbz='$semester_aktuell'";
		if($result = pg_query($conn, $qry))
			while($row = pg_fetch_object($result))
				$noten[$row->lehrveranstaltung_id] = $row->note;
		
		$anzahl=0;
		$summe=0;
		foreach ($lehrveranstaltung->lehrveranstaltungen as $row_lva)
		{
			if(isset($noten[$row_lva->lehrveranstaltung_id]))
			{
				if($noten_farben[$noten[$row_lva->lehrveranstaltung_id]]!='')
					$farbe = "style='background-color: #".$noten_farben[$noten[$row_lva->lehrveranstaltung_id]].";'";
				else
					$farbe = '';
				
				echo "<td $farbe>".$noten_arr[$noten[$row_lva->lehrveranstaltung_id]]."</td>";
				
				if(is_numeric($noten_arr[$noten[$row_lva->lehrveranstaltung_id]]))
				{
					if(!isset($summe_lv[$row_lva->lehrveranstaltung_id]))
					{
						$summe_lv[$row_lva->lehrveranstaltung_id]=0;
						$anzahl_lv[$row_lva->lehrveranstaltung_id]=0;
					}
					$summe_lv[$row_lva->lehrveranstaltung_id] += $noten[$row_lva->lehrveranstaltung_id];
					$anzahl_lv[$row_lva->lehrveranstaltung_id]++;
					$summe+=$noten[$row_lva->lehrveranstaltung_id];
					$anzahl++;
				}
			}
			else 
			{
				echo '<td style="background-color: #'.$noten_farben[9].'">&nbsp;</td>';
			}
		}
		if($anzahl!=0)
			$schnitt = $summe/$anzahl;
		else
			$schnitt=0;
		echo "<td>".($schnitt==0?'&nbsp;':sprintf("%.2f", $schnitt))."</td>";
		echo '</tr>';
	}
	
	echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td>Notendurchschnitt</td>';
	$summe_schnitt=0;
	$anzahl_schnitt=0;
	foreach ($lehrveranstaltung->lehrveranstaltungen as $row_lva)
	{
		if(isset($summe_lv[$row_lva->lehrveranstaltung_id]))
		{
			if($anzahl_lv[$row_lva->lehrveranstaltung_id]!=0)
				$schnitt = $summe_lv[$row_lva->lehrveranstaltung_id]/$anzahl_lv[$row_lva->lehrveranstaltung_id];
			else 
				$schnitt = 0;
		}
		else
			$schnitt=0;
		if($schnitt!=0)
		{
			$summe_schnitt +=$schnitt;
			$anzahl_schnitt++;
		}
		echo "<td>".($schnitt==0?'&nbsp;':sprintf("%.2f",$schnitt))."</td>";
	}
	
	if($anzahl_schnitt!=0)
		$schnitt = $summe_schnitt/$anzahl_schnitt;
	else 
		$schnitt=0;
	echo "<td>".($schnitt==0?'&nbsp;':sprintf("%.2f",$schnitt))."</td>";
	
	echo '</table>';
	echo '</body>
	</html>';
}
?>
