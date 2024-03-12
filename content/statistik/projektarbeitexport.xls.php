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
 * Exportiert die Projektarbeiten inklusive deren Betreuern
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/datum.class.php');
require_once('../../include/Excel/excel.php');

$db = new basis_db();
$user = get_uid();
$datum_obj = new datum();
loadVariables($user);

//Parameter holen
$studiengang_kz = isset($_GET['studiengang_kz'])?$_GET['studiengang_kz']:'';
$semester = isset($_GET['semester'])?$_GET['semester']:'';
$verband = isset($_GET['verband'])?$_GET['verband']:'';
$gruppe = isset($_GET['gruppe'])?$_GET['gruppe']:'';
$gruppe_kurzbz = isset($_GET['gruppe_kurzbz'])?$_GET['gruppe_kurzbz']:'';
$studiensemester_kurzbz = isset($_GET['studiensemester_kurzbz'])?$_GET['studiensemester_kurzbz']:'';

$maxlength = array();
$zeile = 1;

// Creating a workbook
$workbook = new Spreadsheet_Excel_Writer();
$workbook->setVersion(8);

// sending HTTP headers
$workbook->send("Projektarbeit". "_" . date("d_m_Y") . ".xls");

// Creating a worksheet
$worksheet =& $workbook->addWorksheet("Studenten");
$worksheet->setInputEncoding('utf-8');

$format_bold =& $workbook->addFormat();
$format_bold->setBold();

$format_title =& $workbook->addFormat();
$format_title->setBold();
// let's merge
$format_title->setAlign('merge');

//Zeilenueberschriften ausgeben
$headline = array('Typ der Projektarbeit','Titel der Projektarbeit','Student',
                'Note','Punkte','Beginn','Ende','Freigegeben','Gesperrt bis','Themenbereich',
                'Anmerkung','Projektarbeit ID');

$i = 0;
foreach ($headline as $title)
{
	$worksheet->write(0,$i,$title, $format_bold);
		$maxlength[$i] = mb_strlen($title);
	$i++;
}

// Daten holen
$qry = "SELECT
			tbl_projekttyp.bezeichnung, titel,
			trim(COALESCE(titelpre,'') || ' ' || COALESCE(vorname,'')
			|| ' ' || COALESCE(nachname,'') || ' ' || COALESCE(titelpost,'')),
			(SELECT anmerkung FROM lehre.tbl_note WHERE note=tbl_projektarbeit.note) as anmerkung, punkte, beginn,
			ende, CASE WHEN freigegeben THEN 'Ja' ELSE 'Nein' END, gesperrtbis, themenbereich,
			tbl_projektarbeit.anmerkung, projektarbeit_id
		FROM
			lehre.tbl_projektarbeit, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung,
			public.tbl_benutzer, public.tbl_person, lehre.tbl_projekttyp
		WHERE
			tbl_projektarbeit.lehreinheit_id = tbl_lehreinheit.lehreinheit_id AND
			tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id AND
			tbl_projektarbeit.student_uid = tbl_benutzer.uid AND
			tbl_benutzer.person_id = tbl_person.person_id AND
			tbl_projektarbeit.projekttyp_kurzbz = tbl_projekttyp.projekttyp_kurzbz AND
			tbl_lehreinheit.studiensemester_kurzbz = ".$db->db_add_param($studiensemester_kurzbz)." AND
			tbl_lehrveranstaltung.studiengang_kz = ".$db->db_add_param($studiengang_kz)." AND
			tbl_projektarbeit.projekttyp_kurzbz IN ('Bachelor','Diplom','Projekt')";

if ($semester != '')
	$qry .= " AND tbl_lehrveranstaltung.semester=".$db->db_add_param($semester);

//echo $qry;
$zeile=1;
if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_array($result, null, PGSQL_BOTH))
	{
		$zeile++;
		$i = 0;

		//Projektarbeit
		foreach ($row as $idx => $content)
		{
			if (is_numeric($idx))
			{
				$worksheet->write($zeile, $i, $content);
				if (mb_strlen($content) > $maxlength[$i])
					$maxlength[$i] = mb_strlen($content);
				$i++;
			}
		}
		$zeile++;

		//Betreuer
		$qry_betreuer = "
			SELECT
				betreuerart_kurzbz, COALESCE(titelpre,'') || ' ' || COALESCE(vorname,'')
				|| ' ' || COALESCE(nachname,'') || ' ' || COALESCE(titelpost,''),
				tbl_note.anmerkung, name, punkte, stunden, stundensatz
			FROM
				(lehre.tbl_projektbetreuer JOIN tbl_person USING(person_id))
				LEFT JOIN lehre.tbl_note USING(note)
			WHERE
				projektarbeit_id = ".$db->db_add_param($row['projektarbeit_id']);

		if ($result_betreuer = $db->db_query($qry_betreuer))
		{
			if ($db->db_num_rows($result_betreuer) > 0)
			{
				$headline = array('Betreuerart','Betreuer','Note','Name','Punkte','Stunden','Stundensatz');

				$i = 1;

				foreach ($headline as $title)
				{
					$worksheet->write($zeile,$i,$title, $format_bold);
					if (mb_strlen($title) > $maxlength[$i])
						$maxlength[$i] = mb_strlen($title);
					$i++;
				}

				$zeile++;
				while ($row_betreuer = $db->db_fetch_array($result_betreuer, null, PGSQL_BOTH))
				{
					$i = 1;

					foreach ($row_betreuer as $idx => $content)
					{
						if (is_numeric($idx))
						{
							$worksheet->write($zeile, $i, $content);
							if (mb_strlen($content) > $maxlength[$i])
								$maxlength[$i] = mb_strlen($content);
							$i++;
						}
					}
					$zeile++;
				}
			}
		}

	}
}
//Die Breite der Spalten setzen
foreach ($maxlength as $i => $breite)
	$worksheet->setColumn($i, $i, $breite + 2);

$workbook->close();

?>
