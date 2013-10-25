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
require_once('../config/vilesci.config.inc.php');
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
	// eine Neue Studienordnung anlegen
	$studienordnung = new studienordnung();
	$studienordnung->studiengang_kz=$rowstg->studiengang_kz;
	$studienordnung->bezeichnung=$rowstg->kuerzel.'_V2';
	$studienordnung->version='V2';
	$studienordnung->ects=30;
	$studienordnung->gueltigvon='WS2013';
	$studienordnung->gueltigbis='';
	$studienordnung->studiengangbezeichnung = $rowstg->bezeichnung;
	$studienordnung->studiengangbezeichnung_englisch = $rowstg->english;
	$studienordnung->studiengangkurzbzlang = $rowstg->kurzbzlang;
	$studienordnung->max_semester = $rowstg->max_semester;
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
				createStudienplan($row->orgform_kurzbz, $studienordnung_id, $rowstg);
			}
		}
	}
	else
	{
		createStudienplan($rowstg->orgform_kurzbz, $studienordnung_id, $rowstg);
	}
}

function createStudienplan($orgform, $studienordnung_id, $rowstg)
{
	global $db;
	$studienplan = new studienplan();
	$studienplan->studienordnung_id = $studienordnung_id;
	$studienplan->orgform_kurzbz=$orgform;
	$studienplan->version = 'V2';
	$studienplan->bezeichnung = $rowstg->kuerzel.'V2';
	$studienplan->regelstudiendauer = $rowstg->max_semester;
	$studienplan->sprache = $rowstg->sprache;
	$studienplan->aktiv = true;
	$studienplan->semesterwochen = 15;
	$studienplan->testtool_sprachwahl = true;//$db->db_parse_bool($rowstg->testtool_sprachwahl);
	$studienplan->insertvon = 'generate';
	if(!$studienplan->save())
	{
		echo 'Studienplan failed for '.$rowstg->studiengang_kz.$studienplan->errormsg;
		continue;
	}
	else
		$studienplan_id = $studienplan->studienplan_id;

	$qry = "SELECT 
				distinct lehrveranstaltung_id, semester, koordinator
			FROM
				lehre.tbl_lehrveranstaltung
			WHERE
				tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($rowstg->studiengang_kz)."
				AND (orgform_kurzbz is null or orgform_kurzbz=".$db->db_add_param($orgform).')';				

	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$lehrveranstaltung = new lehrveranstaltung();
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
