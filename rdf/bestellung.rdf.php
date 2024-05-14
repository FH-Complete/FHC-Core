<?php
/* Copyright (C) 2011 Technikum-Wien
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
require_once('../include/wawi_bestellung.class.php');

if(isset($_GET['projektKurzbz']))
{
	$projektKurzbz = $_GET['projektKurzbz'];
	$oRdf = new rdf('BESTELLUNG','http://www.technikum-wien.at/bestellung');
	$oRdf->sendHeader();
	
	$oBestellung = new wawi_bestellung();
	$oBestellung->getBestellungProjekt($projektKurzbz);
	
	
	foreach ($oBestellung->result as $bestellung)
	{	
		$brutto = $bestellung->getBrutto($bestellung->bestellung_id);
		if($brutto == '')
			$brutto = '0';
		else 
			$brutto = sprintf("%01.2f", $brutto); 
		
		
		$i=$oRdf->newObjekt($bestellung->bestellung_id);
		$oRdf->obj[$i]->setAttribut('bestellung_id',$bestellung->bestellung_id,false);
		$oRdf->obj[$i]->setAttribut('kostenstelle_id',$bestellung->kostenstelle_id,false);
		$oRdf->obj[$i]->setAttribut('konto_id',$bestellung->konto_id,false);
		$oRdf->obj[$i]->setAttribut('lieferadresse',$bestellung->lieferadresse,true);
		$oRdf->obj[$i]->setAttribut('rechnungsadresse',$bestellung->rechnungsadresse,true);
		$oRdf->obj[$i]->setAttribut('freigegeben',$bestellung->freigegeben,true);
		$oRdf->obj[$i]->setAttribut('bestell_nr',$bestellung->bestell_nr,false);
		$oRdf->obj[$i]->setAttribut('titel',$bestellung->titel,true);
		$oRdf->obj[$i]->setAttribut('bemerkung',$bestellung->bemerkung,true);
		$oRdf->obj[$i]->setAttribut('liefertermin',$bestellung->liefertermin,true);
		$oRdf->obj[$i]->setAttribut('besteller_uid',$bestellung->besteller_uid,false);
		$oRdf->obj[$i]->setAttribut('updateamum',$bestellung->updateamum,true);
		$oRdf->obj[$i]->setAttribut('updatevon',$bestellung->updatevon,true);
		$oRdf->obj[$i]->setAttribut('insertamum',$bestellung->insertamum,true);
		$oRdf->obj[$i]->setAttribut('insertvon',$bestellung->insertvon,true);
		$oRdf->obj[$i]->setAttribut('ext_id',$bestellung->ext_id,false);
		$oRdf->obj[$i]->setAttribut('betrag',$brutto,false);
		$oRdf->obj[$i]->setAttribut('zahlungstyp_kurzbz',$bestellung->zahlungstyp_kurzbz,true);
		
		$oRdf->addSequence($bestellung->bestellung_id);
	}
	
	$oRdf->sendRdfText();
	}
else
{
	echo "Parameter: projektKurzbz"; 
}
?>
