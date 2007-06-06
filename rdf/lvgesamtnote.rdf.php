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
require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/lvgesamtnote.class.php');
require_once('../include/datum.class.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$user = get_uid();
loadVariables($conn, $user);
$datum = new datum();

if(isset($_GET['uid']))
	$uid = $_GET['uid'];
else 
	$uid = null;
	
if(isset($_GET['lehrveranstaltung_id']))
	$lehrveranstaltung_id = $_GET['lehrveranstaltung_id'];
else 
	$lehrveranstaltung_id = null;
	
$rdf_url='http://www.technikum-wien.at/lvgesamtnote';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NOTE="'.$rdf_url.'/rdf#"
>
   <RDF:Seq about="'.$rdf_url.'/liste">
';
   
//Daten holen
$obj = new lvgesamtnote($conn);

$obj->getLvGesamtNoten($lehrveranstaltung_id, $uid, $semester_aktuell);

foreach ($obj->result as $row)	
{
	echo '
		  <RDF:li>
	         <RDF:Description  id="'.$row->lehrveranstaltung_id.'/'.$row->student_uid.'/'.$row->studiensemester_kurzbz.'"  about="'.$rdf_url.'/'.$row->lehrveranstaltung_id.'/'.$row->student_uid.'/'.$row->studiensemester_kurzbz.'" >
				<NOTE:lehrveranstaltung_id><![CDATA['.$row->lehrveranstaltung_id.']]></NOTE:lehrveranstaltung_id>
				<NOTE:student_uid><![CDATA['.$row->student_uid.']]></NOTE:student_uid>
				<NOTE:mitarbeiter_uid><![CDATA['.$row->mitarbeiter_uid.']]></NOTE:mitarbeiter_uid>
				<NOTE:studiensemester_kurzbz><![CDATA['.$row->studiensemester_kurzbz.']]></NOTE:studiensemester_kurzbz>
				<NOTE:note><![CDATA['.$row->note.']]></NOTE:note>
				<NOTE:freigabedatum_iso><![CDATA['.$row->freigabedatum.']]></NOTE:freigabedatum_iso>
				<NOTE:freigabedatum><![CDATA['.$datum->convertISODate($row->freigabedatum).']]></NOTE:freigabedatum>
				<NOTE:benotungsdatum_iso><![CDATA['.$row->benotungsdatum.']]></NOTE:benotungsdatum_iso>
				<NOTE:benotungsdatum><![CDATA['.$datum->convertISODate($row->benotungsdatum).']]></NOTE:benotungsdatum>
				<NOTE:note_bezeichnung><![CDATA['.$row->note_bezeichnung.']]></NOTE:note_bezeichnung>
				<NOTE:lehrveranstaltung_bezeichnung><![CDATA['.$row->lehrveranstaltung_bezeichnung.']]></NOTE:lehrveranstaltung_bezeichnung>
	         </RDF:Description>
	      </RDF:li>';
}
?>
   </RDF:Seq>
</RDF:RDF>