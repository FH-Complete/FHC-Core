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
require_once('../include/studiengang.class.php');
require_once('../include/funktion.class.php');
require_once('../include/organisationseinheit.class.php');
require_once('../include/datum.class.php');

$rdf_url='http://www.technikum-wien.at/bnfunktion';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:BNFUNKTION="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';
$uid = (isset($_GET['uid'])?$_GET['uid']:'');
$benutzerfunktion_id = (isset($_GET['benutzerfunktion_id'])?$_GET['benutzerfunktion_id']:'');
$filter = (isset($_GET['filter'])?$_GET['filter']:'');
$stg_arr = array();
$fkt_arr = array();
$datum_obj = new datum();
$fkt = new funktion();
$fkt->getAll();

foreach ($fkt->result as $row)
	$fkt_arr[$row->funktion_kurzbz] = $row->beschreibung;

$db = new basis_db();

if($uid!='')
{
	$qry = "SELECT * FROM public.tbl_benutzerfunktion WHERE uid=".$db->db_add_param($uid);
	if($filter == 'offene')
		$qry .= " AND (datum_bis>=now() OR datum_bis is null)";
	$qry.=" ORDER BY funktion_kurzbz";
}
else
{
	$qry = "SELECT * FROM public.tbl_benutzerfunktion WHERE benutzerfunktion_id=".$db->db_add_param($benutzerfunktion_id);
}

if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{
		$oe = new organisationseinheit($row->oe_kurzbz);
		echo '
	      <RDF:li>
		     <RDF:Description  id="'.$row->benutzerfunktion_id.'"  about="'.$rdf_url.'/'.$row->benutzerfunktion_id.'" >
		     	<BNFUNKTION:benutzerfunktion_id><![CDATA['.$row->benutzerfunktion_id.']]></BNFUNKTION:benutzerfunktion_id>
		        <BNFUNKTION:fachbereich_kurzbz><![CDATA['.$row->fachbereich_kurzbz.']]></BNFUNKTION:fachbereich_kurzbz>
		        <BNFUNKTION:uid><![CDATA['.$row->uid.']]></BNFUNKTION:uid>
		        <BNFUNKTION:oe_kurzbz><![CDATA['.$row->oe_kurzbz.']]></BNFUNKTION:oe_kurzbz>
		        <BNFUNKTION:organisationseinheit><![CDATA['.$oe->organisationseinheittyp_kurzbz.' '.$oe->bezeichnung.']]></BNFUNKTION:organisationseinheit>
		        <BNFUNKTION:semester><![CDATA['.$row->semester.']]></BNFUNKTION:semester>
		        <BNFUNKTION:funktion_kurzbz><![CDATA['.$row->funktion_kurzbz.']]></BNFUNKTION:funktion_kurzbz>
		        <BNFUNKTION:funktion><![CDATA['.$fkt_arr[$row->funktion_kurzbz].']]></BNFUNKTION:funktion>
		        <BNFUNKTION:datum_von><![CDATA['.$datum_obj->convertISODate($row->datum_von).']]></BNFUNKTION:datum_von>
		        <BNFUNKTION:datum_von_iso><![CDATA['.$row->datum_von.']]></BNFUNKTION:datum_von_iso>
		        <BNFUNKTION:datum_bis><![CDATA['.$datum_obj->convertISODate($row->datum_bis).']]></BNFUNKTION:datum_bis>
		        <BNFUNKTION:datum_bis_iso><![CDATA['.$row->datum_bis.']]></BNFUNKTION:datum_bis_iso>
		        <BNFUNKTION:bezeichnung><![CDATA['.$row->bezeichnung.']]></BNFUNKTION:bezeichnung>
		        <BNFUNKTION:wochenstunden><![CDATA['.$row->wochenstunden.']]></BNFUNKTION:wochenstunden>
		     </RDF:Description>
		  </RDF:li>';
	}
}
?>
   </RDF:Seq>
</RDF:RDF>
