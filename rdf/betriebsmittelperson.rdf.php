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
// header f�r no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/betriebsmittelperson.class.php');
require_once('../include/datum.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

if(isset($_GET['person_id']))
	$person_id = $_GET['person_id'];
else 
	$person_id = '';

if(isset($_GET['betriebsmitteltyp']))
	$betriebsmitteltyp = $_GET['betriebsmitteltyp'];
else 
	$betriebsmitteltyp = null;
	
if(isset($_GET['betriebsmittel_id']))
	$betriebsmittel_id = $_GET['betriebsmittel_id'];
else 
	$betriebsmittel_id = null;

$datum = new datum();

$rdf_url='http://www.technikum-wien.at/betriebsmittel';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:BTM="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">';


$betriebsmittel = new betriebsmittelperson($conn, null, null, true);
if($betriebsmittel_id=='')
	if($betriebsmittel->getBetriebsmittelPerson($person_id, $betriebsmitteltyp))
		foreach ($betriebsmittel->result as $row)
			draw_content($row);
	else 
		die($betriebsmittel->errormsg);
else 
	if($betriebsmittel->load($betriebsmittel_id, $person_id))
		draw_content($betriebsmittel);
	else 
		die($betriebsmittel->errormsg);

function draw_content($row)
{
	global $rdf_url, $datum;
	
	echo '
      <RDF:li>
         <RDF:Description  id="'.$row->person_id.'/'.$row->betriebsmittel_id.'"  about="'.$rdf_url.'/'.$row->person_id.'/'.$row->betriebsmittel_id.'" >
            <BTM:betriebsmittel_id><![CDATA['.$row->betriebsmittel_id.']]></BTM:betriebsmittel_id>
            <BTM:beschreibung><![CDATA['.$row->beschreibung.']]></BTM:beschreibung>
            <BTM:betriebsmitteltyp><![CDATA['.$row->betriebsmitteltyp.']]></BTM:betriebsmitteltyp>
            <BTM:nummer><![CDATA['.$row->nummer.']]></BTM:nummer>
            <BTM:reservieren><![CDATA['.($row->reservieren?'Ja':'Nein').']]></BTM:reservieren>
            <BTM:ort_kurzbz><![CDATA['.$row->ort_kurzbz.']]></BTM:ort_kurzbz>            
            <BTM:person_id><![CDATA['.$row->person_id.']]></BTM:person_id>
            <BTM:anmerkung><![CDATA['.$row->anmerkung.']]></BTM:anmerkung>
            <BTM:kaution><![CDATA['.$row->kaution.']]></BTM:kaution>
            <BTM:ausgegebenam_iso><![CDATA['.$row->ausgegebenam.']]></BTM:ausgegebenam_iso>
            <BTM:ausgegebenam><![CDATA['.$datum->convertISODate($row->ausgegebenam).']]></BTM:ausgegebenam>
            <BTM:retouram_iso><![CDATA['.$row->retouram.']]></BTM:retouram_iso>
            <BTM:retouram><![CDATA['.$datum->convertISODate($row->retouram).']]></BTM:retouram>
         </RDF:Description>
      </RDF:li>';

}
?>
   </RDF:Seq>

</RDF:RDF>