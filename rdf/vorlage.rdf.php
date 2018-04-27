<?php
/* Copyright (C) 2018 fhcomplete.org
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
require_once('../include/vorlage.class.php');
require_once('../include/functions.inc.php');

$uid = get_uid();

$oRdf = new rdf('VORLAGE','http://www.technikum-wien.at/vorlage');
$oRdf->sendHeader();

$vorlage = new vorlage();
$vorlage->getVorlagenArchiv();

foreach($vorlage->result as $row)
{
	$i=$oRdf->newObjekt($row->vorlage_kurzbz);
	$oRdf->obj[$i]->setAttribut('vorlage_kurzbz', $row->vorlage_kurzbz, true);
	$oRdf->obj[$i]->setAttribut('bezeichnung',$row->bezeichnung,true);

	$oRdf->addSequence($row->vorlage_kurzbz);
}

$oRdf->sendRdfText();
?>
