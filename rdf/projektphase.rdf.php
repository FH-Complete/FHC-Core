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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 * 			Karl Burkhart <burkhart@technikum-wien.at>
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/projekt.class.php');
require_once('../include/projektphase.class.php');
require_once('../include/rdf.class.php');

$oRdf = new rdf('PROJEKTPHASE','http://www.technikum-wien.at/projektphase');
$oRdf->sendHeader();

if(isset($_GET['projektphase_id']))
{
	$phase = new projektphase();
	$timestamp = time(); 
	$timestamp = date('Y-m-d');
	
	$phase->load($_GET['projektphase_id']);
	$ergebnis = $phase->getFortschritt($_GET['projektphase_id']);
	$i=$oRdf->newObjekt($phase->projektphase_id);

	// hat phase schon begonnen
	if($timestamp <= $projektphase->start || $projektphase->start == '')
		$ergebnis = "-";
	
	$oRdf->obj[$i]->setAttribut('projektphase_id',$phase->projektphase_id);
	$oRdf->obj[$i]->setAttribut('projekt_kurzbz',$phase->projekt_kurzbz);
	$oRdf->obj[$i]->setAttribut('projektphase_fk',$phase->projektphase_fk);
	$oRdf->obj[$i]->setAttribut('bezeichnung',$phase->bezeichnung);
	$oRdf->obj[$i]->setAttribut('beschreibung',$phase->beschreibung);
	$oRdf->obj[$i]->setAttribut('start',$phase->start);
	$oRdf->obj[$i]->setAttribut('ende',$phase->ende);
	$oRdf->obj[$i]->setAttribut('budget',$phase->budget);
	$oRdf->obj[$i]->setAttribut('fortschritt',$ergebnis);
	$oRdf->obj[$i]->setAttribut('personentage',$phase->personentage);
	
	if($phase->projektphase_fk!='')
		$oRdf->addSequence($phase->projektphase_id, $phase->projektphase_fk);
	else
		$oRdf->addSequence($phase->projektphase_id);
}
else
{
	$projekt_obj = new projekt();
	$projekt_obj->getProjekte();
	$projektphase_obj = new projektphase();
	$sequenzProjektphase = array();
		
	$descr='';
	$sequenz='';
	$lastOE=null;
	for ($i=0;$i<count($projekt_obj->result);$i++)
	{
		$currentOE=$projekt_obj->result[$i]->oe_kurzbz;
		//echo $currentOE;
		$nextOE=(($i<count($projekt_obj->result)-1)?$projekt_obj->result[$i+1]->oe_kurzbz:null);
		//echo $nextOE;
		$projekt=$projekt_obj->result[$i];
		// Bin ich schon in der naechsten OE? Oder vielleicht in der ersten?
		if ($lastOE!=$currentOE || $i==0)
		{
			$idx=$oRdf->newObjekt($projekt->oe_kurzbz);
	
			$oRdf->obj[$idx]->setAttribut('bezeichnung',$projekt->oe_kurzbz);
			$oRdf->obj[$idx]->setAttribut('oe_kurzbz',$projekt->oe_kurzbz);
			$oRdf->obj[$idx]->setAttribut('projekt_kurzbz','');
			$oRdf->obj[$idx]->setAttribut('projekt_phase','');
			$oRdf->obj[$idx]->setAttribut('projekt_phase_id','');
			$oRdf->obj[$idx]->setAttribut('nummer','');
			$oRdf->obj[$idx]->setAttribut('titel','');
			$oRdf->obj[$idx]->setAttribut('beschreibung','');
			$oRdf->obj[$idx]->setAttribut('beginn','');
			$oRdf->obj[$idx]->setAttribut('ende','');
			
			$oRdf->addSequence($projekt->oe_kurzbz);
			
			$lastOE=$currentOE;
		}
		
		$idx=$oRdf->newObjekt($projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz);
	
		$oRdf->obj[$idx]->setAttribut('bezeichnung',$projekt->titel);
		$oRdf->obj[$idx]->setAttribut('oe_kurzbz',$projekt->oe_kurzbz);
		$oRdf->obj[$idx]->setAttribut('projekt_kurzbz',$projekt->projekt_kurzbz);
		$oRdf->obj[$idx]->setAttribut('projekt_phase','');
		$oRdf->obj[$idx]->setAttribut('projekt_phase_id','');
		$oRdf->obj[$idx]->setAttribut('nummer',$projekt->nummer);
		$oRdf->obj[$idx]->setAttribut('titel',$projekt->titel);
		$oRdf->obj[$idx]->setAttribut('beschreibung',$projekt->beschreibung);
		$oRdf->obj[$idx]->setAttribut('beginn',$projekt->beginn);
		$oRdf->obj[$idx]->setAttribut('ende',$projekt->ende);
		
		$oRdf->addSequence($projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz, $projekt->oe_kurzbz);
				
		$projektphase_obj->getProjektphasen($projekt->projekt_kurzbz);
		$tmpStr='';
		for ($j=0;$j<count($projektphase_obj->result);$j++)
		{
			$projektphase=$projektphase_obj->result[$j];
					
			$timestamp = time(); 
			$timestamp = date('Y-m-d');

			$ergebnis = $projektphase->getFortschritt($projektphase->projektphase_id);
			
			// hat phase schon begonnen
			if($timestamp <= $projektphase->start || $projektphase->start == '')
				$ergebnis = "-";
			
			$idx=$oRdf->newObjekt($projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz.'/'.$projektphase->projektphase_id);
	
			$oRdf->obj[$idx]->setAttribut('bezeichnung',$projektphase->bezeichnung);
			$oRdf->obj[$idx]->setAttribut('oe_kurzbz',$projekt->oe_kurzbz);
			$oRdf->obj[$idx]->setAttribut('projekt_kurzbz',$projektphase->projekt_kurzbz);
			$oRdf->obj[$idx]->setAttribut('projekt_phase',$projektphase->bezeichnung);
			$oRdf->obj[$idx]->setAttribut('projekt_phase_id',$projektphase->projektphase_id);
			$oRdf->obj[$idx]->setAttribut('nummer','');
			$oRdf->obj[$idx]->setAttribut('titel',$projektphase->bezeichnung);
			$oRdf->obj[$idx]->setAttribut('beschreibung',$projektphase->beschreibung);
			$oRdf->obj[$idx]->setAttribut('beginn',$projektphase->start);
			$oRdf->obj[$idx]->setAttribut('ende',$projektphase->ende);
			$oRdf->obj[$idx]->setAttribut('fortschritt',$ergebnis);
			$oRdf->obj[$idx]->setAttribut('budget',$projektphase->budget);
			$oRdf->obj[$idx]->setAttribut('personentage',$projektphase->personentage);
			
			if (!is_null($projektphase->projektphase_fk))
				$oRdf->addSequence($projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz.'/'.$projektphase->projektphase_id, $projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz.'/'.$projektphase->projektphase_fk);
			else
				$oRdf->addSequence($projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz.'/'.$projektphase->projektphase_id, $projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz);
		}
	}	
}

$oRdf->sendRdfText();
?>
