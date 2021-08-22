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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/* Erstellt einen Lehrauftrag im PDF Format
 *
 * Erstellt ein XML File fuer den Lehrauftrag
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/lehreinheit.class.php');
require_once('../include/organisationseinheit.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/bisverwendung.class.php');

if (isset($_SERVER['REMOTE_USER']))
{
	// Wenn das Script direkt aufgerufen wird muss es ein Admin sein
	$user=get_uid();
	$berechtigung = new benutzerberechtigung();
	$berechtigung->getBerechtigungen($user);
	if (!$berechtigung->isBerechtigt('admin'))
		die('Sie haben keine Berechtigung fuer diese Seite');
}

// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");

//Parameter holen
if (isset($_GET['uid']))
	$uid = $_GET['uid'];
else
	$uid = null;

if (isset($_GET['stg_kz']))
	$studiengang_kz = $_GET['stg_kz'];
else
	die('Fehlerhafte Parameteruebergabe');
if (isset($_GET['ss']))
	$ss = $_GET['ss'];
else
	die('Fehlerhafte Parameteruebergabe');

// GENERATE XML
$xml = '<?xml version="1.0" encoding="UTF-8" ?><lehrauftraege>';
$stg_arr = array();
$studiengang = new studiengang();
$studiengang->getAll(null, false);

foreach ($studiengang->result as $row)
	$stg_arr[$row->studiengang_kz] = $row->kuerzel;

//Studiengang laden
$studiengang = new studiengang($studiengang_kz);

//Organisationseinheiten laden
$oe_arr = array();
$organisationseinheit_obj = new organisationseinheit();
$organisationseinheit_obj->getAll();
foreach ($organisationseinheit_obj->result as $oe)
{
	$oe_arr[$oe->oe_kurzbz] = $oe->bezeichnung;
}

//Studiengangsleiter holen
$stgl = '';
$db = new basis_db();
if ($studiengang_kz != '')
{
	$studiengang_obj = new studiengang();
	$stgleiter = $studiengang_obj->getLeitung($studiengang_kz);

	foreach ($stgleiter as $stgleiter_uid)
	{
		$row = new mitarbeiter($stgleiter_uid);
		$stgl .= trim($row->titelpre.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost);
	}
}

if ($uid == null)
{
	$qry = "
			SELECT
				distinct mitarbeiter_uid
			FROM (
					SELECT
						tbl_lehreinheitmitarbeiter.mitarbeiter_uid
					FROM
						lehre.tbl_lehreinheitmitarbeiter, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung
					WHERE
						tbl_lehreinheitmitarbeiter.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND
						tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND
						tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER)." AND
						tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($ss)."
					UNION
					SELECT
						tbl_benutzer.uid as mitarbeiter_uid
					FROM
						lehre.tbl_projektbetreuer, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung,
						public.tbl_benutzer, lehre.tbl_projektarbeit, campus.vw_student, public.tbl_mitarbeiter
					WHERE
						tbl_projektbetreuer.person_id=tbl_benutzer.person_id AND
						tbl_projektarbeit.projektarbeit_id=tbl_projektbetreuer.projektarbeit_id AND
						student_uid=vw_student.uid AND
						tbl_benutzer.uid = tbl_mitarbeiter.mitarbeiter_uid AND
						tbl_lehreinheit.lehreinheit_id=tbl_projektarbeit.lehreinheit_id AND
						tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($ss)." AND
						tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id AND
						tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER)." AND
						tbl_projektbetreuer.stunden!='0'
					) as mitarbeiter ORDER BY mitarbeiter_uid";

	if ($db->db_query($qry))
	{
		while ($row = $db->db_fetch_object())
		{
			drawLehrauftrag($row->mitarbeiter_uid);
		}
	}
}
else
	drawLehrauftrag($uid);

function drawLehrauftrag($uid)
{
	global $studiengang;
	global $studiengang_kz;
	global $oe_arr;
	global $stg_arr;
	global $ss;
	global $xml;
	global $stgl;

	$db = new basis_db();

	$xml.='<lehrauftrag>
		<studiengang><![CDATA[FH-';
	//Studiengang
	$typ='';
	if ($studiengang->typ=='d')
	{
		$xml.= 'Diplom-';
		$typ = 'Diplom';
	}
	elseif ($studiengang->typ=='m')
	{
		$xml.= 'Master-';
		$typ = 'Master';
	}
	elseif ($studiengang->typ=='b')
	{
		$xml.= 'Bachelor-';
		$typ = 'Bachelor';
	}

	$xml.= 'Studiengang '.$studiengang->bezeichnung.']]></studiengang>';
	$xml.= '<studiengang_bezeichnung><![CDATA['. $studiengang->bezeichnung. ']]></studiengang_bezeichnung>';
	$xml.= '<studiengang_bezeichnung_englisch><![CDATA['. $studiengang->english. ']]></studiengang_bezeichnung_englisch>';
	$xml.= '<studiengang_typ><![CDATA['. $typ. ']]></studiengang_typ>';

	//Studiensemester
	if (substr($ss,0,2) == 'WS')
		$studiensemester = 'Wintersemester '.substr($ss,2);
	else
		$studiensemester = 'Sommersemester '.substr($ss,2);
	$xml .= '<studiensemester_kurzbz><![CDATA['. $ss. ']]></studiensemester_kurzbz>
		<studiensemester><![CDATA['. $studiensemester. ']]></studiensemester>';

	//Lektor
	$qry = "
		SELECT
			*
		FROM
			campus.vw_mitarbeiter
			LEFT JOIN public.tbl_adresse USING(person_id)
		WHERE
			uid=".$db->db_add_param($uid)."
		ORDER BY zustelladresse DESC, firma_id
		LIMIT 1";

	if ($result = $db->db_query($qry))
	{
		if ($row = $db->db_fetch_object($result))
		{
			$firmenanschrift = false;
			if ($row->firma_id != '')
			{
				$qry ="
					SELECT
						tbl_firma.name, tbl_adresse.strasse, tbl_adresse.plz, tbl_adresse.ort
					FROM
						public.tbl_firma
						JOIN public.tbl_adresse USING(firma_id)
					WHERE
						tbl_firma.firma_id=".$db->db_add_param($row->firma_id)."
						AND person_id=".$db->db_add_param($row->person_id)."
					LIMIT 1";

				if ($result_firma = $db->db_query($qry))
				{
					if ($row_firma = $db->db_fetch_object($result_firma))
					{
						$name_gesamt = $row_firma->name;
						$strasse = $row_firma->strasse;
						$plz = $row_firma->plz;
						$ort = $row_firma->ort;
						$zuhanden = "zu Handen ".trim($row->titelpre.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost);
						$firmenanschrift = true;
					}
				}
			}

			if (!$firmenanschrift)
			{
				$strasse = $row->strasse;
				$plz = $row->plz;
				$ort = $row->ort;
				$name_gesamt = trim($row->titelpre.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost);
				$zuhanden='';
			}
			// Lädt die letzte (aktuellste) Verwendungen eines Mitarbeiters um die inkludierte Lehre auslesen zu können
			$bis = new bisverwendung();
			$bis->getLastAktVerwendung($uid);

			$xml .= '
		<mitarbeiter>
			<titelpre><![CDATA['.$row->titelpre.']]></titelpre>
			<vorname><![CDATA['.$row->vorname.']]></vorname>
			<familienname><![CDATA['.$row->nachname.']]></familienname>
			<titelpost><![CDATA['.$row->titelpost.']]></titelpost>
			<anschrift><![CDATA['.$strasse.']]></anschrift>
			<name_gesamt><![CDATA['.$name_gesamt.']]></name_gesamt>
			<zuhanden><![CDATA['.$zuhanden.']]></zuhanden>
			<plz><![CDATA['.$plz.']]></plz>
			<ort><![CDATA['.$ort.']]></ort>
			<svnr><![CDATA['.$row->svnr.']]></svnr>
			<personalnummer><![CDATA['.$row->personalnummer.']]></personalnummer>
			<inkludierte_lehre><![CDATA['.$bis->inkludierte_lehre.']]></inkludierte_lehre>
		</mitarbeiter>';
		}
	}

	//Lehreinheiten
	$qry = "
		SELECT
			*
		FROM
			campus.vw_lehreinheit
		WHERE
			mitarbeiter_uid=".$db->db_add_param($uid)."
			AND studiensemester_kurzbz=".$db->db_add_param($ss);

	if ($studiengang_kz != '') //$studiengang_kz!='0' &&
        $qry .= " AND lv_studiengang_kz=".$db->db_add_param($studiengang_kz);
	$qry .= " ORDER BY lv_orgform_kurzbz, lv_bezeichnung, lehreinheit_id";
	$lv = array();
	$anzahl_lvs = 0;
	if ($result = $db->db_query($qry))
	{
		$last_le = '';
		$gesamtkosten = 0;
		$gesamtstunden = 0;
		$gruppen = array();
		$grp = '';
		$gruppen_getrennt = '';
		$einzelgruppe = '';
		while ($row = $db->db_fetch_object($result))
		{
			if ($last_le != $row->lehreinheit_id && $last_le != '')
			{
				array_unique($gruppen);
				sort($gruppen);
				foreach ($gruppen as $gruppe)
				{
					$grp .= $gruppe.' ';
					$gruppen_getrennt .= '<einzelgruppe><![CDATA['.$gruppe.']]></einzelgruppe>';
				}
				$einzelgruppe = $gruppen_getrennt;
				$lv[$anzahl_lvs]['lehreinheit_id'] = $lehreinheit_id;
				$lv[$anzahl_lvs]['lehrveranstaltung'] = $lehrveranstaltung;
				$lv[$anzahl_lvs]['fachbereich'] = (isset($oe_arr[$lehrfach_oe_kurzbz])?$oe_arr[$lehrfach_oe_kurzbz]:'');
				$lv[$anzahl_lvs]['gruppe'] = ($grp != ''?trim($grp):' ');
				$lv[$anzahl_lvs]['stunden'] = ($stunden != ''?$stunden:' ');
				$lv[$anzahl_lvs]['satz'] = ($satz != ''?$satz:' ');
				$lv[$anzahl_lvs]['faktor'] = ($faktor != ''?$faktor:' ');
				$lv[$anzahl_lvs]['brutto'] = number_format($brutto,2,',','.');
				$lv[$anzahl_lvs]['einzelgruppe'] = ($gruppen_getrennt != ''?$gruppen_getrennt:' ');
				$anzahl_lvs++;

				$gesamtkosten = $gesamtkosten + $brutto;
				$gesamtstunden = $gesamtstunden + $stunden;

				$lehreinheit_id = '';
				$lehrveranstaltung = '';
				$lehrfach_oe_kurzbz = '';
				$gruppen = array();
				$stunden = '';
				$satz = '';
				$faktor = '';
				$brutto = '';
				$grp = '';
				$gruppen_getrennt = '';
			}

			$lehreinheit_id = $row->lehreinheit_id;
			$lehrveranstaltung = CutString($row->lv_bezeichnung, 30, '...').' '.$row->lehrform_kurzbz.' '.$row->lv_semester.'. Semester';
			$lehrfach_oe_kurzbz = $row->lehrfach_oe_kurzbz;

			if ($row->gruppe_kurzbz != '')
				$gruppen[] = $row->gruppe_kurzbz;
			else
				$gruppen[] = trim($stg_arr[$row->studiengang_kz].'-'.$row->semester.$row->verband.$row->gruppe).' ';

			$stunden = $row->semesterstunden;
			$satz = $row->stundensatz;
			$faktor = $row->faktor;
			$brutto = $row->semesterstunden * $row->stundensatz;
			$last_le = $row->lehreinheit_id;
		}
		array_unique($gruppen);
		sort($gruppen);
		foreach ($gruppen as $gruppe)
		{
			$grp .= $gruppe.' ';
			$gruppen_getrennt .= '<einzelgruppe><![CDATA['.$gruppe.']]></einzelgruppe>';
		}
		if (isset($lehreinheit_id))
		{
			$lv[$anzahl_lvs]['lehreinheit_id'] = (isset($lehreinheit_id)?$lehreinheit_id:' ');
			$lv[$anzahl_lvs]['lehrveranstaltung'] = (isset($lehrveranstaltung)?$lehrveranstaltung:' ');
			$lv[$anzahl_lvs]['fachbereich'] = (isset($lehrfach_oe_kurzbz)?$oe_arr[$lehrfach_oe_kurzbz]:' ');
			$lv[$anzahl_lvs]['gruppe'] = ($grp!=''?trim($grp):' ');
			$lv[$anzahl_lvs]['stunden'] = (isset($stunden)?$stunden:' ');
			$lv[$anzahl_lvs]['satz'] = (isset($satz)?$satz:' ');
			$lv[$anzahl_lvs]['faktor'] = (isset($faktor)?$faktor:' ');
			$lv[$anzahl_lvs]['brutto'] = (isset($brutto)?number_format($brutto,2,',','.'):' ');
			$lv[$anzahl_lvs]['einzelgruppe'] = ($gruppen_getrennt!=''?$gruppen_getrennt:' ');
			$anzahl_lvs++;

			if (isset($brutto))
				$gesamtkosten = $gesamtkosten + $brutto;
			if (isset($stunden))
				$gesamtstunden = $gesamtstunden + $stunden;
		}
	}
	$qry = "SELECT tbl_projektarbeit.projektarbeit_id
				,tbl_projektbetreuer.faktor
				,tbl_projektbetreuer.stunden
				,tbl_projektbetreuer.stundensatz
				,tbl_lehrveranstaltung.semester
				,vorname
				,nachname
				,vw_student.studiengang_kz
				,projekttyp_kurzbz
				,lehrfach.oe_kurzbz
			FROM lehre.tbl_projektbetreuer
				,lehre.tbl_lehreinheit
				,lehre.tbl_lehrveranstaltung AS lehrfach
				,lehre.tbl_lehrveranstaltung
				,public.tbl_organisationseinheit
				,public.tbl_benutzer
				,lehre.tbl_projektarbeit
				,campus.vw_student
			WHERE tbl_projektbetreuer.person_id = tbl_benutzer.person_id
				AND tbl_benutzer.uid = ".$db->db_add_param($uid)."
				AND tbl_projektarbeit.projektarbeit_id = tbl_projektbetreuer.projektarbeit_id
				AND student_uid = vw_student.uid
				AND tbl_organisationseinheit.oe_kurzbz = tbl_lehrveranstaltung.oe_kurzbz
				AND tbl_lehreinheit.lehreinheit_id = tbl_projektarbeit.lehreinheit_id
				AND tbl_lehreinheit.lehrfach_id = lehrfach.lehrveranstaltung_id
				AND tbl_lehreinheit.studiensemester_kurzbz = ".$db->db_add_param($ss)."
				AND tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id";
	if ($studiengang_kz != '')
		$qry .= " AND tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);
	if ($result = $db->db_query($qry))
	{
		while ($row = $db->db_fetch_object($result))
		{
			$stg = new studiengang();
			$stg->load($row->studiengang_kz);
			$stg_kuerzel = $stg->kuerzel;

			$brutto = $row->stunden * $row->stundensatz;
			if ($row->stunden != 0)
			{
				switch ($row->projekttyp_kurzbz)
				{
					case 'Bachelor':  $kuerzel = 'BA'; break;
					case 'Diplom':    $kuerzel = 'DA'; break;
					case 'Projekt':   $kuerzel = 'PJ'; break;
					case 'Praktikum': $kuerzel = 'PX'; break;
					case 'Praxis':    $kuerzel = 'PX'; break;
					default:          $kuerzel = 'PA'; break;
				}

				$lv[$anzahl_lvs]['lehreinheit_id'] = (isset($row->projektarbeit_id)?$kuerzel.$row->projektarbeit_id:' ');
				$lv[$anzahl_lvs]['lehrveranstaltung'] = 'Betreuung '.$row->vorname.' '.$row->nachname;
				$lv[$anzahl_lvs]['fachbereich'] = (isset($row->oe_kurzbz) && array_key_exists($row->oe_kurzbz, $oe_arr)?$oe_arr[$row->oe_kurzbz]:' ');
				$lv[$anzahl_lvs]['gruppe'] = ' ';
				$lv[$anzahl_lvs]['stunden'] = (isset($row->stunden)?number_format($row->stunden,2):' ');
				$lv[$anzahl_lvs]['satz'] = (isset($row->stundensatz)?$row->stundensatz:' ');
				$lv[$anzahl_lvs]['faktor'] = (isset($row->faktor)?$row->faktor:'');
				$lv[$anzahl_lvs]['brutto'] = (isset($brutto)?number_format($brutto,2,',','.'):' ');
				$lv[$anzahl_lvs]['einzelgruppe'] = '<einzelgruppe><![CDATA['.$stg_kuerzel.'-'.$row->semester.']]></einzelgruppe>';
				$anzahl_lvs++;

				$gesamtkosten = $gesamtkosten + $brutto;
				$gesamtstunden = $gesamtstunden + $row->stunden;
			}
		}
	}



	foreach ($lv as $lv_row)
	{
		$xml .= '
			<lehreinheit>
				<lehreinheit_id><![CDATA['.$lv_row['lehreinheit_id'].']]></lehreinheit_id>
				<lehrveranstaltung><![CDATA['.$lv_row['lehrveranstaltung'].']]></lehrveranstaltung>
				<fachbereich><![CDATA['.$lv_row['fachbereich'].']]></fachbereich>
				<gruppe><![CDATA['.$lv_row['gruppe'].']]></gruppe>
				<gruppen_getrennt>'. $lv_row['einzelgruppe']. '</gruppen_getrennt> <!-- Variable enthält CDATA tags-->
				<stunden><![CDATA['.$lv_row['stunden'].']]></stunden>
				<satz><![CDATA['.$lv_row['satz'].']]></satz>
				<faktor><![CDATA['.$lv_row['faktor'].']]></faktor>
				<brutto><![CDATA['.$lv_row['brutto'].']]></brutto>
			</lehreinheit>';
	};

	// Gesamtstunden und Gesamtkosten
	$xml .= "
		<gesamtstunden><![CDATA[".number_format($gesamtstunden,2)."]]></gesamtstunden>
		<gesamtbetrag><![CDATA[".number_format($gesamtkosten,2,',','.')."]]></gesamtbetrag>";


	$xml .= "
		<studiengangsleiter><![CDATA[$stgl]]></studiengangsleiter>";

	$xml .= '
		<datum><![CDATA['.date('d.m.Y').']]></datum>
	</lehrauftrag>
	';
}

// END GENERATE XML
echo $xml.'</lehrauftraege>';

?>
