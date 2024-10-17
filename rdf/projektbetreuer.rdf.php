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
require_once('../include/projektbetreuer.class.php');
require_once('../include/datum.class.php');
require_once('../include/person.class.php');

$rdf_url='http://www.technikum-wien.at/projektbetreuer';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:PROJEKTBETREUER="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

$datum_obj = new datum();
$projektbetreuer = new projektbetreuer();

if(isset($_GET['projektarbeit_id']) && !isset($_GET['person_id']))
{
	$projektbetreuer->getProjektbetreuer($_GET['projektarbeit_id']);

	foreach ($projektbetreuer->result as $row)
		draw_content($row);
}
elseif(isset($_GET['person_id']) && is_numeric($_GET['person_id']) && isset($_GET['projektarbeit_id']) && is_numeric($_GET['projektarbeit_id']))
{
	if($projektbetreuer->load($_GET['person_id'], $_GET['projektarbeit_id'], $_GET['betreuerart_kurzbz']))
		draw_content($projektbetreuer);
	else
		die('Eintrag wurde nicht gefunden');
}
else
	die('Projektarbeit_id muss uebergeben werden');

function draw_content($row)
{
	global $rdf_url, $datum_obj;
	$person=new person($row->person_id);
	echo '
      <RDF:li>
         <RDF:Description id="'.$row->person_id.'/'.$row->projektarbeit_id.'/'.$row->betreuerart_kurzbz.'"  about="'.$rdf_url.'/'.$row->person_id.'/'.$row->projektarbeit_id.'/'.$row->betreuerart_kurzbz.'" >
            <PROJEKTBETREUER:projektarbeit_id><![CDATA['.$row->projektarbeit_id.']]></PROJEKTBETREUER:projektarbeit_id>
            <PROJEKTBETREUER:person_id><![CDATA['.$row->person_id.']]></PROJEKTBETREUER:person_id>
            <PROJEKTBETREUER:person_nachname><![CDATA['.$person->nachname.']]></PROJEKTBETREUER:person_nachname>
            <PROJEKTBETREUER:person_vorname><![CDATA['.$person->vorname.']]></PROJEKTBETREUER:person_vorname>
            <PROJEKTBETREUER:note><![CDATA['.$row->note.']]></PROJEKTBETREUER:note>
            <PROJEKTBETREUER:faktor><![CDATA['.$row->faktor.']]></PROJEKTBETREUER:faktor>
            <PROJEKTBETREUER:name><![CDATA['.$row->name.']]></PROJEKTBETREUER:name>
            <PROJEKTBETREUER:punkte><![CDATA['.$row->punkte.']]></PROJEKTBETREUER:punkte>
            <PROJEKTBETREUER:stunden><![CDATA['.(float)$row->stunden.']]></PROJEKTBETREUER:stunden>
            <PROJEKTBETREUER:stundensatz><![CDATA['.$row->stundensatz.']]></PROJEKTBETREUER:stundensatz>
            <PROJEKTBETREUER:betreuerart_kurzbz><![CDATA['.$row->betreuerart_kurzbz.']]></PROJEKTBETREUER:betreuerart_kurzbz>
            <PROJEKTBETREUER:vertrag_id><![CDATA['.$row->vertrag_id.']]></PROJEKTBETREUER:vertrag_id>
         </RDF:Description>
      </RDF:li>
      ';
}
?>
   </RDF:Seq>
</RDF:RDF>
