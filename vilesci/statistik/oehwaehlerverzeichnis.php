<?php
/* Copyright (C) 2014 fhcomplete.org
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
 *
 */
/**
 * CSV Export des OEH Waehlerverzeichnis
 * Zur Meldung an das BMWFW
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/erhalter.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/datum.class.php');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('admin'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$db = new basis_db();
$datum_obj = new datum();

$stsem_obj = new studiensemester();
$studiensemester_kurzbz = $stsem_obj->getAktOrNext();

$erhalter = new erhalter();
$erhalter->getAll();

if(!isset($erhalter->result[0]))
	die('Es ist kein Erhalter vorhanden');

$erhalter_row = $erhalter->result[0];

$filename=sprintf('%1$03d',$erhalter_row->erhalter_kz).'_'.str_replace(' ','_',$erhalter_row->bezeichnung).'.csv';

header( 'Content-Type: text/csv' );
header( 'Content-Disposition: attachment;filename='.$filename);


// Daten holen - Alle Personen mit akt. Status Student, Diplomand und Incoming die bezahlt haben
$qry="
SELECT * FROM (
SELECT DISTINCT ON (matrikelnr) matrikelnr AS personenkennzeichen,
	tbl_person.svnr,
	tbl_person.ersatzkennzeichen,
	tbl_person.gebdatum,
	tbl_person.nachname,
	tbl_person.vorname,
	tbl_person.geschlecht,
	tbl_student.studiengang_kz,
	tbl_student.student_uid,
	(SELECT plz FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY zustelladresse desc  LIMIT 1) AS zustell_plz,
	(SELECT gemeinde FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY zustelladresse desc  LIMIT 1) AS zustell_ort,
	(SELECT strasse FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY zustelladresse desc  LIMIT 1) AS zustell_strasse,
	(SELECT plz FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse desc LIMIT 1) AS heimat_plz,
	(SELECT gemeinde FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse desc LIMIT 1) AS heimat_ort,
	(SELECT strasse FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse desc LIMIT 1) AS heimat_strasse,
	tbl_person.person_id
FROM public.tbl_person
	JOIN public.tbl_konto as ka using(person_id)
	JOIN public.tbl_konto as kb using(person_id)
	JOIN public.tbl_benutzer using(person_id)
	JOIN public.tbl_student on(uid=student_uid)
	JOIN public.tbl_prestudent using(prestudent_id)
	JOIN public.tbl_prestudentstatus on(tbl_prestudentstatus.prestudent_id=tbl_student.prestudent_id)
WHERE
	tbl_prestudentstatus.studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)."
	AND get_rolle_prestudent(tbl_prestudent.prestudent_id, ".$db->db_add_param($studiensemester_kurzbz).") in('Student','Diplomand','Incoming','Absolvent')
	AND tbl_student.studiengang_kz<10000
	AND ka.studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." AND ka.buchungstyp_kurzbz in('OEH','Lehrgangsgebuehr') AND tbl_student.studiengang_kz=ka.studiengang_kz
	AND kb.studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." AND kb.buchungstyp_kurzbz in('OEH','Lehrgangsgebuehr') AND tbl_student.studiengang_kz=kb.studiengang_kz
	AND kb.buchungsnr_verweis=ka.buchungsnr AND bismelden
) a
ORDER BY person_id";

$last_person_id='';
$data_row = array();
$personenkennzeichen = array();
$studiengang_kz = array();

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if($last_person_id!=$row->person_id && $last_person_id!='')
		{
			// Personen die in mehreren Studiengaengen gleichzeitig studieren werden nur einmal in die Liste aufgenommen
			// dabei werden beide Personenkennzeichen und Studiengangskennzahlen mit ; getrennt
			$data_row[2]=implode(';',$personenkennzeichen);
			$data_row[8]=implode(';',$studiengang_kz);

			echo implode('|',$data_row)."|\r\n";
			$data_row = array();
			$personenkennzeichen = array();
			$studiengang_kz = array();
		}

		$last_person_id=$row->person_id;

		$personenkennzeichen[]=trim($row->personenkennzeichen);
		if($row->studiengang_kz<0)
			$studiengang_kz[] = sprintf('%1$03d',$erhalter_row->erhalter_kz).sprintf('%1$04d',abs($row->studiengang_kz));
		else
			$studiengang_kz[] = sprintf('%1$04d',$row->studiengang_kz);

		$data_row = array(
			sprintf('%1$03d',$erhalter_row->erhalter_kz),
			$erhalter_row->bezeichnung,
			null,
			($row->svnr!=''?$row->svnr:$row->ersatzkennzeichen),
			$datum_obj->formatDatum($row->gebdatum,'Ymd'),
			$row->nachname,
			$row->vorname,
			($row->geschlecht=='m'?'M':($row->geschlecht=='w'?'W':'X')),
			null,
			$row->zustell_plz,
			$row->zustell_ort,
			$row->zustell_strasse,
			$row->heimat_plz,
			$row->heimat_ort,
			$row->heimat_strasse,
			$row->student_uid.'@'.DOMAIN
			);

	}
	$data_row[2]=implode(';',$personenkennzeichen);
	$data_row[8]=implode(';',$studiengang_kz);

	echo implode('|',$data_row)."|\r\n";
}

