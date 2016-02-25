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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/rdf.class.php');
require_once('../include/dokument.class.php');

$oRdf = new rdf('DOKUMENTTYP','http://www.technikum-wien.at/dokumenttyp');
$oRdf->sendHeader();

$ohne_dok=isset($_REQUEST["ohne_dok"])?$_REQUEST["ohne_dok"]:'';

$dokumente = new dokument();
$dokumente->getAllDokumente($ohne_dok);

foreach($dokumente->result as $row)
{	
		$i=$oRdf->newObjekt($row->dokument_kurzbz);
		$oRdf->obj[$i]->setAttribut('dokument_kurzbz',$row->dokument_kurzbz,true);
		$oRdf->obj[$i]->setAttribut('bezeichnung',$row->bezeichnung,true);
		
		$oRdf->addSequence($row->dokument_kurzbz);
}
$oRdf->sendRdfText();
?>
