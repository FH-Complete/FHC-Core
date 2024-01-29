<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
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

$datum_obj = new datum();

$vertrag = new vertrag();
if(!$vertrag->getAllVertragstypen())
	die('Fehlgeschlagen:'.$vertrag->errormsg);

$oRdf = new rdf('VT','http://www.technikum-wien.at/vertragstyp');
$oRdf->sendHeader();

foreach($vertrag->result as $row)
{	
	$key = $row->vertragstyp_kurzbz;
	$i=$oRdf->newObjekt($key);
	$oRdf->obj[$i]->setAttribut('vertragstyp_kurzbz',$row->vertragstyp_kurzbz,true);
	$oRdf->obj[$i]->setAttribut('vertragstyp_bezeichnung',$row->vertragstyp_bezeichnung,true);

	$oRdf->addSequence($key);
}

$oRdf->sendRdfText();
?>
