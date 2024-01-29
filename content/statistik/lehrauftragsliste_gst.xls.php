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
/**
 * Erstellt ein Excel File mit einer Uebersicht der
 * Kosten fuer die Geschaeftsstelle
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/Excel/excel.php');
require_once('../../include/studiengang.class.php');

if(isset($_GET['studiengang_kz']) && is_numeric($_GET['studiengang_kz']))
	$studiengang_kz=$_GET['studiengang_kz'];
else
	die('studiengangs_kz muss uebergeben werden');

if(isset($_GET['semester']) && is_numeric($_GET['semester']))
	$semester=$_GET['semester'];
else
	$semester='';

$user = get_uid();
loadVariables($user);

//Studiengang laden
$studiengang = new studiengang($studiengang_kz);

// Creating a workbook
$workbook = new Spreadsheet_Excel_Writer();
$workbook->setVersion(8);
// sending HTTP headers
$workbook->send("Lehrauftragsliste_".$semester_aktuell."_".$studiengang->kuerzel.($semester!=''?'_'.$semester:'').".xls");

// Creating a worksheet
$worksheet =& $workbook->addWorksheet("Lehrauftragsliste");
$worksheet->setInputEncoding('utf-8');
//Formate Definieren
$format_bold =& $workbook->addFormat();
$format_bold->setBold();

$format_number =& $workbook->addFormat();
$format_number->setNumFormat('0,0.00');

$format_number_bold =& $workbook->addFormat();
$format_number_bold->setNumFormat('0,0.00');
$format_number_bold->setBold();

$i=0;
$worksheet->write(0,0,'Erstellt am '.date('d.m.Y').' '.$semester_aktuell.' '.$studiengang->kuerzel.' '.$semester, $format_bold);
//Ueberschriften
$worksheet->write(2,$i,"Studiengang", $format_bold);
$worksheet->write(2,++$i,"Personalnr", $format_bold);
$worksheet->write(2,++$i,"Titel", $format_bold);
$worksheet->write(2,++$i,"Vorname", $format_bold);
$worksheet->write(2,++$i,"Familienname", $format_bold);
$worksheet->write(2,++$i,"Fixangestellt", $format_bold);
$worksheet->write(2,++$i,"Stunden", $format_bold);
$worksheet->write(2,++$i,"Kosten", $format_bold);
$db = new basis_db();
//Daten holen
$qry = "SELECT * FROM (
		SELECT
			tbl_lehreinheit.*, tbl_person.vorname, tbl_person.nachname, tbl_person.titelpre,
			tbl_mitarbeiter.personalnummer, tbl_person.person_id, tbl_mitarbeiter.mitarbeiter_uid,
			tbl_mitarbeiter.fixangestellt,
			tbl_lehreinheitmitarbeiter.stundensatz as stundensatz,
			tbl_lehreinheitmitarbeiter.semesterstunden as semesterstunden
		FROM
			lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, public.tbl_mitarbeiter,
			public.tbl_benutzer, public.tbl_person, lehre.tbl_lehrveranstaltung
		WHERE
			tbl_person.person_id = tbl_benutzer.person_id AND
			tbl_benutzer.uid = tbl_mitarbeiter.mitarbeiter_uid AND
			tbl_lehreinheitmitarbeiter.mitarbeiter_uid = tbl_mitarbeiter.mitarbeiter_uid AND
			tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitmitarbeiter.lehreinheit_id AND
			tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id AND
			studiengang_kz = ".$db->db_add_param($studiengang_kz)."
			AND studiensemester_kurzbz = ".$db->db_add_param($semester_aktuell)." AND
			tbl_lehreinheitmitarbeiter.semesterstunden<>0 AND tbl_lehreinheitmitarbeiter.semesterstunden is not null
			AND	EXISTS (SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id=tbl_lehreinheit.lehreinheit_id)";
if($semester!='')
	$qry.=" AND semester=".$db->db_add_param($semester);

//Projektsbetreuungen
$qry.= " UNION
		 SELECT
		 	tbl_lehreinheit.*, tbl_person.vorname, tbl_person.nachname, tbl_person.titelpre,
		 	tbl_mitarbeiter.personalnummer, tbl_person.person_id, tbl_mitarbeiter.mitarbeiter_uid,
			tbl_mitarbeiter.fixangestellt,
			0 as stundensatz,
			0 as semesterstunden
		 FROM
		 	lehre.tbl_lehreinheit, lehre.tbl_projektarbeit, lehre.tbl_projektbetreuer,
		 	public.tbl_mitarbeiter, public.tbl_benutzer, lehre.tbl_lehrveranstaltung, public.tbl_person
		 WHERE
		 	tbl_mitarbeiter.mitarbeiter_uid = tbl_benutzer.uid AND
		 	tbl_benutzer.person_id = tbl_projektbetreuer.person_id AND
		 	tbl_projektarbeit.projektarbeit_id = tbl_projektbetreuer.projektarbeit_id AND
		 	tbl_projektarbeit.lehreinheit_id = tbl_lehreinheit.lehreinheit_id AND
		 	tbl_lehreinheit.studiensemester_kurzbz = ".$db->db_add_param($semester_aktuell)." AND
		 	tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id AND
		 	tbl_lehrveranstaltung.studiengang_kz = ".$db->db_add_param($studiengang_kz, FHC_INTEGER)." AND
		 	tbl_person.person_id = tbl_projektbetreuer.person_id";
if($semester!='')
	$qry.=" AND tbl_lehrveranstaltung.semester=".$db->db_add_param($semester, FHC_INTEGER);
$qry.=") as foo";
$qry.="	ORDER BY nachname, vorname, mitarbeiter_uid";

if($result = $db->db_query($qry))
{
	$zeile=3;
	$gesamtkosten = 0;
	$gesamtstunden = 0;
	$liste=array();

	while($row = $db->db_fetch_object($result))
	{
		//Gesamtstunden und Kosten ermitteln
		if(array_key_exists($row->mitarbeiter_uid, $liste))
		{
			$liste[$row->mitarbeiter_uid]['gesamtstunden'] = $liste[$row->mitarbeiter_uid]['gesamtstunden'] + $row->semesterstunden;
			$liste[$row->mitarbeiter_uid]['gesamtkosten'] = $liste[$row->mitarbeiter_uid]['gesamtkosten'] + ($row->semesterstunden*$row->stundensatz);
		}
		else
		{
			$liste[$row->mitarbeiter_uid]['gesamtstunden'] = $row->semesterstunden;
			$liste[$row->mitarbeiter_uid]['gesamtkosten'] = $row->semesterstunden*$row->stundensatz;
		}
		$liste[$row->mitarbeiter_uid]['personalnummer'] = $row->personalnummer;
		$liste[$row->mitarbeiter_uid]['titelpre'] = $row->titelpre;
		$liste[$row->mitarbeiter_uid]['fixangestellt'] = $row->fixangestellt;
		$liste[$row->mitarbeiter_uid]['vorname'] = $row->vorname;
		$liste[$row->mitarbeiter_uid]['nachname'] = $row->nachname;
	}

	//Betreuungen fuer Projektarbeiten
	foreach ($liste as $uid=>$arr)
	{
		$qry = "
			SELECT
				tbl_projektbetreuer.stunden, tbl_projektbetreuer.stundensatz
			FROM lehre.tbl_projektbetreuer, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung,
				public.tbl_benutzer, lehre.tbl_projektarbeit, campus.vw_student
			WHERE
				tbl_projektbetreuer.person_id=tbl_benutzer.person_id
				AND tbl_benutzer.uid=".$db->db_add_param($uid)."
				AND tbl_projektarbeit.projektarbeit_id=tbl_projektbetreuer.projektarbeit_id
				AND student_uid=vw_student.uid
				AND tbl_lehreinheit.lehreinheit_id=tbl_projektarbeit.lehreinheit_id
				AND tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($semester_aktuell)."
				AND tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id
				AND tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);
		if($semester!='')
			$qry.=" AND tbl_lehrveranstaltung.semester=".$db->db_add_param($semester, FHC_INTEGER);
		if($result = $db->db_query($qry))
		{
			while($row = $db->db_fetch_object($result))
			{
				$liste[$uid]['gesamtstunden'] = $liste[$uid]['gesamtstunden'] + $row->stunden;
				$liste[$uid]['gesamtkosten'] = $liste[$uid]['gesamtkosten'] + ($row->stunden*$row->stundensatz);
			}
		}
	}

	//Daten ausgeben
	foreach ($liste as $row)
	{
		$i=0;
		//Studiengang
		$worksheet->write($zeile,$i,$studiengang->kuerzel);
		//Personalnummer
		$worksheet->write($zeile,++$i,$row['personalnummer']);
		//Titel
		$worksheet->write($zeile,++$i,$row['titelpre']);
		//Vorname
		$worksheet->write($zeile,++$i,$row['vorname']);
		//Nachname
		$worksheet->write($zeile,++$i,$row['nachname']);
		//Fixangestellt
		$worksheet->write($zeile,++$i,($row['fixangestellt']=='t'?'Ja':'Nein'));
		//Stunden
		$worksheet->write($zeile,++$i,$row['gesamtstunden']);
		//Kosten
		$worksheet->writeNumber($zeile,++$i,$row['gesamtkosten'], $format_number);

		//Kosten zu den Gesamtkosten hinzurechnen
		$gesamtkosten = $gesamtkosten + $row['gesamtkosten'];
		$gesamtstunden = $gesamtstunden + $row['gesamtstunden'];
		$zeile++;
	}

	//Gesamtkosten und Gesamtstunden anzeigen
	$worksheet->writeNumber($zeile,6,$gesamtstunden, $format_bold);
	$worksheet->writeNumber($zeile,7,$gesamtkosten, $format_number_bold);
}

	$workbook->close();
?>
