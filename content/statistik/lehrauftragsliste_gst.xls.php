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
require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/Excel/excel.php');
require_once('../../include/studiengang.class.php');

if (!$conn=pg_pconnect(CONN_STRING))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!');

if(isset($_GET['studiengang_kz']) && is_numeric($_GET['studiengang_kz']))
	$studiengang_kz=$_GET['studiengang_kz'];
else 
	die('studiengangs_kz muss uebergeben werden');

$user = get_uid();
loadVariables($conn, $user);

//Studiengang laden
$studiengang = new studiengang($conn, $studiengang_kz);

// Creating a workbook
$workbook = new Spreadsheet_Excel_Writer();

// sending HTTP headers
$workbook->send("Lehrauftragsliste_GST_" . date("Y_m_d") . ".xls");

// Creating a worksheet
$worksheet =& $workbook->addWorksheet("Lehrauftragsliste");

//Formate Definieren
$format_bold =& $workbook->addFormat();
$format_bold->setBold();

$format_number =& $workbook->addFormat();
$format_number->setNumFormat('0,0.00');

$format_number_bold =& $workbook->addFormat();
$format_number_bold->setNumFormat('0,0.00');
$format_number_bold->setBold();

$i=0;

//Ueberschriften
$worksheet->write(0,$i,"Studiengang", $format_bold);
$worksheet->write(0,++$i,"Personalnr", $format_bold);
$worksheet->write(0,++$i,"Titel", $format_bold);
$worksheet->write(0,++$i,"Vorname", $format_bold);
$worksheet->write(0,++$i,"Familienname", $format_bold);
$worksheet->write(0,++$i,"Stunden", $format_bold);
$worksheet->write(0,++$i,"Kosten", $format_bold);

//Daten holen
$qry = "SELECT vw_lehreinheit.*, tbl_person.vorname, tbl_person.nachname, tbl_person.titelpre, tbl_mitarbeiter.personalnummer, tbl_person.person_id
		FROM campus.vw_lehreinheit, public.tbl_mitarbeiter, public.tbl_benutzer, public.tbl_person WHERE 
		tbl_person.person_id = tbl_benutzer.person_id AND tbl_benutzer.uid=tbl_mitarbeiter.mitarbeiter_uid AND
		vw_lehreinheit.mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid AND
		studiengang_kz='$studiengang_kz' AND studiensemester_kurzbz='$semester_aktuell' 
		ORDER BY nachname, vorname, mitarbeiter_uid";

if($result = pg_query($conn, $qry))
{
	$zeile=1;
	$gesamtkosten = 0;
	$liste=array();
	
	while($row=pg_fetch_object($result))
	{
		//Gesamtstunden und Kosten ermitteln
		if(array_key_exists($row->mitarbeiter_uid, $liste))
		{
			$liste[$row->mitarbeiter_uid]['gesamtstunden'] = $liste[$row->mitarbeiter_uid]['gesamtstunden'] + $row->semesterstunden;
			$liste[$row->mitarbeiter_uid]['gesamtkosten'] = $liste[$row->mitarbeiter_uid]['gesamtkosten'] + ($row->semesterstunden*$row->stundensatz*$row->faktor);
		}
		else 
		{
			$liste[$row->mitarbeiter_uid]['gesamtstunden'] = $row->semesterstunden;
			$liste[$row->mitarbeiter_uid]['gesamtkosten'] = $row->semesterstunden*$row->stundensatz*$row->faktor;
		}
		$liste[$row->mitarbeiter_uid]['personalnummer'] = $row->personalnummer;
		$liste[$row->mitarbeiter_uid]['titelpre'] = $row->titelpre;
		$liste[$row->mitarbeiter_uid]['vorname'] = $row->vorname;
		$liste[$row->mitarbeiter_uid]['nachname'] = $row->nachname;
	}
	
	//Betreuungen fuer Projektarbeiten
	foreach ($liste as $uid=>$arr)
	{
		$qry = "SELECT tbl_projektbetreuer.faktor, tbl_projektbetreuer.stunden, tbl_projektbetreuer.stundensatz
	        FROM lehre.tbl_projektbetreuer, lehre.tbl_lehreinheit, lehre.tbl_lehrfach, lehre.tbl_lehrveranstaltung, 
	               public.tbl_benutzer, lehre.tbl_projektarbeit, campus.vw_student 
	        WHERE tbl_projektbetreuer.person_id=tbl_benutzer.person_id AND tbl_benutzer.uid='$uid' AND 
	              tbl_projektarbeit.projektarbeit_id=tbl_projektbetreuer.projektarbeit_id AND student_uid=vw_student.uid
	              AND tbl_lehreinheit.lehreinheit_id=tbl_projektarbeit.lehreinheit_id AND tbl_lehreinheit.lehrfach_id=tbl_lehrfach.lehrfach_id AND
	              tbl_lehreinheit.studiensemester_kurzbz='$semester_aktuell' AND tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id AND
	              tbl_lehrveranstaltung.studiengang_kz='$studiengang_kz'";
		if($result = pg_query($conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$liste[$uid]['gesamtstunden'] = $liste[$uid]['gesamtstunden'] + $row->stunden;
				$liste[$uid]['gesamtkosten'] = $liste[$uid]['gesamtkosten'] + ($row->stunden*$row->stundensatz*$row->faktor);
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
		//Stunden
		$worksheet->write($zeile,++$i,$row['gesamtstunden']);
		//Kosten
		$worksheet->writeNumber($zeile,++$i,$row['gesamtkosten'], $format_number);
		
		//Kosten zu den Gesamtkosten hinzurechnen
		$gesamtkosten = $gesamtkosten + $row['gesamtkosten'];
		$zeile++;
	}
	
	//Gesamtkosten anzeigen
	$worksheet->writeNumber($zeile,6,$gesamtkosten, $format_number_bold);
}
	$workbook->close();
?>