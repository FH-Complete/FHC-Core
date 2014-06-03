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

$oRdf = new rdf('AUFWANDSTYP','http://www.technikum-wien.at/aufwandstyp');
$oRdf->sendHeader();

$qry = "SELECT * FROM fue.tbl_aufwandstyp ORDER BY bezeichnung";
$db = new basis_db();
if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{	
		$i=$oRdf->newObjekt($row->aufwandstyp_kurzbz);
		$oRdf->obj[$i]->setAttribut('aufwandstyp_kurzbz',$row->aufwandstyp_kurzbz,true);
		$oRdf->obj[$i]->setAttribut('bezeichnung',$row->bezeichnung,true);
		
		$oRdf->addSequence($row->aufwandstyp_kurzbz);
	}
}
$oRdf->sendRdfText();
?>