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
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/rdf.class.php');
require_once('../include/mantis.class.php');
require_once('../include/functions.inc.php');

get_uid();
$oRdf = new rdf('MANTIS_PROJECT','http://www.technikum-wien.at/mantis_project');
$oRdf->sendHeader();

$mantis = new mantis();
$mantis->getProjects();

foreach($mantis->result as $row)
{	
	$i=$oRdf->newObjekt($row->issue_project->id);
	$oRdf->obj[$i]->setAttribut('id',$row->issue_project->id,true);
	$oRdf->obj[$i]->setAttribut('name',$row->issue_project->name,true);
	
	$oRdf->addSequence($row->issue_project->id);
}

$oRdf->sendRdfText();
?>
