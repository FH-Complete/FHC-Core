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
require_once('../include/organisationseinheit.class.php');
require_once('../include/rdf.class.php');
require_once('../include/benutzerberechtigung.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('basis/fas'))
	die('Sie haben keine Berechtigung für diese Seite');

$oes = $rechte->getOEkurzbz('assistenz');
$oe=new organisationseinheit();
$oe->loadArray($oes, 'bezeichnung, organisationseinheittyp_kurzbz', true);

$oRdf = new rdf('OE','http://www.technikum-wien.at/organisationseinheit');
$oRdf->sendHeader();

foreach($oe->result as $row)
{
	if($row->lehre)
	{
		$i=$oRdf->newObjekt($row->oe_kurzbz);
		$oRdf->obj[$i]->setAttribut('oe_kurzbz',$row->oe_kurzbz,true);
		$oRdf->obj[$i]->setAttribut('bezeichnung',$row->bezeichnung,true);
		$oRdf->obj[$i]->setAttribut('typ',$row->organisationseinheittyp_kurzbz,true);
		$oRdf->obj[$i]->setAttribut('uid','',true);

		$oRdf->addSequence($row->oe_kurzbz);
	}
}

$oRdf->sendRdfText();
?>
