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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Raab <gerald.raab@technikum-wien.at>.
 */
/*
 * Created on 02.12.2004
 *
 */
// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../config/vilesci.config.inc.php');
require_once('../include/dokument.class.php');
require_once('../include/akte.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/datum.class.php');

$rdf_url='http://www.technikum-wien.at/dokument';

if(isset($_GET['studiengang_kz']) && is_numeric($_GET['studiengang_kz']))
	$studiengang_kz=$_GET['studiengang_kz'];
else
	die('Fehlerhafte Parameteruebergabe');

if(isset($_GET['prestudent_id']))
	if(is_numeric($_GET['prestudent_id']))
		$prestudent_id=$_GET['prestudent_id'];
	else
		die('Prestudent_id ist ungueltig');
else
	$prestudent_id = null;

$dok = new dokument();
if(!$dok->getFehlendeDokumente($studiengang_kz, $prestudent_id, false))
	die($dok->errormsg);
	
//var_dump($dok);
$prestudent = new prestudent();
if(!$prestudent->load($prestudent_id))
	die($prestudent->errormsg);

$date = new datum();
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:DOKUMENT="<?php echo $rdf_url; ?>/rdf#"
>

  <RDF:Seq about="<?php echo $rdf_url ?>/liste">

<?php

// Alle dokumenttypen die der Student abzugeben hat vom Studiengang
foreach ($dok->result as $row)
{
	$akte = new akte();
	$akte->getAkten($prestudent->person_id, $row->dokument_kurzbz);
	// Schleife für alle Akten -> wenn akte draufhängt id in rdf -> akte_id anhängen

	$onlinebewerbung = ($row->onlinebewerbung)?'ja':'nein';
	$pflicht = ($row->pflicht)?'ja':'nein';
	// Wenn Akten vorhanden anzeigen
	if(count($akte->result) != 0)
	{

		foreach($akte->result as $a)
		{
			$datum='';
			$datumhochgeladen=(isset($a->insertamum))?$date->formatDatum($a->insertamum, 'd.m.Y'):'';
			$nachgereicht = (isset($a->nachgereicht) && $a->nachgereicht)?'ja':'';
			$info = (isset($a->anmerkung))?$akte->result[0]->anmerkung:'';
			$vorhanden = (isset($a->dms_id) || $a->inhalt_vorhanden)?'ja':((isset($a->nachgereicht) && $a->nachgereicht)?'nachgereicht':'nein');

			echo 	'
			  <RDF:li>
					<RDF:Description  id="'.$row->dokument_kurzbz.'/'.$a->akte_id.'"  about="'.$rdf_url.'/'.$row->dokument_kurzbz.'/'.$a->akte_id.'" >
						<DOKUMENT:dokument_kurzbz><![CDATA['.$row->dokument_kurzbz.']]></DOKUMENT:dokument_kurzbz>
						<DOKUMENT:bezeichnung><![CDATA['.($a->titel_intern!=''?$row->bezeichnung.' ('.$a->titel_intern.')':$row->bezeichnung).']]></DOKUMENT:bezeichnung>
						<DOKUMENT:datum><![CDATA['.$datum.']]></DOKUMENT:datum>
						<DOKUMENT:datumhochgeladen>'.$datumhochgeladen.'</DOKUMENT:datumhochgeladen>
						<DOKUMENT:nachgereicht><![CDATA['.$nachgereicht.']]></DOKUMENT:nachgereicht>
						<DOKUMENT:infotext><![CDATA['.$info.']]></DOKUMENT:infotext>
						<DOKUMENT:vorhanden><![CDATA['.$vorhanden.']]></DOKUMENT:vorhanden>
						<DOKUMENT:akte_id><![CDATA['.$a->akte_id.']]></DOKUMENT:akte_id>
						<DOKUMENT:titel_intern><![CDATA['.$a->titel_intern.']]></DOKUMENT:titel_intern>
						<DOKUMENT:anmerkung_intern><![CDATA['.$a->anmerkung_intern.']]></DOKUMENT:anmerkung_intern>
						<DOKUMENT:onlinebewerbung><![CDATA['.$onlinebewerbung.']]></DOKUMENT:onlinebewerbung>
						<DOKUMENT:pflicht><![CDATA['.$pflicht.']]></DOKUMENT:pflicht>
						<DOKUMENT:nachgereicht_am><![CDATA['.$a->nachgereicht_am.']]></DOKUMENT:nachgereicht_am>
					</RDF:Description>
			  </RDF:li>
				  ';
		}
	}
	else // Wenn keine Akten vorhanden sind -> Abzugebende anzeigen
	{
			echo 	'
			  <RDF:li>
					<RDF:Description  id="'.$row->dokument_kurzbz.'"  about="'.$rdf_url.'/'.$row->dokument_kurzbz.'" >
						<DOKUMENT:dokument_kurzbz><![CDATA['.$row->dokument_kurzbz.']]></DOKUMENT:dokument_kurzbz>
						<DOKUMENT:bezeichnung><![CDATA['.$row->bezeichnung.']]></DOKUMENT:bezeichnung>
						<DOKUMENT:datum></DOKUMENT:datum>
						<DOKUMENT:datumhochgeladen></DOKUMENT:datumhochgeladen>
						<DOKUMENT:nachgereicht></DOKUMENT:nachgereicht>
						<DOKUMENT:infotext></DOKUMENT:infotext>
						<DOKUMENT:vorhanden><![CDATA[nein]]></DOKUMENT:vorhanden>
						<DOKUMENT:akte_id></DOKUMENT:akte_id>
						<DOKUMENT:titel_intern></DOKUMENT:titel_intern>
						<DOKUMENT:anmerkung_intern></DOKUMENT:anmerkung_intern>
						<DOKUMENT:onlinebewerbung><![CDATA['.$onlinebewerbung.']]></DOKUMENT:onlinebewerbung>
						<DOKUMENT:pflicht><![CDATA['.$pflicht.']]></DOKUMENT:pflicht>
					</RDF:Description>
			  </RDF:li>
				  ';
	}
}

// Alle Akten/Dokumente holen die Upgeloaded wurden ohne die vom Studiengang und Zeugnisse
$akte = new akte();
if(!$akte->getAkten($prestudent->person_id, null, $prestudent->studiengang_kz, $prestudent->prestudent_id))
	die('fehler');

foreach($akte->result as $a)
{

	$datum='';
	$datumhochgeladen=(isset($a->insertamum))?$date->formatDatum($a->insertamum, 'd.m.Y'):'';
	$nachgereicht = (isset($a->nachgereicht) && $a->nachgereicht)?'ja':'';
	$info = (isset($a->anmerkung))?$akte->result[0]->anmerkung:'';
	$vorhanden = (isset($a->dms_id) || $a->inhalt_vorhanden)?'ja':'nein';
	$dokument_kurzbz = $a->dokument_kurzbz;
	$dokument = new dokument();
	$dokument->loadDokumenttyp($dokument_kurzbz);

	echo 	'
	  <RDF:li>
			<RDF:Description  id="'.$a->dokument_kurzbz.'/'.$a->akte_id.'"  about="'.$rdf_url.'/'.$a->dokument_kurzbz.'/'.$a->akte_id.'" >
				<DOKUMENT:dokument_kurzbz><![CDATA['.$a->dokument_kurzbz.']]></DOKUMENT:dokument_kurzbz>s
				<DOKUMENT:bezeichnung><![CDATA['.($a->dokument_kurzbz=='Sonst' && $a->titel_intern!==''?$dokument->bezeichnung.' ('.$a->titel_intern.')':$dokument->bezeichnung).']]></DOKUMENT:bezeichnung>
				<DOKUMENT:datum>'.$datum.'</DOKUMENT:datum>
				<DOKUMENT:datumhochgeladen>'.$datumhochgeladen.'</DOKUMENT:datumhochgeladen>
				<DOKUMENT:nachgereicht>'.$nachgereicht.'</DOKUMENT:nachgereicht>
				<DOKUMENT:infotext>'.$info.'</DOKUMENT:infotext>
				<DOKUMENT:vorhanden>'.$vorhanden.'</DOKUMENT:vorhanden>
				<DOKUMENT:akte_id>'.$a->akte_id.'</DOKUMENT:akte_id>
				<DOKUMENT:titel_intern><![CDATA['.$a->titel_intern.']]></DOKUMENT:titel_intern>
				<DOKUMENT:anmerkung_intern><![CDATA['.$a->anmerkung_intern.']]></DOKUMENT:anmerkung_intern>
				<DOKUMENT:onlinebewerbung><![CDATA[nein]]></DOKUMENT:onlinebewerbung>
				<DOKUMENT:nachgereicht_am><![CDATA['.$a->nachgereicht_am.']]></DOKUMENT:nachgereicht_am>
			</RDF:Description>
	  </RDF:li>
		  ';
}
?>

  </RDF:Seq>
</RDF:RDF>
