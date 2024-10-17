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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/projekttask.class.php');
require_once('../include/rdf.class.php');
require_once('../include/datum.class.php');
require_once('../include/ressource.class.php'); 

$datum_obj = new datum();

$projekttask_obj = new projekttask();

$projektphase_id=4; //zum Testen, ansonsten null
if (isset($_GET['projektphase_id']))
{
	if(isset($_GET['filter']))
	{
		$projektphase_id=$_GET['projektphase_id'];
		$filter = $_GET['filter'];
		$projekttask_obj->getProjekttasks($projektphase_id,null,$filter);
	}else
	{
		$projektphase_id=$_GET['projektphase_id'];
		$projekttask_obj->getProjekttasks($projektphase_id);
	}
}
	
if(isset($_GET['projekttask_id']))
{
	$projekttask_obj->load($_GET['projekttask_id']);
	$projekttask_obj->result[] = $projekttask_obj;
}

$oRdf = new rdf('PROJEKTTASK','http://www.technikum-wien.at/projekttask');
$oRdf->sendHeader();

$lastPT=null;
foreach($projekttask_obj->result as $projekttask)
{
	$i=$oRdf->newObjekt($projekttask->projekttask_id);
	
	$oRdf->obj[$i]->setAttribut('projekttask_id',$projekttask->projekttask_id);
	$oRdf->obj[$i]->setAttribut('projektphase_id',$projekttask->projektphase_id);
	$oRdf->obj[$i]->setAttribut('bezeichnung',$projekttask->bezeichnung);
	$oRdf->obj[$i]->setAttribut('beschreibung',$projekttask->beschreibung);
	$oRdf->obj[$i]->setAttribut('aufwand',$projekttask->aufwand);
	$oRdf->obj[$i]->setAttribut('mantis_id',$projekttask->mantis_id);
	$oRdf->obj[$i]->setAttribut('scrumsprint_id',$projekttask->scrumsprint_id);
	$oRdf->obj[$i]->setAttribut('erledigt',($projekttask->erledigt?'true':'false'));
	$oRdf->obj[$i]->setAttribut('projekttask_fk',$projekttask->projekttask_fk);
	$ressource_bezeichnung ='-'; 
	if($projekttask->ressource_id != '')
	{
		$ressource = new ressource(); 
		$ressource->load($projekttask->ressource_id);
		$ressource_bezeichnung = $ressource->bezeichnung; 
	} 
	$oRdf->obj[$i]->setAttribut('ressource_bezeichnung',$ressource_bezeichnung);
	$oRdf->obj[$i]->setAttribut('ressource_id',$projekttask->ressource_id);
	$oRdf->obj[$i]->setAttribut('ende',$datum_obj->formatDatum($projekttask->ende,'d.m.Y'));
	
	if($projekttask->projekttask_fk!='')
		$oRdf->addSequence($projekttask->projekttask_id, $projekttask->projekttask_fk);
	else
		$oRdf->addSequence($projekttask->projekttask_id);
}
$oRdf->sendRdfText();
?>
