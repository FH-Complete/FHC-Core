<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
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
require_once('../include/notiz.class.php');
require_once('../include/datum.class.php');

$rdf_url='http://www.technikum-wien.at/notiz';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NOTIZ="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

$notiz = new notiz();
if(isset($_GET['erledigt']))
	$erledigt=($_GET['erledigt']=='true'?true:false);
else
	$erledigt=null;
$projekt_kurzbz=(isset($_GET['projekt_kurzbz'])?$_GET['projekt_kurzbz']:null);
$projektphase_id=(isset($_GET['projektphase_id'])?$_GET['projektphase_id']:null);
$projekttask_id=(isset($_GET['projekttask_id'])?$_GET['projekttask_id']:null);
$uid=(isset($_GET['uid'])?$_GET['uid']:null);
$person_id=(isset($_GET['person_id'])?$_GET['person_id']:null);
$prestudent_id=(isset($_GET['prestudent_id'])?$_GET['prestudent_id']:null);
$bestellung_id=(isset($_GET['bestellung_id'])?$_GET['bestellung_id']:null);
$lehreinheit_id=(isset($_GET['lehreinheit_id'])?$_GET['lehreinheit_id']:null);
$stundenplandev_id=(isset($_GET['stundenplandev_id'])?$_GET['stundenplandev_id']:null);
$anrechnung_id=(isset($_GET['anrechnung_id'])?$_GET['anrechnung_id']:null);
$datum_obj = new datum();
$user=(isset($_GET['user'])?$_GET['user']:null);

$notiz_id = (isset($_GET['notiz_id'])?$_GET['notiz_id']:null);

if(is_null($notiz_id))
{
	if(!$notiz->getNotiz($erledigt, $projekt_kurzbz, $projektphase_id, $projekttask_id, $uid, $person_id, $prestudent_id, $bestellung_id, $user, $lehreinheit_id, $stundenplandev_id, $anrechnung_id))
		die($notiz->errormsg);
}
else
{
	if($notiz->load($notiz_id))
	{
		$notiz->result[] = $notiz;
	}
	else
		die($notiz->errormsg);
}

foreach($notiz->result as $row)
{
	echo '
      <RDF:li>
         <RDF:Description  id="'.$row->notiz_id.'"  about="'.$rdf_url.'/'.$row->notiz_id.'" >
			<NOTIZ:notiz_id><![CDATA['.$row->notiz_id.']]></NOTIZ:notiz_id>
			<NOTIZ:titel><![CDATA['.$row->titel.']]></NOTIZ:titel>
			<NOTIZ:text><![CDATA['.$row->text.']]></NOTIZ:text>
			<NOTIZ:text_stripped><![CDATA['.strip_tags($row->text).']]></NOTIZ:text_stripped>
			<NOTIZ:verfasser_uid><![CDATA['.$row->verfasser_uid.']]></NOTIZ:verfasser_uid>
			<NOTIZ:bearbeiter_uid><![CDATA['.$row->bearbeiter_uid.']]></NOTIZ:bearbeiter_uid>
			<NOTIZ:start><![CDATA['.$datum_obj->formatDatum($row->start,'d.m.Y').']]></NOTIZ:start>
			<NOTIZ:ende><![CDATA['.$datum_obj->formatDatum($row->ende,'d.m.Y').']]></NOTIZ:ende>
			<NOTIZ:startISO><![CDATA['.$row->start.']]></NOTIZ:startISO>
			<NOTIZ:endeISO><![CDATA['.$row->ende.']]></NOTIZ:endeISO>
			<NOTIZ:erledigt><![CDATA['.($row->erledigt?'true':'false').']]></NOTIZ:erledigt>
            <NOTIZ:dokumente><![CDATA['.count($row->dokumente).']]></NOTIZ:dokumente>
			<NOTIZ:insertamum><![CDATA['.$row->insertamum.']]></NOTIZ:insertamum>
			<NOTIZ:insertvon><![CDATA['.$row->insertvon.']]></NOTIZ:insertvon>
			<NOTIZ:updateamum><![CDATA['.$datum_obj->formatDatum($row->updateamum,'d.m.Y H:i:s').']]></NOTIZ:updateamum>
			<NOTIZ:updatevon><![CDATA['.$row->updatevon.']]></NOTIZ:updatevon>
         </RDF:Description>
      </RDF:li>
      ';
}

echo '  </RDF:Seq>
</RDF:RDF>';
?>
