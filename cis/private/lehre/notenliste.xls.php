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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/*
 * Erstellt Notenliste im Excel Format
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../config/global.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/note.class.php');
require_once('../../../include/notenschluessel.class.php');
require_once('../../../include/Excel/excel.php');

$uid = get_uid();

if(!check_lektor($uid))
	die('Sie haben keine Berechtigung fuer diese Seite');

if (!$db = new basis_db())
	die('Fehler beim Herstellen der Datenbankverbindung');
	
if(isset($_GET['lvid']) && is_numeric($_GET['lvid']))
	$lvid=$_GET['lvid'];
else
	die("Fehlerhafte Parameteruebergabe");
   	
if(isset($_GET['stg']) && is_numeric($_GET['stg']))
	$stg=$_GET['stg'];
else 
	die("Fehlerhafte Parameteruebergabe");
   		
if(isset($_GET['gruppe_kurzbz']))
	$gruppe_kurzbz = $_GET['gruppe_kurzbz'];
else 
	$gruppe_kurzbz = '';
   		
if(isset($_GET['sem']) && is_numeric($_GET['sem']))
	$sem = $_GET['sem'];
else 	
	$sem = '';
   	
if(isset($_GET['verband']))
	$verband = $_GET['verband'];
else 
	$verband = '';
   		
if(isset($_GET['gruppe']) && is_numeric($_GET['gruppe']))
	$gruppe = $_GET['gruppe'];
else
	$gruppe = '';
   		
if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
	die('Studiensemester muss uebergeben werden');
   		
if(isset($_GET['lehreinheit_id']))
	$lehreinheit_id = $_GET['lehreinheit_id'];
else 
	$lehreinheit_id = '';
   	
/*
 * Create Excel File
 */

	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();
	$workbook->setVersion(8);
	
	// sending HTTP headers
	$workbook->send("Notenliste". "_" . date("d_m_Y") . ".xls");
	$workbook->setCustomColor (15,192,192,192); //Setzen der HG-Farbe Hellgrau

	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("Notenliste");
	// Neu - UTF-8 Excel
	$worksheet->setInputEncoding('utf-8');

	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();
	
	$format_highlight =& $workbook->addFormat();
	$format_highlight->setFgColor(15);
	$format_highlight->setBorder(1);
	$format_highlight->setBorderColor('white');
	
	$format_border_bottom =& $workbook->addFormat();
	$format_border_bottom ->setBottom(2);
	$format_border_bottom->setBold();

	$format_title =& $workbook->addFormat();
	$format_title->setBold();
//	$format_title->setColor('yellow');
//	$format_title->setPattern(1);
//	$format_title->setFgColor('blue');
	// let's merge
	$format_title->setAlign('merge');

	$lvobj = new lehrveranstaltung($lvid);
		
	$worksheet->write(0,0,"Notenliste ".$lvobj->bezeichnung,$format_bold);
	
	$stg_obj = new studiengang($stg);
	
	$qry = "SELECT
				distinct on(kuerzel, semester, verband, gruppe, gruppe_kurzbz) UPPER(stg_typ::varchar(1) || stg_kurzbz) as kuerzel, 
				semester, verband, gruppe, gruppe_kurzbz 
			FROM
				campus.vw_lehreinheit 
			WHERE 
				lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)." AND studiensemester_kurzbz=".$db->db_add_param($stsem);
	if($lehreinheit_id!='')
		$qry.=" AND lehreinheit_id=".$db->db_add_param($lehreinheit_id, FHC_INTEGER);

	$gruppen='';
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			if($gruppen!='')
				$gruppen.=', ';
			if($row->gruppe_kurzbz=='')
				$gruppen.=trim($row->kuerzel.'-'.$row->semester.$row->verband.$row->gruppe);
			else
				$gruppen.=$row->gruppe_kurzbz;
		}
	}
			
	$worksheet->write(1,0,"Studiengang: $stg_obj->bezeichnung $gruppen");
	$lines=2;
	//Lektoren ermitteln
	
	$qry = "SELECT 
				distinct vorname, nachname 
			FROM 
				campus.vw_benutzer, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter 
			WHERE 
				uid=mitarbeiter_uid AND 
				tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND 
				lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)." AND 
				studiensemester_kurzbz=".$db->db_add_param($stsem);
	
	if($lehreinheit_id!='')
		$qry.=" AND tbl_lehreinheit.lehreinheit_id=".$db->db_add_param($lehreinheit_id, FHC_INTEGER);
	
	$qry.=' ORDER BY nachname, vorname';
	
	if($result = $db->db_query($qry))
	{
		while($row=$db->db_fetch_object($result))
		{
			$worksheet->write($lines,1,"$row->vorname $row->nachname");
			$lines++;
		}
	}

	//Studenten holen
	$lines++;
	$worksheet->write($lines,1,"Familiennname",$format_border_bottom);
	$worksheet->write($lines,2,"Vorname",$format_border_bottom);
	$worksheet->write($lines,3,"Gruppe",$format_border_bottom);
	$worksheet->write($lines,4,"Kennzeichen",$format_border_bottom);

	if(defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE)
		$worksheet->write($lines,5,"Punkte",$format_border_bottom);
	else
		$worksheet->write($lines,5,"Note",$format_border_bottom);

	$stsem_obj = new studiensemester();
	$stsem_obj->load($stsem);
	$stsemdatumvon = $stsem_obj->start;
	$stsemdatumbis = $stsem_obj->ende;	
	
	$qry = "SELECT 
			distinct on(nachname, vorname, person_id) vorname, nachname, matrikelnr, person_id, tbl_student.student_uid as uid,
			tbl_studentlehrverband.semester, tbl_studentlehrverband.verband, tbl_studentlehrverband.gruppe,
			(SELECT status_kurzbz FROM public.tbl_prestudentstatus WHERE prestudent_id=tbl_student.prestudent_id ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1) as status,
			tbl_bisio.bisio_id, tbl_bisio.bis, tbl_bisio.von,
			tbl_zeugnisnote.note 
		FROM 
			campus.vw_student_lehrveranstaltung JOIN public.tbl_benutzer USING(uid) 
			JOIN public.tbl_person USING(person_id) JOIN public.tbl_student ON(uid=student_uid) 
			LEFT JOIN public.tbl_studentlehrverband USING(student_uid,studiensemester_kurzbz)
			LEFT JOIN lehre.tbl_zeugnisnote on(vw_student_lehrveranstaltung.lehrveranstaltung_id=tbl_zeugnisnote.lehrveranstaltung_id AND tbl_zeugnisnote.student_uid=tbl_student.student_uid AND tbl_zeugnisnote.studiensemester_kurzbz=tbl_studentlehrverband.studiensemester_kurzbz)
			LEFT JOIN bis.tbl_bisio ON(uid=tbl_bisio.student_uid)
		WHERE 
			vw_student_lehrveranstaltung.lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)." AND 
			vw_student_lehrveranstaltung.studiensemester_kurzbz=".$db->db_add_param($stsem);

	if($lehreinheit_id!='')
		$qry.=" AND vw_student_lehrveranstaltung.lehreinheit_id=".$db->db_add_param($lehreinheit_id, FHC_INTEGER);
	
	$qry.=' ORDER BY nachname, vorname, person_id, tbl_bisio.bis DESC';
	
	if($result = $db->db_query($qry))
	{
		$i=1;
		$lines++;
		while($elem = $db->db_fetch_object($result))
		{
			if(!preg_match('*dummy*',$elem->uid) && $elem->semester!=10)
	   		{   	
	   			if($elem->status!='Abbrecher' && $elem->status!='Unterbrecher')
	   			{
					$worksheet->write($lines,0,$i);
					if($elem->status=='Incoming')
						$inc=' (i)';
					else 
						$inc='';
					if($elem->bisio_id!='' && $elem->status!='Incoming' && ($elem->bis > $stsemdatumvon || $elem->bis=='') && $elem->von < $stsemdatumbis) //Outgoing
						$inc.=' (o)';
						
					if($elem->note==6) //angerechnet
					{
						$inc.=' (ar)';
						$note='6';
					}
					else 
						$note='';
					$worksheet->write($lines,1,$elem->nachname.$inc);
					$worksheet->write($lines,2,$elem->vorname);
					$worksheet->write($lines,3,$elem->semester.$elem->verband.$elem->gruppe);
					$worksheet->write($lines,4,'="'.trim($elem->matrikelnr).'"',$format_highlight);
					$worksheet->write($lines,5,$note,$format_highlight);
					$i++;
					$lines++;
	   			}
	   		}
		}
	}
	
	//Noten
	$note = new note();
	$note->getAll();

	$notenschluessel = new notenschluessel();
	$schluessel = $notenschluessel->getNotenschluessel($lvid, $stsem);
	$notenschluessel->loadAufteilung($schluessel);

	$aufteilung = array();
	foreach($notenschluessel->result as $row)
		$aufteilung[$row->note]=$row->punkte;	
	
	$worksheet->write(++$lines,0,'Noten:');
	foreach($note->result as $row)
	{
		if($row->aktiv && $row->lehre)
		{
			if(CIS_GESAMTNOTE_PUNKTE)
			{
				if(isset($aufteilung[$row->note]))
					$punkte = '>='.(float)$aufteilung[$row->note].' Punkte - ';
				else
					$punkte='';
				$worksheet->write(++$lines,0,$punkte.$row->bezeichnung.' ('.$row->anmerkung.')');
			}
			else
				$worksheet->write(++$lines,0,$row->bezeichnung.' ('.$row->anmerkung.')');
		}
	}
	
	$worksheet->writeBlank(++$lines,0,0);
	$worksheet->writeBlank(++$lines,0,$format_highlight);
	$worksheet->write($lines,1,'...Kopieren Sie diese Zellen in den Zwischenspeicher, um damit die Import-Spalte des Gesamtnotenformulars zu befüllen');
	$lines++;
	$worksheet->write(++$lines,0,'(i)  ... Incoming');	
	$worksheet->write(++$lines,0,'(o)  ... Outgoing');
	$worksheet->write(++$lines,0,'(ar) ... angerechnet');
	
	$worksheet->setColumn(0, 0, 5);
	$worksheet->setColumn(1, 1, 25);
	$worksheet->setColumn(2, 2, 25);
	$worksheet->setColumn(3, 3, 7);
	$worksheet->setColumn(4, 4, 13);
	$workbook->close();
?>
