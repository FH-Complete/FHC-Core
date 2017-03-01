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
require_once('../include/firma.class.php');
require_once('../include/datum.class.php');

if(isset($_GET['firma_id']))
	$firma_id = $_GET['firma_id'];
else
	$firma_id = '';

if(isset($_GET['filter']))
	$filter = $_GET['filter'];
else
	$filter = '';

if(isset($_GET['firmentyp_kurzbz']))
	$firmentyp_kurzbz = $_GET['firmentyp_kurzbz'];
else
	$firmentyp_kurzbz='';

if(isset($_GET['partner']))
	$partner = true;
else
	$partner = false;

$datum = new datum();

$firma = new firma();

$rdf_url='http://www.technikum-wien.at/firma';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:FIRMA="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

if(isset($_GET['optional']) && $_GET['optional']=='true')
{
	echo '
      <RDF:li>
         <RDF:Description  id=""  about="" >
            <FIRMA:firma_id><![CDATA[]]></FIRMA:firma_id>
            <FIRMA:name><![CDATA[-- keine Auswahl --]]></FIRMA:name>
            <FIRMA:anmerkung><![CDATA[]]></FIRMA:anmerkung>
            <FIRMA:firmentyp_kurzbz><![CDATA[]]></FIRMA:firmentyp_kurzbz>
         </RDF:Description>
      </RDF:li>
      ';
}

if($firma_id!='')
{
	$firma->load($firma_id);
	draw_rdf($firma);
}
elseif($firmentyp_kurzbz!='' || $filter!='')
{
	$firma->searchFirma($filter, $firmentyp_kurzbz);
	foreach ($firma->result as $row)
		draw_rdf($row);
}
elseif($partner)
{
	$firma->getFirmaPartner();
	foreach ($firma->result as $row)
		draw_rdf($row);
}
else
{
	//$firma->getAll($firma_id);
	//foreach ($firma->result as $row)
	//	draw_rdf($row);
}

function draw_rdf($row)
{
	global $rdf_url;

	echo '
      <RDF:li>
         <RDF:Description  id="'.$row->firma_id.'"  about="'.$rdf_url.'/'.$row->firma_id.'" >
            <FIRMA:firma_id><![CDATA['.$row->firma_id.']]></FIRMA:firma_id>
            <FIRMA:name><![CDATA['.$row->name.']]></FIRMA:name>
            <FIRMA:anmerkung><![CDATA['.$row->anmerkung.']]></FIRMA:anmerkung>
            <FIRMA:firmentyp_kurzbz><![CDATA['.$row->firmentyp_kurzbz.']]></FIRMA:firmentyp_kurzbz>
         </RDF:Description>
      </RDF:li>
      ';
}
?>
   </RDF:Seq>
</RDF:RDF>