<?php
/* Copyright (C) 2016 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <oesi@technikum-wien.at>
 */
require_once('../config/vilesci.config.inc.php');
require_once('../config/global.config.inc.php');
require_once('../include/rdf.class.php');
require_once('../include/basis_db.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/datum.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/pruefling.class.php');
require_once('../include/reihungstest.class.php');
require_once('../include/studienplan.class.php');
require_once('../include/studienordnung.class.php');
require_once('../include/studiengang.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('assistenz') && !$rechte->isBerechtigt('lvplan') && !$rechte->isBerechtigt('admin'))
	die($rechte->errormsg);

$datum_obj = new datum();

$oRdf = new rdf('AUFNAHMETERMINE','http://www.technikum-wien.at/aufnahmetermine');

$prestudent_id = filter_input(INPUT_GET, 'prestudent_id');

$rt_person_id = filter_input(INPUT_GET, 'rt_person_id');

$oRdf->sendHeader();
$reihungstest_obj_arr = array();
$studienplan_obj_arr = array();
$studienordnung_obj_arr = array();
$stsem_arr = array();
$youngest_rt_stsem = '';
$zuordnung_fuer_selben_studiengang = array();

if($prestudent_id!='')
{
	$prestudent = new prestudent();
	if(!$prestudent->load($prestudent_id))
		die($prestudent->errormsg);

	$reihungstest = new reihungstest();
	$reihungstest->getReihungstestPerson($prestudent->person_id);

	foreach($reihungstest->result as $row)
	{
		// Reihungstest laden
		if(!isset($reihungstest_obj_arr[$row->reihungstest_id]))
		{
			$reihungstest_obj_arr[$row->reihungstest_id] = new reihungstest();
			$reihungstest_obj_arr[$row->reihungstest_id]->load($row->reihungstest_id);
		}

		// Studienplan laden
		if(!isset($studienplan_obj_arr[$row->studienplan_id]))
		{
			$studienplan_obj_arr[$row->studienplan_id] = new studienplan();
			$studienplan_obj_arr[$row->studienplan_id]->loadStudienplan($row->studienplan_id);
		}

		// Studienordnung laden
		$studienordnung_id = $studienplan_obj_arr[$row->studienplan_id]->studienordnung_id;
		if(!isset($studienordnung_obj_arr[$studienordnung_id]))
		{
			$studienordnung_obj_arr[$studienordnung_id] = new studienordnung();
			$studienordnung_obj_arr[$studienordnung_id]->loadStudienordnung($studienordnung_id);
		}

		// Pruefen ob das ein Reihungstest fuer den Studiengang des Prestudenten ist
		if($studienordnung_obj_arr[$studienordnung_id]->studiengang_kz == $prestudent->studiengang_kz)
		{
			$zuordnung_fuer_selben_studiengang[] = $row->rt_person_id;
			$stsem_arr[] = $reihungstest_obj_arr[$row->reihungstest_id]->studiensemester_kurzbz;
		}

		// Studiengangstyp ermitteln
		$stpl = new Studienplan();
		$stpl->loadStudienplan($row->studienplan_id);	// Studienplan von RT-Person
		$rtp_sto_id = $stpl->studienordnung_id;	// Studienordnung-ID von RT-Person
		$sto = new Studienordnung();
		$sto->loadStudienordnung($rtp_sto_id);
		$studiengang_kz = $sto->studiengang_kz;	// Studiengang-KZ von RT-Person
		$stg = new Studiengang($studiengang_kz);
		$typ = $stg->typ;	// Studiengangstyp von RT-Person

		// Reihungstestpunkte der Basisgebiete ermitteln (ohne Quereinsteiger)
		$pruefling = new Pruefling();
		$endpunkte_inkl_gebiete = 0;
		$endpunkte_exkl_gebiete = 0;

		// * Endpunkte über alle Basisgebiete
		if(defined('FAS_REIHUNGSTEST_PUNKTE') && FAS_REIHUNGSTEST_PUNKTE)
		{
			$endpunkte_inkl_gebiete = $pruefling->getReihungstestErgebnisPerson($row->person_id, true, $row->reihungstest_id, false, $row->studiengang_kz, $studiengang_kz);
		}
		else
		{
			$endpunkte_inkl_gebiete = $pruefling->getReihungstestErgebnisPerson($row->person_id, false, $row->reihungstest_id, false, $row->studiengang_kz, $studiengang_kz);
		}

		// * ggf. Endpunkte exklusive bestimmter Gebiete, die in der config-Datei gesetzt sind
		if (defined('FAS_REIHUNGSTEST_EXCLUDE_GEBIETE') && !empty(FAS_REIHUNGSTEST_EXCLUDE_GEBIETE))
		{
			if(defined('FAS_REIHUNGSTEST_PUNKTE') && FAS_REIHUNGSTEST_PUNKTE)
			{
				$endpunkte_exkl_gebiete = $pruefling->getReihungstestErgebnisPerson($row->person_id, true, $row->reihungstest_id, true, $row->studiengang_kz, $studiengang_kz);
			}
			else
			{
				$endpunkte_exkl_gebiete = $pruefling->getReihungstestErgebnisPerson($row->person_id, false, $row->reihungstest_id, true, $row->studiengang_kz, $studiengang_kz);
			}
		}

		$row->endpunkte_inkl_gebiete = round($endpunkte_inkl_gebiete, 2);
		$row->endpunkte_exkl_gebiete = round($endpunkte_exkl_gebiete, 2);
		$row->typ = $typ;
	}
	if(count($stsem_arr) > 0)
	{
		$studiensemester = new studiensemester();
		$youngest_rt_stsem = $studiensemester->getYoungestFromArray($stsem_arr);
	}

	foreach($reihungstest->result as $row)
	{
		drawrow($row);
	}
}
elseif($rt_person_id!='')
{
	$reihungstest = new reihungstest();
	if($reihungstest->loadReihungstestPerson($rt_person_id))
	{
		$reihungstest_obj_arr[$reihungstest->reihungstest_id] = new reihungstest();
		$reihungstest_obj_arr[$reihungstest->reihungstest_id]->load($reihungstest->reihungstest_id);

		// Studiengangstyp ermitteln
		$stpl = new Studienplan();
		$stpl->loadStudienplan($reihungstest->studienplan_id);	// Studienplan von RT-Person
		$rtp_sto_id = $stpl->studienordnung_id;	// Studienordnung-ID von RT-Person
		$sto = new Studienordnung();
		$sto->loadStudienordnung($rtp_sto_id);
		$studiengang_kz = $sto->studiengang_kz;	// Studiengang-KZ von RT-Person
		$stg = new Studiengang($studiengang_kz);
		$typ = $stg->typ;	// Studiengangstyp von RT-Person

		// Reihungstestpunkte der Basisgebiete ermitteln (ohne Quereinsteiger)
		$pruefling = new Pruefling();
		$endpunkte_inkl_gebiete = 0;
		$endpunkte_exkl_gebiete = 0;

		// * Endpunkte über alle Basisgebiete
		if(defined('FAS_REIHUNGSTEST_PUNKTE') && FAS_REIHUNGSTEST_PUNKTE)
		{
			$endpunkte_inkl_gebiete = $pruefling->getReihungstestErgebnisPerson($reihungstest->person_id, true, $reihungstest->reihungstest_id, false, $studiengang_kz, $studiengang_kz);
		}
		else
		{
			$endpunkte_inkl_gebiete = $pruefling->getReihungstestErgebnisPerson($reihungstest->person_id, false, $reihungstest->reihungstest_id, false, $studiengang_kz, $studiengang_kz);
		}
		// * ggf. Endpunkte exklusive bestimmter Gebiete, die in der config-Datei gesetzt sind
		if (defined('FAS_REIHUNGSTEST_EXCLUDE_GEBIETE') && !empty(FAS_REIHUNGSTEST_EXCLUDE_GEBIETE))
		{
			if(defined('FAS_REIHUNGSTEST_PUNKTE') && FAS_REIHUNGSTEST_PUNKTE)
			{
				$endpunkte_exkl_gebiete = $pruefling->getReihungstestErgebnisPerson($reihungstest->person_id, true, $reihungstest->reihungstest_id, true, $studiengang_kz, $studiengang_kz);
			}
			else
			{
				$endpunkte_exkl_gebiete = $pruefling->getReihungstestErgebnisPerson($reihungstest->person_id, false, $reihungstest->reihungstest_id, true, $studiengang_kz, $studiengang_kz);
			}
		}

		$reihungstest->endpunkte_inkl_gebiete = round($endpunkte_inkl_gebiete, 2);
		$reihungstest->endpunkte_exkl_gebiete = round($endpunkte_exkl_gebiete, 2);
		$reihungstest->typ = $typ;

		drawrow($reihungstest);
	}
	else
		die($reihungstest->errormsg);
}

function drawrow($row)
{
	global $oRdf, $datum_obj, $reihungstest_obj_arr, $youngest_rt_stsem, $zuordnung_fuer_selben_studiengang;
	global $studienplan_obj_arr, $studienordnung_obj_arr;

	$reihungstest_obj = $reihungstest_obj_arr[$row->reihungstest_id];

	if(!isset($studienplan_obj_arr[$row->studienplan_id]))
	{
		$studienplan = new studienplan();
		$studienplan->loadStudienplan($row->studienplan_id);
	}
	else
	{
		$studienplan = $studienplan_obj_arr[$row->studienplan_id];
	}

	if(!isset($studienordnung_obj_arr[$studienplan->studienordnung_id]))
	{
		$studienordnung = new studienordnung();
		$studienordnung->loadStudienordnung($studienplan->studienordnung_id);
	}
	else
		$studienordnung = $studienordnung_obj_arr[$studienplan->studienordnung_id];

	$stpl_stg = new studiengang();
	$stpl_stg->load($studienordnung->studiengang_kz);

	$i=$oRdf->newObjekt($row->rt_person_id);
	$oRdf->obj[$i]->setAttribut('rt_person_id',$row->rt_person_id,true);
	$oRdf->obj[$i]->setAttribut('rt_id',$row->reihungstest_id,true);
	$oRdf->obj[$i]->setAttribut('person_id',$row->person_id,true);
	$oRdf->obj[$i]->setAttribut('anmeldedatum',$datum_obj->formatDatum($row->anmeldedatum, 'd.m.Y'),true);
	$oRdf->obj[$i]->setAttribut('anmeldedatum_iso',$row->anmeldedatum,true);
	$oRdf->obj[$i]->setAttribut('teilgenommen',($row->teilgenommen?'Ja':'Nein'),true);
	$oRdf->obj[$i]->setAttribut('punkte',$row->punkte,true);
	$oRdf->obj[$i]->setAttribut('ort_kurzbz',$row->ort_kurzbz,true);
	$oRdf->obj[$i]->setAttribut('anmerkung',$reihungstest_obj->anmerkung,true);
	$oRdf->obj[$i]->setAttribut('stufe',$reihungstest_obj->stufe,true);
	$oRdf->obj[$i]->setAttribut('studienplan_id',$row->studienplan_id,true);
	$oRdf->obj[$i]->setAttribut('studienplan_bezeichnung',$studienplan->bezeichnung,true);
	$oRdf->obj[$i]->setAttribut('studienplan_studiengang_kz',$stpl_stg->studiengang_kz,true);
	$oRdf->obj[$i]->setAttribut('studienplan_studiengang',$stpl_stg->kuerzel,true);
	$oRdf->obj[$i]->setAttribut('studiensemester',$reihungstest_obj->studiensemester_kurzbz,true);
	$oRdf->obj[$i]->setAttribut('datum',$datum_obj->formatDatum($reihungstest_obj->datum,'d.m.Y'),true);
	$oRdf->obj[$i]->setAttribut('datum_iso',$reihungstest_obj->datum,true);
	$oRdf->obj[$i]->setAttribut('endpunkte_inkl_gebiete', $row->endpunkte_inkl_gebiete, true);
	$oRdf->obj[$i]->setAttribut('endpunkte_exkl_gebiete', $row->endpunkte_exkl_gebiete, true);
	$oRdf->obj[$i]->setAttribut('typ', $row->typ, true);

	// Es wird der neueste Reihungstest im Studiengang des Prestudenten markiert damit im FAS erkennbar ist welches
	// Eintraege zur Punkteberechnung verwendet werden
	if($reihungstest_obj->studiensemester_kurzbz == $youngest_rt_stsem
		&& in_array($row->rt_person_id,$zuordnung_fuer_selben_studiengang))
		$oRdf->obj[$i]->setAttribut('properties','makeItMarked',true);
	else
		$oRdf->obj[$i]->setAttribut('properties','',true);

	$oRdf->addSequence($row->rt_person_id);
}

$oRdf->sendRdfText();
?>
