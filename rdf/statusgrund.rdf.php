<?php
/* Copyright (C) 2006 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/rdf.class.php');
require_once('../include/basis_db.class.php');
require_once('../include/statusgrund.class.php');
require_once('../include/functions.inc.php');

$uid = get_uid();

$oRdf = new rdf('AUFWANDSTYP','http://www.technikum-wien.at/statusgrund');
$oRdf->sendHeader();

if(isset($_GET['status_kurzbz']))
	$status_kurzbz = $_GET['status_kurzbz'];
else
	die('Status_kurzbz muss uebergeben werden');

$statusgrund = new statusgrund();
$statusgrund->getFromStatus($status_kurzbz,true);

$i=$oRdf->newObjekt('');
$oRdf->obj[$i]->setAttribut('statusgrund_id','',true);
$oRdf->obj[$i]->setAttribut('status_kurzbz','',true);
$oRdf->obj[$i]->setAttribut('beschreibung','-- keine Auswahl --',true);
$oRdf->obj[$i]->setAttribut('bezeichnung_mehrsprachig','-- keine Auswahl --',true);
$oRdf->addSequence('');

$include_id_found=false;
$include_id = '';
if(isset($_GET['include_id']))
{
	$include_id = $_GET['include_id'];
}
foreach($statusgrund->result as $row)
{
	if($include_id==$row->statusgrund_id)
		$include_id_found=true;

	$i=$oRdf->newObjekt($row->statusgrund_id);
	$oRdf->obj[$i]->setAttribut('statusgrund_id',$row->statusgrund_id,true);
	$oRdf->obj[$i]->setAttribut('status_kurzbz',$row->status_kurzbz,true);
	$oRdf->obj[$i]->setAttribut('beschreibung',$row->beschreibung[DEFAULT_LANGUAGE],true);
	$oRdf->obj[$i]->setAttribut('bezeichnung_mehrsprachig',$row->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE],true);

	$oRdf->addSequence($row->statusgrund_id);
}

if(!$include_id_found && $include_id!='')
{
	$statusgrund->load($include_id);

	$i=$oRdf->newObjekt($statusgrund->statusgrund_id);
	$oRdf->obj[$i]->setAttribut('statusgrund_id',$statusgrund->statusgrund_id,true);
	$oRdf->obj[$i]->setAttribut('status_kurzbz',$statusgrund->status_kurzbz,true);
	$oRdf->obj[$i]->setAttribut('beschreibung',$statusgrund->beschreibung[DEFAULT_LANGUAGE],true);
	$oRdf->obj[$i]->setAttribut('bezeichnung_mehrsprachig',$statusgrund->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE],true);

	$oRdf->addSequence($statusgrund->statusgrund_id);
}

$oRdf->sendRdfText();
?>