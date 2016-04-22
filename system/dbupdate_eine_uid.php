<?php
/* Copyright (C) 2015 FH Technikum-Wien
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
 * Authors: Andreas Moik <moik@technikum-wien.at>,
 *
 * Beschreibung:
 * Dieses Skript prueft die Datenbank auf Aenderungen bezueglich eine_uid, dabei werden fehlende Attribute angelegt.
 */
require_once('../config/system.config.inc.php');
require_once('../include/basis_db.class.php');
$db = new basis_db();
echo '<html>
<head>
	<title>CS-EineUid</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="../skin/vilesci.css" type="text/css" />
</head>
<body>';

$all_tables_to_update =
array
(
	array("schema" => "bis",    "name" => "tbl_bisio",                "from" => "student_uid", "to" => "prestudent_id", "datatype" => "int",         "newTarget" => "tbl_prestudent", "newTargetSchema" => "public", "pickDataFrom" => "tbl_student",  "pickDataFromCol" => "student_uid", "constraint" => "SET NOT NULL"),
	array("schema" => "campus", "name" => "tbl_lvgesamtnote",         "from" => "student_uid", "to" => "prestudent_id", "datatype" => "int",         "newTarget" => "tbl_prestudent", "newTargetSchema" => "public", "pickDataFrom" => "tbl_student",  "pickDataFromCol" => "student_uid", "constraint" => "SET NOT NULL"),
	array("schema" => "campus", "name" => "tbl_studentbeispiel",      "from" => "student_uid", "to" => "uid",           "datatype" => "varchar(32)", "newTarget" => "tbl_benutzer",   "newTargetSchema" => "public", "pickDataFrom" => "tbl_benutzer", "pickDataFromCol" => "uid"        , "constraint" => "SET NOT NULL"),
	array("schema" => "campus", "name" => "tbl_studentuebung",        "from" => "student_uid", "to" => "uid",           "datatype" => "varchar(32)", "newTarget" => "tbl_benutzer",   "newTargetSchema" => "public", "pickDataFrom" => "tbl_benutzer", "pickDataFromCol" => "uid"        , "constraint" => "SET NOT NULL"),
	array("schema" => "campus", "name" => "tbl_legesamtnote",         "from" => "student_uid", "to" => "prestudent_id", "datatype" => "int",         "newTarget" => "tbl_prestudent", "newTargetSchema" => "public", "pickDataFrom" => "tbl_student",  "pickDataFromCol" => "student_uid", "constraint" => "SET NOT NULL"),
	array("schema" => "lehre",  "name" => "tbl_abschlusspruefung",    "from" => "student_uid", "to" => "prestudent_id", "datatype" => "int",         "newTarget" => "tbl_prestudent", "newTargetSchema" => "public", "pickDataFrom" => "tbl_student",  "pickDataFromCol" => "student_uid", "constraint" => "SET NOT NULL"),
	array("schema" => "public", "name" => "tbl_studentlehrverband",   "from" => "student_uid", "to" => "prestudent_id", "datatype" => "int",         "newTarget" => "tbl_prestudent", "newTargetSchema" => "public", "pickDataFrom" => "tbl_student",  "pickDataFromCol" => "student_uid", "constraint" => "SET NOT NULL"),
	array("schema" => "lehre",  "name" => "tbl_projektarbeit",        "from" => "student_uid", "to" => "prestudent_id", "datatype" => "int",         "newTarget" => "tbl_prestudent", "newTargetSchema" => "public", "pickDataFrom" => "tbl_student",  "pickDataFromCol" => "student_uid", "constraint" => "SET NOT NULL"),
	array("schema" => "fue",    "name" => "tbl_ressource",            "from" => "student_uid", "to" => "uid",           "datatype" => "varchar(32)", "newTarget" => "tbl_benutzer",   "newTargetSchema" => "public", "pickDataFrom" => "tbl_benutzer", "pickDataFromCol" => "uid"        , "constraint" => ""),
	array("schema" => "lehre",  "name" => "tbl_pruefung",             "from" => "student_uid", "to" => "prestudent_id", "datatype" => "int",         "newTarget" => "tbl_prestudent", "newTargetSchema" => "public", "pickDataFrom" => "tbl_student",  "pickDataFromCol" => "student_uid", "constraint" => "SET NOT NULL"),
);

if(!isset($_POST["action"]))
{
	$needed = false;
	foreach($all_tables_to_update as $t)
	{
		if(checkForUpdates($db, $t))
			$needed = true;
	}



	if($needed)
	{
		echo "<h2 style='color:red;'>ACHTUNG!</h2>";
		echo "<h3>Folgendes wird geändert:</h3>";
		echo "<p>";

		foreach($all_tables_to_update as $t)
			describeOneChange($db, $t);

		echo "</p>";
		echo "
		<form action='dbupdate_eine_uid.php' method='POST' name='startform'>
			<input type='submit' value='Starten' name='action'>
		</form>";
	}
	else
	{
		echo "<h2>Es sind keine Änderungen vorzunehmen!</h2>";
	}
}
else if($_POST["action"] == "Starten")
{

	echo '<H1>Systemcheck!</H1>';
	echo '<H2>DB-Updates!</H2>';


	// *** Pruefung und hinzufuegen der neuen Attribute und Tabellen
	echo '<H2>Pruefe Tabellen und Attribute!</H2>';


	//********************************DROP ALL VIEWS********************************
	//bis.vw_bisio
	if($result = @$db->db_query("SELECT 1 FROM bis.vw_bisio LIMIT 1;"))
	{
		if(!$db->db_query("DROP VIEW bis.vw_bisio"))
		{
			echo "<p>Could not DROP view bis.vw_bisio: " . $create_view_qry."</p>";
		}
	}

	//campus.vw_student_lehrveranstaltung
	if($result = @$db->db_query("SELECT 1 FROM campus.vw_student_lehrveranstaltung LIMIT 1;"))
	{
		if(!$db->db_query("DROP VIEW campus.vw_student_lehrveranstaltung"))
		{
			echo "<p>Could not DROP view campus.vw_student_lehrveranstaltung: " . $create_view_qry."</p>";
		}
	}

	//lehre.vw_stundenplandev_student_unr
	if($result = @$db->db_query("SELECT 1 FROM lehre.vw_stundenplandev_student_unr LIMIT 1;"))
	{
		if(!$db->db_query("DROP VIEW lehre.vw_stundenplandev_student_unr"))
		{
			echo "<p>Could not DROP view lehre.vw_stundenplandev_student_unr: " . $create_view_qry."</p>";
		}
	}

	//public.vw_gruppen
	if($result = @$db->db_query("SELECT 1 FROM public.vw_gruppen LIMIT 1;"))
	{
		if(!$db->db_query("DROP VIEW public.vw_gruppen"))
		{
			echo "<p>Could not DROP view public.vw_gruppen: " . $create_view_qry."</p>";
		}
	}


	//modify all tables
	foreach($all_tables_to_update as $t)
		modifyOneTable($db, $t);






	//********************************CREATE ALL VIEWS********************************

	//bis.vw_bisio
	if(!$result = @$db->db_query("SELECT 1 FROM bis.vw_bisio LIMIT 1;"))
	{
		$create_view_qry = "
			CREATE VIEW bis.vw_bisio AS SELECT tbl_prestudentstatus.studiensemester_kurzbz,
				tbl_prestudentstatus.status_kurzbz,
				tbl_prestudent.person_id,
				tbl_prestudent.prestudent_id,
				tbl_bisio.bisio_id,
				tbl_bisio.mobilitaetsprogramm_code,
				tbl_bisio.nation_code,
				tbl_bisio.von,
				tbl_bisio.bis,
				tbl_bisio.zweck_code,
				tbl_bisio.ort,
				tbl_bisio.universitaet,
				tbl_bisio.lehreinheit_id,
				tbl_student.matrikelnr,
				tbl_student.student_uid,
				tbl_prestudent.studiengang_kz,
				tbl_student.semester,
				tbl_prestudent.aufmerksamdurch_kurzbz,
				tbl_prestudent.berufstaetigkeit_code,
				tbl_prestudent.ausbildungcode,
				tbl_prestudent.zgv_code,
				tbl_prestudent.zgvort,
				tbl_prestudent.zgvdatum,
				tbl_prestudent.zgvmas_code,
				tbl_prestudent.zgvmaort,
				tbl_prestudent.zgvmadatum,
				tbl_prestudent.aufnahmeschluessel,
				tbl_prestudent.facheinschlberuf,
				tbl_prestudent.reihungstest_id,
				tbl_prestudent.anmeldungreihungstest,
				tbl_prestudent.reihungstestangetreten,
				tbl_prestudent.rt_gesamtpunkte,
				tbl_prestudent.bismelden,
				tbl_prestudent.dual,
				tbl_prestudent.rt_punkte1,
				tbl_prestudent.rt_punkte2,
				tbl_prestudent.ausstellungsstaat,
				tbl_prestudent.rt_punkte3,
				tbl_prestudent.zgvdoktor_code,
				tbl_prestudent.zgvdoktorort,
				tbl_prestudent.zgvdoktordatum,
				tbl_prestudent.mentor,
				tbl_prestudent.zgvnation,
				tbl_prestudent.zgvmanation,
				tbl_prestudent.zgvdoktornation,
				tbl_prestudentstatus.ausbildungssemester,
				tbl_prestudentstatus.datum,
				tbl_prestudentstatus.orgform_kurzbz,
				tbl_prestudentstatus.studienplan_id,
				tbl_prestudentstatus.bestaetigtam,
				tbl_prestudentstatus.bestaetigtvon,
				tbl_prestudentstatus.fgm,
				tbl_prestudentstatus.faktiv,
				tbl_prestudentstatus.bewerbung_abgeschicktamum
			FROM bis.tbl_bisio
				JOIN tbl_student USING (prestudent_id)
				JOIN tbl_prestudent USING (prestudent_id)
				LEFT JOIN tbl_prestudentstatus ON tbl_prestudent.prestudent_id = tbl_prestudentstatus.prestudent_id AND (tbl_prestudentstatus.status_kurzbz::text = 'Incoming'::text OR tbl_prestudentstatus.status_kurzbz::text = 'Outgoing'::text);
			COMMENT ON VIEW bis.vw_bisio IS 'Incoming Outgoing';";
		if(!$db->db_query($create_view_qry))
		{
			echo "<p>Could not CREATE view bis.vw_bisio: " . $create_view_qry."</p>";
		}
	}



	//campus.vw_student_lehrveranstaltung
	if(!$result = @$db->db_query("SELECT 1 FROM campus.vw_student_lehrveranstaltung LIMIT 1;"))
	{
		$create_view_qry = "
			CREATE VIEW campus.vw_student_lehrveranstaltung AS
				SELECT tbl_benutzergruppe.uid,
					tbl_lehrveranstaltung.zeugnis,
					tbl_lehrveranstaltung.sort,
					tbl_lehrveranstaltung.lehrveranstaltung_id,
					tbl_lehrveranstaltung.kurzbz,
					tbl_lehrveranstaltung.bezeichnung,
					tbl_lehrveranstaltung.bezeichnung_english,
					tbl_lehrveranstaltung.studiengang_kz,
					tbl_lehrveranstaltung.semester,
					tbl_lehrveranstaltung.sprache,
					tbl_lehrveranstaltung.ects,
					tbl_lehrveranstaltung.semesterstunden,
					tbl_lehrveranstaltung.anmerkung,
					tbl_lehrveranstaltung.lehre,
					tbl_lehrveranstaltung.lehreverzeichnis,
					tbl_lehrveranstaltung.aktiv,
					tbl_lehrveranstaltung.planfaktor,
					tbl_lehrveranstaltung.planlektoren,
					tbl_lehrveranstaltung.planpersonalkosten,
					tbl_lehrveranstaltung.plankostenprolektor,
					tbl_lehrveranstaltung.updateamum,
					tbl_lehrveranstaltung.updatevon,
					tbl_lehrveranstaltung.insertamum,
					tbl_lehrveranstaltung.insertvon,
					tbl_lehrveranstaltung.ext_id,
					tbl_lehreinheit.lehreinheit_id,
					tbl_lehreinheit.studiensemester_kurzbz,
					tbl_lehreinheit.lehrfach_id,
					tbl_lehreinheit.lehrform_kurzbz,
					tbl_lehreinheit.stundenblockung,
					tbl_lehreinheit.wochenrythmus,
					tbl_lehreinheit.start_kw,
					tbl_lehreinheit.raumtyp,
					tbl_lehreinheit.raumtypalternativ,
					tbl_lehrveranstaltung.lehrform_kurzbz AS lv_lehrform_kurzbz
				FROM lehre.tbl_lehreinheitgruppe,
					tbl_benutzergruppe,
					lehre.tbl_lehreinheit,
					lehre.tbl_lehrveranstaltung
				WHERE tbl_lehreinheitgruppe.gruppe_kurzbz::text = tbl_benutzergruppe.gruppe_kurzbz::text AND tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id AND tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitgruppe.lehreinheit_id AND tbl_lehreinheit.studiensemester_kurzbz::text = tbl_benutzergruppe.studiensemester_kurzbz::text
					UNION
					SELECT tbl_studentlehrverband.prestudent_id,
					tbl_lehrveranstaltung.zeugnis,
					tbl_lehrveranstaltung.sort,
					tbl_lehrveranstaltung.lehrveranstaltung_id,
					tbl_lehrveranstaltung.kurzbz,
					tbl_lehrveranstaltung.bezeichnung,
					tbl_lehrveranstaltung.bezeichnung_english,
					tbl_lehrveranstaltung.studiengang_kz,
					tbl_lehrveranstaltung.semester,
					tbl_lehrveranstaltung.sprache,
					tbl_lehrveranstaltung.ects,
					tbl_lehrveranstaltung.semesterstunden,
					tbl_lehrveranstaltung.anmerkung,
					tbl_lehrveranstaltung.lehre,
					tbl_lehrveranstaltung.lehreverzeichnis,
					tbl_lehrveranstaltung.aktiv,
					tbl_lehrveranstaltung.planfaktor,
					tbl_lehrveranstaltung.planlektoren,
					tbl_lehrveranstaltung.planpersonalkosten,
					tbl_lehrveranstaltung.plankostenprolektor,
					tbl_lehrveranstaltung.updateamum,
					tbl_lehrveranstaltung.updatevon,
					tbl_lehrveranstaltung.insertamum,
					tbl_lehrveranstaltung.insertvon,
					tbl_lehrveranstaltung.ext_id,
					tbl_lehreinheit.lehreinheit_id,
					tbl_lehreinheit.studiensemester_kurzbz,
					tbl_lehreinheit.lehrfach_id,
					tbl_lehreinheit.lehrform_kurzbz,
					tbl_lehreinheit.stundenblockung,
					tbl_lehreinheit.wochenrythmus,
					tbl_lehreinheit.start_kw,
					tbl_lehreinheit.raumtyp,
					tbl_lehreinheit.raumtypalternativ,
					tbl_lehrveranstaltung.lehrform_kurzbz AS lv_lehrform_kurzbz
					FROM lehre.tbl_lehreinheitgruppe,
					tbl_studentlehrverband,
					lehre.tbl_lehreinheit,
					lehre.tbl_lehrveranstaltung
					WHERE tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitgruppe.lehreinheit_id AND tbl_lehreinheit.studiensemester_kurzbz::text = tbl_studentlehrverband.studiensemester_kurzbz::text AND tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id AND tbl_studentlehrverband.studiengang_kz = tbl_lehreinheitgruppe.studiengang_kz AND tbl_studentlehrverband.semester = tbl_lehreinheitgruppe.semester AND (btrim(tbl_studentlehrverband.verband::text) = btrim(tbl_lehreinheitgruppe.verband::text) OR (tbl_lehreinheitgruppe.verband IS NULL OR btrim(tbl_lehreinheitgruppe.verband::text) = ''::text) AND tbl_lehreinheitgruppe.gruppe_kurzbz IS NULL) AND (btrim(tbl_studentlehrverband.gruppe::text) = btrim(tbl_lehreinheitgruppe.gruppe::text) OR (tbl_lehreinheitgruppe.gruppe IS NULL OR btrim(tbl_lehreinheitgruppe.gruppe::text) = ''::text) AND tbl_lehreinheitgruppe.gruppe_kurzbz IS NULL);
		";
		if(!$db->db_query($create_view_qry))
		{
			echo "<p>Could not CREATE view campus.vw_student_lehrveranstaltung: " . $create_view_qry."</p>";
		}
	}



	//lehre.vw_stundenplandev_student_unr
	if(!$result = @$db->db_query("SELECT 1 FROM lehre.vw_stundenplandev_student_unr LIMIT 1;"))
	{
		$create_view_qry = "
			CREATE VIEW lehre.vw_stundenplandev_student_unr AS
				SELECT sub_stpl_uid.unr,
					sub_stpl_uid.datum,
					sub_stpl_uid.stunde,
					sub_stpl_uid.student_uid
				FROM ( SELECT stpl.unr,
								stpl.datum,
								stpl.stunde,
								tbl_benutzergruppe.uid AS student_uid
							 FROM tbl_stundenplandev stpl
								 JOIN tbl_benutzergruppe USING (gruppe_kurzbz)
							WHERE tbl_benutzergruppe.studiensemester_kurzbz::text = ((( SELECT tbl_studiensemester.studiensemester_kurzbz
								       FROM tbl_studiensemester
								      WHERE stpl.datum <= tbl_studiensemester.ende AND stpl.datum >= tbl_studiensemester.start))::text)
							GROUP BY stpl.unr, stpl.datum, stpl.stunde, tbl_benutzergruppe.uid
						UNION
						 SELECT stpl.unr,
								stpl.datum,
								stpl.stunde,
								tbl_studentlehrverband.student_uid
							 FROM tbl_stundenplandev stpl
								 JOIN tbl_studentlehrverband ON stpl.gruppe_kurzbz IS NULL AND stpl.studiengang_kz = tbl_studentlehrverband.studiengang_kz AND stpl.semester = tbl_studentlehrverband.semester AND (stpl.verband = tbl_studentlehrverband.verband OR stpl.verband = ' '::bpchar AND stpl.verband <> tbl_studentlehrverband.verband) AND (stpl.gruppe = tbl_studentlehrverband.gruppe OR stpl.gruppe = ' '::bpchar AND stpl.gruppe <> tbl_studentlehrverband.gruppe)
							WHERE tbl_studentlehrverband.studiensemester_kurzbz::text = ((( SELECT tbl_studiensemester.studiensemester_kurzbz
								       FROM tbl_studiensemester
								      WHERE stpl.datum <= tbl_studiensemester.ende AND stpl.datum >= tbl_studiensemester.start))::text)
							GROUP BY stpl.unr, stpl.datum, stpl.stunde, tbl_studentlehrverband.student_uid) sub_stpl_uid
				GROUP BY sub_stpl_uid.unr, sub_stpl_uid.datum, sub_stpl_uid.stunde, sub_stpl_uid.student_uid;
			";
		if(!$db->db_query($create_view_qry))
		{
			echo "<p>Could not CREATE view lehre.vw_stundenplandev_student_unr: " . $create_view_qry."</p>";
		}
	}



	//public.vw_gruppen
	if(!$result = @$db->db_query("SELECT 1 FROM public.vw_gruppen LIMIT 1;"))
	{
		$create_view_qry = "
			CREATE VIEW public.vw_gruppen AS
				SELECT tbl_gruppe.gid,
					tbl_gruppe.gruppe_kurzbz,
					tbl_benutzergruppe.uid,
					tbl_gruppe.mailgrp,
					tbl_gruppe.beschreibung,
					tbl_gruppe.studiengang_kz,
					tbl_gruppe.semester,
					tbl_benutzergruppe.studiensemester_kurzbz,
					NULL::bpchar AS verband,
					NULL::bpchar AS gruppe
				 FROM tbl_gruppe
					 LEFT JOIN tbl_benutzergruppe USING (gruppe_kurzbz)
				UNION
				SELECT tbl_lehrverband.gid,
					upper(btrim((((( SELECT tbl_studiengang.typ::text || tbl_studiengang.kurzbz::text
								 FROM tbl_studiengang
								WHERE tbl_studiengang.studiengang_kz = tbl_lehrverband.studiengang_kz)) || tbl_lehrverband.semester) || tbl_lehrverband.verband::text) || tbl_lehrverband.gruppe::text)) AS gruppe_kurzbz,
					tbl_studentlehrverband.prestudent_id,
					true AS mailgrp,
					tbl_lehrverband.bezeichnung AS beschreibung,
					tbl_lehrverband.studiengang_kz,
					tbl_lehrverband.semester,
					tbl_studentlehrverband.studiensemester_kurzbz,
					tbl_lehrverband.verband,
					tbl_lehrverband.gruppe
				 FROM tbl_lehrverband
					 LEFT JOIN tbl_studentlehrverband USING (studiengang_kz, semester)
				WHERE (tbl_lehrverband.verband = tbl_studentlehrverband.verband OR tbl_lehrverband.verband IS NULL OR btrim(tbl_lehrverband.verband::text) = ''::text OR tbl_studentlehrverband.verband IS NULL) AND (tbl_lehrverband.gruppe = tbl_studentlehrverband.gruppe OR tbl_lehrverband.gruppe IS NULL OR btrim(tbl_lehrverband.gruppe::text) = ''::text OR tbl_studentlehrverband.gruppe IS NULL);
			";
		if(!$db->db_query($create_view_qry))
		{
			echo "<p>Could not CREATE view public.vw_gruppen: " . $create_view_qry."</p>";
		}
	}













}



echo '</body></html>';























/* FUNCTIONS */
function describeOneChange($db, $table)
{

	if(!$result = @$db->db_query('SELECT '.$table["to"].' FROM '.$table["schema"].'.'.$table["name"].' LIMIT 1;'))
	{
		echo "<div>".$table["schema"].".".$table["name"].": ".$table["from"]." wird <strong style='color:red;'>gelöscht</strong>!</div>";
		echo "<div>".$table["schema"].".".$table["name"].": ".$table["to"]." wird <strong style='color:green;'>eingefügt</strong>!</div>";
	}
}

function checkForUpdates($db, $table)
{
	if(!$result = @$db->db_query('SELECT '.$table["to"].' FROM '.$table["schema"].'.'.$table["name"].' LIMIT 1;'))
	{
		return true;
	}
	return false;
}


function modifyOneTable($db, $table)
{
	if(!$result = @$db->db_query('SELECT '.$table["to"].' FROM '.$table["schema"].'.'.$table["name"].' LIMIT 1;'))
	{
		$db->db_query("BEGIN;");

		$indices = array();
		$primary_keys = array();


		$index_search_result = $db->db_query("SELECT * FROM pg_indexes WHERE schemaname=".$db->db_add_param($table["schema"])." AND tablename=".$db->db_add_param($table["name"]));
		while($row = $db->db_fetch_object($index_search_result))
		{
			if(strpos($row->indexdef, $table["from"]) !== false)		//only if the pk is affected
			{
				$check_if_pk_result = $db->db_query("select * from pg_constraint where conname=".$db->db_add_param($row->indexname));

				if($db->db_num_rows($check_if_pk_result) == 1)
				{
					$get_definition_result = $db->db_query(
						"SELECT conrelid::regclass AS table_from
							,conname
							,pg_get_constraintdef(c.oid)
							FROM pg_constraint c
							JOIN pg_namespace n ON n.oid = c.connamespace
						WHERE contype IN ('f', 'p ')
							AND n.nspname = ".$db->db_add_param($row->schemaname)."
							AND conname = ".$db->db_add_param($row->indexname)."
							ORDER BY conrelid::regclass::text, contype DESC;");
					$def = $db->db_fetch_object($get_definition_result);

					if(!$pk_drop_result = $db->db_query('ALTER TABLE '.$table["schema"].".".$table["name"].' DROP CONSTRAINT '.$row->indexname))
					{
						echo "<p><span style='color:red;'>ACHTUNG:</span> DROPPEN von PRIMARY KEY ".$row->indexname." fehlgeschlagen</p>";
						$db->db_query("ROLLBACK;");
						return;
					}

					$constraint_add_query = str_replace ($table["from"], $table["to"], $def->pg_get_constraintdef );
					$primary_keys[] = 'ALTER TABLE '.$table["schema"].".".$table["name"].' ADD CONSTRAINT '.$row->indexname.' '.$constraint_add_query;
				}
				else
				{
					if(!$index_drop_result = $db->db_query('DROP INDEX '.$table["schema"].".".$row->indexname))
					{
						echo "<p><span style='color:red;'>ACHTUNG:</span> DROPPEN von INDEX ".$row->indexname." fehlgeschlagen</p>";
						$db->db_query("ROLLBACK;");
						return;
					}

					$index_add_query = str_replace ($table["from"], $table["to"], $row->indexdef );
					$indices[] = $index_add_query;
				}
			}
		}



		//spalte einfuegen
		$alter_update_qry = 'ALTER TABLE '.$table["schema"].'.'.$table["name"].' ADD COLUMN '.$table["to"].' '.$table["datatype"].';
		UPDATE '.$table["schema"].".".$table["name"].' SET '.$table["to"].' = (SELECT '.$table["to"].' FROM '.$table["newTargetSchema"].'.'.$table["pickDataFrom"].' WHERE '.$table["pickDataFromCol"].'='.$table["schema"].'.'.$table["name"].'.'.$table["from"].');';
		$db->db_query($alter_update_qry);

		$constraint_qry = "";

		//constraints: $TO FK, $TO
		if($table["constraint"] != "")
			$constraint_qry = 'ALTER TABLE '.$table["schema"].".".$table["name"].' ALTER COLUMN '.$table["to"].' '.$table["constraint"].";";

		$constraint_qry.=' ALTER TABLE '.$table["schema"].".".$table["name"].' ADD CONSTRAINT fk_'.$table["name"].'_'.$table["newTarget"].'_'.$table["to"].' FOREIGN KEY ('.$table["to"].') REFERENCES '.$table["newTargetSchema"].'.'.$table["newTarget"].' ('.$table["to"].');';

		if(!$db->db_query($constraint_qry))
		{
			echo '<strong>'.$table["schema"].".".$table["name"].': '.$db->db_last_error().'</strong><br>';
		}
		else
		{
			echo ' '.$table["schema"].".".$table["name"].': Spalte '.$table["to"].' hinzugefuegt!<br>';
			echo ' '.$table["schema"].".".$table["name"].': Spalte '.$table["from"].' auf '.$table["to"].' geändert!<br>';
			echo ' '.$table["schema"].".".$table["name"].': Spalte '.$table["to"].' constraints eingefuegt!<br>';

		//FROM löschen
			$qry = 'ALTER TABLE '.$table["schema"].".".$table["name"].' DROP COLUMN '.$table["from"].';';

			if(!$db->db_query($qry))
			{
				echo '<strong>'.$table["schema"].".".$table["name"].': '.$db->db_last_error().'</strong><br>';
				echo '<strong>'.$table["schema"].".".$table["name"].': ACHTUNG! In diesem Fall sollte '.$table["to"].' ordnungsgemäß eingetragen sein, jedoch '.$table["from"].' nicht gelöscht worden sein. Das Skript erneut zu starten wird nicht funktionieren!</strong><br>';
			}
			else
			{
				echo ' '.$table["schema"].".".$table["name"].': Spalte '.$table["from"].' gelöscht!<br>';

				foreach( $primary_keys as $pk)
				{
					if(!$pk_add_result = $db->db_query($pk))
					{
						echo "<p><span style='color:red;'>ACHTUNG:</span> ADDEN von PRIMARY KEY ".$row->indexname." fehlgeschlagen</p>";
						$db->db_query("ROLLBACK;");
						return;
					}
				}
				foreach( $indices as $ind)
				{
					if(!$index_add_result = $db->db_query($ind))
					{
						echo "<p><span style='color:red;'>ACHTUNG:</span> ADDEN von INDEX ".$row->indexname." fehlgeschlagen</p>";
						$db->db_query("ROLLBACK;");
						return;
					}
				}

				$db->db_query("COMMIT;");
				return;
			}
		}
	}
}


?>
