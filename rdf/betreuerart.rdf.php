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

$oRdf = new rdf('BETREUERART','http://www.technikum-wien.at/betreuerart');
$oRdf->sendHeader();

$qry = "SELECT * FROM lehre.tbl_betreuerart ORDER BY aktiv DESC, beschreibung";
$db = new basis_db();
if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{	
		$i=$oRdf->newObjekt($row->betreuerart_kurzbz);
		//$oRdf->obj[$i]->setAttribut('id',$row->betreuerart_kurzbz,false);
		$oRdf->obj[$i]->setAttribut('betreuerart_kurzbz',$row->betreuerart_kurzbz,true);
		$oRdf->obj[$i]->setAttribut('beschreibung',$row->beschreibung,true);
		$oRdf->obj[$i]->setAttribut('aktiv',($db->db_parse_bool($row->aktiv)?'true':'false'),true);
		
		$oRdf->addSequence($row->betreuerart_kurzbz);
	}
}
$oRdf->sendRdfText();
?>
