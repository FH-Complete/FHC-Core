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
 *			Manuela Thamer < manuela.thamer@technikum-wien.at >
 */
/**
 * Vorrückung aller AKTIVEN Studenten.
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/lehrverband.class.php');
require_once('../../include/studienordnung.class.php');
require_once('../../include/studienplan.class.php');
require_once('../../include/statusgrund.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if (!$rechte->isBerechtigt('student/vorrueckung', null, 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$ausbildungssemester = 0;
$s = new studiengang();
$s->loadArray($rechte->getStgKz('student/vorrueckung'), 'typ, kurzbz', true);
$studiengang = $s->result;

echo '<!doctype html>
<html>
<head>
	<title>Studenten Vorrueckung</title>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">';
include('../../include/meta/jquery.php');
include('../../include/meta/jquery-tablesorter.php');
echo '
	<script language="Javascript">
	$(document).ready(function()
	{
		$("#t1").tablesorter(
		{
			sortList: [[7,0],[0,0],[1,0]],
			widgets: ["zebra","filter"],
		});
	});
	</script>
</head>
<body>';

// Output flushen damit nach dem aendern des Dropdowns gleich die neue Seite geladen wird.
// Sonst wird bei zu langen Ladezeiten vom User noch auf einen anderen Link gedrueckt und der Studiengang
// wieder zurueckgesetzt
// This prevent the notice error "ob_flush(): failed to flush buffer. No buffer to flush"
if (ob_get_contents() != false)
{
	ob_flush();
}

flush();

//Einlesen der studiensemester in einen Array
$ss = new studiensemester();
$ss->getAll('desc');
foreach ($ss->studiensemester as $studiensemester)
{
	$ss_arr[] = $studiensemester->studiensemester_kurzbz;
}

//Übergabeparameter
//studiengang
if (isset($_GET['stg_kz']) || isset($_POST['stg_kz']))
{
	$stg_kz = (isset($_GET['stg_kz'])?$_GET['stg_kz']:$_POST['stg_kz']);
}
else
{
	$stg_kz = $studiengang[0]->studiengang_kz;
}
//semester anzeige
if (isset($_GET['semester']) || isset($_POST['semester']))
{
	$semester = (isset($_GET['semester'])?$_GET['semester']:$_POST['semester']);
}
else
{
	$semester = 100;
}
//semester vorrückung
if (isset($_GET['semesterv']) || isset($_POST['semesterv']))
{
	$semesterv = (isset($_GET['semesterv'])?$_GET['semesterv']:$_POST['semesterv']);
}
else
{
	$semesterv = 100;
}
//angezeigtes studiensemester
if (isset($_GET['studiensemester_kurzbz']) || isset($_POST['studiensemester_kurzbz']))
{
	if (isset($_GET['studiensemester_kurzbz']))
		$studiensemester_kurzbz = $_GET['studiensemester_kurzbz'];
	else
		$studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
}
else
{
	if (!$studiensemester_kurzbz = $ss->getakt())
		$studiensemester_kurzbz = $ss->getaktorNext();
}
//ausgangssemester für vorrückung
if (isset($_GET['studiensemester_kurzbz_akt']) || isset($_POST['studiensemester_kurzbz_akt']))
{
	if (isset($_GET['studiensemester_kurzbz_akt']))
		$studiensemester_kurzbz_akt = $_GET['studiensemester_kurzbz_akt'];
	else
		$studiensemester_kurzbz_akt = $_POST['studiensemester_kurzbz_akt'];
}
else
{
	$studiensemester_kurzbz_akt = $ss->getLastOrAktSemester(30);
}
//zielsemester für vorrückung
if (isset($_GET['studiensemester_kurzbz_zk']) || isset($_POST['studiensemester_kurzbz_zk']))
{
	if (isset($_GET['studiensemester_kurzbz_zk']))
		$studiensemester_kurzbz_zk = $_GET['studiensemester_kurzbz_zk'];
	else
		$studiensemester_kurzbz_zk = $_POST['studiensemester_kurzbz_zk'];
}
else
{
	$studiensemester_kurzbz_zk = $ss->getNextFrom($studiensemester_kurzbz_akt);
}

if (!is_numeric($stg_kz))
{
	$stg_kz = 0;
}

//semester=100 bedeutet die Auswahl aller Semester
if (!is_numeric($semester))
{
	$semester = 100;
}

//Einlesen der maximalen, regulären Dauer der Studiengänge in einen Array
$stg = new studiengang();
$stg->getAll();
foreach ($stg->result as $row_stg)
{
	$max[$row_stg->studiengang_kz] = $row_stg->max_semester;
}

//select für die Anzeige
$sql_query = "SELECT
				tbl_student.*,
				tbl_person.vorname, tbl_person.nachname, tbl_person.person_id,
				tbl_studentlehrverband.semester as semester_stlv,
				tbl_studentlehrverband.verband as verband_stlv,
				tbl_studentlehrverband.gruppe as gruppe_stlv
			FROM
				tbl_studentlehrverband
				JOIN tbl_student USING (student_uid)
				JOIN tbl_benutzer ON (student_uid=uid)
				JOIN tbl_person USING (person_id)
			WHERE
				tbl_benutzer.aktiv
				AND tbl_studentlehrverband.studiengang_kz=".$db->db_add_param($stg_kz, FHC_INTEGER)."
				AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz);

if ($semester < 100) //semester = 100 wählt alle aus
{
	$sql_query .= " AND tbl_studentlehrverband.semester=".$db->db_add_param($semester, FHC_INTEGER);
}
$sql_query .= " ORDER BY semester, nachname";

if (!$result_std = $db->db_query($sql_query))
{
	error("Studenten not found!");
}
$outp = '';

// ****************************** Vorrücken ******************************
if (isset($_POST['vorr']))
{
	$statisticAdded = 0;
	$statisticUebersprungen = 0;
	$statisticStudienplanKorrektur = 0;
	$errorMsg = array('Studenten im letzten Ausbildungssemester ohne Diplomandenstatus' => 0);

	$stg_help = new studiengang();
	if (!$stg_help->load($stg_kz))
		die("Studiengang mit der Kennzahl $stg_kz kann nicht geladen werden");

	if (!$rechte->isBerechtigt('student/vorrueckung', $stg_help->oe_kurzbz, 'suid'))
		die('Sie haben keine Berechtigung fuer diese Aktion');

	//select für die Vorrückung
	$sql_query = "SELECT
					tbl_student.*,
					tbl_person.vorname, tbl_person.nachname, tbl_person.person_id,
					tbl_studentlehrverband.semester as semester_stlv,
					tbl_studentlehrverband.verband as verband_stlv,
					tbl_studentlehrverband.gruppe as gruppe_stlv
				FROM
					tbl_studentlehrverband JOIN tbl_student USING (student_uid)
					JOIN tbl_benutzer ON (student_uid=uid)
					JOIN tbl_person USING (person_id)
				WHERE
					tbl_benutzer.aktiv
					AND tbl_studentlehrverband.studiengang_kz=".$db->db_add_param($stg_kz, FHC_INTEGER)."
					AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz_akt);

	if ($semester < 100) //semester = 100 wählt alle aus
	{
		$sql_query .= "AND tbl_studentlehrverband.semester=".$db->db_add_param($semesterv);
	}
	$sql_query .= "ORDER BY semester, nachname";

	if (!$result_std = $db->db_query($sql_query))
	{
		error("Studenten not found!");
	}

	$next_ss = $studiensemester_kurzbz_zk;
	while ($row = $db->db_fetch_object($result_std))
	{
		//aktuelle Rolle laden
		$qry_status = "
			SELECT
				*
			FROM
				public.tbl_prestudentstatus
				JOIN public.tbl_prestudent USING(prestudent_id)
			WHERE
				prestudent_id=".$db->db_add_param($row->prestudent_id, FHC_INTEGER)."
				AND studiengang_kz=".$db->db_add_param($row->studiengang_kz, FHC_INTEGER)."
				AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz_akt)."
			ORDER BY datum desc, tbl_prestudentstatus.insertamum desc, tbl_prestudentstatus.ext_id desc
			LIMIT 1;";

		if ($result_status = $db->db_query($qry_status))
		{
			if ($row_status = $db->db_fetch_object($result_status))
			{
				//Studenten im letzten Semester bleiben dort, wenn aktiv

				// Semester fuer Studentlehrverband
				if (VORRUECKUNG_LEHRVERBAND_MAX_SEMESTER != '')
				{
					if ($row->semester_stlv >= VORRUECKUNG_LEHRVERBAND_MAX_SEMESTER)
						$s = $row->semester_stlv;
					else
						$s = $row->semester_stlv + 1;
				}
				else
				{
					if ($row->semester_stlv >= $max[$stg_kz] || $row->semester_stlv == 0)
						$s = $row->semester_stlv;
					else
						$s = $row->semester_stlv + 1;
				}

				if (!VORRUECKUNG_STATUS_MAX_SEMESTER)
					$ausbildungssemester = $row_status->ausbildungssemester + 1;
				else
				{
					// Semester fuer Status
					if ($row_status->ausbildungssemester >= $max[$stg_kz]
						|| $row_status->status_kurzbz == "Unterbrecher"
						|| $row_status->status_kurzbz == "Incoming")
					{
						$ausbildungssemester = $row_status->ausbildungssemester;
					}
					else
					{
						$ausbildungssemester = $row_status->ausbildungssemester + 1;
					}
				}

				//auf statusgrund_kurzbz abfragen
				$statusgrundObj = new statusgrund($row_status->statusgrund_id);
				$statusgrundId = null;
				if (isset($statusgrundObj->statusgrund_kurzbz) && $statusgrundObj->statusgrund_kurzbz === "prewiederholer" && $row_status->ausbildungssemester > 1)
				{
					$s = $row->semester_stlv - 1;
					$ausbildungssemester = $row_status->ausbildungssemester - 1;
					$statusgrundId = $statusgrundObj->getByStatusgrundKurzbz('wiederholer')->statusgrund_id;
				}

				// Wenn VORRUECKUNG_STATUS_MAX_SEMESTER true ist und
				// der Student kein Wiederholer ist und
				// der aktuelle Status "Student" im Max-Semester des Studiengangs ist, wird übersprungen
				if (VORRUECKUNG_STATUS_MAX_SEMESTER
					&& $statusgrundId == ''
					&& $row_status->ausbildungssemester == $max[$stg_kz]
					&& $row_status->status_kurzbz == 'Student')
				{
					$errorMsg['Studenten im letzten Ausbildungssemester ohne Diplomandenstatus'] = $errorMsg['Studenten im letzten Ausbildungssemester ohne Diplomandenstatus']+1;
					continue;
				}

				$lvb = new lehrverband();

				//Lehrverbandgruppe anlegen, wenn noch nicht vorhanden
				if (!$lvb->exists($row->studiengang_kz, $s, $row->verband_stlv, $row->gruppe_stlv))
				{
					$lvb = new lehrverband();
					$lvb->studiengang_kz = $row->studiengang_kz;
					$lvb->semester = $s;
					$lvb->verband = $row->verband_stlv;
					$lvb->gruppe = $row->gruppe_stlv;
					$lvb->aktiv = true;
					$lvb->new = true;
					if (!$lvb->save())
						die($lvb->errormsg);
				}
				//Überprüfen ob Eintrag schon vorhanden
				$qry_chk = "SELECT
				 		 	*
						FROM
							public.tbl_studentlehrverband
						WHERE
							student_uid=".$db->db_add_param($row->student_uid)."
							AND studiensemester_kurzbz=".$db->db_add_param($next_ss).";";

				$sql = '';
				if ($db->db_num_rows($db->db_query($qry_chk)) < 1)
				{
					//Eintragen der neuen Gruppe
					$sql = "INSERT INTO public.tbl_studentlehrverband (student_uid, studiensemester_kurzbz,
								studiengang_kz, semester, verband, gruppe, updateamum, updatevon,
								insertamum, insertvon, ext_id)
							VALUES (".$db->db_add_param($row->student_uid).", ".
								$db->db_add_param($next_ss).", ".
								$db->db_add_param($row->studiengang_kz).", ".
								$db->db_add_param($s).", ".
								$db->db_add_param($row->verband_stlv).", ".
								$db->db_add_param($row->gruppe_stlv).",NULL,NULL,now(),".
								$db->db_add_param($user).",NULL);";
				}
				//Check, ob schon ein Status für das Zielsemester vorhanden ist
				$qry_chk = "SELECT
							*
						FROM
							public.tbl_prestudentstatus
						WHERE
							prestudent_id=".$db->db_add_param($row->prestudent_id)."
							AND studiensemester_kurzbz=".$db->db_add_param($next_ss).";";

				if ($db->db_num_rows($db->db_query($qry_chk)) < 1
				&& $row_status->status_kurzbz != 'Absolvent')
				{
					// Pruefen ob der Studienplan fuer das vorgerueckte Semester noch gueltig ist
					// und GGf einen besseren Studienplan suchen
					$studienplan_id = getCorrectedStudienplan(
						$next_ss,
						$ausbildungssemester,
						$row_status->studienplan_id
					);

					if ($row_status->studienplan_id != $studienplan_id)
						$statisticStudienplanKorrektur++;

					//Eintragen des neuen Status
					$sql .= "INSERT INTO public.tbl_prestudentstatus (prestudent_id, status_kurzbz,
								studiensemester_kurzbz, ausbildungssemester, datum, insertamum,
								insertvon, updateamum, updatevon, ext_id, orgform_kurzbz, studienplan_id, statusgrund_id)
							VALUES (".$db->db_add_param($row->prestudent_id).", ".
								$db->db_add_param($row_status->status_kurzbz).", ".
								$db->db_add_param($next_ss).", ".
								$db->db_add_param($ausbildungssemester).", now(), now(), ".
								$db->db_add_param($user).",	NULL, NULL, NULL, ".
								$db->db_add_param($row_status->orgform_kurzbz).", ".
								$db->db_add_param($studienplan_id).", ".
								$db->db_add_param($statusgrundId).");";
				}
				if ($sql != '')
				{
					if (!$r = $db->db_query($sql))
					{
						die($db->db_last_error()."<br>".$sql);
					}
					else
					{
						$statisticAdded++;
					}
				}
				else
				{
					$statisticUebersprungen++;
				}
			}
		}
	}
	echo '<span class="ok">';
	if ($statisticAdded > 0)
		echo 'Vorgerückte Personen: '.$statisticAdded.'<br>';
	if ($statisticStudienplanKorrektur > 0)
		echo 'Studienplanzuordnung korrigiert: '.$statisticStudienplanKorrektur.'<br>';
	echo '</span>';
	echo '<span class="warning">';
	if ($statisticUebersprungen > 0)
		echo $statisticUebersprungen.' Personen wurden übersprungen, weil schon ein Eintrag im Zielsemester vorhanden ist<br>';
	echo '</span>';
	echo '<span class="error">';
	foreach($errorMsg AS $text=>$anzahl)
	{
		if ($anzahl > 0)
		{
			echo $anzahl.' '.$text;
		}
	}
	echo '</span>';
}

// **************** Ausgabe vorbereiten ******************************
$s = array();
$outp .= '
<table>
<tr>
	<td colspan="2" align="center"><b>Anzeige</b></td>
</tr>
<tr>
	<td>Studiengang:</td>
	<td>
		<SELECT name="stg_kz" onchange="document.location.href=this.value">';

//Auswahl Studiengang
foreach ($studiengang as $stg)
{
	$url = $_SERVER['PHP_SELF']."?stg_kz=$stg->studiengang_kz";
	$url .= "&semester=$semester";
	$url .= "&semesterv=$semesterv";
	$url .= "&studiensemester_kurzbz=$studiensemester_kurzbz";
	$url .= "&studiensemester_kurzbz_akt=$studiensemester_kurzbz_akt";
	$url .= "&studiensemester_kurzbz_zk=$studiensemester_kurzbz_zk";

	$outp .= "<OPTION value='" . $url . "' " . ($stg->studiengang_kz == $stg_kz ? 'selected' : '') . ">";
	$outp .= "$stg->kurzbzlang ($stg->kuerzel) - $stg->bezeichnung</OPTION>";
	if (!isset($s[$stg->studiengang_kz]))
		$s[$stg->studiengang_kz] = new stdClass();

	$s[$stg->studiengang_kz]->max_sem = $stg->max_semester;
	$s[$stg->studiengang_kz]->kurzbz = $stg->kurzbzlang;
}
$outp .= '</SELECT>
	</td>
</tr>';
//Auswahl angezeigtes Studiensemester
$outp .= "
<tr>
	<td>Angezeigtes Studiensemester:</td>
	<td><select name='studiensemester_kurzbz' onchange='document.location.href=this.value'>\n";

if (isset($ss_arr) && is_array($ss_arr))
{
	foreach ($ss_arr as $sts)
	{
		$url = $_SERVER['PHP_SELF']."?stg_kz=$stg_kz";
		$url .= "&semester=$semester";
		$url .= "&semesterv=$semesterv";
		$url .= "&studiensemester_kurzbz=$sts";
		$url .= "&studiensemester_kurzbz_akt=$studiensemester_kurzbz_akt";
		$url .= "&studiensemester_kurzbz_zk=$studiensemester_kurzbz_zk";

		$outp .= "<option value='" .$url ."' " . ($studiensemester_kurzbz == $sts ? 'selected' : '') . ">".$sts."</option>";
	}
}
$outp .= "		</select>
	</td>
</tr>";
$outp .= '
<tr>
	<td>Ausbildungssemester der Anzeige:</td>
	<td> -- ';
for ($i = 0; $i <= $s[$stg_kz]->max_sem; $i++)
{
	$url = $_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz;
	$url .= '&semester='.$i;
	$url .= '&semesterv='.$semesterv;
	$url .= '&studiensemester_kurzbz='.$studiensemester_kurzbz;
	$url .= '&studiensemester_kurzbz_akt='.$studiensemester_kurzbz_akt;
	$url .= '&studiensemester_kurzbz_zk='.$studiensemester_kurzbz_zk;

	$outp .= '<a href="'.$url.'">'.$i.'</A> -- ';
}

$url = $_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz;
$url .= '&semesterv='.$semesterv;
$url .= '&semester=100';
$url .= '&studiensemester_kurzbz='.$studiensemester_kurzbz;
$url .= '&studiensemester_kurzbz_akt='.$studiensemester_kurzbz_akt;
$url .= '&studiensemester_kurzbz_zk='.$studiensemester_kurzbz_zk;

$outp .= '<A href="'.$url.'">alle</A> -- ';

//Auswahl Studiensemester von dem weg vorgerückt werden soll
$outp .= '
	</td>
</tr>
<tr>
	<td colspan="2" align="center"><b>Vorr&uuml;ckung Studiengang '.$s[$stg_kz]->kurzbz.'</b></td>
</tr>
<tr>
	<td>Ausgangs-Studiensemester:</td>
	<td><select name="studiensemester_kurzbz_akt" onchange="document.location.href=this.value">';

if (isset($ss_arr) && is_array($ss_arr))
{
	foreach ($ss_arr as $sts2)
	{
		$url = $_SERVER['PHP_SELF']."?stg_kz=$stg_kz";
		$url .= "&semester=$semester";
		$url .= "&semesterv=$semesterv";
		$url .= "&studiensemester_kurzbz=$studiensemester_kurzbz";
		$url .= "&studiensemester_kurzbz_akt=$sts2";
		$url .= "&studiensemester_kurzb_zk=$studiensemester_kurzbz_zk";

		$outp .= "<option value='".$url ."' " . ($studiensemester_kurzbz_akt == $sts2 ? 'selected' : '') . ">".$sts2."</option>";
	}
}
$outp .= "		</select>
	</td>
</tr>\n";
$outp .= '
<tr>
	<td>Ausgangs-Ausbildungssemester:</td>
	<td>-- ';
for ($j = 0; $j <= $s[$stg_kz]->max_sem; $j++)
{
	$url = $_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz;
	$url .= '&semester='.$semester;
	$url .= '&semesterv='.$j;
	$url .= '&studiensemester_kurzbz='.$studiensemester_kurzbz;
	$url .= '&studiensemester_kurzbz_akt='.$studiensemester_kurzbz_akt;
	$url .= '&studiensemester_kurzbz_zk='.$studiensemester_kurzbz_zk;

	$outp .= '<A href="'.$url.'">'.$j.'</A> -- ';
}

$url = $_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz;
$url .= '&semester='.$semester;
$url .= '&semesterv=100';
$url .= '&studiensemester_kurzbz='.$studiensemester_kurzbz;
$url .= '&studiensemester_kurzbz_akt='.$studiensemester_kurzbz_akt;
$url .= '&studiensemester_kurzbz_zk='.$studiensemester_kurzbz_zk;

$outp .=  '<A href="'.$url.'">alle</A> --
	</td>
</tr>';
//Auswahl Studiensemester in das vorgerückt werden soll
$outp .= "
<tr>
	<td>Ziel-Studiensemester:</td>
	<td><select name='studiensemester_kurzbz_zk' onchange='document.location.href=this.value'>\n";

if (isset($ss_arr) && is_array($ss_arr))
{
	foreach ($ss_arr as $sts3)
	{
		if ($studiensemester_kurzbz_zk == $sts3)
			$sel3 = " selected ";
		else
			$sel3 = '';

		$url = $_SERVER['PHP_SELF']."?stg_kz=$stg_kz";
		$url .= "&semester=$semester";
		$url .= "&semesterv=$semesterv";
		$url .= "&studiensemester_kurzbz=$studiensemester_kurzbz";
		$url .= "&studiensemester_kurzbz_akt=$studiensemester_kurzbz_akt";
		$url .= "&studiensemester_kurzbz_zk=$sts3";

		$outp .= "<option value='" .$url ."' " . ($studiensemester_kurzbz_zk == $sts3 ? 'selected' : '') . ">".$sts3."</option>";
	}
}
$outp .= "		</select>\n
	</td>
</tr>
</table>";
$outp .= "Vorr&uuml;ckung von ".$studiensemester_kurzbz_akt." / ".($semesterv < 100?$semesterv.".":'alle');
$outp .= "Semester  -> ".$studiensemester_kurzbz_zk;

//Überschrift
echo "<H2>Studenten Vorr&uuml;ckung (".$s[$stg_kz]->kurzbz." - ".($semester < 100?$semester:'alle')." - ".
	$studiensemester_kurzbz."), DB:".DB_NAME."</H2>";


echo '<form action="" method="POST">';
echo '<table width="70%"><tr><td>';
//Ausgabe der Auswahl
echo $outp;
echo '</td><td>';
echo '<br><br><br><input type="submit" name="vorr" value="Vorruecken" />';
echo '</td><td>&nbsp;</td></tr></table>';
echo '</form>';
//Überschrift Anzeige
echo "<h3>&Uuml;bersicht (".$studiensemester_kurzbz."/".($semester < 100?$semester.".":'alle')." Semester )</h3>";
//Anzeige Tabelle
if ($result_std != 0)
{
	$num_rows = $db->db_num_rows($result_std);
	echo 'Anzahl: '.$num_rows;
	echo '
	<table class="tablesorter" id="t1">
	<thead>
		<tr>
			<th>Nachname</th>
			<th>Vorname</th>
			<th>STG</th>
			<th>Sem</th>
			<th>Ver</th>
			<th>Grp</th>
			<th>Status</th>
			<th>AusbSem</th>
			<th>Studienplan</th>
		</tr>
	</thead>
	<tbody>';

	for ($i = 0; $i < $num_rows; $i++)
	{
		$row = $db->db_fetch_object($result_std, $i);
		$qry_status = "
			SELECT
				tbl_prestudentstatus.status_kurzbz, statusgrund_kurzbz, ausbildungssemester, tbl_studienplan.studienplan_id, tbl_studienplan.bezeichnung
			FROM
				public.tbl_prestudentstatus
				JOIN public.tbl_prestudent USING(prestudent_id)
				LEFT JOIN lehre.tbl_studienplan USING(studienplan_id)
				LEFT JOIN public.tbl_status_grund USING (statusgrund_id)
			WHERE
				person_id=".$db->db_add_param($row->person_id, FHC_INTEGER)."
				AND studiengang_kz=".$db->db_add_param($row->studiengang_kz, FHC_INTEGER)."
				AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)."
			ORDER BY datum desc, tbl_prestudentstatus.insertamum desc, tbl_prestudentstatus.ext_id desc LIMIT 1;";

		if ($result_status = $db->db_query($qry_status))
		{
			$status_kurzbz = '';
			$ausbildungssemester = '';
			$studienplan_id = '';
			$studienplan_bezeichnung = '';

			if ($row_status = $db->db_fetch_object($result_status))
			{
				$status_kurzbz = $row_status->status_kurzbz;
				$statusgrund_kurzbz = $row_status->statusgrund_kurzbz;
				$ausbildungssemester = $row_status->ausbildungssemester;
				$studienplan_id = $row_status->studienplan_id;
				$studienplan_bezeichnung = $row_status->bezeichnung;
			}

			if ($studienplan_id != '')
				$studienplan_bezeichnung .= '('.$studienplan_id.')';
			echo "
			<tr>
				<td>".$db->convert_html_chars($row->nachname)."</td>
				<td>".$db->convert_html_chars($row->vorname)."</td>
				<td>".$db->convert_html_chars($row->studiengang_kz)."</td>
				<td>".$db->convert_html_chars($row->semester_stlv)."</td>
				<td>".$db->convert_html_chars($row->verband_stlv)."</td>
				<td>".$db->convert_html_chars($row->gruppe_stlv)."</td>
				<td>".$db->convert_html_chars($status_kurzbz)."</td>
				<td>".$db->convert_html_chars($ausbildungssemester)."</td>
				<td>".$db->convert_html_chars($studienplan_bezeichnung)."</td>
			</tr>\n";
		}
		else
		{
			error("Roles not found!");
		}
	}
	echo '</tbody>
	</table>';
}
else
	echo "Kein Eintrag gefunden!";

/**
 * Prüft ob der Studienplan im vorgerueckten Studiensemester noch aktiv ist
 * falls dieser dort keine Gueltigkeit mehr hat, wird versucht den neuen Studienplan zu suchen
 * wenn ein eindeutiger gefunden wird, wird der neue gesetzt
 * Wenn kein eindeutiger gefunden wird, bleibt der alte Studienplan eingetragen
 *
 * @param string $studiensemester_kurzbz Neues Studiensemester.
 * @param int $ausbildungssemester Neues Ausbildungssemester.
 * @param int $studienplan_id Alte Studienplan_id.
 * @return neue Studienplan_id
 */
function getCorrectedStudienplan($studiensemester_kurzbz, $ausbildungssemester, $studienplan_id)
{
	if ($studienplan_id == '')
	{
		// Wenn kein Studienplan eingetragen ist auch keinen suchen
		return '';
	}

	$studienplan = new studienplan();
	if ($studienplan->isSemesterZugeordnet($studienplan_id, $studiensemester_kurzbz, $ausbildungssemester))
		return $studienplan_id;
	else
	{
		if ($studienplan->loadStudienplan($studienplan_id))
		{
			$studienordnung = new studienordnung();
			$studienordnung->loadStudienordnung($studienplan->studienordnung_id);

			$stp_neu = new studienplan();
			$ret = $stp_neu->getStudienplaeneFromSem(
				$studienordnung->studiengang_kz,
				$studiensemester_kurzbz,
				$ausbildungssemester,
				$studienplan->orgform_kurzbz
			);

			if ($ret === true && count($stp_neu->result) == 1)
			{
				// Es wurde ein eindeutiger neue Studienplan gefunden
				return $stp_neu->result[0]->studienplan_id;
			}
			else
			{
				// Kein eindeutiger gefunden -> es bleibt der alte
				return $studienplan_id;
			}
		}
		else
		{
			return $studienplan_id;
		}
	}
}
?>
</body>
</html>
