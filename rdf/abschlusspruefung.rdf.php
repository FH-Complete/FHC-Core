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
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/xhtml+xml");

require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/abschlusspruefung.class.php');
require_once('../include/abschlusspruefung_antritt.class.php');
require_once('../include/person.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/student.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/nation.class.php');
require_once('../include/datum.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/studienordnung.class.php');
require_once('../include/akadgrad.class.php');
require_once('../include/organisationseinheit.class.php');
require_once('../include/projektarbeit.class.php');
require_once('../include/lehreinheit.class.php');
require_once('../include/lehrveranstaltung.class.php');
require_once('../include/note.class.php');

$xmlformat='rdf';
if(isset($_GET['xmlformat']))
	$xmlformat=$_GET['xmlformat'];

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

$datum_obj = new datum();
$db = new basis_db();

$abschlussbeurteilung_arr = array();
$abschlussbeurteilung_arrEnglish = array();
$qry = "SELECT * FROM lehre.tbl_abschlussbeurteilung";
if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{
		$abschlussbeurteilung_arr[$row->abschlussbeurteilung_kurzbz]=$row->bezeichnung;
		$abschlussbeurteilung_arrEng[$row->abschlussbeurteilung_kurzbz]=$row->bezeichnung_english;
	}
}

$note_arr = array();
$qry = "SELECT * FROM lehre.tbl_note";
if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{
		$note_arr[$row->note]=$row->anmerkung;
	}
}

function draw_content_xml($row)
{
	global $rdf_url, $datum_obj, $abschlussbeurteilung_arr, $abschlussbeurteilung_arrEng, $note_arr;
	$vorsitz = '';
	$pruefer1= '';
	$pruefer2= '';
	$pruefer3= '';

	//Nachnamen der Pruefer holden
	$person = new person();
	$mitarbeiter = new mitarbeiter();
	$student= new student($row->student_uid);
	$prestudent = new prestudent($student->prestudent_id);

	$nation=new nation($student->geburtsnation);
	$geburtsnation=$nation->kurztext;
	$geburtsnation_engl=$nation->engltext;
	$nation->load($student->staatsbuergerschaft);
	$staatsbuergerschaft=$nation->kurztext;
	$staatsbuergerschaft_engl=$nation->engltext;

	$studiengang = new studiengang($student->studiengang_kz);
	$akadgrad = new akadgrad($row->akadgrad_id);
	$vorsitz_geschlecht = '';

	if ($prestudent->getLastStatus($student->prestudent_id))
	{
		$studienplan_id = $prestudent->studienplan_id;
		$studienordnung = new studienordnung();
		if ($studienordnung->getStudienordnungFromStudienplan($studienplan_id))
		{
			$studiengangbezeichnung = $studienordnung->__get('studiengangbezeichnung');
			$studiengangbezeichnung_englisch = $studienordnung->__get('studiengangbezeichnung_englisch');
		}
	}
	$studiengang_bezeichnung = empty($studiengangbezeichnung) ? $studiengang->bezeichnung : $studiengangbezeichnung;
	$studiengang_bezeichnung_englisch = empty($studiengangbezeichnung_englisch) ? $studiengang->english : $studiengangbezeichnung_englisch;

	if($mitarbeiter->load($row->vorsitz))
	{
		$vorsitz = $mitarbeiter->titelpre.' '.$mitarbeiter->vorname;
		$vorsitz .= ' '.$mitarbeiter->nachname.' '.$mitarbeiter->titelpost;
		$vorsitz = trim($vorsitz);

		$vorsitz_geschlecht = $mitarbeiter->geschlecht;
	}
	if($person->load($row->pruefer1))
		$pruefer1 = trim($person->titelpre.' '.$person->vorname.' '.$person->nachname.' '.$person->titelpost);
	if($person->load($row->pruefer2))
		$pruefer2 = trim($person->titelpre.' '.$person->vorname.' '.$person->nachname.' '.$person->titelpost);
	if($person->load($row->pruefer3))
		$pruefer3 = trim($person->titelpre.' '.$person->vorname.' '.$person->nachname.' '.$person->titelpost);

	$qry = "SELECT *
			FROM PUBLIC.tbl_benutzerfunktion
			JOIN campus.vw_mitarbeiter USING (uid)
			WHERE funktion_kurzbz = 'rek'
				AND (
					tbl_benutzerfunktion.datum_von <= now()
					OR tbl_benutzerfunktion.datum_von IS NULL
					)
				AND (
					tbl_benutzerfunktion.datum_bis >= now()
					OR tbl_benutzerfunktion.datum_bis IS NULL
					)
			ORDER BY tbl_benutzerfunktion.insertamum DESC LIMIT 1";
	$rektor = '';
	$db = new basis_db();
	$db2 = new basis_db();
	if($db->db_query($qry))
		if($row_rek = $db->db_fetch_object())
			$rektor = $row_rek->titelpre.' '.$row_rek->vorname.' '.$row_rek->nachname.' '.$row_rek->titelpost;
	$qry = "SELECT
				*
			FROM
				(
					SELECT
						titel as themenbereich, ende, projektarbeit_id, note, beginn
					FROM
						lehre.tbl_projektarbeit a
					WHERE
						student_uid=".$db->db_add_param($student->uid)."
						AND projekttyp_kurzbz in('Bachelor','Diplom','Master','Dissertation','Lizenziat','Magister')
						AND a.final=true
					ORDER BY beginn DESC, projektarbeit_id ASC LIMIT 2
				) as a
			ORDER BY beginn asc";
	$themenbereich='';
	$datum_projekt='';
	$betreuer = '';
	$betreuer_2 = '';
	$themenbereich_2 = '';
	$note = '';
	$note2='';
	$datum_projekt2='';

	if($result_proj = $db->db_query($qry))
	{
		if($row_proj = $db->db_fetch_object($result_proj))
		{
			$qry_bet = "SELECT titelpre, vorname, nachname, titelpost
				FROM
					lehre.tbl_projektbetreuer
					JOIN public.tbl_person USING(person_id)
				WHERE
					projektarbeit_id=".$db->db_add_param($row_proj->projektarbeit_id)."
					AND (betreuerart_kurzbz in('Erstbegutachter', 'Erstbetreuer', 'Betreuer', 'Begutacher'))
				LIMIT 1";
			if($db2->db_query($qry_bet))
				if($row_bet = $db2->db_fetch_object())
					$betreuer = $row_bet->titelpre.' '.$row_bet->vorname.' '.$row_bet->nachname.' '.$row_bet->titelpost;

			$themenbereich = $row_proj->themenbereich;
			$note = (isset($note_arr[$row_proj->note])?$note_arr[$row_proj->note]:$row_proj->note);
			$datum_projekt = $datum_obj->convertISODate($row_proj->ende);
			$projektarbeit = new projektarbeit($row_proj->projektarbeit_id);
			$lehreinheit = new lehreinheit($projektarbeit->lehreinheit_id);
			$lehrveranstaltung = new lehrveranstaltung($lehreinheit->lehrveranstaltung_id);
			$projektnote = new note($note);
		}

		if($row_proj = $db->db_fetch_object($result_proj))
		{
			$qry_bet = "SELECT titelpre, vorname, nachname, titelpost
						FROM
							lehre.tbl_projektbetreuer
							JOIN public.tbl_person USING(person_id)
						WHERE
							projektarbeit_id=".$db->db_add_param($row_proj->projektarbeit_id, FHC_INTEGER)."
							AND (betreuerart_kurzbz in('Erstbegutachter', 'Erstbetreuer', 'Betreuer', 'Begutacher'))
						LIMIT 1";
			if($db2->db_query($qry_bet))
				if($row_bet = $db2->db_fetch_object())
					$betreuer_2 = $row_bet->titelpre.' '.$row_bet->vorname.' '.$row_bet->nachname.' '.$row_bet->titelpost;

			$themenbereich_2 = $row_proj->themenbereich;
			$note2 = (isset($note_arr[$row_proj->note])?$note_arr[$row_proj->note]:$row_proj->note);
			$datum_projekt2 = $datum_obj->convertISODate($row_proj->ende);
		}
	}

	switch($student->anrede)
	{
		case 'Herr': $anrede_engl = 'Mr'; break;
		case 'Frau': $anrede_engl = 'Ms'; break;
		default: $anrede_engl = ''; break;
	}

	if($student->anrede == 'Herr')
		$anrede = 'Herrn';
	else
		$anrede = $student->anrede;

	if($row->sponsion=='')
		$row->sponsion=$row->datum;

	$oe = new organisationseinheit();
	$parents = $oe->getParents($studiengang->oe_kurzbz);
	$oe_parent = "";

	foreach ($parents as $parent)
	{
		$oe_temp = new organisationseinheit();
		$oe_temp->load($parent);
		if($oe_temp->organisationseinheittyp_kurzbz == 'Fakultät')
		{
			$oe_parent = $oe_temp->bezeichnung;
			break;
		}
	}

	$studiengang_bezeichnung2 = explode(" ", $studiengang_bezeichnung, 2);
	$name = $student->titelpre.' '.trim($student->vorname.' '.$student->vornamen).' '.$student->nachname;
	$name .= ($student->titelpost!=''?', '.$student->titelpost:'');
	$name = trim($name);

	//Wenn Lehrgang, dann Erhalter-KZ vor die Studiengangs-Kz hängen
	if ($student->studiengang_kz<0)
	{
		$stg = new studiengang();
		$stg->load($student->studiengang_kz);

		$studiengang_kz = sprintf("%03s", $stg->erhalter_kz).sprintf("%04s", abs($student->studiengang_kz));
	}
	else
		$studiengang_kz = sprintf("%04s", abs($student->studiengang_kz));

	echo "\t<pruefung>".'
	<abschlusspruefung_id><![CDATA['.$row->abschlusspruefung_id.']]></abschlusspruefung_id>
	<student_uid><![CDATA['.$row->student_uid.']]></student_uid>
	<vorsitz><![CDATA['.$row->vorsitz.']]></vorsitz>
	<vorsitz_nachname><![CDATA['.$vorsitz.']]></vorsitz_nachname>
	<vorsitz_geschlecht><![CDATA['.$vorsitz_geschlecht.']]></vorsitz_geschlecht>
	<pruefer1><![CDATA['.$row->pruefer1.']]></pruefer1>
	<pruefer1_nachname><![CDATA['.$pruefer1.']]></pruefer1_nachname>
	<pruefer2><![CDATA['.$row->pruefer2.']]></pruefer2>
	<pruefer2_nachname><![CDATA['.$pruefer2.']]></pruefer2_nachname>
	<pruefer3><![CDATA['.$row->pruefer3.']]></pruefer3>
	<pruefer3_nachname><![CDATA['.$pruefer3.']]></pruefer3_nachname>
	<abschlussbeurteilung_kurzbz><![CDATA['.($row->abschlussbeurteilung_kurzbz!=''?$abschlussbeurteilung_arr[$row->abschlussbeurteilung_kurzbz]:'').']]></abschlussbeurteilung_kurzbz>
	<abschlussbeurteilung_kurzbzEng><![CDATA['.($row->abschlussbeurteilung_kurzbz!=''?$abschlussbeurteilung_arrEng[$row->abschlussbeurteilung_kurzbz]:'').']]></abschlussbeurteilung_kurzbzEng>
	<akadgrad_id><![CDATA['.$row->akadgrad_id.']]></akadgrad_id>
	<datum><![CDATA['.$datum_obj->convertISODate($row->datum).']]></datum>
	<datum_iso><![CDATA['.$row->datum.']]></datum_iso>
	<uhrzeit><![CDATA['.$row->uhrzeit.']]></uhrzeit>
	<sponsion><![CDATA['.$datum_obj->convertISODate($row->sponsion).']]></sponsion>
	<sponsion_iso><![CDATA['.$row->sponsion.']]></sponsion_iso>
	<pruefungstyp_kurzbz><![CDATA['.$row->pruefungstyp_kurzbz.']]></pruefungstyp_kurzbz>
	<pruefungstyp_beschreibung><![CDATA['.$row->beschreibung.']]></pruefungstyp_beschreibung>
	<anrede><![CDATA['.$anrede.']]></anrede>
	<anrede_engl><![CDATA['.$anrede_engl.']]></anrede_engl>
	<name><![CDATA['.$name.']]></name>
	<titelpre><![CDATA['.$student->titelpre.']]></titelpre>
	<vorname><![CDATA['.$student->vorname.']]></vorname>
	<vornamen><![CDATA['.$student->vornamen.']]></vornamen>
	<nachname><![CDATA['.$student->nachname.']]></nachname>
	<titelpost><![CDATA['.$student->titelpost.']]></titelpost>
	<matrikelnr><![CDATA['.trim($student->matrikelnr).']]></matrikelnr>
	<gebdatum_iso><![CDATA['.$student->gebdatum.']]></gebdatum_iso>
	<geschlecht><![CDATA['.$student->geschlecht.']]></geschlecht>
	<gebdatum><![CDATA['.$datum_obj->convertISODate($student->gebdatum).']]></gebdatum>
	<gebort><![CDATA['.$student->gebort.']]></gebort>
	<staatsbuergerschaft><![CDATA['.$staatsbuergerschaft.']]></staatsbuergerschaft>
	<staatsbuergerschaft_engl><![CDATA['.$staatsbuergerschaft_engl.']]></staatsbuergerschaft_engl>
	<geburtsnation><![CDATA['.$geburtsnation.']]></geburtsnation>
	<geburtsnation_engl><![CDATA['.$geburtsnation_engl.']]></geburtsnation_engl>
	<studiengang_kz><![CDATA['.$studiengang_kz.']]></studiengang_kz>
	<stg_bezeichnung><![CDATA['.$studiengang_bezeichnung.']]></stg_bezeichnung>
	<stg_bezeichnung2><![CDATA['.(isset($studiengang_bezeichnung2[1])?$studiengang_bezeichnung2[1]:'').']]></stg_bezeichnung2>
	<stg_bezeichnung_engl><![CDATA['.$studiengang_bezeichnung_englisch.']]></stg_bezeichnung_engl>
	<stg_oe_parent><![CDATA['.$oe_parent.']]></stg_oe_parent>
	<stg_art><![CDATA['.$studiengang->typ.']]></stg_art>
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
	<projekt_typ><![CDATA['.$projektarbeit->projekttyp_bezeichnung.']]></projekt_typ>
	<projekt_fach><![CDATA['.$lehrveranstaltung->bezeichnung.']]></projekt_fach>
	<projekt_titel><![CDATA['.$projektarbeit->titel.']]></projekt_titel>
	<themenbereich_2><![CDATA['.$themenbereich_2.']]></themenbereich_2>
	<betreuer><![CDATA['.$betreuer.']]></betreuer>
	<betreuer_2><![CDATA['.$betreuer_2.']]></betreuer_2>
	<note><![CDATA['.$note.']]></note>
	<note_bezeichnung><![CDATA['.$projektnote->bezeichnung.']]></note_bezeichnung>
	<note2><![CDATA['.$note2.']]></note2>
	<notekommpruef><![CDATA['.$row->note.']]></notekommpruef>
	<datum_projekt><![CDATA['.$datum_projekt.']]></datum_projekt>
	<datum_projekt2><![CDATA['.$datum_projekt.']]></datum_projekt2>
	<ort_datum><![CDATA['.date('d.m.Y').']]></ort_datum>';

	echo "\n\t</pruefung>";
}

// ----------------------------------- RDF --------------------------------------
if ($xmlformat=='rdf')
{
	$pruefung = new abschlusspruefung();
	$rdf_url='http://www.technikum-wien.at/abschlusspruefung';
	function draw_content($row)
	{
		global $rdf_url, $datum_obj, $abschlussbeurteilung_arr;
		$vorsitz = '';
		$pruefer1= '';
		$pruefer2= '';
		$pruefer3= '';

		//Nachnamen der Pruefer holden
		$person = new person();
		$mitarbeiter = new mitarbeiter();

		$antritt = new abschlusspruefung_antritt();
		if ($row->pruefungsantritt_kurzbz!='')
			$antritt->load($row->pruefungsantritt_kurzbz);

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
				<ABSCHLUSSPRUEFUNG:abschlussbeurteilung_bezeichnung><![CDATA['.($row->abschlussbeurteilung_kurzbz!=''?$abschlussbeurteilung_arr[$row->abschlussbeurteilung_kurzbz]:'').']]></ABSCHLUSSPRUEFUNG:abschlussbeurteilung_bezeichnung>
				<ABSCHLUSSPRUEFUNG:notekommpruef><![CDATA['.$row->note.']]></ABSCHLUSSPRUEFUNG:notekommpruef>
				<ABSCHLUSSPRUEFUNG:akadgrad_id><![CDATA['.$row->akadgrad_id.']]></ABSCHLUSSPRUEFUNG:akadgrad_id>
				<ABSCHLUSSPRUEFUNG:datum><![CDATA['.$datum_obj->convertISODate($row->datum).']]></ABSCHLUSSPRUEFUNG:datum>
				<ABSCHLUSSPRUEFUNG:datum_iso><![CDATA['.$row->datum.']]></ABSCHLUSSPRUEFUNG:datum_iso>
				<ABSCHLUSSPRUEFUNG:uhrzeit><![CDATA['.$row->uhrzeit.']]></ABSCHLUSSPRUEFUNG:uhrzeit>
				<ABSCHLUSSPRUEFUNG:endezeit><![CDATA['.$row->endezeit.']]></ABSCHLUSSPRUEFUNG:endezeit>
				<ABSCHLUSSPRUEFUNG:freigabedatum_iso><![CDATA['.$row->freigabedatum.']]></ABSCHLUSSPRUEFUNG:freigabedatum_iso>
				<ABSCHLUSSPRUEFUNG:freigabedatum><![CDATA['.$datum_obj->convertISODate($row->freigabedatum).']]></ABSCHLUSSPRUEFUNG:freigabedatum>
				<ABSCHLUSSPRUEFUNG:pruefungsantritt_kurzbz><![CDATA['.$row->pruefungsantritt_kurzbz.']]></ABSCHLUSSPRUEFUNG:pruefungsantritt_kurzbz>
				<ABSCHLUSSPRUEFUNG:pruefungsantritt_bezeichnung><![CDATA['.$antritt->bezeichnung.']]></ABSCHLUSSPRUEFUNG:pruefungsantritt_bezeichnung>
				<ABSCHLUSSPRUEFUNG:protokoll><![CDATA['.$row->protokoll.']]></ABSCHLUSSPRUEFUNG:protokoll>
				<ABSCHLUSSPRUEFUNG:sponsion><![CDATA['.$datum_obj->convertISODate($row->sponsion).']]></ABSCHLUSSPRUEFUNG:sponsion>
				<ABSCHLUSSPRUEFUNG:sponsion_iso><![CDATA['.$row->sponsion.']]></ABSCHLUSSPRUEFUNG:sponsion_iso>
				<ABSCHLUSSPRUEFUNG:pruefungstyp_kurzbz><![CDATA['.$row->pruefungstyp_kurzbz.']]></ABSCHLUSSPRUEFUNG:pruefungstyp_kurzbz>
				<ABSCHLUSSPRUEFUNG:beschreibung><![CDATA['.$row->beschreibung.']]></ABSCHLUSSPRUEFUNG:beschreibung>
				<ABSCHLUSSPRUEFUNG:anmerkung><![CDATA['.$row->anmerkung.']]></ABSCHLUSSPRUEFUNG:anmerkung>
				<ABSCHLUSSPRUEFUNG:link_abschlusspruefung><![CDATA['.APP_ROOT.'index.ci.php/lehre/Pruefungsprotokoll/showProtokoll?abschlusspruefung_id='.$row->abschlusspruefung_id.']]></ABSCHLUSSPRUEFUNG:link_abschlusspruefung>
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
}
// ----------------------------------- XML --------------------------------------
elseif ($xmlformat=='xml')
{
	$pruefung = new abschlusspruefung();
	echo "\n<abschlusspruefung>\n";

	if(isset($_GET['uid']))
	{
		$uids = explode(';',$_GET['uid']);

		foreach ($uids as $uid)
		{
			if($uid!='')
			{
				$pruefung = new abschlusspruefung();
				if($pruefung->getAbschlusspruefungen($uid))
				{
					foreach ($pruefung->result as $row)
						draw_content_xml($row);
				}
			}
		}
	}
	elseif(isset($_GET['student_uid']))
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
}
?>
