<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
/*
 * Prueft die Daten in der Datenbank auf konsistenz und gibt Hinweise
 * bei fehlerhafter Datenmigration oder Datenmanipulation direkt auf Datenbankebene
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('admin'))
{
	die('Sie haben keine Berechtigung fuer diese Seite (admin)');
}

$db = new basis_db();

$error_kritisch=0;
$error_warning=0;

echo '<!DOCTYPE HTML>
<html>
<head>
	<title>Konsistenzpruefung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="../skin/vilesci.css" type="text/css" />
	<link rel="stylesheet" href="../skin/fhcomplete.css" type="text/css" />
	<script type="text/javascript" src="../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../include/js/jquery.ui.datepicker.translation.js"></script>
	<link rel="stylesheet" href="../skin/tablesort.css" type="text/css">
</head>
<body>';

/************************************************************************************
 * Pruefung auf falschen Personenkreislauf
 * tbl_person->tbl_benutzer->tbl_student->tbl_prestudent->tbl_person
 */

$qry = "SELECT
			vorname, nachname, tbl_benutzer.uid, tbl_prestudent.prestudent_id,
			tbl_person.person_id as pers_person_id, tbl_prestudent.person_id pre_person_id
		FROM
			public.tbl_person
			JOIN public.tbl_benutzer USING(person_id)
			JOIN public.tbl_student ON(uid=student_uid)
			JOIN public.tbl_prestudent USING(prestudent_id)
		WHERE
			tbl_person.person_id<>tbl_prestudent.person_id
		ORDER BY nachname, vorname, uid";

if($result = $db->db_query($qry))
{
	echo '<h2>Inkonsistenter Personenkreislauf tbl_person-&gt;tbl_benutzer-&gt;tbl_student-&gt;tbl_prestudent-&gt;tbl_person</h2>';
	$anzahl = $db->db_num_rows($result);
	echo '<span class="'.($anzahl>0?'error':'ok').'">'.$anzahl.' Probleme gefunden</span>';

	if($anzahl>0)
	{
		echo '<br><a href="#Anzeigen" onclick="$(\'#personenkreislauf\').toggle(); return false;">Anzeigen &gt;&gt;</a>';
		echo '
		<script type="text/javascript">
		$(document).ready(function()
			{
				$("#personenkreislauf").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra"]
				});
			});
		</script>
			<table id="personenkreislauf" class="tablesorter" style="display:none">
				<thead>
					<tr>
						<th>Nachname</th>
						<th>Vorname</th>
						<th>UID</th>
						<th>PrestudentID</th>
						<th>tbl_person.person_id</th>
						<th>tbl_prestudent.person_id</th>
					</tr>
				</thead>
				<tbody>';

		while($row = $db->db_fetch_object($result))
		{
			$error_kritisch++;
			echo '<tr>
					<th>'.$db->convert_html_chars($row->nachname).'</th>
					<th>'.$db->convert_html_chars($row->vorname).'</th>
					<th>'.$db->convert_html_chars($row->uid).'</th>
					<th>'.$db->convert_html_chars($row->prestudent_id).'</th>
					<th>'.$db->convert_html_chars($row->pers_person_id).'</th>
					<th>'.$db->convert_html_chars($row->pre_person_id).'</th>
				</tr>';
		}
		echo '</thead></table>';
	}
}
flush();


/**************************************************************************************
 * UIDs ohne Student und ohne Mitarbeiter
 *
 */
$qry = "SELECT
			tbl_benutzer.uid, tbl_benutzer.person_id, tbl_person.vorname, tbl_person.nachname
		FROM
			public.tbl_benutzer
			LEFT JOIN public.tbl_person USING(person_id)
		WHERE
				NOT EXISTS (SELECT 1 FROM public.tbl_student WHERE student_uid=tbl_benutzer.uid)
			AND NOT EXISTS (SELECT 1 FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid=tbl_benutzer.uid)";

if($result = $db->db_query($qry))
{
	echo '<h2>Benutzer ohne Student und ohne Mitarbeiter Eintrag</h2>';

	$anzahl = $db->db_num_rows($result);

	echo '<span class="'.($anzahl>0?'warning':'ok').'">'.$anzahl.' Probleme gefunden</span>';
	if($anzahl>0)
	{
		echo '<br><a href="#Anzeigen" onclick="$(\'#benutzerohnestudent\').toggle(); return false;">Anzeigen &gt;&gt;</a>';
			echo '
		<script type="text/javascript">
		$(document).ready(function()
			{
				$("#benutzerohnestudent").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra"]
				});
			});
		</script>
			<table class="tablesorter" id="benutzerohnestudent" style="display:none">
				<thead>
					<tr>
						<th>Nachname</th>
						<th>Vorname</th>
						<th>UID</th>
						<th>PersonID</th>
					</tr>
				</thead>
				<tbody>';
		while($row = $db->db_fetch_object($result))
		{
			$error_warning++;
			echo '
			<tr>
				<td>'.$db->convert_html_chars($row->nachname).'</td>
				<td>'.$db->convert_html_chars($row->vorname).'</td>
				<td>'.$db->convert_html_chars($row->uid).'</td>
				<td>'.$db->convert_html_chars($row->person_id).'</td>
			</tr>';
		}
		echo '</tbody></table>';
	}
}

/**************************************************************************************
 * Studentenstatus ohne UID
 *
 */
$qry = "SELECT
			distinct tbl_person.person_id, tbl_person.vorname, tbl_person.nachname, tbl_prestudent.prestudent_id
		FROM
			public.tbl_prestudentstatus
			JOIN public.tbl_prestudent USING(prestudent_id)
			JOIN public.tbl_person USING(person_id)
		WHERE
			status_kurzbz IN('Student','Absolvent','Diplomand','Incoming')
			AND NOT EXISTS (SELECT 1 FROM public.tbl_student WHERE prestudent_id=tbl_prestudent.prestudent_id)";

if($result = $db->db_query($qry))
{
	echo '<h2>Prestudenten mit Studenten/Absolventen/Diplomanden/Incoming Status aber ohne StudentUID</h2>';

	$anzahl = $db->db_num_rows($result);

	echo '<span class="'.($anzahl>0?'error':'ok').'">'.$anzahl.' Probleme gefunden</span>';

	if($anzahl>0)
	{
		echo '<br><a href="#Anzeigen" onclick="$(\'#studentohneuid\').toggle(); return false;">Anzeigen &gt;&gt;</a>';
			echo '
		<script type="text/javascript">
		$(document).ready(function()
			{
				$("#studentohneuid").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra"]
				});
			});
		</script>
			<table class="tablesorter" id="studentohneuid" style="display:none">
				<thead>
					<tr>
						<th>Nachname</th>
						<th>Vorname</th>
						<th>PrestudentID</th>
						<th>PersonID</th>
					</tr>
				</thead>
				<tbody>';
		while($row = $db->db_fetch_object($result))
		{
			$error_kritisch++;
			echo '
			<tr>
				<td>'.$db->convert_html_chars($row->nachname).'</td>
				<td>'.$db->convert_html_chars($row->vorname).'</td>
				<td>'.$db->convert_html_chars($row->prestudent_id).'</td>
				<td>'.$db->convert_html_chars($row->person_id).'</td>
			</tr>';
		}
		echo '</tbody></table>';
	}
}

/**************************************************************************************
 * StgKz von Stunent und Prestudent unterschiedlich
 *
 */
$qry = "SELECT
			tbl_person.vorname, tbl_person.nachname, tbl_prestudent.prestudent_id, tbl_student.student_uid,
			tbl_student.studiengang_kz as stud_studiengang_kz, tbl_prestudent.studiengang_kz as pre_studiengang_kz
		FROM
			public.tbl_student
			JOIN public.tbl_prestudent USING(prestudent_id)
			JOIN public.tbl_person USING(person_id)
		WHERE
			tbl_student.studiengang_kz<>tbl_prestudent.studiengang_kz";

if($result = $db->db_query($qry))
{
	echo '<h2>Studiengangskennzahl von tbl_student ungleich tbl_prestudent</h2>';

	$anzahl = $db->db_num_rows($result);

	echo '<span class="'.($anzahl>0?'error':'ok').'">'.$anzahl.' Probleme gefunden</span>';

	if($anzahl>0)
	{
		echo '<br><a href="#Anzeigen" onclick="$(\'#stgungleich\').toggle(); return false;">Anzeigen &gt;&gt;</a>';
			echo '
		<script type="text/javascript">
		$(document).ready(function()
			{
				$("#stgungleich").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra"]
				});
			});
		</script>
			<table class="tablesorter" id="stgungleich" style="display:none">
				<thead>
					<tr>
						<th>Nachname</th>
						<th>Vorname</th>
						<th>PrestudentID</th>
						<th>StudentStgKZ</th>
						<th>PrestudentStgKZ</th>
					</tr>
				</thead>
				<tbody>';
		while($row = $db->db_fetch_object($result))
		{
			$error_kritisch++;
			echo '
			<tr>
				<td>'.$db->convert_html_chars($row->nachname).'</td>
				<td>'.$db->convert_html_chars($row->vorname).'</td>
				<td>'.$db->convert_html_chars($row->prestudent_id).'</td>
				<td>'.$db->convert_html_chars($row->stud_studiengang_kz).'</td>
				<td>'.$db->convert_html_chars($row->pre_studiengang_kz).'</td>
			</tr>';
		}
		echo '</tbody></table>';
	}
}

/**************************************************************************************
 * Studenten ohne passenden Status
 *
 */
$qry = "SELECT
			tbl_person.vorname, tbl_person.nachname, tbl_prestudent.prestudent_id, tbl_student.student_uid,
			get_rolle_prestudent(prestudent_id, null) as laststatus
		FROM
			public.tbl_student
			JOIN public.tbl_prestudent USING(prestudent_id)
			JOIN public.tbl_person USING(person_id)
		WHERE
			NOT EXISTS(SELECT 1 FROM public.tbl_prestudentstatus WHERE prestudent_id=tbl_prestudent.prestudent_id AND status_kurzbz in('Student','Incoming','Diplomand','Absolvent'))";

if($result = $db->db_query($qry))
{
	echo '<h2>Studenten ohne Status Student/Diplomand/Incoming/Absolvent</h2>';

	$anzahl = $db->db_num_rows($result);

	echo '<span class="'.($anzahl>0?'warning':'ok').'">'.$anzahl.' Probleme gefunden</span>';

	if($anzahl>0)
	{
		echo '<br><a href="#Anzeigen" onclick="$(\'#studentenohnestatus\').toggle(); return false;">Anzeigen &gt;&gt;</a>';
			echo '
		<script type="text/javascript">
		$(document).ready(function()
			{
				$("#studentenohnestatus").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra"]
				});
			});
		</script>
			<table class="tablesorter" id="studentenohnestatus" style="display:none">
				<thead>
					<tr>
						<th>Nachname</th>
						<th>Vorname</th>
						<th>prestudent_id</th>
						<th>UID</th>
						<th>Letzter Status</th>
					</tr>
				</thead>
				<tbody>';
		while($row = $db->db_fetch_object($result))
		{
			$error_warning++;
			echo '
			<tr>
				<td>'.$db->convert_html_chars($row->nachname).'</td>
				<td>'.$db->convert_html_chars($row->vorname).'</td>
				<td>'.$db->convert_html_chars($row->prestudent_id).'</td>
				<td>'.$db->convert_html_chars($row->student_uid).'</td>
				<td>'.$db->convert_html_chars($row->laststatus).'</td>
			</tr>';
		}
		echo '</tbody></table>';
	}
}

/**************************************************************************************
 * Prestudenten ohne Status
 *
 */
$qry = "SELECT
			tbl_person.vorname, tbl_person.nachname, tbl_prestudent.prestudent_id
		FROM
			public.tbl_prestudent
			JOIN public.tbl_person USING(person_id)
		WHERE
			NOT EXISTS(SELECT 1 FROM public.tbl_prestudentstatus WHERE prestudent_id=tbl_prestudent.prestudent_id)";

if($result = $db->db_query($qry))
{
	echo '<h2>Prestudenten ohne Status</h2>';

	$anzahl = $db->db_num_rows($result);

	echo '<span class="'.($anzahl>0?'warning':'ok').'">'.$anzahl.' Probleme gefunden</span>';

	if($anzahl>0)
	{
		echo '<br><a href="#Anzeigen" onclick="$(\'#prestudentenohnestatus\').toggle(); return false;">Anzeigen &gt;&gt;</a>';
			echo '
		<script type="text/javascript">
		$(document).ready(function()
			{
				$("#prestudentenohnestatus").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra"]
				});
			});
		</script>
			<table class="tablesorter" id="prestudentenohnestatus" style="display:none">
				<thead>
					<tr>
						<th>Nachname</th>
						<th>Vorname</th>
						<th>prestudent_id</th>
					</tr>
				</thead>
				<tbody>';
		while($row = $db->db_fetch_object($result))
		{
			$error_warning++;
			echo '
			<tr>
				<td>'.$db->convert_html_chars($row->nachname).'</td>
				<td>'.$db->convert_html_chars($row->vorname).'</td>
				<td>'.$db->convert_html_chars($row->prestudent_id).'</td>
			</tr>';
		}
		echo '</tbody></table>';
	}
}

/**************************************************************************************
 * Studenten ohne Studentlehrverband eintrag
 *
 */
$qry = "SELECT
			tbl_person.vorname, tbl_person.nachname, tbl_student.student_uid
		FROM
			public.tbl_student
			JOIN public.tbl_benutzer ON(uid=student_uid)
			JOIN public.tbl_person USING(person_id)
		WHERE
			NOT EXISTS(SELECT 1 FROM public.tbl_studentlehrverband WHERE student_uid=tbl_student.student_uid)";

if($result = $db->db_query($qry))
{
	echo '<h2>Studenten ohne Studentlehrverband Eintrag</h2>';

	$anzahl = $db->db_num_rows($result);

	echo '<span class="'.($anzahl>0?'warning':'ok').'">'.$anzahl.' Probleme gefunden</span>';

	if($anzahl>0)
	{
		echo '<br><a href="#Anzeigen" onclick="$(\'#studentlehrverband\').toggle(); return false;">Anzeigen &gt;&gt;</a>';
			echo '
		<script type="text/javascript">
		$(document).ready(function()
			{
				$("#studentlehrverband").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra"]
				});
			});
		</script>
			<table class="tablesorter" id="studentlehrverband" style="display:none">
				<thead>
					<tr>
						<th>Nachname</th>
						<th>Vorname</th>
						<th>UID</th>
					</tr>
				</thead>
				<tbody>';
		while($row = $db->db_fetch_object($result))
		{
			$error_warning++;
			echo '
			<tr>
				<td>'.$db->convert_html_chars($row->nachname).'</td>
				<td>'.$db->convert_html_chars($row->vorname).'</td>
				<td>'.$db->convert_html_chars($row->student_uid).'</td>
			</tr>';
		}
		echo '</tbody></table>';
	}
}

/**************************************************************************************
 * Incoming ohne IO Datensatz
 *
 */
$qry = "SELECT
			tbl_person.vorname, tbl_person.nachname, tbl_student.student_uid
		FROM
			public.tbl_student
			JOIN public.tbl_prestudent USING(prestudent_id)
			JOIN public.tbl_person USING(person_id)
		WHERE
			NOT EXISTS(SELECT 1 FROM bis.tbl_bisio WHERE student_uid=tbl_student.student_uid)
			AND EXISTS(SELECT 1 FROM public.tbl_prestudentstatus WHERE prestudent_id=tbl_student.prestudent_id AND status_kurzbz='Incoming')";

if($result = $db->db_query($qry))
{
	echo '<h2>Incoming ohne IO-Datensatz</h2>';

	$anzahl = $db->db_num_rows($result);

	echo '<span class="'.($anzahl>0?'warning':'ok').'">'.$anzahl.' Probleme gefunden</span>';

	if($anzahl>0)
	{
		echo '<br><a href="#Anzeigen" onclick="$(\'#incomingohneio\').toggle(); return false;">Anzeigen &gt;&gt;</a>';
			echo '
		<script type="text/javascript">
		$(document).ready(function()
			{
				$("#incomingohneio").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra"]
				});
			});
		</script>
			<table class="tablesorter" id="incomingohneio" style="display:none">
				<thead>
					<tr>
						<th>Nachname</th>
						<th>Vorname</th>
						<th>UID</th>
					</tr>
				</thead>
				<tbody>';
		while($row = $db->db_fetch_object($result))
		{
			$error_warning++;
			echo '
			<tr>
				<td>'.$db->convert_html_chars($row->nachname).'</td>
				<td>'.$db->convert_html_chars($row->vorname).'</td>
				<td>'.$db->convert_html_chars($row->student_uid).'</td>
			</tr>';
		}
		echo '</tbody></table>';
	}
}

/**************************************************************************************
 * Personenkennzeichen passt nicht zur Studiengangskennzahl
 *
 */
$qry = "SELECT
			tbl_person.vorname, tbl_person.nachname, tbl_student.student_uid, tbl_student.matrikelnr, tbl_student.studiengang_kz
		FROM
			public.tbl_student
			JOIN public.tbl_prestudent USING(prestudent_id)
			JOIN public.tbl_person USING(person_id)
		WHERE
			tbl_student.studiengang_kz<1000 AND tbl_student.studiengang_kz>0
			AND tbl_student.studiengang_kz::text!=trim(leading '0' from substring(matrikelnr,5,3))";

if($result = $db->db_query($qry))
{
	echo '<h2>Personenkennzeichen passt nicht zum Studiengang</h2>';

	$anzahl = $db->db_num_rows($result);

	echo '<span class="'.($anzahl>0?'warning':'ok').'">'.$anzahl.' Probleme gefunden</span>';

	if($anzahl>0)
	{
		echo '<br><a href="#Anzeigen" onclick="$(\'#perskzstg\').toggle(); return false;">Anzeigen &gt;&gt;</a>';
			echo '
		<script type="text/javascript">
		$(document).ready(function()
			{
				$("#perskzstg").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra"]
				});
			});
		</script>
			<table class="tablesorter" id="perskzstg" style="display:none">
				<thead>
					<tr>
						<th>Nachname</th>
						<th>Vorname</th>
						<th>UID</th>
						<th>Personenkennzeichen</th>
						<th>StudiengangKZ</th>
					</tr>
				</thead>
				<tbody>';
		while($row = $db->db_fetch_object($result))
		{
			$error_warning++;
			echo '
			<tr>
				<td>'.$db->convert_html_chars($row->nachname).'</td>
				<td>'.$db->convert_html_chars($row->vorname).'</td>
				<td>'.$db->convert_html_chars($row->student_uid).'</td>
				<td>'.$db->convert_html_chars($row->matrikelnr).'</td>
				<td>'.$db->convert_html_chars($row->studiengang_kz).'</td>
			</tr>';
		}
		echo '</tbody></table>';
	}
}

/**************************************************************************************
 * Absolventen ohne Abschlusspruefung
 *
 */
$qry = "SELECT
			tbl_person.vorname, tbl_person.nachname, tbl_student.student_uid
		FROM
			public.tbl_student
			JOIN public.tbl_prestudent USING(prestudent_id)
			JOIN public.tbl_person USING(person_id)
		WHERE
			tbl_student.studiengang_kz<1000 AND tbl_student.studiengang_kz>0
			AND EXISTS (SELECT 1 FROM public.tbl_prestudentstatus WHERE status_kurzbz='Absolvent' AND prestudent_id=tbl_student.prestudent_id)
			AND NOT EXISTS(SELECT 1 FROM lehre.tbl_abschlusspruefung WHERE student_uid=tbl_student.student_uid)";

if($result = $db->db_query($qry))
{
	echo '<h2>Absolventen ohne Abschlusspruefung</h2>';

	$anzahl = $db->db_num_rows($result);

	echo '<span class="'.($anzahl>0?'warning':'ok').'">'.$anzahl.' Probleme gefunden</span>';

	if($anzahl>0)
	{
		echo '<br><a href="#Anzeigen" onclick="$(\'#absolventohnepruefung\').toggle(); return false;">Anzeigen &gt;&gt;</a>';
			echo '
		<script type="text/javascript">
		$(document).ready(function()
			{
				$("#absolventohnepruefung").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra"]
				});
			});
		</script>
			<table class="tablesorter" id="absolventohnepruefung" style="display:none">
				<thead>
					<tr>
						<th>Nachname</th>
						<th>Vorname</th>
						<th>UID</th>
					</tr>
				</thead>
				<tbody>';
		while($row = $db->db_fetch_object($result))
		{
			$error_warning++;
			echo '
			<tr>
				<td>'.$db->convert_html_chars($row->nachname).'</td>
				<td>'.$db->convert_html_chars($row->vorname).'</td>
				<td>'.$db->convert_html_chars($row->student_uid).'</td>
			</tr>';
		}
		echo '</tbody></table>';
	}
}

/**************************************************************************************
 * Studenten mit mind. 2 Stati ohne Noten
 *
 */
$qry = "SELECT
			tbl_person.vorname, tbl_person.nachname, tbl_student.student_uid
		FROM
			public.tbl_student
			JOIN public.tbl_prestudent USING(prestudent_id)
			JOIN public.tbl_person USING(person_id)
		WHERE
			tbl_student.studiengang_kz<1000 AND tbl_student.studiengang_kz>0
			AND 1<(SELECT count(*) FROM public.tbl_prestudentstatus WHERE status_kurzbz='Student' AND prestudent_id=tbl_student.prestudent_id)
			AND NOT EXISTS(SELECT 1 FROM lehre.tbl_zeugnisnote WHERE student_uid=tbl_student.student_uid)";

if($result = $db->db_query($qry))
{
	echo '<h2>Studenten mit mind. 2 Studentenstati aber ohne Noten</h2>';

	$anzahl = $db->db_num_rows($result);

	echo '<span class="'.($anzahl>0?'warning':'ok').'">'.$anzahl.' Probleme gefunden</span>';

	if($anzahl>0)
	{
		echo '<br><a href="#Anzeigen" onclick="$(\'#studentohnenoten\').toggle(); return false;">Anzeigen &gt;&gt;</a>';
			echo '
		<script type="text/javascript">
		$(document).ready(function()
			{
				$("#studentohnenoten").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra"]
				});
			});
		</script>
			<table class="tablesorter" id="studentohnenoten" style="display:none">
				<thead>
					<tr>
						<th>Nachname</th>
						<th>Vorname</th>
						<th>UID</th>
					</tr>
				</thead>
				<tbody>';
		while($row = $db->db_fetch_object($result))
		{
			$error_warning++;
			echo '
			<tr>
				<td>'.$db->convert_html_chars($row->nachname).'</td>
				<td>'.$db->convert_html_chars($row->vorname).'</td>
				<td>'.$db->convert_html_chars($row->student_uid).'</td>
			</tr>';
		}
		echo '</tbody></table>';
	}
}
// ************************************************************************************
echo '<hr>';
echo '<br>Kritische Fehler: '.($error_kritisch>0?'<span class="error">':'<span class="ok">').$error_kritisch.'</span>';
echo '<br>Warnungen: '.($error_warning>0?'<span class="warning">':'<span class="ok">').$error_warning.'</span>';
echo '<br><br><br><br><br><br><br><br></body></html>';
?>
