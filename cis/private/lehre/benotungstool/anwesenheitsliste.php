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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
 */

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/basis_db.class.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/studiengang.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/lehreinheit.class.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/uebung.class.php');
require_once('../../../../include/beispiel.class.php');
require_once('../../../../include/datum.class.php');
include_once('../../../../include/Excel/excel.php');

if (!$db = new basis_db())
	die('Fehler beim Herstellen der Datenbankverbindung');

$user = get_uid();

if(!check_lektor($user))
	die('Sie haben keine Berechtigung fuer diesen Bereich');

if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
	$stsem = '';

if(isset($_GET['uebung_id']) && is_numeric($_GET['uebung_id']))
{
	$uebung_id = $_GET['uebung_id'];
	$uebung_obj = new uebung($uebung_id);
	$lehreinheit_obj = new lehreinheit($uebung_obj->lehreinheit_id);
}
else
{
	if(!isset($_GET['all'])) 
			die('Fehlerhafte Parameteruebergabe');
	else 
	{
		$lehreinheit_id = $_GET['lehreinheit_id'];
		$lehreinheit_obj = new lehreinheit($lehreinheit_id);
	}
}

//Abgabedatei ausliefern
if (isset($_GET["download_abgabe"])){
	$file=$_GET["download_abgabe"];
	$uebung_id = $_GET["uebung_id"];
	$uid = $_GET['uid'];
	$ueb = new uebung();

	$ueb->load_studentuebung($uid, $uebung_id);
	$ueb->load_abgabe($ueb->abgabe_id);
	$filename = BENOTUNGSTOOL_PATH."abgabe/".$ueb->abgabedatei;
	header('Content-Type: application/octet-stream');
	header('Content-disposition: attachment; filename="'.$file.'"');
	readfile($filename);
	exit;
}
/*
$qry = "SELECT * FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id) WHERE 
		tbl_lehreinheit.lehreinheit_id=".$db->db_add_param($lehreinheit_obj->lehreinheit_id, FHC_INTEGER)." AND
		mitarbeiter_uid=".$db->db_add_param($user);
*/
$qry = "SELECT * FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id) WHERE 
		tbl_lehreinheit.lehrveranstaltung_id in(Select lehrveranstaltung_id from lehre.tbl_lehreinheit where lehreinheit_id=".$db->db_add_param($lehreinheit_obj->lehreinheit_id, FHC_INTEGER).") AND
		mitarbeiter_uid=".$db->db_add_param($user);

if(!$result = $db->db_query($qry))
	die('Fehler beim laden der Berechtigung');

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
	
if(!($db->db_num_rows($result)>0 || $rechte->isBerechtigt('admin',0) || $rechte->isBerechtigt('admin',$lehreinheit_obj->studiengang_kz) || $rechte->isBerechtigt('lehre',$lehreinheit_obj->studiengang_kz)))
	die('Sie haben keine Berechtigung f&uuml;r diesen Bereich');

// Beteiligte Gruppen laden
$gruppen = '';	
$qry_gruppen = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id=".$db->db_add_param($lehreinheit_obj->lehreinheit_id, FHC_INTEGER);
if($result_gruppen = $db->db_query($qry_gruppen))
{
	$i=0;
	while($row_gruppen = $db->db_fetch_object($result_gruppen))
	{
		if($row_gruppen->gruppe_kurzbz=='')
			$gruppen.=$row_gruppen->semester.$row_gruppen->verband.$row_gruppen->gruppe;
		else
			$gruppen.=$row_gruppen->gruppe_kurzbz;
		$i++;
		if($i<$db->db_num_rows($result_gruppen))
			$gruppen.=', ';
		else
			$gruppen.=' ';
	}
}

if(isset($_GET['output']) && $_GET['output']=='xls')
{
	if(isset($_GET['all']))
	{
		//EXCEL VERSION / ALLE Kreuzerllisten
		$le_obj = new lehreinheit();
		$le_obj->load($lehreinheit_id);
		
		$lv_obj = new lehrveranstaltung();
		$lv_obj->load($le_obj->lehrveranstaltung_id);
		
		// Creating a workbook
		$workbook = new Spreadsheet_Excel_Writer();
		$workbook->setVersion(8);
		// sending HTTP headers
		$workbook->send("Kreuzerlliste_Gesamt_".$lv_obj->lehreverzeichnis. "_" . date("d_m_Y") . ".xls");
	
		// Creating a worksheet
		$worksheet =& $workbook->addWorksheet("Kreuzerltool");
		$worksheet->setInputEncoding('utf-8');
	
		$format_bold =& $workbook->addFormat();
		$format_bold->setBold();
	
		$format_title =& $workbook->addFormat();
		$format_title->setBold();
		// let's merge
		$format_title->setAlign('merge');
		
		
		
		$worksheet->write(0,0,'Gesamtübersicht '.$lv_obj->bezeichnung.' vom '.date('d.m.Y'), $format_bold);
		$maxlength = array();
		
		//Ueberschrift
		$i=0;
		$worksheet->write(1,$i,"Vorname", $format_title);
		$maxlength[$i]=strlen('Vorname');
		$worksheet->write(1,++$i,"Nachname", $format_title);
		$maxlength[$i]=strlen('Nachname');
		$worksheet->write(1,++$i,"Matrikelnr", $format_title);
		$maxlength[$i]=strlen('Matrikelnr');
		$worksheet->write(1,++$i,"Gruppe", $format_title);
		$maxlength[$i]=strlen('Gruppe');
		$ueb_obj = new uebung();
		$ueb_obj->load_uebung($lehreinheit_id);
		foreach($ueb_obj->uebungen as $row_ueb)
		{
			$worksheet->write(1,++$i,$row_ueb->bezeichnung, $format_title);
			$maxlength[$i]=strlen($row_ueb->bezeichnung);
		}
		$worksheet->write(1,++$i,"Summe", $format_title);
		$maxlength[$i]=8;
		$worksheet->write(1,++$i,"Mitarbeit insgesamt", $format_title);
		$maxlength[$i]=strlen('Mitarbeit insgesamt');
		$worksheet->write(1,++$i,"Punkte insgesamt", $format_title);
		$maxlength[$i]=strlen('Punkte insgesamt');
		$worksheet->write(1,++$i,"Unterschrift", $format_title);
		$maxlength[$i]=strlen('Unterschrift')+5;
		
		if(isset($_GET['gruppe']) && $_GET['gruppe']!='')
		{
			$gruppe = $_GET['gruppe'];
			$qry = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheitgruppe_id=".$db->db_add_param($gruppe);
			if($result = $db->db_query($qry))
			{
				if($row = $db->db_fetch_object($result))
				{
					if($row->gruppe_kurzbz!='')
					{
						$gruppe_bez = 'Gruppe '.$row->gruppe_kurzbz;
						$qry_stud = "SELECT uid, vorname, nachname, matrikelnr FROM campus.vw_student JOIN public.tbl_benutzergruppe USING(uid) WHERE gruppe_kurzbz=".$db->db_add_param($row->gruppe_kurzbz)." AND studiensemester_kurzbz = ".$db->db_add_param($stsem)." ORDER BY nachname, vorname";
					}
					else 
					{
						$gruppe_bez = 'Gruppe '.$row->verband.$row->gruppe;
						$qry_stud = "SELECT uid, vorname, nachname, matrikelnr FROM campus.vw_student 
									 WHERE studiengang_kz=".$db->db_add_param($row->studiengang_kz)." 
									 AND semester=".$db->db_add_param($row->semester).
									 ($row->verband!=''?" AND verband=".$db->db_add_param($row->verband):'').
									 ($row->gruppe!=''?" AND gruppe=".$db->db_add_param($row->gruppe):'').
									 " ORDER BY nachname, vorname";
					}
					
				}
				else
					die('Gruppe konnte nicht ermittelt werden');
			}
			else 
				die('Gruppe konnte nicht ermittelt werden');
		}
		else 
		{
			if(isset($_GET['lehreinheit_id']) && $_GET['lehreinheit_id']!='')
			{
				$lehreinheit_id = $_GET['lehreinheit_id'];
				$gruppe_bez = 'Alle Studienrende';
				//Alle Studenten die dieser Lehreinheit zugeordnet sind
				$qry_stud = "SELECT 
								vw_student.uid, vorname, nachname, matrikelnr,
								tbl_studentlehrverband.semester, tbl_studentlehrverband.verband, tbl_studentlehrverband.gruppe 
							FROM 
								campus.vw_student, public.tbl_benutzergruppe, lehre.tbl_lehreinheitgruppe, 
								public.tbl_studentlehrverband, lehre.tbl_lehreinheit
							WHERE 
								tbl_lehreinheitgruppe.lehreinheit_id=".$db->db_add_param($lehreinheit_id)." AND 
								tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitgruppe.lehreinheit_id AND
								vw_student.uid = tbl_benutzergruppe.uid AND
								tbl_benutzergruppe.gruppe_kurzbz = tbl_lehreinheitgruppe.gruppe_kurzbz AND
								vw_student.uid=tbl_studentlehrverband.student_uid AND
								tbl_studentlehrverband.studiensemester_kurzbz=tbl_lehreinheit.studiensemester_kurzbz
							UNION
							SELECT 
								vw_student.uid, vorname, nachname, matrikelnr, tbl_studentlehrverband.semester, 
								tbl_studentlehrverband.verband, tbl_studentlehrverband.gruppe 
							FROM 
								campus.vw_student, lehre.tbl_lehreinheitgruppe, public.tbl_studentlehrverband, lehre.tbl_lehreinheit
							WHERE
								tbl_lehreinheitgruppe.lehreinheit_id=".$db->db_add_param($lehreinheit_id)." AND
								tbl_lehreinheitgruppe.studiengang_kz=tbl_studentlehrverband.studiengang_kz AND
								tbl_lehreinheitgruppe.semester = tbl_studentlehrverband.semester AND
								tbl_studentlehrverband.student_uid=vw_student.uid AND
								tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitgruppe.lehreinheit_id AND
								tbl_lehreinheit.studiensemester_kurzbz=tbl_studentlehrverband.studiensemester_kurzbz AND
							((tbl_lehreinheitgruppe.verband<>'' AND 
							  tbl_lehreinheitgruppe.gruppe<>'' AND 
							  trim(tbl_lehreinheitgruppe.verband) = trim(tbl_studentlehrverband.verband) AND
							  trim(tbl_lehreinheitgruppe.gruppe) = trim(tbl_studentlehrverband.gruppe))
							OR
							(tbl_lehreinheitgruppe.verband<>'' AND 
							  (trim(tbl_lehreinheitgruppe.gruppe)='' OR tbl_lehreinheitgruppe.gruppe is null) AND
							  trim(tbl_lehreinheitgruppe.verband) = trim(tbl_studentlehrverband.verband))
							  OR (tbl_lehreinheitgruppe.verband is null AND tbl_lehreinheitgruppe.gruppe is null)
							  )
							 ORDER BY nachname, vorname";
			}
			else 
				die('Fehler bei der Parameteruebergabe');
			$gruppe='';
		}
		
		if($result_stud = $db->db_query($qry_stud))
		{
			$zeile=3;
					
			while($row_stud = $db->db_fetch_object($result_stud))
			{			
				$spalte=0;
				$summe=0;
				//vorname
				$worksheet->write($zeile,$spalte,$row_stud->vorname);
				if(strlen($row_stud->vorname)>$maxlength[$spalte])
					$maxlength[$spalte]=strlen($row_stud->vorname);
				//nachname
				$worksheet->write($zeile,++$spalte,$row_stud->nachname);
				if(strlen($row_stud->nachname)>$maxlength[$spalte])
					$maxlength[$spalte]=strlen($row_stud->nachname);
				//matrikelnr
				$worksheet->write($zeile,++$spalte,'="'.$row_stud->matrikelnr.'"');
				if(strlen($row_stud->matrikelnr)>$maxlength[$spalte])
					$maxlength[$spalte]=strlen($row_stud->matrikelnr);					
				//Gruppe
				$worksheet->write($zeile,++$spalte,$row_stud->semester.$row_stud->verband.$row_stud->gruppe);
				if(strlen($row_stud->semester.$row_stud->verband.$row_stud->gruppe)>$maxlength[$spalte])
					$maxlength[$spalte]=strlen($row_stud->semester.$row_stud->verband.$row_stud->gruppe);
					
				foreach($ueb_obj->uebungen as $row_ueb)
				{
					$qry = "SELECT sum(punkte) as punkte FROM campus.tbl_studentbeispiel JOIN campus.tbl_beispiel USING(beispiel_id) 
						WHERE uebung_id=".$db->db_add_param($row_ueb->uebung_id)." AND uid=".$db->db_add_param($row_stud->uid)." AND vorbereitet=true";
					if($result = $db->db_query($qry))
					{
						if($row = $db->db_fetch_object($result))
						{
							$punkte = $row->punkte;
							$summe +=$punkte;
						}
						else 
							$punkte = 'failed';
					}
					else 
						$punkte='failed';
					//punkte auf uebung
					$worksheet->write($zeile,++$spalte,($punkte!=''?$punkte:'0'));
				}
				
				//summe
				$worksheet->write($zeile,++$spalte,$summe);
				
				//mitarbeit
				$qry = "SELECT sum(mitarbeitspunkte) as mitarbeit FROM campus.tbl_studentuebung JOIN campus.tbl_uebung USING(uebung_id) 
				WHERE lehreinheit_id=".$db->db_add_param($lehreinheit_id, FHC_INTEGER)." AND uid=".$db->db_add_param($row_stud->uid);
				if($result = $db->db_query($qry))
					if($row = $db->db_fetch_object($result))
						$mitarbeit=$row->mitarbeit;	
					else 
						$mitarbeit='failed';
				else 
					$mitarbeit='failed';
					
				$worksheet->write($zeile,++$spalte,($row->mitarbeit!=''?$mitarbeit:'0'));
				//punkte insgesamt
				$worksheet->write($zeile,++$spalte,($summe+$mitarbeit), $format_bold);
				
				$zeile++;		
			}
			for($i=0;$i<count($maxlength);$i++)
			{
				$worksheet->setColumn(0, $i, $maxlength[$i]);
			}
		}
		
		$workbook->close();

	}
	else 
	{
		//EXCEL VERSION / Einzelne Kreuzerlliste
		
		// Creating a workbook
		$workbook = new Spreadsheet_Excel_Writer();
		$workbook->setVersion(8);
	
		// sending HTTP headers
		$workbook->send("Kreuzerltool". "_" . date("d_m_Y") . ".xls");
	
		// Creating a worksheet
		$worksheet =& $workbook->addWorksheet("Kreuzerltool");
		$worksheet->setInputEncoding('utf-8');
	
		$format_bold =& $workbook->addFormat();
		$format_bold->setBold();
	
		$format_title =& $workbook->addFormat();
		$format_title->setBold();
		// let's merge
		$format_title->setAlign('merge');
	
		$worksheet->write(0,0,$uebung_obj->bezeichnung.' am '.date('d.m.Y').' '.$gruppen, $format_bold);
		$maxlength = array();
		//Ueberschrift
		$i=0;
		$worksheet->write(1,$i,"Vorname", $format_title);
		$maxlength[$i]=strlen('Vorname');
		$worksheet->write(1,++$i,"Nachname", $format_title);
		$maxlength[$i]=strlen('Nachname');
		$worksheet->write(1,++$i,"Matrikelnr", $format_title);
		$maxlength[$i]=strlen('Matrikelnr');
		//$worksheet->write(1,++$i,"Gruppe", $format_title);
		//$maxlength[$i]=strlen('Gruppe');
		$beispiel_obj = new beispiel();
		$beispiel_obj->load_beispiel($uebung_id);
		foreach($beispiel_obj->beispiele as $row_bsp)
		{
			$worksheet->write(1,++$i,$row_bsp->bezeichnung, $format_title);
			$maxlength[$i]=strlen($row_bsp->bezeichnung);
		}
		$worksheet->write(1,++$i,"Punkte heute", $format_title);
		$maxlength[$i]=strlen('Punkte heute');
		$worksheet->write(1,++$i,"Mitarbeit heute", $format_title);
		$maxlength[$i]=strlen('Mitarbeit_heute');
		$worksheet->write(1,++$i,"Punkte insgesamt", $format_title);
		$maxlength[$i]=strlen('Punkte insgesamt');
		$worksheet->write(1,++$i,"Mitarbeit insgesamt", $format_title);
		$maxlength[$i]=strlen('Mitarbeit insgesamt');
		$worksheet->write(1,++$i,"Unterschrift", $format_title);
		$maxlength[$i]=strlen('Unterschrift')+5;
		
		if(isset($_GET['gruppe']) && $_GET['gruppe']!='')
		{
			$gruppe = $_GET['gruppe'];
			$qry = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheitgruppe_id=".$db->db_add_param($gruppe);
			if($result = $db->db_query($qry))
			{
				if($row = $db->db_fetch_object($result))
				{
					if($row->gruppe_kurzbz!='')
					{
						$gruppe_bez = 'Gruppe '.$row->gruppe_kurzbz;
						$qry_stud = "SELECT uid, vorname, nachname, matrikelnr, vw_student.semester, vw_student.verband, vw_student.gruppe 
						FROM campus.vw_student JOIN public.tbl_benutzergruppe USING(uid) 
						WHERE gruppe_kurzbz=".$db->db_add_param($row->gruppe_kurzbz)." AND studiensemester_kurzbz=".$db->db_add_param($stsem)." ORDER BY nachname, vorname";
					}
					else 
					{
						$gruppe_bez = 'Gruppe '.$row->verband.$row->gruppe;
						$qry_stud = "SELECT uid, vorname, nachname, matrikelnr, vw_student.semester, vw_student.verband, vw_student.gruppe FROM campus.vw_student 
									 WHERE studiengang_kz=".$db->db_add_param($row->studiengang_kz)." 
									 AND semester=".$db->db_add_param($row->semester).
									 ($row->verband!=''?" AND verband=".$db->db_add_param($row->verband):'').
									 ($row->gruppe!=''?" AND gruppe=".$db->db_add_param($row->gruppe):'').
									 " ORDER BY nachname, vorname";
					}
					
				}
				else
					die('Gruppe konnte nicht ermittelt werden');
			}
			else 
				die('Gruppe konnte nicht ermittelt werden');
				
			
			$lehreinheit_id = $uebung_obj->lehreinheit_id;
		}
		else 
		{
			if(isset($_GET['lehreinheit_id']) && $_GET['lehreinheit_id']!='')
			{
				$lehreinheit_id = $_GET['lehreinheit_id'];
				$gruppe_bez = 'Alle Studienrende';

				$qry_stud = "SELECT uid, vorname, nachname, matrikelnr FROM campus.vw_student_lehrveranstaltung JOIN campus.vw_student using(uid)
				WHERE  studiensemester_kurzbz = ".$db->db_add_param($stsem)." AND lehreinheit_id=".$db->db_add_param($lehreinheit_id, FHC_INTEGER)." ORDER BY nachname, vorname";
			
				//Alle Studenten die dieser Lehreinheit zugeordnet sind
				/*
				$qry_stud = "SELECT vw_student.uid, vorname, nachname, matrikelnr, vw_student.semester, vw_student.verband, vw_student.gruppe 
							FROM campus.vw_student, public.tbl_benutzergruppe, lehre.tbl_lehreinheitgruppe 
							WHERE tbl_lehreinheitgruppe.lehreinheit_id=".$db->db_add_param($lehreinheit_id, FHC_INTEGER)." AND 
							vw_student.uid = tbl_benutzergruppe.uid AND
							tbl_benutzergruppe.gruppe_kurzbz = tbl_lehreinheitgruppe.gruppe_kurzbz
							UNION
							SELECT vw_student.uid, vorname, nachname, matrikelnr, vw_student.semester, vw_student.verband, vw_student.gruppe 
							FROM campus.vw_student, lehre.tbl_lehreinheitgruppe WHERE
							tbl_lehreinheitgruppe.lehreinheit_id=".$db->db_add_param($lehreinheit_id)." AND
							tbl_lehreinheitgruppe.studiengang_kz=vw_student.studiengang_kz AND
							tbl_lehreinheitgruppe.semester = vw_student.semester AND
							((tbl_lehreinheitgruppe.verband<>'' AND 
							  tbl_lehreinheitgruppe.gruppe<>'' AND 
							  trim(tbl_lehreinheitgruppe.verband) = trim(vw_student.verband) AND
							  trim(tbl_lehreinheitgruppe.gruppe) = trim(vw_student.gruppe))
							OR
							(tbl_lehreinheitgruppe.verband<>'' AND 
							  (trim(tbl_lehreinheitgruppe.gruppe)='' OR tbl_lehreinheitgruppe.gruppe is null) AND
							  trim(tbl_lehreinheitgruppe.verband) = trim(vw_student.verband))
							  OR (tbl_lehreinheitgruppe.verband is null AND tbl_lehreinheitgruppe.gruppe is null)
							  )
							 ORDER BY nachname, vorname";
				*/
			}
			else 
				die('Fehler bei der Parameteruebergabe');
			$gruppe='';
		}

		if($result_stud = $db->db_query($qry_stud))
		{
			$zeile=3;
					
			while($row_stud = $db->db_fetch_object($result_stud))
			{			
				$spalte=0;
				$punkte_heute=0;
				//vorname
				$worksheet->write($zeile,$spalte,$row_stud->vorname);
				if(strlen($row_stud->vorname)>$maxlength[$spalte])
					$maxlength[$spalte]=strlen($row_stud->vorname);
				//nachname
				$worksheet->write($zeile,++$spalte,$row_stud->nachname);
				if(strlen($row_stud->nachname)>$maxlength[$spalte])
					$maxlength[$spalte]=strlen($row_stud->nachname);
				//matrikelnr
				$worksheet->write($zeile,++$spalte,'="'.$row_stud->matrikelnr.'"');
				if(strlen($row_stud->matrikelnr)>$maxlength[$spalte])
					$maxlength[$spalte]=strlen($row_stud->matrikelnr);
				
				//Gruppe
				/*
				$worksheet->write($zeile,++$spalte,$row_stud->semester.$row_stud->verband.$row_stud->gruppe);
				if(strlen($row_stud->semester.$row_stud->verband.$row_stud->gruppe)>$maxlength[$spalte])
					$maxlength[$spalte]=strlen($row_stud->semester.$row_stud->verband.$row_stud->gruppe);
				*/
				foreach($beispiel_obj->beispiele as $row_bsp)
				{
					$studentbeispiel_obj = new beispiel();
					$studentbeispiel_obj->load_studentbeispiel($row_stud->uid, $row_bsp->beispiel_id);
					if($studentbeispiel_obj->vorbereitet)
						$punkte = $row_bsp->punkte;
					else 
						$punkte = 0;
					$punkte_heute +=$punkte;
					//punkte auf uebung
					$worksheet->write($zeile,++$spalte,$punkte);
				}
				
				//punkte heute
				$worksheet->write($zeile,++$spalte,$punkte_heute);
				
				//mitarbeit heute
				$qry = "SELECT sum(mitarbeitspunkte) as mitarbeit_heute FROM campus.tbl_studentuebung 
					WHERE uebung_id=".$db->db_add_param($uebung_id, FHC_INTEGER)." AND uid=".$db->db_add_param($row_stud->uid);
				if($result = $db->db_query($qry))
					if($row = $db->db_fetch_object($result))
						$worksheet->write($zeile,++$spalte,($row->mitarbeit_heute!=''?$row->mitarbeit_heute:'0'));
					else 
						$worksheet->write($zeile,++$spalte,'failed');
				else 
					$worksheet->write($zeile,++$spalte,'failed');
				
				//punkte insgesamt
				$qry = "SELECT sum(tbl_beispiel.punkte) AS gesamt_ohne_mitarbeit FROM campus.tbl_uebung, campus.tbl_beispiel, campus.tbl_studentbeispiel WHERE
						tbl_studentbeispiel.uid=".$db->db_add_param($row_stud->uid)." AND
						tbl_studentbeispiel.vorbereitet=true AND
						tbl_uebung.lehreinheit_id=".$db->db_add_param($uebung_obj->lehreinheit_id, FHC_INTEGER)." AND
						tbl_uebung.uebung_id=tbl_beispiel.uebung_id AND
						tbl_beispiel.beispiel_id=tbl_studentbeispiel.beispiel_id
						";
				if($result = $db->db_query($qry))
					if($row = $db->db_fetch_object($result))
						$worksheet->write($zeile,++$spalte,($row->gesamt_ohne_mitarbeit!=''?$row->gesamt_ohne_mitarbeit:'0'));
					else 
						$worksheet->write($zeile,++$spalte,'failed');
				else 
					$worksheet->write($zeile,++$spalte,'failed');
				
				//mitarbeit insgesamt
				$qry = "SELECT sum(mitarbeitspunkte) as mitarbeit_heute FROM campus.tbl_studentuebung JOIN campus.tbl_uebung USING(uebung_id) 
				WHERE uid=".$db->db_add_param($row_stud->uid)." AND lehreinheit_id=".$db->db_add_param($lehreinheit_id, FHC_INTEGER);
				if($result = $db->db_query($qry))
					if($row = $db->db_fetch_object($result))
						$worksheet->write($zeile,++$spalte,($row->mitarbeit_heute!=''?$row->mitarbeit_heute:'0'));
					else 
						$worksheet->write($zeile,++$spalte,'failed');
				else 
					$worksheet->write($zeile,++$spalte,'failed');
				
				$zeile++;		
			}
			for($i=0;$i<count($maxlength);$i++)
				$worksheet->setColumn(0, $i, $maxlength[$i]);
		}
		
		$workbook->close();
	}
}
else 
{
	//HTML VERSION
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../../skin/style.css.php" rel="stylesheet" type="text/css">
<title>Kreuzerltool</title>
<script language="Javascript">
function addUser(student_uid) 
{
	var upd, upd_f;
	upd = document.forms[0].update_ids;
	upd_f = "update_" + student_uid;
	if (document.forms[0].elements[upd_f].checked != true)
	{
		document.forms[0].elements[upd_f].checked = true;
		upd.value += student_uid + "#";
	}
}
</script>
</head>
<body>
	<?php
	if(isset($_POST['submit']))
	{		
		//Update der Daten
		$uids = explode('#',$_POST['update_ids']);
		
		$uebung_obj = new uebung($uebung_id);
		$beispiel_obj = new beispiel();
		$beispiel_obj->load_beispiel($uebung_id);
		$error=false;
		foreach($uids as $uid)
		{
			if($uid!='')
			{
				if ($uebung_obj->beispiele)
				{
					foreach($beispiel_obj->beispiele as $bsp)
					{
						if(isset($_POST['update_'.$uid.'_'.$bsp->beispiel_id]))
							$vorbereitet=true;
						else 
							$vorbereitet=false;

						$bsp_obj = new beispiel();

						if(!$bsp_obj->studentbeispiel_exists($uid,$bsp->beispiel_id))
						{
							$new=true;
							$bsp_obj->insertamum = date('Y-m-d H:i:s');
							$bsp_obj->insertvon = $user;
						}
						else 
						{
							$bsp_obj->load_studentbeispiel($uid, $bsp->beispiel_id);
							$new=false;
						}


						$bsp_obj->uid = $uid;
						$bsp_obj->beispiel_id = $bsp->beispiel_id;
						$bsp_obj->vorbereitet = $vorbereitet;
						$bsp_obj->updateamum = date('Y-m-d H:i:s');
						$bsp_obj->updatevon = $user;
						
						if(!$bsp_obj->studentbeispiel_save($new))
							$error=true;
					}
				}
				else
				{

					if (!$uebung_obj->load_studentuebung($uid,$uebung_id))
					{
						$uebung_obj->uid = $uid;
						$uebung_obj->mitarbeiter_uid = $user;
						$uebung_obj->abgabe_id = null;
						$uebung_obj->note = $_POST['update_'.$uid.'_note'];
						$uebung_obj->mitarbeitspunkte = null;
						$uebung_obj->punkte = null;
						$uebung_obj->anmerkung = null;
						$uebung_obj->benotungsdatum = date("Y-m-d H:i:s");
						$uebung_obj->updateamum = null;
						$uebung_obj->updatevon = null;
						$uebung_obj->insertamum = date("Y-m-d H:i:s");
						$uebung_obj->insertvon = $user;
						$new = true;
					}
					else
					{
						$uebung_obj->load_studentuebung($uid,$uebung_id);
						$uebung_obj->mitarbeiter_uid = $user;
						$uebung_obj->note = $_POST['update_'.$uid.'_note'];
						$uebung_obj->benotungsdatum = date("Y-m-d H:i:s");
						$uebung_obj->updateamum = date("Y-m-d H:i:s");
						$uebung_obj->updatevon = $user;
						$new = false;
					}
					$uebung_obj->studentuebung_save($new);

				}
			}
		}
		if(!$error)
			echo "Die &Auml;nderungen wurden erfolgreich gespeichert";
		else 
			echo "<span class='error'>Fehler beim Speichern der &Auml;nderungen</span>";
	}
	
	$uebung_obj = new uebung($uebung_id);
	$lehreinheit_obj = new lehreinheit($uebung_obj->lehreinheit_id);

	$beispiel_obj = new beispiel();
	
	$lehrveranstaltung_obj = new lehrveranstaltung($lehreinheit_obj->lehrveranstaltung_id);
	$stg_obj = new studiengang($lehrveranstaltung_obj->studiengang_kz);
	
	$beispiel_obj->load_beispiel($uebung_id);
	if ($uebung_obj->beispiele)	
		$anzahl = count($beispiel_obj->beispiele);
	else
		$anzahl = 1;
	if(isset($_GET['gruppe']) && $_GET['gruppe']!='')
	{
		$gruppe = $_GET['gruppe'];
		$qry = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheitgruppe_id=".$db->db_add_param($gruppe);
		if($result = $db->db_query($qry))
		{
			if($row = $db->db_fetch_object($result))
			{
				if($row->gruppe_kurzbz!='')
				{
					$gruppe_bez = 'Gruppe '.$row->gruppe_kurzbz;
					$qry_stud = "SELECT uid, vorname, nachname FROM campus.vw_student JOIN public.tbl_benutzergruppe USING(uid) 
					WHERE gruppe_kurzbz=".$db->db_add_param($row->gruppe_kurzbz)." AND studiensemester_kurzbz = ".$db->db_add_param($stsem)." 
					ORDER BY nachname, vorname";
				}
				else 
				{
					$gruppe_bez = 'Gruppe '.$row->verband.$row->gruppe;
					$qry_stud = "SELECT uid, vorname, nachname FROM campus.vw_student 
								WHERE studiengang_kz=".$db->db_add_param($row->studiengang_kz)."
								AND semester=".$db->db_add_param($row->semester).
								($row->verband!=''?" AND verband=".$db->db_add_param($row->verband):'').
								($row->gruppe!=''?" AND gruppe=".$db->db_add_param($row->gruppe):'').
								" ORDER BY nachname, vorname";
				}
				
			}
			else
				die('Gruppe konnte nicht ermittelt werden');
		}
		else 
			die('Gruppe konnte nicht ermittelt werden');
		$lehreinheit_id = '';
	}
	else 
	{
		if(isset($_GET['lehreinheit_id']) && $_GET['lehreinheit_id']!='')
		{
			$lehreinheit_id = $_GET['lehreinheit_id'];
			$gruppe_bez = 'Alle Studierende';
			//Alle Studenten die dieser lehreinheit zugeordnet sind
			// studentenquery		
			$qry_stud = "SELECT uid, vorname, nachname, matrikelnr FROM campus.vw_student_lehrveranstaltung JOIN campus.vw_student using(uid)
			WHERE  studiensemester_kurzbz = ".$db->db_add_param($stsem)." AND lehreinheit_id=".$db->db_add_param($lehreinheit_id, FHC_INTEGER)." ORDER BY nachname, vorname";
			/*		
			$qry_stud = "SELECT vw_student.uid, vorname, nachname FROM campus.vw_student, public.tbl_benutzergruppe, lehre.tbl_lehreinheitgruppe 
						WHERE tbl_lehreinheitgruppe.lehreinheit_id='$lehreinheit_id' AND 
						vw_student.uid = tbl_benutzergruppe.uid AND
						tbl_benutzergruppe.gruppe_kurzbz = tbl_lehreinheitgruppe.gruppe_kurzbz AND
						tbl_benutzergruppe.studiensemester_kurzbz = '$stsem'
						UNION
						SELECT vw_student.uid, vorname, nachname FROM campus.vw_student, lehre.tbl_lehreinheitgruppe WHERE
						tbl_lehreinheitgruppe.lehreinheit_id='$lehreinheit_id' AND
						tbl_lehreinheitgruppe.studiengang_kz=vw_student.studiengang_kz AND
						tbl_lehreinheitgruppe.semester = vw_student.semester AND
						((tbl_lehreinheitgruppe.verband<>'' AND 
						  tbl_lehreinheitgruppe.gruppe<>'' AND 
						  tbl_lehreinheitgruppe.verband is not null AND
						  tbl_lehreinheitgruppe.gruppe is not null AND
						  trim(tbl_lehreinheitgruppe.verband) = trim(vw_student.verband) AND
						  trim(tbl_lehreinheitgruppe.gruppe) = trim(vw_student.gruppe))
						OR
						(tbl_lehreinheitgruppe.verband<>'' AND tbl_lehreinheitgruppe.verband is not null AND
						  (trim(tbl_lehreinheitgruppe.gruppe)='' OR tbl_lehreinheitgruppe.gruppe is null) AND
						  trim(tbl_lehreinheitgruppe.verband) = trim(vw_student.verband))
						  OR (tbl_lehreinheitgruppe.verband is null AND tbl_lehreinheitgruppe.gruppe is null)
						  )
						 ORDER BY nachname, vorname";
		*/
		}
		else 
			die('Fehler bei der Parameteruebergabe');
		$gruppe='';
	}
	
	echo "<form method='POST' action='anwesenheitsliste.php?output=html&uebung_id=$uebung_id&lehreinheit_id=$lehreinheit_id&gruppe=$gruppe&stsem=$stsem'>";
	echo "<input type='hidden' name='update_ids' value=''>";
	echo "<table border='1'>
			<tr>
				<td colspan='".($anzahl+3)."' width='100%'>
					<table width='100%'>
					<tr>
						<td><font class='headline'>$lehrveranstaltung_obj->semester.Semester</font></td>
						<td align='center'><font class='headline'>$stg_obj->kuerzel - $lehrveranstaltung_obj->bezeichnung - $uebung_obj->bezeichnung - $gruppe_bez - $gruppen</font></td>
						<td align='right'><font class='headline'>".date('d.m.Y')."</font></td>
					</tr>
					</table>
				</td>
			</tr>";
	
	echo "<tr><td align='center'><b>Name</b></td>";
	if (!$uebung_obj->beispiele)
		echo "<td>Note</td>";
	else
	{
		foreach($beispiel_obj->beispiele as $row)
		{
			echo "<td>$row->bezeichnung</td>";
		}
	}
	echo "<td align='center' width='200'><b>Unterschrift</b></td><td></td></tr>\n";
	
	if($result = $db->db_query($qry_stud))
	{
		while($row_stud = $db->db_fetch_object($result))
		{

			$filename = '';
			$su_obj = new uebung($uebung_id);
			$su_obj->load_studentuebung($row_stud->uid, $uebung_id);
			if ($su_obj->abgabe_id)	
			{	
				$su_obj->load_abgabe($su_obj->abgabe_id);
				$filename = $su_obj->abgabedatei;
			}
			else
				$filename='';
			
			echo "<tr onMouseOver=\"this.style.backgroundColor='#c7dfe8'\" onMouseOut=\"this.style.backgroundColor='#ffffff'\">
			<td nowrap><input type='checkbox' name='update_$row_stud->uid' disabled>&nbsp;<b>$row_stud->nachname</b>&nbsp;$row_stud->vorname $row_stud->uid</td>";
			if (!$uebung_obj->beispiele)
			{
				$studentuebung_obj = new uebung();
				$studentuebung_obj->load_studentuebung($row_stud->uid,$uebung_id);
				echo "<td align='center'><input type='text' name='update_".$row_stud->uid."_note' onchange=\"addUser('$row_stud->uid');\" value='".$studentuebung_obj->note."' size='3'></td>\n";
				
			}			
			else
			{			
				foreach($beispiel_obj->beispiele as $row_bsp)
				{
					$studentbeispiel_obj = new beispiel();
					$studentbeispiel_obj->load_studentbeispiel($row_stud->uid, $row_bsp->beispiel_id);
					echo "<td align='center'><input type='checkbox' name='update_".$row_stud->uid."_".$row_bsp->beispiel_id."' onClick=\"addUser('$row_stud->uid');\" ".($studentbeispiel_obj->vorbereitet?'checked':'').">".($studentbeispiel_obj->probleme?'<i><small>P</small></i>':'')."</td>\n";
				}
			}
			echo "<td>&nbsp;</td>";

			if ($filename != "")			
				echo "<td><a href='anwesenheitsliste.php?uid=$row_stud->uid&output=html&uebung_id=$uebung_id&lehreinheit_id=$lehreinheit_id&stsem=$stsem&download_abgabe=$filename'>Abgabe</a></td>\n";
			else if ($uebung_obj->abgabe)
				echo "<td><span style='color:red;'>Fehlt!</span></td>";
			else
				echo "<td></td>";
			echo "</tr>\n";
		}
	}
	
	echo '</table>';
	echo "<br><br><table width='100%'><tr><td align='right'><input type='submit' name='submit' value='Änderungen Speichern'></td></tr></table>";
	echo '</form>'
	?>
</body>
</html>
<?php
}
