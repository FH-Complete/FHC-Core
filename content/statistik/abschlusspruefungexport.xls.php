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
 * Exportiert die Abschlusspruefungen in ein Excel File.
 * Die zu exportierenden Spalten werden per GET uebergeben.
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/datum.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/Excel/excel.php');
require_once('../../include/benutzerberechtigung.class.php');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$db = new basis_db();
$user = get_uid();
$datum_obj = new datum();
loadVariables($user);

/**
 * Schreibt eine Spalte ins Excel und speichert die maximale Spaltenbreite
 *
 * @param int $zeile Zeile im Excel.
 * @param int $i Spalte im Excel.
 * @param string $content Inhalt.
 * @return void
 */
function writecol($zeile, $i, $content)
{
	global $worksheet, $maxlength;
	$worksheet->write($zeile, $i, $content);
	if(mb_strlen($content) > $maxlength[$i])
		$maxlength[$i] = mb_strlen($content);
}

//Parameter holen
$studiengang_kz = isset($_GET['studiengang_kz'])?$_GET['studiengang_kz']:'';
$semester = isset($_GET['semester'])?$_GET['semester']:'';
$studiensemester_kurzbz = isset($_GET['studiensemester_kurzbz'])?$_GET['studiensemester_kurzbz']:'';

if($studiengang_kz == '')
	die('studiengang_kz is not set');
if($studiensemester_kurzbz == '')
	die('studiensemester_kurzbz is not set');

$maxlength = array();
$zeile = 1;

if(!$rechte->isBerechtigt('student/stammdaten', $studiengang_kz, 's'))
	die($rechte->errormsg);

// Creating a workbook
$workbook = new Spreadsheet_Excel_Writer();

// sending HTTP headers
$workbook->send("Abschlusspruefung". "_".date("d_m_Y").".xls");
$workbook->setVersion(8);
// Creating a worksheet
$worksheet =& $workbook->addWorksheet("Abschlusspruefung");
$worksheet->setInputEncoding('utf-8');

$format_bold =& $workbook->addFormat();
$format_bold->setBold();

$format_title =& $workbook->addFormat();
$format_title->setBold();
// let's merge
$format_title->setAlign('merge');

$stsem = new studiensemester();
$stsem->load($studiensemester_kurzbz);

//Zeilenueberschriften ausgeben
$headline = array('Titelpre', 'Vorname', 'Nachname', 'Titelpost', 'Vorsitz', 'Pruefer1', 'Pruefer2', 'Pruefer3',
				'Abschlussbeurteilung', 'Typ', 'Datum', 'Sponsion', 'Anmerkung');

$i = 0;
foreach ($headline as $title)
{
	$worksheet->write(0, $i, $title, $format_bold);
		$maxlength[$i] = mb_strlen($title);
	$i++;
}

// Daten holen
$qry = "SELECT
			titelpre, vorname, nachname, titelpost,
			(SELECT COALESCE(titelpre,'') || ' ' || COALESCE(vorname,'') || ' ' || COALESCE(nachname,'')
				|| ' ' || COALESCE(titelpost,'') FROM public.tbl_person JOIN public.tbl_benutzer USING(person_id)
			 WHERE uid=vorsitz) as vorsitz,
			(SELECT COALESCE(titelpre,'') || ' ' || COALESCE(vorname,'') || ' ' || COALESCE(nachname,'')
				|| ' ' || COALESCE(titelpost,'') FROM public.tbl_person WHERE person_id=pruefer1) as pruefer1,
			(SELECT COALESCE(titelpre,'') || ' ' || COALESCE(vorname,'') || ' ' || COALESCE(nachname,'')
				|| ' ' || COALESCE(titelpost,'') FROM public.tbl_person WHERE person_id=pruefer2) as pruefer2,
			(SELECT COALESCE(titelpre,'') || ' ' || COALESCE(vorname,'') || ' ' || COALESCE(nachname,'') || ' '
				|| COALESCE(titelpost,'') FROM public.tbl_person WHERE person_id=pruefer3) as pruefer3,
			(SELECT bezeichnung FROM lehre.tbl_abschlussbeurteilung
				WHERE tbl_abschlussbeurteilung.abschlussbeurteilung_kurzbz
					= tbl_abschlusspruefung.abschlussbeurteilung_kurzbz) as bezeichnung,
					tbl_pruefungstyp.beschreibung, datum, sponsion, tbl_abschlusspruefung.anmerkung
		FROM
			lehre.tbl_abschlusspruefung, public.tbl_studentlehrverband, public.tbl_benutzer, public.tbl_person,
			lehre.tbl_pruefungstyp
		WHERE
			tbl_abschlusspruefung.student_uid=public.tbl_studentlehrverband.student_uid AND
			tbl_studentlehrverband.studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." AND
			tbl_studentlehrverband.studiengang_kz=".$db->db_add_param($studiengang_kz)." AND
			tbl_benutzer.uid = tbl_abschlusspruefung.student_uid AND
			tbl_person.person_id = tbl_benutzer.person_id AND
			tbl_abschlusspruefung.pruefungstyp_kurzbz = tbl_pruefungstyp.pruefungstyp_kurzbz
		";
if ($semester != '')
	$qry .= " AND tbl_studentlehrverband.semester=".$db->db_add_param($semester);
$qry .= ' ORDER BY nachname, vorname';
$zeile = 1;
if ($db->db_query($qry))
{
	while ($row = $db->db_fetch_object())
	{
		$i = 0;

		writecol($zeile, $i++, $row->titelpre);
		writecol($zeile, $i++, $row->vorname);
		writecol($zeile, $i++, $row->nachname);
		writecol($zeile, $i++, $row->titelpost);
		writecol($zeile, $i++, $row->vorsitz);
		writecol($zeile, $i++, $row->pruefer1);
		writecol($zeile, $i++, $row->pruefer2);
		writecol($zeile, $i++, $row->pruefer3);
		writecol($zeile, $i++, $row->bezeichnung);
		writecol($zeile, $i++, $row->beschreibung);
		writecol($zeile, $i++, $row->datum);
		writecol($zeile, $i++, $row->sponsion);
		writecol($zeile, $i++, $row->anmerkung);

		$zeile++;
	}
}
else
	die('Fehler bei Datenbankabfrage');

//Die Breite der Spalten setzen
foreach($maxlength as $i => $breite)
	$worksheet->setColumn($i, $i, $breite + 2);

$workbook->close();
