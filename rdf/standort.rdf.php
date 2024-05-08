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
require_once('../include/basis_db.class.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

$rdf_url='http://www.technikum-wien.at/standort';

if(isset($_GET['firma_id']) && is_numeric($_GET['firma_id']))
	$firma_id = $_GET['firma_id'];
else
	unset($firma_id);

if(isset($_GET['standort_id']) && is_numeric($_GET['standort_id']))
	$standort_id = $_GET['standort_id'];
else
	unset($standort_id);

if(isset($_GET['standort_id_all']) && is_numeric($_GET['standort_id_all']))
	$standort_id_all = $_GET['standort_id_all'];
else
	unset($standort_id_all);

if(isset($_GET['firmentyp_kurzbz']))
	$firmentyp_kurzbz = $_GET['firmentyp_kurzbz'];
else
	unset($firmentyp_kurzbz);


echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:STANDORT="'.$rdf_url.'/rdf#"
>
   <RDF:Seq about="'.$rdf_url.'/liste">
';

if(isset($_GET['optional']) && $_GET['optional']=='true')
{
	echo '
	  <RDF:li>
         <RDF:Description  id=""  about="" >
            <STANDORT:standort_id><![CDATA[]]></STANDORT:standort_id>
            <STANDORT:adresse_id><![CDATA[]]></STANDORT:adresse_id>
            <STANDORT:bezeichnung><![CDATA[-- keine Auswahl --]]></STANDORT:bezeichnung>
            <STANDORT:kurzbz><![CDATA[-- keine Auswahl --]]></STANDORT:kurzbz>
            <STANDORT:firma_id><![CDATA[]]></STANDORT:firma_id>
         </RDF:Description>
      </RDF:li>';
}

$qry = "SELECT * FROM public.tbl_standort";

if(isset($firma_id))
	$qry.=" WHERE firma_id='".addslashes($firma_id)."'";
elseif(isset($standort_id))
	$qry.=" WHERE standort_id='".addslashes($standort_id)."'";
elseif(isset($standort_id_all))
	$qry.=" WHERE firma_id=(SELECT firma_id FROM public.tbl_standort WHERE standort_id='".addslashes($standort_id_all)."')";
elseif(isset($firmentyp_kurzbz))
	$qry.=" JOIN public.tbl_firma USING(firma_id) WHERE firmentyp_kurzbz='".addslashes($firmentyp_kurzbz)."'";
else
	die('Fehlerhafte Parameteruebergabe');
$qry.=" ORDER BY kurzbz";

$db = new basis_db();
if($db->db_query($qry))
	while($row = $db->db_fetch_object())
		draw_content($row);

function draw_content($row)
{
	global $rdf_url, $datum;

	echo '
		  <RDF:li>
	         <RDF:Description  id="'.$row->standort_id.'"  about="'.$rdf_url.'/'.$row->standort_id.'" >
	            <STANDORT:standort_id><![CDATA['.$row->standort_id.']]></STANDORT:standort_id>
	            <STANDORT:adresse_id><![CDATA['.$row->adresse_id.']]></STANDORT:adresse_id>
	            <STANDORT:bezeichnung><![CDATA['.$row->bezeichnung.']]></STANDORT:bezeichnung>
				<STANDORT:bezeichnung_null><![CDATA['.(is_null($row->bezeichnung)?'t':'').']]></STANDORT:bezeichnung_null>
	            <STANDORT:kurzbz><![CDATA['.$row->kurzbz.']]></STANDORT:kurzbz>
	            <STANDORT:firma_id><![CDATA['.$row->firma_id.']]></STANDORT:firma_id>
	         </RDF:Description>
	      </RDF:li>';
}
?>
   </RDF:Seq>
</RDF:RDF>
