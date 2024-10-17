<?php
/* Copyright (C) 2016 fhcomplete.org
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
require_once('../include/basis_db.class.php');
require_once('../include/mobilitaet.class.php');

$oRdf = new rdf('MOBTYP','http://www.technikum-wien.at/mobilitaetstyp');
$oRdf->sendHeader();

$mob = new mobilitaet();
if($mob->getMobilitaetstyp())
{
	foreach($mob->result as $row)
	{
		$i=$oRdf->newObjekt($row->mobilitaetstyp_kurzbz);
		$oRdf->obj[$i]->setAttribut('mobilitaetstyp_kurzbz',$row->mobilitaetstyp_kurzbz,true);
		$oRdf->obj[$i]->setAttribut('bezeichnung',$row->bezeichnung,true);

		$oRdf->addSequence($row->mobilitaetstyp_kurzbz);
	}
}
$oRdf->sendRdfText();
?>
