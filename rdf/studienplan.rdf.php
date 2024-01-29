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
require_once('../include/studienplan.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('assistenz') && !$rechte->isBerechtigt('admin'))
	die($rechte->errormsg);

$oRdf = new rdf('STUDIENPLAN','http://www.technikum-wien.at/studienplan');

$person_id = filter_input(INPUT_GET,'person_id');

if($person_id=='')
	die('person_id muss uebergeben werden');

$oRdf->sendHeader();
$db = new basis_db();

$studienplan = new studienplan();
$studienplan->getStudienplaenePerson($person_id);

$i=0;
if(isset($studienplan->result) && is_array($studienplan->result))
{
	foreach($studienplan->result as $row)
	{
		$i=$oRdf->newObjekt($row->studienplan_id);
		$oRdf->obj[$i]->setAttribut('studienplan_id',$row->studienplan_id,true);
		$oRdf->obj[$i]->setAttribut('bezeichnung',$row->bezeichnung,true);
		$oRdf->addSequence($row->studienplan_id);
		$i++;
	}
}
$oRdf->sendRdfText();
?>
