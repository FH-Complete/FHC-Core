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
 * Authors: Andreas Oesterreicher <oesi@technikum-wien.at>
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/rdf.class.php');
require_once('../include/basis_db.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/variable.class.php');
require_once('../include/lehrstunde.class.php');
require_once('../include/datum.class.php');
require_once('../include/stunde.class.php');
require_once('../include/anwesenheit.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('student/anwesenheit'))
	die($rechte->errormsg);

$variable = new variable();
$variable->loadVariables($user);

$datum_obj = new datum();

$oRdf = new rdf('ANWESENHEIT','http://www.technikum-wien.at/anwesenheit');

$student_uid = filter_input(INPUT_GET,'student_uid');
$lehrveranstaltung_id = filter_input(INPUT_GET,'lehrveranstaltung_id');
$studiensemester_kurzbz = filter_input(INPUT_GET,'studiensemester_kurzbz');
if($studiensemester_kurzbz=='')
	$studiensemester_kurzbz=$variable->variable->semester_aktuell;

$oRdf->sendHeader();
$db = new basis_db();

$anwesenheit = new anwesenheit();
if($student_uid!='')
	$anwesenheit->loadAnwesenheitStudiensemester($studiensemester_kurzbz, $student_uid);
elseif($lehrveranstaltung_id!='')
	$anwesenheit->loadAnwesenheitStudiensemester($studiensemester_kurzbz,null,$lehrveranstaltung_id);

$i=0;
if(isset($anwesenheit->result) && is_array($anwesenheit->result))
{
	foreach($anwesenheit->result as $row)
	{
		$i=$oRdf->newObjekt($i);
		$oRdf->obj[$i]->setAttribut('lehrveranstaltung_bezeichnung',$row->bezeichnung,true);
		$oRdf->obj[$i]->setAttribut('prozent',$row->prozent,true);
		$oRdf->obj[$i]->setAttribut('anwesend',$row->anwesend,true);
		$oRdf->obj[$i]->setAttribut('nichtanwesend',$row->nichtanwesend,true);
		$oRdf->obj[$i]->setAttribut('vorname',$row->vorname,true);
		$oRdf->obj[$i]->setAttribut('nachname',$row->nachname,true);
		$oRdf->obj[$i]->setAttribut('wahlname',$row->wahlname,true);
		$oRdf->obj[$i]->setAttribut('uid',$row->uid,true);


		$ampel='makeIt'.$anwesenheit->getAmpel($row->prozent);
		$oRdf->obj[$i]->setAttribut('ampel',$ampel,true);

		$oRdf->addSequence($i);
		$i++;
	}
}
$oRdf->sendRdfText();
?>
