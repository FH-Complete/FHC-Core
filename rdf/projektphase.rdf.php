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
require_once('../include/datum.class.php');

$oRdf = new rdf('PROJEKTPHASE','http://www.technikum-wien.at/projektphase');
$oRdf->sendHeader();
$datum_obj = new datum();

$projektphase_id = isset($_GET['projektphase_id'])?$_GET['projektphase_id'] : '';
$projekt_kurzbz = isset($_GET['projekt_kurzbz'])?$_GET['projekt_kurzbz'] : '';

if($projektphase_id != '')
{
	$phase = new projektphase();
	$timestamp = time();
	$timestamp = date('Y-m-d');

	if(!$phase->load($projektphase_id))
		die('Fehler beim Laden der Phase');
	$ergebnis = $phase->getFortschritt($projektphase_id);
	$i=$oRdf->newObjekt($phase->projektphase_id);

	// hat phase schon begonnen
	if($timestamp <= $phase->start || $phase->start == '')
		$ergebnis = "-";

	$oRdf->obj[$i]->setAttribut('projektphase_id',$phase->projektphase_id);
	$oRdf->obj[$i]->setAttribut('projekt_kurzbz',$phase->projekt_kurzbz);
	$oRdf->obj[$i]->setAttribut('projektphase_fk',$phase->projektphase_fk);
	$oRdf->obj[$i]->setAttribut('bezeichnung',$phase->bezeichnung);
	$oRdf->obj[$i]->setAttribut('beschreibung',$phase->beschreibung);
	$oRdf->obj[$i]->setAttribut('start_iso',$phase->start);
	$oRdf->obj[$i]->setAttribut('ende_iso',$phase->ende);
	$oRdf->obj[$i]->setAttribut('start',$datum_obj->formatDatum($phase->start,'d.m.Y'));
	$oRdf->obj[$i]->setAttribut('ende',$datum_obj->formatDatum($phase->ende,'d.m.Y'));
	$oRdf->obj[$i]->setAttribut('budget',$phase->budget);
	$oRdf->obj[$i]->setAttribut('fortschritt',$ergebnis);
	$oRdf->obj[$i]->setAttribut('personentage',$phase->personentage);
    $oRdf->obj[$i]->setAttribut('farbe',$phase->farbe);
    $oRdf->obj[$i]->setAttribut('typ',$phase->typ);
    $oRdf->obj[$i]->setAttribut('ressource_id',$phase->ressource_id);
	$oRdf->obj[$i]->setAttribut('zeitaufzeichnung',$phase->zeitaufzeichnung);

	if($phase->projektphase_fk!='')
		$oRdf->addSequence($phase->projektphase_id, $phase->projektphase_fk);
	else
		$oRdf->addSequence($phase->projektphase_id);

}
else if($projekt_kurzbz != '')
{
	$projektphase = new projektphase();

	// gesetzt wenn abfrage für fk ansonsten lade alle phasen zur projekt_kurzbz
	if(isset($_GET['phase_id']))
		$projektphase->getProjektphasenForFk($projekt_kurzbz, $_GET['phase_id']);
	else
		$projektphase->getProjektphasen($projekt_kurzbz);

	if(isset($_GET['optional']))
	{
		$idx=$oRdf->newObjekt('opt');

		$oRdf->obj[$idx]->setAttribut('projektphase_id','');
		$oRdf->obj[$idx]->setAttribut('projekt_kurzbz', '');
		$oRdf->obj[$idx]->setAttribut('projektphase_fk', '');
		$oRdf->obj[$idx]->setAttribut('bezeichnung','< Auswahl >');
		$oRdf->obj[$idx]->setAttribut('beschreibung', '');
		$oRdf->obj[$idx]->setAttribut('start', '');
		$oRdf->obj[$idx]->setAttribut('ende', '');
		$oRdf->obj[$idx]->setAttribut('budget', '');
		$oRdf->obj[$idx]->setAttribut('personentage', '');
        $oRdf->obj[$idx]->setAttribut('farbe', '');
        $oRdf->obj[$idx]->setAttribut('typ', '');
		$oRdf->obj[$idx]->setAttribut('zeitaufzeichnung','');

		$oRdf->addSequence('opt');
	}

	foreach($projektphase->result as $phase)
	{
		$idx=$oRdf->newObjekt($phase->projektphase_id);

		$oRdf->obj[$idx]->setAttribut('projektphase_id',$phase->projektphase_id);
		$oRdf->obj[$idx]->setAttribut('projekt_kurzbz', $phase->projekt_kurzbz);
		$oRdf->obj[$idx]->setAttribut('projektphase_fk', $phase->projektphase_fk);
		$oRdf->obj[$idx]->setAttribut('bezeichnung',$phase->bezeichnung);
		$oRdf->obj[$idx]->setAttribut('beschreibung', $phase->beschreibung);
		$oRdf->obj[$idx]->setAttribut('start', $phase->start);
		$oRdf->obj[$idx]->setAttribut('ende', $phase->ende);
		$oRdf->obj[$idx]->setAttribut('budget', $phase->budget);
		$oRdf->obj[$idx]->setAttribut('personentage', $phase->personentage);
        $oRdf->obj[$idx]->setAttribut('farbe', $phase->farbe);
        $oRdf->obj[$idx]->setAttribut('typ', $phase->typ);
		$oRdf->obj[$idx]->setAttribut('zeitaufzeichnung',$phase->zeitaufzeichnung);

		$oRdf->addSequence($phase->projektphase_id);
	}

}
else
{
	$projekt_obj = new projekt();

	if(isset($_REQUEST['filterprj']))
	{
		$projekt_obj2 = new projekt();
		if($projekt_obj2->load($_REQUEST['filterprj']))
		{
			$projekt_obj->result[] = $projekt_obj2;
		}
	}
	else
	{
	    if(!isset($_REQUEST['filter']))
	        $projekt_obj->getProjekte();
	    else
	    {
	        if($_REQUEST['filter']=='aktuell')
	            $projekt_obj->getProjekteAktuell();
	        else if($_REQUEST['filter']=='kommende')
	            $projekt_obj->getProjekteAktuell(true);
	    }
	}

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
			$oRdf->obj[$idx]->setAttribut('beginn_iso','');
			$oRdf->obj[$idx]->setAttribut('ende_iso','');
			$oRdf->obj[$idx]->setAttribut('beginn','');
			$oRdf->obj[$idx]->setAttribut('ende','');
			$oRdf->obj[$idx]->setAttribut('typ','organisationseinheit');
			$oRdf->obj[$idx]->setAttribut('zeitaufzeichnung','');

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
		$oRdf->obj[$idx]->setAttribut('beginn_iso',$projekt->beginn);
		$oRdf->obj[$idx]->setAttribut('ende_iso',$projekt->ende);
		$oRdf->obj[$idx]->setAttribut('beginn',$datum_obj->formatDatum($projekt->beginn,'d.m.Y'));
		$oRdf->obj[$idx]->setAttribut('ende',$datum_obj->formatDatum($projekt->ende,'d.m.Y'));
		$oRdf->obj[$idx]->setAttribut('typ','projekt');
		$oRdf->obj[$idx]->setAttribut('zeitaufzeichnung',$projekt->zeitaufzeichnung);


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
			$oRdf->obj[$idx]->setAttribut('beginn_iso',$projektphase->start);
			$oRdf->obj[$idx]->setAttribut('ende_iso',$projektphase->ende);
			$oRdf->obj[$idx]->setAttribut('beginn',$datum_obj->formatDatum($projektphase->start,'d.m.Y'));
			$oRdf->obj[$idx]->setAttribut('ende',$datum_obj->formatDatum($projektphase->ende,'d.m.Y'));
			$oRdf->obj[$idx]->setAttribut('fortschritt',$ergebnis);
			$oRdf->obj[$idx]->setAttribut('budget',$projektphase->budget);
			$oRdf->obj[$idx]->setAttribut('personentage',$projektphase->personentage);
            $oRdf->obj[$idx]->setAttribut('farbe',$projektphase->farbe);
			$oRdf->obj[$idx]->setAttribut('typ',strtolower($projektphase->typ));
			$oRdf->obj[$idx]->setAttribut('ressource_bezeichnung',$projektphase->ressource_bezeichnung);
			$oRdf->obj[$idx]->setAttribut('ressource_id',$projektphase->ressource_id);
			$oRdf->obj[$idx]->setAttribut('zeitaufzeichnung',$projektphase->zeitaufzeichnung);
			if (!is_null($projektphase->projektphase_fk))
				$oRdf->addSequence($projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz.'/'.$projektphase->projektphase_id, $projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz.'/'.$projektphase->projektphase_fk);
			else
				$oRdf->addSequence($projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz.'/'.$projektphase->projektphase_id, $projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz);
		}
	}
}

$oRdf->sendRdfText();
?>
