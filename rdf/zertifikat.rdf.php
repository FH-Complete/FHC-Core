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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Raab <gerald.raab@technikum-wien.at>.
 */
// content type setzen
header("Content-type: application/xhtml+xml");
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/zeugnisnote.class.php');
require_once('../include/datum.class.php');
require_once('../include/note.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/studiengang.class.php');

$datum = new datum();
$db = new basis_db();
$projektarbeit=array();
$fussnotenzeichen=array('¹)','²)','³)');
$anzahl_fussnoten=0;
$studiengang_typ='';
$xml_fussnote='';

function breaktext($text, $zeichen)
{
	$arr = explode(' ',$text);
	$ret = '';
	$teilstring='';
	
	foreach($arr as $elem)
	{
		if(strlen($teilstring.$elem)>$zeichen)
		{
			$ret.=' '.$teilstring.'\n';
			$teilstring=$elem;
		}
		else 
			$teilstring .=' '.$elem;
	}
	$ret.=$teilstring;
	return $ret;
}

if (isset($_REQUEST["xmlformat"]) && $_REQUEST["xmlformat"] == "xml")
{

	if(isset($_GET['uid']))
		$uid = $_GET['uid'];
	else 
		$uid = null;
	
	$uid_arr = explode(";",$uid);

	if ($uid_arr[0] == "")
	{
		unset($uid_arr[0]);
		$uid_arr = array_values($uid_arr);
	}
	
	$note_arr = array();
	$note = new note();
	$note->getAll();
	foreach ($note->result as $n){
		$note_arr[$n->note] = $n->anmerkung;
		$note_bezeichnung_arr[$n->note] = $n->bezeichnung;	
	
	}
	if(isset($_GET['ss']))
		$studiensemester_kurzbz = $_GET['ss'];
	else 
		die('Studiensemester muss uebergeben werden');

	if(isset($_GET['lvid']))
		$lehrveranstaltung_id = $_GET['lvid'];
	else 
		$lehrveranstaltung_id = 0;
	
	//Daten holen

	$lqry = "SELECT 
				tbl_person.* 
			FROM 
				public.tbl_benutzer JOIN public.tbl_person using (person_id) 
			WHERE 
				tbl_benutzer.uid = (SELECT 
										tbl_lehreinheitmitarbeiter.mitarbeiter_uid 
									FROM 
										lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehrfunktion USING(lehrfunktion_kurzbz), 
										lehre.tbl_lehreinheit JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) 
									WHERE 
										tbl_lehreinheitmitarbeiter.lehreinheit_id = tbl_lehreinheit.lehreinheit_id AND
										tbl_lehrveranstaltung.lehrveranstaltung_id = '".addslashes($lehrveranstaltung_id)."' AND
										tbl_lehreinheit.studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'
									ORDER BY tbl_lehrfunktion.standardfaktor desc limit 1)";
	if($db->db_query($lqry))
	{
		if ($lrow = $db->db_fetch_object())
		{
			$leiter_titel = $lrow->titelpre;			
			$leiter_vorname = $lrow->vorname;
			$leiter_nachname = $lrow->nachname;			
		}		
	}	
	
	$lvqry = "SELECT * from lehre.tbl_lehrveranstaltung where lehrveranstaltung_id = '".addslashes($lehrveranstaltung_id)."'";
	if($db->db_query($lvqry))
	{
		if ($lvrow = $db->db_fetch_object())
		{
			$sws = $lvrow->semesterstunden;
			$ects = $lvrow->ects;
			$lvbezeichnung = $lvrow->bezeichnung;			
		}		
	}
	
	$lehrinhalte = '';
	$infoqry = "SELECT * FROM campus.tbl_lvinfo WHERE sprache='German' AND lehrveranstaltung_id = '".addslashes($lehrveranstaltung_id)."'";
	if($db->db_query($infoqry))
	{
		if ($inforow = $db->db_fetch_object())
		{
			$lehrinhalte_arr = explode("<br>",$inforow->lehrinhalte);			
			for ($i = 0; $i < sizeof($lehrinhalte_arr); $i++)
			{
				$lehrinhalte .= $lehrinhalte_arr[$i].'\n';			
			}
		}		
	}	
	
	$xml = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>";
	$xml .= "<zertifikate>";
	
	$studiensemester = new studiensemester();
	$studiensemester->load($studiensemester_kurzbz);
			
	for ($i = 0; $i < sizeof($uid_arr); $i++)
	{	
		$anzahl_fussnoten=0;
		$studiengang_typ='';
		$xml_fussnote='';
		
		$query = "SELECT tbl_student.matrikelnr, tbl_student.studiengang_kz, tbl_studiengang.typ, tbl_studiengang.bezeichnung, tbl_person.vorname, tbl_person.nachname,tbl_person.gebdatum,tbl_person.titelpre, tbl_person.titelpost FROM tbl_person, tbl_student, tbl_studiengang, tbl_benutzer WHERE tbl_student.studiengang_kz = tbl_studiengang.studiengang_kz and tbl_student.student_uid = tbl_benutzer.uid and tbl_benutzer.person_id = tbl_person.person_id and tbl_student.student_uid = '".$uid_arr[$i]."'";

		if($db->db_query($query))
		{
				if(!$row = $db->db_fetch_object())
					die('Student not found');
		}
		else
			die('Student not found');
		$stg_oe_obj = new studiengang($row->studiengang_kz);
		$stgl_query = "SELECT 
							titelpre, titelpost, vorname, nachname 
						FROM 
							tbl_person, tbl_benutzer, tbl_benutzerfunktion 
						WHERE 
							tbl_person.person_id = tbl_benutzer.person_id AND 
							tbl_benutzer.uid = tbl_benutzerfunktion.uid AND 
							tbl_benutzerfunktion.funktion_kurzbz = 'stgl' AND 
							tbl_benutzerfunktion.oe_kurzbz = '".$stg_oe_obj->oe_kurzbz."' AND
							(tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now()) AND
							(tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now())";
		if($db->db_query($stgl_query))
			$stgl_row = $db->db_fetch_object();
		else
			die('Stgl not found');
		
		$xml .= "\n	<zertifikat>";
		$xml .= "		<studiensemester>".$studiensemester->bezeichnung."</studiensemester>";
		$xml .= "\n		<vorname>".$row->vorname."</vorname>";
		$xml .= "		<nachname>".$row->nachname."</nachname>";
		$xml .= "		<name>".trim($row->titelpre.' '.$row->vorname.' '.strtoupper($row->nachname).' '.$row->titelpost)."</name>";
		$gebdatum = date('d.m.Y',strtotime($row->gebdatum));
		$xml .= "		<gebdatum>".$gebdatum."</gebdatum>";
		$xml .= "		<matrikelnr>".$row->matrikelnr."</matrikelnr>";
		if(isset($stgl_row->nachname))
			$xml .= "		<studiengangsleiter>".$stgl_row->titelpre." ".$stgl_row->vorname." ".$stgl_row->nachname."</studiengangsleiter>";
		else 
			$xml .= "		<studiengangsleiter></studiengangsleiter>";
		$datum_aktuell = date('d.m.Y');
		$xml .= "		<ort_datum>Wien, am ".$datum_aktuell."</ort_datum>";
		
		
		$obj = new zeugnisnote();
		$obj->load($lehrveranstaltung_id, $uid_arr[$i], $studiensemester_kurzbz);

		if ($obj->note)
		{
			$note = $note_arr[$obj->note];
			$note_bezeichnung = $note_bezeichnung_arr[$obj->note];
		}		
		else
		{
			$note = "";
			$note_bezeichnung = "";
		}		
		$note2=$note;

		$xml .= "				<bezeichnung><![CDATA[".$lvbezeichnung."]]></bezeichnung>";
		$xml .= "				<note>".$note."</note>";
		$xml .= "				<note_bezeichnung>".$note_bezeichnung."</note_bezeichnung>";
		$xml .= "				<sws>".$sws."</sws>";
		$xml .= "				<ects>".number_format($ects,1)."</ects>";
		$xml .= "				<lvleiter>".$leiter_titel." ".$leiter_vorname." ".$leiter_nachname."</lvleiter>";
		$xml .= "				<lehrinhalte><![CDATA[".$lehrinhalte."]]></lehrinhalte>";

		
		$xml .= "	</zertifikat>";
	}
	$xml .= "</zertifikate>";
	echo $xml;
}
?>