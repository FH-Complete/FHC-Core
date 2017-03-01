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
//header("Content-type: application/vnd.mozilla.xul+xml");
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/person.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/datum.class.php');

$rdf_url='http://www.technikum-wien.at/interessent';
$user = get_uid();
loadVariables($user);
$datum = new datum();

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:PRESTD="'.$rdf_url.'/rdf#"
>

  <RDF:Seq about="'.$rdf_url.'/alle">';

if(isset($_GET['studiensemester_kurzbz']))
	$studiensemester_kurzbz = $_GET['studiensemester_kurzbz'];
else
	$studiensemester_kurzbz = null;

if($studiensemester_kurzbz=='aktuelles')
	$studiensemester_kurzbz = $semester_aktuell;

if(isset($_GET['studiengang_kz']) && is_numeric($_GET['studiengang_kz']))
	$studiengang_kz = $_GET['studiengang_kz'];
else
	$studiengang_kz = null;

if(isset($_GET['semester']) && is_numeric($_GET['semester']))
	$semester = $_GET['semester'];
else
	$semester = null;

if(isset($_GET['prestudent_id']) && is_numeric($_GET['prestudent_id']))
	$prestudent_id=$_GET['prestudent_id'];
else
	$prestudent_id=null;

if(isset($_GET['typ']))
	$typ=$_GET['typ'];
else
	$typ=null;

$prestd = new prestudent();

if($studiengang_kz!=null)
{
	if($prestd->loadIntessentenUndBewerber($studiensemester_kurzbz, $studiengang_kz, $semester, $typ))
	{
		foreach ($prestd->result as $row)
		{
			DrawInteressent($row);
		}
	}
}
elseif($prestudent_id!=null)
{
	if($prestd->load($prestudent_id))
		DrawInteressent($prestd);
	else
		echo $prestd->errormsg;
}
else
{
	echo 'Falsche Parameteruebergabe';
}

function DrawInteressent($row)
{
		global $rdf_url, $datum;
		$ps = new prestudent();
		$ps->getLastStatus($row->prestudent_id);
		//<PRESTD:foto><![CDATA['.$row->foto.']]></PRESTD:foto>
		echo '
		  <RDF:li>
	      	<RDF:Description  id="'.$row->prestudent_id.'"  about="'.$rdf_url.'/'.$row->prestudent_id.'" >
	        	<PRESTD:person_id><![CDATA['.$row->person_id.']]></PRESTD:person_id>
	        	<PRESTD:anrede><![CDATA['.$row->anrede.']]></PRESTD:anrede>
	        	<PRESTD:sprache><![CDATA['.$row->sprache.']]></PRESTD:sprache>
	        	<PRESTD:staatsbuergerschaft><![CDATA['.$row->staatsbuergerschaft.']]></PRESTD:staatsbuergerschaft>
	        	<PRESTD:familienstand><![CDATA['.$row->familienstand.']]></PRESTD:familienstand>
	    		<PRESTD:titelpre><![CDATA['.$row->titelpre.']]></PRESTD:titelpre>
	    		<PRESTD:titelpost><![CDATA['.$row->titelpost.']]></PRESTD:titelpost>
	    		<PRESTD:vornamen><![CDATA['.$row->vornamen.']]></PRESTD:vornamen>
	    		<PRESTD:vorname><![CDATA['.$row->vorname.']]></PRESTD:vorname>
	    		<PRESTD:nachname><![CDATA['.$row->nachname.']]></PRESTD:nachname>
	    		<PRESTD:geburtsdatum><![CDATA['.$datum->convertISODate($row->gebdatum).']]></PRESTD:geburtsdatum>
	    		<PRESTD:geburtsdatum_iso><![CDATA['.$row->gebdatum.']]></PRESTD:geburtsdatum_iso>
	    		<PRESTD:geburtsnation><![CDATA['.$row->geburtsnation.']]></PRESTD:geburtsnation>
	    		<PRESTD:homepage><![CDATA['.$row->homepage.']]></PRESTD:homepage>
	    		<PRESTD:aktiv><![CDATA['.($row->aktiv?'true':'false').']]></PRESTD:aktiv>
	    		<PRESTD:gebort><![CDATA['.$row->gebort.']]></PRESTD:gebort>
	    		<PRESTD:gebzeit><![CDATA['.$row->gebzeit.']]></PRESTD:gebzeit>

	    		<PRESTD:anmerkungen><![CDATA['.$row->anmerkungen.']]></PRESTD:anmerkungen>
	    		<PRESTD:svnr><![CDATA['.$row->svnr.']]></PRESTD:svnr>
	    		<PRESTD:ersatzkennzeichen><![CDATA['.$row->ersatzkennzeichen.']]></PRESTD:ersatzkennzeichen>
	    		<PRESTD:geschlecht><![CDATA['.$row->geschlecht.']]></PRESTD:geschlecht>
	    		<PRESTD:anzahlkinder><![CDATA['.$row->anzahlkinder.']]></PRESTD:anzahlkinder>
	    		<PRESTD:updateamum><![CDATA['.$row->updateamum.']]></PRESTD:updateamum>
	    		<PRESTD:updatevon><![CDATA['.$row->updatevon.']]></PRESTD:updatevon>

				<PRESTD:prestudent_id><![CDATA['.$row->prestudent_id.']]></PRESTD:prestudent_id>
				<PRESTD:aufmerksamdurch_kurzbz><![CDATA['.$row->aufmerksamdurch_kurzbz.']]></PRESTD:aufmerksamdurch_kurzbz>
				<PRESTD:studiengang_kz><![CDATA['.$row->studiengang_kz.']]></PRESTD:studiengang_kz>
				<PRESTD:berufstaetigkeit_code><![CDATA['.$row->berufstaetigkeit_code.']]></PRESTD:berufstaetigkeit_code>
				<PRESTD:ausbildungcode><![CDATA['.$row->ausbildungcode.']]></PRESTD:ausbildungcode>
				<PRESTD:zgv_code><![CDATA['.$row->zgv_code.']]></PRESTD:zgv_code>
				<PRESTD:zgvort><![CDATA['.$row->zgvort.']]></PRESTD:zgvort>
				<PRESTD:zgvdatum><![CDATA['.$datum->convertISODate($row->zgvdatum).']]></PRESTD:zgvdatum>
				<PRESTD:zgvdatum_iso><![CDATA['.$row->zgvdatum.']]></PRESTD:zgvdatum_iso>
				<PRESTD:zgvmas_code><![CDATA['.$row->zgvmas_code.']]></PRESTD:zgvmas_code>
				<PRESTD:zgvmaort><![CDATA['.$row->zgvmaort.']]></PRESTD:zgvmaort>
				<PRESTD:zgvmadatum><![CDATA['.$datum->convertISODate($row->zgvmadatum).']]></PRESTD:zgvmadatum>
				<PRESTD:zgvmadatum_iso><![CDATA['.$row->zgvmadatum.']]></PRESTD:zgvmadatum_iso>
				<PRESTD:aufnahmeschluessel><![CDATA['.$row->aufnahmeschluessel.']]></PRESTD:aufnahmeschluessel>
				<PRESTD:facheinschlberuf><![CDATA['.($row->facheinschlberuf?'true':'false').']]></PRESTD:facheinschlberuf>
				<PRESTD:reihungstest_id><![CDATA['.$row->reihungstest_id.']]></PRESTD:reihungstest_id>
				<PRESTD:anmeldungreihungstest><![CDATA['.$datum->convertISODate($row->anmeldungreihungstest).']]></PRESTD:anmeldungreihungstest>
				<PRESTD:anmeldungreihungstest_iso><![CDATA['.$row->anmeldungreihungstest.']]></PRESTD:anmeldungreihungstest_iso>
				<PRESTD:reihungstestangetreten><![CDATA['.($row->reihungstestangetreten?'true':'false').']]></PRESTD:reihungstestangetreten>
				<PRESTD:punkte><![CDATA['.$row->punkte.']]></PRESTD:punkte>
				<PRESTD:bismelden><![CDATA['.($row->bismelden?'true':'false').']]></PRESTD:bismelden>
				<PRESTD:anmerkung><![CDATA['.$row->anmerkung.']]></PRESTD:anmerkung>
				<PRESTD:status><![CDATA['.$ps->status_kurzbz.']]></PRESTD:status>
	      	</RDF:Description>
	      </RDF:li>
	      ';
}
?>
  </RDF:Seq>
</RDF:RDF>