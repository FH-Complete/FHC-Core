<?php
/* Copyright (C) 2006 fhcomplete.org
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
require_once('../include/functions.inc.php');
require_once('../include/zeugnisnote.class.php');
require_once('../include/datum.class.php');
require_once('../include/person.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/student.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/lehrveranstaltung.class.php');
require_once('../include/benutzerberechtigung.class.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

$user = get_uid();
loadVariables($user);
$datum = new datum();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('student/noten'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$stg_arr = array();
$stg_obj = new studiengang();
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
$obj = new zeugnisnote();

$obj->getZeugnisnoten($lehrveranstaltung_id, $uid, $studiensemester_kurzbz);
$benutzer = new student();

foreach ($obj->result as $row)
{
	$benutzer->load($row->student_uid);
	$lv_obj = new lehrveranstaltung();
	$lv_obj->load($row->lehrveranstaltung_id);

	if ($lv_obj->zeugnis==false)
		$zeugnis=APP_ROOT.'skin/images/invisible.png';
	else
		$zeugnis='';

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
				<NOTE:lehrveranstaltung_lehrform><![CDATA['.$lv_obj->lehrform_kurzbz.']]></NOTE:lehrveranstaltung_lehrform>
				<NOTE:lehrveranstaltung_kurzbz><![CDATA['.$lv_obj->kurzbz.']]></NOTE:lehrveranstaltung_kurzbz>
				<NOTE:student_nachname><![CDATA['.$benutzer->nachname.']]></NOTE:student_nachname>
				<NOTE:student_vorname><![CDATA['.$benutzer->vorname.']]></NOTE:student_vorname>
				<NOTE:studiengang><![CDATA['.(isset($stg_arr[$benutzer->studiengang_kz])?$stg_arr[$benutzer->studiengang_kz]:'').']]></NOTE:studiengang>
				<NOTE:studiengang_kz><![CDATA['.$benutzer->studiengang_kz.']]></NOTE:studiengang_kz>
				<NOTE:verband><![CDATA['.$benutzer->verband.']]></NOTE:verband>
				<NOTE:studiengang_lv><![CDATA['.$stg_arr[$lv_obj->studiengang_kz].']]></NOTE:studiengang_lv>
				<NOTE:studiengang_kz_lv><![CDATA['.$lv_obj->studiengang_kz.']]></NOTE:studiengang_kz_lv>
				<NOTE:semester_lv><![CDATA['.$lv_obj->semester.']]></NOTE:semester_lv>
				<NOTE:ects_lv><![CDATA['.$lv_obj->ects.']]></NOTE:ects_lv>
				<NOTE:student_semester><![CDATA['.$benutzer->semester.']]></NOTE:student_semester>
				<NOTE:punkte><![CDATA['.($row->punkte!=''?(float)$row->punkte:'').']]></NOTE:punkte>
				<NOTE:zeugnis><![CDATA['.$zeugnis.']]></NOTE:zeugnis>
				<NOTE:lehrveranstaltung_bezeichnung_english><![CDATA['.$lv_obj->bezeichnung_english.']]></NOTE:lehrveranstaltung_bezeichnung_english>
	         </RDF:Description>
	      </RDF:li>';
}
?>
   </RDF:Seq>
</RDF:RDF>
