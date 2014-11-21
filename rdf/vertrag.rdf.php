<?php
/* Copyright (C) 2009 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>.
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/rdf.class.php');
require_once('../include/vertrag.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('vertrag/mitarbeiter'))
	die('Sie haben keine Berechtigung für diese Seite');

if(isset($_GET['person_id']))
{
	$person_id=$_GET['person_id'];
	$vertrag = new vertrag();
	if(isset($_GET['filter']) && $_GET['filter']=='offen')
	{
		if(!$vertrag->loadVertrag($person_id, false))
			die('Fehlgeschlagen:'.$vertrag->errormsg);
	}
	else
	{
		if(!$vertrag->loadVertrag($person_id))
			die('Fehlgeschlagen:'.$vertrag->errormsg);
	}
}
elseif(isset($_GET['vertrag_id']))
{
	$vertrag_id = $_GET['vertrag_id'];
	$vertrag = new vertrag();
	if(!$vertrag->load($vertrag_id))
		die('Fehlgeschlagen:'.$vertrag->errormsg);
	$vertrag->result[] = $vertrag;
}


$oRdf = new rdf('VER','http://www.technikum-wien.at/vertrag');
$oRdf->sendHeader();

foreach($vertrag->result as $row)
{	
	$i=$oRdf->newObjekt($row->vertrag_id);
	$oRdf->obj[$i]->setAttribut('vertrag_id',$row->vertrag_id,true);
	$oRdf->obj[$i]->setAttribut('person_id',$row->person_id,true);
	$oRdf->obj[$i]->setAttribut('bezeichnung',$row->bezeichnung,true);
	$oRdf->obj[$i]->setAttribut('vertragstyp_kurzbz',$row->vertragstyp_kurzbz,true);
	$oRdf->obj[$i]->setAttribut('vertragstyp_bezeichnung',$row->vertragstyp_bezeichnung,true);
	$oRdf->obj[$i]->setAttribut('betrag',$row->betrag,true);
	$oRdf->obj[$i]->setAttribut('status',$row->status,true);

	$oRdf->addSequence($row->vertrag_id);
}

$oRdf->sendRdfText();
?>
