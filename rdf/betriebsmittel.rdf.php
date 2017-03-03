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
require_once('../include/betriebsmittel.class.php');
require_once('../include/datum.class.php');

if(isset($_GET['betriebsmittel_id']))
	$betriebsmittel_id = $_GET['betriebsmittel_id'];
else
	$betriebsmittel_id = '';

if(isset($_GET['filter']))
	$filter = $_GET['filter'];
else
	$filter = '';

if(isset($_GET['datum']))
	$datum = $_GET['datum'];
else
	$datum = '';

if(isset($_GET['stunde']))
	$stunde = $_GET['stunde'];
else
	$stunde = '';

$betriebsmittel = new betriebsmittel();

$rdf_url='http://www.technikum-wien.at/betriebsmittel';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:BETRIEBSMITTEL="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

if(isset($_GET['optional']) && $_GET['optional']=='true')
{
	echo '
      <RDF:li>
         <RDF:Description  id=""  about="" >
            <BETRIEBSMITTEL:betriebsmittel_id><![CDATA[]]></BETRIEBSMITTEL:betriebsmittel_id>
            <BETRIEBSMITTEL:beschreibung><![CDATA[-- keine Auswahl --]]></BETRIEBSMITTEL:beschreibung>
            <BETRIEBSMITTEL:betriebsmitteltyp><![CDATA[]]></BETRIEBSMITTEL:betriebsmitteltyp>
            <BETRIEBSMITTEL:inventarnummer><![CDATA[]]></BETRIEBSMITTEL:inventarnummer>
         </RDF:Description>
      </RDF:li>
      ';
}

if($betriebsmittel_id!='')
{
	$betriebsmittel->load($betriebsmittel_id);
	draw_rdf($betriebsmittel);
}
elseif($filter!='')
{
	$betriebsmittel->searchBetriebsmittel($filter);

	foreach ($betriebsmittel->result as $row)
		draw_rdf($row);


}
elseif($datum!='')
{
	$betriebsmittel->getVerplanbar($datum, $stunde);

	if(isset($betriebsmittel->result) && count($betriebsmittel->result)>0)
	{
		foreach ($betriebsmittel->result as $row)
			draw_rdf($row);
	}
}


function draw_rdf($row)
{
	global $rdf_url;

	echo '
      <RDF:li>
         <RDF:Description  id="'.$row->betriebsmittel_id.'"  about="'.$rdf_url.'/'.$row->betriebsmittel_id.'" >
            <BETRIEBSMITTEL:betriebsmittel_id><![CDATA['.$row->betriebsmittel_id.']]></BETRIEBSMITTEL:betriebsmittel_id>
            <BETRIEBSMITTEL:beschreibung><![CDATA['.$row->beschreibung.']]></BETRIEBSMITTEL:beschreibung>
            <BETRIEBSMITTEL:betriebsmitteltyp><![CDATA['.$row->betriebsmitteltyp.']]></BETRIEBSMITTEL:betriebsmitteltyp>
            <BETRIEBSMITTEL:inventarnummer><![CDATA['.$row->inventarnummer.']]></BETRIEBSMITTEL:inventarnummer>
         </RDF:Description>
      </RDF:li>
      ';
}
?>
   </RDF:Seq>
</RDF:RDF>
