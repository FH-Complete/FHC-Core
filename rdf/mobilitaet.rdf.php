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
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/rdf.class.php');
require_once('../include/functions.inc.php');
require_once('../include/basis_db.class.php');
require_once('../include/mobilitaet.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/mobilitaetsprogramm.class.php');
require_once('../include/gsprogramm.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('student/stammdaten', null, 's'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$oRdf = new rdf('MOB','http://www.technikum-wien.at/mobilitaet');
$oRdf->sendHeader();

$mobilitaet = new mobilitaet();

if(isset($_GET['prestudent_id']))
{
	$mobilitaet->loadPrestudent($_GET['prestudent_id']);
}
elseif(isset($_GET['mobilitaet_id']))
{
	if($mobilitaet->load($_GET['mobilitaet_id']))
		$mobilitaet->result[] = $mobilitaet;
}
else
	die('Invalid Parameter');

$mobprog = new mobilitaetsprogramm();
$mobprog->getAll();
$mob_arr=array();
foreach($mobprog->result as $rowmob)
	$mob_arr[$rowmob->mobilitaetsprogramm_code]=$rowmob->kurzbz;

if(count($mobilitaet->result)>0)
{
	foreach($mobilitaet->result as $row)
	{
		$i=$oRdf->newObjekt($row->mobilitaet_id);
		$oRdf->obj[$i]->setAttribut('mobilitaet_id',$row->mobilitaet_id,true);
		$oRdf->obj[$i]->setAttribut('prestudent_id',$row->prestudent_id,true);
		$oRdf->obj[$i]->setAttribut('studiensemester_kurzbz',$row->studiensemester_kurzbz,true);
		$oRdf->obj[$i]->setAttribut('mobilitaetsprogramm_code',$row->mobilitaetsprogramm_code,true);

		if(isset($mob_arr[$row->mobilitaetsprogramm_code]))
			$mob = $mob_arr[$row->mobilitaetsprogramm_code];
		else
			$mob = '';
		$oRdf->obj[$i]->setAttribut('mobilitaetsprogramm',$mob,true);
		$oRdf->obj[$i]->setAttribut('gsprogramm_id',$row->gsprogramm_id,true);

		$gsprogramm = new gsprogramm();
		$gsprogramm->load($row->gsprogramm_id);

		$oRdf->obj[$i]->setAttribut('gsprogrammtyp_kurzbz',$gsprogramm->gsprogrammtyp_kurzbz,true);
		$oRdf->obj[$i]->setAttribut('mobilitaetstyp_kurzbz',$row->mobilitaetstyp_kurzbz,true);
		$oRdf->obj[$i]->setAttribut('firma_id',$row->firma_id,true);
		$oRdf->obj[$i]->setAttribut('status_kurzbz',$row->status_kurzbz,true);
		$oRdf->obj[$i]->setAttribut('ausbildungssemester',$row->ausbildungssemester,true);

		$oRdf->addSequence($row->mobilitaet_id);
	}
}
$oRdf->sendRdfText();
?>
