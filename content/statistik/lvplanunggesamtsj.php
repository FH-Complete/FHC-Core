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
$format_colored->setFgColor(47);
$format_colored->setBorder(1);
$format_colored->setBold();

$zeile=0;
$spalte=0;

$worksheet->write($zeile, $spalte,'Fachbereich');
$worksheet->write($zeile, ++$spalte,'Kurzbz');
$worksheet->write($zeile, ++$spalte,'Bezeichnung');
$worksheet->write($zeile, ++$spalte,'Lehrform');
$worksheet->write($zeile, ++$spalte,'ECTS');
$worksheet->write($zeile, ++$spalte,'Stunden');
$worksheet->write($zeile, ++$spalte,'Gruppen');
$worksheet->write($zeile, ++$spalte,'Lektor');
$worksheet->write($zeile, ++$spalte,'Kosten');
$worksheet->write($zeile, ++$spalte,'Gesamtkosten');

if($result = pg_query($conn, $qry))
{
	$last_lva='';
	$stunden_lv=0;
	$kosten_lv=0;
	$gesamtkosten_lva=0;
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
				$worksheet->write($zeile, $spalte,number_format($kosten_lv,2,',','.'), $format_bold);
				
				$gesamtkosten_lva +=$kosten_lv;
				$stunden_lv=0;
				$kosten_lv=0;
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
		$worksheet->write($zeile, ++$spalte, number_format(($row->lektor_stundensatz*$row->lektor_faktor*$row->lektor_semesterstunden),2,',','.'));
		
		$kosten_lv +=($row->lektor_stundensatz*$row->lektor_faktor*$row->lektor_semesterstunden);
		$stunden_lv +=$row->lektor_semesterstunden;
	}
	$gesamtkosten_lva +=$kosten_lv;
	
	$zeile++;
	$spalte=6;
	$worksheet->write($zeile, $spalte, sprintf('%.2f',$stunden_lv), $format_bold);
	$spalte=8;
	$worksheet->write($zeile, $spalte, number_format($kosten_lv,2,',','.'), $format_bold);
	$worksheet->write($zeile, ++$spalte, number_format($gesamtkosten_lva,2,',','.'), $format_bold);
}

$qry = "SELECT
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
			(tbl_projektbetreuer.faktor*tbl_projektbetreuer.stundensatz*tbl_projektbetreuer.stunden)>0
			";

if($result = pg_query($conn, $qry))
{
	if(pg_num_rows($result)>0)
	{
		$zeile++;
		$spalte=0;
		$worksheet->write($zeile, $spalte, "Betreuungen", $format_bold);
		
		$zeile++;

		$spalte=1;
		$worksheet->write($zeile, ++$spalte, "Titel", $format_colored);
		$worksheet->write($zeile, ++$spalte, "Stunden", $format_colored);
		$worksheet->write($zeile, ++$spalte, "Student", $format_colored);
		$worksheet->write($zeile, ++$spalte, "Lektor", $format_colored);
		$worksheet->write($zeile, ++$spalte, "Kosten", $format_colored);
				
		$gesamtkosten_betreuung=0;
		$stunden_betreuung=0;
		while($row = pg_fetch_object($result))
		{
			$zeile++;
			$spalte=1;
			$worksheet->write($zeile, ++$spalte, $row->titel);
			$worksheet->write($zeile, ++$spalte, number_format($row->stunden,2));
			
			$benutzer = new benutzer($conn);
			$benutzer->load($row->student_uid);
			$worksheet->write($zeile, ++$spalte, "$benutzer->nachname $benutzer->vorname");
			$worksheet->write($zeile, ++$spalte, "$row->nachname $row->vorname");
			$worksheet->write($zeile, ++$spalte, number_format(($row->stundensatz*$row->faktor*$row->stunden),2,',','.'));
			
			$gesamtkosten_betreuung +=($row->stundensatz*$row->faktor*$row->stunden);
			$stunden_betreuung+=$row->stunden;
		}

		$zeile++;
		$spalte=3;
		$worksheet->write($zeile, $spalte, number_format($stunden_betreuung,2), $format_bold);
		$spalte=6;
		$worksheet->write($zeile, $spalte, number_format($gesamtkosten_betreuung,2,',','.'), $format_bold);
				
		$zeile++;
		$spalte=8;
		$worksheet->write($zeile, $spalte, number_format(($gesamtkosten_betreuung+$gesamtkosten_lva),2,',','.'), $format_bold);
	}
}

$workbook->close();
?>