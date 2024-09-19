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
require_once('../include/bisio.class.php');
require_once('../include/datum.class.php');

	if (!$db = new basis_db())
    	  die('Fehler beim Oeffnen der Datenbankverbindung');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

if(isset($_GET['uid']))
	$uid = $_GET['uid'];
else
	$uid = null;

if(isset($_GET['bisio_id']))
	$bisio_id = $_GET['bisio_id'];
else
	$bisio_id = null;

$datum = new datum();

$rdf_url='http://www.technikum-wien.at/bisio';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:IO="'.$rdf_url.'/rdf#"
>
   <RDF:Seq about="'.$rdf_url.'/liste">
';

//Daten holen
$ioobj = new bisio();

//Wenn die UID uebergeben wurde, dann werden alle
//Eintraege dieser Person geladen
if($uid)
{
	if($ioobj->getIO($uid))
	{
		foreach ($ioobj->result as $row)
			draw_content($row);
	}
	else
		die($ioobj->errormsg);
}
elseif($bisio_id)
{
	//Wenn nur die ID uebergeben wurde, dann wird nur
	//dieser eine Datensatz geladen
	if($ioobj->load($bisio_id))
		draw_content($ioobj);
	else
		die($ioobj->errormsg);
}
else
	die('Falsche Parameteruebergabe');

function draw_content($row)
{
	global $rdf_url, $datum, $db;
	$lehrveranstaltung_id='';
	$studiensemester_kurzbz = '';

	if($row->lehreinheit_id!='')
	{
		$qry = "SELECT lehrveranstaltung_id, studiensemester_kurzbz FROM lehre.tbl_lehreinheit WHERE lehreinheit_id='$row->lehreinheit_id'";
		if($result = $db->db_query($qry))
		{
			if($row_lv = $db->db_fetch_object($result))
			{
				$lehrveranstaltung_id = $row_lv->lehrveranstaltung_id;
				$studiensemester_kurzbz = $row_lv->studiensemester_kurzbz;
			}
		}
	}

	echo '
		  <RDF:li>
	         <RDF:Description  id="'.$row->bisio_id.'"  about="'.$rdf_url.'/'.$row->bisio_id.'" >
	            <IO:bisio_id><![CDATA['.$row->bisio_id.']]></IO:bisio_id>
	            <IO:mobilitaetsprogramm_code><![CDATA['.$row->mobilitaetsprogramm_code.']]></IO:mobilitaetsprogramm_code>
	            <IO:mobilitaetsprogramm_kurzbz><![CDATA['.$row->mobilitaetsprogramm_kurzbz.']]></IO:mobilitaetsprogramm_kurzbz>
	            <IO:nation_code><![CDATA['.$row->nation_code.']]></IO:nation_code>
	            <IO:herkunftsland_code><![CDATA['.$row->herkunftsland_code.']]></IO:herkunftsland_code>
	            <IO:von_iso><![CDATA['.$row->von.']]></IO:von_iso>
	            <IO:von><![CDATA['.$datum->convertISODate($row->von).']]></IO:von>
	            <IO:bis_iso><![CDATA['.$row->bis.']]></IO:bis_iso>
	            <IO:bis><![CDATA['.$datum->convertISODate($row->bis).']]></IO:bis>
	            <IO:student_uid><![CDATA['.$row->student_uid.']]></IO:student_uid>
	            <IO:lehreinheit_id><![CDATA['.$row->lehreinheit_id.']]></IO:lehreinheit_id>
	            <IO:ort><![CDATA['.$row->ort.']]></IO:ort>
	            <IO:universitaet><![CDATA['.$row->universitaet.']]></IO:universitaet>
	            <IO:lehrveranstaltung_id><![CDATA['.$lehrveranstaltung_id.']]></IO:lehrveranstaltung_id>
	            <IO:studiensemester_kurzbz><![CDATA['.$studiensemester_kurzbz.']]></IO:studiensemester_kurzbz>
				<IO:ects_angerechnet><![CDATA['.$row->ects_angerechnet.']]></IO:ects_angerechnet>
				<IO:ects_erworben><![CDATA['.$row->ects_erworben.']]></IO:ects_erworben>
	         </RDF:Description>
	      </RDF:li>';
}
?>
   </RDF:Seq>
</RDF:RDF>
