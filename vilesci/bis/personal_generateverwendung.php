<?php
/* Copyright (C) 2017 fhcomplete.org
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
 * Authors: Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/bisfunktion.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/benutzerfunktion.class.php');
require_once('../../include/funktion.class.php');
require_once('../../include/bisverwendung.class.php');
require_once('../../include/benutzer.class.php');

if (!$db = new basis_db())
	die ('Es konnte keine Verbindung zum Server aufgebaut werden.');

$uid = get_uid();
$datum_obj = new datum();

$fkt_obj = new funktion();
$fkt_obj->getAll();
$fkt_arr = array();
foreach ($fkt_obj->result as $row_fkt)
	$fkt_arr[$row_fkt->funktion_kurzbz] = $row_fkt->beschreibung;

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$studiensemester = new studiensemester();
$studiensemester->getAll();

$stsem_arr = array();
foreach($studiensemester->studiensemester as $row_stsem)
{
	$stsem_arr[$row_stsem->studiensemester_kurzbz]['start']=$row_stsem->start;
	$stsem_arr[$row_stsem->studiensemester_kurzbz]['ende']=$row_stsem->ende;
}
if (!$rechte->isBerechtigt('mitarbeiter/stammdaten', null, 'suid'))
	die ('Sie haben keine Berechtigung für diese Seite');

echo '<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">';
include('../../include/meta/jquery.php');
include('../../include/meta/jquery-tablesorter.php');
echo '
		<link href="../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
		<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
		<title>Mitarbeitermeldung</title>
		<script>
		$(document).ready(function()
			{
				$( ".datepicker_datum" ).datepicker({
					changeMonth: true,
					hangeYear: true,
					dateFormat: "yy-mm-dd",
				});
			});
		</script>
	</head>
<body>
<h2>Freie Lektoren mit Lehrauftrag ohne Verwendung</h2>
Die folgenden freien Lektoren haben einen aktiven Lehrauftrag im Meldezeitraum,
haben jedoch keine aktive Verwendung.<br>
<br>
Beim Klicken auf "Alle Verwendungen generieren/aktualisieren" werden die Verwendungen automatisch verlängert wenn
die letzte Verwendung näher als 10 Monate liegt. Ansonsten wird eine neue Verwendung erstellt. Als neues Ende-Datum
wird das Ende des Semesters letzten Lehrauftrages herangezogen.
<br>
<script type="text/javascript">
	$(document).ready(function()
	{
		$("#t1").tablesorter(
		{
			sortList: [[0,0]],
			widgets: ["zebra"]
		});
	});
</script>';
$qry = "SELECT ma.* FROM
		(
		SELECT
			vorname, nachname, uid, personalnummer, insertamum, anmerkung, aktiv,
			(
				SELECT studiensemester_kurzbz FROM (
					SELECT
						studiensemester_kurzbz, tbl_studiensemester.start
					FROM
						lehre.tbl_lehreinheitmitarbeiter
						JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
						JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
					WHERE
						tbl_lehreinheitmitarbeiter.mitarbeiter_uid = vw_mitarbeiter.uid
					UNION
					SELECT
						studiensemester_kurzbz, tbl_studiensemester.start
					FROM
						lehre.tbl_projektbetreuer
						JOIN lehre.tbl_projektarbeit USING(projektarbeit_id)
						JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
						JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
					WHERE
						tbl_projektbetreuer.person_id=vw_mitarbeiter.person_id
				) a
				ORDER BY start DESC
				LIMIT 1
			) as letzter_lehrauftrag
		FROM
			campus.vw_mitarbeiter
		WHERE
			fixangestellt = false
			AND lektor = true
			AND bismelden = true
			AND personalnummer > 0
			AND EXISTS(
				SELECT
					1
				FROM
					lehre.tbl_lehreinheitmitarbeiter
					JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				WHERE
					tbl_lehreinheitmitarbeiter.mitarbeiter_uid = vw_mitarbeiter.uid
					AND tbl_lehreinheit.studiensemester_kurzbz IN(
							SELECT
								studiensemester_kurzbz
							FROM
								public.tbl_studiensemester
							WHERE start <= now()
							ORDER BY start DESC
							OFFSET 1
							LIMIT 2)
				UNION
				SELECT
					1
				FROM
					lehre.tbl_projektbetreuer
					JOIN lehre.tbl_projektarbeit USING(projektarbeit_id)
					JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				WHERE
					tbl_lehreinheit.studiensemester_kurzbz IN(SELECT
							studiensemester_kurzbz
						FROM
							public.tbl_studiensemester
						WHERE start <= now()
						ORDER BY start DESC
						OFFSET 1
						LIMIT 2)
					AND tbl_projektbetreuer.person_id=vw_mitarbeiter.person_id
				)
			AND NOT EXISTS(
				SELECT 1 FROM bis.tbl_bisverwendung
				WHERE mitarbeiter_uid=vw_mitarbeiter.uid
				AND (ende is null OR ende>=now())
			)
		) ma
		LEFT JOIN public.tbl_studiensemester ON(studiensemester_kurzbz=ma.letzter_lehrauftrag)
		WHERE
			tbl_studiensemester.start >= (SELECT ende FROM bis.tbl_bisverwendung
											WHERE mitarbeiter_uid=ma.uid
											ORDER BY ende DESC LIMIT 1)";
if ($result = $db->db_query($qry))
{
	echo '<br><br>Anzahl:'.$db->db_num_rows($result);
	echo '
	<div style="float:right" >
	<form method="POST" action="personal_generateverwendung.php">
	<input type="hidden" name="action" value="generateall" />
	<input type="submit" value="Alle Verwendungen generieren / aktualisieren" />
	</form>
	</div>';
	echo '

	<br><br>
	<table class="tablesorter" id="t1">
		<thead>
		<tr>
			<th>Nachname</th>
			<th>Vorname</th>
			<th>UID</th>
			<th>Aktiv</th>
			<th>Personalnummer</th>
			<th>Anlagedatum</th>
			<th>Letzer Lehrauftrag</th>
			<th>Aktive Funktionen</th>
			<th>Letzte Verwendung</th>
			<th>Anmerkung</th>
			<th>Aktion</th>
		</tr>
		</thead>
		<tbody>
	';
	while ($row = $db->db_fetch_object($result))
	{
		echo '
		<tr>
			<td>'.$db->convert_html_chars($row->nachname).'</td>
			<td>'.$db->convert_html_chars($row->vorname).'</td>
			<td>'.$db->convert_html_chars($row->uid).'</td>
			<td>'.($db->db_parse_bool($row->aktiv)?'Ja':'<span style="color:red; font-weight:bold">Nein</span>').'</td>
			<td>'.$db->convert_html_chars($row->personalnummer).'</td>
			<td>'.$db->convert_html_chars($datum_obj->formatDatum($row->insertamum,'d.m.Y')).'</td>
			<td>'.$db->convert_html_chars($row->letzter_lehrauftrag).'</td>
			<td>
				<table>';
		$fkt = new benutzerfunktion();
		$fkt->getBenutzerFunktionByUid($row->uid, null, date('Y-m-d'));

		foreach ($fkt->result as $row_fkt)
		{
			echo '<tr>
					<td width="100px;">'.$fkt_arr[$row_fkt->funktion_kurzbz].'</td>
					<td>'.$row_fkt->oe_kurzbz.'</td>
				</tr>';
		}
		echo '</table></td>';
		$bisverwendung = new bisverwendung();
		$bisverwendung->getLastVerwendung($row->uid);
		echo '<td>'.($bisverwendung->beginn != ''?$datum_obj->formatDatum($bisverwendung->beginn,'d.m.Y'):' unbekannt ');
		echo ' - '.($bisverwendung->ende != ''?$datum_obj->formatDatum($bisverwendung->ende,'d.m.Y'):' jetzt ').'</td>';
		echo '<td>'.($row->anmerkung != ''?'<img src="../../skin/images/sticky.png" title="'.$db->convert_html_chars($row->anmerkung).'" />':'').'</td>';

		if(isset($stsem_arr[$row->letzter_lehrauftrag])
			&& $stsem_arr[$row->letzter_lehrauftrag]['start'] > $bisverwendung->ende)
		{
			// wenn das Stsem des letzten Lehrauftrags größer ist als die Verwendung


				// Wenn die letzte Verwendung weniger als 10 Monate alt ist, wird die bestehende
				// Verwendung aktualisiert auf das neue Datum
				// Ansonsten wird eine neue Verwendung erstellt
				$dt_verwendungsendeplus10 = new DateTime($bisverwendung->ende);
				$dt_now = new DateTime();
				$dt_verwendungsendeplus10->add(new DateInterval('P10M'));

				if ($dt_verwendungsendeplus10 > $dt_now)
				{
					$bisverwendung->ende = $stsem_arr[$row->letzter_lehrauftrag]['ende'];
					$bisverwendung->updateamum = date('Y-m-d H:i:s');
					$bisverwendung->updatevon = $uid;
					if(isset($_POST['action']) && $_POST['action']=='generateall')
					{
						if ($bisverwendung->save(false))
						{
							echo '<td>Verwendung verlängert</td>';
						}
						else
						{
							echo '<td>Failed:'.$bisverwendung->errormsg.'</td>';
						}
					}
					else
						echo '<td>Verlängerung bis '.$bisverwendung->ende.'</td>';
				}
				else
				{
					$bisverwendung->beginn = $stsem_arr[$row->letzter_lehrauftrag]['start'];
					$bisverwendung->ende = $stsem_arr[$row->letzter_lehrauftrag]['ende'];
					$bisverwendung->updateamum = date('Y-m-d H:i:s');
					$bisverwendung->updatevon = $uid;
					$bisverwendung->insertamum = date('Y-m-d H:i:s');
					$bisverwendung->insertvon = $uid;
					if(isset($_POST['action']) && $_POST['action']=='generateall')
					{
						if ($bisverwendung->save(true))
						{
							echo '<td>Neue Verwendung erstellt</td>';
						}
						else
						{
							echo '<td>Failed:'.$bisverwendung->errormsg.'</td>';
						}
					}
					else
						echo '<td>Neue Verwendung wird erstellt '.$bisverwendung->beginn.' bis '.$bisverwendung->ende.'</td>';
				}
		}
		else
		{
			echo '<td>passt eigentlich</td>';
		}

		echo '</tr>';
	}
	echo '</tbody></table>';
}

echo '
</body>
</html>';
