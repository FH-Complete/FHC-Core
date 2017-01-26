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
require_once('../include/studiengang.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/student.class.php');
require_once('../include/studiensemester.class.php');

// Datenbank Verbindung
$db = new basis_db();
echo '<html>
<head>
	<title>Studienplan_id prestudentstatus</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="../skin/vilesci.css" type="text/css" />
	<style>
		th, td
		{
			border: 1px solid black;
			padding: 5px;
		}
		</style>
	<script>
		function startScript()
		{
			var startButton = document.getElementById("startButton")
			startButton.disabled=true;
			startButton.value="Bitte warten...";

			var form = document.createElement("form");
			form.setAttribute("method", "post");
			form.setAttribute("action", "prestudentstatus_studienplan_id.php");

			var hiddenField = document.createElement("input");
			hiddenField.setAttribute("type", "hidden");
			hiddenField.setAttribute("name", "start");
			hiddenField.setAttribute("value", "start");
			form.appendChild(hiddenField);

			document.body.appendChild(form);

			form.submit();
		}

		function showDetails(id)
		{
			var ele = document.getElementById(id);
			if(ele.style.display == "none")
				ele.style.display="block";
			else
				ele.style.display="none";
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

$startString = "Starten";

if(isset($_POST["start"]) && $_POST["start"] == "start")
{
	set_time_limit(10000);

	if($result = $db->db_query("
		SELECT prestudent_id, orgform_kurzbz, studiengang_kz, studiensemester_kurzbz, ausbildungssemester, status_kurzbz
		FROM public.tbl_prestudentstatus
			JOIN public.tbl_prestudent USING(prestudent_id)
		WHERE studienplan_id IS NULL order by 3,4,5"))
	{
		$all_count = $db->db_num_rows($result);
		$entries_not_unique = 0;
		$entries_not_found = 0;
		$entries_with_error = 0;
		$array_stg_without_studienplan = array();
		$array_orgform = array();
		$array_no_orgform = array();
		$array_studienplan_semester = array();
		$array_not_unique = array();

		echo "Es gibt <span style='color:red;'>" . $all_count . "</span> zu bearbeitende Einträge<br>";

		while($row = $db->db_fetch_object($result))
		{
			$qry_search="
				SELECT
					studienplan_id
				FROM
					lehre.tbl_studienplan
					JOIN lehre.tbl_studienordnung USING(studienordnung_id)
				WHERE tbl_studienordnung.studiengang_kz=".$db->db_add_param($row->studiengang_kz)."
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
						SELECT 1 FROM lehre.tbl_studienplan_semester
						WHERE
							studienplan_id=tbl_studienplan.studienplan_id
						AND studiensemester_kurzbz=".$db->db_add_param($row->studiensemester_kurzbz)."
						AND semester=".$db->db_add_param($row->ausbildungssemester,FHC_INTEGER)."
						LIMIT 1
					);
			";

			$result_search = $db->db_query($qry_search);

			$ct = $db->db_num_rows($result_search);
			if($ct < 1)
			{
				// Es wurde kein genau passender Studienplan gefunden

				if(in_array($row->status_kurzbz,array('Abbrecher','Unterbrecher','Diplomand','Absolvent')))
				{
					// Schauen ob fuer das vorherige Studiensemester ein eindeutiger Eintrag vorhanden ist
					// Da bei diesen Statuseintraegen Studiensemster und Ausbildungssemester meist versetzt sind
					// (zB Wintersemester 2. Semester)

					$stsem_obj = new studiensemester();
					$stsem_prev = $stsem_obj->getPreviousFrom($row->studiensemester_kurzbz);

					$qry_search="
						SELECT
							studienplan_id
						FROM
							lehre.tbl_studienplan
							JOIN lehre.tbl_studienordnung USING(studienordnung_id)
						WHERE tbl_studienordnung.studiengang_kz=".$db->db_add_param($row->studiengang_kz)."
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
								SELECT 1 FROM lehre.tbl_studienplan_semester
								WHERE
									studienplan_id=tbl_studienplan.studienplan_id
								AND studiensemester_kurzbz=".$db->db_add_param($stsem_prev)."
								AND semester=".$db->db_add_param($row->ausbildungssemester,FHC_INTEGER)."
								LIMIT 1
							);
					";

					if($result_search_alternativ = $db->db_query($qry_search))
					{
						if($db->db_num_rows($result_search_alternativ)==1)
						{
							// Es wurde ein eindeutiger Eintrag gefunden
							// dieser wird gesetzt
							$sp = $db->db_fetch_object($result_search_alternativ);
							if(!$db->db_query("UPDATE public.tbl_prestudentstatus SET studienplan_id=".$db->db_add_param($sp->studienplan_id, FHC_INTEGER).
								" WHERE prestudent_id=".$db->db_add_param($row->prestudent_id, FHC_INTEGER).
								" AND status_kurzbz=".$db->db_add_param($row->status_kurzbz).
								" AND studiensemester_kurzbz=".$db->db_add_param($row->studiensemester_kurzbz).
								" AND ausbildungssemester=".$db->db_add_param($row->ausbildungssemester, FHC_INTEGER)
								))
								$entries_with_error ++;
							continue;
						}
					}
				}

				// Pruefen ob der Studiengang in diesem Semester Studienplaene hat
				// unabhaengig der orgform
				$qry_analyze3="
				SELECT
					studienplan_id
				FROM
					lehre.tbl_studienplan
					JOIN lehre.tbl_studienordnung USING(studienordnung_id)
				WHERE EXISTS
				(
					SELECT 1 FROM lehre.tbl_studienplan_semester
					WHERE
						studienplan_id=tbl_studienplan.studienplan_id
					AND studiensemester_kurzbz=".$db->db_add_param($row->studiensemester_kurzbz)."
					AND semester=".$db->db_add_param($row->ausbildungssemester,FHC_INTEGER)."
					LIMIT 1
				)
				AND studiengang_kz =
				(
					SELECT studiengang_kz
					FROM public.tbl_prestudent
					WHERE prestudent_id=".$db->db_add_param($row->prestudent_id)."
				);";

				$result_analyze3 = $db->db_query($qry_analyze3);
				$cnt = $db->db_num_rows($result_analyze3);
				if($cnt == 0)
				{
					$key = $row->studiengang_kz." ".$row->ausbildungssemester." ".$row->studiensemester_kurzbz." ".$row->status_kurzbz;
					if(!isset($array_studienplan_semester[$key]))
					{
						$info = array("stg_kz" => $row->studiengang_kz, "ausbildungssemester" => $row->ausbildungssemester, "studiensemester_kurzbz" => $row->studiensemester_kurzbz, "status_kurzbz" => $row->status_kurzbz, "count" => 0);
						$array_studienplan_semester[$key] = $info;
					}
					$array_studienplan_semester[$key]["count"] ++;
					$analyse_gefunden=true;
				}
				elseif($cnt == 1)
				{
					// Wenn es in dem Studiengang genau einen passenden Studienplan gibt
					// dieser jedoch die falsche orgform hat, dann wird dieser trotzdem gesetzt
					// da dies bei alten Studiengaengen nicht immer so genau angelegt wurde
					$sp = $db->db_fetch_object($result_analyze3);
					if(!$db->db_query("UPDATE public.tbl_prestudentstatus SET studienplan_id=".$db->db_add_param($sp->studienplan_id, FHC_INTEGER).
						" WHERE prestudent_id=".$db->db_add_param($row->prestudent_id, FHC_INTEGER).
						" AND status_kurzbz=".$db->db_add_param($row->status_kurzbz).
						" AND studiensemester_kurzbz=".$db->db_add_param($row->studiensemester_kurzbz).
						" AND ausbildungssemester=".$db->db_add_param($row->ausbildungssemester, FHC_INTEGER)
						))
						$entries_with_error ++;
					continue;
				}

				$entries_not_found++;
				$analyse_gefunden=false;

				/* load informations about the student */
				$ps = new prestudent();
				$ps->load($row->prestudent_id);
				$stud = new student();
				$uid = $stud->getUid($row->prestudent_id);

				// Pruefen ob die Orgform generell existiert in einem Studienplan
				$qry_analyze1="
					SELECT
						studienplan_id
					FROM
						lehre.tbl_studienplan
						JOIN lehre.tbl_studienordnung USING(studienordnung_id)
					WHERE lehre.tbl_studienplan.orgform_kurzbz=
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
				";
				$result_analyze1 = $db->db_query($qry_analyze1);
				if($db->db_num_rows($result_analyze1) == 0)
				{
					$orgform = $row->orgform_kurzbz;

					if($orgform == null)
					{
						$qry_analyze1_2="
							SELECT orgform_kurzbz FROM public.tbl_studiengang
							WHERE
								studiengang_kz=(SELECT studiengang_kz FROM public.tbl_prestudent WHERE prestudent_id=".$db->db_add_param($row->prestudent_id,FHC_INTEGER).")";

						$result_analyze1_2 = $db->db_query($qry_analyze1_2);
						if($row3 = $db->db_fetch_object($result_analyze1_2))
							$orgform = $row3->orgform_kurzbz;
					}

					if($orgform == null)
					{
						if(!isset($array_no_orgform[$row->prestudent_id]))
						{
							$info = array("uid" => $uid, "vorname" => $ps->vorname, "nachname" => $ps->nachname, "stg_kz" => $row->studiengang_kz, "count" => 0);
							$array_no_orgform[$row->prestudent_id] = $info;
						}
						$array_no_orgform[$row->prestudent_id]["count"] ++;
					}
					else
					{
						$key = $orgform;
						if(!isset($array_orgform[$key]))
						{
							$info = array("orgform_kurzbz" => $orgform, "count" => 0);
							$array_orgform[$key] = $info;
						}
						$array_orgform[$key]["count"] ++;
					}
					$analyse_gefunden=true;
				}

				// Pruefen ob der Studiengang Studienplaene hat
				$qry_analyze2="
					SELECT
						studienplan_id
					FROM
						lehre.tbl_studienplan
						JOIN lehre.tbl_studienordnung USING(studienordnung_id)
					WHERE tbl_studienordnung.studiengang_kz=".$db->db_add_param($row->studiengang_kz);

				$result_analyze2 = $db->db_query($qry_analyze2);
				if($db->db_num_rows($result_analyze2) == 0)
				{
					$key = $row->studiengang_kz;
					if(!isset($array_stg_without_studienplan[$key]))
					{
						$info = array("stg_kz" => $row->studiengang_kz, "count" => 0);
						$array_stg_without_studienplan[$key] = $info;
					}
					$array_stg_without_studienplan[$key]["count"] ++;
					$analyse_gefunden=true;
				}

				/*if(!$analyse_gefunden)
					var_dump($row);
				*/
			}
			else if($ct > 1)
			{
				// Es wurden mehrere passende Studienplaene gefunden
				// Eindeutige zuordnung nicht moeglich

				$ps = new prestudent();
				$ps->load($row->prestudent_id);
				$stud = new student();
				$uid = $stud->getUid($row->prestudent_id);

				if(!isset($array_not_unique[$row->prestudent_id]))
				{
					$info = array("prestudent_id" => $row->prestudent_id, "uid" => $uid, "vorname" => $ps->vorname, "nachname" => $ps->nachname, "stg_kz" => $row->studiengang_kz, "count" => 0);
					$array_not_unique[$row->prestudent_id] = $info;
				}
				$array_not_unique[$row->prestudent_id]["count"] ++;
				$entries_not_unique++;
			}
			else
			{
				// Es wurde ein eindeutiger Eintrag gefunden
				// dieser wird gesetzt
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
		$color = "black";
		if($quote < 33.3)
			$color = "red";
		else if($quote > 33.3 && $quote < 66.6)
			$color = "orange";
		else
			$color = "green";

		echo "Es wurden <span style='color:red;'>" . $rest_count . "</span> Einträge eingetragen<br>";
		echo $entries_not_found." wurden nicht gefunden<br>";
		echo $entries_not_unique." sind nicht eindeutig<br>";
		echo $entries_with_error." konnten aufgrund eines Fehlers nicht eingetragen werden<br>";
		echo "Es wurde eine quote von <span style='color:$color;'>" . round($quote,2) . "%</span> erreicht<br>";
		echo "<h2 style='margin-top:20px;'>Details</h2>";

		$ct = countIntArray($array_no_orgform);
		if($ct)
		{
			echo $ct . " Eintr&auml;ge ohne Orgform(Prestudentstatus/Studiengang) <input type='button' value='Details' onclick='showDetails(\"no_orgform\")'/><br>";
			echo "<table style='margin-left:10px;display:none;' id='no_orgform'>";
			echo "<th>Anzahl</th><th>Vorname</th><th>Nachname</th><th>UID</th><th>Studiengang</th><th>Studiengang_kz</th>";
			foreach($array_no_orgform as $k => $i)
			{
				$stg = new studiengang();
				$stg->load($i["stg_kz"]);
				echo "<tr><td>".$i["count"]."</td><td>".$i["vorname"]."</td><td>".$i["nachname"]."</td><td>".$i["uid"]."</td><td>".$stg->bezeichnung."</td><td>".$i["stg_kz"]."</td></tr>";
			}
			echo "</table>";
		}

		$ct = countIntArray($array_stg_without_studienplan);
		if($ct)
		{
			echo $ct . " Eintr&auml;ge ohne Studienplan <input type='button' value='Details' onclick='showDetails(\"stg_without_studienplan\")'/><br>";
			echo "<table style='margin-left:10px;display:none;' id='stg_without_studienplan'>";
			echo "<th>Anzahl</th><th>Studiengang</th><th>Studiengang Kennzahl</th><th>Kurzbezeichnung</th>";
			foreach($array_stg_without_studienplan as $k => $i)
			{
				$stg = new studiengang();
				$stg->load($i["stg_kz"]);
				echo "<tr><td>".$i["count"]."</td><td>".$stg->bezeichnung."</td><td>".$i["stg_kz"]."</td><td>".$stg->kurzbzlang."</td></tr>";
			}
			echo "</table>";
		}

		$ct = countIntArray($array_orgform);
		if($ct)
		{
			echo $ct . " Eintr&auml;ge mit unbekannter Orgform <input type='button' value='Details' onclick='showDetails(\"orgform\")'/><br>";
			echo "<table style='margin-left:10px;display:none;' id='orgform'>";
			echo "<th>Anzahl</th><th>Bezeichnung</th>";
			foreach($array_orgform as $k => $i)
			{
				echo "<tr><td>".$i["count"]."</td><td>".$i["orgform_kurzbz"]."</td></tr>";
			}
			echo "</table>";
		}

		$ct = countIntArray($array_studienplan_semester);
		if($ct)
		{
			echo $ct . " Eintr&auml;ge ohne Studienplan - Semester Zuordnung <input type='button' value='Details' onclick='showDetails(\"studienplan_semester\")'/><br>";
			echo "<div style='margin-left:10px;display:none;' id='studienplan_semester'>";
			echo "Gruppierung findet hier nach Studiengang und Prestudentstatus statt";
			echo "<table>";
			echo "<th>Anzahl</th><th>Studiengang</th><th>Studiengang Kennzahl</th><th>Ausbildungssemester</th><th>Semester</th><th>Kurzbezeichnung</th><th>Status</th>";
			foreach($array_studienplan_semester as $k => $i)
			{
				$stg = new studiengang();
				$stg->load($i["stg_kz"]);
				echo "<tr><td>".$i["count"]."</td><td>".$stg->bezeichnung."</td><td>".$i["stg_kz"]."</td><td>".$i["ausbildungssemester"]."</td><td>".$i["studiensemester_kurzbz"]."</td><td>".$stg->kurzbzlang."</td><td>".$i["status_kurzbz"]."</td></tr>";
			}
			echo "</table>";
			echo "</div>";
		}

		$ct = countIntArray($array_not_unique);
		if($ct)
		{
			echo $ct . " Eintr&auml;ge, welche nicht eindeutig ermittelt werden konnten <input type='button' value='Details' onclick='showDetails(\"not_unique\")'/><br>";
			echo "<table style='margin-left:10px;display:none;' id='not_unique'>";
			echo "<th>Anzahl</th><th>Prestudent_id</th><th>Vorname</th><th>Nachname</th><th>UID</th><th>Studiengang</th>";
			foreach($array_not_unique as $k => $i)
			{
				$stg = new studiengang();
				$stg->load($i["stg_kz"]);
				echo "<tr><td>".$i["count"]."</td><td>".$i["prestudent_id"]."</td><td>".$i["vorname"]."</td><td>".$i["nachname"]."</td><td>".$i["uid"]."</td><td>".$stg->bezeichnung."</td></tr>";
			}
			echo "</table>";
		}
	}

	$startString = "Neu Starten";
}
else
{
?>
	<p>Der folgende Vorgang kann unter Umständen mehrere Minuten dauern!</p>
	<p>Es wird versucht anhand der orgform_kurzbz, der prestudent_id, der studiensemester_kurzbz und des ausbildungssemesters die studienplan_id des prestudentstatus zu ermitteln</p>
	<p>Es werden nur Einträge mit studienplan_id IS NULL geändert</p>
	<p>Hinweis:</p>
	<p style="color:orange;">Vor diesem Skript sollte <a style="color:red;text-decoration: underline;" href="checksystem.php">Checksystem</a> ausgef&uuml;hrt werden!</p>
	<p style="color:orange;">Wenn die Studienordnungen nicht vollständig eingepflegt sind, kann <a style="color:red;text-decoration: underline;" href="generate_missing_sto.php">dieses Script</a> ausgef&uuml;hrt werden um Dummy Studienpläne aufgrund von Statuseinträgen zu generieren!</p>

<?php
}
?>
<input style="margin-top: 10px;" type="button" id="startButton" value="<?php echo $startString; ?>" onclick="startScript()"/>
<?php
echo '</body></html>';


function countIntArray($arr)
{
	$count = 0;
	foreach($arr as $k => $i)
	{
		if(is_array($i))
		{
			$count += countIntArray($i);
		}
		else if($k == "count" && is_numeric($i))
		{
			$count += $i;
		}
	}
	return $count;
}
?>
