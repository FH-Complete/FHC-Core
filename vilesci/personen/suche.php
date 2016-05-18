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
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at > and
 *          Andreas Moik <moik@technikum-wien.at>.
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/authentication.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/studiensemester.class.php');

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

$datum_obj = new datum();

echo '
<html>
<head>
<title>Suchergebnis</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
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
  		<input type="submit" value="Suchen">
  	</form>';

if($searchstr!='')
{
	$qry = "SELECT person_id FROM public.tbl_person WHERE person_id in(
			SELECT distinct person_id FROM public.tbl_person LEFT JOIN public.tbl_benutzer USING(person_id) WHERE
			nachname ~* '".$db->db_escape($searchstr)."' OR
			vorname ~* '".$db->db_escape($searchstr)."' OR
			alias ~* '".$db->db_escape($searchstr)."' OR
			COALESCE(nachname,'') || ' ' || COALESCE(vorname,'') = '".$db->db_escape($searchstr)."' OR
			COALESCE(vorname,'') || ' ' || COALESCE(nachname,'') = '".$db->db_escape($searchstr)."' OR
			uid ~* '".$db->db_escape($searchstr)."'
			) ORDER BY nachname, vorname;";

	if($result = $db->db_query($qry))
	{
		$auth = new authentication();

		echo $db->db_num_rows($result).' Person(en) gefunden<br><br>';
		echo '<table>';
		echo '<tr class="liste" align="center">';
		echo "<td colspan='5'><b>Person</b></td>";
		echo "<td colspan='4'><b>Benutzer</b></td>";
		echo "<td colspan='4'><b>Mitarbeiter</b></td>";
		echo "<td colspan='4'><b>Student</b></td>";
		echo '</tr>';
		echo '<tr class="liste" align="center">';
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
		echo '</tr>';

		while($row = $db->db_fetch_object($result))
		{
			$qry = "SELECT * FROM public.tbl_person WHERE person_id='$row->person_id'";
			if($result_person = $db->db_query($qry))
			{
				if($row_person = $db->db_fetch_object($result_person))
				{
					echo '<tr class="liste1">';
					echo "<td><a href='personen_details.php?person_id=$row_person->person_id'>$row_person->nachname</a></td>";
					echo "<td>$row_person->vorname</td>";
					echo "<td>".($row_person->gebdatum!=''?$datum_obj->convertISODate($row_person->gebdatum):'')."</td>";
					echo "<td>".($row_person->updateamum!=''?date('d.m.Y H:i:s', $datum_obj->mktime_fromtimestamp($row_person->updateamum)):'')."</td>";
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
								$content.= "<td>Mitarbeiter</td>";
								$content.= "<td><a href='personen_details.php?uid=$row_mitarbeiter->uid'>$row_mitarbeiter->uid</a></td>";
								$content.= "<td>".($row_mitarbeiter->aktiv=='t'?'Ja':'Nein')."</td>";

								$content.= "<td>";
								if($auth->UserExternalExists($row_mitarbeiter->uid))
									$content.="Ja";
								else
									$content.="Nein";
								$content.= "</td>";
								//$content.= "<td>".($row_mitarbeiter->bnupdateamum!=''?date('d.m.Y H:i:s', $datum_obj->mktime_fromtimestamp($row_mitarbeiter->bnupdateamum)):'')."</td>";
								//$content.= "<td>$row_mitarbeiter->bnupdatevon</td>";

								$content.= "<td>$row_mitarbeiter->telefonklappe</td>";
								$content.= "<td>$row_mitarbeiter->ort_kurzbz</td>";
								$content.= "<td>".($row_mitarbeiter->mupdateamum!=''?date('d.m.Y H:i:s', $datum_obj->mktime_fromtimestamp($row_mitarbeiter->mupdateamum)):'')."</td>";
								$content.= "<td>$row_mitarbeiter->mupdatevon</td>";
								$content.= "<td></td>";
								$content.= "<td></td>";
								$content.= '</tr>';
							}
						}
					}

					$qry = "SELECT *, tbl_benutzer.updateamum as bnupdateamum, tbl_benutzer.updatevon as bnupdatevon,
									tbl_prestudent.updateamum as supdateamum, tbl_prestudent.updatevon as supdatevon
							FROM public.tbl_prestudent JOIN public.tbl_benutzer USING(uid)
							WHERE tbl_benutzer.person_id=".$db->db_add_param($row->person_id, FHC_INTEGER);
					if($result_student = $db->db_query($qry))
					{
						if($db->db_num_rows($result_student))
						{

							while($row_student = $db->db_fetch_object($result_student))
							{
								$prestudent = new prestudent();
								$prestudent->getLastStatus($row_student->prestudent_id);
								$studiensemester = new studiensemester();
								$studiensemester_kurzbz = $studiensemester->getaktorNext();
								$prestudent->load_studentlehrverband($studiensemester_kurzbz);

								$content.= '<tr>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= "<td>$prestudent->status_kurzbz</td>";
								$content.= "<td><a href='personen_details.php?uid=$row_student->uid'>$row_student->uid</a></td>";
								$content.= "<td>".($row_student->aktiv=='t'?'Ja':'Nein')."</td>";

								$content.= "<td>";
								if($auth->UserExternalExists($row_student->uid))
									$content.="Ja";
								else
									$content.="Nein";
								$content.= "</td>";
								//$content.= "<td>".($row_student->bnupdateamum!=''?date('d.m.Y H:i:s', $datum_obj->mktime_fromtimestamp($row_student->bnupdateamum)):'')."</td>";
								//$content.= "<td>$row_student->bnupdatevon</td>";

								$content.= "<td></td>";
								$content.= "<td></td>";
								$content.= "<td></td>";
								$content.= "<td></td>";
								$content.= "<td>".$stg_arr[$row_student->studiengang_kz]."</td>";
								$content.= "<td>$prestudent->semester$prestudent->verband$prestudent->gruppe</td>";
								$content.= "<td>".($row_student->supdateamum!=''?date('d.m.Y H:i:s', $datum_obj->mktime_fromtimestamp($row_student->supdateamum)):'')."</td>";
								$content.= "<td>$row_student->supdatevon</td>";
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
?>
</body>
</html>
