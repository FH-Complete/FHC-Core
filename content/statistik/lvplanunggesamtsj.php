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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/Excel/excel.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Datenbankverbindung konnte nicht hergestellt werden');

$user = get_uid();
loadVariables($conn, $user);

$stg_arr = array();
$studiengang = new studiengang($conn);
$studiengang->getAll();

foreach ($studiengang->result as $row)
	$stg_arr[$row->studiengang_kz] = $row->kuerzel;

// ****** FUNKTIONEN ******* //
function drawBetreuungen()
{
	global $row, $gesamtkosten_lva, $kosten_lv, $zeile, $spalte, $stsem1, $stsem2, $last_fb, $worksheet;
	global $stunden_lv, $format_bold, $format_colored, $gesamtkosten_betreuung, $stunden_betreuungen;
	global $gesamtkosten_fb, $conn, $format_number, $format_number1;
	
	$qry_fb = "SELECT
				*
			FROM
				lehre.tbl_projektarbeit, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung, lehre.tbl_projektbetreuer, public.tbl_person, lehre.tbl_lehrfach
			WHERE
				tbl_projektarbeit.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND
				tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND
				tbl_projektarbeit.projektarbeit_id=tbl_projektbetreuer.projektarbeit_id AND
				tbl_lehreinheit.lehrfach_id=tbl_lehrfach.lehrfach_id AND
				tbl_person.person_id=tbl_projektbetreuer.person_id AND
				(tbl_lehreinheit.studiensemester_kurzbz='$stsem1' OR
				 tbl_lehreinheit.studiensemester_kurzbz='$stsem2') AND
				(tbl_projektbetreuer.faktor*tbl_projektbetreuer.stundensatz*tbl_projektbetreuer.stunden)>0 AND
				tbl_lehrfach.fachbereich_kurzbz='$last_fb'
				";
	$gesamtkosten_betreuung=0;
	if($result_fb = pg_query($conn, $qry_fb))
	{
		$spalte=8;
		$worksheet->writeNumber($zeile, ++$spalte, $gesamtkosten_lva, $format_number);
				
		if(pg_num_rows($result_fb)>0)
		{
					
			$zeile++;
						
			$zeile++;
			$worksheet->write($zeile, 1, "Betreuungen", $format_bold);
			$spalte=2;
			$worksheet->write($zeile, $spalte, "Titel", $format_colored);
			$worksheet->write($zeile, ++$spalte, "", $format_colored);
			$worksheet->write($zeile, ++$spalte, "", $format_colored);
			$worksheet->write($zeile, ++$spalte, "Stunden", $format_colored);
			$worksheet->write($zeile, ++$spalte, "Student", $format_colored);
			$worksheet->write($zeile, ++$spalte, "Lektor", $format_colored);
			$worksheet->write($zeile, ++$spalte, "Kosten", $format_colored);
					
			
			$stunden_betreuung=0;
			while($row_fb = pg_fetch_object($result_fb))
			{
				$zeile++;
				$spalte=2;
				$worksheet->write($zeile, $spalte, $row_fb->titel);
				$spalte+=2;
				$worksheet->write($zeile, ++$spalte, number_format($row_fb->stunden,2));
				
				$benutzer = new benutzer($conn);
				$benutzer->load($row_fb->student_uid);
				$worksheet->write($zeile, ++$spalte, "$benutzer->nachname $benutzer->vorname");
				$worksheet->write($zeile, ++$spalte, "$row_fb->nachname $row_fb->vorname");
				$worksheet->writeNumber($zeile, ++$spalte, ($row_fb->stundensatz*$row_fb->faktor*$row_fb->stunden), $format_number1);
				
				$gesamtkosten_betreuung +=($row_fb->stundensatz*$row_fb->faktor*$row_fb->stunden);
				$stunden_betreuung+=$row_fb->stunden;
			}
			
			$zeile++;
			$spalte=5;
			$worksheet->writeNumber($zeile, $spalte, $stunden_betreuung, $format_number);
			$spalte=8;
			$worksheet->writeNumber($zeile, $spalte, $gesamtkosten_betreuung, $format_number);
					
			$spalte=9;
			$worksheet->writeNumber($zeile, $spalte, $gesamtkosten_betreuung, $format_number);
									
		}
		$gesamtkosten_fb += ($gesamtkosten_betreuung+$gesamtkosten_lva);
		$gesamtkosten_lva=0;
	}
	else 
		echo 'Error';
}
// ****** END FUNKTIONEN ******* //
$stsem1 = $semester_aktuell;
$stsem_obj = new studiensemester($conn);

if(substr($stsem1,0,1)=='W')
	$stsem2 = $stsem_obj->getNextFrom($stsem1);
else 
	$stsem2 = $stsem_obj->getPreviousFrom($stsem1);
	
$qry = "SELECT
			tbl_lehrveranstaltung.kurzbz as kurzbz, tbl_lehrveranstaltung.bezeichnung as bezeichnung, tbl_lehrveranstaltung.lehrveranstaltung_id,
			tbl_lehrveranstaltung.ects as ects, tbl_lehrveranstaltung.semesterstunden as semesterstunden,
			tbl_lehrfach.kurzbz as lf_kurzbz, tbl_lehrfach.bezeichnung as lf_bezeichnung, tbl_lehreinheit.lehreinheit_id as lehreinheit_id,
			tbl_lehreinheit.lehrform_kurzbz as lehrform_kurzbz, tbl_lehreinheitmitarbeiter.semesterstunden as lektor_semesterstunden,
			tbl_lehreinheitmitarbeiter.stundensatz as lektor_stundensatz, tbl_lehreinheitmitarbeiter.faktor as lektor_faktor,
			tbl_person.vorname, tbl_person.nachname, tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.semester,
			tbl_lehrfach.fachbereich_kurzbz
		FROM
			lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter,
			lehre.tbl_lehrfach, public.tbl_benutzer, public.tbl_person
		WHERE
			tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
			tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
			tbl_lehrfach.lehrfach_id=tbl_lehreinheit.lehrfach_id AND
			tbl_benutzer.uid=tbl_lehreinheitmitarbeiter.mitarbeiter_uid AND
			tbl_person.person_id=tbl_benutzer.person_id AND
			(tbl_lehreinheit.studiensemester_kurzbz='".addslashes($stsem1)."' OR
			 tbl_lehreinheit.studiensemester_kurzbz='".addslashes($stsem2)."')
		ORDER BY 
			tbl_lehrfach.fachbereich_kurzbz,
			tbl_lehrveranstaltung.studiengang_kz, 
			tbl_lehrveranstaltung.semester, 
			tbl_lehrveranstaltung.bezeichnung, 
			tbl_lehrveranstaltung.lehrveranstaltung_id, 
			tbl_lehreinheit.lehreinheit_id";

// Creating a workbook
$workbook = new Spreadsheet_Excel_Writer();

// sending HTTP headers
$workbook->send("LVPlanungGesamtSJ". "_" . date("Y_m_d") . ".xls");

// Creating a worksheet
$worksheet =& $workbook->addWorksheet("Bewerberstatistik");

$format_bold =& $workbook->addFormat();
$format_bold->setBold();

$format_bold =& $workbook->addFormat();
$format_bold->setBold();

$format_colored =& $workbook->addFormat();
$format_colored->setFgColor(22);
$format_colored->setBorder(1);
$format_colored->setBold();

$format_colored1 =& $workbook->addFormat();
$format_colored1->setFgColor(22);
$format_colored1->setBorder(1);
$format_colored1->setBold();

$format_number =& $workbook->addFormat();
$format_number->setNumFormat("#,##0.00");
$format_number->setBold();

$format_number1 =& $workbook->addFormat();
$format_number1->setNumFormat("#,##0.00");

$worksheet->write(0, 0,"LV-Planung f�r $stsem1/$stsem2", $format_bold);

$zeile=3;
$spalte=0;

$worksheet->write($zeile, $spalte,'Fachbereich', $format_colored1);
$worksheet->write($zeile, ++$spalte,'Kurzbz', $format_colored1);
$worksheet->write($zeile, ++$spalte,'Bezeichnung', $format_colored1);
$worksheet->write($zeile, ++$spalte,'Lehrform', $format_colored1);
$worksheet->write($zeile, ++$spalte,'ECTS', $format_colored1);
$worksheet->write($zeile, ++$spalte,'Stunden', $format_colored1);
$worksheet->write($zeile, ++$spalte,'Gruppen', $format_colored1);
$worksheet->write($zeile, ++$spalte,'Lektor', $format_colored1);
$worksheet->write($zeile, ++$spalte,'Kosten', $format_colored1);
$worksheet->write($zeile, ++$spalte,'Gesamtkosten', $format_colored1);

if($result = pg_query($conn, $qry))
{
	$last_lva='';
	$stunden_lv=0;
	$kosten_lv=0;
	$gesamtkosten_lva=0;
	$gesamtkosten_fb=0;
	$last_fb='';
	while($row = pg_fetch_object($result))
	{
		if($last_lva!=$row->lehrveranstaltung_id)
		{
			if($last_lva!='')
			{
				$zeile++;
				$spalte=5;
				$worksheet->write($zeile, $spalte,sprintf('%.2f',$stunden_lv), $format_bold);
				$spalte=8;
				$worksheet->writeNumber($zeile, $spalte, $kosten_lv, $format_number);
				
				$gesamtkosten_lva +=$kosten_lv;
				$stunden_lv=0;
				$kosten_lv=0;
			}
			
			if($last_fb!=$row->fachbereich_kurzbz && $last_fb!='')
			{
				drawBetreuungen();
			}
			
			if($last_fb=='' || $last_fb!=$row->fachbereich_kurzbz)
			{
				$zeile++;
				$worksheet->write($zeile, 0, $row->fachbereich_kurzbz, $format_bold);
				$zeile++;
				$last_fb = $row->fachbereich_kurzbz;
			}
			
			$last_lva=$row->lehrveranstaltung_id;
			$zeile++;
			$spalte=0;
			$worksheet->write($zeile, $spalte, $row->fachbereich_kurzbz, $format_colored);
			$worksheet->write($zeile, ++$spalte, $stg_arr[$row->studiengang_kz].'-'.$row->semester.' '.$row->kurzbz, $format_colored);
			$worksheet->write($zeile, ++$spalte,$row->bezeichnung, $format_colored);
			$worksheet->write($zeile, ++$spalte,"", $format_colored);
			$worksheet->write($zeile, ++$spalte, $row->ects, $format_colored);
			$worksheet->write($zeile, ++$spalte,$row->semesterstunden, $format_colored);
			$worksheet->write($zeile, ++$spalte,"", $format_colored);
			$worksheet->write($zeile, ++$spalte,"", $format_colored);
			$worksheet->write($zeile, ++$spalte,"", $format_colored);
			$worksheet->write($zeile, ++$spalte,"", $format_colored);
		}

		$gruppen='';
		$qry_grp = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id='$row->lehreinheit_id'";
		if($result_grp=pg_query($conn, $qry_grp))
		{
			while($row_grp = pg_fetch_object($result_grp))
			{
				if($gruppen=='')
					$gruppen = ($row_grp->gruppe_kurzbz!=''?$row_grp->gruppe_kurzbz:trim($stg_arr[$row_grp->studiengang_kz].'-'.$row_grp->semester.$row_grp->verband.$row_grp->gruppe));
				else
					$gruppen .= ','.($row_grp->gruppe_kurzbz!=''?$row_grp->gruppe_kurzbz:trim($stg_arr[$row_grp->studiengang_kz].'-'.$row_grp->semester.$row_grp->verband.$row_grp->gruppe));
			}
		}
		$zeile++;
		$spalte=1;
		$worksheet->write($zeile, ++$spalte, "$row->lf_bezeichnung ($row->lf_kurzbz)");
		$worksheet->write($zeile, ++$spalte, $row->lehrform_kurzbz);
		$spalte++;
		$worksheet->write($zeile, ++$spalte, $row->lektor_semesterstunden);
		$worksheet->write($zeile, ++$spalte, $gruppen);
		$worksheet->write($zeile, ++$spalte, "$row->nachname $row->vorname");
		$worksheet->writeNumber($zeile, ++$spalte, ($row->lektor_stundensatz*$row->lektor_faktor*$row->lektor_semesterstunden), $format_number1);
		
		$kosten_lv +=($row->lektor_stundensatz*$row->lektor_faktor*$row->lektor_semesterstunden);
		$stunden_lv +=$row->lektor_semesterstunden;
	}
	$zeile++;
	$spalte=5;
	$worksheet->write($zeile, $spalte,sprintf('%.2f',$stunden_lv), $format_bold);
	$spalte=8;
	$worksheet->writeNumber($zeile, $spalte, $kosten_lv, $format_number);
	
	$gesamtkosten_lva +=$kosten_lv;
	$stunden_lv=0;
	$kosten_lv=0;


	drawBetreuungen();
		
}

	

$zeile++;
$spalte=8;
$worksheet->write($zeile, $spalte, 'Gesamt:', $format_bold);
$spalte=9;
$worksheet->writeNumber($zeile, $spalte, $gesamtkosten_fb, $format_number);

$worksheet->setColumn(0, 0, 30);
$worksheet->setColumn(0, 1, 15);
$worksheet->setColumn(0, 2, 40);
$worksheet->setColumn(0, 3, 5);
$worksheet->setColumn(0, 4, 5);
$worksheet->setColumn(0, 5, 10);
$worksheet->setColumn(0, 6, 10);
$worksheet->setColumn(0, 7, 10);
$worksheet->setColumn(0, 8, 10);
$worksheet->setColumn(0, 9, 20);

$workbook->close();
?>