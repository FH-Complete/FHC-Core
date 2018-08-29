<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Manfred Kindl <manfred.kindl@technikum-wien.at>
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/dokument_export.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/lehreinheitgruppe.class.php');
require_once('../../../include/lehreinheit.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/lehreinheitmitarbeiter.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/erhalter.class.php');
require_once('../../../include/datum.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user=get_uid();

$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($user);

if(isset($_GET['lvid']) && is_numeric($_GET['lvid']))
	$lvid = $_GET['lvid'];
else
	die('Eine gueltige LvID muss uebergeben werden');

$lv = new lehrveranstaltung();
$lv->load($lvid);

if(isset($_GET['stsem']))
	$studiensemester = $_GET['stsem'];
else
	die('Eine Studiensemester muss uebergeben werden');

if(	!$berechtigung->isBerechtigt('admin')
	&& !$berechtigung->isBerechtigt('assistenz')
	&& !$berechtigung->isBerechtigt('lehre', $lv->oe_kurzbz, 's')
	&& !check_lektor_lehrveranstaltung($user,$lvid,$studiensemester))
	die('Sie muessen LektorIn der LV sein oder das Recht "ADMIN", "ASSISTENZ" oder "LEHRE" haben, um diese Seite aufrufen zu koennen');

$output='pdf';

if(isset($_GET['output']) && ($output='odt' || $output='doc'))
	$output=$_GET['output'];

isset($_GET['stg_kz']) ? $studiengang = $_GET['stg_kz'] : $studiengang = NULL;
isset($_GET['lehreinheit_id']) ? $lehreinheit = $_GET['lehreinheit_id'] : $lehreinheit = NULL;



$doc = new dokument_export('Anwesenheitslist');

// Teilnehmende Gruppen laden
$qry = "SELECT DISTINCT ON(kuerzel, semester, verband, gruppe, gruppe_kurzbz)
			UPPER(stg_typ::varchar(1) || stg_kurzbz) as kuerzel,
			semester,
			verband,
			gruppe,
			gruppe_kurzbz
		FROM campus.vw_lehreinheit
		WHERE lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)."
		AND studiensemester_kurzbz=".$db->db_add_param($studiensemester);
if($lehreinheit!='')
	$qry.=" AND lehreinheit_id=".$db->db_add_param($lehreinheit, FHC_INTEGER);

$gruppen_string = '';
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if($gruppen_string!='')
			$gruppen_string.=', ';
		if($row->gruppe_kurzbz=='')
			$gruppen_string.=trim($row->kuerzel.'-'.$row->semester.$row->verband.$row->gruppe);
		else
			$gruppen_string.=$row->gruppe_kurzbz;
	}
}


// Verplante Räume laden
$qry = "SELECT distinct(ort_kurzbz)
        FROM lehre.tbl_stundenplan
		WHERE lehreinheit_id in
			(
				SELECT lehreinheit_id
				FROM campus.vw_lehreinheit
				WHERE lehrveranstaltung_id = ".$db->db_add_param($lvid, FHC_INTEGER)."
 				AND studiensemester_kurzbz = ".$db->db_add_param($studiensemester)."
			)";
if($lehreinheit!='')
	$qry.= " AND tbl_stundenplan.lehreinheit_id = ".$db->db_add_param($lehreinheit, FHC_INTEGER);


$raum_string = '';
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if($raum_string!='')
			$raum_string.=', ';
		if($row->ort_kurzbz!='')
			$raum_string.=$row->ort_kurzbz;
	}
}


$stg = new studiengang();
$stg->load($lv->studiengang_kz);

$studiengang_bezeichnung=$stg->bezeichnung;

$stg->getAllTypes();

$data = array(
	'gruppen'=>$gruppen_string,
	'bezeichnung'=>$lv->bezeichnung,
	'lehrveranstaltung_id'=>$lv->lehrveranstaltung_id,
	'studiengang'=>$studiengang_bezeichnung,
	'studiengang_kz'=>$lv->studiengang_kz,
	'typ'=>$stg->studiengang_typ_arr[$stg->typ],
	'ects'=>$lv->ects,
	'sprache'=>$lv->sprache,
	'studiensemester'=>$studiensemester,
	'semester'=>$lv->semester,
	'orgform'=>$lv->orgform_kurzbz,
	'raum'=>$raum_string,
);

//Lehrende der LV laden und in ein Array schreiben
$lehrende = new lehreinheitmitarbeiter();
$lehrende->getMitarbeiterLV($lvid, $studiensemester, $lehreinheit);
$arr_lehrende = array();
if (isset($lehrende->result))
{
	foreach($lehrende->result AS $row)
	{
		$data[]=array('lehrende'=>array('uid'=>$row->uid,'name'=>$row->vorname.' '.$row->nachname));
		$arr_lehrende[]=mb_strtoupper($row->uid);
	}
}


//Studierende der LV laden und in ein Array schreiben

$qry = 'SELECT
			distinct on(nachname, vorname, person_id) vorname, nachname, matrikelnr,
			tbl_studentlehrverband.semester, tbl_studentlehrverband.verband, tbl_studentlehrverband.gruppe,
			(SELECT status_kurzbz FROM public.tbl_prestudentstatus WHERE prestudent_id=tbl_student.prestudent_id ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1) as status,
			tbl_bisio.bisio_id, tbl_bisio.von, tbl_bisio.bis, tbl_student.studiengang_kz AS stg_kz_student,
			tbl_zeugnisnote.note, tbl_mitarbeiter.mitarbeiter_uid, tbl_person.matr_nr, tbl_studiengang.kurzbzlang
		FROM
			campus.vw_student_lehrveranstaltung JOIN public.tbl_benutzer USING(uid)
			JOIN public.tbl_person USING(person_id) LEFT JOIN public.tbl_student ON(uid=student_uid)
			LEFT JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid)
			LEFT JOIN public.tbl_studentlehrverband USING(student_uid,studiensemester_kurzbz)
			LEFT JOIN lehre.tbl_zeugnisnote on(vw_student_lehrveranstaltung.lehrveranstaltung_id=tbl_zeugnisnote.lehrveranstaltung_id AND tbl_zeugnisnote.student_uid=tbl_student.student_uid AND tbl_zeugnisnote.studiensemester_kurzbz=tbl_studentlehrverband.studiensemester_kurzbz)
			LEFT JOIN bis.tbl_bisio ON(uid=tbl_bisio.student_uid)
			LEFT JOIN public.tbl_studiengang ON(tbl_student.studiengang_kz=tbl_studiengang.studiengang_kz)
		WHERE
			vw_student_lehrveranstaltung.lehrveranstaltung_id='.$db->db_add_param($lvid, FHC_INTEGER).' AND
			vw_student_lehrveranstaltung.studiensemester_kurzbz='.$db->db_add_param($studiensemester);

if($lehreinheit!='')
	$qry.=' AND vw_student_lehrveranstaltung.lehreinheit_id='.$db->db_add_param($lehreinheit, FHC_INTEGER);

$qry.=' ORDER BY nachname, vorname, person_id, tbl_bisio.bis DESC';

$stsem_obj = new studiensemester();
$stsem_obj->load($studiensemester);
$stsemdatumvon = $stsem_obj->start;
$stsemdatumbis = $stsem_obj->ende;

$erhalter = new erhalter();
$erhalter->getAll();

$a_o_kz = '9'.sprintf("%03s", $erhalter->result[0]->erhalter_kz); //Stg_Kz AO-Studierende auslesen (9005 fuer FHTW)
$anzahl_studierende = 0;
$datum = new datum();
$zusatz = '';

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if($row->status!='Abbrecher' && $row->status!='Unterbrecher')
		{
			$anzahl_studierende++;

			if($row->status=='Incoming') //Incoming
				$zusatz='(i)';
			else
				$zusatz='';

			if($row->bisio_id!='' && $row->status!='Incoming' && ($row->bis > $stsemdatumvon || $row->bis=='') && $row->von < $stsemdatumbis) //Outgoing
				$zusatz.='(o)(ab '.$datum->formatDatum($row->von,'d.m.Y').')';

			if($row->note==6) //angerechnet
				$zusatz.='(ar)';

			if($row->mitarbeiter_uid!='') //mitarbeiter
				$zusatz.='(ma)';

			if($row->stg_kz_student==$a_o_kz) //Außerordentliche Studierende
				$zusatz.='(a.o.)';

			$data[]=array('student'=>array(
							'vorname'=>$row->vorname,
							'nachname'=>$row->nachname,
							'personenkennzeichen'=>trim($row->matrikelnr),
							'matr_nr'=>$row->matr_nr,
							'semester'=>$row->semester,
							'verband'=>trim($row->verband),
							'gruppe'=>trim($row->gruppe),
							'zusatz'=>$zusatz,
							'studiengang_kurzbz'=>$row->kurzbzlang
							));
		}
	}
	//Anzahl Studierende in Array $data (an erster Stelle) einfuegen
	$data = array_reverse($data, true);
	$data['anzahl_studierende'] = $anzahl_studierende;
	$data = array_reverse($data, true);
}

$doc->addDataArray($data,'anwesenheitsliste');
if($lehreinheit!='')
{
	$lehrende = '_'.implode('_',array_unique($arr_lehrende));
}
else
	$lehrende = '';

$doc->setFilename('Anwesenheitsliste_'.$studiensemester.'_'.$stg->kuerzel.'_'.$lv->semester.'_'.$lv->kurzbz.$lehrende);
if(!$doc->create($output))
	die($doc->errormsg);
$doc->output();
$doc->close();
?>
