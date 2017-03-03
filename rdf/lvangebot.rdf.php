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
 * Authors: Nikolaus Krondraf <nikolaus.krondraf@technikum-wien.at>
 */
if(isset($_GET['lehrveranstaltung_id']))
	$lv_id = $_GET['lehrveranstaltung_id'];
else
	die('lehrveranstaltung_id muss uebergeben werden');

// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/xhtml+xml");

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

require_once('../config/vilesci.config.inc.php');
require_once('../include/basis_db.class.php');
require_once('../include/datum.class.php');
require_once('../include/lvangebot.class.php');

$datum_obj = new datum();
$rdf_url = 'http://www.technikum-wien.at/lvangebot';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:GRP="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

// Vorhandene Eintraege anzeigen
$lvangebotliste = new lvangebot();
$lvangebotliste->getAllFromLvId($lv_id);

foreach($lvangebotliste->result as $lvang)
{
	echo '
		<RDF:li>
		   <RDF:Description  id="'.$lvang->lvangebot_id.'"  about="'.$rdf_url.'/'.$lvang->lvangebot_id.'" >
			  <GRP:lvangebot_id><![CDATA['.$lvang->lvangebot_id.']]></GRP:lvangebot_id>
			  <GRP:gruppe_kurzbz><![CDATA['.$lvang->gruppe_kurzbz.']]></GRP:gruppe_kurzbz>
			  <GRP:plaetze_inc><![CDATA['.$lvang->incomingplaetze.']]></GRP:plaetze_inc>
			  <GRP:plaetze_gesamt><![CDATA['.$lvang->gesamtplaetze.']]></GRP:plaetze_gesamt>
			  <GRP:anmeldefenster_start><![CDATA['.$datum_obj->formatDatum($lvang->anmeldefenster_start,'d.m.Y').']]></GRP:anmeldefenster_start>
			  <GRP:anmeldefenster_ende><![CDATA['.$datum_obj->formatDatum($lvang->anmeldefenster_ende,'d.m.Y').']]></GRP:anmeldefenster_ende>
		   </RDF:Description>
		</RDF:li>
		';
}

?>
   </RDF:Seq>
</RDF:RDF>