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
 *			Manuela Thamer < manuela.thamer@technikum-wien.at >
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
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

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

foreach ($studiensemester->studiensemester as $row_stsem)
{
	$stsem_arr[$row_stsem->studiensemester_kurzbz]['start'] = $row_stsem->start;
	$stsem_arr[$row_stsem->studiensemester_kurzbz]['ende'] = $row_stsem->ende;
}

if (!$rechte->isBerechtigt('mitarbeiter/stammdaten', null, 'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

/**
 * baut die Query
 * @param string $maUid UID des Mitarbeiters.
 * @return string $qry
 */
function buildSQL($maUid = null)
{
	$condition = ($maUid !== null) ? " AND ma.uid = '". $maUid. "' " : '';
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
			LEFT JOIN public.tbl_studiensemester ON(studiensemester_kurzbz=ma.letzter_lehrauftrag)$condition
			WHERE
				tbl_studiensemester.start >= (SELECT ende FROM bis.tbl_bisverwendung
												WHERE mitarbeiter_uid=ma.uid
												ORDER BY ende DESC LIMIT 1)";
	return $qry;
}

/**
 * verlängert die Benutzerfunktion für den betreffenden MA
 * @param string $maUid UID des Mitarbeiters.
 * @param string $stsem_arr Array der Studiensemester.
 * @return String $outputOeFunktion
 */
function verlaengereOeFunktion($maUid, $stsem_arr)
{
	$uid = get_uid();
	$lastLA = '';
	$outputOeFunktion = 'true';
	$qry = buildSQL($maUid);

	if (!$db = new basis_db())
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	if ($result = $db->db_query($qry))
	{
		while ($row = $db->db_fetch_object($result))
		{
			$lastLA = $row->letzter_lehrauftrag;
		}
	}

	//bisverwendung
	$bisverwendung = new bisverwendung();
	$bisverwendung->getLastVerwendung($maUid);

	if (isset($stsem_arr[$lastLA]) && $stsem_arr[$lastLA]['start'] > $bisverwendung->ende)
	{
		$dt_verwendungsendeplus10 = new DateTime($bisverwendung->ende);
		$dt_now = new DateTime();
		$dt_verwendungsendeplus10->add(new DateInterval('P10M'));

		//letzte Benutzerfunktion
		$lastFkt = new benutzerfunktion();
		$lastFkt->getLastBenutzerFunktionByUid($maUid, 'oezuordnung');


		foreach ($lastFkt->result as $row2)
		{
			$vonBf = $row2->datum_von;
			$bisBf = $row2->datum_bis;
			$bisBF10 = new DateTime($bisBf);
			$bisBF10->add(new DateInterval('P10M'));
			$bfId = $row2->benutzerfunktion_id;
			$bfOe_kurzbz = $row2->oe_kurzbz;
			$bfFachbereich_kurzbz = $row2->fachbereich_kurzbz;
			$bfKurzbz = $row2->funktion_kurzbz;
			$bfInsertamum = $row2->insertamum;
			$bfInsertvon = $row2->insertvon;
			$bfUpdateamum = $row2->updateamum;
			$bfBezeichnung = $row2->bezeichnung;
			$bfSem = $row2->semester;
			$bfWochenstunden = $row2->wochenstunden;
		}

		if ($bisBf != null)
		{
			$lastFkt->datum_bis = $stsem_arr[$lastLA]['ende'];
			$lastFkt->updateamum = date('Y-m-d H:i:s');
			$lastFkt->updatevon = $uid;
			$lastFkt->benutzerfunktion_id = $bfId;
			$lastFkt->fachbereich_kurzbz = $bfFachbereich_kurzbz;
			$lastFkt->funktion_kurzbz = $bfKurzbz;
			$lastFkt->uid = $row2->uid;
			$lastFkt->oe_kurzbz = $bfOe_kurzbz;
			$lastFkt->insertamum = $bfInsertamum;
			$lastFkt->insertvon = $bfInsertvon;
			$lastFkt->datum_von = $vonBf;
			$lastFkt->bezeichnung = $bfBezeichnung;
			$lastFkt->semester = $bfSem;
			$lastFkt->wochenstunden = $bfWochenstunden;

			if ($bisBF10 > $dt_now)
			{
				if ($lastFkt->save(false))
				{
					$outputOeFunktion = 'Benutzerfunktion verlängert';
				}
				else
				{
					$outputOeFunktion = $lastFkt->errormsg;
				}
			}
			else
			{
				$lastFkt->datum_von = $stsem_arr[$lastLA]['start'];
				$lastFkt->insertamum = date('Y-m-d H:i:s');
				$lastFkt->insertvon = $uid;
				if ($lastFkt->save(true))
				{
					$outputOeFunktion = 'Benutzerfunktion neu angelegt';
				}
				else
				{
					$outputOeFunktion = $lastFkt->errormsg;
				}
			}
		}
	}

	return $outputOeFunktion;
}

/**
 * verlängert die Bisverwendung bzw. legt eine neue Bisverwendung an
 * @param string $maUid UID des Mitarbeiters.
 * @param string $stsem_arr Array der Studiensemester.
 * @return String Returnstring
 */
function verlaengereBis($maUid, $stsem_arr)
{
	$uid = get_uid();

	$qry = buildSQL($maUid);

	if (!$db = new basis_db())
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	if ($result = $db->db_query($qry))
	{
		while ($row = $db->db_fetch_object($result))
		{
			$lastLA = $row->letzter_lehrauftrag;
		}
	}

	$bisverwendung = new bisverwendung();
	$bisverwendung->getLastVerwendung($maUid);

	if (isset($stsem_arr[$lastLA]) && $stsem_arr[$lastLA]['start'] > $bisverwendung->ende)
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
			$bisverwendung->ende = $stsem_arr[$lastLA]['ende'];
			$bisverwendung->updateamum = date('Y-m-d H:i:s');
			$bisverwendung->updatevon = $uid;

			$retOe = verlaengereOeFunktion($maUid, $stsem_arr);

			if ($bisverwendung->save(false))
			{
				return "bis verlaengert ". $retOe;
			}
			else
			{
				return ('Fehler beim Verlängern Bisverwendung:'.$bisverwendung->errormsg);
			}
		}
		else
		{
			$bisverwendung->beginn = $stsem_arr[$lastLA]['start'];
			$bisverwendung->ende = $stsem_arr[$lastLA]['ende'];
			$bisverwendung->updateamum = date('Y-m-d H:i:s');
			$bisverwendung->updatevon = $uid;
			$bisverwendung->insertamum = date('Y-m-d H:i:s');
			$bisverwendung->insertvon = $uid;

			$retOe = verlaengereOeFunktion($maUid, $stsem_arr);

			if ($bisverwendung->save(true))
			{
				return "bis erstellt ". $retOe;
			}
			else
			{
				return ('Fehler beim Erstellen Bisverwendung:'.$bisverwendung->errormsg);
			}
		}
	}
}



if (isset($_POST['action']) && $_POST['action'] == 'bisverlaengern')
{
	$retstr = verlaengereBis($_POST['uid'], $stsem_arr);
	exit($retstr);
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
					changeYear: true,
					dateFormat: "yy-mm-dd",
				});
			});
			function verlaengereBis(uid)
			{
				$.ajax({
					type:"POST",
					url:"personal_generateverwendung.php",
					data:{ "action": "bisverlaengern", "uid": uid },
					success: function(data)
					{
						if(data=="true")
						{
							$("#verlaengerungslink_"+uid).hide();
							$("#infobox_"+uid).text("OK");
							$("#outputTest_"+uid).text("OK ich verlängere dich");
						}
						else if(data=="bis verlaengert")
						{
							$("#verlaengerungslink_"+uid).hide();
							$("#infobox_"+uid).text("OK, Bisverwendung verlängert");
						}
						else if(data=="bis verlaengert Benutzerfunktion verlängert")
						{
							$("#verlaengerungslink_"+uid).hide();
							$("#infobox_"+uid).text("OK, Bisverwendung und Benutzerfunktion verlängert");
						}
						else if(data=="bis erstellt Benutzerfunktion verlängert")
						{
							$("#verlaengerungslink_"+uid).hide();
							$("#infobox_"+uid).text("OK, Bisverwendung erstellt, Benutzerfunktion verlängert");
						}
						else if(data=="bis verlaengert Benutzerfunktion neu angelegt")
						{
							$("#verlaengerungslink_"+uid).hide();
							$("#infobox_"+uid).text("OK, Bisverwendung verlängert, Benutzerfunktion neu angelegt");
						}
						else if(data=="bis erstellt Benutzerfunktion neu angelegt")
						{
							$("#verlaengerungslink_"+uid).hide();
							$("#infobox_"+uid).text("OK, Bisverwendung und Benutzerfunktion neu angelegt");
						}
						else
						{
							$("#infobox_"+uid).text("ERROR:"+data);
						}
					},
					error: function() { alert("error"); }
				});
			}
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
$qry = buildSQL();
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
			<td>'.$db->convert_html_chars($datum_obj->formatDatum($row->insertamum, 'd.m.Y')).'</td>
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
		echo '<td>'.($bisverwendung->beginn != ''?$datum_obj->formatDatum($bisverwendung->beginn, 'd.m.Y'):' unbekannt ');
		echo ' - '.($bisverwendung->ende != ''?$datum_obj->formatDatum($bisverwendung->ende, 'd.m.Y'):' jetzt ').'</td>';
		echo '<td>'.($row->anmerkung != ''?'<img src="../../skin/images/sticky.png" title="'.$db->convert_html_chars($row->anmerkung).'" />':'').'</td>';


		if (isset($stsem_arr[$row->letzter_lehrauftrag])
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
				$retOe = verlaengereOeFunktion($row->uid, $stsem_arr);

				if (isset($_POST['action']) && $_POST['action'] == 'generateall')
				{
					if ($bisverwendung->save(false))
					{
						echo '<td>Verwendung verlängert '. $retOe. '</td>';
					}
					else
					{
						echo '<td>Failed:'.$bisverwendung->errormsg.'</td>';
					}
				}
				else
					echo '<td>Verlängerung bis '.$bisverwendung->ende.'<br>
					<span id="verlaengerungslink_'.$row->uid.'">
					<a href="#bisverlaengern" onclick="verlaengereBis(\''.$row->uid.'\');return false;">verlängern</a>
					</span>
					<span id="infobox_'.$row->uid.'"></span>
							<span id="outputTest_'.$row->uid.'"></span>
					</td>';
			}
			else
			{
				$bisverwendung->beginn = $stsem_arr[$row->letzter_lehrauftrag]['start'];
				$bisverwendung->ende = $stsem_arr[$row->letzter_lehrauftrag]['ende'];
				$bisverwendung->updateamum = date('Y-m-d H:i:s');
				$bisverwendung->updatevon = $uid;
				$bisverwendung->insertamum = date('Y-m-d H:i:s');
				$bisverwendung->insertvon = $uid;
				$retOe = verlaengereOeFunktion($row->uid, $stsem_arr);

				if (isset($_POST['action']) && $_POST['action'] == 'generateall')
				{
					if ($bisverwendung->save(true))
					{
						echo '<td>Neue Verwendung erstellt '. $retOe. '</td>';
					}
					else
					{
						echo '<td>Failed:'.$bisverwendung->errormsg.'</td>';
					}
				}
				else
					echo '<td>Neue Verwendung wird erstellt '.$bisverwendung->beginn.' bis '.$bisverwendung->ende.'<br>';


					echo '
					<span id="verlaengerungslink_'.$row->uid.'">
					<a href="#bisverlaengern" onclick="verlaengereBis(\''.$row->uid.'\');return false;">verlängern</a>
					</span>
					<span id="infobox_'.$row->uid.'"></span>
						<span id="outputTest_'.$row->uid.'"></span>
						</td>';
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
