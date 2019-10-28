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
/**
 * Erstellt einen Notenspiegel
 * 
 * Parameter: 	studiengang_kz ... Studiengang der angezeigt werden soll
 * 				semester ... Semester das angezeigt werden soll
 * 				orgform ... Filter für Organisationsform (VZ | BB | FST | etc)
 * 				typ	...	Output format (xls | html)
 * 
 * Listet alle Noten der Studierenden des Studiengangs/Semester im eingestellten Studiensemester
 * und berechnet den Notendurchschnitt und gewichteten Notendurchschnitt.
 * 
 * Gewichteter Notendurchschnitt = (Note der LV) * (ECTS der LV) / (Summe aller ECTS) 
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/note.class.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/Excel/excel.php');

$db = new basis_db();
$user = get_uid();
loadVariables($user);

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!isset($_GET['studiengang_kz']))
	die('Falsche Parameteruebergabe');
else 
	$studiengang_kz = $_GET['studiengang_kz'];

if(!$rechte->isBerechtigt('student/noten',$studiengang_kz, 's'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$semester = isset($_GET['semester'])?$_GET['semester']:'';
$typ = isset($_GET['typ'])?$_GET['typ']:'';

if($semester=='')
	die('Bitte ein Semester auswaehlen');
	
$orgform = isset($_GET['orgform'])?$_GET['orgform']:'';

$stg = new studiengang();
$stg_arr = array();
$stg->getAll(false);
foreach ($stg->result as $studiengang)
	$stg_arr[$studiengang->studiengang_kz]=$studiengang->kuerzel;

$stg = new studiengang();
$stg->load($studiengang_kz);

$student = new student();
$result_student = $student->getStudents($studiengang_kz,$semester,null,null,null, $semester_aktuell);
$uids='';
foreach ($result_student as $row) 
{
	if($uids!='')
		$uids.=',';
	$uids.=$db->db_add_param($row->uid);
}
if($uids=='')
	die('Es befinden sich keine Studierende in diesem Semester');

$qry = "SELECT 
			lehrveranstaltung_id, bezeichnung, studiengang_kz, semester, ects
		FROM 
			lehre.tbl_lehrveranstaltung 
        WHERE 
        	lehrveranstaltung_id IN 
        	(
				SELECT 
					distinct lehrveranstaltung_id 
				FROM 
					campus.vw_student_lehrveranstaltung, public.tbl_studentlehrverband
	        	WHERE 
	        		tbl_studentlehrverband.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER)." AND 
	        		tbl_studentlehrverband.semester=".$db->db_add_param($semester, FHC_INTEGER)." AND 
	        		vw_student_lehrveranstaltung.studiensemester_kurzbz=".$db->db_add_param($semester_aktuell)." AND
	        		uid=student_uid AND 
	        		vw_student_lehrveranstaltung.studiensemester_kurzbz=tbl_studentlehrverband.studiensemester_kurzbz
	        ) 
	        AND studiengang_kz<>0
	        AND zeugnis
	    UNION
	    SELECT
	    	lehrveranstaltung_id, bezeichnung, studiengang_kz, semester, ects
	    FROM
	    	lehre.tbl_lehrveranstaltung JOIN lehre.tbl_zeugnisnote USING(lehrveranstaltung_id)
	    WHERE
	    	tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER)." AND
	    	zeugnis AND
	    	tbl_zeugnisnote.student_uid in($uids) AND
	    	tbl_zeugnisnote.studiensemester_kurzbz=".$db->db_add_param($semester_aktuell)."
		ORDER BY bezeichnung";

if(!$result_lva = $db->db_query($qry))
	die('Fehler beim Ermitteln der Lehrveranstaltungen');

$noten = new note();
$noten->getAll();
$noten_arr = array();
$noten_farben = array();

foreach ($noten->result as $row)
{
	$noten_arr[$row->note]=$row->anmerkung;
	$noten_wert[$row->note]=$row->notenwert;
	$noten_farben[$row->note]=$row->farbe;
}

if($typ=='xls')
{
	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();
	$workbook->setVersion(8);
	// sending HTTP headers
	$workbook->send("Notenliste_".$semester_aktuell."_".$stg->kuerzel.($semester!=''?'_'.$semester:'').".xls");
	
	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("Notenliste");
	$worksheet->setInputEncoding('utf-8');
	
	//Formate Definieren
	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();
	$format_bold->setBorder(1);
	
	$format_bold_wrap =& $workbook->addFormat();
	$format_bold_wrap->setBold();
	$format_bold_wrap->setBorder(1);
	$format_bold_wrap->setTextWrap();
	
	$format_rotate =& $workbook->addFormat();
	$format_rotate->setTextRotation(270);
	$format_rotate->setAlign('center');
	
	$format_bold_center =& $workbook->addFormat();
	$format_bold_center->setBold();
	$format_bold_center->setAlign('center');
	$format_bold_center->setBorder(1);
	
	$format_bold_left =& $workbook->addFormat();
	$format_bold_left->setBold();
	$format_bold_left->setAlign('left');
	$format_bold_left->setBorder(1);
	
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

		$format_colored[$note] =& $workbook->addFormat();
		$format_colored[$note]->setFgColor($note+10);
		$format_colored[$note]->setBorder(1);
		$format_colored[$note]->setAlign('center');
	}
	//30 = Grau = Nicht teilgenommen
	$workbook->setCustomColor(30,90,90,90);

	$format_colored_nichtzugeteilt =& $workbook->addFormat();
	$format_colored_nichtzugeteilt->setFgColor(30);
	$format_colored_nichtzugeteilt->setBorder(1);
	$format_colored_nichtzugeteilt->setAlign('center');

	$format_colored_nichteingetragen =& $workbook->addFormat();
	$format_colored_nichteingetragen->setFgColor(19);
	$format_colored_nichteingetragen->setBorder(1);
	$format_colored_nichteingetragen->setAlign('center');
	
	$spalte=0;
	$zeile=1;
	
	$worksheet->write($zeile,$spalte,'Nachname', $format_bold);
	$maxlength[$spalte]=10;
	$worksheet->write($zeile,++$spalte,'Vorname', $format_bold);
	$maxlength[$spalte]=10;
	$worksheet->write($zeile, ++$spalte, 'V', $format_bold);
	$maxlength[$spalte] = 2;
	$worksheet->write($zeile, ++$spalte, 'G', $format_bold);
	$maxlength[$spalte] = 2;
	$worksheet->write($zeile,++$spalte,'Personenkennzeichen', $format_bold);
	$maxlength[$spalte] = 20;
	$maxheaderheight = 20;
	
	while($row_lva = $db->db_fetch_object($result_lva))
	{
		$value = $stg_arr[$row_lva->studiengang_kz].$row_lva->semester.' '.$row_lva->bezeichnung.' ('.$row_lva->ects.' ECTS)';
		$worksheet->write($zeile,++$spalte,$value, $format_rotate);
		$maxlength[$spalte]=3;

		if(mb_strlen($value)>$maxheaderheight)
			$maxheaderheight=mb_strlen($value);
		//echo "len:".mb_strlen($value),' max:'.$maxheaderheight;
	}
	$worksheet->write($zeile,++$spalte,'Notendurchschnitt', $format_bold);
	$maxlength[$spalte]=15;
	$worksheet->write($zeile,++$spalte,"Gewichteter\nNotendurchschnitt", $format_bold_wrap);
	$maxlength[$spalte]=15;
	
	$anzahl_lv=array();
	$summe_lv=array();
	$summegewichtet=0;
	$anzahlgewichtet=0;
	foreach ($result_student as $row_student)
	{
		if($orgform!='')
		{
			//Wenn der Student nicht die passende orgform hat (VZ,BB,FST, etc)
			//dann nicht anzeigen
			$prestudent = new prestudent();
			$prestudent->getLastStatus($row_student->prestudent_id);
			
			if($prestudent->orgform_kurzbz!=$orgform)
				continue;
		}
		$zeile++;
		$spalte=0;
		
		$worksheet->write($zeile,$spalte,$row_student->nachname, $format_bold);
		if($maxlength[$spalte]<strlen($row_student->nachname))
			$maxlength[$spalte]=strlen($row_student->nachname);
		$worksheet->write($zeile,++$spalte,$row_student->vorname, $format_bold);
		if($maxlength[$spalte]<strlen($row_student->vorname))
			$maxlength[$spalte]=strlen($row_student->vorname);
		$worksheet->write($zeile, ++$spalte, $row_student->verband, $format_bold);
		$worksheet->write($zeile, ++$spalte, $row_student->gruppe, $format_bold_left);
		$worksheet->write($zeile, ++$spalte,$row_student->matrikelnr, $format_bold);
				
		//Alle Zeugnisnoten des Studierenden holen
		$noten = array();
		$qry = "SELECT * FROM lehre.tbl_zeugnisnote WHERE student_uid=".$db->db_add_param($row_student->uid)." AND studiensemester_kurzbz=".$db->db_add_param($semester_aktuell);
		if($result = $db->db_query($qry))
			while($row = $db->db_fetch_object($result))
				$noten[$row->lehrveranstaltung_id] = $row->note;
			
		//Alle LVs holen zu denen der Studierende zugeteilt ist
		$zugeteilte_lvs=array();
		$qry = "SELECT distinct lehrveranstaltung_id
					FROM 
						campus.vw_student_lehrveranstaltung 
					WHERE 
						uid=".$db->db_add_param($row_student->uid)." AND 
						studiensemester_kurzbz=".$db->db_add_param($semester_aktuell);
		
		if($result = $db->db_query($qry))
			while($row = $db->db_fetch_object($result))
				$zugeteilte_lvs[] = $row->lehrveranstaltung_id;
						
		$anzahl=0;
		$summe=0;
		$rowcount=0;
		$summeects=0;
		$gewichtetenote=0;

		while($rowcount<$db->db_num_rows($result_lva))
		{
			$row_lva = $db->db_fetch_object($result_lva,$rowcount);
			$rowcount++;
			if(isset($noten[$row_lva->lehrveranstaltung_id]))
			{								
				if(isset($format_colored[$noten[$row_lva->lehrveranstaltung_id]]))
					$worksheet->write($zeile,++$spalte,$noten_arr[$noten[$row_lva->lehrveranstaltung_id]],$format_colored[$noten[$row_lva->lehrveranstaltung_id]]);
				else 
					$worksheet->write($zeile,++$spalte,$noten_arr[$noten[$row_lva->lehrveranstaltung_id]]);
				
				if($noten_wert[$noten[$row_lva->lehrveranstaltung_id]]!='')
				{
					if(!isset($summe_lv[$row_lva->lehrveranstaltung_id]))
					{
						$summe_lv[$row_lva->lehrveranstaltung_id]=0;
						$anzahl_lv[$row_lva->lehrveranstaltung_id]=0;
					}
					$summe_lv[$row_lva->lehrveranstaltung_id] += $noten_wert[$noten[$row_lva->lehrveranstaltung_id]];
					$anzahl_lv[$row_lva->lehrveranstaltung_id]++;
					$summe+=$noten_wert[$noten[$row_lva->lehrveranstaltung_id]];
					if(is_numeric($row_lva->ects))
					{
						$gewichtetenote += $noten_wert[$noten[$row_lva->lehrveranstaltung_id]]*$row_lva->ects;
						$summeects+=$row_lva->ects;
					}
					$anzahl++;
				}
			}
			else 
			{
				//Keine Note fuer diese LV vorhanden				
				if(in_array($row_lva->lehrveranstaltung_id, $zugeteilte_lvs))
				{
					$worksheet->write($zeile,++$spalte,'',$format_colored_nichteingetragen);
				}
				else
				{
					$worksheet->write($zeile,++$spalte,'',$format_colored_nichtzugeteilt);
				}

			}
		}
		if($anzahl!=0)
			$schnitt = $summe/$anzahl;
		else
			$schnitt=0;
			
		if($summeects!=0)
			$gewichtetenote /= $summeects;
		
		$worksheet->write($zeile,++$spalte,sprintf("%.2f",$schnitt), $format_number);
		$worksheet->write($zeile,++$spalte,sprintf("%.2f",$gewichtetenote), $format_number);
		if($gewichtetenote!=0)
		{
			$summegewichtet+=$gewichtetenote;
			$anzahlgewichtet++;
		}
	}
	
	$zeile++;
	$spalte = 4;
	$worksheet->write($zeile,$spalte,'Notendurchschnitt', $format_bold);
	
	$summe_schnitt=0;
	$anzahl_schnitt=0;
	$rowcount=0;
	while($rowcount<$db->db_num_rows($result_lva))
	{
		$row_lva = $db->db_fetch_object($result_lva, $rowcount);
		$rowcount++;
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
		$worksheet->write($zeile,++$spalte,sprintf("%.2f",$schnitt), $format_number);
	}
	
	if($anzahl_schnitt!=0)
		$schnitt = $summe_schnitt/$anzahl_schnitt;
	else 
		$schnitt=0;
	$worksheet->write($zeile,++$spalte,sprintf("%.2f",$schnitt), $format_number);
	if($anzahlgewichtet!=0)
		$summegewichtet = $summegewichtet/$anzahlgewichtet;
	$worksheet->write($zeile,++$spalte,sprintf("%.2f", $summegewichtet), $format_number);
	
	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet->setColumn($i, $i, $breite+2);

	$worksheet->write(0,0,$semester_aktuell." ".$stg->kuerzel.($semester!=''?' '.$semester.'. Semester':'').' Stand: '.date('d.m.Y'), $format_bold_center);
	//Zellen der 1. Zeile verbinden
	$worksheet->setMerge(0,0,0,$spalte);
	
	//Hoehe der 2. Zeile anpassen damit die LVs alle sichtbar sind, aber nicht größer als 300
	if ($maxheaderheight * 5 > 300)
		$maxheaderheight = 60;
	$worksheet->setRow(1, $maxheaderheight * 5);
	
	//Ausdruck auf 1 Seite anpassen
	$worksheet->fitToPages(1,1);
	$workbook->close();
}
else 
{
	echo '
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
	<title>Notenspiegel</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
	
	echo '<table class="liste" style="border: 1px solid black" cellspacing="0"><tr class="liste"><th>Nr</th><th>Name</th><th>PersonenKz</th>';
	while($row_lva = $db->db_fetch_object($result_lva))
	{
		echo "<th>".$stg_arr[$row_lva->studiengang_kz]."$row_lva->semester $row_lva->bezeichnung ($row_lva->ects ECTS)</th>";
	}
	echo '<th>Notendurchschnitt</th>';
	echo '<th>Gewichteter Notendurchschnitt</th>';
	echo '</tr>';
	$i=0;
	$anzahl_lv=array();
	$summe_lv=array();
	$summegewichtet=0;
	$anzahlgewichtet=0;
	foreach ($result_student as $row_student)
	{
		if($orgform!='')
		{
			//Wenn der Student nicht die passende orgform hat (VZ,BB,FST, etc)
			//dann nicht anzeigen
			$prestudent = new prestudent();
			$prestudent->getLastStatus($row_student->prestudent_id);
			
			if($prestudent->orgform_kurzbz!=$orgform)
				continue;
		}
		$i++;
		echo "<tr><td>$i</td><td>$row_student->nachname $row_student->vorname</td><td>$row_student->matrikelnr</td>";
		
		$noten = array();
		$qry = "SELECT * FROM lehre.tbl_zeugnisnote WHERE student_uid=".$db->db_add_param($row_student->uid)." AND studiensemester_kurzbz=".$db->db_add_param($semester_aktuell);
		if($result = $db->db_query($qry))
			while($row = $db->db_fetch_object($result))
				$noten[$row->lehrveranstaltung_id] = $row->note;
				
		//Alle LVs holen zu denen der Studierende zugeteilt ist
		$zugeteilte_lvs=array();
		$qry = "SELECT distinct lehrveranstaltung_id
					FROM 
						campus.vw_student_lehrveranstaltung 
					WHERE 
						uid=".$db->db_add_param($row_student->uid)." AND 
						studiensemester_kurzbz=".$db->db_add_param($semester_aktuell);
		
		if($result = $db->db_query($qry))
			while($row = $db->db_fetch_object($result))
				$zugeteilte_lvs[] = $row->lehrveranstaltung_id;
				
		$anzahl=0;
		$summe=0;
		$rowcount=0;
		$summeects=0;
		$gewichtetenote=0;
		while($rowcount<$db->db_num_rows($result_lva))
		{
			$row_lva =  $db->db_fetch_object($result_lva, $rowcount);
			$rowcount++;
			if(isset($noten[$row_lva->lehrveranstaltung_id]))
			{
				if($noten_farben[$noten[$row_lva->lehrveranstaltung_id]]!='')
					$farbe = "style='background-color: #".$noten_farben[$noten[$row_lva->lehrveranstaltung_id]].";'";
				else
					$farbe = '';
				
				echo "<td $farbe>".$noten_arr[$noten[$row_lva->lehrveranstaltung_id]]."</td>";
				
				if($noten_wert[$noten[$row_lva->lehrveranstaltung_id]]!='')
				{
					if(!isset($summe_lv[$row_lva->lehrveranstaltung_id]))
					{
						$summe_lv[$row_lva->lehrveranstaltung_id]=0;
						$anzahl_lv[$row_lva->lehrveranstaltung_id]=0;
					}
					$summe_lv[$row_lva->lehrveranstaltung_id] += $noten_wert[$noten[$row_lva->lehrveranstaltung_id]];
					$anzahl_lv[$row_lva->lehrveranstaltung_id]++;
					$summe+=$noten_wert[$noten[$row_lva->lehrveranstaltung_id]];
					if(is_numeric($row_lva->ects))
					{
						$gewichtetenote += $noten_wert[$noten[$row_lva->lehrveranstaltung_id]]*$row_lva->ects;
						$summeects+=$row_lva->ects;
					}
					$anzahl++;
				}
			}
			else 
			{
				if(in_array($row_lva->lehrveranstaltung_id, $zugeteilte_lvs))
					$farbe = $noten_farben[9]; //zugeteilt aber noch nicht eingetragen
				else
					$farbe = '5a5a5a'; //nicht zugeteilt zur lv
					
				echo '<td style="background-color: #'.$farbe.'">&nbsp;</td>';
			}
		}
		if($anzahl!=0)
			$schnitt = $summe/$anzahl;
		else
			$schnitt=0;
			
		if($summeects!=0)
			$gewichtetenote /= $summeects;
		echo "<td>".($schnitt==0?'&nbsp;':sprintf("%.2f", $schnitt))."</td>";
		echo "<td>".($gewichtetenote==0?'&nbsp;':sprintf("%.2f", $gewichtetenote))."</td>";
		if($gewichtetenote!=0)
		{
			$summegewichtet+=$gewichtetenote;
			$anzahlgewichtet++;
		}
		echo '</tr>';
	}
	
	echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td>Notendurchschnitt</td>';
	$summe_schnitt=0;
	$anzahl_schnitt=0;
	$rowcount=0;
	while($rowcount<$db->db_num_rows($result_lva))
	{
		$row_lva = $db->db_fetch_object($result_lva, $rowcount);
		$rowcount++;
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
	if($anzahlgewichtet!=0)
		$summegewichtet = $summegewichtet/$anzahlgewichtet;
	echo "<td>".($schnitt==0?'&nbsp;':sprintf("%.2f",$schnitt))."</td>";
	echo "<td>".($summegewichtet==0?'&nbsp;':sprintf("%.2f",$summegewichtet))."</td>";
	
	echo '</table>';
	echo '</body>
	</html>';
}
?>
