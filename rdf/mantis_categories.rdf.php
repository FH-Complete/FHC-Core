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

if(!isset($_GET['project_id']))
	die('Missing Parameter: Project_id');

$project_id=$_GET['project_id'];

$oRdf = new rdf('MANTIS_CATEGORIES','http://www.technikum-wien.at/mantis_categories');
$oRdf->sendHeader();

$mantis = new mantis();
if($mantis->getCategories($project_id))
{

	foreach($mantis->result as $row)
	{	
		$i=$oRdf->newObjekt($row->issue_category);
		$oRdf->obj[$i]->setAttribut('category',$row->issue_category,true);
		
		$oRdf->addSequence($row->issue_category);
	}
}
else
	echo $mantis->errormsg;
$oRdf->sendRdfText();
?>
