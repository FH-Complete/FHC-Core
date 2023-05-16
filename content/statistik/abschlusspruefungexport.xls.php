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
			tbl_pruefungstyp.pruefungstyp_kurzbz , tbl_person.titelpre, tbl_person.vorname, tbl_person.nachname, tbl_person.titelpost,
			concat_ws(' ', vorsitz_person.titelpre, vorsitz_person.vorname, vorsitz_person.nachname, vorsitz_person.titelpost) as vorsitz,
			concat_ws(' ', erst_pruefer.titelpre, erst_pruefer.vorname, erst_pruefer.nachname, erst_pruefer.titelpost) as pruefer1,
			concat_ws(' ', zweit_pruefer.titelpre, zweit_pruefer.vorname, zweit_pruefer.nachname, zweit_pruefer.titelpost) as pruefer2,
			concat_ws(' ', dritt_pruefer.titelpre, dritt_pruefer.vorname, dritt_pruefer.nachname, dritt_pruefer.titelpost) as pruefer3,
			tbl_abschlussbeurteilung.bezeichnung,
			tbl_pruefungstyp.beschreibung, datum, sponsion, tbl_abschlusspruefung.anmerkung
		FROM
			lehre.tbl_abschlusspruefung
			JOIN public.tbl_prestudent USING (prestudent_id)
			JOIN public.tbl_person USING (person_id)
			JOIN public.tbl_benutzer ON tbl_person.person_id = tbl_benutzer.person_id
			JOIN public.tbl_studentlehrverband ON uid = tbl_studentlehrverband.student_uid AND tbl_prestudent.studiengang_kz = tbl_studentlehrverband.studiengang_kz
			JOIN lehre.tbl_pruefungstyp USING (pruefungstyp_kurzbz)
			LEFT JOIN lehre.tbl_abschlussbeurteilung USING (abschlussbeurteilung_kurzbz)
			LEFT JOIN public.tbl_benutzer vorsitz_benutzer ON vorsitz_benutzer.uid = tbl_abschlusspruefung.vorsitz
			LEFT JOIN public.tbl_person vorsitz_person ON vorsitz_benutzer.person_id = vorsitz_person.person_id
		    LEFT JOIN public.tbl_person erst_pruefer ON erst_pruefer.person_id = tbl_abschlusspruefung.pruefer1
		    LEFT JOIN public.tbl_person zweit_pruefer ON zweit_pruefer.person_id = tbl_abschlusspruefung.pruefer2
		    LEFT JOIN public.tbl_person dritt_pruefer ON dritt_pruefer.person_id = tbl_abschlusspruefung.pruefer3
		WHERE
			tbl_studentlehrverband.studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." AND
			tbl_studentlehrverband.studiengang_kz=".$db->db_add_param($studiengang_kz)."
		";
if ($semester != '')
	$qry .= " AND tbl_studentlehrverband.semester=".$db->db_add_param($semester);
$qry .= ' ORDER BY tbl_person.nachname, tbl_person.vorname';
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
