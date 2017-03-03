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
require_once('../include/bisfunktion.class.php');
require_once('../include/datum.class.php');
require_once('../include/studiengang.class.php');

if(isset($_GET['bisverwendung_id']))
	$bisverwendung_id = $_GET['bisverwendung_id'];
else
	$bisverwendung_id = '';

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

$bisfunktion = new bisfunktion();
if(!$bisfunktion->getBisFunktion($bisverwendung_id, $studiengang_kz))
	die($bisfunktion->errormsg);
$rdf_url='http://www.technikum-wien.at/bisfunktion';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:BISFUNKTION="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

foreach ($bisfunktion->result as $row)
{
	echo '
      <RDF:li>
         <RDF:Description  id="'.$row->bisverwendung_id.'/'.$row->studiengang_kz.'"  about="'.$rdf_url.'/'.$row->bisverwendung_id.'/'.$row->studiengang_kz.'" >
            <BISFUNKTION:bisverwendung_id><![CDATA['.$row->bisverwendung_id.']]></BISFUNKTION:bisverwendung_id>
            <BISFUNKTION:studiengang_kz><![CDATA['.$row->studiengang_kz.']]></BISFUNKTION:studiengang_kz>
            <BISFUNKTION:sws><![CDATA['.$row->sws.']]></BISFUNKTION:sws>
            <BISFUNKTION:studiengang><![CDATA['.$stg_arr[$row->studiengang_kz].']]></BISFUNKTION:studiengang>
         </RDF:Description>
      </RDF:li>
      ';
}
?>
   </RDF:Seq>
</RDF:RDF>