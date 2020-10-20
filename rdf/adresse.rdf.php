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
require_once('../include/adresse.class.php');
require_once('../include/datum.class.php');

if(isset($_GET['person_id']))
	$person_id = $_GET['person_id'];
else
	$person_id = '';

if(isset($_GET['adresse_id']))
	$adresse_id = $_GET['adresse_id'];
else
	$adresse_id = '';

$datum = new datum();

$adresse = new adresse();

$rdf_url='http://www.technikum-wien.at/adresse';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:ADRESSE="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

if($adresse_id!='')
{
	if(!$adresse->load($adresse_id))
		die('Fehler: '.$adresse->errormsg);
	draw_rdf($adresse);
}
else
{
	$adresse->load_pers($person_id);
	foreach ($adresse->result as $row)
		draw_rdf($row);
}

function draw_rdf($row)
{
	global $rdf_url;
	$db = new basis_db();

	$typ='';
	switch ($row->typ)
	{
		case 'h': $typ='Hauptwohnsitz'; break;
		case 'n': $typ='Nebenwohnsitz'; break;
		case 'f': $typ='Firma'; break;
		case 'r': $typ='Rechnungsadresse'; break;
	}

	$firma_name='';
	if($row->firma_id!='')
	{
		$qry="SELECT * FROM public.tbl_firma WHERE firma_id='".addslashes($row->firma_id)."'";
		if($db->db_query($qry))
		{
			if($row_firma = $db->db_fetch_object())
			{
				$firma_name = $row_firma->name;
			}
		}
	}
	echo '
      <RDF:li>
         <RDF:Description  id="'.$row->adresse_id.'"  about="'.$rdf_url.'/'.$row->adresse_id.'" >
            <ADRESSE:adresse_id><![CDATA['.$row->adresse_id.']]></ADRESSE:adresse_id>
            <ADRESSE:person_id><![CDATA['.$row->person_id.']]></ADRESSE:person_id>
            <ADRESSE:name><![CDATA['.$row->name.']]></ADRESSE:name>
            <ADRESSE:strasse><![CDATA['.$row->strasse.']]></ADRESSE:strasse>
            <ADRESSE:plz><![CDATA['.$row->plz.']]></ADRESSE:plz>
            <ADRESSE:ort><![CDATA['.$row->ort.']]></ADRESSE:ort>
            <ADRESSE:gemeinde><![CDATA['.$row->gemeinde.']]></ADRESSE:gemeinde>
            <ADRESSE:nation><![CDATA['.$row->nation.']]></ADRESSE:nation>
            <ADRESSE:typ><![CDATA['.$row->typ.']]></ADRESSE:typ>
            <ADRESSE:typ_name><![CDATA['.$typ.']]></ADRESSE:typ_name>
            <ADRESSE:heimatadresse><![CDATA['.($row->heimatadresse?'Ja':'Nein').']]></ADRESSE:heimatadresse>
            <ADRESSE:zustelladresse><![CDATA['.($row->zustelladresse?'Ja':'Nein').']]></ADRESSE:zustelladresse>
            <ADRESSE:co_name><![CDATA['. $row->co_name.']]></ADRESSE:co_name>
            <ADRESSE:firma_id><![CDATA['.$row->firma_id.']]></ADRESSE:firma_id>
            <ADRESSE:firma_name><![CDATA['.$firma_name.']]></ADRESSE:firma_name>
            <ADRESSE:updateamum><![CDATA['.date('d.m.Y H:i:s',strtotime($row->updateamum)).']]></ADRESSE:updateamum>
			<ADRESSE:rechnungsadresse><![CDATA['.($row->rechnungsadresse?'Ja':'Nein').']]></ADRESSE:rechnungsadresse>
			<ADRESSE:anmerkung><![CDATA['.$row->anmerkung.']]></ADRESSE:anmerkung>
         </RDF:Description>
      </RDF:li>
      ';
}
?>
   </RDF:Seq>
</RDF:RDF>
