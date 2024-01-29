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
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/rdf.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/wawi_konto.class.php');
require_once('../include/wawi_kostenstelle.class.php');
require_once('../include/buchung.class.php');
require_once('../include/datum.class.php');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('buchung/mitarbeiter'))
	die('Sie haben keine Berechtigung für diese Seite');

if(isset($_GET['person_id']))
	$person_id = $_GET['person_id'];
else
	$person_id = null;

if(isset($_GET['buchung_id']))
	$buchung_id = $_GET['buchung_id'];
else
	$buchung_id = null;

$oRdf = new rdf('BUCHUNG','http://www.technikum-wien.at/wawi_buchung');
$oRdf->sendHeader();

$datum = new datum();

// Alle Buchungstypen laden
$buchungstypen=array();
$buchungstyp = new buchung();
$buchungstyp->getAllBuchungstypen();
foreach($buchungstyp->result as $row)
	$buchungstypen[$row->buchungstyp_kurzbz]=$row->buchungstyp_bezeichnung;

// Alle Konten laden
$konten=array();
$konto = new wawi_konto();
$konto->getAll();
foreach($konto->result as $row)
	$konten[$row->konto_id]=$row->beschreibung[DEFAULT_LANGUAGE];

// Alle Kostenstellen laden
$kostenstellen=array();
$kostenstelle = new wawi_kostenstelle();
$kostenstelle->getAll();
foreach($kostenstelle->result as $row)
	$kostenstellen[$row->kostenstelle_id]=$row->bezeichnung;

// Buchung laden
$obj = new buchung();

if(!is_null($person_id))
{
	$obj->getBuchungPerson($person_id);

	foreach($obj->result as $row)
		addRow($row);	
}
elseif(!is_null($buchung_id))
{
	$obj->load($buchung_id);
	addRow($obj);
}
else
	die('Falsche Parameterübergabe');

// Ausgabe einer Buchung
function addRow($row)
{
	global $oRdf, $datum;
	global $konten, $kostenstellen, $buchungstypen;

	$i=$oRdf->newObjekt($row->buchung_id);
	$oRdf->obj[$i]->setAttribut('buchung_id',$row->buchung_id,true);
	$oRdf->obj[$i]->setAttribut('buchungsdatum',$datum->formatDatum($row->buchungsdatum,'d.m.Y'),true);
	$oRdf->obj[$i]->setAttribut('buchungsdatum_iso',$row->buchungsdatum,true);
	$oRdf->obj[$i]->setAttribut('buchungstext',$row->buchungstext,true);
	$oRdf->obj[$i]->setAttribut('betrag',$row->betrag,true);
	$oRdf->obj[$i]->setAttribut('konto',(isset($konten[$row->konto_id])?$konten[$row->konto_id]:''),true);
	$oRdf->obj[$i]->setAttribut('kostenstelle',(isset($kostenstellen[$row->kostenstelle_id])?$kostenstellen[$row->kostenstelle_id]:''),true);
	$oRdf->obj[$i]->setAttribut('buchungstyp',(isset($buchungstypen[$row->buchungstyp_kurzbz])?$buchungstypen[$row->buchungstyp_kurzbz]:''),true);
	$oRdf->obj[$i]->setAttribut('konto_id',$row->konto_id,true);
	$oRdf->obj[$i]->setAttribut('kostenstelle_id',$row->kostenstelle_id,true);
	$oRdf->obj[$i]->setAttribut('buchungstyp_kurzbz',$row->buchungstyp_kurzbz,true);
	
	$oRdf->addSequence($row->buchung_id);
}

$oRdf->sendRdfText();
?>
