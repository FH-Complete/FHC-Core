<?php
/* Copyright (C) 2009 Technikum-Wien
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 *          Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
 */
/**
 * Exportiert die Daten von Prestudenten und Studenten in ein Excel File.
 * 
 * Parameter:
 * GET:
 * studiensemester_kurzbz ... Studiensemester
 * POST:
 * data ... Liste der PrestudentIDs der Personen die im Export aufscheinen sollen getrennt durch ','
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/Excel/excel.php');

$user = get_uid();
$datum_obj = new datum();
$db = new basis_db();
loadVariables($user);
	
	//Parameter holen
	$data = $_REQUEST['data'];
	$studiensemester_kurzbz = $_GET['studiensemester_kurzbz'];
	
	$maxlength= array();
	$zeile=1;
	$zgv_arr=array();
	$zgvmas_arr=array();
	
	//ZGV laden
	$qry = "SELECT * FROM bis.tbl_zgv ORDER BY zgv_kurzbz";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$zgv_arr[$row->zgv_code]=$row->zgv_kurzbz;
		}
	}
	
	//ZGV Master laden
	$qry = "SELECT * FROM bis.tbl_zgvmaster ORDER BY zgvmas_kurzbz";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$zgvmas_arr[$row->zgvmas_code]=$row->zgvmas_kurzbz;
		}
	}
	
	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();
	$workbook->setVersion(8);
	
	// sending HTTP headers
	$workbook->send("Studenten". "_" . date("d_m_Y") . ".xls");

	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("Studenten");
	$worksheet->setInputEncoding('utf-8');
	
	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();

	$format_title =& $workbook->addFormat();
	$format_title->setBold();
	// let's merge
	$format_title->setAlign('merge');

	//Zeilenueberschriften ausgeben
	$i=0;
	$zeile=1;
	
	$worksheet->write($zeile,$i,"ANREDE", $format_bold);
	$maxlength[$i]=6;
	$worksheet->write($zeile,++$i,"TITELPRE", $format_bold);
	$maxlength[$i]=8;
	$worksheet->write($zeile,++$i,"NACHNAME", $format_bold);
	$maxlength[$i]=8;
	$worksheet->write($zeile,++$i,"VORNAME", $format_bold);
	$maxlength[$i]=7;
	$worksheet->write($zeile,++$i,"TITELPOST", $format_bold);
	$maxlength[$i]=9;
	$worksheet->write($zeile,++$i,"EMail Privat", $format_bold);
	$maxlength[$i]=12;
	$worksheet->write($zeile,++$i,"GEBURTSDATUM", $format_bold);
	$maxlength[$i]=12;
	$worksheet->write($zeile,++$i,"PERSONENKENNZEICHEN", $format_bold);
	$maxlength[$i]=19;
	$worksheet->write($zeile,++$i,"STAATSBÜRGERSCHAFT", $format_bold);
	$maxlength[$i]=16;
	$worksheet->write($zeile,++$i,"SVNR", $format_bold);
	$maxlength[$i]=4;
	$worksheet->write($zeile,++$i,"ERSATZKENNZEICHEN", $format_bold);
	$maxlength[$i]=17;
	$worksheet->write($zeile,++$i,"GESCHLECHT", $format_bold);
	$maxlength[$i]=10;
	$worksheet->write($zeile,++$i,"STUDIENGANG", $format_bold);
	$maxlength[$i]=11;
	$worksheet->write($zeile,++$i,"SEMESTER IM $studiensemester_kurzbz", $format_bold);
	$maxlength[$i]=19;
	$worksheet->write($zeile,++$i,"SEMESTER AKTUELL", $format_bold);
	$maxlength[$i]=17;
	$worksheet->write($zeile,++$i,"VERBAND", $format_bold);
	$maxlength[$i]=7;
	$worksheet->write($zeile,++$i,"GRUPPE", $format_bold);
	$maxlength[$i]=6;
	
	$worksheet->write($zeile,++$i,"ZGV", $format_bold);
	$maxlength[$i]=10;
	$worksheet->write($zeile,++$i,"ZGV Ort", $format_bold);
	$maxlength[$i]=14;
	$worksheet->write($zeile,++$i,"ZGV Datum", $format_bold);
	$maxlength[$i]=6;
	$worksheet->write($zeile,++$i,"ZGV Master", $format_bold);
	$maxlength[$i]=10;
	$worksheet->write($zeile,++$i,"ZGV Master Ort", $format_bold);
	$maxlength[$i]=14;
	$worksheet->write($zeile,++$i,"ZGV Master Datum", $format_bold);
	$maxlength[$i]=16;
	
	$worksheet->write($zeile,++$i,"STATUS", $format_bold);
	$maxlength[$i]=6;
	$worksheet->write($zeile,++$i,"STATI IN ANDEREN STUDIENGÄNGEN", $format_bold);
	$maxlength[$i]=8;
	$worksheet->write($zeile,++$i,"EMail Intern", $format_bold);
	$maxlength[$i]=12;
	$worksheet->write($zeile,++$i,"TELEFON", $format_bold);
	$maxlength[$i]=3;
	$worksheet->write($zeile,++$i,"GRUPPEN", $format_bold);
	$maxlength[$i]=3;
	$worksheet->write($zeile,++$i,"UID", $format_bold);
	$maxlength[$i]=3;
	$worksheet->write($zeile,++$i,"ORGFORM", $format_bold);
	$maxlength[$i]=7;
	$worksheet->write($zeile,++$i,"VORNAMEN", $format_bold);
	$maxlength[$i]=8;
	$worksheet->write($zeile,++$i,"RT_PUNKTE1", $format_bold);
	$maxlength[$i]=10;
	$worksheet->write($zeile,++$i,"RT_PUNKTE2", $format_bold);
	$maxlength[$i]=10;
	$worksheet->write($zeile,++$i,"RT_GESAMTPUNKTE", $format_bold);
	$maxlength[$i]=18;
	
	$zeile++;
	
	$ids = explode(';',$data);
	$prestudent_ids = '';
	
	foreach ($ids as $id) 
	{
		if($id!='')
		{
			if($prestudent_ids!='')
				$prestudent_ids .= ',';
			$prestudent_ids .= "'".addslashes($id)."'";
		}
	}
	
	if($prestudent_ids!='')
	{
		// Student holen
		$qry = "SELECT *, tbl_prestudent.studiengang_kz as prestgkz, (SELECT UPPER(typ || kurzbz) FROM public.tbl_studiengang WHERE studiengang_kz=tbl_prestudent.studiengang_kz) as stgbez
				FROM public.tbl_prestudent JOIN public.tbl_person USING(person_id) LEFT JOIN public.tbl_student USING(prestudent_id) 
				WHERE prestudent_id in($prestudent_ids) ORDER BY nachname, vorname";
	
		if($db->db_query($qry))
		{
			while($row = $db->db_fetch_object())
			{
				draw_content($row);
				$zeile++;
			}
		}
	}
	
	function draw_content($row)
	{
		global $maxlength, $datum_obj;
		global $zeile, $worksheet;
		global $zgv_arr, $zgvmas_arr;
		global $studiensemester_kurzbz;
		$db = new basis_db();
		
		$prestudent = new prestudent();
		$prestudent->getLastStatus($row->prestudent_id);
		$status = $prestudent->status_kurzbz;
		$orgform = $prestudent->orgform_kurzbz;
			
		$i=0;
		
		//Anrede
		if(mb_strlen($row->anrede)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->anrede);
		$worksheet->write($zeile,$i, $row->anrede);
		$i++;
		
		//Titelpre
		if(mb_strlen($row->titelpre)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->titelpre);
		$worksheet->write($zeile,$i, $row->titelpre);
		$i++;
		
		//Nachname
		if(mb_strlen($row->nachname)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->nachname);
		$worksheet->write($zeile,$i, $row->nachname);
		$i++;
		
		//Vorname
		if(mb_strlen($row->vorname)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->vorname);
		$worksheet->write($zeile,$i, $row->vorname);
		$i++;
		
		//Titelpost
		if(mb_strlen($row->titelpost)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->titelpost);
		$worksheet->write($zeile,$i, $row->titelpost);
		$i++;
		
		//Email Privat
		//ZustellEmailAdresse aus der Datenbank holen und dazuhaengen
		$qry_1 = "SELECT kontakt FROM public.tbl_kontakt 
				  WHERE kontakttyp='email' AND person_id='".addslashes($row->person_id)."' AND zustellung=true 
				  ORDER BY kontakt_id DESC LIMIT 1";
		if($db->db_query($qry_1))
		{
			if($row_1 = $db->db_fetch_object())
			{	
				if(mb_strlen($row_1->kontakt)>$maxlength[$i])
					$maxlength[$i]=mb_strlen($row_1->kontakt);
				$worksheet->write($zeile,$i, $row_1->kontakt);
			}
		}
		$i++;
						
		//Geburtsdatum
		if(mb_strlen($row->gebdatum)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->gebdatum);
		$worksheet->write($zeile,$i, $datum_obj->convertISODate($row->gebdatum));
		$i++;
		
		//Personenkennzeichen
		if(isset($row->matrikelnr))
		{
			if(mb_strlen($row->matrikelnr)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($row->matrikelnr);
			$worksheet->writeString($zeile,$i, $row->matrikelnr);
		}
		$i++;
		
		//Staatsbuergerschaft
		if(mb_strlen($row->staatsbuergerschaft)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->staatsbuergerschaft);
		$worksheet->write($zeile,$i, $row->staatsbuergerschaft);
		$i++;
		
		//SVNR
		if(mb_strlen($row->svnr)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->svnr);
		$worksheet->write($zeile,$i, $row->svnr);
		$i++;
		
		//Ersatzkennzeichen
		if(mb_strlen($row->ersatzkennzeichen)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->ersatzkennzeichen);
		$worksheet->write($zeile,$i, $row->ersatzkennzeichen);
		$i++;
		
		//Geschlecht
		if(mb_strlen($row->geschlecht)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->geschlecht);
		$worksheet->write($zeile,$i, $row->geschlecht);
		$i++;
		
		//Studiengang	
		if(mb_strlen($row->stgbez)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->stgbez);
		$worksheet->write($zeile,$i, $row->stgbez);
		$i++;
		
		$qry = "SELECT tbl_studentlehrverband.semester AS semester_studiensemester, tbl_student.semester AS semester_aktuell,* FROM public.tbl_studentlehrverband JOIN public.tbl_student USING(prestudent_id)
				WHERE prestudent_id=".$db->db_add_param($row->prestudent_id)."
				AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz);
		if($db->db_query($qry))
		{
			if($row_sem = $db->db_fetch_object())
			{
				$semester_aktuell = $row_sem->semester_aktuell;
				$semester_studiensemester = $row_sem->semester_studiensemester;
				$verband = $row_sem->verband;
				$gruppe = $row_sem->gruppe;
			}
			else 
			{
				$semester_aktuell = '';
				$verband = '';
				$gruppe = '';
			}
		}
		//Semester im eingestellten Studiensemester		
		if(isset($semester_studiensemester))
		{
			if(mb_strlen($semester_studiensemester)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($semester_studiensemester);
			$worksheet->write($zeile,$i, $semester_studiensemester);
		}
		$i++;
		
		//Semester aktuell
		if(isset($semester_aktuell))
		{
			if(mb_strlen($semester_aktuell)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($semester_aktuell);
			$worksheet->write($zeile,$i, $semester_aktuell);
		}
		$i++;
		
		//Verband
		if(isset($verband))
		{
			if(mb_strlen($verband)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($verband);
			$worksheet->write($zeile,$i, $verband);
		}
		$i++;
		
		//Gruppe
		if(isset($gruppe))
		{
			if(mb_strlen($gruppe)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($gruppe);
			$worksheet->write($zeile,$i, $gruppe);
		}
		$i++;
		
		//ZGV		
		if($row->zgv_code!='' && isset($zgv_arr[$row->zgv_code]))
		{
			if(mb_strlen($zgv_arr[$row->zgv_code])>$maxlength[$i])
				$maxlength[$i] = mb_strlen($zgv_arr[$row->zgv_code]);
			$worksheet->write($zeile,$i, $zgv_arr[$row->zgv_code]);
		}
		$i++;
		
		//ZGV Ort
		if(mb_strlen($row->zgvort)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->zgvort);
		$worksheet->write($zeile,$i, $row->zgvort);
		$i++;
		
		//ZGV Datum
		if(mb_strlen($row->zgvdatum)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->zgvdatum);
		$worksheet->write($zeile,$i, $row->zgvdatum);
		$i++;
		
		//ZGV Master
		if($row->zgvmas_code!='' && isset($zgvmas_arr[$row->zgvmas_code]))
		{
			if(mb_strlen($zgvmas_arr[$row->zgvmas_code])>$maxlength[$i])
				$maxlength[$i] = mb_strlen($zgvmas_arr[$row->zgvmas_code]);
			$worksheet->write($zeile,$i, $zgvmas_arr[$row->zgvmas_code]);
		}
		$i++;
		
		//ZGV Master Ort
		if(mb_strlen($row->zgvmaort)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->zgvmaort);
		$worksheet->write($zeile,$i, $row->zgvmaort);
		$i++;
		
		//ZGV Master Datum
		if(mb_strlen($row->zgvmadatum)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->zgvmadatum);
		$worksheet->write($zeile,$i, $row->zgvmadatum);
		$i++;
		
		//Status
		if(mb_strlen($status)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($status);
		$worksheet->write($zeile,$i, $status);
		$i++;
		
		//Stati in anderen Studiengaengen
		$stati='';
		$qry_1 = "SELECT UPPER(typ::varchar(1) || kurzbz) as stg, get_rolle_prestudent(prestudent_id, null) as status FROM
				public.tbl_prestudent JOIN public.tbl_studiengang USING(studiengang_kz) 
				WHERE person_id='".addslashes($row->person_id)."' AND tbl_prestudent.studiengang_kz<>'".addslashes($row->prestgkz)."'";
		
		if($db->db_query($qry_1))
		{
			while($row_1 = $db->db_fetch_object())
			{
				if($stati!='')
					$stati.=', ';
				$stati.= $row_1->status.' ('.$row_1->stg.')';
			}
		}
		if(mb_strlen($stati)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($stati);
		$worksheet->write($zeile,$i, $stati);
		$i++;
		
		//Email Intern
		if(isset($row->student_uid))
		{
			if(mb_strlen($row->student_uid.'@'.DOMAIN)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($row->student_uid.'@'.DOMAIN);
			$worksheet->write($zeile,$i, $row->student_uid.'@'.DOMAIN);
		}
		$i++;
				
		//Telefon
		$qry_1 = "SELECT kontakt FROM public.tbl_kontakt 
				  WHERE kontakttyp in('mobil','telefon','so.tel') AND person_id='".addslashes($row->person_id)."' AND zustellung=true LIMIT 1";
		if($db->db_query($qry_1))
		{
			if($row_1 = $db->db_fetch_object())
			{
				if(mb_strlen($row_1->kontakt)>$maxlength[$i])
					$maxlength[$i]=mb_strlen($row_1->kontakt);
				$worksheet->writeString($zeile,$i, $row_1->kontakt);
			}
		}
		$i++;
		
		//Spezialgruppen
		$grps='';
		$qry_1 = "SELECT gruppe_kurzbz FROM public.tbl_student JOIN public.tbl_benutzergruppe ON (student_uid=uid) 
				  WHERE tbl_student.prestudent_id='".addslashes($row->prestudent_id)."' 
				  AND tbl_benutzergruppe.studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";
		if($db->db_query($qry_1))
		{
			while($row_1 = $db->db_fetch_object())
			{
				if($grps!='')
					$grps.=',';
				
				$grps.=$row_1->gruppe_kurzbz;
			}
		}
		if(mb_strlen($grps)>$maxlength[$i])
			$maxlength[$i]=mb_strlen($grps);
		$worksheet->write($zeile,$i, $grps);
		$i++;
		
		//UID
		if(isset($row->student_uid))
		{
			if(mb_strlen($row->student_uid)>$maxlength[$i])
				$maxlength[$i] = mb_strlen($row->student_uid);
			$worksheet->write($zeile,$i, $row->student_uid);
		}
		$i++;
		
		//Orgform
		if(mb_strlen($orgform)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($orgform);
		$worksheet->write($zeile,$i, $orgform);
		$i++;
		
		//Vornamen
		if(mb_strlen($row->vornamen)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->vornamen);
		$worksheet->write($zeile,$i, $row->vornamen);
		$i++;
		
		
		//RT_Punkte1
		if(mb_strlen($row->rt_punkte1)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->rt_punkte1);
		$worksheet->write($zeile,$i, $row->rt_punkte1);
		$i++;
		
		//RT_Punkte2
		if(mb_strlen($row->rt_punkte2)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->rt_punkte2);
		$worksheet->write($zeile,$i, $row->rt_punkte2);
		$i++;
		
		//RT_Gesamtpunkte
		if(mb_strlen($row->rt_gesamtpunkte)>$maxlength[$i])
			$maxlength[$i] = mb_strlen($row->rt_gesamtpunkte);
		$worksheet->write($zeile,$i, $row->rt_gesamtpunkte);
		$i++;
	}
		

	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet->setColumn($i, $i, $breite+2);
    
	$workbook->close();

?>
