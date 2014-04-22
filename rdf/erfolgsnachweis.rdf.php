<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
header("Content-type: application/xhtml+xml");
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/zeugnisnote.class.php');
require_once('../include/datum.class.php');
require_once('../include/note.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/studienordnung.class.php');
require_once('../include/studienplan.class.php');
require_once('../include/student.class.php');
require_once('../include/prestudent.class.php');

$datum = new datum();
$db = new basis_db();

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
	
	// Noten laden
	$note_arr = array();
	$note = new note();
	$note->getAll();
	foreach ($note->result as $n)
	{
		$note_arr[$n->note]['anmerkung'] = $n->anmerkung;
		$note_arr[$n->note]['positiv'] = $n->positiv;
	}
	$note_arr['']['anmerkung'] = '';
	$note_arr['']['positiv'] = false;
	
	// Studienjahr ermitteln
	if(isset($_GET['ss']))
		$studiensemester_kurzbz = $_GET['ss'];
	else 
		$studiensemester_kurzbz = $semester_aktuell;
	
	$studiensemester = new studiensemester();
	$studiensemester_kurzbz2 = $studiensemester->getStudienjahrStudiensemester($studiensemester_kurzbz);
	
	
	//Daten holen
	
	$xml = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>";
	$xml .= "<zeugnisse>";
	
	foreach($uid_arr as $uid)
	{	
		$student = new student();
		if(!$student->load($uid))
			die($student->errormsg);
		
		$studiengang = new studiengang();
		$stgleiter = $studiengang->getLeitung($student->studiengang_kz);
		$stgl='';
		foreach ($stgleiter as $stgleiter_uid)
		{
			$stgl_ma = new mitarbeiter($stgleiter_uid);
			$stgl .= trim($stgl_ma->titelpre.' '.$stgl_ma->vorname.' '.$stgl_ma->nachname.' '.$stgl_ma->titelpost);
		}
		
		$ausbildungssemester = 0;
		
		//Wenn das Semester 0 ist, dann wird das Semester aus der Rolle geholt. (Ausnahme: Incoming)
		//damit bei Outgoing Studenten die im 0. Semester angelegt sind das richtige Semester aufscheint
		$qry ="SELECT ausbildungssemester as semester FROM public.tbl_prestudentstatus 
				WHERE 
				prestudent_id=".$db->db_add_param($student->prestudent_id)." AND 
				studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." AND
				status_kurzbz not in('Incoming','Aufgenommener','Bewerber','Wartender', 'Interessent')
				ORDER BY DATUM DESC LIMIT 1";
		if($result_sem = $db->db_query($qry))
		{
			if($row_sem = $db->db_fetch_object($result_sem))
			{
				$ausbildungssemester = $row_sem->semester;
			}
		}

		$student_studienjahr = round($ausbildungssemester/2);
		
		$jahr1=mb_substr($studiensemester_kurzbz,1);
		$jahr2=mb_substr($studiensemester_kurzbz2,1);
		$studienjahr = ($jahr1>$jahr2?$jahr2.'/'.$jahr1:$jahr1.'/'.$jahr2); 
		
		$studiengang = new studiengang();
		$studiengang->load($student->studiengang_kz);
		
		$prestudent = new prestudent();
		$prestudent->getLastStatus($student->prestudent_id, $studiensemester_kurzbz);
		
		if($prestudent->studienplan_id=='')
		{
			die('keine Studienplan Zuordnung fuer '.$student->nachname);
		}
		$studienplan = new studienplan();
		if(!$studienplan->loadStudienplan($prestudent->studienplan_id))
			die('Studienplan ungueltig');
		
		$studienordnung = new studienordnung();
		if(!$studienordnung->loadStudienordnung($studienplan->studienordnung_id))
			die('Studienordnung ungueltig');
		
		
		$xml .= "\n	<zeugnis>";
		$xml .=	"\n		<semester>".$ausbildungssemester."</semester>";
		$xml .=	"\n		<studienjahr>".$studienjahr."</studienjahr>";
		$xml .=	"\n		<student_studienjahr>".$student_studienjahr."</student_studienjahr>";
		$xml .= "\n		<studiengang>".$studienordnung->studiengangbezeichnung."</studiengang>";
		$xml .= "\n		<studiengang_englisch>".$studienordnung->studiengangbezeichnung_englisch."</studiengang_englisch>";
		$xml .= "\n		<studiengang_typ>".$studiengang->typ."</studiengang_typ>";
		$xml .= "\n		<studiengang_kz>".sprintf('%04s', abs($studiengang->studiengang_kz))."</studiengang_kz>";
		$xml .= "\n		<anrede>".$student->anrede."</anrede>";
		$xml .= "\n		<vorname>".$student->vorname."</vorname>";
		$xml .= "\n		<nachname>".$student->nachname."</nachname>";
		$xml .= "\n		<name>".trim($student->titelpre.' '.trim($student->vorname.' '.$student->vornamen).' '.mb_strtoupper($student->nachname).($student->titelpost!=''?', '.$student->titelpost:''))."</name>";
		$gebdatum = date('d.m.Y',strtotime($student->gebdatum));
		$xml .= "\n		<gebdatum>".$gebdatum."</gebdatum>";
		$xml .= "\n		<personenkennzeichen>".$student->matrikelnr."</personenkennzeichen>";
		$xml .= "\n		<studiengangsleiter>".$stgl."</studiengangsleiter>";
		$datum_aktuell = date('d.m.Y');
		$xml .= "\n		<datum_aktuell>".$datum_aktuell."</datum_aktuell>";
		
		$obj = new zeugnisnote();
		

		$obj->getZeugnisnotenStudienplan($uid, array($studiensemester_kurzbz, $studiensemester_kurzbz2),$prestudent->studienplan_id);
		
		foreach ($obj->result as $row)
		{
			$stpllv[$row->studienplan_lehrveranstaltung_id] = $row->lehrveranstaltung_id;
		}
		
		foreach($obj->result as $row)
		{
			//Gruppieren der Module
			//$lvs['1']['childs']['2']=$obj;
			if($row->studienplan_lehrveranstaltung_id_parent=='')
			{
				$lehrveranstaltungen[$row->lehrveranstaltung_id]['data']=$row;
			}
			else
			{
				if(isset($lehrveranstaltungen[$stpllv[$row->studienplan_lehrveranstaltung_id_parent]]))
				{
					$lehrveranstaltungen[$stpllv[$row->studienplan_lehrveranstaltung_id_parent]]['childs'][$row->lehrveranstaltung_id]['data'] = $row;
				}
				else
				{
					foreach($lehrveranstaltungen as $key=>$row_module)
					{
						if(isset($lehrveranstaltungen[$key]['childs'][$stpllv[$row->studienplan_lehrveranstaltung_id_parent]]))
						{
							$lehrveranstaltungen[$key]['childs'][$stpllv[$row->studienplan_lehrveranstaltung_id_parent]]['childs'][$row->lehrveranstaltung_id]['data']=$row;
						}
					}
				}
			}
		}
		
        $ects_gesamt = 0;
        $ects_absolviert = 0; 
		foreach ($lehrveranstaltungen as $row_lehrveranstaltungen)	
		{
			$xml.=getLVRow($row_lehrveranstaltungen);
		}
        $xml .= "<ects_gesamt>".$ects_gesamt."</ects_gesamt>";
        $xml .= "<ects_absolviert>".$ects_absolviert."</ects_absolviert>";
		$xml .= "	</zeugnis>";
	}
	$xml .= "</zeugnisse>";
	echo $xml;

function getLVRow($obj)
{
	global $ects_gesamt, $ects_absolviert,$studienplan,$note_arr;
	$xml='';
	$row = $obj['data'];
	if($row->zeugnis)
	{
		if ($row->note)
			$note = $note_arr[$row->note]['anmerkung'];
		else
			$note = "";
	
		$bezeichnung = $row->lehrveranstaltung_bezeichnung;
		$bezeichnung_englisch = $row->lehrveranstaltung_bezeichnung_english;
	
		$wochen = $studienplan->semesterwochen;
	
		$xml .= "\n			<unterrichtsfach>";
		$xml .= "\n				<bezeichnung><![CDATA[".$bezeichnung."]]></bezeichnung>";
		$xml .= "\n				<bezeichnung_englisch><![CDATA[".$bezeichnung_englisch."]]></bezeichnung_englisch>";
		$xml .= "\n				<lvnr>".$row->lehrveranstaltung_lvnr."</lvnr>";
		$xml .= "\n				<stsem_kurz><![CDATA[".mb_substr($row->studiensemester_kurzbz,0,2)."]]></stsem_kurz>";
		$xml .= "\n				<semester><![CDATA[".$row->studienplan_lehrveranstaltung_semester."]]></semester>";
		$xml .= "\n				<note>".$note."</note>";
		$xml .= "\n				<positiv>".($note_arr[$row->note]['positiv']?'Ja':'Nein')."</positiv>";
		$xml .= "\n				<sws>".($row->semesterstunden==0?'':number_format(sprintf('%.1F',$row->semesterstunden/$wochen),1))."</sws>";
		$ectspunkte='';
	
		if($row->ects==0 || $row->ects=='')
			$ectspunkte='';
		else
		{
			//Bei 2 Nachkommastellen beide anzeigen, sonst nur 1
			if(number_format($row->ects,1)==number_format($row->ects,2))
				$ectspunkte=number_format($row->ects,1);
			else 	
				$ectspunkte=number_format($row->ects,2);
		}
		$ects_gesamt+=$ectspunkte; 
		
		if($note_arr[$row->note]['positiv'])
			$ects_absolviert+=$ectspunkte;
		
		$xml .= "\n				<ects>".$ectspunkte."</ects>";
		$xml .= "\n				<lehrform>".$row->lv_lehrform_kurzbz."</lehrform>";		

		if(isset($obj['childs']))
		{
			foreach($obj['childs'] as $row_childs)
			{
				$xml.=getLVRow($row_childs);
			}
		}
		
		$xml .= "\n			</unterrichtsfach>";
	}

	return $xml;
}
?>
