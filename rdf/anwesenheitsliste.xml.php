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

if(isset($_GET['typ']) && $_GET['typ'] == 'lehreinheit')
{
	if(isset($_GET['lehreinheit']) && isset($_GET['datum']) && isset($_GET['lv']))
	{
		$lehreinheit_id = $_GET['lehreinheit'];
		$datum = $_GET['datum'];
		$lv = $_GET['lv'];
	}
	else 
		die('Die ID der Lehreinheit, die ID der Lehrveranstaltung und das Datum muessen uebergeben werden');

	echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>";
	echo '<anwesenheitslisten>';

	$db = new basis_db();
	
	// Daten der Lehreinheit ermitteln
	$qry = "SELECT le.lehrveranstaltung_id, lv.lvnr, bezeichnung, stundenblockung, sp.ort_kurzbz, datum, beginn, ende "
		. "FROM lehre.tbl_lehreinheit le "
		. "JOIN lehre.tbl_lehrveranstaltung lv ON lv.lehrveranstaltung_id = le.lehrveranstaltung_id "
		. "JOIN lehre.tbl_stundenplan sp ON sp.unr = le.unr "
		. "JOIN lehre.tbl_stunde stu ON stu.stunde = sp.stunde "
		. "WHERE le.lehreinheit_id = " . $db->db_add_param($lehreinheit_id) . " "
		. "AND sp.datum = " . $db->db_add_param($datum);

	if($db->db_query($qry))
	{
		while($row = $db->db_fetch_object())
		{
			if(empty($row))
				die("Lehreinheit $lehreinheit_id am $datum nicht gefunden");
			
			// Ausgabe der Lehrveranstaltung
			echo "\n		<lehreinheit>";
			echo "\n			<bezeichnung><![CDATA[".$row->bezeichnung."]]></bezeichnung>";
			echo "\n			<kuerzel><![CDATA[".$row->lvnr."]]></kuerzel>";
			echo "\n			<stundenblockung><![CDATA[".$row->stundenblockung."]]></stundenblockung>";
			echo "\n			<ort><![CDATA[".$row->ort_kurzbz."]]></ort>";
			echo "\n			<datum><![CDATA[".date('d.m.Y', strtotime($row->datum))."]]></datum>";
			echo "\n			<beginn><![CDATA[".mb_substr($row->beginn, 0, 5)."]]></beginn>";
			echo "\n			<ende><![CDATA[".mb_substr($row->ende, 0, 5)."]]></ende>";
			echo "\n		</lehreinheit>";
		}
	}
	
	// Daten der Vortragenden ermitteln
	$qry = "SELECT vorname, nachname, titelpre, titelpost "
		. "FROM lehre.tbl_lehreinheitmitarbeiter lema "
		. "JOIN public.tbl_benutzer be ON be.uid = lema.mitarbeiter_uid "
		. "JOIN public.tbl_person pe ON pe.person_id = be.person_id "
		. "WHERE lehreinheit_id = " . $db->db_add_param($lehreinheit_id);
	
	if($db->db_query($qry))
	{
		while($row = $db->db_fetch_object())
		{
			// Ausgabe der Vortragenden
			echo "\n		<vortragender>";
			echo "\n			<vorname><![CDATA[".$row->vorname."]]></vorname>";
			echo "\n			<nachname><![CDATA[".$row->nachname."]]></nachname>";
			echo "\n			<titelpre><![CDATA[".$row->titelpre."]]></titelpre>";
			echo "\n			<titelpost><![CDATA[".$row->titelpost."]]></titelpost>";
			echo "\n		</vortragender>";
		}
	}
		

	// Daten der Teilnehmer ermitteln
	$qry = "SELECT vorname, nachname, titelpre, titelpost "
		. "FROM campus.vw_student_lehrveranstaltung stlv "
		. "JOIN public.tbl_benutzer be ON be.uid = stlv.uid "
		. "JOIN public.tbl_person pe ON pe.person_id = be.person_id "
		. "WHERE stlv.lehreinheit_id = " . $db->db_add_param($lehreinheit_id);
	
	if($db->db_query($qry))
	{
		while($row = $db->db_fetch_object())
		{
			// Ausgabe der Teilnehmer
			echo "\n		<teilnehmer>";
			echo "\n			<vorname><![CDATA[".$row->vorname."]]></vorname>";
			echo "\n			<nachname><![CDATA[".$row->nachname."]]></nachname>";
			echo "\n			<titelpre><![CDATA[".$row->titelpre."]]></titelpre>";
			echo "\n			<titelpost><![CDATA[".$row->titelpost."]]></titelpost>";
			echo "\n		</teilnehmer>";
		}
	}

	echo '</anwesenheitslisten>';
}
else if(isset($_GET['typ']) && $_GET['typ'] == 'studiengang')
{
	
}
else
	die("Der gewuenschte Typ muss angegeben werden");

?>