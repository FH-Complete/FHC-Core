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
require_once('../include/projektarbeit.class.php');
require_once('../include/datum.class.php');
require_once('../include/lehreinheit.class.php');
require_once('../include/functions.inc.php');

$rdf_url='http://www.technikum-wien.at/projektarbeit';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:PROJEKTARBEIT="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

$datum_obj = new datum();
$projektarbeit = new projektarbeit();

if(isset($_GET['student_uid']))
{
	$projektarbeit->getProjektarbeit($_GET['student_uid']);

	foreach ($projektarbeit->result as $row)
		draw_content($row);
}
elseif(isset($_GET['projektarbeit_id']) && is_numeric($_GET['projektarbeit_id']))
{
	if($projektarbeit->load($_GET['projektarbeit_id']))
		draw_content($projektarbeit);
	else
		die('Eintrag wurde nicht gefunden');
}
else
	die('Student_uid oder Projektarbeit_id muss uebergeben werden');


function draw_content($row)
{
	global $rdf_url, $datum_obj;
	$lehreinheit = new lehreinheit($row->lehreinheit_id);
	echo '
      <RDF:li>
         <RDF:Description id="'.$row->projektarbeit_id.'"  about="'.$rdf_url.'/'.$row->projektarbeit_id.'" >
            <PROJEKTARBEIT:projektarbeit_id><![CDATA['.$row->projektarbeit_id.']]></PROJEKTARBEIT:projektarbeit_id>
            <PROJEKTARBEIT:projekttyp_kurzbz><![CDATA['.$row->projekttyp_kurzbz.']]></PROJEKTARBEIT:projekttyp_kurzbz>
            <PROJEKTARBEIT:bezeichnung><![CDATA['.$row->bezeichnung.']]></PROJEKTARBEIT:bezeichnung>
            <PROJEKTARBEIT:titel><![CDATA['.xmlclean($row->titel).']]></PROJEKTARBEIT:titel>
            <PROJEKTARBEIT:titel_english><![CDATA['.xmlclean($row->titel_english).']]></PROJEKTARBEIT:titel_english>
            <PROJEKTARBEIT:lehreinheit_id><![CDATA['.$row->lehreinheit_id.']]></PROJEKTARBEIT:lehreinheit_id>
            <PROJEKTARBEIT:lehreinheit_stsem><![CDATA['.$lehreinheit->studiensemester_kurzbz.']]></PROJEKTARBEIT:lehreinheit_stsem>
            <PROJEKTARBEIT:lehrveranstaltung_id><![CDATA['.$lehreinheit->lehrveranstaltung_id.']]></PROJEKTARBEIT:lehrveranstaltung_id>
            <PROJEKTARBEIT:student_uid><![CDATA['.$row->student_uid.']]></PROJEKTARBEIT:student_uid>
            <PROJEKTARBEIT:firma_id><![CDATA['.$row->firma_id.']]></PROJEKTARBEIT:firma_id>
            <PROJEKTARBEIT:note><![CDATA['.$row->note.']]></PROJEKTARBEIT:note>
            <PROJEKTARBEIT:punkte><![CDATA['.$row->punkte.']]></PROJEKTARBEIT:punkte>
            <PROJEKTARBEIT:beginn><![CDATA['.$datum_obj->convertISODate($row->beginn).']]></PROJEKTARBEIT:beginn>
            <PROJEKTARBEIT:beginn_iso><![CDATA['.$row->beginn.']]></PROJEKTARBEIT:beginn_iso>
            <PROJEKTARBEIT:ende><![CDATA['.$datum_obj->convertISODate($row->ende).']]></PROJEKTARBEIT:ende>
            <PROJEKTARBEIT:ende_iso><![CDATA['.$row->ende.']]></PROJEKTARBEIT:ende_iso>
            <PROJEKTARBEIT:faktor><![CDATA['.$row->faktor.']]></PROJEKTARBEIT:faktor>
            <PROJEKTARBEIT:freigegeben><![CDATA['.($row->freigegeben?'Ja':'Nein').']]></PROJEKTARBEIT:freigegeben>
            <PROJEKTARBEIT:gesperrtbis><![CDATA['.$datum_obj->convertISODate($row->gesperrtbis).']]></PROJEKTARBEIT:gesperrtbis>
            <PROJEKTARBEIT:gesperrtbis_iso><![CDATA['.$row->gesperrtbis.']]></PROJEKTARBEIT:gesperrtbis_iso>
            <PROJEKTARBEIT:stundensatz><![CDATA['.$row->stundensatz.']]></PROJEKTARBEIT:stundensatz>
            <PROJEKTARBEIT:gesamtstunden><![CDATA['.$row->gesamtstunden.']]></PROJEKTARBEIT:gesamtstunden>
            <PROJEKTARBEIT:themenbereich><![CDATA['.$row->themenbereich.']]></PROJEKTARBEIT:themenbereich>
            <PROJEKTARBEIT:anmerkung><![CDATA['.$row->anmerkung.']]></PROJEKTARBEIT:anmerkung>
			<PROJEKTARBEIT:final><![CDATA['.($row->final?'Ja':'Nein').']]></PROJEKTARBEIT:final>
			<PROJEKTARBEIT:abgabedatum><![CDATA['.$datum_obj->convertISODate($row->abgabedatum).']]></PROJEKTARBEIT:abgabedatum>
            <PROJEKTARBEIT:abgabedatum_iso><![CDATA['.$row->abgabedatum.']]></PROJEKTARBEIT:abgabedatum_iso>
         </RDF:Description>
      </RDF:li>
      ';
}
?>
   </RDF:Seq>
</RDF:RDF>
