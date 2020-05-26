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
header("Content-type: application/xhtml+xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
require_once('../config/vilesci.config.inc.php');
require_once('../include/akte.class.php');
require_once('../include/dokument.class.php');
require_once('../include/datum.class.php');

if(isset($_GET['person_id']))
	$person_id = $_GET['person_id'];
else
	$person_id = '';

if(isset($_GET['dokument_kurzbz']))
	$dokument_kurzbz = $_GET['dokument_kurzbz'];
else
	$dokument_kurzbz = '';

if(isset($_GET['akte_id']))
{
	$akte_id=$_GET['akte_id'];
}

$datum = new datum();

$akten = new akte();
if(!isset($akte_id))
{
	if(!$akten->getArchiv($person_id))
		die($akten->errormsg);
}
else
{
	if(!$akten->load($akte_id))
		die($akten->errormsg);
	$akten->result[]=$akten;
}
$rdf_url='http://www.technikum-wien.at/akte';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:AKTE="'.$rdf_url.'/rdf#"
>

	<RDF:Seq about="'.$rdf_url.'/liste">
';

foreach ($akten->result as $row)
{
	$dokument = new dokument();
	$dokument->loadDokumenttyp($row->dokument_kurzbz);
	echo '
	<RDF:li>
		<RDF:Description  id="'.$row->akte_id.'"  about="'.$rdf_url.'/'.$row->akte_id.'" >
			<AKTE:akte_id><![CDATA['.$row->akte_id.']]></AKTE:akte_id>
			<AKTE:person_id><![CDATA['.$row->person_id.']]></AKTE:person_id>
			<AKTE:dokument_kurzbz><![CDATA['.$row->dokument_kurzbz.']]></AKTE:dokument_kurzbz>
			<AKTE:dokument_bezeichnung><![CDATA['.$dokument->bezeichnung.']]></AKTE:dokument_bezeichnung>
			<AKTE:mimetype><![CDATA['.$row->mimetype.']]></AKTE:mimetype>
			<AKTE:erstelltam><![CDATA['.$datum->convertISODate($row->erstelltam).']]></AKTE:erstelltam>
			<AKTE:erstelltam_iso><![CDATA['.$row->erstelltam.']]></AKTE:erstelltam_iso>
			<AKTE:gedruckt><![CDATA['.($row->gedruckt?'Ja':'Nein').']]></AKTE:gedruckt>
			<AKTE:titel><![CDATA['.$row->titel.']]></AKTE:titel>
			<AKTE:bezeichnung><![CDATA['.$row->bezeichnung.']]></AKTE:bezeichnung>
			<AKTE:updateamum><![CDATA['.$row->updateamum.']]></AKTE:updateamum>
			<AKTE:updatevon><![CDATA['.$row->updatevon.']]></AKTE:updatevon>
			<AKTE:insertamum><![CDATA['.$row->insertamum.']]></AKTE:insertamum>
			<AKTE:insertvon><![CDATA['.$row->insertvon.']]></AKTE:insertvon>
			<AKTE:uid><![CDATA['.$row->uid.']]></AKTE:uid>
			<AKTE:anmerkung_intern><![CDATA['.$row->anmerkung_intern.']]></AKTE:anmerkung_intern>
			<AKTE:titel_intern><![CDATA['.$row->titel_intern.']]></AKTE:titel_intern>
			<AKTE:anmerkung><![CDATA['.$row->anmerkung.']]></AKTE:anmerkung>
			<AKTE:nachgereicht><![CDATA['.($row->nachgereicht?'Ja':'Nein').']]></AKTE:nachgereicht>
			<AKTE:nachgereicht_am><![CDATA['.$row->nachgereicht_am.']]></AKTE:nachgereicht_am>
			<AKTE:signiert><![CDATA['.($row->signiert?'Ja':'Nein').']]></AKTE:signiert>
			<AKTE:stud_selfservice><![CDATA['.($row->stud_selfservice?'Ja':'Nein').']]></AKTE:stud_selfservice>
			<AKTE:stud_akzeptiertamum><![CDATA['.($row->akzeptiertamum != '' ? date('d.m.Y H:i:s',strtotime($row->akzeptiertamum)) : '').']]></AKTE:stud_akzeptiertamum>
		</RDF:Description>
	</RDF:li>
	';
}
?>
	</RDF:Seq>
</RDF:RDF>
