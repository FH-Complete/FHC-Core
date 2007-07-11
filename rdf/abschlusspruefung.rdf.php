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

// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/abschlusspruefung.class.php');
require_once('../include/person.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/nation.class.php');
require_once('../include/datum.class.php');

if(isset($_GET['xmlformat']))
{
	echo '<?xml version="1.0" encoding="ISO-8859-15" standalone="yes"?>';
	$xmlformat=$_GET['xmlformat'];
}
else
{
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
	$xmlformat='rdf';
}

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!');

$datum_obj = new datum();
$pruefung = new abschlusspruefung($conn, null, true);

	function draw_content_xml($row)
	{
		global $conn, $rdf_url, $datum_obj;
		$vorsitz = '';
		$pruefer1= '';
		$pruefer2= '';
		$pruefer3= '';

		//Nachnamen der Pruefer holden
		$person = new person($conn, null, true);
		$mitarbeiter = new mitarbeiter($conn, null, true);
		$student= new benutzer($conn,$row->student_uid,true);

		$nation=new nation($conn,$student->geburtsnation,true);
		$geburtsnation=$nation->kurztext;
		$geburtsnation_engl=$nation->engltext;
		$nation->load($student->staatsbuergerschaft);
		$staatsbuergerschaft=$nation->kurztext;
		$staatsbuergerschaft_engl=$nation->engltext;

		if($mitarbeiter->load($row->vorsitz))
			$vorsitz = $mitarbeiter->nachname;
		if($person->load($row->pruefer1))
			$pruefer1 = $person->nachname;
		if($person->load($row->pruefer2))
			$pruefer2 = $person->nachname;
		if($person->load($row->pruefer3))
			$pruefer3 = $person->nachname;

		echo "\t<pruefung>".'
		<abschlusspruefung_id><![CDATA['.$row->abschlusspruefung_id.']]></abschlusspruefung_id>
		<student_uid><![CDATA['.$row->student_uid.']]></student_uid>
		<vorsitz><![CDATA['.$row->vorsitz.']]></vorsitz>
		<vorsitz_nachname><![CDATA['.$vorsitz.']]></vorsitz_nachname>
		<pruefer1><![CDATA['.$row->pruefer1.']]></pruefer1>
		<pruefer1_nachname><![CDATA['.$pruefer1.']]></pruefer1_nachname>
		<pruefer2><![CDATA['.$row->pruefer2.']]></pruefer2>
		<pruefer2_nachname><![CDATA['.$pruefer2.']]></pruefer2_nachname>
		<pruefer3><![CDATA['.$row->pruefer3.']]></pruefer3>
		<pruefer3_nachname><![CDATA['.$pruefer3.']]></pruefer3_nachname>
		<abschlussbeurteilung_kurzbz><![CDATA['.$row->abschlussbeurteilung_kurzbz.']]></abschlussbeurteilung_kurzbz>
		<akadgrad_id><![CDATA['.$row->akadgrad_id.']]></akadgrad_id>
		<datum><![CDATA['.$datum_obj->convertISODate($row->datum).']]></datum>
		<datum_iso><![CDATA['.$row->datum.']]></datum_iso>
		<sponsion><![CDATA['.$datum_obj->convertISODate($row->sponsion).']]></sponsion>
		<sponsion_iso><![CDATA['.$row->sponsion.']]></sponsion_iso>
		<pruefungstyp_kurzbz><![CDATA['.$row->pruefungstyp_kurzbz.']]></pruefungstyp_kurzbz>
		<anrede><![CDATA['.$student->anrede.']]></anrede>
		<vorname><![CDATA['.$student->vorname.']]></vorname>
		<vornamen><![CDATA['.$student->vornamen.']]></vornamen>
		<nachname><![CDATA['.$student->nachname.']]></nachname>
		<gebdatum_iso><![CDATA['.$student->gebdatum.']]></gebdatum_iso>
		<gebdatum><![CDATA['.$student->gebdatum.']]></gebdatum>
		<gebort><![CDATA['.$student->gebort.']]></gebort>
		<staatsbuergerschaft><![CDATA['.$staatsbuergerschaft.']]></staatsbuergerschaft>
		<staatsbuergerschaft_engl><![CDATA['.$staatsbuergerschaft_engl.']]></staatsbuergerschaft_engl>
		<geburtsnation><![CDATA['.$geburtsnation.']]></geburtsnation>
		<geburtsnation_engl><![CDATA['.$geburtsnation_engl.']]></geburtsnation_engl>
		<studiengang_kz><![CDATA['.$student.']]></studiengang_kz>
		<stg_bezeichnung><![CDATA['.$student.']]></stg_bezeichnung>
		<akadgrad_kurzbz><![CDATA['.$student.']]></akadgrad_kurzbz>
		<titel><![CDATA['.$student.']]></titel>
		<datum_aktuell><![CDATA['.$student.']]></datum_aktuell>
		<anmerkung><![CDATA['.$row->anmerkung.']]></anmerkung>';
	 	echo "\n\t</pruefung>";
	}



// ----------------------------------- RDF --------------------------------------
if ($xmlformat=='rdf')
{
	$rdf_url='http://www.technikum-wien.at/abschlusspruefung';
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
	echo '
	<RDF:RDF
		xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
		xmlns:ABSCHLUSSPRUEFUNG="'.$rdf_url.'/rdf#"
	>

	   <RDF:Seq about="'.$rdf_url.'/liste">
	';

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

	
	echo '	</RDF:Seq>';
	echo '</RDF:RDF>';
}	//endof xmlformat==rdf
// ----------------------------------- XML --------------------------------------
elseif ($xmlformat=='xml')
{
	echo "\n<abschlusspruefung>\n";

	if(isset($_GET['student_uid']))
	{
		$pruefung->getAbschlusspruefungen($_GET['student_uid']);

		foreach ($pruefung->result as $row)
			draw_content_xml($row);
	}
	elseif(isset($_GET['abschlusspruefung_id']) && is_numeric($_GET['abschlusspruefung_id']))
	{
		if($pruefung->load($_GET['abschlusspruefung_id']))
			draw_content_xml($pruefung);
		else
			die('Eintrag wurde nicht gefunden');
	}
	else
		die('Student_uid oder Abschlusspruefung_id muss uebergeben werden');

	echo "\n</abschlusspruefung>";
}	//endof xmlformat==xml