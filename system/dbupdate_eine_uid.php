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
	"bis.tbl_bisio",
	"campus.tbl_lvgesamtnote",
	"campus.tbl_studentbeispiel",
	"campus.tbl_studentuebung",
	"campus.tbl_legesamtnote",
);

if(!isset($_POST["action"]) || $_POST["action"] != "start")
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
		echo "<form action='dbupdate_eine_uid.php' method='POST' name='startform'><input type='submit' value='start' name='action'></form>";
	}
	else
	{
		echo "<h2>Es sind keine Änderungen vorzunehmen!</h2>";
	}
}
else
{

	echo '<H1>Systemcheck!</H1>';
	echo '<H2>DB-Updates!</H2>';


	// *** Pruefung und hinzufuegen der neuen Attribute und Tabellen
	echo '<H2>Pruefe Tabellen und Attribute!</H2>';


	foreach($all_tables_to_update as $t)
	modifyOneTable($db, $t);
}


function describeOneChange($db, $tablename)
{
	if(!$result = @$db->db_query("SELECT prestudent_id FROM ".$tablename." LIMIT 1;"))
	{
		echo "<div>".$tablename.": student_uid wird <strong style='color:red;'>gelöscht</strong>!</div>";
		echo "<div>".$tablename.": prestudent_id wird <strong style='color:green;'>eingefügt</strong>!</div>";
	}
}

function checkForUpdates($db, $tablename)
{
	if(!$result = @$db->db_query("SELECT prestudent_id FROM ".$tablename." LIMIT 1;"))
	{
		return true;
	}
	return false;
}


function modifyOneTable($db, $tablename)
{
	if(!$result = @$db->db_query("SELECT prestudent_id FROM ".$tablename." LIMIT 1;"))
	{
		//spalte einfuegen
		$add_qry = 'BEGIN; ALTER TABLE '.$tablename.' ADD COLUMN prestudent_id int;
		UPDATE '.$tablename.' SET prestudent_id = (SELECT prestudent_id FROM public.tbl_student WHERE student_uid='.$tablename.'.student_uid);';
		$db->db_query($add_qry);

		//constraints: prestudent_id FK, prestudent_id NOT NULL
		$constraint_qry = 'ALTER TABLE '.$tablename.' ALTER COLUMN prestudent_id SET NOT NULL;
			ALTER TABLE '.$tablename.' ADD CONSTRAINT prestudent_id FOREIGN KEY (prestudent_id) REFERENCES public.tbl_prestudent (prestudent_id);';

		if(!$db->db_query($constraint_qry))
		{
			echo '<strong>'.$tablename.': '.$db->db_last_error().'</strong><br>';
		}
		else
		{
			echo ' '.$tablename.': Spalte prestudent_id hinzugefuegt!<br>';
			echo ' '.$tablename.': Spalte student_uid auf prestudent_id geändert!<br>';
			echo ' '.$tablename.': Spalte prestudent_id constraints eingefuegt!<br>';

		//student_uid löschen
			$qry = 'ALTER TABLE '.$tablename.' DROP COLUMN student_uid;';

			if(!$db->db_query($qry))
			{
				echo '<strong>'.$tablename.': '.$db->db_last_error().'</strong><br>';
				echo '<strong>'.$tablename.': ACHTUNG! In diesem Fall sollte prestudent_id ordnungsgemäß eingetragen sein, jedoch student_uid nicht gelöscht worden sein. Das Skript erneut zu starten wird nicht funktionieren!</strong><br>';
			}
			else
			{
				echo ' '.$tablename.': Spalte student_uid gelöscht!<br>';
				$db->db_query("COMMIT;");
				return;
			}
		}
	}
	$db->db_query("ROLLBACK;");
}





$tabellen=array(
	"bis.tbl_bisio"  => array("bisio_id","mobilitaetsprogramm_code","nation_code","von","bis","zweck_code","updateamum","updatevon","insertamum","insertvon", "ext_id","ort","universitaet","lehreinheit_id","prestudent_id"),
	"campus.tbl_lvgesamtnote"  => array("lehrveranstaltung_id","studiensemester_kurzbz","note","mitarbeiter_uid","benotungsdatum","freigabedatum","bemerkung","freigabevon_uid","prestudent_id","punkte","ext_id","updateamum","updatevon","insertamum","insertvon"),
);

echo '<H2>Gegenpruefung!</H2>';
$error=false;
$sql_query="SELECT schemaname,tablename FROM pg_catalog.pg_tables WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema' AND schemaname != 'sync' AND schemaname != 'addon';";
if (!$result=@$db->db_query($sql_query))
		echo '<BR><strong>'.$db->db_last_error().' </strong><BR>';
	else
		while ($row=$db->db_fetch_object($result))
		{
			$fulltablename=$row->schemaname.'.'.$row->tablename;
			if (isset($tabellen[$fulltablename]))
				if (!$result_fields=@$db->db_query("SELECT * FROM $fulltablename LIMIT 1;"))
					echo '<BR><strong>'.$db->db_last_error().' </strong><BR>';
				else
					for ($i=0; $i<$db->db_num_fields($result_fields); $i++)
					{
						$found=false;
						$fieldnameDB=$db->db_field_name($result_fields,$i);
						foreach ($tabellen[$fulltablename] AS $fieldnameARRAY)
							if ($fieldnameDB==$fieldnameARRAY)
							{
								$found=true;
								break;
							}
						if (!$found)
						{
							echo 'Attribut '.$fulltablename.'.<strong>'.$fieldnameDB.'</strong> existiert in der DB, aber nicht in diesem Skript!<BR>';
							$error=true;
						}
					}
		}
if($error==false)
	echo '<br>Gegenpruefung fehlerfrei';

echo '</body></html>';
?>
