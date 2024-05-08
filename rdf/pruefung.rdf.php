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

require_once('../config/vilesci.config.inc.php');
require_once('../include/pruefung.class.php');
require_once('../include/datum.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/variable.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('assistenz') && !$rechte->isBerechtigt('admin'))
	die('Sie haben keine Berechtigung für diese Seite');

if(isset($_GET['student_uid']))
	$student_uid = $_GET['student_uid'];
else
	$student_uid = '';

if(isset($_GET['pruefung_id']))
	$pruefung_id = $_GET['pruefung_id'];
else
	$pruefung_id = '';

$datum_obj = new datum();

$pruefung = new pruefung();

$rdf_url='http://www.technikum-wien.at/pruefung';

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:PRUEFUNG="'.$rdf_url.'/rdf#"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

if($pruefung_id!='')
{
	$pruefung->load($pruefung_id);
	draw_rdf($pruefung);
}
else
{
	if(isset($_REQUEST['all_stsem']))
		$stsem = null;
	else
	{
		$variable = new variable();
		$variable->loadVariables($uid);
		$stsem = $variable->variable->semester_aktuell;
	}
	$pruefung->getPruefungen($student_uid, null, null, $stsem);
	foreach ($pruefung->result as $row)
		draw_rdf($row);
}

function draw_rdf($row)
{
	global $rdf_url, $datum_obj;

	echo '
      <RDF:li>
         <RDF:Description  id="'.$row->pruefung_id.'"  about="'.$rdf_url.'/'.$row->pruefung_id.'" >
            <PRUEFUNG:pruefung_id><![CDATA['.$row->pruefung_id.']]></PRUEFUNG:pruefung_id>
            <PRUEFUNG:lehreinheit_id><![CDATA['.$row->lehreinheit_id.']]></PRUEFUNG:lehreinheit_id>
            <PRUEFUNG:student_uid><![CDATA['.$row->student_uid.']]></PRUEFUNG:student_uid>
            <PRUEFUNG:mitarbeiter_uid><![CDATA['.$row->mitarbeiter_uid.']]></PRUEFUNG:mitarbeiter_uid>
            <PRUEFUNG:note NC:parseType="Integer"><![CDATA['.$row->note.']]></PRUEFUNG:note>
            <PRUEFUNG:pruefungstyp_kurzbz><![CDATA['.$row->pruefungstyp_kurzbz.']]></PRUEFUNG:pruefungstyp_kurzbz>
            <PRUEFUNG:datum><![CDATA['.$datum_obj->convertISODate($row->datum).']]></PRUEFUNG:datum>
            <PRUEFUNG:datum_iso><![CDATA['.$row->datum.']]></PRUEFUNG:datum_iso>
            <PRUEFUNG:anmerkung><![CDATA['.$row->anmerkung.']]></PRUEFUNG:anmerkung>
            <PRUEFUNG:note_bezeichnung><![CDATA['.$row->note_bezeichnung.']]></PRUEFUNG:note_bezeichnung>
            <PRUEFUNG:lehrveranstaltung_bezeichnung><![CDATA['.$row->lehrveranstaltung_bezeichnung.']]></PRUEFUNG:lehrveranstaltung_bezeichnung>
            <PRUEFUNG:pruefungstyp_beschreibung><![CDATA['.$row->pruefungstyp_beschreibung.']]></PRUEFUNG:pruefungstyp_beschreibung>
            <PRUEFUNG:lehrveranstaltung_id><![CDATA['.$row->lehrveranstaltung_id.']]></PRUEFUNG:lehrveranstaltung_id>
            <PRUEFUNG:studiensemester_kurzbz><![CDATA['.$row->studiensemester_kurzbz.']]></PRUEFUNG:studiensemester_kurzbz>
            <PRUEFUNG:punkte><![CDATA['.($row->punkte!=''?(float)$row->punkte:'').']]></PRUEFUNG:punkte>
         </RDF:Description>
      </RDF:li>
      ';
}
?>
   </RDF:Seq>
</RDF:RDF>
