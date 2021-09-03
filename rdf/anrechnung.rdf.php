<?php
/* Copyright (C) 2015 Technikum-Wien
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
 * Authors: Nikolaus Krondraf <nikolaus.krondraf@technikum-wien.at>
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/anrechnung.class.php');

isset($_GET['prestudent_id']) ? $prestudent_id = $_GET['prestudent_id'] : $prestudent_id = null;
isset($_GET['anrechnung_id']) ? $anrechnung_id = $_GET['anrechnung_id'] : $anrechnung_id = null;

// Daten ermitteln
$anrechnung = new anrechnung();
if(is_numeric($anrechnung_id))
{
	$anrechnung->getAnrechnung($anrechnung_id);
	
	// Add last Anrechnungstatus
	$anrechnungstatus = new Anrechnung();
	$anrechnungstatus->getLastAnrechnungstatus($anrechnung_id);
	
	$anrechnung->result[0]->status = $anrechnungstatus->result[0]->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE];
}
elseif(is_numeric($prestudent_id))
{
	$anrechnung->getAnrechnungPrestudent($prestudent_id);
	
	// Add last Anrechnungstatus to each Anrechnung of Prestudent
	foreach ($anrechnung->result as $row)
    {
        $anrechnungstatus = new Anrechnung();
        $status = 	$anrechnungstatus->getLastAnrechnungstatus($row->anrechnung_id);
        $row->status = $anrechnungstatus->result[0]->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE];
    }
}
else
{
	die('Prestudent_id oder anrechnung_id muss angegeben werden');
}

// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/xhtml+xml");

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

$rdf_url='http://www.technikum-wien.at/anrechnung';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:ANRECHNUNG="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

// AUSGABE
if(is_array($anrechnung->result))
{
	foreach($anrechnung->result as $row)
	{
		echo '
		  <RDF:li>
			 <RDF:Description  id="'.$row->anrechnung_id.'"  about="'.$rdf_url.'/'.$row->anrechnung_id.'" >
				<ANRECHNUNG:anrechnung_id><![CDATA['.$row->anrechnung_id.']]></ANRECHNUNG:anrechnung_id>
				<ANRECHNUNG:prestudent_id><![CDATA['.$row->prestudent_id.']]></ANRECHNUNG:prestudent_id>
				<ANRECHNUNG:lehrveranstaltung_id><![CDATA['.$row->lehrveranstaltung_id.']]></ANRECHNUNG:lehrveranstaltung_id>
				<ANRECHNUNG:lehrveranstaltung_bez><![CDATA['.$row->lehrveranstaltung_bez.']]></ANRECHNUNG:lehrveranstaltung_bez>
				<ANRECHNUNG:begruendung_id><![CDATA['.$row->begruendung_id.']]></ANRECHNUNG:begruendung_id>
				<ANRECHNUNG:begruendung><![CDATA['.$row->begruendung.']]></ANRECHNUNG:begruendung>
				<ANRECHNUNG:lehrveranstaltung_id_kompatibel><![CDATA['.$row->lehrveranstaltung_id_kompatibel.']]></ANRECHNUNG:lehrveranstaltung_id_kompatibel>
				<ANRECHNUNG:lehrveranstaltung_bez_kompatibel><![CDATA['.$row->lehrveranstaltung_bez_kompatibel.']]></ANRECHNUNG:lehrveranstaltung_bez_kompatibel>
				<ANRECHNUNG:genehmigt_von><![CDATA['.$row->genehmigt_von.']]></ANRECHNUNG:genehmigt_von>
				<ANRECHNUNG:anzahl_notizen><![CDATA['.$anrechnung->getAnzahlNotizen($row->anrechnung_id).']]></ANRECHNUNG:anzahl_notizen>
				<ANRECHNUNG:insertamum><![CDATA['.$row->insertamum.']]></ANRECHNUNG:insertamum>
				<ANRECHNUNG:insertvon><![CDATA['.$row->insertvon.']]></ANRECHNUNG:insertvon>
				<ANRECHNUNG:updateamum><![CDATA['.$row->updateamum.']]></ANRECHNUNG:updateamum>
				<ANRECHNUNG:updatevon><![CDATA['.$row->updatevon.']]></ANRECHNUNG:updatevon>
				<ANRECHNUNG:status><![CDATA['.$row->status.']]></ANRECHNUNG:status>
			 </RDF:Description>
		  </RDF:li>
		  ';
	}
}
?>

	</RDF:Seq>
</RDF:RDF>