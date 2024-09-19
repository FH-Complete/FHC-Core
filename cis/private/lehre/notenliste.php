<?php
/*
 * Copyright (C) 2008 Technikum-Wien
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 * Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 * Rudolf Hangl < rudolf.hangl@technikum-wien.at >
 * Gerald Simane-Sequens < gerald.simane-sequens@technikum-wien.at >
 */
/*
 * Erstellt eine Liste mit den Noten des eingeloggten Studenten
 * das betreffende Studiensemester kann ausgewaehlt werden
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../config/global.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/note.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/studienordnung.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/pruefung.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/prestudent.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);

if (! $db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

if (isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
	$stsem = '';

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="../../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../../vendor/jquery/sizzle/sizzle.js"></script>
	<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>
	<title>' . $p->t('tools/leistungsbeurteilung') . '</title>

	<script language="JavaScript" type="text/javascript">
	function MM_jumpMenu(targ, selObj, restore)
	{
		eval(targ + ".location=\'" + selObj.options[selObj.selectedIndex].value + "\'");

		if(restore)
		{
			selObj.selectedIndex = 0;
		}
	};

	// Add parser through the tablesorter addParser method for sorting Studiensemester
	$.tablesorter.addParser({
		// set a unique id
		id: "studiensemester",
		is: function(s) {
			// return false so this parser is not auto detected
			return false;
		},
		format: function(s) {
			// format data for normalization
			var result = s.substr(2) + s.substr(0, 2);
			return result;
		},
		// set type, either numeric or text
		type: "text"
	});

	$(document).ready(function()
	{
		$("#notenliste").tablesorter(
		{
			headers: {
				1: {
					sorter:"studiensemester"
				}},
			' . ($stsem == 'alle' ? 'sortList: [[1,0],[4,0]],' : 'sortList: [[3,0]],') . '
			widgets: ["zebra"]
		});
	});
	</script>
</head>

<body>
	<h1>' . $p->t('tools/leistungsbeurteilung') . '</h1>';

$user = get_uid();

if (isset($_GET['uid']))
{
	// Administratoren duerfen die UID als Parameter uebergeben um die Notenliste
	// von anderen Personen anzuzeigen

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
	if ($rechte->isBerechtigt('admin'))
	{
		$user = $_GET['uid'];
		$getParam = "&uid=" . $user;
	}
	else
		$getParam = "";
}
else
	$getParam = '';

$datum_obj = new datum();

$error = '';

if (! check_student($user))
{
	$error .= $p->t('tools/mussAlsStudentEingeloggtSein');
}
else
{
	$qry = "SELECT vw_student.vorname, vw_student.nachname, vw_student.wahlname, vw_student.prestudent_id, tbl_studiengang.studiengang_kz
		FROM public.tbl_studiengang JOIN campus.vw_student USING (studiengang_kz)
		WHERE campus.vw_student.uid = " . $db->db_add_param($user) . ";";

	if (! $result = $db->db_query($qry))
		die($p->t('tools/studentWurdeNichtGefunden'));
	else
	{
		$row = $db->db_fetch_object($result);

		$vorname = $row->vorname;
		$nachname = $row->nachname;
		$wahlname = $row->wahlname;
		$prestudent_id = $row->prestudent_id;
		$stg_obj = new studiengang();
		$stg_obj->load($row->studiengang_kz);
		$stg_name = $stg_obj->bezeichnung_arr[$sprache];
		$prestudent_id = $row->prestudent_id;
		$prestudent = new prestudent($prestudent_id);
		if ($prestudent->getLastStatus($prestudent_id))
		{
			$studienplan_id = $prestudent->studienplan_id;
			$studienordnung = new studienordnung();
			if ($studienordnung->getStudienordnungFromStudienplan($studienplan_id))
			{
				$studiengangbezeichnung_sto = $sprache === 'English' ? $studienordnung->__get('studiengangbezeichnung_englisch') : $studienordnung->__get('studiengangbezeichnung');
			}
		}

		$studiengang_bezeichnung = empty($studiengangbezeichnung_sto) ? $stg_name : $studiengangbezeichnung_sto;
	}

	$notenarr = array();
	$note = new note();
	$note->getAll();
	foreach ($note->result as $row)
	{
		$notenarr[$row->note]['bezeichnung'] = $row->bezeichnung;
		$notenarr[$row->note]['notenwert'] = $row->notenwert;
	}

	// Aktuelles Studiensemester ermitteln

	$stsem_obj = new studiensemester();
	if ($stsem == '')
		$stsem = $stsem_obj->getaktorNext();

	// Erstes und letztes Studiensemester mit Studenten-Status ermitteln
	$prestudent = new prestudent();
	// Wenn Incoming, dann Incomingstatus laden, sonst Studentenstatus
	$prestudent->getPrestudentRolle($prestudent_id, 'Incoming');
	if(count($prestudent->result) > 0)
	{
		$prestudent->getFirstStatus($prestudent_id, 'Incoming');
		$firstStudiensemester = $prestudent->studiensemester_kurzbz;
		$prestudent->getLastStatus($prestudent_id, null, 'Incoming');
		$lastStudiensemester = $prestudent->studiensemester_kurzbz;
	}
	else
	{
		$prestudent->getFirstStatus($prestudent_id, 'Student');
		$firstStudiensemester = $prestudent->studiensemester_kurzbz;
		if ($prestudent->getLastStatus($prestudent_id, null, 'Diplomand'))
			$lastStudiensemester = $prestudent->studiensemester_kurzbz;
		elseif ($prestudent->getLastStatus($prestudent_id, null, 'Student'))
			$lastStudiensemester = $prestudent->studiensemester_kurzbz;
	}

	$stsem_obj->getStudiensemesterBetween($firstStudiensemester, $lastStudiensemester);

	echo "<br />";
	echo "<b>".$p->t('global/name').":</b> $vorname $nachname<br />";
	echo "<b>".$p->t('global/studiengang').":</b>  $studiengang_bezeichnung<br />";
	echo "<b>".$p->t('global/studiensemester')."</b> <SELECT name='stsem' onChange=\"MM_jumpMenu('self',this,0)\">";
    echo "<OPTION value='notenliste.php?stsem=alle".$getParam."'>".$p->t('news/allesemester')."</OPTION>";
	$notenImAktuellenStSem = false;
	foreach ($stsem_obj->studiensemester as $semrow)
	{
		if ($stsem == $semrow->studiensemester_kurzbz)
		{
			echo "<OPTION value='notenliste.php?stsem=" . $semrow->studiensemester_kurzbz . $getParam . "' selected>$semrow->studiensemester_kurzbz</OPTION>";
			$notenImAktuellenStSem = true;
		}
		else
		{
			echo "<OPTION value='notenliste.php?stsem=" . $semrow->studiensemester_kurzbz . $getParam . "'>$semrow->studiensemester_kurzbz</OPTION>";
		}
	}
	echo "</SELECT><br />";

	// echo "Datum: ".date('d.m.Y')."<br />";
	echo "<br />";
	if ($notenImAktuellenStSem == false)
	{
		$stsem = 'alle';
	}
	// Lehrveranstaltungen und Noten holen
	if ($stsem != "alle")
	{
		$sqlFilter = " AND tbl_zeugnisnote.studiensemester_kurzbz = " . $db->db_add_param($stsem) . "
                      AND (tbl_lvgesamtnote.studiensemester_kurzbz = " . $db->db_add_param($stsem) . " OR tbl_lvgesamtnote.studiensemester_kurzbz is null) ";
	}
	else
		$sqlFilter = "";

	$qry = "SELECT
			tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_zeugnisnote.note, tbl_zeugnisnote.punkte,
			tbl_lvgesamtnote.note as lvnote, tbl_lvgesamtnote.punkte as lvpunkte,
			tbl_zeugnisnote.benotungsdatum, tbl_lvgesamtnote.freigabedatum, tbl_zeugnisnote.uebernahmedatum,
			tbl_lvgesamtnote.benotungsdatum as lvbenotungsdatum,
			tbl_zeugnisnote.studiensemester_kurzbz AS studiensemester_zeugnis, tbl_lvgesamtnote.studiensemester_kurzbz  AS studiensemester_lvnote,
			tbl_lehrveranstaltung.zeugnis, tbl_lehrveranstaltung.ects
		FROM
			lehre.tbl_lehrveranstaltung, lehre.tbl_zeugnisnote
			LEFT OUTER JOIN campus.tbl_lvgesamtnote USING (lehrveranstaltung_id, student_uid, studiensemester_kurzbz)
			LEFT OUTER JOIN lehre.tbl_note on tbl_zeugnisnote.note = tbl_note.note
		WHERE
			tbl_zeugnisnote.student_uid = " . $db->db_add_param($user) . $sqlFilter . "
			AND	tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_zeugnisnote.lehrveranstaltung_id";

	if(defined('CIS_NOTENLISTE_OFFIZIELL_ANZEIGEN') && CIS_NOTENLISTE_OFFIZIELL_ANZEIGEN)
		$qry .= " AND tbl_note.offiziell = true";

	$qry .= " ORDER BY tbl_lehrveranstaltung.bezeichnung";

	if ($result = $db->db_query($qry))
	{
		// Tabelle anzeigen
		$tbl = "<table class='tablesorter' id='notenliste' style='width: auto;'>";
		$tblHead = "<thead>
				<tr class='liste'>
					<th>" . $p->t('global/lehrveranstaltung') . "</th>";
		if ($stsem == "alle")
			$tblHead .= "<th>" . $p->t('global/studiensemester') . "</th>";

			$tblHead .= "<th>" . $p->t('benotungstool/lvNote') . "</th>";
		if (defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE)
			$tblHead .= "<th>" . $p->t('benotungstool/punkte') . "</th>";

			$tblHead .= "	<th>" . $p->t('benotungstool/zeugnisnote') . "</th>";
		if (defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE)
			$tblHead .= "<th>" . $p->t('benotungstool/punkte') . "</th>";

		$tblHead .= "
					<th>" . $p->t('tools/benotungsdatumDerZeugnisnote') . "</th>
					<th>" . $p->t('benotungstool/pruefung') . "</th>
				</tr>
			</thead>";
		$tblBody = "<tbody>";
		$i = 0;
		$legende = false;
		$notenSummenArray = array();
		while ($row = $db->db_fetch_object($result))
		{
			$lv_obj = new lehrveranstaltung();
			$lv_obj->load($row->lehrveranstaltung_id);

			$i ++;
			$tblBody .= "<tr><td>" . $lv_obj->bezeichnung_arr[$sprache] . ($lv_obj->lehrform_kurzbz != "" && $lv_obj->lehrform_kurzbz != " - " ? " (" . $lv_obj->lehrform_kurzbz . ")" : "") . "</td>";
			if ($stsem == "alle")
				$tblBody .= "<td>" . ($row->studiensemester_zeugnis != '' ? $row->studiensemester_zeugnis : $row->studiensemester_lvnote) . "</th>";

			$tblBody .= "<td>";

			// Nur freigegebene Noten anzeigen
			if ($row->freigabedatum >= $row->lvbenotungsdatum)
			{
				if (isset($notenarr[$row->lvnote]))
					$tblBody .= $notenarr[$row->lvnote]['bezeichnung'];
				else
					$tblBody .= $row->lvnote;

				// Nur Noten, die aufs Zeugnis gedruckt werden für Durchschnittsberechnung addieren
				if ($row->zeugnis == true)
				{
					// Noten ohne Wert werden entfernen
					if(isset($notenarr[$row->note]['notenwert']))
					{
						$notenSummenArray[$row->lehrveranstaltung_id]['notenwert'] = $notenarr[$row->note]['notenwert'];
						$notenSummenArray[$row->lehrveranstaltung_id]['ects'] = $row->ects;
					}
				}
			}
			$tblBody .= "</td>";

			// LV Gesamtnote Punkte
			if (defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE)
			{
				$lvpunkte = ($row->lvpunkte != '' ? (float) $row->lvpunkte : '');
				$tblBody .= "<td>" . $lvpunkte . "</td>";
			}

			if ($row->note != $row->lvnote && $row->lvnote != NULL)
			{
				$markier = " style='background-color: #FFD999;'";
				$legende = true;
			}
			else
				$markier = "";
			$tblBody .= "<td " . $markier . ">";

			if (isset($notenarr[$row->note]))
				$tblBody .= $notenarr[$row->note]['bezeichnung'];
			else
				$tblBody .= $row->note;

			$tblBody .= "</td>";

			if (defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE)
			{
				$punkte = ($row->punkte != '' ? ((float) $row->punkte) : '');
				$tblBody .= "<td>" . $punkte . "</td>";
			}

			$tblBody .= '<td>' . $datum_obj->formatDatum($row->benotungsdatum, 'Y-m-d') . '</td>';

			$pruefung = new pruefung();
			$pruefung->getPruefungen($user, null, $row->lehrveranstaltung_id, $stsem);

			if (count($pruefung->result) > 0)
			{
				$freigabedatum = $row->uebernahmedatum;
				$tblBody .= '<td>';
				foreach ($pruefung->result as $row)
				{
					if (isset($notenarr[$row->note]))
						$note = $notenarr[$row->note]['bezeichnung'];
					else
						$note = $row->note;

					if ($row->punkte != '')
						$punkte = ' (' . (float) $row->punkte . ')';
					else
						$punkte = '';

					if ($datum_obj->formatDatum($freigabedatum, "Y-m-d") >= $row->datum)
						$tblBody .= $row->pruefungstyp_beschreibung . ' ' . $datum_obj->formatDatum($row->datum, 'd.m.Y') . ' ' . $note . $punkte . '<br>';
				}
				$tblBody .= '</td>';
			}
			else
				$tblBody .= '<td></td>';

			$tblBody .= "</tr>";
		}
		// Durchschnitt und gewichteten Durchschnitt berechnen
		$notenSumme = 0;
		$notenSummeGewichtet = 0;
		$ectsSumme = 0;
		$anzahlLv = 0;
		foreach ($notenSummenArray AS $key => $value)
		{
			$anzahlLv++;
			$notenSumme += $value['notenwert'];
			$ectsSumme += $value['ects'];
			$notenSummeGewichtet += $value['notenwert'] * $value['ects'];
		}

		$tblBody .= "</tbody>";
		$tblFoot = "<tfoot>";

		if ($anzahlLv != 0)
			$notenDurchschnitt = round($notenSumme / $anzahlLv, 2);
		else
			$notenDurchschnitt = 0;

		if ($ectsSumme != 0)
			$notenDurchschnittGewichtet = round($notenSummeGewichtet / $ectsSumme, 2);
		else
			$notenDurchschnittGewichtet = 0;

		$tblFoot .= '<tr>';
		$tblFoot .= '<td colspan="'.($stsem == "alle" ? 3 : 2).'" align="right"><b>' . $p->t("tools/notendurchschnittDerZeugnisnote") . '</b></td>';
		$tblFoot .= '<td style="background-color: #EEEEEE;">'.$notenDurchschnitt.'</td>';
		$tblFoot .= '<td colspan="2"></td>';
		$tblFoot .= "</tr>";

		$tblFoot .= '<tr>';
		$tblFoot .= '<td colspan="'.($stsem == "alle" ? 3 : 2).'" align="right"><b>' . $p->t("tools/gewichteterNotendurchschnittDerZeugnisnote") . '</b></td>';
		$tblFoot .= '<td style="background-color: #EEEEEE;">'.$notenDurchschnittGewichtet.'</td>';
		$tblFoot .= '<td colspan="2"></td>';

		$tblFoot .= "</tr>";

		$tblFoot .= "</tfoot>";

		if (!defined('CIS_NOTENLISTE_DURCHSCHNITT_ANZEIGEN') || (defined('CIS_NOTENLISTE_DURCHSCHNITT_ANZEIGEN') && CIS_NOTENLISTE_DURCHSCHNITT_ANZEIGEN))
		{
			$tbl .= $tblHead.$tblFoot.$tblBody;
			$tbl .= "<table><tbody><tr><td width='20' style='text-align: right;'>*</td><td>" . $p->t('tools/legendeNotendurchschnitt') . "</td></tr>";
			$tbl .= "<tr><td width='20' style='text-align: right;'>**</td><td>" . $p->t('tools/legendeGewichteterNotendurchschnitt') . "</td></tr>";
		}
		else
			$tbl .= $tblHead.$tblBody;

		if ($legende)
		{
			$tbl .= "<tr><td width='20' style='background-color: #FFD999;'></td><td>" . $p->t('tools/hinweistextMarkierung') . "</td></tr>";
		}
		$tbl .= "</tbody></table></table>";
		if ($i == 0)
			echo $p->t('tools/nochKeineBeurteilungEingetragen');
		else
		{
			$tbl .= "</table><br><br><br>";
			echo $tbl;
		}
	}
	else
	{
		$error .= $p->t('tools/fehlerBeimAuslesenDerNoten');
	}
}
echo $error;
echo '</body>
</html>';
?>
