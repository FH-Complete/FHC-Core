<?php
/* Copyright (C) 2008 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
  */
// Erstellt ein Excel mit den Kosten der Lehrveranstaltungen
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/Excel/excel.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/organisationseinheit.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/studiensemester.class.php');

$user = get_uid();

loadVariables($user);

if(isset($_GET['studiensemester_kurzbz']))
	$studiensemester_kurzbz = $_GET['studiensemester_kurzbz'];
else if(isset($_POST['studiensemester_kurzbz']))
	$studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
else
	die('studiensemester_kurzbz muss uebergeben werden');

if(isset($_GET['studiengang_kz']))
	$studiengang_kz = $_GET['studiengang_kz'];
else
	$studiengang_kz='';

if(isset($_GET['semester']))
	$semester = $_GET['semester'];
else
	$semester='';

if(isset($_GET['oe_kurzbz']))
	$oe_kurzbz = $_GET['oe_kurzbz'];
else
	$oe_kurzbz = '';

if(isset($_GET['uid']))
	$uid = $_GET['uid'];
else
	$uid = '';

if(isset($_GET['orgform_kurzbz']))
	$orgform_kurzbz = $_GET['orgform_kurzbz'];
else
	$orgform_kurzbz = '';

$db = new basis_db();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if($studiengang_kz != '')
{
	$studiengang_kz_arr = explode(',',$studiengang_kz);
	foreach ($studiengang_kz_arr AS $kennzahl)
	{
		if (!is_numeric($kennzahl))
		{
			die($kennzahl.' is not an iteger value');
		}
		if(!$rechte->isBerechtigt('assistenz', $kennzahl, 's'))
			die($rechte->errormsg);
	}
}
elseif($oe_kurzbz!='')
{
	if(!$rechte->isBerechtigt('assistenz', $oe_kurzbz, 's'))
		die($rechte->errormsg);
}
else
{
	if(!$rechte->isBerechtigt('assistenz', null, 's'))
		die($rechte->errormsg);
}

$oetyp = new organisationseinheit();
$oetyp->getTypen();

foreach($oetyp->result as $row)
{
	$oetyp_arr[$row->organisationseinheittyp_kurzbz] = $row->bezeichnung;
}

$oe = new organisationseinheit();
$oe->getAll();

foreach($oe->result as $row)
{
	$oe_arr[$row->oe_kurzbz] = $oetyp_arr[$row->organisationseinheittyp_kurzbz].' '.$row->bezeichnung;
}
$oe_arr[''] = '';
$stg_obj = new studiengang();
$stg_obj->getAll('typ, kurzbz', false);

$stsem = new studiensemester();

$previousStsem = $stsem->getPreviousFrom($stsem->getPreviousFrom($studiensemester_kurzbz));
$qry = "
SELECT tbl_lehrveranstaltung.bezeichnung AS lf_bezeichnung,
	tbl_lehrveranstaltung.studiengang_kz,
	tbl_lehrveranstaltung.oe_kurzbz AS lv_oe_kurzbz,
	tbl_lehreinheitmitarbeiter.mitarbeiter_uid,
	tbl_lehrveranstaltung.semester AS lv_semester,
	tbl_lehreinheit.lehreinheit_id,
	tbl_lehreinheitmitarbeiter.stundensatz,
	tbl_lehreinheitmitarbeiter.semesterstunden lemss,
	tbl_lehreinheitmitarbeiter.planstunden,
	tbl_lehreinheitmitarbeiter.anmerkung as mitarbeiter_anmerkung,
	tbl_lehreinheit.stundenblockung,
	tbl_lehreinheit.wochenrythmus,
	tbl_lehreinheit.raumtyp,
	tbl_lehreinheit.raumtypalternativ,
	tbl_lehreinheit.anmerkung as lehreinheit_anmerkung,
	tbl_lehreinheit.studiensemester_kurzbz,
	tbl_lehreinheit.start_kw,
	tbl_lehrveranstaltung.ects,
	tbl_lehrveranstaltung.semesterstunden,
	tbl_lehrveranstaltung.semesterstunden AS sws,
	tbl_lehrveranstaltung.lehrform_kurzbz,
	tbl_lehrveranstaltung.lehrveranstaltung_id,
	tbl_lehrveranstaltung.orgform_kurzbz,
	tbl_lehrveranstaltung.sprache,
	(
		SELECT nachname
		FROM PUBLIC.tbl_person
		JOIN PUBLIC.tbl_benutzer USING (person_id)
		WHERE uid = (
				SELECT mitarbeiter_uid
				FROM lehre.tbl_lehreinheitmitarbeiter
				WHERE lehre.tbl_lehreinheitmitarbeiter.lehreinheit_id = lehre.tbl_lehreinheit.lehreinheit_id
					AND lehre.tbl_lehreinheitmitarbeiter.lehrfunktion_kurzbz = 'LV-Leitung' LIMIT 1
				)
		) AS lv_leitung,
	(
		SELECT vorname
		FROM PUBLIC.tbl_person
		JOIN PUBLIC.tbl_benutzer USING (person_id)
		WHERE uid = (
				SELECT mitarbeiter_uid
				FROM lehre.tbl_lehreinheitmitarbeiter
				WHERE lehre.tbl_lehreinheitmitarbeiter.lehreinheit_id = lehre.tbl_lehreinheit.lehreinheit_id
					AND lehre.tbl_lehreinheitmitarbeiter.lehrfunktion_kurzbz = 'LV-Leitung' LIMIT 1
				)
		) AS lv_leitung_vorname,
	(
		SELECT bezeichnung
		FROM lehre.tbl_lehrform
		WHERE lehre.tbl_lehrform.lehrform_kurzbz = tbl_lehrveranstaltung.lehrform_kurzbz LIMIT 1
		) AS lv_type,
	(
			SELECT STRING_AGG(DISTINCT (person.nachname || ' ' || person.vorname), ', ')
			FROM lehre.tbl_lehrveranstaltung slv
			JOIN lehre.tbl_lehreinheit sle USING (lehrveranstaltung_id)
			JOIN lehre.tbl_lehreinheitmitarbeiter slema USING (lehreinheit_id)
			JOIN PUBLIC.tbl_benutzer benutzer ON benutzer.uid = slema.mitarbeiter_uid
			JOIN PUBLIC.tbl_person person USING (person_id)
			WHERE lehre.tbl_lehreinheit.lehrveranstaltung_id = sle.lehrveranstaltung_id
			AND tbl_lehrveranstaltung.lehrform_kurzbz = slv.lehrform_kurzbz
			AND sle.studiensemester_kurzbz = " . $db->db_add_param($previousStsem) . "
		) AS vorjahr_lektor,
	tbl_lehrveranstaltung.lehrform_kurzbz,
	tbl_lehrveranstaltung.las,
	tbl_lehrveranstaltung.alvs
FROM lehre.tbl_lehrveranstaltung
JOIN lehre.tbl_lehreinheit USING (lehrveranstaltung_id)
JOIN lehre.tbl_lehreinheitmitarbeiter USING (lehreinheit_id)
WHERE tbl_lehreinheit.studiensemester_kurzbz = ".$db->db_add_param($studiensemester_kurzbz);

if($studiengang_kz!='')
	$qry.=" AND tbl_lehrveranstaltung.studiengang_kz IN (".$studiengang_kz.")";

if($oe_kurzbz!='')
	$qry.=" AND tbl_lehrveranstaltung.oe_kurzbz=".$db->db_add_param($oe_kurzbz);

if($semester!='')
	$qry.=" AND tbl_lehrveranstaltung.semester=".$db->db_add_param($semester, FHC_INTEGER);

if($uid!='')
	$qry.=" AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid=".$db->db_add_param($uid);

if($orgform_kurzbz!='')
	$qry.=" AND tbl_lehrveranstaltung.orgform_kurzbz=".$db->db_add_param($orgform_kurzbz);

$qry.=" ORDER BY tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.semester, tbl_lehrveranstaltung.bezeichnung";

// Creating a workbook
$workbook = new Spreadsheet_Excel_Writer();

// sending HTTP headers
$workbook->send("LVPlanung.xls");
$workbook->setVersion(8);
// Creating a worksheet
$worksheet =& $workbook->addWorksheet($studiensemester_kurzbz);
$worksheet->setInputEncoding('utf-8');
//Formate Definieren
$format_bold =& $workbook->addFormat();
$format_bold->setBold();

$format_number =& $workbook->addFormat();
$format_number->setNumFormat('0,0.00');

$format_number_bold =& $workbook->addFormat();
$format_number_bold->setNumFormat('0,0.00');
$format_number_bold->setBold();


$zeile=0;
$spalte=0;
$worksheet->write($zeile,$spalte,"Studiengang", $format_bold);
$maxlength[$spalte]=11;
$worksheet->write($zeile,++$spalte,"Organisationseinheit", $format_bold);
$maxlength[$spalte]=25;
$worksheet->write($zeile,++$spalte,"LektorIn", $format_bold);
$maxlength[$spalte]=6;
$worksheet->write($zeile,++$spalte,"Fixangestellt", $format_bold);
$maxlength[$spalte]=10;
$worksheet->write($zeile,++$spalte,"Vorjahrslektor", $format_bold);
$maxlength[$spalte]=12;
$worksheet->write($zeile,++$spalte,"Lektor*in bestätigt", $format_bold);
$maxlength[$spalte]=12;
$worksheet->write($zeile,++$spalte,"Bezeichnung", $format_bold);
$maxlength[$spalte]=25;
$worksheet->write($zeile,++$spalte,"Semester", $format_bold);
$maxlength[$spalte]=8;
$worksheet->write($zeile,++$spalte,"Gruppen", $format_bold);
$maxlength[$spalte]=20;
$worksheet->write($zeile,++$spalte,"Semesterstunden", $format_bold);
$maxlength[$spalte]=12;
$worksheet->write($zeile,++$spalte,"Stundensatz", $format_bold);
$maxlength[$spalte]=12;
$worksheet->write($zeile,++$spalte,"Kosten", $format_bold);
$maxlength[$spalte]=7;
$worksheet->write($zeile,++$spalte,"Planstunden", $format_bold);
$maxlength[$spalte]=10;
$worksheet->write($zeile,++$spalte,"Stundenblockung", $format_bold);
$maxlength[$spalte]=15;
$worksheet->write($zeile,++$spalte,"Wochenrhythmus", $format_bold);
$maxlength[$spalte]=13;
$worksheet->write($zeile,++$spalte,"Raum", $format_bold);
$maxlength[$spalte]=4;
$worksheet->write($zeile,++$spalte,"Raum alternativ", $format_bold);
$maxlength[$spalte]=15;

$worksheet->write($zeile,++$spalte,"Anmerkung für LV Planung", $format_bold);
$maxlength[$spalte]=25;

$worksheet->write($zeile,++$spalte,"Anmerkung für Dpt/KF", $format_bold);
$maxlength[$spalte]=25;

$worksheet->write($zeile,++$spalte,"LV-Leitung", $format_bold);
$maxlength[$spalte]=9;

$worksheet->write($zeile,++$spalte,"LV-Nummer", $format_bold);
$maxlength[$spalte]=9;

$worksheet->write($zeile,++$spalte,"Lehrauftragsstunden", $format_bold);
$maxlength[$spalte]=15;

$worksheet->write($zeile,++$spalte,"ECTS", $format_bold);
$maxlength[$spalte]=9;

$worksheet->write($zeile,++$spalte,"LV-Typ", $format_bold);
$maxlength[$spalte]=9;

$worksheet->write($zeile,++$spalte,"Organisationsform", $format_bold);
$maxlength[$spalte]=15;

$worksheet->write($zeile,++$spalte,"Sprache (LV)", $format_bold);
$maxlength[$spalte]=10;

$worksheet->write($zeile,++$spalte,"Software", $format_bold);
$maxlength[$spalte]=10;

$worksheet->write($zeile,++$spalte,"Start in KW", $format_bold);
$maxlength[$spalte]=10;

$worksheet->write($zeile,++$spalte,"LAS der LV", $format_bold);
$maxlength[$spalte]=10;

$worksheet->write($zeile,++$spalte,"ALVS der LV", $format_bold);
$maxlength[$spalte]=10;

$worksheet->write($zeile,++$spalte,"Anzahl Studierende", $format_bold);
$maxlength[$spalte]=16;

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$spalte=0;
		$zeile++;

		$mitarbeiter = new mitarbeiter();
		$mitarbeiter->load($row->mitarbeiter_uid);

		//Studiengang
		$worksheet->write($zeile,$spalte,$stg_obj->kuerzel_arr[$row->studiengang_kz]);
		if($maxlength[$spalte]<mb_strlen($stg_obj->kuerzel_arr[$row->studiengang_kz]))
			$maxlength[$spalte]=mb_strlen($stg_obj->kuerzel_arr[$row->studiengang_kz]);

		//Organisationseinheit
		$worksheet->write($zeile,++$spalte,$oe_arr[$row->lv_oe_kurzbz]);
		//if($maxlength[$spalte]<mb_strlen($oe_arr[$row->lv_oe_kurzbz]))
			//$maxlength[$spalte]=mb_strlen($oe_arr[$row->lv_oe_kurzbz]);

		//Lektor
		$worksheet->write($zeile,++$spalte,$mitarbeiter->nachname.' '.$mitarbeiter->vorname);
		if($maxlength[$spalte]<mb_strlen($mitarbeiter->nachname.' '.$mitarbeiter->vorname))
			$maxlength[$spalte]=mb_strlen($mitarbeiter->nachname.' '.$mitarbeiter->vorname);

		//Fixangestellt
		$worksheet->write($zeile,++$spalte,($mitarbeiter->fixangestellt ? 'Ja' : 'Nein'));
		if($maxlength[$spalte]<mb_strlen($mitarbeiter->fixangestellt ? 'Ja' : 'Nein'))
			$maxlength[$spalte]=mb_strlen($mitarbeiter->fixangestellt ? 'Ja' : 'Nein');

		$worksheet->write($zeile,++$spalte, $row->vorjahr_lektor);

		$worksheet->write($zeile,++$spalte, '');

		//Lehrfach
		$worksheet->write($zeile,++$spalte,$row->lf_bezeichnung);
		//if($maxlength[$spalte]<mb_strlen($row->lf_bezeichnung))
			//$maxlength[$spalte]=mb_strlen($row->lf_bezeichnung);

		//Semester
		$worksheet->write($zeile,++$spalte,$row->lv_semester);
		if($maxlength[$spalte]<mb_strlen($row->lv_semester))
			$maxlength[$spalte]=mb_strlen($row->lv_semester);

		$qry = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id=".$db->db_add_param($row->lehreinheit_id, FHC_INTEGER);
		$result_gruppe = $db->db_query($qry);
		$gruppe = '';
		while($row_gruppe = $db->db_fetch_object($result_gruppe))
		{
			if($gruppe!='')
				$gruppe.=', ';
			if($row_gruppe->gruppe_kurzbz!='')
				$gruppe.=$row_gruppe->gruppe_kurzbz;
			else
				$gruppe.=trim($stg_obj->kuerzel_arr[$row_gruppe->studiengang_kz].'-'.$row_gruppe->semester.$row_gruppe->verband.$row_gruppe->gruppe);
		}

		//Gruppen
		$worksheet->write($zeile,++$spalte,$gruppe);
		//if($maxlength[$spalte]<mb_strlen($gruppe))
			//$maxlength[$spalte]=mb_strlen($gruppe);

		//Semesterstunden
		$worksheet->write($zeile,++$spalte,$row->lemss);
		if($maxlength[$spalte]<mb_strlen($row->lemss))
			$maxlength[$spalte]=mb_strlen($row->lemss);

		//Stundensatz
		$worksheet->write($zeile,++$spalte,$row->stundensatz);
		if($maxlength[$spalte]<mb_strlen($row->stundensatz))
			$maxlength[$spalte]=mb_strlen($row->stundensatz);

		//Kosten
		$kosten = ($row->stundensatz*$row->lemss);
		$worksheet->write($zeile,++$spalte,$kosten);
		if($maxlength[$spalte]<mb_strlen($kosten))
			$maxlength[$spalte]=mb_strlen($kosten);
		//Planstunden
		$worksheet->write($zeile,++$spalte,$row->planstunden);
		if($maxlength[$spalte]<mb_strlen($row->planstunden))
			$maxlength[$spalte]=mb_strlen($row->planstunden);

		//Stundenblockung
		$worksheet->write($zeile,++$spalte,$row->stundenblockung);
		if($maxlength[$spalte]<mb_strlen($row->stundenblockung))
			$maxlength[$spalte]=mb_strlen($row->stundenblockung);
		//Wochentrythmus
		$worksheet->write($zeile,++$spalte,$row->wochenrythmus);
		if($maxlength[$spalte]<mb_strlen($row->wochenrythmus))
			$maxlength[$spalte]=mb_strlen($row->wochenrythmus);
		//Raumtyp
		$worksheet->write($zeile,++$spalte,$row->raumtyp);
		if($maxlength[$spalte]<mb_strlen($row->raumtyp))
			$maxlength[$spalte]=mb_strlen($row->raumtyp);
		//Raumtypalternativ
		$worksheet->write($zeile,++$spalte,$row->raumtypalternativ);
		if($maxlength[$spalte]<mb_strlen($row->raumtypalternativ))
			$maxlength[$spalte]=mb_strlen($row->raumtypalternativ);

		//Anmerkung für LV Planung
		$worksheet->write($zeile,++$spalte,$row->lehreinheit_anmerkung);

		//Anmerkung für Dpt/KF
		$worksheet->write($zeile,++$spalte,$row->mitarbeiter_anmerkung);

		//LV-Leitung
		$worksheet->write($zeile,++$spalte,$row->lv_leitung.' '.$row->lv_leitung_vorname);
		if($maxlength[$spalte]<mb_strlen($row->lv_leitung.' '.$row->lv_leitung_vorname))
			$maxlength[$spalte]=mb_strlen($row->lv_leitung.' '.$row->lv_leitung_vorname);

		//LV-Nummer
		$worksheet->write($zeile,++$spalte,$row->lehrveranstaltung_id);
		if($maxlength[$spalte]<mb_strlen($row->lehrveranstaltung_id))
			$maxlength[$spalte]=mb_strlen($row->lehrveranstaltung_id);

		//Semesterstunden
		$semesterstunden = $row->semesterstunden;
		if ($row->stundensatz==0 || $row->lemss==0)
			$semesterstunden = 0;

		$worksheet->write($zeile,++$spalte,$semesterstunden);
		if($maxlength[$spalte]<mb_strlen($semesterstunden))
			$maxlength[$spalte]=mb_strlen($semesterstunden);

		//ECTS
		$worksheet->write($zeile,++$spalte,$row->ects);
		if($maxlength[$spalte]<mb_strlen($row->ects))
			$maxlength[$spalte]=mb_strlen($row->ects);

		//LV-Typ
		if (empty($row->lv_type) || $row->lehrform_kurzbz=='-' )
				$row->lv_type='keine';
		$worksheet->write($zeile,++$spalte,$row->lv_type);
		if($maxlength[$spalte]<mb_strlen($row->lv_type))
			$maxlength[$spalte]=mb_strlen($row->lv_type);

		//Organisationsform
		$worksheet->write($zeile,++$spalte,$row->orgform_kurzbz);
		if($maxlength[$spalte]<mb_strlen($row->orgform_kurzbz))
			$maxlength[$spalte]=mb_strlen($row->orgform_kurzbz);

		//Sprache (LV)
		$worksheet->write($zeile,++$spalte,$row->sprache);

		//Software
		$worksheet->write($zeile,++$spalte,'');

		//Start in KW
		$worksheet->write($zeile,++$spalte,$row->start_kw);

		//LAS der LV
		$worksheet->write($zeile,++$spalte,$row->las);

		//ALVS der LV
		$worksheet->write($zeile,++$spalte,$row->alvs);

		// Anzahl Studierende in Lehreinheit
		$anzahlStudierende = 0;
		$qry_anz_std = "SELECT count(DISTINCT uid) as anzahl FROM campus.vw_student_lehrveranstaltung WHERE lehreinheit_id=".$db->db_add_param($row->lehreinheit_id, FHC_INTEGER);

		if($result_anz_std = $db->db_query($qry_anz_std))
		{
			if ($row_anz_std = $db->db_fetch_object($result_anz_std))
			{
				$anzahlStudierende = $row_anz_std->anzahl;
			}
		}

		$worksheet->write($zeile,++$spalte,$anzahlStudierende);
	}

	//Betreuungen
	$qry = "SELECT tbl_lehrveranstaltung.studiengang_kz,
				tbl_lehrveranstaltung.oe_kurzbz AS lv_oe_kurzbz,
				nachname,
				vorname,
				tbl_lehrveranstaltung.bezeichnung,
				tbl_lehrveranstaltung.semester,
				student_uid,
				stunden,
				tbl_projektbetreuer.stundensatz,
				tbl_projektbetreuer.person_id,
				tbl_lehrveranstaltung.orgform_kurzbz
			FROM lehre.tbl_projektarbeit,
				lehre.tbl_lehreinheit,
				lehre.tbl_lehrveranstaltung,
				lehre.tbl_projektbetreuer,
				PUBLIC.tbl_person
			WHERE tbl_projektarbeit.lehreinheit_id = tbl_lehreinheit.lehreinheit_id
				AND tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id
				AND tbl_projektarbeit.projektarbeit_id = tbl_projektbetreuer.projektarbeit_id
				AND tbl_person.person_id = tbl_projektbetreuer.person_id
				AND tbl_lehreinheit.studiensemester_kurzbz = ".$db->db_add_param($studiensemester_kurzbz);

	if($uid!=='')
	{
		$mitarbeiter = new mitarbeiter($uid);
		$qry.=" AND tbl_projektbetreuer.person_id=".$db->db_add_param($mitarbeiter->person_id, FHC_INTEGER);
	}

	if($oe_kurzbz!='')
		$qry.=" AND tbl_lehrveranstaltung.oe_kurzbz=".$db->db_add_param($oe_kurzbz);

	if($studiengang_kz!='')
		$qry.=" AND tbl_lehrveranstaltung.studiengang_kz IN(".$studiengang_kz.")";

	if($semester!='')
		$qry.=" AND tbl_lehrveranstaltung.semester=".$db->db_add_param($semester, FHC_INTEGER);

	if($orgform_kurzbz!='')
		$qry.=" AND tbl_lehrveranstaltung.orgform_kurzbz=".$db->db_add_param($orgform_kurzbz);

	if($result = $db->db_query($qry))
	{
		$spalte=0;
		$zeile++;
		$zeile++;
		$worksheet->write($zeile,$spalte,"Betreuungen", $format_bold);

		while($row = $db->db_fetch_object($result))
		{
			$spalte=0;
			$zeile++;

			$benutzer = new benutzer();
			$benutzer->getBenutzerFromPerson($row->person_id, false);
			if (count($benutzer->result) > 0)
			{
				foreach ($benutzer->result AS $bn)
				{
					$mitarbeiter = new mitarbeiter($bn->uid);
					if ($mitarbeiter->load($bn->uid))
					{
						if ($mitarbeiter->fixangestellt)
						{
							$fixangestellt = 'Ja';
							break;
						}
						else
						{
							$fixangestellt = 'Nein';
							break;
						}
					}
					else
					{
						continue;
					}

				}
			}
			else
			{
				$fixangestellt = 'Extern';
			}

			//Studiengang
			$worksheet->write($zeile,$spalte,$stg_obj->kuerzel_arr[$row->studiengang_kz]);
			if($maxlength[$spalte]<mb_strlen($stg_obj->kuerzel_arr[$row->studiengang_kz]))
				$maxlength[$spalte]=mb_strlen($stg_obj->kuerzel_arr[$row->studiengang_kz]);

			//Organisationseinheit
			$worksheet->write($zeile,++$spalte,$oe_arr[$row->lv_oe_kurzbz]);
			//if($maxlength[$spalte]<mb_strlen($oe_arr[$row->lv_oe_kurzbz]))
				//$maxlength[$spalte]=mb_strlen($oe_arr[$row->lv_oe_kurzbz]);

			//Lektor
			$worksheet->write($zeile,++$spalte,$row->nachname.' '.$row->vorname);
			//if($maxlength[$spalte]<mb_strlen($row->nachname.' '.$row->vorname))
				//$maxlength[$spalte]=mb_strlen($row->nachname.' '.$row->vorname);

			//Fixangestellt
			$worksheet->write($zeile,++$spalte,$fixangestellt);

			//Lehrfach
			$worksheet->write($zeile,++$spalte,$row->bezeichnung);
			//if($maxlength[$spalte]<mb_strlen($row->bezeichnung))
				//$maxlength[$spalte]=mb_strlen($row->bezeichnung);

			//Semester
			$worksheet->write($zeile,++$spalte,$row->semester);
			if($maxlength[$spalte]<mb_strlen($row->semester))
				$maxlength[$spalte]=mb_strlen($row->semester);

			$benutzer = new benutzer();
			$benutzer->load($row->student_uid);

			//Student
			$worksheet->write($zeile,++$spalte,$benutzer->nachname.' '.$benutzer->vorname);
			//if($maxlength[$spalte]<mb_strlen($benutzer->nachname.' '.$benutzer->vorname))
				//$maxlength[$spalte]=mb_strlen($benutzer->nachname.' '.$benutzer->vorname);

			//Stunden
			$worksheet->write($zeile,++$spalte,$row->stunden);
			if($maxlength[$spalte]<mb_strlen($row->stunden))
				$maxlength[$spalte]=mb_strlen($row->stunden);
			//Stundensatz
			$worksheet->write($zeile,++$spalte,$row->stundensatz);
			if($maxlength[$spalte]<mb_strlen($row->stundensatz))
				$maxlength[$spalte]=mb_strlen($row->stundensatz);
			//Kosten
			$worksheet->write($zeile,++$spalte,$row->stunden*$row->stundensatz);
			if($maxlength[$spalte]<mb_strlen($row->stunden*$row->stundensatz))
				$maxlength[$spalte]=mb_strlen($row->stunden*$row->stundensatz);
			//Organisationsform
			$worksheet->write($zeile,++$spalte,$row->orgform_kurzbz);
			if($maxlength[$spalte]<mb_strlen($row->orgform_kurzbz))
				$maxlength[$spalte]=mb_strlen($row->orgform_kurzbz);
		}
	}

	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet->setColumn($i, $i, $breite+2);
}
 $workbook->close();
?>
