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
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/abschlusspruefung.class.php');
require_once('../include/person.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/datum.class.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!');
	
$rdf_url='http://www.technikum-wien.at/abschlusspruefung';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:ABSCHLUSSPRUEFUNG="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

$datum_obj = new datum();
$pruefung = new abschlusspruefung($conn, null, true);

if(isset($_GET['student_uid']))
{
	$pruefung->getAbschlusspruefungen($_GET['student_uid']);
	
	foreach ($pruefung->result as $row)
		draw_content($row);
}
elseif(isset($_GET['abschlusspruefung_id']) && is_numeric($_GET['abschlusspruefung_id']))
{
	if($pruefung->load($_GET['abschlusspruefung_id']))
		draw_content($pruefung);
	else 
		die('Eintrag wurde nicht gefunden');
}
else 
	die('Student_uid oder Abschlusspruefung_id muss uebergeben werden');
	
	
function draw_content($row)
{
	global $conn, $rdf_url, $datum_obj;
	$vorsitz = '';
	$pruefer1= '';
	$pruefer2= '';
	$pruefer3= '';
	
	//Nachnamen der Pruefer holden
	$person = new person($conn, null, true);
	$mitarbeiter = new mitarbeiter($conn, null, true);
	
	if($mitarbeiter->load($row->vorsitz))
		$vorsitz = $mitarbeiter->nachname;
	if($person->load($row->pruefer1))
		$pruefer1 = $person->nachname;
	if($person->load($row->pruefer2))
		$pruefer2 = $person->nachname;
	if($person->load($row->pruefer3))
		$pruefer3 = $person->nachname;
	
	echo '
      <RDF:li>
         <RDF:Description id="'.$row->abschlusspruefung_id.'"  about="'.$rdf_url.'/'.$row->abschlusspruefung_id.'" >
            <ABSCHLUSSPRUEFUNG:abschlusspruefung_id><![CDATA['.$row->abschlusspruefung_id.']]></ABSCHLUSSPRUEFUNG:abschlusspruefung_id>
            <ABSCHLUSSPRUEFUNG:student_uid><![CDATA['.$row->student_uid.']]></ABSCHLUSSPRUEFUNG:student_uid>
            <ABSCHLUSSPRUEFUNG:vorsitz><![CDATA['.$row->vorsitz.']]></ABSCHLUSSPRUEFUNG:vorsitz>
            <ABSCHLUSSPRUEFUNG:vorsitz_nachname><![CDATA['.$vorsitz.']]></ABSCHLUSSPRUEFUNG:vorsitz_nachname>
            <ABSCHLUSSPRUEFUNG:pruefer1><![CDATA['.$row->pruefer1.']]></ABSCHLUSSPRUEFUNG:pruefer1>
            <ABSCHLUSSPRUEFUNG:pruefer1_nachname><![CDATA['.$pruefer1.']]></ABSCHLUSSPRUEFUNG:pruefer1_nachname>
            <ABSCHLUSSPRUEFUNG:pruefer2><![CDATA['.$row->pruefer2.']]></ABSCHLUSSPRUEFUNG:pruefer2>
            <ABSCHLUSSPRUEFUNG:pruefer2_nachname><![CDATA['.$pruefer2.']]></ABSCHLUSSPRUEFUNG:pruefer2_nachname>
            <ABSCHLUSSPRUEFUNG:pruefer3><![CDATA['.$row->pruefer3.']]></ABSCHLUSSPRUEFUNG:pruefer3>
            <ABSCHLUSSPRUEFUNG:pruefer3_nachname><![CDATA['.$pruefer3.']]></ABSCHLUSSPRUEFUNG:pruefer3_nachname>
            <ABSCHLUSSPRUEFUNG:abschlussbeurteilung_kurzbz><![CDATA['.$row->abschlussbeurteilung_kurzbz.']]></ABSCHLUSSPRUEFUNG:abschlussbeurteilung_kurzbz>
            <ABSCHLUSSPRUEFUNG:akadgrad_id><![CDATA['.$row->akadgrad_id.']]></ABSCHLUSSPRUEFUNG:akadgrad_id>
    		<ABSCHLUSSPRUEFUNG:datum><![CDATA['.$datum_obj->convertISODate($row->datum).']]></ABSCHLUSSPRUEFUNG:datum>
            <ABSCHLUSSPRUEFUNG:datum_iso><![CDATA['.$row->datum.']]></ABSCHLUSSPRUEFUNG:datum_iso>
            <ABSCHLUSSPRUEFUNG:sponsion><![CDATA['.$datum_obj->convertISODate($row->sponsion).']]></ABSCHLUSSPRUEFUNG:sponsion>
            <ABSCHLUSSPRUEFUNG:sponsion_iso><![CDATA['.$row->sponsion.']]></ABSCHLUSSPRUEFUNG:sponsion_iso>
            <ABSCHLUSSPRUEFUNG:pruefungstyp_kurzbz><![CDATA['.$row->pruefungstyp_kurzbz.']]></ABSCHLUSSPRUEFUNG:pruefungstyp_kurzbz>
            <ABSCHLUSSPRUEFUNG:anmerkung><![CDATA['.$row->anmerkung.']]></ABSCHLUSSPRUEFUNG:anmerkung>
         </RDF:Description>
      </RDF:li>
      ';
}
?>
   </RDF:Seq>
</RDF:RDF>