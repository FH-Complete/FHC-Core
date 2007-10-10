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
require_once('../include/student.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/nation.class.php');
require_once('../include/datum.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/akadgrad.class.php');

$xmlformat='rdf';
if(isset($_GET['xmlformat']))
	$xmlformat=$_GET['xmlformat'];
if($xmlformat=='xml')
	echo '<?xml version="1.0" encoding="ISO-8859-15" standalone="yes"?>';
else
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!');

$datum_obj = new datum();

$abschlussbeurteilung_arr = array();
$qry = "SELECT * FROM lehre.tbl_abschlussbeurteilung";
if($result = pg_query($conn, $qry))
	while($row = pg_fetch_object($result))
		$abschlussbeurteilung_arr[$row->abschlussbeurteilung_kurzbz]=$row->bezeichnung;

	function draw_content_xml($row)
	{
		global $conn, $rdf_url, $datum_obj, $abschlussbeurteilung_arr;
		$vorsitz = '';
		$pruefer1= '';
		$pruefer2= '';
		$pruefer3= '';

		//Nachnamen der Pruefer holden
		$person = new person($conn,null,false);
		$mitarbeiter = new mitarbeiter($conn,null,false);
		$student= new student($conn,$row->student_uid,false);

		$nation=new nation($conn,$student->geburtsnation,false);
		$geburtsnation=$nation->kurztext;
		$geburtsnation_engl=$nation->engltext;
		$nation->load($student->staatsbuergerschaft);
		$staatsbuergerschaft=$nation->kurztext;
		$staatsbuergerschaft_engl=$nation->engltext;

		$studiengang = new studiengang($conn, $student->studiengang_kz, false);
		$akadgrad = new akadgrad($conn, $row->akadgrad_id, false);
		
		if($mitarbeiter->load($row->vorsitz))
			$vorsitz = $mitarbeiter->titelpre.' '.$mitarbeiter->vorname.' '.$mitarbeiter->nachname.' '.$mitarbeiter->titelpost;
		if($person->load($row->pruefer1))
			$pruefer1 = $person->titelpre.' '.$person->vorname.' '.$person->nachname.' '.$person->titelpost;
		if($person->load($row->pruefer2))
			$pruefer2 = $person->titelpre.' '.$person->vorname.' '.$person->nachname.' '.$person->titelpost;
		if($person->load($row->pruefer3))
			$pruefer3 = $person->titelpre.' '.$person->vorname.' '.$person->nachname.' '.$person->titelpost;
			
		$qry = "SELECT * FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) WHERE funktion_kurzbz='rek'";
		$rektor = '';
		if($result_rek = pg_query($conn, $qry))
			if($row_rek = pg_fetch_object($result_rek))
				$rektor = $row_rek->titelpre.' '.$row_rek->vorname.' '.$row_rek->nachname.' '.$row_rek->titelpost;
		$qry = "SELECT titel as themenbereich, ende, projektarbeit_id, note FROM lehre.tbl_projektarbeit a WHERE student_uid='$student->uid' AND (projekttyp_kurzbz='Bachelor' OR projekttyp_kurzbz='Diplom') ORDER BY beginn LIMIT 2";
		$themenbereich='';
		$datum_projekt='';
		$betreuer = '';
		$betreuer_2 = '';
		$themenbereich_2 = '';
		$note = '';
		
		if($result_proj = pg_query($conn, $qry))
		{
			if($row_proj = pg_fetch_object($result_proj))
			{
				$qry_bet = "SELECT titelpre, vorname, nachname, titelpost FROM lehre.tbl_projektbetreuer JOIN public.tbl_person USING(person_id) WHERE projektarbeit_id='$row_proj->projektarbeit_id' AND (betreuerart_kurzbz in('Erstbegutachter', 'Erstbetreuer', 'Betreuer', 'Begutacher')) LIMIT 1";
				if($result_bet = pg_query($conn, $qry_bet))
					if($row_bet = pg_fetch_object($result_bet))
						$betreuer = $row_bet->titelpre.' '.$row_bet->vorname.' '.$row_bet->nachname.' '.$row_bet->titelpost;

				$themenbereich = $row_proj->themenbereich;
				$note = $row_proj->note;
				$datum_projekt = $datum_obj->convertISODate($row_proj->ende);
			}
			
			if($row_proj = pg_fetch_object($result_proj))
			{
				$qry_bet = "SELECT titelpre, vorname, nachname, titelpost FROM lehre.tbl_projektbetreuer JOIN public.tbl_person USING(person_id) WHERE projektarbeit_id='$row_proj->projektarbeit_id' AND (betreuerart_kurzbz in('Erstbegutachter', 'Erstbetreuer', 'Betreuer', 'Begutacher')) LIMIT 1";
					if($result_bet = pg_query($conn, $qry_bet))
						if($row_bet = pg_fetch_object($result_bet))
							$betreuer_2 = $row_bet->titelpre.' '.$row_bet->vorname.' '.$row_bet->nachname.' '.$row_bet->titelpost;

				$themenbereich_2 = $row_proj->themenbereich;
			}
		}
		
		switch($student->anrede)
		{
			case 'Herr': $anrede_engl = 'Mr.'; break;
			case 'Frau': $anrede_engl = 'Mrs.'; break;
			default: $anrede_engl = ''; break;
		}
		
		if($student->anrede == 'Herr')
			$anrede = 'Herrn';
		else 
			$anrede = $student->anrede;
		
		
					
		if($row->sponsion=='')
			$row->sponsion=$row->datum;
			
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
		<abschlussbeurteilung_kurzbz><![CDATA['.$abschlussbeurteilung_arr[$row->abschlussbeurteilung_kurzbz].']]></abschlussbeurteilung_kurzbz>
		<akadgrad_id><![CDATA['.$row->akadgrad_id.']]></akadgrad_id>
		<datum><![CDATA['.$datum_obj->convertISODate($row->datum).']]></datum>
		<datum_iso><![CDATA['.$row->datum.']]></datum_iso>
		<sponsion><![CDATA['.$datum_obj->convertISODate($row->sponsion).']]></sponsion>
		<sponsion_iso><![CDATA['.$row->sponsion.']]></sponsion_iso>
		<pruefungstyp_kurzbz><![CDATA['.$row->pruefungstyp_kurzbz.']]></pruefungstyp_kurzbz>
		<anrede><![CDATA['.$anrede.']]></anrede>
		<anrede_engl><![CDATA['.$anrede_engl.']]></anrede_engl>
		<vorname><![CDATA['.$student->vorname.']]></vorname>
		<vornamen><![CDATA['.$student->vornamen.']]></vornamen>
		<nachname><![CDATA['.$student->nachname.']]></nachname>
		<matrikelnr><![CDATA['.$student->matrikelnr.']]></matrikelnr>
		<gebdatum_iso><![CDATA['.$student->gebdatum.']]></gebdatum_iso>
		<gebdatum><![CDATA['.$datum_obj->convertISODate($student->gebdatum).']]></gebdatum>
		<gebort><![CDATA['.$student->gebort.']]></gebort>
		<staatsbuergerschaft><![CDATA['.$staatsbuergerschaft.']]></staatsbuergerschaft>
		<staatsbuergerschaft_engl><![CDATA['.$staatsbuergerschaft_engl.']]></staatsbuergerschaft_engl>
		<geburtsnation><![CDATA['.$geburtsnation.']]></geburtsnation>
		<geburtsnation_engl><![CDATA['.$geburtsnation_engl.']]></geburtsnation_engl>
		<studiengang_kz><![CDATA['.sprintf('%04s',$student->studiengang_kz).']]></studiengang_kz>
		<stg_bezeichnung><![CDATA['.$studiengang->bezeichnung.']]></stg_bezeichnung>
		<akadgrad_kurzbz><![CDATA['.$akadgrad->akadgrad_kurzbz.']]></akadgrad_kurzbz>
		<titel><![CDATA['.$akadgrad->titel.']]></titel>
		<datum_aktuell><![CDATA['.date('d.m.Y').']]></datum_aktuell>
		<anmerkung><![CDATA['.$row->anmerkung.']]></anmerkung>
		<bescheidbgbl1><![CDATA['.$studiengang->bescheidbgbl1.']]></bescheidbgbl1>
		<bescheidbgbl2><![CDATA['.$studiengang->bescheidbgbl2.']]></bescheidbgbl2>
		<bescheidgz><![CDATA['.$studiengang->bescheidgz.']]></bescheidgz>
		<bescheidvom><![CDATA['.$datum_obj->convertISODate($studiengang->bescheidvom).']]></bescheidvom>
		<titelbescheidvom><![CDATA['.$datum_obj->convertISODate($studiengang->titelbescheidvom).']]></titelbescheidvom>
		<rektor><![CDATA['.$rektor.']]></rektor>
		<themenbereich><![CDATA['.$themenbereich.']]></themenbereich>
		<themenbereich_2><![CDATA['.$themenbereich_2.']]></themenbereich_2>
		<betreuer><![CDATA['.$betreuer.']]></betreuer>
		<betreuer_2><![CDATA['.$betreuer_2.']]></betreuer_2>
		<note><![CDATA['.$note.']]></note>
		<datum_projekt><![CDATA['.$datum_projekt.']]></datum_projekt>';
		
	 	echo "\n\t</pruefung>";
	}



// ----------------------------------- RDF --------------------------------------
if ($xmlformat=='rdf')
{
	$pruefung = new abschlusspruefung($conn, null, true);
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
	$pruefung = new abschlusspruefung($conn, null, false);
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