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
require_once('../include/bisverwendung.class.php');
require_once('../include/datum.class.php');

if(isset($_GET['uid']))
	$uid = $_GET['uid'];
else
	$uid = '';

if(isset($_GET['bisverwendung_id']) && is_numeric($_GET['bisverwendung_id']))
	$bisverwendung_id = $_GET['bisverwendung_id'];
else
	$bisverwendung_id = '';

$datum = new datum();

$verwendung_obj = new bisverwendung();

$rdf_url='http://www.technikum-wien.at/bisverwendung';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:VERWENDUNG="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

if($uid!='')
{
	$verwendung_obj->getVerwendung($uid);
	foreach ($verwendung_obj->result as $row)
		draw_row($row);
}
elseif($bisverwendung_id!='')
{
	if($verwendung_obj->load($bisverwendung_id))
		draw_row($verwendung_obj);
	else
		die($verwendung_obj->errormsg);
}
else
	die('Falsche Parameteruebergabe');



function draw_row($row)
{
	global $rdf_url, $datum;

	if(is_bool($row->hauptberuflich))
		$hauptberuflich = $row->hauptberuflich?'Ja':'Nein';
	else
		$hauptberuflich = '';

	if(is_bool($row->zeitaufzeichnungspflichtig))
		$zeitaufzeichnungspflichtig = $row->zeitaufzeichnungspflichtig?'Ja':'Nein';
	else
		$zeitaufzeichnungspflichtig = '';

	if(is_bool($row->azgrelevant))
		$azgrelevant = $row->azgrelevant?'Ja':'Nein';
	else
		$azgrelevant = '';
	
	if(is_bool($row->homeoffice))
		$homeoffice = $row->homeoffice?'Ja':'Nein';
	else
		$homeoffice = '';

	echo '
      <RDF:li>
         <RDF:Description  id="'.$row->bisverwendung_id.'"  about="'.$rdf_url.'/'.$row->bisverwendung_id.'" >
            <VERWENDUNG:bisverwendung_id><![CDATA['.$row->bisverwendung_id.']]></VERWENDUNG:bisverwendung_id>
            <VERWENDUNG:ba1code><![CDATA['.$row->ba1code.']]></VERWENDUNG:ba1code>
            <VERWENDUNG:ba2code><![CDATA['.$row->ba2code.']]></VERWENDUNG:ba2code>
            <VERWENDUNG:beschausmasscode><![CDATA['.$row->beschausmasscode.']]></VERWENDUNG:beschausmasscode>
            <VERWENDUNG:verwendung_code><![CDATA['.$row->verwendung_code.']]></VERWENDUNG:verwendung_code>
            <VERWENDUNG:mitarbeiter_uid><![CDATA['.$row->mitarbeiter_uid.']]></VERWENDUNG:mitarbeiter_uid>
            <VERWENDUNG:hauptberufcode><![CDATA['.$row->hauptberufcode.']]></VERWENDUNG:hauptberufcode>
            <VERWENDUNG:hauptberuflich><![CDATA['.$hauptberuflich.']]></VERWENDUNG:hauptberuflich>
            <VERWENDUNG:habilitation><![CDATA['.($row->habilitation?'Ja':'Nein').']]></VERWENDUNG:habilitation>
            <VERWENDUNG:beginn><![CDATA['.$datum->convertISODate($row->beginn).']]></VERWENDUNG:beginn>
            <VERWENDUNG:beginn_iso><![CDATA['.$row->beginn.']]></VERWENDUNG:beginn_iso>
            <VERWENDUNG:ende><![CDATA['.$datum->convertISODate($row->ende).']]></VERWENDUNG:ende>
            <VERWENDUNG:ende_iso><![CDATA['.$row->ende.']]></VERWENDUNG:ende_iso>
            <VERWENDUNG:ba1bez><![CDATA['.$row->ba1bez.']]></VERWENDUNG:ba1bez>
            <VERWENDUNG:ba2bez><![CDATA['.$row->ba2bez.']]></VERWENDUNG:ba2bez>
            <VERWENDUNG:ausmass><![CDATA['.$row->beschausmass.']]></VERWENDUNG:ausmass>
            <VERWENDUNG:verwendung><![CDATA['.$row->verwendung.']]></VERWENDUNG:verwendung>
            <VERWENDUNG:hauptberuf><![CDATA['.$row->hauptberuf.']]></VERWENDUNG:hauptberuf>
            <VERWENDUNG:vertragsstunden><![CDATA['.$row->vertragsstunden.']]></VERWENDUNG:vertragsstunden>
            <VERWENDUNG:updateamum><![CDATA['.$row->updateamum.']]></VERWENDUNG:updateamum>
            <VERWENDUNG:updatevon><![CDATA['.$row->updatevon.']]></VERWENDUNG:updatevon>
            <VERWENDUNG:insertamum><![CDATA['.$row->insertamum.']]></VERWENDUNG:insertamum>
            <VERWENDUNG:insertvon><![CDATA['.$row->insertvon.']]></VERWENDUNG:insertvon>
			<VERWENDUNG:dv_art><![CDATA['.$row->dv_art.']]></VERWENDUNG:dv_art>
			<VERWENDUNG:inkludierte_lehre><![CDATA['.$row->inkludierte_lehre.']]></VERWENDUNG:inkludierte_lehre>
			<VERWENDUNG:zeitaufzeichnungspflichtig><![CDATA['.$zeitaufzeichnungspflichtig.']]></VERWENDUNG:zeitaufzeichnungspflichtig>
			<VERWENDUNG:azgrelevant><![CDATA['.$azgrelevant.']]></VERWENDUNG:azgrelevant>
			<VERWENDUNG:homeoffice><![CDATA['.$homeoffice.']]></VERWENDUNG:homeoffice>
         </RDF:Description>
      </RDF:li>
      ';
}
?>
   </RDF:Seq>
</RDF:RDF>
