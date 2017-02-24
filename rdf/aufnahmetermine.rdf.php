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
require_once('../include/rdf.class.php');
require_once('../include/basis_db.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/datum.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/reihungstest.class.php');
require_once('../include/studienplan.class.php');

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

if($prestudent_id!='')
{
	$prestudent = new prestudent();
	if(!$prestudent->load($prestudent_id))
		die($prestudent->errormsg);

	$reihungstest = new reihungstest();
	$reihungstest->getReihungstestPerson($prestudent->person_id);

	foreach($reihungstest->result as $row)
	{
		drawrow($row);
	}
}
elseif($rt_person_id!='')
{
	$reihungstest = new reihungstest();
	if($reihungstest->loadReihungstestPerson($rt_person_id))
		drawrow($reihungstest);
	else
		die($reihungstest->errormsg);
}

function drawrow($row)
{
	global $oRdf, $datum_obj;

	$reihungstest_obj = new reihungstest();
	$reihungstest_obj->load($row->reihungstest_id);

	$studienplan = new studienplan();
	$studienplan->loadStudienplan($row->studienplan_id);

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
	$oRdf->obj[$i]->setAttribut('studiensemester',$reihungstest_obj->studiensemester_kurzbz,true);
	$oRdf->obj[$i]->setAttribut('datum',$datum_obj->formatDatum($reihungstest_obj->datum,'d.m.Y'),true);
	$oRdf->obj[$i]->setAttribut('datum_iso',$reihungstest_obj->datum,true);

	$oRdf->addSequence($row->rt_person_id);
}

$oRdf->sendRdfText();
?>
