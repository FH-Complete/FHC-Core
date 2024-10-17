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
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../config/vilesci.config.inc.php');
require_once('../include/kontakt.class.php');
require_once('../include/datum.class.php');

if(isset($_GET['person_id']))
	$person_id = $_GET['person_id'];
else
	$person_id = '';

if(isset($_GET['kontakt_id']))
	$kontakt_id = $_GET['kontakt_id'];
else
	$kontakt_id = '';

$datum = new datum();

$kontakt = new kontakt();

$rdf_url='http://www.technikum-wien.at/kontakt';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:KONTAKT="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

if($kontakt_id!='')
{
	$kontakt->load($kontakt_id);
	draw_rdf($kontakt);
}
else
{
	$kontakt->load_pers($person_id);
	foreach ($kontakt->result as $row)
		draw_rdf($row);
}

function draw_rdf($row)
{
	global $rdf_url;
	if($row->kontakttyp == 'hidden')
		return;
	echo '
      <RDF:li>
         <RDF:Description  id="'.$row->kontakt_id.'"  about="'.$rdf_url.'/'.$row->kontakt_id.'" >
            <KONTAKT:kontakt_id><![CDATA['.$row->kontakt_id.']]></KONTAKT:kontakt_id>
            <KONTAKT:person_id><![CDATA['.$row->person_id.']]></KONTAKT:person_id>
            <KONTAKT:firma_id><![CDATA['.$row->firma_id.']]></KONTAKT:firma_id>
            <KONTAKT:firma_name><![CDATA['.$row->firma_name.']]></KONTAKT:firma_name>
            <KONTAKT:standort_id><![CDATA['.$row->standort_id.']]></KONTAKT:standort_id>
            <KONTAKT:kontakttyp><![CDATA['.$row->kontakttyp.']]></KONTAKT:kontakttyp>
            <KONTAKT:anmerkung><![CDATA['.$row->anmerkung.']]></KONTAKT:anmerkung>
            <KONTAKT:kontakt><![CDATA['.$row->kontakt.']]></KONTAKT:kontakt>
            <KONTAKT:zustellung><![CDATA['.($row->zustellung?'Ja':'Nein').']]></KONTAKT:zustellung>
            <KONTAKT:updateamum><![CDATA['.($row->updateamum!=''?date('d.m.Y H:i:s',strtotime($row->updateamum)):'').']]></KONTAKT:updateamum>
         </RDF:Description>
      </RDF:li>
      ';
}
?>
   </RDF:Seq>
</RDF:RDF>
