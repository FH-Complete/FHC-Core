<?php
/* Copyright (C) 2016 Technikum-Wien
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
 * Authors: Andreas Moik 		<moik@technikum-wien.at>
 */


require_once('../config/system.config.inc.php');
require_once('../include/basis_db.class.php');
require_once('../version.php');
require_once('../include/benutzerberechtigung.class.php');

// Datenbank Verbindung
$db = new basis_db();
echo '<html>
<head>
	<title>Studienplan_id prestudentstatus</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="../skin/vilesci.css" type="text/css" />
	<script>
		function hideStartButton()
		{
			document.getElementById("startButton").disabled=true;
			document.getElementById("startButton").value="Bitte warten...";
		}
	</script>
</head>
<body>
<h2>Studienplan_id Update</h2>';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('admin'))
{
	exit('Sie haben keine Berechtigung');
}


if(isset($_POST["start"]) && $_POST["start"] == "start")
{
	if($result = $db->db_query("SELECT prestudent_id, orgform_kurzbz, studiensemester_kurzbz, ausbildungssemester, status_kurzbz FROM public.tbl_prestudentstatus WHERE studienplan_id IS NULL"))
	{
		$all_count = $db->db_num_rows($result);
		$entries_not_unique = 0;
		$entries_not_found = 0;
		$entries_with_error = 0;

		echo "Es gibt <span style='color:red;'>" . $all_count . "</span> zu bearbeitende Einträge<br>";

		while($row = $db->db_fetch_object($result))
		{
			$qry_search="
				SELECT
					studienplan_id
				FROM
					lehre.tbl_studienplan
					JOIN lehre.tbl_studienordnung USING(studienordnung_id)
				WHERE tbl_studienordnung.studiengang_kz=
				(
					SELECT studiengang_kz
					FROM public.tbl_prestudent
					WHERE prestudent_id=".$db->db_add_param($row->prestudent_id,FHC_INTEGER)."
				)
				AND lehre.tbl_studienplan.orgform_kurzbz=
				(
					SELECT COALESCE
					(
						".$db->db_add_param($row->orgform_kurzbz).",
						(
							SELECT orgform_kurzbz FROM public.tbl_studiengang
							WHERE
								studiengang_kz=(SELECT studiengang_kz FROM public.tbl_prestudent WHERE prestudent_id=".$db->db_add_param($row->prestudent_id,FHC_INTEGER).")
		 				)
					)
				)
				AND EXISTS
				(
					SELECT * FROM lehre.tbl_studienplan_semester
					WHERE
						studienplan_id=tbl_studienplan.studienplan_id
					AND studiensemester_kurzbz=".$db->db_add_param($row->studiensemester_kurzbz)."
					AND semester=".$db->db_add_param($row->ausbildungssemester,FHC_INTEGER)."
				);
			";


			$result_search = $db->db_query($qry_search);

			$ct = $db->db_num_rows($result_search);
			if($ct < 1)
			{
				$entries_not_found++;
			}
			else if($ct > 1)
			{
				$entries_not_unique++;
			}
			else
			{
				$sp = $db->db_fetch_object($result_search);
				if(!$db->db_query("UPDATE public.tbl_prestudentstatus SET studienplan_id=".$db->db_add_param($sp->studienplan_id, FHC_INTEGER).
					" WHERE prestudent_id=".$db->db_add_param($row->prestudent_id, FHC_INTEGER).
					" AND status_kurzbz=".$db->db_add_param($row->status_kurzbz).
					" AND studiensemester_kurzbz=".$db->db_add_param($row->studiensemester_kurzbz).
					" AND ausbildungssemester=".$db->db_add_param($row->ausbildungssemester, FHC_INTEGER)
					))
					$entries_with_error ++;
			}
		}

		$rest_count = $all_count - $entries_not_found - $entries_not_unique - $entries_with_error;
		$quote = 100/$all_count*$rest_count;

		echo "Es wurden <span style='color:red;'>" . $rest_count . "</span> Einträge eingetragen<br>";
		echo $entries_not_found." nicht gefunden<br>";
		echo $entries_not_unique." nicht eindeutig<br>";
		echo $entries_with_error." konnten aufgrund eines Fehlers nicht eingetragen werden<br>";
		echo "Es wurde eine quote von " . round($quote,2) . "% erreicht<br>";
	}
}
else
{
?>
	<p>Der folgende Vorgang kann unter Umständen mehrere Minuten dauern!</p>
	<p>Es wird versucht anhand der orgform_kurzbz, der prestudent_id, der studiensemester_kurzbz und des ausbildungssemesters die studienplan_id des prestudentstatus zu ermitteln</p>
	<p>Es werden nur Einträge mit studienplan_id IS NULL geändert</p>
	<p>Hinweis:</p>
	<p>Die Tabelle lehre.tbl_studienplan_semester muss hierfür bereits durch CHECKSYSTEM angelegt UND befüllt sein. Andernfalls kann keine studienplan_id gefunden werden!</p>

	<form onclick="hideStartButton()" action='prestudentstatus_studienplan_id.php' method='POST'>
		<input type="hidden" name="start" value="start" />
		<input id="startButton" type="submit" value="Start" />
	</form>
<?php
}

echo '</body></html>';
?>
