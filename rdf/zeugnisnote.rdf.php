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

// header f�r no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/zeugnisnote.class.php');
require_once('../include/datum.class.php');
require_once('../include/person.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/studiengang.class.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$user = get_uid();
loadVariables($conn, $user);
$datum = new datum();

$stg_arr = array();
$stg_obj = new studiengang($conn);
$stg_obj->getAll(null, false);

foreach ($stg_obj->result as $stg) 
	$stg_arr[$stg->studiengang_kz]=$stg->kuerzel;

if(isset($_GET['uid']))
	$uid = $_GET['uid'];
else 
	$uid = null;
	
if(isset($_GET['lehrveranstaltung_id']))
	$lehrveranstaltung_id = $_GET['lehrveranstaltung_id'];
else 
	$lehrveranstaltung_id = null;

if(isset($_GET['studiensemester_kurzbz']))
	$studiensemester_kurzbz = $_GET['studiensemester_kurzbz'];
else 
	$studiensemester_kurzbz = $semester_aktuell;

$rdf_url='http://www.technikum-wien.at/zeugnisnote';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NOTE="'.$rdf_url.'/rdf#"
>
   <RDF:Seq about="'.$rdf_url.'/liste">
';
   
//Daten holen
$obj = new zeugnisnote($conn, null, null, null, true);

$obj->getZeugnisnoten($lehrveranstaltung_id, $uid, $studiensemester_kurzbz);
$benutzer = new benutzer($conn, null, null);

foreach ($obj->result as $row)	
{
	$benutzer->load($row->student_uid);
	echo '
		  <RDF:li>
	         <RDF:Description  id="'.$row->lehrveranstaltung_id.'/'.$row->student_uid.'/'.$row->studiensemester_kurzbz.'"  about="'.$rdf_url.'/'.$row->lehrveranstaltung_id.'/'.$row->student_uid.'/'.$row->studiensemester_kurzbz.'" >
				<NOTE:lehrveranstaltung_id><![CDATA['.$row->lehrveranstaltung_id.']]></NOTE:lehrveranstaltung_id>
				<NOTE:student_uid><![CDATA['.$row->student_uid.']]></NOTE:student_uid>
				<NOTE:studiensemester_kurzbz><![CDATA['.$row->studiensemester_kurzbz.']]></NOTE:studiensemester_kurzbz>
				<NOTE:note><![CDATA['.$row->note.']]></NOTE:note>
				<NOTE:uebernahmedatum_iso><![CDATA['.$row->uebernahmedatum.']]></NOTE:uebernahmedatum_iso>
				<NOTE:uebernahmedatum><![CDATA['.$datum->convertISODate($row->uebernahmedatum).']]></NOTE:uebernahmedatum>
				<NOTE:benotungsdatum_iso><![CDATA['.$row->benotungsdatum.']]></NOTE:benotungsdatum_iso>
				<NOTE:benotungsdatum><![CDATA['.$datum->convertISODate($row->benotungsdatum).']]></NOTE:benotungsdatum>
				<NOTE:note_bezeichnung><![CDATA['.$row->note_bezeichnung.']]></NOTE:note_bezeichnung>
				<NOTE:lehrveranstaltung_bezeichnung><![CDATA['.$row->lehrveranstaltung_bezeichnung.']]></NOTE:lehrveranstaltung_bezeichnung>
				<NOTE:student_nachname><![CDATA['.$benutzer->nachname.']]></NOTE:student_nachname>
				<NOTE:student_vorname><![CDATA['.$benutzer->vorname.']]></NOTE:student_vorname>
				<NOTE:studiengang><![CDATA['.$stg_arr[$row->studiengang_kz].']]></NOTE:studiengang>
	         </RDF:Description>
	      </RDF:li>';
}
?>
   </RDF:Seq>
</RDF:RDF>