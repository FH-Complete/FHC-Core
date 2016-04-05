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
	array("schema" => "bis",    "name" => "tbl_bisio",           "from" => "student_uid", "to" => "prestudent_id", "datatype" => "int",         "newTarget" => "tbl_prestudent", "newTargetSchema" => "public", "pickDataFrom" => "tbl_student",  "pickDataFromCol" => "student_uid"),
	array("schema" => "campus", "name" => "tbl_lvgesamtnote",    "from" => "student_uid", "to" => "prestudent_id", "datatype" => "int",         "newTarget" => "tbl_prestudent", "newTargetSchema" => "public", "pickDataFrom" => "tbl_student",  "pickDataFromCol" => "student_uid"),
	array("schema" => "campus", "name" => "tbl_studentbeispiel", "from" => "student_uid", "to" => "uid",           "datatype" => "varchar(32)", "newTarget" => "tbl_benutzer",   "newTargetSchema" => "public", "pickDataFrom" => "tbl_benutzer", "pickDataFromCol" => "uid"        ),
	array("schema" => "campus", "name" => "tbl_studentuebung",   "from" => "student_uid", "to" => "uid",           "datatype" => "varchar(32)", "newTarget" => "tbl_benutzer",   "newTargetSchema" => "public", "pickDataFrom" => "tbl_benutzer", "pickDataFromCol" => "uid"        ),
	array("schema" => "campus", "name" => "tbl_legesamtnote",    "from" => "student_uid", "to" => "prestudent_id", "datatype" => "int",         "newTarget" => "tbl_prestudent", "newTargetSchema" => "public", "pickDataFrom" => "tbl_student",  "pickDataFromCol" => "student_uid"),
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
	if(!$db->db_query("DROP VIEW bis.vw_bisio"))
	{
		echo "<p>Could not DROP view bis.vw_bisio: " . $create_view_qry."</p>";
	}


	//modify all tables
	foreach($all_tables_to_update as $t)
		modifyOneTable($db, $t);







	//********************************CREATE ALL VIEWS AGAIN********************************
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

				if(!$index_drop_result = $db->db_query('ALTER TABLE '.$table["schema"].".".$table["name"].' DROP CONSTRAINT '.$row->indexname))
				{
					echo "<p><span style='color:red;'>ACHTUNG:</span> DROPPEN von ".$row->indexname." fehlgeschlagen(wird übersprungen)</p>";
					$db->db_query("ROLLBACK;");
					return;
				}

				$constraint_add_query = str_replace ($table["from"], $table["to"], $def->pg_get_constraintdef );
				$primary_keys[] = 'ALTER TABLE '.$table["schema"].".".$table["name"].' ADD CONSTRAINT '.$row->indexname.' '.$constraint_add_query;
			}
			else
			{
				if(!$index_drop_result = $db->db_query('ALTER TABLE '.$table["schema"].".".$table["name"].' DROP INDEX '.$row->indexname))
				{
					echo "<p><span style='color:red;'>ACHTUNG:</span> DROPPEN von INDEX ".$row->indexname." fehlgeschlagen(wird übersprungen)</p>";
					$db->db_query("ROLLBACK;");
					return;
				}

				$index_add_query = str_replace ($table["from"], $table["to"], $row->indexdef );
				$indices[] = 'ALTER TABLE '.$table["schema"].".".$table["name"].' ADD CONSTRAINT '.$row->indexname.' '.$index_add_query;
			}
		}



		//spalte einfuegen
		$alter_update_qry = 'ALTER TABLE '.$table["schema"].'.'.$table["name"].' ADD COLUMN '.$table["to"].' '.$table["datatype"].';
		UPDATE '.$table["schema"].".".$table["name"].' SET '.$table["to"].' = (SELECT '.$table["to"].' FROM '.$table["newTargetSchema"].'.'.$table["pickDataFrom"].' WHERE '.$table["pickDataFromCol"].'='.$table["schema"].'.'.$table["name"].'.'.$table["from"].');';
		$db->db_query($alter_update_qry);


		//constraints: $TO FK, $TO NOT NULL
		$constraint_qry = 'ALTER TABLE '.$table["schema"].".".$table["name"].' ALTER COLUMN '.$table["to"].' SET NOT NULL;
			ALTER TABLE '.$table["schema"].".".$table["name"].' ADD CONSTRAINT fk_'.$table["name"].'_'.$table["newTarget"].'_'.$table["to"].' FOREIGN KEY ('.$table["to"].') REFERENCES '.$table["newTargetSchema"].'.'.$table["newTarget"].' ('.$table["to"].');';

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
					if(!$index_drop_result = $db->db_query($pk))
					{
						echo "<p><span style='color:red;'>ACHTUNG:</span> ADDEN von INDEX ".$row->indexname." fehlgeschlagen(wird übersprungen)</p>";
						$db->db_query("ROLLBACK;");
						return;
					}
				}
				foreach( $indices as $ind)
				{
					if(!$index_drop_result = $db->db_query($ind))
					{
						echo "<p><span style='color:red;'>ACHTUNG:</span> ADDEN von INDEX ".$row->indexname." fehlgeschlagen(wird übersprungen)</p>";
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
