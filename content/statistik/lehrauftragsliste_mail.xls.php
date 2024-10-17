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
/**
 * Erstellt ein Excel File mit einer Uebersicht der
 * Kosten fuer die Geschaeftsstelle und markiert die Zeilen die in den letzten
 * 31 Tagen veraendert wurden. Dieses File wirde dann per Mail versandt
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/Excel/excel.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/mail.class.php');
require_once('../../include/benutzerfunktion.class.php');
require_once('../../include/organisationseinheit.class.php');

$stsem = new studiensemester();

if (isset($_GET['stsem']))
{
	if (check_stsem($_GET['stsem']))
		$semester_aktuell = $_GET['stsem'];
	else
		die('Studiensemester ist ungueltig');
}
else
	$semester_aktuell = $stsem->getaktorNext();

//UID als Kommandozeilenparameter
if (isset($_SERVER['argv']) && isset($_SERVER['argv'][1]) && !strstr($_SERVER['argv'][1], '='))
{
	$oe_kurzbz = $_SERVER['argv'][1];
}
else
{
	if (isset($_GET['oe_kurzbz']))
		$oe_kurzbz = $_GET['oe_kurzbz'];
	else
	{
		$stg = new studiengang();
		$stg->load('0');
		$oe_kurzbz = $stg->oe_kurzbz;
	}
}

$file = 'lehrauftragsliste.xls';
$file = tempnam('/tmp', 'lehrauftragsliste_').'.xls';

// Creating a workbook
echo 'Lehrauftragslisten werden erstellt. Bitte warten!<BR>';
flush();
$workbook = new Spreadsheet_Excel_Writer($file);
$workbook->setVersion(8);
$db = new basis_db();

$stg = new studiengang();
$stg->getStudiengaengeFromOe($oe_kurzbz);
$stg_arr = array();
if (count($stg->result) > 0)
{
	foreach ($stg->result as $row)
	{
		$stg_arr[] = $row->studiengang_kz;
	}
}
//Studiengaenge ermitteln bei denen sich die lektorzuordnung innerhalb der letzten 31 Tage geaendert haben
$qry_stg = "SELECT distinct studiengang_kz, typ, kurzbz
			FROM (
				SELECT
					studiengang_kz
				FROM
					lehre.tbl_lehreinheit JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
					JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
				WHERE
					lehre.tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($semester_aktuell)." AND
					tbl_lehreinheitmitarbeiter.semesterstunden<>0 AND
					tbl_lehreinheitmitarbeiter.semesterstunden is not null
				UNION
				SELECT
					studiengang_kz
				FROM
					lehre.tbl_projektbetreuer JOIN lehre.tbl_projektarbeit ON tbl_projektbetreuer.projektarbeit_id=tbl_projektarbeit.projektarbeit_id
					JOIN lehre.tbl_lehreinheit ON tbl_lehreinheit.lehreinheit_id = tbl_projektarbeit.lehreinheit_id
					JOIN lehre.tbl_lehrveranstaltung ON tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id
				WHERE
					lehre.tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($semester_aktuell)."
				) as foo
				JOIN public.tbl_studiengang USING (studiengang_kz)
				";
if (count($stg_arr) > 0)
	$qry_stg .= " WHERE studiengang_kz in (".$db->db_implode4SQL($stg_arr).")";

	$qry_stg .= " ORDER BY typ, kurzbz";

$liste_gesamt = array();

$gesamt =& $workbook->addWorksheet('Gesamt');
$gesamt->setInputEncoding('utf-8');
$gesamtsheet_row = 1;

//Formate Definieren
$format_bold =& $workbook->addFormat();
$format_bold->setBold();

$workbook->setCustomColor(10, 255, 186, 179);
$format_colored =& $workbook->addFormat();
$format_colored->setFgColor(10);

$workbook->setCustomColor(10, 238, 238, 0);
$oe_colored =& $workbook->addFormat();
$oe_colored->setFgColor(11);

$format_number_colored =& $workbook->addFormat();
$format_number_colored->setNumFormat('0,0.00');
//$format_number_colored->setNumFormat('0.00');
$format_number_colored->setFgColor(10);

$format_number =& $workbook->addFormat();
$format_number->setNumFormat('0,0.00');

$format_number_bold =& $workbook->addFormat();
$format_number_bold->setNumFormat('0,0.00');
//$format_number_bold->setNumFormat('0.00');
$format_number_bold->setBold();

$format_normal = & $workbook->addFormat();
if ($result_stg = $db->db_query($qry_stg))
{
	while ($row_stg = $db->db_fetch_object($result_stg))
	{
		//Studiengang laden
		$studiengang = new studiengang($row_stg->studiengang_kz);
		$studiengang_kz = $row_stg->studiengang_kz;

		// Creating a worksheet
		$worksheet =& $workbook->addWorksheet($studiengang->kuerzel);
		$worksheet->setInputEncoding('utf-8');
		//echo "Writing $studiengang->kuerzel ...".microtime()."<br>";

		$i = 0;
		$gesamtsheet_row++;
		$worksheet->write(0, 0, 'Erstellt am '.date('d.m.Y').' '.$semester_aktuell.' '.$studiengang->kuerzel, $format_bold);
		$gesamt->write($gesamtsheet_row, 0, 'Erstellt am '.date('d.m.Y').' '.$semester_aktuell.' '.$studiengang->kuerzel, $format_bold);
		$gesamtsheet_row += 2;
		//Ueberschriften
		$worksheet->write(2, $i, "Studiengang", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "Studiengang", $format_bold);
		$worksheet->write(2, ++$i, "Personalnr", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "Personalnr", $format_bold);
		$worksheet->write(2, ++$i, "Titel", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "Titel", $format_bold);
		$worksheet->write(2, ++$i, "Vorname", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "Vorname", $format_bold);
		$worksheet->write(2, ++$i, "Familienname", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "Familienname", $format_bold);
		$worksheet->write(2, ++$i, "Fixangestellt", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "Fixangestellt", $format_bold);
		$worksheet->write(2, ++$i, "Disz. Zuordnung", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "Disz. Zuordnung", $format_bold);
		$worksheet->write(2, ++$i, "Department", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "Department", $format_bold);
		$worksheet->write(2, ++$i, "LV-Stunden", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "LV-Stunden", $format_bold);
		$worksheet->write(2, ++$i, "LV-Kosten", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "LV-Kosten", $format_bold);
		$worksheet->write(2, ++$i, "Betreuerstunden", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "Betreuerstunden", $format_bold);
		$worksheet->write(2, ++$i, "Betreuerkosten", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "Betreuer-Kosten", $format_bold);
		$worksheet->write(2, ++$i, "Gesamtstunden", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "Gesamtstunden", $format_bold);
		$worksheet->write(2, ++$i, "Gesamtkosten", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "Gesamtkosten", $format_bold);
		
		$worksheet->write(2, ++$i, "Gesamtstunden bestellt", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "Gesamtstunden bestellt", $format_bold);
		$worksheet->write(2, ++$i, "Gesamtkosten bestellt", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "Gesamtkosten bestellt", $format_bold);
		
		$worksheet->write(2, ++$i, "Gesamtstunden erteilt", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "Gesamtstunden erteilt", $format_bold);
		$worksheet->write(2, ++$i, "Gesamtkosten erteilt", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "Gesamtkosten erteilt", $format_bold);

		$worksheet->write(2, ++$i, "Gesamtstunden angenommen", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "Gesamtstunden angenommen", $format_bold);
		$worksheet->write(2, ++$i, "Gesamtkosten angenommen", $format_bold);
		$gesamt->write($gesamtsheet_row, $i, "Gesamtkosten angenommen", $format_bold);

		//Daten holen
		$qry = "SELECT tbl_lehreinheit.*,
					tbl_person.vorname,
					tbl_person.nachname,
					tbl_person.titelpre,
					tbl_mitarbeiter.personalnummer,
					tbl_person.person_id,
					tbl_mitarbeiter.mitarbeiter_uid,
					tbl_lehreinheitmitarbeiter.stundensatz AS stundensatz,
					tbl_lehreinheitmitarbeiter.semesterstunden AS semesterstunden,
					CASE
						WHEN tbl_mitarbeiter.fixangestellt = true
							THEN 'Ja'
						ELSE 'Nein'
						END AS fixangestellt,
					(
						SELECT tbl_organisationseinheit.organisationseinheittyp_kurzbz || ' ' || tbl_organisationseinheit.bezeichnung
						FROM PUBLIC.tbl_benutzerfunktion
						JOIN PUBLIC.tbl_organisationseinheit USING (oe_kurzbz)
						WHERE funktion_kurzbz = (CASE WHEN fixangestellt = true THEN 'kstzuordnung' ELSE 'oezuordnung' END)
							AND (
								datum_von IS NULL
								OR datum_von <= now()
								)
							AND (
								datum_bis IS NULL
								OR datum_bis >= now()
								)
							AND tbl_benutzerfunktion.uid = tbl_benutzer.uid LIMIT 1
						) AS oezuordnung,
					(
						WITH RECURSIVE meine_oes(oe_kurzbz, oe_parent_kurzbz, organisationseinheittyp_kurzbz) AS (
								SELECT oe_kurzbz,
									oe_parent_kurzbz,
									organisationseinheittyp_kurzbz
								FROM PUBLIC.tbl_organisationseinheit
								WHERE oe_kurzbz IN (
										SELECT oe_kurzbz
										FROM PUBLIC.tbl_benutzerfunktion
										WHERE funktion_kurzbz = (CASE WHEN fixangestellt = true THEN 'kstzuordnung' ELSE 'oezuordnung' END)
											AND (
												datum_von IS NULL
												OR datum_von <= now()
												)
											AND (
												datum_bis IS NULL
												OR datum_bis >= now()
												)
											AND tbl_benutzerfunktion.uid = tbl_benutzer.uid LIMIT 1
										)
									AND aktiv = true

								UNION ALL

								SELECT o.oe_kurzbz,
									o.oe_parent_kurzbz,
									o.organisationseinheittyp_kurzbz
								FROM PUBLIC.tbl_organisationseinheit o,
									meine_oes
								WHERE o.oe_kurzbz = meine_oes.oe_parent_kurzbz
									AND aktiv = true
								)
						SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT tbl_organisationseinheit.bezeichnung), ', ')
						FROM meine_oes
						JOIN PUBLIC.tbl_organisationseinheit USING (oe_kurzbz)
						WHERE meine_oes.organisationseinheittyp_kurzbz = 'Department'
						) AS department,
					CASE
						WHEN COALESCE(tbl_lehreinheitmitarbeiter.updateamum, tbl_lehreinheitmitarbeiter.insertamum) > now() - interval '31 days'
							THEN 't'
						ELSE 'f'
						END AS geaendert,
					 (SELECT
							ARRAY_TO_STRING(ARRAY_AGG(vertragsstatus_kurzbz), ',')
							FROM
								lehre.tbl_vertrag_vertragsstatus
							WHERE
								vertrag_id = tbl_lehreinheitmitarbeiter.vertrag_id
						) as vertragsstatus
				FROM lehre.tbl_lehreinheit
				    JOIN lehre.tbl_lehreinheitmitarbeiter ON tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitmitarbeiter.lehreinheit_id
				    JOIN PUBLIC.tbl_mitarbeiter ON tbl_lehreinheitmitarbeiter.mitarbeiter_uid = tbl_mitarbeiter.mitarbeiter_uid
					JOIN PUBLIC.tbl_benutzer ON tbl_benutzer.uid = tbl_mitarbeiter.mitarbeiter_uid
					JOIN PUBLIC.tbl_person ON tbl_person.person_id = tbl_benutzer.person_id
					JOIN lehre.tbl_lehrveranstaltung ON  tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id
				WHERE studiengang_kz = ".$db->db_add_param($studiengang_kz)."
					AND studiensemester_kurzbz = ".$db->db_add_param($semester_aktuell)."
					AND tbl_lehreinheitmitarbeiter.semesterstunden <> 0
					AND tbl_lehreinheitmitarbeiter.semesterstunden IS NOT NULL
					AND EXISTS (
						SELECT lehreinheit_id
						FROM lehre.tbl_lehreinheitgruppe
						WHERE lehreinheit_id = tbl_lehreinheit.lehreinheit_id
						)
				ORDER BY nachname,
					vorname,
					tbl_mitarbeiter.mitarbeiter_uid";

		if ($result = $db->db_query($qry))
		{
			$zeile = 3;
			$gesamtkosten = 0;
			$liste = array();
			$gesamtsheet_row++;
			while ($row = $db->db_fetch_object($result))
			{
				$row->vertragsstatus = explode(',', $row->vertragsstatus);
				//Gesamtstunden und Kosten ermitteln
				if (array_key_exists($row->mitarbeiter_uid, $liste))
				{
					$liste[$row->mitarbeiter_uid]['lvstunden'] =
						$liste[$row->mitarbeiter_uid]['lvstunden'] + $row->semesterstunden;
					$liste[$row->mitarbeiter_uid]['lvkosten'] =
						$liste[$row->mitarbeiter_uid]['lvkosten'] + ($row->semesterstunden * $row->stundensatz);
					$liste[$row->mitarbeiter_uid]['gesamtstunden'] =
						$liste[$row->mitarbeiter_uid]['gesamtstunden'] + $row->semesterstunden;
					$liste[$row->mitarbeiter_uid]['gesamtkosten'] =
						$liste[$row->mitarbeiter_uid]['gesamtkosten'] + ($row->semesterstunden * $row->stundensatz);

					if (!isset($liste[$row->mitarbeiter_uid]['gesamtstunden_bestellt']))
					{
						$liste[$row->mitarbeiter_uid]['gesamtstunden_bestellt'] = 0;
						$liste[$row->mitarbeiter_uid]['gesamtkosten_bestellt'] = 0;
					}

					if (!isset($liste[$row->mitarbeiter_uid]['gesamtstunden_erteilt']))
					{
						$liste[$row->mitarbeiter_uid]['gesamtstunden_erteilt'] = 0;
						$liste[$row->mitarbeiter_uid]['gesamtkosten_erteilt'] = 0;
					}

					if (!isset($liste[$row->mitarbeiter_uid]['gesamtstunden_akzeptiert']))
					{
						$liste[$row->mitarbeiter_uid]['gesamtstunden_akzeptiert'] = 0;
						$liste[$row->mitarbeiter_uid]['gesamtkosten_akzeptiert'] = 0;
					}

					if (in_array('bestellt', $row->vertragsstatus))
					{
						$liste[$row->mitarbeiter_uid]['gesamtstunden_bestellt'] =
							$liste[$row->mitarbeiter_uid]['gesamtstunden_bestellt'] + $row->semesterstunden;
						$liste[$row->mitarbeiter_uid]['gesamtkosten_bestellt'] =
							$liste[$row->mitarbeiter_uid]['gesamtkosten_bestellt']
							+ ($row->semesterstunden * $row->stundensatz);
					}

					if (in_array('erteilt', $row->vertragsstatus))
					{
						$liste[$row->mitarbeiter_uid]['gesamtstunden_erteilt'] =
							$liste[$row->mitarbeiter_uid]['gesamtstunden_erteilt'] + $row->semesterstunden;
						$liste[$row->mitarbeiter_uid]['gesamtkosten_erteilt'] =
							$liste[$row->mitarbeiter_uid]['gesamtkosten_erteilt']
							+ ($row->semesterstunden * $row->stundensatz);
					}

					if (in_array('akzeptiert', $row->vertragsstatus))
					{
						$liste[$row->mitarbeiter_uid]['gesamtstunden_akzeptiert'] =
							$liste[$row->mitarbeiter_uid]['gesamtstunden_akzeptiert'] + $row->semesterstunden;
						$liste[$row->mitarbeiter_uid]['gesamtkosten_akzeptiert'] =
							$liste[$row->mitarbeiter_uid]['gesamtkosten_akzeptiert']
							+ ($row->semesterstunden * $row->stundensatz);
					}
				}
				else
				{
					$liste[$row->mitarbeiter_uid]['lvstunden'] = $row->semesterstunden;
					$liste[$row->mitarbeiter_uid]['lvkosten'] = $row->semesterstunden * $row->stundensatz;
					$liste[$row->mitarbeiter_uid]['gesamtstunden'] = $row->semesterstunden;
					$liste[$row->mitarbeiter_uid]['gesamtkosten'] = $row->semesterstunden * $row->stundensatz;

					if (!isset($liste[$row->mitarbeiter_uid]['gesamtstunden_bestellt']))
					{
						$liste[$row->mitarbeiter_uid]['gesamtstunden_bestellt'] = 0;
						$liste[$row->mitarbeiter_uid]['gesamtkosten_bestellt'] = 0;
					}

					if (!isset($liste[$row->mitarbeiter_uid]['gesamtstunden_erteilt']))
					{
						$liste[$row->mitarbeiter_uid]['gesamtstunden_erteilt'] = 0;
						$liste[$row->mitarbeiter_uid]['gesamtkosten_erteilt'] = 0;
					}

					if (!isset($liste[$row->mitarbeiter_uid]['gesamtstunden_akzeptiert']))
					{
						$liste[$row->mitarbeiter_uid]['gesamtstunden_akzeptiert'] = 0;
						$liste[$row->mitarbeiter_uid]['gesamtkosten_akzeptiert'] = 0;
					}

					if (in_array('bestellt', $row->vertragsstatus))
					{
						$liste[$row->mitarbeiter_uid]['gesamtstunden_bestellt'] =
							$liste[$row->mitarbeiter_uid]['gesamtstunden_bestellt'] + $row->semesterstunden;
						$liste[$row->mitarbeiter_uid]['gesamtkosten_bestellt'] =
							$liste[$row->mitarbeiter_uid]['gesamtkosten_bestellt']
							+ ($row->semesterstunden * $row->stundensatz);
					}

					if (in_array('erteilt', $row->vertragsstatus))
					{
						$liste[$row->mitarbeiter_uid]['gesamtstunden_erteilt'] =
							$liste[$row->mitarbeiter_uid]['gesamtstunden_erteilt'] + $row->semesterstunden;
						$liste[$row->mitarbeiter_uid]['gesamtkosten_erteilt'] =
							$liste[$row->mitarbeiter_uid]['gesamtkosten_erteilt']
							+ ($row->semesterstunden * $row->stundensatz);
					}

					if (in_array('akzeptiert', $row->vertragsstatus))
					{
						$liste[$row->mitarbeiter_uid]['gesamtstunden_akzeptiert'] =
							$liste[$row->mitarbeiter_uid]['gesamtstunden_akzeptiert'] + $row->semesterstunden;
						$liste[$row->mitarbeiter_uid]['gesamtkosten_akzeptiert'] =
							$liste[$row->mitarbeiter_uid]['gesamtkosten_akzeptiert']
							+ ($row->semesterstunden * $row->stundensatz);
					}
				}
				$liste[$row->mitarbeiter_uid]['personalnummer'] = $row->personalnummer;
				$liste[$row->mitarbeiter_uid]['titelpre'] = $row->titelpre;
				$liste[$row->mitarbeiter_uid]['vorname'] = $row->vorname;
				$liste[$row->mitarbeiter_uid]['nachname'] = $row->nachname;
				$liste[$row->mitarbeiter_uid]['fixangestellt'] = $row->fixangestellt;

				if (is_null($row->oezuordnung))
				{
					$oeInfos = getOe($row->mitarbeiter_uid, $row->fixangestellt);
					$liste[$row->mitarbeiter_uid]['oezuordnung'] = $oeInfos['oezuordnung'];
					$liste[$row->mitarbeiter_uid]['department'] = $oeInfos['department'];
					$liste[$row->mitarbeiter_uid]['organisationgeaendert'] = $oeInfos['organisationgeaendert'];
				}
				else
				{
					$liste[$row->mitarbeiter_uid]['oezuordnung'] = $row->oezuordnung;
					$liste[$row->mitarbeiter_uid]['department'] = $row->department;
				}

				$liste[$row->mitarbeiter_uid]['betreuergesamtstunden'] = 0;
				$liste[$row->mitarbeiter_uid]['betreuergesamtkosten'] = 0;
				if ($row->geaendert == 't')
					$liste[$row->mitarbeiter_uid]['geaendert'] = true;
			}

			//Alle holen die eine Betreuung aber keinen Lehrauftrag haben
			$qry = "SELECT
						distinct personalnummer, titelpre, vorname, nachname, uid,
						CASE WHEN fixangestellt = true THEN 'Ja' ELSE 'Nein' END as fixangestellt,
						(SELECT
							tbl_organisationseinheit.organisationseinheittyp_kurzbz
							|| ' ' || tbl_organisationseinheit.bezeichnung
						FROM
							public.tbl_benutzerfunktion
							JOIN public.tbl_organisationseinheit USING (oe_kurzbz)
						WHERE
							funktion_kurzbz = (CASE WHEN fixangestellt = true THEN 'kstzuordnung' ELSE 'oezuordnung' END)
							AND (datum_von IS NULL OR datum_von <= now())
							AND (datum_bis IS NULL OR datum_bis >= now())
							AND tbl_benutzerfunktion.uid = tbl_benutzer.uid
						LIMIT 1
						) AS oezuordnung,
						(
						WITH RECURSIVE meine_oes(oe_kurzbz, oe_parent_kurzbz, organisationseinheittyp_kurzbz) AS (
								SELECT oe_kurzbz,
									oe_parent_kurzbz,
									organisationseinheittyp_kurzbz
								FROM PUBLIC.tbl_organisationseinheit
								WHERE oe_kurzbz IN (
										SELECT oe_kurzbz
										FROM PUBLIC.tbl_benutzerfunktion
										WHERE funktion_kurzbz = (CASE WHEN fixangestellt = true THEN 'kstzuordnung' ELSE 'oezuordnung' END)
											AND (
												datum_von IS NULL
												OR datum_von <= now()
												)
											AND (
												datum_bis IS NULL
												OR datum_bis >= now()
												)
											AND tbl_benutzerfunktion.uid = tbl_benutzer.uid LIMIT 1
										)
									AND aktiv = true

								UNION ALL

								SELECT o.oe_kurzbz,
									o.oe_parent_kurzbz,
									o.organisationseinheittyp_kurzbz
								FROM PUBLIC.tbl_organisationseinheit o,
									meine_oes
								WHERE o.oe_kurzbz = meine_oes.oe_parent_kurzbz
									AND aktiv = true
								)
						SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT tbl_organisationseinheit.bezeichnung), ', ')
						FROM meine_oes
						JOIN PUBLIC.tbl_organisationseinheit USING (oe_kurzbz)
						WHERE meine_oes.organisationseinheittyp_kurzbz = 'Department'
						) AS department
					FROM
						lehre.tbl_projektbetreuer JOIN public.tbl_person ON tbl_projektbetreuer.person_id=tbl_person.person_id
						JOIN public.tbl_benutzer ON tbl_benutzer.person_id=tbl_person.person_id
						JOIN public.tbl_mitarbeiter ON tbl_mitarbeiter.mitarbeiter_uid=tbl_benutzer.uid
						JOIN lehre.tbl_projektarbeit ON tbl_projektarbeit.projektarbeit_id=tbl_projektbetreuer.projektarbeit_id
						JOIN lehre.tbl_lehreinheit ON tbl_lehreinheit.lehreinheit_id=tbl_projektarbeit.lehreinheit_id
						JOIN lehre.tbl_lehrveranstaltung ON tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id
					WHERE
						tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($semester_aktuell)." AND
						tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER)." AND
						NOT EXISTS (SELECT
										mitarbeiter_uid
									FROM
										lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit ON tbl_lehreinheitmitarbeiter.lehreinheit_id=tbl_lehreinheit.lehreinheit_id
										JOIN lehre.tbl_lehrveranstaltung ON tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id
									WHERE
										mitarbeiter_uid=tbl_benutzer.uid AND
										tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER)." AND
										tbl_lehreinheitmitarbeiter.semesterstunden<>0 AND
										tbl_lehreinheitmitarbeiter.semesterstunden is not null AND
										EXISTS (
											SELECT lehreinheit_id
											FROM
												lehre.tbl_lehreinheitgruppe
											WHERE
												lehreinheit_id=tbl_lehreinheit.lehreinheit_id) AND
												tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($semester_aktuell).");";

			if ($result = $db->db_query($qry))
			{
				while ($row = $db->db_fetch_object($result))
				{
					if (!isset($liste[$row->uid]))
					{
						$liste[$row->uid]['personalnummer'] = $row->personalnummer;
						$liste[$row->uid]['titelpre'] = $row->titelpre;
						$liste[$row->uid]['vorname'] = $row->vorname;
						$liste[$row->uid]['nachname'] = $row->nachname;
						$liste[$row->uid]['fixangestellt'] = $row->fixangestellt;

						if (is_null($row->oezuordnung))
						{
							$oeInfos = getOe($row->uid, $row->fixangestellt);
							$liste[$row->uid]['oezuordnung'] = $oeInfos['oezuordnung'];
							$liste[$row->uid]['department'] = $oeInfos['department'];
							$liste[$row->uid]['organisationgeaendert'] = $oeInfos['organisationgeaendert'];
						}
						else
						{
							$liste[$row->uid]['oezuordnung'] = $row->oezuordnung;
							$liste[$row->uid]['department'] = $row->department;
						}

						$liste[$row->uid]['geaendert'] = false;
						$liste[$row->uid]['gesamtstunden'] = 0;
						$liste[$row->uid]['gesamtkosten'] = 0;
						$liste[$row->uid]['lvstunden'] = 0;
						$liste[$row->uid]['lvkosten'] = 0;
						$liste[$row->uid]['betreuergesamtstunden'] = 0;
						$liste[$row->uid]['betreuergesamtkosten'] = 0;
					}
				}
			}

			//Betreuungen fuer Projektarbeiten
			foreach ($liste as $uid => $arr)
			{
				$qry = "
					SELECT
						tbl_projektbetreuer.stunden,
						tbl_projektbetreuer.stundensatz,
						CASE WHEN
							COALESCE(tbl_projektbetreuer.updateamum, tbl_projektbetreuer.insertamum) > now() - interval '31 days'
							THEN
								't'
							ELSE
								'f'
						END as geaendert,
						(SELECT
							ARRAY_TO_STRING(ARRAY_AGG(vertragsstatus_kurzbz), ',')
							FROM
								lehre.tbl_vertrag_vertragsstatus
							WHERE
								vertrag_id = tbl_projektbetreuer.vertrag_id
						) as vertragsstatus
					FROM
						lehre.tbl_projektbetreuer JOIN public.tbl_benutzer ON tbl_projektbetreuer.person_id = tbl_benutzer.person_id
						JOIN lehre.tbl_projektarbeit ON tbl_projektarbeit.projektarbeit_id = tbl_projektbetreuer.projektarbeit_id
						JOIN lehre.tbl_lehreinheit ON tbl_lehreinheit.lehreinheit_id = tbl_projektarbeit.lehreinheit_id
						JOIN lehre.tbl_lehrveranstaltung ON tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id
						JOIN campus.vw_student ON vw_student.uid = student_uid
					WHERE
						tbl_benutzer.uid = ".$db->db_add_param($uid)."
						AND tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($semester_aktuell)."
						AND tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);

				if ($result = $db->db_query($qry))
				{
					while ($row = $db->db_fetch_object($result))
					{
						$row->vertragsstatus = explode(',', $row->vertragsstatus);
						$liste[$uid]['gesamtstunden'] = $liste[$uid]['gesamtstunden'] + $row->stunden;
						$liste[$uid]['gesamtkosten'] =
							$liste[$uid]['gesamtkosten'] + ($row->stunden * $row->stundensatz);
						$liste[$uid]['betreuergesamtstunden'] = $liste[$uid]['betreuergesamtstunden'] + $row->stunden;
						$liste[$uid]['betreuergesamtkosten'] =
							$liste[$uid]['betreuergesamtkosten'] + ($row->stunden * $row->stundensatz);
						if ($row->geaendert == 't')
						{
							$liste[$uid]['geaendert'] = true;
						}

						if (!isset($liste[$uid]['gesamtstunden_bestellt']))
						{
							$liste[$uid]['gesamtstunden_bestellt'] = 0;
							$liste[$uid]['gesamtkosten_bestellt'] = 0;
						}

						if (!isset($liste[$uid]['gesamtstunden_erteilt']))
						{
							$liste[$uid]['gesamtstunden_erteilt'] = 0;
							$liste[$uid]['gesamtkosten_erteilt'] = 0;
						}

						if (!isset($liste[$uid]['gesamtstunden_akzeptiert']))
						{
							$liste[$uid]['gesamtstunden_akzeptiert'] = 0;
							$liste[$uid]['gesamtkosten_akzeptiert'] = 0;
						}

						if (in_array('bestellt', $row->vertragsstatus))
						{
							$liste[$uid]['gesamtstunden_bestellt'] =
								$liste[$uid]['gesamtstunden_bestellt'] + $row->stunden;
							$liste[$uid]['gesamtkosten_bestellt'] =
								$liste[$uid]['gesamtkosten_bestellt'] + ($row->stunden * $row->stundensatz);
						}

						if (in_array('erteilt', $row->vertragsstatus))
						{
							$liste[$uid]['gesamtstunden_erteilt'] =
								$liste[$uid]['gesamtstunden_erteilt'] + $row->stunden;
							$liste[$uid]['gesamtkosten_erteilt'] =
								$liste[$uid]['gesamtkosten_erteilt'] + ($row->stunden * $row->stundensatz);
						}

						if (in_array('akzeptiert', $row->vertragsstatus))
						{
							$liste[$uid]['gesamtstunden_akzeptiert'] =
								$liste[$uid]['gesamtstunden_akzeptiert'] + $row->stunden;
							$liste[$uid]['gesamtkosten_akzeptiert'] =
								$liste[$uid]['gesamtkosten_akzeptiert'] + ($row->stunden * $row->stundensatz);
						}
					}
				}
			}

			$vn = array();
			$nn = array();
			foreach ($liste as $key => $row)
			{
				$vn[$key] = $row['vorname'];
				$nn[$key] = $row['nachname'];
			}

			array_multisort($nn, SORT_ASC, $vn, SORT_ASC, $liste);

			//Daten ausgeben
			foreach ($liste as $uid => $row)
			{
				$i = 0;
				if (isset($row['geaendert']) && $row['geaendert'] == true)
				{
					$format = $format_colored;
					$formatOE = $format_colored;
					$formatnb = $format_number_colored;
				}
				else
				{
					$format = $format_normal;
					$formatOE = $format_normal;
					$formatnb = $format_number;
				}

				if(isset($row['organisationgeaendert']) && $row['organisationgeaendert'] === true)
				{
					$formatOE = $oe_colored;
				}
				//Studiengang
				$worksheet->write($zeile, $i, $studiengang->kuerzel, $format);
				$gesamt->write($gesamtsheet_row, $i, $studiengang->kuerzel, $format);
				//Personalnummer
				$worksheet->write($zeile, ++$i, $row['personalnummer'], $format);
				$gesamt->write($gesamtsheet_row, $i, $row['personalnummer'], $format);
				//Titel
				$worksheet->write($zeile, ++$i, $row['titelpre'], $format);
				$gesamt->write($gesamtsheet_row, $i, $row['titelpre'], $format);
				//Vorname
				$worksheet->write($zeile, ++$i, $row['vorname'], $format);
				$gesamt->write($gesamtsheet_row, $i, $row['vorname'], $format);
				//Nachname
				$worksheet->write($zeile, ++$i, $row['nachname'], $format);
				$gesamt->write($gesamtsheet_row, $i, $row['nachname'], $format);
				//Fixangestellt
				$worksheet->write($zeile, ++$i, $row['fixangestellt'], $format);
				$gesamt->write($gesamtsheet_row, $i, $row['fixangestellt'], $format);
				//OE-Zuordnung
				$worksheet->write($zeile, ++$i, $row['oezuordnung'], $formatOE);
				$gesamt->write($gesamtsheet_row, $i, $row['oezuordnung'], $formatOE);
				//Department der OE-Zuordnung
				$worksheet->write($zeile, ++$i, $row['department'], $formatOE);
				$gesamt->write($gesamtsheet_row, $i, $row['department'], $formatOE);
				//LVStunden
				$lvstunden = str_replace(', ', '.', $row['lvstunden']);
				$worksheet->write($zeile, ++$i, $lvstunden, $format);
				$gesamt->write($gesamtsheet_row, $i, $lvstunden, $format);
				//LVKosten
				$lvkosten = str_replace(', ', '.', $row['lvkosten']);
				$worksheet->writeNumber($zeile, ++$i, $lvkosten, $formatnb);
				$gesamt->writeNumber($gesamtsheet_row, $i, $lvkosten, $formatnb);
				//Betreuerstunden
				$betreuergesamtstunden = str_replace(', ', '.', $row['betreuergesamtstunden']);
				$worksheet->write($zeile, ++$i, $betreuergesamtstunden, $formatnb);
				$gesamt->write($gesamtsheet_row, $i, $betreuergesamtstunden, $formatnb);
				//Betreuerkosten
				$betreuergesamtkosten = str_replace(', ', '.', $row['betreuergesamtkosten']);
				$worksheet->write($zeile, ++$i, $betreuergesamtkosten, $formatnb);
				$gesamt->write($gesamtsheet_row, $i, $betreuergesamtkosten, $formatnb);
				//Gesamtstunden
				$gesamtstunden = str_replace(', ', '.', $row['gesamtstunden']);
				$worksheet->write($zeile, ++$i, $gesamtstunden, $formatnb);
				$gesamt->write($gesamtsheet_row, $i, $gesamtstunden, $formatnb);
				//Gesamtkosten
				$gesamtkosten_row = str_replace(', ', '.', $row['gesamtkosten']);
				$worksheet->writeNumber($zeile, ++$i, $gesamtkosten_row, $formatnb);
				$gesamt->writeNumber($gesamtsheet_row, $i, $gesamtkosten_row, $formatnb);
				
				//Gesamtstunden bestellt
				$gesamtstunden_bestellt = str_replace(', ', '.', $row['gesamtstunden_bestellt']);
				$worksheet->write($zeile, ++$i, $gesamtstunden_bestellt, $formatnb);
				$gesamt->write($gesamtsheet_row, $i, $gesamtstunden_bestellt, $formatnb);
				//Gesamtkosten bestellt
				$gesamtkosten_bestellt_row = str_replace(', ', '.', $row['gesamtkosten_bestellt']);
				$worksheet->writeNumber($zeile, ++$i, $gesamtkosten_bestellt_row, $formatnb);
				$gesamt->writeNumber($gesamtsheet_row, $i, $gesamtkosten_bestellt_row, $formatnb);
				
				//Gesamtstunden erteilt
				$gesamtstunden_erteilt = str_replace(', ', '.', $row['gesamtstunden_erteilt']);
				$worksheet->write($zeile, ++$i, $gesamtstunden_erteilt, $formatnb);
				$gesamt->write($gesamtsheet_row, $i, $gesamtstunden_erteilt, $formatnb);
				//Gesamtkosten erteilt
				$gesamtkosten_erteilt_row = str_replace(', ', '.', $row['gesamtkosten_erteilt']);
				$worksheet->writeNumber($zeile, ++$i, $gesamtkosten_erteilt_row, $formatnb);
				$gesamt->writeNumber($gesamtsheet_row, $i, $gesamtkosten_erteilt_row, $formatnb);
				
				//Gesamtstunden akzeptiert
				$gesamtstunden_akzeptiert = str_replace(', ', '.', $row['gesamtstunden_akzeptiert']);
				$worksheet->write($zeile, ++$i, $gesamtstunden_akzeptiert, $formatnb);
				$gesamt->write($gesamtsheet_row, $i, $gesamtstunden_akzeptiert, $formatnb);
				//Gesamtkosten akzeptiert
				$gesamtkosten_akzeptiert_row = str_replace(', ', '.', $row['gesamtkosten_akzeptiert']);
				$worksheet->writeNumber($zeile, ++$i, $gesamtkosten_akzeptiert_row, $formatnb);
				$gesamt->writeNumber($gesamtsheet_row, $i, $gesamtkosten_akzeptiert_row, $formatnb);

				//Kosten zu den Gesamtkosten hinzurechnen
				$gesamtkosten = $gesamtkosten + $row['gesamtkosten'];
				$zeile++;
				$gesamtsheet_row++;

				$liste_gesamt[$uid]['personalnummer'] = $row['personalnummer'];
				$liste_gesamt[$uid]['titelpre'] = $row['titelpre'];
				$liste_gesamt[$uid]['vorname'] = $row['vorname'];
				$liste_gesamt[$uid]['nachname'] = $row['nachname'];
				if (isset($liste_gesamt[$uid]['gesamtstunden']))
					$liste_gesamt[$uid]['gesamtstunden'] += $row['gesamtstunden'];
				else
					$liste_gesamt[$uid]['gesamtstunden'] = $row['gesamtstunden'];

				if (isset($liste_gesamt[$uid]['gesamtkosten']))
					$liste_gesamt[$uid]['gesamtkosten'] += $row['gesamtkosten'];
				else
					$liste_gesamt[$uid]['gesamtkosten'] = $row['gesamtkosten'];

				if (isset($liste_gesamt[$uid]['gesamtstunden_bestellt']))
					$liste_gesamt[$uid]['gesamtstunden_bestellt'] += $row['gesamtstunden_bestellt'];
				else
					$liste_gesamt[$uid]['gesamtstunden_bestellt'] = $row['gesamtstunden_bestellt'];
				
				if (isset($liste_gesamt[$uid]['gesamtkosten_bestellt']))
					$liste_gesamt[$uid]['gesamtkosten_bestellt'] += $row['gesamtkosten_bestellt'];
				else
					$liste_gesamt[$uid]['gesamtkosten_bestellt'] = $row['gesamtkosten_bestellt'];

				if (isset($liste_gesamt[$uid]['gesamtstunden_erteilt']))
					$liste_gesamt[$uid]['gesamtstunden_erteilt'] += $row['gesamtstunden_erteilt'];
				else
					$liste_gesamt[$uid]['gesamtstunden_erteilt'] = $row['gesamtstunden_erteilt'];
				
				if (isset($liste_gesamt[$uid]['gesamtkosten_erteilt']))
					$liste_gesamt[$uid]['gesamtkosten_erteilt'] += $row['gesamtkosten_erteilt'];
				else
					$liste_gesamt[$uid]['gesamtkosten_erteilt'] = $row['gesamtkosten_erteilt'];

				if (isset($liste_gesamt[$uid]['gesamtstunden_akzeptiert']))
					$liste_gesamt[$uid]['gesamtstunden_akzeptiert'] += $row['gesamtstunden_akzeptiert'];
				else
					$liste_gesamt[$uid]['gesamtstunden_akzeptiert'] = $row['gesamtstunden_akzeptiert'];
				
				if (isset($liste_gesamt[$uid]['gesamtkosten_akzeptiert']))
					$liste_gesamt[$uid]['gesamtkosten_akzeptiert'] += $row['gesamtkosten_akzeptiert'];
				else
					$liste_gesamt[$uid]['gesamtkosten_akzeptiert'] = $row['gesamtkosten_akzeptiert'];
			}

			//Gesamtkosten anzeigen
			$worksheet->writeNumber($zeile, 13, $gesamtkosten, $format_number_bold);
			$gesamt->writeNumber($gesamtsheet_row, 13, $gesamtkosten, $format_number_bold);
		}
	}

	//Betreuerstunden
	$worksheet =& $workbook->addWorksheet('Betreuerstunden');
	$worksheet->setInputEncoding('utf-8');
	$qry = "SELECT
				studiensemester_kurzbz, nachname, vorname, sum(stunden) AS stunden, titelpre,
				sum(tbl_projektbetreuer.stundensatz*tbl_projektbetreuer.stunden)::numeric(8, 2) AS euro, person_id
			FROM
				public.tbl_person JOIN lehre.tbl_projektbetreuer USING (person_id)
				JOIN lehre.tbl_projektarbeit USING (projektarbeit_id)
				JOIN lehre.tbl_lehreinheit USING (lehreinheit_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				studiensemester_kurzbz = ".$db->db_add_param($semester_aktuell)." AND
				stunden > 0";

	if (count($stg_arr) > 0)
		$qry .= " AND tbl_lehrveranstaltung.studiengang_kz IN(".$db->db_implode4SQL($stg_arr).")";

	$qry .= "
			GROUP BY
				studiensemester_kurzbz, person_id, nachname, vorname, titelpre
			ORDER BY
				nachname, vorname
			";
	$i = 0;
	$gesamtkosten = 0;

	$worksheet->write(0, 0, 'Erstellt am '.date('d.m.Y').' '.$semester_aktuell.' Betreuerstunden', $format_bold);
	//Ueberschriften
	//$worksheet->write(2, $i, "Studiengang", $format_bold);
	$worksheet->write(2, ++$i, "Titel", $format_bold);
	$worksheet->write(2, ++$i, "Familienname", $format_bold);
	$worksheet->write(2, ++$i, "Vorname", $format_bold);
	$worksheet->write(2, ++$i, "Stunden", $format_bold);
	$worksheet->write(2, ++$i, "Kosten", $format_bold);

	$format = $format_normal;
	$formatnb = $format_number;
	if ($result = $db->db_query($qry))
	{
		$zeile = 3;
		while ($row = $db->db_fetch_object($result))
		{
			$i = 0;
			//Studiensemester
			$worksheet->write($zeile, ++$i, $row->titelpre, $format);
			//Vorname
			$worksheet->write($zeile, ++$i, $row->nachname, $format);
			//Nachname
			$worksheet->write($zeile, ++$i, $row->vorname, $format);
			//Stunden
			$worksheet->writeNumber($zeile, ++$i, $row->stunden, $formatnb);
			//Kosten
			$worksheet->writeNumber($zeile, ++$i, $row->euro, $formatnb);
			$zeile++;

			$gesamtkosten = $gesamtkosten + $row->euro;
		}

		//Gesamtkosten anzeigen
		$worksheet->writeNumber($zeile, 5, $gesamtkosten, $format_number_bold);
	}

	$workbook->close();

	//Mail versenden mit Excel File im Anhang
    $subject = "Lehrauftragsliste ".date('d.m.Y');
    $message = "Dies ist eine automatische eMail!\n\nAnbei die Lehrauftragslisten vom ".date('d.m.Y');
    $message .= "\n\nJederzeit abrufbar unter ".APP_ROOT.'content/statistik/lehrauftragsliste_mail.xls.php';
	if ($oe_kurzbz != '')
		$message .= "?oe_kurzbz=".$oe_kurzbz;

    $fileatttype = "application/xls";
    $fileattname = "lehrauftragsliste_".date('Y_m_d').".xls";

	$empfaenger = MAIL_GST;
	if ($oe_kurzbz == 'lehrgang')
		$empfaenger = MAIL_LG;

	$mail = new mail($empfaenger, 'noreply@'.DOMAIN, $subject, $message);
	$mail->addAttachmentBinary($file, $fileatttype, $fileattname);

	if ($mail->send())
		echo 'Email mit Lehrauftragslisten wurde an '.$empfaenger.' versandt!';
	else
		echo "Fehler beim Versenden der Lehrauftragsliste";
}

function getOe($mitarbeiter_uid, $fixangestellt)
{
	$benutzerfunktion = new Benutzerfunktion();
	$oe = new Organisationseinheit();

	$benutzerfunktion->getBenutzerFunktionByUid($mitarbeiter_uid, $fixangestellt === 'Ja' ? 'kstzuordnung' : 'oezuordnung', date('Y-m-d'));

	if (isset($benutzerfunktion->result[0]))
	{
		array_multisort(array_column($benutzerfunktion->result, 'datum_von'), SORT_ASC, $benutzerfunktion->result);
	}
	else
	{
		$benutzerfunktion->getBenutzerFunktionByUid($mitarbeiter_uid, $fixangestellt === 'Ja' ? 'kstzuordnung' : 'oezuordnung', null, date('Y-m-d'));
		array_multisort(array_column($benutzerfunktion->result, 'datum_bis'), SORT_DESC, $benutzerfunktion->result);
	}

	if (!isset($benutzerfunktion->result[0]))
		return array('oezuordnung' => '', 'department' => '', 'organisationgeaendert' => false);

	$oe->load($benutzerfunktion->result[0]->oe_kurzbz);
	$oezuordnung = $oe->organisationseinheittyp_kurzbz . ' ' . $oe->bezeichnung;

	$oe_parents = $oe->getParents_withOEType($oe->oe_kurzbz);

	if (is_array($oe_parents))
		 $department = ($oe_parents[array_search('Department', array_column($oe_parents, 'oe_typ_bezeichnung'))]->oe_bezeichnung);
	else
		$department = '';

	return array('oezuordnung' => $oezuordnung, 'department' => $department, 'organisationgeaendert' => true);
}