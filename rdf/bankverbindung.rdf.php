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
require_once('../include/bankverbindung.class.php');
require_once('../include/datum.class.php');
require_once('../include/benutzerberechtigung.class.php');

$uid = get_uid();
$recht = new benutzerberechtigung();
$recht->getBerechtigungen($uid);
if(!$recht->isberechtigt('mitarbeiter/bankdaten') && !$recht->isBerechtigt('student/bankdaten'))
    die('Sie haben keine Berechtigung');

if(isset($_GET['person_id']))
	$person_id = $_GET['person_id'];
else
	$person_id = '';

if(isset($_GET['bankverbindung_id']))
	$bankverbindung_id = $_GET['bankverbindung_id'];
else
	$bankverbindung_id = '';

$datum = new datum();

$bankverbindung = new bankverbindung();

$rdf_url='http://www.technikum-wien.at/bankverbindung';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:BANKVERBINDUNG="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

if($bankverbindung_id!='')
{
	$bankverbindung->load($bankverbindung_id);
	draw_rdf($bankverbindung);
}
else
{
	$bankverbindung->load_pers($person_id);
	foreach ($bankverbindung->result as $row)
		draw_rdf($row);
}

function draw_rdf($row)
{
	global $rdf_url;

	switch($row->typ)
	{
		case 'p': $typ_bezeichnung = 'Privatkonto'; break;
		case 'f': $typ_bezeichnung = 'Firmenkonto'; break;
		default: $typ_bezeichnung = ''; break;
	}

	echo '
      <RDF:li>
         <RDF:Description  id="'.$row->bankverbindung_id.'"  about="'.$rdf_url.'/'.$row->bankverbindung_id.'" >
            <BANKVERBINDUNG:bankverbindung_id><![CDATA['.$row->bankverbindung_id.']]></BANKVERBINDUNG:bankverbindung_id>
            <BANKVERBINDUNG:person_id><![CDATA['.$row->person_id.']]></BANKVERBINDUNG:person_id>
            <BANKVERBINDUNG:name><![CDATA['.$row->name.']]></BANKVERBINDUNG:name>
            <BANKVERBINDUNG:anschrift><![CDATA['.$row->anschrift.']]></BANKVERBINDUNG:anschrift>
            <BANKVERBINDUNG:bic><![CDATA['.$row->bic.']]></BANKVERBINDUNG:bic>
            <BANKVERBINDUNG:blz><![CDATA['.$row->blz.']]></BANKVERBINDUNG:blz>
            <BANKVERBINDUNG:iban><![CDATA['.$row->iban.']]></BANKVERBINDUNG:iban>
            <BANKVERBINDUNG:kontonr><![CDATA['.$row->kontonr.']]></BANKVERBINDUNG:kontonr>
            <BANKVERBINDUNG:typ_bezeichnung><![CDATA['.$typ_bezeichnung.']]></BANKVERBINDUNG:typ_bezeichnung>
            <BANKVERBINDUNG:typ><![CDATA['.$row->typ.']]></BANKVERBINDUNG:typ>
            <BANKVERBINDUNG:verrechnung><![CDATA['.($row->verrechnung?'Ja':'Nein').']]></BANKVERBINDUNG:verrechnung>
         </RDF:Description>
      </RDF:li>
      ';
}
?>
   </RDF:Seq>
</RDF:RDF>