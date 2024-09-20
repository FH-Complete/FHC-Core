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
require_once('../include/entwicklungsteam.class.php');
require_once('../include/datum.class.php');
require_once('../include/studiengang.class.php');

if(isset($_GET['entwicklungsteam_id']))
	$entwicklungsteam_id = $_GET['entwicklungsteam_id'];
else
	$entwicklungsteam_id = '';

if(isset($_GET['mitarbeiter_uid']))
	$mitarbeiter_uid = $_GET['mitarbeiter_uid'];
else
	$mitarbeiter_uid = '';

if(isset($_GET['studiengang_kz']))
	$studiengang_kz = $_GET['studiengang_kz'];
else
	$studiengang_kz = '';


$datum = new datum();
$stg = new studiengang();
$stg->getAll(null, false);
$stg_arr = array();

foreach ($stg->result as $row)
	$stg_arr[$row->studiengang_kz]=$row->kuerzel;

$entwicklungsteam = new entwicklungsteam();
if(!$entwicklungsteam->getEntwicklungsteam($mitarbeiter_uid, $studiengang_kz))
	die($entwicklungsteam->errormsg);
$rdf_url='http://www.technikum-wien.at/entwicklungsteam';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:ENTWICKLUNGSTEAM="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

foreach ($entwicklungsteam->result as $row)
{
	echo '
      <RDF:li>
				 <RDF:Description  id="'.$row->mitarbeiter_uid.'/'.$row->studiengang_kz.'/'.$row->entwicklungsteam_id.'"
				 about="'.$rdf_url.'/'.$row->mitarbeiter_uid.'/'.$row->studiengang_kz.'/'.$row->entwicklungsteam_id.'" >
				 	  <ENTWICKLUNGSTEAM:entwicklungsteam_id><![CDATA['.$row->entwicklungsteam_id.']]></ENTWICKLUNGSTEAM:entwicklungsteam_id>
						<ENTWICKLUNGSTEAM:mitarbeiter_uid><![CDATA['.$row->mitarbeiter_uid.']]></ENTWICKLUNGSTEAM:mitarbeiter_uid>
            <ENTWICKLUNGSTEAM:studiengang_kz><![CDATA['.$row->studiengang_kz.']]></ENTWICKLUNGSTEAM:studiengang_kz>
            <ENTWICKLUNGSTEAM:besqualcode><![CDATA['.$row->besqualcode.']]></ENTWICKLUNGSTEAM:besqualcode>
            <ENTWICKLUNGSTEAM:besqual><![CDATA['.$row->besqual.']]></ENTWICKLUNGSTEAM:besqual>
            <ENTWICKLUNGSTEAM:beginn><![CDATA['.$datum->convertISODate($row->beginn).']]></ENTWICKLUNGSTEAM:beginn>
            <ENTWICKLUNGSTEAM:beginn_iso><![CDATA['.$row->beginn.']]></ENTWICKLUNGSTEAM:beginn_iso>
            <ENTWICKLUNGSTEAM:ende><![CDATA['.$datum->convertISODate($row->ende).']]></ENTWICKLUNGSTEAM:ende>
            <ENTWICKLUNGSTEAM:ende_iso><![CDATA['.$row->ende.']]></ENTWICKLUNGSTEAM:ende_iso>
            <ENTWICKLUNGSTEAM:studiengang><![CDATA['.$stg_arr[$row->studiengang_kz].']]></ENTWICKLUNGSTEAM:studiengang>
         </RDF:Description>
      </RDF:li>
      ';
}
?>
   </RDF:Seq>
</RDF:RDF>
