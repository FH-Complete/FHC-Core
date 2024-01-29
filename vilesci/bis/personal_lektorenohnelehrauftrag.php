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

if (!$rechte->isBerechtigt('mitarbeiter/stammdaten', null, 'suid'))
	die ('Sie haben keine Berechtigung für diese Seite');

if (isset($_POST['action']) && $_POST['action'] == 'deaktivieren')
{
	$benutzer = new benutzer();
	if ($benutzer->load($_POST['uid']))
	{
		$benutzer->bnaktiv = false;
		$benutzer->updateamum = date('Y-m-d H:i:s');
		$benutzer->updatevon = $uid;
		if ($benutzer->save(false, false))
		{
			$bisverwendung = new bisverwendung();
			if ($bisverwendung->getLastVerwendung($_POST['uid']))
			{
				if ($bisverwendung->ende == '')
				{
					$bisverwendung->ende = $_POST['datum'];
					$bisverwendung->updateamum = date('Y-m-d H:i:s');
					$bisverwendung->updatevon = $uid;

					if ($bisverwendung->save(false))
						exit ('true');
				}
				else
				{
					exit ('true');
				}
			}
			else
				exit ('Fehler beim Laden der Verwendung:'.$bisverwendung->errormsg);
		}
		else
			exit ('Fehler beim Deaktivieren:'.$benutzer->errormsg);
	}
	else
		exit ('Fehler beim Laden des Benutzers');
}

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
<h2>Aktive freie Lektoren ohne Lehrauftrag</h2>
Die folgenden freien Lektoren haben seit mind. 3 Semestern keinen Lehrauftrag und sind nach wie vor aktiv.
Wählen sie ein Datum und klicken Sie auf den Link "deaktivieren" um den Mitarbeiter zu deaktivieren und
die Verwendung zum angegebenen Datum zu beenden.
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
	function deaktiviere(uid)
	{
		var datum = $("#deaktivierungsdatum").val();

		$.ajax({
			type:"POST",
			url:"personal_lektorenohnelehrauftrag.php",
			data:{ "action": "deaktivieren", "uid": uid, "datum": datum },
			success: function(data)
			{
				if(data=="true")
				{
					$("#deaktivierungslink_"+uid).hide();
					$("#infobox_"+uid).text("OK");
				}
				else
				{
					$("#infobox_"+uid).text("ERROR:"+data);
				}
			},
			error: function() { alert("error"); }
		});
	}
</script>';
$qry = "SELECT
			vorname, nachname, uid, personalnummer, insertamum,anmerkung,
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
			aktiv = true
			AND fixangestellt = false
			AND lektor = true
			AND bismelden = true
			AND personalnummer > 0
			AND insertamum <= now() - '5 months'::interval
			AND NOT EXISTS(
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
							LIMIT 3)
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
						LIMIT 3)
					AND tbl_projektbetreuer.person_id=vw_mitarbeiter.person_id
				)";
if ($result = $db->db_query($qry))
{
	echo '<br><br>Anzahl:'.$db->db_num_rows($result);
	echo '
	<div style="float:right" >Beendingungsdatum der Verwendung:
	<input class="datepicker_datum" type="text" size="10" value="'.(date('Y')-2).'-12-31" id="deaktivierungsdatum"/>
	</div>
	<br><br>
	<table class="tablesorter" id="t1">
		<thead>
		<tr>
			<th>Nachname</th>
			<th>Vorname</th>
			<th>UID</th>
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
		echo '<td>'.($bisverwendung->beginn != ''?$datum_obj->formatDatum($bisverwendung->beginn,'d.m.Y'):' jetzt ');
		echo ' - '.($bisverwendung->ende != ''?$datum_obj->formatDatum($bisverwendung->ende,'d.m.Y'):' jetzt ').'</td>';
		echo '<td>'.($row->anmerkung != ''?'<img src="../../skin/images/sticky.png" title="'.$db->convert_html_chars($row->anmerkung).'" />':'').'</td>';
		echo '
			<td>
			<span id="deaktivierungslink_'.$row->uid.'">
			<a href="#deaktivieren" onclick="deaktiviere(\''.$row->uid.'\');return false;">deaktivieren</a>
			</span>
			<span id="infobox_'.$row->uid.'"></span>
			</td>
		</tr>';
	}
	echo '</tbody></table>';
}

echo '
</body>
</html>';
