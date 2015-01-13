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
require_once('../include/datum.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('vertrag/mitarbeiter'))
	die('Sie haben keine Berechtigung für diese Seite');

if(isset($_GET['vertrag_id']))
	$vertrag_id=$_GET['vertrag_id'];
else
	die('Falsche Parameterübergabe');

$datum_obj = new datum();

$vertrag = new vertrag();
if(!$vertrag->getAllStatus($vertrag_id))
	die('Fehlgeschlagen:'.$vertrag->errormsg);

$oRdf = new rdf('VER','http://www.technikum-wien.at/vertragsstatus');
$oRdf->sendHeader();

foreach($vertrag->result as $row)
{	
	$key = $row->vertragsstatus_kurzbz.'/'.$row->vertrag_id;
	$i=$oRdf->newObjekt($key);
	$oRdf->obj[$i]->setAttribut('vertrag_id',$row->vertrag_id,true);
	$oRdf->obj[$i]->setAttribut('vertragsstatus_kurzbz',$row->vertragsstatus_kurzbz,true);
	$oRdf->obj[$i]->setAttribut('vertragsstatus_bezeichnung',$row->vertragsstatus_bezeichnung,true);
	$oRdf->obj[$i]->setAttribut('datum',$datum_obj->formatDatum($row->datum,'d.m.Y H:i'),true);
	$oRdf->obj[$i]->setAttribut('datum_iso',$row->datum,true);	
	$oRdf->obj[$i]->setAttribut('uid',$row->uid,true);
	$oRdf->obj[$i]->setAttribut('insertvon', $row->insertvon, true);
	$oRdf->obj[$i]->setAttribut('insertamum', $row->insertamum, true);
	$oRdf->obj[$i]->setAttribut('updatevon', $row->updatevon, true);
	$oRdf->obj[$i]->setAttribut('updateamum', $row->updateamum, true);

	$oRdf->addSequence($key);
}

$oRdf->sendRdfText();
?>
