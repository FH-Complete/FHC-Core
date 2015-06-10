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

$stsem = new studiensemester();

if(isset($_GET['stsem']))
{
	if(check_stsem($_GET['stsem']))
		$semester_aktuell = $_GET['stsem'];
	else
		die('Studiensemester ist ungueltig');
}
else
	$semester_aktuell  = $stsem->getaktorNext();

//UID als Kommandozeilenparameter
if(isset($_SERVER['argv']) && isset($_SERVER['argv'][1]) && !strstr($_SERVER['argv'][1],'='))
{
	$oe_kurzbz = $_SERVER['argv'][1];
}
else
{
	if(isset($_GET['oe_kurzbz']))
		$oe_kurzbz = $_GET['oe_kurzbz'];
	else
	{
		$stg = new studiengang();
		$stg->load('0');
		$oe_kurzbz = $stg->oe_kurzbz;
	}
}

$file = 'lehrauftragsliste.xls';
$file = tempnam('/tmp','lehrauftragsliste_').'.xls';

// Creating a workbook
echo 'Lehrauftragslisten werden erstellt. Bitte warten!<BR>';
flush();
$workbook = new Spreadsheet_Excel_Writer($file);
$workbook->setVersion(8);
$db = new basis_db();

$stg = new studiengang();
$stg->getStudiengaengeFromOe($oe_kurzbz);
$stg_arr=array();
if(count($stg->result)>0)
{
	foreach($stg->result as $row)
	{
		$stg_arr[] = $row->studiengang_kz;
	}
}
//Studiengaenge ermitteln bei denen sich die lektorzuordnung innerhalb der letzten 31 Tage geaendert haben
$qry_stg = "SELECT distinct studiengang_kz
			FROM (
				SELECT
					studiengang_kz
				FROM
					lehre.tbl_lehreinheit JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
					JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
				WHERE
					lehre.tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($semester_aktuell)." AND
					tbl_lehreinheitmitarbeiter.semesterstunden<>0 AND
					tbl_lehreinheitmitarbeiter.semesterstunden is not null AND
					tbl_lehreinheitmitarbeiter.stundensatz<>0 AND
					tbl_lehreinheitmitarbeiter.faktor<>0
				UNION
				SELECT
					studiengang_kz
				FROM
					lehre.tbl_projektbetreuer, lehre.tbl_projektarbeit, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung
				WHERE
					lehre.tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($semester_aktuell)." AND
					tbl_projektbetreuer.projektarbeit_id=tbl_projektarbeit.projektarbeit_id AND
					tbl_projektarbeit.lehreinheit_id = tbl_lehreinheit.lehreinheit_id AND
					tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id
				) as foo
				";
if(count($stg_arr)>0)
	$qry_stg.=" WHERE studiengang_kz in (".$db->db_implode4SQL($stg_arr).")";

$liste_gesamt = array();

$gesamt =& $workbook->addWorksheet('Gesamt');
$gesamt->setInputEncoding('utf-8');
$gesamtsheet_row=1;

//Formate Definieren
$format_bold =& $workbook->addFormat();
$format_bold->setBold();

$workbook->setCustomColor(10, 255, 186, 179);
$format_colored =& $workbook->addFormat();
$format_colored->setFgColor(10);

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
if($result_stg = $db->db_query($qry_stg))
{
	while($row_stg = $db->db_fetch_object($result_stg))
	{
		//Studiengang laden
		$studiengang = new studiengang($row_stg->studiengang_kz);
		$studiengang_kz=$row_stg->studiengang_kz;

		// Creating a worksheet
		$worksheet =& $workbook->addWorksheet($studiengang->kuerzel);
		$worksheet->setInputEncoding('utf-8');
		//echo "Writing $studiengang->kuerzel ...".microtime()."<br>";

		$i=0;
		$gesamtsheet_row++;
		$worksheet->write(0,0,'Erstellt am '.date('d.m.Y').' '.$semester_aktuell.' '.$studiengang->kuerzel, $format_bold);
		$gesamt->write($gesamtsheet_row,0,'Erstellt am '.date('d.m.Y').' '.$semester_aktuell.' '.$studiengang->kuerzel, $format_bold);
		$gesamtsheet_row+=2;
		//Ueberschriften
		$worksheet->write(2,$i,"Studiengang", $format_bold);
		$gesamt->write($gesamtsheet_row,$i,"Studiengang", $format_bold);
		$worksheet->write(2,++$i,"Personalnr", $format_bold);
		$gesamt->write($gesamtsheet_row,$i,"Personalnr", $format_bold);
		$worksheet->write(2,++$i,"Titel", $format_bold);
		$gesamt->write($gesamtsheet_row,$i,"Titel", $format_bold);
		$worksheet->write(2,++$i,"Vorname", $format_bold);
		$gesamt->write($gesamtsheet_row,$i,"Vorname", $format_bold);
		$worksheet->write(2,++$i,"Familienname", $format_bold);
		$gesamt->write($gesamtsheet_row,$i,"Familienname", $format_bold);
		$worksheet->write(2,++$i,"LV-Stunden", $format_bold);
		$gesamt->write($gesamtsheet_row,$i,"LV-Stunden", $format_bold);
		$worksheet->write(2,++$i,"LV-Kosten", $format_bold);
		$gesamt->write($gesamtsheet_row,$i,"LV-Kosten", $format_bold);
		$worksheet->write(2,++$i,"Betreuerstunden", $format_bold);
		$gesamt->write($gesamtsheet_row,$i,"Betreuerstunden", $format_bold);
		$worksheet->write(2,++$i,"Betreuerkosten", $format_bold);
		$gesamt->write($gesamtsheet_row,$i,"Betreuer-Kosten", $format_bold);
		$worksheet->write(2,++$i,"Gesamtstunden", $format_bold);
		$gesamt->write($gesamtsheet_row,$i,"Gesamtstunden", $format_bold);
		$worksheet->write(2,++$i,"Gesamtkosten", $format_bold);
		$gesamt->write($gesamtsheet_row,$i,"Gesamtkosten", $format_bold);
		
		//Daten holen
		$qry = "SELECT
					tbl_lehreinheit.*, tbl_person.vorname, tbl_person.nachname, tbl_person.titelpre,
					tbl_mitarbeiter.personalnummer, tbl_person.person_id, tbl_mitarbeiter.mitarbeiter_uid,
					tbl_lehreinheitmitarbeiter.faktor as faktor, tbl_lehreinheitmitarbeiter.stundensatz as stundensatz,
					tbl_lehreinheitmitarbeiter.semesterstunden as semesterstunden,
					CASE WHEN COALESCE(tbl_lehreinheitmitarbeiter.updateamum, tbl_lehreinheitmitarbeiter.insertamum)>now()-interval '31 days' THEN 't' ELSE 'f' END as geaendert
				FROM
					lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, public.tbl_mitarbeiter,
					public.tbl_benutzer, public.tbl_person, lehre.tbl_lehrveranstaltung
				WHERE
					tbl_person.person_id = tbl_benutzer.person_id AND
					tbl_benutzer.uid=tbl_mitarbeiter.mitarbeiter_uid AND
					tbl_lehreinheitmitarbeiter.mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid AND
					tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
					tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
					studiengang_kz=".$db->db_add_param($studiengang_kz)." AND studiensemester_kurzbz=".$db->db_add_param($semester_aktuell)." AND
					tbl_lehreinheitmitarbeiter.semesterstunden<>0 AND tbl_lehreinheitmitarbeiter.semesterstunden is not null
					AND tbl_lehreinheitmitarbeiter.stundensatz<>0 AND tbl_lehreinheitmitarbeiter.faktor<>0
					AND EXISTS (SELECT lehreinheit_id FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id=tbl_lehreinheit.lehreinheit_id)
					ORDER BY nachname, vorname, tbl_mitarbeiter.mitarbeiter_uid";

		if($result = $db->db_query($qry))
		{
			$zeile=3;
			$gesamtkosten = 0;
			$liste=array();
			$gesamtsheet_row++;
			while($row = $db->db_fetch_object($result))
			{
				//Gesamtstunden und Kosten ermitteln
				if(array_key_exists($row->mitarbeiter_uid, $liste))
				{
					$liste[$row->mitarbeiter_uid]['lvstunden'] = $liste[$row->mitarbeiter_uid]['lvstunden'] + $row->semesterstunden;
					$liste[$row->mitarbeiter_uid]['lvkosten'] = $liste[$row->mitarbeiter_uid]['lvkosten'] + ($row->semesterstunden*$row->stundensatz*$row->faktor);
					$liste[$row->mitarbeiter_uid]['gesamtstunden'] = $liste[$row->mitarbeiter_uid]['gesamtstunden'] + $row->semesterstunden;
					$liste[$row->mitarbeiter_uid]['gesamtkosten'] = $liste[$row->mitarbeiter_uid]['gesamtkosten'] + ($row->semesterstunden*$row->stundensatz*$row->faktor);
				}
				else
				{
					$liste[$row->mitarbeiter_uid]['lvstunden'] = $row->semesterstunden;
					$liste[$row->mitarbeiter_uid]['lvkosten'] = $row->semesterstunden*$row->stundensatz*$row->faktor;
					$liste[$row->mitarbeiter_uid]['gesamtstunden'] = $row->semesterstunden;
					$liste[$row->mitarbeiter_uid]['gesamtkosten'] = $row->semesterstunden*$row->stundensatz*$row->faktor;
				}
				$liste[$row->mitarbeiter_uid]['personalnummer'] = $row->personalnummer;
				$liste[$row->mitarbeiter_uid]['titelpre'] = $row->titelpre;
				$liste[$row->mitarbeiter_uid]['vorname'] = $row->vorname;
				$liste[$row->mitarbeiter_uid]['nachname'] = $row->nachname;
				$liste[$row->mitarbeiter_uid]['betreuergesamtstunden'] = 0;
				$liste[$row->mitarbeiter_uid]['betreuergesamtkosten'] = 0;
				if($row->geaendert=='t')
					$liste[$row->mitarbeiter_uid]['geaendert']=true;
			}

			//Alle holen die eine Betreuung aber keinen Lehrauftrag haben
			$qry = "SELECT 
						distinct personalnummer, titelpre, vorname, nachname, uid
					FROM 
						lehre.tbl_projektbetreuer, public.tbl_person, public.tbl_benutzer, 
						public.tbl_mitarbeiter, lehre.tbl_projektarbeit, lehre.tbl_lehreinheit, 
						lehre.tbl_lehrveranstaltung
					WHERE 
						tbl_projektbetreuer.person_id=tbl_person.person_id AND
						tbl_person.person_id=tbl_benutzer.person_id AND
						tbl_mitarbeiter.mitarbeiter_uid=tbl_benutzer.uid AND
						tbl_projektarbeit.projektarbeit_id=tbl_projektbetreuer.projektarbeit_id AND
						tbl_projektarbeit.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND
						tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($semester_aktuell)." AND
						tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id AND
						tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER)." AND 
						NOT EXISTS (SELECT 
										mitarbeiter_uid 
									FROM 
										lehre.tbl_lehreinheitmitarbeiter, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung
									WHERE 
										mitarbeiter_uid=tbl_benutzer.uid AND
										tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND 
										tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER)." AND
										tbl_lehreinheitmitarbeiter.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND
										tbl_lehreinheitmitarbeiter.semesterstunden<>0 AND
										tbl_lehreinheitmitarbeiter.semesterstunden is not null AND
										tbl_lehreinheitmitarbeiter.stundensatz<>0 AND
										tbl_lehreinheitmitarbeiter.faktor<>0 AND
										EXISTS (SELECT lehreinheit_id FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id=tbl_lehreinheit.lehreinheit_id) AND
										tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($semester_aktuell).");";
			
			if($result = $db->db_query($qry))
			{
				while($row = $db->db_fetch_object($result))
				{
					if(!isset($liste[$row->uid]))
					{
						$liste[$row->uid]['personalnummer'] = $row->personalnummer;
						$liste[$row->uid]['titelpre'] = $row->titelpre;
						$liste[$row->uid]['vorname'] = $row->vorname;
						$liste[$row->uid]['nachname'] = $row->nachname;
						$liste[$row->uid]['geaendert']=false;
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
			foreach ($liste as $uid=>$arr)
			{
				$qry = "SELECT tbl_projektbetreuer.faktor, tbl_projektbetreuer.stunden, tbl_projektbetreuer.stundensatz, CASE WHEN COALESCE(tbl_projektbetreuer.updateamum, tbl_projektbetreuer.insertamum)>now()-interval '31 days' THEN 't' ELSE 'f' END as geaendert
			        FROM lehre.tbl_projektbetreuer, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung,
			               public.tbl_benutzer, lehre.tbl_projektarbeit, campus.vw_student
			        WHERE tbl_projektbetreuer.person_id=tbl_benutzer.person_id AND tbl_benutzer.uid=".$db->db_add_param($uid)." AND
			              tbl_projektarbeit.projektarbeit_id=tbl_projektbetreuer.projektarbeit_id AND student_uid=vw_student.uid
			              AND tbl_lehreinheit.lehreinheit_id=tbl_projektarbeit.lehreinheit_id AND
			              tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($semester_aktuell)." AND tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id AND
			              tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);

				if($result = $db->db_query($qry))
				{
					while($row = $db->db_fetch_object($result))
					{
						$liste[$uid]['gesamtstunden'] = $liste[$uid]['gesamtstunden'] + $row->stunden;
						$liste[$uid]['gesamtkosten'] = $liste[$uid]['gesamtkosten'] + ($row->stunden*$row->stundensatz*$row->faktor);
						$liste[$uid]['betreuergesamtstunden'] = $liste[$uid]['betreuergesamtstunden'] + $row->stunden;
						$liste[$uid]['betreuergesamtkosten'] = $liste[$uid]['betreuergesamtkosten'] + ($row->stunden*$row->stundensatz*$row->faktor);
						if($row->geaendert=='t')
						{
							$liste[$uid]['geaendert']=true;
						}
					}
				}
			}

			$vn = array();
			$nn = array();
			foreach ($liste as $key => $row) 
			{
		    	$vn[$key]  = $row['vorname'];
		    	$nn[$key] = $row['nachname'];
			}
			
			array_multisort($nn, SORT_ASC, $vn, SORT_ASC, $liste);
			
			//Daten ausgeben
			foreach ($liste as $uid=>$row)
			{
				$i=0;
				if(isset($row['geaendert']) && $row['geaendert']==true)
				{
					$format = $format_colored;
					$formatnb = $format_number_colored;
				}
				else
				{
					$format = $format_normal;
					$formatnb = $format_number;
				}
				//Studiengang
				$worksheet->write($zeile,$i,$studiengang->kuerzel, $format);
				$gesamt->write($gesamtsheet_row,$i,$studiengang->kuerzel, $format);
				//Personalnummer
				$worksheet->write($zeile,++$i,$row['personalnummer'], $format);
				$gesamt->write($gesamtsheet_row,$i,$row['personalnummer'], $format);
				//Titel
				$worksheet->write($zeile,++$i,$row['titelpre'], $format);
				$gesamt->write($gesamtsheet_row,$i,$row['titelpre'], $format);
				//Vorname
				$worksheet->write($zeile,++$i,$row['vorname'], $format);
				$gesamt->write($gesamtsheet_row,$i,$row['vorname'], $format);
				//Nachname
				$worksheet->write($zeile,++$i,$row['nachname'], $format);
				$gesamt->write($gesamtsheet_row,$i,$row['nachname'], $format);
				//LVStunden
				$lvstunden = str_replace(',', '.', $row['lvstunden']);
				$worksheet->write($zeile,++$i,$lvstunden, $format);
				$gesamt->write($gesamtsheet_row,$i,$lvstunden, $format);
				//LVKosten
				$lvkosten = str_replace(',', '.', $row['lvkosten']);
				$worksheet->writeNumber($zeile,++$i,$lvkosten, $formatnb);
				$gesamt->writeNumber($gesamtsheet_row,$i,$lvkosten, $formatnb);
				//Betreuerstunden
				$betreuergesamtstunden = str_replace(',', '.', $row['betreuergesamtstunden']);
				$worksheet->write($zeile,++$i,$betreuergesamtstunden, $formatnb);
				$gesamt->write($gesamtsheet_row,$i,$betreuergesamtstunden, $formatnb);
				//Betreuerkosten
				$betreuergesamtkosten = str_replace(',', '.', $row['betreuergesamtkosten']);
				$worksheet->write($zeile,++$i,$betreuergesamtkosten, $formatnb);
				$gesamt->write($gesamtsheet_row,$i,$betreuergesamtkosten, $formatnb);
				//Gesamtstunden
				$gesamtstunden = str_replace(',', '.', $row['gesamtstunden']);
				$worksheet->write($zeile,++$i,$gesamtstunden, $formatnb);
				$gesamt->write($gesamtsheet_row,$i,$gesamtstunden, $formatnb);
				//Gesamtkosten
				$gesamtkosten_row = str_replace(',', '.', $row['gesamtkosten']);
				$worksheet->writeNumber($zeile,++$i,$gesamtkosten_row, $formatnb);
				$gesamt->writeNumber($gesamtsheet_row,$i,$gesamtkosten_row, $formatnb);
				
				
				//Kosten zu den Gesamtkosten hinzurechnen
				$gesamtkosten = $gesamtkosten + $row['gesamtkosten'];
				$zeile++;
				$gesamtsheet_row++;
				
				$liste_gesamt[$uid]['personalnummer']=$row['personalnummer'];
				$liste_gesamt[$uid]['titelpre']=$row['titelpre'];
				$liste_gesamt[$uid]['vorname']=$row['vorname'];
				$liste_gesamt[$uid]['nachname']=$row['nachname'];
				if(isset($liste_gesamt[$uid]['gesamtstunden']))
					$liste_gesamt[$uid]['gesamtstunden']+=$row['gesamtstunden'];
				else 
					$liste_gesamt[$uid]['gesamtstunden']=$row['gesamtstunden'];
					
				if(isset($liste_gesamt[$uid]['gesamtkosten']))
					$liste_gesamt[$uid]['gesamtkosten']+=$row['gesamtkosten'];
				else 
					$liste_gesamt[$uid]['gesamtkosten']=$row['gesamtkosten'];
			}

			//Gesamtkosten anzeigen
			$worksheet->writeNumber($zeile,10,$gesamtkosten, $format_number_bold);
			$gesamt->writeNumber($gesamtsheet_row,10,$gesamtkosten, $format_number_bold);
		}
	}
	
	/*
	// Gesamtliste ueber alle Studiengaenge
	$worksheet =& $workbook->addWorksheet('Gesamt');
	$i=0;
	$gesamtkosten=0;
	$zeile=3;
	
	$worksheet->write(0,0,'Erstellt am '.date('d.m.Y').' '.$semester_aktuell.' Gesamtliste', $format_bold);
	//Ueberschriften
	//$worksheet->write(2,$i,"Studiengang", $format_bold);
	$worksheet->write(2,++$i,"Personalnr", $format_bold);
	$worksheet->write(2,++$i,"Titel", $format_bold);
	$worksheet->write(2,++$i,"Vorname", $format_bold);
	$worksheet->write(2,++$i,"Familienname", $format_bold);
	$worksheet->write(2,++$i,"Stunden", $format_bold);
	$worksheet->write(2,++$i,"Kosten", $format_bold);
	
	$vn = array();
	$nn = array();
	foreach ($liste_gesamt as $key => $row) 
	{
    	$vn[$key]  = $row['vorname'];
    	$nn[$key] = $row['nachname'];
	}
	
	array_multisort($nn, SORT_ASC, $vn, SORT_ASC, $liste_gesamt);

	//Daten ausgeben
	foreach ($liste_gesamt as $uid=>$row)
	{
		$i=0;
		if(isset($row['geaendert']) && $row['geaendert']==true)
		{
			$format = $format_colored;
			$formatnb = $format_number_colored;
		}
		else
		{
			$format = $format_normal;
			$formatnb = $format_number;
		}
		
		//Personalnummer
		$worksheet->write($zeile,++$i,$row['personalnummer'], $format);
		//Titel
		$worksheet->write($zeile,++$i,$row['titelpre'], $format);
		//Vorname
		$worksheet->write($zeile,++$i,$row['vorname'], $format);
		//Nachname
		$worksheet->write($zeile,++$i,$row['nachname'], $format);
		//Stunden
		$worksheet->write($zeile,++$i,$row['gesamtstunden'], $format);
		//Kosten
		$worksheet->writeNumber($zeile,++$i,$row['gesamtkosten'], $formatnb);

		//Kosten zu den Gesamtkosten hinzurechnen
		$gesamtkosten = $gesamtkosten + $row['gesamtkosten'];
		$zeile++;
	}

	//Gesamtkosten anzeigen
	$worksheet->writeNumber($zeile,6,$gesamtkosten, $format_number_bold);
	*/
	
	//Betreuerstunden
	$worksheet =& $workbook->addWorksheet('Betreuerstunden');
	$worksheet->setInputEncoding('utf-8');
	$qry = "SELECT 
				studiensemester_kurzbz, nachname, vorname, sum(stunden) AS stunden, titelpre,
				sum(tbl_projektbetreuer.stundensatz*stunden*tbl_projektbetreuer.faktor)::numeric(6,2) AS euro, person_id 
			FROM 
				public.tbl_person JOIN lehre.tbl_projektbetreuer USING (person_id) 
				JOIN lehre.tbl_projektarbeit USING (projektarbeit_id) 
				JOIN lehre.tbl_lehreinheit USING (lehreinheit_id) 
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE 
				studiensemester_kurzbz=".$db->db_add_param($semester_aktuell)." AND
				stunden>0";

	if(count($stg_arr)>0)
		$qry.=" AND tbl_lehrveranstaltung.studiengang_kz IN(".$db->db_implode4SQL($stg_arr).")";

	$qry.="
			GROUP BY 
				studiensemester_kurzbz,person_id,nachname,vorname, titelpre
			ORDER BY 
				nachname,vorname 
			";
	$i=0;
	$gesamtkosten=0;
	
	$worksheet->write(0,0,'Erstellt am '.date('d.m.Y').' '.$semester_aktuell.' Betreuerstunden', $format_bold);
	//Ueberschriften
	//$worksheet->write(2,$i,"Studiengang", $format_bold);
	$worksheet->write(2,++$i,"Titel", $format_bold);
	$worksheet->write(2,++$i,"Familienname", $format_bold);
	$worksheet->write(2,++$i,"Vorname", $format_bold);
	$worksheet->write(2,++$i,"Stunden", $format_bold);
	$worksheet->write(2,++$i,"Kosten", $format_bold);
		
	$format = $format_normal;
	$formatnb = $format_number;
	if($result = $db->db_query($qry))
	{
		$zeile=3;
		while($row = $db->db_fetch_object($result))
		{
			$i=0;
			//Studiensemester
			$worksheet->write($zeile,++$i,$row->titelpre, $format);
			//Vorname
			$worksheet->write($zeile,++$i,$row->nachname, $format);
			//Nachname
			$worksheet->write($zeile,++$i,$row->vorname, $format);
			//Stunden
			$worksheet->writeNumber($zeile,++$i,$row->stunden, $formatnb);
			//Kosten
			$worksheet->writeNumber($zeile,++$i,$row->euro, $formatnb);
			$zeile++;
			
			$gesamtkosten = $gesamtkosten + $row->euro;
		}
		
		//Gesamtkosten anzeigen
		$worksheet->writeNumber($zeile,5,$gesamtkosten, $format_number_bold);
	}
	
	$workbook->close();

	//Mail versenden mit Excel File im Anhang
    $subject = "Lehrauftragsliste ".date('d.m.Y');
    $message = "Dies ist eine automatische eMail!\n\nAnbei die Lehrauftragslisten vom ".date('d.m.Y');
    $message.= "\n\nJederzeit abrufbar unter ".APP_ROOT.'content/statistik/lehrauftragsliste_mail.xls.php';
	if($oe_kurzbz!='')
		$message.="&oe_kurzbz=".$oe_kurzbz;

    $fileatttype = "application/xls";
    $fileattname = "lehrauftragsliste_".date('Y_m_d').".xls";

	$empfaenger = MAIL_GST;
	if($oe_kurzbz=='lehrgang')
		$empfaenger = MAIL_LG;

    $mail = new mail($empfaenger, 'noreply@'.DOMAIN, $subject, $message);
    $mail->addAttachmentBinary($file, $fileatttype, $fileattname);
    
    if($mail->send())
		echo 'Email mit Lehrauftragslisten wurde an '.$empfaenger.' versandt!';
     else
        echo "Fehler beim Versenden der Lehrauftragsliste";
}

?>
