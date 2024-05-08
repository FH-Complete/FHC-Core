<?php
/* Copyright (C) 2013 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 */
require_once('../config/system.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/studiengang.class.php');
require_once('../include/studienordnung.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/studienplan.class.php');
require_once('../include/lehrveranstaltung.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('admin'))
	die('Keine berechtigung');

$studiengang = new studiengang();
$studiengang->getAll('typ, kurzbz',true);
$db = new basis_db();

// Alle Studiengaenge durchlaufen
foreach($studiengang->result as $rowstg)
{
/*
	$qry = "SELECT
				studiensemester_kurzbz
			FROM
				lehre.tbl_lehrveranstaltung
				JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id)
				JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
			WHERE
				tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($rowstg->studiengang_kz, FHC_INTEGER)."
			ORDER BY tbl_studiensemester.start LIMIT 1";
*/
	$stsem = 'WS2013';
/*
	if($result = $db->db_query($qry))
	{
		if($row = $db->db_fetch_object($result))
		{
			$stsem = $row->studiensemester_kurzbz;
		}
	}
*/
	// eine Neue Studienordnung anlegen
	$studienordnung = new studienordnung();
	$studienordnung->studiengang_kz=$rowstg->studiengang_kz;
	$studienordnung->bezeichnung=sprintf('%04s',$rowstg->studiengang_kz).'-'.$rowstg->kuerzel.'-'.$stsem;
	$studienordnung->version='01';
	$studienordnung->ects=($rowstg->max_semester*30);
	$studienordnung->gueltigvon=$stsem;
	$studienordnung->gueltigbis='';
	$studienordnung->studiengangbezeichnung = $rowstg->bezeichnung;
	$studienordnung->studiengangbezeichnung_englisch = $rowstg->english;
	$studienordnung->studiengangkurzbzlang = $rowstg->kurzbzlang;
	$studienordnung->insertvon = 'generate';
	$studienordnung->akadgrad_id=1;
	if(!$studienordnung->save())
	{
		echo 'Fehler bei Stg'.$rowstg->studiengang_kz.$studienordnung->errormsg;
		continue;
	}
	else
		$studienordnung_id = $studienordnung->studienordnung_id;

	// Studienplan anlegen
	if($rowstg->mischform)
	{
		$qry = "SELECT
					distinct orgform_kurzbz
				FROM
					lehre.tbl_lehrveranstaltung
				WHERE
					tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($rowstg->studiengang_kz);

		if($result = $db->db_query($qry))
		{
			while($row = $db->db_fetch_object($result))
			{
				if($row->orgform_kurzbz!='')
					createStudienplan($row->orgform_kurzbz, $studienordnung_id, $rowstg, $studienordnung->bezeichnung);
			}
		}
	}
	else
	{
		createStudienplan($rowstg->orgform_kurzbz, $studienordnung_id, $rowstg, $studienordnung->bezeichnung);
	}
}

/*
$qry = "SELECT * FROM public.tbl_studiensemester WHERE begin<now()";

$stsem = array();
if($result_stsem = $db->db_query($qry))
{
	while($row_stsem = $db->db_fetch_object($result_stsem))
	{
		$stsem[] = $row_stsem->studiensemester_kurzbz;
	}
}
*/
$qry="SELECT *, (Select max_semester FROM public.tbl_studiengang where studiengang_kz=a.studiengang_kz) as max_semester FROM lehre.tbl_studienordnung as a WHERE studienordnung_id=(Select max(studienordnung_id) FROM lehre.tbl_studienordnung WHERE studiengang_kz=a.studiengang_kz)";

if($result_sto = $db->db_query($qry))
{
	while($row_sto = $db->db_fetch_object($result_sto))
	{
		echo $row_sto->bezeichnung.'<br>';
		for($i=1;$i<=$row_sto->max_semester;$i++)
		{
			$qry="INSERT INTO lehre.tbl_studienordnung_semester(studienordnung_id, semester, studiensemester_kurzbz)
				VALUES(".$db->db_add_param($row_sto->studienordnung_id).','.$i.',';
			$stsem_arr=array('WS2013','SS2014','WS2014');
			foreach($stsem_arr as $studiensemester)
			{
				$db->db_query($qry.$db->db_add_param($studiensemester).');');
			}
		}
	}
}

function createStudienplan($orgform, $studienordnung_id, $rowstg, $studienordnungbezeichnung)
{
	global $db;
	$studienplan = new studienplan();
	$studienplan->studienordnung_id = $studienordnung_id;
	$studienplan->orgform_kurzbz=$orgform;
	$studienplan->version = 'V1';
	$studienplan->bezeichnung = $studienordnungbezeichnung.'-'.$orgform;
	$studienplan->regelstudiendauer = $rowstg->max_semester;
	$studienplan->sprache = $rowstg->sprache;
	$studienplan->aktiv = true;

	$wochen='15';
	$qry = "SELECT wochen FROM public.tbl_semesterwochen WHERE studiengang_kz=".$db->db_add_param($rowstg->studiengang_kz)." LIMIT 1";
	if($result = $db->db_query($qry))
	{
		if($row = $db->db_fetch_object($result))
		{
			$wochen = $row->wochen;
		}
	}

	$studienplan->semesterwochen = $wochen;
	$studienplan->testtool_sprachwahl = true;//$db->db_parse_bool($rowstg->testtool_sprachwahl);
	$studienplan->insertvon = 'generate';
	if(!$studienplan->save())
	{
		echo 'Studienplan failed for '.$rowstg->studiengang_kz.$studienplan->errormsg;
		return false;
	}
	else
		$studienplan_id = $studienplan->studienplan_id;

	$qry = "SELECT
				distinct lehrveranstaltung_id, semester, koordinator
			FROM
				lehre.tbl_lehrveranstaltung
			WHERE
				tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($rowstg->studiengang_kz)."
				AND (orgform_kurzbz is null or orgform_kurzbz=".$db->db_add_param($orgform).")
				AND lehrtyp_kurzbz<>'lf'
				AND tbl_lehrveranstaltung.aktiv";

	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$lehrveranstaltung = new studienplan();
			$lehrveranstaltung->new=true;
			$lehrveranstaltung->studienplan_id=$studienplan_id;
			$lehrveranstaltung->semester=$row->semester;
			$lehrveranstaltung->pflicht=true;
			$lehrveranstaltung->koordinator = $row->koordinator;
			$lehrveranstaltung->lehrveranstaltung_id=$row->lehrveranstaltung_id;
			$lehrveranstaltung->insertvon='generate';
			$lehrveranstaltung->saveStudienplanLehrveranstaltung();
		}
	}
}

?>
