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
require_once('../config/vilesci.config.inc.php');
require_once('../include/ort.class.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

$rdf_url='http://www.technikum-wien.at/ort';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:ORT="'.$rdf_url.'/rdf#"
>
   <RDF:Seq about="'.$rdf_url.'/liste">
';

if(isset($_GET['optional']) && $_GET['optional']=='true')
{
	echo '
	  <RDF:li>
         <RDF:Description  id=""  about="" >
            <ORT:ort_kurzbz><![CDATA[]]></ORT:ort_kurzbz>
            <ORT:bezeichnung><![CDATA[]]></ORT:bezeichnung>
            <ORT:planbezeichnung><![CDATA[]]></ORT:planbezeichnung>
            <ORT:max_person><![CDATA[]]></ORT:max_person>
            <ORT:lehre><![CDATA[]]></ORT:lehre>
            <ORT:reservieren><![CDATA[]]></ORT:reservieren>
            <ORT:aktiv><![CDATA[]]></ORT:aktiv>
			<ORT:lageplan><![CDATA[]]></ORT:lageplan>
            <ORT:dislozierung><![CDATA[]]></ORT:dislozierung>
            <ORT:kosten><![CDATA[]]></ORT:kosten>
            <ORT:ausstattung><![CDATA[]]></ORT:ausstattung>
            <ORT:anzeigename><![CDATA[-- keine Auswahl --]]></ORT:anzeigename>
         </RDF:Description>
      </RDF:li>';
}
//Daten holen
$ortobj = new ort();

$ortobj->getAll();
foreach ($ortobj->result as $row)
	draw_content($row);

function draw_content($row)
{
	global $rdf_url, $datum;

	echo '
		  <RDF:li>
	         <RDF:Description  id="'.$row->ort_kurzbz.'"  about="'.$rdf_url.'/'.$row->ort_kurzbz.'" >
	            <ORT:ort_kurzbz><![CDATA['.$row->ort_kurzbz.']]></ORT:ort_kurzbz>
	            <ORT:bezeichnung><![CDATA['.$row->bezeichnung.']]></ORT:bezeichnung>
	            <ORT:planbezeichnung><![CDATA['.$row->planbezeichnung.']]></ORT:planbezeichnung>
	            <ORT:max_person><![CDATA['.$row->max_person.']]></ORT:max_person>
	            <ORT:lehre><![CDATA['.($row->lehre?'Ja':'Nein').']]></ORT:lehre>
	            <ORT:reservieren><![CDATA['.($row->reservieren?'Ja':'Nein').']]></ORT:reservieren>
	            <ORT:aktiv><![CDATA['.($row->aktiv?'Ja':'Nein').']]></ORT:aktiv>
				<ORT:lageplan><![CDATA['.$row->lageplan.']]></ORT:lageplan>
	            <ORT:dislozierung><![CDATA['.$row->dislozierung.']]></ORT:dislozierung>
	            <ORT:kosten><![CDATA['.$row->kosten.']]></ORT:kosten>
	            <ORT:ausstattung><![CDATA['.$row->ausstattung.']]></ORT:ausstattung>
	            <ORT:anzeigename><![CDATA['.$row->ort_kurzbz.']]></ORT:anzeigename>
	         </RDF:Description>
	      </RDF:li>';
}
?>
   </RDF:Seq>
</RDF:RDF>