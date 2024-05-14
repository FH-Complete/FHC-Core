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
 *          Manfred Kindl		< manfred.kindl@technikum-wien.at >
 *          Cristina Hainberger         < hainberg@technikum-wien.at >
 */
/**
 * Reihungstest
 *
 * - Anlegen und Bearbeiten von Terminen
 * - Export von Anwesenheitslisten als Excel
 * - Uebertragung der Ergebnis-Punkte ins FAS
 *
 * Parameter:
 * excel ... wenn gesetzt, dann wird die Anwesenheitsliste als Excel exportiert
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/reihungstest.class.php');
require_once('../../include/ort.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/pruefling.class.php');
require_once('../../include/person.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/Excel/excel.php');
require_once('../../include/adresse.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/studienplan.class.php');
require_once('../../include/sprache.class.php');
require_once('../../include/organisationsform.class.php');
require_once('../../include/gruppe.class.php');
require_once('../../include/variable.class.php');
require_once('../../include/mail.class.php');


//	"Teilgenommen" und "Punkte" werden immer mit false bzw. 0 gespeichert

if (!$db = new basis_db())
{
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
}

//Richtiges Studiensemester zum anzeigen ermitteln
	//Mit getAktOrNext das aktuelle oder kommende WINTERsemester auslesen
	$stsem_aktorNext = new studiensemester();
	$stsem_aktorNext = $stsem_aktorNext->getaktorNext(1);
	//Ergebnis aus $stsem_aktorNext laden und den Timestamp der Semestermitte bestimmen.
	$stsem_berechnet = new studiensemester();
	$stsem_berechnet->load($stsem_aktorNext);
	$mitte = (strtotime($stsem_berechnet->ende) - strtotime($stsem_berechnet->start)) / 2;
	// Wenn die Haelfte des Wintersemesters vorbei ist, das naechste Wintersemester ermitteln, sonst das Aktuelle nehmen
	if (strtotime($stsem_berechnet->ende) - $mitte <= time())
	{
		$stsem_dropdown = new studiensemester();
		$stsem_dropdown->getNextStudiensemester('WS');
		$stsem_dropdown = $stsem_dropdown->studiensemester_kurzbz;
	}
	else
		$stsem_dropdown = $stsem_aktorNext;

$user = get_uid();
$datum_obj = new datum();
$stg_kz = (isset($_GET['stg_kz']) ? $_GET['stg_kz'] : '');
$reihungstest_id = (isset($_GET['reihungstest_id']) ? $_GET['reihungstest_id'] : '');
$studiensemester_kurzbz = (isset($_GET['studiensemester_kurzbz']) ? $_GET['studiensemester_kurzbz'] : $stsem_dropdown);
$studienplan_id = (isset($_GET['studienplan_id']) ? $_GET['studienplan_id'] : '');
$prestudent_id = (isset($_GET['prestudent_id']) ? $_GET['prestudent_id'] : '');
$rtpunkte = (isset($_GET['rtpunkte']) ? $_GET['rtpunkte'] : '');
$neu = (isset($_GET['neu']) ? true : false);
$stg_arr = array();
$error = false;

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if ($stg_kz == '' && ($reihungstest_id != '' || isset($_POST['reihungstest_id'])))
{
	if ($reihungstest_id != '')
	{
		$rt = new Reihungstest();
		$rt->load($reihungstest_id);
		$stg_kz = $rt->studiengang_kz;
	}
	elseif (isset($_POST['reihungstest_id']))
	{
		$rt = new Reihungstest();
		$rt->load($_POST['reihungstest_id']);
		$stg_kz = $rt->studiengang_kz;
	}
	else
		$stg_kz = '-1';
}

if(!$rechte->isBerechtigt('lehre/reihungstest'))
{
	die($rechte->errormsg);
}

$studiengang = new studiengang();
$studiengang->getAll('typ, kurzbz', false);
$types = new studiengang();
$types->getAllTypes();
$typ = '';

$studiensemester = new Studiensemester();
$studiensemester->getAll('desc');

$sprachen_obj = new sprache();
$sprachen_obj->getAll();
$sprachen_arr=array();

foreach($sprachen_obj->result as $row)
{
	if(isset($row->bezeichnung_arr[$sprache]))
		$sprachen_arr[$row->sprache]=$row->bezeichnung_arr[$sprache];
	else
		$sprachen_arr[$row->sprache]=$row->sprache;
}

$orgform_obj = new organisationsform();
$orgform_obj->getAll();
$orgform_arr=array();
foreach($orgform_obj->result as $row)
	$orgform_arr[$row->orgform_kurzbz]=$row->bezeichnung;

// Pruefen ob Variable fuer Punkteberechnung gesetzt ist, wenn nicht, einen neuen Eintrag anlegen
$variable = new variable();
if ($variable->load($user, 'reihungstestverwaltung_punkteberechnung'))
{
	if (isset($_GET['punkteberechnung']) && $_GET['punkteberechnung'] != $variable->wert)
	{
		$variable->new = false;
		$variable->uid = $user;
		$variable->name = 'reihungstestverwaltung_punkteberechnung';
		$variable->wert = $_GET['punkteberechnung'];
		$variable->save();

		$punkteberechnung = $_GET['punkteberechnung'];
	}
	else
		$punkteberechnung = $variable->wert;

}
else
{
	$variable->new = true;
	$variable->uid = $user;
	$variable->name = 'reihungstestverwaltung_punkteberechnung';
	$variable->wert = 'true';
	$variable->save();

	$punkteberechnung = 'true';
}

//Studierende als Excel Exportieren
if(isset($_GET['excel']))
{
	$reihungstest = new reihungstest();
	if($reihungstest->load($_GET['reihungstest_id']))
	{
		$rt_studienplan_id = '';
		$studienplaene_arr = array();
		$studienplaene = new reihungstest();
		$studienplaene->getStudienplaeneReihungstest($reihungstest->reihungstest_id);
		foreach ($studienplaene->result AS $row)
		{
			$studienplan = new studienplan();
			if($studienplan->loadStudienplan($row->studienplan_id))
			{
				$studienplaene_arr[ $row->studienplan_id] = $studienplan->bezeichnung;
				$rt_studienplan_id = $row->studienplan_id;
			}
			else
			{
				die('Fehler beim Laden:'.$studienplan->errormsg);
			}
		}

		$studienplaene_list = implode(',', array_keys($studienplaene_arr));
		$qry = "
		SELECT DISTINCT rt_person_id,
			rt_id,
			'0' AS prestudent_id,
			tbl_rt_person.person_id,
			vorname,
			nachname,
			tbl_rt_person.ort_kurzbz,
			tbl_rt_person.studienplan_id,
			tbl_prestudent.studiengang_kz,
			gebdatum,
			geschlecht,
			punkte,
			(
				SELECT kontakt
				FROM tbl_kontakt
				WHERE kontakttyp = 'email'
					AND person_id = tbl_rt_person.person_id
					AND zustellung = true LIMIT 1
				) AS email,
			(
				SELECT ausbildungssemester
				FROM PUBLIC.tbl_prestudentstatus
				WHERE prestudent_id = tbl_prestudent.prestudent_id
					AND datum = (
						SELECT MAX(datum)
						FROM PUBLIC.tbl_prestudentstatus
						WHERE prestudent_id = tbl_prestudent.prestudent_id
							AND status_kurzbz = 'Interessent'
						) LIMIT 1
				) AS ausbildungssemester,
			(
				SELECT orgform_kurzbz
				FROM PUBLIC.tbl_prestudentstatus
				WHERE prestudent_id = tbl_prestudent.prestudent_id
					AND datum = (
						SELECT MAX(datum)
						FROM PUBLIC.tbl_prestudentstatus
						WHERE prestudent_id = tbl_prestudent.prestudent_id
							AND status_kurzbz = 'Interessent'
						) LIMIT 1
				) AS orgform_kurzbz
		FROM PUBLIC.tbl_rt_person
		JOIN PUBLIC.tbl_person USING (person_id)
		JOIN PUBLIC.tbl_reihungstest rt ON (rt_id = rt.reihungstest_id)
		JOIN PUBLIC.tbl_prestudent ON (tbl_rt_person.person_id = tbl_prestudent.person_id)
		JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
		WHERE rt_id = ".$db->db_add_param($reihungstest_id, FHC_INTEGER)."
			AND tbl_rt_person.studienplan_id IN (
				SELECT studienplan_id
				FROM PUBLIC.tbl_prestudentstatus
				WHERE prestudent_id = tbl_prestudent.prestudent_id
				)
			AND tbl_prestudentstatus.studiensemester_kurzbz IN (
				SELECT studiensemester_kurzbz
				FROM PUBLIC.tbl_studiensemester
				WHERE studiensemester_kurzbz = rt.studiensemester_kurzbz
				
				UNION
				
				(
					SELECT studiensemester_kurzbz
					FROM PUBLIC.tbl_studiensemester
					WHERE ende <= (
							SELECT start
							FROM PUBLIC.tbl_studiensemester
							WHERE studiensemester_kurzbz = rt.studiensemester_kurzbz
							)
					ORDER BY ende DESC LIMIT 1
					)
				
				UNION
				
				(
					SELECT studiensemester_kurzbz
					FROM PUBLIC.tbl_studiensemester
					WHERE start >= (
							SELECT ende
							FROM PUBLIC.tbl_studiensemester
							WHERE studiensemester_kurzbz = rt.studiensemester_kurzbz
							)
					ORDER BY start ASC LIMIT 1
					)
				)
		ORDER BY ort_kurzbz NULLS FIRST,
			nachname,
			vorname
			";

		$gebietbezeichnungen = array();
		if ($rt_studienplan_id != '')
		{
			$qry_gebiete = "SELECT gebiet_id, reihung, bezeichnung FROM testtool.tbl_ablauf JOIN testtool.tbl_gebiet USING (gebiet_id) WHERE studienplan_id = ".$db->db_add_param($rt_studienplan_id)." ORDER BY reihung";
			if($result_gebiete = $db->db_query($qry_gebiete))
			{
				while($row_gebiete = $db->db_fetch_object($result_gebiete))
				{
					$gebietbezeichnungen[$row_gebiete->gebiet_id] = $row_gebiete->bezeichnung;
				}
			}
		}

		// Creating a workbook
		$workbook = new Spreadsheet_Excel_Writer();
		$workbook->setVersion(8);
		// sending HTTP headers
		$workbook->send("Anwesenheitsliste_Aufnahmetermin_".$reihungstest->datum.".xls");

		//Formate Definieren
		$format_bold =& $workbook->addFormat();
		$format_bold->setBold();

		$format_border =& $workbook->addFormat();
		$format_border->setBorder(1);
		$format_border->setTextWrap();
		$format_border->setVAlign ('top');

		$format_border_center =& $workbook->addFormat();
		$format_border_center->setBorder(1);
		$format_border_center->setTextWrap();
		$format_border_center->setVAlign ('top');
		$format_border_center->setHAlign ('center');

		$format_border_left =& $workbook->addFormat();
		$format_border_left->setBorder(1);
		$format_border_left->setTextWrap();
		$format_border_left->setVAlign ('top');
		$format_border_left->setHAlign ('left');

		if($result = $db->db_query($qry))
		{
			$ort_kurzbz = '0';
			// Wenn Daten vorhanden
			if ($db->db_num_rows($result) > 0)
			{
				// Gesamtsheet mit allen TeilnehmerInnen erstellen, danach getrennt nach Raum
				$worksheet =& $workbook->addWorksheet("Gesamtliste");
				$worksheet->setInputEncoding('utf-8');
				//$worksheet->setZoom (85);
				$worksheet->hideScreenGridlines();
				$worksheet->hideGridlines();
				$worksheet->setLandscape();
				$worksheet->centerHorizontally(1);
				$worksheet->fitToPages ( 1, 1);
				$worksheet->setMargins_LR (0.4);
				$worksheet->setMarginTop (0.79);
				$worksheet->setMarginBottom (0.59);

				// Titelzeilen
				$worksheet->write(0,0,'Anwesenheitsliste Aufnahmetermin vom '.$datum_obj->convertISODate($reihungstest->datum).' '.$reihungstest->uhrzeit.' Uhr, '.$reihungstest->anmerkung.', erstellt am '.date('d.m.Y'), $format_bold);
				$worksheet->write(2,0,'Studienpläne: '.implode(', ', $studienplaene_arr));
				$worksheet->write(3,0,'Stufe: '.$reihungstest->stufe);
				$worksheet->write(4,0,'Testmodule: '.implode(', ', $gebietbezeichnungen));

				//Ueberschriften
				$zeile=6;
				$col=0;
				$worksheet->write($zeile,$col,"Nachname", $format_bold);
				$maxlength[$col] = 8;
				$worksheet->write($zeile,++$col,"Vorname", $format_bold);
				$maxlength[$col] = 7;
				$worksheet->write($zeile,++$col,"G", $format_bold);
				$maxlength[$col] = 2;
				$worksheet->write($zeile,++$col,"Geburtsdatum", $format_bold);
				$maxlength[$col] = 12;
				$worksheet->write($zeile,++$col,"Studiengang", $format_bold);
				$maxlength[$col] = 11;
				$worksheet->write($zeile,++$col,"OrgForm", $format_bold);
				$maxlength[$col] = 7;
				$worksheet->write($zeile,++$col,"S", $format_bold);
				$maxlength[$col] = 2;
				$worksheet->write($zeile,++$col,"Raumzuteilung", $format_bold);
				$maxlength[$col] = 12;
				$worksheet->write($zeile,++$col,"Bereits absolvierte RTs", $format_bold);
				$maxlength[$col] = 20;
				$worksheet->write($zeile,++$col,"Sonstige Termine", $format_bold);
				$maxlength[$col] = 20;
				$worksheet->write($zeile,++$col,"EMail", $format_bold);
				$maxlength[$col] = 5;
				$worksheet->write($zeile,++$col,"Strasse", $format_bold);
				$maxlength[$col] = 6;
				$worksheet->write($zeile,++$col,"PLZ", $format_bold);
				$maxlength[$col] = 3;
				$worksheet->write($zeile,++$col,"Ort", $format_bold);
				$maxlength[$col] = 3;
				$worksheet->write($zeile,++$col,"Unterschrift", $format_bold);
				$maxlength[$col] = 30;

				$zeile++;

				while($row = $db->db_fetch_object($result))
				{
					$pruefling = new pruefling();
					$rt_in_anderen_stg='';
					$erg = '';
					$rt_prestudent_arr = array();

					//Daten ermitteln für Spalte absolvierte Verfahren
					$qry_absolvierte_Verfahren = "SELECT
						distinct tbl_reihungstest.reihungstest_id,
						tbl_pruefling.pruefling_id,
						tbl_prestudent.prestudent_id,
						tbl_rt_person.person_id
						FROM
						public.tbl_rt_person
						JOIN lehre.tbl_studienplan USING(studienplan_id)
						JOIN lehre.tbl_studienordnung USING(studienordnung_id)
						JOIN public.tbl_prestudent USING(person_id)
						JOIN public.tbl_prestudentstatus USING(studienplan_id, prestudent_id)
						JOIN public.tbl_reihungstest ON(tbl_reihungstest.reihungstest_id=tbl_rt_person.rt_id)
						LEFT JOIN testtool.tbl_pruefling using(prestudent_id) WHERE
						(tbl_rt_person.anmeldedatum is null OR tbl_rt_person.anmeldedatum<=tbl_reihungstest.datum)
						AND tbl_reihungstest.datum >=(SELECT min(begintime)::date FROM testtool.tbl_pruefling_frage WHERE pruefling_id=tbl_pruefling.pruefling_id AND tbl_reihungstest.datum>=begintime-'1 days'::interval)									AND (tbl_reihungstest.stufe is null or tbl_reihungstest.stufe=1)
						AND person_id=".$db->db_add_param($row->person_id, FHC_INTEGER);

					if($result_rt_prestudent = $db->db_query($qry_absolvierte_Verfahren))
					{
						while($obj = $db->db_fetch_object($result_rt_prestudent))
						{
							array_push($rt_prestudent_arr, $obj);
						}
					}

					foreach($rt_prestudent_arr as $item)
					{
						$pruefling->getPruefling($item->prestudent_id);
						$rt = new Reihungstest();
						$rt->load($item->reihungstest_id);
						$rt_letztes_login = $datum_obj->formatDatum($pruefling->registriert, 'Y-m-d');
						$rt_antrittstermin = $datum_obj->formatDatum($rt->datum, 'Y-m-d');

						if($item->prestudent_id!=$row->prestudent_id || $rt_letztes_login < $rt_antrittstermin)
						{
							if(defined('FAS_REIHUNGSTEST_PUNKTE') && FAS_REIHUNGSTEST_PUNKTE)
								$erg = $pruefling->getReihungstestErgebnisPrestudent($item->prestudent_id, true, $item->reihungstest_id);
							else
								$erg = $pruefling->getReihungstestErgebnisPrestudent($item->prestudent_id, false, $item->reihungstest_id);

							if($erg!==false)
							{
								$rt_in_anderen_stg.=number_format($erg,2).((FAS_REIHUNGSTEST_PUNKTE) ? ' Punkte' : ' %').' im Studiengang '.$studiengang->kuerzel_arr[$pruefling->studiengang_kz]."\n";

								if ($item->prestudent_id == $row->prestudent_id && $rt_letztes_login < $rt_antrittstermin)
								{
									$rt_in_anderen_stg .= '(Letzter '.$studiengang->kuerzel_arr[$pruefling->studiengang_kz].'-Antritt: '.$datum_obj->formatDatum($rt_letztes_login, 'd.m.Y').'), ';
								}
							}
						}
					}

					$weitere_zuteilungen = array();
					$qry_zuteilungen = "
							SELECT
									DISTINCT tbl_studienplan.bezeichnung, tbl_reihungstest.datum, tbl_rt_person.studienplan_id
							FROM
									public.tbl_rt_person JOIN public.tbl_reihungstest ON (rt_id = reihungstest_id)
									JOIN lehre.tbl_studienplan USING (studienplan_id)
									JOIN testtool.tbl_ablauf USING (studienplan_id)
							WHERE
									person_id=".$db->db_add_param($row->person_id)."
									AND studiensemester_kurzbz=".$db->db_add_param($reihungstest->studiensemester_kurzbz)."
							ORDER BY bezeichnung";

					if($result_zuteilungen = $db->db_query($qry_zuteilungen))
					{
						while($row_zuteilungen = $db->db_fetch_object($result_zuteilungen))
						{
							$testmodule = array();
							$qry_gebiete = "SELECT gebiet_id, bezeichnung, reihung FROM testtool.tbl_ablauf JOIN testtool.tbl_gebiet USING (gebiet_id) WHERE studienplan_id = ".$db->db_add_param($row_zuteilungen->studienplan_id)." ORDER BY reihung";
							if($result_gebiete = $db->db_query($qry_gebiete))
							{
								while($row_gebiete = $db->db_fetch_object($result_gebiete))
								{
									$testmodule[$row_gebiete->gebiet_id] = $row_gebiete->bezeichnung;
								}
							}
							$weitere_zuteilungen[] = $row_zuteilungen->bezeichnung.' am '.$datum_obj->formatDatum($row_zuteilungen->datum, 'd.m.Y').' ('.implode(', ', $testmodule).')';
						}
					}

					$col=0;

					$worksheet->write($zeile,$col,$row->nachname, $format_border);
					if(strlen($row->nachname)>$maxlength[$col])
						$maxlength[$col] = strlen($row->nachname);

					$worksheet->write($zeile,++$col, $row->vorname, $format_border);
					if(strlen($row->vorname)>$maxlength[$col])
						$maxlength[$col] = strlen($row->vorname);

					$worksheet->write($zeile,++$col, $row->geschlecht, $format_border_center);
					if(strlen($row->geschlecht)>$maxlength[$col])
						$maxlength[$col] = strlen($row->geschlecht);

					$worksheet->write($zeile,++$col,$datum_obj->convertISODate($row->gebdatum), $format_border);
					if(strlen($row->gebdatum)>$maxlength[$col])
						$maxlength[$col] = strlen($row->gebdatum);

					$worksheet->write($zeile,++$col,$studiengang->kuerzel_arr[$row->studiengang_kz], $format_border);
					if(strlen($studiengang->kuerzel_arr[$row->studiengang_kz])>$maxlength[$col])
						$maxlength[$col] = strlen($studiengang->kuerzel_arr[$row->studiengang_kz]);

					$worksheet->write($zeile,++$col,$row->orgform_kurzbz, $format_border);
					if(strlen($row->orgform_kurzbz)>$maxlength[$col])
						$maxlength[$col] = strlen($row->orgform_kurzbz);

					$worksheet->write($zeile,++$col,$row->ausbildungssemester, $format_border_center);
					if(strlen($row->ausbildungssemester)>$maxlength[$col])
						$maxlength[$col] = strlen($row->ausbildungssemester);

					$worksheet->write($zeile,++$col,($row->ort_kurzbz != '' ? $row->ort_kurzbz : 'Ohne'), $format_border);
					if(strlen($row->ort_kurzbz)>$maxlength[$col])
						$maxlength[$col] = strlen($row->ort_kurzbz);

					$worksheet->write($zeile,++$col,$rt_in_anderen_stg, $format_border);
					if(strlen($rt_in_anderen_stg)>$maxlength[$col])
						$maxlength[$col] = strlen($rt_in_anderen_stg);

					$worksheet->write($zeile,++$col,implode("\n", $weitere_zuteilungen), $format_border);
					foreach ($weitere_zuteilungen as $items)
					{
						if (strlen($items)>$maxlength[$col])
							$maxlength[$col] = strlen($items);
					}

					$worksheet->write($zeile,++$col,$row->email, $format_border);
					if(strlen($row->email)>$maxlength[$col])
						$maxlength[$col] = strlen($row->email);

					$adresse = new adresse();
					$adresse->loadZustellAdresse($row->person_id);

					$worksheet->write($zeile,++$col,$adresse->strasse, $format_border);
					if(strlen($adresse->strasse)>$maxlength[$col])
						$maxlength[$col] = strlen($adresse->strasse);

					$worksheet->write($zeile,++$col,$adresse->plz, $format_border_left);
					if(strlen($adresse->plz)>$maxlength[$col])
						$maxlength[$col] = strlen($adresse->plz);

					$worksheet->write($zeile,++$col,$adresse->ort, $format_border);
					if(strlen($adresse->ort)>$maxlength[$col])
						$maxlength[$col] = strlen($adresse->ort);

					$worksheet->write($zeile,++$col,'', $format_border);

					if(count($weitere_zuteilungen)>2)
						$worksheet->setRow($zeile, count($weitere_zuteilungen)*14);
					else
						$worksheet->setRow($zeile, 35);

					$zeile++;

					//Die Breite der Spalten setzen
					foreach($maxlength as $col=>$breite)
						$worksheet->setColumn($col, $col, $breite+2);
				}
				$i = 0;
				while($i < $db->db_num_rows($result ) && $rowRaeume = $db->db_fetch_object($result, $i))
				{
					$i++;
					if ($ort_kurzbz == '0' || $ort_kurzbz != $rowRaeume->ort_kurzbz)
					{
						// Creating a worksheet
						if ($rowRaeume->ort_kurzbz=='')
								$worksheet =& $workbook->addWorksheet("Ohne Raumzuteilung");
						else
								$worksheet =& $workbook->addWorksheet("Raum ".$rowRaeume->ort_kurzbz);
						$worksheet->setInputEncoding('utf-8');
						//$worksheet->setZoom (85);
						$worksheet->hideScreenGridlines();
						$worksheet->hideGridlines();
						$worksheet->setLandscape();
						$worksheet->centerHorizontally(1);
						$worksheet->fitToPages ( 1, 1);
						$worksheet->setMargins_LR (0.4);
						$worksheet->setMarginTop (0.79);
						$worksheet->setMarginBottom (0.59);

						// Titelzeilen
						$worksheet->write(0,0,'Anwesenheitsliste Aufnahmetermin vom '.$datum_obj->convertISODate($reihungstest->datum).' '.$reihungstest->uhrzeit.' Uhr, '.$reihungstest->anmerkung.', erstellt am '.date('d.m.Y'), $format_bold);
						if ($rowRaeume->ort_kurzbz=='')
								$worksheet->write(1,0,'Ohne Raumzuteilung', $format_bold);
						else
								$worksheet->write(1,0,'Raum '.$rowRaeume->ort_kurzbz, $format_bold);
						$worksheet->write(2,0,'Studienpläne: '.implode(', ', $studienplaene_arr));
						$worksheet->write(3,0,'Stufe: '.$reihungstest->stufe);
						$worksheet->write(4,0,'Testmodule: '.implode(', ', $gebietbezeichnungen));

						//Ueberschriften
						$zeile=6;
						$col=0;
						$worksheet->write($zeile,$col,"Nachname", $format_bold);
						$maxlength[$col] = 8;
						$worksheet->write($zeile,++$col,"Vorname", $format_bold);
						$maxlength[$col] = 7;
						$worksheet->write($zeile,++$col,"G", $format_bold);
						$maxlength[$col] = 2;
						$worksheet->write($zeile,++$col,"Geburtsdatum", $format_bold);
						$maxlength[$col] = 12;
						$worksheet->write($zeile,++$col,"Studiengang", $format_bold);
						$maxlength[$col] = 11;
						$worksheet->write($zeile,++$col,"OrgForm", $format_bold);
						$maxlength[$col] = 7;
						$worksheet->write($zeile,++$col,"S", $format_bold);
						$maxlength[$col] = 2;
						$worksheet->write($zeile,++$col,"Bereits absolvierte RTs", $format_bold);
						$maxlength[$col] = 20;
						$worksheet->write($zeile,++$col,"Sonstige Termine", $format_bold);
						$maxlength[$col] = 20;
						$worksheet->write($zeile,++$col,"EMail", $format_bold);
						$maxlength[$col] = 5;
						$worksheet->write($zeile,++$col,"Strasse", $format_bold);
						$maxlength[$col] = 6;
						$worksheet->write($zeile,++$col,"PLZ", $format_bold);
						$maxlength[$col] = 3;
						$worksheet->write($zeile,++$col,"Ort", $format_bold);
						$maxlength[$col] = 3;
						$worksheet->write($zeile,++$col,"Unterschrift", $format_bold);
						$maxlength[$col] = 30;

						$ort_kurzbz = $rowRaeume->ort_kurzbz;
						$zeile++;
					}

					$pruefling = new pruefling();
					$rt_in_anderen_stg='';
					$erg = '';
					$rt_prestudent_arr = array();

					//Daten ermitteln für Spalte absolvierte Verfahren
					$qry_absolvierte_Verfahren = "SELECT
						distinct tbl_reihungstest.reihungstest_id,
						tbl_pruefling.pruefling_id,
						tbl_prestudent.prestudent_id,
						tbl_rt_person.person_id
						FROM
						public.tbl_rt_person
						JOIN lehre.tbl_studienplan USING(studienplan_id)
						JOIN lehre.tbl_studienordnung USING(studienordnung_id)
						JOIN public.tbl_prestudent USING(person_id)
						JOIN public.tbl_prestudentstatus USING(studienplan_id, prestudent_id)
						JOIN public.tbl_reihungstest ON(tbl_reihungstest.reihungstest_id=tbl_rt_person.rt_id)
						LEFT JOIN testtool.tbl_pruefling using(prestudent_id) WHERE
						(tbl_rt_person.anmeldedatum is null OR tbl_rt_person.anmeldedatum<=tbl_reihungstest.datum)
						AND tbl_reihungstest.datum >=(SELECT min(begintime)::date FROM testtool.tbl_pruefling_frage WHERE pruefling_id=tbl_pruefling.pruefling_id AND tbl_reihungstest.datum>=begintime-'1 days'::interval)									AND (tbl_reihungstest.stufe is null or tbl_reihungstest.stufe=1)
						AND person_id=".$db->db_add_param($rowRaeume->person_id, FHC_INTEGER);

					if($result_rt_prestudent = $db->db_query($qry_absolvierte_Verfahren))
					{
						while($obj = $db->db_fetch_object($result_rt_prestudent))
						{
							array_push($rt_prestudent_arr, $obj);
						}
					}

					foreach($rt_prestudent_arr as $item)
					{
						$pruefling->getPruefling($item->prestudent_id);
						$rt = new Reihungstest();
						$rt->load($item->reihungstest_id);
						$rt_letztes_login = $datum_obj->formatDatum($pruefling->registriert, 'Y-m-d');
						$rt_antrittstermin = $datum_obj->formatDatum($rt->datum, 'Y-m-d');

							if($item->prestudent_id!=$rowRaeume->prestudent_id || $rt_letztes_login < $rt_antrittstermin)
							{
								if(defined('FAS_REIHUNGSTEST_PUNKTE') && FAS_REIHUNGSTEST_PUNKTE)
										$erg = $pruefling->getReihungstestErgebnisPrestudent($item->prestudent_id, true, $item->reihungstest_id);
								else
										$erg = $pruefling->getReihungstestErgebnisPrestudent($item->prestudent_id, false, $item->reihungstest_id);

								if($erg!==false)
								{
									$rt_in_anderen_stg.=number_format($erg,2).((FAS_REIHUNGSTEST_PUNKTE) ? ' Punkte' : ' %').' im Studiengang '.$studiengang->kuerzel_arr[$pruefling->studiengang_kz]."\n";

									 if ($item->prestudent_id == $rowRaeume->prestudent_id && $rt_letztes_login < $rt_antrittstermin)
										{
											$rt_in_anderen_stg .= '(Letzter '.$studiengang->kuerzel_arr[$pruefling->studiengang_kz].'-Antritt: '.$datum_obj->formatDatum($rt_letztes_login, 'd.m.Y').'), ';
										}
								}
							}
					}

					$weitere_zuteilungen = array();
					$qry_zuteilungen = "
							SELECT
									DISTINCT tbl_studienplan.bezeichnung, tbl_reihungstest.datum, tbl_rt_person.studienplan_id
							FROM
									public.tbl_rt_person JOIN public.tbl_reihungstest ON (rt_id = reihungstest_id)
									JOIN lehre.tbl_studienplan USING (studienplan_id)
									JOIN testtool.tbl_ablauf USING (studienplan_id)
							WHERE
									person_id=".$db->db_add_param($rowRaeume->person_id)."
									AND studiensemester_kurzbz=".$db->db_add_param($reihungstest->studiensemester_kurzbz)."
							ORDER BY bezeichnung";

					if($result_zuteilungen = $db->db_query($qry_zuteilungen))
					{
						while($row_zuteilungen = $db->db_fetch_object($result_zuteilungen))
						{
								$testmodule = array();
								$qry_gebiete = "SELECT gebiet_id, bezeichnung, reihung FROM testtool.tbl_ablauf JOIN testtool.tbl_gebiet USING (gebiet_id) WHERE studienplan_id = ".$db->db_add_param($row_zuteilungen->studienplan_id)." ORDER BY reihung";
								if($result_gebiete = $db->db_query($qry_gebiete))
								{
										while($row_gebiete = $db->db_fetch_object($result_gebiete))
										{
												$testmodule[$row_gebiete->gebiet_id] = $row_gebiete->bezeichnung;
										}
								}
								$weitere_zuteilungen[] = $row_zuteilungen->bezeichnung.' am '.$datum_obj->formatDatum($row_zuteilungen->datum, 'd.m.Y').' ('.implode(', ', $testmodule).')';
						}
					}

					$col=0;

					$worksheet->write($zeile,$col,$rowRaeume->nachname, $format_border);
					if(strlen($rowRaeume->nachname)>$maxlength[$col])
							$maxlength[$col] = strlen($rowRaeume->nachname);

					$worksheet->write($zeile,++$col, $rowRaeume->vorname, $format_border);
					if(strlen($rowRaeume->vorname)>$maxlength[$col])
							$maxlength[$col] = strlen($rowRaeume->vorname);

					$worksheet->write($zeile,++$col, $rowRaeume->geschlecht, $format_border_center);
					if(strlen($rowRaeume->geschlecht)>$maxlength[$col])
							$maxlength[$col] = strlen($rowRaeume->geschlecht);

					$worksheet->write($zeile,++$col,$datum_obj->convertISODate($rowRaeume->gebdatum), $format_border);
					if(strlen($rowRaeume->gebdatum)>$maxlength[$col])
							$maxlength[$col] = strlen($rowRaeume->gebdatum);

					$worksheet->write($zeile,++$col,$studiengang->kuerzel_arr[$rowRaeume->studiengang_kz], $format_border);
					if(strlen($studiengang->kuerzel_arr[$rowRaeume->studiengang_kz])>$maxlength[$col])
							$maxlength[$col] = strlen($studiengang->kuerzel_arr[$rowRaeume->studiengang_kz]);

					$worksheet->write($zeile,++$col,$rowRaeume->orgform_kurzbz, $format_border);
					if(strlen($rowRaeume->orgform_kurzbz)>$maxlength[$col])
							$maxlength[$col] = strlen($rowRaeume->orgform_kurzbz);

					$worksheet->write($zeile,++$col,$rowRaeume->ausbildungssemester, $format_border_center);
					if(strlen($rowRaeume->ausbildungssemester)>$maxlength[$col])
							$maxlength[$col] = strlen($rowRaeume->ausbildungssemester);

					$worksheet->write($zeile,++$col,$rt_in_anderen_stg, $format_border);
					if(strlen($rt_in_anderen_stg)>$maxlength[$col])
							$maxlength[$col] = strlen($rt_in_anderen_stg);

					$worksheet->write($zeile,++$col,implode("\n", $weitere_zuteilungen), $format_border);
					foreach ($weitere_zuteilungen as $items)
					{
							if (strlen($items)>$maxlength[$col])
									$maxlength[$col] = strlen($items);
					}

					$worksheet->write($zeile,++$col,$rowRaeume->email, $format_border);
					if(strlen($rowRaeume->email)>$maxlength[$col])
							$maxlength[$col] = strlen($rowRaeume->email);

					$adresse = new adresse();
					$adresse->loadZustellAdresse($rowRaeume->person_id);

					$worksheet->write($zeile,++$col,$adresse->strasse, $format_border);
					if(strlen($adresse->strasse)>$maxlength[$col])
							$maxlength[$col] = strlen($adresse->strasse);

					$worksheet->write($zeile,++$col,$adresse->plz, $format_border_left);
					if(strlen($adresse->plz)>$maxlength[$col])
							$maxlength[$col] = strlen($adresse->plz);

					$worksheet->write($zeile,++$col,$adresse->ort, $format_border);
					if(strlen($adresse->ort)>$maxlength[$col])
							$maxlength[$col] = strlen($adresse->ort);

					$worksheet->write($zeile,++$col,'', $format_border);

					if(count($weitere_zuteilungen)>2)
							$worksheet->setRow($zeile, count($weitere_zuteilungen)*14);
					else
							$worksheet->setRow($zeile, 35);

					$zeile++;

					//Die Breite der Spalten setzen
					foreach($maxlength as $col=>$breite)
							$worksheet->setColumn($col, $col, $breite+2);
				}
			}
			else
			{
				// Creating a worksheet
				$worksheet =& $workbook->addWorksheet("Keine Daten");
				$worksheet->setInputEncoding('utf-8');
				$worksheet->hideScreenGridlines();
				$worksheet->hideGridlines();
				$worksheet->setLandscape();
				$worksheet->centerHorizontally(1);
				$worksheet->fitToPages ( 1, 1);
				$worksheet->setMargins_LR (0.4);
				$worksheet->setMarginTop (0.79);
				$worksheet->setMarginBottom (0.59);

				// Titelzeilen
				$worksheet->write(0,0,'Anwesenheitsliste Aufnahmetermin vom '.$datum_obj->convertISODate($reihungstest->datum).' '.$reihungstest->uhrzeit.' Uhr, '.$reihungstest->anmerkung.', erstellt am '.date('d.m.Y'), $format_bold);

				$worksheet->write(3,0,'Keine BewerberInnen zugeteilt', $format_bold);
			}
		}
		$workbook->close();
	}
	else
	{
		echo 'Reihungstest wurde nicht gefunden!';
	}
	return;
} ?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Reihungstest</title>
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		
		<?php 
			include('../../include/meta/jquery.php');
			include('../../include/meta/jquery-tablesorter.php');
		?>

		<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
		<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>

		<link href="../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
		<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
 		<script src="../../vendor/rmariuzzo/jquery-checkboxes/dist/jquery.checkboxes-1.0.7.min.js" type="text/javascript"></script>

		<link href="../../vendor/fgelinas/timepicker/jquery.ui.timepicker.css" rel="stylesheet" type="text/css"/>
		<script src="../../vendor/fgelinas/timepicker/jquery.ui.timepicker.js" type="text/javascript" ></script>

		<script type="text/javascript">
			$(document).ready(function()
			{
				// Check, ob Räume zugeteilt sind oder max_teilnehmer gesetzt ist, wenn "öffentlich" gesetzt wird
				$('#rt_form').submit(function()
				{
					if ($('#oeffentlich').is(":checked"))
					{
						if ($('.ort_listitem').length == 0 && $('#max_teilnehmer').val() == '' && $('#ort').val() == '')
							confirm('Wenn der Reihungstest "Öffentlich" ist, sollten Räume zugeteilt sein, oder "Max TeilnehmerInnen" gesetzt sein');
					}

					if ($('#zugangs_ueberpruefung').is(":checked") && $('#zugangcode').val() == '')
					{
						alert('Wenn die Zugangsüberprüfung aktiviert ist, ist ein Zugangscode verpflichtend.');
						return false;
					}
				});

				if ($('#oeffentlich').is(":checked") && $('.ort_listitem').length == 0 && $('#max_teilnehmer').val() == '' && $('#ort').val() == '')
				{
					$('#messageDiv').html('Wenn der Reihungstest "Öffentlich" ist, sollten Räume zugeteilt sein, oder "Max TeilnehmerInnen" gesetzt sein');
				}
				else
				{
					$('#messageDiv').html();
				}

				// Set Anmeldefrist 14 Tage vor Testdatum, wenn Testdatum geändert wird
				$('#rt_datum').change(function()
				{
					var testdatum = $(this).val();
					tag = testdatum.substring(0,2);
					monat = testdatum.substring(3,5);
					jahr = testdatum.substring(6,10);
					var olddate = monat+'/'+tag+'/'+jahr;

					var date = new Date(olddate);
					var newdate = new Date(date);

					newdate.setDate(newdate.getDate()-14); // 14 Tage abziehen

					var anmeldefrist = new Date(newdate);

					var newTag = anmeldefrist.getDate();
					var newMonat = anmeldefrist.getMonth()+1; // JavaScript months are 0-11
					var newJahr = anmeldefrist.getFullYear();

					$('#rt_anmeldefrist').val(newTag + "." + newMonat + "." + newJahr);
					$('#anmeldefristInfoDiv').html('Die Anmeldefrist wurde automatisch 14 Tage vor Testdatum gesetzt');
				});

				$(".datepicker_datum").datepicker($.datepicker.regional['de']);

				$( ".timepicker" ).timepicker({
					showPeriodLabels: false,
					hourText: "Stunde",
					minuteText: "Minute",
					hours: {starts: 7,ends: 22},
					rows: 4,
				});

				// Correct width to avoid jump on hover
				$.extend($.ui.autocomplete.prototype.options, {
					open: function(event, ui) {
						$(this).autocomplete("widget").css({
							"width": ($(".ui-menu-item").width()+ 20 + "px"),
							"padding-left": "5px"
							});
						}
				});

				$("#ort").autocomplete({
					source: "reihungstestverwaltung_autocomplete.php?autocomplete=ort_aktiv",
					minLength:2,
					response: function(event, ui)
					{
						//Value und Label fuer die Anzeige setzen
						for(i in ui.content)
						{
							ui.content[i].value=ui.content[i].ort_kurzbz;
							if (ui.content[i].arbeitsplaetze != '')
								ui.content[i].label=ui.content[i].ort_kurzbz+" "+ui.content[i].bezeichnung+" ("+ui.content[i].arbeitsplaetze+" Arbeitsplätze)";
							else
								ui.content[i].label=ui.content[i].ort_kurzbz+" "+ui.content[i].bezeichnung;
						}
					},
					select: function(event, ui)
					{
						//Ausgewaehlte Ressource zuweisen und Textfeld wieder leeren
						$("#ort_kurzbz").val(ui.item.uid);
					}
				});

				$(".aufsicht_uid").autocomplete({
					source: "reihungstestverwaltung_autocomplete.php?autocomplete=kunde",
					minLength:2,
					response: function(event, ui)
					{
						//Value und Label fuer die Anzeige setzen
						for(i in ui.content)
						{
							//ui.content[i].value=ui.content[i].uid;
							ui.content[i].value=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
							ui.content[i].label=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
						}
					},
					select: function(event, ui)
					{
						//Ausgeaehlte Ressource zuweisen und Textfeld wieder leeren
						$(this.id).val(ui.item.uid);
					}
				});

				$("#studienplan_autocomplete").autocomplete({
					source: function(request, response) 
					{
						$.getJSON("reihungstestverwaltung_autocomplete.php", 
						{ 
							autocomplete: 'studienplan',
							aktiv: 'true',
							studiensemester_kurzbz: $('#studiensemester_dropdown').val(),
							term: request.term
						}, 
						response);
					},
					minLength:2,
					response: function(event, ui)
					{
						//Value und Label fuer die Anzeige setzen
						for(i in ui.content)
						{
							ui.content[i].value = ui.content[i].bezeichnung;
							ui.content[i].label = ui.content[i].bezeichnung;
							ui.content[i].disabled = ui.content[i].disabled;
						}
					},
					select: function(event, ui)
					{
						//Ausgewaehlte Ressource zuweisen und Textfeld wieder leeren
						$("#studienplan_id").val(ui.item.studienplan_id);
					}
				}).data("ui-autocomplete")._renderItem = function (ul, item) {
					//Add the .ui-state-disabled class and don't wrap in <a> if value is empty
					if(item.disabled == true){
						return $('<li class="ui-state-disabled" style="opacity: 1; padding: 2px .4em; font-weight: bold">'+item.label+'</li>').appendTo(ul);
					}else{
						return $("<li>")
						.append("<a>" + item.label + "</a>")
						.appendTo(ul);
					}
				};

				// Wenn die Spalten "Absolvierte Tests" oder "Ergebnis" angezeigt werden, wird die Punkteberechnung aktiviert
				$('#clm_absolviert').on('click', function()
				{
					if (<?php echo json_encode($punkteberechnung);?> == 'false' && ($('#clm_absolviert').attr('class') == 'active'))
					{
						$('.wait').html('...loading...');
						window.location.href = "<?php echo $_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&reihungstest_id='.$reihungstest_id.'&studiensemester_kurzbz='.$studiensemester_kurzbz.'&punkteberechnung=true';?>";
					}
					else if (<?php echo json_encode($punkteberechnung);?> == 'true' && $('#clm_absolviert').attr('class') == 'inactive')
						window.location.href = "<?php echo $_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&reihungstest_id='.$reihungstest_id.'&studiensemester_kurzbz='.$studiensemester_kurzbz.'&punkteberechnung=false';?>";
				});

				$(".tablesorter").each(function(i,v)
				{
					$("#"+v.id).tablesorter(
						{
							widgets: ["zebra", "filter", "stickyHeaders"],
							sortList: [[2,0],[3,0]],
							headers: {0: { sorter: false}},
							widgetOptions: {filter_cssFilter: [
									"filter_clm_null",
									"filter_clm_prestudent_id",
									"filter_clm_person_id",
									"filter_clm_null",
									"filter_clm_vorname",
									"filter_clm_geschlecht",
									"filter_clm_studiengang",
									"filter_clm_orgform",
									"filter_clm_studienplan",
									"filter_clm_einstiegssemester",
									"filter_clm_geburtsdatum",
									"filter_clm_email",
									"filter_clm_absolviert"]}
						});

					$("#toggle_"+v.id).on('click', function(e) {
						$("#"+v.id).checkboxes('toggle');
						e.preventDefault();
						if ($("input.chkbox:checked").size() > 0)
						{
							$("#mailSendButton").html('Mail an markierte Personen senden');
							$("#msgSendButton").html('Message an markierte Personen senden');
						}
						else
						{
							$("#mailSendButton").html('Mail an alle senden');
							$("#msgSendButton").html('Message an alle senden');
						}
					});

					$("#uncheck_"+v.id).on('click', function(e) {
						$("#"+v.id).checkboxes('uncheck');
						e.preventDefault();
						if ($("input.chkbox:checked").size() > 0)
						{
							$("#mailSendButton").html('Mail an markierte Personen senden');
							$("#msgSendButton").html('Message an markierte Personen senden');
						}
						else
						{
							$("#mailSendButton").html('Mail an alle senden');
							$("#msgSendButton").html('Message an alle senden');
						}
					});

					$("#"+v.id).checkboxes('range', true);
				});

				if (window.localStorage && window.localStorage !== 'undefined')
				{
					if (typeof(Storage) !== 'undefined')
					{
						var arr = ['clm_null',
							'clm_prestudent_id',
							'clm_person_id',
							'clm_null',
							'clm_vorname',
							'clm_geschlecht',
							'clm_studiengang',
							'clm_orgform',
							'clm_studienplan',
							'clm_einstiegssemester',
							'clm_geburtsdatum',
							'clm_email',
							'clm_absolviert'];
						for (var i in arr)
						{
							i = arr[i];
							if (localStorage.getItem(i) != null)
							{
								$('.'+i).css('display', localStorage.getItem(i));
								$('.filter_'+i).parent().css('display', localStorage.getItem(i));
								var element =  document.getElementById(i);
								if (typeof(element) != 'undefined' && element != null)
								{
									if (localStorage.getItem(i) == 'none')
										document.getElementById(i).className = 'inactive';
									else
										document.getElementById(i).className = 'active';
								}
								// Wenn punkteberechnung true, wird spalte clm_absolviert anzezeigt
								if(<?php echo json_encode($punkteberechnung);?> == 'true' && i == 'clm_absolviert')
								{
									$('.'+i).css('display', 'table-cell');
									$('.filter_'+i).parent().css('display', 'table-cell');
									document.getElementById(i).className = 'active';
								}
							}
						}

						var state = localStorage.getItem('bearbeitenForm');
						if (state == 'none')
						{
							$('#bearbeitenForm').hide();
							$('#toggle_bearbeitenForm').html('Ausklappen');
						}
						else
						{
							$('#bearbeitenForm').show();
							$('#toggle_bearbeitenForm').html('Einklappen');
						}

					}
					else
					{
						alert('Local Storage nicht unterstuetzt');
					}
				}

				$('.errorMessage').click(function()
				{
					$(".errorMessage").fadeTo(500, 0).slideUp(500, function(){
						$(this).remove();
					});
				});

				$('.chkbox').change(function()
				{
					if ($("input.chkbox:checked").size() > 0)
					{
						$("#mailSendButton").html('Mail an markierte Personen senden');
						$("#msgSendButton").html('Message an markierte Personen senden');
					}
					else
					{
						$("#mailSendButton").html('Mail an alle senden');
						$("#msgSendButton").html('Message an alle senden');
					}
				});

				$('#toggle_bearbeitenForm').click(function()
				{
					$('#bearbeitenForm').toggle();
					var state = $('#bearbeitenForm').css('display');
					localStorage.setItem('bearbeitenForm', state);

					if ($('#bearbeitenForm').css('display') == 'none')
					{
						$('#toggle_bearbeitenForm').html('Ausklappen');
					}
					else
					{
						$('#toggle_bearbeitenForm').html('Einklappen');
					}
				});
			});

			window.setTimeout(function()
			{
				$(".successMessage").slideUp(500, function()
				{
					$(this).remove();
				});
			}, 2000);

			function hideColumn(column)
			{
				if (window.localStorage)
				{
					if ($('.'+column).css('display') == 'table-cell')
					{
						$('.'+column).css('display', 'none');
						$('.filter_'+column).parent().css('display', 'none');
						localStorage.setItem(column, 'none');
						if (localStorage.getItem(column) == 'none')
						{
							document.getElementById(column).className = 'inactive';
						}
						else
							document.getElementById(column).className = 'active';
					}
					else
					{
						$('.'+column).css('display', 'table-cell');
						$('.tablesorter').trigger('filterReset');
						$('.filter_'+column).parent().css('display', 'table-cell');
						localStorage.setItem(column, 'table-cell');
						if (localStorage.getItem(column) == 'none')
							document.getElementById(column).className = 'inactive';
						else
							document.getElementById(column).className = 'active';
					}
				}
			}

			function LoadStudienplan(type)
			{
				if(typeof type=='undefined')
					type='';

				var studiengang_kz = $('#studiengang_dropdown'+type).val();
				var studiensemester_kurzbz = $('#studiensemester_dropdown'+type).val();

				$.ajax({
					url: "reihungstestverwaltung_autocomplete.php",
					data: { 'autocomplete':'getStudienplan',
							'stg_kz':studiengang_kz,
							'studiensemester_kurzbz': studiensemester_kurzbz
						 },
					type: "POST",
					dataType: "json",
					success: function(data)
					{
						$("#studienplan_dropdown"+type).empty();
						$("#studienplan_dropdown"+type).append('<option value="">Studienplan auswaehlen</option>');
						$.each(data, function(i, data){
							if (typeof data['sto_bezeichnung']!='undefined')
								$("#studienplan_dropdown"+type).append('<option value="" disabled>Studienordnung: '+data['sto_bezeichnung']+'</option>');

							$("#studienplan_dropdown"+type).append('<option value="'+data['stpid']+'">'+data['bezeichnung']+'</option>');
						});
					},
					error: function(data)
					{
						alert("Fehler beim Laden der Daten: "+data);
					}
				});
			}

			function SendMail()
			{
				// Wenn Checkboxen markiert sind, an diese senden, sonst an alle
				if ($("input.chkbox:checked").size() > 0)
					var elements = $("input.chkbox:checked");
				else
					var elements = $("input.chkbox");
				var mailadressen = '';
				var adresse = '';

				// Schleife ueber die einzelnen Elemente
				$.each(elements, function(index, item)
				{
					adresse = $(this).closest('tr').find('td.clm_email a:first').attr('href');
					adresse = adresse.replace(/^mailto?:/, '') + ';';
					mailadressen += adresse;
				});
				window.location.href = "mailto:?bcc="+mailadressen;
			}
			
			function SendMessage()
			{
				// Wenn Checkboxen markiert sind, an diese senden, sonst an alle
				if ($("input.chkbox:checked").size() > 0)
					var elements = $("input.chkbox:checked");
				else
					var elements = $("input.chkbox");
				var form = $("#sendMsgForm");
				form.find("input[type=hidden]").remove();
				$.each(elements, function(index, item)
				{
					var prestudent_id = $(this).closest('tr').find('td.clm_prestudent_id').text();
					form.append("<input type='hidden' name='prestudent_id[]' value='" + prestudent_id + "'>");
				});
				form.submit();
			}
		</script>
		<style type="text/css">
		.active
		{
			cursor: pointer;
			color: #000000;
			margin-right: 5px;
			text-decoration: none;
			border-radius: 3px;
			-webkit-border-radius: 3px;
			-moz-border-radius: 3px;
			background-color: #8dbdd8;
			border-top: 3px solid #8dbdd8;
			border-bottom: 3px solid #8dbdd8;
			border-right: 8px solid #8dbdd8;
			border-left: 8px solid #8dbdd8;
			display: inline-block;
		}
		.inactive
		{
			cursor: pointer;
			color: #000000;
			margin-right: 5px;
			text-decoration: none;
			border-radius: 3px;
			-webkit-border-radius: 3px;
			-moz-border-radius: 3px;
			background-color: #DCE4EF;
			border-top: 3px solid #DCE4EF;
			border-bottom: 3px solid #DCE4EF;
			border-right: 8px solid #DCE4EF;
			border-left: 8px solid #DCE4EF;
			display: inline-block;
		}
		.buttongreen, a.buttongreen
		{
			cursor: pointer;
			color: #FFFFFF;
			margin: 0 5px 5px 0;
			text-decoration: none;
			border-radius: 3px;
			-webkit-border-radius: 3px;
			-moz-border-radius: 3px;
			background-color: #5cb85c;
			border-top: 3px solid #5cb85c;
			border-bottom: 3px solid #5cb85c;
			border-right: 8px solid #5cb85c;
			border-left: 8px solid #5cb85c;
			display: inline-block;
			vertical-align: middle;
		}
		.buttonorange, a.buttonorange
		{
			cursor: pointer;
			color: #FFFFFF;
			margin: 0 5px 5px 0;
			text-decoration: none;
			border-radius: 3px;
			-webkit-border-radius: 3px;
			-moz-border-radius: 3px;
			background-color: #EC971F;
			border-top: 3px solid #EC971F;
			border-bottom: 3px solid #EC971F;
			border-right: 8px solid #EC971F;
			border-left: 8px solid #EC971F;
			display: inline-block;
			vertical-align: middle;
		}
		.studienplan_listitem
		{
			background-color: lightgray;
			padding: 2px 5px 2px 6px;
			border-radius: 3px;
			-webkit-border-radius: 3px;
			-moz-border-radius: 3px;
			vertical-align: middle;
		}
		.ort_listitem
		{
			background-color: lightgray;
			padding: 2px 5px 2px 6px;
			border-radius: 3px;
			-webkit-border-radius: 3px;
			-moz-border-radius: 3px;
			vertical-align: middle;
		}
		.feldtitel
		{
			text-align: right;
			padding-right: 5px;
		}
		input
		{
			padding-left: 6px;
		}
		.infoDiv
		{
			position: fixed;
			width: 500px;
			top: 0%;
			left: 50%;
			margin-left: -250px; /* Negative half of width. */
		}
		.successMessage
		{
			color: #155724;
			background-color: #d4edda;
			padding: .75rem 1.25rem;
			border: 1px solid #c3e6cb;
		}
		.errorMessage
		{
			color: #721c24;
			background-color: #f8d7da;
			padding: .75rem 1.25rem;
			border: 1px solid #f5c6cb;
		}
		</style>
	</head>
	<body class="Background_main">
	<h2>Reihungstest - Verwaltung</h2>

<?php
$messageSuccess = '';
$messageError = '';
// Speichern eines Termines
if(isset($_POST['speichern']) || isset($_POST['kopieren']))
{
	$reihungstest = new reihungstest();

	if(isset($_POST['reihungstest_id']) && $_POST['reihungstest_id']!='' && !isset($_POST['kopieren']))
	{
		//Reihungstest laden
		if(!$reihungstest->load($_POST['reihungstest_id']))
		{
			die($reihungstest->errormsg);
		}

		$reihungstest->new = false;
	}
	else
	{
		//Neuen Reihungstest anlegen
		$reihungstest->new = true;
		$reihungstest->insertvon = $user;
		$reihungstest->insertamum = date('Y-m-d H:i:s');
	}
	
	// OE über Studiengang des Reihungstests laden und Berechtigung prüfen
	$stg_rechtecheck = new studiengang($reihungstest->studiengang_kz);
	if(!$rechte->isBerechtigt('lehre/reihungstest', $stg_rechtecheck->oe_kurzbz, 'sui'))
	{
		die($rechte->errormsg);
	}

	//Datum und Uhrzeit pruefen
	if($_POST['datum']!='' && !$datum_obj->checkDatum($_POST['datum']))
	{
		$messageError .= '<p>Datum ist ungueltig. Das Datum muss im Format DD.MM.JJJJ eingegeben werden</p>';
		$error = true;
	}
	if($_POST['uhrzeit']!='' && !$datum_obj->checkUhrzeit($_POST['uhrzeit']))
	{
		$messageError .= '<p>Uhrzeit ist ungueltig. Die Uhrzeit muss im Format HH:MM angegeben werden!</p>';
		$error = true;
	}
	// Die Anmeldefrist darf nicht nach dem Testdatum sein
	if($_POST['anmeldefrist'] != '')
	{
		if (strtotime($_POST['anmeldefrist']) > strtotime($_POST['datum']))
		{
			$messageError .= '<p>Die Anmeldefrist darf nicht nach dem Testdatum liegen</p>';
			$error = true;
		}
	}
	
	if (isset($_POST['zugangs_ueberpruefung']) && $_POST['zugangcode'] === '')
	{
		$messageError .= '<p>Der Zugangscode muss ausgefüllt sein, wenn die Zugangsüberprüfung aktiviert ist. </p>';
		$error = true;
	}

	if(!$error)
	{
		if (isset($_POST['kopieren']))
		{
			$reihungstest->freigeschaltet = false;
			$reihungstest->max_teilnehmer = filter_input(INPUT_POST, 'max_teilnehmer', FILTER_VALIDATE_INT);
			$reihungstest->oeffentlich = false;
			$reihungstest->stufe = filter_input(INPUT_POST, 'stufe', FILTER_VALIDATE_INT);
			$reihungstest->aufnahmegruppe_kurzbz = filter_input(INPUT_POST, 'aufnahmegruppe');
			$reihungstest->anmeldefrist = $datum_obj->formatDatum($_POST['anmeldefrist']);
			$reihungstest->zugangs_ueberpruefung = false;
			$reihungstest->zugangscode = null;
		}
		else
		{
			$reihungstest->freigeschaltet = isset($_POST['freigeschaltet']);
			$reihungstest->max_teilnehmer = filter_input(INPUT_POST, 'max_teilnehmer', FILTER_VALIDATE_INT);
			if ($rechte->isBerechtigt('lehre/reihungstestOeffentlich', $stg_rechtecheck->oe_kurzbz, 'suid'))
			{
				$reihungstest->oeffentlich = (isset($_POST['oeffentlich']) ? true : false);
			}
			$reihungstest->stufe = filter_input(INPUT_POST, 'stufe', FILTER_VALIDATE_INT);
			$reihungstest->aufnahmegruppe_kurzbz = filter_input(INPUT_POST, 'aufnahmegruppe');
			$reihungstest->anmeldefrist = $datum_obj->formatDatum($_POST['anmeldefrist']);
			$reihungstest->updateamum = date('Y-m-d H:i:s');
			$reihungstest->updatevon = $user;
			$reihungstest->zugangs_ueberpruefung = isset($_POST['zugangs_ueberpruefung']);
			$reihungstest->zugangscode = ($_POST['zugangcode'] === '' ? null : $_POST['zugangcode']);
		}
		$reihungstest->studiengang_kz = $_POST['studiengang_kz'];
		//$reihungstest->ort_kurzbz = $_POST['ort_kurzbz'];
		$reihungstest->studiensemester_kurzbz = filter_input(INPUT_POST, 'studiensemester_kurzbz');
		$reihungstest->anmerkung = $_POST['anmerkung'];
		$reihungstest->datum = $datum_obj->formatDatum($_POST['datum']);
		$reihungstest->uhrzeit = $_POST['uhrzeit'];

		if($reihungstest->save())
		{
			if (isset($_POST['ort_kurzbz']) && $_POST['ort_kurzbz']!='')
			{
				$ort = new ort();

				if (!$ort->load($_POST['ort_kurzbz']))
					$messageError .= '<p>Die Bezeichnung des Ortes ist ungueltig oder wurde nicht gefunden</p>';
				else
				{
					if($rechte->isBerechtigt('lehre/reihungstestOrt', $stg_rechtecheck->oe_kurzbz, 'sui'))
					{
						$orte_zugeteilt = new reihungstest();
						$orte_zugeteilt->getOrteReihungstest($reihungstest->reihungstest_id);
						$zugeteilt = false;
						foreach ($orte_zugeteilt->result AS $row)
						{
							if ($row->ort_kurzbz == $_POST['ort_kurzbz'])
							{
								$zugeteilt = true;
								break;
							}
						}
						// Check, ob der Raum schon diesem RT zugeteilt ist
						if ($zugeteilt == false)
						{
							$add_ort = new reihungstest();
							$add_ort->new = true;
							$add_ort->reihungstest_id = $reihungstest->reihungstest_id;
							$add_ort->ort_kurzbz = $_POST['ort_kurzbz'];
							$add_ort->uid = null;

							if ($add_ort->saveOrtReihungstest())
							{
								$messageSuccess .= '<p><b>Daten wurden erfolgreich gespeichert</b> <script>window.opener.StudentReihungstestDropDownRefresh();</script></p>';
							}
							else
								$messageError .= '<p>Fehler beim Speichern der Raumzuordnung: '.$db->convert_html_chars($reihungstest->errormsg).'</p>';
						}
						else
							$messageError .= '<p>Der Raum '.$_POST['ort_kurzbz'].' ist bereits diesem Reihungstest zugeteilt</p>';
					}
					else
						die($rechte->errormsg);
				}
			}
			if (isset($_POST['studienplan_id']) && $_POST['studienplan_id'] != '')
			{
				$rt_stplaeneArray = array();
				// Studienpläne laden um nicht doppelt zu speichern
				$rt_stplaene = new reihungstest();
				$rt_stplaene->getStudienplaeneReihungstest($reihungstest->reihungstest_id);
				foreach ($rt_stplaene->result as $key => $value)
				{
					$rt_stplaeneArray[] = $value->studienplan_id;
				}

				// Es können mehrere Studienpläne übergeben werden
				$studienplaeneArr = explode(',', $_POST['studienplan_id']);
				$error = false;
				foreach ($studienplaeneArr AS $studienplan)
				{
					$rt_stpl = new reihungstest();
					$rt_stpl->new = true;
					$rt_stpl->reihungstest_id = $reihungstest->reihungstest_id;
					$rt_stpl->studienplan_id = $studienplan;
					
					if (!in_array($studienplan, $rt_stplaeneArray))
					{
						if (!$rt_stpl->saveStudienplanReihungstest())
						{
							$messageError .= '<p>Fehler beim Speichern des Studienplans: '.$db->convert_html_chars($rt_stpl->errormsg).'</p>';
							$error = true;
						}
					}
				}
				if ($error == false)
				{
					$messageSuccess .= '<p><b>Daten wurden erfolgreich gespeichert</b> <script>window.opener.StudentReihungstestDropDownRefresh();</script></p>';
				}
			}
			$reihungstest_id = $reihungstest->reihungstest_id;
			$stg_kz = $reihungstest->studiengang_kz;
			$studiensemester_kurzbz = $reihungstest->studiensemester_kurzbz;
			// Beim kopieren auch die zugeteilten Studienpläne übernehmen
			if (isset($_POST['reihungstest_id']) && isset($_POST['kopieren']))
			{
				$rt_studienplan = new reihungstest();
				$rt_studienplan->getStudienplaeneReihungstest($_POST['reihungstest_id']);
				$error = false;
				foreach ($rt_studienplan->result as $row) 
				{
					$rtKopieStudienplan = new reihungstest();
					$rtKopieStudienplan->new = true;
					$rtKopieStudienplan->reihungstest_id = $reihungstest_id;
					$rtKopieStudienplan->studienplan_id = $row->studienplan_id;
					if (!$rtKopieStudienplan->saveStudienplanReihungstest())
						$error = true;
				}
				if ($error == true)
					$messageError .= '<p>Fehler beim übernehmen der Studienpläne</p>';
			}
			// Beim kopieren auch die zugeteilten Räume übernehmen
			if (isset($_POST['reihungstest_id']) && isset($_POST['kopieren']))
			{
				$rtOrt = new reihungstest();
				$rtOrt->getOrteReihungstest($_POST['reihungstest_id']);
				$error = false;
				foreach ($rtOrt->result as $row)
				{
					$rtKopieOrt = new reihungstest();
					$rtKopieOrt->new = true;
					$rtKopieOrt->reihungstest_id = $reihungstest_id;
					$rtKopieOrt->ort_kurzbz = $row->ort_kurzbz;
					$rtKopieOrt->uid = '';
					if (!$rtKopieOrt->saveOrtReihungstest())
						$error = true;
				}
				if ($error == true)
					$messageError .= '<p>Fehler beim übernehmen der Raumzuteilungen</p>';
			}
			if ($reihungstest->new == true)
			{
				if (isset($_POST['kopieren']))
				{
					$messageSuccess .= '<p>Der Termin wurde erfolgreich kopiert</p>';
				}
				else 
				{
					$messageSuccess .= '<p>Neuer Reihungstesttermin erfolgreich angelegt</p>';
				}
			}
			else
			{
				$messageSuccess .= '<p>Daten wurden erfolgreich gespeichert</p>';
			}
		}
		else
		{
			$messageError .= '<p>Fehler beim Speichern der Daten: '.$db->convert_html_chars($reihungstest->errormsg).'</p>';
		}
	}
	$neu=false;
}

if ($reihungstest_id != '' || isset($_POST['reihungstest_id']))
{
	$orte = new Reihungstest();
	$orte->getOrteReihungstest($reihungstest_id != ''?$reihungstest_id:$_POST['reihungstest_id']);
	$orte_array = array();
	foreach ($orte->result AS $row)
	{
		// Wenn Arbeitsplaetze in DB gepflegt sind, Schwund herausrechnen (wenn gesetzt) sonst max_person verwenden und Schwund herausrechnen (wenn gesetzt)
		$raum = new Ort();
		$raum->load($row->ort_kurzbz);
		if ($raum->arbeitsplaetze != '')
		{
			if(defined('REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND') && REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND > 0)
				$orte_array[$row->ort_kurzbz] = $raum->arbeitsplaetze - ceil(($raum->arbeitsplaetze/100)*REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND);
			else
				$orte_array[$row->ort_kurzbz] = $raum->arbeitsplaetze;
		}
		else
		{
			if(defined('REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND') && REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND > 0)
				$orte_array[$row->ort_kurzbz] = $raum->max_person - ceil(($raum->max_person/100)*REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND);
			else
				$orte_array[$row->ort_kurzbz] = $raum->max_person;
		}
	}
	$arbeitsplaetze_gesamt = array_sum($orte_array);
}

if(isset($_POST['raumzuteilung_speichern']))
{
	$raumzuteilung = new reihungstest();

	if(isset($_POST['reihungstest_id']) && $_POST['reihungstest_id']!='')
	{
		//Reihungstest laden
		if(!$raumzuteilung->load($_POST['reihungstest_id']))
		{
			die($raumzuteilung->errormsg);
		}
		
		// OE über Studiengang des Reihungstests laden und Berechtigung prüfen
		$stg_rechtecheck = new studiengang($raumzuteilung->studiengang_kz);
		if(!$rechte->isBerechtigt('lehre/reihungstest', $stg_rechtecheck->oe_kurzbz, 'su'))
		{
			die($rechte->errormsg);
		}
		
		if (isset($_POST['checkbox']))
		{
			$person_ids = $_POST['checkbox'];

			foreach ($person_ids AS $key=>$value)
			{
				$load_person = new reihungstest();
				if ($load_person->getPersonReihungstest($key, $_POST['reihungstest_id']))
				{
					$raumzuteilung->new = false;
					$raumzuteilung->rt_person_id = $load_person->rt_person_id;
					$raumzuteilung->anmeldedatum = $load_person->anmeldedatum;
					$raumzuteilung->teilgenommen = $load_person->teilgenommen;
					$raumzuteilung->punkte = $load_person->punkte;
					$raumzuteilung->studienplan_id = $load_person->studienplan_id;

					$raumzuteilung->reihungstest_id = $load_person->reihungstest_id;
					$raumzuteilung->person_id = $key;
					$raumzuteilung->ort_kurzbz = $_POST['raumzuteilung'];
					$raumzuteilung->updateamum = date('Y-m-d H:i:s');
					$raumzuteilung->updatevon = $user;
				}
				else
					die('PersonID '.$key.' hat keine korrekte Zuordnung -> Abbruch');

				if (!$raumzuteilung->savePersonReihungstest())
				{
					$messageError .= '<p>Fehler beim Speichern der Daten: '.$db->convert_html_chars($reihungstest->errormsg).'<p>';
				}
			}
		}
		$reihungstest_id = $_POST['reihungstest_id'];
	}
	$neu=false;
}

// Uebertraegt die Punkte eines Prestudenten ins FAS
if(isset($_GET['type']) && $_GET['type']=='savertpunkte')
{
	$prestudent = new prestudent();
	$prestudent->load($prestudent_id);

	if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('assistenz', $prestudent->studiengang_kz, 'sui'))
	{
		$rtperson = new reihungstest();
		$rtperson->loadReihungstestPerson($_GET['rt_person_id']);
		$rtperson->punkte = str_replace(',','.',$rtpunkte);
		$rtperson->new = false;
		$rtperson->teilgenommen = true;
		$rtperson->updateamum = date('Y-m-d H:i:s');
		$rtperson->updatevon = $user;
		if(!$rtperson->savePersonReihungstest())
		{
			$messageError .= '<p>Fehler:'.$rtperson->errormsg.'</p>';
		}
	}
	else
	{
		$messageError .= '<p><br>Sie haben keine Berechtigung zur Uebernahme der Punkte fuer '.$db->convert_html_chars($row->nachname).' '.$db->convert_html_chars($row->vorname).'</p>';
	}
}

// Uebertraegt alle Punkte eines Reihungstests ins FAS
if(isset($_GET['type']) && $_GET['type']=='saveallrtpunkte')
{
	$errormsg='';
	$qry = "SELECT
				prestudent_id, tbl_prestudent.studiengang_kz, nachname, vorname,
				tbl_studiengang.oe_kurzbz, rt_person_id, tbl_person.person_id
			FROM
				public.tbl_prestudent
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_studiengang USING(studiengang_kz)
				JOIN public.tbl_rt_person USING(person_id)
				JOIN lehre.tbl_studienplan ON(tbl_rt_person.studienplan_id=tbl_studienplan.studienplan_id)
				JOIN lehre.tbl_studienordnung ON(tbl_studienplan.studienordnung_id=tbl_studienordnung.studienordnung_id)
			WHERE
				tbl_studienordnung.studiengang_kz=tbl_prestudent.studiengang_kz
				AND tbl_rt_person.rt_id=".$db->db_add_param($reihungstest_id, FHC_INTEGER);

	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			if($rechte->isBerechtigt('student/stammdaten', $row->oe_kurzbz,'sui'))
			{
				$prestudent = new prestudent();
				$prestudent->load($row->prestudent_id);

				$reihungstest = new reihungstest();
				if($reihungstest->loadReihungstestPerson($row->rt_person_id))
				{
					$pruefling = new pruefling();
					if(defined('FAS_REIHUNGSTEST_PUNKTE') && FAS_REIHUNGSTEST_PUNKTE)
						$rtpunkte = $pruefling->getReihungstestErgebnisPerson($row->person_id, true, $reihungstest->reihungstest_id);
					else
						$rtpunkte = $pruefling->getReihungstestErgebnisPerson($row->person_id, false, $reihungstest->reihungstest_id);

					$reihungstest->punkte = str_replace(',','.',$rtpunkte);
					$reihungstest->reihungstestangetreten = true;
					$reihungstest->save(false);
					$reihungstest->new = false;

					if($rtpunkte!==false)
					{
						$reihungstest->punkte = str_replace(',','.',$rtpunkte);
						$reihungstest->teilgenommen = true;
						$reihungstest->save(false);
						$reihungstest->new = false;
						$reihungstest->updateamum = date('Y-m-d H:i:s');
						$reihungstest->updatevon = $user;

						if(!$reihungstest->savePersonReihungstest())
						{
							$errormsg .='<br>Fehler:'.$reihungstest->errorsmg;
						}
					}
				}
			}
			else
			{
				$errormsg .= "<br>Sie haben keine Berechtigung zur Uebernahme der Punkte fuer $row->nachname $row->vorname";
			}
		}
		if($errormsg!='')
		{
			$messageError .= '<p>'.$db->convert_html_chars($errormsg).'</p>';
		}
	}
}

// Verteilt alle BewerberInnen gleichmaessig auf die Raeume
if(isset($_GET['type']) && $_GET['type']=='verteilen')
{
	if($reihungstest_id!='')
	{
		$rt_rechtecheck = new reihungstest($reihungstest_id);
		// OE über Studiengang des Reihungstests laden und Berechtigung prüfen
		$stg_rechtecheck = new studiengang($rt_rechtecheck->studiengang_kz);
		if(!$rechte->isBerechtigt('lehre/reihungstest', $stg_rechtecheck->oe_kurzbz, 'sui'))
		{
			die($rechte->errormsg);
		}
		$errormsg='';
		$qry = "SELECT
					person_id,
					vorname,
					nachname,
					ort_kurzbz
				FROM
					public.tbl_prestudent
					JOIN public.tbl_person USING (person_id)
					LEFT JOIN public.tbl_rt_person USING (person_id)
				WHERE
					tbl_rt_person.rt_id = ".$db->db_add_param($reihungstest_id, FHC_INTEGER)."
					AND tbl_rt_person.studienplan_id IN (SELECT studienplan_id FROM public.tbl_prestudentstatus WHERE prestudent_id=tbl_prestudent.prestudent_id)
				ORDER BY nachname,vorname ";

		$raumzuteilung = new reihungstest();
		if($result = $db->db_query($qry))
		{
			$anz_personen = $db->db_num_rows($result);

			if($arbeitsplaetze_gesamt!=0)
			{
				$multiplikator = $anz_personen/$arbeitsplaetze_gesamt;
				foreach ($orte->result AS $ort)
				{
					$counter = 0;

					$anz_zugeteilte = new Reihungstest();
					$anz_zugeteilte->getPersonReihungstestOrt($reihungstest_id, $ort->ort_kurzbz);
					$anz_zugeteilte = count($anz_zugeteilte->result);

					$anteil = round(($orte_array[$ort->ort_kurzbz] * $multiplikator))-$anz_zugeteilte;

					if ($orte_array[$ort->ort_kurzbz] == 0 || ($anteil - $anz_zugeteilte)<=0)
						continue;

					while($row = $db->db_fetch_object($result))
					{
						//Nur Personen ohne Raumzuteilung verteilen
						if ($row->ort_kurzbz == '')
						{
							$load_person = new reihungstest();
							if ($load_person->getPersonReihungstest($row->person_id, $reihungstest_id))
							{
								$raumzuteilung->new = false;
								$raumzuteilung->rt_person_id = $load_person->rt_person_id;
								$raumzuteilung->anmeldedatum = $load_person->anmeldedatum;
								$raumzuteilung->teilgenommen = $load_person->teilgenommen;
								$raumzuteilung->punkte = $load_person->punkte;
								$raumzuteilung->studienplan_id = $load_person->studienplan_id;

								$raumzuteilung->reihungstest_id = $load_person->reihungstest_id;
								$raumzuteilung->person_id = $row->person_id;
								$raumzuteilung->ort_kurzbz = $ort->ort_kurzbz;
								$raumzuteilung->updateamum = date('Y-m-d H:i:s');
								$raumzuteilung->updatevon = $user;
							}
							else
							{
								die('Person zuteilung nicht gefunden');
							}

							if (!$raumzuteilung->savePersonReihungstest())
							{
								$messageError .= '<p>Fehler beim Speichern der Daten: '.$db->convert_html_chars($raumzuteilung->errormsg).'</p>';
							}
							$counter++;

							//Wenn 0 Arbeitsplaetze vorhanden sind oder die max. Arbeitsplatzanzahl erreicht ist
							if ($orte_array[$ort->ort_kurzbz] == 0 || ($anteil - $counter)<=0)
								break;
						}
					}
				}
			}
			else
			{
				$messageError .= '<p>Nicht genug Raumkapazität vorhanden</p>';
			}
		}

	}
	$neu=false;
}

// Fuellt die Raeume aufsteigend mit BewerberInnen an
if(isset($_GET['type']) && $_GET['type']=='auffuellen')
{
	if($reihungstest_id!='')
	{
		$rt_rechtecheck = new reihungstest($reihungstest_id);
		// OE über Studiengang des Reihungstests laden und Berechtigung prüfen
		$stg_rechtecheck = new studiengang($rt_rechtecheck->studiengang_kz);
		if(!$rechte->isBerechtigt('lehre/reihungstest', $stg_rechtecheck->oe_kurzbz, 'sui'))
		{
			die($rechte->errormsg);
		}
		
		$orte = new Reihungstest();
		$orte->getOrteReihungstest($reihungstest_id);

		$errormsg='';
		$qry = "SELECT
					person_id,
					vorname,
					nachname,
					ort_kurzbz
				FROM
					public.tbl_prestudent
				JOIN
					public.tbl_person USING (person_id)
					LEFT JOIN public.tbl_rt_person USING (person_id)
				WHERE
					tbl_rt_person.rt_id = ".$db->db_add_param($reihungstest_id, FHC_INTEGER)."
					AND tbl_rt_person.studienplan_id IN (SELECT studienplan_id FROM public.tbl_prestudentstatus WHERE prestudent_id=tbl_prestudent.prestudent_id)
					AND tbl_rt_person.ort_kurzbz IS NULL
				ORDER BY nachname,vorname ";

		$raumzuteilung = new reihungstest();
		if($result = $db->db_query($qry))
		{
			foreach ($orte->result AS $ort)
			{
				$counter = 0;

				$anz_zugeteilte = new Reihungstest();
				$anz_zugeteilte->getPersonReihungstestOrt($reihungstest_id, $ort->ort_kurzbz);
				$anz_zugeteilte = count($anz_zugeteilte->result);

				if ($orte_array[$ort->ort_kurzbz] == 0 || ($orte_array[$ort->ort_kurzbz]-$anz_zugeteilte)<=0)
					continue;

				while($row = $db->db_fetch_object($result))
				{
					$load_person = new reihungstest();

					if ($load_person->getPersonReihungstest($row->person_id, $reihungstest_id))
					{
						$raumzuteilung->new = false;
						$raumzuteilung->rt_person_id = $load_person->rt_person_id;
						$raumzuteilung->anmeldedatum = $load_person->anmeldedatum;
						$raumzuteilung->teilgenommen = $load_person->teilgenommen;
						$raumzuteilung->punkte = $load_person->punkte;
						$raumzuteilung->studienplan_id = $load_person->studienplan_id;

						$raumzuteilung->reihungstest_id = $load_person->reihungstest_id;
						$raumzuteilung->person_id = $row->person_id;
						$raumzuteilung->ort_kurzbz = $ort->ort_kurzbz;
						$raumzuteilung->updateamum = date('Y-m-d H:i:s');
						$raumzuteilung->updatevon = $user;
					}
					else
						die('Personen zuteilung nicht gefunden');

					if (!$raumzuteilung->savePersonReihungstest())
					{
						$messageError .= '<p>Fehler beim Speichern der Daten: '.$db->convert_html_chars($raumzuteilung->errormsg).'</p>';
					}
					$counter++;

					//Wenn 0 Arbeitsplaetze vorhanden sind oder die max. Arbeitsplatzanzahl erreicht ist
					if ($orte_array[$ort->ort_kurzbz] == 0 || ($orte_array[$ort->ort_kurzbz]-($anz_zugeteilte+$counter))<=0)
						break;
				}
			}
		}

	}
	$neu=false;
}

if(isset($_POST['aufsicht']) && $_POST['aufsicht']!='' && !isset($_POST['kopieren']))
{
	$save_aufsicht = new reihungstest();

	if(isset($_POST['reihungstest_id']) && $_POST['reihungstest_id']!='')
	{
		$rt_rechtecheck = new reihungstest($_POST['reihungstest_id']);
		// OE über Studiengang des Reihungstests laden und Berechtigung prüfen
		$stg_rechtecheck = new studiengang($rt_rechtecheck->studiengang_kz);
		if(!$rechte->isBerechtigt('lehre/reihungstest', $stg_rechtecheck->oe_kurzbz, 'su'))
		{
			die($rechte->errormsg);
		}
		
		//Reihungstest laden
		if(!$save_aufsicht->load($_POST['reihungstest_id']))
		{
			die($save_aufsicht->errormsg);
		}
		$aufsichtspersonen = $_POST['aufsicht'];

		foreach ($aufsichtspersonen AS $key=>$value)
		{
			// UID aus POST-String auslesen
			$length = (strrpos($value, ')')) - (strpos($value, '('));
			$uid = substr($value,strpos($value, '(')+1, $length-1);

			$benutzer = new benutzer();
			if ($uid!='' && !$benutzer->load($uid))
				$messageError .= '<p>Die UID '.$value.' konnte nicht gefunden werden</p>';
			else
			{
				$save_aufsicht->new = false;
				$save_aufsicht->reihungstest_id = $_POST['reihungstest_id'];
				$save_aufsicht->ort_kurzbz = $key;
				$save_aufsicht->uid = $uid;
				if (!$save_aufsicht->saveOrtReihungstest())
				{
					$messageError .= '<p>Fehler beim Speichern der Daten: '.$db->convert_html_chars($reihungstest->errormsg).'</p>';
				}
			}
		}
		$reihungstest_id = $save_aufsicht->reihungstest_id;
		$stg_kz = $save_aufsicht->studiengang_kz;
	}
	$neu=false;
}

if(isset($_POST['delete_ort']))
{
	if(isset($_POST['reihungstest_id']) && $_POST['reihungstest_id']!='')
	{
		$rt_rechtecheck = new reihungstest($_POST['reihungstest_id']);
		// OE über Studiengang des Reihungstests laden und Berechtigung prüfen
		$stg_rechtecheck = new studiengang($rt_rechtecheck->studiengang_kz);
		if(!$rechte->isBerechtigt('lehre/reihungstestOrt', $stg_rechtecheck->oe_kurzbz, 'suid'))
		{
			die($rechte->errormsg);
		}
		
		$delete_ort = new reihungstest();
		$delete_ort->getPersonReihungstestOrt($_POST['reihungstest_id'], $_POST['delete_ort']);

		if (count($delete_ort->result) == 0)
		{
			if (!$delete_ort->deleteOrtReihungstest($_POST['reihungstest_id'], $_POST['delete_ort']))
				$messageError .= '<p>Fehler beim löschen der Raumzuordnung: '.$db->convert_html_chars($reihungstest->errormsg).'</p>';
		}
		else
			$messageError .= '<p>Dem Raum '.$_POST['delete_ort'].' sind noch '.count($delete_ort->result).' Personen zugeteilt. Bitte entfernen Sie zuerst diese Zuteilungen</p>';

		$reihungstest_id = $_POST['reihungstest_id'];
		$studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
	}
	$neu=false;
}

if(isset($_POST['delete_studienplan'])) //@todo: Check, ob Zuordnungen zu diesem Studienplan vorhanden sind. Wenn ja, nicht loeschen!
{
	if(isset($_POST['reihungstest_id']) && $_POST['reihungstest_id']!='')
	{
		$delete_studienplan = new reihungstest();

		if (!$delete_studienplan->deleteStudienplanReihungstest($_POST['reihungstest_id'], $_POST['delete_studienplan']))
			$messageError .= '<p>Fehler beim löschen der Studienplanzuteilung: '.$db->convert_html_chars($delete_studienplan->errormsg).'</p>';

		$reihungstest_id = $_POST['reihungstest_id'];
		$studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
	}
	$neu=false;
}

if(isset($_POST['deleteReihungstest'])) //@todo: Check, ob Zuordnungen zu diesem Studienplan vorhanden sind. Wenn ja, nicht loeschen!
{
	if(isset($_POST['reihungstest_id']) && $_POST['reihungstest_id']!='')
	{
		$deleteReihungstest = new reihungstest();

		if (!$deleteReihungstest->delete($_POST['reihungstest_id']))
			$messageError .= '<p>Fehler beim löschen des Reihungstests: '.$db->convert_html_chars($deleteReihungstest->errormsg).'</p>';

		$reihungstest_id = '';
		$studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
	}
	$neu = true;
}

echo '<div class="infoDiv">';
if ($messageSuccess != '')
{
	echo '<div class="successMessage">'.$messageSuccess.'</div>';
}
if ($messageError != '')
{
	echo '<div class="errorMessage">'.$messageError.'</div>';
}
echo '</div>';

echo '<table width="100%"><tr><td>';

// Studiengang DropDown
echo "<SELECT name='studiengang' onchange='window.location.href=this.value'>";
if($stg_kz==-1)
	$selected='selected';
else
	$selected='';

echo "<OPTION value='".$_SERVER['PHP_SELF']."?stg_kz=-1&studiensemester_kurzbz=".$studiensemester_kurzbz."' $selected>Alle Studiengaenge</OPTION>";
foreach ($studiengang->result as $row)
{
	$stg_arr[$row->studiengang_kz] = $row->kuerzel;
	
	if ($typ != $row->typ || $typ == '')
	{
		if ($typ != '')
		{
			echo '</optgroup>';
		}
		echo '<optgroup label="'.($types->studiengang_typ_arr[$row->typ] != ''?$types->studiengang_typ_arr[$row->typ]:$row->typ).'">';
	}
	
	if ($stg_kz == '')
		$stg_kz = $row->studiengang_kz;
	if ($row->studiengang_kz == $stg_kz)
		$selected = 'selected';
	else
		$selected = '';
	
	echo "<OPTION value='" . $_SERVER['PHP_SELF'] . "?stg_kz=$row->studiengang_kz&studiensemester_kurzbz=$studiensemester_kurzbz' $selected>" . $db->convert_html_chars($row->kuerzel) . " (" . $db->convert_html_chars($row->bezeichnung) . ")</OPTION>" . "\n";
	$typ = $row->typ;
}
echo "</SELECT>";
$studienplan_obj = new studienplan();
$studienplan_obj->getStudienplaeneFromSem($stg_kz, $studiensemester_kurzbz);
$studienordnung_arr = array();
$studienplan_arr = array();
$studienplaene_verwendet = array();

foreach($studienplan_obj->result as $row_sto)
{
	$studienordnung_arr[$row_sto->studienordnung_id]['bezeichnung']=$row_sto->bezeichnung_studienordnung;
	$studienplan_arr[$row_sto->studienordnung_id][$row_sto->studienplan_id]['bezeichnung']=$row_sto->bezeichnung_studienplan;

	$studienplan_arr[$row_sto->studienordnung_id][$row_sto->studienplan_id]['orgform_kurzbz']=$row_sto->orgform_kurzbz;
	$studienplan_arr[$row_sto->studienordnung_id][$row_sto->studienplan_id]['sprache']=(isset($sprachen_arr[$row_sto->sprache])?$sprachen_arr[$row_sto->sprache]:'');
	$studienplaene_verwendet[$row_sto->studienplan_id] = $row_sto->bezeichnung_studienplan;
}

// Pruefen ob uebergebene StudienplanID in Auswahl enthalten
// ist und ggf auf leer setzen
if($studienplan_id!='')
{
	$studienplan_found=false;
	foreach($studienplan_arr as $stoid=>$row_sto)
	{
		if(array_key_exists($studienplan_id, $studienplan_arr[$stoid]))
		{
			$studienplan_found=true;
			break;
		}
	}
	if(!$studienplan_found)
	{
		$studienplan_id='';
	}
}
// Studiensemester DropDown
echo "<SELECT name='studiensemester' onchange='window.location.href=this.value'>";

echo "<OPTION value='".$_SERVER['PHP_SELF']."?stg_kz=".$stg_kz."&studiensemester_kurzbz='>Alle Studiensemester</OPTION>";
foreach ($studiensemester->studiensemester as $row)
{
	if($row->studiensemester_kurzbz == $studiensemester_kurzbz)
		$selected='selected';
	else
		$selected='';

	if ($row->ende < date('Y-m-d'))
		$style = 'style="color: grey"';
	else
		$style = '';

	echo '<OPTION value="'.$_SERVER['PHP_SELF'].'?studiensemester_kurzbz='.$row->studiensemester_kurzbz.'&stg_kz='.$stg_kz.'&reihungstest_id='.$reihungstest_id.'" '.$selected.' '.$style.'>'.$db->convert_html_chars($row->studiensemester_kurzbz).'</OPTION>'.'\n';
}
echo "</SELECT>";

//Reihungstest DropDown
$reihungstest = new reihungstest();
if($stg_kz==-1 && $studiensemester_kurzbz=='')
	$reihungstest->getAll(date('Y').'-01-01'); //Alle Reihungstests ab diesem Jahr laden
elseif($stg_kz==-1 && $studiensemester_kurzbz!='')
	$reihungstest->getReihungstest('','datum DESC,uhrzeit DESC',$studiensemester_kurzbz);
else
	$reihungstest->getReihungstest($stg_kz,'datum DESC,uhrzeit DESC',$studiensemester_kurzbz);


echo "<SELECT name='reihungstest' id='reihungstest' onchange='window.location.href=this.value'>";
foreach ($reihungstest->result as $row)
{
	//if($reihungstest_id=='')
	//	$reihungstest_id=$row->reihungstest_id;
	if($row->reihungstest_id==$reihungstest_id)
		$selected='selected';
	elseif ($selected=='' && $reihungstest_id=='' && $row->datum==date('Y-m-d'))
		$selected='selected';
	else
		$selected='';

	echo '<OPTION value="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&reihungstest_id='.$row->reihungstest_id.'&studiensemester_kurzbz='.$studiensemester_kurzbz.'" '.$selected.'>'.$db->convert_html_chars($row->datum.' '.$datum_obj->formatDatum($row->uhrzeit,'H:i').' '.$studiengang->kuerzel_arr[$row->studiengang_kz].' '.$row->ort_kurzbz.' '.$row->anmerkung).'</OPTION>';
	echo "\n";
}
echo '</SELECT>';
echo "<button onclick='window.location.href=document.getElementById(\"reihungstest\").value;'>Anzeigen</button>";
echo '&nbsp;&nbsp;<button onclick="window.location.href=\''.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&neu=true\'" >Neuen Termin anlegen</button>';
echo "</td></tr></table>";

if($reihungstest_id=='')
	$neu=true;
$reihungstest = new reihungstest();

if(!$neu)
{
	if(!$reihungstest->load($reihungstest_id))
		die('Reihungstest existiert nicht');
}
else
{
	if($stg_kz!=-1 && $stg_kz!='')
		$reihungstest->studiengang_kz = $stg_kz;
	$reihungstest_id='';
	$reihungstest->datum = date('Y-m-d');
	$reihungstest->uhrzeit = date('H:i:s');
	$reihungstest->anmeldefrist = date('Y-m-d', time() - 60 * 60 * 24);
}

$studienplaene_arr = array();
$studienplaene = new reihungstest();
$studienplaene->getStudienplaeneReihungstest($reihungstest->reihungstest_id);
foreach ($studienplaene->result AS $row)
{
	$studienplan = new studienplan();
	$studienplan->loadStudienplan($row->studienplan_id);
	$studienplaene_arr[ $row->studienplan_id] = $studienplan->bezeichnung;
}

$studienplaene_list = implode(',', array_keys($studienplaene_arr));

//Formular zum Bearbeiten des Reihungstests
?>
<hr style="border: 1px solid lightgrey">
<div id="bearbeitenForm">
<form id='rt_form' method='POST' action='<?php echo $_SERVER['PHP_SELF'] ?>'>
<input type='hidden' value='<?php echo $reihungstest->reihungstest_id ?>' name='reihungstest_id' />

	<table>
		<tr>
		<td style="vertical-align: top">
		<table>
		<tr>
			<td class="feldtitel">Studiengang</td>
			<td>
				<select id='studiengang_dropdown' name='studiengang_kz' onchange='LoadStudienplan()'>
				<?php if($reihungstest->studiengang_kz)
						$selected = '';
					else
						$selected = 'selected'; ?>

				<option value='' <?php echo $selected ?>>-- keine Auswahl --</option>
					<?php foreach ($studiengang->result as $row)
					{
						if($row->studiengang_kz==$reihungstest->studiengang_kz)
							$selected = 'selected';
						else
							$selected = ''; ?>

						<option value="<?php echo $row->studiengang_kz ?>" <?php echo $selected ?>><?php echo $db->convert_html_chars($row->kuerzel).' ('.$db->convert_html_chars($row->bezeichnung) . ')' ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="feldtitel">Stufe</td>
			<td>
				<select name='stufe'>
				<option value=''>-- keine Auswahl --</option>
					<?php 
							// An der FHTW wird eine Beschreibung neben der Stufe angezeigt
							if (defined('DOMAIN') && DOMAIN == 'technikum-wien.at')
							{
								echo '<option value="1" '.($reihungstest->stufe == 1 ? 'selected' : '').'>1 (Computertest)</option>';
								echo '<option value="2" '.($reihungstest->stufe == 2 ? 'selected' : '').'>2 (Interview)</option>';
								echo '<option value="3" '.($reihungstest->stufe == 3 ? 'selected' : '').'>3</option>';
							}
							else
							{
								echo '<option value="1" '.($reihungstest->stufe == 1 ? 'selected' : '').'>1</option>';
								echo '<option value="2" '.($reihungstest->stufe == 2 ? 'selected' : '').'>2</option>';
								echo '<option value="3" '.($reihungstest->stufe == 3 ? 'selected' : '').'>3</option>';
							}
					?>
				</select>
				&nbsp;&nbsp;Studiensemester
				<select id='studiensemester_dropdown' name='studiensemester_kurzbz'>
				<option value=''>-- keine Auswahl --</option>
					<?php

						$stsem_zukuenftig = new Studiensemester();
						if($neu)
							$stsem_zukuenftig->getPlusMinus(5,1);
						else
							$stsem_zukuenftig->getAll();

						foreach ($stsem_zukuenftig->studiensemester as $row)
						{
							if((!$neu && $row->studiensemester_kurzbz == $reihungstest->studiensemester_kurzbz)
							   ||
							   ($neu && $row->studiensemester_kurzbz == $studiensemester_kurzbz))
								$selected='selected="selected"';
							else
								$selected='';

							echo '<OPTION value="'.$row->studiensemester_kurzbz.'" '.$selected.'>'.$db->convert_html_chars($row->studiensemester_kurzbz).'</OPTION>'."\n";
						}
					?>
				</select>
			</td>
		</tr>
		<?php
		if(!defined('FAS_REIHUNGSTEST_AUFNAHMEGRUPPEN') || FAS_REIHUNGSTEST_AUFNAHMEGRUPPEN==true)
		{
			echo '
			<tr>
				<td class="feldtitel">Gruppe</td>
				<td>
					<select name="aufnahmegruppe">
					<option value="">-- keine Auswahl --</option>
					';
					$gruppen_obj = new gruppe();
					$gruppen_obj->getAufnahmegruppen();
					foreach($gruppen_obj->result as $row)
					{
						if($reihungstest->aufnahmegruppe_kurzbz==$row->gruppe_kurzbz)
							$selected = 'selected="selected"';
						else
							$selected = '';

						echo '<option value="'.$db->convert_html_chars($row->gruppe_kurzbz).'" '.$selected.'>'.
								$db->convert_html_chars($row->bezeichnung).
							'</option>';
					}
				echo '
						</select>
					</td>
				</tr>';
		}

		$stg_rechtecheck = new studiengang($reihungstest->studiengang_kz);
		if($neu)
		{
			echo '<tr>';
			echo '<td class="feldtitel">Studienplan</td>';
			echo '<td>';

			// Studienplan DropDown
			echo "<SELECT id='studienplan_dropdown' name='studienplan_id'>";

			echo "<OPTION value=''>Studienplan auswaehlen</OPTION>";
			// Pruefen ob uebergebene StudienplanID in Auswahl enthalten
			// ist und ggf auf leer setzen
			if($studienplan_id!='')
			{
				$studienplan_found=false;
				foreach($studienplan_arr as $stoid=>$row_sto)
				{
					if(array_key_exists($studienplan_id, $studienplan_arr[$stoid]))
					{
						$studienplan_found=true;
						break;
					}
				}
				if(!$studienplan_found)
				{
					$studienplan_id='';
				}
			}
			foreach($studienordnung_arr as $stoid=>$row_sto)
			{
				$selected='';

				echo '<option value="" disabled>Studienordnung: '.$db->convert_html_chars($row_sto['bezeichnung']).'</option>';

				foreach ($studienplan_arr[$stoid] as $stpid=>$row_stp)
				{
					$selected='';
					if($studienplan_id=='')
						$studienplan_id=$stpid;
					if($stpid == $studienplan_id)
						$selected='selected';

					echo '<option value="'.$stpid.'" '.$selected.'>'.$db->convert_html_chars($row_stp['bezeichnung']).' ('.$orgform_arr[$row_stp['orgform_kurzbz']].', '.$row_stp['sprache'].') </option>';
				}
			}
			echo "</SELECT>";
			echo '</td></tr>';
		}
		else
		{
			echo '<tr><td class="feldtitel">Studienpläne</td>';

			if(!$neu)
			{
				//echo '<td><table>';
				echo '<td>';
				if ($rechte->isBerechtigt('lehre/reihungstest', $stg_rechtecheck->oe_kurzbz, 'sui'))
				{
					echo '<input id="studienplan_id" type="hidden" name="studienplan_id" value="">';
					echo '<input id="studienplan_autocomplete" type="text" name="studienplan" size="50" placeholder="Weiterer Studienplan" value="">';
					echo '<button type="submit" name="speichern"><img src="../../skin/images/list-add.png" alt="Studienplan hinzufügen" height="13px"></button>';
				}
				else
				{
					echo '<input id="studienplan_autocomplete" type="text" name="studienplan" size="50" placeholder="Keine Berechtigung zum Zuteilen von Studienplänen" value="" disabled>';
				}
				echo '</td>';
				echo '</tr>';

				foreach ($studienplaene->result AS $row)
				{
					$studienplan = new studienplan();
					$studienplan->loadStudienplan($row->studienplan_id);

					echo '<tr><td>&nbsp;</td>';
					echo '<td class="studienplan_listitem">'.$studienplan->bezeichnung.' ('.$studienplan->studienplan_id.')</td>';
					if ($rechte->isBerechtigt('lehre/reihungstest', $stg_rechtecheck->oe_kurzbz, 'suid'))
					{
						echo '<td><button type="submit" name="delete_studienplan" value="'.$row->studienplan_id.'"><img src="../../skin/images/delete_x.png" alt="Studienplan entfernen" height="13px"></button></td>';
					}
					echo '</tr>';
				}
				//echo '</table></td>';
			}
			else
				echo '<td colspan="2">Nach dem Anlegen eines Termins, können Sie weitere Studienpläne zuordnen</td>';
		}

		$arbeitsplaetze_sum = 0;
		if(!$neu)
		{
			echo '<tr><td class="feldtitel">Ort</td>';
			//echo '<td>';

			if ($rechte->isBerechtigt('lehre/reihungstestOrt', $stg_rechtecheck->oe_kurzbz, 'sui'))
			{
				echo '<td><input id="ort" type="text" name="ort_kurzbz" placeholder="Ort eingeben" value="">';
				echo '<button type="submit" name="speichern"><img src="../../skin/images/list-add.png" alt="Ort hinzufügen" height="13px"></button></td>';
				echo '</td></tr>';
			}
			else
			{
				echo '<td colspan="2"><input id="ort" type="text" name="ort_kurzbz" size="50" placeholder="Keine Berechtigung zum Zuteilen von Räumen" value="" disabled></td>';
			}
			$orte = new Reihungstest();
			$orte->getOrteReihungstest($reihungstest->reihungstest_id);
			foreach ($orte->result AS $row)
			{
				$person = new Person();
				$person->getPersonFromBenutzer($row->uid);
				if ($row->uid != '')
					$anzeigename = $person->vorname.' '.$person->nachname.' ('.$row->uid.')';
				else
					$anzeigename = '';

				echo '<tr><td>&nbsp;</td>';
				echo '<td class="ort_listitem">'.$row->ort_kurzbz.' ('.$orte_array[$row->ort_kurzbz].' Arbeitsplätze';
				if(defined('REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND') && REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND > 0)
					echo '*';
				echo ')';
				if ($rechte->isBerechtigt('lehre/reihungstestOrt', $stg_rechtecheck->oe_kurzbz, 'sui'))
				{
					echo ' <input type="text" id="aufsicht_'.$row->ort_kurzbz.'" class="aufsicht_uid" name="aufsicht['.$row->ort_kurzbz.']" value="'.$anzeigename.'" placeholder="Aufsichtsperson" size="32">';
				}
				if ($rechte->isBerechtigt('lehre/reihungstestOrt',  $stg_rechtecheck->oe_kurzbz, 'suid'))
				{
					echo '</td><td><button type="submit" name="delete_ort" value="'.$row->ort_kurzbz.'"><img src="../../skin/images/delete_x.png" alt="Ort hinzufügen" height="13px"></button>';
				}
				echo '</td></tr>';
				$arbeitsplaetze_sum = $arbeitsplaetze_sum + $orte_array[$row->ort_kurzbz];
			}
			if(defined('REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND') && REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND > 0)
				echo '<tr><td>&nbsp;</td><td>* Inklusive '.REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND.'% Schwund</td></tr>';
			//echo '</table></td>';
		}
		else
		{
			echo '<tr><td class="feldtitel">Ort</td>';
			echo '<td colspan="2">Nach dem Anlegen eines Termins, können Sie Räume zuordnen</td></tr>';
		}
		?>
		</table>
		</td>
		<td style="padding-left: 20px; vertical-align: top">
		<table>
		<tr>
			<td class="feldtitel">Anmerkung (intern)</td>
			<td><input type="text" size="64" maxlength="64" name="anmerkung" value="<?php echo $db->convert_html_chars($reihungstest->anmerkung) ?>"> (max. 64 Zeichen)</td>
		</tr>
		<tr>
			<td class="feldtitel">Datum</td>
			<td><input id="rt_datum" class="datepicker_datum" type="text" name="datum" value="<?php echo $datum_obj->convertISODate($reihungstest->datum) ?>"></td>
		</tr>
		<tr>
			<td class="feldtitel">Uhrzeit</td>
			<td><input type="text" class="timepicker" name="uhrzeit" value="<?php echo $db->convert_html_chars($datum_obj->formatDatum($reihungstest->uhrzeit,'H:i')) ?>" placeholder="HH:MM"> (Format: HH:MM)</td>
		</tr>
		<tr>
			<td class="feldtitel">Anmeldefrist (inkl.)</td>
			<td>
				<input id="rt_anmeldefrist" class="datepicker_datum" type="text" name="anmeldefrist" value="<?php echo $datum_obj->convertISODate($reihungstest->anmeldefrist) ?>">
				<span id="anmeldefristInfoDiv" style="color: red;"></span>
			</td>
		</tr>
		<tr>
			<td class="feldtitel">Max TeilnehmerInnen</td>
			<td>
				<input type="number" name="max_teilnehmer" id="max_teilnehmer" value="<?php echo ($reihungstest->max_teilnehmer!=''?$reihungstest->max_teilnehmer:'') ?>">
				(optional; <?php echo $arbeitsplaetze_sum; ?> laut Raumkapazität
				<?php
					if(defined('REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND') && REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND > 0)
						echo ' inklusive '.REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND.'% Schwund)';
					else
						echo ')';
				?>
			</td>
		</tr>
		<tr <?php echo ($rechte->isBerechtigt('lehre/reihungstestOeffentlich', $stg_rechtecheck->oe_kurzbz, 'suid') ? '' : 'style="display:none"') ?>>
			<td class="feldtitel">Öffentlich</td>
			<td>
				<input id="oeffentlich" type="checkbox" name="oeffentlich" value="1" <?php echo $reihungstest->oeffentlich ? 'checked="checked"' : '' ?>>
				(Für Bewerber sichtbar/auswählbar)
				<span id="messageDiv" style="color: red;"></span>
			</td>
		</tr>
		<tr>
			<td class="feldtitel">Freigeschaltet</td>
			<td>
				<input type="checkbox" name="freigeschaltet"<?php echo $reihungstest->freigeschaltet ? 'checked="checked"' : '' ?>>
				(Kurz vor Testbeginn aktivieren)
			</td>
		</tr>
		<tr>
			<td class="feldtitel">Zugangsüberprüfung</td>
			<td>
				<input type="checkbox" id="zugangs_ueberpruefung" name="zugangs_ueberpruefung"<?php echo $reihungstest->zugangs_ueberpruefung ? 'checked="checked"' : '' ?>>
			</td>
		</tr>
		<tr>
			<td class="feldtitel">Zugangscode</td>
			<td>
				<input type="number" class="input" id="zugangcode" name="zugangcode" value="<?php echo $db->convert_html_chars($reihungstest->zugangscode) ?>"> (Verpflichtend, wenn die Zugangsüberprüfung aktiviert ist)
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<?php 	
					if(!$neu)
					{
						if($rechte->isBerechtigt('lehre/reihungstest',  $stg_rechtecheck->oe_kurzbz, 'sui'))
						{
							echo '<button type="submit" name="speichern">Änderung Speichern</button>';
							echo '<button type="submit" name="kopieren" onclick="return confirm (\'Eine Kopie dieses Termins erstellen?\')">Kopie erstellen</button>';
						}
					}
					else
					{
						echo '<button type="submit" name="speichern">Neu anlegen</button>';
					}
					
					if($rechte->isBerechtigt('lehre/reihungstest',  $stg_rechtecheck->oe_kurzbz, 'suid'))
					{
						$anzahl_teilnehmer = new reihungstest();
						$anzahl_teilnehmer = $anzahl_teilnehmer->getTeilnehmerAnzahl($reihungstest_id);

						if (isset($orte) && count($orte->result) == 0 && isset($studienplaene) && count($studienplaene->result) == 0 && $anzahl_teilnehmer == 0 && $reihungstest_id != '')
							echo '<button type="submit" name="deleteReihungstest" onclick="return confirm (\'Diesen Reihungstesttermin löschen?\')">Termin löschen</button>';
						else
							echo '<button type="submit" name="" disabled="disabled" title="Entfernen Sie zuerst alle Raumzuteilungen, Studienpläne und TeilnehmerInnen">Termin löschen</button>';
					}
				?>
			</td>
		</tr>
		</table>
		</td>
		</tr>
	</table>
</form>
</div>
<br>
<div style="width: 100%; text-align: center">
	<div id="toggle_bearbeitenForm" style="width: 100px; text-align: center; background-color: #e8e8e8; border: 1px solid lightgrey; display: inline; padding: 3px 3px 0 3px; color: grey; border-radius: 3px 3px 0 0;">Einklappen</div>
</div>
<hr style="margin-top: 0; border: 1px solid lightgrey;">
<?php
echo '<table width="100%"><tr><td>';
if($reihungstest_id!='')
{
	echo '<a class="buttongreen" href="'.$_SERVER['PHP_SELF'].'?reihungstest_id='.$reihungstest_id.'&excel=true">Excel Export</a>';
	//echo '<a class="buttongreen" href="'.$_SERVER['PHP_SELF'].'?reihungstest_id='.$reihungstest_id.'&type=saveallrtpunkte">Punkte ins FAS &uuml;bertragen</a>';
	echo '<a class="buttongreen" href="#" onclick="SendMail()" id="mailSendButton">Mail an alle BewerberInnen senden</a>';
	echo '<form id="sendMsgForm" method="post" action="'. APP_ROOT .'index.ci.php/system/messages/FASMessages/writeTemplate" target="_blank" style="display:inline-block">
			<a class="buttongreen" href="javascript:void(0)" onclick="SendMessage()" id="msgSendButton">Message an alle BewerberInnen senden</a>
			</form>';
}
if (defined('CAMPUS_NAME') && CAMPUS_NAME == 'FH Technikum Wien')
{
	echo '<a class="buttongreen" href="'.APP_ROOT.'vilesci/stammdaten/auswertung_fhtw.php?'.($reihungstest_id!=''?"reihungstest=$reihungstest_id":'').'" target="_blank">Auswertung</a>';
}
else
{
	echo '<a class="buttongreen" href="../../cis/testtool/admin/auswertung.php?'.($reihungstest_id!=''?"reihungstest=$reihungstest_id":'').'" target="_blank">Auswertung</a>';
}

echo '<a class="buttonorange" href="reihungstest_zusammenlegung.php">Anmeldungen zusammenlegen</a>';
if($rechte->isBerechtigt('basis/testtool', null, 'suid'))
{
	echo '<a class="buttonorange" href="reihungstest_administration.php">Administration</a>';
}

echo '</td></tr>';
echo '</td></tr></table>';
if($reihungstest_id!='')
{
	//Liste der Interessenten die zum Reihungstest angemeldet sind
	$qry = "
	SELECT DISTINCT rt_person_id,
		rt_id,
		prestudent_id,
		tbl_rt_person.person_id,
		vorname,
		nachname,
		tbl_rt_person.ort_kurzbz,
		tbl_rt_person.studienplan_id,
		tbl_prestudent.studiengang_kz,
		gebdatum,
		geschlecht,
		punkte,
		(
			SELECT kontakt
			FROM tbl_kontakt
			WHERE kontakttyp = 'email'
				AND person_id = tbl_rt_person.person_id
				AND zustellung = true LIMIT 1
			) AS email,
		(
			SELECT ausbildungssemester
			FROM PUBLIC.tbl_prestudentstatus
			WHERE prestudent_id = tbl_prestudent.prestudent_id
				AND datum = (
					SELECT MAX(datum)
					FROM PUBLIC.tbl_prestudentstatus
					WHERE prestudent_id = tbl_prestudent.prestudent_id
						AND status_kurzbz = 'Interessent'
					) LIMIT 1
			) AS ausbildungssemester,
		(
			SELECT orgform_kurzbz
			FROM PUBLIC.tbl_prestudentstatus
			WHERE prestudent_id = tbl_prestudent.prestudent_id
				AND datum = (
					SELECT MAX(datum)
					FROM PUBLIC.tbl_prestudentstatus
					WHERE prestudent_id = tbl_prestudent.prestudent_id
						AND status_kurzbz = 'Interessent'
					) LIMIT 1
			) AS orgform_kurzbz
	FROM PUBLIC.tbl_rt_person
	JOIN PUBLIC.tbl_person USING (person_id)
	JOIN PUBLIC.tbl_reihungstest rt ON (rt_id = rt.reihungstest_id)
	JOIN PUBLIC.tbl_prestudent ON (tbl_rt_person.person_id = tbl_prestudent.person_id)
	JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
	WHERE rt_id = ".$db->db_add_param($reihungstest_id, FHC_INTEGER)."
		AND tbl_rt_person.studienplan_id IN (
			SELECT studienplan_id
			FROM PUBLIC.tbl_prestudentstatus
			WHERE prestudent_id = tbl_prestudent.prestudent_id
			)
		AND tbl_prestudentstatus.studiensemester_kurzbz IN (
			SELECT studiensemester_kurzbz
			FROM PUBLIC.tbl_studiensemester
			WHERE studiensemester_kurzbz = rt.studiensemester_kurzbz
			
			UNION
			
			(
				SELECT studiensemester_kurzbz
				FROM PUBLIC.tbl_studiensemester
				WHERE ende <= (
						SELECT start
						FROM PUBLIC.tbl_studiensemester
						WHERE studiensemester_kurzbz = rt.studiensemester_kurzbz
						)
				ORDER BY ende DESC LIMIT 1
				)
			
			UNION
			
			(
				SELECT studiensemester_kurzbz
				FROM PUBLIC.tbl_studiensemester
				WHERE start >= (
						SELECT ende
						FROM PUBLIC.tbl_studiensemester
						WHERE studiensemester_kurzbz = rt.studiensemester_kurzbz
						)
				ORDER BY start ASC LIMIT 1
				)
			)
	ORDER BY ort_kurzbz NULLS FIRST,
		nachname,
		vorname";

	$mailto = '';
	$result_arr = array();

	$orte = new Reihungstest();
	$orte->getOrteReihungstest($reihungstest_id);
	$orte_zuteilung_array = array();
	$orte_zuteilung_array['ohne'] = 0;
	foreach ($orte->result AS $row)
		$orte_zuteilung_array[$row->ort_kurzbz] = 0;

	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$result_arr[] = $row;

			if (is_null($row->ort_kurzbz))
				$orte_zuteilung_array['ohne']++;
			else
				$orte_zuteilung_array[$row->ort_kurzbz]++;
		}
	}

	echo '<table width="100%"><tr><td>';
	echo '<tr><td>';
	echo '<span style="font-size: 9pt">Anzahl: '.$db->db_num_rows($result).' ('.($reihungstest->max_teilnehmer!=''?$reihungstest->max_teilnehmer:$arbeitsplaetze_sum).' Plätze verfügbar)</span>';
	if (	($reihungstest->max_teilnehmer!='' && $db->db_num_rows($result) > $reihungstest->max_teilnehmer)
			|| ($reihungstest->max_teilnehmer=='' && $db->db_num_rows($result) > $arbeitsplaetze_sum)
			&& !empty($orte_array)
		)
		echo '<br><span style="color: red"><b>Achtung!</b> Anzahl Arbeitsplätze überschritten</span>';
	echo '</td></tr>';
	echo '<tr><td>';
	echo '<div id="clm_prestudent_id" class="active" onclick="hideColumn(\'clm_prestudent_id\')">Prestudent ID</div>';
	echo '<div id="clm_person_id" class="active" onclick="hideColumn(\'clm_person_id\')">Person ID</div>';
	echo '<div id="clm_vorname" class="active" onclick="hideColumn(\'clm_vorname\')">Vorname</div>';
	echo '<div id="clm_geschlecht" class="active" onclick="hideColumn(\'clm_geschlecht\')">Geschlecht</div>';
	echo '<div id="clm_studiengang" class="active" onclick="hideColumn(\'clm_studiengang\')">Studiengang</div>';
	echo '<div id="clm_orgform" class="active" onclick="hideColumn(\'clm_orgform\')">OrgForm</div>';
	echo '<div id="clm_studienplan" class="active" onclick="hideColumn(\'clm_studienplan\')">Studienplan</div>';
	echo '<div id="clm_einstiegssemester" class="active" onclick="hideColumn(\'clm_einstiegssemester\')">Einstiegssemester</div>';
	echo '<div id="clm_geburtsdatum" class="active" onclick="hideColumn(\'clm_geburtsdatum\')">Geburtsdatum</div>';
	echo '<div id="clm_email" class="active" onclick="hideColumn(\'clm_email\')">EMail</div>';
	echo '<div id="clm_absolviert" class="active" onclick="hideColumn(\'clm_absolviert\')">Absolvierte Tests <span class="wait"></span></div>';
	//echo '<div id="clm_ergebnis" class="active" onclick="hideColumn(\'clm_ergebnis\')">Ergebnis <span class="wait"></span></div>';
	//echo '<div id="clm_fas" class="active" onclick="hideColumn(\'clm_fas\')">FAS</div>';
	echo '</td></tr></table>';
	echo '</div>';
	echo '<br>';


	$pruefling = new pruefling();

	$cnt = 0;

	//TABLE OHNE RAUMZUTEILUNG
	if ($orte_zuteilung_array['ohne']>0)
	{
		echo '<div style="vertical-align: top; display: inline-block; padding: 10px">';
		echo '<div style="text-align: center; padding: 0 0 5px 0"><b>Ohne Raumzuteilung ('.$orte_zuteilung_array['ohne'].')</b></div>';
		echo '<div align="center"><a class="buttonorange" href="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&reihungstest_id='.$reihungstest_id.'&studiensemester_kurzbz='.$studiensemester_kurzbz.'&type=verteilen" onclick="return confirm(\'BewerberInnen gleichmaeßig auf alle Raeume verteilen?\');">Gleichmäßig verteilen</a>';
		echo '<a class="buttonorange" href="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&reihungstest_id='.$reihungstest_id.'&studiensemester_kurzbz='.$studiensemester_kurzbz.'&type=auffuellen" onclick="return confirm(\'Die Räume werden ansteigend mit BewerbeInnen aufgefuellt\');">Auffüllen</a></div>';
		echo '<form id="raumzuteilung_form[ohne]" method="POST" action="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&reihungstest_id='.$reihungstest->reihungstest_id.'&studiensemester_kurzbz='.$studiensemester_kurzbz.'"">';
		echo '<input type="hidden" value="'.$reihungstest->reihungstest_id.'" name="reihungstest_id">';
		echo '<table class="tablesorter" id="t'.$cnt.'">
				<thead>
				<tr class="liste">
					<th style="text-align: center; width: 40px">
					<nobr>
						<a href="#" data-toggle="checkboxes" data-action="toggle" id="toggle_t'.$cnt.'"><img src="../../skin/images/checkbox_toggle.png" name="toggle"></a>
						<a href="#" data-toggle="checkboxes" data-action="uncheck" id="uncheck_t'.$cnt.'"><img src="../../skin/images/checkbox_uncheck.png" name="toggle"></a>
					</nobr>
					</th>
					<th style="display: table-cell" class="clm_prestudent_id" title="PrestudentID">Prestudent ID</th>
					<th style="display: table-cell" class="clm_person_id" title="PersonID">Person ID</th>
					<th>Nachname</th>
					<th style="display: table-cell" class="clm_vorname">Vorname</th>
					<th style="display: table-cell" class="clm_geschlecht">Geschlecht</th>
					<th style="display: table-cell" class="clm_studiengang">Studiengang</th>
 					<th style="display: table-cell" class="clm_orgform">OrgForm</th>
					<th style="display: table-cell" class="clm_studienplan">Studienplan</th>
					<th style="display: table-cell" class="clm_einstiegssemester">Einstiegssemester</th>
					<th style="display: table-cell" class="clm_geburtsdatum">Geburtsdatum</th>
					<th style="display: table-cell" class="clm_email">EMail</th>
					<th style="display: table-cell" class="clm_absolviert">bereits absolvierte Verfahren</th>
					<!--<th style="display: table-cell" class="clm_ergebnis">Ergebnis</th>
					<th style="display: table-cell" class="clm_fas">FAS</th>-->
				</tr>
				</thead>
				<tbody>';

		foreach ($result_arr AS $row)
		{
					$rt_in_anderen_stg='';
					$rtergebnis = '';
					$rt_prestudent_arr = array();

					if($punkteberechnung == 'true')
					{
					  //Daten für Spalte bereits absolvierte Verfahren
					  $qry = "	SELECT DISTINCT tbl_reihungstest.reihungstest_id,
									tbl_pruefling.pruefling_id,
									tbl_prestudent.prestudent_id,
									tbl_rt_person.person_id
								FROM PUBLIC.tbl_rt_person
								JOIN lehre.tbl_studienplan USING (studienplan_id)
								JOIN lehre.tbl_studienordnung USING (studienordnung_id)
								JOIN PUBLIC.tbl_prestudent USING (person_id)
								JOIN PUBLIC.tbl_prestudentstatus USING (
										studienplan_id,
										prestudent_id
										)
								JOIN PUBLIC.tbl_reihungstest ON (tbl_reihungstest.reihungstest_id = tbl_rt_person.rt_id)
								LEFT JOIN testtool.tbl_pruefling using (prestudent_id)
								WHERE (
										tbl_rt_person.anmeldedatum IS NULL
										OR tbl_rt_person.anmeldedatum <= tbl_reihungstest.datum
										)
									AND tbl_reihungstest.datum >= (
										SELECT min(begintime)::DATE
										FROM testtool.tbl_pruefling_frage
										WHERE pruefling_id = tbl_pruefling.pruefling_id
											AND tbl_reihungstest.datum >= begintime - '1 days'::interval
										)
									AND (
										tbl_reihungstest.stufe IS NULL
										OR tbl_reihungstest.stufe = 1
										)
									AND person_id = ".$db->db_add_param($row->person_id, FHC_INTEGER);

						if($result = $db->db_query($qry))
						{
							while($obj = $db->db_fetch_object($result))
							{
								array_push($rt_prestudent_arr, $obj);
							}
						}

						//Ergebnis ermitteln
						if(defined('FAS_REIHUNGSTEST_PUNKTE') && FAS_REIHUNGSTEST_PUNKTE)
								$rtergebnis = $pruefling->getReihungstestErgebnisPrestudent($row->prestudent_id, true, $reihungstest->reihungstest_id);
						else
								$rtergebnis = $pruefling->getReihungstestErgebnisPrestudent($row->prestudent_id, false, $reihungstest->reihungstest_id);


						//Ausgabe für bereits absolvierte Verfahren
						foreach($rt_prestudent_arr as $item)
						{
							$pruefling->getPruefling($item->prestudent_id);
							$rt = new Reihungstest();
							$rt->load($item->reihungstest_id);
							$rt_letztes_login = $datum_obj->formatDatum($pruefling->registriert, 'Y-m-d');
							$rt_antrittstermin = $datum_obj->formatDatum($rt->datum, 'Y-m-d');

						  //Wenn bereits absolvierte Verfahren vorhanden
							if($item->prestudent_id != $row->prestudent_id || $rt_letztes_login < $rt_antrittstermin)
							{
								if(defined('FAS_REIHUNGSTEST_PUNKTE') && FAS_REIHUNGSTEST_PUNKTE)
									$erg = $pruefling->getReihungstestErgebnisPrestudent($item->prestudent_id, true, $item->reihungstest_id);
								else
									$erg = $pruefling->getReihungstestErgebnisPrestudent($item->prestudent_id, false, $item->reihungstest_id);

								if($erg !== false)
								{
									$rt_in_anderen_stg .= number_format($erg, 2).((FAS_REIHUNGSTEST_PUNKTE) ? ' Punkte' : ' %').' im Studiengang '.$studiengang->kuerzel_arr[$pruefling->studiengang_kz].'<br>';

									if ($item->prestudent_id == $row->prestudent_id && $rt_letztes_login < $rt_antrittstermin)
									{
										$rt_in_anderen_stg .= '(Letzter '.$studiengang->kuerzel_arr[$pruefling->studiengang_kz].'-Antritt: '.$datum_obj->formatDatum($rt_letztes_login, 'd.m.Y');
										if($rechte->isBerechtigt('basis/testtool', null, 'suid'))
											$rt_in_anderen_stg .= ',<br><a href="reihungstest_administration.php">absolvierte RT-Gebiete entsperren</a>';
										$rt_in_anderen_stg .= ')<br>';
									}
								}
							}
						}
					}

					if ($row->ort_kurzbz == '')
					{
							if(isset($studienplaene_arr[$row->studienplan_id]))
									$studienplan_bezeichnung = $studienplaene_arr[$row->studienplan_id];
							else
							{
									$studienplan_obj = new studienplan();
									$studienplan_obj->loadStudienplan($row->studienplan_id);
									$studienplan_bezeichnung = $studienplan_obj->bezeichnung;
									$studienplaene_arr[$row->studienplan_id]=$studienplan_obj->bezeichnung;
							}
							echo '
									<tr>
										<td style="text-align: center"><input type="checkbox" class="chkbox" id="checkbox_'.$row->person_id.'" name="checkbox['.$row->person_id.']"></td>
										<td style="display: table-cell" class="clm_prestudent_id">'.$db->convert_html_chars($row->prestudent_id).'</td>
										<td style="display: table-cell" class="clm_person_id">'.$db->convert_html_chars($row->person_id).'</td>
										<td>'.$db->convert_html_chars($row->nachname).'</td>
										<td style="display: table-cell" class="clm_vorname">'.$db->convert_html_chars($row->vorname).'</td>
										<td style="display: table-cell" class="clm_geschlecht">'.$db->convert_html_chars($row->geschlecht).'</td>
										<td style="display: table-cell" class="clm_studiengang">'.$db->convert_html_chars($stg_arr[$row->studiengang_kz]).'</td>
										<td style="display: table-cell" class="clm_orgform">'.$db->convert_html_chars($row->orgform_kurzbz!=''?$row->orgform_kurzbz:' ').'</td>
										<td style="display: table-cell" class="clm_studienplan">'.$db->convert_html_chars($studienplan_bezeichnung).' ('.$row->studienplan_id.')</td>
										<td style="display: table-cell" class="clm_einstiegssemester">'.$db->convert_html_chars($row->ausbildungssemester).'</td>
										<td style="display: table-cell" class="clm_geburtsdatum">'.$db->convert_html_chars($row->gebdatum!=''?$datum_obj->convertISODate($row->gebdatum):' ').'</td>
										<td style="display: table-cell; text-align: center" class="clm_email"><a href="mailto:'.$db->convert_html_chars($row->email).'"><img src="../../skin/images/button_mail.gif" name="mail"></a></td>
										<td style="display: table-cell;" class="clm_absolviert">'.$rt_in_anderen_stg.'</td>
									</tr>';

							$mailto.= ($mailto!=''?',':'').$row->email;
					}
		}
		echo '</tbody></table>';

		echo '<select name="raumzuteilung">';
		echo '<option value="">Ohne Zuteilung</option>';
		foreach ($orte->result AS $item)
		{
			if($item->ort_kurzbz=='')
				$selected = 'selected="selected"';
			else
				$selected = '';
			echo '<option value="'.$item->ort_kurzbz.'" '.$selected.'>'.$item->ort_kurzbz.'</option>';
		}
		echo '</select>';
		echo '<button type="submit" name="raumzuteilung_speichern">Speichern</button>';

		echo '</form>';
		echo '</div>';

		//echo '<br>';
	}

		foreach ($orte->result AS $ort)
		{
			$cnt++;
			if ($orte_array[$ort->ort_kurzbz] - $orte_zuteilung_array[$ort->ort_kurzbz] < 0)
				$style = 'text-align: center; margin: 0 5px 0 5px; color: red';
			else
				$style = 'text-align: center; margin: 0 5px 0 5px;';

			//TABLE MIT RAUMZUTEILUNG
			if ($orte_zuteilung_array[$ort->ort_kurzbz] > 0)
			{
				echo '<div style="vertical-align: top; display: inline-block; padding: 10px">';
				echo '<div style="'.$style.'"><b>Mit Raumzuteilung in '.$ort->ort_kurzbz.' ('.$orte_zuteilung_array[$ort->ort_kurzbz].'/'.$orte_array[$ort->ort_kurzbz].')</b></div>';
				echo '<div align="center" style="height: 35px"></div>';
				echo '<form id="raumzuteilung_form['.$ort->ort_kurzbz.']" method="POST" action="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&reihungstest_id='.$reihungstest->reihungstest_id.'&studiensemester_kurzbz='.$studiensemester_kurzbz.'">';
				echo '<input type="hidden" value="'.$reihungstest->reihungstest_id.'" name="reihungstest_id">';
				echo '<table class="tablesorter" id="t'.$cnt.'">
					<thead>
						<tr class="liste">
							<th style="text-align: center; width: 40px">
							<nobr>
									<a href="#" data-toggle="checkboxes" data-action="toggle" id="toggle_t'.$cnt.'"><img src="../../skin/images/checkbox_toggle.png" name="toggle"></a>
									<a href="#" data-toggle="checkboxes" data-action="uncheck" id="uncheck_t'.$cnt.'"><img src="../../skin/images/checkbox_uncheck.png" name="toggle"></a>
							</nobr>
							</th>
							<th style="display: table-cell" class="clm_prestudent_id" title="PrestudentID">Prestudent ID</th>
							<th style="display: table-cell" class="clm_person_id" title="PersonID">Person ID</th>
							<th>Nachname</th>
							<th style="display: table-cell" class="clm_vorname">Vorname</th>
							<th style="display: table-cell" class="clm_geschlecht">Geschlecht</th>
							<th style="display: table-cell" class="clm_studiengang">Studiengang</th>
							<th style="display: table-cell" class="clm_orgform">OrgForm</th>
							<th style="display: table-cell" class="clm_studienplan">Studienplan</th>
							<th style="display: table-cell" class="clm_einstiegssemester">Einstiegssemester</th>
							<th style="display: table-cell" class="clm_geburtsdatum">Geburtsdatum</th>
							<th style="display: table-cell" class="clm_email">EMail</th>
							<th style="display: table-cell" class="clm_absolviert">bereits absolvierte Verfahren</th>
							<!--<th style="display: table-cell" class="clm_ergebnis">Ergebnis</th>
							<th style="display: table-cell" class="clm_fas">FAS</th>-->
						</tr>
					</thead>
				<tbody>';

				$cnt_personen = 0;

				foreach ($result_arr AS $row)
				{
					$rt_in_anderen_stg='';
					$rtergebnis = '';
					$rt_prestudent_arr = array();

					if($punkteberechnung == 'true')
					{
						//Daten für Spalte bereits absolvierte Verfahren
						$qry = "SELECT DISTINCT tbl_reihungstest.reihungstest_id,
									tbl_pruefling.pruefling_id,
									tbl_prestudent.prestudent_id,
									tbl_rt_person.person_id
								FROM PUBLIC.tbl_rt_person
								JOIN lehre.tbl_studienplan USING (studienplan_id)
								JOIN lehre.tbl_studienordnung USING (studienordnung_id)
								JOIN PUBLIC.tbl_prestudent USING (person_id)
								JOIN PUBLIC.tbl_prestudentstatus USING (
										studienplan_id,
										prestudent_id
										)
								JOIN PUBLIC.tbl_reihungstest ON (tbl_reihungstest.reihungstest_id = tbl_rt_person.rt_id)
								LEFT JOIN testtool.tbl_pruefling using (prestudent_id)
								WHERE (
										tbl_rt_person.anmeldedatum IS NULL
										OR tbl_rt_person.anmeldedatum <= tbl_reihungstest.datum
										)
									AND tbl_reihungstest.datum >= (
										SELECT min(begintime)::DATE
										FROM testtool.tbl_pruefling_frage
										WHERE pruefling_id = tbl_pruefling.pruefling_id
											AND tbl_reihungstest.datum >= begintime - '1 days'::interval
										)
									AND (
										tbl_reihungstest.stufe IS NULL
										OR tbl_reihungstest.stufe = 1
										)
									AND person_id = ".$db->db_add_param($row->person_id, FHC_INTEGER);

						if($result = $db->db_query($qry))
						{
							while($obj = $db->db_fetch_object($result))
							{
								array_push($rt_prestudent_arr, $obj);
							}
						}

						//Ergebnis ermitteln
						if(defined('FAS_REIHUNGSTEST_PUNKTE') && FAS_REIHUNGSTEST_PUNKTE)
							$rtergebnis = $pruefling->getReihungstestErgebnisPrestudent($row->prestudent_id, true, $reihungstest->reihungstest_id);
						else
							$rtergebnis = $pruefling->getReihungstestErgebnisPrestudent($row->prestudent_id, false, $reihungstest->reihungstest_id);

						 //Ausgabe für bereits absolvierte Verfahren
						foreach($rt_prestudent_arr as $item)
						{
							$pruefling->getPruefling($item->prestudent_id);
							$rt = new Reihungstest();
							$rt->load($item->reihungstest_id);
							$rt_letztes_login = $datum_obj->formatDatum($pruefling->registriert, 'Y-m-d');
							$rt_antrittstermin = $datum_obj->formatDatum($rt->datum, 'Y-m-d');

							//Wenn bereits absolvierte Verfahren vorhanden
							if($item->prestudent_id != $row->prestudent_id || $rt_letztes_login < $rt_antrittstermin)
							{
								if(defined('FAS_REIHUNGSTEST_PUNKTE') && FAS_REIHUNGSTEST_PUNKTE)
									$erg = $pruefling->getReihungstestErgebnisPrestudent($item->prestudent_id, true, $item->reihungstest_id);
								else
									$erg = $pruefling->getReihungstestErgebnisPrestudent($item->prestudent_id, false, $item->reihungstest_id);

								if($erg!==false)
								{
									$rt_in_anderen_stg .= number_format($erg, 2).((FAS_REIHUNGSTEST_PUNKTE) ? ' Punkte' : ' %').' im Studiengang '.$studiengang->kuerzel_arr[$pruefling->studiengang_kz].'<br>';

									if ($item->prestudent_id == $row->prestudent_id && $rt_letztes_login < $rt_antrittstermin)
									{
										$rt_in_anderen_stg .= '(Letzter '.$studiengang->kuerzel_arr[$pruefling->studiengang_kz].'-Antritt: '.$datum_obj->formatDatum($rt_letztes_login, 'd.m.Y').',<br>';
										$rt_in_anderen_stg .= '<a href="reihungstest_administration.php">absolvierte RT-Gebiete entsperren</a>)<br>';
									}
								}
							}
						}
					}
						if ($row->ort_kurzbz == $ort->ort_kurzbz)
						{
							if(isset($studienplaene_arr[$row->studienplan_id]))
								$studienplan_bezeichnung = $studienplaene_arr[$row->studienplan_id];
							else
							{
								$studienplan_obj = new studienplan();
								$studienplan_obj->loadStudienplan($row->studienplan_id);
								$studienplan_bezeichnung = $studienplan_obj->bezeichnung;
								$studienplaene_arr[$row->studienplan_id]=$studienplan_obj->bezeichnung;
							}

							$cnt_personen++;
							echo '
								<tr>
									<td style="text-align: center"><input class="chkbox" type="checkbox" id="checkbox_'.$row->person_id.'" name="checkbox['.$row->person_id.']"></td>
									<td style="display: table-cell" class="clm_prestudent_id">'.$db->convert_html_chars($row->prestudent_id).'</td>
									<td style="display: table-cell" class="clm_person_id">'.$db->convert_html_chars($row->person_id).'</td>
									<td>'.$db->convert_html_chars($row->nachname).'</td>
									<td style="display: table-cell" class="clm_vorname">'.$db->convert_html_chars($row->vorname).'</td>
									<td style="display: table-cell" class="clm_geschlecht">'.$db->convert_html_chars($row->geschlecht).'</td>
									<td style="display: table-cell" class="clm_studiengang">'.$db->convert_html_chars($stg_arr[$row->studiengang_kz]).'</td>
									<td style="display: table-cell" class="clm_orgform">'.$db->convert_html_chars($row->orgform_kurzbz!=''?$row->orgform_kurzbz:' ').'</td>
									<td style="display: table-cell" class="clm_studienplan">'.$db->convert_html_chars($studienplan_bezeichnung).' ('.$row->studienplan_id.')</td>
									<td style="display: table-cell" class="clm_einstiegssemester">'.$db->convert_html_chars($row->ausbildungssemester).'</td>
									<td style="display: table-cell" class="clm_geburtsdatum">'.$db->convert_html_chars($row->gebdatum!=''?$datum_obj->convertISODate($row->gebdatum):' ').'</td>
									<td style="display: table-cell; text-align: center" class="clm_email"><a href="mailto:'.$db->convert_html_chars($row->email).'"><img src="../../skin/images/button_mail.gif" name="mail"></a></td>
									<td style="display: table-cell;" class="clm_absolviert">'.$rt_in_anderen_stg.'</td>';
							/*echo	'<td style="display: table-cell; align: right" class="clm_ergebnis">'.($rtergebnis==0?'-':number_format($rtergebnis,2,'.','')).' %</td>
									<td style="display: table-cell; align: right" class="clm_fas">';
									if($rtergebnis!==false && $rtergebnis != '' && $row->punkte=='')
										echo '<a href="'.$_SERVER['PHP_SELF'].'?reihungstest_id='.$reihungstest_id.'&stg_kz='.$stg_kz.'&type=savertpunkte&rt_person_id='.$row->rt_person_id.'&rtpunkte='.$rtergebnis.'" >&uuml;bertragen</a>';
									else
									{
										if($row->punkte!='')
											echo number_format($row->punkte,2,'.','');
									}
									echo '</td>';*/
							echo '</tr>';

							$mailto.= ($mailto!=''?',':'').$row->email;
						}
				}
					echo '</tbody></table>';

					echo '<select name="raumzuteilung">';
					echo '<option value="">Zuteilung entfernen</option>';
					foreach ($orte->result AS $item)
					{
							if($item->ort_kurzbz==$ort->ort_kurzbz)
									$selected = 'selected="selected"';
							else
									$selected = '';
							echo '<option value="'.$item->ort_kurzbz.'" '.$selected.'>'.$item->ort_kurzbz.'</option>';
					}
					echo '</select>';
					echo '<button type="submit" name="raumzuteilung_speichern">Speichern</button>';

					echo '</form>';
					echo '</div>';
			}
		}
} 

/**
 * Liefert die interne Empfangsadresse des Studiengangs fuer den Mailversand.
 * Wenn BEWERBERTOOL_MAILEMPFANG gesetzt ist, wird diese genommen,
 * sonst diejenige aus BEWERBERTOOL_BEWERBUNG_EMPFAENGER,
 * sonst die Mailadresse des Studiengangs
 *
 * @param integer $studiengang_kz
 * @param integer $studienplan_id
 * @param string $orgform_kurzbz
 * @return string mit den Mailadressen sonst false
 */
function getMailEmpfaenger($studiengang_kz, $studienplan_id = null, $orgform_kurzbz = null)
{
	$studiengang = new studiengang($studiengang_kz);
	
	if ($studienplan_id != '')
	{
		$studienplan = new studienplan();
		$studienplan->loadStudienplan($studienplan_id);
	}
	
	$empf_array = array();
	$empfaenger = '';
	if(defined('BEWERBERTOOL_BEWERBUNG_EMPFAENGER'))
		$empf_array = unserialize(BEWERBERTOOL_BEWERBUNG_EMPFAENGER);
		
	// Umgehung für FHTW. Ausprogrammiert im Code
	if(defined('BEWERBERTOOL_MAILEMPFANG') && BEWERBERTOOL_MAILEMPFANG != '')
	{
		$empfaenger = BEWERBERTOOL_MAILEMPFANG;
	}
	elseif(isset($empf_array[$studiengang_kz]))
	{
		// Pfuschloesung, damit bei BIF Dual die Mail an info.bid geht
		if ($studiengang_kz == 257)
		{
			if ((isset($studienplan) && $studienplan->orgform_kurzbz == 'DUA') ||
				($orgform_kurzbz != '' && $orgform_kurzbz == 'DUA'))
				$empfaenger = 'info.bid@technikum-wien.at';
		}
		else
			$empfaenger = $empf_array[$studiengang_kz];
	}
	else
		$empfaenger = $studiengang->email;
		
	if ($empfaenger != '')
		return $empfaenger;
	else
		return false;
}

?>

	</body>
</html>
