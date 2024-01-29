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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");

// DAO
require_once('../config/vilesci.config.inc.php');
require_once('../include/betriebsmittelperson.class.php');
require_once('../include/datum.class.php');
require_once('../include/organisationseinheit.class.php');
require_once('../include/person.class.php');
require_once('../include/betriebsmitteltyp.class.php');
require_once('../include/betriebsmittel.class.php');
require_once('../include/wawi_bestellung.class.php');
require_once('../include/firma.class.php');

if(isset($_GET['person_id']))
	$person_id = $_GET['person_id'];
else
	$person_id = '';

if(isset($_GET['betriebsmitteltyp']))
	$betriebsmitteltyp = $_GET['betriebsmitteltyp'];
else
	$betriebsmitteltyp = null;

if(isset($_GET['betriebsmittelperson_id']))
	$betriebsmittelperson_id = $_GET['betriebsmittelperson_id'];
else
	$betriebsmittelperson_id = null;

if(isset($_GET['id']))
	$betriebsmittelperson_id = $_GET['id'];

if(isset($_GET['xmlformat']))
	$xmlformat=$_GET['xmlformat'];
else
	$xmlformat='rdf';

$datum = new datum();
if($xmlformat!='xml')
{
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

	$rdf_url='http://www.technikum-wien.at/betriebsmittel';

	echo '
	<RDF:RDF
		xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
		xmlns:BTM="'.$rdf_url.'/rdf#"
	>

	   <RDF:Seq about="'.$rdf_url.'/liste">';


	$betriebsmittel = new betriebsmittelperson();
	if($betriebsmittelperson_id=='' && $person_id!='')
	{
		if($betriebsmittel->getBetriebsmittelPerson($person_id, $betriebsmitteltyp))
			foreach ($betriebsmittel->result as $row)
				draw_content($row);
		else
			die($betriebsmittel->errormsg);
	}
	else
	{
		if($betriebsmittel->load($betriebsmittelperson_id))
			draw_content($betriebsmittel);
		else
			die($betriebsmittel->errormsg);
	}
	echo '</RDF:Seq>
	</RDF:RDF>';
}
else
{
	$bmp = new betriebsmittelperson();
	if(!$bmp->load($betriebsmittelperson_id))
		die('Fehler '.$bmp->errormsg);

	$oe = new organisationseinheit();
	$oe->load($bmp->oe_kurzbz);
	$organisationseinheit = $oe->organisationseinheittyp_kurzbz.' '.$oe->bezeichnung;

	$person = new person();
	$person->load($bmp->person_id);

	$bmt = new betriebsmitteltyp();
	$bmt->load($bmp->betriebsmitteltyp);
	$typ = $bmt->result[0]->beschreibung;
	
	$bm = new betriebsmittel($bmp->betriebsmittel_id);
	
	$bestellung = new wawi_bestellung($bm->bestellung_id);
	$firma =  new firma($bestellung->firma_id);

	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
	echo '
	<betriebsmittelperson>
		<beschreibung><![CDATA['.$bmp->beschreibung.']]></beschreibung>
		<inventarnummer><![CDATA['.$bmp->inventarnummer.']]></inventarnummer>
		<kaution><![CDATA['.number_format($bmp->kaution,2,",",".").']]></kaution>
		<ausgegebenam><![CDATA['.$datum->convertISODate($bmp->ausgegebenam).']]></ausgegebenam>
		<retouram><![CDATA['.$datum->convertISODate($bmp->retouram).']]></retouram>
		<organisationseinheit><![CDATA['.trim($organisationseinheit).']]></organisationseinheit>
		<titelpre><![CDATA['.$person->titelpre.']]></titelpre>
		<vorname><![CDATA['.$person->vorname.']]></vorname>
		<nachname><![CDATA['.$person->nachname.']]></nachname>
		<titelpost><![CDATA['.$person->titelpost.']]></titelpost>
		<name_gesamt><![CDATA['.trim($person->titelpre.' '.$person->vorname.' '.$person->nachname.' '.$person->titelpost).']]></name_gesamt>
		<geschlecht><![CDATA['.$person->geschlecht.']]></geschlecht>
		<geburtsdatum><![CDATA['.$datum->convertISODate($person->gebdatum).']]></geburtsdatum>
		<svnr><![CDATA['.$person->svnr.']]></svnr>
		<nummer><![CDATA['.$bmp->nummer.']]></nummer>
		<nummer2><![CDATA['.$bmp->nummer2.']]></nummer2>
		<betriebsmitteltyp><![CDATA['.$bmp->betriebsmitteltyp.']]></betriebsmitteltyp>
		<bestellnummer><![CDATA['.$bestellung->bestell_nr.']]></bestellnummer>
		<hersteller><![CDATA['.$bm->hersteller.']]></hersteller>
		<bestellung_id><![CDATA['.$bm->bestellung_id.']]></bestellung_id>
		<lieferfirma><![CDATA['.$firma->name.']]></lieferfirma>
		<typ><![CDATA['.$typ.']]></typ>
		<datum><![CDATA['.date("d.m.Y").']]></datum>
	</betriebsmittelperson>
	';

}
function draw_content($row)
{
	global $rdf_url, $datum;

	echo '
      <RDF:li>
         <RDF:Description  id="'.$row->betriebsmittelperson_id.'"  about="'.$rdf_url.'/'.$row->betriebsmittelperson_id.'" >
         	<BTM:betriebsmittelperson_id><![CDATA['.$row->betriebsmittelperson_id.']]></BTM:betriebsmittelperson_id>
            <BTM:betriebsmittel_id><![CDATA['.$row->betriebsmittel_id.']]></BTM:betriebsmittel_id>
            <BTM:beschreibung><![CDATA['.$row->beschreibung.']]></BTM:beschreibung>
            <BTM:betriebsmitteltyp><![CDATA['.$row->betriebsmitteltyp.']]></BTM:betriebsmitteltyp>
            <BTM:nummer><![CDATA['.$row->nummer.']]></BTM:nummer>
            <BTM:nummer2><![CDATA['.$row->nummer2.']]></BTM:nummer2>
            <BTM:inventarnummer><![CDATA['.$row->inventarnummer.']]></BTM:inventarnummer>
            <BTM:reservieren><![CDATA['.($row->reservieren?'Ja':'Nein').']]></BTM:reservieren>
            <BTM:ort_kurzbz><![CDATA['.$row->ort_kurzbz.']]></BTM:ort_kurzbz>
            <BTM:person_id><![CDATA['.$row->person_id.']]></BTM:person_id>
            <BTM:anmerkung><![CDATA['.$row->anmerkung.']]></BTM:anmerkung>
            <BTM:kaution><![CDATA['.$row->kaution.']]></BTM:kaution>
            <BTM:ausgegebenam_iso><![CDATA['.$row->ausgegebenam.']]></BTM:ausgegebenam_iso>
            <BTM:ausgegebenam><![CDATA['.$datum->convertISODate($row->ausgegebenam).']]></BTM:ausgegebenam>
            <BTM:retouram_iso><![CDATA['.$row->retouram.']]></BTM:retouram_iso>
            <BTM:retouram><![CDATA['.$datum->convertISODate($row->retouram).']]></BTM:retouram>
            <BTM:uid><![CDATA['.$row->uid.']]></BTM:uid>
         </RDF:Description>
      </RDF:li>';

}

?>

