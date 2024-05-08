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
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/lvgesamtnote.class.php');
require_once('../include/datum.class.php');
require_once('../include/studiengang.class.php');
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

$rdf_url='http://www.technikum-wien.at/lvgesamtnote';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NOTE="'.$rdf_url.'/rdf#"
>
   <RDF:Seq about="'.$rdf_url.'/liste">
';

//Daten holen
$obj = new lvgesamtnote();

$obj->getLvGesamtNoten($lehrveranstaltung_id, $uid, $semester_aktuell);
$db = new basis_db();

foreach ($obj->result as $row)
{
	if($row->freigabedatum!='')
	{
		$vorname = '';
		$nachname = '';
		$qry_name = "SELECT vorname, nachname FROM public.tbl_person JOIN public.tbl_benutzer USING(person_id) WHERE uid=".$db->db_add_param($row->student_uid);
		if($db->db_query($qry_name))
		{
			if($row_name = $db->db_fetch_object())
			{
				$vorname = $row_name->vorname;
				$nachname = $row_name->nachname;
			}
		}

		echo '
			  <RDF:li>
		         <RDF:Description  id="'.$row->lehrveranstaltung_id.'/'.$row->student_uid.'/'.$row->studiensemester_kurzbz.'"  about="'.$rdf_url.'/'.$row->lehrveranstaltung_id.'/'.$row->student_uid.'/'.$row->studiensemester_kurzbz.'" >
					<NOTE:lehrveranstaltung_id><![CDATA['.$row->lehrveranstaltung_id.']]></NOTE:lehrveranstaltung_id>
					<NOTE:student_uid><![CDATA['.$row->student_uid.']]></NOTE:student_uid>
					<NOTE:mitarbeiter_uid><![CDATA['.$row->mitarbeiter_uid.']]></NOTE:mitarbeiter_uid>
					<NOTE:studiensemester_kurzbz><![CDATA['.$row->studiensemester_kurzbz.']]></NOTE:studiensemester_kurzbz>
					<NOTE:note><![CDATA['.$row->note.']]></NOTE:note>
					<NOTE:punkte><![CDATA['.$row->punkte.']]></NOTE:punkte>
					<NOTE:freigabedatum_iso><![CDATA['.$row->freigabedatum.']]></NOTE:freigabedatum_iso>
					<NOTE:freigabedatum><![CDATA['.$datum->convertISODate($row->freigabedatum).']]></NOTE:freigabedatum>
					<NOTE:benotungsdatum_iso><![CDATA['.$row->benotungsdatum.']]></NOTE:benotungsdatum_iso>
					<NOTE:benotungsdatum><![CDATA['.$datum->convertISODate($row->benotungsdatum).']]></NOTE:benotungsdatum>
					<NOTE:note_bezeichnung><![CDATA['.$row->note_bezeichnung.']]></NOTE:note_bezeichnung>
					<NOTE:lehrveranstaltung_bezeichnung><![CDATA['.$row->lehrveranstaltung_bezeichnung.']]></NOTE:lehrveranstaltung_bezeichnung>
					<NOTE:student_vorname><![CDATA['.$vorname.']]></NOTE:student_vorname>
					<NOTE:student_nachname><![CDATA['.$nachname.']]></NOTE:student_nachname>
					<NOTE:studiengang><![CDATA['.$stg_arr[$row->studiengang_kz].']]></NOTE:studiengang>
		         </RDF:Description>
		      </RDF:li>';
	}
}
?>
   </RDF:Seq>
</RDF:RDF>
