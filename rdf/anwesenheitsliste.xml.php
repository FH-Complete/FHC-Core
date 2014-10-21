<?php
/* Copyright (C) 2014 Technikum-Wien
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
 * Authors: Nikolaus Krondraf <nikolaus.krondraf@technikum-wien.at>.
 */
/**
 * Erstellt das XML fuer die Anwesenheitsliste
 */
// content type setzen
header("Content-type: application/xhtml+xml");
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/basis_db.class.php');
require_once('../include/ean13.function.php');

// Optionen abfragen
isset($_GET['von']) ? $von = $_GET['von'] : $von = date('Y-m-d', time());
isset($_GET['bis']) ? $bis = $_GET['bis'] : $bis = $von;
isset($_GET['stg_kz']) ? $studiengang = $_GET['stg_kz'] : $studiengang = NULL;
isset($_GET['semester']) ? $semester = $_GET['semester'] : $semester = NULL;
isset($_GET['lehreinheit']) ? $lehreinheit = $_GET['lehreinheit'] : $lehreinheit = NULL;

$db = new basis_db();
$data = array();

if(!$studiengang)
	die('Die ID des Studiengangs muss uebergeben werden');

// Daten der Lehreinheiten ermitteln
$qry = "SELECT le.lehreinheit_id, le.lehrveranstaltung_id, lv.lvnr, lv.bezeichnung AS lvbez, stg.bezeichnung AS stgbez, "
	. "sp.ort_kurzbz, datum, beginn, ende "
	. "FROM lehre.tbl_lehreinheit le "
	. "JOIN lehre.tbl_lehrveranstaltung lv ON lv.lehrveranstaltung_id = le.lehrveranstaltung_id "
	. "JOIN public.tbl_studiengang stg ON stg.studiengang_kz = lv.studiengang_kz "
	. "JOIN lehre.tbl_stundenplan sp ON sp.unr = le.unr "
	. "JOIN lehre.tbl_stunde stu ON stu.stunde = sp.stunde "
	. "WHERE stg.studiengang_kz = " . $db->db_add_param($studiengang) . " "
	. "AND (sp.datum <= " . $db->db_add_param($von) . "::DATE AND sp.datum >= " . $db->db_add_param($bis) . "::DATE) ";

// Optionen zu Query hinzufügen
if($lehreinheit)
	$qry .= " AND le.lehreinheit_id = " . $db->db_add_param($lehreinheit);
if($semester)
	$qry .= " AND lv.semester = " . $db->db_add_param($semester);

$qry .= " ORDER BY stu.stunde ASC";

if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{
		if(empty($row))
			die("Lehreinheit $lehreinheit am $von nicht gefunden");
		
		$data[$row->lehreinheit_id]['einheiten'][] = $row;
	}
}
		
foreach($data as $key => $value)
{
	// Daten der Vortragenden ermitteln
	$qry = "SELECT vorname, nachname, titelpre, titelpost "
		. "FROM lehre.tbl_lehreinheitmitarbeiter lema "
		. "JOIN public.tbl_benutzer be ON be.uid = lema.mitarbeiter_uid "
		. "JOIN public.tbl_person pe ON pe.person_id = be.person_id "
		. "WHERE lehreinheit_id = " . $db->db_add_param($key);

	if($db->db_query($qry))
	{
		while($row = $db->db_fetch_object())
		{
			$data[$key]['vortragende'][] = $row;
		}
	}


	// Daten der Studenten ermitteln
	$qry = "SELECT pe.person_id, vorname, nachname, titelpre, titelpost, note, "
		. "get_rolle_prestudent(pre.prestudent_id, null) as laststatus "
		. "FROM campus.vw_student_lehrveranstaltung stlv "
		. "JOIN public.tbl_benutzer be ON be.uid = stlv.uid "
		. "JOIN public.tbl_person pe ON pe.person_id = be.person_id "
		. "JOIN public.tbl_prestudent pre ON (pre.person_id = pe.person_id AND pre.studiengang_kz = " . $db->db_add_param($studiengang) . ") "
		. "LEFT JOIN lehre.tbl_zeugnisnote zn ON (zn.lehrveranstaltung_id = stlv.lehrveranstaltung_id AND zn.student_uid = stlv.uid) "
		. "WHERE stlv.lehreinheit_id = " . $db->db_add_param($key) . " "
		. "ORDER BY nachname ASC";

	if($db->db_query($qry))
	{
		while($row = $db->db_fetch_object())
		{
			$data[$key]['studenten'][] = $row;
		}
	}
}

// AUSGABE
echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>";
echo "<anwesenheitslisten>";

foreach($data as $lehreinheit_id => $value)
{
	echo "<anwesenheitsliste>";
	
	// Barcode erstellen
	$paddedLehreinheitId = str_pad($lehreinheit_id, 6, "0", STR_PAD_LEFT);
	$convertableString = date('ymd', strtotime($value['einheiten'][0]->datum)) . $paddedLehreinheitId;
	$barcode = ean13($convertableString);

	// Ausgabe der Lehrveranstaltung
	echo "\n		<lehreinheit>";
	echo "\n			<lehreinheit_id><![CDATA[".$value['einheiten'][0]->lehreinheit_id."]]></lehreinheit_id>";
	echo "\n			<studiengang><![CDATA[".$value['einheiten'][0]->stgbez."]]></studiengang>";
	echo "\n			<bezeichnung><![CDATA[".$value['einheiten'][0]->lvbez."]]></bezeichnung>";
	echo "\n			<barcode><![CDATA[".ean13($convertableString)."]]></barcode>";
	echo "\n			<kuerzel><![CDATA[".$value['einheiten'][0]->lvnr."]]></kuerzel>";
	echo "\n			<einheiten><![CDATA[".count($value['einheiten'])."]]></einheiten>";
	echo "\n			<ort><![CDATA[".$value['einheiten'][0]->ort_kurzbz."]]></ort>";
	echo "\n			<datum><![CDATA[".date('d.m.Y', strtotime($value['einheiten'][0]->datum))."]]></datum>";
	echo "\n			<beginn><![CDATA[".mb_substr($value['einheiten'][0]->beginn, 0, 5)."]]></beginn>";
	echo "\n			<ende><![CDATA[".mb_substr($value['einheiten'][count($value['einheiten']) - 1]->ende, 0, 5)."]]></ende>";
	echo "\n		</lehreinheit>";
	
	// Ausgabe der Vortragenden
	echo "<vortragende>";
	foreach($value['vortragende'] as $vortragender)
	{
		echo "\n		<vortragender>";
		echo "\n			<vorname><![CDATA[".$vortragender->vorname."]]></vorname>";
		echo "\n			<nachname><![CDATA[".$vortragender->nachname."]]></nachname>";
		echo "\n			<titelpre><![CDATA[".$vortragender->titelpre."]]></titelpre>";
		echo "\n			<titelpost><![CDATA[".$vortragender->titelpost."]]></titelpost>";
		echo "\n		</vortragender>";
	}
	echo "</vortragende>";
	
	// Ausgabe der Studenten
	echo "<studenten>";
	foreach($value['studenten'] as $student)
	{
		// Barcode erstellen
		$paddedPersonId = str_pad($student->person_id, 12, "0", STR_PAD_LEFT);
		$barcode = ean13($paddedPersonId);

		echo "\n		<student>";
		echo "\n			<barcode><![CDATA[".$barcode."]]></barcode>";
		echo "\n			<vorname><![CDATA[".$student->vorname."]]></vorname>";
		echo "\n			<nachname><![CDATA[".$student->nachname."]]></nachname>";
		echo "\n			<titelpre><![CDATA[".$student->titelpre."]]></titelpre>";
		echo "\n			<titelpost><![CDATA[".$student->titelpost."]]></titelpost>";
		echo "\n			<note><![CDATA[".$student->note."]]></note>";
		echo "\n			<status><![CDATA[".$student->laststatus."]]></status>";
		echo "\n		</student>";
	}
	echo "</studenten>";
	echo "</anwesenheitsliste>";
}

echo "</anwesenheitslisten>";

?>