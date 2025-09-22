<?php
/* Copyright (C) 2004 Technikum-Wien
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
// header for no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");

// DAO
require_once('../config/vilesci.config.inc.php');
require_once('../include/person.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/functions.inc.php');
require_once('../include/datum.class.php');

$user = get_uid();
$datum = new datum();

loadVariables($user);

if(isset($_GET['uid']))
	$uid = $_GET['uid'];
else
	$uid=null;

if (isset($_GET['fix']))
	$fix = $_GET['fix'];
else
	$fix=null;

if (isset($_GET['stgl']))
	$stgl = $_GET['stgl'];
else
	$stgl=null;

if (isset($_GET['fbl']))
	$fbl = $_GET['fbl'];
else
	$fbl=null;

if (isset($_GET['aktiv']))
	$aktiv = $_GET['aktiv'];
else
	$aktiv=null;

if (isset($_GET['karenziert']))
	$karenziert = $_GET['karenziert'];
else
	$karenziert=null;

if (isset($_GET['verwendung']))
	$verwendung = $_GET['verwendung'];
else
	$verwendung=null;

$vertrag=null;

if (isset($_GET['VertragNochNichtRetour']))
{
	// Vertraege muessen nur von externen Lektoren retourniert werden
	$fix='false';
	$vertrag = 'VertragNochNichtRetour';
}

if (isset($_GET['VertragHabilitiert']))
{
	$fix='false';
	$vertrag = 'VertragHabilitiert';
}

if (isset($_GET['VertragNichtHabilitiert']))
{
	$fix='false';
	$vertrag = 'VertragNichtHabilitiert';
}

if (isset($_GET['VertragNichtGedruckt']))
{
	$fix='false';
	$vertrag = 'VertragNichtGedruckt';
}



if(isset($_GET['filter']))
	$filter = $_GET['filter'];
else
	$filter = null;
$rdf_url='http://www.technikum-wien.at/mitarbeiter';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
	xmlns:MITARBEITER="'.$rdf_url.'/rdf#"
>


  <RDF:Seq RDF:about="'.$rdf_url.'/alle">
';

// Mitarbeiter holen
$mitarbeiterDAO=new mitarbeiter();

if($uid==null)
{
	if($filter!='')
		$mitarbeiterDAO->searchPersonal($filter);
	else
		$mitarbeiterDAO->getPersonal($fix, $stgl, $fbl, $aktiv, $karenziert, $verwendung, $vertrag);

	foreach ($mitarbeiterDAO->result as $mitarbeiter)
		draw_row($mitarbeiter);
}
else
{
	$mitarbeiterDAO->load($uid);
	draw_row($mitarbeiterDAO);
}

function draw_row($mitarbeiter)
{
	global $rdf_url, $datum;

	echo '
	  <RDF:li>
      	<RDF:Description RDF:about="'.$rdf_url.'/'.$mitarbeiter->uid.'" >
        	<MITARBEITER:person_id NC:parseType="Integer"><![CDATA['.$mitarbeiter->person_id.']]></MITARBEITER:person_id>
    		<MITARBEITER:nachname><![CDATA['.$mitarbeiter->nachname.']]></MITARBEITER:nachname>
    		<MITARBEITER:vorname><![CDATA['.$mitarbeiter->vorname.']]></MITARBEITER:vorname>
    		<MITARBEITER:vornamen><![CDATA['.$mitarbeiter->vornamen.']]></MITARBEITER:vornamen>
				<MITARBEITER:wahlname><![CDATA['.$mitarbeiter->wahlname.']]></MITARBEITER:wahlname>
    		<MITARBEITER:anrede><![CDATA['.$mitarbeiter->anrede.']]></MITARBEITER:anrede>
    		<MITARBEITER:geschlecht><![CDATA['.$mitarbeiter->geschlecht.']]></MITARBEITER:geschlecht>
    		<MITARBEITER:geburtsdatum><![CDATA['.$datum->convertISODate($mitarbeiter->gebdatum).']]></MITARBEITER:geburtsdatum>
    		<MITARBEITER:geburtsdatum_iso><![CDATA['.$mitarbeiter->gebdatum.']]></MITARBEITER:geburtsdatum_iso>
    		<MITARBEITER:geburtsort><![CDATA['.$mitarbeiter->gebort.']]></MITARBEITER:geburtsort>
    		<MITARBEITER:geburtszeit><![CDATA['.$mitarbeiter->gebzeit.']]></MITARBEITER:geburtszeit>
    		<MITARBEITER:staatsbuergerschaft><![CDATA['.$mitarbeiter->staatsbuergerschaft.']]></MITARBEITER:staatsbuergerschaft>
    		<MITARBEITER:familienstand><![CDATA['.$mitarbeiter->familienstand.']]></MITARBEITER:familienstand>
    		<MITARBEITER:familienstand_bezeichnung><![CDATA['.$mitarbeiter->familienstand.']]></MITARBEITER:familienstand_bezeichnung>
    		<MITARBEITER:svnr><![CDATA['.$mitarbeiter->svnr.']]></MITARBEITER:svnr>
    		<MITARBEITER:anzahlkinder NC:parseType="Integer"><![CDATA['.$mitarbeiter->anzahlkinder.']]></MITARBEITER:anzahlkinder>
    		<MITARBEITER:ersatzkennzeichen><![CDATA['.$mitarbeiter->ersatzkennzeichen.']]></MITARBEITER:ersatzkennzeichen>
    		<MITARBEITER:anmerkungen><![CDATA['.$mitarbeiter->anmerkungen.']]></MITARBEITER:anmerkungen>
    		<MITARBEITER:homepage><![CDATA['.$mitarbeiter->homepage.']]></MITARBEITER:homepage>
    		<MITARBEITER:sprache><![CDATA['.$mitarbeiter->sprache.']]></MITARBEITER:sprache>
    		<MITARBEITER:titelpre><![CDATA['.$mitarbeiter->titelpre.']]></MITARBEITER:titelpre>
    		<MITARBEITER:titelpost><![CDATA['.$mitarbeiter->titelpost.']]></MITARBEITER:titelpost>
    		<MITARBEITER:uid><![CDATA['.$mitarbeiter->uid.']]></MITARBEITER:uid>
    		<MITARBEITER:geburtsnation><![CDATA['.$mitarbeiter->geburtsnation.']]></MITARBEITER:geburtsnation>
    		<MITARBEITER:personalnummer NC:parseType="Integer"><![CDATA['.$mitarbeiter->personalnummer.']]></MITARBEITER:personalnummer>
    		<MITARBEITER:kurzbz><![CDATA['.$mitarbeiter->kurzbz.']]></MITARBEITER:kurzbz>
    		<MITARBEITER:stundensatz NC:parseType="Integer"><![CDATA['.$mitarbeiter->stundensatz.']]></MITARBEITER:stundensatz>
    		<MITARBEITER:ausbildung><![CDATA['.$mitarbeiter->ausbildungcode.']]></MITARBEITER:ausbildung>
    		<MITARBEITER:aktiv><![CDATA['.($mitarbeiter->bnaktiv?'Ja':'Nein').']]></MITARBEITER:aktiv>
    		<MITARBEITER:lektor><![CDATA['.($mitarbeiter->lektor?'Ja':'Nein').']]></MITARBEITER:lektor>
    		<MITARBEITER:fixangestellt><![CDATA['.($mitarbeiter->fixangestellt?'Ja':'Nein').']]></MITARBEITER:fixangestellt>
    		<MITARBEITER:bismelden><![CDATA['.($mitarbeiter->bismelden?'Ja':'Nein').']]></MITARBEITER:bismelden>
    		<MITARBEITER:ort_kurzbz><![CDATA['.$mitarbeiter->ort_kurzbz.']]></MITARBEITER:ort_kurzbz>
    		<MITARBEITER:telefonklappe><![CDATA['.$mitarbeiter->telefonklappe.']]></MITARBEITER:telefonklappe>
    		<MITARBEITER:anmerkung><![CDATA['.$mitarbeiter->anmerkung.']]></MITARBEITER:anmerkung>
    		<MITARBEITER:standort_id><![CDATA['.$mitarbeiter->standort_id.']]></MITARBEITER:standort_id>
    		<MITARBEITER:alias><![CDATA['.$mitarbeiter->alias.']]></MITARBEITER:alias>
    		<MITARBEITER:insertamum><![CDATA['.date('d.m.Y H:i:s',$datum->mktime_fromtimestamp($mitarbeiter->insertamum)).']]></MITARBEITER:insertamum>
    		<MITARBEITER:insertamum_iso><![CDATA['.$mitarbeiter->insertamum.']]></MITARBEITER:insertamum_iso>
    		<MITARBEITER:insertvon><![CDATA['.$mitarbeiter->insertvon.']]></MITARBEITER:insertvon>
    		<MITARBEITER:updateamum><![CDATA['.date('d.m.Y H:i:s',$datum->mktime_fromtimestamp($mitarbeiter->updateamum)).']]></MITARBEITER:updateamum>
    		<MITARBEITER:updateamum_iso><![CDATA['.$mitarbeiter->updateamum.']]></MITARBEITER:updateamum_iso>
    		<MITARBEITER:updatevon><![CDATA['.$mitarbeiter->updatevon.']]></MITARBEITER:updatevon>
			<MITARBEITER:kleriker><![CDATA['.($mitarbeiter->kleriker?'Ja':'Nein').']]></MITARBEITER:kleriker>
      	</RDF:Description>
      </RDF:li>
	';
}
?>
	</RDF:Seq>
</RDF:RDF>
