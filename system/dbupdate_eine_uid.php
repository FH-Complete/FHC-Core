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
 * Dieses Skript prueft die Datenbank auf Aenderungen bezueglich eine_uid, dabei werden fehlende Attribute, usw angelegt.
 */
require_once('../config/system.config.inc.php');
require_once('../include/basis_db.class.php');
$db = new basis_db();
?>

<html>
<head>
	<title>CS-EineUid</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="../skin/vilesci.css" type="text/css" />

	<script>
		function startScript()
		{
			document.getElementById('startButton').disabled = true;
			var form = document.createElement('form');
			form.setAttribute('method', 'POST');
			form.setAttribute('action', 'dbupdate_eine_uid.php');

			var hiddenField = document.createElement('input');
			hiddenField.setAttribute('type', 'hidden');
			hiddenField.setAttribute('value', 'Starten');
			hiddenField.setAttribute('name', 'action');
			form.appendChild(hiddenField);

			document.body.appendChild(form);
			form.submit();
		}
	</script>

</head>
<body>
<?php

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
	array("schema" => "lehre",  "name" => "tbl_zeugnis",              "from" => "student_uid", "to" => "prestudent_id", "datatype" => "int",         "newTarget" => "tbl_prestudent", "newTargetSchema" => "public", "pickDataFrom" => "tbl_student",  "pickDataFromCol" => "student_uid", "constraint" => "SET NOT NULL"),
	array("schema" => "lehre",  "name" => "tbl_zeugnisnote",          "from" => "student_uid", "to" => "prestudent_id", "datatype" => "int",         "newTarget" => "tbl_prestudent", "newTargetSchema" => "public", "pickDataFrom" => "tbl_student",  "pickDataFromCol" => "student_uid", "constraint" => "SET NOT NULL"),
	array("schema" => "public", "name" => "tbl_preoutgoing",          "from" => "uid",         "to" => "prestudent_id", "datatype" => "int",         "newTarget" => "tbl_prestudent", "newTargetSchema" => "public", "pickDataFrom" => "tbl_student",  "pickDataFromCol" => "student_uid", "constraint" => "SET NOT NULL"),

);

if(!isset($_POST["action"]))
{
	$change_needed = false;
	$generic_needs = "";
	
	/* GENERIC */
	if(!$result = @$db->db_query("SELECT perskz FROM public.tbl_prestudent LIMIT 1;"))
	{
		$change_needed = true;
		$generic_needs .= "public.tbl_prestudent: uid wird <strong style='color:green;'>eingefügt</strong>(student_uid von tbl_student)<br>";
		$generic_needs .= "public.tbl_prestudent: perskz wird <strong style='color:green;'>eingefügt</strong>(matrikelnr von tbl_student)<br>";
	}
	
	/* TABLE SPECIFIC */
	foreach($all_tables_to_update as $t)
	{
		if(checkForUpdates($db, $t))
			$change_needed = true;
	}



	if($change_needed)
	{
		echo "<h2 style='color:red;'>ACHTUNG!</h2>";
		echo "<h3>Folgendes wird geändert:</h3>";
		echo "<p>";
		
		if($generic_needs != "")
		{
			echo $generic_needs;
		}

		foreach($all_tables_to_update as $t)
			describeOneChange($db, $t);

		echo "</p>";
		echo "
		<h1 style='margin-top:50px;color:#F44'>ACHTUNG: Dieser Vorgang kann mehrere Minuten dauern!</h1>

			<input id='startButton' type='button' value='Starten' onclick='startScript()'>";
	}
	else
	{
		echo "<h2>Es sind keine Änderungen vorzunehmen!</h2>";
	}
}
else if($_POST["action"] == "Starten")
{
	$db->db_query("BEGIN;");
	$error = false;

	echo '<H1>Systemcheck!</H1>';
	echo '<H2>DB-Updates!</H2>';


	// *** Pruefung und hinzufuegen der neuen Attribute und Tabellen
	echo '<H2>Pruefe Tabellen und Attribute!</H2>';

	//********************************tbl_prestudent CHANGES********************************
	if(!$db->db_num_rows($db->db_query("SELECT * FROM information_schema.columns WHERE
		table_schema='public' AND
		table_name='tbl_prestudent' AND
		column_name='perskz';")))
	{
		$prestudent_qry = "ALTER TABLE public.tbl_prestudent ADD COLUMN uid varchar(32);
			ALTER TABLE public.tbl_prestudent ADD CONSTRAINT fk_tbl_prestudent_tbl_benutzer_uid FOREIGN KEY (uid) REFERENCES public.tbl_benutzer (uid) ON DELETE RESTRICT ON UPDATE CASCADE;
			UPDATE public.tbl_prestudent SET uid = (SELECT student_uid FROM public.tbl_student WHERE tbl_student.prestudent_id = tbl_prestudent.prestudent_id);
		";
		if(!$result = @$db->db_query($prestudent_qry))
		{
			$error = true;
			echo "<p>Could not ADD COLUMN uid TO public.tbl_prestudent: " . $db->db_last_error()."</p>";
		}

		$prestudent_qry = "ALTER TABLE public.tbl_prestudent ADD COLUMN perskz character(15);
		UPDATE public.tbl_prestudent SET perskz = (SELECT matrikelnr FROM public.tbl_student WHERE tbl_student.prestudent_id = tbl_prestudent.prestudent_id);
		";
		if(!$result = @$db->db_query($prestudent_qry))
		{
			$error = true;
			echo "<p>Could not ADD COLUMN perskz TO public.tbl_prestudent: " . $db->db_last_error()."</p>";
		}
	}

	//********************************tbl_benutzergruppe CHANGES********************************
	if(!$db->db_num_rows($db->db_query("SELECT * FROM information_schema.columns WHERE
		table_schema='public' AND
		table_name='tbl_benutzergruppe' AND
		column_name='prestudent_id';")))
	{
		$prestudent_qry = "ALTER TABLE public.tbl_benutzergruppe ADD COLUMN prestudent_id int;
			ALTER TABLE public.tbl_benutzergruppe ADD CONSTRAINT fk_tbl_benutzergruppe_tbl_prestudent_prestudent_id FOREIGN KEY (prestudent_id) REFERENCES public.tbl_prestudent (prestudent_id) ON DELETE RESTRICT ON UPDATE CASCADE;
			UPDATE public.tbl_benutzergruppe SET prestudent_id = (SELECT prestudent_id FROM public.tbl_prestudent WHERE tbl_prestudent.uid = tbl_benutzergruppe.uid);
		";
		if(!$result = @$db->db_query($prestudent_qry))
		{
			$error = true;
			echo "<p>Could not ADD COLUMN prestudent_id TO public.tbl_benutzergruppe: " . $db->db_last_error()."</p>";
		}
	}


	//********************************tbl_preoutgoing - MA - testdaten entfernen(uid wird auf prestudent_id geändert)********************************

	$qry = "DELETE FROM public.tbl_preoutgoing where uid in(select uid from tbl_mitarbeiter where tbl_mitarbeiter.mitarbeiter_uid = tbl_preoutgoing.uid);
	";
	if(!$result = @$db->db_query($qry))
	{
		$error = true;
		echo "<p>Could not ADD COLUMN prestudent_id TO public.tbl_benutzergruppe: " . $db->db_last_error()."</p>";
	}

	if(!dropViews($db, $error))
		$error = true;

	//modify all tables
	foreach($all_tables_to_update as $t)
		if(!modifyOneTable($db, $t, $error))
			$error = true;

	if(!createViews($db, $error))
		$error = true;

	if($error)
	{
		$db->db_query("ROLLBACK;");
		echo "<p style='color:red;'>Rolled Back because of errors</p>";
	}
	else
	$db->db_query("COMMIT;");
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


function modifyOneTable($db, $table, $error)
{
	if($error)
		return false;

	$qry_count = 'SELECT 1
		FROM information_schema.columns
		WHERE table_name='.$db->db_add_param($table["name"]).'
		AND column_name='.$db->db_add_param($table["to"]).'
		AND table_schema='.$db->db_add_param($table["schema"]).'
		LIMIT 1';
	$result_count = $db->db_query($qry_count);
	$row_count = $db->db_num_rows($result_count);

	if($row_count < 1)
	{
		$indices = array();
		$primary_keys = array();

		$index_search_result = $db->db_query("SELECT * FROM pg_indexes WHERE schemaname=".$db->db_add_param($table["schema"])." AND tablename=".$db->db_add_param($table["name"]));
		if(!$index_search_result)
		{
			echo "<p>ERROR: ".$db->getErrorMsg()."</p>";
			return false;
		}
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
						echo "<p><span style='color:red;'>ACHTUNG:</span> DROPPEN von PRIMARY KEY ".$row->indexname." fehlgeschlagen: " . $db->db_last_error() . "</p>";
						return false;
					}

					$constraint_add_query = str_replace ($table["from"], $table["to"], $def->pg_get_constraintdef );
					$primary_keys[] = 'ALTER TABLE '.$table["schema"].".".$table["name"].' ADD CONSTRAINT '.$row->indexname.' '.$constraint_add_query;
				}
				else
				{
					if(!$index_drop_result = $db->db_query('DROP INDEX '.$table["schema"].".".$row->indexname))
					{
						echo "<p><span style='color:red;'>ACHTUNG:</span> DROPPEN von INDEX ".$row->indexname." fehlgeschlagen: " . $db->db_last_error() . "</p>";
						return false;
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
		if($table["constraint"] != "")
			$constraint_qry = 'ALTER TABLE '.$table["schema"].".".$table["name"].' ALTER COLUMN '.$table["to"].' '.$table["constraint"].";";

		$constraint_qry.=' ALTER TABLE '.$table["schema"].".".$table["name"].' ADD CONSTRAINT fk_'.$table["name"].'_'.$table["newTarget"].'_'.$table["to"].' FOREIGN KEY ('.$table["to"].') REFERENCES '.$table["newTargetSchema"].'.'.$table["newTarget"].' ('.$table["to"].');';

		if(!$db->db_query($constraint_qry))
		{
			echo '<strong>'.$table["schema"].".".$table["name"].': '.$db->db_last_error().'</strong><br>';
		}
		else
		{
			echo "<p><span style='color:green;'>Added</span>: ".$table["schema"].".".$table["name"].".".$table["to"]."</p>";

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
						echo "<p><span style='color:red;'>ACHTUNG:</span> ADDEN von PRIMARY KEY ".$row->indexname." fehlgeschlagen: " . $db->db_last_error() . "</p>";
						return false;
					}
				}
				foreach( $indices as $ind)
				{
					if(!$index_add_result = $db->db_query($ind))
					{
						echo "<p><span style='color:red;'>ACHTUNG:</span> ADDEN von INDEX ".$row->indexname." fehlgeschlagen: " . $db->db_last_error() . "</p>";
						return false;
					}
				}
			}
		}
	}
	return true;
}



function dropViews($db, $error)
{
	if($error)
		return false;

	$viewsToDrop = array
	(
		array("schema" => "bis", "name" => "vw_bisio"),
		array("schema" => "campus", "name" => "vw_student_lehrveranstaltung"),
		array("schema" => "lehre", "name" => "vw_stundenplandev_student_unr"),
		array("schema" => "public", "name" => "vw_gruppen"),
		array("schema" => "lehre", "name" => "vw_zeugnisnote"),
		array("schema" => "testtool", "name" => "vw_reihungstest_zeugnisnoten"),
		array("schema" => "reports", "name" => "vw_outgoing"),
	);

	//********************************DROP ALL VIEWS********************************
	foreach($viewsToDrop as $v)
	{
		if(!@$db->db_query("DROP VIEW ".$v["schema"].".".$v["name"]))
		{
			echo "<p>Could not DROP view ".$v["schema"].".".$v["name"].": " . $db->db_last_error() . "</p>";
			return false;
		} else
		{
			echo "<p><span style='color:green;'>Dropped</span>: ".$v["schema"].".".$v["name"]."</p>";
		}
	}
	return true;
}




function createViews($db, $error)
{
	if($error)
		return false;

	//********************************CREATE ALL VIEWS********************************

	//bis.vw_bisio
	if(!$db->db_num_rows($db->db_query("SELECT * FROM information_schema.tables WHERE table_type='VIEW'
		AND table_schema='bis'
		AND table_name='vw_bisio';")))
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
			echo "<p>Could not CREATE view bis.vw_bisio: " . $db->db_last_error() . "</p>";
			return false;
		}
		else
		{
			echo "<p><span style='color:green;'>Created</span>: bis.vw_bisio</p>";
		}
	}


	//campus.vw_student_lehrveranstaltung
	if(!$db->db_num_rows($db->db_query("SELECT * FROM information_schema.tables WHERE table_type='VIEW'
		AND table_schema='campus'
		AND table_name='vw_student_lehrveranstaltung';")))
	{
		$create_view_qry = "
			CREATE VIEW campus.vw_student_lehrveranstaltung AS
				SELECT tbl_benutzergruppe.prestudent_id,
					tbl_benutzergruppe.uid,
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
					tbl_prestudent.uid,
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
					lehre.tbl_lehrveranstaltung,
					public.tbl_prestudent
					WHERE tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitgruppe.lehreinheit_id AND tbl_lehreinheit.studiensemester_kurzbz::text = tbl_studentlehrverband.studiensemester_kurzbz::text AND tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id AND tbl_studentlehrverband.studiengang_kz = tbl_lehreinheitgruppe.studiengang_kz AND tbl_studentlehrverband.semester = tbl_lehreinheitgruppe.semester AND (btrim(tbl_studentlehrverband.verband::text) = btrim(tbl_lehreinheitgruppe.verband::text) OR (tbl_lehreinheitgruppe.verband IS NULL OR btrim(tbl_lehreinheitgruppe.verband::text) = ''::text) AND tbl_lehreinheitgruppe.gruppe_kurzbz IS NULL) AND (btrim(tbl_studentlehrverband.gruppe::text) = btrim(tbl_lehreinheitgruppe.gruppe::text) OR (tbl_lehreinheitgruppe.gruppe IS NULL OR btrim(tbl_lehreinheitgruppe.gruppe::text) = ''::text) AND tbl_lehreinheitgruppe.gruppe_kurzbz IS NULL) AND tbl_studentlehrverband.prestudent_id = tbl_prestudent.prestudent_id;
		";
		if(!$db->db_query($create_view_qry))
		{
			echo "<p>Could not CREATE view campus.vw_student_lehrveranstaltung: " . $db->db_last_error() . "</p>";
			return false;
		}
		else
		{
			echo "<p><span style='color:green;'>Created</span>: campus.vw_student_lehrveranstaltung</p>";
		}
	}



	//lehre.vw_stundenplandev_student_unr
	if(!$db->db_num_rows($db->db_query("SELECT * FROM information_schema.tables WHERE table_type='VIEW'
		AND table_schema='lehre'
		AND table_name='vw_stundenplandev_student_unr';")))
	{
		$create_view_qry = "
			CREATE VIEW lehre.vw_stundenplandev_student_unr AS
				SELECT sub_stpl_uid.unr,
					sub_stpl_uid.datum,
					sub_stpl_uid.stunde,
					sub_stpl_uid.uid
				FROM ( SELECT stpl.unr,
								stpl.datum,
								stpl.stunde,
								tbl_benutzergruppe.uid,
								tbl_benutzergruppe.prestudent_id
							 FROM lehre.tbl_stundenplandev stpl
								 JOIN tbl_benutzergruppe USING (gruppe_kurzbz)
							WHERE tbl_benutzergruppe.studiensemester_kurzbz::text = ((( SELECT tbl_studiensemester.studiensemester_kurzbz
									     FROM tbl_studiensemester
									    WHERE stpl.datum <= tbl_studiensemester.ende AND stpl.datum >= tbl_studiensemester.start))::text)
							GROUP BY stpl.unr, stpl.datum, stpl.stunde, tbl_benutzergruppe.uid, tbl_benutzergruppe.prestudent_id
						UNION
						 SELECT stpl.unr,
								stpl.datum,
								stpl.stunde,
								tbl_prestudent.uid,
								tbl_studentlehrverband.prestudent_id
							FROM lehre.tbl_stundenplandev stpl
								JOIN tbl_studentlehrverband ON stpl.gruppe_kurzbz IS NULL AND stpl.studiengang_kz = tbl_studentlehrverband.studiengang_kz AND stpl.semester = tbl_studentlehrverband.semester AND (stpl.verband = tbl_studentlehrverband.verband OR stpl.verband = ' '::bpchar AND stpl.verband <> tbl_studentlehrverband.verband) AND (stpl.gruppe = tbl_studentlehrverband.gruppe OR stpl.gruppe = ' '::bpchar AND stpl.gruppe <> tbl_studentlehrverband.gruppe)
								JOIN tbl_prestudent on(tbl_prestudent.prestudent_id = tbl_studentlehrverband.prestudent_id)
							WHERE tbl_studentlehrverband.studiensemester_kurzbz::text = ((( SELECT tbl_studiensemester.studiensemester_kurzbz
									     FROM tbl_studiensemester
									    WHERE stpl.datum <= tbl_studiensemester.ende AND stpl.datum >= tbl_studiensemester.start))::text)
							GROUP BY stpl.unr, stpl.datum, stpl.stunde, tbl_studentlehrverband.prestudent_id, tbl_prestudent.uid) sub_stpl_uid
				GROUP BY sub_stpl_uid.unr, sub_stpl_uid.datum, sub_stpl_uid.stunde, sub_stpl_uid.uid;
			";
		if(!$db->db_query($create_view_qry))
		{
			echo "<p>Could not CREATE view lehre.vw_stundenplandev_student_unr: " . $db->db_last_error() . "</p>";
			return false;
		}
		else
		{
			echo "<p><span style='color:green;'>Created</span>: lehre.vw_stundenplandev_student_unr</p>";
		}
	}



	//public.vw_gruppen
	if(!$db->db_num_rows($db->db_query("SELECT * FROM information_schema.tables WHERE table_type='VIEW'
		AND table_schema='public'
		AND table_name='vw_gruppen';")))
	{
		$create_view_qry = "
			CREATE VIEW public.vw_gruppen AS
				SELECT tbl_gruppe.gid,
					tbl_gruppe.gruppe_kurzbz,
					tbl_benutzergruppe.uid,
					tbl_benutzergruppe.prestudent_id,
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
					tbl_prestudent.uid,
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
					 JOIN tbl_prestudent ON(tbl_prestudent.prestudent_id=tbl_studentlehrverband.prestudent_id)
				WHERE (tbl_lehrverband.verband = tbl_studentlehrverband.verband OR tbl_lehrverband.verband IS NULL OR btrim(tbl_lehrverband.verband::text) = ''::text OR tbl_studentlehrverband.verband IS NULL) AND (tbl_lehrverband.gruppe = tbl_studentlehrverband.gruppe OR tbl_lehrverband.gruppe IS NULL OR btrim(tbl_lehrverband.gruppe::text) = ''::text OR tbl_studentlehrverband.gruppe IS NULL);
			";
		if(!$db->db_query($create_view_qry))
		{
			echo "<p>Could not CREATE view public.vw_gruppen: " . $db->db_last_error() . "</p>";
			return false;
		}
		else
		{
			echo "<p><span style='color:green;'>Created</span>: public.vw_gruppen</p>";
		}
	}



	//lehre.vw_zeugnisnote
	if(!$db->db_num_rows($db->db_query("SELECT * FROM information_schema.tables WHERE table_type='VIEW'
		AND table_schema='lehre'
		AND table_name='vw_zeugnisnote';")))
	{
		$create_view_qry = "
			CREATE VIEW lehre.vw_zeugnisnote AS
				SELECT tbl_zeugnisnote.studiensemester_kurzbz,
					tbl_prestudent.uid,
					tbl_prestudent.perskz,
					tbl_prestudent.studiengang_kz,
					tbl_lehrveranstaltung.kurzbz,
					tbl_zeugnisnote.note,
					tbl_lehrveranstaltung.ects,
					tbl_zeugnisnote.lehrveranstaltung_id,
					tbl_person.person_id,
					tbl_prestudent.prestudent_id,
					tbl_zeugnisnote.benotungsdatum,
					tbl_person.staatsbuergerschaft,
					tbl_person.geburtsnation,
					tbl_person.sprache,
					tbl_person.nachname,
					tbl_person.vorname,
					tbl_person.gebdatum,
					tbl_person.gebort,
					tbl_person.gebzeit,
					tbl_person.svnr,
					tbl_person.ersatzkennzeichen,
					tbl_person.familienstand,
					tbl_person.geschlecht,
					tbl_person.anzahlkinder,
					tbl_person.bundesland_code,
					tbl_lehrveranstaltung.bezeichnung,
					tbl_lehrveranstaltung.studiengang_kz AS lv_studiengang_kz,
					tbl_lehrveranstaltung.semester AS lv_semester,
					tbl_lehrveranstaltung.semesterstunden,
					tbl_lehrveranstaltung.lehrform_kurzbz,
					tbl_lehrveranstaltung.orgform_kurzbz,
					tbl_prestudent.rt_punkte1,
					tbl_prestudent.rt_punkte2,
					tbl_prestudent.rt_punkte3,
					tbl_prestudent.rt_gesamtpunkte
				 FROM tbl_prestudent
					 JOIN lehre.tbl_zeugnisnote USING (prestudent_id)
					 JOIN tbl_person USING (person_id)
					 JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id);
				COMMENT ON VIEW lehre.vw_zeugnisnote IS 'Zeugnisnoten inkl. Personendaten, LV-Daten und RT-Punkte';
			";
		if(!$db->db_query($create_view_qry))
		{
			echo "<p>Could not CREATE view lehre.vw_zeugnisnote: " . $db->db_last_error() . "</p>";
			return false;
		}
		else
		{
			echo "<p><span style='color:green;'>Created</span>: lehre.vw_zeugnisnote</p>";
		}
	}

	//testtool.vw_reihungstest_zeugnisnoten
	if(!$db->db_num_rows($db->db_query("SELECT * FROM information_schema.tables WHERE table_type='VIEW'
		AND table_schema='testtool'
		AND table_name='vw_reihungstest_zeugnisnoten';")))
	{
		$create_view_qry = "
			CREATE VIEW testtool.vw_reihungstest_zeugnisnoten AS
				SELECT tbl_zeugnisnote.studiensemester_kurzbz,
								CASE
									  WHEN tbl_zeugnisnote.note IS NULL THEN 5
									  WHEN tbl_zeugnisnote.note = ANY (ARRAY[7, 13, 14, 15]) THEN 5
									  ELSE tbl_zeugnisnote.note::integer
								END AS note,
						tbl_zeugnisnote.lehrveranstaltung_id,
						tbl_zeugnisnote.benotungsdatum,
						tbl_benutzer.uid,
						tbl_student.matrikelnr,
						tbl_student.studiengang_kz AS student_stg_kz,
						tbl_student.semester,
						tbl_student.prestudent_id,
						tbl_lehrveranstaltung.kurzbz,
						tbl_lehrveranstaltung.bezeichnung,
						tbl_lehrveranstaltung.studiengang_kz AS lv_studiengang_kz,
						tbl_lehrveranstaltung.semester AS lv_semester,
						tbl_lehrveranstaltung.semesterstunden,
						tbl_lehrveranstaltung.lehrform_kurzbz,
						tbl_lehrveranstaltung.orgform_kurzbz,
						tbl_lehrveranstaltung.ects,
						tbl_lehrveranstaltung.zeugnis,
						tbl_lehrveranstaltung.studiengang_kz AS lv_stg_kz,
						tbl_person.person_id,
						tbl_person.staatsbuergerschaft,
						tbl_person.geburtsnation,
						tbl_person.sprache,
						tbl_person.nachname,
						tbl_person.vorname,
						tbl_person.gebdatum,
						tbl_person.gebort,
						tbl_person.gebzeit,
						tbl_person.svnr,
						tbl_person.ersatzkennzeichen,
						tbl_person.familienstand,
						tbl_person.geschlecht,
						tbl_person.anzahlkinder,
						tbl_person.bundesland_code,
						tbl_prestudent.rt_punkte1,
						tbl_prestudent.rt_punkte2,
						tbl_prestudent.rt_punkte3,
						tbl_prestudent.rt_gesamtpunkte,
						tbl_studiensemester.ende
					 FROM tbl_student
						 JOIN lehre.tbl_zeugnisnote USING (prestudent_id)
						 JOIN tbl_benutzer ON tbl_student.student_uid::text = tbl_benutzer.uid::text
						 JOIN tbl_person USING (person_id)
						 JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
						 JOIN tbl_prestudent USING (prestudent_id)
						 JOIN tbl_studiensemester USING (studiensemester_kurzbz);
				COMMENT ON VIEW testtool.vw_reihungstest_zeugnisnoten IS 'Fuer die Gegenueberstellung der Reihungstestergebnisse mit den Zeugnisnoten';
			";
		if(!$db->db_query($create_view_qry))
		{
			echo "<p>Could not CREATE view testtool.vw_reihungstest_zeugnisnoten: " . $db->db_last_error() . "</p>";
			return false;
		}
		else
		{
			echo "<p><span style='color:green;'>Created</span>: testtool.vw_reihungstest_zeugnisnoten</p>";
		}
	}

	//reports.vw_outgoing
	if(!$db->db_num_rows($db->db_query("SELECT * FROM information_schema.tables WHERE table_type='VIEW'
		AND table_schema='reports'
		AND table_name='vw_outgoing';")))
	{
		$create_view_qry = "
			CREATE VIEW reports.vw_outgoing AS
				SELECT 'Outgoing'::character varying AS status_kurzbz,
					tbl_preoutgoing_firma.mobilitaetsprogramm_code,
					tbl_benutzer.person_id,
					tbl_preoutgoing.prestudent_id,
					tbl_preoutgoing_firma.firma_id,
					tbl_preoutgoing.preoutgoing_id,
					tbl_preoutgoing.dauer_von,
					tbl_preoutgoing.dauer_bis,
					tbl_preoutgoing.ansprechperson,
					tbl_preoutgoing.bachelorarbeit,
					tbl_preoutgoing.masterarbeit,
					tbl_preoutgoing.betreuer,
					tbl_preoutgoing.sprachkurs,
					tbl_preoutgoing.intensivsprachkurs,
					tbl_preoutgoing.sprachkurs_von,
					tbl_preoutgoing.sprachkurs_bis,
					tbl_preoutgoing.praktikum,
					tbl_preoutgoing.praktikum_von,
					tbl_preoutgoing.praktikum_bis,
					tbl_preoutgoing.behinderungszuschuss,
					tbl_preoutgoing.studienbeihilfe,
					tbl_preoutgoing.studienrichtung_gastuniversitaet,
					tbl_preoutgoing.projektarbeittitel,
					tbl_preoutgoing_firma.preoutgoing_firma_id,
					tbl_preoutgoing_firma.name AS partneruniname,
					tbl_preoutgoing_firma.auswahl,
					tbl_firma.name AS firmenname,
					tbl_firma.firmentyp_kurzbz,
					tbl_firma.schule,
					tbl_firma.steuernummer,
					tbl_firma.gesperrt,
					tbl_firma.aktiv AS firmaaktiv,
					tbl_benutzer.alias,
					tbl_person.staatsbuergerschaft,
					tbl_person.geburtsnation,
					tbl_person.sprache,
					tbl_person.anrede,
					tbl_person.titelpost,
					tbl_person.titelpre,
					tbl_person.nachname,
					tbl_person.vorname,
					tbl_person.vornamen,
					tbl_person.gebdatum,
					tbl_person.gebort,
					tbl_person.gebzeit,
					tbl_person.homepage,
					tbl_person.svnr,
					tbl_person.ersatzkennzeichen,
					tbl_person.familienstand,
					tbl_person.geschlecht,
					tbl_person.anzahlkinder,
					tbl_person.bundesland_code,
					tbl_person.kompetenzen,
					tbl_person.kurzbeschreibung,
					tbl_person.zugangscode,
					tbl_person.foto_sperre,
					tbl_person.matr_nr,
					tbl_mobilitaetsprogramm.kurzbz,
					tbl_mobilitaetsprogramm.beschreibung,
					tbl_mobilitaetsprogramm.sichtbar,
					tbl_mobilitaetsprogramm.sichtbar_outgoing
				FROM tbl_preoutgoing
					JOIN tbl_preoutgoing_firma USING (preoutgoing_id)
					LEFT JOIN tbl_firma USING (firma_id)
					JOIN tbl_prestudent USING (prestudent_id)
					JOIN tbl_benutzer USING (uid)
					JOIN tbl_person ON (tbl_benutzer.person_id = tbl_person.person_id)
					LEFT JOIN bis.tbl_mobilitaetsprogramm USING (mobilitaetsprogramm_code)
				WHERE tbl_firma.firmentyp_kurzbz::text = 'Partneruniversität'::text OR tbl_firma.firmentyp_kurzbz IS NULL;
			";
		if(!$db->db_query($create_view_qry))
		{
			echo "<p>Could not CREATE view reports.vw_outgoing: " . $db->db_last_error() . "</p>";
			return false;
		}
		else
		{
			echo "<p><span style='color:green;'>Created</span>: reports.vw_outgoing</p>";
		}
	}



	return true;
}




?>
