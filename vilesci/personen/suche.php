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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/authentication.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user=get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('basis/person') && !$rechte->isBerechtigt('student/stammdaten') && !$rechte->isBerechtigt('mitarbeiter/stammdaten'))
	die($rechte->errormsg);

if(isset($_GET['searchstr']))
	$searchstr = $_GET['searchstr'];
else
	$searchstr = '';

$msgString = "";
$errString = "";

if(isset($_GET['filter']))
	$filter = $_GET['filter'];
else
	$filter = '';

$datum_obj = new datum();

$admin = false;
if($rechte->isBerechtigt('admin'))
	$admin = true;

if($admin)
{
	if(isset($_REQUEST["delete"]))
	{
		if(isset($_REQUEST["mitarbeiter"]))
		{
			$mita = new mitarbeiter();
			$mita->load($_REQUEST["mitarbeiter"]);

			if(!casDeleteMitarbeiter($db, $_REQUEST["mitarbeiter"]))
				$errString = 'Fehler beim Loeschen des Mitarbeiter-Datensatzes';
			else
			{
				$msgString = ($mita->geschlecht == "w" ? "Mitarbeiterin '" : "Mitarbeiter '") . $mita->vorname . " " . $mita->nachname . "' wurde erfolgreich geloescht";
			}
		}
		else if(isset($_REQUEST["prestudent"]))
		{
			$pres = new prestudent();
			$pres->load($_REQUEST["prestudent"]);

			if(!casDeletePrestudent($db, $_REQUEST["prestudent"]))
				$errString = 'Fehler beim Loeschen des Prestudent-Datensatzes';
			else
			{
				$msgString = ($pres->geschlecht == "w" ? "Studentin '" : "Student '") . $pres->vorname . " " . $pres->nachname . "' wurde erfolgreich geloescht";
			}
		}
		else if(isset($_REQUEST["person"]))
		{
			$pers = new person();
			$pers->load($_REQUEST["person"]);

			if(!casDeletePerson($db, $_REQUEST["person"]))
				$errString = 'Fehler beim Loeschen des Person-Datensatzes';
			else
			{
				$msgString = "Person '" . $pers->vorname . " " . $pers->nachname . "' wurde erfolgreich geloescht";
			}
		}
	}
}

echo '
<html>
<head>
<title>Suchergebnis</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<script>
	function delPerson(type, info)
	{
		if(!confirm("!!!ACHTUNG!!!\nDie Aktion löscht alle Daten der Person im System.\n"
					+"Das betrifft auch alle PreStudierenden- sowie ggf. MitarbeiterInnen-Daten\n\n"
					+"Sind Sie sich sicher?"))
			return;



		var form = document.createElement("form");
		form.setAttribute("method", "get");
		form.setAttribute("action", "suche.php");
		form.setAttribute("target", "_self");

		if(type == "prestudent")
		{
			var hiddenField = document.createElement("input");
			hiddenField.setAttribute("type", "hidden");
			hiddenField.setAttribute("name", "prestudent");
			hiddenField.setAttribute("value", info);
			form.appendChild(hiddenField);
		}
		else if(type == "person")
		{
			var hiddenField = document.createElement("input");
			hiddenField.setAttribute("type", "hidden");
			hiddenField.setAttribute("name", "person");
			hiddenField.setAttribute("value", info);
			form.appendChild(hiddenField);
		}
		else if(type == "mitarbeiter")
		{
			var hiddenField = document.createElement("input");
			hiddenField.setAttribute("type", "hidden");
			hiddenField.setAttribute("name", "mitarbeiter");
			hiddenField.setAttribute("value", info);
			form.appendChild(hiddenField);
		}

		';
		if(isset($_REQUEST["searchstr"]))
		{
			echo '
				var hiddenField = document.createElement("input");
				hiddenField.setAttribute("type", "hidden");
				hiddenField.setAttribute("name", "searchstr");
				hiddenField.setAttribute("value", "'.$_REQUEST["searchstr"].'");
				form.appendChild(hiddenField);
			';
		}
		if(isset($_REQUEST["filter"]))
		{
			echo '
			var hiddenField = document.createElement("input");
			hiddenField.setAttribute("type", "hidden");
			hiddenField.setAttribute("name", "filter");
			hiddenField.setAttribute("value", "'.$_REQUEST["filter"].'");
			form.appendChild(hiddenField);
			';
		}
		echo '
		var hiddenField = document.createElement("input");
		hiddenField.setAttribute("type", "hidden");
		hiddenField.setAttribute("name", "delete");
		hiddenField.setAttribute("value", "true");
		form.appendChild(hiddenField);

		document.body.appendChild(form);
		form.submit();
	}
</script>
</head>

<body class="background_main">
<h2>Personensuche</h2>';

$stg = new studiengang();
$stg->getAll('typ, kurzbz', false);


$stg_arr = array();
foreach ($stg->result as $row)
	$stg_arr[$row->studiengang_kz]=$row->kuerzel;
echo '
	<form accept-charset="UTF-8" name="search" method="GET">
  		Bitte Suchbegriff eingeben:
  		<input type="text" name="searchstr" size="30" value="'.$db->convert_html_chars($searchstr).'">
  		<input type="radio" name="filter" value="mitarbeiter" '.($filter=='mitarbeiter'?'checked="checked"':'').'> Nur MitarbeiterInnen
  		<input type="radio" name="filter" value="student"  '.($filter=='student'?'checked="checked"':'').'> Nur Studierende
  		<input type="submit" value="Suchen">
  	</form>';

echo "<p style='color:#A00;padding:5px;'>$errString</p>";
echo "<p style='color:#0A0;padding:5px;'>$msgString</p>";

if($searchstr!='')
{
	$qry = "SELECT
				distinct on (nachname, vorname, person_id) *
			FROM
				public.tbl_person
				LEFT JOIN public.tbl_benutzer USING(person_id)";

			if ($filter=='mitarbeiter')
				$qry .= " JOIN public.tbl_mitarbeiter ON (uid=mitarbeiter_uid) ";
			elseif ($filter=='student')
				$qry .= " JOIN public.tbl_prestudent USING (person_id) ";

			$qry .= " WHERE true
			AND 	nachname ~* '".$db->db_escape($searchstr)."' OR
					vorname ~* '".$db->db_escape($searchstr)."' OR
					(nachname || ' ' || vorname) ~* '".$db->db_escape($searchstr)."' OR
					(vorname || ' ' || nachname) ~* '".$db->db_escape($searchstr)."' OR
					uid=".$db->db_add_param($searchstr)."
			ORDER BY nachname, vorname;";

	if($result = $db->db_query($qry))
	{
		$auth = new authentication();

		echo $db->db_num_rows($result).' Person(en) gefunden<br><br>';
		echo '<table>';
		echo '<tr class="liste" align="center">';
		echo "<td colspan='6'><b>Person</b></td>";
		echo "<td colspan='4'><b>Benutzer</b></td>";
		echo "<td colspan='4'><b>Mitarbeiter</b></td>";
		echo "<td colspan='4'><b>Student</b></td>";
		if($admin){echo "<td><b></b></td>";}
		echo '</tr>';
		echo '<tr class="liste" align="center">';
		echo "<td><b>Person ID</b></td>";
		echo "<td><b>Nachname</b></td>";
		echo "<td><b>Vorname</b></td>";
		echo "<td><b>Gebdatum</b></td>";
		echo "<td><b>updateAmUm</b></td>";
		echo "<td><b>updateVon</b></td>";
		echo "<td><b>Status</b></td>";
		echo "<td><b>UID</b></td>";
		echo "<td><b>Aktiv</b></td>";
		echo "<td><b>LDAP</b></td>";
		echo "<td><b>Telefon</b></td>";
		echo "<td><b>Ort</b></td>";
		echo "<td><b>updateAmUm</b></td>";
		echo "<td><b>updateVon</b></td>";
		echo "<td><b>Stg</b></td>";
		echo "<td><b>Gruppe</b></td>";
		echo "<td><b>updateAmUm</b></td>";
		echo "<td><b>updateVon</b></td>";
		if($admin){echo "<td></td>";}
		echo '</tr>';

		while($row = $db->db_fetch_object($result))
		{
			$qry = "SELECT * FROM public.tbl_person WHERE person_id='$row->person_id'";
			if($result_person = $db->db_query($qry))
			{
				if($row_person = $db->db_fetch_object($result_person))
				{
					echo '<tr class="liste1">';
					echo "<td>$row_person->person_id</td>";
					echo "<td><a href='personen_details.php?person_id=$row_person->person_id'>$row_person->nachname</a></td>";
					echo "<td>$row_person->vorname</td>";
					echo "<td>".($row_person->gebdatum!=''?$datum_obj->convertISODate($row_person->gebdatum):'')."</td>";
					echo "<td>".($row_person->updateamum!=''?date('d.m.Y H:i:s', $datum_obj->mktime_FROMtimestamp($row_person->updateamum)):'')."</td>";
					echo "<td>$row_person->updatevon</td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					if($admin){echo '<td><img style="cursor: pointer;" src="../../skin/images/cross.png" alt="Löschen" onclick="delPerson(\'person\', \''.$row_person->person_id.'\')"/></td>';}
					echo '</tr>';

					$content = '';
					$qry = "SELECT
									*, tbl_benutzer.updateamum as bnupdateamum, tbl_benutzer.updatevon as bnupdatevon,
									tbl_mitarbeiter.updateamum as mupdateamum, tbl_mitarbeiter.updatevon as mupdatevon
							FROM public.tbl_mitarbeiter JOIN public.tbl_benutzer on(uid=mitarbeiter_uid)
							WHERE person_id=".$db->db_add_param($row->person_id, FHC_INTEGER);
					if($result_mitarbeiter = $db->db_query($qry))
					{
						if($db->db_num_rows($result_mitarbeiter)>0)
						{

							while($row_mitarbeiter = $db->db_fetch_object($result_mitarbeiter))
							{
								$content.= '<tr >';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= "<td>Mitarbeiter</td>";
								$content.= "<td><a href='personen_details.php?uid=$row_mitarbeiter->uid'>$row_mitarbeiter->uid</a></td>";
								$content.= "<td>".($row_mitarbeiter->aktiv=='t'?'Ja':'Nein')."</td>";

								$content.= "<td>";
								if($auth->UserExternalExists($row_mitarbeiter->uid))
									$content.="Ja";
								else
									$content.="Nein";
								$content.= "</td>";
								//$content.= "<td>".($row_mitarbeiter->bnupdateamum!=''?date('d.m.Y H:i:s', $datum_obj->mktime_FROMtimestamp($row_mitarbeiter->bnupdateamum)):'')."</td>";
								//$content.= "<td>$row_mitarbeiter->bnupdatevon</td>";

								$content.= "<td>$row_mitarbeiter->telefonklappe</td>";
								$content.= "<td>$row_mitarbeiter->ort_kurzbz</td>";
								$content.= "<td>".($row_mitarbeiter->mupdateamum!=''?date('d.m.Y H:i:s', $datum_obj->mktime_FROMtimestamp($row_mitarbeiter->mupdateamum)):'')."</td>";
								$content.= "<td>$row_mitarbeiter->mupdatevon</td>";
								$content.= "<td></td>";
								$content.= "<td></td>";
								$content.= "<td></td>";
								$content.= "<td></td>";
								if($admin){$content.= '<td><img style="cursor: pointer;" src="../../skin/images/cross.png" alt="Löschen" onclick="delPerson(\'mitarbeiter\', \''.$row_mitarbeiter->uid.'\')"/></td>';}
								$content.= '</tr>';
							}
						}
					}

					$qry = "SELECT *, tbl_benutzer.updateamum as bnupdateamum, tbl_benutzer.updatevon as bnupdatevon,
									tbl_student.updateamum as supdateamum, tbl_student.updatevon as supdatevon
							FROM public.tbl_student JOIN public.tbl_benutzer ON(student_uid=uid)
							WHERE person_id=".$db->db_add_param($row->person_id, FHC_INTEGER);
					if($result_student = $db->db_query($qry))
					{
						if($db->db_num_rows($result_student))
						{

							while($row_student = $db->db_fetch_object($result_student))
							{
								$student = new prestudent();
								$student->getLastStatus($row_student->prestudent_id);

								$content.= '<tr>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= "<td>$student->status_kurzbz</td>";
								$content.= "<td><a href='personen_details.php?uid=$row_student->uid'>$row_student->uid</a></td>";
								$content.= "<td>".($row_student->aktiv=='t'?'Ja':'Nein')."</td>";

								$content.= "<td>";
								if($auth->UserExternalExists($row_student->uid))
									$content.="Ja";
								else
									$content.="Nein";
								$content.= "</td>";
								//$content.= "<td>".($row_student->bnupdateamum!=''?date('d.m.Y H:i:s', $datum_obj->mktime_FROMtimestamp($row_student->bnupdateamum)):'')."</td>";
								//$content.= "<td>$row_student->bnupdatevon</td>";

								$content.= "<td></td>";
								$content.= "<td></td>";
								$content.= "<td></td>";
								$content.= "<td></td>";
								$content.= "<td>".$stg_arr[$row_student->studiengang_kz]."</td>";
								$content.= "<td>$row_student->semester$row_student->verband$row_student->gruppe</td>";
								$content.= "<td>".($row_student->supdateamum!=''?date('d.m.Y H:i:s', $datum_obj->mktime_FROMtimestamp($row_student->supdateamum)):'')."</td>";
								$content.= "<td>$row_student->supdatevon</td>";
								if($admin){$content.= '<td><img style="cursor: pointer;" src="../../skin/images/cross.png" alt="Löschen" onclick="delPerson(\'prestudent\', \''.$row_student->prestudent_id.'\')"/></td>';}
								$content.= '</tr>';
							}
						}
					}

					echo $content;
				}
			}
		}
		echo '</table>';
	}

}

/*
 * Cascading delete functions
 */
function casDeleteMitarbeiter($db, $mitarbeiter_uid, $trans=true)
{
	/*
	 * Init
	 */
	if($trans){$db->db_query("BEGIN;");}
	$error = false;

	$projektphase_ids = array();

	// get all projektphase_ids, where the employee is involved with
	if(!$error)
	{
		$qry = '
			SELECT projektphase_id FROM fue.tbl_projektphase
				WHERE ressource_id IN (SELECT ressource_id FROM fue.tbl_ressource WHERE mitarbeiter_uid='.$db->db_add_param($mitarbeiter_uid).')';
		$res = $db->db_query($qry);
		if(!$res)
		{
			$error = true;
		}
		else
		{
			while($row = $db->db_fetch_object($res))
			{
				// and get FROM this projektphase_ids all other projekphase_ids recursively
				$add = recursiveGetAllProjektphase_id($db, $row->projektphase_id);
				if($add)
					$projektphase_ids = array_merge($projektphase_ids, $add);
			}
		}
	}

	if(!$error && !empty($projektphase_ids))
	{
		$qry = '
			DELETE FROM fue.tbl_projekt_ressource
				WHERE projektphase_id IN ('.$db->implode4SQL($projektphase_ids).')';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM fue.tbl_projekttask
				WHERE ressource_id IN (SELECT ressource_id FROM fue.tbl_ressource WHERE mitarbeiter_uid='.$db->db_add_param($mitarbeiter_uid).')';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error && !empty($projektphase_ids))
	{
		$qry = '
			DELETE FROM fue.tbl_projekt_dokument
				WHERE projektphase_id IN ('.$db->implode4SQL($projektphase_ids).')';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error && !empty($projektphase_ids))
	{
		$qry = '
			DELETE FROM fue.tbl_projekttask
				WHERE projektphase_id IN ('.$db->implode4SQL($projektphase_ids).')';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error && !empty($projektphase_ids))
	{
		$qry = '
			DELETE FROM fue.tbl_projektphase
				WHERE projektphase_id IN ('.$db->implode4SQL($projektphase_ids).')';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$tqry = "SELECT 1
			FROM INFORMATION_SCHEMA.TABLES
			WHERE table_type='BASE TABLE'
				AND table_schema='addon'
				AND table_name='tbl_lvevaluierung_selbstevaluierung';";

		if(!$result = $db->db_query($tqry))
			$error = true;

		if($db->db_num_rows($result))
		{
			$qry = '
				DELETE FROM addon.tbl_lvevaluierung_selbstevaluierung
					WHERE lvevaluierung_id IN (SELECT lvevaluierung_id FROM addon.tbl_lvevaluierung_antwort
						WHERE lvevaluierung_code_id IN (SELECT lvevaluierung_code_id FROM addon.tbl_lvevaluierung_code
							WHERE lvevaluierung_id IN (SELECT lvevaluierung_id FROM addon.tbl_lvevaluierung
								WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM campus.tbl_studentbeispiel
									WHERE beispiel_id IN (SELECT beispiel_id FROM campus.tbl_beispiel
										WHERE uebung_id IN (SELECT uebung_id FROM campus.tbl_uebung
											WHERE lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
												WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung
													WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).'))))))))';
			if(!$db->db_query($qry))
				$error = true;
		}
	}

	if(!$error)
	{
		$tqry = "SELECT 1
			FROM INFORMATION_SCHEMA.TABLES
			WHERE table_type='BASE TABLE'
				AND table_schema='addon'
				AND table_name='tbl_lvevaluierung_antwort';";

		if(!$result = $db->db_query($tqry))
			$error = true;

		if($db->db_num_rows($result))
		{
			$qry = '
				DELETE FROM addon.tbl_lvevaluierung_antwort
					WHERE lvevaluierung_code_id IN (SELECT lvevaluierung_code_id FROM addon.tbl_lvevaluierung_code
						WHERE lvevaluierung_id IN (SELECT lvevaluierung_id FROM addon.tbl_lvevaluierung
							WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM campus.tbl_studentbeispiel
								WHERE beispiel_id IN (SELECT beispiel_id FROM campus.tbl_beispiel
									WHERE uebung_id IN (SELECT uebung_id FROM campus.tbl_uebung
										WHERE lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
											WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung
												WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).')))))))';
			if(!$db->db_query($qry))
				$error = true;
		}
	}


	if(!$error)
	{
		$tqry = "SELECT 1
			FROM INFORMATION_SCHEMA.TABLES
			WHERE table_type='BASE TABLE'
				AND table_schema='addon'
				AND table_name='tbl_lvevaluierung_code';";

		if(!$result = $db->db_query($tqry))
			$error = true;

		if($db->db_num_rows($result))
		{
			$qry = '
				DELETE FROM addon.tbl_lvevaluierung_code
					WHERE lvevaluierung_id IN (SELECT lvevaluierung_id FROM addon.tbl_lvevaluierung
						WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM campus.tbl_studentbeispiel
							WHERE beispiel_id IN (SELECT beispiel_id FROM campus.tbl_beispiel
								WHERE uebung_id IN (SELECT uebung_id FROM campus.tbl_uebung
									WHERE lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
										WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung
											WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).'))))))';
			if(!$db->db_query($qry))
				$error = true;
		}
	}

	if(!$error)
	{
		$tqry = "SELECT 1
			FROM INFORMATION_SCHEMA.TABLES
			WHERE table_type='BASE TABLE'
				AND table_schema='addon'
				AND table_name='tbl_lvevaluierung';";

		if(!$result = $db->db_query($tqry))
			$error = true;

		if($db->db_num_rows($result))
		{
			$qry = '
				DELETE FROM addon.tbl_lvevaluierung
					WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM campus.tbl_studentbeispiel
						WHERE beispiel_id IN (SELECT beispiel_id FROM campus.tbl_beispiel
							WHERE uebung_id IN (SELECT uebung_id FROM campus.tbl_uebung
								WHERE lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
									WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung
										WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).')))))';
			if(!$db->db_query($qry))
				$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_studentbeispiel
				WHERE beispiel_id IN (SELECT beispiel_id FROM campus.tbl_beispiel
					WHERE uebung_id IN (SELECT uebung_id FROM campus.tbl_uebung
						WHERE lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
							WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung
								WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).'))))';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_beispiel
				WHERE uebung_id IN (SELECT uebung_id FROM campus.tbl_uebung
					WHERE lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
						WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung
							WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).')))';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_studentuebung
				WHERE uebung_id IN (SELECT uebung_id FROM campus.tbl_uebung
					WHERE lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
						WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung
							WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).')))';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_uebung
				WHERE lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
					WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung
						WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).'))';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_legesamtnote
				WHERE lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
					WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung
						WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).'))';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM public.tbl_preincoming_lehrveranstaltung
				WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung
						WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).')';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_benutzerlvstudiensemester
				WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung
						WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).')';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_feedback
				WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung
						WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).')';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_feedback
				WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung
						WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).')';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM lehre.tbl_projektbetreuer
				WHERE projektarbeit_id IN (SELECT projektarbeit_id FROM lehre.tbl_projektarbeit
					WHERE lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
						WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung
							WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).')))';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM bis.tbl_bisfunktion
				WHERE bisverwendung_id IN (SELECT bisverwendung_id FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid='.$db->db_add_param($mitarbeiter_uid).')';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM fue.tbl_projektphase
				WHERE ressource_id IN (SELECT ressource_id FROM fue.tbl_ressource WHERE mitarbeiter_uid='.$db->db_add_param($mitarbeiter_uid).')';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM fue.tbl_projekt_ressource
				WHERE ressource_id IN (SELECT ressource_id FROM fue.tbl_ressource WHERE mitarbeiter_uid='.$db->db_add_param($mitarbeiter_uid).')';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM lehre.tbl_stundenplan
				WHERE lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
					WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung
						WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).'))';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}


	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_paabgabe
				WHERE projektarbeit_id IN (SELECT projektarbeit_id FROM lehre.tbl_projektarbeit
					WHERE lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
						WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung
							WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).')))';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM lehre.tbl_projektarbeit
				WHERE lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).'))';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM lehre.tbl_pruefung
				WHERE lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).'))';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM lehre.tbl_stundenplandev
				WHERE lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).'))';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM lehre.tbl_studienplan_lehrveranstaltung
				WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).')';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM lehre.tbl_zeugnisnote
				WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).')';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM lehre.tbl_lehreinheit
				WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).')';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_lvgesamtnote
				WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).')';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_lvinfo
				WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung WHERE koordinator='.$db->db_add_param($mitarbeiter_uid).')';
		if(!$db->db_query($qry))
		{
			$error = true;
		}
	}

	if(!$error)
	{
		if(!deleteWithFirstlevelDeps($db, 'mitarbeiter_uid', 'public', 'tbl_mitarbeiter', $mitarbeiter_uid, FHC_STRING))
		{
			$error = true;
		}
	}

	/*
	 * Delete the tbl_mitarbeiter entry
	 */
	$qry = 'DELETE FROM public.tbl_mitarbeiter where mitarbeiter_uid='.$db->db_add_param($mitarbeiter_uid);
	if(!$error && !$db->db_query($qry))
		$error = true;

	/*
	 * Rollback if an
	 * error occoured
	 */
	if(!$error)
	{

	if($trans){$db->db_query("COMMIT;");}
		return true;
	}


	if($trans){$db->db_query("ROLLBACK;");}
	return false;
}






function casDeletePrestudent($db, $prestudent_id, $trans=true)
{
	/*
	 * Init
	 */
	if($trans){$db->db_query("BEGIN;");}
	$error = false;

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_studentbeispiel
				WHERE student_uid IN (SELECT student_uid FROM public.tbl_student WHERE prestudent_id='.$db->db_add_param($prestudent_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM bis.tbl_bisio
				WHERE student_uid IN (SELECT student_uid FROM public.tbl_student WHERE prestudent_id='.$db->db_add_param($prestudent_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_legesamtnote
				WHERE student_uid IN (SELECT student_uid FROM public.tbl_student WHERE prestudent_id='.$db->db_add_param($prestudent_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_lvgesamtnote
				WHERE student_uid IN (SELECT student_uid FROM public.tbl_student WHERE prestudent_id='.$db->db_add_param($prestudent_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_studentuebung
				WHERE student_uid IN (SELECT student_uid FROM public.tbl_student WHERE prestudent_id='.$db->db_add_param($prestudent_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM lehre.tbl_abschlusspruefung
				WHERE student_uid IN (SELECT student_uid FROM public.tbl_student WHERE prestudent_id='.$db->db_add_param($prestudent_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM lehre.tbl_projektbetreuer
				WHERE projektarbeit_id IN (SELECT projektarbeit_id FROM lehre.tbl_projektarbeit WHERE student_uid IN (SELECT student_uid FROM tbl_student WHERE prestudent_id='.$db->db_add_param($prestudent_id, FHC_INTEGER).'))';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_paabgabe
				WHERE projektarbeit_id IN (SELECT projektarbeit_id FROM lehre.tbl_projektarbeit WHERE student_uid IN (SELECT student_uid FROM tbl_student WHERE prestudent_id='.$db->db_add_param($prestudent_id, FHC_INTEGER).'))';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM lehre.tbl_projektarbeit
				WHERE student_uid IN (SELECT student_uid FROM public.tbl_student WHERE prestudent_id='.$db->db_add_param($prestudent_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM lehre.tbl_zeugnisnote
				WHERE student_uid IN (SELECT student_uid FROM public.tbl_student WHERE prestudent_id='.$db->db_add_param($prestudent_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM public.tbl_studentlehrverband
				WHERE student_uid IN (SELECT student_uid FROM public.tbl_student WHERE prestudent_id='.$db->db_add_param($prestudent_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM lehre.tbl_pruefung
				WHERE student_uid IN (SELECT student_uid FROM public.tbl_student WHERE prestudent_id='.$db->db_add_param($prestudent_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM fue.tbl_projekt_ressource
				WHERE ressource_id IN(SELECT ressource_id FROM fue.tbl_ressource
					WHERE student_uid IN (SELECT student_uid FROM public.tbl_student WHERE prestudent_id='.$db->db_add_param($prestudent_id, FHC_INTEGER).'))';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM fue.tbl_ressource
				WHERE student_uid IN (SELECT student_uid FROM public.tbl_student WHERE prestudent_id='.$db->db_add_param($prestudent_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		if(!deleteWithFirstlevelDeps($db, 'prestudent_id', 'public', 'tbl_prestudent', $prestudent_id, FHC_INTEGER))
		{
			$error = true;
		}
	}

	/*
	 * Delete the tbl_student entry
	 */
	$qry = 'DELETE FROM public.tbl_student where prestudent_id='.$db->db_add_param($prestudent_id, FHC_INTEGER);
	if(!$error && !$db->db_query($qry))
		$error = true;

	/*
	 * Delete the tbl_prestudent entry
	 */
	$qry = 'DELETE FROM public.tbl_prestudent where prestudent_id='.$db->db_add_param($prestudent_id, FHC_INTEGER);
	if(!$error && !$db->db_query($qry))
		$error = true;

	/*
	 * Rollback if an
	 * error occoured
	 */
	if(!$error)
	{

	if($trans){$db->db_query("COMMIT;");}
		return true;
	}


	if($trans){$db->db_query("ROLLBACK;");}
	return false;
}






function casDeletePerson($db, $person_id, $trans=true)
{
	/*
	 * Init
	 */
	if($trans){$db->db_query("BEGIN;");}
	$error = false;

	if(!$error)
	{
		$qry = '
			DELETE FROM public.tbl_prestudentstatus
				WHERE prestudent_id IN (SELECT prestudent_id FROM public.tbl_prestudent WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM public.tbl_dokumentprestudent
				WHERE prestudent_id IN (SELECT prestudent_id FROM public.tbl_prestudent WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_feedback
				WHERE uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM lehre.tbl_studienplan_lehrveranstaltung
				WHERE koordinator IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM public.tbl_msg_recipient
				WHERE message_id IN (SELECT message_id FROM tbl_msg_message WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER) . ')';
		if(!$db->db_query($qry))
			$error = true;
	}
	
	if(!$error)
	{
		$qry = '
			DELETE FROM public.tbl_msg_recipient
				WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER);
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM public.tbl_msg_status
				WHERE message_id IN (SELECT message_id FROM tbl_msg_message WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER) . ')';
		if(!$db->db_query($qry))
			$error = true;
	}
	
	if(!$error)
	{
		$qry = '
			DELETE FROM public.tbl_msg_status
				WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER);
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM public.tbl_msg_message
				WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER);
		if(!$db->db_query($qry))
			$error = true;
	}
	
	if(!$error)
	{
		$qry = '
			DELETE FROM public.tbl_adresse
				WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER);
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM public.tbl_akte
				WHERE uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}
	
	if(!$error)
	{
		$qry = '
			DELETE FROM public.tbl_akte
				WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER);
		if(!$db->db_query($qry))
			$error = true;
	}
	
	if(!$error)
	{
		$qry = '
			DELETE FROM public.tbl_preincoming
				WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER);
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$tqry = "SELECT 1
			FROM INFORMATION_SCHEMA.TABLES
			WHERE table_type='BASE TABLE'
				AND table_schema='addon'
				AND table_name='tbl_lvinfostatus_zuordnung';";

		if(!$result = $db->db_query($tqry))
			$error = true;

		if($db->db_num_rows($result))
		{
			$qry = '
				DELETE FROM addon.tbl_lvinfostatus_zuordnung
					WHERE uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
			if(!$db->db_query($qry))
				$error = true;
		}
	}

	if(!$error)
	{
		$tqry = "SELECT 1
			FROM INFORMATION_SCHEMA.TABLES
			WHERE table_type='BASE TABLE'
				AND table_schema='addon'
				AND table_name='tbl_casetime_gruppen';";

		if(!$result = $db->db_query($tqry))
			$error = true;

		if($db->db_num_rows($result))
		{
			$qry = '
				DELETE FROM addon.tbl_casetime_gruppen
					WHERE uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
			if(!$db->db_query($qry))
				$error = true;
		}
	}

	if(!$error)
	{
		$tqry = "SELECT 1
			FROM INFORMATION_SCHEMA.TABLES
			WHERE table_type='BASE TABLE'
				AND table_schema='addon'
				AND table_name='tbl_casetime_zeitaufzeichnung';";

		if(!$result = $db->db_query($tqry))
			$error = true;

		if($db->db_num_rows($result))
		{
			$qry = '
				DELETE FROM addon.tbl_casetime_zeitaufzeichnung
					WHERE uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
			if(!$db->db_query($qry))
				$error = true;
		}
	}

	if(!$error)
	{
		$tqry = "SELECT 1
			FROM INFORMATION_SCHEMA.TABLES
			WHERE table_type='BASE TABLE'
				AND table_schema='addon'
				AND table_name='tbl_casetime_zeitsperre';";

		if(!$result = $db->db_query($tqry))
			$error = true;

		if($db->db_num_rows($result))
		{
			$qry = '
				DELETE FROM addon.tbl_casetime_zeitsperre
					WHERE uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
			if(!$db->db_query($qry))
				$error = true;
		}
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_zeitaufzeichnung
				WHERE uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_contentlog
				WHERE uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}
	
	if(!$error)
	{
		$qry = '
			DELETE FROM system.tbl_person_lock
				WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER);
		if(!$db->db_query($qry))
			$error = true;
	}
	
	if(!$error)
	{
		$qry = '
			DELETE FROM system.tbl_log
				WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER);
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_coodle_ressource_termin
				WHERE coodle_ressource_id IN (SELECT coodle_ressource_id FROM campus.tbl_coodle_ressource
					WHERE coodle_id IN (SELECT coodle_id FROM campus.tbl_coodle
						WHERE ersteller_uid IN(SELECT uid FROM public.tbl_benutzer
							WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).'
							)
						)
					)';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_coodle_ressource
				WHERE coodle_id IN (SELECT coodle_id FROM campus.tbl_coodle WHERE ersteller_uid IN(SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).'))';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_coodle_termin
				WHERE coodle_id IN (SELECT coodle_id FROM campus.tbl_coodle
					WHERE ersteller_uid IN(SELECT uid FROM public.tbl_benutzer
						WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).'))';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_coodle
				WHERE ersteller_uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_coodle_ressource_termin
				WHERE coodle_ressource_id IN (SELECT coodle_ressource_id FROM campus.tbl_coodle_ressource
					WHERE uid IN (SELECT uid FROM public.tbl_benutzer
						WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).'
					)
				)';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_coodle_ressource
				WHERE uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_zeitaufzeichnung
				WHERE uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_zeitaufzeichnung
				WHERE kunde_uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_news
				WHERE uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM public.tbl_variable
				WHERE uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM public.tbl_prestudentstatus
				WHERE bestaetigtvon IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM wawi.tbl_betriebsmittelperson
				WHERE uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM wawi.tbl_bestellung_bestellstatus
				WHERE uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM wawi.tbl_rechnung
				WHERE bestellung_id IN (SELECT bestellung_id FROM wawi.tbl_bestellung
					WHERE besteller_uid IN(SELECT uid FROM public.tbl_benutzer
						WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).'))';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = 'DELETE FROM wawi.tbl_betriebsmittelperson
				WHERE betriebsmittel_id IN ( SELECT betriebsmittel_id FROM wawi.tbl_betriebsmittel
					WHERE bestellung_id IN (SELECT bestellung_id FROM wawi.tbl_bestellung
						WHERE besteller_uid IN(SELECT uid FROM public.tbl_benutzer
							WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')))';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM wawi.tbl_betriebsmittel
				WHERE bestellung_id IN (SELECT bestellung_id FROM wawi.tbl_bestellung
					WHERE besteller_uid IN(SELECT uid FROM public.tbl_benutzer
						WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).'))';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM wawi.tbl_bestellung
				WHERE besteller_uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM wawi.tbl_bestellung_bestellstatus
				WHERE uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM public.tbl_preinteressentstudiengang
				WHERE preinteressent_id IN (SELECT preinteressent_id FROM public.tbl_preinteressent WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_reservierung
				WHERE veranstaltung_id IN (SELECT veranstaltung_id FROM campus.tbl_veranstaltung
					WHERE freigabevon IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).'))';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM campus.tbl_veranstaltung
				WHERE freigabevon IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$qry = '
			DELETE FROM public.tbl_preoutgoing
				WHERE uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
		if(!$db->db_query($qry))
			$error = true;
	}
	
	if(!$error)
	{
		$qry = '
			WITH deleted_rows AS (
				DELETE FROM public.tbl_notizzuordnung
				WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).'
				RETURNING notiz_id
			)
			DELETE FROM public.tbl_notiz
				WHERE notiz_id IN (SELECT notiz_id FROM deleted_rows)';
		if(!$db->db_query($qry))
			$error = true;
	}

	if(!$error)
	{
		$tqry = "SELECT 1
			FROM INFORMATION_SCHEMA.TABLES
			WHERE table_type='BASE TABLE'
				AND table_schema='addon'
				AND table_name='tbl_lvevaluierung_selbstevaluierung';";

		if(!$result = $db->db_query($tqry))
			$error = true;

		if($db->db_num_rows($result))
		{
			$qry = '
				DELETE FROM addon.tbl_lvevaluierung_selbstevaluierung
					WHERE uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
			if(!$db->db_query($qry))
				$error = true;
		}
	}

	if(!$error)
	{
		$tqry = "SELECT 1
			FROM INFORMATION_SCHEMA.TABLES
			WHERE table_type='BASE TABLE'
				AND table_schema='addon'
				AND table_name='tbl_software';";

		if(!$result = $db->db_query($tqry))
			$error = true;

		if($db->db_num_rows($result))
		{
			$qry = '
				DELETE FROM addon.tbl_software
					WHERE ansprechperson_uid IN (SELECT uid FROM public.tbl_benutzer WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).')';
			if(!$db->db_query($qry))
			{
				$error = true;
			}
		}
	}





	/*
	 * delete all prestudent entries
	 */
	if(!$error)
	{
		$queryPs = '
			SELECT tbl_prestudent.prestudent_id FROM tbl_prestudent
				JOIN tbl_student ON (tbl_prestudent.prestudent_id = tbl_student.prestudent_id)
				JOIN tbl_benutzer ON (tbl_student.student_uid = tbl_benutzer.uid)
			WHERE tbl_benutzer.person_id = ' . $db->db_add_param($person_id, FHC_INTEGER).'
		';
		$resultPs = $db->db_query($queryPs);
		if(!$resultPs)
		{
			$error = true;
		}
		else
		{
			while($row = $db->db_fetch_object($resultPs))
			{
				if(!$error)
				{
					if(!casDeletePrestudent($db, $row->prestudent_id, false))
					{
						$error = true;
					}
				}
				else { break; }
			}
		}
	}
	
	if(!$error)
	{
		$queryPs = '
			SELECT tbl_prestudent.prestudent_id FROM tbl_prestudent
			WHERE person_id = ' . $db->db_add_param($person_id, FHC_INTEGER).'
		';
		$resultPs = $db->db_query($queryPs);
		if(!$resultPs)
		{
			$error = true;
		}
		else
		{
			while($row = $db->db_fetch_object($resultPs))
			{
				if(!$error)
				{
					if(!casDeletePrestudent($db, $row->prestudent_id, false))
					{
						$error = true;
					}
				}
				else { break; }
			}
		}
	}

	/*
	 * delete all mitarbeiter entries
	 */
	if(!$error)
	{
		$queryMa = '
			SELECT tbl_mitarbeiter.mitarbeiter_uid FROM tbl_mitarbeiter
				JOIN tbl_benutzer ON (tbl_mitarbeiter.mitarbeiter_uid = tbl_benutzer.uid)
			WHERE tbl_benutzer.person_id = ' . $db->db_add_param($person_id, FHC_INTEGER).'
		';
		$resultMa = $db->db_query($queryMa);
		if(!$resultMa)
		{
			$error = true;
		}
		else
		{
			while($row = $db->db_fetch_object($resultMa))
			{
				if(!$error)
				{
					if(!casDeleteMitarbeiter($db, $row->mitarbeiter_uid, false))
						$error = true;
				}
				else { break; }
			}
		}
	}



	if(!$error)
	{
		if(!deleteWithFirstlevelDeps($db, 'person_id', 'public', 'tbl_person', $person_id, FHC_INTEGER))
		{
			$error = true;
		}
	}

	/*
	 * Delete the tbl_person entry
	 */
	$qry = 'DELETE FROM public.tbl_person where person_id='.$db->db_add_param($person_id, FHC_INTEGER);
	if(!$error && !$db->db_query($qry))
	{
		$error = true;
	}

	/*
	 * Rollback, if an
	 * error occoured
	 */
	if(!$error)
	{

	if($trans){$db->db_query("COMMIT;");}
		return true;
	}


	if($trans){$db->db_query("ROLLBACK;");}
	return false;
}





function deleteWithFirstlevelDeps($db, $column_name, $table_schema, $table_name, $data, $dataType)
{
	/*
	 * Resolve all neede Tables
	 */
	$qryResolve = '
	SELECT R.TABLE_NAME, R.TABLE_SCHEMA, R.COLUMN_NAME
		FROM INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE u
		INNER JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS FK
			on U.CONSTRAINT_CATALOG = FK.UNIQUE_CONSTRAINT_CATALOG
			AND U.CONSTRAINT_SCHEMA = FK.UNIQUE_CONSTRAINT_SCHEMA
			AND U.CONSTRAINT_NAME = FK.UNIQUE_CONSTRAINT_NAME
		INNER JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE R
			ON R.CONSTRAINT_CATALOG = FK.CONSTRAINT_CATALOG
			AND R.CONSTRAINT_SCHEMA = FK.CONSTRAINT_SCHEMA
			AND R.CONSTRAINT_NAME = FK.CONSTRAINT_NAME
	WHERE U.COLUMN_NAME  = ' . $db->db_add_param($column_name) . '
		AND U.TABLE_SCHEMA = ' . $db->db_add_param($table_schema) . '
		AND U.TABLE_NAME   = ' . $db->db_add_param($table_name) . ';';

	$resultResolve = $db->db_query($qryResolve);
	if(!$resultResolve)
		return false;

	while($rowResolve = $db->db_fetch_object($resultResolve))
	{
		$qryDelete = '
			DELETE FROM '.$rowResolve->table_schema.'.'.$rowResolve->table_name.'
			WHERE '.$rowResolve->column_name.'='.$db->db_add_param($data, $dataType);
		$resultDelete = $db->db_query($qryDelete);
		if(!$resultDelete)
		{
			return false;
		}
	}
	return true;
}



function recursiveGetAllProjektphase_id($db, $projektphase_id, $arr = null)
{
	if(is_null($arr))
		$arr = array();


	$qry = 'SELECT projektphase_id FROM fue.tbl_projektphase WHERE projektphase_fk='.$db->db_add_param($projektphase_id, FHC_INTEGER).';';

	$res = $db->db_query($qry);
	if(!$res)
		return false;

	while($row = $db->db_fetch_object($res))
	{
		if(!is_null($row->projektphase_id))
			$arr = array_merge($arr, recursiveGetAllProjektphase_id($db, $row->projektphase_id, $arr));
	}

	$arr[] = $projektphase_id;

	return $arr;
}

?>
</body>
</html>
